<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * PATRON ASISTANI — OTOMATIK KAMPANYA motoru.
 *
 * 4 akis: kayip musteri geri kazanim, bos saat doldurma, dogum gunu, genel kampanya.
 * GUVENLIK: "sor" ucundan yalniz TEKLIF (salt-okunur, sayi + onizleme) doner;
 * GERCEK gonderim yalniz patron ONAYLADIKTAN sonra "uygula" ucundan yapilir.
 *
 * Mevcut altyapiyi yeniden kullanir: CarkifelekOdulleri (kupon), sms_gonder (SMS),
 * musteri_portfoy/users/randevular (segment).
 */
class PatronAsistanKampanyaServisi
{
    /** Guvenlik: tek kampanyada en fazla alici (timeout + maliyet siniri). */
    const MAX_ALICI = 500;

    /** Kampanya turleri: varsayilan indirim, baslik, mesaj sablonu. */
    protected function turler()
    {
        return [
            'kayip' => [
                'ad'      => 'Kayıp Müşteri Geri Kazanım',
                'oran'    => 20,
                'baslik'  => 'Sizi Özledik İndirimi',
                'sablon'  => 'Merhaba! Sizi özledik. {salon} olarak size özel %{oran} indirim tanımladık. Kupon kodunuz: {kod}. Sizi tekrar aramızda görmek isteriz.',
            ],
            'bossaat' => [
                'ad'      => 'Boş Saat Doldurma',
                'oran'    => 25,
                'baslik'  => 'Sakin Saat Fırsatı',
                'sablon'  => '{salon}\'dan fırsat! Sakin saatlerimizde %{oran} indirim sizi bekliyor. Kupon kodunuz: {kod}. Randevu için bekleriz.',
            ],
            'dogumgunu' => [
                'ad'      => 'Doğum Günü Kutlaması',
                'oran'    => 25,
                'baslik'  => 'Doğum Günü Hediyesi',
                'sablon'  => 'İyi ki doğdunuz! {salon} olarak doğum gününüzü %{oran} indirimle kutluyoruz. Kupon kodunuz: {kod}. Nice mutlu yıllara!',
            ],
            'genel' => [
                'ad'      => 'Genel Kampanya',
                'oran'    => 15,
                'baslik'  => 'Size Özel Kampanya',
                'sablon'  => '{salon}\'dan size özel kampanya! %{oran} indirim fırsatını kaçırmayın. Kupon kodunuz: {kod}.',
            ],
        ];
    }

    /**
     * Mesaj bir kampanya OLUSTURMA/GONDERME istegi mi? Hangi tur?
     * @return string|null 'kayip' | 'bossaat' | 'dogumgunu' | 'genel' | null
     */
    public function kampanyaTetik($metin)
    {
        $n = ' ' . $this->normalize($metin) . ' ';

        // Bir "gonderme/yapma" niyeti olmali (yoksa danisma/soru olabilir).
        $eylem = false;
        foreach (['gonder', 'yap', 'olustur', 'hazirla', 'baslat', 'duzenle bir', 'kampanya', 'indirim'] as $e) {
            if (strpos($n, ' ' . $e) !== false || strpos($n, $e . ' ') !== false) { $eylem = true; break; }
        }
        if (!$eylem) {
            return null;
        }

        if (strpos($n, 'kayip') !== false || strpos($n, 'gelmeyen') !== false
            || strpos($n, 'geri kazan') !== false || strpos($n, 'ozledik') !== false
            || strpos($n, 'uzun sure gelmeyen') !== false) {
            return 'kayip';
        }
        if (strpos($n, 'bos saat') !== false || strpos($n, 'olu saat') !== false
            || strpos($n, 'sakin saat') !== false || strpos($n, 'saat doldur') !== false
            || strpos($n, 'bossaat') !== false) {
            return 'bossaat';
        }
        if (strpos($n, 'dogum gun') !== false || strpos($n, 'dogumgun') !== false) {
            return 'dogumgunu';
        }
        // "kampanya gonder / indirim gonder / herkese / tum musteriler" -> genel
        if (strpos($n, 'kampanya') !== false || strpos($n, 'indirim') !== false
            || strpos($n, 'herkese') !== false || strpos($n, 'tum musteri') !== false
            || strpos($n, 'duyuru') !== false) {
            return 'genel';
        }
        return null;
    }

    /**
     * TEK GIRIS: mesaj bir kampanya/reklam istegi mi? Ise uygun TEKLIFI dondurur:
     *   - Mesajda bir MUSTERI ADI geciyorsa  -> SADECE o kisiye (kisi segmenti).
     *   - Tur belliyse (kayip/bossaat/dogumgunu/genel) -> segment/genel kampanya.
     *   - Ne kisi ne tur belliyse -> "kime gonderelim?" diye NETLESTIR (yanlislikla
     *     herkese gitmesin). Kampanya baglami yoksa null (controller devam eder).
     */
    public function coz($metin, $salonId)
    {
        $n = ' ' . $this->normalize($metin) . ' ';
        $bekleAnahtar = 'patron_asistan_kamp_bekle:' . $salonId;
        $bekliyor = false;
        try { $bekliyor = (bool) \Cache::get($bekleAnahtar); } catch (\Throwable $e) {}

        // Kampanya baglami yok VE onceki turda "kime gonderelim?" de sormadiysak -> bizim
        // isimiz degil (controller devam etsin). Bekleme varsa bu mesaji cevap say.
        $baglam = $this->kampanyaBaglamiMi($n);
        if (!$baglam && !$bekliyor) {
            return null;
        }

        $salonAdi = \App\Salonlar::where('id', $salonId)->value('salon_adi');
        $tip = $this->kampanyaTuru($n); // null olabilir

        // 1) Mesajda bir musteri adi geciyorsa -> SADECE o kisiye.
        $kisi = $this->mesajdanMusteri($n, $salonId);
        if (is_object($kisi)) {
            $this->beklemeTemizle($bekleAnahtar);
            return $this->kisiyeTeklif($tip ?: 'genel', $kisi, $salonAdi);
        }
        if (is_array($kisi) && isset($kisi['coklu'])) {
            $this->beklemeKur($bekleAnahtar); // hala kisi bekliyoruz
            $adlar = implode(', ', array_slice($kisi['adlar'], 0, 5));
            return [
                'basarili' => true, 'intent' => 'kampanya_soru', 'seslendir' => true, 'kart' => null,
                'cevap' => 'Birden fazla müşteri eşleşti: ' . $adlar
                         . '. Hangisine göndereyim, tam adıyla söyler misiniz?',
            ];
        }

        // 2) Tur belliyse segment/genel kampanya teklifi.
        if ($tip !== null) {
            $this->beklemeTemizle($bekleAnahtar);
            return $this->teklif($tip, $salonId, $salonAdi);
        }

        // 3) Ne kisi ne tur belli.
        if ($baglam) {
            // Ilk kez soruyoruz: "kime?" -> bekleme kur (sonraki mesaj cevap say).
            $this->beklemeKur($bekleAnahtar);
            return [
                'basarili' => true, 'intent' => 'kampanya_soru', 'seslendir' => true, 'kart' => null,
                'cevap' => 'Kampanyayı kime göndereyim? Örneğin kayıp müşterilere kampanya gönder, '
                         . 'boş saatler için kampanya gönder, doğum günü olanlara gönder, tüm müşterilere '
                         . 'gönder ya da bir müşterinin adını söyleyerek sadece ona gönder diyebilirsiniz.',
            ];
        }

        // Bekliyorduk ama bu mesaj kampanya cozmedi -> birak, normal akis islesin.
        $this->beklemeTemizle($bekleAnahtar);
        return null;
    }

    /** "Kampanya hedefi bekleniyor" durumunu kur (kisa omurlu: 3 dakika). */
    protected function beklemeKur($anahtar)
    {
        try { \Cache::put($anahtar, 1, 3); } catch (\Throwable $e) {} // L5.6: 3 = dakika
    }

    /** Bekleme durumunu temizle. */
    protected function beklemeTemizle($anahtar)
    {
        try { \Cache::forget($anahtar); } catch (\Throwable $e) {}
    }

    /** Mesaj bir kampanya/reklam OLUSTURMA-GONDERME baglami tasiyor mu? */
    protected function kampanyaBaglamiMi($n)
    {
        $noun = strpos($n, 'reklam') !== false || strpos($n, 'kampanya') !== false
             || strpos($n, 'indirim') !== false || strpos($n, 'duyuru') !== false;
        $eylem = false;
        foreach (['gonder', 'yolla', 'olustur', 'hazirla', 'baslat', 'yap', 'duzenle'] as $a) {
            if (strpos($n, $a) !== false) { $eylem = true; break; }
        }
        $test = strpos($n, 'test') !== false || strpos($n, 'deneme') !== false;
        // Kampanya/reklam ismi var; ya da "test ... gonder" gibi eylemli test ifadesi.
        return $noun || ($test && $eylem);
    }

    /** Kampanya turunu belirler; hicbir tur anahtari yoksa null (varsayilan YOK). */
    protected function kampanyaTuru($n)
    {
        if (strpos($n, 'kayip') !== false || strpos($n, 'gelmeyen') !== false
            || strpos($n, 'geri kazan') !== false || strpos($n, 'ozledik') !== false
            || strpos($n, 'uzun sure gelmeyen') !== false) {
            return 'kayip';
        }
        if (strpos($n, 'bos saat') !== false || strpos($n, 'olu saat') !== false
            || strpos($n, 'sakin saat') !== false || strpos($n, 'saat doldur') !== false
            || strpos($n, 'bossaat') !== false) {
            return 'bossaat';
        }
        if (strpos($n, 'dogum gun') !== false || strpos($n, 'dogumgun') !== false) {
            return 'dogumgunu';
        }
        if (strpos($n, 'genel') !== false || strpos($n, 'herkese') !== false
            || strpos($n, 'tum musteri') !== false || strpos($n, 'butun musteri') !== false) {
            return 'genel';
        }
        return null;
    }

    /**
     * Mesajda gecen bir portfoy musterisini bulur (adin TUM kelimeleri mesajda gecmeli;
     * guclu eslesme: 2 kelimeli ad ya da tek kelime >=4 harf). Junk kelimelerden etkilenmez.
     * @return object|array|null tek=object, coklu=['coklu'=>true,'adlar'=>[]], yok=null
     */
    protected function mesajdanMusteri($n, $salonId)
    {
        $mesajKel = [];
        foreach (preg_split('/\s+/', trim($n)) as $k) {
            $k = $this->kelimeTemizle($k);
            if ($k !== '' && mb_strlen($k) >= 3) $mesajKel[] = $k;
        }
        if (empty($mesajKel)) return null;

        $rows = DB::table('musteri_portfoy as mp')
            ->join('users as u', 'u.id', '=', 'mp.user_id')
            ->where('mp.salon_id', $salonId)
            ->where('mp.aktif', 1)
            ->select('u.id', 'u.name', 'u.cep_telefon')
            ->limit(5000)->get();

        $eslesme = [];
        foreach ($rows as $r) {
            $adKel = array_values(array_filter(
                preg_split('/\s+/', $this->normalize((string) $r->name)),
                function ($w) { return mb_strlen($w) >= 2; }
            ));
            if (empty($adKel)) continue;

            $tumu = true; $eslesenAd = 0;
            foreach ($adKel as $aw) {
                $bulundu = false;
                foreach ($mesajKel as $mw) {
                    if ($aw === $mw) { $bulundu = true; break; }
                    $kisa = mb_strlen($mw) <= mb_strlen($aw) ? $mw : $aw;
                    $uzun = ($kisa === $mw) ? $aw : $mw;
                    if (mb_strlen($kisa) >= 3 && strpos($uzun, $kisa) === 0) { $bulundu = true; break; }
                }
                if ($bulundu) $eslesenAd++; else { $tumu = false; break; }
            }
            // Guclu eslesme: adin butun kelimeleri gecmis + (>=2 kelime ya da tek kelime >=4).
            if ($tumu && (count($adKel) >= 2 || ($eslesenAd === 1 && mb_strlen($adKel[0]) >= 4))) {
                $eslesme[] = $r;
                if (count($eslesme) > 6) break;
            }
        }

        if (empty($eslesme)) return null;
        if (count($eslesme) === 1) return $eslesme[0];
        return ['coklu' => true, 'adlar' => array_map(function ($r) { return $r->name; }, $eslesme)];
    }

    /** Tek kisiye ozel gonderim teklifi (kisi segmenti; onay -> test|tip|id kodlamasi). */
    protected function kisiyeTeklif($tip, $kisi, $salonAdi)
    {
        $turler = $this->turler();
        if (!isset($turler[$tip])) $tip = 'genel';
        $t = $turler[$tip];
        $oran = $t['oran'];

        $cevap = 'Hazırım. Bu kampanyayı SADECE ' . $kisi->name . ' adlı müşteriye yüzde ' . $oran
               . ' indirim bildirimi olarak göndereceğim. Diğer hiçbir müşteriye gitmeyecek. Onaylıyor musunuz?';

        return [
            'basarili'  => true, 'intent' => 'kampanya_teklif', 'seslendir' => true, 'kart' => null,
            'cevap'     => $cevap,
            'aksiyon'   => [
                'tur'        => 'kampanya',
                'tip'        => 'test|' . $tip . '|' . (int) $kisi->id, // uygula -> kisi segmenti
                'oran'       => $oran,
                'baslik'     => 'Kişiye Özel · ' . $t['ad'],
                'hedef_sayi' => 1,
                'onay_metni' => 'Sadece ' . $kisi->name . ' kişisine gönder',
            ],
        ];
    }

    /**
     * GUVENLI TEST istegi mi? "test icin Ahmet'e kampanya gonder" gibi.
     * @return array|null ['tip' => <kampanya turu>, 'isim' => <cikarilan kisi adi>]
     */
    public function testTetik($metin)
    {
        $n = ' ' . $this->normalize($metin) . ' ';
        // "test" / "deneme" gecmezse test degil.
        if (strpos($n, 'test') === false && strpos($n, 'deneme') === false) {
            return null;
        }
        // Kampanya/gonderim baglami olmali (yoksa "kac test yapildi" gibi alakasiz olabilir).
        $baglam = false;
        foreach (['kampanya', 'indirim', 'bildirim', 'gonder', 'yolla'] as $k) {
            if (strpos($n, $k) !== false) { $baglam = true; break; }
        }
        if (!$baglam) {
            return null;
        }
        // Kampanya turu (varsa anahtar kelimeden, yoksa genel). Test'te segment zaten
        // 'kisi'ye zorlanir; tur yalniz mesaj sablonu/oranini belirler.
        $tip = $this->kampanyaTetik($metin);
        if (!$tip) $tip = 'genel';

        return ['tip' => $tip, 'isim' => $this->isimAyikla($n)];
    }

    /** Mesajdan komut kelimelerini atip geriye kalan (muhtemel kisi adi) metni doner. */
    protected function isimAyikla($n)
    {
        $stop = [
            'test', 'deneme', 'icin', 'kampanya', 'kampanyayi', 'kampanyasi', 'kampanyasini',
            'indirim', 'indirimi', 'bildirim', 'bildirimi', 'reklam', 'reklami',
            'gonder', 'gonderelim', 'gonderin', 'gondersene', 'yolla', 'yollayalim',
            'sadece', 'yalniz', 'yalnizca', 'kisiye', 'kisi', 'musteri', 'musteriye',
            'musteriler', 'musterilere', 'patron', 'asistan', 'olustur', 'hazirla', 'yap',
            'bir', 'bu', 'su', 've', 'ile', 'lutfen', 'nin', 'nun', 'adli', 'adinda', 'isimli',
        ];
        $out = [];
        foreach (preg_split('/\s+/', trim($n)) as $kel) {
            $kel = $this->kelimeTemizle($kel); // kesme/ek ve noktalama temizligi
            if ($kel === '' || mb_strlen($kel) < 2) continue;
            if (in_array($kel, $stop, true)) continue;
            $out[] = $kel;
        }
        return trim(implode(' ', $out));
    }

    /** Bir kelimeden kesme isareti+sonrasini ve harf/rakam disi karakterleri atar. */
    protected function kelimeTemizle($kel)
    {
        $kel = trim((string) $kel);
        // "orbey'e" -> "orbey" (kesme ve sonrasi at)
        $kel = preg_replace("/['’`].*$/u", '', $kel);
        // normalize sonrasi ascii bekleniyor; kalan noktalama vs. at
        $kel = preg_replace('/[^a-z0-9]/u', '', $kel);
        return (string) $kel;
    }

    /**
     * TEST TEKLIFI: adi verilen tek musteriyi bulur, SADECE ona gidecek gonderimi teklif eder.
     * Onay aksiyonunda tip = "test|<tur>|<user_id>" -> uygula ucu kisi segmentine zorlar.
     */
    public function testTeklif($tip, $isim, $salonId, $salonAdi)
    {
        $turler = $this->turler();
        if (!isset($turler[$tip])) $tip = 'genel';
        $t = $turler[$tip];

        if ($isim === '') {
            return [
                'basarili' => true, 'intent' => 'kampanya_test_soru', 'seslendir' => true, 'kart' => null,
                'cevap' => 'Testi hangi müşteriye göndereyim? Lütfen müşterinin adını söyleyin.',
            ];
        }

        $kisi = $this->isimdenMusteri($isim, $salonId);
        if ($kisi === null) {
            return [
                'basarili' => true, 'intent' => 'kampanya_test_soru', 'seslendir' => true, 'kart' => null,
                'cevap' => $isim . ' adında bir müşteri bulamadım. Adını tam ve doğru söyleyebilir misiniz?',
            ];
        }
        if (is_array($kisi) && isset($kisi['coklu'])) {
            $adlar = implode(', ', array_slice($kisi['adlar'], 0, 5));
            return [
                'basarili' => true, 'intent' => 'kampanya_test_soru', 'seslendir' => true, 'kart' => null,
                'cevap' => 'Birden fazla müşteri eşleşti: ' . $adlar
                         . '. Hangisi olduğunu tam adıyla belirtir misiniz?',
            ];
        }

        $oran = $t['oran'];
        $cevap = 'Test göndermeye hazırım. Bu kampanyayı SADECE ' . $kisi->name
               . ' adlı müşteriye yüzde ' . $oran . ' indirim bildirimi olarak göndereceğim. '
               . 'Diğer hiçbir müşteriye gitmeyecek. Onaylıyor musunuz?';

        return [
            'basarili'  => true,
            'intent'    => 'kampanya_teklif',
            'seslendir' => true,
            'cevap'     => $cevap,
            'kart'      => null,
            'aksiyon'   => [
                'tur'        => 'kampanya',
                'tip'        => 'test|' . $tip . '|' . (int) $kisi->id, // uygula bunu cozer
                'oran'       => $oran,
                'baslik'     => 'Test: ' . $t['ad'],
                'hedef_sayi' => 1,
                'onay_metni' => 'Sadece ' . $kisi->name . ' kişisine test gönder',
            ],
        ];
    }

    /**
     * Ada gore portfoy musterisi bul (Turkce karakter/collation guvenli: PHP tarafinda normalize).
     * @return object|array|null tek eslesme=object, coklu=['coklu'=>true,'adlar'=>[]], yok=null
     */
    protected function isimdenMusteri($isim, $salonId)
    {
        $kelimeler = array_values(array_filter(preg_split('/\s+/', trim($this->normalize($isim)))));
        if (empty($kelimeler)) return null;

        $rows = DB::table('musteri_portfoy as mp')
            ->join('users as u', 'u.id', '=', 'mp.user_id')
            ->where('mp.salon_id', $salonId)
            ->where('mp.aktif', 1)
            ->select('u.id', 'u.name', 'u.cep_telefon')
            ->limit(5000)->get();

        $eslesme = [];
        foreach ($rows as $r) {
            if ($this->adEslesir((string) $r->name, $kelimeler)) {
                $eslesme[] = $r;
                if (count($eslesme) > 6) break;
            }
        }

        if (empty($eslesme)) return null;
        if (count($eslesme) === 1) return $eslesme[0];
        return ['coklu' => true, 'adlar' => array_map(function ($r) { return $r->name; }, $eslesme)];
    }

    /**
     * Verilen aranan kelimelerin HEPSI, musteri adindaki bir kelimeyle onek-eslesir mi?
     * Onek/iki yonlu prefix: "orbeye"/"orbey" ve "orbey"/"orbey" eslesir (ek toleransi).
     */
    protected function adEslesir($ad, array $kelimeler)
    {
        $adKelime = array_values(array_filter(preg_split('/\s+/', $this->normalize($ad))));
        foreach ($kelimeler as $kel) {
            $kel = $this->kelimeTemizle($kel);
            if ($kel === '') continue;
            $bulundu = false;
            foreach ($adKelime as $aw) {
                if ($aw === $kel) { $bulundu = true; break; }
                $kisa = mb_strlen($kel) <= mb_strlen($aw) ? $kel : $aw;
                $uzun = ($kisa === $kel) ? $aw : $kel;
                if (mb_strlen($kisa) >= 3 && strpos($uzun, $kisa) === 0) { $bulundu = true; break; }
            }
            if (!$bulundu) return false;
        }
        return true;
    }

    /** Belirtilen turun hedef musteri kitlesi (salt-okunur sorgu). */
    public function hedefKitle($tip, $salonId)
    {
        switch ($tip) {
            case 'kayip':     return $this->gelmeyenMusteriler($salonId, 30, self::MAX_ALICI);
            case 'dogumgunu': return $this->dogumGunuMusteriler($salonId, self::MAX_ALICI);
            case 'bossaat':
            case 'genel':
            default:          return $this->tumMusteriler($salonId, self::MAX_ALICI);
        }
    }

    /**
     * TEKLIF (salt-okunur): kaç kişi, hangi indirim, örnek mesaj, tahmini kontör.
     * Yaninda 'aksiyon' doner -> uygulama Onayla/Iptal butonu gosterir.
     */
    public function teklif($tip, $salonId, $salonAdi)
    {
        $turler = $this->turler();
        if (!isset($turler[$tip])) {
            return ['basarili' => false, 'cevap' => 'Bu kampanya türünü tanımıyorum.', 'kart' => null];
        }
        $t = $turler[$tip];
        $kitle = $this->hedefKitle($tip, $salonId);
        $sayi = $kitle->count();

        if ($sayi === 0) {
            return [
                'basarili'  => true, 'intent' => 'kampanya_teklif', 'seslendir' => true,
                'cevap'     => $t['ad'] . ' için uygun müşteri bulunamadı, gönderilecek kimse yok.',
                'kart'      => null,
            ];
        }

        // GERCEK erisim: kampanya UCRETSIZ bildirim reklami olarak gider; alici sayisi
        // uygulamasi olan (aktif push token'i olan) musterilerdir. SMS/kontor YOK.
        $reklam = $this->reklamNesnesi($tip, $salonId);
        $pushSayi = count(\App\Jobs\BildirimReklamGonderJob::alicilar($reklam));

        if ($pushSayi === 0) {
            return [
                'basarili'  => true, 'intent' => 'kampanya_teklif', 'seslendir' => true,
                'cevap'     => $t['ad'] . ' için uygun müşteri var ancak şu an uygulamayı kullanan '
                             . '(anlık bildirim alabilen) kimse yok. İsterseniz kampanyayı yine de '
                             . 'oluşturayım, uygulama içinde görünsün.',
                'kart'      => null,
                'aksiyon'   => [
                    'tur' => 'kampanya', 'tip' => $tip, 'oran' => $t['oran'],
                    'baslik' => $t['ad'], 'hedef_sayi' => 0,
                    'onay_metni' => 'Kampanyayı yine de oluştur',
                ],
            ];
        }

        $cevap = $t['ad'] . ' hazır. Uygulamayı kullanan ' . $pushSayi . ' müşterinize yüzde '
               . $t['oran'] . ' indirimi ücretsiz bildirim olarak göndereceğim. Bildirime dokunan '
               . 'müşteriye kupon anında tanımlanır. Kontör harcanmaz. Onaylıyor musunuz?';

        return [
            'basarili'  => true,
            'intent'    => 'kampanya_teklif',
            'seslendir' => true,
            'cevap'     => $cevap,
            'kart'      => null,
            // Uygulama bunu gorup Onayla/Iptal butonu cikarir; Onayla'da 'uygula' ucuna yollar.
            'aksiyon'   => [
                'tur'       => 'kampanya',
                'tip'       => $tip,
                'oran'      => $t['oran'],
                'baslik'    => $t['ad'],
                'hedef_sayi'=> $pushSayi,
                'onay_metni'=> $pushSayi . ' müşteriye %' . $t['oran'] . ' indirim bildirimi gönder',
            ],
        ];
    }

    /**
     * UYGULA (onaydan sonra): her aliciya kupon olustur + mesaji hazirla.
     * SMS gonderimini controller yapar (sms_gonder). Doner: mesajlar[], sayi, baslik.
     */
    public function uygula($tip, $salonId, $salonAdi, $oran = 0)
    {
        $turler = $this->turler();
        if (!isset($turler[$tip])) {
            return ['sayi' => 0, 'mesajlar' => [], 'baslik' => '', 'hata' => 'Bilinmeyen tür'];
        }
        $t = $turler[$tip];
        $oran = ($oran >= 5 && $oran <= 70) ? (int) $oran : $t['oran'];
        $kitle = $this->hedefKitle($tip, $salonId);

        $mesajlar = [];
        $gecerlilik = date('Y-m-d', strtotime('+15 days'));
        foreach ($kitle as $m) {
            $tel = trim((string) ($m->cep_telefon ?? ''));
            if ($tel === '') continue;
            $kod = strtoupper(Str::random(8));
            try {
                \App\CarkifelekOdulleri::create([
                    'salon_id'          => $salonId,
                    'user_id'           => $m->id,
                    'kod'               => $kod,
                    'tip'               => 'hizmet_indirimi',
                    'dever'             => $oran,
                    'indirim_tipi'      => 'yuzde',
                    'baslik'            => $t['baslik'] . ' %' . $oran,
                    'gecerlilik_tarihi' => $gecerlilik,
                ]);
            } catch (\Throwable $e) {
                // kupon yazilamazsa bu aliciyi atla (mesaj gonderme)
                continue;
            }
            $mesajlar[] = [
                'to'      => $tel,
                'message' => $this->mesajUret($t['sablon'], $salonAdi, $oran, $kod),
            ];
        }

        return [
            'sayi'     => count($mesajlar),
            'mesajlar' => $mesajlar,
            'baslik'   => $t['ad'],
            'oran'     => $oran,
        ];
    }

    // ─────────────── UCRETSIZ BILDIRIM REKLAMI yolu ───────────────

    /**
     * Kampanya turu -> BildirimReklamlari tur + segment eslemesi.
     * (Job::hedefKullanicilar bu 'hedef_kosul' JSON'ini bekler.)
     */
    protected function reklamAyar($tip)
    {
        switch ($tip) {
            case 'kayip':
                return ['tur' => 'geri_kazanim', 'hedef_kitle' => 'segment',
                        'hedef_kosul' => json_encode(['tip' => 'gelmeyen', 'gun' => 30])];
            case 'dogumgunu':
                return ['tur' => 'ozel_gun', 'hedef_kitle' => 'segment',
                        'hedef_kosul' => json_encode(['tip' => 'dogum_gunu'])];
            case 'bossaat':
                return ['tur' => 'bos_slot', 'hedef_kitle' => 'tumu', 'hedef_kosul' => null];
            case 'genel':
            default:
                return ['tur' => 'kampanya', 'hedef_kitle' => 'tumu', 'hedef_kosul' => null];
        }
    }

    /**
     * Kaydedilmemis bir BildirimReklamlari nesnesi (yalniz segment/reach hesabi icin).
     * Job::alicilar() bu nesneyi okur; DB'ye YAZILMAZ.
     */
    protected function reklamNesnesi($tip, $salonId)
    {
        $ayar = $this->reklamAyar($tip);
        return new \App\BildirimReklamlari([
            'salon_id'    => $salonId,
            'hedef_kitle' => $ayar['hedef_kitle'],
            'hedef_kosul' => $ayar['hedef_kosul'],
            'kanal_push'  => 1,
        ]);
    }

    /** Push gövdesi (kupon kodu YOK; müşteri dokununca kupon-kap ile tanımlanır). */
    protected function pushMesaj($tip, $salonAdi, $oran)
    {
        $salon = $salonAdi ?: 'Salonumuz';
        switch ($tip) {
            case 'kayip':
                return 'Sizi özledik! Size özel yüzde ' . $oran . ' indirim hazırladık. '
                     . 'Dokunun, kupon anında hesabınıza tanımlansın.';
            case 'bossaat':
                return 'Sakin saatlerimize özel yüzde ' . $oran . ' indirim! '
                     . 'Dokunun, kuponunuzu hemen alın.';
            case 'dogumgunu':
                return 'İyi ki doğdunuz! Doğum gününüze özel yüzde ' . $oran . ' indirim hediyemiz. '
                     . 'Dokunun, kuponunuz tanımlansın.';
            case 'genel':
            default:
                return $salon . '\'dan size özel yüzde ' . $oran . ' indirim! '
                     . 'Dokunun, kuponunuzu alın.';
        }
    }

    /**
     * UYGULA (BILDIRIM REKLAMI - UCRETSIZ): kampanyayi bir BildirimReklamlari kaydina
     * cevirir (kupon reklam altyapisinda tanimlanir -> musteri dokununca kupon-kap),
     * ve push job'ini tetikler. SMS/kontor HARCANMAZ.
     *
     * @return array ['sayi', 'baslik', 'oran', 'reklam_id'] veya ['hata'=>...]
     */
    public function uygulaReklam($tip, $salonId, $salonAdi, $oran = 0, $testUserId = 0)
    {
        $turler = $this->turler();
        if (!isset($turler[$tip])) {
            return ['sayi' => 0, 'baslik' => '', 'hata' => 'Bilinmeyen tür'];
        }
        $t = $turler[$tip];
        $oran = ($oran >= 5 && $oran <= 70) ? (int) $oran : $t['oran'];
        $ayar = $this->reklamAyar($tip);

        // GUVENLI TEST: yalniz secilen tek kisiye gonder (kisi segmenti). Job::hedefKullanicilar
        // 'kisi' tipinde SADECE bu user_id'yi doner -> baska hicbir musteriye gitmez.
        if ((int) $testUserId > 0) {
            $ayar['hedef_kitle'] = 'segment';
            $ayar['hedef_kosul'] = json_encode(['tip' => 'kisi', 'user_id' => (int) $testUserId]);
        }

        try {
            $reklam = \App\BildirimReklamlari::create([
                'salon_id'             => $salonId,
                'baslik'               => $t['baslik'] . ' %' . $oran,
                'mesaj'                => $this->pushMesaj($tip, $salonAdi, $oran),
                'tur'                  => $ayar['tur'],
                'kanal_push'           => 1,
                'kanal_inapp'          => 1,
                'tam_ekran'            => 0,
                'aksiyon_tipi'         => 'kupon',
                'kupon_indirim_tipi'   => 'yuzde',
                'kupon_deger'          => $oran,
                'kupon_baslik'         => '%' . $oran . ' İndirim',
                'kupon_gecerlilik_gun' => 15,
                'kupon_kisi_limit'     => 1,
                'kupon_dagitilan'      => 0,
                'hedef_kitle'          => $ayar['hedef_kitle'],
                'hedef_kosul'          => $ayar['hedef_kosul'],
                'durum'                => 'aktif',
                'yayin_baslangic'      => date('Y-m-d H:i:s'),
                'yayin_bitis'          => date('Y-m-d H:i:s', strtotime('+15 days')),
                'push_gonderildi'      => 0,
            ]);
        } catch (\Throwable $e) {
            return ['sayi' => 0, 'baslik' => $t['ad'], 'hata' => 'Reklam oluşturulamadı'];
        }

        // Gercek push alici sayisi (aktif FCM token'i olanlar).
        $sayi = count(\App\Jobs\BildirimReklamGonderJob::alicilar($reklam));

        // Gonderim: cok aliciysa kuyruk, azsa senkron (mevcut reklam modulu mantigi).
        try {
            if ($sayi > 100) {
                \App\Jobs\BildirimReklamGonderJob::dispatch($reklam->id);
            } elseif ($sayi > 0) {
                (new \App\Jobs\BildirimReklamGonderJob($reklam->id))->handle();
            }
            $reklam->update(['push_gonderildi' => 1]);
        } catch (\Throwable $e) {
            // Push tetiklenemese bile reklam 'aktif' -> uygulama icinde gorunur.
        }

        return [
            'sayi'      => $sayi,
            'baslik'    => $t['ad'],
            'oran'      => $oran,
            'reklam_id' => $reklam->id,
        ];
    }

    // ─────────────── Segment sorgulari (salt-okunur) ───────────────

    /** Belirli gundur (varsayilan 30) hic randevusu OLMAYAN ama gecmiste gelmis musteriler. */
    protected function gelmeyenMusteriler($salonId, $gun = 30, $max = 500)
    {
        $cutoff = date('Y-m-d', strtotime("-{$gun} days"));
        return DB::table('musteri_portfoy as mp')
            ->join('users as u', 'u.id', '=', 'mp.user_id')
            ->where('mp.salon_id', $salonId)
            ->where('mp.aktif', 1)
            ->where(function ($q) { $q->whereNull('mp.kara_liste')->orWhere('mp.kara_liste', '!=', 1); })
            ->whereNotNull('u.cep_telefon')->where('u.cep_telefon', '!=', '')
            ->whereExists(function ($q) use ($salonId) {
                $q->select(DB::raw(1))->from('randevular as r')
                    ->whereColumn('r.user_id', 'u.id')->where('r.salon_id', $salonId);
            })
            ->whereNotExists(function ($q) use ($salonId, $cutoff) {
                $q->select(DB::raw(1))->from('randevular as r2')
                    ->whereColumn('r2.user_id', 'u.id')->where('r2.salon_id', $salonId)
                    ->where('r2.tarih', '>=', $cutoff);
            })
            ->select('u.id', 'u.name', 'u.cep_telefon')
            ->limit($max)->get();
    }

    /** Bu AY dogum gunu olan musteriler. */
    protected function dogumGunuMusteriler($salonId, $max = 500)
    {
        return DB::table('musteri_portfoy as mp')
            ->join('users as u', 'u.id', '=', 'mp.user_id')
            ->where('mp.salon_id', $salonId)
            ->where('mp.aktif', 1)
            ->where(function ($q) { $q->whereNull('mp.kara_liste')->orWhere('mp.kara_liste', '!=', 1); })
            ->whereNotNull('u.cep_telefon')->where('u.cep_telefon', '!=', '')
            ->whereNotNull('u.dogum_tarihi')
            ->whereRaw('MONTH(u.dogum_tarihi) = ?', [(int) date('n')])
            ->select('u.id', 'u.name', 'u.cep_telefon')
            ->limit($max)->get();
    }

    /** Telefonu olan tum aktif portfoy musterileri (kara liste haric). */
    protected function tumMusteriler($salonId, $max = 500)
    {
        return DB::table('musteri_portfoy as mp')
            ->join('users as u', 'u.id', '=', 'mp.user_id')
            ->where('mp.salon_id', $salonId)
            ->where('mp.aktif', 1)
            ->where(function ($q) { $q->whereNull('mp.kara_liste')->orWhere('mp.kara_liste', '!=', 1); })
            ->whereNotNull('u.cep_telefon')->where('u.cep_telefon', '!=', '')
            ->select('u.id', 'u.name', 'u.cep_telefon')
            ->limit($max)->get();
    }

    // ─────────────── Yardimcilar ───────────────

    protected function mesajUret($sablon, $salonAdi, $oran, $kod)
    {
        return str_replace(
            ['{salon}', '{oran}', '{kod}'],
            [$salonAdi ?: 'Salonumuz', $oran, $kod],
            $sablon
        );
    }

    protected function normalize($metin)
    {
        $metin = mb_strtolower(trim((string) $metin), 'UTF-8');
        $ceviri = ['ç'=>'c','ğ'=>'g','ı'=>'i','İ'=>'i','ö'=>'o','ş'=>'s','ü'=>'u','â'=>'a','î'=>'i','û'=>'u'];
        $metin = strtr($metin, $ceviri);
        return str_replace(['i̇'], ['i'], $metin);
    }
}
