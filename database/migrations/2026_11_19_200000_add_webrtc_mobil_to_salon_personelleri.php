<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mobil uygulama softphone'u icin AYRI WebRTC dahilisi.
 * dahili_no_webrtc / dahili_sifre_webrtc = web paneldeki tarayici softphone'u (aynen kalir).
 * dahili_no_webrtc_mobil / dahili_sifre_webrtc_mobil = uygulama ici softphone; ikisi de doluysa
 * uygulama bu dahiliyle santrale kaydolur ve 'Ara' butonlari uygulama icinden arar.
 */
class AddWebrtcMobilToSalonPersonelleri extends Migration
{
    public function up()
    {
        Schema::table('salon_personelleri', function (Blueprint $t) {
            if (!Schema::hasColumn('salon_personelleri', 'dahili_no_webrtc_mobil')) {
                $t->string('dahili_no_webrtc_mobil', 20)->nullable();
            }
            if (!Schema::hasColumn('salon_personelleri', 'dahili_sifre_webrtc_mobil')) {
                $t->string('dahili_sifre_webrtc_mobil', 100)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('salon_personelleri', function (Blueprint $t) {
            foreach (['dahili_no_webrtc_mobil', 'dahili_sifre_webrtc_mobil'] as $k) {
                if (Schema::hasColumn('salon_personelleri', $k)) $t->dropColumn($k);
            }
        });
    }
}
