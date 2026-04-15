<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ilceler extends Model
{
	 protected $fillable = ['il_id','ilce_adi'];

    protected $table = 'ilce';
    
    protected $with =  'il';

    public function il()
    {
        return $this->belongsTo(Iller::class);
    }
}
