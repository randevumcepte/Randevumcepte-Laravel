<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Salon sahiplerinin online randevuya AÇIK saatleri kürasyonu.
 *
 * - salonlar.online_saat_kisitlama_aktif : 1 ise online boş saatler yalnız
 *   tanımlı pencere/istisnalara göre gösterilir (0/varsayilan = eski davranış,
 *   çalışma saatindeki tüm boşluklar online açık).
 * - salonlar.online_gunluk_slot_limiti   : NULL/0 = limitsiz; N>0 ise günde en
 *   fazla N boş slot online gösterilir (kalanlar dolu görünür = "seyreltme").
 * - salon_online_randevu_saatleri        : haftanın günü bazlı açık pencere(ler);
 *   bir güne birden çok satır = birden çok aralık (öğle arası boşluğu vb.).
 * - salon_online_randevu_istisnalari     : belirli tarihe özel a��/kapa; haftalık
 *   kuralı ezer. tip='kapali' o gün tamamen kapalı, tip='ozel' verilen aralık(lar).
 */
class OnlineRandevuSaatYonetimi extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'online_saat_kisitlama_aktif')) {
                $table->boolean('online_saat_kisitlama_aktif')->default(false);
            }
            if (!Schema::hasColumn('salonlar', 'online_gunluk_slot_limiti')) {
                $table->integer('online_gunluk_slot_limiti')->nullable();
            }
        });

        if (!Schema::hasTable('salon_online_randevu_saatleri')) {
            Schema::create('salon_online_randevu_saatleri', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('salon_id');
                $table->tinyInteger('haftanin_gunu'); // 1=Pzt ... 7=Pzr (date('N'))
                $table->string('baslangic_saati', 5);  // HH:MM
                $table->string('bitis_saati', 5);       // HH:MM
                $table->timestamps();
                $table->index(['salon_id', 'haftanin_gunu']);
            });
        }

        if (!Schema::hasTable('salon_online_randevu_istisnalari')) {
            Schema::create('salon_online_randevu_istisnalari', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('salon_id');
                $table->date('tarih');
                $table->string('tip', 10)->default('kapali'); // 'kapali' | 'ozel'
                $table->string('baslangic_saati', 5)->nullable();
                $table->string('bitis_saati', 5)->nullable();
                $table->timestamps();
                $table->index(['salon_id', 'tarih']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('salon_online_randevu_istisnalari');
        Schema::dropIfExists('salon_online_randevu_saatleri');
        Schema::table('salonlar', function (Blueprint $table) {
            if (Schema::hasColumn('salonlar', 'online_gunluk_slot_limiti')) {
                $table->dropColumn('online_gunluk_slot_limiti');
            }
            if (Schema::hasColumn('salonlar', 'online_saat_kisitlama_aktif')) {
                $table->dropColumn('online_saat_kisitlama_aktif');
            }
        });
    }
}
