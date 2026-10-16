<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Takvim kaynak renk SIRASI. Salonun 10'lu renk skalasini (renk_duzenleri)
 * kendi tercih ettigi sirayla saklar (JSON hex dizisi, orn ["#EA43F2","#9200BC",...]).
 * "Tek renk" KAPALIyken takvim kaynaklari (personel/oda/cihaz/kategori) bu siraya
 * gore (gorunum sirasina indeksli, N'de bir donguler) renklenir. Ayar yok/bossa
 * eski davranis: renk_duzenleri tablo rengi (kaynak bazli stored eslesme) gecerli.
 * Idempotent.
 */
class AddTakvimRenkSirasiToSalonlar extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salonlar') && !Schema::hasColumn('salonlar', 'takvim_renk_sirasi')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->text('takvim_renk_sirasi')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('salonlar') && Schema::hasColumn('salonlar', 'takvim_renk_sirasi')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->dropColumn('takvim_renk_sirasi');
            });
        }
    }
}
