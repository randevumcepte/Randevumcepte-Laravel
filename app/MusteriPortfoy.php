<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MusteriPortfoy extends Model
{

    protected $fillable = ['user_id','salon_id','kara_liste','musteri_tipi','aktif','musteri_tipi'];

    protected $table = 'musteri_portfoy';

    protected $with =  ['users'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function users()
    {
    	return $this->belongsTo(User::class,'user_id');
    }

    protected static function boot()
    {
        parent::boot();
        // Yeni musteri (panelden / online / santral) eklendigi anda
        // hatirlatma feed cache'ini temizle ki popup gecikmeden gozuksun
        static::created(function ($portfoy) {
            try {
                if ($portfoy && $portfoy->salon_id) {
                    Cache::forget('salon_hatirlatma.' . $portfoy->salon_id);
                }
            } catch (\Throwable $e) {}
        });
    }
}