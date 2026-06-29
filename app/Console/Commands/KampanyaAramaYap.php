<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\KampanyaYonetimi;
use App\SalonEAsistanAyarlari;
use App\Jobs\HatirlatmaAramaJob;
use App\Jobs\SendCompletionNotification;

/**
 * Reklam (kampanya) hatirlatma aramalari — eczane24'teki sistemin RandevuMcepte
 * uyarlamasi. Yuksek hacimli oldugu icin chunk(50) + asenkron kuyruk (database
 * connection, queue=hatirlatmalar) + chunk'lar arasi delay ile yayilarak
 * santral kanallarini tasirmadan calistirilir.
 *
 * MUKERRER ARAMA KORUMASI: Komut her dakika calisir; isaretleme ise cagri sonrasi
 * (asenkron worker) yapilir. Eger secim kosulu surekli dogru kalsaydi (orn.
 * asistan_tarih_saat <= now) ayni kisi isaretlenene kadar HER DAKIKA yeniden
 * kuyruga girer ve birden cok kez aranirdi. Bunu onlemek icin secim TAM DAKIKA
 * esaslidir (randevuarama:yap ile ayni desen):
 *   - Ilk arama: kampanyanin asistan_tarih_saat DAKIKASI == su anki dakika.
 *   - Tekrar arama: katilimci.tekrar_arama_tarih_saat DAKIKASI == su anki dakika.
 * Boylece her katilimci yalnizca tek bir dakikada kuyruga alinir.
 *
 * NOT: Laravel 5.6'da Bus::batch YOK; duz dispatch + ->delay() kullanilir.
 * Joblari isleyen surec calismali:
 *   php artisan queue:work database --queue=hatirlatmalar,notifications
 */
class KampanyaAramaYap extends Command
{
    protected $signature = 'kampanyaarama:yap';
    protected $description = 'Reklam (kampanya) hatirlatma aramalarini kuyruga ekler';

    /** Santral kanal limitine gore chunk basina arama. */
    protected $chunkSize = 50;
    /** Bir chunk'in ortalama suresi (sn) — chunk'lar bu kadar arayla baslar. */
    protected $callDuration = 35;

    /** ayar_id = 8: reklam/kampanya aramasi acik/kapali. */
    const AYAR_ID_KAMPANYA = 8;

    public function handle()
    {
        $nowMin = now()->format('Y-m-d H:i');
        Log::info('[REKLAM-ARAMA] kontrol basladi. dk=' . $nowMin);

        KampanyaYonetimi::where('aktifmi', 1)
            ->where('arama_ile_gonderim', 1)
            ->where('asistan_tarih_saat', '<=', now())
            ->with(['salon:id,santral_telaffuz_hatirlatma_aramasi'])
            ->chunk(20, function ($kampanyalar) use ($nowMin) {
                foreach ($kampanyalar as $kampanya) {
                    $this->kampanyayiIsle($kampanya, $nowMin);
                }
            });

        Log::info('[REKLAM-ARAMA] kontrol tamamlandi.');
    }

    protected function kampanyayiIsle($kampanya, $nowMin)
    {
        if ($kampanya->hatirlatma_gorevi_iptal) {
            return;
        }

        $ilkAramaDakikasi = (date('Y-m-d H:i', strtotime($kampanya->asistan_tarih_saat)) === $nowMin);

        // Bu kampanya bu dakikada ya yeni baslar ya da tekrar aramasi olanlari
        // vardir. Katilimci sorgusu TAM DAKIKA esasli kurulur.
        $sorgu = $kampanya->kampanya_katilimcilari()
            ->with(['musteri:id,name,cinsiyet,cep_telefon'])
            ->select('id', 'user_id', 'kampanya_id', 'tekrar_arandi', 'tekrar_aranacak', 'tekrar_arama_tarih_saat', 'durum_asistan', 'kilitli')
            ->whereNull('durum_asistan')
            ->where(function ($q) {
                $q->whereNull('kilitli')->orWhere('kilitli', '!=', 1);
            });

        if ($ilkAramaDakikasi) {
            // Ilk arama partisi: henuz hic aranmamis katilimcilar.
            $sorgu->whereNull('tekrar_arandi')->whereNull('tekrar_aranacak');
        } else {
            // Sadece tekrar arama zamani su an olan katilimcilar.
            $sorgu->where('tekrar_aranacak', 1)
                  ->where(function ($q) {
                      $q->whereNull('tekrar_arandi')->orWhere('tekrar_arandi', '!=', 1);
                  })
                  ->where('tekrar_arama_tarih_saat', 'like', $nowMin . '%');
        }

        // Ayar kontrolu (kampanya basina tek sorgu) — kapaliysa hic dolasma.
        $ayarAcik = SalonEAsistanAyarlari::where('salon_id', $kampanya->salon_id)
            ->where('ayar_id', self::AYAR_ID_KAMPANYA)
            ->value('acik_kapali');
        if (!$ayarAcik) {
            return;
        }

        $tumListe = [];
        $sorgu->chunk(200, function ($katilimcilar) use ($kampanya, &$tumListe) {
            foreach ($katilimcilar as $katilimci) {
                if (!$katilimci->musteri || !$katilimci->musteri->cep_telefon) {
                    continue;
                }
                $tumListe[] = $this->katilimciParametresi($katilimci, $kampanya);
            }
        });

        if (empty($tumListe)) {
            return;
        }

        $toplam = count($tumListe);
        Log::info("[REKLAM-ARAMA] kampanya {$kampanya->id} / salon {$kampanya->salon_id}: {$toplam} arama (" .
            ($ilkAramaDakikasi ? 'ilk' : 'tekrar') . ').');

        // 50'serli chunk'lara bol, her chunk'i 35sn arayla kuyruga koy.
        $chunks = array_chunk($tumListe, $this->chunkSize);
        foreach ($chunks as $i => $chunk) {
            $job = new HatirlatmaAramaJob($chunk, $kampanya->salon_id, $kampanya->id);
            $gecikme = $i * $this->callDuration;
            if ($gecikme > 0) {
                $job->delay(now()->addSeconds($gecikme));
            }
            dispatch($job);
        }

        Log::info("[REKLAM-ARAMA] {$toplam} arama " . count($chunks) . " chunk halinde kuyruga eklendi (kampanya {$kampanya->id}).");

        // Yoneticilere "tamamlandi" bildirimi yalnizca ilk arama partisinde.
        if ($ilkAramaDakikasi) {
            SendCompletionNotification::dispatch($toplam, $kampanya->salon_id, $kampanya->id);
        }
    }

    /**
     * Bir katilimci icin arama parametresi (santral icin) uretir.
     */
    protected function katilimciParametresi($katilimci, $kampanya)
    {
        $cinsiyetStr = $katilimci->musteri->cinsiyet === 0 ? 'hanim' : 'bey';
        $ilkAd = explode(' ', trim($katilimci->musteri->name))[0];
        $hitap = $ilkAd . ' ' . $cinsiyetStr;

        $mesaj = 'Merhaba ' . $hitap . '. Sizi ' .
            $kampanya->salon->santral_telaffuz_hatirlatma_aramasi . ' ariyorum. ' .
            'Umarim gununuz saglikli geciyordur. ' . $kampanya->mesaj;

        return [
            'alacakIdler' => '',
            'randevuid' => '',
            'kampanyaKatilimci' => $katilimci->id,
            'katilimci' => $katilimci->id,
            'mesaj' => $mesaj,
            'tel' => $katilimci->musteri->cep_telefon,
            'salonId' => $kampanya->salon_id,
            'exten' => 3,
        ];
    }
}
