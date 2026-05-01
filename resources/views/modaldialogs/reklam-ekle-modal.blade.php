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

                        <!-- KANAL SEÇİCİ KARTLARI (görev türü) -->
                        <div class="reklam-kanal-secici">
                           <div class="reklam-kanal-secici-baslik">
                              <span class="reklam-kanal-secici-step">1</span>
                              <div>
                                 <h6>Kanal Seçin</h6>
                                 <small>Reklamı hangi kanaldan göndereceksiniz?</small>
                              </div>
                           </div>
                           <div class="reklam-kanal-grid">
                              <button type="button" class="reklam-kanal-kart reklam-kanal-kart--sms"  data-gorev="2">
                                 <span class="reklam-kanal-ic"><i class="fa fa-comment-alt"></i></span>
                                 <span class="reklam-kanal-baslik">SMS</span>
                                 <span class="reklam-kanal-aciklama">Toplu SMS gönder</span>
                              </button>
                              <button type="button" class="reklam-kanal-kart reklam-kanal-kart--call" data-gorev="1">
                                 <span class="reklam-kanal-ic"><i class="fa fa-phone-alt"></i></span>
                                 <span class="reklam-kanal-baslik">Santral Arama</span>
                                 <span class="reklam-kanal-aciklama">Sesli kayıt ile ara</span>
                              </button>
                              <button type="button" class="reklam-kanal-kart reklam-kanal-kart--push" data-gorev="3">
                                 <span class="reklam-kanal-ic"><i class="fa fa-bell"></i></span>
                                 <span class="reklam-kanal-baslik">Uygulama Bildirimi</span>
                                 <span class="reklam-kanal-aciklama">Push notification</span>
                              </button>
                              <button type="button" class="reklam-kanal-kart reklam-kanal-kart--info" data-gorev="4">
                                 <span class="reklam-kanal-ic"><i class="fa fa-info-circle"></i></span>
                                 <span class="reklam-kanal-baslik">Bilgilendirme</span>
                                 <span class="reklam-kanal-aciklama">Genel duyuru</span>
                              </button>
                           </div>
                        </div>

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
                        <!-- Gizli görev türü selecti (kanal kartları tetikler) -->
                        <div style="display:none;">
                           <select id="gorevTuru" name="gorevTuru" class="form-control">
                              <option value="">Seçiniz..</option>
                              <option value="1">Arama</option>
                              <option value="2">SMS</option>
                              <option value="3">Reklam Bildirimi</option>
                              <option value="4">Bilgilendirme Bildirimi</option>
                           </select>
                        </div>

                        <!-- HEDEF KİTLE PANELİ -->
                        <div class="rkp" id="reklamKitlePanel">
                           <div class="reklam-kanal-secici-baslik" style="margin-bottom: 14px;">
                              <span class="reklam-kanal-secici-step">2</span>
                              <div>
                                 <h6>Hedef Kitle</h6>
                                 <small>Reklamı kim alacak? Boş bıraktığın filtreler <b>Tümü</b> demektir.</small>
                              </div>
                              <div class="rkp-summary ml-auto">
                                 <i class="fa fa-users"></i>
                                 <span><b id="rkpKitleSayi">0</b> kişi eşleşiyor</span>
                              </div>
                           </div>

                           <!-- Üst satır: Cinsiyet (segmented) + Davranış + Grup -->
                           <div class="rkp-grid">
                              <div class="rkp-cell" id="musteriDanisanFiltre">
                                 <div class="rkp-label"><i class="fa fa-user"></i> Cinsiyet</div>
                                 <div class="rkp-segment" data-target="katilimciTuru">
                                    <button type="button" class="rkp-seg-btn is-active" data-val=""><i class="fa fa-globe"></i> Tümü</button>
                                    <button type="button" class="rkp-seg-btn rkp-seg-btn--kadin" data-val="kadinlar"><i class="fa fa-venus"></i> Kadın</button>
                                    <button type="button" class="rkp-seg-btn rkp-seg-btn--erkek" data-val="erkekler"><i class="fa fa-mars"></i> Erkek</button>
                                 </div>
                                 <select class="form-control" name="katilimciTuru" id="katilimciTuru" style="display:none;">
                                    <option value="seciniz">Seçiniz...</option>
                                    <option value="" selected>Tümü</option>
                                    <option value="erkekler">Erkekler</option>
                                    <option value="kadinlar">Kadınlar</option>
                                 </select>
                              </div>

                              <div class="rkp-cell" id="katilimFiltre">
                                 <div class="rkp-label"><i class="fa fa-history"></i> Müşteri Davranışı</div>
                                 <select id="gelenGelmeyenMusteri" name="gelenGelmeyenMusteri" class="form-control rkp-select">
                                    <option value="">Hepsi (filtre yok)</option>
                                    <optgroup label="— Aktivite Durumu —">
                                       <option value="7">⭐ Aktif Müşteriler</option>
                                       <option value="6">💎 Sadık Müşteriler</option>
                                       <option value="8">😴 Pasif Müşteriler</option>
                                    </optgroup>
                                    <optgroup label="— Son Ziyaret Aralığı —">
                                       <option value="1">Son 1 Yıl İçinde Gelenler</option>
                                       <option value="2">Son 2 Yıl İçinde Gelenler</option>
                                       <option value="3">Son 3 Yıl İçinde Gelenler</option>
                                       <option value="4">Son 4 Yıl İçinde Gelenler</option>
                                       <option value="5">Son 5 Yıl İçinde Gelenler</option>
                                    </optgroup>
                                 </select>
                              </div>

                              <div class="rkp-cell" id="gruplarFiltre">
                                 <div class="rkp-label"><i class="fa fa-users"></i> Grup</div>
                                 <select id="musteriGruplari" name="musteriGruplari" class="form-control rkp-select">
                                    <option value="">Hepsi (grup filtresi yok)</option>
                                    @php $hastaGruplari = \App\ReceteGrubu::all(); @endphp
                                    @if(count($hastaGruplari))
                                    <optgroup label="— Hasta Grupları —">
                                       @foreach($hastaGruplari as $grup1)
                                          <option value="hastagrup-{{ $grup1->id }}">{{ $grup1->grup_adi }}</option>
                                       @endforeach
                                    </optgroup>
                                    @endif
                                    @php
                                       $hariciGrupVar = false;
                                       foreach(($gruplar ?? []) as $g){ if(isset($g['id'])){ $hariciGrupVar = true; break; } }
                                    @endphp
                                    @if($hariciGrupVar)
                                    <optgroup label="— SMS Grupları —">
                                       @foreach($gruplar as $grup2)
                                          @if(isset($grup2['id']))
                                          <option value="haricigrup-{{ $grup2['id'] }}">{{ $grup2['grup_adi'] }}</option>
                                          @endif
                                       @endforeach
                                    </optgroup>
                                    @endif
                                 </select>
                              </div>
                           </div>

                           <!-- Alt satır: Hizmet/Ürün ilgisi (Kategori → Hizmet/Ürün) -->
                           <div class="rkp-grid rkp-grid--2 rkp-mt">
                              <div class="rkp-cell" id="kategoriFiltre">
                                 <div class="rkp-label"><i class="fa fa-folder"></i> Hizmet/Ürün Kategorisi <span class="rkp-opt">opsiyonel</span></div>
                                 <select id="kampanyaKategori" name="kampanyaKategori" class="form-control rkp-select">
                                    <option value="">Tüm kategoriler</option>
                                    @if(count(\App\Hizmet_Kategorisi::all()))
                                    <optgroup label="— Hizmet Kategorileri —">
                                       @foreach(\App\Hizmet_Kategorisi::all() as $hizmetKategori)
                                          <option value="{{$hizmetKategori->id}}">{{$hizmetKategori->hizmet_kategorisi_adi}}</option>
                                       @endforeach
                                    </optgroup>
                                    @endif
                                    @if(count(\App\UrunKategorisi::all()))
                                    <optgroup label="— Ürün Kategorileri —">
                                       @foreach(\App\UrunKategorisi::all() as $urunKategori)
                                          <option value="urun-{{$urunKategori->id}}">{{$urunKategori->urun_kategori_adi}}</option>
                                       @endforeach
                                    </optgroup>
                                    @endif
                                 </select>
                              </div>
                              <div class="rkp-cell" id="hizmetUrunFiltre">
                                 <div class="rkp-label"><i class="fa fa-tag"></i> Belirli Hizmet / Ürün <span class="rkp-opt">opsiyonel</span></div>
                                 <select id="hizmetUrunPaket" name="hizmetUrunPaket" class="form-control opsiyonelSelect rkp-select">
                                    <option></option>
                                    @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',1)->get() as $hizmet)
                                       <option value="{{$hizmet->hizmet_id}}">{{$hizmet->hizmetler->hizmet_adi}}</option>
                                    @endforeach
                                    @foreach(\App\Urunler::where('salon_id',$isletme->id)->where('aktif',1)->get() as $urun)
                                       <option value="urun-{{$urun->id}}">{{$urun->urun_adi}}</option>
                                    @endforeach
                                 </select>
                                 <small class="rkp-hint">Bu hizmet/ürünle ilgilenen müşterilere göndermek istiyorsan seç.</small>
                              </div>
                           </div>

                           <!-- Aktif filtreler özeti -->
                           <div class="rkp-active-chips" id="rkpAktifFiltreler"></div>

                           <!-- Şablon Türü filtresi (kanala göre dinamik gösterim) -->
                           <div class="rkp-extra rkp-mt" id='kampanyaSablonFiltre' style="display: none;">
                              <div class="rkp-label"><i class="fa fa-file-alt"></i> Şablon Türü</div>
                              <select id="kampanyaTuru" name="kampanyaTuru" class="form-control rkp-select">
                                 <option value="">Tümü</option>
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

      <style>
      /* KANAL SEÇİCİ KARTLARI — yeni reklam modalı içinde */
      #yeni_kampanya_modal .reklam-kanal-secici { padding: 14px 4px 18px; border-bottom: 1px dashed #e2e8f0; margin-bottom: 14px; }
      #yeni_kampanya_modal .reklam-kanal-secici-baslik { display:flex; align-items:center; gap:12px; margin-bottom: 12px; }
      #yeni_kampanya_modal .reklam-kanal-secici-step {
         width: 28px; height: 28px; border-radius: 50%;
         background: linear-gradient(135deg,#5C008E,#7B2FB8);
         color:#fff; font-weight:700; font-size:13px;
         display:flex; align-items:center; justify-content:center;
      }
      #yeni_kampanya_modal .reklam-kanal-secici-baslik h6 { margin:0; font-weight:700; color:#1e293b; font-size:14px; }
      #yeni_kampanya_modal .reklam-kanal-secici-baslik small { color:#64748b; font-size:12px; }

      #yeni_kampanya_modal .reklam-kanal-grid {
         display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;
      }
      #yeni_kampanya_modal .reklam-kanal-kart {
         background:#fff; border:2px solid #e2e8f0; border-radius:14px;
         padding:14px 12px; text-align:center; cursor:pointer;
         transition: all .18s ease; position:relative; overflow:hidden;
         display:flex; flex-direction:column; align-items:center; gap:6px;
      }
      #yeni_kampanya_modal .reklam-kanal-kart:hover { transform: translateY(-3px); box-shadow: 0 8px 22px rgba(15,23,42,.08); }
      #yeni_kampanya_modal .reklam-kanal-ic {
         width:48px; height:48px; border-radius:14px;
         display:flex; align-items:center; justify-content:center;
         font-size:20px; color:#fff; margin-bottom:2px;
      }
      #yeni_kampanya_modal .reklam-kanal-baslik { font-weight:700; font-size:13.5px; color:#1e293b; }
      #yeni_kampanya_modal .reklam-kanal-aciklama { font-size:11.5px; color:#64748b; }

      #yeni_kampanya_modal .reklam-kanal-kart--sms  .reklam-kanal-ic { background: linear-gradient(135deg,#06b6d4,#0284c7); }
      #yeni_kampanya_modal .reklam-kanal-kart--call .reklam-kanal-ic { background: linear-gradient(135deg,#10b981,#059669); }
      #yeni_kampanya_modal .reklam-kanal-kart--push .reklam-kanal-ic { background: linear-gradient(135deg,#8b5cf6,#6d28d9); }
      #yeni_kampanya_modal .reklam-kanal-kart--info .reklam-kanal-ic { background: linear-gradient(135deg,#f59e0b,#d97706); }

      #yeni_kampanya_modal .reklam-kanal-kart.is-active { border-color: #7B2FB8; background: linear-gradient(180deg,#faf5ff,#fff); box-shadow: 0 8px 22px rgba(123,47,184,.12); }
      #yeni_kampanya_modal .reklam-kanal-kart.is-active::after {
         content: "\f00c"; font-family: "Font Awesome 5 Free"; font-weight: 900;
         position:absolute; top:8px; right:10px; color:#7B2FB8; font-size:13px;
      }
      #yeni_kampanya_modal .reklam-kanal-kart--sms.is-active  { border-color:#0284c7; background: linear-gradient(180deg,#ecfeff,#fff); box-shadow:0 8px 22px rgba(2,132,199,.14); }
      #yeni_kampanya_modal .reklam-kanal-kart--sms.is-active::after { color:#0284c7; }
      #yeni_kampanya_modal .reklam-kanal-kart--call.is-active { border-color:#059669; background: linear-gradient(180deg,#ecfdf5,#fff); box-shadow:0 8px 22px rgba(5,150,105,.14); }
      #yeni_kampanya_modal .reklam-kanal-kart--call.is-active::after { color:#059669; }
      #yeni_kampanya_modal .reklam-kanal-kart--push.is-active { border-color:#6d28d9; background: linear-gradient(180deg,#f5f3ff,#fff); box-shadow:0 8px 22px rgba(109,40,217,.14); }
      #yeni_kampanya_modal .reklam-kanal-kart--push.is-active::after { color:#6d28d9; }
      #yeni_kampanya_modal .reklam-kanal-kart--info.is-active { border-color:#d97706; background: linear-gradient(180deg,#fffbeb,#fff); box-shadow:0 8px 22px rgba(217,119,6,.14); }
      #yeni_kampanya_modal .reklam-kanal-kart--info.is-active::after { color:#d97706; }

      @media (max-width: 767px) {
         #yeni_kampanya_modal .reklam-kanal-grid { grid-template-columns: repeat(2, 1fr); }
      }

      /* ============ HEDEF KİTLE PANELİ (rkp) ============ */
      #yeni_kampanya_modal .rkp {
         background: linear-gradient(180deg, #fafbff 0%, #ffffff 100%);
         border: 1px solid #eef0f3; border-radius: 14px;
         padding: 18px 18px 16px; margin-bottom: 18px;
      }
      #yeni_kampanya_modal .rkp .reklam-kanal-secici-baslik { display: flex; align-items: center; gap: 12px; }
      #yeni_kampanya_modal .rkp-summary {
         display: inline-flex; align-items: center; gap: 6px;
         padding: 6px 12px; border-radius: 999px;
         background: linear-gradient(135deg,#5C008E,#7B2FB8); color: #fff;
         font-size: 12px; font-weight: 600;
         box-shadow: 0 4px 12px rgba(123,47,184,.18);
      }
      #yeni_kampanya_modal .rkp-summary i { font-size: 11px; }
      #yeni_kampanya_modal .rkp-summary b { font-size: 14px; }

      #yeni_kampanya_modal .rkp-grid {
         display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;
      }
      #yeni_kampanya_modal .rkp-grid--2 { grid-template-columns: 1fr 1.4fr; }
      #yeni_kampanya_modal .rkp-mt { margin-top: 14px; }

      #yeni_kampanya_modal .rkp-cell { min-width: 0; }
      #yeni_kampanya_modal .rkp-label {
         font-size: 12px; font-weight: 700; color: #475569;
         text-transform: uppercase; letter-spacing: .3px;
         margin-bottom: 8px; display: flex; align-items: center; gap: 6px;
      }
      #yeni_kampanya_modal .rkp-label i { color: #7B2FB8; font-size: 11px; }
      #yeni_kampanya_modal .rkp-opt {
         margin-left: auto; font-size: 10px; font-weight: 600;
         color: #94a3b8; text-transform: lowercase; letter-spacing: 0;
         background: #f1f5f9; padding: 2px 8px; border-radius: 999px;
      }

      /* Modern select görünümü */
      #yeni_kampanya_modal .rkp-select {
         height: 42px; border: 1.5px solid #e2e8f0; border-radius: 10px;
         padding: 8px 12px; font-size: 13.5px; background-color: #fff;
         transition: all .15s ease; color: #1e293b;
      }
      #yeni_kampanya_modal .rkp-select:hover { border-color: #cbd5e1; }
      #yeni_kampanya_modal .rkp-select:focus {
         border-color: #7B2FB8; outline: none;
         box-shadow: 0 0 0 3px rgba(123,47,184,.12);
      }
      /* Filtre aktifse (boş değilse) yeşil kenar */
      #yeni_kampanya_modal .rkp-select.is-active {
         border-color: #7B2FB8; background: #faf5ff;
      }

      /* Cinsiyet segmented butonlar */
      #yeni_kampanya_modal .rkp-segment {
         display: grid; grid-template-columns: repeat(3, 1fr);
         border: 1.5px solid #e2e8f0; border-radius: 10px;
         overflow: hidden; background: #fff;
      }
      #yeni_kampanya_modal .rkp-seg-btn {
         border: none; background: transparent;
         padding: 11px 8px; font-size: 13px; font-weight: 600;
         color: #64748b; cursor: pointer;
         display: inline-flex; align-items: center; justify-content: center; gap: 6px;
         transition: all .15s ease;
         border-right: 1px solid #f1f5f9;
      }
      #yeni_kampanya_modal .rkp-seg-btn:last-child { border-right: none; }
      #yeni_kampanya_modal .rkp-seg-btn:hover { background: #f8fafc; color: #1e293b; }
      #yeni_kampanya_modal .rkp-seg-btn i { font-size: 11px; }
      #yeni_kampanya_modal .rkp-seg-btn.is-active {
         background: linear-gradient(135deg,#5C008E,#7B2FB8); color: #fff;
         box-shadow: inset 0 -2px 0 rgba(0,0,0,.1);
      }
      #yeni_kampanya_modal .rkp-seg-btn--kadin.is-active { background: linear-gradient(135deg,#ec4899,#be185d); }
      #yeni_kampanya_modal .rkp-seg-btn--erkek.is-active { background: linear-gradient(135deg,#3b82f6,#1d4ed8); }

      #yeni_kampanya_modal .rkp-hint { display: block; margin-top: 6px; color: #94a3b8; font-size: 11.5px; }

      /* Aktif filtre chipleri özeti */
      #yeni_kampanya_modal .rkp-active-chips {
         display: flex; flex-wrap: wrap; gap: 6px;
         margin-top: 12px; min-height: 0;
      }
      #yeni_kampanya_modal .rkp-active-chips:empty { display: none; }
      #yeni_kampanya_modal .rkp-chip {
         display: inline-flex; align-items: center; gap: 6px;
         padding: 4px 10px 4px 12px; border-radius: 999px;
         background: #ede9fe; color: #5b21b6; font-size: 12px; font-weight: 600;
         border: 1px solid #ddd6fe;
      }
      #yeni_kampanya_modal .rkp-chip i { font-size: 10px; }
      #yeni_kampanya_modal .rkp-chip-x {
         margin-left: 4px; cursor: pointer; padding: 0 4px;
         border-radius: 50%; transition: background .15s ease;
      }
      #yeni_kampanya_modal .rkp-chip-x:hover { background: rgba(91,33,182,.18); }

      #yeni_kampanya_modal .rkp-extra { padding: 10px 12px; background: #fff; border: 1px dashed #e2e8f0; border-radius: 10px; }

      /* Responsive */
      @media (max-width: 991px) {
         #yeni_kampanya_modal .rkp-grid { grid-template-columns: repeat(2, 1fr); }
         #yeni_kampanya_modal .rkp-grid--2 { grid-template-columns: 1fr; }
      }
      @media (max-width: 575px) {
         #yeni_kampanya_modal .rkp-grid { grid-template-columns: 1fr; }
         #yeni_kampanya_modal .rkp-summary { display: none; }
         #yeni_kampanya_modal .rkp { padding: 14px; }
      }
      </style>

      <script>
      (function(){
         // ---- KANAL KARTLARI ----
         $(document).on('click','#yeni_kampanya_modal .reklam-kanal-kart',function(e){
            e.preventDefault();
            var v = $(this).data('gorev');
            $('#yeni_kampanya_modal .reklam-kanal-kart').removeClass('is-active');
            $(this).addClass('is-active');
            $('#gorevTuru').val(String(v)).trigger('change');
         });
         // ---- MODAL KAPANINCA TAM SIFIRLA ----
         function reklamModalSifirla(){
            var $m = $('#yeni_kampanya_modal');
            // Form alanlarını sıfırla
            var formEl = document.getElementById('kampanya_formu');
            if(formEl) formEl.reset();
            // Hidden ve dinamik input'lar
            $m.find('input[name="kampanya_id"]').val('');
            $m.find('#kampanyaKodu').val('');
            $m.find('#seciliSablonId').val('');
            // Kanal kartları
            $m.find('.reklam-kanal-kart').removeClass('is-active');
            // Cinsiyet segmented → "Tümü" aktif
            var $segs = $m.find('.rkp-segment .rkp-seg-btn');
            $segs.removeClass('is-active');
            $segs.filter('[data-val=""]').addClass('is-active');
            // Tüm filtre select'lerini boşalt
            ['gorevTuru','katilimciTuru','kampanyaTuru','kampanyaKategori','hizmetUrunPaket','musteriGruplari','gelenGelmeyenMusteri']
               .forEach(function(id){
                  var $s = $('#'+id);
                  if(!$s.length) return;
                  $s.val('').removeClass('is-active');
                  if($s.hasClass('select2-hidden-accessible')) $s.trigger('change.select2');
               });
            // Kampanya metni / şablon / sayım
            $m.find('#kampanyaPrompt').empty();
            $m.find('#karaktersayisi').empty();
            $m.find('.kampanyaSablonSecim').removeClass('selected');
            $m.find('#kampanya_katilimci_sayisi').text('0');
            $m.find('#rkpKitleSayi').text('0');
            $m.find('#rkpAktifFiltreler').empty();
            // İndirim alanları default
            $m.find('#indirimTuru').prop('checked', false);
            $m.find('#XalYodeBolumu').show();
            $m.find('#yuzdeIndirimBolumu').hide();
            // Modal başlığı default
            $m.find('#kampanya_modal_baslik').text('Yeni Reklam Oluştur');
         }
         $('#yeni_kampanya_modal').on('hidden.bs.modal', reklamModalSifirla);
         $('#yeni_kampanya_modal').on('shown.bs.modal', function(){
            var v = $('#gorevTuru').val();
            $('#yeni_kampanya_modal .reklam-kanal-kart').removeClass('is-active');
            if(v) $('#yeni_kampanya_modal .reklam-kanal-kart[data-gorev="'+v+'"]').addClass('is-active');
            rkpFiltreOzeti();
         });

         // ---- CİNSİYET SEGMENTED ----
         $(document).on('click','#yeni_kampanya_modal .rkp-seg-btn',function(e){
            e.preventDefault();
            var v = $(this).data('val') || '';
            $(this).siblings().removeClass('is-active');
            $(this).addClass('is-active');
            // Gizli select'i tetikle (mevcut kampanyaSablonGetir akışı çalışsın)
            $('#katilimciTuru').val(v === '' ? '' : v).trigger('change');
            rkpFiltreOzeti();
         });

         // Eğer JS başka yerden #katilimciTuru değiştirirse butonları senkronize et
         $(document).on('change','#katilimciTuru',function(){
            var v = $(this).val();
            var $btns = $('#yeni_kampanya_modal .rkp-segment[data-target="katilimciTuru"] .rkp-seg-btn');
            $btns.removeClass('is-active');
            if(v === '' || v === 'seciniz' || v == null) $btns.filter('[data-val=""]').addClass('is-active');
            else $btns.filter('[data-val="'+v+'"]').addClass('is-active');
            rkpFiltreOzeti();
         });

         // ---- AKTİF FİLTRELERİ ÖZETLE ----
         function chip(label, value, clearTarget, icon){
            return '<span class="rkp-chip"><i class="fa '+(icon||'fa-filter')+'"></i> <b>'+label+':</b> '+value+
                   ' <span class="rkp-chip-x" data-clear="'+clearTarget+'" title="Kaldır">×</span></span>';
         }
         function rkpFiltreOzeti(){
            var $box = $('#rkpAktifFiltreler'); if(!$box.length) return;
            var html = '';
            // Cinsiyet
            var c = $('#katilimciTuru').val();
            if(c === 'kadinlar')      html += chip('Cinsiyet','Kadın','katilimciTuru','fa-venus');
            else if(c === 'erkekler') html += chip('Cinsiyet','Erkek','katilimciTuru','fa-mars');
            // Davranış
            var dV = $('#gelenGelmeyenMusteri').val();
            if(dV)  html += chip('Davranış', $('#gelenGelmeyenMusteri option:selected').text(),'gelenGelmeyenMusteri','fa-history');
            // Grup
            var gV = $('#musteriGruplari').val();
            if(gV)  html += chip('Grup', $('#musteriGruplari option:selected').text(),'musteriGruplari','fa-users');
            // Kategori
            var kV = $('#kampanyaKategori').val();
            if(kV)  html += chip('Kategori', $('#kampanyaKategori option:selected').text(),'kampanyaKategori','fa-folder');
            // Hizmet/Ürün
            var hV = $('#hizmetUrunPaket').val();
            if(hV)  html += chip('Hizmet/Ürün', $('#hizmetUrunPaket option:selected').text(),'hizmetUrunPaket','fa-tag');
            $box.html(html);

            // Select'leri "aktif" görseliyle işaretle
            ['gelenGelmeyenMusteri','musteriGruplari','kampanyaKategori','hizmetUrunPaket','kampanyaTuru'].forEach(function(id){
               var $s = $('#'+id);
               if($s.val()) $s.addClass('is-active'); else $s.removeClass('is-active');
            });

            // Kitle sayısını mevcut #kampanya_katilimci_sayisi'ndan ayna olarak göster
            var sayi = parseInt($('#kampanya_katilimci_sayisi').text(), 10) || 0;
            $('#rkpKitleSayi').text(sayi);
         }
         // Selectler değiştiğinde özet güncellensin
         $(document).on('change','#gelenGelmeyenMusteri, #musteriGruplari, #kampanyaKategori, #hizmetUrunPaket, #kampanyaTuru', rkpFiltreOzeti);

         // Chip × ile filtre kaldır
         $(document).on('click','.rkp-chip-x',function(){
            var t = $(this).data('clear');
            if(t === 'katilimciTuru'){
               $('#katilimciTuru').val('').trigger('change');
            } else if(t){
               $('#'+t).val('').trigger('change');
            }
         });

         // Katılımcı sayısı değiştiğinde özetteki kitle sayısı da güncellensin
         var elKS = document.getElementById('kampanya_katilimci_sayisi');
         if(elKS && window.MutationObserver){
            new MutationObserver(rkpFiltreOzeti).observe(elKS, {childList:true, subtree:true, characterData:true});
         }
      })();
      </script>
   </div>