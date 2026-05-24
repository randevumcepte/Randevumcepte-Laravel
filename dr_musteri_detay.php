<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

// Onceki testteki musid'lerden biri: 1937280
foreach (['musteri.aspx?musid=1937280', 'musteri.aspx?musid=1937280&tip=d', 'musteri.aspx?id=1937280'] as $p) {
    $h = $c->getHtml('/' . $p);
    echo "/$p: " . strlen($h) . " byte\n";
    if (strlen($h) > 5000) {
        // Telefon ara
        if (preg_match('~name="(TB_[^"]*[Tt]elefon[^"]*)"[^>]+value="([^"]+)"~i', $h, $m)) {
            echo "  $m[1] = $m[2]\n";
        }
        if (preg_match('~name="(TB_[^"]*Cep[^"]*)"[^>]+value="([^"]+)"~i', $h, $m)) {
            echo "  $m[1] = $m[2]\n";
        }
        // Ad, soyad
        foreach (['TB_Ad','TB_Soyad','TB_AdSoyad','TB_FullName'] as $f) {
            if (preg_match('~name="' . $f . '"[^>]+value="([^"]+)"~i', $h, $m)) {
                echo "  $f = $m[1]\n";
            }
        }
        break;
    }
}

// 2) Randevu listesinde musid linki var mi?
echo "\n=== Randevu satirinda musid kontrolu ===\n";
$h = $c->postBack('/gunlukrandevulistesi.aspx', 'BTN_Ara', '', [
    'TB_Tarih1' => '06.05.2026', 'TB_Tarih2' => '06.05.2026',
]);
preg_match_all('~href="(musteri\.aspx\?musid=\d+[^"]*)"~', $h, $m);
$unique = array_unique($m[1]);
echo "musid linkleri (ilk 5): " . count($unique) . " unique\n";
foreach (array_slice($unique, 0, 5) as $l) echo "  $l\n";
