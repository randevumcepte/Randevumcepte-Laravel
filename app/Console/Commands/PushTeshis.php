<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use App\Salonlar;

/**
 * Push (FCM) teşhis komutu — neden gitmediğini ekrana yazar.
 * mysql creds gerektirmez; Laravel'in kendi bağlantısını kullanır.
 *
 * Kullanım:
 *   php artisan push:teshis                 -> genel token/app_bundle sağlık raporu
 *   php artisan push:teshis {salonId}       -> o salon için token bul + GERÇEK test push at, FCM hatasını göster
 *
 * Örnek:
 *   /opt/php74/bin/php artisan push:teshis
 *   /opt/php74/bin/php artisan push:teshis 331
 */
class PushTeshis extends Command
{
    protected $signature = 'push:teshis {salonId?}';
    protected $description = 'FCM push teşhisi: token sayıları, app_bundle eşleşmesi ve gerçek gönderim testi';

    public function handle()
    {
        $this->info('=== PUSH (FCM) TEŞHİS ===');
        $this->line('');

        // 1) Global token sağlığı
        $toplam = DB::table('bildirim_kimlikleri')->count();
        $aktif  = DB::table('bildirim_kimlikleri')->where('aktif', 1)->count();
        $tokenli = DB::table('bildirim_kimlikleri')
            ->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '')->count();
        $kullanilabilir = DB::table('bildirim_kimlikleri')
            ->where('aktif', 1)->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '')->count();

        $this->info('--- bildirim_kimlikleri ---');
        $this->line("toplam kayit        : {$toplam}");
        $this->line("aktif=1             : {$aktif}");
        $this->line("bildirim_id dolu    : {$tokenli}");
        $this->line("aktif + tokenli     : {$kullanilabilir}   <- gerçekte gönderilebilir olanlar");
        $this->line('');

        // 2) app_bundle kırılımı (token tarafı)
        $this->info('--- Token app_bundle dağılımı (aktif+tokenli) ---');
        $tokBundles = DB::table('bildirim_kimlikleri')
            ->select('app_bundle', DB::raw('COUNT(*) as adet'))
            ->where('aktif', 1)->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '')
            ->groupBy('app_bundle')->orderByDesc('adet')->get();
        foreach ($tokBundles as $b) {
            $ab = $b->app_bundle === null ? 'NULL' : ($b->app_bundle === '' ? "'' (bos)" : $b->app_bundle);
            $this->line(sprintf('  %-45s %d', $ab, $b->adet));
        }
        $this->line('');

        // 3) Salonlar tarafındaki app_bundle değerleri
        $this->info('--- Salonlar.app_bundle dağılımı ---');
        $salBundles = DB::table('salonlar')
            ->select('app_bundle', DB::raw('COUNT(*) as adet'))
            ->groupBy('app_bundle')->orderByDesc('adet')->get();
        $salSet = [];
        foreach ($salBundles as $b) {
            $ab = $b->app_bundle === null ? 'NULL' : ($b->app_bundle === '' ? "'' (bos)" : $b->app_bundle);
            if (!empty($b->app_bundle)) $salSet[$b->app_bundle] = true;
            $this->line(sprintf('  %-45s %d salon', $ab, $b->adet));
        }
        $this->line('');

        // 4) EŞLEŞME KONTROLÜ: token app_bundle'ı hiçbir salonun app_bundle'ına uymuyorsa
        //    o cihazlara ASLA push gitmez (brand izolasyon filtresi eler).
        $this->info('--- app_bundle EŞLEŞME KONTROLÜ ---');
        $uyumsuz = 0;
        foreach ($tokBundles as $b) {
            if (empty($b->app_bundle)) continue;
            if (!isset($salSet[$b->app_bundle])) {
                $this->error("  [UYUMSUZ] token app_bundle='{$b->app_bundle}' ({$b->adet} cihaz) hiçbir salonda yok -> bu cihazlara push GİTMEZ");
                $uyumsuz += $b->adet;
            }
        }
        if ($uyumsuz === 0) {
            $this->info('  [OK] Tüm token app_bundle değerleri en az bir salonla eşleşiyor.');
        } else {
            $this->warn("  Toplam {$uyumsuz} cihaz brand izolasyonu yüzünden eleniyor olabilir.");
        }
        $this->line('');

        // 5) Firebase JSON dosyaları mevcut mu + OAuth kimlik testi (401'in kök nedeni burada)
        $this->info('--- Firebase kimlik (OAuth token) testi ---');
        $goruldu = [];
        foreach ((array) config('firebase_projects') as $profile => $rel) {
            if (isset($goruldu[$rel])) continue; // ayni JSON'u bir kez test et
            $goruldu[$rel] = true;
            $abs = storage_path($rel);
            if (!is_file($abs)) {
                $this->error(sprintf('  [YOK] %s (%s)', $rel, $profile));
                continue;
            }
            [$ok, $detay] = $this->oauthTokenDene($abs);
            if ($ok) {
                $this->info(sprintf('  [OK]  %s -> access_token alindi (%s)', basename($rel), $detay));
            } else {
                $this->error(sprintf('  [401/HATA] %s -> %s', basename($rel), $detay));
            }
        }
        $this->line('');

        $salonId = $this->argument('salonId');
        if (!$salonId) {
            $this->comment('Bir salona gerçek test push atmak için: php artisan push:teshis {salonId}');
            return 0;
        }

        // 6) Belirli salon için gerçek gönderim testi
        $salonId = (int) $salonId;
        $salon = Salonlar::find($salonId);
        if (!$salon) {
            $this->error("Salon bulunamadı: {$salonId}");
            return 1;
        }

        $this->info("=== SALON {$salonId} GERÇEK TEST PUSH ===");
        $this->line("salon_adi        : " . ($salon->salon_adi ?? '-'));
        $this->line("firebase_profile : " . ($salon->firebase_profile ?? 'NULL'));
        $this->line("app_bundle       : " . ($salon->app_bundle ?? 'NULL'));

        // Profil config'te var mı?
        $prof = $salon->firebase_profile;
        if (!empty($prof)) {
            $path = config("firebase_projects.{$prof}");
            if (empty($path)) {
                $this->error("  [X] firebase_profile='{$prof}' config/firebase_projects.php'de YOK -> default'a düşer, yanlış proje!");
            } else {
                $this->info("  [OK] firebase_profile -> {$path}");
            }
        }
        $this->line('');

        // Bu salonun brand'ine ait, aktif+tokenli cihaz sayısı
        $q = DB::table('bildirim_kimlikleri')
            ->where('aktif', 1)->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '');
        if (!empty($salon->app_bundle)) {
            $q->where('app_bundle', $salon->app_bundle);
        }
        $hedefAdet = $q->count();
        $this->line("Bu salonun app_bundle'ına ait gönderilebilir cihaz: {$hedefAdet}");

        // Test edilecek token: once salon-eslesen, yoksa app_bundle filtresi OLMADAN herhangi biri
        $ornek = $q->select('bildirim_id', 'platform', 'app_bundle')->first();
        if (!$ornek) {
            $this->warn('  [!] Bu salonun app_bundle filtresine uyan token YOK. FCM auth yolunu yine de test etmek icin');
            $this->warn('      app_bundle filtresi OLMADAN herhangi bir aktif token secilecek (brand izolasyonu bypass, sadece test).');
            $ornek = DB::table('bildirim_kimlikleri')
                ->where('aktif', 1)->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '')
                ->select('bildirim_id', 'platform', 'app_bundle')->first();
        }
        if (!$ornek) {
            $this->error('  [X] Sistemde HIC aktif token yok. Uygulama token kaydetmiyor.');
            return 0;
        }
        $this->line("  Test token app_bundle: " . ($ornek->app_bundle ?? 'NULL') . " / platform: " . ($ornek->platform ?? 'NULL'));
        $this->line('');

        // DOGRUDAN messages:send testi — NotificationService hatayi yutuyor, biz ham istekle
        // Google'in TAM cevabini (401 ise gerekcesiyle) ekrana basiyoruz.
        $this->info("--- DOGRUDAN FCM messages:send testi (tam cevap) ---");
        // Hangi JSON? Salon profili varsa o, yoksa default.
        $rel = config("firebase_projects." . ($salon->firebase_profile ?: 'default'))
             ?: config('firebase_projects.default');
        $jsonAbs = storage_path($rel);
        $this->line("  Kullanilan JSON: {$rel}");
        [$tokOk, $tokDetay, $accessToken] = $this->oauthTokenAl($jsonAbs);
        if (!$tokOk) {
            $this->error("  [X] Access token alinamadi: {$tokDetay}");
            return 0;
        }
        $projectId = json_decode(file_get_contents($jsonAbs), true)['project_id'] ?? null;
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 15]);
            $resp = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => ['message' => [
                    'token' => $ornek->bildirim_id,
                    'notification' => ['title' => 'Push Test', 'body' => 'Teshis testi'],
                ]],
                'http_errors' => false,
            ]);
            $code = $resp->getStatusCode();
            $body = $resp->getBody()->getContents();
            $this->line("  HTTP {$code}");
            $this->line("  Cevap: " . $body);
            if ($code === 200) {
                $this->info('  [OK] FCM KABUL ETTI. Kimlik + gonderim yolu saglam. Sorun app_bundle eslesmesinde.');
            } elseif ($code === 401) {
                $this->error('  [401] Kimlik reddedildi — messages:send yetkisi/API sorunu.');
            } elseif ($code === 404 || stripos($body, 'UNREGISTERED') !== false) {
                $this->warn('  [404/UNREGISTERED] Bu token artik gecersiz ama KIMLIK CALISIYOR (auth 200). Yeni token gerek.');
            } else {
                $this->warn("  [{$code}] Yukaridaki cevaba bak.");
            }
        } catch (\Throwable $e) {
            $this->error('  [X] ISTISNA: ' . $e->getMessage());
        }

        return 0;
    }

    /**
     * NotificationService::getFcmAccessToken() ile birebir ayni JWT->access_token
     * akisini calistirir; basarili mi, degilse Google'in tam cevabi neyse onu doner.
     * 401'in kok nedeni (kimlik gecersiz / API kapali / SA silinmis) burada gorunur.
     *
     * @return array{0:bool,1:string}
     */
    private function oauthTokenDene(string $jsonAbsPath): array
    {
        [$ok, $detay] = $this->oauthTokenAl($jsonAbsPath);
        return [$ok, $detay];
    }

    /**
     * OAuth token akisi; basari halinde access_token'i da doner.
     * @return array{0:bool,1:string,2:?string}
     */
    private function oauthTokenAl(string $jsonAbsPath): array
    {
        try {
            $json = json_decode(file_get_contents($jsonAbsPath), true);
            if (empty($json['client_email']) || empty($json['private_key']) || empty($json['token_uri'])) {
                return [false, 'JSON eksik alan (client_email/private_key/token_uri)', null];
            }
            $now = time();
            $b64 = function ($d) { return rtrim(strtr(base64_encode($d), '+/', '-_'), '='); };
            $header = ['alg' => 'RS256', 'typ' => 'JWT'];
            $claim = [
                'iss'   => $json['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => $json['token_uri'],
                'iat'   => $now,
                'exp'   => $now + 3600,
            ];
            $data = $b64(json_encode($header)) . '.' . $b64(json_encode($claim));
            $sigOk = openssl_sign($data, $signature, $json['private_key'], OPENSSL_ALGO_SHA256);
            if (!$sigOk) {
                return [false, 'openssl_sign BASARISIZ (private_key bozuk?) - ' . (openssl_error_string() ?: 'sebep yok'), null];
            }
            $jwt = $data . '.' . $b64($signature);

            $client = new \GuzzleHttp\Client(['timeout' => 15]);
            $resp = $client->post($json['token_uri'], [
                'form_params' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $jwt,
                ],
                'http_errors' => false, // hata cevabini da okuyabilmek icin
            ]);
            $code = $resp->getStatusCode();
            $body = json_decode($resp->getBody()->getContents(), true);
            if ($code === 200 && !empty($body['access_token'])) {
                return [true, 'HTTP 200, token uzunluk=' . strlen($body['access_token']) . ', client_email=' . $json['client_email'], $body['access_token']];
            }
            $err = isset($body['error']) ? (is_array($body['error']) ? json_encode($body['error']) : $body['error']) : 'bilinmeyen';
            $desc = $body['error_description'] ?? '';
            return [false, "HTTP {$code} | error={$err} | {$desc} | SA={$json['client_email']}", null];
        } catch (\Throwable $e) {
            return [false, 'ISTISNA: ' . $e->getMessage(), null];
        }
    }
}
