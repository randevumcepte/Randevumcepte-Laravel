<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * YETİM ALACAK / TAKSİT TEMİZLİK — bir salonda parent'ı silinmiş (dangling) alacak
 * ve taksitli tahsilat kayıtlarını bulur ve (--uygula ile) siler.
 *
 * Arka plan: taksitekleguncelle her vadeye bir alacaklar kaydı açıp taksit_vade_id /
 * taksitli_tahsilat_id ile bağlıyor; ama eski silme akışı alacakları YALNIZCA
 * adisyon_id üzerinden siliyordu. adisyon_id NULL olan taksit alacakları silinmeyip
 * "silinmiş vadeye işaret eden hayalet borç" olarak kalıyordu (ör. 18 alacak + 1 taksit).
 * Silme akışı düzeltildi; bu komut GEÇMİŞ kalıntıları temizler.
 *
 * Bir alacak "yetim" sayılır: en az bir yapısal bağı (adisyon_id, taksit_vade_id,
 * senet_vade_id, taksitli_tahsilat_id, senet_id) DOLU ama bunların HİÇBİRİ yaşayan
 * bir satıra çözülmüyorsa. Canlı bağı olan alacaklara DOKUNULMAZ.
 *
 * Kullanım:
 *   /opt/php74/bin/php artisan alacak:temizle 401            (KURU: sadece rapor)
 *   /opt/php74/bin/php artisan alacak:temizle 401 --uygula   (siler)
 */
class AlacakTemizle extends Command
{
    protected $signature = 'alacak:temizle {salon : Salon ID} {--uygula : Yetim kayitlari sil (yoksa kuru calisir)}';
    protected $description = 'Bir salonda parent\'i silinmis yetim alacak/taksit kayitlarini bulur ve --uygula ile siler';

    public function handle()
    {
        $salonId = (int) $this->argument('salon');
        $uygula  = (bool) $this->option('uygula');

        $this->info('=== YETİM ALACAK / TAKSİT TEMİZLİK ===');
        $this->line("salon={$salonId}  mod=".($uygula ? 'UYGULA' : 'KURU (rapor)'));
        $this->line('');

        // Yasayan id kumelerini onceden yukle (hizli lookup)
        $adisyonVar = DB::table('adisyonlar')->pluck('id')->flip();
        $taksitVar  = DB::table('taksitli_tahsilatlar')->pluck('id')->flip();
        $taksitVadeVar = DB::table('taksit_vadeleri')->pluck('id')->flip();
        $senetVar   = DB::table('senetler')->pluck('id')->flip();
        $senetVadeVar = DB::table('senet_vadeleri')->pluck('id')->flip();

        $has = function ($col) {
            return \Schema::hasColumn('alacaklar', $col);
        };
        $colAdisyon   = $has('adisyon_id');
        $colTVade     = $has('taksit_vade_id');
        $colSVade     = $has('senet_vade_id');
        $colTaksit    = $has('taksitli_tahsilat_id');
        $colSenet     = $has('senet_id');

        $alacaklar = DB::table('alacaklar')->where('salon_id', $salonId)->get();
        $yetimAlacakIdler = [];
        $this->line("Salon {$salonId} toplam alacak kaydı: ".count($alacaklar));

        foreach ($alacaklar as $a) {
            $baglar = []; // [ 'etiket' => cozuldu_mu(bool) ]
            if ($colAdisyon && !empty($a->adisyon_id)) $baglar['adisyon'] = isset($adisyonVar[$a->adisyon_id]);
            if ($colTVade   && !empty($a->taksit_vade_id)) $baglar['taksit_vade'] = isset($taksitVadeVar[$a->taksit_vade_id]);
            if ($colSVade   && !empty($a->senet_vade_id)) $baglar['senet_vade'] = isset($senetVadeVar[$a->senet_vade_id]);
            if ($colTaksit  && !empty($a->taksitli_tahsilat_id)) $baglar['taksit'] = isset($taksitVar[$a->taksitli_tahsilat_id]);
            if ($colSenet   && !empty($a->senet_id)) $baglar['senet'] = isset($senetVar[$a->senet_id]);

            if (count($baglar) == 0) continue;              // hiç yapısal bağ yok -> dokunma (elle alacak olabilir)
            if (in_array(true, $baglar, true)) continue;     // en az bir canlı bağ var -> yetim değil

            $yetimAlacakIdler[] = $a->id;
            $sebep = implode(',', array_map(function ($k) { return $k.'=SİLİNMİŞ'; }, array_keys($baglar)));
            $this->line(sprintf('  yetim alacak #%-6d tutar=%s bağ[%s]', $a->id, number_format((float)$a->tutar, 2, ',', '.'), $sebep));
        }

        // Yetim taksitli tahsilatlar: adisyon_id dolu ama adisyon yok; VEYA adisyon_id
        // yok ve hiçbir adisyon kalemi (hizmet/urun/paket) taksitli_tahsilat_id ile
        // bu taksite bağlı değil.
        $taksitler = DB::table('taksitli_tahsilatlar')->where('salon_id', $salonId)->get();
        $yetimTaksitIdler = [];
        foreach ($taksitler as $t) {
            $yetim = false;
            if (!empty($t->adisyon_id)) {
                $yetim = !isset($adisyonVar[$t->adisyon_id]);
            } else {
                $bagliKalem =
                    DB::table('adisyon_hizmetler')->where('taksitli_tahsilat_id', $t->id)->exists() ||
                    DB::table('adisyon_urunler')->where('taksitli_tahsilat_id', $t->id)->exists() ||
                    DB::table('adisyon_paketler')->where('taksitli_tahsilat_id', $t->id)->exists();
                $yetim = !$bagliKalem;
            }
            if ($yetim) {
                $yetimTaksitIdler[] = $t->id;
                $vadeSayisi = DB::table('taksit_vadeleri')->where('taksitli_tahsilat_id', $t->id)->count();
                $this->line(sprintf('  yetim taksit  #%-6d adisyon_id=%s vade=%d', $t->id, $t->adisyon_id ?? 'NULL', $vadeSayisi));
            }
        }

        $this->line('');
        $this->info('ÖZET: '.count($yetimAlacakIdler).' yetim alacak, '.count($yetimTaksitIdler).' yetim taksit (+ vadeleri)');

        if (!$uygula) {
            $this->warn('KURU çalışma — hiçbir şey silinmedi. Yazmak için sonuna --uygula ekleyin.');
            return 0;
        }

        DB::beginTransaction();
        try {
            if (count($yetimAlacakIdler) > 0)
                DB::table('alacaklar')->whereIn('id', $yetimAlacakIdler)->delete();
            if (count($yetimTaksitIdler) > 0) {
                // Yetim taksitin vadeleri + o vadelere bağlı alacaklar da gitsin
                $vadeIdler = DB::table('taksit_vadeleri')->whereIn('taksitli_tahsilat_id', $yetimTaksitIdler)->pluck('id')->toArray();
                if (count($vadeIdler) > 0 && $colTVade)
                    DB::table('alacaklar')->whereIn('taksit_vade_id', $vadeIdler)->delete();
                if ($colTaksit)
                    DB::table('alacaklar')->whereIn('taksitli_tahsilat_id', $yetimTaksitIdler)->delete();
                DB::table('taksit_vadeleri')->whereIn('taksitli_tahsilat_id', $yetimTaksitIdler)->delete();
                DB::table('taksitli_tahsilatlar')->whereIn('id', $yetimTaksitIdler)->delete();
            }
            DB::commit();
            $this->info('BİTTİ — yetim kayıtlar silindi.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('HATA, geri alındı: '.$e->getMessage());
            return 1;
        }
        return 0;
    }
}
