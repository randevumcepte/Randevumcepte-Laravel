<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HizmetHavuzuTemizlik extends Migration
{
    public function up()
    {
        Schema::table('hizmetler', function (Blueprint $table) {
            if (!Schema::hasColumn('hizmetler', 'silindi')) {
                $table->boolean('silindi')->default(false)->index();
            }
            if (!Schema::hasColumn('hizmetler', 'silindi_at')) {
                $table->timestamp('silindi_at')->nullable();
            }
            if (!Schema::hasColumn('hizmetler', 'birlesti_id')) {
                // Bir hizmet baska bir hizmete birlestirildiyse hedefin id'si
                $table->unsignedBigInteger('birlesti_id')->nullable()->index();
            }
            if (!Schema::hasColumn('hizmetler', 'salon_turu_id')) {
                // Hangi sektorun havuzuna ait — null = henuz atanmamis
                $table->unsignedBigInteger('salon_turu_id')->nullable()->index();
            }
            if (!Schema::hasColumn('hizmetler', 'normalized_ad')) {
                // Turkce karakter + bosluk + buyuk/kucuk normalize edilmis isim — duplicate tespiti icin
                $table->string('normalized_ad', 200)->nullable()->index();
            }
        });

        if (!Schema::hasTable('hizmet_merge_log')) {
            Schema::create('hizmet_merge_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('ana_hizmet_id')->index();
                $table->unsignedBigInteger('birlestirilen_hizmet_id')->index();
                $table->string('ana_hizmet_adi', 200)->nullable();
                $table->string('birlestirilen_hizmet_adi', 200)->nullable();
                $table->json('etkilenen_tablolar')->nullable();
                $table->unsignedBigInteger('yapan_admin_id')->nullable();
                $table->text('not')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('hizmet_merge_log');

        Schema::table('hizmetler', function (Blueprint $table) {
            foreach (['silindi','silindi_at','birlesti_id','salon_turu_id','normalized_ad'] as $c) {
                if (Schema::hasColumn('hizmetler', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
}
