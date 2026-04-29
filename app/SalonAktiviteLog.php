<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalonAktiviteLog extends Model
{
    protected $table = 'salon_aktivite_log';

    protected $fillable = [
        'salon_id',
        'user_id', 'user_type', 'user_name', 'user_rol',
        'action', 'target_type', 'target_id', 'target_label',
        'aciklama', 'meta',
        'ip', 'user_agent',
    ];
}
