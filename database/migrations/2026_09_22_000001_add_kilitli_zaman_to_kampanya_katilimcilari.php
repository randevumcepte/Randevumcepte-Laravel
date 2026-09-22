<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * kampanya_katilimcilari tablosuna 'kilitli_zaman' (kilitlenme zamani) kolonu ekler.
 *
 * TAM TARAMA GARANTISI + ULASILAMAMA 1-TEKRAR:
 * Cevapsiz/cevaplanmamis aramalar kilitli=1 takili kalir (santral cevapsiz cagriyi
 * geri bildirmiyor -> kampanyaHatirlatmaAramasiYapildiIsaretle cagrilmiyor). Boyle
 * kalan kilitli katilimci kalici atlanir. KampanyaAramaYap::kilitliKurtar(), cagri
 * suresinden (KAMPANYA_KILIT_TIMEOUT_DK, default 5dk) eskiyen ve hala cevaplanmamis
 * (durum_asistan NULL) kilitleri cozer; bunu yapabilmek icin kilitlenme aninin
 * zaman damgasi gerekir. Kilitleme aninda now() yazilir, kilit cozulunce NULL.
 */
class AddKilitliZamanToKampanyaKatilimcilari extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('kampanya_katilimcilari', 'kilitli_zaman')) {
            Schema::table('kampanya_katilimcilari', function (Blueprint $table) {
                $table->timestamp('kilitli_zaman')->nullable()->default(null);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('kampanya_katilimcilari', 'kilitli_zaman')) {
            Schema::table('kampanya_katilimcilari', function (Blueprint $table) {
                $table->dropColumn('kilitli_zaman');
            });
        }
    }
}
