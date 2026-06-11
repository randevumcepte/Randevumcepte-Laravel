<?php

namespace App\Console\Commands;

use App\Hizmet_Kategorisi;
use App\Hizmetler;
use App\SalonHizmetler;
use App\Salonlar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class GuzellikSablonKur extends Command
{
    protected $signature = 'hizmetler:guzellik-sablon-kur
        {--salon-turu=3 : Sablon salonun salon_turu_id degeri (guzellik = 3)}
        {--dry-run : Sadece raporla, kayit yapma}';

    protected $description = 'Guzellik varsayilan hizmetleri icin GIZLI bir sablon salon olusturur ve kuratorlu hizmet listesiyle doldurur. Idempotent — hicbir gercek salona dokunmaz.';

    /**
     * Kuratorlu varsayilan katalog: kategori => [hizmet adlari].
     * Gercek uretim verisinden (>=4 salonda kullanilan) turetildi. Sac DAHIL DEGIL (kuafor).
     */
    protected $katalog = [
        'Lazer Epilasyon' => [
            'Lazer Epilasyon (Tüm Vücut)',
            'Lazer Epilasyon (Koltuk Altı)',
            'Lazer Epilasyon (Genital)',
            'Lazer Epilasyon (Yüz)',
            'Lazer Epilasyon (Bacak)',
            'Lazer Epilasyon (Sırt)',
            'Lazer Epilasyon (Göbek)',
            'Lazer Epilasyon (Çene)',
            'Lazer Epilasyon (Dudak Üstü)',
            'Lazer Epilasyon (Kol)',
            'Lazer Epilasyon (Bikini)',
        ],
        'Tırnak' => [
            'Manikür',
            'Pedikür',
            'Protez Tırnak',
            'Kalıcı Oje (El)',
            'Kalıcı Oje (Ayak)',
            'Bakım Manikürü',
        ],
        'Kalıcı Makyaj' => [
            'Microblading',
            'Kaş Kontürü',
            'Dudak Renklendirme',
            'Kalıcı Eyeliner',
            'Pudralama',
            'Kaş Laminasyonu',
            'Gelin Makyajı',
        ],
        'Kirpik & Kaş' => [
            'İpek Kirpik',
            'Kirpik Lifting',
            'Kirpik Boyama',
            'Kaş Alma',
        ],
        'Cilt Bakımı' => [
            'Cilt Bakımı',
            'Dermapen',
            'Bölgesel İncelme',
        ],
        'Medikal' => [
            'Hydrafacial',
            'Mezoterapi',
            'PRP (Vampir Bakımı)',
            'Leke Tedavisi',
            'Cilt Gençleştirme',
            'Kimyasal Peeling',
            'Bölgesel Yağ / Lipoliz',
        ],
    ];

    public function handle()
    {
        $turId  = (int) $this->option('salon-turu');
        $dryRun = (bool) $this->option('dry-run');
        $sablonAd = config('varsayilanlar.guzellik_sablon_salon_adi');

        if (!$sablonAd) {
            $this->error('config/varsayilanlar.php icindeki guzellik_sablon_salon_adi bos.');
            return 1;
        }

        $this->info("Sablon salon adi: {$sablonAd}");
        $this->info("salon_turu_id: {$turId}");
        if ($dryRun) {
            $this->warn('DRY-RUN: hicbir kayit yapilmayacak.');
        }
        $this->line('');

        // 1) Gizli sablon salonu (firstOrCreate by salon_adi)
        $sablon = Salonlar::where('salon_adi', $sablonAd)->first();
        if (!$sablon) {
            if ($dryRun) {
                $this->line("- Sablon salon olusturulacak: {$sablonAd}");
            } else {
                $sablon = new Salonlar();
                $sablon->salon_adi            = $sablonAd;
                $sablon->salon_turu_id        = $turId;
                $sablon->adres                = '';
                $sablon->randevu_saat_araligi = 15;
                $sablon->randevu_takvim_turu  = 1;
                $sablon->uyelik_turu          = 3;
                $sablon->uyelik_bitis_tarihi  = '2000-01-01'; // suresi gecmis -> pasif
                $sablon->demo_hesabi          = true;
                if (Schema::hasColumn('salonlar', 'askiya_alindi')) {
                    $sablon->askiya_alindi = 1; // musteri tarafinda gizle
                }
                if (Schema::hasColumn('salonlar', 'hesap_acildi')) {
                    $sablon->hesap_acildi = 0;
                }
                $sablon->save();
                $this->line("- Sablon salon olusturuldu: {$sablonAd} (id={$sablon->id})");
            }
        } else {
            $this->line("- Sablon salon mevcut: {$sablonAd} (id={$sablon->id}, salon_turu_id={$sablon->salon_turu_id})");
        }

        $sablonId = $sablon ? $sablon->id : null; // dry-run'da salon yoksa null olabilir
        $hizmetTuruVar = Schema::hasColumn('hizmetler', 'salon_turu_id');
        $aktifVar = Schema::hasColumn('salon_sunulan_hizmetler', 'aktif');

        $yeniKategori = 0;
        $yeniHizmet   = 0;
        $yeniSablonH  = 0;

        // 2) Kategoriler + hizmetler + sablona ekleme
        foreach ($this->katalog as $kategoriAd => $hizmetAdlar) {
            // Kategori
            $kategori = Hizmet_Kategorisi::where('hizmet_kategorisi_adi', $kategoriAd)->first();
            if (!$kategori) {
                if ($dryRun) {
                    $this->line("  [kategori] olusturulacak: {$kategoriAd}");
                } else {
                    $kategori = new Hizmet_Kategorisi();
                    $kategori->hizmet_kategorisi_adi = $kategoriAd;
                    $kategori->save();
                    $yeniKategori++;
                    $this->line("  [kategori] olusturuldu: {$kategoriAd} (id={$kategori->id})");
                }
            } else {
                $this->line("  [kategori] mevcut: {$kategoriAd} (id={$kategori->id})");
            }
            $kategoriId = $kategori ? $kategori->id : null;

            foreach ($hizmetAdlar as $ad) {
                // Global hizmet katalogu
                $hizmet = Hizmetler::where('hizmet_adi', $ad)
                    ->when($kategoriId, function ($q) use ($kategoriId) {
                        $q->where('hizmet_kategori_id', $kategoriId);
                    })
                    ->first();
                if (!$hizmet) {
                    if ($dryRun) {
                        $this->line("      - hizmet olusturulacak: {$ad}");
                        continue; // dry-run'da id yok, sablon adimi atlanir
                    }
                    $hizmet = new Hizmetler();
                    $hizmet->hizmet_kategori_id = $kategoriId;
                    $hizmet->hizmet_adi = $ad;
                    $hizmet->fiyat = 0;
                    if ($hizmetTuruVar) {
                        $hizmet->salon_turu_id = $turId;
                    }
                    $hizmet->save();
                    $yeniHizmet++;
                }

                // Sablon salona ekle
                $zatenVar = $sablonId
                    ? SalonHizmetler::where('salon_id', $sablonId)->where('hizmet_id', $hizmet->id)->exists()
                    : false;
                if (!$zatenVar) {
                    if ($dryRun) {
                        $this->line("      -> sablona eklenecek: {$ad}");
                    } else {
                        $sh = new SalonHizmetler();
                        $sh->salon_id           = $sablonId;
                        $sh->hizmet_id          = $hizmet->id;
                        $sh->hizmet_kategori_id = $kategoriId;
                        $sh->baslangic_fiyat    = 0;
                        $sh->son_fiyat          = 0;
                        if ($aktifVar) {
                            $sh->aktif = 1;
                        }
                        $sh->save();
                        $yeniSablonH++;
                    }
                }
            }
        }

        $this->line('');
        if ($dryRun) {
            $this->info('DRY-RUN bitti. Yukaridakiler uygulanacak.');
        } else {
            // Mevcut sablon satirlarini da garanti aktif yap (onceki kurulumlar icin)
            if ($aktifVar) {
                SalonHizmetler::where('salon_id', $sablon->id)->update(['aktif' => 1]);
            }
            $toplam = SalonHizmetler::where('salon_id', $sablon->id)->count();
            $this->info("Bitti. Yeni: {$yeniKategori} kategori, {$yeniHizmet} hizmet, {$yeniSablonH} sablon hizmet.");
            $this->info("Sablon salon (id={$sablon->id}) toplam {$toplam} hizmet iceriyor.");
            $this->line('Artik yeni guzellik kayitlari bu listeyi otomatik alacak (config salon adindan cozer).');
        }
        return 0;
    }
}
