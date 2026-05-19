<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * oda_sunulan_hizmetler: hangi odada hangi hizmetlerin verildigini tutar.
 * personel_sunulan_hizmetler ve cihaz_sunulan_hizmetler ile ayni desende
 * cok-cok iliski. Paket satislarinda her hizmet icin uygun odanin otomatik
 * secilmesini saglar, oda bazli takvimde her hizmetin dogru odaya dusmesini
 * mumkun kilar.
 */
class CreateOdaSunulanHizmetlerTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('oda_sunulan_hizmetler')) {
            Schema::create('oda_sunulan_hizmetler', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('salon_id');
                $table->unsignedBigInteger('oda_id');
                $table->unsignedBigInteger('hizmet_id');
                $table->timestamps();

                $table->index('salon_id', 'osh_salon_idx');
                $table->index('oda_id', 'osh_oda_idx');
                $table->index('hizmet_id', 'osh_hizmet_idx');
                $table->unique(['oda_id', 'hizmet_id'], 'osh_oda_hizmet_uq');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('oda_sunulan_hizmetler');
    }
}
