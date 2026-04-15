<?php
// =============================================================
// GitHub Webhook Endpoint
// GitHub push event geldiğinde deploy.sh'ı çalıştırır
// =============================================================

// ---- AYARLAR ----
// GitHub Webhook ayarlarında belirlediğiniz secret key
$secret = 'BURAYA_GUCLU_BIR_SECRET_KEY_YAZIN';

// Deploy script yolu
$deployScript = dirname(__DIR__) . '/deploy.sh';

// Log dosyası
$logFile = dirname(__DIR__) . '/storage/logs/webhook.log';

// ---- GÜVENLİK KONTROLÜ ----
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');

if (empty($signature)) {
    http_response_code(403);
    die('Signature eksik');
}

$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);
if (!hash_equals($hash, $signature)) {
    http_response_code(403);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Geçersiz signature\n", FILE_APPEND);
    die('Geçersiz signature');
}

// ---- EVENT KONTROLÜ ----
$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
$data = json_decode($payload, true);

// Sadece push event'lerinde deploy yap
if ($event !== 'push') {
    echo "Event: $event - deploy gerekmiyor";
    exit;
}

// Hangi branch'a push yapıldığını kontrol et
$branch = $data['ref'] ?? '';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Push alındı: $branch\n", FILE_APPEND);

// ---- DEPLOY'U ÇALIŞTIR ----
// deploy.sh'ı arka planda çalıştır (webhook timeout olmasın)
exec("chmod +x $deployScript");
exec("bash $deployScript > /dev/null 2>&1 &");

http_response_code(200);
echo json_encode([
    'status' => 'ok',
    'message' => 'Deploy başlatıldı',
    'branch' => $branch,
    'time' => date('Y-m-d H:i:s')
]);
