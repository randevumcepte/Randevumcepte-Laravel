<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hizmet_Kategorisi extends Model
{
	protected $table = 'hizmet_kategorisi';
    protected $fillable = ['hizmet_kategorisi_adi'];
    //protected $with = ['hizmetler'];
    public function renk()
    {
        return $this->belongsTo(RenkDuzenleri::class,'renk');
    }
    public function hizmetler()
    {
        return $this->hasMany(Hizmetler::class,'hizmet_kategori_id');
    }
}
