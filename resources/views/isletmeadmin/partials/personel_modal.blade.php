{{-- ================= Personel Modal (Ekle / Düzenle) ================= --}}
<div id="personel-modal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dialog-centered" style="max-width:850px;width:92%;">
      <div class="modal-content" style="width:100%;">
         <form id="yenipersonelbilgiekle" method="POST">
            {!!csrf_field()!!}
            <input type="hidden" name="personel_id" id='personel_id'>
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            <div class="modal-header">
               <h2 class="modal_baslik"></h2>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-md-4">
                     <div class="form-group">
                        <label>Personel Adı</label>
                        <input id="personel_adi" name="personel_adi" required class="form-control">
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="form-group">
                        <label>Cinsiyet</label>
                        <select id="cinsiyet" name="cinsiyet" class="form-control">
                           <option value="0">Kadın</option>
                           <option value="1">Erkek</option>
                        </select>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="form-group">
                        <label>Cep Telefon</label>
                        <input type="tel" name='cep_telefon' id='cep_telefon' data-inputmask =" 'mask' : '5999999999'" required class="form-control">
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Unvan</label>
                        <input class="form-control" id='unvan' name="unvan" type="text">
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Hesap Türü</label>
                        <select class="form-control" name="sistem_yetki" id="sistem_yetki">
                           <option disabled value="Hesap Sahibi">Hesap Sahibi</option>
                           @foreach($roller as $key => $rol)
                              @if($key != 0 && $key != 5)
                              <option value="{{$rol->name}}">{{$rol->name}}</option>
                              @endif
                           @endforeach
                        </select>
                     </div>
                  </div>
               </div>
               {{-- ================= TANITIM SAYFASI ALANLARI ================= --}}
               <div class="row">
                  <div class="col-md-12" style="margin-top:10px; border-top:1px solid #e2e2e2; padding-top:10px;">
                     <h3 style="font-size: 15px; font-weight: bold; margin-bottom:2px;"><i class="fa fa-id-card-o" style="color:#007bff;"></i> Tanıtım Sayfası Bilgileri <small style="color:#888; font-weight:normal;">(opsiyonel)</small></h3>
                     <p style="color:#888; font-size:12px; margin:0 0 10px">Bu bilgiler işletmenizin online tanıtım sayfasında personel kartlarında müşterilere gösterilir.</p>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Uzmanlık Alanı</label>
                        <input class="form-control" id="uzmanlik" name="uzmanlik" type="text" placeholder="Ör: Saç Boyama · Balyaj · Kaynak" maxlength="200">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Yıllık Tecrübe</label>
                        <input class="form-control" id="yillik_tecrube" name="yillik_tecrube" type="number" min="0" max="80" placeholder="Ör: 8">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Instagram Kullanıcı Adı</label>
                        <input class="form-control" id="instagram" name="instagram" type="text" placeholder="kullaniciadi" maxlength="150">
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Detaylı Açıklama / Biyografi</label>
                        <textarea class="form-control" id="aciklama" name="aciklama" rows="4" maxlength="1500" placeholder="Ör: 10 yılı aşkın deneyimiyle müşterilerine en uygun saç stilini öneriyor. Özel gün makyajı ve balyaj konusunda uzmandır."></textarea>
                        <small style="color:#888;">Maksimum 1500 karakter. Personelin tecrübesi, eğitimleri ve uzmanlığını özetleyin.</small>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <h3 style="font-size: 15px; font-weight: bold;">Çalışma Saatleri</h3>
                     <table class="table table table-striped table-hover">
                        <tbody>
                           @php $gunler = ['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar']; @endphp
                           @foreach($gunler as $i => $gun)
                              @php $n = $i+1; @endphp
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       <input type="checkbox" id="personelcalisiyor{{$n}}" name="calisiyor{{$n}}"><label for="calisiyor{{$n}}"></label>
                                    </div>
                                 </td>
                                 <td>{{$gun}}</td>
                                 <td><input type="time" id='personelbaslangicsaati{{$n}}' class="form-control" value="00:00" name="baslangicsaati{{$n}}" style="float: left;"></td>
                                 <td><input type="time" id='personelbitissaati{{$n}}' class="form-control" value="00:00" name="bitissaati{{$n}}" style="float: left;"></td>
                              </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
                  <div class="col-md-6">
                     <h3 style="font-size: 15px; font-weight: bold;">Personel Mola Saatleri</h3>
                     <table class="table table table-striped table-hover">
                        <tbody>
                           @foreach($gunler as $i => $gun)
                              @php $n = $i+1; @endphp
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       <input type="checkbox" id="personelmolavar{{$n}}" name="molavar{{$n}}"><label for="molavar{{$n}}"></label>
                                    </div>
                                 </td>
                                 <td>{{$gun}}</td>
                                 <td><input type="time" id='personelmolabaslangicsaati{{$n}}' class="form-control" value="00:00" name="molabaslangicsaati{{$n}}" style="float: left;"></td>
                                 <td><input type="time" id='personelmolabitissaati{{$n}}' class="form-control" value="00:00" name="molabitissaati{{$n}}" style="float: left;"></td>
                              </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
                  <div class="col-md-12" style="margin-top:10px; border-top:1px solid #e2e2e2; padding-top:10px;">
                     <h3 style="font-size: 15px; font-weight: bold; margin-bottom:2px;"><i class="fa fa-money" style="color:#28a745;"></i> Prim & Hak Ediş Ayarları</h3>
                     <p style="color:#888; font-size:12px; margin:0 0 10px">Personelin aylık maaşı ve tahsil edilen tutarlar üzerinden alacağı prim yüzdeleri. Hak ediş raporlarında otomatik hesaplanır.</p>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Sabit Maaş (₺)</label>
                        <input type="number" step="0.01" min="0" id='personel_maas' name="personel_maas" class="form-control" placeholder="Ör: 15000">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Hizmet Primi (%)</label>
                        <input type="number" step="0.01" min="0" max="100" id='hizmet_prim_yuzde' name="hizmet_prim_yuzde" class="form-control" placeholder="Ör: 20">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Ürün Primi (%)</label>
                        <input type="number" step="0.01" min="0" max="100" id='urun_prim_yuzde' name="urun_prim_yuzde" class="form-control" placeholder="Ör: 10">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Paket Primi (%)</label>
                        <input type="number" step="0.01" min="0" max="100" id='paket_prim_yuzde' name="paket_prim_yuzde" class="form-control" placeholder="Ör: 15">
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block;">
               <div class="row">
                  <div class="col-md-6">
                     <button type="submit" class="btn btn-success btn-lg btn-block"> Kaydet</button>
                  </div>
                  <div class="col-md-6">
                     <button type="button" class="btn btn-danger btn-lg btn-block modal_kapat" data-dismiss="modal">Kapat</button>
                  </div>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>
