<?php
require_once "/var/www/www-root/data/www/randevumceptetest/vendor/autoload.php";
$app = require_once "/var/www/www-root/data/www/randevumceptetest/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$dump = json_decode(file_get_contents("/tmp/salonappy_v7_1780435641843.json"), true);
$payMap = []; // payment_id => source_text
foreach (($dump["payments"] ?? []) as $p) $payMap[(string)$p["id"]] = $p["source_text"] ?? "";

$pure = DB::table("tahsilatlar")->where("salon_id",368)
    ->whereBetween("odeme_tarihi",["2026-05-01","2026-05-31"])
    ->where("notlar","LIKE","%[salonappy-payment:%")
    ->where("notlar","NOT LIKE","%[salonappy:%")
    ->where("notlar","NOT LIKE","%[salonappy-prodsale:%")
    ->where("notlar","NOT LIKE","%[salonappy-pkgsale:%")
    ->get(["id","notlar","tutar"]);

$silinen = 0; $silinenTutar = 0; $grup = [];
foreach ($pure as $t) {
    if (!preg_match('~\[salonappy-payment:(\d+)\]~', $t->notlar, $m)) continue;
    $payId = $m[1];
    $src = $payMap[$payId] ?? "BILINMIYOR";
    $grup[$src] = ($grup[$src] ?? 0) + 1;
    // "Adisyon" source'lu ama pure payment olarak duran → fazlalik, sil
    if ($src === "Adisyon") {
        DB::table("tahsilatlar")->where("id", $t->id)->delete();
        $silinen++; $silinenTutar += (float)$t->tutar;
    }
}
echo "Pure payment source_text dagilimi:\n";
foreach ($grup as $k => $v) echo "  $k: $v kayit\n";
echo "\nSilinen (Adisyon source'lu pure): $silinen kayit, " . number_format($silinenTutar,2) . " TRY\n";

$sum = DB::table("tahsilatlar")->where("salon_id",368)
    ->whereBetween("odeme_tarihi",["2026-05-01","2026-05-31"])->sum("tutar");
echo "Yeni Mayis 2026 toplam: " . number_format($sum,2) . " TRY (hedef 243.060)\n";
