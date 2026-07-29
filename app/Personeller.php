<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Personeller extends Model
{
    
    protected $table = 'salon_personelleri';
    //protected $with = ['salonlar','randevuhizmetler'];
    protected $fillable = [
        'personel_adi', 'salon_id' ,'yetkili_id','cep_telefon',
        'uzmanlik','aciklama','yillik_tecrube','instagram',
        'maas','hizmet_prim_yuzde','urun_prim_yuzde','paket_prim_yuzde',
        'hizmet_prim_detayli','urun_prim_detayli','paket_prim_detayli',
        'unvan','cinsiyet','arsivli'
    ];
   
    public function setPersonelAdiAttribute($value)
    {
        $this->attributes['personel_adi'] = \App\Helpers\Metin::basHarfBuyut($value);
    }

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function randevuhizmetler(){
    	return $this->belongsTo(RandevuHizmetler::class,'personel_id');
    }
    public function sube()
    {
        return $this->belongsTo(Subeler::class,'sube_id');
    }
    public function hesap_turu()
    {
        return $this->belongsTo(YetkiliHesapTurleri::class,'hesap_turu_id');
    }
    public function trenk()
    {
        return $this->belongsTo(RenkDuzenleri::class,'renk');
    }

    /**
     * TÜM işletmelere hükmeden özel/sistem hesapları (yetkili_id).
     * Bunlar gerçek bir "işletme sahibi" değildir; her salonda personel satırı
     * bulunabildiğinden kardeş-şube gruplamasında ASLA kullanılmazlar. Aksi halde
     * bu hesabın altındaki tüm salonlar birbirine "kardeş" sayılır ve bir işletmenin
     * ödülü alakasız başka bir işletmede geçerli hale gelirdi (izinsiz sızıntı).
     */
    const SISTEM_YETKILI_IDLER = [3];

    /**
     * Aynı hesap sahibine (yetkili_id) bağlı kardeş şubelerin salon_id listesi.
     *
     * Çarkıfelek gibi "aynı işletmenin tüm şubelerinde ortak" davranması gereken
     * özellikler için kullanılır: bir şubede kaydedilen çark ilişkili şubelere de
     * yazılır, kazanılan kupon/ödül SADECE aynı sahibin şubelerinde geçerli olur.
     *
     * KORUMA (izolasyon): Dönen liste her zaman tek bir gerçek sahibin şubeleriyle
     * sınırlıdır; bu yüzden `whereIn('salon_id', kardesSalonIdler($x))` bir ödülün
     * alakasız başka bir işletmede kullanılmasını yapısal olarak imkânsız kılar.
     *   - Süper/sistem hesapları (SISTEM_YETKILI_IDLER) hem grup anahtarı olarak
     *     kullanılmaz hem de bir salonun gerçek sahibi çözülürken dışlanır.
     *   - Sahip çözülemezse / tek şube ise YALNIZCA verilen salon döner (fail-closed).
     *   - salonId geçersizse boş döner → whereIn hiçbir kayda eşleşmez (fail-closed).
     */
    public static function kardesSalonIdler($salonId)
    {
        $salonId = (int) $salonId;
        if ($salonId <= 0) return [];

        // Salonun GERÇEK sahibi (sistem/süper hesaplar hariç).
        $yetkiliId = self::where('salon_id', $salonId)
            ->whereNotNull('yetkili_id')
            ->whereNotIn('yetkili_id', self::SISTEM_YETKILI_IDLER)
            ->value('yetkili_id');

        // Sahip yok / yalnızca sistem hesabına bağlı → tek salona kilitle (sızıntı yok).
        if (!$yetkiliId) {
            return [$salonId];
        }

        $ids = self::where('yetkili_id', $yetkiliId)
            ->whereNotIn('yetkili_id', self::SISTEM_YETKILI_IDLER)
            ->whereNotNull('salon_id')
            ->distinct()
            ->pluck('salon_id')
            ->map(function ($v) { return (int) $v; })
            ->filter(function ($v) { return $v > 0; })
            ->toArray();

        // Kaynak salon her zaman dahil (güvenli alt sınır).
        if (!in_array($salonId, $ids, true)) {
            $ids[] = $salonId;
        }

        return array_values(array_unique($ids));
    }

}
