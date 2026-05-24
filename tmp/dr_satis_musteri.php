<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

$h = $c->postBack('/genel_kasa_raporu_satis.aspx', 'BTN_Ara', '', [
    'TB_TarihSec1' => '01.06.2024', 'TB_TarihSec2' => '07.06.2024',
]);
echo "Boyut: " . strlen($h) . "\n";

// Repeater satirlarinda "Musteri Sayfasini Ac" linki
preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $h, $rows);
$shown = 0;
foreach ($rows[1] as $tr) {
    if (stripos($tr, 'Müşteri Sayfasını Aç') === false && stripos($tr, 'Musteri Sayfasini') === false) continue;
    if (++$shown > 3) break;
    echo "\n--- Satir $shown ---\n";
    // ilk td: musteri linki
    if (preg_match('~<a[^>]+href="([^"]+)"[^>]*>~i', $tr, $m)) echo "  href: " . $m[1] . "\n";
    if (preg_match_all('~__doPostBack\([\'"&#39;]+([^\'"&]+)[\'"&#39;]+,\s*[\'"&#39;]+([^\'"&]*)[\'"&#39;]+~', $tr, $pm)) {
        foreach ($pm[1] as $i => $t) printf("  postback target=%s arg=%s\n", $t, $pm[2][$i]);
    }
    // Hidden field MusteriID benzeri
    if (preg_match_all('~<input[^>]+name="([^"]+)"[^>]*value="([^"]+)"~', $tr, $im, PREG_SET_ORDER)) {
        foreach ($im as $row) if (preg_match('/MusteriID|MusID|HF/i', $row[1])) printf("  HF: %s = %s\n", $row[1], $row[2]);
    }
}

// Bir musteri detay sayfasi nasil acilir? Link uzerinden bak
echo "\n=== Olasi musteri detay sayfasi ===\n";
foreach (['musteridetay.aspx?id=161011','musteri_detay.aspx?id=161011','musteri_dosya.aspx?id=161011','dosya.aspx?id=161011'] as $p) {
    $r = $c->getHtml('/' . $p);
    $stat = strlen($r);
    echo "  /$p: $stat byte" . ($stat < 4000 ? ' (404)' : ' (var)') . "\n";
}
