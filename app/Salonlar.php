<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Salonlar extends Model
{
	protected $table = 'salonlar';
    protected $fillable = [
        'salon_adi', 'adres' , 'satis_ortagi_id','pasif_ortak_id','il_id','ilce_id','telefon_1','telefon_2','telefon_3' ,'yetkili_adi','yetkili_telefon','hesap_acildi','aciklama',
        'whatsapp_aktif','whatsapp_durum','whatsapp_numara','whatsapp_baglanti_tarihi','whatsapp_gunluk_limit','whatsapp_warmup_baslangic','whatsapp_son_hata',
        'whatsapp_saglayici','cloud_api_phone_number_id','cloud_api_token',
        'cloud_api_template_1gun','cloud_api_template_yaklasan','cloud_api_template_iptal','cloud_api_template_guncelleme','cloud_api_template_dil','faturasiz_gizle',
        'whatsapp_promo_baslangic','whatsapp_promo_bitis','whatsapp_promo_aktif','whatsapp_promo_kapatildi' ];

    protected $casts = [
        'whatsapp_aktif' => 'boolean',
        'whatsapp_baglanti_tarihi' => 'datetime',
        'whatsapp_warmup_baslangic' => 'datetime',
        'faturasiz_gizle' => 'boolean',
        'whatsapp_promo_aktif' => 'boolean',
        'whatsapp_promo_kapatildi' => 'boolean',
    ];

    public function setSalonAdiAttribute($value)
    {
        $this->attributes['salon_adi'] = \App\Helpers\Metin::basHarfBuyut($value);
    }

    /**
     * WhatsApp "2 Ay Ücretsiz" tanıtım durumunu hesaplar.
     * Ücretli (pro/premium) salonlar promo kapsamı dışındadır.
     * İlk çağrıda promo başlatılmamışsa otomatik başlatır (başlangıç=bugün, bitiş=+2 ay).
     * Hesabım ekranı ve panel layout'undaki uyarı popup'ı bu metodu kullanır.
     */
    public static function whatsappPromoBilgisi($isletme)
    {
        if (!$isletme) {
            return ['promo' => false];
        }

        // İletişim numarası tek noktadan
        $iletisimTel = '05412948144';

        try {
            // Ücretli paketler promo dışında — onların kendi kartı/akışı var
            $paket = $isletme->whatsapp_paket ?: 'baslangic';
            if (in_array($paket, ['pro', 'premium'])) {
                return ['promo' => false, 'iletisim' => $iletisimTel];
            }

            // Kolon yoksa (migration çalışmamışsa) sessizce çık (request içi tek kontrol)
            static $kolonVar = null;
            if ($kolonVar === null) {
                $kolonVar = \Schema::hasColumn('salonlar', 'whatsapp_promo_baslangic');
            }
            if (!$kolonVar) {
                return ['promo' => false, 'iletisim' => $iletisimTel];
            }

            // Lazy init — promo henüz başlatılmamışsa bugünden başlat
            if (empty($isletme->whatsapp_promo_baslangic)) {
                $bas = \Carbon\Carbon::now()->startOfDay();
                $bit = (clone $bas)->addMonths(2);
                \DB::table('salonlar')->where('id', $isletme->id)->update([
                    'whatsapp_promo_baslangic' => $bas->toDateString(),
                    'whatsapp_promo_bitis'     => $bit->toDateString(),
                    'whatsapp_promo_aktif'     => 1,
                    'whatsapp_promo_kapatildi' => 0,
                ]);
                $isletme->whatsapp_promo_baslangic = $bas->toDateString();
                $isletme->whatsapp_promo_bitis     = $bit->toDateString();
                $isletme->whatsapp_promo_aktif     = 1;
                $isletme->whatsapp_promo_kapatildi = 0;
            }

            $bitis = \Carbon\Carbon::parse($isletme->whatsapp_promo_bitis)->startOfDay();
            $kalan = (int) \Carbon\Carbon::now()->startOfDay()->diffInDays($bitis, false);
            $suresiDoldu = $kalan < 0 || (int) ($isletme->whatsapp_promo_kapatildi ?? 0) === 1;
            $aktif = !$suresiDoldu && (int) ($isletme->whatsapp_promo_aktif ?? 1) === 1;

            return [
                'promo'       => true,
                'baslangic'   => $isletme->whatsapp_promo_baslangic,
                'bitis'       => $isletme->whatsapp_promo_bitis,
                'kalan_gun'   => max(0, $kalan),
                'suresi_doldu'=> $suresiDoldu,
                'aktif'       => $aktif,
                // Son 5 gün uyarısı (süre dolmadan)
                'uyari'       => $aktif && $kalan >= 0 && $kalan <= 5,
                'iletisim'    => $iletisimTel,
            ];
        } catch (\Exception $e) {
            return ['promo' => false, 'iletisim' => $iletisimTel];
        }
    }
    //protected $with =  ['il', 'ilce', 'salon_turu','calisma_saatleri','mola_saatleri'];
    public function personeller()
    {
        return $this->hasMany(Personeller::class,'salon_id');
    }
    
    public function il()
    {
        return $this->belongsTo(Iller::class,'il_id');
    }

    public function ilce()
    {
        return $this->belongsTo(\App\Ilceler::class,'ilce_id');
    }

    public function salon_turu()
    {
        return $this->belongsTo(\App\SalonTuru::class,'salon_turu_id');
    }



   
}
