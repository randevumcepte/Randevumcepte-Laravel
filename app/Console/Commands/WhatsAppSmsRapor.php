<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDF;

/**
 * Salon-bazli WhatsApp + SMS gonderim raporu (PDF).
 *
 * Kullanim (canlida):
 *   /opt/php74/bin/php artisan wa:pdf-rapor 223 2026-01-14 2026-09-04
 *
 * Uretilen PDF: storage/app/public/raporlar/wa_sms_{salon}_{baslangic}_{bitis}.pdf
 * URL: https://.../storage/raporlar/wa_sms_223_2026-01-14_2026-09-04.pdf
 * (storage:link kurulu ise; degilse: sunucudan indir)
 */
class WhatsAppSmsRapor extends Command
{
    protected $signature = 'wa:pdf-rapor {salonId} {baslangic} {bitis?}';
    protected $description = 'Salon icin WA+SMS gonderim raporu (PDF) uretir';

    public function handle()
    {
        $salonId   = (int) $this->argument('salonId');
        $baslangic = $this->argument('baslangic');
        $bitis     = $this->argument('bitis') ?: date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $baslangic) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $bitis)) {
            $this->error('Tarih formati YYYY-MM-DD olmali. Ornek: 2026-01-14');
            return 1;
        }

        $salon = DB::table('salonlar')->where('id', $salonId)->first();
        if (!$salon) {
            $this->error("Salon bulunamadi: $salonId");
            return 1;
        }

        $bitis23 = $bitis . ' 23:59:59';

        // WHATSAPP GONDERIMLERI
        $waTumu = DB::table('whatsapp_gonderim_loglari')
            ->where('salon_id', $salonId)
            ->whereBetween('created_at', [$baslangic, $bitis23])
            ->orderBy('id', 'desc')
            ->get();

        $waOzet = [
            'toplam' => $waTumu->count(),
            'basarili' => $waTumu->where('durum', 1)->count(),
            'hata' => $waTumu->where('durum', 2)->count(),
            'kuyrukta' => $waTumu->where('durum', 0)->count(),
            'sms_fallback' => $waTumu->where('durum', 3)->count(),
        ];

        $waTipeGore = $waTumu->groupBy('gonderim_tipi')->map(function ($grup) {
            return [
                'toplam' => $grup->count(),
                'basarili' => $grup->where('durum', 1)->count(),
                'hata' => $grup->where('durum', 2)->count(),
                'fallback' => $grup->where('durum', 3)->count(),
            ];
        });

        $waGunluk = $waTumu->groupBy(function ($r) {
            return substr($r->created_at, 0, 10);
        })->map(function ($g) {
            return [
                'toplam' => $g->count(),
                'basarili' => $g->where('durum', 1)->count(),
            ];
        })->sortKeys();

        // SMS ILETIM RAPORLARI (sms_iletim_raporlari)
        $smsTumu = collect();
        if (Schema::hasTable('sms_iletim_raporlari')) {
            $smsTumu = DB::table('sms_iletim_raporlari')
                ->where('salon_id', $salonId)
                ->whereBetween('updated_at', [$baslangic, $bitis23])
                ->orderBy('id', 'desc')
                ->get();
        }

        $turAdi = [1 => 'Bildirim', 2 => 'Grup', 3 => 'Filtre', 4 => 'Toplu', 5 => 'Kampanya', 6 => 'Etkinlik'];
        $smsOzet = [
            'toplam_kayit' => $smsTumu->count(),
            'toplam_adet' => (int) $smsTumu->sum('adet'),
            'toplam_kredi' => (int) $smsTumu->sum('kredi'),
        ];
        $smsTureGore = $smsTumu->groupBy('tur')->map(function ($g) {
            return [
                'kayit' => $g->count(),
                'adet' => (int) $g->sum('adet'),
                'kredi' => (int) $g->sum('kredi'),
            ];
        });

        $html = view('rapor.wa_sms_rapor', [
            'salon'       => $salon,
            'baslangic'   => $baslangic,
            'bitis'       => $bitis,
            'waOzet'      => $waOzet,
            'waTipeGore'  => $waTipeGore,
            'waGunluk'    => $waGunluk,
            'waTumu'      => $waTumu,
            'smsOzet'     => $smsOzet,
            'smsTureGore' => $smsTureGore,
            'smsTumu'     => $smsTumu,
            'turAdi'      => $turAdi,
        ])->render();

        $dir = storage_path('app/public/raporlar');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $dosya = $dir . "/wa_sms_{$salonId}_{$baslangic}_{$bitis}.pdf";

        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif', 'isRemoteEnabled' => false]);
        $pdf->save($dosya);

        $this->info("PDF olusturuldu: $dosya");
        $this->info("URL (storage:link varsa): " . url('storage/raporlar/' . basename($dosya)));
        return 0;
    }
}
