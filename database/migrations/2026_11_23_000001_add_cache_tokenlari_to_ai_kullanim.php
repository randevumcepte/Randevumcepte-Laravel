<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ai_kullanim: prompt-cache okuma/yazma token kolonlari (AI Kredi paneli teknik ayrinti).
 * NULL = kolonlar oncesi kayit. AiKullanimLog::semaGaranti() ayni kolonlari calisma aninda da ekler.
 */
class AddCacheTokenlariToAiKullanim extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ai_kullanim') && !Schema::hasColumn('ai_kullanim', 'cache_okuma_token')) {
            Schema::table('ai_kullanim', function (Blueprint $table) {
                $table->unsignedInteger('cache_okuma_token')->nullable();
                $table->unsignedInteger('cache_yazma_token')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('ai_kullanim') && Schema::hasColumn('ai_kullanim', 'cache_okuma_token')) {
            Schema::table('ai_kullanim', function (Blueprint $table) {
                $table->dropColumn(['cache_okuma_token', 'cache_yazma_token']);
            });
        }
    }
}
