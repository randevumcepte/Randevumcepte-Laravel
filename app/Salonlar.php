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
        'whatsapp_promo_baslangic','whatsapp_promo_bitis','whatsapp_promo_aktif','whatsapp_promo_kapatildi',
        'whatsapp_deneme_bitis','whatsapp_kontor','whatsapp_paylasilan_salon_id',
        'cakisma_uyarisi_aktif','kara_liste_aktif','tema_config','tema_seed' ];

    protected $casts = [
        'whatsapp_aktif' => 'boolean',
        'whatsapp_baglanti_tarihi' => 'datetime',
        'whatsapp_warmup_baslangic' => 'datetime',
        'whatsapp_deneme_bitis' => 'date',
        'faturasiz_gizle' => 'boolean',
        'whatsapp_promo_aktif' => 'boolean',
        'whatsapp_promo_kapatildi' => 'boolean',
        'cakisma_uyarisi_aktif' => 'boolean',
        'kara_liste_aktif' => 'boolean',
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
            // === MODEL (2026-08) — salon-basi 60 gun deneme (whatsapp_deneme_bitis kolonu) ===
            // Her salon WhatsApp'a ilk baglandiginda 60 gun ucretsiz deneme baslar
            // (webhook.onConnected). Elle uzatilabilir. Mevcut salonlar 2026-08-31'e
            // migrate edildi (baslangic karari). Deneme bitisinden sonra kontorlu.

            // Ücretli paketler promo dışında — kendi kartı/akışı var
            $paket = $isletme->whatsapp_paket ?: 'baslangic';
            if (in_array($paket, ['pro', 'premium'])) {
                return ['promo' => false, 'iletisim' => $iletisimTel];
            }

            // Salon-ozel deneme bitisi (yoksa global 2026-08-31 fallback)
            $bitisStr = $isletme->whatsapp_deneme_bitis ?? '2026-08-31';
            $bitis = \Carbon\Carbon::parse($bitisStr)->startOfDay();
            $kalan = (int) \Carbon\Carbon::now()->startOfDay()->diffInDays($bitis, false);
            $suresiDoldu = $kalan < 0;

            // Son ay heads-up popup'i (kalan <=30 gun)
            $agustosUyari = !$suresiDoldu && $kalan <= 30;

            return [
                'promo'         => true,
                'baslangic'     => null,
                'bitis'         => $bitis->toDateString(),
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
    // Santral ozel karsilama menusu: tus -> TTS'te okunus ("biri, ikiyi ..."). 0 = menuyu tekrar dinle (rezerve).
    const SANTRAL_TUS_OKUNUS = [
        '1' => 'biri', '2' => 'ikiyi', '3' => 'üçü', '4' => 'dördü', '5' => 'beşi',
        '6' => 'altıyı', '7' => 'yediyi', '8' => 'sekizi', '9' => 'dokuzu',
    ];

    /**
     * Ham secenek listesini temizler: gecerli tus (1-9, tekil), bos olmayan etiket,
     * yalniz rakam hedef (kuyruk/dahili no). Tusa gore sirali doner.
     */
    public static function santralOzelMenuNormalize($ham)
    {
        $temiz = [];
        foreach ((array) $ham as $satir) {
            $tus = trim((string) ($satir['tus'] ?? ''));
            $etiket = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($satir['etiket'] ?? ''))));
            $hedef = preg_replace('/\D/', '', (string) ($satir['hedef'] ?? ''));
            if (!isset(self::SANTRAL_TUS_OKUNUS[$tus]) || isset($temiz[$tus]) || $etiket === '' || strlen($hedef) < 2 || strlen($hedef) > 10) {
                continue;
            }
            $temiz[$tus] = ['tus' => $tus, 'etiket' => mb_substr($etiket, 0, 60), 'hedef' => $hedef];
        }
        ksort($temiz);
        return array_values($temiz);
    }

    // Aktif degilse bos dizi; santral bu durumda standart akisa duser.
    public function santralOzelMenuSecenekleri()
    {
        if (!$this->santral_ozel_menu_aktif) {
            return [];
        }
        return self::santralOzelMenuNormalize(json_decode((string) $this->santral_ozel_menu, true));
    }

    public static function santralOzelMenuMetni(array $secenekler)
    {
        if (empty($secenekler)) {
            return '';
        }
        $parcalar = array_map(function ($s) {
            return $s['etiket'] . ' için ' . self::SANTRAL_TUS_OKUNUS[$s['tus']];
        }, $secenekler);
        return implode(', ', $parcalar) . ' tuşlayınız. Menüyü tekrar dinlemek için sıfırı tuşlayınız. Operatöre bağlanmak için lütfen bekleyiniz.';
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
