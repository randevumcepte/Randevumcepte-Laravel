<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$adlar = ['Lightening serum', 'EYE CREAM', 'FOAM CLENSER NORMAL', 'LIGHTENING ANTI AGE NIGHT CREAM'];

echo "=== Bizdeki Hizmetler tablosunda ===\n";
foreach ($adlar as $ad) {
    $h = App\Hizmetler::where('hizmet_adi', 'LIKE', '%' . trim($ad) . '%')->first();
    echo "  '$ad': " . ($h ? "VAR (id={$h->id})" : "yok") . "\n";
}

echo "\n=== Bizdeki Urunler tablosunda (salon 362) ===\n";
foreach ($adlar as $ad) {
    $u = App\Urunler::where('salon_id', 362)->where('urun_adi', 'LIKE', '%' . trim($ad) . '%')->first();
    echo "  '$ad': " . ($u ? "VAR (id={$u->id})" : "yok") . "\n";
}

echo "\n=== Bizdeki SalonHizmetler salon 362'de bu hizmetlere bagli ===\n";
foreach ($adlar as $ad) {
    $h = App\Hizmetler::where('hizmet_adi', 'LIKE', '%' . trim($ad) . '%')->first();
    if ($h) {
        $sh = App\SalonHizmetler::where('salon_id', 362)->where('hizmet_id', $h->id)->first();
        echo "  '$ad': " . ($sh ? "SalonHizmetler'da var" : "salon disi") . "\n";
    }
}
