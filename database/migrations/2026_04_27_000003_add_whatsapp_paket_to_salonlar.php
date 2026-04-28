<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappPaketToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'whatsapp_paket')) {
                $table->string('whatsapp_paket', 20)->default('baslangic')
                    ->comment('baslangic | pro | premium');
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_paket_periyot')) {
                $table->string('whatsapp_paket_periyot', 10)->nullable()
                    ->comment('aylik | yillik');
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_paket_baslangic')) {
                $table->timestamp('whatsapp_paket_baslangic')->nullable();
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_paket_bitis')) {
                $table->timestamp('whatsapp_paket_bitis')->nullable();
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_paket_deneme')) {
                $table->boolean('whatsapp_paket_deneme')->default(0)
                    ->comment('1 = ucretsiz deneme aktif');
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            foreach (['whatsapp_paket', 'whatsapp_paket_periyot',
                     'whatsapp_paket_baslangic', 'whatsapp_paket_bitis',
                     'whatsapp_paket_deneme'] as $col) {
                if (Schema::hasColumn('salonlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
