<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Randevular;
use App\Salonlar;
use App\SalonSMSAyarlari;
use App\Personeller;
use App\BildirimKimlikleri;
use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RandevuSMSHatirlatma extends Command
{
    protected $signature = 'randevusms:hatirlat';
    protected $description = 'Randevu WhatsApp/SMS ve bildirim hatırlatmaları';

    public function handle()
    {
        $randevular = Randevular::has('hizmetler')
            ->where('durum', 1)
            ->where('user_id', '!=', 2012)
            ->where(function ($q) {
                $q->where('randevuya_geldi', null);
                $q->orWhere('randevuya_geldi', '!=', 0);
            })
            ->whereBetween('tarih', [date('Y-m-d'), date('Y-m-d', strtotime('+1 days', strtotime(date('Y-m-d'))))])
            ->get();

        $controller = app()->make(Controller::class);
        $wa = app(WhatsAppService::class);

        Log::info('randevu SMS/bildirim gönderilecek randevu sayısı ' . $randevular->count());

        foreach ($randevular as $value) {
            if ($value->salon_id === null || $value->salon_id == 0) continue;
            if (!$value->salonlar) continue;

            $randevutarihsaat = date('d.m.Y', strtotime($value->tarih)) . ' ' . date('H:i', strtotime($value->saat));

            // Müşteriye hatırlatma (salon kendi belirlediği X saat önce) — WhatsApp + SMS fallback
            if (date('d.m.Y H:i') == date('d.m.Y H:i', strtotime('-' . $value->salonlar->randevu_sms_hatirlatma . ' hours', strtotime($randevutarihsaat)))) {
                $ayar = SalonSMSAyarlari::where('salon_id', $value->salon_id)->where('ayar_id', 1)->first();
                if ($ayar && $ayar->musteri) {
                    $mesaj = 'Bugün ' . date('H:i', strtotime($value->saat)) . ' saatinde ' . $value->salonlar->salon_adi . ' tarafından oluşturulan randevunuzu hatırlatmak isteriz.';
                    $this->musteriyeGonder($wa, $controller, $value, $ayar, $mesaj);
                }
            }

            // Müşteriye 24 saat önce hatırlatma — TEK WhatsApp NOKTASI (SMS fallback'li)
            if (date('d.m.Y H:i') == date('d.m.Y H:i', strtotime('-24 hours', strtotime($randevutarihsaat)))) {
                $ayar = SalonSMSAyarlari::where('salon_id', $value->salon_id)->where('ayar_id', 6)->first();
                if ($ayar && $ayar->musteri) {
                    $mesaj = 'Yarın ' . date('H:i', strtotime($value->saat)) . ' saatinde ' . $value->salonlar->salon_adi . ' tarafından oluşturulan randevunuzu hatırlatmak isteriz.';
                    $this->musteriyeGonder($wa, $controller, $value, $ayar, $mesaj);
                }
            }

            // Personele hizmet bazlı SMS + push + bildirim
            foreach ($value->hizmetler as $hizmet) {
                $randevutarihsaatHizmet = date('d.m.Y', strtotime($value->tarih)) . ' ' . date('H:i:s', strtotime($hizmet->saat));
                Log::info($value->salon_id . ' için randevutarihsaat ' . $randevutarihsaatHizmet);

                if (date('d.m.Y H:i') != date('d.m.Y H:i', strtotime('-' . $value->salonlar->randevu_sms_hatirlatma . ' hours', strtotime($randevutarihsaatHizmet)))) {
                    continue;
                }

                $mesaj = $value->users->name . ' isimli müşterinin bugün ' . date('H:i', strtotime($hizmet->saat)) . ' saatli ' . $hizmet->hizmetler->hizmet_adi . ' randevusunu hatırlatmak isteriz.';

                if (SalonSMSAyarlari::where('salon_id', $value->salon_id)->where('ayar_id', 1)->value('personel')) {
                    $controller->sms_gonder($value->salon_id, [[
                        'to' => Personeller::where('id', $hizmet->personel_id)->value('cep_telefon'),
                        'message' => $mesaj,
                    ]]);
                }

                $bildirimkimlikleri = BildirimKimlikleri::whereIn(
                    'isletme_yetkili_id',
                    Personeller::where('salon_id', $value->salon_id)->pluck('id')->toArray()
                )->whereNotNull('bildirim_id')->pluck('bildirim_id')->toArray();

                Log::info('Bildirim gidecek personel sayısı ' . count($bildirimkimlikleri));
                $bildirimkimlikleri = array_merge($bildirimkimlikleri, self::yonetici_kimlikleri($value->salon_id));
                Log::info('Bildirim gidecek toplam personel sayısı ' . count($bildirimkimlikleri));

                self::bildirimgonder(
                    $bildirimkimlikleri,
                    $mesaj,
                    'Randevu Hatırlatma',
                    $value->salon_id,
                    '12d6537e-7a7d-4d1d-a838-e3fc947eaf44',
                    '5e50f84e-2cd8-4532-a765-f2cb82a22ff9',
                    'os_v2_app_lzipqtrm3bctfj3f6lfyfirp7ghx6w4i7t6e6iufqzlj6ginpkucdwamtgxy5bclne737yh7y62zxlfmep2c4ijioiimrps4jcq5ysi'
                );

                self::bildirimekle($value->salon_id, $mesaj, '#', $hizmet->personel_id, $value->user_id, $value->users->profil_resim, $value->id, null);

                if ($value->salonlar->bildirim_app_id && $value->salonlar->bildirim_channel_id && $value->salonlar->bildirim_api_key) {
                    self::bildirimgonder(
                        $bildirimkimlikleri,
                        $mesaj,
                        'Randevu Hatırlatma',
                        $value->salon_id,
                        $value->salonlar->bildirim_channel_id,
                        $value->salonlar->bildirim_app_id,
                        $value->salonlar->bildirim_api_key
                    );
                }
            }
        }
    }

    protected function musteriyeGonder(WhatsAppService $wa, Controller $controller, $randevu, $ayar, $mesajBase)
    {
        $salon = $randevu->salonlar;
        $musteri = $randevu->users;
        if (!$musteri || !$musteri->cep_telefon) return;

        $whatsappDenendi = false;
        $whatsappBasarili = false;

        $whatsappKanaliAcik = !empty($ayar->whatsapp_musteri)
            && $salon->whatsapp_aktif
            && $salon->whatsapp_durum === 'connected';

        $musteriOnayli = !Schema::hasColumn('users', 'whatsapp_onay') || (int) ($musteri->whatsapp_onay ?? 1) === 1;

        if ($whatsappKanaliAcik && $musteriOnayli) {
            $whatsappDenendi = true;
            $personalized = $wa->varyMessage($mesajBase, $musteri->name);
            $sonuc = $wa->sendReminder($salon, $musteri->cep_telefon, $personalized, $randevu->id, $musteri->id);
            if ($sonuc['ok'] ?? false) {
                $whatsappBasarili = true;
                Log::info('WhatsApp hatırlatma başarılı', ['salon_id' => $salon->id, 'randevu_id' => $randevu->id]);
            } else {
                Log::warning('WhatsApp hatırlatma başarısız', [
                    'salon_id' => $salon->id,
                    'randevu_id' => $randevu->id,
                    'error' => $sonuc['error'] ?? 'unknown',
                ]);
            }
        }

        if (!$whatsappBasarili) {
            $smsMesaj = 'İyi günler. ' . $mesajBase;
            $controller->sms_gonder($salon->id, [[
                'to' => $musteri->cep_telefon,
                'message' => $smsMesaj,
            ]]);
            if ($whatsappDenendi && config('whatsapp.fallback_to_sms', true)) {
                Log::info('SMS fallback yapıldı', ['salon_id' => $salon->id, 'randevu_id' => $randevu->id]);
            }
        }
    }

    protected function yonetici_kimlikleri($salon_id)
    {
        $yonetici = Personeller::join('model_has_roles', 'salon_personelleri.yetkili_id', '=', 'model_has_roles.model_id')
            ->where('salon_personelleri.salon_id', $salon_id)
            ->where('model_has_roles.role_id', '<', '5')
            ->pluck('salon_personelleri.id')->toArray();

        return BildirimKimlikleri::whereIn('isletme_yetkili_id', $yonetici)->pluck('bildirim_id')->toArray();
    }

    protected function bildirimekle($salonid, $mesaj, $url, $personelid, $musteriid, $imgurl, $randevuid, $satisortagiid)
    {
        $bildirim = new \App\Bildirimler();
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
