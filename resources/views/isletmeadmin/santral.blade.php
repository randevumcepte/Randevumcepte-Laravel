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
                              <li class="nav-item" style="margin-left: 20px;display: inline-block;">
                                 <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#asistan-gorusmeleri"
                                    role="tab"
                                    aria-selected="false"
                                    id="asistan_gorusmeleri_tab"
                                    >Asistan Görüşmeleri</button
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
                              @if($kullaniciRolu < 5)
                              {{-- AI asistan gorusmeleri: restoran santral-kayitlar tasarimi (sol liste, sag sohbet dokumu + ses) --}}
                              <div class="tab-pane fade" id="asistan-gorusmeleri" role="tabpanel" style="margin-top: 20px;">
                                 <div class="ag-kok">
                                    <div class="ag-stats">
                                       <div class="ag-stat"><div class="n" id="ag_s_top">—</div><div class="l"><span class="dot b-none"></span>Toplam görüşme</div></div>
                                       <div class="ag-stat"><div class="n" id="ag_s_rnd">—</div><div class="l"><span class="dot b-randevu"></span>Randevu talebi</div></div>
                                       <div class="ag-stat"><div class="n" id="ag_s_hat">—</div><div class="l"><span class="dot b-teyit"></span>Hatırlatma araması</div></div>
                                       <div class="ag-stat"><div class="n" id="ag_s_akt">—</div><div class="l"><span class="dot b-aktar"></span>İşletmeye aktarılan</div></div>
                                    </div>
                                    <div class="ag-filtre">
                                       <input type="date" id="ag_baslangic" value="{{date('Y-m-d', strtotime('-6 days'))}}">
                                       <span style="color:#94a3b8">—</span>
                                       <input type="date" id="ag_bitis" value="{{date('Y-m-d')}}">
                                       <select id="ag_tip">
                                          <option value="">Tüm görüşmeler</option>
                                          <option>Gelen arama</option>
                                          <option>Randevu hatırlatma</option>
                                          <option>Ödeme hatırlatma</option>
                                       </select>
                                       <button type="button" id="ag_listele"><i class="fa fa-refresh"></i> Yenile</button>
                                    </div>
                                    <div class="ag-grid">
                                       <div class="ag-panel">
                                          <div class="ag-ara"><input id="ag_q" placeholder="🔎 Müşteri, telefon veya içerik ara…"></div>
                                          <div class="ag-liste" id="ag_liste"><div class="ag-bos">Yükleniyor…</div></div>
                                       </div>
                                       <div class="ag-panel"><div class="ag-detay" id="ag_detay"><div class="ag-bos">Soldan bir görüşme seçin</div></div></div>
                                    </div>
                                 </div>
                              </div>
                              <style>
                                 .ag-kok{--line:#e5e7eb;--sub:#64748b;--p2:#f8fafc;--indigo:#6366f1;--us:linear-gradient(135deg,#6366f1,#8b5cf6)}
                                 .ag-kok *{box-sizing:border-box}
                                 .ag-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px}
                                 @media(max-width:768px){.ag-stats{grid-template-columns:repeat(2,1fr)}}
                                 .ag-stat{background:#fff;border:1px solid var(--line);border-radius:14px;padding:14px 16px}
                                 .ag-stat .n{font-size:24px;font-weight:800;color:#0f172a}
                                 .ag-stat .l{font-size:12px;color:var(--sub);margin-top:2px;display:flex;align-items:center;gap:6px}
                                 .ag-stat .dot{width:8px;height:8px;border-radius:50%;display:inline-block}
                                 .ag-filtre{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:14px}
                                 .ag-filtre input,.ag-filtre select{border:1px solid var(--line);border-radius:10px;padding:8px 10px;font-size:13px;background:#fff}
                                 .ag-filtre button{border:none;background:var(--us);color:#fff;border-radius:10px;padding:9px 16px;font-weight:700;font-size:13px;cursor:pointer}
                                 .ag-grid{display:grid;grid-template-columns:360px minmax(0,1fr);gap:16px}
                                 @media(max-width:900px){.ag-grid{grid-template-columns:minmax(0,1fr)}}
                                 .ag-panel{background:#fff;border:1px solid var(--line);border-radius:16px;overflow:hidden}
                                 .ag-ara{padding:12px;border-bottom:1px solid var(--line)}
                                 .ag-ara input{width:100%;background:var(--p2);border:1px solid var(--line);border-radius:10px;padding:10px 12px;font-size:14px;outline:none}
                                 .ag-liste{max-height:70vh;overflow:auto}
                                 .ag-kayit{padding:12px 14px;border-bottom:1px solid var(--line);cursor:pointer;display:flex;gap:11px;align-items:flex-start;border-left:3px solid transparent;transition:.12s}
                                 .ag-kayit:hover{background:var(--p2)}
                                 .ag-kayit.sec{background:#eef2ff;border-left-color:var(--indigo)}
                                 .ag-av{width:38px;height:38px;border-radius:50%;flex:none;display:flex;align-items:center;justify-content:center;font-size:17px;color:#fff}
                                 .ag-ov{flex:1;min-width:0}
                                 .ag-ad{font-weight:700;font-size:14px;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
                                 .ag-sn{font-size:12px;color:var(--sub);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
                                 .ag-alt{display:flex;align-items:center;gap:6px;margin-top:5px;flex-wrap:wrap}
                                 .ag-rozet{font-size:10.5px;border-radius:20px;padding:2px 9px;font-weight:800;letter-spacing:.02em}
                                 .ag-zaman{font-size:11px;color:#94a3b8;margin-left:auto;white-space:nowrap;padding-left:6px}
                                 .ag-kucuk{font-size:11px;color:#94a3b8}
                                 .c-randevu{background:rgba(59,130,246,.14);color:#2563eb}.b-randevu{background:#3b82f6}
                                 .c-teyit{background:rgba(34,197,94,.14);color:#16a34a}.b-teyit{background:#22c55e}
                                 .c-iptal{background:rgba(239,68,68,.14);color:#dc2626}.b-iptal{background:#ef4444}
                                 .c-aktar{background:rgba(245,158,11,.16);color:#d97706}.b-aktar{background:#f59e0b}
                                 .c-guncelle{background:rgba(99,102,241,.14);color:#4f46e5}.b-guncelle{background:#6366f1}
                                 .c-none{background:rgba(100,116,139,.14);color:#475569}.b-none{background:#64748b}
                                 .ag-detay{padding:18px;max-height:74vh;overflow:auto}
                                 .ag-meta{background:var(--p2);border:1px solid var(--line);border-radius:12px;padding:14px;margin-bottom:14px}
                                 .ag-meta .ln1{display:flex;align-items:center;gap:10px;flex-wrap:wrap;font-weight:800;font-size:16px;color:#0f172a}
                                 .ag-meta .ln2{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:8px;font-size:12px;color:var(--sub)}
                                 .ag-chip{background:#fff;border:1px solid var(--line);border-radius:8px;padding:3px 9px;font-size:12px;color:#0f172a}
                                 .ag-ses{background:#fff;border:1px solid var(--line);border-radius:10px;padding:8px 10px;margin-top:8px}
                                 .ag-ses .et{font-size:12px;color:var(--sub);margin-bottom:5px;font-weight:700}
                                 .ag-ses audio{width:100%;height:34px}
                                 .ag-kopyala{background:var(--us);border:none;color:#fff;border-radius:9px;padding:8px 14px;font-weight:700;cursor:pointer;font-size:12.5px;margin-bottom:14px}
                                 .ag-msg{max-width:84%;padding:10px 14px;border-radius:15px;font-size:14px;line-height:1.5;margin-bottom:10px;white-space:pre-wrap}
                                 .ag-msg .kim{font-size:10.5px;opacity:.75;margin-bottom:3px;font-weight:700;letter-spacing:.02em}
                                 .ag-us{background:var(--us);color:#fff;border-bottom-right-radius:5px;margin-left:auto}
                                 .ag-ai{background:var(--p2);border:1px solid var(--line);border-bottom-left-radius:5px;color:#0f172a}
                                 .ag-bos{color:var(--sub);text-align:center;padding:40px 20px}
                              </style>
                              <script>
                              window.addEventListener('load', function () {
                                 var $ = window.jQuery; if (!$) return;
                                 var sube = '{{$isletme->id}}', yuklendi = false, hepsi = [], dokumMetni = '';
                                 var IKON = {randevu:'📅', teyit:'✅', iptal:'✖️', aktar:'↪️', guncelle:'🔁', none:'📞'};
                                 var TIP_IKON = {'Gelen arama':'📞', 'Randevu hatırlatma':'⏰', 'Ödeme hatırlatma':'💳'};
                                 function esc(s){ return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
                                 function rozet(x){ return '<span class="ag-rozet c-' + x.renk + '">' + (IKON[x.renk] || '•') + ' ' + esc(x.sonuc) + '</span>'; }

                                 function listele(){
                                    $('#ag_liste').html('<div class="ag-bos">Yükleniyor…</div>');
                                    $.ajax({ url: '/isletmeyonetim/santral-asistan-gorusmeler', dataType: 'json', cache: false,
                                       data: { sube: sube, baslangic: $('#ag_baslangic').val(), bitis: $('#ag_bitis').val() },
                                       success: function (r) {
                                          var tip = $('#ag_tip').val();
                                          hepsi = (r.liste || []).filter(function (x) { return !tip || x.tip === tip; });
                                          $('#ag_s_top').text(hepsi.length);
                                          $('#ag_s_rnd').text(hepsi.filter(function (x) { return x.renk === 'randevu'; }).length);
                                          $('#ag_s_hat').text(hepsi.filter(function (x) { return x.tip !== 'Gelen arama'; }).length);
                                          $('#ag_s_akt').text(hepsi.filter(function (x) { return x.renk === 'aktar'; }).length);
                                          filtrele();
                                          if (hepsi[0]) detay(hepsi[0].id); else $('#ag_detay').html('<div class="ag-bos">Bu aralıkta asistan görüşmesi yok.</div>');
                                       },
                                       error: function (q) { $('#ag_liste').html('<div class="ag-bos">' + esc((q.responseJSON && q.responseJSON.hata) || 'Liste alınamadı.') + '</div>'); }
                                    });
                                 }
                                 function ciz(list){
                                    if (!list.length) { $('#ag_liste').html('<div class="ag-bos">Kayıt yok</div>'); return; }
                                    var h = '';
                                    list.forEach(function (x) {
                                       h += '<div class="ag-kayit" id="ag_k' + x.id + '" data-id="' + x.id + '">' +
                                            '<div class="ag-av b-' + x.renk + '">' + (TIP_IKON[x.tip] || '📞') + '</div>' +
                                            '<div class="ag-ov"><div style="display:flex;align-items:center"><span class="ag-ad">' + esc(x.musteri || x.telefon) + '</span><span class="ag-zaman">' + esc(x.tarih) + '</span></div>' +
                                            '<div class="ag-sn">' + esc(x.son_musteri || (x.musteri ? x.telefon : '—')) + '</div>' +
                                            '<div class="ag-alt">' + rozet(x) + '<span class="ag-kucuk">' + esc(x.tip) + (x.sure ? ' · ' + esc(x.sure) : '') + (x.ses ? ' · 🎙️' : '') + '</span></div></div></div>';
                                    });
                                    $('#ag_liste').html(h);
                                 }
                                 function filtrele(){
                                    var q = ($('#ag_q').val() || '').toLocaleLowerCase('tr').trim();
                                    if (!q) { ciz(hepsi); return; }
                                    ciz(hepsi.filter(function (x) { return [x.musteri, x.telefon, x.son_musteri, x.sonuc].join(' ').toLocaleLowerCase('tr').indexOf(q) !== -1; }));
                                 }
                                 function detay(id){
                                    $('.ag-kayit').removeClass('sec'); $('#ag_k' + id).addClass('sec');
                                    $('#ag_detay').find('audio').each(function () { this.pause(); });
                                    $('#ag_detay').html('<div class="ag-bos">Yükleniyor…</div>');
                                    $.ajax({ url: '/isletmeyonetim/santral-asistan-gorusme/' + id, data: { sube: sube }, dataType: 'json', cache: false,
                                       success: function (r) {
                                          var k = r.kayit || {};
                                          var chips = '<span>🕐 ' + esc(k.tarih) + '</span><span class="ag-chip">' + esc(k.tip) + '</span>';
                                          if (k.randevu) chips += '<span class="ag-chip">📅 Randevu ' + esc(k.randevu) + '</span>';
                                          if (k.sure) chips += '<span class="ag-chip">⏱ ' + esc(k.sure) + '</span>';
                                          var m = '<div class="ag-meta"><div class="ln1">' + esc(k.musteri || k.telefon || 'Gizli numara') + ' ' + rozet(k) + '</div>' +
                                                  '<div class="ln2">' + (k.musteri ? '<span>📱 ' + esc(k.telefon) + '</span>' : '') + chips + '</div>';
                                          if (k.sesler && k.sesler.length) {
                                             k.sesler.forEach(function (a) { m += '<div class="ag-ses"><div class="et">' + (a.aktarma ? '↪️ ' : '🤖 ') + esc(a.tur) + '</div><audio controls preload="none" src="' + esc(a.url) + '"></audio></div>'; });
                                          } else m += '<div style="margin-top:10px;font-size:12px;color:#94a3b8">🎙️ Ses kaydı yok</div>';
                                          m += '</div>';
                                          var c = '';
                                          dokumMetni = (k.tarih || '') + ' | ' + (k.musteri || '') + ' ' + (k.telefon || '') + ' | ' + (k.sonuc || '') + '\n';
                                          (k.konusma || []).forEach(function (x) {
                                             var ai = x.rol === 'asistan';
                                             c += '<div class="ag-msg ' + (ai ? 'ag-ai' : 'ag-us') + '"><div class="kim">' + (ai ? '🤖 ASİSTAN' : '🧑 MÜŞTERİ') + '</div>' + esc(x.metin) + '</div>';
                                             dokumMetni += '\n' + (ai ? 'ASİSTAN' : 'MÜŞTERİ') + ': ' + x.metin;
                                          });
                                          $('#ag_detay').html(m + '<button type="button" class="ag-kopyala" id="ag_kopyala">📋 Dökümü kopyala</button>' + (c || '<div class="ag-bos">Konuşma boş (müşteri konuşmadan kapattı)</div>'));
                                       },
                                       error: function (q) { $('#ag_detay').html('<div class="ag-bos">' + esc((q.responseJSON && q.responseJSON.hata) || 'Kayıt alınamadı.') + '</div>'); }
                                    });
                                 }
                                 // Sekme tiklanarak da, sayfa son acik sekmeyi geri yukleyerek de (tiklama olmadan) acilabilir:
                                 // her iki durumda da listeyi bir kez yukle.
                                 function gerekirseYukle() { if (!yuklendi && $('#asistan-gorusmeleri').is(':visible')) { yuklendi = true; listele(); } }
                                 $(document).on('shown.bs.tab click', '[href="#asistan-gorusmeleri"]', function () { setTimeout(gerekirseYukle, 50); });
                                 gerekirseYukle();
                                 setTimeout(gerekirseYukle, 600);
                                 setTimeout(gerekirseYukle, 1500);
                                 $('#ag_listele').on('click', listele);
                                 $('#ag_tip, #ag_baslangic, #ag_bitis').on('change', listele);
                                 $('#ag_q').on('input', filtrele);
                                 $('#ag_liste').on('click', '.ag-kayit', function () { detay($(this).data('id')); });
                                 $('#ag_detay').on('click', '#ag_kopyala', function () {
                                    if (navigator.clipboard) navigator.clipboard.writeText(dokumMetni).then(function () { swal({ type: 'success', title: 'Döküm kopyalandı', showConfirmButton: false, timer: 1500 }); });
                                 });
                              });
                              </script>
                              @endif
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

                                 @php
                                    try { \App\Services\RandevuAsistan::sssTablo(); $sssListe = \DB::table('santral_sss')->where('salon_id', $isletme->id)->orderBy('id')->get(); }
                                    catch (\Throwable $e) { $sssListe = collect(); }
                                 @endphp
                                 <form id="santral_sss_form" method="POST">
                                    {{csrf_field()}}
                                    <input type="hidden" name="sube" value="{{$isletme->id}}">
                                    <div class="pd-20 card-box mb-30">
                                       <h6>Sesli Asistan — Sık Sorulan Sorular</h6>
                                       <p>Telefondaki yapay zeka asistanı, müşteri burada yazdığınız kelimelerden birini söylediğinde cevabınızı <b>aynen</b> okur. Kelimeleri virgülle ayırın (ör. <i>otopark, park yeri, araba</i>). Fiyat soruları bu listede olsa bile işletmeye aktarılır.</p>
                                       <div class="row d-none d-md-flex font-14 weight-500 mb-5">
                                          <div class="col-md-4">Soru kelimeleri (virgülle)</div>
                                          <div class="col-md-7">Asistanın okuyacağı cevap</div>
                                       </div>
                                       <div id="santral_sss_satirlar">
                                          @foreach(count($sssListe) ? $sssListe : [(object)['tetikleyiciler'=>'','cevap'=>'']] as $sss)
                                          <div class="row santral-sss-satir mb-10">
                                             <div class="col-md-4 mb-5"><input type="text" class="form-control" maxlength="500" data-alan="tetikleyiciler" value="{{$sss->tetikleyiciler}}" placeholder="Örn: otopark, park yeri"></div>
                                             <div class="col-md-7 mb-5"><textarea class="form-control" rows="2" maxlength="600" data-alan="cevap" placeholder="Örn: Binamızın önünde ücretsiz otopark bulunmaktadır.">{{$sss->cevap}}</textarea></div>
                                             <div class="col-md-1 mb-5"><button type="button" class="btn btn-outline-danger btn-block santral-sss-sil" title="Sil"><i class="fa fa-trash"></i></button></div>
                                          </div>
                                          @endforeach
                                       </div>
                                       <button type="button" class="btn btn-outline-primary btn-sm" id="santral_sss_ekle"><i class="fa fa-plus"></i> Soru Ekle</button>
                                       <br><button type="submit" class="btn btn-success mt-15">Soruları Kaydet</button>
                                    </div>
                                 </form>
                                 <script>
                                 window.addEventListener('load', function () {
                                    var $ = window.jQuery; if (!$) return;
                                    function adlandir() {
                                       $('#santral_sss_satirlar .santral-sss-satir').each(function (i) {
                                          $(this).find('[data-alan]').each(function () { $(this).attr('name', 'sss[' + i + '][' + $(this).data('alan') + ']'); });
                                       });
                                    }
                                    adlandir();
                                    $('#santral_sss_ekle').on('click', function () {
                                       var $y = $('#santral_sss_satirlar .santral-sss-satir:first').clone();
                                       $y.find('input,textarea').val('');
                                       $('#santral_sss_satirlar').append($y); adlandir();
                                    });
                                    $(document).on('click', '.santral-sss-sil', function () {
                                       var $s = $('#santral_sss_satirlar .santral-sss-satir');
                                       if ($s.length > 1) $(this).closest('.santral-sss-satir').remove(); else $s.find('input,textarea').val('');
                                       adlandir();
                                    });
                                    $('#santral_sss_form').on('submit', function (e) {
                                       e.preventDefault(); adlandir();
                                       $.ajax({ type: 'POST', url: '/isletmeyonetim/santral-sss-kaydet', dataType: 'json', data: $(this).serialize(),
                                          beforeSend: function () { $('#preloader').show(); },
                                          success: function (r) { $('#preloader').hide(); swal({ type: 'success', title: r.mesaj, showConfirmButton: false, timer: 3000 }); },
                                          error: function (q) { $('#preloader').hide(); swal({ type: 'warning', title: (q.responseJSON && q.responseJSON.mesaj) ? q.responseJSON.mesaj : 'Kaydedilemedi.' }); }
                                       });
                                    });
                                 });
                                 </script>

                              </div>
                              
                               
                           </div>
                        </div>
                     </div>
                  </div>
                  
</div>

 
@endsection