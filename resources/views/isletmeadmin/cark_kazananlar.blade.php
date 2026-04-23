@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)
@section('content')

<style>
.kz-wrap { padding: 24px; }
.kz-wrap * { box-sizing: border-box; }

.kz-hero {
    background: linear-gradient(135deg,#6c5ce7 0%,#8b5cf6 50%,#fd79a8 100%);
    color:#fff; border-radius:16px; padding:24px 28px; margin-bottom:22px;
    box-shadow:0 12px 34px rgba(108,92,231,.22);
}
.kz-hero h1 { margin:0 0 5px; font-size:22px; font-weight:800; letter-spacing:-.3px; }
.kz-hero p  { margin:0; opacity:.92; font-size:13px; }

.kz-ozet { display:grid; grid-template-columns:repeat(4, 1fr); gap:14px; margin-bottom:20px; }
@media (max-width:720px){ .kz-ozet { grid-template-columns:repeat(2, 1fr); } }
.kz-card {
    background:#fff; border-radius:14px; padding:16px 18px;
    box-shadow: 0 4px 14px rgba(0,0,0,.05); border:1px solid #e5e7eb;
}
.kz-card .lbl { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; font-weight:700; }
.kz-card .val { font-size:26px; font-weight:800; color:#6c5ce7; margin-top:4px; line-height:1; }
.kz-card .sub { font-size:11px; color:#9ca3af; margin-top:4px; }

.kz-tabs {
    display:flex; gap:8px; background:#fff; padding:8px; border-radius:12px;
    margin-bottom:16px; box-shadow:0 2px 8px rgba(0,0,0,.04);
    border:1px solid #e5e7eb; flex-wrap:wrap;
}
.kz-tab {
    padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600;
    color:#6b7280; text-decoration:none; cursor:pointer; transition:.2s;
}
.kz-tab:hover { background:#f3f4f6; color:#374151; text-decoration:none; }
.kz-tab.active { background:#6c5ce7; color:#fff; }

.kz-panel { background:#fff; border-radius:14px; overflow:hidden; border:1px solid #e5e7eb; box-shadow:0 4px 14px rgba(0,0,0,.04); margin-bottom:20px; }
.kz-panel-head { padding:14px 18px; background: linear-gradient(135deg,#fafbff 0%,#f4f6ff 100%); border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; }
.kz-panel-head h3 { margin:0; font-size:15px; font-weight:700; color:#111827; }
.kz-panel-head .cnt { font-size:12px; color:#6b7280; }

table.kz-tbl { width:100%; border-collapse:collapse; }
table.kz-tbl th { background:#f9fafb; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; padding:10px 14px; text-align:left; border-bottom:1px solid #e5e7eb; }
table.kz-tbl td { padding:12px 14px; border-bottom:1px solid #f3f4f6; font-size:13px; color:#374151; vertical-align:middle; }
table.kz-tbl tr:last-child td { border-bottom:none; }
table.kz-tbl tr:hover td { background:#fafbff; }

.kod-pill { display:inline-block; padding:4px 10px; background:#fef3c7; color:#92400e; font-family:monospace; font-weight:800; letter-spacing:2px; border-radius:6px; font-size:12px; border:1.5px dashed #f59e0b; }
.tip-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.tip-puan   { background:#d1fae5; color:#065f46; }
.tip-hizmet { background:#dbeafe; color:#1e40af; }
.tip-urun   { background:#fce7f3; color:#9f1239; }
.tip-tekrar { background:#fef3c7; color:#92400e; }
.tip-bos    { background:#f3f4f6; color:#6b7280; }

.durum-gecerli { color:#059669; font-weight:700; font-size:12px; }
.durum-kullan  { color:#dc2626; font-weight:700; font-size:12px; }
.durum-doldu   { color:#9ca3af; font-weight:700; font-size:12px; }

.btn-kullan, .btn-geri {
    padding:6px 14px; border-radius:6px; border:none;
    font-size:12px; font-weight:700; cursor:pointer; transition:.2s;
}
.btn-kullan { background:#10b981; color:#fff; }
.btn-kullan:hover { background:#059669; }
.btn-geri   { background:#f3f4f6; color:#6b7280; border:1px solid #e5e7eb; }
.btn-geri:hover   { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }

.kz-empty { text-align:center; padding:60px 20px; color:#9ca3af; }
.kz-empty .ic { font-size:48px; margin-bottom:10px; }

#toast { position:fixed; top:20px; right:20px; z-index:9999; padding:14px 20px; background:#10b981; color:#fff; border-radius:10px; font-weight:600; box-shadow:0 10px 30px rgba(0,0,0,.2); display:none; }
#toast.show { display:block; }
#toast.err  { background:#ef4444; }
</style>

<div class="kz-wrap">
    <div class="kz-hero">
        <h1>🏆 Çarkıfelek Kazananlar</h1>
        <p>Çarkıfelek çevirme geçmişi ve kazanılan kuponlar. Müşteri kuponu getirdiğinde "Kullanıldı" butonuyla işaretleyin.</p>
    </div>

    <div class="kz-ozet">
        <div class="kz-card">
            <div class="lbl">Toplam Çevirme</div>
            <div class="val">{{ $ozet['toplam_cevirme'] }}</div>
            <div class="sub">bugüne kadar</div>
        </div>
        <div class="kz-card">
            <div class="lbl">Bugün</div>
            <div class="val">{{ $ozet['bugun_cevirme'] }}</div>
            <div class="sub">çevirme</div>
        </div>
        <div class="kz-card">
            <div class="lbl">Bekleyen Kupon</div>
            <div class="val" style="color:#f59e0b;">{{ $ozet['kullanilmamis'] }}</div>
            <div class="sub">müşteri kullanabilir</div>
        </div>
        <div class="kz-card">
            <div class="lbl">Kullanılmış</div>
            <div class="val" style="color:#10b981;">{{ $ozet['kullanilmis'] }}</div>
            <div class="sub">kupon</div>
        </div>
    </div>

    <div class="kz-tabs">
        <a href="?filtre=tumu"       class="kz-tab {{ $filtre=='tumu'       ? 'active' : '' }}">Tümü</a>
        <a href="?filtre=gecerli"    class="kz-tab {{ $filtre=='gecerli'    ? 'active' : '' }}">Geçerli Kuponlar</a>
        <a href="?filtre=kullanildi" class="kz-tab {{ $filtre=='kullanildi' ? 'active' : '' }}">Kullanıldı</a>
        <a href="?filtre=sure_doldu" class="kz-tab {{ $filtre=='sure_doldu' ? 'active' : '' }}">Süresi Doldu</a>
    </div>

    <div class="kz-panel">
        <div class="kz-panel-head">
            <h3>🎁 Kuponlar</h3>
            <span class="cnt">{{ $odulluler->count() }} kayıt</span>
        </div>
        @if($odulluler->isEmpty())
            <div class="kz-empty"><div class="ic">🎫</div>Bu filtrede kupon yok.</div>
        @else
            <div style="overflow-x:auto;">
                <table class="kz-tbl">
                    <thead>
                        <tr>
                            <th>Kod</th>
                            <th>Ödül</th>
                            <th>Müşteri</th>
                            <th>Kazanma</th>
                            <th>Son Kullanma</th>
                            <th>Durum</th>
                            <th style="text-align:right;">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($odulluler as $o)
                        @php
                            $u = $users->get($o->user_id);
                            $gecmis = $o->gecerlilik_tarihi && $o->gecerlilik_tarihi->isPast();
                            $tipCls = [
                                'hizmet_indirimi' => 'tip-hizmet',
                                'urun_indirimi'   => 'tip-urun',
                                'puan'            => 'tip-puan',
                            ][$o->tip] ?? 'tip-bos';
                        @endphp
                        <tr data-id="{{ $o->id }}">
                            <td><span class="kod-pill">{{ $o->kod }}</span></td>
                            <td><span class="tip-badge {{ $tipCls }}">{{ $o->baslik }}</span></td>
                            <td>
                                @if($u)
                                    <strong>{{ $u->name ?? 'İsimsiz' }}</strong><br>
                                    <span style="color:#6b7280;font-size:11px;">{{ $u->email ?? '' }}</span>
                                @else
                                    <span style="color:#9ca3af;">#{{ $o->user_id }}</span>
                                @endif
                            </td>
                            <td style="color:#6b7280;">{{ $o->created_at ? $o->created_at->format('d.m.Y H:i') : '-' }}</td>
                            <td style="color:#6b7280;">{{ $o->gecerlilik_tarihi ? $o->gecerlilik_tarihi->format('d.m.Y') : 'Süresiz' }}</td>
                            <td>
                                @if($o->kullanildi)
                                    <span class="durum-kullan">✓ Kullanıldı</span><br>
                                    <small style="color:#9ca3af;">{{ $o->kullanim_tarihi ? $o->kullanim_tarihi->format('d.m.Y H:i') : '' }}</small>
                                @elseif($gecmis)
                                    <span class="durum-doldu">⊘ Süresi Doldu</span>
                                @else
                                    <span class="durum-gecerli">● Geçerli</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                @if($o->kullanildi)
                                    <button class="btn-geri" onclick="kuponAksiyon({{ $o->id }}, 'geri_al', this)">Geri Al</button>
                                @elseif(!$gecmis)
                                    <button class="btn-kullan" onclick="kuponAksiyon({{ $o->id }}, 'kullan', this)">✓ Kullanıldı</button>
                                @else
                                    <span style="color:#9ca3af;font-size:12px;">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="kz-panel">
        <div class="kz-panel-head">
            <h3>📜 Son Çevirme Logları</h3>
            <span class="cnt">{{ $loglar->count() }} kayıt (en son 500)</span>
        </div>
        @if($loglar->isEmpty())
            <div class="kz-empty"><div class="ic">📋</div>Henüz çevirme yapılmamış.</div>
        @else
            <div style="overflow-x:auto;">
                <table class="kz-tbl">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Müşteri</th>
                            <th>Kazanan Dilim</th>
                            <th>Ödül</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($loglar as $l)
                        @php
                            $u = $users->get($l->user_id);
                            $tipCls = [
                                'hizmet_indirimi' => 'tip-hizmet',
                                'urun_indirimi'   => 'tip-urun',
                                'puan'            => 'tip-puan',
                                'tekrar_dene'     => 'tip-tekrar',
                                'bos'             => 'tip-bos',
                            ][$l->tip] ?? 'tip-bos';
                            $odul = $l->tip === 'puan' && $l->deger ? ((int)$l->deger).' Puan'
                                  : ($l->tip === 'hizmet_indirimi' && $l->deger ? '%'.((int)$l->deger).' Hizmet İnd.'
                                  : ($l->tip === 'urun_indirimi' && $l->deger ? '%'.((int)$l->deger).' Ürün İnd.'
                                  : ($l->tip === 'tekrar_dene' ? 'Tekrar Dene' : 'Boş')));
                        @endphp
                        <tr>
                            <td style="color:#6b7280;">{{ $l->created_at ? $l->created_at->format('d.m.Y H:i') : '-' }}</td>
                            <td>
                                @if($u)
                                    <strong>{{ $u->name ?? 'İsimsiz' }}</strong>
                                @else
                                    <span style="color:#9ca3af;">#{{ $l->user_id }}</span>
                                @endif
                            </td>
                            <td style="color:#6b7280;">{{ $l->dilim_ismi ?: '-' }}</td>
                            <td><span class="tip-badge {{ $tipCls }}">{{ $odul }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div id="toast"></div>

<script>
const KULLAN_URL = '{{ route("isletmeadmin.cark.kuponkullan") }}{{ isset($_GET["sube"]) ? "?sube=".$isletme->id : "" }}';
const CSRF = '{{ csrf_token() }}';

function showToast(msg, err) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = err ? 'err show' : 'show';
    setTimeout(() => t.classList.remove('show'), 2500);
}

async function kuponAksiyon(id, aksiyon, btn) {
    btn.disabled = true;
    try {
        const resp = await fetch(KULLAN_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ odul_id: id, aksiyon: aksiyon }),
        });
        const data = await resp.json();
        if (!data.success) {
            showToast(data.message || 'Hata oluştu', true);
            btn.disabled = false;
            return;
        }
        showToast(aksiyon === 'kullan' ? 'Kupon kullanıldı olarak işaretlendi' : 'Kupon geri alındı');
        setTimeout(() => location.reload(), 700);
    } catch (e) {
        showToast('Bağlantı hatası', true);
        btn.disabled = false;
    }
}
</script>
@endsection
