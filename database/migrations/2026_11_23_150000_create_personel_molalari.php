<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Musaitlik durumu (2026-11-23): mola talepleri / onayli molalar. Ayni DDL PersonelMola::semaGaranti'de
 * idempotent olarak da var. Var olan veriye dokunulmaz.
 */
class CreatePersonelMolalari extends Migration
{
    public function up()
    {
        \Illuminate\Support\Facades\Cache::forget(\App\Services\PersonelMola::SEMA);
        \App\Services\PersonelMola::semaGaranti();
    }

    public function down()
    {
        // Bilincli olarak geri alinmaz: mola kayitlari ve takvim kapamalari ile bagli.
    }
}
