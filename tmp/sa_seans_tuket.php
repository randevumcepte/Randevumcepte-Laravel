<?php
require "/var/www/www-root/data/www/randevumceptetest/vendor/autoload.php";
$app = require "/var/www/www-root/data/www/randevumceptetest/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$SALON = 368;
$tuketildi = 0; $atlandi = 0;

// Tum salon visit randevulari (durum=onayli + geldi=1, paket markerli adisyon disindaki)
$randevular = DB::table("randevular as r")
    ->where("r.salon_id", $SALON)
    ->where("r.durum", 1)
    ->where("r.randevuya_geldi", 1)
    ->select("r.id","r.user_id","r.tarih","r.saat")->get();
echo "Onayli+geldi randevu: ".count($randevular)."\n";

foreach ($randevular as $rnd) {
    // Bu randevunun hizmetlerini al
    $rhs = DB::table("randevu_hizmetler")->where("randevu_id", $rnd->id)->pluck("hizmet_id");
    foreach ($rhs as $hid) {
        if (!$hid) continue;
        // Bu user'in bu hizmet icin acik paket placeholder'i (geldi=0, randevu_id NULL) var mi
        $aps = DB::select("
            SELECT aps.id, aps.adisyon_hizmet_id
            FROM adisyon_paket_seanslar aps
            JOIN adisyon_hizmetler ah ON ah.id=aps.adisyon_hizmet_id
            JOIN adisyonlar a ON a.id=ah.adisyon_id
            WHERE a.salon_id=? AND a.user_id=? AND ah.hizmet_id=?
              AND aps.geldi=0 AND aps.randevu_id IS NULL
            ORDER BY aps.id ASC LIMIT 1
        ", [$SALON, $rnd->user_id, $hid]);
        if (empty($aps)) { $atlandi++; continue; }
        DB::table("adisyon_paket_seanslar")->where("id", $aps[0]->id)->update([
            "geldi" => 1,
            "randevu_id" => $rnd->id,
            "seans_tarih" => $rnd->tarih,
            "seans_saat" => $rnd->saat,
            "updated_at" => date("Y-m-d H:i:s"),
        ]);
        $tuketildi++;
    }
}
echo "Tuketildi: $tuketildi APS\n";
echo "Atlandi (acik paket yok): $atlandi\n";

// Ozet
$dolu = DB::table("adisyon_paket_seanslar as aps")
    ->join("adisyon_hizmetler as ah","ah.id","=","aps.adisyon_hizmet_id")
    ->join("adisyonlar as a","a.id","=","ah.adisyon_id")
    ->where("a.salon_id",$SALON)
    ->selectRaw("SUM(CASE WHEN aps.geldi=1 THEN 1 ELSE 0 END) AS gelen, SUM(CASE WHEN aps.geldi=0 THEN 1 ELSE 0 END) AS bos")
    ->first();
echo "\nSalon 368 APS: gelen={$dolu->gelen}, bos={$dolu->bos}\n";
