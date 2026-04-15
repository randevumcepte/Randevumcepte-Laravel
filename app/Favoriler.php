<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Favoriler extends Model
{
   
    protected $fillable = ['salon_id','user_id'];

    protected $table = 'favoriler';
    
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
