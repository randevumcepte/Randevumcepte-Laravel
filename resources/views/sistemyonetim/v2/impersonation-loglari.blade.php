@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2>Salon Hesabına Giriş Logları</h2>
        <div class="subtitle">Hangi destek personeli, hangi salona, hangi sebeple, ne kadar süre girdi</div>
    </div>
    <div class="sy-flex-row">
        <a href="/sistemyonetim/v2/guvenlik/girisler" class="sy-btn">Girişler</a>
        <a href="/sistemyonetim/v2/guvenlik/impersonation" class="sy-btn sy-btn-soft">Salon Hesabı Girişleri</a>
    </div>
</div>

<div class="sy-card">
    <div class="sy-card-body tight">
        <table class="sy-table">
            <thead>
                <tr><th>Personel</th><th>Salon</th><th>Sebep</th><th>Başlangıç</th><th>Süre</th><th>IP</th></tr>
            </thead>
            <tbody>
                @forelse($loglar as $i)
                    <tr>
                        <td><strong>{{ $i->user_name }}</strong></td>
                        <td>
                            <a href="/sistemyonetim/v2/salon/{{ $i->salon_id }}">{{ $i->salon_adi }}</a>
                            <div class="sy-text-muted sy-fs-12">{{ $i->isletme_yetkili_email }}</div>
                        </td>
                        <td class="sy-fs-13">
                            {{ $i->sebep ?: '—' }}
                            @if($i->ticket_id)<br><a href="/sistemyonetim/v2/ticket/{{ $i->ticket_id }}" class="sy-fs-12">→ Ticket</a>@endif
                        </td>
                        <td class="nowrap sy-fs-12">{{ \Carbon\Carbon::parse($i->baslangic_tarihi)->format('d.m.Y H:i:s') }}</td>
                        <td class="sy-fs-13">
                            @if($i->bitis_tarihi)
                                {{ \Carbon\Carbon::parse($i->baslangic_tarihi)->diffForHumans($i->bitis_tarihi, true) }}
                            @else
                                <span class="sy-badge sy-badge-warning">Aktif</span>
                            @endif
                        </td>
                        <td class="sy-text-muted sy-fs-12">{{ $i->ip }}</td>
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
