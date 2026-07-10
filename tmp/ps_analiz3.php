<?php
require_once "/var/www/www-root/data/www/randevumceptetest/vendor/autoload.php";
$app = require_once "/var/www/www-root/data/www/randevumceptetest/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$dump = json_decode(file_get_contents("/tmp/salonappy_v7_1780426072711.json"), true);
$ps = $dump["productSales"] ?? [];

$bizSayim = DB::table("adisyon_urunler as au")
    ->join("adisyonlar as a", "a.id", "=", "au.adisyon_id")
    ->where("a.salon_id", 368)
    ->where(function ($q) {
        $q->where("a.notlar", "LIKE", "%[salonappy:%")
          ->orWhere("a.notlar", "LIKE", "%[salonappy-prodsale:%");
    })
    ->count();
echo "BIZDEKI toplam salonappy markerli adisyon_urunler: $bizSayim\n";
echo "DUMP productSales: " . count($ps) . "\n";

$eksikS = []; $eksikV = [];
foreach ($ps as $sale) {
    $id = $sale["id"];
    $urunAd = trim($sale["product_text"]);
    $fiyat = $sale["product_price"];
    if (empty($sale["is_session"])) {
        $exists = DB::table("adisyonlar")->where("salon_id",368)->where("notlar","LIKE","%[salonappy-prodsale:$id]%")->exists();
        if (!$exists) $eksikS[] = $sale;
    } else {
        $sess = $sale["session_id"];
        $adId = DB::table("adisyonlar")->where("salon_id",368)->where("notlar","LIKE","%[salonappy:$sess]%")->value("id");
        if (!$adId) { $eksikV[] = ["sale"=>$sale, "sebep"=>"adisyon yok sess=$sess"]; continue; }
        $found = DB::table("adisyon_urunler as au")
            ->join("urunler as u","u.id","=","au.urun_id")
            ->where("au.adisyon_id",$adId)
            ->where("u.urun_adi", $urunAd)
            ->where("au.fiyat", $fiyat)
            ->exists();
        if (!$found) $eksikV[] = ["sale"=>$sale, "sebep"=>"ad+fiyat eslesmedi adisyon=$adId"];
    }
}
echo "\nEKSIK standalone: " . count($eksikS) . "\n";
foreach ($eksikS as $s) printf("  id=%s tarih=%s urun=%s tutar=%s\n", $s["id"], $s["date"], substr($s["product_text"],0,40), $s["total_amount"]);
echo "\nEKSIK visit-bagli: " . count($eksikV) . "\n";
foreach ($eksikV as $e) printf("  id=%s sess=%s sebep=%s urun=%s\n", $e["sale"]["id"], $e["sale"]["session_id"], $e["sebep"], substr($e["sale"]["product_text"],0,40));
