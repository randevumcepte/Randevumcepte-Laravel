@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif
@extends($_layout)

@section('content')
{{-- V2 TAKVIM SAYFASI: randevular.blade.php'nin takvim yapisi ile birebir,
     fark: window.useV2Modal=true flag'i ile slot tiklaninca v1 yerine v2 acilir. --}}

<script>
    // Bu flag #modal-view-event-add (v1) modali acilmaya calistiginda
    // randevu-ekle-modal-v2.blade.php icindeki intercept tarafindan kullanilir.
    // V1 modali sayfa ustunde duruyor (DOM'da include edili) ki paket akisi/
    // submit proxy mantigi calissin — ama gorsel olarak v2 acilir.
    window.useV2Modal = true;
</script>

<style>
    .rd-detail { font-size:13.5px; color:#3a2e57; margin:-10px -15px; }
    .rd-detail .rd-row { display:flex; align-items:flex-start; padding:9px 14px; border-bottom:1px solid #f1ecf7; gap:10px; }
    .rd-detail .rd-row:last-child { border-bottom:0; }
    .rd-detail .rd-row:nth-child(odd) { background:#fbfafd; }
    .rd-detail .rd-label { flex:0 0 160px; color:#7c6c8a; font-weight:600; font-size:12.5px; display:flex; align-items:center; gap:6px; }
    .rd-detail .rd-label i { color:#5C008E; opacity:.75; width:14px; text-align:center; }
    .rd-detail .rd-value { flex:1; color:#2d2143; font-weight:500; word-break:break-word; }
    .rd-detail .rd-value.empty { color:#bcb3c9; font-style:italic; font-weight:400; }
    .rd-status { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:700; }
    .rd-status.beklemede { background:#fff4e0; color:#a86200; }
    .rd-status.basarili  { background:#e6f9ed; color:#0c7a3a; }
    .rd-status.iptal     { background:#fdecec; color:#c81e1e; }
    .rd-status.geldi     { background:#e6f9ed; color:#0c7a3a; }
    .rd-status.gelmedi   { background:#fdecec; color:#c81e1e; }
    .rdb-row { display:flex; gap:8px; flex-wrap:wrap; width:100%; }
    .rdb-row .btn { flex: 1 1 130px; min-width: 0; border-radius: 8px; font-weight: 600; font-size: 13px; padding: 9px 12px; line-height: 1.2; white-space: normal; }
    .rdb-row .btn i { margin-right: 4px; }
    .rdb-row .rdb-pull-right { margin-left: auto; flex-grow: 0; }

    body > .select2-container--open { z-index: 100015 !important; }
    body > .ts-dropdown { z-index: 100015 !important; }
    #softPaketSecimModal { z-index: 100020 !important; }
    .sweet-overlay { z-index: 100029 !important; }
    .sweet-alert   { z-index: 100030 !important; }
    .swal2-container { z-index: 100030 !important; }

    /* V2 hover tooltip — randevunun uzerine gelince acilir minik kart */
    #rc-event-tip {
        position: fixed;
        z-index: 99999;
        min-width: 240px;
        max-width: 320px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 12px 32px rgba(40, 12, 80, 0.18), 0 2px 6px rgba(40, 12, 80, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.06);
        pointer-events: none;
        opacity: 0;
        transition: opacity 120ms ease, transform 120ms ease;
        transform: translateY(4px);
        overflow: hidden;
        font-family: inherit;
    }
    #rc-event-tip.rc-tip-show { opacity: 1; transform: translateY(0); }
    #rc-event-tip .rc-tip { font-size: 12.5px; color: #2d2143; }
    #rc-event-tip .rc-tip-head {
        background: var(--rc-tip-bg, #5C008E);
        color: #fff;
        padding: 10px 14px 9px;
    }
    #rc-event-tip .rc-tip-name {
        font-size: 14px;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    #rc-event-tip .rc-tip-meta {
        display: flex; align-items: center; flex-wrap: wrap; gap: 6px;
        font-size: 11.5px; opacity: .95;
    }
    #rc-event-tip .rc-tip-meta i { margin-right: 3px; opacity: .9; }
    #rc-event-tip .rc-tip-dur { opacity: .85; }
    #rc-event-tip .rc-tip-status {
        margin-left: auto;
        padding: 2px 9px;
        border-radius: 999px;
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: .2px;
        text-transform: uppercase;
        background: rgba(255,255,255,0.25);
        color: #fff;
    }
    #rc-event-tip .rc-tip-status.rc-st-geldi      { background: #e6f9ed; color: #0c7a3a; }
    #rc-event-tip .rc-tip-status.rc-st-gelmedi    { background: #fdecec; color: #c81e1e; }
    #rc-event-tip .rc-tip-status.rc-st-beklemede  { background: #fff4e0; color: #a86200; }
    #rc-event-tip .rc-tip-status.rc-st-gelecek    { background: #ffe6d4; color: #b34a00; }
    #rc-event-tip .rc-tip-body { padding: 8px 14px 10px; background: #fff; }
    #rc-event-tip .rc-tip-row {
        display: flex; align-items: flex-start; gap: 8px;
        padding: 4px 0;
        font-size: 12.5px;
        line-height: 1.35;
        color: #3a2e57;
    }
    #rc-event-tip .rc-tip-row + .rc-tip-row { border-top: 1px dashed #f1ecf7; }
    #rc-event-tip .rc-tip-row i {
        flex: 0 0 14px;
        color: var(--rc-tip-bg, #5C008E);
        opacity: .8;
        margin-top: 2px;
        text-align: center;
    }
    #rc-event-tip .rc-tip-row span {
        flex: 1;
        word-break: break-word;
        color: #2d2143;
        font-weight: 500;
    }
    #rc-event-tip .rc-tip-note span { color: #5b4d75; font-style: italic; }
    /* Mobilde / dokunmatik cihazlarda gizle (hover yok zaten) */
    @media (hover: none) {
        #rc-event-tip { display: none !important; }
    }

    /* V2 sayfasinda ust bar ozel rozet */
    .v2-page-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: linear-gradient(135deg,#4f46e5,#7c3aed);
        color: #fff;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        margin-left: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<div class="page-header">
    <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-7 col-7">
            <div class="title">
                <h1>{{ $sayfa_baslik }} <span class="v2-page-badge"><i class="fa fa-magic"></i> Yeni Tasarım</span></h1>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/isletmeyonetim{{ (isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $sayfa_baslik }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-8 col-sm-6 col-xs-5 col-5">
            <div class="d-flex justify-content-end">
                <a href="/isletmeyonetim/randevular{{ (isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="btn btn-outline-secondary mr-2" title="Eski takvime dön">
                    <i class="fa fa-arrow-left"></i> Eski Takvim
                </a>
                <button class="btn btn-primary mr-2 randevu-count-button">
                    Toplam Randevu: {{ $randevular['randevu_sayisi'] }}
                </button>
                @yetki('randevu.olustur')
                <a href="#" data-toggle="modal" data-target="#modal-view-event-add-v2" class="btn btn-lg" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;">
                    <i class="fa fa-plus"></i> Yeni Randevu
                </a>
                @endyetki
            </div>
        </div>
    </div>
</div>

<div class="pd-20 card-box mb-30">
    <div class="row" style="margin-bottom: 10px;">
        @if(Auth::guard('satisortakligi')->check() || (Auth::guard('isletmeyonetim')->check() && !Auth::guard('isletmeyonetim')->user()->hasRole('Personel')))
        <div class="col-md-6 col-sm-6 col-xs-6 col-6">
        @else
        <div class="col-md-6 col-sm-6 col-xs-6 col-6" style="display:none">
        @endif
            <select class="form-control" id="randevu_ayarina_gore">
                <option {{ ($isletme->randevu_takvim_turu==1) ? 'selected' : '' }} value="1">Personele Göre</option>
                <option {{ ($isletme->randevu_takvim_turu==0) ? 'selected' : '' }} value="0">Hizmete Göre</option>
                <option {{ ($isletme->randevu_takvim_turu==2) ? 'selected' : '' }} value="2">Cihaza Göre</option>
                <option {{ ($isletme->randevu_takvim_turu==3) ? 'selected' : '' }} value="3">Odaya Göre</option>
            </select>
        </div>
        <div class="col-md-6 col-sm-6 col-xs-6 col-6">
            <input type="text" class="form-control calendardatepicker" autocomplete="off" id='takvim_tarihe_gore' placeholder='Tarih Seçiniz'>
        </div>
    </div>
    <div style="position:relative; width:100%; overflow-y:auto">
        @if(!empty($gapKampanyalari))
        <div class="gap-info-strip">
            <span class="gap-strip-label"><i class="fa fa-tag"></i> Aktif Kampanya:</span>
            @foreach($gapKampanyalari as $k)
            <span class="gap-chip gap-{{ $k['gapKey'] }}" title="{{ $k['gapLabel'] }} Kampanyası — %{{ $k['discount'] }} indirim">
                <span class="gap-chip-dot" style="background:{{ $k['color'] }}"></span>
                <span class="gap-chip-time">{{ $k['gapLabel'] }} {{ sprintf('%02d:00-%02d:00', $k['startHour'], $k['endHour']) }}</span>
                <span class="gap-chip-disc">%{{ $k['discount'] }}</span>
            </span>
            @endforeach
        </div>
        @endif

        <div class="calendar-wrap">
            <div id="calendar"></div>
        </div>
    </div>
</div>
<div id="hata"></div>

<style type="text/css">
    .gap-info-strip {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 14px; margin: 0 0 12px 0;
        background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
        border: 1px solid rgba(251, 191, 36, 0.40);
        border-radius: 12px; flex-wrap: wrap;
        font-size: 13px; box-shadow: 0 2px 6px rgba(251, 191, 36, 0.08);
    }
    .gap-info-strip .gap-strip-label { font-weight: 700; color: #92400E; display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; }
    .gap-info-strip .gap-strip-label i { color: #D97706; }
    .gap-info-strip .gap-chip { display: inline-flex; align-items: center; gap: 7px; padding: 5px 10px; background: #fff; border: 1px solid #FCD34D; border-radius: 999px; font-size: 12px; font-weight: 600; color: #1f2937; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04); }
    .gap-info-strip .gap-chip-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
    .gap-info-strip .gap-chip-time { font-weight: 600; }
    .gap-info-strip .gap-chip-disc { background: linear-gradient(135deg, #22C55E, #16A34A); color: #fff; padding: 2px 9px; border-radius: 999px; font-size: 11px; font-weight: 800; letter-spacing: -0.2px; }
</style>

@endsection
