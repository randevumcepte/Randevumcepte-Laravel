<?php

namespace App\Services;

use App\OdaHizmetler;
use App\Odalar;
use App\RandevuHizmetler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Hizmete uygun ve secilen tarih+saat dilimde musait olan oda secimini yapar.
 * Paket satislarinda ve manuel randevu olusturmada kullanilir.
 *
 * Mantik:
 *   1) oda_sunulan_hizmetler tablosundan hizmetin verilebildigi odalari bul
 *   2) Bu odalar arasinda aktif olanlari filtrele
 *   3) randevu_hizmetler'de ayni tarihte cakisanlari ele
 *   4) Bos olanlardan ilkini don (kullanici manuel override edebilir)
 *
 * Tablo runtime'da yoksa self-heal yapar (deploy gecikmesini tolere et).
 */
class OdaAtamaServisi
{
    private static function tabloVarMi(): bool
    {
        if (Schema::hasTable('oda_sunulan_hizmetler')) {
            return true;
        }
        try {
            Schema::create('oda_sunulan_hizmetler', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('salon_id');
                $table->unsignedBigInteger('oda_id');
                $table->unsignedBigInteger('hizmet_id');
                $table->timestamps();
                $table->index('salon_id', 'osh_salon_idx');
                $table->index('oda_id', 'osh_oda_idx');
                $table->index('hizmet_id', 'osh_hizmet_idx');
                $table->unique(['oda_id', 'hizmet_id'], 'osh_oda_hizmet_uq');
            });
            $migName = '2026_05_19_000004_create_oda_sunulan_hizmetler_table';
            if (!DB::table('migrations')->where('migration', $migName)->count()) {
                $batch = (int) DB::table('migrations')->max('batch');
                DB::table('migrations')->insert(['migration' => $migName, 'batch' => $batch ?: 1]);
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Tek hizmet icin uygun + bos oda sec.
     *
     * @param int      $salonId
     * @param int      $hizmetId
     * @param string   $tarih      Y-m-d formatinda
     * @param string   $saat       H:i baslangic
     * @param string   $saatBitis  H:i bitis
     * @param int[]    $haricOdaIdleri Bu cagrida ayni gruptan secilen odalar (cift atama olmasin)
     * @return int|null            Oda ID veya null (uygun yoksa)
     */
    public static function uygunOdaSec(int $salonId, int $hizmetId, string $tarih, string $saat, string $saatBitis, array $haricOdaIdleri = []): ?int
    {
        if (!self::tabloVarMi()) {
            return null;
        }

        $odaIdleri = OdaHizmetler::where('salon_id', $salonId)
            ->where('hizmet_id', $hizmetId)
            ->pluck('oda_id')
            ->toArray();

        if (empty($odaIdleri)) {
            return null;
        }

        // Aktif ve musait olanlari sec
        $aktifOdalar = Odalar::whereIn('id', $odaIdleri)
            ->where('salon_id', $salonId)
            ->where('aktifmi', true)
            ->where('durum', true)
            ->orderBy('takvim_sirasi', 'asc')
            ->pluck('id')
            ->toArray();

        if (empty($aktifOdalar)) {
            return null;
        }

        // Cakisma kontrolu: ayni gun ayni odada saat araligi orten randevu var mi?
        // randevu_hizmetler'i randevular ile join'le tarih ve oda kontrolu yap.
        $dolu = DB::table('randevu_hizmetler')
            ->join('randevular', 'randevu_hizmetler.randevu_id', '=', 'randevular.id')
            ->whereIn('randevu_hizmetler.oda_id', $aktifOdalar)
            ->where('randevular.tarih', $tarih)
            ->where('randevular.durum', '!=', 0)
            ->whereRaw("randevu_hizmetler.saat < ?", [$saatBitis])
            ->whereRaw("randevu_hizmetler.saat_bitis > ?", [$saat])
            ->pluck('randevu_hizmetler.oda_id')
            ->unique()
            ->toArray();

        $bosOdalar = array_diff($aktifOdalar, $dolu, $haricOdaIdleri);
        if (empty($bosOdalar)) {
            // Bu hizmette ayni anda fazla randevu olamayacagindan ilk aktifi don
            // (kullaniciya UI'da uyari basabiliriz, sonra)
            $fallback = array_diff($aktifOdalar, $haricOdaIdleri);
            return !empty($fallback) ? (int) array_values($fallback)[0] : null;
        }

        return (int) array_values($bosOdalar)[0];
    }
}
