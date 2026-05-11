<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tum mevcut anket sablonlarinin gonder_saat_sonra degerini 0'a ceker.
 * Boylece anket SMS'i randevu biter bitmez (hizmet suresi tamamlandiginda) gider.
 *
 * Eski default 24 saat -> Yeni davranis: 0 saat (hemen).
 */
class ResetAnketGonderSaatSonra extends Migration
{
    public function up()
    {
        if (Schema::hasTable('anket_sablonlari') && Schema::hasColumn('anket_sablonlari', 'gonder_saat_sonra')) {
            DB::table('anket_sablonlari')->update(['gonder_saat_sonra' => 0]);
        }
    }

    public function down()
    {
        // Geri donus: 24 saat default'una don
        if (Schema::hasTable('anket_sablonlari') && Schema::hasColumn('anket_sablonlari', 'gonder_saat_sonra')) {
            DB::table('anket_sablonlari')->update(['gonder_saat_sonra' => 24]);
        }
    }
}
