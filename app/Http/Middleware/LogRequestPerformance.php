<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Yavas/agir istekleri yakalar ve 'yavas_istekler' tablosuna yazar.
 *
 * ESKI DAVRANIS: HER istegi process_counter.log'a + 'performance' kanalina yaziyordu
 * -> log sismesi + her istekte dosya I/O. Kimse bu loglari okumadigi icin kaldirildi.
 *
 * YENI DAVRANIS: Yalnizca ESIK USTU istekleri (sure >= ESIK_MS veya tepe bellek >= ESIK_MB)
 * yapisal olarak tabloya kaydeder: URI, method, Controller@action, user_id, salon_id,
 * sure_ms, bellek_mb, pid. Boylece "hangi istek/isletme/sayfa CPU/bellek yiyor" sorgulanir;
 * CPU uyarisi (guvenlik-watchdog) son yavas istekleri mesaja ekleyebilir.
 *
 * Loglama TAMAMEN try/catch icinde — hicbir kosulda istegi/yaniti bozmaz.
 */
class LogRequestPerformance
{
    /** Bu esiklerin ALTINDAKI istekler kaydedilmez (gurultuyu onler). */
    const ESIK_MS = 1500;   // 1.5 sn+
    const ESIK_MB = 160;    // 160 MB tepe bellek+

    public function handle($request, Closure $next)
    {
        $start = microtime(true);

        // N+1 teshisi: istek basina DB sorgu sayisini HAFIF sayac ile say (query log DEGIL,
        // sadece int increment -> minimum overhead). php-fpm'de app her istekte taze boot
        // oldugundan listener birikmez.
        // Toplam sorgu suresi + en yavas sorgu (SQL parametresiz; bindings tutulmaz -> kisisel veri yok).
        $sorguSayisi = 0;
        $sorguMs = 0.0;
        $enYavasMs = 0.0;
        $enYavasSql = null;
        try {
            \DB::listen(function ($q) use (&$sorguSayisi, &$sorguMs, &$enYavasMs, &$enYavasSql) {
                $sorguSayisi++;
                $sorguMs += (float) $q->time;
                if ((float) $q->time > $enYavasMs) {
                    $enYavasMs = (float) $q->time;
                    $enYavasSql = $q->sql;
                }
            });
        } catch (\Throwable $e) {}

        $response = $next($request);

        // Hata izleme: 5xx (controller'in yakalayip dondurdugu dahil), 419 oturum, 429 sinir.
        try {
            $durum = method_exists($response, 'getStatusCode') ? (int) $response->getStatusCode() : 200;
            $kasitli = isset($response->headers) && $response->headers->get('X-Rc-Kasitli') === '1';
            if (!$kasitli && ($durum >= 500 || $durum === 419 || $durum === 429)) {
                \App\Services\HataKaydi::http($request, $durum, $durum >= 500 ? mb_substr((string) $response->getContent(), 0, 4000) : null);
            }
        } catch (\Throwable $e) {}

        try {
            $sure_ms   = (int) round((microtime(true) - $start) * 1000);
            $bellek_mb = (int) round(memory_get_peak_usage(true) / 1048576);

            if ($sure_ms < self::ESIK_MS && $bellek_mb < self::ESIK_MB) {
                return $response; // esik alti -> kaydetme
            }
            if (!Schema::hasTable('yavas_istekler')) {
                return $response;
            }

            // Controller@action (namespace'siz)
            $aksiyon = null;
            $route = $request->route();
            if ($route) {
                $an = $route->getActionName();                 // App\Http\...\Foo@bar
                if ($an && $an !== 'Closure') {
                    $aksiyon = preg_replace('#^.*\\\\#', '', $an); // Foo@bar
                }
            }

            // Isletme (sube) — panel isteklerinde query/param olarak gelir
            $salon_id = $request->input('sube')
                ?? $request->input('salonid')
                ?? $request->input('salon_id')
                ?? null;
            if (!is_numeric($salon_id)) $salon_id = null;

            // Kullanici — once panel guard'i, sonra default
            // API isteginde session yok; session guard'i patlarsa kayit dusmesin diye ayri korunur.
            $user_id = null;
            try {
                $guardlar = $request->hasSession() ? ['isletmeyonetim', 'satisortakligi', 'web'] : [];
                foreach ($guardlar as $g) {
                    if (auth($g)->check()) { $user_id = auth($g)->id(); break; }
                }
                if ($user_id === null && $request->user()) $user_id = $request->user()->id;
            } catch (\Throwable $e) {}

            $uri = $request->getMethod() === 'GET'
                ? $request->getRequestUri()        // query string dahil (sube=... gorunur)
                : $request->path();
            $uri = mb_substr($uri, 0, 500);

            DB::table('yavas_istekler')->insert([
                'created_at' => date('Y-m-d H:i:s'),
                'uri'        => $uri,
                'method'     => $request->getMethod(),
                'aksiyon'    => $aksiyon ? mb_substr($aksiyon, 0, 191) : null,
                'user_id'    => is_numeric($user_id) ? (int) $user_id : null,
                'salon_id'   => $salon_id ? (int) $salon_id : null,
                'sure_ms'    => $sure_ms,
                'bellek_mb'  => $bellek_mb,
                'sorgu_sayisi' => $sorguSayisi,
                'pid'        => function_exists('getmypid') ? getmypid() : null,
            ] + (self::sorguKolonlariVar() ? [
                'sorgu_ms'          => (int) round($sorguMs),
                'en_yavas_sorgu_ms' => (int) round($enYavasMs),
                'en_yavas_sorgu'    => $enYavasSql !== null ? mb_substr(preg_replace('/\s+/', ' ', $enYavasSql), 0, 1000) : null,
            ] : []));

            // Tabloyu sinirla: ~%1 ihtimalle 7 gunden eski kayitlari temizle (cron'suz bound).
            if (mt_rand(1, 100) === 1) {
                DB::table('yavas_istekler')
                    ->where('created_at', '<', date('Y-m-d H:i:s', time() - 7 * 86400))
                    ->delete();
            }
        } catch (\Throwable $e) {
            // Teshis loglama hicbir zaman istegi etkilemesin — sessizce yut.
        }

        return $response;
    }

    /** Migration canliya kod yuklenmeden once kosmamissa insert patlamasin (kolonlar 1 saat cache). */
    private static function sorguKolonlariVar()
    {
        return (bool) \Cache::remember('yavas_istekler_sorgu_kolonlari', 60, function () {
            return Schema::hasColumn('yavas_istekler', 'en_yavas_sorgu') ? 1 : 0;
        });
    }
}
