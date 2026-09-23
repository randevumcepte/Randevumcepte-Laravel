<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\MusteriPortfoy;
use App\User;

/**
 * Bir salonun portfoyundeki mukerrer (duble) musteri kayitlarini bulur.
 * Iki kritere gore gruplar:
 *   - Normalize telefon (0 / +90 / bosluk / parantez / tire temizlenir)
 *   - Ad-soyad (trim + kucuk harf)
 *
 * Ornek:
 *   php artisan musteri:duble-bul 368
 *   php artisan musteri:duble-bul 368 --musteri=195827   # belirli musteriyle ayni tel/isim olanlari goster
 *   php artisan musteri:duble-bul 368 --tumu             # pasif portfoyleri de dahil et
 */
class MusteriDubleBul extends Command
{
    protected $signature = 'musteri:duble-bul {salon : Salon (isletme) id} {--musteri= : Sadece bu user_id ile eslesen dubleleri goster} {--tumu : Pasif portfoyleri de dahil et}';

    protected $description = 'Bir salonun portfoyundeki mukerrer musteri kayitlarini (telefon/isim) bulur';

    public function handle()
    {
        $salon = (int) $this->argument('salon');
        $tumu  = (bool) $this->option('tumu');
        $hedefMusteri = $this->option('musteri') ? (int) $this->option('musteri') : null;

        if ($salon <= 0) {
            $this->error('Gecersiz salon id.');
            return 1;
        }

        $q = MusteriPortfoy::where('salon_id', $salon);
        if (!$tumu) {
            $q->where('aktif', true);
        }
        $userIdler = $q->pluck('user_id')->unique()->values();

        if ($userIdler->isEmpty()) {
            $this->info("Salon {$salon} portfoyunde kayit yok.");
            return 0;
        }

        $users = User::whereIn('id', $userIdler)
            ->get(['id', 'name', 'cep_telefon', 'email', 'tc_kimlik_no', 'created_at']);

        $this->info("Salon {$salon}: portfoyde " . $users->count() . " musteri inceleniyor.\n");

        // --- Telefona gore grupla ---
        $telGruplari = $users->groupBy(function ($u) {
            return $this->tel_normalize($u->cep_telefon);
        })->filter(function ($grup, $tel) {
            return $tel !== '' && $grup->count() > 1;
        });

        // --- Isime gore grupla ---
        $isimGruplari = $users->groupBy(function ($u) {
            return mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $u->name)), 'UTF-8');
        })->filter(function ($grup, $isim) {
            return $isim !== '' && $grup->count() > 1;
        });

        if ($hedefMusteri) {
            return $this->hedefRaporu($users, $hedefMusteri, $salon);
        }

        $this->baslik('TELEFONA GORE DUBLE (normalize)');
        if ($telGruplari->isEmpty()) {
            $this->line('  Yok.');
        } else {
            foreach ($telGruplari as $tel => $grup) {
                $this->warn("  Tel: {$tel}  ({$grup->count()} kayit)");
                $this->grupYaz($grup);
            }
        }

        $this->baslik('ISME GORE DUBLE');
        if ($isimGruplari->isEmpty()) {
            $this->line('  Yok.');
        } else {
            foreach ($isimGruplari as $isim => $grup) {
                $this->warn("  Isim: {$isim}  ({$grup->count()} kayit)");
                $this->grupYaz($grup);
            }
        }

        $this->line('');
        $this->info('Telefon-duble grup sayisi: ' . $telGruplari->count());
        $this->info('Isim-duble grup sayisi   : ' . $isimGruplari->count());

        return 0;
    }

    private function hedefRaporu($users, $hedefMusteri, $salon)
    {
        $hedef = $users->firstWhere('id', $hedefMusteri);
        if (!$hedef) {
            // Portfoyde olmayabilir; yine de users'tan cek
            $hedef = User::find($hedefMusteri);
            if (!$hedef) {
                $this->error("user {$hedefMusteri} bulunamadi.");
                return 1;
            }
            $this->warn("Not: user {$hedefMusteri} salon {$salon} aktif portfoyunde DEGIL.");
        }

        $hedefTel  = $this->tel_normalize($hedef->cep_telefon);
        $hedefIsim = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $hedef->name)), 'UTF-8');

        $this->baslik("HEDEF: #{$hedef->id} {$hedef->name} / {$hedef->cep_telefon} (normalize: {$hedefTel})");

        $eslesenler = $users->filter(function ($u) use ($hedefTel, $hedefIsim, $hedefMusteri) {
            if ($u->id == $hedefMusteri) return false;
            $tel = $this->tel_normalize($u->cep_telefon);
            $isim = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $u->name)), 'UTF-8');
            return ($hedefTel !== '' && $tel === $hedefTel) || ($hedefIsim !== '' && $isim === $hedefIsim);
        });

        if ($eslesenler->isEmpty()) {
            $this->info('  Ayni telefon/isimde baska kayit YOK. Duble gorunmuyor.');
        } else {
            $this->warn('  ' . $eslesenler->count() . ' olasi duble:');
            $this->grupYaz($eslesenler);
        }
        return 0;
    }

    private function grupYaz($grup)
    {
        foreach ($grup->sortBy('id') as $u) {
            $this->line(sprintf(
                "    #%-8s %-28s tel:%-16s tc:%-13s %s",
                $u->id,
                mb_substr((string) $u->name, 0, 28),
                $u->cep_telefon,
                $u->tc_kimlik_no ?: '-',
                $u->created_at
            ));
        }
    }

    private function baslik($t)
    {
        $this->line('');
        $this->line('=== ' . $t . ' ===');
    }

    private function tel_normalize($telefon)
    {
        $phone = preg_replace('/^(\+?90|0)/', '', (string) $telefon);
        $phone = str_replace(["(", ")", " ", "-"], "", $phone);
        return trim($phone);
    }
}
