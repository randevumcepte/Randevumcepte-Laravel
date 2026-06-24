<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * salonlar.whatsapp_bridge_tipi:
 *   'baileys'   (default) -> mevcut Node bridge (port 3001)
 *   'whatsmeow' (opt-in)  -> Go pilot bridge (port 3002)
 *
 * WhatsAppService bu kolona gore HTTP endpoint'i secer. Salon basina
 * tek tek pilot bridge'e tasinabilir; geri kalan herkes Baileys'te kalir.
 * Flag bos veya 'baileys' ise davranis sifir degisir.
 */
class AddWhatsappBridgeTipiToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'whatsapp_bridge_tipi')) {
                $table->string('whatsapp_bridge_tipi', 20)->default('baileys')->after('whatsapp_durum');
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (Schema::hasColumn('salonlar', 'whatsapp_bridge_tipi')) {
                $table->dropColumn('whatsapp_bridge_tipi');
            }
        });
    }
}
