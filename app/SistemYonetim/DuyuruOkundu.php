<?php

namespace App\SistemYonetim;

use Illuminate\Database\Eloquent\Model;

class DuyuruOkundu extends Model
{
    protected $table = 'sistemyonetim_duyuru_okundu';
    public $timestamps = false;
    protected $fillable = ['duyuru_id', 'salon_id', 'user_id', 'okundu_tarihi'];
}
