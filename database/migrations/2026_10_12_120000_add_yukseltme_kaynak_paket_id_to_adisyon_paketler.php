<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * adisyon_paketler tablosuna 'yukseltme_kaynak_paket_id' kolonu:
 *   "Paket Yukseltme" (yerinde donusturme) islemi yapildiginda, paketin
 *   DONUSTURULMEDEN ONCEKI paket_id'si buraya yazilir.
 *   NULL  = paket hic yukseltilmedi (normal satis).
 *   Dolu  = paket yukseltildi; eski paket_id'yi de saklar (gecmis/rozet icin).
 *
 *   Satis takibinde / adisyon kaleminde bu kolon doluysa "Yükseltildi"
 *   rozeti gosterilir.
 */
class AddYukseltmeKaynakPaketIdToAdisyonPaketler extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('adisyon_paketler', 'yukseltme_kaynak_paket_id')) {
            Schema::table('adisyon_paketler', function (Blueprint $table) {
                $table->integer('yukseltme_kaynak_paket_id')->nullable()->default(null)->after('paket_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('adisyon_paketler', 'yukseltme_kaynak_paket_id')) {
            Schema::table('adisyon_paketler', function (Blueprint $table) {
                $table->dropColumn('yukseltme_kaynak_paket_id');
            });
        }
    }
}
