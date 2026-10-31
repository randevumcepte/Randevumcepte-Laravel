<?php

namespace App\Console\Commands;

use App\Http\Controllers\ApiController;
use App\Randevular;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * v2 bildirim kuyrugu isleyicisi (outbox consumer).
 *
 * Yeni L13 app (Randevumcepte-New-Laravel) randevu durum islemlerinde yan
 * etkileri 'v2_bildirim_kuyrugu'na yazar; bu komut onlari MEVCUT bildirim
 * koduyla gonderir (kod tekrari YOK — v1 uclari da ayni metodlari cagirir):
 *
 *   randevu_iptal  → ApiController::randevuIptalBildirimleri (payload.red)
 *   randevu_onay   → ApiController::randevuOnayBildirimleri  (yetkili_id = onaylayan)
 *   dogrulama_kodu → ApiController::dogrulama_kodu_gonder    (kodu uretir+kaydeder+gonderir)
 *   kvkk_kodu      → ApiController::kvkkOnayKoduGonder        (portfoy onay_kodu)
 *   seans_kullanim → SeansBildirimService::bilgilendir
 *
 * Kernel: everyMinute + withoutOverlapping; komut ~55 sn boyunca 2 sn aralikla
 * kuyrugu yoklar → kod SMS'leri pratikte birkac saniyede gider.
 *
 * Satir talep etme ATOMIK (durum 0→3 kosullu UPDATE) → iki surec ayni satiri
 * isleyemez. Hata: 3 denemeye kadar tekrar kuyruga (durum 0), sonra kalici (2).
 * 5 dk'dan uzun 'isleniyor'da kalan (surec olduyse) satirlar geri alinir.
 */
class V2BildirimIsle extends Command
{
    protected $signature = 'v2:bildirim-isle {--sure=55 : saniye cinsinden calisma butcesi} {--tek : tek tur calis ve cik}';

    protected $description = 'Yeni v2 app randevu bildirim kuyrugunu mevcut bildirim koduyla gonder';

    const MAKS_DENEME = 3;

    public function handle()
    {
        if (!Schema::hasTable('v2_bildirim_kuyrugu')) {
            return 0;
        }

        // Olmus surecten kalan 'isleniyor' satirlarini geri al.
        DB::table('v2_bildirim_kuyrugu')
            ->where('durum', 3)
            ->where('updated_at', '<', date('Y-m-d H:i:s', strtotime('-5 minutes')))
            ->update(['durum' => 0, 'updated_at' => date('Y-m-d H:i:s')]);

        $bitis = time() + max(1, (int) $this->option('sure'));
        $toplam = 0;

        do {
            $islenen = $this->tur();
            $toplam += $islenen;
            if ($this->option('tek')) {
                break;
            }
            if ($islenen === 0) {
                sleep(2);
            }
        } while (time() < $bitis);

        if ($toplam > 0) {
            $this->info("v2 bildirim: $toplam kayit islendi");
        }

        return 0;
    }

    /** Bir tur: en fazla 20 bekleyen kaydi talep et ve isle. */
    private function tur()
    {
        $ids = DB::table('v2_bildirim_kuyrugu')->where('durum', 0)->orderBy('id')->limit(20)->pluck('id');
        $islenen = 0;

        foreach ($ids as $id) {
            // ATOMIK talep: yalnizca hala bekliyorsa bu surec alir.
            $alindi = DB::table('v2_bildirim_kuyrugu')->where('id', $id)->where('durum', 0)
                ->update(['durum' => 3, 'updated_at' => date('Y-m-d H:i:s')]);
            if (!$alindi) {
                continue;
            }

            $kayit = DB::table('v2_bildirim_kuyrugu')->where('id', $id)->first();
            try {
                $this->gonder($kayit);
                DB::table('v2_bildirim_kuyrugu')->where('id', $id)->update([
                    'durum' => 1, 'islenme_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $e) {
                $deneme = (int) $kayit->deneme + 1;
                DB::table('v2_bildirim_kuyrugu')->where('id', $id)->update([
                    'durum' => $deneme >= self::MAKS_DENEME ? 2 : 0,
                    'deneme' => $deneme,
                    'hata' => mb_substr($e->getMessage(), 0, 2000),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                Log::warning('[v2:bildirim-isle] hata', ['id' => $id, 'tur' => $kayit->tur, 'deneme' => $deneme, 'hata' => $e->getMessage()]);
            }
            $islenen++;
        }

        return $islenen;
    }

    private function gonder($kayit)
    {
        $payload = $kayit->payload ? (json_decode($kayit->payload, true) ?: []) : [];
        $randevu = Randevular::find($kayit->randevu_id);
        if (!$randevu) {
            throw new \RuntimeException('Randevu bulunamadi: ' . $kayit->randevu_id);
        }

        $api = app(ApiController::class);
        $istek = new Request();

        switch ($kayit->tur) {
            case 'randevu_iptal':
                $api->randevuIptalBildirimleri($istek, $randevu, !empty($payload['red']), true);
                break;
            case 'randevu_onay':
                $api->randevuOnayBildirimleri($istek, $randevu, $kayit->yetkili_id, true);
                break;
            case 'dogrulama_kodu':
                $api->dogrulama_kodu_gonder(new Request(['randevuid' => $randevu->id]));
                break;
            case 'kvkk_kodu':
                $onayKodu = DB::table('musteri_portfoy')
                    ->where('user_id', $randevu->user_id)->where('salon_id', $randevu->salon_id)
                    ->value('onay_kodu');
                if (!$onayKodu) {
                    throw new \RuntimeException('Portfoy onay_kodu yok');
                }
                $api->kvkkOnayKoduGonder($istek, $randevu, $onayKodu);
                break;
            case 'seans_kullanim':
                app(\App\Services\SeansBildirimService::class)->bilgilendir($randevu);
                break;
            default:
                throw new \RuntimeException('Bilinmeyen tur: ' . $kayit->tur);
        }
    }
}
