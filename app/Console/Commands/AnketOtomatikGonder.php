<?php

namespace App\Console\Commands;

use App\AnketGonderim;
use App\AnketSablon;
use App\Http\Controllers\StoreAdminController;
use App\Randevular;
use App\SalonSMSAyarlari;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AnketOtomatikGonder extends Command
{
    protected $signature = 'anket:otomatik-gonder';
    protected $description = 'Randevu bitiminde (MAX(randevu_hizmetler.saat_bitis)) bir kez memnuniyet anketi gönderir.';

    public function handle()
    {
        if (!Schema::hasTable('anket_sablonlari') || !Schema::hasTable('anket_gonderimleri') || !Schema::hasTable('randevu_hizmetler')) {
            $this->info('Anket/randevu_hizmetler tabloları yok, çıkılıyor.');
            return 0;
        }

        $aktifSablonlar = AnketSablon::where('aktif', 1)
            ->where('otomatik_gonder', 1)
            ->get();

        if ($aktifSablonlar->isEmpty()) {
            return 0;
        }

        $now = Carbon::now();
        $toplam = 0;
        $salonAyarCache = []; // salon_id => bool (ayar_id=13 musteri aktif mi?)

        // ── Ban koruması: anket WhatsApp burst gönderimini engelle ───────────────────
        // Aynı dakikada onlarca anket kuyruğa düşüp WhatsApp'ı spam'e itmesin diye 2 katman:
        //  1) STAGGER: randevu bitişinden sonra randevu id'sine göre 0..STAGGER-1 dk geciktir
        //     (aynı anda biten randevular farklı dakikalara yayılır)
        //  2) MAX_PER_MINUTE: her cron tikinde salon başına en fazla N anket gönder
        // Gönderilemeyenler sonraki dakikalarda alınır (26 saatlik pencere içinde).
        $STAGGER_MINUTES = 10;    // randevu id'sine göre dakikalara yayma genişliği
        $MAX_PER_MINUTE  = 5;     // salon başına dakikada en fazla anket
        $salonGonderimSayac = []; // salon_id => bu cron tikinde gönderilen anket sayısı

        // Basarisiz gonderim (SMS reddi + WA yok/basarisiz): kayit silinir, randevu DENEME_ARALIK dk sonra
        // tekrar aday olur; en fazla MAX_DENEME kez. Sayaclar cache'te (L5.6: TTL dakika).
        $MAX_DENEME    = 3;
        $DENEME_ARALIK = 10;

        foreach ($aktifSablonlar as $sablon) {
            // SMS Ayarları → "Randevu Sonrası Değerlendirme" (ayar_id=13, musteri=1) açık değilse atla.
            $salonId = $sablon->salon_id;
            // Bildirim tercihi (degerlendirme_anketi/musteri): hicbir kanal acik degilse atla.
            if (!array_key_exists($salonId, $salonAyarCache)) {
                $salonAyarCache[$salonId] = \App\Services\BildirimTercih::acikMi($salonId, 'degerlendirme_anketi', 'musteri');
            }
            if (!$salonAyarCache[$salonId]) continue;

            // Stagger + dakika limiti yalniz WhatsApp ban korumasi icin; SMS-first salonda (anket_gonderim_kanali=sms)
            // anket tam gonderim aninda ve limitsiz gider.
            $smsOnce = DB::table('salonlar')->where('id', $salonId)->value('anket_gonderim_kanali') === 'sms';

            // Bugün veya dün tarihli, aktif (durum=1) randevular.
            // user_id zorunlu; randevuya_geldi durumuna bakılmaz.
            $randevular = Randevular::where('salon_id', $sablon->salon_id)
                ->where('durum', 1)
                ->whereBetween('tarih', [
                    $now->copy()->subDay()->toDateString(),
                    $now->copy()->toDateString(),
                ])
                ->whereNotNull('user_id')
                ->where('user_id', '!=', 0)
                ->orderBy('id', 'asc') // FIFO: dakika limiti dolunca eski randevular önce gider
                ->get();

            if ($randevular->isEmpty()) continue;

            // N+1 -> BATCH: asagidaki 3 sorgu eskiden randevu BASINA calisiyordu (her dakika,
            // her aday icin exists + MAX + User). Artik sablon basina TEK sorguya indirildi.
            $randevuIds = $randevular->pluck('id')->all();
            $gonderilmisSet = array_flip(
                AnketGonderim::where('salon_id', $sablon->salon_id)
                    ->whereIn('randevu_id', $randevuIds)
                    ->pluck('randevu_id')->all()
            );
            // Randevu suresini hesaplamak icin hem bitis (MAX saat_bitis) hem baslangic (MIN saat) cekilir.
            $sureRows = DB::table('randevu_hizmetler')
                ->whereIn('randevu_id', $randevuIds)
                ->groupBy('randevu_id')
                ->selectRaw('randevu_id, MAX(saat_bitis) as max_bitis, MIN(saat) as min_baslangic')
                ->get();
            $maxBitisMap     = $sureRows->pluck('max_bitis', 'randevu_id')->all();
            $minBaslangicMap = $sureRows->pluck('min_baslangic', 'randevu_id')->all();
            $userMap = User::whereIn('id', array_values(array_unique($randevular->pluck('user_id')->all())))
                ->get()->keyBy('id');

            foreach ($randevular as $rnd) {
                // 1) Aynı randevu için anket zaten gönderildi mi? Tek gönderim garantisi.
                if (isset($gonderilmisSet[$rnd->id])) continue;

                // 2) Bitiş saati = MAX(saat_bitis) (yukarida sablon basina batch'lendi)
                $maxBitis = $maxBitisMap[$rnd->id] ?? null;
                if (!$maxBitis) continue; // hizmet yoksa veya saat_bitis boşsa atla

                try {
                    $bitis = Carbon::parse($rnd->tarih . ' ' . $maxBitis);
                } catch (\Exception $e) {
                    continue;
                }

                // 2b) Randevu suresi (dk) = MAX(saat_bitis) - MIN(saat)
                $baslangic = null;
                $sureDk    = null;
                $minBaslangic = $minBaslangicMap[$rnd->id] ?? null;
                if ($minBaslangic) {
                    try {
                        $baslangic = Carbon::parse($rnd->tarih . ' ' . $minBaslangic);
                        $sureDk    = $baslangic->diffInMinutes($bitis);
                    } catch (\Exception $e) { $baslangic = null; }
                }

                // 3) GONDERIM ANI:
                //    - Suresi 10 dk'dan UZUN randevular: bitisten 15 dk ONCE gonder.
                //      (Musteri hala salonda olur; calisan "size anket yolladik" diye sozlu hatirlatabilir.)
                //    - 10 dk ve alti (veya sure hesaplanamayan) randevular: bitisten SONRA gonder (eski davranis).
                $ONCEDEN_DK = 15;   // uzun randevularda bitisten kac dk once gonderilecek
                $SURE_ESIGI = 10;   // bu dk'nin uzerindeki randevular "erken gonderim" grubuna girer
                if ($sureDk !== null && $sureDk > $SURE_ESIGI) {
                    $gonderimAni = $bitis->copy()->subMinutes($ONCEDEN_DK);
                    // Randevu baslamadan anket gitmesin (deneyim henuz yasanmadan): en erken baslangic ani.
                    if ($baslangic && $gonderimAni->lt($baslangic)) {
                        $gonderimAni = $baslangic->copy();
                    }
                } else {
                    $gonderimAni = $bitis->copy();
                }

                // STAGGER: gonderim anindan sonra randevu id'sine gore 0..STAGGER-1 dk geciktir.
                // Hedef an gelmediyse atla (ayni anda tetiklenen randevular dakikalara yayilir).
                $hedefAn = $smsOnce ? $gonderimAni->copy() : $gonderimAni->copy()->addMinutes(((int) $rnd->id) % $STAGGER_MINUTES);
                if ($now->lt($hedefAn)) continue;
                // 4) 26 saatten eski randevuya da gönderme (geç kalmış cron için makul üst sınır)
                if ($bitis->diffInHours($now, false) > 26) continue;

                // 5) Dakika başına salon limiti — burst engeli. Doluysa bu randevu sonraki tikte gider.
                if (!$smsOnce && ($salonGonderimSayac[$salonId] ?? 0) >= $MAX_PER_MINUTE) continue;

                // 6) Onceki deneme basarisizsa: bekleme suresi dolmadan veya deneme hakki bittiyse atla
                $denemeKey = 'anket_oto_deneme_' . $rnd->id;
                if (Cache::has('anket_oto_bekle_' . $rnd->id)) continue;
                if ((int) Cache::get($denemeKey, 0) >= $MAX_DENEME) continue;

                $musteri = $userMap->get($rnd->user_id);
                if (!$musteri) continue;
                $tel = trim($musteri->cep_telefon ?? '');
                if (!$tel) continue;

                try {
                    $gonderim = StoreAdminController::anketGonderimOlustur(
                        $sablon->salon_id,
                        $sablon,
                        $musteri,
                        $tel,
                        [
                            'randevu_id'  => $rnd->id,
                            'personel_id' => $rnd->personel_id ?? null,
                            'kanal'       => 'sms',
                        ]
                    );

                    $gitti = $this->tercihliGonder($gonderim, $sablon, $musteri);
                    $salonGonderimSayac[$salonId] = ($salonGonderimSayac[$salonId] ?? 0) + 1;
                    if (!$gitti) {
                        // Hicbir kanal kabul etmedi: kaydi sil ki randevu tekrar aday olsun (cevapsiz, yeni olusturuldu)
                        $deneme = (int) Cache::get($denemeKey, 0) + 1;
                        Cache::put($denemeKey, $deneme, 60 * 27);
                        Cache::put('anket_oto_bekle_' . $rnd->id, 1, $DENEME_ARALIK);
                        try { $gonderim->delete(); } catch (\Throwable $e) { /* yoksay */ }
                        Log::warning('[ANKET-OTO] gonderilemedi' . ($deneme >= $MAX_DENEME ? ', deneme hakki bitti' : ', tekrar denenecek'), [
                            'randevu_id' => $rnd->id, 'salon_id' => $sablon->salon_id, 'deneme' => $deneme, 'tel' => $tel,
                        ]);
                        continue;
                    }
                    $toplam++;

                    Log::info('[ANKET-OTO] gönderim', [
                        'randevu_id'  => $rnd->id,
                        'salon_id'    => $sablon->salon_id,
                        'sablon_id'   => $sablon->id,
                        'gonderim_id' => $gonderim->id,
                        'bitis'       => $bitis->toDateTimeString(),
                        'sure_dk'     => $sureDk,
                        'gonderim_ani'=> $gonderimAni->toDateTimeString(),
                        'erken'       => ($sureDk !== null && $sureDk > $SURE_ESIGI) ? 1 : 0,
                        'tel'         => $tel,
                    ]);
                } catch (\Exception $e) {
                    Log::error('[ANKET-OTO] hata: ' . $e->getMessage(), [
                        'randevu_id' => $rnd->id,
                        'salon_id'   => $sablon->salon_id,
                    ]);
                }
            }
        }

        $this->info('Toplam ' . $toplam . ' anket gönderimi yapıldı.');
        return 0;
    }

    /**
     * Anket davetini bildirim tercihine (degerlendirme_anketi/musteri) gore gonderir.
     * Metinler StoreAdminController::anketSmsGonder ile AYNI. Kanal yalniz seciliyse kullanilir:
     *  - anket_gonderim_kanali yalniz SIRAYI belirler (SMS+WA ikisi seciliyse once hangisi, digeri yedek).
     *  - WA-first: WA ulasmazsa SMS -> SMS seciliyse ya da (yedek SMS acik + paket var).
     *  - SMS-first: SMS gidemezse WA -> yalniz whatsapp seciliyse.
     *  - Push seciliyse ayrica uygulama bildirimi.
     * Donus: herhangi bir kanal kabul ettiyse true (false -> cron tekrar dener).
     */
    protected function tercihliGonder($gonderim, $sablon, $musteri)
    {
        $salon = \App\Salonlar::where('id', $gonderim->salon_id)->first();
        if (!$salon) return false;
        $k = \App\Services\BildirimTercih::kanallar($salon->id, 'degerlendirme_anketi', 'musteri');

        // ── Metinler (StoreAdminController::anketSmsGonder ile birebir) ──
        $host = ($_SERVER['HTTP_HOST'] ?? null) ?: ($salon->domain ?? 'app.randevumcepte.com.tr');
        $link = 'https://' . $host . '/anket/' . $gonderim->token;
        $adSoyad = $musteri->name ?? '';
        $ilkAd = $adSoyad ? trim(explode(' ', $adSoyad)[0]) : '';
        $salonAd = $salon->salon_adi ?? '';
        $odulIfade = null;
        if ($sablon) {
            if (($sablon->odul_tipi ?? 'yok') === 'kupon' && (float) $sablon->odul_kupon_deger > 0) {
                $d = (float) $sablon->odul_kupon_deger;
                $odulIfade = ($sablon->odul_kupon_indirim_tipi === 'tutar')
                    ? (rtrim(rtrim(number_format($d, 2, ',', '.'), '0'), ',') . ' ₺ indirim kuponu')
                    : (intval($d) . '% indirim kuponu');
            } elseif (($sablon->odul_tipi ?? 'yok') === 'puan' && (float) $sablon->odul_puan > 0) {
                $p = (float) $sablon->odul_puan;
                $odulIfade = rtrim(rtrim(number_format($p, 2, ',', '.'), '0'), ',') . ' puan';
            }
        }
        if ($odulIfade) {
            $mesaj = "Merhaba Sn. " . $ilkAd . " 🎁" . "\n\n"
                . $salonAd . " deneyiminizi değerlendirin, size özel " . $odulIfade . " hediyemiz olsun!" . "\n\n"
                . "Anketi doldurmak (30 sn):" . "\n" . $link . "\n\n"
                . "Görüşleriniz bizim için çok değerli, teşekkür ederiz.";
        } else {
            $mesaj = "Merhaba Sn. " . $ilkAd . "," . "\n\n"
                . $salonAd . "'da yaşadığınız deneyim bizim için çok değerli. Hizmet kalitemizi geliştirebilmemiz adına görüşlerinizi birkaç dakikanızı ayırarak bizimle paylaşabilir misiniz?" . "\n\n"
                . "Anketi doldurmak için:" . "\n" . $link . "\n\n"
                . "Değerli geri bildiriminiz için şimdiden teşekkür eder, sizi yeniden ağırlamaktan mutluluk duyarız.";
        }
        $smsMesaj = StoreAdminController::anketSmsKisaMetin($ilkAd, $salonAd, $link, $odulIfade);

        $smsOnce = ($salon->anket_gonderim_kanali ?? 'whatsapp') === 'sms';
        $waBagli = \App\Services\BildirimTercih::waBagli($salon);

        $waDene = function () use ($salon, $gonderim, $musteri, $mesaj) {
            try {
                $sonuc = app(\App\Services\WhatsAppService::class)->sendReminder(
                    $salon, $gonderim->telefon, $mesaj, $gonderim->randevu_id ?? null, $musteri->id ?? null, null, false, 'anket'
                );
                if (!empty($sonuc['ok'])) {
                    try { $gonderim->gonderim_kanali = 'whatsapp'; $gonderim->save(); } catch (\Throwable $e) { /* yoksay */ }
                    Log::info('[ANKET-WA] gonderildi', ['salon_id' => $salon->id, 'gonderim_id' => $gonderim->id,
                        'to' => $gonderim->telefon, 'provider' => $sonuc['provider'] ?? null, 'logId' => $sonuc['logId'] ?? null]);
                    return true;
                }
                Log::warning('[ANKET-WA] basarisiz', ['salon_id' => $salon->id, 'gonderim_id' => $gonderim->id, 'err' => $sonuc['error'] ?? 'unknown']);
            } catch (\Throwable $e) {
                Log::warning('[ANKET-WA] istisna: ' . $e->getMessage(), ['salon_id' => $salon->id, 'gonderim_id' => $gonderim->id ?? null]);
            }
            return false;
        };
        $smsGonder = function () use ($gonderim, $smsMesaj) {
            try {
                app()->make(\App\Http\Controllers\Controller::class)->sms_gonder($gonderim->salon_id, [['to' => $gonderim->telefon, 'message' => $smsMesaj]]);
                $ok = \App\Http\Controllers\Controller::$sonSmsBasarili === true;
                Log::info('[ANKET-SMS] ' . ($ok ? 'saglayici kabul etti' : 'GONDERILEMEDI'), [
                    'salon_id' => $gonderim->salon_id, 'gonderim_id' => $gonderim->id, 'to' => $gonderim->telefon,
                ]);
                return $ok;
            } catch (\Throwable $e) {
                Log::error('anketSmsGonder hata: ' . $e->getMessage());
                return false;
            }
        };
        $yedekSmsUygun = function () use ($salon) {
            return \App\Services\BildirimTercih::waYedekSms($salon->id) && \App\Services\BildirimTercih::smsKullanilabilir($salon);
        };

        $gitti = false;
        if ($smsOnce && $k['sms']) {
            // SMS-first; SMS gidemezse (bakiye/saglayici) WA bagliysa WA ile dene — bugunku ters yedek davranisi
            $gitti = $smsGonder();
            if (!$gitti && $waBagli) $gitti = $waDene();
        } elseif ($k['whatsapp'] || $k['sms']) {
            // WA-first; WA kapali/basarisizsa SMS: secili ise ya da yedek SMS uygunsa
            if ($k['whatsapp'] && $waBagli) $gitti = $waDene();
            if (!$gitti && ($k['sms'] || ($k['whatsapp'] && $yedekSmsUygun()))) $gitti = $smsGonder();
        }

        if ($k['push'] && !empty($musteri->id)) {
            \App\Services\BildirimTercih::genelPush($salon, 'degerlendirme_anketi', [
                'user_id' => (int) $musteri->id, 'alici' => 'musteri', 'randevu_id' => $gonderim->randevu_id ?? null,
                'push_baslik' => 'Değerlendirme Anketi', 'push_mesaj' => $mesaj,
            ]);
            $gitti = true; // push gitti: tekrar deneyip musteriye ikinci push atma
        }
        return $gitti;
    }
}
