<?php
require_once "/var/www/www-root/data/www/randevumceptetest/vendor/autoload.php";
$app = require_once "/var/www/www-root/data/www/randevumceptetest/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$duplicates = DB::select("
SELECT t1.id, t1.notlar AS pay_notlar, t2.id AS orjId, t2.notlar AS orj_notlar
FROM tahsilatlar t1
JOIN tahsilatlar t2 ON t2.salon_id=t1.salon_id AND t2.user_id=t1.user_id 
    AND t2.odeme_tarihi=t1.odeme_tarihi AND t2.tutar=t1.tutar AND t2.id<>t1.id
WHERE t1.salon_id=368
  AND t1.notlar LIKE '%[salonappy-payment:%'
  AND t1.adisyon_id IS NULL
  AND (t2.notlar LIKE '%[salonappy:%' OR t2.notlar LIKE '%[salonappy-prodsale:%' OR t2.notlar LIKE '%[salonappy-pkgsale:%')
  AND t2.notlar NOT LIKE '%[salonappy-payment:%'
");
echo "Duplicate sayisi: " . count($duplicates) . "\n";

$silinen = 0;
foreach ($duplicates as $d) {
    if (preg_match('~\[salonappy-payment:\d+\]~', $d->pay_notlar, $m)) {
        $payMarker = $m[0];
        $yeniNot = trim(($d->orj_notlar ?? '') . ' ' . $payMarker);
        DB::table("tahsilatlar")->where("id", $d->orjId)->update(["notlar" => $yeniNot]);
        DB::table("tahsilatlar")->where("id", $d->id)->delete();
        $silinen++;
    }
}
echo "Silinen duplicate: $silinen\n";

$sum = DB::table("tahsilatlar")->where("salon_id",368)
    ->whereBetween("odeme_tarihi", ["2026-05-01","2026-05-31"])->sum("tutar");
echo "Mayis 2026 tahsilat: " . number_format($sum,2) . " TRY (hedef 243.060)\n";
