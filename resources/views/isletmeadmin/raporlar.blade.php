@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="page-header">
   <div class="row">
      <div class="col-md-12 col-sm-12">
         <div class="title">
            <h1 style="font-size:20px">{{$sayfa_baslik}}</h1>
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
      
   </div>
</div>
<div class="card-box mb-30">
   
   <div class="pb-20" style="padding-top:20px">

     
      <ul class=" nav nav-tabs element" role="tablist">
         <li class="nav-item" style="margin-left: 20px;">
            <button 
               class="btn btn-outline-primary active"
               data-toggle="tab"
               href="#hizmet_raporlari"
               role="tab"
               aria-selected="false" 
               style="width: 160px;" 
               >
             Hizmet Raporları
            </button>
         </li>
         <li class="nav-item" style="margin-left: 20px;">
            <button 
               class="btn btn-outline-primary"
               data-toggle="tab"
               href="#urun_raporlari"
               role="tab"
               aria-selected="false" 
               style="width: 160px;" 
               >
             Ürün Raporları
            </button>
         </li>
         <li class="nav-item" style="margin-left: 20px;">
            <button 
               class="btn btn-outline-primary"
               data-toggle="tab"
               href="#paket_raporlari"
               role="tab"
               aria-selected="false" 
               style="width: 160px;" 
               >
             Paket Raporları
            </button>
         </li>
         <li class="nav-item" style="margin-left: 20px;display: inline-block; ">
            <button 
               class="btn btn-outline-primary"
               data-toggle="tab"
               href="#personel_raporlari"
               role="tab"
               aria-selected="false" 
               style="width: 160px;" 
               >
             Personel Raporları
         </li>
         
      </ul>
      <div class="tab-content" style="padding: 0 30px 0 30px;">
         <div class="tab-pane fade show active" id="hizmet_raporlari" role="tab-panel" style="margin-top: 20px;">
             <div class="form-group row" style="margin-bottom:32px">
                
                 
                <div class="col-sm-3 col-xs-12 col-12">
                <label>Zaman Aralığı</label>
                <select class="form-control" id="hizmet_rapor_zamana_gore_filtre" >
                   
                    <option  value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
                    <option value="{{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}}">Dün</option>
                    <option selected value="<?php  echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
                    <option value="<?php  echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
                    <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
                    <option value="<?php echo date(date('Y',strtotime('-1 year')).'-01-01') . " / ". date(date('Y',strtotime('-1 year')).'-12-31'); ?>">Geçen yıl</option>
                    <option value="ozel">Özel</option>
                  </select>
                </div>
                <div class="col-sm-3  col-xs-6 col-6"  id="hizmet_rapor_ozel_tarih_filtresi_1" style="display:none;"> 
                   <label>Başlangıç Tarihi</label>
                    <input
                      class="form-control"
                      placeholder="Başlangıç Tarihini seçiniz.."
                      type="text" id="hizmet_rapor_baslangic_tarihi" 
                    />
                 </div>
                  <div class="col-sm-3 col-xs-6 col-6"  id="hizmet_rapor_ozel_tarih_filtresi_2" style="display:none;">
                    <label>Başlangıç Tarihi</label>
                    <input
                      class="form-control"
                      placeholder="Bitiş tarihini seçiniz.."
                      type="text" id="hizmet_rapor_bitis_tarihi"
                    />
                 </div>
                 <div class="col-sm-3  col-xs-6 col-6" > 
                     <label>Personele Göre Filtrele</label>
                     <select class="form-control personel_secimi" id='hizmetRaporPersonelFiltre' style="width:100%">
                        <option></option>
                     </select>
                  </div>
            </div>
            
               <div class="row">
                  <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-6 mb-20">
                     <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                           <div class="widget-data">
                              <div class="row">
                                 
                                 <div class="col-12 col-xs-12 col-sm-12">
                                    <div class="weight-700 font-20 text-dark" id="hizmetGeliri">{{number_format($hizmetRaporlari->sum('toplamTutarNumeric'),2,',','.')}}</div>
                                    <div class="font-14 text-secondary weight-500">
                                      Toplam Hizmet Geliri
                                    </div>
                                 </div>
                                 
                              </div>

                             
                           </div>
                           <div class="widget-icon" style="background-color: rgb(70, 203, 218);">
                              <div class="icon" data-color="#fff" style="color: rgb(255, 255, 255);">
                                 ₺
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-6 mb-20">
                     <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                           <div class="widget-data">
                              <div class="row">
                                 
                                 <div class="col-12 col-xs-12 col-sm-12">
                                    <div class="weight-700 font-20 text-dark" id="hizmetKazanci">{{number_format($hizmetRaporlari->sum('toplamKazancNumeric'),2,',','.')}}</div>
                                    <div class="font-14 text-secondary weight-500">
                                     Toplam Kazanç
                                    </div>
                                 </div> 
                                
                              </div>
                             
                           </div>
                           <div class="widget-icon" style="background-color:rgb(146, 0, 188)">
                              <div class="icon" data-color="#fff" style="color: rgb(255, 255, 255);">
                                  ₺
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                   
                   <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-6 mb-20">
                     <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                           <div class="widget-data">
                              <div class="row">
                                 
                                 <div class="col-12 col-xs-12 col-sm-12">
                                    <div class="weight-700 font-20 text-dark" id="hizmetBorc">{{number_format($hizmetRaporlari->sum('borcNumeric'),2,',','.')}}</div>
                                    <div class="font-14 text-secondary weight-500">
                                      Kalan Alacak
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
                  
               
               </div>
            
            <table class="data-table table stripe hover nowrap" id="hizmet_rapor_tablo">
               <thead>
                  
                  <th>Hizmet</th>
                 
                  <th>Toplam Verilen Hizmet Sayısı</th>
                  <th>Hizmet Geliri ₺</th>
                  <th>Toplam Kazanç ₺ </th>
                  <th>Kalan Alacak ₺ </th>
                  <th>İşlemler </th>
               
               </thead>
               <tbody>
               @foreach($hizmetRaporlari as $rapor)
                   <tr>
                     
                       <td>{{ $rapor->hizmet_adi }}</td>
                       <td>{{ $rapor->adet }}</td>
                       <td>{{ $rapor->toplam_tutar }}</td>
                       <td>{{ $rapor->toplamKazanc }}</td>
                       <td>{{ $rapor->borc  }}</td>
                       <td>
                           <a title="Detaylı Bilgi" href='' class='btn btn-info'>
                               <i class='dw dw-eye'></i>
                           </a>
                       </td>
                   </tr>
               @endforeach
               </tbody>
            </table>
         </div>
         <div class="tab-pane fade show" id="personel_raporlari" role="tab-panel" style="margin-top: 20px;">
             <div class="form-group row" style="margin-bottom:32px">
                
                 
                <div class="col-sm-3 col-xs-12 col-12">
                <label>Zaman Aralığı</label>
                 <select class="form-control" id="personel_rapor_zamana_gore_filtre" >
                   
                    <option  value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
                    <option value="{{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}}">Dün</option>
                    <option selected value="<?php  echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
                    <option value="<?php  echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
                    <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
                    <option value="<?php echo date(date('Y',strtotime('-1 year')).'-01-01') . " / ". date(date('Y',strtotime('-1 year')).'-12-31'); ?>">Geçen yıl</option>
                    <option value="ozel">Özel</option>
                  </select>
                </div>
                <div class="col-sm-3  col-xs-6 col-6"  id="personel_rapor_ozel_tarih_filtresi_1" style="display:none;">
                   <label>Başlangıç Tarihi</label>
                    <input
                      class="form-control"
                      placeholder="Başlangıç Tarihini seçiniz.."
                      type="text" id="personel_rapor_baslangic_tarihi" 
                    />
                 </div>
                  <div class="col-sm-3 col-xs-6 col-6"  id="personel_rapor_ozel_tarih_filtresi_2" style="display:none;">
                    <label>Başlangıç Tarihi</label>
                    <input
                      class="form-control"
                      placeholder="Bitiş tarihini seçiniz.."
                      type="text" id="personel_rapor_bitis_tarihi"
                    />
                 </div>

            </div>
            <table class="data-table table stripe hover nowrap" id="personel_rapor_tablo">
               <thead>
                  
                  <th>Personel Adı</th>
                 
                  <th>Hizmet Geliri ₺</th>
                  <th>Hizmet Primi ₺</th>
                  <th>Ürün Geliri ₺ </th>
                  <th>Ürün Primi ₺ </th>
                  <th>Paket Geliri ₺ </th>
                  <th>Paket Paket Primi ₺ </th>
               
               </thead>
               <tbody>
                @foreach($personelRaporlari as $rapor)
                   <tr>
                     
                       <td>{{ $rapor['personel_adi'] }}</td>
                       <td>{{ number_format($rapor['hizmet_geliri'],2,',','.') }}</td>
                       <td>{{ number_format($rapor['hizmet_primi'],2,',','.') }}</td>
                       <td>{{ number_format($rapor['urun_geliri'],2,',','.') }}</td>
                       <td>{{ number_format($rapor['urun_primi'],2,',','.') }}</td>
                       <td>{{ number_format($rapor['paket_geliri'],2,',','.') }}</td>
                        <td>{{ number_format($rapor['paket_primi'],2,',','.') }}</td>
                   </tr>
               @endforeach
               </tbody>
            </table>
         </div>
         <div class="tab-pane fade show" id="urun_raporlari" role="tab-panel" style="margin-top: 20px;">

         

             <div class="form-group row" style="margin-bottom:32px">
                
                 
                <div class="col-sm-3 col-xs-12 col-12">
                <label>Zaman Aralığı</label>
                   <select class="form-control" id="urun_rapor_zamana_gore_filtre" >
                   
                    <option  value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
                    <option value="{{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}}">Dün</option>
                    <option selected value="<?php  echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
                    <option value="<?php  echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
                    <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
                    <option value="<?php echo date(date('Y',strtotime('-1 year')).'-01-01') . " / ". date(date('Y',strtotime('-1 year')).'-12-31'); ?>">Geçen yıl</option>
                    <option value="ozel">Özel</option>
                  </select>
                </div>
                <div class="col-sm-3  col-xs-6 col-6"  id="urun_rapor_ozel_tarih_filtresi_1" style="display:none;">
                   <label>Başlangıç Tarihi</label>
                    <input
                      class="form-control"
                      placeholder="Başlangıç Tarihini seçiniz.."
                      type="text" id="urun_rapor_baslangic_tarihi" 
                    />
                 </div>
                  <div class="col-sm-3 col-xs-6 col-6" id="urun_rapor_ozel_tarih_filtresi_2" style="display:none;">
                    <label>Başlangıç Tarihi</label>
                    <input
                      class="form-control"
                      placeholder="Bitiş tarihini seçiniz.."
                      type="text" id="urun_rapor_bitis_tarihi"
                    />
                 </div>
                  <div class="col-sm-3  col-xs-6 col-6" > 
                     <label>Personele Göre Filtrele</label>
                     <select class="form-control personel_secimi" id='urunRaporPersonelFiltre' style="width:100%">
                        <option></option>
                     </select>
                  </div>
            </div>

                <div class="row">
                  <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-6 mb-20">
                     <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                           <div class="widget-data">
                              <div class="row">
                                 
                                 <div class="col-12 col-xs-12 col-sm-12">
                                    <div class="weight-700 font-20 text-dark" id="urunGeliri">{{number_format($urunRaporlari->sum('toplamTutarNumeric'),2,',','.')}}</div>
                                    <div class="font-14 text-secondary weight-500">
                                      Toplam Hizmet Geliri
                                    </div>
                                 </div>
                                 
                              </div>

                             
                           </div>
                           <div class="widget-icon" style="background-color: rgb(70, 203, 218);">
                              <div class="icon" data-color="#fff" style="color: rgb(255, 255, 255);">
                                 ₺
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-6 mb-20">
                     <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                           <div class="widget-data">
                              <div class="row">
                                 
                                 <div class="col-12 col-xs-12 col-sm-12">
                                    <div class="weight-700 font-20 text-dark" id="urunKazanci">{{number_format($urunRaporlari->sum('toplamKazancNumeric'),2,',','.')}}</div>
                                    <div class="font-14 text-secondary weight-500">
                                     Toplam Kazanç
                                    </div>
                                 </div> 
                                
                              </div>
                             
                           </div>
                           <div class="widget-icon" style="background-color:rgb(146, 0, 188)">
                              <div class="icon" data-color="#fff" style="color: rgb(255, 255, 255);">
                                  ₺
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                   
                   <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-6 mb-20">
                     <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                           <div class="widget-data">
                              <div class="row">
                                 
                                 <div class="col-12 col-xs-12 col-sm-12">
                                    <div class="weight-700 font-20 text-dark" id="urunBorc">{{number_format($urunRaporlari->sum('borcNumeric'),2,',','.')}}</div>
                                    <div class="font-14 text-secondary weight-500">
                                      Kalan Alacak
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
                  
               
               </div>


             <table class="data-table table stripe hover nowrap" id="urun_rapor_tablo">
               <thead>
                  
                  <th>Ürün</th>
                 
                  <th>Adet</th>
                  <th>Ürün Geliri ₺</th>
                  <th>Toplam Kazanç ₺ </th>
                  <th>Kalan Alacak ₺ </th>
                  <th>İşlemler </th>
               
               </thead>
               <tbody>
                @foreach($urunRaporlari as $rapor)
                   <tr>
                     
                       <td>{{ $rapor->urun_adi }}</td>
                        <td>{{ $rapor->adet }}</td>
                       <td>{{ $rapor->toplam_tutar }}</td>
                       <td>{{ $rapor->toplamKazanc }}</td>
                       <td>{{ $rapor->borc  }}</td>
                       <td>
                           <a title="Detaylı Bilgi" href='' class='btn btn-info'>
                               <i class='dw dw-eye'></i>
                           </a>
                       </td>
                   </tr>
               @endforeach
               </tbody>
            </table>
         </div>
         <div class="tab-pane fade show" id="paket_raporlari" role="tab-panel" style="margin-top: 20px;">
             <div class="form-group row" style="margin-bottom:32px">
                
                 
                <div class="col-sm-3 col-xs-12 col-12">
                <label>Zaman Aralığı</label>
                  <select class="form-control" id="paket_rapor_zamana_gore_filtre" >
                  
                    <option  value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
                    <option value="{{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}}">Dün</option>
                    <option selected value="<?php  echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
                    <option value="<?php  echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
                    <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
                    <option value="<?php echo date(date('Y',strtotime('-1 year')).'-01-01') . " / ". date(date('Y',strtotime('-1 year')).'-12-31'); ?>">Geçen yıl</option>
                    <option value="ozel">Özel</option>
                  </select>
                </div>
                <div class="col-sm-3  col-xs-6 col-6"  id="paket_rapor_ozel_tarih_filtresi_1" style="display:none;">
                   <label>Başlangıç Tarihi</label>
                    <input
                      class="form-control"
                      placeholder="Başlangıç Tarihini seçiniz.."
                      type="text" id="paket_rapor_baslangic_tarihi" 
                    />
                 </div>
                  <div class="col-sm-3 col-xs-6 col-6"  id="paket_rapor_ozel_tarih_filtresi_2" style="display:none;">
                    <label>Başlangıç Tarihi</label>
                    <input
                      class="form-control"
                      placeholder="Bitiş tarihini seçiniz.."
                      type="text" id="paket_rapor_bitis_tarihi"
                    />
                 </div>
                  <div class="col-sm-3  col-xs-6 col-6" > 
                     <label>Personele Göre Filtrele</label>
                     <select class="form-control personel_secimi" id='paketRaporPersonelFiltre' style="width:100%">
                        <option></option>
                     </select>
                  </div>
            </div>

                <div class="row">
                  <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-6 mb-20">
                     <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                           <div class="widget-data">
                              <div class="row">
                                 
                                 <div class="col-12 col-xs-12 col-sm-12">
                                    <div class="weight-700 font-20 text-dark" id="paketGeliri">{{number_format($paketRaporlari->sum('toplamTutarNumeric'),2,',','.')}}</div>
                                    <div class="font-14 text-secondary weight-500">
                                      Toplam Hizmet Geliri
                                    </div>
                                 </div>
                                 
                              </div>

                             
                           </div>
                           <div class="widget-icon" style="background-color: rgb(70, 203, 218);">
                              <div class="icon" data-color="#fff" style="color: rgb(255, 255, 255);">
                                 ₺
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-6 mb-20">
                     <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                           <div class="widget-data">
                              <div class="row">
                                 
                                 <div class="col-12 col-xs-12 col-sm-12">
                                    <div class="weight-700 font-20 text-dark" id="paketKazanci">{{number_format($paketRaporlari->sum('toplamKazancNumeric'),2,',','.')}}</div>
                                    <div class="font-14 text-secondary weight-500">
                                     Toplam Kazanç
                                    </div>
                                 </div> 
                                
                              </div>
                             
                           </div>
                           <div class="widget-icon" style="background-color:rgb(146, 0, 188)">
                              <div class="icon" data-color="#fff" style="color: rgb(255, 255, 255);">
                                  ₺
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                   
                   <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-6 mb-20">
                     <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                           <div class="widget-data">
                              <div class="row">
                                 
                                 <div class="col-12 col-xs-12 col-sm-12">
                                    <div class="weight-700 font-20 text-dark" id="paketBorc">{{number_format($paketRaporlari->sum('borcNumeric'),2,',','.')}}</div>
                                    <div class="font-14 text-secondary weight-500">
                                      Kalan Alacak
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
                  
               
               </div>

             <table class="data-table table stripe hover nowrap" id="paket_rapor_tablo">
               <thead>
                  
                  <th>Paket</th>
                 
                  <th>Adet</th>
                  <th>Paket Geliri ₺</th>
                  <th>Toplam Kazanç ₺ </th>
                  <th>Kalan Alacak ₺ </th>
                  <th>İşlemler </th>
               
               </thead>
               <tbody>
                @foreach($paketRaporlari as $rapor)
                   <tr>
                     
                       <td>{{ $rapor->paket_adi }}</td>
                        <td>{{ $rapor->adet }}</td>
                       <td>{{ $rapor->toplam_tutar }}</td>
                       <td>{{ $rapor->toplamKazanc }}</td>
                       <td>{{ $rapor->borc  }}</td>
                       <td>
                           <a title="Detaylı Bilgi" href='' class='btn btn-info'>
                               <i class='dw dw-eye'></i>
                           </a>
                       </td>
                   </tr>
               @endforeach

               </tbody>
               </table>
         </div>
        
         
      </div>
   </div>
</div>

@endsection()