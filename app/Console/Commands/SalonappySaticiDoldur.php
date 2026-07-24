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
        {--kasa-personel= : Salonappy\'de satici "Kasa" (seller_id=0) olan satislari bu personel ID\'sine yaz. Verilmezse atlanir.}
        {--yedek-esle : Adisyonda import marker\'i yoksa musteri+tarih+hizmet+seans+tutar ile esle (sadece TEK aday varsa yazar).}
        {--eksik-personel-ekle : Sistemde bulunmayan satici adlarini ARSIVLI personel olarak ac (aktif=0, arsivli=1).}
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

        // "Kasa" satislari icin hedef personel (opsiyonel)
        $kasaPersonelId = $this->option('kasa-personel') ? (int) $this->option('kasa-personel') : null;
        if ($kasaPersonelId) {
            $kp = DB::table('salon_personelleri')->where('id', $kasaPersonelId)
                ->where('salon_id', $salonId)->first();
            if (!$kp) { $this->error("--kasa-personel={$kasaPersonelId} bu salonda bulunamadi."); return 1; }
            $this->line('Kasa satislari su personele yazilacak: #' . $kp->id . ' ' . $kp->personel_adi);
        }

        $this->info('=== SALONAPPY SATICI DOLDUR — salon ' . $salonId
            . ' | mod: ' . ($uygula ? 'UYGULA (yazacak)' : 'DRY-RUN (yazmaz)') . ' ===');

        $yedek = (bool) $this->option('yedek-esle');
        if ($yedek) $this->line('Yedek eslesme AKTIF: marker\'i olmayan adisyonlarda musteri+tarih+hizmet+seans+tutar ile eslenecek.');
        $eksikEkle = (bool) $this->option('eksik-personel-ekle');
        if ($eksikEkle) $this->line('Eksik personel ekleme AKTIF: bulunamayan saticilar ARSIVLI personel olarak acilacak.');

        $toplam = 0;
        $toplam += $this->isle($salonId, $uygula, 'paket',
            $j['packageSales'] ?? [], 'salonappy-pkgsale', 'adisyon_hizmetler', 'seller_text', true, $kasaPersonelId, $yedek, $eksikEkle);
        $toplam += $this->isle($salonId, $uygula, 'urun',
            $j['productSales'] ?? [], 'salonappy-prodsale', 'adisyon_urunler', 'seller_name', false, $kasaPersonelId, $yedek, $eksikEkle);

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
     * @param  int|null $kasaPersonelId "Kasa" satislarinin yazilacagi personel (null = atla)
     * @param  bool   $yedek       marker yoksa musteri+tarih+hizmet ile esle
     * @param  bool   $eksikEkle   bulunamayan saticiyi arsivli personel olarak ac
     */
    private function isle($salonId, $uygula, $etiket, $satirlar, $markerAd, $tablo, $adKolonu, $grupla, $kasaPersonelId = null, $yedek = false, $eksikEkle = false)
    {
        if (empty($satirlar) || !\Schema::hasTable($tablo)) return 0;
        if (!\Schema::hasColumn($tablo, 'personel_id')) return 0;

        $this->line("\n[{$etiket}] dump satiri: " . count($satirlar));

        // marker anahtari -> satici adi (Kasa/bos olanlar ayri sayilir)
        // $satirHarita: marker anahtari -> dump satirlari (marker yoksa yedek eslesme icin)
        $saticiler = []; $kasaAnahtar = []; $satirHarita = [];
        foreach ($satirlar as $r) {
            $anahtar = (string) ($grupla ? ($r['group_id'] ?? $r['id'] ?? '') : ($r['id'] ?? ''));
            if ($anahtar === '') continue;
            $satirHarita[$anahtar][] = $r;
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

        // Islenecek hedefler: marker anahtari => personel_id
        $hedefler = [];
        $acilanPersonel = [];
        foreach ($saticiler as $anahtar => $ad) {
            $pid = $adHarita[$this->trKey($ad)] ?? null;
            if (!$pid && $eksikEkle) {
                if ($uygula) {
                    $pid = $this->arsivliPersonelAc($salonId, $ad);
                    if ($pid) {
                        $adHarita[$this->trKey($ad)] = $pid;
                        $acilanPersonel[$ad] = $pid;
                    }
                } else {
                    // DRY-RUN: kayit acilmaz, sadece raporlanir
                    $acilanPersonel[$ad] = null;
                }
            }
            if (!$pid) { $eslesmeyen[$ad] = ($eslesmeyen[$ad] ?? 0) + 1; continue; }
            $hedefler[$anahtar] = $pid;
        }
        if (!empty($acilanPersonel)) {
            $this->line('  ' . ($uygula ? 'Arsivli personel ACILDI' : 'Arsivli personel ACILACAK (dry-run)') . ': ' . count($acilanPersonel));
            foreach ($acilanPersonel as $ad => $pid) {
                $this->line('    ' . $ad . ($pid ? '  -> #' . $pid : ''));
            }
        }
        // --kasa-personel verildiyse "Kasa" satislari da o personele yazilir
        if ($kasaPersonelId) {
            foreach (array_keys($kasaAnahtar) as $anahtar) $hedefler[$anahtar] = $kasaPersonelId;
        }

        $yedekEslesen = 0;
        $sahiplenilen = [];   // ayni kalem iki satisa yazilmasin
        foreach ($hedefler as $anahtar => $pid) {
            $adIds = DB::table('adisyonlar')->where('salon_id', $salonId)
                ->where('notlar', 'LIKE', '%[' . $markerAd . ':' . $anahtar . ']%')
                ->pluck('id');

            if ($adIds->isEmpty()) {
                // Marker YOK (adisyon baska akistan girmis; notlar NULL olabilir).
                // Yedek eslesme: musteri + tarih + hizmet + seans + tutar ile bul.
                if ($yedek && $tablo === 'adisyon_hizmetler') {
                    foreach (($satirHarita[$anahtar] ?? []) as $r) {
                        $kid = $this->yedekKalemBul($salonId, $r);
                        if ($kid && !isset($sahiplenilen[$kid])) {
                            $sahiplenilen[$kid] = true;
                            $guncellenecek[$pid][] = $kid;
                            $yedekEslesen++;
                        }
                    }
                }
                $adisyonYok++;
                continue;
            }

            $kalemler = DB::table($tablo)->whereIn('adisyon_id', $adIds)->get(['id', 'personel_id']);
            if ($kalemler->isEmpty()) { $kalemYok++; continue; }

            foreach ($kalemler as $k) {
                if ($k->personel_id) { $zatenDolu++; continue; }
                if (isset($sahiplenilen[$k->id])) continue;
                $sahiplenilen[$k->id] = true;
                $guncellenecek[$pid][] = $k->id;
            }
        }

        $doldurulacak = 0;
        foreach ($guncellenecek as $ids) $doldurulacak += count($ids);

        $this->line("  {$tablo}: doldurulacak {$doldurulacak} kalem"
            . " | zaten dolu {$zatenDolu} | marker'li adisyon bulunamadi {$adisyonYok} | kalem yok {$kalemYok}");
        if ($yedek) {
            $this->line("  yedek eslesme ile bulunan: {$yedekEslesen} kalem"
                . ($adisyonYok > $yedekEslesen ? "  (kalan " . ($adisyonYok - $yedekEslesen) . " satis eslenemedi/belirsiz)" : ""));
        } elseif ($adisyonYok > 0 && $tablo === 'adisyon_hizmetler') {
            $this->warn("  !! {$adisyonYok} satista adisyonda import marker'i YOK — --yedek-esle ile musteri+tarih+hizmet uzerinden denenebilir.");
        }

        foreach ($guncellenecek as $pid => $ids) {
            $ad = DB::table('salon_personelleri')->where('id', $pid)->value('personel_adi');
            $this->line(sprintf('    #%-6d %-28s -> %d kalem', $pid, mb_substr($ad, 0, 28), count($ids)));
        }

        if (!empty($eslesmeyen)) {
            $this->warn('  !! Sistemde karsiligi olmayan satici adlari (atlandi):');
            foreach ($eslesmeyen as $ad => $n) $this->warn("       {$ad}  ({$n} satis)");
            $this->warn('     Bunlari arsivli personel olarak acmak icin: --eksik-personel-ekle');
        }
        if (!empty($kasaAnahtar)) {
            if ($kasaPersonelId) {
                $this->line('  * ' . count($kasaAnahtar) . ' satista satici "Kasa" — --kasa-personel=' . $kasaPersonelId . ' ile yazilacak.');
            } else {
                $this->warn('  !! ' . count($kasaAnahtar) . ' satista Salonappy\'de satici "Kasa" — kaynakta kisi bilgisi YOK, atlandi.');
                $this->warn('     Bunlari bir personele toplamak icin: --kasa-personel=<personel_id>');
            }
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

    /**
     * YEDEK ESLESME — adisyonda import marker'i yoksa (notlar NULL) kullanilir.
     * Dump satirini musteri + tarih + hizmet adi + seans sayisi + tutar ile
     * adisyon_hizmetler'de arar.
     *
     * GUVENLIK: yalnizca TEK aday kaldiginda id doner; belirsizlikte null doner
     * (yanlis kisiye prim yazmamak icin). Zaten personelli kalemler haric tutulur.
     */
    private function yedekKalemBul($salonId, $r)
    {
        $tarih = substr(trim((string) ($r['date'] ?? '')), 0, 10);
        $svc   = trim((string) ($r['service_text'] ?? ''));
        if ($tarih === '' || $svc === '') return null;

        $qty   = (int) ($r['quantity'] ?? 0);
        $tutar = (float) ($r['total_amount'] ?? 0);

        // Musteri: once telefon, sonra tam ad
        $userId = null;
        $tel = preg_replace('~\D~', '', (string) ($r['client_phone_number_local'] ?? $r['client_phone_number'] ?? ''));
        if ($tel !== '') $userId = DB::table('users')->where('cep_telefon', $tel)->value('id');
        if (!$userId) {
            $mad = trim((string) ($r['client_name'] ?? ''));
            if ($mad === '') return null;
            $userId = DB::table('users')->where('name', $mad)->value('id');
        }
        if (!$userId) return null;

        $q = DB::table('adisyon_hizmetler as ah')
            ->join('adisyonlar as a', 'a.id', '=', 'ah.adisyon_id')
            ->leftJoin('hizmetler as h', 'h.id', '=', 'ah.hizmet_id')
            ->where('a.salon_id', $salonId)
            ->where('a.user_id', $userId)
            ->whereNull('ah.personel_id')
            ->whereDate('a.tarih', $tarih);
        if ($qty > 0) $q->where('ah.seans_sayisi', $qty);
        else          $q->where('ah.seans_sayisi', '>', 0);

        $adaylar = $q->get(['ah.id', 'ah.fiyat', 'h.hizmet_adi']);
        if ($adaylar->isEmpty()) return null;

        // Hizmet adi ZORUNLU eslesir (Turkce duyarsiz) — tutmuyorsa eslesme yok.
        $svcKey  = $this->trKey($svc);
        $adaylar = $adaylar->filter(fn($x) => $this->trKey($x->hizmet_adi) === $svcKey)->values();
        if ($adaylar->isEmpty()) return null;

        // Birden fazla aday varsa tutar ile daralt
        if ($adaylar->count() > 1 && $tutar > 0) {
            $adaylar = $adaylar->filter(fn($x) => abs((float) $x->fiyat - $tutar) < 1)->values();
        }

        return $adaylar->count() === 1 ? (int) $adaylar->first()->id : null;
    }

    /**
     * Sistemde olmayan saticiyi ARSIVLI personel olarak acar.
     * aktif=0, arsivli=1, takvimde_gorunsun=0 — takvimde/aktif listelerde
     * gorunmez ve prim raporuna girmez; sadece satisin sahibi kaybolmaz.
     * "Kasa" gibi kisi olmayan degerlerde kayit ACMAZ.
     */
    private function arsivliPersonelAc($salonId, $ad)
    {
        $ad = trim((string) $ad);
        if ($ad === '' || $this->trKey($ad) === 'kasa') return null;
        try {
            $yetkili = new \App\IsletmeYetkilileri();
            $yetkili->name = $ad;
            $yetkili->save();

            $p = new \App\Personeller();
            $p->personel_adi = $ad;
            $p->salon_id     = $salonId;
            $p->aktif        = false;
            $p->yetkili_id   = $yetkili->id;
            if (\Schema::hasColumn('salon_personelleri', 'arsivli'))           $p->arsivli = 1;
            if (\Schema::hasColumn('salon_personelleri', 'takvimde_gorunsun')) $p->takvimde_gorunsun = 0;
            $p->save();
            return (int) $p->id;
        } catch (\Throwable $e) {
            $this->warn('    Personel acilamadi: ' . $ad . ' — ' . $e->getMessage());
            return null;
        }
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
