<?php

namespace App\Services;

use App\AdisyonHizmetler;
use App\Adisyonlar;
use App\DamgaKartiKullanimlari;
use App\DamgaKartiKurallari;
use App\Randevular;
use App\TahsilatHizmetler;
use App\Tahsilatlar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Damga karti ("Cilt Bakimini 4 kez alana 1 seans ucretsiz").
 *
 * Sayim (cron/defter yok, her seferinde kaynaktan hesaplanir):
 *   ucretli ziyaret = kuralin hizmetini iceren, "Geldi" isaretli (durum=onayli,
 *   randevuya_geldi=1), baslangic'tan sonraki randevular (randevu basina 1). Haric:
 *   paket seansi (adisyon_paket_seanslar), seansli hizmet kalemi, telafi, yardimci
 *   personel satiri, ucretsiz hakkin kullanildigi randevu ve o an tahsil edilen randevu.
 *   kazanilan = floor(ucretli / gereken_adet) * ucretsiz_adet
 *   hak       = kazanilan - kullanilan
 * O an tahsil edilen randevu haric tutulur: 4. ziyaret kendi kendine hak dogurup
 * bedava olamaz; hak 5. ziyarette dogar.
 *
 * Uygulama (personel onayiyla, tahsilat ekranindan):
 *   kalemin kalan borcu indirim_tutari'na eklenir (fiyat DEGISMEZ -> raporlarda
 *   bedava verilen hizmetin degeri gorunur) + 0 TL tahsilat ("Damga karti" notlu)
 *   + kullanim satiri. Kalem/adisyon silinirse kullanim sayilmaz, hak geri gelir.
 */
class DamgaKartiServisi
{
    /**
     * GECICI TEST (11 Kas 2026): true iken ileri tarihli ama "Geldi" isaretli randevular
     * da damga sayilir. Test bitince false yapilacak (normal kural: gunu gelince sayilir).
     */
    const ILERI_TARIH_SAYILIR = true;

    private static $kolonOnbellek = [];

    private static function kolonVar($tablo, $kolon)
    {
        $k = $tablo . '.' . $kolon;
        if (!array_key_exists($k, self::$kolonOnbellek)) {
            self::$kolonOnbellek[$k] = Schema::hasColumn($tablo, $kolon);
        }
        return self::$kolonOnbellek[$k];
    }

    public static function tabloHazir()
    {
        return Schema::hasTable('damga_karti_kurallari') && Schema::hasTable('damga_karti_kullanimlari');
    }

    /** Salonun kurallari, hizmet adiyla. */
    public static function kurallar($salonId, $sadeceAktif = false)
    {
        if (!self::tabloHazir()) return collect();
        $q = DB::table('damga_karti_kurallari as k')
            ->leftJoin('hizmetler as h', 'h.id', '=', 'k.hizmet_id')
            ->where('k.salon_id', (int) $salonId)
            ->select('k.*', 'h.hizmet_adi')
            ->orderBy('h.hizmet_adi');
        if ($sadeceAktif) $q->where('k.aktif', 1);
        return $q->get();
    }

    /** Kural formu icin salonun aktif hizmetleri. */
    public static function hizmetSecenekleri($salonId)
    {
        return DB::table('salon_sunulan_hizmetler as s')
            ->join('hizmetler as h', 'h.id', '=', 's.hizmet_id')
            ->where('s.salon_id', (int) $salonId)
            ->where('s.aktif', 1)
            ->groupBy('h.id', 'h.hizmet_adi')
            ->orderBy('h.hizmet_adi')
            ->get(['h.id', 'h.hizmet_adi']);
    }

    /**
     * Kurali ekler/gunceller. Kapali->acik (veya yeni acik) geciste baslangic = simdi:
     * oncesindeki ziyaretler damga sayilmaz.
     * @throws \RuntimeException
     */
    public static function kuralKaydet($salonId, array $g)
    {
        $salonId  = (int) $salonId;
        $id       = (int) ($g['id'] ?? 0);
        $hizmetId = (int) ($g['hizmet_id'] ?? 0);
        $gereken  = (int) ($g['gereken_adet'] ?? 0);
        $ucretsiz = (int) ($g['ucretsiz_adet'] ?? 1);
        $aktif    = !empty($g['aktif']) ? 1 : 0;

        if ($hizmetId <= 0) throw new \RuntimeException('Hizmet seçin.');
        if ($gereken < 1 || $gereken > 100) throw new \RuntimeException('Alım adedi 1 ile 100 arasında olmalı.');
        if ($ucretsiz < 1 || $ucretsiz > 10) throw new \RuntimeException('Ücretsiz seans 1 ile 10 arasında olmalı.');

        $kural = $id
            ? DamgaKartiKurallari::where('id', $id)->where('salon_id', $salonId)->first()
            : new DamgaKartiKurallari(['salon_id' => $salonId]);
        if (!$kural) throw new \RuntimeException('Kural bulunamadı.');

        $cakisan = DamgaKartiKurallari::where('salon_id', $salonId)->where('hizmet_id', $hizmetId)
            ->where('id', '!=', $id)->exists();
        if ($cakisan) throw new \RuntimeException('Bu hizmet için zaten bir kural var; onu düzenleyin.');

        $oncekiAktif = $kural->exists ? (int) $kural->aktif : 0;
        $kural->salon_id      = $salonId;
        $kural->hizmet_id     = $hizmetId;
        $kural->gereken_adet  = $gereken;
        $kural->ucretsiz_adet = $ucretsiz;
        $kural->aktif         = $aktif;
        if ($aktif && (!$oncekiAktif || !$kural->baslangic)) {
            $kural->baslangic = date('Y-m-d H:i:s');
        }
        $kural->save();

        return $kural;
    }

    /** Kalemi/adisyonu hala duran kullanimlar (silinen kalemin hakki geri gelir). */
    private static function gecerliKullanimlar($kuralId, $userId)
    {
        return DB::table('damga_karti_kullanimlari as ku')
            ->join('adisyon_hizmetler as ah', 'ah.id', '=', 'ku.adisyon_hizmet_id')
            ->where('ku.kural_id', (int) $kuralId)
            ->where('ku.user_id', (int) $userId)
            ->get(['ku.id', 'ku.randevu_id']);
    }

    private static function ucretliZiyaretSayisi($kural, $userId, array $haricRandevuIdler)
    {
        if (!$kural->baslangic) return 0;
        $hizmetId = (int) $kural->hizmet_id;

        $q = DB::table('randevular as r')
            ->join('randevu_hizmetler as rh', 'rh.randevu_id', '=', 'r.id')
            ->where('r.salon_id', (int) $kural->salon_id)
            ->where('r.user_id', (int) $userId)
            ->where('r.durum', Randevular::ONAYLANDI)
            ->where('r.randevuya_geldi', 1)
            ->where('r.tarih', '>=', date('Y-m-d', strtotime($kural->baslangic)))
            ->where('rh.hizmet_id', $hizmetId)
            ->whereNotExists(function ($s) {
                $s->select(DB::raw(1))->from('adisyon_paket_seanslar as aps')
                    ->whereColumn('aps.randevu_id', 'r.id')
                    ->whereColumn('aps.hizmet_id', 'rh.hizmet_id');
            });
        if (!self::ILERI_TARIH_SAYILIR) {
            $q->where('r.tarih', '<=', date('Y-m-d'));
        }
        if (self::kolonVar('randevu_hizmetler', 'yardimci_personel')) {
            $q->whereRaw('COALESCE(rh.yardimci_personel, 0) != 1');
        }
        if (self::kolonVar('randevu_hizmetler', 'telafi_kaynak_rh_id')) {
            $q->whereNull('rh.telafi_kaynak_rh_id');
        }
        if (self::kolonVar('adisyon_hizmetler', 'seans_sayisi') && self::kolonVar('adisyon_hizmetler', 'randevu_id')) {
            $q->whereNotExists(function ($s) {
                $s->select(DB::raw(1))->from('adisyon_hizmetler as sah')
                    ->whereColumn('sah.randevu_id', 'r.id')
                    ->whereColumn('sah.hizmet_id', 'rh.hizmet_id')
                    ->where('sah.seans_sayisi', '>', 1);
            });
        }
        $haric = array_values(array_filter(array_map('intval', $haricRandevuIdler)));
        if ($haric) $q->whereNotIn('r.id', $haric);

        return (int) $q->distinct()->count('r.id');
    }

    /**
     * Musterinin bu kuraldaki durumu.
     * @param int|null $buRandevuId O an tahsil edilen randevu (sayima katilmaz).
     */
    public static function durum($kural, $userId, $buRandevuId = null)
    {
        $kullanimlar = self::gecerliKullanimlar($kural->id, $userId);
        $haric = $kullanimlar->pluck('randevu_id')->all();
        if ($buRandevuId) $haric[] = (int) $buRandevuId;

        $ucretli   = self::ucretliZiyaretSayisi($kural, $userId, $haric);
        $gereken   = max(1, (int) $kural->gereken_adet);
        $kazanilan = intdiv($ucretli, $gereken) * (int) $kural->ucretsiz_adet;
        $kullanilan = $kullanimlar->count();

        return [
            'ucretli'    => $ucretli,
            'ilerleme'   => $ucretli % $gereken,
            'gereken'    => $gereken,
            'kazanilan'  => $kazanilan,
            'kullanilan' => $kullanilan,
            'hak'        => max(0, $kazanilan - $kullanilan),
        ];
    }

    /**
     * Musterinin bu hizmeti iceren son randevulari ve her birinin damgaya sayilip
     * sayilmadigi (sayilmadiysa nedeni). Tahsilat ekraninda "hangi ziyaretler sayildi"
     * dokumu; sayim kurallariyla (ucretliZiyaretSayisi) ayni sirayla karar verir.
     */
    public static function ziyaretDokumu($kural, $userId, $buRandevuId = null, $limit = 15)
    {
        $hizmetId = (int) $kural->hizmet_id;
        $sec = ['r.id', 'r.tarih', 'r.durum', 'r.randevuya_geldi'];
        $q = DB::table('randevular as r')
            ->join('randevu_hizmetler as rh', 'rh.randevu_id', '=', 'r.id')
            ->where('r.salon_id', (int) $kural->salon_id)
            ->where('r.user_id', (int) $userId)
            ->where('rh.hizmet_id', $hizmetId)
            ->groupBy('r.id', 'r.tarih', 'r.durum', 'r.randevuya_geldi')
            ->orderBy('r.tarih', 'desc')->orderBy('r.id', 'desc')
            ->limit((int) $limit);
        $q->select($sec);
        if (self::kolonVar('randevu_hizmetler', 'yardimci_personel')) {
            $q->addSelect(DB::raw('MIN(COALESCE(rh.yardimci_personel, 0)) as sadece_yardimci'));
        }
        if (self::kolonVar('randevu_hizmetler', 'telafi_kaynak_rh_id')) {
            $q->addSelect(DB::raw('MIN(CASE WHEN rh.telafi_kaynak_rh_id IS NULL THEN 0 ELSE 1 END) as sadece_telafi'));
        }
        $satirlar = $q->get();
        if ($satirlar->isEmpty()) return [];

        $idler = $satirlar->pluck('id')->all();
        $paket = DB::table('adisyon_paket_seanslar')->whereIn('randevu_id', $idler)
            ->where('hizmet_id', $hizmetId)->pluck('randevu_id')->map(function ($v) { return (int) $v; })->all();
        if (self::kolonVar('adisyon_hizmetler', 'seans_sayisi') && self::kolonVar('adisyon_hizmetler', 'randevu_id')) {
            $paket = array_merge($paket, DB::table('adisyon_hizmetler')->whereIn('randevu_id', $idler)
                ->where('hizmet_id', $hizmetId)->where('seans_sayisi', '>', 1)
                ->pluck('randevu_id')->map(function ($v) { return (int) $v; })->all());
        }
        $kullanilan = self::gecerliKullanimlar($kural->id, $userId)->pluck('randevu_id')->map(function ($v) { return (int) $v; })->all();
        $baslangicGun = $kural->baslangic ? date('Y-m-d', strtotime($kural->baslangic)) : null;
        $bugun = date('Y-m-d');

        $sonuc = [];
        foreach ($satirlar as $s) {
            $neden = null;
            if ((int) $s->durum !== Randevular::ONAYLANDI)          $neden = 'Onaylı değil';
            elseif ((int) $s->randevuya_geldi !== 1)                 $neden = '"Geldi" işaretli değil';
            elseif (!self::ILERI_TARIH_SAYILIR && $s->tarih > $bugun)                              $neden = 'İleri tarihli (günü gelince sayılır)';
            elseif (!$baslangicGun || $s->tarih < $baslangicGun)     $neden = 'Kural açılmadan önce';
            elseif (in_array((int) $s->id, $paket, true))            $neden = 'Paket / seanslı satış seansı';
            elseif (!empty($s->sadece_telafi))                       $neden = 'Telafi seansı';
            elseif (!empty($s->sadece_yardimci))                     $neden = 'Yardımcı personel kaydı';
            elseif (in_array((int) $s->id, $kullanilan, true))       $neden = 'Ücretsiz seans olarak kullanıldı';
            $sonuc[] = [
                'randevu_id' => (int) $s->id,
                'tarih'      => date('d.m.Y', strtotime($s->tarih)),
                'bu_ziyaret' => $buRandevuId && (int) $s->id === (int) $buRandevuId,
                'sayildi'    => $neden === null,
                'neden'      => $neden,
            ];
        }
        return $sonuc;
    }

    /**
     * Cok seanslik satis kalemi (seans_sayisi>1). Tum seanslarin bedelini tasir;
     * %100 indirim tum seanslari bedava yapacagi icin damga uygulanmaz. Seanslari
     * da adisyon_paket_seanslar'da tutuldugundan damga sayimina girmez.
     */
    private static function seansliKalem($ah)
    {
        return (int) ($ah->seans_sayisi ?? 0) > 1;
    }

    private static function kalanBorc($ah)
    {
        $odenen = (float) TahsilatHizmetler::where('adisyon_hizmet_id', $ah->id)->sum('tutar');
        return round((float) $ah->fiyat - (float) $ah->indirim_tutari - $odenen, 2);
    }

    /**
     * Tahsilat ekrani: adisyondaki kurala tabi hizmet kalemlerinin damga durumu.
     * Her kalem icin: uygulanabilir mi, uygulandiysa kullanim id (geri alma).
     */
    public static function adisyonDurumu($salonId, $adisyonId)
    {
        if (!self::tabloHazir() || !$adisyonId) return [];
        $adisyon = Adisyonlar::where('id', (int) $adisyonId)->where('salon_id', (int) $salonId)->first();
        if (!$adisyon || !$adisyon->user_id) return [];

        $kurallar = DamgaKartiKurallari::where('salon_id', (int) $salonId)->where('aktif', 1)->get()->keyBy('hizmet_id');
        if ($kurallar->isEmpty()) return [];

        $kalemler = AdisyonHizmetler::where('adisyon_id', $adisyon->id)
            ->whereIn('hizmet_id', $kurallar->keys()->all())->orderBy('id')->get()
            ->filter(function ($ah) { return !self::seansliKalem($ah); })->values();
        if ($kalemler->isEmpty()) return [];

        $kullanimlar = DamgaKartiKullanimlari::whereIn('adisyon_hizmet_id', $kalemler->pluck('id')->all())
            ->get()->keyBy('adisyon_hizmet_id');
        $hizmetAdlari = DB::table('hizmetler')->whereIn('id', $kalemler->pluck('hizmet_id')->unique()->all())
            ->pluck('hizmet_adi', 'id');

        $sonuc = [];
        foreach ($kalemler as $ah) {
            $kural = $kurallar->get($ah->hizmet_id);
            $durum = self::durum($kural, $adisyon->user_id, $ah->randevu_id ?? null);
            $kullanim = $kullanimlar->get($ah->id);
            $kalan = self::kalanBorc($ah);
            $dokum = self::ziyaretDokumu($kural, $adisyon->user_id, $ah->randevu_id ?? null);
            // Ekranda gosterilen damga BU ZIYARET DAHIL (personel "3 Geldi isaretledim" der);
            // hak hesabi ise bu ziyareti haric tutar (durum()).
            $buSayilir = false;
            foreach ($dokum as $z) { if ($z['bu_ziyaret'] && $z['sayildi']) $buSayilir = true; }
            $toplam = $durum['ucretli'] + ($buSayilir ? 1 : 0);
            $sonuc[] = [
                'dokum'             => $dokum,
                'toplam'            => $toplam,
                'ilerleme'          => $toplam % max(1, (int) $kural->gereken_adet),
                'adisyon_hizmet_id' => (int) $ah->id,
                'hizmet_adi'        => (string) ($hizmetAdlari[$ah->hizmet_id] ?? 'Hizmet'),
                'gereken'           => (int) $kural->gereken_adet,
                'ucretsiz'          => (int) $kural->ucretsiz_adet,
                'durum'             => $durum,
                'kalan'             => $kalan,
                'kullanim_id'       => $kullanim ? (int) $kullanim->id : null,
                'kullanim_indirim'  => $kullanim ? (float) $kullanim->indirim_tutari : 0,
                'uygulanabilir'     => !$kullanim && $durum['hak'] > 0 && $kalan > 0
                    && !$ah->hediye && $ah->senet_id === null && $ah->taksitli_tahsilat_id === null,
            ];
        }
        return $sonuc;
    }

    /**
     * Ucretsiz seansi kaleme uygular: %100 indirim + 0 TL tahsilat + kullanim.
     * @throws \RuntimeException
     */
    public static function uygula($salonId, $adisyonHizmetId, $olusturanId = null)
    {
        return DB::transaction(function () use ($salonId, $adisyonHizmetId, $olusturanId) {
            $ah = AdisyonHizmetler::where('id', (int) $adisyonHizmetId)->lockForUpdate()->first();
            if (!$ah) throw new \RuntimeException('Hizmet kalemi bulunamadı.');
            $adisyon = Adisyonlar::where('id', $ah->adisyon_id)->where('salon_id', (int) $salonId)->first();
            if (!$adisyon || !$adisyon->user_id) throw new \RuntimeException('Adisyon bulunamadı.');
            if ($ah->senet_id !== null || $ah->taksitli_tahsilat_id !== null) {
                throw new \RuntimeException('Senetli/taksitli kaleme ücretsiz seans uygulanamaz.');
            }
            if ($ah->hediye) throw new \RuntimeException('Bu kalem zaten hediye olarak işaretli.');
            if (self::seansliKalem($ah)) {
                throw new \RuntimeException('Seanslı satışa ücretsiz seans uygulanamaz; tek seanslık kaleme uygulayın.');
            }
            if (DamgaKartiKullanimlari::where('adisyon_hizmet_id', $ah->id)->exists()) {
                throw new \RuntimeException('Bu kaleme ücretsiz seans zaten uygulanmış.');
            }

            // Kural satiri kilitlenir: ayni musteriye iki ekrandan ayni hakkin cift kullanimi olmasin.
            $kural = DamgaKartiKurallari::where('salon_id', (int) $salonId)->where('hizmet_id', $ah->hizmet_id)
                ->where('aktif', 1)->lockForUpdate()->first();
            if (!$kural) throw new \RuntimeException('Bu hizmet için açık damga kartı kuralı yok.');

            $durum = self::durum($kural, $adisyon->user_id, $ah->randevu_id ?? null);
            if ($durum['hak'] < 1) throw new \RuntimeException('Müşterinin bu hizmette ücretsiz seans hakkı yok.');

            $kalan = self::kalanBorc($ah);
            if ($kalan <= 0) throw new \RuntimeException('Bu kalemin ödenecek tutarı kalmamış.');

            $ah->indirim_tutari = round((float) $ah->indirim_tutari + $kalan, 2);
            $ah->save();

            $hizmetAdi = (string) DB::table('hizmetler')->where('id', $ah->hizmet_id)->value('hizmet_adi');
            $tahsilat = new Tahsilatlar();
            $tahsilat->adisyon_id       = $adisyon->id;
            $tahsilat->user_id          = $adisyon->user_id;
            $tahsilat->salon_id         = (int) $salonId;
            $tahsilat->tutar            = 0;
            $tahsilat->yapilan_odeme    = 0;
            $tahsilat->odeme_tarihi     = date('Y-m-d');
            $tahsilat->olusturan_id     = $olusturanId;
            $tahsilat->odeme_yontemi_id = \App\OdemeYontemleri::where('odeme_yontemi', 'like', '%Nakit%')->value('id');
            $tahsilat->notlar           = 'Damga kartı: ' . $kural->gereken_adet . ' ' . $hizmetAdi . ' alımına '
                . $kural->ucretsiz_adet . ' ücretsiz seans (' . number_format($kalan, 2, ',', '.') . ' ₺ indirim)';
            $tahsilat->save();

            TahsilatHizmetler::create([
                'tahsilat_id'       => $tahsilat->id,
                'adisyon_hizmet_id' => $ah->id,
                'tutar'             => 0,
            ]);

            return DamgaKartiKullanimlari::create([
                'salon_id'          => (int) $salonId,
                'kural_id'          => $kural->id,
                'user_id'           => (int) $adisyon->user_id,
                'adisyon_hizmet_id' => $ah->id,
                'randevu_id'        => $ah->randevu_id ?? null,
                'tahsilat_id'       => $tahsilat->id,
                'indirim_tutari'    => $kalan,
                'olusturan_id'      => $olusturanId,
            ]);
        });
    }

    /**
     * Uygulamayi geri alir: indirim kalemden duser, 0 TL tahsilat silinir, hak geri gelir.
     * @throws \RuntimeException
     */
    public static function geriAl($salonId, $kullanimId)
    {
        return DB::transaction(function () use ($salonId, $kullanimId) {
            $ku = DamgaKartiKullanimlari::where('id', (int) $kullanimId)->where('salon_id', (int) $salonId)
                ->lockForUpdate()->first();
            if (!$ku) throw new \RuntimeException('Kayıt bulunamadı.');

            $ah = AdisyonHizmetler::where('id', $ku->adisyon_hizmet_id)->lockForUpdate()->first();
            if ($ah) {
                $ah->indirim_tutari = max(0, round((float) $ah->indirim_tutari - (float) $ku->indirim_tutari, 2));
                $ah->save();
            }
            if ($ku->tahsilat_id) {
                TahsilatHizmetler::where('tahsilat_id', $ku->tahsilat_id)->delete();
                Tahsilatlar::where('id', $ku->tahsilat_id)->where('tutar', 0)->delete();
            }
            $ku->delete();
            return $ku;
        });
    }
}
