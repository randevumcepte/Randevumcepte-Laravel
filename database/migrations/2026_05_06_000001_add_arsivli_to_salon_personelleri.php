<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArsivliToSalonPersonelleri extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salon_personelleri') && !Schema::hasColumn('salon_personelleri', 'arsivli')) {
            Schema::table('salon_personelleri', function (Blueprint $table) {
                // Soft archive: sil dendiginde true yapiyoruz, listeden gizleniyor.
                // Iliskili randevu/tahsilat/prim kayitlari korunur, eski raporlar bozulmaz.
                $table->boolean('arsivli')->default(false)->after('takvimde_gorunsun');
                $table->index(['salon_id', 'arsivli']);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('salon_personelleri') && Schema::hasColumn('salon_personelleri', 'arsivli')) {
            Schema::table('salon_personelleri', function (Blueprint $table) {
                $table->dropIndex(['salon_id', 'arsivli']);
                $table->dropColumn('arsivli');
            });
        }
    }
}
