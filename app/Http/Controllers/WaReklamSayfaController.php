<?php

namespace App\Http\Controllers;

use App\Services\WaReklam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * WhatsApp asistaninin gonderdigi salon kampanyasinin herkese acik sayfasi: /k/{token}
 * Afis + metin + "WhatsApp'tan randevu al" (salonun WhatsApp hattina hazir mesajla doner; asistan devam eder).
 * ?g={gosterim_id} -> tiklama olcumu.
 */
class WaReklamSayfaController extends Controller
{
    public function sayfa(Request $request, $token)
    {
        if (!WaReklam::semaGaranti()) abort(404);
        $r = DB::table('wa_reklamlar')->where('token', (string) $token)->first();
        if (!$r) abort(404);
        $gid = (int) $request->get('g');
        if ($gid > 0) {
            try {
                DB::table('wa_reklam_gosterim')->where('id', $gid)->where('reklam_id', $r->id)->whereNull('tiklama_at')->update(['tiklama_at' => now()]);
            } catch (\Throwable $e) {}
        }
        $salon = DB::table('salonlar')->where('id', $r->salon_id)->first(['salon_adi', 'whatsapp_numara', 'telefon_1', 'adres']);
        $bitti = ($r->bitis && $r->bitis < date('Y-m-d')) || !$r->aktif;
        $wa = preg_replace('/\D/', '', (string) (($salon->whatsapp_numara ?? '') ?: ($salon->telefon_1 ?? '')));
        if (strlen($wa) === 10) $wa = '90' . $wa;
        if (strlen($wa) === 11 && $wa[0] === '0') $wa = '9' . $wa;
        $waLink = $wa !== '' ? 'https://wa.me/' . $wa . '?text=' . rawurlencode('Merhaba, "' . $r->baslik . '" kampanyası için randevu almak istiyorum.') : null;
        return response()->view('wa-reklam-sayfa', [
            'r' => $r, 'salon' => $salon, 'bitti' => $bitti, 'waLink' => $waLink,
            'afis' => $r->gorsel ? WaReklam::afisYolu($r->token, $r->updated_at) : null,
            // og:image / og:url tam adres ister (WhatsApp onizlemesi)
            'afisTam' => $r->gorsel ? $request->getSchemeAndHttpHost() . WaReklam::afisYolu($r->token, $r->updated_at) : null,
            'link' => $request->getSchemeAndHttpHost() . '/k/' . $r->token,
        ])->header('Cache-Control', 'no-store');
    }

    public function afis($token)
    {
        if (!WaReklam::semaGaranti()) abort(404);
        $r = DB::table('wa_reklamlar')->where('token', (string) $token)->first(['gorsel']);
        if (!$r || !$r->gorsel) abort(404);
        $yol = storage_path('app/' . $r->gorsel);
        if (!is_file($yol)) abort(404);
        $mime = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][strtolower(pathinfo($yol, PATHINFO_EXTENSION))] ?? 'image/jpeg';
        return response()->file($yol, ['Content-Type' => $mime, 'Cache-Control' => 'public, max-age=86400']);
    }
}
