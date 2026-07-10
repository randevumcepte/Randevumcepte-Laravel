<?php
require __DIR__."/../var/www/www-root/data/www/randevumceptetest/vendor/autoload.php";
$app = require "/var/www/www-root/data/www/randevumceptetest/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$dump = json_decode(file_get_contents("/tmp/salonappy_v7_1780440583671.json"), true);
$set = [];
foreach (($dump["payments"] ?? []) as $p) $set[(string)$p["id"]] = 1;
echo "Dump payments: ".count($set)."\n";
$rows = DB::table("tahsilatlar")->where("salon_id",368)->whereBetween("odeme_tarihi",["2026-05-01","2026-05-31"])->where("notlar","LIKE","%[salonappy-payment:%")->get(["id","notlar","tutar"]);
$sil=0; $silT=0; $kor=0;
foreach ($rows as $t) {
  preg_match_all('~\[salonappy-payment:(\d+)\]~', $t->notlar, $m);
  $var=false; foreach (($m[1]??[]) as $pid) if (isset($set[$pid])) { $var=true; break; }
  if ($var) { $kor++; continue; }
  DB::table("tahsilatlar")->where("id",$t->id)->delete();
  $sil++; $silT += (float)$t->tutar;
}
echo "Korunan: $kor | Silinen: $sil ($silT TRY)\n";
$sum = DB::table("tahsilatlar")->where("salon_id",368)->whereBetween("odeme_tarihi",["2026-05-01","2026-05-31"])->sum("tutar");
echo "Yeni Mayis toplam: ".number_format($sum,2)." TRY (hedef 243.060)\n";
