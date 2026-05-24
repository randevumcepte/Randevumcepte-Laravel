<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

// Liste sayfasindan tum personel id'leri
$h = $c->getHtml('/calisanmodulu.aspx');
preg_match_all('#calisan_ekle\.aspx\?id=(\d+)&t=d#', $h, $m);
$ids = array_unique($m[1]);
echo "Toplam personel id: " . count($ids) . "\n";
echo "Ilk 10 id: " . implode(',', array_slice($ids, 0, 10)) . "\n\n";

$dolu = 0; $bos = 0; $sample = [];
foreach (array_slice($ids, 0, 10) as $id) {
    $detail = $c->getHtml("/calisan_ekle.aspx?id={$id}&t=d");
    $ad = $soyad = $tel = $unvan = '';
    if (preg_match('#name="TB_Ad"[^>]+value="([^"]*)"#i', $detail, $m1)) $ad = $m1[1];
    if (preg_match('#name="TB_Soyad"[^>]+value="([^"]*)"#i', $detail, $m1)) $soyad = $m1[1];
    if (preg_match('#name="TB_Telefon"[^>]+value="([^"]*)"#i', $detail, $m1)) $tel = $m1[1];
    if (preg_match('#name="TB_Unvan"[^>]+value="([^"]*)"#i', $detail, $m1)) $unvan = $m1[1];
    if ($tel) $dolu++; else $bos++;
    printf("  id=%-7s ad=[%-15s] soyad=[%-15s] tel=[%-12s] unvan=[%s]\n",
        $id, $ad, $soyad, $tel, $unvan);
    usleep(300000);
}
echo "\nSonuc: telefon dolu={$dolu}, bos={$bos}\n";
