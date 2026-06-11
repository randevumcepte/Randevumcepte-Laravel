<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    

    protected $fillable = [
        'name',   'password','cep_telefon','whatsapp_onay'
    ];

    protected $casts = [
        'whatsapp_onay' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    protected $with =  ['il', 'ilce'];

    /**
     * İsim kaydedilirken her kelimenin baş harfini otomatik büyüt (Türkçe uyumlu).
     * Sadece yeni atamalarda çalışır; mevcut kayıtlar okunurken değişmez.
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = \App\Helpers\Metin::basHarfBuyut($value);
    }

     public function il()
    {
        return $this->belongsTo(Iller::class,'il_id');
    }
      public function ilce()
    {
        return $this->belongsTo(Ilceler::class,'ilce_id');
    }
    public function salonlar()
    {
        return $this->hasMany(MusteriPortfoy::class,'user_id');
    }
}
