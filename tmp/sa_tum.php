<?php
require "/var/www/www-root/data/www/randevumceptetest/vendor/autoload.php";
$app = require "/var/www/www-root/data/www/randevumceptetest/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 1) Phantom sil (visit/urun/paket marker'li, payment marker yok)
$phantoms = DB::table("tahsilatlar")->where("salon_id",368)
    ->where("notlar","NOT LIKE","%[salonappy-payment:%")
    ->where(function($q){
        $q->where("notlar","LIKE","%[salonappy:%")
          ->orWhere("notlar","LIKE","%[salonappy-prodsale:%")
          ->orWhere("notlar","LIKE","%[salonappy-pkgsale:%");
    })->get(["id","tutar"]);
$pn=0; $pt=0;
foreach($phantoms as $t){ DB::table("tahsilatlar")->where("id",$t->id)->delete(); $pn++; $pt+=(float)$t->tutar; }
echo "1) Phantom silindi: $pn kayit, ".number_format($pt,2)." TRY\n";

// 2) Payment_id duplicate sil (ayni payment_id birden cok tahsilatta)
$rows = DB::table("tahsilatlar")->where("salon_id",368)
    ->where("notlar","LIKE","%[salonappy-payment:%")
    ->get(["id","notlar","tutar"]);
$idMap = [];
foreach($rows as $t){
    preg_match_all('~\[salonappy-payment:(\d+)\]~', $t->notlar, $m);
    foreach(($m[1]??[]) as $pid) $idMap[$pid][] = $t;
}
$dn=0; $dt=0;
foreach($idMap as $pid=>$tList){
    if(count($tList)<2) continue;
    usort($tList, fn($a,$b)=>$a->id-$b->id);
    array_shift($tList);
    foreach($tList as $t){ DB::table("tahsilatlar")->where("id",$t->id)->delete(); $dn++; $dt+=(float)$t->tutar; }
}
echo "2) Duplicate payment_id silindi: $dn kayit, ".number_format($dt,2)." TRY\n";

$sum = DB::table("tahsilatlar")->where("salon_id",368)->sum("tutar");
echo "\nSalon 368 TUM ZAMAN tahsilat toplami: ".number_format($sum,2)." TRY\n";
