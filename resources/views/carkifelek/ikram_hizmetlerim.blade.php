@extends('layout.layout_cark')

@section('title', 'İkram Hizmetlerim')
@section('content')
{{-- Musteri: kazandigi ikram hizmetler (hizmet bazinda ucretli alim / kazanilan / kullanilan / kalan).
     Veri DamgaKartiServisi::musteriKartlari (tahsilat ekraniyla ayni sayim). --}}
<style>
.ih-page { max-width: 860px; margin: 40px auto; padding: 0 16px 80px; }
.ih-hero { background: linear-gradient(135deg,#7c3aed 0%,#a855f7 55%,#10b981 100%); color:#fff; border-radius:22px; padding:26px; box-shadow:0 14px 36px rgba(124,58,237,.22); display:flex; align-items:center; gap:22px; flex-wrap:wrap; }
.ih-hero h1 { margin:0 0 4px; font-size:20px; font-weight:800; color:#fff; }
.ih-hero .salon { font-size:14px; opacity:.92; }
.ih-sayi { font-size:60px; font-weight:800; line-height:1; letter-spacing:-2px; }
.ih-sayi-alt { font-size:14px; opacity:.92; margin-top:4px; }
.ih-bilgi { background:#fff; border-radius:16px; padding:16px 20px; margin-top:14px; box-shadow:0 4px 18px rgba(0,0,0,.06); font-size:13px; color:#4b5563; line-height:1.6; }
.ih-kart { background:#fff; border-radius:16px; padding:18px 20px; box-shadow:0 4px 18px rgba(0,0,0,.06); margin-top:14px; }
.ih-bas { display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center; }
.ih-bas b { font-size:16px; color:#111827; }
.ih-bas span { font-size:13px; font-weight:700; color:#7c3aed; }
.ih-istat { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin:12px 0; }
.ih-istat div { background:#f9fafb; border-radius:12px; padding:10px; text-align:center; }
.ih-istat b { display:block; font-size:20px; color:#111827; }
.ih-istat small { font-size:11px; color:#6b7280; }
.ih-istat .kalan { background:#f5f3ff; } .ih-istat .kalan b { color:#7c3aed; }
.ih-cizgi { display:flex; flex-wrap:wrap; gap:7px; margin:6px 0 8px; align-items:center; }
.ih-n { width:30px; height:30px; border-radius:50%; border:1.5px solid rgba(124,58,237,.35); color:rgba(124,58,237,.6); display:flex; align-items:center; justify-content:center; font-size:12px; }
.ih-n.dolu { background:#7c3aed; border-color:#7c3aed; color:#fff; font-weight:800; }
.ih-hediye { height:30px; padding:0 12px; border-radius:15px; background:#ecfdf5; border:1px solid rgba(16,185,129,.5); color:#047857; font-size:12px; font-weight:800; display:flex; align-items:center; }
.ih-not { font-size:13px; color:#6b7280; }
.ih-gecmis { margin-top:10px; border-top:1px solid #f3f4f6; padding-top:8px; }
.ih-gecmis div { display:flex; justify-content:space-between; font-size:13px; color:#6b7280; padding:3px 0; }
.ih-gecmis b { color:#059669; }
.ih-gbas { font-size:13px; font-weight:800; color:#374151; margin-bottom:4px; }
.ih-gecmis .ih-satir { align-items:center; gap:10px; padding:7px 0; border-bottom:1px dashed #f3f4f6; line-height:1.45; }
.ih-gecmis .ih-satir small { color:#9ca3af; }
.ih-rozet { flex-shrink:0; font-size:11px; font-weight:800; padding:3px 10px; border-radius:12px; white-space:nowrap; }
.ih-kullanildi { background:#ecfdf5; color:#047857; }
.ih-gecerli { background:#ede9fe; color:#6d28d9; }
.ih-suresi_doldu { background:#fef2f2; color:#dc2626; }
.ih-son { background:#fffbeb; color:#92400e; border-radius:10px; padding:8px 12px; font-size:13px; margin:0 0 8px; }
.ih-bos { text-align:center; color:#9ca3af; padding:40px 10px; }
</style>

@php
    $_kartlar = collect($ikramKartlari);
    $_hak = $_kartlar->sum('hak');
@endphp
<div class="ih-page">
    <div class="ih-hero">
        <div>
            <div class="ih-sayi">🎁 {{ $_hak }}</div>
            <div class="ih-sayi-alt">ikram hizmetiniz var</div>
        </div>
        <div style="flex:1;min-width:200px;">
            <h1>İkram Hizmetlerim</h1>
            <div class="salon">{{ $salon->salon_adi }}</div>
        </div>
    </div>

    <div class="ih-bilgi">
        Hizmetleri ücretli aldıkça ikram kazanırsınız. Kazandığınız ikramı <b>dilediğiniz bir seansınızda</b> kullanabilirsiniz;
        kullanmak istediğinizi ödeme sırasında salona söylemeniz yeterli. İstemezseniz hakkınız geçerlilik süresi boyunca saklı kalır.
    </div>

    @forelse($_kartlar as $k)
        <div class="ih-kart">
            <div class="ih-bas">
                <b>{{ $k['hizmet_adi'] }}</b>
                <span>{{ $k['kural_yazi'] ?? ($k['gereken_adet'].' alana '.$k['ucretsiz_adet'].' ikram') }} @if(!empty($k['kampanya_bitis']))· kampanya {{ date('d.m.Y', strtotime($k['kampanya_bitis'])) }} tarihine kadar @endif @if(!empty($k['gecerlilik']))· {{ $k['gecerlilik'] }} geçerli @endif</span>
            </div>
            <div class="ih-istat">
                <div><b>{{ $k['kazanilan'] }}</b><small>Kazanılan ikram</small></div>
                <div><b>{{ $k['kullanilan'] }}</b><small>Kullanılan</small></div>
                <div class="kalan"><b>{{ $k['hak'] }}</b><small>Kalan ikram</small></div>
            </div>
            @if(!empty($k['son_kullanma']))
                <div class="ih-son">⏳ {{ count($k['gecerli_ikramlar']) > 1 ? 'En yakın ikramınızın' : 'İkramınızın' }} son kullanma tarihi: <b>{{ date('d.m.Y', strtotime($k['son_kullanma'])) }}</b></div>
            @endif
            @if(!empty($k['suresi_dolan']))
                <div class="ih-not" style="color:#dc2626;">{{ $k['suresi_dolan'] }} ikramın süresi doldu.</div>
            @endif
            @if($k['aktif'])
                <div class="ih-cizgi">
                    @for($i = 0; $i < $k['gereken_adet']; $i++)
                        @if($k['hak'] > 0 || $i < $k['ilerleme'])<div class="ih-n dolu">✓</div>@else<div class="ih-n">{{ $i + 1 }}</div>@endif
                    @endfor
                    <div class="ih-hediye">🎁 İkram</div>
                </div>
                @if($k['hak'] > 0)
                    <div class="ih-not"><b>Kartınız doldu: {{ $k['hak'] }} ücretsiz hakkınız var.</b>@if($k['ilerleme'] > 0) Yeni kartınız: {{ $k['ilerleme'] }}/{{ $k['gereken_adet'] }}@endif</div>
                @else
                    <div class="ih-not">Ücretsiz hakkınıza {{ $k['gereken_adet'] - $k['ilerleme'] }} ücretli alım kaldı.</div>
                @endif
                <div class="ih-not">Paket seanslarınız bu kampanyaya sayılmaz.</div>
            @else
                <div class="ih-not" style="color:#9ca3af;">Bu ikram kampanyası sona erdi; kalan ikramınızı kullanabilirsiniz.</div>
            @endif
            @if(!empty($k['ikramlar']))
                <div class="ih-gecmis">
                    <div class="ih-gbas">İkram geçmişi</div>
                    @foreach($k['ikramlar'] as $ik)
                        <div class="ih-satir">
                            <span>
                                <small>{{ date('d.m.Y', strtotime($ik['kazanma'])) }} tarihinde kazanıldı</small>
                                @if($ik['durum'] === 'kullanildi')
                                    <br>{{ date('d.m.Y', strtotime($ik['kullanim_tarihi'])) }} tarihinde kullanıldı
                                    @if($ik['tutar'] > 0) · <b>{{ number_format($ik['tutar'], $ik['tutar'] == floor($ik['tutar']) ? 0 : 2, ',', '.') }} ₺ kazanç</b> @endif
                                @elseif($ik['durum'] === 'gecerli')
                                    <br>{{ $ik['bitis'] ? date('d.m.Y', strtotime($ik['bitis'])).' tarihine kadar kullanılabilir' : 'Süresiz, dilediğiniz zaman kullanılabilir' }}
                                @else
                                    <br>{{ date('d.m.Y', strtotime($ik['bitis'])) }} tarihinde süresi doldu
                                @endif
                            </span>
                            <span class="ih-rozet ih-{{ $ik['durum'] }}">{{ ['kullanildi' => 'Kullanıldı', 'gecerli' => 'Kullanılabilir', 'suresi_doldu' => 'Süresi doldu'][$ik['durum']] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="ih-kart ih-bos">Bu salonda şu an ikram hizmet kampanyası yok.</div>
    @endforelse
</div>
@endsection
