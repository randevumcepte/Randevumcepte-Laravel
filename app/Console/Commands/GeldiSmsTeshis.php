<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Salonlar;
use App\SalonSMSAyarlari;
use App\Randevular;
use App\AdisyonPaketSeanslar;

/**
 * "Geldi" isaretlemede MUSTERIYE giden DOGRULAMA KODU'nun neden gitmedigini teshis eder.
 *
 * Gercek akis (StoreAdminController::randevugeldiisaretle):
 *   1) Blok SADECE $seansVar>0 (randevuda paket seansi varsa) calisir. Normal hizmet
 *      randevusunda dogrulama kodu HIC gitmez.
 *   2) salon_sms_ayarlari.ayar_id=16.musteri == 1 ise (veya personel popup'i onaylarsa)
 *      randevu_dogrulama_kodu_gonder() musterinin cep_telefon'una "Dogrulama kodunuz" yollar.
 *   3) Gonderim sms_gonder_bildirimli ile yapilir; salon WA aktif+connected ise mesaj
 *      ONCE WhatsApp kuyruguna girer, SMS inline DENENMEZ.
 *   4) $randevu->dogrulama_sms_gonderildi == 1 ise TEKRAR gondermez (bir kez gonderilmis sayilir).
 *
 * Kullanim:
 *   php artisan sms:geldi-teshis 278
 *   php artisan sms:geldi-teshis 278 --randevu=123456
 *
 * WA/SMS gercek gonderimini ayrica test etmek icin:
 *   php artisan whatsapp:test 278 <musteri_telefonu> --mesaj="Dogrulama kodunuz : 1234"
 */
class GeldiSmsTeshis extends Command
{
    protected $signature = 'sms:geldi-teshis {salonId} {--randevu=}';
    protected $description = '"Geldi"de musteriye dogrulama kodu neden gitmiyor? Salon bazli teshis.';

    public function handle()
    {
        $salonId = (int) $this->argument('salonId');

        $salon = Salonlar::find($salonId);
        if (!$salon) {
            $this->error("[X] Salon bulunamadi (id={$salonId})");
            return 1;
        }
        $this->info("=== Geldi Dogrulama Kodu Teshis: {$salon->salon_adi} (id={$salonId}) ===");

        // 1) Dogrulama kodu ayari (ayar_id=16, musteri alani) — UI: "Dogrulama Kodu"
        $ayar = SalonSMSAyarlari::where('salon_id', $salonId)->where('ayar_id', 16)->first();
        $this->line('');
        $this->info('--- 1) Dogrulama kodu ayari (ayar_id=16) ---');
        if (!$ayar) {
            $this->error("[X] salon_sms_ayarlari'nda ayar_id=16 SATIRI YOK.");
            $this->line('    Bu durumda otomatik gitmez; sadece personel popup\'i onaylarsa gider.');
        } else {
            $acik = ((int) $ayar->musteri) === 1;
            $this->line('musteri (dogrulama toggle): ' . var_export($ayar->musteri, true) . ($acik ? '  [ACIK]' : '  [KAPALI]'));
            if (!$acik) {
                $this->warn('[!] Toggle KAPALI -> kod ancak personel "gonderilsin mi?" popup\'ini onaylayinca gider.');
            } else {
                $this->info('[OK] Toggle acik -> geldi isaretinde otomatik kod gonderilmeli.');
            }
        }

        // 2) Kanal: WA mi SMS mi?
        $this->line('');
        $this->info('--- 2) Gonderim kanali (sms_gonder_bildirimli pre-filter) ---');
        $waAcik = !empty($salon->whatsapp_aktif) && ($salon->whatsapp_durum ?? null) === 'connected';
        $this->line('whatsapp_aktif : ' . var_export($salon->whatsapp_aktif, true));
        $this->line('whatsapp_durum : ' . var_export($salon->whatsapp_durum, true));
        if ($waAcik) {
            $this->warn('[!] WA aktif+connected -> kod ONCE WhatsApp kuyruguna girer, inline SMS DENENMEZ.');
            $this->line('    Musteriye WA gelmediyse: numarada WhatsApp yok / session bozuk / saat kisiti / gunluk limit olabilir.');
            $this->line('    Gercek gonderimi test et: php artisan whatsapp:test ' . $salonId . ' <musteri_tel> --mesaj="Dogrulama kodunuz : 1234"');
        } else {
            $this->line('[OK] WA kapali/connected degil -> dogrudan SMS yolu kullanilir.');
            $yeniSms = (int) $salon->yeni_sms === 1;
            $this->line('yeni_sms: ' . var_export($salon->yeni_sms, true) . ($yeniSms ? ' (VoiceTelekom)' : ' (EFETech)'));
            if ($yeniSms) {
                if (empty($salon->sms_user_name) || empty($salon->sms_secret) || empty($salon->sms_baslik)) {
                    $this->error('[X] VoiceTelekom bilgileri eksik -> SMS gitmez.');
                } else { $this->info('[OK] VoiceTelekom bilgileri dolu.'); }
            } else {
                if ($salon->sms_baslik === null || $salon->sms_apikey === null) {
                    $this->error('[X] EFETech sms_baslik/sms_apikey NULL -> kod sessizce loglanir, SMS gitmez.');
                } else { $this->info('[OK] EFETech bilgileri dolu.'); }
            }
        }

        // 3) Randevu bazli: seansVar gate + dogrulama_sms_gonderildi + musteri telefonu
        $this->line('');
        $this->info('--- 3) Randevu kosullari ---');
        $rid = $this->option('randevu');
        if ($rid) {
            $randevu = Randevular::find((int) $rid);
            if (!$randevu) {
                $this->error("[X] Randevu bulunamadi (id={$rid})");
            } else {
                $seansVar = AdisyonPaketSeanslar::where('randevu_id', $randevu->id)->count();
                $this->line("randevu #{$randevu->id} seans (AdisyonPaketSeanslar): {$seansVar}");
                if ($seansVar == 0) {
                    $this->error('[X] seansVar=0 -> dogrulama kodu BLOGU HIC CALISMAZ. Bu randevuda kod gitmez.');
                    $this->line('    Dogrulama kodu sadece PAKET SEANSI olan randevularda tetiklenir.');
                } else {
                    $this->info('[OK] seansVar>0 -> dogrulama blogu calisir.');
                }
                $this->line('dogrulama_sms_gonderildi: ' . var_export($randevu->dogrulama_sms_gonderildi, true)
                    . (((int)$randevu->dogrulama_sms_gonderildi === 1) ? '  [!] 1 -> TEKRAR GONDERMEZ' : ''));
                $tel = optional($randevu->users)->cep_telefon;
                $this->line('musteri cep_telefon: ' . var_export($tel, true) . (empty($tel) ? '  [X] BOS -> gonderilemez' : ''));
            }
        } else {
            $this->line('(Belirli bir randevu icin: --randevu=ID ekle.)');
            $this->warn('[!] HATIRLATMA: Dogrulama blogu SADECE $seansVar>0 (paket seansli randevu) ise calisir.');
            $this->warn('    Test ettigin randevu paket seansi icermiyorsa hicbir kod (ne WA ne SMS) gitmez.');
        }

        $this->line('');
        $this->info('=== Ozet ===');
        $this->line('Sirayla: (1) randevuda paket seansi var mi -> (2) ayar_id=16 acik mi / popup -> (3) WA connected ise WA\'dan gider -> (4) numarada WhatsApp var mi.');
        return 0;
    }
}
