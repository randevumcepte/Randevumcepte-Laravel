<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * PATRON ASISTANI — OTOMATIK KAMPANYA motoru.
 *
 * 4 akis: kayip musteri geri kazanim, bos saat doldurma, dogum gunu, genel kampanya.
 * GUVENLIK: "sor" ucundan yalniz TEKLIF (salt-okunur, sayi + onizleme) doner;
 * GERCEK gonderim yalniz patron ONAYLADIKTAN sonra "uygula" ucundan yapilir.
 *
 * Mevcut altyapiyi yeniden kullanir: CarkifelekOdulleri (kupon), sms_gonder (SMS),
 * musteri_portfoy/users/randevular (segment).
 */
class PatronAsistanKampanyaServisi
{
    /** Guvenlik: tek kampanyada en fazla alici (timeout + maliyet siniri). */
    const MAX_ALICI = 500;

    /** Kampanya turleri: varsayilan indirim, baslik, mesaj sablonu. */
    protected function turler()
    {
        return [
            'kayip' => [
                'ad'      => 'Kayıp Müşteri Geri Kazanım',
                'oran'    => 20,
                'baslik'  => 'Sizi Özledik İndirimi',
                'sablon'  => 'Merhaba! Sizi özledik. {salon} olarak size özel %{oran} indirim tanımladık. Kupon kodunuz: {kod}. Sizi tekrar aramızda görmek isteriz.',
            ],
            'bossaat' => [
                'ad'      => 'Boş Saat Doldurma',
                'oran'    => 25,
                'baslik'  => 'Sakin Saat Fırsatı',
                'sablon'  => '{salon}\'dan fırsat! Sakin saatlerimizde %{oran} indirim sizi bekliyor. Kupon kodunuz: {kod}. Randevu için bekleriz.',
            ],
            'dogumgunu' => [
                'ad'      => 'Doğum Günü Kutlaması',
                'oran'    => 25,
                'baslik'  => 'Doğum Günü Hediyesi',
                'sablon'  => 'İyi ki doğdunuz! {salon} olarak doğum gününüzü %{oran} indirimle kutluyoruz. Kupon kodunuz: {kod}. Nice mutlu yıllara!',
            ],
            'genel' => [
                'ad'      => 'Genel Kampanya',
                'oran'    => 15,
                'baslik'  => 'Size Özel Kampanya',
                'sablon'  => '{salon}\'dan size özel kampanya! %{oran} indirim fırsatını kaçırmayın. Kupon kodunuz: {kod}.',
            ],
        ];
    }

    /**
     * Mesaj bir kampanya OLUSTURMA/GONDERME istegi mi? Hangi tur?
     * @return string|null 'kayip' | 'bossaat' | 'dogumgunu' | 'genel' | null
     */
    public function kampanyaTetik($metin)
    {
        $n = ' ' . $this->normalize($metin) . ' ';

        // Bir "gonderme/yapma" niyeti olmali (yoksa danisma/soru olabilir).
        $eylem = false;
        foreach (['gonder', 'yap', 'olustur', 'hazirla', 'baslat', 'duzenle bir', 'kampanya', 'indirim'] as $e) {
            if (strpos($n, ' ' . $e) !== false || strpos($n, $e . ' ') !== false) { $eylem = true; break; }
        }
        if (!$eylem) {
            return null;
        }

        if (strpos($n, 'kayip') !== false || strpos($n, 'gelmeyen') !== false
            || strpos($n, 'geri kazan') !== false || strpos($n, 'ozledik') !== false
            || strpos($n, 'uzun sure gelmeyen') !== false) {
            return 'kayip';
        }
        if (strpos($n, 'bos saat') !== false || strpos($n, 'olu saat') !== false
            || strpos($n, 'sakin saat') !== false || strpos($n, 'saat doldur') !== false
            || strpos($n, 'bossaat') !== false) {
            return 'bossaat';
        }
        if (strpos($n, 'dogum gun') !== false || strpos($n, 'dogumgun') !== false) {
            return 'dogumgunu';
        }
        // "kampanya gonder / indirim gonder / herkese / tum musteriler" -> genel
        if (strpos($n, 'kampanya') !== false || strpos($n, 'indirim') !== false
            || strpos($n, 'herkese') !== false || strpos($n, 'tum musteri') !== false
            || strpos($n, 'duyuru') !== false) {
            return 'genel';
        }
        return null;
    }

    /** Belirtilen turun hedef musteri kitlesi (salt-okunur sorgu). */
    public function hedefKitle($tip, $salonId)
    {
        switch ($tip) {
            case 'kayip':     return $this->gelmeyenMusteriler($salonId, 30, self::MAX_ALICI);
            case 'dogumgunu': return $this->dogumGunuMusteriler($salonId, self::MAX_ALICI);
            case 'bossaat':
            case 'genel':
            default:          return $this->tumMusteriler($salonId, self::MAX_ALICI);
        }
    }

    /**
     * TEKLIF (salt-okunur): kaç kişi, hangi indirim, örnek mesaj, tahmini kontör.
     * Yaninda 'aksiyon' doner -> uygulama Onayla/Iptal butonu gosterir.
     */
    public function teklif($tip, $salonId, $salonAdi)
    {
        $turler = $this->turler();
        if (!isset($turler[$tip])) {
            return ['basarili' => false, 'cevap' => 'Bu kampanya türünü tanımıyorum.', 'kart' => null];
        }
        $t = $turler[$tip];
        $kitle = $this->hedefKitle($tip, $salonId);
        $sayi = $kitle->count();

        if ($sayi === 0) {
            return [
                'basarili'  => true, 'intent' => 'kampanya_teklif', 'seslendir' => true,
                'cevap'     => $t['ad'] . ' için uygun müşteri bulunamadı, gönderilecek kimse yok.',
                'kart'      => null,
            ];
        }

        $ornek = $this->mesajUret($t['sablon'], $salonAdi, $t['oran'], 'ORNEK123');
        $cevap = $t['ad'] . ' hazır. Toplam ' . $sayi . ' müşteriye yüzde ' . $t['oran']
               . ' indirim göndereceğim. Örnek mesaj: ' . $ornek
               . ' Tahmini ' . $sayi . ' kontör harcanır. Onaylıyor musunuz?';

        return [
            'basarili'  => true,
            'intent'    => 'kampanya_teklif',
            'seslendir' => true,
            'cevap'     => $cevap,
            'kart'      => null,
            // Uygulama bunu gorup Onayla/Iptal butonu cikarir; Onayla'da 'uygula' ucuna yollar.
            'aksiyon'   => [
                'tur'       => 'kampanya',
                'tip'       => $tip,
                'oran'      => $t['oran'],
                'baslik'    => $t['ad'],
                'hedef_sayi'=> $sayi,
                'onay_metni'=> $sayi . ' müşteriye %' . $t['oran'] . ' indirim gönder',
            ],
        ];
    }

    /**
     * UYGULA (onaydan sonra): her aliciya kupon olustur + mesaji hazirla.
     * SMS gonderimini controller yapar (sms_gonder). Doner: mesajlar[], sayi, baslik.
     */
    public function uygula($tip, $salonId, $salonAdi, $oran = 0)
    {
        $turler = $this->turler();
        if (!isset($turler[$tip])) {
            return ['sayi' => 0, 'mesajlar' => [], 'baslik' => '', 'hata' => 'Bilinmeyen tür'];
        }
        $t = $turler[$tip];
        $oran = ($oran >= 5 && $oran <= 70) ? (int) $oran : $t['oran'];
        $kitle = $this->hedefKitle($tip, $salonId);

        $mesajlar = [];
        $gecerlilik = date('Y-m-d', strtotime('+15 days'));
        foreach ($kitle as $m) {
            $tel = trim((string) ($m->cep_telefon ?? ''));
            if ($tel === '') continue;
            $kod = strtoupper(Str::random(8));
            try {
                \App\CarkifelekOdulleri::create([
                    'salon_id'          => $salonId,
                    'user_id'           => $m->id,
                    'kod'               => $kod,
                    'tip'               => 'hizmet_indirimi',
                    'dever'             => $oran,
                    'indirim_tipi'      => 'yuzde',
                    'baslik'            => $t['baslik'] . ' %' . $oran,
                    'gecerlilik_tarihi' => $gecerlilik,
                ]);
            } catch (\Throwable $e) {
                // kupon yazilamazsa bu aliciyi atla (mesaj gonderme)
                continue;
            }
            $mesajlar[] = [
                'to'      => $tel,
                'message' => $this->mesajUret($t['sablon'], $salonAdi, $oran, $kod),
            ];
        }

        return [
            'sayi'     => count($mesajlar),
            'mesajlar' => $mesajlar,
            'baslik'   => $t['ad'],
            'oran'     => $oran,
        ];
    }

    // ─────────────── Segment sorgulari (salt-okunur) ───────────────

    /** Belirli gundur (varsayilan 30) hic randevusu OLMAYAN ama gecmiste gelmis musteriler. */
    protected function gelmeyenMusteriler($salonId, $gun = 30, $max = 500)
    {
        $cutoff = date('Y-m-d', strtotime("-{$gun} days"));
        return DB::table('musteri_portfoy as mp')
            ->join('users as u', 'u.id', '=', 'mp.user_id')
            ->where('mp.salon_id', $salonId)
            ->where('mp.aktif', 1)
            ->where(function ($q) { $q->whereNull('mp.kara_liste')->orWhere('mp.kara_liste', '!=', 1); })
            ->whereNotNull('u.cep_telefon')->where('u.cep_telefon', '!=', '')
            ->whereExists(function ($q) use ($salonId) {
                $q->select(DB::raw(1))->from('randevular as r')
                    ->whereColumn('r.user_id', 'u.id')->where('r.salon_id', $salonId);
            })
            ->whereNotExists(function ($q) use ($salonId, $cutoff) {
                $q->select(DB::raw(1))->from('randevular as r2')
                    ->whereColumn('r2.user_id', 'u.id')->where('r2.salon_id', $salonId)
                    ->where('r2.tarih', '>=', $cutoff);
            })
            ->select('u.id', 'u.name', 'u.cep_telefon')
            ->limit($max)->get();
    }

    /** Bu AY dogum gunu olan musteriler. */
    protected function dogumGunuMusteriler($salonId, $max = 500)
    {
        return DB::table('musteri_portfoy as mp')
            ->join('users as u', 'u.id', '=', 'mp.user_id')
            ->where('mp.salon_id', $salonId)
            ->where('mp.aktif', 1)
            ->where(function ($q) { $q->whereNull('mp.kara_liste')->orWhere('mp.kara_liste', '!=', 1); })
            ->whereNotNull('u.cep_telefon')->where('u.cep_telefon', '!=', '')
            ->whereNotNull('u.dogum_tarihi')
            ->whereRaw('MONTH(u.dogum_tarihi) = ?', [(int) date('n')])
            ->select('u.id', 'u.name', 'u.cep_telefon')
            ->limit($max)->get();
    }

    /** Telefonu olan tum aktif portfoy musterileri (kara liste haric). */
    protected function tumMusteriler($salonId, $max = 500)
    {
        return DB::table('musteri_portfoy as mp')
            ->join('users as u', 'u.id', '=', 'mp.user_id')
            ->where('mp.salon_id', $salonId)
            ->where('mp.aktif', 1)
            ->where(function ($q) { $q->whereNull('mp.kara_liste')->orWhere('mp.kara_liste', '!=', 1); })
            ->whereNotNull('u.cep_telefon')->where('u.cep_telefon', '!=', '')
            ->select('u.id', 'u.name', 'u.cep_telefon')
            ->limit($max)->get();
    }

    // ─────────────── Yardimcilar ───────────────

    protected function mesajUret($sablon, $salonAdi, $oran, $kod)
    {
        return str_replace(
            ['{salon}', '{oran}', '{kod}'],
            [$salonAdi ?: 'Salonumuz', $oran, $kod],
            $sablon
        );
    }

    protected function normalize($metin)
    {
        $metin = mb_strtolower(trim((string) $metin), 'UTF-8');
        $ceviri = ['ç'=>'c','ğ'=>'g','ı'=>'i','İ'=>'i','ö'=>'o','ş'=>'s','ü'=>'u','â'=>'a','î'=>'i','û'=>'u'];
        $metin = strtr($metin, $ceviri);
        return str_replace(['i̇'], ['i'], $metin);
    }
}
