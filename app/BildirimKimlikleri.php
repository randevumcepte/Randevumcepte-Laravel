<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BildirimKimlikleri extends Model
{
   
    

    protected $table = 'bildirim_kimlikleri';
    
    protected $with =  ['musteri','personel'];

    public function musteri()
    {
        return $this->belongsTo(User::class,'user_id');
    } 
    public function personel()
    {
    	return $this->belongsTo(Personeller::class,'isletme_yetkili_id');
    }
    
    


    
}