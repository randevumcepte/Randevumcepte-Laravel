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
                           <a title="Detaylı Bilgi" name="hizmeti_alan_musteriler" data-value="{{ $rapor->id }}" data-adi="{{ $rapor->hizmet_adi }}" href="javascript:void(0)" class="btn btn-info">
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

<style>
#hizmetiAlanMusterilerModal .modal-dialog{
   max-width: 1100px;
   width: 95%;
   margin: 1.5rem auto;
}
#hizmetiAlanMusterilerModal .modal-content{
   border: none;
   border-radius: 16px;
   overflow: hidden;
   box-shadow: 0 25px 60px rgba(80,30,180,0.25);
}
#hizmetiAlanMusterilerModal .ham-header{
   background: linear-gradient(135deg, #7E3FF2 0%, #B45CFF 50%, #FF6FB5 100%);
   color: #fff;
   padding: 22px 28px;
   display: flex;
   align-items: center;
   justify-content: space-between;
   gap: 16px;
}
#hizmetiAlanMusterilerModal .ham-header .ham-icon{
   width: 52px; height: 52px;
   border-radius: 14px;
   background: rgba(255,255,255,0.18);
   display:flex; align-items:center; justify-content:center;
   font-size: 24px;
   flex-shrink:0;
}
#hizmetiAlanMusterilerModal .ham-header h4{
   margin:0; font-size:20px; font-weight:700;
}
#hizmetiAlanMusterilerModal .ham-header .ham-sub{
   font-size:13px; opacity:.9; margin-top:4px; font-weight:500;
}
#hizmetiAlanMusterilerModal .ham-close{
   color:#fff; opacity:.85; background:rgba(255,255,255,.15);
   border:none; border-radius:10px; width:38px; height:38px;
   font-size:22px; line-height:1; cursor:pointer; transition:.2s;
}
#hizmetiAlanMusterilerModal .ham-close:hover{ opacity:1; background:rgba(255,255,255,.28); }
#hizmetiAlanMusterilerModal .ham-body{
   padding: 24px 28px;
   background: #faf8ff;
   max-height: 65vh;
   overflow-y: auto;
}
#hizmetiAlanMusterilerModal .ham-summary{
   display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px;
}
#hizmetiAlanMusterilerModal .ham-chip{
   background:#fff; border-radius:12px; padding:10px 16px;
   box-shadow:0 2px 8px rgba(126,63,242,0.08);
   font-size:13px; color:#5a4080; font-weight:600;
}
#hizmetiAlanMusterilerModal .ham-chip strong{ color:#7E3FF2; font-size:15px; margin-left:6px; }
#hizmetiAlanMusterilerModal .ham-table{
   width:100%; border-collapse: separate; border-spacing:0;
   background:#fff; border-radius:12px; overflow:hidden;
   box-shadow:0 4px 16px rgba(126,63,242,0.10);
}
#hizmetiAlanMusterilerModal .ham-table thead th{
   background: linear-gradient(135deg,#7E3FF2,#B45CFF);
   color:#fff; font-weight:600; font-size:13px;
   padding:14px 12px; text-align:left; border:none;
   text-transform:uppercase; letter-spacing:.4px;
}
#hizmetiAlanMusterilerModal .ham-table tbody td{
   padding:14px 12px; border-bottom:1px solid #f0eaff; font-size:14px; color:#3a2a5a;
}
#hizmetiAlanMusterilerModal .ham-table tbody tr:last-child td{ border-bottom:none; }
#hizmetiAlanMusterilerModal .ham-table tbody tr:hover td{ background:#f6f0ff; }
#hizmetiAlanMusterilerModal .ham-table .ham-musteri{ font-weight:600; color:#2a1a4a; }
#hizmetiAlanMusterilerModal .ham-table .ham-tel{ color:#6a5b8a; font-size:13px; }
#hizmetiAlanMusterilerModal .ham-table .ham-personel-badge{
   display:inline-block; padding:4px 12px; border-radius:20px;
   background:#ede4ff; color:#7E3FF2; font-size:12px; font-weight:600;
}
#hizmetiAlanMusterilerModal .ham-table .ham-amount{ font-weight:600; text-align:right; white-space:nowrap; }
#hizmetiAlanMusterilerModal .ham-table .ham-fiyat{ color:#2a1a4a; }
#hizmetiAlanMusterilerModal .ham-table .ham-odenen{ color:#1ba94c; }
#hizmetiAlanMusterilerModal .ham-table .ham-kalan{ color:#e44d6d; }
#hizmetiAlanMusterilerModal .ham-empty{
   text-align:center; padding:50px 20px; color:#9e8fc7;
}
#hizmetiAlanMusterilerModal .ham-empty .ham-empty-icon{
   font-size:48px; margin-bottom:12px; opacity:.5;
}
#hizmetiAlanMusterilerModal .ham-loading{
   text-align:center; padding:60px 20px; color:#7E3FF2;
}
#hizmetiAlanMusterilerModal .ham-spinner{
   display:inline-block; width:40px; height:40px;
   border:4px solid #ede4ff; border-top-color:#7E3FF2;
   border-radius:50%; animation: hamSpin .8s linear infinite;
}
@keyframes hamSpin { to { transform: rotate(360deg); } }
#hizmetiAlanMusterilerModal .ham-footer{
   padding:16px 28px; background:#fff;
   display:flex; justify-content:flex-end; border-top:1px solid #f0eaff;
}
#hizmetiAlanMusterilerModal .ham-footer .btn-kapat{
   background: linear-gradient(135deg,#7E3FF2,#B45CFF);
   color:#fff; border:none; padding:10px 28px; border-radius:10px;
   font-weight:600; cursor:pointer; transition:.2s;
   box-shadow:0 4px 12px rgba(126,63,242,0.3);
}
#hizmetiAlanMusterilerModal .ham-footer .btn-kapat:hover{ transform:translateY(-1px); box-shadow:0 6px 16px rgba(126,63,242,0.4); }

@media (max-width: 768px){
   #hizmetiAlanMusterilerModal .ham-header{ padding:18px; }
   #hizmetiAlanMusterilerModal .ham-body{ padding:16px; }
   #hizmetiAlanMusterilerModal .ham-table thead th,
   #hizmetiAlanMusterilerModal .ham-table tbody td{ padding:10px 8px; font-size:12px; }
}
</style>
<div class="modal fade" id="hizmetiAlanMusterilerModal" tabindex="-1" role="dialog" aria-labelledby="hizmetiAlanMusterilerModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
         <div class="ham-header">
            <div style="display:flex; align-items:center; gap:16px; flex:1; min-width:0;">
               <div class="ham-icon"><i class="dw dw-eye"></i></div>
               <div style="flex:1; min-width:0;">
                  <h4 id="hizmetiAlanMusterilerModalLabel">Hizmeti Alan Müşteriler</h4>
                  <div class="ham-sub" id="hizmetiAlanMusteriler_hizmetAdi"></div>
               </div>
            </div>
            <button type="button" class="ham-close" data-dismiss="modal" aria-label="Kapat">&times;</button>
         </div>
         <div class="ham-body">
            <div id="hizmetiAlanMusteriler_icerik">
               <div class="ham-loading">
                  <div class="ham-spinner"></div>
                  <div style="margin-top:14px; font-weight:500;">Yükleniyor...</div>
               </div>
            </div>
         </div>
         <div class="ham-footer">
            <button type="button" class="btn-kapat" data-dismiss="modal">Kapat</button>
         </div>
      </div>
   </div>
</div>

@endsection()