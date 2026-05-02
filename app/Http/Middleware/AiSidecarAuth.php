<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * AI sesli asistan sidecar'ından gelen istekleri Bearer token ile dogrular.
 * .env'de AI_SIDECAR_TOKEN tanimli olmalidir.
 */
class AiSidecarAuth
{
    public function handle(Request $request, Closure $next)
    {
        $expected = env('AI_SIDECAR_TOKEN');
        if (empty($expected)) {
            return response()->json([
                'ok' => false,
                'mesaj' => 'AI_SIDECAR_TOKEN .env\'de tanimli degil'
            ], 500);
        }

        $authHeader = $request->header('Authorization', '');
        $sent = '';
        if (stripos($authHeader, 'Bearer ') === 0) {
            $sent = trim(substr($authHeader, 7));
        } else {
            $sent = $request->header('X-Sidecar-Token', '');
        }

        if (!hash_equals((string) $expected, (string) $sent)) {
            return response()->json([
                'ok' => false,
                'mesaj' => 'Yetkisiz: token gecersiz'
            ], 401);
        }

        return $next($request);
    }
}
