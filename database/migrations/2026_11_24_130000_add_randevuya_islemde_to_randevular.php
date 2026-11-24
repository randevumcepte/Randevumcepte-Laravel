<?php

use App\Services\RandevuSemasi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AddRandevuyaIslemdeToRandevular extends Migration
{
    public function up()
    {
        RandevuSemasi::ensure();
    }

    public function down()
    {
        Cache::forget(RandevuSemasi::CACHE_ANAHTAR);
        Schema::table('randevular', function (Blueprint $t) {
            $t->dropColumn('randevuya_islemde');
        });
    }
}
