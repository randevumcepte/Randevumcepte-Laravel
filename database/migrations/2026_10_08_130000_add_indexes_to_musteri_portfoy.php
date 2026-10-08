<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * musteri_portfoy'a EKSIK index'ler — mariadb CPU'nun kok nedeni.
 * Tablo salon_id (200+) ve user_id (180+) ile surekli sorgulaniyor (musteridetay, feed,
 * dropliste, karaliste, join'ler) ama bu kolonlarda index YOKTU -> her sorgu TUM TABLOYU
 * tariyordu (tum salonlarin musterileri). Iki composite ile her iki filtre yonu da karsilanir:
 *   (salon_id, user_id) -> salon_id-only + salon_id+user_id
 *   (user_id, salon_id) -> user_id-only + user_id+salon_id + user_id uzerinden join
 */
class AddIndexesToMusteriPortfoy extends Migration
{
    private function indexVar($name)
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'musteri_portfoy')
            ->where('INDEX_NAME', $name)
            ->exists();
    }

    public function up()
    {
        if (!Schema::hasTable('musteri_portfoy')) return;
        if (!$this->indexVar('idx_mp_salon_user')) {
            DB::statement('CREATE INDEX idx_mp_salon_user ON musteri_portfoy (salon_id, user_id)');
        }
        if (!$this->indexVar('idx_mp_user_salon')) {
            DB::statement('CREATE INDEX idx_mp_user_salon ON musteri_portfoy (user_id, salon_id)');
        }
    }

    public function down()
    {
        if (!Schema::hasTable('musteri_portfoy')) return;
        if ($this->indexVar('idx_mp_salon_user')) DB::statement('DROP INDEX idx_mp_salon_user ON musteri_portfoy');
        if ($this->indexVar('idx_mp_user_salon')) DB::statement('DROP INDEX idx_mp_user_salon ON musteri_portfoy');
    }
}
