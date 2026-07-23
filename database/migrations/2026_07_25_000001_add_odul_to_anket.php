<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anketi tamamlayan musteriye otomatik ODUL (kupon veya puan) verme altyapisi.
 *
 * anket_sablonlari: salon basina odul ayarlari (kupon/puan/kapali + degerler).
 *   odul_tipi: 'yok' (varsayilan) | 'kupon' | 'puan'
 *   Kupon secilirse: indirim_tipi (yuzde|tutar), deger, gecerlilik_gun, hizmet_id (null=tum), baslik.
 *   Puan secilirse: odul_puan (eklenecek sadakat puani).
 *
 * anket_gonderimleri: odulun tek sefer verildigini garanti etmek icin izleme kolonlari.
 *   odul_verildi: bu gonderime odul tanimlandi mi (tekrar tanimlamayi engeller)
 *   odul_kupon_id: verilen kupon kaydinin id'si (izlenebilirlik; puan icin null)
 *
 * Idempotent: kolon varsa no-op. default'lar mevcut sablonlari etkilemez (odul_tipi='yok').
 */
class AddOdulToAnket extends Migration
{
    public function up()
    {
        if (Schema::hasTable('anket_sablonlari')) {
            Schema::table('anket_sablonlari', function (Blueprint $table) {
                if (!Schema::hasColumn('anket_sablonlari', 'odul_tipi')) {
                    $table->string('odul_tipi', 10)->default('yok')->after('gonder_saat_sonra'); // yok | kupon | puan
                }
                if (!Schema::hasColumn('anket_sablonlari', 'odul_kupon_indirim_tipi')) {
                    $table->string('odul_kupon_indirim_tipi', 10)->nullable()->after('odul_tipi'); // yuzde | tutar
                }
                if (!Schema::hasColumn('anket_sablonlari', 'odul_kupon_deger')) {
                    $table->decimal('odul_kupon_deger', 10, 2)->nullable()->after('odul_kupon_indirim_tipi');
                }
                if (!Schema::hasColumn('anket_sablonlari', 'odul_kupon_gecerlilik_gun')) {
                    $table->unsignedSmallInteger('odul_kupon_gecerlilik_gun')->nullable()->after('odul_kupon_deger'); // null = suresiz
                }
                if (!Schema::hasColumn('anket_sablonlari', 'odul_kupon_hizmet_id')) {
                    $table->unsignedInteger('odul_kupon_hizmet_id')->nullable()->after('odul_kupon_gecerlilik_gun'); // null = tum hizmetler
                }
                if (!Schema::hasColumn('anket_sablonlari', 'odul_puan')) {
                    $table->decimal('odul_puan', 10, 2)->nullable()->after('odul_kupon_hizmet_id');
                }
                if (!Schema::hasColumn('anket_sablonlari', 'odul_baslik')) {
                    $table->string('odul_baslik', 200)->nullable()->after('odul_puan'); // kupon basligi; bossa otomatik
                }
            });
        }

        if (Schema::hasTable('anket_gonderimleri')) {
            Schema::table('anket_gonderimleri', function (Blueprint $table) {
                if (!Schema::hasColumn('anket_gonderimleri', 'odul_verildi')) {
                    $table->boolean('odul_verildi')->default(0)->after('kvkk_onay');
                }
                if (!Schema::hasColumn('anket_gonderimleri', 'odul_kupon_id')) {
                    $table->unsignedInteger('odul_kupon_id')->nullable()->after('odul_verildi');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('anket_sablonlari')) {
            Schema::table('anket_sablonlari', function (Blueprint $table) {
                foreach (['odul_baslik','odul_puan','odul_kupon_hizmet_id','odul_kupon_gecerlilik_gun','odul_kupon_deger','odul_kupon_indirim_tipi','odul_tipi'] as $col) {
                    if (Schema::hasColumn('anket_sablonlari', $col)) $table->dropColumn($col);
                }
            });
        }
        if (Schema::hasTable('anket_gonderimleri')) {
            Schema::table('anket_gonderimleri', function (Blueprint $table) {
                foreach (['odul_kupon_id','odul_verildi'] as $col) {
                    if (Schema::hasColumn('anket_gonderimleri', $col)) $table->dropColumn($col);
                }
            });
        }
    }
}
