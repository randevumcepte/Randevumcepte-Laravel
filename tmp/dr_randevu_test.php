<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

// 1) BTN_Ara postback geniş tarih aralığı
$h = $c->postBack('/gunlukrandevulistesi.aspx', 'BTN_Ara', '', [
    'TB_Tarih1' => '01.01.2020',
    'TB_Tarih2' => '31.12.2030',
]);
if (!$h) { echo "postback null\n"; exit; }
echo "Postback boyut: " . strlen($h) . " byte\n";

// 2) En geniş tablo, satır + sütun sayısı
preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tm);
$bestRows = [];
foreach ($tm[1] as $t) {
    if (preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $t, $r)) {
        if (count($r[1]) > count($bestRows)) $bestRows = $r[1];
    }
}
echo "En genis tablo tr: " . count($bestRows) . "\n";

// 3) Pagination
if (preg_match_all('~__doPostBack\([\'"&#39;]+([^\'"&]+)[\'"&#39;]+,\s*[\'"&#39;]+(Page\$[^\'"&]+)[\'"&#39;]+~', $h, $pm)) {
    $u = [];
    foreach ($pm[1] as $i => $t) $u[$t . '|' . $pm[2][$i]] = "{$t} arg={$pm[2][$i]}";
    echo "Pagination postback'leri:\n  " . implode("\n  ", array_unique($u)) . "\n";
} else {
    echo "Pagination yok (tek sayfa veya farkli yapi)\n";
}

// 4) Toplam kayıt text'i
$plain = preg_replace('~\s+~', ' ', strip_tags($h));
foreach (['Toplam', 'Sayfa', 'Listelenen', 'Bulunan'] as $kw) {
    if (preg_match("~({$kw}[^<,;.]{0,80})~iu", $plain, $m)) echo "  " . trim($m[1]) . "\n";
}

// 5) İlk 2 veri satırı raw td
echo "\n=== Ilk 2 satir td icerikleri ===\n";
$shown = 0;
foreach ($bestRows as $tr) {
    if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
    preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
    if (empty($tds[1])) continue;
    if (++$shown > 2) break;
    echo "--- Satir $shown - " . count($tds[1]) . " td ---\n";
    foreach ($tds[1] as $i => $td) {
        $clean = trim(preg_replace('~\s+~', ' ', strip_tags($td)));
        $clean = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (strlen($clean) > 100) $clean = substr($clean, 0, 100) . '...';
        printf("  td[%2d] %s\n", $i, $clean);
    }
}
