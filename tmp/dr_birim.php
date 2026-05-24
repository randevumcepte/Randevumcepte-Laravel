<?php
foreach ($argv as $i => $f) {
    if ($i === 0) continue;
    if (!file_exists($f)) { echo "yok: $f\n"; continue; }
    $h = file_get_contents($f);
    echo "\n========= " . basename($f) . " (" . strlen($h) . " byte) =========\n";

    preg_match_all('#<select[^>]+(?:id|name)="([^"]+)"[^>]*>(.*?)</select>#is', $h, $m, PREG_SET_ORDER);
    foreach ($m as $sel) {
        preg_match_all('#<option[^>]*value="([^"]*)"[^>]*>(.*?)</option>#i', $sel[2], $opts, PREG_SET_ORDER);
        if (count($opts) < 2) continue;
        echo "\n--- <select> id={$sel[1]} (" . count($opts) . " opt) ---\n";
        $c = 0;
        foreach ($opts as $o) {
            $val = $o[1]; $txt = trim(strip_tags($o[2]));
            printf("    val=%-15s txt=%s\n", substr($val, 0, 15), $txt);
            if (++$c >= 25) { echo "    ... (kalan " . (count($opts) - 25) . ")\n"; break; }
        }
    }

    // Tablo basliklari
    if (preg_match_all('#<th[^>]*>(.*?)</th>#is', $h, $thm)) {
        echo "\n--- TH'ler (" . count($thm[1]) . ") ---\n";
        foreach ($thm[1] as $i => $t) {
            $s = trim(strip_tags($t));
            if ($s && strlen($s) < 100) printf("    [%d] %s\n", $i, $s);
        }
    }
}
