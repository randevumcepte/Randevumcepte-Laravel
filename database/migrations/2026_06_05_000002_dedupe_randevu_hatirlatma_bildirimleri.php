<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Tek seferlik temizlik: randevu hatirlatma panel bildirimlerinin mukerrer
 * kayitlarini siler. Ayni (personel_id, randevu_id, aciklama) grubunda en
 * kucuk id'li (en eski) kayit tutulur, digerleri silinir.
 *
 * Mukerrer uretimin kok nedeni ayni deploy'da giderildi (yonetici JOIN'inde
 * DISTINCT + idempotency bayragi). Bu migration yalnizca birikmis eski
 * duplikeleri temizler.
 */
class DedupeRandevuHatirlatmaBildirimleri extends Migration
{
    public function up()
    {
        // Silinecek mukerrer satir sayisini onceden raporla (laravel.log'a).
        $silinecek = DB::table('bildirimler as b1')
            ->join('bildirimler as b2', function ($j) {
                $j->on('b1.personel_id', '=', 'b2.personel_id')
                  ->on('b1.randevu_id', '=', 'b2.randevu_id')
                  ->on('b1.aciklama', '=', 'b2.aciklama')
                  ->on('b1.id', '>', 'b2.id');
            })
            ->whereNotNull('b1.randevu_id')
            ->where('b1.aciklama', 'like', '%hatırlatmak isteriz%')
            ->count();

        Log::info('[dedupe-bildirim] silinecek mukerrer satir', ['adet' => $silinecek]);

        // Her mukerrer grupta en kucuk id disindakileri sil (MySQL multi-table DELETE).
        $deleted = DB::delete("
            DELETE b1 FROM bildirimler b1
            INNER JOIN bildirimler b2
              ON b1.personel_id = b2.personel_id
             AND b1.randevu_id  = b2.randevu_id
             AND b1.aciklama    = b2.aciklama
             AND b1.id > b2.id
            WHERE b1.randevu_id IS NOT NULL
              AND b1.aciklama LIKE '%hatırlatmak isteriz%'
        ");

        Log::info('[dedupe-bildirim] silinen satir', ['adet' => $deleted]);
    }

    public function down()
    {
        // Geri alinamaz (silinen mukerrer kayitlar). No-op.
    }
}
