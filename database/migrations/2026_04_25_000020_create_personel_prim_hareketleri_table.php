<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonelPrimHareketleriTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('personel_prim_hareketleri')) {
            Schema::create('personel_prim_hareketleri', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('personel_id');
                $table->unsignedInteger('salon_id');
                $table->date('tarih');
                $table->enum('tip', ['bonus', 'kesinti']);
                $table->decimal('tutar', 12, 2);
                $table->string('aciklama', 300)->nullable();
                $table->unsignedInteger('ekleyen_yetkili_id')->nullable();
                $table->timestamps();

                $table->index(['salon_id', 'personel_id', 'tarih']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('personel_prim_hareketleri');
    }
}
