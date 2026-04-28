@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2>Genel Bakış</h2>
        <div class="subtitle">Sistemin nabzı, son aktiviteler ve bekleyen işler.</div>
    </div>
    <div class="sy-flex-row">
        <a href="/sistemyonetim/v2/salonlar" class="sy-btn sy-btn-soft"><span class="mdi mdi-store"></span> Salonlar</a>
        <a href="/sistemyonetim/v2/ticket" class="sy-btn sy-btn-primary"><span class="mdi mdi-lifebuoy"></span> Destek</a>
    </div>
</div>

<div class="sy-metric-grid">
    <div class="sy-metric">
        <div class="icon-bg mdi mdi-store"></div>
        <div class="label">Toplam Salon</div>
        <div class="value">{{ number_format($metrikler['toplam_salon']) }}</div>
        <div class="delta">{{ $metrikler['aktif_salon'] }} aktif · {{ $metrikler['askida_salon'] }} askıda</div>
    </div>
    <div class="sy-metric success">
        <div class="icon-bg mdi mdi-account-plus"></div>
        <div class="label">Bu Hafta Yeni Salon</div>
        <div class="value">{{ $metrikler['hafta_yeni_salon'] }}</div>
        <div class="delta">Bugün: {{ $metrikler['bugun_yeni_salon'] }}</div>
    </div>
    <div class="sy-metric info">
        <div class="icon-bg mdi mdi-calendar-check"></div>
        <div class="label">Bugün Randevu</div>
        <div class="value">{{ number_format($metrikler['bugun_randevu']) }}</div>
        <div class="delta">Toplam: {{ number_format($metrikler['toplam_randevu']) }}</div>
    </div>
    <div class="sy-metric warning">
        <div class="icon-bg mdi mdi-lifebuoy"></div>
        <div class="label">Açık Talep</div>
        <div class="value">{{ $metrikler['acik_ticket'] }}</div>
        <div class="delta">Acil: {{ $metrikler['acil_ticket'] }}</div>
    </div>
    <div class="sy-metric">
        <div class="icon-bg mdi mdi-account-group"></div>
        <div class="label">Aktif Ekip</div>
        <div class="value">{{ $metrikler['aktif_ekip'] }}</div>
        <div class="delta">Yetkili: {{ $metrikler['toplam_yetkili'] }}</div>
    </div>
    <div class="sy-metric">
        <div class="icon-bg mdi mdi-account-tie"></div>
        <div class="label">Toplam Personel</div>
        <div class="value">{{ number_format($metrikler['toplam_personel']) }}</div>
    </div>
</div>

<div class="sy-grid-2-1">
    <div class="sy-card">
        <div class="sy-card-head">
            <h3>Son Aktiviteler</h3>
            <a href="/sistemyonetim/v2/aktivite-log" class="sy-btn sy-btn-sm sy-btn-soft">Tümü</a>
        </div>
        <div class="sy-card-body">
            @if($sonAktiviteler->isEmpty())
                <div class="sy-empty">
                    <div class="icon mdi mdi-timer-sand-empty"></div>
                    <div class="baslik">Henüz aktivite yok</div>
                </div>
            @else
                <div class="sy-timeline">
                    @foreach($sonAktiviteler as $log)
                        <div class="sy-timeline-item">
                            <div class="meta">
                                <strong>{{ $log->user_name ?: 'Sistem' }}</strong>
                                @if($log->user_rol)
                                    <span class="sy-badge sy-badge-muted" style="margin-left:6px">{{ $log->user_rol }}</span>
                                @endif
                                · {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                            </div>
                            <div class="body">
                                <strong>{{ $log->action }}</strong>
                                @if($log->target_type)
                                    → <span class="sy-text-muted">{{ $log->target_type }}</span>
                                @endif
                                @if($log->target_label)
                                    : {{ $log->target_label }}
                                @endif
                                @if($log->aciklama)
                                    <div class="sy-text-muted sy-fs-12">{{ $log->aciklama }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="sy-stack">
        <div class="sy-card">
            <div class="sy-card-head">
                <h3>Bekleyen Talepler</h3>
                <a href="/sistemyonetim/v2/ticket" class="sy-btn sy-btn-sm sy-btn-soft">Tümü</a>
            </div>
            <div class="sy-card-body tight">
                @if($bekleyenTicketlar->isEmpty())
                    <div class="sy-empty"><div class="icon mdi mdi-check-all"></div><div class="baslik">Tertemiz!</div><div>Bekleyen talep yok.</div></div>
                @else
                    @foreach($bekleyenTicketlar as $t)
                        <a href="/sistemyonetim/v2/ticket/{{ $t->id }}" style="display:block; padding: 12px 18px; border-bottom: 1px solid var(--sy-border); color: var(--sy-text);">
                            <div class="sy-flex-row" style="justify-content: space-between">
                                <strong>{{ $t->numara }}</strong>
                                @php
                                    $oncelikRenk = ['acil'=>'danger','yuksek'=>'warning','orta'=>'info','dusuk'=>'muted'];
                                @endphp
                                <span class="sy-badge sy-badge-{{ $oncelikRenk[$t->oncelik] ?? 'muted' }}">{{ $t->oncelik }}</span>
                            </div>
                            <div class="sy-fs-13" style="margin-top: 3px">{{ \Illuminate\Support\Str::limit($t->konu, 60) }}</div>
                            <div class="sy-text-muted sy-fs-12" style="margin-top: 2px">
                                {{ $t->salon_adi ?: '—' }} · {{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }}
                            </div>
                        </a>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="sy-card">
            <div class="sy-card-head"><h3>Son Girişler</h3></div>
            <div class="sy-card-body tight">
                @forelse($sonGirisler as $g)
                    <div style="padding: 10px 18px; border-bottom: 1px solid var(--sy-border); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div class="sy-fs-13"><strong>{{ \Illuminate\Support\Str::limit($g->email_attempt, 28) }}</strong></div>
                            <div class="sy-text-muted sy-fs-12">{{ $g->ip }} · {{ \Carbon\Carbon::parse($g->created_at)->diffForHumans() }}</div>
                        </div>
                        @if($g->basarili)
                            <span class="sy-badge sy-badge-success">OK</span>
                        @else
                            <span class="sy-badge sy-badge-danger">Hata</span>
                        @endif
                    </div>
                @empty
                    <div class="sy-empty"><div class="baslik">Kayıt yok</div></div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="sy-card sy-mt-24">
    <div class="sy-card-head"><h3>Son 7 Gün</h3></div>
    <div class="sy-card-body">
        <table class="sy-table">
            <thead>
                <tr><th>Tarih</th><th>Yeni Salon</th><th>Randevu</th></tr>
            </thead>
            <tbody>
                @php
                    $gunler = ['Mon'=>'Pazartesi','Tue'=>'Salı','Wed'=>'Çarşamba','Thu'=>'Perşembe','Fri'=>'Cuma','Sat'=>'Cumartesi','Sun'=>'Pazar'];
                @endphp
                @foreach($trend as $t)
                    @php $c = \Carbon\Carbon::parse($t['tarih']); @endphp
                    <tr>
                        <td>{{ $c->format('d.m.Y') }} ({{ $gunler[$c->format('D')] ?? '' }})</td>
                        <td><strong>{{ $t['salon'] }}</strong></td>
                        <td><strong>{{ $t['randevu'] }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
