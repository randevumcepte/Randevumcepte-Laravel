<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSListeleri extends Model
{
   
    protected $fillable = ['sms_liste_adi','user_id','sms_liste_bilgiler_id'];

    protected $table = 'sms_listeleri';
    
    protected $with =  ['isletmeyetkilileri','sms_liste_bilgiler'];

    public function isletmeyetkilileri()
    {
        return $this->belongsTo(IsletmeYetkilileri::class,'user_id');
    }
    public function sms_liste_bilgiler()
    {
    	return $this->belongsTo(SMSListeBilgiler::class,'sms_liste_bilgiler_id');
    }

    
}