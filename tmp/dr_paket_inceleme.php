<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 1) AdisyonPaketler model alanlari
echo "=== AdisyonPaketler kolonlari ===\n";
$cols = Illuminate\Support\Facades\Schema::getColumnListing('adisyon_paketler');
echo implode(', ', $cols) . "\n";
$cols2 = Illuminate\Support\Facades\Schema::getColumnListing('adisyon_paket_seanslar');
if ($cols2) echo "\nadisyon_paket_seanslar: " . implode(', ', $cols2) . "\n";

// 2) Drklinik randevu listesinde "Tip" sutunu (td[6]) tum farkli degerleri (1 ay)
$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();
$h = $c->postBack('/gunlukrandevulistesi.aspx', 'BTN_Ara', '', [
    'TB_Tarih1' => '01.06.2024', 'TB_Tarih2' => '30.06.2024',
]);
preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tm);
$bestRows = [];
foreach ($tm[1] as $t) if (preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $t, $r) && count($r[1]) > count($bestRows)) $bestRows = $r[1];

$tipler = []; $birimler = [];
$sample = [];
foreach ($bestRows as $tr) {
    if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
    preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
    if (empty($tds[1]) || count($tds[1]) < 16) continue;
    $cells = array_map(function($td){
        $c = trim(preg_replace('~\s+~',' ',strip_tags($td)));
        return trim(html_entity_decode($c, ENT_QUOTES|ENT_HTML5, 'UTF-8'));
    }, $tds[1]);
    $tip = $cells[6] ?? '';
    $birim = $cells[10] ?? '';
    $tipler[$tip] = ($tipler[$tip] ?? 0) + 1;
    if (count($sample) < 5 && stripos($tr, 'paket') !== false) $sample[] = $cells;
}
echo "\n=== Randevu 'Tip' sutunu dagilim (td[6]) ===\n";
arsort($tipler);
foreach ($tipler as $t => $c) printf("  %-25s = %d\n", $t, $c);

// 3) Bir paket satisi td icerigi (orn 06.05.2026 - SEDA KARA 100 dakika solaryum)
echo "\n=== Sample 'paket' geçen randevu satiri td icerikleri ===\n";
foreach ($sample as $i => $cells) {
    echo "Sample " . ($i+1) . ":\n";
    foreach ($cells as $j => $v) printf("  td[%2d] %s\n", $j, mb_substr($v, 0, 70));
}
