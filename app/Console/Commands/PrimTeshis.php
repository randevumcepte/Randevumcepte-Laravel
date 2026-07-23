<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Prim & Hak Edis raporu bos/0 cikiyorsa nedenini gosterir.
 * Rapor mantigi (StoreAdminController::primHakedisVerisi) su zinciri kullanir:
 *   adisyonlar(tarih araligi) -> adisyon_{hizmetler,urunler,paketler}(personel_id DOLU olmali)
 *   -> tahsilat_{hizmetler,urunler,paketler}(tutar) * personel yuzdesi
 * Zincirdeki her halkanin durumunu ayri ayri raporlar.
 */
class PrimTeshis extends Command
{
    protected $signature = 'prim:teshis {--salon= : Salon (isletme) ID} {--ay= : YYYY-MM, varsayilan bu ay}';
    protected $description = 'Prim raporu neden 0 cikiyor? Personel yuzdeleri + kalem personel_id + tahsilat zincirini teshis eder.';

    public function handle()
    {
        $salonId = (int) $this->option('salon');
        if (!$salonId) { $this->error('--salon zorunlu'); return 1; }

        $ay = $this->option('ay') ?: date('Y-m');
        $bas = $ay . '-01 00:00:00';
        $bit = date('Y-m-t', strtotime($ay . '-01')) . ' 23:59:59';

        $this->info("=== PRIM TESHIS — salon {$salonId} / donem {$ay} ({$bas} .. {$bit}) ===");

        // 1) Personel yuzdeleri
        $this->line("\n[1] PERSONEL YUZDELERI (salon_personelleri)");
        $personeller = DB::table('salon_personelleri')
            ->where('salon_id', $salonId)->where('aktif', 1)
            ->where(function ($q) { $q->where('arsivli', 0)->orWhereNull('arsivli'); })
            ->orderBy('takvim_sirasi')->get();
        if ($personeller->isEmpty()) {
            $this->warn('  !! Aktif/arsivsiz personel YOK — rapor komple bos gelir.');
        }
        $sifirYuzdeli = 0;
        foreach ($personeller as $p) {
            $h = (float) ($p->hizmet_prim_yuzde ?? 0);
            $u = (float) ($p->urun_prim_yuzde ?? 0);
            $k = (float) ($p->paket_prim_yuzde ?? 0);
            $det = [];
            foreach (['hizmet', 'urun', 'paket'] as $t) {
                $kol = $t . '_prim_detayli';
                if (!empty($p->$kol)) {
                    $adet = DB::table('personel_prim_oranlari')
                        ->where('personel_id', $p->id)->where('tur', $t)->count();
                    $det[] = $t . '-detayli(' . $adet . ' kalem' . ($adet === 0 ? ' -> PRIM 0!' : '') . ')';
                }
            }
            if ($h == 0 && $u == 0 && $k == 0 && empty($det)) $sifirYuzdeli++;
            $this->line(sprintf('  #%-6d %-28s hizmet:%5s urun:%5s paket:%5s %s',
                $p->id, mb_substr($p->personel_adi, 0, 28), $h, $u, $k, implode(' ', $det)));
        }
        if ($sifirYuzdeli > 0) {
            $this->warn("  !! {$sifirYuzdeli} personelin TUM yuzdeleri 0 — bunlara prim hesaplanmaz.");
        }

        // 2) Donemdeki adisyon kalemleri ve personel_id doluluk orani
        $this->line("\n[2] DONEMDEKI ADISYON KALEMLERI (personel_id doluluk)");
        $adIds = DB::table('adisyonlar')->where('salon_id', $salonId)
            ->whereBetween('tarih', [$bas, $bit])->pluck('id');
        $this->line('  Adisyon sayisi: ' . $adIds->count());
        if ($adIds->isEmpty()) {
            $this->warn('  !! Bu donemde hic adisyon yok — prim 0 cikar (adisyonlar.tarih araligi disinda mi?).');
        }

        $kalemler = [
            'adisyon_hizmetler' => 'tahsilat_hizmetler|adisyon_hizmet_id',
            'adisyon_urunler'   => 'tahsilat_urunler|adisyon_urun_id',
            'adisyon_paketler'  => 'tahsilat_paketler|adisyon_paket_id',
        ];
        foreach ($kalemler as $tablo => $tahsilatBilgi) {
            if (!\Schema::hasTable($tablo)) continue;
            list($tTablo, $tKolon) = explode('|', $tahsilatBilgi);
            $ids = $adIds->isEmpty() ? collect()
                : DB::table($tablo)->whereIn('adisyon_id', $adIds)->pluck('id');
            $dolu = $adIds->isEmpty() ? 0
                : DB::table($tablo)->whereIn('adisyon_id', $adIds)->whereNotNull('personel_id')->count();
            $toplam = $ids->count();
            $bos = $toplam - $dolu;

            $tahsilatToplam = 0; $tahsilatDolu = 0;
            if ($toplam > 0 && \Schema::hasTable($tTablo)) {
                $tahsilatToplam = (float) DB::table($tTablo)->whereIn($tKolon, $ids)->sum('tutar');
                $doluIds = DB::table($tablo)->whereIn('adisyon_id', $adIds)
                    ->whereNotNull('personel_id')->pluck('id');
                $tahsilatDolu = $doluIds->isEmpty() ? 0
                    : (float) DB::table($tTablo)->whereIn($tKolon, $doluIds)->sum('tutar');
            }

            $this->line(sprintf('  %-18s kalem:%-6d personelli:%-6d PERSONELSIZ:%-6d | tahsilat toplam:%12s prime giren:%12s',
                $tablo, $toplam, $dolu, $bos,
                number_format($tahsilatToplam, 2), number_format($tahsilatDolu, 2)));
            if ($bos > 0) {
                $this->warn("    !! {$bos} kalemde personel_id BOS — rapor bunlari komple atlar (gelir de prim de 0 gorunur).");
            }
            if ($toplam > 0 && $tahsilatToplam <= 0) {
                $this->warn("    !! {$tTablo} bos — odeme kalem bazinda dagitilmamis, prim tabani 0.");
            }
        }

        // 3) Ozet: rapor bu donem icin ne uretiyor
        $this->line("\n[3] BEKLENEN PRIM (rapor mantiginin ozeti)");
        $toplamPrim = 0;
        foreach ($personeller as $p) {
            $satir = 0;
            foreach ([
                ['adisyon_hizmetler', 'tahsilat_hizmetler', 'adisyon_hizmet_id', 'hizmet'],
                ['adisyon_urunler',   'tahsilat_urunler',   'adisyon_urun_id',   'urun'],
                ['adisyon_paketler',  'tahsilat_paketler',  'adisyon_paket_id',  'paket'],
            ] as $x) {
                list($kt, $tt, $tk, $tur) = $x;
                if (!\Schema::hasTable($kt) || $adIds->isEmpty()) continue;
                $ids = DB::table($kt)->whereIn('adisyon_id', $adIds)
                    ->where('personel_id', $p->id)->pluck('id');
                if ($ids->isEmpty()) continue;
                $kazanc = (float) DB::table($tt)->whereIn($tk, $ids)->sum('tutar');
                $yuzde = (float) ($p->{$tur . '_prim_yuzde'} ?? 0);
                if (!empty($p->{$tur . '_prim_detayli'})) $yuzde = -1; // detayli: kalem bazli
                $satir += $yuzde > 0 ? $kazanc * $yuzde / 100 : 0;
            }
            if ($satir > 0) {
                $toplamPrim += $satir;
                $this->line(sprintf('  #%-6d %-28s prim: %s', $p->id, mb_substr($p->personel_adi, 0, 28), number_format($satir, 2)));
            }
        }
        $this->info('  TOPLAM PRIM: ' . number_format($toplamPrim, 2));
        if ($toplamPrim <= 0) {
            $this->warn('  -> Rapor 0 gosterecek. Yukaridaki [1] ve [2] uyarilarina bak.');
        }

        // 4) Kisi bazinda ciro (kazanc) dagilimi — yuzde ne olursa olsun.
        //    "Para kimin uzerinde, o kisinin yuzdesi kac?" sorusunu net gosterir.
        //    kazanc>0 ama yuzde=0 olan satirlar KAYIP PRIM'dir (yuzde girilmeli).
        $this->line("\n[4] KISI BAZINDA CIRO ve YUZDE (kayip prim teshisi)");
        $this->line(sprintf('  %-28s %14s %5s %14s %5s %14s %5s',
            'PERSONEL', 'HIZMET_CIRO', 'yzd', 'URUN_CIRO', 'yzd', 'PAKET_CIRO', 'yzd'));
        $kayipVar = false;
        foreach ($personeller as $p) {
            $ciro = ['hizmet' => 0, 'urun' => 0, 'paket' => 0];
            foreach ([
                ['adisyon_hizmetler', 'tahsilat_hizmetler', 'adisyon_hizmet_id', 'hizmet'],
                ['adisyon_urunler',   'tahsilat_urunler',   'adisyon_urun_id',   'urun'],
                ['adisyon_paketler',  'tahsilat_paketler',  'adisyon_paket_id',  'paket'],
            ] as $x) {
                list($kt, $tt, $tk, $tur) = $x;
                if (!\Schema::hasTable($kt) || $adIds->isEmpty()) continue;
                $ids = DB::table($kt)->whereIn('adisyon_id', $adIds)->where('personel_id', $p->id)->pluck('id');
                if ($ids->isEmpty()) continue;
                $ciro[$tur] = (float) DB::table($tt)->whereIn($tk, $ids)->sum('tutar');
            }
            if ($ciro['hizmet'] == 0 && $ciro['urun'] == 0 && $ciro['paket'] == 0) continue;

            $hy = (float) ($p->hizmet_prim_yuzde ?? 0);
            $uy = (float) ($p->urun_prim_yuzde ?? 0);
            $py = (float) ($p->paket_prim_yuzde ?? 0);
            $isaret = '';
            if (($ciro['hizmet'] > 0 && $hy == 0 && empty($p->hizmet_prim_detayli))
             || ($ciro['urun']   > 0 && $uy == 0 && empty($p->urun_prim_detayli))
             || ($ciro['paket']  > 0 && $py == 0 && empty($p->paket_prim_detayli))) {
                $isaret = '  <- CIRO VAR AMA YUZDE 0 = KAYIP PRIM';
                $kayipVar = true;
            }
            $this->line(sprintf('  #%-4d %-22s %14s %5s %14s %5s %14s %5s%s',
                $p->id, mb_substr($p->personel_adi, 0, 22),
                number_format($ciro['hizmet'], 0), $hy,
                number_format($ciro['urun'], 0), $uy,
                number_format($ciro['paket'], 0), $py, $isaret));
        }
        if ($kayipVar) {
            $this->warn("\n  !! CIRO VAR AMA YUZDE 0 olan personeller var — prim raporunda gorunmemelerinin");
            $this->warn("     asil sebebi budur. Ilgili personelin prim yuzdesini girmen gerekiyor.");
        }

        return 0;
    }
}
