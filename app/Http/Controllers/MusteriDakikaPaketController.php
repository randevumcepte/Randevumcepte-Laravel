<?php

namespace App\Http\Controllers;

use App\MusteriDakikaPaketHareketi;
use App\MusteriDakikaPaketi;
use App\MusteriPortfoy;
use App\RandevuHizmetler;
use App\Services\MusteriDakikaPaketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Dakika bazli paket satislari API'si. Solaryum / masaj gibi sure satilan
 * hizmetler icin musteriye dakika havuzu acar; randevu "geldi"
 * isaretlendiginde otomatik duser. Web admin de mobil app de bu
 * endpoint'leri kullanir.
 */
class MusteriDakikaPaketController extends Controller
{
    protected MusteriDakikaPaketService $service;

    public function __construct(MusteriDakikaPaketService $service)
    {
        $this->service = $service;
    }

    /** POST /api/dakika-paketi/sat */
    public function sat(Request $request)
    {
        $request->validate([
            'salon_id'           => 'required|integer',
            'musteri_portfoy_id' => 'required|integer',
            'hizmet_id'          => 'required|integer',
            'toplam_dakika'      => 'required|integer|min:1',
            'satis_fiyati'       => 'nullable|numeric|min:0',
            'bitis_tarihi'       => 'nullable|date',
            'notlar'             => 'nullable|string|max:1000',
        ]);

        $portfoy = MusteriPortfoy::where('id', $request->musteri_portfoy_id)
            ->where('salon_id', $request->salon_id)
            ->first();
        if (!$portfoy) return response()->json(['hatali' => '1', 'mesaj' => 'Musteri bulunamadi'], 404);

        $paket = $this->service->paketSat($request->only([
            'salon_id', 'musteri_portfoy_id', 'hizmet_id',
            'toplam_dakika', 'satis_fiyati', 'bitis_tarihi', 'notlar',
        ]), Auth::id(), $this->personelId($request));

        return response()->json(['hatali' => '0', 'paket' => $this->paketFormat($paket->fresh(['hizmet']))]);
    }

    /** GET /api/dakika-paketi/musteri/{musteri_portfoy_id} */
    public function musteriListesi(Request $request, int $musteriPortfoyId)
    {
        $q = MusteriDakikaPaketi::with('hizmet')
            ->where('musteri_portfoy_id', $musteriPortfoyId);
        if ($request->filled('salon_id')) $q->where('salon_id', $request->salon_id);
        if ($request->filled('durum'))    $q->where('durum', $request->durum);

        $paketler = $q->orderByRaw("FIELD(durum,'aktif','bitti','iptal')")
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn($p) => $this->paketFormat($p));

        return response()->json(['hatali' => '0', 'paketler' => $paketler]);
    }

    /** GET /api/dakika-paketi/{id} */
    public function detay(int $id)
    {
        $paket = MusteriDakikaPaketi::with(['hizmet', 'musteri.users'])->findOrFail($id);
        $hareketler = MusteriDakikaPaketHareketi::where('musteri_dakika_paketi_id', $id)
            ->orderBy('tarih', 'desc')
            ->get();

        return response()->json([
            'hatali'     => '0',
            'paket'      => $this->paketFormat($paket),
            'musteri_adi'=> $paket->musteri->users->name ?? '',
            'hareketler' => $hareketler->map(fn($h) => [
                'id'         => $h->id,
                'tur'        => $h->tur,
                'dakika'     => (int) $h->dakika,
                'tarih'      => $h->tarih ? $h->tarih->format('Y-m-d H:i') : null,
                'aciklama'   => $h->aciklama,
                'randevu_id' => $h->randevu_id,
            ])->values(),
        ]);
    }

    /** POST /api/dakika-paketi/{id}/manuel-kullanim */
    public function manuelKullanim(Request $request, int $id)
    {
        $request->validate([
            'dakika'   => 'required|integer|min:1',
            'aciklama' => 'nullable|string|max:500',
        ]);
        try {
            $hareket = $this->service->manuelKullanim(
                $id, (int) $request->dakika, $request->aciklama,
                Auth::id(), $this->personelId($request)
            );
            $paket = MusteriDakikaPaketi::with('hizmet')->find($id);
            return response()->json([
                'hatali' => '0',
                'paket'  => $this->paketFormat($paket),
                'hareket_id' => $hareket->id,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['hatali' => '1', 'mesaj' => $e->getMessage()], 422);
        }
    }

    /** POST /api/dakika-paketi/{id}/duzeltme */
    public function duzeltme(Request $request, int $id)
    {
        $request->validate([
            'dakika'   => 'required|integer',
            'aciklama' => 'required|string|max:500',
        ]);
        try {
            $hareket = $this->service->duzeltme(
                $id, (int) $request->dakika, $request->aciklama,
                Auth::id(), $this->personelId($request)
            );
            $paket = MusteriDakikaPaketi::with('hizmet')->find($id);
            return response()->json([
                'hatali' => '0',
                'paket'  => $this->paketFormat($paket),
                'hareket_id' => $hareket->id,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['hatali' => '1', 'mesaj' => $e->getMessage()], 422);
        }
    }

    /** POST /api/dakika-paketi/{id}/iptal */
    public function iptal(Request $request, int $id)
    {
        $paket = $this->service->paketIptal($id, $request->aciklama, Auth::id(), $this->personelId($request));
        return response()->json([
            'hatali' => '0',
            'paket'  => $this->paketFormat($paket->fresh(['hizmet'])),
        ]);
    }

    /** POST /api/dakika-paketi/randevu-baglat
     *  Bir randevu_hizmet satirina dakika paketi bagla / sok.
     *  body: randevu_hizmet_id, paket_id (null = baglamayi kaldir), paket_dakika? */
    public function randevuBaglat(Request $request)
    {
        $request->validate([
            'randevu_hizmet_id' => 'required|integer',
            'paket_id'          => 'nullable|integer',
            'paket_dakika'      => 'nullable|integer|min:1',
        ]);

        $rh = RandevuHizmetler::find($request->randevu_hizmet_id);
        if (!$rh) return response()->json(['hatali' => '1', 'mesaj' => 'Randevu satiri bulunamadi'], 404);

        if ($request->paket_id) {
            $paket = MusteriDakikaPaketi::find($request->paket_id);
            if (!$paket || $paket->durum !== 'aktif') {
                return response()->json(['hatali' => '1', 'mesaj' => 'Paket aktif degil'], 422);
            }
            if ($paket->hizmet_id != $rh->hizmet_id) {
                return response()->json(['hatali' => '1', 'mesaj' => 'Paket bu hizmete ait degil'], 422);
            }
            $dakika = (int) ($request->paket_dakika ?? $rh->sure_dk ?? 0);
            if ($dakika <= 0) {
                return response()->json(['hatali' => '1', 'mesaj' => 'Gecerli dakika girilmeli'], 422);
            }
            if ($paket->kalan_dakika < $dakika) {
                return response()->json(['hatali' => '1', 'mesaj' => 'Yetersiz bakiye (kalan: ' . $paket->kalan_dakika . ' dk)'], 422);
            }
            $rh->musteri_dakika_paketi_id = $paket->id;
            $rh->paket_dakika = $dakika;
        } else {
            $rh->musteri_dakika_paketi_id = null;
            $rh->paket_dakika = null;
        }
        $rh->save();

        return response()->json(['hatali' => '0']);
    }

    /** GET /api/dakika-paketi/randevu-icin-uygun/{randevuId}
     *  Bir randevu icin musterinin paketi olan ve henuz paketten dusulmemis
     *  hizmet satirlarini liste dondurur. "Geldi" popup'unda kullanilir. */
    public function randevuIcinUygun(int $randevuId)
    {
        $liste = $this->service->randevuIcinUygunPaketler($randevuId);
        return response()->json(['hatali' => '0', 'liste' => $liste]);
    }

    /** POST /api/dakika-paketi/randevu-icin-kullanim
     *  Body: randevu_id, kullanimlar:[{rh_id, paket_id, dakika}, ...]
     *  Her satiri pakete bagla + randevu zaten geldi ise direkt dus. */
    public function randevuIcinKullanim(Request $request)
    {
        $request->validate([
            'randevu_id'   => 'required|integer',
            'kullanimlar' => 'required',
        ]);
        $kullanimlar = $request->input('kullanimlar', []);
        if (is_string($kullanimlar)) {
            $j = json_decode($kullanimlar, true);
            if (is_array($j)) $kullanimlar = $j;
        }
        if (!is_array($kullanimlar)) $kullanimlar = [];
        $sonuc = $this->service->randevuIcinKullanimKaydet(
            (int) $request->randevu_id,
            $kullanimlar,
            Auth::id(),
            $this->personelId($request)
        );
        return response()->json(['hatali' => '0', 'sonuc' => $sonuc]);
    }

    /** GET /api/dakika-paketi/musteri-uygun/{musteri_portfoy_id}/hizmet/{hizmet_id}
     *  Bu musteri + hizmet icin kullanilabilir paketleri dondurur. */
    public function musteriHizmetIcinUygun(int $musteriPortfoyId, int $hizmetId)
    {
        $paketler = MusteriDakikaPaketi::with('hizmet')
            ->where('musteri_portfoy_id', $musteriPortfoyId)
            ->where('hizmet_id', $hizmetId)
            ->where('durum', 'aktif')
            ->where(function ($q) {
                $q->whereNull('bitis_tarihi')->orWhere('bitis_tarihi', '>=', now()->toDateString());
            })
            ->orderBy('satis_tarihi')
            ->get()
            ->map(fn($p) => $this->paketFormat($p));

        return response()->json(['hatali' => '0', 'paketler' => $paketler]);
    }

    // --- yardimcilar ---

    protected function personelId(Request $request): ?int
    {
        $p = $request->input('personel_id');
        return $p ? (int) $p : null;
    }

    protected function paketFormat(MusteriDakikaPaketi $p): array
    {
        return [
            'id'                 => $p->id,
            'salon_id'           => $p->salon_id,
            'musteri_portfoy_id' => $p->musteri_portfoy_id,
            'hizmet_id'          => $p->hizmet_id,
            'hizmet_adi'         => $p->hizmet->hizmet_adi ?? '',
            'toplam_dakika'      => (int) $p->toplam_dakika,
            'kalan_dakika'       => (int) $p->kalan_dakika,
            'kullanilan_dakika'  => (int) $p->toplam_dakika - (int) $p->kalan_dakika,
            'satis_fiyati'       => (float) $p->satis_fiyati,
            'satis_tarihi'       => $p->satis_tarihi ? $p->satis_tarihi->format('Y-m-d') : null,
            'bitis_tarihi'       => $p->bitis_tarihi ? $p->bitis_tarihi->format('Y-m-d') : null,
            'durum'              => $p->durum,
            'notlar'             => $p->notlar,
        ];
    }
}
