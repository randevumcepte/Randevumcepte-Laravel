@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="row pb-10">
               @if($kullaniciRolu < 5)
              <div class="col-xl-4 col-lg-4  col-md-4 col-sm-4 col-xs-6 col-6 mb-20">
                  <div class="card-box height-100-p widget-style3">
                     <div class="d-flex flex-wrap">
                        <div class="widget-data">
                           <div class="weight-700 font-24 text-dark" id='gelen_arama_sayisi'>{{$santral_raporlari['gelen_arama']}}</div>
                           <div class="font-14 text-secondary weight-500">
                               Gelen Arama
                           </div>
                        </div>
                        <div class="widget-icon" style="background-color: #28a745">
                           <div class="icon" data-color="#fff">
                              <i class="icon-copy bi bi-telephone-inbound-fill"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-xl-4 col-lg-4  col-md-4 col-sm-4 col-xs-6 col-6 mb-20">
                  <div class="card-box height-100-p widget-style3">
                     <div class="d-flex flex-wrap">
                        <div class="widget-data">
                           <div class="weight-700 font-24 text-dark" id='giden_arama_sayisi'>{{$santral_raporlari['giden_arama']}}</div>
                           <div class="font-14 text-secondary weight-500">
                              Giden Arama
                           </div>
                        </div>
                        <div class="widget-icon" style="background-color:rgb(146, 0, 188)">
                           <div class="icon" data-color="#fff">
                              
                             <i class="icon-copy bi bi-telephone-outbound-fill"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
                
               <div class="col-xl-4 col-lg-4  col-md-4 col-sm-4 col-xs-12 col-12 mb-20">
                  <div class="card-box height-100-p widget-style3">
                     <div class="d-flex flex-wrap">
                        <div class="widget-data">
                           <div class="weight-700 font-24 text-dark" id='cevapsiz_arama_sayisi'>{{$santral_raporlari['cevapsiz_arama']}}</div>
                           <div class="font-14 text-secondary weight-500">
                            Cevapsız Arama
                           </div>
                        </div>
                        <div class="widget-icon" style="background-color: #ff0000 ">
                           <div class="icon"  data-color="#fff">
                               <i class="icon-copy bi bi-telephone-x-fill"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               
               <div class="col-xl-3 col-lg-3  col-md-6 col-sm-6 col-xs-6 col-6 mb-20" style="display:none">
                  <div class="card-box height-100-p widget-style3">
                     <div class="d-flex flex-wrap">
                        <div class="widget-data">
                           <div class="weight-700 font-24 text-dark" id='ses_kayitlari'>0</div>
                           <div class="font-14 text-secondary weight-500">Sesli Mesaj</div>
                        </div>
                        <div class="widget-icon" style="background-color:rgb(234, 67, 242)">
                           <div class="icon" data-color="#fff">
                              
                              <i class="icon-copy bi bi-mic-fill"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               @endif
               <div class="col-lg-12 col-md-12 col-sm-12 mb-30">

                     <div class="pd-20 card-box">
                         @if($kullaniciRolu < 5)
                        <div class="row">
                           <div class="col-sm-4 col-xs-6 col-6 col-md-4">
                              <div class="form-group">
                                 <label>Rapor Başlangıç Tarihi</label>
                                 <input type="text" id="cdr_baslangic_tarihi" value="{{date('Y-m-d')}}" class="form-control">
                              </div>
                           </div>
                           <div class="col-sm-4 col-xs-6 col-6 col-md-4 ">
                                 <label>Rapor Bitiş Tarihi</label>
                                 <input type="text" id="cdr_bitis_tarihi" value="{{date('Y-m-d')}}" class="form-control">
                           </div>
                           <div class="col-sm-4 col-xs-6 col-6 col-md-4 text-right" >
                              <div class="form-group" >
                                    <label style="visibility:hidden">Rapor Bitiş Tarihi</label>
                             <button class="btn btn-success"  data-toggle="modal" id='arama_listesi_olustur' data-target="#santral_musteri_listesi"> <i class="fa fa-plus"></i> Arama Listesi Oluştur</button> 
                              </div>
                             
                           </div>
                        </div>
                        @endif
                         <h2 style="margin-bottom:20px">Santral Özet</h2>
                        <div class="tab">
                        
                           <ul class="nav nav-tabs element" role="tablist" style="overflow-x: auto;  overflow-y: hidden; height: 50px;
                           
 

                           ">
                           @if($kullaniciRolu < 5)
                              <li class="nav-item">
                                 <button class="btn btn-outline-primary active" data-toggle="tab" href="#tum-aramalar" role="tab" aria-selected="true">Tümü</button>
                              </li>
                              <li class="nav-item" style="margin-left: 20px;display: inline-block;">
                                 <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#gelen-arama"
                                    role="tab"
                                    aria-selected="false"
                                    >Gelen Arama</button
                                 >
                              </li>
                              <li class="nav-item" style="margin-left: 20px;">
                                 <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#giden-arama"
                                    role="tab"
                                    aria-selected="false"
                                    >Giden Arama</button
                                 >
                              </li>
                              <li class="nav-item" style="margin-left: 20px;display: inline-block;">
                                 <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#cevapsiz-arama"
                                    role="tab"
                                    aria-selected="false"
                                    >Cevapsız Arama</button
                                 >
                              </li>
                              @endif
                              <li class="nav-item" style="margin-left: 20px;display: inline-block;">
                                 <button
                                    id="arama_listesi_tablosu_button"
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#arama_listesi_tablosu"
                                    role="tab"
                                    aria-selected="false"
                                    >Arama Listesi</button
                                 >
                              </li>
                              @if($kullaniciRolu < 5)
                              <li class="nav-item" style="margin-left: 20px;display: none;">
                                 <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#konusma-paketleri"
                                    role="tab"
                                    aria-selected="false"
                                    >Paketler</button
                                 >
                              </li>
                              <li class="nav-item" style="margin-left: 20px;display: none;">
                                 <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#faturalar"
                                    role="tab"
                                    aria-selected="false"
                                    >Faturalar</button
                                 >
                              </li>
                              <li class="nav-item" style="margin-left: 20px;display: inline-block;">
                                 <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#santral-ayarlar"
                                    role="tab"
                                    aria-selected="false"
                                    >Ayarlar</button
                                 >
                              </li>
                              @endif
                            
                             
                              
                           </ul>
                          
                           <div class="tab-content">

                              <div
                                 class="tab-pane fade {{$kullaniciRolu < 5 ? 'show active' : ''}}"
                                 id="tum-aramalar"
                                 role="tabpanel" style="margin-top: 20px;"
                              >
                                      

                                    <table class="data-table table stripe hover nowrap" id="santral_arama_tum">
                                             <thead >
                                                <th>Müşteri</th>
                                                <th>Telefon Numarası</th>
                                                <th>Görüşme Yapan</th>
                                                 
                                                <th>Tarih</th>
                                                <th>Saat</th>
                                                <th>Durum </th>
                                             
                                                <th>Ses Kaydı & Arama</th>
                                                
                                             </thead>
                                             <tbody>
                                            
                                             </tbody>
                                    </table>
                                 
                              </div>
                              <div
                                 class="tab-pane fade"
                                 id="gelen-arama"
                                 role="tabpanel" style="margin-top: 20px; overflow-x: scroll;height: auto;">
                                 <div class="tab">
                                       <table class="data-table table stripe hover nowrap" id="santral_gelen_arama">
                                             <thead >
                                                <th>Müşteri</th>
                                                <th>Telefon Numarası</th>
                                                <th>Görüşme Yapan</th>
                                                 
                                                <th>Tarih</th>
                                                <th>Saat</th>
                                                <th>Durum </th>
                                             
                                                <th>Ses Kaydı & Arama</th>
                                                
                                             </thead>
                                             <tbody>
                                            
                                             </tbody>
                                    </table>
                                 </div>
                                 
                              </div>
                              <div
                                 class="tab-pane fade"
                                 id="giden-arama"
                                 role="tabpanel" style="margin-top: 20px;"
                              >
                                    <table class="data-table table stripe hover nowrap" id="santral_giden_arama">
                                             <thead >
                                                <th>Müşteri</th>
                                                <th>Telefon Numarası</th>
                                                <th>Görüşme Yapan</th>
                                                 
                                                <th>Tarih</th>
                                                <th>Saat</th>
                                                 
                                                <th>Durum </th>
                                                <th>Ses Kaydı & Arama</th>
                                                
                                             </thead>
                                             <tbody>
                                            
                                             </tbody>
                                    </table>
                                       
                                   
                                  
                              
                              </div>

                              <div
                                 class="tab-pane fade"
                                 id="cevapsiz-arama"
                                 role="tabpanel" style="margin-top: 20px;"
                              >
                                   <table class="data-table table stripe hover nowrap" id="santral_cevapsiz_arama">
                                             <thead >
                                                <th>Müşteri</th>
                                                <th>Telefon Numarası</th>
                                                <th>Görüşme Yapan</th>
                                                 
                                                <th>Tarih</th>
                                                <th>Saat</th>
                                                 
                                                <th>Durum </th>
                                                <th>Ses Kaydı & Arama</th>
                                                
                                             </thead>
                                             <tbody>
                                            
                                             </tbody>
                                    </table>
                                
                              
                              </div>
                              <div
                                 class="tab-pane fade {{$kullaniciRolu == 5 ? 'show active' : ''}}"
                                 id="arama_listesi_tablosu"
                                 role="tabpanel" style="margin-top: 20px;"
                              >
                                      

                                    <table class="data-table table stripe hover nowrap" id="arama_liste_tablo">
                                             <thead >
                                                <th>Başlık</th>
                                                <th>Personel</th>
                                                <th>Detaylar </th>
                                              
                                                
                                             </thead>
                                             <tbody>
                                            
                                             </tbody>
                                    </table>
                                 
                              </div>
                              <div
                                 class="tab-pane fade"
                                 id="santral-ayarlar"
                                 role="tabpanel" style="margin-top: 20px;"
                              >
                                 <form id="santral_ayarlari" method="POST">
                                    {{csrf_field()}}
                                    <input  type="hidden" name="sube" value="{{$isletme->id}}">
                                    <div class="row" data-value="0">
                                       <div class=" col-md-4 col-sm-12 mb-30">
                                          <div class="pd-20 card-box  mb-10">
                                             <h6>Yaklaşan Randevu Hatırlatma</h6>
                                             <p style="font-weight: 5px;">Randevu hatırlatmalarına dair müşteriye otomatik hatırlatma araması yapılsın/yapılmasın ayarıdır.</p>
                                             <div class="row">
                                                <div class="col-md-12 custom-control custom-checkbox mb-5">

                                                   <input type="checkbox" class="custom-control-input" {{($santral_ayarlari[0]->musteri) ? 'checked' : ''}} name='santralayar_1_musteri' id="customCheck1">
                                                   <label class="custom-control-label" for="customCheck1"  > Açık / Kapalı</label>
                                                </div>
                                                 
                                             </div>
                                             <p style="font-weight: 5px;">Kaç saat önce aransın?</p>
                                             <select class="form-control" name="randevu_hatirlatama_saat_once" >
                                             <option {{($isletme->randevu_cagri_hatirlatma==1) ? 'selected' : ''}} value="1">1 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==2) ? 'selected' : ''}} value="2" selected="">2 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==3) ? 'selected' : ''}} value="3">3 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==4) ? 'selected' : ''}} value="4">4 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==5) ? 'selected' : ''}} value="5">5 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==6) ? 'selected' : ''}} value="6">6 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==7) ? 'selected' : ''}} value="7">7 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==8) ? 'selected' : ''}} value="8">8 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==9) ? 'selected' : ''}} value="9">9 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==10) ? 'selected' : ''}} value="10">10 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==11) ? 'selected' : ''}} value="11">11 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==12) ? 'selected' : ''}} value="12">12 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==13) ? 'selected' : ''}} value="13">13 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==14) ? 'selected' : ''}} value="14">14 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==15) ? 'selected' : ''}} value="15">15 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==16) ? 'selected' : ''}} value="16">16 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==17) ? 'selected' : ''}} value="17">17 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==18) ? 'selected' : ''}} value="18">18 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==19) ? 'selected' : ''}} value="19">19 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==20) ? 'selected' : ''}} value="20">20 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==21) ? 'selected' : ''}} value="21">21 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==22) ? 'selected' : ''}} value="22">22 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==23) ? 'selected' : ''}} value="23">23 saat</option>
                                             </select>
                                          </div>
                                          <div class="col-md-12" style="margin-top: 80px;">
                                             <button type="submit" class="btn btn-success btn-block">Ayarları Güncelle</button>
                                          </div>
                                       </div>
                                    </div>
                                 </form>
                                
                              
                              </div>
                              
                               
                           </div>
                        </div>
                     </div>
                  </div>
                  
</div>

 
@endsection