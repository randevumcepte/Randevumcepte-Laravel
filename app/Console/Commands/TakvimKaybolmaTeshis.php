<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * "Web takviminde randevular bir sure sonra kayboluyor, mobilde hic" sikayeti teshisi.
 *
 * Mekanizma (StoreAdminController::randevuyukle + gecmisRandevuGizlensinMi):
 *   - Salonlar.gecmis_randevulari_gizle = 1 VE host = randevu.randevumcepte.com.tr ise
 *     randevuyukle sorgusuna "CONCAT(tarih, saat) >= NOW()" kosulu eklenir; saati gecen
 *     her kalem bir sonraki takvim yenilemesinde (15sn imza pollingi -> takvimyukle) duser.
 *   - Mobil (ApiController, farkli host) bu filtreyi UYGULAMAZ -> mobilde hic kaybolmaz.
 *   - Personel rolu (role_id 5) + 'randevu.tum_personel_gor' yetkisi YOKSA takvimde
 *     yalnizca kendi personel_id'sine atali randevular gorunur.
 *
 * Kullanim: php artisan takvim:kaybolma-teshis --salon=355 --personel=1202
 */
class TakvimKaybolmaTeshis extends Command
{
    protected $signature = 'takvim:kaybolma-teshis {--salon= : Salon ID} {--personel= : salon_personelleri.id (opsiyonel)}';
    protected $description = 'Web takviminde randevu kaybolma (gecmis_randevulari_gizle) teshisi.';

    public function handle()
    {
        $salonId = (int) $this->option('salon');
        if (!$salonId) { $this->error('--salon zorunlu'); return 1; }
        $personelId = (int) $this->option('personel');

        $salon = DB::table('salonlar')->where('id', $salonId)
            ->first(['id', 'salon_adi', 'gecmis_randevulari_gizle', 'randevu_takvim_turu']);
        if (!$salon) { $this->error("Salon {$salonId} bulunamadi."); return 1; }

        $this->info("=== TAKVIM KAYBOLMA TESHIS — salon {$salonId} ({$salon->salon_adi}) ===");

        // 1) Ana bayrak
        $gizle = (int) ($salon->gecmis_randevulari_gizle ?? 0);
        $this->line("\n[1] Salonlar.gecmis_randevulari_gizle = {$gizle}");
        if ($gizle === 1) {
            $this->error("  ** ACIK. Web panelinde (randevu.randevumcepte.com.tr) saati gecen randevular");
            $this->error("     her yenilemede takvimden DUSER. Mobilde bu filtre yok -> mobilde kaybolmaz.");
            $this->line("     Bu, sikayetin en olasi nedeni. Kapatmak icin: online randevu ayarlarindan");
            $this->line("     'Gecmis randevulari gizle' kapatilir ya da bu kolon 0 yapilir.");
        } else {
            $this->info("  KAPALI. Kaybolma bu ayardan DEGIL. Baska neden aranmali (durum>=2, tarih araligi,");
            $this->line("     ya da resource/refetch). Asagidaki [3] sayimlari yine de bilgi verir.");
        }

        // 2) Takvim modu
        $mod = (int) ($salon->randevu_takvim_turu ?? 0);
        $modAd = [0 => 'personele gore', 1 => 'personele gore', 2 => 'cihaza gore', 3 => 'odaya gore'][$mod] ?? 'bilinmiyor';
        $this->line("\n[2] randevu_takvim_turu = {$mod} ({$modAd})");

        // 3) Personel bazli bugunku randevu sayimi (gecmis vs gelecek)
        if ($personelId) {
            $p = DB::table('salon_personelleri')->where('id', $personelId)
                ->first(['id', 'salon_id', 'yetkili_id', 'aktif', 'personel_adi']);
            $this->line("\n[3] PERSONEL {$personelId}:");
            if (!$p) {
                $this->warn("  salon_personelleri.id={$personelId} bulunamadi.");
            } else {
                $this->line("  ad=" . ($p->personel_adi ?? '-') . "  salon_id={$p->salon_id}  yetkili_id={$p->yetkili_id}  aktif={$p->aktif}");
                if ((int)$p->salon_id !== $salonId) {
                    $this->warn("  !! Bu personel salon {$p->salon_id}'e ait, --salon={$salonId} ile ayni degil.");
                }
                // role_id 5 (personel rolu) kontrolu -> kisitli goruntuleme adayi
                $rol5 = DB::table('model_has_roles')->where('role_id', 5)
                    ->where('model_id', $p->yetkili_id)->exists();
                $this->line("  personel rolu (model_has_roles role_id=5, model_id=yetkili_id): " . ($rol5 ? 'VAR' : 'yok'));
                $this->line("  (Not: 'randevu.tum_personel_gor' yetkisi VARSA tum randevulari gorur; yoksa yalniz kendi.)");

                $bugun = date('Y-m-d');
                $simdi = date('Y-m-d H:i:s');
                $base = DB::table('randevu_hizmetler as rh')
                    ->join('randevular as r', 'r.id', '=', 'rh.randevu_id')
                    ->where('r.salon_id', $salonId)
                    ->where('r.tarih', $bugun)
                    ->where('r.durum', '<', 2)
                    ->where('rh.sure_dk', '>', 0)
                    ->where('rh.personel_id', $personelId);
                $toplam = (clone $base)->count();
                $gecmisKalem = (clone $base)
                    ->whereRaw("CONCAT(r.tarih,' ',COALESCE(rh.saat, r.saat)) < ?", [$simdi])->count();
                $gelecek = $toplam - $gecmisKalem;
                $this->line("  Bugun ({$bugun}) personel {$personelId} kalem sayisi: toplam={$toplam}, saati-gecmis={$gecmisKalem}, kalan={$gelecek}");
                if ($gizle === 1 && $gecmisKalem > 0) {
                    $this->error("  ** Su an {$gecmisKalem} kalem 'saati gecmis' -> web takviminde GORUNMUYOR (gizle acik).");
                    $this->line("     Mobilde bu {$gecmisKalem} kalem gorunmeye devam eder. Fark tam olarak bu.");
                }
            }
        } else {
            $this->line("\n[3] Personel sayimi icin --personel=<id> ekleyin.");
        }

        $this->info("\n=== BITTI ===");
        return 0;
    }
}
