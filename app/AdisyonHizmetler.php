<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdisyonHizmetler extends Model
{
   
    protected $fillable = ['adisyon_id','hizmet_id','sure','fiyat','islem_tarihi','islem_saati','indirim_tutari','hediye'];

    protected $table = 'adisyon_hizmetler';

    /* PERF: Global $with kaldirildi. ->with(['hizmet','personel','cihaz']) gerektiginde acikca.
       Eski deger: ['hizmet','personel','cihaz'] */
    // protected $connection = 'mysql_source'; // Varsayılan olarak kaynak database

    protected static function boot()
    {
        parent::boot();

        // NOT: L5.6'da 'updated' olayi syncChanges'ten ONCE calisir; wasChanged burada hep false.
        static::updated(function ($kalem) {
            if ($kalem->isDirty('fiyat')) {
                $kalem->fiyatiRandevuyaYansit();
            }
        });
    }

    /**
     * Randevuya bagli kalemin yeni fiyatini randevu_hizmetler'e yazar. Paket seansi / seansli
     * hizmet ve telafi kalemleri haric. Ayni hizmet randevuda birden cok ise kalemler sirayla
     * eslesir; yardimci personel satirlari ana kalemin fiyatini tasir.
     * New-Laravel App\Models\AdisyonHizmetler ile ayni kural.
     */
    public function fiyatiRandevuyaYansit()
    {
        if (!$this->randevu_id || (int) $this->seans_sayisi > 1) {
            return;
        }
        $paketSeansi = \DB::table('adisyon_paket_seanslar')
            ->where('randevu_id', $this->randevu_id)->where('hizmet_id', $this->hizmet_id)->exists();
        if ($paketSeansi) {
            return;
        }

        $kalemIdleri = self::where('randevu_id', $this->randevu_id)->where('hizmet_id', $this->hizmet_id)
            ->orderBy('id')->pluck('id')->map(function ($id) { return (int) $id; })->all();
        $sira = array_search((int) $this->id, $kalemIdleri, true);

        $satirlar = \DB::table('randevu_hizmetler')
            ->where('randevu_id', $this->randevu_id)->where('hizmet_id', $this->hizmet_id)
            ->whereNull('telafi_kaynak_rh_id')->orderBy('id')->get(['id', 'saat', 'yardimci_personel']);
        $ana = $satirlar->filter(function ($s) { return !$s->yardimci_personel; })->values()->get($sira);
        if (!$ana) {
            return;
        }

        $idler = $satirlar->filter(function ($s) use ($ana) {
            return $s->id == $ana->id || ($s->yardimci_personel && $s->saat == $ana->saat);
        })->pluck('id')->all();
        \DB::table('randevu_hizmetler')->whereIn('id', $idler)
            ->update(['fiyat' => $this->fiyat, 'updated_at' => now()]);
    }

     public function adisyon()
    {
        return $this->belongsTo(Adisyonlar::class,'adisyon_id');
    }
     public function hizmet()
    {
        return $this->belongsTo(Hizmetler::class,'hizmet_id');
    }
     public function personel()
    {
        return $this->belongsTo(Personeller::class,'personel_id');
    }
     public function cihaz()
    {
        return $this->belongsTo(Cihazlar::class,'cihaz_id');
    }
    public function seanslar()
    {
        return $this->hasMany(AdisyonPaketSeanslar::class,'adisyon_hizmet_id');
    }
    public function tahsilatlar()
    {
        return $this->hasMany(TahsilatHizmetler::class,'adisyon_hizmet_id');
        
    }
   

    /*public function setTargetConnection()
    {
        $this->setConnection('mysql_target');
    }*/
    
}
