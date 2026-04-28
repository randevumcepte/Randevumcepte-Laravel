<?php

namespace App\Http\Controllers\SistemYonetim;

use App\Http\Controllers\Controller;
use App\Salonlar;
use App\Iller;
use App\SistemYonetim\Audit;
use App\SistemYonetim\Duyuru;
use App\SistemYonetim\DuyuruOkundu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DuyuruController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sistemyonetim');
    }

    private function user() { return Auth::guard('sistemyonetim')->user(); }
    private function rol()
    {
        $u = $this->user();
        if (!empty($u->rol)) return $u->rol;
        return $u->admin == 1 ? 'super_admin' : 'destek';
    }
    private function gerektir($izinler)
    {
        if (!in_array($this->rol(), $izinler, true)) abort(403);
    }

    public function index()
    {
        $duyurular = Duyuru::orderBy('id', 'desc')->paginate(25);
        return view('sistemyonetim.v2.duyurular', [
            'title' => 'Duyurular',
            'aktifMenu' => 'duyuru',
            'duyurular' => $duyurular,
        ]);
    }

    public function yeni()
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $iller = Iller::orderBy('il_adi')->get(['id', 'il_adi']);
        return view('sistemyonetim.v2.duyuru-form', [
            'title' => 'Yeni Duyuru',
            'aktifMenu' => 'duyuru',
            'duyuru' => null,
            'iller' => $iller,
        ]);
    }

    public function kaydet(Request $request)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $this->validate($request, [
            'baslik' => 'required|min:2|max:200',
            'icerik' => 'required|min:2',
            'tip'    => 'required|in:bilgi,uyari,onemli,bakim,kampanya',
            'hedef_tipi' => 'required|in:hepsi,secili,il',
        ]);

        $hedefIds = [];
        if ($request->hedef_tipi === 'secili' && $request->salon_ids) {
            $hedefIds = array_map('intval', array_filter((array) $request->salon_ids));
        }
        if ($request->hedef_tipi === 'il' && $request->il_ids) {
            $hedefIds = array_map('intval', array_filter((array) $request->il_ids));
        }

        $d = Duyuru::create([
            'baslik' => $request->baslik,
            'icerik' => $request->icerik,
            'tip'    => $request->tip,
            'hedef_tipi' => $request->hedef_tipi,
            'hedef_ids'  => $hedefIds ? json_encode($hedefIds) : null,
            'baslangic_tarihi' => $request->baslangic_tarihi ?: null,
            'bitis_tarihi'     => $request->bitis_tarihi ?: null,
            'aktif'   => $request->aktif ? 1 : 0,
            'sticky'  => $request->sticky ? 1 : 0,
            'cta_metin' => $request->cta_metin,
            'cta_link'  => $request->cta_link,
            'olusturan_user_id'   => $this->user()->id,
            'olusturan_user_name' => $this->user()->name,
        ]);

        Audit::log('duyuru_olustur', 'duyuru', $d->id, $d->baslik, "Hedef: {$d->hedef_tipi}");

        return redirect('/sistemyonetim/v2/duyuru')->with('basari', 'Duyuru oluşturuldu.');
    }

    public function duzenle($id)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $duyuru = Duyuru::findOrFail($id);
        $iller = Iller::orderBy('il_adi')->get(['id', 'il_adi']);
        $secilenSalonlar = collect();
        if ($duyuru->hedef_tipi === 'secili') {
            $secilenSalonlar = Salonlar::whereIn('id', $duyuru->hedefIdsArray())->get(['id', 'salon_adi']);
        }
        return view('sistemyonetim.v2.duyuru-form', [
            'title' => $duyuru->baslik,
            'aktifMenu' => 'duyuru',
            'duyuru' => $duyuru,
            'iller' => $iller,
            'secilenSalonlar' => $secilenSalonlar,
        ]);
    }

    public function guncelle(Request $request, $id)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $duyuru = Duyuru::findOrFail($id);
        $this->validate($request, [
            'baslik' => 'required|min:2|max:200',
            'icerik' => 'required|min:2',
            'tip'    => 'required|in:bilgi,uyari,onemli,bakim,kampanya',
            'hedef_tipi' => 'required|in:hepsi,secili,il',
        ]);

        $hedefIds = [];
        if ($request->hedef_tipi === 'secili' && $request->salon_ids) {
            $hedefIds = array_map('intval', array_filter((array) $request->salon_ids));
        }
        if ($request->hedef_tipi === 'il' && $request->il_ids) {
            $hedefIds = array_map('intval', array_filter((array) $request->il_ids));
        }

        $duyuru->fill([
            'baslik' => $request->baslik,
            'icerik' => $request->icerik,
            'tip'    => $request->tip,
            'hedef_tipi' => $request->hedef_tipi,
            'hedef_ids'  => $hedefIds ? json_encode($hedefIds) : null,
            'baslangic_tarihi' => $request->baslangic_tarihi ?: null,
            'bitis_tarihi'     => $request->bitis_tarihi ?: null,
            'aktif'   => $request->aktif ? 1 : 0,
            'sticky'  => $request->sticky ? 1 : 0,
            'cta_metin' => $request->cta_metin,
            'cta_link'  => $request->cta_link,
        ])->save();

        Audit::log('duyuru_guncelle', 'duyuru', $duyuru->id, $duyuru->baslik);

        return redirect('/sistemyonetim/v2/duyuru')->with('basari', 'Duyuru güncellendi.');
    }

    public function sil($id)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $duyuru = Duyuru::findOrFail($id);
        Audit::log('duyuru_sil', 'duyuru', $duyuru->id, $duyuru->baslik);
        $duyuru->delete();
        return redirect()->back()->with('basari', 'Duyuru silindi.');
    }

    public function detay($id)
    {
        $duyuru = Duyuru::findOrFail($id);
        $okundu = DB::table('sistemyonetim_duyuru_okundu')
            ->leftJoin('isletmeyetkilileri', 'sistemyonetim_duyuru_okundu.user_id', '=', 'isletmeyetkilileri.id')
            ->leftJoin('salonlar', 'sistemyonetim_duyuru_okundu.salon_id', '=', 'salonlar.id')
            ->where('duyuru_id', $id)
            ->select('sistemyonetim_duyuru_okundu.*', 'isletmeyetkilileri.name as yetkili_adi', 'salonlar.salon_adi')
            ->orderBy('okundu_tarihi', 'desc')
            ->get();

        $hedefSayisi = 0;
        if ($duyuru->hedef_tipi === 'hepsi') $hedefSayisi = Salonlar::count();
        elseif ($duyuru->hedef_tipi === 'secili') $hedefSayisi = count($duyuru->hedefIdsArray());
        elseif ($duyuru->hedef_tipi === 'il') $hedefSayisi = Salonlar::whereIn('il_id', $duyuru->hedefIdsArray())->count();

        return view('sistemyonetim.v2.duyuru-detay', [
            'title' => $duyuru->baslik,
            'aktifMenu' => 'duyuru',
            'duyuru' => $duyuru,
            'okundu' => $okundu,
            'hedefSayisi' => $hedefSayisi,
        ]);
    }
}
