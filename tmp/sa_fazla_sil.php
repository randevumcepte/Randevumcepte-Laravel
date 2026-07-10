<?php
require_once "/var/www/www-root/data/www/randevumceptetest/vendor/autoload.php";
$app = require_once "/var/www/www-root/data/www/randevumceptetest/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$DUMP_PATH = "/tmp/salonappy_v7_1780440583671.json";
$SALON = 368;
$AY_BAS = "2026-05-01"; $AY_SON = "2026-05-31";

$dump = json_decode(file_get_contents($DUMP_PATH), true);
$paySet = [];
foreach (($dump["payments"] ?? []) as $p) $paySet[(string)$p["id"]] = true;
echo "Dump payment toplam: " . count($paySet) . "\n";

$bizimler = DB::table("tahsilatlar")->where("salon_id", $SALON)
    ->whereBetween("odeme_tarihi", [$AY_BAS, $AY_SON])
    ->where("notlar", "LIKE", "%[salonappy-payment:%")
    ->get(["id", "notlar", "tutar"]);

$silinecek = []; $korunan = 0;
foreach ($bizimler as $t) {
    preg_match_all('~\[salonappy-payment:(\d+)\]~', $t->notlar, $m);
    $varMi = false;
    foreach (($m[1] ?? []) as $pid) if (isset($paySet[$pid])) { $varMi = true; break; }
    if ($varMi) { $korunan++; continue; }
    $silinecek[] = $t;
}
echo "Korunan: $korunan\n";
echo "Silinecek: " . count($silinecek) . "\n";
$silTutar = 0;
foreach ($silinecek as $t) {
    DB::table("tahsilatlar")->where("id", $t->id)->delete();
    $silTutar += (float)$t->tutar;
}
echo "Silinen tutar: " . number_format($silTutar,2) . " TRY\n";

$sum = DB::table("tahsilatlar")->where("salon_id",$SALON)
    ->whereBetween("odeme_tarihi", [$AY_BAS, $AY_SON])->sum("tutar");
echo "Yeni Mayis: " . number_format($sum,2) . " TRY (hedef 243.060)\n";
