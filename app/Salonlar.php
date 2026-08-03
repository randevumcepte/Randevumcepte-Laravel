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
            // === YENİ MODEL (2026-07) — salon-başı 2 ay promo KALDIRILDI ===
            // Herkes 31.08.2026'ya kadar ücretsiz (global tek tarih); 1 Eylül'de
            // kontörlü sisteme geçiş (bkz. KontorServisi + whatsapp.blade kontör
            // uyarıları). Bu metod artık SADECE "Hesabım > Aldığım Hizmetler"
            // kartını besler; ESKİ "son 5 gün ara/öde" popup'ı ÜRETMEZ
            // ('uyari' her zaman false). whatsapp_promo_* kolonları kullanılmıyor.

            // Ücretli paketler zaten promo dışında — kendi kartı/akışı var
            $paket = $isletme->whatsapp_paket ?: 'baslangic';
            if (in_array($paket, ['pro', 'premium'])) {
                return ['promo' => false, 'iletisim' => $iletisimTel];
            }

            $bitis = \Carbon\Carbon::parse('2026-08-31')->startOfDay();
            $kalan = (int) \Carbon\Carbon::now()->startOfDay()->diffInDays($bitis, false);
            $suresiDoldu = $kalan < 0; // 1 Eylül+ => kontörlü dönem başladı

            // Ağustos "kontör al" heads-up popup'ı: 1–31 Ağustos arası günde 1 kez
            // gösterilir (layout). bitis=31 Ağustos → Ağustos 1'de kalan=30.
            // 1 Eylül+ olunca (suresiDoldu) kapanır; yerini kontör bakiyesi
            // popup'ları alır (whatsapp.blade). Ücretli paketler zaten yukarıda elendi.
            $agustosUyari = !$suresiDoldu && $kalan <= 30;

            return [
                'promo'         => true,
                'baslangic'     => null,
                'bitis'         => '2026-08-31',
                'kalan_gun'     => max(0, $kalan),
                'suresi_doldu'  => $suresiDoldu,
                'aktif'         => !$suresiDoldu,
                'uyari'         => false, // ESKİ 5-gün ara/öde popup'ı KAPALI
                'agustos_uyari' => $agustosUyari,
                'iletisim'      => $iletisimTel,
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
