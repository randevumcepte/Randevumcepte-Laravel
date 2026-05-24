<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rIds = App\Randevular::where('salon_id', 362)->pluck('id');
echo "Salon 362 randevu: " . $rIds->count() . "\n";
echo "RH (sure_dk > 0): " . App\RandevuHizmetler::whereIn('randevu_id', $rIds)->where('sure_dk', '>', 0)->count() . "\n";
echo "RH (toplam): " . App\RandevuHizmetler::whereIn('randevu_id', $rIds)->count() . "\n";
$noRh = App\Randevular::where('salon_id', 362)->whereNotIn('id', App\RandevuHizmetler::whereIn('randevu_id', $rIds)->pluck('randevu_id')->unique())->count();
echo "RH'siz randevu: " . $noRh . "\n";
