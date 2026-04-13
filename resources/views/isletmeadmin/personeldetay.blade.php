@extends("layout.layout_isletmeadmin")
@section("content")
<div class="page-header">
   <div class="row">
      <div class="col-md-12 col-sm-12">
         <div class="title">
            <h1>{{$sayfa_baslik}}</h1>
            <input type="hidden" id='personel_id' value="{{$personel->id}}">
         </div>
         <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
               </li>
               @if(!Auth::guard('isletmeyonetim')->user()->hasRole('Personel'))
                <li class="breadcrumb-item">
                  <a href="/isletmeyonetim/ayarlar?p=personeller&{{(isset($_GET['sube'])) ? 'sube='.$isletme->id : '' }}">Personeller</a>
               </li>
               @endif
               <li class="breadcrumb-item active" aria-current="page">
                  @if(!Auth::guard('isletmeyonetim')->user()->hasRole('Personel'))

                  {{$sayfa_baslik}}
                  @else
                  Raporlar
                   @endif
               </li>
            </ol>
         </nav>
      </div>
     
   </div>
</div>
<div class="row">
   @if(!Auth::guard('isletmeyonetim')->user()->hasRole('Personel'))
   <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 mb-30">
      <div class="pd-20 card-box height-100-p">
         <div class="profile-photo">
                            
                           <img
                              id="personel_profil_resmi"
                              src="{{(\App\IsletmeYetkilileri::where('id',$personel->id)->value('profil_resim') !== null ? \App\IsletmeYetkilileri::where('id',$personel->id)->value('profil_resim') : '/public/isletmeyonetim_assets/img/avatar.png' )}}"
                              alt=""
                              class="avatar-photo" style="object-fit: cover; width: 160px; height: 160px;"
                           />
         </div>
         <div class="profile-info">
           
            <ul>
               <li>
                  <span style="float:left;"><i class="fa fa-mobile"></i> &nbsp;</span>
                  0{{$personel->gsm1}}
               </li>
               <li>
                  <span style="float:left;"><i class="fa fa-user"></i> &nbsp;</span>
                  {{$salonpersonel->unvan}}
               </li>
               
               <li>
                  <span style="float:left;">Maaş : </span>
                   {{number_format( $salonpersonel->maas,2,",",".")}} ₺
               </li>
                <li>
                  <span style="float:left;">Hizmet Primi Hak Edişi : </span>
                   %{{$salonpersonel->hizmet_prim_yuzde}} 
               </li>
               <li>
                  <span style="float:left;">Ürün Primi Hak Edişi : </span>
                   %{{$salonpersonel->urun_prim_yuzde }} 
               </li>
               <li>
                  <span style="float:left;">Paket Primi Hak Edişi : </span>
                   %{{$salonpersonel->paket_prim_yuzde}}
               </li>
                
            </ul>
         </div>
      </div>
   </div>
   @endif

   <div class="{{(Auth::guard('isletmeyonetim')->user()->hasRole('Personel')) ? 'col-xl-12 col-lg-12 col-md-12' : 'col-xl-9 col-lg-9 col-md-9 '}} col-sm-12 mb-30">
      <div class="card-box height-100-p overflow-hidden pd-20">
          
               <div class="row">
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Ay</label>  
                        <select class="form-control" id="personel_rapor_ay">

                           <option {{(date("m")=="01") ? "selected" : ""}} value="01">Ocak</option>
                           <option {{(date("m")=="02") ? "selected" : ""}} value="02">Şubat</option>
                           <option {{(date("m")=="03") ? "selected" : ""}} value="03">Mart</option>
                           <option {{(date("m")=="04") ? "selected" : ""}} value="04">Nisan</option>
                           <option {{(date("m")=="05") ? "selected" : ""}} value="05">Mayıs</option>
                           <option {{(date("m")=="06") ? "selected" : ""}} value="06">Haziran</option>
                           <option {{(date("m")=="07") ? "selected" : ""}} value="07">Temmuz</option>
                           <option {{(date("m")=="08") ? "selected" : ""}} value="08">Ağustos</option>
                           <option {{(date("m")=="09") ? "selected" : ""}} value="09">Eylül</option>
                           <option {{(date("m")=="10") ? "selected" : ""}} value="10">Ekim</option>
                           <option {{(date("m")=="11") ? "selected" : ""}} value="11">Kasım</option>
                           <option {{(date("m")=="12") ? "selected" : ""}} value="12">Aralık</option>
                        </select>
                          
                     </div>
                  </div>
                  <div class="col-md-3"> 
                     <div class="form-group">
                        <label>Yıl</label>
                        <select class="form-control" id="personel_rapor_yil">
                           <option {{(date("Y")=="2022") ? "selected" : ""}} value="2022">2022</option>
                           <option {{(date("Y")=="2023") ? "selected" : ""}} value="2023">2023</option>
                           <option {{(date("Y")=="2024") ? "selected" : ""}} value="2024">2024</option>
                           <option {{(date("Y")=="2025") ? "selected" : ""}} value="2025">2025</option>
                           <option {{(date("Y")=="2026") ? "selected" : ""}} value="2026">2026</option>
                        </select>
                        
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group input-icons">
                        <label>Detaylı Filtre</label>
                        <i class="fa fa-calendar icon-inside-input"></i>
                        <input type="text" class="form-control" id="personel_rapor_tarih_araligi_1" placeholder="Başlangıç Tarihi" style="padding-left:50px;" autocomplete="off">
                     </div>
                  </div>
                   <div class="col-md-3">
                     <div class="form-group input-icons">
                        <label style="visibility: hidden;">Tarih Aralığı</label>
                        <i class="fa fa-calendar icon-inside-input"></i>
                        <input type="text" class="form-control" id="personel_rapor_tarih_araligi_2" placeholder="Bitiş Tarihi" style="padding-left:50px;" autocomplete="off">
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-xl-6 col-lg-6 col-md-6 mb-20">
                     <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                           <div class="widget-data">
                              <div class="row">
                                 
                                 <div class="col-6 col-xs-6 col-sm-6">
                                    <div class="weight-700 font-20 text-dark" id='hizmet_satisi'></div>
                                    <div class="font-14 text-secondary weight-500">
                                       Hizmet Satışları
                                    </div>
                                 </div>
                                 <div class="col-6 col-xs-6 col-sm-6">
                                    <div class="weight-700 font-20 text-dark" id='hizmet_primi'></div>
                                    <div class="font-14 text-secondary weight-500">
                                      Prim
                                    </div>
                                 </div>
                              </div>

                             
                           </div>
                           <div class="widget-icon" style="background-color: rgb(70, 203, 218);">
                              <div class="icon" data-color="#fff">
                                 ₺
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-6 col-lg-6 col-md-6 mb-20">
                     <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                           <div class="widget-data">
                              <div class="row">
                                 
                                 <div class="col-6 col-xs-6 col-sm-6">
                                    <div class="weight-700 font-20 text-dark" id='urun_satisi'></div>
                                    <div class="font-14 text-secondary weight-500">
                                       Ürün Satışları
                                    </div>
                                 </div> 
                                 <div class="col-6 col-xs-6 col-sm-6">
                                    <div class="weight-700 font-20 text-dark" id='urun_primi'></div>
                                    <div class="font-14 text-secondary weight-500">
                                      Prim
                                    </div>
                                 </div>
                              </div>
                             
                           </div>
                           <div class="widget-icon" style="background-color:rgb(146, 0, 188)">
                              <div class="icon" data-color="#fff">
                                  ₺
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                   
                  <div class="col-xl-6 col-lg-6 col-md-6 mb-20">
                     <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                           <div class="widget-data">
                              <div class="row">
                                 
                                 <div class="col-6 col-xs-6 col-sm-6">
                                    <div class="weight-700 font-20 text-dark" id='paket_satisi'></div>
                                    <div class="font-14 text-secondary weight-500">
                                      Paket Satışları
                                    </div>
                                 </div>
                                 <div class="col-6 col-xs-6 col-sm-6">
                                    <div class="weight-700 font-20 text-dark" id='paket_primi'></div>
                                    <div class="font-14 text-secondary weight-500">
                                      Prim
                                    </div>
                                 </div>
                              </div>
                             
                           </div>
                           <div class="widget-icon" style="background-color:rgb(234, 67, 242)">
                              <div class="icon">
                                  ₺
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <div class="col-xl-6 col-lg-6 col-md-6 mb-20">
                     <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                           <div class="widget-data alert alert-success" style="margin-bottom: 0;">
                              <div class="row">
                                 <div class="col-6 col-xs-6 col-sm-6">
                                    <div class="weight-700 font-20 text-dark" id='toplam_hakedis'></div>
                                    <div style="color:#343a40 ;font-weight:bold;">Prim Hakediş</div>
                                 </div>
                                 <div class="col-6 col-xs-6 col-sm-6">
                                    <div class="weight-700 font-20 text-dark"></div>
                                    <div style="color:#343a40 ;font-weight:bold;">
                                      Maaş
                                    </div>
                                 </div>
                              </div>
                                 
                             
                             
                           </div>
                           <div class="widget-icon" style="background-color: rgb(0, 109, 190) " >
                              <div class="icon" data-color="#fff">
                                 ₺
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
                <table class="data-table table stripe hover nowrap" id="adisyon_liste_personel">
                  <thead>
                    <th>Açılış Tarihi</th>
                    <th>Müşteri</th> 
                    <th>Satış Türü</th>
                    <th>Adisyon İçeriği </th>
                     <th>Toplam ₺</th>
                     <th>Ödenen ₺</th>
                     <th>Kalan ₺</th>
                     <th>Hakediş ₺</th>
                  </thead>
                  <tbody>
                   
                  </tbody>
                </table>
             
      </div>
   </div>
</div>

@endsection