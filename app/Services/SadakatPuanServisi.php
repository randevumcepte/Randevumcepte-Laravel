<?php

namespace App\Services;

use App\Randevular;
use App\SadakatPuanHareketleri;
use App\SalonSadakatAyarlari;
use App\Tahsilatlar;
use Illuminate\Support\Facades\DB;

/**
 * Sadakat puani KAZANIM (Starbucks tarzi): her X TL tahsilata Y puan + gelinen
 * randevu basina Z puan. Kazanilan puan salon_sadakat_puanlari bakiyesine eklenir;
 * puan merdiveni (salon_puan_odulleri) ile kupona cevrilir.
 *
 * Neden mutabakat (cron) ve tahsilat koduna kanca degil: tahsilat 25+ yerden
 * (web, mobil, v2/New-Laravel, importlar) yaziliyor. Tek yerden, kaynak tablolara
 * bakarak calismak hicbir yazma yolunu kacirmaz.
 *
 * Kurallar:
 *  - Kaynak basina tek defter satiri (unique kaynak+kaynak_id) -> cift puan yok.
 *  - Verilen puan DONDURULUR: ayar sonradan degisse de eski kayit yeniden hesaplanmaz.
 *  - Kaynak silinir / randevu "gelmedi"ye doner / tutar 0'lanirsa puan geri alinir.
 *    Tahsilatin musterisi degisirse puan yeni musteriye tasinir.
 *  - Yalnizca ayar acildiktan (baslangic) sonraki ve son PENCERE_GUN icindeki
 *    kayitlara bakilir; geriye donuk import'lar puan kazanmaz.
 */
class SadakatPuanServisi
{
    const PENCERE_GUN = 90;

    public static function ayar($salonId)
    {
        return SalonSadakatAyarlari::where('salon_id', (int) $salonId)->first();
    }

    public static function ayarDizi($ayar)
    {
        return [
            'aktif'          => $ayar ? (int) $ayar->aktif : 0,
            'harcama_tutari' => $ayar ? (float) $ayar->harcama_tutari : 100.0,
            'harcama_puani'  => $ayar ? (int) $ayar->harcama_puani : 0,
            'ziyaret_puani'  => $ayar ? (int) $ayar->ziyaret_puani : 0,
            'baslangic'      => $ayar && $ayar->baslangic ? date('d.m.Y H:i', strtotime($ayar->baslangic)) : null,
        ];
    }

    /**
     * Ayari kaydeder. Kapali->acik geciste baslangic simdi olur: o ana kadarki
     * tahsilat/randevular puan kazanmaz (geriye donuk toplu puan dagitilmaz).
     */
    public static function ayarKaydet($salonId, array $girdi)
    {
        $ayar = SalonSadakatAyarlari::firstOrNew(['salon_id' => (int) $salonId]);
        $oncekiAktif = (int) $ayar->aktif;

        $ayar->aktif          = !empty($girdi['aktif']) ? 1 : 0;
        $ayar->harcama_tutari = max(1, (float) ($girdi['harcama_tutari'] ?? 100));
        $ayar->harcama_puani  = max(0, (int) ($girdi['harcama_puani'] ?? 0));
        $ayar->ziyaret_puani  = max(0, (int) ($girdi['ziyaret_puani'] ?? 0));
        if ($ayar->aktif && !$oncekiAktif) {
            $ayar->baslangic = date('Y-m-d H:i:s');
        }
        $ayar->save();

        return $ayar;
    }

    public static function tahsilatPuani($ayar, $tutar)
    {
        if ((int) $ayar->harcama_puani <= 0 || (float) $ayar->harcama_tutari <= 0) return 0;
        return (int) floor(((float) $tutar) / (float) $ayar->harcama_tutari) * (int) $ayar->harcama_puani;
    }

    /**
     * Bir salonun pencere icindeki tahsilat ve randevularini defterle esitler.
     * @return array ['verilen' => puan, 'geri_alinan' => puan, 'islem' => satir]
     */
    public static function mutabakat(SalonSadakatAyarlari $ayar, $dry = false)
    {
        $sonuc = ['verilen' => 0, 'geri_alinan' => 0, 'islem' => 0];
        if (!(int) $ayar->aktif || !$ayar->baslangic) return $sonuc;

        $salonId = (int) $ayar->salon_id;
        $pencere = max(strtotime($ayar->baslangic), strtotime('-' . self::PENCERE_GUN . ' days'));
        $pencereZaman = date('Y-m-d H:i:s', $pencere);
        $baslangicGun = date('Y-m-d', strtotime($ayar->baslangic));

        // --- Tahsilat (harcama) ---
        $tahsilatlar = Tahsilatlar::where('salon_id', $salonId)
            ->whereNotNull('user_id')
            ->where('tutar', '>', 0)
            ->where('created_at', '>=', $pencereZaman)
            ->where('odeme_tarihi', '>=', $baslangicGun)
            ->get(['id', 'user_id', 'tutar']);
        self::kaynaklariIsle($salonId, 'tahsilat', $tahsilatlar, function ($t) use ($ayar) {
            return self::tahsilatPuani($ayar, $t->tutar);
        }, $dry, $sonuc);
        self::kayiplariGeriAl($salonId, 'tahsilat', $tahsilatlar->pluck('id')->all(), function ($idler) {
            return Tahsilatlar::whereIn('id', $idler)->whereNotNull('user_id')->where('tutar', '>', 0)->pluck('user_id', 'id')->all();
        }, $dry, $sonuc);

        // --- Gelinen randevu (ziyaret) ---
        $randevular = Randevular::where('salon_id', $salonId)
            ->whereNotNull('user_id')
            ->where('durum', Randevular::ONAYLANDI)
            ->where('randevuya_geldi', 1)
            ->where('tarih', '>=', date('Y-m-d', $pencere))
            ->where('tarih', '<=', date('Y-m-d'))
            ->get(['id', 'user_id']);
        self::kaynaklariIsle($salonId, 'randevu', $randevular, function ($r) use ($ayar) {
            return (int) $ayar->ziyaret_puani;
        }, $dry, $sonuc);
        self::kayiplariGeriAl($salonId, 'randevu', $randevular->pluck('id')->all(), function ($idler) {
            return Randevular::whereIn('id', $idler)->whereNotNull('user_id')
                ->where('durum', Randevular::ONAYLANDI)->where('randevuya_geldi', 1)
                ->pluck('user_id', 'id')->all();
        }, $dry, $sonuc);

        return $sonuc;
    }

    private static function kaynaklariIsle($salonId, $kaynak, $kayitlar, callable $puanHesapla, $dry, array &$sonuc)
    {
        foreach ($kayitlar->chunk(1000) as $parca) {
            $defter = SadakatPuanHareketleri::where('kaynak', $kaynak)
                ->whereIn('kaynak_id', $parca->pluck('id')->all())
                ->get()->keyBy('kaynak_id');

            foreach ($parca as $k) {
                $satir = $defter->get($k->id);
                // Verilmis puan dondurulur; yalniz musteri degistiyse tasinir.
                if ($satir && (float) $satir->puan > 0) {
                    if ((int) $satir->user_id !== (int) $k->user_id) {
                        self::uygula($satir, $salonId, $kaynak, $k->id, (int) $k->user_id, (float) $satir->puan, $dry, $sonuc);
                    }
                    continue;
                }
                $puan = $puanHesapla($k);
                if ($puan <= 0) continue;
                self::uygula($satir, $salonId, $kaynak, $k->id, (int) $k->user_id, $puan, $dry, $sonuc);
            }
        }
    }

    /**
     * Defterde puanli olup bu turda kaynak listesinde olmayanlari tek tek dogrular:
     * kaynak gercekten yoksa/gecersizse puan geri alinir (pencere sinirindaki ama
     * hala gecerli kayitlara dokunulmaz).
     */
    private static function kayiplariGeriAl($salonId, $kaynak, array $gorulenIdler, callable $gecerliler, $dry, array &$sonuc)
    {
        $gorulen = array_flip($gorulenIdler);
        $adaylar = SadakatPuanHareketleri::where('salon_id', $salonId)
            ->where('kaynak', $kaynak)
            ->where('puan', '>', 0)
            ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-' . self::PENCERE_GUN . ' days')))
            ->get()
            ->filter(function ($s) use ($gorulen) { return !isset($gorulen[$s->kaynak_id]); });

        foreach ($adaylar->chunk(1000) as $parca) {
            $gecerli = $gecerliler($parca->pluck('kaynak_id')->all());
            foreach ($parca as $satir) {
                if (isset($gecerli[$satir->kaynak_id])) continue;
                self::uygula($satir, $salonId, $kaynak, $satir->kaynak_id, (int) $satir->user_id, 0, $dry, $sonuc);
            }
        }
    }

    private static function uygula($satir, $salonId, $kaynak, $kaynakId, $userId, $puan, $dry, array &$sonuc)
    {
        $eski = $satir ? (float) $satir->puan : 0;
        $sonuc['islem']++;
        if ($puan > $eski || ($satir && (int) $satir->user_id !== $userId)) $sonuc['verilen'] += $puan;
        if ($puan < $eski || ($satir && (int) $satir->user_id !== $userId)) $sonuc['geri_alinan'] += $eski;
        if ($dry) return;

        DB::transaction(function () use ($satir, $salonId, $kaynak, $kaynakId, $userId, $puan, $eski) {
            if ($satir && $eski > 0) {
                self::bakiyeEkle($salonId, (int) $satir->user_id, -$eski);
            }
            if ($puan > 0) {
                self::bakiyeEkle($salonId, $userId, $puan);
            }
            if (!$satir) {
                $satir = new SadakatPuanHareketleri([
                    'salon_id' => $salonId, 'kaynak' => $kaynak, 'kaynak_id' => $kaynakId,
                ]);
            }
            $satir->user_id = $userId;
            $satir->puan    = $puan;
            $satir->save();
        });
    }

    /** Atomik bakiye degisimi (puan talebiyle yaris olmasin diye read-modify-write yok). */
    private static function bakiyeEkle($salonId, $userId, $delta)
    {
        $etkilenen = DB::table('salon_sadakat_puanlari')
            ->where('salon_id', $salonId)->where('user_id', $userId)
            ->increment('puan', $delta, ['updated_at' => date('Y-m-d H:i:s')]);
        if (!$etkilenen) {
            DB::table('salon_sadakat_puanlari')->insert([
                'salon_id' => $salonId, 'user_id' => $userId, 'puan' => $delta,
                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
