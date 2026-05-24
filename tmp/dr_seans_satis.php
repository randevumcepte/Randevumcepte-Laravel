<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

function dropdowns($h) {
    if (preg_match_all('~<select[^>]+(?:id|name)="([^"]+)"[^>]*>(.*?)</select>~is', $h, $sm, PREG_SET_ORDER)) {
        foreach ($sm as $s) {
            preg_match_all('~<option[^>]*value="([^"]*)"[^>]*>(.*?)</option>~is', $s[2], $om, PREG_SET_ORDER);
            if (count($om) > 1) {
                echo "  select id={$s[1]} (" . count($om) . " opt):\n";
                $ct = 0;
                foreach ($om as $o) {
                    $sel = strpos($o[0], 'selected') !== false ? '*' : ' ';
                    printf("   %s val=%-15s txt=%s\n", $sel, substr($o[1],0,15), trim(strip_tags($o[2])));
                    if (++$ct >= 12) { echo "    ... +" . (count($om)-12) . " daha\n"; break; }
                }
            }
        }
    }
}

echo "\n========= randevu_hakedis_raporu.aspx (GET dropdownlar) =========\n";
$h = $c->getHtml('/randevu_hakedis_raporu.aspx');
dropdowns($h);

echo "\n========= satis_raporlari.aspx (GET dropdownlar) =========\n";
$h = $c->getHtml('/satis_raporlari.aspx');
dropdowns($h);

echo "\n========= paket_satis_raporu.aspx (GET dropdownlar) =========\n";
$h = $c->getHtml('/paket_satis_raporu.aspx');
echo "  Boyut: " . strlen($h) . "\n";
dropdowns($h);

// Olasi urun satis sayfalari
echo "\n========= Olasi urun satis sayfalari =========\n";
foreach (['urun_satis_raporu.aspx','urunsatis_raporu.aspx','urunsatislari.aspx','urun_satislari.aspx','satis_listesi.aspx','urun_islemleri.aspx'] as $p) {
    $h = $c->getHtml('/' . $p);
    $stat = strlen($h);
    echo "  /$p: $stat byte" . ($stat < 4000 ? ' (404)' : ' (var)') . "\n";
}
