<?php
require_once "/var/www/www-root/data/www/randevumceptetest/vendor/autoload.php";
$app = require_once "/var/www/www-root/data/www/randevumceptetest/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$mayBaslangic = "2026-05-01"; $maySon = "2026-05-31";

$kat = [
    'visit'      => "%[salonappy:%",
    'urun'       => "%[salonappy-prodsale:%",
    'paket'      => "%[salonappy-pkgsale:%",
    'payment'    => "%[salonappy-payment:%",
];
echo "=== Mayis 2026 marker bazli toplam ===\n";
foreach ($kat as $ad => $ptn) {
    $q = DB::table("tahsilatlar")->where("salon_id", 368)
        ->whereBetween("odeme_tarihi", [$mayBaslangic, $maySon])
        ->where("notlar", "LIKE", $ptn);
    printf("  %-10s : %5d kayit, %12.2f TRY\n", $ad, $q->count(), $q->sum("tutar"));
}

echo "\n=== Mayis 2026 visit marker'siz (bazi visit pipeline marker yazmamis olabilir) ===\n";
$ms = DB::table("tahsilatlar")->where("salon_id", 368)
    ->whereBetween("odeme_tarihi", [$mayBaslangic, $maySon])
    ->where(function($q){ $q->whereNull("notlar")->orWhere("notlar","NOT LIKE","%[salonappy%"); });
printf("  marker yok : %5d kayit, %12.2f TRY\n", $ms->count(), $ms->sum("tutar"));

$tum = DB::table("tahsilatlar")->where("salon_id", 368)
    ->whereBetween("odeme_tarihi", [$mayBaslangic, $maySon])->sum("tutar");
echo "\nTOPLAM Mayis: " . number_format($tum, 2) . " TRY (salonappy hedef: 243.060)\n";

echo "\n=== Sadece payment marker'li, baska marker yok (purely payment-only) ===\n";
$pureRows = DB::table("tahsilatlar")->where("salon_id", 368)
    ->whereBetween("odeme_tarihi", [$mayBaslangic, $maySon])
    ->where("notlar", "LIKE", "%[salonappy-payment:%")
    ->where("notlar", "NOT LIKE", "%[salonappy:%")
    ->where("notlar", "NOT LIKE", "%[salonappy-prodsale:%")
    ->where("notlar", "NOT LIKE", "%[salonappy-pkgsale:%");
printf("  pure payment: %5d kayit, %12.2f TRY (yeni paket/borc tahsilatlari beklenir)\n", $pureRows->count(), $pureRows->sum("tutar"));
