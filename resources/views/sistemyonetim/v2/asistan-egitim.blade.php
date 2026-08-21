@extends('sistemyonetim.v2.layout')

@section('content')
@php $js = function ($v) { return json_encode($v, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); }; @endphp
<style>
    .ae-baslik h1 { font-size:20px; font-weight:700; margin:0 0 4px; color:#2a2340; }
    .ae-alt { color:#8b7ba8; font-size:13px; margin-bottom:16px; }
    .ae-kartlar { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; margin-bottom:18px; }
    .ae-kart { background:#fff; border:1px solid #ece7f6; border-radius:14px; padding:16px; box-shadow:0 2px 8px rgba(92,0,142,.05); }
    .ae-kart .e { font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:#8b7ba8; font-weight:700; }
    .ae-kart .d { font-size:24px; font-weight:800; margin-top:5px; color:#2a2340; }
    .ae-kart.yesil .d { color:#16a34a; }
    .ae-panel { background:#fff; border:1px solid #ece7f6; border-radius:14px; padding:18px; margin-bottom:18px; box-shadow:0 2px 8px rgba(92,0,142,.05); }
    .ae-panel h2 { font-size:13px; text-transform:uppercase; letter-spacing:.4px; color:#8b7ba8; font-weight:700; margin:0 0 14px; }
    .ae-alan { margin-bottom:12px; }
    .ae-alan label { display:block; font-size:12px; color:#6b5b86; font-weight:600; margin-bottom:5px; }
    .ae-alan textarea, .ae-alan input[type=text] { width:100%; background:#f7f6fb; border:1px solid #e3daf3; border-radius:10px; padding:10px 12px; color:#2a2340; font-size:14px; font-family:inherit; box-sizing:border-box; }
    .ae-alan textarea { min-height:70px; resize:vertical; }
    .ae-ipucu { font-size:11.5px; color:#9b90b3; margin-top:4px; }
    .ae-btn { background:linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; border:none; border-radius:9px; padding:10px 20px; font-weight:700; cursor:pointer; font-size:14px; }
    .ae-btn.gri { background:#f0ebf8; color:#5C008E; border:1px solid #e3daf3; padding:7px 12px; font-size:12.5px; }
    .ae-btn.kirmizi { background:#fdecec; color:#b91c1c; border:1px solid #f3b4b4; padding:7px 12px; font-size:12.5px; }
    .ae-tablo { width:100%; border-collapse:collapse; font-size:13px; }
    .ae-tablo th { text-align:left; font-size:10.5px; text-transform:uppercase; letter-spacing:.4px; color:#8b7ba8; font-weight:600; padding:9px 10px; border-bottom:2px solid #f0ebf8; }
    .ae-tablo td { padding:11px 10px; border-bottom:1px solid #f4f1fa; color:#3a2a5c; vertical-align:top; }
    .ae-etik { display:inline-block; background:#f0ebf8; color:#5C008E; border-radius:6px; padding:2px 7px; font-size:11.5px; margin:2px 4px 2px 0; }
    .ae-ok { background:#e8f8ef; border:1px solid #a7e0c0; color:#15803d; border-radius:10px; padding:10px 14px; font-size:13px; margin-bottom:14px; }
    .ae-uyari { background:#fef8e7; border:1px solid #f3d98a; color:#8a6100; border-radius:10px; padding:12px 14px; font-size:13px; margin-bottom:16px; }
    .ae-rozet { display:inline-block; background:#eef2ff; color:#4338ca; border-radius:20px; padding:2px 9px; font-size:12px; font-weight:700; }
</style>

<div class="ae-baslik"><h1>🎓 Asistan Eğitimi</h1></div>
<div class="ae-alt">Kalıp soru-cevap ekle → o sorular (ve eş anlamlıları) AI’ya gitmeden <b>bedava</b> cevaplanır. Aşağıda AI’ya düşen soruları görüp tek tıkla kalıba çevir; asistan zamanla AI’ya daha az ihtiyaç duyar.</div>

@if(session('ok'))<div class="ae-ok">✓ {{ session('ok') }}</div>@endif
@if(session('hata'))<div class="ae-uyari">⚠ {{ session('hata') }}</div>@endif
@if(!empty($tabloYok))
    <div class="ae-uyari">⚠ <b>asistan_kalip</b> tabloları yok. Sunucuda <code>php artisan migrate</code> çalıştırın.</div>
@endif

<div class="ae-kartlar">
    <div class="ae-kart"><div class="e">Kalıp Sayısı</div><div class="d">{{ number_format($ozet['kalip'],0,',','.') }}</div></div>
    <div class="ae-kart yesil"><div class="e">Bedava Cevaplanan</div><div class="d">{{ number_format($ozet['bedava'],0,',','.') }}</div></div>
    <div class="ae-kart"><div class="e">Bekleyen Soru</div><div class="d">{{ number_format($ozet['bekleyen'],0,',','.') }}</div></div>
</div>

{{-- PDF ile toplu egitim --}}
<div class="ae-panel">
    <h2>📄 PDF ile Eğitim (toplu)</h2>
    <div style="font-size:13px;color:#6b5b86;margin-bottom:12px;line-height:1.5">
        Bir PDF yükle → yapay zeka belgeyi <b>tek seferlik</b> okuyup soru-cevap kalıpları çıkarır → sen kontrol edip kaydedersin.
        Kaydedilen sorular <b style="color:#16a34a">sonsuza kadar bedava</b> cevaplanır (bir daha AI’ya gitmez).
        <br><span class="ae-ipucu">Not: PDF gelişigüzel olabilir (broşür, fiyat listesi, kurallar). Soru-cevap ayrı ayrı yazman gerekmez; AI kendisi çıkarır. En fazla 20 MB.</span>
    </div>
    <form method="POST" action="/sistemyonetim/v2/asistan-egitim/pdf-coz" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <input type="file" name="pdf" accept="application/pdf,.pdf" required
                   style="font-size:13px;color:#3a2a5c">
            <button type="submit" class="ae-btn" onclick="this.innerText='Okunuyor… (10-30 sn)';this.disabled=true;this.form.submit();">
                PDF’i Oku ve Çıkar
            </button>
        </div>
    </form>
</div>

@if(session('pdf_onizleme'))
{{-- PDF'ten cikan onizleme -> onayla ve kaydet --}}
<div class="ae-panel" style="border-color:#c9b8ec">
    <h2>✅ Çıkarılan Soru-Cevaplar — kontrol et, kaydet
        @if(session('pdf_dosya'))<span style="text-transform:none;color:#8b7ba8;font-weight:600"> ({{ session('pdf_dosya') }})</span>@endif
    </h2>
    <form method="POST" action="/sistemyonetim/v2/asistan-egitim/pdf-kaydet">
        {{ csrf_field() }}
        @foreach(session('pdf_onizleme') as $i => $o)
            <div style="border:1px solid #ece7f6;border-radius:12px;padding:14px;margin-bottom:12px;background:#faf9fe">
                <label style="display:flex;align-items:center;gap:8px;font-weight:700;color:#2a2340;font-size:13px;margin-bottom:10px">
                    <input type="checkbox" name="sec[]" value="{{ $i }}" checked> Bu kalıbı kaydet
                    <span class="ae-etik" style="margin-left:auto">{{ $o['kategori'] }}</span>
                </label>
                <div class="ae-alan">
                    <label>Tetikleyiciler (virgülle)</label>
                    <textarea name="tetikleyiciler[{{ $i }}]" style="min-height:48px">{{ $o['tetikleyiciler'] }}</textarea>
                </div>
                <div class="ae-alan" style="margin-bottom:0">
                    <label>Cevap</label>
                    <textarea name="cevap[{{ $i }}]">{{ $o['cevap'] }}</textarea>
                </div>
                <input type="hidden" name="kategori[{{ $i }}]" value="{{ $o['kategori'] }}">
            </div>
        @endforeach
        <div style="display:flex;gap:10px;align-items:center;margin-top:6px">
            <button type="submit" class="ae-btn">Seçilenleri Kaydet</button>
            <button type="button" class="ae-btn gri" onclick="document.querySelectorAll('input[name=&quot;sec[]&quot;]').forEach(c=>c.checked=false)">Tümünün işaretini kaldır</button>
            <button type="button" class="ae-btn gri" onclick="document.querySelectorAll('input[name=&quot;sec[]&quot;]').forEach(c=>c.checked=true)">Tümünü seç</button>
        </div>
    </form>
</div>
@endif

{{-- Kalip ekle/duzenle formu --}}
<div class="ae-panel" id="kalipForm">
    <h2 id="formBaslik">Yeni Kalıp Ekle</h2>
    <form method="POST" action="/sistemyonetim/v2/asistan-egitim/kalip-kaydet">
        {{ csrf_field() }}
        <input type="hidden" name="id" id="f_id">
        <input type="hidden" name="cozulmeyen_id" id="f_coz">
        <div class="ae-alan">
            <label>Tetikleyiciler (eş anlamlılar) — her satıra bir tane ya da virgülle ayır</label>
            <textarea name="tetikleyiciler" id="f_tetik" placeholder="iade&#10;para iade&#10;geri ödeme&#10;iade politikası"></textarea>
            <div class="ae-ipucu">Bu ifadelerden biri soruda geçerse kalıp devreye girer. Ne kadar çok eş anlamlı yazarsan o kadar çok soruyu bedava karşılar.</div>
        </div>
        <div class="ae-alan">
            <label>Cevap</label>
            <textarea name="cevap" id="f_cevap" placeholder="İade politikamız 7 gündür; kullanılmamış ürünlerde fiş ile iade alınır."></textarea>
        </div>
        <div class="ae-alan" style="max-width:280px">
            <label>Kategori (opsiyonel)</label>
            <input type="text" name="kategori" id="f_kategori" placeholder="ör. politika, hizmet, sohbet">
        </div>
        <div style="display:flex; gap:10px; align-items:center">
            <button type="submit" class="ae-btn">Kaydet</button>
            <button type="button" class="ae-btn gri" onclick="aeTemizle()">Temizle</button>
            <label style="font-size:13px;color:#6b5b86"><input type="checkbox" name="aktif" value="1" id="f_aktif" checked> Aktif</label>
        </div>
    </form>
</div>

{{-- Cevaplanamayan sorular --}}
<div class="ae-panel">
    <h2>AI’ya Düşen Sorular — kalıba çevir, bedava yap</h2>
    <table class="ae-tablo">
        <thead><tr><th>Soru</th><th style="width:90px" class="">Kaç kez</th><th style="width:130px">Son</th><th style="width:220px"></th></tr></thead>
        <tbody>
        @forelse($cozulmeyenler as $c)
            <tr>
                <td>{{ $c->ham ?: $c->soru_norm }}</td>
                <td><span class="ae-rozet">{{ $c->adet }}×</span></td>
                <td style="color:#8b7ba8;font-size:12px">{{ $c->son_tarih ? \Carbon\Carbon::parse($c->son_tarih)->format('d.m H:i') : '—' }}</td>
                <td>
                    <button type="button" class="ae-btn gri" onclick="aeKalibaCevir({{ $c->id }}, {!! $js($c->ham ?: $c->soru_norm) !!})">Kalıba çevir</button>
                    <form method="POST" action="/sistemyonetim/v2/asistan-egitim/cozulmeyen-sil/{{ $c->id }}" style="display:inline" onsubmit="return confirm('Kaldırılsın mı?')">
                        {{ csrf_field() }}
                        <button type="submit" class="ae-btn kirmizi">Kaldır</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" style="text-align:center;color:#8b7ba8;padding:24px">Şimdilik AI’ya düşen (kalıba çevrilecek) soru yok. 🎉</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Mevcut kaliplar --}}
<div class="ae-panel">
    <h2>Kalıp Kütüphanesi</h2>
    <table class="ae-tablo">
        <thead><tr><th style="width:36%">Tetikleyiciler</th><th>Cevap</th><th style="width:80px">Kullanım</th><th style="width:150px"></th></tr></thead>
        <tbody>
        @forelse($kaliplar as $k)
            <tr style="{{ $k->aktif ? '' : 'opacity:.5' }}">
                <td>
                    @foreach(preg_split('/[\r\n,;]+/', $k->tetikleyiciler) as $t)
                        @if(trim($t) !== '')<span class="ae-etik">{{ trim($t) }}</span>@endif
                    @endforeach
                    @if(!$k->aktif)<div style="color:#b91c1c;font-size:11px;margin-top:4px">pasif</div>@endif
                </td>
                <td>{{ $k->cevap }}</td>
                <td><span class="ae-rozet">{{ number_format($k->kullanim_sayisi,0,',','.') }}</span></td>
                <td>
                    <button type="button" class="ae-btn gri" onclick="aeDuzenle({{ $k->id }}, {!! $js($k->tetikleyiciler) !!}, {!! $js($k->cevap) !!}, {!! $js($k->kategori) !!}, {{ $k->aktif }})">Düzenle</button>
                    <form method="POST" action="/sistemyonetim/v2/asistan-egitim/kalip-sil/{{ $k->id }}" style="display:inline" onsubmit="return confirm('Kalıp silinsin mi?')">
                        {{ csrf_field() }}
                        <button type="submit" class="ae-btn kirmizi">Sil</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" style="text-align:center;color:#8b7ba8;padding:24px">Henüz kalıp yok. Yukarıdan ilk kalıbı ekleyin.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<script>
    function aeTemizle(){
        document.getElementById('f_id').value=''; document.getElementById('f_coz').value='';
        document.getElementById('f_tetik').value=''; document.getElementById('f_cevap').value='';
        document.getElementById('f_kategori').value=''; document.getElementById('f_aktif').checked=true;
        document.getElementById('formBaslik').textContent='Yeni Kalıp Ekle';
    }
    function aeDuzenle(id, tetik, cevap, kategori, aktif){
        document.getElementById('f_id').value=id; document.getElementById('f_coz').value='';
        document.getElementById('f_tetik').value=tetik||''; document.getElementById('f_cevap').value=cevap||'';
        document.getElementById('f_kategori').value=kategori||''; document.getElementById('f_aktif').checked=!!aktif;
        document.getElementById('formBaslik').textContent='Kalıbı Düzenle (#'+id+')';
        document.getElementById('kalipForm').scrollIntoView({behavior:'smooth'});
    }
    function aeKalibaCevir(cozId, soru){
        aeTemizle();
        document.getElementById('f_coz').value=cozId;
        document.getElementById('f_tetik').value=soru||'';
        document.getElementById('formBaslik').textContent='Bu Soruyu Kalıba Çevir';
        document.getElementById('f_cevap').focus();
        document.getElementById('kalipForm').scrollIntoView({behavior:'smooth'});
    }
</script>
@endsection
