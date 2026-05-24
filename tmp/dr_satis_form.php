<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

echo "=== satis_raporlari.aspx tum form alanlari ===\n";
$h = $c->getHtml('/satis_raporlari.aspx');
echo "Boyut: " . strlen($h) . "\n\n";

// Tum input alanlari
preg_match_all('~<input[^>]+name="([^"]+)"[^>]*?(?:value="([^"]*)")?[^>]*?/?>~i', $h, $im, PREG_SET_ORDER);
echo "INPUT alanlari:\n";
foreach ($im as $i) {
    if (preg_match('/^__/', $i[1])) continue;
    printf("  %-30s = [%s]\n", $i[1], $i[2] ?? '');
}

// Tum select alanlari (CheckBoxList olabilir, name pattern'lari)
echo "\nSELECT alanlari:\n";
preg_match_all('~<select[^>]+name="([^"]+)"[^>]*>(.*?)</select>~is', $h, $sm, PREG_SET_ORDER);
foreach ($sm as $s) {
    preg_match_all('~<option[^>]*value="([^"]*)"[^>]*>(.*?)</option>~is', $s[2], $om);
    $sel = '';
    if (preg_match('~<option[^>]+selected[^>]*value="([^"]*)"[^>]*>(.*?)</option>~is', $s[2], $selM)) {
        $sel = "selected={$selM[1]}";
    }
    echo "  {$s[1]} (" . count($om[0]) . " opt) $sel\n";
}

// CheckBox/RadioButtonList izleri (input type=checkbox/radio)
echo "\nCheckBox/Radio name pattern'lari:\n";
preg_match_all('~<input[^>]+type="(checkbox|radio)"[^>]+name="([^"]+)"~i', $h, $cm, PREG_SET_ORDER);
$names = [];
foreach ($cm as $c) {
    $name = preg_replace('/\$\d+$/', '', $c[2]); // $0, $1 -> base name
    $names[$name] = ($names[$name] ?? 0) + 1;
}
foreach ($names as $n => $cnt) echo "  $n ($cnt item, type=" . $cm[0][1] . ")\n";

// Form actions
if (preg_match('~<form[^>]+action="([^"]+)"~', $h, $fm)) echo "\nFORM action: " . $fm[1] . "\n";

// Sayfanin sidebar/menu'sunde dosya/satis ile ilgili linkler
echo "\nSayfadaki .aspx linkleri (sidebar'dan farkli):\n";
preg_match_all('~href="([^"]*\.aspx[^"]*)"~', $h, $hm);
foreach (array_unique($hm[1]) as $l) {
    if (preg_match('~satis|dosya|adisyon|islem|musteri_~i', $l)) echo "  $l\n";
}
