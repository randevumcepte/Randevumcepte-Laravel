<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// Tekrarli katilim: secilen SABIT ders slotlari (ders_programi_sablonu id listesi, JSON).
// Slot bazli akis: musteri dogrudan sablon slotlarina (gun+saat+egitmen sabit) yazilir.
// Doluysa bos degil; gunler/saatler/personel_id filtreleri (eski akis) geriye donuk korunur.
class AddSablonlarToDersTekrarliKatilim extends Migration
{
    public function up()
    {
        Schema::table('ders_tekrarli_katilim', function (Blueprint $table) {
            $table->string('sablonlar')->nullable()->after('saatler'); // JSON [sablon_id,...]
        });
    }

    public function down()
    {
        Schema::table('ders_tekrarli_katilim', function (Blueprint $table) {
            $table->dropColumn('sablonlar');
        });
    }
}
