@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-6">
            <div class="title">
               <h1 style="font-size:20px">Asistanım</h1>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                     <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                  </li>
                  
                  <li class="breadcrumb-item active" aria-current="page">
                   Asistanım
                  </li>
               </ol>
            </nav>
         </div>
  
 
      </div>
   </div>
   <div class="row clearfix">
     <div class="col-lg-12 col-md-12 col-sm-12 mb-30">
       <div class="pd-20 card-box">
      
         <div class="tab">
           <div class="row clearfix">
             <div class=" col-md-12 col-sm-12">
               <ul class="nav nav-tabs element" role="tablist" style="overflow-x:scroll;height: 50px; ">
                <li class="nav-item">
                  <button href="#bugunku_e_asistan"
                  class="btn btn-outline-primary active  "
                  data-toggle='tab'
                  role="tab"
                  style="width: 150px;" 
                  aria-selected="true"
                  > Bugünkü Görevlerim </button>
                
                 <li class="nav-item">
                  <buton href="#yarinki_gorevler"
                  class="btn btn-outline-primary"
                  data-toggle='tab'
                  role="tab" 
                   style="width: 150px;margin-left: 10px;" 
                  aria-selected="false" 
                  > Yarınki Görevlerim </button>
                </li>
                 <li class="nav-item">
                  <button href="#e_asistan_ayarlari"
                  class="btn btn-outline-primary "
                  data-toggle='tab'
                  role="tab"
                   style="width: 200px;margin-left: 10px;" 
                
                  aria-selected="false" 
                  >Asistan Ayarları</button>
                </li>
                
               </ul>
             </div>
             <div class="col-md-12 col-sm-12" >
              <div class="tab-content">
                   <div class="tab-pane fade show active" id="bugunku_e_asistan" role="tabpanel">
                 <div class="pd-20">
                  <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                    <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">Bugünkü Görevlerim</h2>
                   </div>
                  
                  </div>
                  <table class="data-table table stripe hover nowrap" id="bugunkugorevtablo">
                                             <thead >
                                             
                                                
                                                <th>Mesaj</th>
                                                <th>Durum </th>
                                             
                                                   
                                             </thead>
                                             <tbody>
                                            
                                             </tbody>
                                        </table>
                                           
                                       </div>
                 </div>
              
                 
               <div class="tab-pane fade show" id="yarinki_gorevler" role="tabpanel">
                 <div class="pd-20">
                  <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                    <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">Yarınki Görevlerim</h2>
                   </div>
                  </div>
               
                  <table class="data-table table stripe hover nowrap" id="yarinkigorevtablo">
                                             <thead>
                                            
                                                <th>Mesaj</th>
                                                <th>Durum </th>
                                              
                                             </thead>
                                             <tbody>
                                            
                                             </tbody>
                                        </table>
                 
                 </div>
               </div>
                
                <div class="tab-pane fade show" id="e_asistan_ayarlari" role="tabpanel">
                 <div class="pd-20">
                  <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                    <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">Asistan Ayarları</h2>
                   </div>
                  </div>
                  <form id="otomatik_e_asistan_ayarlari" method="POST">
                    {{csrf_field()}}
                    <input  type="hidden" name="sube" value="{{$isletme->id}}">
                   <div class="row" data-value="0">
                      <div class=" col-md-4 col-sm-12 mb-30">
                         
                            <div class="pd-20 card-box mb-10">
                             
                               <h6>Alacak Hatırlatması</h6>
                               <p style="font-weight: 5px;">Müşterilerin alacak hatırlatmalarını 2 gün öncesinden araması yapılsın/yapılmasın ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                    
                                        <input type="checkbox" class="custom-control-input" {{($e_asistan_ayarlari[0]->acik_kapali) ? 'checked' : ''}} id="customCheck1" name='e_asistan_alacak_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck1">Açık / Kapalı</label>
                                                                      </div>
                               </div>
                            </div>
                             
                          
                            <div class="pd-20 card-box mb-10"  >
                               <h6>Randevu Hatırlatma</h6>
                               <p style="font-weight: 5px;">Randevu hatırlatmalarını 1 gün öncesinden araması yapılsın/yapılmasın ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                    
                                        <input type="checkbox" class="custom-control-input" {{($e_asistan_ayarlari[3]->acik_kapali) ? 'checked' : ''}} id="customCheck2" name='e_asistan_randevu_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck2">Açık / Kapalı</label>
                                     
                                  </div>
                               </div>
                            </div>
                            <div class="pd-20 card-box mb-10"  >
                               <h6>Doğum Günü Hatırlatma</h6>
                               <p style="font-weight: 5px;">Doğum günü olan müşterilerinize kutlama araması yapılsın/yapılmasın ayarıdır. Bu ayar işletmenize/kendinize özel gönderici adınızın olması durumunda çalışmaktadır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                    
                                        <input type="checkbox" class="custom-control-input" {{($e_asistan_ayarlari[6]->acik_kapali) ? 'checked' : ''}} id="customCheck7" name='e_asistan_dogumgunu_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck7">Açık / Kapalı</label>
                                     
                                  </div>
                               </div>
                            </div>
                           
                            
                      </div>

                      <div class=" col-md-4 col-sm-12 mb-30">
                        

                            <div class="pd-20 card-box mb-10">
                             
                               <h6>Ön Görüşme Hatırlatması</h6>
                               <p style="font-weight: 5px;">Ön görüşme hatırlatmalarının 1 gün öncesinden araması yapılsın/yapılmasın ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                    
                                        <input type="checkbox" class="custom-control-input" id="customCheck3" {{($e_asistan_ayarlari[1]->acik_kapali) ? 'checked' : ''}}  name='e_asistan_ongorusme_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck3">Açık / Kapalı</label>
                                                                      </div>
                               </div>
                            </div>

                            <div class="pd-20 card-box mb-10"  >
                               <h6>Kampanya Hatırlatması</h6>
                               <p style="font-weight: 5px;">Kampanya ve promosyonlar hakkında bilgi için arama yapılsın/yapılmasın ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                    
                                        <input type="checkbox" class="custom-control-input" {{($e_asistan_ayarlari[7]->acik_kapali) ? 'checked' : ''}} id="customCheck8" name='e_asistan_kampanya_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck8">Açık / Kapalı</label>
                                     
                                  </div>
                               </div>
                            </div>
                           
                            
                      </div>
                      <div class=" col-md-4 col-sm-12 mb-30">
                         <div class="pd-20 card-box  mb-10">
                          
                               <h6>Tekrar Arama</h6>
                               <p style="font-weight: 5px;">Ulaşılmayan aramaların tekrar araması yapılsın/yapılmasın ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                       
                                  <input type="checkbox" class="custom-control-input" id="customCheck5" {{($e_asistan_ayarlari[2]->acik_kapali) ? 'checked' : ''}} name='e_asistan_tekrar_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck5">Açık / Kapalı</label>
                                 
                                                                  </div>
                                 
                               </div>
                               <p style="font-weight: 5px;">Kaç saat sonra arasın ?</p>
                               <select class="form-control" name="arama_saat_sonra" >
                                 
                                  <option {{($isletme->e_asistan_hatirlatma==1) ? 'selected' : ''}} value="1" selected="">1 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==2) ? 'selected' : ''}} value="2" >2 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==3) ? 'selected' : ''}} value="3">3 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==4) ? 'selected' : ''}} value="4">4 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==5) ? 'selected' : ''}} value="5">5 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==6) ? 'selected' : ''}} value="6">6 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==7) ? 'selected' : ''}} value="7">7 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==8) ? 'selected' : ''}} value="8">8 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==9) ? 'selected' : ''}} value="9">9 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==10) ? 'selected' : ''}} value="10">10 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==11) ? 'selected' : ''}} value="11">11 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==12) ? 'selected' : ''}} value="12">12 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==13) ? 'selected' : ''}} value="13">13 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==14) ? 'selected' : ''}} value="14">14 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==15) ? 'selected' : ''}} value="15">15 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==16) ? 'selected' : ''}} value="16">16 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==17) ? 'selected' : ''}} value="17">17 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==18) ? 'selected' : ''}} value="18">18 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==19) ? 'selected' : ''}} value="19">19 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==20) ? 'selected' : ''}} value="20">20 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==21) ? 'selected' : ''}} value="21">21 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==22) ? 'selected' : ''}} value="22">22 saat</option>
                                  <option {{($isletme->e_asistan_hatirlatma==23) ? 'selected' : ''}} value="23">23 saat</option>
                                 
                               </select>
                            </div>
                            <div class="pd-20 card-box  mb-10">
                               <h6>Kara Liste</h6>
                               <p style="font-weight: 5px;">Müsteri numarası kara listeye eklendiginde aramalar yapılsın/yapılmasın ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                               
                                        <input type="checkbox" class="custom-control-input" id="customCheck6" {{($e_asistan_ayarlari[5]->acik_kapali) ? 'checked' : ''}} name='e_asistan_karaliste_acik_kapali'>

                                        <label class="custom-control-label" for="customCheck6">Açık / Kapalı</label>
                                 
                                  </div>
                               </div>
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
       </div>
     </div>
   </div>
     

 
 

  
 
@endsection