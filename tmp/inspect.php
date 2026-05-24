<?php
$dump = '/var/www/www-root/data/www/randevumceptetest/storage/app/planla/20260420_170010';

echo "================= APPOINTMENT field listesi =================\n";
$f = glob($dump . '/connectRaw_*category_appointments_event_read_200.body')[0] ?? null;
if ($f) {
    $j = json_decode(file_get_contents($f), true);
    $d = $j['data'] ?? [];
    $keys = [];
    foreach ($d as $r) foreach (array_keys($r) as $k) $keys[$k] = ($keys[$k] ?? 0) + 1;
    arsort($keys);
    foreach ($keys as $k => $c) echo "  $k: $c\n";
}

echo "\n================= FINANCES dagilimi =================\n";
$f = glob($dump . '/connectRaw_*category_finances_event_read_200.body')[0] ?? null;
if ($f) {
    $j = json_decode(file_get_contents($f), true);
    $d = $j['data'] ?? [];
    echo "Toplam: " . count($d) . "\n";
    $cat = []; $sec = []; $pm = []; $hasApp = 0; $hasSrv = 0; $hasPkg = 0; $hasPrd = 0;
    foreach ($d as $r) {
        $c = $r['category'] ?? ''; $cat[$c] = ($cat[$c] ?? 0) + 1;
        $s = $r['section']  ?? ''; $sec[$s] = ($sec[$s] ?? 0) + 1;
        $m = $r['paymentMethod'] ?? '(bos)'; $pm[$m] = ($pm[$m] ?? 0) + 1;
        if (!empty($r['appointment'])) $hasApp++;
        if (!empty($r['service'])) $hasSrv++;
        if (!empty($r['package'])) $hasPkg++;
        if (!empty($r['product'])) $hasPrd++;
    }
    echo "category dagilimi:\n";  foreach ($cat as $k => $v) echo "  $k: $v\n";
    echo "section dagilimi:\n";   foreach ($sec as $k => $v) echo "  $k: $v\n";
    echo "paymentMethod dagilimi:\n"; foreach ($pm as $k => $v) echo "  $k: $v\n";
    echo "appointment dolu: $hasApp / " . count($d) . "\n";
    echo "service dolu: $hasSrv\n";
    echo "package dolu: $hasPkg\n";
    echo "product dolu: $hasPrd\n";
    // Negatif (gider) ornek var mi
    $expense = array_filter($d, function ($r) { return ($r['section'] ?? '') === 'expense'; });
    if ($expense) {
        echo "\nIlk 1 expense ornegi:\n";
        echo json_encode(array_values($expense)[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    }
}
