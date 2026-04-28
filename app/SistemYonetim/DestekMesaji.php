<?php

namespace App\SistemYonetim;

use Illuminate\Database\Eloquent\Model;

class DestekMesaji extends Model
{
    protected $table = 'sistemyonetim_destek_mesajlari';
    protected $fillable = [
        'ticket_id', 'user_id', 'user_name', 'user_tipi',
        'mesaj', 'ic_not',
    ];
}
