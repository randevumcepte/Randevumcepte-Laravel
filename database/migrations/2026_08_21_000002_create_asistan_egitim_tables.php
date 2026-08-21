<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * ASISTAN EGITIMI — bedava kalip kutuphanesi + cevaplanamayan soru kaydi.
 *
 * asistan_kalip : sistem yoneticisinin ekledigi soru-kalibi -> cevap. Soru gelince
 *   AI'dan ONCE burada aranir; eslesirse BEDAVA cevaplanir (Haiku'ya gitmez).
 * asistan_cozulmeyen : kural+kalip bulamayip AI'ya/genel yanita dusen sorular (adet
 *   sayacli). Panelde en cok sorulanlar gorunur -> tek tikla kalibi eklenir.
 */
class CreateAsistanEgitimTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('asistan_kalip')) {
            Schema::create('asistan_kalip', function (Blueprint $table) {
                $table->increments('id');
                $table->text('tetikleyiciler');            // satir/virgul ile ayrilmis tetik ifadeler (es anlamlilar)
                $table->text('cevap');
                $table->string('kategori', 40)->nullable();
                $table->boolean('aktif')->default(1);
                $table->unsignedInteger('kullanim_sayisi')->default(0);
                $table->timestamps();
                $table->index('aktif', 'asistan_kalip_aktif_idx');
            });
        }

        if (!Schema::hasTable('asistan_cozulmeyen')) {
            Schema::create('asistan_cozulmeyen', function (Blueprint $table) {
                $table->increments('id');
                $table->string('soru_norm', 191)->unique();
                $table->text('ham')->nullable();
                $table->unsignedInteger('adet')->default(1);
                $table->timestamp('son_tarih')->nullable();
                $table->timestamps();
                $table->index('adet', 'asistan_coz_adet_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('asistan_kalip');
        Schema::dropIfExists('asistan_cozulmeyen');
    }
}
