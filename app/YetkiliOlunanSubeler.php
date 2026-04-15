<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class YetkiliOlunanSubeler extends Model
{
    
    protected $table = 'yetkili_olunan_subeler';
    protected $fillable = ['salon_id','yetkili_id'];
    protected $with = ['yetkili','salon'];
    
    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function yetkili()
    {
        return $this->belongsTo(IsletmeYetkilileri::class,'yetkili_id');
    }
     
   
   
    
}
