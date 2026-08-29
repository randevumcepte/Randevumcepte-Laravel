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
            // EAGER LOADING: dongude $value->salonlar / ->users / ->hizmetler->hizmetler
            // her aday icin lazy-load ediliyordu (her dakika N+1). Tek batch'e indir.
            ->with(['salonlar', 'users', 'hizmetler.hizmetler'])
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
            // PUSH icin sabit tetik: SMS/WA salon ayarindan (X saat once) bagimsiz,
            // her randevu icin 3 saat once musteri + personel + yonetici push atilir.
            $tetik3SaatPush = date('d.m.Y H:i', strtotime('-3 hours', strtotime($randevutarihsaat)));
            if ($simdi == $tetik3SaatPush) {
                $this->pushHatirlatmaGonder($value, '3saat');
            }

            // PER-ROW DEBUG LOG DEVRE DISI (2026-06-26): her aday icin her dakika
            // yaziliyordu (~288K satir/gun, disk I/O). Sadece TETIK aninda loglama
            // birakildi (asagidaki "tetiklendi" / kanal karar / WA sonuc loglari).
            // Debug gerekirse asagidaki yorumu ac:
            /*
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
            */

            // Müşteriye hatırlatma (salon kendi belirlediği X saat önce)
            // NOT: Push, SMS/WA toggle'dan BAGIMSIZ. musteriyeGonder icinde ayar
            // kontrolu WA/SMS blogunu koruyor; push blogu her zaman calisir.
            // Personel/yonetici tarafi ile ayni pattern (bkz. 167-177, 197-208).
            if ($simdi == $tetikSalonSaat) {
                $ayar = SalonSMSAyarlari::where('salon_id', $value->salon_id)->where('ayar_id', 1)->first();
                Log::info('[RND-SMS] müşteri salon-saati tetiklendi', [
                    'randevu_id' => $value->id,
                    'ayar_var' => (bool) $ayar,
                    'ayar_musteri' => $ayar ? (int) $ayar->musteri : null,
                    'ayar_wa_musteri' => $ayar ? (int) ($ayar->whatsapp_musteri ?? 0) : null,
                ]);
                $saat = date('H:i', strtotime($value->saat));
                $tarihStr = date('d.m.Y', strtotime($value->tarih));
                $hizmetMetni = $this->hizmetMetniOlustur($value);
                // SMS icin: eski kisa format (karakter tasarrufu)
                $mesajSMS = 'Sayın ' . optional($value->users)->name . '; ' . $tarihStr . ' tarihinde saat ' . $saat . ' ' . $hizmetMetni . 'randevunuzu hatırlatmak isteriz görüşmek üzere ✨';
                // WA icin: zenginlestirilmis format (konum + telefon + emoji + hizmet)
                $hizmetlerRich = trim($this->hizmetMetniOlustur($value), ' ,');
                $mesajWA = $this->whatsAppRichMetin($value->salonlar, $value->users, $tarihStr, $saat, 'randevunuzu', $hizmetlerRich);
                $templateCtx = ['key' => 'yaklasan', 'params' => [$saat, $value->salonlar->salon_adi]];
                // musteriyeGonder her cagride push atar; WA/SMS blogunu ic gate kontrol eder.
                $this->musteriyeGonder($wa, $controller, $value, $ayar, $mesajWA, $templateCtx, $mesajSMS);
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

            // Hizmeti atanmış personele SMS/WA (her personel kendi hizmet listesiyle)
            // PUSH: burada YOK. Push, 3 saat once sabit tetikte pushHatirlatmaGonder()
            // uzerinden gonderiliyor (SMS/WA zamanlamasindan bagimsiz).
            foreach ($personelHizmetMap as $pid => $satirlar) {
                $mesaj = $value->users->name . ' isimli müşterinin bugün ' . implode(', ', $satirlar) . ' randevu' . (count($satirlar) > 1 ? 'larını' : 'sunu') . ' hatırlatmak isteriz.';

                if ($personelAyari && $personelAyari->personel) {
                    $personelTelefon = Personeller::where('id', $pid)->value('cep_telefon');
                    $this->personeleGonder($wa, $controller, $value->salonlar, $personelTelefon, $mesaj, $personelAyari, $value->id);
                }

                self::bildirimekle($value->salon_id, $mesaj, '#', $pid, $value->user_id, $value->users->profil_resim, $value->id, null);
            }

            // Yönetici SMS/WA - Yönetici için SMS/WA kanali kod olarak bugun sadece
            // atanmis personel tarafinda calisiyor (personelAyari->personel togglei).
            // Yonetici PUSH ise pushHatirlatmaGonder icinde (3 saat once) atiliyor.
            // Yönetici inbox kaydini (bildirimler tablosu) uyumlu tutmak icin listeye ekle:
            $tumSatirlar = array_column($tetiklenenHizmetler, 'satir');
            $yoneticiMesaji = $value->users->name . ' isimli müşterinin bugün ' . implode(', ', $tumSatirlar) . ' randevu' . (count($tumSatirlar) > 1 ? 'larını' : 'sunu') . ' hatırlatmak isteriz.';

            $atanmisPersonelIdleri = array_keys($personelHizmetMap);
            $yoneticiIdleri = Personeller::join('model_has_roles', 'salon_personelleri.yetkili_id', '=', 'model_has_roles.model_id')
                ->where('salon_personelleri.salon_id', $value->salon_id)
                ->where('model_has_roles.role_id', '<', 5)
                ->whereNotIn('salon_personelleri.id', $atanmisPersonelIdleri)
                ->distinct()
                ->pluck('salon_personelleri.id')->toArray();
            $yoneticiIdleri = array_values(array_unique($yoneticiIdleri));
            foreach ($yoneticiIdleri as $yId) {
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

        // Paylasilan oturum: 416 icin 246'nin durumu kontrol edilir.
        $waSalon = \App\Services\WhatsAppService::resolveWaSalon($salon);
        $whatsappKanaliAcik = !empty($waSalon->whatsapp_aktif)
            && $waSalon->whatsapp_durum === 'connected';

        Log::info('[RND-SMS] personel kanal karar', [
            'salon_id' => $salon->id,
            'wa_session_salon_id' => $waSalon->id,
            'randevu_id' => $randevuId,
            'telefon' => $telefon,
            'wa_aktif' => (int) ($waSalon->whatsapp_aktif ?? 0),
            'wa_durum' => $waSalon->whatsapp_durum,
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

    protected function musteriyeGonder(WhatsAppService $wa, Controller $controller, $randevu, $ayar, $mesajBase, $templateCtx = null, $mesajSMS = null)
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

        // Paylasilan oturum: 416 icin 246'nin WA durumu/token'i kontrol edilir.
        $waSalon = \App\Services\WhatsAppService::resolveWaSalon($salon);
        $saglayici = $waSalon->whatsapp_saglayici ?? 'baileys';
        if ($saglayici === 'cloud_api') {
            // Cloud API: token + phone_number_id + ilgili template adı varsa kanal açık
            $templateField = isset($templateCtx['key']) ? 'cloud_api_template_' . $templateCtx['key'] : null;
            $whatsappKanaliAcik = !empty($waSalon->cloud_api_token)
                && !empty($waSalon->cloud_api_phone_number_id)
                && ($templateField ? !empty($waSalon->{$templateField}) : false);
        } else {
            // Baileys: aktif + connected (ayar kolonu kontrolu kaldirildi - salon WA acikken her zaman WA dene)
            $whatsappKanaliAcik = !empty($waSalon->whatsapp_aktif)
                && $waSalon->whatsapp_durum === 'connected';
        }

        $musteriOnayli = !Schema::hasColumn('users', 'whatsapp_onay') || (int) ($musteri->whatsapp_onay ?? 1) === 1;

        Log::info('[RND-SMS] müşteri kanal karar', [
            'salon_id' => $salon->id,
            'wa_session_salon_id' => $waSalon->id,
            'randevu_id' => $randevu->id,
            'musteri_id' => $musteri->id,
            'telefon' => $musteri->cep_telefon,
            'saglayici' => $saglayici,
            'wa_aktif' => (int) ($waSalon->whatsapp_aktif ?? 0),
            'wa_durum' => $waSalon->whatsapp_durum,
            'wa_kanali_acik' => $whatsappKanaliAcik,
            'musteri_onayli' => $musteriOnayli,
        ]);

        // SMS/WA gate: $ayar->musteri = 0 ise SMS ve WA gonderme; push asagida
        // her zaman calisir. Onceden bu gate cagirandaki if bloguydu; push da
        // atlaniyordu. Push'un SMS/WA'dan bagimsiz kalmasi icin gate'i buraya
        // tasidik. Personel/yonetici tarafiyla ayni pattern.
        $smsWaAcik = $ayar && $ayar->musteri;

        if ($smsWaAcik && $whatsappKanaliAcik && $musteriOnayli) {
            $whatsappDenendi = true;
            // WA metni SMS ile birebir aynı olsun (Cloud API kendi template'ini kullanır).
            $personalized = $mesajBase;
            // Uygulaması OLMAYAN müşteriye, salonun uygulama linki (uygulamalar_kisa_link)
            // tanımlıysa SADECE WhatsApp'ta indirme daveti ekle (SMS'e eklenmez).
            // Aktif push token'ı varsa = uygulaması var -> eklenmez. Cloud API
            // salonlarda template kullanıldığından bu ek metin görünmez; yalnızca
            // whatsmeow serbest-metin gönderiminde alta eklenir.
            // Uygulaması olmayana WA'da indirme daveti + link ekle (merkezî yardımcı)
            $personalized = \App\Services\WhatsAppMesajFormat::uygulamaDavetiEk($personalized, $salon, $musteri->id);
            // Gönderim tipi: "1 gün önce" hatırlatma ÜCRETSİZ (kontör düşmez),
            // "yaklaşan" (X saat önce) ve diğer tüm mesajlar ücretli.
            $_gonderimTipi = (isset($templateCtx['key']) && $templateCtx['key'] === '1gun')
                ? 'randevu_hatirlatma_1gun'
                : 'randevu_hatirlatma';
            $sonuc = $wa->sendReminder($salon, $musteri->cep_telefon, $personalized, $randevu->id, $musteri->id, $templateCtx, false, $_gonderimTipi);
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

        if ($smsWaAcik && !$whatsappBasarili) {
            // SMS'te gönderici kimliği görünmediği için işletme adını başa ekle.
            // (WhatsApp'ta mesaj salonun kendi numarasından gittiği için eklenmez.)
            // WA icin zenginlestirilmis format kullanildiginda $mesajSMS verilir;
            // SMS icin bu daha kisa metni kullan. Verilmediyse eski davranis.
            $smsBase = $mesajSMS ?: $mesajBase;
            $smsMesaj = !empty($salon->salon_adi)
                ? $salon->salon_adi . ' - ' . $smsBase
                : $smsBase;
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
        } elseif (!$smsWaAcik) {
            Log::info('[RND-SMS] müşteri SMS/WA toggle kapali — atlandi', [
                'salon_id' => $salon->id, 'randevu_id' => $randevu->id,
            ]);
        }
        // NOT: Push burada YOK. Push tetigi (3 saat once sabit + 1 gun once) ayri
        // yerlerde: ana loop'ta pushHatirlatmaGonder() ve birGunOnceGrupluGonder().
        // Amac: push'u SMS/WA zamanlamasindan tamamen ayirmak, salon ayarindan bagimsiz.
    }

    /**
     * Randevu push hatirlatmasini herkese gonderir: musteri + atanan personeller +
     * salon yoneticileri (role<5). SMS/WA'dan tamamen bagimsiz.
     *
     * @param  \App\Randevular  $value
     * @param  string  $etiket  '3saat' veya '1gun' — sadece log/mesaj ayrimi icin
     */
    protected function pushHatirlatmaGonder($value, string $etiket = '3saat')
    {
        $salonId = (int) $value->salon_id;
        $randevuId = (int) $value->id;
        $musteriAdi = optional($value->users)->name ?? 'Müşteri';
        $tarihStr = date('d.m.Y', strtotime($value->tarih));
        $saatStr = date('H:i', strtotime($value->saat));
        $salonAdi = optional($value->salonlar)->salon_adi ?? 'Salon';

        // Hizmet listesi (tekil satirlarda "10:00 Sac Kesimi" formati)
        $hizmetSatirlari = [];
        foreach ($value->hizmetler ?? [] as $h) {
            $hAdi = optional($h->hizmetler)->hizmet_adi;
            if (!$hAdi) continue;
            $hizmetSatirlari[] = date('H:i', strtotime($h->saat)) . ' ' . $hAdi;
        }
        $hizmetListesi = implode(', ', $hizmetSatirlari);

        $on = $etiket === '1gun' ? '1 gün' : '3 saat';

        $musteriBody = 'Sayın ' . $musteriAdi . '; ' . $tarihStr . ' saat ' . $saatStr
            . ' ' . ($hizmetListesi ? $hizmetListesi . ' ' : '')
            . 'randevunuza ' . $on . ' kaldı, hatırlatmak isteriz ✨';

        $personelBody = $musteriAdi . ' isimli müşterinin ' . $tarihStr . ' '
            . ($hizmetListesi ?: $saatStr) . ' randevusuna ' . $on . ' kaldı.';

        // 1) Musteri push
        if (!empty($value->user_id)) {
            try {
                \App\Services\NotificationService::toCustomer((int) $value->user_id, $salonId)
                    ->type(\App\Services\NotificationTypes::APPOINTMENT_REMINDER)
                    ->title('Randevu Hatırlatma')
                    ->body($musteriBody)
                    ->randevu($randevuId)
                    ->deepLink('appointment_detail', ['randevu_id' => $randevuId])
                    ->send();
            } catch (\Throwable $e) {
                Log::warning('[RND-PUSH] musteri push fail', ['etiket' => $etiket, 'randevu_id' => $randevuId, 'err' => $e->getMessage()]);
            }
        }

        // 2) Atanan hizmet personellerine push (kisi basi tek push, hizmetler gruplu)
        $atanmisPersonelIdleri = [];
        foreach ($value->hizmetler ?? [] as $h) {
            if ($h->personel_id) $atanmisPersonelIdleri[] = (int) $h->personel_id;
        }
        $atanmisPersonelIdleri = array_values(array_unique($atanmisPersonelIdleri));
        foreach ($atanmisPersonelIdleri as $pid) {
            try {
                \App\Services\NotificationService::toStaff($pid, $salonId)
                    ->type(\App\Services\NotificationTypes::APPOINTMENT_REMINDER)
                    ->title('Randevu Hatırlatma')
                    ->body($personelBody)
                    ->randevu($randevuId)
                    ->deepLink('appointment_detail', ['randevu_id' => $randevuId])
                    ->send();
            } catch (\Throwable $e) {
                Log::warning('[RND-PUSH] personel push fail', ['etiket' => $etiket, 'randevu_id' => $randevuId, 'personel_id' => $pid, 'err' => $e->getMessage()]);
            }
        }

        // 3) Salon yoneticilerine push (role_id<5, atanmislari haric)
        try {
            $yoneticiIdleri = Personeller::join('model_has_roles', 'salon_personelleri.yetkili_id', '=', 'model_has_roles.model_id')
                ->where('salon_personelleri.salon_id', $salonId)
                ->where('model_has_roles.role_id', '<', 5)
                ->whereNotIn('salon_personelleri.id', $atanmisPersonelIdleri)
                ->distinct()
                ->pluck('salon_personelleri.id')->toArray();
            $yoneticiIdleri = array_values(array_unique($yoneticiIdleri));
            foreach ($yoneticiIdleri as $yid) {
                try {
                    \App\Services\NotificationService::toStaff((int) $yid, $salonId)
                        ->type(\App\Services\NotificationTypes::APPOINTMENT_REMINDER)
                        ->title('Randevu Hatırlatma')
                        ->body($personelBody)
                        ->randevu($randevuId)
                        ->deepLink('appointment_detail', ['randevu_id' => $randevuId])
                        ->send();
                } catch (\Throwable $e) {
                    Log::warning('[RND-PUSH] yonetici push fail', ['etiket' => $etiket, 'randevu_id' => $randevuId, 'yonetici_id' => $yid, 'err' => $e->getMessage()]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[RND-PUSH] yonetici listesi fail', ['etiket' => $etiket, 'randevu_id' => $randevuId, 'err' => $e->getMessage()]);
        }

        Log::info('[RND-PUSH] hatirlatma push atildi', [
            'etiket' => $etiket, 'randevu_id' => $randevuId,
            'salon_id' => $salonId, 'user_id' => $value->user_id,
        ]);
    }

    /**
     * 1-gün-öncesi müşteri hatırlatmasını (salon_id, user_id) bazında GRUPLAR.
     * Aynı müşterinin yarınki tüm randevuları TEK mesajda toplanır.
     *
     * Davranis:
     *   - Sadece 17:00-19:00 saat araliginda gonderilir
     *   - Pencere icinde deterministik stagger (salon_id*31 + user_id*7 mod 120dk)
     *     ile dakika dakika dagitilir (anti-burst)
     *   - Ayar (SalonSMSAyarlari ayar_id=6, musteri toggle) kapaliysa atlanir,
     *     flag set edilir (bir daha denenmesin)
     *
     * NOT: <24h kala oluşturulan randevuda toggle bypass'i BURADA degil
     *      randevu olusturma noktasinda yapilir (StoreAdminController),
     *      cunku 19:00 sonrasi pencere zaten gecmis olur.
     */
    protected function birGunOnceGrupluGonder($randevular, $wa, $controller)
    {
        $yarinTarih = date('Y-m-d', strtotime('+1 day'));
        $now = time();
        $nowMinuteOfDay = ((int) date('G', $now)) * 60 + (int) date('i', $now);

        // 17:00-19:00 = dakika cinsinden 1020-1140 araligi (120dk pencere)
        $winStart = 17 * 60;
        $winEnd   = 19 * 60;
        $bucketSize = $winEnd - $winStart; // 120

        // Pencere disindaysak hicbir sey yapma (her cron tick bos cikar)
        if ($nowMinuteOfDay < $winStart || $nowMinuteOfDay >= $winEnd) {
            return;
        }

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

            // Deterministik stagger: ayni (salon, musteri) hep ayni dakikaya duser
            $stagger = (int) ((($salon->id * 31) + ($musteri->id * 7)) % $bucketSize);
            $targetMinute = $winStart + $stagger;

            if ($nowMinuteOfDay < $targetMinute) {
                // Henuz stagger dakikasi gelmedi
                continue;
            }

            // SMS ayari kontrolu (1gun = ayar_id=6)
            // NOT: Ayar kapali olsa dahi PUSH atilir (musteriyeGonder icinde SMS/WA
            // toggle'a bagli, push bagimsiz). Flag'i her durumda claim edelim ki
            // musteriye ayni push tekrar tekrar yagmasın.
            $ayar = SalonSMSAyarlari::where('salon_id', $salon->id)->where('ayar_id', 6)->first();

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

            if (!$ayar || !$ayar->musteri) {
                Log::info('[RND-SMS] 1 gun once GRUP: SMS/WA toggle kapali — sadece push', [
                    'salon_id' => $salon->id, 'user_id' => $musteri->id,
                    'randevu_idler' => $randevuIdler,
                ]);
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

            // SMS icin: kisa format
            $mesajSMS = 'Sayın ' . $musteri->name . '; ' . $tarihStr . ' tarihinde '
                . $hizmetListesi . ' ' . $randevuKelime
                . ' hatırlatmak isteriz görüşmek üzere ✨';

            // WA icin: zenginlestirilmis format. Coklu randevu ise saat listesini,
            // tek randevu ise tek saati goster.
            $saatler = $sortedGrup->map(function ($r) {
                return date('H:i', strtotime($r->saat));
            })->unique()->values()->implode(', ');

            // Hizmet listesi (uniqe, "solaryum, cilt bakimi" formatinda) — saatsiz,
            // saat zaten yukarida ayri satirda gosteriliyor.
            $hizmetAdlari = collect();
            foreach ($sortedGrup as $r) {
                foreach ($r->hizmetler as $h) {
                    $hAd = optional($h->hizmetler)->hizmet_adi;
                    if ($hAd) $hizmetAdlari->push($hAd);
                }
            }
            $hizmetlerRich = $hizmetAdlari->unique()->values()->implode(', ');
            $mesajWA = $this->whatsAppRichMetin($salon, $musteri, $tarihStr, $saatler, $randevuKelime, $hizmetlerRich);

            $ilkSaat = date('H:i', strtotime($sortedGrup->first()->saat));
            $templateCtx = ['key' => '1gun', 'params' => [$ilkSaat, $salon->salon_adi]];

            Log::info('[RND-SMS] 1 gun once GRUPLU gonderim', [
                'salon_id' => $salon->id,
                'salon' => $salon->salon_adi,
                'user_id' => $musteri->id,
                'randevu_idler' => $randevuIdler,
                'hizmet_sayisi' => count($satirlar),
                'claimed' => $claimed,
                'stagger_dk' => $stagger,
                'target_dk' => $targetMinute,
            ]);

            // musteriyeGonder ilk randevu uzerinden cagrilir (telefon, salon, vs ondan alinir)
            // Bu cagri sadece SMS/WA gonderir (push kismi musteriyeGonder icinden cikarildi).
            $this->musteriyeGonder($wa, $controller, $ilk, $ayar, $mesajWA, $templateCtx, $mesajSMS);

            // 1 gun once push: gruptaki HER randevu icin ayri push at
            // (musteri + atanan personel + role<5 yonetici). SMS/WA'dan bagimsiz.
            foreach ($sortedGrup as $r) {
                try { $this->pushHatirlatmaGonder($r, '1gun'); }
                catch (\Throwable $e) { Log::warning('[RND-PUSH] 1gun push fail', ['randevu_id' => $r->id, 'err' => $e->getMessage()]); }
            }
        }
    }

    /**
     * WhatsApp icin ZENGIN formatli hatirlatma mesaji uretir.
     * SMS'ten farkli (link + emoji + markdown vurgu). Konum_linki / telefon_1
     * varsa gosterilir, yoksa o satir atlanir.
     */
    protected function whatsAppRichMetin($salon, $musteri, $tarihStr, $saatlerStr, $randevuKelime, $hizmetlerStr = '')
    {
        $mesaj  = "🌸 Merhaba, *" . $musteri->name . "*,\n\n";
        $mesaj .= ($salon->salon_adi ?? 'Salon') . " için size yaklaşan " . $randevuKelime . " hatırlatmak isteriz. 💖\n\n";
        $mesaj .= "📅 *Tarih:* " . $tarihStr . "\n";
        $mesaj .= "🕒 *Saat:* " . $saatlerStr . "\n";
        if (!empty($hizmetlerStr)) {
            $mesaj .= "✂️ *Hizmetler:* " . $hizmetlerStr . "\n";
        }
        $mesaj .= "\nSizi zamanında ve en iyi şekilde ağırlayabilmemiz için randevu saatinizden birkaç dakika önce salonda olmanızı rica ederiz.\n\n";

        if (!empty($salon->konum_linki)) {
            $mesaj .= "📍 *Konum:* " . $salon->konum_linki . "\n";
        }
        if (!empty($salon->telefon_1)) {
            $tel = ltrim((string) $salon->telefon_1);
            // Basina 0 yoksa ve numara rakamla basliyorsa 0 ekle
            // (ornek: "2121234567" -> "02121234567"). +90 gibi uluslararasi
            // format zaten '+' ile basladigi icin dokunulmaz.
            if ($tel !== '' && ctype_digit($tel[0]) && $tel[0] !== '0') {
                $tel = '0' . $tel;
            }
            $mesaj .= "📞 *Salon Telefonu:* " . $tel . "\n";
        }

        $mesaj .= "\nHerhangi bir değişiklik yapmak veya bizimle iletişime geçmek isterseniz bu WhatsApp hattından ya da telefon numaramızdan bize ulaşabilirsiniz.\n\n";
        $mesaj .= "Bizi tercih ettiğiniz için teşekkür eder, sizi ağırlamayı sabırsızlıkla bekleriz. 💐";

        return $mesaj;
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
