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
    protected $signature = 'ders:hatirlat';
    protected $description = 'Grup dersi katilimcilarina WhatsApp/SMS hatirlatma (randevu hatirlatma yapisi)';

    public function handle()
    {
        $simdi = date('Y-m-d H:i');
        $bugun = date('Y-m-d');
        $ustSinir = date('Y-m-d', strtotime('+2 day')); // X saat gece yarisini asabilir

        $oturumlar = DersOturumu::where('aktif', true)
            ->where(function ($q) { $q->whereNull('iptal')->orWhere('iptal', 0); })
            ->whereNull('hatirlatma_gonderildi')
            ->whereBetween('tarih', [$bugun, $ustSinir])
            ->get();

        if ($oturumlar->isEmpty()) return;

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
            if (!$salon) continue;

            // Randevu hatirlatma toggle'i kapaliysa ders hatirlatmasi da gitmesin
            $ayar = $ayarCache[$sid];
            if ($ayar && !$ayar->musteri) continue;

            $x = (int) ($salon->randevu_sms_hatirlatma ?? 0);
            if ($x <= 0) continue; // salon hatirlatma suresi tanimlamamis

            $dersZamani = strtotime($o->tarih . ' ' . $o->saat);
            $tetik = date('Y-m-d H:i', $dersZamani - $x * 3600);
            if ($tetik !== $simdi) continue;

            // Atomik claim: bu oturumun hatirlatmasini yalnizca bir process alsin
            $claimed = DB::table('ders_oturumlari')
                ->where('id', $o->id)
                ->whereNull('hatirlatma_gonderildi')
                ->update(['hatirlatma_gonderildi' => date('Y-m-d H:i:s')]);
            if ($claimed !== 1) continue;

            $this->oturumuHatirlat($o, $salon);
        }
    }

    private function oturumuHatirlat(DersOturumu $o, Salonlar $salon)
    {
        // Kontenjani dolduran (iptal/bekleme/gelmedi olmayan) katilimcilar
        $katilimcilar = DB::table('ders_katilimcilar')
            ->join('users', 'ders_katilimcilar.user_id', '=', 'users.id')
            ->where('ders_katilimcilar.oturum_id', $o->id)
            ->whereNotIn('ders_katilimcilar.durum', ['iptal', 'bekleme', 'gelmedi'])
            ->get(['users.id', 'users.name', 'users.cep_telefon', 'users.whatsapp_onay']);

        if ($katilimcilar->isEmpty()) return;

        $tarihStr = date('d.m.Y', strtotime($o->tarih));
        $saatStr  = substr($o->saat, 0, 5);
        $dersAdi  = $o->ders_tipi ?: 'Grup Dersi';

        foreach ($katilimcilar as $m) {
            $mesaj = 'Sayın ' . $m->name . '; ' . $tarihStr . ' tarihinde saat ' . $saatStr
                . ' ' . $dersAdi . ' dersinizi hatırlatmak isteriz, görüşmek üzere ✨';
            DersBildirimServisi::musteriyeGonder($salon, $m, $mesaj, 'ders_hatirlatma');
        }

        Log::info('[DERS-HAT] oturum hatirlatildi', [
            'oturum_id' => $o->id, 'salon_id' => $salon->id, 'katilimci' => $katilimcilar->count(),
        ]);
    }
}
