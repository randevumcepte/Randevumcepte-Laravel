<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Mobil "Santral Raporlari" (cdrraporson) listesinde AI asistanin karsiladigi / yaptigi aramalari isaretler
 * ve asistan ses kaydini "Dinle"ye baglar. Uygulama guncellemesi GEREKMEZ: mevcut Cdr alanlari kullanilir
 * (gorusmeyiyapan = "🤖 Asistan · <sonuc>", seskaydi = imzali herkese acik .wav linki, durum 3/2 -> Dinle gorunur).
 * FreePBX CDR'da asistan cagrisi "ana-menu / Sonuçsuz" olarak gelir; eslesme telefon (son 10 hane) + saat (±3 dk).
 */
class SantralAsistanCdr
{
    const PENCERE_SN = 180;
    const IMZA_GUN = 7;

    /**
     * @param array $rapor  cdrRaporLatest satirlari (referans)
     * @param array $meta   her rapor satiri icin ['son10' => ..., 'ts' => unix, 'giden' => bool]
     */
    public static function zenginlestir(array &$rapor, array $meta, int $salonId): void
    {
        try {
            if (!$rapor || !Schema::hasTable('santral_oturumlari')) return;
            $tsler = array_filter(array_column($meta, 'ts'));
            if (!$tsler) return;
            $oturumlar = DB::table('santral_oturumlari')->where('sube_id', $salonId)
                ->whereBetween('created_at', [date('Y-m-d H:i:s', min($tsler) - self::PENCERE_SN), date('Y-m-d H:i:s', max($tsler) + self::PENCERE_SN)])
                ->get(array_merge(['id', 'telefon', 'sonuc', 'durum', 'created_at'], Schema::hasColumn('santral_oturumlari', 'geri_ara') ? ['geri_ara'] : []));
            if ($oturumlar->isEmpty()) return;

            $sesler = Schema::hasTable('santral_ses_kayit')
                ? DB::table('santral_ses_kayit')->whereIn('oturum_id', $oturumlar->pluck('id')->all())->orderBy('id')->get(['id', 'oturum_id', 'tur'])
                : collect();
            $aiSes = [];
            foreach ($sesler as $s) {
                // Oncelik asistan kismi (ai); yoksa aktarma kaydi
                if ($s->tur === 'ai' || !isset($aiSes[$s->oturum_id])) $aiSes[(int) $s->oturum_id] = (int) $s->id;
            }

            $kullanildi = [];
            foreach ($rapor as $i => &$satir) {
                $m = $meta[$i] ?? null;
                if (!$m || empty($m['son10']) || empty($m['ts'])) continue;
                $en = null; $enFark = PHP_INT_MAX;
                foreach ($oturumlar as $o) {
                    if (isset($kullanildi[$o->id])) continue;
                    $t = substr(preg_replace('/\D/', '', (string) $o->telefon), -10);
                    if ($t !== $m['son10']) continue;
                    $fark = abs(strtotime($o->created_at) - $m['ts']);
                    if ($fark <= self::PENCERE_SN && $fark < $enFark) { $en = $o; $enFark = $fark; }
                }
                if (!$en) continue;
                $kullanildi[$en->id] = 1;
                $kod = ($en->sonuc === 'geri_ara' && (int) ($en->geri_ara ?? 0) === 2) ? 'geri_arandi' : $en->sonuc;
                $etiket = \App\Http\Controllers\StoreAdminController::asistanSonucEtiketi($kod, $en->durum);
                $onceki = trim((string) ($satir['gorusmeyiyapan'] ?? ''));
                // Aktarilip bir dahilide cevaplandiysa kim cevapladi da kalsin
                $aktarilan = ($onceki !== '' && !in_array($onceki, ['Sonuçsuz', 'Santral', 'null'], true) && !preg_match('/^(Yol tarifi|Kampanya|Yeni randevu|Randevu )/u', $onceki)) ? ' → ' . $onceki : '';
                $satir['gorusmeyiyapan'] = '🤖 Asistan · ' . $etiket . $aktarilan;
                if (isset($aiSes[$en->id])) {
                    $satir['seskaydi'] = self::imzaliUrl($aiSes[$en->id]);
                    // "Dinle" butonu yalniz durum 2 (giden) / 3 (gelen cevaplandi) icin gorunur
                    if (!in_array((string) $satir['durum'], ['2', '3'], true)) $satir['durum'] = empty($m['giden']) ? '3' : '2';
                }
            }
            unset($satir);
        } catch (\Throwable $e) {
            Log::warning('[SANTRAL-CDR-ASISTAN] ' . $e->getMessage());
        }
    }

    public static function imza(int $id, int $bitis): string
    {
        return substr(hash_hmac('sha256', 'santral_ses|' . $id . '|' . $bitis, (string) config('app.key')), 0, 32);
    }

    public static function imzaliUrl(int $sesId): string
    {
        $bitis = strtotime('+' . self::IMZA_GUN . ' days');
        $bitis -= $bitis % 3600; // saatlik yuvarla (ayni linkler)
        return url('/api/v1/santral-asistan-ses/' . $sesId . '/' . $bitis . '/' . self::imza($sesId, $bitis) . '.wav');
    }
}
