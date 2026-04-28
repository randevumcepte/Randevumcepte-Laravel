@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2>{{ $duzenleniyor ? $duzenleniyor->name.' — Düzenle' : 'Yeni Ekip Üyesi' }}</h2>
        <div class="subtitle">Rol ve erişim ayarları</div>
    </div>
    <a href="/sistemyonetim/v2/ekip" class="sy-btn"><span class="mdi mdi-arrow-left"></span> Geri</a>
</div>

<div class="sy-card" style="max-width:720px">
    <form method="post" action="{{ $duzenleniyor ? '/sistemyonetim/v2/ekip/'.$duzenleniyor->id : '/sistemyonetim/v2/ekip' }}">
        @csrf
        @if($duzenleniyor)
            <input type="hidden" name="_method" value="PUT">
        @endif
        <div class="sy-card-body">
            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>Ad Soyad *</label>
                    <input type="text" name="name" class="sy-input" required value="{{ old('name', $duzenleniyor->name ?? '') }}">
                </div>
                <div class="sy-form-group">
                    <label>E-posta *</label>
                    <input type="email" name="email" class="sy-input" required value="{{ old('email', $duzenleniyor->email ?? '') }}">
                </div>
            </div>

            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>Telefon</label>
                    <input type="text" name="telefon" class="sy-input" value="{{ old('telefon', $duzenleniyor->telefon ?? '') }}">
                </div>
                <div class="sy-form-group">
                    <label>Rol *</label>
                    @php $r = old('rol', $duzenleniyor->rol ?? 'destek'); @endphp
                    <select name="rol" class="sy-select" required>
                        <option value="super_admin" {{ $r=='super_admin'?'selected':'' }}>Süper Admin · tüm yetkiler</option>
                        <option value="yonetici" {{ $r=='yonetici'?'selected':'' }}>Yönetici · ekip dışı tüm yetkiler</option>
                        <option value="destek" {{ $r=='destek'?'selected':'' }}>Destek · atanan salonlara giriş + ticket</option>
                        <option value="izleyici" {{ $r=='izleyici'?'selected':'' }}>İzleyici · sadece okuma</option>
                    </select>
                </div>
            </div>

            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>Şifre {{ $duzenleniyor ? '(boş bırakılırsa değişmez)' : '*' }}</label>
                    <input type="password" name="password" class="sy-input" {{ $duzenleniyor ? '' : 'required' }} minlength="6">
                </div>
                <div class="sy-form-group">
                    <label>Durum</label>
                    <label class="sy-flex-row" style="margin-top:10px">
                        <input type="checkbox" name="aktif" value="1" {{ ($duzenleniyor && $duzenleniyor->aktif) || !$duzenleniyor ? 'checked' : '' }}>
                        Aktif (giriş yapabilir)
                    </label>
                </div>
            </div>

            <div class="sy-flex-row" style="justify-content:flex-end">
                <a href="/sistemyonetim/v2/ekip" class="sy-btn">İptal</a>
                <button class="sy-btn sy-btn-primary"><span class="mdi mdi-content-save"></span> Kaydet</button>
            </div>
        </div>
    </form>
</div>

@endsection
