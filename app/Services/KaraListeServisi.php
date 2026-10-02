<?php

namespace App\Services;

use App\MusteriPortfoy;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Merkezi kara liste filtresi — SMS + WhatsApp + Push ucleyi icin tek nokta.
 *
 * Kurali (kullanici karari, 2026-09):
 *   - musteri_portfoy.kara_liste=1 olan musteriye SMS/WA/Push HICBIR mesaj gitmez
 *     (randevu bildirim/hatirlatma/iptal dahil TUMU susar)
 *   - ISTISNA: musterinin kendi aksiyonu sonucu uretilen guvenlik mesajlari
 *     (sifremi unuttum, geldi dogrulama kodu, cark kodu) kara liste olsa bile gider.
 *     Yoksa musteri sisteme giremez.
 *
 * Entegrasyon noktalari:
 *   - WhatsAppService::sendReminder          -> engelliMi()
 *   - Controller::sms_gonder (ve wrapperlar) -> topluSuz()
 *   - NotificationService::send              -> engelliMi() (yalniz musteri hedefinde)
 */
class KaraListeServisi
{
    /**
     * Kara liste MUAFI gonderim tipleri — musterinin kendi aksiyonu sonucu uretilen
     * guvenlik/OTP mesajlari. Bu tiplerde kara liste bakilmaz.
     */
    const MUAF_TIPLER = [
        'sifre_sifirlama',          // Sifremi unuttum
        'geldi_dogrulama_kodu',     // Geldi tiklandi, kod istek
        'cark_kodu',                // Carkifelek kod
    ];

    /** Belirli bir gonderim tipi kara liste muafi mi? */
    public static function tipMuaf($gonderimTipi)
    {
        return $gonderimTipi && in_array($gonderimTipi, self::MUAF_TIPLER, true);
    }

    /**
     * Tek musteri icin kara liste kontrolu.
     * @param int|null $salonId
     * @param string|null $telefon normalize edilmemis de olabilir
     * @param int|null $userId varsa dogrudan kullanilir, yoksa telefondan cozulur
     * @param string|null $gonderimTipi muaf tiplerde her zaman false doner
     * @return bool true = engelli (gondermeyin), false = serbest
     */
    public static function engelliMi($salonId, $telefon = null, $userId = null, $gonderimTipi = null)
    {
        if (self::tipMuaf($gonderimTipi)) return false;
        $salonId = (int) $salonId;
        if ($salonId <= 0) return false; // salon belirsizse engelleme

        try {
            // user_id varsa dogrudan portfoy sorgusu
            if ($userId) {
                return self::portfoyKara($salonId, [(int) $userId]);
            }
            // Telefondan user_id(ler) coz
            $norm = self::normalizeTel($telefon);
            if ($norm === null) return false;
            $userIds = User::where(function ($q) use ($norm) {
                $q->where('cep_telefon', $norm)->orWhere('cep_telefon', ltrim($norm, '9'));
            })->pluck('id')->all();
            if (empty($userIds)) return false;
            return self::portfoyKara($salonId, $userIds);
        } catch (\Throwable $e) {
            Log::warning('[KARA-LISTE] engelliMi hata: ' . $e->getMessage());
            return false; // fail-open: hata varsa engelleme (mesaj kaybolmasin)
        }
    }

    /**
     * Toplu suzgec — SMS gonderim wrapperlarinda [['to'=>...,'message'=>...], ...]
     * dizisinden kara listedekileri cikarir. Tek bir DB sorgusu ile cozer.
     *
     * @param int|null $salonId
     * @param array $mesajlar
     * @param string|null $gonderimTipi muaf tiplerde orijinal dizi aynen doner
     * @return array filtrelenmis dizi
     */
    public static function topluSuz($salonId, array $mesajlar, $gonderimTipi = null)
    {
        if (self::tipMuaf($gonderimTipi)) return $mesajlar;
        $salonId = (int) $salonId;
        if ($salonId <= 0 || empty($mesajlar)) return $mesajlar;

        try {
            // Telefonlari topla + user_id'li varsa dogrudan isaretle
            $telefonlar = [];
            $hazirUserIds = [];
            foreach ($mesajlar as $m) {
                if (!empty($m['user_id'])) {
                    $hazirUserIds[(int) $m['user_id']] = true;
                    continue;
                }
                $t = self::normalizeTel($m['to'] ?? null);
                if ($t !== null) $telefonlar[$t] = true;
            }

            // Telefonlardan user_id(ler) coz
            $telUserIdMap = []; // normTel => [user_id, ...]
            if (!empty($telefonlar)) {
                $rows = User::whereIn('cep_telefon', array_keys($telefonlar))
                    ->get(['id', 'cep_telefon']);
                // Ayrica basinda 90 olmayan kayitlari da dene (5XXX... format)
                $noPrefix = array_map(function ($t) { return ltrim($t, '9'); }, array_keys($telefonlar));
                $noPrefix = array_filter($noPrefix, function ($t) { return !empty($t); });
                if (!empty($noPrefix)) {
                    $rows2 = User::whereIn('cep_telefon', $noPrefix)->get(['id', 'cep_telefon']);
                    $rows = $rows->merge($rows2);
                }
                foreach ($rows as $u) {
                    $tn = self::normalizeTel($u->cep_telefon);
                    if ($tn === null) continue;
                    $telUserIdMap[$tn][] = (int) $u->id;
                }
            }

            // Tum user_id'leri topla, kara listedekileri cek
            $tumUserIds = array_keys($hazirUserIds);
            foreach ($telUserIdMap as $ids) $tumUserIds = array_merge($tumUserIds, $ids);
            $tumUserIds = array_values(array_unique(array_map('intval', $tumUserIds)));
            if (empty($tumUserIds)) return $mesajlar;

            $karaUserIds = MusteriPortfoy::whereIn('user_id', $tumUserIds)
                ->where('salon_id', $salonId)
                ->where('kara_liste', 1)
                ->pluck('user_id')->map('intval')->all();
            if (empty($karaUserIds)) return $mesajlar;
            $karaSet = array_flip($karaUserIds);

            // Mesajlari suz
            $kalan = [];
            $dusen = 0;
            foreach ($mesajlar as $m) {
                $engelli = false;
                if (!empty($m['user_id']) && isset($karaSet[(int) $m['user_id']])) {
                    $engelli = true;
                } else {
                    $tn = self::normalizeTel($m['to'] ?? null);
                    if ($tn !== null && isset($telUserIdMap[$tn])) {
                        foreach ($telUserIdMap[$tn] as $uid) {
                            if (isset($karaSet[$uid])) { $engelli = true; break; }
                        }
                    }
                }
                if ($engelli) { $dusen++; continue; }
                $kalan[] = $m;
            }

            if ($dusen > 0) {
                Log::info('[KARA-LISTE] toplu suzuldu', [
                    'salon_id' => $salonId, 'tip' => $gonderimTipi,
                    'gelen' => count($mesajlar), 'dusen' => $dusen, 'kalan' => count($kalan),
                ]);
            }
            return $kalan;
        } catch (\Throwable $e) {
            Log::warning('[KARA-LISTE] topluSuz hata: ' . $e->getMessage());
            return $mesajlar; // fail-open
        }
    }

    protected static function portfoyKara($salonId, array $userIds)
    {
        return MusteriPortfoy::whereIn('user_id', $userIds)
            ->where('salon_id', $salonId)
            ->where('kara_liste', 1)
            ->exists();
    }

    protected static function normalizeTel($raw)
    {
        $n = preg_replace('/\D+/', '', (string) $raw);
        if (!$n) return null;
        if (substr($n, 0, 2) === '00') $n = substr($n, 2);
        if (strlen($n) === 10 && $n[0] === '5') $n = '90' . $n;
        if (strlen($n) === 11 && $n[0] === '0') $n = '90' . substr($n, 1);
        return strlen($n) >= 11 ? $n : null;
    }
}
