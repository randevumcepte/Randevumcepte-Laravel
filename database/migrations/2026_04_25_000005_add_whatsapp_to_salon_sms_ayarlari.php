<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappToSalonSmsAyarlari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('salon_sms_ayarlari')) {
            return;
        }
        Schema::table('salon_sms_ayarlari', function (Blueprint $table) {
            if (!Schema::hasColumn('salon_sms_ayarlari', 'whatsapp_musteri')) {
                $table->boolean('whatsapp_musteri')->default(0);
            }
            if (!Schema::hasColumn('salon_sms_ayarlari', 'whatsapp_personel')) {
                $table->boolean('whatsapp_personel')->default(0);
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('salon_sms_ayarlari')) {
            return;
        }
        Schema::table('salon_sms_ayarlari', function (Blueprint $table) {
            foreach (['whatsapp_musteri', 'whatsapp_personel'] as $col) {
                if (Schema::hasColumn('salon_sms_ayarlari', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
