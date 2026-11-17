<?php

namespace App\Http\Controllers\SistemYonetim;

use App\Http\Controllers\Controller;
use App\Services\TurkceIsimCinsiyet;
use App\SistemYonetim\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Sistem Yonetimi > İsim Sözlüğü (santral hitabi "Ferdi Bey" / "Ayşe Hanım").
 * Asistan sozlukte olmayan bir adla karsilasinca ad buraya "karar bekliyor" olarak duser (gorulme sayilir);
 * Erkek / Kadın / Hitapsız secilince bir sonraki aramada kullanilir. Elle ad eklenebilir, sozlugu de ezer.
 */
class IsimSozlukController extends Controller
{
    const ETIKET = [1 => 'Erkek', 0 => 'Kadın', 2 => 'Hitapsız'];

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

    private function yetkili(): bool
    {
        return in_array($this->rol(), ['super_admin', 'yonetici'], true);
    }

    public function index(Request $request)
    {
        TurkceIsimCinsiyet::semaGaranti();
        $durum = in_array($request->get('durum'), ['bekleyen', 'karar', 'hepsi'], true) ? $request->get('durum') : 'bekleyen';
        $q = trim((string) $request->get('q', ''));

        $sorgu = DB::table(TurkceIsimCinsiyet::TABLO);
        if ($durum === 'bekleyen') $sorgu->whereNull('cinsiyet');
        if ($durum === 'karar') $sorgu->whereNotNull('cinsiyet');
        if ($q !== '') $sorgu->where('anahtar', 'like', '%' . TurkceIsimCinsiyet::anahtar($q) . '%');
        $satirlar = $sorgu->orderByRaw('cinsiyet IS NOT NULL')->orderBy('gorulme', 'desc')->orderBy('son_gorulme', 'desc')->limit(200)->get();
        foreach ($satirlar as $s) { // PDO bazi kurulumlarda sayiyi metin dondurur -> gorunumde === karsilastirmasi
            $s->cinsiyet = $s->cinsiyet === null ? null : (int) $s->cinsiyet;
            $s->gorulme = (int) $s->gorulme;
        }

        // Oneri: sistemde ayni adli ve cinsiyeti girilmis musteri sayilari (yalniz bekleyenler; ad basina 1 gun onbellek)
        $oneriler = [];
        foreach ($satirlar as $s) {
            if ($s->cinsiyet === null && count($oneriler) < 60) $oneriler[$s->anahtar] = TurkceIsimCinsiyet::istatistik($s->ad);
        }

        $sayim = DB::table(TurkceIsimCinsiyet::TABLO)
            ->selectRaw('SUM(cinsiyet IS NULL) AS bekleyen, SUM(cinsiyet IS NOT NULL) AS karar')->first();

        return view('sistemyonetim.v2.isim-sozluk', [
            'title'     => 'İsim Sözlüğü',
            'aktifMenu' => 'isimsozluk',
            'satirlar'  => $satirlar,
            'oneriler'  => $oneriler,
            'durum'     => $durum,
            'q'         => $q,
            'bekleyen'  => (int) ($sayim->bekleyen ?? 0),
            'karar'     => (int) ($sayim->karar ?? 0),
            'etiket'    => self::ETIKET,
            'yetkili'   => $this->yetkili(),
        ]);
    }

    /** Karar ver / elle ekle. cinsiyet: 1 Erkek, 0 Kadın, 2 Hitapsız, 'bekle' = karari geri al. */
    public function kaydet(Request $request)
    {
        if (!$this->yetkili()) abort(403, 'Bu işlem için yetkiniz yok.');
        TurkceIsimCinsiyet::semaGaranti();

        $ad = trim((string) $request->input('ad'));
        $anahtar = TurkceIsimCinsiyet::anahtar($ad);
        if (!preg_match('/^[a-z]{2,60}$/', $anahtar)) {
            return redirect()->back()->with('hata', 'Geçerli bir ad girin (yalnız harf, tek kelime).');
        }
        $c = (string) $request->input('cinsiyet');
        $cinsiyet = in_array($c, ['0', '1', '2'], true) ? (int) $c : null;

        $eski = DB::table(TurkceIsimCinsiyet::TABLO)->where('anahtar', $anahtar)->first();
        $veri = ['cinsiyet' => $cinsiyet, 'karar_veren' => optional(Auth::guard('sistemyonetim')->user())->name, 'updated_at' => now()];
        if ($eski) {
            DB::table(TurkceIsimCinsiyet::TABLO)->where('id', $eski->id)->update($veri);
            $id = $eski->id; $gorunen = $eski->ad;
        } else {
            $gorunen = mb_strtoupper(strtr(mb_substr($ad, 0, 1, 'UTF-8'), ['i' => 'İ', 'ı' => 'I']), 'UTF-8')
                . mb_strtolower(strtr(mb_substr(explode(' ', $ad)[0], 1, null, 'UTF-8'), ['I' => 'ı', 'İ' => 'i']), 'UTF-8');
            $id = DB::table(TurkceIsimCinsiyet::TABLO)->insertGetId($veri + ['anahtar' => $anahtar, 'ad' => $gorunen, 'gorulme' => 0, 'created_at' => now()]);
        }
        TurkceIsimCinsiyet::onbellekSil();

        $sonuc = $cinsiyet === null ? 'karar bekliyor' : self::ETIKET[$cinsiyet];
        Audit::log('isim_sozluk_kaydet', 'isim', $id, $gorunen, "{$gorunen}: {$sonuc}",
            ['eski' => $eski ? $eski->cinsiyet : 'yok', 'yeni' => $cinsiyet]);

        return redirect()->back()->with('basari', "{$gorunen}: {$sonuc}");
    }
}
