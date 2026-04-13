@extends('layout.layout_satisortakligi')
@section('content')
 
<div class="header pb-6 d-flex align-items-center" style="min-height: 274px;">
   <span class="mask bg-gradient-default opacity-8"></span>
   <!-- Header container -->
   <div class="container-fluid">
      <div class="header-body">
         <div class="row align-items-center" style="padding-bottom:1.5rem;padding-top:1rem">
            <div class="col-lg-9 col-md-9 d-none d-md-inline-block">
               <h6 class="h2 text-white d-inline-block mb-0 ">Anasayfa</h6>
               <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                  <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                     <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i></a></li>
                     <li class="breadcrumb-item"><a href="#">Ortaklık Paneli</a></li>
                     <li class="breadcrumb-item active" aria-current="page">Anasayfa</li>
                  </ol>
               </nav>
            </div>
            <div class="mobilgorunum col-md-3 " >
           
               <button type="button" class="btn btn-secondary" style="font-size:16px">Hakediş Tutarı : <?php echo number_format(($hakedis['toplam']),2,',','.');?> ₺</button>
            </div>
         </div>
         <div class="row">
            <div class="col-xl-3 col-md-6">
               <div class="card card-stats">
                  <!-- Card body -->
                  <div class="card-body">
                     <div class="row">
                        <div class="col-md-9 col-xs-9 col-9">
                           <h5 class="card-title text-uppercase text-muted mb-0">Komisyon</h5>
                           <div class="row" style="padding-top:5px;">
                           <span style="padding-left: 15px;" class="h3 font-weight-bold mb-0 ">40% </span>
                           
                           <span style="padding-left: 25px;" class="h3 font-weight-bold mb-0 ">15% </span>
                           
                           <span style="padding-left: 30px;"class="h3 font-weight-bold mb-0 ">20% </span> 
                           </div>
                           <div class="row" ->
                           <a style="font-size:11px;padding-left: 15px;"  data-toggle="modal" href="#modal_ilksatis">İlk satış</a>
                           
                           <a style="font-size:11px;padding-left: 23px;" data-toggle="modal" href="#modal_yenileme" href="#">Yenileme</a>
                        
                           <a style="font-size:11px;padding-left: 15px;" data-toggle="modal" href="#modal_desteksatis" href="#">Destek satış</a>
                           </div>
                          
                        </div>
                        <div class="col-md-3 col-xs-3 col-3">
                           <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                              <i class="ni ni-active-40"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-xl-3 col-md-6">
               <div class="card card-stats">
                  <!-- Card body -->
                  <div class="card-body">
                     <div class="row">
                        <div class="col-md-9 col-xs-9 col-9">
                           <h5 class="card-title text-uppercase text-muted mb-0">Satışlar</h5>
                           <span class="h2 font-weight-bold mb-0" ><span id="yillik_satis_kdvsiz">{{number_format(($hakedis['kdvsiztoplam']),2,',','.')}}<span> ₺</span>
                         <div class="row">  <span style="padding-left: 15px;" class="h6 font-weight-bold mb-0 ">Son 12 ay için.</span> </div>
                        </div>
                        <div class="col-md-3 col-xs-3 col-3">
                           <div class="icon icon-shape bg-gradient-orange text-white rounded-circle shadow">
                              <i class="ni ni-chart-pie-35"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-xl-3 col-md-6">
               <div class="card card-stats">
                  <!-- Card body -->
                  <div class="card-body">
                     <div class="row">
                        <div class="col">
                           <h5 class="card-title text-uppercase text-muted mb-0">Hakediş Tutarı</h5>
                           <span class="h2 font-weight-bold mb-0"><span id="hakedis_yillik">{{number_format(($hakedis['toplam']+$hakedis['talepEdilmisToplam']),2,',','.')}}</span> ₺
                          <div class="row"> <a style="font-size:11px; padding-left:15px" href="/satisortakligi/odeme-talepleri">Ödeme talep et</a></div>
                        </div>
                        <div class="col-md-3 col-xs-3 col-3">
                           <div class="icon icon-shape bg-gradient-green text-white rounded-circle shadow">
                              <i class="ni ni-money-coins"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-xl-3 col-md-6">
               <div class="card card-stats">
                  <!-- Card body -->
                  <div class="card-body">
                     <div class="row">
                        <div class="col-md-9 col-xs-9 col-9">
                           <h5 class="card-title text-uppercase text-muted mb-0">Müşteri Sayısı</h5>
                           
                           <span class="h2 font-weight-bold mb-0 ">{{$musteriler->count() }}</span>
                           <div class="row"> <h6 class="card-title text-uppercase text-muted mb-0 text-white">Müşteriler</h6></div>
                        </div>
                       
                        <div class="col-md-3 col-xs-3 col-3">
                           <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                              <i class="ni ni-chart-bar-32"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- Page content -->
<div class="container-fluid mt--6">
</div>
<div class="col-xl-12 order-xl-3">
   <div class="nav-wrapper col-xl-6 order-xl-3">
      <ul class="nav nav-pills nav-fill flex-md-row" id="tabs-icons-text" role="tablist">
         <li class="nav-item butongorunum">
            <a class="nav-link mb-sm-3 mb-md-0 active" id="tabs-icons-text-1-tab" data-toggle="tab" href="#tabs-icons-text-1" role="tab" aria-controls="tabs-icons-text-1" aria-selected="true">Üyelik Süresi</a>
         </li>
         <li class="nav-item butongorunum">
            <a class="nav-link mb-sm-3 mb-md-0" id="tabs-icons-text-2-tab" data-toggle="tab" href="#tabs-icons-text-2" role="tab" aria-controls="tabs-icons-text-2" aria-selected="false">Demo Süresi</a>
         </li>
      </ul>
   </div>
   <div class="card shadow">
      <!-- Card header -->
      <div class="card-body">
         <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="tabs-icons-text-1" role="tabpanel" aria-labelledby="tabs-icons-text-1-tab">
               <div class="table-responsive">
                  <table class="table align-items-center table-flush" id="aktif_musteriler">
                    <thead class="thead-light">
                      <tr>
                        <th>İşletme ID</th>
                        <th>İşletme Adı</th>
                        <th>İşletme Yetkilisi </th>
                          <th>Telefon</th>
                        <th>Üyelik Türü / Satılan Paket</th>
                        <th>Üyelik Süresi</th>
                        <th>İşlemler</th>
                         
                      </tr>
                    </thead>
                    <tbody class="list">
                      
                   
                   
                    </tbody>
             
                   
                     
                  </table>
               </div>
            </div>
            <div class="tab-pane fade" id="tabs-icons-text-2" role="tabpanel" aria-labelledby="tabs-icons-text-2-tab">
               <div class="table-responsive">
                  <table class="table align-items-center table-flush" id="demomusterileri">
                     <thead class="thead-light">
                        <tr>
                           <th>İşletme ID</th>
                           <th>İşletme Adı</th>
                           <th>İşletme Yetkilisi </th>
                          <th>Telefon</th>
                           <th>Demo Süresi</th>
                           <th>Notlar</th>
                           <th>İşlemler</th>
                        </tr>
                     </thead>
                     <tbody class="list">
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="modal-default" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true" style="display: none;">
      <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h6 class="modal-title" id="modal-title-default">ÖDEME TALEBİ</h6>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">×</span>
               </button>
            </div>
            <form id="odeme_talep_et_formu" enctype='multipart/form-data'  method="POST">
               @csrf
               <div class="modal-body">
                  <div class="card card-pricing bg-success border-0 text-center mb-4" style="padding:0">
                     <div class="card-header bg-transparent" style="padding: 0">
                        <h4 class="text-uppercase ls-1 text-white py-3 mb-0" style="padding: 0">GÜNCEL HAKEDİŞ TUTARINIZ</h4>
                     </div>
                     <div class="card-body px-lg-7" style="padding: 0">
                        <input type="hidden" name="hakedis_miktari" id="hakedis_miktari">
                        <div class="display-2 text-white" id="hakedis_miktari_text"></div>
                     </div>
                  </div>
                  <div class="alert alert-info fade show" role="alert">
                     <span class="alert-icon"><i class="fa fa-info"></i></span>
                     <span class="alert-text"><strong>Bilgi Notu!</strong> Hakedişinizi hesabınıza transfer edebilmemiz için lütfen komisyon faturanızı veya gider pusulanızı ekleyiniz.</span>
                  </div>
                  <div class="form-group">
                     <label>Belge ekleyin</label>
                     <input type="file" id="komisyon_fatura_gider_pusulasi" required name="komisyon_fatura_gider_pusulasi" class="form-control">
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="submit" class="btn btn-primary"><i class="ni ni-send"></i>Ödeme Talebi Gönder</button>
                  <button type="button" class="btn btn-danger  ml-auto" data-dismiss="modal"><i class="fa fa-times-circle"></i>Kapat</button>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>
<div class="modal fade" id="modal_ilksatis" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
   <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
      <div class="modal-content">
      <div class="modal-header">
                <h6 class="modal-title" id="modal-title-default">Müşteriye İlk Satışta Alınacak Komisyon Oranı</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            
            <div class="modal-body">
            	
                <p>Kendi müşterinize oluşturduğunuz demo süreci boyunca veya başka bir satış ortağının oluşturduğu müşterinin demo süreci sonrasında yapılan satışlarda, tarafınıza uygulanacak komisyon oranı %40 olarak belirlenmiştir.</p>
               
                
            </div>
            
      
      </div>
   </div>
</div>
<div class="modal fade" id="modal_yenileme" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
   <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
      <div class="modal-content">
      <div class="modal-header">
                <h6 class="modal-title" id="modal-title-default">Yıllık Yenileme Komisyon Oranı</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            
            <div class="modal-body">
            	
                <p>Daha önce müşterinize sattığınız paketin yıllık yenileme işlemlerinde, tarafınıza uygulanacak komisyon oranı %15 olarak belirlenmiştir.</p>
              
                
            </div>
      
      </div>
   </div>
</div>
<div class="modal fade" id="modal_desteksatis" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
   <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
      <div class="modal-content">
      <div class="modal-header">
                <h6 class="modal-title" id="modal-title-default">Destek Satış Komisyon Oranı</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            
            <div class="modal-body">
            	
                <p>Demosunu oluşturduğunuz müşterinizin, demo sürecini takip eden 7 gün içinde başka bir satış ortağı tarafından yapılan satışlardan tarafınıza uygulanacak komisyon oranı %20 olarak belirlenmiştir.</p>
             
                
            </div>
            
         
      </div>
   </div>
</div>
</div>

<div id="hata"></div>
@endsection