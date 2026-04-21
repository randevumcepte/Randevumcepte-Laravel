<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Randevular;
use App\MusteriPortfoy;
use App\SalonEAsistanAyarlari;
use App\Salonlar;
use App\Jobs\HatirlatmaAramaJob;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class RandevuHatirlatmaAramasiYap extends Command
{
    protected $signature = 'randevuarama:yap';
    protected $description = 'Randevu Teyit Aramaları (chunk + queue)';

    // 50 kanal limitine göre ayarla
    protected $chunkSize = 50;
    // Ortalama arama süresi (saniye) - her chunk arası bekleme süresi
    protected $callDuration = 35;

    public function handle()
    {
        Log::info('randevu hatırlatma araması kontrolü başlatıldı.');
        $controller = app()->make(Controller::class);

        Randevular::has('hizmetler')
            ->where('durum', 1)
            ->where('user_id', '!=', 2012)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('tarih', date('Y-m-d', strtotime('+1 days', strtotime(date('Y-m-d')))))
                       ->where('saat', date('H:i:00'));
                });
                $q->orWhere(function ($q2) {
                    $q2->where('tekrar_arama_tarih_saat', date('Y-m-d H:i:00'))
                       ->where('tekrar_aranacak', true);
                });
            })
            ->chunk(200, function ($randevular) use ($controller) {

                $aramaListesi = [];
                $salonIdList = [];

                foreach ($randevular as $value) {
                    $karaListedeMi = MusteriPortfoy::where('user_id', $value->user_id)
                        ->where('salon_id', $value->salon_id)
                        ->value('kara_liste');
                    if ($karaListedeMi) {
                        continue;
                    }

                    $ayarAcik = SalonEAsistanAyarlari::where('salon_id', $value->salon_id)
                        ->where('ayar_id', 4)
                        ->value('acik_kapali');
                    if (!$ayarAcik) {
                        continue;
                    }
                    if ($value->tekrar_arandi) {
                        continue;
                    }

                    $tarihSaat = $value->tarih . ' ' . $value->saat;
                    $zamanUygun = (
                        date('d.m.Y H:i') == date('d.m.Y H:i', strtotime('-24 hours', strtotime($tarihSaat))) ||
                        date('d.m.Y H:i', strtotime($value->tekrar_arama_tarih_saat)) == date('d.m.Y H:i')
                    );
                    if (!$zamanUygun) {
                        continue;
                    }
                    if ($value->hatirlatma_gorevi_iptal === true) {
                        continue;
                    }
                    if ($value->tekrar_aranacak === false) {
                        continue;
                    }

                    if (!$controller->hatirlatmaSaatiIcinde(date('H:i'))) {
                        if (date('H:i') > date('H:i', strtotime('19:30'))) {
                            $value->tekrar_arama_tarih_saat = date('Y-m-d', strtotime('+1 days', strtotime(date('Y-m-d')))) . ' 10:00:00';
                        } else {
                            $value->tekrar_arama_tarih_saat = date('Y-m-d') . ' 10:00:00';
                        }
                        $value->save();
                        continue;
                    }

                    $aramaListesi[] = [
                        'alacakIdler' => '',
                        'randevuid' => $value->id,
                        'kampanyaKatilimci' => '',
                        'katilimci' => '',
                        'mesaj' => 'Sayın ' . $value->users->name . '. ' .
                            Salonlar::where('id', $value->salon_id)->value('santral_telaffuz_2') .
                            ' için ' . $value->tarih . ' saat ' . date('H:i', strtotime($value->saat)) .
                            ' randevunuzu hatırlatmak isteriz. Randevuya gelecekseniz biri, randevunuzu başka bir tarihe ertelemek istiyorsanız ikiyi tuşlayınız.',
                        'tel' => $value->users->cep_telefon,
                        'salonId' => $value->salon_id,
                        'exten' => 1,
                    ];
                    $salonIdList[$value->salon_id] = true;
                }

                if (count($aramaListesi) === 0) {
                    return;
                }

                $chunks = array_chunk($aramaListesi, $this->chunkSize);
                $tekSalonId = count($salonIdList) === 1 ? array_key_first($salonIdList) : null;

                foreach ($chunks as $chunkIndex => $chunk) {
                    $delaySeconds = $chunkIndex * $this->callDuration;

                    $dispatch = HatirlatmaAramaJob::dispatch($chunk, $tekSalonId, null)
                        ->onQueue('hatirlatmalar');

                    if ($delaySeconds > 0) {
                        $dispatch->delay(now()->addSeconds($delaySeconds));
                        Log::info("Randevu arama chunk {$chunkIndex} gecikmeli gönderildi: {$delaySeconds} sn sonra");
                    } else {
                        Log::info("Randevu arama chunk {$chunkIndex} hemen gönderildi.");
                    }

                    Log::info("Randevu arama chunk {$chunkIndex}: " . count($chunk) . ' arama kuyruğa eklendi');
                }

                Log::info(count($aramaListesi) . ' adet randevu araması ' . count($chunks) . ' chunk halinde kuyruğa eklendi.');
            });

        Log::info('randevu hatırlatma araması kontrolü tamamlandı.');
    }
}
