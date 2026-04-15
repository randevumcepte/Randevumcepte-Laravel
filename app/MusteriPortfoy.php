<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MusteriPortfoy extends Model
{
   
    protected $fillable = ['user_id','salon_id','kara_liste','musteri_tipi','aktif','musteri_tipi'];

    protected $table = 'musteri_portfoy';
    
    protected $with =  ['users'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function users()
    {
    	return $this->belongsTo(User::class,'user_id');
    }


    
}