@extends('sistemyonetim.v2.layout')

@section('content')

{{-- Salon listesi: tablo yazilari cok kucuktu, okunabilirlik icin bu sayfaya ozel buyutuldu --}}
<style>
    .sy-table { font-size: 15px; }
    .sy-table tbody td { padding: 15px 16px; }
    .sy-table thead th { font-size: 12.5px; }
    .sy-table .sy-fw-600 { font-size: 16px; }
    .sy-table .sy-fs-13 { font-size: 14.5px; }
    .sy-table .sy-fs-12 { font-size: 14px; }
</style>

@php
    $rolMevcut = Auth::guard('sistemyonetim')->user()->rol ?? (Auth::guard('sistemyonetim')->user()->admin == 1 ? 'super_admin' : 'destek');
@endphp

<div class="sy-page-head">
    <div>
        <h2>Salonlar</h2>
        <div class="subtitle">Toplam {{ $salonlar->total() }} salon · {{ $salonlar->firstItem() ?? 0 }}-{{ $salonlar->lastItem() ?? 0 }} arası</div>
    </div>
    <div class="sy-flex-row">
        <a href="/sistemyonetim/v2/salonlar/csv?{{ http_build_query(request()->all()) }}" class="sy-btn"><span class="mdi mdi-file-download"></span> CSV</a>
        <a href="/sistemyonetim/v2/salon-ekle" class="sy-btn sy-btn-primary"><span class="mdi mdi-plus"></span> Yeni Salon</a>
    </div>
</div>

<form method="get" class="sy-filters">
    <div class="sy-form-group">
        <label>Ara</label>
        <input type="text" name="q" value="{{ $q }}" class="sy-input" placeholder="Salon adı, telefon, yetkili...">
    </div>
    <div class="sy-form-group" style="max-width:160px">
        <label>Durum</label>
        <select name="durum" class="sy-select">
            <option value="hepsi" {{ $durum=='hepsi'?'selected':'' }}>Hepsi</option>
            <option value="aktif" {{ $durum=='aktif'?'selected':'' }}>Aktif</option>
            <option value="askida" {{ $durum=='askida'?'selected':'' }}>Askıda</option>
        </select>
    </div>
    <div class="sy-form-group" style="max-width:200px">
        <label>Müşteri Temsilcisi</label>
        <select name="mt" class="sy-select">
            <option value="">Hepsi</option>
            @foreach($musteriTemsilcileri as $mt)
                <option value="{{ $mt->id }}" {{ request('mt')==$mt->id?'selected':'' }}>{{ $mt->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="sy-form-group" style="max-width:130px">
        <label>Sayfa başı</label>
        @php $pp = (int) request('per_page', 100); @endphp
        <select name="per_page" class="sy-select">
            <option value="50"  {{ $pp==50 ?'selected':'' }}>50</option>
            <option value="100" {{ $pp==100?'selected':'' }}>100</option>
            <option value="200" {{ $pp==200?'selected':'' }}>200</option>
            <option value="500" {{ $pp==500?'selected':'' }}>500</option>
        </select>
    </div>
    <button class="sy-btn sy-btn-primary"><span class="mdi mdi-magnify"></span> Filtrele</button>
    <a href="/sistemyonetim/v2/salonlar" class="sy-btn">Sıfırla</a>
</form>

@if(in_array($rolMevcut, ['super_admin','yonetici']))
<form id="bulkForm" method="post" action="/sistemyonetim/v2/salon/toplu-islem" onsubmit="return bulkSubmit()">
    @csrf
    <div class="sy-bulk-bar" id="bulkBar">
        <span class="count" id="bulkCount">0 salon seçildi</span>
        <select name="islem" id="bulkIslem" class="sy-select" style="max-width:220px" onchange="bulkIslemDegis(this.value)">
            <option value="">İşlem seç...</option>
            <option value="mt_ata">Müşteri Temsilcisi Ata</option>
            <option value="askiya_al">Askıya Al</option>
            <option value="aktif_et">Aktif Et</option>
        </select>
        <select name="mt_id" id="bulkMt" class="sy-select" style="max-width:220px;display:none">
            <option value="">Atama yok</option>
            @foreach($musteriTemsilcileri as $mt)
                <option value="{{ $mt->id }}">{{ $mt->name }}</option>
            @endforeach
        </select>
        <input type="text" name="sebep" id="bulkSebep" class="sy-input" placeholder="Askıya alma sebebi" style="max-width:280px;display:none">
        <button type="submit" class="sy-btn sy-btn-primary"><span class="mdi mdi-arrow-right"></span> Uygula</button>
        <button type="button" class="sy-btn" onclick="bulkTemizle()">Vazgeç</button>
    </div>
</form>
@endif

<div class="sy-card">
    <div class="sy-card-body tight">
        <table class="sy-table">
            <thead>
                <tr>
                    @if(in_array($rolMevcut, ['super_admin','yonetici']))
                    <th class="sy-row-check"><input type="checkbox" id="bulkAll" onclick="toggleAll(this)"></th>
                    @endif
                    <th>Salon</th>
                    <th>Konum</th>
                    <th>Yetkili</th>
                    <th>Müşteri Temsilcisi</th>
                    <th>Kayıt</th>
                    <th>Üyelik Bitiş</th>
                    <th>Durum</th>
                    <th class="sy-text-right">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salonlar as $s)
                    <tr>
                        @if(in_array($rolMevcut, ['super_admin','yonetici']))
                        <td class="sy-row-check"><input type="checkbox" class="bulkChk" name="ids[]" value="{{ $s->id }}" form="bulkForm" onclick="bulkSayim()"></td>
                        @endif
                        <td>
                            <div class="sy-fw-600">{{ $s->salon_adi }}</div>
                            <div class="sy-text-muted sy-fs-12">{{ $s->telefon_1 ?: '—' }}</div>
                        </td>
                        <td class="sy-text-muted sy-fs-13">
                            {{ optional($s->il)->il_adi ?: '—' }} / {{ optional($s->ilce)->ilce_adi ?: '—' }}
                        </td>
                        <td>
                            @php
                                $yAd  = trim((string) $s->yetkili_adi);
                                $yTel = trim((string) $s->yetkili_telefon);
                                // Yetkili adi VE telefonu bos ise hesap sahibine (role_id=1) dus
                                if ($yAd === '' && $yTel === '') {
                                    $hs = $hesapSahipleri[$s->id] ?? null;
                                    if ($hs) {
                                        $yAd  = trim((string) $hs->personel_adi);
                                        $yTel = trim((string) $hs->cep_telefon);
                                    }
                                }
                            @endphp
                            <div>{{ $yAd ?: '—' }}</div>
                            @if($yTel !== '')
                                <div class="sy-text-muted sy-fs-12">
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $yTel) }}" style="color:inherit">{{ $yTel }}</a>
                                </div>
                            @endif
                        </td>
                        <td class="sy-text-muted sy-fs-13">
                            {{ $mtMap[$s->musteri_yetkili_id] ?? '—' }}
                        </td>
                        <td class="sy-text-muted sy-fs-12 nowrap">{{ \Carbon\Carbon::parse($s->created_at)->format('d.m.Y') }}</td>
                        <td class="sy-fs-12 nowrap">
                            @php
                                $ub = $s->uyelik_bitis_tarihi;
                                $ubGecerli = $ub && substr((string) $ub, 0, 4) !== '0000';
                                $kalan = $ubGecerli ? (int) floor((strtotime($ub . ' 23:59:59') - time()) / 86400) : null;
                                $ubRenk = !$ubGecerli ? 'muted' : ($kalan < 0 ? 'danger' : ($kalan <= 7 ? 'warning' : 'success'));
                            @endphp
                            @if($ubGecerli)
                                <span style="color:var(--sy-{{ $ubRenk }});font-weight:700">{{ \Carbon\Carbon::parse($ub)->format('d.m.Y') }}</span>
                                <div class="sy-text-muted" style="font-size:11px">
                                    @if($kalan < 0) {{ abs($kalan) }} gün önce doldu
                                    @elseif($kalan === 0) bugün doluyor
                                    @else {{ $kalan }} gün kaldı @endif
                                </div>
                            @else
                                <span class="sy-text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                // Demo: uyelik_turu=3 VE lisansi kisa (<=90 gun). Uzun lisansi olan
                                // (Sirius gibi lisans almis ama demo_hesabi bayragi bayat kalmis) = Aktif.
                                $demo = (int) ($s->uyelik_turu ?? 0) === 3 && ($kalan === null || $kalan <= 90);
                                $lisansGecerli = $ubGecerli && $kalan !== null && $kalan >= 0;
                            @endphp
                            @if($s->askiya_alindi)
                                <span class="sy-badge sy-badge-danger">Askıda</span>
                            @elseif($demo)
                                <span class="sy-badge sy-badge-warning">Demo</span>
                            @elseif($lisansGecerli)
                                <span class="sy-badge sy-badge-success">Aktif</span>
                            @else
                                <span class="sy-badge sy-badge-muted">Süresi Doldu</span>
                            @endif
                        </td>
                        <td class="sy-text-right nowrap">
                            <a href="/sistemyonetim/v2/salon/{{ $s->id }}" class="sy-btn sy-btn-sm sy-btn-soft" title="Detay">
                                <span class="mdi mdi-information-outline"></span>
                            </a>
                            <form method="post" action="/sistemyonetim/v2/salon/{{ $s->id }}/hesabina-gir" style="display:inline">
                                @csrf
                                <input type="hidden" name="sebep" value="Destek girişi">
                                <button type="submit" class="sy-btn sy-btn-sm sy-btn-primary" title="Salonun hesabına gir" {{ $s->askiya_alindi ? 'disabled' : '' }}>
                                    <span class="mdi mdi-login"></span> Hesabına Gir
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><div class="sy-empty"><div class="icon mdi mdi-store-off"></div><div class="baslik">Salon bulunamadı</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="sy-pagination">
    {{ $salonlar->links() }}
</div>

@push('scripts')
<script>
function toggleAll(box) {
    document.querySelectorAll('.bulkChk').forEach(c => c.checked = box.checked);
    bulkSayim();
}
function bulkSayim() {
    const n = document.querySelectorAll('.bulkChk:checked').length;
    const bar = document.getElementById('bulkBar');
    const cnt = document.getElementById('bulkCount');
    if (n > 0) {
        bar.classList.add('visible');
        cnt.textContent = n + ' salon seçildi';
    } else {
        bar.classList.remove('visible');
        document.getElementById('bulkAll').checked = false;
    }
}
function bulkIslemDegis(v) {
    document.getElementById('bulkMt').style.display = v === 'mt_ata' ? 'block' : 'none';
    document.getElementById('bulkSebep').style.display = v === 'askiya_al' ? 'block' : 'none';
}
function bulkTemizle() {
    document.querySelectorAll('.bulkChk').forEach(c => c.checked = false);
    document.getElementById('bulkAll').checked = false;
    bulkSayim();
}
function bulkSubmit() {
    const n = document.querySelectorAll('.bulkChk:checked').length;
    const islem = document.getElementById('bulkIslem').value;
    if (!n || !islem) { alert('Önce salon ve işlem seçin.'); return false; }
    const labels = { mt_ata: 'müşteri temsilcisi atanacak', askiya_al: 'askıya alınacak', aktif_et: 'aktif edilecek' };
    return confirm(n + ' salon ' + labels[islem] + '. Onaylıyor musunuz?');
}
</script>
@endpush

@endsection
