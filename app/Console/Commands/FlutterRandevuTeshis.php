<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Flutter mobil takviminde personelin randevulari gelmiyorsa nedenini gosterir.
 *
 * Mobil endpoint (ApiController@randevular) personel filtresi: role_id==5 iken
 * where('personel_id', GONDERILEN_ID). GONDERILEN_ID'yi Flutter, login yanitindaki
 * yetkili_olunan_isletmeler (aktif=1, salon_id'ye gore) satirindan cozer.
 * Web ise personel_id'yi sunucuda yetkili_id+salon_id ile cozer (aktif filtresiz).
 *
 * Bu komut: (a) web'in cozdugu id, (b) Flutter login'in verecegi id, (c) randevularin
 * fiilen hangi personel_id altinda oldugu -> uyusmazligi ortaya cikarir.
 *
 * Kullanim: php artisan flutter:randevu-teshis --salon=355 --personel=1202 [--tarih=2026-07-31]
 */
class FlutterRandevuTeshis extends Command
{
    protected $signature = 'flutter:randevu-teshis {--salon= : Salon ID} {--personel= : salon_personelleri.id} {--tarih= : YYYY-MM-DD, varsayilan bugun}';
    protected $description = 'Flutter takviminde personelin randevulari gelmiyor teshisi (personel_id cozumleme).';

    public function handle()
    {
        $salonId = (int) $this->option('salon');
        $personelId = (int) $this->option('personel');
        if (!$salonId || !$personelId) { $this->error('--salon ve --personel zorunlu'); return 1; }
        $tarih = $this->option('tarih') ?: date('Y-m-d');

        $this->info("=== FLUTTER RANDEVU TESHIS — salon {$salonId} / personel {$personelId} / tarih {$tarih} ===");

        // 1) Personel kaydi
        $p = DB::table('salon_personelleri')->where('id', $personelId)
            ->first(['id', 'salon_id', 'yetkili_id', 'role_id', 'aktif', 'personel_adi']);
        if (!$p) { $this->error("salon_personelleri.id={$personelId} YOK"); return 1; }
        $this->line("\n[1] PERSONEL: ad=" . ($p->personel_adi ?? '-') . "  salon_id={$p->salon_id}  yetkili_id={$p->yetkili_id}  role_id=" . var_export($p->role_id, true) . "  aktif={$p->aktif}");
        if ((int)$p->salon_id !== $salonId) $this->warn("  !! Bu personel salon {$p->salon_id}'e ait, --salon={$salonId} degil.");
        if ((int)$p->role_id !== 5) $this->warn("  !! role_id != 5 -> mobilde personel filtresi UYGULANMAZ (tum randevular gelir, bos degil).");
        $yetkiliId = (int) $p->yetkili_id;

        // 2) Ayni yetkili_id'nin TUM salon_personelleri kayitlari (mukerrer/coklu sube)
        $tumKayitlar = DB::table('salon_personelleri')->where('yetkili_id', $yetkiliId)
            ->orderBy('salon_id')->orderBy('id')->get(['id', 'salon_id', 'role_id', 'aktif', 'personel_adi']);
        $this->line("\n[2] yetkili_id={$yetkiliId} TUM personel kayitlari ({$tumKayitlar->count()} satir):");
        foreach ($tumKayitlar as $k) {
            $mark = ((int)$k->salon_id === $salonId) ? '  <-- bu salon' : '';
            $this->line("   id={$k->id}  salon_id={$k->salon_id}  role_id=" . var_export($k->role_id, true) . "  aktif={$k->aktif}  ad=" . ($k->personel_adi ?? '-') . $mark);
        }
        $buSalondakiler = $tumKayitlar->where('salon_id', $salonId);
        if ($buSalondakiler->count() > 1) {
            $this->warn("  !! Bu salonda yetkili_id={$yetkiliId} icin BIRDEN FAZLA personel kaydi var -> id cozumleme tutarsiz olabilir.");
        }

        // 3) Web'in cozecegi personel_id (aktif FILTRESIZ, value = en dusuk id)
        $webId = DB::table('salon_personelleri')->where('yetkili_id', $yetkiliId)
            ->where('salon_id', $salonId)->value('id');
        $this->line("\n[3] WEB cozumu (yetkili_id+salon_id, aktif filtresiz -> value id): " . var_export($webId, true));

        // 4) Flutter login'in verecegi personel_id (yetkili_olunan_isletmeler: aktif=1, salon_id'ye gore)
        //    AuthController login yaniti aktif=1 filtreler; app takvim.dart salon_id eslesen ilk/son satirin id'sini alir.
        $loginKayitlari = DB::table('salon_personelleri')->where('yetkili_id', $yetkiliId)
            ->where('salon_id', $salonId)->where('aktif', 1)->orderBy('id')->pluck('id');
        $this->line("\n[4] FLUTTER login (aktif=1) bu salon icin verebilecegi id'ler: " .
            ($loginKayitlari->isEmpty() ? 'HICBIRI (aktif=1 kayit YOK)' : $loginKayitlari->implode(', ')));
        if ($loginKayitlari->isEmpty()) {
            $this->error("  ** Bu salonda aktif=1 personel kaydi YOK -> Flutter dogru personel_id cozemez,");
            $this->error("     ONCEKI subenin BAYAT id'sini gonderir -> mobil takvim BOS. (Web aktif filtrelemedigi icin calisiyor.)");
        }

        // 5) Randevular fiilen hangi personel_id altinda? (o tarih, durum<2)
        $this->line("\n[5] {$tarih} salon {$salonId} randevu_hizmetler personel_id dagilimi (durum<2, sure>0):");
        $dagilim = DB::table('randevu_hizmetler as rh')
            ->join('randevular as r', 'r.id', '=', 'rh.randevu_id')
            ->where('r.salon_id', $salonId)->where('r.tarih', $tarih)
            ->where('r.durum', '<', 2)->where('rh.sure_dk', '>', 0)
            ->groupBy('rh.personel_id')
            ->select('rh.personel_id', DB::raw('COUNT(*) as adet'))
            ->orderBy('adet', 'desc')->get();
        if ($dagilim->isEmpty()) {
            $this->warn("  Bu tarihte salon {$salonId}'de hic randevu kalemi yok (durum<2). Baska tarih deneyin: --tarih=");
        }
        foreach ($dagilim as $d) {
            $ad = DB::table('salon_personelleri')->where('id', $d->personel_id)->value('personel_adi');
            $mark = ((int)$d->personel_id === $personelId) ? '  <-- aranan personel' : '';
            $this->line("   personel_id=" . var_export($d->personel_id, true) . "  adet={$d->adet}  ({$ad})" . $mark);
        }

        // 6) Sonuc
        $this->line("\n[6] SONUC:");
        $arananVar = $dagilim->firstWhere('personel_id', $personelId);
        $webVar = $webId ? $dagilim->firstWhere('personel_id', $webId) : null;
        if (!$arananVar) {
            $this->warn("  - personel {$personelId} icin bu tarihte randevu YOK. (Belki randevular baska personel_id altinda -> yukari bak.)");
        } else {
            $this->info("  - personel {$personelId} randevulari MEVCUT ({$arananVar->adet}). Sorun cozumleme/filtrede.");
        }
        if ($webId && (int)$webId !== $personelId) {
            $this->warn("  - WEB {$webId}'e cozuyor ama sorulan {$personelId}. Randevular hangisinde ise takvim onu gosterir.");
        }
        if ($loginKayitlari->isEmpty()) {
            $this->error("  - ANA SEBEP ADAYI: Flutter bu salonda aktif=1 kayit bulamayip bayat personel_id gonderiyor.");
            $this->line("    Cozum secenekleri: (a) personel {$personelId} kaydini aktif=1 yap; (b) mobil endpoint");
            $this->line("    personel_id'yi auth'tan cozsun (deploy edildi ama auth guard null donuyorsa etkisiz).");
        } elseif (!$loginKayitlari->contains($personelId)) {
            $this->warn("  - Flutter'in verecegi id(ler) [" . $loginKayitlari->implode(',') . "] arasinda {$personelId} YOK -> app farkli id gonderiyor olabilir.");
        }

        $this->info("\n=== BITTI ===");
        return 0;
    }
}
