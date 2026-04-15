<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalonCalismaSaatleri extends Model
{
   
    protected $fillable = ['salon_id','haftanin_gunu','calisiyor','baslangic_saati', 'bitis_saati'];

    protected $table = 'salon_calisma_saatleri';
    
    
}
