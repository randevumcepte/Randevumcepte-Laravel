<?php

namespace App;

/**
 * ÇOKLU HİZMET — SIRA BAĞIMSIZ (KOMBİNASYONLU) MÜSAİTLİK YARDIMCILARI
 * ------------------------------------------------------------------
 * Hem mobil app (ApiController@randevuTarihSaatAdimi / randevuekleguncelle) hem
 * web online randevu (HomeController@tarihsaatadiminagec / randevuonaylaDirekt)
 * AYNI mantığı kullansın diye ortak trait. Böylece iki kanal tutarlı davranır.
 *
 * Temel kural: bir başlangıç saati, seçilen hizmetler HERHANGİ bir sıraya
 * dizildiğinde (her hizmet KENDİ personeliyle, KENDİ ardışık alt-diliminde;
 * salon açık + personel çalışma penceresinde + randevu/mola ile çakışmadan)
 * sığıyorsa MÜSAİT sayılır. Doluluk durum<2 (bekleyen+onaylı) ile ölçülür;
 * iptal (2/3) sayılmaz.
 */
trait RandevuMusaitlikTrait
{
    /** İlgili personellerin dolu aralıkları (randevu+mola) ve çalışma pencereleri.
     *  Döner: [ $persDolu(pid=>[[bas,bit]..]), $persPencere(pid=>[bas,bit]), $salonBas, $salonBit ].
     *  Çalışma penceresi: personel kaydı varsa salon ile kesişim, YOKSA salon saatleri. */
    protected function _persMusaitlikVerisi($tarih, $salonId, array $personelIds, $day, $haricRandevuId = null)
    {
        $salonCalisma = SalonCalismaSaatleri::where('salon_id', $salonId)
            ->where('calisiyor', 1)->where('haftanin_gunu', $day)->first();
        $salonBas = $salonCalisma ? strtotime($salonCalisma->baslangic_saati) : strtotime('00:00');
        $salonBit = $salonCalisma ? strtotime($salonCalisma->bitis_saati) : strtotime('23:59');

        $personelIds = array_values(array_unique(array_filter($personelIds, function ($p) {
            return $p !== null && $p !== '' && $p !== 'null' && (int) $p !== 0;
        })));

        $persPencere = array();
        $persDolu = array();
        if (!empty($personelIds)) {
            $cal = PersonelCalismaSaatleri::whereIn('personel_id', $personelIds)
                ->where('calisiyor', 1)->where('haftanin_gunu', $day)->get();
            foreach ($cal as $c) {
                $persPencere[$c->personel_id] = array(
                    max($salonBas, strtotime($c->baslangic_saati)),
                    min($salonBit, strtotime($c->bitis_saati))
                );
            }
            $rand = Randevular::where('tarih', $tarih)->where('durum', '<', 2)
                ->when($haricRandevuId, function ($q) use ($haricRandevuId) { $q->where('id', '!=', $haricRandevuId); })
                ->whereHas('hizmetler', function ($q) use ($personelIds) { $q->whereIn('personel_id', $personelIds); })
                ->with(['hizmetler' => function ($q) use ($personelIds) {
                    $q->select('randevu_id', 'saat', 'saat_bitis', 'personel_id')->whereIn('personel_id', $personelIds);
                }])->get();
            foreach ($rand as $r) {
                foreach ($r->hizmetler as $rH) {
                    if (!in_array($rH->personel_id, $personelIds)) continue;
                    $persDolu[$rH->personel_id][] = array(strtotime($rH->saat), strtotime($rH->saat_bitis));
                }
            }
            $mol = PersonelMolaSaatleri::whereIn('personel_id', $personelIds)
                ->where('mola_var', 1)->where('haftanin_gunu', $day)->get();
            foreach ($mol as $m) {
                if ($m->baslangic_saati && $m->bitis_saati) {
                    $persDolu[$m->personel_id][] = array(strtotime($m->baslangic_saati), strtotime($m->bitis_saati));
                }
            }
        }
        return array($persDolu, $persPencere, $salonBas, $salonBit);
    }

    /** Verilen başlangıçta hizmetleri HERHANGİ bir sıraya dizince sığan İLK sırayı bul.
     *  $servisler: [ ['pid'=>, 'sure'=>dk], ... ]. Döner: sığan sıra
     *  [ ['orijinal_index'=>, 'pid'=>, 'sure'=>, 'bas'=>ts, 'bit'=>ts], ... ] veya null. */
    protected function _coklu_sigan_sira(array $servisler, $startTs, array $persDolu, array $persPencere, $salonBas, $salonBit)
    {
        $n = count($servisler);
        if ($n === 0) return array();
        // Permütasyon patlamasını önle: 6'dan fazla hizmette verilen sırayı dene.
        $permler = ($n > 6) ? array(range(0, $n - 1)) : $this->_permutasyonlar(range(0, $n - 1));
        foreach ($permler as $perm) {
            $out = array();
            $offsetDk = 0;
            $sigar = true;
            foreach ($perm as $idx) {
                $srv = $servisler[$idx];
                $bas = $startTs + $offsetDk * 60;
                $bit = $bas + ((int) $srv['sure']) * 60;
                $offsetDk += (int) $srv['sure'];
                $pid = $srv['pid'];

                if ($bit > $salonBit) { $sigar = false; break; }
                $pencere = isset($persPencere[$pid]) ? $persPencere[$pid] : array($salonBas, $salonBit);
                if ($bas < $pencere[0] || $bit > $pencere[1]) { $sigar = false; break; }
                foreach ((isset($persDolu[$pid]) ? $persDolu[$pid] : array()) as $ar) {
                    if ($bas < $ar[1] && $bit > $ar[0]) { $sigar = false; break 2; }
                }
                $out[] = array('orijinal_index' => $idx, 'pid' => $pid, 'sure' => (int) $srv['sure'], 'bas' => $bas, 'bit' => $bit);
            }
            if ($sigar) return $out;
        }
        return null;
    }

    /** Basit permütasyon üreteci (küçük N için). */
    protected function _permutasyonlar(array $items)
    {
        if (count($items) <= 1) return array($items);
        $sonuc = array();
        foreach ($items as $i => $it) {
            $kalan = $items;
            array_splice($kalan, $i, 1);
            foreach ($this->_permutasyonlar(array_values($kalan)) as $p) {
                array_unshift($p, $it);
                $sonuc[] = $p;
            }
        }
        return $sonuc;
    }

    /** Hizmet satırlarını, seçilen başlangıçta SIĞAN sıraya diz (sıra bağımsızlığı:
     *  müsaitlik hangi sırayla sığdıysa kayıt da onu yazsın → tutarlı). Sığan sıra
     *  yoksa orijinali döndürür (çakışma guard'ları yakalar).
     *  $hizmetler: her satırda en az ['personel_id'=>, 'sure_dk'=>] olmalı. */
    protected function _hizmetleriSigacakSiraya_diz(array $hizmetler, $randevuSaati, $tarih, $salonId, $haricRandevuId = null)
    {
        if (count($hizmetler) < 2) return $hizmetler;
        $day = date('N', strtotime($tarih));
        $servisler = array();
        $pids = array();
        foreach ($hizmetler as $h) {
            $sure = (isset($h['sure_dk']) && $h['sure_dk'] !== '') ? (int) $h['sure_dk'] : 60;
            $pid = isset($h['personel_id']) ? $h['personel_id'] : null;
            $servisler[] = array('pid' => $pid, 'sure' => $sure);
            $pids[] = $pid;
        }
        list($persDolu, $persPencere, $salonBas, $salonBit) =
            $this->_persMusaitlikVerisi($tarih, $salonId, $pids, $day, $haricRandevuId);
        $r = $this->_coklu_sigan_sira($servisler, strtotime($randevuSaati), $persDolu, $persPencere, $salonBas, $salonBit);
        if ($r === null) return $hizmetler;
        $yeni = array();
        foreach ($r as $slot) { $yeni[] = $hizmetler[$slot['orijinal_index']]; }
        return $yeni;
    }
}
