<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdetDuzeni extends Model
{
    

    protected $table = 'adet_duzeni';

    protected $fillable = [
        'user_id',
        'baslangic_tarihi',
        'bitis_tarihi',
        'sure',
        'dongu',
        'belirtiler',
        'not_metni',
        'renk'
    ];

    protected $casts = [
        'baslangic_tarihi' => 'date',
        'bitis_tarihi' => 'date',
        'belirtiler' => 'array'
    ];

    // Kullanıcı ilişkisi
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}