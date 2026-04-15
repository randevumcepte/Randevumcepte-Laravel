<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Islemler extends Model
{
   
    

    protected $table = 'islemler';
    
    protected $with =  ['personeller','user','subeler','salonlar','randevu','hizmet_kategorisi'];

    public function personeller()
    {
        return $this->belongsTo(Personeller::class,'personel_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function subeler(){
        return $this->belongsTo(Subeler::class,'sube_id');
    }
    public function salonlar(){
        return $this->belongsTo(Salonlar::class,'salon_id');
    } 
    public function randevu()
    {
        return $this->belongsTo(Randevular::class,'randevu_id');
    }
    
    public function hizmet_kategorisi(){
        return $this->belongsTo(Hizmet_Kategorisi::class,'hizmet_kategori_id');
    }
}