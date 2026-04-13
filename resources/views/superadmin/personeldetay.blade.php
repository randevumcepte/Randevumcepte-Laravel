@extends('layout.layout_sistemadmin')
@section('content')

 
      
   
     <div class="main-content container-fluid">
     
     <div class="row" style="margin-top: 30px">
      <div class="col-md-12">
                <div class="user-display">

                 
                  <div class="user-display-bottom">
                    <div class="user-display-avatar">
                      @if($personel->profil_resmi == null ||$personel->profil_resmi =='')
                      @if($personel->cinsiyet==0)
                      <img src="{{secure_asset('public/img/author0.jpg')}}" id="profilresim" alt="Profil Resmi"></div>
                      @else
                      <img src="{{secure_asset('public/img/author1.jpg')}}" id="profilresim" alt="Profil Resmi"></div>
                      @endif
                      @else
                      <img src="{{secure_asset($personel->profil_resmi)}}" id="profilresim" alt="Profil Resmi"></div>
                      @endif
                      
                        <form enctype="multipart/form-data" id="personelresimyukle_superadmin" method="post">
                         {!!csrf_field()!!}
                         <div class="single-file-input">
                                                <input type="file" id="profil_resim_superadmin" name="profil_resim">
                                                <div class="btn btn-framed btn-primary small">Resim Seç</div>
                                                 </div> 
                             </form>
                         
                    <div class="user-display-info">
                      <form id="personeladiunvani" method="GET">
                      <div class="name">
                         <div class="form-group">
                          <input type="hidden" name="personelid" id="personelid" value="{{$personel->id}}">
                          <label>Personel Adı</label>
                          <input type="text" name="personeladi" value="{{$personel->personel_adi}}" class="form-control"></div>
                         <div class="nick">
                          <div class="col-md-6">
                          <span class="mdi mdi-account"></span>
                          <label>Unvan</label>
                          <input type="text" name="unvan" value="{{$personel->unvan}}" class="form-control input-xs">
                          </div>
                          <div class="col-md-6">
                            <label>Cinsiyet</label>
                            <select name="personelcinsiyet" class="form-control input-xs">
                              @if($personel->cinsiyet==0)
                                <option selected value="0">Bayan</option>
                                <option value="1">Bay</option>
                              @else
                                 <option value="0">Bayan</option>
                                <option selected value="1">Bay</option>
                              @endif
                            </select>
                          </div>
                        </div>
                      </form>
                    </div>
                    <div class="row user-display-details">
                      <div class="col-xs-3">
                        <div class="title">Puan</div>
                        <div class="counter">0</div>
                      </div>
                      <div class="col-xs-3">
                        <div class="title">Yapılan Yorum Sayısı</div>
                        <div class="counter">0</div>
                      </div>
                      <div class="col-xs-3">
                        <div class="title">Randevu Sayısı</div>
                        <div class="counter">0</div>
                      </div>
                      <div class="col-xs-3">
                        <div class="title">Kazanç</div>
                        <div class="counter">0</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
     </div>
     <div class="row">
              <div class="col-md-6">
                <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                  Çalışma Saatleri 

                 </div>
                  <div class="panel-body">
                       <table class="table table table-striped table-hover">
                        <tbody>
                          <form id="calismasaatiguncelle_personel" method="GET">
                             
                            {!! csrf_field() !!}
                             @foreach($personelcalismasaatleri as $key => $value)
                             @if($value->haftanin_gunu == 1)
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  @if($value->calisiyor == 1)
                                  <input type="checkbox" checked id="calisiyor1" name="calisiyor1"><label for="calisiyor1">
                                    @else
                                     <input type="checkbox" id="calisiyor1" name="calisiyor1"><label for="calisiyor1">
                                    @endif
                                   
                                  </label>
                                </div>
                              </td>
                              <td>Pazartesi</td>
                              <td>

                                <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati1" style="float: left; width: 80px">   
                                <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati1"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            @endif
                            @if($value->haftanin_gunu == 2)
                            <tr>
                              <td>

                                <div class="be-checkbox be-checkbox-color inline">
                                   @if($value->calisiyor == 1)
                                  <input type="checkbox" checked id="calisiyor2" name="calisiyor2"><label for="calisiyor2">
                                    @else
                                      <input type="checkbox" id="calisiyor2" name="calisiyor2"><label for="calisiyor2">
                                    @endif
                                  </label>
                                </div>
                              </td>
                              <td>Salı</td>
                              <td>
                                
                                <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati2" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati2"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            @endif
                            @if($value->haftanin_gunu==3)
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  @if($value->calisiyor==1)
                                  <input type="checkbox" checked id="calisiyor3" name="calisiyor3"><label for="calisiyor3">
                                  @else
                                   <input type="checkbox" id="calisiyor3" name="calisiyor3"><label for="calisiyor3">
                                  @endif
                                  </label>
                                </div>
                              </td>
                              <td>Çarşamba</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati3" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati3"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            @endif
                            @if($value->haftanin_gunu==4)
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  @if($value->calisiyor==1)
                                  <input type="checkbox" checked id="calisiyor4" name="calisiyor4"><label for="calisiyor4">
                                   @else
                                   <input type="checkbox" id="calisiyor4" name="calisiyor4"><label for="calisiyor4">
                                    @endif
                                  </label>
                                </div>
                              </td>
                              <td>Perşembe</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati4" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati4"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            @endif
                           @if($value->haftanin_gunu==5)
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                   @if($value->calisiyor==1)
                                  <input type="checkbox" checked id="calisiyor5" name="calisiyor5"><label for="calisiyor5">
                                  @else
                                  <input type="checkbox" id="calisiyor5" name="calisiyor5"><label for="calisiyor5">
                                  @endif
                                  </label>
                                </div>
                              </td>
                              <td>Cuma</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati5" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati5"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            @endif
                            @if($value->haftanin_gunu==6)
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                   @if($value->calisiyor==1)
                                  <input type="checkbox" checked id="calisiyor6" name="calisiyor6"><label for="calisiyor6">
                                    @else
                                    <input type="checkbox" id="calisiyor6" name="calisiyor6"><label for="calisiyor6">
                                    @endif
                                  </label>
                                </div>
                              </td>
                              <td>Cumartesi</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati6" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati6"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            @endif
                            @if($value->haftanin_gunu==7)
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                   @if($value->calisiyor==1)
                                  <input type="checkbox" checked id="calisiyor7" name="calisiyor7"><label for="calisiyor7">
                                    @else
                                  <input type="checkbox" id="calisiyor7" name="calisiyor7"><label for="calisiyor7">
                                    @endif
                               
                                  </label>
                                </div>
                              </td>
                              <td>Pazar</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati7" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati7"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            @endif
                            @endforeach
                            @if($personelcalismasaatleri->count()==0)
                                   <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor1" name="calisiyor1"><label for="calisiyor1">
                                   
                                  </label>
                                </div>
                              </td>
                              <td>Pazartesi</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati1" style="float: left; width: 80px">   
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati1"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor2" name="calisiyor2"><label for="calisiyor2">
                                    
                                  </label>
                                </div>
                              </td>
                              <td>Salı</td>
                              <td>
                                
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati2" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati2"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor3" name="calisiyor3"><label for="calisiyor3">
                                       
                                  </label>
                                </div>
                              </td>
                              <td>Çarşamba</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati3" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati3"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor4" name="calisiyor4"><label for="calisiyor4">
                           
                                  </label>
                                </div>
                              </td>
                              <td>Perşembe</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati4" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati4"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor5" name="calisiyor5"><label for="calisiyor5">
                                  
                                  </label>
                                </div>
                              </td>
                              <td>Cuma</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati5" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati5"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor6" name="calisiyor6"><label for="calisiyor6">
                                    
                                  </label>
                                </div>
                              </td>
                              <td>Cumartesi</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati6" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati6"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor7" value="00:00" name="calisiyor7"><label for="calisiyor7">
                               
                                  </label>
                                </div>
                              </td>
                              <td>Pazar</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati7" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati7"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            @endif
                            <tr>
                            
                          </tr>
                        </form>
                        </tbody>
                       </table>

                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                  Sunduğu Hizmetler  

                 </div>
                   <div class="panel-body" style="overflow-y: auto; height: 410px">
                     <div class="widget-chart-container">
                     <table   class="table table-striped table-borderless">
                      <thead>
                        <tr>
                          <th>Hizmet</th>
                          <th>İşlemler</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>
                            <select name="hizmetler" multiple="" class="tags input-xs">
                              @foreach(\App\Hizmetler::all() as $hizmetler)
                              
                                <option value="{{$hizmetler->id}}">{{$hizmetler->hizmet_adi}}</option>
                                
                                 @endforeach
                             </select>
                          </td>
                          <td>
                            <button id="hizmetekle_personel_superadmin" class="btn btn-primary">Ekle</button>
                          </td>
                        </tr>
                      </tbody>
                      <tbody>
                        <tr>
                          <td colspan="2">
                           <div class="col-md-12">
                           
                        <input type="text" id="arananhizmet" placeholder="Listede ara..." class="form-control"><span class="mdi mdi-search form-control-feedback"></span>
                      </div>
                          </td>
                           
                        </tr>
                      </tbody>
                      <tbody id="personelsunulanhizmetler"  style="height: 400px; overflow-y: auto;">
                      
                        {!!$personelsunulanhizmetler!!}
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              


               
            </div>
            <div class="col-md-12">
               <button type="button" style="width: 100%" class="btn btn-primary" id="bilgileriguncelle_personel_superadmin">Bilgileri Güncelle</button>
             </div>
</div>
<div id="hata"></div>
     
           
      

@endsection