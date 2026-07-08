<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// 'database' cache driver'i icin standart Laravel cache tablosu.
// file cache (eski L5.6 FileStore) 3+ isletmede
// "file_put_contents ... No such file or directory" ile tum istekleri 500'e
// dusurdu; database cache'e gecince RateLimiter/dashboard kasa cache bu tabloyu
// kullanir, dosya sistemi iznine bagimli kalmaz.
class CreateCacheTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cache')) {
            return;
        }

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->unique();
            $table->mediumText('value');
            $table->integer('expiration');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cache');
    }
}
