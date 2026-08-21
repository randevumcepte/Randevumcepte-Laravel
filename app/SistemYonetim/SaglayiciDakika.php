<?php

namespace App\SistemYonetim;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * SaglayiciDakika — voicetelekom panelinden bir trunk'in FATURALANDIRILAN
 * (gercek) giden dakikasini ceker. Saglayicinin API'si olmadigi icin panele
 * (kullanici adi/sifre ile) login olup CDR rapor sayfasindaki
 * "Faturalandirilmis Sure, dk:sn: MM:SS" toplamini HTML'den ayristirir.
 *
 * Neden: Asterisk billsec'i early-media/ringback'i konusma sayarak ~%10 fazla
 * cikiyor (bkz. dstchannel olcumu). Faturaya birebir uyan tek kaynak saglayici.
 *
 * Kimlik bilgileri config/santral.php -> .env (koda yazilmaz). Sonuc 30 dk cache.
 */
class SaglayiciDakika
{
    /** Istek boyunca paylasilan oturum cookie'si (liste dongusunde tek login). */
    private static $cookie = null;
    private static $loggedIn = false;

    /**
     * @return array{
     *   ok:bool, dakika?:float, saniye?:int, adet?:int,
     *   source?:string, start?:string, end?:string, hata?:string, ham_ozet?:string
     * }
     */
    public static function fetch($source, $tarih1 = null, $tarih2 = null)
    {
        $source = preg_replace('/\D/', '', (string) $source);
        if ($source === '') {
            return ['ok' => false, 'hata' => 'gecersiz source (trunk numarasi)'];
        }

        $cfg = config('santral.saglayici');
        if (empty($cfg['user']) || empty($cfg['pass'])) {
            return ['ok' => false, 'hata' => 'Saglayici kimlik bilgisi tanimli degil (.env: SANTRAL_SAGLAYICI_USER/PASS)'];
        }

        $cacheKey = 'sy.saglayici.' . md5($source . '|' . $tarih1 . '|' . $tarih2);
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) return $cached;

        // Tarih formati saglayici panelindeki gibi: DD-MM-YYYY HH:MM:SS ; bitis "now".
        $start = $tarih1 ? date('d-m-Y H:i:s', strtotime($tarih1 . ' 00:00:00')) : '01-01-2020 00:00:00';
        $end   = $tarih2 ? date('d-m-Y H:i:s', strtotime($tarih2 . ' 23:59:59')) : 'now';

        $url = strtr($cfg['cdr_url'], [
            '{SOURCE}' => urlencode($source),
            '{START}'  => urlencode($start),
            '{END}'    => urlencode($end),
        ]);

        // Oturum ac (gerekiyorsa), CDR'i cek. Ayristirilamazsa cookie dusmus
        // olabilir -> bir kez zorla yeniden login olup tekrar dene.
        for ($deneme = 1; $deneme <= 2; $deneme++) {
            $hataLogin = self::ensureLogin($cfg, $deneme === 2);
            if ($hataLogin) return ['ok' => false, 'hata' => $hataLogin];

            $cdr = self::curl($cfg, [CURLOPT_URL => $url]);
            if ($cdr['err']) return ['ok' => false, 'hata' => 'cdr baglantisi: ' . $cdr['err']];

            $parsed = self::parse($cdr['body']);
            if ($parsed !== null) {
                $sonuc = [
                    'ok'     => true,
                    'dakika' => $parsed['dakika'],
                    'saniye' => $parsed['saniye'],
                    'adet'   => $parsed['adet'],
                    'source' => $source,
                    'start'  => $start,
                    'end'    => $end,
                ];
                Cache::put($cacheKey, $sonuc, ($cfg['cache_dk'] ?? 30) * 60);
                return $sonuc;
            }
            self::$loggedIn = false; // ikinci turda yeniden login
        }

        Log::warning('SaglayiciDakika: toplam sure ayristirilamadi', ['source' => $source]);
        return [
            'ok'       => false,
            'hata'     => 'Toplam sure ayristirilamadi (login basarisiz ya da sayfa formati degismis olabilir)',
            'ham_ozet' => isset($cdr) ? trim(mb_substr(preg_replace('/\s+/', ' ', strip_tags($cdr['body'])), 0, 300)) : '',
        ];
    }

    /** CDR HTML'inden faturalandirilan toplam sure + cagri adedi. null = bulunamadi. */
    private static function parse($html)
    {
        if (!is_string($html) || $html === '') return null;
        $dk = $sn = null;
        if (preg_match('/Faturaland[^0-9]{0,40}?([0-9]+):([0-9]{1,2})/u', $html, $m)) {
            $dk = (int) $m[1]; $sn = (int) $m[2];
        } elseif (preg_match('/Sure[^0-9]{0,20}?dak?[^0-9]{0,6}san[^0-9]{0,6}([0-9]+):([0-9]{1,2})/iu', $html, $m)) {
            $dk = (int) $m[1]; $sn = (int) $m[2];
        }
        if ($dk === null) return null;

        $adet = null;
        if (preg_match('/Aramalar[^0-9]{0,10}([0-9]+)/u', $html, $m)) $adet = (int) $m[1];

        return ['dakika' => round($dk + $sn / 60, 1), 'saniye' => $dk * 60 + $sn, 'adet' => $adet];
    }

    /** Oturum ac (bir kez / istek). $zorla=true ise mevcut oturumu yenile. */
    private static function ensureLogin($cfg, $zorla = false)
    {
        if (self::$loggedIn && !$zorla) return null;

        if (self::$cookie === null) {
            self::$cookie = tempnam(sys_get_temp_dir(), 'vtck');
            register_shutdown_function(function () {
                if (is_string(self::$cookie) && file_exists(self::$cookie)) @unlink(self::$cookie);
            });
        }

        $login = self::curl($cfg, [
            CURLOPT_URL        => rtrim($cfg['base'], '/') . $cfg['login_path'],
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'acct_type'  => $cfg['acct_type'] ?? 'customer',
                'login_page' => 'all',
                'username'   => $cfg['user'],
                'password'   => $cfg['pass'],
                'Login'      => 'Login',
            ]),
        ]);
        if ($login['err']) return 'login baglantisi: ' . $login['err'];

        self::$loggedIn = true;
        return null;
    }

    /**
     * Ortak curl — paylasilan cookie jar (self::$cookie) ile oturum tasir.
     * @return array{err:?string, body:?string, http:int}
     */
    private static function curl($cfg, array $opts)
    {
        $ch = curl_init();
        curl_setopt_array($ch, $opts + [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 40,
            CURLOPT_COOKIEJAR      => self::$cookie,
            CURLOPT_COOKIEFILE     => self::$cookie,
            CURLOPT_SSL_VERIFYPEER => (bool) ($cfg['verify_ssl'] ?? true),
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (randevumcepte-santral-dakika)',
        ]);
        $body = curl_exec($ch);
        $err  = curl_errno($ch) ? curl_error($ch) : null;
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['err' => $err, 'body' => $body, 'http' => $http];
    }
}
