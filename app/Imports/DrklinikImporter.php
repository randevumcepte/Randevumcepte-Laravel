<?php

namespace App\Imports;

use App\Services\DrklinikClient;
use App\Hizmetler;
use App\Hizmet_Kategorisi;
use App\SalonHizmetler;
use App\SalonHizmetKategoriRenkleri;
use App\Personeller;
use App\PersonelCalismaSaatleri;
use App\IsletmeYetkilileri;
use App\Urunler;
use App\Odalar;
use App\OdaRenkleri;
use App\User;
use App\MusteriPortfoy;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * uygulama.drklinik.net'ten cekilen verileri randevumcepte modellerine aktarir.
 * Site ASP.NET WebForms - veri HTML tablo olarak render ediliyor.
 */
class DrklinikImporter
{
    /** @var DrklinikClient */
    private $client;
    /** @var int */
    private $salonId;
    private $out;
    private $counts = ['hizmet' => 0, 'personel' => 0, 'urun' => 0, 'oda' => 0, 'musteri' => 0, 'randevu' => 0, 'tahsilat' => 0, 'skipped' => 0];
    private $odaMap = []; // drklinik oda id => local oda id
    private $musteriMap = []; // drklinik musteri id => local user id
    private $defaultKategoriId = null;

    public function __construct(DrklinikClient $client, $salonId, $out = null)
    {
        $this->client = $client;
        $this->salonId = (int) $salonId;
        $this->out = $out;
    }

    public function summary()
    {
        return $this->counts;
    }

    /**
     * hizmet_listesi.aspx tablosu: 2 sutun (Hizmet Adi, Fiyat).
     * Birimler DDL_Birim dropdown'unda; her birim icin postback ile filtreleyip
     * o birim hizmetlerini bizim Hizmet_Kategorisi olarak ekleriz.
     */
    public function importHizmetler()
    {
        $this->log('Hizmetler cekiliyor (hizmet_listesi.aspx)...');
        $initialHtml = $this->client->getHtml('/hizmet_listesi.aspx', 'hizmet_initial');
        if ($initialHtml === '') { $this->log('Sayfa cekilemedi.'); return; }

        // DDL_Birim option'larini cikar
        $birimler = $this->parseSelectOptions($initialHtml, 'DDL_Birim');
        if (empty($birimler)) {
            $this->log('Birim listesi bulunamadi - default kategori ile aktarilacak.');
            $this->importHizmetlerDefault($initialHtml);
            return;
        }

        $this->log('  ' . count($birimler) . ' birim bulundu.');
        $eklendi = 0;
        foreach ($birimler as $val => $ad) {
            if ($val === '0' || $val === '') continue; // "Birim Seciniz" placeholder
            $kategoriAd = trim(html_entity_decode($ad, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $kategoriAd = mb_convert_case(mb_strtolower($kategoriAd, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            $kategoriId = $this->kategoriEkleVeyaGetir($kategoriAd);

            // Birim secip postback yap
            $birimHtml = $this->client->postBack('/hizmet_listesi.aspx', 'DDL_Birim', '', [
                'DDL_Birim' => $val,
            ]);
            if ($birimHtml === null) { $this->log("  [{$kategoriAd}] postback basarisiz."); continue; }

            $rows = $this->parseTableRows($birimHtml);
            $birimEklendi = 0;
            foreach ($rows as $row) {
                if (count($row) < 2) continue;
                $hadi = trim($row[0]);
                if (!$hadi) continue;
                $fiyat = (float) preg_replace('/[^0-9.,]/', '', str_replace(',', '.', $row[1] ?? ''));
                if ($this->saveHizmet($hadi, $fiyat, $kategoriId)) {
                    $birimEklendi++;
                    $eklendi++;
                }
            }
            $this->log("  [{$kategoriAd}]: {$birimEklendi} hizmet");
            usleep(500000); // 0.5s rate limit
        }
        $this->counts['hizmet'] = $eklendi;
        $this->log("Hizmet aktarim toplam: {$eklendi}");
    }

    private function importHizmetlerDefault($html)
    {
        $kategoriId = $this->kategoriEkleVeyaGetir('Drklinik');
        $rows = $this->parseTableRows($html);
        foreach ($rows as $row) {
            if (count($row) < 2) continue;
            $ad = trim($row[0]);
            if (!$ad) continue;
            $fiyat = (float) preg_replace('/[^0-9.,]/', '', str_replace(',', '.', $row[1] ?? ''));
            if ($this->saveHizmet($ad, $fiyat, $kategoriId)) $this->counts['hizmet']++;
        }
        $this->log("Hizmet aktarim (default kategori): " . $this->counts['hizmet']);
    }

    private function saveHizmet($ad, $fiyat, $kategoriId)
    {
        $hizmet = Hizmetler::where('hizmet_adi', $ad)->where('hizmet_kategori_id', $kategoriId)->first();
        if (!$hizmet) {
            $hizmet = new Hizmetler();
            $hizmet->hizmet_adi = $ad;
            $hizmet->hizmet_kategori_id = $kategoriId;
            $hizmet->ozel_hizmet = true;
            if (Schema::hasColumn('hizmetler', 'salon_id')) $hizmet->salon_id = $this->salonId;
            $hizmet->save();
        }
        $sh = SalonHizmetler::where('salon_id', $this->salonId)->where('hizmet_id', $hizmet->id)->first();
        if (!$sh) {
            $sh = new SalonHizmetler();
            $sh->salon_id = $this->salonId;
            $sh->hizmet_id = $hizmet->id;
            $sh->hizmet_kategori_id = $kategoriId;
            $sh->aktif = 1;
            $sh->bolum = 2;
        }
        $sh->sure_dk = 30;
        $sh->baslangic_fiyat = $fiyat;
        $sh->son_fiyat = $fiyat;
        $sh->save();
        $this->ensureKategoriRenk($kategoriId);
        return true;
    }

    private function kategoriEkleVeyaGetir($ad)
    {
        $k = Hizmet_Kategorisi::where('hizmet_kategorisi_adi', $ad)->first();
        if (!$k) {
            $k = new Hizmet_Kategorisi();
            $k->hizmet_kategorisi_adi = $ad;
            $k->save();
        }
        return $k->id;
    }

    /**
     * calisanmodulu.aspx tablosu: Ad | Soyad | Telefon | Unvan | Duzenle | Sil
     * Tam Planla akisini uygular: IsletmeYetkilileri + Personeller + model_has_roles + calisma saatleri.
     */
    public function importPersoneller()
    {
        $this->log('Personel listesi cekiliyor (calisanmodulu.aspx)...');
        $listHtml = $this->client->getHtml('/calisanmodulu.aspx', 'personel_listesi');
        if ($listHtml === '') { $this->log('Liste sayfasi cekilemedi.'); return; }

        // Liste sayfasindan tum personel id'leri cikar (Duzenle linklerinden)
        preg_match_all('#calisan_ekle\.aspx\?id=(\d+)&t=d#', $listHtml, $idm);
        $ids = array_values(array_unique($idm[1]));
        $this->log('  ' . count($ids) . ' personel id bulundu.');

        $eklendi = 0;
        foreach ($ids as $idx => $id) {
            // Her personelin detay/duzenle formunu cek - ad, soyad, telefon, unvan dolu gelir
            $detail = $this->client->getHtml('/calisan_ekle.aspx?id=' . $id . '&t=d');
            if ($detail === '') continue;
            $ad    = $this->extractInputValue($detail, 'TB_Ad');
            $soyad = $this->extractInputValue($detail, 'TB_Soyad');
            $tel   = $this->telefonNormalize($this->extractInputValue($detail, 'TB_Telefon'));
            $unvan = $this->extractInputValue($detail, 'TB_Unvan');
            $tamAd = trim($ad . ' ' . $soyad);
            if ($tamAd === '') continue;

            if ($idx > 0 && $idx % 10 === 0) $this->log("  ..{$idx}/" . count($ids) . " personel okundu");
            usleep(200000);
            $tamAd  = trim($ad . ' ' . $soyad);
            if ($tamAd === '') continue;

            // Mevcut personel varsa SADECE eksik alanlari (telefon, unvan) guncelle, yetkili/role/calisma saatleri ekleme
            $p = Personeller::where('personel_adi', $tamAd)->where('salon_id', $this->salonId)->first();
            if ($p) {
                $upd = false;
                if (!$p->cep_telefon && $tel) { $p->cep_telefon = $tel; $upd = true; }
                if ($unvan && Schema::hasColumn('personeller', 'unvan') && !$p->unvan) { $p->unvan = $unvan; $upd = true; }
                if ($upd) $p->save();
                // Yetkili'ye de gsm1 ekle
                if ($p->yetkili_id && $tel) {
                    $y = IsletmeYetkilileri::find($p->yetkili_id);
                    if ($y && !$y->gsm1) { $y->gsm1 = $tel; $y->save(); }
                }
                continue;
            }

            // 1) IsletmeYetkilileri (login hesabi)
            $yetkili = new IsletmeYetkilileri();
            $yetkili->name = $tamAd;
            $yetkili->gsm1 = $tel;
            $yetkili->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
            $yetkili->password = Hash::make(Str::random(10));
            $yetkili->aktif = 1;
            $yetkili->save();

            // 2) Renk + sira hesapla
            $sonSira = Personeller::where('salon_id', $this->salonId)->max('takvim_sirasi');
            $sira = ($sonSira ? $sonSira : 0) + 1;
            $sonRenk = Personeller::where('salon_id', $this->salonId)->orderBy('id', 'desc')->value('renk');
            $renk = (!$sonRenk || $sonRenk >= 10) ? 1 : $sonRenk + 1;

            // 3) Personeller
            $p = new Personeller();
            $p->personel_adi = $tamAd;
            $p->cep_telefon = $tel;
            $p->salon_id = $this->salonId;
            $p->yetkili_id = $yetkili->id;
            $p->role_id = 5;
            $p->aktif = 1;
            $p->takvimde_gorunsun = 1;
            $p->takvim_sirasi = $sira;
            $p->renk = $renk;
            if ($unvan && Schema::hasColumn('personeller', 'unvan')) $p->unvan = $unvan;
            $p->save();

            // 4) model_has_roles
            DB::insert(
                'INSERT INTO model_has_roles (role_id, model_type, model_id, salon_id) VALUES (?, ?, ?, ?)',
                [5, 'App\\IsletmeYetkilileri', $yetkili->id, $this->salonId]
            );

            // 5) PersonelCalismaSaatleri (7 gun, default 09:00-21:00 acik, pazar kapali)
            for ($g = 1; $g <= 7; $g++) {
                $pcs = new PersonelCalismaSaatleri();
                $pcs->personel_id = $p->id;
                $pcs->haftanin_gunu = $g;
                $pcs->calisiyor = ($g === 7) ? 0 : 1;
                $pcs->baslangic_saati = '09:00';
                $pcs->bitis_saati = '21:00';
                $pcs->save();
            }
            $eklendi++;
            $this->counts['personel']++;
        }
        $this->log("Personel aktarim: {$eklendi} yeni");
    }

    /**
     * Musteriler: musterilistesi.aspx (DataGridView, sayfa basina 6 kayit, postback pagination).
     * Sutunlar: Sec(buton) | ID | Ad | Soyad | Cep | D.Tarihi
     * Pagination __doPostBack('DGRV_MusteriListesi', 'Page$N')
     */
    public function importMusteriler()
    {
        $this->log('Musteriler cekiliyor (musterilistesi.aspx, pagination)...');
        $page = 1;
        $totalRows = 0;
        $sayfaUyari = 0;

        $h = $this->client->getHtml('/musterilistesi.aspx');
        if ($h === '') { $this->log('Sayfa cekilemedi.'); return; }

        while (true) {
            $rowsAdded = $this->importMusteriPage($h);
            $totalRows += $rowsAdded;
            if ($page % 10 === 0) $this->log("  ..sayfa {$page}, toplam islenen: {$totalRows}");

            // Bir sonraki sayfa Page${page+1} var mi?
            $next = $page + 1;
            $hasNext = preg_match('~__doPostBack\([\'"&#39;]+DGRV_MusteriListesi[\'"&#39;]+,\s*[\'"&#39;]+Page\$' . $next . '[\'"&#39;]+~i', $h);
            if (!$hasNext) {
                // Numeric pager goremiyor olabiliriz, "..." ile devam ediyor olabilir
                // Page$X (X > current) genel pattern'i ara
                if (preg_match_all('~Page\$(\d+)~', $h, $m2)) {
                    $maxSeen = max(array_map('intval', $m2[1]));
                    if ($maxSeen >= $next) $hasNext = true;
                }
            }
            if (!$hasNext) {
                $sayfaUyari++;
                if ($sayfaUyari > 1) break; // Iki kez ust uste sayfa yoksa dur
            } else { $sayfaUyari = 0; }

            // Sonraki sayfa postback
            $h = $this->client->postBack('/musterilistesi.aspx', 'DGRV_MusteriListesi', 'Page$' . $next);
            if ($h === null) { $this->log('Postback null, durduruldu.'); break; }

            // Donen HTML hala 1. sayfa gibi ise (page change calismadi) dur
            if (!preg_match('~Page\$\d+~', $h)) break;
            $page = $next;
            usleep(200000); // 0.2sn
        }
        $this->log("Musteri aktarim toplam: {$this->counts['musteri']} (skipped: {$this->counts['skipped']})");
    }

    private function importMusteriPage($html)
    {
        $rows = $this->parseTableRowsRaw($html);
        $count = 0;
        foreach ($rows as $rawRow) {
            // Sec butonunu atla, kalanlar: ID, Ad, Soyad, Cep, D.Tarihi
            $cells = [];
            foreach ($rawRow as $tdRaw) {
                if ($this->isButtonCell($tdRaw)) continue;
                $clean = trim(preg_replace('/\s+/', ' ', strip_tags($tdRaw)));
                $clean = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $cells[] = $clean;
            }
            if (count($cells) < 4) { $this->counts['skipped']++; continue; }
            $drklinikId = $cells[0];
            $ad         = $cells[1] ?: '';
            $soyad      = $cells[2] ?: '';
            $tel        = $this->telefonNormalize($cells[3]);
            $dogum      = isset($cells[4]) ? $this->tarihNormalize($cells[4]) : null;

            $tamAd = trim($ad . ' ' . $soyad);
            if (!$tamAd) $tamAd = 'Drklinik ' . $drklinikId;

            // Effective tel - telefon yoksa drklinik id ile placeholder
            $effectiveTel = $tel ?: ('drklinik_' . $drklinikId);

            $user = User::where('cep_telefon', $effectiveTel)->first();
            if (!$user) {
                $user = new User();
                $user->name = $tamAd;
                $user->cep_telefon = $effectiveTel;
                $user->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                if ($dogum) $user->dogum_tarihi = $dogum;
                $user->save();
            }

            $portfoy = MusteriPortfoy::where('user_id', $user->id)->where('salon_id', $this->salonId)->first();
            if (!$portfoy) {
                $portfoy = new MusteriPortfoy();
                $portfoy->user_id = $user->id;
                $portfoy->salon_id = $this->salonId;
                $portfoy->aktif = 1;
                $portfoy->save();
            }

            if ($drklinikId) $this->musteriMap[$drklinikId] = $user->id;
            $this->counts['musteri']++;
            $count++;
        }
        return $count;
    }

    private function tarihNormalize($t)
    {
        if (!$t || $t === ' ' || trim($t) === '') return null;
        // dd.mm.yyyy veya dd/mm/yyyy
        if (preg_match('~^(\d{1,2})[./](\d{1,2})[./](\d{4})$~', trim($t), $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        $ts = strtotime($t);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    /**
     * Odalar: calisan_ekle.aspx detay formundaki DDL_Oda dropdown'undan cekilir.
     * Ayrica oda.aspx sayfasi mevcut (alternatif kaynak).
     */
    public function importOdalar()
    {
        $this->log('Odalar cekiliyor (DDL_Oda dropdown)...');

        // Bir personelin id'sini al, detay formundan DDL_Oda options
        $list = $this->client->getHtml('/calisanmodulu.aspx');
        if (!preg_match('~calisan_ekle\.aspx\?id=(\d+)&t=d~', $list, $m)) {
            $this->log('Personel id bulunamadi, DDL_Oda alinamiyor.');
            return;
        }
        $personelId = $m[1];
        $detail = $this->client->getHtml('/calisan_ekle.aspx?id=' . $personelId . '&t=d');
        $odalar = $this->parseSelectOptions($detail, 'DDL_Oda');
        if (empty($odalar)) { $this->log('DDL_Oda secenekleri bos.'); return; }

        $eklendi = 0;
        foreach ($odalar as $val => $ad) {
            if ($val === '0' || $val === '') continue;
            $odaAd = trim(html_entity_decode($ad, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($odaAd === '') continue;

            $oda = Odalar::where('oda_adi', $odaAd)->where('salon_id', $this->salonId)->first();
            if (!$oda) {
                $oda = new Odalar();
                $oda->oda_adi = $odaAd;
                $oda->salon_id = $this->salonId;
                $oda->durum = 1;
                if (Schema::hasColumn('odalar', 'aktifmi')) $oda->aktifmi = 1;
                $oda->save();
                $eklendi++;
            }
            $this->odaMap[$val] = $oda->id;
            $this->ensureOdaRenk($oda->id);
            $this->counts['oda']++;
        }
        $this->log("Oda aktarim: yeni={$eklendi}, toplam_map=" . count($this->odaMap));
    }

    private function ensureOdaRenk($odaId)
    {
        $var = OdaRenkleri::where('salon_id', $this->salonId)->where('oda_id', $odaId)->first();
        if ($var) return;
        $last = OdaRenkleri::where('salon_id', $this->salonId)->orderBy('id', 'desc')->first();
        $renk = 1;
        if ($last && isset($last->renk_id)) $renk = ($last->renk_id >= 10) ? 1 : $last->renk_id + 1;
        $n = new OdaRenkleri();
        $n->salon_id = $this->salonId;
        $n->oda_id = $odaId;
        $n->renk_id = $renk;
        $n->save();
    }

    /**
     * uruntanimlamalari.aspx tablosu: Urun Adi | Urun Kodu | Stok | ... | Satis Fiyati | Alis Fiyati | Marka
     */
    public function importUrunler()
    {
        $this->log('Urunler cekiliyor (uruntanimlamalari.aspx)...');
        $html = $this->client->getHtml('/uruntanimlamalari.aspx', 'urun_listesi');
        if ($html === '') { $this->log('Sayfa cekilemedi.'); return; }

        $rows = $this->parseTableRowsRaw($html);
        $this->log('  HTML tablodan ' . count($rows) . ' satir cikartildi.');

        $eklendi = 0;
        foreach ($rows as $rawRow) {
            $cells = [];
            foreach ($rawRow as $tdRaw) {
                if ($this->isButtonCell($tdRaw)) continue;
                $clean = trim(preg_replace('/\s+/', ' ', strip_tags($tdRaw)));
                $clean = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $cells[] = $clean;
            }
            $ad = isset($cells[0]) ? $cells[0] : '';
            if (!$ad) continue;
            $kod  = isset($cells[1]) ? $cells[1] : '';
            $stok = isset($cells[2]) ? (int) preg_replace('/[^0-9-]/', '', $cells[2]) : 0;
            // Ilk fiyat-benzeri (3. cell ve sonrasi) -> satis fiyati
            $fiyat = 0;
            foreach (array_slice($cells, 3) as $cell) {
                if (preg_match('/(\d+[\.,]\d{2})/', $cell, $m)) {
                    $fiyat = (float) str_replace(',', '.', $m[1]);
                    break;
                }
            }

            $u = Urunler::where('urun_adi', $ad)->where('salon_id', $this->salonId)->first();
            if (!$u) {
                $u = new Urunler();
                $u->urun_adi = $ad;
                $u->salon_id = $this->salonId;
            }
            if ($kod) $u->barkod = $kod;
            $u->stok_adedi = $stok;
            $u->fiyat = $fiyat;
            $u->aktif = 1;
            $u->save();
            $eklendi++;
            $this->counts['urun']++;
        }
        $this->log("Urun aktarim: {$eklendi}");
    }

    /**
     * En genis tabloyu bul, her satirin RAW td HTML'lerini doner (icerisinde input/a tag'leri korunur).
     */
    private function parseTableRowsRaw($html)
    {
        if (!preg_match_all('#<table[^>]*>(.*?)</table>#is', $html, $tables)) return [];
        $bestRows = [];
        foreach ($tables[1] as $t) {
            if (preg_match_all('#<tr[^>]*>(.*?)</tr>#is', $t, $r)) {
                if (count($r[1]) > count($bestRows)) $bestRows = $r[1];
            }
        }
        if (empty($bestRows)) return [];
        $out = [];
        foreach ($bestRows as $tr) {
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('#<td[^>]*>(.*?)</td>#is', $tr, $tds);
            if (empty($tds[1])) continue;
            $out[] = $tds[1];
        }
        return $out;
    }

    /**
     * td icerigi sadece buton/link/script ise true. Gercek text yoksa atilmali.
     */
    private function isButtonCell($tdRaw)
    {
        $hasButton = preg_match('#<(?:input[^>]+type="(?:button|submit)"|a\s|button\s)#i', $tdRaw);
        $textOnly = trim(strip_tags($tdRaw));
        // Sadece buton var ve disindaki text (anchor text dahil edilmis olabilir) "kisa anlamsiz" ise atla
        if ($hasButton) {
            // Buton text icerigi (anchor metni) bilinen aksiyon kelimelerine eslesirse buton say
            if (preg_match('/^(D[uü]zenle|Sil|[ÖÖoO]demeler|Prim\s*Hesab[ıi]|Sec|Sec[iı]m|Detay|G[oo]ster|G[uü]ncelle|Kaydet|Iptal|İptal)$/iu', $textOnly)) return true;
            if ($textOnly === '' || strlen($textOnly) < 2) return true;
        }
        return false;
    }

    private function telefonNormalize($tel)
    {
        if (!$tel) return null;
        $tel = preg_replace('/[^0-9]/', '', (string) $tel);
        $tel = preg_replace('/^90/', '', $tel);
        $tel = preg_replace('/^0/', '', $tel);
        return $tel ?: null;
    }

    /**
     * ASP.NET WebForms TextBox <input name="X" value="..."> degerini cikar.
     */
    private function extractInputValue($html, $name)
    {
        // value once gelirse veya sonra gelirse iki paterni dene
        $pat = '#<input[^>]+name="' . preg_quote($name, '#') . '"[^>]*\bvalue="([^"]*)"#i';
        if (preg_match($pat, $html, $m)) return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $pat2 = '#<input[^>]+\bvalue="([^"]*)"[^>]+name="' . preg_quote($name, '#') . '"#i';
        if (preg_match($pat2, $html, $m)) return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return '';
    }

    private function parseSelectOptions($html, $selectId)
    {
        $pat = '#<select[^>]+(?:id|name)="' . preg_quote($selectId, '#') . '"[^>]*>(.*?)</select>#is';
        if (!preg_match($pat, $html, $m)) return [];
        $opts = [];
        if (preg_match_all('#<option[^>]*value="([^"]*)"[^>]*>(.*?)</option>#is', $m[1], $om, PREG_SET_ORDER)) {
            foreach ($om as $o) $opts[$o[1]] = trim(strip_tags($o[2]));
        }
        return $opts;
    }

    private function ensureKategoriRenk($kategoriId)
    {
        $var = SalonHizmetKategoriRenkleri::where('salon_id', $this->salonId)
            ->where('hizmet_kategori_id', $kategoriId)->first();
        if ($var) return;
        $last = SalonHizmetKategoriRenkleri::where('salon_id', $this->salonId)
            ->orderBy('renk_id', 'desc')->first();
        $renk = 1;
        if ($last) $renk = ($last->renk_id >= 10) ? 1 : $last->renk_id + 1;
        $n = new SalonHizmetKategoriRenkleri();
        $n->salon_id = $this->salonId;
        $n->hizmet_kategori_id = $kategoriId;
        $n->renk_id = $renk;
        $n->save();
    }

    /**
     * Generic HTML tablo parser: <tr> icindeki <td>'leri (header hari�) toplar.
     * Header'i (th) atlar, sadece veri satirlarini doner. Her satir td icerigi text array'i.
     */
    private function parseTableRows($html)
    {
        // En genis tabloyu bul (rapor gridviewleri ana tablodur)
        if (!preg_match_all('#<table[^>]*>(.*?)</table>#is', $html, $tables)) return [];
        // En cok satir iceren tabloyu sec
        $bestRows = [];
        foreach ($tables[1] as $t) {
            if (preg_match_all('#<tr[^>]*>(.*?)</tr>#is', $t, $r)) {
                if (count($r[1]) > count($bestRows)) $bestRows = $r[1];
            }
        }
        if (empty($bestRows)) return [];

        $out = [];
        foreach ($bestRows as $tr) {
            // header satirini atla
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('#<td[^>]*>(.*?)</td>#is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cols = array_map(function ($td) {
                $t = preg_replace('/\s+/', ' ', strip_tags($td));
                return trim(html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }, $tds[1]);
            $out[] = $cols;
        }
        return $out;
    }

    private function log($msg)
    {
        if ($this->out) $this->out->writeln($msg);
        Log::info('[DrklinikImporter] ' . $msg);
    }
}
