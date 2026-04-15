<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonelYetkileri extends Model
{
    
    protected $table = 'personel_yetkileri';
    protected $with =  ['isletmeyetkilileri','yetkiler'];
 
    public function yetkili()
    {
        return $this->belongsTo(IsletmeYetkilileri::class,'user_id');
    }
    public function yetkiler(){
    	return $this->belongsTo(Yetkiler::class,'yetki_id');
    }
   

   
   
    
}
