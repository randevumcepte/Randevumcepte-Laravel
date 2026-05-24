<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$adIds = App\Adisyonlar::where('salon_id', 362)->pluck('id');
$rIds  = App\Randevular::where('salon_id', 362)->pluck('id');
$ahCount = App\AdisyonHizmetler::whereIn('adisyon_id', $adIds)->count();
$rhCount = App\RandevuHizmetler::whereIn('randevu_id', $rIds)->count();
echo "Silinecek:\n";
echo "  adisyon: " . $adIds->count() . "\n";
echo "  adisyon_hizmet: $ahCount\n";
echo "  randevu: " . $rIds->count() . "\n";
echo "  randevu_hizmet: $rhCount\n";

App\AdisyonHizmetler::whereIn('adisyon_id', $adIds)->delete();
App\Adisyonlar::where('salon_id', 362)->delete();
App\RandevuHizmetler::whereIn('randevu_id', $rIds)->delete();
App\Randevular::where('salon_id', 362)->delete();
echo "Silindi.\n";
