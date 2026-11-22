<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Profil sayfasi (2026-11-23): personel_durum, personel_izinleri, personel_hedefleri, personel_calismalari.
 * Ayni DDL PersonelProfil::semaGaranti'de idempotent olarak da var. Var olan veriye dokunulmaz.
 */
class CreatePersonelProfilTablolari extends Migration
{
    public function up()
    {
        \Illuminate\Support\Facades\Cache::forget(\App\Services\PersonelProfil::SEMA);
        \App\Services\PersonelProfil::semaGaranti();
    }

    public function down()
    {
        // Bilincli olarak geri alinmaz: izin/hedef/calisma kayitlari silinmesin.
    }
}
