@extends('sistemyonetim.v2.layout')

@section('content')

@php
    $oncelikRenk = ['acil'=>'danger','yuksek'=>'warning','orta'=>'info','dusuk'=>'muted'];
    $durumRenk = ['acik'=>'danger','islemde'=>'warning','bekliyor'=>'info','cozumlendi'=>'success','kapali'=>'muted'];
@endphp

<div class="sy-page-head">
    <div>
        <h2>Destek Talepleri</h2>
        <div class="subtitle">{{ $ticketlar->total() }} talep — gerçek zamanlı çalışma alanı</div>
    </div>
    <div class="sy-flex-row">
        <a href="/sistemyonetim/v2/ticket/csv" class="sy-btn"><span class="mdi mdi-file-download"></span> CSV</a>
        <a href="/sistemyonetim/v2/ticket/yeni" class="sy-btn sy-btn-primary"><span class="mdi mdi-plus"></span> Yeni Talep</a>
    </div>
</div>

<form method="get" class="sy-filters">
    <div class="sy-form-group">
        <label>Ara</label>
        <input type="text" name="q" value="{{ $q }}" class="sy-input" placeholder="numara, konu, salon...">
    </div>
    <div class="sy-form-group" style="max-width:170px">
        <label>Durum</label>
        <select name="durum" class="sy-select">
            <option value="acik_islemde" {{ $durum=='acik_islemde'?'selected':'' }}>Açık + İşlemde</option>
            <option value="hepsi" {{ $durum=='hepsi'?'selected':'' }}>Hepsi</option>
            <option value="acik" {{ $durum=='acik'?'selected':'' }}>Açık</option>
            <option value="islemde" {{ $durum=='islemde'?'selected':'' }}>İşlemde</option>
            <option value="bekliyor" {{ $durum=='bekliyor'?'selected':'' }}>Bekliyor</option>
            <option value="cozumlendi" {{ $durum=='cozumlendi'?'selected':'' }}>Çözümlendi</option>
            <option value="kapali" {{ $durum=='kapali'?'selected':'' }}>Kapalı</option>
        </select>
    </div>
    <div class="sy-form-group" style="max-width:140px">
        <label>Öncelik</label>
        <select name="oncelik" class="sy-select">
            <option value="">Hepsi</option>
            <option value="acil" {{ $oncelik=='acil'?'selected':'' }}>Acil</option>
            <option value="yuksek" {{ $oncelik=='yuksek'?'selected':'' }}>Yüksek</option>
            <option value="orta" {{ $oncelik=='orta'?'selected':'' }}>Orta</option>
            <option value="dusuk" {{ $oncelik=='dusuk'?'selected':'' }}>Düşük</option>
        </select>
    </div>
    <div class="sy-form-group" style="max-width:180px">
        <label>Atanan</label>
        <select name="atanan" class="sy-select">
            <option value="">Hepsi</option>
            <option value="bana" {{ $atanan=='bana'?'selected':'' }}>Bana atananlar</option>
            <option value="atanmamis" {{ $atanan=='atanmamis'?'selected':'' }}>Atanmamış</option>
            @foreach($ekip as $e)
                <option value="{{ $e->id }}" {{ $atanan==$e->id?'selected':'' }}>{{ $e->name }}</option>
            @endforeach
        </select>
    </div>
    <button class="sy-btn sy-btn-primary"><span class="mdi mdi-magnify"></span> Filtrele</button>
</form>

<div class="sy-card">
    <div class="sy-card-body tight">
        <table class="sy-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Konu</th>
                    <th>Salon</th>
                    <th>Kategori</th>
                    <th>Öncelik</th>
                    <th>Durum</th>
                    <th>Atanan</th>
                    <th>Açılış</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ticketlar as $t)
                    <tr style="cursor:pointer" onclick="window.location='/sistemyonetim/v2/ticket/{{ $t->id }}'">
                        <td class="nowrap"><strong>{{ $t->numara }}</strong></td>
                        <td><div class="sy-fw-600">{{ \Illuminate\Support\Str::limit($t->konu, 50) }}</div></td>
                        <td class="sy-fs-13">{{ $t->salon_adi ?: '—' }}</td>
                        <td><span class="sy-badge sy-badge-muted">{{ $t->kategori }}</span></td>
                        <td><span class="sy-badge sy-badge-{{ $oncelikRenk[$t->oncelik] ?? 'muted' }}">{{ $t->oncelik }}</span></td>
                        <td><span class="sy-badge sy-badge-{{ $durumRenk[$t->durum] ?? 'muted' }}">{{ $t->durum }}</span></td>
                        <td class="sy-fs-13">{{ $t->atanan_user_name ?: '—' }}</td>
                        <td class="nowrap sy-text-muted sy-fs-12">
                            {{ \Carbon\Carbon::parse($t->created_at)->format('d.m.Y H:i') }}<br>
                            {{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="sy-empty"><div class="icon mdi mdi-check-all"></div><div class="baslik">Talep yok</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="sy-pagination">{{ $ticketlar->links() }}</div>

@endsection
