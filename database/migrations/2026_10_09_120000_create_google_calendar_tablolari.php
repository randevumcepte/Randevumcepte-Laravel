<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleCalendarTablolari extends Migration
{
    public function up()
    {
        // Her isletme personeli (yetkili_id) kendi Google hesabini baglayabilir.
        // Push: randevu_hizmetler.personel_id -> yetkili_id -> baglanti -> Google Calendar API.
        Schema::create('google_calendar_baglantilar', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('yetkili_id')->unsigned();     // isletmeyetkilileri.id
            $t->integer('salon_id')->unsigned();
            $t->string('google_email', 191);
            $t->string('calendar_id', 191)->default('primary');
            $t->text('access_token');
            $t->text('refresh_token')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->string('scope', 512)->nullable();
            $t->tinyInteger('aktif')->default(1);
            $t->timestamp('son_hata_zamani')->nullable();
            $t->text('son_hata_mesaji')->nullable();
            $t->timestamps();

            $t->unique(['yetkili_id', 'salon_id'], 'gcb_yet_salon_uq');
            $t->index('salon_id');
        });

        // Randevu hizmet satiri <-> Google event id eslemesi.
        // Guncelleme/silmede ayni event_id ile PATCH/DELETE yapmak icin.
        Schema::create('google_calendar_event_eslemeleri', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('baglanti_id')->unsigned();     // google_calendar_baglantilar.id
            $t->integer('randevu_hizmet_id')->unsigned(); // randevu_hizmetler.id
            $t->string('google_event_id', 191);
            $t->timestamp('son_sync_zamani')->nullable();
            $t->timestamps();

            $t->unique(['baglanti_id', 'randevu_hizmet_id'], 'gce_bag_rh_uq');
            $t->index('randevu_hizmet_id');
            $t->index('google_event_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('google_calendar_event_eslemeleri');
        Schema::dropIfExists('google_calendar_baglantilar');
    }
}
