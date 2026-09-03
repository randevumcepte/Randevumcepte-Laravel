<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

/**
 * randevual.dart (müşteri randevu alma) akışının BACKEND karşılığı için kapsamlı
 * çakışma/müsaitlik testleri. Asıl mantık burada (ApiController):
 *   - randevuTarihSaatAdimi        → müsait saat hesabı
 *   - randevuekleguncelle          → kayıt + çakışma engelleri
 *   - odaMusaitlikCakismasi        → oda sert engeli
 *   - kaynak_cakisma_kontrol_api   → personel/oda/cihaz çakışma
 *
 * Bellek-içi sqlite + yalnız ilgili tablolar. Çakışma tespiti kayıt döngüsünden
 * ÖNCE return ile olduğu için adisyon/tahsilat tablolarına gerek yoktur.
 */
class RandevuCakismaTest extends TestCase
{
    /** @var int */
    private $salonId = 1;
    /** @var string yyyy-mm-dd — sabit gelecek tarih (simdikiZaman filtresine takılmasın) */
    private $tarih = '2026-09-10';
    /** @var int haftanın günü (1=Pzt..7=Paz) */
    private $gun;

    public function setUp()
    {
        parent::setUp();

        // Bellek-içi sqlite'a geç
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');

        $this->gun = (int) date('N', strtotime($this->tarih));
        $this->semaKur();
        $this->temelVeri();
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  ŞEMA (yalnız ilgili tablolar)
    // ─────────────────────────────────────────────────────────────────────────
    private function semaKur(): void
    {
        $s = Schema::connection('sqlite');

        $s->create('salonlar', function (Blueprint $t) {
            $t->increments('id');
            $t->string('app_bundle')->nullable();
            $t->string('name')->nullable();
            $t->integer('randevu_saat_araligi')->nullable();
            $t->integer('cakisma_uyarisi_aktif')->nullable();
            $t->integer('online_saat_kisitlama_aktif')->nullable();
            $t->integer('online_gunluk_slot_limiti')->nullable();
        });

        $s->create('salon_calisma_saatleri', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('salon_id');
            $t->integer('haftanin_gunu');
            $t->integer('calisiyor')->default(1);
            $t->string('baslangic_saati');
            $t->string('bitis_saati');
        });

        $s->create('personel_calisma_saatleri', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('personel_id');
            $t->integer('haftanin_gunu');
            $t->integer('calisiyor')->default(1);
            $t->string('baslangic_saati');
            $t->string('bitis_saati');
        });

        $s->create('personel_mola_saatleri', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('personel_id');
            $t->integer('haftanin_gunu');
            $t->integer('mola_var')->default(1);
            $t->string('baslangic_saati');
            $t->string('bitis_saati');
        });

        $s->create('salon_personelleri', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('salon_id');
            $t->string('personel_adi')->nullable();
            $t->integer('aktif')->default(1);
        });

        $s->create('hizmetler', function (Blueprint $t) {
            $t->increments('id');
            $t->string('hizmet_adi')->nullable();
        });

        $s->create('salon_sunulan_hizmetler', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('salon_id');
            $t->integer('hizmet_id');
            $t->integer('sure_dk')->nullable();
        });

        $s->create('randevular', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('user_id')->nullable();
            $t->integer('salon_id');
            $t->string('tarih');
            $t->string('saat')->nullable();
            $t->string('saat_bitis')->nullable();
            $t->integer('durum')->default(0);
            $t->integer('uygulama')->nullable();
            $t->integer('salon')->nullable();
            $t->integer('web')->nullable();
            $t->integer('olusturan_personel_id')->nullable();
            $t->integer('olusturan_user_id')->nullable();
            $t->integer('on_gorusme_id')->nullable();
            $t->integer('easistan')->nullable();
            $t->text('personel_notu')->nullable();
        });

        $s->create('randevu_hizmetler', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('randevu_id');
            $t->integer('hizmet_id')->nullable();
            $t->integer('personel_id')->nullable();
            $t->integer('cihaz_id')->nullable();
            $t->integer('oda_id')->nullable();
            $t->integer('sure_dk')->nullable();
            $t->string('fiyat')->nullable();
            $t->string('saat')->nullable();
            $t->string('saat_bitis')->nullable();
            $t->integer('yardimci_personel')->nullable();
            $t->integer('dusum_miktari')->nullable();
        });

        $s->create('odalar', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('salon_id');
            $t->string('oda_adi')->nullable();
            $t->integer('personel_id')->nullable();
            $t->integer('aktifmi')->nullable();
            $t->integer('takvim_sirasi')->nullable();
        });

        $s->create('oda_sunulan_hizmetler', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('salon_id');
            $t->integer('oda_id');
            $t->integer('hizmet_id');
        });

        $s->create('cihazlar', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('salon_id');
            $t->string('cihaz_adi')->nullable();
            $t->integer('aktifmi')->nullable();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TEMEL VERİ: 1 salon (09:00-20:00), 2 personel, 1 hizmet (50 dk)
    // ─────────────────────────────────────────────────────────────────────────
    private function temelVeri(): void
    {
        DB::table('salonlar')->insert([
            'id' => $this->salonId, 'app_bundle' => 'test.bundle', 'name' => 'Test Salon',
            'randevu_saat_araligi' => 15,
            'cakisma_uyarisi_aktif' => 0,           // varsayılan KAPALI (bağımsızlığı test edeceğiz)
            'online_saat_kisitlama_aktif' => 0,     // kürasyon kapalı
            'online_gunluk_slot_limiti' => 0,
        ]);

        DB::table('salon_calisma_saatleri')->insert([
            'salon_id' => $this->salonId, 'haftanin_gunu' => $this->gun,
            'calisiyor' => 1, 'baslangic_saati' => '09:00', 'bitis_saati' => '20:00',
        ]);

        // Personel 10 ve 20
        foreach ([10, 20] as $pid) {
            DB::table('salon_personelleri')->insert([
                'id' => $pid, 'salon_id' => $this->salonId, 'personel_adi' => 'Personel ' . $pid, 'aktif' => 1,
            ]);
            DB::table('personel_calisma_saatleri')->insert([
                'personel_id' => $pid, 'haftanin_gunu' => $this->gun,
                'calisiyor' => 1, 'baslangic_saati' => '09:00', 'bitis_saati' => '20:00',
            ]);
        }

        // Hizmet 100 (50 dk)
        DB::table('hizmetler')->insert(['id' => 100, 'hizmet_adi' => 'Test Hizmet']);
        DB::table('salon_sunulan_hizmetler')->insert([
            'salon_id' => $this->salonId, 'hizmet_id' => 100, 'sure_dk' => 50,
        ]);
    }

    /** Var olan bir randevu + tek hizmet satırı ekler. */
    private function randevuEkle(int $personelId, string $saat, string $saatBitis, int $durum, ?int $odaId = null): int
    {
        $rid = DB::table('randevular')->insertGetId([
            'user_id' => 999, 'salon_id' => $this->salonId, 'tarih' => $this->tarih,
            'saat' => $saat, 'saat_bitis' => $saatBitis, 'durum' => $durum,
        ]);
        DB::table('randevu_hizmetler')->insert([
            'randevu_id' => $rid, 'hizmet_id' => 100, 'personel_id' => $personelId,
            'oda_id' => $odaId, 'sure_dk' => (strtotime($saatBitis) - strtotime($saat)) / 60,
            'saat' => $saat, 'saat_bitis' => $saatBitis, 'fiyat' => '0',
        ]);
        return $rid;
    }

    private function controller(): ApiController
    {
        return new ApiController();
    }

    /** randevuTarihSaatAdimi çağır, boş saatlerin listesini (['09:00',...]) döndür. */
    private function musaitSaatler(array $personeller, array $hizmetler): array
    {
        $req = Request::create('/api/v1/randevuTarihSaatAdimi', 'POST', [
            'sube' => (string) $this->salonId,
            'personeller' => $personeller,
            'secilenhizmetler' => $hizmetler,
            'randevutarihi' => $this->tarih,
            'appBundle' => 'test.bundle',
        ]);
        $res = $this->controller()->randevuTarihSaatAdimi($req);
        if (!isset($res['saatler']) || !is_array($res['saatler'])) return [];
        $bos = array_filter($res['saatler'], function ($x) { return $x['dolu'] === '0'; });
        return array_values(array_map(function ($x) { return $x['saat']; }, $bos));
    }

    /** randevuekleguncelle çağır (app kaynaklı, tek hizmet). Dönen diziyi ver. */
    private function appRandevuEkle(int $personelId, string $saat, string $cakisanEkle = ''): array
    {
        $req = Request::create('/api/v1/randevuekleguncelle', 'POST', [
            'salonid' => (string) $this->salonId,
            'user_id' => 500,
            'randevu_id' => '',
            'randevu_tarihi' => $this->tarih,
            'randevu_saati' => $saat,
            'hizmetler' => [[
                'hizmet_id' => 100, 'personel_id' => (string) $personelId,
                'oda_id' => '', 'cihaz_id' => '', 'sure_dk' => 50, 'fiyat' => '0',
            ]],
            'notlar' => '',
            'randevuKaynak' => 'uygulama',
            'durum' => '0',
            'cakisanrandevuekle' => $cakisanEkle,
            'appBundle' => 'test.bundle',
        ]);
        return (array) $this->controller()->randevuekleguncelle($req);
    }

    private function cakismaAyariAc(): void
    {
        DB::table('salonlar')->where('id', $this->salonId)->update(['cakisma_uyarisi_aktif' => 1]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  A) MÜSAİTLİK (randevuTarihSaatAdimi)
    // ═════════════════════════════════════════════════════════════════════════

    /** REGRESYON: başka personelin randevusu, seçili personeli bloklamamalı. */
    public function test_musaitlik_sadece_secili_personeli_dikkate_alir()
    {
        // Personel 20'nin 10:00-10:50 randevusu (onaylı)
        $this->randevuEkle(20, '10:00', '10:50', 1);

        // Personel 10 için müsaitlik istiyoruz — 10:00 BOŞ olmalı
        $bos = $this->musaitSaatler([10], [100]);

        $this->assertContains('10:00', $bos, '10:00 personel 10 için boş olmalı (personel 20 doluyken)');
    }

    /** Seçili personelin kendi randevusu overlap eden başlangıçları bloklamalı. */
    public function test_musaitlik_dolu_araligi_bloklar()
    {
        $this->randevuEkle(10, '10:00', '10:50', 1);

        $bos = $this->musaitSaatler([10], [100]);

        // 50 dk hizmet: [09:30,10:20) 10:00'i içerir → dolu; [10:00..10:45] dolu
        $this->assertNotContains('09:30', $bos, '09:30 (→10:20) 10:00 randevusuyla çakışır');
        $this->assertNotContains('10:00', $bos);
        $this->assertNotContains('10:15', $bos);
        $this->assertNotContains('10:45', $bos, '10:45 (→11:35) hâlâ 10:50 içine girer');
        // 11:00 (→11:50) serbest olmalı
        $this->assertContains('11:00', $bos);
    }

    /** Personel molası (16:30-20:00) müsaitliği bloklamalı. */
    public function test_musaitlik_personel_molasi_bloklar()
    {
        DB::table('personel_mola_saatleri')->insert([
            'personel_id' => 10, 'haftanin_gunu' => $this->gun,
            'mola_var' => 1, 'baslangic_saati' => '16:30', 'bitis_saati' => '20:00',
        ]);

        $bos = $this->musaitSaatler([10], [100]);

        foreach ($bos as $saat) {
            $this->assertTrue($saat < '16:30', "Mola içi/aşan slot müsait görünmemeli: $saat");
        }
        $this->assertContains('16:00', $bos, '16:00 (→16:50) hâlâ molaya girer → OLMAMALI');
    }

    /** Çoklu hizmet: toplam süre kadar pencere overlap kontrolü. */
    public function test_musaitlik_coklu_hizmet_toplam_sureyle_bloklar()
    {
        // 2. hizmet 100 daha ekleyelim: toplam 100 dk
        $this->randevuEkle(10, '11:00', '11:50', 1);

        // iki kez 100 → toplam 100 dk
        $bos = $this->musaitSaatler([10, 10], [100, 100]);

        // 10:15 (→11:55) 11:00 randevusuna girer → dolu
        $this->assertNotContains('10:15', $bos);
        // 09:00 (→10:40) serbest
        $this->assertContains('09:00', $bos);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  B) KAYIT ÇAKIŞMA ENGELİ (randevuekleguncelle, app kaynağı)
    // ═════════════════════════════════════════════════════════════════════════

    /** ANA VAKA: app, ONAYLI randevunun üstüne randevu OLUŞTURAMAZ (ayar kapalı bile olsa). */
    public function test_app_onayli_randevu_ustune_cakisma_verir_ayar_kapali()
    {
        $this->randevuEkle(10, '10:00', '10:50', 1); // onaylı mevcut

        $res = $this->appRandevuEkle(10, '10:30'); // overlap

        $this->assertArrayHasKey('cakismavar', $res);
        $this->assertEquals('1', $res['cakismavar'], 'App çakışan randevu OLUŞTURMAMALI (ayar kapalı olsa da)');
    }

    /** Ayar AÇIK iken de aynı şekilde engellenir. */
    public function test_app_cakisma_ayari_acik_da_engeller()
    {
        $this->cakismaAyariAc();
        $this->randevuEkle(10, '10:00', '10:50', 1);

        $res = $this->appRandevuEkle(10, '10:30');

        $this->assertEquals('1', $res['cakismavar']);
    }

    /** BEKLEYEN (durum=0) randevu da app için çakışma sayılır. */
    public function test_app_bekleyen_randevu_da_cakisma_sayilir()
    {
        $this->randevuEkle(10, '10:00', '10:50', 0); // bekleyen mevcut

        $res = $this->appRandevuEkle(10, '10:15');

        $this->assertEquals('1', $res['cakismavar'], 'Bekleyen randevu da çakışma olmalı');
    }

    /** App "yine de oluştur" (cakisanrandevuekle=1) ile çakışmayı OVERRIDE EDEMEZ. */
    public function test_app_cakismayi_override_edemez()
    {
        $this->randevuEkle(10, '10:00', '10:50', 1);

        $res = $this->appRandevuEkle(10, '10:30', '1'); // yine de oluştur

        $this->assertEquals('1', $res['cakismavar'], 'App override edememeli (sert engel)');
    }

    /** Farklı personelde aynı saat çakışma DEĞİLDİR (engellenmemeli). */
    public function test_farkli_personel_ayni_saat_cakisma_degil()
    {
        $this->cakismaAyariAc();
        $this->randevuEkle(20, '10:00', '10:50', 1); // personel 20 dolu

        $res = $this->appRandevuEkle(10, '10:00'); // personel 10 boş

        $this->assertNotEquals('1', $res['cakismavar'] ?? '0', 'Farklı personel çakışma sayılmamalı');
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  C) ODA SERT ENGELİ (odaMusaitlikCakismasi)
    // ═════════════════════════════════════════════════════════════════════════

    /** Hizmete atanmış tek oda o saatte doluysa app engellenir (ayardan bağımsız). */
    public function test_oda_dolu_ise_app_engellenir()
    {
        // Oda 1, hizmet 100'e atanmış, aktif
        DB::table('odalar')->insert([
            'id' => 1, 'salon_id' => $this->salonId, 'oda_adi' => 'Oda 1',
            'personel_id' => null, 'aktifmi' => 1, 'takvim_sirasi' => 1,
        ]);
        DB::table('oda_sunulan_hizmetler')->insert([
            'salon_id' => $this->salonId, 'oda_id' => 1, 'hizmet_id' => 100,
        ]);
        // Personel 20 o odayı 10:00-10:50 kullanmış
        $this->randevuEkle(20, '10:00', '10:50', 1, 1);

        // Personel 10 aynı hizmet için 10:30 istiyor → tek oda dolu → engel
        $res = $this->appRandevuEkle(10, '10:30');

        $this->assertEquals('1', $res['cakismavar'], 'Tek uygun oda doluyken app engellenmeli');
    }

    /** İki oda varsa biri boşken engel YOK (boş odaya atanır). */
    public function test_iki_oda_biri_bos_ise_engel_yok()
    {
        foreach ([1, 2] as $oid) {
            DB::table('odalar')->insert([
                'id' => $oid, 'salon_id' => $this->salonId, 'oda_adi' => 'Oda ' . $oid,
                'personel_id' => null, 'aktifmi' => 1, 'takvim_sirasi' => $oid,
            ]);
            DB::table('oda_sunulan_hizmetler')->insert([
                'salon_id' => $this->salonId, 'oda_id' => $oid, 'hizmet_id' => 100,
            ]);
        }
        // Sadece oda 1 dolu
        $this->randevuEkle(20, '10:00', '10:50', 1, 1);

        $res = $this->appRandevuEkle(10, '10:30');

        $this->assertNotEquals('1', $res['cakismavar'] ?? '0', 'Boş oda (2) varken engel olmamalı');
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  D) SALON/WEB davranışı korunmuş mu (ayar bağımlı, override edilebilir)
    // ═════════════════════════════════════════════════════════════════════════

    /** Salon kaynağı + ayar KAPALI → kaynak_cakisma_kontrol boş döner (engel yok). */
    public function test_salon_ayar_kapali_kaynak_kontrol_bos()
    {
        $this->randevuEkle(10, '10:00', '10:50', 1);

        $m = new \ReflectionMethod(ApiController::class, 'kaynak_cakisma_kontrol_api');
        $m->setAccessible(true);

        $req = Request::create('/x', 'POST', [
            'randevu_saati' => '10:30', 'randevu_id' => '',
            'hizmetler' => [['hizmet_id' => 100, 'personel_id' => '10', 'oda_id' => '', 'cihaz_id' => '', 'sure_dk' => 50]],
        ]);
        // ayarZorunlu=true, sadeceOnayli=true (salon/web modu)
        $msg = $m->invoke($this->controller(), $req, [$this->tarih], $this->salonId, true, true);

        $this->assertSame('', $msg, 'Ayar kapalıyken salon/web kaynak kontrolü boş dönmeli');
    }

    /** Salon kaynağı + ayar AÇIK + onaylı çakışma → mesaj döner. */
    public function test_salon_ayar_acik_kaynak_kontrol_cakisma_bulur()
    {
        $this->cakismaAyariAc();
        $this->randevuEkle(10, '10:00', '10:50', 1);

        $m = new \ReflectionMethod(ApiController::class, 'kaynak_cakisma_kontrol_api');
        $m->setAccessible(true);
        $req = Request::create('/x', 'POST', [
            'randevu_saati' => '10:30', 'randevu_id' => '',
            'hizmetler' => [['hizmet_id' => 100, 'personel_id' => '10', 'oda_id' => '', 'cihaz_id' => '', 'sure_dk' => 50]],
        ]);
        $msg = $m->invoke($this->controller(), $req, [$this->tarih], $this->salonId, true, true);

        $this->assertNotSame('', $msg, 'Ayar açıkken onaylı çakışma bulunmalı');
    }
}
