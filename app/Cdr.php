<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cdr extends Model
{
    
    protected $table = 'cdr';
    protected $fillable = ['user_id','personel_id','tarih_saat','telefon','durum','ses_kaydi'];
    protected $with = ['musteri','personel'];
    public function musteri()
    {
        return $this->belongsTo(User::class,'user_id');
    }
   
    public function personel(){
        return $this->belongsTo(Personeller::class,'personel_id');
    }
     
    
   
   
    
}
