<?php

namespace App\SatisOrtakligiModel;


use Illuminate\Database\Eloquent\Model;
use App\Salonlar;
use App\Musteri_Temsilcileri;
use App\IsletmeYetkilileri;
class Musteri_Formlari extends Model
{
    
    protected $table = 'musteri_formlari';
  
   	protected $with = ['salon','satis_ortagi','musteri_temsilcisi','form_durumu','yetkili'];
       protected $fillable = [
        'salon_id'   ,'satis_ortagi_id','satis_ortagi_hakedis_odeme_durumu_id','durum_id' ];
   	 public function salon(){
         return $this->belongsTo(Salonlar::class,'salon_id');
         }
          public function satis_ortagi(){
         return $this->belongsTo(Salonlar::class,'satis_ortagi_id');
         }
   
      public function musteri_temsilcisi(){
        return $this->belongsTo(Musteri_Temsilcileri::class,'musteri_temsilcisi_id');
     }
     public function form_durumu(){
        return $this->belongsTo(Form_Durumu::class,'durum_id');
    }
    public function hizmetler()
    {
        return $this->hasMany(Musteri_Formlari_Hizmetler::class,'form_id');
    }
    public function yetkili()

    {
        return $this->belongsTo(IsletmeYetkilileri::class,'yetkili_id');
    }
     
   
}
