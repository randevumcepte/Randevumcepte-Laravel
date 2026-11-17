<?php

namespace App\Http\Controllers\SistemYonetim;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Sistem Yonetimi > Santral Ses Onizleme.
 * Google (Standard / WaveNet / Neural2 / Chirp3-HD) ve ElevenLabs seslerini ayni metinle dinleme.
 * "Telefon kalitesi" acikken ses santralin gonderdigi bicimle birebir (8 kHz mu-law) uretilir; kapaliyken yuksek kalite.
 * Uretilen ses onbellege yazilir (ayni metin + ses tekrar ucretlendirilmez).
 */
class SantralSesController extends Controller
{
    const ONBELLEK_DIZIN = 'ses-onizleme';
    const MAKS_KARAKTER = 400;

    /** Hizli secim: su an santralde kullanilan ayar + denedigimiz adaylar */
    const HAZIR = [
        ['ad' => 'Şu anki santral: ElevenLabs v3 · Jessica', 'saglayici' => 'elevenlabs', 'ses' => 'cgSgspJ2msm6clMCkdW9', 'ses_ad' => 'Jessica', 'model' => 'eleven_v3', 'stabilite' => 0.0],
        ['ad' => 'ElevenLabs Flash v2.5 · Jessica', 'saglayici' => 'elevenlabs', 'ses' => 'cgSgspJ2msm6clMCkdW9', 'ses_ad' => 'Jessica', 'model' => 'eleven_flash_v2_5', 'stabilite' => 0.5],
        ['ad' => 'ElevenLabs Multilingual v2 · Jessica', 'saglayici' => 'elevenlabs', 'ses' => 'cgSgspJ2msm6clMCkdW9', 'ses_ad' => 'Jessica', 'model' => 'eleven_multilingual_v2', 'stabilite' => 0.5],
        ['ad' => 'Google Chirp3-HD · Leda (kadın)', 'saglayici' => 'google', 'ses' => 'tr-TR-Chirp3-HD-Leda', 'ses_ad' => 'Leda'],
        ['ad' => 'Google Chirp3-HD · Achird (erkek)', 'saglayici' => 'google', 'ses' => 'tr-TR-Chirp3-HD-Achird', 'ses_ad' => 'Achird'],
        ['ad' => 'Google WaveNet-E (eski santral sesi)', 'saglayici' => 'google', 'ses' => 'tr-TR-Wavenet-E', 'ses_ad' => 'Wavenet-E'],
    ];

    const ORNEK_METINLER = [
        "Merhaba Anıl Bey, Randevumcepte'ye hoş geldiniz. Ben yapay zeka asistanınızım. Size nasıl yardımcı olabilirim?",
        "Tamamdır Anıl Bey, perşembe saat üçe Gülşah Hanım'la lazer epilasyon randevunuzu oluşturdum, size de mesaj gönderdim. Başka yardımcı olabileceğim bir konu var mı?",
        "Cuma, 20 Kasım için uzmanımız Anıl Bey ile saat 10:00 ya da 11:40 uygun. Hangisini tercih edersiniz?",
        "Maalesef perşembe saat 14:00 dolu. Size aynı gün saat 15:00 ya da 16:00 önerebilirim, hangisi uygun olur?",
        "Lazer epilasyon hizmetinizde iki paketiniz var. Birincisinde beş seans, ikincisinde üç seans kalmış. Hangi paketinizden randevu oluşturalım?",
    ];

    const EL_MODELLER = [
        'eleven_v3' => 'v3 (en ifadeli, ilk ses ~0,9 sn)',
        'eleven_multilingual_v2' => 'Multilingual v2 (~0,6 sn)',
        'eleven_flash_v2_5' => 'Flash v2.5 (en hızlı, ~0,4 sn)',
        'eleven_v4_turbo' => 'v4 Turbo',
        'eleven_v4' => 'v4',
    ];

    public function __construct()
    {
        $this->middleware('auth:sistemyonetim');
    }

    private function rol()
    {
        $u = Auth::guard('sistemyonetim')->user();
        if (!$u) return null;
        if (!empty($u->rol)) return $u->rol;
        return $u->admin == 1 ? 'super_admin' : 'destek';
    }

    private function googleKey(): string { return (string) config('services.google_tts.key', ''); }
    private function elKey(): string { return (string) config('services.elevenlabs.key', ''); }

    public function index()
    {
        return view('sistemyonetim.v2.santral-ses', [
            'title'     => 'Santral Ses Önizleme',
            'aktifMenu' => 'santralses',
            'hazir'     => self::HAZIR,
            'ornekler'  => self::ORNEK_METINLER,
            'elModeller'=> self::EL_MODELLER,
            'googleVar' => $this->googleKey() !== '',
            'elVar'     => $this->elKey() !== '',
            'yetkili'   => in_array($this->rol(), ['super_admin', 'yonetici'], true),
            'maks'      => self::MAKS_KARAKTER,
        ]);
    }

    /** GET ?saglayici=google|elevenlabs -> ses listesi (onbellekli) */
    public function sesler(Request $r)
    {
        $s = $r->get('saglayici') === 'elevenlabs' ? 'elevenlabs' : 'google';
        try {
            $liste = $s === 'google' ? $this->googleSesleri() : $this->elSesleri();
            return response()->json(['ok' => true, 'sesler' => $liste]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'hata' => $e->getMessage()]);
        }
    }

    private function googleSesleri(): array
    {
        if ($this->googleKey() === '') throw new \RuntimeException('GOOGLE_TTS_API_KEY tanımlı değil');
        return Cache::remember('santral_ses_google_tr', 60 * 24, function () {
            $d = $this->httpJson('GET', 'https://texttospeech.googleapis.com/v1/voices?languageCode=tr-TR&key=' . urlencode($this->googleKey()));
            $out = [];
            foreach (($d['voices'] ?? []) as $v) {
                $ad = (string) ($v['name'] ?? '');
                if (strpos($ad, 'tr-TR') !== 0) continue;
                $tur = 'Standard';
                foreach (['Chirp3-HD', 'Chirp-HD', 'Neural2', 'Wavenet', 'Standard'] as $t) { if (stripos($ad, $t) !== false) { $tur = $t; break; } }
                $out[] = [
                    'id' => $ad,
                    'ad' => preg_replace('/^tr-TR-(Chirp3-HD|Chirp-HD|Neural2|Wavenet|Standard)-/', '', $ad),
                    'tur' => $tur,
                    'cinsiyet' => ($v['ssmlGender'] ?? '') === 'FEMALE' ? 'kadın' : ((($v['ssmlGender'] ?? '') === 'MALE') ? 'erkek' : ''),
                    'not' => in_array($tur, ['Chirp3-HD', 'Chirp-HD'], true) ? 'en doğal Google' : ($tur === 'Wavenet' ? 'ucuz' : ''),
                ];
            }
            $sira = ['Chirp3-HD' => 0, 'Chirp-HD' => 1, 'Neural2' => 2, 'Wavenet' => 3, 'Standard' => 4];
            usort($out, function ($a, $b) use ($sira) { return [$sira[$a['tur']] ?? 9, $a['ad']] <=> [$sira[$b['tur']] ?? 9, $b['ad']]; });
            return $out;
        });
    }

    private function elSesleri(): array
    {
        if ($this->elKey() === '') throw new \RuntimeException('ELEVENLABS_API_KEY tanımlı değil (Laravel .env)');
        return Cache::remember('santral_ses_el_v2', 10, function () {
            $out = []; $hesapta = [];
            $d = $this->httpJson('GET', 'https://api.elevenlabs.io/v1/voices', ['xi-api-key: ' . $this->elKey()]);
            foreach (($d['voices'] ?? []) as $v) {
                $l = $v['labels'] ?? [];
                // Kutuphaneden hesaba eklenen sesler 'professional': ucretsiz planda API ile seslendirilemez (402)
                $kutuphane = ($v['category'] ?? '') === 'professional';
                $out[] = [
                    'id' => $v['voice_id'], 'ad' => preg_replace('/\s*[-|].*$/', '', (string) $v['name']),
                    'tur' => $kutuphane ? 'Türkçe kütüphane' : 'Hesaptaki',
                    'cinsiyet' => ($l['gender'] ?? '') === 'female' ? 'kadın' : (($l['gender'] ?? '') === 'male' ? 'erkek' : ''),
                    'not' => trim(($l['accent'] ?? '') . ' ' . ($l['language'] ?? '')) . (($l['language'] ?? '') === 'tr' ? '' : ' · Türkçe anadili değil')
                        . ($kutuphane ? ' · ücretli plan gerekir' : ''),
                ];
                $hesapta[$v['voice_id']] = 1;
            }
            // Turkce kutuphane sesleri (ucretsiz planda API ile seslendirilemez; ucretli planda acilir)
            try {
                $k = $this->httpJson('GET', 'https://api.elevenlabs.io/v1/shared-voices?language=tr&page_size=40&sort=usage_character_count_1y', ['xi-api-key: ' . $this->elKey()]);
                foreach (($k['voices'] ?? []) as $v) {
                    if (isset($hesapta[$v['voice_id']])) continue; // hesaba eklenmis olan zaten listede
                    $out[] = [
                        'id' => $v['voice_id'], 'ad' => preg_replace('/\s*-.*$/', '', (string) $v['name']), 'tur' => 'Türkçe kütüphane',
                        'cinsiyet' => ($v['gender'] ?? '') === 'female' ? 'kadın' : (($v['gender'] ?? '') === 'male' ? 'erkek' : ''),
                        'not' => trim(($v['use_case'] ?? '') . ' · ' . ($v['accent'] ?? '')) . ' · ücretli plan gerekir',
                    ];
                }
            } catch (\Throwable $e) { /* kutuphane listesi opsiyonel */ }
            return $out;
        });
    }

    /** POST: metni secilen sesle uretir, ses dosyasini dondurur (onbellekli). */
    public function uret(Request $r)
    {
        if (!in_array($this->rol(), ['super_admin', 'yonetici'], true)) {
            return response()->json(['ok' => false, 'hata' => 'Bu işlem için yetkiniz yok.'], 403);
        }
        $saglayici = $r->input('saglayici') === 'elevenlabs' ? 'elevenlabs' : 'google';
        $ses = trim((string) $r->input('ses'));
        $metin = trim((string) $r->input('metin'));
        $telefon = (bool) $r->input('telefon', true);
        $model = (string) $r->input('model', 'eleven_v3');
        if (!isset(self::EL_MODELLER[$model])) $model = 'eleven_v3';
        $stab = max(0, min(1, (float) $r->input('stabilite', 0.5)));
        if ($model === 'eleven_v3') $stab = $stab < 0.25 ? 0.0 : ($stab < 0.75 ? 0.5 : 1.0); // v3 yalniz 0 / 0.5 / 1
        $hiz = max(0.7, min(1.2, (float) $r->input('hiz', 1.0)));

        if ($ses === '' || $metin === '') return response()->json(['ok' => false, 'hata' => 'Ses ve metin gerekli.'], 422);
        if (mb_strlen($metin) > self::MAKS_KARAKTER) return response()->json(['ok' => false, 'hata' => 'Metin en fazla ' . self::MAKS_KARAKTER . ' karakter olabilir.'], 422);

        $anahtar = md5(json_encode([$saglayici, $ses, $metin, $telefon, $saglayici === 'elevenlabs' ? [$model, $stab, $hiz] : $hiz]));
        $disk = \Storage::disk('local');
        foreach (['wav' => 'audio/wav', 'mp3' => 'audio/mpeg'] as $uz => $tip) {
            $yol = self::ONBELLEK_DIZIN . "/{$anahtar}.{$uz}";
            if ($disk->exists($yol)) return response($disk->get($yol), 200, ['Content-Type' => $tip, 'X-Onbellek' => '1']);
        }

        $t0 = microtime(true);
        try {
            [$veri, $uz] = $saglayici === 'google'
                ? $this->googleUret($ses, $metin, $telefon, $hiz)
                : $this->elUret($ses, $metin, $telefon, $model, $stab, $hiz);
        } catch (\Throwable $e) {
            // 422: 502 donulurse on katman (Cloudflare/nginx) kendi hata sayfasini koyup mesaji yutuyordu
            return response()->json(['ok' => false, 'hata' => $e->getMessage()], 422);
        }
        $ms = (int) round((microtime(true) - $t0) * 1000);
        $disk->put(self::ONBELLEK_DIZIN . "/{$anahtar}.{$uz}", $veri);
        Log::info('[SANTRAL-SES-ONIZLEME]', ['saglayici' => $saglayici, 'ses' => $ses, 'model' => $saglayici === 'elevenlabs' ? $model : null,
            'telefon' => $telefon, 'karakter' => mb_strlen($metin), 'ms' => $ms, 'kullanici' => optional(Auth::guard('sistemyonetim')->user())->name]);
        return response($veri, 200, ['Content-Type' => $uz === 'mp3' ? 'audio/mpeg' : 'audio/wav', 'X-Uretim-Ms' => (string) $ms]);
    }

    /** @return array{0:string,1:string} [ses verisi, uzanti] */
    private function googleUret(string $ses, string $metin, bool $telefon, float $hiz): array
    {
        if ($this->googleKey() === '') throw new \RuntimeException('GOOGLE_TTS_API_KEY tanımlı değil');
        $ac = $telefon
            ? ['audioEncoding' => 'MULAW', 'sampleRateHertz' => 8000, 'speakingRate' => $hiz, 'effectsProfileId' => ['telephony-class-application']]
            : ['audioEncoding' => 'LINEAR16', 'sampleRateHertz' => 24000, 'speakingRate' => $hiz];
        if (stripos($ses, 'Chirp') !== false) unset($ac['speakingRate']); // Chirp HD hiz parametresini her surumde kabul etmiyor
        $d = $this->httpJson('POST', 'https://texttospeech.googleapis.com/v1/text:synthesize?key=' . urlencode($this->googleKey()), [], [
            'input' => ['text' => $metin], 'voice' => ['languageCode' => 'tr-TR', 'name' => $ses], 'audioConfig' => $ac,
        ]);
        $ham = base64_decode((string) ($d['audioContent'] ?? ''));
        if ($ham === '') throw new \RuntimeException('Google boş ses döndürdü');
        if (!$telefon) return [$ham, 'wav']; // LINEAR16 zaten WAV basliklı
        return [$this->ulawWav($this->wavVeri($ham)), 'wav'];
    }

    private function elUret(string $ses, string $metin, bool $telefon, string $model, float $stab, float $hiz): array
    {
        if ($this->elKey() === '') throw new \RuntimeException('ELEVENLABS_API_KEY tanımlı değil (Laravel .env)');
        $govde = ['text' => $metin, 'model_id' => $model, 'voice_settings' => ['stability' => $stab, 'similarity_boost' => 0.75, 'speed' => $hiz]];
        if (preg_match('/v2_5|v3|v4/', $model)) $govde['language_code'] = 'tr';
        $bicim = $telefon ? 'ulaw_8000' : 'mp3_44100_128';
        $ham = $this->httpHam('POST', "https://api.elevenlabs.io/v1/text-to-speech/" . rawurlencode($ses) . "?output_format={$bicim}",
            ['xi-api-key: ' . $this->elKey(), 'Content-Type: application/json'], json_encode($govde, JSON_UNESCAPED_UNICODE));
        return $telefon ? [$this->ulawWav($ham), 'wav'] : [$ham, 'mp3'];
    }

    /** WAV icinden 'data' parcasini ayikla (baslik yoksa veriyi aynen dondur). */
    private function wavVeri(string $b): string
    {
        if (substr($b, 0, 4) !== 'RIFF') return $b;
        $p = strpos($b, 'data');
        return $p === false ? substr($b, 44) : substr($b, $p + 8);
    }

    /** 8 kHz mu-law ham veri -> 16 bit PCM WAV (tarayicilar mu-law WAV'i her zaman calamiyor). */
    private function ulawWav(string $ulaw): string
    {
        static $tablo = null;
        if ($tablo === null) {
            $tablo = [];
            for ($i = 0; $i < 256; $i++) {
                $u = ~$i & 0xFF;
                $isaret = $u & 0x80; $us = ($u >> 4) & 7; $mant = $u & 0x0F;
                $v = ((($mant << 3) + 0x84) << $us) - 0x84;
                $tablo[$i] = pack('v', $isaret ? (-$v & 0xFFFF) : $v);
            }
        }
        $pcm = '';
        $n = strlen($ulaw);
        for ($i = 0; $i < $n; $i++) $pcm .= $tablo[ord($ulaw[$i])];
        $boy = strlen($pcm);
        return 'RIFF' . pack('V', 36 + $boy) . 'WAVE' . 'fmt ' . pack('VvvVVvv', 16, 1, 1, 8000, 16000, 2, 16) . 'data' . pack('V', $boy) . $pcm;
    }

    private function hataAt(string $m)
    {
        throw new \RuntimeException($m);
    }

    private function httpJson(string $yontem, string $url, array $basliklar = [], ?array $govde = null): array
    {
        $ham = $this->httpHam($yontem, $url, array_merge($basliklar, $govde !== null ? ['Content-Type: application/json'] : []),
            $govde !== null ? json_encode($govde, JSON_UNESCAPED_UNICODE) : null);
        $d = json_decode($ham, true);
        if (!is_array($d)) throw new \RuntimeException('Geçersiz cevap');
        return $d;
    }

    private function httpHam(string $yontem, string $url, array $basliklar, ?string $govde = null): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 45, CURLOPT_CUSTOMREQUEST => $yontem, CURLOPT_HTTPHEADER => $basliklar,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ]);
        if ($govde !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $govde);
        $ham = curl_exec($ch);
        $kod = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hata = curl_error($ch);
        curl_close($ch);
        if ($ham === false) throw new \RuntimeException('Bağlantı hatası: ' . $hata);
        if ($kod !== 200) {
            $j = json_decode($ham, true);
            $m = $j['detail']['message'] ?? ($j['error']['message'] ?? ($j['detail'] ?? substr($ham, 0, 200)));
            if (is_array($m)) $m = json_encode($m, JSON_UNESCAPED_UNICODE);
            if (stripos((string) $m, 'library voices') !== false || stripos((string) $m, 'paid_plan') !== false) {
                return $this->hataAt('Bu Türkçe kütüphane sesi ücretsiz planda kullanılamıyor. ElevenLabs Starter (ücretli) plana geçince dinlenebilir ve santralde kullanılabilir.');
            }
            throw new \RuntimeException("HTTP {$kod}: {$m}");
        }
        return $ham;
    }
}
