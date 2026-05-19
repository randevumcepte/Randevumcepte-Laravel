<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * musteri_portfoy tablosuna olusturan_personel_id alani ekler.
 * "Personelin kendi portfoyu" yetki kisitlamasi icin kullanilir:
 *   - Yetki yoksa personel sadece kendi olusturdugu + hizmet/urun/paket
 *     satislarinda kendisinin yer aldigi musterileri gorur.
 *
 * Eski kayitlar NULL kalir; bunlar icin "olusturan" kriteri devre disi
 * olur ama "satislarinda kendisi" kriteri zaten gecmis kayitlardan
 * EXISTS sorgusu ile cikarilir.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('musteri_portfoy', 'olusturan_personel_id')) {
            Schema::table('musteri_portfoy', function (Blueprint $table) {
                $table->unsignedBigInteger('olusturan_personel_id')->nullable()->after('salon_id');
                $table->index('olusturan_personel_id', 'mp_olusturan_personel_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('musteri_portfoy', 'olusturan_personel_id')) {
            Schema::table('musteri_portfoy', function (Blueprint $table) {
                $table->dropIndex('mp_olusturan_personel_idx');
                $table->dropColumn('olusturan_personel_id');
            });
        }
    }
};
