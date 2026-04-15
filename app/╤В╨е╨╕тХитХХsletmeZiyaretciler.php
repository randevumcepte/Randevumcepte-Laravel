<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IsletmeZiyaretciler extends Model
{
   
    protected $fillable = ['salon_id','ipadres'];

    protected $table = 'isletme_ziyaretciler';
    
    protected $with =  ['salonlar'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    
}
