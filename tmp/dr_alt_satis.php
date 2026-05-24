<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

// 1) genel_kasa_raporu_satis.aspx - daha basit yapida olabilir
echo "=== genel_kasa_raporu_satis.aspx ===\n";
$h = $c->getHtml('/genel_kasa_raporu_satis.aspx');
echo "GET boyut: " . strlen($h) . "\n";
preg_match_all('~<input[^>]+name="([^"]+)"~i', $h, $im);
$inputs = array_unique(array_filter($im[1], function($n){return !preg_match('/^__/', $n);}));
echo "Inputlar: " . implode(', ', $inputs) . "\n";

$h = $c->postBack('/genel_kasa_raporu_satis.aspx', 'BTN_Ara', '', [
    'TB_TarihSec1' => '01.06.2024', 'TB_TarihSec2' => '30.06.2024',
]);
echo "POSTBACK boyut: " . ($h ? strlen($h) : 0) . "\n";
if ($h) {
    if (preg_match_all('~<th[^>]*>(.*?)</th>~is', $h, $tm)) {
        $clean = array_filter(array_map(function($t){return trim(html_entity_decode(strip_tags($t), ENT_QUOTES|ENT_HTML5, 'UTF-8'));}, $tm[1]));
        if ($clean) echo "TH: " . implode(' | ', array_slice(array_unique($clean), 0, 25)) . "\n";
    }
    preg_match_all('~<tr[^>]*>~i', $h, $rm);
    echo "tr sayisi: " . count($rm[0]) . "\n";
}

// 2) satis_onay_listesi.aspx
echo "\n=== satis_onay_listesi.aspx ===\n";
$h = $c->getHtml('/satis_onay_listesi.aspx');
echo "GET boyut: " . strlen($h) . "\n";
$h = $c->postBack('/satis_onay_listesi.aspx', 'BTN_Ara', '', [
    'TB_TarihSec1' => '01.06.2024', 'TB_TarihSec2' => '30.06.2024',
]);
echo "POSTBACK boyut: " . ($h ? strlen($h) : 0) . "\n";
if ($h) {
    if (preg_match_all('~<th[^>]*>(.*?)</th>~is', $h, $tm)) {
        $clean = array_filter(array_map(function($t){return trim(html_entity_decode(strip_tags($t), ENT_QUOTES|ENT_HTML5, 'UTF-8'));}, $tm[1]));
        if ($clean) echo "TH: " . implode(' | ', array_slice(array_unique($clean), 0, 25)) . "\n";
    }
    preg_match_all('~<tr[^>]*>~i', $h, $rm);
    echo "tr sayisi: " . count($rm[0]) . "\n";
    // Ilk 2 veri satiri
    preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $h, $rows);
    $shown = 0;
    foreach ($rows[1] as $tr) {
        if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
        preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
        if (empty($tds[1])) continue;
        if (++$shown > 2) break;
        echo "Satir $shown (" . count($tds[1]) . " td):\n";
        foreach ($tds[1] as $i => $td) {
            $clean = trim(preg_replace('~\s+~', ' ', strip_tags($td)));
            $clean = trim(html_entity_decode($clean, ENT_QUOTES|ENT_HTML5, 'UTF-8'));
            if (strlen($clean) > 60) $clean = substr($clean, 0, 60) . '...';
            printf("  td[%2d] %s\n", $i, $clean);
        }
    }
}

// 3) DOSYA INCELE - tahsilatdaki ilk satirdan link cikart
echo "\n=== Tahsilattan 'Dosyayi incele' onclick ===\n";
$h = $c->postBack('/kasa_islemleri.aspx', 'BTN_Ara', '', [
    'TB_TarihSec1' => '01.06.2024', 'TB_TarihSec2' => '30.06.2024',
]);
// "Dosyayı incele" yakinindaki onclick veya href yakala
if (preg_match_all('~(?:href|onclick)="([^"]*Dosya[^"]*)"~i', $h, $m)) {
    foreach (array_slice(array_unique($m[1]), 0, 5) as $hr) echo "  $hr\n";
}
// "Dosya" ile baslayan veya iceren tum link/button onclick'ler
if (preg_match_all('~<a[^>]+(?:href|onclick)="([^"]+)"[^>]*>\s*(?:<[^>]+>)*\s*Dosyay[ıi][^<]+~iu', $h, $m)) {
    foreach (array_slice(array_unique($m[1]), 0, 5) as $hr) echo "  link: $hr\n";
}
