<?php

namespace App\Services;

use App\Alacaklar;
use App\MusteriPortfoy;
use App\SalonEAsistanAyarlari;
use App\Jobs\HatirlatmaAramaJob;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Alacak (borc) hatirlatma aramalari. `alacaksms:hatirlat` icinden her dakika cagrilir
 * (eskiden ayri `alacakhatirlatma:aramayap` komutu her dakika alacaklar tablosunu
 * `LIKE` ile tariyordu -> indeks kullanilamiyor, her dakika full scan).
 *
 * Secim ZAMAN ARALIGI esaslidir: son islenen dakikadan (cache) su ana kadar olan
 * aralik. Boylece cron bir dakikayi kacirsa (onceki komut uzun surdu) arama kacmaz,
 * ayni dakika iki kez islenmez (withoutOverlapping + aralik ilerler).
 *   - Ilk hatirlatma: aralik bugun 10:00'i iceriyorsa, odeme tarihi 2 gun sonra olanlar.
 *   - Tekrar arama: tekrar_arama_tarih_saat araliga dusenler (sargable range).
 * Ayni musterinin birden fazla alacagi TEK aramada birlestirilir.
 */
class AlacakHatirlatmaAramasi
{
    const SON_DAKIKA_ANAHTARI = 'alacak_hatirlatma_aramasi_son_dk';
    /** ayar_id = 1: alacak hatirlatma aramasi acik/kapali. */
    const AYAR_ID_ALACAK = 1;
    const ILK_HATIRLATMA_SAATI = '10:00:00';
    /** Kesinti sonrasi en fazla bu kadar geriye bakilir (birikmis aramalar bir anda yagmasin). */
    const EN_FAZLA_GERI_DK = 120;

    protected $chunkSize = 50;
    protected $callDuration = 35;

    public function calistir()
    {
        $simdi = now();
        $bitis = $simdi->format('Y-m-d H:i:59');
        $enEski = $simdi->copy()->subMinutes(self::EN_FAZLA_GERI_DK)->format('Y-m-d H:i:59');
        $son = Cache::get(self::SON_DAKIKA_ANAHTARI);
        $baslangic = ($son && $son > $enEski) ? $son : $simdi->copy()->subMinute()->format('Y-m-d H:i:59');
        if ($baslangic >= $bitis) {
            return; // bu dakika zaten islendi
        }

        $ilkSaat = $simdi->format('Y-m-d') . ' ' . self::ILK_HATIRLATMA_SAATI;
        $ilkHatirlatma = ($ilkSaat > $baslangic && $ilkSaat <= $bitis);

        $alacaklar = Alacaklar::where(function ($q) use ($ilkHatirlatma, $baslangic, $bitis) {
                $q->where(function ($q2) use ($baslangic, $bitis) {
                    $q2->where('tekrar_arama_tarih_saat', '>', $baslangic)
                       ->where('tekrar_arama_tarih_saat', '<=', $bitis)
                       ->where(function ($q3) {
                           $q3->whereNull('hatirlatma_aramasi_yapildi')
                              ->orWhere('hatirlatma_aramasi_yapildi', '!=', 1);
                       });
                });
                if ($ilkHatirlatma) {
                    $q->orWhere(function ($q2) {
                        $q2->where('planlanan_odeme_tarihi', date('Y-m-d', strtotime('+2 days')))
                           ->where(function ($q3) {
                               $q3->whereNull('hatirlatma_aramasi_yapildi')
                                  ->orWhere('hatirlatma_aramasi_yapildi', '!=', 1);
                           });
                    });
                }
            })
            ->where(function ($q) {
                $q->whereNull('hatirlatma_gorevi_iptal')->orWhere('hatirlatma_gorevi_iptal', '!=', 1);
            })
            ->where(function ($q) {
                $q->whereNull('tekrar_arandi')->orWhere('tekrar_arandi', '!=', 1);
            })
            ->get();

        // Aralik islendi: hata olsa da ayni dakikalari tekrar isleyip mukerrer arama yapmayalim.
        Cache::forever(self::SON_DAKIKA_ANAHTARI, $bitis);

        if ($alacaklar->isEmpty()) {
            return;
        }

        $controller = app()->make(Controller::class);

        // Hatirlatma saatleri (10:00–19:30) disindaysa: arama, tekrar saatine ertele.
        if (!$controller->hatirlatmaSaatiIcinde(date('H:i'))) {
            $tekrarSaat = (date('H:i') >= '19:30')
                ? date('Y-m-d', strtotime('+1 days')) . ' 10:00:00'
                : date('Y-m-d') . ' 10:00:00';
            foreach ($alacaklar as $alacak) {
                $alacak->tekrar_arama_tarih_saat = $tekrarSaat;
                $alacak->save();
            }
            Log::info('[ALACAK-ARAMA] saat disinda, ' . $alacaklar->count() . ' alacak ' . $tekrarSaat . '\'e ertelendi.');
            return;
        }

        $gruplar = [];
        foreach ($alacaklar as $alacak) {
            $gruplar[$alacak->user_id . '_' . $alacak->salon_id][] = $alacak;
        }

        $ayarCache = [];
        $aramaListesi = [];
        foreach ($gruplar as $grup) {
            $parametre = $this->grupParametresi($grup, $ayarCache);
            if ($parametre !== null) {
                $aramaListesi[] = $parametre;
            }
        }

        if (empty($aramaListesi)) {
            Log::info('[ALACAK-ARAMA] filtre sonrasi aranacak musteri kalmadi.');
            return;
        }

        $chunks = array_chunk($aramaListesi, $this->chunkSize);
        foreach ($chunks as $i => $chunk) {
            $job = new HatirlatmaAramaJob($chunk, null, null);
            $gecikme = $i * $this->callDuration;
            if ($gecikme > 0) {
                $job->delay(now()->addSeconds($gecikme));
            }
            dispatch($job);
        }

        Log::info('[ALACAK-ARAMA] ' . count($aramaListesi) . ' musteri ' . count($chunks) . ' chunk halinde kuyruga eklendi.'
            . ($ilkHatirlatma ? ' (ilk-hatirlatma)' : ''));
    }

    /**
     * Bir musterinin alacak grubu icin arama parametresi; aranmamasi gerekiyorsa null.
     */
    protected function grupParametresi(array $grup, array &$ayarCache)
    {
        $ilk = $grup[0];
        $salonId = $ilk->salon_id;
        $userId = $ilk->user_id;

        $karaListedeMi = MusteriPortfoy::where('user_id', $userId)
            ->where('salon_id', $salonId)
            ->value('kara_liste');
        if ($karaListedeMi) {
            return null;
        }

        if (!array_key_exists($salonId, $ayarCache)) {
            $ayarCache[$salonId] = SalonEAsistanAyarlari::where('salon_id', $salonId)
                ->where('ayar_id', self::AYAR_ID_ALACAK)
                ->value('acik_kapali');
        }
        if (!$ayarCache[$salonId]) {
            return null;
        }

        if (!$ilk->musteri || !$ilk->musteri->cep_telefon) {
            return null;
        }

        $alacakIdler = [];
        $toplamTutar = 0;
        $tarihSeti = [];
        foreach ($grup as $alacak) {
            $alacakIdler[] = $alacak->id;
            $toplamTutar += (float) $alacak->tutar;
            if ($alacak->planlanan_odeme_tarihi) {
                $tarihSeti[date('d.m.Y', strtotime($alacak->planlanan_odeme_tarihi))] = true;
            }
        }
        $tarihler = implode(', ', array_keys($tarihSeti));
        $salonAdi = $ilk->salon ? $ilk->salon->salon_adi : '';

        $mesaj = 'Sayin ' . $ilk->musteri->name . '. Sizi ' . $salonAdi . ' adina ariyorum. ' .
            $tarihler . ' tarihinde odemeniz gereken toplam ' . $this->tutarMetni($toplamTutar) .
            ' TL borcunuz bulunmaktadir. Odemeyi gerceklestirecekseniz biri, ' .
            'vade guncellemesi icin operatore baglanmak istiyorsaniz ikiyi tuslayiniz.';

        return [
            'alacakIdler' => $alacakIdler,
            'randevuid' => '',
            'kampanyaKatilimci' => '',
            'katilimci' => '',
            'mesaj' => $mesaj,
            'tel' => $ilk->musteri->cep_telefon,
            'salonId' => $salonId,
            // 2 = [alacak-hatirlatma]; yeni asistan acik salonlarda 9 -> Stasis(randevu-santral-ai).
            'exten' => RandevuAsistan::aiHatirlatmaAcik($salonId) ? 9 : 2,
            'aiHedef' => 'alacak:' . implode('-', array_map('intval', (array) $alacakIdler)),
        ];
    }

    protected function tutarMetni($tutar)
    {
        return (floor($tutar) == $tutar)
            ? number_format($tutar, 0, ',', '.')
            : number_format($tutar, 2, ',', '.');
    }
}
