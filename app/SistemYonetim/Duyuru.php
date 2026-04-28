<?php

namespace App\SistemYonetim;

use Illuminate\Database\Eloquent\Model;

class Duyuru extends Model
{
    protected $table = 'sistemyonetim_duyurular';
    protected $fillable = [
        'baslik', 'icerik', 'tip', 'hedef_tipi', 'hedef_ids',
        'baslangic_tarihi', 'bitis_tarihi', 'aktif', 'sticky',
        'cta_metin', 'cta_link',
        'olusturan_user_id', 'olusturan_user_name',
    ];

    public function hedefIdsArray()
    {
        if (!$this->hedef_ids) return [];
        $d = json_decode($this->hedef_ids, true);
        return is_array($d) ? $d : [];
    }

    /**
     * Verilen salon icin gecerli mi?
     */
    public function salonIcinGecerli($salonId, $ilId = null)
    {
        if (!$this->aktif) return false;
        $now = now();
        if ($this->baslangic_tarihi && $this->baslangic_tarihi > $now) return false;
        if ($this->bitis_tarihi && $this->bitis_tarihi < $now) return false;

        if ($this->hedef_tipi === 'hepsi') return true;

        $hedef = $this->hedefIdsArray();
        if ($this->hedef_tipi === 'secili') return in_array((int) $salonId, array_map('intval', $hedef), true);
        if ($this->hedef_tipi === 'il' && $ilId) return in_array((int) $ilId, array_map('intval', $hedef), true);
        return false;
    }
}
