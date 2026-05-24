<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$adIds = App\Adisyonlar::where('salon_id', 362)->pluck('id');
$rIds  = App\Randevular::where('salon_id', 362)->pluck('id');
$ahIds = App\AdisyonHizmetler::whereIn('adisyon_id', $adIds)->pluck('id');
echo "Silinecek: adisyon=" . $adIds->count() . " ah=" . $ahIds->count() . " randevu=" . $rIds->count() . "\n";
App\AdisyonPaketSeanslar::whereIn('adisyon_hizmet_id', $ahIds)->delete();
App\AdisyonUrunler::whereIn('adisyon_id', $adIds)->delete();
App\AdisyonHizmetler::whereIn('adisyon_id', $adIds)->delete();
App\Adisyonlar::where('salon_id', 362)->delete();
App\RandevuHizmetler::whereIn('randevu_id', $rIds)->delete();
App\Randevular::where('salon_id', 362)->delete();
echo "Silindi. Yeni durum:\n";
echo "  randevu=" . App\Randevular::where('salon_id',362)->count() . "\n";
echo "  randevu_hizmet=" . App\RandevuHizmetler::whereIn('randevu_id', App\Randevular::where('salon_id',362)->pluck('id'))->count() . "\n";
echo "  adisyon=" . App\Adisyonlar::where('salon_id',362)->count() . "\n";
