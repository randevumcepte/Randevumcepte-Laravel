<?php

namespace App\Console\Commands;

use App\DamgaKartiKurallari;
use App\Salonlar;
use App\Services\DamgaKartiServisi;
use App\Services\NotificationService;
use App\Services\NotificationTypes;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Damga karti doldu -> musteriye "ucretsiz seans kazandiniz" push'u.
 *
 * "Geldi" isareti 10+ yerden yaziliyor (web, mobil, v2, toplu), bu yuzden yazma
 * yollarina kanca yerine mutabakat: son 3 gunde "Geldi" isaretli randevusu olan
 * musterilerin damga durumu (DamgaKartiServisi::durum, tahsilat ekraniyla ayni sayim)
 * defterle karsilastirilir. Kazanilan sayisi defterdekinden buyukse ve hak bekliyorsa
 * TEK push gider. Idempotent; sayim duserse defter dusurulmez (tekrar push olmasin).
 * Schedule: everyTenMinutes + withoutOverlapping.
 */
class DamgaKartiBildirim extends Command
{
    protected $signature = 'damga:bildirim
        {--salon= : Sadece bu salon ID icin calis (test)}
        {--dry : Gondermeden ne olacagini raporla (test)}';
    protected $description = 'Damga karti dolan musterilere ucretsiz seans bildirimi gonderir';

    public function handle()
    {
        if (!DamgaKartiServisi::tabloHazir() || !Schema::hasTable('damga_karti_bildirimleri')) {
            $this->line('Damga karti tablolari yok (migration calistirilmamis).');
            return 0;
        }
        $dry = (bool) $this->option('dry');
        $bugun = Carbon::today();

        $q = DamgaKartiKurallari::where('aktif', 1)->whereNotNull('baslangic');
        if ($this->option('salon')) $q->where('salon_id', (int) $this->option('salon'));

        $gonderilen = 0;
        foreach ($q->get() as $kural) {
            try {
                $adaylar = DB::table('randevular as r')
                    ->join('randevu_hizmetler as rh', 'rh.randevu_id', '=', 'r.id')
                    ->where('r.salon_id', $kural->salon_id)
                    ->where('rh.hizmet_id', $kural->hizmet_id)
                    ->where('r.randevuya_geldi', 1)
                    ->whereNotNull('r.user_id')
                    ->whereBetween('r.tarih', [$bugun->copy()->subDays(3)->toDateString(), $bugun->toDateString()])
                    ->where('r.tarih', '>=', date('Y-m-d', strtotime($kural->baslangic)))
                    ->distinct()->pluck('r.user_id');
                if ($adaylar->isEmpty()) continue;

                $defter = DB::table('damga_karti_bildirimleri')->where('kural_id', $kural->id)
                    ->whereIn('user_id', $adaylar->all())->pluck('bildirilen_kazanilan', 'user_id');
                $salon = null;
                $hizmetAdi = DB::table('hizmetler')->where('id', $kural->hizmet_id)->value('hizmet_adi') ?: 'Hizmet';

                foreach ($adaylar as $userId) {
                    $userId = (int) $userId;
                    $d = DamgaKartiServisi::durum($kural, $userId);
                    $onceki = (int) ($defter[$userId] ?? 0);
                    if ($d['kazanilan'] <= $onceki) continue;

                    $gonder = $d['hak'] > 0;
                    if ($this->option('salon') || $dry) {
                        $this->line("kural {$kural->id} user {$userId}: kazanilan {$d['kazanilan']} (defter {$onceki}), hak {$d['hak']}" . ($gonder ? ' -> push' : ' -> yalniz defter') . ($dry ? ' (dry)' : ''));
                    }
                    if ($dry) continue;

                    if ($gonder) {
                        $salon = $salon ?: Salonlar::find($kural->salon_id);
                        $ad = trim(explode(' ', (string) DB::table('users')->where('id', $userId)->value('name'))[0] ?? '');
                        $govde = ($ad !== '' ? "Tebrikler {$ad}! " : 'Tebrikler! ')
                            . "{$hizmetAdi} hizmetinden ikram kazandınız. Dilediğiniz bir seansınızda kullanabilirsiniz 🎁";
                        // SMS/WhatsApp: yalniz isletme sectiyse ayni metin (tohumda kapali)
                        if (\App\Services\BildirimTercih::mesajAcikMi($kural->salon_id, 'damga_karti', 'musteri')) {
                            try {
                                $tel = DB::table('users')->where('id', $userId)->value('cep_telefon');
                                $sms = ($tel && $salon) ? \App\Services\BildirimTercih::dagit($salon, [[
                                    'to' => $tel, 'message' => $govde, 'user_id' => $userId,
                                    'bk' => 'damga_karti', 'alici' => 'musteri', 'push_ayri' => true,
                                ]]) : [];
                                if ($sms) {
                                    app(\App\Http\Controllers\Controller::class)->sms_gonder((int) $kural->salon_id, [['to' => $sms[0]['to'], 'message' => $sms[0]['message']]]);
                                }
                            } catch (\Throwable $e) {
                                Log::warning('[damga:bildirim] mesaj hatasi', ['user_id' => $userId, 'err' => $e->getMessage()]);
                            }
                        }
                        // Push: bildirim tercihi (damga_karti/musteri/push)
                        if (\App\Services\BildirimTercih::izin($kural->salon_id, 'damga_karti', 'musteri', 'push')) try {
                            NotificationService::toCustomer($userId, (int) $kural->salon_id)
                                ->type(NotificationTypes::DAMGA_KAZANILDI)
                                ->title('🎁 ' . ($salon->salon_adi ?? 'İkram hizmet kazandınız'))
                                ->body($govde)
                                ->deepLink('ikram_hizmet', ['salon_id' => $kural->salon_id])
                                ->extra(['kural_id' => $kural->id])
                                ->send();
                            $gonderilen++;
                        } catch (\Throwable $e) {
                            Log::warning('[damga:bildirim] push hatasi', ['user_id' => $userId, 'err' => $e->getMessage()]);
                        }
                    }
                    DB::table('damga_karti_bildirimleri')->updateOrInsert(
                        ['kural_id' => $kural->id, 'user_id' => $userId],
                        ['salon_id' => $kural->salon_id, 'bildirilen_kazanilan' => $d['kazanilan'],
                         'son_bildirim' => $gonder ? now() : null, 'updated_at' => now(), 'created_at' => now()]
                    );
                }
            } catch (\Throwable $e) {
                Log::error('damga:bildirim hata', ['kural_id' => $kural->id, 'err' => $e->getMessage()]);
            }
        }
        if ($gonderilen || $this->option('salon')) $this->info("{$gonderilen} damga bildirimi gonderildi.");
        return 0;
    }
}
