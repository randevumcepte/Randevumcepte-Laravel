<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

$uid = 11982; $sid = 362;

echo 'TOPLAM: ' . DB::table('randevular')->where('user_id',$uid)->where('salon_id',$sid)->count() . PHP_EOL;

echo PHP_EOL . '--- Saat dagilimi ---' . PHP_EOL;
$saatler = DB::table('randevular')->where('user_id',$uid)->where('salon_id',$sid)->select('saat', DB::raw('COUNT(*) as c'))->groupBy('saat')->orderBy('saat')->get();
foreach ($saatler as $s) echo "  {$s->saat}: {$s->c}" . PHP_EOL;

$rIds = DB::table('randevular')->where('user_id',$uid)->where('salon_id',$sid)->pluck('id');
$hizmetli = DB::table('randevu_hizmetler')->whereIn('randevu_id',$rIds)->whereNotNull('hizmet_id')->distinct()->count('randevu_id');
$rhSiz = DB::table('randevular')->where('user_id',$uid)->where('salon_id',$sid)->whereNotExists(function($q){ $q->selectRaw('1')->from('randevu_hizmetler')->whereColumn('randevu_hizmetler.randevu_id','randevular.id'); })->count();
echo PHP_EOL . "hizmetli randevu = $hizmetli" . PHP_EOL;
echo "rh kaydi olmayan randevu = $rhSiz" . PHP_EOL;

echo PHP_EOL . '--- Duplicate (tarih+saat) ---' . PHP_EOL;
$dups = DB::table('randevular')->where('user_id',$uid)->where('salon_id',$sid)->select('tarih','saat',DB::raw('COUNT(*) as c'))->groupBy('tarih','saat')->having('c','>',1)->get();
foreach ($dups as $d) echo "  {$d->tarih} {$d->saat}: {$d->c}" . PHP_EOL;
echo 'duplicate sayisi: ' . $dups->count() . PHP_EOL;

echo PHP_EOL . '--- Durum/Geldi ---' . PHP_EOL;
$durums = DB::table('randevular')->where('user_id',$uid)->where('salon_id',$sid)->select('durum','randevuya_geldi',DB::raw('COUNT(*) as c'))->groupBy('durum','randevuya_geldi')->get();
foreach ($durums as $d) echo "  durum={$d->durum} geldi=" . ($d->randevuya_geldi ?? 'NULL') . ": {$d->c}" . PHP_EOL;

echo PHP_EOL . '--- Ilk 10 ---' . PHP_EOL;
$ilk = DB::table('randevular')->where('user_id',$uid)->where('salon_id',$sid)->orderBy('tarih')->orderBy('saat')->limit(10)->select('id','tarih','saat','durum','personel_notu')->get();
foreach ($ilk as $r) {
  $not = mb_strimwidth($r->personel_notu ?? '', 0, 50, '...', 'UTF-8');
  echo "  id={$r->id} {$r->tarih} {$r->saat} d={$r->durum} not='{$not}'" . PHP_EOL;
}
