<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Tahsilat/fiyat KÜRÜŞ RECONCILE — geçmiş verideki float küsürat artıklarını temizler.
 *
 * Arka plan: eski kodda orantısal tahsilat dağıtımı round() yapmadan yazıyordu
 * (tahsilat_*.tutar float birikiyordu) ve bu float, pakettahsilattutaridegistir gibi
 * akışlarda adisyon_*.fiyat alanına da sızıyordu (ör. 43.999,94 GELİR). Yeni kod bunu
 * kaynağında düzeltti; bu komut MEVCUT satırları 2 haneye sabitler.
 *
 * Yalnızca zaten 2 haneden farklı (float artıklı) satırlara dokunur; idempotenttir.
 *
 * Kullanım (önce KURU çalıştır, sayıları gör):
 *   /opt/php74/bin/php artisan kurus:reconcile 401
 *   /opt/php74/bin/php artisan kurus:reconcile 401 --uygula
 *   /opt/php74/bin/php artisan kurus:reconcile --uygula        (TÜM salonlar)
 */
class KurusReconcile extends Command
{
    protected $signature = 'kurus:reconcile {salon? : Salon ID (bos = tum salonlar)} {--uygula : Degisiklikleri yaz (yoksa kuru calisir)}';
    protected $description = 'Gecmis tahsilat_*.tutar ve adisyon_*.fiyat float kusuratini 2 haneye sabitler (reconcile)';

    public function handle()
    {
        $salonId = $this->argument('salon') ? (int) $this->argument('salon') : null;
        $uygula  = (bool) $this->option('uygula');

        $this->info('=== KURUŞ RECONCILE ===');
        $this->line($salonId ? "salon={$salonId}" : 'salon=TÜMÜ');
        $this->line($uygula ? 'mod=UYGULA (yazılacak)' : 'mod=KURU (sadece rapor; yazmak için --uygula)');
        $this->line('');

        // [tablo, tutar_kolonu, ilişki_kolonu, adisyon_tablosu, adisyon_ilişki_kolonu]
        // tahsilat_* -> adisyon_* -> adisyonlar.salon_id zinciriyle salon filtrelenir.
        $tahsilatlar = [
            ['tahsilat_hizmetler', 'tutar', 'adisyon_hizmet_id', 'adisyon_hizmetler'],
            ['tahsilat_urunler',   'tutar', 'adisyon_urun_id',   'adisyon_urunler'],
            ['tahsilat_paketler',  'tutar', 'adisyon_paket_id',  'adisyon_paketler'],
        ];
        $adisyonlar = [
            ['adisyon_hizmetler', ['fiyat', 'indirim_tutari']],
            ['adisyon_urunler',   ['fiyat', 'indirim_tutari']],
            ['adisyon_paketler',  ['fiyat', 'indirim_tutari']],
        ];

        $toplamEtkilenen = 0;

        // 1) tahsilat_*.tutar
        foreach ($tahsilatlar as [$tablo, $kolon, $iliskiKolon, $adisyonTablo]) {
            $q = DB::table($tablo)->whereRaw("$kolon <> ROUND($kolon, 2)");
            if ($salonId) {
                $altIds = DB::table($adisyonTablo)
                    ->join('adisyonlar', 'adisyonlar.id', '=', "$adisyonTablo.adisyon_id")
                    ->where('adisyonlar.salon_id', $salonId)
                    ->pluck("$adisyonTablo.id");
                $q->whereIn($iliskiKolon, $altIds);
            }
            $adet = (clone $q)->count();
            $toplamEtkilenen += $adet;
            $this->line(sprintf('%-20s %s : %d satır', $tablo, $kolon, $adet));
            if ($uygula && $adet > 0) {
                (clone $q)->update([$kolon => DB::raw("ROUND($kolon, 2)")]);
            }
        }

        // 2) adisyon_*.fiyat / indirim_tutari
        foreach ($adisyonlar as [$tablo, $kolonlar]) {
            foreach ($kolonlar as $kolon) {
                $q = DB::table($tablo)->whereRaw("$kolon <> ROUND($kolon, 2)");
                if ($salonId) {
                    $q->whereIn('adisyon_id', function ($sub) use ($salonId) {
                        $sub->select('id')->from('adisyonlar')->where('salon_id', $salonId);
                    });
                }
                $adet = (clone $q)->count();
                $toplamEtkilenen += $adet;
                $this->line(sprintf('%-20s %s : %d satır', $tablo, $kolon, $adet));
                if ($uygula && $adet > 0) {
                    (clone $q)->update([$kolon => DB::raw("ROUND($kolon, 2)")]);
                }
            }
        }

        $this->line('');
        if ($uygula) {
            $this->info("BİTTİ. Toplam {$toplamEtkilenen} satır 2 haneye sabitlendi.");
        } else {
            $this->warn("KURU çalışma: {$toplamEtkilenen} satır düzeltilecek. Yazmak için sonuna --uygula ekleyin.");
        }
        return 0;
    }
}
