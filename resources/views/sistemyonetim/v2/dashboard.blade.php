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

<div class="sy-grid-2 sy-mt-24">
    <div class="sy-card">
        <div class="sy-card-head">
            <h3>Trend</h3>
            <div class="sy-flex-row">
                <select id="chartGun" class="sy-select" style="height:32px;padding:5px 10px;font-size:12px">
                    <option value="7">7 gün</option>
                    <option value="30" selected>30 gün</option>
                    <option value="90">90 gün</option>
                </select>
            </div>
        </div>
        <div class="sy-card-body">
            <canvas id="chartTrend" height="120"></canvas>
        </div>
    </div>

    <div class="sy-card">
        <div class="sy-card-head"><h3>Talep Dağılımı</h3></div>
        <div class="sy-card-body">
            <div class="sy-grid-2">
                <div>
                    <div class="sy-text-muted sy-fs-12" style="margin-bottom:6px;text-align:center">Kategori</div>
                    <canvas id="chartKategori" height="180"></canvas>
                </div>
                <div>
                    <div class="sy-text-muted sy-fs-12" style="margin-bottom:6px;text-align:center">Durum</div>
                    <canvas id="chartDurum" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
let chartTrend = null, chartKategori = null, chartDurum = null;
const morRenkler = ['#6d3aaa','#8a5cc7','#d9b3f5','#5C008E','#a872e8','#7B2FB8','#9D5DC8','#bf91e6','#5dd4d8','#f3a8c8','#d99a1f','#d04d5e'];

function loadCharts(gun) {
    fetch('/sistemyonetim/v2/api/dashboard-chart?gun=' + gun)
        .then(r => r.json())
        .then(d => {
            // Trend chart
            const ctx = document.getElementById('chartTrend').getContext('2d');
            if (chartTrend) chartTrend.destroy();
            chartTrend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: d.tarihler,
                    datasets: [
                        { label: 'Yeni Salon', data: d.salon, borderColor: '#6d3aaa', backgroundColor: 'rgba(109,58,170,0.1)', tension: 0.35, fill: true },
                        { label: 'Randevu',    data: d.randevu, borderColor: '#5dd4d8', backgroundColor: 'rgba(93,212,216,0.1)', tension: 0.35, fill: true, yAxisID: 'y1' },
                        { label: 'Talep',      data: d.ticket, borderColor: '#d99a1f', backgroundColor: 'rgba(217,154,31,0.1)', tension: 0.35, fill: true },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
                    scales: {
                        y:  { beginAtZero: true, position: 'left' },
                        y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
                    }
                }
            });

            // Kategori
            const kLabels = Object.keys(d.kategori_dagilim);
            const kData = Object.values(d.kategori_dagilim);
            if (chartKategori) chartKategori.destroy();
            chartKategori = new Chart(document.getElementById('chartKategori'), {
                type: 'doughnut',
                data: { labels: kLabels, datasets: [{ data: kData, backgroundColor: morRenkler, borderWidth: 0 }] },
                options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } } }
            });

            // Durum
            const sLabels = Object.keys(d.durum_dagilim);
            const sData = Object.values(d.durum_dagilim);
            const sRenk = sLabels.map(l => ({ acik: '#d04d5e', islemde: '#d99a1f', bekliyor: '#4a8bdc', cozumlendi: '#2cae71', kapali: '#777589' }[l] || '#777589'));
            if (chartDurum) chartDurum.destroy();
            chartDurum = new Chart(document.getElementById('chartDurum'), {
                type: 'doughnut',
                data: { labels: sLabels, datasets: [{ data: sData, backgroundColor: sRenk, borderWidth: 0 }] },
                options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } } }
            });
        });
}
document.addEventListener('DOMContentLoaded', () => {
    loadCharts(30);
    document.getElementById('chartGun').addEventListener('change', e => loadCharts(e.target.value));
});
</script>
@endpush

@endsection
