<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonelPuanlar extends Model
{
   
    protected $fillable = ['personel_id','musteri_id','puan'];

    protected $table = 'personel_puanlar';
    
    protected $with =  ['salon_personelleri','users'];

    public function salon_personelleri()
    {
        return $this->belongsTo(Personeller::class);
    }
     public function users()
    {
        return $this->belongsTo(User::class);
    }
}
