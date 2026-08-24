<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp ücretsiz denemesi: global tek tarih (2026-08-31) yerine
 * salon-başı 60 gün deneme modeline geçiş.
 *
 * - Yeni kolon: salonlar.whatsapp_deneme_bitis (nullable date)
 * - Mevcut baglantisi olan tum salonlar icin default: 2026-08-31
 *   (kullanicinin "cogunu 31 Agustos'a kadar uzatacagim" istegi ile uyumlu)
 * - Yeni baglananlar icin webhook.onConnected 60 gun ekler
 * - Elle uzatma icin bu kolon panelden veya SQL ile duzenlenebilir
 */
class AddWhatsappDenemeBitisToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'whatsapp_deneme_bitis')) {
                $table->date('whatsapp_deneme_bitis')->nullable()->after('whatsapp_baglanti_tarihi');
            }
        });

        // Mevcut baglantisi olan tum salonlarin deneme bitisi = 2026-08-31
        // (WhatsApp'a bagli olmayanlara dokunmayiz; onlar baglaninca 60 gun alacak)
        if (Schema::hasColumn('salonlar', 'whatsapp_deneme_bitis')) {
            DB::table('salonlar')
                ->whereNull('whatsapp_deneme_bitis')
                ->whereNotNull('whatsapp_baglanti_tarihi')
                ->update(['whatsapp_deneme_bitis' => '2026-08-31']);
        }
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (Schema::hasColumn('salonlar', 'whatsapp_deneme_bitis')) {
                $table->dropColumn('whatsapp_deneme_bitis');
            }
        });
    }
}
