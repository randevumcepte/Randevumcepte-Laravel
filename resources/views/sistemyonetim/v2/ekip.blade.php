@extends('sistemyonetim.v2.layout')

@section('content')

@php
    $rolEt = ['super_admin'=>'Süper Admin','yonetici'=>'Yönetici','destek'=>'Destek','izleyici'=>'İzleyici'];
    $rolRenk = ['super_admin'=>'danger','yonetici'=>'info','destek'=>'success','izleyici'=>'muted'];
    $rolMevcut = Auth::guard('sistemyonetim')->user()->rol ?? (Auth::guard('sistemyonetim')->user()->admin == 1 ? 'super_admin' : 'destek');
@endphp

<div class="sy-page-head">
    <div>
        <h2>Ekip & Roller</h2>
        <div class="subtitle">Ekip üyeleri, rol atamaları ve son giriş bilgileri</div>
    </div>
    @if($rolMevcut === 'super_admin')
    <a href="/sistemyonetim/v2/ekip/yeni" class="sy-btn sy-btn-primary"><span class="mdi mdi-plus"></span> Yeni Üye</a>
    @endif
</div>

<div class="sy-card">
    <div class="sy-card-body tight">
        <table class="sy-table">
            <thead>
                <tr>
                    <th>Üye</th>
                    <th>E-posta</th>
                    <th>Telefon</th>
                    <th>Rol</th>
                    <th>Son Giriş</th>
                    <th>Durum</th>
                    <th class="sy-text-right">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ekip as $u)
                    @php $rolU = $u->rol ?: ($u->admin == 1 ? 'super_admin' : 'destek'); @endphp
                    <tr>
                        <td>
                            <div class="sy-flex-row">
                                <div style="width:32px;height:32px;border-radius:50%;background:var(--sy-primary-soft);color:var(--sy-primary);display:flex;align-items:center;justify-content:center;font-weight:600">
                                    {{ mb_substr($u->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="sy-fw-600">{{ $u->name }}</div>
                                    <div class="sy-text-muted sy-fs-12">ID: {{ $u->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->telefon ?: '—' }}</td>
                        <td><span class="sy-badge sy-badge-{{ $rolRenk[$rolU] ?? 'muted' }}">{{ $rolEt[$rolU] ?? $rolU }}</span></td>
                        <td class="sy-text-muted sy-fs-12">
                            @if($u->son_giris_tarihi)
                                {{ \Carbon\Carbon::parse($u->son_giris_tarihi)->diffForHumans() }}<br>
                                <span class="sy-text-soft">{{ $u->son_giris_ip }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($u->aktif)
                                <span class="sy-badge sy-badge-success">Aktif</span>
                            @else
                                <span class="sy-badge sy-badge-muted">Pasif</span>
                            @endif
                        </td>
                        <td class="sy-text-right nowrap">
                            @if($rolMevcut === 'super_admin')
                                <a href="/sistemyonetim/v2/ekip/{{ $u->id }}/duzenle" class="sy-btn sy-btn-sm sy-btn-soft"><span class="mdi mdi-pencil"></span></a>
                                @if($u->aktif && $u->id != Auth::guard('sistemyonetim')->user()->id)
                                <form method="post" action="/sistemyonetim/v2/ekip/{{ $u->id }}/pasif" style="display:inline" onsubmit="return confirm('Pasif edilsin mi?')">
                                    @csrf
                                    <button class="sy-btn sy-btn-sm sy-btn-danger"><span class="mdi mdi-cancel"></span></button>
                                </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="sy-card sy-mt-24">
    <div class="sy-card-head"><h3>Rol İzinleri</h3></div>
    <div class="sy-card-body">
        <table class="sy-table">
            <thead><tr><th>Yetenek</th><th>Süper Admin</th><th>Yönetici</th><th>Destek</th><th>İzleyici</th></tr></thead>
            <tbody>
                <tr><td>Tüm salonları görme</td><td>✅</td><td>✅</td><td>Atananlar</td><td>✅</td></tr>
                <tr><td>Salon hesabına giriş</td><td>✅</td><td>✅</td><td>Atananlar</td><td>—</td></tr>
                <tr><td>Salon askıya alma</td><td>✅</td><td>✅</td><td>—</td><td>—</td></tr>
                <tr><td>Müşteri temsilcisi atama</td><td>✅</td><td>✅</td><td>—</td><td>—</td></tr>
                <tr><td>Ticket cevaplama</td><td>✅</td><td>✅</td><td>✅</td><td>—</td></tr>
                <tr><td>Ekip üyesi ekle/düzenle</td><td>✅</td><td>—</td><td>—</td><td>—</td></tr>
                <tr><td>Aktivite logu görme</td><td>✅</td><td>✅</td><td>—</td><td>✅</td></tr>
                <tr><td>Sistem sağlık / güvenlik</td><td>✅</td><td>✅</td><td>—</td><td>—</td></tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
