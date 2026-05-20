<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * app.salonrandevu.com istemcisi.
 * - API base: https://api.salonrandevu.com/api/v1
 * - Login: POST /auth/staff {mail, password} -> 201 -> data.data.token
 * - Auth: Authorization: Bearer <token>
 *
 * Kesfedilen endpoint'ler (bundle analizi):
 *   /auth/staff           login
 *   /customers            musteri listesi   /customers/detail/{id}
 *   /services             hizmet listesi    /services/detail/{id}
 *   /staff                personel listesi
 *   /appointments/list    randevu listesi   /appointments/detail/{id}
 *   /packages/list        paket listesi     /packages/sales  /packages/detail/{id}
 *   /products             urun listesi      /products/detail/{id}
 *   /expense              gider listesi     /expense/types
 */
class SalonrandevuClient
{
    const BASE_API = 'https://api.salonrandevu.com/api/v1';
    const BASE_APP = 'https://app.salonrandevu.com';

    /** @var Client */ private $http;
    /** @var string */ private $email;
    /** @var string */ private $password;
    /** @var string|null */ private $token;
    /** @var string */ private $dumpDir;

    public function __construct($email, $password, $dumpDir = null, $proxy = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->dumpDir = $dumpDir ?: storage_path('app/salonrandevu/' . date('Ymd_His'));
        if (!is_dir($this->dumpDir)) @mkdir($this->dumpDir, 0775, true);

        $cfg = [
            'timeout'         => 300,
            'connect_timeout' => 30,
            'http_errors'     => false,
            'verify'          => false,
            'headers'         => [
                'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36',
                'Accept'          => 'application/json, text/plain, */*',
                'Accept-Language' => 'tr-TR,tr;q=0.9,en;q=0.8',
                'Origin'          => self::BASE_APP,
                'Referer'         => self::BASE_APP . '/',
            ],
        ];
        if ($proxy) $cfg['proxy'] = $proxy;
        $this->http = new Client($cfg);
    }

    public function dumpDir() { return $this->dumpDir; }
    public function setToken($t) { $this->token = $t; }
    public function getToken() { return $this->token; }

    private function dump($name, $content)
    {
        @file_put_contents($this->dumpDir . '/' . $name,
            is_string($content) ? $content : json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private function headers($extra = [])
    {
        $h = ['Accept' => 'application/json'];
        if ($this->token) $h['Authorization'] = 'Bearer ' . $this->token;
        return array_merge($h, $extra);
    }

    /**
     * Login: POST /auth/staff. Basari = HTTP 200/201.
     * Salonrandevu girisi telefon numarasi ile yapilir; API alan adi
     * mail/phone/gsm olabilir -> hepsini sirayla dener.
     * Token: response.data.token
     */
    public function login()
    {
        // Telefon: rakam-disi temizle (5xx... formatina indir)
        $kimlik = trim((string) $this->email);
        $digits = preg_replace('~\D~', '', $kimlik);
        if (strlen($digits) >= 10) {
            $digits = preg_replace('~^90~', '', $digits);
            $digits = preg_replace('~^0~', '', $digits);
        }

        $payloadVariants = [
            ['mail'  => $kimlik,  'password' => $this->password],
            ['phone' => $digits,  'password' => $this->password],
            ['gsm'   => $digits,  'password' => $this->password],
            ['mail'  => $digits,  'password' => $this->password],
            ['phone' => $kimlik,  'password' => $this->password],
            ['username' => $kimlik, 'password' => $this->password],
        ];

        $lastDetail = '';
        foreach ($payloadVariants as $i => $payload) {
            $r = $this->http->post(self::BASE_API . '/auth/staff', [
                'json'    => $payload,
                'headers' => ['Content-Type' => 'application/json'],
            ]);
            $status = $r->getStatusCode();
            $body = (string) $r->getBody();
            $field = implode('+', array_keys($payload));
            $this->dump("login_v{$i}_{$field}.body", "STATUS={$status}\n" . $body);

            if ($status === 200 || $status === 201) {
                $j = json_decode($body, true);
                $token = $j['data']['token'] ?? $j['token'] ?? ($j['data']['data']['token'] ?? null);
                if ($token) {
                    $this->token = $token;
                    return ['ok' => true, 'method' => "POST /auth/staff [{$field}]",
                            'detail' => 'token alindi (uzunluk ' . strlen($token) . ')'];
                }
                $lastDetail = "HTTP {$status} ama token yok: " . substr($body, 0, 200);
            } else {
                $lastDetail = "[{$field}] HTTP {$status}: " . substr($body, 0, 200);
            }
        }
        return ['ok' => false, 'method' => 'POST /auth/staff', 'detail' => $lastDetail];
    }

    /**
     * GET istek. Sayfalama destekli.
     * NOT: Guzzle'a bos 'query' option gecilirse URL'deki mevcut query string
     * silinir. Bu yuzden query sadece dolu ise eklenir.
     */
    public function get($path, $query = [])
    {
        $url = self::BASE_API . $path;
        $opts = ['headers' => $this->headers()];
        if (!empty($query)) $opts['query'] = $query;
        $r = $this->http->get($url, $opts);
        $status = $r->getStatusCode();
        $body = (string) $r->getBody();
        $safe = str_replace('/', '_', trim($path, '/'));
        $this->dump("get_{$safe}.body", "STATUS={$status} QUERY=" . json_encode($query) . "\n" . substr($body, 0, 8000));
        if ($status !== 200 && $status !== 201) return null;
        return json_decode($body, true);
    }

    public function post($path, $payload = [])
    {
        $r = $this->http->post(self::BASE_API . $path, [
            'headers' => $this->headers(['Content-Type' => 'application/json']),
            'json'    => $payload,
        ]);
        $status = $r->getStatusCode();
        $body = (string) $r->getBody();
        if ($status !== 200 && $status !== 201) return null;
        return json_decode($body, true);
    }

    /**
     * Anasayfa + JS bundle analizi (login gerekmez).
     */
    public function analyze()
    {
        $r = $this->http->get(self::BASE_APP . '/');
        $html = (string) $r->getBody();
        $this->dump('home.body', $html);

        $assets = [];
        if (preg_match_all('/<script[^>]+src="([^"]+\.js)"/i', $html, $m)) $assets = array_merge($assets, $m[1]);
        if (preg_match_all('/<link[^>]+href="([^"]+\.css)"/i', $html, $m)) $assets = array_merge($assets, $m[1]);
        $assets = array_values(array_unique($assets));

        $bundleFindings = [];
        $allApiPaths = [];
        foreach ($assets as $a) {
            if (substr($a, -3) !== '.js') continue;
            $url = (strpos($a, 'http') === 0) ? $a : self::BASE_APP . $a;
            $code = (string) $this->http->get($url)->getBody();
            $this->dump('asset_' . basename(parse_url($url, PHP_URL_PATH) ?: $url), $code);

            $hits = [];
            if (preg_match_all('~["\'`](/(?:api|v1|v2|auth|customers|services|staff|appointments|packages|products|expense|salon)[a-zA-Z0-9_\-/\.]{1,80})["\'`]~', $code, $m)) {
                foreach ($m[1] as $p) { $hits[] = $p; $allApiPaths[$p] = true; }
            }
            $hits = array_values(array_unique($hits));
            if ($hits) $bundleFindings[$url] = $hits;
        }
        $summary = [
            'home_size'       => strlen($html),
            'assets'          => $assets,
            'api_paths_html'  => [],
            'api_paths_all'   => array_values(array_unique(array_keys($allApiPaths))),
            'bundle_findings' => $bundleFindings,
        ];
        $this->dump('analyze_summary.json', $summary);
        return ['ok' => true, 'detail' => 'analyze done', 'summary' => $summary];
    }

    /**
     * Login + kesfedilen /company/* endpoint'leri dene.
     * Her endpoint once GET, 404/405 ise POST denenir.
     */
    public function probe()
    {
        $endpoints = [
            '/company/itself',
            '/company/customers',
            '/company/customers?page=1',
            '/company/services',
            '/company/services/filter?key=&paginate=1',
            '/company/services/with/category/all',
            '/company/category/all',
            '/company/staffs/unsafe',
            '/company/appointment/list',
            '/company/appointments',
            '/company/appointments/index2',
            '/company/packets?name=&page=-1',
            '/company/stock/items/notpag',
            '/company/accounting/expenses',
            '/company/expense/categories',
            '/company/accounting/incomes',
            '/company/receipts/opened',
            '/company/room',
            '/company/hours',
        ];
        $out = [];
        foreach ($endpoints as $path) {
            $resInfo = $this->probeOne('GET', $path);
            // GET 404/405 ise POST dene
            if (in_array($resInfo['status'], [404, 405])) {
                $postInfo = $this->probeOne('POST', $path);
                if ($postInfo['status'] === 200 || $postInfo['status'] === 201) {
                    $out['POST ' . $path] = $postInfo['line'];
                    continue;
                }
            }
            $out['GET ' . $path] = $resInfo['line'];
        }
        return $out;
    }

    /**
     * Belirli endpoint'leri cek, ilk kaydin TAM yapisini dondur (importer tasarimi icin).
     */
    public function inspect()
    {
        $targets = [
            'itself'        => '/company/itself',
            'customer'      => '/company/customers',
            'service'       => '/company/services/filter?key=&paginate=1',
            'staff'         => '/company/staffs/unsafe',
            'appointment'   => '/company/appointment/list',
            'packet'        => '/company/packets?name=&page=-1',
            'stock'         => '/company/stock/items/notpag',
            'receipt_open'  => '/company/receipts/opened',
            'expense_cat'   => '/company/expense/categories',
        ];
        $out = [];
        foreach ($targets as $key => $path) {
            $r = $this->http->get(self::BASE_API . $path, ['headers' => $this->headers()]);
            $body = (string) $r->getBody();
            $j = json_decode($body, true);
            $this->dump("inspect_{$key}.json", $body);
            $d = $j['data'] ?? $j;
            $first = null; $count = 0; $meta = [];
            if (is_array($d)) {
                if (isset($d['records']) && is_array($d['records'])) {
                    $count = count($d['records']);
                    $first = $d['records'][0] ?? null;
                    $meta = array_diff_key($d, ['records' => 1]);
                } elseif (isset($d[0])) {
                    $count = count($d);
                    $first = $d[0];
                } else {
                    $first = $d; // tek obje (itself gibi)
                }
            }
            $out[$key] = ['path' => $path, 'count' => $count, 'meta' => $meta, 'first' => $first];
        }
        return $out;
    }

    private function probeOne($method, $path)
    {
        $opts = ['headers' => $this->headers()];
        if ($method === 'POST') {
            $opts['headers']['Content-Type'] = 'application/json';
            $opts['json'] = [];
        }
        $r = $this->http->request($method, self::BASE_API . $path, $opts);
        $status = $r->getStatusCode();
        $body = (string) $r->getBody();
        $safe = $method . '_' . str_replace(['/', '?', '=', '&', ' '], '_', trim($path, '/'));
        $this->dump("probe_{$safe}.body", "STATUS={$status}\n" . substr($body, 0, 6000));

        $struct = '';
        if ($status === 200 || $status === 201) {
            $j = json_decode($body, true);
            if (is_array($j)) {
                $struct = ' keys=[' . implode(',', array_slice(array_keys($j), 0, 6)) . ']';
                // data icindeki yapiyi ozetle
                $d = $j['data'] ?? $j;
                if (is_array($d)) {
                    foreach (['records', 'list', 'items', 'data', 'rows'] as $dk) {
                        if (isset($d[$dk]) && is_array($d[$dk])) {
                            $struct .= " data.{$dk}=array(" . count($d[$dk]) . ')';
                            if (!empty($d[$dk][0]) && is_array($d[$dk][0])) {
                                $struct .= ' fields=[' . implode(',', array_slice(array_keys($d[$dk][0]), 0, 12)) . ']';
                            }
                        }
                    }
                    if (isset($d[0]) && is_array($d[0])) {
                        $struct .= ' data=array(' . count($d) . ') fields=[' . implode(',', array_slice(array_keys($d[0]), 0, 12)) . ']';
                    }
                }
            }
        }
        return [
            'status' => $status,
            'line'   => "status={$status} size=" . strlen($body) . $struct,
        ];
    }
}
