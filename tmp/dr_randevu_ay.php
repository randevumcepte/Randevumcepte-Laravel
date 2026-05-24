<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

foreach (['01.06.2024-30.06.2024', '01.01.2024-31.12.2024', '01.06.2024-15.06.2024'] as $range) {
    [$s, $e] = explode('-', $range);
    $h = $c->postBack('/gunlukrandevulistesi.aspx', 'BTN_Ara', '', [
        'TB_Tarih1' => $s, 'TB_Tarih2' => $e,
    ]);
    if (!$h) { echo "$range: postback null\n"; continue; }
    preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tm);
    $best = 0;
    foreach ($tm[1] as $t) if (preg_match_all('~<tr[^>]*>~i', $t, $r) && count($r[0]) > $best) $best = count($r[0]);
    printf("  %-30s -> %d byte, %d tr\n", $range, strlen($h), $best);
    sleep(1);
}
