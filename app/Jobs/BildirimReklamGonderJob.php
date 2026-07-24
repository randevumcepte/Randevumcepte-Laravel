<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\BildirimReklamlari;
use App\Services\NotificationService;
use App\Services\NotificationTypes;

/**
 * Bir bildirim reklamini HEDEF musterilere PUSH olarak gonderir.
 *
 * Tasarim (HatirlatmaAramaJob ile ayni felsefe):
 * - $queue='notifications': prod worker (queue:work database --queue=hatirlatmalar,notifications)
 *   bu joblari isler. Lokalde QUEUE_DRIVER=sync -> inline calisir.
 * - $tries=1: retry = MUKERRER push. Asla retry etme; hatalari handle() icinde yut.
 * - Hedef kitle (segment) job icinde cozulur; boylece buyuk salonlarda binlerce
 *   push senkron istek yerine arka planda gonderilir.
 */
class BildirimReklamGonderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue = 'notifications';
    public $timeout = 1700;
    public $tries = 1;

    protected $reklamId;

    public function __construct($reklamId)
    {
        $this->reklamId = $reklamId;
    }

    public function handle()
    {
        $reklam = BildirimReklamlari::find($this->reklamId);
        if (!$reklam || !$reklam->kanal_push) return;

        $userIds = $this->hedefKullanicilar($reklam);
        if (empty($userIds)) {
            Log::info('[REKLAM-PUSH] hedef bos', ['reklam_id' => $this->reklamId]);
            return;
        }

        $imageUrl = $reklam->gorsel ? url($reklam->gorsel) : null;
        $gonderilen = 0;
        foreach ($userIds as $uid) {
            try {
                $n = NotificationService::toCustomer((int) $uid, (int) $reklam->salon_id)
                    ->type(NotificationTypes::DISCOUNT)
                    ->title($reklam->baslik)
                    ->body((string) ($reklam->mesaj ?: ''))
                    ->deepLink('reklam_detay', ['reklam_id' => $reklam->id]);
                if ($imageUrl) $n->image($imageUrl);
                $n->send();
                $gonderilen++;
            } catch (\Throwable $e) {
                // Tek musteri hatasi tum gonderimi bozmasin (retry yok)
            }
        }

        Log::info('[REKLAM-PUSH] tamamlandi', [
            'reklam_id'  => $this->reklamId,
            'hedef'      => count($userIds),
            'gonderilen' => $gonderilen,
        ]);
    }

    /**
     * Reklamin hedef kitlesine gore user_id listesi.
     * Taban: salonun aktif musterileri (musteri_portfoy). Segment ise daraltilir.
     */
    protected function hedefKullanicilar(BildirimReklamlari $reklam)
    {
        $salonId = (int) $reklam->salon_id;
        $portfoy = DB::table('musteri_portfoy')
            ->where('salon_id', $salonId)
            ->where('aktif', 1)
            ->pluck('user_id')->unique()->values()->all();

        if ($reklam->hedef_kitle !== 'segment' || empty($reklam->hedef_kosul)) {
            return $portfoy;
        }

        $kosul = json_decode($reklam->hedef_kosul, true);
        if (!is_array($kosul) || empty($kosul['tip'])) return $portfoy;

        switch ($kosul['tip']) {
            case 'kisi':
                // Test/tekil gonderim: sadece secilen musteriye
                $uid = (int) ($kosul['user_id'] ?? 0);
                return $uid > 0 ? [$uid] : [];

            case 'cinsiyet':
                $c = $kosul['cinsiyet'] ?? null;
                if ($c === null || $c === '') return $portfoy;
                return DB::table('users')->whereIn('id', $portfoy)
                    ->where('cinsiyet', $c)->pluck('id')->all();

            case 'dogum_gunu':
                return DB::table('users')->whereIn('id', $portfoy)
                    ->whereNotNull('dogum_tarihi')
                    ->whereRaw('MONTH(dogum_tarihi) = ?', [(int) date('m')])
                    ->pluck('id')->all();

            case 'hizmet':
                $hizmetId = (int) ($kosul['hizmet_id'] ?? 0);
                if ($hizmetId <= 0) return $portfoy;
                return DB::table('adisyonlar')
                    ->join('adisyon_hizmetler', 'adisyonlar.id', '=', 'adisyon_hizmetler.adisyon_id')
                    ->where('adisyonlar.salon_id', $salonId)
                    ->whereIn('adisyonlar.user_id', $portfoy)
                    ->where('adisyon_hizmetler.hizmet_id', $hizmetId)
                    ->distinct()->pluck('adisyonlar.user_id')->all();

            case 'gelmeyen':
                $gun = (int) ($kosul['gun'] ?? 60);
                if ($gun <= 0) $gun = 60;
                $esik = now()->subDays($gun)->toDateTimeString();
                // Son X gunde adisyonu (ziyareti) OLAN musteriler
                $gelenler = DB::table('adisyonlar')
                    ->where('salon_id', $salonId)
                    ->whereIn('user_id', $portfoy)
                    ->where('created_at', '>=', $esik)
                    ->distinct()->pluck('user_id')->all();
                $gelenSet = array_flip($gelenler);
                // Gelmeyenler = portfoyde olup son X gunde adisyonu olmayanlar
                return array_values(array_filter($portfoy, function ($uid) use ($gelenSet) {
                    return !isset($gelenSet[$uid]);
                }));
        }

        return $portfoy;
    }
}
