<?php
$dump = '/var/www/www-root/data/www/randevumceptetest/storage/app/planla/20260420_170010';
$f = glob($dump . '/connectRaw_*category_finances_event_read_200.body')[0] ?? null;
if (!$f) { echo "dump yok\n"; exit; }
$j = json_decode(file_get_contents($f), true);
$d = $j['data'] ?? [];

$prices = [];
$pricesNonZero = 0;
$priceFields = []; // toplam farkli fiyat-benzeri alan adlari
$nonZeroSample = [];

foreach ($d as $r) {
    foreach ($r as $k => $v) {
        if (is_string($v) || is_numeric($v)) {
            if (preg_match('/(price|amount|tutar|sum|fiyat|cost|paid|payment)/i', $k)) {
                $priceFields[$k] = ($priceFields[$k] ?? 0) + 1;
            }
        }
    }
    $p = (float) preg_replace('/[^0-9.\-]/', '', (string) ($r['price'] ?? '0'));
    $prices[$p] = ($prices[$p] ?? 0) + 1;
    if ($p > 0 && count($nonZeroSample) < 3) $nonZeroSample[] = $r;
    if ($p > 0) $pricesNonZero++;
}
echo "Toplam: " . count($d) . "\n";
echo "price > 0: $pricesNonZero\n";
echo "price = 0: " . ($prices[0] ?? 0) . "\n";
echo "Farkli price degerleri (en cok 10):\n";
arsort($prices);
$i = 0;
foreach ($prices as $v => $c) { echo "  $v: $c\n"; if (++$i >= 10) break; }

echo "\nFiyat-benzeri tum field'lar:\n";
foreach ($priceFields as $k => $c) echo "  $k: $c\n";

echo "\nIlk 3 price>0 ornegi:\n";
echo json_encode($nonZeroSample, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
