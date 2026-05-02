<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Salonlar extends Model
{
	protected $table = 'salonlar';
    protected $fillable = [
        'salon_adi', 'adres' , 'satis_ortagi_id','pasif_ortak_id','il_id','ilce_id','telefon_1','telefon_2','telefon_3' ,'yetkili_adi','yetkili_telefon','hesap_acildi','aciklama',
        'whatsapp_aktif','whatsapp_durum','whatsapp_numara','whatsapp_baglanti_tarihi','whatsapp_gunluk_limit','whatsapp_warmup_baslangic','whatsapp_son_hata',
        'whatsapp_saglayici','cloud_api_phone_number_id','cloud_api_token',
        'cloud_api_template_1gun','cloud_api_template_yaklasan','cloud_api_template_iptal','cloud_api_template_guncelleme','cloud_api_template_dil' ];

    protected $casts = [
        'whatsapp_aktif' => 'boolean',
        'whatsapp_baglanti_tarihi' => 'datetime',
        'whatsapp_warmup_baslangic' => 'datetime',
    ];
    //protected $with =  ['il', 'ilce', 'salon_turu','calisma_saatleri','mola_saatleri'];
    public function personeller()
    {
        return $this->hasMany(Personeller::class,'salon_id');
    }
    
    public function il()
    {
        return $this->belongsTo(Iller::class,'il_id');
    }

    public function ilce()
    {
        return $this->belongsTo(\App\Ilceler::class,'ilce_id');
    }

    public function salon_turu()
    {
        return $this->belongsTo(\App\SalonTuru::class,'salon_turu_id');
    }



   
}
