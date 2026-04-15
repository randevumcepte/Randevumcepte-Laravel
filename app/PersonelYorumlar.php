<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonelYorumlar extends Model
{
   
    protected $fillable = ['personel_id','musteri_id','yorum'];

    protected $table = 'personel_yorumlar';
    
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
