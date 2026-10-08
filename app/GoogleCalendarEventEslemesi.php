<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GoogleCalendarEventEslemesi extends Model
{
    protected $table = 'google_calendar_event_eslemeleri';

    protected $fillable = [
        'baglanti_id', 'randevu_hizmet_id', 'google_event_id', 'son_sync_zamani',
    ];

    protected $dates = ['son_sync_zamani'];

    public function baglanti()
    {
        return $this->belongsTo(GoogleCalendarBaglanti::class, 'baglanti_id');
    }
}
