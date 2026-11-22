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
use App\MusteriPortfoy;
use App\SalonEAsistanAyarlari;
use App\Jobs\HatirlatmaAramaJob;

class RandevuSMSHatirlatma extends Command
{
    protected $signature = 'randevusms:hatirlat';
    protected $description = 'Randevu WhatsApp/SMS, bildirim ve sesli asistan hatırlatmaları';

    /** Bu turda kuyruga eklenecek hatirlatma aramalari (handle sonunda chunk'li dispatch). */
    protected $aramaListesi = [];
    /** aramaListesi ile ayni sirada 'ilk' / 'tekrar' (ozet log icin) */
    protected $aramaEtiketleri = [];
    /** salon_id => e-asistan randevu hatirlatma aramasi (ayar_id=4) acik mi */
    protected $aramaAyarCache = [];
    /** Ayni anda santrale verilecek arama sayisi / chunk'lar arasi bekleme (sn) */
    protected $aramaChunk = 50;
    protected $aramaChunkArasi = 35;

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

        // Hatirlatma istisnalari — salon_id => hizmet_adi (lowercase) icinde gecen pattern'ler.
        // Bu kalemlerin hizmet olarak bulundugu randevulara hatirlatma MESAJI gonderilmez
        // (ne musteriye ne personele ne push). Istek: 195 "Arama Randevusu" hizmeti.
        $_hatirlatmaIstisnalari = [
            195 => ['arama randev'], // "Arama Randevu" / "Arama Randevusu" vb. yakalar
        ];
        if (!empty($_hatirlatmaIstisnalari)) {
            $_oncekiSayi = $randevular->count();
            $randevular = $randevular->reject(function ($r) use ($_hatirlatmaIstisnalari) {
                if (!isset($_hatirlatmaIstisnalari[(int) $r->salon_id])) return false;
                $patterns = $_hatirlatmaIstisnalari[(int) $r->salon_id];
                foreach (($r->hizmetler ?? []) as $rh) {
                    $ad = mb_strtolower((string) optional($rh->hizmetler)->hizmet_adi);
                    if ($ad === '') continue;
                    foreach ($patterns as $p) {
                        if (mb_stripos($ad, $p) !== false) return true; // reject
                    }
                }
                return false;
            })->values();
            $_atlanan = $_oncekiSayi - $randevular->count();
            if ($_atlanan > 0) {
                Log::info('[RND-SMS] hatirlatma istisnasi atlandi', ['sayi' => $_atlanan]);
            }
        }

        $controller = app()->make(Controller::class);
        $wa = app(WhatsAppService::class);

        // Personel/yonetici hatirlatmasi randevu basina yalnizca BIR kez gonderilsin
        // diye idempotency bayragi (cron overlap / yeniden tetikleme korumasi).
        $personelFlagVar = Schema::hasColumn('randevular', 'hatirlatma_personel_gonderildi');
        // Musteri "X saat once" hatirlatmasi icin idempotency bayragi (grace penceresi fix)
        $musteriFlagVar = Schema::hasColumn('randevular', 'hatirlatma_musteri_gonderildi');

        Log::info('[RND-SMS] cron tick', [
            'simdi' => date('d.m.Y H:i'),
            'aday_randevu_sayisi' => $randevular->count(),
        ]);

        // 1-GUN-ONCE MUSTERI HATIRLATMASI — musteri+salon bazinda GRUPLU.
        // Bir musterinin yarinki tum randevulari TEK mesajda toplanir; eskiden
        // her randevu icin ayri ayri mesaj atiliyordu (4 randevu = 4 SMS).
        $this->birGunOnceGrupluGonder($randevular, $wa, $controller);

        // Ulasilamayan hatirlatma aramalarinin tekrari (salon "tekrar arama" saati sonra).
        // Ilk arama birGunOnceGrupluGonder icinde WA/SMS ile ayni dakikada kuyruga girer.
        // Eskiden ayri `randevuarama:yap` komutu her dakika randevular tablosunu tariyordu;
        // artik zaten yuklenen bugun+yarin listesi kullanilir (ek sorgu yok).
        try {
            $this->tekrarAramalari($randevular, $controller);
        } catch (\Throwable $e) {
            Log::error('[RND-ARAMA] tekrar arama hatasi: ' . $e->getMessage());
        }
        $this->aramalariKuyrugaGonder();

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
                // Push kanal kararlari pushHatirlatmaGonder icinde (hatirlatma_yaklasan)
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
            // Musteri "X saat once" hatirlatmasi — GRACE PENCERESI + idempotency.
            // Eskiden tam-dakika esitligi (simdi==tetik) idi; cron o dakikayi kacirirsa
            // (bugun ders tarafinda 13:45 turu komple atlanmisti) hatirlatma kaciyordu.
            // Simdi: tetik aninDAN tetik+15dk'ya kadar herhangi bir turda gonder, atomik
            // claim ile TEK sefer. Bayrak kolonu yoksa eski exact-dakika davranisina duser.
            $musteriGonder = false;
            if ($musteriFlagVar) {
                $simdiTs   = strtotime($simdi);
                $tetikTs   = strtotime($tetikSalonSaat);
                $randevuTs = strtotime($randevutarihsaat);
                $pencereSon = min($tetikTs + 900, $randevuTs); // +15dk, randevu baslamadan
                if ($simdiTs >= $tetikTs && $simdiTs < $pencereSon) {
                    $claimed = Randevular::where('id', $value->id)
                        ->whereNull('hatirlatma_musteri_gonderildi')
                        ->update(['hatirlatma_musteri_gonderildi' => now()]);
                    $musteriGonder = ($claimed === 1);
                    if ($claimed !== 1) {
                        Log::info('[RND-SMS] musteri hatirlatmasi zaten gonderilmis — atlandi', ['randevu_id' => $value->id]);
                    }
                }
            } else {
                $musteriGonder = ($simdi == $tetikSalonSaat);
            }
            if ($musteriGonder) {
                // Kanal secimi bildirim tercihlerinden (eski ayar_id=1 musteri yerine)
                $ayar = 'hatirlatma_yaklasan';
                Log::info('[RND-SMS] müşteri salon-saati tetiklendi', [
                    'randevu_id' => $value->id,
                    'kanallar' => \App\Services\BildirimTercih::kanallar($value->salon_id, $ayar, 'musteri'),
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

            // Personel SMS/WA: bildirim tercihi (eski ayar_id=1 personel yerine)
            $personelMesajAcik = \App\Services\BildirimTercih::mesajAcikMi($value->salon_id, 'hatirlatma_yaklasan', 'personel');

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

                if ($personelMesajAcik) {
                    $personelTelefon = Personeller::where('id', $pid)->value('cep_telefon');
                    $this->personeleGonder($wa, $controller, $value->salonlar, $personelTelefon, $mesaj, 'hatirlatma_yaklasan', $value->id);
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

    protected function personeleGonder(WhatsAppService $wa, Controller $controller, $salon, $telefon, $mesajBase, $kod, $randevuId)
    {
        if (!$telefon) {
            Log::info('[RND-SMS] personel telefon yok — atlandi', ['salon_id' => $salon->id, 'randevu_id' => $randevuId]);
            return;
        }

        // Kanal secimi bildirim tercihlerinden: WA dene <=> whatsapp secili; SMS <=> sms secili veya WA yedegi
        $kanal = \App\Services\BildirimTercih::kanallar($salon->id, $kod, 'personel');

        // Paylasilan oturum: 416 icin 246'nin durumu kontrol edilir.
        $waSalon = \App\Services\WhatsAppService::resolveWaSalon($salon);
        $whatsappKanaliAcik = $kanal['whatsapp'] && !empty($waSalon->whatsapp_aktif)
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
                $this->waYedeksizIsaretle($salon->id, $sonuc, $kanal['sms']);
            } else {
                Log::warning('[RND-SMS] personel WA başarısız → SMS fallback', [
                    'salon_id' => $salon->id, 'randevu_id' => $randevuId,
                    'error' => $sonuc['error'] ?? 'unknown',
                ]);
            }
        }

        if ($this->smsGitsinMi($salon, $kanal, $whatsappBasarili)) {
            Log::info('[RND-SMS] personel SMS gönderiliyor', [
                'salon_id' => $salon->id, 'randevu_id' => $randevuId, 'telefon' => $telefon,
            ]);
            $controller->sms_gonder($salon->id, [[
                'to' => $telefon,
                'message' => $mesajBase,
            ]]);
        }
    }

    protected function musteriyeGonder(WhatsAppService $wa, Controller $controller, $randevu, $kod, $mesajBase, $templateCtx = null, $mesajSMS = null)
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

        // SMS/WA gate: bildirim tercihleri ($kod / musteri). WA yalniz whatsapp seciliyse denenir;
        // SMS sms seciliyse ya da WA secili ama ulasmadiysa (yedek SMS acik + paket var) gider.
        $kanal = \App\Services\BildirimTercih::kanallar($salon->id, $kod, 'musteri');
        $smsWaAcik = $kanal['sms'] || $kanal['whatsapp'];

        if ($kanal['whatsapp'] && $whatsappKanaliAcik && $musteriOnayli) {
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
                $this->waYedeksizIsaretle($salon->id, $sonuc, $kanal['sms']);
            } else {
                Log::warning('[RND-SMS] müşteri WA başarısız → SMS fallback', [
                    'salon_id' => $salon->id,
                    'randevu_id' => $randevu->id,
                    'error' => $sonuc['error'] ?? 'unknown',
                ]);
            }
        }

        if ($smsWaAcik && $this->smsGitsinMi($salon, $kanal, $whatsappBasarili)) {
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
     * SMS gitsin mi: SMS secili VEYA (WhatsApp secili ama ulasmadi/denenemedi VE salonun
     * WA->SMS yedegi acik VE SMS paketi var). WA+SMS ikisi seciliyse ikisi de gider.
     */
    protected function smsGitsinMi($salon, array $kanal, $whatsappBasarili)
    {
        if ($kanal['sms']) return true;
        if (!$kanal['whatsapp'] || $whatsappBasarili) return false;
        if (!\App\Services\BildirimTercih::waYedekSms($salon->id)) return false;
        return \App\Services\BildirimTercih::smsKullanilabilir($salon);
    }

    /** WA kuyruga girdi: SMS ayrica gittiyse veya yedek kapaliysa WA sonradan duserse SMS'e dusmesin. */
    protected function waYedeksizIsaretle($salonId, $sonuc, $smsDeGidiyor)
    {
        if (empty($sonuc['logId'])) return;
        if (!$smsDeGidiyor && \App\Services\BildirimTercih::waYedekSms($salonId)) return;
        try {
            \Illuminate\Support\Facades\DB::table('whatsapp_gonderim_loglari')->where('id', $sonuc['logId'])->update(['sms_yedeksiz' => 1]);
        } catch (\Throwable $e) {}
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

        // Push kararlari bildirim tercihlerinden: 3 saat -> hatirlatma_yaklasan, 1 gun -> hatirlatma_bir_gun_once
        $bkKod = $etiket === '1gun' ? 'hatirlatma_bir_gun_once' : 'hatirlatma_yaklasan';
        $musteriPush = \App\Services\BildirimTercih::izin($salonId, $bkKod, 'musteri', 'push');
        $personelPush = \App\Services\BildirimTercih::izin($salonId, $bkKod, 'personel', 'push');
        // 1 gun once personel SMS/WA bugun yok; isletme secerse ayni metin dagit ile gider (tohumda kapali)
        $personelMesaj = $etiket === '1gun' && \App\Services\BildirimTercih::mesajAcikMi($salonId, $bkKod, 'personel');
        $personelSatirlari = [];

        // 1) Musteri push
        if ($musteriPush && !empty($value->user_id)) {
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
            if ($personelMesaj) $personelSatirlari[] = $pid;
            if (!$personelPush) continue;
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
                if ($personelMesaj) $personelSatirlari[] = (int) $yid;
                if (!$personelPush) continue;
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

        // 1 gun once personel SMS/WA (yalniz isletme sectiyse): push yukarida atildi -> push_ayri
        if ($personelSatirlari) {
            try {
                $telefonlar = Personeller::whereIn('id', array_values(array_unique($personelSatirlari)))
                    ->whereNotNull('cep_telefon')->where('cep_telefon', '!=', '')->pluck('cep_telefon', 'id');
                $satirlar = [];
                foreach ($telefonlar as $pid => $tel) {
                    $satirlar[] = ['to' => $tel, 'message' => $personelBody, 'randevu_id' => $randevuId, 'personel_id' => (int) $pid,
                        'bk' => $bkKod, 'alici' => 'personel', 'push_ayri' => true, 'gonderim_tipi' => 'personel_hatirlatma'];
                }
                $salonObj = $value->salonlar;
                $smsSatirlari = $satirlar ? \App\Services\BildirimTercih::dagit($salonObj, $satirlar, 'personel_hatirlatma') : [];
                if ($smsSatirlari) {
                    $smsSatirlari = array_map(function ($m) { return ['to' => $m['to'], 'message' => $m['message']]; }, $smsSatirlari);
                    app()->make(Controller::class)->sms_gonder($salonId, $smsSatirlari);
                }
            } catch (\Throwable $e) {
                Log::warning('[RND-SMS] 1gun personel mesaj fail', ['randevu_id' => $randevuId, 'err' => $e->getMessage()]);
            }
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
     *   - Sadece 15:00-18:00 saat araliginda gonderilir (salon acikken; musteri
     *     mesaji alinca arayip guncelleme/iptal yapabilsin). TUM salonlar icin.
     *   - Pencere icinde deterministik stagger (salon_id*31 + user_id*7 mod 180dk)
     *     ile dakika dakika dagitilir (anti-burst)
     *   - Ayar (SalonSMSAyarlari ayar_id=6, musteri toggle) kapaliysa atlanir,
     *     flag set edilir (bir daha denenmesin)
     *
     * NOT: <24h kala oluşturulan randevuda toggle bypass'i BURADA degil
     *      randevu olusturma noktasinda yapilir (StoreAdminController),
     *      cunku 18:00 sonrasi pencere zaten gecmis olur.
     */
    protected function birGunOnceGrupluGonder($randevular, $wa, $controller)
    {
        $yarinTarih = date('Y-m-d', strtotime('+1 day'));
        $now = time();
        $nowMinuteOfDay = ((int) date('G', $now)) * 60 + (int) date('i', $now);

        // 15:00-18:00 = dakika cinsinden 900-1080 araligi (180dk pencere).
        // Salon ACIKKEN gonderilsin ki musteri mesaji alinca arayip guncelleme/
        // iptal yapabilsin. Tum salonlar icin ayni. (onceden 17:00-19:00 idi)
        $winStart = 15 * 60;
        $winEnd   = 18 * 60;
        $bucketSize = $winEnd - $winStart; // 180

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
            // Kanal secimi bildirim tercihlerinden (eski ayar_id=6 musteri yerine)
            $ayar = 'hatirlatma_bir_gun_once';

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

            // Sesli asistan hatirlatma aramasi: WA/SMS ile ayni (dagitilmis) dakikada,
            // musteri basina TEK arama (en erken randevu uzerinden). Hata WA/SMS'i etkilemez.
            try {
                $this->ilkHatirlatmaAramasi($grup->sortBy('saat')->first());
            } catch (\Throwable $e) {
                Log::error('[RND-ARAMA] ilk arama hatasi: ' . $e->getMessage(), ['randevu_idler' => $randevuIdler]);
            }

            if (!\App\Services\BildirimTercih::mesajAcikMi($salon->id, $ayar, 'musteri')) {
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

    /** E-asistan randevu hatirlatma aramasi (ayar_id=4) salonda acik mi (tur ici cache). */
    protected function aramaAcik($salonId): bool
    {
        if (!array_key_exists($salonId, $this->aramaAyarCache)) {
            $this->aramaAyarCache[$salonId] = (bool) SalonEAsistanAyarlari::where('salon_id', $salonId)
                ->where('ayar_id', 4)
                ->value('acik_kapali');
        }
        return $this->aramaAyarCache[$salonId];
    }

    /** Randevu aranamazsa nedeni, aranabilirse null (ayar, telefon, iptal edilmis gorev, kara liste). */
    protected function aranamamaNedeni($r)
    {
        if (!$r->salon_id || !$this->aramaAcik($r->salon_id)) return 'e-asistan randevu aramasi kapali';
        if (empty(optional($r->users)->cep_telefon)) return 'musteri telefonu yok';
        if (!empty($r->hatirlatma_gorevi_iptal)) return 'gorev iptal edilmis';
        $karaListe = MusteriPortfoy::where('user_id', $r->user_id)
            ->where('salon_id', $r->salon_id)
            ->value('kara_liste');
        return $karaListe ? 'kara listede' : null;
    }

    /**
     * 1 gun once ilk hatirlatma aramasi. Idempotency: cagiran birGunOnceGrupluGonder'in
     * hatirlatma_gunonce_gonderildi atomik claim'i. Gecis gunu korumasi: eski komut bu
     * randevuyu zaten aradiysa (arama_saat dolu) tekrar arama.
     */
    protected function ilkHatirlatmaAramasi($r)
    {
        if (!$r) return;
        if (!empty($r->arama_saat) || !empty($r->tekrar_arandi)) {
            Log::info('[RND-ARAMA] ilk arama atlandi: bu randevu zaten arandi', ['randevu_id' => $r->id, 'salon_id' => $r->salon_id]);
            return;
        }
        if (($neden = $this->aranamamaNedeni($r)) !== null) {
            // Ayar kapali salonlar her gun cok satir uretir; yalniz digerlerini logla.
            if ($neden !== 'e-asistan randevu aramasi kapali') {
                Log::info('[RND-ARAMA] ilk arama atlandi: ' . $neden, ['randevu_id' => $r->id, 'salon_id' => $r->salon_id]);
            }
            return;
        }
        $this->aramaEkle($r, 'ilk');
    }

    /**
     * Ulasilamayan aramanin tekrari. Arama Originate edilince Controller
     * tekrar_aranacak=1 + tekrar_arama_tarih_saat = simdi + salon.e_asistan_hatirlatma saat
     * yazar; musteri acarsa (ulasildi / asistan) tekrar_aranacak=0 olur.
     * Atomik claim: tekrar_arandi=1 (ikinci kez tekrar yok).
     */
    protected function tekrarAramalari($randevular, $controller)
    {
        if (!$controller->hatirlatmaSaatiIcinde(date('H:i'))) return; // 10:00–19:30 disi: pencere acilinca
        $simdi = time();
        foreach ($randevular as $r) {
            if ((int) $r->tekrar_aranacak !== 1 || !empty($r->tekrar_arandi)) continue;
            if (empty($r->tekrar_arama_tarih_saat)) continue;
            $t = strtotime($r->tekrar_arama_tarih_saat);
            if ($t === false || $t <= 0 || $t > $simdi) continue; // '0000-00-00' (ertelemede sifirlanir) dahil
            // Salon tekrar arama suresi tanimlamadiysa (0) tekrar yok — yoksa ilk aramanin hemen ardindan tekrar arardi.
            if (empty(optional($r->salonlar)->e_asistan_hatirlatma)) continue;
            // Randevuya 1 saatten az kaldiysa / gectiyse hatirlatma anlamsiz.
            if (strtotime($r->tarih . ' ' . $r->saat) - $simdi < 3600) continue;
            if (($neden = $this->aranamamaNedeni($r)) !== null) {
                if ($neden !== 'e-asistan randevu aramasi kapali') {
                    Log::info('[RND-ARAMA] tekrar arama atlandi: ' . $neden, ['randevu_id' => $r->id, 'salon_id' => $r->salon_id]);
                }
                continue;
            }

            $claimed = \Illuminate\Support\Facades\DB::table('randevular')
                ->where('id', $r->id)
                ->where('tekrar_aranacak', 1)
                ->where(function ($q) {
                    $q->whereNull('tekrar_arandi')->orWhere('tekrar_arandi', 0);
                })
                ->update(['tekrar_arandi' => 1]);
            if ($claimed !== 1) continue;

            $this->aramaEkle($r, 'tekrar');
        }
    }

    protected function aramaEkle($r, string $etiket)
    {
        // Eski IVR (yeni asistan kapali salon / kopru ayakta degil) mesaji: Turkce tarih + net saat.
        $rTs = strtotime($r->tarih . ' ' . $r->saat);
        $aylarTR = [1=>'Ocak',2=>'Şubat',3=>'Mart',4=>'Nisan',5=>'Mayıs',6=>'Haziran',7=>'Temmuz',8=>'Ağustos',9=>'Eylül',10=>'Ekim',11=>'Kasım',12=>'Aralık'];
        $tarihMetin = date('j', $rTs) . ' ' . ($aylarTR[(int) date('n', $rTs)] ?? '');
        $telaffuz = optional($r->salonlar)->santral_telaffuz_2 ?: optional($r->salonlar)->salon_adi;

        $this->aramaListesi[] = [
            'alacakIdler' => '',
            'randevuid' => $r->id,
            'kampanyaKatilimci' => '',
            'katilimci' => '',
            'mesaj' => 'Sayın ' . $r->users->name . '. ' . $telaffuz . ' için ' . $tarihMetin . ' saat ' . date('H:i', $rTs) .
                ' randevunuzu hatırlatmak isteriz. Randevuya gelecekseniz biri, randevunuzu başka bir tarihe ertelemek istiyorsanız ikiyi tuşlayınız.',
            'tel' => $r->users->cep_telefon,
            'salonId' => $r->salon_id,
            // Yeni asistan acik salonlarda exten 8 -> Stasis(randevu-santral-ai); kopru yoksa dialplan exten 1 akisina duser.
            'exten' => \App\Services\RandevuAsistan::aiHatirlatmaAcik($r->salon_id) ? 8 : 1,
            'aiHedef' => 'randevu:' . $r->id,
        ];
        $this->aramaEtiketleri[] = $etiket;
        Log::info('[RND-ARAMA] kuyruga alinacak', [
            'etiket' => $etiket, 'randevu_id' => $r->id, 'salon_id' => $r->salon_id,
            'randevu' => date('d.m.Y H:i', $rTs), 'yeni_asistan' => $this->aramaListesi[count($this->aramaListesi) - 1]['exten'] == 8,
        ]);
    }

    protected function aramalariKuyrugaGonder()
    {
        if (empty($this->aramaListesi)) return;
        foreach (array_chunk($this->aramaListesi, $this->aramaChunk) as $i => $chunk) {
            $job = new HatirlatmaAramaJob($chunk, null, null);
            if ($i > 0) $job->delay(now()->addSeconds($i * $this->aramaChunkArasi));
            dispatch($job);
        }
        $sayac = array_count_values($this->aramaEtiketleri);
        Log::info('[RND-ARAMA] ' . count($this->aramaListesi) . ' hatirlatma aramasi kuyruga eklendi'
            . ' (ilk: ' . ($sayac['ilk'] ?? 0) . ', tekrar: ' . ($sayac['tekrar'] ?? 0) . ').');
        $this->aramaListesi = [];
        $this->aramaEtiketleri = [];
    }

    /**
     * WhatsApp icin ZENGIN formatli hatirlatma mesaji uretir.
     * SMS'ten farkli (link + emoji + markdown vurgu). Konum_linki / telefon_1
     * varsa gosterilir, yoksa o satir atlanir.
     */
    protected function whatsAppRichMetin($salon, $musteri, $tarihStr, $saatlerStr, $randevuKelime, $hizmetlerStr = '')
    {
        // Klinik turu isletmeler (dermatoloji/estetik/tip merkezleri) icin ikon+kelime degistir
        $klinikMi = \App\Services\WhatsAppMesajFormat::klinikMi($salon);
        $isyeriKucuk = $klinikMi ? 'klinikte' : 'salonda';
        $isyeriBuyuk = $klinikMi ? 'Klinik' : 'Salon';
        $hizmetIkon = \App\Services\WhatsAppMesajFormat::hizmetIkon($salon);

        $mesaj  = "🌸 Merhaba, *" . $musteri->name . "*,\n\n";
        $mesaj .= ($salon->salon_adi ?? $isyeriBuyuk) . " için size yaklaşan " . $randevuKelime . " hatırlatmak isteriz. 💖\n\n";
        $mesaj .= "📅 *Tarih:* " . $tarihStr . "\n";
        $mesaj .= "🕒 *Saat:* " . $saatlerStr . "\n";
        if (!empty($hizmetlerStr)) {
            $mesaj .= $hizmetIkon . " *Hizmetler:* " . $hizmetlerStr . "\n";
        }
        $mesaj .= "\nSizi zamanında ve en iyi şekilde ağırlayabilmemiz için randevu saatinizden birkaç dakika önce " . $isyeriKucuk . " olmanızı rica ederiz.\n\n";

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
            $mesaj .= "📞 *" . $isyeriBuyuk . " Telefonu:* " . $tel . "\n";
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
