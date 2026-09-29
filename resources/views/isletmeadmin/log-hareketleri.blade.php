@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<style>
    .log-wrap { --mor:#5C008E; }
    /* Başlık */
    .log-page-head {
        display:flex; align-items:center; gap:16px; padding:18px 22px; margin-bottom:14px;
        background:linear-gradient(135deg,#5C008E 0%,#7B2FB8 55%,#9D5DC8 100%);
        border-radius:16px; box-shadow:0 8px 24px rgba(92,0,142,.18);
    }
    .log-page-head .hicon {
        width:52px; height:52px; border-radius:14px; flex:0 0 auto;
        background:rgba(255,255,255,.18); color:#fff; display:flex; align-items:center; justify-content:center; font-size:26px;
    }
    .log-page-head h2 { color:#fff; margin:0; font-size:22px; font-weight:800; letter-spacing:.2px; }
    .log-page-head .sub { color:#f0e6fb; margin-top:3px; font-size:13px; }

    /* Renk açıklaması (legend) */
    .log-legend { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px; }
    .log-legend .lg { display:inline-flex; align-items:center; gap:7px; font-size:12px; font-weight:700;
        background:#fff; border:1px solid #eee; padding:6px 12px; border-radius:20px; box-shadow:0 1px 2px rgba(0,0,0,.03); }
    .log-legend .lg i { font-size:13px; }

    /* Filtreler */
    .log-filters { background:#fff; border:1px solid #eef0f4; border-radius:14px; padding:14px 16px; margin-bottom:16px;
        display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; box-shadow:0 2px 8px rgba(0,0,0,.03); }
    .log-filters .form-group { margin:0; }
    .log-filters label { font-size:12px; font-weight:700; color:#3a2e57; margin-bottom:5px; display:block; }
    .log-filters .form-control { min-width:160px; border-radius:9px; border:1px solid #e6e6ef; }
    .log-filters .btn-mor { background:linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; border:none; font-weight:700; padding:9px 18px; border-radius:9px; }
    .log-filters .btn-mor:hover { filter:brightness(1.08); color:#fff; }
    .log-filters .btn-temiz { background:#f1f1f5; color:#3a2e57; border:none; padding:9px 15px; border-radius:9px; text-decoration:none; display:inline-block; line-height:1.4; }

    /* Tablo / satırlar */
    .log-card { background:#fff; border:1px solid #eef0f4; border-radius:16px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.04); }
    .log-table { width:100%; border-collapse:separate; border-spacing:0; }
    .log-table thead th { background:#faf8fd; font-weight:800; color:#6b5b86; font-size:11.5px; text-transform:uppercase; letter-spacing:.5px;
        padding:13px 16px; text-align:left; border-bottom:1px solid #f0eef5; }
    .log-table tbody td { padding:14px 16px; text-align:left; border-bottom:1px solid #f4f4f8; vertical-align:middle; font-size:13px; }
    .log-table tbody tr:last-child td { border-bottom:0; }
    .log-table tbody tr { transition:background .12s; }

    .log-mute { color:#9a94a6; font-size:11.5px; }
    .log-strong { font-weight:700; color:#2d2143; }

    .time-cell { position:relative; }
    .time-cell .accent { position:absolute; left:0; top:8px; bottom:8px; width:4px; border-radius:0 4px 4px 0; }
    .time-cell .tdate { font-weight:700; color:#3a2e57; font-size:12.5px; }

    .user-wrap { display:flex; align-items:center; gap:10px; }
    .uavatar { width:34px; height:34px; border-radius:50%; color:#fff; font-weight:800; font-size:14px;
        display:inline-flex; align-items:center; justify-content:center; flex:0 0 auto; }
    .urol { font-size:11px; font-weight:700; margin-top:1px; }

    .islem-wrap { display:flex; align-items:center; gap:10px; }
    .act-icon { width:36px; height:36px; border-radius:11px; display:inline-flex; align-items:center; justify-content:center; font-size:17px; flex:0 0 auto; }
    .act-badge { display:inline-block; padding:3px 11px; border-radius:20px; font-size:11.5px; font-weight:800; white-space:nowrap; }
    .cat-label { font-size:10.5px; font-weight:700; margin-top:2px; opacity:.85; }
    .log-src { display:inline-block; margin-left:6px; padding:1px 7px; border-radius:12px; font-size:10px; font-weight:800; vertical-align:middle; }
    .log-src.src-mobil { background:#eef2ff; color:#4338ca; }
    .log-src.src-panel { background:#eef2f6; color:#475569; }

    .tgt-chip { display:inline-block; background:#f3eafa; color:#5C008E; font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:8px; margin-bottom:3px; }
    .log-detail-pre { background:#faf8ff; border:1px solid #ece3f8; border-radius:8px; padding:9px; font-size:11.5px; margin-top:6px; max-width:380px; overflow:auto; white-space:pre-wrap; word-break:break-word; }
    .ipm { font-family:ui-monospace,Menlo,Consolas,monospace; color:#94909f; font-size:11.5px; white-space:nowrap; }

    .log-empty { padding:60px 20px; text-align:center; color:#9a94a6; }
    .log-empty .ei { font-size:46px; margin-bottom:10px; opacity:.4; color:#5C008E; }
    .log-pagination { display:flex; justify-content:center; padding:18px 0; }
    .log-pagination .pagination { margin:0; }

    @media (max-width:820px){
        .log-table thead { display:none; }
        .log-table, .log-table tbody, .log-table tr, .log-table td { display:block; width:100%; }
        .log-table tbody tr { padding:8px 4px 12px; border-bottom:8px solid #f6f4fa; position:relative; }
        .log-table tbody td { border:0; padding:4px 16px; }
        .time-cell .accent { top:0; bottom:auto; height:100%; }
    }
</style>

<div class="log-wrap">
    <div class="log-page-head">
        <div class="hicon"><i class="bi bi-clock-history"></i></div>
        <div>
            <h2>Log Hareketleri</h2>
            <div class="sub">İşletmenizde yapılan <b>tüm</b> işlemleri (kim · ne · ne zaman) buradan takip edin.</div>
        </div>
    </div>

    {{-- Renk açıklaması --}}
    <div class="log-legend">
        <span class="lg" style="color:#059669"><i class="bi bi-plus-circle-fill"></i> Ekleme</span>
        <span class="lg" style="color:#d97706"><i class="bi bi-pencil-square"></i> Güncelleme</span>
        <span class="lg" style="color:#ef4444"><i class="bi bi-trash3-fill"></i> Silme</span>
        <span class="lg" style="color:#dc2626"><i class="bi bi-x-circle-fill"></i> İptal</span>
        <span class="lg" style="color:#0d9488"><i class="bi bi-cash-coin"></i> Ödeme / Onay</span>
        <span class="lg" style="color:#2563eb"><i class="bi bi-send-fill"></i> Gönderim</span>
        <span class="lg" style="color:#4f46e5"><i class="bi bi-box-arrow-in-right"></i> Giriş</span>
        <span class="lg" style="color:#7c3aed"><i class="bi bi-lightning-charge-fill"></i> Diğer</span>
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
            <a href="/isletmeyonetim/log-hareketleri{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="btn-temiz">Sıfırla</a>
        </div>
    </form>

    <div class="log-card">
        <div style="overflow-x:auto">
            <table class="log-table">
                <thead>
                    <tr>
                        <th style="width:150px">Zaman</th>
                        <th style="width:190px">Kullanıcı</th>
                        <th style="width:210px">İşlem</th>
                        <th>Hedef</th>
                        <th>Açıklama</th>
                        <th style="width:120px">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loglar as $log)
                        @php
                            $a = $log->action;
                            // kategori: [etiket, renk, açık-bg, bootstrap-ikon]
                            if (str_contains($a,'sil') || str_contains($a,'kaldir'))            $cat=['Silme','#ef4444','#fef2f2','bi-trash3-fill'];
                            elseif (str_contains($a,'iptal'))                                    $cat=['İptal','#dc2626','#fef2f2','bi-x-circle-fill'];
                            elseif (str_contains($a,'ekle')||str_contains($a,'olustur')||str_contains($a,'kaydet')||str_contains($a,'yeni')) $cat=['Ekleme','#059669','#ecfdf5','bi-plus-circle-fill'];
                            elseif (str_contains($a,'guncelle')||str_contains($a,'duzenle')||str_contains($a,'degistir')) $cat=['Güncelleme','#d97706','#fffbeb','bi-pencil-square'];
                            elseif (str_contains($a,'onay')||str_contains($a,'odeme')||str_contains($a,'tahsilat')) $cat=['Ödeme / Onay','#0d9488','#f0fdfa','bi-cash-coin'];
                            elseif (str_contains($a,'gonder')||str_contains($a,'yolla')||str_contains($a,'sms')) $cat=['Gönderim','#2563eb','#eff6ff','bi-send-fill'];
                            elseif ($a=='login'||$a=='logout'||str_contains($a,'giris'))          $cat=['Giriş','#4f46e5','#eef2ff','bi-box-arrow-in-right'];
                            else                                                                 $cat=['İşlem','#7c3aed','#f5f3ff','bi-lightning-charge-fill'];

                            $kaynak=null;
                            if($log->meta){ $mA=json_decode($log->meta,true); $kaynak=is_array($mA)?($mA['kaynak']??null):null; }

                            $rol = $log->user_rol;
                            $rolRenk = $rol=='Hesap Sahibi' ? '#7c3aed' : ($rol=='Yönetici' ? '#2563eb' : ($rol=='Personel' ? '#0d9488' : ($rol=='Satış Ortağı' ? '#d97706' : '#64748b')));
                            $bas = strtoupper(mb_substr(trim($log->user_name ?: 'S'),0,1));
                        @endphp
                        <tr onmouseover="this.style.background='{{ $cat[2] }}'" onmouseout="this.style.background=''">
                            <td class="time-cell">
                                <span class="accent" style="background:{{ $cat[1] }}"></span>
                                <div class="tdate">{{ \Carbon\Carbon::parse($log->created_at)->format('d.m.Y') }}</div>
                                <div class="log-mute">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}</div>
                                <div class="log-mute" style="margin-top:2px">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="user-wrap">
                                    <span class="uavatar" style="background:{{ $rolRenk }}">{{ $bas }}</span>
                                    <div>
                                        <div class="log-strong">{{ $log->user_name ?: 'Sistem' }}</div>
                                        <div class="urol" style="color:{{ $rolRenk }}">{{ $rol ?: '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="islem-wrap">
                                    <span class="act-icon" style="background:{{ $cat[2] }};color:{{ $cat[1] }}"><i class="bi {{ $cat[3] }}"></i></span>
                                    <div>
                                        <span class="act-badge" style="background:{{ $cat[2] }};color:{{ $cat[1] }}">{{ $log->action }}</span>
                                        <div class="cat-label" style="color:{{ $cat[1] }}">
                                            {{ $cat[0] }}
                                            @if($kaynak=='mobil')<span class="log-src src-mobil">📱 mobil</span>@elseif($kaynak=='panel_oto' || $kaynak=='panel')<span class="log-src src-panel">🖥️ panel</span>@endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($log->target_type)
                                    <span class="tgt-chip">{{ $log->target_type }}#{{ $log->target_id }}</span><br>
                                @endif
                                <span class="log-strong">{{ $log->target_label ?: '—' }}</span>
                            </td>
                            <td>
                                <span style="color:#4b4459">{{ $log->aciklama ?: '—' }}</span>
                                @if($log->meta)
                                    <details style="margin-top:5px">
                                        <summary class="log-mute" style="cursor:pointer;font-weight:600">Detay</summary>
                                        <pre class="log-detail-pre">{{ json_encode(json_decode($log->meta), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @endif
                            </td>
                            <td><span class="ipm">{{ $log->ip }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="log-empty">
                                    <div class="ei"><i class="bi bi-clock-history"></i></div>
                                    <div style="font-weight:700;color:#3a2e57;font-size:15px;">Kayıt bulunamadı</div>
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
</div>

@endsection
