<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStokYonetimiV2 extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('depolar')) {
            Schema::create('depolar', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('salon_id')->index();
                $table->string('depo_adi', 120);
                $table->text('aciklama')->nullable();
                $table->boolean('varsayilan')->default(false);
                $table->boolean('aktif')->default(true);
                $table->timestamps();
                $table->index(['salon_id', 'aktif']);
            });
        }

        if (!Schema::hasTable('tedarikciler')) {
            Schema::create('tedarikciler', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('salon_id')->index();
                $table->string('ad', 200);
                $table->string('telefon', 30)->nullable();
                $table->string('vergi_no', 30)->nullable();
                $table->string('email', 150)->nullable();
                $table->text('adres')->nullable();
                $table->text('aciklama')->nullable();
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('urun_kategoriler')) {
            Schema::create('urun_kategoriler', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('salon_id')->index();
                $table->string('ad', 120);
                $table->string('ikon', 50)->nullable();
                $table->string('renk', 16)->nullable();
                $table->integer('sira')->default(0);
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        } else {
            Schema::table('urun_kategoriler', function (Blueprint $table) {
                if (!Schema::hasColumn('urun_kategoriler', 'salon_id')) {
                    $table->unsignedBigInteger('salon_id')->nullable()->index();
                }
                if (!Schema::hasColumn('urun_kategoriler', 'ad')) {
                    $table->string('ad', 120)->nullable();
                }
                if (!Schema::hasColumn('urun_kategoriler', 'ikon')) {
                    $table->string('ikon', 50)->nullable();
                }
                if (!Schema::hasColumn('urun_kategoriler', 'renk')) {
                    $table->string('renk', 16)->nullable();
                }
                if (!Schema::hasColumn('urun_kategoriler', 'sira')) {
                    $table->integer('sira')->default(0);
                }
                if (!Schema::hasColumn('urun_kategoriler', 'aktif')) {
                    $table->boolean('aktif')->default(true);
                }
                if (!Schema::hasColumn('urun_kategoriler', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        Schema::table('urunler', function (Blueprint $table) {
            if (!Schema::hasColumn('urunler', 'kategori_id')) {
                $table->unsignedBigInteger('kategori_id')->nullable()->index();
            }
            if (!Schema::hasColumn('urunler', 'tedarikci_id')) {
                $table->unsignedBigInteger('tedarikci_id')->nullable()->index();
            }
            if (!Schema::hasColumn('urunler', 'alis_fiyati')) {
                $table->decimal('alis_fiyati', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('urunler', 'birim')) {
                $table->string('birim', 16)->default('adet');
            }
            if (!Schema::hasColumn('urunler', 'tip')) {
                $table->string('tip', 16)->default('satis');
            }
            if (!Schema::hasColumn('urunler', 'kritik_stok_siniri')) {
                $table->decimal('kritik_stok_siniri', 12, 3)->nullable();
            }
            if (!Schema::hasColumn('urunler', 'resim_url')) {
                $table->string('resim_url', 500)->nullable();
            }
            if (!Schema::hasColumn('urunler', 'aciklama')) {
                $table->text('aciklama')->nullable();
            }
            if (!Schema::hasColumn('urunler', 'varsayilan_depo_id')) {
                $table->unsignedBigInteger('varsayilan_depo_id')->nullable()->index();
            }
            if (!Schema::hasColumn('urunler', 'sku')) {
                $table->string('sku', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('urunler', 'kdv_orani')) {
                $table->decimal('kdv_orani', 5, 2)->nullable();
            }
        });

        if (!Schema::hasTable('stok_hareketleri')) {
            Schema::create('stok_hareketleri', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('salon_id')->index();
                $table->unsignedBigInteger('urun_id')->index();
                $table->unsignedBigInteger('depo_id')->nullable()->index();
                $table->decimal('miktar', 12, 3);
                $table->string('hareket_tipi', 32);
                $table->string('referans_tip', 50)->nullable();
                $table->unsignedBigInteger('referans_id')->nullable();
                $table->string('batch_uuid', 64)->nullable()->index();
                $table->decimal('birim_alis_fiyati', 12, 2)->nullable();
                $table->decimal('birim_satis_fiyati', 12, 2)->nullable();
                $table->text('aciklama')->nullable();
                $table->unsignedBigInteger('kullanici_id')->nullable();
                $table->string('kullanici_tipi', 32)->nullable();
                $table->timestamp('tarih')->nullable()->index();
                $table->timestamps();
                $table->index(['salon_id', 'urun_id', 'tarih']);
                $table->index(['hareket_tipi', 'salon_id']);
            });
        }

        if (!Schema::hasTable('urun_depo_stoklari')) {
            Schema::create('urun_depo_stoklari', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('salon_id')->index();
                $table->unsignedBigInteger('urun_id');
                $table->unsignedBigInteger('depo_id');
                $table->decimal('stok', 12, 3)->default(0);
                $table->timestamps();
                $table->unique(['urun_id', 'depo_id']);
                $table->index(['salon_id', 'urun_id']);
            });
        }

        if (!Schema::hasTable('hizmet_sarf_receteleri')) {
            Schema::create('hizmet_sarf_receteleri', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('salon_id')->index();
                $table->unsignedBigInteger('hizmet_id');
                $table->string('hizmet_tipi', 30)->default('islem');
                $table->unsignedBigInteger('urun_id');
                $table->decimal('miktar', 12, 3);
                $table->boolean('aktif')->default(true);
                $table->timestamps();
                $table->index(['salon_id', 'hizmet_id', 'hizmet_tipi']);
            });
        }

        // Mevcut salonlara varsayilan depo
        DB::statement("INSERT INTO depolar (salon_id, depo_adi, varsayilan, aktif, created_at, updated_at)
                       SELECT DISTINCT u.salon_id, 'Ana Depo', 1, 1, NOW(), NOW() FROM urunler u
                       WHERE u.salon_id IS NOT NULL
                         AND u.salon_id NOT IN (SELECT salon_id FROM depolar WHERE varsayilan = 1)");

        // Eski tek-depo mantığını koruyup ürünlere varsayilan depo bağla
        DB::statement("UPDATE urunler u
                       INNER JOIN depolar d ON d.salon_id = u.salon_id AND d.varsayilan = 1
                       SET u.varsayilan_depo_id = d.id
                       WHERE u.varsayilan_depo_id IS NULL");

        // Mevcut stok_adedi degerlerini urun_depo_stoklari'na tasi
        DB::statement("INSERT INTO urun_depo_stoklari (salon_id, urun_id, depo_id, stok, created_at, updated_at)
                       SELECT u.salon_id, u.id, u.varsayilan_depo_id, COALESCE(u.stok_adedi, 0), NOW(), NOW()
                       FROM urunler u
                       WHERE u.varsayilan_depo_id IS NOT NULL
                         AND NOT EXISTS (SELECT 1 FROM urun_depo_stoklari uds
                                         WHERE uds.urun_id = u.id AND uds.depo_id = u.varsayilan_depo_id)");

        // Audit icin acilis hareketi
        DB::statement("INSERT INTO stok_hareketleri
                       (salon_id, urun_id, depo_id, miktar, hareket_tipi, aciklama, tarih, created_at, updated_at)
                       SELECT u.salon_id, u.id, u.varsayilan_depo_id, COALESCE(u.stok_adedi, 0),
                              'acilis', 'Eski sistemden devralinan stok', NOW(), NOW(), NOW()
                       FROM urunler u
                       WHERE u.varsayilan_depo_id IS NOT NULL
                         AND COALESCE(u.stok_adedi, 0) <> 0
                         AND NOT EXISTS (SELECT 1 FROM stok_hareketleri sh
                                         WHERE sh.urun_id = u.id AND sh.hareket_tipi = 'acilis')");
    }

    public function down()
    {
        // Geriye donus: yeni tablolari sil ama urunler'in mevcut kolonlarina dokunma
        Schema::dropIfExists('hizmet_sarf_receteleri');
        Schema::dropIfExists('urun_depo_stoklari');
        Schema::dropIfExists('stok_hareketleri');
        Schema::dropIfExists('tedarikciler');
        Schema::dropIfExists('depolar');
    }
}
