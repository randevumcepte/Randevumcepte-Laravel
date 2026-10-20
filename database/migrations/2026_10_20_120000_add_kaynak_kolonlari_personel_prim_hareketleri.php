<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Telafi devri (personel_prim_hareketleri tip=telafi_devir) kayitlarini kaynaklarina
 * baglar. Boylece bir satis (adisyon_hizmet / adisyon_paket) silindiginde ilgili -tutar
 * clawback'i hak ediste YETIM kalmaz; primHakedisVerisi orphan telafi devrini yok sayar.
 */
class AddKaynakKolonlariPersonelPrimHareketleri extends Migration
{
    public function up()
    {
        Schema::table('personel_prim_hareketleri', function (Blueprint $t) {
            if (!Schema::hasColumn('personel_prim_hareketleri', 'kaynak_randevu_id')) {
                // Telafi randevusu (yeni ucretsiz randevu) id
                $t->integer('kaynak_randevu_id')->unsigned()->nullable()->index('ppd_kaynak_randevu_idx');
            }
            if (!Schema::hasColumn('personel_prim_hareketleri', 'kaynak_adisyon_hizmet_id')) {
                // Clawback'in hesaplandigi satis kalemi (adisyon_hizmetler.id)
                $t->integer('kaynak_adisyon_hizmet_id')->unsigned()->nullable()->index('ppd_kaynak_ah_idx');
            }
            if (!Schema::hasColumn('personel_prim_hareketleri', 'kaynak_adisyon_paket_id')) {
                // Clawback'in hesaplandigi paket (adisyon_paketler.id)
                $t->integer('kaynak_adisyon_paket_id')->unsigned()->nullable()->index('ppd_kaynak_ap_idx');
            }
        });
    }

    public function down()
    {
        Schema::table('personel_prim_hareketleri', function (Blueprint $t) {
            if (Schema::hasColumn('personel_prim_hareketleri', 'kaynak_randevu_id')) {
                $t->dropIndex('ppd_kaynak_randevu_idx');
                $t->dropColumn('kaynak_randevu_id');
            }
            if (Schema::hasColumn('personel_prim_hareketleri', 'kaynak_adisyon_hizmet_id')) {
                $t->dropIndex('ppd_kaynak_ah_idx');
                $t->dropColumn('kaynak_adisyon_hizmet_id');
            }
            if (Schema::hasColumn('personel_prim_hareketleri', 'kaynak_adisyon_paket_id')) {
                $t->dropIndex('ppd_kaynak_ap_idx');
                $t->dropColumn('kaynak_adisyon_paket_id');
            }
        });
    }
}
