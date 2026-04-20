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
            '/sign-in',
            '/api/auth/login',
            '/api/auth/signin',
            '/api/v1/auth/login',
            '/api/login',
            '/auth/login',
            '/api/user/login',
            '/api/sign-in',
            '/api/auth/sign-in',
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
        // Planla.co SPA route'lari: ayni path'lere Accept:application/json ile JSON donebilir
        $pages = [
            '/customers', '/services', '/employees',
            '/finances', '/packages', '/products', '/reviews',
            '/statistics', '/payments', '/settings',
            // Nested list denemeleri
            '/customers/list', '/services/list', '/employees/list',
            '/customers?limit=1', '/services?limit=1',
            // Eski denemeler (dokumante icin)
            '/api/customers', '/api/services', '/api/employees',
        ];

        $results = [];
        $i = 0;
        $jsonHeaders = array_merge($this->authHeaders(), [
            'Accept'           => 'application/json, text/plain, */*',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer'          => self::BASE_ADMIN . '/customers',
            'Origin'           => self::BASE_ADMIN,
        ]);
        $methods = [
            'GET'        => function ($p, $headers) { return $this->http->get($p, ['headers' => $headers]); },
            'POST_empty' => function ($p, $headers) {
                return $this->http->post($p, [
                    'headers' => array_merge($headers, ['Content-Type' => 'application/json']),
                    'body'    => '{}',
                ]);
            },
        ];
        foreach ($pages as $p) {
            foreach ($methods as $mname => $caller) {
                try {
                    $resp = $caller($p, $jsonHeaders);
                } catch (RequestException $e) {
                    $results["{$mname} {$p}"] = 'EXC:' . $e->getMessage();
                    continue;
                }
                $status = $resp->getStatusCode();
                $body   = (string) $resp->getBody();
                $ctype  = $resp->getHeaderLine('Content-Type');
                $len    = strlen($body);
                $slug   = "probe_{$mname}_{$this->slug($p)}_{$status}";
                $this->dump($slug, $body, $resp->getHeaders());
                $isJson = stripos($ctype, 'json') !== false;
                $marker = '';
                if ($isJson) $marker = ' [JSON!]';
                elseif ($status !== 200 && $status !== 404) $marker = ' [!' . $status . ']';
                $results["{$mname} {$p}"] = "status={$status} len={$len} type={$ctype}" . $marker;
                if (++$i % 10 === 0) usleep(500000);
            }
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

        // Tum path-benzeri stringler (", ', `)
        $paths = [];
        foreach (['"', "'", '`'] as $q) {
            $pat = '#' . preg_quote($q, '#') . '(/[a-zA-Z][a-zA-Z0-9/_\-.\$]{2,80})' . preg_quote($q, '#') . '#';
            if (preg_match_all($pat, $js, $m)) {
                $paths = array_merge($paths, $m[1]);
            }
        }
        $paths = array_values(array_unique($paths));

        // Axios/fetch cagrilari (daha guvenilir isaretci)
        $calls = [];
        $callPatterns = [
            '#axios\.(?:get|post|put|delete|patch)\(["\'`]([^"\'`]+)["\'`]#i',
            '#\.request\(\s*\{[^}]*url\s*:\s*["\'`]([^"\'`]+)["\'`]#i',
            '#fetch\(\s*["\'`]([^"\'`]+)["\'`]#i',
            '#url\s*:\s*["\'`](/[a-zA-Z][^"\'`]{2,80})["\'`]#i',
        ];
        foreach ($callPatterns as $p) {
            if (preg_match_all($p, $js, $m)) $calls = array_merge($calls, $m[1]);
        }
        $calls = array_values(array_unique($calls));

        // baseURL / API_URL
        $baseUrls = [];
        if (preg_match_all('#(?:baseURL|base_url|API_URL|apiUrl|apiURL|API_BASE|API_ENDPOINT)\s*[:=]\s*["\'`]([^"\'`]+)["\'`]#i', $js, $m)) {
            $baseUrls = array_values(array_unique($m[1]));
        }

        // Planla domains (daha genis)
        $urls = [];
        if (preg_match_all('#https?://[a-zA-Z0-9.\-]*planla[a-zA-Z0-9.\-/?=_&]*#', $js, $m)) {
            $urls = array_values(array_unique($m[0]));
        }

        // Login/auth pathleri (tum path havuzundan)
        $loginPaths = [];
        foreach (array_merge($paths, $calls) as $p) {
            if (preg_match('#(sign[-_]?in|login|auth|session)#i', $p)) $loginPaths[] = $p;
        }
        $loginPaths = array_values(array_unique($loginPaths));

        // Data endpoint adaylari
        $dataPaths = [];
        foreach (array_merge($paths, $calls) as $p) {
            if (preg_match('#(customer|musteri|client|appointment|randevu|booking|service|hizmet|staff|personel|employee|calendar|ajanda|kategori|categor|branch|sube)#i', $p)) {
                $dataPaths[] = $p;
            }
        }
        $dataPaths = array_values(array_unique($dataPaths));

        $payloadHints = [];
        if (preg_match_all('#\{[^{}]{0,200}(password|sifre)[^{}]{0,200}\}#', $js, $m)) {
            $payloadHints = array_slice(array_values(array_unique($m[0])), 0, 10);
        }

        // Path string'lerinin etrafindaki JS context'i (gercek HTTP call pattern'ini bulmak icin)
        $contexts = [];
        $interesting = ['customers', 'services', 'employees', 'appointments', 'bookings', 'sign-in'];
        foreach ($interesting as $needle) {
            $quoted = '"/' . $needle . '"';
            $pos = 0;
            $count = 0;
            while (($p = strpos($js, $quoted, $pos)) !== false && $count < 3) {
                $start = max(0, $p - 180);
                $len = min(400, strlen($js) - $start);
                $contexts[$needle . '#' . $count] = substr($js, $start, $len);
                $pos = $p + strlen($quoted);
                $count++;
            }
        }

        // Http client instance'lari (axios.create, API kurucu)
        $httpInit = [];
        foreach (['axios\\.create\\s*\\([^)]{0,400}\\)', 'new\\s+[A-Z][a-zA-Z]*Api\\s*\\([^)]{0,200}\\)', 'createApi\\s*\\([^)]{0,400}\\)'] as $p) {
            if (preg_match_all('#' . $p . '#', $js, $m)) {
                $httpInit = array_merge($httpInit, array_slice(array_unique($m[0]), 0, 5));
            }
        }

        // GraphQL / socket.io / ws ipuclari
        $protocols = [];
        foreach (['graphql', 'subscription', 'io\\(["\'`]', 'socket\\.io', 'new WebSocket', '/ws/', '/api/v', '/v[0-9]+/'] as $p) {
            if (preg_match('#' . $p . '#i', $js, $m)) $protocols[] = trim($m[0]);
        }

        // postOptions / getOptions / useQuery / useMutation url'leri
        $operationOptions = [];
        foreach (['postOptions', 'getOptions', 'putOptions', 'deleteOptions', 'requestOptions', 'mutation', 'query'] as $opt) {
            $pat = '#' . $opt . '\\s*:\\s*\\{[^{}]{0,300}\\}#';
            if (preg_match_all($pat, $js, $m)) {
                foreach (array_slice(array_unique($m[0]), 0, 8) as $hit) $operationOptions[] = $hit;
            }
        }

        // url:"..." + method:"..." pattern'leri
        $urlFields = [];
        if (preg_match_all('#url\\s*:\\s*["\'`]([^"\'`]{2,100})["\'`][^{}]{0,60}method\\s*:\\s*["\'`]([a-z]+)["\'`]#i', $js, $m, PREG_SET_ORDER)) {
            foreach (array_slice($m, 0, 40) as $row) {
                $urlFields[] = $row[2] . ' ' . $row[1];
            }
        }
        if (preg_match_all('#method\\s*:\\s*["\'`]([a-z]+)["\'`][^{}]{0,60}url\\s*:\\s*["\'`]([^"\'`]{2,100})["\'`]#i', $js, $m, PREG_SET_ORDER)) {
            foreach (array_slice($m, 0, 40) as $row) {
                $urlFields[] = $row[1] . ' ' . $row[2];
            }
        }
        if (preg_match_all('#url\\s*:\\s*["\'`](/[a-zA-Z][^"\'`]{2,100})["\'`]#', $js, $m)) {
            foreach (array_slice(array_unique($m[1]), 0, 40) as $u) $urlFields[] = 'url=' . $u;
        }
        $urlFields = array_values(array_unique($urlFields));

        // queryKey arraylari (React Query)
        $queryKeys = [];
        if (preg_match_all('#queryKey\\s*:\\s*\\[([^\\]]{0,200})\\]#', $js, $m)) {
            $queryKeys = array_slice(array_values(array_unique($m[1])), 0, 30);
        }

        // graphql / connect-api context
        $keywordCtx = [];
        foreach (['graphql', 'connect-api', 'subscription'] as $kw) {
            $pos = 0; $count = 0;
            while (($p = stripos($js, $kw, $pos)) !== false && $count < 5) {
                $start = max(0, $p - 120);
                $keywordCtx[$kw . '#' . $count] = substr($js, $start, 280);
                $pos = $p + strlen($kw);
                $count++;
            }
        }

        // Api wrapper cagrilari (Apollo client useQuery, axios wrapper vs.)
        $wrapperCalls = [];
        foreach ([
            '#useQuery\\s*\\([^)]{0,200}\\)#',
            '#useMutation\\s*\\([^)]{0,200}\\)#',
            '#useGet\\s*\\([^)]{0,200}\\)#',
            '#usePost\\s*\\([^)]{0,200}\\)#',
            '#useApi\\s*\\([^)]{0,200}\\)#',
        ] as $p) {
            if (preg_match_all($p, $js, $m)) {
                foreach (array_slice(array_unique($m[0]), 0, 10) as $hit) $wrapperCalls[] = $hit;
            }
        }

        $summary = [
            'bundle_size'    => strlen($js),
            'login_paths'    => $loginPaths,
            'data_paths'     => $dataPaths,
            'base_urls'      => $baseUrls,
            'http_calls'     => array_slice($calls, 0, 60),
            'all_paths'      => array_slice($paths, 0, 120),
            'planla_urls'    => $urls,
            'payload_hints'  => $payloadHints,
            'path_contexts'  => $contexts,
            'http_init'      => $httpInit,
            'protocols'      => $protocols,
            'operation_opts' => $operationOptions,
            'url_fields'     => $urlFields,
            'query_keys'     => $queryKeys,
            'keyword_ctx'    => $keywordCtx,
            'wrapper_calls'  => $wrapperCalls,
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

    /**
     * Planla.co'nun tek data endpoint'i: POST /connect-api
     * Body yapisi: {meta: {version:"1", action:"..."}, data: {...}}
     * Cevap: {meta:{...}, data:{...}} veya hata.
     *
     * @param string $action Action adi (readCustomers, readServices, ...)
     * @param array  $data   Payload (opsiyonel)
     * @param array  $meta   Ek meta alanlari
     * @return array|null    JSON decode edilmis cevap veya null
     */
    public function connectApi($action, array $data = [], array $meta = [])
    {
        $baseMeta = ['version' => '1'];
        // Geriye uyumluluk: eger $meta bos ve $action bir string ise, onu category olarak ekle
        if (empty($meta)) {
            $baseMeta['category'] = $action;
        }
        $body = [
            'meta' => array_merge($baseMeta, $meta),
            'data' => (object) $data,
        ];
        $headers = array_merge($this->authHeaders(), [
            'Accept'           => 'application/json, text/plain, */*',
            'Content-Type'     => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer'          => self::BASE_ADMIN . '/',
            'Origin'           => self::BASE_ADMIN,
        ]);
        try {
            $resp = $this->http->post('/connect-api', [
                'headers' => $headers,
                'body'    => json_encode($body, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (RequestException $e) {
            Log::warning('connectApi exception: ' . $e->getMessage());
            return null;
        }
        $status = $resp->getStatusCode();
        $respBody = (string) $resp->getBody();
        $ctype = $resp->getHeaderLine('Content-Type');
        $this->dump('connect_' . $action . '_' . $status, $respBody, $resp->getHeaders());
        if (stripos($ctype, 'json') === false) return null;
        $j = json_decode($respBody, true);
        return is_array($j) ? $j : null;
    }

    /**
     * meta shape: {version, category, event} tespit edildi.
     * category dogru ama event yoktu ("Event couldn't found" hatasi).
     * Bu tarama category x event matrisini dener.
     */
    public function probeConnectApi(array $categories = null, array $events = null)
    {
        if ($categories === null) {
            $categories = [
                'customers', 'services', 'appointments', 'employees',
                'packages', 'products', 'reviews', 'statistics', 'settings',
                'finances', 'account', 'categories',
            ];
        }
        if ($events === null) {
            $events = [
                'read', 'list', 'all', 'getAll', 'readAll', 'fetchAll',
                'find', 'search', 'index', 'get',
                'readList', 'getList', 'listAll',
            ];
        }
        $results = [];
        $i = 0;
        foreach ($categories as $cat) {
            foreach ($events as $ev) {
                $meta = ['version' => '1', 'category' => $cat, 'event' => $ev];
                $resp = $this->connectApiRaw($meta, []);
                $key = $cat . ':' . $ev;
                if ($resp === null) {
                    $results[$key] = 'no-json';
                } elseif (isset($resp['meta']['error']) || isset($resp['error']) || isset($resp['data']['error'])) {
                    $err = isset($resp['error']) ? $resp['error']
                         : (isset($resp['meta']['error']) ? $resp['meta']['error']
                         : $resp['data']['error']);
                    if (is_array($err)) $err = json_encode($err);
                    $results[$key] = 'ERR: ' . substr($err, 0, 100);
                } else {
                    $topKeys = array_slice(array_keys($resp), 0, 6);
                    $dataKeys = [];
                    $count = 0;
                    if (isset($resp['data']) && is_array($resp['data'])) {
                        $dataKeys = array_slice(array_keys($resp['data']), 0, 10);
                        if (isset($resp['data'][0])) $count = count($resp['data']);
                        elseif (isset($resp['data']['list']) && is_array($resp['data']['list'])) $count = count($resp['data']['list']);
                        elseif (isset($resp['data']['items']) && is_array($resp['data']['items'])) $count = count($resp['data']['items']);
                        elseif (isset($resp['data']['data']) && is_array($resp['data']['data'])) $count = count($resp['data']['data']);
                    }
                    $results[$key] = 'OK top=[' . implode(',', $topKeys) . '] data.keys=[' . implode(',', $dataKeys) . '] count=' . $count;
                }
                if (++$i % 15 === 0) usleep(800000);
            }
        }
        return $results;
    }

    /**
     * connect-api'yi tam kontrollu meta + data ile cagirir.
     */
    public function connectApiRaw(array $meta, array $data = [])
    {
        $body = ['meta' => (object) $meta, 'data' => (object) $data];
        $headers = array_merge($this->authHeaders(), [
            'Accept'           => 'application/json, text/plain, */*',
            'Content-Type'     => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer'          => self::BASE_ADMIN . '/',
            'Origin'           => self::BASE_ADMIN,
        ]);
        try {
            $resp = $this->http->post('/connect-api', [
                'headers' => $headers,
                'body'    => json_encode($body, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (RequestException $e) {
            return null;
        }
        $status = $resp->getStatusCode();
        $respBody = (string) $resp->getBody();
        $ctype = $resp->getHeaderLine('Content-Type');
        $metaSlug = $this->slug(json_encode($meta));
        $this->dump('connectRaw_' . substr($metaSlug, 0, 60) . '_' . $status, $respBody, $resp->getHeaders());
        if (stripos($ctype, 'json') === false) return null;
        $j = json_decode($respBody, true);
        return is_array($j) ? $j : null;
    }

    public function getJson($path, array $query = [])
    {
        try {
            $resp = $this->http->get($path, [
                'headers' => array_merge($this->authHeaders(), [
                    'Accept'           => 'application/json, text/plain, */*',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Referer'          => self::BASE_ADMIN . $path,
                ]),
                'query'   => $query,
            ]);
        } catch (RequestException $e) {
            Log::warning('planla getJson exception: ' . $e->getMessage());
            return null;
        }
        $body = (string) $resp->getBody();
        $ctype = $resp->getHeaderLine('Content-Type');
        if (stripos($ctype, 'json') === false) return null;
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
