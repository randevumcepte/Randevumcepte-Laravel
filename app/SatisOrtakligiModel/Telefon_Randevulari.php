<?php

namespace App\SatisOrtakligiModel;
use App\Salonlar;

use Illuminate\Database\Eloquent\Model;


class Telefon_Randevulari extends Model
{
    
    protected $table = 'telefon_randevulari';
    
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $with =['salon','satis_ortagi','musteri_temsilcisi'];
       public function salon(){
        return $this->belongsTo(Salonlar::class,'musteri_id');
    }
    public function satis_ortagi(){
        return $this->belongsTo(SatisOrtaklari::class,'satis_ortagi_id');
    }
    public function musteri_temsilcisi(){
        return $this->belongsTo(Musteri_Temsilcileri::class,'musteri_temsilcisi_id');
    }
   

    
   
     
   
     
   
}
