<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Alacaklar;
use App\MusteriPortfoy;
use App\SalonEAsistanAyarlari;
use App\Jobs\HatirlatmaAramaJob;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Alacak (borc) hatirlatma aramalari. Odeme tarihinden 2 gun once VE planlanan
 * tekrar arama dakikasinda, henuz hatirlatilmamis alacaklari santralden arar.
 *
 * Ayni musterinin birden fazla alacagi varsa TEK arama yapilir; mesajda toplam
 * tutar ve tarihler birlestirilir, isaretleme tum alacak id'lerine uygulanir.
 *
 * MUKERRER ARAMA KORUMASI (bkz. KampanyaAramaYap): Komut her dakika calisir,
 * isaretleme cagri sonrasi (asenkron worker) yapilir. Secim TAM DAKIKA esaslidir:
 *   - Ilk hatirlatma: yalnizca gunde bir kez, hatirlatma penceresinin basinda
 *     (10:00) tetiklenir; odeme tarihi 2 gun sonra olan alacaklar.
 *   - Tekrar arama: tekrar_arama_tarih_saat DAKIKASI == su anki dakika.
 * Boylece ayni alacak ayni gun icinde birden fazla kez kuyruga girmez.
 *
 * Aramalar HatirlatmaAramaJob ile asenkron kuyruga (database, queue=hatirlatmalar)
 * eklenir; isleyici:  php artisan queue:work database --queue=hatirlatmalar,notifications
 */
class AlacakHatirlatmaAramasiYap extends Command
{
    protected $signature = 'alacakhatirlatma:aramayap';
    protected $description = 'Alacak (borc) hatirlatma aramalarini kuyruga ekler';

    protected $chunkSize = 50;
    protected $callDuration = 35;

    /** ayar_id = 1: alacak hatirlatma aramasi acik/kapali. */
    const AYAR_ID_ALACAK = 1;
    /** Ilk hatirlatmanin gonderildigi sabit dakika (hatirlatma penceresi basi). */
    const ILK_HATIRLATMA_SAATI = '10:00';

    public function handle()
    {
        $controller = app()->make(Controller::class);
        $nowMin = now()->format('Y-m-d H:i');
        $ilkHatirlatmaDakikasi = (date('H:i') === self::ILK_HATIRLATMA_SAATI);

        Log::info('[ALACAK-ARAMA] kontrol basladi. dk=' . $nowMin . ($ilkHatirlatmaDakikasi ? ' (ilk-hatirlatma)' : ''));

        $alacaklar = Alacaklar::where(function ($q) use ($ilkHatirlatmaDakikasi, $nowMin) {
                // Tekrar arama: planlanan dakika su an.
                $q->where(function ($q2) use ($nowMin) {
                    $q2->where('tekrar_arama_tarih_saat', 'like', $nowMin . '%')
                       ->where(function ($q3) {
                           $q3->whereNull('hatirlatma_aramasi_yapildi')
                              ->orWhere('hatirlatma_aramasi_yapildi', '!=', 1);
                       });
                });
                // Ilk hatirlatma: yalnizca 10:00 dakikasinda, odeme tarihi 2 gun sonra.
                if ($ilkHatirlatmaDakikasi) {
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

        if ($alacaklar->isEmpty()) {
            Log::info('[ALACAK-ARAMA] aranacak alacak yok.');
            return;
        }

        // Hatirlatma saatleri (10:00–19:30) disindaysa: arama, tekrar saatine ertele.
        if (!$controller->hatirlatmaSaatiIcinde(date('H:i'))) {
            $tekrarSaat = (date('H:i') > date('H:i', strtotime('19:30')))
                ? date('Y-m-d', strtotime('+1 days')) . ' 10:00:00'
                : date('Y-m-d') . ' 10:00:00';
            foreach ($alacaklar as $alacak) {
                $alacak->tekrar_arama_tarih_saat = $tekrarSaat;
                $alacak->save();
            }
            Log::info('[ALACAK-ARAMA] saat disinda, ' . $alacaklar->count() . ' alacak ' . $tekrarSaat . '\'e ertelendi.');
            return;
        }

        // Musteri (user_id + salon_id) bazinda grupla — tek aramada tum borclar.
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

        $toplam = count($aramaListesi);
        $chunks = array_chunk($aramaListesi, $this->chunkSize);
        foreach ($chunks as $i => $chunk) {
            $job = new HatirlatmaAramaJob($chunk, null, null);
            $gecikme = $i * $this->callDuration;
            if ($gecikme > 0) {
                $job->delay(now()->addSeconds($gecikme));
            }
            dispatch($job);
        }

        Log::info("[ALACAK-ARAMA] {$toplam} musteri " . count($chunks) . ' chunk halinde kuyruga eklendi.');
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
            'exten' => 1,
        ];
    }

    protected function tutarMetni($tutar)
    {
        return (floor($tutar) == $tutar)
            ? number_format($tutar, 0, ',', '.')
            : number_format($tutar, 2, ',', '.');
    }
}
