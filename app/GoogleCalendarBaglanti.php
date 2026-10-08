<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GoogleCalendarBaglanti extends Model
{
    protected $table = 'google_calendar_baglantilar';

    protected $fillable = [
        'yetkili_id', 'salon_id', 'google_email', 'calendar_id',
        'access_token', 'refresh_token', 'expires_at', 'scope', 'aktif',
        'son_hata_zamani', 'son_hata_mesaji',
    ];

    protected $dates = ['expires_at', 'son_hata_zamani'];

    public function yetkili()
    {
        return $this->belongsTo(IsletmeYetkilileri::class, 'yetkili_id');
    }

    public function eslemeler()
    {
        return $this->hasMany(GoogleCalendarEventEslemesi::class, 'baglanti_id');
    }

    public function tokenGecerli()
    {
        return $this->expires_at && $this->expires_at->isFuture();
    }
}
