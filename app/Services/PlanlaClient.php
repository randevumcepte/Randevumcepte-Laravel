<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * Planla.co istemcisi.
 * - Login olur, cookie jar tutar
 * - Yaygin endpoint pattern'larini dener (probe)
 * - Tum istek/cevaplari dump eder (iterasyon icin)
 *
 * Gercek endpoint'ler ogrenildikten sonra fetchMusteriler/fetchRandevular/fetchHizmetler
 * metodlari concrete hale getirilecek.
 */
class PlanlaClient
{
    const BASE_ADMIN = 'https://admin.planla.co';
    const BASE_WWW   = 'https://planla.co';

    /** @var Client */
    private $http;
    /** @var CookieJar */
    private $jar;
    /** @var string */
    private $email;
    /** @var string */
    private $password;
    /** @var string|null */
    private $bearer;
    /** @var string|null */
    private $xsrf;
    /** @var string */
    private $dumpDir;

    public function __construct($email, $password, $dumpDir = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->jar = new CookieJar();
        $this->dumpDir = $dumpDir ?: storage_path('app/planla/' . date('Ymd_His'));
        if (!is_dir($this->dumpDir)) {
            @mkdir($this->dumpDir, 0775, true);
        }

        $this->http = new Client([
            'base_uri'        => self::BASE_ADMIN,
            'cookies'         => $this->jar,
            'allow_redirects' => ['max' => 5, 'track_redirects' => true],
            'timeout'         => 30,
            'http_errors'     => false,
            'verify'          => false,
            'headers'         => [
                'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36',
                'Accept-Language' => 'tr-TR,tr;q=0.9,en;q=0.8',
            ],
        ]);
    }

    public function dumpDir()
    {
        return $this->dumpDir;
    }

    /**
     * Login dener. Once login sayfasini cekip CSRF token + form action'i cikarmaya calisir,
     * sonra yaygin JSON API endpoint'lerini dener.
     * Basarili olursa cookie jar'a session + varsa Bearer token set edilir.
     *
     * @return array ['ok' => bool, 'method' => string, 'detail' => string]
     */
    public function login()
    {
        // 1) Login sayfasini cek ve dump et
        $signInHtml = $this->getHtml('/sign-in', 'sign-in');

        // Muhtemel CSRF token / XSRF-TOKEN cookie
        $xsrfFromCookie = $this->cookieValue('XSRF-TOKEN');
        if ($xsrfFromCookie) {
            $this->xsrf = urldecode($xsrfFromCookie);
        }

        $csrfMeta = null;
        if (preg_match('/name="csrf-token"\s+content="([^"]+)"/i', $signInHtml, $m)) {
            $csrfMeta = $m[1];
        }
        if (!$csrfMeta && preg_match('/name="_token"\s+value="([^"]+)"/i', $signInHtml, $m)) {
            $csrfMeta = $m[1];
        }

        // 2) JSON API login denemeleri (Next.js / React SPA olma ihtimaline karsi)
        $jsonLoginEndpoints = [
            '/api/auth/login',
            '/api/auth/signin',
            '/api/v1/auth/login',
            '/api/login',
            '/auth/login',
            '/api/user/login',
            '/api/sign-in',
        ];
        foreach ($jsonLoginEndpoints as $path) {
            $res = $this->tryJsonLogin($path, $csrfMeta);
            if ($res['ok']) {
                return ['ok' => true, 'method' => 'json:' . $path, 'detail' => $res['detail']];
            }
        }

        // 3) Form post (SSR klasik uygulama varsa)
        $formPaths = ['/sign-in', '/login', '/auth/login'];
        foreach ($formPaths as $path) {
            $res = $this->tryFormLogin($path, $csrfMeta);
            if ($res['ok']) {
                return ['ok' => true, 'method' => 'form:' . $path, 'detail' => $res['detail']];
            }
        }

        return ['ok' => false, 'method' => 'none', 'detail' => 'Login denemeleri basarisiz. ' . $this->dumpDir . ' icinde response dumplarini inceleyin.'];
    }

    private function tryJsonLogin($path, $csrfMeta = null)
    {
        $variants = [
            ['email' => $this->email, 'password' => $this->password],
            ['username' => $this->email, 'password' => $this->password],
            ['eposta' => $this->email, 'sifre' => $this->password],
            ['mail' => $this->email, 'password' => $this->password],
        ];
        foreach ($variants as $idx => $body) {
            $headers = [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
                'Referer'      => self::BASE_ADMIN . '/sign-in',
                'Origin'       => self::BASE_ADMIN,
            ];
            if ($this->xsrf) {
                $headers['X-XSRF-TOKEN'] = $this->xsrf;
            }
            if ($csrfMeta) {
                $headers['X-CSRF-TOKEN'] = $csrfMeta;
            }

            try {
                $resp = $this->http->post($path, [
                    'headers' => $headers,
                    'body'    => json_encode($body),
                ]);
            } catch (RequestException $e) {
                continue;
            }

            $status = $resp->getStatusCode();
            $body   = (string) $resp->getBody();
            $this->dump("login_json_{$this->slug($path)}_v{$idx}_{$status}", $body, $resp->getHeaders());
            if ($this->bodyIndicatesFailure($body)) {
                continue;
            }

            if ($status >= 200 && $status < 300) {
                // Token arama
                $j = json_decode($body, true);
                if (is_array($j)) {
                    foreach (['token', 'access_token', 'accessToken', 'jwt', 'bearer'] as $k) {
                        if (!empty($j[$k])) { $this->bearer = $j[$k]; break; }
                        if (isset($j['data'][$k])) { $this->bearer = $j['data'][$k]; break; }
                    }
                }
                // Login sonrasi dashboard cek, gercekten giris olmus mu kontrol
                if ($this->isLoggedIn()) {
                    return ['ok' => true, 'detail' => "JSON login ok via {$path} variant {$idx}" . ($this->bearer ? ' (bearer alindi)' : '')];
                }
            }
        }
        return ['ok' => false, 'detail' => ''];
    }

    private function tryFormLogin($path, $csrfMeta = null)
    {
        $variants = [
            ['email' => $this->email, 'password' => $this->password],
            ['eposta' => $this->email, 'sifre' => $this->password],
            ['username' => $this->email, 'password' => $this->password],
        ];
        foreach ($variants as $idx => $body) {
            if ($csrfMeta) {
                $body['_token'] = $csrfMeta;
            }
            $headers = [
                'Referer' => self::BASE_ADMIN . '/sign-in',
                'Origin'  => self::BASE_ADMIN,
            ];
            try {
                $resp = $this->http->post($path, [
                    'headers'     => $headers,
                    'form_params' => $body,
                ]);
            } catch (RequestException $e) {
                continue;
            }
            $status = $resp->getStatusCode();
            $body = (string) $resp->getBody();
            $dumpName = "login_form_{$this->slug($path)}_v{$idx}_{$status}";
            $this->dump($dumpName, $body, $resp->getHeaders());
            if ($this->bodyIndicatesFailure($body)) {
                continue;
            }
            if ($status >= 200 && $status < 400) {
                if ($this->isLoggedIn()) {
                    return ['ok' => true, 'detail' => "Form login ok via {$path} variant {$idx}"];
                }
            }
        }
        return ['ok' => false, 'detail' => ''];
    }

    private function bodyIndicatesFailure($body)
    {
        if ($body === '') return false;
        $j = json_decode($body, true);
        if (is_array($j)) {
            if (isset($j['success']) && $j['success'] === false) return true;
            if (isset($j['status']) && in_array($j['status'], ['error', 'fail'], true)) return true;
            if (!empty($j['error']) && empty($j['token']) && empty($j['access_token'])) return true;
        }
        return false;
    }

    /**
     * Login basarili mi? Dashboard/anasayfa/profil endpoint'lerinden biri 200 donuyor ve
     * /sign-in redirect'i yoksa basarili.
     */
    public function isLoggedIn()
    {
        $checks = ['/', '/dashboard', '/anasayfa', '/api/user', '/api/me', '/api/auth/me', '/api/auth/user'];
        foreach ($checks as $path) {
            try {
                $resp = $this->http->get($path, ['headers' => $this->authHeaders()]);
            } catch (RequestException $e) {
                continue;
            }
            $status = $resp->getStatusCode();
            $body = (string) $resp->getBody();
            // Redirect track: eger /sign-in'a atiyorsa giris yok
            $redirects = $resp->getHeader('X-Guzzle-Redirect-History');
            $endedAtSignIn = false;
            foreach ($redirects as $r) {
                if (stripos($r, 'sign-in') !== false || stripos($r, 'login') !== false) {
                    $endedAtSignIn = true;
                }
            }
            // SPA shell'de isAuthenticated:false varsa login OLMAMISTIR (false positif koruma)
            if (stripos($body, '"isAuthenticated":false') !== false) {
                continue;
            }
            if ($status === 200 && stripos($body, '"isAuthenticated":true') !== false) {
                return true;
            }
            if ($status === 200 && !$endedAtSignIn && stripos($body, 'sign-in') === false && !$this->looksLikeJson($body)) {
                // SSR rendered page (non-SPA) ve sign-in redirect yok
                return true;
            }
            if ($status === 200 && $this->looksLikeJson($body)) {
                $j = json_decode($body, true);
                if (is_array($j)) {
                    if (isset($j['success']) && $j['success'] === false) continue;
                    if (!empty($j['error'])) continue;
                    if (isset($j['id']) || isset($j['user']) || isset($j['data']) || isset($j['email'])) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Probe: yaygin veri endpoint'lerini cek ve dump et.
     * Gercek endpoint'ler tespit edilip fetch* metodlari yazildiginda artik gerekli degil.
     */
    public function probe()
    {
        $pages = [
            // JSON API candidates
            '/api/musteriler', '/api/customers', '/api/clients', '/api/users',
            '/api/v1/musteriler', '/api/v1/customers',
            '/api/randevular', '/api/appointments', '/api/bookings',
            '/api/v1/randevular', '/api/v1/appointments',
            '/api/hizmetler', '/api/services',
            '/api/v1/hizmetler', '/api/v1/services',
            '/api/personeller', '/api/staff', '/api/employees',
            '/api/v1/personeller',
            '/api/settings', '/api/profile', '/api/account',
            '/api/kategoriler', '/api/categories',
            // SSR HTML candidates
            '/musteriler', '/customers',
            '/randevular', '/appointments',
            '/hizmetler', '/services',
            '/personeller', '/staff',
            '/dashboard', '/panel', '/anasayfa',
        ];

        $results = [];
        $i = 0;
        foreach ($pages as $p) {
            try {
                $resp = $this->http->get($p, ['headers' => $this->authHeaders()]);
            } catch (RequestException $e) {
                $results[$p] = 'EXC:' . $e->getMessage();
                continue;
            }
            $status = $resp->getStatusCode();
            $body   = (string) $resp->getBody();
            $ctype  = $resp->getHeaderLine('Content-Type');
            $len    = strlen($body);
            $slug   = "probe_{$this->slug($p)}_{$status}";
            $this->dump($slug, $body, $resp->getHeaders());
            $results[$p] = "status={$status} len={$len} type={$ctype}";
            if (++$i % 10 === 0) usleep(500000); // 10 istekte bir 0.5sn bekle (rate limit: 30/dk)
        }
        return $results;
    }

    /**
     * Site.js bundle'ini indirir ve icinden /api, login payload anahtarlari, URL pattern'larini cikarir.
     * Bundle analizinde login endpoint + gercek data endpoint'leri tespit edilir.
     */
    public function analyzeBundle($bundleUrl = null)
    {
        if (!$bundleUrl) $bundleUrl = self::BASE_ADMIN . '/Site.js?v=862';
        try {
            $resp = $this->http->get($bundleUrl);
        } catch (RequestException $e) {
            return ['ok' => false, 'detail' => $e->getMessage()];
        }
        $js = (string) $resp->getBody();
        $this->dump('bundle_site_js', $js, $resp->getHeaders());

        $endpoints = [];
        if (preg_match_all('#["\'`](/api/[a-zA-Z0-9/_\-.$?={}:]+)["\'`]#', $js, $m)) {
            $endpoints = array_values(array_unique($m[1]));
        }
        $urls = [];
        if (preg_match_all('#https?://[a-zA-Z0-9.\-]+\.planla[a-zA-Z0-9./?=_\-]*#', $js, $m)) {
            $urls = array_values(array_unique($m[0]));
        }
        $payloadHints = [];
        if (preg_match_all('#\{[^{}]{0,200}(password|sifre)[^{}]{0,200}\}#', $js, $m)) {
            $payloadHints = array_slice(array_values(array_unique($m[0])), 0, 10);
        }
        $loginPaths = [];
        foreach ($endpoints as $e) {
            if (preg_match('#(sign|login|auth)#i', $e)) $loginPaths[] = $e;
        }

        $summary = [
            'bundle_size'    => strlen($js),
            'login_paths'    => $loginPaths,
            'api_endpoints'  => $endpoints,
            'planla_urls'    => $urls,
            'payload_hints'  => $payloadHints,
        ];
        $this->dump('bundle_analysis', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return ['ok' => true, 'summary' => $summary];
    }

    public function getHtml($path, $tag = null)
    {
        try {
            $resp = $this->http->get($path, ['headers' => $this->authHeaders()]);
        } catch (RequestException $e) {
            return '';
        }
        $body = (string) $resp->getBody();
        if ($tag !== null) {
            $this->dump("get_{$this->slug($path)}_{$tag}", $body, $resp->getHeaders());
        }
        return $body;
    }

    public function getJson($path, array $query = [])
    {
        try {
            $resp = $this->http->get($path, [
                'headers' => array_merge($this->authHeaders(), ['Accept' => 'application/json']),
                'query'   => $query,
            ]);
        } catch (RequestException $e) {
            Log::warning('planla getJson exception: ' . $e->getMessage());
            return null;
        }
        $body = (string) $resp->getBody();
        $j = json_decode($body, true);
        return is_array($j) ? $j : null;
    }

    private function authHeaders()
    {
        $h = [];
        if ($this->bearer) {
            $h['Authorization'] = 'Bearer ' . $this->bearer;
        }
        if ($this->xsrf) {
            $h['X-XSRF-TOKEN'] = $this->xsrf;
        }
        return $h;
    }

    private function cookieValue($name)
    {
        foreach ($this->jar->toArray() as $c) {
            if ($c['Name'] === $name) {
                return $c['Value'];
            }
        }
        return null;
    }

    private function looksLikeJson($body)
    {
        $s = ltrim($body);
        return $s !== '' && ($s[0] === '{' || $s[0] === '[');
    }

    private function slug($s)
    {
        $s = preg_replace('/[^a-zA-Z0-9]+/', '_', $s);
        return trim($s, '_');
    }

    private function dump($name, $body, $headers = [])
    {
        $file = $this->dumpDir . '/' . $name;
        @file_put_contents($file . '.body', $body);
        if ($headers) {
            $h = [];
            foreach ($headers as $k => $vals) {
                $h[] = $k . ': ' . (is_array($vals) ? implode(', ', $vals) : $vals);
            }
            @file_put_contents($file . '.headers', implode("\n", $h));
        }
    }
}
