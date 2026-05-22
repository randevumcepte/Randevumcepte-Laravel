<?php

namespace App\Services;

use App\MusteriDakikaPaketHareketi;
use App\MusteriDakikaPaketi;
use App\Randevular;
use App\RandevuHizmetler;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Dakika bazli paket satislari icin merkezi mantik.
 *
 * Solaryum gibi sure satilan hizmetlerde musteriye dakika havuzu acilir,
 * randevu "geldi" isaretlendiginde otomatik olarak duser. ApiController
 * (mobil) ve StoreAdminController (web admin) bu service'i cagirir.
 */
class MusteriDakikaPaketService
{
    /**
     * Yeni paket sat.
     *
     *  @param array $data salon_id, musteri_portfoy_id, hizmet_id, toplam_dakika,
     *                    satis_fiyati, bitis_tarihi?, notlar?
     */
    public function paketSat(array $data, ?int $userId = null, ?int $personelId = null): MusteriDakikaPaketi
    {
        $toplam = (int) ($data['toplam_dakika'] ?? 0);
        if ($toplam <= 0) {
            throw new \InvalidArgumentException('Toplam dakika 0 dan buyuk olmali');
        }

        $bitis = $data['bitis_tarihi'] ?? null;
        if ($bitis === '' || $bitis === '0000-00-00') $bitis = null;

        return MusteriDakikaPaketi::create([
            'salon_id'              => $data['salon_id'],
            'musteri_portfoy_id'    => $data['musteri_portfoy_id'],
            'hizmet_id'             => $data['hizmet_id'],
            'toplam_dakika'         => $toplam,
            'kalan_dakika'          => $toplam,
            'satis_fiyati'          => $data['satis_fiyati'] ?? 0,
            'satis_tarihi'          => $data['satis_tarihi'] ?? now()->toDateString(),
            'bitis_tarihi'          => $bitis,
            'durum'                 => 'aktif',
            'notlar'                => $data['notlar'] ?? null,
            'olusturan_user_id'     => $userId,
            'olusturan_personel_id' => $personelId,
        ]);
    }

    /**
     * Bir randevu "geldi" isaretlendiginde, randevu satirlarinda paket bagli
     * olanlardan otomatik dakika dus. Daha once dusulmusse tekrar dusmez.
     */
    public function randevuGeldiUygula(Randevular $randevu, ?int $userId = null, ?int $personelId = null): array
    {
        $sonuc = [];
        $rhler = RandevuHizmetler::where('randevu_id', $randevu->id)
            ->whereNotNull('musteri_dakika_paketi_id')
            ->get();

        foreach ($rhler as $rh) {
            $varMi = MusteriDakikaPaketHareketi::where('randevu_hizmet_id', $rh->id)
                ->where('tur', 'randevu_kullanim')
                ->exists();
            if ($varMi) continue;

            $dakika = (int) ($rh->paket_dakika ?? $rh->sure_dk ?? 0);
            if ($dakika <= 0) continue;

            try {
                $this->bakiyeDus(
                    $rh->musteri_dakika_paketi_id,
                    $dakika,
                    'randevu_kullanim',
                    $randevu->id,
                    $rh->id,
                    null,
                    $userId,
                    $personelId
                );
                $sonuc[] = ['rh_id' => $rh->id, 'dakika' => $dakika];
            } catch (\Throwable $e) {
                Log::warning('[DAKIKA-PAKETI] randevu geldi dusum hata', [
                    'randevu_id' => $randevu->id,
                    'rh_id'      => $rh->id,
                    'err'        => $e->getMessage(),
                ]);
            }
        }

        return $sonuc;
    }

    /**
     * "Geldi" geri alindiginda daha onceki randevu_kullanim hareketlerini
     * tersine cevirir (iade hareketi yazip bakiyeye geri yukler).
     */
    public function randevuGelmediIade(Randevular $randevu, ?int $userId = null, ?int $personelId = null): array
    {
        $sonuc = [];
        $hareketler = MusteriDakikaPaketHareketi::where('randevu_id', $randevu->id)
            ->where('tur', 'randevu_kullanim')
            ->get();

        foreach ($hareketler as $h) {
            // Bu hareket icin iade var mi?
            $iadeVar = MusteriDakikaPaketHareketi::where('randevu_hizmet_id', $h->randevu_hizmet_id)
                ->where('tur', 'iade')
                ->exists();
            if ($iadeVar) continue;

            try {
                DB::transaction(function () use ($h, $userId, $personelId) {
                    $paket = MusteriDakikaPaketi::lockForUpdate()->find($h->musteri_dakika_paketi_id);
                    if (!$paket) return;
                    $paket->kalan_dakika = $paket->kalan_dakika + abs($h->dakika);
                    if ($paket->kalan_dakika > 0 && $paket->durum === 'bitti') {
                        $paket->durum = 'aktif';
                    }
                    $paket->save();

                    MusteriDakikaPaketHareketi::create([
                        'musteri_dakika_paketi_id' => $h->musteri_dakika_paketi_id,
                        'randevu_id'               => $h->randevu_id,
                        'randevu_hizmet_id'        => $h->randevu_hizmet_id,
                        'dakika'                   => -abs($h->dakika),
                        'tur'                      => 'iade',
                        'tarih'                    => now(),
                        'aciklama'                 => 'Randevu geldi isareti geri alindi',
                        'olusturan_user_id'        => $userId,
                        'olusturan_personel_id'    => $personelId,
                    ]);
                });
                $sonuc[] = ['hareket_id' => $h->id, 'dakika' => abs($h->dakika)];
            } catch (\Throwable $e) {
                Log::warning('[DAKIKA-PAKETI] iade hata', [
                    'hareket_id' => $h->id,
                    'err' => $e->getMessage(),
                ]);
            }
        }

        return $sonuc;
    }

    /**
     * Manuel kullanim (randevusuz dusum).
     */
    public function manuelKullanim(int $paketId, int $dakika, ?string $aciklama, ?int $userId = null, ?int $personelId = null): MusteriDakikaPaketHareketi
    {
        if ($dakika <= 0) {
            throw new \InvalidArgumentException('Dakika 0 dan buyuk olmali');
        }
        return $this->bakiyeDus($paketId, $dakika, 'manuel_kullanim', null, null, $aciklama, $userId, $personelId);
    }

    /**
     * +/- duzeltme. Pozitif = bakiyeden dus, negatif = bakiyeye ekle.
     */
    public function duzeltme(int $paketId, int $dakika, ?string $aciklama, ?int $userId = null, ?int $personelId = null): MusteriDakikaPaketHareketi
    {
        if ($dakika === 0) {
            throw new \InvalidArgumentException('Dakika 0 olamaz');
        }
        return DB::transaction(function () use ($paketId, $dakika, $aciklama, $userId, $personelId) {
            $paket = MusteriDakikaPaketi::lockForUpdate()->findOrFail($paketId);
            $yeniKalan = $paket->kalan_dakika - $dakika;
            if ($yeniKalan < 0) {
                throw new \InvalidArgumentException('Bakiye yetersiz (kalan: ' . $paket->kalan_dakika . ')');
            }
            $paket->kalan_dakika = $yeniKalan;
            if ($yeniKalan === 0 && $paket->durum === 'aktif') $paket->durum = 'bitti';
            if ($yeniKalan > 0 && $paket->durum === 'bitti')  $paket->durum = 'aktif';
            $paket->save();

            return MusteriDakikaPaketHareketi::create([
                'musteri_dakika_paketi_id' => $paketId,
                'dakika'                   => $dakika,
                'tur'                      => 'duzeltme',
                'tarih'                    => now(),
                'aciklama'                 => $aciklama,
                'olusturan_user_id'        => $userId,
                'olusturan_personel_id'    => $personelId,
            ]);
        });
    }

    /**
     * Paketi iptal et (bakiyesi sifira inse de hareket gecmisi durur).
     */
    public function paketIptal(int $paketId, ?string $aciklama, ?int $userId = null, ?int $personelId = null): MusteriDakikaPaketi
    {
        return DB::transaction(function () use ($paketId, $aciklama, $userId, $personelId) {
            $paket = MusteriDakikaPaketi::lockForUpdate()->findOrFail($paketId);
            if ($paket->durum === 'iptal') return $paket;

            $kalan = $paket->kalan_dakika;
            $paket->durum = 'iptal';
            $paket->save();

            if ($kalan > 0) {
                MusteriDakikaPaketHareketi::create([
                    'musteri_dakika_paketi_id' => $paketId,
                    'dakika'                   => $kalan,
                    'tur'                      => 'duzeltme',
                    'tarih'                    => now(),
                    'aciklama'                 => 'Paket iptal: kalan ' . $kalan . ' dk silindi' . ($aciklama ? ' - ' . $aciklama : ''),
                    'olusturan_user_id'        => $userId,
                    'olusturan_personel_id'    => $personelId,
                ]);
            }

            return $paket;
        });
    }

    /**
     * Bir randevunun hizmet satirlari icin musterinin uygun dakika paketleri.
     * "Geldi" sonrasi popup'ta gosterilecek liste:
     *   - randevu_hizmetler.musteri_dakika_paketi_id NULL ise sorulur,
     *     dolusa zaten bir paket secilmis (popupa eklenmez).
     *   - Musterinin o hizmete ait aktif paketi yoksa atlanir.
     */
    public function randevuIcinUygunPaketler(int $randevuId): array
    {
        $randevu = \App\Randevular::find($randevuId);
        if (!$randevu) return [];

        $musteriPortfoy = \App\MusteriPortfoy::where('user_id', $randevu->user_id)
            ->where('salon_id', $randevu->salon_id)
            ->first();
        if (!$musteriPortfoy) return [];

        $rhler = \App\RandevuHizmetler::where('randevu_id', $randevuId)
            ->whereNull('musteri_dakika_paketi_id')
            ->with('hizmetler')
            ->get();

        $sonuc = [];
        foreach ($rhler as $rh) {
            if (!$rh->hizmet_id) continue;
            $paketler = MusteriDakikaPaketi::where('musteri_portfoy_id', $musteriPortfoy->id)
                ->where('hizmet_id', $rh->hizmet_id)
                ->where('durum', 'aktif')
                ->where('kalan_dakika', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('bitis_tarihi')->orWhere('bitis_tarihi', '>=', now()->toDateString());
                })
                ->orderBy('satis_tarihi')
                ->orderBy('id')
                ->get();
            if ($paketler->isEmpty()) continue;

            $sonuc[] = [
                'rh_id'        => (int) $rh->id,
                'hizmet_id'    => (int) $rh->hizmet_id,
                'hizmet_adi'   => $rh->hizmetler->hizmet_adi ?? '',
                'sure_dk'      => (int) ($rh->sure_dk ?? 0),
                'paketler'     => $paketler->map(function ($p) {
                    return [
                        'id'             => (int) $p->id,
                        'toplam_dakika'  => (int) $p->toplam_dakika,
                        'kalan_dakika'   => (int) $p->kalan_dakika,
                        'satis_tarihi'   => $p->satis_tarihi ? $p->satis_tarihi->format('Y-m-d') : null,
                        'bitis_tarihi'   => $p->bitis_tarihi ? $p->bitis_tarihi->format('Y-m-d') : null,
                    ];
                })->values()->all(),
            ];
        }
        return $sonuc;
    }

    /**
     * Randevu icin toplu paket kullanim kaydi:
     * Her satir icin randevu_hizmetler'e paket bagla; randevu zaten geldi
     * isaretlendiyse direkt bakiyeden dus. Geldi false ise sadece baglar,
     * sonradan "Geldi" tiklayinca hook otomatik duser.
     *
     *  @param array $kullanimlar  [{rh_id, paket_id, dakika}, ...]
     */
    public function randevuIcinKullanimKaydet(int $randevuId, array $kullanimlar, ?int $userId = null, ?int $personelId = null): array
    {
        $randevu = \App\Randevular::findOrFail($randevuId);
        $geldiMi = (bool) $randevu->randevuya_geldi;
        $sonuc = [];

        foreach ($kullanimlar as $k) {
            $rhId = (int) ($k['rh_id'] ?? 0);
            $paketId = (int) ($k['paket_id'] ?? 0);
            $dakika = (int) ($k['dakika'] ?? 0);
            if ($rhId <= 0 || $paketId <= 0 || $dakika <= 0) continue;

            $rh = \App\RandevuHizmetler::find($rhId);
            if (!$rh || $rh->randevu_id != $randevuId) continue;

            $paket = MusteriDakikaPaketi::find($paketId);
            if (!$paket || $paket->durum !== 'aktif') continue;
            if ($paket->hizmet_id != $rh->hizmet_id) continue;
            if ($paket->kalan_dakika < $dakika) {
                $sonuc[] = ['rh_id' => $rhId, 'hata' => 'Yetersiz bakiye (' . $paket->kalan_dakika . ')'];
                continue;
            }

            $rh->musteri_dakika_paketi_id = $paketId;
            $rh->paket_dakika = $dakika;
            $rh->save();

            if ($geldiMi) {
                // Geldi zaten true: direkt dus (cunku hook artik tetiklenmeyecek)
                $varMi = MusteriDakikaPaketHareketi::where('randevu_hizmet_id', $rh->id)
                    ->where('tur', 'randevu_kullanim')
                    ->exists();
                if (!$varMi) {
                    try {
                        $this->bakiyeDus(
                            $paketId, $dakika, 'randevu_kullanim',
                            $randevu->id, $rh->id, null, $userId, $personelId
                        );
                        $sonuc[] = ['rh_id' => $rhId, 'dakika' => $dakika, 'dusuldu' => true];
                    } catch (\Throwable $e) {
                        $sonuc[] = ['rh_id' => $rhId, 'hata' => $e->getMessage()];
                    }
                } else {
                    $sonuc[] = ['rh_id' => $rhId, 'dakika' => $dakika, 'dusuldu' => 'mevcut'];
                }
            } else {
                $sonuc[] = ['rh_id' => $rhId, 'dakika' => $dakika, 'baglandi' => true];
            }
        }
        return $sonuc;
    }

    /**
     * Belirli hizmet icin musterinin kullanilabilir paketini bul (FIFO).
     * Aktif + bitis tarihi gecmemis + yeterli bakiyesi olan ilk paket.
     */
    public function uygunPaketBul(int $musteriPortfoyId, int $hizmetId, int $gerekenDakika = 1): ?MusteriDakikaPaketi
    {
        return MusteriDakikaPaketi::where('musteri_portfoy_id', $musteriPortfoyId)
            ->where('hizmet_id', $hizmetId)
            ->where('durum', 'aktif')
            ->where('kalan_dakika', '>=', $gerekenDakika)
            ->where(function ($q) {
                $q->whereNull('bitis_tarihi')->orWhere('bitis_tarihi', '>=', now()->toDateString());
            })
            ->orderBy('satis_tarihi')
            ->orderBy('id')
            ->first();
    }

    // --- internal ---

    protected function bakiyeDus(
        int $paketId,
        int $dakika,
        string $tur,
        ?int $randevuId,
        ?int $rhId,
        ?string $aciklama,
        ?int $userId,
        ?int $personelId
    ): MusteriDakikaPaketHareketi {
        return DB::transaction(function () use ($paketId, $dakika, $tur, $randevuId, $rhId, $aciklama, $userId, $personelId) {
            $paket = MusteriDakikaPaketi::lockForUpdate()->findOrFail($paketId);
            if ($paket->durum !== 'aktif') {
                throw new \InvalidArgumentException('Paket aktif degil (durum: ' . $paket->durum . ')');
            }
            if ($paket->kalan_dakika < $dakika) {
                throw new \InvalidArgumentException('Yetersiz bakiye (kalan: ' . $paket->kalan_dakika . ', istenen: ' . $dakika . ')');
            }
            $paket->kalan_dakika -= $dakika;
            if ($paket->kalan_dakika === 0) $paket->durum = 'bitti';
            $paket->save();

            return MusteriDakikaPaketHareketi::create([
                'musteri_dakika_paketi_id' => $paketId,
                'randevu_id'               => $randevuId,
                'randevu_hizmet_id'        => $rhId,
                'dakika'                   => $dakika,
                'tur'                      => $tur,
                'tarih'                    => now(),
                'aciklama'                 => $aciklama,
                'olusturan_user_id'        => $userId,
                'olusturan_personel_id'    => $personelId,
            ]);
        });
    }
}
