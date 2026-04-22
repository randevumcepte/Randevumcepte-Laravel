<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDinamikFormColumns extends Migration
{
    public function up()
    {
        Schema::table('formtaslaklari', function (Blueprint $table) {
            $table->text('aciklama')->nullable()->after('form_adi');
            $table->text('sorular_json')->nullable()->after('aciklama');
            $table->boolean('is_dinamik')->default(false)->after('sorular_json');
        });

        Schema::table('arsiv', function (Blueprint $table) {
            $table->text('cevaplar_json')->nullable()->after('dogrulama_kodu');
        });
    }

    public function down()
    {
        Schema::table('formtaslaklari', function (Blueprint $table) {
            $table->dropColumn(['aciklama', 'sorular_json', 'is_dinamik']);
        });
        Schema::table('arsiv', function (Blueprint $table) {
            $table->dropColumn('cevaplar_json');
        });
    }
}
