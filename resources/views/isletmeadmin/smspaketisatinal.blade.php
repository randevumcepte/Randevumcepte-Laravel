@extends('layout.layout_isletmeadminpaketornek')
@section('content')
	<div class="main-content container-fluid">
          <h1 class="display-heading text-center">SMS Paketi Satın Alma</h1>
         <div class="row">
         	<div class="col-xs-12 col-sm-12 col-md-12">
                <div class="panel panel-default panel-table">
                 
                  
                  <div class="panel-heading panel-heading-divider xs-pb-15" style="font-weight: bold;">Satın Alınacak SMS Paketi
                   
                </div>
                <div class="panel-body table-responsive">
                  <table class="table table-striped table-borderless">
                    <thead style="font-size:18px">
                      <tr>
                      	<th>Paket</th>
                        <th>Kullanım Süresi</th>
                        <th>Tutar</th>
                        <th>İşlemler(Lütfen ödeme yöntemini seçiniz)</th>
                         
                         </tr>
                    </thead>
                    <tbody class="no-border-x" style="font-size:15px">
                     
                      <tr>
                       	<td>@if($paket==1) 2500 SMS @elseif($paket==2)5000 SMS @elseif($paket==3) 10000 SMS @elseif($paket==4) 20000 SMS @endif</td>
                       	<td>Sınırsız</td>
                       	<td>
                       		@if($paket==1) 80 <span class="simge-tl">&#8378;</span>
                       		@elseif($paket==2) 99 <span class="simge-tl">&#8378;</span>
                       		@elseif($paket==3) 150 <span class="simge-tl">&#8378;</span>
                       		@elseif($paket==4) 250 <span class="simge-tl">&#8378;</span>@endif
                       	</td>
                        <td> <button id="smskredikartiodeme" style="font-size: 15px" class="btn btn-primary" >Kredi Kartı ile Öde</button> <button id="smshavaleodeme" style="font-size: 15px" class="btn btn-primary" >Havale/EFT ile Öde</button></td>
                      </tr>
                       
                      
                    </tbody>
                  </table>
                </div>
              </div></div>
         	<div class="col-xs-12 col-sm-12 col-md-12" id="smsodemebilgileri" style="display: none">
          	 <div class="panel panel-default panel-table">
                 
                  
                  <div class="panel-heading panel-heading-divider xs-pb-15" style="font-weight: bold;">Ödeme Bilgileri
                   
                </div>
                <div class="panel-body table-responsive">
                	<img src="{{secure_asset('/public/img/ornekpos.jpg')}}" >
                </div>
            </div>
          	

          </div>
         	<div class="col-xs-12 col-sm-12 col-md-12" id="smsodemebilgileri_havale" style="display: none">
          	 <div class="panel panel-default">
                 
                  
                  <div class="panel-heading panel-heading-divider xs-pb-15" style="font-weight: bold;">Banka Bilgilerimiz
                   
                </div>
                <div class="panel-body table-responsive">
                	<div class="form-group">
                	 <label><strong>Aşağıdaki bankalardan havale ve EFT yapabilirsiniz.</strong></label>
                	</div>
                	 <table class="table table-striped table-borderless">
                    <thead>
                      <tr>
                      	<th>Banka</th>
                        <th>Hesap Sahibi</th>
                        <th>Şube</th>
                        <th>Hesap No</th>
                        <th>IBAN</th>
                        <th>İşlemler</th>
                       </tr>
                    </thead>
                    <tbody class="no-border-x">
                     
                      <tr>
                       	<td>GARANTİ BANKASI</td>
                       	<td>WEBFİRMAM İNTERNET HİZ.REK.SAN.TİC.LTD.ŞTİ.</td>
                       	<td>
                       		 1100   
                       	</td>
                       	<td>6298737</td>
                       	<td>TR070006200110000006298737</td>
                        <td> <button id="havaleodemebildirimigaranti" class="btn btn-success" >Ödeme Bildirimi Gönder</button> </td>
                      </tr>
                      <tr>
                      	
                      		<td>İŞ BANKASI</td>
                       	<td>WEBFİRMAM İNTERNET HİZ.REK.SAN.TİC.LTD.ŞTİ.</td>
                       	<td>
                       		 3430     
                       	</td>
                       	<td>0829529</td>
                       	<td>TR880006400000134300829529</td>
                        <td> <button id="havaleodemebildirimiisbank" class="btn btn-success" >Ödeme Bildirimi Gönder</button> </td>
                      </tr>
                       
                      
                    </tbody>
                  </table>
					 
                </div>
            </div>
          	

          </div>
          </div>
          
      </div>
    </div>
@endsection