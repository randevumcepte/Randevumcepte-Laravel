<?php

namespace App\Http\Controllers\SistemYonetim;

use App\Http\Controllers\Controller;
use App\SMSPaketleri;
use App\SistemYonetim\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsPaketController extends Controller
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

    // Salon panelinde kart rengi olarak kullanilan class secenekleri (pricing-table-{class} / btn-{class})
    public static function classSecenekleri()
    {
        return ['primary', 'success', 'info', 'warning', 'danger'];
    }

    public function index()
    {
        $paketler = SMSPaketleri::orderBy('sms_adet')->get();
        return view('sistemyonetim.v2.sms-paketleri', [
            'title' => 'İnteraktif Duyuru Paketleri',
            'aktifMenu' => 'smspaket',
            'paketler' => $paketler,
        ]);
    }

    public function yeni()
    {
        $this->gerektir(['super_admin', 'yonetici']);
        return view('sistemyonetim.v2.sms-paket-form', [
            'title' => 'Yeni Paket',
            'aktifMenu' => 'smspaket',
            'paket' => null,
            'classSecenekleri' => self::classSecenekleri(),
        ]);
    }

    public function kaydet(Request $request)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $this->validate($request, [
            'sms_adet'   => 'required|integer|min:1',
            'ucret'      => 'required|numeric|min:0',
            'class'      => 'nullable|string|max:200',
            'paket_adi'  => 'nullable|string|max:255',
            'alt_baslik' => 'nullable|string|max:255',
        ]);

        $p = SMSPaketleri::create([
            'sms_adet'   => (int) $request->sms_adet,
            'ucret'      => (float) $request->ucret,
            'class'      => $request->class ?: 'primary',
            'paket_adi'  => $request->paket_adi ?: null,
            'alt_baslik' => $request->alt_baslik ?: null,
        ]);

        Audit::log('sms_paket_olustur', 'sms_paket', $p->id, $p->sms_adet.' SMS', $p->ucret.' TL');

        return redirect('/sistemyonetim/v2/sms-paket')->with('basari', 'Paket oluşturuldu.');
    }

    public function duzenle($id)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $paket = SMSPaketleri::findOrFail($id);
        return view('sistemyonetim.v2.sms-paket-form', [
            'title' => $paket->sms_adet.' SMS — Düzenle',
            'aktifMenu' => 'smspaket',
            'paket' => $paket,
            'classSecenekleri' => self::classSecenekleri(),
        ]);
    }

    public function guncelle(Request $request, $id)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $paket = SMSPaketleri::findOrFail($id);
        $this->validate($request, [
            'sms_adet'   => 'required|integer|min:1',
            'ucret'      => 'required|numeric|min:0',
            'class'      => 'nullable|string|max:200',
            'paket_adi'  => 'nullable|string|max:255',
            'alt_baslik' => 'nullable|string|max:255',
        ]);

        $paket->fill([
            'sms_adet'   => (int) $request->sms_adet,
            'ucret'      => (float) $request->ucret,
            'class'      => $request->class ?: 'primary',
            'paket_adi'  => $request->paket_adi ?: null,
            'alt_baslik' => $request->alt_baslik ?: null,
        ])->save();

        Audit::log('sms_paket_guncelle', 'sms_paket', $paket->id, $paket->sms_adet.' SMS', $paket->ucret.' TL');

        return redirect('/sistemyonetim/v2/sms-paket')->with('basari', 'Paket güncellendi.');
    }

    public function sil($id)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $paket = SMSPaketleri::findOrFail($id);
        Audit::log('sms_paket_sil', 'sms_paket', $paket->id, $paket->sms_adet.' SMS');
        $paket->delete();
        return redirect()->back()->with('basari', 'Paket silindi.');
    }
}
