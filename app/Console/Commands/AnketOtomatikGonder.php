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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AnketOtomatikGonder extends Command
{
    protected $signature = 'anket:otomatik-gonder';
    protected $description = 'Randevu bitiminde otomatik memnuniyet anketi SMS gönderimi (otomatik_gonder=1 olan şablonlar için)';

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
            // gonder_saat_sonra: 0 = randevu biter bitmez, N = bitisten N saat sonra
            $saatSonra = max(0, (int) $sablon->gonder_saat_sonra);

            // Son 26 saat içindeki olası randevuları al (geç kalmış cron koşusu için)
            $randevular = Randevular::where('salon_id', $sablon->salon_id)
                ->where('durum', 1)
                ->where(function ($q) {
                    $q->whereNull('randevuya_geldi')->orWhere('randevuya_geldi', '!=', 0);
                })
                ->whereBetween('tarih', [
                    $now->copy()->subHours(26 + $saatSonra)->toDateString(),
                    $now->copy()->toDateString(),
                ])
                ->whereNotNull('user_id')
                ->where('user_id', '!=', 0)
                ->get();

            foreach ($randevular as $rnd) {
                // Aynı randevu için anket zaten gönderildi mi?
                $varMi = AnketGonderim::where('salon_id', $sablon->salon_id)
                    ->where('randevu_id', $rnd->id)
                    ->exists();
                if ($varMi) continue;

                // Randevu başlangıç zamanı
                try {
                    $baslangic = Carbon::parse($rnd->tarih . ' ' . $rnd->saat);
                } catch (\Exception $e) {
                    continue;
                }

                // Hizmet süresi toplamı (dakika) — randevu_hizmetler tablosundan
                $sureDk = 0;
                try {
                    if (Schema::hasTable('randevu_hizmetler')) {
                        $sureDk = (int) DB::table('randevu_hizmetler')
                            ->where('randevu_id', $rnd->id)
                            ->sum('sure_dk');
                    }
                } catch (\Exception $e) {
                    $sureDk = 0;
                }
                if ($sureDk <= 0) $sureDk = 30; // default 30 dk

                // Randevu bitiş zamanı + saatSonra bekleme
                $bitis = $baslangic->copy()->addMinutes($sureDk);
                $tetikleme = $bitis->copy()->addHours($saatSonra);

                // Tetikleme zamanı geçmediyse atla (henüz erken)
                if ($now->lt($tetikleme)) continue;
                // Çok eski randevu ise atla (26 saatten önce tetiklenmiş olmalıydı)
                if ($tetikleme->diffInHours($now, false) > 26) continue;

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
                        'sure_dk' => $sureDk,
                        'saat_sonra' => $saatSonra,
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
