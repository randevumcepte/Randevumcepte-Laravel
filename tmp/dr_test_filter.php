<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

function dump($c, $path, $btn, $body, $label) {
    echo "\n========= $label =========\n";
    $h = $c->postBack('/' . $path, $btn, '', $body);
    if (!$h) { echo "POSTBACK NULL\n"; return; }
    echo "Boyut: " . strlen($h) . "\n";
    if (preg_match_all('~<th[^>]*>(.*?)</th>~is', $h, $tm)) {
        $clean = array_filter(array_map(function($t){return trim(html_entity_decode(strip_tags($t), ENT_QUOTES|ENT_HTML5, 'UTF-8'));}, $tm[1]));
        if ($clean) echo "TH (" . count($clean) . "): " . implode(' | ', array_slice(array_unique($clean), 0, 22)) . "\n";
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
        if (++$shown > 2) break;
        echo "Satir $shown (" . count($tds[1]) . " td):\n";
        foreach ($tds[1] as $i => $td) {
            $clean = trim(preg_replace('~\s+~', ' ', strip_tags($td)));
            $clean = trim(html_entity_decode($clean, ENT_QUOTES|ENT_HTML5, 'UTF-8'));
            if (strlen($clean) > 70) $clean = substr($clean, 0, 70) . '...';
            printf("  td[%2d] %s\n", $i, $clean);
        }
    }
}

// 1) Satis - Detayli Rapor
dump($c, 'satis_raporlari.aspx', 'BTN_Ara', [
    'TB_TarihSec1' => '01.06.2024', 'TB_TarihSec2' => '30.06.2024',
    'DDL_RaporSecenegi' => 'Detaylı Rapor',
], 'SATIS Detayli');

// 2) Paket satis - bir personel ile (Berna yilmaz: 11402)
dump($c, 'paket_satis_raporu.aspx', 'BTN_Ara', [
    'TB_TarihSec1' => '01.06.2024', 'TB_TarihSec2' => '30.06.2024',
    'DDL_Calisan' => '11402',
], 'PAKET SATIS (Berna)');

// 3) Hakedis - sayfanin tum form alanlarini print et
echo "\n========= HAKEDIS GET tum alanlar =========\n";
$h = $c->getHtml('/randevu_hakedis_raporu.aspx');
echo "Boyut: " . strlen($h) . "\n";
preg_match_all('~<input[^>]+name="([^"]+)"[^>]*?(?:value="([^"]*)")?[^>]*?/?>~i', $h, $im, PREG_SET_ORDER);
echo "INPUT alanlari:\n";
foreach ($im as $i) {
    if (preg_match('/^__/', $i[1])) continue;
    printf("  %-30s = [%s]\n", $i[1], $i[2] ?? '');
}
preg_match_all('~<select[^>]+name="([^"]+)"[^>]*>~i', $h, $sm);
echo "SELECT alanlari: " . implode(', ', $sm[1]) . "\n";
