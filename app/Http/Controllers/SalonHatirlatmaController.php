<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Salonlar;
use App\Personeller;
use App\PersonelMaasOdemesi;
use App\Randevular;
use App\MusteriPortfoy;

class SalonHatirlatmaController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::guard('isletmeyonetim')->check() && !Auth::guard('satisortakligi')->check()) {
                return response()->json(['hatirlatmalar' => [], 'sayi' => 0], 401);
            }
            return $next($request);
        });
    }

    private function aktifSalonId(Request $request)
    {
        if (Auth::guard('satisortakligi')->check()) {
            return 15;
        }
        if ($request->filled('sube')) {
            return (int) $request->sube;
        }
        $subeler = Auth::guard('isletmeyonetim')->user()
            ->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();

        $host = $_SERVER['HTTP_HOST'] ?? '';
        $merkeziHostlar = ['app.randevumcepte.com.tr', 'apptest.randevumcepte.com.tr', 'demoapp.randevumcepte.com.tr', 'randevu.randevumcepte.com.tr'];
        if (!in_array($host, $merkeziHostlar)) {
            $domSalon = Salonlar::where('domain', $host)->value('id');
            if ($domSalon && in_array($domSalon, $subeler)) return (int) $domSalon;
        }
        return $subeler[0] ?? 0;
    }

    public function feed(Request $request)
    {
        $salonId = $this->aktifSalonId($request);
        if (!$salonId) {
            return response()->json(['hatirlatmalar' => [], 'sayi' => 0]);
        }

        $cacheKey = 'salon_hatirlatma.' . $salonId;
        if (filter_var($request->input('refresh'), FILTER_VALIDATE_BOOLEAN)) { // Request::boolean() bu surumde yok
            Cache::forget($cacheKey);
        }
        // DIKKAT: Laravel 5.6'da remember TTL'i DAKIKA cinsindendir. Eskiden 15 yaziyordu =
        // 15 DAKIKA cache -> "hep ayni veri" sikayeti buradan. Carbon ile 20 saniyeye cekildi.
        $hatirlatmalar = Cache::remember($cacheKey, now()->addSeconds(20), function () use ($salonId) {
            return $this->topla($salonId);
        });

        // Arama randevulari KULLANICIYA OZEL -> salon cache'ine konamaz (baska personele sizar).
        // Bu yuzden cache disinda, giris yapan kullaniciya gore taze hesaplanip eklenir.
        try {
            $aramaRandevulari = $this->aramaRandevulari($salonId);
        } catch (\Throwable $e) {
            \Log::warning('SalonHatirlatma [aramaRandevulari] hata: ' . $e->getMessage());
            $aramaRandevulari = [];
        }
        if (!empty($aramaRandevulari)) {
            $hatirlatmalar = array_merge($aramaRandevulari, $hatirlatmalar);
            usort($hatirlatmalar, function ($a, $b) {
                return ($b['oncelik'] ?? 1) <=> ($a['oncelik'] ?? 1);
            });
        }

        return response()->json([
            'sayi'          => count($hatirlatmalar),
            'hatirlatmalar' => $hatirlatmalar,
        ]);
    }

    /* -----------------------------------------------------------------
     * Cagri Merkezi: zamani gelen ARAMA RANDEVULARI (kullaniciya ozel).
     * "X müşteriniz aramanızı bekliyor" popup'i. Rol 5 ise sadece kendine
     * atanan listeler; yonetici ise salonun tum arama randevulari.
     * Onceki notlar + son ses kaydi popup'a eklenir (sureci takip icin).
     * ----------------------------------------------------------------- */
    private function aramaRandevulari($salonId)
    {
        if (!$this->tabloVarMi('aranacak_musteriler') || !$this->tabloVarMi('arama_listesi')) return [];

        $authUser = Auth::guard('isletmeyonetim')->user();
        if (!$authUser) return [];
        $authId = $authUser->id;

        // Arama randevusu popup'i SADECE listenin atandigi personele gider (role bakmadan).
        // Boylece her kullanici yalnizca kendi personel kaydina atanan randevulari gorur.
        $personelId = DB::table('salon_personelleri')->where('yetkili_id', $authId)->where('salon_id', $salonId)->value('id');
        if (!$personelId) return [];

        // Maskeleme icin rol (rol 5 ise musteri soyadi maskelenir)
        $rol = DB::table('model_has_roles')->where('model_id', $authId)->where('salon_id', $salonId)->value('role_id');

        $bugun = date('Y-m-d');
        $simdiSaat = date('H:i:s');

        $kayitlar = DB::table('aranacak_musteriler as am')
            ->join('arama_listesi as al', 'al.id', '=', 'am.arama_id')
            ->join('users as u', 'u.id', '=', 'am.user_id')
            ->where('al.salon_id', $salonId)
            ->where('al.personel_id', $personelId)
            ->where('am.durum', 3)         // 3 = Arama Randevusu
            ->where('am.tarih', $bugun)
            ->whereRaw('am.saat <= ?', [$simdiSaat])
            ->select('am.id', 'am.user_id', 'am.saat', 'u.name', 'al.personel_id')
            ->orderBy('am.saat', 'asc')->limit(20)->get();

        $gorusmeNotuVar = $this->tabloVarMi('gorusme_notlari');
        $items = [];
        foreach ($kayitlar as $k) {
            $ad = ($rol == 5) ? $this->adSoyadMaskele($k->name) : $k->name;

            $oncekiNot = '';
            $sonSes = '';
            if ($gorusmeNotuVar) {
                $notlar = DB::table('gorusme_notlari')->where('aranacak_musteri_id', $k->id)
                    ->orderBy('id', 'desc')->limit(5)->get();
                foreach ($notlar as $n) {
                    if (!$oncekiNot && !empty($n->not)) $oncekiNot = $n->not;
                    if (!$sonSes && !empty($n->ses_kaydi)) $sonSes = 'https://voicerecords.randevumcepte.com.tr' . $n->ses_kaydi;
                }
            }

            $items[] = [
                'id'                  => 'arama_randevusu_' . $k->id,
                'tip'                 => 'arama_randevusu',
                'oncelik'             => 5,
                'tema'                => 'mavi-cinglir',
                'ikon'                => 'fa-phone',
                'emoji'               => '📞',
                'baslik'              => 'Arama Randevusu',
                'mesaj'               => $ad . ' isimli müşteriniz aramanızı bekliyor.',
                'altMesaj'            => substr($k->saat, 0, 5) . ' için planlandı.' . ($oncekiNot ? ' Önceki not: ' . mb_substr($oncekiNot, 0, 80) : ''),
                'cta_text'            => 'Hemen Ara',
                'aksiyon'             => 'arama_baslat',
                'aranacak_musteri_id' => $k->id,
                'son_ses'             => $sonSes,
                'link'                => '/isletmeyonetim/arama-listelerim?sube=' . $salonId,
                'sayac'               => 1,
            ];
        }
        return $items;
    }

    private function topla($salonId)
    {
        $list = [];
        $tetikleyiciler = [
            'geldiGelmediIsaretlenmemis',
            'yeniMusteriler',
            'personelOdemesi',
            'bekleyenRandevular',
            'dogumGunuBugun',
            'acikAdisyonEski',
            'gecikenAlacaklar',
            'dusukSmsBakiyesi',
        ];
        foreach ($tetikleyiciler as $met) {
            try {
                $h = $this->{$met}($salonId);
                if ($h) $list[] = $h;
            } catch (\Throwable $e) {
                \Log::warning('SalonHatirlatma ['.$met.'] hata: '.$e->getMessage());
            }
        }
        usort($list, function ($a, $b) {
            return ($b['oncelik'] ?? 1) <=> ($a['oncelik'] ?? 1);
        });
        return $list;
    }

    /* -----------------------------------------------------------------
     * 1) Son saatlerde randevuya GELDİ/GELMEDİ işaretlenmemiş kayıtlar
     * ----------------------------------------------------------------- */
    private function geldiGelmediIsaretlenmemis($salonId)
    {
        if (!$this->kolonVarMi('randevular', 'randevuya_geldi')) return null;

        // SADECE BUGUN + 15 dk tolerans: bugunun onayli (durum=1) randevulari icinden,
        // saati su andan en az 15 dk once gecmis VE hala geldi/gelmedi isaretlenmemis (NULL) olanlar.
        // (Eskiden son 4 saatlik pencere vardi; sabah gecen randevu ogleden sonra karttan kayboluyordu.)
        $bugun = date('Y-m-d');
        $esik  = date('Y-m-d H:i:s', strtotime('-15 minutes'));

        $sayi = DB::table('randevular')
            ->where('salon_id', $salonId)
            ->where('durum', 1)
            ->whereNull('randevuya_geldi')
            ->where('tarih', $bugun)
            ->whereRaw("CONCAT(tarih,' ',saat) <= ?", [$esik])
            ->count();

        if ($sayi <= 0) return null;

        return [
            'id'        => 'geldi_gelmedi',
            'tip'       => 'geldi_gelmedi',
            'oncelik'   => 3,
            'tema'      => 'kirmizi-uyari',
            'ikon'      => 'fa-question-circle',
            'emoji'     => '🚨',
            'baslik'    => 'Saati Geçen Randevular',
            'mesaj'     => $sayi . ' randevunun saati geçti, durumu hâlâ işaretlenmemiş.',
            'altMesaj'  => 'Geldiyse işlem yapın, gelmediyse veya iptalse lütfen iptal edin — gün sonu raporunuz doğru çıksın.',
            'cta_text'  => 'Randevulara Git',
            'link'      => '/isletmeyonetim/randevular?sube=' . $salonId . '&filtre=isaretsiz',
            'sayac'     => $sayi,
        ];
    }

    /* -----------------------------------------------------------------
     * 2) Son 24 saatte (online/santral) eklenmiş yeni müşteri
     * ----------------------------------------------------------------- */
    private function yeniMusteriler($salonId)
    {
        $altSinir = date('Y-m-d H:i:s', strtotime('-24 hours'));

        // GERCEK sayi: eskiden limit(10) vardi, 15 yeni musteri olsa bile "10" yaziyordu.
        // Artik dogrudan count -> tam sayi.
        $sayi = DB::table('musteri_portfoy')
            ->where('salon_id', $salonId)
            ->where('created_at', '>=', $altSinir)
            ->count();

        if ($sayi <= 0) return null;

        // Tekil mesaj icin en son eklenen musterinin adi
        $isim = DB::table('musteri_portfoy')
            ->join('users', 'users.id', '=', 'musteri_portfoy.user_id')
            ->where('musteri_portfoy.salon_id', $salonId)
            ->where('musteri_portfoy.created_at', '>=', $altSinir)
            ->orderBy('musteri_portfoy.created_at', 'desc')
            ->value('users.name') ?: 'Yeni misafir';

        return [
            // NOT: id SABIT olmali. Eskiden md5($altSinir) idi; $altSinir her saniye
            // degistigi icin her feed'de yeni id uretiyor, toast'lar ust uste birikiyordu.
            'id'       => 'yeni_musteri',
            'tip'      => 'yeni_musteri',
            'oncelik'  => 2,
            'tema'     => 'konfeti-parti',
            'ikon'     => 'fa-user-plus',
            'emoji'    => '🎉',
            'baslik'   => 'Yeni Müşteri Geldi! 🎊',
            'mesaj'    => $sayi == 1
                ? "Son 24 saatte $isim portföyünüze eklendi."
                : "Son 24 saatte $sayi yeni müşteri portföyünüze eklendi.",
            'altMesaj' => 'Tıkla → "Yeni Eklenen Müşteriler" listesini aç (en yeniler üstte), hoş geldin SMS\'i at.',
            'cta_text' => 'Yeni Müşterileri Gör',
            // ?filtre=yeni: musteriler sayfasi acilinca "Yeni Eklenen Musteriler" sekmesini otomatik acar.
            'link'     => '/isletmeyonetim/musteriler?sube=' . $salonId . '&filtre=yeni',
            'sayac'    => $sayi,
        ];
    }

    /* -----------------------------------------------------------------
     * 3) Personel maaş ödemesi geciken (önceki ay)
     * ----------------------------------------------------------------- */
    private function personelOdemesi($salonId)
    {
        if (!$this->tabloVarMi('personel_maas_odemeleri')) return null;

        $bugun = (int) date('d');
        if ($bugun < 5) return null;

        $oncekiAyDonem = date('Y-m', strtotime('first day of last month'));
        $oncekiAyAdi   = $this->ayAdi(date('m', strtotime('first day of last month')));
        $oncekiAyYil   = date('Y', strtotime('first day of last month'));

        // Personel filtresi Prim raporu sayfasiyla AYNI olmali: aktif + arsivli olmayan.
        // (Eskiden arsivli filtresi yoktu; arsivli ama maasi>0 personel sayfada gorunmedigi
        //  halde karta dahil oluyor, "fazla" gosteriyordu.)
        $personeller = Personeller::where('salon_id', $salonId)
            ->where('aktif', 1)
            ->where(function ($q) { $q->where('arsivli', false)->orWhereNull('arsivli'); })
            ->where('maas', '>', 0)
            ->get(['id', 'personel_adi', 'maas']);

        if ($personeller->isEmpty()) return null;

        $odenmis = PersonelMaasOdemesi::where('salon_id', $salonId)
            ->where('donem', $oncekiAyDonem)
            ->pluck('personel_id')
            ->toArray();

        $bekleyen = $personeller->filter(function ($p) use ($odenmis) {
            return ((float) $p->maas) > 0 && !in_array($p->id, $odenmis);
        });

        $sayi = $bekleyen->count();
        if ($sayi <= 0) return null;

        // Kim sayiliyor net olsun: ilk birkac adi goster (dogrulama + faydali bilgi).
        $adlar = $bekleyen->pluck('personel_adi')->filter()->values();
        $adListesi = $adlar->take(4)->implode(', ') . ($adlar->count() > 4 ? ' +' . ($adlar->count() - 4) : '');

        return [
            'id'       => 'personel_odeme_' . $oncekiAyDonem,
            'tip'      => 'personel_odeme',
            'oncelik'  => 3,
            'tema'     => 'altin-yagmur',
            'ikon'     => 'fa-money-bill-wave',
            'emoji'    => '💸',
            'baslik'   => $oncekiAyAdi . ' ' . $oncekiAyYil . ' Maaş Ödemeleri',
            'mesaj'    => $sayi . ' personelin ' . $oncekiAyAdi . ' ayı maaş ödemesi hâlâ kayıtlı değil.',
            'altMesaj' => 'Kayıtlı değil: ' . $adListesi,
            'cta_text' => 'Maaş & Prim',
            // Maaş takibinin asil yapildigi sayfa Prim & Hak Edis; personeller listesi degil.
            'link'     => '/isletmeyonetim/primraporu?sube=' . $salonId,
            'sayac'    => $sayi,
        ];
    }

    /* -----------------------------------------------------------------
     * 4) Bekleyen randevu onayı (durum=0)
     * ----------------------------------------------------------------- */
    private function bekleyenRandevular($salonId)
    {
        // SADECE BUGUN: randevular sayfasi (randevuyukle) yalnizca bugunun takvimini
        // yukluyor; kart da bugunun onay bekleyenlerini saysin ki sayi sayfayla bire bir tutsun.
        // (Eskiden tarih>=bugun idi; gelecek gunlerin bekleyenleri "fazla" gosteriyordu.)
        $bugun = date('Y-m-d');
        $sayi = DB::table('randevular')
            ->where('salon_id', $salonId)
            ->where('durum', 0)
            ->where('tarih', $bugun)
            ->count();

        if ($sayi <= 0) return null;

        return [
            'id'       => 'bekleyen_randevu',
            'tip'      => 'bekleyen_randevu',
            'oncelik'  => 2,
            'tema'     => 'mavi-cinglir',
            'ikon'     => 'fa-bell',
            'emoji'    => '🔔',
            'baslik'   => 'Onay Bekleyen Randevu',
            'mesaj'    => 'Bugün ' . $sayi . ' randevu hâlâ onayınızı bekliyor.',
            'altMesaj' => 'Cevapsız bırakırsan müşteri başka kapı çalar, hadi onayla!',
            'cta_text' => 'Randevular',
            'link'     => '/isletmeyonetim/randevular?sube=' . $salonId . '&durum=bekleyen',
            'sayac'    => $sayi,
        ];
    }

    /* -----------------------------------------------------------------
     * 5) Bugün doğum günü olan müşteriler
     * ----------------------------------------------------------------- */
    private function dogumGunuBugun($salonId)
    {
        if (!$this->kolonVarMi('users', 'dogum_tarihi')) return null;

        $bugunMd = date('m-d');
        $musteriler = DB::table('users')
            ->join('musteri_portfoy', 'musteri_portfoy.user_id', '=', 'users.id')
            ->where('musteri_portfoy.salon_id', $salonId)
            ->whereNotNull('users.dogum_tarihi')
            ->whereRaw("DATE_FORMAT(users.dogum_tarihi,'%m-%d') = ?", [$bugunMd])
            ->limit(20)
            ->pluck('users.name')
            ->toArray();

        $sayi = count($musteriler);
        if ($sayi <= 0) return null;

        $isim = $musteriler[0] ?? 'Müşteriniz';
        return [
            'id'       => 'dogum_gunu_' . date('Y-m-d'),
            'tip'      => 'dogum_gunu',
            'oncelik'  => 1,
            'tema'     => 'pasta-balon',
            'ikon'     => 'fa-birthday-cake',
            'emoji'    => '🎂',
            'baslik'   => 'Bugün Doğum Günü Var!',
            'mesaj'    => $sayi == 1
                ? "$isim'in bugün doğum günü! Bir SMS atmaya ne dersin?"
                : "$sayi müşterinizin bugün doğum günü. Tebrik mesajı atmayı unutma!",
            'altMesaj' => 'Küçük bir mesaj, büyük bir sadakat oluşturur. 💝',
            'cta_text' => 'Toplu SMS',
            'link'     => '/isletmeyonetim/toplusmsgonder?sube=' . $salonId,
            'sayac'    => $sayi,
        ];
    }

    /* -----------------------------------------------------------------
     * 6) Açık adisyon (1 günden eski, kalan tutar > 0)
     * ----------------------------------------------------------------- */
    private function acikAdisyonEski($salonId)
    {
        if (!$this->tabloVarMi('adisyonlar')) return null;

        $eskiTarih = date('Y-m-d 00:00:00', strtotime('-1 day'));

        try {
            $adisyonlar = DB::table('adisyonlar')
                ->where('salon_id', $salonId)
                ->where('created_at', '<', $eskiTarih)
                ->whereRaw('IFNULL(silindi,0) = 0')
                ->limit(200)
                ->get(['id']);
        } catch (\Throwable $e) {
            $adisyonlar = DB::table('adisyonlar')
                ->where('salon_id', $salonId)
                ->where('created_at', '<', $eskiTarih)
                ->limit(200)
                ->get(['id']);
        }

        if ($adisyonlar->isEmpty()) return null;

        $ids = $adisyonlar->pluck('id')->toArray();
        $kalanSayi = 0;
        foreach (array_chunk($ids, 100) as $chunk) {
            $kalanSayi += $this->acikAdisyonChunkSay($chunk);
        }

        if ($kalanSayi <= 0) return null;

        return [
            'id'       => 'acik_adisyon',
            'tip'      => 'acik_adisyon',
            'oncelik'  => 2,
            'tema'     => 'turuncu-kasa',
            'ikon'     => 'fa-cash-register',
            'emoji'    => '🧾',
            'baslik'   => 'Açık Adisyon Birikti!',
            'mesaj'    => $kalanSayi . ' adisyon dünden kalmış, hâlâ tahsilat bekliyor.',
            'altMesaj' => 'Açık adisyon = sahipsiz para. Tahsilatı kapatmayı unutma!',
            'cta_text' => 'Adisyonlara Git',
            'link'     => '/isletmeyonetim/adisyonlar?sube=' . $salonId . '&adisyondurumu=acik',
            'sayac'    => $kalanSayi,
        ];
    }

    private function acikAdisyonChunkSay(array $ids)
    {
        $sayim = 0;
        $hizmet = DB::table('adisyon_hizmetler')->whereIn('adisyon_id', $ids)
            ->select('adisyon_id', DB::raw('SUM(IFNULL(toplam_tutar,IFNULL(birim_tutar,0))) as t'))
            ->groupBy('adisyon_id')->pluck('t', 'adisyon_id');
        $urun = DB::table('adisyon_urunler')->whereIn('adisyon_id', $ids)
            ->select('adisyon_id', DB::raw('SUM(IFNULL(toplam_tutar,IFNULL(birim_tutar,0))) as t'))
            ->groupBy('adisyon_id')->pluck('t', 'adisyon_id');
        $paket = DB::table('adisyon_paketler')->whereIn('adisyon_id', $ids)
            ->select('adisyon_id', DB::raw('SUM(IFNULL(toplam_tutar,IFNULL(birim_tutar,0))) as t'))
            ->groupBy('adisyon_id')->pluck('t', 'adisyon_id');
        $tahsilat = DB::table('tahsilatlar')->whereIn('adisyon_id', $ids)
            ->select('adisyon_id', DB::raw('SUM(IFNULL(tutar,0)) as t'))
            ->groupBy('adisyon_id')->pluck('t', 'adisyon_id');

        foreach ($ids as $id) {
            $toplam = (float) ($hizmet[$id] ?? 0) + (float) ($urun[$id] ?? 0) + (float) ($paket[$id] ?? 0);
            $odenen = (float) ($tahsilat[$id] ?? 0);
            if ($toplam - $odenen > 0.01) $sayim++;
        }
        return $sayim;
    }

    /* -----------------------------------------------------------------
     * 7) Geciken alacaklar (planlanan tarih dünü geçmiş)
     * ----------------------------------------------------------------- */
    private function gecikenAlacaklar($salonId)
    {
        if (!$this->tabloVarMi('alacaklar')) return null;

        $bugun = date('Y-m-d');
        try {
            $sayi = DB::table('alacaklar')
                ->where('salon_id', $salonId)
                ->whereNotNull('planlanan_odeme_tarihi')
                ->where('planlanan_odeme_tarihi', '<', $bugun)
                ->whereRaw('IFNULL(silindi,0) = 0')
                ->count();
        } catch (\Throwable $e) {
            $sayi = DB::table('alacaklar')
                ->where('salon_id', $salonId)
                ->whereNotNull('planlanan_odeme_tarihi')
                ->where('planlanan_odeme_tarihi', '<', $bugun)
                ->count();
        }

        if ($sayi <= 0) return null;

        return [
            'id'       => 'geciken_alacak',
            'tip'      => 'geciken_alacak',
            'oncelik'  => 2,
            'tema'     => 'kirmizi-uyari',
            'ikon'     => 'fa-hand-holding-usd',
            'emoji'    => '⏰',
            'baslik'   => 'Geciken Alacaklar',
            'mesaj'    => $sayi . ' alacağın planlanan ödeme tarihi geçti.',
            'altMesaj' => 'Müşteriyi nazikçe ara, takip etmeyen unutulur!',
            'cta_text' => 'Alacaklara Git',
            'link'     => '/isletmeyonetim/alacaklar?sube=' . $salonId,
            'sayac'    => $sayi,
        ];
    }

    /* -----------------------------------------------------------------
     * 8) Düşük SMS bakiyesi
     * ----------------------------------------------------------------- */
    private function dusukSmsBakiyesi($salonId)
    {
        return null;
    }

    /* -----------------------------------------------------------------
     * Yardımcılar
     * ----------------------------------------------------------------- */
    private function tabloVarMi($table)
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }
    private function kolonVarMi($table, $col)
    {
        try {
            return Schema::hasColumn($table, $col);
        } catch (\Throwable $e) {
            return false;
        }
    }
    private function ayAdi($ay)
    {
        $aylar = [1 => 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
            'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        return $aylar[(int) $ay] ?? '';
    }
}
