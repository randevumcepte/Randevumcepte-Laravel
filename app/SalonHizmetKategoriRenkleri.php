<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalonHizmetKategoriRenkleri extends Model
{
     
    protected $table = 'salon_hizmet_kategori_renkleri';
    protected $with =  ['salon','hizmet_kategorisi','renkduzeni'];
    
   
 
    public function salon(){
    	return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function hizmet_kategorisi(){
        return $this->belongsTo(Hizmet_Kategorisi::class,'hizmet_kategori_id');
        
    }
    public function renkduzeni(){
        return $this->belongsTo(RenkDuzenleri::class,'renk_id');
    }
    
    
}
