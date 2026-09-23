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
        // ATTENDED = randevuya_geldi truthy + tarih GECMIS. durum=1 filtresi KALDIRILDI:
        // tamamlanan/gecmis randevularin durum'u 1 olmayabilir (talep/onay/tamam farkli
        // degerler) ve o filtre gecmis "geldi" kayitlarini eliyor -> hep 30 fallback donuyordu.
        $sonGeldigi = \App\Randevular::where('user_id', $userId)
            ->where('salon_id', $salonId)
            ->where('randevuya_geldi', 1)
            ->whereDate('tarih', '<=', \Carbon\Carbon::today())
            ->orderBy('tarih', 'desc')
            ->value('tarih');

        if ($sonGeldigi) {
            $g = \Carbon\Carbon::parse($sonGeldigi)->startOfDay()->diffInDays(\Carbon\Carbon::today());
            \Log::info("[KAMPANYA-GUN] user={$userId} salon={$salonId} son_geldigi={$sonGeldigi} gun={$g}");
            return $g > 0 ? $g : 1; // bugun geldiyse 1 ("0 gundur" demesin)
        }

        \Log::info("[KAMPANYA-GUN] user={$userId} salon={$salonId} gelmis randevu YOK -> fallback 30");
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
}