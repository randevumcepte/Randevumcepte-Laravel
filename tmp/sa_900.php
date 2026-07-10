<?php
require "/var/www/www-root/data/www/randevumceptetest/vendor/autoload.php";
$app = require "/var/www/www-root/data/www/randevumceptetest/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$dump = json_decode(file_get_contents("/tmp/salonappy_v7_1780440583671.json"), true);
$dumpMayisIds = [];
foreach (($dump["payments"] ?? []) as $p) {
    $d = $p["date"] ?? "";
    if ($d >= "2026-05-01" && $d <= "2026-05-31") $dumpMayisIds[(string)$p["id"]] = (float)$p["amount"];
}
echo "Dump Mayis payment: ".count($dumpMayisIds).", toplam ".number_format(array_sum($dumpMayisIds),2)."\n";
$rows = DB::table("tahsilatlar")->where("salon_id",368)
    ->whereBetween("odeme_tarihi",["2026-05-01","2026-05-31"])
    ->where("notlar","LIKE","%[salonappy-payment:%")->get(["id","notlar","tutar"]);
$bizimMayisIds = [];
foreach ($rows as $t) {
    preg_match_all('~\[salonappy-payment:(\d+)\]~', $t->notlar, $m);
    foreach (($m[1]??[]) as $pid) $bizimMayisIds[$pid] = (float)$t->tutar;
}
$fazla = array_diff_key($bizimMayisIds, $dumpMayisIds);
echo "Bizde Mayis ama dump'ta degil (tarih kaymis): ".count($fazla)." kayit\n";
foreach ($fazla as $pid => $tutar) echo "  payment_id=$pid tutar=$tutar TRY\n";
