<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Salonappy ZIYARET dump'indaki package_usages[] bilgisinden paket SEANS
 * DUSUMLERINI (adisyon_paket_seanslar) gunceller.
 *
 * NEDEN AYRI KOMUT: salonappy:import paket satisini dedup ile ATLADIGI icin
 * (notlar marker'i varsa continue) mevcut kayitlarda dusum guncellemez. Ayrica
 * marker'i olmayan (notlar NULL) adisyonlar import'un usage eslesmesine hic
 * girmez — bu komut --yedek-esle ile onlari da kapsar.
 *
 * NE YAPAR : tarihsiz (seans_tarih NULL) APS satirlarini kaynaktaki kullanim
 *            tarihiyle doldurur ve geldi=1 yapar.
 * NE YAPMAZ: seans SILMEZ, isaretli dusumu geri ALMAZ (bizdeki sayi kaynaktan
 *            fazlaysa dokunmaz), seans_sayisi kapasitesini ASMAZ.
 *            Yeni APS satiri eklemek opsiyoneldir (--eksik-aps-ekle).
 *
 * IDEMPOTENT: (paket kalemi + tarih) basina kaynaktaki toplam adet ile bizdeki
 * mevcut adet karsilastirilir; sadece EKSIK kadar satir doldurulur. Komutu
 * tekrar calistirmak fazladan dusum yazmaz.
 */
class SalonappyDusumGuncelle extends Command
{
    protected $signature = 'salonappy:dusum-guncelle
        {--salon= : Salon (isletme) ID — zorunlu}
        {--dump-file= : Ziyaret (visits) dump JSON yolu — package_usages icermeli}
        {--yedek-esle : Marker\'i olmayan adisyonlardaki paketleri de kapsa (musteri+hizmet ile)}
        {--eksik-aps-ekle : Bos APS satiri kalmadiysa kapasite icinde yeni APS EKLE (varsayilan: ekleme)}
        {--uygula : Gercekten yaz (verilmezse sadece rapor / dry-run)}';

    protected $description = 'Salonappy visits package_usages -> paket seans dusumlerini gunceller (sadece eksigi tamamlar).';

    public function handle()
    {
        $salonId = (int) $this->option('salon');
        $dosya   = $this->option('dump-file');
        if (!$salonId) { $this->error('--salon zorunlu'); return 1; }
        if (!$dosya || !file_exists($dosya)) { $this->error('--dump-file bulunamadi: ' . $dosya); return 1; }

        $uygula   = (bool) $this->option('uygula');
        $yedek    = (bool) $this->option('yedek-esle');
        $apsEkle  = (bool) $this->option('eksik-aps-ekle');

        $j = json_decode(file_get_contents($dosya), true);
        if (!is_array($j)) { $this->error('Dump okunamadi (gecersiz JSON).'); return 1; }

        $this->info('=== SALONAPPY DUSUM GUNCELLE — salon ' . $salonId
            . ' | mod: ' . ($uygula ? 'UYGULA (yazacak)' : 'DRY-RUN (yazmaz)') . ' ===');
        if ($yedek)   $this->line('Yedek eslesme AKTIF: marker\'siz adisyonlardaki paketler de kapsanacak.');
        if ($apsEkle) $this->line('Eksik APS ekleme AKTIF: bos satir kalmazsa kapasite icinde yeni APS eklenecek.');

        // 1) Dump'tan kullanimlari topla
        $bd = $j['bookingDetails'] ?? [];
        if (empty($bd)) { $this->error('Dump\'ta bookingDetails yok — ziyaret (visits) dump\'i vermelisin.'); return 1; }

        $ham = 0;
        $kullanimlar = [];   // "userId|hizmetId|tarih" => adet
        $musteriYok = 0; $hizmetYok = 0;

        foreach ($bd as $obj) {
            $det = $obj['details'] ?? [];
            $pus = $obj['package_usages'] ?? [];
            if (empty($pus)) continue;

            $userId = $this->musteriBul($det);
            foreach ($pus as $pu) {
                $ham++;
                $svc   = trim((string) ($pu['service_text'] ?? ''));
                $tarih = substr(trim((string) ($pu['date'] ?? '')), 0, 10);
                $adet  = max(1, (int) ($pu['quantity'] ?? 1));
                if ($svc === '' || $tarih === '') continue;
                if (!$userId) { $musteriYok++; continue; }

                $hid = $this->hizmetBul($salonId, $svc);
                if (!$hid) { $hizmetYok++; continue; }

                $k = $userId . '|' . $hid . '|' . $tarih;
                $kullanimlar[$k] = ($kullanimlar[$k] ?? 0) + $adet;
            }
        }

        $this->line("\nDump'taki kullanim satiri: {$ham}  ->  gruplanmis (musteri+hizmet+tarih): " . count($kullanimlar));
        if ($musteriYok) $this->warn("  !! {$musteriYok} kullanimda musteri eslesmedi (atlandi).");
        if ($hizmetYok)  $this->warn("  !! {$hizmetYok} kullanimda hizmet sistemde bulunamadi (atlandi).");

        // 2) Her kullanim grubu icin eksik dusumu tamamla
        $guncellenecek = [];   // seans_tarih => [aps id, ...]
        $eklenecek = [];       // [ah_id, hizmet_id, tarih, adet ]
        $zatenTam = 0; $paketYok = 0; $bosSatirYok = 0; $fazlaVar = 0;
        $toplamEksik = 0;

        foreach ($kullanimlar as $k => $kaynakAdet) {
            list($userId, $hid, $tarih) = explode('|', $k);

            $ahIds = $this->paketKalemleriniBul($salonId, (int) $userId, (int) $hid, $yedek);
            if (empty($ahIds)) { $paketYok++; continue; }

            // Bu tarihte bizde kayitli dusum adedi (idempotency anahtari)
            $mevcut = DB::table('adisyon_paket_seanslar')
                ->whereIn('adisyon_hizmet_id', $ahIds)
                ->whereDate('seans_tarih', $tarih)
                ->count();

            if ($mevcut >= $kaynakAdet) {
                if ($mevcut > $kaynakAdet) $fazlaVar++;   // bizde fazla — DOKUNMA
                $zatenTam++;
                continue;
            }
            $eksik = $kaynakAdet - $mevcut;
            $toplamEksik += $eksik;

            // Tarihsiz (bos) APS satirlarini sirayla doldur
            foreach ($ahIds as $ahId) {
                if ($eksik <= 0) break;
                $boslar = DB::table('adisyon_paket_seanslar')
                    ->where('adisyon_hizmet_id', $ahId)
                    ->whereNull('seans_tarih')
                    ->orderBy('seans_no')->limit($eksik)->pluck('id')->all();
                if (!empty($boslar)) {
                    foreach ($boslar as $apsId) $guncellenecek[$tarih][] = $apsId;
                    $eksik -= count($boslar);
                }
            }

            // Hala eksik varsa: kapasite icinde yeni APS (opsiyonel)
            if ($eksik > 0) {
                if (!$apsEkle) { $bosSatirYok++; continue; }
                foreach ($ahIds as $ahId) {
                    if ($eksik <= 0) break;
                    $seansSayisi = (int) DB::table('adisyon_hizmetler')->where('id', $ahId)->value('seans_sayisi');
                    $mevcutAps   = DB::table('adisyon_paket_seanslar')->where('adisyon_hizmet_id', $ahId)->count();
                    $kapasite    = max(0, $seansSayisi - $mevcutAps);
                    if ($kapasite <= 0) continue;
                    $ekle = min($eksik, $kapasite);
                    $eklenecek[] = [$ahId, (int) $hid, $tarih, $ekle, $mevcutAps];
                    $eksik -= $ekle;
                }
                if ($eksik > 0) $bosSatirYok++;
            }
        }

        $guncelSayi = 0;
        foreach ($guncellenecek as $ids) $guncelSayi += count($ids);
        $ekleSayi = 0;
        foreach ($eklenecek as $e) $ekleSayi += $e[3];

        $this->line("\n--- SONUC ---");
        $this->line("  Kaynakta eksik gorunen dusum      : {$toplamEksik} seans");
        $this->line("  Bos APS satiri doldurulacak       : {$guncelSayi}");
        $this->line("  Yeni APS eklenecek                : {$ekleSayi}" . ($apsEkle ? '' : '  (--eksik-aps-ekle kapali)'));
        $this->line("  Zaten tam (dokunulmadi)           : {$zatenTam}");
        if ($fazlaVar)    $this->line("  Bizde kaynaktan FAZLA (korundu)   : {$fazlaVar} grup");
        if ($paketYok)    $this->warn("  !! Paket kalemi bulunamadi        : {$paketYok} grup" . ($yedek ? '' : '  -> --yedek-esle deneyin'));
        if ($bosSatirYok) $this->warn("  !! Bos APS satiri/kapasite yok    : {$bosSatirYok} grup" . ($apsEkle ? '' : '  -> --eksik-aps-ekle deneyin'));

        if (!$uygula) {
            $this->comment("\nDRY-RUN — hicbir sey yazilmadi. Uygulamak icin --uygula ekle.");
            return 0;
        }

        // 3) Yaz
        $simdi = date('Y-m-d H:i:s');
        foreach ($guncellenecek as $tarih => $ids) {
            foreach (array_chunk($ids, 500) as $parca) {
                DB::table('adisyon_paket_seanslar')->whereIn('id', $parca)
                    ->update(['seans_tarih' => $tarih, 'geldi' => 1, 'updated_at' => $simdi]);
            }
        }
        foreach ($eklenecek as $e) {
            list($ahId, $hid, $tarih, $adet, $mevcutAps) = $e;
            for ($i = 1; $i <= $adet; $i++) {
                DB::table('adisyon_paket_seanslar')->insert([
                    'adisyon_hizmet_id' => $ahId,
                    'hizmet_id'         => $hid,
                    'seans_no'          => $mevcutAps + $i,
                    'geldi'             => 1,
                    'seans_tarih'       => $tarih,
                    'created_at'        => $simdi,
                    'updated_at'        => $simdi,
                ]);
            }
        }
        $this->info("  -> {$guncelSayi} APS guncellendi, {$ekleSayi} APS eklendi.");
        return 0;
    }

    /** Ziyaret details'inden musteri (users.id) — once telefon, sonra ad. */
    private function musteriBul($det)
    {
        if (!is_array($det)) return null;
        static $cache = [];
        $tel = preg_replace('~\D~', '', (string) ($det['client_phone_number'] ?? ''));
        $ad  = trim((string) ($det['client_name'] ?? ''));
        $ck  = $tel . '|' . $ad;
        if (array_key_exists($ck, $cache)) return $cache[$ck];

        $id = null;
        if ($tel !== '') $id = DB::table('users')->where('cep_telefon', $tel)->value('id');
        if (!$id && $ad !== '') $id = DB::table('users')->where('name', $ad)->value('id');
        return $cache[$ck] = ($id ? (int) $id : null);
    }

    /** Hizmet adindan hizmet_id — MEVCUT hizmeti bulur, YENI OLUSTURMAZ. */
    private function hizmetBul($salonId, $ad)
    {
        static $salonMap = null, $globalMap = null;
        $needle = $this->trKey($ad);

        if ($salonMap === null) {
            $salonMap = [];
            $rows = DB::table('salon_sunulan_hizmetler as sh')
                ->join('hizmetler as h', 'sh.hizmet_id', '=', 'h.id')
                ->where('sh.salon_id', $salonId)
                ->select('h.id', 'h.hizmet_adi')->get();
            foreach ($rows as $h) $salonMap[$this->trKey($h->hizmet_adi)] = (int) $h->id;
        }
        if (isset($salonMap[$needle])) return $salonMap[$needle];

        if ($globalMap === null) {
            $globalMap = [];
            foreach (DB::table('hizmetler')->select('id', 'hizmet_adi')->get() as $h) {
                $k = $this->trKey($h->hizmet_adi);
                if (!isset($globalMap[$k])) $globalMap[$k] = (int) $h->id;
            }
        }
        return $globalMap[$needle] ?? null;
    }

    /**
     * Musterinin bu hizmete ait paket kalemleri (adisyon_hizmetler.id), adisyon
     * tarihine gore sirali. $yedek=false ise sadece [salonappy-pkgsale:] marker'li
     * adisyonlar; true ise marker sarti YOK (marker'siz kayitlar da kapsanir).
     */
    private function paketKalemleriniBul($salonId, $userId, $hizmetId, $yedek)
    {
        $q = DB::table('adisyon_hizmetler as ah')
            ->join('adisyonlar as a', 'a.id', '=', 'ah.adisyon_id')
            ->where('a.salon_id', $salonId)
            ->where('a.user_id', $userId)
            ->where('ah.hizmet_id', $hizmetId)
            ->where('ah.seans_sayisi', '>', 0);
        if (!$yedek) $q->where('a.notlar', 'LIKE', '%[salonappy-pkgsale:%');
        return $q->orderBy('a.tarih')->pluck('ah.id')->all();
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
