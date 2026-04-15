<?php

namespace App\Http\Middleware;

use Closure;

class SetPassportAuthGuard
{
    public function handle($request, Closure $next, $guard = '')
    {
        app('config')->set('auth.passport.guard', $guard);

        return $next($request);
    }
}