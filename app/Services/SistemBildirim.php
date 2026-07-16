<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Sistem yonetimi bildirimleri — salon-BAGIMSIZ "sistem" WhatsApp oturumu + SMS.
 *
 * Amac: musteri demo acinca sistem sahibine (Ferdi) WhatsApp + SMS mesaji gitsin.
 * WhatsApp, sidecar'da 'sistem' oturum id'siyle (salon_sistem klasoru) calisir; hicbir
 * salona bagli degildir — QR sistem yonetimi ekranindan taranir.
 *
 * Ayar (numara + aktiflik) storage/app/sistem_wa.json'da tutulur (migration gerekmez;
 * storage git-reset'te silinmez).
 */
class SistemBildirim
{
    /** Sidecar oturum id'si — salonlardan tamamen bagimsiz (varsayilan) */
    const SESSION = 'sistem';

    public static function ayarDosyasi()
    {
        return storage_path('app/sistem_wa.json');
    }

    public static function ayarOku()
    {
        $f = self::ayarDosyasi();
        if (is_file($f)) {
            $d = json_decode((string) file_get_contents($f), true);
            if (is_array($d)) {
                return [
                    'numara' => $d['numara'] ?? '',                   // ALICI (bildirim gelecek)
                    'gonderen_numara' => $d['gonderen_numara'] ?? '', // GONDEREN (QR ile baglanan)
                    'aktif' => !empty($d['aktif']),
                    'session_id' => $d['session_id'] ?? self::SESSION,
                ];
            }
        }
        return ['numara' => '', 'gonderen_numara' => '', 'aktif' => false, 'session_id' => self::SESSION];
    }

    /** Ayar dosyasini mevcut degerleri koruyarak gunceller. */
    public static function ayarGuncelle(array $yeni)
    {
        $data = array_merge(self::ayarOku(), $yeni);
        @file_put_contents(self::ayarDosyasi(), json_encode($data));
        return $data;
    }

    public static function ayarYaz($numara, $aktif, $gonderenNumara = null)
    {
        return self::ayarGuncelle([
            'numara' => self::normalizeTel($numara),
            'gonderen_numara' => self::normalizeTel($gonderenNumara),
            'aktif' => (bool) $aktif,
        ]);
    }

    /** Aktif WhatsApp oturum id'si (taze baglanti icin degistirilebilir). */
    public static function sessionId()
    {
        $a = self::ayarOku();
        return !empty($a['session_id']) ? $a['session_id'] : self::SESSION;
    }

    /**
     * TAZE oturum id uretir ve kaydeder — "Oturumu Kapat"ta cagrilir ki sidecar eski
     * kimligi (auth) korusa bile yeni baglantida MUTLAKA yeni QR ciksin (eski numara
     * geri baglanmasin).
     */
    public static function yeniOturumId()
    {
        $yeni = self::SESSION . '-' . substr((string) time(), -6);
        self::ayarGuncelle(['session_id' => $yeni]);
        return $yeni;
    }

    /**
     * WhatsApp gonderiminde kullanilacak "gonderen" salon — hatirlatmalarin kullandigi
     * yolun aynisi (bagli bir salonun WA hatti). Once ayardaki tercih, yoksa ilk bagli salon.
     */
    public static function gonderenSalon($tercihId = null)
    {
        // SADECE acikca secilen salon (kendi hattin) kullanilir. Rastgele bir MUSTERI
        // salonunun WhatsApp'i kullanilmasin diye otomatik secim YOK. Seçilmezse WA atlanir,
        // SMS zaten gider (asil/garanti kanal).
        if (!$tercihId) return null;
        $s = \App\Salonlar::find($tercihId);
        if ($s && $s->whatsapp_aktif && $s->whatsapp_durum === 'connected') return $s;
        return null;
    }

    /** Panel dropdown'u icin: WhatsApp'i bagli salonlar. */
    public static function bagliSalonlar()
    {
        return \App\Salonlar::where('whatsapp_aktif', 1)
            ->where('whatsapp_durum', 'connected')
            ->orderBy('salon_adi')
            ->get(['id', 'salon_adi', 'whatsapp_numara']);
    }

    /** 05xx / 5xx / +90... -> 90xxxxxxxxxx (WhatsApp/SMS icin) */
    public static function normalizeTel($tel)
    {
        $t = preg_replace('/[^0-9]/', '', (string) $tel);
        if ($t === '') return '';
        if (strlen($t) === 10 && $t[0] === '5') $t = '90' . $t;
        elseif (strlen($t) === 11 && $t[0] === '0') $t = '90' . substr($t, 1);
        return $t;
    }

    /** Yapilandirilmis numaraya WhatsApp (bagli salon hatti, hatirlatma metodu) + SMS gonderir. */
    public static function gonder($mesaj)
    {
        $ayar = self::ayarOku();
        if (empty($ayar['aktif']) || empty($ayar['numara'])) {
            return ['ok' => false, 'reason' => 'kapali-veya-numara-yok'];
        }
        $numara = $ayar['numara'];
        $detay = ['wa' => null, 'sms' => null];

        // WhatsApp: ayri (salon-bagimsiz) 'sistem' oturumundan gonderilir. Bu oturuma
        // AYRI bir numara QR ile baglanir; alici (senin numaran) gonderenden FARKLI oldugu
        // icin "kendine gonderme" sorunu olmaz. Baglı degilse WA atlanir, SMS yine gider.
        try {
            // Aktif bridge whatsmeow (3002) — 'sistem' oturumu onun uzerinden.
            $detay['wa'] = app(WhatsmeowService::class)->sendTest(self::sessionId(), $numara, $mesaj);
        } catch (\Throwable $e) {
            $detay['wa'] = ['ok' => false, 'error' => $e->getMessage()];
            Log::warning('[SistemBildirim] WA hata', ['e' => $e->getMessage()]);
        }

        try {
            $detay['sms'] = self::smsGonder($numara, $mesaj);
        } catch (\Throwable $e) {
            $detay['sms'] = ['ok' => false, 'error' => $e->getMessage()];
            Log::warning('[SistemBildirim] SMS hata', ['e' => $e->getMessage()]);
        }

        return ['ok' => true, 'detay' => $detay];
    }

    /**
     * Yeni demo acildiginda cagrilir (kayit akisini asla bozmasin diye try/catch'li kullan).
     * Satis takibi icin: salon adi + yetkili adi + telefon -> hemen aranabilsin.
     */
    public static function demoAcildi($salon, $yetkiliAd = null, $yetkiliTel = null)
    {
        $ad   = $salon->salon_adi ?? 'Salon';
        $yad  = $yetkiliAd ?: ($salon->yetkili_adi ?? '');
        $ytel = $yetkiliTel ?: ($salon->yetkili_telefon ?? '');
        $mesaj = "🆕 YENİ DEMO KAYDI\nSalon: {$ad}"
            . ($yad ? "\nYetkili: {$yad}" : '')
            . ($ytel ? "\nTel: {$ytel}" : '')
            . "\nID: " . ($salon->id ?? '-')
            . "\n" . date('d.m.Y H:i');
        return self::gonder($mesaj);
    }

    /**
     * SMS — mevcut global gonderim altyapisini kullanir (salonid='' => global baslik).
     * API anahtari burada TUTULMAZ; Controller::sms_gonder icindeki mevcut mekanizma calisir.
     */
    protected static function smsGonder($numara, $mesaj)
    {
        // efetech YEREL format bekler (5xxxxxxxxx) — WA icin 90'li tutulan numaradan
        // ulke kodunu soy. (WhatsApp 90..., SMS 5... — farkli format.)
        $yerel = preg_replace('/^90/', '', (string) $numara);
        $resp = (new \App\Http\Controllers\Controller())->sms_gonder('', [
            ['to' => $yerel, 'message' => $mesaj],
        ]);
        $ok = is_array($resp) && (($resp['status'] ?? '') === 'success' || isset($resp['response']));
        $durum = is_array($resp)
            ? ($resp['status'] ?? ($resp['error'] ?? (isset($resp['errors']) ? json_encode($resp['errors']) : 'bilinmiyor')))
            : 'yanit-yok';
        return ['ok' => $ok, 'to' => $yerel, 'durum' => $durum];
    }
}
