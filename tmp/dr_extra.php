<?php
require __DIR__ . '/../var/www/www-root/data/www/randevumceptetest/vendor/autoload.php';
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$login = $c->login();
echo "Login: " . ($login['ok'] ? 'OK' : 'FAIL') . " - " . $login['detail'] . "\n\n";

$pages = [
    'kullanici_listesi.aspx', 'kasa_islemleri.aspx', 'genel_kasa_raporu_satis.aspx',
    'gunlukrandevulistesi.aspx', 'musteriArama.aspx', 'tanimlama_islemleri.aspx',
    'satis_raporlari.aspx', 'satis_onay_listesi.aspx', 'parapuan_listele.aspx',
    'kredi_karti_odeme.aspx', 'fatura_raporlari.aspx', 'musteri_raporlari.aspx',
];
foreach ($pages as $p) {
    $h = $c->getHtml('/' . $p, 'extra_' . preg_replace('/[^a-z0-9]+/i','_',$p));
    $len = strlen($h);
    $rows = preg_match_all('#<tr[^>]*>#i', $h);
    $hasLogin = stripos($h, 'TB_KullaniciAd') !== false;
    $hasGrid = preg_match('#DGRV[a-zA-Z_]*#i', $h, $m);
    $gridName = $hasGrid ? $m[0] : '-';
    $tag = $hasLogin ? 'LOGIN_GERI' : ($rows > 1 ? "rows={$rows}" : 'BOSGIBI');
    printf("  %-35s len=%-7d %s grid=%s\n", $p, $len, $tag, $gridName);
}

echo "\nDump dizini: " . $c->dumpDir() . "\n";
