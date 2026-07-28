<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * İki özellik:
 *  1) Google yorumu ödülü: müşteri "Google'da Yorum Yaz" butonuna tıklayınca otomatik kupon/puan.
 *     salonlar tablosuna google_odul_* ayar kolonları; anket_gonderimleri'ne tek-sefer + izleme.
 *  2) Olumlu yorumların salon web sitesinde yayınlanması: anket_gonderimleri.web_gizle
 *     (0=yayınlanabilir, 1=sahibi gizledi). Yüksek puan + yorumu olanlar otomatik yayınlanır.
 *
 * Idempotent: kolon varsa no-op.
 */
class AddGoogleOdulAndWebYayinToAnket extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salonlar')) {
            Schema::table('salonlar', function (Blueprint $table) {
                if (!Schema::hasColumn('salonlar', 'google_odul_tipi')) {
                    $table->string('google_odul_tipi', 10)->default('yok'); // yok | kupon | puan
                }
                if (!Schema::hasColumn('salonlar', 'google_odul_kupon_indirim_tipi')) {
                    $table->string('google_odul_kupon_indirim_tipi', 10)->nullable(); // yuzde | tutar
                }
                if (!Schema::hasColumn('salonlar', 'google_odul_kupon_deger')) {
                    $table->decimal('google_odul_kupon_deger', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('salonlar', 'google_odul_kupon_gecerlilik_gun')) {
                    $table->unsignedSmallInteger('google_odul_kupon_gecerlilik_gun')->nullable(); // null = suresiz
                }
                if (!Schema::hasColumn('salonlar', 'google_odul_puan')) {
                    $table->decimal('google_odul_puan', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('salonlar', 'google_odul_baslik')) {
                    $table->string('google_odul_baslik', 200)->nullable();
                }
            });
        }

        if (Schema::hasTable('anket_gonderimleri')) {
            Schema::table('anket_gonderimleri', function (Blueprint $table) {
                if (!Schema::hasColumn('anket_gonderimleri', 'google_odul_verildi')) {
                    $table->boolean('google_odul_verildi')->default(0);
                }
                if (!Schema::hasColumn('anket_gonderimleri', 'google_odul_kupon_id')) {
                    $table->unsignedInteger('google_odul_kupon_id')->nullable();
                }
                if (!Schema::hasColumn('anket_gonderimleri', 'web_gizle')) {
                    $table->boolean('web_gizle')->default(0); // 1 = sahibi web sitesinde gizledi
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('salonlar')) {
            Schema::table('salonlar', function (Blueprint $table) {
                foreach (['google_odul_baslik','google_odul_puan','google_odul_kupon_gecerlilik_gun','google_odul_kupon_deger','google_odul_kupon_indirim_tipi','google_odul_tipi'] as $col) {
                    if (Schema::hasColumn('salonlar', $col)) $table->dropColumn($col);
                }
            });
        }
        if (Schema::hasTable('anket_gonderimleri')) {
            Schema::table('anket_gonderimleri', function (Blueprint $table) {
                foreach (['web_gizle','google_odul_kupon_id','google_odul_verildi'] as $col) {
                    if (Schema::hasColumn('anket_gonderimleri', $col)) $table->dropColumn($col);
                }
            });
        }
    }
}
