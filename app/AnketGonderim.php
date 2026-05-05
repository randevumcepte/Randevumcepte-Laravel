<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnketGonderim extends Model
{
    protected $table = 'anket_gonderimleri';

    protected $fillable = [
        'salon_id', 'sablon_id', 'randevu_id', 'arsiv_id', 'user_id', 'personel_id',
        'token', 'ad_soyad', 'telefon',
        'gonderim_kanali', 'gonderim_zamani', 'son_gecerlilik',
        'cevaplandi', 'cevap_zamani', 'cevaplar_json',
        'nps_skoru', 'csat_skoru', 'genel_yorum',
        'ip', 'user_agent', 'kvkk_onay',
    ];

    protected $dates = ['gonderim_zamani', 'son_gecerlilik', 'cevap_zamani'];

    public function sablon()
    {
        return $this->belongsTo(AnketSablon::class, 'sablon_id');
    }

    public function salon()
    {
        return $this->belongsTo(Salonlar::class, 'salon_id');
    }

    public function musteri()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function randevu()
    {
        return $this->belongsTo(Randevular::class, 'randevu_id');
    }
}
