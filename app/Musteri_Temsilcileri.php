<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Cache;
class Musteri_Temsilcileri extends Authenticatable
{
    use Notifiable;
    protected $table = 'musteri_temsilcileri';
    protected $guard = 'musteri-temsilcisi';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
          'email', 'password',
    ];

    /**
     * The attributes that should be dden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function aktif()
    {
        return Cache::has('user-is-online-' . $this->id);
    }
   
}
