@extends('layout.layout_isletmeadmin')
@section('content')
@php
  $aylar = [1=>'Ocak',2=>'Şubat',3=>'Mart',4=>'Nisan',5=>'Mayıs',6=>'Haziran',7=>'Temmuz',8=>'Ağustos',9=>'Eylül',10=>'Ekim',11=>'Kasım',12=>'Aralık'];
  $toplamMaas = array_sum(array_column($rapor,'maas'));
  $toplamPrim = array_sum(array_column($rapor,'prim_toplam'));
  $toplamBonus = array_sum(array_column($rapor,'bonus'));
  $toplamKesinti = array_sum(array_column($rapor,'kesinti'));
  $toplamNet = array_sum(array_column($rapor,'net_hakedis'));
@endphp
<style>
  .primRapor-ozet .widget-style3{transition:transform .15s}
  .primRapor-ozet .widget-style3:hover{transform:translateY(-2px)}
  #primrapor_tablo td,#primrapor_tablo th{vertical-align:middle}

  /* ============ Prim Hareket Modal — Modern Tasarim ============ */
  #primHareketListeModal .modal-dialog,
  #primHareketModal .modal-dialog{
    max-width: 720px !important;
    width: 92vw;
    margin: 1.75rem auto !important;
    display: flex; align-items: center; min-height: calc(100vh - 3.5rem);
  }
  #primHareketListeModal .modal-content,
  #primHareketModal .modal-content{
    border: 0;
    border-radius: 18px;
    box-shadow: 0 25px 60px rgba(0,0,0,.25);
    overflow: hidden;
    width: 100%;
    animation: primModalIn .35s cubic-bezier(.2,.8,.2,1);
  }
  @keyframes primModalIn{ from{ opacity:0; transform: translateY(20px) scale(.96);} to{ opacity:1; transform: translateY(0) scale(1);} }

  #primHareketListeModal .modal-header,
  #primHareketModal .modal-header{
    background: linear-gradient(135deg,#6366f1 0%,#8b5cf6 50%,#ec4899 100%);
    color: #fff; border: 0; padding: 20px 26px;
    position: relative;
  }
  #primHareketListeModal .modal-header .modal-title,
  #primHareketModal .modal-header .modal-title{
    color: #fff; font-weight: 700; font-size: 18px; display: flex; align-items: center; gap: 10px;
  }
  #primHareketListeModal .modal-header .close,
  #primHareketModal .modal-header .close{
    color: #fff; opacity: .85; font-size: 28px; font-weight: 300; text-shadow: none;
    position: absolute; right: 18px; top: 14px;
  }
  #primHareketListeModal .modal-header .close:hover,
  #primHareketModal .modal-header .close:hover{ opacity: 1; }
  .prim-modal-personel{
    display:inline-block; margin-top:6px; padding:4px 12px; background:rgba(255,255,255,.22);
    border-radius:20px; font-size:12px; font-weight:600; backdrop-filter: blur(6px);
  }
  .prim-modal-donem{
    color: rgba(255,255,255,.9); font-size:12px; margin-left:8px; font-weight:500;
  }

  #primHareketListeModal .modal-body{ padding: 22px 26px; background:#fafbfc; }
  #primHareketModal .modal-body{ padding: 22px 26px; }
  #primHareketListeModal .modal-footer,
  #primHareketModal .modal-footer{
    border-top: 1px solid #eef0f3; padding: 14px 22px; background:#fff;
  }

  /* Ozet kartlari (liste modal usten) */
  .prim-ozet-row{ display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px; margin-bottom:18px; }
  .prim-ozet-card{
    padding:14px 16px; border-radius:14px; background:#fff;
    box-shadow: 0 2px 6px rgba(0,0,0,.04); border:1px solid #eef0f3;
  }
  .prim-ozet-card .lbl{ font-size:11px; color:#9ca3af; font-weight:600; letter-spacing:.5px; text-transform:uppercase; }
  .prim-ozet-card .val{ font-size:18px; font-weight:700; margin-top:4px; }
  .prim-ozet-card.bonus .val{ color:#10b981; }
  .prim-ozet-card.kesinti .val{ color:#ef4444; }
  .prim-ozet-card.net .val{ color:#6366f1; }

  /* Hareket karti */
  .hareketler-listesi{
    max-height: 360px; overflow-y: auto;
    background: transparent; border: 0; padding: 0;
    display: flex; flex-direction: column; gap: 10px;
  }
  .hareketler-listesi::-webkit-scrollbar{ width:6px; }
  .hareketler-listesi::-webkit-scrollbar-thumb{ background:#cbd5e1; border-radius:3px; }

  .hareket-item{
    display: flex !important; align-items:center; gap: 14px;
    background: #fff; border-radius: 12px; padding: 14px 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04); border-left: 4px solid transparent;
    transition: all .2s; border-bottom: 0;
  }
  .hareket-item:hover{ box-shadow: 0 4px 12px rgba(0,0,0,.08); transform: translateX(2px); }
  .hareket-item.tip-bonus{ border-left-color:#10b981; }
  .hareket-item.tip-kesinti{ border-left-color:#ef4444; }

  .hareket-icon{
    width: 42px; height: 42px; border-radius: 50%; display:flex; align-items:center; justify-content:center;
    flex-shrink: 0; font-size: 18px;
  }
  .hareket-icon.bonus{ background:#dcfce7; color:#10b981; }
  .hareket-icon.kesinti{ background:#fee2e2; color:#ef4444; }

  .hareket-info{ flex:1; min-width:0; }
  .hareket-info .row1{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
  .hareket-info .tutar{ font-size: 17px; font-weight: 700; }
  .hareket-info .tutar.bonus{ color:#10b981; }
  .hareket-info .tutar.kesinti{ color:#ef4444; }
  .hareket-info .tarih{ font-size: 12px; color:#9ca3af; font-weight:500; display:inline-flex; align-items:center; gap:4px; }
  .hareket-info .aciklama{ font-size: 13px; color:#4b5563; margin-top: 4px; line-height: 1.45; }

  .prim-hareket-sil{
    cursor:pointer; width: 34px; height:34px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    color:#94a3b8; background:transparent; border:0; transition:all .2s; flex-shrink:0;
  }
  .prim-hareket-sil:hover{ color:#ef4444; background:#fee2e2; }

  /* Empty state */
  .hareket-empty{
    text-align:center; padding: 40px 20px; color:#9ca3af;
  }
  .hareket-empty .icon{
    width:72px; height:72px; border-radius:50%; background:#f3f4f6;
    display:inline-flex; align-items:center; justify-content:center; font-size:32px;
    color:#cbd5e1; margin-bottom:14px;
  }
  .hareket-empty .baslik{ font-size:15px; font-weight:600; color:#6b7280; margin-bottom:4px; }
  .hareket-empty .alt{ font-size:13px; color:#9ca3af; }

  /* Bonus/Kesinti ekleme modal — tip secici buyuk butonlar */
  .prim-tip-radio{ display:grid; grid-template-columns: 1fr 1fr; gap:10px; }
  .prim-tip-radio input[type=radio]{ display:none; }
  .prim-tip-radio label{
    cursor:pointer; padding:14px 16px; border-radius:12px; border:2px solid #e5e7eb;
    text-align:center; font-weight:600; transition:all .15s; margin:0;
    display:flex; flex-direction:column; align-items:center; gap:6px;
  }
  .prim-tip-radio label .ic{ font-size: 22px; }
  .prim-tip-radio input[value=bonus]:checked + label{ border-color:#10b981; background:#ecfdf5; color:#065f46; }
  .prim-tip-radio input[value=kesinti]:checked + label{ border-color:#ef4444; background:#fef2f2; color:#991b1b; }
  .prim-tip-radio input[value=bonus] + label .ic{ color:#10b981; }
  .prim-tip-radio input[value=kesinti] + label .ic{ color:#ef4444; }

  .prim-form-group{ margin-bottom: 16px; }
  .prim-form-group label{ font-weight:600; color:#374151; font-size:13px; margin-bottom:6px; display:block; }
  .prim-form-group .form-control{ border-radius:10px; border-color:#e5e7eb; padding: 10px 14px; font-size:14px; }
  .prim-form-group .form-control:focus{ border-color:#6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
  .prim-tutar-input{ position:relative; }
  .prim-tutar-input .form-control{ padding-left: 36px; font-size:18px; font-weight:700; }
  .prim-tutar-input::before{ content:'₺'; position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:16px; font-weight:700; z-index:2; }

  .prim-btn-kaydet{
    background: linear-gradient(135deg,#6366f1,#8b5cf6); border:0;
    color:#fff; font-weight:600; padding:10px 28px; border-radius:10px;
    transition:all .2s; box-shadow: 0 4px 12px rgba(99,102,241,.3);
  }
  .prim-btn-kaydet:hover{ transform: translateY(-1px); box-shadow: 0 6px 18px rgba(99,102,241,.4); color:#fff; }
  .prim-btn-iptal{
    background:#f3f4f6; color:#6b7280; border:0; padding:10px 22px; border-radius:10px; font-weight:600;
  }
  .prim-btn-iptal:hover{ background:#e5e7eb; color:#374151; }

  @media(max-width:600px){
    .prim-ozet-row{ grid-template-columns: 1fr; }
    #primHareketListeModal .modal-dialog,
    #primHareketModal .modal-dialog{ width: 96vw; }
  }
</style>

<div class="page-header">
  <div class="row">
    <div class="col-md-12 col-sm-12">
      <div class="title">
        <h1 style="font-size:20px"><i class="fa fa-money" style="color:#28a745"></i> {{$sayfa_baslik}}</h1>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
          </li>
          <li class="breadcrumb-item active">{{$sayfa_baslik}}</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

<div class="card-box mb-30">
  <div class="pd-20">
    <form method="get" id="primRaporFiltre" class="row">
      <input type="hidden" name="sube" value="{{$isletme->id}}">
      <div class="col-md-3 col-sm-4 col-6">
        <label>Ay</label>
        <select class="form-control" name="ay" onchange="document.getElementById('primRaporFiltre').submit()">
          @foreach($aylar as $ayNo => $ayAdi)
            <option value="{{$ayNo}}" {{$ayNo==$ay?'selected':''}}>{{$ayAdi}}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3 col-sm-4 col-6">
        <label>Yıl</label>
        <select class="form-control" name="yil" onchange="document.getElementById('primRaporFiltre').submit()">
          @for($y=date('Y'); $y>=date('Y')-4; $y--)
            <option value="{{$y}}" {{$y==$yil?'selected':''}}>{{$y}}</option>
          @endfor
        </select>
      </div>
      <div class="col-md-6 col-sm-4 col-12 text-right" style="align-self:end; margin-top:10px">
        <span style="color:#888; font-size:13px"><i class="fa fa-calendar"></i> {{date('d.m.Y', strtotime($tarih1))}} - {{date('d.m.Y', strtotime($tarih2))}}</span>
      </div>
    </form>
  </div>

  <div class="pd-20 primRapor-ozet">
    <div class="row">
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-6 mb-20">
        <div class="card-box height-100-p widget-style3">
          <div class="d-flex flex-wrap">
            <div class="widget-data">
              <div class="weight-700 font-18 text-dark">{{number_format($toplamMaas,2,',','.')}}</div>
              <div class="font-13 text-secondary weight-500">Toplam Maaş ₺</div>
            </div>
            <div class="widget-icon" style="background-color:#6c757d"><div class="icon" style="color:#fff">₺</div></div>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-6 mb-20">
        <div class="card-box height-100-p widget-style3">
          <div class="d-flex flex-wrap">
            <div class="widget-data">
              <div class="weight-700 font-18 text-dark">{{number_format($toplamPrim,2,',','.')}}</div>
              <div class="font-13 text-secondary weight-500">Toplam Prim ₺</div>
            </div>
            <div class="widget-icon" style="background-color:#17a2b8"><div class="icon" style="color:#fff">%</div></div>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-6 mb-20">
        <div class="card-box height-100-p widget-style3">
          <div class="d-flex flex-wrap">
            <div class="widget-data">
              <div class="weight-700 font-18 text-dark" style="color:#28a745">{{number_format($toplamBonus,2,',','.')}}</div>
              <div class="font-13 text-secondary weight-500">Toplam Bonus ₺</div>
            </div>
            <div class="widget-icon" style="background-color:#28a745"><div class="icon" style="color:#fff">+</div></div>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-6 mb-20">
        <div class="card-box height-100-p widget-style3">
          <div class="d-flex flex-wrap">
            <div class="widget-data">
              <div class="weight-700 font-18 text-dark" style="color:#dc3545">{{number_format($toplamKesinti,2,',','.')}}</div>
              <div class="font-13 text-secondary weight-500">Toplam Kesinti ₺</div>
            </div>
            <div class="widget-icon" style="background-color:#dc3545"><div class="icon" style="color:#fff">−</div></div>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-lg-8 col-md-8 col-sm-12 col-12 mb-20">
        <div class="card-box height-100-p widget-style3" style="background:linear-gradient(90deg,#007bff,#0056b3); color:#fff">
          <div class="d-flex flex-wrap">
            <div class="widget-data">
              <div class="weight-700 font-22" style="color:#fff">{{number_format($toplamNet,2,',','.')}} ₺</div>
              <div class="font-14 weight-500" style="color:rgba(255,255,255,0.85)">NET ÖDENECEK (Maaş+Prim+Bonus−Kesinti)</div>
            </div>
            <div class="widget-icon" style="background-color:rgba(255,255,255,0.18)"><div class="icon" style="color:#fff"><i class="fa fa-credit-card"></i></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="pd-20">
    <table class="data-table table stripe hover nowrap" id="primrapor_tablo" style="width:100%">
      <thead>
        <tr>
          <th>Personel</th>
          <th>Maaş</th>
          <th>Hizmet Primi</th>
          <th>Ürün Primi</th>
          <th>Paket Primi</th>
          <th>Prim Toplam</th>
          <th>Bonus</th>
          <th>Kesinti</th>
          <th>NET Ödenecek</th>
          <th class="datatable-nosort">İşlemler</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rapor as $r)
          <tr>
            <td><strong>{{$r['personel_adi']}}</strong></td>
            <td>{{number_format($r['maas'],2,',','.')}} ₺</td>
            <td>{{number_format($r['hizmet_primi'],2,',','.')}} ₺</td>
            <td>{{number_format($r['urun_primi'],2,',','.')}} ₺</td>
            <td>{{number_format($r['paket_primi'],2,',','.')}} ₺</td>
            <td><strong>{{number_format($r['prim_toplam'],2,',','.')}} ₺</strong></td>
            <td style="color:#28a745"><strong>+{{number_format($r['bonus'],2,',','.')}}</strong>@if($r['hareket_sayisi']>0) <small style="color:#999">({{$r['hareket_sayisi']}})</small>@endif</td>
            <td style="color:#dc3545"><strong>−{{number_format($r['kesinti'],2,',','.')}}</strong></td>
            <td style="background:#f1f8ff"><strong style="font-size:15px; color:#007bff">{{number_format($r['net_hakedis'],2,',','.')}} ₺</strong></td>
            <td>
              <button class="btn btn-sm btn-success prim-bonus-ekle" data-value="{{$r['personel_id']}}" data-adi="{{$r['personel_adi']}}" title="Bonus/Kesinti Ekle">
                <i class="fa fa-plus"></i>
              </button>
              <button class="btn btn-sm btn-info prim-hareket-goster" data-value="{{$r['personel_id']}}" data-adi="{{$r['personel_adi']}}" title="Hareketleri Görüntüle">
                <i class="fa fa-list"></i>
              </button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- ========== Bonus/Kesinti Ekleme Modal ========== --}}
<div class="modal fade" id="primHareketModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form id="primHareketForm">
        {!!csrf_field()!!}
        <input type="hidden" name="sube" value="{{$isletme->id}}">
        <input type="hidden" name="personel_id" id="primHareket_personelId">
        <div class="modal-header">
          <div>
            <h4 class="modal-title"><i class="fa fa-plus-circle"></i> <span>Prim Hareketi Ekle</span></h4>
            <span class="prim-modal-personel" id="primHareket_personelAdi"></span>
          </div>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="prim-form-group">
            <label>Hareket Tipi</label>
            <div class="prim-tip-radio">
              <input type="radio" id="prtip_bonus" name="tip" value="bonus" checked>
              <label for="prtip_bonus"><span class="ic">＋</span>Bonus / Ek Ödeme</label>
              <input type="radio" id="prtip_kesinti" name="tip" value="kesinti">
              <label for="prtip_kesinti"><span class="ic">−</span>Kesinti</label>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="prim-form-group">
                <label>Tutar</label>
                <div class="prim-tutar-input">
                  <input type="number" step="0.01" min="0.01" class="form-control" name="tutar" placeholder="0,00" required>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="prim-form-group">
                <label>Tarih</label>
                <input type="date" class="form-control" name="tarih" value="{{date('Y-m-d')}}" required>
              </div>
            </div>
          </div>
          <div class="prim-form-group">
            <label>Açıklama <small style="color:#9ca3af; font-weight:400">(opsiyonel)</small></label>
            <textarea class="form-control" name="aciklama" rows="2" maxlength="300" placeholder="Ör: Ay sonu performans bonusu / Geç gelme kesintisi"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="prim-btn-iptal" data-dismiss="modal">İptal</button>
          <button type="submit" class="prim-btn-kaydet"><i class="fa fa-check"></i> Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ========== Hareket Geçmişi Modal ========== --}}
<div class="modal fade" id="primHareketListeModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h4 class="modal-title"><i class="fa fa-history"></i> <span>Prim Hareketleri</span></h4>
          <span class="prim-modal-personel" id="primListe_personelAdi"></span>
          <span class="prim-modal-donem"><i class="fa fa-calendar"></i> {{date('d.m.Y', strtotime($tarih1))}} — {{date('d.m.Y', strtotime($tarih2))}}</span>
        </div>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="prim-ozet-row" id="primListe_ozet" style="display:none">
          <div class="prim-ozet-card bonus">
            <div class="lbl">Toplam Bonus</div>
            <div class="val" id="primListe_toplamBonus">0,00 ₺</div>
          </div>
          <div class="prim-ozet-card kesinti">
            <div class="lbl">Toplam Kesinti</div>
            <div class="val" id="primListe_toplamKesinti">0,00 ₺</div>
          </div>
          <div class="prim-ozet-card net">
            <div class="lbl">Net Etki</div>
            <div class="val" id="primListe_netEtki">0,00 ₺</div>
          </div>
        </div>
        <div class="hareketler-listesi" id="primHareketListesi">
          <div class="text-center text-muted" style="padding:30px">Yükleniyor...</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="prim-btn-iptal" data-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
</div>

<script>
$(function(){
  var _csrf = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
  var _sube = {{$isletme->id}};
  var _tarih1 = '{{$tarih1}}';
  var _tarih2 = '{{$tarih2}}';

  var _ayAdi = $('#primRaporFiltre select[name="ay"] option:selected').text();
  var _yilAdi = $('#primRaporFiltre select[name="yil"] option:selected').text();
  var _isletmeAdi = @json($isletme->salon_adi);
  var _dosyaAdi = 'Prim_Hakedis_'+_ayAdi+'_'+_yilAdi;

  $('#primrapor_tablo').DataTable({
    pageLength: 50,
    order: [[8,'desc']],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json' },
    dom: '<"d-flex justify-content-between align-items-center mb-2"<"d-flex"l><"d-flex"B>>frtip',
    buttons: [
      { extend: 'excelHtml5',  text: '<i class="fa fa-file-excel-o"></i> Excel',  className: 'btn btn-success btn-sm', title: _dosyaAdi, exportOptions: { columns: [0,1,2,3,4,5,6,7,8] } },
      { extend: 'pdfHtml5',    text: '<i class="fa fa-file-pdf-o"></i> PDF',      className: 'btn btn-danger btn-sm',  title: _isletmeAdi+' - Prim & Hak Ediş ('+_ayAdi+' '+_yilAdi+')', orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: [0,1,2,3,4,5,6,7,8] } },
      { extend: 'print',       text: '<i class="fa fa-print"></i> Yazdır',         className: 'btn btn-secondary btn-sm', title: _isletmeAdi+' - Prim & Hak Ediş ('+_ayAdi+' '+_yilAdi+')', exportOptions: { columns: [0,1,2,3,4,5,6,7,8] } }
    ]
  });

  $(document).on('click','.prim-bonus-ekle', function(){
    $('#primHareket_personelId').val($(this).data('value'));
    $('#primHareket_personelAdi').text($(this).data('adi'));
    $('#primHareketForm')[0].reset();
    $('#primHareket_personelId').val($(this).data('value'));
    $('#primHareketForm input[name="tarih"]').val('{{date("Y-m-d")}}');
    $('#primHareketModal').modal('show');
  });

  $('#primHareketForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({
      url: '/isletmeyonetim/primhareketekle',
      method: 'POST',
      data: $(this).serialize(),
      headers: {'X-CSRF-TOKEN': _csrf},
      success: function(res){
        if(res.basarili){
          $('#primHareketModal').modal('hide');
          swal({title:'Kaydedildi', type:'success', timer:1200, showConfirmButton:false})
            .then(()=>location.reload())
            .catch(()=>location.reload());
        } else {
          swal({title:'Hata', text: res.mesaj || 'Kaydedilemedi', type:'error'});
        }
      },
      error: function(){
        swal({title:'Hata', text:'Sunucu hatası', type:'error'});
      }
    });
  });

  function _formatTL(v){ return parseFloat(v||0).toLocaleString('tr-TR',{minimumFractionDigits:2, maximumFractionDigits:2}); }
  function _escHtml(s){ return $('<div>').text(s||'').html(); }

  $(document).on('click','.prim-hareket-goster', function(){
    var pid = $(this).data('value');
    var adi = $(this).data('adi');
    $('#primListe_personelAdi').text(adi);
    $('#primListe_ozet').hide();
    $('#primHareketListesi').html('<div class="text-center text-muted" style="padding:30px"><i class="fa fa-spinner fa-spin fa-2x" style="color:#6366f1"></i><div style="margin-top:10px">Yükleniyor...</div></div>');
    $('#primHareketListeModal').modal('show');

    $.ajax({
      url: '/isletmeyonetim/primhareketlistesi',
      method: 'GET',
      data: { personel_id: pid, sube: _sube, tarih1: _tarih1, tarih2: _tarih2 },
      success: function(res){
        if(!res.basarili || !res.hareketler || res.hareketler.length===0){
          $('#primHareketListesi').html(
            '<div class="hareket-empty">'+
              '<div class="icon"><i class="fa fa-inbox"></i></div>'+
              '<div class="baslik">Bu dönemde kayıt yok</div>'+
              '<div class="alt">Tabloda "+" butonuyla bonus veya kesinti ekleyebilirsiniz.</div>'+
            '</div>'
          );
          return;
        }

        var toplamBonus = 0, toplamKesinti = 0;
        var html = '';
        res.hareketler.forEach(function(h){
          var isBonus = h.tip === 'bonus';
          var tutarNum = parseFloat(h.tutar||0);
          if(isBonus) toplamBonus += tutarNum; else toplamKesinti += tutarNum;
          var tutarStr = _formatTL(h.tutar);
          var tarihStr = h.tarih ? (new Date(h.tarih)).toLocaleDateString('tr-TR') : '';
          var icon = isBonus ? '<i class="fa fa-arrow-up"></i>' : '<i class="fa fa-arrow-down"></i>';
          var tipKisaltma = isBonus ? 'BONUS' : 'KESİNTİ';
          var tipBadge = isBonus ? 'prim-tip-bonus' : 'prim-tip-kesinti';
          var tutarSign = isBonus ? '+' : '−';

          html += '<div class="hareket-item tip-'+(isBonus?'bonus':'kesinti')+'">';
          html += '  <div class="hareket-icon '+(isBonus?'bonus':'kesinti')+'">'+icon+'</div>';
          html += '  <div class="hareket-info">';
          html += '    <div class="row1">';
          html += '      <span class="prim-tip-badge '+tipBadge+'">'+tipKisaltma+'</span>';
          html += '      <span class="tutar '+(isBonus?'bonus':'kesinti')+'">'+tutarSign+tutarStr+' ₺</span>';
          html += '      <span class="tarih"><i class="fa fa-calendar"></i> '+tarihStr+'</span>';
          html += '    </div>';
          if(h.aciklama){ html += '    <div class="aciklama">'+_escHtml(h.aciklama)+'</div>'; }
          html += '  </div>';
          html += '  <button class="prim-hareket-sil" data-id="'+h.id+'" title="Sil"><i class="fa fa-trash"></i></button>';
          html += '</div>';
        });

        $('#primListe_toplamBonus').text(_formatTL(toplamBonus)+' ₺');
        $('#primListe_toplamKesinti').text(_formatTL(toplamKesinti)+' ₺');
        var net = toplamBonus - toplamKesinti;
        $('#primListe_netEtki').text((net>=0?'+':'')+_formatTL(net)+' ₺').css('color', net>=0?'#10b981':'#ef4444');
        $('#primListe_ozet').css('display','grid');

        $('#primHareketListesi').html(html);
      }
    });
  });

  $(document).on('click','.prim-hareket-sil', function(){
    var id = $(this).data('id');
    swal({
      title: 'Silinsin mi?',
      text: 'Bu prim hareketi silinecek.',
      type: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sil',
      cancelButtonText: 'Vazgeç',
      confirmButtonClass: 'btn btn-danger'
    }).then(function(r){
      if(!r.value) return;
      $.ajax({
        url: '/isletmeyonetim/primhareketsil',
        method: 'POST',
        data: { id: id, sube: _sube, _token: _csrf },
        headers: {'X-CSRF-TOKEN': _csrf},
        success: function(res){
          if(res.basarili){
            location.reload();
          } else {
            swal({title:'Hata', text: res.mesaj || 'Silinemedi', type:'error'});
          }
        }
      });
    });
  });
});
</script>
@endsection
