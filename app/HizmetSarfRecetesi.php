<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HizmetSarfRecetesi extends Model
{
    protected $table = 'hizmet_sarf_receteleri';

    protected $fillable = [
        'salon_id', 'hizmet_id', 'hizmet_tipi', 'urun_id', 'miktar', 'aktif',
    ];

    protected $casts = [
        'miktar' => 'decimal:3',
        'aktif'  => 'boolean',
    ];

    public function urun()
    {
        return $this->belongsTo(Urunler::class, 'urun_id');
    }
}
