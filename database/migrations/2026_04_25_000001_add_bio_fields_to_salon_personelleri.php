<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBioFieldsToSalonPersonelleri extends Migration
{
    public function up()
    {
        Schema::table('salon_personelleri', function (Blueprint $table) {
            if (!Schema::hasColumn('salon_personelleri', 'uzmanlik')) {
                $table->string('uzmanlik', 200)->nullable()->after('personel_adi');
            }
            if (!Schema::hasColumn('salon_personelleri', 'aciklama')) {
                $table->text('aciklama')->nullable()->after('uzmanlik');
            }
            if (!Schema::hasColumn('salon_personelleri', 'yillik_tecrube')) {
                $table->unsignedSmallInteger('yillik_tecrube')->nullable()->after('aciklama');
            }
            if (!Schema::hasColumn('salon_personelleri', 'instagram')) {
                $table->string('instagram', 150)->nullable()->after('yillik_tecrube');
            }
        });
    }

    public function down()
    {
        Schema::table('salon_personelleri', function (Blueprint $table) {
            $cols = [];
            foreach (['uzmanlik', 'aciklama', 'yillik_tecrube', 'instagram'] as $c) {
                if (Schema::hasColumn('salon_personelleri', $c)) $cols[] = $c;
            }
            if ($cols) $table->dropColumn($cols);
        });
    }
}
