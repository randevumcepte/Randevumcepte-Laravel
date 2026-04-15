<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CihazCalismaSaatleri extends Model
{
   
    protected $fillable = ['cihaz_id','haftanin_gunu','calisiyor','baslangic_saati','bitis_saati'];

    protected $table = 'cihaz_calisma_saatleri';
    
    protected $with =  ['cihazlar'];

    public function cihazlar()
    {
        return $this->belongsTo(Cihazlar::class);
    }
    
}
