@extends('sistemyonetim.v2.layout')

@section('content')

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
        <a href="/sistemyonetim/yeniisletme" class="sy-btn sy-btn-primary"><span class="mdi mdi-plus"></span> Yeni Salon</a>
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
                        <td>{{ $s->yetkili_adi ?: '—' }}</td>
                        <td class="sy-text-muted sy-fs-13">
                            {{ \App\SistemYoneticileri::where('id',$s->musteri_yetkili_id)->value('name') ?: '—' }}
                        </td>
                        <td class="sy-text-muted sy-fs-12 nowrap">{{ \Carbon\Carbon::parse($s->created_at)->format('d.m.Y') }}</td>
                        <td>
                            @if($s->askiya_alindi)
                                <span class="sy-badge sy-badge-danger">Askıda</span>
                            @else
                                <span class="sy-badge sy-badge-success">Aktif</span>
                            @endif
                        </td>
                        <td class="sy-text-right nowrap">
                            <a href="/sistemyonetim/v2/salon/{{ $s->id }}" class="sy-btn sy-btn-sm sy-btn-soft" title="Detay">
                                <span class="mdi mdi-information-outline"></span>
                            </a>
                            <form method="post" action="/sistemyonetim/v2/salon/{{ $s->id }}/hesabina-gir" style="display:inline" onsubmit="return confirm('{{ $s->salon_adi }} hesabına geçiş yapılacak. Tüm hareketleriniz loglanacaktır. Devam edilsin mi?');">
                                @csrf
                                <input type="hidden" name="sebep" value="Destek girişi">
                                <button type="submit" class="sy-btn sy-btn-sm sy-btn-primary" title="Salonun hesabına gir" {{ $s->askiya_alindi ? 'disabled' : '' }}>
                                    <span class="mdi mdi-login"></span> Hesabına Gir
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="sy-empty"><div class="icon mdi mdi-store-off"></div><div class="baslik">Salon bulunamadı</div></div></td></tr>
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
