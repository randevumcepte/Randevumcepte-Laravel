<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * kampanya_katilimcilari tablosuna 'kilitli' kolonu ekler.
 *
 * Cift-arama kilidi: KampanyaAramaYap katilimciyi kuyruga atarken kilitli=1 yapar
 * (chunk'lar 35sn gecikmeli girdiginden, gecikme penceresinde bir sonraki dakikanin
 * kosusu ayni katilimciyi TEKRAR kuyruga atmasin). Arama yapildi isaretlenince
 * (Controller::kampanyaHatirlatmaAramasiYapildiIsaretle) kilitli=0'a cekilir; boylece
 * tekrar arama zamani geldiginde tekrar secilebilir.
 *
 * Kolon eksikti -> KampanyaAramaYap katilimci sorgusu her dakika SQLSTATE 42S22 ile
 * cokuyordu, hic arama kuyruga girmiyordu.
 */
class AddKilitliToKampanyaKatilimcilari extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('kampanya_katilimcilari', 'kilitli')) {
            Schema::table('kampanya_katilimcilari', function (Blueprint $table) {
                $table->tinyInteger('kilitli')->nullable()->default(null);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('kampanya_katilimcilari', 'kilitli')) {
            Schema::table('kampanya_katilimcilari', function (Blueprint $table) {
                $table->dropColumn('kilitli');
            });
        }
    }
}
