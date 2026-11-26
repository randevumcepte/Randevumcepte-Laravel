<?php

namespace App\Http\Middleware;

use App\Personeller;
use App\Services\MusteriDili;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Isletme paneli: pilot salonda calisan personel kendi dilini secer (uygulamadaki secimle ayni kayit, personel_dil).
 * Sayfa Turkce uretilir; secili dilde public/js/musteri-dil.js (panel kipi) cevirir. Turkce secen personele
 * yalniz profil menusundeki "Dil / Language" secimi eklenir.
 */
class MusteriDilPanel
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        try {
            if (!$request->isMethod('GET') || $request->ajax() || !method_exists($response, 'getContent')) return $response;
            if (strpos(trim($request->path(), '/'), 'isletmeyonetim') !== 0) return $response;
            if (stripos((string) $response->headers->get('Content-Type'), 'text/html') === false) return $response;
            if (session('sysadmin_impersonation_id')) return $response; // sistem yoneticisi salona girdiyse Turkce
            $y = Auth::guard('isletmeyonetim')->user();
            if (!$y || !$salonId = self::salon($y->id, $request->input('sube'))) return $response;
            $html = $response->getContent();
            $yer = is_string($html) ? stripos($html, '</head>') : false;
            if ($yer === false) return $response;

            $dil = MusteriDili::yetkiliDil($y->id) ?: 'tr';
            $surum = [];
            if ($dil !== 'tr') $surum[$dil] = MusteriDili::salonSurum($salonId, $dil);
            $ayar = json_encode([
                'diller' => MusteriDili::diller(),
                'surum' => $surum,
                'panel' => ['dil' => $dil, 'sozluk' => '/isletmeyonetim/dil-sozluk/' . $dil . '?s=' . $salonId, 'kaydet' => '/isletmeyonetim/dil'],
            ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
            $js = public_path('js/musteri-dil.js');
            $ek = '<script>window.MUSTERI_DIL=' . $ayar . ';</script><script src="/public/js/musteri-dil.js?v=' . (is_file($js) ? filemtime($js) : 1) . '"></script>';
            $response->setContent(substr($html, 0, $yer) . $ek . substr($html, $yer));
        } catch (\Throwable $e) {
            \Log::warning('[DIL] panel', ['err' => $e->getMessage()]);
        }
        return $response;
    }

    /** Personelin calistigi pilot salon (once acik olan sube, yoksa ilk pilot salon); yoksa 0. */
    public static function salon($yetkiliId, $sube = null)
    {
        $salonlar = Personeller::where('yetkili_id', (int) $yetkiliId)->pluck('salon_id')->map(function ($s) { return (int) $s; })->all();
        if ($sube && in_array((int) $sube, $salonlar, true) && MusteriDili::acikMi($sube)) return (int) $sube;
        foreach ($salonlar as $s) if (MusteriDili::acikMi($s)) return $s;
        return 0;
    }
}
