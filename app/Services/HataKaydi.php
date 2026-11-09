<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hata izleme kaydi (sistem_hatalari + sistem_hata_kayitlari). Tek yazma yolu; kaynaklar:
 * Handler::report (istisna/komut), LogRequestPerformance (5xx/419/429), Log::error dinleyicisi (log),
 * PerformansController (tarayici js / zaman_asimi).
 *
 * Kurallar: hicbir kosulda istisna firlatmaz ve log yazmaz (sonsuz dongu olmasin); ayri DB baglantisi
 * kullanir (geri alinan transaction kaydi silmesin); istek basina en fazla 20 kayit.
 */
class HataKaydi
{
    const ISTEK_BASI_SINIR = 20;

    /** Bu istekte istisna kaydedildi mi (ara katman ayni 500'u ikinci kez yazmasin). */
    public static $istisnaKaydedildi = false;

    /** Bu istekte herhangi bir hata kaydi yazildi mi (ayni 500 ara katmandan tekrar yazilmasin). */
    public static $kayitVar = false;

    private static $sayac = 0;
    private static $pencere = 0;   // kuyruk isci gibi uzun sureclerde sinir dakikalik sifirlanir
    private static $yaziyor = false;
    private static $tabloVar = null;

    public static function istisna($e)
    {
        self::$istisnaKaydedildi = true;
        $konsol = app()->runningInConsole();
        // Gecersiz/suresi dolmus token: Passport raporlayip 401 doner; sunucu hatasi degil, ayri tur.
        $oturum = $e instanceof \League\OAuth2\Server\Exception\OAuthServerException
            || $e instanceof \Illuminate\Auth\AuthenticationException;
        self::yaz([
            'tur'    => $oturum ? 'oturum' : ($konsol ? 'komut' : 'istisna'),
            'baslik' => get_class($e),
            'mesaj'  => $e->getMessage(),
            'konum'  => self::kisaYol($e->getFile()).':'.$e->getLine(),
            'uc'     => $konsol ? self::komutAdi() : self::istekUcu(),
            'ornek'  => self::kisaTrace($e->getTraceAsString()),
        ]);
    }

    /** 5xx / 419 / 429 cevaplar (controller'in yakalayip 500 dondurdugu hatalar dahil). */
    public static function http($request, $durum, $govde = null)
    {
        if (self::$kayitVar) {
            return; // istisna/log olarak zaten yazildi
        }
        $mesaj = null;
        if (is_string($govde) && $govde !== '') {
            $json = json_decode($govde, true);
            if (is_array($json)) {
                foreach (['eklenemez', 'message', 'mesaj', 'error', 'hata'] as $k) {
                    if (!empty($json[$k]) && is_string($json[$k])) { $mesaj = strip_tags($json[$k]); break; }
                }
            }
        }
        $etiket = [419 => 'Oturum süresi doldu (419)', 429 => 'İstek sınırı aşıldı (429)', 500 => 'Sunucu hatası (500)',
                   502 => 'Ağ geçidi hatası (502)', 503 => 'Hizmet dışı (503)', 504 => 'Ağ geçidi zaman aşımı (504)'];
        self::yaz([
            'tur'        => 'http',
            'baslik'     => $etiket[$durum] ?? ('HTTP '.$durum),
            'mesaj'      => $mesaj,
            'uc'         => self::istekUcu(),
            'durum_kodu' => (int) $durum,
            // ayni uc + ayni durum = ayni grup (mesajdaki degisken sayilar grubu bolmesin)
            'parmak'     => 'http|'.$durum.'|'.self::istekUcu(),
        ]);
    }

    /** Log::error / critical (istisnasiz; istisnali olanlar Handler'dan zaten gelir). */
    public static function log($seviye, $mesaj, array $baglam)
    {
        if (isset($baglam['exception']) || self::$yaziyor) {
            return;
        }
        $konum = null;
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 25) as $f) {
            $dosya = $f['file'] ?? '';
            if ($dosya !== '' && strpos($dosya, DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR) === false
                && strpos($dosya, 'HataKaydi.php') === false && strpos($dosya, 'EventServiceProvider') === false) {
                $konum = self::kisaYol($dosya).':'.($f['line'] ?? 0);
                break;
            }
        }
        $konsol = app()->runningInConsole();
        $ozet = json_encode(array_slice($baglam, 0, 8), JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
        self::yaz([
            'tur'    => 'log',
            'baslik' => 'Log '.$seviye,
            'mesaj'  => (string) $mesaj,
            'konum'  => $konum,
            'uc'     => $konsol ? self::komutAdi() : self::istekUcu(),
            'ornek'  => $ozet && $ozet !== '[]' ? mb_substr($ozet, 0, 3000) : null,
        ]);
    }

    /** Tarayicidan gelen JS hatasi / zaman asimi. */
    public static function istemci($tur, $baslik, $mesaj, $konum, $uri, $salonId, $userId, $ornek = null)
    {
        self::yaz([
            'tur'      => $tur,
            'baslik'   => $baslik,
            'mesaj'    => $mesaj,
            'konum'    => $konum,
            'uc'       => $uri,
            'ornek'    => $ornek,
            'uri'      => $uri,
            'salon_id' => $salonId,
            'user_id'  => $userId,
        ]);
    }

    private static function yaz(array $h)
    {
        $dakika = intdiv(time(), 60);
        if ($dakika !== self::$pencere) {
            self::$pencere = $dakika;
            self::$sayac = 0;
        }
        if (self::$yaziyor || self::$sayac >= self::ISTEK_BASI_SINIR) {
            return;
        }
        self::$yaziyor = true;
        self::$sayac++;
        self::$kayitVar = true;
        try {
            $db = self::db();
            if (self::$tabloVar === null) {
                self::$tabloVar = Schema::connection('izleme')->hasTable('sistem_hatalari');
            }
            if (!self::$tabloVar) {
                return;
            }

            $simdi = date('Y-m-d H:i:s');
            $mesaj = $h['mesaj'] !== null ? mb_substr(preg_replace('/\s+/', ' ', (string) $h['mesaj']), 0, 1000) : null;
            // Parmak izi: degisken parcalar (sayi, tirnakli deger) gruplamayi bolmesin.
            $normal = $h['parmak'] ?? ($h['tur'].'|'.$h['baslik'].'|'.($h['konum'] ?? '').'|'
                .preg_replace(['/\d+/', "/'[^']*'/", '/"[^"]*"/'], ['#', "'?'", '"?"'], (string) $mesaj));
            $parmak = md5($normal);

            $db->statement(
                'INSERT INTO sistem_hatalari (parmak_izi, tur, baslik, mesaj, konum, uc, ilk_gorulme, son_gorulme, adet, ornek)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
                 ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), adet = adet + 1, son_gorulme = VALUES(son_gorulme),
                    mesaj = VALUES(mesaj), ornek = COALESCE(VALUES(ornek), ornek)',
                [$parmak, $h['tur'], mb_substr((string) $h['baslik'], 0, 255), $mesaj,
                 isset($h['konum']) ? mb_substr((string) $h['konum'], 0, 500) : null,
                 isset($h['uc']) ? mb_substr((string) $h['uc'], 0, 255) : null,
                 $simdi, $simdi, isset($h['ornek']) ? mb_substr((string) $h['ornek'], 0, 6000) : null]
            );
            $hataId = (int) $db->getPdo()->lastInsertId();

            if ($hataId > 0) {
                $istek = self::istekBaglami();
                $db->table('sistem_hata_kayitlari')->insert([
                    'hata_id'    => $hataId,
                    'created_at' => $simdi,
                    'salon_id'   => $h['salon_id'] ?? $istek['salon_id'],
                    'user_id'    => $h['user_id'] ?? $istek['user_id'],
                    'uri'        => mb_substr((string) ($h['uri'] ?? $istek['uri']), 0, 500) ?: null,
                    'durum_kodu' => $h['durum_kodu'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            // izleme kaydi hicbir zaman ikinci bir hata uretmesin
        } finally {
            self::$yaziyor = false;
        }
    }

    /** Uygulama baglantisinin kopyasi: ayri PDO, acik transaction'dan etkilenmez. */
    private static function db()
    {
        if (!config('database.connections.izleme')) {
            $varsayilan = config('database.default');
            config(['database.connections.izleme' => config('database.connections.'.$varsayilan)]);
        }

        return DB::connection('izleme');
    }

    private static function istekBaglami()
    {
        $b = ['salon_id' => null, 'user_id' => null, 'uri' => null];
        if (app()->runningInConsole()) {
            return $b;
        }
        try {
            $r = request();
            $salon = $r->input('sube') ?? $r->input('salonid') ?? $r->input('salon_id');
            $b['salon_id'] = is_numeric($salon) ? (int) $salon : null;
            $b['uri'] = '/'.ltrim($r->path(), '/');
            if ($r->hasSession()) {
                foreach (['isletmeyonetim', 'sistemyonetim', 'satisortakligi', 'web'] as $g) {
                    if (auth($g)->check()) { $b['user_id'] = (int) auth($g)->id(); break; }
                }
            }
        } catch (\Throwable $e) {
        }

        return $b;
    }

    private static function istekUcu()
    {
        try {
            $r = request();
            $route = $r->route();
            if ($route && ($an = $route->getActionName()) && $an !== 'Closure') {
                return preg_replace('#^.*\\\\#', '', $an);
            }

            return $r->method().' /'.ltrim($r->path(), '/');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function komutAdi()
    {
        $argv = $_SERVER['argv'] ?? [];

        return 'artisan '.($argv[1] ?? '?');
    }

    private static function kisaYol($dosya)
    {
        return str_replace(base_path().DIRECTORY_SEPARATOR, '', (string) $dosya);
    }

    private static function kisaTrace($trace)
    {
        $satirlar = explode("\n", str_replace(base_path().DIRECTORY_SEPARATOR, '', (string) $trace));

        return implode("\n", array_slice($satirlar, 0, 25));
    }
}
