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
    /**
     * Mantik App\Services\AlacakHatirlatmaAramasi'ndadir; zamanlayicida artik
     * `alacaksms:hatirlat` icinden calisir (bu komut elle calistirma/geri donus icin).
     */
    public function handle()
    {
        app(\App\Services\AlacakHatirlatmaAramasi::class)->calistir();
    }
}
