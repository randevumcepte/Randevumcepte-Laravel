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
      <div class="col-md-6 col-sm-6 text-right">
         
                            
                             
           

      </div>
   </div>
</div>

<div class="pd-20 card-box mb-30">

       
               <div class="row" style="margin-bottom: 32px;">
                  <div class="col-md-6">
                   <h2>Reklam Yönetimi</h2>
                      <a href="#" data-toggle="modal" data-target="#grup_sms_liste_modal" class="btn btn-success btn-lg"><i class="fa fa-plus"></i> Gruplar</a>
                  </div>
                  <div class="col-md-6" style="text-align: right;">
                      <a href="#" data-toggle="modal" data-target="#yeni_kampanya_modal" class="btn btn-success btn-lg yenieklebuton"><i class="fa fa-plus"></i> Yeni Kampanya</a>
                           <button class="btn btn-success" onclick="modalbaslikata('Yeni Grup','grup_sms_formu')" data-toggle="modal" id='grup_olustur_buton' data-target="#grup_sms_olustur_modal"> <i class="fa fa-plus"></i>   Grup Oluştur</button>
      
                  </div>

               </div>
		         <table class="data-table table stripe hover nowrap" id="kampanyayonetim_tablo">
                  <thead>
                    <th>Görev Türü</th>
                    <th>Kampanya</th>
                    <th>Başlangıç Tarihi</th>
                    <th>Bitiş Tarihi</th>
                    <th>Arama Saati</th>
                    <th>Hizmet/Ürün</th>
                    <th>İndirim Türü</th>
                    <th>Müşteriler</th>
                    <th>Katılımcı Sayısı</th>
                    
                    
                    
             
                     <th>İşlemler</th>
                  </thead>
                  <tbody>
                    
                  </tbody>
               </table>
          
    
 
</div>
    <!--grup sms ekle -->
<div id="grup_sms_liste_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="margin-left: 15%; width: 70%;">
              <div class="modal-header bg-soft-primary">
                  
                  <div class="d-flex align-items-center w-100">
                        <div class="modal-icon mr-3 bg-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fa fa-users text-white" style="font-size: 1.2rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="modal-title mb-0 font-weight-600" id="grupSMSModalLabel">Gruplar</h5>
                            <p class="text-muted mb-0 small">Oluşturduğunuz grupları görüntüleyebilir ve düzenleyebilirsiniz</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
               </div>
               <div class="modal-body py-4">
                     <table class="data-table table stripe hover nowrap" id="grup_sms_tablo">
                     <thead>
                       <tr>
                         <th>Grup Adı </th>
                         <th>Müşteri Sayısı</th>
                         <th></th>
                       </tr>
                     </thead>
                     <tbody>
                      
                     </tbody>
                   </table>
               </div>

        </div>
    </div>
</div>


  <div id="grup_sms_olustur_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="grupSMSModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="margin-left: 15%; width: 70%;">
            <form id="grup_sms_formu" method="POST">
                {{ csrf_field() }}
                <input type="hidden" name="sube" value="{{$isletme->id}}">
                <input type="hidden" name="grup_id">
                
                <!-- Modal Header -->
                <div class="modal-header bg-soft-primary">
                    <div class="d-flex align-items-center w-100">
                        <div class="modal-icon mr-3 bg-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fa fa-users text-white" style="font-size: 1.2rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="modal-title mb-0 font-weight-600" id="grupSMSModalLabel">Yeni Grup Oluştur</h5>
                            <p class="text-muted mb-0 small">Müşteri grubu oluşturarak toplu SMS gönderebilirsiniz</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Body -->
                <div class="modal-body py-4">
                    <!-- Grup Adı ve Müşteri Arama (Yan Yana) -->
                    <div class="form-section">
                        <div class="section-header mb-3">
                            <h6 class="section-title text-primary mb-0">
                                <i class="fa fa-info-circle mr-2"></i>Grup Bilgileri ve Müşteri Seçimi
                            </h6>
                           
                        </div>
                        
                        <div class="row">
                            <!-- Grup Adı Alanı -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="grup_adi" class="form-label font-weight-600">
                                        Grup Adı <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-soft-primary border-primary">
                                                <i class="fa fa-tag"></i>
                                            </span>
                                        </div>
                                        <input type="text" 
                                               class="form-control border-primary" 
                                               id="grup_adi" 
                                               name="grup_adi"
                                               placeholder="Örn: Sadık Müşteriler, Özel Kampanya Grubu"
                                               required>
                                    </div>
                                    <small class="form-text text-muted">Grubu kolayca tanımlayabileceğiniz bir isim verin</small>
                                </div>
                            </div>
                            
                            <!-- Müşteri Arama Alanı -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="musteriarama_grupsms" class="form-label font-weight-600">
                                        Müşteri Ara
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-soft-info border-info">
                                                <i class="fa fa-search"></i>
                                            </span>
                                        </div>
                                        <input type="text" 
                                               id="musteriarama_grupsms" 
                                               name="musteriarama_grupsms" 
                                               class="form-control border-info" 
                                               placeholder="İsim ile müşteri arayın...">
                                    </div>
                                    <small class="form-text text-muted">Müşteriler arasında hızlı arama yapın</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Müşteri Listesi -->
                    <div class="form-section">
                        <div class="card border-soft">
                            <div class="card-header bg-soft-light py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 font-weight-600">Müşteri Listesi</h6>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <!-- Yükleme Göstergesi -->
                                <div class="loading text-center py-5" id="musteriYukleniyor" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Yükleniyor...</span>
                                    </div>
                                </div>
                                
                                <!-- Müşteri Listesi Container -->
                                <div id="musteriListesiGrupSMS" style="max-height: 300px; overflow-y: auto; min-height: 200px;">
                                    <!-- İlk yükleme mesajı -->
                                    <div class="text-center py-5 text-muted" id="musteriListesiIlkMesaj">
                                        <i class="fa fa-user-friends fa-3x mb-3 opacity-50"></i>
                                        <p>Lütfen bekleyin, müşteriler yükleniyor...</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Seçili Müşteri Bilgisi -->
                            <div class="card-footer bg-soft-success py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa fa-check-circle text-success mr-2"></i>
                                        <span class="font-weight-600" id="grupSMSSeciliMusteriler">0 müşteri seçildi</span>
                                    </div>
                                    <div class="text-muted small">
                                        <span id="gosterilenMusteriSayisi">0</span> / <span id="toplamMusteriSayisiFooter">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="modal-footer bg-soft-light border-top">
                    <div class="row w-100 align-items-center">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fa fa-save mr-2"></i>Grubu Kaydet
                            </button>
                        </div>
                        <div class="col-md-2"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Özel Stiller */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.modal-header.bg-soft-primary {
    background-color: rgba(94, 114, 228, 0.1);
    border-bottom: 1px solid rgba(94, 114, 228, 0.2);
    border-radius: 12px 12px 0 0;
    padding: 1.25rem 1.5rem;
}

.modal-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-soft-primary {
    background-color: rgba(94, 114, 228, 0.1) !important;
}

.bg-soft-info {
    background-color: rgba(23, 162, 184, 0.1) !important;
}

.bg-soft-success {
    background-color: rgba(40, 167, 69, 0.1) !important;
}

.bg-soft-light {
    background-color: rgba(248, 249, 250, 0.8) !important;
}

.border-primary {
    border-color: #5e72e4 !important;
}

.border-info {
    border-color: #17a2b8 !important;
}

.border-soft {
    border-color: #e9ecef !important;
}

.form-section {
    margin-bottom: 0rem;
}

.section-header {
    position: relative;
}

.section-divider {
    height: 2px;
    background: linear-gradient(90deg, #5e72e4, transparent);
    margin-top: 5px;
}

.form-label {
    font-size: 0.875rem;
    color: #525f7f;
    margin-bottom: 0.5rem;
}

.input-group-text {
    transition: all 0.3s ease;
}

.input-group:focus-within .input-group-text {
    background-color: #5e72e4;
    color: white;
}

#musteriListesiGrupSMS {
    scrollbar-width: thin;
    scrollbar-color: #c1c9d4 transparent;
}

#musteriListesiGrupSMS::-webkit-scrollbar {
    width: 6px;
}

#musteriListesiGrupSMS::-webkit-scrollbar-track {
    background: #f8f9fa;
}

#musteriListesiGrupSMS::-webkit-scrollbar-thumb {
    background-color: #c1c9d4;
    border-radius: 3px;
}

/* Müşteri kartı stilleri */
.musteri-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f1f1f1;
    transition: all 0.2s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.musteri-item:hover {
    background-color: rgba(94, 114, 228, 0.05);
}

.musteri-item.selected {
    background-color: rgba(40, 167, 69, 0.1);
    border-left: 3px solid #28a745;
}

.musteri-info {
    flex: 1;
}

.musteri-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #5e72e4;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-weight: 600;
}

.musteri-ad {
    font-weight: 600;
    color: #32325d;
    margin-bottom: 2px;
}

.musteri-detay {
    font-size: 0.85rem;
    color: #6c757d;
}

.checkbox-wrapper {
    width: 20px;
    height: 20px;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 8px;
}

/* Responsive ayarlamalar */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 10px;
    }
    
    .modal-content {
        margin-left: 0 !important;
        width: 100% !important;
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .btn-lg {
        padding: 0.6rem 1rem;
        font-size: 0.95rem;
    }
    
    /* Mobilde grup adı ve arama alanları alt alta */
    .row .col-md-6 {
        width: 100%;
        margin-bottom: 1rem;
    }
}

@media (max-width: 576px) {
    .modal-header .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .modal-icon {
        margin-bottom: 10px;
        margin-right: 0;
    }
    
    .modal-footer .row {
        flex-direction: column;
    }
    
    .modal-footer .col-md-2,
    .modal-footer .col-md-8 {
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>

@endsection