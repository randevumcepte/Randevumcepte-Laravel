<?php
$dump = $argv[1];
foreach (['musterilistesi', 'hizmet_listesi'] as $name) {
    $f = $dump . "/probe_{$name}_aspx_200.body";
    if (!file_exists($f)) { echo "yok: $f\n"; continue; }
    $h = file_get_contents($f);
    echo "\n========= $name.aspx =========\n";

    preg_match_all('#<th[^>]*>(.*?)</th>#is', $h, $m);
    echo "Sutun basliklari (" . count($m[1]) . "):\n";
    foreach ($m[1] as $i => $t) printf("  %2d: %s\n", $i, trim(strip_tags($t)));

    preg_match_all('#<tr[^>]*>(.*?)</tr>#is', $h, $rows);
    echo "Toplam tr: " . count($rows[1]) . "\n";

    // Ilk veri satiri (1. index, 0 header genelde)
    if (isset($rows[1][1])) {
        echo "\nIlk satirdaki <td>'ler:\n";
        preg_match_all('#<td[^>]*>(.*?)</td>#is', $rows[1][1], $tds);
        foreach ($tds[1] as $i => $td) {
            $clean = trim(preg_replace('/\s+/', ' ', strip_tags($td)));
            if (strlen($clean) > 200) $clean = substr($clean, 0, 200) . '...';
            printf("  td[%d]: %s\n", $i, $clean);
        }
    }
}
