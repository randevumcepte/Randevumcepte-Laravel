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

    /**
     * DAKIKA BASINA MAKSIMUM ARAMA (tum kampanyalar toplami). OLCEKLENME KORUMASI:
     * QUEUE_DRIVER=sync oldugu icin isler inline calisir; tavan olmadan tek koşuda
     * BINLERCE Originate atilir -> santral kanal tasmasi + dakikalarca blok + sisme.
     * Komut her dakika calistigindan, koşu basina bu kadarla sinirlarsak buyuk kampanya
     * kendiliginden dakikalara yayilir (bu koşuda alinanlar kilitli=1, kalanlar sonraki
     * dakika). env KAMPANYA_ARAMA_MAX_PER_RUN ile ayarlanir; santral es zamanli kanal
     * kapasitesine gore secilmeli.
     */
    protected $maxAramaPerRun = 40;
    /** Bu koşuda kalan arama butcesi (handle icinde doldurulur). */
    protected $kalanButce = 0;

    /** ayar_id = 8: reklam/kampanya aramasi acik/kapali. */
    const AYAR_ID_KAMPANYA = 8;

    public function handle()
    {
        $nowMin = now()->format('Y-m-d H:i');
        $this->kalanButce = (int) env('KAMPANYA_ARAMA_MAX_PER_RUN', $this->maxAramaPerRun);
        Log::info("[REKLAM-ARAMA] kontrol basladi. dk={$nowMin} (dakika butcesi={$this->kalanButce})");

        // TAM TARAMA GARANTISI: takili kalan kilitleri coz (cevapsiz cagri -> santral
        // geri bildirmedigi icin kilitli=1 kalir; cozulmezse o kisi kalici atlanir).
        // Ayrica ulasilamama 1-tekrar mantigini burada uygular.
        $this->kilitliKurtar();

        // Teshis: kac kampanya uygun (aktifmi=1, arama_ile_gonderim=1, zamani gelmis)?
        $uygunKampanya = KampanyaYonetimi::where('aktifmi', 1)
            ->where('arama_ile_gonderim', 1)
            ->where('asistan_tarih_saat', '<=', now())
            ->count();
        Log::info("[REKLAM-ARAMA] uygun kampanya: {$uygunKampanya} (aktifmi=1 & arama_ile_gonderim=1 & asistan_tarih_saat<=now).");

        KampanyaYonetimi::where('aktifmi', 1)
            ->where('arama_ile_gonderim', 1)
            ->where('asistan_tarih_saat', '<=', now())
            ->with(['salon:id,santral_telaffuz_hatirlatma_aramasi,salon_adi'])
            ->chunk(20, function ($kampanyalar) use ($nowMin) {
                foreach ($kampanyalar as $kampanya) {
                    if ($this->kalanButce <= 0) {
                        return false; // dakika butcesi doldu -> chunk dongusunu durdur
                    }
                    $this->kampanyayiIsle($kampanya, $nowMin);
                }
            });

        if ($this->kalanButce <= 0) {
            Log::info("[REKLAM-ARAMA] dakika butcesi doldu; kalan katilimcilar sonraki dakika(lar) aranacak (kilitli degil).");
        }
        Log::info('[REKLAM-ARAMA] kontrol tamamlandi.');
    }

    /**
     * TAKILI KILIT KURTARMA — tam tarama garantisi + ulasilamama 1-tekrar.
     *
     * Cevapsiz/cevaplanmamis cagrilar kilitli=1 takili kalir (santral cevapsiz cagriyi
     * geri bildirmez -> isaretle fonksiyonu cagrilmaz). Cozulmezse o katilimci KALICI
     * ATLANIR. Cagri suresinden (KAMPANYA_KILIT_TIMEOUT_DK, default 5dk) eskiyen ve hala
     * cevaplanmamis (durum_asistan NULL) kilitleri cozeriz:
     *   - Ilk deneme cevapsiz (tekrar planlanmamis) -> tekrar_aranacak=1 + zaman -> 1 KEZ DAHA aranir.
     *   - Tekrar da cevapsiz (tekrar_aranacak=1'di) -> tekrar_arandi=1 -> FINAL, bir daha aranmaz.
     * Her iki halde kilitli=0 -> asla kalici takili kalmaz. asistan_ulasamadi=1 raporlanir.
     * SIRA ONEMLI: once FINAL (tekrar_aranacak=1 olanlar), sonra ILK (tekrar_aranacak!=1);
     * boylece bu turda ilk-denemeye tekrar_aranacak=1 verip ayni turda final'e cekmeyiz.
     */
    protected function kilitliKurtar()
    {
        $esik       = now()->subMinutes((int) env('KAMPANYA_KILIT_TIMEOUT_DK', 5));
        $tekrarGeci = now()->addMinutes((int) env('KAMPANYA_TEKRAR_ARAMA_DK', 15));

        // (2) FINAL: tekrar denemesi de cevapsiz kaldi -> bir daha arama.
        $final = \App\KampanyaKatilimcilari::where('kilitli', 1)
            ->whereNull('durum_asistan')
            ->whereNotNull('kilitli_zaman')
            ->where('kilitli_zaman', '<=', $esik)
            ->where('tekrar_aranacak', 1)
            ->where(function ($q) { $q->whereNull('tekrar_arandi')->orWhere('tekrar_arandi', '!=', 1); })
            ->update([
                'asistan_ulasamadi' => 1,
                'tekrar_arandi'     => 1,   // secim disi -> final ulasilamadi
                'kilitli'           => 0,
                'kilitli_zaman'     => null,
            ]);

        // (1) ILK: ilk deneme cevapsiz -> 1 kez daha aramak uzere planla.
        $ilk = \App\KampanyaKatilimcilari::where('kilitli', 1)
            ->whereNull('durum_asistan')
            ->whereNotNull('kilitli_zaman')
            ->where('kilitli_zaman', '<=', $esik)
            ->where(function ($q) { $q->whereNull('tekrar_aranacak')->orWhere('tekrar_aranacak', '!=', 1); })
            ->update([
                'asistan_ulasamadi'       => 1,
                'tekrar_aranacak'         => 1,
                'tekrar_arama_tarih_saat' => $tekrarGeci,
                'kilitli'                 => 0,
                'kilitli_zaman'           => null,
            ]);

        if ($ilk > 0 || $final > 0) {
            Log::info("[REKLAM-ARAMA] takili kilit kurtarma: {$ilk} katilimci tekrar planlandi, {$final} final ulasilamadi.");
        }
    }

    protected function kampanyayiIsle($kampanya, $nowMin)
    {
        if ($kampanya->hatirlatma_gorevi_iptal) {
            return;
        }

        $ilkAramaDakikasi = (date('Y-m-d H:i', strtotime($kampanya->asistan_tarih_saat)) === $nowMin);

        // Bu kampanya bu dakikada ya yeni baslar ya da tekrar aramasi olanlari
        // vardir. Katilimci sorgusu TAM DAKIKA esasli kurulur.
        // kilitli: cift-arama kilidi. Kuyruga atilan katilimci kilitli=1 yapilir (asagida);
        // gecikmeli chunk penceresinde bir sonraki dakika ayni katilimciyi TEKRAR kuyruga
        // atmasin diye kilitli=1 olanlar elenir. (Kolon 2026_09_17 migration ile eklendi.)
        $sorgu = $kampanya->kampanya_katilimcilari()
            ->with(['musteri:id,name,cinsiyet,cep_telefon'])
            ->select('id', 'user_id', 'kampanya_id', 'tekrar_arandi', 'tekrar_aranacak', 'tekrar_arama_tarih_saat', 'durum_asistan', 'kilitli')
            ->whereNull('durum_asistan')
            ->where(function ($q) {
                $q->whereNull('kilitli')->orWhere('kilitli', '!=', 1);
            });

        // ONEMLI: Eskiden "ilk arama" YALNIZCA asistan_tarih_saat'in TAM DAKIKASINDA yapiliyordu
        // (ilkAramaDakikasi). O dakika kacinca (orn. job cokerse/gecikirse) hic aranmamis
        // katilimci "tekrar arama" dalina da giremiyor (tekrar_aranacak=null) -> KALICI TAKILI.
        // Cozum: hic aranmamislari, kampanya zamani geldiginde (dis sorgu asistan_tarih_saat<=now)
        // her koşuda al; tekrar arama icin de tam-dakika yerine "zamani gelmis/gecmis" (<=now).
        // Cift-arama kilidi (kilitli) + tekrar_aranacak bayragi spam'i onler.
        $sorgu->where(function ($q) {
            // (a) Ilk arama: hic aranmamis katilimcilar
            $q->where(function ($q2) {
                $q2->whereNull('tekrar_arandi')->whereNull('tekrar_aranacak');
            })
            // (b) VEYA tekrar arama zamani gelmis/gecmis
            ->orWhere(function ($q2) {
                $q2->where('tekrar_aranacak', 1)
                   ->where(function ($q3) {
                       $q3->whereNull('tekrar_arandi')->orWhere('tekrar_arandi', '!=', 1);
                   })
                   ->whereNotNull('tekrar_arama_tarih_saat')
                   ->where('tekrar_arama_tarih_saat', '<=', now());
            });
        });

        // Ayar kontrolu (kampanya basina tek sorgu) — kapaliysa hic dolasma.
        $ayarAcik = SalonEAsistanAyarlari::where('salon_id', $kampanya->salon_id)
            ->where('ayar_id', self::AYAR_ID_KAMPANYA)
            ->value('acik_kapali');
        if (!$ayarAcik) {
            Log::info("[REKLAM-ARAMA] kampanya {$kampanya->id} ATLANDI: E-Asistan ayar_id=8 (kampanya aramasi) KAPALI (salon {$kampanya->salon_id}).");
            return;
        }

        // Dakika butcesi kadar topla; kalanlar (kilitli=NULL) sonraki koşuda alinir.
        $tumListe = [];
        $sorgu->chunk(200, function ($katilimcilar) use ($kampanya, &$tumListe) {
            foreach ($katilimcilar as $katilimci) {
                if (count($tumListe) >= $this->kalanButce) {
                    return false; // bu koşu icin tavan doldu -> chunk'i durdur
                }
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

        // Cift-arama kilidi: kuyruga giren katilimcilari HEMEN kilitle; gecikmeli chunk
        // penceresinde bir sonraki dakikanin kosusu ayni kisiyi tekrar kuyruga atmasin.
        // Arama yapildi isaretlenince Controller kilitli=0'a ceker (tekrar arama serbest kalir).
        $kilitlenecek = array_column($tumListe, 'kampanyaKatilimci');
        if (!empty($kilitlenecek)) {
            // kilitli_zaman: kilitli takili kalirsa (cevapsiz cagri) kilitliKurtar() bunu
            // esik'e gore cozer -> kimse kalici takili kalmaz, tum liste taranir.
            \App\KampanyaKatilimcilari::whereIn('id', $kilitlenecek)
                ->update(['kilitli' => 1, 'kilitli_zaman' => now()]);
        }

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

        // Bu kampanyanin aldigi kadar dakika butcesinden dus (sonraki kampanyalar/koşular icin).
        $this->kalanButce -= $toplam;

        Log::info("[REKLAM-ARAMA] {$toplam} arama " . count($chunks) . " chunk halinde kuyruga eklendi (kampanya {$kampanya->id}). Kalan dakika butcesi={$this->kalanButce}.");

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
        // Cinsiyete gore bey/hanim EKLENMEZ; musterinin tam ismi okunur.
        $hitap = trim($katilimci->musteri->name);

        // Govdedeki kisiye ozel placeholder'lari ({müşteri}, {gün}=bu musterinin en son
        // GELDIGI randevudan bu yana yokluk gunu) bu katilimciya gore coz.
        $govde = \App\KampanyaYonetimi::kisisellestir($kampanya->mesaj, $katilimci->user_id, $kampanya->salon_id);

        // Isletme adi: telaffuz alani bos ise salon adina dus (yoksa "Sizi  ariyorum" der).
        $isletmeAdi = trim((string) $kampanya->salon->santral_telaffuz_hatirlatma_aramasi);
        if ($isletmeAdi === '') {
            $isletmeAdi = trim((string) $kampanya->salon->salon_adi);
        }

        $mesaj = 'Merhaba ' . $hitap . '. Sizi ' .
            $isletmeAdi . ' arıyorum. ' .
            'Umarım gününüz sağlıklı geçiyordur. ' . $govde;
        // BUYUK harf kelimeleri (ORBEY -> Orbey) duzelt; yoksa Google TTS harf harf okur.
        $mesaj = \App\KampanyaYonetimi::okunusNormalize($mesaj);

        return [
            'alacakIdler' => '',
            'randevuid' => '',
            'kampanyaKatilimci' => $katilimci->id,
            'katilimci' => $katilimci->id,
            'mesaj' => $mesaj,
            'tel' => $katilimci->musteri->cep_telefon,
            'salonId' => $kampanya->salon_id,
            // exten 4 = RANDEVUMCEPTE kampanya akisi (kampanya-tanitim -> ulasildi.php ->
            // app.randevumcepte.com.tr). exten 3 ECZANE akisiydi (ulasildiEczane.php ->
            // app.eczella.com): yanlis anons + "ulasildi" bilgisi eczella'ya gidiyordu.
            'exten' => 4,
        ];
    }
}
