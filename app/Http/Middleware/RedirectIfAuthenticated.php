<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                switch ($guard) {
                    case 'isletmeyonetim':
                        return redirect()->route('isletmeadmin.dashboard');
                    case 'sistemyonetim':
                        return redirect()->route('superadmin.dashboard');
                    case 'satisortakligi':
                        return redirect()->route('satisortakligi.dashboard');
                }
            }
        }

        return $next($request);
    }
}
