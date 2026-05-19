<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Log;

/**
 * app.salonrandevu.com istemcisi.
 * - Login (form-based) + cookie jar
 * - --analyze: anasayfa + JS bundle'i tarayip endpoint adaylari cikarir
 * - --probe: yaygin endpoint'leri dener
 * Asama 1: kesif (login + analyze + probe)
 * Asama 2: endpoint'ler ogrenildikten sonra fetchMusteri/fetchHizmet/... metodlari eklenecek
 */
class SalonrandevuClient
{
    const BASE_APP = 'https://app.salonrandevu.com';
    const BASE_WWW = 'https://salonrandevu.com';

    /** @var Client */ private $http;
    /** @var CookieJar */ private $jar;
    /** @var string */ private $email;
    /** @var string */ private $password;
    /** @var string|null */ private $bearer;
    /** @var string|null */ private $xsrf;
    /** @var string */ private $dumpDir;

    public function __construct($email, $password, $dumpDir = null, $proxy = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->jar = new CookieJar();
        $this->dumpDir = $dumpDir ?: storage_path('app/salonrandevu/' . date('Ymd_His'));
        if (!is_dir($this->dumpDir)) @mkdir($this->dumpDir, 0775, true);

        $cfg = [
            'base_uri'        => self::BASE_APP,
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
        ];
        if ($proxy) $cfg['proxy'] = $proxy;
        $this->http = new Client($cfg);
    }

    public function dumpDir() { return $this->dumpDir; }
    public function setBearer($t) { $this->bearer = $t; }

    private function dump($name, $content) {
        @file_put_contents($this->dumpDir . '/' . $name, is_string($content) ? $content : json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    private function cookieValue($name) {
        foreach ($this->jar->toArray() as $c) if ($c['Name'] === $name) return $c['Value'];
        return null;
    }
    private function authHeaders($extra = []) {
        $h = ['Accept' => 'application/json'];
        if ($this->bearer) $h['Authorization'] = 'Bearer ' . $this->bearer;
        if ($this->xsrf) $h['X-XSRF-TOKEN'] = $this->xsrf;
        return array_merge($h, $extra);
    }

    /**
     * GET HTML dump + return body
     */
    private function getHtml($path, $dumpName)
    {
        $r = $this->http->get($path);
        $body = (string) $r->getBody();
        $this->dump("{$dumpName}.status", $r->getStatusCode());
        $this->dump("{$dumpName}.body", $body);
        return $body;
    }

    /**
     * Anasayfa + JS bundle'lerini indirip endpoint adaylarini cikarir.
     * Login olmadan calisir.
     */
    public function analyze()
    {
        $html = $this->getHtml('/', 'home');
        // <script src=...> ve <link href=...> ile tum assetleri topla
        $assets = [];
        if (preg_match_all('/<script[^>]+src="([^"]+\.js)"/i', $html, $m)) $assets = array_merge($assets, $m[1]);
        if (preg_match_all('/<link[^>]+href="([^"]+\.css)"/i', $html, $m)) $assets = array_merge($assets, $m[1]);
        $assets = array_values(array_unique($assets));

        // Bundle'lari indir, regex ile endpoint'leri tara
        $bundleFindings = [];
        $allApiPaths = [];
        foreach ($assets as $a) {
            if (substr($a, -3) !== '.js') continue;
            $url = $a;
            if (strpos($url, 'http') !== 0) $url = (strpos($url, '/') === 0 ? self::BASE_APP : self::BASE_APP . '/') . $url;
            $r = $this->http->get($url);
            $code = (string) $r->getBody();
            $name = basename(parse_url($url, PHP_URL_PATH) ?: $url);
            $this->dump('asset_' . $name, $code);

            $hits = [];
            // path string'leri: '/api/...', '/v1/...', '/setup/...'
            if (preg_match_all('~["\'`](/(?:api|v1|v2|setup|customer|client|musteri|booking|visit|appointment|randevu|service|hizmet|staff|personel|payment|odeme|tahsilat|product|urun|package|paket|expense|gider|salon|business|admin|dashboard|auth|login)[a-zA-Z0-9_\-/\.]{2,80})["\'`]~', $code, $m)) {
                foreach ($m[1] as $p) { $hits[] = $p; $allApiPaths[$p] = true; }
            }
            // full URL'ler: salonrandevu.com/api/...
            if (preg_match_all('~(https?://[^"\'`\s]*salonrandevu\.com[^"\'`\s]*)~', $code, $m)) {
                foreach ($m[1] as $u) $hits[] = $u;
            }
            // BASE URL tanimi
            if (preg_match_all('~["\'`](https?://[^"\'`\s]*api[^"\'`\s]*)["\'`]~', $code, $m)) {
                foreach ($m[1] as $u) $hits[] = 'BASE: ' . $u;
            }
            $hits = array_values(array_unique($hits));
            if ($hits) $bundleFindings[$url] = $hits;
        }

        // HTML icindeki direct API path adaylari
        $htmlPaths = [];
        if (preg_match_all('~["\'](/(?:api|v1|setup|musteri|hizmet|randevu)[a-zA-Z0-9_\-/\.]{2,80})["\']~', $html, $m)) {
            $htmlPaths = array_values(array_unique($m[1]));
        }

        $summary = [
            'home_size'        => strlen($html),
            'assets'           => $assets,
            'api_paths_html'   => $htmlPaths,
            'api_paths_all'    => array_values(array_unique(array_keys($allApiPaths))),
            'bundle_findings'  => $bundleFindings,
        ];
        $this->dump('analyze_summary.json', $summary);
        return ['ok' => true, 'detail' => 'analyze done', 'summary' => $summary];
    }

    /**
     * Login dener. Once /login sayfasi -> CSRF token cek -> form POST.
     * Birden fazla path varyantini dener.
     */
    public function login()
    {
        // 1) Login sayfasini cek, CSRF + form action bul
        $candidates = ['/login', '/signin', '/sign-in', '/giris', '/'];
        $signInHtml = '';
        $loginPath = '/login';
        foreach ($candidates as $p) {
            $r = $this->http->get($p);
            if ($r->getStatusCode() === 200) {
                $body = (string) $r->getBody();
                if (stripos($body, 'password') !== false || stripos($body, 'şifre') !== false) {
                    $signInHtml = $body;
                    $loginPath = $p;
                    $this->dump('login_page.body', $body);
                    break;
                }
            }
        }
        $xsrf = $this->cookieValue('XSRF-TOKEN');
        if ($xsrf) $this->xsrf = urldecode($xsrf);

        $csrf = null;
        if (preg_match('/name="csrf-token"\s+content="([^"]+)"/i', $signInHtml, $m)) $csrf = $m[1];
        if (!$csrf && preg_match('/name="_token"\s+value="([^"]+)"/i', $signInHtml, $m)) $csrf = $m[1];

        // 2) JSON API login varyantlarini sirayla dene
        $jsonPaths = [
            '/api/auth/login', '/api/login', '/api/v1/auth/login', '/api/v1/login',
            '/api/sign-in', '/api/signin', '/api/user/login',
        ];
        foreach ($jsonPaths as $jp) {
            $payloads = [
                ['email' => $this->email, 'password' => $this->password],
                ['email' => $this->email, 'password' => $this->password, 'remember' => true],
                ['username' => $this->email, 'password' => $this->password],
                ['phone' => $this->email, 'password' => $this->password],
            ];
            foreach ($payloads as $i => $body) {
                $r = $this->http->post($jp, [
                    'json'    => $body,
                    'headers' => $this->authHeaders(['X-Requested-With' => 'XMLHttpRequest'] + ($csrf ? ['X-CSRF-TOKEN' => $csrf] : [])),
                ]);
                $status = $r->getStatusCode();
                $resp = (string) $r->getBody();
                $this->dump("login_json_{$jp}_p{$i}.body", "STATUS={$status}\n" . $resp);
                if ($status === 200 || $status === 201) {
                    $j = json_decode($resp, true);
                    if (is_array($j)) {
                        $tok = $j['token'] ?? $j['access_token'] ?? $j['data']['token'] ?? null;
                        if ($tok) { $this->bearer = $tok; return ['ok' => true, 'method' => "JSON {$jp}", 'detail' => 'token alindi']; }
                        if (isset($j['user']) || isset($j['data'])) return ['ok' => true, 'method' => "JSON {$jp}", 'detail' => 'session cookie ile login'];
                    }
                }
            }
        }

        // 3) Form-based login varyanti
        $formPaths = [$loginPath, '/login', '/signin'];
        foreach ($formPaths as $fp) {
            $r = $this->http->post($fp, [
                'form_params' => [
                    'email' => $this->email,
                    'password' => $this->password,
                ] + ($csrf ? ['_token' => $csrf] : []),
                'headers' => ['Accept' => 'text/html'] + ($csrf ? ['X-CSRF-TOKEN' => $csrf] : []),
            ]);
            $status = $r->getStatusCode();
            $body = (string) $r->getBody();
            $this->dump("login_form_{$fp}.body", "STATUS={$status}\n" . substr($body, 0, 4000));
            // Redirect ile dashboard'a giderse OK
            if ($status === 302 || (stripos($body, 'logout') !== false && stripos($body, 'dashboard') !== false)) {
                return ['ok' => true, 'method' => "FORM {$fp}", 'detail' => 'session cookie ile login'];
            }
        }

        return ['ok' => false, 'method' => 'none', 'detail' => 'login basarisiz, dump dizinine bakin: ' . $this->dumpDir];
    }

    /**
     * Yaygin endpoint'leri dener (probe).
     */
    public function probe()
    {
        $paths = [
            '/api/customers', '/api/customer/list', '/api/musteri', '/api/musteriler',
            '/api/staff', '/api/staff/list', '/api/personel',
            '/api/services', '/api/service/list', '/api/hizmetler',
            '/api/appointments', '/api/booking', '/api/booking/list',
            '/api/randevu', '/api/randevular',
            '/api/payments', '/api/tahsilat', '/api/tahsilatlar',
            '/api/products', '/api/urun', '/api/urunler',
            '/api/packages', '/api/paket', '/api/paketler',
            '/api/expenses', '/api/gider', '/api/giderler',
            '/api/dashboard', '/api/me', '/api/user', '/api/profile',
        ];
        $out = [];
        foreach ($paths as $p) {
            $r = $this->http->get($p, ['headers' => $this->authHeaders()]);
            $status = $r->getStatusCode();
            $body = (string) $r->getBody();
            $size = strlen($body);
            $this->dump("probe_" . str_replace('/', '_', trim($p, '/')) . ".body", "STATUS={$status}\n" . substr($body, 0, 2000));
            $out[$p] = "status={$status} size={$size}";
        }
        return $out;
    }
}
