<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MusteriPortfoy;
use App\SalonSMSAyarlari;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DogumGunuSMSHatirlatma extends Command
{
    protected $signature = 'dogumgunusms:hatirlat';
    protected $description = 'Doğum günü SMS hatırlatmaları';

    public function handle()
    {
        // Saat guard'i BASTA: dogum gunu SMS'i yalnizca 15:17'de gonderiliyor.
        // Eskiden guard dongunun icindeydi -> agir whereMonth/whereDay (users full
        // scan) her dakika calisiyordu. Artik gunde 1 kez (15:17). Davranis ayni.
        if (date('H:i') !== '15:17') {
            return;
        }

        $bugun = Carbon::today();
        $dogumGunleri = MusteriPortfoy::whereHas('users', function ($q) use ($bugun) {
            $q->whereMonth('dogum_tarihi', $bugun->month)
              ->whereDay('dogum_tarihi', $bugun->day);
        })
        ->with(['users' => function ($q) use ($bugun) {
            $q->whereMonth('dogum_tarihi', $bugun->month)
              ->whereDay('dogum_tarihi', $bugun->day);
        }])
        ->where('aktif', 1)
        ->get();

        $controller = app()->make(Controller::class);

        foreach ($dogumGunleri as $dogumGunu) {
            if (!SalonSMSAyarlari::where('salon_id', $dogumGunu->salon_id)->where('ayar_id', 8)->value('musteri')) {
                continue;
            }
            // (saat guard'i handle() basina alindi — 15:17 kontrolu burada tekrarlanmiyor)

            // Bugun isletme panelinden manuel olarak (popup uzerinden) gonderildiyse tekrar gonderme
            $manuelGonderildi = DB::table('dogum_gunu_mesaj_loglari')
                ->where('salon_id', $dogumGunu->salon_id)
                ->where('user_id', $dogumGunu->user_id)
                ->whereDate('gonderim_tarihi', date('Y-m-d'))
                ->exists();
            if ($manuelGonderildi) {
                Log::info('doğum günü otomatik SMS atlandı (manuel gönderim var)', [
                    'salon_id' => $dogumGunu->salon_id, 'user_id' => $dogumGunu->user_id,
                ]);
                continue;
            }

            $kutlamaMesaji = 'Sayın ' . $dogumGunu->users->name . ' ' . $dogumGunu->salonlar->salon_adi . ' olarak doğum gününüzü kutlar, sağlıklı, mutlu ve başarılı dolu seneler dileriz.';

            $mesaj = [[
                'to' => $dogumGunu->users->cep_telefon,
                'message' => $kutlamaMesaji,
            ]];
            Log::info('doğum günü SMS salon_id ' . $dogumGunu->salon_id);
            $controller->sms_gonder($dogumGunu->salon_id, $mesaj);

            // Otomatik SMS gonderildigine dair log dus (gun icinde duplicate engellemesi icin)
            try {
                DB::table('dogum_gunu_mesaj_loglari')->insert([
                    'salon_id' => $dogumGunu->salon_id,
                    'user_id' => $dogumGunu->user_id,
                    'kanal' => 'sms-otomatik',
                    'mesaj' => mb_substr($kutlamaMesaji, 0, 500),
                    'detay' => 'scheduled',
                    'gonderim_tarihi' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('[DOGUM-GUNU-AUTO] log yazilamadi', ['err' => $e->getMessage()]);
            }

            try {
                \App\Services\NotificationService::toCustomer((int) $dogumGunu->user_id, (int) $dogumGunu->salon_id)
                    ->type(\App\Services\NotificationTypes::BIRTHDAY)
                    ->title('🎂 Doğum gününüz kutlu olsun!')
                    ->body($kutlamaMesaji)
                    ->popup(true)
                    ->send();
            } catch (\Throwable $e) {
                Log::warning('[DOGUM-GUNU] push fail', [
                    'user_id' => $dogumGunu->user_id,
                    'salon_id' => $dogumGunu->salon_id,
                    'err' => $e->getMessage(),
                ]);
            }
        }
    }
}
