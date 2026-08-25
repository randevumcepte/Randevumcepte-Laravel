<?php

namespace App\Services;

/**
 * Google Cloud Text-to-Speech ile metni MP3'e cevirir ve SUNUCUDA ONBELLEGE alir.
 *
 * Mantik:
 *  - Ayni metin (ayni ses) daha once uretildiyse tekrar Google'a gidilmez -> dosya
 *    dogrudan doner. Bilgilendirme cevaplari SABIT oldugu icin tum salonlarda ORTAK
 *    cache; pratikte tek seferlik uretim -> maliyet ~0.
 *  - Anahtar yoksa null doner; cagiran taraf cihaz TTS'ine duser (regresyon yok).
 *
 * Dosyalar: storage/app/tts/<hash>.mp3
 */
class SeslendirmeServisi
{
    protected $klasor;

    /** Son Google cagrisinin teshis bilgisi (debug icin). */
    public $sonHata = null;

    public function __construct()
    {
        $this->klasor = storage_path('app/tts');
        if (!is_dir($this->klasor)) {
            @mkdir($this->klasor, 0775, true);
        }
    }

    /**
     * Metni sese cevirir. Cache'de varsa uretmeden dosya ADINI doner (<hash>.mp3).
     * Basarisizsa null (cagiran cihaz TTS'ine duser).
     */
    public function uret($metin, $ses = null)
    {
        $metin = trim((string) $metin);
        if ($metin === '') return null;

        $ses = $ses ?: (string) config('services.google_tts.voice', 'tr-TR-Wavenet-E');
        $okunacak = $this->okunusHazirla($metin);
        if ($okunacak === '') return null;

        $hash = md5($ses . '|' . $okunacak);
        $ad = $hash . '.mp3';
        $yol = $this->klasor . '/' . $ad;

        // Cache hit
        if (is_file($yol) && filesize($yol) > 0) return $ad;

        $mp3 = $this->googleUret($okunacak, $ses);
        if ($mp3 === null || $mp3 === '') return null;

        $yazilan = @file_put_contents($yol, $mp3);
        if (is_array($this->sonHata)) {
            $this->sonHata['klasor']         = $this->klasor;
            $this->sonHata['klasor_yazilir'] = is_writable($this->klasor) ? 1 : 0;
            $this->sonHata['yazilan_byte']   = ($yazilan === false) ? -1 : (int) $yazilan;
            $this->sonHata['dosya_var']      = is_file($yol) ? 1 : 0;
        }
        return (is_file($yol) && filesize($yol) > 0) ? $ad : null;
    }

    /** Dosya adindan tam yol (guvenlik: sadece basename). Yoksa null. */
    public function dosyaYolu($ad)
    {
        $ad = basename((string) $ad);
        if (!preg_match('/^[a-f0-9]{32}\.mp3$/', $ad)) return null;
        $yol = $this->klasor . '/' . $ad;
        return is_file($yol) ? $yol : null;
    }

    /** Google Cloud TTS REST cagrisi. Basarisizsa null. */
    protected function googleUret($metin, $ses)
    {
        $key = (string) config('services.google_tts.key', '');
        if ($key === '') return null;

        $url = 'https://texttospeech.googleapis.com/v1/text:synthesize?key=' . urlencode($key);
        $govde = json_encode([
            'input'       => ['text' => $metin],
            'voice'       => ['languageCode' => 'tr-TR', 'name' => $ses],
            'audioConfig' => ['audioEncoding' => 'MP3', 'speakingRate' => 1.0, 'pitch' => 0.0],
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=utf-8'],
            CURLOPT_POSTFIELDS     => $govde,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 8,
        ]);
        $res     = curl_exec($ch);
        $kod     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        // Teshis (debug icin): ne oldu?
        $this->sonHata = [
            'http'     => $kod,
            'curl_err' => $curlErr,
            'govde'    => is_string($res) ? mb_substr($res, 0, 250) : '(bos)',
        ];

        if ($kod !== 200 || !$res) return null;
        $j = json_decode($res, true);
        if (empty($j['audioContent'])) return null;
        $bin = base64_decode($j['audioContent']); // strict degil: Google'a guveniyoruz
        $this->sonHata['mp3_uzunluk'] = ($bin !== false) ? strlen($bin) : -1;
        return ($bin !== false && $bin !== '') ? $bin : null;
    }

    /**
     * Okunusu hazirlar: (1) marka terimlerini fonetik yaz (Hydrafacial -> Hidrafeşıl),
     * (2) saat "14:30" -> "on dort otuz", (3) kalan rakamlari yaziya cevir. Boylece
     * TTS rakamlari/markalari dogru okur.
     */
    public function okunusHazirla($metin)
    {
        $metin = (string) $metin;
        // Gecersiz UTF-8 ise onar; aksi halde /u regex NULL doner ve cevap KAYBOLUR.
        if (!mb_check_encoding($metin, 'UTF-8')) {
            $metin = @mb_convert_encoding($metin, 'UTF-8', 'UTF-8');
        }
        $m = ' ' . trim($metin) . ' ';

        // Her adimda NULL gelirse onceki degeri koru (asla cevabi kaybetme).
        // 1) Marka/yabanci terimler (Turkce TTS yanlis okuyor)
        $t = preg_replace('/hydra\s*facial/iu', 'Hidrafeşıl', $m); if ($t !== null) $m = $t;
        $t = preg_replace('/hydrafacial/iu', 'Hidrafeşıl', $m);   if ($t !== null) $m = $t;

        // 2) Saat HH:MM -> yaziyla
        $t = preg_replace_callback('/\b([01]?\d|2[0-3]):([0-5]\d)\b/u', function ($x) {
            $s = $this->sayiYazi((int) $x[1]);
            $d = ((int) $x[2]) > 0 ? ' ' . $this->sayiYazi((int) $x[2]) : '';
            return $s . $d;
        }, $m);
        if ($t !== null) $m = $t;

        // 3) Kalan tam sayilar -> yaziya
        $t = preg_replace_callback('/\d+/u', function ($x) {
            return $this->sayiYazi((int) $x[0]);
        }, $m);
        if ($t !== null) $m = $t;

        $t = preg_replace('/\s+/u', ' ', $m); if ($t !== null) $m = $t;
        $m = trim($m);

        // Emniyet: bir sekilde bosaldiysa orijinal metne don.
        return $m !== '' ? $m : trim((string) $metin);
    }

    /** 0..999999 arasi tam sayiyi Turkce yaziya cevirir (nadir buyuklukte oldugu gibi). */
    protected function sayiYazi($n)
    {
        if ($n === 0) return 'sıfır';
        $on = '';
        if ($n < 0) { $on = 'eksi '; $n = -$n; }
        if ($n >= 1000000) return $on . (string) $n;

        $bin = intdiv($n, 1000);
        $kalan = $n % 1000;
        $out = '';
        if ($bin > 0) {
            $out .= ($bin === 1) ? 'bin ' : $this->ucBasamak($bin) . ' bin ';
        }
        if ($kalan > 0) {
            $out .= $this->ucBasamak($kalan);
        }
        return $on . trim($out);
    }

    protected function ucBasamak($n)
    {
        $birler = ['', 'bir', 'iki', 'üç', 'dört', 'beş', 'altı', 'yedi', 'sekiz', 'dokuz'];
        $onlar  = ['', 'on', 'yirmi', 'otuz', 'kırk', 'elli', 'altmış', 'yetmiş', 'seksen', 'doksan'];
        $out = '';
        $yuz = intdiv($n, 100);
        $k   = $n % 100;
        if ($yuz > 0) $out .= (($yuz === 1) ? 'yüz' : $birler[$yuz] . ' yüz') . ' ';
        $o = intdiv($k, 10);
        $b = $k % 10;
        if ($o > 0) $out .= $onlar[$o] . ' ';
        if ($b > 0) $out .= $birler[$b] . ' ';
        return trim($out);
    }
}
