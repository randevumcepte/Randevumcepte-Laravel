<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SantralAccess
{
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization') ?? $request->input('token');

        // Flutter API token kontrolü
        if ($token === 'Bearer ' . env('SANTRAL_API_TOKEN', 'secret123')) {
            return $next($request);
        }

        // Web kullanıcı kontrolü
        if (!Auth::guard('isletmeyonetim')->check() && !Auth::guard('satisortakligi')->check()) {
            return redirect('/isletmeyonetim/girisyap');
        }

        return $next($request);
    }
}
