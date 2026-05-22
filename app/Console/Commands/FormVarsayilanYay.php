<?php

namespace App\Console\Commands;

use App\FormTaslaklari;
use App\Http\Controllers\ApiController;
use App\Salonlar;
use Illuminate\Console\Command;

class FormVarsayilanYay extends Command
{
    protected $signature = 'formlar:varsayilan-yay
        {--kaynak=370 : Kaynak salon ID (default: Semall Beauty)}
        {--dry-run : Sadece raporla, kayit yapma}';

    protected $description = 'Kaynak salonun (default 370) form sablonlarini ayni salon turundeki tum salonlara yayar. Ayni form_adi varsa atlanir.';

    public function handle()
    {
        $kaynakId = (int) $this->option('kaynak');
        $dryRun   = (bool) $this->option('dry-run');

        $kaynak = Salonlar::find($kaynakId);
        if (!$kaynak) {
            $this->error("Kaynak salon bulunamadi (id={$kaynakId}).");
            return 1;
        }

        $kaynakFormlar = FormTaslaklari::where('salon_id', $kaynakId)
            ->where('is_dinamik', 1)
            ->orderBy('sira', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($kaynakFormlar->isEmpty()) {
            $this->warn("Kaynak salonda dinamik form bulunamadi.");
            return 0;
        }

        $turId = (int) $kaynak->salon_turu_id;
        $hedefler = Salonlar::where('salon_turu_id', $turId)
            ->where('id', '!=', $kaynakId)
            ->get();

        $this->info("Kaynak: {$kaynak->salon_adi} (id={$kaynakId}) -> {$kaynakFormlar->count()} form");
        $this->info("Hedef sektoru (salon_turu_id={$turId}): {$hedefler->count()} salon");
        if ($dryRun) {
            $this->warn("DRY-RUN: kayit yapilmayacak, sadece raporlanacak.");
        }
        $this->line('');

        $kaynakAdlar = $kaynakFormlar->pluck('form_adi')
            ->map(function ($a) { return mb_strtolower(trim((string)$a)); })
            ->all();

        $toplamEklenen = 0;
        $degisenSalon  = 0;

        foreach ($hedefler as $h) {
            if ($dryRun) {
                $mevcutAdlar = FormTaslaklari::where('salon_id', $h->id)
                    ->where('is_dinamik', 1)
                    ->pluck('form_adi')
                    ->map(function ($a) { return mb_strtolower(trim((string)$a)); })
                    ->all();
                $eklenecek = 0;
                foreach ($kaynakAdlar as $ad) {
                    if ($ad !== '' && !in_array($ad, $mevcutAdlar, true)) {
                        $eklenecek++;
                    }
                }
                if ($eklenecek > 0) {
                    $this->line("- [{$h->id}] {$h->salon_adi}: {$eklenecek} form eklenecek");
                    $toplamEklenen += $eklenecek;
                    $degisenSalon++;
                }
            } else {
                $n = ApiController::varsayilanFormlariKopyala($h->id, $kaynakId);
                if ($n > 0) {
                    $this->line("- [{$h->id}] {$h->salon_adi}: {$n} form eklendi");
                    $toplamEklenen += $n;
                    $degisenSalon++;
                }
            }
        }

        $this->line('');
        $this->info("Toplam {$toplamEklenen} form, {$degisenSalon} salona ".($dryRun ? 'eklenecek' : 'eklendi').".");
        return 0;
    }
}
