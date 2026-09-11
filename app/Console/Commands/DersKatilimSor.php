<?php

namespace App\Console\Commands;

use App\DersKatilimci;
use App\DersOturumu;
use App\Salonlar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Ders bitiminde salon "Geldi" isaretlemediyse, hala "rezerve" olan katilimcilara
 * "Derse katildiniz mi?" push gonderir. Musteri uygulamadan Katildim/Katilmadim ile
 * kendi bildirir (seans dusumu kendi tetiklenir).
 *
 * Trigger: ders bitis saatinden itibaren 6 saat icinde, katilimci basina TEK sefer
 * (katilim_soruldu bayragi). Sadece hizmete bagli (dusum yapilabilir) dersler.
 * Schedule: everyMinute + withoutOverlapping.
 */
class DersKatilimSor extends Command
{
    protected $signature = 'ders:katilim-sor';
    protected $description = 'Ders bitiminde rezerve katilimcilara "katildiniz mi?" push (self-bildirim)';

    public function handle()
    {
        $now = time();
        $bugun = date('Y-m-d');
        $alt = date('Y-m-d', strtotime('-1 day'));

        $oturumlar = DersOturumu::where('aktif', true)
            ->where(function ($q) { $q->whereNull('iptal')->orWhere('iptal', 0); })
            ->whereNotNull('hizmet_id')
            ->whereBetween('tarih', [$alt, $bugun])
            ->get();

        foreach ($oturumlar as $o) {
            $bitisTs = strtotime($o->tarih . ' ' . $o->saat_bitis);
            if ($now < $bitisTs) continue;              // ders daha bitmedi
            if ($now > $bitisTs + 6 * 3600) continue;    // 6 saatten eski, sorma

            $kats = DersKatilimci::where('oturum_id', $o->id)->where('durum', 'rezerve')
                ->where(function ($q) { $q->whereNull('katilim_soruldu')->orWhere('katilim_soruldu', 0); })
                ->get();
            if ($kats->isEmpty()) continue;

            $salon = Salonlar::find($o->salon_id);
            if (!$salon) continue;
            $dersAdi = $o->ders_tipi ?: 'Grup Dersi';
            $saatStr = substr($o->saat, 0, 5);

            foreach ($kats as $k) {
                // Atomik claim: yalnizca bir kez sor
                $claimed = DB::table('ders_katilimcilar')->where('id', $k->id)
                    ->where(function ($q) { $q->whereNull('katilim_soruldu')->orWhere('katilim_soruldu', 0); })
                    ->update(['katilim_soruldu' => 1]);
                if ($claimed !== 1) continue;

                try {
                    \App\Services\NotificationService::toCustomer((int) $k->user_id, (int) $o->salon_id)
                        ->type(\App\Services\NotificationTypes::SESSION_REMINDER)
                        ->title('Derse katıldınız mı?')
                        ->body('Bugün ' . $saatStr . ' ' . $dersAdi . ' dersinize katıldıysanız lütfen onaylayın: Katıldım / Katılmadım.')
                        ->deepLink('group_classes', ['salon_id' => (int) $o->salon_id])
                        ->send();
                } catch (\Throwable $e) {
                    Log::warning('[DERS-KATILIM-SOR] push fail', ['katilimci' => $k->id, 'err' => $e->getMessage()]);
                }
            }
            Log::info('[DERS-KATILIM-SOR] soruldu', ['oturum' => $o->id, 'adet' => $kats->count()]);
        }
    }
}
