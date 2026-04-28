<?php

namespace App\SistemYonetim;

use Illuminate\Database\Eloquent\Model;

class ImpersonationLog extends Model
{
    protected $table = 'sistemyonetim_impersonation_loglari';
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'user_name', 'salon_id', 'salon_adi',
        'isletme_yetkili_id', 'isletme_yetkili_email',
        'sebep', 'ticket_id', 'baslangic_tarihi', 'bitis_tarihi',
        'ip', 'user_agent',
    ];
}
