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

/* Page header — kompakt, tek satira yakin --------------------------- */
.ea-hero {
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    padding: 4px 2px 14px;
    margin: 0 0 12px;
    border-bottom: 1px solid var(--line);
}
.ea-hero-left { display: flex; align-items: baseline; gap: 14px; flex-wrap: wrap; }
.ea-hero h1 {
    color: var(--ink); font-size: 18px; font-weight: 700;
    margin: 0; letter-spacing: -0.3px;
}
.ea-breadcrumb { color: var(--ink-3); font-size: 12.5px; }
.ea-breadcrumb a { color: var(--ink-3); text-decoration: none; }
.ea-breadcrumb a:hover { color: var(--brand); }
.ea-breadcrumb .sep { margin: 0 4px; }

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
/* ===== Modern task card feed (Mockup B) ============================ */
.ea-feed { display: flex; flex-direction: column; gap: 10px; }
.ea-task-card {
    display: flex; gap: 14px;
    background: #fff;
    border: 1px solid var(--line);
    border-left: 3px solid var(--brand);
    border-radius: 12px;
    padding: 14px 16px;
    transition: all .15s ease;
}
.ea-task-card:hover {
    border-color: rgba(120,0,179,.25);
    box-shadow: 0 4px 14px rgba(15,9,30,.05);
}
.ea-task-card.t-randevu { border-left-color: #3b82f6; }
.ea-task-card.t-alacak  { border-left-color: #f59e0b; }
.ea-task-card.t-kampanya{ border-left-color: #8b5cf6; }

.ea-task-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    background: var(--brand-soft);
    color: var(--brand);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.t-randevu  .ea-task-icon { background: #dbeafe; color: #3b82f6; }
.t-alacak   .ea-task-icon { background: #fef3c7; color: #d97706; }
.t-kampanya .ea-task-icon { background: #ede9fe; color: #8b5cf6; }

.ea-task-body { flex: 1; min-width: 0; }
.ea-task-top {
    display: flex; align-items: baseline; justify-content: space-between; gap: 12px;
    margin-bottom: 3px;
}
.ea-task-top h4 {
    font-size: 13.5px; font-weight: 700; margin: 0; color: var(--ink);
}
.ea-task-time {
    font-size: 12.5px; font-weight: 700;
    color: var(--brand); white-space: nowrap;
    background: var(--brand-soft);
    padding: 3px 9px; border-radius: 999px;
}
.ea-task-msg {
    font-size: 13px; color: var(--ink-2);
    margin: 0 0 8px; line-height: 1.5;
}
.ea-task-meta {
    display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
}
.ea-task-result {
    font-size: 12.5px; color: var(--ink-3);
    display: inline-flex; align-items: center; gap: 6px;
}
.ea-task-result:before {
    content: ""; width: 4px; height: 4px;
    background: var(--ink-3); border-radius: 50%;
}
.ea-task-actions { margin-left: auto; display: flex; gap: 8px; }

/* Feed icindeki controller'in donen buton stillerini override et */
.ea-feed .btn {
    line-height: 1.3 !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    padding: 5px 11px !important;
    border-radius: 999px !important;
    box-shadow: none !important;
    white-space: nowrap !important;
}
.ea-feed .btn.btn-danger {
    background: var(--danger-soft) !important;
    color: var(--danger) !important;
    border: 1px solid transparent !important;
}
.ea-feed .btn.btn-success {
    background: var(--ok-soft) !important;
    color: var(--ok) !important;
    border: 1px solid transparent !important;
}
.ea-feed .btn[name^="gorev_iptal_et"] {
    background: #fff !important;
    color: var(--danger) !important;
    border: 1px solid var(--danger-soft) !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
}
.ea-feed .btn[name^="gorev_iptal_et"]:hover {
    background: var(--danger-soft) !important;
}
.ea-feed .btn[name="kampanya_detay"] {
    background: var(--brand-soft) !important;
    color: var(--brand) !important;
    border: 1px solid transparent !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
}

/* Empty state */
.ea-empty {
    text-align: center;
    padding: 60px 20px;
    color: var(--ink-3);
}
.ea-empty .icon-wrap {
    width: 64px; height: 64px;
    background: var(--ok-soft);
    color: var(--ok);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    margin: 0 auto 14px;
}
.ea-empty h3 {
    font-size: 16px; font-weight: 700; color: var(--ink);
    margin: 0 0 4px;
}
.ea-empty p { font-size: 13.5px; margin: 0; }

/* Loading skeleton */
.ea-loading {
    text-align: center; padding: 40px 20px;
    color: var(--ink-3); font-size: 13px;
}
.ea-loading i { margin-right: 6px; }

/* DataTables UI'si gizle — biz data'yi xhr.dt event'i ile aliyoruz */
#bugunkugorevtablo_wrapper, #yarinkigorevtablo_wrapper { display: none !important; }
.ea-hidden-table { display: none; }

@media (max-width: 575.98px) {
    .ea-card { padding: 14px; }
    .ea-tabs .nav-link { padding: 9px 14px; font-size: 13px; }
    .ea-settings-grid { grid-template-columns: 1fr; }
    .ea-task-card { padding: 12px; gap: 10px; }
    .ea-task-icon { width: 36px; height: 36px; font-size: 14px; }
    .ea-task-actions { margin-left: 0; width: 100%; }
}
</style>

<div class="ea-page">

    <div class="ea-hero">
        <div class="ea-hero-left">
            <h1>Asistanım</h1>
            <div class="ea-breadcrumb">
                <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                <span class="sep">›</span>
                <span>Asistanım</span>
            </div>
        </div>
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
                <div class="ea-feed" id="bugun-feed">
                    <div class="ea-loading"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>
                </div>
                {{-- DataTables init'i bozmayalim diye tablo DOM'da gizli kaliyor --}}
                <div class="ea-hidden-table">
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
        </div>

        {{-- ============================== YARIN ============================== --}}
        <div class="tab-pane fade" id="yarinki_gorevler" role="tabpanel">
            <div class="ea-card">
                <div class="ea-feed" id="yarin-feed">
                    <div class="ea-loading"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>
                </div>
                <div class="ea-hidden-table">
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
/* ===========================================================================
   E-Asistan kart feed render'i
   DataTables init frontend-scripts'te yapiliyor (tablo DOM'da gizli).
   Biz xhr.dt event'i ile gelen veriyi yakalayip kart olarak basiyoruz.
   Sayisi tab badge'lerine yansiyor. Gorev iptal sonrasi ajax.reload otomatik
   tekrar render tetikliyor (custom.js degismeden calisir).
   =========================================================================== */
(function(){
    function escapeHtml(s){
        if (s === null || s === undefined) return '';
        return String(s).replace(/[&<>"']/g, function(c){
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];
        });
    }

    function typeOf(baslik){
        var b = String(baslik || '');
        if (b.indexOf('Randevu')  !== -1) return {cls:'t-randevu',  icon:'fa fa-calendar-check-o'};
        if (b.indexOf('Alacak')   !== -1) return {cls:'t-alacak',   icon:'fa fa-money'};
        if (b.indexOf('Kampanya') !== -1) return {cls:'t-kampanya', icon:'fa fa-bullhorn'};
        return {cls:'', icon:'fa fa-bell'};
    }

    function renderFeed($target, items){
        if (!items || items.length === 0){
            $target.html(
                '<div class="ea-empty">' +
                  '<div class="icon-wrap"><i class="fa fa-check"></i></div>' +
                  '<h3>Bu sekmede görev yok</h3>' +
                  '<p>E-asistanın yapacağı bir hatırlatma bulunmuyor.</p>' +
                '</div>'
            );
            return;
        }
        var html = '';
        items.forEach(function(item){
            var t = typeOf(item.baslik);
            var saat = escapeHtml(item.saat || '');
            html += '<div class="ea-task-card ' + t.cls + '">';
            html += '  <div class="ea-task-icon"><i class="' + t.icon + '"></i></div>';
            html += '  <div class="ea-task-body">';
            html += '    <div class="ea-task-top">';
            html += '      <h4>' + escapeHtml(item.baslik) + '</h4>';
            if (saat) html += '      <span class="ea-task-time">' + saat + '</span>';
            html += '    </div>';
            html += '    <p class="ea-task-msg">' + escapeHtml(item.mesaj) + '</p>';
            html += '    <div class="ea-task-meta">';
            // durum: controller HTML donuyor (btn-danger/btn-success rozet) — escape ETME
            if (item.durum) html += '      <div class="ea-task-status">' + item.durum + '</div>';
            // sonuc: varsayilan "Hatirlatma aramasi yapilacak." baslikta zaten anlasiliyor,
            // sadece gercek bir durum bilgisi varsa goster (Randevuya gelecek, Ulasilamadi vs.).
            var sonuc = (item.sonuc || '').trim();
            var isDefaultSonuc = sonuc === 'Hatırlatma araması yapılacak.' || sonuc === 'Hatirlatma aramasi yapilacak.';
            if (sonuc && !isDefaultSonuc) {
                html += '      <div class="ea-task-result">' + escapeHtml(sonuc) + '</div>';
            }
            // islemler: controller HTML donuyor (gorev iptal et butonu) — escape ETME
            if (item.islemler) html += '      <div class="ea-task-actions">' + item.islemler + '</div>';
            html += '    </div>';
            html += '  </div>';
            html += '</div>';
        });
        $target.html(html);
    }

    function bind(tableId, feedSel, badgeKey){
        var $t = $(tableId);
        if (!$t.length) return;
        $t.on('xhr.dt', function(e, settings, json){
            var data = (json && json.data) ? json.data : [];
            var total = (json && (json.recordsTotal !== undefined)) ? json.recordsTotal : data.length;
            renderFeed($(feedSel), data);
            $('[data-badge="'+badgeKey+'"]').text(total);
        });
    }

    $(document).ready(function(){
        var tries = 0;
        var iv = setInterval(function(){
            tries++;
            var b = $.fn.DataTable && $.fn.DataTable.isDataTable('#bugunkugorevtablo');
            var y = $.fn.DataTable && $.fn.DataTable.isDataTable('#yarinkigorevtablo');
            if (b && y){
                clearInterval(iv);
                bind('#bugunkugorevtablo', '#bugun-feed', 'bugun');
                bind('#yarinkigorevtablo', '#yarin-feed', 'yarin');
            } else if (tries > 50){
                clearInterval(iv);
            }
        }, 200);
    });
})();
</script>

@endsection
