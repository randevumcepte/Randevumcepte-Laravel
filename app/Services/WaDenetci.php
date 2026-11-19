<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp Asistani "Gece Denetcisi": asistanin sohbetlerini her gece inceler, kendini gelistirir.
 *
 *  1) gonder(): bitmis sohbetler (son 3 gun, henuz incelenmemis) Anthropic Batch API'ye (yari fiyat) yollanir.
 *     Denetci modeli (Sonnet) her sohbete puan verir, sorunlari (istek atlandi, gereksiz soru, uzun akis...) ve
 *     "dogrusu buydu" cevabini yazar.
 *  2) topla(): batch bitince sonuclar wa_denetim'e yazilir; isletme basina dersUret():
 *     - davranis dersi: yeniden oynatma testinden GECERSE asistanin talimatina eklenir (RandevuAsistan waDersler).
 *       Test: sorunlu sohbetlerin o ani + iyi giden sohbetler, ders OLMADAN ve ILE yeniden uretilir (araclar
 *       CALISTIRILMAZ: randevu olusmaz), denetci hangisinin daha iyi oldugunu kor degerlendirir.
 *     - bilgi eksigi (isletmeye ozel bilgi: "Damla Hanım hangi gün?"): panelde isletmenin cevabini bekler.
 *     - kod bulgusu: gelistirici raporu (panel + log).
 *  3) Fiyat / kampanya / politika iceren ders ASLA otomatik uygulanmaz (dersGuvenliMi).
 *  4) haftalikOzet(): pazartesi isletmeye kisa ozet bildirimi.
 */
class WaDenetci
{
    const SEMA = 'wa_denetci_sema_v1';
    const SORUN_TURLERI = ['istek_atlandi', 'gereksiz_soru', 'uzun_akis', 'yanlis_bilgi', 'anlamadi', 'ton', 'bilgi_eksik', 'gereksiz_aktarma', 'teknik_hata', 'diger'];
    const SORUN_AD = [
        'istek_atlandi' => 'Müşterinin isteği atlandı', 'gereksiz_soru' => 'Bilinen bilgi tekrar soruldu', 'uzun_akis' => 'Randevu gereğinden uzun sürdü',
        'yanlis_bilgi' => 'Yanlış / eksik bilgi', 'anlamadi' => 'Müşteri anlaşılmadı', 'ton' => 'Üslup', 'bilgi_eksik' => 'İşletme bilgisi eksik',
        'gereksiz_aktarma' => 'Gereksiz işletmeye aktarma', 'teknik_hata' => 'Teknik hata', 'diger' => 'Diğer',
    ];
    const MAX_AKTIF_DERS = 25;

    // ------------------------------------------------------------------ sema
    public static function semaGaranti(): bool
    {
        if (Cache::get(self::SEMA)) return true;
        if (!WaAsistanVeri::semaGaranti()) return false;
        try {
            if (!Schema::hasTable('wa_denetim')) {
                Schema::create('wa_denetim', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');
                    $t->unsignedBigInteger('isletme_id');
                    $t->unsignedBigInteger('oturum_id')->unique();
                    $t->unsignedBigInteger('is_id')->nullable();
                    $t->string('durum', 12)->default('bekliyor');      // bekliyor | tamam | hata
                    $t->tinyInteger('puan')->nullable();               // 0-100
                    $t->text('ozet')->nullable();
                    $t->text('sorunlar')->nullable();                  // json [{tur, mesaj_no, aciklama, daha_iyi_cevap}]
                    $t->text('oneriler')->nullable();                  // json ders_onerileri + bilgi_eksikleri
                    $t->text('metrik')->nullable();                    // json (kodla olculen)
                    $t->tinyInteger('randevu')->default(0);
                    $t->unsignedInteger('musteri_mesaj')->default(0);
                    $t->decimal('maliyet_usd', 12, 6)->default(0);
                    $t->timestamps();
                    $t->index(['isletme_id', 'created_at'], 'wa_den_isletme_tarih_idx');
                });
            }
            if (!Schema::hasTable('wa_denetim_is')) {
                Schema::create('wa_denetim_is', function ($t) {
                    $t->bigIncrements('id');
                    $t->string('batch_id', 100)->nullable();
                    $t->string('durum', 15)->default('gonderildi');   // gonderildi | bitti | hata
                    $t->unsignedInteger('adet')->default(0);
                    $t->text('hata')->nullable();
                    $t->timestamps();
                });
            }
            if (!Schema::hasTable('wa_asistan_dersler')) {
                Schema::create('wa_asistan_dersler', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('isletme_id');
                    $t->string('tur', 12);                             // davranis | bilgi | kod
                    $t->text('metin');                                 // ders cumlesi / soru / bulgu basligi
                    $t->text('gerekce')->nullable();
                    $t->text('kanit')->nullable();                     // json [{oturum_id, mesaj_no}]
                    $t->string('durum', 12);                           // aktif | oneri | reddedildi | kapali | cevaplandi
                    $t->text('test')->nullable();                      // json yeniden oynatma sonucu
                    $t->string('kaynak', 12)->default('denetci');
                    $t->timestamps();
                    $t->index(['isletme_id', 'durum'], 'wa_ders_isletme_durum_idx');
                });
            }
            foreach (['denetci' => 1, 'denetci_oto' => 1] as $kolon => $vars) {
                if (!Schema::hasColumn('wa_asistan_ayarlar', $kolon)) {
                    Schema::table('wa_asistan_ayarlar', function ($t) use ($kolon, $vars) { $t->tinyInteger($kolon)->default($vars); });
                }
            }
            Cache::forever(self::SEMA, 1);
            return true;
        } catch (\Throwable $e) {
            Log::error('[WA-DENETCI] sema olusturulamadi: ' . $e->getMessage());
            return false;
        }
    }

    // ------------------------------------------------------------------ saf yardimcilar (testli)
    /** gecmis -> numarali transkript satirlari [['no','rol','metin','baglam']] (yalniz dolu user/assistant). */
    public static function transkript(array $gecmis): array
    {
        $out = []; $no = 0;
        foreach ($gecmis as $i => $m) {
            $rol = $m['role'] ?? ''; $c = trim((string) ($m['content'] ?? ''));
            if (!in_array($rol, ['user', 'assistant'], true) || $c === '') continue;
            $no++;
            $out[] = ['no' => $no, 'i' => $i, 'rol' => $rol === 'user' ? 'MÜŞTERİ' : (!empty($m['personel']) ? 'PERSONEL' : 'ASİSTAN'),
                'metin' => $c, 'baglam' => mb_substr(is_array($m['baglam'] ?? null) ? implode(' ', $m['baglam']) : (string) ($m['baglam'] ?? ''), 0, 300)];
        }
        return $out;
    }

    public static function transkriptMetni(array $satirlar): string
    {
        return implode("\n", array_map(function ($s) {
            return '[' . $s['no'] . '] ' . $s['rol'] . ': ' . $s['metin'] . ($s['baglam'] !== '' ? "\n    (asistanın araç sonucu, müşteri görmedi: {$s['baglam']})" : '');
        }, $satirlar));
    }

    /** Kodla olculen sinyaller (bedava). */
    public static function metrikler(array $gecmis, ?string $sonuc = null): array
    {
        $t = self::transkript($gecmis);
        $musteri = 0; $asistan = 0; $personel = 0; $randevuyaKadar = null; $anlamadi = 0; $sorular = []; $tekrarSoru = 0;
        foreach ($t as $s) {
            if ($s['rol'] === 'MÜŞTERİ') {
                $musteri++;
                if (preg_match('/\b(anlamadım|anlamadim|ne\?|ne demek|nasıl yani|tekrar yaz|dediğim gibi|demiştim|yazmıştım|zaten)\b/iu', $s['metin'])) $anlamadi++;
            } elseif ($s['rol'] === 'PERSONEL') {
                $personel++;
            } else {
                $asistan++;
                preg_match_all('/[^.!?\n]*\?/u', $s['metin'], $mm);
                foreach ($mm[0] as $soru) {
                    $soru = mb_strtolower(trim(preg_replace('/[^\p{L}\s?]/u', '', $soru)));
                    if (mb_strlen($soru) < 12) continue;
                    if (in_array($soru, $sorular, true)) $tekrarSoru++;
                    $sorular[] = $soru;
                }
                if ($randevuyaKadar === null && preg_match('/randevunuz(u)?\s+(oluşturdum|oluşturuldu|alındı|kaydettim)/iu', $s['metin'])) $randevuyaKadar = $musteri;
            }
        }
        return ['musteri_mesaj' => $musteri, 'asistan_mesaj' => $asistan, 'personel_mesaj' => $personel,
            'randevu' => $sonuc === 'randevu' || $randevuyaKadar !== null, 'randevuya_kadar' => $randevuyaKadar,
            'anlamadi' => $anlamadi, 'tekrar_soru' => $tekrarSoru, 'aktarildi' => $sonuc === 'aktar'];
    }

    /** Fiyat, indirim, kampanya, para/odeme politikasi ya da musteriye soz iceren ders otomatik uygulanmaz. */
    public static function dersGuvenliMi(string $metin): bool
    {
        if (mb_strlen($metin) < 15 || mb_strlen($metin) > 300) return false;
        if (preg_match('/\d+\s*(tl|₺|lira|%)|\b(fiyat|ücret|indirim|kampanya|bedava|ücretsiz|iade|kapora|depozito|ödeme|taksit|garanti|söz ver)/iu', $metin)) return false;
        return true;
    }

    /**
     * Yeniden oynatma karari. $sonuclar: [['grup' => 'kanit'|'iyi', 'kazanan' => 'yeni'|'eski'|'esit']].
     * Kabul: sorunlu anlarin en az yarisinda yeni daha iyi VE yeni > eski; iyi sohbetlerin en fazla 1/3'unde kotulesme.
     */
    public static function testKarari(array $sonuclar): array
    {
        $say = ['kanit' => ['yeni' => 0, 'eski' => 0, 'esit' => 0], 'iyi' => ['yeni' => 0, 'eski' => 0, 'esit' => 0]];
        foreach ($sonuclar as $s) {
            $grup = ($s['grup'] ?? '') === 'iyi' ? 'iyi' : 'kanit';
            $kaz = in_array($s['kazanan'] ?? '', ['yeni', 'eski'], true) ? $s['kazanan'] : 'esit';
            $say[$grup][$kaz]++;
        }
        $k = $say['kanit']; $i = $say['iyi'];
        $nk = array_sum($k); $ni = array_sum($i);
        $gecti = $nk > 0 && $k['yeni'] >= max(1, (int) ceil($nk / 2)) && $k['yeni'] > $k['eski'] && $i['eski'] <= (int) floor($ni / 3);
        return ['gecti' => $gecti, 'kanit' => $k, 'iyi' => $i];
    }

    /** Test icin gecmis kesiti: mesaj_no'daki asistan cevabindan ONCEKI kisim (musteri mesajiyla biter). */
    public static function kesit(array $gecmis, int $mesajNo): array
    {
        $t = self::transkript($gecmis);
        $son = null;
        foreach ($t as $s) {
            if ($s['no'] > $mesajNo) break;
            if ($s['no'] === $mesajNo && $s['rol'] !== 'MÜŞTERİ') break;
            $son = $s;
        }
        if (!$son) return [];
        $kes = array_slice($gecmis, 0, $son['i'] + 1);
        while ($kes && (($kes[count($kes) - 1]['role'] ?? '') !== 'user')) array_pop($kes);
        return $kes;
    }

    public static function denetimSemasi(): array
    {
        $obj = function (array $props) { return ['type' => 'object', 'additionalProperties' => false, 'required' => array_keys($props), 'properties' => $props]; };
        return $obj([
            'puan' => ['type' => 'integer'],
            'ozet' => ['type' => 'string'],
            'sorunlar' => ['type' => 'array', 'items' => $obj([
                'tur' => ['type' => 'string', 'enum' => self::SORUN_TURLERI], 'mesaj_no' => ['type' => 'integer'],
                'aciklama' => ['type' => 'string'], 'daha_iyi_cevap' => ['type' => 'string'],
            ])],
            'ders_onerileri' => ['type' => 'array', 'items' => $obj(['tur' => ['type' => 'string', 'enum' => ['davranis', 'bilgi', 'kod']], 'metin' => ['type' => 'string']])],
            'bilgi_eksikleri' => ['type' => 'array', 'items' => $obj(['soru' => ['type' => 'string'], 'not' => ['type' => 'string']])],
        ]);
    }

    public static function denetimTalimati(): string
    {
        return <<<T
Sen bir güzellik / bakım salonunun WhatsApp randevu asistanını denetleyen kıdemli bir müşteri deneyimi uzmanısın.
Sana bir WhatsApp yazışması (numaralı), işletme bilgileri ve kodla ölçülen sinyaller verilecek. Amaç: asistan müşteriye EN DOĞRU cevabı EN HIZLI biçimde versin ve randevuya en az mesajla ulaşsın.

Değerlendir:
- Müşterinin ilk mesajdaki isteği (hizmet, personel, gün, saat) hemen kullanıldı mı, yoksa atlanıp hazır metin mi yazıldı? (istek_atlandi)
- Bilinen ya da gereksiz bir şey soruldu mu (WhatsApp numarası, zaten söylenen gün/saat/hizmet)? (gereksiz_soru)
- Randevu gereğinden fazla mesaj sürdü mü? Bilgiler tek mesajda toplanabilir miydi? (uzun_akis)
- Yanlış, uydurma ya da eksik bilgi verildi mi? (yanlis_bilgi)
- Müşteri anlaşılmadı mı, yazım hatası/kısaltma kaçırıldı mı? (anlamadi)
- Üslup soğuk, robotik, fazla uzun ya da gereksiz resmi mi? (ton)
- İşletmenin bilmesi gereken ama sistemde olmayan bir bilgi mi soruldu (çalışma günü, personel uzmanlığı, otopark, ödeme yöntemi...)? (bilgi_eksik)
- Asistan gereksiz yere işletmeye aktardı mı? (gereksiz_aktarma)
- Sistem/araç hatası gibi görünen bir şey var mı? (teknik_hata)
PERSONEL satırları işletme çalışanının elle yazdıklarıdır: asistanın nasıl cevap vermesi gerektiğine dair en güvenilir örnektir.

Çıktı kuralları:
- puan: 0-100 (100 = kusursuz ve hızlı). Kısa, sorunsuz bilgi sohbetleri de yüksek puan alır.
- ozet: 1 cümle, Türkçe.
- sorunlar: yalnız gerçek sorunlar; mesaj_no sorunlu ASİSTAN mesajının numarası; daha_iyi_cevap o noktada asistanın yazması gereken tam mesaj (kısa, WhatsApp üslubunda; fiyat/saat UYDURMA, bilmiyorsan genel yaz).
- ders_onerileri: asistanın genel davranışı için tek cümlelik, emir kipinde, müşteri adı içermeyen dersler (tur=davranis); işletmeye özel eksik bilgi (tur=bilgi); kod/sistem hatası (tur=kod). Sorun yoksa boş dizi.
- bilgi_eksikleri: müşterinin sorduğu ama asistanın bilmediği işletme bilgileri (soru + kısa not).
Fiyat, indirim, kampanya ya da işletme politikası UYDURAN ders önerme.
T;
    }

    // ------------------------------------------------------------------ API
    protected static function anahtar(): ?string
    {
        return config('services.anthropic.key') ?: env('ANTHROPIC_API_KEY') ?: null;
    }

    protected static function model(): string
    {
        return (string) (config('services.wa_asistan.denetci_model') ?: 'claude-sonnet-5-5');
    }

    /** @return array [kod, json|null, ham] */
    protected static function http(string $method, string $url, ?array $body = null, int $timeout = 60): array
    {
        $key = self::anahtar();
        if (!$key) return [0, null, 'anahtar_yok'];
        $ch = curl_init($url);
        $opt = [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => ['content-type: application/json', 'x-api-key: ' . $key, 'anthropic-version: 2023-06-01']];
        if ($method === 'POST') { $opt[CURLOPT_POST] = true; $opt[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_UNICODE); }
        curl_setopt_array($ch, $opt);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($raw === false || $code !== 200) Log::warning('[WA-DENETCI] api', ['url' => $url, 'kod' => $code, 'yanit' => mb_substr((string) ($raw ?: $err), 0, 300)]);
        return [$code, is_string($raw) ? json_decode($raw, true) : null, $raw === false ? $err : $raw];
    }

    /** Tek (batch disi) cagri; JSON semali. */
    protected static function tekCagri(string $sistem, string $metin, array $sema, int $max = 3000): array
    {
        $body = ['model' => self::model(), 'max_tokens' => $max, 'system' => $sistem, 'messages' => [['role' => 'user', 'content' => $metin]],
            'output_config' => ['effort' => 'medium', 'format' => ['type' => 'json_schema', 'schema' => $sema]]];
        [$code, $j] = self::http('POST', 'https://api.anthropic.com/v1/messages', $body, 120);
        if ($code === 429 || $code >= 500 || $code === 0) { sleep(2); [$code, $j] = self::http('POST', 'https://api.anthropic.com/v1/messages', $body, 120); }
        return [self::jsonCikar($j), self::usd($j, false)];
    }

    public static function jsonCikar($j): ?array
    {
        if (!is_array($j) || ($j['stop_reason'] ?? '') === 'refusal') return null;
        $txt = '';
        foreach ((array) ($j['content'] ?? []) as $b) if (($b['type'] ?? '') === 'text') $txt .= $b['text'] ?? '';
        return \App\Services\Gorsel\AnthropicGorselSaglayici::jsonAyikla($txt);
    }

    public static function usd($j, bool $batch): float
    {
        $u = is_array($j) ? ($j['usage'] ?? []) : [];
        $girdi = (int) ($u['input_tokens'] ?? 0) + (int) ($u['cache_read_input_tokens'] ?? 0) + (int) ($u['cache_creation_input_tokens'] ?? 0);
        $usd = ($girdi * (float) config('services.wa_asistan.gorsel_girdi_usd_m', 2.0) + (int) ($u['output_tokens'] ?? 0) * (float) config('services.wa_asistan.gorsel_cikti_usd_m', 10.0)) / 1000000;
        return $batch ? $usd / 2 : $usd;
    }

    // ------------------------------------------------------------------ ayar
    public static function acikMi(int $isletmeId): bool
    {
        $a = WaAsistanVeri::ayarlar($isletmeId);
        return (int) ($a['denetci'] ?? 1) === 1;
    }

    // ------------------------------------------------------------------ 1) gonder
    /** Incelenecek sohbetleri batch'e yollar. $isletmeId verilirse yalniz o isletme ("Şimdi incele"). */
    public static function gonder(?int $isletmeId = null, int $limitIsletme = 80): array
    {
        if (!self::semaGaranti()) return ['ok' => false, 'hata' => 'sema'];
        $q = DB::table('wa_asistan_oturumlari as o')->leftJoin('wa_denetim as d', 'd.oturum_id', '=', 'o.id')->whereNull('d.id')
            ->where('o.son_mesaj_at', '<', now()->subMinutes($isletmeId ? 30 : 120))->where('o.son_mesaj_at', '>=', now()->subDays(3))
            ->orderByDesc('o.id')->select('o.*');
        if ($isletmeId) $q->where('o.isletme_id', $isletmeId);
        $istekler = []; $satirlar = []; $sayac = []; $baglam = []; $acik = [];
        foreach ($q->limit(2000)->get() as $o) {
            $iid = (int) $o->isletme_id;
            if (!isset($acik[$iid])) $acik[$iid] = WaAsistan::acikMi((int) $o->salon_id) && self::acikMi($iid);
            if (!$acik[$iid] || ($sayac[$iid] ?? 0) >= $limitIsletme) continue;
            $gecmis = json_decode($o->gecmis ?: '[]', true) ?: [];
            $m = self::metrikler($gecmis, $o->sonuc);
            if ($m['musteri_mesaj'] < 1 || ($m['asistan_mesaj'] + $m['personel_mesaj']) < 1) continue;
            if (!isset($baglam[$iid])) $baglam[$iid] = self::isletmeBaglami($iid);
            $sayac[$iid] = ($sayac[$iid] ?? 0) + 1;
            $istekler[] = ['custom_id' => 'oturum-' . $o->id, 'params' => self::denetimParametreleri($gecmis, $m, $baglam[$iid])];
            $satirlar[] = ['salon_id' => (int) $o->salon_id, 'isletme_id' => $iid, 'oturum_id' => (int) $o->id, 'durum' => 'bekliyor',
                'metrik' => json_encode($m, JSON_UNESCAPED_UNICODE), 'randevu' => $m['randevu'] ? 1 : 0, 'musteri_mesaj' => $m['musteri_mesaj'],
                'created_at' => now(), 'updated_at' => now()];
        }
        if (!$istekler) return ['ok' => true, 'adet' => 0];
        [$code, $j, $ham] = self::http('POST', 'https://api.anthropic.com/v1/messages/batches', ['requests' => $istekler], 120);
        if ($code !== 200 || empty($j['id'])) {
            DB::table('wa_denetim_is')->insert(['durum' => 'hata', 'adet' => count($istekler), 'hata' => mb_substr((string) $ham, 0, 1000), 'created_at' => now(), 'updated_at' => now()]);
            return ['ok' => false, 'hata' => 'batch_' . $code];
        }
        $isId = DB::table('wa_denetim_is')->insertGetId(['batch_id' => $j['id'], 'durum' => 'gonderildi', 'adet' => count($istekler), 'created_at' => now(), 'updated_at' => now()]);
        foreach ($satirlar as $s) {
            try { DB::table('wa_denetim')->insert($s + ['is_id' => $isId]); } catch (\Throwable $e) {} // ayni oturum baska iste (unique)
        }
        Log::info('[WA-DENETCI] batch gonderildi', ['batch' => $j['id'], 'adet' => count($istekler)]);
        return ['ok' => true, 'adet' => count($istekler), 'batch' => $j['id']];
    }

    public static function denetimParametreleri(array $gecmis, array $metrik, string $baglam): array
    {
        $metin = "# İŞLETME\n{$baglam}\n\n# KODLA ÖLÇÜLEN SİNYALLER\n" . json_encode($metrik, JSON_UNESCAPED_UNICODE)
            . "\n\n# YAZIŞMA\n" . self::transkriptMetni(self::transkript($gecmis)) . "\n\nBu yazışmayı şemaya göre değerlendir.";
        return ['model' => self::model(), 'max_tokens' => 4000, 'system' => self::denetimTalimati(),
            'messages' => [['role' => 'user', 'content' => $metin]],
            'output_config' => ['effort' => 'medium', 'format' => ['type' => 'json_schema', 'schema' => self::denetimSemasi()]]];
    }

    /** Denetciye isletme baglami: hizmetler (fiyatli), personeller, calisma saatleri. */
    public static function isletmeBaglami(int $isletmeId): string
    {
        try {
            $as = new RandevuAsistan($isletmeId, null, 'whatsapp');
            $b = $as->waDenetimBaglami();
            $h = implode(', ', array_map(function ($x) { return $x['ad'] . ((float) ($x['fiyat'] ?? 0) > 0 ? ' (' . (int) $x['fiyat'] . ' TL)' : ''); }, array_slice($b['hizmetler'], 0, 60)));
            return "Salon: {$b['salon']}\nHizmetler: " . ($h ?: '-') . "\nPersoneller: " . (implode(', ', $b['personeller']) ?: '-') . "\nÇalışma saatleri: {$b['calisma']}";
        } catch (\Throwable $e) {
            return 'Salon #' . $isletmeId;
        }
    }

    // ------------------------------------------------------------------ 2) topla
    public static function topla(): array
    {
        if (!self::semaGaranti()) return ['ok' => false];
        $islenen = 0; $isletmeler = [];
        foreach (DB::table('wa_denetim_is')->where('durum', 'gonderildi')->orderBy('id')->limit(10)->get() as $is) {
            [$code, $j] = self::http('GET', 'https://api.anthropic.com/v1/messages/batches/' . $is->batch_id);
            if ($code !== 200 || !is_array($j)) continue;
            if (($j['processing_status'] ?? '') !== 'ended') {
                // 24 saati gecen batch: suresi doldu say (sohbetler bir sonraki gece yeniden aday olur)
                if (strtotime($is->created_at) < time() - 26 * 3600) self::isKapat($is->id, 'hata', 'zaman_asimi');
                continue;
            }
            $sonuclar = self::sonuclariAl((string) ($j['results_url'] ?? ''));
            foreach ($sonuclar as $cid => $r) {
                $oid = (int) str_replace('oturum-', '', $cid);
                $d = DB::table('wa_denetim')->where('oturum_id', $oid)->first();
                if (!$d) continue;
                $json = ($r['type'] ?? '') === 'succeeded' ? self::jsonCikar($r['message'] ?? null) : null;
                if (!$json) { DB::table('wa_denetim')->where('id', $d->id)->update(['durum' => 'hata', 'updated_at' => now()]); continue; }
                DB::table('wa_denetim')->where('id', $d->id)->update(self::sonucSatiri($json) + ['maliyet_usd' => self::usd($r['message'] ?? null, true), 'durum' => 'tamam', 'updated_at' => now()]);
                $isletmeler[(int) $d->isletme_id] = 1; $islenen++;
            }
            DB::table('wa_denetim')->where('is_id', $is->id)->where('durum', 'bekliyor')->delete();
            self::isKapat($is->id, 'bitti');
        }
        foreach (array_keys($isletmeler) as $iid) {
            try { self::dersUret($iid); } catch (\Throwable $e) { Log::error('[WA-DENETCI] ders uretilemedi: ' . $e->getMessage(), ['isletme' => $iid]); }
        }
        return ['ok' => true, 'islenen' => $islenen, 'isletme' => count($isletmeler)];
    }

    /** Denetci JSON'u -> wa_denetim kolonlari (saf). */
    public static function sonucSatiri(array $json): array
    {
        $sorunlar = array_values(array_filter((array) ($json['sorunlar'] ?? []), function ($s) { return is_array($s) && in_array($s['tur'] ?? '', self::SORUN_TURLERI, true); }));
        return [
            'puan' => max(0, min(100, (int) ($json['puan'] ?? 0))),
            'ozet' => mb_substr((string) ($json['ozet'] ?? ''), 0, 500),
            'sorunlar' => json_encode(array_slice($sorunlar, 0, 8), JSON_UNESCAPED_UNICODE),
            'oneriler' => json_encode(['dersler' => array_slice((array) ($json['ders_onerileri'] ?? []), 0, 5), 'bilgi' => array_slice((array) ($json['bilgi_eksikleri'] ?? []), 0, 5)], JSON_UNESCAPED_UNICODE),
        ];
    }

    protected static function sonuclariAl(string $url): array
    {
        if ($url === '') return [];
        [$code, , $ham] = self::http('GET', $url, null, 120);
        if ($code !== 200 || !is_string($ham)) return [];
        return self::jsonlCoz($ham);
    }

    /** Batch sonuc JSONL -> [custom_id => result] (saf). */
    public static function jsonlCoz(string $ham): array
    {
        $out = [];
        foreach (preg_split('/\r?\n/', $ham) as $satir) {
            $j = json_decode(trim($satir), true);
            if (is_array($j) && isset($j['custom_id'])) $out[$j['custom_id']] = $j['result'] ?? [];
        }
        return $out;
    }

    protected static function isKapat(int $id, string $durum, ?string $hata = null): void
    {
        DB::table('wa_denetim_is')->where('id', $id)->update(['durum' => $durum, 'hata' => $hata, 'updated_at' => now()]);
    }

    // ------------------------------------------------------------------ 3) ders uret + test
    public static function aktifDersler(int $isletmeId): array
    {
        if (!self::semaGaranti()) return [];
        return Cache::remember("wa_dersler_{$isletmeId}", 10, function () use ($isletmeId) {
            return DB::table('wa_asistan_dersler')->where('isletme_id', $isletmeId)->where('tur', 'davranis')->where('durum', 'aktif')
                ->orderBy('id')->limit(self::MAX_AKTIF_DERS)->pluck('metin')->all();
        });
    }

    public static function dersUret(int $isletmeId): void
    {
        $denetimler = DB::table('wa_denetim')->where('isletme_id', $isletmeId)->where('durum', 'tamam')->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('id')->limit(80)->get();
        $adlar = DB::table('wa_asistan_oturumlari')->whereIn('id', $denetimler->pluck('oturum_id')->all())->pluck('ad', 'id');
        $girdi = [];
        foreach ($denetimler as $d) {
            foreach ((array) json_decode($d->sorunlar ?: '[]', true) as $s) $girdi[] = ['oturum_id' => (int) $d->oturum_id, 'mesaj_no' => (int) ($s['mesaj_no'] ?? 0), 'tur' => $s['tur'] ?? '', 'aciklama' => $s['aciklama'] ?? '', 'dogrusu' => $s['daha_iyi_cevap'] ?? ''];
            $on = (array) json_decode($d->oneriler ?: '{}', true);
            foreach ((array) ($on['dersler'] ?? []) as $x) $girdi[] = ['oturum_id' => (int) $d->oturum_id, 'oneri' => $x];
            foreach ((array) ($on['bilgi'] ?? []) as $x) $girdi[] = ['oturum_id' => (int) $d->oturum_id, 'bilgi' => $x];
        }
        $yeni = $denetimler->filter(function ($d) { return strtotime($d->updated_at) >= time() - 3 * 3600; });
        if (!$girdi) {
            if ($yeni->count()) self::raporYaz($isletmeId, $yeni->count() . ' sohbeti inceledim ve hiçbirinde sorun bulmadım. Asistan müşterilere doğru ve hızlı cevap vermiş; şu an düzeltmem gereken bir şey yok.');
            return;
        }
        $mevcut =DB::table('wa_asistan_dersler')->where('isletme_id', $isletmeId)->whereIn('durum', ['aktif', 'oneri', 'reddedildi', 'kapali', 'cevaplandi'])
            ->where('created_at', '>=', now()->subDays(60))->get(['tur', 'metin', 'durum']);
        $sema = ['type' => 'object', 'additionalProperties' => false, 'required' => ['rapor', 'dersler', 'bilgi', 'kod'], 'properties' => [
            'rapor' => ['type' => 'string'],
            'dersler' => ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['metin', 'gerekce', 'kanitlar'], 'properties' => [
                'metin' => ['type' => 'string'], 'gerekce' => ['type' => 'string'],
                'kanitlar' => ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['oturum_id', 'mesaj_no'], 'properties' => ['oturum_id' => ['type' => 'integer'], 'mesaj_no' => ['type' => 'integer']]]],
            ]]],
            'bilgi' => ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['soru', 'not'], 'properties' => ['soru' => ['type' => 'string'], 'not' => ['type' => 'string']]]],
            'kod' => ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['baslik', 'aciklama', 'oturum_id'], 'properties' => ['baslik' => ['type' => 'string'], 'aciklama' => ['type' => 'string'], 'oturum_id' => ['type' => 'integer']]]],
        ]];
        $sistem = "WhatsApp randevu asistanının son 7 günlük denetim bulgularını, asistanın talimatına eklenecek DERSLERE dönüştürüyorsun.\n"
            . "- dersler: en fazla 3, en çok tekrarlanan ve randevuyu en çok etkileyen sorunlardan. Her ders tek cümle, emir kipinde, genel (müşteri adı yok), 220 karakteri geçmez. "
            . "Fiyat, indirim, kampanya, ödeme ya da işletme politikası içeren ders YAZMA. Zaten var olan, reddedilmiş ya da kapatılmış derslerin aynısını/benzerini YAZMA. "
            . "kanitlar: dersin düzelteceği sorunun görüldüğü (oturum_id, mesaj_no) çiftleri (en fazla 4, girdiden aynen al).\n"
            . "- bilgi: müşterilerin sorduğu ama işletmenin sisteme girmesi gereken bilgiler (tekrarları birleştir, en fazla 5).\n"
            . "- kod: sistem/araç hatası gibi görünen bulgular (en fazla 3).\n"
            . "- rapor: işletme sahibine, birinci ağızdan ('inceledim', 'düzelteceğim') sade Türkçe kısa rapor. Teknik terim, kod, oturum numarası YOK. "
            . "Satır başı '• ' ile 4-8 madde: kaç sohbet incelediğin ve genel durum; yakaladığın en önemli sorunlar (müşteri adıyla, ne oldu, doğrusu neydi); "
            . "neleri düzelteceğin (yukarıdaki dersler, her biri sade bir cümle); işletmeden ne beklediğin (bilgi soruları, elle bakılması gereken müşteri). Bulgu yoksa uydurma.";
        $ozetler = $yeni->take(40)->map(function ($d) use ($adlar) { return '- ' . ($adlar[$d->oturum_id] ?? 'Müşteri') . " (puan {$d->puan}): " . $d->ozet; })->all();
        $metin = '# BU İNCELEME: ' . $yeni->count() . " yeni sohbet\n" . implode("\n", $ozetler) . "\n\n# MEVCUT DERSLER (tekrarlama)\n" . ($mevcut->count() ? implode("\n", $mevcut->map(function ($x) { return "- [{$x->durum}/{$x->tur}] {$x->metin}"; })->all()) : '- yok')
            . "\n\n# BULGULAR\n" . json_encode(array_slice($girdi, 0, 150), JSON_UNESCAPED_UNICODE);
        [$j] = self::tekCagri($sistem, $metin, $sema, 5000);
        if (!$j) return;
        if (trim((string) ($j['rapor'] ?? '')) !== '') self::raporYaz($isletmeId, (string) $j['rapor']);
        $aktifSay =DB::table('wa_asistan_dersler')->where('isletme_id', $isletmeId)->where('tur', 'davranis')->where('durum', 'aktif')->count();
        $oto = (int) (WaAsistanVeri::ayarlar($isletmeId)['denetci_oto'] ?? 1) === 1;
        foreach (array_slice((array) ($j['dersler'] ?? []), 0, 3) as $ders) {
            $metinD = trim((string) ($ders['metin'] ?? ''));
            if ($metinD === '') continue;
            $kanit = array_slice((array) ($ders['kanitlar'] ?? []), 0, 4);
            $id = DB::table('wa_asistan_dersler')->insertGetId(['isletme_id' => $isletmeId, 'tur' => 'davranis', 'metin' => mb_substr($metinD, 0, 300),
                'gerekce' => mb_substr((string) ($ders['gerekce'] ?? ''), 0, 1000), 'kanit' => json_encode($kanit), 'durum' => 'oneri', 'created_at' => now(), 'updated_at' => now()]);
            if (!self::dersGuvenliMi($metinD)) { DB::table('wa_asistan_dersler')->where('id', $id)->update(['test' => json_encode(['not' => 'Fiyat/politika içerdiği için otomatik uygulanmadı; onayınızı bekliyor.'])]); continue; }
            $t = self::testEt($isletmeId, $metinD, $kanit);
            $durum = $t['gecti'] ? (($oto && $aktifSay < self::MAX_AKTIF_DERS) ? 'aktif' : 'oneri') : 'reddedildi';
            if ($durum === 'aktif') $aktifSay++;
            DB::table('wa_asistan_dersler')->where('id', $id)->update(['durum' => $durum, 'test' => json_encode($t, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);
        }
        foreach (array_slice((array) ($j['bilgi'] ?? []), 0, 5) as $b) {
            $soru = trim((string) ($b['soru'] ?? ''));
            if ($soru === '' || DB::table('wa_asistan_dersler')->where('isletme_id', $isletmeId)->where('tur', 'bilgi')->where('metin', $soru)->exists()) continue;
            DB::table('wa_asistan_dersler')->insert(['isletme_id' => $isletmeId, 'tur' => 'bilgi', 'metin' => mb_substr($soru, 0, 300), 'gerekce' => mb_substr((string) ($b['not'] ?? ''), 0, 1000), 'durum' => 'oneri', 'created_at' => now(), 'updated_at' => now()]);
        }
        foreach (array_slice((array) ($j['kod'] ?? []), 0, 3) as $k) {
            $bas = trim((string) ($k['baslik'] ?? ''));
            if ($bas === '' || DB::table('wa_asistan_dersler')->where('isletme_id', $isletmeId)->where('tur', 'kod')->where('metin', $bas)->exists()) continue;
            DB::table('wa_asistan_dersler')->insert(['isletme_id' => $isletmeId, 'tur' => 'kod', 'metin' => mb_substr($bas, 0, 300), 'gerekce' => mb_substr((string) ($k['aciklama'] ?? ''), 0, 1000),
                'kanit' => json_encode([['oturum_id' => (int) ($k['oturum_id'] ?? 0), 'mesaj_no' => 0]]), 'durum' => 'oneri', 'created_at' => now(), 'updated_at' => now()]);
            Log::warning('[WA-DENETCI] gelistirici bulgusu', ['isletme' => $isletmeId, 'baslik' => $bas, 'aciklama' => $k['aciklama'] ?? '']);
        }
        Cache::forget("wa_dersler_{$isletmeId}");
    }

    /** Denetcinin isletmeye sade anlatimi (panelde "Denetçinin raporu"); dersler tablosunda tur=rapor, son 10 tutulur. */
    public static function raporYaz(int $isletmeId, string $metin): void
    {
        DB::table('wa_asistan_dersler')->insert(['isletme_id' => $isletmeId, 'tur' => 'rapor', 'metin' => mb_substr(trim($metin), 0, 3000), 'durum' => 'rapor', 'created_at' => now(), 'updated_at' => now()]);
        $eski = DB::table('wa_asistan_dersler')->where('isletme_id', $isletmeId)->where('tur', 'rapor')->orderByDesc('id')->skip(10)->take(100)->pluck('id')->all();
        if ($eski) DB::table('wa_asistan_dersler')->whereIn('id', $eski)->delete();
    }

    /**
     * Yeniden oynatma: sorunlu anlar (kanit) + iyi giden sohbetlerin ilk cevabi; ders OLMADAN ve ILE asistan cevabi uretilir
     * (araclar calistirilmaz), denetci kor karsilastirir.
     */
    public static function testEt(int $isletmeId, string $ders, array $kanitlar): array
    {
        $ornek = [];
        foreach ($kanitlar as $k) {
            $o = DB::table('wa_asistan_oturumlari')->where('id', (int) ($k['oturum_id'] ?? 0))->where('isletme_id', $isletmeId)->first();
            if (!$o) continue;
            $kes = self::kesit(json_decode($o->gecmis ?: '[]', true) ?: [], (int) ($k['mesaj_no'] ?? 0));
            if ($kes) $ornek[] = ['grup' => 'kanit', 'gecmis' => $kes, 'tel' => $o->musteri_tel ?: $o->telefon, 'ad' => $o->ad];
        }
        $iyiler = DB::table('wa_denetim as d')->join('wa_asistan_oturumlari as o', 'o.id', '=', 'd.oturum_id')->where('d.isletme_id', $isletmeId)
            ->where('d.durum', 'tamam')->where('d.puan', '>=', 85)->orderByDesc('d.id')->limit(3)->get(['o.gecmis', 'o.musteri_tel', 'o.telefon', 'o.ad']);
        foreach ($iyiler as $o) {
            $g = json_decode($o->gecmis ?: '[]', true) ?: [];
            $ilk = []; foreach ($g as $m) { $ilk[] = $m; if (($m['role'] ?? '') === 'user') break; }
            if ($ilk && (end($ilk)['role'] ?? '') === 'user') $ornek[] = ['grup' => 'iyi', 'gecmis' => $ilk, 'tel' => $o->musteri_tel ?: $o->telefon, 'ad' => $o->ad];
        }
        if (!array_filter($ornek, function ($x) { return $x['grup'] === 'kanit'; })) return self::testKarari([]) + ['not' => 'Test için örnek bulunamadı.'];
        $eski = self::aktifDersler($isletmeId);
        $ciftler = []; $usd = 0.0;
        foreach ($ornek as $n => $x) {
            [$a, $ua] = self::denemeCevabi($isletmeId, $x, $eski);
            [$b, $ub] = self::denemeCevabi($isletmeId, $x, array_merge($eski, [$ders]));
            $usd += $ua + $ub;
            if ($a === null || $b === null) continue;
            $ters = mt_rand(0, 1) === 1; // konum yanliligi olmasin
            $ciftler[] = ['no' => $n + 1, 'grup' => $x['grup'], 'ters' => $ters, 'gecmis' => self::transkriptMetni(self::transkript($x['gecmis'])), 'X' => $ters ? $b : $a, 'Y' => $ters ? $a : $b];
        }
        if (!$ciftler) return self::testKarari([]) + ['not' => 'Deneme cevapları üretilemedi.'];
        $sema = ['type' => 'object', 'additionalProperties' => false, 'required' => ['kararlar'], 'properties' => ['kararlar' => ['type' => 'array', 'items' => [
            'type' => 'object', 'additionalProperties' => false, 'required' => ['no', 'kazanan', 'neden'],
            'properties' => ['no' => ['type' => 'integer'], 'kazanan' => ['type' => 'string', 'enum' => ['X', 'Y', 'esit']], 'neden' => ['type' => 'string']]]]]];
        $metin = implode("\n\n", array_map(function ($c) {
            return "## ÖRNEK {$c['no']}\n{$c['gecmis']}\n\nCEVAP X: {$c['X']}\n\nCEVAP Y: {$c['Y']}";
        }, $ciftler));
        [$j, $uj] = self::tekCagri("Bir salonun WhatsApp randevu asistanı için her örnekte iki alternatif sonraki adımı (X ve Y) karşılaştırıyorsun. "
            . "Hangisi müşterinin isteğini daha doğru ve daha hızlı karşılıyor, gereksiz soru sormuyor, randevuya daha az mesajla götürüyor ve bilgi uydurmuyor? "
            . "'→ araç:' satırları asistanın o adımda çağıracağı sistem araçlarıdır (ör. uygun saat sorgusu) ve doğru araç çağrısı iyi bir şeydir. Fark yoksa 'esit' de.", $metin, $sema, 3000);
        $usd += $uj;
        $sonuc = [];
        foreach ((array) ($j['kararlar'] ?? []) as $k) {
            foreach ($ciftler as $c) if ($c['no'] === (int) ($k['no'] ?? 0)) {
                $kaz = $k['kazanan'] === 'esit' ? 'esit' : ((($k['kazanan'] === 'Y') xor $c['ters']) ? 'eski' : 'yeni');
                $sonuc[] = ['grup' => $c['grup'], 'kazanan' => $kaz, 'neden' => mb_substr((string) ($k['neden'] ?? ''), 0, 300)];
            }
        }
        return self::testKarari($sonuc) + ['ornek' => $sonuc, 'maliyet_usd' => round($usd, 4)];
    }

    /** Asistanin (canli model + canli talimat) bu gecmise vereceği ilk adim; araclar CALISTIRILMAZ. @return [metin|null, usd] */
    protected static function denemeCevabi(int $isletmeId, array $x, array $dersler): array
    {
        try {
            $as = new RandevuAsistan($isletmeId, $x['tel'] ?: null, 'whatsapp');
            $as->waProfilAdiAyarla($x['ad'] ?? null);
            $as->waDersleriAyarla($dersler);
            $body = $as->waDenemeIstegi($x['gecmis']);
        } catch (\Throwable $e) {
            return [null, 0.0];
        }
        [$code, $j] = self::http('POST', 'https://api.anthropic.com/v1/messages', $body, 60);
        if ($code !== 200 || !is_array($j)) return [null, 0.0];
        $u = $j['usage'] ?? [];
        $usd = SantralPaket::claudeMaliyetUsd(['girdi' => (int) ($u['input_tokens'] ?? 0), 'cikti' => (int) ($u['output_tokens'] ?? 0),
            'cache_okuma' => (int) ($u['cache_read_input_tokens'] ?? 0), 'cache_yazma' => (int) ($u['cache_creation_input_tokens'] ?? 0)]);
        $out = [];
        foreach ((array) ($j['content'] ?? []) as $b) {
            if (($b['type'] ?? '') === 'text' && trim((string) $b['text']) !== '') $out[] = trim((string) $b['text']);
            if (($b['type'] ?? '') === 'tool_use') $out[] = '→ araç: ' . ($b['name'] ?? '') . ' ' . mb_substr(json_encode($b['input'] ?? [], JSON_UNESCAPED_UNICODE), 0, 200);
        }
        return [$out ? implode("\n", $out) : null, $usd];
    }

    // ------------------------------------------------------------------ 4) haftalik ozet + panel
    public static function panel(int $isletmeId): array
    {
        self::semaGaranti();
        $ayar = WaAsistanVeri::ayarlar($isletmeId);
        $d14 = DB::table('wa_denetim')->where('isletme_id', $isletmeId)->where('durum', 'tamam')->where('created_at', '>=', now()->subDays(14)->startOfDay())
            ->orderByDesc('id')->get(['oturum_id', 'puan', 'ozet', 'sorunlar', 'metrik', 'randevu', 'musteri_mesaj', 'maliyet_usd', 'created_at']);
        $bu = $d14->filter(function ($d) { return strtotime($d->created_at) >= strtotime('-7 days'); });
        $once = $d14->filter(function ($d) { return strtotime($d->created_at) < strtotime('-7 days'); });
        $ort = function ($c, $f) { $v = $c->map($f)->filter(function ($x) { return $x !== null; }); return $v->count() ? round($v->avg(), 1) : null; };
        $randevuMesaj = function ($c) use ($ort) { return $ort($c->filter(function ($d) { return (int) $d->randevu === 1; }), function ($d) { $m = json_decode($d->metrik ?: '{}', true); return isset($m['randevuya_kadar']) ? (int) $m['randevuya_kadar'] : (int) $d->musteri_mesaj; }); };
        $turlar = [];
        foreach ($bu as $d) foreach ((array) json_decode($d->sorunlar ?: '[]', true) as $s) $turlar[$s['tur'] ?? 'diger'] = ($turlar[$s['tur'] ?? 'diger'] ?? 0) + 1;
        arsort($turlar);
        $gunluk = [];
        for ($i = 13; $i >= 0; $i--) $gunluk[date('Y-m-d', strtotime("-{$i} days"))] = [];
        foreach ($d14 as $d) { $g = substr($d->created_at, 0, 10); if (isset($gunluk[$g])) $gunluk[$g][] = (int) $d->puan; }
        $adlar = DB::table('wa_asistan_oturumlari')->whereIn('id', $bu->pluck('oturum_id')->take(30)->all())->pluck('ad', 'id');
        $dersler = DB::table('wa_asistan_dersler')->where('isletme_id', $isletmeId)->whereIn('durum', ['aktif', 'oneri', 'reddedildi', 'kapali'])
            ->orderByDesc('id')->limit(80)->get(['id', 'tur', 'metin', 'gerekce', 'durum', 'test', 'created_at']);
        return [
            'acik' => (int) ($ayar['denetci'] ?? 1), 'oto' => (int) ($ayar['denetci_oto'] ?? 1),
            'puan' => $ort($bu, function ($d) { return (int) $d->puan; }), 'puan_once' => $ort($once, function ($d) { return (int) $d->puan; }),
            'randevu_mesaj' => $randevuMesaj($bu), 'randevu_mesaj_once' => $randevuMesaj($once),
            'incelenen' => $bu->count(), 'sorunlu' => $bu->filter(function ($d) { return (int) $d->puan < 70; })->count(),
            'maliyet_usd' => round($bu->sum('maliyet_usd'), 4),
            'turlar' => array_map(function ($k, $v) { return ['tur' => $k, 'ad' => self::SORUN_AD[$k] ?? $k, 'adet' => $v]; }, array_keys($turlar), $turlar),
            'gunluk' => array_map(function ($g, $p) { return ['gun' => $g, 'puan' => $p ? round(array_sum($p) / count($p)) : null, 'adet' => count($p)]; }, array_keys($gunluk), $gunluk),
            'son' => $bu->take(30)->map(function ($d) use ($adlar) {
                return ['oturum_id' => (int) $d->oturum_id, 'ad' => $adlar[$d->oturum_id] ?? null, 'puan' => (int) $d->puan, 'ozet' => $d->ozet,
                    'sorunlar' => array_map(function ($s) { return ['tur' => self::SORUN_AD[$s['tur'] ?? ''] ?? '', 'aciklama' => $s['aciklama'] ?? '', 'dogrusu' => $s['daha_iyi_cevap'] ?? '']; }, (array) json_decode($d->sorunlar ?: '[]', true)),
                    'tarih' => $d->created_at];
            })->values()->all(),
            'dersler' => $dersler->map(function ($x) { $x->test = json_decode($x->test ?: 'null', true); return $x; })->values()->all(),
            'rapor' => DB::table('wa_asistan_dersler')->where('isletme_id', $isletmeId)->where('tur', 'rapor')->orderByDesc('id')->first(['metin', 'created_at']),
            'bekleyen_is' => DB::table('wa_denetim')->where('isletme_id', $isletmeId)->where('durum', 'bekliyor')->count(),
        ];
    }

    /** Pazartesi: gecen haftanin kisa ozeti isletmeye (panel + push). */
    public static function haftalikOzet(): int
    {
        if (!self::semaGaranti()) return 0;
        $n = 0;
        foreach (DB::table('wa_denetim')->where('durum', 'tamam')->where('created_at', '>=', now()->subDays(7))->distinct()->pluck('isletme_id') as $iid) {
            $p = self::panel((int) $iid);
            $yeni = collect($p['dersler'])->filter(function ($d) { return strtotime($d->created_at) >= strtotime('-7 days'); });
            $metin = 'WhatsApp asistanı bu hafta ' . $p['incelenen'] . ' sohbeti kendi kendine inceledi. Kalite puanı: ' . ($p['puan'] ?? '-')
                . ($p['puan_once'] !== null ? ' (önceki hafta ' . $p['puan_once'] . ')' : '') . '. '
                . $yeni->where('tur', 'davranis')->where('durum', 'aktif')->count() . ' yeni ders öğrendi'
                . ($p['randevu_mesaj'] !== null ? ', randevu ortalama ' . str_replace('.', ',', (string) $p['randevu_mesaj']) . ' mesajda alındı' : '') . '.'
                . (($b = $yeni->where('tur', 'bilgi')->where('durum', 'oneri')->count()) ? " {$b} soru cevabınızı bekliyor (WhatsApp Asistanı > Asistan Gelişimi)." : '');
            WaAsistan::bildir((int) $iid, $metin, '/isletmeyonetim/whatsapp-asistan?sube=' . $iid);
            $n++;
        }
        return $n;
    }
}
