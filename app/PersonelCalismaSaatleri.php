<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonelCalismaSaatleri extends Model
{
   
    protected $fillable = ['personel_id','haftanin_gunu','calisiyor','baslangic_saati','bitis_saati'];

    protected $table = 'personel_calisma_saatleri';
    
    protected $with =  ['personeller'];

    public function personeller()
    {
        return $this->belongsTo(Personeller::class);
    }
    
}
