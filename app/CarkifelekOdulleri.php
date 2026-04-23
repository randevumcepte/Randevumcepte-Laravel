<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarkifelekOdulleri extends Model
{
    protected $table = 'carkifelek_odulleri';

    protected $fillable = [
        'log_id', 'salon_id', 'user_id', 'kod', 'tip',
        'deger', 'baslik', 'kullanildi', 'kullanim_tarihi',
        'gecerlilik_tarihi',
    ];

    protected $casts = [
        'deger'             => 'float',
        'kullanildi'        => 'integer',
        'kullanim_tarihi'   => 'datetime',
        'gecerlilik_tarihi' => 'date',
    ];

    public function isGecerli()
    {
        if ($this->kullanildi) return false;
        if (!$this->gecerlilik_tarihi) return true;
        return $this->gecerlilik_tarihi->isFuture() || $this->gecerlilik_tarihi->isToday();
    }
}
