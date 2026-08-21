@extends('sistemyonetim.v2.layout')

@section('content')

<style>
    .dk-ozet { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:18px; }
    .dk-ozet .kutu { flex:1; min-width:180px; background:var(--sy-surface,#fff); border:1px solid var(--sy-border,#ece6f7); border-radius:12px; padding:14px 16px; box-shadow:var(--sy-shadow-sm); }
    .dk-ozet .kutu .k-etiket { font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-text-muted,#7e7595); font-weight:600; }
    .dk-ozet .kutu .k-deger { font-size:22px; font-weight:700; margin-top:4px; color:var(--sy-text,#2a1f48); }
    .dk-tablo { width:100%; border-collapse:collapse; font-size:13.5px; color:var(--sy-text,#2a1f48); }
    .dk-tablo th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-text-muted,#7e7595); font-weight:600; padding:10px 12px; border-bottom:1px solid var(--sy-border,#ece6f7); }
    .dk-tablo td { padding:12px; border-bottom:1px solid var(--sy-border,#ece6f7); vertical-align:middle; }
    .dk-tablo tr:hover td { background:var(--sy-primary-soft,#f0e8fb); }
    .dk-salon-ad { font-weight:600; }
    .dk-salon-sub { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:2px; }
    .dk-num { font-variant-numeric:tabular-nums; }
    .dk-bar-wrap { min-width:150px; }
    .dk-bar { height:8px; border-radius:6px; background:var(--sy-border,#ece6f7); overflow:hidden; }
    .dk-bar span { display:block; height:100%; border-radius:6px; }
    .dk-bar-ok span { background:#2f9e6f; }
    .dk-bar-uyari span { background:#e0932f; }
    .dk-bar-kritik span { background:#d04d5e; }
    .dk-yuzde { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:4px; }
    .dk-kalan-eksi { color:#d04d5e; font-weight:700; }
    .dk-kalan-arti { color:#2f9e6f; font-weight:600; }
    .dk-hata { color:#d04d5e; font-size:11.5px; }
    .dk-kaynak { display:inline-block; font-size:10px; font-weight:600; padding:1px 6px; border-radius:6px; margin-top:3px; }
    .dk-kaynak-fatura { background:rgba(47,158,111,.12); color:#2f9e6f; }
    .dk-kaynak-santral { background:rgba(224,147,47,.12); color:#c47f1e; }
    .dk-bos { text-align:center; padding:40px; color:var(--sy-text-muted,#7e7595); }

    /* Modal */
    .dk-modal-bg { display:none; position:fixed; inset:0; background:rgba(30,27,46,.5); backdrop-filter:blur(2px); z-index:9999; align-items:center; justify-content:center; }
    .dk-modal-bg.acik { display:flex; }
    .dk-modal { background:var(--sy-surface,#fff); border:1px solid var(--sy-border,#ece6f7); border-radius:14px; width:100%; max-width:420px; padding:22px; box-shadow:0 24px 60px rgba(20,20,40,.25); color:var(--sy-text,#2a1f48); }
    .dk-modal h3 { margin:0 0 4px; font-size:17px; color:var(--sy-text,#2a1f48); }
    .dk-modal .alt { font-size:12.5px; color:var(--sy-text-muted,#7e7595); margin-bottom:16px; }
    .dk-modal label { display:block; font-size:12.5px; font-weight:600; margin-bottom:6px; }
    .dk-modal .aciklama { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:5px; }
    .dk-modal-alt { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; }
</style>

<div class="sy-page-head">
    <div>
        <h2>Dakika Yönetimi</h2>
        <div class="subtitle">E-santral müşterilerine tanımlanan giden (dış hat) konuşma dakikası vs. harcanan</div>
    </div>
</div>

<div class="dk-ozet">
    <div class="kutu">
        <div class="k-etiket">Toplam Tanımlı</div>
        <div class="k-deger dk-num">{{ number_format($toplamTanimli, 0, ',', '.') }} <small style="font-size:13px;color:var(--sy-muted,#94a3b8)">dk</small></div>
    </div>
    <div class="kutu">
        <div class="k-etiket">Toplam Kullanılan</div>
        <div class="k-deger dk-num">{{ number_format($toplamKullanilan, 1, ',', '.') }} <small style="font-size:13px;color:var(--sy-muted,#94a3b8)">dk</small></div>
    </div>
    <div class="kutu">
        <div class="k-etiket">Toplam Kalan</div>
        <div class="k-deger dk-num">{{ number_format($toplamTanimli - $toplamKullanilan, 1, ',', '.') }} <small style="font-size:13px;color:var(--sy-muted,#94a3b8)">dk</small></div>
    </div>
</div>

<div class="sy-card">
    <div class="sy-card-body">

        <form method="GET" action="/sistemyonetim/v2/dakika" class="sy-flex-row" style="gap:8px;margin-bottom:16px">
            <input type="text" name="q" value="{{ $q }}" class="sy-input" placeholder="Salon adı / yetkili / telefon ara..." style="max-width:320px">
            <button type="submit" class="sy-btn sy-btn-soft sy-btn-sm"><span class="mdi mdi-magnify"></span> Ara</button>
            @if($q !== '')
                <a href="/sistemyonetim/v2/dakika" class="sy-btn sy-btn-soft sy-btn-sm">Temizle</a>
            @endif
        </form>

        @if(count($satirlar) === 0)
            <div class="dk-bos">
                <span class="mdi mdi-phone-off" style="font-size:32px;display:block;margin-bottom:8px"></span>
                Santral (trunk) tanımlı müşteri bulunamadı.
            </div>
        @else
        <div style="overflow-x:auto">
        <table class="dk-tablo">
            <thead>
                <tr>
                    <th>Salon</th>
                    <th>Trunk</th>
                    <th>Sayım Başlangıç</th>
                    <th style="text-align:right">Tanımlı (dk)</th>
                    <th style="text-align:right">Kullanılan (dk)</th>
                    <th style="text-align:right">Kalan (dk)</th>
                    <th>Kullanım</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($satirlar as $s)
                    @php
                        $barSinif = $s['yuzde'] >= 90 ? 'dk-bar-kritik' : ($s['yuzde'] >= 70 ? 'dk-bar-uyari' : 'dk-bar-ok');
                    @endphp
                    <tr>
                        <td>
                            <div class="dk-salon-ad">{{ $s['salon_adi'] }}</div>
                            <div class="dk-salon-sub">#{{ $s['salon_id'] }} · {{ $s['adet'] }} giden çağrı</div>
                        </td>
                        <td class="dk-num">{{ $s['trunk'] }}</td>
                        <td class="dk-num">{{ date('d.m.Y', strtotime($s['sayim_baslangic'])) }}</td>
                        <td class="dk-num" style="text-align:right">{{ number_format($s['tanimli'], 0, ',', '.') }}</td>
                        <td class="dk-num" style="text-align:right">
                            {{ number_format($s['kullanilan'], 1, ',', '.') }}
                            @if(($s['kaynak'] ?? '') === 'saglayici')
                                <div class="dk-kaynak dk-kaynak-fatura" title="Operatör (voicetelekom) faturalandırılan süre">fatura</div>
                            @else
                                <div class="dk-kaynak dk-kaynak-santral" title="Santral (CDR) ölçümü — operatör faturasından ~%10 fazla olabilir">≈ santral</div>
                            @endif
                            @if($s['hata'])<div class="dk-hata">veri alınamadı</div>@endif
                        </td>
                        <td class="dk-num" style="text-align:right">
                            <span class="{{ $s['kalan'] < 0 ? 'dk-kalan-eksi' : 'dk-kalan-arti' }}">
                                {{ number_format($s['kalan'], 1, ',', '.') }}
                            </span>
                        </td>
                        <td class="dk-bar-wrap">
                            <div class="dk-bar {{ $barSinif }}"><span style="width:{{ $s['yuzde'] }}%"></span></div>
                            <div class="dk-yuzde">%{{ $s['yuzde'] }}@if($s['tanimli'] == 0) (tanımsız)@endif</div>
                        </td>
                        <td style="text-align:right">
                            <button type="button" class="sy-btn sy-btn-soft sy-btn-sm dk-duzenle"
                                data-id="{{ $s['salon_id'] }}"
                                data-ad="{{ $s['salon_adi'] }}"
                                data-tanimli="{{ $s['tanimli'] }}"
                                data-sayim="{{ $s['sayim_baslangic'] }}">
                                <span class="mdi mdi-pencil"></span> Düzenle
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif

    </div>
</div>

<!-- Duzenleme modali -->
<div class="dk-modal-bg" id="dkModalBg">
    <form class="dk-modal" method="POST" id="dkForm">
        {{ csrf_field() }}
        <h3>Tanımlı Dakika</h3>
        <div class="alt" id="dkModalSalon">—</div>

        <div style="margin-bottom:14px">
            <label>Tanımlı dakika (havuz)</label>
            <input type="number" name="tanimli_dakika" id="dkTanimli" class="sy-input" min="0" step="1" placeholder="Örn. 1000">
            <div class="aciklama">Müşteriye tanınan toplam giden konuşma dakikası. Havuzu büyütmek için artırın.</div>
        </div>

        <div>
            <label>Sayım başlangıç tarihi (opsiyonel)</label>
            <input type="date" name="sayim_baslangic" id="dkSayim" class="sy-input">
            <div class="aciklama">Boş bırakılırsa salonun oluşturma tarihinden itibaren sayılır.</div>
        </div>

        <div class="dk-modal-alt">
            <button type="button" class="sy-btn sy-btn-soft" id="dkIptal">Vazgeç</button>
            <button type="submit" class="sy-btn sy-btn-primary"><span class="mdi mdi-content-save"></span> Kaydet</button>
        </div>
    </form>
</div>

<script>
(function(){
    var bg     = document.getElementById('dkModalBg');
    var form   = document.getElementById('dkForm');
    var salonU = document.getElementById('dkModalSalon');
    var tanimli= document.getElementById('dkTanimli');
    var sayim  = document.getElementById('dkSayim');

    function ac(id, ad, t, s){
        form.action = '/sistemyonetim/v2/salon/' + id + '/dakika';
        salonU.textContent = ad + ' (#' + id + ')';
        tanimli.value = t || 0;
        sayim.value = (s && s.length >= 10) ? s.substring(0,10) : '';
        bg.classList.add('acik');
        tanimli.focus();
    }
    function kapat(){ bg.classList.remove('acik'); }

    document.querySelectorAll('.dk-duzenle').forEach(function(b){
        b.addEventListener('click', function(){
            ac(b.dataset.id, b.dataset.ad, b.dataset.tanimli, b.dataset.sayim);
        });
    });
    document.getElementById('dkIptal').addEventListener('click', kapat);
    bg.addEventListener('click', function(e){ if(e.target === bg) kapat(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape') kapat(); });
})();
</script>

@endsection
