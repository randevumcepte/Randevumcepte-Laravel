<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Ilac extends Model
{


    // Tablo adı (Laravel otomatik olarak "ilacs" beklerdi, bu yüzden belirtiyoruz)
    protected $table = 'ilaclar';

    // Doldurulabilir alanlar
    protected $fillable = [
        'adi',
        'gunluk_kullanim',
        'saatler',
        'kalan_adet',
        'baslangic_tarihi',
        'bitis_tarihi',
        'user_id',
    ];

    // JSON sütunu otomatik olarak diziye dönsün
    protected $casts = [
        'saatler' => 'array',
        'baslangic_tarihi' => 'datetime',
        'bitis_tarihi' => 'datetime',
    ];

    /**
     * Kullanıcı ilişkisi (eğer kullanıcı tablosu varsa)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function kulanimGecmisi()
    {
        return $this->hasMany(IlacKullanimGecmisi::class, 'ilac_id');
    }
}
