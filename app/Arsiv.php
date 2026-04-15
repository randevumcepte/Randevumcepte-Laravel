<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class Arsiv extends Model{

	protected $table ='arsiv';
	protected $with=['salonlar','sube','personel','musteri', 'personel','form'];

	public function salonlar(){
		return $this->belongsTo(Salonlar::class,'salon_id');
	}

	public function musteri(){
		return $this->belongsTo(User::class,'user_id');
	}
	public function sube(){
       return $this->belongsTo(Subeler::class,'sube_id');
    }
    public function personel()
    {
        return $this->belongsTo(Personeller::class,'personel_id');
    }
   	public function form()
   	{
   		return $this->belongsTo(FormTaslaklari::class,'form_id');
   	}
   	public function hizmet()
   	{
   		return $this->belongsTo(Hizmetler::class,'hizmet_id');
   	}
   
   
   

}