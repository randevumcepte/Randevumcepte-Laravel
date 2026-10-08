<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RandevuHizmetler extends Model
{
    
    protected $table = 'randevu_hizmetler';
    //protected $with =  ['hizmetler' ,'personeller','cihaz'];
    
    protected $fillable = [
        'randevu_id', 'hizmet_id' ,'personel_id' ,'saat','saat_bitis','oda_id','cihaz_id','sure_dk','fiyat',
        'dusum_miktari'
    ];
    public function randevu()
    {
        return $this->belongsTo(Randevular::class, 'randevu_id');
    }

    public function hizmetler()
    {
        return $this->belongsTo(Hizmetler::class,'hizmet_id');
    }
    
    public function personeller(){
        return $this->belongsTo(Personeller::class,'personel_id');
    }
    public function cihaz(){
        return $this->belongsTo(Cihazlar::class,'cihaz_id');
    }
    public function oda()
    {
        return $this->belongsTo(Odalar::class,'oda_id');
    }

    /**
     * Google Calendar entegrasyonu: RH kaydet/sil event'lerinde ilgili personelin
     * Google Takvimine push. Yetkili baglantisi yoksa no-op; hata API'den donerse
     * log'lanir, uygulama akisi bozulmaz.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($rh) {
            try {
                // Yalniz forward tarihli ve personel atanmis randevulari sync et
                if (!$rh->personel_id || !$rh->randevu_id) return;
                $svc = app(\App\Services\GoogleCalendarService::class);
                $svc->syncRandevuHizmet($rh);
            } catch (\Throwable $e) {
                \Log::warning('[GoogleCalendar] saved event hata', ['rh_id' => $rh->id, 'hata' => $e->getMessage()]);
            }
        });

        static::deleting(function ($rh) {
            try {
                $svc = app(\App\Services\GoogleCalendarService::class);
                $svc->removeRandevuHizmet($rh->id);
            } catch (\Throwable $e) {
                \Log::warning('[GoogleCalendar] deleting event hata', ['rh_id' => $rh->id, 'hata' => $e->getMessage()]);
            }
        });
    }
}
