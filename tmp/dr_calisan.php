<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();
foreach (['calisanmodulu.aspx', 'kullanici_listesi.aspx', 'ayarlar.aspx'] as $p) {
    $h = $c->getHtml('/' . $p, 'inspect_' . preg_replace('/[^a-z0-9]+/i','_',$p));
    echo "\n========= $p (" . strlen($h) . " byte) =========\n";
    if (preg_match_all('#<select[^>]+(?:id|name)="([^"]+)"[^>]*>(.*?)</select>#is', $h, $sm, PREG_SET_ORDER)) {
        foreach ($sm as $s) {
            preg_match_all('#<option[^>]*value="([^"]*)"[^>]*>(.*?)</option>#i', $s[2], $om, PREG_SET_ORDER);
            if (count($om) > 1) {
                echo "  <select id={$s[1]} options=" . count($om) . ">\n";
                $cnt = 0;
                foreach ($om as $o) {
                    printf("    %-12s %s\n", substr($o[1],0,12), trim(strip_tags($o[2])));
                    if (++$cnt >= 15) { echo "    ... +" . (count($om)-15) . " daha\n"; break; }
                }
            }
        }
    }
    if (preg_match_all('#<th[^>]*>(.*?)</th>#is', $h, $tm)) {
        $clean = array_filter(array_map(function($t){return trim(strip_tags($t));}, $tm[1]));
        if ($clean) echo "  TH: " . implode(' | ', array_slice($clean, 0, 15)) . "\n";
    }
    $tdCount = preg_match_all('#<td[^>]*>#i', $h);
    echo "  td count: $tdCount\n";
}
echo "\nDump: " . $c->dumpDir() . "\n";
