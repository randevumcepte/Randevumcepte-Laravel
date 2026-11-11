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

    /** Musteri ekraninda kaynak etiketleri (sira = ozet sirasi). */
    const KAYNAK_ETIKET = [
        'tahsilat' => 'Ödemeler',
        'randevu'  => 'Gelinen randevular',
        'cark'     => 'Çarkıfelek',
        'anket'    => 'Memnuniyet anketi',
        'google'   => 'Google yorumu',
        'harcama'  => 'Ödüle çevrilen',
        'diger'    => 'Diğer / önceki işlemler',
    ];

    /**
     * Musterinin bir salondaki puanlarinin NEREDEN geldigi (ve nereye harcandigi).
     *
     * Bakiyeyi yazan 5+ yol var (cark v1/v2, anket, Google yorumu, kazanim cron'u,
     * odul talebi) ve yalniz kazanim defterlidir. Bu yuzden gecmis OKUMA aninda kaynak
     * tablolardan kurulur; hicbir yazma yoluna dokunulmaz, eski puanlar da gorunur.
     *  - cark: carkifelek_cevirme_loglari (tip=puan) — kesin tutar.
     *  - tahsilat/randevu: sadakat_puan_hareketleri — kesin tutar.
     *  - anket / google: verilen tutar saklanmiyor -> sablonun / salonun GUNCEL puani.
     *  - harcama: puan odulu basligiyla eslesen kupon -> odulun GUNCEL esigi.
     * Bakiye ile toplam arasindaki fark "diger" satiriyla kapatilir (ozet daima bakiyeye esit).
     *
     * @return array ['ozet' => [[kaynak, etiket, puan]], 'hareketler' => [[tarih, kaynak, etiket, aciklama, puan]]]
     */
    public static function gecmis($salonId, $userId, $bakiye, $limit = 100)
    {
        $salonId = (int) $salonId;
        $userId  = (int) $userId;
        $h = [];
        $ekle = function ($tarih, $kaynak, $aciklama, $puan) use (&$h) {
            if ((float) $puan == 0) return;
            $h[] = [
                'tarih'    => $tarih ? date('Y-m-d H:i:s', strtotime($tarih)) : null,
                'kaynak'   => $kaynak,
                'etiket'   => self::KAYNAK_ETIKET[$kaynak],
                'aciklama' => (string) $aciklama,
                'puan'     => (int) round((float) $puan),
            ];
        };
        // Her kaynak bagimsiz: biri patlarsa (eksik kolon vb.) digerleri yine gelir.
        $guvenli = function (callable $f) {
            try { $f(); } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[sadakat gecmis] ' . $e->getMessage());
            }
        };

        $guvenli(function () use ($salonId, $userId, $ekle) {
            DB::table('carkifelek_cevirme_loglari')
                ->where('salon_id', $salonId)->where('user_id', $userId)
                ->where('tip', 'puan')->where('deger', '>', 0)
                ->get(['deger', 'dilim_ismi', 'created_at'])
                ->each(function ($r) use ($ekle) {
                    $ekle($r->created_at, 'cark', $r->dilim_ismi ?: 'Çark çevirme ödülü', $r->deger);
                });
        });

        $guvenli(function () use ($salonId, $userId, $ekle) {
            DB::table('sadakat_puan_hareketleri as h')
                ->leftJoin('tahsilatlar as t', function ($j) {
                    $j->on('t.id', '=', 'h.kaynak_id')->where('h.kaynak', '=', 'tahsilat');
                })
                ->leftJoin('randevular as r', function ($j) {
                    $j->on('r.id', '=', 'h.kaynak_id')->where('h.kaynak', '=', 'randevu');
                })
                ->where('h.salon_id', $salonId)->where('h.user_id', $userId)->where('h.puan', '>', 0)
                ->get(['h.kaynak', 'h.puan', 'h.created_at', 't.tutar', 't.odeme_tarihi', 'r.tarih'])
                ->each(function ($r) use ($ekle) {
                    if ($r->kaynak === 'tahsilat') {
                        $acik = $r->tutar !== null
                            ? number_format((float) $r->tutar, 2, ',', '.') . ' TL ödeme'
                            : 'Ödeme';
                        $ekle($r->odeme_tarihi ?: $r->created_at, 'tahsilat', $acik, $r->puan);
                    } elseif ($r->kaynak === 'randevu') {
                        $acik = $r->tarih ? date('d.m.Y', strtotime($r->tarih)) . ' tarihli randevu' : 'Randevu';
                        $ekle($r->tarih ?: $r->created_at, 'randevu', $acik, $r->puan);
                    }
                });
        });

        $guvenli(function () use ($salonId, $userId, $ekle) {
            DB::table('anket_gonderimleri as g')
                ->join('anket_sablonlari as s', 's.id', '=', 'g.sablon_id')
                ->where('g.salon_id', $salonId)->where('g.user_id', $userId)
                ->where('g.odul_verildi', 1)->whereNull('g.odul_kupon_id')
                ->where('s.odul_tipi', 'puan')->where('s.odul_puan', '>', 0)
                ->get(['s.odul_puan', 'g.cevap_zamani', 'g.updated_at'])
                ->each(function ($r) use ($ekle) {
                    $ekle($r->cevap_zamani ?: $r->updated_at, 'anket', 'Anketi yanıtladınız', $r->odul_puan);
                });
        });

        $guvenli(function () use ($salonId, $userId, $ekle) {
            $salon = DB::table('salonlar')->where('id', $salonId)->first(['google_odul_tipi', 'google_odul_puan']);
            if (!$salon || $salon->google_odul_tipi !== 'puan' || (float) $salon->google_odul_puan <= 0) return;
            DB::table('anket_gonderimleri')
                ->where('salon_id', $salonId)->where('user_id', $userId)
                ->where('google_odul_verildi', 1)->whereNull('google_odul_kupon_id')
                ->get(['updated_at'])
                ->each(function ($r) use ($ekle, $salon) {
                    $ekle($r->updated_at, 'google', 'Google’da yorum yaptınız', $salon->google_odul_puan);
                });
        });

        $guvenli(function () use ($salonId, $userId, $ekle) {
            $esikler = DB::table('salon_puan_odulleri')->where('salon_id', $salonId)
                ->orderBy('id')->get(['baslik', 'puan_esigi'])
                ->unique('baslik')->pluck('puan_esigi', 'baslik')->all();
            if (!$esikler) return;
            DB::table('carkifelek_odulleri')
                ->where('salon_id', $salonId)->where('user_id', $userId)
                ->whereNull('log_id')->whereIn('baslik', array_keys($esikler))
                ->get(['baslik', 'created_at'])
                ->each(function ($r) use ($ekle, $esikler) {
                    $ekle($r->created_at, 'harcama', $r->baslik . ' ödülü alındı', -((float) $esikler[$r->baslik]));
                });
        });

        // Ozet: kaynak bazli toplam + bakiyeye esitleyen fark satiri.
        $toplam = [];
        foreach ($h as $x) {
            $toplam[$x['kaynak']] = ($toplam[$x['kaynak']] ?? 0) + $x['puan'];
        }
        $fark = (int) round((float) $bakiye) - array_sum($toplam);
        if ($fark != 0) $toplam['diger'] = $fark;

        $ozet = [];
        foreach (self::KAYNAK_ETIKET as $k => $etiket) {
            if (!empty($toplam[$k])) $ozet[] = ['kaynak' => $k, 'etiket' => $etiket, 'puan' => (int) $toplam[$k]];
        }

        usort($h, function ($a, $b) { return strcmp((string) $b['tarih'], (string) $a['tarih']); });

        return ['ozet' => $ozet, 'hareketler' => array_slice($h, 0, $limit)];
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
