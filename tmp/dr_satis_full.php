<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

// Tum CheckBoxList itemlerini "on" yap (ASP.NET'te checked = name=...$N, value=on)
$body = [
    'TB_TarihSec1' => '01.06.2024',
    'TB_TarihSec2' => '30.06.2024',
    'DDL_RaporSecenegi' => 'Detaylı Rapor',
];
foreach ([
    'DDL_AramaTipi' => 4,
    'DDL_HizmetListesi' => 329,
    'DDL_UrunListesi' => 112,
    'DDL_BirimListesi' => 9,
    'DDL_MarkaListesi' => 2,
    'DDL_CalisanListesi' => 40,
] as $name => $count) {
    for ($i = 0; $i < $count; $i++) $body["{$name}\${$i}"] = 'on';
}

$h = $c->postBack('/satis_raporlari.aspx', 'BTN_Ara', '', $body);
if (!$h) { echo "POSTBACK NULL\n"; exit; }
echo "Boyut: " . strlen($h) . "\n";

if (preg_match_all('~<th[^>]*>(.*?)</th>~is', $h, $tm)) {
    $clean = array_filter(array_map(function($t){return trim(html_entity_decode(strip_tags($t), ENT_QUOTES|ENT_HTML5, 'UTF-8'));}, $tm[1]));
    if ($clean) echo "TH (" . count($clean) . "): " . implode(' | ', array_slice(array_unique($clean), 0, 25)) . "\n";
}

preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tlist);
$best = ''; $maxRows = 0;
foreach ($tlist[1] as $t) {
    if (preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $t, $r) && count($r[1]) > $maxRows) { $maxRows = count($r[1]); $best = $t; }
}
echo "En genis tr: $maxRows\n";

preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $best, $rows);
$shown = 0;
foreach ($rows[1] as $tr) {
    if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
    preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
    if (empty($tds[1])) continue;
    if (++$shown > 3) break;
    echo "Satir $shown (" . count($tds[1]) . " td):\n";
    foreach ($tds[1] as $i => $td) {
        $clean = trim(preg_replace('~\s+~', ' ', strip_tags($td)));
        $clean = trim(html_entity_decode($clean, ENT_QUOTES|ENT_HTML5, 'UTF-8'));
        if (strlen($clean) > 70) $clean = substr($clean, 0, 70) . '...';
        printf("  td[%2d] %s\n", $i, $clean);
    }
}
