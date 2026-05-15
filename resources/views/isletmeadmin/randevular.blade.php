@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<style>
   /* Randevu detay popup gorunumu (eskiden her event icin partial'dan tekrar tekrar gomuluyordu, sayfaya bir kez tasindi) */
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
</style>
<div class="page-header">
   <div class="row">
   <div class="col-md-4 col-sm-6 col-xs-7 col-7">
   <div class="title">
      <h1>{{$sayfa_baslik}}</h1>
   </div>
   <nav aria-label="breadcrumb" role="navigation">
      <ol class="breadcrumb">
         <li class="breadcrumb-item">
            <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
         </li>
         <li class="breadcrumb-item active" aria-current="page">
            {{$sayfa_baslik}}
         </li>
      </ol>
   </nav>
</div>

<div class="col-md-8 col-sm-6 col-xs-5 col-5">
   <div class="d-flex justify-content-end">
   <button class="btn btn-primary mr-2 randevu-count-button">
    Toplam Randevu: {{$randevular['randevu_sayisi']}}
</button>
      
      <a href="#" data-toggle="modal" data-target="#modal-view-event-add" class="btn btn-success btn-lg yenieklebuton">
         <i class="fa fa-plus"></i> Yeni Randevu
      </a>
   </div>
</div>
   </div>
</div>
<div class="pd-20 card-box mb-30" >
   <div class="row" style="margin-bottom: 10px;">

      @if(Auth::guard('satisortakligi')->check() || ( Auth::guard('isletmeyonetim')->check() && !Auth::guard('isletmeyonetim')->user()->hasRole('Personel')))
      <div class="col-md-6 col-sm-6 col-xs-6 col-6">
      @else
      <div class="col-md-6 col-sm-6 col-xs-6 col-6" style="display:none">
      @endif 
         <select class="form-control" id="randevu_ayarina_gore">
                     
                    <option {{($isletme->randevu_takvim_turu==1) ? 'selected' : ''}} value="1">Personele Göre</option>
                    <option {{($isletme->randevu_takvim_turu==0) ? 'selected' : ''}} value="0">Hizmete Göre</option>
                    <option {{($isletme->randevu_takvim_turu==2) ? 'selected' : ''}} value="2">Cihaza Göre</option>
                    <option {{($isletme->randevu_takvim_turu==3) ? 'selected' : ''}} value="3">Odaya Göre</option>
         </select>
      </div>
      
      <div class="col-md-6 col-sm-6 col-xs-6 col-6">
         <input type="text" class="form-control calendardatepicker" autocomplete="off" id='takvim_tarihe_gore' placeholder='Tarih Seçiniz'>
      </div>
   </div>
   <div style="position:relative; width:100%; overflow-y:auto">

      {{-- Aktif gap kampanyalari bilgi seridi --}}
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
         <div id="calendar">
         </div>
      </div>
   </div>




</div>
<div id="hata"></div>

{{-- Gap kampanya gorsel: bilgi seridi + kart rozeti. TAKVIM GRID'INE DOKUNMAZ --}}
<style type="text/css">
  /* Üst bilgi şeridi — takvim wrapper'ın üstünde, FullCalendar'ı etkilemez */
  .gap-info-strip {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 14px;
    margin: 0 0 12px 0;
    background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
    border: 1px solid rgba(251, 191, 36, 0.40);
    border-radius: 12px;
    flex-wrap: wrap;
    font-size: 13px;
    box-shadow: 0 2px 6px rgba(251, 191, 36, 0.08);
  }
  .gap-info-strip .gap-strip-label {
    font-weight: 700;
    color: #92400E;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
  }
  .gap-info-strip .gap-strip-label i { color: #D97706; }
  .gap-info-strip .gap-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 5px 10px;
    background: #ffffff;
    border: 1px solid #FCD34D;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    color: #1f2937;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
  }
  .gap-info-strip .gap-chip.gap-morning   { border-color: #F59E0B; }
  .gap-info-strip .gap-chip.gap-afternoon { border-color: #EA580C; }
  .gap-info-strip .gap-chip.gap-evening   { border-color: #7C3AED; }
  .gap-info-strip .gap-chip-dot {
    width: 10px; height: 10px; border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
  }
  .gap-info-strip .gap-chip-time { font-weight: 600; }
  .gap-info-strip .gap-chip-disc {
    background: linear-gradient(135deg, #22C55E, #16A34A);
    color: #fff;
    padding: 2px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: -0.2px;
    box-shadow: 0 1px 2px rgba(22, 163, 74, 0.25);
  }

  /* Randevu kartı rozeti — kart İÇİNDE konumlu, layout / rendering'i etkilemez */
  #calendar .fc-event.fc-event-gap-discount {
    position: relative;
  }
  #calendar .fc-event.fc-event-gap-discount::after {
    position: absolute;
    top: 2px;
    right: 3px;
    background: linear-gradient(135deg, #22C55E, #16A34A);
    color: #fff;
    font-size: 9px;
    font-weight: 800;
    line-height: 1.1;
    padding: 1.5px 6px;
    border-radius: 999px;
    border: 1.2px solid #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.22);
    letter-spacing: -0.2px;
    pointer-events: none;
    z-index: 10;
  }
  #calendar .fc-event.fc-event-gap-disc-15::after { content: '%15'; }
  #calendar .fc-event.fc-event-gap-disc-20::after { content: '%20'; }
  #calendar .fc-event.fc-event-gap-disc-25::after { content: '%25'; }
  #calendar .fc-event.fc-event-gap-disc-30::after { content: '%30'; }
  #calendar .fc-event.fc-event-gap-disc-35::after { content: '%35'; }
  #calendar .fc-event.fc-event-gap-disc-40::after { content: '%40'; }
  #calendar .fc-event.fc-event-gap-disc-45::after { content: '%45'; }
  #calendar .fc-event.fc-event-gap-disc-50::after { content: '%50'; }
  #calendar .fc-event.fc-event-gap-disc-55::after { content: '%55'; }
  #calendar .fc-event.fc-event-gap-disc-60::after { content: '%60'; }
  #calendar .fc-event.fc-event-gap-disc-65::after { content: '%65'; }
  #calendar .fc-event.fc-event-gap-disc-70::after { content: '%70'; }
</style>

@endsection
