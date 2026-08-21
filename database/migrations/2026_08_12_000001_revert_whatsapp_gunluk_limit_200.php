<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp gunluk limit 350 -> 200 geri cekme.
 *
 * 355 nolu isletmede 350'ye ciktiktan sonra WhatsApp anti-spam kisitlamasi
 * ("Hesabiniz su anda kisitli") tetiklendi. SMS ayari olmayan salonlar var,
 * o yuzden WA kisitlanirsa mesaj hic gitmiyor. 200 daha guvenli seviye.
 *
 * Sadece 350 olan salonlari 200'e ceker; salon-ozel farkli deger set edenler
 * (100, 500 vs.) korunur.
 */
class RevertWhatsappGunlukLimit200 extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('salonlar', 'whatsapp_gunluk_limit')) return;

        DB::table('salonlar')
            ->where('whatsapp_gunluk_limit', 350)
            ->update(['whatsapp_gunluk_limit' => 200]);
    }

    public function down()
    {
        if (!Schema::hasColumn('salonlar', 'whatsapp_gunluk_limit')) return;

        DB::table('salonlar')
            ->where('whatsapp_gunluk_limit', 200)
            ->update(['whatsapp_gunluk_limit' => 350]);
    }
}
