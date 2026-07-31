<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Isletme yetkilisi (isletmeyetkilileri) neden login olamiyor / "sifre hatali"?
 *
 * Login akisi (AuthController::login + AuthStoreAdmin/LoginController::login):
 *   Auth::guard('isletmeyonetim')->attempt(['gsm1'=>tel,'password'=>...])  (web ayrica gsm2)
 *   -> EloquentUserProvider::retrieveByCredentials: where('gsm1', tel)->first()
 *   -> Hash::check(girilen_sifre, isletmeyetkilileri.password)
 *
 * Sifre gonderme (StoreAdminController::personelsifregonder):
 *   personel(salon_personelleri).yetkili_id -> isletmeyetkilileri kaydini gunceller.
 *
 * ANA ARIZA: ayni gsm1 birden fazla isletmeyetkilileri satirinda varsa, reset BIR
 * satiri gunceller ama login ->first() BASKA satiri (dusuk id) dogrular -> her sifre hatali.
 *
 * Kullanim:
 *   php artisan yetkili:giris-teshis 5365225639
 *   php artisan yetkili:giris-teshis 5365225639 --sifre=ABC123   (hangi satir eslesiyor)
 *   php artisan yetkili:giris-teshis 5365225639 --salon=355
 */
class YetkiliGirisTeshis extends Command
{
    protected $signature = 'yetkili:giris-teshis {telefon : gsm1/gsm2 numarasi} {--sifre= : verilen sifreyi her satira Hash::check et} {--salon= : ilgili salon id (bilgi amacli)}';
    protected $description = 'Isletme yetkilisi neden login olamiyor (duplicate gsm1 / hash / format) teshisi.';

    private function tel_format($telefon)
    {
        // AuthController::telefon_no_format_duzenle ile ayni mantik
        $phone = preg_replace('/^(\+?90|0)/', '', $telefon);
        $phone = str_replace(['(', ')', ' ', '-'], '', $phone);
        return $phone;
    }

    public function handle()
    {
        $ham = trim($this->argument('telefon'));
        $norm = $this->tel_format($ham);
        $sifre = $this->option('sifre');
        $salon = $this->option('salon');

        $this->info("=== YETKILI GIRIS TESHIS ===");
        $this->line("Girilen numara (ham) : {$ham}");
        $this->line("Normalize (login format) : {$norm}");
        if ($salon) $this->line("Ilgili salon (bilgi) : {$salon}");

        // 1) Login'in gsm1/gsm2 ile bulacagi TUM satirlar (normalize edilmis deger, attempt boyle sorgular)
        $gsm1Rows = DB::table('isletmeyetkilileri')->where('gsm1', $norm)->orderBy('id')->get();
        $gsm2Rows = DB::table('isletmeyetkilileri')->where('gsm2', $norm)->orderBy('id')->get();

        // 2) gsm1'in ham/farkli formatlarda kayitli olma ihtimali (login BULAMAZ ise sebep bu)
        $benzer = DB::table('isletmeyetkilileri')
            ->where(function ($q) use ($ham, $norm) {
                $q->where('gsm1', 'like', '%' . substr($norm, -9) . '%')
                  ->orWhere('gsm2', 'like', '%' . substr($norm, -9) . '%');
            })->orderBy('id')->get();

        $this->line("\n[1] gsm1 = '{$norm}' ile eslesen satir sayisi: " . $gsm1Rows->count());
        $this->line("    gsm2 = '{$norm}' ile eslesen satir sayisi: " . $gsm2Rows->count());

        if ($gsm1Rows->isEmpty() && $gsm2Rows->isEmpty()) {
            $this->warn("  !! Login sorgusu (normalize) HICBIR satir bulamiyor -> 'kullanici bulunamadi/hatali'.");
            $this->warn("     Numara muhtemelen farkli formatta kayitli. Benzer satirlar (son 9 hane):");
        }

        $tumSatirlar = collect();
        foreach ([$gsm1Rows, $gsm2Rows, $benzer] as $set) {
            foreach ($set as $r) {
                if (!$tumSatirlar->contains('id', $r->id)) $tumSatirlar->push($r);
            }
        }
        $tumSatirlar = $tumSatirlar->sortBy('id');

        $this->line("\n[2] ILGILI ISLETMEYETKILILERI SATIRLARI:");
        foreach ($tumSatirlar as $r) {
            $pw = $r->password ?? '';
            $hashTip = (strlen($pw) >= 55 && (strpos($pw, '$2y$') === 0 || strpos($pw, '$2a$') === 0))
                ? 'bcrypt-OK' : ('!!SUPHELI(len=' . strlen($pw) . ')');
            $bagliPersonel = DB::table('salon_personelleri')->where('yetkili_id', $r->id)
                ->get(['id', 'salon_id', 'aktif', 'personel_adi']);
            $subeler = $bagliPersonel->map(function ($p) {
                return $p->salon_id . '(p' . $p->id . ($p->aktif ? ',aktif' : ',PASIF') . ')';
            })->implode(', ');

            $this->line("  ┌ id={$r->id}  name=" . ($r->name ?? '-'));
            $this->line("  │ gsm1=" . ($r->gsm1 ?? '-') . "  gsm2=" . ($r->gsm2 ?? '-'));
            $this->line("  │ password: {$hashTip}  prefix=" . substr($pw, 0, 7) . "...");
            $this->line("  │ bagli salon_personelleri (yetkili_id={$r->id}): " . ($subeler ?: 'YOK'));
            if ($sifre !== null && $sifre !== '') {
                $eslesti = $pw ? Hash::check($sifre, $pw) : false;
                $this->line("  │ Hash::check('{$sifre}') => " . ($eslesti ? '>>> ESLESTI <<<' : 'eslesmedi'));
            }
            $this->line("  └");
        }

        // 3) Login'in fiilen dogrulayacagi satir (retrieveByCredentials ->first(), id ASC)
        $loginSatir = $gsm1Rows->first();
        $this->line("\n[3] LOGIN gsm1 ile SU SATIRI dogrular (->first, en dusuk id): " .
            ($loginSatir ? "id={$loginSatir->id} name=" . ($loginSatir->name ?? '-') : 'YOK'));

        // 4) Teshis sonucu
        $this->line("\n[4] SONUC:");
        if ($gsm1Rows->count() > 1) {
            $this->error("  ** DUPLICATE gsm1: {$gsm1Rows->count()} satir ayni gsm1'e sahip. **");
            $this->error("     Sifre gonderme personelin yetkili_id satirini gunceller; login ise en dusuk id'li");
            $this->error("     satiri (id={$loginSatir->id}) dogrular. Farkli satirlarsa sifre HER ZAMAN hatali cikar.");
            $this->line("     Cozum: dogru yetkili satirinda sifre set et, ya da mukerrer satirlari birlestir/temizle.");
        } elseif ($gsm1Rows->count() === 1) {
            $r = $gsm1Rows->first();
            $pw = $r->password ?? '';
            $bcryptOk = (strlen($pw) >= 55 && (strpos($pw, '$2y$') === 0 || strpos($pw, '$2a$') === 0));
            if (!$bcryptOk) {
                $this->error("  ** id={$r->id} password bcrypt hash DEGIL (len=" . strlen($pw) . "). Hash::check hep false.");
                $this->line("     Cozum: sifreyi Hash::make ile yeniden set et (Sifre Degistir & Gonder).");
            } else {
                $this->info("  Tek satir + bcrypt hash normal.");
                $this->line("  Sifre eslesmesi icin --sifre parametresini kullanin. Eslesiyorsa sorun");
                $this->line("  yetki/aktif filtresinde (bkz. [5]) ya da girilen numara/formatta.");
            }
        }

        // 5) Yetki/aktif durumu (giris gecse bile 'yetkiniz yok' verebilir)
        if ($loginSatir) {
            $aktifSube = DB::table('salon_personelleri')->where('yetkili_id', $loginSatir->id)
                ->where('aktif', 1)->count();
            $this->line("\n[5] Login satiri (id={$loginSatir->id}) aktif=1 bagli sube sayisi: {$aktifSube}");
            if ($aktifSube === 0) {
                $this->warn("     !! aktif sube YOK -> giris dogrulansa bile 'yetkiniz bulunmamaktadir' donebilir.");
            }
        }

        $this->info("\n=== BITTI ===");
        return 0;
    }
}
