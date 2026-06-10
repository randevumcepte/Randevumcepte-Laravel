<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * whatsapp_gonderim_loglari tablosuna teslimat takibi + metadata kolonlari:
 *   - teslim_tarihi : alici cihaza teslim olundugunda (Baileys/Cloud API status 3)
 *   - okundu_tarihi : alici okudugunda (status 4)
 *   - provider      : 'baileys' | 'cloud_api' — hangi yoldan gitti
 *   - gonderim_tipi : 'randevu_hatirlatma' | 'sifre_kodu' | 'anket' | 'iptal_bildirim' vb.
 *
 * "Gonderildi" (durum=1) artik sadece "WhatsApp sunucusu kabul etti" demek;
 * gercek teslimat icin teslim_tarihi'ye bakilir. Bu ayrim olmadan "gonderildi
 * gorunuyor ama karsida saat ikonu" sikayeti tespit edilemiyordu.
 */
class EnrichWhatsappGonderimLoglari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('whatsapp_gonderim_loglari')) {
            return;
        }

        Schema::table('whatsapp_gonderim_loglari', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_gonderim_loglari', 'teslim_tarihi')) {
                $table->timestamp('teslim_tarihi')->nullable()->after('gonderim_tarihi');
            }
            if (!Schema::hasColumn('whatsapp_gonderim_loglari', 'okundu_tarihi')) {
                $table->timestamp('okundu_tarihi')->nullable()->after('teslim_tarihi');
            }
            if (!Schema::hasColumn('whatsapp_gonderim_loglari', 'provider')) {
                $table->string('provider', 20)->nullable()->after('mesaj_id');
            }
            if (!Schema::hasColumn('whatsapp_gonderim_loglari', 'gonderim_tipi')) {
                $table->string('gonderim_tipi', 40)->nullable()->after('provider');
            }
        });

        // Teslim takibi raporlari icin
        if (!$this->indexVarMi('whatsapp_gonderim_loglari', 'wgl_durum_teslim_idx')) {
            Schema::table('whatsapp_gonderim_loglari', function (Blueprint $table) {
                $table->index(['salon_id', 'durum', 'teslim_tarihi'], 'wgl_durum_teslim_idx');
            });
        }
        if (!$this->indexVarMi('whatsapp_gonderim_loglari', 'wgl_tipi_idx')) {
            Schema::table('whatsapp_gonderim_loglari', function (Blueprint $table) {
                $table->index(['salon_id', 'gonderim_tipi'], 'wgl_tipi_idx');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('whatsapp_gonderim_loglari')) return;

        Schema::table('whatsapp_gonderim_loglari', function (Blueprint $table) {
            if ($this->indexVarMi('whatsapp_gonderim_loglari', 'wgl_durum_teslim_idx')) {
                $table->dropIndex('wgl_durum_teslim_idx');
            }
            if ($this->indexVarMi('whatsapp_gonderim_loglari', 'wgl_tipi_idx')) {
                $table->dropIndex('wgl_tipi_idx');
            }
            foreach (['teslim_tarihi', 'okundu_tarihi', 'provider', 'gonderim_tipi'] as $kolon) {
                if (Schema::hasColumn('whatsapp_gonderim_loglari', $kolon)) {
                    $table->dropColumn($kolon);
                }
            }
        });
    }

    protected function indexVarMi($tablo, $indexAdi)
    {
        try {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexler = $sm->listTableIndexes($tablo);
            return array_key_exists($indexAdi, $indexler);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
