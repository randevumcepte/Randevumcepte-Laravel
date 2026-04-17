<div
      id="yeni_kampanya_modal"
      class="modal modal-top fade calendar-modal"  tabindex="-1"
      >
      <div class="modal-dialog modal-dialog-centered modal-xl" >
         <div class="modal-content" style="max-height: 95%; width: 95%; margin: 0 auto;" >
            <form id="kampanya_formu"  method="POST">
               <div class="modal-header">
                  <h2 class="modal_baslik" id="kampanya_modal_baslik">Yeni Reklam Oluştur</h2>
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true"  >
                  ×
                  </button>
               </div>
               <div class="modal-body">
                  {!!csrf_field()!!}
                  <input type="hidden" name="kampanya_id" value="">
                  <input type="hidden" name="sube" value="{{$isletme->id}}">
                  <input type="hidden" name="kampanyaKodu" id="kampanyaKodu">
                   <input type="hidden" name="seciliSablonId" id="seciliSablonId">

                  <div class="row" >
                     <div class="col-md-12" style="padding: 0 20px 20px 20px">
                        
                        <div class="row" style="display: none;">
                           <div class="col-6 col-xs-6 col-sm-6 col-md-2">
                              <label></label>
                              <button id="kampanyaHizmetOlarakSec" type="button"  class="btn btn-success btn-block" style="font-size:11px;margin-bottom: 10px;">
                              Hizmet </button>
                           </div>
                           <div class="col-6 col-xs-6 col-sm-6 col-md-2">
                              <label></label>
                              <button id="kampanyaUrunOlarakSec" type="button"  class="btn btn-info btn-block" style="font-size:11px;margin-bottom: 10px;">
                              Ürün </button>
                           </div>
                           <div class="col-12 col-xs-12 col-sm-12 col-md-2">
                              <label></label>
                              <button id="kampanyaPaketOlarakSec" type="button"  class="btn btn-primary btn-block" style="font-size:11px;margin-bottom: 10px;">
                              Paket</button>
                           </div>
                        </div>
                        <div class="row">
                             <div class="col-6 col-xs-6 col-sm-6 col-md-2">
                                <label>Görev Türü</label>
                                <select id="gorevTuru" name="gorevTuru" class="form-control" style="width: 100%;">
                                  <option value="">Seçiniz..</option>
                                 <option value="1">Arama</option>
                                 <option value="2">SMS</option>
                                 <option value="3">Reklam Bildirimi</option>
                                 <option value="4">Bilgilendirme Bildirimi</option>
                              </select>
                           
                           </div>
                           <div class="col-6 col-xs-6 col-sm-6 col-md-2" id='kampanyaSablonFiltre' style="display: none;">
                                <label>Şablon Türü</label>
                                <select id="kampanyaTuru" name="kampanyaTuru" class="form-control" style="width: 100%;">
                                 <option value="">Tümü</option>
                              </select>
                           
                           </div>
                            <div class="col-6 col-xs-6 col-sm-6 col-md-2" id="kategoriFiltre">
                              <label>Kategori</label>
                              <select id="kampanyaKategori" name="kampanyaKategori" class="form-control" style="width: 100%;">
                                 <option value="">Tümü</option>
                                 @foreach(\App\Hizmet_Kategorisi::all() as $hizmetKategori)
                                <option value="{{$hizmetKategori->id}}">{{$hizmetKategori->hizmet_kategorisi_adi}}</option>
                                @endforeach
                                 @foreach(\App\UrunKategorisi::all() as $urunKategori)
                                <option value="urun-{{$urunKategori->id}}">{{$urunKategori->urun_kategori_adi}}</option>
                                @endforeach
                              </select>
                           </div>
                           <div class="col-6 col-xs-6 col-sm-6 col-md-2" id="hizmetUrunFiltre">
                              <label>Hizmet/Ürün</label>
                              <select id="hizmetUrunPaket" name="hizmetUrunPaket" class="form-control opsiyonelSelect " style="width: 100%;">
                                 <option></option>
                                @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',1)->get() as $hizmet)
                                <option value="{{$hizmet->hizmet_id}}">{{$hizmet->hizmetler->hizmet_adi}}</option>
                                @endforeach
                                  @foreach(\App\Urunler::where('salon_id',$isletme->id)->where('aktif',1)->get() as $urun)
                                <option value="urun-{{$urun->id}}">{{$urun->urun_adi}}</option>
                                @endforeach
                              </select>
                           </div>
                           <div class="col-6 col-xs-6 col-sm-6  col-md-2" id="musteriDanisanFiltre">
                              <label style="">Müşteri/Danışanlar</label>
                              <select class="form-control" name="katilimciTuru" id='katilimciTuru'>
                                 <option value="seciniz">Seçiniz...</option>
                                 <option value="">Tümü</option>
                                 <option value="erkekler">Erkekler</option>
                                 <option value="kadinlar">Kadınlar</option> 
                              </select>
                           </div>

                            <!-- YENİ EKLENEN GRUPLAR FİLTRESİ -->
                           <div class="col-6 col-xs-6 col-sm-6  col-md-2" id="gruplarFiltre">
                              <label>Gruplar</label>
                              <select id="musteriGruplari" name="musteriGruplari" class="form-control">
                                 <option value="">Tümü</option>
                                 
                                    @foreach(\App\ReceteGrubu::all() as $grup1)
                                       <option value="hastagrup-{{ $grup1->id }}">{{ $grup1->grup_adi }}</option>
                                    @endforeach
                                 
                                    @foreach($gruplar as $grup2)
                                       @if(isset($grup2['id']))
                                       <option value="haricigrup-{{ $grup2['id'] }}">{{ $grup2['grup_adi'] }}</option>
                                       @endif
                                    @endforeach
                                
                              </select>
                           </div>
                           <div class="col-6 col-xs-6 col-sm-6  col-md-2" id="katilimFiltre">
                              <label>Katılım Durumu</label>
                              <select id="gelenGelmeyenMusteri" name="gelenGelmeyenMusteri" class="form-control">
                                 <option value="">Tümü</option>
                                 <option value="1">Son 1 yıllık müşteriler</option>
                                 <option value="2">Son 2 yıllık müşteriler</option>
                                 <option value="3">Son 3 yıllık müşteriler</option>
                                 <option value="4">Son 4 yıllık müşteriler</option>
                                 <option value="5">Son 5 yıllık müşteriler</option>
                                 <option value="6">Sadık müşteriler</option>
                                 <option value="7">Aktif müşteriler</option>
                                 <option value="8">Pasif müşteriler</option>
                              </select>
                           </div>
                           
                          
                        </div>
                     </div>
                  
                     <div class="col-md-6">
                        <label>Şablon Seçiniz</label>
                     
                        <div style="height:350px;overflow-y: auto;border: 1px solid #e2e2e2;border-radius: 10px;padding: 10px;" id='kampanyaSablonBolumu'>
                           @foreach(\App\SMSTaslaklari::where('salon_id',$isletme->id)->get() as $sablon)
                           <div class="kampanyaSablonSecim" title="Metni Seç" data-value="sablon-{{$sablon->id}}"  style="position:relative; cursor: pointer; margin-bottom: 8px;" name="kampanyaSablonSecim">
                              <p style="padding:5px;background-color: #f2f2f2; border-radius: 20px;border-bottom-left-radius: 0;color:black;font-size:15px; overflow: hidden;">
                                 {{$sablon->baslik}}  
                                 <a name="sablonSil" title="Şablon Sil" data-value="{{$sablon->id}}" style="float:right;z-index:9999999;font-size: 22px;color: red;margin-left: 5px;font-weight: 100;margin-top: -2px;">
                                    <i class="fa fa-remove"></i>
                                 </a> 
                                 &nbsp;
                                 <a name="smsTaslakDuzenle" data-value="{{$sablon->id}}" title="Şablon Düzenle" data-text="{{$sablon->baslik}}|{{$sablon->taslak_icerik}}" style="float:right;z-index:9999999;font-size: 20px; color: #0055B4;">
                                    <i class="fa fa-edit"></i>
                                 </a>
                              </p>
                           </div>
                           @endforeach
                           @foreach(\App\KampanyaSablonlari::all() as $sablon)
                           <div class="kampanyaSablonSecim" title="Metni Seç" data-value="{{$sablon->id}}" style="position:relative; cursor: pointer; margin-bottom: 8px;" name="kampanyaSablonSecim">
                              <p style="padding:5px;background-color: #f2f2f2; border-radius: 20px;border-bottom-left-radius: 0;color:black;font-size:15px; overflow: hidden;">
                                 {{$sablon->baslik}} 
                              </p>
                           </div>
                           @endforeach
                        </div>
                        <div style="margin-top:10px"></div>
                        <button class="btn btn-success btn-sm" data-toggle="modal" id="sablon_olustur" data-target="#sablon_olustur_modal"> 
                           <i class="fa fa-plus"></i> Şablon Ekle
                        </button>
                     </div>
                     
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Kampanya Metni</label>
                           <div style="height: 350px;border: 1px solid #e2e2e2;border-radius: 10px;padding: 10px; overflow-y: auto;" id="kampanyaPrompt" name="kampanyaPrompt" ></div>
                           <div id="karaktersayisi" style="margin-top: 5px; font-size: 12px; color: #666;"></div>
                        </div>
                        <button id='kampanyaMetniGuncelle' class="btn btn-info btn-sm" style="color:white">Metni Güncelle</button>
                     </div>
                     
                     <div class="col-md-2" style="padding:10px 0 10px 10px">
                        <label style="font-size: 16px; "><b>Planlama</b></label>
                     </div>
                     
                     <div class="col-md-2 randevuVerBolumu" style="visibility:hidden">
                        <label>Randevu Verilecek</label>
                         <label class="switch" style="display: flex; align-items: center;">
                         <input id="randevuVer" disabled name="randevuVer"   type="checkbox">
                         <span class="slider"></span>
                       </label>
                     </div>
                     
                     <div class="col-md-2 randevuVerBolumu" style="visibility:hidden;">
                        <div class="form-group">
                           <label>Randevu Tarihi</label>
                           <input type="text" class="form-control date-picker" name="etkinlikRandevuTarihi" id="etkinlikRandevuTarihi">
                           <i class="fa fa-calendar" style="position: absolute; top: 30px; right: 28px; font-size: 13px; z-index: 0;"></i>
                        </div>
                     </div>
                     
                     <div class="col-md-6" id="indirimBolumu" style="padding:10px 0 0 10px; display: flex; align-items: center; gap: 8px;">
                       <span style="font-size: 12px;">X al Y öde</span>
                       <label class="switch" style="display: flex; align-items: center;">
                         <input id="indirimTuru" name="indirimTuru" type="checkbox">
                         <span class="slider"></span>
                       </label>
                       <span style="font-size: 12px;">Yüzde İndirim</span>
                     </div>
                     
                     <div class="col-md-2 col-sm-2 col-xs-6 col-6">
                        <label>Başlangıç Tarihi</label>
                        <i class="fa fa-calendar" style="position: absolute; top: 30px; right: 28px; font-size: 13px; z-index: 0;"></i>
                        <input type="text" class="form-control date-picker" name="asistan_tarih" id="kampanyatarih" value="{{date('Y-m-d')}}" autocomplete="off">
                     </div>
                     
                     <div class="col-md-2 col-sm-2 col-xs-6 col-6">
                        <label>Bitis Tarihi</label>
                        <i class="fa fa-calendar" style="position: absolute; top: 30px; right: 28px; font-size: 13px; z-index: 0;"></i>
                        <input type="text" id='kampanyaGecerlilikTarihi' name='kampanyaGecerlilikTarihi' value="{{date('Y-m-d')}}" class="form-control date-picker">
                     </div>
                     
                     <div class="col-md-2 col-sm-2 col-xs-6 col-6">
                        <label>Saat</label>
                        <input type="time" class="form-control" name="asistan_saat" id="kampanyasaat" value="{{date('H:i')}}" autocomplete="off">
                     </div>
                     
                     <div class="col-md-4 col-sm-4 col-xs-6 col-6 " id="indirimInput">
                       <div id="XalYodeBolumu" style="display:block">
                        <label style="width:100%;visibility: hidden;">X al Y öde</label>
                        <input type="tel" class="form-control" style="max-width:100px;float: left;" id="Xal" placeholder="X al" value="2">
                        <input type="tel" class="form-control" style="max-width: 100px;float: left;margin-left: 10px;" id="Yode" placeholder="Y öde" value="1">
                       </div>
                       <div id="yuzdeIndirimBolumu" style="display:none">
                          <label style="visibility: hidden;">İndirim(%)</label>
                          <input type="tel" id='kampanyaIndirim'  name='kampanyaIndirim' placeholder="İndirim(%)" value="10" class="form-control" style="max-width:210px">
                       </div>
                     </div>
                      
                      <div class="col-md-2 col-sm-3 col-xs-6 col-6" style="text-align:center;">
                        <label>Katılımcı Sayısı </label>
                        <p id="kampanya_katilimci_sayisi" style="font-size:20px; margin-top: 3px;font-weight: bold;">0</p>
                     </div>
                  </div>
                  
                  <div class="row">
                  </div>
                  
                  <div class="modal-footer" style="display:none">
                     <div class="row">
                        <div class="col-7 col-xs-7 col-sm-7">
                           <button type="submit"  class="btn btn-success btn-block">
                           Kaydet & Gönder </button>  
                        </div>
                        <div class="col-5 col-xs-5 col-sm-5">
                           <button  
                              type="button"
                              class="btn btn-danger modal_kapat btn-block"
                              data-dismiss="modal"
                              > <i class="fa fa-times"></i>
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
                  
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6 col-md-5">
                          <audio id='kampanyaSesKaydiCal' controls style="width: 100%;">
                              <source src="http://34.45.69.65/monitor/polly-68dfbd4ca3451.wav"  id='calinacak_kayit' type="audio/wav">
                              Tarayıcınız yürütmeyi desteklememektedir.
                           </audio>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6 col-md-4"></div>
                        <div class="col-6 col-xs-6 col-sm-6 col-md-3">
                           <button id="gorevTanimla" type="button"  class="btn btn-success btn-block">
                          Görev Tanımla</button>
                        </div>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>