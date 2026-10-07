<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KampanyaKatilimcilari extends Model
{
   
    

    protected $table = 'kampanya_katilimcilari';
    protected $with = ['musteri'];
    
    public function musteri()
    {
         return $this->belongsTo(User::class,'user_id');
    }

    /**
     * Kupon indirimini DOGRUDAN adisyon kalem(ler)ine yazar (yuzde tipi).
     * Web/mobil tahsilat harici-indirim akisiyla BIREBIR ayni mantik: mevcut
     * indirim_tutari uzerine EKLER, kalan borctan fazla uygulamaz (adisyon
     * yanlislikla 'tam kapali'/negatif gorunmesin). Spesifik hizmet/urun/paket
     * kisiti varsa sadece o kaleme, yoksa adisyonun tum kalemlerine oransal dagitir.
     *
     * @return float Adisyona gercekten yazilan (kirpilmis) indirim tutari.
     */
    public static function adisyonaIndirimYaz(int $adisyonId, int $yuzde, int $hId = 0, int $uId = 0, int $pId = 0): float
    {
        if ($adisyonId <= 0 || $yuzde <= 0) return 0.0;

        $spesifik = ($hId || $uId || $pId);
        $qH = \App\AdisyonHizmetler::where('adisyon_id', $adisyonId)->whereNull('senet_id')->whereNull('taksitli_tahsilat_id');
        $qU = \App\AdisyonUrunler::where('adisyon_id', $adisyonId)->whereNull('senet_id')->whereNull('taksitli_tahsilat_id');
        $qP = \App\AdisyonPaketler::where('adisyon_id', $adisyonId)->whereNull('senet_id')->whereNull('taksitli_tahsilat_id');
        if ($spesifik) {
            if ($hId)      { $qH->where('hizmet_id', $hId); $qU->whereRaw('1=0'); $qP->whereRaw('1=0'); }
            elseif ($uId)  { $qU->where('urun_id', $uId);   $qH->whereRaw('1=0'); $qP->whereRaw('1=0'); }
            else           { $qP->where('paket_id', $pId);  $qH->whereRaw('1=0'); $qU->whereRaw('1=0'); }
        }
        $hizmetler = $qH->get();
        $urunler   = $qU->get();
        $paketler  = $qP->get();

        $toplamKalemFiyat = (float)($hizmetler->sum('fiyat') + $urunler->sum('fiyat') + $paketler->sum('fiyat'));
        if ($toplamKalemFiyat <= 0) return 0.0;

        $hedefIndirim  = round($toplamKalemFiyat * $yuzde / 100, 2);
        $mevcutIndirim = (float)($hizmetler->sum('indirim_tutari') + $urunler->sum('indirim_tutari') + $paketler->sum('indirim_tutari'));

        $hIds = $hizmetler->pluck('id')->all();
        $uIds = $urunler->pluck('id')->all();
        $pIds = $paketler->pluck('id')->all();
        $oncekiTahsilat = 0.0;
        if (!empty($hIds)) $oncekiTahsilat += (float) \App\TahsilatHizmetler::whereIn('adisyon_hizmet_id', $hIds)->sum('tutar');
        if (!empty($uIds)) $oncekiTahsilat += (float) \App\TahsilatUrunler::whereIn('adisyon_urun_id', $uIds)->sum('tutar');
        if (!empty($pIds)) $oncekiTahsilat += (float) \App\TahsilatPaketler::whereIn('adisyon_paket_id', $pIds)->sum('tutar');

        $kalanBorc    = $toplamKalemFiyat - $mevcutIndirim - $oncekiTahsilat;
        $uygulanacak  = max(0.0, min($hedefIndirim, $kalanBorc));
        if ($uygulanacak <= 0) return 0.0;

        foreach ($hizmetler as $_h) {
            $_pay = round(((float)$_h->fiyat / $toplamKalemFiyat) * $uygulanacak, 2);
            $_h->indirim_tutari = (float)($_h->indirim_tutari ?? 0) + $_pay;
            $_h->save();
        }
        foreach ($urunler as $_u) {
            $_pay = round(((float)$_u->fiyat / $toplamKalemFiyat) * $uygulanacak, 2);
            $_u->indirim_tutari = (float)($_u->indirim_tutari ?? 0) + $_pay;
            $_u->save();
        }
        foreach ($paketler as $_p) {
            $_pay = round(((float)$_p->fiyat / $toplamKalemFiyat) * $uygulanacak, 2);
            $_p->indirim_tutari = (float)($_p->indirim_tutari ?? 0) + $_pay;
            $_p->save();
        }

        return $uygulanacak;
    }
}