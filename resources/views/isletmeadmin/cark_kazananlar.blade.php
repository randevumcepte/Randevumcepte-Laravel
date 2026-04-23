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

/* Doğrulama kutusu */
.dg-box {
    background: linear-gradient(135deg,#fef3c7 0%,#fed7aa 100%);
    border: 2px solid #f59e0b;
    border-radius: 16px;
    padding: 22px 24px;
    margin-bottom: 22px;
    box-shadow: 0 8px 24px rgba(245,158,11,.18);
}
.dg-head { display:flex; align-items:center; gap:12px; margin-bottom: 14px; }
.dg-head .ic { font-size: 30px; }
.dg-head h2  { margin:0; font-size:17px; font-weight:800; color:#92400e; letter-spacing:-.3px; }
.dg-head p   { margin:2px 0 0; font-size:12px; color:#78350f; opacity:.85; }
.dg-form { display:flex; gap:10px; align-items:stretch; flex-wrap:wrap; }
.dg-form input {
    flex:1; min-width:180px;
    padding: 14px 18px; border: 2px solid #f59e0b;
    border-radius: 10px; background:#fff;
    font-size: 22px; font-weight: 800; letter-spacing: 5px;
    font-family: monospace; text-transform: uppercase;
    text-align: center; color: #92400e;
    transition:.2s;
}
.dg-form input:focus { outline:none; border-color:#d97706; box-shadow:0 0 0 4px rgba(245,158,11,.2); }
.dg-form button {
    padding: 0 28px; min-height:52px;
    background:#92400e; color:#fff; border:none; border-radius:10px;
    font-size:14px; font-weight:700; cursor:pointer; transition:.2s;
    letter-spacing:.3px;
}
.dg-form button:hover { background:#78350f; transform:translateY(-2px); }
.dg-form button:disabled { opacity:.6; cursor:not-allowed; transform:none; }

/* Sonuç kartı */
.dg-result {
    margin-top:16px; padding: 20px 22px;
    background:#fff; border-radius:14px;
    border-left: 6px solid #6c5ce7;
    box-shadow: 0 6px 18px rgba(0,0,0,.08);
    display:none;
}
.dg-result.show { display:block; animation: slidein .3s ease-out; }
.dg-result.gecerli    { border-left-color:#10b981; }
.dg-result.kullanildi { border-left-color:#dc2626; }
.dg-result.sure_doldu { border-left-color:#9ca3af; }
.dg-result.hata       { border-left-color:#ef4444; }
@keyframes slidein { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }

.dgr-row { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:14px; }
.dgr-info { flex:1; min-width:240px; }
.dgr-bn {
    display:inline-block; padding:5px 14px; border-radius:20px;
    font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.5px;
    margin-bottom:8px;
}
.dgr-bn.gecerli    { background:#d1fae5; color:#065f46; }
.dgr-bn.kullanildi { background:#fee2e2; color:#991b1b; }
.dgr-bn.sure_doldu { background:#f3f4f6; color:#6b7280; }

.dgr-baslik { font-size:26px; font-weight:800; color:#6c5ce7; margin:4px 0 12px; }
.dgr-detay { font-size:13px; color:#374151; line-height:1.8; }
.dgr-detay b { color:#111827; font-weight:700; }
.dgr-detay .lbl { display:inline-block; min-width:100px; color:#6b7280; font-weight:500; }

.dgr-act { min-width:220px; text-align:right; }
.dgr-act button {
    padding: 14px 22px; border-radius:10px; border:none;
    font-size:14px; font-weight:800; cursor:pointer; transition:.2s;
    width:100%;
}
.btn-onay { background:#10b981; color:#fff; }
.btn-onay:hover { background:#059669; transform:translateY(-2px); box-shadow:0 8px 20px rgba(16,185,129,.35); }
.btn-geri-al { background:#f3f4f6; color:#374151; border:1px solid #d1d5db; }
.btn-geri-al:hover { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }
.dgr-act .note { display:block; margin-top:8px; font-size:11px; color:#6b7280; }
</style>

<div class="kz-wrap">
    <div class="kz-hero">
        <h1>🏆 Çarkıfelek Kazananlar</h1>
        <p>Çarkıfelek çevirme geçmişi ve kazanılan kuponlar. Müşteri kuponu getirdiğinde aşağıdaki kutuya kodu girin ve onaylayın.</p>
    </div>

    <div class="dg-box">
        <div class="dg-head">
            <span class="ic">🔍</span>
            <div>
                <h2>Kupon Doğrulama</h2>
                <p>Müşterinin elindeki kodu yazın, detayları görün ve tek tıkla kullanım onayı verin.</p>
            </div>
        </div>
        <form class="dg-form" onsubmit="event.preventDefault(); kuponDogrula();">
            <input type="text" id="dg-kod" maxlength="12" placeholder="ÖRN: AB3XY7Q2" autocomplete="off">
            <button type="submit" id="dg-btn">🔍 Doğrula</button>
        </form>
        <div class="dg-result" id="dg-result"></div>
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
const KULLAN_URL  = '{{ route("isletmeadmin.cark.kuponkullan") }}{{ isset($_GET["sube"]) ? "?sube=".$isletme->id : "" }}';
const DOGRULA_URL = '{{ route("isletmeadmin.cark.kupondogrula") }}{{ isset($_GET["sube"]) ? "?sube=".$isletme->id : "" }}';
const CSRF = '{{ csrf_token() }}';

/* Kupon doğrulama — kutudan kod gir, detayları anında göster */
let _dgAktif = null;
async function kuponDogrula() {
    const inp = document.getElementById('dg-kod');
    const btn = document.getElementById('dg-btn');
    const res = document.getElementById('dg-result');
    const kod = (inp.value || '').trim().toUpperCase();
    if (!kod) { inp.focus(); return; }

    btn.disabled = true; btn.textContent = '⏳ Kontrol...';
    res.className = 'dg-result';
    res.innerHTML = '';

    try {
        const resp = await fetch(DOGRULA_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ kod: kod }),
        });
        const data = await resp.json();
        btn.disabled = false; btn.textContent = '🔍 Doğrula';

        if (!data.success) {
            res.classList.add('show', 'hata');
            res.innerHTML = '<div style="display:flex;align-items:center;gap:14px;"><div style="font-size:40px;">❌</div><div><h3 style="margin:0 0 4px;color:#991b1b;font-weight:800;">Geçersiz Kod</h3><p style="margin:0;color:#7f1d1d;">' + (data.message || 'Kod bulunamadı') + '</p></div></div>';
            return;
        }
        renderDogrulaSonuc(data.odul);
    } catch (e) {
        btn.disabled = false; btn.textContent = '🔍 Doğrula';
        res.classList.add('show', 'hata');
        res.innerHTML = '<div>Bağlantı hatası. Lütfen tekrar deneyin.</div>';
    }
}

function renderDogrulaSonuc(o) {
    const res = document.getElementById('dg-result');
    _dgAktif = o;
    const rozet = {
        gecerli:    '<span class="dgr-bn gecerli">● Geçerli</span>',
        kullanildi: '<span class="dgr-bn kullanildi">✓ Zaten Kullanıldı</span>',
        sure_doldu: '<span class="dgr-bn sure_doldu">⊘ Süresi Dolmuş</span>',
    }[o.durum] || '';
    const btnAlan = o.durum === 'gecerli'
        ? '<button class="btn-onay" onclick="dgOnayla('+o.id+', this)">✓ Kullanıldı Olarak İşaretle</button><span class="note">Tek tıkla anında onay verir</span>'
        : (o.durum === 'kullanildi'
            ? '<button class="btn-geri-al" onclick="dgOnayla('+o.id+', this, \'geri_al\')">↺ Kullanımı Geri Al</button><span class="note">Hatalı işaretlediyseniz</span>'
            : '<span style="color:#9ca3af;font-size:13px;">Süresi dolmuş — işlem yapılamaz</span>');

    res.className = 'dg-result show ' + o.durum;
    res.innerHTML =
        '<div class="dgr-row">' +
            '<div class="dgr-info">' +
                rozet +
                '<div class="dgr-baslik">'+ o.baslik +'</div>' +
                '<div class="dgr-detay">' +
                    '<div><span class="lbl">Müşteri:</span> <b>'+ o.musteri_adi +'</b></div>' +
                    (o.musteri_tel   ? '<div><span class="lbl">Telefon:</span> '+ o.musteri_tel + '</div>' : '') +
                    (o.musteri_email ? '<div><span class="lbl">E-posta:</span> '+ o.musteri_email +'</div>' : '') +
                    '<div><span class="lbl">Kazanma:</span> '+ (o.kazanma_tarihi || '-') +'</div>' +
                    '<div><span class="lbl">Son Kullanım:</span> '+ (o.gecerlilik || 'Süresiz') +'</div>' +
                    (o.kullanildi && o.kullanim_tarihi
                        ? '<div><span class="lbl">Kullanıldı:</span> <b style="color:#dc2626;">'+ o.kullanim_tarihi +'</b></div>' : '') +
                '</div>' +
            '</div>' +
            '<div class="dgr-act">' + btnAlan + '</div>' +
        '</div>';
}

async function dgOnayla(id, btn, aksiyon) {
    aksiyon = aksiyon || 'kullan';
    btn.disabled = true;
    btn.textContent = '⏳ İşleniyor...';
    try {
        const resp = await fetch(KULLAN_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ odul_id: id, aksiyon: aksiyon }),
        });
        const data = await resp.json();
        if (!data.success) { showToast(data.message || 'Hata', true); btn.disabled=false; return; }
        showToast(aksiyon === 'kullan' ? '✓ Kupon kullanıldı olarak işaretlendi' : '↺ Kullanım geri alındı');
        // Doğrulama kutusu içeriğini yenile
        if (_dgAktif) {
            _dgAktif.kullanildi = aksiyon === 'kullan' ? 1 : 0;
            _dgAktif.kullanim_tarihi = data.kullanim_tarihi;
            _dgAktif.durum = aksiyon === 'kullan' ? 'kullanildi' : 'gecerli';
            renderDogrulaSonuc(_dgAktif);
        }
        // Alt tabloyu da tazele
        setTimeout(() => location.reload(), 1200);
    } catch (e) {
        showToast('Bağlantı hatası', true);
        btn.disabled = false;
    }
}

// Kutuya yazınca otomatik büyük harf + 8 harf sonrası Enter tetikle
document.addEventListener('DOMContentLoaded', () => {
    const inp = document.getElementById('dg-kod');
    if (!inp) return;
    inp.addEventListener('input', e => {
        e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });
    inp.focus();
});

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
