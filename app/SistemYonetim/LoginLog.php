<?php

namespace App\SistemYonetim;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $table = 'sistemyonetim_login_loglari';
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'email_attempt', 'basarili', 'hata',
        'ip', 'user_agent', 'created_at',
    ];
}
