<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappColumnsToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'whatsapp_aktif')) {
                $table->boolean('whatsapp_aktif')->default(0);
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_durum')) {
                $table->string('whatsapp_durum', 40)->nullable();
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_numara')) {
                $table->string('whatsapp_numara', 20)->nullable();
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_baglanti_tarihi')) {
                $table->timestamp('whatsapp_baglanti_tarihi')->nullable();
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_gunluk_limit')) {
                $table->unsignedInteger('whatsapp_gunluk_limit')->default(150);
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_warmup_baslangic')) {
                $table->timestamp('whatsapp_warmup_baslangic')->nullable();
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_son_hata')) {
                $table->string('whatsapp_son_hata', 120)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            foreach ([
                'whatsapp_aktif', 'whatsapp_durum', 'whatsapp_numara',
                'whatsapp_baglanti_tarihi', 'whatsapp_gunluk_limit',
                'whatsapp_warmup_baslangic', 'whatsapp_son_hata',
            ] as $col) {
                if (Schema::hasColumn('salonlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
