<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Cagri Merkezi: yoneticinin tanimladigi gorusme scriptleri.
 * Agent calisma ekraninda dropdown'dan secip okur.
 */
class CagriScript extends Model
{
    protected $table = 'cagri_scriptleri';

    protected $fillable = [
        'salon_id',
        'baslik',
        'icerik',
        'aktif',
    ];
}
