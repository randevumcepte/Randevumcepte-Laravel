<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Salon sahibinin online randevuya AÇIK bıraktığı haftalık saat pencereleri.
 * Bir güne birden çok satır girilebilir (öğle arası boşluğu için 2 aralık gibi).
 *
 * Boş-saat hesabında (ApiController@randevuTarihSaatAdimi ve
 * AiAsistanController@musaitSaatler) tek kaynak burasıdır; ikisi de
 * izinliAraliklar()/slotIzinliMi()/seyrelt() yardımcılarını kullanır.
 */
class SalonOnlineRandevuSaatleri extends Model
{
    protected $table = 'salon_online_randevu_saatleri';

    protected $fillable = ['salon_id', 'haftanin_gunu', 'baslangic_saati', 'bitis_saati'];

    /**
     * Verilen salon+tarih için online randevuya açık aralıklar.
     *
     * Dönüş:
     *   null  -> kısıtlama kapalı; TÜM çalışma saatleri online açık (eski davranış)
     *   []    -> o gün online tamamen KAPALI
     *   [['bas'=>'11:00','bit'=>'13:00'], ...] -> yalnız bu aralıklar açık
     */
    public static function izinliAraliklar($salonId, $tarih)
    {
        $salon = Salonlar::find($salonId);
        if (!$salon || (int) ($salon->online_saat_kisitlama_aktif ?? 0) !== 1) {
            return null; // kısıtlama yok
        }

        // 1) Tarihe özel istisna haftalık kuralı ezer
        $istisnalar = SalonOnlineRandevuIstisnasi::where('salon_id', $salonId)
            ->where('tarih', $tarih)
            ->get();

        if ($istisnalar->count() > 0) {
            // Herhangi bir 'kapali' kaydı varsa o gün tamamen kapalı
            foreach ($istisnalar as $i) {
                if ($i->tip === 'kapali') return array();
            }
            $araliklar = array();
            foreach ($istisnalar as $i) {
                if ($i->tip === 'ozel' && $i->baslangic_saati && $i->bitis_saati) {
                    $araliklar[] = array(
                        'bas' => substr($i->baslangic_saati, 0, 5),
                        'bit' => substr($i->bitis_saati, 0, 5),
                    );
                }
            }
            return $araliklar; // boşsa (geçersiz istisna) o gün kapalı sayılır
        }

        // 2) Haftalık pencereler
        $gun = (int) date('N', strtotime($tarih));
        $rows = self::where('salon_id', $salonId)->where('haftanin_gunu', $gun)->get();

        $araliklar = array();
        foreach ($rows as $r) {
            if ($r->baslangic_saati && $r->bitis_saati) {
                $araliklar[] = array(
                    'bas' => substr($r->baslangic_saati, 0, 5),
                    'bit' => substr($r->bitis_saati, 0, 5),
                );
            }
        }
        return $araliklar; // boş = o gün online kapalı (sahibi o günü açmamış)
    }

    /**
     * HH:MM başlangıçlı bir slot online'da gösterilebilir mi?
     * $toplamSureDk > 0 ise randevu [saat, saat+süre) aralığı bir pencereye
     * TAM sığmalı; 0 ise yalnız başlangıç bir pencere içinde olmalı.
     */
    public static function slotIzinliMi($araliklar, $saat, $toplamSureDk = 0)
    {
        if ($araliklar === null) return true; // kısıtlama yok

        $bas = strtotime($saat);
        $bit = ($toplamSureDk > 0) ? $bas + ($toplamSureDk * 60) : $bas;

        foreach ($araliklar as $a) {
            $ab = strtotime($a['bas']);
            $ae = strtotime($a['bit']);
            if ($toplamSureDk > 0) {
                if ($bas >= $ab && $bit <= $ae) return true;
            } else {
                if ($bas >= $ab && $bas < $ae) return true;
            }
        }
        return false;
    }

    /**
     * Günlük limit (seyreltme): sıralı bir diziden eşit aralıklarla en fazla
     * $limit eleman seçer. ['secili'=>[...], 'elenmis'=>[...]] döner.
     * $limit <= 0 veya eleman sayısı <= limit ise hepsi 'secili'.
     */
    public static function seyrelt(array $sirali, $limit)
    {
        $n = count($sirali);
        if ($limit <= 0 || $n <= $limit) {
            return array('secili' => array_values($sirali), 'elenmis' => array());
        }

        $secIdx = array();
        if ($limit == 1) {
            $secIdx[(int) floor($n / 2)] = true; // tek slot -> ortadan
        } else {
            for ($i = 0; $i < $limit; $i++) {
                $secIdx[(int) round($i * ($n - 1) / ($limit - 1))] = true;
            }
        }

        $secili = array();
        $elenmis = array();
        $idx = 0;
        foreach ($sirali as $v) {
            if (isset($secIdx[$idx])) $secili[] = $v;
            else $elenmis[] = $v;
            $idx++;
        }
        return array('secili' => $secili, 'elenmis' => $elenmis);
    }
}
