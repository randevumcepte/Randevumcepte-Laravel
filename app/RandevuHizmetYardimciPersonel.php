<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RandevuHizmetYardimciPersonel extends Model
{
    
    protected $table = 'randevu_hizmet_yardimci_personel';
    protected $with =  ['randevu_hizmet' ,'personel' ];
    protected $fillable = [
        'randevu__hizmet_id', 'yardimci_personel_id' 
    ];
   
     
    public function personel(){
        return $this->belongsTo(Personeller::class,'yardimci_personel_id');
    }
     
   
   
    
}
