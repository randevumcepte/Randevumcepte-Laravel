@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="page-header">
   <div class="row">
      <div class="col-md-6 col-sm-6">
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
     <div class="col-md-6 col-sm-12 text-right">
        
          <div class="row">
            <div class="col-md-4">
                <a onclick="modalbaslikata('Yeni Masraf','musteri_bilgi_formu');" href="#" data-toggle="modal" data-target="#yeni_masraf_modal" class="btn btn-danger btn-lg btn-block yenieklebuton331"><i class="fa fa-plus"></i> Yeni Masraf </a>
           
            </div>
            <div class="col-md-4">
                <a href="#" data-toggle="modal" data-target="#kasaya_para_koy" class="btn btn-primary btn-lg btn-block yenieklebuton332">  Para Ekle </a>
           
            </div>
            <div class="col-md-4">
                <a  href="#" data-toggle="modal" data-target="#kasadan_para_al" class="btn btn-success btn-lg btn-block yenieklebuton333"> Para Çek </a>
           
            </div>
          </div>
         
      </div>
   </div>
</div>
<div class="pd-20 card-box mb-30">
   <div class="pb-20" style="padding-top:20px">
        <div class="form-group row">
          
         <div class="col-md-3 col-sm-6 col-xs-6 col-6">
            <label>Ödeme Yöntemi</label>
            <select class="form-control" id="odeme_yontemine_gore_filtre">
               <option value="">Hepsi</option>
               <option value="1">Nakit</option>
               <option value="2">Kredi Kartı</option>
               <option value="3">Havale / EFT</option>
               <option value="4">Online Ödeme</option>
               <option value="5">Senet</option>
            </select>
         </div> 
         <div class="col-md-3 col-sm-6 col-xs-6 col-6">
             <label>Banka</label>
              <select class="form-control" id='bankaya_gore_filtre'>
                            <option value=''>Hepsi</option>
                           @foreach(\App\SatisOrtakligiModel\Bankalar::all() as $banka)
                           <option value="{{$banka->id}}">{{$banka->banka}}</option>
                           @endforeach
                        </select>
         </div>
         
         <div class="col-md-3 col-sm-6 col-xs-6 col-6">
            <label>Zaman</label>
            <select class="form-control" id="zamana_gore_filtre_kasa" >
               <option  value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
               <option value="{{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}}">Dün</option>
               <option selected value="<?php  echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
               <option value="<?php  echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
               <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
               
               <option value="ozel">Özel</option>
            </select>
         </div>
          <div class="col-md-3 col-sm-6 col-xs-6 col-6">
               <label style="color:white"> buton</label>
        <button class="btn  btn-lg form-control" style="color:white;background-color:#ffa500;" id="aylik_kasa_ozeti_buton">
      
            <i class="fa fa-calendar"></i> Devreden Aylar
        </button>
   
</div>
         
         <div class="col-sm-3 col-sm-6 col-xs-6 col-6" id='kasa_baslangic' style="display:none">
            <label>Başlangıç tarihi</label>
            <input
               class="form-control"
             
               type="text" id="kasa_baslangic_tarihi" autocomplete='off'
               />
         </div>
         <div class="col-sm-3 col-sm-6 col-xs-6 col-6" id='kasa_bitis' style="display:none">
            <label>Bitiş tarihi</label>
            <input
               class="form-control"
             
               type="text" id="kasa_bitis_tarihi" autocomplete='off'
               />
         </div>
        
   
      </div>
      <div class="row">

                <div class="col-lg-3 col-md-3 col-sm-6 col-6 col-xs-6 mb-20">
                  <div class="card-box height-100-p widget-style3">
                     <div class="d-flex flex-wrap">
                        <div class="widget-data">
                           <div class="weight-700 font-24 text-dark" ><span id='kasa_gelir_tutari'>{{$kasa['gelir']}}</span> ₺</div>
                           <div class="font-14 text-secondary weight-500">Gelir</div>
                        </div>
                        <div class="widget-icon" style="background-color: rgb(70, 203, 218);">
                           <div class="icon" data-color="#fff">
                              <i class="icon-copy fa fa-chevron-up" aria-hidden="true"></i>
                           </div>
                        </div>
                     </div>
                  </div>
                </div>
                <div class="col-lg-3  col-md-3 col-sm-6 col-6 col-xs-6 mb-20">
                  <div class="card-box height-100-p widget-style3">
                     <div class="d-flex flex-wrap">
                        <div class="widget-data">
                           <div class="weight-700 font-24 text-dark"><span id='kasa_gider_tutari'>{{$kasa['gider']}}</span> ₺</div>
                           <div class="font-14 text-secondary weight-500">Gider</div>
                        </div>
                        <div class="widget-icon" style="background-color:rgb(146, 0, 188)">
                           <div class="icon" data-color="#fff">
                              <i class="icon-copy fa fa-chevron-down" aria-hidden="true"></i>
                           </div>
                        </div>
                     </div>
                  </div>
                </div>
                {{-- Mevcut kasa toplam kartı --}}
<!-- Mevcut kasa toplam kartı -->
<div class="col-lg-3 col-md-3 col-sm-6 col-6 col-xs-6 mb-20">
    <div class="card-box height-100-p widget-style3">
        <div class="d-flex flex-wrap">
            <div class="widget-data">
                <div class="weight-700 font-24 text-dark"><span id='kasa_toplam_tutar'>{{$kasa['toplam']}}</span> ₺</div>
                <div class="font-14 text-secondary weight-500">
                    <span id="kasa_period_label">Dönem Net Karı</span>
                    <small id="kasa_date_range" style="display: none; font-size: 10px;"></small>
                </div>
            </div>
            <div class="widget-icon" style="background-color: rgb(0, 109, 190) ">
                <div class="icon" data-color="#fff">
                    <i class="icon-copy fa fa-money" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toplam Ciro Kartı -->
<div class="col-lg-3 col-md-3 col-sm-6 col-6 col-xs-6 mb-20">
    <div class="card-box height-100-p widget-style3">
        <div class="d-flex flex-wrap">
            <div class="widget-data">
                <div class="weight-700 font-24 text-dark"><span id='toplam_ciro_tutari'>{{$kasa['toplam_ciro']}}</span> ₺</div>
                <div class="font-14 text-secondary weight-500">
                    Toplam Kazanç
                   
                </div>
            </div>
            <div class="widget-icon" style="background-color: rgb(34, 139, 34) ">
                <div class="icon" data-color="#fff">
                    <i class="icon-copy fa fa-line-chart" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>
</div>
               <div class="col-lg-6 col-md-12 mb-20">
                  <div class="card-box widget-style3">
                    <div class="card-header">
                      <h2>Gelirler</h2>
                      
                    </div>
                    <div class="card-body">
                      <table class="table">
                        <thead>
                          <th>Tarih</th>
                          <th>Müşteri</th>
                          <th>Tahsil Eden</th>
                          <th>Notlar</th>
                          <th>Ödeme Yöntemi & Banka</th>
                          <th>Tutar (₺)</th>
                        </thead>
                        <tbody  id='tahsilatlar_listesi'>
                          {!! $kasa['tahsilatlar'] !!}
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6 col-md-12 mb-20">
                  <div class="card-box widget-style3">
                    <div class="card-header">
                      <h2>Giderler</h2>
                    </div>
                    <div class="card-body">
                      <table class="table"  >
                        <thead>
                          <th>Tarih</th>
                          <th>Harcayan</th>
                          <th>Açıklama</th>
                          <th>Ödeme Yöntemi</th>
                          <th>Tutar (₺)</th>
                          <th></th>
                        </thead>
                        <tbody id='masraflar_listesi' >
                           {!! $kasa['masraflar'] !!}
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
      </div>
   </div>
</div>
  <!-- yeni masraf -->
      <div
         id="kasaya_para_koy"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;">
               <form id="kasaya_para_koy_form"  method="POST">
                  <div class="modal-header">
                     <h2 >Kasaya Para Ekle</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     <input type="hidden" name="paraekle_id" id='paraekle_id' value="">
                    
                     <div class="row" data-value="0">
                        <div class="col-md-6">
                           <label>Tarih</label>
                           <input type="text" required class="form-control date-picker" name="parakoyma_tarihi" id='parakoyma_tarihi' value="{{date('Y-m-d')}}" autocomplete="off">
                        </div>
                        <div class="col-md-6">
                           <label>Tutar (₺)</label>
                           <input type="tel" name="para_tutari" id='para_tutari' required class="form-control try-currency">
                        </div>
                        <div class="col-md-12">
                           <label>Açıklama</label>
                           <textarea name="para_aciklama" id='para_aciklama' class="form-control"></textarea>
                        </div>
                     </div>
              
                     <div class="row" data-value="0">
                        <div class="col-md-6">
                           <label>Ödeme Yöntemi</label>
                           <select name="para_odeme_yontemi" id='para_odeme_yontemi' class="form-control custom-select2" style="width: 100%;">
                              @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                              <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Para Ekleyen</label>
                           <select name="paraekleyen" id='paraekleyen' class="form-control custom-select2 personel_secimi" style="width: 100%;">
                              <option></option>
                           </select>
                        </div>
                     </div>
                   
                     <div class="modal-footer" style="display:block">
                        <div class="row" data-value="0">
                           <div class="col-md-6  col-sm-6 col-xs-6 col-6">
                              <button type="submit" {{Auth::guard('satisortakligi')->check() ? 'disabled' : ''}} class="btn btn-success btn-lg btn-block"> <i class="fa fa-save"></i>
                              Kaydet </button>
                           </div>
                           <div class="col-md-6  col-sm-6 col-xs-6 col-6">
                              <button  
                                 type="button"
                                 class="btn btn-danger btn-lg btn-block"
                                 data-dismiss="modal"
                                 > <i class="fa fa-times"></i>
                              Kapat
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
          <div
         id="kasadan_para_al"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;">
               <form id="kasadan_para_al_form"  method="POST">
                  <div class="modal-header">
                     <h2 >Kasadan Para Çek</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     <input type="hidden" name="paraalid" id='paraalid' value="">
                    
                     <div class="row" data-value="0">
                        <div class="col-md-6">
                           <label>Tarih</label>
                           <input type="text" required class="form-control date-picker" name="paraalma_tarihi" id='paraalma_tarihi' value="{{date('Y-m-d')}}" autocomplete="off">
                        </div>
                        <div class="col-md-6">
                           <label>Tutar (₺)</label>
                           <input type="tel" name="paraalma_tutari" id='paraalma_tutari' required class="form-control try-currency">
                        </div>
                        <div class="col-md-6">
                           <label>Açıklama</label>
                           <textarea name="paraalma_aciklama" id='paraalma_aciklama' class="form-control"></textarea>
                        </div>
                          <div class="col-md-3">
                           <label>Onay Kodu</label>
                           <input type="tel" name="onaykoduparacekme" id='onaykoduparacekme' required class="form-control">
                        </div>
                        <div class="col-md-3">
                           <label style="color:white;">Onay Kodu</label>
                           <button {{Auth::guard('satisortakligi')->check() ? 'disabled' : ''}} class="btn-block btn btn-lg btn-primary" type="button" id="paracekmeonaykodu" name="paracekmeonaykodu">Kod Gönder</button>
                        </div>
                     </div>
              
                     <div class="row" data-value="0">
                        <div class="col-md-6">
                           <label>Ödeme Yöntemi</label>
                           <select name="paraalma_odeme_yontemi" id='paraalma_odeme_yontemi' class="form-control custom-select2" style="width: 100%;">
                              @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                              <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Para Çeken</label>
                           <select name="paraalan" id='paraalan' class="form-control custom-select2 personel_secimi" style="width: 100%;">
                              <option></option>
                           </select>
                        </div>
                     </div>
                   
                     <div class="modal-footer" style="display:block">
                        <div class="row" data-value="0">
                           <div class="col-md-6  col-sm-6 col-xs-6 col-6">
                              <button type="submit" {{Auth::guard('satisortakligi')->check() ? 'disabled' : ''}} class="btn btn-success btn-lg btn-block"> <i class="fa fa-save"></i>
                              Kaydet </button>
                           </div>
                           <div class="col-md-6  col-sm-6 col-xs-6 col-6">
                              <button  
                                 type="button"
                                 class="btn btn-danger btn-lg btn-block"
                                 data-dismiss="modal"
                                 > <i class="fa fa-times"></i>
                              Kapat
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
      <!-- Devreden Aylar Modal -->
<div id="devreden_aylar_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Devreden Aylar</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Yıl Seçin</label>
                        <select id="devreden_aylar_yil" class="form-control">
                            @php
                                $currentYear = date('Y');
                                $startYear = 2015; // İşletmenizin başlangıç yılı
                            @endphp
                            @for($year = $currentYear; $year >= $startYear; $year--)
                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                  
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ay</th>
                                <th>Toplam Kasa (₺)</th>
                                 <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody id="devreden_aylar_listesi">
                            <!-- AJAX ile dolacak -->
                        </tbody>
                       
                    </table>
                </div>
            </div>
           
        </div>
    </div>
</div>
      <script>
         // Devreden Aylar butonuna tıklama
$('#aylik_kasa_ozeti_buton').click(function() {
    $('#devreden_aylar_modal').modal('show');
    getDevredenAylar($('#devreden_aylar_yil').val());
});

// Yıl değiştiğinde
$('#devreden_aylar_yil').change(function() {
    getDevredenAylar($(this).val());
});

// Yenile butonu
$('#devreden_aylar_getir').click(function() {
    getDevredenAylar($('#devreden_aylar_yil').val());
});

// Devreden ayları getiren fonksiyon
function getDevredenAylar(yil) {
    $('#devreden_aylar_listesi').html('<tr><td colspan="3" class="text-center"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</td></tr>');
    
    $.ajax({
        url: '/isletmeyonetim/devreden-aylar', // Route tanımlamanız gerekecek
        type: 'GET',
        data: {
            yil: yil,
            sube: '{{ $isletme->id }}',
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                renderDevredenAylar(response.data);
            } else {
                $('#devreden_aylar_listesi').html('<tr><td colspan="3" class="text-center text-danger">' + response.message + '</td></tr>');
            }
        },
        error: function() {
            $('#devreden_aylar_listesi').html('<tr><td colspan="3" class="text-center text-danger">Bir hata oluştu!</td></tr>');
        }
    });
}

// Listeyi render eden fonksiyon
function renderDevredenAylar(data) {
    var html = '';
    var yilToplam = 0;
    
    if (data.length === 0) {
        html = '<tr><td colspan="3" class="text-center">Kayıt bulunamadı</td></tr>';
    } else {
        data.forEach(function(ay) {
            yilToplam += parseFloat(ay.donem_net_kar);
            
            var durumClass = ay.donem_net_kar > 0 ? 'text-success' : (ay.donem_net_kar < 0 ? 'text-danger' : 'text-secondary');
            var durumIcon = ay.donem_net_kar > 0 ? 'fa-chevron-up' : (ay.donem_net_kar < 0 ? 'fa-chevron-down' : 'fa-minus');
            
            html += '<tr>' +
                    '<td><strong>' + ay.ay_adi + ' ' + ay.yil + '</strong></td>' +
                    '<td class="' + durumClass + '">' +
                    '<i class="fa ' + durumIcon + '"></i> ' +
                    formatCurrency(ay.donem_net_kar) + ' ₺' +
                    '</td>' +
                    '<td>' +
                    '<span class="badge ' + (ay.donem_net_kar > 0 ? 'badge-success' : (ay.donem_net_kar < 0 ? 'badge-danger' : 'badge-secondary')) + '">' +
                    (ay.donem_net_kar > 0 ? 'KAR' : (ay.donem_net_kar < 0 ? 'ZARAR' : 'DENGELİ')) +
                    '</span>' +
                    '</td>' +
                    '</tr>';
        });
    }
    
    $('#devreden_aylar_listesi').html(html);
    $('#devreden_aylar_yil_toplam').html(formatCurrency(yilToplam) + ' ₺');
}

// Currency format fonksiyonu
function formatCurrency(value) {
    return parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}
$(document).ready(function() {
    // Filtre değiştiğinde çalışacak fonksiyon
    function updateKasaPeriodLabel() {
        var zamanFiltresi = $('#zamana_gore_filtre_kasa').val();
        var label = $('#kasa_period_label');
        var dateRange = $('#kasa_date_range');
        
        // Özel tarih seçimi durumu
        if (zamanFiltresi === 'ozel') {
            var baslangic = $('#kasa_baslangic_tarihi').val();
            var bitis = $('#kasa_bitis_tarihi').val();
            
            if (baslangic && bitis) {
                // Tarih formatını düzenle
                var baslangicFormatted = formatDate(baslangic);
                var bitisFormatted = formatDate(bitis);
                
                label.text('Toplam Kasa');
                dateRange.text(baslangicFormatted + ' - ' + bitisFormatted).show();
            } else {
                label.text('Toplam Kasa (Özel)');
                dateRange.hide();
            }
        } 
        // Bugün seçildiyse
        else if (zamanFiltresi.includes('/') && zamanFiltresi.split(' / ')[0] === zamanFiltresi.split(' / ')[1]) {
            var tarih = zamanFiltresi.split(' / ')[0];
            var tarihFormatted = formatDate(tarih);
            
            if (tarih === '{{date("Y-m-d")}}') {
                label.text('Günlük Toplam Kasa');
                dateRange.text('').show();
            } else {
                label.text('Günlük Toplam Kasa');
                dateRange.text('(' + tarihFormatted + ')').show();
            }
        }
        // Dün seçildiyse
        else if (zamanFiltresi.includes('/') && zamanFiltresi.split(' / ')[0] === zamanFiltresi.split(' / ')[1]) {
            var tarih = zamanFiltresi.split(' / ')[0];
            var tarihFormatted = formatDate(tarih);
            
            label.text('Günlük Toplam Kasa');
            dateRange.text('(' + tarihFormatted + ')').show();
        }
        // Bu ay seçildiyse
        else if (zamanFiltresi === '<?php echo date("Y-m-01") . " / " . date("Y-m-t"); ?>') {
            label.text('Aylık Toplam Kasa');
            dateRange.text('').show();
        }
        // Geçen ay seçildiyse
        else if (zamanFiltresi === '<?php echo date("Y-m-01",strtotime("-1 months")) . " / " . date("Y-m-t",strtotime("-1 months")); ?>') {
            label.text('Aylık Toplam Kasa');
            dateRange.text('').show();
        }
        // Bu yıl seçildiyse
        else if (zamanFiltresi === '<?php echo date("Y-01-01") . " / " . date("Y-12-31"); ?>') {
            label.text('Yıllık Toplam Kasa');
            dateRange.text('').show();
        }
        // Diğer durumlar
        else {
            label.text('Toplam Kasa');
            dateRange.hide();
        }
    }
    
    // Tarih formatlama fonksiyonu
    function formatDate(dateString) {
        var date = new Date(dateString);
        var day = date.getDate().toString().padStart(2, '0');
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var year = date.getFullYear();
        return day + '.' + month + '.' + year;
    }
    
    // Sayfa yüklendiğinde etiketi güncelle
    updateKasaPeriodLabel();
    
    // Zaman filtresi değiştiğinde
    $('#zamana_gore_filtre_kasa').change(function() {
        updateKasaPeriodLabel();
        
        // Özel tarih seçimi göster/gizle
        if ($(this).val() === 'ozel') {
            $('#kasa_baslangic').show();
            $('#kasa_bitis').show();
        } else {
            $('#kasa_baslangic').hide();
            $('#kasa_bitis').hide();
        }
    });
    
    // Özel tarih değiştiğinde
    $('#kasa_baslangic_tarihi, #kasa_bitis_tarihi').change(function() {
        updateKasaPeriodLabel();
    });
    
    // AJAX ile filtreleme yapıldığında da etiketi güncelle
    $(document).on('kasaFiltreUygulandi', function(event, data) {
        // AJAX başarılı olduktan sonra etiketi güncelle
        setTimeout(function() {
            updateKasaPeriodLabel();
        }, 500);
    });
});
</script>
@endsection
