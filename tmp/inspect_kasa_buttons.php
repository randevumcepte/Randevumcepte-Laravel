<?php
require __DIR__ . '/../var/www/www-root/data/www/randevumceptetest/vendor/autoload.php';
$app = require_once __DIR__ . '/../var/www/www-root/data/www/randevumceptetest/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$c = new \App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$l = $c->login();
if (!$l['ok']) { echo "LOGIN FAIL\n"; exit; }
$h = $c->getHtml('/kasa_islemleri.aspx');
file_put_contents('/tmp/kasa_islemleri.html', $h);
echo "Sayfa kaydedildi: /tmp/kasa_islemleri.html (" . strlen($h) . " byte)\n\n";

echo "=== BTN_ butonlari ===\n";
preg_match_all('~name="(BTN_[A-Za-z0-9_]+)"~', $h, $bm);
foreach (array_unique($bm[1]) as $n) echo "  $n\n";

echo "\n=== 'Tum' / 'Hepsi' iceren input/button tag'leri ===\n";
preg_match_all('~<(?:input|button|asp:Button)[^>]+>~i', $h, $tm);
foreach ($tm[0] as $tag) {
    if (preg_match('~(value|onclick)[^"]*"[^"]*(T[uü]m|Hepsi|Gelir|Tahsilat)[^"]*"~iu', $tag)) {
        echo "  " . trim(preg_replace('~\s+~', ' ', $tag)) . "\n";
    }
}

echo "\n=== Tarih input'lari ===\n";
preg_match_all('~name="(TB_[A-Za-z0-9_]+)"[^>]*~', $h, $tbm);
foreach (array_unique($tbm[1]) as $n) {
    if (stripos($n, 'tarih') !== false || stripos($n, 'date') !== false) echo "  $n\n";
}
