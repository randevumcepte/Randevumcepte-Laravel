<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Bildirim Ayarlari (2026-11-22): tablo + salon geneli yedek ayari + WA log "SMS yedegi yok" isareti.
 * Ayni DDL BildirimTercih::semaGaranti'de idempotent olarak da var (migrate kosmamis ortam icin);
 * burada deploy sirasinda BIR KEZ yapilir ki buyuk whatsapp_gonderim_loglari ALTER'i ilk gonderimde
 * calisan isleri bekletmesin. Var olan kolon/tabloya dokunulmaz; veri tasinmaz (tohum ilk okumada).
 */
class CreateSalonBildirimTercihleri extends Migration
{
    public function up()
    {
        \App\Services\BildirimTercih::semaGaranti();
        // semaGaranti onbellek anahtari yazilmissa yeniden dener (eski cache ile atlanmasin)
        if (!\Illuminate\Support\Facades\Schema::hasTable(\App\Services\BildirimTercih::TABLO)) {
            \Illuminate\Support\Facades\Cache::forget(\App\Services\BildirimTercih::SEMA);
            \App\Services\BildirimTercih::semaGaranti();
        }
    }

    public function down()
    {
        // Bilincli olarak geri alinmaz: isletmelerin bildirim tercihleri silinmesin.
    }
}
