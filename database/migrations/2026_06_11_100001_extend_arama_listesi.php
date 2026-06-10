<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Cagri Merkezi (Call Center) semasi.
 *
 * Tum DDL App\Services\CagriMerkeziSchema::ensure() icinde (tek dogruluk kaynagi).
 * NOT: Prod deploy'u `php artisan migrate` CALISTIRMADIGI icin asil uygulama
 * `cagrimerkezi:schema-ensure` komutu (Kernel scheduler, saatlik) ile olur.
 * Bu migration duzgun ortamlar / fresh install icin ayni semayi garanti eder.
 *
 * Kapsam: arama_listesi (yeni alanlar), aranacak_musteriler (son_arama_zamani +
 * timestamps), gorusme_notlari (yeni tablo). Tamamen idempotent.
 */
class ExtendAramaListesi extends Migration
{
    public function up()
    {
        \App\Services\CagriMerkeziSchema::ensure();
    }

    public function down()
    {
        // Geri alma uygulanmaz — cagri merkezi semasi veriyle birlikte kalir.
    }
}
