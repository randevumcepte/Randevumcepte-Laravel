<?php

namespace App\Services;

use App\DersKatilimci;
use App\DersOturumu;
use App\DersProgramiSablonu;
use App\DersTekrarliKatilim;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Tekrarli otomatik ders katilimi dagitici (studyo modu).
 *
 * Bir tekrarli kayit icin: musterinin secili gun/saat/hoca tercihine gore
 * ILERI tarihli grup dersi oturumlarina otomatik katilimci ekler.
 *
 * Kurallar (kullanici onayli):
 *  - Dolu oturum: ATLA, sonraki uygun gune gec (o hafta seans harcanmaz).
 *  - O gun/saatte oturum YOKSA: eslesen sablondan oturum URET (grup dersi ekle
 *    mantigi), sonra katilimci ekle.
 *  - Yeniden dagitimda GECMIS korunur; yalnizca gelecekteki otomatik katilimlar
 *    silinip yeniden dagitilir.
 *
 * Dusum burada YAPILMAZ — seans "Geldi/Gelmedi" isaretlenince dusulur
 * (DersRezervasyonServisi ile ayni ilke).
 */
class DersOtomatikKatilimServisi
{
    const MAX_GUN = 371; // en fazla ~1 yil ileri tara

    /**
     * Kaydin gelecekteki otomatik katilimlarini temizleyip yeniden dagitir.
     *
     * @return array ['olusan'=>int,'oturum_olusturulan'=>int,'dolu_atlanan'=>int,
     *                'yerlesmeyen'=>int,'hedef'=>int,'gecmis'=>int]
     */
    public static function dagit(DersTekrarliKatilim $kayit): array
    {
        $bugun = Carbon::today();

        // 1) Gelecekteki (bugun dahil ileri) otomatik katilimlari temizle. Gecmis korunur.
        //    Sadece bu kayda ait (tekrarli_id) ve henuz gerceklesmemis (rezerve/bekleme) olanlar.
        $silinecekler = DersKatilimci::where('tekrarli_id', $kayit->id)
            ->whereIn('durum', ['rezerve', 'bekleme'])
            ->whereHas('oturum', function ($q) use ($bugun) {
                $q->where('tarih', '>=', $bugun->toDateString());
            })->get();
        foreach ($silinecekler as $k) {
            if ($k->hak_dusuldu || $k->aps_id) {
                DersSeansServisi::dusumGeriAl($k); // guvenlik; normalde rezervede dusum olmaz
            }
            $k->delete();
        }

        // 2) Kalan hedef = toplam - halihazirda bu kayda bagli (iptal disi) katilimlar (gecmis + korunan).
        $mevcutSayi = DersKatilimci::where('tekrarli_id', $kayit->id)
            ->where('durum', '!=', 'iptal')->count();
        $hedef = (int) $kayit->toplam_seans;
        $kalan = max(0, $hedef - $mevcutSayi);

        $sonuc = [
            'olusan' => 0, 'oturum_olusturulan' => 0, 'dolu_atlanan' => 0,
            'yerlesmeyen' => 0, 'hedef' => $hedef, 'gecmis' => $mevcutSayi,
        ];
        if ($kalan <= 0 || !$kayit->aktif) {
            return $sonuc;
        }

        $gunler  = $kayit->gunlerDizi();   // [1..7], 1=Pzt (ISO)
        $saatler = $kayit->saatlerDizi();  // ["09:00",..] veya [] = hepsi
        if (empty($gunler)) {
            return $sonuc; // gun secilmemis -> dagitilamaz
        }

        // Eslesen sablonlar (haftalik). Gun bazli gruplayalim.
        $sablonQuery = DersProgramiSablonu::where('salon_id', $kayit->salon_id)
            ->where('aktif', true)
            ->whereIn('hafta_gunu', $gunler);
        if ($kayit->hizmet_id) {
            $sablonQuery->where('hizmet_id', $kayit->hizmet_id);
        }
        if ($kayit->personel_id) {
            $sablonQuery->where('personel_id', $kayit->personel_id);
        }
        $sablonlar = $sablonQuery->orderBy('saat')->get();
        // Saat filtresi (HH:MM) uygula
        if (!empty($saatler)) {
            $sablonlar = $sablonlar->filter(function ($s) use ($saatler) {
                return in_array(substr($s->saat, 0, 5), $saatler, true);
            })->values();
        }
        if ($sablonlar->isEmpty()) {
            $sonuc['yerlesmeyen'] = $kalan;
            return $sonuc; // tercihe uyan sablon yok
        }

        // Gun -> sablon listesi
        $gunSablon = [];
        foreach ($sablonlar as $s) {
            $gunSablon[(int) $s->hafta_gunu][] = $s;
        }

        $baslangic = $kayit->baslangic_tarihi
            ? Carbon::parse($kayit->baslangic_tarihi)->startOfDay()
            : $bugun->copy();
        if ($baslangic->lt($bugun)) {
            $baslangic = $bugun->copy();
        }

        $tarih = $baslangic->copy();
        for ($i = 0; $i < self::MAX_GUN && $kalan > 0; $i++, $tarih->addDay()) {
            $iso = (int) $tarih->dayOfWeekIso; // 1=Pzt..7=Paz
            if (!isset($gunSablon[$iso])) {
                continue;
            }
            // Bu gunun eslesen sablonlarini saat sirasiyla dene; ilk uygun yere yerlestir.
            foreach ($gunSablon[$iso] as $s) {
                // Gecmis saat ise (bugun) atla
                if ($tarih->isSameDay($bugun) && strtotime($tarih->toDateString() . ' ' . $s->saat) < time()) {
                    continue;
                }
                $yerlesti = self::slotaYerlestir($kayit, $s, $tarih->toDateString(), $sonuc);
                if ($yerlesti) {
                    $kalan--;
                    break; // gun basina 1 seans -> sonraki uygun gune gec
                }
            }
        }

        $sonuc['yerlesmeyen'] = $kalan;
        return $sonuc;
    }

    /**
     * Bir sablon+tarih icin oturumu bul (yoksa sablondan uret), dolu degilse
     * katilimci ekle. Basari => true.
     */
    protected static function slotaYerlestir(DersTekrarliKatilim $kayit, DersProgramiSablonu $s, string $tarih, array &$sonuc): bool
    {
        return DB::transaction(function () use ($kayit, $s, $tarih, &$sonuc) {
            // Oturum var mi? (sablon_id + tarih)
            $oturum = DersOturumu::where('salon_id', $kayit->salon_id)
                ->where('sablon_id', $s->id)->where('tarih', $tarih)
                ->lockForUpdate()->first();

            if (!$oturum) {
                // Grup dersi ekle mantigi: sablondan oturum uret
                $oturum = DersOturumu::create([
                    'salon_id'    => $s->salon_id,
                    'sube_id'     => $s->sube_id,
                    'personel_id' => $s->personel_id,
                    'ders_tipi'   => $s->ders_tipi,
                    'hizmet_id'   => $s->hizmet_id,
                    'tarih'       => $tarih,
                    'saat'        => $s->saat,
                    'saat_bitis'  => $s->saat_bitis,
                    'kapasite'    => $s->kapasite,
                    'sablon_id'   => $s->id,
                    'renk'        => $s->renk,
                    'aktif'       => true,
                ]);
                $sonuc['oturum_olusturulan']++;
            } elseif ($oturum->iptal || !$oturum->aktif) {
                return false;
            }

            // Musteri bu oturumda zaten var mi?
            $zaten = DersKatilimci::where('oturum_id', $oturum->id)
                ->where('user_id', $kayit->user_id)->where('durum', '!=', 'iptal')->first();
            if ($zaten) {
                return false;
            }

            // Kapasite dolu mu? -> atla (sonraki gune)
            $aktifSayi = DersKatilimci::where('oturum_id', $oturum->id)
                ->whereNotIn('durum', ['iptal', 'bekleme'])->count();
            if ($aktifSayi >= (int) $oturum->kapasite) {
                $sonuc['dolu_atlanan']++;
                return false;
            }

            DersKatilimci::create([
                'oturum_id'        => $oturum->id,
                'user_id'          => $kayit->user_id,
                'salon_id'         => $kayit->salon_id,
                'durum'            => 'rezerve',
                'adisyon_paket_id' => $kayit->adisyon_paket_id,
                'tekrarli_id'      => $kayit->id,
                'not'              => 'otomatik',
            ]);
            $sonuc['olusan']++;
            return true;
        });
    }

    /**
     * Kaydi silmeden gelecekteki otomatik katilimlari temizler (kayit silinince).
     */
    public static function gelecekTemizle(DersTekrarliKatilim $kayit): int
    {
        $bugun = Carbon::today()->toDateString();
        $silinecekler = DersKatilimci::where('tekrarli_id', $kayit->id)
            ->whereIn('durum', ['rezerve', 'bekleme'])
            ->whereHas('oturum', function ($q) use ($bugun) {
                $q->where('tarih', '>=', $bugun);
            })->get();
        $n = 0;
        foreach ($silinecekler as $k) {
            if ($k->hak_dusuldu || $k->aps_id) {
                DersSeansServisi::dusumGeriAl($k);
            }
            $k->delete();
            $n++;
        }
        return $n;
    }
}
