<?php
/**
 * Bagimsiz FCM HTTP v1 test scripti (Laravel gerektirmez).
 * Kullanim:
 *   php fcm_test.php <service_account.json> <FCM_TOKEN>
 *   php fcm_test.php <service_account.json>            (DB'den salon 432 yetkili ios token'lari cekilir)
 *
 * Ne yapar: service account ile OAuth2 access token uretir, FCM v1
 * messages:send ucuna push atar, tam HTTP yanitini gosterir.
 */

// GUVENLIK: yalnizca komut satirindan (CLI) calissin. Bu dosya shared hosting
// kokune dusebilir; web'den (https://.../fcm_test.php) tetiklenmesini engelle.
if (PHP_SAPI !== 'cli') { http_response_code(403); die('CLI only'); }

error_reporting(E_ERROR | E_PARSE); // deprecated uyarilarini bastir

$jsonPath = $argv[1] ?? null;
$tokenArg = $argv[2] ?? null;

if (!$jsonPath || !is_file($jsonPath)) {
    fwrite(STDERR, "HATA: service account json bulunamadi: $jsonPath\n");
    exit(1);
}

$sa = json_decode(file_get_contents($jsonPath), true);
$projectId   = $sa['project_id'];
$clientEmail = $sa['client_email'];
$privateKey  = $sa['private_key'];
$tokenUri    = $sa['token_uri'] ?? 'https://oauth2.googleapis.com/token';

echo "==============================================\n";
echo "Firebase projesi : $projectId\n";
echo "Service account  : $clientEmail\n";
echo "==============================================\n\n";

// ---- 1) OAuth2 access token uret (JWT RS256) ----
function b64url($d){ return rtrim(strtr(base64_encode($d), '+/', '-_'), '='); }

$now = time();
$header = b64url(json_encode(['alg'=>'RS256','typ'=>'JWT']));
$claim  = b64url(json_encode([
    'iss'   => $clientEmail,
    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
    'aud'   => $tokenUri,
    'iat'   => $now,
    'exp'   => $now + 3600,
]));
$signingInput = "$header.$claim";
$signature = '';
if (!openssl_sign($signingInput, $signature, $privateKey, 'SHA256')) {
    fwrite(STDERR, "HATA: JWT imzalanamadi (private_key sorunu)\n");
    exit(1);
}
$jwt = "$signingInput." . b64url($signature);

$ch = curl_init($tokenUri);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt,
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
]);
$tokenResp = curl_exec($ch);
$tokenHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$tokenData = json_decode($tokenResp, true);
$accessToken = $tokenData['access_token'] ?? null;
if (!$accessToken) {
    echo "❌ OAuth access token ALINAMADI (HTTP $tokenHttp):\n$tokenResp\n";
    exit(1);
}
echo "✅ OAuth access token alindi (HTTP $tokenHttp)\n\n";

// ---- 2) Hedef token(lar)i belirle ----
$tokens = [];
if ($tokenArg) {
    $tokens[] = ['bildirim_id'=>$tokenArg, 'id'=>'(arg)', 'app_bundle'=>'?', 'son_kullanim_tarihi'=>'?'];
} else {
    // DB dockerize -> duz PDO ile baglanamiyoruz. Laravel'i bootstrap edip
    // uygulamanin KENDI DB baglantisini kullaniyoruz (docker DB'ye nasil
    // baglaniyorsa oyle). Bu yuzden bu yol php74 gerektirir.
    $autoload  = getcwd().'/vendor/autoload.php';
    $bootstrap = getcwd().'/bootstrap/app.php';
    if (!is_file($autoload) || !is_file($bootstrap)) {
        echo "❌ Laravel bulunamadi (vendor/autoload.php veya bootstrap/app.php yok).\n";
        echo "   Bu scripti PROJE KOKUNDE ve php74 ile calistirin, ya da token'i 2. arguman olarak verin.\n";
        exit(1);
    }
    require $autoload;
    $app = require $bootstrap;
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    try {
        $rows = Illuminate\Support\Facades\DB::table('bildirim_kimlikleri')
            ->where('salon_id', 432)
            ->where('aktif', 1)
            ->whereIn('kullanici_tipi', ['yetkili', 'personel'])
            ->orderBy('platform')
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id','bildirim_id','kullanici_tipi','platform','app_bundle','isletme_yetkili_id','user_id','son_kullanim_tarihi']);
        $tokens = json_decode(json_encode($rows), true);
    } catch (Exception $e) {
        echo "❌ DB sorgusu basarisiz: ".$e->getMessage()."\n";
        exit(1);
    }
    // Platform dagilimini ozetle (ios vs android karsilastirmasi icin)
    $dagilim = [];
    foreach ($tokens as $t) { $p = $t['platform'] ?: 'NULL'; $dagilim[$p] = ($dagilim[$p]??0)+1; }
    echo "salon 432 / (yetkili+personel) AKTIF token sayisi: ".count($tokens)."\n";
    echo "Platform dagilimi: ".json_encode($dagilim)."\n\n";
    if (!$tokens) {
        echo "⚠️ Hic ios token bulunamadi. Kontrol: cihaz backend'e kayit oldu mu, platform kolonu 'ios' mu.\n";
        exit(0);
    }
}

// ---- 3) Her token'a push at ----
$endpoint = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";
foreach ($tokens as $i => $row) {
    $fcmToken = $row['bildirim_id'];
    $plt = $row['platform'] ?? '?'; $ktip = $row['kullanici_tipi'] ?? '?';
    echo "----- [".($i+1)."] id={$row['id']} [{$plt}/{$ktip}] bundle={$row['app_bundle']} son_kullanim={$row['son_kullanim_tarihi']} -----\n";
    echo "token: ".substr($fcmToken,0,28)."...\n";

    $payload = json_encode([
        'message' => [
            'token' => $fcmToken,
            'notification' => [
                'title' => 'Test Bildirimi 🔔',
                'body'  => 'curl/FCM v1 test — '.date('H:i:s'),
            ],
            'data' => [ 'type' => 'test', 'olusturan' => 'fcm_test_script' ],
            'apns' => [
                'headers' => [ 'apns-priority' => '10' ],
                'payload' => [ 'aps' => [ 'sound' => 'default', 'badge' => 1 ] ],
            ],
        ],
    ]);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer '.$accessToken,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http == 200) {
        echo "✅ FCM KABUL ETTI (HTTP 200): $resp\n";
    } else {
        echo "❌ FCM HATA (HTTP $http): $resp\n";
    }
    echo "\n";
}
echo "Bitti.\n";
