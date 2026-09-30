<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * PERSONEL <-> YETKİLİ TEŞHİS (READ-ONLY, hicbir sey degistirmez)
 *
 * "203 bug'i": personelekleduzenle guncellemede yetkili'yi TELEFON (gsm1) ile
 * buluyordu. Telefon bos gelince where('gsm1','') bos-gsm'li rastgele kaydi
 * (or. id 203) yakalayip adini/unvanini/rolunu eziyor ve personel.yetkili_id=203
 * yapiyordu. Birden cok bos-telefonlu personel ayni yetkiliye "cokuyor".
 *
 * Bu komut hasari OLCER (yazmaz):
 *   - Birden fazla personel tarafindan paylasilan yetkili_id'ler (asil kanit)
 *   - Personel adi ile yetkili adi uyusmayan kayitlar (ezme izi)
 *   - yetkili_id NULL / gecersiz personeller
 *   - Sahipsiz (hicbir personelin isaret etmedigi) yetkililer
 *   - Etkilenen yetkililerin model_has_roles satirlari
 *
 * Kullanim:
 *   /opt/php74/bin/php artisan personel:yetkili-teshis            (tum sistem)
 *   /opt/php74/bin/php artisan personel:yetkili-teshis 432        (sadece salon 432)
 */
class PersonelYetkiliTeshis extends Command
{
    protected $signature = 'personel:yetkili-teshis {salonId? : salon_id ile sinirla (opsiyonel)}';
    protected $description = 'Personel<->yetkili<->rol veri bozulmasini (203 bug) OLCER, hicbir sey degistirmez';

    public function handle()
    {
        $salonId = $this->argument('salonId') ? (int) $this->argument('salonId') : null;
        $this->info('=== PERSONEL <-> YETKİLİ TEŞHİS (READ-ONLY) ===');
        if ($salonId) $this->line("Kapsam: salon_id = {$salonId}");
        else $this->line('Kapsam: TUM sistem');
        $this->line('');

        // ---- 1) Birden fazla personel tarafindan paylasilan yetkili_id'ler ----
        $this->info('1) BİRDEN FAZLA PERSONELİN PAYLAŞTIĞI yetkili_id (asil kanit)');
        $paylasan = DB::table('salon_personelleri')
            ->select('yetkili_id', DB::raw('COUNT(*) as adet'))
            ->whereNotNull('yetkili_id')
            ->where('yetkili_id', '>', 0)
            ->when($salonId, function ($q) use ($salonId) { $q->where('salon_id', $salonId); })
            ->groupBy('yetkili_id')
            ->having('adet', '>', 1)
            ->orderByDesc('adet')
            ->get();

        if ($paylasan->isEmpty()) {
            $this->line('   Temiz: paylasilan yetkili_id yok.');
        } else {
            $this->error('   ' . $paylasan->count() . ' adet paylasilan yetkili_id bulundu:');
            foreach ($paylasan as $p) {
                $yetkili = DB::table('isletmeyetkilileri')->where('id', $p->yetkili_id)->first();
                $yad = $yetkili ? ($yetkili->name ?: '(bos)') : '!! YETKİLİ YOK';
                $ygsm = $yetkili ? ($yetkili->gsm1 ?: '(bos)') : '-';
                $this->line("   ── yetkili_id={$p->yetkili_id}  adet={$p->adet}  yetkili.name='{$yad}'  gsm1='{$ygsm}'");
                $pers = DB::table('salon_personelleri')
                    ->where('yetkili_id', $p->yetkili_id)
                    ->when($salonId, function ($q) use ($salonId) { $q->where('salon_id', $salonId); })
                    ->orderBy('salon_id')->orderBy('id')
                    ->get(['id', 'personel_adi', 'cep_telefon', 'salon_id', 'role_id', 'aktif', 'arsivli']);
                foreach ($pers as $pp) {
                    $tel = $pp->cep_telefon ?: '(BOŞ)';
                    $this->line("        personel #{$pp->id}  '{$pp->personel_adi}'  tel={$tel}  salon={$pp->salon_id}  role_id={$pp->role_id}  aktif={$pp->aktif}  arsivli={$pp->arsivli}");
                }
            }
        }
        $this->line('');

        // ---- 2) Personel adi != yetkili adi (ezme izi) ----
        $this->info('2) PERSONEL ADI ile YETKİLİ ADI UYUŞMAYAN kayitlar (ezme izi)');
        $mismatch = DB::table('salon_personelleri as sp')
            ->join('isletmeyetkilileri as y', 'sp.yetkili_id', '=', 'y.id')
            ->whereNotNull('sp.yetkili_id')
            ->when($salonId, function ($q) use ($salonId) { $q->where('sp.salon_id', $salonId); })
            ->whereRaw('TRIM(COALESCE(sp.personel_adi,"")) <> TRIM(COALESCE(y.name,""))')
            ->orderBy('sp.salon_id')->orderBy('sp.id')
            ->limit(200)
            ->get(['sp.id', 'sp.personel_adi', 'sp.salon_id', 'sp.yetkili_id', 'y.name as yetkili_adi', 'y.gsm1']);
        if ($mismatch->isEmpty()) {
            $this->line('   Temiz: ad uyusmazligi yok.');
        } else {
            $this->error('   ' . $mismatch->count() . ' kayit (max 200 gosterilir):');
            foreach ($mismatch as $m) {
                $this->line("   personel #{$m->id} salon={$m->salon_id}  personel_adi='{$m->personel_adi}'  <>  yetkili#{$m->yetkili_id}.name='{$m->yetkili_adi}' (gsm1='{$m->gsm1}')");
            }
        }
        $this->line('');

        // ---- 3) yetkili_id NULL / gecersiz personeller ----
        $this->info('3) yetkili_id NULL veya GEÇERSİZ (karsiligi olmayan) personeller');
        $nullYetkili = DB::table('salon_personelleri')
            ->when($salonId, function ($q) use ($salonId) { $q->where('salon_id', $salonId); })
            ->where(function ($q) {
                $q->whereNull('yetkili_id')->orWhere('yetkili_id', 0)
                  ->orWhereNotIn('yetkili_id', function ($sub) {
                      $sub->from('isletmeyetkilileri')->select('id');
                  });
            })
            ->orderBy('salon_id')->orderBy('id')
            ->limit(200)
            ->get(['id', 'personel_adi', 'cep_telefon', 'salon_id', 'yetkili_id']);
        if ($nullYetkili->isEmpty()) {
            $this->line('   Temiz.');
        } else {
            $this->error('   ' . $nullYetkili->count() . ' kayit (max 200):');
            foreach ($nullYetkili as $n) {
                $this->line("   personel #{$n->id} salon={$n->salon_id}  '{$n->personel_adi}'  tel=" . ($n->cep_telefon ?: '(BOŞ)') . "  yetkili_id=" . ($n->yetkili_id ?? 'NULL'));
            }
        }
        $this->line('');

        // ---- 4) Sahipsiz yetkililer (bos gsm1 olanlar one cikar) ----
        $this->info('4) SAHİPSİZ isletmeyetkilileri (hicbir personel isaret etmiyor) — bos gsm1 olanlar riskli');
        $sahipsiz = DB::table('isletmeyetkilileri as y')
            ->whereNotIn('y.id', function ($sub) {
                $sub->from('salon_personelleri')->select('yetkili_id')->whereNotNull('yetkili_id');
            })
            ->where(function ($q) {
                $q->whereNull('y.gsm1')->orWhere('y.gsm1', '');
            })
            ->orderBy('y.id')
            ->limit(100)
            ->get(['y.id', 'y.name', 'y.gsm1', 'y.unvan']);
        if ($sahipsiz->isEmpty()) {
            $this->line('   Temiz: bos-gsm sahipsiz yetkili yok.');
        } else {
            $this->line('   ' . $sahipsiz->count() . ' kayit (max 100):');
            foreach ($sahipsiz as $s) {
                $this->line("   yetkili #{$s->id}  name='" . ($s->name ?: '(bos)') . "'  gsm1='" . ($s->gsm1 ?: '(BOŞ)') . "'  unvan='{$s->unvan}'");
            }
        }
        $this->line('');

        // ---- 5) Paylasilan yetkililerin model_has_roles satirlari ----
        if (!$paylasan->isEmpty()) {
            $this->info('5) PAYLAŞILAN yetkililerin model_has_roles kayitlari');
            $ids = $paylasan->pluck('yetkili_id')->all();
            $roller = DB::table('model_has_roles as mr')
                ->leftJoin('roles as r', 'mr.role_id', '=', 'r.id')
                ->whereIn('mr.model_id', $ids)
                ->orderBy('mr.model_id')->orderBy('mr.salon_id')
                ->get(['mr.model_id', 'mr.role_id', 'r.name as rol_adi', 'mr.salon_id']);
            if ($roller->isEmpty()) {
                $this->line('   Bu yetkililer icin model_has_roles kaydi YOK (roller silinmis olabilir).');
            } else {
                foreach ($roller as $r) {
                    $this->line("   model_id(yetkili)={$r->model_id}  role_id={$r->role_id} ({$r->rol_adi})  salon_id={$r->salon_id}");
                }
            }
            $this->line('');
        }

        $this->info('=== TEŞHİS BİTTİ (hicbir veri degistirilmedi) ===');
        $this->line('Onarim icin bu ciktiyi paylasin; hedefli bir onarim komutu birlikte planlayalim.');
        return 0;
    }
}
