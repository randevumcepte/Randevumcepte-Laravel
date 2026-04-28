@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2>Giriş Logları</h2>
        <div class="subtitle">Tüm sistem yönetim girişleri (başarılı + başarısız)</div>
    </div>
    <div class="sy-flex-row">
        <a href="/sistemyonetim/v2/guvenlik/girisler" class="sy-btn sy-btn-soft">Girişler</a>
        <a href="/sistemyonetim/v2/guvenlik/impersonation" class="sy-btn">Salon Hesabı Girişleri</a>
    </div>
</div>

<div class="sy-card">
    <div class="sy-card-body tight">
        <table class="sy-table">
            <thead>
                <tr><th>Zaman</th><th>E-posta</th><th>Durum</th><th>Hata</th><th>IP</th><th>User-Agent</th></tr>
            </thead>
            <tbody>
                @forelse($loglar as $g)
                    <tr>
                        <td class="nowrap sy-fs-12">{{ \Carbon\Carbon::parse($g->created_at)->format('d.m.Y H:i:s') }}</td>
                        <td><strong>{{ $g->email_attempt }}</strong></td>
                        <td>
                            @if($g->basarili)
                                <span class="sy-badge sy-badge-success">Başarılı</span>
                            @else
                                <span class="sy-badge sy-badge-danger">Başarısız</span>
                            @endif
                        </td>
                        <td class="sy-fs-13">{{ $g->hata ?: '—' }}</td>
                        <td class="sy-text-muted sy-fs-12">{{ $g->ip }}</td>
                        <td class="sy-text-muted sy-fs-12">{{ \Illuminate\Support\Str::limit($g->user_agent, 40) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="sy-empty"><div class="baslik">Kayıt yok</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="sy-pagination">{{ $loglar->links() }}</div>

@endsection
