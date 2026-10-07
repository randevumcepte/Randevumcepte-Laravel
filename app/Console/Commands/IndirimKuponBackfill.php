<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * KUPON İNDİRİM BACKFILL — kuponu kullanılmış (yakılmış) ama indirimi adisyona HİÇ
 * yazılmamış geçmiş adisyonları, indirimi kaleme işleyerek onarır.
 *
 * Arka plan: kampanyaIndirimKoduKullan / kampanyaKodKullanApi eskiden sadece kuponu
 * "kullanıldı" işaretliyor, indirimi JSON'da dönüyordu; indirim adisyona YALNIZCA aynı
 * oturumda harici-indirim dolu bir tahsilat kaydedilirse işleniyordu. Yakılıp tahsilat
 * kaydedilmeyen senaryolarda adisyon indirim kadar hayalet bakiyeyle AÇIK kalıyordu.
 * Forward path düzeltildi (indirim artık kupon yanınca yazılıyor); bu komut GEÇMİŞİ onarır.
 *
 * GÜVENLİK (sistemi bozmadan):
 *  - İşletme (salon) ZORUNLU; istenirse --adisyon ile belirli adisyonlara daraltılır.
 *  - Varsayılan KURU çalışır (sadece rapor); yazmak için --uygula.
 *  - ÇİFT İNDİRİM KORUMASI: kalem(ler)de kuponun indirimi ZATEN varsa (eski tahsilat
 *    akışıyla işlenmiş) atlar; yalnızca EKSİK kısmı yazar.
 *  - Yazılan tutar kalem bakiyesine (fiyat - mevcut indirim - tahsilat) KIRPILIR;
 *    adisyon asla negatife/fazla-kapalıya düşmez.
 *  - Yalnızca YÜZDE tipli kuponlar; "X Al Y Öde" vb. elle kalır.
 *
 * Kullanım:
 *   /opt/php74/bin/php artisan indirim:kupon-backfill 401                 (KURU rapor)
 *   /opt/php74/bin/php artisan indirim:kupon-backfill 401 --uygula        (yazar)
 *   /opt/php74/bin/php artisan indirim:kupon-backfill 401 --adisyon=1234,1240 --uygula
 */
class IndirimKuponBackfill extends Command
{
    protected $signature = 'indirim:kupon-backfill {salon : Salon ID}
        {--adisyon= : Yalnizca bu adisyon ID(ler)i (virgulle ayrilmis)}
        {--uygula : Indirimi adisyona yaz (yoksa kuru/rapor calisir)}';
    protected $description = 'Kuponu yakilmis ama indirimi adisyona yazilmamis adisyonlari onarir (salon bazli, cift-indirim korumali)';

    public function handle()
    {
        $salonId = (int) $this->argument('salon');
        $uygula  = (bool) $this->option('uygula');
        $adisyonFiltre = array_values(array_filter(array_map('intval',
            explode(',', (string) $this->option('adisyon'))), fn($v) => $v > 0));

        if ($salonId <= 0) { $this->error('Gecerli bir salon ID verin.'); return 1; }

        $this->info('=== KUPON İNDİRİM BACKFILL ===');
        $this->line("salon={$salonId}  mod=".($uygula ? 'UYGULA' : 'KURU (rapor)')
            .(empty($adisyonFiltre) ? '' : '  adisyon='.implode(',', $adisyonFiltre)));
        $this->line('');

        // Yakilmis, bir adisyona bagli, HENUZ backfill/yeni-akis ile tutar yazilmamis kuponlar.
        $kolonTutarVar = \Schema::hasColumn('kampanya_katilimcilari', 'indirim_kodu_tutar');
        $kolonDusulduVar = \Schema::hasColumn('kampanya_katilimcilari', 'indirim_kodu_tahsilata_dusuldu');

        $q = DB::table('kampanya_katilimcilari as kk')
            ->join('kampanya_yonetimi as ky', 'ky.id', '=', 'kk.kampanya_id')
            ->where('ky.salon_id', $salonId)
            ->where('kk.indirim_kodu_kullanildi', 1)
            ->whereNotNull('kk.indirim_kodu_adisyon_id')
            ->where('kk.indirim_kodu_adisyon_id', '>', 0);
        if ($kolonTutarVar) {
            $q->whereNull('kk.indirim_kodu_tutar'); // yeni akis/backfill ile zaten yazilanlari atla
        }
        if (!empty($adisyonFiltre)) {
            $q->whereIn('kk.indirim_kodu_adisyon_id', $adisyonFiltre);
        }
        $kuponlar = $q->select('kk.id as kk_id', 'kk.indirim_kodu', 'kk.indirim_kodu_adisyon_id as adisyon_id',
                'ky.indirim_turu', 'ky.hizmet_id as k_hizmet_id', 'ky.urun_id as k_urun_id', 'ky.paket_id as k_paket_id')
            ->orderBy('kk.indirim_kodu_adisyon_id')->get();

        $this->line("Aday kupon kaydi: ".count($kuponlar));
        $this->line('');

        $onarilacak = []; // [kk_id => [...plan]]
        $atlanan = ['kapali' => 0, 'zaten_var' => 0, 'yuzde_degil' => 0, 'kalem_yok' => 0, 'adisyon_yok' => 0];

        foreach ($kuponlar as $k) {
            $adisyonId = (int) $k->adisyon_id;
            if (!DB::table('adisyonlar')->where('id', $adisyonId)->exists()) { $atlanan['adisyon_yok']++; continue; }

            // Yuzde coz
            if (!preg_match('/%\s*(\d+)/u', (string) $k->indirim_turu, $m)) { $atlanan['yuzde_degil']++; continue; }
            $yuzde = (int) $m[1];
            if ($yuzde <= 0) { $atlanan['yuzde_degil']++; continue; }

            $hId = (int) ($k->k_hizmet_id ?? 0);
            $uId = (int) ($k->k_urun_id ?? 0);
            $pId = (int) ($k->k_paket_id ?? 0);
            $spesifik = ($hId || $uId || $pId);

            // Kapsam kalemleri (senet/taksit haric — forward akisla ayni)
            $scopeH = DB::table('adisyon_hizmetler')->where('adisyon_id', $adisyonId)->whereNull('senet_id')->whereNull('taksitli_tahsilat_id')->when($spesifik && $hId, fn($q)=>$q->where('hizmet_id',$hId))->when($spesifik && !$hId, fn($q)=>$q->whereRaw('1=0'))->get();
            $scopeU = DB::table('adisyon_urunler')->where('adisyon_id', $adisyonId)->whereNull('senet_id')->whereNull('taksitli_tahsilat_id')->when($spesifik && $uId, fn($q)=>$q->where('urun_id',$uId))->when($spesifik && !$uId, fn($q)=>$q->whereRaw('1=0'))->get();
            $scopeP = DB::table('adisyon_paketler')->where('adisyon_id', $adisyonId)->whereNull('senet_id')->whereNull('taksitli_tahsilat_id')->when($spesifik && $pId, fn($q)=>$q->where('paket_id',$pId))->when($spesifik && !$pId, fn($q)=>$q->whereRaw('1=0'))->get();

            $scopeFiyat   = (float)($scopeH->sum('fiyat') + $scopeU->sum('fiyat') + $scopeP->sum('fiyat'));
            if ($scopeFiyat <= 0) { $atlanan['kalem_yok']++; continue; }
            $scopeIndirim = (float)($scopeH->sum('indirim_tutari') + $scopeU->sum('indirim_tutari') + $scopeP->sum('indirim_tutari'));

            $expected = round($scopeFiyat * $yuzde / 100, 2);
            // ZATEN UYGULANMIS MI? kapsamdaki mevcut indirim beklenen kadar (veya fazla) ise dokunma.
            if ($scopeIndirim >= $expected - 0.01) { $atlanan['zaten_var']++; continue; }

            // Kapsam tahsilati
            $hIds = $scopeH->pluck('id')->all(); $uIds = $scopeU->pluck('id')->all(); $pIds = $scopeP->pluck('id')->all();
            $scopeTahsilat = 0.0;
            if ($hIds) $scopeTahsilat += (float) DB::table('tahsilat_hizmetler')->whereIn('adisyon_hizmet_id', $hIds)->sum('tutar');
            if ($uIds) $scopeTahsilat += (float) DB::table('tahsilat_urunler')->whereIn('adisyon_urun_id', $uIds)->sum('tutar');
            if ($pIds) $scopeTahsilat += (float) DB::table('tahsilat_paketler')->whereIn('adisyon_paket_id', $pIds)->sum('tutar');

            $room    = $scopeFiyat - $scopeIndirim - $scopeTahsilat; // kapsam bakiyesi
            $eksik   = $expected - $scopeIndirim;                    // yazilmasi gereken eksik indirim
            $uygulanacak = max(0.0, min($eksik, $room));
            if ($uygulanacak <= 0.009) { $atlanan['kapali']++; continue; } // bakiye yok -> zaten kapali/odenmis

            $onarilacak[] = [
                'kk_id' => (int) $k->kk_id, 'kod' => $k->indirim_kodu, 'adisyon_id' => $adisyonId,
                'yuzde' => $yuzde, 'beklenen' => $expected, 'mevcut_indirim' => round($scopeIndirim,2),
                'uygulanacak' => round($uygulanacak,2),
                'scopeFiyat' => $scopeFiyat,
                'kalemler' => ['h'=>$scopeH, 'u'=>$scopeU, 'p'=>$scopeP],
            ];
            $this->line(sprintf('  adisyon #%-6d kod=%-12s %%%d  beklenen=%s mevcut=%s -> YAZ=%s',
                $adisyonId, $k->indirim_kodu, $yuzde,
                number_format($expected,2,',','.'), number_format($scopeIndirim,2,',','.'),
                number_format($uygulanacak,2,',','.')));
        }

        $this->line('');
        $this->info('ÖZET: '.count($onarilacak).' onarilacak | atlanan: '
            .'zaten_var='.$atlanan['zaten_var'].' kapali/odenmis='.$atlanan['kapali']
            .' yuzde_degil='.$atlanan['yuzde_degil'].' kalem_yok='.$atlanan['kalem_yok'].' adisyon_yok='.$atlanan['adisyon_yok']);

        if (!$uygula) {
            $this->warn('KURU çalışma — hiçbir şey yazılmadı. Uygulamak için --uygula ekleyin.');
            return 0;
        }
        if (empty($onarilacak)) { $this->info('Onarilacak kayit yok.'); return 0; }

        $yazilan = 0;
        DB::beginTransaction();
        try {
            foreach ($onarilacak as $p) {
                $apply = $p['uygulanacak'];
                $scopeFiyat = $p['scopeFiyat'];
                foreach (['h'=>'adisyon_hizmetler', 'u'=>'adisyon_urunler', 'p'=>'adisyon_paketler'] as $tip => $tablo) {
                    foreach ($p['kalemler'][$tip] as $kalem) {
                        $pay = round(((float)$kalem->fiyat / $scopeFiyat) * $apply, 2);
                        if ($pay == 0) continue;
                        DB::table($tablo)->where('id', $kalem->id)
                            ->update(['indirim_tutari' => DB::raw('COALESCE(indirim_tutari,0) + '.$pay)]);
                    }
                }
                // Kupon kaydini isaretle: tutar + tahsilata-dusuldu (guard cift uygulamayi onlesin)
                $guncelle = [];
                if ($kolonTutarVar)   $guncelle['indirim_kodu_tutar'] = $apply;
                if ($kolonDusulduVar) $guncelle['indirim_kodu_tahsilata_dusuldu'] = 1; // gecmis: tahsilat zaten olmus
                if (!empty($guncelle)) DB::table('kampanya_katilimcilari')->where('id', $p['kk_id'])->update($guncelle);
                $yazilan++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('HATA, geri alındı (hiçbir şey yazılmadı): '.$e->getMessage());
            return 1;
        }

        // Acik/kapali sayim cache'ini gecersiz kil (mobil rozetler taze olsun)
        try {
            $key = 'adisyon_sayim_ver_salon:'.$salonId;
            Cache::forever($key, (int) Cache::get($key, 0) + 1);
        } catch (\Throwable $e) {}

        $this->info("BİTTİ — {$yazilan} adisyona indirim yazıldı.");
        return 0;
    }
}
