<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Adisyonlar extends Model
{
   
   //protected $connection = 'mysql_source'; // Varsayılan olarak kaynak database
    protected $fillable = ['salon_id','user_id'];

    protected $table = 'adisyonlar';
    
    protected $with =  ['salon','musteri','urunler','hizmetler','paketler','olusturan'];

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
   
     /*public function setTargetConnection()
    }
    {
        $this->setConnection('mysql_target');
    }*/

    
}
