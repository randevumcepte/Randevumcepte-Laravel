<?php

namespace App\Console\Commands;

use App\AnketGonderim;
use App\AnketSablon;
use App\Http\Controllers\StoreAdminController;
use App\Randevular;
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

        foreach ($aktifSablonlar as $sablon) {
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

                // 3) Henüz bitiş saati gelmediyse atla (gelecek randevulara gitmesin)
                if ($now->lt($bitis)) continue;
                // 4) 26 saatten eski randevuya da gönderme (geç kalmış cron için makul üst sınır)
                if ($bitis->diffInHours($now, false) > 26) continue;

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
