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
  .prim-tip-badge{padding:3px 8px; border-radius:10px; font-size:11px; font-weight:600}
  .prim-tip-bonus{background:#d4edda; color:#155724}
  .prim-tip-kesinti{background:#f8d7da; color:#721c24}
  .prim-hareket-sil{cursor:pointer; color:#c82333}
  .prim-hareket-sil:hover{color:#a71d2a}
  .hareketler-listesi{max-height:260px; overflow-y:auto; border:1px solid #e2e2e2; border-radius:6px; padding:8px}
  .hareket-item{padding:6px 8px; border-bottom:1px solid #f0f0f0; display:flex; justify-content:space-between; align-items:center}
  .hareket-item:last-child{border-bottom:0}
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
<div class="modal fade" id="primHareketModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="primHareketForm">
        {!!csrf_field()!!}
        <input type="hidden" name="sube" value="{{$isletme->id}}">
        <input type="hidden" name="personel_id" id="primHareket_personelId">
        <div class="modal-header">
          <h4 class="modal-title">
            <i class="fa fa-plus-circle"></i> Prim Hareketi Ekle
            <small class="text-muted" id="primHareket_personelAdi" style="display:block; font-size:12px"></small>
          </h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Tip</label>
                <select class="form-control" name="tip" id="primHareket_tip" required>
                  <option value="bonus">Bonus (Ek Ödeme)</option>
                  <option value="kesinti">Kesinti</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Tarih</label>
                <input type="date" class="form-control" name="tarih" value="{{date('Y-m-d')}}" required>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Tutar (₺)</label>
                <input type="number" step="0.01" min="0.01" class="form-control" name="tutar" placeholder="Ör: 500" required>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Açıklama (opsiyonel)</label>
                <textarea class="form-control" name="aciklama" rows="2" maxlength="300" placeholder="Ör: Ay sonu performans bonusu / Geç gelme kesintisi"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
          <button type="submit" class="btn btn-success">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ========== Hareket Geçmişi Modal ========== --}}
<div class="modal fade" id="primHareketListeModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">
          <i class="fa fa-list"></i> Prim Hareketleri
          <small class="text-muted" id="primListe_personelAdi" style="display:block; font-size:12px"></small>
        </h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p class="text-muted">Dönem: {{date('d.m.Y', strtotime($tarih1))}} - {{date('d.m.Y', strtotime($tarih2))}}</p>
        <div class="hareketler-listesi" id="primHareketListesi">
          <div class="text-center text-muted">Yükleniyor...</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
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

  $('#primrapor_tablo').DataTable({
    pageLength: 50,
    order: [[8,'desc']],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json' }
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

  $(document).on('click','.prim-hareket-goster', function(){
    var pid = $(this).data('value');
    var adi = $(this).data('adi');
    $('#primListe_personelAdi').text(adi);
    $('#primHareketListesi').html('<div class="text-center text-muted">Yükleniyor...</div>');
    $('#primHareketListeModal').modal('show');

    $.ajax({
      url: '/isletmeyonetim/primhareketlistesi',
      method: 'GET',
      data: { personel_id: pid, sube: _sube, tarih1: _tarih1, tarih2: _tarih2 },
      success: function(res){
        if(!res.basarili || !res.hareketler || res.hareketler.length===0){
          $('#primHareketListesi').html('<div class="text-center text-muted">Bu dönemde kayıt yok.</div>');
          return;
        }
        var html = '';
        res.hareketler.forEach(function(h){
          var tipCls = h.tip === 'bonus' ? 'prim-tip-bonus' : 'prim-tip-kesinti';
          var tipYazi = h.tip === 'bonus' ? 'BONUS +' : 'KESİNTİ −';
          var tutarStr = parseFloat(h.tutar).toLocaleString('tr-TR',{minimumFractionDigits:2, maximumFractionDigits:2});
          var tarihStr = h.tarih ? (new Date(h.tarih)).toLocaleDateString('tr-TR') : '';
          html += '<div class="hareket-item">';
          html += '<div>';
          html += '<span class="prim-tip-badge '+tipCls+'">'+tipYazi+tutarStr+' ₺</span> ';
          html += '<small class="text-muted">&nbsp;'+tarihStr+'</small>';
          if(h.aciklama){ html += '<div style="font-size:12px; color:#555; margin-top:3px">'+$('<div>').text(h.aciklama).html()+'</div>'; }
          html += '</div>';
          html += '<i class="fa fa-trash prim-hareket-sil" data-id="'+h.id+'" title="Sil"></i>';
          html += '</div>';
        });
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
