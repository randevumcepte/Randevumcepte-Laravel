<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KampanyaYonetimi extends Model
{
   
    

    protected $table = 'kampanya_yonetimi';
    protected $with = ['kampanya_katilimcilari','salon'];
    public function kampanya_katilimcilari()
    {
        return $this->hasMany(KampanyaKatilimcilari::class,'kampanya_id');
    }
    public function salon()
    {
         return $this->belongsTo(Salonlar::class,'salon_id');
    }

    /**
     * Musterinin en son GELDIGI randevudan (durum=1 & randevuya_geldi=true) bu yana
     * gecen gun sayisi = "kac gundur gelmedi" (yokluk gunu). Hic gelmemis / bugun
     * gelmisse makul varsayilan (30).
     */
    public static function musteriYoklukGunu($userId, $salonId)
    {
        // ATTENDED = durum=1 & randevuya_geldi=1 & tarih GECMIS/bugun. En son geleni al.
        $sonGeldigi = \App\Randevular::where('user_id', $userId)
            ->where('salon_id', $salonId)
            ->where('durum', 1)
            ->where('randevuya_geldi', 1)
            ->whereDate('tarih', '<=', \Carbon\Carbon::today())
            ->orderBy('tarih', 'desc')
            ->value('tarih');

        if ($sonGeldigi) {
            // ONEMLI: kayit BULUNDU -> gercek gun sayisini dondur. Eskiden $g==0 (bugun
            // geldi) durumunda 'return 30' fallback'ine dusuyordu -> "durum=1 & geldi=1
            // olmasina ragmen 30" bug'i. Bugun geldiyse en az 1 dondur, ASLA 30'a dusme.
            $g = \Carbon\Carbon::parse($sonGeldigi)->startOfDay()->diffInDays(\Carbon\Carbon::today());
            \Log::info("[KAMPANYA-GUN] user={$userId} salon={$salonId} son_geldigi={$sonGeldigi} gun={$g}");
            return max($g, 1);
        }

        \Log::info("[KAMPANYA-GUN] user={$userId} salon={$salonId} durum=1&geldi=1 randevu YOK -> fallback 30");
        return 30;
    }

    /**
     * Kampanya mesajindaki KISIYE OZEL placeholder'lari ({müşteri}, {gün}) gonderim
     * aninda o katilimciya gore cozer. Diger placeholder'lar ({işletmeden}, {indirim},
     * {ürün_hizmet_paket}) olusturmada zaten cozuldugu icin burada dokunulmaz.
     */
    public static function kisisellestir($mesaj, $userId, $salonId)
    {
        $ad  = trim((string) \App\User::where('id', $userId)->value('name'));
        $gun = self::musteriYoklukGunu($userId, $salonId);
        $mesaj = str_replace('{müşteri}', $ad, (string) $mesaj);
        $mesaj = str_replace('{gün}', $gun, $mesaj);
        return $mesaj;
    }

    /**
     * TTS okunus normalize: 2+ harfli TAMAMEN BUYUK kelimeleri ("ORBEY") bas-harfi-buyuk
     * forma ("Orbey") cevirir; yoksa Google TTS harf harf okur (O-R-B-E-Y). santral
     * gttscache.php seslendirmeMetni() ve sesli-asistan.php ile AYNI mantik. Turkce
     * kucuk harf donusumu (I->ı, İ->i) korunur.
     */
    public static function okunusNormalize($s)
    {
        return preg_replace_callback('/[A-ZÇĞİÖŞÜ]{2,}/u', function ($m) {
            $w = $m[0];
            $kucuk = mb_strtolower(str_replace(['I', 'İ'], ['ı', 'i'], $w), 'UTF-8');
            return mb_substr($w, 0, 1, 'UTF-8') . mb_substr($kucuk, 1, null, 'UTF-8');
        }, (string) $s);
    }

    /**
     * "Yeni reklam kampanyasi olusturuldu" push'u — salonun personel cihazlarina (web panel +
     * v2 outbox 'kampanya_olustur' AYNI kod). Kayit akisinin kritik parcasi degil: hata loglanir.
     */
    public static function olusturmaBildirimiGonder(self $kampanya)
    {
        try {
            $controller = app(\App\Http\Controllers\BildirimController::class);
            $personeller = Personeller::where('salon_id', $kampanya->salon_id)->pluck('id')->toArray();
            $bildirimKimlikleri = BildirimKimlikleri::whereIn('isletme_yetkili_id', $personeller)->whereNotNull('bildirim_id')
                ->where('salon_id', $kampanya->salon_id)->get();

            foreach ($bildirimKimlikleri as $token) {
                $data = [
                    'category' => 'reklam',
                    'buttons' => json_encode([]),
                    'userInfo' => '',
                    'salonId' => $kampanya->salon_id,
                    'bildirimlereGitYonetici' => "1",
                    'kullaniciRolu' => Personeller::where('id', $token->isletme_yetkili_id)->value('role_id'),
                    'type' => '',
                ];
                $controller->bildirimGonder(
                    'app/firebase/randevumcepte-uygulamala-5ff4d-8a85c43832c1.json',
                    $token->bildirim_id,
                    "Yeni Reklam Kampanyası Oluşturma",
                    "Sisteme ".$kampanya->paket_isim." ile ilgili reklam kaydını başarıyla olşturdunuz.",
                    $data,
                    $kampanya->salon_id,
                    null,
                    '/public/yeni_panel/vendors/images/eczane24-icon.jpg',
                    'kampanya',
                    null,
                    null,
                    null,
                    null,
                    $token->isletme_yetkili_id,
                    $kampanya->id
                );
            }
        } catch (\Throwable $e) {
            \Log::error('[KAMPANYA-KAYDET-HATA] push bildirim basarisiz (kayit korundu)', [
                'kampanya_id' => $kampanya->id, 'hata' => $e->getMessage(),
            ]);
        }
    }
}