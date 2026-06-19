<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResimNotuToIslemler extends Migration
{
    /**
     * Musteri gorseline iliskilendirilmis serbest metin not.
     * islemler tablosunda her satir (yeni yuklemelerde) tek gorsel
     * tuttugu icin not satir basina = gorsel basinadir.
     */
    public function up()
    {
        Schema::table('islemler', function (Blueprint $table) {
            if (!Schema::hasColumn('islemler', 'resim_notu')) {
                $table->text('resim_notu')->nullable()->after('islem_fotolari');
            }
        });
    }

    public function down()
    {
        Schema::table('islemler', function (Blueprint $table) {
            if (Schema::hasColumn('islemler', 'resim_notu')) {
                $table->dropColumn('resim_notu');
            }
        });
    }
}
