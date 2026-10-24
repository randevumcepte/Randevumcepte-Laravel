<?php

namespace App\Http\Middleware;

use Closure;
use HTMLMin\HTMLMin\Http\Middleware\MinifyMiddleware as BaseMinifyMiddleware;

/**
 * HTML minify middleware — vendor HTMLMin MinifyMiddleware'in GUVENLI sarmalayicisi.
 *
 * SORUN (beyaz ekran kok nedeni):
 *   Minify_HTML::minify() tum HTML uzerinde `[\s\S]*?` gibi geriye-izlemeli
 *   preg_replace desenleri calistirir. Cok buyuk bir sayfada (orn. randevu takvimi:
 *   binlerce "Bos slot" + buyuk inline JSON) PCRE pcre.backtrack_limit asilir ve
 *   preg_replace NULL doner. Vendor middleware bunu kontrol ETMEDEN
 *   setContent($minified) yapar; govde bosalir. Sonuc: BEYAZ EKRAN, exception yok,
 *   laravel.log'a HICBIR iz dusmez. Yalnizca en buyuk HTML'e sahip salonlarda
 *   (orn. 236 numarali isletme takvimi) tetiklenir.
 *
 * COZUM:
 *   1) Istek basina PCRE backtrack/recursion limitlerini yukselt (buyuk sayfalar
 *      sorunsuz minify olsun).
 *   2) Buna ragmen minify bos/null donerse minify'i YOK SAY ve ORIJINAL icerigi
 *      koru. Byte tasarrufu kaybolur ama sayfa calisir (bos ekran yerine).
 */
class MinifyMiddleware extends BaseMinifyMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($this->isAResponseObject($response) && $this->isAnHtmlResponse($response)) {
            $output = $response->getContent();

            // Buyuk HTML'de preg_replace'in NULL donmesini onle.
            @ini_set('pcre.backtrack_limit', '50000000');
            @ini_set('pcre.recursion_limit', '5000000');

            $minified = $this->html->render($output);

            // Guvenlik agi: minify yine de bos/null dondurduyse (asiri buyuk HTML
            // veya PCRE hatasi) orijinal icerigi koru; ASLA bos govde yollama.
            if (($minified === null || $minified === '') && $output !== '' && $output !== null) {
                // TESHIS: bu satir log'a dustuyse "beyaz ekran" kok nedeni KESIN
                // olarak minify'in bos donmesidir (PCRE backtrack_limit asimi).
                \Log::warning('[MINIFY-BOS] minify null/bos dondu, orijinal korundu', [
                    'url'       => $request->fullUrl(),
                    'html_len'  => strlen($output),
                    'pcre_err'  => preg_last_error(),
                ]);
                $minified = $output;
            }

            $response->setContent($minified);
        }

        return $response;
    }
}
