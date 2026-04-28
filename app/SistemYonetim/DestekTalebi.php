<?php

namespace App\SistemYonetim;

use Illuminate\Database\Eloquent\Model;

class DestekTalebi extends Model
{
    protected $table = 'sistemyonetim_destek_talepleri';
    protected $fillable = [
        'numara', 'salon_id', 'salon_adi',
        'iletisim_ad', 'iletisim_telefon', 'iletisim_email',
        'konu', 'aciklama', 'kategori', 'oncelik', 'durum',
        'atanan_user_id', 'atanan_user_name',
        'olusturan_user_id', 'olusturan_user_name',
        'ilk_yanit_tarihi', 'cozumlenme_tarihi', 'kapanis_tarihi',
    ];

    public function mesajlar()
    {
        return $this->hasMany(DestekMesaji::class, 'ticket_id')->orderBy('id', 'asc');
    }
}
