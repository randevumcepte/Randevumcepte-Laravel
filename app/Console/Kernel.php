<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\DBYedekAl::class,
        Commands\DiskTemizle::class,
        Commands\HizmetMaliyetKolonuEkle::class,
        Commands\GuvenlikBildir::class,
        Commands\GuvenlikDb::class,
        Commands\KurusReconcile::class,
        Commands\AlacakTemizle::class,
        Commands\IndirimKuponBackfill::class,
        Commands\MusteriDubleBul::class,
        Commands\YetkiTeshis::class,
        Commands\PersonelYetkiliTeshis::class,
        Commands\AramaRandevuAktar::class,
        Commands\SadakatPuanIsle::class,
        Commands\DamgaKartiBildirim::class,

        Commands\SMSGonder::class,
        Commands\RandevuSMSHatirlatma::class,
        Commands\DogumGunuSMSHatirlatma::class,
        Commands\AlacakSMSHatirlatma::class,

        Commands\KampanyaAramaYap::class,
        Commands\KampanyaSMSGonder::class,
        Commands\RandevuHatirlatmaAramasiYap::class,
        Commands\AlacakHatirlatmaAramasiYap::class,
        Commands\TekrarAramaHatirlat::class,
        Commands\PlanlaImport::class,
        Commands\PlanlaSeansRapor::class,
        Commands\DrklinikImport::class,
        Commands\SalonappyImport::class,
        Commands\SalonrandevuImport::class,
        Commands\PrimTeshis::class,
        Commands\SalonappySaticiDoldur::class,
        Commands\SalonappyDusumGuncelle::class,
        Commands\CarkHatirlatmaGonder::class,
        Commands\AnketOtomatikGonder::class,
        Commands\WhatsappStuckKurtar::class,
        Commands\WhatsappTestGonder::class,
        Commands\PushTeshis::class,
        Commands\WhatsappSalonTeshis::class,
        Commands\WhatsappKopruDurum::class,
        Commands\WhatsAppSmsRapor::class,
        Commands\SmsRapor::class,
        Commands\HatirlatmaNedenGitmedi::class,
        Commands\IlacHatirlatmalari::class,
        Commands\OlcumHatirlatmalari::class,
        Commands\SeansHatirlatma::class,
        Commands\DersHatirlatma::class,
        Commands\FormVarsayilanYay::class,
        Commands\SiriusFormKur::class,
        Commands\DermatouchFormKur::class,
        Commands\CerenCevizFormKur::class,
        Commands\HizmetVarsayilanYay::class,
        Commands\GuzellikSablonKur::class,
        Commands\EasistanIndexEnsure::class,
        Commands\WhatsappPromoKontrol::class,
        Commands\CagriMerkeziSchemaEnsure::class,
        Commands\AiCagriLoglari::class,
        Commands\MusteriKlonla::class,
        Commands\YetkiliGirisTeshis::class,
        Commands\TakvimKaybolmaTeshis::class,
        Commands\FlutterRandevuTeshis::class,
        Commands\CakismaTeshis::class,
        Commands\TahsilatTeshis::class,
        Commands\V2BildirimIsle::class,
        Commands\AsistanSohbetKaliplari::class,
        Commands\HizmetBilgiKaliplari::class,
        Commands\WaGeriKazanimCalistir::class,
        Commands\WaDenetciCalistir::class,
        Commands\WaTalepTakip::class,
        Commands\WaInsanDevam::class,
        Commands\SeslendirmeOnUret::class,
        Commands\PerformansNobetci::class,
    ];

    public function __construct(\Illuminate\Contracts\Foundation\Application $app, \Illuminate\Contracts\Events\Dispatcher $events)
    {
        // Defansif: bir komut dosyasi canli sunucuda eksikse (pull olmamissa)
        // tum Kernel cokuyordu ve cron komple duruyor (randevusms:hatirlat dahil).
        // Eksik class'lari filtrele, en azindan mevcut komutlar calismaya devam etsin.
        $this->commands = array_values(array_filter($this->commands, function ($cls) {
            return class_exists($cls);
        }));
        parent::__construct($app, $events);
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Performans nobetcisi: arka planda, diger dakikalik komutlarin bitmesini beklemesin.
        $schedule->command('performans:nobetci')->withoutOverlapping()->everyMinute()->runInBackground();

        // SMS komutları
        $schedule->command('sms:gonder')->withoutOverlapping()->everyMinute();
        $schedule->command('randevusms:hatirlat')->withoutOverlapping()->everyMinute();
        $schedule->command('dogumgunusms:hatirlat')->withoutOverlapping()->everyMinute();
        $schedule->command('alacaksms:hatirlat')->withoutOverlapping()->everyMinute();

        // Santral (FreePBX) hatırlatma aramaları — HatirlatmaAramaJob ile
        // `hatirlatmalar` kuyruğuna eklenir; bunları MEVCUT supervisor worker'ı
        // (randevumcepte-worker: `queue:work --queue=hatirlatmalar`, prod
        // QUEUE_DRIVER=database) işler. Yeni worker/program GEREKMEZ.
        // Detay: docs/SANTRAL_ARAMALARI.md
        //
        // randevuarama:yap N+1 (2026-06-29) salon ayar/bilgi cache'lenerek giderildi.
        $schedule->command('kampanyaarama:yap')->withoutOverlapping()->everyMinute();        // Reklam (kampanya)
        // randevuarama:yap ve alacakhatirlatma:aramayap ZAMANLAYICIDAN CIKARILDI (2026-11-16):
        // randevu aramasi randevusms:hatirlat icinde (1 gun once 15:00-18:00, WA/SMS ile ayni
        // dagitilmis dakikada), alacak aramasi alacaksms:hatirlat icinde (2 gun once 10:00).
        // Siniflar elle calistirma / geri donus icin duruyor.
        // $schedule->command('randevuarama:yap')->withoutOverlapping()->everyMinute();
        // $schedule->command('alacakhatirlatma:aramayap')->withoutOverlapping()->everyMinute();
        $schedule->command('arama:hatirlat')->withoutOverlapping()->everyMinute();
        // v2 (Randevumcepte-New-Laravel) randevu bildirim kuyrugu — ~55 sn yoklar, mevcut bildirim koduyla gonderir.
        $schedule->command('v2:bildirim-isle')->withoutOverlapping()->everyMinute();
        //$schedule->command('kampanyasms:gonder')->withoutOverlapping()->everyMinute();

        // Çarkıfelek hatırlatma — her 5 dakikada bir kontrol (saatlerin ±5dk penceresi)
        $schedule->command('cark:hatirlatma-gonder')->withoutOverlapping()->everyFiveMinutes();
        // Sadakat puani kazanimi (tahsilat + gelinen randevu -> puan; silinene geri al)
        $schedule->command('sadakat:puan-isle')->withoutOverlapping()->everyFiveMinutes();
        $schedule->command('damga:bildirim')->withoutOverlapping()->everyTenMinutes();

        // İlaç ve ölçüm hatırlatmaları — kullanıcının belirlediği saatte (HH:i) tetiklenir
        $schedule->command('ilac:hatirlatma-calistir')->withoutOverlapping()->everyMinute();
        // olcum:hatirlatma-calistir DEVRE DISI (2026-06-26): sistemde olcum hatirlatma
        // kullanilmiyor; her dakika WHERE'siz full-table-scan + satir basi log uretiyordu.
        // $schedule->command('olcum:hatirlatma-calistir')->withoutOverlapping()->everyMinute();

        // Memnuniyet anketi otomatik gönderim — randevu bitiş saatinde (MAX randevu_hizmetler.saat_bitis) tek sefer.
        $schedule->command('anket:otomatik-gonder')->withoutOverlapping()->everyMinute();

        // Seans hatırlatma — yarın yapılacak seansı 12:00'de tek seferlik push olarak müşteriye hatırlat.
        // Bildirim tıklanınca "Seanslarım" ekranı açılır (session_reminder -> sessions intent).
        $schedule->command('seans:hatirlat')->withoutOverlapping()->dailyAt('12:00');

        // Grup dersi (Pilates/kurs) hatırlatma — randevu hatırlatma ile aynı yapı:
        // salon.randevu_sms_hatirlatma saati kadar önce, WhatsApp öncelikli/SMS yedek.
        // Her dakika tetik anını kontrol eder (oturum başına atomik claim ile tek gönderim).
        $schedule->command('ders:hatirlat')->withoutOverlapping()->everyMinute();
        // Ders bitiminde salon "Geldi" dememisse musteriye "katildiniz mi?" push
        $schedule->command('ders:katilim-sor')->withoutOverlapping()->everyMinute();

        // WhatsApp kuyrukta takılı kalan mesajları SMS'e düşür — her 3 dakikada bir
        // Sebep: Node service RAM-only queue, restart/crash olunca mesajlar takılı kalıyordu
        $schedule->command('whatsapp:stuck-kurtar')->withoutOverlapping()->cron('*/3 * * * *');

        // E-Asistan sorgu index'lerini garanti et (idempotent). Deploy sadece
        // `git pull` yaptigi icin perf-index migration'i prod'da otomatik
        // calismiyordu; bu komut eksik index'leri kendiliginden olusturur.
        // Index'ler mevcutken sadece ucuz SHOW INDEX kontrolu (no-op).
        $schedule->command('easistan:index-ensure --quiet-noop')->withoutOverlapping()->hourly();

        // Cagri Merkezi (Call Center) semasini garanti et (idempotent). Deploy sadece
        // `git pull` yaptigi icin cagri merkezi migration'i prod'da otomatik calismiyor;
        // bu komut eksik kolon/tablolari kendiliginden olusturur. Her sey varken ucuz no-op.
        $schedule->command('cagrimerkezi:schema-ensure --quiet-noop')->withoutOverlapping()->hourly();

        // WhatsApp 2 ay ücretsiz promo — günde bir: promosu başlat + süresi dolanı kapat
        $schedule->command('whatsapp:promo-kontrol')->withoutOverlapping()->dailyAt('00:30');

        // Asistan Egitimi kalip kutuphaneleri (sohbet + hizmet bilgilendirme) — surum
        // degisince OTOMATIK uygular. Ayni surumse ucuz no-op. Boylece deploy sonrasi
        // elle "artisan asistan:*-kaliplari" calistirmaya GEREK KALMAZ.
        $schedule->command('asistan:sohbet-kaliplari --quiet-noop')->withoutOverlapping()->everyTenMinutes();
        $schedule->command('asistan:hizmet-kaliplari --quiet-noop')->withoutOverlapping()->everyTenMinutes();

        // WhatsApp asistani: kacan musteriyi geri kazanma (10:00-20:00, mesai icinde gonderir) + takip + eski fotograf silme
        $schedule->command('wa:geri-kazanim')->withoutOverlapping()->hourlyAt(17);

        // WhatsApp asistani Gece Denetcisi: sohbetleri incele (Batch API) -> sonuclari isle + ders uret/test -> haftalik ozet
        // 2026-11-20 KAPALI (kullanici karari: Sonnet incelemesi bosa token; sohbetleri panelden kendisi izliyor).
        // Ogrenilmis dersler kullanilmaya devam eder; elle "Denetimi baslat" (sistem yoneticisi) calisir. Acmak: .env WA_DENETCI_GECE=1
        if (config('services.wa_asistan.denetci_gece')) {
            $schedule->command('wa:denetci gonder')->withoutOverlapping()->dailyAt('03:00');
            $schedule->command('wa:denetci ozet')->withoutOverlapping()->weeklyOn(1, '09:30');
        }
        // topla acik kalir: elle baslatilan / yolda olan denetimin sonucu islenir (yeni denetim gondermez)
        $schedule->command('wa:denetci topla')->withoutOverlapping()->everyThirtyMinutes();
        // Isletmeye sorulan konular: 15 dk hatirlatma, 45 dk asistan musteriye doner, 24 saat cevapsiz (musteri havada kalmaz)
        $schedule->command('wa:talep-takip')->withoutOverlapping()->everyFiveMinutes();
        // Personel yazdi ama sonra sustu: 5 dk sonra asistan musteriye doner (her dakika kontrol)
        $schedule->command('wa:insan-devam')->withoutOverlapping()->everyMinute();

        // Yedek
        $schedule->command('dbyedek:al')->dailyAt('23:59')->withoutOverlapping();

        // Disk sismesi onleme: yedek rotasyonu + eski oturum + sisen log kirpma.
        // Sisen pm2/whatsapp loglari GB'larca buyuyebildigi icin saat basi kontrol.
        $schedule->command('disk:temizle')->hourly()->withoutOverlapping();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
