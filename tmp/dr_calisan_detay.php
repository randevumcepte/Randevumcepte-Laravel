<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

// Berna yilmaz id=11402 duzenle formu
$h = $c->getHtml('/calisan_ekle.aspx?id=11402&t=d');
echo "Sayfa boyut: " . strlen($h) . "\n\n";

// Tum input alanlarini listele
preg_match_all('#<input[^>]+name="([^"]+)"[^>]*?(?:value="([^"]*)")?[^>]*?/?>#i', $h, $m, PREG_SET_ORDER);
echo "==== Input field'lari ====\n";
foreach ($m as $row) {
    $name = $row[1]; $val = $row[2] ?? '';
    if (preg_match('/^__(VIEWSTATE|EVENTVALIDATION|VIEWSTATEGENERATOR|EVENTTARGET|EVENTARGUMENT)/', $name)) continue;
    if (strlen($val) > 80) $val = substr($val, 0, 80) . '...';
    printf("  %-40s = %s\n", $name, $val);
}

echo "\n==== Select'ler ====\n";
preg_match_all('#<select[^>]+name="([^"]+)"[^>]*>(.*?)</select>#is', $h, $sm, PREG_SET_ORDER);
foreach ($sm as $s) {
    $sel = '';
    if (preg_match('#<option[^>]+selected[^>]*value="([^"]*)"[^>]*>(.*?)</option>#i', $s[2], $m2)) {
        $sel = $m2[1] . ' (' . trim(strip_tags($m2[2])) . ')';
    }
    printf("  %-40s = %s\n", $s[1], $sel);
}

echo "\n==== Telefon/GSM/cep ile eslesen text ====\n";
$plain = strip_tags($h);
foreach (['Telefon', 'GSM', 'Cep', 'Mobil', 'Tel:', 'Telefon:'] as $kw) {
    if (preg_match("#({$kw}[^\n<]{0,80})#i", $plain, $m)) echo "  " . trim($m[1]) . "\n";
}
