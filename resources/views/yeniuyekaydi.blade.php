@extends('layout.layoutpages')
@section('content')
 <section class="block">
            <div class="container" style="text-align: justify;">
            	 <div class="row">
            	 	<div class="col-md-6">
            	 		<h2>İşletme Bilgileri</h2>
                              <div class="form-group">
                                    <input type="text" class="form-group" placeholder="İşletme Adı & Unvanı">

                              </div>
                               <div class="form-group">
                                    <input type="text" class="form-group" placeholder="İşletme Adresi">
                                    
                              </div>
                              <div class="form-group">
                                    <label>İşletme Türü</label>
                                    <select class="form-group">
                                          <option selected>Kuaförler</option>
                                          <option>Güzellik Merkezi</option>
                                          <option>Lazer Epilasyon</option>
                                          <option>Tırnak Center</option>
                                          <option>Erkek Kuaförü</option>
                                    </select>
                                    </div>
                                    <div class="form-group">
                                    <label>Üyelik Türü</label>
                                    <select class="form-group">
                                          <option>Avantajlı Randevu Üyeliği</option>
                                          <option>Avantajlı Kampanya Üyeliği</option>
                                          <option  selected>Full Paket (Avantajlı Kampanya & Randevu Üyeliği)</option>
                                         
                                    </select>
                              </div>
                              <div class="form-group">
                                    <input type="text" class="form-group" placeholder="Yetkili E-posta">
                                    
                              </div>
                              <div class="form-group">
                                    <input type="text" class="form-group" placeholder="Yetkili Ad Soyad">
                                    
                              </div>
                              <div class="form-group">
                                    <input type="text" class="form-group" placeholder="Yetkili E-posta">
                                    
                              </div>
                              
            	 		 
            	 	</div>
            	 	<div class="col-md-6">
            	 		 <h2>Ödeme Bilgileri</h2>
                               <p><strong>Toplam Ödeme Tutarı : </strong> <span style="background-color: #ff4e00;color:white;padding:10px;font-size: 15px;border-radius: 3px"> 1180 TL</p>
                               <div class="form-group">
                                    <input type="text" class="form-group" placeholder="Kart Üzerindeki Ad Soyad">
                               </div>
                                <div class="form-group">
                                    <input type="text" class="form-group" placeholder="Kart Numarası">
                               </div>
                               <div class="form-group">
                                    <label>Kartınızın son kullanma tarihi</label>
                                    <div class="row">
                                    <div class="col-md-6">
                                          <input type="text" class="form-group" placeholder="Ay">
                                    </div>
                                    <div class="col-md-6">
                                          <input type="text" class="form-group" placeholder="Yıl">
                                    </div></div>
                               </div>
                                <div class="form-group">
                                    <input type="text" class="form-group" placeholder="Güvenlik Kodu">
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary btn-rounded">Ödeme Yap ve Üyeliğimi Oluştur</button>
                                </div>
                                
            	 	</div>
            	 </div>
 			</div>
 </section>
@endsection