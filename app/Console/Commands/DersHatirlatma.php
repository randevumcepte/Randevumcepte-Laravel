<?php

namespace App\Console\Commands;

use App\DersOturumu;
use App\Salonlar;
use App\SalonSMSAyarlari;
use App\Services\DersBildirimServisi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Grup dersi (Pilates/kurs) hatirlatmasi. Randevu hatirlatmasi ile AYNI yapi:
 *  - "Kac saat once" ayari salon bazli: salonlar.randevu_sms_hatirlatma (randevu
 *    ile ayni kolon/deger).
 *  - Toggle: salon_sms_ayarlari ayar_id=1 (randevu hatirlatma) musteri bayragi.
 *  - Kanal: WhatsApp ONCELIKLI, SMS yedek (DersBildirimServisi).
 *
 * Her dakika tetik anini (ders_zamani - X saat) dakika hassasiyetinde karsilastirir.
 * Idempotency: ders_oturumlari.hatirlatma_gonderildi ile atomik claim (oturum basina
 * tek gonderim; tum aktif katilimcilara).
 *
 * Schedule: everyMinute + withoutOverlapping (RandevuSMSHatirlatma ile ayni).
 */
class DersHatirlatma extends Command
{
    protected $signature = 'ders:hatirlat
        {--oturum= : Sadece bu oturum ID icin calis (test)}
        {--force : Tetik saatini/gonderildi bayragini yoksay, hemen gonder (test)}
        {--dry : Gondermeden ne olacagini raporla (test)}';
    protected $description = 'Grup dersi katilimcilarina WhatsApp/SMS hatirlatma (randevu hatirlatma yapisi)';

    public function handle()
    {
        $simdi = date('Y-m-d H:i');
        $bugun = date('Y-m-d');
        $ustSinir = date('Y-m-d', strtotime('+2 day')); // X saat gece yarisini asabilir

        $oturumId = $this->option('oturum');
        $force    = (bool) $this->option('force');
        $dry      = (bool) $this->option('dry');
        $test     = $oturumId || $force || $dry;

        $q = DersOturumu::where('aktif', true)
            ->where(function ($q) { $q->whereNull('iptal')->orWhere('iptal', 0); });
        if ($oturumId) {
            $q->where('id', (int) $oturumId);
        } else {
            $q->whereNull('hatirlatma_gonderildi')
              ->whereBetween('tarih', [$bugun, $ustSinir]);
        }
        $oturumlar = $q->get();

        if ($oturumlar->isEmpty()) {
            if ($test) $this->line('Uygun oturum bulunamadi.');
            return;
        }

        // Salon + ayar cache (per-salon tek sorgu)
        $salonCache = [];
        $ayarCache  = [];

        foreach ($oturumlar as $o) {
            $sid = (int) $o->salon_id;
            if (!array_key_exists($sid, $salonCache)) {
                $salonCache[$sid] = Salonlar::find($sid);
                // Bildirim tercihi (ders_hatirlatma/musteri): herhangi bir kanal aciksa gonderilir
                // Ek alicilar (personel/yonetici bilgi kopyasi) aciksa da oturum islenir; musteri
                // gonderimi mesajGonder/izin icinde kendi tercihine bakar (kapaliysa musteriye gitmez).
                $ayarCache[$sid]  = \App\Services\BildirimTercih::acikMi($sid, 'ders_hatirlatma', 'musteri')
                    || \App\Services\BildirimTercih::acikMi($sid, 'ders_hatirlatma', 'personel')
                    || \App\Services\BildirimTercih::acikMi($sid, 'ders_hatirlatma', 'yonetici');
            }
            $salon = $salonCache[$sid];
            if (!$salon) { if ($test) $this->warn("Oturum {$o->id}: salon yok"); continue; }

            // Ders hatirlatma bildirimi tum kanallarda kapaliysa gitmesin
            $toggleAcik = (bool) $ayarCache[$sid];
            $x = (int) ($salon->randevu_sms_hatirlatma ?? 0);
            $dersZamani = strtotime($o->tarih . ' ' . $o->saat);
            $tetik = date('Y-m-d H:i', $dersZamani - $x * 3600);

            // GOZLEMLENEBILIRLIK: tetige +-5 dk yakin oturumlari her cron tur'unda
            // logla (cron elle komut calistirmadan neden gitti/gitmedi gorunsun).
            $yakin = abs(($dersZamani - $x * 3600) - strtotime($simdi)) <= 300;
            if ($yakin && !$test) {
                Log::info('[DERS-HAT] aday', [
                    'oturum' => $o->id, 'salon' => $sid, 'ders' => $o->ders_tipi,
                    'tetik' => $tetik, 'simdi' => $simdi, 'toggle' => $toggleAcik ? 1 : 0,
                    'sure_saat' => $x, 'gonderildi' => $o->hatirlatma_gonderildi,
                ]);
            }

            if ($test) {
                $this->line("Oturum #{$o->id} [{$salon->salon_adi}] {$o->tarih} {$o->saat} '{$o->ders_tipi}'");
                $this->line("  toggle(ayar_id=1): " . ($toggleAcik ? 'ACIK' : 'KAPALI')
                    . " | hatirlatma_suresi(randevu_sms_hatirlatma): {$x} saat"
                    . " | tetik: {$tetik} | simdi: {$simdi}");
            }

            if (!$toggleAcik) {
                if ($yakin && !$test) Log::info('[DERS-HAT] ATLANDI: toggle(ayar_id=1) KAPALI', ['oturum' => $o->id]);
                if ($test) $this->warn('  -> ATLANDI: randevu hatirlatma toggle KAPALI'); continue;
            }
            if ($x <= 0) {
                if ($yakin && !$test) Log::info('[DERS-HAT] ATLANDI: hatirlatma suresi 0', ['oturum' => $o->id]);
                if ($test) $this->warn('  -> ATLANDI: salon hatirlatma suresi tanimsiz (0)'); continue;
            }

            // Tetik PENCERESI (--force ile yoksayilir): tetik aninDAN ders baslangicina
            // KADAR herhangi bir cron turunda gonder. Exact-dakika esitligi (tetik==simdi)
            // KIRILGANDI: cron o dakikayi kacirirsa (sunucu yuku / withoutOverlapping
            // gecikmesi — log'da 13:45 turu hic olmamis, 13:44'ten 13:46'ya atlamis)
            // hatirlatma sonsuza dek kacar. Pencere + atomik claim (hatirlatma_gonderildi)
            // = kacan dakikayi sonraki tur yakalar, yine de TEK gonderim.
            // Grace penceresi: tetik aninDAN tetik+15dk'ya kadar. Kacan cron dakikasini
            // yakalar ama saatler once tetigi gecmis oturumlara (deploy sonrasi) toplu
            // mesaj ATMAZ. Ders baslangicini gecmisse de gondermez.
            $simdiTs = strtotime($simdi);
            $tetikTs = $dersZamani - $x * 3600;
            $pencereSon = min($tetikTs + 900, $dersZamani); // +15dk, ama ders baslamadan
            if (!$force && !($simdiTs >= $tetikTs && $simdiTs < $pencereSon)) {
                if ($yakin && !$test) Log::info('[DERS-HAT] ATLANDI: tetik penceresi disinda', ['oturum' => $o->id, 'tetik' => $tetik, 'simdi' => $simdi]);
                if ($test) $this->warn('  -> ATLANDI: tetik penceresi disinda (--force ile zorlanabilir)');
                continue;
            }

            if ($dry) { $this->info('  -> GONDERILECEKTI (dry, gonderilmedi)'); $this->oturumuHatirlat($o, $salon, true); continue; }

            if ($force) {
                // Test: bayragi yoksay, dogrudan gonder (tekrar test edilebilsin)
                DB::table('ders_oturumlari')->where('id', $o->id)->update(['hatirlatma_gonderildi' => date('Y-m-d H:i:s')]);
                $this->info('  -> GONDERILIYOR (force)');
                $this->oturumuHatirlat($o, $salon);
                continue;
            }

            // Normal akis: atomik claim (yalnizca bir process alsin)
            $claimed = DB::table('ders_oturumlari')
                ->where('id', $o->id)
                ->whereNull('hatirlatma_gonderildi')
                ->update(['hatirlatma_gonderildi' => date('Y-m-d H:i:s')]);
            if ($claimed !== 1) {
                Log::info('[DERS-HAT] ATLANDI: baska process claim etti / zaten gonderildi', ['oturum' => $o->id]);
                continue;
            }

            Log::info('[DERS-HAT] TETIK ESLESTI, gonderiliyor', ['oturum' => $o->id, 'tetik' => $tetik]);
            $this->oturumuHatirlat($o, $salon);
        }
    }

    private function oturumuHatirlat(DersOturumu $o, Salonlar $salon, bool $dry = false)
    {
        // Kontenjani dolduran (iptal/bekleme/gelmedi olmayan) katilimcilar
        $katilimcilar = DB::table('ders_katilimcilar')
            ->join('users', 'ders_katilimcilar.user_id', '=', 'users.id')
            ->where('ders_katilimcilar.oturum_id', $o->id)
            ->whereNotIn('ders_katilimcilar.durum', ['iptal', 'bekleme', 'gelmedi'])
            ->get(['users.id', 'users.name', 'users.cep_telefon', 'users.whatsapp_onay']);

        if ($katilimcilar->isEmpty()) {
            if ($dry) $this->warn('     (gonderilecek katilimci yok — rezerve/geldi durumunda kimse yok)');
            return;
        }

        $tarihStr = date('d.m.Y', strtotime($o->tarih));
        $saatStr  = substr($o->saat, 0, 5);
        $dersAdi  = $o->ders_tipi ?: 'Grup Dersi';

        // Egitmen (hoca) adi
        $egitmen = optional($o->personel)->personel_adi;

        // Klinik/salon telefonu (randevu hatirlatmasi ile ayni: telefon_1, basina 0)
        $tel = trim((string) ($salon->telefon_1 ?? ''));
        if ($tel !== '' && ctype_digit($tel[0]) && $tel[0] !== '0') {
            $tel = '0' . $tel;
        }

        // Randevu hatirlatmasi gibi: ders adi + egitmen + tarih + saat + klinik no
        $satirlar = [];
        $satirlar[] = '📅 Tarih: ' . $tarihStr;
        $satirlar[] = '🕒 Saat: ' . $saatStr;
        $satirlar[] = '🧘 Ders: ' . $dersAdi;
        if (!empty($egitmen)) $satirlar[] = '👤 Eğitmen: ' . $egitmen;
        if ($tel !== '')      $satirlar[] = '📞 ' . $tel;

        // ODEME HATIRLATMASI (studyo modu): ders hatirlatmasina EK olarak, danisanin
        // tamamlanmamis odemesi (odendi=0 adisyon) varsa mesaja tutarsiz bir odeme
        // satiri eklenir. Dersler tekrar ettikce odeme "Odeme Alindi" (tamOde) ile
        // kapanana kadar dogal olarak tekrarlar. SADECE studyo modu: non-studyo
        // salonlarda odendi kullanilmadigindan (hep 0) yanlis pozitif olurdu.
        $studyoModu = (int) ($salon->studyo_modu ?? 0) === 1;

        foreach ($katilimcilar as $m) {
            $odemeBekliyor = $studyoModu && $this->musteriOdemeBekliyorMu($salon->id, $m->id);
            if ($dry) {
                $this->line('     - ' . $m->name . ' (' . ($m->cep_telefon ?: 'TEL YOK') . ')'
                    . ($odemeBekliyor ? '  [+odeme hatirlatma]' : ''));
                continue;
            }
            $mesaj = 'Sayın ' . $m->name . '; aşağıdaki dersinizi hatırlatmak isteriz, görüşmek üzere ✨' . "\n\n"
                . implode("\n", $satirlar);
            if ($odemeBekliyor) {
                $mesaj .= "\n\n" . '💳 Ayrıca tamamlanmamış bir ödemeniz görünmektedir. Ödemenizi tamamlamak için bizimle iletişime geçebilirsiniz.';
            }
            self::mesajGonder($salon, $m, $mesaj, 'ders_hatirlatma', 'ders_hatirlatma');

            // PUSH — WhatsApp/SMS kanalindan BAGIMSIZ, bildirim tercihine bagli (ders_hatirlatma/push).
            if (\App\Services\BildirimTercih::izin($salon->id, 'ders_hatirlatma', 'musteri', 'push')) try {
                $pushBody = $dersAdi . ' dersiniz ' . $tarihStr . ' ' . $saatStr . '. Görüşmek üzere!'
                    . ($odemeBekliyor ? ' (Bekleyen ödemeniz bulunmaktadır.)' : '');
                \App\Services\NotificationService::toCustomer($m->id, $salon->id)
                    ->type(\App\Services\NotificationTypes::CLASS_REMINDER)
                    ->title('Ders Hatırlatması')
                    ->body($pushBody)
                    ->deepLink('sessions')
                    ->send();
            } catch (\Throwable $e) {
                Log::warning('[DERS-HAT] push fail', ['salon_id' => $salon->id, 'user_id' => $m->id, 'err' => $e->getMessage()]);
            }
        }
        if ($dry) return;

        // Ek alicilar (egitmen/yonetici bilgi kopyasi) — oturum basina tek mesaj
        $ekAdlar = [];
        foreach ($katilimcilar as $m) {
            $ekAdlar[] = trim($m->name . (!empty($m->cep_telefon) ? ' (' . $m->cep_telefon . ')' : ''));
        }
        $ekListe = array_slice($ekAdlar, 0, 10);
        if (count($ekAdlar) > 10) $ekListe[] = '+' . (count($ekAdlar) - 10) . ' kişi daha';
        \App\Services\BildirimTercih::ekGonder($salon, 'ders_hatirlatma', [
            'personel_ids' => !empty($o->personel_id) ? [(int) $o->personel_id] : [],
            'ozet' => $dersAdi . ' dersi (' . $tarihStr . ' ' . $saatStr . (!empty($egitmen) ? ', ' . $egitmen : '') . ') için '
                . count($ekAdlar) . ' katılımcı: ' . implode(', ', $ekListe) . '.',
        ]);

        Log::info('[DERS-HAT] oturum hatirlatildi', [
            'oturum_id' => $o->id, 'salon_id' => $salon->id, 'katilimci' => $katilimcilar->count(),
        ]);
    }

    /**
     * Bildirim tercihine gore WA/SMS. Yalniz WhatsApp secili + yedek SMS acik = bugunku yol
     * (DersBildirimServisi: WA dene, gitmezse SMS). Diger secimler BildirimTercih::dagit ile.
     * Push burada atilmaz (cagiran kendi atiyor).
     */
    public static function mesajGonder(Salonlar $salon, $m, string $mesaj, string $kod, string $gonderimTipi)
    {
        $k = \App\Services\BildirimTercih::kanallar($salon->id, $kod, 'musteri');
        if (!$k['sms'] && !$k['whatsapp']) return;
        if (empty($m->cep_telefon)) return;
        if ($k['whatsapp'] && !$k['sms'] && \App\Services\BildirimTercih::waYedekSms($salon->id)) {
            DersBildirimServisi::musteriyeGonder($salon, $m, $mesaj, $gonderimTipi);
            return;
        }
        try {
            $satir = [
                'to' => $m->cep_telefon,
                // SMS'te gonderici gorunmez: salon adi basa (DersBildirimServisi ile ayni)
                'message' => !empty($salon->salon_adi) ? $salon->salon_adi . ' - ' . $mesaj : $mesaj,
                'wa_message' => \App\Services\WhatsAppMesajFormat::uygulamaDavetiEk($mesaj, $salon, $m->id ?? null),
                'user_id' => $m->id ?? null, 'gonderim_tipi' => $gonderimTipi,
                'bk' => $kod, 'alici' => 'musteri', 'push_ayri' => true,
            ];
            $sms = \App\Services\BildirimTercih::dagit($salon, [$satir], $gonderimTipi);
            if ($sms) {
                app(\App\Http\Controllers\Controller::class)->sms_gonder($salon->id, [['to' => $sms[0]['to'], 'message' => $sms[0]['message']]]);
            }
        } catch (\Throwable $e) {
            Log::warning('[DERS-BILD] tercihli gonderim hatasi', ['salon_id' => $salon->id, 'kod' => $kod, 'err' => $e->getMessage()]);
        }
    }

    /**
     * Studyo modu: danisanin ODENMEMIS adisyonu var mi? Sinyal = adisyonlar.odendi != 1
     * ('Odeme Alindi' butonu basilmamis) VE gercek kalem (hizmet/paket/urun) iceren adisyon.
     *
     * NEDEN kalan DEGIL: studyoda fiyat 0'dir (fiyat gosterilmez/girilmez) -> kalan hep 0,
     * yani kalan bazli tespit hic tetiklenmez. Odeme durumu SADECE odendi bayragiyla
     * ('Odeme Alindi'/'Alinmadi') tutulur. (Uygulamanin studyo 'Odendi' gostergesi
     * isFullPaid=kalan<=0'a bakiyor -> fiyat 0 iken hepsini 'Odendi' gosteriyor; bu app
     * tarafi ayri bir bug. Web ise odendi'ye bakip dogru gosteriyor.)
     * 'Odeme Alindi' (tamOde) -> odendi=1 olunca false doner, hatirlatma durur.
     * Deploy'da migration yoksa false (guvenli).
     */
    private function musteriOdemeBekliyorMu($salonId, $userId): bool
    {
        if (!\Schema::hasColumn('adisyonlar', 'odendi')) return false;
        return DB::table('adisyonlar')
            ->where('salon_id', $salonId)
            ->where('user_id', $userId)
            ->whereRaw('COALESCE(odendi,0) <> 1')
            ->where(function ($q) {
                $q->whereExists(function ($s) {
                    $s->select(DB::raw(1))->from('adisyon_hizmetler')
                      ->whereRaw('adisyon_hizmetler.adisyon_id = adisyonlar.id');
                })->orWhereExists(function ($s) {
                    $s->select(DB::raw(1))->from('adisyon_paketler')
                      ->whereRaw('adisyon_paketler.adisyon_id = adisyonlar.id');
                })->orWhereExists(function ($s) {
                    $s->select(DB::raw(1))->from('adisyon_urunler')
                      ->whereRaw('adisyon_urunler.adisyon_id = adisyonlar.id');
                });
            })
            ->exists();
    }
}
