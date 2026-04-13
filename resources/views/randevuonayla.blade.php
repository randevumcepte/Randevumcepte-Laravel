@extends('layout.layout_randevuonayla')
@section('content')
          
          
             <section class="block">
                <div class="container">

                    <div class="row">
                        <!--============ Listing Detail =============================================================-->
                        <div class="col-md-12" style="border-radius: 4px;border: 1px solid #e4e4e2">
                            <div class="col-md-12 randevuozetbaslik">
                                 <h3 style="font-size:20px">Yeni Randevu Onayı</h3>
                                 </div>
                                 <form id="randevuonayformu" method="POST">
                                     {!! csrf_field() !!}
                                    
                                 <table class="randevuozet" style="font-size:15px">
                                    <tr>
                                        <td style="width: 170px">Salon adı : </td>

                                        <td>
                                            <div class="col-md-12">
                                            <input type="hidden" name="salonno" value="{{$salon->id}}">{{$salon->salon_adi}}
                                        </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Seçilen hizmetler : </td>
                                        <td>
                                          <div class="col-md-12"> 
                                            @foreach($secilenhizmetler as $key => $value)
                                                 <input type="hidden" name="hizmetler[]" value="{{$value->id}}">
                                                {{$value->hizmet_adi}}
                                               @if($key+1 != $secilenhizmetler->count())
                                                    ,&nbsp;
                                                @endif
                                             
                                             @endforeach
                                          </div>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>Personeller : </td>
                                        <td>
                                              <?php $personelparametre = explode('_',$personelparametre); ?>
                                               @foreach($personelparametre as $personelparametre1)
                                                    @if($personelparametre1 != null || $personelparametre1 != '')
                                                      <div class="col-md-2" style="float: left;">
                                                     <input type="hidden" name="personeller[]" value="{{\App\Personeller::where('id',$personelparametre1)->value('id')}}">
                                                        <div class="author small" style="position: relative;">
                                                         <div class="author-image" style="float: none">
                                                            <div class="background-image">
                                                                 @if(\App\Personeller::where('id',$personelparametre1)->value('profil_resmi') == '' ||\App\Personeller::where('id',$personelparametre1)->value('profil_resmi') == null)
                                                                    @if(\App\Personeller::where('id',$personelparametre1)->value('cinsiyet')==0)
                                                                      <img src="{{secure_asset('public/img/author0.jpg')}}" alt="Profil Resmi" />
                                                                    @else
                                                                        <img src="{{secure_asset('public/img/author1.jpg')}}" alt="Profil Resmi" />
                                                                    @endif
                                                                 @else
                                                                      <img src="{{secure_asset(\App\Personeller::where('id',$personelparametre1)->value('profil_resmi'))}}" alt="Profil Resmi" />
                                                                @endif
                                                            </div></div></div>
                                                        {{\App\Personeller::where('id',$personelparametre1)->value('personel_adi')}} <br />
                                                @endif
                                                </div>
                                             @endforeach
                                            

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Randevu tarihi : </td><td>
                                            <div class="col-md-12">
                                             <input type="hidden" name="randevutarihi" value="{{$randevutarihi}}"></div>
                                         {{$randevutarihi}}</td>
                                     
                                        <td></td>
                                    </tr>
                                    <tr>
                                          <td>Randevu saati :</td><td>
                                            <div class="col-md-12">
                                         <input type="hidden" name="randevusaati" value="{{str_replace('_',':',$randevusaati)}}">
                                           {{$randevusaati}}
                                       </div>
                                       </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Randevu aldığınızda kullanım ve gizlilik koşullarını kabul etmiş sayılırsınız</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Yukarıda detayları listelenen randevunuzu onaylamak istiyor musunuz? &nbsp;    <button type="button" id="randevuonaylabutton" class="btn btn-success">Evet</button>
                                            <button class="btn btn-danger">Hayır</button>
                                        
                                        </td>
                                        
                                    </tr>

                                 </table>
                                   <div id="randevuonaybildirim"> </div>
                             </form>
                           
                                 

                            
                        </div>
                        <!--============ End Listing Detail =========================================================-->
                        <!--============ Sidebar ====================================================================-->
                       <!-- <div class="col-md-4">
                             
                             
                        </div>-->
                         
                       
                    </div>
                </div>
                <!--end container-->
            </section> 
           <script>
          
           </script>

          

        
           
@endsection
        