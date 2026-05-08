<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * Salonappy istemcisi (webapp.salonappy.com).
 * - Cookie jar tutar
 * - --analyze: anasayfa + JS bundle indir, endpoint adaylari bul
 * - --probe:   yaygin endpoint'leri test et
 * - login + getJson + postJson sonradan keşfedilen format'a göre
 */
class SalonappyClient
{
    const BASE_APP = 'https://webapp.salonappy.com';

    /** @var Client */
    private $http;
    /** @var CookieJar */
    private $jar;
    private $username;
    private $password;
    private $bearer;
    private $dumpDir;

    public function __construct($username, $password, $dumpDir = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->jar = new CookieJar();
        $this->dumpDir = $dumpDir ?: storage_path('app/salonappy/' . date('Ymd_His'));
        if (!is_dir($this->dumpDir)) @mkdir($this->dumpDir, 0775, true);

        $this->http = new Client([
            'base_uri'        => self::BASE_APP,
            'cookies'         => $this->jar,
            'allow_redirects' => ['max' => 5, 'track_redirects' => true],
            'timeout'         => 120,
            'connect_timeout' => 30,
            'http_errors'     => false,
            'verify'          => false,
            'headers'         => [
                'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                'Accept-Language' => 'tr-TR,tr;q=0.9,en;q=0.8',
            ],
        ]);
    }

    public function dumpDir() { return $this->dumpDir; }

    /**
     * Anasayfa + bundle indirip endpoint adaylarini cikar.
     */
    public function analyze()
    {
        $home = $this->getHtml('/', 'home');
        if ($home === '') return ['ok' => false, 'detail' => 'Anasayfa cekilemedi'];

        $assets = [];
        if (preg_match_all('#<(?:script|link)[^>]+(?:src|href)="([^"]+\.(?:js|css)[^"]*)"#i', $home, $m)) {
            foreach (array_unique($m[1]) as $u) $assets[] = $this->absolute($u);
        }

        $apiPathsHtml = [];
        if (preg_match_all('#["\'`](/[a-zA-Z][a-zA-Z0-9/_\-.\$]{2,80})["\'`]#', $home, $m)) {
            $apiPathsHtml = array_values(array_unique($m[1]));
        }

        $bundleFindings = [];
        foreach ($assets as $url) {
            if (substr($url, -3) !== '.js') continue;
            try {
                $r = $this->http->get($url);
            } catch (RequestException $e) {
                continue;
            }
            $body = (string) $r->getBody();
            $name = preg_replace('#[^a-zA-Z0-9._-]#', '_', basename(parse_url($url, PHP_URL_PATH)));
            @file_put_contents($this->dumpDir . '/asset_' . $name, $body);
            $hits = [];

            // Tum string path adaylari
            $paths = [];
            foreach (['"', "'", '`'] as $q) {
                $pat = '#' . preg_quote($q, '#') . '(/[a-zA-Z][a-zA-Z0-9/_\-.\$]{2,80})' . preg_quote($q, '#') . '#';
                if (preg_match_all($pat, $body, $mm)) $paths = array_merge($paths, $mm[1]);
            }
            $paths = array_values(array_unique($paths));

            // axios / fetch / url:""
            $calls = [];
            $callPatterns = [
                '#axios\.(?:get|post|put|delete|patch)\(["\'`]([^"\'`]+)["\'`]#i',
                '#fetch\(\s*["\'`]([^"\'`]+)["\'`]#i',
                '#url\s*:\s*["\'`](/[a-zA-Z][^"\'`]{2,80})["\'`]#i',
                '#["\'`]([A-Z]+)\s+(/[a-zA-Z][^"\'`]{2,80})["\'`]#',
            ];
            foreach ($callPatterns as $p) {
                if (preg_match_all($p, $body, $mm)) {
                    if (isset($mm[2])) {
                        foreach ($mm[2] as $u) $calls[] = $u;
                    } else {
                        $calls = array_merge($calls, $mm[1]);
                    }
                }
            }
            $calls = array_values(array_unique($calls));

            $baseUrls = [];
            if (preg_match_all('#(?:baseURL|API_URL|apiUrl|API_BASE|VITE_API_URL|REACT_APP_API)\s*[:=]\s*["\'`]([^"\'`]+)["\'`]#i', $body, $mm)) {
                $baseUrls = array_values(array_unique($mm[1]));
            }
            // Salonappy hostlari
            $domains = [];
            if (preg_match_all('#https?://[a-zA-Z0-9.\-]*salonappy[a-zA-Z0-9.\-/?=_&]*#', $body, $mm)) {
                $domains = array_values(array_unique($mm[0]));
            }

            $auth = [];
            foreach (array_merge($paths, $calls) as $p) {
                if (preg_match('#(login|auth|sign|session|token|otp)#i', $p)) $auth[] = $p;
            }
            $auth = array_values(array_unique($auth));

            $data = [];
            foreach (array_merge($paths, $calls) as $p) {
                if (preg_match('#(staff|employee|personel|service|hizmet|category|category|customer|musteri|appointment|randevu|salon|branch|sube|company)#i', $p)) {
                    $data[] = $p;
                }
            }
            $data = array_values(array_unique($data));

            if ($baseUrls) $hits[] = 'baseURL: ' . implode(', ', $baseUrls);
            if ($domains)  $hits[] = 'domains: ' . implode(', ', array_slice($domains, 0, 10));
            if ($auth)     $hits[] = 'AUTH: ' . implode(' | ', array_slice($auth, 0, 30));
            if ($data)     $hits[] = 'DATA: ' . implode(' | ', array_slice($data, 0, 60));

            if ($hits) $bundleFindings[$url] = $hits;
        }

        return [
            'ok' => true,
            'summary' => [
                'home_size'      => strlen($home),
                'assets'         => $assets,
                'api_paths_html' => $apiPathsHtml,
                'bundle_findings' => $bundleFindings,
            ],
        ];
    }

    public function getHtml($path, $tag = null)
    {
        try {
            $r = $this->http->get($path);
        } catch (RequestException $e) {
            return '';
        }
        $body = (string) $r->getBody();
        if ($tag) $this->dump($tag, $body, $r->getHeaders());
        return $body;
    }

    public function getJson($path, array $query = [])
    {
        try {
            $r = $this->http->get($path, [
                'query' => $query,
                'headers' => $this->authHeaders() + ['Accept' => 'application/json'],
            ]);
        } catch (RequestException $e) {
            return ['ok' => false, 'detail' => $e->getMessage()];
        }
        $body = (string) $r->getBody();
        $code = $r->getStatusCode();
        $tag = preg_replace('#[^a-zA-Z0-9]+#', '_', trim($path, '/')) ?: 'root';
        $this->dump('get_' . $code . '_' . $tag, $body, $r->getHeaders());
        $j = json_decode($body, true);
        return ['ok' => $code >= 200 && $code < 300, 'code' => $code, 'data' => $j, 'raw' => $body];
    }

    public function postJson($path, array $payload, array $extraHeaders = [])
    {
        try {
            $r = $this->http->post($path, [
                'json' => $payload,
                'headers' => $this->authHeaders() + $extraHeaders + ['Accept' => 'application/json'],
            ]);
        } catch (RequestException $e) {
            return ['ok' => false, 'detail' => $e->getMessage()];
        }
        $body = (string) $r->getBody();
        $code = $r->getStatusCode();
        $tag = preg_replace('#[^a-zA-Z0-9]+#', '_', trim($path, '/')) ?: 'root';
        $this->dump('post_' . $code . '_' . $tag, $body, $r->getHeaders());
        $j = json_decode($body, true);
        return ['ok' => $code >= 200 && $code < 300, 'code' => $code, 'data' => $j, 'raw' => $body];
    }

    public function setBearer($token) { $this->bearer = $token; }
    public function bearer() { return $this->bearer; }

    private function authHeaders()
    {
        $h = [];
        if ($this->bearer) $h['Authorization'] = 'Bearer ' . $this->bearer;
        return $h;
    }

    private function absolute($u)
    {
        if (preg_match('#^https?://#i', $u)) return $u;
        if (substr($u, 0, 2) === '//') return 'https:' . $u;
        if (substr($u, 0, 1) === '/') return self::BASE_APP . $u;
        return self::BASE_APP . '/' . $u;
    }

    private function dump($tag, $body, $headers = [])
    {
        $name = date('His') . '_' . preg_replace('#[^a-zA-Z0-9_]+#', '_', $tag);
        $f = $this->dumpDir . '/' . $name;
        @file_put_contents($f . '.body', $body);
        if ($headers) @file_put_contents($f . '.headers', json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    /**
     * Login. Bundle analizinden sonra concrete hale getirilecek.
     * Suan basit: /api/auth/login JSON POST denemesi.
     */
    public function login()
    {
        $candidates = [
            ['/api/auth/login', ['username' => $this->username, 'password' => $this->password]],
            ['/api/auth/login', ['email'    => $this->username, 'password' => $this->password]],
            ['/api/login',      ['username' => $this->username, 'password' => $this->password]],
            ['/api/login',      ['email'    => $this->username, 'password' => $this->password]],
            ['/auth/login',     ['username' => $this->username, 'password' => $this->password]],
            ['/login',          ['username' => $this->username, 'password' => $this->password]],
        ];
        foreach ($candidates as [$path, $payload]) {
            $r = $this->postJson($path, $payload);
            if ($r['ok'] && is_array($r['data'])) {
                // token aday alanlari
                foreach (['token', 'access_token', 'accessToken', 'jwt', 'authToken'] as $k) {
                    if (!empty($r['data'][$k])) { $this->bearer = $r['data'][$k]; break; }
                }
                if (!$this->bearer && isset($r['data']['data']) && is_array($r['data']['data'])) {
                    foreach (['token', 'access_token', 'accessToken', 'jwt'] as $k) {
                        if (!empty($r['data']['data'][$k])) { $this->bearer = $r['data']['data'][$k]; break; }
                    }
                }
                return ['ok' => true, 'method' => 'json:' . $path, 'detail' => $this->bearer ? 'Bearer alindi' : 'Bearer yok, cookie aktif olabilir'];
            }
        }
        return ['ok' => false, 'method' => 'json', 'detail' => 'Hicbir login adayi 2xx donmedi (dump dizinine bakin).'];
    }

    public function probe()
    {
        $paths = [
            '/api/me','/api/user','/api/profile','/api/account',
            '/api/staff','/api/employees','/api/personel',
            '/api/services','/api/hizmetler','/api/service-categories',
            '/api/categories','/api/branches','/api/companies','/api/salons',
            '/api/customers','/api/clients','/api/musteriler',
            '/api/appointments','/api/bookings','/api/randevular',
        ];
        $out = [];
        foreach ($paths as $p) {
            $r = $this->getJson($p);
            $out[$p] = $r['code'] . ' ' . (is_array($r['data']) ? '(json ' . count($r['data']) . ')' : 'text');
        }
        return $out;
    }
}
