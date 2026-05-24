<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

// Salon 362 ozet
echo "===== Salon 362 mevcut sayılar =====\n";
echo "Adisyon: " . App\Adisyonlar::where('salon_id', 362)->count() . "\n";
echo "AdisyonHizmet: " . App\AdisyonHizmetler::whereIn('adisyon_id', App\Adisyonlar::where('salon_id', 362)->pluck('id'))->count() . "\n";
echo "Randevu: " . App\Randevular::where('salon_id', 362)->count() . "\n";
echo "User+portfoy: " . App\MusteriPortfoy::where('salon_id', 362)->count() . "\n";

// Bir gün için drklinik vs DB karşılaştırma
echo "\n===== 06.05.2026 randevular drklinik vs DB =====\n";
$h = $c->postBack('/gunlukrandevulistesi.aspx', 'BTN_Ara', '', [
    'TB_Tarih1' => '06.05.2026', 'TB_Tarih2' => '06.05.2026',
]);
preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tm);
$bestRows = [];
foreach ($tm[1] as $t) if (preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $t, $r) && count($r[1]) > count($bestRows)) $bestRows = $r[1];

function nrm($t){ $t=preg_replace('/[^0-9]/','',(string)$t); $t=preg_replace('/^90/','',$t); return preg_replace('/^0/','',$t);}

$ekran = [];
foreach ($bestRows as $tr) {
    if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
    preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
    if (empty($tds[1])) continue;
    $cells = array_map(function($td){
        $c = trim(preg_replace('~\s+~',' ',strip_tags($td)));
        return trim(html_entity_decode($c, ENT_QUOTES|ENT_HTML5, 'UTF-8'));
    }, $tds[1]);
    if (count($cells) < 16) continue;
    $key = substr($cells[3],0,5);
    $ekran[] = ['saat'=>$cells[3], 'ad'=>$cells[5], 'tel'=>nrm($cells[8])];
}
echo "Drklinik bugun: " . count($ekran) . " randevu\n";

$dbRandevular = App\Randevular::where('salon_id', 362)->where('tarih', '2026-05-06')->get();
echo "DB bugun: " . $dbRandevular->count() . " randevu\n\n";

foreach ($ekran as $e) {
    // DB'de bu telefon ile user var mi
    $u = App\User::where('cep_telefon', $e['tel'])->first();
    $dbAd = $u ? $u->name : '(yok)';
    $marker = $u && stripos($u->name, $e['ad']) === false && stripos($e['ad'], $u->name) === false ? '⚠' : ' ';
    printf("%s saat=%s drk=[%s/tel=%s] -> dbUser=%s (id=%s)\n",
        $marker, $e['saat'], $e['ad'], $e['tel'] ?: '(BOS)',
        $dbAd, $u ? $u->id : '-');
}
