<div id="kampanya_detay_modal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
         
         <!-- Modal Header -->
         <div class="modal-header border-bottom-0">
            <div class="d-flex align-items-center justify-content-between w-100">
               <div>
                  <h2 class="modal-title h4 font-weight-600 text-gray-800 mb-0">Reklam Raporu</h2>
                  <p class="text-muted small mb-0 mt-1">Kampanya detayları ve katılımcı analizi</p>
               </div>
               <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
         </div>

         <!-- Modal Body -->
         <div class="modal-body p-0">
            
            <!-- Özet Kartları -->
            <div class="dashboard-cards p-4 border-bottom">
               <div class="row">
                  <div class="col-xl-3 col-lg-6 mb-3">
                     <div class="card summary-card">
                        <div class="card-body p-3">
                           <div class="d-flex align-items-center">
                              <div class="icon-circle bg-blue-soft">
                                 <i class="fa fa-tasks text-blue"></i>
                              </div>
                              <div class="ml-3">
                                 <h6 class="card-subtitle text-muted small mb-1">Görev</h6>
                                 <p class="card-title h5 font-weight-600 mb-0 text-gray-800" id="paket_adi">-</p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <div class="col-xl-3 col-lg-6 mb-3">
                     <div class="card summary-card">
                        <div class="card-body p-3">
                           <div class="d-flex align-items-center">
                              <div class="icon-circle bg-green-soft">
                                 <i class="fa fa-bullhorn text-green"></i>
                              </div>
                              <div class="ml-3">
                                 <h6 class="card-subtitle text-muted small mb-1">Kampanya</h6>
                                 <p class="card-title h5 font-weight-600 mb-0 text-gray-800" id="kampanya_seans">-</p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <div class="col-xl-3 col-lg-6 mb-3">
                     <div class="card summary-card">
                        <div class="card-body p-3">
                           <div class="d-flex align-items-center">
                              <div class="icon-circle bg-orange-soft">
                                 <i class="fa fa-users text-orange"></i>
                              </div>
                              <div class="ml-3">
                                 <h6 class="card-subtitle text-muted small mb-1">Katılımcı Sayısı</h6>
                                 <p class="card-title h5 font-weight-600 mb-0 text-gray-800" id="kampanya_katilimci">-</p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <div class="col-xl-3 col-lg-6 mb-3">
                     <div class="card summary-card">
                        <div class="card-body p-3">
                           <div class="d-flex align-items-center">
                              <div class="icon-circle bg-purple-soft">
                                 <i class="fa fa-cube text-purple"></i>
                              </div>
                              <div class="ml-3">
                                 <h6 class="card-subtitle text-muted small mb-1">Hizmet/Ürün</h6>
                                 <p class="card-title h5 font-weight-600 mb-0 text-gray-800" id="kampanya_hizmeti">-</p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-12">
                     
                      <!-- Arama Alt Tab Menüsü -->
                        <div class="subtabs-container mb-4">
                           <div class="d-flex justify-content-between align-items-center">
                              <ul class="nav nav-tabs subtabs-nav" role="tablist">
                                 <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#tum_kampanya_arama" role="tab">
                                       <span class="badge badge-light badge-sm ml-1" id="tum_arama_count">0</span> Tümü
                                    </a>
                                 </li>
                                 <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#kampanya_katilanlar_arama" role="tab">
                                       <span class="badge badge-light badge-sm ml-1" id="katilan_arama_count">0</span> Katılanlar
                                    </a>
                                 </li>
                                 <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#kampanya_katilmayanlar_arama" role="tab">
                                       <span class="badge badge-light badge-sm ml-1" id="katilmayan_arama_count">0</span> Katılmayanlar
                                    </a>
                                 </li>
                                 <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#kampanya_beklenen_arama" role="tab">
                                       <span class="badge badge-light badge-sm ml-1" id="beklenen_arama_count">0</span> Beklenenler
                                    </a>
                                 </li>
                              </ul>
                              <div class="d-flex align-items-center katilimci-ekle-alani">
                                 <!-- Mesaj İçeriği Butonu -->
                                 <button class="btn btn-info btn-sm mr-2" id="mesajIcerigiBtn">
                                    <i class="fa fa-envelope mr-1"></i> Kampanya İçeriği
                                 </button>
                                 <!-- Katılımcı Ekleme Alanı -->
                                 <div class="katilimci-select-container mr-2" style="min-width: 250px;">
                                    <select class="form-control form-control-sm katilimci-secim-select" id="katilimciSecimSelect">
                                       <option value="">Müşteri seçin...</option>
                                    </select>
                                 </div>
                                 <button class="btn btn-primary btn-sm" id="katilimciEkleBtn">
                                    <i class="fa fa-plus mr-1"></i> Katılımcı Ekle
                                 </button>
                              </div>
                           </div>
                        </div>
                        <div class="tab-content">
                           
                           <!-- Tümü -->
                           <div class="tab-pane fade show active" id="tum_kampanya_arama" role="tabpanel">
                              <div class="data-table-card">
                                 <div class="card-header border-bottom bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                       <h4 class="h6 font-weight-600 text-gray-800 mb-0">Tüm Katılımcılar</h4>
                                       <div class="d-flex align-items-center">
                                          <div class="search-box mr-2">
                                             <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                   <span class="input-group-text bg-white border-right-0">
                                                      <i class="fa fa-search text-muted"></i>
                                                   </span>
                                                </div>
                                                <input id="katilimciArama1" type="text" class="form-control border-left-0 arama-search" placeholder="İsim veya telefon ara..." data-target="tum_arama">
                                             </div>
                                          </div>
                                         
                                       </div>
                                    </div>
                                 </div>
                                 <div class="table-responsive " id='aranacak_musteriler1' style="max-height: 320px;">
                                    <table class="table table-hover mb-0" id="kampanya_tablo_tum_katilimci_arama">
                                       <thead class="thead-light">
                                          <tr>
                                             <th width="40%">Ad Soyad</th>
                                             <th width="30%">Telefon Numarası</th>
                                             <th width="25%">Durum</th>
                                             <th width="5%"></th>
                                          </tr>
                                       </thead>
                                       <tbody>
                                          <!-- Veriler buraya gelecek -->
                                       </tbody>
                                    </table>
                                    <div id="tum_arama_empty" class="empty-state">
                                       <div class="empty-state-icon">
                                          <i class="fa fa-users text-muted"></i>
                                       </div>
                                       <p class="empty-state-text">Katılımcı bulunmamaktadır</p>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           
                           <!-- Katılanlar -->
                           <div class="tab-pane fade" id="kampanya_katilanlar_arama" role="tabpanel">
                              <div class="data-table-card">
                                 <div class="card-header border-bottom bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                       <h4 class="h6 font-weight-600 text-gray-800 mb-0">Katılanlar</h4>
                                       <div class="d-flex align-items-center">
                                          <div class="search-box mr-2">
                                             <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                   <span class="input-group-text bg-white border-right-0">
                                                      <i class="fa fa-search text-muted"></i>
                                                   </span>
                                                </div>
                                                <input id="katilimciArama2" type="text" class="form-control border-left-0 arama-search" placeholder="İsim veya telefon ara..." data-target="katilan_arama">
                                             </div>
                                          </div>
                                       
                                       </div>
                                    </div>
                                 </div>
                                 <div class="table-responsive" id='aranacak_musteriler2' style="max-height: 320px;">
                                    <table class="table table-hover mb-0" id="kampanya_tablo_katilanlar_katilimci_arama">
                                       <thead class="thead-light">
                                          <tr>
                                             <th width="45%">Ad Soyad</th>
                                             <th width="45%">Telefon Numarası</th>
                                             <th width="10%"></th>
                                          </tr>
                                       </thead>
                                       <tbody>
                                          <!-- Veriler buraya gelecek -->
                                       </tbody>
                                    </table>
                                    <div id="katilan_arama_empty" class="empty-state">
                                       <div class="empty-state-icon">
                                          <i class="fa fa-user-check text-muted"></i>
                                       </div>
                                       <p class="empty-state-text">Katılan bulunmamaktadır</p>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           
                           <!-- Katılmayanlar -->
                           <div class="tab-pane fade" id="kampanya_katilmayanlar_arama" role="tabpanel">
                              <div class="data-table-card">
                                 <div class="card-header border-bottom bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                       <h4 class="h6 font-weight-600 text-gray-800 mb-0">Katılmayanlar</h4>
                                       <div class="d-flex align-items-center">
                                          <div class="search-box mr-2">
                                             <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                   <span class="input-group-text bg-white border-right-0">
                                                      <i class="fa fa-search text-muted"></i>
                                                   </span>
                                                </div>
                                                <input id="katilimciArama3" type="text" class="form-control border-left-0 arama-search" placeholder="İsim veya telefon ara..." data-target="katilmayan_arama">
                                             </div>
                                          </div>
                                         
                                       </div>
                                    </div>
                                 </div>
                                 <div class="table-responsive " id='aranacak_musteriler3' style="max-height: 250px;">
                                    <table class="table table-hover mb-0" id="kampanya_tablo_katilmayanlar_katilimci_arama">
                                       <thead class="thead-light">
                                          <tr>
                                             <th width="45%">Ad Soyad</th>
                                             <th width="45%">Telefon Numarası</th>
                                             <th width="10%"></th>
                                          </tr>
                                       </thead>
                                       <tbody>
                                          <!-- Veriler buraya gelecek -->
                                       </tbody>
                                    </table>
                                    <div id="katilmayan_arama_empty" class="empty-state">
                                       <div class="empty-state-icon">
                                          <i class="fa fa-user-times text-muted"></i>
                                       </div>
                                       <p class="empty-state-text">Katılmayan bulunmamaktadır</p>
                                    </div>
                                 </div>
                                 <div class="card-footer bg-white border-top py-3">
                                    <div class="text-center">
                                       <button class="btn btn-outline-success btn-action" id="kampanyabeklenenleritekrarara">
                                          <i class="fa fa-redo-alt mr-2"></i> Tekrar Aramamı İster Misiniz?
                                       </button>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           
                           <!-- Beklenenler -->
                           <div class="tab-pane fade" id="kampanya_beklenen_arama" role="tabpanel">
                              <div class="data-table-card">
                                 <div class="card-header border-bottom bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                       <h4 class="h6 font-weight-600 text-gray-800 mb-0">Beklenenler</h4>
                                       <div class="d-flex align-items-center">
                                          <div class="search-box mr-2">
                                             <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                   <span class="input-group-text bg-white border-right-0">
                                                      <i class="fa fa-search text-muted"></i>
                                                   </span>
                                                </div>
                                                <input id="katilimciArama4" type="text" class="form-control border-left-0 arama-search" placeholder="İsim veya telefon ara..." data-target="beklenen_arama">
                                             </div>
                                          </div>
                                       
                                       </div>
                                    </div>
                                 </div>
                                 <div class="table-responsive "  id='aranacak_musteriler4' style="max-height: 250px;">
                                    <table class="table table-hover mb-0" id="kampanya_tablo_beklenen_katilimci_arama">
                                       <thead class="thead-light">
                                          <tr>
                                             <th width="45%">Ad Soyad</th>
                                             <th width="45%">Telefon Numarası</th>
                                             <th width="10%"></th>
                                          </tr>
                                       </thead>
                                       <tbody>
                                          <!-- Veriler buraya gelecek -->
                                       </tbody>
                                    </table>
                                    <div id="beklenen_arama_empty" class="empty-state">
                                       <div class="empty-state-icon">
                                          <i class="fa fa-clock text-muted"></i>
                                       </div>
                                       <p class="empty-state-text">Beklenen bulunmamaktadır</p>
                                    </div>
                                 </div>
                                 <div class="card-footer bg-white border-top py-3">
                                    <div class="text-center">
                                       <button class="btn btn-outline-success btn-action" id="kampanyabeklenenleriara">
                                          <i class="fa fa-redo-alt mr-2"></i> Tekrar Aramamı İster Misiniz?
                                       </button>
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
   </div>
</div>

<!-- Silme Onay Modalı -->
<div class="modal fade delete-confirm-modal" id="silmeOnayModal" tabindex="-1" role="dialog">
   <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title text-danger">
               <i class="fa fa-exclamation-circle mr-2"></i>Silme İşlemi
            </h5>
            <button type="button" class="close" data-dismiss="modal">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <p id="silmeOnayMesaji" class="mb-4">Bu katılımcıyı silmek istediğinizden emin misiniz?</p>
            <input type="hidden" id="silinecekKatilimciId">
            <input type="hidden" id="silinecekTabloTuru">
            <input type="hidden" id="silinecekTabloId">
         </div>
         <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-light" data-dismiss="modal">
               İptal
            </button>
            <button type="button" class="btn btn-danger" id="silmeOnayBtn">
               Sil
            </button>
         </div>
      </div>
   </div>
</div>

<!-- Mesaj İçeriği Modalı -->
<div class="modal fade" id="mesajIcerigiModal" tabindex="-1" role="dialog" aria-labelledby="mesajIcerigiModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="mesajIcerigiModalLabel">
               <i class="fa fa-envelope mr-2"></i>Kampanya Mesaj İçeriği
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Kapat">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <div class="alert alert-info mb-3">
               <i class="fa fa-info-circle mr-2"></i>
               Bu kampanyada müşterilere gönderilen mesajın orijinal içeriği aşağıda görüntülenmektedir.
            </div>
            <div class="mesaj-icerigi-container">
               <div class="mesaj-icerigi-header d-flex justify-content-between align-items-center mb-3">
                  <h6 class="mb-0 text-gray-700">Kampanya Metni:</h6>
                  <button class="btn btn-primary btn-sm" id="mesajIcerigiKopyala">
                     <i class="fa fa-copy mr-1"></i> Kopyala
                  </button>
               </div>
               <div class="mesaj-icerigi-content">
                  <div class="card">
                     <div class="card-body">
                        <pre id="mesajIcerigiContent" class="mb-0" style="white-space: pre-wrap; word-wrap: break-word; font-family: inherit; font-size: 0.9rem; line-height: 1.5; max-height: 400px; overflow-y: auto;">
                           Mesaj içeriği yükleniyor...
                        </pre>
                     </div>
                  </div>
               </div>
               <div class="mesaj-bilgileri mt-3">
                  <div class="row">
                     <div class="col-md-6">
                        <div class="alert alert-light">
                           <small class="text-muted">
                              <i class="fa fa-clock mr-1"></i>
                              <strong>Mesaj Tipi:</strong> Toplu SMS
                           </small>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="alert alert-light">
                           <small class="text-muted">
                              <i class="fa fa-user mr-1"></i>
                              <strong>Hedef Kitle:</strong> Kampanya Katılımcıları
                           </small>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
       
      </div>
   </div>
</div>

<style>
/* Select2 için ek stiller */
.select2-container--default .select2-selection--single {
    height: 36px;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-sm);
    background: white;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    color: var(--gray-800);
    font-size: 0.875rem;
    padding-left: 10px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: var(--primary-light);
    color: var(--primary);
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: var(--primary);
    color: white;
}

.select2-container--default .select2-dropdown {
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-md);
}

.musteri-option {
    padding: 8px 12px;
}

.musteri-option .musteri-ad {
    font-weight: 500;
    color: var(--gray-800);
}

.musteri-option .musteri-telefon {
    margin-top: 2px;
}

/* Mesaj İçeriği Modalı için stiller */
#mesajIcerigiModal .modal-content {
    border: none;
    border-radius: var(--radius-lg);
    overflow: hidden;
}

#mesajIcerigiModal .modal-header {
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

#mesajIcerigiModal .modal-body {
    padding: 1.5rem;
}

#mesajIcerigiModal .modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gray-200);
    background: var(--gray-50);
}

.mesaj-icerigi-container {
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    background: white;
}

.mesaj-icerigi-content .card {
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-sm);
    background: #f8f9fa;
}

.mesaj-icerigi-content pre {
    font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
    color: var(--gray-800);
}

/* Mesaj İçeriği Modalı için responsive stiller */
@media (max-width: 768px) {
    #mesajIcerigiModal .modal-dialog {
        margin: 0.5rem;
    }
    
    .mesaj-icerigi-container {
        padding: 1rem;
    }
    
    .mesaj-icerigi-header {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    #mesajIcerigiKopyala {
        margin-top: 0.5rem;
        width: 100%;
    }
    
    .mesaj-bilgileri .row > div {
        margin-bottom: 0.5rem;
    }
}

/* Modern ve Minimalist Tema */
:root {
   --primary: #e53e3e;
   --primary-light: #ebf8ff;
   --success: #38a169;
   --success-light: #f0fff4;
   --danger: #e53e3e;
   --danger-light: #fff5f5;
   --warning: #d69e2e;
   --warning-light: #fffaf0;
   --gray-50: #f7fafc;
   --gray-100: #edf2f7;
   --gray-200: #e2e8f0;
   --gray-300: #cbd5e0;
   --gray-400: #a0aec0;
   --gray-700: #4a5568;
   --gray-800: #2d3748;
   --radius-sm: 6px;
   --radius-md: 8px;
   --radius-lg: 12px;
   --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
   --shadow-md: 0 2px 8px rgba(0,0,0,0.08);
   --shadow-lg: 0 4px 12px rgba(0,0,0,0.1);
}

/* Modal Genel Stilleri */
.modal-content {
   border: none;
   border-radius: var(--radius-lg);
   overflow: hidden;
   box-shadow: var(--shadow-lg);
}

.modal-header {
   padding: 1.5rem 1.5rem 0.5rem;
   background: white;
}

.modal-body {
   padding: 1.25rem;
}

/* Özet Kartları */
.summary-card {
   border: 1px solid var(--gray-200);
   border-radius: var(--radius-md);
   background: white;
   transition: all 0.3s ease;
   height: 100%;
}

.summary-card:hover {
   transform: translateY(-2px);
   box-shadow: var(--shadow-md);
   border-color: var(--primary);
}

.icon-circle {
   width: 48px;
   height: 48px;
   border-radius: 12px;
   display: flex;
   align-items: center;
   justify-content: center;
}

.bg-blue-soft { background-color: var(--primary-light); }
.bg-green-soft { background-color: var(--success-light); }
.bg-orange-soft { background-color: #fffaf0; }
.bg-purple-soft { background-color: #faf5ff; }

.text-blue { color: var(--primary); }
.text-green { color: var(--success); }
.text-orange { color: var(--warning); }
.text-purple { color: #805ad5; }

/* Tab Menüleri */
.main-tabs .nav-pills {
   border-bottom: none;
}

.main-tabs .nav-pills .nav-link {
   padding: 0.75rem 1.5rem;
   border-radius: var(--radius-md);
   background: white;
   border: 1px solid var(--gray-200);
   color: var(--gray-700);
   font-weight: 500;
   margin-right: 0.5rem;
   transition: all 0.2s ease;
}

.main-tabs .nav-pills .nav-link:hover {
   border-color: var(--primary);
   color: var(--primary);
}

.main-tabs .nav-pills .nav-link.active {
   background: var(--primary);
   border-color: var(--primary);
   color: white;
   box-shadow: var(--shadow-sm);
}

/* Alt Tablar */
.subtabs-container {
   background: white;
   border-radius: var(--radius-md);
   padding: 0.5rem;
   box-shadow: var(--shadow-sm);
}

.subtabs-nav {
   border: none;
}

.subtabs-nav .nav-link {
   border: none;
   padding: 0.75rem 1rem;
   color: var(--gray-600);
   font-weight: 500;
   border-radius: var(--radius-sm);
   margin: 0 0.25rem;
   transition: all 0.2s ease;
   position: relative;
   background: transparent;
}

.subtabs-nav .nav-link:hover {
   background: var(--gray-50);
   color: var(--gray-800);
}

.subtabs-nav .nav-link.active {
   background: var(--primary-light);
   color: var(--primary);
   font-weight: 600;
}

.subtabs-nav .nav-link.active::after {
   content: '';
   position: absolute;
   bottom: -0.5rem;
   left: 50%;
   transform: translateX(-50%);
   width: 24px;
   height: 3px;
   background: var(--primary);
   border-radius: 2px;
}

/* Katılımcı Ekleme Alanı */
.katilimci-ekle-alani {
   min-width: 500px;
   justify-content: flex-end;
}

.katilimci-select-container {
   flex-grow: 1;
   max-width: 250px;
}

.katilimci-secim-select {
   height: 36px;
   font-size: 0.875rem;
   border-radius: var(--radius-sm);
   border: 1px solid var(--gray-300);
   transition: all 0.2s ease;
}

.katilimci-secim-select:focus {
   border-color: var(--primary);
   box-shadow: 0 0 0 2px rgba(66, 153, 225, 0.15);
}

/* Tablo Kartları */
.data-table-card {
   border: 1px solid var(--gray-200);
   border-radius: var(--radius-md);
   overflow: hidden;
   background: white;
}

.data-table-card .card-header {
   padding: 1rem 1.5rem;
   background: white;
}

.data-table-card .card-footer {
   padding: 1rem 1.5rem;
   background: var(--gray-50);
}

/* Arama Kutuları */
.search-box {
   min-width: 250px;
}

.search-box .input-group {
   border: 1px solid var(--gray-300);
   border-radius: var(--radius-sm);
   overflow: hidden;
}

.search-box .input-group:focus-within {
   border-color: var(--primary);
   box-shadow: 0 0 0 2px rgba(66, 153, 225, 0.15);
}

.search-box .form-control {
   border: none;
   background: white;
   font-size: 0.875rem;
   padding: 0.5rem 0.75rem;
}

.search-box .form-control:focus {
   box-shadow: none;
}

.search-box .input-group-text {
   border: none;
   background: white;
   color: var(--gray-400);
}

/* Tablolar */
.table {
   margin-bottom: 0;
   border-collapse: separate;
   border-spacing: 0;
}

.table thead th {
   border: none;
   padding: 1rem 1.5rem;
   background: var(--gray-50);
   color: var(--gray-700);
   font-weight: 600;
   font-size: 0.875rem;
   text-transform: uppercase;
   letter-spacing: 0.5px;
   border-bottom: 2px solid var(--gray-200);
}

.table tbody td {
   padding: 1rem 1.5rem;
   border-bottom: 1px solid var(--gray-100);
   vertical-align: middle;
   font-size: 0.875rem;
   color: var(--gray-800);
}

.table tbody tr:last-child td {
   border-bottom: none;
}

.table tbody tr:hover {
   background-color: var(--gray-50);
}

.table-responsive {
   position: relative;
   scrollbar-width: thin;
   scrollbar-color: var(--gray-300) var(--gray-100);
}

.table-responsive::-webkit-scrollbar {
   width: 8px;
   height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
   background: var(--gray-100);
   border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
   background: var(--gray-300);
   border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
   background: var(--gray-400);
}

/* Durum Badgeleri */
.status-badge {
   display: inline-block;
   padding: 0.25rem 0.75rem;
   border-radius: 12px;
   font-size: 0.75rem;
   font-weight: 600;
   text-transform: uppercase;
   letter-spacing: 0.5px;
}

.status-aktif {
   background: var(--success-light);
   color: var(--success);
}

.status-pasif {
   background: var(--danger-light);
   color: var(--danger);
}

.status-beklemede {
   background: var(--warning-light);
   color: var(--warning);
}

/* Boş Durum */
.empty-state {
   display: none;
   padding: 3rem 1.5rem;
   text-align: center;
   background: white;
}

.empty-state-icon {
   font-size: 3rem;
   color: var(--gray-300);
   margin-bottom: 1rem;
}

.empty-state-text {
   color: var(--gray-400);
   font-size: 0.875rem;
   margin: 0;
}

/* Butonlar */
.btn-action {
   padding: 0.75rem 1.5rem;
   border-radius: var(--radius-md);
   font-weight: 500;
   transition: all 0.2s ease;
   border: 1px solid var(--success);
   color: var(--success);
   background: white;
}

.btn-action:hover {
   background: var(--success);
   color: white;
   transform: translateY(-1px);
   box-shadow: var(--shadow-sm);
}

.btn-primary {
   background: var(--primary);
   border-color: var(--primary);
   padding: 0.5rem 1.25rem;
   border-radius: var(--radius-sm);
   font-weight: 500;
   height: 36px;
   display: inline-flex;
   align-items: center;
   justify-content: center;
}

.btn-primary:hover {
   background: #c53030;
   border-color: #c53030;
}

.btn-info {
   background: #17a2b8;
   border-color: #17a2b8;
   padding: 0.5rem 1.25rem;
   border-radius: var(--radius-sm);
   font-weight: 500;
   height: 36px;
   display: inline-flex;
   align-items: center;
   justify-content: center;
}

.btn-info:hover {
   background: #c53030;
   border-color: #c53030;
}

.btn-outline-primary {
   border-color: var(--primary);
   color: var(--primary);
}

.btn-outline-primary:hover {
   background: var(--primary);
   color: white;
}

.btn-light {
   background: var(--gray-100);
   border-color: var(--gray-200);
   color: var(--gray-700);
   padding: 0.5rem 1.25rem;
   border-radius: var(--radius-sm);
   font-weight: 500;
}

.btn-danger {
   background: var(--danger);
   border-color: var(--danger);
   padding: 0.5rem 1.25rem;
   border-radius: var(--radius-sm);
   font-weight: 500;
}

.btn-danger:hover {
   background: #c53030;
   border-color: #c53030;
}

.btn-secondary {
   background: #6c757d;
   border-color: #6c757d;
   padding: 0.5rem 1.25rem;
   border-radius: var(--radius-sm);
   font-weight: 500;
}

.btn-secondary:hover {
   background: #5a6268;
   border-color: #545b62;
}

/* Silme İşlem Butonu */
.btn-delete {
   background: transparent;
   border: none;
   color: var(--danger);
   padding: 0.375rem 0.75rem;
   border-radius: var(--radius-sm);
   transition: all 0.2s ease;
   font-size: 0.875rem;
}

.btn-delete:hover {
   background: var(--danger-light);
   color: #c53030;
}

/* Badgeler */
.badge-sm {
   font-size: 0.75rem;
   padding: 0.125rem 0.5rem;
   min-width: 20px;
   height: 20px;
   border-radius: 10px;
   display: inline-flex;
   align-items: center;
   justify-content: center;
}

/* Responsive Tasarım */
@media (max-width: 1200px) {
   .katilimci-ekle-alani {
      min-width: 450px;
   }
   
   .katilimci-select-container {
      max-width: 200px;
   }
}

@media (max-width: 992px) {
   .modal-dialog {
      margin: 1rem;
   }
   
   .modal-content {
      margin: 0 auto;
   }
   
   .subtabs-container {
      flex-direction: column;
   }
   
   .subtabs-nav {
      margin-bottom: 1rem;
   }
   
   .katilimci-ekle-alani {
      min-width: 100%;
      justify-content: space-between;
      margin-top: 1rem;
   }
   
   .katilimci-select-container {
      max-width: none;
      flex-grow: 1;
      margin-right: 10px;
   }
   
   .search-box {
      min-width: 100%;
      margin-top: 1rem;
   }
   
   .main-tabs .nav-pills .nav-link {
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
   }
   
   .data-table-card .card-header {
      flex-direction: column;
      align-items: flex-start !important;
   }
   
   .data-table-card .card-header h4 {
      margin-bottom: 1rem;
   }
}

@media (max-width: 768px) {
   .modal-header {
      padding: 1rem 1rem 0.5rem;
   }
   
   .dashboard-cards {
      padding: 1rem;
   }
   
   .main-tabs .nav-pills {
      flex-wrap: nowrap;
      overflow-x: auto;
      padding-bottom: 0.5rem;
   }
   
   .main-tabs .nav-pills .nav-link {
      white-space: nowrap;
      margin-right: 0.25rem;
   }
   
   .subtabs-nav {
      flex-wrap: nowrap;
      overflow-x: auto;
      padding-bottom: 0.5rem;
   }
   
   .subtabs-nav .nav-link {
      white-space: nowrap;
   }
   
   .table thead th,
   .table tbody td {
      padding: 0.75rem 1rem;
   }
   
   .icon-circle {
      width: 40px;
      height: 40px;
      border-radius: 10px;
   }
   
   .btn-action {
      width: 100%;
      padding: 0.75rem;
   }
   
   .katilimci-ekle-alani {
      flex-direction: column;
   }
   
   .katilimci-select-container {
      margin-right: 0;
      margin-bottom: 10px;
      width: 100%;
   }
   
   #katilimciEkleBtn,
   #mesajIcerigiBtn {
      width: 100%;
      margin-bottom: 10px;
   }
   
   #mesajIcerigiBtn {
      margin-right: 0;
   }
}

@media (max-width: 576px) {
   .modal-dialog {
      margin: 0.5rem;
   }
   
   .modal-content {
      border-radius: var(--radius-md);
   }
   
   .table-responsive {
      font-size: 0.8125rem;
   }
   
   .table thead th,
   .table tbody td {
      padding: 0.5rem 0.75rem;
   }
   
   .empty-state {
      padding: 2rem 1rem;
   }
   
   .empty-state-icon {
      font-size: 2rem;
   }
}

/* Animasyonlar */
.fade {
   transition: opacity 0.15s linear;
}

.modal.fade .modal-dialog {
   transition: transform 0.3s ease-out;
   transform: translateY(-50px);
}

.modal.show .modal-dialog {
   transform: none;
}

/* Accessibility */
.btn:focus,
.form-control:focus {
   outline: none;
   box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.25);
}

/* Print Stilleri */
@media print {
   .modal-content {
      box-shadow: none;
      border: 1px solid var(--gray-300);
   }
   
   .btn-action,
   .btn-delete,
   .search-box,
   .katilimci-ekle-alani,
   #mesajIcerigiBtn {
      display: none !important;
   }
}
</style>