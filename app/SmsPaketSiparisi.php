<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsPaketSiparisi extends Model
{
    protected $table = 'sms_paket_siparisleri';

    protected $fillable = [
        'salon_id', 'paket_id', 'sms_adet', 'tutar', 'merchant_oid',
        'durum', 'basarisiz_neden',
        'yukleme_durumu', 'yukleme_tarihi', 'yukleyen',
        'fatura_unvan', 'fatura_vkn', 'fatura_vergi_dairesi', 'fatura_adres',
        'fatura_durumu', 'fatura_no', 'fatura_url',
    ];

    public function salon()
    {
        return $this->belongsTo(Salonlar::class, 'salon_id');
    }
}
