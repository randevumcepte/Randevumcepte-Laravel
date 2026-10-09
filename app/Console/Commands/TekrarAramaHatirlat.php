<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Bildirimler;
use App\Personeller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

/**
 * Arama Randevusu (callback) hatirlatmalari — SADECE durum=3 (Tekrar Aranacak).
 *
 * Musteriye HICBIR sey gitmez; yalnizca arayacak personel uyarilir.
 * Normal randevu akisindan (randevular tablosu + RandevuSMSHatirlatma) tamamen bagimsizdir.
 *
 * Her dakika (Kernel: everyMinute, withoutOverlapping) calisir. Tam-dakika eslesmesi
 * yerine PENCERE + idempotent BAYRAK (ar_5dk_at / ar_zaman_at / ar_gecikti) kullanir;
 * boylece cron bir dakikayi kacirsa bile hatirlatma kaybolmaz ve asla cift gonderilmez.
 *
 * Fazlar:
 *   - 5 dk once : randevu zamani (now, now+6dk] -> personele "yaklasiyor" bildirimi
 *   - tam zamani: randevu zamani [now-5dk, now]  -> yuksek oncelikli "simdi ara" bildirimi
 *   - gecikti   : randevu zamani < now-10dk ve hala aranmadi -> ar_gecikti=1 + tek seferlik uyari
 */
class TekrarAramaHatirlat extends Command
{
    protected $signature = 'arama:hatirlat';
    protected $description = 'Arama Randevusu (callback) hatirlatmalari — sadece personele';

    public function handle()
    {
        // Yeni kolonlar yoksa (schema henuz uygulanmadiysa) sessizce cik.
        if (!Schema::hasTable('aranacak_musteriler') || !Schema::hasColumn('aranacak_musteriler', 'ar_5dk_at')) {
            return 0;
        }

        $now   = date('Y-m-d H:i:s');
        $art6  = date('Y-m-d H:i:s', strtotime('+6 minutes'));
        $once5 = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $once10= date('Y-m-d H:i:s', strtotime('-10 minutes'));

        $this->faz5dk($now, $art6);
        $this->fazZaman($once5, $now);
        $this->fazGecikti($once10);

        return 0;
    }

    /** Ortak: durum=3 + tarih/saat dolu callback'leri, verilen zaman penceresinde ceker. */
    private function adaylar($altSinir, $ustSinir, $ekBayrakKolonu)
    {
        return DB::table('aranacak_musteriler as am')
            ->join('arama_listesi as al', 'al.id', '=', 'am.arama_id')
            ->leftJoin('users as u', 'u.id', '=', 'am.user_id')
            ->whereNotIn('al.durum', [2, 3])   // aktif = arsiv(2) ve pasif(3) DISINDA
            ->where('am.durum', 3)
            ->whereNotNull('am.tarih')->where('am.tarih', '!=', '')
            ->whereNotNull('am.saat')->where('am.saat', '!=', '')
            ->whereNull('am.' . $ekBayrakKolonu)
            ->whereNull('am.ar_tamamlandi_at')
            ->whereRaw("CONCAT(am.tarih,' ',am.saat) >= ?", [$altSinir])
            ->whereRaw("CONCAT(am.tarih,' ',am.saat) <= ?", [$ustSinir])
            ->whereNotNull('al.personel_id')
            ->select([
                'am.id', 'am.tarih', 'am.saat', 'am.user_id', 'am.musteri_not',
                'al.salon_id', 'al.personel_id',
                'u.name as musteri_ad', 'u.profil_resim',
            ])
            ->limit(200)->get();
    }

    private function faz5dk($altSinir, $ustSinir)
    {
        foreach ($this->adaylar($altSinir, $ustSinir, 'ar_5dk_at') as $a) {
            // atomik claim: sadece 1 sunucu/calisma gondersin
            $claim = DB::table('aranacak_musteriler')->where('id', $a->id)
                ->whereNull('ar_5dk_at')->update(['ar_5dk_at' => date('Y-m-d H:i:s')]);
            if ($claim < 1) continue;

            $saat = date('H:i', strtotime($a->saat));
            $ad   = $a->musteri_ad ?: 'Musteri';
            $mesaj = $saat . "'de " . $ad . " aranacak (5 dakika kaldi).";
            $this->uyar($a, '⏰ Yaklasan Arama', $mesaj, false);
        }
    }

    private function fazZaman($altSinir, $ustSinir)
    {
        foreach ($this->adaylar($altSinir, $ustSinir, 'ar_zaman_at') as $a) {
            $claim = DB::table('aranacak_musteriler')->where('id', $a->id)
                ->whereNull('ar_zaman_at')->update(['ar_zaman_at' => date('Y-m-d H:i:s')]);
            if ($claim < 1) continue;

            $ad = $a->musteri_ad ?: 'Musteri';
            $mesaj = $ad . " simdi aranacak. Arama zamani geldi.";
            $this->uyar($a, '📞 Simdi Ara', $mesaj, true);
        }
    }

    /** Zamani gecmis ama hala aranmamis callback'leri isaretle + personele tek uyari. */
    private function fazGecikti($ustSinir)
    {
        $gecikenler = DB::table('aranacak_musteriler as am')
            ->join('arama_listesi as al', 'al.id', '=', 'am.arama_id')
            ->leftJoin('users as u', 'u.id', '=', 'am.user_id')
            ->whereNotIn('al.durum', [2, 3])   // aktif = arsiv(2) ve pasif(3) DISINDA
            ->where('am.durum', 3)
            ->where('am.ar_gecikti', 0)
            ->whereNull('am.ar_tamamlandi_at')
            ->whereNotNull('am.tarih')->where('am.tarih', '!=', '')
            ->whereNotNull('am.saat')->where('am.saat', '!=', '')
            ->whereRaw("CONCAT(am.tarih,' ',am.saat) < ?", [$ustSinir])
            ->whereNotNull('al.personel_id')
            ->select([
                'am.id', 'am.tarih', 'am.saat', 'am.user_id', 'am.musteri_not',
                'al.salon_id', 'al.personel_id',
                'u.name as musteri_ad', 'u.profil_resim',
            ])
            ->limit(200)->get();

        foreach ($gecikenler as $a) {
            $claim = DB::table('aranacak_musteriler')->where('id', $a->id)
                ->where('ar_gecikti', 0)->update(['ar_gecikti' => 1]);
            if ($claim < 1) continue;

            $ad = $a->musteri_ad ?: 'Musteri';
            $mesaj = $ad . " icin arama zamani gecti, hala aranmadi.";
            $this->uyar($a, '⚠️ Geciken Arama', $mesaj, true);
        }
    }

    /** Arayacak personele push + panel ici bildirim gonderir. Musteriye GITMEZ. */
    private function uyar($a, $baslik, $mesaj, $yuksekOncelik)
    {
        $yetkiliIdleri = Personeller::where('id', $a->personel_id)->pluck('yetkili_id')->toArray();
        foreach ($yetkiliIdleri as $yid) {
            if (!$yid) continue;
            try {
                \App\Services\NotificationService::toStaff((int) $yid, (int) $a->salon_id)
                    ->type(\App\Services\NotificationTypes::CALL_APPOINTMENT_REMINDER)
                    ->title($baslik)
                    ->body($mesaj)
                    ->send();
            } catch (\Throwable $e) {
                Log::warning('[ARAMA-RANDEVU] push fail', ['yetkili_id' => $yid, 'err' => $e->getMessage()]);
            }
        }

        try {
            $this->bildirimekle($a->salon_id, $mesaj, '/isletmeyonetim/arama-randevu-takvim?sube=' . $a->salon_id . '&tarih=' . $a->tarih,
                $a->personel_id, $a->user_id, $a->profil_resim ?? null);
        } catch (\Throwable $e) {
            Log::warning('[ARAMA-RANDEVU] bildirim fail', ['id' => $a->id, 'err' => $e->getMessage()]);
        }
    }

    private function bildirimekle($salonid, $mesaj, $url, $personelid, $musteriid, $imgurl)
    {
        $bildirim = new Bildirimler();
        $bildirim->aciklama = $mesaj;
        $bildirim->salon_id = $salonid;
        $bildirim->personel_id = $personelid;
        $bildirim->satis_ortagi_id = null;
        $bildirim->url = $url;
        $bildirim->tarih_saat = date('Y-m-d H:i:s');
        $bildirim->okundu = false;
        $bildirim->user_id = $musteriid;
        $bildirim->img_src = $imgurl;
        $bildirim->randevu_id = null;
        $bildirim->save();
    }
}
