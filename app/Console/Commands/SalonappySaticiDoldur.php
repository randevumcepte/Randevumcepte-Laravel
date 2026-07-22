<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Aktarilmis Salonappy satislarinda EKSIK KALAN personel_id'yi dump'taki
 * satici bilgisinden doldurur.
 *
 * Neden gerekli: eski importer paket satislarini adisyon_hizmetler'e
 * personel_id YAZMADAN ekliyordu. Prim & Hak Edis raporu personel_id'si bos
 * kalemleri komple atladigi icin bu satislar hicbir ayda prime girmiyor.
 *
 * NE YAPAR : sadece personel_id'si NULL olan kalemleri UPDATE eder.
 * NE YAPMAZ: kayit SILMEZ, kayit EKLEMEZ, yeni personel OLUSTURMAZ,
 *            dolu personel_id'nin uzerine YAZMAZ.
 *
 * Eslesme zinciri (tahmin yok, birebir dump):
 *   adisyonlar.notlar '[salonappy-pkgsale:<group_id>]'
 *     -> dump packageSales[group_id].seller_text
 *     -> salon_personelleri (Turkce-duyarsiz ad eslesmesi, MEVCUT kayit)
 *
 * seller_id=0 / seller_text="Kasa" olan satislarda Salonappy'de gercek satici
 * YOKTUR; bunlar atlanir ve raporlanir (elle karar verilmesi gerekir).
 */
class SalonappySaticiDoldur extends Command
{
    protected $signature = 'salonappy:satici-doldur
        {--salon= : Salon (isletme) ID — zorunlu}
        {--dump-file= : package_sales veya product_sales dump JSON yolu — zorunlu}
        {--uygula : Gercekten yaz (verilmezse sadece rapor / dry-run)}';

    protected $description = 'Salonappy dump\'indaki seller_text bilgisinden eksik adisyon personel_id\'lerini doldurur (sadece UPDATE).';

    public function handle()
    {
        $salonId = (int) $this->option('salon');
        $dosya = $this->option('dump-file');
        if (!$salonId) { $this->error('--salon zorunlu'); return 1; }
        if (!$dosya || !file_exists($dosya)) { $this->error('--dump-file bulunamadi: ' . $dosya); return 1; }

        $uygula = (bool) $this->option('uygula');
        $j = json_decode(file_get_contents($dosya), true);
        if (!is_array($j)) { $this->error('Dump okunamadi (gecersiz JSON).'); return 1; }

        $this->info('=== SALONAPPY SATICI DOLDUR — salon ' . $salonId
            . ' | mod: ' . ($uygula ? 'UYGULA (yazacak)' : 'DRY-RUN (yazmaz)') . ' ===');

        $toplam = 0;
        $toplam += $this->isle($salonId, $uygula, 'paket',
            $j['packageSales'] ?? [], 'salonappy-pkgsale', 'adisyon_hizmetler', 'seller_text', true);
        $toplam += $this->isle($salonId, $uygula, 'urun',
            $j['productSales'] ?? [], 'salonappy-prodsale', 'adisyon_urunler', 'seller_name', false);

        if ($toplam === 0) $this->warn('Dump\'ta islenecek satis bulunamadi.');
        if (!$uygula) $this->comment("\nDRY-RUN — hicbir sey yazilmadi. Uygulamak icin --uygula ekle.");
        $this->line('Kontrol: php artisan prim:teshis --salon=' . $salonId);
        return 0;
    }

    /**
     * @param  array  $satirlar   dump satislari
     * @param  string $markerAd   adisyonlar.notlar icindeki marker on eki
     * @param  string $tablo      guncellenecek kalem tablosu
     * @param  string $adKolonu   dump'taki satici ad alani
     * @param  bool   $grupla     marker group_id mi (paket) yoksa id mi (urun)
     */
    private function isle($salonId, $uygula, $etiket, $satirlar, $markerAd, $tablo, $adKolonu, $grupla)
    {
        if (empty($satirlar) || !\Schema::hasTable($tablo)) return 0;
        if (!\Schema::hasColumn($tablo, 'personel_id')) return 0;

        $this->line("\n[{$etiket}] dump satiri: " . count($satirlar));

        // marker anahtari -> satici adi (Kasa/bos olanlar ayri sayilir)
        $saticiler = []; $kasaAnahtar = [];
        foreach ($satirlar as $r) {
            $anahtar = (string) ($grupla ? ($r['group_id'] ?? $r['id'] ?? '') : ($r['id'] ?? ''));
            if ($anahtar === '') continue;
            $ad = trim((string) ($r[$adKolonu] ?? ''));
            $sellerId = trim((string) ($r['seller_id'] ?? ''));
            if ($ad === '' || $sellerId === '0' || $this->trKey($ad) === 'kasa') {
                $kasaAnahtar[$anahtar] = true;
                continue;
            }
            $saticiler[$anahtar] = $ad;
        }
        $this->line('  gercek saticili: ' . count($saticiler) . '  |  Kasa/saticisiz: ' . count($kasaAnahtar));

        // Ad -> MEVCUT personel_id (yeni personel olusturulmaz)
        $personeller = DB::table('salon_personelleri')->where('salon_id', $salonId)->get(['id', 'personel_adi']);
        $adHarita = [];
        foreach ($personeller as $p) $adHarita[$this->trKey($p->personel_adi)] = (int) $p->id;

        $eslesmeyen = [];
        $guncellenecek = [];   // personel_id => [kalem id, ...]
        $adisyonYok = 0; $zatenDolu = 0; $kalemYok = 0;

        foreach ($saticiler as $anahtar => $ad) {
            $pid = $adHarita[$this->trKey($ad)] ?? null;
            if (!$pid) { $eslesmeyen[$ad] = ($eslesmeyen[$ad] ?? 0) + 1; continue; }

            $adIds = DB::table('adisyonlar')->where('salon_id', $salonId)
                ->where('notlar', 'LIKE', '%[' . $markerAd . ':' . $anahtar . ']%')
                ->pluck('id');
            if ($adIds->isEmpty()) { $adisyonYok++; continue; }

            $kalemler = DB::table($tablo)->whereIn('adisyon_id', $adIds)->get(['id', 'personel_id']);
            if ($kalemler->isEmpty()) { $kalemYok++; continue; }

            foreach ($kalemler as $k) {
                if ($k->personel_id) { $zatenDolu++; continue; }
                $guncellenecek[$pid][] = $k->id;
            }
        }

        $doldurulacak = 0;
        foreach ($guncellenecek as $ids) $doldurulacak += count($ids);

        $this->line("  {$tablo}: doldurulacak {$doldurulacak} kalem"
            . " | zaten dolu {$zatenDolu} | adisyon bulunamadi {$adisyonYok} | kalem yok {$kalemYok}");

        foreach ($guncellenecek as $pid => $ids) {
            $ad = DB::table('salon_personelleri')->where('id', $pid)->value('personel_adi');
            $this->line(sprintf('    #%-6d %-28s -> %d kalem', $pid, mb_substr($ad, 0, 28), count($ids)));
        }

        if (!empty($eslesmeyen)) {
            $this->warn('  !! Sistemde karsiligi olmayan satici adlari (atlandi):');
            foreach ($eslesmeyen as $ad => $n) $this->warn("       {$ad}  ({$n} satis)");
            $this->warn('     Bu kisiler personel olarak eklenirse komut tekrar calistirilabilir.');
        }
        if (!empty($kasaAnahtar)) {
            $this->warn('  !! ' . count($kasaAnahtar) . ' satista Salonappy\'de satici "Kasa" — kaynakta kisi bilgisi YOK, atlandi.');
        }

        if ($uygula && $doldurulacak > 0) {
            foreach ($guncellenecek as $pid => $ids) {
                foreach (array_chunk($ids, 500) as $parca) {
                    DB::table($tablo)->whereIn('id', $parca)->whereNull('personel_id')
                        ->update(['personel_id' => $pid, 'updated_at' => date('Y-m-d H:i:s')]);
                }
            }
            $this->info("  -> {$doldurulacak} kalem guncellendi.");
        }

        return count($satirlar);
    }

    /** Turkce karakter / buyuk-kucuk duyarsiz karsilastirma anahtari. */
    private function trKey($s)
    {
        $s = mb_strtolower((string) $s, 'UTF-8');
        $s = preg_replace('/\p{M}+/u', '', $s);
        $s = strtr($s, ['ı'=>'i','İ'=>'i','ş'=>'s','Ş'=>'s','ğ'=>'g','Ğ'=>'g','ü'=>'u','Ü'=>'u','ö'=>'o','Ö'=>'o','ç'=>'c','Ç'=>'c']);
        $s = preg_replace('~[^a-z0-9]+~', ' ', $s);
        return trim($s);
    }
}
