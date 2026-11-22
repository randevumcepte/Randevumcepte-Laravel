<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Bildirimlerim (2026-11-23): personelin sessize aldigi bildirim turleri. Ayni DDL
 * PersonelBildirimSessiz::semaGaranti'de idempotent olarak da var. Var olan veriye dokunulmaz.
 */
class CreatePersonelBildirimSessiz extends Migration
{
    public function up()
    {
        \Illuminate\Support\Facades\Cache::forget(\App\Services\PersonelBildirimSessiz::SEMA);
        \App\Services\PersonelBildirimSessiz::semaGaranti();
    }

    public function down()
    {
        // Bilincli olarak geri alinmaz: personel tercihleri silinmesin.
    }
}
