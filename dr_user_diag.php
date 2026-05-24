<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$uid = 161011;
$u = App\User::find($uid);
if (!$u) { echo "User $uid yok\n"; exit; }
echo "User: id={$uid} name=[{$u->name}] tel={$u->cep_telefon}\n";

echo "\nSalon portfoy:\n";
foreach (App\MusteriPortfoy::where('user_id', $uid)->get() as $p) {
    echo "  salon_id={$p->salon_id}\n";
}

echo "\nAdisyonlar salon dagilim:\n";
$ad = App\Adisyonlar::where('user_id', $uid)
    ->selectRaw('salon_id, COUNT(*) as c')->groupBy('salon_id')->get();
foreach ($ad as $r) echo "  salon={$r->salon_id} adisyon={$r->c}\n";

echo "\nRandevular salon dagilim:\n";
$r = App\Randevular::where('user_id', $uid)
    ->selectRaw('salon_id, COUNT(*) as c')->groupBy('salon_id')->get();
foreach ($r as $row) echo "  salon={$row->salon_id} randevu={$row->c}\n";

echo "\nAyni isim '{$u->name}' veya benzer kullanicilar:\n";
$benzer = App\User::where('name', 'LIKE', '%' . trim($u->name) . '%')->take(20)->get();
foreach ($benzer as $b) echo "  id={$b->id} name=[{$b->name}] tel={$b->cep_telefon}\n";

echo "\n'FATMA OZERIM' aramasi (ad benzer):\n";
$f = App\User::where('name', 'LIKE', '%FATMA%')->where('name', 'LIKE', '%ZER%')->take(20)->get();
foreach ($f as $b) echo "  id={$b->id} name=[{$b->name}] tel={$b->cep_telefon}\n";
