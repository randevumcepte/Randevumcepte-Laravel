@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<style>
    .log-page-head {
        display:flex; align-items:center; justify-content:space-between;
        gap:16px; padding:16px 20px; margin-bottom:16px;
        background: linear-gradient(135deg, #faf5ff 0%, #fff 100%);
        border:1px solid #ede1f7; border-left:4px solid #5C008E;
        border-radius:12px;
    }
    .log-page-head h2 { color:#5C008E; margin:0; font-size:22px; font-weight:700; }
    .log-page-head .sub { color:#6b7280; margin-top:4px; font-size:13px; }

    .log-filters {
        background:#fff; border:1px solid #ececf1; border-radius:12px;
        padding:14px 16px; margin-bottom:16px;
        display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;
    }
    .log-filters .form-group { margin:0; }
    .log-filters label { font-size:12px; font-weight:600; color:#3a2e57; margin-bottom:4px; display:block; }
    .log-filters .form-control { min-width:160px; }
    .log-filters .btn-mor { background:#5C008E; color:#fff; border:none; font-weight:700; padding:8px 16px; border-radius:8px; }
    .log-filters .btn-mor:hover { background:#48006e; color:#fff; }
    .log-filters .btn-temiz { background:#f1f1f5; color:#3a2e57; border:none; padding:8px 14px; border-radius:8px; }

    .log-card { background:#fff; border:1px solid #ececf1; border-radius:12px; overflow:hidden; }
    .log-table { width:100%; border-collapse:collapse; }
    .log-table th, .log-table td { padding:12px 14px; text-align:left; border-bottom:1px solid #f1f1f5; vertical-align:top; font-size:13px; }
    .log-table th { background:#fafafc; font-weight:700; color:#3a2e57; font-size:12.5px; text-transform:uppercase; letter-spacing:.3px; }
    .log-table tr:last-child td { border-bottom:0; }
    .log-table tr:hover { background:#fafafc; }
    .log-meta-mute { color:#8b8694; font-size:11.5px; }
    .log-fw-600 { font-weight:600; color:#2d2143; }
    .log-badge {
        display:inline-block; padding:3px 10px; border-radius:20px;
        background:#f3eafa; color:#5C008E; font-size:11.5px; font-weight:700;
        white-space:nowrap;
    }
    .log-badge.action-sil, .log-badge.action-iptal { background:#fdecec; color:#c81e1e; }
    .log-badge.action-ekle, .log-badge.action-olustur { background:#e6f9ed; color:#0c7a3a; }
    .log-badge.action-guncelle, .log-badge.action-duzenle { background:#fff4e0; color:#a86200; }
    .log-badge.action-login, .log-badge.action-logout { background:#e7eefb; color:#1f4ec5; }

    .log-detail-pre {
        background:#f7f4ff; border:1px solid #e3d6f7; border-radius:6px;
        padding:8px; font-size:11.5px; margin-top:6px; max-width:380px; overflow:auto;
        white-space:pre-wrap; word-break:break-word;
    }
    .log-empty { padding:60px 20px; text-align:center; color:#8b8694; }
    .log-empty .icon { font-size:48px; margin-bottom:12px; opacity:.5; }

    .log-pagination { display:flex; justify-content:center; padding:16px 0; }
    .log-pagination .pagination { margin:0; }

    @media (max-width:768px) {
        .log-page-head { flex-direction:column; align-items:flex-start; }
        .log-filters { gap:8px; }
        .log-filters .form-control { min-width:120px; }
        .log-table thead { display:none; }
        .log-table tr { display:block; padding:10px 0; border-bottom:1px solid #f1f1f5; }
        .log-table td { display:block; border:0; padding:4px 14px; }
    }
</style>

<div class="log-page-head">
    <div>
        <h2><i class="bi bi-clock-history"></i> Log Hareketleri</h2>
        <div class="sub">İşletmenizde yapılan tüm işlemleri (kim, ne, ne zaman) buradan takip edebilirsiniz.</div>
    </div>
</div>

<form method="get" class="log-filters">
    @if(isset($_GET['sube']))
        <input type="hidden" name="sube" value="{{$_GET['sube']}}">
    @endif
    <div class="form-group" style="flex:1; min-width:220px;">
        <label>Ara</label>
        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="hedef, açıklama, kullanıcı...">
    </div>
    <div class="form-group">
        <label>İşlem</label>
        <select name="action" class="form-control">
            <option value="">Hepsi</option>
            @foreach($aksiyonlar as $a)
                <option value="{{ $a }}" {{ $action==$a?'selected':'' }}>{{ $a }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Kullanıcı</label>
        <select name="user_id" class="form-control">
            <option value="">Hepsi</option>
            @foreach($kullanicilar as $k)
                <option value="{{ $k->user_id }}" {{ $user_id==$k->user_id?'selected':'' }}>{{ $k->user_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Tarih</label>
        <input type="date" name="tarih" value="{{ $tarih }}" class="form-control">
    </div>
    <div class="form-group">
        <button class="btn-mor" type="submit"><i class="fa fa-search"></i> Filtrele</button>
        <a href="/isletmeyonetim/log-hareketleri{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="btn-temiz" style="text-decoration:none;display:inline-block;line-height:1;">Sıfırla</a>
    </div>
</form>

<div class="log-card">
    <div style="overflow-x:auto">
        <table class="log-table">
            <thead>
                <tr>
                    <th style="width:140px">Zaman</th>
                    <th style="width:170px">Kullanıcı</th>
                    <th style="width:140px">İşlem</th>
                    <th>Hedef</th>
                    <th>Açıklama</th>
                    <th style="width:120px">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loglar as $log)
                    @php
                        $actionClass = '';
                        if (str_contains($log->action, 'sil')) $actionClass = 'action-sil';
                        elseif (str_contains($log->action, 'iptal')) $actionClass = 'action-iptal';
                        elseif (str_contains($log->action, 'ekle') || str_contains($log->action, 'olustur')) $actionClass = 'action-ekle';
                        elseif (str_contains($log->action, 'guncelle') || str_contains($log->action, 'duzenle')) $actionClass = 'action-guncelle';
                        elseif ($log->action == 'login' || $log->action == 'logout') $actionClass = 'action-login';
                    @endphp
                    <tr>
                        <td class="log-meta-mute" style="font-size:12px">
                            <div class="log-fw-600" style="color:#3a2e57;">{{ \Carbon\Carbon::parse($log->created_at)->format('d.m.Y H:i:s') }}</div>
                            <div>{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="log-fw-600">{{ $log->user_name ?: 'Sistem' }}</div>
                            <div class="log-meta-mute">{{ $log->user_rol }}</div>
                        </td>
                        <td><span class="log-badge {{ $actionClass }}">{{ $log->action }}</span></td>
                        <td>
                            @if($log->target_type)
                                <div class="log-meta-mute">{{ $log->target_type }}#{{ $log->target_id }}</div>
                            @endif
                            <strong>{{ $log->target_label ?: '—' }}</strong>
                        </td>
                        <td>
                            {{ $log->aciklama ?: '—' }}
                            @if($log->meta)
                                <details style="margin-top:4px">
                                    <summary class="log-meta-mute" style="cursor:pointer">Detay</summary>
                                    <pre class="log-detail-pre">{{ json_encode(json_decode($log->meta), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @endif
                        </td>
                        <td class="log-meta-mute" style="white-space:nowrap">{{ $log->ip }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="log-empty">
                                <div class="icon"><i class="bi bi-search"></i></div>
                                <div style="font-weight:600;color:#3a2e57;">Kayıt bulunamadı</div>
                                <div style="font-size:13px;margin-top:4px;">Bu kriterlere uyan log hareketi yok.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="log-pagination">{{ $loglar->links() }}</div>

@endsection
