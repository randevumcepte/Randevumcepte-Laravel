<?php

namespace App\Http\Controllers;

use App\SistemYonetim\DuyuruOkundu;
use App\SistemYonetim\DestekTalebi;
use App\SistemYonetim\DestekMesaji;
use App\Salonlar;
use App\IsletmeYetkilileri;
use App\Personeller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalonDestekController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:isletmeyonetim');
    }

    private function user() { return Auth::guard('isletmeyonetim')->user(); }

    private function aktifSalonId()
    {
        $u = $this->user();
        if (!$u) return null;
        $personel = Personeller::where('yetkili_id', $u->id)->first();
        return $personel ? $personel->salon_id : null;
    }

    public function duyuruOkundu($id, Request $request)
    {
        $u = $this->user();
        $salonId = $this->aktifSalonId();
        try {
            DuyuruOkundu::firstOrCreate(
                ['duyuru_id' => $id, 'user_id' => $u->id],
                ['salon_id' => $salonId, 'okundu_tarihi' => date('Y-m-d H:i:s')]
            );
        } catch (\Exception $e) {}
        return response()->json(['ok' => 1]);
    }

    public function destekListesi()
    {
        $salonId = $this->aktifSalonId();
        $u = $this->user();
        if (!$salonId) return redirect('/isletmeyonetim');
        $salon = Salonlar::find($salonId);

        $ticketlar = DestekTalebi::where('salon_id', $salonId)
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('isletmeadmin.destek-listesi', [
            'sayfa_baslik' => 'Destek Talepleri',
            'pageindex'    => 200,
            'isletme'      => $salon,
            'ticketlar'    => $ticketlar,
            'kalan_uyelik_suresi' => 999, // basit gosterim icin
        ]);
    }

    public function destekYeniForm()
    {
        $salonId = $this->aktifSalonId();
        if (!$salonId) return redirect('/isletmeyonetim');
        $salon = Salonlar::find($salonId);
        return view('isletmeadmin.destek-yeni', [
            'sayfa_baslik' => 'Yeni Destek Talebi',
            'pageindex'    => 201,
            'isletme'      => $salon,
            'kalan_uyelik_suresi' => 999,
        ]);
    }

    public function destekKaydet(Request $request)
    {
        $u = $this->user();
        $salonId = $this->aktifSalonId();
        if (!$salonId) return redirect('/isletmeyonetim');

        $this->validate($request, [
            'konu'    => 'required|min:2|max:250',
            'kategori'=> 'required|in:teknik,odeme,egitim,ozellik,sikayet,diger',
            'aciklama'=> 'required|min:5',
        ]);

        $salonAdi = Salonlar::where('id', $salonId)->value('salon_adi');
        $numara = 'T-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $ticket = DestekTalebi::create([
            'numara'    => $numara,
            'salon_id'  => $salonId,
            'salon_adi' => $salonAdi,
            'iletisim_ad'      => $u->name,
            'iletisim_email'   => $u->email,
            'iletisim_telefon' => $u->cep_telefon ?? null,
            'konu'      => $request->konu,
            'aciklama'  => $request->aciklama,
            'kategori'  => $request->kategori,
            'oncelik'   => $request->oncelik ?: 'orta',
            'durum'     => 'acik',
            'olusturan_user_id'   => null,
            'olusturan_user_name' => $u->name . ' (Salon)',
        ]);

        DestekMesaji::create([
            'ticket_id' => $ticket->id,
            'user_id'   => null,
            'user_name' => $u->name,
            'user_tipi' => 'salon',
            'mesaj'     => $request->aciklama,
            'ic_not'    => 0,
        ]);

        return redirect('/isletmeyonetim/destek/' . $ticket->id)->with('basari', 'Talebiniz alındı. En kısa sürede dönüş yapacağız.');
    }

    public function destekDetay($id)
    {
        $salonId = $this->aktifSalonId();
        if (!$salonId) return redirect('/isletmeyonetim');
        $salon = Salonlar::find($salonId);

        $ticket = DestekTalebi::where('id', $id)->where('salon_id', $salonId)->firstOrFail();
        $mesajlar = DestekMesaji::where('ticket_id', $id)->where('ic_not', 0)->orderBy('id', 'asc')->get();

        return view('isletmeadmin.destek-detay', [
            'sayfa_baslik' => '#' . $ticket->numara,
            'pageindex'    => 200,
            'isletme'      => $salon,
            'ticket'       => $ticket,
            'mesajlar'     => $mesajlar,
            'kalan_uyelik_suresi' => 999,
        ]);
    }

    public function destekYanit(Request $request, $id)
    {
        $u = $this->user();
        $salonId = $this->aktifSalonId();
        $ticket = DestekTalebi::where('id', $id)->where('salon_id', $salonId)->firstOrFail();

        $this->validate($request, ['mesaj' => 'required|min:1']);

        DestekMesaji::create([
            'ticket_id' => $ticket->id,
            'user_id'   => null,
            'user_name' => $u->name,
            'user_tipi' => 'salon',
            'mesaj'     => $request->mesaj,
            'ic_not'    => 0,
        ]);

        if ($ticket->durum === 'cozumlendi' || $ticket->durum === 'kapali') {
            $ticket->durum = 'islemde';
            $ticket->save();
        }

        return redirect()->back()->with('basari', 'Mesajınız iletildi.');
    }
}
