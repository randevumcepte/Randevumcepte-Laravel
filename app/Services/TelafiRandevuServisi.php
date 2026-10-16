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

    protected function primTransfer(RandevuHizmetler $origRH, Randevular $yeniRandevu, $yeniPersonelId, $kullaniciId)
    {
        $origPersonelId = (int) $origRH->personel_id;
        if (!$yeniPersonelId || $yeniPersonelId === $origPersonelId) {
            return; // ayni personel: devir yok
        }

        // Orijinal RH'ye bagli adisyon_hizmet(ler) — tek seferlik satis kalemleri
        $ahListesi = AdisyonHizmetler::where('randevu_id', $origRH->randevu_id)
            ->where('hizmet_id', $origRH->hizmet_id)
            ->get();

        if ($ahListesi->isEmpty()) {
            return; // satis kalemi yok, prim de yok (saf hizmet randevusu)
        }

        $salonId = (int) $origRH->randevu->salon_id;
        $tarih   = date('Y-m-d');
        $aciklamaBase = 'Telafi randevusu #'.$yeniRandevu->id.' – orijinal randevu #'.$origRH->randevu_id;

        foreach ($ahListesi as $ah) {
            $odenmisTutar = (float) DB::table('tahsilat_hizmetler')
                ->where('adisyon_hizmet_id', $ah->id)
                ->sum('tutar');

            if ($odenmisTutar <= 0) {
                // Prim henuz hak edilmemis -> satis kaleminin personelini degistir
                $ah->personel_id = $yeniPersonelId;
                $ah->save();
                continue;
            }

            // Odenmis -> prim duzeltme hareketleri
            $yuzde = $this->primYuzdesi($origPersonelId, (int) $ah->hizmet_id);
            if ($yuzde <= 0) continue;
            $tutar = round($odenmisTutar * $yuzde / 100, 2);
            if ($tutar <= 0) continue;

            PersonelPrimHareketi::create([
                'personel_id'        => $origPersonelId,
                'salon_id'           => $salonId,
                'tarih'              => $tarih,
                'tip'                => 'telafi_devir',
                'tutar'              => -$tutar,
                'aciklama'           => $aciklamaBase.' (devir: çıkış)',
                'ekleyen_yetkili_id' => $kullaniciId,
            ]);
            PersonelPrimHareketi::create([
                'personel_id'        => $yeniPersonelId,
                'salon_id'           => $salonId,
                'tarih'              => $tarih,
                'tip'                => 'telafi_devir',
                'tutar'              => +$tutar,
                'aciklama'           => $aciklamaBase.' (devir: giriş)',
                'ekleyen_yetkili_id' => $kullaniciId,
            ]);
        }
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
