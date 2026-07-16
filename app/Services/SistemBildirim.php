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
    /** Sidecar oturum id'si — salonlardan tamamen bagimsiz */
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
                    'numara' => $d['numara'] ?? '',
                    'aktif' => !empty($d['aktif']),
                    'gonderen_salon_id' => $d['gonderen_salon_id'] ?? null,
                ];
            }
        }
        return ['numara' => '', 'aktif' => false, 'gonderen_salon_id' => null];
    }

    public static function ayarYaz($numara, $aktif, $gonderenSalonId = null)
    {
        $data = [
            'numara' => self::normalizeTel($numara),
            'aktif' => (bool) $aktif,
            'gonderen_salon_id' => $gonderenSalonId ? (int) $gonderenSalonId : null,
        ];
        @file_put_contents(self::ayarDosyasi(), json_encode($data));
        return $data;
    }

    /**
     * WhatsApp gonderiminde kullanilacak "gonderen" salon — hatirlatmalarin kullandigi
     * yolun aynisi (bagli bir salonun WA hatti). Once ayardaki tercih, yoksa ilk bagli salon.
     */
    public static function gonderenSalon($tercihId = null)
    {
        if ($tercihId) {
            $s = \App\Salonlar::find($tercihId);
            if ($s && $s->whatsapp_aktif && $s->whatsapp_durum === 'connected') return $s;
        }
        return \App\Salonlar::where('whatsapp_aktif', 1)
            ->where('whatsapp_durum', 'connected')
            ->orderBy('id')->first();
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

        // WhatsApp: hatirlatmalarin kullandigi PROVEN yol — bagli bir salonun hattindan
        // sendUrgent ile gonder. (Ayri 'sistem' oturumu kendine gonderemiyordu; bu yol
        // farkli bir salondan gonderdigi icin o sorun da yok.)
        try {
            $salon = self::gonderenSalon($ayar['gonderen_salon_id'] ?? null);
            if ($salon) {
                $detay['wa'] = app(WhatsAppService::class)
                    ->sendUrgent($salon, $numara, $mesaj, null, 'sistem_bildirim');
            } else {
                $detay['wa'] = ['ok' => false, 'error' => 'bagli-salon-yok'];
            }
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

    /** Yeni demo acildiginda cagrilir (kayit akisini asla bozmasin diye try/catch'li kullan). */
    public static function demoAcildi($salon)
    {
        $ad  = $salon->salon_adi ?? 'Salon';
        $tel = $salon->yetkili_telefon ?? '';
        $mesaj = "🆕 Yeni DEMO açıldı\nSalon: {$ad}"
            . ($tel ? "\nTel: {$tel}" : '')
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
        (new \App\Http\Controllers\Controller())->sms_gonder('', [
            ['to' => $yerel, 'message' => $mesaj],
        ]);
        return ['ok' => true];
    }
}
