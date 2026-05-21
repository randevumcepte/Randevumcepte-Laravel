<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFaturaToAdisyonlar extends Migration
{
    public function up()
    {
        Schema::table('adisyonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('adisyonlar', 'fatura_kesildi')) {
                $table->boolean('fatura_kesildi')->default(false)->index();
            }
            if (!Schema::hasColumn('adisyonlar', 'fatura_kesildi_tarihi')) {
                $table->dateTime('fatura_kesildi_tarihi')->nullable();
            }
            if (!Schema::hasColumn('adisyonlar', 'fatura_kesen_personel_id')) {
                $table->unsignedBigInteger('fatura_kesen_personel_id')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('adisyonlar', function (Blueprint $table) {
            if (Schema::hasColumn('adisyonlar', 'fatura_kesen_personel_id')) {
                $table->dropColumn('fatura_kesen_personel_id');
            }
            if (Schema::hasColumn('adisyonlar', 'fatura_kesildi_tarihi')) {
                $table->dropColumn('fatura_kesildi_tarihi');
            }
            if (Schema::hasColumn('adisyonlar', 'fatura_kesildi')) {
                $table->dropColumn('fatura_kesildi');
            }
        });
    }
}
