<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdisyonUrunler extends Model
{
   
    protected $fillable = ['adisyon_id','urun_id','adet','fiyat','indirim_tutari','hediye'];

    protected $table = 'adisyon_urunler';
    
    protected $with =  ['urun','personel'];

  
     public function urun()
    {
        return $this->belongsTo(Urunler::class,'urun_id');
    }
    public function personel()
    {
        return $this->belongsTo(Personeller::class,'personel_id');
    }
      public function tahsilatlar()
    {
        return $this->hasMany(TahsilatUrunler::class,'adisyon_urun_id');
        
    }
}
