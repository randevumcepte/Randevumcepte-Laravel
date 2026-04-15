<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSListeBilgiler extends Model
{
   
    protected $fillable = ['sms_listeleri_id','ad_soyad','cep_telefon','sms_kampanya_karaliste', 'sms_kampanya_karaliste_nedeni'];

    protected $table = 'sms_liste_bilgiler';
    
    

   
   

    
}