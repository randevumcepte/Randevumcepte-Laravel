@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="row pb-10">
               @if($kullaniciRolu < 5)
              <div class="col-xl-4 col-lg-4  col-md-4 col-sm-4 col-xs-6 col-6 mb-20">
                  <div class="card-box height-100-p widget-style3">
                     <div class="d-flex flex-wrap">
                        <div class="widget-data">
                           <div class="weight-700 font-24 text-dark" id='gelen_arama_sayisi'>{{$santral_raporlari['gelen_arama']}}</div>
                           <div class="font-14 text-secondary weight-500">
                               Gelen Arama
                           </div>
                        </div>
                        <div class="widget-icon" style="background-color: #28a745">
                           <div class="icon" data-color="#fff">
                              <i class="icon-copy bi bi-telephone-inbound-fill"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-xl-4 col-lg-4  col-md-4 col-sm-4 col-xs-6 col-6 mb-20">
                  <div class="card-box height-100-p widget-style3">
                     <div class="d-flex flex-wrap">
                        <div class="widget-data">
                           <div class="weight-700 font-24 text-dark" id='giden_arama_sayisi'>{{$santral_raporlari['giden_arama']}}</div>
                           <div class="font-14 text-secondary weight-500">
                              Giden Arama
                           </div>
                        </div>
                        <div class="widget-icon" style="background-color:rgb(146, 0, 188)">
                           <div class="icon" data-color="#fff">
                              
                             <i class="icon-copy bi bi-telephone-outbound-fill"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
                
               <div class="col-xl-4 col-lg-4  col-md-4 col-sm-4 col-xs-12 col-12 mb-20">
                  <div class="card-box height-100-p widget-style3">
                     <div class="d-flex flex-wrap">
                        <div class="widget-data">
                           <div class="weight-700 font-24 text-dark" id='cevapsiz_arama_sayisi'>{{$santral_raporlari['cevapsiz_arama']}}</div>
                           <div class="font-14 text-secondary weight-500">
                            Cevapsız Arama
                           </div>
                        </div>
                        <div class="widget-icon" style="background-color: #ff0000 ">
                           <div class="icon"  data-color="#fff">
                               <i class="icon-copy bi bi-telephone-x-fill"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               
               <div class="col-xl-3 col-lg-3  col-md-6 col-sm-6 col-xs-6 col-6 mb-20" style="display:none">
                  <div class="card-box height-100-p widget-style3">
                     <div class="d-flex flex-wrap">
                        <div class="widget-data">
                           <div class="weight-700 font-24 text-dark" id='ses_kayitlari'>0</div>
                           <div class="font-14 text-secondary weight-500">Sesli Mesaj</div>
                        </div>
                        <div class="widget-icon" style="background-color:rgb(234, 67, 242)">
                           <div class="icon" data-color="#fff">
                              
                              <i class="icon-copy bi bi-mic-fill"></i>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               @endif

               @if(!empty($dakika))
               @php
                   $dk_personel = !empty($dakika['dahili']);
                   $dk_yuzde = (int)($dakika['yuzde'] ?? 0);
                   $dk_renk = $dk_yuzde >= 90 ? '#ff0000' : ($dk_yuzde >= 70 ? '#f59e0b' : '#28a745');
               @endphp
               <div class="col-lg-12 col-md-12 col-sm-12 mb-20">
                  <div class="pd-20 card-box">
                     @if($dk_personel)
                        {{-- Personel: yalnizca kendi dahilisinin giden konusma dakikasi --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                           <div>
                              <div class="font-14 text-secondary weight-500">Bu dönem giden konuşma sürem (Dahili {{ $dakika['dahili'] }})</div>
                              <div class="weight-700 font-24 text-dark">{{ number_format($dakika['kullanilan'], 1, ',', '.') }} <span class="font-14 text-secondary">dk</span></div>
                              <div class="font-13 text-secondary">{{ $dakika['adet'] }} giden çağrı · {{ date('d.m.Y', strtotime($dakika['sayim_baslangic'])) }} tarihinden bugüne</div>
                           </div>
                           <div class="widget-icon" style="background-color:rgb(146, 0, 188)">
                              <div class="icon" data-color="#fff"><i class="icon-copy bi bi-stopwatch-fill"></i></div>
                           </div>
                        </div>
                     @else
                        {{-- Salon geneli: tanimli / kullanilan / kalan + ilerleme --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-15">
                           <div class="font-16 weight-600 text-dark"><i class="bi bi-stopwatch-fill"></i> Dakika Kullanımı <span class="font-13 text-secondary weight-400">(giden — havuz)</span></div>
                           <div class="font-13 text-secondary">{{ date('d.m.Y', strtotime($dakika['sayim_baslangic'])) }} tarihinden bugüne · {{ $dakika['adet'] }} giden çağrı</div>
                        </div>
                        <div class="row">
                           <div class="col-4 text-center">
                              <div class="weight-700 font-24 text-dark">{{ number_format($dakika['tanimli'], 0, ',', '.') }}</div>
                              <div class="font-13 text-secondary weight-500">Tanımlı (dk)</div>
                           </div>
                           <div class="col-4 text-center">
                              <div class="weight-700 font-24 text-dark">{{ number_format($dakika['kullanilan'], 1, ',', '.') }}</div>
                              <div class="font-13 text-secondary weight-500">Kullanılan (dk)</div>
                           </div>
                           <div class="col-4 text-center">
                              <div class="weight-700 font-24" style="color:{{ $dakika['kalan'] < 0 ? '#ff0000' : '#28a745' }}">{{ number_format($dakika['kalan'], 1, ',', '.') }}</div>
                              <div class="font-13 text-secondary weight-500">Kalan (dk)</div>
                           </div>
                        </div>
                        @if($dakika['tanimli'] > 0)
                        <div style="margin-top:15px;height:10px;border-radius:6px;background:#eceff4;overflow:hidden">
                           <div style="height:100%;width:{{ $dk_yuzde }}%;background:{{ $dk_renk }};border-radius:6px"></div>
                        </div>
                        <div class="font-13 text-secondary text-right" style="margin-top:5px">%{{ $dk_yuzde }}</div>
                        @else
                        <div class="font-13 text-secondary text-center" style="margin-top:12px">Bu işletme için henüz tanımlı dakika girilmemiş.</div>
                        @endif
                     @endif
                     @if(!empty($dakika['hata']))
                        <div class="font-12" style="color:#ff0000;margin-top:8px">Dakika verisi şu an alınamadı, lütfen daha sonra tekrar deneyin.</div>
                     @endif
                  </div>
               </div>
               @endif

               <div class="col-lg-12 col-md-12 col-sm-12 mb-30">

                     <div class="pd-20 card-box">
                         @if($kullaniciRolu < 5)
                        <div class="row">
                           <div class="col-sm-4 col-xs-6 col-6 col-md-4">
                              <div class="form-group">
                                 <label>Rapor Başlangıç Tarihi</label>
                                 <input type="text" id="cdr_baslangic_tarihi" value="{{date('Y-m-d')}}" class="form-control">
                              </div>
                           </div>
                           <div class="col-sm-4 col-xs-6 col-6 col-md-4 ">
                                 <label>Rapor Bitiş Tarihi</label>
                                 <input type="text" id="cdr_bitis_tarihi" value="{{date('Y-m-d')}}" class="form-control">
                           </div>
                           <div class="col-sm-4 col-xs-6 col-6 col-md-4 text-right" >
                              <div class="form-group" >
                                    <label style="visibility:hidden">Rapor Bitiş Tarihi</label>
                                    {{-- "Arama Listesi Oluştur" butonu Çağrı Merkezi paneline taşındı (oradan yönetilir). --}}
                              </div>
                           </div>
                        </div>
                        @endif
                         <h2 style="margin-bottom:20px">Santral Özet</h2>
                        <div class="tab">
                        
                           <ul class="nav nav-tabs element" role="tablist" style="overflow-x: auto;  overflow-y: hidden; height: 50px;
                           
 

                           ">
                           @if($kullaniciRolu < 5)
                              <li class="nav-item">
                                 <button class="btn btn-outline-primary active" data-toggle="tab" href="#tum-aramalar" role="tab" aria-selected="true">Tümü</button>
                              </li>
                              <li class="nav-item" style="margin-left: 20px;display: inline-block;">
                                 <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#gelen-arama"
                                    role="tab"
                                    aria-selected="false"
                                    >Gelen Arama</button
                                 >
                              </li>
                              <li class="nav-item" style="margin-left: 20px;">
                                 <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#giden-arama"
                                    role="tab"
                                    aria-selected="false"
                                    >Giden Arama</button
                                 >
                              </li>
                              <li class="nav-item" style="margin-left: 20px;display: inline-block;">
                                 <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#cevapsiz-arama"
                                    role="tab"
                                    aria-selected="false"
                                    >Cevapsız Arama</button
                                 >
                              </li>
                              @endif
                              {{-- "Arama Listesi" sekmesi Çağrı Merkezi paneline taşındı (oradan yönetilir). --}}
                              @if($kullaniciRolu < 5)
                              <li class="nav-item" style="margin-left: 20px;display: none;">
                                 <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#konusma-paketleri"
                                    role="tab"
                                    aria-selected="false"
                                    >Paketler</button
                                 >
                              </li>
                              <li class="nav-item" style="margin-left: 20px;display: none;">
                                 <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#faturalar"
                                    role="tab"
                                    aria-selected="false"
                                    >Faturalar</button
                                 >
                              </li>
                              <li class="nav-item" style="margin-left: 20px;display: inline-block;">
                                 <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#santral-ayarlar"
                                    role="tab"
                                    aria-selected="false"
                                    >Ayarlar</button
                                 >
                              </li>
                              @endif
                            
                             
                              
                           </ul>
                          
                           <div class="tab-content">

                              <div
                                 class="tab-pane fade {{$kullaniciRolu < 5 ? 'show active' : ''}}"
                                 id="tum-aramalar"
                                 role="tabpanel" style="margin-top: 20px;"
                              >
                                      

                                    <table class="data-table table stripe hover nowrap" id="santral_arama_tum">
                                             <thead >
                                                <th>Müşteri</th>
                                                <th>Telefon Numarası</th>
                                                <th>Görüşme Yapan</th>
                                                 
                                                <th>Tarih</th>
                                                <th>Saat</th>
                                                <th>Durum </th>
                                             
                                                <th>Ses Kaydı & Arama</th>
                                                
                                             </thead>
                                             <tbody>
                                            
                                             </tbody>
                                    </table>
                                 
                              </div>
                              <div
                                 class="tab-pane fade"
                                 id="gelen-arama"
                                 role="tabpanel" style="margin-top: 20px; overflow-x: scroll;height: auto;">
                                 <div class="tab">
                                       <table class="data-table table stripe hover nowrap" id="santral_gelen_arama">
                                             <thead >
                                                <th>Müşteri</th>
                                                <th>Telefon Numarası</th>
                                                <th>Görüşme Yapan</th>
                                                 
                                                <th>Tarih</th>
                                                <th>Saat</th>
                                                <th>Durum </th>
                                             
                                                <th>Ses Kaydı & Arama</th>
                                                
                                             </thead>
                                             <tbody>
                                            
                                             </tbody>
                                    </table>
                                 </div>
                                 
                              </div>
                              <div
                                 class="tab-pane fade"
                                 id="giden-arama"
                                 role="tabpanel" style="margin-top: 20px;"
                              >
                                    <table class="data-table table stripe hover nowrap" id="santral_giden_arama">
                                             <thead >
                                                <th>Müşteri</th>
                                                <th>Telefon Numarası</th>
                                                <th>Görüşme Yapan</th>
                                                 
                                                <th>Tarih</th>
                                                <th>Saat</th>
                                                 
                                                <th>Durum </th>
                                                <th>Ses Kaydı & Arama</th>
                                                
                                             </thead>
                                             <tbody>
                                            
                                             </tbody>
                                    </table>
                                       
                                   
                                  
                              
                              </div>

                              <div
                                 class="tab-pane fade"
                                 id="cevapsiz-arama"
                                 role="tabpanel" style="margin-top: 20px;"
                              >
                                   <table class="data-table table stripe hover nowrap" id="santral_cevapsiz_arama">
                                             <thead >
                                                <th>Müşteri</th>
                                                <th>Telefon Numarası</th>
                                                <th>Görüşme Yapan</th>
                                                 
                                                <th>Tarih</th>
                                                <th>Saat</th>
                                                 
                                                <th>Durum </th>
                                                <th>Ses Kaydı & Arama</th>
                                                
                                             </thead>
                                             <tbody>
                                            
                                             </tbody>
                                    </table>
                                
                              
                              </div>
                              {{-- "Arama Listesi" sekme içeriği Çağrı Merkezi paneline taşındı. --}}
                              <div
                                 class="tab-pane fade"
                                 id="santral-ayarlar"
                                 role="tabpanel" style="margin-top: 20px;"
                              >
                                 <form id="santral_ayarlari" method="POST">
                                    {{csrf_field()}}
                                    <input  type="hidden" name="sube" value="{{$isletme->id}}">
                                    <div class="row" data-value="0">
                                       <div class=" col-md-4 col-sm-12 mb-30">
                                          <div class="pd-20 card-box  mb-10">
                                             <h6>Yaklaşan Randevu Hatırlatma</h6>
                                             <p style="font-weight: 5px;">Randevu hatırlatmalarına dair müşteriye otomatik hatırlatma araması yapılsın/yapılmasın ayarıdır.</p>
                                             <div class="row">
                                                <div class="col-md-12 custom-control custom-checkbox mb-5">

                                                   <input type="checkbox" class="custom-control-input" {{($santral_ayarlari[0]->musteri) ? 'checked' : ''}} name='santralayar_1_musteri' id="customCheck1">
                                                   <label class="custom-control-label" for="customCheck1"  > Açık / Kapalı</label>
                                                </div>
                                                 
                                             </div>
                                             <p style="font-weight: 5px;">Kaç saat önce aransın?</p>
                                             <select class="form-control" name="randevu_hatirlatama_saat_once" >
                                             <option {{($isletme->randevu_cagri_hatirlatma==1) ? 'selected' : ''}} value="1">1 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==2) ? 'selected' : ''}} value="2" selected="">2 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==3) ? 'selected' : ''}} value="3">3 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==4) ? 'selected' : ''}} value="4">4 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==5) ? 'selected' : ''}} value="5">5 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==6) ? 'selected' : ''}} value="6">6 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==7) ? 'selected' : ''}} value="7">7 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==8) ? 'selected' : ''}} value="8">8 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==9) ? 'selected' : ''}} value="9">9 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==10) ? 'selected' : ''}} value="10">10 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==11) ? 'selected' : ''}} value="11">11 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==12) ? 'selected' : ''}} value="12">12 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==13) ? 'selected' : ''}} value="13">13 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==14) ? 'selected' : ''}} value="14">14 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==15) ? 'selected' : ''}} value="15">15 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==16) ? 'selected' : ''}} value="16">16 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==17) ? 'selected' : ''}} value="17">17 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==18) ? 'selected' : ''}} value="18">18 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==19) ? 'selected' : ''}} value="19">19 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==20) ? 'selected' : ''}} value="20">20 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==21) ? 'selected' : ''}} value="21">21 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==22) ? 'selected' : ''}} value="22">22 saat</option>
                                             <option {{($isletme->randevu_cagri_hatirlatma==23) ? 'selected' : ''}} value="23">23 saat</option>
                                             </select>
                                          </div>
                                          <div class="col-md-12" style="margin-top: 80px;">
                                             <button type="submit" class="btn btn-success btn-block">Ayarları Güncelle</button>
                                          </div>
                                       </div>
                                    </div>
                                 </form>

                                 @php $ozelSecenekler = \App\Salonlar::santralOzelMenuNormalize(json_decode((string) $isletme->santral_ozel_menu, true)); @endphp
                                 <form id="santral_ozel_menu_form" method="POST">
                                    {{csrf_field()}}
                                    <input type="hidden" name="sube" value="{{$isletme->id}}">
                                    <div class="pd-20 card-box mb-30">
                                       <h6>Özel Karşılama Menüsü</h6>
                                       <p>Açık olduğunda işletmenize gelen çağrılar karşılamadan sonra aşağıdaki tuşlu menüyü dinler. Her tuş için yönlendirmeyi seçin: <b>Kuyruk</b> (birden çok dahilinin çaldığı santral kuyruğu) veya <b>Dahili</b> (tek bir personelin dahilisi). Arayan tuşlama yapmazsa çağrı standart akışla operatöre aktarılır; 0 tuşu menüyü tekrarlar.</p>
                                       <div class="custom-control custom-checkbox mb-15">
                                          <input type="checkbox" class="custom-control-input" {{$isletme->santral_ozel_menu_aktif ? 'checked' : ''}} name="santral_ozel_menu_aktif" id="santral_ozel_menu_aktif">
                                          <label class="custom-control-label" for="santral_ozel_menu_aktif">Açık / Kapalı</label>
                                       </div>
                                       <div class="row d-none d-md-flex font-14 weight-500 mb-5">
                                          <div class="col-md-2">Tuş</div>
                                          <div class="col-md-4">İşlem adı (anonsta okunur)</div>
                                          <div class="col-md-2">Yönlendirme</div>
                                          <div class="col-md-3">Kuyruk / Dahili No</div>
                                       </div>
                                       <div id="santral_ozel_menu_satirlar">
                                          @foreach(count($ozelSecenekler) ? $ozelSecenekler : [['tus'=>'1','etiket'=>'','tip'=>'kuyruk','hedef'=>'']] as $secenek)
                                          <div class="row santral-ozel-menu-satir mb-10">
                                             <div class="col-md-2 col-4 mb-5">
                                                <select class="form-control" name="secenekler[{{$loop->index}}][tus]">
                                                   @foreach(\App\Salonlar::SANTRAL_TUS_OKUNUS as $tus => $okunus)
                                                   <option value="{{$tus}}" {{$secenek['tus'] == $tus ? 'selected' : ''}}>{{$tus}}</option>
                                                   @endforeach
                                                </select>
                                             </div>
                                             <div class="col-md-4 col-8 mb-5">
                                                <input type="text" class="form-control" maxlength="60" name="secenekler[{{$loop->index}}][etiket]" value="{{$secenek['etiket']}}" placeholder="Örn: Randevu işlemleri">
                                             </div>
                                             <div class="col-md-2 col-4 mb-5">
                                                <select class="form-control santral-ozel-menu-tip" name="secenekler[{{$loop->index}}][tip]">
                                                   <option value="kuyruk" {{$secenek['tip'] == 'kuyruk' ? 'selected' : ''}}>Kuyruk</option>
                                                   <option value="dahili" {{$secenek['tip'] == 'dahili' ? 'selected' : ''}}>Dahili</option>
                                                </select>
                                             </div>
                                             <div class="col-md-3 col-5 mb-5">
                                                <input type="text" class="form-control santral-ozel-menu-hedef" inputmode="numeric" list="santral_ozel_menu_hedefler_{{$secenek['tip']}}" name="secenekler[{{$loop->index}}][hedef]" value="{{$secenek['hedef']}}" placeholder="{{$secenek['tip'] == 'dahili' ? 'Dahili no, örn: 101' : 'Kuyruk no, örn: 10013'}}">
                                             </div>
                                             <div class="col-md-1 col-3 mb-5">
                                                <button type="button" class="btn btn-outline-danger btn-block santral-ozel-menu-sil" title="Seçeneği sil"><i class="fa fa-trash"></i></button>
                                             </div>
                                          </div>
                                          @endforeach
                                       </div>
                                       @foreach($santral_hedefler as $tip => $hedefler)
                                       <datalist id="santral_ozel_menu_hedefler_{{$tip}}">
                                          @foreach($hedefler as $hedef)
                                          <option value="{{$hedef['no']}}">{{$hedef['ad']}}</option>
                                          @endforeach
                                       </datalist>
                                       @endforeach
                                       <button type="button" class="btn btn-outline-primary btn-sm" id="santral_ozel_menu_ekle"><i class="fa fa-plus"></i> Seçenek Ekle</button>
                                       <div class="mt-15 p-2" style="background:#f7f7f9;border-radius:4px">
                                          <div class="font-14 weight-500">Okunacak anons</div>
                                          <div id="santral_ozel_menu_metin" class="font-14">{{ \App\Salonlar::santralOzelMenuMetni($ozelSecenekler) ?: 'Henüz kayıtlı seçenek yok.' }}</div>
                                       </div>
                                       <button type="submit" class="btn btn-success mt-15">Menüyü Kaydet</button>
                                    </div>
                                 </form>

                              </div>
                              
                               
                           </div>
                        </div>
                     </div>
                  </div>
                  
</div>

 
@endsection