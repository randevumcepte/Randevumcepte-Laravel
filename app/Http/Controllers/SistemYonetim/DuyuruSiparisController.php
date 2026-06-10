<?php

namespace App\Http\Controllers\SistemYonetim;

use App\Http\Controllers\Controller;
use App\SmsPaketSiparisi;
use App\Salonlar;
use App\SistemYonetim\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DuyuruSiparisController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sistemyonetim');
    }

    private function user() { return Auth::guard('sistemyonetim')->user(); }

    public function index()
    {
        $siparisler = SmsPaketSiparisi::orderBy('id', 'desc')->paginate(40);

        // Salon adlarını topluca çek
        $salonIds = $siparisler->pluck('salon_id')->unique()->filter()->all();
        $salonlar = Salonlar::whereIn('id', $salonIds)->pluck('salon_adi', 'id');

        // Bekleyen yükleme sayısı (ödendi ama yüklenmedi)
        $bekleyen = SmsPaketSiparisi::where('durum', 1)->where('yukleme_durumu', 0)->count();

        return view('sistemyonetim.v2.duyuru-paketi-siparisleri', [
            'title' => 'Duyuru Paketi Siparişleri',
            'aktifMenu' => 'duyurusiparis',
            'siparisler' => $siparisler,
            'salonlar' => $salonlar,
            'bekleyen' => $bekleyen,
        ]);
    }

    // Manuel SMS yüklemesi yapıldı → işaretle
    public function yuklendi($id)
    {
        $siparis = SmsPaketSiparisi::findOrFail($id);
        $siparis->yukleme_durumu = 1;
        $siparis->yukleme_tarihi = date('Y-m-d H:i:s');
        $siparis->yukleyen = $this->user()->name ?? '';
        $siparis->save();

        Audit::log('duyuru_paketi_yuklendi', 'sms_paket_siparis', $siparis->id, $siparis->sms_adet.' SMS', 'Salon #'.$siparis->salon_id);

        return redirect()->back()->with('basari', 'Sipariş "yüklendi" olarak işaretlendi.');
    }
}
