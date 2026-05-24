<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

foreach ([
    '01.06.2024-30.06.2024' => '1 ay',
    '01.06.2024-09.06.2024' => '9 gun',
    '01.06.2024-07.06.2024' => '1 hafta',
    '15.06.2024-21.06.2024' => '1 hafta(2)',
] as $range => $label) {
    [$s, $e] = explode('-', $range);
    $h = $c->postBack('/genel_kasa_raporu_satis.aspx', 'BTN_Ara', '', [
        'TB_TarihSec1' => $s, 'TB_TarihSec2' => $e,
    ]);
    if (!$h) { echo "$label: postback null\n"; continue; }
    preg_match_all('~name="RP_Satis\$ctl\d+\$HF_SatisID"[^>]*value="(\d+)"~', $h, $sm);
    preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tm);
    $maxTr = 0;
    foreach ($tm[1] as $t) if (preg_match_all('~<tr[^>]*>~i', $t, $rm) && count($rm[0]) > $maxTr) $maxTr = count($rm[0]);

    // Pager veya "..." benzeri postback hedefleri
    $hasPager = preg_match('~Page\$\d+~', $h) ? 'evet' : 'hayir';

    // Toplam metni
    $toplam = '';
    if (preg_match('~TB_Toplam[^>]+value="([^"]*)"~', $h, $tm2)) $toplam = $tm2[1];

    printf("%-15s %s: boyut=%d tr=%d satis=%d toplam=%s pager=%s\n",
        $label, $range, strlen($h), $maxTr, count($sm[1]), $toplam, $hasPager);
    sleep(1);
}
