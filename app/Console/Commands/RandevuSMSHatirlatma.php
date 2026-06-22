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

        // Personel/yonetici hatirlatmasi randevu basina yalnizca BIR kez gonderilsin
        // diye idempotency bayragi (cron overlap / yeniden tetikleme korumasi).
        $personelFlagVar = Schema::hasColumn('randevular', 'hatirlatma_personel_gonderildi');

        Log::info('[RND-SMS] cron tick', [
            'simdi' => date('d.m.Y H:i'),
            'aday_randevu_sayisi' => $randevular->count(),
        ]);

        // 1-GUN-ONCE MUSTERI HATIRLATMASI — musteri+salon bazinda GRUPLU.
        // Bir musterinin yarinki tum randevulari TEK mesajda toplanir; eskiden
        // her randevu icin ayri ayri mesaj atiliyordu (4 randevu = 4 SMS).
        $this->birGunOnceGrupluGonder($randevular, $wa, $controller);

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
                    $tarihStr = date('d.m.Y', strtotime($value->tarih));
                    $hizmetMetni = $this->hizmetMetniOlustur($value);
                    $mesaj = 'Sayın ' . optional($value->users)->name . '; ' . $tarihStr . ' tarihinde saat ' . $saat . ' ' . $hizmetMetni . 'randevunuzu hatırlatmak isteriz görüşmek üzere ✨';
                    $templateCtx = ['key' => 'yaklasan', 'params' => [$saat, $value->salonlar->salon_adi]];
                    $this->musteriyeGonder($wa, $controller, $value, $ayar, $mesaj, $templateCtx);
                } elseif ($ayar) {
                    Log::info('[RND-SMS] müşteri SMS toggle kapali — atlandi', ['randevu_id' => $value->id]);
                }
            }

            // (1-gun-once musteri hatirlatmasi artik birGunOnceGrupluGonder()
            // metodu icinde GRUPLU sekilde isleniyor — bu noktada per-randevu degil)

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

            // Idempotency: bu randevu icin personel/yonetici hatirlatmasi daha once
            // gonderildiyse tekrar gonderme. Atomik claim — es zamanli/overlap eden
            // cron kosulari ayni bildirimi cogaltmasin.
            if ($personelFlagVar) {
                $claimed = \Illuminate\Support\Facades\DB::table('randevular')
                    ->where('id', $value->id)
                    ->whereNull('hatirlatma_personel_gonderildi')
                    ->update(['hatirlatma_personel_gonderildi' => now()]);
                if (!$claimed) {
                    Log::info('[RND-SMS] personel hatirlatmasi zaten gonderilmis — atlandi', ['randevu_id' => $value->id]);
                    continue;
                }
            }

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
            // DISTINCT + array_unique: bir yoneticinin role_id<5 olan birden fazla
            // rolu varsa JOIN ayni salon_personelleri.id'yi tekrar dondurup ayni kisiye
            // mukerrer bildirim/push gonderiyordu. Tekillestir.
            $yoneticiIdleri = Personeller::join('model_has_roles', 'salon_personelleri.yetkili_id', '=', 'model_has_roles.model_id')
                ->where('salon_personelleri.salon_id', $value->salon_id)
                ->where('model_has_roles.role_id', '<', 5)
                ->whereNotIn('salon_personelleri.id', $atanmisPersonelIdleri)
                ->distinct()
                ->pluck('salon_personelleri.id')->toArray();
            $yoneticiIdleri = array_values(array_unique($yoneticiIdleri));
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
            $sonuc = $wa->sendReminder($salon, $telefon, $mesajBase, $randevuId, null, null, false, 'personel_hatirlatma');
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
            $personalized = $mesajBase;
            $sonuc = $wa->sendReminder($salon, $musteri->cep_telefon, $personalized, $randevu->id, $musteri->id, $templateCtx, false, 'randevu_hatirlatma');
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
            // SMS'te gönderici kimliği görünmediği için işletme adını başa ekle.
            // (WhatsApp'ta mesaj salonun kendi numarasından gittiği için eklenmez.)
            $smsMesaj = !empty($salon->salon_adi)
                ? $salon->salon_adi . ' - ' . $mesajBase
                : $mesajBase;
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

    /**
     * 1-gün-öncesi müşteri hatırlatmasını (salon_id, user_id) bazında GRUPLAR.
     * Aynı müşterinin yarınki tüm randevuları TEK mesajda toplanır.
     * Eskiden her randevu icin ayri mesaj atiliyordu (4 randevu = 4 SMS).
     * Mesaj formati: "Sayin X; tarih tarihinde 10:00 hizmetA, 10:30 hizmetB, ...
     *                 randevularinizi hatirlatmak isteriz gorusmek uzere ✨"
     */
    protected function birGunOnceGrupluGonder($randevular, $wa, $controller)
    {
        $yarinTarih = date('Y-m-d', strtotime('+1 day'));

        $yarinRandevulari = $randevular->filter(function ($r) use ($yarinTarih) {
            return $r->tarih === $yarinTarih
                && empty($r->hatirlatma_gunonce_gonderildi)
                && $r->salon_id && $r->salonlar
                && $r->users;
        });

        if ($yarinRandevulari->isEmpty()) return;

        $gruplar = $yarinRandevulari->groupBy(function ($r) {
            return $r->salon_id . '|' . $r->user_id;
        });

        foreach ($gruplar as $grup) {
            $ilk = $grup->first();
            $salon = $ilk->salonlar;
            $musteri = $ilk->users;
            $randevuIdler = $grup->pluck('id')->all();

            // SMS ayari kontrolu (1gun = ayar_id=6)
            $ayar = SalonSMSAyarlari::where('salon_id', $salon->id)->where('ayar_id', 6)->first();
            if (!$ayar || !$ayar->musteri) {
                // Toggle kapali — tekrar tekrar denenmesin diye flag'leri set et
                \Illuminate\Support\Facades\DB::table('randevular')
                    ->whereIn('id', $randevuIdler)
                    ->whereNull('hatirlatma_gunonce_gonderildi')
                    ->update(['hatirlatma_gunonce_gonderildi' => now()]);
                Log::info('[RND-SMS] 1 gun once GRUP atlandi (ayar kapali)', [
                    'salon_id' => $salon->id, 'user_id' => $musteri->id,
                    'randevu_idler' => $randevuIdler,
                ]);
                continue;
            }

            // Atomik grup claim: bu (salon, musteri) icin flag null olan
            // tum randevulari bir kerede claim et. Sayi = bu cron'un kazandigi.
            $claimed = \Illuminate\Support\Facades\DB::table('randevular')
                ->whereIn('id', $randevuIdler)
                ->whereNull('hatirlatma_gunonce_gonderildi')
                ->update(['hatirlatma_gunonce_gonderildi' => now()]);

            if ($claimed === 0) {
                // Baska bir cron tick'i bu arada claim etmis — atla
                continue;
            }

            // Hizmet + saat satirlarini topla (tum randevulardaki tum hizmetler)
            $sortedGrup = $grup->sortBy('saat');
            $satirlar = [];
            foreach ($sortedGrup as $r) {
                foreach ($r->hizmetler as $h) {
                    $hAd = optional($h->hizmetler)->hizmet_adi;
                    if (!$hAd) continue;
                    $satirlar[] = date('H:i', strtotime($h->saat)) . ' ' . $hAd;
                }
            }
            $satirlar = array_values(array_unique($satirlar));
            if (empty($satirlar)) {
                // Hizmet adlari cikmadi (iliski eksik) — sade fallback
                $satirlar = $sortedGrup->map(function ($r) {
                    return date('H:i', strtotime($r->saat));
                })->unique()->values()->all();
            }

            $tarihStr = date('d.m.Y', strtotime($yarinTarih));
            $hizmetListesi = implode(', ', $satirlar);
            $birdenCok = count($satirlar) > 1;
            $randevuKelime = $birdenCok ? 'randevularınızı' : 'randevunuzu';
            $mesaj = 'Sayın ' . $musteri->name . '; ' . $tarihStr . ' tarihinde '
                . $hizmetListesi . ' ' . $randevuKelime
                . ' hatırlatmak isteriz görüşmek üzere ✨';

            $ilkSaat = date('H:i', strtotime($sortedGrup->first()->saat));
            $templateCtx = ['key' => '1gun', 'params' => [$ilkSaat, $salon->salon_adi]];

            Log::info('[RND-SMS] 1 gun once GRUPLU gonderim', [
                'salon_id' => $salon->id,
                'salon' => $salon->salon_adi,
                'user_id' => $musteri->id,
                'randevu_idler' => $randevuIdler,
                'hizmet_sayisi' => count($satirlar),
                'claimed' => $claimed,
            ]);

            // musteriyeGonder ilk randevu uzerinden cagrilir (telefon, salon, vs ondan alinir)
            $this->musteriyeGonder($wa, $controller, $ilk, $ayar, $mesaj, $templateCtx);
        }
    }

    // Randevudaki hizmet adlarini "solaryum, saç kesimi " seklinde birlestirir.
    // Birden fazla hizmet varsa toplu yazilir; hizmet yoksa bos string doner.
    protected function hizmetMetniOlustur($randevu)
    {
        $adlar = $randevu->hizmetler
            ->map(function ($h) { return optional($h->hizmetler)->hizmet_adi; })
            ->filter()
            ->unique()
            ->values()
            ->all();

        return empty($adlar) ? '' : implode(', ', $adlar) . ' ';
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
