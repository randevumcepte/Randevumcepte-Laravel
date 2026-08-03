@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2>Güvenlik Duvarı</h2>
        <div class="subtitle">Flood / SSH brute-force otomatik engelleme · saldırı anında WhatsApp alarm</div>
    </div>
    <div class="sy-flex-row">
        <a href="/sistemyonetim/v2/sistem-saglik" class="sy-btn sy-btn-soft">Sistem Sağlık</a>
        <a href="/sistemyonetim/v2/guvenlik/girisler" class="sy-btn sy-btn-soft">Giriş Logları</a>
    </div>
</div>

{{-- Özet kartlar --}}
<div class="sy-metric-grid">
    <div class="sy-metric danger">
        <div class="icon-bg mdi mdi-shield-lock"></div>
        <div class="label">Şu An Engelli IP</div>
        <div class="value">{{ $ozet['engelli_aktif'] }}</div>
        <div class="delta">Aktif ban (son 24s)</div>
    </div>
    <div class="sy-metric warning">
        <div class="icon-bg mdi mdi-cancel"></div>
        <div class="label">Son 24 Saat Engel</div>
        <div class="value">{{ $ozet['son24_engel'] }}</div>
        <div class="delta">Otomatik engelleme sayısı</div>
    </div>
    <div class="sy-metric {{ $ozet['son_load'] ? 'warning' : 'success' }}">
        <div class="icon-bg mdi mdi-speedometer"></div>
        <div class="label">Son Yük Uyarısı</div>
        <div class="value">{{ $ozet['son_load'] ? number_format(($ozet['son_load']->deger ?? 0)/100, 1) : '—' }}</div>
        <div class="delta">{{ $ozet['son_load'] ? \Carbon\Carbon::parse($ozet['son_load']->created_at)->format('d.m H:i') : 'Yük uyarısı yok' }}</div>
    </div>
    <div class="sy-metric">
        <div class="icon-bg mdi mdi-clock-outline"></div>
        <div class="label">Son Olay</div>
        <div class="value sy-fs-13" style="font-size:15px">{{ $ozet['son_olay'] ? \Carbon\Carbon::parse($ozet['son_olay']->created_at)->format('d.m H:i:s') : '—' }}</div>
        <div class="delta">{{ $ozet['son_olay'] ? ($ozet['son_olay']->tur . ' · ' . $ozet['son_olay']->aksiyon) : 'Kayıt yok' }}</div>
    </div>
</div>

{{-- Manuel engelle / whitelist ekle --}}
<div class="sy-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
    <div class="sy-card">
        <div class="sy-card-head"><strong>Whitelist’e Ekle</strong><span class="sy-text-muted sy-fs-12"> — bu IP asla engellenmez</span></div>
        <div class="sy-card-body">
            <form method="POST" action="/sistemyonetim/v2/guvenlik-duvari/whitelist" class="sy-flex-row" style="gap:8px;flex-wrap:wrap">
                @csrf
                <input type="text" name="ip" placeholder="Örn: 151.135.241.18" class="sy-input" required style="flex:1;min-width:160px">
                <input type="text" name="aciklama" placeholder="Açıklama (opsiyonel)" class="sy-input" style="flex:1;min-width:140px">
                <button class="sy-btn sy-btn-success">Ekle</button>
            </form>
            <div class="sy-text-muted sy-fs-12" style="margin-top:8px">Kendi ofis/ev/ekip IP’lerini buraya ekle ki yanlışlıkla engellenmesin.</div>
        </div>
    </div>
    <div class="sy-card">
        <div class="sy-card-head"><strong>Kalıcı Engelle (Blacklist)</strong></div>
        <div class="sy-card-body">
            <form method="POST" action="/sistemyonetim/v2/guvenlik-duvari/blacklist" class="sy-flex-row" style="gap:8px;flex-wrap:wrap">
                @csrf
                <input type="text" name="ip" placeholder="Engellenecek IP" class="sy-input" required style="flex:1;min-width:160px">
                <input type="text" name="aciklama" placeholder="Sebep (opsiyonel)" class="sy-input" style="flex:1;min-width:140px">
                <button class="sy-btn sy-btn-danger">Engelle</button>
            </form>
            <div class="sy-text-muted sy-fs-12" style="margin-top:8px">Bilinen saldırgan IP’yi elle kalıcı engelle — watchdog en geç 1 dk içinde uygular.</div>
        </div>
    </div>
</div>

{{-- Aktif engelli IP'ler --}}
<div class="sy-card" style="margin-top:16px">
    <div class="sy-card-head"><strong>Aktif Engellenen IP’ler</strong> <span class="sy-badge sy-badge-danger">{{ count($engelli) }}</span></div>
    <div class="sy-card-body tight">
        <table class="sy-table">
            <thead>
                <tr><th>IP</th><th>Tür</th><th>Değer</th><th>Detay</th><th>Zaman</th><th style="text-align:right">İşlem</th></tr>
            </thead>
            <tbody>
                @forelse($engelli as $o)
                    <tr>
                        <td><strong>{{ $o->ip }}</strong></td>
                        <td>
                            @if($o->tur === 'flood')<span class="sy-badge sy-badge-danger">Flood</span>
                            @elseif($o->tur === 'ssh_brute')<span class="sy-badge sy-badge-warning">SSH Brute</span>
                            @else<span class="sy-badge">{{ $o->tur }}</span>@endif
                        </td>
                        <td class="sy-fs-13">{{ $o->deger !== null ? $o->deger : '—' }}{{ $o->tur === 'flood' ? ' bağ.' : ($o->tur === 'ssh_brute' ? ' deneme' : '') }}</td>
                        <td class="sy-fs-13 sy-text-muted">{{ $o->detay ?: '—' }}</td>
                        <td class="nowrap sy-fs-12">{{ \Carbon\Carbon::parse($o->created_at)->format('d.m.Y H:i:s') }}</td>
                        <td style="text-align:right">
                            <form method="POST" action="/sistemyonetim/v2/guvenlik-duvari/unban" style="display:inline" onsubmit="return confirm('{{ $o->ip }} engeli kaldırılsın (whitelist’e eklenir)?')">
                                @csrf
                                <input type="hidden" name="ip" value="{{ $o->ip }}">
                                <button class="sy-btn sy-btn-soft sy-btn-sm">Engeli Kaldır</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="sy-empty"><div class="baslik">Şu an engelli IP yok</div><div>Sistem temiz görünüyor.</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Whitelist + Blacklist --}}
<div class="sy-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
    <div class="sy-card">
        <div class="sy-card-head"><strong>Whitelist</strong> <span class="sy-badge sy-badge-success">{{ count($whitelist) }}</span></div>
        <div class="sy-card-body tight">
            <table class="sy-table">
                <thead><tr><th>IP</th><th>Açıklama</th><th>Ekleyen</th><th></th></tr></thead>
                <tbody>
                    @forelse($whitelist as $w)
                        <tr>
                            <td><strong>{{ $w->ip }}</strong></td>
                            <td class="sy-fs-13">{{ $w->aciklama ?: '—' }}</td>
                            <td class="sy-fs-12 sy-text-muted">{{ $w->ekleyen ?: '—' }}</td>
                            <td style="text-align:right">
                                <form method="POST" action="/sistemyonetim/v2/guvenlik-duvari/whitelist-sil" style="display:inline" onsubmit="return confirm('{{ $w->ip }} whitelist’ten silinsin?')">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $w->id }}">
                                    <button class="sy-btn sy-btn-soft sy-btn-sm">Sil</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="sy-empty"><div class="baslik">Whitelist boş</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="sy-card">
        <div class="sy-card-head"><strong>Blacklist (kalıcı)</strong> <span class="sy-badge sy-badge-danger">{{ count($blacklist) }}</span></div>
        <div class="sy-card-body tight">
            <table class="sy-table">
                <thead><tr><th>IP</th><th>Sebep</th><th>Ekleyen</th><th></th></tr></thead>
                <tbody>
                    @forelse($blacklist as $b)
                        <tr>
                            <td><strong>{{ $b->ip }}</strong></td>
                            <td class="sy-fs-13">{{ $b->aciklama ?: '—' }}</td>
                            <td class="sy-fs-12 sy-text-muted">{{ $b->ekleyen ?: '—' }}</td>
                            <td style="text-align:right">
                                <form method="POST" action="/sistemyonetim/v2/guvenlik-duvari/whitelist-sil" style="display:inline" onsubmit="return confirm('{{ $b->ip }} engeli kaldırılsın?')">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $b->id }}">
                                    <button class="sy-btn sy-btn-soft sy-btn-sm">Kaldır</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="sy-empty"><div class="baslik">Blacklist boş</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Son olaylar --}}
<div class="sy-card" style="margin-top:16px">
    <div class="sy-card-head"><strong>Son Olaylar</strong> <span class="sy-text-muted sy-fs-12"> — son 100 kayıt</span></div>
    <div class="sy-card-body tight">
        <table class="sy-table">
            <thead>
                <tr><th>Zaman</th><th>Tür</th><th>IP</th><th>Değer</th><th>Aksiyon</th><th>Detay</th></tr>
            </thead>
            <tbody>
                @forelse($sonOlaylar as $o)
                    <tr>
                        <td class="nowrap sy-fs-12">{{ \Carbon\Carbon::parse($o->created_at)->format('d.m.Y H:i:s') }}</td>
                        <td class="sy-fs-13">
                            @if($o->tur === 'flood')Flood
                            @elseif($o->tur === 'ssh_brute')SSH Brute
                            @elseif($o->tur === 'load_yuksek')Yüksek Yük
                            @else{{ $o->tur }}@endif
                        </td>
                        <td class="sy-fs-13">{{ $o->ip ?: '—' }}</td>
                        <td class="sy-fs-13">{{ $o->tur === 'load_yuksek' && $o->deger !== null ? number_format($o->deger/100, 1) : ($o->deger !== null ? $o->deger : '—') }}</td>
                        <td>
                            @if($o->aksiyon === 'engellendi')<span class="sy-badge sy-badge-danger">Engellendi</span>
                            @elseif($o->aksiyon === 'uyari')<span class="sy-badge sy-badge-warning">Uyarı</span>
                            @else<span class="sy-badge">{{ $o->aksiyon }}</span>@endif
                        </td>
                        <td class="sy-fs-12 sy-text-muted">{{ \Illuminate\Support\Str::limit($o->detay, 60) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="sy-empty"><div class="baslik">Henüz olay yok</div><div>Watchdog kurulduğunda olaylar burada listelenecek.</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
