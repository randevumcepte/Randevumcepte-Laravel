<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Randevular extends Model
{
     const
        BEKLENIYOR = 0,
        ONAYLANDI = 1,
        IPTAL_EDILDI = 2,
        KABUL_EDILMEDI = 3 
         
    ;
    protected $table = 'randevular';
    //protected $with =  ['users','salonlar','sube','hizmetler'];
    protected $fillable = [
        'user_id', 'salon_id' ,'tarih' ,'saat','personel_notu' , 'salon' ,'web','uygulama', 'olusturan_personel_id', 'durum'
    ];
   
      public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function users(){
    	return $this->belongsTo(User::class,'user_id');
    }
    public function hizmetler(){
        return $this->hasMany(RandevuHizmetler::class,'randevu_id');
        
    }
    public function ongorusme()
    {
        return $this->belongsTo(OnGorusmeler::class,'on_gorusme_id');
    }
    public function sube(){
       return $this->belongsTo(Subeler::class,'sube_id');
    }
   
    public function olusturan_personel(){
        return $this->belongsTo(IsletmeYetkilileri::class, 'olusturan_personel_id');

    }
    public function olusturan_musteri()
    {
        return $this->belongsTo(User::class,'olusturan_user_id');
    }
    

   
   
    
}
