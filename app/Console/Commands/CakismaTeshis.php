<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Salonlar;

/**
 * Kaynak çakışma uyarısı teşhis komutu — randevu verirken "aynı saatte dolu
 * personel/cihaz/oda" popup'ı NEDEN çıkmıyor tek komutta gösterir.
 *
 * kaynak_cakisma_kontrol() sessizce null döndüğünde suçlu genelde:
 *   1) salonlar.cakisma_uyarisi_aktif kolonu YOK (migration prod'da çalışmamış)
 *   2) kolon var ama ayar KAPALI (varsayılan 0)
 *   3) takvim türü ile seçilen kaynak kolonu eşleşmiyor / o gün o kaynakta
 *      durum=1 onaylı randevu yok
 *
 * Kullanım:
 *   /opt/php74/bin/php artisan cakisma:teshis {salon}
 *   /opt/php74/bin/php artisan cakisma:teshis {salon} {tarih=Y-m-d} {saat=H:i}
 *
 * Örnek:
 *   /opt/php74/bin/php artisan cakisma:teshis 331
 *   /opt/php74/bin/php artisan cakisma:teshis 331 2026-08-08 15:00
 */
class CakismaTeshis extends Command
{
    protected $signature = 'cakisma:teshis {salon} {tarih?} {saat?}';
    protected $description = 'Kaynak çakışma uyarısı teşhisi: kolon/ayar/takvim türü ve o gün ilgili kaynağın doluluğu';

    public function handle()
    {
        $salonId = (int) $this->argument('salon');
        $tarih   = $this->argument('tarih') ?: date('Y-m-d');
        $saat    = $this->argument('saat')  ?: date('H:i');

        $this->info('=== KAYNAK ÇAKIŞMA TEŞHİS ===');
        $this->line("salon={$salonId}  tarih={$tarih}  saat={$saat}");
        $this->line('');

        // 1) Kolon var mı? (migration prod'da çalıştı mı)
        $kolonVar = Schema::hasColumn('salonlar', 'cakisma_uyarisi_aktif');
        $this->info('--- 1) salonlar.cakisma_uyarisi_aktif kolonu ---');
        if (!$kolonVar) {
            $this->error('  KOLON YOK  -> migration prod\'da ÇALIŞMAMIŞ.');
            $this->line('  Çözüm: /opt/php74/bin/php artisan migrate --force');
            $this->line('  (Kolon olmadan ayar hiç kaydedilmez ve kontrol her zaman null döner.)');
            return 0;
        }
        $this->line('  kolon VAR (ok)');
        $migRun = DB::table('migrations')
            ->where('migration', 'like', '%add_cakisma_uyarisi_to_salonlar%')->exists();
        $this->line('  migrations tablosunda kayit: '.($migRun ? 'VAR (ok)' : 'YOK (kolon elle mi eklendi?)'));
        $this->line('');

        // 2) Salon + ayar + takvim türü
        $salon = Salonlar::find($salonId);
        if (!$salon) {
            $this->error("  salon #{$salonId} bulunamadi.");
            return 0;
        }
        $ayar = $salon->cakisma_uyarisi_aktif;
        $turu = (int) $salon->randevu_takvim_turu;
        $turuAd = [0 => 'Hizmet/Kategori', 1 => 'Personel', 2 => 'Cihaz', 3 => 'Oda'][$turu] ?? "bilinmiyor({$turu})";

        $this->info('--- 2) Salon ayarı ---');
        $this->line('  cakisma_uyarisi_aktif : '.var_export($ayar, true).($ayar ? '  (ACIK)' : '  <- KAPALI: kontrol hic calismaz'));
        $this->line("  randevu_takvim_turu   : {$turu}  ({$turuAd})");
        if (empty($ayar)) {
            $this->line('');
            $this->error('  AYAR KAPALI -> kaynak_cakisma_kontrol ilk satirda null doner.');
            $this->line('  Çözüm: İşletme Ayarları > "Aynı saatte dolu personel/cihaz/oda için uyar" kutusunu isaretle,');
            $this->line('         VEYA: UPDATE salonlar SET cakisma_uyarisi_aktif=1 WHERE id='.$salonId.';');
            return 0;
        }
        $this->line('');

        // 3) Takvim türüne göre kontrol edilecek kolon
        if ($turu == 2)      { $kolon = 'cihaz_id';    $tip = 'cihaz'; }
        elseif ($turu == 3)  { $kolon = 'oda_id';      $tip = 'oda'; }
        else                 { $kolon = 'personel_id'; $tip = 'personel'; }

        $this->info('--- 3) O gün ilgili kaynak doluluğu ---');
        $this->line("  Kontrol edilen kolon  : randevu_hizmetler.{$kolon}  (tip={$tip})");

        $kayitlar = DB::table('randevular')
            ->join('randevu_hizmetler', 'randevular.id', '=', 'randevu_hizmetler.randevu_id')
            ->where('randevular.tarih', $tarih)
            ->where('randevular.durum', 1)
            ->where('randevular.salon_id', $salonId)
            ->whereNotNull('randevu_hizmetler.'.$kolon)
            ->select(
                'randevular.id as randevu_id',
                'randevu_hizmetler.'.$kolon.' as kaynak_id',
                'randevu_hizmetler.saat',
                'randevu_hizmetler.saat_bitis',
                'randevu_hizmetler.hizmet_id'
            )
            ->orderBy('randevu_hizmetler.saat')
            ->get();

        $this->line("  O gün durum=1 + {$kolon} dolu randevu-hizmet satiri: ".count($kayitlar));
        if (count($kayitlar) === 0) {
            $this->line('');
            $this->error("  BU KOLON İÇİN KAYIT YOK -> Muhtemel nedenler:");
            $this->line("   - Takvim türü '{$turuAd}' ama randevular {$kolon} dolu kaydedilmiyor (yanlis mod?)");
            $this->line("   - O gün gerçekten onayli (durum=1) randevu yok");
            $this->line("   - Eski randevularda {$kolon} NULL (kaynak randevu_hizmetler'e yazilmamis)");
            // Alternatif kolonlarda kayit var mi -> mod uyumsuzlugunu yakala
            foreach (['personel_id', 'cihaz_id', 'oda_id'] as $alt) {
                if ($alt === $kolon) continue;
                $c = DB::table('randevular')
                    ->join('randevu_hizmetler', 'randevular.id', '=', 'randevu_hizmetler.randevu_id')
                    ->where('randevular.tarih', $tarih)->where('randevular.durum', 1)
                    ->where('randevular.salon_id', $salonId)
                    ->whereNotNull('randevu_hizmetler.'.$alt)->count();
                $this->line("   [karsilastirma] {$alt} dolu satir: {$c}");
            }
            return 0;
        }

        $this->line('');
        $this->info('  Kaynak bazinda pencereler (saat-saat_bitis):');
        foreach ($kayitlar as $k) {
            $this->line(sprintf('   randevu#%-6s kaynak=%-5s  %s - %s  (hizmet=%s)',
                $k->randevu_id, $k->kaynak_id, $k->saat, $k->saat_bitis ?: 'BOS', $k->hizmet_id ?: '-'));
            if (empty($k->saat_bitis)) {
                $this->error('     ^ saat_bitis BOS -> overlap hesabi bu kayitta calismaz!');
            }
        }

        $this->line('');
        $this->info("--- Ozet: girilen {$saat} icin cakisan kaynaklar ---");
        $ns = strtotime($saat);
        $carpisan = 0;
        foreach ($kayitlar as $k) {
            if (empty($k->saat_bitis)) continue;
            $es = strtotime($k->saat);
            $ee = strtotime($k->saat_bitis);
            // yeni randevu suresi bilinmediginden 0-genislik varsayimi (baslangic mevcut aralikta mi)
            if ($ns >= $es && $ns < $ee) {
                $this->line("   kaynak={$k->kaynak_id} DOLU ({$k->saat}-{$k->saat_bitis}) -> bu kaynaga verilirse popup CIKAR");
                $carpisan++;
            }
        }
        if ($carpisan === 0) {
            $this->line("   {$saat} baslangicinda hicbir kaynak dolu degil -> bu saatte popup beklenmez (dogru davranis).");
        }

        return 0;
    }
}
