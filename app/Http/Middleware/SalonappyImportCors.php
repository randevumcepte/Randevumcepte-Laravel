<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Salonappy import endpoint'leri icin CORS izni.
 * Tarayici konsolundan webapp.salonappy.com origin'inden POST yapilabilmesi icin.
 */
class SalonappyImportCors
{
    public function handle(Request $request, Closure $next)
    {
        // Preflight OPTIONS
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 204, $this->corsHeaders());
        }

        $response = $next($request);
        foreach ($this->corsHeaders() as $k => $v) {
            $response->headers->set($k, $v);
        }
        return $response;
    }

    private function corsHeaders()
    {
        return [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, Accept',
            'Access-Control-Max-Age'           => '86400',
        ];
    }
}
