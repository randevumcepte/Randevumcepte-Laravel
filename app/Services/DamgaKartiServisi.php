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
     * Test anahtari: true iken ileri tarihli ama "Geldi" isaretli randevular da damga sayilir.
     * Normal kural false: randevu gunu gelince sayilir (11 Kas 2026 test bitti, kapatildi).
     */
    const ILERI_TARIH_SAYILIR = true; // GECICI TEST (16 Kas 2026): test bitince false

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
        // Gecerlilik: bos/0 = suresiz; birim gun|ay
        $gDeger   = (int) ($g['gecerlilik_deger'] ?? 0);
        $gBirim   = ($g['gecerlilik_birim'] ?? 'ay') === 'gun' ? 'gun' : 'ay';
        if ($gDeger < 0 || ($gBirim === 'gun' && $gDeger > 3650) || ($gBirim === 'ay' && $gDeger > 120)) {
            throw new \RuntimeException('Geçerlilik süresi en fazla 3650 gün / 120 ay olabilir.');
        }

        if ($hizmetId <= 0) throw new \RuntimeException('Hizmet seçin.');
        if ($gereken < 1 || $gereken > 100) throw new \RuntimeException('Alım adedi 1 ile 100 arasında olmalı.');
        if ($ucretsiz < 1 || $ucretsiz > 10) throw new \RuntimeException('İkram adedi 1 ile 10 arasında olmalı.');

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
        if (self::kolonVar('damga_karti_kurallari', 'gecerlilik_deger')) {
            $kural->gecerlilik_deger = $gDeger > 0 ? $gDeger : null;
            $kural->gecerlilik_birim = $gBirim;
        }
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
            ->get(['ku.id', 'ku.randevu_id', 'ku.created_at', 'ku.indirim_tutari']);
    }

    private static function ucretliZiyaretSayisi($kural, $userId, array $haricRandevuIdler)
    {
        $q = self::ucretliZiyaretSorgu($kural, $userId, $haricRandevuIdler);
        return $q ? (int) $q->distinct()->count('r.id') : 0;
    }

    /** Sayilan ucretli ziyaretlerin tarihleri (eskiden yeniye) — ikram kazanma tarihi icin. */
    private static function ucretliZiyaretTarihleri($kural, $userId, array $haricRandevuIdler)
    {
        $q = self::ucretliZiyaretSorgu($kural, $userId, $haricRandevuIdler);
        if (!$q) return [];
        return $q->select('r.id', 'r.tarih')->distinct()->orderBy('r.tarih')->orderBy('r.id')->get()
            ->map(function ($r) { return date('Y-m-d', strtotime($r->tarih)); })->values()->all();
    }

    /** Sayim kosullari TEK yerde (sayi ve tarih listesi ayni kurali kullanir). */
    private static function ucretliZiyaretSorgu($kural, $userId, array $haricRandevuIdler)
    {
        if (!$kural->baslangic) return null;
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

        return $q;
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

        $gereken    = max(1, (int) $kural->gereken_adet);
        $kullanilan = $kullanimlar->count();
        $gecerlilik = self::gecerlilik($kural);
        $sonsuz     = '9999-12-31';

        // Her ikram, kartini dolduran ucretli ziyaretin tarihinde kazanilir; sureli kuralda
        // [kazanma, kazanma + sure] araliginda gecerlidir (suresizde bitis yok). Kullanimlar
        // (tarih sirasiyla) o an gecerli ve suresi EN YAKIN dolacak ikramdan dusulur (FIFO);
        // kalanlardan suresi gecmis olanlar "suresi_dolan", digerleri kullanilabilir hak.
        $tarihler = self::ucretliZiyaretTarihleri($kural, $userId, $haric);
        $ucretli  = count($tarihler);
        $birimler = [];
        for ($b = 1; $b * $gereken <= $ucretli; $b++) {
            $kazanma = $tarihler[$b * $gereken - 1];
            $bitis   = $gecerlilik ? self::bitisTarihi($kazanma, $gecerlilik) : $sonsuz;
            for ($u = 0; $u < (int) $kural->ucretsiz_adet; $u++) {
                $birimler[] = ['kazanma' => $kazanma, 'bitis' => $bitis, 'kullanim' => null];
            }
        }
        $kullanimSirali = $kullanimlar->map(function ($k) {
            return [
                'tarih' => $k->created_at ? date('Y-m-d', strtotime($k->created_at)) : date('Y-m-d'),
                'tutar' => (float) ($k->indirim_tutari ?? 0),
            ];
        })->sortBy('tarih')->values()->all();
        foreach ($kullanimSirali as $ku) {
            $kt = $ku['tarih'];
            $sec = null;
            foreach ($birimler as $i => $b) {
                if ($b['kullanim'] || $b['kazanma'] > $kt || $b['bitis'] < $kt) continue;
                if ($sec === null || $b['bitis'] < $birimler[$sec]['bitis']) $sec = $i;
            }
            if ($sec === null) {
                // Veri tutarsizligi (or. sure sonradan kisaltildi): en eski kullanilmamis birimi dus
                foreach ($birimler as $i => $b) { if (!$b['kullanim']) { $sec = $i; break; } }
            }
            if ($sec !== null) $birimler[$sec]['kullanim'] = $ku;
        }
        $bugun = date('Y-m-d');
        $gecerli = [];
        $dolan = 0;
        $hak = 0;
        $ikramlar = [];
        foreach ($birimler as $b) {
            if ($b['kullanim']) {
                $d = 'kullanildi';
            } elseif ($b['bitis'] < $bugun) {
                $d = 'suresi_doldu';
                $dolan++;
            } else {
                $d = 'gecerli';
                $hak++;
                if ($b['bitis'] !== $sonsuz) $gecerli[] = $b['bitis'];
            }
            $ikramlar[] = [
                'kazanma'         => $b['kazanma'],
                'bitis'           => $b['bitis'] === $sonsuz ? null : $b['bitis'],
                'durum'           => $d,
                'kullanim_tarihi' => $b['kullanim']['tarih'] ?? null,
                'tutar'           => $b['kullanim']['tutar'] ?? null,
            ];
        }
        sort($gecerli);

        return [
            'ucretli'          => $ucretli,
            'ilerleme'         => $ucretli % $gereken,
            'gereken'          => $gereken,
            'kazanilan'        => count($birimler),
            'kullanilan'       => $kullanilan,
            'suresi_dolan'     => $dolan,
            'hak'              => $hak,
            'gecerli_ikramlar' => $gecerli, // kullanilabilir ikramlarin son gecerlilik tarihleri (yakindan uzaga; suresizde bos)
            'ikramlar'         => $ikramlar, // kazanilan her ikram: kazanma, bitis, durum (kullanildi|gecerli|suresi_doldu)
        ];
    }

    /** Kuralin ikram gecerlilik suresi: ['deger' => int, 'birim' => 'gun'|'ay'] ya da null (suresiz). */
    public static function gecerlilik($kural)
    {
        if (!self::kolonVar('damga_karti_kurallari', 'gecerlilik_deger')) return null;
        $deger = (int) ($kural->gecerlilik_deger ?? 0);
        if ($deger < 1) return null;
        $birim = ($kural->gecerlilik_birim ?? 'ay') === 'gun' ? 'gun' : 'ay';
        return ['deger' => $deger, 'birim' => $birim];
    }

    /** "6 ay" / "30 gün" / null */
    public static function gecerlilikYazi($kural)
    {
        $g = self::gecerlilik($kural);
        return $g ? $g['deger'] . ($g['birim'] === 'gun' ? ' gün' : ' ay') : null;
    }

    /** Ikramin son gecerli gunu (dahil): kazanma + sure. */
    private static function bitisTarihi($kazanma, array $g)
    {
        $t = \Carbon\Carbon::parse($kazanma);
        $t = $g['birim'] === 'gun' ? $t->addDays($g['deger']) : $t->addMonthsNoOverflow($g['deger']);
        return $t->toDateString();
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
            elseif (in_array((int) $s->id, $kullanilan, true))       $neden = 'İkram olarak kullanıldı';
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
     * Yonetim (Ikram Hizmet sayfasi): ikram kazanmis musteriler, kural basina.
     * Aday = kural acildiktan sonra o hizmette en az "gereken" kadar Geldi randevusu olan
     * musteriler (tek gruplu sorgu); kesin sayim her aday icin durum() ile (tahsilatla ayni).
     * @return array [[user_id, ad, telefon, hizmet_adi, kural, ucretli, kazanilan, kullanilan, hak]]
     */
    public static function yonetimOzet($salonId, $limit = 300)
    {
        if (!self::tabloHazir()) return [];
        $sonuc = [];
        foreach (DamgaKartiKurallari::where('salon_id', (int) $salonId)->whereNotNull('baslangic')->get() as $k) {
            $adaylar = DB::table('randevular as r')
                ->join('randevu_hizmetler as rh', 'rh.randevu_id', '=', 'r.id')
                ->where('r.salon_id', (int) $salonId)
                ->where('rh.hizmet_id', (int) $k->hizmet_id)
                ->where('r.randevuya_geldi', 1)
                ->whereNotNull('r.user_id')
                ->where('r.tarih', '>=', date('Y-m-d', strtotime($k->baslangic)))
                ->groupBy('r.user_id')
                ->havingRaw('COUNT(DISTINCT r.id) >= ?', [max(1, (int) $k->gereken_adet)])
                ->limit($limit)
                ->pluck('r.user_id');
            $kullananlar = DB::table('damga_karti_kullanimlari')->where('kural_id', $k->id)->distinct()->pluck('user_id');
            $idler = $adaylar->merge($kullananlar)->map(function ($v) { return (int) $v; })->unique()->values();
            if ($idler->isEmpty()) continue;

            $hizmet = DB::table('hizmetler')->where('id', $k->hizmet_id)->value('hizmet_adi') ?: 'Hizmet';
            $kisiler = DB::table('users')->whereIn('id', $idler->all())->get(['id', 'name', 'cep_telefon'])->keyBy('id');
            foreach ($idler as $uid) {
                $d = self::durum($k, $uid);
                if ($d['kazanilan'] < 1 && $d['kullanilan'] < 1) continue;
                $kisi = $kisiler->get($uid);
                $sonuc[] = [
                    'user_id'    => $uid,
                    'ad'         => $kisi->name ?? ('Müşteri #' . $uid),
                    'telefon'    => $kisi->cep_telefon ?? '',
                    'hizmet_adi' => $hizmet,
                    'kural'      => (int) $k->gereken_adet . ' al, ' . (int) $k->ucretsiz_adet . ' ikram'
                        . (($gy = self::gecerlilikYazi($k)) ? ' · ' . $gy . ' geçerli' : ''),
                    'ucretli'    => $d['ucretli'],
                    'kazanilan'  => $d['kazanilan'],
                    'kullanilan' => $d['kullanilan'],
                    'hak'        => $d['hak'],
                    'suresi_dolan' => $d['suresi_dolan'],
                    'son_kullanma' => $d['gecerli_ikramlar'][0] ?? null,
                ];
            }
        }
        usort($sonuc, function ($a, $b) {
            return [$b['hak'], $b['kazanilan']] <=> [$a['hak'], $a['kazanilan']];
        });
        return $sonuc;
    }

    /**
     * Musteri paneli: musterinin bu salondaki damga kartlari (ilerleme + hak) ve
     * damga kuralindan kazanip kullandigi ucretsiz seanslar. Salt okuma; sayim
     * tahsilat ekraniyla ayni (durum()). Aktif kurallar + musterinin kullanimi
     * olan kapali kurallar listelenir.
     */
    public static function musteriKartlari($salonId, $userId)
    {
        if (!self::tabloHazir() || !$userId) return [];
        $salonId = (int) $salonId;
        $userId  = (int) $userId;

        $kullanimlar = DB::table('damga_karti_kullanimlari as ku')
            ->join('adisyon_hizmetler as ah', 'ah.id', '=', 'ku.adisyon_hizmet_id')
            ->leftJoin('randevular as r', 'r.id', '=', 'ku.randevu_id')
            ->where('ku.salon_id', $salonId)->where('ku.user_id', $userId)
            ->orderByDesc('ku.created_at')
            ->get(['ku.kural_id', 'ku.indirim_tutari', 'ku.created_at', 'r.tarih'])
            ->groupBy('kural_id');

        $kurallar = DamgaKartiKurallari::where('salon_id', $salonId)
            ->where(function ($q) use ($kullanimlar) {
                $q->where('aktif', 1)->orWhereIn('id', $kullanimlar->keys()->all());
            })->get();
        if ($kurallar->isEmpty()) return [];
        $hizmetAdlari = DB::table('hizmetler')->whereIn('id', $kurallar->pluck('hizmet_id')->all())->pluck('hizmet_adi', 'id');

        $sonuc = [];
        foreach ($kurallar as $k) {
            $d = self::durum($k, $userId);
            $sonuc[] = [
                'kural_id'      => (int) $k->id,
                'hizmet_adi'    => (string) ($hizmetAdlari[$k->hizmet_id] ?? 'Hizmet'),
                'gereken_adet'  => (int) $k->gereken_adet,
                'ucretsiz_adet' => (int) $k->ucretsiz_adet,
                'aktif'         => (int) $k->aktif,
                'ilerleme'      => $d['ilerleme'],
                'ucretli'       => $d['ucretli'],
                'kazanilan'     => $d['kazanilan'],
                'kullanilan'    => $d['kullanilan'],
                'hak'           => $d['hak'],
                // Gecerlilik: "6 ay" / null (suresiz); suresi dolan ikram sayisi; kullanilabilir
                // ikramlarin son gecerlilik tarihleri (yakindan uzaga) ve en yakini.
                'gecerlilik'       => self::gecerlilikYazi($k),
                'suresi_dolan'     => $d['suresi_dolan'],
                'gecerli_ikramlar' => $d['gecerli_ikramlar'],
                'son_kullanma'     => $d['gecerli_ikramlar'][0] ?? null,
                // Ikram gecmisi (en yeni ustte): her ikram icin kazanma/bitis/durum/kullanim
                'ikramlar'         => array_reverse($d['ikramlar']),
                'kullanimlar'   => ($kullanimlar->get($k->id) ?: collect())->map(function ($u) {
                    return [
                        'tarih'  => date('Y-m-d', strtotime($u->tarih ?: $u->created_at)),
                        'tutar'  => (float) $u->indirim_tutari,
                    ];
                })->values()->all(),
            ];
        }
        // Hak bekleyen once, sonra ilerlemesi olan, sonra digerleri
        usort($sonuc, function ($a, $b) {
            return [$b['hak'], $b['ilerleme'], $b['kullanilan']] <=> [$a['hak'], $a['ilerleme'], $a['kullanilan']];
        });
        return $sonuc;
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
                throw new \RuntimeException('Senetli/taksitli kaleme ikram uygulanamaz.');
            }
            if ($ah->hediye) throw new \RuntimeException('Bu kalem zaten hediye olarak işaretli.');
            if (self::seansliKalem($ah)) {
                throw new \RuntimeException('Seanslı satışa ikram uygulanamaz; tek seanslık kaleme uygulayın.');
            }
            if (DamgaKartiKullanimlari::where('adisyon_hizmet_id', $ah->id)->exists()) {
                throw new \RuntimeException('Bu kaleme ikram zaten uygulanmış.');
            }

            // Kural satiri kilitlenir: ayni musteriye iki ekrandan ayni hakkin cift kullanimi olmasin.
            $kural = DamgaKartiKurallari::where('salon_id', (int) $salonId)->where('hizmet_id', $ah->hizmet_id)
                ->where('aktif', 1)->lockForUpdate()->first();
            if (!$kural) throw new \RuntimeException('Bu hizmet için açık ikram kuralı yok.');

            $durum = self::durum($kural, $adisyon->user_id, $ah->randevu_id ?? null);
            if ($durum['hak'] < 1) throw new \RuntimeException('Müşterinin bu hizmette ikram hakkı yok.');

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
            $tahsilat->notlar           = 'İkram hizmet: ' . $kural->gereken_adet . ' ' . $hizmetAdi . ' alımına '
                . $kural->ucretsiz_adet . ' ikram (' . number_format($kalan, 2, ',', '.') . ' ₺ indirim)';
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
