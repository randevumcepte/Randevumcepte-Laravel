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
        // ASP.NET WebForms postback login.
        // 1) Anasayfayi GET et, formdan __VIEWSTATE/__VIEWSTATEGENERATOR/__EVENTVALIDATION cek
        $home = $this->getHtml('/', 'home');
        if ($home === '') return ['ok' => false, 'method' => 'none', 'detail' => 'Anasayfa cekilemedi'];

        $vs   = $this->extractFormField($home, '__VIEWSTATE');
        $vsg  = $this->extractFormField($home, '__VIEWSTATEGENERATOR');
        $ev   = $this->extractFormField($home, '__EVENTVALIDATION');
        if (!$vs) return ['ok' => false, 'method' => 'none', 'detail' => '__VIEWSTATE bulunamadi (form yapisi degismis olabilir)'];

        // Form action: ./giris.aspx -> /giris.aspx
        $action = '/giris.aspx';
        if (preg_match('#<form[^>]*action=["\']([^"\']+)["\']#i', $home, $m)) {
            $a = $m[1];
            $action = (strpos($a, 'http') === 0) ? $a : ('/' . ltrim(str_replace('./', '', $a), '/'));
        }

        $body = [
            '__EVENTTARGET'        => 'LB_Giris', // LinkButton id (form HTML'inden)
            '__EVENTARGUMENT'      => '',
            '__VIEWSTATE'          => $vs,
            '__VIEWSTATEGENERATOR' => $vsg ?: '',
            '__EVENTVALIDATION'    => $ev ?: '',
            'TB_KullaniciAd'       => $this->username,
            'TB_Sifre'             => $this->password,
        ];

        try {
            $resp = $this->http->post($action, [
                'headers'     => [
                    'Referer' => self::BASE . '/',
                    'Origin'  => self::BASE,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $body,
            ]);
        } catch (RequestException $e) {
            return ['ok' => false, 'method' => 'webforms', 'detail' => 'Exception: ' . $e->getMessage()];
        }

        $status = $resp->getStatusCode();
        $resBody = (string) $resp->getBody();
        $this->dump("login_webforms_{$status}", $resBody, $resp->getHeaders());

        // Login basarili mi? Cevap body'de login formu (TB_KullaniciAd) DONMEMELI.
        // 302 redirect olduysa Guzzle takip etmis, gelen body authenticated sayfa.
        $stillLogin = stripos($resBody, 'TB_KullaniciAd') !== false;
        $endedAt = '';
        foreach ($resp->getHeader('X-Guzzle-Redirect-History') as $r) $endedAt = $r;

        if (!$stillLogin) {
            return ['ok' => true, 'method' => 'webforms', 'detail' => "Login OK. Son URL: {$endedAt}"];
        }
        return ['ok' => false, 'method' => 'webforms', 'detail' => 'Login basarisiz; cevap login formuna donmus. Dump: login_webforms_' . $status . '.body'];
    }

    private function extractFormField($html, $name)
    {
        $pat = '#<input[^>]+name=["\']' . preg_quote($name, '#') . '["\'][^>]+value=["\']([^"\']*)["\']#i';
        if (preg_match($pat, $html, $m)) return $m[1];
        // value once gelirse
        $pat = '#<input[^>]+value=["\']([^"\']*)["\'][^>]+name=["\']' . preg_quote($name, '#') . '["\']#i';
        if (preg_match($pat, $html, $m)) return $m[1];
        return '';
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
        // ASP.NET WebForms: anasayfa veya /default.aspx isteyince login formu DONMEMELI
        $checks = ['/', '/default.aspx', '/anasayfa.aspx', '/dashboard.aspx', '/anaSayfa.aspx'];
        foreach ($checks as $path) {
            try { $resp = $this->http->get($path); } catch (RequestException $e) { continue; }
            $body = (string) $resp->getBody();
            // Login formu icermiyorsa ve TB_KullaniciAd yoksa giris yapilmis demek
            if (stripos($body, 'TB_KullaniciAd') === false && stripos($body, 'TB_Sifre') === false) {
                return true;
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
            '/default.aspx', '/anasayfa.aspx', '/anaSayfa.aspx', '/dashboard.aspx',
            '/musteriler.aspx', '/musteri.aspx', '/musteri_listesi.aspx', '/musterilistesi.aspx',
            '/randevular.aspx', '/randevu.aspx', '/randevu_listesi.aspx', '/randevulistesi.aspx', '/takvim.aspx',
            '/hizmetler.aspx', '/hizmet.aspx', '/hizmet_listesi.aspx',
            '/personeller.aspx', '/personel.aspx', '/personel_listesi.aspx',
            '/tahsilatlar.aspx', '/tahsilat.aspx', '/tahsilat_listesi.aspx',
            '/raporlar.aspx', '/rapor.aspx',
            // Olasi alt klasorler
            '/admin/musteriler.aspx', '/uygulama/musteriler.aspx',
        ];
        $results = [];
        $i = 0;
        foreach ($pages as $p) {
            try {
                $resp = $this->http->get($p);
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
            // Login formu varsa "kapali" demek; tablo varsa anlamli sayfa
            $hasLogin = stripos($body, 'TB_KullaniciAd') !== false;
            $hasTable = preg_match('#<table[^>]*class="[^"]*(?:table|grid|gridview)#i', $body) || stripos($body, '<table') !== false;
            $marker = '';
            if ($hasLogin) $marker = ' [LOGIN_GERI]';
            elseif ($hasTable) $marker = ' [TABLE]';
            $results[$p] = "status={$status} len={$len} type={$ctype}" . $marker;
            if (++$i % 5 === 0) usleep(300000);
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

    /**
     * ASP.NET WebForms postback yapar:
     * - Once GET ile sayfayi cek, viewstate vs. extract et
     * - Sonra POST ile __EVENTTARGET/__EVENTARGUMENT + ek form alanlari + viewstate gonder
     *
     * @param string $path Sayfa yolu (orn: /hizmet_listesi.aspx)
     * @param string $eventTarget __EVENTTARGET degeri (orn: DDL_Birim, LB_Listele)
     * @param string $eventArgument __EVENTARGUMENT degeri (cogunlukla "")
     * @param array $extraFields Ek form alanlari (orn: ['DDL_Birim' => '5554'])
     * @return string|null donen HTML body, null = hata
     */
    public function postBack($path, $eventTarget, $eventArgument = '', array $extraFields = [])
    {
        // Once GET ile guncel viewstate
        try {
            $g = $this->http->get($path);
        } catch (RequestException $e) { return null; }
        if ($g->getStatusCode() !== 200) return null;
        $html = (string) $g->getBody();

        $vs   = $this->extractFormField($html, '__VIEWSTATE');
        $vsg  = $this->extractFormField($html, '__VIEWSTATEGENERATOR');
        $ev   = $this->extractFormField($html, '__EVENTVALIDATION');

        $body = array_merge([
            '__EVENTTARGET'        => $eventTarget,
            '__EVENTARGUMENT'      => $eventArgument,
            '__VIEWSTATE'          => $vs,
            '__VIEWSTATEGENERATOR' => $vsg ?: '',
            '__EVENTVALIDATION'    => $ev ?: '',
        ], $extraFields);

        // Form action: ./<path> veya path
        $action = $path;
        if (preg_match('#<form[^>]*action="([^"]+)"#i', $html, $m)) {
            $a = $m[1];
            $action = (strpos($a, 'http') === 0) ? $a : ('/' . ltrim(str_replace('./', '', $a), '/'));
        }

        try {
            $resp = $this->http->post($action, [
                'headers'     => [
                    'Referer' => self::BASE . $path,
                    'Origin'  => self::BASE,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $body,
            ]);
        } catch (RequestException $e) { return null; }
        $b = (string) $resp->getBody();
        $tag = 'postback_' . $this->slug($path) . '_' . $this->slug($eventTarget);
        $this->dump($tag, $b, $resp->getHeaders());
        return $b;
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
