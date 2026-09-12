<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Adisyonlar extends Model
{
   
   //protected $connection = 'mysql_source'; // Varsayılan olarak kaynak database
    protected $fillable = ['salon_id','user_id','harici','fatura_kesildi','fatura_kesildi_tarihi','fatura_kesen_personel_id','odendi','odendi_tarihi'];

    protected $casts = [
        'harici' => 'boolean',
        'fatura_kesildi' => 'boolean',
        'fatura_kesildi_tarihi' => 'datetime',
    ];

    protected $table = 'adisyonlar';

    /* PERF: Global $with kaldirildi (eskiden salon,musteri,urunler,hizmetler,paketler,olusturan
       her zaman yukleniyordu; urunler/hizmetler/paketler kendi $with'leriyle agaci buyutuyordu).
       Iliskiye erisilince lazy-load eder; gerekli yerlerde ->with([...]) kullanin.
       Eski deger: ['salon','musteri','urunler','hizmetler','paketler','olusturan'] */

    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
      public function musteri()
    {
        return $this->belongsTo(User::class,'user_id');
    }
     public function hizmetler(){
        return $this->hasMany(AdisyonHizmetler::class,'adisyon_id');
        
    }

    public function urunler(){
        return $this->hasMany(AdisyonUrunler::class,'adisyon_id');
        
    }
    public function paketler(){
        return $this->hasMany(AdisyonPaketler::class,'adisyon_id');
        
    }
    public function olusturan()
    {
        return $this->belongsTo(IsletmeYetkilileri::class,'olusturan_id');
    }

    /**
     * Studyo modu (fiyat gizleme): "Odeme Alindi" -> adisyonu TAM kapatir.
     * Fiyat sistemde tutulur ama UI'da gizli; tam tahsilat dusulunce kalan=0 olur
     * ve adisyon normal mekanizmayla "kapali" sayilir (cekirdek sorguya dokunulmaz).
     * Kalem bazli tahsilat satirlari uretir (odenen kalem tahsilatlarindan hesaplaniyor).
     */
    public function tamOde($odemeYontemiId = null, $olusturanId = null)
    {
        $this->load(['hizmetler','urunler','paketler']);
        $odemeYontemiId = $odemeYontemiId ?: (\App\OdemeYontemleri::orderBy('id')->value('id') ?: 1);

        $hizmetKalem = []; $urunKalem = []; $paketKalem = []; $toplam = 0;
        foreach ($this->hizmetler as $h) {
            $paid = \App\TahsilatHizmetler::where('adisyon_hizmet_id',$h->id)->sum('tutar');
            $rem = (float)$h->fiyat - (float)$paid - (float)($h->indirim_tutari ?? 0);
            if ($rem > 0) { $hizmetKalem[] = [$h->id,$rem]; $toplam += $rem; }
        }
        foreach ($this->urunler as $u) {
            $paid = \App\TahsilatUrunler::where('adisyon_urun_id',$u->id)->sum('tutar');
            $rem = (float)$u->fiyat - (float)$paid - (float)($u->indirim_tutari ?? 0);
            if ($rem > 0) { $urunKalem[] = [$u->id,$rem]; $toplam += $rem; }
        }
        foreach ($this->paketler as $p) {
            $paid = \App\TahsilatPaketler::where('adisyon_paket_id',$p->id)->sum('tutar');
            $rem = (float)$p->fiyat - (float)$paid - (float)($p->indirim_tutari ?? 0);
            if ($rem > 0) { $paketKalem[] = [$p->id,$rem]; $toplam += $rem; }
        }

        $tahsilat = new \App\Tahsilatlar();
        $tahsilat->adisyon_id = $this->id;
        $tahsilat->user_id = $this->user_id;
        $tahsilat->salon_id = $this->salon_id;
        $tahsilat->odeme_tarihi = date('Y-m-d');
        $tahsilat->odeme_yontemi_id = $odemeYontemiId;
        if ($olusturanId) $tahsilat->olusturan_id = $olusturanId;
        $tahsilat->tutar = $toplam;
        if (\Schema::hasColumn('tahsilatlar','yapilan_odeme')) $tahsilat->yapilan_odeme = $toplam;
        $tahsilat->save();

        foreach ($hizmetKalem as $k) { $t=new \App\TahsilatHizmetler(); $t->adisyon_hizmet_id=$k[0]; $t->tahsilat_id=$tahsilat->id; $t->tutar=$k[1]; $t->save(); }
        foreach ($urunKalem as $k) { $t=new \App\TahsilatUrunler(); $t->adisyon_urun_id=$k[0]; $t->tahsilat_id=$tahsilat->id; $t->tutar=$k[1]; $t->save(); }
        foreach ($paketKalem as $k) { $t=new \App\TahsilatPaketler(); $t->adisyon_paket_id=$k[0]; $t->tahsilat_id=$tahsilat->id; $t->tutar=$k[1]; $t->save(); }

        if (\Schema::hasColumn('adisyonlar','odendi')) $this->odendi = 1;
        if (\Schema::hasColumn('adisyonlar','odendi_tarihi')) $this->odendi_tarihi = date('Y-m-d H:i:s');
        $this->save();
        return true;
    }

    /**
     * Studyo modu "Odeme Alinmadi": bu adisyonun tum tahsilatlarini kaldirir
     * (studyoda kismi odeme yok, ikili durum) -> adisyon tekrar "acik" olur.
     */
    public function odemeGeriAl()
    {
        $tahsilatIds = \App\Tahsilatlar::where('adisyon_id',$this->id)->pluck('id');
        if ($tahsilatIds->count()) {
            \App\TahsilatHizmetler::whereIn('tahsilat_id',$tahsilatIds)->delete();
            \App\TahsilatUrunler::whereIn('tahsilat_id',$tahsilatIds)->delete();
            \App\TahsilatPaketler::whereIn('tahsilat_id',$tahsilatIds)->delete();
            \App\Tahsilatlar::where('adisyon_id',$this->id)->delete();
        }
        if (\Schema::hasColumn('adisyonlar','odendi')) $this->odendi = 0;
        if (\Schema::hasColumn('adisyonlar','odendi_tarihi')) $this->odendi_tarihi = null;
        $this->save();
        return true;
    }
   
     /*public function setTargetConnection()
    }
    {
        $this->setConnection('mysql_target');
    }*/

    
}
