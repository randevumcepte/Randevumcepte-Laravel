<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdisyonPaketler extends Model
{
   
    protected $fillable = ['adisyon_id','paket_id','fiyat','indirim_tutari','hediye'];

    protected $table = 'adisyon_paketler';
    
    protected $with =  ['paket','seanslar','personel'];

    

     public function paket()
    {
        return $this->belongsTo(Paketler::class,'paket_id');
    }
     public function seanslar()
    {
        return $this->hasMany(AdisyonPaketSeanslar::class,'adisyon_paket_id');
    }
    public function personel()
    {
        return $this->belongsTo(Personeller::class,'personel_id');
    }
      public function tahsilatlar()
    {
        return $this->hasMany(TahsilatPaketler::class,'adisyon_paket_id');
        
    }
    
}
