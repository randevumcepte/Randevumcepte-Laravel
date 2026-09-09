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
        $guard = $this->auth->getDefaultDriver();
        $key = 'password_hash_' . $guard;

        if ($this->auth->viaRemember()) {
            $recaller = $request->cookies->get($this->auth->getRecallerName());
            $parts = $recaller ? explode('|', $recaller) : [];
            $passwordHash = isset($parts[2]) ? $parts[2] : null;

            if ($passwordHash !== null && $passwordHash != $request->user()->getAuthPassword()) {
                $this->logout($request);
            }
        }

        // Kiyaslanacak deger: parola hash'i + (isletmeyonetim icin) erisim-iptal
        // damgasi. Damga oturumHashDamgasi() ile hesaplanir; personel pasif/silme
        // sonrasi OturumServisi bu damgayi degistirir -> oturum sonlanir. Diger
        // guard'larda (satisortakligi/sistemyonetim) davranis eskisi gibi: yalniz
        // parola hash'i (parola degisimi hala yakalanir).
        $damga = $this->oturumDamgasi($request->user(), $guard);

        if (! $request->session()->has($key)) {
            $request->session()->put($key, $damga);
        }

        if ($request->session()->get($key) !== $damga) {
            $this->logout($request);
        }

        return tap($next($request), function ($response) use ($request, $key, $guard) {
            if ($request->user()) {
                // NOT: Damgayi burada HER istekte YENIDEN YAZMIYORUZ (Laravel'in
                // logoutOtherDevices davranisi). Boyle olsaydi, parolayi DEGISTIREN
                // oturum kendi damgasini yeni degere guncelleyip acik kalirdi ("mevcut
                // oturumu koru"). Kullanici talebi: parola degisince o oturum DA atilsin.
                // Bu yuzden damga yalniz login'de bir kez tohumlanir (yukarida); parola
                // (veya personel pasif/silme -> oturum_iptal_tarihi) degisince tohumlanan
                // damga ile uyusmayan TUM oturumlar (degisiteni dahil) sonlanir.

                // SAFARI FIX: kimligi dogrulanmis panel sayfalarini onbelleklenemez
                // yap. Aksi halde Safari, reload'da sayfayi HTTP disk cache'inden /
                // bfcache'ten sunup sunucuya HIC GITMIYOR -> bu middleware'in
                // oturum-iptal (parola degisimi / personel pasif-silme) kontrolu
                // calismiyor ve oturum sonlanmis gibi gorunmuyordu. Chrome reload'da
                // revalidate ettigi icin dogru calisiyordu. no-store hem HTTP cache'i
                // hem Safari bfcache'ini keser. Public/mini-site yanitlarina dokunmaz
                // (orada $request->user() bostur -> bu blok calismaz), SEO cache'i korunur.
                if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
                    $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
                    $response->headers->set('Pragma', 'no-cache');
                    $response->headers->set('Expires', '0');
                }
            }
        });
    }

    /**
     * Oturum-gecerlilik damgasi. isletmeyonetim guard'inda parola hash'i + erisim
     * iptal damgasi (oturumHashDamgasi); diger guard'larda yalniz parola hash'i.
     */
    protected function oturumDamgasi($user, $guard)
    {
        if ($guard === 'isletmeyonetim' && method_exists($user, 'oturumHashDamgasi')) {
            return $user->oturumHashDamgasi();
        }
        return $user->getAuthPassword();
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
