<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * kampanya_yonetimi tablosuna 'randevu_olustur' bayragi ekler.
 *
 * Senaryonun aksiyonlar JSON'undaki randevu_olustur, kampanya olusturulurken buraya
 * yazilir. Santral sesli akisi (kampanyaGeriKazanimRandevu.php -> /api/v1/kampanyaSesliRandevu
 * mod=bilgi) bu bayraga bakar: randevu istemeyen kampanyada sesli randevu diyaloguna
 * GIRMEZ (aksi halde her "evet"te randevu teklif ederdi). Kampanya senaryo_id saklamadigi
 * icin bayragi dogrudan kampanyaya kopyaliyoruz.
 */
class AddRandevuOlusturToKampanyaYonetimi extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('kampanya_yonetimi', 'randevu_olustur')) {
            Schema::table('kampanya_yonetimi', function (Blueprint $table) {
                $table->tinyInteger('randevu_olustur')->nullable()->default(null);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('kampanya_yonetimi', 'randevu_olustur')) {
            Schema::table('kampanya_yonetimi', function (Blueprint $table) {
                $table->dropColumn('randevu_olustur');
            });
        }
    }
}
