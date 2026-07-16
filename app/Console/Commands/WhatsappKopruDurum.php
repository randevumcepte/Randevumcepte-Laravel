<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hangi salon hangi WhatsApp köprüsünde? — tek ekranda gösterir.
 *
 * Kullanım:
 *   php artisan whatsapp:kopru            -> WhatsApp'ı olan tüm salonlar
 *   php artisan whatsapp:kopru sirius     -> adında 'sirius' geçen salonlar
 *   php artisan whatsapp:kopru --hepsi    -> hiç WhatsApp kaydı olmayanlar dahil
 *
 * Köprü tespiti:
 *   whatsapp_saglayici='cloud_api'      -> Cloud API (resmi)
 *   whatsapp_bridge_tipi='whatsmeow'    -> whatsmeow (yeni, port 3002)
 *   diğer (NULL/baileys)                -> Baileys (eski, port 3001)
 */
class WhatsappKopruDurum extends Command
{
    protected $signature = 'whatsapp:kopru {arama? : Salon adında ara (örn. sirius)} {--hepsi : WhatsApp kaydı olmayan salonları da göster}';
    protected $description = 'Her salonun hangi WhatsApp köprüsünde olduğunu (whatsmeow / Baileys / Cloud API) tablo halinde gösterir.';

    public function handle()
    {
        $arama = $this->argument('arama');
        $hepsi = (bool) $this->option('hepsi');

        $bridgeKolonVar = Schema::hasColumn('salonlar', 'whatsapp_bridge_tipi');
        $saglayiciKolonVar = Schema::hasColumn('salonlar', 'whatsapp_saglayici');

        $secim = ['id', 'salon_adi', 'whatsapp_aktif', 'whatsapp_durum', 'whatsapp_numara'];
        if ($bridgeKolonVar)    $secim[] = 'whatsapp_bridge_tipi';
        if ($saglayiciKolonVar) $secim[] = 'whatsapp_saglayici';

        $q = DB::table('salonlar')->select($secim);

        if ($arama) {
            $q->where('salon_adi', 'like', '%' . $arama . '%');
        } elseif (!$hepsi) {
            // Sadece WhatsApp'la ilgisi olan salonlar
            $q->where(function ($w) use ($bridgeKolonVar, $saglayiciKolonVar) {
                $w->where('whatsapp_aktif', 1)
                  ->orWhereNotNull('whatsapp_durum')
                  ->orWhereNotNull('whatsapp_numara');
                if ($bridgeKolonVar)    $w->orWhereNotNull('whatsapp_bridge_tipi');
                if ($saglayiciKolonVar) $w->orWhere('whatsapp_saglayici', 'cloud_api');
            });
        }

        $salonlar = $q->orderBy('salon_adi')->get();

        if ($salonlar->isEmpty()) {
            $this->warn($arama ? "'{$arama}' için WhatsApp salonu bulunamadı." : 'Gösterilecek salon yok.');
            return 0;
        }

        $sayac = ['whatsmeow' => 0, 'baileys' => 0, 'cloud_api' => 0];
        $rows = [];

        foreach ($salonlar as $s) {
            $saglayici = $saglayiciKolonVar ? ($s->whatsapp_saglayici ?? 'baileys') : 'baileys';
            $bridge    = $bridgeKolonVar ? ($s->whatsapp_bridge_tipi ?? null) : null;

            if ($saglayici === 'cloud_api') {
                $kopru = '☁️  Cloud API (RESMİ)';
                $sayac['cloud_api']++;
            } elseif ($bridge === 'whatsmeow') {
                $kopru = '🟢 whatsmeow (YENİ · 3002)';
                $sayac['whatsmeow']++;
            } else {
                $kopru = '🟡 Baileys (ESKİ · 3001)';
                $sayac['baileys']++;
            }

            $durum = $s->whatsapp_durum ?: '—';
            $durumIsaret = $durum === 'connected' ? '✅ ' : (in_array($durum, ['banned-or-loggedout', 'auto-paused-ban-risk', 'rate-limited'], true) ? '⛔ ' : '');

            $rows[] = [
                $s->id,
                mb_substr($s->salon_adi ?? '-', 0, 28),
                $kopru,
                ((int) ($s->whatsapp_aktif ?? 0) === 1 ? 'Açık' : 'Kapalı'),
                $durumIsaret . $durum,
                $s->whatsapp_numara ?: '—',
            ];
        }

        $this->table(
            ['ID', 'Salon', 'Köprü', 'Aktif', 'Durum', 'Numara'],
            $rows
        );

        $this->line('');
        $this->info(sprintf(
            'Toplam %d salon  →  ☁️  Cloud API: %d   |   🟢 whatsmeow: %d   |   🟡 Baileys: %d',
            $salonlar->count(),
            $sayac['cloud_api'],
            $sayac['whatsmeow'],
            $sayac['baileys']
        ));

        if (!$bridgeKolonVar) {
            $this->warn('Not: salonlar.whatsapp_bridge_tipi kolonu yok — hepsi Baileys sayıldı (migration koşmamış olabilir).');
        }

        return 0;
    }
}
