<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Paket tanim tablolari
foreach (['paketler','salon_paketler','salon_paketleri','paket_hizmetleri','paket_hizmet'] as $t) {
    if (Illuminate\Support\Facades\Schema::hasTable($t)) {
        echo "$t kolonlari: " . implode(', ', Illuminate\Support\Facades\Schema::getColumnListing($t)) . "\n";
    } else echo "$t: yok\n";
}

// Adisyon urun
foreach (['adisyon_urunler','adisyon_urun'] as $t) {
    if (Illuminate\Support\Facades\Schema::hasTable($t)) {
        echo "$t kolonlari: " . implode(', ', Illuminate\Support\Facades\Schema::getColumnListing($t)) . "\n";
    }
}

// AdisyonPaketler test - paket_id NULL kabul ediyor mu
echo "\nAdisyonPaketler nullable check (paket_id):\n";
try {
    $a = \App\Adisyonlar::where('salon_id', 362)->first();
    if ($a) {
        echo "Salon 362'de adisyon var, deneme yapilabilir\n";
    } else {
        echo "Salon 362'de adisyon yok (silinmisti); deneme yapilamadi.\n";
    }
} catch (\Exception $e) { echo "  ERR: " . $e->getMessage() . "\n"; }

// Salon 362'nin SalonHizmetler'i kac tane (paket adayi)
echo "Salon 362 SalonHizmetler: " . \App\SalonHizmetler::where('salon_id', 362)->count() . "\n";
