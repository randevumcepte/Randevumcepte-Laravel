<?php

namespace App\Console\Commands;

use App\Http\Controllers\ApiController;
use App\Salonlar;
use App\SalonHizmetler;
use Illuminate\Console\Command;

class HizmetVarsayilanYay extends Command
{
    protected $signature = 'hizmetler:varsayilan-yay
        {--kaynak= : Kaynak/sablon salon ID (bos ise config varsayilanlar.guzellik_sablon_salon_id)}
        {--dry-run : Sadece raporla, kayit yapma}';

    protected $description = 'Sablon salonun sunulan hizmetlerini AYNI salon turundeki tum salonlara yayar. Hedefte zaten olan hizmet_id atlanir (idempotent). FormVarsayilanYay ile ayni desen.';

    public function handle()
    {
        $kaynakId = (int) ($this->option('kaynak') ?: config('varsayilanlar.guzellik_sablon_salon_id', 0));
        $dryRun   = (bool) $this->option('dry-run');

        if (!$kaynakId) {
            $this->error('Kaynak salon ID yok (--kaynak verin veya config/varsayilanlar.php icindeki guzellik_sablon_salon_id ayarlayin).');
            return 1;
        }

        $kaynak = Salonlar::find($kaynakId);
        if (!$kaynak) {
            $this->error("Kaynak salon bulunamadi (id={$kaynakId}).");
            return 1;
        }

        $turId = (int) $kaynak->salon_turu_id;
        $hedefler = Salonlar::where('salon_turu_id', $turId)
            ->where('id', '!=', $kaynakId)
            ->get();

        $kaynakHizmetSayisi = SalonHizmetler::where('salon_id', $kaynakId)->count();

        $this->info("Kaynak: {$kaynak->salon_adi} (id={$kaynakId}) -> {$kaynakHizmetSayisi} sunulan hizmet");
        $this->info("Hedef turu (salon_turu_id={$turId}): {$hedefler->count()} salon");
        if ($dryRun) {
            $this->warn('DRY-RUN: kayit yapilmayacak, sadece raporlanacak.');
        }
        $this->line('');

        $kaynakIdler = SalonHizmetler::where('salon_id', $kaynakId)
            ->pluck('hizmet_id')
            ->map(function ($v) { return (int)$v; })
            ->all();

        $toplamEklenen = 0;
        $degisenSalon  = 0;

        foreach ($hedefler as $h) {
            if ($dryRun) {
                $mevcut = SalonHizmetler::where('salon_id', $h->id)
                    ->pluck('hizmet_id')
                    ->map(function ($v) { return (int)$v; })
                    ->all();
                $eklenecek = count(array_diff($kaynakIdler, $mevcut));
                if ($eklenecek > 0) {
                    $this->line("- [{$h->id}] {$h->salon_adi}: {$eklenecek} hizmet eklenecek");
                    $toplamEklenen += $eklenecek;
                    $degisenSalon++;
                }
            } else {
                $n = ApiController::varsayilanHizmetleriKopyala($h->id, $kaynakId);
                if ($n > 0) {
                    $this->line("- [{$h->id}] {$h->salon_adi}: {$n} hizmet eklendi");
                    $toplamEklenen += $n;
                    $degisenSalon++;
                }
            }
        }

        $this->line('');
        $this->info("Toplam {$toplamEklenen} hizmet, {$degisenSalon} salona ".($dryRun ? 'eklenecek' : 'eklendi').".");
        return 0;
    }
}
