<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCarkifelekLoglariMisafir extends Migration
{
    public function up()
    {
        Schema::table('carkifelek_cevirme_loglari', function (Blueprint $table) {
            if (!Schema::hasColumn('carkifelek_cevirme_loglari', 'session_id')) {
                $table->string('session_id', 100)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('carkifelek_cevirme_loglari', 'misafir_ip')) {
                $table->string('misafir_ip', 45)->nullable()->after('session_id');
            }
        });

        // user_id'yi nullable yap (Laravel 5 eski sürümlerinde doctrine/dbal gerekli)
        try {
            \DB::statement('ALTER TABLE carkifelek_cevirme_loglari MODIFY user_id INT UNSIGNED NULL');
        } catch (\Exception $e) {
            // sürücü desteklemiyorsa sessizce geç
        }
    }

    public function down()
    {
        Schema::table('carkifelek_cevirme_loglari', function (Blueprint $table) {
            if (Schema::hasColumn('carkifelek_cevirme_loglari', 'session_id')) $table->dropColumn('session_id');
            if (Schema::hasColumn('carkifelek_cevirme_loglari', 'misafir_ip')) $table->dropColumn('misafir_ip');
        });
    }
}
