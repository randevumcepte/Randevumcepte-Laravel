<?php

namespace App\Http\Middleware;

use App\Salonlar;
use App\Services\MusteriDili;
use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Isletme mini sitesi (salonun kendi domain'i): pilot salonda sayfaya dil secici + ceviri betigi ekler.
 * Pilotta olmayan host, panel ve hukuki metin sayfalari (onam, sozlesme, KVKK) oldugu gibi gecer.
 */
class MusteriDilWeb
{
    const ATLA = ['isletmeyonetim', 'sistemyonetim', 'satisortakligi', 'superadmin', 'api', 'sozlesme', 'onam', 'musteriform',
        'musteri-form', 'form', 'gizlilik', 'kvkk', 'kullanici-sozlesmesi', 'kullanicisozlesmesi', 'musteri-dil'];

    public function handle($request, Closure $next)
    {
        $response = $next($request);
        try {
            if (!$request->isMethod('GET') || $request->ajax() || !method_exists($response, 'getContent')) return $response;
            $salonId = self::salon($request->getHost());
            if (!$salonId || !MusteriDili::acikMi($salonId)) return $response;
            $ilk = mb_strtolower(explode('/', trim($request->path(), '/'))[0]);
            foreach (self::ATLA as $a) if ($a !== 'form' ? strpos($ilk, $a) === 0 : $ilk === 'form') return $response;
            if (stripos((string) $response->headers->get('Content-Type'), 'text/html') === false) return $response;
            $html = $response->getContent();
            $yer = is_string($html) ? stripos($html, '</head>') : false;
            if ($yer === false) return $response;

            $surum = [];
            foreach (MusteriDili::diller() as $d) if ($d['kod'] !== 'tr') $surum[$d['kod']] = MusteriDili::salonSurum($salonId, $d['kod']);
            $ayar = json_encode(['diller' => MusteriDili::diller(), 'surum' => $surum], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
            $js = public_path('js/musteri-dil.js');
            $ek = '<script>window.MUSTERI_DIL=' . $ayar . ';</script><script src="/public/js/musteri-dil.js?v=' . (is_file($js) ? filemtime($js) : 1) . '"></script>';
            $response->setContent(substr($html, 0, $yer) . $ek . substr($html, $yer));
        } catch (\Throwable $e) {
            \Log::warning('[DIL] web', ['err' => $e->getMessage()]);
        }
        return $response;
    }

    /** Host -> salon id (10 dk onbellek; hatirlanmayan host 0). */
    public static function salon($host)
    {
        $host = str_replace('www.', '', (string) $host);
        $id = Cache::get('md_host_' . $host);
        if ($id === null) {
            $id = (int) Salonlar::where('domain', $host)->value('id');
            Cache::put('md_host_' . $host, $id, 10);
        }
        return (int) $id;
    }
}
