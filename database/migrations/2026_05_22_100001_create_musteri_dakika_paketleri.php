<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * musteri_dakika_paketleri:
 *   Solaryum, masaj gibi sure satisi yapilan hizmetler icin musteriye
 *   atanan dakika havuzu. Mevcut seans-bazli paketler/adisyon_paketler
 *   sistemi bozulmasin diye ayri tablo.
 */
class CreateMusteriDakikaPaketleri extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('musteri_dakika_paketleri')) {
            Schema::create('musteri_dakika_paketleri', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('salon_id');
                $table->unsignedBigInteger('musteri_portfoy_id');
                $table->unsignedBigInteger('hizmet_id');
                $table->integer('toplam_dakika');
                $table->integer('kalan_dakika');
                $table->decimal('satis_fiyati', 12, 2)->default(0);
                $table->date('satis_tarihi');
                $table->date('bitis_tarihi')->nullable();
                $table->enum('durum', ['aktif', 'bitti', 'iptal'])->default('aktif');
                $table->text('notlar')->nullable();
                $table->unsignedBigInteger('olusturan_user_id')->nullable();
                $table->unsignedBigInteger('olusturan_personel_id')->nullable();
                $table->timestamps();

                $table->index('salon_id', 'mdp_salon_idx');
                $table->index('musteri_portfoy_id', 'mdp_musteri_idx');
                $table->index('hizmet_id', 'mdp_hizmet_idx');
                $table->index(['salon_id', 'durum'], 'mdp_salon_durum_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('musteri_dakika_paketleri');
    }
}
