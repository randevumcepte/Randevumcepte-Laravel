<?php

namespace App\Console\Commands;

use App\BildirimKimlikleri;
use App\CarkHatirlatmaAyarlari;
use App\CarkHatirlatmaLoglari;
use App\CarkifelekCevirmeLoglari;
use App\CarkifelekSistemi;
use App\Http\Controllers\BildirimController;
use App\Randevular;
use App\Salonlar;
use App\Services\NotificationService;
use App\Services\NotificationTypes;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CarkHatirlatmaGonder extends Command
{
    protected $signature = 'cark:hatirlatma-gonder';
    protected $description = 'Çarkıfelek için günlük 4 push hatırlatma gönderir (saatte bir, ±5dk pencere)';

    public function handle()
    {
        if (!Schema::hasTable('cark_hatirlatma_ayarlari')) {
            $this->info('Tablo yok, çıkılıyor.');
            return 0;
        }

        $now      = Carbon::now();
        $bugun    = $now->toDateString();
        $haftaGun = (int) $now->format('N'); // 1 (Pzt) - 7 (Paz)

        $aktifAyarlar = CarkHatirlatmaAyarlari::where('aktif', 1)->get();
        if ($aktifAyarlar->isEmpty()) {
            $this->info('Aktif hatırlatma ayarı yok, çıkılıyor.');
            return 0;
        }

        $toplamGonderim = 0;

        foreach ($aktifAyarlar as $ayar) {
            // Bu salon için bu hafta günü gönderim yapma listesinde mi?
            $skipDays = is_array($ayar->gonderim_gunleri) ? $ayar->gonderim_gunleri : [];
            if (in_array($haftaGun, $skipDays)) continue;

            // Çarkıfelek aktif mi?
            $cark = CarkifelekSistemi::where('salon_id', $ayar->salon_id)->first();
            if (!$cark || !$cark->aktifmi) continue;

            // Hangi aşama? Şu an saatlerin ±5dk içinde miyiz?
            // Maks 3 hatırlatma; her aşamanın kendi aktif flag'i var (default 1).
            // Eski 'son' slot'u kaldirildi — yeni mimariye gore sadece 1/2/3 calisir.
            $asama = null; $mesaj = null;
            $saatler = [
                1 => ['saat' => $ayar->saat_1, 'mesaj' => $ayar->mesaj_1, 'aktif' => $ayar->aktif_1 ?? 1],
                2 => ['saat' => $ayar->saat_2, 'mesaj' => $ayar->mesaj_2, 'aktif' => $ayar->aktif_2 ?? 1],
                3 => ['saat' => $ayar->saat_3, 'mesaj' => $ayar->mesaj_3, 'aktif' => $ayar->aktif_3 ?? 1],
            ];
            foreach ($saatler as $no => $s) {
                if ((int) $s['aktif'] !== 1) continue; // bu aşama salon tarafından kapatılmış
                if (empty($s['saat'])) continue;
                $hedef = Carbon::parse($bugun . ' ' . $s['saat']);
                if ($now->between($hedef->copy()->subMinutes(5), $hedef->copy()->addMinutes(5))) {
                    $asama = $no;
                    $mesaj = $s['mesaj'];
                    break;
                }
            }
            if (!$asama) continue;

            // Hak sahibi müşteriler: onaylanmış randevusu olan + bugün çevirmemiş + bu aşama push almamış
            $onayliRandevuKullanicilari = Randevular::where('salon_id', $ayar->salon_id)
                ->where('durum', 1)
                ->pluck('user_id')->unique()->filter();

            if ($onayliRandevuKullanicilari->isEmpty()) continue;

            // Bugün çarkı çevirmemiş kullanıcılar
            $bugunCevirenler = CarkifelekCevirmeLoglari::where('salon_id', $ayar->salon_id)
                ->where('tip', '!=', 'tekrar_dene')
                ->whereDate('created_at', $bugun)
                ->pluck('user_id')->unique();

            $hedefUserlar = $onayliRandevuKullanicilari->diff($bugunCevirenler);
            if ($hedefUserlar->isEmpty()) continue;

            // Bu aşamayı bugün almış olanları çıkar
            $bugunAlmisKullanicilar = CarkHatirlatmaLoglari::where('salon_id', $ayar->salon_id)
                ->where('asama', $asama)
                ->whereDate('tarih', $bugun)
                ->pluck('user_id')->unique();
            $hedefUserlar = $hedefUserlar->diff($bugunAlmisKullanicilar);

            if ($hedefUserlar->isEmpty()) continue;

            $salon = Salonlar::find($ayar->salon_id);
            if (!$salon) continue;

            // Çarkıfelek görseli (mevcut alanlardan dene)
            $carkGorsel = null;
            foreach (['gorsel', 'kapak_gorsel', 'banner_url', 'image_url'] as $kol) {
                if (Schema::hasColumn('carkifelek_sistemi', $kol) && !empty($cark->{$kol})) {
                    $carkGorsel = $cark->{$kol};
                    break;
                }
            }
            if ($carkGorsel && !preg_match('#^https?://#i', $carkGorsel)) {
                $carkGorsel = rtrim(config('app.url', ''), '/') . '/' . ltrim($carkGorsel, '/');
            }

            foreach ($hedefUserlar as $userId) {
                $user = User::find($userId);
                if (!$user) continue;

                // Mesaj kişiselleştirme
                $body = strtr($mesaj, [
                    '{ad}'        => explode(' ', $user->name)[0] ?? '',
                    '{salon_adi}' => $salon->salon_adi,
                ]);
                $title = '🎡 ' . $salon->salon_adi;

                try {
                    $sonuc = NotificationService::toCustomer((int)$userId, (int)$salon->id)
                        ->type(NotificationTypes::WHEEL_CHANCE)
                        ->title($title)
                        ->body($body)
                        ->image($carkGorsel)
                        ->popup(true)
                        ->deepLink('wheel', ['salon_id' => $salon->id])
                        ->extra(['asama' => $asama])
                        ->send();

                    $basarili = ($sonuc['sent'] ?? 0) > 0;
                    CarkHatirlatmaLoglari::create([
                        'salon_id'        => $salon->id,
                        'user_id'         => $userId,
                        'asama'           => $asama,
                        'tarih'           => $bugun,
                        'gonderim_tarihi' => $now,
                        'durum'           => $basarili ? 'gonderildi' : 'hata',
                    ]);
                    if ($basarili) $toplamGonderim++;
                } catch (\Exception $e) {
                    Log::warning("Cark push gönderim hatası user={$userId}: " . $e->getMessage());
                    CarkHatirlatmaLoglari::create([
                        'salon_id'        => $salon->id,
                        'user_id'         => $userId,
                        'asama'           => $asama,
                        'tarih'           => $bugun,
                        'gonderim_tarihi' => $now,
                        'durum'           => 'hata',
                    ]);
                }
            }
        }

        $this->info("Toplam {$toplamGonderim} push hatırlatma gönderildi.");
        return 0;
    }
}
