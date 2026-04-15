<?php

namespace App\SatisOrtakligiModel;


use Illuminate\Database\Eloquent\Model;


class SatisOrtagiOdemeTalepleri extends Model
{
    
    protected $table = 'satis_ortagi_odeme_talepleri';
   	protected $with = ['satis_ortagi','satis_ortagi_hakedis_odeme_durumu','banka'];

    public function satis_ortagi(){
        return $this->belongsTo(SatisOrtaklari::class,'satis_ortagi_id');
    }
    public function satis_ortagi_hakedis_odeme_durumu(){
        return $this->belongsTo(SatisOrtagiHakedisOdemeDurumu::class,'satis_ortagi_hakedis_odeme_durumu_id');
    }
    public function banka(){
    	return $this->belongsTo(Bankalar::class,'banka_id');
    }
 
   
     
   
}
