<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BildirimKimlikleri extends Model
{
   
    

    protected $table = 'bildirim_kimlikleri';

    protected $guarded = [];

    protected $with =  ['musteri','personel'];

    public function musteri()
    {
        return $this->belongsTo(User::class,'user_id');
    } 
    public function personel()
    {
    	return $this->belongsTo(Personeller::class,'isletme_yetkili_id');
    }

    /**
     * Brand izolasyonu icin cihaz kayitlarini salon'un app_bundle'ina daralt.
     * NotificationService icindeki findTokens/logToDb filtresiyle ayni mantik;
     * BildirimController + Jobs + CustomerController gibi merkezi disi push
     * akislarinda tek noktadan uygulanabilsin diye scope olarak paketlendi.
     *
     * Usage:
     *   BildirimKimlikleri::where('user_id', $x)->forBrand($salonId)->pluck('bildirim_id')
     *
     * Salon bulunamazsa (id gecersiz) veya salon'un app_bundle'i bossa filtre
     * uygulanmaz -> geriye donuk uyum (eski salon kayitlari icin push kaybi olmaz).
     */
    public function scopeForBrand($query, $salonId)
    {
        if (empty($salonId)) return $query;
        $brandBundle = \App\Salonlar::where('id', $salonId)->value('app_bundle');
        if (empty($brandBundle)) return $query;
        return $query->where('app_bundle', $brandBundle);
    }
}