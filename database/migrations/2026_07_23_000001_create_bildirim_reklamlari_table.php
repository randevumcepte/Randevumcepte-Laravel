<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bildirim Reklamlari — salonun kendi musterilerine gonderdigi RESIMLI,
 * tiklanabilir reklamlar. Mevcut karmasik "kampanya_yonetimi" (SMS/arama)
 * sisteminden AYRI, sade bir modul.
 *
 * Kanallar: Push (uygulama bildirimi) + In-app (uygulama-ici kart).
 * Ana senaryo: musteri gorsele DOKUNUNCA hesabina indirim KUPONU tanimlanir
 * (carkifelek_odulleri tablosuna kayit) — TEK DOKUNUS = aninda kupon.
 *
 * Idempotent: tablo varsa no-op.
 */
class CreateBildirimReklamlariTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('bildirim_reklamlari')) {
            return;
        }

        Schema::create('bildirim_reklamlari', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('salon_id');

            // Icerik
            $table->string('baslik', 191);              // push baslik / kart baslik
            $table->text('mesaj')->nullable();          // push govde / kart aciklama
            $table->string('gorsel')->nullable();       // in-app kart gorseli (public yol)

            // Tur: kampanya|bos_slot|yeni_hizmet|geri_kazanim|ozel_gun|sadakat|etkinlik
            $table->string('tur', 30)->default('kampanya');

            // Kanallar
            $table->boolean('kanal_push')->default(true);
            $table->boolean('kanal_inapp')->default(true);

            // Tiklama aksiyonu: kupon|randevu|hizmet|sayfa|yok
            $table->string('aksiyon_tipi', 20)->default('kupon');
            $table->string('aksiyon_hedef')->nullable(); // randevu/hizmet -> hizmet_id, sayfa -> url

            // Kupon ayarlari (aksiyon_tipi = kupon)
            $table->string('kupon_indirim_tipi', 10)->nullable();  // yuzde | tutar
            $table->decimal('kupon_deger', 10, 2)->nullable();
            $table->unsignedInteger('kupon_hizmet_id')->nullable(); // null = tum hizmetler
            $table->string('kupon_baslik', 191)->nullable();
            $table->unsignedSmallInteger('kupon_gecerlilik_gun')->nullable(); // null = suresiz
            $table->unsignedSmallInteger('kupon_kisi_limit')->default(1);     // kisi basi kap adedi
            $table->unsignedInteger('kupon_toplam_adet')->nullable();         // null = sinirsiz
            $table->unsignedInteger('kupon_dagitilan')->default(0);           // kac kisi kapti

            // Hedefleme
            $table->string('hedef_kitle', 20)->default('tumu'); // tumu | segment
            $table->text('hedef_kosul')->nullable();            // JSON {gelmeyen_gun, hizmet_id, dogum_ay}

            // Yayin
            $table->string('durum', 15)->default('taslak');     // taslak|aktif|pasif|bitti
            $table->dateTime('yayin_baslangic')->nullable();
            $table->dateTime('yayin_bitis')->nullable();
            $table->boolean('push_gonderildi')->default(false);
            $table->timestamps();

            $table->index(['salon_id', 'durum'], 'br_salon_durum_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bildirim_reklamlari');
    }
}
