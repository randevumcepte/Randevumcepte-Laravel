<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 432 nolu isletme icin eksik salon_sms_ayarlari satirlarini tamamlar.
 *
 * toplusms (SMS Ayarlari) sayfasi $sms_ayarlari[0..22] indekslerine dogrudan
 * eriser; ayar_id 1..23 satirlarinin tamami yoksa view patlar / kayit hic
 * olusmamistir. Kanonik varsayilanlar kayit akisindaki ApiController::insert
 * bloguyla birebir aynidir. Yalnizca EKSIK (salon_id, ayar_id) ciftleri eklenir;
 * mevcut ayar varsa asla ezilmez.
 */
class BackfillSalonSmsAyarlari extends Migration
{
    private $salonId = 432;

    // ayar_id => [musteri, personel]  (ApiController kayit varsayilanlari)
    private $defaults = [
        1  => [1, 1],
        2  => [1, 1],
        3  => [1, 1],
        4  => [1, 0],
        5  => [1, 0],
        6  => [1, 1],
        7  => [1, 1],
        8  => [1, 0],
        9  => [1, 0],
        10 => [1, 0],
        11 => [1, 1],
        12 => [1, 1],
        13 => [1, 0],
        14 => [1, 1],
        15 => [1, 0],
        16 => [1, 0],
        17 => [0, 0],
        18 => [1, 0],
        19 => [1, 0],
        20 => [0, 0],
        21 => [0, 0],
        22 => [0, 0],
        23 => [0, 0],
    ];

    public function up()
    {
        $hasWhatsapp = DB::getSchemaBuilder()->hasColumn('salon_sms_ayarlari', 'whatsapp_musteri');
        $now = date('Y-m-d H:i:s');

        $existing = DB::table('salon_sms_ayarlari')
            ->where('salon_id', $this->salonId)
            ->pluck('ayar_id')
            ->all();
        $existing = array_flip($existing);

        $insert = [];
        foreach ($this->defaults as $ayarId => $vals) {
            if (isset($existing[$ayarId])) {
                continue;
            }
            $row = [
                'salon_id'   => $this->salonId,
                'ayar_id'    => $ayarId,
                'musteri'    => $vals[0],
                'personel'   => $vals[1],
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if ($hasWhatsapp) {
                $row['whatsapp_musteri']  = 0;
                $row['whatsapp_personel'] = 0;
            }
            $insert[] = $row;
        }

        if (!empty($insert)) {
            DB::table('salon_sms_ayarlari')->insert($insert);
        }
    }

    public function down()
    {
        // Veri tamamlama migration'i; geri alinmaz.
    }
}
