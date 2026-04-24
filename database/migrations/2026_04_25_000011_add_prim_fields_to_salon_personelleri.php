<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrimFieldsToSalonPersonelleri extends Migration
{
    public function up()
    {
        Schema::table('salon_personelleri', function (Blueprint $table) {
            if (!Schema::hasColumn('salon_personelleri', 'maas')) {
                $table->decimal('maas', 12, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('salon_personelleri', 'hizmet_prim_yuzde')) {
                $table->decimal('hizmet_prim_yuzde', 5, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('salon_personelleri', 'urun_prim_yuzde')) {
                $table->decimal('urun_prim_yuzde', 5, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('salon_personelleri', 'paket_prim_yuzde')) {
                $table->decimal('paket_prim_yuzde', 5, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('salon_personelleri', 'unvan')) {
                $table->string('unvan', 150)->nullable();
            }
            if (!Schema::hasColumn('salon_personelleri', 'cinsiyet')) {
                $table->tinyInteger('cinsiyet')->nullable();
            }
        });
    }

    public function down()
    {
        // Prod verisi korumak icin down bos birakildi.
    }
}
