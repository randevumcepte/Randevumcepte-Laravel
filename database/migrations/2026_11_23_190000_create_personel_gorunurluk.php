<?php

use Illuminate\Database\Migrations\Migration;

/** Personelin on gorusme / arama randevusu gorunurlugu. DDL tek yerde: PersonelGorunurluk::semaGaranti (idempotent). */
class CreatePersonelGorunurluk extends Migration
{
    public function up()
    {
        \Illuminate\Support\Facades\Cache::forget(\App\Services\PersonelGorunurluk::SEMA);
        \App\Services\PersonelGorunurluk::semaGaranti();
    }

    public function down()
    {
        // Bilincli olarak geri alinmaz: isletmenin gorunurluk tercihleri silinmesin.
    }
}
