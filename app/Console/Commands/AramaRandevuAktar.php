<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\AramaListesi;
use App\AranacakMusteriler;

/**
 * Bir isletmenin "Arama Randevusu" HIZMETLI normal randevularini, Cagri Merkezi
 * tarafindaki ARAMA RANDEVUSU (callback) kayitlarina (aranacak_musteriler.durum=3)
 * aktarir.
 *
 * Arka plan: 195 gibi bazi isletmeler, cagri merkezi callback'lerini normal randevu
 * takvimine "Arama Randevusu" adli bir hizmetle giriyordu. Bu komut onlari asil
 * arama-randevusu sistemine tasir (tarih + saat korunur), boylece "Arama Randevularim"
 * ekraninda listelenir ve dogrudan aranabilir.
 *
 * GUVENLI: Varsayilan DRY-RUN (sadece raporlar). Yazmak icin --apply gerekir.
 * IDEMPOTENT: Ayni musteri + tarih + saat icin salonda zaten durum=3 kayit varsa atlar;
 * tekrar calistirmak kopya olusturmaz.
 *
 * Kullanim (canli: /opt/php74/bin/php artisan ...):
 *   arama-randevu:aktar                 -> 195 icin dry-run (onizleme)
 *   arama-randevu:aktar --apply         -> 195 icin uygula
 *   arama-randevu:aktar --salon=195 --apply
 *   arama-randevu:aktar --gecmis --apply-> gecmis tarihli randevulari da dahil et
 */
class AramaRandevuAktar extends Command
{
    protected $signature = 'arama-randevu:aktar
        {--salon=195 : Hangi isletme (salon_id)}
        {--apply : Gercekten yaz (varsayilan dry-run)}
        {--gecmis : Gecmis tarihli randevulari da aktar (varsayilan: sadece bugun ve sonrasi)}
        {--pattern=arama randev : Hizmet adinda aranacak metin (kucuk harf)}';

    protected $description = "Isletmenin 'Arama Randevusu' hizmetli randevularini arama randevusu (callback) olarak aktarir";

    public function handle()
    {
        $salon   = (int) $this->option('salon');
        $apply   = (bool) $this->option('apply');
        $gecmis  = (bool) $this->option('gecmis');
        $pattern = mb_strtolower(trim((string) $this->option('pattern')));

        if ($salon <= 0) { $this->error('Gecersiz salon.'); return 1; }
        if ($pattern === '') { $this->error('Gecersiz pattern.'); return 1; }

        $bugun = date('Y-m-d');
        $this->info(($apply ? '[UYGULA]' : '[DRY-RUN]')
            . " salon=$salon  pattern='$pattern'  kapsam=" . ($gecmis ? 'TUM tarihler' : "bugun ve sonrasi ($bugun+)"));

        // --- Kaynak: "arama randev" hizmetli randevular (iptal/kabul edilmedi haric) ---
        $q = DB::table('randevular as r')
            ->join('randevu_hizmetler as rh', 'rh.randevu_id', '=', 'r.id')
            ->join('hizmetler as h', 'h.id', '=', 'rh.hizmet_id')
            ->where('r.salon_id', $salon)
            ->whereRaw('LOWER(h.hizmet_adi) LIKE ?', ['%' . $pattern . '%'])
            // IPTAL kurali: bu isletmede randevular.durum > 2 ise iptal sayilir.
            // Iptal olanlari aktarmayiz; durum <= 2 (veya NULL/beklemede) dikkate alinir.
            ->where(function ($w) {
                $w->where('r.durum', '<=', 2)->orWhereNull('r.durum');
            })
            ->whereNotNull('r.user_id');
        if (!$gecmis) {
            $q->where('r.tarih', '>=', $bugun);
        }
        $rows = $q->select(
            'r.id as randevu_id', 'r.user_id', 'r.tarih',
            'r.saat as r_saat', 'rh.saat as rh_saat', 'rh.personel_id'
        )->orderBy('r.tarih')->orderBy('r.saat')->get();

        // Randevu basina TEK kayit (ayni randevuda birden fazla arama-randev kalemi olabilir)
        $benzersiz = [];
        foreach ($rows as $r) {
            if (!isset($benzersiz[$r->randevu_id])) $benzersiz[$r->randevu_id] = $r;
        }
        $kaynak = array_values($benzersiz);

        if (empty($kaynak)) {
            $this->warn('Aktarilacak uygun randevu bulunamadi.');
            return 0;
        }
        $this->info('Bulunan uygun randevu: ' . count($kaynak));

        // --- Idempotentlik: salonda ZATEN var olan callback'ler (user|tarih|saat) ---
        $salonListeIds = AramaListesi::where('salon_id', $salon)->pluck('id')->all();
        $mevcut = [];
        if ($salonListeIds) {
            DB::table('aranacak_musteriler')
                ->whereIn('arama_id', $salonListeIds)
                ->where('durum', 3)
                ->select('user_id', 'tarih', 'saat')
                ->orderBy('id')
                ->chunk(2000, function ($ch) use (&$mevcut) {
                    foreach ($ch as $e) {
                        $mevcut[$e->user_id . '|' . $e->tarih . '|' . substr((string) $e->saat, 0, 5)] = true;
                    }
                });
        }

        $arKolonVar = Schema::hasColumn('aranacak_musteriler', 'ar_5dk_at');
        $now = date('Y-m-d H:i:s');

        // Personel -> aktarilacak kayitlar
        $gruplar = [];
        $atlanan = 0;
        foreach ($kaynak as $r) {
            $tarih = $r->tarih;
            $saat  = substr((string) ($r->r_saat ?: $r->rh_saat), 0, 5);
            if ($tarih === '' || $tarih === null) { $atlanan++; continue; }
            $key = $r->user_id . '|' . $tarih . '|' . $saat;
            if (isset($mevcut[$key])) { $atlanan++; continue; } // zaten var
            $mevcut[$key] = true; // ayni calistirmada ikinci kez eklenmesin
            $pid = $r->personel_id ? (int) $r->personel_id : 0;
            $gruplar[$pid][] = ['user_id' => (int) $r->user_id, 'tarih' => $tarih, 'saat' => $saat];
        }

        $toplamYeni = array_sum(array_map('count', $gruplar));
        $this->info("Zaten mevcut/atlanan: $atlanan   Aktarilacak YENI: $toplamYeni");

        if ($toplamYeni === 0) {
            $this->info('Yapilacak yeni aktarim yok.');
            return 0;
        }

        // Personel isimleri (ozet tablo icin)
        $pidler = array_filter(array_keys($gruplar));
        $isimler = $pidler
            ? \App\Personeller::whereIn('id', $pidler)->pluck('personel_adi', 'id')->toArray()
            : [];

        // Ozet tablo
        $this->table(
            ['Personel ID', 'Personel', 'Yeni kayit'],
            array_map(function ($pid, $items) use ($isimler) {
                $ad = $pid === 0 ? '(atanmamis)' : ($isimler[$pid] ?? '(isim yok)');
                return [$pid === 0 ? '(atanmamis)' : $pid, $ad, count($items)];
            }, array_keys($gruplar), $gruplar)
        );

        if (!$apply) {
            $this->warn('DRY-RUN: hicbir sey yazilmadi. Uygulamak icin --apply ekleyin.');
            return 0;
        }

        // --- Uygula ---
        $yazilan = 0;
        foreach ($gruplar as $pid => $items) {
            $liste = $this->listeBulVeyaOlustur($salon, $pid, $now);
            $satirlar = [];
            foreach ($items as $it) {
                $satir = [
                    'user_id'    => $it['user_id'],
                    'arama_id'   => $liste->id,
                    'durum'      => 3,          // Arama Randevusu (callback)
                    'tarih'      => $it['tarih'],
                    'saat'       => $it['saat'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if ($arKolonVar) {
                    $satir['ar_5dk_at'] = null;
                    $satir['ar_zaman_at'] = null;
                    $satir['ar_gecikti'] = 0;
                    $satir['ar_tamamlandi_at'] = null;
                }
                $satirlar[] = $satir;
            }
            foreach (array_chunk($satirlar, 500) as $parca) {
                AranacakMusteriler::insert($parca);
                $yazilan += count($parca);
            }
            $pAd = $pid > 0 ? ($isimler[$pid] ?? '(isim yok)') : 'atanmamis';
            $this->line("  Liste #{$liste->id} ('{$liste->arama_baslik}', personel=" . ($pid ?: 'atanmamis') . " {$pAd}): " . count($items) . ' kayit');
        }

        $this->info("TAMAM. Toplam yazilan arama randevusu: $yazilan");
        return 0;
    }

    /** Salon + personel icin aktarim listesini bulur, yoksa olusturur. */
    private function listeBulVeyaOlustur($salon, $pid, $now)
    {
        $baslik = $pid > 0
            ? 'Arama Randevuları (Aktarılan)'
            : 'Arama Randevuları (Aktarılan - Atanmamış)';

        $sorgu = AramaListesi::where('salon_id', $salon)->where('arama_baslik', $baslik);
        if ($pid > 0) { $sorgu->where('personel_id', $pid); } else { $sorgu->whereNull('personel_id'); }
        $liste = $sorgu->first();
        if ($liste) return $liste;

        $liste = new AramaListesi();
        $liste->salon_id = $salon;
        $liste->arama_baslik = $baslik;
        $liste->personel_id = $pid > 0 ? $pid : null;
        $liste->aranacak_tarih = null;
        $liste->durum = 1;
        if (Schema::hasColumn('arama_listesi', 'filtre_snapshot')) {
            $liste->filtre_snapshot = null;
        }
        $liste->save();
        return $liste;
    }
}
