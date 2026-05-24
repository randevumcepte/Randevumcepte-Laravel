<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

// 1 ay için kaç randevu, td[13] dolu/boş dağılımı
$h = $c->postBack('/gunlukrandevulistesi.aspx', 'BTN_Ara', '', [
    'TB_Tarih1' => '01.06.2024', 'TB_Tarih2' => '07.06.2024',
]);
preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tm);
$bestRows = [];
foreach ($tm[1] as $t) if (preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $t, $r) && count($r[1]) > count($bestRows)) $bestRows = $r[1];

$dolu = 0; $bos = 0; $sample = [];
foreach ($bestRows as $tr) {
    if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
    preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
    if (empty($tds[1]) || count($tds[1]) < 16) continue;
    $cells = array_map(function($td){
        $c = trim(preg_replace('~\s+~',' ',strip_tags($td)));
        return trim(html_entity_decode($c, ENT_QUOTES|ENT_HTML5, 'UTF-8'));
    }, $tds[1]);
    $h13 = trim($cells[13] ?? '');
    if ($h13 !== '') { $dolu++; if (count($sample) < 3) $sample[] = ['ad'=>$cells[5], 'h13'=>$h13]; }
    else { $bos++; }
}
echo "1 hafta randevular: dolu=" . $dolu . " bos=" . $bos . " toplam=" . ($dolu+$bos) . "\n";
echo "Td[13] dolu ornekler:\n";
foreach ($sample as $s) echo "  " . $s['ad'] . " | " . $s['h13'] . "\n";

// DB'de salon 362 mevcut randevu/RandevuHizmet sayısı
echo "\nDB salon 362 mevcut: randevu=" . App\Randevular::where('salon_id',362)->count() .
     " randevu_hizmet=" . App\RandevuHizmetler::whereIn('randevu_id', App\Randevular::where('salon_id',362)->pluck('id'))->count() . "\n";
