@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2>Aktivite Logu</h2>
        <div class="subtitle">Tüm sistem yöneticilerinin yaptığı işlemler — kim, ne, ne zaman</div>
    </div>
    <a href="/sistemyonetim/v2/aktivite-log/csv" class="sy-btn"><span class="mdi mdi-file-download"></span> CSV İndir</a>
</div>

<form method="get" class="sy-filters">
    <div class="sy-form-group">
        <label>Ara</label>
        <input type="text" name="q" value="{{ $q }}" class="sy-input" placeholder="hedef, açıklama, kullanıcı...">
    </div>
    <div class="sy-form-group" style="max-width:200px">
        <label>İşlem</label>
        <select name="action" class="sy-select">
            <option value="">Hepsi</option>
            @foreach($aksiyonlar as $a)
                <option value="{{ $a }}" {{ $action==$a?'selected':'' }}>{{ $a }}</option>
            @endforeach
        </select>
    </div>
    <div class="sy-form-group" style="max-width:200px">
        <label>Kullanıcı</label>
        <select name="user_id" class="sy-select">
            <option value="">Hepsi</option>
            @foreach($kullanicilar as $k)
                <option value="{{ $k->id }}" {{ $user_id==$k->id?'selected':'' }}>{{ $k->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="sy-form-group" style="max-width:160px">
        <label>Tarih</label>
        <input type="date" name="tarih" value="{{ $tarih }}" class="sy-input">
    </div>
    <button class="sy-btn sy-btn-primary"><span class="mdi mdi-magnify"></span> Filtrele</button>
    <a href="/sistemyonetim/v2/aktivite-log" class="sy-btn">Sıfırla</a>
</form>

<div class="sy-card">
    <div class="sy-card-body tight">
        <table class="sy-table">
            <thead>
                <tr>
                    <th>Zaman</th>
                    <th>Kullanıcı</th>
                    <th>İşlem</th>
                    <th>Hedef</th>
                    <th>Açıklama</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loglar as $log)
                    <tr>
                        <td class="nowrap sy-fs-12">
                            <div>{{ \Carbon\Carbon::parse($log->created_at)->format('d.m.Y H:i:s') }}</div>
                            <div class="sy-text-muted">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="sy-fw-600">{{ $log->user_name ?: 'Sistem' }}</div>
                            <div class="sy-text-muted sy-fs-12">{{ $log->user_rol }}</div>
                        </td>
                        <td><span class="sy-badge">{{ $log->action }}</span></td>
                        <td>
                            @if($log->target_type)
                                <span class="sy-text-muted sy-fs-12">{{ $log->target_type }}#{{ $log->target_id }}</span><br>
                            @endif
                            <strong>{{ $log->target_label ?: '—' }}</strong>
                        </td>
                        <td class="sy-fs-13">
                            {{ $log->aciklama ?: '—' }}
                            @if($log->meta)
                                <details style="margin-top:4px">
                                    <summary class="sy-text-muted sy-fs-12" style="cursor:pointer">Detay</summary>
                                    <pre style="background:#f7f4ff; border:1px solid #e3d6f7; border-radius:6px; padding:8px; font-size:11.5px; margin-top:4px; max-width:380px; overflow:auto">{{ json_encode(json_decode($log->meta), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @endif
                        </td>
                        <td class="sy-text-muted sy-fs-12 nowrap">{{ $log->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="sy-empty"><div class="icon mdi mdi-magnify-close"></div><div class="baslik">Kayıt yok</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="sy-pagination">{{ $loglar->links() }}</div>

@endsection
