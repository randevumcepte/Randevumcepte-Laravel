<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MusteriPortfoy;
use App\SalonSMSAyarlari;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DogumGunuSMSHatirlatma extends Command
{
    protected $signature = 'dogumgunusms:hatirlat';
    protected $description = 'Doğum günü SMS hatırlatmaları';

    public function handle()
    {
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
            if (date('H:i') != date('H:i', strtotime('15:17'))) {
                continue;
            }

            $mesaj = [[
                'to' => $dogumGunu->users->cep_telefon,
                'message' => 'Sayın ' . $dogumGunu->users->name . ' ' . $dogumGunu->salonlar->salon_adi . ' olarak doğum gününüzü kutlar, sağlıklı, mutlu ve başarılı dolu seneler dileriz.',
            ]];
            Log::info('doğum günü SMS salon_id ' . $dogumGunu->salon_id);
            $controller->sms_gonder($dogumGunu->salon_id, $mesaj);
        }
    }
}
