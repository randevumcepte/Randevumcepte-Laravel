<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonelPrimOranlariVeDetayliFlag extends Migration
{
    public function up()
    {
        // Kalem (tek tek hizmet/urun/paket) bazli prim oranlari
        if (!Schema::hasTable('personel_prim_oranlari')) {
            Schema::create('personel_prim_oranlari', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('personel_id');
                $table->unsignedInteger('salon_id');
                $table->enum('tur', ['hizmet', 'urun', 'paket']);
                $table->unsignedInteger('kalem_id'); // hizmet_id / urun_id / paket_id
                $table->decimal('yuzde', 5, 2)->default(0);
                $table->timestamps();

                $table->unique(['personel_id', 'tur', 'kalem_id'], 'personel_prim_kalem_unique');
                $table->index(['salon_id', 'personel_id']);
            });
        }

        // Her tur icin bagimsiz "detayli prim" anahtari (default kapali — geriye uyumlu)
        Schema::table('salon_personelleri', function (Blueprint $table) {
            if (!Schema::hasColumn('salon_personelleri', 'hizmet_prim_detayli')) {
                $table->tinyInteger('hizmet_prim_detayli')->default(0);
            }
            if (!Schema::hasColumn('salon_personelleri', 'urun_prim_detayli')) {
                $table->tinyInteger('urun_prim_detayli')->default(0);
            }
            if (!Schema::hasColumn('salon_personelleri', 'paket_prim_detayli')) {
                $table->tinyInteger('paket_prim_detayli')->default(0);
            }
        });
    }

    public function down()
    {
        // Prod verisi korumak icin down bos birakildi.
    }
}
