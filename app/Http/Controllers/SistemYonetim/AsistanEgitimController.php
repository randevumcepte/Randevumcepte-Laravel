<?php

namespace App\Http\Controllers\SistemYonetim;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Sistem Yonetimi > Asistan Egitimi.
 * Bedava KALIP kutuphanesi (soru tetikleyicileri -> cevap) + AI'ya dusen
 * "cevaplanamayan" sorular. Amac: sorulari kalibla bedava karsilayip AI ihtiyacini
 * azaltmak. Kalip degisince patron-asistan cache'i tazelenir.
 */
class AsistanEgitimController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sistemyonetim');
    }

    public function index()
    {
        $tabloYok = !\Schema::hasTable('asistan_kalip');
        $ortak = ['title' => 'Asistan Eğitimi', 'aktifMenu' => 'asistanegitim', 'tabloYok' => $tabloYok];

        if ($tabloYok) {
            return view('sistemyonetim.v2.asistan-egitim', array_merge($ortak, [
                'kaliplar' => collect(), 'cozulmeyenler' => collect(),
                'ozet' => ['kalip' => 0, 'bedava' => 0, 'bekleyen' => 0],
            ]));
        }

        $kaliplar = DB::table('asistan_kalip')->orderByDesc('kullanim_sayisi')->orderByDesc('id')->get();
        $cozulmeyenler = DB::table('asistan_cozulmeyen')->orderByDesc('adet')->orderByDesc('son_tarih')->limit(100)->get();

        return view('sistemyonetim.v2.asistan-egitim', array_merge($ortak, [
            'kaliplar' => $kaliplar,
            'cozulmeyenler' => $cozulmeyenler,
            'ozet' => [
                'kalip'   => $kaliplar->count(),
                'bedava'  => (int) $kaliplar->sum('kullanim_sayisi'),
                'bekleyen' => \Schema::hasTable('asistan_cozulmeyen') ? (int) DB::table('asistan_cozulmeyen')->count() : 0,
            ],
        ]));
    }

    /** Kalip ekle / guncelle. */
    public function kalipKaydet(Request $request)
    {
        $this->validate($request, [
            'tetikleyiciler' => 'required|string',
            'cevap'          => 'required|string',
            'kategori'       => 'nullable|string|max:40',
        ]);
        $veri = [
            'tetikleyiciler' => trim($request->input('tetikleyiciler')),
            'cevap'          => trim($request->input('cevap')),
            'kategori'       => $request->input('kategori') ?: null,
            'aktif'          => $request->input('aktif', 1) ? 1 : 0,
            'updated_at'     => date('Y-m-d H:i:s'),
        ];
        if ($request->filled('id')) {
            DB::table('asistan_kalip')->where('id', (int) $request->input('id'))->update($veri);
            $mesaj = 'Kalıp güncellendi.';
        } else {
            $veri['kullanim_sayisi'] = 0;
            $veri['created_at'] = date('Y-m-d H:i:s');
            DB::table('asistan_kalip')->insert($veri);
            $mesaj = 'Kalıp eklendi. Artık bu sorular AI’ya gitmeden bedava cevaplanır.';
        }
        // Cozulmeyen bir sorudan eklendiyse onu listeden kaldir.
        if ($request->filled('cozulmeyen_id')) {
            DB::table('asistan_cozulmeyen')->where('id', (int) $request->input('cozulmeyen_id'))->delete();
        }
        $this->cacheTazele();
        return redirect('/sistemyonetim/v2/asistan-egitim')->with('ok', $mesaj);
    }

    public function kalipSil(Request $request, $id)
    {
        DB::table('asistan_kalip')->where('id', (int) $id)->delete();
        $this->cacheTazele();
        return redirect('/sistemyonetim/v2/asistan-egitim')->with('ok', 'Kalıp silindi.');
    }

    public function cozulmeyenSil(Request $request, $id)
    {
        DB::table('asistan_cozulmeyen')->where('id', (int) $id)->delete();
        return redirect('/sistemyonetim/v2/asistan-egitim')->with('ok', 'Soru listeden kaldırıldı.');
    }

    protected function cacheTazele()
    {
        try { \Cache::forget('asistan_kalip_liste_v1'); } catch (\Throwable $e) {}
    }
}
