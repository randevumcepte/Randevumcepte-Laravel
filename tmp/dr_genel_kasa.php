<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

// Once GET (default tarih, sayfa kendisi data'siyla geliyor mu)
echo "=== GET ===\n";
$h = $c->getHtml('/genel_kasa_raporu_satis.aspx');
echo "Boyut: " . strlen($h) . "\n";

// TH'ler
if (preg_match_all('~<th[^>]*>(.*?)</th>~is', $h, $tm)) {
    $clean = array_filter(array_map(function($t){return trim(html_entity_decode(strip_tags($t), ENT_QUOTES|ENT_HTML5, 'UTF-8'));}, $tm[1]));
    echo "TH (" . count($clean) . "): " . implode(' | ', array_slice(array_unique($clean), 0, 25)) . "\n";
}

// SatisID'ler
preg_match_all('~name="(RP_Satis\$ctl\d+\$HF_SatisID)"[^>]*value="([^"]*)"~', $h, $sm, PREG_SET_ORDER);
echo "SatisID listesi (GET'te ilk sayfa):\n";
foreach ($sm as $s) printf("  %s = %s\n", $s[1], $s[2]);

// Detay link/onclick
preg_match_all('~(?:href|onclick)="([^"]*(?:satis_detay|dosya|incele|musteri_islem)[^"]*)"~i', $h, $lm);
echo "Detay linkleri:\n";
foreach (array_unique($lm[1] ?? []) as $l) echo "  $l\n";

// Repeater satirinin ilk td'leri
preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tlist);
$best = ''; $maxRows = 0;
foreach ($tlist[1] as $t) {
    if (preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $t, $r) && count($r[1]) > $maxRows) { $maxRows = count($r[1]); $best = $t; }
}
echo "En genis tablo tr: $maxRows\n";
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
        if (strlen($clean) > 80) $clean = substr($clean, 0, 80) . '...';
        printf("  td[%2d] %s\n", $i, $clean);
    }
}

// 2) Detay sayfasi: satis_detay.aspx mi? Bir SatisID ile dene
$satisId = $sm[0][2] ?? null;
if ($satisId) {
    echo "\n=== Satis detay denemeleri (id=$satisId) ===\n";
    foreach (['satis_detay.aspx?id=' . $satisId, 'dosya.aspx?id=' . $satisId, 'satis_islemleri.aspx?id=' . $satisId, 'satis_islem.aspx?id=' . $satisId] as $p) {
        $h = $c->getHtml('/' . $p);
        $stat = strlen($h);
        echo "  /$p: $stat byte" . ($stat < 4000 ? ' (404)' : ' (var)') . "\n";
    }
}
