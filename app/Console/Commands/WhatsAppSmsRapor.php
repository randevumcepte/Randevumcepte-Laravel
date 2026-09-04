<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDF;

/**
 * Salon-bazli WhatsApp gonderim ozet raporu (PDF, sadece toplam+kategori).
 *
 * Kullanim (canlida):
 *   /opt/php74/bin/php artisan wa:pdf-rapor 223 2026-01-14 2026-09-04
 *
 * Cikti: storage/app/public/raporlar/wa_{salon}_{baslangic}_{bitis}.pdf
 */
class WhatsAppSmsRapor extends Command
{
    protected $signature = 'wa:pdf-rapor {salonId} {baslangic} {bitis?}';
    protected $description = 'Salon icin WhatsApp gonderim ozet raporu (PDF, kategorize)';

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

        // Sadece toplam+kategori icin DB agregasyonu (detay yok — bellek dostu)
        $waOzet = [
            'toplam'       => (int) DB::table('whatsapp_gonderim_loglari')->where('salon_id', $salonId)->whereBetween('created_at', [$baslangic, $bitis23])->count(),
            'basarili'     => (int) DB::table('whatsapp_gonderim_loglari')->where('salon_id', $salonId)->whereBetween('created_at', [$baslangic, $bitis23])->where('durum', 1)->count(),
            'hata'         => (int) DB::table('whatsapp_gonderim_loglari')->where('salon_id', $salonId)->whereBetween('created_at', [$baslangic, $bitis23])->where('durum', 2)->count(),
            'kuyrukta'     => (int) DB::table('whatsapp_gonderim_loglari')->where('salon_id', $salonId)->whereBetween('created_at', [$baslangic, $bitis23])->where('durum', 0)->count(),
            'sms_fallback' => (int) DB::table('whatsapp_gonderim_loglari')->where('salon_id', $salonId)->whereBetween('created_at', [$baslangic, $bitis23])->where('durum', 3)->count(),
        ];

        $waTipeGore = DB::table('whatsapp_gonderim_loglari')
            ->where('salon_id', $salonId)
            ->whereBetween('created_at', [$baslangic, $bitis23])
            ->select('gonderim_tipi',
                DB::raw('COUNT(*) as toplam'),
                DB::raw('SUM(CASE WHEN durum=1 THEN 1 ELSE 0 END) as basarili'),
                DB::raw('SUM(CASE WHEN durum=2 THEN 1 ELSE 0 END) as hata'),
                DB::raw('SUM(CASE WHEN durum=3 THEN 1 ELSE 0 END) as fallback')
            )
            ->groupBy('gonderim_tipi')
            ->orderByDesc('toplam')
            ->get();

        $waAylik = DB::table('whatsapp_gonderim_loglari')
            ->where('salon_id', $salonId)
            ->whereBetween('created_at', [$baslangic, $bitis23])
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ay"),
                DB::raw('COUNT(*) as toplam'),
                DB::raw('SUM(CASE WHEN durum=1 THEN 1 ELSE 0 END) as basarili')
            )
            ->groupBy('ay')
            ->orderBy('ay')
            ->get();

        $html = view('rapor.wa_rapor', [
            'salon'      => $salon,
            'baslangic'  => $baslangic,
            'bitis'      => $bitis,
            'waOzet'     => $waOzet,
            'waTipeGore' => $waTipeGore,
            'waAylik'    => $waAylik,
        ])->render();

        $dir = storage_path('app/public/raporlar');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $dosya = $dir . "/wa_{$salonId}_{$baslangic}_{$bitis}.pdf";

        PDF::loadHTML($html)->setPaper('a4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif', 'isRemoteEnabled' => false])
            ->save($dosya);

        $this->info("PDF olusturuldu: $dosya");
        $this->info("URL: " . url('storage/raporlar/' . basename($dosya)));
        return 0;
    }
}
