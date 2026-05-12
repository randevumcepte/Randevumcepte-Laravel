<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

/**
 * Mobil WhatsApp Admin Controller — ayri dosyada, herhangi bir
 * autoload/compile sorunu yalnizca bu controller'i etkiler.
 * Tum endpoint'ler defensive (try/catch + null safe + Schema kontrol).
 */
class WhatsappMobileController extends Controller
{
    // ============ Servis (varsa) ============

    public function baslat(Request $request, $salonId)
    {
        try {
            if (!class_exists(\App\Services\WhatsAppService::class)) {
                return response()->json(['error' => 'wa-servis-yok'], 200);
            }
            $svc = app(\App\Services\WhatsAppService::class);
            $res = $svc->startSession($salonId);
            if ($res['ok'] ?? false) {
                \App\Salonlar::where('id', $salonId)->update([
                    'whatsapp_aktif' => 1,
                    'whatsapp_durum' => $res['body']['status'] ?? 'connecting',
                ]);
            }
            return response()->json($res['body'] ?? ['error' => 'servis-erisilemiyor'], $res['status'] ?: 502);
        } catch (\Throwable $e) {
            Log::warning('WP baslat: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 200);
        }
    }

    public function durum(Request $request, $salonId)
    {
        try {
            if (!class_exists(\App\Services\WhatsAppService::class)) {
                $salon = \App\Salonlar::find($salonId);
                return response()->json([
                    'status' => $salon->whatsapp_durum ?? 'baglanti-yok',
                    'phone' => $salon->whatsapp_numara ?? null,
                ]);
            }
            $svc = app(\App\Services\WhatsAppService::class);
            $res = $svc->status($salonId);
            $body = $res['body'] ?? ['status' => 'servis-kapali'];
            if (($res['ok'] ?? false) && isset($body['status'])) {
                $update = ['whatsapp_durum' => $body['status']];
                if ($body['status'] === 'connected') {
                    $salon = \App\Salonlar::find($salonId);
                    if ($salon && !$salon->whatsapp_baglanti_tarihi) {
                        $update['whatsapp_baglanti_tarihi'] = now();
                        $update['whatsapp_warmup_baslangic'] = now();
                    }
                    if (!empty($body['phone'])) $update['whatsapp_numara'] = $body['phone'];
                }
                try { \App\Salonlar::where('id', $salonId)->update($update); } catch (\Throwable $e) {}
            }
            return response()->json($body);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'servis-kapali', 'hata' => $e->getMessage()]);
        }
    }

    public function qr(Request $request, $salonId)
    {
        try {
            if (!class_exists(\App\Services\WhatsAppService::class)) {
                return response()->json(['error' => 'wa-servis-yok'], 404);
            }
            $svc = app(\App\Services\WhatsAppService::class);
            $res = $svc->qr($salonId);
            return response()->json($res['body'] ?? ['error' => 'qr-yok'], $res['status'] ?: 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cikis(Request $request, $salonId)
    {
        try {
            if (class_exists(\App\Services\WhatsAppService::class)) {
                $svc = app(\App\Services\WhatsAppService::class);
                $svc->logout($salonId);
            }
        } catch (\Throwable $e) {
            // ignore
        }
        try {
            \App\Salonlar::where('id', $salonId)->update([
                'whatsapp_aktif' => 0,
                'whatsapp_durum' => 'cikis-yapildi',
                'whatsapp_numara' => null,
                'whatsapp_baglanti_tarihi' => null,
                'whatsapp_warmup_baslangic' => null,
            ]);
        } catch (\Throwable $e) {}
        return response()->json(['ok' => true]);
    }

    // ============ Data ============

    public function ozet(Request $request, $salonId)
    {
        try {
            if (!Schema::hasTable('whatsapp_gonderim_loglari')) {
                return response()->json($this->ozetBosVeri());
            }

            $today = Carbon::today();
            $weekStart = Carbon::today()->subDays(6);
            $monthStart = Carbon::today()->subDays(29);

            $base = DB::table('whatsapp_gonderim_loglari')->where('salon_id', $salonId);
            $bugunToplam   = (clone $base)->whereDate('created_at', $today)->count();
            $bugunBasari   = (clone $base)->whereDate('created_at', $today)->where('durum', 1)->count();
            $bugunFail     = (clone $base)->whereDate('created_at', $today)->where('durum', 2)->count();
            $bugunFallback = (clone $base)->whereDate('created_at', $today)->where('durum', 3)->count();

            $haftaToplam = (clone $base)->whereDate('created_at', '>=', $weekStart)->count();
            $haftaBasari = (clone $base)->whereDate('created_at', '>=', $weekStart)->where('durum', 1)->count();
            $ayToplam = (clone $base)->whereDate('created_at', '>=', $monthStart)->count();
            $ayBasari = (clone $base)->whereDate('created_at', '>=', $monthStart)->where('durum', 1)->count();

            $basariOrani = $haftaToplam > 0 ? round(($haftaBasari / $haftaToplam) * 100, 1) : 0;
            $salon = \App\Salonlar::find($salonId);

            $start = Carbon::today()->subDays(29);
            $trend = DB::table('whatsapp_gonderim_loglari')
                ->select(DB::raw('DATE(created_at) as tarih'), 'durum', DB::raw('COUNT(*) as adet'))
                ->where('salon_id', $salonId)
                ->whereDate('created_at', '>=', $start)
                ->groupBy('tarih', 'durum')->get();

            $gunler = [];
            for ($i = 0; $i < 30; $i++) {
                $g = Carbon::today()->subDays(29 - $i)->format('Y-m-d');
                $gunler[$g] = ['gun' => $g, 'basari' => 0, 'fail' => 0, 'fallback' => 0];
            }
            foreach ($trend as $r) {
                $key = $r->tarih;
                if (!isset($gunler[$key])) continue;
                if ((int) $r->durum === 1) $gunler[$key]['basari'] = (int) $r->adet;
                if ((int) $r->durum === 2) $gunler[$key]['fail'] = (int) $r->adet;
                if ((int) $r->durum === 3) $gunler[$key]['fallback'] = (int) $r->adet;
            }

            return response()->json([
                'durum' => $salon ? ($salon->whatsapp_durum ?? null) : null,
                'numara' => $salon ? ($salon->whatsapp_numara ?? null) : null,
                'gunluk_limit' => (int) ($salon && isset($salon->whatsapp_gunluk_limit) && $salon->whatsapp_gunluk_limit ? $salon->whatsapp_gunluk_limit : 150),
                'bugun' => ['toplam' => $bugunToplam, 'basari' => $bugunBasari, 'fail' => $bugunFail, 'fallback' => $bugunFallback],
                'hafta' => ['toplam' => $haftaToplam, 'basari' => $haftaBasari],
                'ay' => ['toplam' => $ayToplam, 'basari' => $ayBasari],
                'basariOrani' => $basariOrani,
                'gunler' => array_values($gunler),
            ]);
        } catch (\Throwable $e) {
            Log::warning('WP ozet: ' . $e->getMessage());
            return response()->json($this->ozetBosVeri());
        }
    }

    private function ozetBosVeri()
    {
        $gunler = [];
        for ($i = 0; $i < 30; $i++) {
            $g = Carbon::today()->subDays(29 - $i)->format('Y-m-d');
            $gunler[] = ['gun' => $g, 'basari' => 0, 'fail' => 0, 'fallback' => 0];
        }
        return [
            'durum' => null,
            'numara' => null,
            'gunluk_limit' => 150,
            'bugun' => ['toplam' => 0, 'basari' => 0, 'fail' => 0, 'fallback' => 0],
            'hafta' => ['toplam' => 0, 'basari' => 0],
            'ay' => ['toplam' => 0, 'basari' => 0],
            'basariOrani' => 0,
            'gunler' => $gunler,
        ];
    }

    public function loglar(Request $request, $salonId)
    {
        try {
            if (!Schema::hasTable('whatsapp_gonderim_loglari')) {
                return response()->json(['rows' => [], 'toplam' => 0, 'page' => 1, 'per_page' => 50, 'son_sayfa' => 1]);
            }

            $q = DB::table('whatsapp_gonderim_loglari as wl')
                ->leftJoin('users as u', 'u.id', '=', 'wl.user_id')
                ->select('wl.id', 'wl.user_id', 'wl.randevu_id', 'wl.telefon', 'wl.mesaj',
                    'wl.durum', 'wl.hata', 'wl.mesaj_id', 'wl.gonderim_tarihi', 'wl.created_at',
                    'u.name as musteri_adi')
                ->where('wl.salon_id', $salonId);

            if ($request->filled('durum') || $request->input('durum') === '0' || $request->input('durum') === 0) {
                $q->where('wl.durum', (int) $request->input('durum'));
            }
            if ($telefon = $request->input('telefon')) $q->where('wl.telefon', 'like', '%' . $telefon . '%');
            if ($baslangic = $request->input('baslangic')) $q->whereDate('wl.created_at', '>=', $baslangic);
            if ($bitis = $request->input('bitis')) $q->whereDate('wl.created_at', '<=', $bitis);
            if ($arama = $request->input('arama')) $q->where('wl.mesaj', 'like', '%' . $arama . '%');

            $perPage = min((int) $request->input('per_page', 50), 200);
            $page = max((int) $request->input('page', 1), 1);
            $toplam = (clone $q)->count();
            $rows = $q->orderByDesc('wl.id')->offset(($page - 1) * $perPage)->limit($perPage)->get();

            return response()->json([
                'rows' => $rows,
                'toplam' => $toplam,
                'page' => $page,
                'per_page' => $perPage,
                'son_sayfa' => $perPage > 0 ? (int) ceil($toplam / $perPage) : 1,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['rows' => [], 'toplam' => 0, 'hata' => $e->getMessage()]);
        }
    }

    public function aliciler(Request $request, $salonId)
    {
        try {
            if (!Schema::hasTable('whatsapp_gonderim_loglari')) {
                return response()->json(['rows' => [], 'toplam' => 0]);
            }
            $rows = DB::table('whatsapp_gonderim_loglari as wl')
                ->leftJoin('users as u', 'u.id', '=', 'wl.user_id')
                ->select(
                    'wl.telefon',
                    DB::raw('MAX(u.name) as musteri_adi'),
                    DB::raw('COUNT(*) as toplam'),
                    DB::raw('SUM(CASE WHEN wl.durum = 1 THEN 1 ELSE 0 END) as basari'),
                    DB::raw('SUM(CASE WHEN wl.durum = 2 THEN 1 ELSE 0 END) as fail'),
                    DB::raw('SUM(CASE WHEN wl.durum = 3 THEN 1 ELSE 0 END) as fallback'),
                    DB::raw('MAX(wl.created_at) as son_mesaj'),
                    DB::raw('MIN(wl.created_at) as ilk_mesaj')
                )
                ->where('wl.salon_id', $salonId)
                ->groupBy('wl.telefon')
                ->orderByDesc('son_mesaj')
                ->limit(500)
                ->get();
            return response()->json(['rows' => $rows, 'toplam' => $rows->count()]);
        } catch (\Throwable $e) {
            return response()->json(['rows' => [], 'toplam' => 0, 'hata' => $e->getMessage()]);
        }
    }

    public function aliciGecmis(Request $request, $salonId, $telefon)
    {
        try {
            if (!Schema::hasTable('whatsapp_gonderim_loglari')) {
                return response()->json(['rows' => []]);
            }
            $rows = DB::table('whatsapp_gonderim_loglari')
                ->where('salon_id', $salonId)
                ->where('telefon', $telefon)
                ->orderByDesc('created_at')
                ->limit(100)
                ->get(['id', 'mesaj', 'durum', 'hata', 'mesaj_id', 'gonderim_tarihi', 'created_at', 'randevu_id']);
            return response()->json(['rows' => $rows]);
        } catch (\Throwable $e) {
            return response()->json(['rows' => [], 'hata' => $e->getMessage()]);
        }
    }

    // ============ Kanal toggle ============

    public function kanalDurum(Request $request, $salonId)
    {
        try {
            if (!Schema::hasTable('salon_sms_ayarlari') || !Schema::hasColumn('salon_sms_ayarlari', 'whatsapp_musteri')) {
                return response()->json(['aktif' => false, 'sms_aktif_aynigun' => false, 'sms_aktif_24sa' => false]);
            }
            $ayar1 = DB::table('salon_sms_ayarlari')->where('salon_id', $salonId)->where('ayar_id', 1)->first();
            $ayar6 = DB::table('salon_sms_ayarlari')->where('salon_id', $salonId)->where('ayar_id', 6)->first();

            $aktif = ($ayar1 && (int) ($ayar1->whatsapp_musteri ?? 0) === 1)
                  || ($ayar6 && (int) ($ayar6->whatsapp_musteri ?? 0) === 1);

            return response()->json([
                'aktif' => $aktif,
                'sms_aktif_aynigun' => $ayar1 ? ((int) ($ayar1->musteri ?? 0) === 1) : false,
                'sms_aktif_24sa' => $ayar6 ? ((int) ($ayar6->musteri ?? 0) === 1) : false,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['aktif' => false, 'hata' => $e->getMessage()]);
        }
    }

    public function kanalToggle(Request $request, $salonId)
    {
        try {
            if (!Schema::hasTable('salon_sms_ayarlari') || !Schema::hasColumn('salon_sms_ayarlari', 'whatsapp_musteri')) {
                return response()->json(['ok' => false, 'mesaj' => 'salon_sms_ayarlari.whatsapp_musteri yok'], 200);
            }
            $yeniDeger = (int) $request->input('aktif', 0) === 1 ? 1 : 0;
            foreach ([1, 6] as $ayarId) {
                $row = DB::table('salon_sms_ayarlari')->where('salon_id', $salonId)->where('ayar_id', $ayarId)->first();
                if (!$row) {
                    DB::table('salon_sms_ayarlari')->insert([
                        'salon_id' => $salonId,
                        'ayar_id' => $ayarId,
                        'musteri' => 0,
                        'personel' => 0,
                        'whatsapp_musteri' => $yeniDeger,
                    ]);
                } else {
                    DB::table('salon_sms_ayarlari')->where('salon_id', $salonId)->where('ayar_id', $ayarId)
                        ->update(['whatsapp_musteri' => $yeniDeger]);
                }
            }
            return response()->json(['ok' => true, 'aktif' => $yeniDeger === 1]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'hata' => $e->getMessage()], 200);
        }
    }

    // ============ Paket ============

    public function paketDurum(Request $request, $salonId)
    {
        try {
            $salon = \App\Salonlar::find($salonId);
            if (!$salon) return response()->json(['paket' => 'baslangic']);

            $bitis = $salon->whatsapp_paket_bitis ?? null;
            $kalanGun = null;
            if ($bitis) {
                $kalanGun = max(0, Carbon::now()->diffInDays(Carbon::parse($bitis), false));
            }
            return response()->json([
                'paket' => $salon->whatsapp_paket ?? 'baslangic',
                'periyot' => $salon->whatsapp_paket_periyot ?? null,
                'baslangic' => isset($salon->whatsapp_paket_baslangic) && $salon->whatsapp_paket_baslangic
                    ? Carbon::parse($salon->whatsapp_paket_baslangic)->format('Y-m-d') : null,
                'bitis' => $bitis ? Carbon::parse($bitis)->format('Y-m-d') : null,
                'deneme' => isset($salon->whatsapp_paket_deneme) ? (bool) $salon->whatsapp_paket_deneme : false,
                'kalan_gun' => $kalanGun,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['paket' => 'baslangic', 'hata' => $e->getMessage()]);
        }
    }

    public function paketTalep(Request $request, $salonId)
    {
        $paket = $request->input('paket');
        $periyot = $request->input('periyot');
        $iletisim = trim((string) $request->input('iletisim', ''));

        if (!in_array($paket, ['pro', 'premium'])) {
            return response()->json(['error' => 'gecersiz-paket'], 400);
        }
        if (!in_array($periyot, ['aylik', 'yillik'])) {
            return response()->json(['error' => 'gecersiz-periyot'], 400);
        }

        try {
            $salon = \App\Salonlar::find($salonId);
            $salonAd = $salon ? ($salon->salon_adi ?? '') : '';
            DB::table('bildirimler')->insert([
                'aciklama' => "PAKET YUKSELTME TALEBI - {$salonAd} (#{$salonId}) -> "
                    . strtoupper($paket) . ' / ' . ucfirst($periyot)
                    . ' - Iletisim: ' . ($iletisim ?: '-'),
                'salon_id' => $salonId,
                'url' => '/sistemyonetim/isletmedetay/' . $salonId,
                'tarih_saat' => date('Y-m-d H:i:s'),
                'okundu' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('WP paketTalep: ' . $e->getMessage());
        }

        return response()->json([
            'ok' => true,
            'mesaj' => 'Talebiniz alindi. Musteri temsilcimiz en kisa surede iletisime gececek.',
        ]);
    }
}
