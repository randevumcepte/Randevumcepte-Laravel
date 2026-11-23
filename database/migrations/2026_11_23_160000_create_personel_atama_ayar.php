<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Adil randevu dagitimi (2026-11-23): "otomatik atamaya dahil etme" ayari. Ayni DDL AdilAtama::semaGaranti'de
 * idempotent olarak da var. Var olan veriye dokunulmaz.
 */
class CreatePersonelAtamaAyar extends Migration
{
    public function up()
    {
        \Illuminate\Support\Facades\Cache::forget(\App\Services\AdilAtama::SEMA);
        \App\Services\AdilAtama::semaGaranti();
    }

    public function down()
    {
        // Bilincli olarak geri alinmaz: isletmenin atama tercihleri silinmesin.
    }
}
