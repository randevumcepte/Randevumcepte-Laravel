<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalonGorselleri extends Model
{
   
    protected $fillable = ['salon_gorseli','kapak_fotografi','salon_id'];

    protected $table = 'salon_gorselleri';
    
    protected $with =  'salonlar';

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class);
    }
}
