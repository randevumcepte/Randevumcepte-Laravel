<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Iller extends Model
{
	 protected $fillable = ['ulke_id','aktif','il_adi'];

    protected $table = 'il';
    
    protected $with =  'ulke';

    public function ulke()
    {
        return $this->belongsTo(Ulkeler::class);
    }
}
