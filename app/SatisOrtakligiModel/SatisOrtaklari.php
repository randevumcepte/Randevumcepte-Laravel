<?php

namespace App\SatisOrtakligiModel;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
 use Laravel\Passport\HasApiTokens;
 use Spatie\Permission\Traits\HasRoles;

class SatisOrtaklari extends Authenticatable
{
   use HasApiTokens, Notifiable, HasRoles;
    protected $table = 'satis_ortaklari';
    protected $guard = 'satisortakligi';

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
   
     
   
}
