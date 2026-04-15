<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalonYorumlar extends Model
{
   
    protected $fillable = ['salon_id','user_id','yorum'];

    protected $table = 'salon_yorumlar';
    
    protected $with =  ['salonlar','users'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class);
    }
     public function users()
    {
        return $this->belongsTo(User::class);
    }
}
