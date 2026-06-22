<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Randevular;
use App\Salonlar;
use App\SalonSMSAyarlari;

/**
 * Bir musterinin belirli bir tarihteki randevularina hatirlatma gidip
 * gitmediginin sebebini tek tek analiz eder.
 *
 * Kullanim:
 *   php artisan hatirlatma:neden-gitmedi 376 164988
 *   php artisan hatirlatma:neden-gitmedi 376 164988 --tarih=2026-06-23
 *
 * RandevuSMSHatirlatma cron'unun her filtresini ve tetik sartini
 * randevu randevu kontrol eder, ayni zamanda whatsapp_gonderim_loglari'na
 * bakip o randevu icin gercekten gonderim yapilmis mi gosterir.
 */
class HatirlatmaNedenGitmedi extends Command
{
    protected $signature = 'hatirlatma:neden-gitmedi
        {salonId : Salon ID}
        {userId : Musteri user_id}
        {--tarih= : YYYY-MM-DD (default: yarin)}';
    protected $description = 'Bir musterinin randevularina hatirlatma gidip gitmediginin sebebini analiz eder';

    public function handle()
    {
        $salonId = (int) $this->argument('salonId');
        $userId = (int) $this->argument('userId');
        $tarih = $this->option('tarih') ?: date('Y-m-d', strtotime('+1 day'));

        $salon = Salonlar::find($salonId);
        if (!$salon) {
            $this->error("Salon yok: id={$salonId}");
            return 1;
        }

        $this->info("=== Hatirlatma Teshis ===");
        $this->line("Salon  : #{$salon->id} {$salon->salon_adi}");
        $this->line("Musteri: user_id={$userId}");
        $this->line("Tarih  : {$tarih}");
        $this->line("Salon X-saat-once hatirlatma  : " . (int) $salon->randevu_sms_hatirlatma . ' saat');
        $this->line('');

        // Cron filtreleri (RandevuSMSHatirlatma.handle ile birebir ayni)
        $base = Randevular::with(['hizmetler.hizmetler', 'users', 'salonlar'])
            ->where('user_id', $userId)
            ->where('salon_id', $salonId)
            ->where('tarih', $tarih)
            ->orderBy('saat')
            ->get();

        if ($base->isEmpty()) {
            $this->warn("Bu musterinin {$tarih} tarihinde {$salonId} salonunda hic randevusu yok.");
            return 0;
        }

        $this->info("Bulunan randevular: " . $base->count());
        $this->line('');

        // SMS ayarlari
        $ayarYaklasan = SalonSMSAyarlari::where('salon_id', $salonId)->where('ayar_id', 1)->first();
        $ayar1Gun = SalonSMSAyarlari::where('salon_id', $salonId)->where('ayar_id', 6)->first();
        $this->line('SMS ayar X-saat-once (ayar_id=1) musteri toggle : '
            . (($ayarYaklasan && $ayarYaklasan->musteri) ? 'ACIK' : 'KAPALI'));
        $this->line('SMS ayar 1-gun-once  (ayar_id=6) musteri toggle : '
            . (($ayar1Gun && $ayar1Gun->musteri) ? 'ACIK' : 'KAPALI'));
        $this->line('');

        // Cron'un BIREBIR ayni query'sini simule et — bu musterinin randevulari icinde
        // kac tanesi cron tarafindan goruluyor?
        $this->info('CRON QUERY SIMULASYONU (RandevuSMSHatirlatma.handle ile birebir ayni):');
        $cronQuery = \App\Randevular::has('hizmetler')
            ->where('user_id', $userId)
            ->where('salon_id', $salonId)
            ->where('durum', 1)
            ->where('user_id', '!=', 2012)
            ->where(function ($q) {
                $q->where('randevuya_geldi', null);
                $q->orWhere('randevuya_geldi', '!=', 0);
            })
            ->whereBetween('tarih', [date('Y-m-d'), date('Y-m-d', strtotime('+1 days', strtotime(date('Y-m-d'))))]);

        // Generated SQL'i goster
        $sql = $cronQuery->toSql();
        $bindings = $cronQuery->getBindings();
        $this->line('  SQL: ' . $sql);
        $this->line('  Bind: ' . json_encode($bindings));

        $cronSonuc = $cronQuery->get();
        $this->line('  Cron query sonucundaki randevu sayisi: ' . $cronSonuc->count());
        if ($cronSonuc->count() > 0) {
            $this->line('  ID listesi: ' . $cronSonuc->pluck('id')->implode(', '));
        }

        $tumIdler = $base->pluck('id')->all();
        $cronIdler = $cronSonuc->pluck('id')->all();
        $eksik = array_diff($tumIdler, $cronIdler);
        if (!empty($eksik)) {
            $this->warn('  ! Cron query DISINDA kalan randevular: ' . implode(', ', $eksik));
            $this->warn('  ! Bu randevular cron tarafindan hic gorulmedigi icin hatirlatma denenmedi.');
        } else {
            $this->line('  -> Tum randevular cron query sonucunda var.');
        }
        $this->line('');

        // Raw tarih kolonunu ham SQL ile incele — DATETIME vs DATE farkini yakalamak icin
        $hamTarih = \Illuminate\Support\Facades\DB::select(
            'SELECT id, tarih, CAST(tarih AS CHAR) tarih_str, DATE(tarih) tarih_date, saat
             FROM randevular WHERE id IN (' . implode(',', $tumIdler) . ')'
        );
        $this->info('HAM TARIH KOLONU (DATETIME vs DATE ayirimi icin):');
        foreach ($hamTarih as $h) {
            $this->line("  #{$h->id} tarih={$h->tarih}  str='{$h->tarih_str}'  date={$h->tarih_date}  saat={$h->saat}");
        }
        $this->line('');

        $hatirlatmaSaat = (int) ($salon->randevu_sms_hatirlatma ?? 0);
        $simdi = time();

        foreach ($base as $r) {
            $this->line("--- Randevu #{$r->id} saat={$r->saat} ---");

            // 1) Cron query filtreleri
            $filterDurum = ((int) $r->durum) === 1;
            $filterInternalUser = ((int) $r->user_id) !== 2012;
            $filterGeldi = is_null($r->randevuya_geldi) || ((int) $r->randevuya_geldi) !== 0;
            $filterHizmet = $r->hizmetler->count() > 0;
            $filterSalon = $r->salon_id && $r->salonlar;

            $this->line("  Filtreler:");
            $this->line("    durum=1                         : " . ($filterDurum ? 'OK' : "FAIL (durum={$r->durum})"));
            $this->line("    user_id != 2012                 : " . ($filterInternalUser ? 'OK' : 'FAIL'));
            $this->line("    randevuya_geldi null|!=0        : " . ($filterGeldi ? 'OK' : "FAIL (geldi={$r->randevuya_geldi})"));
            $this->line("    has hizmetler                   : " . ($filterHizmet ? "OK ({$r->hizmetler->count()} hizmet)" : 'FAIL'));
            $this->line("    salon iliskisi var              : " . ($filterSalon ? 'OK' : 'FAIL'));

            $filtreGecti = $filterDurum && $filterInternalUser && $filterGeldi && $filterHizmet && $filterSalon;
            if (!$filtreGecti) {
                $this->warn("  -> Bu randevu cron filtrelerini gecmedi, hatirlatma DENENMEDI bile.");
            }

            // 2) Tetik dakikasi
            $randevuTs = strtotime($r->tarih . ' ' . $r->saat);
            $this->line("  Randevu zamani                   : " . date('d.m.Y H:i', $randevuTs));
            if ($hatirlatmaSaat > 0) {
                $tetikTs = strtotime("-{$hatirlatmaSaat} hours", $randevuTs);
                $this->line("  X-saat-once tetik dakikasi       : " . date('d.m.Y H:i', $tetikTs));
                if ($tetikTs > $simdi) {
                    $kalan = $tetikTs - $simdi;
                    $this->line("  -> Tetik henuz gelmedi (" . round($kalan / 60) . ' dk sonra)');
                } elseif ($randevuTs < $simdi) {
                    $this->line("  -> Tetik gecmis, randevu da gecmis.");
                } else {
                    $this->line("  -> Tetik dakikasi gecti (" . round(($simdi - $tetikTs) / 60) . ' dk once)');
                }
            }

            // 3) 1 gun once flag
            if (Schema::hasColumn('randevular', 'hatirlatma_gunonce_gonderildi')) {
                $g = $r->hatirlatma_gunonce_gonderildi ?? null;
                $this->line("  hatirlatma_gunonce_gonderildi   : " . ($g ?: 'BOS'));
            }

            // 4) whatsapp_gonderim_loglari'nda bu randevu icin kayit
            if (Schema::hasTable('whatsapp_gonderim_loglari')) {
                $loglar = DB::table('whatsapp_gonderim_loglari')
                    ->where('randevu_id', $r->id)
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get(['id', 'durum', 'hata', 'gonderim_tarihi', 'created_at', 'telefon']);
                if ($loglar->isEmpty()) {
                    $this->warn("  WA gonderim logu: BOS — bu randevu icin hicbir WA denemesi yapilmamis.");
                } else {
                    $this->line("  WA gonderim loglari (son " . $loglar->count() . "):");
                    foreach ($loglar as $l) {
                        $durEtiket = ['0' => 'KUY', '1' => 'OK', '2' => 'FAIL', '3' => 'SMS_FBK'][(string) $l->durum] ?? '?';
                        $this->line("    #{$l->id} {$durEtiket} tel={$l->telefon} created={$l->created_at}" . ($l->hata ? " HATA: {$l->hata}" : ''));
                    }
                }
            }

            // 5) Bildirimler tablosunda bu randevu icin kayit (personel/yonetici taraf)
            if (Schema::hasTable('bildirimler')) {
                $bildirimSayisi = DB::table('bildirimler')->where('randevu_id', $r->id)->count();
                $this->line("  bildirimler tablosu kayit sayisi : {$bildirimSayisi}");
            }

            $this->line('');
        }

        return 0;
    }
}
