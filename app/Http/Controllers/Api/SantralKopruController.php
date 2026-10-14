<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RandevuAsistan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Randevu AI Santral kopru uclari (restaurant-laravel /api/santral/* muadili).
 * Node kopru (randevu-santral-ai) bu uclari cagirir: STT metni gonderir, cevap+aksiyon alir.
 * Ses YOK; sadece metin. sube_id = salon_id.
 */
class SantralKopruController extends Controller
{
    // ---------------------------------------------------------- tablolar
    protected function tabloGaranti(): void
    {
        if (!Schema::hasTable('santral_oturumlari')) {
            Schema::create('santral_oturumlari', function ($t) {
                $t->id();
                $t->unsignedBigInteger('sube_id');              // = salon_id
                $t->string('telefon', 30)->nullable();
                $t->unsignedBigInteger('musteri_id')->nullable();
                $t->longText('gecmis')->nullable();             // JSON [{role,content}]
                $t->string('durum', 15)->default('acik');       // acik | kapandi
                $t->string('sonuc', 20)->nullable();            // randevu | iptal | guncelle | aktar | bilgi
                $t->unsignedBigInteger('randevu_id')->nullable();
                $t->timestamp('created_at')->useCurrent();
                $t->timestamp('updated_at')->nullable();
                $t->index(['sube_id', 'durum']);
            });
        }
        if (!Schema::hasTable('santral_ses_kayit')) {
            Schema::create('santral_ses_kayit', function ($t) {
                $t->id();
                $t->unsignedBigInteger('oturum_id')->nullable();
                $t->unsignedBigInteger('sube_id')->nullable();
                $t->string('tur', 12)->default('ai');           // ai | aktarma
                $t->string('dosya', 255);
                $t->unsignedInteger('boyut')->default(0);
                $t->timestamp('created_at')->useCurrent();
                $t->index('oturum_id');
            });
        }
    }

    protected function yetkiVar(Request $r): bool
    {
        $secret = (string) env('SANTRAL_SECRET', '');
        if ($secret === '') return true; // sir tanimli degilse acik
        $gelen = $r->header('X-Santral-Secret') ?: $r->input('santral_secret');
        return is_string($gelen) && hash_equals($secret, $gelen);
    }

    protected function j($data, int $code = 200)
    {
        return response()->json($data, $code, [], JSON_UNESCAPED_UNICODE);
    }

    // ---------------------------------------------------------- /baslat
    public function baslat(Request $r)
    {
        if (!$this->yetkiVar($r)) return $this->j(['ok' => 0, 'hata' => 'Yetkisiz santral isteği'], 401);
        $this->tabloGaranti();

        $salonId = (int) ($r->input('sube_id') ?: (DB::table('salonlar')->min('id') ?: 1));
        $telefon = trim((string) $r->input('telefon'));

        $as = new RandevuAsistan($salonId, $telefon ?: null);
        $karsilama = $as->karsilama();

        $oid = DB::table('santral_oturumlari')->insertGetId([
            'sube_id' => $salonId,
            'telefon' => $telefon ?: null,
            'gecmis' => json_encode([['role' => 'assistant', 'content' => $karsilama]], JSON_UNESCAPED_UNICODE),
            'durum' => 'acik',
            'created_at' => now(),
        ]);

        return $this->j([
            'ok' => 1,
            'oturum_id' => $oid,
            'karsilama' => $karsilama,
            'sube_id' => $salonId,
            'menu_adet' => $as->hizmetSayisi(),   // randevuda: aktif hizmet sayisi
            'geri_arama' => 0,
            'musteri' => $as->taninanMusteri(),
            'son_siparis' => '',
        ]);
    }

    // ---------------------------------------------------------- /konus
    public function konus(Request $r)
    {
        if (!$this->yetkiVar($r)) return $this->j(['ok' => 0, 'hata' => 'Yetkisiz santral isteği'], 401);
        $this->tabloGaranti();

        $oid = (int) $r->input('oturum_id');
        $metin = (string) $r->input('metin');
        $o = DB::table('santral_oturumlari')->where('id', $oid)->first();
        if (!$o) return $this->j(['ok' => 0, 'hata' => 'oturum_yok'], 404);

        $gecmis = json_decode($o->gecmis ?: '[]', true) ?: [];

        $as = new RandevuAsistan((int) $o->sube_id, $o->telefon);
        $d = $as->konus($metin, $gecmis);

        // gecmisi guncelle (son 40)
        $gecmis[] = ['role' => 'user', 'content' => $metin];
        $gecmis[] = ['role' => 'assistant', 'content' => $d['cevap']];
        $gecmis = array_slice($gecmis, -40);

        $guncelle = ['gecmis' => json_encode($gecmis, JSON_UNESCAPED_UNICODE), 'updated_at' => now()];
        $aksiyon = $d['aksiyon'] ?? null;
        if (in_array($aksiyon, ['randevu', 'iptal', 'guncelle', 'aktar'], true)) $guncelle['sonuc'] = $aksiyon;
        if (!empty($d['veri']['randevu_id'])) $guncelle['randevu_id'] = (int) $d['veri']['randevu_id'];
        if (!empty($d['bitir'])) $guncelle['durum'] = 'kapandi';
        DB::table('santral_oturumlari')->where('id', $oid)->update($guncelle);

        return $this->j([
            'ok' => 1,
            'cevap' => $d['cevap'],
            'aksiyon' => $aksiyon,
            'bitir' => (bool) ($d['bitir'] ?? false),
            'teshis' => $as->teshis,
        ]);
    }

    // ---------------------------------------------------------- /bitir
    public function bitir(Request $r)
    {
        if (!$this->yetkiVar($r)) return $this->j(['ok' => 0, 'hata' => 'Yetkisiz santral isteği'], 401);
        $this->tabloGaranti();

        $oid = (int) $r->input('oturum_id');
        $sonuc = (string) ($r->input('ozet') ?: $r->input('sonuc') ?: 'bilgi');
        if ($oid) {
            $o = DB::table('santral_oturumlari')->where('id', $oid)->first();
            $upd = ['durum' => 'kapandi', 'updated_at' => now()];
            if ($o && !$o->sonuc) $upd['sonuc'] = $sonuc;   // onceki aksiyonu (randevu/aktar) ezme
            DB::table('santral_oturumlari')->where('id', $oid)->update($upd);
        }
        return $this->j(['ok' => 1]);
    }

    // ---------------------------------------------------------- /aktarma-hedef
    // Operatore aktarma hedefi. Basit surum: env ile tek hedef (SANTRAL_AKTARMA_DIAL).
    // Ileride salon bazli panele cekilebilir.
    public function aktarmaHedef(Request $r)
    {
        $dial = (string) env('SANTRAL_AKTARMA_DIAL', '');
        $aktif = $dial !== '' ? 1 : 0;
        return $this->j([
            'ok' => 1,
            'aktif' => $aktif,
            'strateji' => 'hepsi',
            'hedefler' => $aktif ? [$dial] : [],
            'dial' => $dial,
            'zil' => (int) env('SANTRAL_AKTARMA_ZIL', 30),
        ]);
    }

    // ---------------------------------------------------------- /kayit-yukle
    public function kayitYukle(Request $r)
    {
        if (!$this->yetkiVar($r)) return $this->j(['ok' => 0, 'hata' => 'Yetkisiz santral isteği'], 401);
        $this->tabloGaranti();

        $oturum = (int) $r->input('oturum_id');
        $tur = in_array($r->input('tur'), ['ai', 'aktarma'], true) ? $r->input('tur') : 'ai';
        $raw = file_get_contents('php://input');
        if (!$raw || strlen($raw) < 200) return $this->j(['ok' => 0, 'hata' => 'bos ses']);

        $dir = storage_path('app/santral_ses');
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $ad = 'santral_' . ($oturum ?: 'x') . '_' . $tur . '_' . date('YmdHis') . '_' . substr(md5((string) mt_rand()), 0, 6) . '.wav';
        @file_put_contents($dir . '/' . $ad, $raw);

        $sube = (int) $r->input('sube_id') ?: (DB::table('santral_oturumlari')->where('id', $oturum)->value('sube_id') ?: null);
        DB::table('santral_ses_kayit')->insert([
            'oturum_id' => $oturum ?: null, 'sube_id' => $sube, 'tur' => $tur,
            'dosya' => $ad, 'boyut' => strlen($raw), 'created_at' => now(),
        ]);
        return $this->j(['ok' => 1, 'dosya' => $ad]);
    }

    // ---------------------------------------------------------- geri-arama (stub)
    // Otomatik geri arama su an KAPALI (Node GERI_ARAMA=0). Kopru hata almasin diye bos liste.
    public function geriAramaBekleyen(Request $r)
    {
        return response()->json(['ok' => 1, 'liste' => []], 200,
            ['Cache-Control' => 'no-store'], JSON_UNESCAPED_UNICODE);
    }

    public function geriAramaDurum(Request $r)
    {
        return $this->j(['ok' => 1]);
    }
}
