<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPersonelMaasOdemesiIdToMasraflar extends Migration
{
    public function up()
    {
        if (Schema::hasTable('masraflar') && !Schema::hasColumn('masraflar', 'personel_maas_odemesi_id')) {
            Schema::table('masraflar', function (Blueprint $table) {
                $table->unsignedInteger('personel_maas_odemesi_id')->nullable()->after('salon_id');
                $table->index('personel_maas_odemesi_id', 'masraflar_pmo_idx');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('masraflar') && Schema::hasColumn('masraflar', 'personel_maas_odemesi_id')) {
            Schema::table('masraflar', function (Blueprint $table) {
                $table->dropIndex('masraflar_pmo_idx');
                $table->dropColumn('personel_maas_odemesi_id');
            });
        }
    }
}
