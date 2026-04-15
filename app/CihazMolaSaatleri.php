<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CihazMolaSaatleri extends Model
{
   
    protected $fillable = ['cihaz_id','haftanin_gunu','mola_var','baslangic_saati','bitis_saati'];

    protected $table = 'cihaz_mola_saatleri';
    
    protected $with =  ['cihazlar'];

    public function cihazlar()
    {
        return $this->belongsTo(Cihazlar::class,'cihaz_id');
    }
    
}
