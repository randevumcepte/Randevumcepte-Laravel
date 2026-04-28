@extends('sistemyonetim.v2.layout')

@section('content')

@php
    $rolEt = ['super_admin'=>'Süper Admin','yonetici'=>'Yönetici','destek'=>'Destek','izleyici'=>'İzleyici'];
    $rolU = $u->rol ?: ($u->admin == 1 ? 'super_admin' : 'destek');
@endphp

<div class="sy-page-head">
    <div>
        <h2>Profilim</h2>
        <div class="subtitle">Kişisel bilgileriniz, şifre değişikliği ve aktivite geçmişiniz</div>
    </div>
</div>

<div class="sy-grid-2">
    <div class="sy-card">
        <div class="sy-card-head"><h3>Kişisel Bilgiler</h3></div>
        <div class="sy-card-body">
            <form method="post" action="/sistemyonetim/v2/profil">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="sy-form-group">
                    <label>Ad Soyad</label>
                    <input type="text" name="name" class="sy-input" value="{{ old('name', $u->name) }}" required>
                </div>
                <div class="sy-form-group">
                    <label>E-posta</label>
                    <input type="email" name="email" class="sy-input" value="{{ old('email', $u->email) }}" required>
                </div>
                <div class="sy-form-group">
                    <label>Telefon</label>
                    <input type="text" name="telefon" class="sy-input" value="{{ old('telefon', $u->telefon) }}">
                </div>
                <div class="sy-form-group">
                    <label>Rol</label>
                    <input type="text" class="sy-input" value="{{ $rolEt[$rolU] ?? $rolU }}" disabled>
                    <div class="sy-text-muted sy-fs-12">Rol değişikliği için Süper Admin'e başvurun.</div>
                </div>
                <button class="sy-btn sy-btn-primary"><span class="mdi mdi-content-save"></span> Kaydet</button>
            </form>
        </div>
    </div>

    <div class="sy-card">
        <div class="sy-card-head"><h3>Şifre Değiştir</h3></div>
        <div class="sy-card-body">
            <form method="post" action="/sistemyonetim/v2/profil/sifre">
                @csrf
                <div class="sy-form-group">
                    <label>Mevcut Şifre</label>
                    <input type="password" name="mevcut_sifre" class="sy-input" required>
                </div>
                <div class="sy-form-group">
                    <label>Yeni Şifre (en az 6 karakter)</label>
                    <input type="password" name="yeni_sifre" class="sy-input" required minlength="6">
                </div>
                <div class="sy-form-group">
                    <label>Yeni Şifre (Tekrar)</label>
                    <input type="password" name="yeni_sifre_confirmation" class="sy-input" required minlength="6">
                </div>
                <button class="sy-btn sy-btn-primary"><span class="mdi mdi-key-change"></span> Şifre Güncelle</button>
            </form>
        </div>
    </div>
</div>

<div class="sy-grid-2 sy-mt-24">
    <div class="sy-card">
        <div class="sy-card-head"><h3>Son Girişler</h3></div>
        <div class="sy-card-body tight">
            @forelse($sonGirisler as $g)
                <div style="padding:10px 18px;border-bottom:1px solid var(--sy-border)">
                    <div class="sy-flex-row" style="justify-content:space-between">
                        <div>
                            <strong>{{ $g->ip ?: '—' }}</strong>
                            <span class="sy-text-muted sy-fs-12">{{ \Illuminate\Support\Str::limit($g->user_agent, 60) }}</span>
                        </div>
                        <span class="sy-badge sy-badge-{{ $g->basarili ? 'success' : 'danger' }}">{{ $g->basarili ? 'OK' : 'Hata' }}</span>
                    </div>
                    <div class="sy-text-muted sy-fs-12">{{ \Carbon\Carbon::parse($g->created_at)->format('d.m.Y H:i:s') }}</div>
                </div>
            @empty
                <div class="sy-empty"><div class="baslik">Kayıt yok</div></div>
            @endforelse
        </div>
    </div>

    <div class="sy-card">
        <div class="sy-card-head"><h3>Son Aktiviteleriniz</h3></div>
        <div class="sy-card-body tight">
            @forelse($sonAktiviteler as $a)
                <div style="padding:10px 18px;border-bottom:1px solid var(--sy-border)">
                    <div class="sy-fw-600">{{ $a->action }} <span class="sy-text-muted sy-fs-12">→ {{ $a->target_type }}: {{ $a->target_label }}</span></div>
                    <div class="sy-text-muted sy-fs-12">{{ \Carbon\Carbon::parse($a->created_at)->format('d.m.Y H:i') }} · {{ $a->ip }}</div>
                </div>
            @empty
                <div class="sy-empty"><div class="baslik">Henüz aktivite yok</div></div>
            @endforelse
        </div>
    </div>
</div>

@endsection
