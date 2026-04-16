<?php
/**
 * GitHub Webhook - Push sonrasi sunucuda git pull calistirir
 * URL: https://apptest.randevumcepte.com.tr/github-webhook.php
 */

$secret = getenv('GITHUB_WEBHOOK_SECRET');

// Secret tanimli degilse calis
if (!$secret) {
    http_response_code(500);
    echo json_encode(['error' => 'Webhook secret not configured']);
    exit(1);
}

// GitHub imzasini dogrula
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

$expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid signature']);
    exit(1);
}

// Sadece push event'lerini isle
$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
if ($event !== 'push') {
    echo json_encode(['message' => 'Event ignored: ' . $event]);
    exit(0);
}

// Sadece main branch'i isle
$data = json_decode($payload, true);
$branch = str_replace('refs/heads/', '', $data['ref'] ?? '');

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
