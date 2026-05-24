<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

// Bir musteri (DAMLA UMDU vs benzeri ya da ilk Haziran 2024'tekilerden)
$musid = 1937280; // ASLI HACIOGLU - test musid
$h = $c->getHtml('/musteri.aspx?musid=' . $musid, 'inspect_musteri');
echo "Boyut: " . strlen($h) . "\n\n";

// Sekme/tab linkleri
echo "=== Sekmeler/Tablar ===\n";
if (preg_match_all('~<a[^>]+(?:href|onclick)="([^"]+)"[^>]*>([^<]+)</a>~i', $h, $m, PREG_SET_ORDER)) {
    foreach ($m as $row) {
        $t = trim($row[2]);
        if (preg_match('~satis|seans|paket|urun|odeme|hizmet|tahsilat|randevu/i~iu', $t)) {
            echo "  text=[$t] href=" . substr($row[1], 0, 80) . "\n";
        }
    }
}

// Tum tablolari incele - hangi tabloda ne var
echo "\n=== Tablolar (icerigi az olanlar atlandi) ===\n";
preg_match_all('~<table[^>]*(?:id|class)="([^"]*)"[^>]*>(.*?)</table>~is', $h, $tm, PREG_SET_ORDER);
foreach ($tm as $i => $row) {
    $tableId = $row[1];
    $body = $row[2];
    preg_match_all('~<tr[^>]*>~i', $body, $r);
    $trCount = count($r[0]);
    if ($trCount < 2) continue;
    echo "\nTablo[$i] id/class=\"$tableId\" tr=$trCount:\n";
    if (preg_match_all('~<th[^>]*>(.*?)</th>~is', $body, $th)) {
        $headers = array_filter(array_map(function($t){return trim(strip_tags($t));}, $th[1]));
        if ($headers) echo "  TH: " . implode(' | ', array_slice(array_unique($headers), 0, 15)) . "\n";
    }
    // Ilk 1 veri satiri
    preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $body, $rows);
    foreach ($rows[1] as $tr) {
        if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
        preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
        if (empty($tds[1])) continue;
        $cells = [];
        foreach ($tds[1] as $td) {
            $clean = trim(preg_replace('~\s+~',' ',strip_tags($td)));
            $cells[] = trim(html_entity_decode($clean, ENT_QUOTES|ENT_HTML5, 'UTF-8'));
        }
        $sample = array_slice($cells, 0, 10);
        $sample = array_map(function($s){ return mb_substr($s, 0, 35); }, $sample);
        echo "  ilk satir: " . implode(' | ', $sample) . "\n";
        break;
    }
}

// Repeater/GridView control id'leri
echo "\n=== Postback hedefleri (Satis/Seans/Paket/Urun) ===\n";
if (preg_match_all('~__doPostBack\([\'\"&#39;]+([^\'\"&]+)[\'\"&#39;]+~', $h, $pm)) {
    $unique = array_unique($pm[1]);
    foreach ($unique as $t) {
        if (preg_match('~Satis|Seans|Paket|Urun|Odeme|Tahsilat~i', $t)) echo "  $t\n";
    }
}
