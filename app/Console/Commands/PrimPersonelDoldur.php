<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Prim raporu, personel_id'si BOS olan adisyon kalemlerini komple atlar
 * (StoreAdminController::primHakedisVerisi -> filter(fn($i)=>$i->personel_id)).
 * Salonappy paket aktarimi bu alani hic yazmadigi icin (ve manuel girislerde
 * personel secilmediginde) gecmis kalemler personelsiz kalmis olabilir.
 *
 * Bu komut personelsiz kalemleri sirasiyla su kaynaklardan doldurur:
 *   1) ayni adisyondaki personelli baska bir kalem
 *   2) ayni musteri + ayni hizmet icin randevu_hizmetler.personel_id (tarihe en yakin)
 *   3) adisyonlar.olusturan_id (adisyonu acan personel)
 *   4) --varsayilan=ID  (kalan hepsi icin elle verilen personel)
 *
 * Varsayilan olarak SADECE RAPORLAR. Yazmak icin --uygula gerekir.
 */
class PrimPersonelDoldur extends Command
{
    protected $signature = 'prim:personel-doldur
        {--salon= : Salon (isletme) ID — zorunlu}
        {--from= : Baslangic tarihi YYYY-MM-DD (adisyonlar.tarih)}
        {--to= : Bitis tarihi YYYY-MM-DD}
        {--tur=hepsi : hizmet|urun|paket|hepsi}
        {--varsayilan= : Hicbir kaynaktan cozulemeyenler icin personel ID}
        {--uygula : Gercekten yaz (verilmezse sadece rapor / dry-run)}';

    protected $description = 'Personel_id bos adisyon kalemlerini doldurur (prim raporu bu kalemleri atliyor).';

    /** @var array kalem tablosu => [hizmet kolonu, randevu eslesmesi mumkun mu] */
    private $tablolar = [
        'hizmet' => ['adisyon_hizmetler', 'hizmet_id', true],
        'urun'   => ['adisyon_urunler',   'urun_id',   false],
        'paket'  => ['adisyon_paketler',  'paket_id',  false],
    ];

    public function handle()
    {
        $salonId = (int) $this->option('salon');
        if (!$salonId) { $this->error('--salon zorunlu'); return 1; }

        $uygula = (bool) $this->option('uygula');
        $varsayilan = $this->option('varsayilan') ? (int) $this->option('varsayilan') : null;
        $tur = $this->option('tur') ?: 'hepsi';

        if ($varsayilan) {
            $v = DB::table('salon_personelleri')->where('id', $varsayilan)->where('salon_id', $salonId)->first();
            if (!$v) { $this->error("--varsayilan={$varsayilan} bu salonda bulunamadi."); return 1; }
            $this->line('Varsayilan personel: #' . $v->id . ' ' . $v->personel_adi);
        }

        $adSorgu = DB::table('adisyonlar')->where('salon_id', $salonId);
        if ($this->option('from')) $adSorgu->where('tarih', '>=', $this->option('from') . ' 00:00:00');
        if ($this->option('to'))   $adSorgu->where('tarih', '<=', $this->option('to') . ' 23:59:59');
        $adIds = $adSorgu->pluck('id');

        $this->info('=== PERSONEL DOLDUR — salon ' . $salonId
            . ' | adisyon: ' . $adIds->count()
            . ' | mod: ' . ($uygula ? 'UYGULA (yazacak)' : 'DRY-RUN (yazmaz)') . ' ===');
        if ($adIds->isEmpty()) { $this->warn('Bu filtreyle adisyon yok.'); return 0; }

        // Randevu kaynagi: user+hizmet -> [ [tarih, personel_id], ... ]
        $randevuHarita = $this->randevuHaritasi($salonId);
        // Adisyon -> olusturan_id
        $olusturanlar = DB::table('adisyonlar')->whereIn('id', $adIds)
            ->whereNotNull('olusturan_id')->pluck('olusturan_id', 'id');
        // Adisyon -> user_id, tarih
        $adisyonBilgi = DB::table('adisyonlar')->whereIn('id', $adIds)
            ->get(['id', 'user_id', 'tarih'])->keyBy('id');

        $genelToplam = 0; $genelCozulen = 0;
        foreach ($this->tablolar as $ad => $bilgi) {
            if ($tur !== 'hepsi' && $tur !== $ad) continue;
            list($tablo, $kalemKolon, $randevuVar) = $bilgi;
            if (!\Schema::hasTable($tablo) || !\Schema::hasColumn($tablo, 'personel_id')) continue;

            $bos = DB::table($tablo)->whereIn('adisyon_id', $adIds)
                ->whereNull('personel_id')->get(['id', 'adisyon_id', $kalemKolon]);
            if ($bos->isEmpty()) {
                $this->line("\n[{$ad}] personelsiz kalem yok.");
                continue;
            }
            $this->line("\n[{$ad}] personelsiz kalem: " . $bos->count());

            // Ayni adisyondaki personelli kalemler (tum turlerden)
            $adisyonPersonel = $this->adisyonPersonelHaritasi($adIds);

            $sayac = ['adisyon' => 0, 'randevu' => 0, 'olusturan' => 0, 'varsayilan' => 0, 'cozulemeyen' => 0];
            $guncellemeler = [];

            foreach ($bos as $k) {
                $pid = null; $kaynak = null;

                if (isset($adisyonPersonel[$k->adisyon_id])) {
                    $pid = $adisyonPersonel[$k->adisyon_id]; $kaynak = 'adisyon';
                }
                if (!$pid && $randevuVar) {
                    $ab = $adisyonBilgi->get($k->adisyon_id);
                    if ($ab) {
                        $pid = $this->randevudanBul($randevuHarita, $ab->user_id, $k->$kalemKolon, $ab->tarih);
                        if ($pid) $kaynak = 'randevu';
                    }
                }
                if (!$pid && isset($olusturanlar[$k->adisyon_id])) {
                    $pid = (int) $olusturanlar[$k->adisyon_id]; $kaynak = 'olusturan';
                }
                if (!$pid && $varsayilan) {
                    $pid = $varsayilan; $kaynak = 'varsayilan';
                }

                if ($pid) { $sayac[$kaynak]++; $guncellemeler[$pid][] = $k->id; }
                else      { $sayac['cozulemeyen']++; }
            }

            $cozulen = $bos->count() - $sayac['cozulemeyen'];
            $this->line(sprintf('  ayni adisyon:%d  randevu:%d  olusturan:%d  varsayilan:%d  COZULEMEYEN:%d',
                $sayac['adisyon'], $sayac['randevu'], $sayac['olusturan'], $sayac['varsayilan'], $sayac['cozulemeyen']));

            if ($uygula && !empty($guncellemeler)) {
                foreach ($guncellemeler as $pid => $ids) {
                    foreach (array_chunk($ids, 500) as $parca) {
                        DB::table($tablo)->whereIn('id', $parca)
                            ->update(['personel_id' => $pid, 'updated_at' => date('Y-m-d H:i:s')]);
                    }
                }
                $this->info("  -> {$cozulen} kalem guncellendi.");
            } elseif (!$uygula) {
                $this->comment("  -> DRY-RUN: yazilmadi. Uygulamak icin --uygula ekle.");
            }

            if ($sayac['cozulemeyen'] > 0) {
                $this->warn('  !! ' . $sayac['cozulemeyen'] . ' kalem hicbir kaynaktan cozulemedi — --varsayilan=<personel_id> ile atayabilirsin.');
            }

            $genelToplam += $bos->count();
            $genelCozulen += $cozulen;
        }

        $this->info("\nOZET: {$genelToplam} personelsiz kalemin {$genelCozulen} tanesi cozuldu"
            . ($uygula ? ' ve yazildi.' : ' (DRY-RUN — yazilmadi).'));
        $this->line('Kontrol: php artisan prim:teshis --salon=' . $salonId);
        return 0;
    }

    /**
     * Bir adisyonda personelli kalem varsa o personeli dondur (ayni adisyondaki
     * personelsiz kalemler ayni kisiye ait kabul edilir).
     */
    private function adisyonPersonelHaritasi($adIds)
    {
        $harita = [];
        foreach ($this->tablolar as $bilgi) {
            $tablo = $bilgi[0];
            if (!\Schema::hasTable($tablo) || !\Schema::hasColumn($tablo, 'personel_id')) continue;
            foreach (array_chunk($adIds->all(), 2000) as $parca) {
                $rows = DB::table($tablo)->whereIn('adisyon_id', $parca)
                    ->whereNotNull('personel_id')->get(['adisyon_id', 'personel_id']);
                foreach ($rows as $r) {
                    if (!isset($harita[$r->adisyon_id])) $harita[$r->adisyon_id] = (int) $r->personel_id;
                }
            }
        }
        return $harita;
    }

    /**
     * user_id|hizmet_id -> [[tarih, personel_id], ...] (randevu_hizmetler uzerinden)
     */
    private function randevuHaritasi($salonId)
    {
        $harita = [];
        DB::table('randevu_hizmetler as rh')
            ->join('randevular as r', 'r.id', '=', 'rh.randevu_id')
            ->where('r.salon_id', $salonId)
            ->whereNotNull('rh.personel_id')
            ->select('r.user_id', 'rh.hizmet_id', 'rh.personel_id', 'r.tarih')
            ->orderBy('r.tarih')
            ->chunk(5000, function ($rows) use (&$harita) {
                foreach ($rows as $r) {
                    $harita[$r->user_id . '|' . $r->hizmet_id][] = [$r->tarih, (int) $r->personel_id];
                }
            });
        return $harita;
    }

    /** Adisyon tarihine en yakin randevunun personelini sec. */
    private function randevudanBul($harita, $userId, $hizmetId, $adisyonTarihi)
    {
        $key = $userId . '|' . $hizmetId;
        if (empty($harita[$key])) return null;
        $hedef = strtotime(substr((string) $adisyonTarihi, 0, 10));
        $enIyi = null; $enKucukFark = null;
        foreach ($harita[$key] as $kayit) {
            $fark = abs(strtotime(substr((string) $kayit[0], 0, 10)) - $hedef);
            if ($enKucukFark === null || $fark < $enKucukFark) {
                $enKucukFark = $fark; $enIyi = $kayit[1];
            }
        }
        return $enIyi;
    }
}
