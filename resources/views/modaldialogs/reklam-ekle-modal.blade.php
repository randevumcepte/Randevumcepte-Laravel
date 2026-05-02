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

                  @php
                     // Hizmet & ürünleri (kategori bilgisiyle) JS'e ver
                     $hkList = \App\Hizmet_Kategorisi::all()->map(function($k){
                        return ['id'=>(int)$k->id,'ad'=>$k->hizmet_kategorisi_adi];
                     })->values();
                     $hizmetList = \App\SalonHizmetler::with('hizmetler')
                        ->where('salon_id',$isletme->id)->where('aktif',1)->get()
                        ->map(function($sh){
                           $h = $sh->hizmetler;
                           if(!$h) return null;
                           return ['id'=>(int)$sh->hizmet_id,'ad'=>$h->hizmet_adi,'kategori_id'=>$h->hizmet_kategori_id ? (int)$h->hizmet_kategori_id : null];
                        })->filter()->values();

                     $ukList = \App\UrunKategorisi::all()->map(function($k){
                        return ['id'=>(int)$k->id,'ad'=>$k->urun_kategori_adi];
                     })->values();

                     // Ürün-kategori ilişkisi şemada varsa kullan
                     $urunKategoriField = null;
                     if(\Illuminate\Support\Facades\Schema::hasColumn('urunler','urun_kategori_id'))    $urunKategoriField = 'urun_kategori_id';
                     elseif(\Illuminate\Support\Facades\Schema::hasColumn('urunler','kategori_id'))     $urunKategoriField = 'kategori_id';
                     elseif(\Illuminate\Support\Facades\Schema::hasColumn('urunler','urun_kategorisi_id')) $urunKategoriField = 'urun_kategorisi_id';

                     $urunList = \App\Urunler::where('salon_id',$isletme->id)->where('aktif',1)->get()
                        ->map(function($u) use ($urunKategoriField){
                           return ['id'=>(int)$u->id,'ad'=>$u->urun_adi,'kategori_id'=> $urunKategoriField ? ($u->{$urunKategoriField} ? (int)$u->{$urunKategoriField} : null) : null];
                        })->values();
                  @endphp
                  <script>
                     window.RKP_DATA = {
                        hizmetKategoriler: @json($hkList),
                        hizmetler:         @json($hizmetList),
                        urunKategoriler:   @json($ukList),
                        urunler:           @json($urunList),
                        urunKategoriIliskisiVar: {{ $urunKategoriField ? 'true' : 'false' }}
                     };
                  </script>

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

                           <!-- 1. Cinsiyet — küçük chip filtresi -->
                           <div class="rkp-cinsiyet-bar" id="musteriDanisanFiltre">
                              <span class="rkp-cinsiyet-label">Cinsiyet:</span>
                              <div class="rkp-segment rkp-segment--mini" data-target="katilimciTuru">
                                 <button type="button" class="rkp-seg-btn is-active" data-val=""><i class="fa fa-globe"></i> Hepsi</button>
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

                           <!-- 2. Hazır kitle paketleri (preset cards) -->
                           <div class="rkp-presets">
                              <button type="button" class="rkp-preset rkp-preset--all is-active" data-preset="all">
                                 <span class="rkp-preset-emoji">🌟</span>
                                 <span class="rkp-preset-baslik">Tüm Müşterilerim</span>
                                 <span class="rkp-preset-aciklama">Bütün müşterilere gönder</span>
                              </button>

                              <button type="button" class="rkp-preset rkp-preset--loyal" data-preset="sadik">
                                 <span class="rkp-preset-emoji">💎</span>
                                 <span class="rkp-preset-baslik">Sadık Müşterilerim</span>
                                 <span class="rkp-preset-aciklama">Sürekli gelen sadık kitle</span>
                              </button>

                              <button type="button" class="rkp-preset rkp-preset--active" data-preset="aktif">
                                 <span class="rkp-preset-emoji">⭐</span>
                                 <span class="rkp-preset-baslik">Aktif Müşteriler</span>
                                 <span class="rkp-preset-aciklama">Yakın zamanda hizmet alanlar</span>
                              </button>

                              <button type="button" class="rkp-preset rkp-preset--winback" data-preset="pasif">
                                 <span class="rkp-preset-emoji">😴</span>
                                 <span class="rkp-preset-baslik">Geri Kazanılmalı</span>
                                 <span class="rkp-preset-aciklama">Uzun süredir gelmeyenler</span>
                              </button>

                              <button type="button" class="rkp-preset rkp-preset--recent" data-preset="son1yil">
                                 <span class="rkp-preset-emoji">🆕</span>
                                 <span class="rkp-preset-baslik">Son 1 Yılın Müşterileri</span>
                                 <span class="rkp-preset-aciklama">Son 12 ay içinde gelmiş</span>
                              </button>

                              <button type="button" class="rkp-preset rkp-preset--service" data-preset="hizmet">
                                 <span class="rkp-preset-emoji">💇</span>
                                 <span class="rkp-preset-baslik">Belirli Hizmet</span>
                                 <span class="rkp-preset-aciklama">Önce kategori, sonra hizmet</span>
                              </button>

                              <button type="button" class="rkp-preset rkp-preset--product" data-preset="urun">
                                 <span class="rkp-preset-emoji">🛍️</span>
                                 <span class="rkp-preset-baslik">Belirli Ürün</span>
                                 <span class="rkp-preset-aciklama">Önce kategori, sonra ürün</span>
                              </button>

                              <button type="button" class="rkp-preset rkp-preset--group" data-preset="grup">
                                 <span class="rkp-preset-emoji">👥</span>
                                 <span class="rkp-preset-baslik">Özel Grubum</span>
                                 <span class="rkp-preset-aciklama">Önceden oluşturduğum grup</span>
                              </button>

                           </div>

                           <!-- 3. Preset'e göre dinamik açılan ek alanlar -->
                           <!-- HİZMET preset → 2 kademe: kategori → hizmet -->
                           <div class="rkp-preset-extra" id="rkpPresetHizmet" style="display:none;" id-block="hizmetUrunFiltre">
                              <div class="rkp-step-grid">
                                 <div class="rkp-step">
                                    <div class="rkp-step-num">1</div>
                                    <div class="rkp-step-body">
                                       <div class="rkp-label"><i class="fa fa-folder"></i> Hizmet Kategorisi</div>
                                       <select class="form-control rkp-select" id="rkpHizmetKategoriSec">
                                          <option value="">Kategori seçin...</option>
                                       </select>
                                    </div>
                                 </div>
                                 <div class="rkp-step-arrow"><i class="fa fa-chevron-right"></i></div>
                                 <div class="rkp-step">
                                    <div class="rkp-step-num">2</div>
                                    <div class="rkp-step-body">
                                       <div class="rkp-label"><i class="fa fa-cut"></i> Hizmet</div>
                                       <select class="form-control rkp-select" id="rkpHizmetSec" disabled>
                                          <option value="">Önce kategori seçin</option>
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <small class="rkp-hint" style="margin-top:8px; display:block;">
                                 Bu hizmetle ilgilenen müşterilere reklam gönderilir.
                              </small>
                           </div>

                           <!-- ÜRÜN preset → 2 kademe: kategori → ürün -->
                           <div class="rkp-preset-extra" id="rkpPresetUrun" style="display:none;">
                              <div class="rkp-step-grid">
                                 <div class="rkp-step">
                                    <div class="rkp-step-num">1</div>
                                    <div class="rkp-step-body">
                                       <div class="rkp-label"><i class="fa fa-folder"></i> Ürün Kategorisi</div>
                                       <select class="form-control rkp-select" id="rkpUrunKategoriSec">
                                          <option value="">Kategori seçin...</option>
                                       </select>
                                    </div>
                                 </div>
                                 <div class="rkp-step-arrow"><i class="fa fa-chevron-right"></i></div>
                                 <div class="rkp-step">
                                    <div class="rkp-step-num">2</div>
                                    <div class="rkp-step-body">
                                       <div class="rkp-label"><i class="fa fa-box-open"></i> Ürün</div>
                                       <select class="form-control rkp-select" id="rkpUrunSec" disabled>
                                          <option value="">Önce kategori seçin</option>
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <small class="rkp-hint" style="margin-top:8px; display:block;">
                                 Bu ürünle ilgilenen müşterilere reklam gönderilir.
                              </small>
                           </div>

                           <!-- Eski tek select - GİZLİ — backend'in beklediği gerçek değerleri taşır -->
                           <div style="display:none;" id="hizmetUrunFiltre">
                              <select id="hizmetUrunPaket" name="hizmetUrunPaket" class="opsiyonelSelect">
                                 <option></option>
                                 @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',1)->get() as $hizmet)
                                    <option value="{{$hizmet->hizmet_id}}">{{$hizmet->hizmetler->hizmet_adi}}</option>
                                 @endforeach
                                 @foreach(\App\Urunler::where('salon_id',$isletme->id)->where('aktif',1)->get() as $urun)
                                    <option value="urun-{{$urun->id}}">{{$urun->urun_adi}}</option>
                                 @endforeach
                              </select>
                           </div>

                           <div class="rkp-preset-extra" id="rkpPresetGrup" style="display:none;">
                              <div class="rkp-cell" id="gruplarFiltre">
                                 <div class="rkp-grup-header">
                                    <div class="rkp-label"><i class="fa fa-users"></i> Hangi grup?</div>
                                    <button type="button" class="rkp-yeni-grup-btn" id="rkpYeniGrupBtn">
                                       <i class="fa fa-plus"></i> Yeni Grup Oluştur
                                    </button>
                                 </div>
                                 @php
                                    $hariciGruplar = [];
                                    foreach(($gruplar ?? []) as $g){ if(isset($g['id'])){ $hariciGruplar[] = $g; } }
                                 @endphp
                                 <select id="musteriGruplari" name="musteriGruplari" class="form-control rkp-select">
                                    <option value="">Grup seçin...</option>
                                    @foreach($hariciGruplar as $grup2)
                                       <option value="haricigrup-{{ $grup2['id'] }}">{{ $grup2['grup_adi'] }}</option>
                                    @endforeach
                                 </select>
                                 @if(count($hariciGruplar) === 0)
                                    <div class="rkp-grup-empty">
                                       <i class="fa fa-info-circle"></i>
                                       Henüz grup oluşturmadınız. <b>+ Yeni Grup Oluştur</b> butonuyla oluşturabilirsiniz.
                                    </div>
                                 @endif
                              </div>
                           </div>

                           <!-- Gizli select'ler — preset'ler tarafından programatik olarak set edilir -->
                           <div id="rkpPresetCustom" style="display:none;">
                              <select id="gelenGelmeyenMusteri" name="gelenGelmeyenMusteri">
                                 <option value="" selected>Hepsi</option>
                                 <option value="7">Aktif Müşteriler</option>
                                 <option value="6">Sadık Müşteriler</option>
                                 <option value="8">Pasif Müşteriler</option>
                                 <option value="1">Son 1 Yıl</option>
                                 <option value="2">Son 2 Yıl</option>
                                 <option value="3">Son 3 Yıl</option>
                                 <option value="4">Son 4 Yıl</option>
                                 <option value="5">Son 5 Yıl</option>
                              </select>
                              <select id="kampanyaKategori" name="kampanyaKategori">
                                 <option value="" selected>Tüm kategoriler</option>
                                 @foreach(\App\Hizmet_Kategorisi::all() as $hizmetKategori)
                                    <option value="{{$hizmetKategori->id}}">{{$hizmetKategori->hizmet_kategorisi_adi}}</option>
                                 @endforeach
                                 @foreach(\App\UrunKategorisi::all() as $urunKategori)
                                    <option value="urun-{{$urunKategori->id}}">{{$urunKategori->urun_kategori_adi}}</option>
                                 @endforeach
                              </select>
                              <span id="katilimFiltre"></span>
                           </div>

                           <!-- 5. Seçilen kitle özeti -->
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
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" id="sablon_olustur" data-target="#sablon_olustur_modal">
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
                     <div class="row align-items-center">
                        <div class="col-12 col-md-9">
                           <div id="kampanyaSesPlayer" class="kss-player">
                              <button type="button" id="kampanyaSesOku" class="kss-btn kss-btn-play">
                                 <i class="fa fa-play"></i> Sesli Önizle
                              </button>
                              <button type="button" id="kampanyaSesDurdur" class="kss-btn kss-btn-stop" style="display:none;">
                                 <i class="fa fa-stop"></i> Durdur
                              </button>
                              <div class="kss-voice-wrap">
                                 <label class="kss-voice-label" title="Sesi değiştir">
                                    <i class="fa fa-microphone"></i>
                                    <select id="kampanyaSesSecici" class="kss-voice-select"></select>
                                 </label>
                                 <span class="kss-quality-badge" id="kssQualityBadge"></span>
                              </div>
                              <audio id='kampanyaSesKaydiCal' controls preload="none" style="display:none;">
                                 <source src="" id='calinacak_kayit' type="audio/wav">
                              </audio>
                           </div>
                        </div>
                        <div class="col-12 col-md-3">
                           <button id="gorevTanimla" type="button" class="btn btn-success btn-block">
                              Görev Tanımla
                           </button>
                        </div>
                     </div>
                  </div>

                  <style>
                  #yeni_kampanya_modal .kss-player {
                     display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
                     padding: 10px 14px; background: linear-gradient(180deg,#f8fafc,#fff);
                     border: 1px solid #e2e8f0; border-radius: 12px;
                  }
                  #yeni_kampanya_modal .kss-btn {
                     display: inline-flex; align-items: center; gap: 6px;
                     padding: 9px 16px; border-radius: 999px;
                     font-size: 13px; font-weight: 700; cursor: pointer;
                     border: none; transition: all .15s ease;
                  }
                  #yeni_kampanya_modal .kss-btn-play {
                     background: linear-gradient(135deg,#10b981,#059669); color:#fff;
                     box-shadow: 0 4px 12px rgba(5,150,105,.28);
                  }
                  #yeni_kampanya_modal .kss-btn-play:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(5,150,105,.36); }
                  #yeni_kampanya_modal .kss-btn-play.is-playing {
                     background: linear-gradient(135deg,#f59e0b,#d97706);
                     animation: kssPulse 1.2s ease-in-out infinite;
                  }
                  #yeni_kampanya_modal .kss-btn-stop {
                     background:#fff; color:#dc2626; border:1.5px solid #fecaca;
                  }
                  #yeni_kampanya_modal .kss-btn-stop:hover { background:#fef2f2; }
                  /* Voice selector */
                  #yeni_kampanya_modal .kss-voice-wrap {
                     flex: 1; min-width: 220px; display: flex; align-items: center; gap: 8px;
                  }
                  #yeni_kampanya_modal .kss-voice-label {
                     position: relative; display: flex; align-items: center; gap: 6px;
                     background: #fff; border: 1.5px solid #e2e8f0; border-radius: 999px;
                     padding: 4px 10px 4px 12px; cursor: pointer; flex: 1; min-width: 0;
                     transition: all .15s ease;
                  }
                  #yeni_kampanya_modal .kss-voice-label:hover { border-color: #c4b5fd; }
                  #yeni_kampanya_modal .kss-voice-label i { color: #7B2FB8; font-size: 12px; flex-shrink: 0; }
                  #yeni_kampanya_modal .kss-voice-select {
                     border: none; background: transparent; outline: none;
                     font-size: 12.5px; color: #1e293b; font-weight: 600;
                     cursor: pointer; padding: 4px 4px;
                     flex: 1; min-width: 0;
                     -webkit-appearance: none; -moz-appearance: none; appearance: auto;
                  }
                  #yeni_kampanya_modal .kss-quality-badge {
                     display: none; padding: 3px 9px; border-radius: 999px;
                     font-size: 10.5px; font-weight: 700; flex-shrink: 0;
                  }
                  #yeni_kampanya_modal .kss-quality-badge.kss-q-online {
                     display: inline-block; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;
                  }
                  #yeni_kampanya_modal .kss-quality-badge.kss-q-offline {
                     display: inline-block; background: #fef3c7; color: #92400e; border: 1px solid #fde68a;
                  }
                  @keyframes kssPulse {
                     0%, 100% { box-shadow: 0 4px 12px rgba(217,119,6,.36); }
                     50%      { box-shadow: 0 6px 22px rgba(217,119,6,.55); }
                  }
                  </style>

                  <script>
                  (function(){
                     // -------- VIRTUAL VOICES (TTS Proxy üzerinden — sunucu fallback yapar) --------
                     // Polly'de Türkçe sadece "Filiz" var. Farklı tonlar SSML rate/pitch ile sağlanır.
                     // Google Translate TTS ek seçenek olarak (200 char limit).
                     var VIRTUAL_VOICES = [
                        { id:'sev_filiz_normal', voice:'filiz_normal', ad:'Filiz · Doğal Konuşma',   gender:'kadın' },
                        { id:'sev_filiz_fast',   voice:'filiz_fast',   ad:'Filiz · Hızlı / Canlı',   gender:'kadın' },
                        { id:'sev_filiz_high',   voice:'filiz_high',   ad:'Filiz · Yüksek Ton',      gender:'kadın' },
                        { id:'sev_filiz_low',    voice:'filiz_low',    ad:'Filiz · Kalın Ton',       gender:'erkeksi' },
                        { id:'sev_filiz_slow',   voice:'filiz_slow',   ad:'Filiz · Sakin / Yavaş',   gender:'kadın' },
                        { id:'sev_google',       voice:'google',       ad:'Google Türkçe (200char)', gender:'kadın' }
                     ];
                     function virtualUrl(v, metin){
                        // Backend proxy: CORS/SSL/availability sorunlarını önler,
                        // sağlayıcılar arası fallback yapar.
                        var t = metin.substring(0, 1500);
                        return '/isletmeyonetim/tts-proxy?voice=' + encodeURIComponent(v.voice || 'Filiz') +
                               '&q=' + encodeURIComponent(t) +
                               '&_=' + Date.now();
                     }
                     function virtualBul(id){ return VIRTUAL_VOICES.find(function(v){ return v.id === id; }); }

                     // -------- BROWSER VOICES --------
                     var hasSpeech = ('speechSynthesis' in window);
                     function tumBrowserSesleri(){
                        if(!hasSpeech) return [];
                        try { return (speechSynthesis.getVoices() || []).filter(function(v){ return /^tr/i.test(v.lang); }); }
                        catch(_) { return []; }
                     }

                     var seciliVoiceURI = localStorage.getItem('kss_voice_uri') || 'sev_filiz_fast';

                     function selectorDoldur(){
                        var $sel = $('#kampanyaSesSecici');
                        if(!$sel.length) return;
                        var html = '';

                        // Önce: yüksek kalite (network, Polly tabanlı)
                        html += '<optgroup label="✨ Doğal Sesler (Önerilen)">';
                        VIRTUAL_VOICES.forEach(function(v){
                           html += '<option value="'+ v.id +'">'+ v.ad +'</option>';
                        });
                        html += '</optgroup>';

                        // Sonra: tarayıcı yerel Türkçe sesleri (offline)
                        var brSesler = tumBrowserSesleri();
                        if(brSesler.length){
                           html += '<optgroup label="📱 Cihaz Sesleri (Çevrimdışı)">';
                           brSesler.forEach(function(v){
                              html += '<option value="br_'+ v.voiceURI +'">'+ v.name +'</option>';
                           });
                           html += '</optgroup>';
                        }

                        $sel.prop('disabled', false).html(html);

                        // Seçili
                        if(seciliVoiceURI && $sel.find('option[value="'+ seciliVoiceURI +'"]').length){
                           $sel.val(seciliVoiceURI);
                        } else {
                           $sel.val('sev_filiz_fast');
                           seciliVoiceURI = 'sev_filiz_fast';
                        }
                        qualityBadge();
                     }

                     function qualityBadge(){
                        var v = $('#kampanyaSesSecici').val() || '';
                        var $b = $('#kssQualityBadge');
                        if(v.indexOf('sev_') === 0){
                           $b.removeClass('kss-q-offline').addClass('kss-q-online').text('✨ Yüksek Kalite');
                        } else {
                           $b.removeClass('kss-q-online').addClass('kss-q-offline').text('Cihaz Sesi');
                        }
                     }

                     $(document).on('change','#kampanyaSesSecici',function(){
                        seciliVoiceURI = $(this).val();
                        try { localStorage.setItem('kss_voice_uri', seciliVoiceURI); } catch(_) {}
                        qualityBadge();
                     });

                     if(hasSpeech){
                        speechSynthesis.onvoiceschanged = selectorDoldur;
                     }
                     selectorDoldur();
                     setTimeout(selectorDoldur, 250);
                     setTimeout(selectorDoldur, 800);

                     // -------- Çalma kontrol --------
                     function getMetin(){
                        var t = $('#kampanyaPrompt').text() || '';
                        return t.replace(/\s+/g,' ').trim();
                     }
                     var aktifUtt = null;
                     var $audio = $('#kampanyaSesKaydiCal');
                     function durdur(){
                        try { if(hasSpeech) speechSynthesis.cancel(); } catch(_) {}
                        try {
                           var a = $audio[0];
                           if(a){ a.pause(); a.currentTime = 0; }
                        } catch(_) {}
                        aktifUtt = null;
                        $('#kampanyaSesOku').removeClass('is-playing').html('<i class="fa fa-play"></i> Sesli Önizle');
                        $('#kampanyaSesDurdur').hide();
                     }
                     function calmaBaslat(){
                        $('#kampanyaSesOku').addClass('is-playing').html('<i class="fa fa-pause"></i> Okunuyor...');
                        $('#kampanyaSesDurdur').show();
                     }

                     $(document).on('click','#kampanyaSesOku',function(e){
                        e.preventDefault();
                        var metin = getMetin();
                        if(!metin){
                           if(typeof swal === 'function') swal({type:'info',title:'Metin yok',text:'Önce bir şablon seçin veya kampanya metnini yazın.',timer:2500,showConfirmButton:false});
                           return;
                        }
                        durdur();
                        var sec = $('#kampanyaSesSecici').val() || 'sev_filiz_fast';

                        if(sec.indexOf('sev_') === 0){
                           // Harici API → audio src ile çal
                           var v = virtualBul(sec);
                           if(!v){ return; }
                           var url = virtualUrl(v, metin);
                           var a = $audio[0];
                           if(!a){ return; }
                           $('#calinacak_kayit').attr('src', url);
                           a.load();
                           a.onplaying = calmaBaslat;
                           a.onended = a.onerror = durdur;
                           var p = a.play();
                           if(p && p.catch) p.catch(function(){
                              durdur();
                              if(typeof swal === 'function') swal({type:'warning',title:'Ses yüklenemedi',text:'İnternet bağlantısını veya seçili sesi kontrol edin.',timer:3000,showConfirmButton:false});
                           });
                           return;
                        }

                        // Cihaz sesi (browser SpeechSynthesis)
                        if(!hasSpeech){
                           if(typeof swal === 'function') swal({type:'warning',title:'Desteklenmiyor',text:'Tarayıcınız cihaz sesini desteklemiyor.',timer:3000,showConfirmButton:false});
                           return;
                        }
                        var brURI = sec.replace(/^br_/, '');
                        var brVoice = (speechSynthesis.getVoices() || []).find(function(v){ return v.voiceURI === brURI; });
                        aktifUtt = new SpeechSynthesisUtterance(metin);
                        if(brVoice){ aktifUtt.voice = brVoice; aktifUtt.lang = brVoice.lang; }
                        else aktifUtt.lang = 'tr-TR';
                        aktifUtt.rate = 0.98; aktifUtt.pitch = 1.05; aktifUtt.volume = 1.0;
                        aktifUtt.onstart = calmaBaslat;
                        aktifUtt.onend = aktifUtt.onerror = durdur;
                        speechSynthesis.speak(aktifUtt);
                     });
                     $(document).on('click','#kampanyaSesDurdur',function(e){ e.preventDefault(); durdur(); });
                     $('#yeni_kampanya_modal').on('hidden.bs.modal', durdur);
                  })();
                  </script>
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

      /* ============ PRESET KİTLE KARTLARI ============ */
      #yeni_kampanya_modal .rkp-cinsiyet-bar {
         display: flex; align-items: center; gap: 12px;
         padding: 10px 14px; background: #fff; border: 1px solid #e2e8f0;
         border-radius: 10px; margin-bottom: 14px;
      }
      #yeni_kampanya_modal .rkp-cinsiyet-label {
         font-size: 12px; font-weight: 700; color: #475569;
         text-transform: uppercase; letter-spacing: .3px;
      }
      #yeni_kampanya_modal .rkp-segment--mini {
         display: inline-flex; flex: 0 0 auto;
         border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;
      }
      #yeni_kampanya_modal .rkp-segment--mini .rkp-seg-btn { padding: 6px 14px; font-size: 12.5px; }

      #yeni_kampanya_modal .rkp-presets {
         display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;
         margin-bottom: 4px;
      }
      #yeni_kampanya_modal .rkp-preset {
         position: relative; text-align: left;
         display: flex; flex-direction: column; gap: 4px;
         padding: 14px 14px 12px; border-radius: 12px;
         background: #fff; border: 2px solid #e2e8f0;
         cursor: pointer; transition: all .18s ease;
         min-height: 96px;
      }
      #yeni_kampanya_modal .rkp-preset:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15,23,42,.08); border-color: #cbd5e1; }
      #yeni_kampanya_modal .rkp-preset-emoji { font-size: 22px; line-height: 1; margin-bottom: 4px; }
      #yeni_kampanya_modal .rkp-preset-baslik { font-weight: 700; font-size: 13.5px; color: #1e293b; line-height: 1.2; }
      #yeni_kampanya_modal .rkp-preset-aciklama { font-size: 11.5px; color: #64748b; line-height: 1.3; }

      /* Aktif kart tema renkleri */
      #yeni_kampanya_modal .rkp-preset.is-active::after {
         content: "\f00c"; font-family: "Font Awesome 5 Free"; font-weight: 900;
         position: absolute; top: 10px; right: 12px; font-size: 12px;
         width: 22px; height: 22px; border-radius: 50%;
         display: flex; align-items: center; justify-content: center;
         color: #fff;
      }
      #yeni_kampanya_modal .rkp-preset--all.is-active     { background: linear-gradient(180deg, #faf5ff, #fff); border-color: #7B2FB8; box-shadow: 0 8px 22px rgba(123,47,184,.16); }
      #yeni_kampanya_modal .rkp-preset--all.is-active::after     { background: #7B2FB8; }
      #yeni_kampanya_modal .rkp-preset--loyal.is-active   { background: linear-gradient(180deg, #fef3c7, #fff); border-color: #d97706; box-shadow: 0 8px 22px rgba(217,119,6,.16); }
      #yeni_kampanya_modal .rkp-preset--loyal.is-active::after   { background: #d97706; }
      #yeni_kampanya_modal .rkp-preset--active.is-active  { background: linear-gradient(180deg, #ecfdf5, #fff); border-color: #059669; box-shadow: 0 8px 22px rgba(5,150,105,.16); }
      #yeni_kampanya_modal .rkp-preset--active.is-active::after  { background: #059669; }
      #yeni_kampanya_modal .rkp-preset--winback.is-active { background: linear-gradient(180deg, #fee2e2, #fff); border-color: #dc2626; box-shadow: 0 8px 22px rgba(220,38,38,.16); }
      #yeni_kampanya_modal .rkp-preset--winback.is-active::after { background: #dc2626; }
      #yeni_kampanya_modal .rkp-preset--recent.is-active  { background: linear-gradient(180deg, #ecfeff, #fff); border-color: #0284c7; box-shadow: 0 8px 22px rgba(2,132,199,.16); }
      #yeni_kampanya_modal .rkp-preset--recent.is-active::after  { background: #0284c7; }
      #yeni_kampanya_modal .rkp-preset--service.is-active { background: linear-gradient(180deg, #fdf4ff, #fff); border-color: #c026d3; box-shadow: 0 8px 22px rgba(192,38,211,.16); }
      #yeni_kampanya_modal .rkp-preset--service.is-active::after { background: #c026d3; }
      #yeni_kampanya_modal .rkp-preset--product.is-active { background: linear-gradient(180deg, #fff7ed, #fff); border-color: #ea580c; box-shadow: 0 8px 22px rgba(234,88,12,.16); }
      #yeni_kampanya_modal .rkp-preset--product.is-active::after { background: #ea580c; }
      #yeni_kampanya_modal .rkp-preset--group.is-active   { background: linear-gradient(180deg, #eef2ff, #fff); border-color: #4f46e5; box-shadow: 0 8px 22px rgba(79,70,229,.16); }
      #yeni_kampanya_modal .rkp-preset--group.is-active::after   { background: #4f46e5; }

      /* Ek panel */
      #yeni_kampanya_modal .rkp-preset-extra {
         margin-top: 12px; padding: 14px; border-radius: 10px;
         background: #fafbff; border: 1px dashed #c7d2fe;
         animation: rkpFadeIn .25s ease;
      }
      @keyframes rkpFadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }

      /* Step 1 → Step 2 (kategori → seçim) */
      #yeni_kampanya_modal .rkp-step-grid {
         display: grid;
         grid-template-columns: 1fr 28px 1fr;
         gap: 10px;
         align-items: end;
      }
      #yeni_kampanya_modal .rkp-step {
         display: flex; gap: 10px;
         padding: 10px; background: #fff;
         border-radius: 10px; border: 1px solid #e2e8f0;
      }
      #yeni_kampanya_modal .rkp-step-num {
         width: 28px; height: 28px; border-radius: 50%;
         background: #5C008E; color: #fff;
         display: flex; align-items: center; justify-content: center;
         font-size: 12px; font-weight: 700; flex-shrink: 0;
      }
      #yeni_kampanya_modal .rkp-step-body { flex: 1; min-width: 0; }
      #yeni_kampanya_modal .rkp-step-arrow {
         display: flex; align-items: center; justify-content: center;
         color: #94a3b8; font-size: 14px;
      }
      #yeni_kampanya_modal .rkp-step .rkp-label { margin-bottom: 6px; }
      #yeni_kampanya_modal .rkp-step .rkp-select { height: 38px; font-size: 13px; }
      #yeni_kampanya_modal .rkp-step .rkp-select:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }

      @media (max-width: 575px) {
         #yeni_kampanya_modal .rkp-step-grid { grid-template-columns: 1fr; }
         #yeni_kampanya_modal .rkp-step-arrow { transform: rotate(90deg); }
      }

      /* Yeni Grup Oluştur butonu */
      #yeni_kampanya_modal .rkp-grup-header {
         display: flex; align-items: center; justify-content: space-between;
         margin-bottom: 8px; gap: 10px;
      }
      #yeni_kampanya_modal .rkp-grup-header .rkp-label { margin-bottom: 0; }
      #yeni_kampanya_modal .rkp-yeni-grup-btn {
         display: inline-flex; align-items: center; gap: 6px;
         padding: 7px 14px; border-radius: 8px;
         background: linear-gradient(135deg,#10b981,#059669);
         color: #fff; border: none; font-size: 12.5px; font-weight: 600;
         cursor: pointer; transition: transform .15s ease, box-shadow .15s ease;
         box-shadow: 0 4px 12px rgba(5,150,105,.22);
      }
      #yeni_kampanya_modal .rkp-yeni-grup-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(5,150,105,.32); }
      #yeni_kampanya_modal .rkp-yeni-grup-btn i { font-size: 11px; }
      #yeni_kampanya_modal .rkp-grup-empty {
         margin-top: 10px; padding: 14px;
         background: #fef3c7; color: #92400e;
         border: 1px dashed #fbbf24; border-radius: 8px;
         font-size: 12.5px; line-height: 1.5;
      }
      #yeni_kampanya_modal .rkp-grup-empty i { margin-right: 4px; }

      /* Responsive */
      @media (max-width: 991px) {
         #yeni_kampanya_modal .rkp-presets { grid-template-columns: repeat(2, 1fr); }
      }
      @media (max-width: 575px) {
         #yeni_kampanya_modal .rkp-presets { grid-template-columns: 1fr; }
         #yeni_kampanya_modal .rkp-cinsiyet-bar { flex-wrap: wrap; }
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
            // Preset kartları → "Tüm Müşterilerim" aktif, ek paneller kapalı
            $m.find('.rkp-preset').removeClass('is-active');
            $m.find('.rkp-preset[data-preset="all"]').addClass('is-active');
            $m.find('#rkpPresetHizmet, #rkpPresetUrun, #rkpPresetGrup').hide();
            // 2 kademeli step select'leri sıfırla
            $m.find('#rkpHizmetKategoriSec, #rkpUrunKategoriSec').val('');
            $m.find('#rkpHizmetSec').empty().append('<option value="">Önce kategori seçin</option>').prop('disabled', true);
            $m.find('#rkpUrunSec').empty().append('<option value="">Önce kategori seçin</option>').prop('disabled', true);
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

         // ---- PRESET KİTLE KARTLARI ----
         // Her preset gizli select'leri otomatik ayarlar.
         var rkpPresets = {
            // gelenGelmeyenMusteri | musteriGruplari | hizmetUrunPaket | kampanyaKategori
            'all':     { gelen: '',  grup: '',   hizmet: '',  kat: '',  show:'' },
            'sadik':   { gelen: '6', grup: '',   hizmet: '',  kat: '',  show:'' },
            'aktif':   { gelen: '7', grup: '',   hizmet: '',  kat: '',  show:'' },
            'pasif':   { gelen: '8', grup: '',   hizmet: '',  kat: '',  show:'' },
            'son1yil': { gelen: '1', grup: '',   hizmet: '',  kat: '',  show:'' },
            'hizmet':  { gelen: '',  grup: '',   hizmet: '',  kat: '',  show:'hizmet' },
            'urun':    { gelen: '',  grup: '',   hizmet: '',  kat: '',  show:'urun'   },
            'grup':    { gelen: '',  grup: null, hizmet: '',  kat: '',  show:'grup'   }
         };

         function rkpSelectSet(id, value){
            var $s = $('#'+id);
            if(!$s.length) return;
            // null = "kullanıcı kendisi seçecek, dokunma"
            if(value === null) return;
            $s.val(value);
            if($s.hasClass('select2-hidden-accessible')) $s.trigger('change.select2');
            $s.trigger('change');
         }

         // ---- 2 KADEMELİ SELECTLER (kategori → hizmet/ürün) ----
         function rkpHizmetKategorileriYukle(){
            var $sel = $('#rkpHizmetKategoriSec');
            $sel.find('option:not(:first)').remove();
            (window.RKP_DATA && window.RKP_DATA.hizmetKategoriler || []).forEach(function(k){
               $sel.append('<option value="'+k.id+'">'+ $('<div>').text(k.ad).html() +'</option>');
            });
         }
         function rkpUrunKategorileriYukle(){
            var $sel = $('#rkpUrunKategoriSec');
            $sel.find('option:not(:first)').remove();
            (window.RKP_DATA && window.RKP_DATA.urunKategoriler || []).forEach(function(k){
               $sel.append('<option value="'+k.id+'">'+ $('<div>').text(k.ad).html() +'</option>');
            });
         }
         function rkpHizmetleriYukleByKategori(katId){
            var $sel = $('#rkpHizmetSec');
            $sel.find('option').remove();
            if(!katId){
               $sel.append('<option value="">Önce kategori seçin</option>').prop('disabled', true);
               return;
            }
            $sel.append('<option value="">Hizmet seçin...</option>');
            var liste = (window.RKP_DATA && window.RKP_DATA.hizmetler || []).filter(function(h){
               return String(h.kategori_id) === String(katId);
            });
            if(liste.length === 0){
               $sel.append('<option value="" disabled>Bu kategoride hizmet yok</option>');
            } else {
               liste.forEach(function(h){
                  $sel.append('<option value="'+h.id+'">'+ $('<div>').text(h.ad).html() +'</option>');
               });
            }
            $sel.prop('disabled', false);
         }
         function rkpUrunleriYukleByKategori(katId){
            var $sel = $('#rkpUrunSec');
            $sel.find('option').remove();
            var data = (window.RKP_DATA && window.RKP_DATA.urunler || []);
            // Eğer ürün-kategori ilişkisi yoksa, tüm ürünleri göster (kategori seçimi sembolik)
            var iliskiYok = !(window.RKP_DATA && window.RKP_DATA.urunKategoriIliskisiVar);
            if(!katId && !iliskiYok){
               $sel.append('<option value="">Önce kategori seçin</option>').prop('disabled', true);
               return;
            }
            $sel.append('<option value="">Ürün seçin...</option>');
            var liste = iliskiYok ? data : data.filter(function(u){
               return String(u.kategori_id) === String(katId);
            });
            if(liste.length === 0){
               $sel.append('<option value="" disabled>Bu kategoride ürün yok</option>');
            } else {
               liste.forEach(function(u){
                  $sel.append('<option value="urun-'+u.id+'">'+ $('<div>').text(u.ad).html() +'</option>');
               });
            }
            $sel.prop('disabled', false);
         }

         // Hizmet kategori değiştiğinde
         $(document).on('change','#rkpHizmetKategoriSec',function(){
            var katId = $(this).val();
            rkpHizmetleriYukleByKategori(katId);
            // Gizli #kampanyaKategori'yi senkronize et (sadece sayısal ID — hizmet kategorisi)
            rkpSelectSet('kampanyaKategori', katId || '');
            // Hizmet seçimini sıfırla
            rkpSelectSet('hizmetUrunPaket', '');
         });
         // Hizmet seçildiğinde
         $(document).on('change','#rkpHizmetSec',function(){
            var hId = $(this).val();
            rkpSelectSet('hizmetUrunPaket', hId || '');
         });
         // Ürün kategori değiştiğinde
         $(document).on('change','#rkpUrunKategoriSec',function(){
            var katId = $(this).val();
            rkpUrunleriYukleByKategori(katId);
            // Gizli #kampanyaKategori'yi "urun-X" formatında senkronize et
            rkpSelectSet('kampanyaKategori', katId ? 'urun-'+katId : '');
            rkpSelectSet('hizmetUrunPaket', '');
         });
         // Ürün seçildiğinde
         $(document).on('change','#rkpUrunSec',function(){
            var uVal = $(this).val(); // zaten "urun-X" formatında
            rkpSelectSet('hizmetUrunPaket', uVal || '');
         });

         $(document).on('click','#yeni_kampanya_modal .rkp-preset',function(e){
            e.preventDefault();
            var presetId = $(this).data('preset');
            var p = rkpPresets[presetId];
            if(!p) return;
            // Aktif görseli
            $('#yeni_kampanya_modal .rkp-preset').removeClass('is-active');
            $(this).addClass('is-active');
            // Ek paneller — yalnızca ilgili olan açık
            $('#rkpPresetHizmet').toggle(p.show === 'hizmet');
            $('#rkpPresetUrun').toggle(p.show === 'urun');
            $('#rkpPresetGrup').toggle(p.show === 'grup');
            // Gizli select'leri ayarla (null = dokunma)
            rkpSelectSet('gelenGelmeyenMusteri', p.gelen);
            rkpSelectSet('musteriGruplari',      p.grup);
            rkpSelectSet('hizmetUrunPaket',      p.hizmet);
            rkpSelectSet('kampanyaKategori',     p.kat);

            // 2-kademeli select'leri hazırla
            if(p.show === 'hizmet'){
               rkpHizmetKategorileriYukle();
               $('#rkpHizmetKategoriSec').val('');
               rkpHizmetleriYukleByKategori('');
            } else if(p.show === 'urun'){
               rkpUrunKategorileriYukle();
               $('#rkpUrunKategoriSec').val('');
               rkpUrunleriYukleByKategori('');
            }
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

         // ---- YENİ GRUP OLUŞTUR (preset Grup paneli içindeki buton) ----
         $(document).on('click','#rkpYeniGrupBtn',function(e){
            e.preventDefault();
            // Reklam modalını geçici kapat, kapanınca grup ekle modal aç
            sessionStorage.setItem('rkpReklamGeriDon','1');
            $('#yeni_kampanya_modal').modal('hide');
         });

         // Reklam modal'ı kapanınca: grup ekle modalını açmamız gerekiyor mu?
         $('#yeni_kampanya_modal').on('hidden.bs.modal.rkpgrup', function(){
            if(sessionStorage.getItem('rkpReklamGeriDon') === '1'){
               // Reset zaten çalıştı; önce flag'i tut, grup ekle modalını aç
               setTimeout(function(){
                  if(typeof modalbaslikata === 'function') {
                     try { modalbaslikata('Yeni Grup','grup_sms_formu'); } catch(_) {}
                  }
                  $('#grup_sms_olustur_modal').modal('show');
               }, 350);
            }
         });

         // Grup ekle modalı kapandığında: reklam modalına geri dön
         $(document).on('hidden.bs.modal','#grup_sms_olustur_modal',function(){
            if(sessionStorage.getItem('rkpReklamGeriDon') === '1'){
               sessionStorage.removeItem('rkpReklamGeriDon');
               // Geri dönerken yeni eklenen grupları da yansıt
               var yeniId  = sessionStorage.getItem('rkpYeniGrupId');
               var yeniAdi = sessionStorage.getItem('rkpYeniGrupAdi');
               sessionStorage.removeItem('rkpYeniGrupId');
               sessionStorage.removeItem('rkpYeniGrupAdi');
               setTimeout(function(){
                  $('#yeni_kampanya_modal').modal('show');
                  setTimeout(function(){
                     // "Özel Grubum" preset'ine dön ve yeni grubu seç
                     if(yeniId){
                        var v = 'haricigrup-'+yeniId;
                        if($('#musteriGruplari option[value="'+v+'"]').length === 0){
                           $('#musteriGruplari').append('<option value="'+v+'">'+ $('<div>').text(yeniAdi || ('Grup #'+yeniId)).html() +'</option>');
                           // empty state'i kaldır (varsa)
                           $('#yeni_kampanya_modal .rkp-grup-empty').remove();
                        }
                        $('#yeni_kampanya_modal .rkp-preset[data-preset="grup"]').trigger('click');
                        $('#musteriGruplari').val(v).trigger('change');
                     }
                  }, 300);
               }, 250);
            }
         });

         // Grup ekle başarılı response yakala — yeni grup ID'sini sakla
         // (mevcut $.ajax'a hook olamadığımız için $(document).ajaxSuccess kullan)
         $(document).on('ajaxSuccess', function(event, xhr, settings){
            if(!settings || !settings.url) return;
            if(settings.url.indexOf('/isletmeyonetim/grupsmsekle') === -1) return;
            try {
               var resp = (typeof xhr.responseJSON === 'object' && xhr.responseJSON) ? xhr.responseJSON : JSON.parse(xhr.responseText);
               if(resp && resp.id){
                  sessionStorage.setItem('rkpYeniGrupId', resp.id);
                  sessionStorage.setItem('rkpYeniGrupAdi', resp.grupAdi || resp.grup_adi || '');
               }
            } catch(_) {}
         });
      })();
      </script>
   </div>