<?php
// Sunucuda calistir:  php tmp/teshis_526280.php
// (gerekirse autoload/bootstrap yolunu kendi sunucuna gore duzelt)
require_once __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$ADISYON = 526280;
$line = str_repeat('=',60);

echo "\n$line\nADISYON $ADISYON TESHIS\n$line\n";

$tah = DB::table('tahsilatlar')->where('adisyon_id',$ADISYON)->orderBy('id')->get();
echo "\n-- TAHSILATLAR (".count($tah).") --\n";
foreach($tah as $t){
    $hz = DB::table('tahsilat_hizmetler')->where('tahsilat_id',$t->id)->count();
    $pk = DB::table('tahsilat_paketler')->where('tahsilat_id',$t->id)->count();
    $ur = DB::table('tahsilat_urunler')->where('tahsilat_id',$t->id)->count();
    $junction = $hz+$pk+$ur;
    $flag = $junction==0 ? '  <<< OKSUZ (detayda GORUNMEZ)' : '';
    echo "#{$t->id} tutar={$t->tutar} odeme_yon={$t->odeme_yontemi_id} tarih={$t->odeme_tarihi} | junction(hz=$hz pk=$pk ur=$ur)$flag\n";
}

echo "\n-- ALACAKLAR --\n";
foreach(DB::table('alacaklar')->where('adisyon_id',$ADISYON)->get() as $a){
    echo "#{$a->id} tutar={$a->tutar} planlanan={$a->planlanan_odeme_tarihi} aciklama=".substr($a->aciklama??'',0,40)."\n";
}

echo "\n-- ADISYON KALEMLERI (borc tarafi) --\n";
$toplamBorc=0;
foreach(DB::table('adisyon_hizmetler')->where('adisyon_id',$ADISYON)->get() as $h){
    $odenen = DB::table('tahsilat_hizmetler')->where('adisyon_hizmet_id',$h->id)->sum('tutar');
    $kalan = $h->fiyat - $h->indirim_tutari - $odenen; $toplamBorc += $h->fiyat-$h->indirim_tutari;
    echo "HIZMET#{$h->id} fiyat={$h->fiyat} indirim={$h->indirim_tutari} odenen=$odenen kalan=$kalan\n";
}
foreach(DB::table('adisyon_paketler')->where('adisyon_id',$ADISYON)->get() as $p){
    $odenen = DB::table('tahsilat_paketler')->where('adisyon_paket_id',$p->id)->sum('tutar');
    $kalan = $p->fiyat - $p->indirim_tutari - $odenen; $toplamBorc += $p->fiyat-$p->indirim_tutari;
    echo "PAKET#{$p->id} fiyat={$p->fiyat} indirim={$p->indirim_tutari} odenen=$odenen kalan=$kalan\n";
}
foreach(DB::table('adisyon_urunler')->where('adisyon_id',$ADISYON)->get() as $u){
    $odenen = DB::table('tahsilat_urunler')->where('adisyon_urun_id',$u->id)->sum('tutar');
    $kalan = $u->fiyat - $u->indirim_tutari - $odenen; $toplamBorc += $u->fiyat-$u->indirim_tutari;
    echo "URUN#{$u->id} fiyat={$u->fiyat} indirim={$u->indirim_tutari} odenen=$odenen kalan=$kalan\n";
}

$toplamTahsilat = DB::table('tahsilatlar')->where('adisyon_id',$ADISYON)->sum('tutar');
echo "\n-- OZET --\n";
echo "Toplam borc (kalem)      : $toplamBorc\n";
echo "Toplam tahsilat (kayit)  : $toplamTahsilat\n";
echo "Detayda gorunen (junction toplami): ".(
    DB::table('tahsilat_hizmetler')->whereIn('tahsilat_id',$tah->pluck('id'))->sum('tutar')
   +DB::table('tahsilat_paketler')->whereIn('tahsilat_id',$tah->pluck('id'))->sum('tutar')
   +DB::table('tahsilat_urunler')->whereIn('tahsilat_id',$tah->pluck('id'))->sum('tutar')
)."\n";
echo "\nNOT: 'OKSUZ' isaretli tahsilat varsa => cift gonderim. Karar: ya o tahsilati sil,\n";
echo "ya da gercek bir 2. odemeyse kalemlere junction satiri ekle.\n$line\n";
