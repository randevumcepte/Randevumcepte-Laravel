<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordResets extends Model
{
   
    protected $fillable = ['email','cep_telefon','token','dogrulama_kodu'];

    protected $table = 'password_resets';
     
    
}
