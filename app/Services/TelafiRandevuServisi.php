<?php

namespace App\Services;

use App\AdisyonHizmetler;
use App\PersonelPrimHareketi;
use App\Randevular;
use App\RandevuHizmetler;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Telafi randevusu: ucretsiz duzeltme/garanti randevusu olustur ve orijinal randevudaki
 * primi (hak edilmisse) yeni personele devret.
 *
 * Prim kurali:
 *  - Orijinal adisyon_hizmet icin HENUZ tahsilat yoksa (prim hak edilmemis): adisyon_hizmetler.personel_id'yi
 *    yeni personele cevir; prim tahakkuk ettiginde dogru personele yazilir.
 *  - Tahsilat varsa (prim hak edilmis): orijinal personele -tutar, yeni personele +tutar PersonelPrimHareketi
 *    kayitlari eklenir (gecmise dokunulmaz, net hak edis dengelenir).
 */
class TelafiRandevuServisi
{
    public function olustur(
        $orijinalRHId,
        $yeniTarih,
        $yeniSaat,
        $yeniPersonelId,
        $yeniOdaId,
        $yeniCihazId,
        $yeniSure,
        $not,
        $kullaniciId
    ) {
        $orig = RandevuHizmetler::with('randevu')->find($orijinalRHId);
        if (!$orig)         throw new \Exception('Orijinal randevu kaydı bulunamadı.');
        if (!$orig->randevu) throw new \Exception('Orijinal randevu silinmiş.');
        if ($orig->telafi_kaynak_rh_id) throw new \Exception('Bu randevu zaten bir telafi randevusu.');
        if ($orig->telafi_devir_yapildi) throw new \Exception('Bu randevu için zaten telafi oluşturulmuş.');

        $yeniPersonelId = (int) $yeniPersonelId ?: (int) $orig->personel_id;
        $yeniSure = (int) ($yeniSure ?: $orig->sure_dk ?: 15);
        $saatBitis = date('H:i', strtotime('+'.$yeniSure.' minutes', strtotime($yeniSaat)));

        DB::beginTransaction();
        try {
            // 1) Yeni randevu
            $yeniRandevu = new Randevular();
            $yeniRandevu->user_id   = $orig->randevu->user_id;
            $yeniRandevu->salon_id  = $orig->randevu->salon_id;
            $yeniRandevu->tarih     = $yeniTarih;
            $yeniRandevu->saat      = $yeniSaat;
            $yeniRandevu->durum     = 1;
            $yeniRandevu->salon     = true;
            $yeniRandevu->sms_hatirlatma = $orig->randevu->sms_hatirlatma;
            $yeniRandevu->olusturan_personel_id = $kullaniciId;
            $yeniRandevu->personel_notu = trim(($not ? $not.' ' : '').'[Telafi: orijinal #'.$orig->randevu_id.']');
            $yeniRandevu->save();

            // 2) Yeni RH — fiyat 0 (ucretsiz telafi), telafi_kaynak_rh_id ile orijinali isaretle
            $yeniRH = new RandevuHizmetler();
            $yeniRH->randevu_id  = $yeniRandevu->id;
            $yeniRH->hizmet_id   = $orig->hizmet_id;
            $yeniRH->personel_id = $yeniPersonelId;
            $yeniRH->cihaz_id    = $yeniCihazId ?: null;
            $yeniRH->oda_id      = $yeniOdaId ?: null;
            $yeniRH->sure_dk     = $yeniSure;
            $yeniRH->fiyat       = 0;
            $yeniRH->saat        = $yeniSaat;
            $yeniRH->saat_bitis  = $saatBitis;
            $yeniRH->telafi_kaynak_rh_id = $orig->id;
            $yeniRH->save();

            // 3) Prim transfer (personel degistiyse)
            $this->primTransfer($orig, $yeniRandevu, $yeniPersonelId, $kullaniciId);

            // Orijinali 'telafi devredildi' olarak isaretle (tekrar yapilmasin)
            $orig->telafi_devir_yapildi = 1;
            $orig->save();

            DB::commit();
            return $yeniRH;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /** Son prim transfer islem ozeti (olustur() dondurur). */
    public $sonTransferOzeti = ['ah'=>0, 'aps'=>0, 'hareket'=>0, 'adisyon_degisti'=>0, 'uyari'=>null];

    protected function primTransfer(RandevuHizmetler $origRH, Randevular $yeniRandevu, $yeniPersonelId, $kullaniciId)
    {
        $this->sonTransferOzeti = ['ah'=>0, 'aps'=>0, 'hareket'=>0, 'adisyon_degisti'=>0, 'uyari'=>null];
        $origPersonelId = (int) $origRH->personel_id;
        \Log::info('[Telafi primTransfer] baslangic', [
            'origRH_id' => $origRH->id,
            'orig_personel' => $origPersonelId,
            'yeni_personel' => $yeniPersonelId,
            'randevu_id' => $origRH->randevu_id,
            'hizmet_id'  => $origRH->hizmet_id,
        ]);
        if (!$yeniPersonelId || $yeniPersonelId === $origPersonelId) {
            \Log::info('[Telafi primTransfer] atlandi: ayni/bos personel');
            return;
        }

        $salonId = (int) $origRH->randevu->salon_id;
        $tarih   = date('Y-m-d');
        $aciklamaBase = 'Telafi randevusu #'.$yeniRandevu->id.' – orijinal randevu #'.$origRH->randevu_id;

        // KURAL 2 — "Bu hizmetin primi orijinal personele ODENDI mi?"
        // Yalnizca PRIM tipi odeme + hizmetin ait oldugu DONEM (ay) bazinda bakilir.
        //  - Prim odenmemis (2a): yalnizca satis kalemi yeni personele devredilir, clawback YOK.
        //  - Prim odenmis  (2b): satis devredilir + orijinal personele -odenen_prim kadar telafi devri.
        // (Eski kod "butun zaman + butun odeme tipleri" topluyordu; maas/avans odemesini bile
        //  "prim odendi" sayip yanlis clawback yaziyordu. Kurala gore duzeltildi.)
        $primOdenmisMiDonem = function ($donem) use ($origPersonelId, $salonId) {
            static $cache = [];
            $donem = (string) $donem;
            if (!array_key_exists($donem, $cache)) {
                $cache[$donem] = ((float) DB::table('personel_maas_odemeleri')
                    ->where('personel_id', $origPersonelId)
                    ->where('salon_id', $salonId)
                    ->where('odeme_tipi', 'prim')
                    ->where('donem', $donem)
                    ->sum('tutar')) > 0;
            }
            return $cache[$donem];
        };

        $islenenKayit = 0;

        // ============ YOL 1: tek seferlik satis (adisyon_hizmetler) ============
        // Oncelik: randevu_id eslesmesi. Yoksa fallback: ayni musteri+salon+hizmet, randevu_id NULL
        // olan (adisyona manuel eklenmis ama randevuya baglanmamis) satirlar.
        $musteriId = (int) ($origRH->randevu->user_id ?? 0);
        $ahListesi = AdisyonHizmetler::where('randevu_id', $origRH->randevu_id)
            ->where('hizmet_id', $origRH->hizmet_id)
            ->get();
        if ($ahListesi->isEmpty() && $musteriId > 0) {
            // Fallback A: ayni musteri+salon+hizmet+personel, randevu_id NULL
            $ahListesi = AdisyonHizmetler::whereHas('adisyon', function($q) use ($musteriId, $salonId){
                    $q->where('user_id', $musteriId)->where('salon_id', $salonId);
                })
                ->where('hizmet_id', $origRH->hizmet_id)
                ->where('personel_id', $origPersonelId)
                ->whereNull('randevu_id')
                ->get();
            \Log::info('[Telafi primTransfer] AH fallback A (randevu_id NULL) kullanildi', ['count'=>$ahListesi->count()]);
        }
        if ($ahListesi->isEmpty() && $musteriId > 0) {
            // Fallback B: ayni musteri+salon+hizmet+personel, randevu_id FARKLI (ama tarih yakin)
            // Normal hizmet randevulari icin adisyon_hizmetler.randevu_id yanlis baglanmis olabilir
            $origTarih = $origRH->randevu->tarih ?? null;
            $q = AdisyonHizmetler::whereHas('adisyon', function($q) use ($musteriId, $salonId){
                    $q->where('user_id', $musteriId)->where('salon_id', $salonId);
                })
                ->where('hizmet_id', $origRH->hizmet_id)
                ->where('personel_id', $origPersonelId);
            if ($origTarih) {
                // Orijinal randevunun tarihinden geriye max 7 gun pencere
                $tarihMin = date('Y-m-d', strtotime($origTarih.' -7 days'));
                $tarihMax = date('Y-m-d', strtotime($origTarih.' +1 days'));
                $q->whereHas('adisyon', function($q2) use ($tarihMin, $tarihMax){
                    $q2->whereBetween('tarih', [$tarihMin, $tarihMax]);
                });
            }
            $ahListesi = $q->get();
            \Log::info('[Telafi primTransfer] AH fallback B (musteri+hizmet+personel+tarih~) kullanildi', ['count'=>$ahListesi->count()]);
        }
        \Log::info('[Telafi primTransfer] adisyon_hizmetler bulunan', ['count' => $ahListesi->count()]);
        $this->sonTransferOzeti['ah'] = $ahListesi->count();

        foreach ($ahListesi as $ah) {
            $islenenKayit++;

            // HER DURUMDA satis kalemi yeni personele devredilsin — hem 2a (prim odenmemis:
            // prim yeni personele gecer) hem 2b (odenmis) icin gerekli. Hak edis tablosunda
            // Hizmet Primi otomatik orijinalden dusup yeni personele yansir.
            $ah->personel_id = $yeniPersonelId;
            $ah->save();
            $this->sonTransferOzeti['adisyon_degisti']++;

            // KURAL 2b: orijinal personel bu hizmetten prim KAZANIP ODENDIYSE, kazandigi
            // (odenen) prim kadar -tutar telafi devri. Kazanilan prim = MUSTERININ odedigi
            // tahsilat * ORIJINAL personelin bu hizmetteki yuzdesi.
            //  - Musteri odemediyse (tahsilat 0): prim kazanilmamis -> clawback YOK.
            //  - Orijinal personelin yuzdesi 0 ise: kazanilmamis -> clawback YOK.
            // (Eski fiyat/yuzde fallback'leri KALDIRILDI: kazanilmamis primi geri almak yanlisti.)
            $odenmisTutar = (float) DB::table('tahsilat_hizmetler')
                ->where('adisyon_hizmet_id', $ah->id)
                ->sum('tutar');
            $donemAh = substr((string) (optional($ah->adisyon)->tarih ?: date('Y-m-d')), 0, 10);
            $donemAh = substr($donemAh, 0, 7);
            $yuzde   = $this->primYuzdesi($origPersonelId, (int) $ah->hizmet_id);
            $tutar   = round($odenmisTutar * $yuzde / 100, 2);
            $primOdenmis = $primOdenmisMiDonem($donemAh);
            \Log::info('[Telafi primTransfer] AH', [
                'ah_id'=>$ah->id, 'musteri_odemis'=>$odenmisTutar, 'donem'=>$donemAh,
                'yuzde'=>$yuzde, 'tutar'=>$tutar, 'prim_odenmis'=>$primOdenmis,
            ]);

            if ($primOdenmis && $tutar > 0) {
                $this->primHareket(
                    $origPersonelId, $salonId, $tarih, -$tutar,
                    $aciklamaBase.' (telafi: ödenen prim geri devri)', $kullaniciId,
                    ['kaynak_randevu_id' => $yeniRandevu->id, 'kaynak_adisyon_hizmet_id' => $ah->id]
                );
                $this->sonTransferOzeti['hareket'] += 1;
            }
        }

        // ============ YOL 2: paket satisi (APS -> adisyon_paket) ============
        $apsList = \App\AdisyonPaketSeanslar::where('randevu_id', $origRH->randevu_id)
            ->where('hizmet_id', $origRH->hizmet_id)
            ->whereNotNull('adisyon_paket_id')
            ->get();
        \Log::info('[Telafi primTransfer] APS bulunan', ['count' => $apsList->count()]);
        $this->sonTransferOzeti['aps'] = $apsList->count();

        foreach ($apsList as $aps) {
            $islenenKayit++;
            $ap = \App\AdisyonPaketler::find($aps->adisyon_paket_id);
            if (!$ap) continue;

            // HER DURUMDA APS.personel_id yeni personele (2a + 2b)
            $aps->personel_id = $yeniPersonelId;
            $aps->save();
            $this->sonTransferOzeti['adisyon_degisti']++;

            // KURAL 2b: paket tahsil edilmis + orijinal personelin yuzdesi varsa seans-basi
            // odenen prim kadar -tutar devri. Tahsilat/yuzde yoksa prim kazanilmamis -> clawback yok.
            $toplamOdenmisPaket = (float) DB::table('tahsilat_paketler')
                ->where('adisyon_paket_id', $ap->id)
                ->sum('tutar');
            $toplamSeans = (int) ($ap->seans_sayisi ?: 0);
            if ($toplamOdenmisPaket <= 0 || $toplamSeans <= 0) continue;

            $seansBasiOdeme = $toplamOdenmisPaket / max($toplamSeans, 1);
            $yuzde = $this->primYuzdesi($origPersonelId, (int) $origRH->hizmet_id);
            $tutar = round($seansBasiOdeme * $yuzde / 100, 2);
            $donemAp = substr((string) (optional($ap->adisyon)->tarih ?: date('Y-m-d')), 0, 7);
            $primOdenmis = $primOdenmisMiDonem($donemAp);
            \Log::info('[Telafi primTransfer] APS prim', [
                'ap_id'=>$ap->id, 'paket_odenmis'=>$toplamOdenmisPaket, 'donem'=>$donemAp,
                'seans_sayisi'=>$toplamSeans, 'seans_basi'=>$seansBasiOdeme,
                'yuzde'=>$yuzde, 'tutar'=>$tutar, 'prim_odenmis'=>$primOdenmis,
            ]);
            if ($tutar <= 0) continue;

            if ($primOdenmis) {
                $this->primHareket(
                    $origPersonelId, $salonId, $tarih, -$tutar,
                    $aciklamaBase.' (paket telafi: ödenen prim geri devri)', $kullaniciId,
                    ['kaynak_randevu_id' => $yeniRandevu->id, 'kaynak_adisyon_paket_id' => $ap->id]
                );
                $this->sonTransferOzeti['hareket'] += 1;
            }
        }

        if ($islenenKayit === 0) {
            $this->sonTransferOzeti['uyari'] = 'Bu randevuya bağlı adisyon kaydı bulunamadı (ne satış kalemi ne paket seansı). Prim devri yapılamadı.';
            \Log::warning('[Telafi primTransfer] bu randevu+hizmet icin ne AH ne APS kaydi var — prim devri yapilamadi', [
                'rh_id' => $origRH->id, 'randevu_id' => $origRH->randevu_id, 'hizmet_id' => $origRH->hizmet_id
            ]);
        }
    }

    /**
     * Kisa yardimci: tek bir telafi_devir prim hareketi insert.
     * $kaynak: ['kaynak_randevu_id'=>..,'kaynak_adisyon_hizmet_id'=>..,'kaynak_adisyon_paket_id'=>..]
     * Kaynak referanslari sayesinde satis silinince hak ediste yetim kalmaz.
     */
    protected function primHareket($personelId, $salonId, $tarih, $tutar, $aciklama, $kullaniciId, array $kaynak = [])
    {
        $veri = [
            'personel_id'        => (int) $personelId,
            'salon_id'           => (int) $salonId,
            'tarih'              => $tarih,
            'tip'                => 'telafi_devir',
            'tutar'              => $tutar,
            'aciklama'           => $aciklama,
            'ekleyen_yetkili_id' => (int) $kullaniciId,
        ];
        foreach (['kaynak_randevu_id', 'kaynak_adisyon_hizmet_id', 'kaynak_adisyon_paket_id'] as $k) {
            if (!empty($kaynak[$k]) && \Schema::hasColumn('personel_prim_hareketleri', $k)) {
                $veri[$k] = (int) $kaynak[$k];
            }
        }
        PersonelPrimHareketi::create($veri);
    }

    /**
     * Personelin hizmet prim yuzdesini bul.
     * Oncelik: hizmet_prim_detayli=1 ise personel_prim_oranlari (hizmet bazli); yoksa
     * salon_personelleri.hizmet_prim_yuzde.
     */
    protected function primYuzdesi($personelId, $hizmetId)
    {
        $sp = DB::table('salon_personelleri')->where('id', $personelId)->first();
        if (!$sp) return 0;
        if (!empty($sp->hizmet_prim_detayli)) {
            $y = DB::table('personel_prim_oranlari')
                ->where('personel_id', $personelId)
                ->where('tur', 'hizmet')
                ->where('kalem_id', $hizmetId)
                ->value('yuzde');
            if ($y !== null) return (float) $y;
        }
        return (float) ($sp->hizmet_prim_yuzde ?? 0);
    }
}
