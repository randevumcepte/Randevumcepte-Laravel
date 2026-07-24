<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappKontorToSalonlar extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('salonlar', 'whatsapp_kontor')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->integer('whatsapp_kontor')->default(0); // WhatsApp mesaj kontoru (1 mesaj = 1 kontor)
            });
        }

        if (!Schema::hasTable('whatsapp_kontor_hareketleri')) {
            Schema::create('whatsapp_kontor_hareketleri', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('salon_id')->index();
                $table->string('tip', 20);                 // yukleme | harcama
                $table->integer('adet');                    // +yukleme / -harcama
                $table->integer('bakiye_sonrasi')->nullable();
                $table->string('aciklama', 160)->nullable();
                $table->timestamps();
                $table->index(['salon_id', 'created_at']);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('salonlar', 'whatsapp_kontor')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->dropColumn('whatsapp_kontor');
            });
        }
        Schema::dropIfExists('whatsapp_kontor_hareketleri');
    }
}
