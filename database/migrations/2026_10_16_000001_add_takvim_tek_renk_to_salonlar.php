<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Takvim "Tek Renk" ayari. Acikken randevu takviminde (hem web panel hem mobil
 * uygulama) TUM kaynaklar (personel/oda/cihaz/hizmet kategorisi) ve bu kaynaklara
 * ait NORMAL randevular tek bir renge boyanir. Randevu DURUM renkleri
 * (geldi=yesil, gelmedi=kirmizi, bekliyor=sari, gelecek=turuncu, iptal/kapali=siyah)
 * korunur; tek renk yalnizca normal/kaynak rengin yerine gecer.
 *   - takvim_tek_renk_aktif : ana ac/kapa (varsayilan 0)
 *   - takvim_tek_renk       : hex renk (#RRGGBB)
 * Idempotent.
 */
class AddTakvimTekRenkToSalonlar extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salonlar') && !Schema::hasColumn('salonlar', 'takvim_tek_renk_aktif')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->boolean('takvim_tek_renk_aktif')->default(0);
                $table->string('takvim_tek_renk', 10)->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('salonlar') && Schema::hasColumn('salonlar', 'takvim_tek_renk_aktif')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->dropColumn(['takvim_tek_renk_aktif', 'takvim_tek_renk']);
            });
        }
    }
}
