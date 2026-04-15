<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalonMolaSaatleri extends Model
{
   
    protected $fillable = ['salon_id','haftanin_gunu','mola_var','baslangic_saati', 'bitis_saati'];

    protected $table = 'salon_mola_saatleri';
    
   
}
