<?php

namespace App\Console\Commands;

use App\AdisyonPaketSeanslar;
use App\Services\NotificationService;
use App\Services\NotificationTypes;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Yarin icin planlanmis (seans_tarih = yarin) ama henuz yapilmamis seanslari
 * bulup ilgili musteriye push hatirlatma yollar. Bildirime tiklayinca
 * uygulamada "Seanslarim" ekrani acilir (NotificationRouter session_reminder
 * -> sessions intent).
 *
 * Randevuya bagli seanslar zaten RandevuSMSHatirlatma cron'u tarafindan
 * yakalandigi icin burada randevu_id NULL olan satirlara odaklanmiyoruz —
 * tum bekleyen yarin seanslarini hatirlatiyoruz; musteri 1 kez hatirlanmak
 * istenirse cooldown daha sonra eklenebilir.
 *
 * Schedule: dailyAt('12:00') — gunde tek tetik, duplicate riski yok.
 */
class SeansHatirlatma extends Command
{
    protected $signature = 'seans:hatirlat';
    protected $description = 'Yarin yapilacak seansin musteriye push hatirlatmasi';

    public function handle()
    {
        $yarin = date('Y-m-d', strtotime('+1 day'));

        Log::info('[SEANS-HAT] cron tick', ['hedef_tarih' => $yarin]);

        // Yarin icin planlanmis, henuz gerceklesmemis seanslar.
        // adisyon_paket_id ya da adisyon_hizmet_id uzerinden adisyon'a join
        // edip salon + musteri bilgilerine ulasiyoruz.
        $seanslar = AdisyonPaketSeanslar::query()
            ->whereDate('seans_tarih', $yarin)
            ->whereNull('geldi')
            ->where(function ($q) {
                $q->whereNull('iptal')->orWhere('iptal', 0);
            })
            ->get();

        if ($seanslar->isEmpty()) {
            Log::info('[SEANS-HAT] yarin icin planli seans yok');
            return;
        }

        // Musteri+salon kombinasyonu basinda gruplayalim. Boylece ayni musteriye
        // ayni gun icin tek push gidiyor; birden cok seans varsa adet/icerik
        // mesajda toplaniyor.
        $gruplar = [];

        foreach ($seanslar as $seans) {
            $adisyon = $this->adisyonBul($seans);
            if (!$adisyon || !$adisyon->user_id || !$adisyon->salon_id) {
                continue;
            }

            $hizmetAdi = optional($seans->hizmet)->hizmet_adi ?? 'Seans';
            $saat = $seans->seans_saat ? date('H:i', strtotime($seans->seans_saat)) : null;

            $anahtar = $adisyon->user_id . '-' . $adisyon->salon_id;
            if (!isset($gruplar[$anahtar])) {
                $gruplar[$anahtar] = [
                    'user_id'  => (int) $adisyon->user_id,
                    'salon_id' => (int) $adisyon->salon_id,
                    'musteri'  => $adisyon->musteri,
                    'seanslar' => [],
                ];
            }

            $gruplar[$anahtar]['seanslar'][] = [
                'hizmet' => $hizmetAdi,
                'saat'   => $saat,
            ];
        }

        Log::info('[SEANS-HAT] hatirlatilacak musteri sayisi', [
            'adet' => count($gruplar),
        ]);

        foreach ($gruplar as $grup) {
            $this->musteriyeGonder($grup, $yarin);
        }
    }

    /**
     * Seans satirinin baglandigi adisyonu cek. Once paket, sonra hizmet
     * uzerinden tek sorgu ile.
     */
    private function adisyonBul($seans)
    {
        if ($seans->adisyon_paket_id) {
            $adisyonId = DB::table('adisyon_paketler')
                ->where('id', $seans->adisyon_paket_id)
                ->value('adisyon_id');
        } elseif ($seans->adisyon_hizmet_id) {
            $adisyonId = DB::table('adisyon_hizmetler')
                ->where('id', $seans->adisyon_hizmet_id)
                ->value('adisyon_id');
        } else {
            return null;
        }

        if (!$adisyonId) return null;

        return \App\Adisyonlar::with('musteri')->find($adisyonId);
    }

    private function musteriyeGonder(array $grup, string $yarinTarih)
    {
        $musteri = $grup['musteri'];
        if (!$musteri) {
            Log::info('[SEANS-HAT] musteri bulunamadi, atlandi', [
                'user_id' => $grup['user_id'],
            ]);
            return;
        }

        $isim = $musteri->name ?? '';
        $seansListesi = $grup['seanslar'];
        $tarihGosterim = date('d.m.Y', strtotime($yarinTarih));

        if (count($seansListesi) === 1) {
            $ilk = $seansListesi[0];
            $saatKismi = $ilk['saat'] ? "saat {$ilk['saat']}'de " : '';
            $body = "Sayin {$isim}, yarin {$saatKismi}{$ilk['hizmet']} seansiniz var.";
        } else {
            $satirlar = array_map(function ($s) {
                return $s['saat'] ? "{$s['saat']} {$s['hizmet']}" : $s['hizmet'];
            }, $seansListesi);
            $body = "Sayin {$isim}, yarin (" . $tarihGosterim . ") "
                . count($seansListesi) . " seansiniz var: "
                . implode(', ', $satirlar) . '.';
        }

        try {
            $sonuc = NotificationService::toCustomer($grup['user_id'], $grup['salon_id'])
                ->type(NotificationTypes::SESSION_REMINDER)
                ->title('Seans Hatirlatmasi')
                ->body($body)
                ->deepLink('sessions')
                ->send();

            Log::info('[SEANS-HAT] musteri push gonderildi', [
                'user_id'  => $grup['user_id'],
                'salon_id' => $grup['salon_id'],
                'seans_adet' => count($seansListesi),
                'sonuc' => $sonuc,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[SEANS-HAT] musteri push fail', [
                'user_id' => $grup['user_id'],
                'salon_id' => $grup['salon_id'],
                'err' => $e->getMessage(),
            ]);
        }
    }
}
