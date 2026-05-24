<?php
$d = $argv[1];
$f = glob($d . 'get_calisanmodulu*.body')[0] ?? glob($d . '*calisanmodulu*.body')[0] ?? null;
if (!$f) { echo "calisanmodulu dump yok\n"; exit; }
$h = file_get_contents($f);
echo "Dosya: " . basename($f) . " (" . strlen($h) . " byte)\n";

// En genis tablo
preg_match_all('#<table[^>]*>(.*?)</table>#is', $h, $tables);
$bestRows = [];
foreach ($tables[1] as $t) {
    if (preg_match_all('#<tr[^>]*>(.*?)</tr>#is', $t, $r)) {
        if (count($r[1]) > count($bestRows)) $bestRows = $r[1];
    }
}
echo "Toplam tr: " . count($bestRows) . "\n\n";

// Ilk 3 veri satirini raw goster
$shown = 0;
foreach ($bestRows as $tr) {
    if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
    preg_match_all('#<td[^>]*>(.*?)</td>#is', $tr, $tds);
    if (empty($tds[1])) continue;
    if (++$shown > 2) break;
    echo "===== Satir $shown — " . count($tds[1]) . " td =====\n";
    foreach ($tds[1] as $i => $td) {
        $raw = trim($td);
        if (strlen($raw) > 250) $raw = substr($raw, 0, 250) . '...';
        $text = trim(strip_tags($td));
        // input value var mi
        $inputVal = '';
        if (preg_match('#<input[^>]+value="([^"]*)"#i', $td, $m)) $inputVal = $m[1];
        printf("td[%d] text=[%s] inputVal=[%s]\n  raw=%s\n", $i, $text, $inputVal, $raw);
    }
    echo "\n";
}
