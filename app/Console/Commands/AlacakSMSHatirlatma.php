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
        // Alacak hatirlatma ARAMALARI (2 gun once 10:00 + tekrar aramalar) — eskiden ayri
        // `alacakhatirlatma:aramayap` komutuydu. Hata SMS/WA akisini etkilemesin.
        try {
            app(\App\Services\AlacakHatirlatmaAramasi::class)->calistir();
        } catch (\Throwable $e) {
            Log::error('[ALACAK-ARAMA] hata: ' . $e->getMessage());
        }

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

            // Ek alicilar (personel bilgi kopyasi: "Alacaklari yonet" yetkilileri — ekGonder kendisi bulur)
            \App\Services\BildirimTercih::ekGonder($salon, 'alacak_hatirlatma', [
                'user_id' => (int) $tahsilat->user_id,
                'musteri_adi' => $musteri->name,
                'musteri_tel' => $musteri->cep_telefon,
                'ozet' => trim($musteri->name . (!empty($musteri->cep_telefon) ? ' (' . $musteri->cep_telefon . ')' : ''))
                    . ' – ' . $tutarStr . ' ₺ taksit, vade ' . date('d.m.Y', strtotime($vade->vade_tarih))
                    . ($vade->vade_tarih == $bugun ? ' (bugün)' : ' (yarın)') . '. Ödeme hatırlatma günü.',
            ]);

            if ($vade->vade_tarih == $yarin) {
                // Müşteriye SMS
                $musteriMesaj = 'Sayın ' . $musteri->name . ' ' . $salon->salon_adi . ' için yarın ödemeniz gereken ' . $tutarStr . ' TL borcunuzun olduğunu hatırlatmak isteriz.';
                if (\App\Services\BildirimTercih::acikMi($salon_id, 'alacak_hatirlatma', 'musteri')) {
                    $this->waYaDaSms($controller, $salon, $salon_id, $musteri->cep_telefon, $musteriMesaj, 'alacak_hatirlatma', 'musteri', ['user_id' => $tahsilat->user_id]);
                }

                // Yetkililere SMS + push + bildirim
                foreach ($yetkililer as $yetkili) {
                    $ymesaj = 'Sayın ' . $yetkili->personel_adi . '. ' . $musteri->name . ' isimli müşterinizin yarın ' . $tutarStr . ' TL alacağınız olduğunu hatırlatmak isteriz.';
                    if (\App\Services\BildirimTercih::mesajAcikMi($salon_id, 'alacak_hatirlatma', 'yonetici')) {
                        $this->waYaDaSms($controller, $salon, $salon_id, $yetkili->cep_telefon, $ymesaj, 'alacak_hatirlatma', 'yonetici', ['personel_id' => $yetkili->id, 'push_ayri' => true]);
                    }
                    self::bildirimekle($salon_id, $ymesaj, '#', $yetkili->id, $tahsilat->user_id, $musteri->profil_resim, null, null);

                    // Push: bildirim tercihi (alacak_hatirlatma / personel / push)
                    if (\App\Services\BildirimTercih::izin($salon_id, 'alacak_hatirlatma', 'yonetici', 'push')) try {
                        \App\Services\NotificationService::toStaff((int) $yetkili->id, (int) $salon_id)
                            ->type(\App\Services\NotificationTypes::SYSTEM_ANNOUNCEMENT)
                            ->title('Alacak Hatırlatma')
                            ->body($ymesaj)
                            ->send();
                    } catch (\Throwable $e) {
                        Log::warning('[ALACAK-SMS] push fail', ['yetkili_id' => $yetkili->id, 'err' => $e->getMessage()]);
                    }
                }
            } elseif ($vade->vade_tarih == $bugun) {
                $musteriMesaj = 'Sayın ' . $musteri->name . ' ' . $salon->salon_adi . ' için ' . $tutarStr . ' TL borcunuzun olduğunu hatırlatmak isteriz. Tahsilat için lütfen iletişime geçin. 0' . $salon->telefon_1;
                if (\App\Services\BildirimTercih::acikMi($salon_id, 'alacak_hatirlatma', 'musteri')) {
                    $this->waYaDaSms($controller, $salon, $salon_id, $musteri->cep_telefon, $musteriMesaj, 'alacak_hatirlatma', 'musteri', ['user_id' => $tahsilat->user_id]);
                }

                foreach ($yetkililer as $yetkili) {
                    $ymesaj = 'Sayın ' . $yetkili->personel_adi . ' ' . $musteri->name . ' isimli müşterinizin bugün ' . $tutarStr . ' TL alacağınız olduğunu hatırlatmak isteriz. Tahsilat için lütfen müşterinizi arayınız. 0' . $musteri->cep_telefon;
                    if (\App\Services\BildirimTercih::mesajAcikMi($salon_id, 'alacak_hatirlatma', 'yonetici')) {
                        $this->waYaDaSms($controller, $salon, $salon_id, $yetkili->cep_telefon, $ymesaj, 'alacak_hatirlatma', 'yonetici', ['personel_id' => $yetkili->id, 'push_ayri' => true]);
                    }
                    self::bildirimekle($salon_id, $ymesaj, '#', $yetkili->id, $tahsilat->user_id, $musteri->profil_resim, null, null);

                    // Push: bildirim tercihi (alacak_hatirlatma / personel / push)
                    if (\App\Services\BildirimTercih::izin($salon_id, 'alacak_hatirlatma', 'yonetici', 'push')) try {
                        \App\Services\NotificationService::toStaff((int) $yetkili->id, (int) $salon_id)
                            ->type(\App\Services\NotificationTypes::SYSTEM_ANNOUNCEMENT)
                            ->title('Alacak Hatırlatma')
                            ->body($ymesaj)
                            ->send();
                    } catch (\Throwable $e) {
                        Log::warning('[ALACAK-SMS] push fail', ['yetkili_id' => $yetkili->id, 'err' => $e->getMessage()]);
                    }
                }
            }
        }
    }

    /**
     * Bildirim tercihine gore gonderim (alacak_hatirlatma): WA/SMS/push kararini BildirimTercih::dagit
     * verir (WA ulasmazsa yedek SMS). Alacak_hatirlatma tipi kontor duser (randevu bildirim/hatirlatma DEGIL).
     */
    protected function waYaDaSms($controller, $salon, $salonId, $telefon, $mesaj, $gonderimTipi, $alici = 'musteri', array $ek = [])
    {
        if (empty($telefon) && empty($ek['user_id'])) return;
        try {
            $satir = array_merge(['to' => $telefon, 'message' => $mesaj, 'gonderim_tipi' => $gonderimTipi,
                'bk' => 'alacak_hatirlatma', 'alici' => $alici, 'push_baslik' => 'Alacak Hatırlatma'], $ek);
            $sms = \App\Services\BildirimTercih::dagit($salon, [$satir], $gonderimTipi);
            if ($sms) {
                $controller->sms_gonder($salonId, [['to' => $sms[0]['to'], 'message' => $sms[0]['message']]]);
            }
        } catch (\Throwable $e) {
            Log::warning('[ALACAK-SMS] gonderim hatasi: ' . $e->getMessage(), ['salon_id' => $salonId]);
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

}
