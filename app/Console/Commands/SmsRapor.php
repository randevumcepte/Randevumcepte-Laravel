<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDF;

/**
 * Salon-bazli SMS gonderim ozet raporu (PDF, sadece toplam+kategori).
 *
 * Kullanim (canlida):
 *   /opt/php74/bin/php artisan sms:pdf-rapor 223 2026-01-14 2026-09-04
 *
 * Cikti: storage/app/public/raporlar/sms_{salon}_{baslangic}_{bitis}.pdf
 */
class SmsRapor extends Command
{
    protected $signature = 'sms:pdf-rapor {salonId} {baslangic} {bitis?}';
    protected $description = 'Salon icin SMS gonderim ozet raporu (PDF, kategorize)';

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
        $turAdi = [1 => 'Bildirim', 2 => 'Grup', 3 => 'Filtre', 4 => 'Toplu', 5 => 'Kampanya', 6 => 'Etkinlik'];

        $smsOzet = DB::table('sms_iletim_raporlari')
            ->where('salon_id', $salonId)
            ->whereBetween('updated_at', [$baslangic, $bitis23])
            ->select(
                DB::raw('COUNT(*) as kayit'),
                DB::raw('SUM(adet) as adet'),
                DB::raw('SUM(kredi) as kredi')
            )
            ->first();

        $smsTureGore = DB::table('sms_iletim_raporlari')
            ->where('salon_id', $salonId)
            ->whereBetween('updated_at', [$baslangic, $bitis23])
            ->select('tur',
                DB::raw('COUNT(*) as kayit'),
                DB::raw('SUM(adet) as adet'),
                DB::raw('SUM(kredi) as kredi')
            )
            ->groupBy('tur')
            ->orderByDesc('adet')
            ->get();

        $smsAylik = DB::table('sms_iletim_raporlari')
            ->where('salon_id', $salonId)
            ->whereBetween('updated_at', [$baslangic, $bitis23])
            ->select(DB::raw("DATE_FORMAT(updated_at, '%Y-%m') as ay"),
                DB::raw('COUNT(*) as kayit'),
                DB::raw('SUM(adet) as adet'),
                DB::raw('SUM(kredi) as kredi')
            )
            ->groupBy('ay')
            ->orderBy('ay')
            ->get();

        $html = view('rapor.sms_rapor', [
            'salon'        => $salon,
            'baslangic'    => $baslangic,
            'bitis'        => $bitis,
            'smsOzet'      => $smsOzet,
            'smsTureGore'  => $smsTureGore,
            'smsAylik'     => $smsAylik,
            'turAdi'       => $turAdi,
        ])->render();

        $dir = storage_path('app/public/raporlar');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $dosya = $dir . "/sms_{$salonId}_{$baslangic}_{$bitis}.pdf";

        PDF::loadHTML($html)->setPaper('a4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif', 'isRemoteEnabled' => false])
            ->save($dosya);

        $this->info("PDF olusturuldu: $dosya");
        $this->info("URL: " . url('storage/raporlar/' . basename($dosya)));
        return 0;
    }
}
