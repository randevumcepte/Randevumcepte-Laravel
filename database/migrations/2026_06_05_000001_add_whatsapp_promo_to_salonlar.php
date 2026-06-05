<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp 2 Ay Ücretsiz tanıtım (promo) kolonları.
 * Aldığım Hizmetler ekranında WhatsApp'ı "2 Ay Ücretsiz" olarak gösterir;
 * süre dolunca whatsapp:promo-kontrol komutu otomatik devre dışı bırakır.
 */
class AddWhatsappPromoToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'whatsapp_promo_baslangic')) {
                $table->date('whatsapp_promo_baslangic')->nullable()
                    ->comment('2 ay ucretsiz WhatsApp tanitiminin baslangici');
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_promo_bitis')) {
                $table->date('whatsapp_promo_bitis')->nullable()
                    ->comment('Ucretsiz surenin bitis tarihi (baslangic + 2 ay)');
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_promo_aktif')) {
                $table->boolean('whatsapp_promo_aktif')->default(1)
                    ->comment('1 = ucretsiz promo suresi devam ediyor');
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_promo_kapatildi')) {
                $table->boolean('whatsapp_promo_kapatildi')->default(0)
                    ->comment('1 = sure dolup otomatik devre disi birakildi');
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            foreach (['whatsapp_promo_baslangic', 'whatsapp_promo_bitis',
                     'whatsapp_promo_aktif', 'whatsapp_promo_kapatildi'] as $col) {
                if (Schema::hasColumn('salonlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
