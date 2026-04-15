<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KVKKOnay extends Model
{
   
    

    protected $table = 'kvkk_onay';
    
    protected $with =  ['salon','musteri'];

    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function musteri()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    
}
