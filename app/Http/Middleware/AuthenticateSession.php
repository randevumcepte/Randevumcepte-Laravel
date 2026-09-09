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
 *
 * PAROLA DEGISINCE OTURUM SONLANDIRMA (kullanici talebi):
 * logout() artik AKTIF. Parola degistiginde, o hesabin parola-degisimini YAPAN
 * oturum haric (o oturum tap() ile yeni hash'i alir) diger tum web oturumlari
 * bir sonraki isteklerinde hash uyusmazligina dusup cikis yapar (Laravel'in
 * kanonik logoutOtherDevices mekanizmasi). Eski "rastgele atma" agrisinin iki
 * nedeni de artik yok: (1) cok-guard karismasi -> password_hash_{guard} ayrimi,
 * (2) impersonation -> asagidaki sysadmin_impersonation_id bypass'i. Bu yuzden
 * logout, tum oturumu flush etmek yerine SADECE ilgili guard'i cikarir; ayni
 * oturumda mesru olarak aktif baska guard (or. sistemyonetim) varsa korunur.
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
        // IMPERSONATION BYPASS: "salon hesabina gir" sirasinda ayni oturumda
        // birden cok guard (sistemyonetim + isletmeyonetim) aktif. Cok-guard'li
        // hash celiskisi yuzunden bu middleware oturumu flush edip kullaniciyi
        // ATIYORDU. Impersonation bir sysadmin islemi (guvenilir); bu sirada
        // parola-degisti-cikis kontrolunu komple atla -> flush imkansiz.
        if ($request->session() && $request->session()->has('sysadmin_impersonation_id')) {
            return $next($request);
        }

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
     * Parola-hash uyusmazliginda SADECE ilgili guard'in oturumunu sonlandirir.
     *
     * Laravel'in orijinali burada tum session'i flush() edip regenerate() yapar;
     * bu uygulamada ayni oturumda birden cok guard mesru olarak aktif olabildigi
     * icin (or. sistemyonetim), full flush yanlis guard'i da atardi. Bunun yerine
     * yalnizca aktif guard cikarilir + o guard'in hash anahtari silinir, sonra
     * guard'a duyarli AuthenticationException firlatilir (Handler dogru login
     * sayfasina yonlendirir; AJAX icin 401 doner). Impersonation zaten handle()
     * basinda bypass edildigi icin buraya impersonation isteklerinde girilmez.
     */
    protected function logout($request)
    {
        $guard = $this->auth->getDefaultDriver();
        try {
            $this->auth->guard($guard)->logout();
            $request->session()->forget('password_hash_' . $guard);
            try { \Log::info('[AuthSession] parola degisti -> oturum sonlandirildi guard=' . $guard); } catch (\Throwable $e) {}
        } catch (\Throwable $e) {}

        throw new AuthenticationException('Unauthenticated.', [$guard]);
    }
}
