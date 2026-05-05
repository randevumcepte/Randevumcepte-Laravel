<?php

namespace App\Console\Commands;

use App\AnketGonderim;
use App\AnketSablon;
use App\Http\Controllers\StoreAdminController;
use App\Randevular;
use App\Salonlar;
use App\SalonSMSAyarlari;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AnketOtomatikGonder extends Command
{
    protected $signature = 'anket:otomatik-gonder';
    protected $description = 'Randevu sonrası otomatik memnuniyet anketi SMS gönderimi (otomatik_gonder=1 olan şablonlar için)';

    public function handle()
    {
        if (!Schema::hasTable('anket_sablonlari') || !Schema::hasTable('anket_gonderimleri')) {
            $this->info('Anket tabloları yok, çıkılıyor.');
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

        foreach ($aktifSablonlar as $sablon) {
            $saatSonra = max(1, (int) $sablon->gonder_saat_sonra);
            $eskiSinir = $now->copy()->subHours($saatSonra);
            // 24 saatten eski randevuları işleme — geç kalmış cron koşusunda eski cevaplı/iptal randevulara anket göndermeyi engeller
            $altSinir  = $now->copy()->subHours($saatSonra + 24);

            // Bu salon için anket gönderim limit kontrolü: salon SMS toggle'ı yoksa atla
            $smsAyarVar = SalonSMSAyarlari::where('salon_id', $sablon->salon_id)->where('ayar_id', 1)->where('musteri', 1)->exists();
            // Anket için ayrı bir SMS ayar_id'si yok; randevu hatırlatma SMS toggle'ını proxy olarak kullan (salon SMS aktifliği)
            // İleride özel ayar_id eklenebilir. Şimdilik her salon için açık varsayalım.

            // Bu sablon için bu zaman penceresinde "biten" randevuları bul
            // randevuların tarih+saat = randevu başlangıcı, hizmet süresi yok varsayımıyla başlangıç anı kullanılır
            $randevular = Randevular::where('salon_id', $sablon->salon_id)
                ->where('durum', 1)
                ->where(function ($q) {
                    // Müşteri geldi (null veya geldi flag'i)
                    $q->whereNull('randevuya_geldi')->orWhere('randevuya_geldi', '!=', 0);
                })
                ->whereBetween('tarih', [$altSinir->toDateString(), $eskiSinir->toDateString()])
                ->whereNotNull('user_id')
                ->where('user_id', '!=', 0)
                ->get()
                ->filter(function ($r) use ($altSinir, $eskiSinir) {
                    try {
                        $rDt = Carbon::parse($r->tarih . ' ' . $r->saat);
                        return $rDt->between($altSinir, $eskiSinir);
                    } catch (\Exception $e) {
                        return false;
                    }
                });

            foreach ($randevular as $rnd) {
                // Bu randevuya aynı salon için zaten anket gönderildi mi?
                $varMi = AnketGonderim::where('salon_id', $sablon->salon_id)
                    ->where('randevu_id', $rnd->id)
                    ->exists();
                if ($varMi) continue;

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
                            'randevu_id' => $rnd->id,
                            'personel_id' => $rnd->personel_id ?? null,
                            'kanal' => 'sms',
                        ]
                    );

                    StoreAdminController::anketSmsGonder(null, $gonderim, $sablon, $musteri);
                    $toplam++;

                    Log::info('[ANKET-OTO] gönderim', [
                        'randevu_id' => $rnd->id,
                        'salon_id' => $sablon->salon_id,
                        'sablon_id' => $sablon->id,
                        'gonderim_id' => $gonderim->id,
                        'tel' => $tel,
                    ]);
                } catch (\Exception $e) {
                    Log::error('[ANKET-OTO] hata: ' . $e->getMessage(), [
                        'randevu_id' => $rnd->id,
                        'salon_id' => $sablon->salon_id,
                    ]);
                }
            }
        }

        $this->info('Toplam ' . $toplam . ' anket gönderimi yapıldı.');
        return 0;
    }
}
