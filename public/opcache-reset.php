<?php
/**
 * OPcache reset endpoint.
 *
 * Neden: deploy.sh `git reset --hard` ile PHP dosyalarini gunceller AMA
 * php-fpm'in OPcache'i eski bytecode'u servis etmeye devam edebilir
 * (ozellikle opcache.validate_timestamps=0 ise). CLI `php artisan` FPM'in
 * opcache'ini SIFIRLAMAZ (ayri SAPI). Bu endpoint php-fpm icinde calisip
 * opcache_reset() cagirir; deploy.sh bunu curl ile tetikler.
 *
 * Guvenlik: sadece localhost (127.0.0.1/::1) + .env'deki GITHUB_WEBHOOK_SECRET
 * token'i ile. Disaridan erisim 403.
 */

$envFile = dirname(__DIR__) . '/.env';
$secret = null;
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos($line, 'GITHUB_WEBHOOK_SECRET=') === 0) {
            $secret = trim(substr($line, strlen('GITHUB_WEBHOOK_SECRET=')));
            break;
        }
    }
}

$remote  = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$isLocal = in_array($remote, ['127.0.0.1', '::1'], true);
$token   = isset($_GET['token']) ? $_GET['token'] : '';

if (!$isLocal || !$secret || !hash_equals($secret, $token)) {
    http_response_code(403);
    echo 'forbidden';
    exit;
}

$result = [
    'opcache_loaded'      => extension_loaded('Zend OPcache') || function_exists('opcache_reset'),
    'opcache_reset'       => function_exists('opcache_reset') ? opcache_reset() : null,
    'validate_timestamps' => function_exists('opcache_get_configuration')
        ? (opcache_get_configuration()['directives']['opcache.validate_timestamps'] ?? null)
        : null,
    'time'                => date('Y-m-d H:i:s'),
];

header('Content-Type: application/json');
echo json_encode($result);
