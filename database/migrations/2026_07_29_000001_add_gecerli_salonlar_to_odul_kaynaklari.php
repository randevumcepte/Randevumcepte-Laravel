<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Coklu sube "sube secimi" ozelligi icin ortak alan: gecerli_salonlar.
 *
 * Odul kaynaklari (carkifelek_sistemi / bildirim_reklamlari / salon_puan_odulleri)
 * artik owner tarafindan SECILEN sube grubuna uygulanabilir; uretilen kupon
 * (carkifelek_odulleri) bu grubu miras alir. Redemption'da kupon yalnizca kendi
 * gecerli_salonlar listesindeki subelerde kullanilabilir.
 *
 * JSON string olarak salon_id listesi tutar; null = eski kayit (kardes-sube grubu
 * fallback'i uygulanir). Idempotent.
 */
class AddGecerliSalonlarToOdulKaynaklari extends Migration
{
    public function up()
    {
        $tablolar = [
            'carkifelek_sistemi',
            'bildirim_reklamlari',
            'salon_puan_odulleri',
            'carkifelek_odulleri',
        ];

        foreach ($tablolar as $tablo) {
            if (Schema::hasTable($tablo) && !Schema::hasColumn($tablo, 'gecerli_salonlar')) {
                Schema::table($tablo, function (Blueprint $table) {
                    $table->text('gecerli_salonlar')->nullable();
                });
            }
        }
    }

    public function down()
    {
        $tablolar = [
            'carkifelek_sistemi',
            'bildirim_reklamlari',
            'salon_puan_odulleri',
            'carkifelek_odulleri',
        ];

        foreach ($tablolar as $tablo) {
            if (Schema::hasTable($tablo) && Schema::hasColumn($tablo, 'gecerli_salonlar')) {
                Schema::table($tablo, function (Blueprint $table) {
                    $table->dropColumn('gecerli_salonlar');
                });
            }
        }
    }
}
