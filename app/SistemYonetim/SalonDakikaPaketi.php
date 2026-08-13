<?php

namespace App\SistemYonetim;

use Illuminate\Database\Eloquent\Model;

class SalonDakikaPaketi extends Model
{
    protected $table = 'salon_dakika_paketi';

    protected $fillable = [
        'salon_id', 'tanimli_dakika', 'sayim_baslangic', 'guncelleyen',
    ];
}
