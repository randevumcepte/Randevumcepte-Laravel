<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarkifelekOdulleri extends Model
{
    protected $table = 'carkifelek_odulleri';

    protected $fillable = [
        'log_id', 'kaynak_reklam_id', 'salon_id', 'user_id', 'kod', 'tip',
        'deger', 'indirim_tipi', 'hizmet_id', 'urun_id', 'baslik', 'kullanildi', 'kullanim_tarihi',
        'gecerlilik_tarihi', 'gecerli_salonlar',
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

    /**
     * Kuponun geçerli olduğu şube (salon_id) listesi.
     * gecerli_salonlar doluysa o grup; boşsa (eski kayıt) kendi salonunun
     * kardeş-şube grubu fallback'i (owner geneli). Redemption bu listeyi kullanır.
     */
    public function gecerliSubeler()
    {
        $liste = \App\Personeller::salonIdListesiCoz($this->gecerli_salonlar);
        if (!empty($liste)) return $liste;
        return \App\Personeller::kardesSalonIdler($this->salon_id);
    }
}
