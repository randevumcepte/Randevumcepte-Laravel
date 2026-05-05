<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdemeTipiToPersonelMaasOdemeleri extends Migration
{
    public function up()
    {
        if (Schema::hasTable('personel_maas_odemeleri') && !Schema::hasColumn('personel_maas_odemeleri', 'odeme_tipi')) {
            Schema::table('personel_maas_odemeleri', function (Blueprint $table) {
                // 'maas' | 'prim' | 'diger' (eski kayitlar 'diger' olarak kalir)
                $table->string('odeme_tipi', 20)->default('diger')->after('tutar');
                $table->index(['salon_id', 'personel_id', 'donem', 'odeme_tipi'], 'pmo_arama_idx');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('personel_maas_odemeleri') && Schema::hasColumn('personel_maas_odemeleri', 'odeme_tipi')) {
            Schema::table('personel_maas_odemeleri', function (Blueprint $table) {
                $table->dropIndex('pmo_arama_idx');
                $table->dropColumn('odeme_tipi');
            });
        }
    }
}
