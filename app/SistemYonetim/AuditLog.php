<?php

namespace App\SistemYonetim;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'sistemyonetim_audit_log';
    protected $fillable = [
        'user_id', 'user_name', 'user_rol',
        'action', 'target_type', 'target_id', 'target_label',
        'aciklama', 'meta', 'ip', 'user_agent',
    ];
}
