<?php
require "/var/www/www-root/data/www/randevumceptetest/vendor/autoload.php";
$app = require "/var/www/www-root/data/www/randevumceptetest/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rows = DB::table("tahsilatlar")->where("salon_id",368)
    ->whereBetween("odeme_tarihi",["2026-05-01","2026-05-31"])
    ->where("notlar","NOT LIKE","%[salonappy-payment:%")
    ->where(function($q){
        $q->where("notlar","LIKE","%[salonappy:%")
          ->orWhere("notlar","LIKE","%[salonappy-prodsale:%")
          ->orWhere("notlar","LIKE","%[salonappy-pkgsale:%");
    })->get(["id","notlar","tutar","odeme_tarihi","user_id"]);
echo "Phantom (visit/urun/paket marker'li, payment marker yok): ".count($rows)."\n";
$grup=['visit'=>['n'=>0,'t'=>0],'urun'=>['n'=>0,'t'=>0],'paket'=>['n'=>0,'t'=>0]];
$silTutar=0; $silAdet=0;
foreach($rows as $t){
    if(strpos($t->notlar,"[salonappy-prodsale:")!==false){ $k='urun'; }
    elseif(strpos($t->notlar,"[salonappy-pkgsale:")!==false){ $k='paket'; }
    else { $k='visit'; }
    $grup[$k]['n']++; $grup[$k]['t']+=(float)$t->tutar;
    DB::table("tahsilatlar")->where("id",$t->id)->delete();
    $silAdet++; $silTutar+=(float)$t->tutar;
}
foreach($grup as $k=>$v) printf("  %s: %d kayit, %.2f TRY\n",$k,$v['n'],$v['t']);
echo "Silinen toplam: $silAdet kayit, ".number_format($silTutar,2)." TRY\n";
$sum = DB::table("tahsilatlar")->where("salon_id",368)->whereBetween("odeme_tarihi",["2026-05-01","2026-05-31"])->sum("tutar");
echo "Yeni Mayis: ".number_format($sum,2)." TRY (hedef 243.060)\n";
