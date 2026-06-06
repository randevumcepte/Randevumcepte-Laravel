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

        foreach ($aktifSablonlar as $sablon) {
            // SMS Ayarları → "Randevu Sonrası Değerlendirme" (ayar_id=13, musteri=1) açık değilse atla.
            $salonId = $sablon->salon_id;
            if (!array_key_exists($salonId, $salonAyarCache)) {
                $salonAyarCache[$salonId] = (bool) SalonSMSAyarlari::where('salon_id', $salonId)
                    ->where('ayar_id', 13)
                    ->value('musteri');
            }
            if (!$salonAyarCache[$salonId]) continue;

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

            foreach ($randevular as $rnd) {
                // 1) Aynı randevu için anket zaten gönderildi mi? Tek gönderim garantisi.
                $varMi = AnketGonderim::where('salon_id', $sablon->salon_id)
                    ->where('randevu_id', $rnd->id)
                    ->exists();
                if ($varMi) continue;

                // 2) Bitiş saati = MAX(saat_bitis) FROM randevu_hizmetler WHERE randevu_id = ...
                $maxBitis = DB::table('randevu_hizmetler')
                    ->where('randevu_id', $rnd->id)
                    ->max('saat_bitis');
                if (!$maxBitis) continue; // hizmet yoksa veya saat_bitis boşsa atla

                try {
                    $bitis = Carbon::parse($rnd->tarih . ' ' . $maxBitis);
                } catch (\Exception $e) {
                    continue;
                }

                // 3) STAGGER: bitişten sonra randevu id'sine göre 0..STAGGER-1 dk geciktir.
                //    Hedef an gelmediyse atla (aynı anda biten randevular dakikalara yayılır).
                $hedefAn = $bitis->copy()->addMinutes(((int) $rnd->id) % $STAGGER_MINUTES);
                if ($now->lt($hedefAn)) continue;
                // 4) 26 saatten eski randevuya da gönderme (geç kalmış cron için makul üst sınır)
                if ($bitis->diffInHours($now, false) > 26) continue;

                // 5) Dakika başına salon limiti — burst engeli. Doluysa bu randevu sonraki tikte gider.
                if (($salonGonderimSayac[$salonId] ?? 0) >= $MAX_PER_MINUTE) continue;

                $musteri = User::where('id', $rnd->user_id)->first();
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

                    StoreAdminController::anketSmsGonder(null, $gonderim, $sablon, $musteri);
                    $toplam++;
                    $salonGonderimSayac[$salonId] = ($salonGonderimSayac[$salonId] ?? 0) + 1;

                    Log::info('[ANKET-OTO] gönderim', [
                        'randevu_id'  => $rnd->id,
                        'salon_id'    => $sablon->salon_id,
                        'sablon_id'   => $sablon->id,
                        'gonderim_id' => $gonderim->id,
                        'bitis'       => $bitis->toDateTimeString(),
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
}
