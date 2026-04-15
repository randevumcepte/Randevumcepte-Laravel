<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IlacKullanimGecmisi extends Model
{
    protected $table = 'ilac_kullanim_gecmisi';

    protected $fillable = [
        'ilac_id',
        'user_id',
        'kullanim_tarihi',
        'kullanilan_adet',
    ];

    protected $casts = [
        'kullanim_tarihi' => 'datetime',
    ];

    /**
     * İlaç ilişkisi
     */
    public function ilac()
    {
        return $this->belongsTo(Ilac::class, 'ilac_id');
    }

    /**
     * Kullanıcı ilişkisi
     */
    public function kullanici()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

  
}