<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

/**
 * Guard'a duyarli AuthenticateSession.
 *
 * Laravel 5.6'nin orijinali (Illuminate\Session\Middleware\AuthenticateSession)
 * session'da TEK global 'password_hash' anahtari kullanir. Bu uygulamada ayni
 * oturumda birden cok guard (sistemyonetim + isletmeyonetim + satisortakligi)
 * ayni anda aktif olabiliyor — ozellikle "salon hesabina gir" (impersonation)
 * sirasinda hem admin (sistemyonetim) hem isletme kullanicisi (isletmeyonetim)
 * ayni oturumda. Tek 'password_hash' anahtari yuzunden, default guard her
 * istekte degisince ($request->user() bir istekte admin, digerinde isletme
 * kullanicisi) hash uyusmazligi olusuyor ve middleware butun oturumu flush edip
 * kullaniciyi atiyordu ("bir iki islemden sonra oturum sonlaniyor").
 *
 * Cozum: hash anahtarini guard adina gore ayir -> password_hash_{guard}
 * (Laravel 6+ ile gelen davranis). Boylece her guard kendi hash'ini tutar;
 * impersonation sirasinda iki oturum birbirini atmaz, password degisiminde
 * oturum gecersiz kilma guvenligi de korunur.
 */
class AuthenticateSession
{
    /**
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    public function __construct(AuthFactory $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next)
    {
        if (! $request->user() || ! $request->session()) {
            return $next($request);
        }

        // Aktif guard (auth:<guard> middleware'i shouldUse ile burayi set eder)
        $key = 'password_hash_' . $this->auth->getDefaultDriver();

        if ($this->auth->viaRemember()) {
            $recaller = $request->cookies->get($this->auth->getRecallerName());
            $parts = $recaller ? explode('|', $recaller) : [];
            $passwordHash = isset($parts[2]) ? $parts[2] : null;

            if ($passwordHash !== null && $passwordHash != $request->user()->getAuthPassword()) {
                $this->logout($request);
            }
        }

        if (! $request->session()->has($key)) {
            $request->session()->put($key, $request->user()->getAuthPassword());
        }

        if ($request->session()->get($key) !== $request->user()->getAuthPassword()) {
            $this->logout($request);
        }

        return tap($next($request), function () use ($request, $key) {
            if ($request->user()) {
                $request->session()->put($key, $request->user()->getAuthPassword());
            }
        });
    }

    /**
     * Kullaniciyi cikar (aktif guard) ve oturumu temizle.
     */
    protected function logout($request)
    {
        $this->auth->logout();

        $request->session()->flush();

        throw new AuthenticationException;
    }
}
