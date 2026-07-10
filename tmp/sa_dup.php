<?php
require "/var/www/www-root/data/www/randevumceptetest/vendor/autoload.php";
$app = require "/var/www/www-root/data/www/randevumceptetest/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rows = DB::table("tahsilatlar")->where("salon_id",368)
    ->whereBetween("odeme_tarihi",["2026-05-01","2026-05-31"])
    ->where("notlar","LIKE","%[salonappy-payment:%")
    ->get(["id","notlar","tutar","odeme_tarihi","user_id"]);
$idMap = []; // payment_id => [tahsilat rows]
foreach ($rows as $t) {
    preg_match_all('~\[salonappy-payment:(\d+)\]~', $t->notlar, $m);
    foreach (($m[1]??[]) as $pid) $idMap[$pid][] = $t;
}
$dupCount=0; $silTutar=0;
foreach ($idMap as $pid => $tList) {
    if (count($tList) < 2) continue;
    echo "payment_id=$pid -> ".count($tList)." tahsilat:\n";
    foreach ($tList as $t) echo "  tahsilat_id={$t->id} tutar={$t->tutar} tarih={$t->odeme_tarihi}\n";
    // En kucuk id korunur, digerleri silinir
    usort($tList, fn($a,$b)=>$a->id-$b->id);
    array_shift($tList);
    foreach ($tList as $t) { DB::table("tahsilatlar")->where("id",$t->id)->delete(); $dupCount++; $silTutar+=(float)$t->tutar; }
}
echo "\nSilinen duplicate: $dupCount kayit, ".number_format($silTutar,2)." TRY\n";
$sum = DB::table("tahsilatlar")->where("salon_id",368)->whereBetween("odeme_tarihi",["2026-05-01","2026-05-31"])->sum("tutar");
echo "Yeni Mayis: ".number_format($sum,2)." TRY (hedef 243.060)\n";
