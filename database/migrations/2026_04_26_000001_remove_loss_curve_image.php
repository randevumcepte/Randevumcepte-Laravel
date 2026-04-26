<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemoveLossCurveImage extends Migration
{
    /**
     * Yanlislikla yuklenen "loss curve" grafigi (685e8dd97888b.jpg) DB'den
     * ve dosya sisteminden temizlenir.
     */
    public function up()
    {
        if (!Schema::hasTable('salon_gorselleri')) return;

        $rows = DB::table('salon_gorselleri')
            ->where('salon_gorseli', 'like', '%685e8dd97888b%')
            ->get();

        foreach ($rows as $r) {
            $relPath = ltrim($r->salon_gorseli, '/');
            $candidates = [
                base_path($relPath),
                public_path(str_replace('public/', '', $relPath)),
                base_path('public/' . $relPath),
            ];
            foreach ($candidates as $p) {
                if (is_file($p)) { @unlink($p); break; }
            }
        }

        DB::table('salon_gorselleri')
            ->where('salon_gorseli', 'like', '%685e8dd97888b%')
            ->delete();
    }

    public function down()
    {
        // Geri alinamaz (silinen gorsel kayit/dosya).
    }
}
