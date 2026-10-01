<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * salon_uyelik_odemeleri'ne "KDV dahil mi" bayragi. Isaretlendiginde yetkiliye giden
 * bilgilendirme metninde tutarin soluna "KDV dahil" yazilir.
 */
class AddKdvDahilToSalonUyelikOdemeleri extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salon_uyelik_odemeleri')
            && !Schema::hasColumn('salon_uyelik_odemeleri', 'kdv_dahil')) {
            Schema::table('salon_uyelik_odemeleri', function (Blueprint $table) {
                $table->boolean('kdv_dahil')->default(0)->after('ucret');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('salon_uyelik_odemeleri', 'kdv_dahil')) {
            Schema::table('salon_uyelik_odemeleri', function (Blueprint $table) {
                $table->dropColumn('kdv_dahil');
            });
        }
    }
}
