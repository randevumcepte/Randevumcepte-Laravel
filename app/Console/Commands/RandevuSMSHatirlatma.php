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

            // İlk geçiş: tetik penceresine giren hizmetleri topla.
            // Aynı randevuda birden fazla hizmet varsa personel/yöneticilere TEK
            // push gider, hizmetler virgülle ayrılır.
            $tetiklenenHizmetler = []; // [['hizmet'=>..., 'satir'=>'10:00 Saç Kesimi']]
            foreach ($value->hizmetler as $hizmet) {
                $randevutarihsaatHizmet = date('d.m.Y', strtotime($value->tarih)) . ' ' . date('H:i:s', strtotime($hizmet->saat));
                if (date('d.m.Y H:i') != date('d.m.Y H:i', strtotime('-' . $value->salonlar->randevu_sms_hatirlatma . ' hours', strtotime($randevutarihsaatHizmet)))) {
                    continue;
                }
                $tetiklenenHizmetler[] = [
                    'hizmet' => $hizmet,
                    'satir'  => date('H:i', strtotime($hizmet->saat)) . ' ' . $hizmet->hizmetler->hizmet_adi,
                ];
            }

            if (empty($tetiklenenHizmetler)) continue;

            $personelAyari = SalonSMSAyarlari::where('salon_id', $value->salon_id)->where('ayar_id', 1)->first();

            // Personel bazinda hizmetleri grupla -> tek SMS, tek push
            $personelHizmetMap = []; // personel_id => [satir, ...]
            foreach ($tetiklenenHizmetler as $row) {
                $pid = $row['hizmet']->personel_id;
                if (!$pid) continue;
                if (!isset($personelHizmetMap[$pid])) $personelHizmetMap[$pid] = [];
                $personelHizmetMap[$pid][] = $row['satir'];
            }

            // Hizmeti atanmış personele (her personel kendi hizmet listesiyle)
            foreach ($personelHizmetMap as $pid => $satirlar) {
                $mesaj = $value->users->name . ' isimli müşterinin bugün ' . implode(', ', $satirlar) . ' randevu' . (count($satirlar) > 1 ? 'larını' : 'sunu') . ' hatırlatmak isteriz.';

                if ($personelAyari && $personelAyari->personel) {
                    $personelTelefon = Personeller::where('id', $pid)->value('cep_telefon');
                    $this->personeleGonder($wa, $controller, $value->salonlar, $personelTelefon, $mesaj, $personelAyari, $value->id);
                }

                try {
                    \App\Services\NotificationService::toStaff((int) $pid, (int) $value->salon_id)
                        ->type(\App\Services\NotificationTypes::APPOINTMENT_REMINDER)
                        ->title('Randevu Hatırlatma')
                        ->body($mesaj)
                        ->randevu((int) $value->id)
                        ->deepLink('appointment_detail', ['randevu_id' => $value->id])
                        ->send();
                } catch (\Throwable $e) {
                    Log::warning('[RND-SMS] personel push fail', ['randevu_id' => $value->id, 'personel_id' => $pid, 'err' => $e->getMessage()]);
                }

                self::bildirimekle($value->salon_id, $mesaj, '#', $pid, $value->user_id, $value->users->profil_resim, $value->id, null);
            }

            // Yöneticilere TEK push: randevudaki TÜM tetiklenen hizmetler birlikte
            $tumSatirlar = array_column($tetiklenenHizmetler, 'satir');
            $yoneticiMesaji = $value->users->name . ' isimli müşterinin bugün ' . implode(', ', $tumSatirlar) . ' randevu' . (count($tumSatirlar) > 1 ? 'larını' : 'sunu') . ' hatırlatmak isteriz.';

            $atanmisPersonelIdleri = array_keys($personelHizmetMap);
            $yoneticiIdleri = Personeller::join('model_has_roles', 'salon_personelleri.yetkili_id', '=', 'model_has_roles.model_id')
                ->where('salon_personelleri.salon_id', $value->salon_id)
                ->where('model_has_roles.role_id', '<', 5)
                ->whereNotIn('salon_personelleri.id', $atanmisPersonelIdleri)
                ->pluck('salon_personelleri.id')->toArray();
            foreach ($yoneticiIdleri as $yId) {
                try {
                    \App\Services\NotificationService::toStaff((int) $yId, (int) $value->salon_id)
                        ->type(\App\Services\NotificationTypes::APPOINTMENT_REMINDER)
                        ->title('Randevu Hatırlatma')
                        ->body($yoneticiMesaji)
                        ->randevu((int) $value->id)
                        ->deepLink('appointment_detail', ['randevu_id' => $value->id])
                        ->send();
                } catch (\Throwable $e) {
                    Log::warning('[RND-SMS] yonetici push fail', ['randevu_id' => $value->id, 'yonetici_id' => $yId, 'err' => $e->getMessage()]);
                }
                self::bildirimekle($value->salon_id, $yoneticiMesaji, '#', $yId, $value->user_id, $value->users->profil_resim, $value->id, null);
            }
        }
    }

    protected function personeleGonder(WhatsAppService $wa, Controller $controller, $salon, $telefon, $mesajBase, $ayar, $randevuId)
    {
        if (!$telefon) {
            Log::info('[RND-SMS] personel telefon yok — atlandi', ['salon_id' => $salon->id, 'randevu_id' => $randevuId]);
            return;
        }

        $whatsappKanaliAcik = !empty($salon->whatsapp_aktif)
            && $salon->whatsapp_durum === 'connected';

        Log::info('[RND-SMS] personel kanal karar', [
            'salon_id' => $salon->id,
            'randevu_id' => $randevuId,
            'telefon' => $telefon,
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
            $whatsappKanaliAcik = !empty($salon->cloud_api_token)
                && !empty($salon->cloud_api_phone_number_id)
                && ($templateField ? !empty($salon->{$templateField}) : false);
        } else {
            // Baileys: aktif + connected (ayar kolonu kontrolu kaldirildi - salon WA acikken her zaman WA dene)
            $whatsappKanaliAcik = !empty($salon->whatsapp_aktif)
                && $salon->whatsapp_durum === 'connected';
        }

        $musteriOnayli = !Schema::hasColumn('users', 'whatsapp_onay') || (int) ($musteri->whatsapp_onay ?? 1) === 1;

        Log::info('[RND-SMS] müşteri kanal karar', [
            'salon_id' => $salon->id,
            'randevu_id' => $randevu->id,
            'musteri_id' => $musteri->id,
            'telefon' => $musteri->cep_telefon,
            'saglayici' => $saglayici,
            'wa_aktif' => (int) ($salon->whatsapp_aktif ?? 0),
            'wa_durum' => $salon->whatsapp_durum,
            'wa_kanali_acik' => $whatsappKanaliAcik,
            'musteri_onayli' => $musteriOnayli,
        ]);

        if ($whatsappKanaliAcik && $musteriOnayli) {
            $whatsappDenendi = true;
            // WA metni SMS ile birebir aynı olsun (Cloud API kendi template'ini kullanır).
            $personalized = ($saglayici === 'cloud_api')
                ? $mesajBase
                : 'İyi günler. ' . $mesajBase;
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

        // Musteri push: WA/SMS'ten bagimsiz, her zaman gonderilir (SMS metniyle birebir)
        if ($musteri->id) {
            try {
                \App\Services\NotificationService::toCustomer((int) $musteri->id, (int) $salon->id)
                    ->type(\App\Services\NotificationTypes::APPOINTMENT_REMINDER)
                    ->title('Randevu Hatırlatma')
                    ->body($mesajBase)
                    ->randevu((int) $randevu->id)
                    ->deepLink('appointment_detail', ['randevu_id' => $randevu->id])
                    ->send();
            } catch (\Throwable $e) {
                Log::warning('[RND-SMS] musteri push fail', ['randevu_id' => $randevu->id, 'err' => $e->getMessage()]);
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

}
