<?php
/**
 * GitHub Webhook - Push sonrasi sunucuda git pull calistirir
 * URL: https://apptest.randevumcepte.com.tr/github-webhook.php
 */

// Secret'i Laravel .env dosyasindan oku
$envFile = dirname(__DIR__) . '/.env';
$secret = null;

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'GITHUB_WEBHOOK_SECRET=') === 0) {
            $secret = trim(substr($line, strlen('GITHUB_WEBHOOK_SECRET=')));
            break;
        }
    }
}

if (!$secret) {
    http_response_code(500);
    echo json_encode(['error' => 'Webhook secret not configured in .env']);
    exit(1);
}

// GitHub imzasini dogrula
$payload = file_get_contents('php://input');
$signature = isset($_SERVER['HTTP_X_HUB_SIGNATURE_256']) ? $_SERVER['HTTP_X_HUB_SIGNATURE_256'] : '';

$expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid signature']);
    exit(1);
}

// Sadece push event'lerini isle
$event = isset($_SERVER['HTTP_X_GITHUB_EVENT']) ? $_SERVER['HTTP_X_GITHUB_EVENT'] : '';
if ($event !== 'push') {
    echo json_encode(['message' => 'Event ignored: ' . $event]);
    exit(0);
}

// Sadece main branch'i isle
$data = json_decode($payload, true);
$branch = str_replace('refs/heads/', '', isset($data['ref']) ? $data['ref'] : '');

if ($branch !== 'main') {
    echo json_encode(['message' => 'Branch ignored: ' . $branch]);
    exit(0);
}

// Git pull calistir
$projectDir = dirname(__DIR__);
$output = [];
$returnCode = 0;

exec("cd {$projectDir} && git pull origin main 2>&1", $output, $returnCode);

$result = [
    'success' => $returnCode === 0,
    'branch'  => $branch,
    'output'  => implode("\n", $output),
    'time'    => date('Y-m-d H:i:s'),
];

http_response_code($returnCode === 0 ? 200 : 500);
echo json_encode($result);
