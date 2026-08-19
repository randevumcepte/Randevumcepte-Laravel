<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp gunluk limit 200 -> 350 yukseltme.
 *
 * Sadece halihazirda 150 (ilk default) ya da 200 (ikinci default) olan
 * salonlari 350'ye ceker. Salon-ozel farkli deger set edenler (100, 500 vs.)
 * korunur — kullanicinin bilincli tercihini bozmayiz.
 *
 * 246 icin ozel not: 246 dogal olarak bu update'e girer (150 olabilir).
 * Aynen 246'nin sessionunu paylasan 416 icin de gecerli (paylasilan cap
 * WhatsAppService'te resolve edilen salon uzerinden = 246).
 */
class BumpWhatsappGunlukLimit350 extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('salonlar', 'whatsapp_gunluk_limit')) return;

        DB::table('salonlar')
            ->whereIn('whatsapp_gunluk_limit', [150, 200])
            ->update(['whatsapp_gunluk_limit' => 350]);
    }

    public function down()
    {
        if (!Schema::hasColumn('salonlar', 'whatsapp_gunluk_limit')) return;

        DB::table('salonlar')
            ->where('whatsapp_gunluk_limit', 350)
            ->update(['whatsapp_gunluk_limit' => 200]);
    }
}
