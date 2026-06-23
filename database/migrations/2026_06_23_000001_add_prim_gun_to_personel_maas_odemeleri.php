<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrimGunToPersonelMaasOdemeleri extends Migration
{
    public function up()
    {
        Schema::table('personel_maas_odemeleri', function (Blueprint $table) {
            if (!Schema::hasColumn('personel_maas_odemeleri', 'prim_gun')) {
                // Gunluk prim odemesinin ait oldugu gun (sadece gunluk prim odemelerinde dolu)
                $table->date('prim_gun')->nullable()->after('donem');
                $table->index(['salon_id', 'personel_id', 'prim_gun']);
            }
        });
    }

    public function down()
    {
        // Prod verisi korumak icin down bos birakildi.
    }
}
