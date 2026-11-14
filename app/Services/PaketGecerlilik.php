<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sinirsiz / sureli paket kurali — eski monolitin TEK kaynagi.
 * v2 karsiligi: New-Laravel App\Domain\Paket\PaketGecerlilik (iki repoda ayni kural).
 *
 * Sure ILK "Geldi" (geldi=1) seansinda baslar; bitis saklanmaz, her seferinde hesaplanir.
 * Boylece "Geldi" geri alinirsa ya da gecmis tarihli seans girilirse kendiliginden duzelir.
 * Hic gelinmemis pakette sure islemez.
 *
 * Sinirsiz pakette ADET KONTROLU YOKTUR: sure bitene kadar her randevu seansa yazilir, sure
 * bitince yazilmaz. seans_sayisi yalniz `seans_sayisi > 0` arayan eski sorgular/mobil icin dolu
 * tutulan teknik bir degerdir; KURAL ICIN KULLANILMAZ, EKRANDA GOSTERILMEZ (bkz. seansAlani).
 */
class PaketGecerlilik
{
    private static $kolonVar = null;

    /** Canlida migration elle calisir; kolon yoksa eski davranis (uygulama patlamasin). */
    public static function kolonVar(): bool
    {
        if (self::$kolonVar === null) {
            self::$kolonVar = Schema::hasColumn('adisyon_paketler', 'sinirsiz');
        }
        return self::$kolonVar;
    }

    public static function ustSinir($sayi, $birim, $gunlukLimit): int
    {
        $gun = (int) $sayi * ($birim === 'yil' ? 366 : 31);
        return max(1, $gun) * max(1, (int) $gunlukLimit);
    }

    /** Ilk gelis + sure. Ay sonu tasmaz (31 Oca + 1 ay = 28/29 Sub). */
    public static function bitis($ilkGeldi, $sayi, $birim): ?string
    {
        if (!$ilkGeldi || (int) $sayi <= 0) return null;
        $ay = (int) $sayi * ($birim === 'yil' ? 12 : 1);
        return Carbon::parse($ilkGeldi)->addMonthsNoOverflow($ay)->toDateString();
    }

    /** "6 Ay" / "2 Yıl" */
    public static function sureMetni($sayi, $birim): string
    {
        return (int) $sayi > 0 ? ((int) $sayi . ' ' . ($birim === 'yil' ? 'Yıl' : 'Ay')) : '';
    }

    /** Seans adedi yerine: sinirsiz pakette rozet, degilse verilen input/metin. */
    public static function seansAlani($paket, $normalHtml)
    {
        if (empty($paket->sinirsiz)) return $normalHtml;
        $sure = self::sureMetni($paket->gecerlilik_sayi ?? null, $paket->gecerlilik_birim ?? null);
        return '<span style="display:inline-block;padding:2px 10px;border-radius:999px;background:#f5eefe;color:#5C008E;font-size:12px;font-weight:700;white-space:nowrap">Sınırsız'.($sure ? ' · '.$sure : '').'</span>';
    }

    /** Paket formu (web): alanlari pakete yazar. Sinirsizda sure bossa 1 yil, limit bossa 1. */
    public static function paketFormunuYaz($paket, $request): void
    {
        if (!self::kolonVar() || !$request->has('sinirsiz')) return;

        $sinirsiz = (int) $request->sinirsiz === 1;
        $birim = $request->gecerlilik_birim === 'ay' ? 'ay' : 'yil';
        $sayi = min(120, max(0, (int) $request->gecerlilik_sayi));
        if ($sinirsiz && $sayi === 0) { $sayi = 1; $birim = 'yil'; }

        $paket->sinirsiz = $sinirsiz;
        $paket->gecerlilik_sayi = $sayi > 0 ? $sayi : null;
        $paket->gecerlilik_birim = $sayi > 0 ? $birim : null;
        // Limit opsiyonel: bos = o gun acilan her randevu seansa yazilir.
        $paket->gunluk_limit = ($sinirsiz && (int) $request->gunluk_limit > 0) ? min(50, (int) $request->gunluk_limit) : null;
        if ($sinirsiz) {
            $paket->miktar = self::ustSinir($sayi, $birim, $paket->gunluk_limit);
        }
    }

    /**
     * AdisyonPaketler::saving — paket_id yazildiginda (satis / paket yukseltme) kataloğu kopyalar.
     * Satis 6 ayri yoldan yapildigi icin model olayinda; yollarin hicbiri bilmek zorunda degil.
     */
    public static function satisaKopyala($ap): void
    {
        if (!self::kolonVar() || !$ap->paket_id || !$ap->isDirty('paket_id')) return;
        $p = DB::table('paketler')->where('id', $ap->paket_id)->first(['sinirsiz', 'gecerlilik_sayi', 'gecerlilik_birim', 'gunluk_limit']);
        if (!$p) return;

        $ap->sinirsiz = (int) $p->sinirsiz;
        $ap->gecerlilik_sayi = $p->gecerlilik_sayi;
        $ap->gecerlilik_birim = $p->gecerlilik_birim;
        $ap->gunluk_limit = $p->gunluk_limit;
        if ((int) $p->sinirsiz === 1) {
            $tavan = self::ustSinir($p->gecerlilik_sayi, $p->gecerlilik_birim, $p->gunluk_limit);
            $ap->seans_sayisi = $tavan;
            $ap->bekleyen_seans = $tavan - (int) $ap->kullanilan_seans - (int) $ap->kullanilmayan_seans;
        }
    }

    /** @return array{sinirsiz:bool, sure:string, baslangic:?string, bitis:?string, gunluk_limit:int} */
    public static function durum($adisyonPaketId): array
    {
        $bos = ['sinirsiz' => false, 'sure' => '', 'baslangic' => null, 'bitis' => null, 'gunluk_limit' => 0];
        if (!self::kolonVar() || !$adisyonPaketId) return $bos;
        $s = DB::table('adisyon_paketler')->where('id', $adisyonPaketId)
            ->first(['sinirsiz', 'gecerlilik_sayi', 'gecerlilik_birim', 'gunluk_limit']);
        if (!$s) return $bos;

        $ilk = (int) $s->gecerlilik_sayi > 0
            ? DB::table('adisyon_paket_seanslar')->where('adisyon_paket_id', $adisyonPaketId)
                ->where('geldi', 1)->where('seans_tarih', '>', '2000-01-01')->min('seans_tarih')
            : null;
        $ilk = $ilk ? substr((string) $ilk, 0, 10) : null;

        return [
            'sinirsiz'     => (int) $s->sinirsiz === 1,
            'sure'         => self::sureMetni($s->gecerlilik_sayi, $s->gecerlilik_birim),
            'baslangic'    => $ilk,
            'bitis'        => self::bitis($ilk, $s->gecerlilik_sayi, $s->gecerlilik_birim),
            'gunluk_limit' => (int) $s->gunluk_limit,
        ];
    }

    public static function suresiDoldu($adisyonPaketId, $tarih = null): bool
    {
        $d = self::durum($adisyonPaketId);
        return $d['bitis'] !== null && date('Y-m-d', strtotime($tarih ?: 'today')) > $d['bitis'];
    }

    /** Bu tarihte bu hizmet icin YENI seans acilabilir mi? (sure + gunluk limit; seans adedi cagirana ait) */
    public static function seansUygun($adisyonPaketId, $hizmetId, $tarih): bool
    {
        return self::seansEngeli($adisyonPaketId, $hizmetId, $tarih) === null;
    }

    /** Yeni seans acilamiyorsa kullaniciya gosterilecek sebep; acilabiliyorsa null. */
    public static function seansEngeli($adisyonPaketId, $hizmetId, $tarih): ?string
    {
        $d = self::durum($adisyonPaketId);
        $gun = date('Y-m-d', strtotime($tarih ?: 'today'));
        if ($d['bitis'] !== null && $gun > $d['bitis']) {
            return 'Paketin geçerlilik süresi '.date('d.m.Y', strtotime($d['bitis'])).' tarihinde doldu.';
        }
        if ($d['sinirsiz'] && $d['gunluk_limit'] > 0) {
            $bugunku = DB::table('adisyon_paket_seanslar')->where('adisyon_paket_id', $adisyonPaketId)
                ->where('hizmet_id', $hizmetId)->whereDate('seans_tarih', $gun)->count();
            if ($bugunku >= $d['gunluk_limit']) {
                return 'Bu pakette bu hizmet için günlük kullanım limiti '.$d['gunluk_limit'].'. '
                    .date('d.m.Y', strtotime($gun)).' tarihinde '.$bugunku.' kullanım zaten kayıtlı.';
            }
        }
        return null;
    }

    /**
     * Gecerli sinirsiz paket varsa once o kullanilir: musterinin parayla aldigi seansli paket
     * uyelik suresinde bosa tukenmesin. Donus, aday secim satirlariyla ayni alanlar + sinirsiz=1.
     */
    public static function sinirsizAday($salonId, $userId, $hizmetId, $tarih)
    {
        if (!self::kolonVar()) return null;
        $adaylar = DB::table('adisyon_paketler')
            ->join('adisyonlar', 'adisyon_paketler.adisyon_id', '=', 'adisyonlar.id')
            ->join('paket_hizmetler', 'paket_hizmetler.paket_id', '=', 'adisyon_paketler.paket_id')
            ->where('paket_hizmetler.hizmet_id', $hizmetId)
            ->where('adisyonlar.user_id', $userId)->where('adisyonlar.salon_id', $salonId)
            ->where('adisyon_paketler.sinirsiz', 1)
            ->orderBy('adisyonlar.tarih', 'asc')
            ->select('adisyon_paketler.id', 'adisyon_paketler.seans_sayisi', 'adisyon_paketler.sinirsiz',
                     'adisyon_paketler.kullanilan_seans', 'adisyon_paketler.kullanilmayan_seans',
                     'adisyonlar.tarih as adisyon_tarih')
            ->distinct()->get();
        foreach ($adaylar as $a) {
            if (self::seansUygun($a->id, $hizmetId, $tarih)) return $a;
        }
        return null;
    }
}
