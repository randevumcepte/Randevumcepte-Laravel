<?php

namespace App\Console\Commands;

use App\DersOturumu;
use App\Salonlar;
use App\SalonSMSAyarlari;
use App\Services\DersBildirimServisi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Grup dersi (Pilates/kurs) hatirlatmasi. Randevu hatirlatmasi ile AYNI yapi:
 *  - "Kac saat once" ayari salon bazli: salonlar.randevu_sms_hatirlatma (randevu
 *    ile ayni kolon/deger).
 *  - Toggle: salon_sms_ayarlari ayar_id=1 (randevu hatirlatma) musteri bayragi.
 *  - Kanal: WhatsApp ONCELIKLI, SMS yedek (DersBildirimServisi).
 *
 * Her dakika tetik anini (ders_zamani - X saat) dakika hassasiyetinde karsilastirir.
 * Idempotency: ders_oturumlari.hatirlatma_gonderildi ile atomik claim (oturum basina
 * tek gonderim; tum aktif katilimcilara).
 *
 * Schedule: everyMinute + withoutOverlapping (RandevuSMSHatirlatma ile ayni).
 */
class DersHatirlatma extends Command
{
    protected $signature = 'ders:hatirlat
        {--oturum= : Sadece bu oturum ID icin calis (test)}
        {--force : Tetik saatini/gonderildi bayragini yoksay, hemen gonder (test)}
        {--dry : Gondermeden ne olacagini raporla (test)}';
    protected $description = 'Grup dersi katilimcilarina WhatsApp/SMS hatirlatma (randevu hatirlatma yapisi)';

    public function handle()
    {
        $simdi = date('Y-m-d H:i');
        $bugun = date('Y-m-d');
        $ustSinir = date('Y-m-d', strtotime('+2 day')); // X saat gece yarisini asabilir

        $oturumId = $this->option('oturum');
        $force    = (bool) $this->option('force');
        $dry      = (bool) $this->option('dry');
        $test     = $oturumId || $force || $dry;

        $q = DersOturumu::where('aktif', true)
            ->where(function ($q) { $q->whereNull('iptal')->orWhere('iptal', 0); });
        if ($oturumId) {
            $q->where('id', (int) $oturumId);
        } else {
            $q->whereNull('hatirlatma_gonderildi')
              ->whereBetween('tarih', [$bugun, $ustSinir]);
        }
        $oturumlar = $q->get();

        if ($oturumlar->isEmpty()) {
            if ($test) $this->line('Uygun oturum bulunamadi.');
            return;
        }

        // Salon + ayar cache (per-salon tek sorgu)
        $salonCache = [];
        $ayarCache  = [];

        foreach ($oturumlar as $o) {
            $sid = (int) $o->salon_id;
            if (!array_key_exists($sid, $salonCache)) {
                $salonCache[$sid] = Salonlar::find($sid);
                $ayarCache[$sid]  = SalonSMSAyarlari::where('salon_id', $sid)->where('ayar_id', 1)->first();
            }
            $salon = $salonCache[$sid];
            if (!$salon) { if ($test) $this->warn("Oturum {$o->id}: salon yok"); continue; }

            // Randevu hatirlatma toggle'i kapaliysa ders hatirlatmasi da gitmesin
            $ayar = $ayarCache[$sid];
            $toggleAcik = !($ayar && !$ayar->musteri);
            $x = (int) ($salon->randevu_sms_hatirlatma ?? 0);
            $dersZamani = strtotime($o->tarih . ' ' . $o->saat);
            $tetik = date('Y-m-d H:i', $dersZamani - $x * 3600);

            // GOZLEMLENEBILIRLIK: tetige +-5 dk yakin oturumlari her cron tur'unda
            // logla (cron elle komut calistirmadan neden gitti/gitmedi gorunsun).
            $yakin = abs(($dersZamani - $x * 3600) - strtotime($simdi)) <= 300;
            if ($yakin && !$test) {
                Log::info('[DERS-HAT] aday', [
                    'oturum' => $o->id, 'salon' => $sid, 'ders' => $o->ders_tipi,
                    'tetik' => $tetik, 'simdi' => $simdi, 'toggle' => $toggleAcik ? 1 : 0,
                    'sure_saat' => $x, 'gonderildi' => $o->hatirlatma_gonderildi,
                ]);
            }

            if ($test) {
                $this->line("Oturum #{$o->id} [{$salon->salon_adi}] {$o->tarih} {$o->saat} '{$o->ders_tipi}'");
                $this->line("  toggle(ayar_id=1): " . ($toggleAcik ? 'ACIK' : 'KAPALI')
                    . " | hatirlatma_suresi(randevu_sms_hatirlatma): {$x} saat"
                    . " | tetik: {$tetik} | simdi: {$simdi}");
            }

            if (!$toggleAcik) {
                if ($yakin && !$test) Log::info('[DERS-HAT] ATLANDI: toggle(ayar_id=1) KAPALI', ['oturum' => $o->id]);
                if ($test) $this->warn('  -> ATLANDI: randevu hatirlatma toggle KAPALI'); continue;
            }
            if ($x <= 0) {
                if ($yakin && !$test) Log::info('[DERS-HAT] ATLANDI: hatirlatma suresi 0', ['oturum' => $o->id]);
                if ($test) $this->warn('  -> ATLANDI: salon hatirlatma suresi tanimsiz (0)'); continue;
            }

            // Tetik PENCERESI (--force ile yoksayilir): tetik aninDAN ders baslangicina
            // KADAR herhangi bir cron turunda gonder. Exact-dakika esitligi (tetik==simdi)
            // KIRILGANDI: cron o dakikayi kacirirsa (sunucu yuku / withoutOverlapping
            // gecikmesi — log'da 13:45 turu hic olmamis, 13:44'ten 13:46'ya atlamis)
            // hatirlatma sonsuza dek kacar. Pencere + atomik claim (hatirlatma_gonderildi)
            // = kacan dakikayi sonraki tur yakalar, yine de TEK gonderim.
            $simdiTs = strtotime($simdi);
            $tetikTs = $dersZamani - $x * 3600;
            if (!$force && !($simdiTs >= $tetikTs && $simdiTs < $dersZamani)) {
                if ($yakin && !$test) Log::info('[DERS-HAT] ATLANDI: tetik penceresi disinda', ['oturum' => $o->id, 'tetik' => $tetik, 'simdi' => $simdi]);
                if ($test) $this->warn('  -> ATLANDI: tetik penceresi disinda (--force ile zorlanabilir)');
                continue;
            }

            if ($dry) { $this->info('  -> GONDERILECEKTI (dry, gonderilmedi)'); $this->oturumuHatirlat($o, $salon, true); continue; }

            if ($force) {
                // Test: bayragi yoksay, dogrudan gonder (tekrar test edilebilsin)
                DB::table('ders_oturumlari')->where('id', $o->id)->update(['hatirlatma_gonderildi' => date('Y-m-d H:i:s')]);
                $this->info('  -> GONDERILIYOR (force)');
                $this->oturumuHatirlat($o, $salon);
                continue;
            }

            // Normal akis: atomik claim (yalnizca bir process alsin)
            $claimed = DB::table('ders_oturumlari')
                ->where('id', $o->id)
                ->whereNull('hatirlatma_gonderildi')
                ->update(['hatirlatma_gonderildi' => date('Y-m-d H:i:s')]);
            if ($claimed !== 1) {
                Log::info('[DERS-HAT] ATLANDI: baska process claim etti / zaten gonderildi', ['oturum' => $o->id]);
                continue;
            }

            Log::info('[DERS-HAT] TETIK ESLESTI, gonderiliyor', ['oturum' => $o->id, 'tetik' => $tetik]);
            $this->oturumuHatirlat($o, $salon);
        }
    }

    private function oturumuHatirlat(DersOturumu $o, Salonlar $salon, bool $dry = false)
    {
        // Kontenjani dolduran (iptal/bekleme/gelmedi olmayan) katilimcilar
        $katilimcilar = DB::table('ders_katilimcilar')
            ->join('users', 'ders_katilimcilar.user_id', '=', 'users.id')
            ->where('ders_katilimcilar.oturum_id', $o->id)
            ->whereNotIn('ders_katilimcilar.durum', ['iptal', 'bekleme', 'gelmedi'])
            ->get(['users.id', 'users.name', 'users.cep_telefon', 'users.whatsapp_onay']);

        if ($katilimcilar->isEmpty()) {
            if ($dry) $this->warn('     (gonderilecek katilimci yok — rezerve/geldi durumunda kimse yok)');
            return;
        }

        $tarihStr = date('d.m.Y', strtotime($o->tarih));
        $saatStr  = substr($o->saat, 0, 5);
        $dersAdi  = $o->ders_tipi ?: 'Grup Dersi';

        foreach ($katilimcilar as $m) {
            if ($dry) {
                $this->line('     - ' . $m->name . ' (' . ($m->cep_telefon ?: 'TEL YOK') . ') wa_onay=' . (int)($m->whatsapp_onay ?? 1));
                continue;
            }
            $mesaj = 'Sayın ' . $m->name . '; ' . $tarihStr . ' tarihinde saat ' . $saatStr
                . ' ' . $dersAdi . ' dersinizi hatırlatmak isteriz, görüşmek üzere ✨';
            DersBildirimServisi::musteriyeGonder($salon, $m, $mesaj, 'ders_hatirlatma');
        }
        if ($dry) return;

        Log::info('[DERS-HAT] oturum hatirlatildi', [
            'oturum_id' => $o->id, 'salon_id' => $salon->id, 'katilimci' => $katilimcilar->count(),
        ]);
    }
}
