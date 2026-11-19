<?php

namespace App\Services;

use App\Salonlar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Kacan musteriyi geri kazanma (WhatsApp asistani).
 *
 * Kacan musteri: asistandan fiyat / bilgi / musait saat / fotograf fiyati aldi, randevu OLUSTURMADI ve isletmenin
 * ayarindaki sure (vars. 24 saat) boyunca yazmadi. Saatlik komut (wa:geri-kazanim) bu musterilere bir kez (30 gunde
 * en fazla 1) neden-uygun, KODLA kurulan bir mesaj yollar (AI maliyeti yok, paketten dusmez):
 *   "Merhaba Ayşe Hanım 😊 Dün Protez Tırnak için fiyat almıştınız. Perşembe 20 Kasım 14:00 ve ... yerimiz var..."
 * Gercek bos saatler RandevuAsistan'dan (uydurma yok); kampanya yalniz isletmenin yazdigi cumle (asistan indirim uydurmaz).
 * Musteri cevap verirse yeni oturum (gecmiste bu mesaj + baglam) normal asistanla randevuya baglanir.
 * Takip: mesajdan sonraki 7 gunde alinan randevu "geri kazanildi"; randevu tarihi gecip "geldi" isaretlenince tutar
 * (randevu hizmet fiyatlari) toplanir -> panel "Geri kazanılan müşteriler" karti.
 * "dur / istemiyorum" -> bir daha yazilmaz (ret).
 */
class WaGeriKazanim
{
    const TABLO = 'wa_geri_kazanim';
    const SEMA = 'wa_geri_kazanim_sema_v1';
    const GUNLUK_TAVAN = 20;          // salon basina gunde en fazla geri kazanma mesaji
    const TEKRAR_GUN = 30;            // ayni musteriye en erken bu kadar gun sonra tekrar
    const ATIF_GUN = 7;               // mesajdan sonra bu surede alinan randevu geri kazanilmis sayilir
    const PENCERE_SAAT = 48;          // bekleme suresinden sonra bu kadar saat icinde yazilir (cok eski sohbete yazma)

    public static function semaGaranti(): bool
    {
        if (Cache::get(self::SEMA)) return true;
        if (!WaAsistanVeri::semaGaranti()) return false;
        try {
            if (!Schema::hasTable(self::TABLO)) {
                Schema::create(self::TABLO, function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');                 // hat salonu
                    $t->unsignedBigInteger('isletme_id');
                    $t->unsignedBigInteger('oturum_id');                // kacan sohbet
                    $t->unsignedBigInteger('yeni_oturum_id')->nullable(); // geri kazanma sohbeti
                    $t->string('anahtar', 100);
                    $t->string('jid', 100)->nullable();
                    $t->string('telefon', 30)->nullable();
                    $t->string('ad', 100)->nullable();
                    $t->string('neden', 20)->nullable();                // fiyat | bilgi | saat | gorsel | pahali | dusunme
                    $t->unsignedBigInteger('hizmet_id')->nullable();
                    $t->string('hizmet_ad', 150)->nullable();
                    $t->text('mesaj')->nullable();
                    $t->string('durum', 20)->default('gonderildi');     // gonderildi | cevap | randevu | geldi | gelmedi | ret | gonderilemedi
                    $t->timestamp('gonderildi_at')->nullable();
                    $t->timestamp('cevap_at')->nullable();
                    $t->unsignedBigInteger('randevu_id')->nullable();
                    $t->date('randevu_tarih')->nullable();
                    $t->tinyInteger('geldi')->nullable();
                    $t->decimal('tutar', 12, 2)->nullable();
                    $t->timestamps();
                    $t->index(['salon_id', 'anahtar'], 'wa_gk_salon_anahtar_idx');
                    $t->index(['isletme_id', 'gonderildi_at'], 'wa_gk_isletme_tarih_idx');
                });
            }
            Cache::forever(self::SEMA, 1);
            return true;
        } catch (\Throwable $e) {
            Log::error('[WA-GERI-KAZANIM] sema: ' . $e->getMessage());
            return false;
        }
    }

    // ================================================================== saf
    /** Oturum geri kazanma adayi mi? $o: wa_asistan_oturumlari satiri (nesne), $now unix. */
    public static function adayMi($o, int $bekleSaat, int $now): bool
    {
        if (!$o || !empty($o->randevu_id) || !empty($o->ret) || !empty($o->geri_kazanim_id) || !empty($o->insan_devraldi)) return false;
        if (in_array((string) $o->sonuc, ['randevu', 'guncelle', 'iptal', 'teyit', 'odeme', 'aktar'], true)) return false;
        if (empty($o->fiyat_verildi) && empty($o->saat_onerildi) && empty($o->gorsel_sayisi) && empty($o->bilgi_verildi)) return false;
        if (empty($o->jid) && empty($o->telefon)) return false;
        $son = $o->son_mesaj_at ? strtotime($o->son_mesaj_at) : 0;
        if (!$son) return false;
        return $son <= $now - $bekleSaat * 3600 && $son >= $now - ($bekleSaat + self::PENCERE_SAAT) * 3600;
    }

    /** Neden: son musteri mesajlarindan (pahali / dusunme) yoksa oturum isaretinden. */
    public static function neden($o, array $gecmis): string
    {
        $musteri = '';
        foreach (array_slice($gecmis, -6) as $m) if (($m['role'] ?? '') === 'user') $musteri .= ' ' . WaBedavaKatman::sade((string) ($m['content'] ?? ''));
        if (preg_match('/ (pahali\w*|cok fazla|cok yuksek|indirim\w*|biraz yuksek|butcem\w*|uygun degil|daha uygun|ucuz\w*)/u', $musteri . ' ')) return 'pahali';
        if (preg_match('/ (dusun\w*|haber ver\w*|sonra yaz\w*|sonra bak\w*|bakarim|bakacagim|kararsiz\w*|esime sor\w*|ayarlayip|netlestir\w*)/u', $musteri . ' ')) return 'dusunme';
        if (!empty($o->gorsel_sayisi)) return 'gorsel';
        if (!empty($o->saat_onerildi)) return 'saat';
        if (!empty($o->fiyat_verildi)) return 'fiyat';
        return 'bilgi';
    }

    /** "dur / istemiyorum / yazmayin" -> bir daha geri kazanma mesaji yollanmaz. */
    public static function retMi(string $m): bool
    {
        $s = ' ' . WaBedavaKatman::sade($m) . ' ';
        return (bool) preg_match('/^ ?(dur|stop|hayir|yok)( tesekkur\w*)? ?$| (istemiyorum|ilgilenmiyorum|yazmayin|yazmayiniz|rahatsiz etme\w*|mesaj atmayin|vazgectim|gerek yok|listeden cikar\w*|engelle\w*) /u', $s);
    }

    /**
     * Mesaj metni (saf). $selam: "Merhaba Ayşe Hanım." ; $saatOnce: kacan sohbetin kac saat once oldugu.
     * $secenekler: RandevuAsistan::geriKazanimSecenekleri sonucu ([gun, saat, personel]).
     */
    public static function mesajMetni(string $selam, ?string $hizmetAd, string $neden, array $secenekler, ?string $kampanya, int $saatOnce): string
    {
        $selam = rtrim(trim($selam), '.!') ?: 'Merhaba';
        $zaman = $saatOnce <= 36 ? 'Dün' : 'Geçen gün';
        $konu = $hizmetAd ? " {$hizmetAd} için" : '';
        switch ($neden) {
            case 'gorsel': $giris = "{$zaman} gönderdiğiniz model için fiyat almıştınız."; break;
            case 'saat':   $giris = "{$zaman}{$konu} uygun saatlerimize bakmıştınız."; break;
            case 'fiyat':
            case 'pahali': $giris = "{$zaman}{$konu} fiyat bilgisi almıştınız."; break;
            case 'dusunme': $giris = "{$zaman}{$konu} bilgi almış, düşünüp haber vereceğinizi söylemiştiniz."; break;
            default:       $giris = "{$zaman}{$konu} bilgi almıştınız.";
        }
        $out = "{$selam} 😊 {$giris}";
        $kampanya = trim((string) $kampanya);
        if ($kampanya !== '' && in_array($neden, ['pahali', 'fiyat', 'gorsel', 'dusunme'], true)) $out .= "\n\n" . $kampanya;
        if ($secenekler) {
            $s = array_map(function ($x) {
                return trim(($x['gun'] ?? $x['tarih'] ?? '') . ' ' . ($x['saat'] ?? '')) . (!empty($x['personel']) ? ' (' . $x['personel'] . ')' : '');
            }, array_slice($secenekler, 0, 2));
            $out .= "\n\nYakın zamanda şu saatlerde yerimiz var:\n" . implode("\n", array_map(function ($x) { return '• ' . $x; }, $s))
                . "\n\nSize uygunsa hemen ayırabilirim; başka bir gün de söyleyebilirsiniz.";
        } else {
            $out .= "\n\nSize uygun bir gün ve saat yazarsanız randevunuzu hemen oluşturabilirim.";
        }
        return $out . "\n\nBu konuda tekrar mesaj almak istemezseniz \"dur\" yazmanız yeterli.";
    }

    /** Asistanin gecmisine gizli baglam (model musait saat id'lerini bilsin). */
    public static function baglamNotu(?int $hizmetId, array $secenekler): string
    {
        $b = 'geri kazanma mesaji (müşteri daha önce bilgi almış, randevu almamıştı)';
        if ($hizmetId) $b .= "; hizmet_id={$hizmetId}";
        if ($secenekler) {
            $b .= '; önerilen seçenekler: ' . implode('; ', array_map(function ($x) {
                return ($x['tarih'] ?? '') . 'T' . ($x['saat'] ?? '') . ' personel_id=' . ($x['personel_id'] ?? '') . ' (' . ($x['personel'] ?? '') . ')';
            }, $secenekler));
        }
        return $b;
    }

    // ================================================================== tarama + gonderim (komut)
    /** @return array ['aday'=>n,'gonderildi'=>n,'atlandi'=>[neden=>n]] */
    public static function tara(bool $kuru = false): array
    {
        $rapor = ['aday' => 0, 'gonderildi' => 0, 'atlandi' => []];
        if (!self::semaGaranti()) return $rapor;
        $atla = function ($n) use (&$rapor) { $rapor['atlandi'][$n] = ($rapor['atlandi'][$n] ?? 0) + 1; };
        $now = time();
        // En uzun bekleme 168 saat: aday penceresi 168+48 saati kapsar
        $rows = DB::table('wa_asistan_oturumlari')
            ->whereNull('randevu_id')->whereNull('geri_kazanim_id')->where('ret', 0)
            ->where('son_mesaj_at', '>=', date('Y-m-d H:i:s', $now - (168 + self::PENCERE_SAAT) * 3600))
            ->where('son_mesaj_at', '<=', date('Y-m-d H:i:s', $now - 2 * 3600))
            ->where(function ($w) { $w->where('fiyat_verildi', 1)->orWhere('saat_onerildi', 1)->orWhere('gorsel_sayisi', '>', 0)->orWhere('bilgi_verildi', 1); })
            ->orderBy('id')->limit(500)->get();
        foreach ($rows as $o) {
            if (!WaAsistan::acikMi((int) $o->salon_id)) { $atla('asistan_kapali'); continue; }
            $ayar = WaAsistanVeri::ayarlar((int) $o->isletme_id);
            if (empty($ayar['geri_kazanim'])) { $atla('ayar_kapali'); continue; }
            if (!self::adayMi($o, (int) $ayar['gk_bekle_saat'], $now)) { $atla('aday_degil'); continue; }
            $rapor['aday']++;
            $r = self::gonder($o, $ayar, $now, $kuru);
            if ($r === true) $rapor['gonderildi']++; else $atla($r);
        }
        return $rapor;
    }

    /** @return true|string gonderildi ya da atlama nedeni */
    protected static function gonder($o, array $ayar, int $now, bool $kuru)
    {
        $isletme = (int) $o->isletme_id;
        $saat = (int) date('G', $now);
        if ($saat < 10 || $saat >= 20 || WaAsistanVeri::mesaiDisiMi($isletme, $now)) return 'mesai_disi';
        if (WaPaket::kapali((int) $o->salon_id)) return 'paket_bitti';
        $onceki = DB::table(self::TABLO)->where('salon_id', $o->salon_id)->where('anahtar', $o->anahtar)
            ->where(function ($w) { $w->where('durum', 'ret')->orWhere('created_at', '>=', now()->subDays(self::TEKRAR_GUN)); })->exists();
        if ($onceki) return 'yakin_zamanda_yazildi';
        // Bu arada musteri yeniden yazdiysa (yeni sohbet) ya da randevusu varsa yazma
        $sonra = DB::table('wa_asistan_oturumlari')->where('salon_id', $o->salon_id)->where('anahtar', $o->anahtar)
            ->where('id', '>', $o->id)->exists();
        if ($sonra) return 'musteri_tekrar_yazdi';
        $tel = $o->musteri_tel ?: $o->telefon;
        if ($tel && self::gelecekRandevuVar($isletme, $tel)) return 'randevusu_var';
        $bugun = DB::table(self::TABLO)->where('salon_id', $o->salon_id)->where('created_at', '>=', date('Y-m-d 00:00:00', $now))->count();
        if ($bugun >= self::GUNLUK_TAVAN) return 'gunluk_tavan';
        $salon = Salonlar::find($o->salon_id);
        if (!$salon) return 'salon_yok';

        $gecmis = json_decode($o->gecmis ?: '[]', true) ?: [];
        $neden = self::neden($o, $gecmis);
        $as = new RandevuAsistan($isletme, $tel, 'whatsapp');
        $hizmetId = $o->son_hizmet_id ? (int) $o->son_hizmet_id : null;
        $secenek = $hizmetId ? $as->geriKazanimSecenekleri($hizmetId) : [];
        $saatOnce = (int) floor(($now - strtotime($o->son_mesaj_at)) / 3600);
        $mesaj = self::mesajMetni($as->waSelam(), $o->son_hizmet_ad ?: null, $neden, $secenek, $ayar['gk_kampanya'] ?? null, $saatOnce);
        if ($kuru) {
            Log::info('[WA-GERI-KAZANIM] (kuru) gonderilecekti', ['oturum' => $o->id, 'mesaj' => $mesaj]);
            return true;
        }

        $gkId = DB::table(self::TABLO)->insertGetId([
            'salon_id' => $o->salon_id, 'isletme_id' => $isletme, 'oturum_id' => $o->id, 'anahtar' => $o->anahtar,
            'jid' => $o->jid, 'telefon' => $tel, 'ad' => $o->ad, 'neden' => $neden, 'hizmet_id' => $hizmetId,
            'hizmet_ad' => $o->son_hizmet_ad, 'mesaj' => $mesaj, 'durum' => 'gonderildi', 'gonderildi_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $hedef = $o->jid ?: $o->telefon;
        $r = app(WhatsAppService::class)->sendAsistanCevap($salon, $hedef, $mesaj, $o->telefon);
        if (empty($r['ok'])) {
            DB::table(self::TABLO)->where('id', $gkId)->update(['durum' => 'gonderilemedi', 'updated_at' => now()]);
            Log::warning('[WA-GERI-KAZANIM] gonderilemedi', ['oturum' => $o->id, 'hata' => $r['error'] ?? null]);
            return 'gonderilemedi';
        }
        // Eski sohbet kapanir; cevap gelirse devam edecek yeni sohbet (gecmiste bu mesaj + baglam)
        DB::table('wa_asistan_oturumlari')->where('id', $o->id)->update(['durum' => 'kapandi', 'updated_at' => now()]);
        $yeni = DB::table('wa_asistan_oturumlari')->insertGetId([
            'salon_id' => $o->salon_id, 'isletme_id' => $isletme, 'anahtar' => $o->anahtar, 'jid' => $o->jid,
            'telefon' => $o->telefon, 'musteri_tel' => $o->musteri_tel, 'ad' => $o->ad,
            'gecmis' => json_encode([['role' => 'assistant', 'content' => $mesaj, 'geri_kazanim' => 1, 'at' => date('Y-m-d H:i:s'), 'baglam' => self::baglamNotu($hizmetId, $secenek)]], JSON_UNESCAPED_UNICODE),
            'durum' => 'acik', 'son_mesaj_at' => now(), 'geri_kazanim_id' => $gkId,
            'son_hizmet_id' => $hizmetId, 'son_hizmet_ad' => $o->son_hizmet_ad,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table(self::TABLO)->where('id', $gkId)->update(['yeni_oturum_id' => $yeni]);
        WaAsistanVeri::cevapKaydet(['salon_id' => $o->salon_id, 'isletme_id' => $isletme, 'oturum_id' => $yeni, 'kaynak' => 'geri_kazanim', 'ai' => 0]);
        Log::info('[WA-GERI-KAZANIM] gonderildi', ['oturum' => $o->id, 'gk' => $gkId, 'neden' => $neden]);
        return true;
    }

    protected static function gelecekRandevuVar(int $salonId, string $tel): bool
    {
        $t10 = substr(preg_replace('/\D/', '', $tel), -10);
        if (strlen($t10) !== 10) return false;
        return DB::table('randevular as r')->join('users as u', 'u.id', '=', 'r.user_id')
            ->where('r.salon_id', $salonId)->where('u.cep_telefon', $t10)
            ->whereIn('r.durum', [0, 1])->where('r.tarih', '>=', date('Y-m-d'))->exists();
    }

    // ================================================================== musteri cevabi
    /**
     * Geri kazanma sohbetinde musterinin ilk cevabi. "dur" ise ret + kisa cevap doner (AI'ya gitmez); degilse null
     * (normal asistan devam eder) ve kayit "cevap" olur.
     */
    public static function cevapGeldi(int $gkId, string $metin): ?string
    {
        try {
            if (!self::semaGaranti()) return null;
            $gk = DB::table(self::TABLO)->where('id', $gkId)->first();
            if (!$gk) return null;
            if (self::retMi($metin)) {
                DB::table(self::TABLO)->where('id', $gkId)->update(['durum' => 'ret', 'cevap_at' => $gk->cevap_at ?: now(), 'updated_at' => now()]);
                DB::table('wa_asistan_oturumlari')->where('geri_kazanim_id', $gkId)->update(['ret' => 1]);
                return 'Anlaşıldı, bu konuda size tekrar yazmayacağız. İhtiyacınız olduğunda buradan yazabilirsiniz, iyi günler dileriz 😊';
            }
            if (!$gk->cevap_at) {
                DB::table(self::TABLO)->where('id', $gkId)->update(['cevap_at' => now(), 'updated_at' => now(),
                    'durum' => $gk->durum === 'gonderildi' ? 'cevap' : $gk->durum]);
            }
        } catch (\Throwable $e) {
            Log::warning('[WA-GERI-KAZANIM] cevap kaydi: ' . $e->getMessage());
        }
        return null;
    }

    // ================================================================== takip (randevu + geldi + tutar)
    public static function takip(): int
    {
        if (!self::semaGaranti()) return 0;
        $n = 0;
        $kayitlar = DB::table(self::TABLO)->whereIn('durum', ['gonderildi', 'cevap', 'randevu'])
            ->where('gonderildi_at', '>=', now()->subDays(45))->limit(1000)->get();
        foreach ($kayitlar as $gk) {
            $upd = [];
            $rid = $gk->randevu_id;
            if (!$rid) {
                $t10 = substr(preg_replace('/\D/', '', (string) $gk->telefon), -10);
                if (strlen($t10) === 10) {
                    $r = DB::table('randevular as r')->join('users as u', 'u.id', '=', 'r.user_id')
                        ->where('r.salon_id', $gk->isletme_id)->where('u.cep_telefon', $t10)->whereIn('r.durum', [0, 1])
                        ->where('r.created_at', '>=', $gk->gonderildi_at)
                        ->where('r.created_at', '<=', date('Y-m-d H:i:s', strtotime($gk->gonderildi_at) + self::ATIF_GUN * 86400))
                        ->orderBy('r.id')->first(['r.id', 'r.tarih']);
                    if ($r) { $rid = $r->id; $upd += ['randevu_id' => $r->id, 'randevu_tarih' => $r->tarih, 'durum' => 'randevu']; }
                }
            }
            if ($rid) {
                $r = DB::table('randevular')->where('id', $rid)->first(['id', 'tarih', 'durum', 'randevuya_geldi']);
                // "Geldi" yalniz panelde isaretlenince; isaretlenmemis gecmis randevu "randevu" olarak kalir (gelmedi sayilmaz)
                if ($r && $r->randevuya_geldi !== null && (int) $r->randevuya_geldi === 1) {
                    $upd['geldi'] = 1; $upd['durum'] = 'geldi'; $upd['tutar'] = self::randevuTutari((int) $rid);
                } elseif ($r && ((int) $r->durum === 2 || ($r->randevuya_geldi !== null && (int) $r->randevuya_geldi === 0))) {
                    $upd['geldi'] = 0; $upd['durum'] = 'gelmedi';
                } elseif ($r && !$gk->tutar) {
                    $upd['tutar'] = self::randevuTutari((int) $rid); // beklenen tutar (geldi isaretlenince kesinlesir)
                }
            }
            if ($upd) {
                DB::table(self::TABLO)->where('id', $gk->id)->update($upd + ['updated_at' => now()]);
                $n++;
            }
        }
        return $n;
    }

    public static function randevuTutari(int $randevuId): float
    {
        try { return (float) DB::table('randevu_hizmetler')->where('randevu_id', $randevuId)->sum('fiyat'); }
        catch (\Throwable $e) { return 0.0; }
    }
}
