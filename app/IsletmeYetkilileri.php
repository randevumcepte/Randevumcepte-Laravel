<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
 use Laravel\Passport\HasApiTokens;
 use Spatie\Permission\Traits\HasRoles;

class IsletmeYetkilileri extends Authenticatable
{
   use HasApiTokens, Notifiable, HasRoles;
    protected $table = 'isletmeyetkilileri';
    protected $guard = 'isletmeyonetim';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be dden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
     protected $with = ['yetkili_olunan_isletmeler'];
      
    
     public function yetkili_olunan_isletmeler(){
        return $this->hasMany(Personeller::class,'yetkili_id');
     }

     /**
      * Web oturumu gecerlilik damgasi (AuthenticateSession middleware kullanir).
      *
      * getAuthPassword (parola hash) + oturum_iptal_tarihi birlestirilir. Boylece:
      *  - parola degisince (hash degisir) -> damga degisir -> diger oturumlar cikar,
      *  - personel pasif/silme sonrasi OturumServisi oturum_iptal_tarihi'ni gunceller
      *    -> damga degisir -> web oturumlari cikar.
      * getAuthPassword'in KENDISI degismedigi icin login/credential dogrulamasi
      * etkilenmez; bu damga yalnizca middleware'in oturum-gecerlilik kiyaslamasinda
      * kullanilir.
      */
     public function oturumHashDamgasi()
     {
        return $this->getAuthPassword() . '|' . (string) ($this->getAttribute('oturum_iptal_tarihi') ?? '');
     }
      
     
   
}
