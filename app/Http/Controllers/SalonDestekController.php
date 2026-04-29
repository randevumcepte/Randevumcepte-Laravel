<?php

namespace App\Http\Controllers;

use App\SistemYonetim\DuyuruOkundu;
use App\SistemYonetim\DestekTalebi;
use App\SistemYonetim\DestekMesaji;
use App\Salonlar;
use App\IsletmeYetkilileri;
use App\Personeller;
use App\Bildirimler;
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

    private function aktifSalonId(Request $request = null)
    {
        $u = $this->user();
        if (!$u) return null;
        // Eger ?sube=X URL parametresi varsa onu kullan
        if ($request && $request->has('sube')) {
            $sube = (int) $request->get('sube');
            $sahip = Personeller::where('yetkili_id', $u->id)->where('salon_id', $sube)->exists();
            if ($sahip) return $sube;
        }
        $personel = Personeller::where('yetkili_id', $u->id)->first();
        return $personel ? $personel->salon_id : null;
    }

    /**
     * isletmeyonetim layoutu (layout/layout_isletmeadmin.blade.php) tarafindan
     * beklenen ortak degiskenler. Eksigi exception atiyor.
     */
    private function layoutVerisi($salon)
    {
        $u = $this->user();
        // Yetkili olunan isletmeler — Personeller.yetkili_id uzerinden salon_id'leri
        $yetkiliolunanisletmeler = $u
            ? Personeller::where('yetkili_id', $u->id)->pluck('salon_id')->unique()->values()->all()
            : [];

        // Bildirimler — bos Eloquent collection, layout ->where()->count() ile cagiriyor
        $bildirimler = Bildirimler::query()->whereRaw('1=0')->get();

        return [
            'isletme' => $salon,
            'yetkiliolunanisletmeler' => $yetkiliolunanisletmeler,
            'bildirimler' => $bildirimler,
            'kalan_uyelik_suresi' => 999,
            'paketler' => collect(),
            'urun_drop' => [],
        ];
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

    public function destekListesi(Request $request)
    {
        $salonId = $this->aktifSalonId($request);
        if (!$salonId) return redirect('/isletmeyonetim');
        $salon = Salonlar::find($salonId);

        $ticketlar = DestekTalebi::where('salon_id', $salonId)
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('isletmeadmin.destek-listesi', array_merge($this->layoutVerisi($salon), [
            'sayfa_baslik' => 'Destek Talepleri',
            'pageindex'    => 200,
            'ticketlar'    => $ticketlar,
        ]));
    }

    public function destekYeniForm(Request $request)
    {
        $salonId = $this->aktifSalonId($request);
        if (!$salonId) return redirect('/isletmeyonetim');
        $salon = Salonlar::find($salonId);
        return view('isletmeadmin.destek-yeni', array_merge($this->layoutVerisi($salon), [
            'sayfa_baslik' => 'Yeni Destek Talebi',
            'pageindex'    => 201,
        ]));
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

        // Sistem yonetim panelinde badge'in yeni talebi hemen gormesi icin layout cache'ini temizle
        // (bildirim feed cache'i 15sn zaten — kisa surede yenilenir)
        try {
            \Cache::forget('sy.layout.bekleyen_ticket');
            // Aktif sistem yoneticilerinin bildirim cache'ini sil
            $aktifIds = DB::table('sistemyoneticileri')->where('aktif', 1)->pluck('id');
            foreach ($aktifIds as $sid) {
                \Cache::forget('sy.bildirim.user.' . $sid);
            }
        } catch (\Exception $e) {}

        return redirect('/isletmeyonetim/destek/' . $ticket->id)->with('basari', 'Talebiniz alındı. En kısa sürede dönüş yapacağız.');
    }

    public function destekDetay(Request $request, $id)
    {
        $salonId = $this->aktifSalonId($request);
        if (!$salonId) return redirect('/isletmeyonetim');
        $salon = Salonlar::find($salonId);

        $ticket = DestekTalebi::where('id', $id)->where('salon_id', $salonId)->firstOrFail();
        $mesajlar = DestekMesaji::where('ticket_id', $id)->where('ic_not', 0)->orderBy('id', 'asc')->get();

        return view('isletmeadmin.destek-detay', array_merge($this->layoutVerisi($salon), [
            'sayfa_baslik' => '#' . $ticket->numara,
            'pageindex'    => 200,
            'ticket'       => $ticket,
            'mesajlar'     => $mesajlar,
        ]));
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
