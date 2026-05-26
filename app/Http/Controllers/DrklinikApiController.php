<?php

namespace App\Http\Controllers;

use App\Services\DrklinikClient;
use App\Imports\DrklinikImporter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Drklinik import/diagnostic/repair endpoint'leri.
 * SSH+PHP -r yerine HTTP cagri ile calismayi saglar.
 *
 * Auth: isletmeyonetim middleware altinda. Salon id session'dan veya URL'den.
 * Drklinik creds: query ?user=...&pass=... ya da env('DRKLINIK_USER').
 */
class DrklinikApiController extends Controller
{
    /**
     * Drklinik creds'i request veya env'den al.
     */
    private function creds(Request $r)
    {
        $user = $r->query('user') ?: $r->input('user') ?: env('DRKLINIK_USER', '');
        $pass = $r->query('pass') ?: $r->input('pass') ?: env('DRKLINIK_PASS', '');
        if (!$user || !$pass) {
            abort(400, 'Drklinik kullanici/sifre eksik (query: ?user=X&pass=Y veya env DRKLINIK_USER/PASS)');
        }
        return [$user, $pass];
    }

    /**
     * GET /isletmeyonetim/api/drklinik/scan/{musid}?salon=362
     * Bir musteri icin drklinik Kalan Seanslar + bizim DB AH/APS karsilastir.
     * Return JSON: { drk: [...], db: [...], farklar: [...] }
     */
    public function scanMusteri($musid, Request $r)
    {
        $salonId = (int) $r->query('salon');
        if (!$salonId) abort(400, '?salon=ID gerekli');
        [$u, $p] = $this->creds($r);

        $client = new DrklinikClient($u, $p);
        $login = $client->login();
        if (!$login['ok']) return response()->json(['error' => 'Drklinik login fail', 'detail' => $login['detail']], 500);

        $h = $client->getHtml('/musteri.aspx?musid=' . $musid);
        if (strlen($h) < 5000) return response()->json(['error' => 'musteri.aspx cekilemedi (boyut yetersiz)'], 500);

        // Drklinik Kalan Seanslar tablosunu cek
        $drkRows = [];
        preg_match_all('~<table[^>]*class="[^"]*table[^"]*"[^>]*>(.*?)</table>~is', $h, $tm);
        foreach ($tm[1] as $body) {
            preg_match_all('~<th[^>]*>(.*?)</th>~is', $body, $th);
            $hdrs = array_map(function ($t) { return trim(html_entity_decode(strip_tags($t), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }, $th[1]);
            if (!in_array('Harcanan', $hdrs, true)) continue;
            preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $body, $rs);
            foreach ($rs[1] as $tr) {
                if (stripos($tr, '<th') !== false) continue;
                preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
                if (empty($tds[1])) continue;
                $cells = [];
                foreach ($tds[1] as $td) $cells[] = trim(preg_replace('~\s+~', ' ', strip_tags($td)));
                if (count($cells) < 4) continue;
                $drkRows[] = [
                    'hizmet'   => $cells[0] ?? '',
                    'alinan'   => (int) preg_replace('~\D~', '', $cells[1] ?? '0'),
                    'biten'    => (int) preg_replace('~\D~', '', $cells[2] ?? '0'),
                    'harcanan' => (int) preg_replace('~[^\d-]~', '', $cells[3] ?? '0'),
                    'kalan'    => (int) preg_replace('~[^\d-]~', '', $cells[4] ?? '0'),
                ];
            }
            break;
        }

        // Bizdeki user_id musid'den bul (musteri_portfoy uzerinden cep_tel join ile)
        $userId = (int) $r->query('user_id', 0);
        if (!$userId) {
            // Ad/soyad ile dene
            $ad = ''; $soyad = '';
            if (preg_match('~name="TB_Ad"[^>]*value="([^"]*)"~i', $h, $m)) $ad = $m[1];
            if (preg_match('~name="TB_Soyad"[^>]*value="([^"]*)"~i', $h, $m)) $soyad = $m[1];
            $tamAd = trim($ad . ' ' . $soyad);
            $u = DB::table('users as u')->join('musteri_portfoy as p', 'p.user_id', '=', 'u.id')
                ->where('p.salon_id', $salonId)
                ->where('u.name', 'LIKE', '%' . $tamAd . '%')
                ->select('u.id')->first();
            if ($u) $userId = $u->id;
        }

        // DB AH/APS dokumu
        $dbRows = [];
        if ($userId) {
            $ahs = DB::table('adisyon_hizmetler as ah')
                ->join('adisyonlar as a', 'a.id', '=', 'ah.adisyon_id')
                ->leftJoin('hizmetler as h', 'h.id', '=', 'ah.hizmet_id')
                ->where('a.salon_id', $salonId)->where('a.user_id', $userId)
                ->select('ah.id', 'a.tarih', 'h.hizmet_adi', 'ah.seans_sayisi',
                    DB::raw('(SELECT COUNT(*) FROM adisyon_paket_seanslar WHERE adisyon_hizmet_id = ah.id) as aps'))
                ->orderBy('a.tarih')->orderBy('ah.id')->get();
            foreach ($ahs as $a) {
                $dbRows[] = [
                    'ahid'         => $a->id,
                    'tarih'        => $a->tarih,
                    'hizmet'       => $a->hizmet_adi,
                    'seans_sayisi' => (int) $a->seans_sayisi,
                    'aps'          => (int) $a->aps,
                    'kalan'        => (int) $a->seans_sayisi - (int) $a->aps,
                ];
            }
        }

        // Karsilastirma (trKey normalize)
        $trKey = function ($s) {
            $s = mb_strtolower((string) $s, 'UTF-8');
            $s = preg_replace('/\p{M}+/u', '', $s);
            $s = strtr($s, ['ı'=>'i','İ'=>'i','ş'=>'s','Ş'=>'s','ğ'=>'g','Ğ'=>'g','ü'=>'u','Ü'=>'u','ö'=>'o','Ö'=>'o','ç'=>'c','Ç'=>'c']);
            return trim(preg_replace('~[^a-z0-9]+~', ' ', $s));
        };
        $drkAgg = [];
        foreach ($drkRows as $d) {
            $k = $trKey($d['hizmet']);
            if (!isset($drkAgg[$k])) $drkAgg[$k] = ['hizmet' => $d['hizmet'], 'alinan' => 0, 'harcanan' => 0];
            $drkAgg[$k]['alinan']   += $d['alinan'];
            $drkAgg[$k]['harcanan'] += $d['harcanan'];
        }
        $dbAgg = [];
        foreach ($dbRows as $d) {
            $k = $trKey($d['hizmet']);
            if (!isset($dbAgg[$k])) $dbAgg[$k] = ['hizmet' => $d['hizmet'], 'alinan' => 0, 'aps' => 0];
            $dbAgg[$k]['alinan'] += $d['seans_sayisi'];
            $dbAgg[$k]['aps']    += $d['aps'];
        }
        $farklar = [];
        foreach (array_unique(array_merge(array_keys($drkAgg), array_keys($dbAgg))) as $k) {
            $drk = $drkAgg[$k] ?? ['hizmet' => '', 'alinan' => 0, 'harcanan' => 0];
            $db  = $dbAgg[$k]  ?? ['hizmet' => '', 'alinan' => 0, 'aps' => 0];
            $durum = 'OK';
            if ($drk['alinan'] !== $db['alinan'] || $drk['harcanan'] !== $db['aps']) {
                $durum = ($db['alinan'] === 0 && $db['aps'] === 0) ? 'EKSIK_DB' : 'FARK';
            }
            $farklar[] = [
                'hizmet'         => $drk['hizmet'] ?: $db['hizmet'],
                'drk_alinan'     => $drk['alinan'],
                'db_alinan'      => $db['alinan'],
                'drk_harcanan'   => $drk['harcanan'],
                'db_aps'         => $db['aps'],
                'drk_kalan'      => $drk['alinan'] - $drk['harcanan'],
                'db_kalan'       => $db['alinan'] - $db['aps'],
                'durum'          => $durum,
            ];
        }

        return response()->json([
            'musid'    => $musid,
            'user_id'  => $userId,
            'drk_rows' => $drkRows,
            'db_rows'  => $dbRows,
            'farklar'  => $farklar,
            'ozet'     => [
                'toplam'   => count($farklar),
                'ok'       => count(array_filter($farklar, function ($f) { return $f['durum'] === 'OK'; })),
                'fark'     => count(array_filter($farklar, function ($f) { return $f['durum'] === 'FARK'; })),
                'eksik_db' => count(array_filter($farklar, function ($f) { return $f['durum'] === 'EKSIK_DB'; })),
            ],
        ]);
    }

    /**
     * POST /isletmeyonetim/api/drklinik/repair/{musid}?salon=362&user_id=NNN
     * Bir musteri icin importMusteriDetay calistir (CLEAN REBUILD).
     */
    public function repairMusteri($musid, Request $r)
    {
        $salonId = (int) $r->query('salon');
        $userId  = (int) $r->query('user_id', 0);
        if (!$salonId) abort(400, '?salon=ID gerekli');
        [$u, $p] = $this->creds($r);

        $client = new DrklinikClient($u, $p);
        $login = $client->login();
        if (!$login['ok']) return response()->json(['error' => 'Login fail', 'detail' => $login['detail']], 500);

        $importer = new DrklinikImporter($client, $salonId, null);
        if (!$userId) $userId = (int) $importer->ensureUserByMusidPublic($musid);
        if (!$userId) return response()->json(['error' => 'User olusturulamadi'], 500);

        $importer->importMusteriDetay((string) $musid, $userId);

        // Sonuc dokumu
        $ahs = DB::table('adisyon_hizmetler as ah')
            ->join('adisyonlar as a', 'a.id', '=', 'ah.adisyon_id')
            ->leftJoin('hizmetler as h', 'h.id', '=', 'ah.hizmet_id')
            ->where('a.salon_id', $salonId)->where('a.user_id', $userId)
            ->select('a.tarih', 'h.hizmet_adi', 'ah.seans_sayisi',
                DB::raw('(SELECT COUNT(*) FROM adisyon_paket_seanslar WHERE adisyon_hizmet_id = ah.id) as aps'))
            ->orderBy('a.tarih')->get();

        return response()->json([
            'ok'      => true,
            'musid'   => $musid,
            'user_id' => $userId,
            'ah'      => $ahs,
            'ozet'    => $importer->summary(),
        ]);
    }

    /**
     * GET /isletmeyonetim/api/drklinik/satis-mismatch?salon=362
     * verify CSV'sinden satış sayısı uyusmazligi olanlari listeler.
     */
    public function satisMismatch(Request $r)
    {
        $salonId = (int) $r->query('salon');
        if (!$salonId) abort(400, '?salon=ID gerekli');
        $csv = '/tmp/drk_verify_' . $salonId . '.csv';
        if (!file_exists($csv)) return response()->json(['error' => 'CSV yok: ' . $csv . ' once --verify ile reimport calistirin'], 404);

        $fp = fopen($csv, 'r');
        $header = fgetcsv($fp);
        $mismatch = [];
        while (($row = fgetcsv($fp)) !== false) {
            // header: musid,musteri,hizmet,drk_alinan,db_alinan,drk_harcanan,db_harcanan,drk_kalan,db_kalan,durum
            if (($row[9] ?? '') !== 'FARK') continue;
            if ((int) $row[3] === (int) $row[4]) continue; // alinan ayni, sadece harcanan farkli ise skip
            $mismatch[] = [
                'musid'        => $row[0],
                'musteri'      => $row[1],
                'hizmet'       => $row[2],
                'drk_alinan'   => (int) $row[3],
                'db_alinan'    => (int) $row[4],
                'fark'         => (int) $row[3] - (int) $row[4],
                'drk_harcanan' => (int) $row[5],
                'db_harcanan'  => (int) $row[6],
            ];
        }
        fclose($fp);
        usort($mismatch, function ($a, $b) { return abs($b['fark']) - abs($a['fark']); });

        return response()->json([
            'salon_id' => $salonId,
            'toplam'   => count($mismatch),
            'patern'   => [
                'db_alinan_fazla'  => count(array_filter($mismatch, function ($m) { return $m['fark'] < 0; })),
                'drk_alinan_fazla' => count(array_filter($mismatch, function ($m) { return $m['fark'] > 0; })),
            ],
            'liste'    => $mismatch,
        ]);
    }

    /**
     * GET /isletmeyonetim/api/drklinik/verify-ozet?salon=362
     * verify CSV ozeti.
     */
    public function verifyOzet(Request $r)
    {
        $salonId = (int) $r->query('salon');
        if (!$salonId) abort(400, '?salon=ID gerekli');
        $csv = '/tmp/drk_verify_' . $salonId . '.csv';
        if (!file_exists($csv)) return response()->json(['error' => 'CSV yok: ' . $csv], 404);

        $stats = ['ok' => 0, 'fark' => 0, 'eksik_db' => 0, 'toplam_musteri' => 0];
        $musteriler = [];
        $fp = fopen($csv, 'r');
        fgetcsv($fp);
        while (($row = fgetcsv($fp)) !== false) {
            $durum = $row[9] ?? '';
            $musid = $row[0] ?? '';
            if ($durum === 'OK') $stats['ok']++;
            elseif ($durum === 'FARK') $stats['fark']++;
            elseif ($durum === 'EKSIK_DB') $stats['eksik_db']++;
            $musteriler[$musid] = true;
        }
        fclose($fp);
        $stats['toplam_musteri'] = count($musteriler);
        $stats['toplam_satir']   = $stats['ok'] + $stats['fark'] + $stats['eksik_db'];
        $stats['ok_orani']       = $stats['toplam_satir'] > 0
            ? round($stats['ok'] / $stats['toplam_satir'] * 100, 2)
            : 0;
        $stats['csv']            = $csv;
        $stats['csv_mtime']      = date('Y-m-d H:i:s', filemtime($csv));

        return response()->json($stats);
    }

    /**
     * POST /isletmeyonetim/api/drklinik/full-reimport?salon=362
     * Full salon reimport baslat (background). nohup ile artisan komutu cagrir.
     */
    public function fullReimport(Request $r)
    {
        $salonId = (int) $r->query('salon');
        if (!$salonId) abort(400, '?salon=ID gerekli');
        [$u, $p] = $this->creds($r);

        $log = '/tmp/drk' . $salonId . '_api_reimport_' . date('Ymd_His') . '.log';
        $cmd = sprintf(
            'cd %s && nohup /opt/php74/bin/php artisan drklinik:import --salon=%d --username=%s --password=%s --only=satis-tahsilat --verify > %s 2>&1 &',
            escapeshellarg(base_path()),
            $salonId,
            escapeshellarg($u),
            escapeshellarg($p),
            escapeshellarg($log)
        );
        exec($cmd, $out, $rc);

        return response()->json([
            'ok'  => $rc === 0,
            'log' => $log,
            'msg' => 'Background calistirildi. tail -f ' . $log,
        ]);
    }
}
