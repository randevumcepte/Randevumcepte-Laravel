<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Randevular;
use App\Salonlar;
use App\SalonSMSAyarlari;
use App\SalonCalismaSaatleri;
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

        Log::info('[RND-SMS] cron tick', [
            'simdi' => date('d.m.Y H:i'),
            'aday_randevu_sayisi' => $randevular->count(),
        ]);

        foreach ($randevular as $value) {
            if ($value->salon_id === null || $value->salon_id == 0) {
                Log::info('[RND-SMS] atlandi (salon_id yok)', ['randevu_id' => $value->id]);
                continue;
            }
            if (!$value->salonlar) {
                Log::info('[RND-SMS] atlandi (salon ilişkisi yok)', ['randevu_id' => $value->id, 'salon_id' => $value->salon_id]);
                continue;
            }

            $randevutarihsaat = date('d.m.Y', strtotime($value->tarih)) . ' ' . date('H:i', strtotime($value->saat));
            $simdi = date('d.m.Y H:i');
            $tetikSalonSaat = date('d.m.Y H:i', strtotime('-' . $value->salonlar->randevu_sms_hatirlatma . ' hours', strtotime($randevutarihsaat)));
            $tetik24Saat = date('d.m.Y H:i', strtotime('-24 hours', strtotime($randevutarihsaat)));

            Log::info('[RND-SMS] randevu inceleniyor', [
                'randevu_id' => $value->id,
                'salon_id' => $value->salon_id,
                'salon' => $value->salonlar->salon_adi,
                'tarih_saat' => $randevutarihsaat,
                'simdi' => $simdi,
                'salon_hatirlatma_saat' => $value->salonlar->randevu_sms_hatirlatma,
                'tetik_salon' => $tetikSalonSaat,
                'tetik_24h' => $tetik24Saat,
                'wa_aktif' => (int) ($value->salonlar->whatsapp_aktif ?? 0),
                'wa_durum' => $value->salonlar->whatsapp_durum,
            ]);

            // Müşteriye hatırlatma (salon kendi belirlediği X saat önce) — WhatsApp + SMS fallback
            if ($simdi == $tetikSalonSaat) {
                $ayar = SalonSMSAyarlari::where('salon_id', $value->salon_id)->where('ayar_id', 1)->first();
                Log::info('[RND-SMS] müşteri salon-saati tetiklendi', [
                    'randevu_id' => $value->id,
                    'ayar_var' => (bool) $ayar,
                    'ayar_musteri' => $ayar ? (int) $ayar->musteri : null,
                    'ayar_wa_musteri' => $ayar ? (int) ($ayar->whatsapp_musteri ?? 0) : null,
                ]);
                if ($ayar && $ayar->musteri) {
                    $saat = date('H:i', strtotime($value->saat));
                    $mesaj = 'Bugün ' . $saat . ' saatinde ' . $value->salonlar->salon_adi . ' tarafından oluşturulan randevunuzu hatırlatmak isteriz.';
                    $templateCtx = ['key' => 'yaklasan', 'params' => [$saat, $value->salonlar->salon_adi]];
                    $this->musteriyeGonder($wa, $controller, $value, $ayar, $mesaj, $templateCtx);
                } elseif ($ayar) {
                    Log::info('[RND-SMS] müşteri SMS toggle kapali — atlandi', ['randevu_id' => $value->id]);
                }
            }

            // Müşteriye 1 gün öncesi hatırlatma — salonun bugünkü çalışma saatleri penceresinde,
            // id'ye göre dakikalara dağıtılmış (her randevu tek bir dakikada), tek seferlik
            $yarinTarih = date('Y-m-d', strtotime('+1 day'));
            $nowMinuteOfDay = (int) date('G') * 60 + (int) date('i');

            // salon_calisma_saatleri.haftanin_gunu: 1=Pzt..7=Paz; PHP date('N') aynı format
            $bugunGunu = (int) date('N');
            $calisma = SalonCalismaSaatleri::where('salon_id', $value->salon_id)
                ->where('haftanin_gunu', $bugunGunu)->first();

            if ($calisma && $calisma->calisiyor && $calisma->baslangic_saati && $calisma->bitis_saati) {
                $winStart = (int) date('G', strtotime($calisma->baslangic_saati)) * 60
                          + (int) date('i', strtotime($calisma->baslangic_saati));
                $winEnd   = (int) date('G', strtotime($calisma->bitis_saati)) * 60
                          + (int) date('i', strtotime($calisma->bitis_saati));
            } else {
                // Salon bugün kapalı veya tanım yok — güvenli default: 09:00-21:00
                $winStart = 9 * 60;
                $winEnd = 21 * 60;
            }

            if ($winEnd <= $winStart) {
                $winStart = 9 * 60;
                $winEnd = 21 * 60;
            }

            if ($value->tarih === $yarinTarih
                && $nowMinuteOfDay >= $winStart && $nowMinuteOfDay < $winEnd
                && empty($value->hatirlatma_gunonce_gonderildi)) {

                $bucketSize = $winEnd - $winStart;
                $stagger = ((int) $value->id) % $bucketSize;
                $targetMinute = $winStart + $stagger;

                if ($nowMinuteOfDay >= $targetMinute) {
                    $ayar = SalonSMSAyarlari::where('salon_id', $value->salon_id)->where('ayar_id', 6)->first();
                    Log::info('[RND-SMS] 1 gün öncesi (çalışma saati penceresi) tetiklendi', [
                        'randevu_id' => $value->id,
                        'win_start_dk' => $winStart,
                        'win_end_dk' => $winEnd,
                        'stagger_dk' => $stagger,
                        'hedef_dk' => $targetMinute,
                        'simdi_dk' => $nowMinuteOfDay,
                        'ayar_var' => (bool) $ayar,
                        'ayar_musteri' => $ayar ? (int) $ayar->musteri : null,
                        'ayar_wa_musteri' => $ayar ? (int) ($ayar->whatsapp_musteri ?? 0) : null,
                    ]);
                    if ($ayar && $ayar->musteri) {
                        $saat = date('H:i', strtotime($value->saat));
                        $mesaj = 'Yarın ' . $saat . ' saatinde ' . $value->salonlar->salon_adi . ' tarafından oluşturulan randevunuzu hatırlatmak isteriz.';
                        $templateCtx = ['key' => '1gun', 'params' => [$saat, $value->salonlar->salon_adi]];
                        $this->musteriyeGonder($wa, $controller, $value, $ayar, $mesaj, $templateCtx);
                    }
                    \Illuminate\Support\Facades\DB::table('randevular')
                        ->where('id', $value->id)
                        ->update(['hatirlatma_gunonce_gonderildi' => now()]);
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

                $personelAyari = SalonSMSAyarlari::where('salon_id', $value->salon_id)->where('ayar_id', 1)->first();
                if ($personelAyari && $personelAyari->personel) {
                    $personelTelefon = Personeller::where('id', $hizmet->personel_id)->value('cep_telefon');
                    $this->personeleGonder($wa, $controller, $value->salonlar, $personelTelefon, $mesaj, $personelAyari, $value->id);
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

    protected function personeleGonder(WhatsAppService $wa, Controller $controller, $salon, $telefon, $mesajBase, $ayar, $randevuId)
    {
        if (!$telefon) {
            Log::info('[RND-SMS] personel telefon yok — atlandi', ['salon_id' => $salon->id, 'randevu_id' => $randevuId]);
            return;
        }

        $whatsappKanaliAcik = !empty($ayar->whatsapp_personel)
            && $salon->whatsapp_aktif
            && $salon->whatsapp_durum === 'connected';

        Log::info('[RND-SMS] personel kanal karar', [
            'salon_id' => $salon->id,
            'randevu_id' => $randevuId,
            'telefon' => $telefon,
            'wa_personel_toggle' => (int) ($ayar->whatsapp_personel ?? 0),
            'wa_aktif' => (int) ($salon->whatsapp_aktif ?? 0),
            'wa_durum' => $salon->whatsapp_durum,
            'wa_kanali_acik' => $whatsappKanaliAcik,
        ]);

        $whatsappBasarili = false;
        if ($whatsappKanaliAcik) {
            $sonuc = $wa->sendReminder($salon, $telefon, $mesajBase, $randevuId, null);
            Log::info('[RND-SMS] personel WA sonuc', [
                'salon_id' => $salon->id, 'randevu_id' => $randevuId, 'sonuc' => $sonuc,
            ]);
            if ($sonuc['ok'] ?? false) {
                $whatsappBasarili = true;
            } else {
                Log::warning('[RND-SMS] personel WA başarısız → SMS fallback', [
                    'salon_id' => $salon->id, 'randevu_id' => $randevuId,
                    'error' => $sonuc['error'] ?? 'unknown',
                ]);
            }
        }

        if (!$whatsappBasarili) {
            Log::info('[RND-SMS] personel SMS gönderiliyor', [
                'salon_id' => $salon->id, 'randevu_id' => $randevuId, 'telefon' => $telefon,
            ]);
            $controller->sms_gonder($salon->id, [[
                'to' => $telefon,
                'message' => $mesajBase,
            ]]);
        }
    }

    protected function musteriyeGonder(WhatsAppService $wa, Controller $controller, $randevu, $ayar, $mesajBase, $templateCtx = null)
    {
        $salon = $randevu->salonlar;
        $musteri = $randevu->users;
        if (!$musteri || !$musteri->cep_telefon) {
            Log::info('[RND-SMS] müşteri telefon yok — atlandi', [
                'salon_id' => $salon->id ?? null, 'randevu_id' => $randevu->id,
                'musteri_var' => (bool) $musteri,
            ]);
            return;
        }

        $whatsappDenendi = false;
        $whatsappBasarili = false;

        $saglayici = $salon->whatsapp_saglayici ?? 'baileys';
        if ($saglayici === 'cloud_api') {
            // Cloud API: token + phone_number_id + ilgili template adı varsa kanal açık
            $templateField = isset($templateCtx['key']) ? 'cloud_api_template_' . $templateCtx['key'] : null;
            $whatsappKanaliAcik = !empty($ayar->whatsapp_musteri)
                && !empty($salon->cloud_api_token)
                && !empty($salon->cloud_api_phone_number_id)
                && ($templateField ? !empty($salon->{$templateField}) : false);
        } else {
            // Baileys: aktif + connected
            $whatsappKanaliAcik = !empty($ayar->whatsapp_musteri)
                && $salon->whatsapp_aktif
                && $salon->whatsapp_durum === 'connected';
        }

        $musteriOnayli = !Schema::hasColumn('users', 'whatsapp_onay') || (int) ($musteri->whatsapp_onay ?? 1) === 1;

        Log::info('[RND-SMS] müşteri kanal karar', [
            'salon_id' => $salon->id,
            'randevu_id' => $randevu->id,
            'musteri_id' => $musteri->id,
            'telefon' => $musteri->cep_telefon,
            'wa_musteri_toggle' => (int) ($ayar->whatsapp_musteri ?? 0),
            'wa_aktif' => (int) ($salon->whatsapp_aktif ?? 0),
            'wa_durum' => $salon->whatsapp_durum,
            'wa_kanali_acik' => $whatsappKanaliAcik,
            'musteri_onayli' => $musteriOnayli,
        ]);

        if ($whatsappKanaliAcik && $musteriOnayli) {
            $whatsappDenendi = true;
            // Cloud API için varyasyon kapalı (template'ler sabit), Baileys için varyasyon açık
            $personalized = ($saglayici === 'cloud_api')
                ? $mesajBase
                : $wa->varyMessage($mesajBase, $musteri->name);
            $sonuc = $wa->sendReminder($salon, $musteri->cep_telefon, $personalized, $randevu->id, $musteri->id, $templateCtx);
            Log::info('[RND-SMS] müşteri WA sonuc', [
                'salon_id' => $salon->id, 'randevu_id' => $randevu->id, 'sonuc' => $sonuc,
            ]);
            if ($sonuc['ok'] ?? false) {
                $whatsappBasarili = true;
            } else {
                Log::warning('[RND-SMS] müşteri WA başarısız → SMS fallback', [
                    'salon_id' => $salon->id,
                    'randevu_id' => $randevu->id,
                    'error' => $sonuc['error'] ?? 'unknown',
                ]);
            }
        }

        if (!$whatsappBasarili) {
            $smsMesaj = 'İyi günler. ' . $mesajBase;
            Log::info('[RND-SMS] müşteri SMS gönderiliyor', [
                'salon_id' => $salon->id,
                'randevu_id' => $randevu->id,
                'telefon' => $musteri->cep_telefon,
                'wa_denendi' => $whatsappDenendi,
            ]);
            $controller->sms_gonder($salon->id, [[
                'to' => $musteri->cep_telefon,
                'message' => $smsMesaj,
            ]]);
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
