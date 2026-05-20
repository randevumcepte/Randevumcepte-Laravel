# PROPOSED: Dashboard Karşılaştırma Endpoint'i

Bu dosya Laravel backend'ine eklenmesi önerilen yeni endpoint'i içerir.
**Henüz canlıya inmedi — kullanıcı onayı sonrası entegre edilecek.**

Mobil uygulama Dashboard'una eklenen periyot bazlı karşılaştırma kartlarını
(Bugün/Hafta/Ay/Yıl seçimine göre ciro, kâr-maliyet, top performer, saat yoğunluğu,
şube karşılaştırması) gerçek veri ile beslemek için bir API endpoint'i gerekiyor.

## 1. Eklenecek route

`routes/api.php` içine, `Route::group(['prefix'=> 'v1'], function () {` bloğuna
ekle (örnek olarak `/dashboard` route'unun yanına):

```php
Route::post('/dashboardKarsilastirma/{salonId}', 'ApiController@dashboardKarsilastirma');
Route::get('/dashboardKarsilastirma/{salonId}',  'ApiController@dashboardKarsilastirma');
```

## 2. Eklenecek metod (ApiController'a)

`app/Http/Controllers/ApiController.php` içine, mevcut `ozetsayfasi` metodunun
yakınına aşağıdaki metodu ekle:

```php
/**
 * Dashboard karşılaştırma — periyot bazlı (gunluk|haftalik|aylik|yillik) toplam ciro,
 * önceki periyot karşılaştırması, son 7 periyot serisi, top personel/hizmet/urun,
 * saat yoğunluğu, şube performansı, kâr-maliyet özeti.
 *
 * GET/POST /api/v1/dashboardKarsilastirma/{salonId}?period=aylik
 */
public function dashboardKarsilastirma(Request $request, $salonId)
{
    $period = $request->query('period', $request->input('period', 'aylik'));
    if (!in_array($period, ['gunluk', 'haftalik', 'aylik', 'yillik'])) {
        $period = 'aylik';
    }

    [$curStart, $curEnd, $prevStart, $prevEnd] = $this->_periodRanges($period);

    // --- Ciro (mevcut periyot) — tahsilat line item'larından topla ---
    $currentCiro = $this->_ciroFor($salonId, $curStart, $curEnd);
    $previousCiro = $this->_ciroFor($salonId, $prevStart, $prevEnd);

    // --- Maliyet (Masraflar) ---
    $maliyet = (float) Masraflar::where('salon_id', $salonId)
        ->whereBetween('tarih', [$curStart->toDateString(), $curEnd->toDateString()])
        ->sum('tutar');

    $kar = $currentCiro - $maliyet;

    // --- Alacak (planlanan_odeme_tarihi periyot içinde) ---
    $alacak = (float) Alacaklar::where('salon_id', $salonId)
        ->whereBetween('planlanan_odeme_tarihi', [$curStart->toDateString(), $curEnd->toDateString()])
        ->sum('tutar');

    // --- Son 7 periyot serisi ---
    $series = [];
    for ($i = 6; $i >= 0; $i--) {
        [$s, $e] = $this->_offsetRange($period, $i);
        $series[] = $this->_ciroFor($salonId, $s, $e);
    }

    // --- Top personel (hizmet+urun+paket toplam tutar) ---
    $topPersonel = DB::table('tahsilat_hizmetler')
        ->join('adisyon_hizmetler', 'adisyon_hizmetler.id', '=', 'tahsilat_hizmetler.adisyon_hizmet_id')
        ->join('tahsilatlar', 'tahsilatlar.id', '=', 'tahsilat_hizmetler.tahsilat_id')
        ->join('salon_personelleri as sp', 'sp.id', '=', 'adisyon_hizmetler.personel_id')
        ->where('tahsilatlar.salon_id', $salonId)
        ->whereBetween('tahsilatlar.odeme_tarihi', [$curStart, $curEnd])
        ->groupBy('sp.id', 'sp.adi')
        ->select('sp.adi as name', DB::raw('SUM(tahsilat_hizmetler.tutar) as value'))
        ->orderByDesc('value')
        ->first();

    // --- Top hizmet ---
    $topHizmet = DB::table('adisyon_hizmetler')
        ->join('hizmetler', 'hizmetler.id', '=', 'adisyon_hizmetler.hizmet_id')
        ->join('adisyonlar', 'adisyonlar.id', '=', 'adisyon_hizmetler.adisyon_id')
        ->where('adisyonlar.salon_id', $salonId)
        ->whereBetween('adisyonlar.created_at', [$curStart, $curEnd])
        ->groupBy('hizmetler.id', 'hizmetler.hizmet_adi')
        ->select('hizmetler.hizmet_adi as name', DB::raw('COUNT(*) as count'))
        ->orderByDesc('count')
        ->first();

    // --- Top ürün ---
    $topUrun = DB::table('adisyon_urunler')
        ->join('urunler', 'urunler.id', '=', 'adisyon_urunler.urun_id')
        ->join('adisyonlar', 'adisyonlar.id', '=', 'adisyon_urunler.adisyon_id')
        ->where('adisyonlar.salon_id', $salonId)
        ->whereBetween('adisyonlar.created_at', [$curStart, $curEnd])
        ->groupBy('urunler.id', 'urunler.urun_adi')
        ->select('urunler.urun_adi as name', DB::raw('SUM(adisyon_urunler.adet) as count'))
        ->orderByDesc('count')
        ->first();

    // --- Saat yoğunluğu — randevular tablosu, periyot içinde, HOUR(saat) ile group ---
    $hourlyRaw = DB::table('randevular')
        ->where('salon_id', $salonId)
        ->whereBetween('tarih', [$curStart->toDateString(), $curEnd->toDateString()])
        ->select(DB::raw('HOUR(saat) as h'), DB::raw('COUNT(*) as cnt'))
        ->groupBy('h')
        ->pluck('cnt', 'h')
        ->toArray();
    $maxHourly = max(array_merge(array_values($hourlyRaw), [1]));
    $hourly = [];
    for ($h = 0; $h < 24; $h++) {
        $hourly[] = isset($hourlyRaw[$h]) ? round($hourlyRaw[$h] / $maxHourly, 2) : 0.0;
    }

    // --- Şubeler (kullanıcı çoklu şube yetkilisi ise) ---
    // Burada salon_id verilen tek şube. Multi-branch kullanıcı için
    // ayrı endpoint gerekir; istersen sonra ekleriz.
    $subeler = [];

    return response()->json([
        'period' => $period,
        'current' => [
            'label' => $this->_periodLabel($period, true),
            'value' => $currentCiro,
        ],
        'previous' => [
            'label' => $this->_periodLabel($period, false),
            'value' => $previousCiro,
        ],
        'series' => $series,
        'kasa' => $currentCiro - $maliyet,
        'maliyet' => $maliyet,
        'kar' => $kar,
        'alacak' => $alacak,
        'topPersonel' => $topPersonel ? [
            'name' => $topPersonel->name,
            'value' => (float) $topPersonel->value,
        ] : null,
        'topHizmet' => $topHizmet ? [
            'name' => $topHizmet->name,
            'count' => (int) $topHizmet->count,
        ] : null,
        'topUrun' => $topUrun ? [
            'name' => $topUrun->name,
            'count' => (int) $topUrun->count,
        ] : null,
        'hourlyDensity' => $hourly,
        'subeler' => $subeler,
    ]);
}

// --------- Yardımcılar ---------

private function _ciroFor($salonId, $start, $end)
{
    $sumHizmet = (float) DB::table('tahsilat_hizmetler')
        ->join('tahsilatlar', 'tahsilatlar.id', '=', 'tahsilat_hizmetler.tahsilat_id')
        ->where('tahsilatlar.salon_id', $salonId)
        ->whereBetween('tahsilatlar.odeme_tarihi', [$start, $end])
        ->sum('tahsilat_hizmetler.tutar');
    $sumUrun = (float) DB::table('tahsilat_urunler')
        ->join('tahsilatlar', 'tahsilatlar.id', '=', 'tahsilat_urunler.tahsilat_id')
        ->where('tahsilatlar.salon_id', $salonId)
        ->whereBetween('tahsilatlar.odeme_tarihi', [$start, $end])
        ->sum('tahsilat_urunler.tutar');
    $sumPaket = (float) DB::table('tahsilat_paketler')
        ->join('tahsilatlar', 'tahsilatlar.id', '=', 'tahsilat_paketler.tahsilat_id')
        ->where('tahsilatlar.salon_id', $salonId)
        ->whereBetween('tahsilatlar.odeme_tarihi', [$start, $end])
        ->sum('tahsilat_paketler.tutar');
    return $sumHizmet + $sumUrun + $sumPaket;
}

private function _periodRanges($period)
{
    $now = \Carbon\Carbon::now();
    switch ($period) {
        case 'gunluk':
            return [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
            ];
        case 'haftalik':
            return [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek(),
            ];
        case 'aylik':
            return [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ];
        case 'yillik':
            return [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                $now->copy()->subYear()->startOfYear(),
                $now->copy()->subYear()->endOfYear(),
            ];
    }
    return [
        $now->copy()->startOfDay(),
        $now->copy()->endOfDay(),
        $now->copy()->subDay()->startOfDay(),
        $now->copy()->subDay()->endOfDay(),
    ];
}

private function _offsetRange($period, $stepsAgo)
{
    $now = \Carbon\Carbon::now();
    switch ($period) {
        case 'gunluk':
            $d = $now->copy()->subDays($stepsAgo);
            return [$d->copy()->startOfDay(), $d->copy()->endOfDay()];
        case 'haftalik':
            $d = $now->copy()->subWeeks($stepsAgo);
            return [$d->copy()->startOfWeek(), $d->copy()->endOfWeek()];
        case 'aylik':
            $d = $now->copy()->subMonths($stepsAgo);
            return [$d->copy()->startOfMonth(), $d->copy()->endOfMonth()];
        case 'yillik':
            $d = $now->copy()->subYears($stepsAgo);
            return [$d->copy()->startOfYear(), $d->copy()->endOfYear()];
    }
    return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
}

private function _periodLabel($period, $current)
{
    $map = [
        'gunluk'   => $current ? 'Bugün'    : 'Geçen Aynı Gün',
        'haftalik' => $current ? 'Bu Hafta' : 'Geçen Hafta',
        'aylik'    => $current ? 'Bu Ay'    : 'Geçen Ay',
        'yillik'   => $current ? 'Bu Yıl'   : 'Geçen Yıl',
    ];
    return $map[$period] ?? '';
}
```

## 3. Deploy adımları

1. Yukarıdaki **route'ları** `routes/api.php`'a ekle (mevcut `Route::group(['prefix'=> 'v1'])` bloğunun içine).
2. Yukarıdaki **metodu + 4 yardımcıyı** `ApiController.php`'a ekle (örneğin `ozetsayfasi` metodunun hemen altına).
3. `composer require nesbot/carbon` zaten var (Laravel default), import gerekmez.
4. Deploy: `git add -A && git commit -m "Dashboard karşılaştırma endpoint'i" && git push` ardından `./deploy.sh`.
5. Test: `curl -X GET "https://app.randevumcepte.com.tr/api/v1/dashboardKarsilastirma/SALON_ID?period=haftalik"`

## 4. Tablo varsayımları

Endpoint şu tabloları kullanır — yapı farklıysa SQL'leri ayarla:

- `tahsilatlar` → `salon_id`, `odeme_tarihi`
- `tahsilat_hizmetler` / `tahsilat_urunler` / `tahsilat_paketler` → `tahsilat_id`, `tutar`
- `adisyon_hizmetler` → `adisyon_id`, `personel_id`, `hizmet_id`
- `adisyon_urunler` → `adisyon_id`, `urun_id`, `adet`
- `adisyonlar` → `salon_id`, `created_at`
- `salon_personelleri` → `id`, `adi`
- `hizmetler` → `id`, `hizmet_adi`
- `urunler` → `id`, `urun_adi`
- `randevular` → `salon_id`, `tarih`, `saat`
- `masraflar` → `salon_id`, `tarih`, `tutar`
- `alacaklar` → `salon_id`, `planlanan_odeme_tarihi`, `tutar`

## 5. Mobil entegrasyon (B adımı — endpoint canlıya çıkınca)

Flutter `Backend/backend.dart`'a şu fonksiyonu ekleriz:

```dart
Future<Map<String, dynamic>> dashboardKarsilastirma(String salonId, String period) async {
  final res = await http.get(
    Uri.parse("https://app.randevumcepte.com.tr/api/v1/dashboardKarsilastirma/$salonId?period=$period"),
  );
  if (res.statusCode == 200) return jsonDecode(res.body);
  return {};
}
```

Sonra `home_screen.dart`'taki mock `_comparisonData()` fonksiyonu yerine `FutureBuilder` ile bu API'yi çağırırız, period chip değişince otomatik fetch.

## 6. Performans notu

Endpoint birden fazla sum query yapıyor. Tahsilat hacmi büyük salonlarda yavaşlayabilir. İleride şu optimizasyonlar yapılabilir:
- `cache()` kullanarak 5 dakikalık cache (period bazlı key)
- Series query'leri tek SQL'de UNION ile alınabilir
- `tahsilatlar.toplam_tutar` kolonu varsa onu kullan (line item join'leri yapma)

## 7. Test çıktısı örneği

```json
{
  "period": "haftalik",
  "current": { "label": "Bu Hafta", "value": 9800.0 },
  "previous": { "label": "Geçen Hafta", "value": 8500.0 },
  "series": [6800, 7400, 7900, 8500, 8200, 9100, 9800],
  "kasa": 7350.0,
  "maliyet": 2450.0,
  "kar": 7350.0,
  "alacak": 1200.0,
  "topPersonel": { "name": "Ayşe Yılmaz", "value": 4250.0 },
  "topHizmet": { "name": "Saç Bakımı", "count": 47 },
  "topUrun": { "name": "Şampuan Premium", "count": 23 },
  "hourlyDensity": [0,0,0,...,0.85,0.95,...,0],
  "subeler": []
}
```
