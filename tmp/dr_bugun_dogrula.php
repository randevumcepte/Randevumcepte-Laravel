<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();
$today = date('d.m.Y');
$todayDb = date('Y-m-d');

$h = $c->postBack('/gunlukrandevulistesi.aspx', 'BTN_Ara', '', [
    'TB_Tarih1' => $today, 'TB_Tarih2' => $today,
]);
preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tm);
$bestRows = [];
foreach ($tm[1] as $t) {
    if (preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $t, $r) && count($r[1]) > count($bestRows)) $bestRows = $r[1];
}
echo "Drklinik bugun ($today) - HTML tr sayisi: " . count($bestRows) . "\n";

$drklinikSet = [];
foreach ($bestRows as $tr) {
    if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
    preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
    if (empty($tds[1])) continue;
    $cells = [];
    foreach ($tds[1] as $td) {
        $clean = trim(preg_replace('~\s+~', ' ', strip_tags($td)));
        $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
    if (count($cells) < 16) continue;
    $key = ($cells[3] ?? '') . '|' . ($cells[5] ?? '') . '|' . ($cells[8] ?? '');
    $drklinikSet[$key] = "saat={$cells[3]} ad={$cells[5]} tel={$cells[8]} birim={$cells[10]} personel={$cells[11]}";
}
echo "Drklinik bugun islenebilir randevu: " . count($drklinikSet) . "\n";

$db = App\Randevular::where('salon_id', 362)->where('tarih', $todayDb)
    ->with('users')->get();
echo "DB bugun ($todayDb) salon 362 randevu: " . $db->count() . "\n\n";

echo "===== Drklinik'te olup DB'de olmayanlar =====\n";
$dbSet = [];
foreach ($db as $r) {
    $tel = $r->users ? preg_replace('/[^0-9]/', '', $r->users->cep_telefon ?: '') : '';
    $tel = preg_replace('/^90/', '', $tel); $tel = preg_replace('/^0/', '', $tel);
    $key = substr($r->saat, 0, 5) . '|' . ($r->users->name ?? '') . '|' . $tel;
    $dbSet[$key] = true;
}
foreach ($drklinikSet as $key => $info) {
    if (!isset($dbSet[$key])) echo "EKSIK: $info\n";
}
