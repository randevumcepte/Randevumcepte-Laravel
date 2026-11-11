<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Damga karti "ucretsiz seans kazandiniz" bildirimi defteri: musteriye kural basina
 * en son hangi kazanilan sayisina kadar bildirim yapildi (mukerrer push olmasin).
 */
class CreateDamgaKartiBildirimleri extends Migration
{
    public function up()
    {
        if (Schema::hasTable('damga_karti_bildirimleri')) return;
        Schema::create('damga_karti_bildirimleri', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('salon_id');
            $t->unsignedInteger('kural_id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('bildirilen_kazanilan')->default(0);
            $t->timestamp('son_bildirim')->nullable();
            $t->timestamps();
            $t->unique(['kural_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('damga_karti_bildirimleri');
    }
}
