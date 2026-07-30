<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappPaylasilanSalonIdToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'whatsapp_paylasilan_salon_id')) {
                $table->unsignedInteger('whatsapp_paylasilan_salon_id')->nullable()->after('whatsapp_numara');
            }
        });

        // 416 salonu 246'nin WhatsApp oturumunu paylasir (istek uzerine, tek atomik veri).
        // Sadece bu iki salon icin. Diger salonlar NULL kalir, davranislari degismez.
        if (\Schema::hasColumn('salonlar', 'whatsapp_paylasilan_salon_id')) {
            \DB::table('salonlar')
                ->where('id', 416)
                ->update(['whatsapp_paylasilan_salon_id' => 246]);
        }
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (Schema::hasColumn('salonlar', 'whatsapp_paylasilan_salon_id')) {
                $table->dropColumn('whatsapp_paylasilan_salon_id');
            }
        });
    }
}
