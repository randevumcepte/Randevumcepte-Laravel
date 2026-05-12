<?php

namespace App\Http\Controllers;

use App\Depo;
use App\HizmetSarfRecetesi;
use App\Salonlar;
use App\StokHareketi;
use App\Tedarikci;
use App\UrunDepoStoku;
use App\UrunKategorisi;
use App\Urunler;
use App\UrunSatislari;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Stok Yonetimi v2 API
 *
 * Tum endpoint'ler /api/stok/* altinda toplandi. Eski endpoint'ler
 * (ApiController@urunler, urunekleduzenle vb.) geriye uyumluluk icin
 * yerinde duruyor — yeni mobil ekranlar bu controller'i kullanir.
 */
class StokController extends Controller
{
    // ============================================================
    // YARDIMCILAR
    // ============================================================

    /**
     * Stok hareketi olustur + urun_depo_stoklari cache'ini guncelle.
     * Tum stok degisiklikleri buradan gecmeli ki audit tutarli kalsin.
     *
     * @param  array  $data  salon_id, urun_id, depo_id, miktar (+/-), hareket_tipi,
     *                       referans_tip, referans_id, batch_uuid,
     *                       birim_alis_fiyati, birim_satis_fiyati,
     *                       aciklama, kullanici_id, kullanici_tipi, tarih
     */
    public static function hareketKaydet(array $data): StokHareketi
    {
        $data['tarih'] = $data['tarih'] ?? now();
        $data['miktar'] = (float) $data['miktar'];

        return DB::transaction(function () use ($data) {
            $hareket = StokHareketi::create($data);

            if (!empty($data['depo_id'])) {
                $cache = UrunDepoStoku::firstOrCreate(
                    ['urun_id' => $data['urun_id'], 'depo_id' => $data['depo_id']],
                    ['salon_id' => $data['salon_id'], 'stok' => 0]
                );
                $cache->stok = (float) $cache->stok + (float) $data['miktar'];
                $cache->save();
            }

            // Geriye uyumluluk: eski urunler.stok_adedi kolonunu da senkron tut
            $toplam = UrunDepoStoku::where('urun_id', $data['urun_id'])->sum('stok');
            Urunler::where('id', $data['urun_id'])->update(['stok_adedi' => $toplam]);

            return $hareket;
        });
    }

    /** Salon icin varsayilan depo, yoksa olustur. */
    public static function varsayilanDepoyuGetirVeyaOlustur(int $salonId): Depo
    {
        $depo = Depo::where('salon_id', $salonId)->where('varsayilan', true)->first();
        if (!$depo) {
            $depo = Depo::create([
                'salon_id'   => $salonId,
                'depo_adi'   => 'Ana Depo',
                'varsayilan' => true,
                'aktif'      => true,
            ]);
        }

        return $depo;
    }

    protected function urunFormat(Urunler $u): array
    {
        return [
            'id'                  => (string) $u->id,
            'urun_adi'            => (string) $u->urun_adi,
            'barkod'              => (string) ($u->barkod ?? ''),
            'sku'                 => (string) ($u->sku ?? ''),
            'fiyat'               => (string) ($u->fiyat ?? '0'),
            'alis_fiyati'         => (string) ($u->alis_fiyati ?? ''),
            'kdv_orani'           => (string) ($u->kdv_orani ?? ''),
            'birim'               => (string) ($u->birim ?? 'adet'),
            'tip'                 => (string) ($u->tip ?? 'satis'),
            'kategori_id'         => $u->kategori_id ? (string) $u->kategori_id : '',
            'kategori_adi'        => optional($u->kategori)->ad ?? '',
            'kategori_renk'       => optional($u->kategori)->renk ?? '',
            'kategori_ikon'       => optional($u->kategori)->ikon ?? '',
            'tedarikci_id'        => $u->tedarikci_id ? (string) $u->tedarikci_id : '',
            'tedarikci_adi'       => optional($u->tedarikci)->ad ?? '',
            'varsayilan_depo_id'  => $u->varsayilan_depo_id ? (string) $u->varsayilan_depo_id : '',
            'aktif'               => $u->aktif ? '1' : '0',
            'stok_adedi'          => (string) ($u->stok_adedi ?? '0'),
            'dusuk_stok_siniri'   => (string) ($u->dusuk_stok_siniri ?? ''),
            'kritik_stok_siniri'  => (string) ($u->kritik_stok_siniri ?? ''),
            'resim_url'           => (string) ($u->resim_url ?? ''),
            'aciklama'            => (string) ($u->aciklama ?? ''),
            'salon_id'            => (string) ($u->salon_id ?? ''),
        ];
    }

    // ============================================================
    // URUN CRUD
    // ============================================================

    public function urunListesi(Request $request, $salonid)
    {
        $q = Urunler::with(['kategori', 'tedarikci'])
            ->where('salon_id', $salonid)
            ->where('aktif', true);

        if ($request->filled('arama')) {
            $a = $request->arama;
            $q->where(function ($w) use ($a) {
                $w->where('urun_adi', 'like', "%{$a}%")
                  ->orWhere('barkod', 'like', "%{$a}%")
                  ->orWhere('sku', 'like', "%{$a}%");
            });
        }
        if ($request->filled('kategori_id')) {
            $q->where('kategori_id', $request->kategori_id);
        }
        if ($request->filled('tip')) {
            $q->where('tip', $request->tip);
        }

        $urunler = $q->orderBy('urun_adi')->get();

        return $urunler->map(fn ($u) => $this->urunFormat($u))->values();
    }

    public function urunDetay(Request $request, $urunid)
    {
        $u = Urunler::with(['kategori', 'tedarikci', 'varsayilanDepo', 'depoStoklari.depo'])
            ->where('id', $urunid)
            ->first();

        if (!$u) {
            return response()->json(['status' => 'error', 'mesaj' => 'Urun bulunamadi'], 404);
        }

        $data = $this->urunFormat($u);
        $data['depo_stoklari'] = $u->depoStoklari->map(fn ($d) => [
            'depo_id'   => (string) $d->depo_id,
            'depo_adi'  => optional($d->depo)->depo_adi ?? '',
            'stok'      => (string) $d->stok,
        ])->values();

        return $data;
    }

    public function urunBarkodAra(Request $request, $salonid)
    {
        $barkod = trim((string) $request->barkod);
        if ($barkod === '') {
            return response()->json(['status' => 'error', 'mesaj' => 'Barkod bos'], 400);
        }
        $u = Urunler::with(['kategori', 'tedarikci'])
            ->where('salon_id', $salonid)
            ->where('aktif', true)
            ->where('barkod', $barkod)
            ->first();

        if (!$u) {
            return response()->json(['status' => 'bulunamadi'], 404);
        }

        return $this->urunFormat($u);
    }

    public function urunKaydet(Request $request, $salonid)
    {
        $data = $request->all();

        $urun = !empty($data['id']) && $data['id'] != '0'
            ? Urunler::find($data['id'])
            : new Urunler();

        if (!$urun) {
            return response()->json(['status' => 'error', 'mesaj' => 'Urun bulunamadi'], 404);
        }

        $yeni = !$urun->exists;

        $urun->salon_id            = $salonid;
        $urun->urun_adi            = $data['urun_adi'] ?? $urun->urun_adi;
        $urun->barkod              = $data['barkod']   ?? $urun->barkod;
        $urun->sku                 = $data['sku']      ?? $urun->sku;
        $urun->fiyat               = $data['fiyat']    ?? $urun->fiyat ?? 0;
        $urun->alis_fiyati         = $data['alis_fiyati'] ?? $urun->alis_fiyati;
        $urun->kdv_orani           = $data['kdv_orani'] ?? $urun->kdv_orani;
        $urun->birim               = $data['birim']    ?? $urun->birim ?? 'adet';
        $urun->tip                 = $data['tip']      ?? $urun->tip ?? 'satis';
        $urun->kategori_id         = !empty($data['kategori_id']) ? $data['kategori_id'] : null;
        $urun->tedarikci_id        = !empty($data['tedarikci_id']) ? $data['tedarikci_id'] : null;
        $urun->dusuk_stok_siniri   = $data['dusuk_stok_siniri'] ?? $urun->dusuk_stok_siniri;
        $urun->kritik_stok_siniri  = $data['kritik_stok_siniri'] ?? $urun->kritik_stok_siniri;
        $urun->resim_url           = $data['resim_url'] ?? $urun->resim_url;
        $urun->aciklama            = $data['aciklama'] ?? $urun->aciklama;
        $urun->aktif               = $data['aktif'] ?? true;

        $depo = self::varsayilanDepoyuGetirVeyaOlustur((int) $salonid);
        if (empty($urun->varsayilan_depo_id)) {
            $urun->varsayilan_depo_id = $depo->id;
        }
        $urun->save();

        // Yeni urun ise ve baslangic stok_adedi gonderildiyse acilis hareketi olustur
        if ($yeni) {
            $baslangic = (float) ($data['stok_adedi'] ?? 0);
            if ($baslangic != 0) {
                self::hareketKaydet([
                    'salon_id'           => $salonid,
                    'urun_id'            => $urun->id,
                    'depo_id'            => $urun->varsayilan_depo_id,
                    'miktar'             => $baslangic,
                    'hareket_tipi'       => 'acilis',
                    'birim_alis_fiyati'  => $urun->alis_fiyati,
                    'aciklama'           => 'Urun olusturulurken baslangic stogu',
                    'kullanici_id'       => $request->kullanici_id,
                    'kullanici_tipi'     => $request->kullanici_tipi,
                ]);
            }
        }

        $urun->refresh();

        return $this->urunFormat($urun);
    }

    public function urunSil(Request $request)
    {
        $u = Urunler::find($request->urun_id);
        if (!$u) {
            return response()->json(['status' => 'error', 'mesaj' => 'Urun bulunamadi'], 404);
        }
        $u->aktif = false;
        $u->save();

        return ['status' => 'ok'];
    }

    // ============================================================
    // KATEGORI CRUD
    // ============================================================

    public function kategoriListesi(Request $request, $salonid)
    {
        return UrunKategorisi::where('salon_id', $salonid)
            ->where('aktif', true)
            ->orderBy('sira')
            ->orderBy('ad')
            ->get();
    }

    public function kategoriKaydet(Request $request, $salonid)
    {
        $k = !empty($request->id) ? UrunKategorisi::find($request->id) : new UrunKategorisi();
        if (!$k) {
            return response()->json(['status' => 'error', 'mesaj' => 'Kategori yok'], 404);
        }
        $k->salon_id = $salonid;
        $k->ad       = $request->ad;
        $k->ikon     = $request->ikon;
        $k->renk     = $request->renk;
        $k->sira     = (int) ($request->sira ?? 0);
        $k->aktif    = (bool) ($request->aktif ?? true);
        $k->save();

        return $k;
    }

    public function kategoriSil(Request $request)
    {
        $k = UrunKategorisi::find($request->id);
        if ($k) {
            $k->aktif = false;
            $k->save();
        }

        return ['status' => 'ok'];
    }

    // ============================================================
    // DEPO CRUD
    // ============================================================

    public function depoListesi(Request $request, $salonid)
    {
        $depolar = Depo::where('salon_id', $salonid)
            ->where('aktif', true)
            ->orderByDesc('varsayilan')
            ->orderBy('depo_adi')
            ->get();

        return $depolar->map(function ($d) {
            $toplam = UrunDepoStoku::where('depo_id', $d->id)->sum('stok');

            return [
                'id'           => (string) $d->id,
                'depo_adi'     => $d->depo_adi,
                'aciklama'     => $d->aciklama,
                'varsayilan'   => $d->varsayilan ? '1' : '0',
                'aktif'        => $d->aktif ? '1' : '0',
                'toplam_stok'  => (string) $toplam,
                'salon_id'     => (string) $d->salon_id,
            ];
        })->values();
    }

    public function depoKaydet(Request $request, $salonid)
    {
        $d = !empty($request->id) ? Depo::find($request->id) : new Depo();
        if (!$d) {
            return response()->json(['status' => 'error'], 404);
        }
        $d->salon_id = $salonid;
        $d->depo_adi = $request->depo_adi;
        $d->aciklama = $request->aciklama;
        $d->aktif    = (bool) ($request->aktif ?? true);

        if ($request->boolean('varsayilan')) {
            Depo::where('salon_id', $salonid)->update(['varsayilan' => false]);
            $d->varsayilan = true;
        }
        $d->save();

        return $d;
    }

    public function depoSil(Request $request)
    {
        $d = Depo::find($request->id);
        if (!$d) {
            return ['status' => 'ok'];
        }
        $stok = UrunDepoStoku::where('depo_id', $d->id)->where('stok', '>', 0)->exists();
        if ($stok) {
            return response()->json([
                'status' => 'error',
                'mesaj'  => 'Bu depoda hala stok var, once transfer veya fire ile bosaltin.',
            ], 422);
        }
        if ($d->varsayilan) {
            return response()->json([
                'status' => 'error',
                'mesaj'  => 'Varsayilan depo silinemez.',
            ], 422);
        }
        $d->aktif = false;
        $d->save();

        return ['status' => 'ok'];
    }

    // ============================================================
    // TEDARIKCI CRUD
    // ============================================================

    public function tedarikciListesi(Request $request, $salonid)
    {
        return Tedarikci::where('salon_id', $salonid)->where('aktif', true)->orderBy('ad')->get();
    }

    public function tedarikciKaydet(Request $request, $salonid)
    {
        $t = !empty($request->id) ? Tedarikci::find($request->id) : new Tedarikci();
        if (!$t) {
            return response()->json(['status' => 'error'], 404);
        }
        $t->salon_id = $salonid;
        $t->ad       = $request->ad;
        $t->telefon  = $request->telefon;
        $t->vergi_no = $request->vergi_no;
        $t->email    = $request->email;
        $t->adres    = $request->adres;
        $t->aciklama = $request->aciklama;
        $t->aktif    = (bool) ($request->aktif ?? true);
        $t->save();

        return $t;
    }

    public function tedarikciSil(Request $request)
    {
        $t = Tedarikci::find($request->id);
        if ($t) {
            $t->aktif = false;
            $t->save();
        }

        return ['status' => 'ok'];
    }

    // ============================================================
    // STOK HAREKETLERI
    // ============================================================

    public function hareketListesi(Request $request, $salonid)
    {
        $q = StokHareketi::where('salon_id', $salonid);

        if ($request->filled('urun_id')) {
            $q->where('urun_id', $request->urun_id);
        }
        if ($request->filled('depo_id')) {
            $q->where('depo_id', $request->depo_id);
        }
        if ($request->filled('hareket_tipi')) {
            $q->where('hareket_tipi', $request->hareket_tipi);
        }
        if ($request->filled('baslangic')) {
            $q->where('tarih', '>=', $request->baslangic);
        }
        if ($request->filled('bitis')) {
            $q->where('tarih', '<=', $request->bitis);
        }

        $hareketler = $q->orderByDesc('tarih')->limit((int) ($request->limit ?? 200))->get();

        return $hareketler->map(function ($h) {
            return [
                'id'                  => (string) $h->id,
                'urun_id'             => (string) $h->urun_id,
                'depo_id'             => (string) ($h->depo_id ?? ''),
                'miktar'              => (string) $h->miktar,
                'hareket_tipi'        => $h->hareket_tipi,
                'referans_tip'        => $h->referans_tip,
                'referans_id'         => (string) ($h->referans_id ?? ''),
                'birim_alis_fiyati'   => (string) ($h->birim_alis_fiyati ?? ''),
                'birim_satis_fiyati'  => (string) ($h->birim_satis_fiyati ?? ''),
                'aciklama'            => $h->aciklama,
                'kullanici_id'        => (string) ($h->kullanici_id ?? ''),
                'kullanici_tipi'      => $h->kullanici_tipi,
                'tarih'               => optional($h->tarih)->format('Y-m-d H:i:s'),
            ];
        })->values();
    }

    /** Manuel stok hareketi (fire, manuel duzeltme, baslangic, vb.). */
    public function manuelHareket(Request $request, $salonid)
    {
        $urun = Urunler::find($request->urun_id);
        if (!$urun) {
            return response()->json(['status' => 'error', 'mesaj' => 'Urun bulunamadi'], 404);
        }
        $depoId = $request->depo_id ?: $urun->varsayilan_depo_id ?: self::varsayilanDepoyuGetirVeyaOlustur($salonid)->id;

        $miktar = (float) $request->miktar;
        $tip    = $request->hareket_tipi ?: 'manuel';
        if (in_array($tip, ['satis', 'sarf', 'fire', 'transfer_cikis']) && $miktar > 0) {
            $miktar = -$miktar;
        }

        $h = self::hareketKaydet([
            'salon_id'           => $salonid,
            'urun_id'            => $urun->id,
            'depo_id'            => $depoId,
            'miktar'             => $miktar,
            'hareket_tipi'       => $tip,
            'birim_alis_fiyati'  => $request->birim_alis_fiyati,
            'birim_satis_fiyati' => $request->birim_satis_fiyati,
            'aciklama'           => $request->aciklama,
            'kullanici_id'       => $request->kullanici_id,
            'kullanici_tipi'     => $request->kullanici_tipi,
        ]);

        return ['status' => 'ok', 'hareket_id' => (string) $h->id];
    }

    // ============================================================
    // ALIS GIRISI (mal kabul)
    // ============================================================

    public function alisGirisi(Request $request, $salonid)
    {
        $kalemler = $request->kalemler;
        if (!is_array($kalemler) || count($kalemler) === 0) {
            return response()->json(['status' => 'error', 'mesaj' => 'Kalem yok'], 422);
        }
        $tedarikciId = $request->tedarikci_id ?: null;
        $batch       = (string) Str::uuid();
        $depoId      = $request->depo_id ?: self::varsayilanDepoyuGetirVeyaOlustur($salonid)->id;

        $sonuc = [];
        foreach ($kalemler as $k) {
            if (empty($k['urun_id']) || empty($k['miktar'])) {
                continue;
            }
            $miktar = abs((float) $k['miktar']);
            $birimFiyat = isset($k['birim_alis_fiyati']) ? (float) $k['birim_alis_fiyati'] : null;

            self::hareketKaydet([
                'salon_id'           => $salonid,
                'urun_id'            => $k['urun_id'],
                'depo_id'            => $k['depo_id'] ?? $depoId,
                'miktar'             => $miktar,
                'hareket_tipi'       => 'alis',
                'referans_tip'       => 'alis_fisi',
                'batch_uuid'         => $batch,
                'birim_alis_fiyati'  => $birimFiyat,
                'aciklama'           => $request->aciklama ?: ($tedarikciId ? "Tedarikci ID:{$tedarikciId}" : null),
                'kullanici_id'       => $request->kullanici_id,
                'kullanici_tipi'     => $request->kullanici_tipi,
            ]);

            if ($tedarikciId && $birimFiyat) {
                Urunler::where('id', $k['urun_id'])->update([
                    'tedarikci_id' => $tedarikciId,
                    'alis_fiyati'  => $birimFiyat,
                ]);
            }
            $sonuc[] = (string) $k['urun_id'];
        }

        return ['status' => 'ok', 'batch_uuid' => $batch, 'kalem_sayisi' => count($sonuc)];
    }

    // ============================================================
    // TRANSFER (depodan depoya)
    // ============================================================

    public function transfer(Request $request, $salonid)
    {
        $urunId      = $request->urun_id;
        $kaynakDepo  = $request->kaynak_depo_id;
        $hedefDepo   = $request->hedef_depo_id;
        $miktar      = abs((float) $request->miktar);

        if (!$urunId || !$kaynakDepo || !$hedefDepo || $miktar <= 0) {
            return response()->json(['status' => 'error', 'mesaj' => 'Eksik parametre'], 422);
        }
        if ($kaynakDepo == $hedefDepo) {
            return response()->json(['status' => 'error', 'mesaj' => 'Ayni depo'], 422);
        }

        $batch = (string) Str::uuid();
        DB::transaction(function () use ($salonid, $urunId, $kaynakDepo, $hedefDepo, $miktar, $batch, $request) {
            self::hareketKaydet([
                'salon_id'       => $salonid,
                'urun_id'        => $urunId,
                'depo_id'        => $kaynakDepo,
                'miktar'         => -$miktar,
                'hareket_tipi'   => 'transfer_cikis',
                'batch_uuid'     => $batch,
                'aciklama'       => "Hedef depo: {$hedefDepo}",
                'kullanici_id'   => $request->kullanici_id,
                'kullanici_tipi' => $request->kullanici_tipi,
            ]);
            self::hareketKaydet([
                'salon_id'       => $salonid,
                'urun_id'        => $urunId,
                'depo_id'        => $hedefDepo,
                'miktar'         => $miktar,
                'hareket_tipi'   => 'transfer_giris',
                'batch_uuid'     => $batch,
                'aciklama'       => "Kaynak depo: {$kaynakDepo}",
                'kullanici_id'   => $request->kullanici_id,
                'kullanici_tipi' => $request->kullanici_tipi,
            ]);
        });

        return ['status' => 'ok', 'batch_uuid' => $batch];
    }

    // ============================================================
    // SAYIM
    // ============================================================

    /**
     * kalemler: [{urun_id, depo_id, sayilan_miktar}]
     * Sistem her urun icin mevcut stok - sayilan_miktar = fark; fark != 0 ise sayim hareketi.
     */
    public function sayimUygula(Request $request, $salonid)
    {
        $kalemler = $request->kalemler;
        if (!is_array($kalemler)) {
            return response()->json(['status' => 'error', 'mesaj' => 'Kalem yok'], 422);
        }
        $batch = (string) Str::uuid();
        $fark  = 0;
        $sayilan = 0;

        foreach ($kalemler as $k) {
            $urunId = $k['urun_id'] ?? null;
            $depoId = $k['depo_id'] ?? null;
            $sayilanMiktar = isset($k['sayilan_miktar']) ? (float) $k['sayilan_miktar'] : 0;
            if (!$urunId || !$depoId) {
                continue;
            }
            $mevcut = (float) UrunDepoStoku::where('urun_id', $urunId)->where('depo_id', $depoId)->value('stok');
            $diff = $sayilanMiktar - $mevcut;
            if (abs($diff) < 0.0001) {
                continue;
            }
            self::hareketKaydet([
                'salon_id'       => $salonid,
                'urun_id'        => $urunId,
                'depo_id'        => $depoId,
                'miktar'         => $diff,
                'hareket_tipi'   => 'sayim',
                'batch_uuid'     => $batch,
                'aciklama'       => "Sayim duzeltme: mevcut {$mevcut}, sayilan {$sayilanMiktar}",
                'kullanici_id'   => $request->kullanici_id,
                'kullanici_tipi' => $request->kullanici_tipi,
            ]);
            $fark += $diff;
            $sayilan++;
        }

        return [
            'status'        => 'ok',
            'batch_uuid'    => $batch,
            'sayilan_kalem' => $sayilan,
            'toplam_fark'   => (string) $fark,
        ];
    }

    // ============================================================
    // HIZLI SATIS (kasa)
    // ============================================================

    /**
     * sepet: [{urun_id, miktar, birim_fiyat}]
     * Satis hareketi ve urun_satislari kayitlari olusturur.
     */
    public function hizliSatis(Request $request, $salonid)
    {
        $sepet = $request->sepet;
        if (!is_array($sepet) || count($sepet) === 0) {
            return response()->json(['status' => 'error', 'mesaj' => 'Sepet bos'], 422);
        }
        $batch       = (string) Str::uuid();
        $musteriId   = $request->user_id;
        $personelId  = $request->personel_id;
        $randevuId   = $request->randevu_id;
        $toplamTutar = 0;
        $satisIds    = [];

        DB::transaction(function () use (&$sepet, $salonid, $batch, $musteriId, $personelId, $randevuId, &$toplamTutar, &$satisIds, $request) {
            foreach ($sepet as $k) {
                $urunId = $k['urun_id'] ?? null;
                $miktar = abs((float) ($k['miktar'] ?? 1));
                if (!$urunId || $miktar <= 0) {
                    continue;
                }
                $urun = Urunler::find($urunId);
                if (!$urun) {
                    continue;
                }
                $birimFiyat = isset($k['birim_fiyat']) ? (float) $k['birim_fiyat'] : (float) $urun->fiyat;
                $depoId = $k['depo_id'] ?? $urun->varsayilan_depo_id;

                $satis = new UrunSatislari();
                $satis->salon_id    = $salonid;
                $satis->urun_id     = $urun->id;
                $satis->user_id     = $musteriId;
                $satis->personel_id = $personelId;
                $satis->randevu_id  = $randevuId;
                $satis->adet        = $miktar;
                $satis->fiyat       = $birimFiyat;
                $satis->tarih       = now();
                $satis->save();
                $satisIds[] = (string) $satis->id;

                self::hareketKaydet([
                    'salon_id'            => $salonid,
                    'urun_id'             => $urun->id,
                    'depo_id'             => $depoId,
                    'miktar'              => -$miktar,
                    'hareket_tipi'        => 'satis',
                    'referans_tip'        => 'urun_satislari',
                    'referans_id'         => $satis->id,
                    'batch_uuid'          => $batch,
                    'birim_satis_fiyati'  => $birimFiyat,
                    'birim_alis_fiyati'   => $urun->alis_fiyati,
                    'kullanici_id'        => $request->kullanici_id,
                    'kullanici_tipi'      => $request->kullanici_tipi,
                ]);
                $toplamTutar += $birimFiyat * $miktar;
            }
        });

        return [
            'status'       => 'ok',
            'batch_uuid'   => $batch,
            'satis_ids'    => $satisIds,
            'toplam_tutar' => (string) $toplamTutar,
        ];
    }

    // ============================================================
    // RAPOR
    // ============================================================

    public function ozet(Request $request, $salonid)
    {
        $toplamUrun = Urunler::where('salon_id', $salonid)->where('aktif', true)->count();

        // Dusuk ve tukenen stok hesabi
        $urunler = Urunler::where('salon_id', $salonid)->where('aktif', true)->get(['id', 'stok_adedi', 'dusuk_stok_siniri', 'kritik_stok_siniri', 'fiyat', 'alis_fiyati']);
        $dusuk = 0;
        $tukenen = 0;
        $toplamSatisDegeri = 0;
        $toplamAlisDegeri  = 0;
        foreach ($urunler as $u) {
            $stok = (float) $u->stok_adedi;
            $dusukSinir = (float) $u->dusuk_stok_siniri;
            if ($stok <= 0) {
                $tukenen++;
            } elseif ($dusukSinir > 0 && $stok <= $dusukSinir) {
                $dusuk++;
            }
            $toplamSatisDegeri += $stok * (float) $u->fiyat;
            $toplamAlisDegeri  += $stok * (float) ($u->alis_fiyati ?? 0);
        }

        $busatisAdet = StokHareketi::where('salon_id', $salonid)
            ->where('hareket_tipi', 'satis')
            ->where('tarih', '>=', Carbon::now()->startOfDay())
            ->sum(DB::raw('ABS(miktar)'));

        $busatisTutar = StokHareketi::where('salon_id', $salonid)
            ->where('hareket_tipi', 'satis')
            ->where('tarih', '>=', Carbon::now()->startOfDay())
            ->sum(DB::raw('ABS(miktar) * COALESCE(birim_satis_fiyati,0)'));

        return [
            'toplam_urun'          => (string) $toplamUrun,
            'dusuk_stok'           => (string) $dusuk,
            'tukenen'              => (string) $tukenen,
            'toplam_satis_degeri'  => (string) $toplamSatisDegeri,
            'toplam_alis_degeri'   => (string) $toplamAlisDegeri,
            'bugun_satis_adet'     => (string) $busatisAdet,
            'bugun_satis_tutar'    => (string) $busatisTutar,
        ];
    }

    public function dusukStokListesi(Request $request, $salonid)
    {
        $urunler = Urunler::with('kategori')
            ->where('salon_id', $salonid)
            ->where('aktif', true)
            ->whereNotNull('dusuk_stok_siniri')
            ->whereColumn('stok_adedi', '<=', 'dusuk_stok_siniri')
            ->orderBy('stok_adedi')
            ->get();

        return $urunler->map(fn ($u) => $this->urunFormat($u))->values();
    }

    public function urunSatisRaporu(Request $request, $urunid)
    {
        $baslangic = $request->baslangic ?: Carbon::now()->subDays(30)->startOfDay();
        $bitis     = $request->bitis     ?: Carbon::now()->endOfDay();

        $satislar = StokHareketi::where('urun_id', $urunid)
            ->where('hareket_tipi', 'satis')
            ->whereBetween('tarih', [$baslangic, $bitis])
            ->selectRaw('DATE(tarih) gun, SUM(ABS(miktar)) adet, SUM(ABS(miktar)*COALESCE(birim_satis_fiyati,0)) tutar')
            ->groupBy('gun')
            ->orderBy('gun')
            ->get();

        return $satislar;
    }

    // ============================================================
    // SARF RECETELERI (Faz 6)
    // ============================================================

    public function receteListesi(Request $request, $salonid)
    {
        $q = HizmetSarfRecetesi::with('urun')
            ->where('salon_id', $salonid)
            ->where('aktif', true);
        if ($request->filled('hizmet_id')) {
            $q->where('hizmet_id', $request->hizmet_id);
            $q->where('hizmet_tipi', $request->hizmet_tipi ?: 'islem');
        }

        return $q->get();
    }

    public function receteKaydet(Request $request, $salonid)
    {
        $r = !empty($request->id) ? HizmetSarfRecetesi::find($request->id) : new HizmetSarfRecetesi();
        if (!$r) {
            return response()->json(['status' => 'error'], 404);
        }
        $r->salon_id    = $salonid;
        $r->hizmet_id   = $request->hizmet_id;
        $r->hizmet_tipi = $request->hizmet_tipi ?: 'islem';
        $r->urun_id     = $request->urun_id;
        $r->miktar      = (float) $request->miktar;
        $r->aktif       = (bool) ($request->aktif ?? true);
        $r->save();

        return $r;
    }

    public function receteSil(Request $request)
    {
        $r = HizmetSarfRecetesi::find($request->id);
        if ($r) {
            $r->aktif = false;
            $r->save();
        }

        return ['status' => 'ok'];
    }

    /**
     * Bir hizmet tamamlandiginda receteyi uygula — sarf hareketleri olusturur.
     * Diger controller'lar (randevu kapanisi vb.) bu metodu cagirabilir.
     */
    public static function receteyiUygula(int $salonId, int $hizmetId, string $hizmetTipi, int $randevuId = null, int $personelId = null, float $carpan = 1.0): array
    {
        $receteler = HizmetSarfRecetesi::where('salon_id', $salonId)
            ->where('hizmet_id', $hizmetId)
            ->where('hizmet_tipi', $hizmetTipi)
            ->where('aktif', true)
            ->get();
        if ($receteler->isEmpty()) {
            return ['uygulandi' => 0];
        }
        $batch = (string) Str::uuid();
        $say = 0;
        foreach ($receteler as $r) {
            $urun = Urunler::find($r->urun_id);
            if (!$urun) {
                continue;
            }
            $depoId = $urun->varsayilan_depo_id ?: self::varsayilanDepoyuGetirVeyaOlustur($salonId)->id;
            $miktar = -1 * abs((float) $r->miktar * $carpan);
            self::hareketKaydet([
                'salon_id'       => $salonId,
                'urun_id'        => $r->urun_id,
                'depo_id'        => $depoId,
                'miktar'         => $miktar,
                'hareket_tipi'   => 'sarf',
                'referans_tip'   => $hizmetTipi . '_recete',
                'referans_id'    => $randevuId,
                'batch_uuid'     => $batch,
                'aciklama'       => "Hizmet ID:{$hizmetId} icin otomatik sarf",
                'kullanici_id'   => $personelId,
                'kullanici_tipi' => 'personel',
            ]);
            $say++;
        }

        return ['uygulandi' => $say, 'batch_uuid' => $batch];
    }
}
