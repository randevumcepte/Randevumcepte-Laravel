<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\SatisOrtakligiModel\SatisOrtaklari;
class Bildirimler extends Model
{
    protected $table = 'bildirimler';
    protected $with =  ['user','personel','randevu','arsiv','satis_ortagi'];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function randevu()
    {
        return $this->belongsTo(Randevular::class,'randevu_id');
    }
    public function personel(){
    	return $this->belongsTo(Personeller::class,'personel_id');
    }
    public function salon()
    {
    	return $this->belongsTo(Salonlar::class,'salon_id');
    }
     public function arsiv()
    {
        return $this->belongsTo(Arsiv::class,'arsiv_id');
    }
    public function satis_ortagi()
    {
        return $this->belongsTo(SatisOrtaklari::class,'satis_ortagi_id');
    }
}
