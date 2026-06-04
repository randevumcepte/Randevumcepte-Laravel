@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<style>
/* =========================================================================
   E-Asistan — Modern visual layer (Option A mockup)
   Sadece gorsel katman degisti; tablo ID'leri, form name'leri, JS hook'lar
   ayni — DataTables/AJAX/ayar kayit akisi etkilenmez.
   ========================================================================= */
.ea-page {
    --brand: #7800B3;
    --brand-2: #9b25d4;
    --brand-soft: #f3eafa;
    --brand-soft-2: #fbf7ff;
    --ink: #1f1933;
    --ink-2: #574d6b;
    --ink-3: #8b8298;
    --line: #ece6f3;
    --ok: #16a34a;
    --ok-soft: #e6f4ec;
    --warn: #ea580c;
    --danger: #dc2626;
    --danger-soft: #fde6e6;
    color: var(--ink);
}

/* Page header (yedek, original page-header kullanilmiyor) ------------- */
.ea-hero {
    background: linear-gradient(135deg, #faf6ff 0%, #ffffff 60%);
    border: 1px solid var(--line);
    border-radius: 16px;
    padding: 26px 30px;
    margin-bottom: 18px;
}
.ea-breadcrumb { color: var(--ink-3); font-size: 13px; margin-bottom: 10px; }
.ea-breadcrumb a { color: var(--ink-3); text-decoration: none; }
.ea-breadcrumb a:hover { color: var(--brand); }
.ea-breadcrumb .sep { margin: 0 6px; }
.ea-hero h1 {
    color: var(--ink); font-size: 28px; font-weight: 700;
    margin: 0 0 6px; letter-spacing: -0.5px;
}
.ea-hero .subtitle { color: var(--ink-2); font-size: 14px; margin: 0; max-width: 640px; }

/* Tabs ---------------------------------------------------------------- */
.ea-tabs {
    display: flex; flex-wrap: nowrap; gap: 6px;
    border: 1px solid var(--line);
    background: #fff;
    padding: 6px;
    border-radius: 14px;
    margin: 0 0 18px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    list-style: none;
}
.ea-tabs .nav-item { list-style: none; }
.ea-tabs .nav-link {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 18px;
    color: var(--ink-2);
    background: transparent;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
    cursor: pointer;
    transition: all .15s ease;
    text-decoration: none;
}
.ea-tabs .nav-link i { font-size: 14px; opacity: .8; }
.ea-tabs .nav-link:hover { background: var(--brand-soft-2); color: var(--brand); text-decoration: none; }
.ea-tabs .nav-link.active { background: var(--brand); color: #fff; box-shadow: 0 4px 12px rgba(120,0,179,.22); }
.ea-tabs .nav-link.active i { opacity: 1; }
.ea-tab-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 22px; height: 22px; padding: 0 7px;
    background: rgba(120, 0, 179, 0.12);
    color: var(--brand);
    border-radius: 11px;
    font-size: 11px;
    font-weight: 700;
}
.ea-tabs .nav-link.active .ea-tab-badge {
    background: rgba(255,255,255,0.25);
    color: #fff;
}

/* Card panel ---------------------------------------------------------- */
.ea-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 2px rgba(15,9,30,.03);
}
.ea-card-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--line);
}
.ea-card-head h2 {
    font-size: 18px; font-weight: 700; color: var(--ink); margin: 0 0 2px;
}
.ea-card-head .ea-card-sub { color: var(--ink-3); font-size: 13px; }

/* DataTable polish ---------------------------------------------------- */
.ea-card .dataTables_wrapper .dataTables_length,
.ea-card .dataTables_wrapper .dataTables_filter,
.ea-card .dataTables_wrapper .dataTables_info,
.ea-card .dataTables_wrapper .dataTables_paginate { margin-bottom: 10px; color: var(--ink-2); font-size: 13px; }
.ea-card .dataTables_filter input {
    border: 1px solid var(--line) !important;
    border-radius: 8px !important;
    padding: 7px 12px !important;
    font-size: 13px !important;
    min-width: 200px;
    outline: none;
    transition: border-color .15s;
}
.ea-card .dataTables_filter input:focus { border-color: var(--brand) !important; }
.ea-card .dataTables_length select {
    border: 1px solid var(--line);
    border-radius: 8px;
    padding: 5px 8px;
    font-size: 13px;
}
.ea-card table.dataTable { border-collapse: separate !important; border-spacing: 0; width: 100% !important; }
.ea-card table.dataTable thead th {
    background: #fafafd;
    color: var(--ink-2);
    font-weight: 600;
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: .6px;
    border-bottom: 1px solid var(--line);
    padding: 13px 12px;
    text-align: left;
}
.ea-card table.dataTable tbody td {
    color: var(--ink);
    font-size: 13px;
    padding: 14px 12px;
    border-bottom: 1px solid var(--line);
    vertical-align: middle;
}
.ea-card table.dataTable tbody tr:hover { background: var(--brand-soft-2); }
.ea-card table.dataTable tbody tr:last-child td { border-bottom: 0; }

/* Pagination */
.ea-card .dataTables_paginate .paginate_button {
    border-radius: 8px !important;
    padding: 5px 11px !important;
    margin: 0 2px !important;
    border: 1px solid transparent !important;
    background: transparent !important;
    color: var(--ink-2) !important;
}
.ea-card .dataTables_paginate .paginate_button:hover {
    background: var(--brand-soft) !important;
    color: var(--brand) !important;
    border-color: transparent !important;
}
.ea-card .dataTables_paginate .paginate_button.current,
.ea-card .dataTables_paginate .paginate_button.current:hover {
    background: var(--brand) !important;
    color: #fff !important;
    border-color: var(--brand) !important;
}

/* Durum/Islem pill ve buton tasarimi (Controller'in donen btn'lerini override) */
.ea-card table.dataTable td .btn {
    line-height: 1.3 !important;
    padding: 5px 11px !important;
    font-size: 12px !important;
    border-radius: 999px !important;
    font-weight: 600 !important;
    white-space: nowrap !important;
    box-shadow: none !important;
}
.ea-card table.dataTable td .btn.btn-danger {
    background: var(--danger-soft) !important;
    color: var(--danger) !important;
    border: 1px solid transparent !important;
}
.ea-card table.dataTable td .btn.btn-success {
    background: var(--ok-soft) !important;
    color: var(--ok) !important;
    border: 1px solid transparent !important;
}
/* Aksiyon butonlari: gorev iptal + kampanya detay biraz daha kare */
.ea-card table.dataTable td .btn[name="gorev_iptal_et_randevu"],
.ea-card table.dataTable td .btn[name="gorev_iptal_et_alacak"],
.ea-card table.dataTable td .btn[name="gorev_iptal_et_kampanya"] {
    background: #fff !important;
    color: var(--danger) !important;
    border: 1px solid var(--danger-soft) !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
    transition: all .15s;
}
.ea-card table.dataTable td .btn[name^="gorev_iptal_et"]:hover {
    background: var(--danger-soft) !important;
}
.ea-card table.dataTable td .btn[name="kampanya_detay"],
.ea-card table.dataTable td a.btn[name="kampanya_detay"] {
    background: var(--brand-soft) !important;
    color: var(--brand) !important;
    border: 1px solid transparent !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
}

/* Empty state */
.ea-card .dataTables_empty {
    color: var(--ink-3) !important;
    padding: 40px 12px !important;
    font-size: 14px !important;
    text-align: center !important;
}

/* Settings grid ------------------------------------------------------- */
.ea-settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.ea-setting {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 18px;
    transition: all .15s ease;
    position: relative;
}
.ea-setting:hover {
    border-color: rgba(120, 0, 179, 0.28);
    box-shadow: 0 4px 16px rgba(120, 0, 179, 0.06);
}
.ea-set-head {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 14px;
}
.ea-set-body { flex: 1; min-width: 0; }
.ea-set-icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: var(--brand-soft); color: var(--brand);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 17px;
    margin-bottom: 10px;
}
.ea-setting h6 {
    color: var(--ink); font-size: 14px; font-weight: 700;
    margin: 0 0 5px;
}
.ea-setting p {
    color: var(--ink-3); font-size: 12.5px; line-height: 1.5; margin: 0;
}

/* Toggle switch ------------------------------------------------------- */
.ea-toggle {
    position: relative; display: inline-block;
    width: 46px; height: 26px;
    flex-shrink: 0; margin-top: 4px;
}
.ea-toggle input { opacity: 0; width: 0; height: 0; }
.ea-toggle-slider {
    position: absolute; cursor: pointer; inset: 0;
    background: #e2dee9;
    transition: .25s;
    border-radius: 26px;
}
.ea-toggle-slider:before {
    position: absolute; content: "";
    height: 20px; width: 20px; left: 3px; bottom: 3px;
    background: #fff;
    transition: .25s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,.18);
}
.ea-toggle input:checked + .ea-toggle-slider { background: var(--brand); }
.ea-toggle input:checked + .ea-toggle-slider:before { transform: translateX(20px); }

/* "Tekrar arama" saat secimi - setting card icindeki ek alan */
.ea-saat-pick {
    margin-top: 14px; padding-top: 14px;
    border-top: 1px dashed var(--line);
    display: flex; align-items: center; gap: 10px;
}
.ea-saat-pick label { color: var(--ink-2); font-size: 12.5px; margin: 0; white-space: nowrap; }
.ea-saat-pick select {
    flex: 1;
    border: 1px solid var(--line);
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 13px;
    background: #fff;
    color: var(--ink);
    outline: none;
}
.ea-saat-pick select:focus { border-color: var(--brand); }

/* Save bar */
.ea-save-bar { display: flex; justify-content: flex-end; }
.ea-btn-save {
    background: var(--brand); color: #fff;
    border: none; border-radius: 10px;
    padding: 11px 24px;
    font-weight: 600; font-size: 14px;
    cursor: pointer;
    transition: all .15s ease;
    display: inline-flex; align-items: center; gap: 8px;
}
.ea-btn-save:hover {
    background: #5d0089;
    box-shadow: 0 6px 18px rgba(120, 0, 179, 0.28);
    transform: translateY(-1px);
}
.ea-btn-save:active { transform: translateY(0); }

/* Mobile -------------------------------------------------------------- */
@media (max-width: 575.98px) {
    .ea-hero { padding: 20px 16px; }
    .ea-hero h1 { font-size: 22px; }
    .ea-card { padding: 16px; }
    .ea-tabs .nav-link { padding: 9px 14px; font-size: 13px; }
    .ea-settings-grid { grid-template-columns: 1fr; }
}
</style>

<div class="ea-page">

    <div class="ea-hero">
        <div class="ea-breadcrumb">
            <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
            <span class="sep">›</span>
            <span>Asistanım</span>
        </div>
        <h1>Asistanım</h1>
        <p class="subtitle">Bugünkü ve yarınki hatırlatma görevlerini takip et, e-asistanın hangi tür hatırlatmaları yapacağını yönet.</p>
    </div>

    <ul class="nav ea-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#bugunku_e_asistan" role="tab" aria-selected="true">
                <i class="fa fa-calendar-check-o"></i>
                Bugünkü Görevler
                <span class="ea-tab-badge" data-badge="bugun">0</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#yarinki_gorevler" role="tab" aria-selected="false">
                <i class="fa fa-calendar-o"></i>
                Yarınki Görevler
                <span class="ea-tab-badge" data-badge="yarin">0</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#e_asistan_ayarlari" role="tab" aria-selected="false">
                <i class="fa fa-cog"></i>
                Ayarlar
            </a>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ============================== BUGÜN ============================== --}}
        <div class="tab-pane fade show active" id="bugunku_e_asistan" role="tabpanel">
            <div class="ea-card">
                <table class="data-table table" id="bugunkugorevtablo">
                    <thead>
                        <tr>
                            <th>Başlık</th>
                            <th>İçerik</th>
                            <th>Arama Saati</th>
                            <th>Durum</th>
                            <th>Sonuç</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- ============================== YARIN ============================== --}}
        <div class="tab-pane fade" id="yarinki_gorevler" role="tabpanel">
            <div class="ea-card">
                <table class="data-table table" id="yarinkigorevtablo">
                    <thead>
                        <tr>
                            <th>Başlık</th>
                            <th>İçerik</th>
                            <th>Arama Saati</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- ============================== AYARLAR ============================== --}}
        <div class="tab-pane fade" id="e_asistan_ayarlari" role="tabpanel">
            <div class="ea-card">
                <form id="otomatik_e_asistan_ayarlari" method="POST">
                    {{csrf_field()}}
                    <input type="hidden" name="sube" value="{{$isletme->id}}">

                    <div class="ea-settings-grid">

                        <div class="ea-setting">
                            <div class="ea-set-head">
                                <div class="ea-set-body">
                                    <div class="ea-set-icon"><i class="fa fa-money"></i></div>
                                    <h6>Alacak Hatırlatma</h6>
                                    <p>Alacak hatırlatmalarını planlanan ödeme tarihinden 2 gün önce ara.</p>
                                </div>
                                <label class="ea-toggle">
                                    <input type="checkbox" name="e_asistan_alacak_acik_kapali" {{($e_asistan_ayarlari[0]->acik_kapali) ? 'checked' : ''}}>
                                    <span class="ea-toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="ea-setting">
                            <div class="ea-set-head">
                                <div class="ea-set-body">
                                    <div class="ea-set-icon"><i class="fa fa-calendar-check-o"></i></div>
                                    <h6>Randevu Hatırlatma</h6>
                                    <p>Randevu hatırlatmalarını randevudan 1 gün önce ara.</p>
                                </div>
                                <label class="ea-toggle">
                                    <input type="checkbox" name="e_asistan_randevu_acik_kapali" {{($e_asistan_ayarlari[3]->acik_kapali) ? 'checked' : ''}}>
                                    <span class="ea-toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="ea-setting">
                            <div class="ea-set-head">
                                <div class="ea-set-body">
                                    <div class="ea-set-icon"><i class="fa fa-birthday-cake"></i></div>
                                    <h6>Doğum Günü Kutlama</h6>
                                    <p>Doğum günü olan müşterilere kutlama araması yapılsın. (Özel gönderici adı gerekir.)</p>
                                </div>
                                <label class="ea-toggle">
                                    <input type="checkbox" name="e_asistan_dogumgunu_acik_kapali" {{($e_asistan_ayarlari[6]->acik_kapali) ? 'checked' : ''}}>
                                    <span class="ea-toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="ea-setting">
                            <div class="ea-set-head">
                                <div class="ea-set-body">
                                    <div class="ea-set-icon"><i class="fa fa-comments-o"></i></div>
                                    <h6>Ön Görüşme Hatırlatma</h6>
                                    <p>Ön görüşme hatırlatmalarını görüşmeden 1 gün önce ara.</p>
                                </div>
                                <label class="ea-toggle">
                                    <input type="checkbox" name="e_asistan_ongorusme_acik_kapali" {{($e_asistan_ayarlari[1]->acik_kapali) ? 'checked' : ''}}>
                                    <span class="ea-toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="ea-setting">
                            <div class="ea-set-head">
                                <div class="ea-set-body">
                                    <div class="ea-set-icon"><i class="fa fa-bullhorn"></i></div>
                                    <h6>Kampanya Hatırlatma</h6>
                                    <p>Kampanya ve promosyon bilgilendirme aramaları yapılsın.</p>
                                </div>
                                <label class="ea-toggle">
                                    <input type="checkbox" name="e_asistan_kampanya_acik_kapali" {{($e_asistan_ayarlari[7]->acik_kapali) ? 'checked' : ''}}>
                                    <span class="ea-toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="ea-setting">
                            <div class="ea-set-head">
                                <div class="ea-set-body">
                                    <div class="ea-set-icon"><i class="fa fa-refresh"></i></div>
                                    <h6>Tekrar Arama</h6>
                                    <p>Ulaşılamayan aramalar belirlenen süre sonra tekrar denensin.</p>
                                </div>
                                <label class="ea-toggle">
                                    <input type="checkbox" name="e_asistan_tekrar_acik_kapali" {{($e_asistan_ayarlari[2]->acik_kapali) ? 'checked' : ''}}>
                                    <span class="ea-toggle-slider"></span>
                                </label>
                            </div>
                            <div class="ea-saat-pick">
                                <label>Tekrar deneme:</label>
                                <select class="form-control" name="arama_saat_sonra">
                                    @for($i=1;$i<=23;$i++)
                                        <option value="{{$i}}" {{($isletme->e_asistan_hatirlatma==$i) ? 'selected' : ''}}>{{$i}} saat sonra</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="ea-setting">
                            <div class="ea-set-head">
                                <div class="ea-set-body">
                                    <div class="ea-set-icon"><i class="fa fa-ban"></i></div>
                                    <h6>Kara Liste Kontrolü</h6>
                                    <p>Kara listedeki numaralara hatırlatma araması yapılmasın.</p>
                                </div>
                                <label class="ea-toggle">
                                    <input type="checkbox" name="e_asistan_karaliste_acik_kapali" {{($e_asistan_ayarlari[5]->acik_kapali) ? 'checked' : ''}}>
                                    <span class="ea-toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                    </div>

                    <div class="ea-save-bar">
                        <button type="submit" class="ea-btn-save">
                            <i class="fa fa-check"></i>
                            Ayarları Güncelle
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
/* Tab count badge'lerini DataTables AJAX response'undan guncelle.
   DataTables init frontend-scripts'te pageindex==60 blogunda yapiliyor —
   DOM ready'de zaten init olmus oluyor; biz xhr.dt event'ine baglaniyoruz. */
(function(){
    function bindBadge(tableId, badgeKey){
        var $t = $(tableId);
        if (!$t.length) return;
        $t.on('xhr.dt', function(e, settings, json){
            var n = (json && (json.recordsTotal !== undefined)) ? json.recordsTotal : 0;
            $('[data-badge="'+badgeKey+'"]').text(n);
        });
        // Init tamamlanmis ve veri zaten gelmis olabilir; bir initial AJAX
        // (re)load tetiklemek yerine current data sayisini yansit.
        if ($.fn.DataTable && $.fn.DataTable.isDataTable(tableId)) {
            var info = $t.DataTable().page.info();
            if (info && info.recordsTotal !== undefined) {
                $('[data-badge="'+badgeKey+'"]').text(info.recordsTotal);
            }
        }
    }
    $(document).ready(function(){
        // Tablolar init olduktan sonra bagla (kucuk gecikme yeter)
        var tries = 0;
        var iv = setInterval(function(){
            tries++;
            var bugunReady = $.fn.DataTable && $.fn.DataTable.isDataTable('#bugunkugorevtablo');
            var yarinReady = $.fn.DataTable && $.fn.DataTable.isDataTable('#yarinkigorevtablo');
            if (bugunReady && yarinReady) {
                clearInterval(iv);
                bindBadge('#bugunkugorevtablo', 'bugun');
                bindBadge('#yarinkigorevtablo', 'yarin');
            } else if (tries > 50) { // ~10s timeout
                clearInterval(iv);
            }
        }, 200);
    });
})();
</script>

@endsection
