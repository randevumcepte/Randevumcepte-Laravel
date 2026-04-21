<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TaksitVadeleri;
use App\TaksitliTahsilatlar;
use App\Personeller;
use App\MusteriPortfoy;
use App\SalonSMSAyarlari;
use App\BildirimKimlikleri;
use App\Bildirimler;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class AlacakSMSHatirlatma extends Command
{
    protected $signature = 'alacaksms:hatirlat';
    protected $description = 'Taksit vade alacak SMS ve bildirim hatırlatmaları';

    public function handle()
    {
        if (date('H:i') != '14:10') {
            return;
        }

        $controller = app()->make(Controller::class);

        $yarin = date('Y-m-d', strtotime('+1 days', strtotime(date('Y-m-d'))));
        $bugun = date('Y-m-d');

        // Yarınki ve bugünkü vadesi olan, ödenmemiş taksit vadeleri
        $vadeler = TaksitVadeleri::whereIn('vade_tarih', [$yarin, $bugun])
            ->where(function ($q) {
                $q->whereNull('odendi')->orWhere('odendi', false);
            })
            ->get();

        Log::info('Alacak SMS gönderilecek vade sayısı ' . $vadeler->count());

        foreach ($vadeler as $vade) {
            $tahsilat = TaksitliTahsilatlar::where('id', $vade->taksitli_tahsilat_id)->first();
            if (!$tahsilat || !$tahsilat->musteri || !$tahsilat->salon) {
                continue;
            }

            $musteri = $tahsilat->musteri;
            $salon = $tahsilat->salon;
            $salon_id = $tahsilat->salon_id;

            $portfoy = MusteriPortfoy::where('user_id', $tahsilat->user_id)
                ->where('salon_id', $salon_id)
                ->first();
            if ($portfoy && $portfoy->kara_liste) {
                continue;
            }

            $yetkililer = Personeller::join('model_has_roles', 'salon_personelleri.yetkili_id', '=', 'model_has_roles.model_id')
                ->where('salon_personelleri.salon_id', $salon_id)
                ->whereIn('model_has_roles.role_id', [1, 2, 3, 4])
                ->get(['salon_personelleri.id', 'salon_personelleri.cep_telefon', 'salon_personelleri.personel_adi']);

            $tutarStr = number_format($vade->tutar, 2, ',', '.');

            if ($vade->vade_tarih == $yarin) {
                // Müşteriye SMS
                $musteriMesaj = 'Sayın ' . $musteri->name . ' ' . $salon->salon_adi . ' için yarın ödemeniz gereken ' . $tutarStr . ' TL borcunuzun olduğunu hatırlatmak isteriz.';
                if (SalonSMSAyarlari::where('salon_id', $salon_id)->where('ayar_id', 1)->value('musteri')) {
                    $controller->sms_gonder($salon_id, [[
                        'to' => $musteri->cep_telefon,
                        'message' => $musteriMesaj,
                    ]]);
                }

                // Yetkililere SMS + push + bildirim
                foreach ($yetkililer as $yetkili) {
                    $ymesaj = 'Sayın ' . $yetkili->personel_adi . '. ' . $musteri->name . ' isimli müşterinizin yarın ' . $tutarStr . ' TL alacağınız olduğunu hatırlatmak isteriz.';
                    if (SalonSMSAyarlari::where('salon_id', $salon_id)->where('ayar_id', 1)->value('personel')) {
                        $controller->sms_gonder($salon_id, [[
                            'to' => $yetkili->cep_telefon,
                            'message' => $ymesaj,
                        ]]);
                    }
                    self::bildirimekle($salon_id, $ymesaj, '#', $yetkili->id, $tahsilat->user_id, $musteri->profil_resim, null, null);

                    $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id', $yetkili->id)->pluck('bildirim_id')->toArray();
                    self::bildirimgonder(
                        $bildirimkimlikleri,
                        $ymesaj,
                        'Alacak Hatırlatma',
                        $salon_id,
                        '12d6537e-7a7d-4d1d-a838-e3fc947eaf44',
                        '5e50f84e-2cd8-4532-a765-f2cb82a22ff9',
                        'os_v2_app_lzipqtrm3bctfj3f6lfyfirp7ghx6w4i7t6e6iufqzlj6ginpkucdwamtgxy5bclne737yh7y62zxlfmep2c4ijioiimrps4jcq5ysi'
                    );

                    if ($salon->bildirim_app_id && $salon->bildirim_channel_id && $salon->bildirim_api_key) {
                        self::bildirimgonder(
                            $bildirimkimlikleri,
                            $ymesaj,
                            'Alacak Hatırlatma',
                            $salon_id,
                            $salon->bildirim_channel_id,
                            $salon->bildirim_app_id,
                            $salon->bildirim_api_key
                        );
                    }
                }
            } elseif ($vade->vade_tarih == $bugun) {
                $musteriMesaj = 'Sayın ' . $musteri->name . ' ' . $salon->salon_adi . ' için ' . $tutarStr . ' TL borcunuzun olduğunu hatırlatmak isteriz. Tahsilat için lütfen iletişime geçin. 0' . $salon->telefon_1;
                if (SalonSMSAyarlari::where('salon_id', $salon_id)->where('ayar_id', 1)->value('musteri')) {
                    $controller->sms_gonder($salon_id, [[
                        'to' => $musteri->cep_telefon,
                        'message' => $musteriMesaj,
                    ]]);
                }

                foreach ($yetkililer as $yetkili) {
                    $ymesaj = 'Sayın ' . $yetkili->personel_adi . ' ' . $musteri->name . ' isimli müşterinizin bugün ' . $tutarStr . ' TL alacağınız olduğunu hatırlatmak isteriz. Tahsilat için lütfen müşterinizi arayınız. 0' . $musteri->cep_telefon;
                    if (SalonSMSAyarlari::where('salon_id', $salon_id)->where('ayar_id', 1)->value('personel')) {
                        $controller->sms_gonder($salon_id, [[
                            'to' => $yetkili->cep_telefon,
                            'message' => $ymesaj,
                        ]]);
                    }
                    self::bildirimekle($salon_id, $ymesaj, '#', $yetkili->id, $tahsilat->user_id, $musteri->profil_resim, null, null);

                    $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id', $yetkili->id)->pluck('bildirim_id')->toArray();
                    self::bildirimgonder(
                        $bildirimkimlikleri,
                        $ymesaj,
                        'Alacak Hatırlatma',
                        $salon_id,
                        '12d6537e-7a7d-4d1d-a838-e3fc947eaf44',
                        '5e50f84e-2cd8-4532-a765-f2cb82a22ff9',
                        'os_v2_app_lzipqtrm3bctfj3f6lfyfirp7ghx6w4i7t6e6iufqzlj6ginpkucdwamtgxy5bclne737yh7y62zxlfmep2c4ijioiimrps4jcq5ysi'
                    );
                }
            }
        }
    }

    protected function bildirimekle($salonid, $mesaj, $url, $personelid, $musteriid, $imgurl, $randevuid, $satisortagiid)
    {
        $bildirim = new Bildirimler();
        $bildirim->aciklama = $mesaj;
        $bildirim->salon_id = $salonid;
        $bildirim->personel_id = $personelid;
        $bildirim->satis_ortagi_id = $satisortagiid;
        $bildirim->url = $url;
        $bildirim->tarih_saat = date('Y-m-d H:i:s');
        $bildirim->okundu = false;
        $bildirim->user_id = $musteriid;
        $bildirim->img_src = $imgurl;
        $bildirim->randevu_id = $randevuid;
        $bildirim->save();
    }

    protected function bildirimgonder($bildirimkimlikleri, $mesaj, $baslik, $salonid, $channelid, $appid, $key)
    {
        if (empty($bildirimkimlikleri)) {
            return;
        }
        $post_url = 'https://api.onesignal.com/notifications?c=push';
        $headers = [
            'Accept: application/json',
            'Authorization: Key ' . $key,
            'Content-Type: application/json',
        ];
        $post_data = json_encode([
            'app_id' => $appid,
            'include_player_ids' => $bildirimkimlikleri,
            'android_channel_id' => $channelid,
            'contents' => ['en' => $mesaj],
            'headings' => ['en' => $baslik],
            'sound' => 'default',
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_exec($ch);
        curl_close($ch);
    }
}
