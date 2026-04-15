<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonelMolaSaatleri extends Model
{
   
    protected $fillable = ['personel_id','haftanin_gunu','mola_var','baslangic_saati','bitis_saati'];

    protected $table = 'personel_mola_saatleri';
    
    protected $with =  ['personeller'];

    public function personeller()
    {
        return $this->belongsTo(Personeller::class,'personel_id');
    }
    
}
