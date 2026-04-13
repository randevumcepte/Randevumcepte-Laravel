@extends('layout.layout_randevual')
@section('content')
          
          
             <section class="block">
                <div class="container">

                    <div class="row">
                        <!--============ Listing Detail =============================================================-->
                        <div class="col-md-8">
                            <h2>Yeni Randevu Oluştur</h2>
                          <form id="randevuhizmetvepersonelleri" method="post">
                            {!! csrf_field() !!}
                            <input type="hidden" name="salonno" id="salonno" value="{{$salon->id}}">

                          <div class="row">
                            <div class="col-md-2" style="float: left;">
                                <div class="feature-box">
                                 <figure>
                                        
                                         
                                    </figure>
                                </div>
                            </div>
                            <div class="col-md-10" style="float: left;">
                            <h3>Hizmet Seçimi & Hizmet Ekle/Çıkar</h3>
                              <ul>
                            
                                @foreach($secilenhizmetler as $secilenhizmet)
                                    <li>
                                        <input type="hidden" name="hizmetler[]" value="{{$secilenhizmet->id}}">{{$secilenhizmet->hizmet_adi}}
                                        </li>
                                      
                                @endforeach
                               </ul>
                            <br/> 
                            <button type="button" id="randevualmodal" class="btn btn-primary small">Hizmet Ekle / Çıkar</button>  
                           </div>
                          </div>
                            <br /> <br />
                            <div class="row">
                             <div class="col-md-2">
                                 <div class="feature-box">
                                 <figure>
                                        
                                         
                                    </figure>
                                </div>

                             </div>
                             <div class="col-md-10">
                            <h3>Personel Seç</h3>
                              <div id="secilenpersonelkismi">
                                   
                                   
                                    <div id="secilenpersoneldeger" name="secilenpersoneldeger"></div>
                                    <br />
                                         
                                     <button type="button" id="personeldegistir" class="btn btn-primary small">Personel Değiştir</button>
                                       
                               </div>
                                <div id="personeltablosu" class="personeller2">

                             @foreach($secilenhizmetler as $secilenhizmet)

                                <p>{{$secilenhizmet->hizmet_adi}} için personel seçiniz</p>
                                <div class="form-group">
                                        <select name="personeller[]">
                                            <option value="0">Farketmez</option>
                                            @foreach($personelhizmetleri as $personelhizmet)
                                               @if($personelhizmet->hizmet_id == $secilenhizmet->id)
                                                <option>{{$personelhizmet->personeller->personel_adi}}</option>
                                                @endif
                                            @endforeach
                                             
                                        </select>
                                </div>
                             @endforeach
                               <button type="submit" id="tarihsaatsecimaktifet" class="btn btn-primary small">
                                 
                               Tarih ve Saat Seç</button><br/>
                                 </div>
                              </div>

                         
                              </div>
                          
                               <br /> <br />
                              <div class="row" id="randevutarihalani" tabindex="-1">
                                 <div class="col-md-2" style="float: left;">
                                    <div class="feature-box">
                                        <figure>
                                        
                                         
                                        </figure>
                                    </div>
                                 </div>
                                 <div class="col-md-10" style="float: left;">
                                     <h3>Tarih Seç</h3>
                                     <div id="secilentarihkismi">
                                         <input id="secilentarih" type="hidden">
                                         <div id="secilentarihdeger"></div>
                                         <br />
                                         <button type="button" id="tarihdegistir" class="btn btn-primary small">Tarih Değiştir</button>

                                     </div>
                                     <div id="tarihtablosu" class="tarihler">
                                      
                                     <div class="input-radio"><input type="radio" id="bugun" name="randevutarihi" value="{{date('Y-m-d')}}" checked> <label for="bugun">Bugün</label></div>
                                    <div class="input-radio"><input type="radio" id="yarin" name="randevutarihi" value="{{date('Y-m-d',strtotime('+1 days',strtotime(date('Y-m-d')))) }}"> <label for="yarin">Yarın</label></div>
                                     @for ($i = 2 ;$i <= 30; $i++)
                                          <div class="input-radio"><input  id="nextdays{{$i}}"  type="radio" name="randevutarihi" value="{{date('Y-m-d',strtotime('+'.$i.' days',strtotime(date('Y-m-d')))) }}"> <label for="nextdays{{$i}}">{{date('d.m D',strtotime('+'.$i.' days',strtotime(date('Y-m-d')))) }}</label></div>
                                     @endfor
                                     </div>
                                 </div>
                              </div>
                                 <br /> <br />
                              <div class="row" id="randevusaatalani" tabindex="-1">

                                 <div class="col-md-2" style="float: left;">
                                    <div class="feature-box">
                                        <figure>
                                        
                                         
                                        </figure>
                                    </div>
                                 </div>
                                 <div class="col-md-10" style="float: left;">
                                     <h3>Saat Seç</h3>
                                      <div id="secilensaatkismi">
                                         <input id="secilensaat" name="randevusaati" type="hidden">
                                         <div id="secilensaatdeger"></div><br />
                                        <button type="button" id="saatdegistir" class="btn btn-primary small">Saat Değiştir</button>
                                         
                                     </div>
                                     <div id="saatsecimtablosu" class="saatler">
                                      
                                     </div>
                                 </div>
                              </div>
                              
                             @if(!Auth::check())
                              <div class="row" id="kisiselbilgiler" tabindex="-1">
                            <div class="col-md-2" style="float: left;">
                                <div class="feature-box">
                                 <figure>
                                        
                                         
                                    </figure>
                                </div>
                            </div>
                            <div class="col-md-10" style="float: left;">
                                <div class="kisiselbilgialani">
                            <h3>Kişisel Bilgiler</h3>
                                  <div class="form-group">
                                        <input type="email" id="eposta" name="eposta" placeholder="E-posta adresiniz">
                                    </div>
                                   <div id="epostahata"></div>
                                   <div id="hosgeldinizbildirimalani">
                                   </div>
                                   <div id="sifrealaniregister">
                                        
                                   </div>
                                   
                                   
                                    <button type="submit" id="sifregonder" class="btn btn-primary small">Gönder</button>  


                                </div>
                           </div>
                          </div>
                          @else

                              
                             
                                    <div class="row" id="randevuonay">
                                        <button type="button" class="btn btn-info">İptal</button>
                                        <button type="button" class="btn btn-primary">Gönder</button>
                                    </div> 
                            @endif
                              </form>
                        </div>
                        <!--============ End Listing Detail =========================================================-->
                        <!--============ Sidebar ====================================================================-->
                        <div class="col-md-4">
                             <div class="col-md-12 randevuozetbaslik">
                                 <h3 style="font-size:20px">Randevu Bilgileri</h3>
                                 </div>
                                 <table class="randevuozet">
                                    <tr>
                                        <td>Salon adı : </td>
                                        <td>{{$salon->salon_adi}}</td>
                                    </tr>
                                    <tr>
                                        <td>Seçilen hizmetler : </td>
                                        <td>
                                          
                                           @foreach($secilenhizmetler as $secilenhizmet)
                                              
                                                {{$secilenhizmet->hizmet_adi}} <br />
                                             
                                             @endforeach
                                          
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>Personeller : </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Randevu tarihi : </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                          <td>Randevu saati : </td>
                                        <td></td>
                                    </tr>

                                 </table>
                             
                        </div>
                         
                       
                    </div>
                </div>
                <!--end container-->
            </section> 
           <script>
          
           </script>

          

        
           
@endsection
        