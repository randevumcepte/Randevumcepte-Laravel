<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MusteriPortfoy extends Model
{

    protected $fillable = ['user_id','salon_id','kara_liste','musteri_tipi','aktif','olusturan_personel_id'];

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

        // KVKK invariant: musteri_portfoy'a yeni bir satir eklendiginde KVKK onayi
        // henuz alinmamis kabul edilir (kvkk_onay_alindi=0) ve 4 haneli onay_kodu
        // uretilir. Portfoyu olusturan cok sayida akis var (register, panelden ekleme,
        // on gorusme, aktarim, santral...) ve bir kismi bu alanlari set etmiyordu;
        // merkezi hook ile hepsi garanti altina alinir. Cagiran alanlari acikca
        // set ettiyse (ornegin kopyalama) o degerlere dokunmayiz.
        static::creating(function ($portfoy) {
            if ($portfoy->onay_kodu === null || $portfoy->onay_kodu === '') {
                $portfoy->onay_kodu = rand(1000, 9999);
            }
            if ($portfoy->kvkk_onay_alindi === null) {
                $portfoy->kvkk_onay_alindi = 0;
            }
        });

        // Pasif -> aktif (0 -> 1) reaktivasyonunda KVKK yeniden istenir: onay
        // sifirlanir ve yeni bir 4 haneli kod uretilir. Cagiran bu save'de ilgili
        // alani zaten degistirdiyse onun degerini korur.
        static::updating(function ($portfoy) {
            if (!$portfoy->isDirty('aktif')) {
                return;
            }
            $eski = $portfoy->getOriginal('aktif');
            $eskiPasif = ($eski === null || $eski === 0 || $eski === '0' || $eski === false);
            $yeniAktif = (!empty($portfoy->aktif) && $portfoy->aktif !== '0');
            if ($eskiPasif && $yeniAktif) {
                if (!$portfoy->isDirty('kvkk_onay_alindi')) {
                    $portfoy->kvkk_onay_alindi = 0;
                }
                if (!$portfoy->isDirty('onay_kodu') || $portfoy->onay_kodu === null || $portfoy->onay_kodu === '') {
                    $portfoy->onay_kodu = rand(1000, 9999);
                }
            }
        });
    }
}