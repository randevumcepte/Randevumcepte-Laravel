<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Salonlar;
use App\SalonSMSAyarlari;
use App\Personeller;
use App\IsletmeYetkilileri;
use Illuminate\Support\Facades\DB;

/**
 * "Geldi" isaretlemede personele giden SMS akisinin neden gitmedigini teshis eder.
 *
 * Gercek akis: StoreAdminController::randevugeldiisaretle -> sadece
 *   salon_sms_ayarlari.ayar_id=21.personel == 1 ise personelin yetkilisinin
 *   gsm1'ine SMS gider (sms_gonder_bildirimli). WA acik+connected ise once
 *   WhatsApp'a kuyruga girer, SMS GITMEZ (beklenen davranis).
 *
 * Kullanim:
 *   php artisan sms:geldi-teshis 278
 */
class GeldiSmsTeshis extends Command
{
    protected $signature = 'sms:geldi-teshis {salonId}';
    protected $description = '"Geldi" isaretlemede SMS neden gitmiyor? Salon bazli teshis.';

    public function handle()
    {
        $salonId = (int) $this->argument('salonId');

        $salon = Salonlar::find($salonId);
        if (!$salon) {
            $this->error("[X] Salon bulunamadi (id={$salonId})");
            return 1;
        }
        $this->info("=== Geldi SMS Teshis: {$salon->salon_adi} (id={$salonId}) ===");

        // 1) Geldi ayari (ayar_id=21, personel alani) — UI'da "Musteri Geldi Bildirimi"
        $ayar = SalonSMSAyarlari::where('salon_id', $salonId)->where('ayar_id', 21)->first();
        $this->line('');
        $this->info('--- 1) Geldi bildirimi ayari (ayar_id=21) ---');
        if (!$ayar) {
            $this->error("[X] salon_sms_ayarlari'nda ayar_id=21 SATIRI YOK -> SMS hic gitmez.");
            $this->line('    Cozum: bu salon icin ayar_id=21 satiri olusturulmali (personel=1).');
        } else {
            $acik = ((int) $ayar->personel) === 1;
            $this->line('personel (geldi toggle): ' . var_export($ayar->personel, true) . ($acik ? '  [ACIK]' : '  [KAPALI]'));
            if (!$acik) {
                $this->error('[X] Toggle KAPALI -> SMS gitmez. Panel > SMS Ayarlari > "Musteri Geldi Bildirimi" acilmali.');
            } else {
                $this->info('[OK] Toggle acik.');
            }
        }

        // 2) Kanal: once WhatsApp mi?
        $this->line('');
        $this->info('--- 2) Gonderim kanali ---');
        $waAcik = !empty($salon->whatsapp_aktif) && ($salon->whatsapp_durum ?? null) === 'connected';
        $this->line('whatsapp_aktif : ' . var_export($salon->whatsapp_aktif, true));
        $this->line('whatsapp_durum : ' . var_export($salon->whatsapp_durum, true));
        if ($waAcik) {
            $this->warn('[!] WhatsApp acik+connected -> mesaj ONCE WhatsApp\'a gider, BASARILIYSA SMS GITMEZ.');
            $this->line('    "SMS gelmiyor" sikayeti aslinda mesajin WhatsApp\'tan gitmesi olabilir (beklenen).');
        } else {
            $this->line('[OK] WA kapali/baglanti yok -> dogrudan SMS yolu kullanilir.');
        }

        // 3) SMS saglayici yapilandirmasi
        $this->line('');
        $this->info('--- 3) SMS saglayici yapilandirmasi ---');
        $yeniSms = (int) $salon->yeni_sms === 1;
        $this->line('yeni_sms       : ' . var_export($salon->yeni_sms, true) . ($yeniSms ? '  (VoiceTelekom)' : '  (EFETech)'));
        $this->line('sms_baslik     : ' . var_export($salon->sms_baslik, true));
        if ($yeniSms) {
            $this->line('sms_user_name  : ' . var_export($salon->sms_user_name, true));
            $this->line('sms_secret     : ' . ($salon->sms_secret !== null ? '[VAR]' : '[YOK]'));
            if (empty($salon->sms_user_name) || empty($salon->sms_secret) || empty($salon->sms_baslik)) {
                $this->error('[X] VoiceTelekom bilgileri eksik -> SMS gitmez.');
            } else {
                $this->info('[OK] VoiceTelekom bilgileri dolu.');
            }
        } else {
            $this->line('sms_apikey     : ' . ($salon->sms_apikey !== null ? '[VAR]' : '[YOK]'));
            if ($salon->sms_baslik === null || $salon->sms_apikey === null) {
                $this->error('[X] EFETech sms_baslik/sms_apikey NULL -> kod sessizce loglar, SMS gitmez.');
            } else {
                $this->info('[OK] EFETech bilgileri dolu.');
            }
        }

        // 4) Alici zinciri: personel -> yetkili -> gsm1
        $this->line('');
        $this->info('--- 4) Alici zinciri (personel -> yetkili -> gsm1) ---');
        $personeller = Personeller::where('salon_id', $salonId)->get();
        if ($personeller->isEmpty()) {
            $this->warn('[!] Bu salonda personel kaydi bulunamadi (salon_id eslesmesi?).');
        }
        $bosGsm = 0;
        foreach ($personeller as $p) {
            $yetkiliId = $p->yetkili_id;
            $gsm = $yetkiliId ? IsletmeYetkilileri::where('id', $yetkiliId)->value('gsm1') : null;
            $durum = (!$yetkiliId) ? 'YETKILI_ID YOK' : (empty($gsm) ? 'GSM1 BOS' : $gsm);
            if (!$yetkiliId || empty($gsm)) { $bosGsm++; }
            $ad = $p->ad ?? $p->personel_adi ?? $p->name ?? ('#' . $p->id);
            $this->line(sprintf('  personel #%-5s %-25s yetkili_id=%-6s gsm1=%s',
                $p->id, mb_substr((string)$ad, 0, 24), var_export($yetkiliId, true), $durum));
        }
        if ($bosGsm > 0) {
            $this->error("[X] {$bosGsm} personelin yetkili/gsm1 bilgisi eksik -> bu personellerin randevularinda 'to' bos olur, SMS sessizce gitmez.");
        }

        $this->line('');
        $this->info('=== Ozet ===');
        $this->line('Yukarida [X] gorunen ilk madde SMS\'in gitmeme sebebidir.');
        return 0;
    }
}
