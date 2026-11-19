<?php

namespace App\Services;

use App\Salonlar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp asistani — restaurant-laravel /api/wa/gelen akisinin randevumcepte uyarlamasi.
 *
 * Kopru (whatsmeow-bridge) gelen mesaji "message.received", personelin kendi telefonundan yazdigini
 * "message.manual" olarak WhatsAppWebhookController'a yollar; o da buraya devreder.
 * Cevabi santraldeki RandevuAsistan uretir ('whatsapp' kanal modu); tum randevu araclari ayni.
 *
 * Restoranda OLMAYAN korumalar (ban + maliyet riski) burada en bastan var:
 *  - numara basina hiz siniri (10 dk'da N cevap; asilirsa 60 dk sus + isletmeye bildirim)
 *  - dongu tespiti (ayni mesaj 3 kez) ve "otomatik yanit" kalibi (iki botun birbirine cevap vermesi)
 *  - hat basina gunluk bot cevabi tavani
 *  - haric numaralar: isletme personeli, yetkili telefonu, WA_ASISTAN_HARIC listesi (+ kara liste)
 *  - 10 dk'dan eski (cevrimdisi birikmis) mesajlar kopruda elenir
 * Restorandan aynen: grup/durum mesaji yok, echo ayikligi, personel elle yazinca AI susar (esikten sonra geri alir).
 *
 * Webhook hemen doner; is cevap gonderildikten sonra (app()->terminating) yapilir. Art arda yazilan
 * mesajlar birlestir_sn icinde tek mesaj sayilir; ayni sohbette ayni anda tek islem (DB kilidi).
 */
class WaAsistan
{
    const SEMA = 'wa_asistan_sema_v1';

    // ------------------------------------------------------------------ sema
    public static function semaGaranti(): bool
    {
        if (Cache::get(self::SEMA)) return true;
        try {
            if (!Schema::hasTable('wa_asistan_oturumlari')) {
                Schema::create('wa_asistan_oturumlari', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');                 // WhatsApp hattinin (oturumun) salonu
                    $t->unsignedBigInteger('isletme_id');               // asistanin hizmet verdigi salon (paylasilan hatta farkli olabilir)
                    $t->string('anahtar', 100);                         // numara (90...) ya da gizli numara JID'i (<lid>@lid)
                    $t->string('jid', 100)->nullable();                 // cevap hedefi
                    $t->string('telefon', 30)->nullable();              // WhatsApp numarasi (gizliyse null)
                    $t->string('musteri_tel', 30)->nullable();          // kayitta ogrenilen telefon (gizli numara)
                    $t->string('ad', 100)->nullable();                  // WhatsApp profil adi
                    $t->longText('gecmis')->nullable();                 // JSON [{role,content,...}]
                    $t->string('durum', 15)->default('acik');           // acik | kapandi
                    $t->string('sonuc', 20)->nullable();                // randevu | iptal | guncelle | aktar | teyit ...
                    $t->unsignedBigInteger('randevu_id')->nullable();
                    $t->tinyInteger('insan_devraldi')->default(0);
                    $t->timestamp('son_insan_at')->nullable();
                    $t->timestamp('sessiz_bitis')->nullable();          // bu zamana kadar AI cevap vermez
                    $t->string('sessiz_neden', 30)->nullable();         // aktar | hiz_siniri | dongu | otomatik_yanit
                    $t->text('cevap_zamanlari')->nullable();            // JSON [unix] (hiz siniri)
                    $t->unsignedInteger('mesaj_sayisi')->default(0);
                    $t->unsignedInteger('cevap_sayisi')->default(0);
                    $t->unsignedInteger('girdi_token')->default(0);
                    $t->unsignedInteger('cikti_token')->default(0);
                    $t->unsignedInteger('cache_okuma_token')->default(0);
                    $t->unsignedInteger('cache_yazma_token')->default(0);
                    $t->decimal('maliyet_usd', 12, 6)->default(0);
                    $t->string('kilit', 40)->nullable();
                    $t->timestamp('kilit_at')->nullable();
                    $t->timestamp('son_mesaj_at')->nullable();
                    $t->timestamp('created_at')->nullable();
                    $t->timestamp('updated_at')->nullable();
                    $t->index(['salon_id', 'anahtar']);
                    $t->index(['isletme_id', 'created_at']);
                });
            }
            if (!Schema::hasTable('wa_asistan_gelen')) {
                Schema::create('wa_asistan_gelen', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');
                    $t->string('anahtar', 100);
                    $t->string('jid', 100)->nullable();
                    $t->string('telefon', 30)->nullable();
                    $t->string('ad', 100)->nullable();
                    $t->text('metin');
                    $t->string('islem', 40)->nullable();                // isleyen token (null = bekliyor)
                    $t->timestamp('created_at')->nullable();
                    $t->index(['salon_id', 'anahtar', 'islem']);
                });
            }
            Cache::forever(self::SEMA, 1);
            return true;
        } catch (\Throwable $e) {
            Log::error('[WA-ASISTAN] sema olusturulamadi: ' . $e->getMessage());
            return false;
        }
    }

    // ------------------------------------------------------------------ ayarlar
    protected static function ayar(string $k, $vars = null)
    {
        return config('services.wa_asistan.' . $k, $vars);
    }

    /** Bu salonun WhatsApp hattinda asistan acik mi? (.env WA_ASISTAN_SALONLAR="20,236" | "*") */
    public static function acikMi(int $salonId): bool
    {
        if ($salonId <= 0) return false;
        $l = trim((string) self::ayar('salonlar', ''));
        if ($l !== '' && ($l === '*' || in_array($salonId, array_map('intval', preg_split('/[\s,;]+/', $l)), true))) return true;
        // Sistem Yonetimi > WhatsApp Asistan Paketleri'nde paketi tanimli hat da acik
        $p = WaPaket::paket($salonId);
        return $p && (int) $p->dahil_mesaj > 0;
    }

    protected static function tel(?string $t): ?string
    {
        return $t ? app(WhatsAppService::class)->normalizePhone($t) : null;
    }

    /** Asistanin cevap vermeyecegi numaralar: personel, yetkili, .env listesi. */
    protected static function haricMi(int $salonId, string $tel): bool
    {
        $liste = Cache::remember("wa_asistan_haric_{$salonId}", 10, function () use ($salonId) {
            $out = [];
            foreach (preg_split('/[\s,;]+/', (string) self::ayar('haric', '')) as $n) {
                if ($n = self::tel($n)) $out[] = $n;
            }
            try {
                $salonlar = DB::table('salonlar')->where('whatsapp_paylasilan_salon_id', $salonId)->pluck('id')->push($salonId)->all();
                foreach (DB::table('salon_personelleri')->whereIn('salon_id', $salonlar)->pluck('cep_telefon') as $n) {
                    if ($n = self::tel($n)) $out[] = $n;
                }
                foreach (DB::table('salonlar')->whereIn('id', $salonlar)->pluck('yetkili_telefon') as $n) {
                    if ($n = self::tel($n)) $out[] = $n;
                }
            } catch (\Throwable $e) {}
            return array_values(array_unique($out));
        });
        return in_array($tel, $liste, true);
    }

    // ------------------------------------------------------------------ webhook girisleri
    /** message.received: mesaji kuyruga yaz, isi cevap gonderildikten sonra yap. */
    public static function gelen(array $p): array
    {
        $salonId = (int) ($p['salonId'] ?? 0);
        if (!self::acikMi($salonId)) return ['tip' => 'kapali'];
        $metin = trim((string) ($p['text'] ?? ''));
        // Fotograf (kopru: type=image, image=base64, text=fotograf aciklamasi): Gorsel Danisman
        $resim = ($p['type'] ?? '') === 'image' && !empty($p['image']) ? (string) $p['image'] : '';
        if ($metin === '' && $resim === '') return ['tip' => 'bos'];
        $metin = mb_substr($metin, 0, 1000);
        $jid = (string) ($p['fromJid'] ?? '');
        $lid = !empty($p['fromLid']) || substr($jid, -4) === '@lid';
        $tel = $lid ? null : self::tel((string) ($p['from'] ?? ''));
        $anahtar = $lid ? $jid : (string) $tel;
        // Gizli kimlik (LID): kopru gercek numarayi biliyorsa (fromPn) musteri tanınır, telefon sorulmaz (anahtar LID kalir)
        if ($lid && !empty($p['fromPn'])) $tel = self::tel((string) $p['fromPn']);
        if ($anahtar === '') return ['tip' => 'gecersiz'];
        if (!self::semaGaranti()) return ['tip' => 'sema_yok'];

        $mid = (string) ($p['messageId'] ?? '');
        if ($mid !== '' && !Cache::add('wa_asistan_mid_' . $mid, 1, 60)) return ['tip' => 'tekrar'];
        if ($tel && self::haricMi($salonId, $tel)) {
            Log::info('[WA-ASISTAN] haric numara, cevap yok', ['salon' => $salonId, 'tel' => $tel]);
            return ['tip' => 'haric'];
        }

        $gorselId = null;
        if ($resim !== '' && WaAsistanVeri::semaGaranti()) {
            try { $gorselId = WaGorselDanisman::fotografKaydet($salonId, $anahtar, $resim); }
            catch (\Throwable $e) { Log::warning('[WA-GORSEL] fotograf kaydedilemedi: ' . $e->getMessage()); }
            if (!$gorselId && $metin === '') return ['tip' => 'gorsel_gecersiz'];
        }
        $satir = [
            'salon_id' => $salonId, 'anahtar' => $anahtar, 'jid' => $jid ?: null, 'telefon' => $tel,
            'ad' => mb_substr((string) ($p['pushName'] ?? ''), 0, 100) ?: null, 'metin' => $gorselId ? trim('[fotoğraf] ' . $metin) : $metin,
            'created_at' => now(),
        ];
        if ($gorselId) $satir['gorsel_id'] = $gorselId;
        $id = DB::table('wa_asistan_gelen')->insertGetId($satir);
        app()->terminating(function () use ($salonId, $anahtar, $id) {
            try { self::isle($salonId, $anahtar, $id); }
            catch (\Throwable $e) { Log::error('[WA-ASISTAN] isleme hatasi: ' . $e->getMessage(), ['salon' => $salonId, 'anahtar' => $anahtar]); }
        });
        if (mt_rand(1, 200) === 1) {
            try { DB::table('wa_asistan_gelen')->where('created_at', '<', now()->subDays(7))->delete(); } catch (\Throwable $e) {}
        }
        return ['tip' => 'kuyruk'];
    }

    /** message.manual: personel kendi telefonundan yazdi -> o sohbette AI susar (insan_esik_dk boyunca). */
    public static function manuel(array $p): array
    {
        $salonId = (int) ($p['salonId'] ?? 0);
        if (!self::acikMi($salonId) || !self::semaGaranti()) return ['tip' => 'kapali'];
        $jid = (string) ($p['toJid'] ?? '');
        $lid = substr($jid, -4) === '@lid';
        $tel = $lid ? null : self::tel((string) ($p['to'] ?? ''));
        $anahtar = $lid ? $jid : (string) $tel;
        if ($anahtar === '') return ['tip' => 'gecersiz'];
        $o = self::aktifOturum($salonId, $anahtar) ?: self::yeniOturum($salonId, $anahtar, $jid ?: null, $tel, null);
        $g = json_decode($o->gecmis ?: '[]', true) ?: [];
        $metin = mb_substr(trim((string) ($p['text'] ?? '')), 0, 1000);
        if ($metin !== '') $g[] = ['role' => 'assistant', 'content' => $metin, 'personel' => 1];
        DB::table('wa_asistan_oturumlari')->where('id', $o->id)->update([
            'gecmis' => json_encode(array_slice($g, -40), JSON_UNESCAPED_UNICODE),
            'insan_devraldi' => 1, 'son_insan_at' => now(), 'son_mesaj_at' => now(), 'updated_at' => now(),
        ]);
        Log::info('[WA-ASISTAN] personel devraldi', ['salon' => $salonId, 'oturum' => $o->id]);
        return ['tip' => 'insan_devraldi'];
    }

    // ------------------------------------------------------------------ oturum
    /** Acik sohbet; oturum_dk sessizlikten sonra yeni yazisma (susturulmus sohbet susturma bitene kadar surer). */
    protected static function aktifOturum(int $salonId, string $anahtar)
    {
        $o = DB::table('wa_asistan_oturumlari')->where('salon_id', $salonId)->where('anahtar', $anahtar)
            ->where('durum', 'acik')->orderByDesc('id')->first();
        if (!$o) return null;
        $dk = max(5, (int) self::ayar('oturum_dk', 30));
        $taze = $o->son_mesaj_at && strtotime($o->son_mesaj_at) >= time() - $dk * 60;
        $sessiz = $o->sessiz_bitis && strtotime($o->sessiz_bitis) > time();
        $insan = $o->insan_devraldi && $o->son_insan_at && strtotime($o->son_insan_at) > time() - max(1, (int) self::ayar('insan_esik_dk', 10)) * 60;
        // Geri kazanma mesaji gonderilmis sohbet: musteri 2 gun icinde cevap verirse ayni sohbette devam (baglam: onerilen saatler)
        $gk = !empty($o->geri_kazanim_id) && $o->son_mesaj_at && strtotime($o->son_mesaj_at) >= time() - 48 * 3600;
        if ($taze || $sessiz || $insan || $gk) return $o;
        DB::table('wa_asistan_oturumlari')->where('id', $o->id)->update(['durum' => 'kapandi', 'updated_at' => now()]);
        return null;
    }

    protected static function yeniOturum(int $salonId, string $anahtar, ?string $jid, ?string $tel, ?string $ad)
    {
        $id = DB::table('wa_asistan_oturumlari')->insertGetId([
            'salon_id' => $salonId, 'isletme_id' => $salonId, 'anahtar' => $anahtar, 'jid' => $jid, 'telefon' => $tel,
            'ad' => $ad, 'gecmis' => '[]', 'durum' => 'acik', 'son_mesaj_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        return DB::table('wa_asistan_oturumlari')->where('id', $id)->first();
    }

    // ------------------------------------------------------------------ isleme
    protected static function isle(int $salonId, string $anahtar, int $gelenId): void
    {
        $bek = max(0, min(15, (int) self::ayar('birlestir_sn', 2)));
        if ($bek > 0) sleep($bek);
        // Bu arada musteri yeni mesaj yazdiysa onu isleyen istek hepsini birlikte cevaplar
        $yeniVar = DB::table('wa_asistan_gelen')->where('salon_id', $salonId)->where('anahtar', $anahtar)
            ->whereNull('islem')->where('id', '>', $gelenId)->exists();
        if ($yeniVar) return;

        WaAsistanVeri::semaGaranti(); // ek kolonlar (isaretler, memnuniyet, geri kazanma) + cevap kaydi tablosu
        $ilk = DB::table('wa_asistan_gelen')->where('id', $gelenId)->first();
        $o = self::aktifOturum($salonId, $anahtar)
            ?: self::yeniOturum($salonId, $anahtar, $ilk->jid ?? null, $ilk->telefon ?? null, $ilk->ad ?? null);

        // Ayni sohbette tek islem: onceki cevap uretilirken gelen mesaj, o bitince islenir
        $token = substr(md5(uniqid('', true)), 0, 32);
        $alindi = 0;
        for ($i = 0; $i < 120 && !$alindi; $i++) {
            $alindi = DB::table('wa_asistan_oturumlari')->where('id', $o->id)->where(function ($w) {
                $w->whereNull('kilit')->orWhere('kilit_at', '<', now()->subMinutes(2));
            })->update(['kilit' => $token, 'kilit_at' => now()]);
            if (!$alindi) usleep(500000);
        }
        if (!$alindi) {
            Log::warning('[WA-ASISTAN] sohbet kilidi alinamadi', ['oturum' => $o->id]);
            return;
        }
        try {
            DB::table('wa_asistan_gelen')->where('salon_id', $salonId)->where('anahtar', $anahtar)->whereNull('islem')->update(['islem' => $token]);
            $satirlar = DB::table('wa_asistan_gelen')->where('islem', $token)->orderBy('id')->get();
            if ($satirlar->isEmpty()) return;
            self::cevapla(DB::table('wa_asistan_oturumlari')->where('id', $o->id)->first(), $satirlar);
        } finally {
            DB::table('wa_asistan_oturumlari')->where('id', $o->id)->where('kilit', $token)->update(['kilit' => null, 'kilit_at' => null]);
        }
    }

    protected static function cevapla($o, $satirlar): void
    {
        $son = $satirlar->last();
        $metin = trim($satirlar->pluck('metin')->implode("\n"));
        $gecmis = json_decode($o->gecmis ?: '[]', true) ?: [];
        $tel = $o->telefon ?: $son->telefon;
        $hedef = $son->jid ?: ($o->jid ?: $tel);
        $upd = ['son_mesaj_at' => now(), 'updated_at' => now(), 'mesaj_sayisi' => DB::raw('mesaj_sayisi + ' . count($satirlar))];
        if ($son->jid) $upd['jid'] = $son->jid;
        if ($son->ad && !$o->ad) $upd['ad'] = $son->ad;

        $kaydet = function (array $g) use ($o, &$upd) {
            $upd['gecmis'] = json_encode(array_slice($g, -40), JSON_UNESCAPED_UNICODE);
            DB::table('wa_asistan_oturumlari')->where('id', $o->id)->update($upd);
        };
        // Cevap verilmeyecekse mesaj yine gecmise yazilir (panel + AI geri dondugunde baglam)
        $sus = function (string $neden) use (&$gecmis, $metin, $kaydet, $o) {
            $gecmis[] = ['role' => 'user', 'content' => $metin];
            $kaydet($gecmis);
            Log::info('[WA-ASISTAN] cevap verilmedi: ' . $neden, ['oturum' => $o->id, 'salon' => $o->salon_id]);
        };

        // 1) PERSONEL DEVRALDI: esik dolana kadar AI susar; personel sustuysa AI geri alir
        if ($o->insan_devraldi) {
            if ($o->son_insan_at && strtotime($o->son_insan_at) > time() - max(1, (int) self::ayar('insan_esik_dk', 10)) * 60) {
                $sus('insan_devralmada');
                return;
            }
            $upd['insan_devraldi'] = 0;
        }
        // 2) SUSTURULMUS SOHBET (isletmeye aktarildi / hiz siniri / dongu / otomatik yanit)
        if ($o->sessiz_bitis && strtotime($o->sessiz_bitis) > time()) {
            $sus('sessiz:' . $o->sessiz_neden);
            return;
        }
        // 3) KARSI TARAF BOT: "otomatik yanit" kalibi -> 24 saat sus (iki bot birbirine sonsuz cevap vermesin)
        if (self::otomatikYanitMi($metin)) {
            $upd['sessiz_bitis'] = now()->addHours(24); $upd['sessiz_neden'] = 'otomatik_yanit';
            $sus('otomatik_yanit');
            return;
        }
        // 4) DONGU: ayni mesaj ust uste 3. kez
        $oncekiler = array_values(array_filter($gecmis, function ($m) { return ($m['role'] ?? '') === 'user'; }));
        $n = count($oncekiler);
        if ($n >= 2 && self::sade($oncekiler[$n - 1]['content'] ?? '') === self::sade($metin)
            && self::sade($oncekiler[$n - 2]['content'] ?? '') === self::sade($metin)) {
            $upd['sessiz_bitis'] = now()->addMinutes(60); $upd['sessiz_neden'] = 'dongu';
            $sus('dongu');
            return;
        }
        // 5) NUMARA BASI HIZ SINIRI
        $zamanlar = array_values(array_filter(json_decode($o->cevap_zamanlari ?: '[]', true) ?: [], function ($t) {
            return (int) $t > time() - 600;
        }));
        if (count($zamanlar) >= max(3, (int) self::ayar('numara_10dk', 15))) {
            $upd['sessiz_bitis'] = now()->addMinutes(60); $upd['sessiz_neden'] = 'hiz_siniri';
            $sus('hiz_siniri');
            self::isletmeyeBildir((int) $o->isletme_id, $o, 'WhatsApp asistanı ' . ($tel ?: 'gizli numara')
                . ' ile yazışmada çok sık mesaj aldığı için bu sohbette 1 saat cevap vermeyecek. Gerekirse müşteriye siz dönün.');
            return;
        }
        // 6) HAT BASI GUNLUK TAVAN
        $oturumSalonlari = DB::table('salonlar')->where('whatsapp_paylasilan_salon_id', $o->salon_id)->pluck('id')->push($o->salon_id)->all();
        $bugun = 0;
        try {
            $bugun = DB::table('whatsapp_gonderim_loglari')->whereIn('salon_id', $oturumSalonlari)
                ->where('gonderim_tipi', WhatsAppService::ASISTAN_TIPI)->where('created_at', '>=', now()->startOfDay())->count();
        } catch (\Throwable $e) {}
        if ($bugun >= max(10, (int) self::ayar('gunluk_tavan', 300))) {
            $sus('gunluk_tavan');
            if (Cache::add("wa_asistan_tavan_{$o->salon_id}_" . date('Ymd'), 1, 1440)) {
                self::isletmeyeBildir((int) $o->isletme_id, $o, 'WhatsApp asistanı bugünkü cevap sınırına ulaştı; bugün gelen yeni mesajlara cevap vermeyecek.');
            }
            return;
        }

        // 7) PAKET BITTI (Sistem Yonetimi > WhatsApp Asistan Paketleri; asimda durdur): ay sonuna kadar cevap yok
        if (WaPaket::kapali((int) $o->salon_id)) {
            $sus('paket_bitti');
            return;
        }

        $salon = Salonlar::find($o->salon_id);
        if (!$salon) return;

        // ---- cevap
        $selam = null; $d = null; $cevap = '';
        $kaynak = 'kod';           // wa_asistan_cevaplar.kaynak (rapor); 'ai' = Claude cagrildi (paketten duser)
        $ekUsd = 0.0;              // Claude disi AI maliyeti (gorsel analiz)
        $baglamEk = '';            // gecmise gizli not (gorsel analiz)
        $hatirlatma = null;
        if (empty($gecmis) && $tel && ($hatirlatma = self::sonHatirlatma($oturumSalonlari, $tel))) {
            // WhatsApp hatirlatmasina cevap: hatirlatmanin salonu asistanin salonu olur (paylasilan hat)
            $upd['isletme_id'] = $hatirlatma['salon_id'];
            $o->isletme_id = $hatirlatma['salon_id'];
        }
        $ayar = WaAsistanVeri::ayarlar((int) $o->isletme_id);
        // Numara gizli (LID) ama bu kisi daha once numarasini verdiyse: onceki sohbetten (her seferinde tekrar sorulmasin)
        $musteriTel = $o->musteri_tel ?: $tel;
        if (!$musteriTel) {
            $musteriTel = DB::table('wa_asistan_oturumlari')->where('salon_id', $o->salon_id)->where('anahtar', $o->anahtar)
                ->whereNotNull('musteri_tel')->orderByDesc('id')->value('musteri_tel');
            if ($musteriTel) $upd['musteri_tel'] = $musteriTel;
        }
        $as = new RandevuAsistan((int) $o->isletme_id, $musteriTel, 'whatsapp');
        $as->waFiyatSoyle = !empty($ayar['fiyat_soyle']);
        $as->waProfilAdiAyarla($son->ad ?: $o->ad);
        try { $as->waDersleriAyarla(WaDenetci::aktifDersler((int) $o->isletme_id)); } catch (\Throwable $e) {} // Gece Denetcisi dersleri
        $musteriMesajVar = (bool) array_filter($gecmis, function ($m) { return ($m['role'] ?? '') === 'user'; });
        $gorselId = (int) ($satirlar->pluck('gorsel_id')->filter()->last() ?: 0);
        $gkCevap = (!empty($o->geri_kazanim_id) && !$musteriMesajVar) ? WaGeriKazanim::cevapGeldi((int) $o->geri_kazanim_id, $metin) : null;
        $on = null;

        if ($gkCevap !== null) {
            $cevap = $gkCevap; $kaynak = 'geri_kazanim';
        } elseif ($gorselId) {
            // MODEL FOTOGRAFI -> ozellik analizi + isletme kurallariyla fiyat (AI fiyat uydurmaz)
            $yazi = trim(preg_replace('/\[fotoğraf\]\s*/u', '', $metin));
            $g = WaGorselDanisman::isle($gorselId, (int) $o->isletme_id, (int) $o->id, $as->hizmetler(), $yazi);
            $ekUsd = (float) $g['maliyet_usd'];
            $kaynak = $g['ai'] ? 'gorsel' : 'gorsel_onbellek';
            $upd['gorsel_sayisi'] = DB::raw('gorsel_sayisi + 1');
            if (!empty($g['hizmet']['id'])) { $upd['son_hizmet_id'] = (int) $g['hizmet']['id']; $upd['son_hizmet_ad'] = $g['hizmet']['ad']; }
            if ($g['durum'] === 'fiyat') $upd['fiyat_verildi'] = 1;
            $baglamEk = (string) $g['baglam'];
            if ($g['aksiyon'] === 'aktar') {
                $d = ['cevap' => 'model fiyat', 'aksiyon' => 'aktar', 'veri' => [], 'bitir' => false]; // asagidaki aktarma akisi
                $metin = trim($metin . ' ' . ($g['tarif'] ?? ''));
            } else {
                $cevap = $g['cevap'];
                if (empty($gecmis) && $as->taninanMusteri()) $cevap = $as->waSelam() . ' ' . $cevap;
            }
        } elseif ($hatirlatma && ($meta = $as->waHatirlatmaMeta($hatirlatma['randevu_id']))) {
            $gecmis[] = ['role' => 'assistant', 'content' => $hatirlatma['mesaj'], 'hatirlatma' => $meta];
            $d = $as->konus($metin, $gecmis);
        } elseif (empty($gecmis) && self::selamMi($metin)) {
            // Yalniz selam: karsilama. Ilk mesajda istek varsa (kayitsiz da olsa) karsilama YOK, istege dogrudan cevap
            // (kayit randevu onayinda; RandevuAsistan WA kayit kurali)
            $cevap = $as->waKarsilama();
            $kaynak = 'karsilama';
        } elseif ($on = WaBedavaKatman::onCevap((int) $o->isletme_id, $as, $metin, $gecmis, $o, $ayar)) {
            // BEDAVA KATMAN: fiyat / SSS / hizmet bilgisi / ogrenilen / tesekkur (Claude'a gitmez, paketten dusmez)
            $cevap = (string) $on['cevap'];
            $kaynak = (string) $on['kaynak'];
            if ($cevap !== '' && empty($gecmis)) $cevap = $as->waSelam() . ' ' . $cevap;
            if (!empty($on['hizmet']['id'])) { $upd['son_hizmet_id'] = (int) $on['hizmet']['id']; $upd['son_hizmet_ad'] = $on['hizmet']['ad']; }
            if (!empty($on['isaret']['fiyat'])) $upd['fiyat_verildi'] = 1;
            if (!empty($on['isaret']['bilgi'])) $upd['bilgi_verildi'] = 1;
            if (isset($on['memnuniyet'])) $upd['memnuniyet'] = (int) $on['memnuniyet'];
        } elseif (empty($gecmis)) {
            $selam = $as->waSelam();
            $d = $as->konus($metin, []);
        } else {
            $d = $as->konus($metin, $gecmis);
        }

        $aksiyon = null;
        if ($d) {
            $cevap = trim((string) ($d['cevap'] ?? ''));
            $aksiyon = $d['aksiyon'] ?? null;
            if ($aksiyon === 'aktar') {
                $fiyat = mb_stripos($cevap, 'fiyat') !== false;
                $cevap = ($fiyat ? 'Fiyat bilgisi için talebinizi' : 'Talebinizi')
                    . ' işletmemize ilettim; yetkili arkadaşımız en kısa sürede buradan size dönüş yapacak. 🙏';
                $upd['sessiz_bitis'] = now()->addMinutes(max(5, (int) self::ayar('aktar_sessiz_dk', 120)));
                $upd['sessiz_neden'] = 'aktar';
                self::isletmeyeBildir((int) $o->isletme_id, $o, 'WhatsApp asistanı bir müşteriyi size yönlendirdi ('
                    . ($o->ad ?: $son->ad ?: 'müşteri') . ', ' . ($o->musteri_tel ?: $tel ?: 'gizli numara') . '). Mesajı: "'
                    . mb_substr($metin, 0, 200) . '". Lütfen WhatsApp\'tan dönüş yapın.');
            }
            $cevap = self::telefonIfadeleri($cevap);
            if ($selam) $cevap = $selam . ' ' . $cevap;
            if (!empty($d['bitir'])) $upd['durum'] = 'kapandi';
            if (in_array($aksiyon, ['randevu', 'iptal', 'guncelle', 'aktar', 'teyit', 'odeme'], true)) $upd['sonuc'] = $aksiyon;
            if (!empty($d['veri']['randevu_id'])) $upd['randevu_id'] = (int) $d['veri']['randevu_id'];
            if (($as->kullanim['cagri'] ?? 0) > 0) $kaynak = 'ai';
            elseif (!$gorselId) $kaynak = 'kod';
            self::isaretler($upd, $metin, $d, $as, $aksiyon);
            // OGRENEN ONBELLEK: baglamsiz bilgi sorusuna AI'nin tek cagrili, aracsiz cevabi -> bu isletmede sonra bedava
            if ($kaynak === 'ai' && WaBedavaKatman::cevapOgrenilebilir($metin, $d, $as->kullanim, $as->taninanMusteri())) {
                WaBedavaKatman::ogren((int) $o->isletme_id, $metin, $cevap);
            }
        }
        if ($as->yeniTelefon) $upd['musteri_tel'] = $as->yeniTelefon;
        // Musteri acikca vazgecti: bu sohbet icin geri kazanma mesaji gitmesin
        if (preg_match('/\b(istemiyorum|vazgeçtim|vazgectim|ilgilenmiyorum|gerek yok)\b/iu', $metin)) $upd['ret'] = 1;
        // Randevudan sonra tek seferlik memnuniyet sorusu (cevap bedava katmanda kaydedilir)
        if ($aksiyon === 'randevu' && !empty($ayar['memnuniyet_sor']) && empty($o->memnuniyet_soruldu) && $cevap !== '') {
            $cevap .= "\n\nYazışmamızdan memnun kaldıysanız 👍, kalmadıysanız 👎 gönderebilirsiniz.";
            $upd['memnuniyet_soruldu'] = 1;
        }

        // Maliyet (Claude token) -> oturum + AI kullanim kaydi
        $k = $as->kullanim;
        $usd = 0.0;
        if (($k['cagri'] ?? 0) > 0) {
            $usd = SantralPaket::claudeMaliyetUsd($k);
            $upd['girdi_token'] = DB::raw('girdi_token + ' . (int) $k['girdi']);
            $upd['cikti_token'] = DB::raw('cikti_token + ' . (int) $k['cikti']);
            $upd['cache_okuma_token'] = DB::raw('cache_okuma_token + ' . (int) $k['cache_okuma']);
            $upd['cache_yazma_token'] = DB::raw('cache_yazma_token + ' . (int) $k['cache_yazma']);
            AiKullanimLog::yaz((int) $o->isletme_id, 'wa_asistan', (int) $k['girdi'], (int) $k['cikti'],
                (string) (config('services.anthropic.santral_model') ?: 'claude-haiku-5-5'), false, true, $usd);
        }
        if ($usd + $ekUsd > 0) $upd['maliyet_usd'] = DB::raw('maliyet_usd + ' . sprintf('%.6F', $usd + $ekUsd));

        $gecmis[] = ['role' => 'user', 'content' => $metin];
        $gonderildi = false;
        if ($cevap !== '' && $hedef) {
            $r = app(WhatsAppService::class)->sendAsistanCevap($salon, $hedef, $cevap, $tel);
            $gonderildi = !empty($r['ok']);
            if (!$gonderildi) Log::warning('[WA-ASISTAN] cevap gonderilemedi', ['oturum' => $o->id, 'hata' => $r['error'] ?? null]);
        }
        // Musteri gormediyse cevap gecmise yazilmaz (islem yapildiysa yazilir: model bilsin)
        if ($gonderildi || in_array($aksiyon, ['randevu', 'iptal', 'guncelle', 'teyit', 'odeme'], true)) {
            $asistanMsj = ['role' => 'assistant', 'content' => $cevap];
            $b = trim(($d && !empty($d['baglam']) ? $d['baglam'] : '') . ($baglamEk !== '' ? ' ' . $baglamEk : ''));
            if ($b !== '') $asistanMsj['baglam'] = $b;
            $gecmis[] = $asistanMsj;
        }
        if ($gonderildi) {
            $zamanlar[] = time();
            $upd['cevap_zamanlari'] = json_encode(array_slice($zamanlar, -50));
            $upd['cevap_sayisi'] = DB::raw('cevap_sayisi + 1');
        }
        $kaydet($gecmis);
        // OLCUM: gonderilen her cevap (kaynak, AI mi, cevap suresi, mesai disi, randevu) -> "Kazandirdiklari" + paket sayaci
        $yapayZeka = $kaynak === 'ai' || $kaynak === 'gorsel';
        if ($gonderildi || ($yapayZeka && in_array($aksiyon, ['randevu', 'iptal', 'guncelle', 'teyit', 'odeme'], true))) {
            $ilkAn = strtotime((string) ($satirlar->first()->created_at ?? '')) ?: time();
            WaAsistanVeri::cevapKaydet([
                'salon_id' => (int) $o->salon_id, 'isletme_id' => (int) $o->isletme_id, 'oturum_id' => (int) $o->id,
                'kaynak' => $kaynak, 'ai' => $yapayZeka, 'sure_sn' => time() - $ilkAn, 'aksiyon' => $aksiyon,
                'randevu_id' => !empty($d['veri']['randevu_id']) ? (int) $d['veri']['randevu_id'] : null, 'maliyet_usd' => $usd + $ekUsd,
            ]);
            if ($yapayZeka) WaPaket::esikKontrol((int) $o->salon_id);
        }
        Log::info('[WA-ASISTAN] tur', ['oturum' => $o->id, 'salon' => $o->isletme_id, 'metin' => $metin, 'cevap' => $cevap,
            'aksiyon' => $aksiyon, 'kaynak' => $kaynak, 'teshis' => $as->teshis, 'gonderildi' => $gonderildi]);
    }

    /**
     * AI turundan geri kazanma / rapor isaretleri: fiyat verildi, musait saat onerildi, bilgi verildi, son konusulan hizmet.
     * Hizmet: arac baglamindaki hizmet_id ya da mesajda gecen hizmet adi.
     */
    protected static function isaretler(array &$upd, string $metin, array $d, RandevuAsistan $as, ?string $aksiyon): void
    {
        $baglam = is_array($d['baglam'] ?? null) ? implode(' ', $d['baglam']) : (string) ($d['baglam'] ?? '');
        if ($aksiyon !== 'aktar' && $as->waFiyatSoyle && WaBedavaKatman::fiyatSorusuMu($metin) && preg_match('/\d[\d.,]*\s*(TL|₺)/u', (string) ($d['cevap'] ?? ''))) {
            $upd['fiyat_verildi'] = 1;
        }
        if (strpos($baglam, 'musait_saatler') !== false) $upd['saat_onerildi'] = 1;
        if (!$aksiyon && WaBedavaKatman::bilgiSorusuMu($metin)) $upd['bilgi_verildi'] = 1;
        $hizmetler = $as->hizmetler();
        $hid = preg_match('/hizmet_id=(\d+)/', $baglam, $m) ? (int) $m[1] : null;
        if (!$hid && ($b = WaBedavaKatman::hizmetBul($metin, $hizmetler))) $hid = (int) $b[0]['id'];
        if ($hid) {
            foreach ($hizmetler as $h) {
                if ((int) $h['id'] === $hid) { $upd['son_hizmet_id'] = $hid; $upd['son_hizmet_ad'] = mb_substr((string) $h['ad'], 0, 150); break; }
            }
        }
    }

    // ------------------------------------------------------------------ yardimcilar
    /** Son 48 saatte bu numaraya WhatsApp'tan giden randevu hatirlatmasi (cevap ona verilmis olabilir). */
    protected static function sonHatirlatma(array $salonlar, string $tel): ?array
    {
        try {
            $l = DB::table('whatsapp_gonderim_loglari')->whereIn('salon_id', $salonlar)->where('telefon', $tel)
                ->whereIn('gonderim_tipi', ['randevu_hatirlatma_1gun', 'randevu_hatirlatma'])
                ->whereNotNull('randevu_id')->where('created_at', '>=', now()->subHours(48))
                ->orderByDesc('id')->first();
        } catch (\Throwable $e) {
            return null;
        }
        return $l ? ['salon_id' => (int) $l->salon_id, 'randevu_id' => (int) $l->randevu_id, 'mesaj' => (string) $l->mesaj] : null;
    }

    protected static function sade(string $s): string
    {
        $s = mb_strtolower(strtr($s, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', preg_replace('/[^\p{L}\p{N}\s]/u', '', $s)));
    }

    /** Yalniz selamlasma mi ("merhaba", "iyi günler", "selamlar") -> karsilama. */
    protected static function selamMi(string $m): bool
    {
        $s = self::sade($m);
        if ($s === '' || count(explode(' ', $s)) > 4) return false;
        return (bool) preg_match('/^(merhaba(lar)?|mrb|mrh|selam(lar)?|slm|sa|selamün aleyküm|selamun aleykum|iyi (günler|akşamlar|geceler)|günaydın|hayırlı (günler|akşamlar)|hello|hi|hey)( (iyi günler|kolay gelsin|nasılsınız))?$/u', $s);
    }

    /** Karsi taraf otomatik yanit/bot mu? (iki bot birbirine cevap vermesin) */
    protected static function otomatikYanitMi(string $m): bool
    {
        $s = mb_strtolower($m, 'UTF-8');
        return (bool) preg_match('/otomatik (yanıt|cevap|mesaj)|auto.?reply|automatic reply|out of office|mesajınız (bize )?(ulaştı|alınmıştır|alındı)|mesajınızı aldık|en kısa sürede (size )?(dönüş|geri dönüş|geri dön)|mesai saatleri(miz)? dışında|şu an(da)? (müsait değil|cevap veremiyor|ulaşılamıyor)|bu numara(ya)? (whatsapp )?(mesaj|hizmet) (vermemektedir|kabul etmemektedir|kullanılmamaktadır)/u', $s);
    }

    /** RandevuAsistan'in telefon kalibi kalmissa yaziya cevir. */
    protected static function telefonIfadeleri(string $c): string
    {
        $c = str_replace(['bizi aradığınız için', 'Bizi aradığınız için'], ['bize yazdığınız için', 'Bize yazdığınız için'], $c);
        $c = preg_replace('/,?\s*lütfen hatta kalın\.?/u', '.', $c);
        $c = preg_replace('/\.{2,}/u', '.', $c);
        return trim($c);
    }

    /** Panel bildirimi + yoneticilere push (WhatsAppWebhookController ban uyarisi ile ayni kanal). */
    protected static function isletmeyeBildir(int $salonId, $o, string $mesaj): void
    {
        self::bildir($salonId, $mesaj);
    }

    /** Salon yoneticilerine panel bildirimi + push (Gece Denetcisi haftalik ozeti de kullanir). */
    public static function bildir(int $salonId, string $mesaj, ?string $url = null): void
    {
        try {
            $b = new \App\Bildirimler();
            $b->aciklama = $mesaj;
            $b->salon_id = $salonId;
            $b->url = $url ?: '/isletmeyonetim/whatsapp?sube=' . $salonId;
            $b->tarih_saat = date('Y-m-d H:i:s');
            $b->okundu = false;
            $b->save();
        } catch (\Throwable $e) {
            Log::warning('[WA-ASISTAN] panel bildirimi yazilamadi: ' . $e->getMessage());
        }
        try {
            $yoneticiler = \App\Personeller::join('model_has_roles', 'salon_personelleri.yetkili_id', '=', 'model_has_roles.model_id')
                ->where('salon_personelleri.salon_id', $salonId)->where('model_has_roles.role_id', '<', 5)
                ->pluck('salon_personelleri.id')->toArray();
            foreach ($yoneticiler as $pid) {
                NotificationService::toStaff((int) $pid, $salonId)->type(NotificationTypes::SYSTEM_ANNOUNCEMENT)
                    ->title('WhatsApp Asistanı')->body($mesaj)->send();
            }
        } catch (\Throwable $e) {
            Log::warning('[WA-ASISTAN] push gonderilemedi: ' . $e->getMessage());
        }
    }
}
