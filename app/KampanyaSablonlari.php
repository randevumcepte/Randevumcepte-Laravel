<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KampanyaSablonlari extends Model
{
   
    
    protected $table = 'kampanya_sablonlari';

    public function kampanyaTuru()
    {
        return $this->belongsTo(KampanyaTuru::class,'kategori');
    }
 
  
}
