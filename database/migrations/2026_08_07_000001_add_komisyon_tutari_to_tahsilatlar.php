<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKomisyonTutariToTahsilatlar extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tahsilatlar')) return;
        Schema::table('tahsilatlar', function (Blueprint $table) {
            if (!Schema::hasColumn('tahsilatlar', 'komisyon_tutari')) {
                $table->decimal('komisyon_tutari', 12, 2)->default(0)->after('tutar');
                $table->index('komisyon_tutari');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('tahsilatlar')) return;
        Schema::table('tahsilatlar', function (Blueprint $table) {
            if (Schema::hasColumn('tahsilatlar', 'komisyon_tutari')) {
                $table->dropIndex(['komisyon_tutari']);
                $table->dropColumn('komisyon_tutari');
            }
        });
    }
}
