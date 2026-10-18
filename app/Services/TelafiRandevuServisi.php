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

        // Fatma'ya bu salonda HERHANGI bir prim ODEMESI (personel_maas_odemeleri tip=prim) yapildi mi?
        // Yapildiysa -> adisyon_hizmet.personel_id DEGISTIRME (gecmis rapor dogru kalsin),
        //              yerine Fatma -tutar / Ayse +tutar prim_hareketleri yaz.
        // Yapilmadiysa -> adisyon.personel_id = Ayse degistir (hak edis otomatik dengelenir),
        //                hareket yazma.
        $odenenPrim = (float) DB::table('personel_maas_odemeleri')
            ->where('personel_id', $origPersonelId)
            ->where('salon_id', $salonId)
            ->where('odeme_tipi', 'prim')
            ->sum('tutar');
        $primOdenmisMi = $odenenPrim > 0;
        \Log::info('[Telafi primTransfer] odenen prim toplam', [
            'orig_personel' => $origPersonelId,
            'toplam_prim_odemesi' => $odenenPrim,
            'prim_odenmis_mi' => $primOdenmisMi,
        ]);

        $islenenKayit = 0;

        // ============ YOL 1: tek seferlik satis (adisyon_hizmetler) ============
        // Oncelik: randevu_id eslesmesi. Yoksa fallback: ayni musteri+salon+hizmet, randevu_id NULL
        // olan (adisyona manuel eklenmis ama randevuya baglanmamis) satirlar.
        $musteriId = (int) ($origRH->randevu->user_id ?? 0);
        $ahListesi = AdisyonHizmetler::where('randevu_id', $origRH->randevu_id)
            ->where('hizmet_id', $origRH->hizmet_id)
            ->get();
        if ($ahListesi->isEmpty() && $musteriId > 0) {
            // Fallback: tahsilat edilmis + personel_id = orig + hizmet_id = hedef
            $ahListesi = AdisyonHizmetler::whereHas('adisyon', function($q) use ($musteriId, $salonId){
                    $q->where('user_id', $musteriId)->where('salon_id', $salonId);
                })
                ->where('hizmet_id', $origRH->hizmet_id)
                ->where('personel_id', $origPersonelId)
                ->whereNull('randevu_id')
                ->get();
            \Log::info('[Telafi primTransfer] AH fallback (randevu_id yok) kullanildi', ['count'=>$ahListesi->count()]);
        }
        \Log::info('[Telafi primTransfer] adisyon_hizmetler bulunan', ['count' => $ahListesi->count()]);
        $this->sonTransferOzeti['ah'] = $ahListesi->count();

        foreach ($ahListesi as $ah) {
            $odenmisTutar = (float) DB::table('tahsilat_hizmetler')
                ->where('adisyon_hizmet_id', $ah->id)
                ->sum('tutar');
            $islenenKayit++;
            \Log::info('[Telafi primTransfer] AH', ['ah_id'=>$ah->id, 'musteri_odemis'=>$odenmisTutar, 'personel'=>$ah->personel_id]);

            if ($odenmisTutar <= 0) {
                // Henuz prim hak edilmemis (musteri odemesi yok) -> adisyon personel degistir
                $ah->personel_id = $yeniPersonelId;
                $ah->save();
                continue;
            }

            $yuzde = $this->primYuzdesi($origPersonelId, (int) $ah->hizmet_id);
            $tutar = round($odenmisTutar * $yuzde / 100, 2);
            \Log::info('[Telafi primTransfer] AH prim', ['yuzde'=>$yuzde, 'tutar'=>$tutar, 'strateji'=>$primOdenmisMi?'hareket':'adisyon_degistir']);
            if ($tutar <= 0) continue;

            // HER DURUMDA adisyon.personel_id Ayse'ye devredilsin -> hak edis tablosu
            // Anil'dan dusup Ayse'ye otomatik yansisin (Hizmet Primi kolonu)
            $ah->personel_id = $yeniPersonelId;
            $ah->save();
            $this->sonTransferOzeti['adisyon_degisti']++;

            // EK: Prim ODENMIS ise Anil'a -tutar hareket (haksiz yere aldigi primi gelecek
            // hak edisten dussun diye bakiye negatife gitsin). Ayse icin ek hareket yok —
            // adisyon degisikligi ile hak edisi zaten artmistir.
            if ($primOdenmisMi) {
                $this->primHareket($origPersonelId, null, $salonId, $tarih, -$tutar, $aciklamaBase.' (telafi: geçmiş prim ödemesi düzeltme)', $kullaniciId);
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

            $toplamOdenmisPaket = (float) DB::table('tahsilat_paketler')
                ->where('adisyon_paket_id', $ap->id)
                ->sum('tutar');
            if ($toplamOdenmisPaket <= 0) {
                // Paket tahsilat yok -> APS kaydinin personel'ini degistir
                $aps->personel_id = $yeniPersonelId;
                $aps->save();
                continue;
            }
            $toplamSeans = (int) ($ap->seans_sayisi ?: 0);
            if ($toplamSeans <= 0) continue;
            $seansBasiOdeme = $toplamOdenmisPaket / max($toplamSeans, 1);
            $yuzde = $this->primYuzdesi($origPersonelId, (int) $origRH->hizmet_id);
            $tutar = round($seansBasiOdeme * $yuzde / 100, 2);
            \Log::info('[Telafi primTransfer] APS prim', [
                'ap_id'=>$ap->id, 'paket_odenmis'=>$toplamOdenmisPaket,
                'seans_sayisi'=>$toplamSeans, 'seans_basi'=>$seansBasiOdeme,
                'yuzde'=>$yuzde, 'tutar'=>$tutar, 'strateji'=>$primOdenmisMi?'hareket':'aps_degistir'
            ]);
            if ($tutar <= 0) continue;

            // HER DURUMDA APS.personel_id Ayse'ye
            $aps->personel_id = $yeniPersonelId;
            $aps->save();
            $this->sonTransferOzeti['adisyon_degisti']++;

            if ($primOdenmisMi) {
                $this->primHareket($origPersonelId, null, $salonId, $tarih, -$tutar, $aciklamaBase.' (paket telafi: geçmiş prim ödemesi düzeltme)', $kullaniciId);
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

    /** Kisa yardimci: tek bir prim hareketi insert. */
    protected function primHareket($personelId, $_unused, $salonId, $tarih, $tutar, $aciklama, $kullaniciId)
    {
        PersonelPrimHareketi::create([
            'personel_id'        => (int) $personelId,
            'salon_id'           => (int) $salonId,
            'tarih'              => $tarih,
            'tip'                => 'telafi_devir',
            'tutar'              => $tutar,
            'aciklama'           => $aciklama,
            'ekleyen_yetkili_id' => (int) $kullaniciId,
        ]);
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
