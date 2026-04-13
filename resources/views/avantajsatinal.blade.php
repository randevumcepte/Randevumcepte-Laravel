@extends('layout.layout_avantajsatinalma')
@section('content')
 
<section class="block">
    <div class="container">
     	<form class="form-clearfix" id="avantajsatinalma" method="get">
            <div class="row">
         	 
         	      <div class="col-12 col-sm-12 col-md-4 avantajsatinalmakolon">
         	 	  
                        <h3>Avantaj Bilgileri</h3>
         	 	        <input type="hidden" value="{{$avantaj->id}}" id="avantajid" name="avantajid">
         	 	        <img src="{{secure_asset($kapakgorsel->salon_gorseli)}}" alt="Avantaj Görseli" style="width: 100%; height:100% auto" />

         	 	        <p style="margin-top: 10px"><strong>{{$avantaj->kampanya_baslik}}</strong></p>
         	 	        <p>{{$avantaj->kampanya_aciklama}}</p>
         	 	        <table class="hizmettablo" style="font-weight: bold">
         	 	 	        <tr>
                 	 	 		<td style="padding-bottom: 8px">
                 	 	 			Birim Fiyatı : 
                 	 	 		</td>
                 	 	 		<td style="text-align: center;padding-bottom: 8px">
                 	 	 			<div class="avantajsatinalfiyat fiyatturuncurenk"> 
                 	 	 			{{$avantaj->kampanya_fiyat}} <span class="simge-tl">&#8378;</span>
                 	 	 		</div>
                 	 	 		</td>
                 	 	 	</tr>
                 	 	 	<tr>
                 	 	 		<td>
                 	 	 			Adet :
                 	 	 		</td>
                 	 	 		<td>
                 	 	 			<select class="from-control avantajsatinaladet" name="avantajadedi" id="avantajadedi" style="text-align: center;">
                 	 	 				<option selected value="1">1</option>
                 	 	 				<option value="2">2</option>
                 	 	 				<option value="3">3</option>
                 	 	 				<option value="4">4</option>
                 	 	 				<option value="5">5</option>
                 	 	 				<option value="6">6</option>
                 	 	 				<option value="7">7</option>
                 	 	 				<option value="8">8</option>
                 	 	 				<option value="9">9</option>
                 	 	 				<option value="10">10</option>
                 	 	 				<option value="11">11</option>
                 	 	 				<option value="12">12</option>
                 	 	 				<option value="13">13</option>
                 	 	 				<option value="14">14</option>
                 	 	 				<option value="15">15</option>
                 	 	 				<option value="16">16</option>
                 	 	 				<option value="17">17</option>
                 	 	 				<option value="18">18</option>
                 	 	 				<option value="19">19</option>
                 	 	 				<option value="20">20</option>
                 	 	 			</select>
                 	 	 		</td>

                 	 	 	</tr>
                 	 	 	<tr>
                 	 	 		<td style="padding-bottom: 8px">Toplam Fiyat : </td>
                 	 	 		<td  style="color:green;text-align: center;padding-bottom: 8px">
                 	 	 			<div class="avantajsatinalfiyat fiyatyesilrenk" id="avantajtoplamfiyat"> 
                 	 	 			{{$avantaj->kampanya_fiyat}} <span class="simge-tl">&#8378;</span>
                 	 	 		</div>
                 	 	 		</td>
                 	 	 	</tr>

                 	 	</table>
         	       </div>
         	       <div class="col-12 col-sm-12 col-md-4 avantajsatinalmakolon">
         	 	        <h3>Kullanıcı Bilgileri</h3>
         	 	        @if(!Auth::check())
         	 	        <div class="from-group">
         	 	  	         <label>Ad Soyad</label>
         	 	 	         <input type="text" class="from-control" name="adsoyad" id="adsoyad" required style="margin-bottom: 10px">

         	 	        </div>
         	 	        <div class="from-group">
         	 	 	         <label>Cep Telefonu (başında 0 olmadan xxxxxxxxxx şeklinde)</label>
         	 	 	         <input required style="margin-bottom: 10px" type="text" pattern="\[5]\d{9}\" name="ceptelefon" id="ceptelefon">
         	 	        </div>
         	 	        <div class="from-group">
         	 	 	        <label>E-posta</label>
         	 	 	        <input style="margin-bottom: 10px" type="email" class="from-control" name="eposta" id="eposta">
         	 	        </div>
                        <div class="from-group" id="girishata">
                        </div>

         	 	        @else
         	 	        <div class="from-group">
         	 	  	         <label>Ad Soyad</label>
         	 	 	         <input style="margin-bottom: 10px" type="text" class="from-control"  name="adsoyad" id="adsoyad" value="{{Auth::user()->name}}" placeholder="Ad Soyad" required>

         	 	        </div>
         	 	        <div class="from-group">
         	 	 	        <label>Cep Telefonu (başında 0 olmadan xxxxxxxxxx şeklinde)</label>
         	 	 	        <input style="margin-bottom: 10px" type="text" pattern="\0\d{9}\" value="{{Auth::user()->cep_telefon}}" required placeholder="Cep Telefonu (başında 0 olmadan xxxxxxxxxx şeklinde)" name="ceptelefon" id="ceptelefon">
         	 	        </div>
         	 	        <div class="from-group">
         	 	 	        <label>E-posta</label>
         	 	 	        <input style="margin-bottom: 10px" type="mail" class="from-control" placeholder="E-posta" value="{{Auth::user()->email}}" name="eposta" id="eposta">
         	 	        </div>

         	 	        @endif
         	      </div>
         	      <div class="col-12 col-sm-12 col-md-4">
         	 	       <h3>Ödeme</h3>
         	 	       <img src="{{secure_asset('/public/img/kart.png')}}" style="width: 100%; height: 100% auto" alt="Kartlar">
         	 	       <div class="from-group">
         	 	            <button type="submit" class="btn btn-primary btn-rounded avantajsatinalbutton" style="width: 100%">Kredi Kartı ile Ödeme Yap</button>
         	 	        </div>
         	 	        <div class="from-group">
         	 		       <label style="width: 100%;text-align: center;">veya</label>
         	 	        </div>
         	 	        <div class="from-group">
         	 		       <button type="submit" class="btn btn-primary btn-rounded avantajsatinalbutton" style="width: 100%">Havale ile Ödeme Yap</button>
         	 	        </div>
         	      </div>
         	 
            </div>
        </form>
        <form class="form-clearfix" id="avantajkredikartiodeme" method="get">
            <div id="satinalinanavantajdetayi" style="display: none" class="row" style="margin-top:30px">
     	  
           
     	            <div class="col-md-12" style="padding:20px;border:3px solid #FF4E00; border-radius: 10px; margin-top: 20px;text-align: center;">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                   
                                <div class="row">
                                    @foreach(\App\KartKomisyonOranlari::get() as $key=>$value)
                 
                                    <div class="col-md-3 kartlargorsel">
                                    <a name="kartmarkalar" data-value="{{$value->id}}" style="cursor: pointer;">
                                    <img src="{{$value->Kredi_Karti_Banka_Gorsel}}">
                                    <br /> 
                                    Tek Ödeme ve Taksit Seçenekleri


                                    </a>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="row">
                                    @foreach(\App\KartKomisyonOranlari::get() as $key=>$value)
                                        @if($key==0)
                                        <div class="col-md-12" id="pos{{$value->id}}" style="display: block;">
                                        @else
                                        <div class="col-md-12" id="pos{{$value->id}}" style="display: none">
                                        @endif
                                        <table class="odemesecenekleritablosu">
                                            <tr>
                                                <td style="width: 50px"></td>
                                                <td><img src="{{$value->Kredi_Karti_Banka_Gorsel}}"> </td>
                                                <td>Taksit Tutarı</td>
                                  
                                                <td>Toplam Ödeme</td>
                                            </tr>
                                            <tbody id="odemesecenekleriliste{{$value->id}}">
                                     

                                            </tbody>

                                        </table>
                                        </div>
                                    @endforeach
                                    <div class="col-md-12" id="kredikartibilgibolumu">
                            
                           
                                        <table class="hizmettablo" style="background-color: white;margin:10px 0 10px 0">
                                            <tr>
                                                <td></td>
                                                <td style="width: 350px">Satın Alınacak Avantaj</td>
                                                <td style="display: none">Birim Fiyatı</td>
                                                <td style="display: none">Adet</td>
                                                <td style="min-width: 150px">Toplam Ödeme Tutarı</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td id="avantajsatinalinan"></td>
                                                <td id="avantajsatinalinanbirimfiyat" style="display: none"></td>
                                                <td id="avantajsatinalinanadet" style="display: none"></td>
                                                <td id="avantajsatinalinantoplamfiyat"><span id="odenecektoplamtutar" class="avantajsatinalfiyat fiyatturuncurenk">0 <span class="simge-tl">&#8378;</span></td>
                                            </tr>
                                            <tr style="display: none">
                                                <td><input type="hidden" id="kuponkodu" name="kuponkodu"></td>
                                                <td><input type="hidden" id="odemeaciklama" name="odemeaciklama" value="{{$avantaj->kampanya_baslik}} {{$avantaj->kampanya_aciklama}}"></td>
                                                <td><input type="hidden" id="pos_id" name="pos_id"></td>
                                                <td><input type="hidden" id="taksit_sayisi" name="taksit_sayisi"></td>
                                                <td><input type="hidden" id="odemetoplamfiyat" name="toplam_tutar"></td>
                                            </tr>

                                        </table> 
                                        <div class="col-md-7" style="float: left;">
                            
                                            <div class="from-group">
                                                <input type="text" required name="kartno" placeholder="Kart No" class="from-control">
                                            </div>
                                            <div class="from-group">
                                                <input type="text" required name="kartadsoyad" placeholder="Kart Üzerindeki Ad Soyad" class="from-control">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12" style="text-align: left;">
                                                    <label>Kart Son Kullanma Tarihi ve Güvenlik Kodu(CVC)</label>
                                                </div>
                                                <div class="col-4 col-sm-4">
                                                    <div class="from-group">
                                                        <label>Ay</label>
                                                        <select required name="kartay" class="from-control">
                                                            <option value="01">01</option>
                                                            <option value="02">02</option>
                                                            <option value="03">03</option>
                                                            <option value="04">04</option>
                                                            <option value="05">05</option>
                                                            <option value="06">06</option>
                                                            <option value="07">07</option>
                                                            <option value="08">08</option>
                                                            <option value="09">09</option>
                                                            <option value="10">10</option>
                                                            <option value="11">11</option>
                                                            <option value="12">12</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-4 col-sm-4">
                                                    <div class="from-group">
                                                        <label>Yıl</label>
                                                        <select required name="kartyil" class="from-control">
                                                            <option value="2019">2019</option>
                                                            <option value="2020">2020</option>
                                                            <option value="2021">2021</option>
                                                            <option value="2022">2022</option>
                                                            <option value="2023">2023</option>
                                                            <option value="2024">2024</option>
                                                            <option value="2025">2025</option>
                                                            <option value="2026">2026</option>
                                                            <option value="2027">2027</option>
                                                            <option value="2028">2028</option>
                                                            <option value="2029">2029</option>
                                                            <option value="2030">2030</option>
                                                            <option value="2031">2031</option>
                                                            <option value="2032">2032</option>
                                                            <option value="2033">2033</option>
                                                            <option value="2034">2034</option>
                                                            <option value="2035">2035</option>
                                                            <option value="2036">2036</option>
                                                            <option value="2037">2037</option>
                                                            <option value="2038">2038</option>
                                                            <option value="2039">2039</option>
                                                            <option value="2040">2040</option>
                                                            <option value="2041">2041</option>
                                                            <option value="2042">2042</option>
                                                            <option value="2043">2043</option>

                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-4 col-sm-4">
                                                    <div class="from-group">
                                                        <label>CVC</label>
                                                        <input required type="text" name="kartcvc" placeholder="CVC" class="from-control withzeromargin">
                                                    </div>
                                                </div>
                                            </div>
                      
                                        </div>
                                        <div class="col-md-5" style="float: left;">
                                            <img src="{{secure_asset('/public/img/3dsecure.jpg')}}" style="width: 100%; height100% auto">
                                        </div>
                   
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row justify-content-center">
                                            <div class="from-group" style="margin:10px 0 10px 0">
                                                    <button type="submit" id="avantajkartodemeyap" class="btn btn-primary btn-rounded" style="font-size:20px;width: 300px"> ÖDEME YAP</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">

                                        <div class="from-group" id="3dekran">
                          
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div> 
               
                        <div class="row">


                        </div>
              
     	            </div>
                 
            </div> 
     	 </form>
     
      <div class="row">
		<div id="hata"></div>
	  </div>
     </div>
     

</section>
 
@endsection