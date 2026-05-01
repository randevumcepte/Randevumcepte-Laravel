<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * uygulama.drklinik.net istemcisi.
 * - Login olur, cookie jar tutar
 * - Bundle/HTML analiziyle endpoint kesfi yapar
 * - Yaygin endpoint pattern'larini dener (probe)
 * - Tum istek/cevaplari dump eder (iterasyon icin)
 */
class DrklinikClient
{
    const BASE = 'https://uygulama.drklinik.net';

    /** @var Client */
    private $http;
    /** @var CookieJar */
    private $jar;
    /** @var string */
    private $username;
    /** @var string */
    private $password;
    /** @var string|null */
    private $bearer;
    /** @var string|null */
    private $xsrf;
    /** @var string */
    private $dumpDir;

    public function __construct($username, $password, $dumpDir = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->jar = new CookieJar();
        $this->dumpDir = $dumpDir ?: storage_path('app/drklinik/' . date('Ymd_His'));
        if (!is_dir($this->dumpDir)) {
            @mkdir($this->dumpDir, 0775, true);
        }

        $this->http = new Client([
            'base_uri'        => self::BASE,
            'cookies'         => $this->jar,
            'allow_redirects' => ['max' => 5, 'track_redirects' => true],
            'timeout'         => 300,
            'connect_timeout' => 30,
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
     * Login dener. Anasayfa/login sayfasi cekilir, CSRF token bulunur, sonra
     * yaygin JSON ve form login endpoint varyantlari denenir.
     */
    public function login()
    {
        // Anasayfayi cek (genelde login form burada)
        $homeHtml = $this->getHtml('/', 'home');
        $loginHtml = $this->getHtml('/login', 'login');
        $signInHtml = $this->getHtml('/sign-in', 'sign-in');

        $xsrfFromCookie = $this->cookieValue('XSRF-TOKEN');
        if ($xsrfFromCookie) {
            $this->xsrf = urldecode($xsrfFromCookie);
        }

        $csrfMeta = null;
        foreach ([$homeHtml, $loginHtml, $signInHtml] as $html) {
            if ($html === '') continue;
            if (preg_match('/name="csrf-token"\s+content="([^"]+)"/i', $html, $m)) { $csrfMeta = $m[1]; break; }
            if (preg_match('/name="_token"\s+value="([^"]+)"/i', $html, $m)) { $csrfMeta = $m[1]; break; }
        }

        // JSON login adaylari
        $jsonPaths = [
            '/login', '/api/login', '/api/auth/login', '/api/v1/auth/login',
            '/auth/login', '/api/user/login', '/sign-in', '/api/sign-in',
        ];
        foreach ($jsonPaths as $path) {
            $res = $this->tryJsonLogin($path, $csrfMeta);
            if ($res['ok']) {
                return ['ok' => true, 'method' => 'json:' . $path, 'detail' => $res['detail']];
            }
        }

        // Form login adaylari
        $formPaths = ['/login', '/sign-in', '/auth/login', '/giris', '/oturum-ac'];
        foreach ($formPaths as $path) {
            $res = $this->tryFormLogin($path, $csrfMeta);
            if ($res['ok']) {
                return ['ok' => true, 'method' => 'form:' . $path, 'detail' => $res['detail']];
            }
        }

        return ['ok' => false, 'method' => 'none', 'detail' => 'Login denemeleri basarisiz. ' . $this->dumpDir . ' icindeki response dumplarini inceleyin.'];
    }

    private function tryJsonLogin($path, $csrfMeta = null)
    {
        $variants = [
            ['username' => $this->username, 'password' => $this->password],
            ['email' => $this->username, 'password' => $this->password],
            ['kullanici_adi' => $this->username, 'sifre' => $this->password],
            ['user' => $this->username, 'password' => $this->password],
        ];
        foreach ($variants as $idx => $body) {
            $headers = [
                'Accept'       => 'application/json, text/plain, */*',
                'Content-Type' => 'application/json',
                'Referer'      => self::BASE . '/',
                'Origin'       => self::BASE,
            ];
            if ($this->xsrf)   $headers['X-XSRF-TOKEN'] = $this->xsrf;
            if ($csrfMeta)     $headers['X-CSRF-TOKEN'] = $csrfMeta;

            try {
                $resp = $this->http->post($path, [
                    'headers' => $headers,
                    'body'    => json_encode($body),
                ]);
            } catch (RequestException $e) { continue; }

            $status = $resp->getStatusCode();
            $b = (string) $resp->getBody();
            $this->dump("login_json_{$this->slug($path)}_v{$idx}_{$status}", $b, $resp->getHeaders());
            if ($this->bodyIndicatesFailure($b)) continue;

            if ($status >= 200 && $status < 300) {
                $j = json_decode($b, true);
                if (is_array($j)) {
                    foreach (['token', 'access_token', 'accessToken', 'jwt', 'bearer'] as $k) {
                        if (!empty($j[$k])) { $this->bearer = $j[$k]; break; }
                        if (isset($j['data'][$k])) { $this->bearer = $j['data'][$k]; break; }
                    }
                }
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
            ['username' => $this->username, 'password' => $this->password],
            ['email' => $this->username, 'password' => $this->password],
            ['kullanici_adi' => $this->username, 'sifre' => $this->password],
            ['user' => $this->username, 'password' => $this->password],
        ];
        foreach ($variants as $idx => $body) {
            if ($csrfMeta) $body['_token'] = $csrfMeta;
            $headers = ['Referer' => self::BASE . '/', 'Origin' => self::BASE];
            try {
                $resp = $this->http->post($path, ['headers' => $headers, 'form_params' => $body]);
            } catch (RequestException $e) { continue; }
            $status = $resp->getStatusCode();
            $b = (string) $resp->getBody();
            $this->dump("login_form_{$this->slug($path)}_v{$idx}_{$status}", $b, $resp->getHeaders());
            if ($this->bodyIndicatesFailure($b)) continue;
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
        // HTML icinde "Hatali sifre", "kullanici bulunamadi" gibi tipik mesajlar
        if (stripos($body, 'hatali') !== false && stripos($body, 'sifre') !== false) return true;
        if (stripos($body, 'invalid credentials') !== false) return true;
        return false;
    }

    public function isLoggedIn()
    {
        $checks = ['/', '/dashboard', '/anasayfa', '/panel', '/api/user', '/api/me', '/api/auth/me'];
        foreach ($checks as $path) {
            try {
                $resp = $this->http->get($path, ['headers' => $this->authHeaders()]);
            } catch (RequestException $e) { continue; }
            $status = $resp->getStatusCode();
            $body = (string) $resp->getBody();
            $redirects = $resp->getHeader('X-Guzzle-Redirect-History');
            $endedAtSignIn = false;
            foreach ($redirects as $r) {
                if (stripos($r, 'login') !== false || stripos($r, 'sign-in') !== false || stripos($r, 'giris') !== false) {
                    $endedAtSignIn = true;
                }
            }
            if (stripos($body, '"isAuthenticated":false') !== false) continue;
            if ($status === 200 && stripos($body, '"isAuthenticated":true') !== false) return true;
            if ($status === 200 && !$endedAtSignIn && stripos($body, 'login') === false && stripos($body, 'sign-in') === false && !$this->looksLikeJson($body)) {
                return true;
            }
            if ($status === 200 && $this->looksLikeJson($body)) {
                $j = json_decode($body, true);
                if (is_array($j)) {
                    if (isset($j['success']) && $j['success'] === false) continue;
                    if (!empty($j['error'])) continue;
                    if (isset($j['id']) || isset($j['user']) || isset($j['data']) || isset($j['email'])) return true;
                }
            }
        }
        return false;
    }

    /**
     * Yaygin data endpoint'leri tarar. Hepsine GET + Accept:json gonderir,
     * gelen icerigin JSON olup olmadigini dumpler.
     */
    public function probe()
    {
        $pages = [
            // PHP/Laravel klasik
            '/musteriler', '/randevular', '/hizmetler', '/personeller', '/tahsilatlar',
            '/uygulama/musteriler', '/uygulama/randevular', '/uygulama/hizmetler',
            // API katmani
            '/api/musteriler', '/api/randevular', '/api/hizmetler', '/api/personeller',
            '/api/v1/musteriler', '/api/v1/randevular',
            '/api/customers', '/api/appointments', '/api/services', '/api/staff',
            // Dashboard/anasayfa
            '/dashboard', '/anasayfa', '/panel',
            // Reports/raporlar
            '/raporlar', '/api/raporlar', '/istatistik',
        ];

        $results = [];
        $jsonHeaders = array_merge($this->authHeaders(), [
            'Accept'           => 'application/json, text/plain, */*',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer'          => self::BASE . '/',
            'Origin'           => self::BASE,
        ]);
        $i = 0;
        foreach ($pages as $p) {
            try {
                $resp = $this->http->get($p, ['headers' => $jsonHeaders]);
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
            $isJson = stripos($ctype, 'json') !== false;
            $marker = $isJson ? ' [JSON!]' : '';
            $results[$p] = "status={$status} len={$len} type={$ctype}" . $marker;
            if (++$i % 10 === 0) usleep(500000);
        }
        return $results;
    }

    /**
     * Anasayfa/HTML icinde JS bundle URL'leri ve API pattern'larini arar.
     */
    public function analyze()
    {
        $home = $this->getHtml('/', 'home');
        if ($home === '') return ['ok' => false, 'detail' => 'Anasayfa cekilemedi'];

        // JS/CSS asset URL'leri
        $assets = [];
        if (preg_match_all('#(?:src|href)\s*=\s*["\']([^"\']+\.(?:js|css)[^"\']*)["\']#i', $home, $m)) {
            $assets = array_values(array_unique($m[1]));
        }

        // API ve veri pattern'lari
        $apiPaths = [];
        if (preg_match_all('#["\'`](/(?:api|uygulama|admin)/[a-zA-Z0-9/_\-.]+)["\'`]#', $home, $m)) {
            $apiPaths = array_values(array_unique($m[1]));
        }

        // Bundle dosyalarini indir + onlarda arama
        $bundleFindings = [];
        foreach ($assets as $a) {
            $url = $a;
            if (strpos($a, 'http') !== 0) $url = (strpos($a, '/') === 0 ? self::BASE : self::BASE . '/') . ltrim($a, '/');
            if (!preg_match('#\.js(\?|$)#i', $url)) continue;
            try {
                $resp = $this->http->get($url);
            } catch (RequestException $e) { continue; }
            if ($resp->getStatusCode() !== 200) continue;
            $js = (string) $resp->getBody();
            if (strlen($js) < 5000) continue; // ufak chunk'lari atla
            $name = preg_replace('#[^a-zA-Z0-9._-]#', '_', basename(parse_url($url, PHP_URL_PATH)));
            $this->dump('bundle_' . substr($name, 0, 60), $js, $resp->getHeaders());

            $hits = [];
            // axios.get/post(...)
            if (preg_match_all('#axios\.(?:get|post|put|delete|patch)\(["\'`]([^"\'`]+)["\'`]#i', $js, $m)) $hits = array_merge($hits, $m[1]);
            // fetch("...")
            if (preg_match_all('#fetch\(\s*["\'`]([^"\'`]+)["\'`]#i', $js, $m)) $hits = array_merge($hits, $m[1]);
            // url:"..."
            if (preg_match_all('#url\s*:\s*["\'`](/[^"\'`]{2,80})["\'`]#i', $js, $m)) $hits = array_merge($hits, $m[1]);
            // route("...") veya route name
            if (preg_match_all('#route\(\s*["\'`]([^"\'`]+)["\'`]#i', $js, $m)) $hits = array_merge($hits, $m[1]);
            $hits = array_values(array_unique($hits));
            if ($hits) $bundleFindings[$url] = $hits;
        }

        return ['ok' => true, 'summary' => [
            'home_size'       => strlen($home),
            'assets'          => $assets,
            'api_paths_html'  => $apiPaths,
            'bundle_findings' => $bundleFindings,
        ]];
    }

    public function getHtml($path, $tag = null)
    {
        try { $resp = $this->http->get($path, ['headers' => $this->authHeaders()]); }
        catch (RequestException $e) { return ''; }
        $body = (string) $resp->getBody();
        if ($tag !== null) $this->dump("get_{$this->slug($path)}_{$tag}", $body, $resp->getHeaders());
        return $body;
    }

    public function getJson($path, array $query = [])
    {
        try {
            $resp = $this->http->get($path, [
                'headers' => array_merge($this->authHeaders(), [
                    'Accept'           => 'application/json, text/plain, */*',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Referer'          => self::BASE . '/',
                ]),
                'query'   => $query,
            ]);
        } catch (RequestException $e) {
            Log::warning('drklinik getJson exception: ' . $e->getMessage());
            return null;
        }
        $body = (string) $resp->getBody();
        $ctype = $resp->getHeaderLine('Content-Type');
        if (stripos($ctype, 'json') === false) return null;
        $j = json_decode($body, true);
        return is_array($j) ? $j : null;
    }

    public function postJson($path, array $body)
    {
        $headers = array_merge($this->authHeaders(), [
            'Accept'           => 'application/json, text/plain, */*',
            'Content-Type'     => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer'          => self::BASE . '/',
            'Origin'           => self::BASE,
        ]);
        try {
            $resp = $this->http->post($path, ['headers' => $headers, 'body' => json_encode($body)]);
        } catch (RequestException $e) {
            Log::warning('drklinik postJson exception: ' . $e->getMessage());
            return null;
        }
        $b = (string) $resp->getBody();
        $this->dump('post_' . $this->slug($path) . '_' . $resp->getStatusCode(), $b, $resp->getHeaders());
        $ctype = $resp->getHeaderLine('Content-Type');
        if (stripos($ctype, 'json') === false) return null;
        $j = json_decode($b, true);
        return is_array($j) ? $j : null;
    }

    private function authHeaders()
    {
        $h = [];
        if ($this->bearer) $h['Authorization'] = 'Bearer ' . $this->bearer;
        if ($this->xsrf)   $h['X-XSRF-TOKEN'] = $this->xsrf;
        return $h;
    }

    private function cookieValue($name)
    {
        foreach ($this->jar->toArray() as $c) {
            if ($c['Name'] === $name) return $c['Value'];
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
            foreach ($headers as $k => $vals) $h[] = $k . ': ' . (is_array($vals) ? implode(', ', $vals) : $vals);
            @file_put_contents($file . '.headers', implode("\n", $h));
        }
    }
}
