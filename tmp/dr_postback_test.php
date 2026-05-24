<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

// 1) Urunler — tarih yok, direkt parse
$h = $c->getHtml('/uruntanimlamalari.aspx');
echo "=== URUNLER (uruntanimlamalari.aspx) ===\n";
showStruct($h);

// 2) Tahsilatlar — BTN_Ara postback
$h = $c->postBack('/kasa_islemleri.aspx', 'BTN_Ara', '', [
    'TB_TarihSec1' => '01.01.2015',
    'TB_TarihSec2' => '31.12.2030',
]);
echo "\n=== TAHSILATLAR (kasa_islemleri.aspx, BTN_Ara) ===\n";
if ($h) showStruct($h);
else echo "  postback null\n";

// 3) Satislar
$h = $c->postBack('/satis_raporlari.aspx', 'BTN_Ara', '', [
    'TB_TarihSec1' => '01.01.2015',
    'TB_TarihSec2' => '31.12.2030',
]);
echo "\n=== SATISLAR (satis_raporlari.aspx, BTN_Ara) ===\n";
if ($h) showStruct($h);
else echo "  postback null\n";

// 4) Randevular
$h = $c->postBack('/gunlukrandevulistesi.aspx', 'BTN_Ara', '', [
    'TB_Tarih1' => '01.01.2015',
    'TB_Tarih2' => '31.12.2030',
]);
echo "\n=== RANDEVULAR (gunlukrandevulistesi.aspx, BTN_Ara) ===\n";
if ($h) showStruct($h);
else echo "  postback null\n";

// 5) Hakedis/Seans
$h = $c->postBack('/randevu_hakedis_raporu.aspx', 'BTN_Ara', '', [
    'TB_Tarih1' => '01.01.2015',
    'TB_Tarih2' => '31.12.2030',
]);
echo "\n=== HAKEDIS (randevu_hakedis_raporu.aspx, BTN_Ara) ===\n";
if ($h) showStruct($h);
else echo "  postback null\n";

function showStruct($h) {
    echo "  Boyut: " . strlen($h) . " byte\n";
    if (preg_match_all('~<th[^>]*>(.*?)</th>~is', $h, $m)) {
        $clean = array_filter(array_map(function($t){return trim(strip_tags($t));}, $m[1]));
        if ($clean) echo "  TH: " . implode(' | ', array_slice(array_unique($clean), 0, 20)) . "\n";
    }
    // En genis tablo tr sayisi
    if (preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tm)) {
        $best = 0;
        foreach ($tm[1] as $t) {
            if (preg_match_all('~<tr[^>]*>~i', $t, $rm)) if (count($rm[0]) > $best) $best = count($rm[0]);
        }
        echo "  En genis tablo tr: $best\n";
    }
    // Pagination postback'leri
    if (preg_match_all('~__doPostBack\(&#39;([^&]+)&#39;,&#39;Page\$(\d+)&#39;~', $h, $m)) {
        $unique = array_unique(array_map(function($x,$y){return $x;}, $m[1], $m[2]));
        $pages = array_unique($m[2]);
        echo "  Pagination: target=" . ($unique[0] ?? '-') . " sayfalar=" . implode(',', array_slice($pages,0,10)) . (count($pages)>10?'...':'') . "\n";
    }
}
