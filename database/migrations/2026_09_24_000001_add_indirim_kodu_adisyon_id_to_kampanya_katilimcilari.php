<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * kampanya_katilimcilari'ya indirim kodunun UYGULANDIGI adisyon id'sini ekler.
 *
 * Kod tahsilatta uygulaninca hangi adisyona islendigi yazilir. Boylece adisyona geri
 * donuldugunde kod "kullanildi" hatasi vermek yerine, o adisyona ait uygulanmis kod
 * DISABLED olarak gosterilir (cark kuponundaki carkifelek_odulleri.adisyon_id mantigi).
 */
class AddIndirimKoduAdisyonIdToKampanyaKatilimcilari extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('kampanya_katilimcilari', 'indirim_kodu_adisyon_id')) {
            Schema::table('kampanya_katilimcilari', function (Blueprint $table) {
                $table->unsignedBigInteger('indirim_kodu_adisyon_id')->nullable()->default(null);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('kampanya_katilimcilari', 'indirim_kodu_adisyon_id')) {
            Schema::table('kampanya_katilimcilari', function (Blueprint $table) {
                $table->dropColumn('indirim_kodu_adisyon_id');
            });
        }
    }
}
