@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<style>
/* ====== Musteri Detay — Modern Tema (scoped) ====== */
#mdetay{--m1:#5C008E;--m2:#7B2FB8;--m3:#9D5DC8;--ink:#1f2533;--muted:#7b8294;--line:#eceef4;--soft:#f7f6fb;}
#mdetay .page-header{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0 0 16px!important;}

/* Header kart */
#mdetay .mh-card{display:flex;align-items:center;gap:16px 20px;flex-wrap:wrap;background:#fff;border:1px solid var(--line);border-radius:18px;padding:16px 22px;box-shadow:0 12px 30px rgba(92,0,142,.07);}
#mdetay .mh-left{flex:1 1 180px;min-width:0;}
#mdetay .mh-titlerow{display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
#mdetay .mh-title{margin:0;font-size:26px;font-weight:800;color:var(--ink);letter-spacing:-.3px;overflow-wrap:anywhere;word-break:break-word;}
#mdetay .mh-crumb{margin-top:6px;font-size:13px;color:var(--muted);}
#mdetay .mh-crumb a{color:var(--m2);font-weight:600;text-decoration:none;}
#mdetay .mh-crumb .sep{margin:0 7px;color:#c8c2d6;}
#mdetay .mh-pill{display:inline-flex;align-items:center;gap:7px;font-size:12px;font-weight:700;padding:5px 13px;border-radius:999px;text-transform:uppercase;letter-spacing:.4px;}
#mdetay .mh-pill::before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor;}
#mdetay .mh-pill.is-aktif{background:#e8f7ed;color:#1f9d55;}
#mdetay .mh-pill.is-pasif{background:#fdeaea;color:#d64545;}
#mdetay .mh-pill.is-sadik{background:#fff4e0;color:#d98a00;}
#mdetay .mh-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end;flex:0 0 auto;}
#mdetay .mh-stat{display:flex;flex-direction:column;gap:2px;padding:10px 18px;border-radius:14px;border:0;min-width:130px;color:#fff;}
#mdetay .mh-stat.is-borc{background:linear-gradient(135deg,#f15a5a,#c41f1f);box-shadow:0 8px 20px rgba(196,31,31,.38);}
#mdetay .mh-stat.is-odenen{background:linear-gradient(135deg,#34c759,#1c8a3c);box-shadow:0 8px 20px rgba(28,138,60,.38);}
#mdetay .mh-stat .lbl{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:rgba(255,255,255,.92);text-transform:uppercase;letter-spacing:.4px;}
#mdetay .mh-stat .val,#mdetay .mh-stat .val:focus,#mdetay .mh-stat .val:active,#mdetay .mh-stat .val:hover{font-size:20px;font-weight:800;color:#fff!important;background:none!important;border:0!important;padding:0!important;text-align:left;cursor:default;box-shadow:none!important;outline:none!important;text-shadow:0 1px 2px rgba(0,0,0,.12);}
#mdetay .mh-btn{display:inline-flex;align-items:center;gap:8px;border:0;border-radius:12px;padding:11px 18px;font-size:14px;font-weight:700;color:#fff;cursor:pointer;transition:transform .12s,box-shadow .12s,filter .12s;}
#mdetay .mh-btn:hover{transform:translateY(-1px);filter:brightness(1.04);color:#fff;}
#mdetay .mh-btn.is-wa{background:#25d366;box-shadow:0 8px 18px rgba(37,211,102,.3);}
#mdetay .mh-btn.is-dark{background:linear-gradient(135deg,var(--m1),#3a1857);box-shadow:0 8px 18px rgba(92,0,142,.3);}
#mdetay .mh-btn.is-ok{background:#1f9d55;box-shadow:0 8px 18px rgba(31,157,85,.3);}

/* Ana sekmeler */
#mdetay .elementmusteridetay{border:0;display:flex;flex-wrap:wrap;gap:8px;padding:6px;background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:0 8px 22px rgba(92,0,142,.05);}
#mdetay .elementmusteridetay .nav-item{margin:0!important;}
#mdetay .elementmusteridetay .btn{width:auto!important;border:0!important;border-radius:11px!important;padding:10px 18px!important;font-weight:600;color:var(--muted);background:transparent;transition:all .15s;}
#mdetay .elementmusteridetay .btn:hover{background:var(--soft);color:var(--m2);}
#mdetay .elementmusteridetay .btn.active{background:linear-gradient(135deg,var(--m1),var(--m3));color:#fff!important;box-shadow:0 8px 18px rgba(92,0,142,.28);}
#mdetay .elementmusteridetay .btn-warning,#mdetay .elementmusteridetay .btn-warning.active{background:linear-gradient(135deg,#f7971e,#ffb347)!important;color:#fff!important;box-shadow:0 8px 18px rgba(247,151,30,.28);}
#mdetay .elementmusteridetay .btn.tab-mini{padding:7px 13px!important;font-size:12.5px!important;border-radius:10px!important;}

/* Kartlar */
#mdetay .card-box{border-radius:16px;border:1px solid var(--line);box-shadow:0 8px 22px rgba(31,37,51,.05);}
#mdetay .text-blue,#mdetay h3.text-blue,#mdetay h4.text-blue{color:var(--m1)!important;font-weight:800;}

/* Profil kartı */
#mdetay .prof-card{text-align:center;}
#mdetay .prof-card .profile-photo{position:relative;display:inline-block;margin:0 auto!important;}
#mdetay .prof-card .avatar-photo{border-radius:50%!important;border:4px solid #fff;box-shadow:0 0 0 3px var(--m3),0 12px 26px rgba(92,0,142,.2);}
#mdetay .prof-card .edit-avatar{position:absolute;right:4px;bottom:4px;width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--m1),var(--m3));color:#fff!important;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 14px rgba(92,0,142,.35);z-index:3;border:2px solid #fff;}
#mdetay .prof-name{font-size:21px;font-weight:800;color:var(--ink);margin:14px 0 2px;}
#mdetay .info-list{list-style:none;margin:16px 0 0;padding:0;text-align:left;display:grid;grid-template-columns:1fr 1fr;gap:10px;}
#mdetay .info-list li{display:flex;gap:10px;align-items:center;padding:10px 12px;border:1px solid var(--line);border-radius:12px;background:#fff;min-width:0;}
#mdetay .info-list li.info-full{grid-column:1 / -1;align-items:flex-start;}
#mdetay .info-list .ico{flex:0 0 34px;width:34px;height:34px;border-radius:10px;background:var(--soft);color:var(--m2);display:flex;align-items:center;justify-content:center;font-size:14px;}
#mdetay .info-list li>div{min-width:0;}
#mdetay .info-list .k{font-size:10.5px;color:var(--muted);text-transform:uppercase;letter-spacing:.3px;font-weight:600;line-height:1.2;}
#mdetay .info-list .v{font-size:14px;color:var(--ink);font-weight:600;word-break:break-word;line-height:1.35;margin-top:1px;}
@media (max-width:575px){#mdetay .info-list{grid-template-columns:1fr;}}
#mdetay .notlar-box{max-height:150px;overflow-y:auto;padding-right:8px;white-space:pre-line;}
#mdetay .notlar-box::-webkit-scrollbar{width:6px;}
#mdetay .notlar-box::-webkit-scrollbar-thumb{background:var(--m3);border-radius:3px;}
#mdetay .notlar-box::-webkit-scrollbar-track{background:transparent;}
#mdetay .prof-card .card-footer{border:0!important;background:none!important;padding:18px 0 0!important;}
#mdetay .prof-edit-btn{border:0;border-radius:12px;padding:12px;font-weight:700;color:#fff;background:linear-gradient(135deg,var(--m1),var(--m3));box-shadow:0 8px 18px rgba(92,0,142,.25);}
#mdetay .prof-edit-btn:hover{color:#fff;filter:brightness(1.05);}

/* Sağ taraf sekme + zaman çizelgesi */
#mdetay .element{border:0;display:flex;flex-wrap:wrap;gap:8px;}
#mdetay .element .nav-item{margin:0!important;}
#mdetay .element .btn{border:0;border-radius:11px;padding:9px 18px;font-weight:600;color:var(--muted);background:var(--soft);width:auto!important;}
#mdetay .element .btn:hover{color:var(--m2);}
#mdetay .element .btn.active{background:linear-gradient(135deg,var(--m1),var(--m3));color:#fff;box-shadow:0 8px 18px rgba(92,0,142,.25);}
#mdetay .tl-wrap{overflow-y:auto;max-height:572px;padding-right:6px;}
#mdetay .tl-item{position:relative;background:#fff;border:1px solid var(--line);border-radius:14px;padding:14px 16px;margin-bottom:14px;box-shadow:0 4px 12px rgba(31,37,51,.04);border-left:4px solid var(--m3);}
#mdetay .tl-head{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px;}
#mdetay .tl-date{font-size:13px;font-weight:700;color:var(--m1);background:var(--soft);padding:3px 11px;border-radius:8px;white-space:nowrap;}
#mdetay .tl-serv{font-size:13px;font-weight:600;color:var(--ink);text-align:right;}
#mdetay .tl-body{font-size:13px;color:#555c6b;line-height:1.5;white-space:pre-line;}
#mdetay .tl-body:empty::after{content:"—";color:#c5c9d3;}
#mdetay .mdetay-empty{background:var(--soft);border:1px dashed var(--line);color:var(--muted);border-radius:12px;padding:22px;text-align:center;font-size:14px;font-weight:600;}
#mdetay .mdetay-empty i{color:var(--m3);margin-right:6px;}
#mdetay .notes-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:14px;}
#mdetay .notes-count{font-size:12px;font-weight:700;color:var(--m2);background:var(--soft);padding:5px 12px;border-radius:999px;white-space:nowrap;}
#mdetay .tl-head{align-items:flex-start;}
#mdetay .tl-tags{display:flex;flex-wrap:wrap;gap:5px;justify-content:flex-end;}
#mdetay .tl-tag{font-size:11px;font-weight:600;color:var(--m1);background:rgba(157,93,200,.13);padding:3px 9px;border-radius:8px;line-height:1.5;}
#mdetay .tl-notes{display:flex;flex-direction:column;gap:8px;margin-top:10px;}
#mdetay .tl-note{font-size:13px;color:#3a4252;line-height:1.5;background:var(--soft);border-radius:10px;padding:9px 12px;border-left:3px solid var(--m3);white-space:pre-line;word-break:break-word;}
#mdetay .tl-note-lbl{display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:var(--m2);margin-bottom:3px;}
#mdetay .tl-empty-note{font-size:12.5px;color:#aeb4c2;font-style:italic;margin-top:8px;}
#mdetay .tl-empty-note i{margin-right:5px;}

/* Sağlık bilgileri formu */
#mdetay #musteri_saglik_bilgileri .col-md-6,#mdetay #musteri_saglik_bilgileri .col-md-12{margin-bottom:16px;}
#mdetay #musteri_saglik_bilgileri label{font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.3px;margin-bottom:7px;display:block;}
#mdetay #musteri_saglik_bilgileri .form-control{border:1px solid var(--line);border-radius:10px;padding:10px 12px;height:auto;background:#fff;box-shadow:none;transition:border-color .15s,box-shadow .15s;}
#mdetay #musteri_saglik_bilgileri .form-control:focus{border-color:var(--m3);box-shadow:0 0 0 3px rgba(157,93,200,.15);}
#mdetay #musteri_saglik_bilgileri textarea.form-control{min-height:90px;}
#mdetay #musteri_saglik_bilgileri .btn-success{background:linear-gradient(135deg,var(--m1),var(--m3))!important;border:0!important;border-radius:12px!important;padding:13px!important;font-weight:700;box-shadow:0 8px 18px rgba(92,0,142,.25);}

/* Müşteri resimleri galeri */
#mdetay #musteri_resimleri .mr-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
#mdetay #musteri_resimleri .mr-add{display:inline-flex;align-items:center;gap:8px;border:0;border-radius:12px;padding:11px 18px;font-weight:700;color:#fff;background:linear-gradient(135deg,var(--m1),var(--m3));box-shadow:0 8px 18px rgba(92,0,142,.25);}
#mdetay #musteri_resimleri .mr-add:hover{color:#fff;filter:brightness(1.05);}
#mdetay #buttonContainer{display:flex;flex-wrap:wrap;gap:14px;}
#mdetay #buttonContainer .btn{border:1px solid var(--line)!important;border-radius:14px!important;padding:10px!important;margin-top:0!important;background:#fff!important;color:var(--ink)!important;font-size:12px;font-weight:600;box-shadow:0 6px 16px rgba(31,37,51,.06);transition:transform .12s,box-shadow .12s;}
#mdetay #buttonContainer .btn:hover{transform:translateY(-2px);box-shadow:0 12px 24px rgba(92,0,142,.14);}
#mdetay #buttonContainer .btn img{border-radius:10px;object-fit:cover;}
#mdetay .mr-empty{flex:1;}

@media (max-width:991px){#mdetay .mh-right{width:100%;justify-content:flex-start;}}
</style>
<div id="mdetay">
<div class="page-header">
   <input type="hidden" id="musteriKarti" value="{{$musteri_bilgi->id}}">
   @php
      if($tahsilatlar_count > 3 && $son_tahsilat_tarihi && strtotime('+90 days', strtotime($son_tahsilat_tarihi)) < strtotime('+90 days')){ $_durumKod='sadik'; $_durumYazi='Sadık Müşteri'; }
      elseif($tahsilatlar_count == 0){ $_durumKod='pasif'; $_durumYazi='Pasif'; }
      else { $_durumKod='aktif'; $_durumYazi='Aktif'; }
      $_waSaglayici = $isletme->whatsapp_saglayici ?? 'baileys';
      $_waBagli = $_waSaglayici === 'cloud_api'
         ? (!empty($isletme->cloud_api_token) && !empty($isletme->cloud_api_phone_number_id))
         : (($isletme->whatsapp_aktif ?? 0) && ($isletme->whatsapp_durum ?? '') === 'connected');
   @endphp
   <div class="mh-card">
      <div class="mh-left">
         <div class="mh-titlerow">
            <h1 class="mh-title">{{$sayfa_baslik}}</h1>
            <span class="mh-pill is-{{$_durumKod}}">{{$_durumYazi}}</span>
         </div>
         <nav class="mh-crumb" aria-label="breadcrumb" role="navigation">
            <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
            <span class="sep">/</span>{{$sayfa_baslik}}
         </nav>
      </div>
      <div class="mh-right">
         @if($_SERVER['HTTP_HOST'] != 'randevu.randevumcepte.com.tr')
         <div class="mh-stat is-borc">
            <span class="lbl"><i class="fa fa-arrow-down"></i> Toplam Borç</span>
            <button type="button" class="val" id="toplamBorc">0,00 ₺</button>
         </div>
         <div class="mh-stat is-odenen">
            <span class="lbl"><i class="fa fa-check-circle"></i> Toplam Ödenen</span>
            <button type="button" class="val" id="toplamOdenen">0,00 ₺</button>
         </div>
         @endif
         <button class="mh-btn is-wa whatsapp-mesaj-ac"
            data-userid="{{$musteri_bilgi->id}}"
            data-telefon="{{$musteri_bilgi->cep_telefon}}"
            data-ad="{{$musteri_bilgi->name}}"
            data-onay="{{ (int)($musteri_bilgi->whatsapp_onay ?? 0) }}"
            data-bagli="{{ $_waBagli ? 1 : 0 }}">
            <i class="fa fa-whatsapp"></i> WhatsApp Mesaj
         </button>
         @if(!$is_personel_rolu)
         <button style='display:{{($kara_liste != 1) ? "inline-flex": "none"}}' class="mh-btn is-dark" id='musteri_sms_kara_listeye_ekle' data-value='{{$musteri_bilgi->id}}'>
            <i class="fa fa-times"></i> Kara Listeye Ekle
         </button>
         <button style='display:{{($kara_liste == 1) ? "inline-flex": "none"}}' class="mh-btn is-ok" id='musteri_sms_kara_listeden_cikar' data-value='{{$musteri_bilgi->id}}'>
            <i class="fa fa-check"></i> Kara Listeden Çıkar
         </button>
         @endif
      </div>
   </div>
</div>
<div class="row clearfix">
   <div class="col-lg-12 col-md-12 col-sm-12 mb-30">
      <div class="tab">
         <ul
            class="nav nav-tabs elementmusteridetay"
            role="tablist" 
            >
            <li class="nav-item" style="margin:5px; ">
               <a
                  class="btn btn-outline-primary active"
                  data-toggle="tab"
                  href="#musteri-bilgileri"
                  role="tab"
                  aria-selected="true"
                  style="width: 130px;"
                  >Genel Bilgiler</a>
            </li>
            <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-outline-primary"
                  data-toggle="tab"
                  href="#randevular"
                  role="tab"
                  style="width: 130px;"
                  aria-selected="false"
                  >Randevular</a>
            </li>
            @yetki('paket.seans_takip')
            <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-outline-primary "
                  data-toggle="tab"
                  href="#formlar"
                  style="width: 150px;"
                  role="tab"
                  aria-selected="false"
                  >Seanslar</a>
            </li>
            @endyetki
            @if($_SERVER['HTTP_HOST']!='randevu.randevumcepte.com.tr')
            @yetki('musteri.gecmis_satis_gor')
            <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-outline-primary "
                  data-toggle="tab"
                  href="#tum_adisyonlar"
                  style="width: 130px;"
                  role="tab"
                  aria-selected="false"
                  >Satışlar</a>
            </li>
            @endyetki
            @endif
            @if(!$is_personel_rolu)
            <li class="nav-item" style="margin:5px;display: none;">
               <a
                  class="btn btn-outline-primary "
                  data-toggle="tab"
                  href="#borclar"
                  style="width: 130px;"
                  role="tab" 
                  aria-selected="false"
                  >Alacaklar</a>
            </li>
            <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-outline-primary "
                  data-toggle="tab"
                  href="#saglik-bilgileri"
                  role="tab"
                  style="width: 130px;"
                  aria-selected="false"
                  >Sağlık Bilgileri</a>
            </li>
            <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-outline-primary "
                  data-toggle="tab"
                  href="#belgeler"
                  style="width: 160px;"
                  role="tab"
                  aria-selected="false"
                  >Sözleşmeler/Belgeler</a>
            </li>
            <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-outline-primary "
                  data-toggle="tab"
                  href="#musteri_resimleri"
                  style="width: 160px;"
                  role="tab"
                  aria-selected="false"
                  >Müşteri Resimleri</a>
            </li>
            @endif
            @yetki('satis.adisyon_olustur')
            <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-warning tab-mini"
                  data-toggle="tab"
                  href="#tahsilatEkrani"
                  role="tab"
                  aria-selected="false"
                  ><i class="fa fa-shopping-cart"></i> Randevusuz Satış</a>
            </li>
            @endyetki
            @yetki('satis.tahsilat_al')
            <li class="nav-item" style="margin:5px">
               <button
                  type="button"
                  class="btn tab-mini"
                  style="background:#4338ca;color:#fff"
                  data-toggle="modal"
                  data-target="#harici_tahsilat_modal"
                  ><i class="fa fa-cloud-download"></i> Harici Tahsilat</button>
            </li>
            @endyetki
            @yetki('pazarlama.anket_yonet')
            <li class="nav-item" style="margin:5px">
               <button
                  type="button"
                  class="btn tab-mini"
                  style="background:#25D366;color:#fff"
                  onclick="anketHizliGonder({{ $musteri_bilgi->id }}, this)"
                  ><i class="fa fa-comments"></i> Anket Gönder</button>
            </li>
            @endyetki
         </ul>
         <div class="tab-content">
            <div
               class="tab-pane fade"
               id="formlar"
               role="tabpanel"
               >
               <div class="card-box  pd-10">
                  <h4 style="float:left;"><b>{{$musteri_bilgi->name}} in Tüm Seansları </b></h4>
                  <table class="data-table table stripe hover nowrap" id="seans_takip_liste">
                     <thead>
                        <tr>
                           <th scope="col">ID</th>
                         <th scope="col">Müşteri</th>
                     <th scope="col">Seans Başlangıcı</th>
                     <th scope="col">Paket Adı</th>
                     <th scope="col">Seans Detayı</th>
                           
                     <th class="datatable-nosort"></th>
                        </tr>
                     </thead>
                     <tbody>
                     </tbody>
                  </table>
               </div>
            </div>
            <div
               class="tab-pane fade"
               id="hizmetler"
               role="tabpanel"
               >
               <div class="pd-20">
                  Hizmetler
               </div>
            </div>
            {{-- Dakika Paketleri: solaryum, masaj gibi sure satilan hizmetler --}}
            <div class="tab-pane fade" id="dakika_paketleri" role="tabpanel">
               <div class="card-box pd-20">
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                     <h4 style="margin:0"><b>{{$musteri_bilgi->name}} - Dakika Paketleri</b></h4>
                     <button type="button" class="btn btn-primary" onclick="dakikaPaketSatModal()">+ Yeni Paket Sat</button>
                  </div>
                  <div id="dakika_paketleri_liste" style="display:flex;flex-direction:column;gap:12px;">
                     <div style="color:#888;text-align:center;padding:30px;">Yukleniyor...</div>
                  </div>
               </div>
            </div>
            @if($_SERVER['HTTP_HOST']!='randevu.randevumcepte.com.tr')
            <div
               class="tab-pane fade show"
               id="tum_adisyonlar"
               role="tabpanel"
               >
               <input id="adisyon_musteriye_gore_filtrele" value="{{$musteri_bilgi->id}}" type="hidden">
               <div class="card-box pd-10">
                  <table class="data-table table stripe hover nowrap" id="adisyon_liste_musteri">
                     <thead>
                        <th>Satış Tarihi</th>
                        <th>Planlanan Alacak Tarihi</th>
                       
                        <th>Satış İçeriği </th>
                        <th>Toplam ₺</th>
                        <th>Ödenen ₺</th>
                        <th>Kalan ₺</th>
                        <th>İşlemler</th>
                     </thead>
                     <tbody>
                     </tbody>
                  </table>
               </div>
            </div>
            @endif
            <div
               class="tab-pane fade show"
               id="belgeler"
               role="tabpanel"
               >
               <div class="card-box mb-30">
                  <div style="padding: 20px">
                     <ul class="nav nav-tabs element" role="tablist">
                        <li class="nav-item" style="margin-left: 20px;">
                           <button
                              class="btn btn-outline-primary active"
                              data-toggle="tab"
                              href="#tum_arsiv"
                              role="tab"
                              aria-selected="false"
                              >Tümü</button
                              >
                        </li>
                        <li class="nav-item" style="margin-left: 20px;">
                           <button
                              class="btn btn-outline-primary "
                              data-toggle="tab"
                              href="#onayli_arsiv"
                              role="tab"
                              aria-selected="false"
                              >Onaylananlar</button
                              >
                        </li>
                        <li class="nav-item" style="margin-left: 20px;">
                           <button
                              class="btn btn-outline-primary "
                              data-toggle="tab"
                              href="#beklenen_arsiv"
                              role="tab"
                              aria-selected="false"
                              >Beklenenler</button
                              >
                        </li>
                        <li class="nav-item" style="margin-left: 20px;">
                           <button
                              class="btn btn-outline-primary "
                              data-toggle="tab"
                              href="#iptal_arsiv"
                              role="tab"
                              aria-selected="false"
                              >İptal Edilenler</button
                              >
                        </li>
                        <li class="nav-item" style="margin-left: 20px;">
                           <button
                              class="btn btn-outline-primary "
                              data-toggle="tab"
                              href="#harici_arsiv"
                              role="tab"
                              aria-selected="false"
                              >Harici Belgeler</button
                              >
                        </li>
                     </ul>
                     <div class="tab-content">
                        <div class="tab-pane fade show active" id="tum_arsiv" role="tab-panel" style="margin-top: 20px;">
                           <table class="data-table table stripe hover nowrap" id="arsiv_liste">
                              <thead>
                                 <th>Müşteri</th>
                                 <th>Başlık</th>
                                 <th>Oluşturulma Tarihi</th>
                                 <th>Belge Durumu</th>
                                 <th>Durum</th>
                                 <th>İşlemler</th>
                              </thead>
                              <tbody>
                              </tbody>
                                    
                           </table>
                        </div>
                        <div class="tab-pane fade show " id="onayli_arsiv" role="tab-panel" style="margin-top: 20px;">
                           <table class="data-table table stripe hover nowrap" id="arsiv_liste_onayli">
                              <thead>
                                 <th>Müşteri</th>
                                 <th>Başlık</th>
                                 <th>Oluşturulma Tarihi</th>
                                 <th>Belge Durumu</th>
                                 <th>Durum</th>
                                 <th>İşlemler</th>
                              </thead>
                              <tbody>
                              </tbody>
                                    
                           </table>
                        </div>
                        <div class="tab-pane fade show " id="beklenen_arsiv" role="tab-panel" style="margin-top: 20px;">
                           <table class="data-table table stripe hover nowrap" id="arsiv_liste_beklenen">
                              <thead>
                                 <th>Müşteri</th>
                                 <th>Başlık</th>
                                 <th>Oluşturulma Tarihi</th>
                                 <th>Belge Durumu</th>
                                 <th>Durum</th>
                                 <th>İşlemler</th>
                              </thead>
                              <tbody>
                              </tbody>
                                    
                           </table>
                        </div>
                        <div class="tab-pane fade show " id="iptal_arsiv" role="tab-panel" style="margin-top: 20px;">
                           <table class="data-table table stripe hover nowrap" id="arsiv_liste_iptal">
                              <thead>
                                 <th>Müşteri</th>
                                 <th>Başlık</th>
                                 <th>Oluşturulma Tarihi</th>
                                 <th>Belge Durumu</th>
                                 <th>Durum</th>
                                 <th>İşlemler</th>
                              </thead>
                              <tbody>
                              </tbody>
                                    
                           </table>
                        </div>
                        <div class="tab-pane fade show " id="harici_arsiv" role="tab-panel" style="margin-top: 20px;">
                           <table class="data-table table stripe hover nowrap" id="arsiv_liste_harici">
                              <thead>
                                 <th>Müşteri</th>
                                 <th>Başlık</th>
                                 <th>Oluşturulma Tarihi</th>
                                 <th>Belge Durumu</th>
                                 <th>Durum</th>
                                 <th>İşlemler</th>
                              </thead>
                              <tbody>
                              </tbody>
                                    
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div
               class="tab-pane fade show"
               id="randevular"
               role="tabpanel"
               >
               <div class="card-box pd-10">
                  <table class="data-table table stripe hover nowrap" id="randevu_liste">
                     <thead>
                        <tr>
                           <th>Tarih </th>
                           <th>Saat</th>
                           <th>Durum</th>
                           <th>Hizmetler</th>
                           <th>Personel/Cihaz/Oda</th>
                           
                           <th>Oluşturan</th>
                           <th>Oluşturulma</th>
                           <th class="datatable-nosort"></th>
                        </tr>
                     </thead>
                     <tbody>
                     </tbody>
                  </table>
               </div>
               <div class="pd-20">
               </div>
            </div>
            <div
               class="tab-pane fade"
               id="takvim-ayarlari"
               role="tabpanel"
               >
               <div class="pd-20">
                  Takvim Ayarları
               </div>
            </div>
            <div
               class="tab-pane fade show active"
               id="musteri-bilgileri"
               role="tabpanel"
               >
               <div class="row mdetay-info-row" style="padding:4px 0;">
                  <div class="col-md-5">
                     <div class="card-box prof-card" style="padding:24px;">
                              <div class="profile-photo" style="margin-top:6px;">
                                 <a href="#" class="edit-avatar" onclick="thisFileUpload();"><i class="fa fa-pencil"></i></a>
                                 <img id="mevcut_musteri_profil_resmi"
                                    src="{{($musteri_bilgi->profil_resim !== null ? $musteri_bilgi->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}" alt="" class="avatar-photo" style="object-fit: cover; width: 160px; height: 160px;">
                                 <div
                                    class="modal fade"
                                    id="modal"
                                    tabindex="-1"
                                    role="dialog"
                                    aria-labelledby="modalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document"></div>
                                    <div class="modal-content">
                                       <div class="modal-body pd-5">
                                          <div class="img-container">
                                             <img                            
                                                src="{{($musteri_bilgi->profil_resim !== null ? $musteri_bilgi->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}"
                                                alt="Avatar"
                                                />
                                             <input type="file" id="musteri_profil_resmi" style="display:none;" />
                                          </div>
                                       </div>
                                       <div class="modal-footer" style="display: block;">
                                          <div class="row">
                                             <div class="col-6 col-xs-6 col-sm-6">
                                                <button id="button" name="button" value="Upload" class="btn btn-primary btn-lg btn-block" onclick="thisFileUpload();"><i class="fa fa-upload"></i> Fotoğraf Yükle</button>
                                             </div>
                                             <div class="col-6 col-xs-6 col-sm-6">
                                                <button type="button" class="btn btn-danger btn-lg btn-block" data-dismiss="modal"><i class="fa fa-times"></i>
                                                Kapat
                                                </button>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <button id="crop_modal_ac_musteri"  data-toggle="modal" data-target="#crop_modal_musteri" style="display:none"> modal aç</button>             
                                 <div class="modal fade"
                                    id="crop_modal_musteri"
                                    tabindex="-1"
                                    role="dialog"
                                    aria-labelledby="modalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog"
                                       role="document">
                                       <div class="modal-content">
                                          <div class="modal-body pd-5">
                                             <div class="img-container">
                                                <div class="row">
                                                   <div class="col-md-12">
                                                      <!--  default image where we will set the src via jquery-->
                                                      <img id="croppedimg" src="{{(Auth::guard('isletmeyonetim')->user()->profil_resim !== null ? Auth::guard('isletmeyonetim')->user()->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}" style="display:block;max-width: 100%;position: relative;height: auto;">
                                                   </div>
                                                </div>
                                             </div>
                                          </div>
                                          <div class="modal-footer" style="display: block;">
                                             <div class="row">
                                                <div class="col-6 col-xs-6 col-sm-6">
                                                   <button id="crop" class="btn btn-primary btn-lg btn-block">Kırp</button>
                                                </div>
                                                <div class="col-6 col-xs-6 col-sm-6">
                                                   <button type="button" id="crop_modal_kapat" class="btn btn-danger btn-lg btn-block" data-dismiss="modal"><i class="fa fa-times"></i>
                                                   Kapat
                                                   </button>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                        <div class="prof-name">{{$musteri_bilgi->name}}</div>
                        <ul class="info-list musteri_genel_bilgi_kart">
                           <li><span class="ico"><i class="fa fa-hashtag"></i></span><div><div class="k">ID</div><div class="v">{{$musteri_bilgi->id}}</div></div></li>
                           <li><span class="ico"><i class="fa fa-phone"></i></span><div><div class="k">Telefon</div><div class="v">{{ \App\PersonelYetkiSabitleri::telefonGoster($musteri_bilgi->cep_telefon) }}</div></div></li>
                           <li><span class="ico"><i class="fa fa-envelope-o"></i></span><div><div class="k">E-posta</div><div class="v">{{$musteri_bilgi->email ?: '—'}}</div></div></li>
                           <li><span class="ico"><i class="fa fa-bullhorn"></i></span><div><div class="k">Referans</div><div class="v">@if($portfoy->musteri_tipi == 1)İnternet @elseif($portfoy->musteri_tipi == 2)Reklam @elseif($portfoy->musteri_tipi == 3)Instagram @elseif($portfoy->musteri_tipi == 4)Facebook @elseif($portfoy->musteri_tipi == 5)Tanıdık @else Yok @endif</div></div></li>
                           <li><span class="ico"><i class="fa fa-birthday-cake"></i></span><div><div class="k">Doğum Tarihi</div><div class="v">{{date('d.m.Y', strtotime($musteri_bilgi->dogum_tarihi))}}</div></div></li>
                           <li><span class="ico"><i class="fa fa-id-card-o"></i></span><div><div class="k">TC Kimlik No</div><div class="v">{{$musteri_bilgi->tc_kimlik_no ?: '—'}}</div></div></li>
                           <li><span class="ico"><i class="fa fa-venus-mars"></i></span><div><div class="k">Cinsiyet</div><div class="v">@if($musteri_bilgi->cinsiyet === 0)Kadın @elseif($musteri_bilgi->cinsiyet===1)Erkek @else Belirtilmemiş @endif</div></div></li>
                           <li class="info-full"><span class="ico"><i class="fa fa-sticky-note-o"></i></span><div><div class="k">Notlar</div><div class="v notlar-box" id="musteriNotlarKutu">{{$portfoy->ozel_notlar ?: '—'}}</div></div></li>
                        </ul>
                        <div class="card-footer">
                           @yetki('musteri.ekle_duzenle')
                           <button onclick='modalbaslikata("<?php echo $musteri_bilgi->name;?> Bilgilerini Düzenle","");' class="btn prof-edit-btn btn-block" data-toggle="modal" data-target="#musteri-bilgi-duzenle-modal">
                           <i class="fa fa-edit"></i> Bilgileri Düzenle
                           </button>
                           @endyetki
                        </div>
                     </div>
                  </div>
                  <div class="col-md-7">
                     <div class="notes-head">
                        <h4 class="text-blue" style="margin:0;">Randevu Notları</h4>
                        <span class="notes-count">{{$randevular->count()}} randevu</span>
                     </div>
                     <div class="tl-wrap">
                        @forelse($randevular as $randevu)
                        @php $_pn = trim($randevu->personel_notu ?? ''); $_rn = trim($randevu->randevu_sonrasi_not ?? ''); @endphp
                        <div class="tl-item">
                           <div class="tl-head">
                              <span class="tl-date">{{date('d.m.Y',strtotime($randevu->tarih))}}</span>
                              <div class="tl-tags">
                                 @foreach($randevu->hizmetler as $hizmet)@if($hizmet->hizmet_id && $hizmet->hizmetler)<span class="tl-tag">{{$hizmet->hizmetler->hizmet_adi}}</span>@endif @endforeach
                              </div>
                           </div>
                           @if($_pn || $_rn)
                           <div class="tl-notes">
                              @if($_pn)<div class="tl-note"><span class="tl-note-lbl">Personel Notu</span>{{$_pn}}</div>@endif
                              @if($_rn)<div class="tl-note"><span class="tl-note-lbl">Randevu Sonrası Not</span>{{$_rn}}</div>@endif
                           </div>
                           @else
                           <div class="tl-empty-note"><i class="fa fa-pencil"></i> Bu randevuya not girilmemiş</div>
                           @endif
                        </div>
                        @empty
                        <div class="mdetay-empty"><i class="fa fa-calendar-o"></i> Müşteriye ait randevu veya işlem bulunamadı!</div>
                        @endforelse
                     </div>
                  </div>
               </div>
            </div>
            <div
               class="tab-pane fade"
               id="saglik-bilgileri"
               role="tabpanel"
               >
               <div class="card-box pd-20">
                  <h4 class="text-blue" style="margin:0 0 18px;">Sağlık Bilgileri</h4>
                  <form id="musteri_saglik_bilgileri" method="GET">
                     <input name="musteri_id" type="hidden" value="{{$musteri_bilgi->id}}">
                     <div class="row">
                        <div class="col-md-6">
                           <label>Hemofili Hastalığı Var mı?</label>
                           <select name="hemofili_hastaligi_var" class="form-control">
                              @if($musteri_bilgi->hemofili_hastaligi_var)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Şeker Hastalığı Var mı?</label>
                           <select name="seker_hastaligi_var" class="form-control">
                              @if($musteri_bilgi->seker_hastaligi_var)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Hamile mi?</label>
                           <select name="seker_hastaligi_var" class="form-control">
                              @if($musteri_bilgi->hamile)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Yakın bir zamanda ameliyat geçirdi mi?</label>
                           <select name="yakin_zamanda_ameliyat_gecirildi" class="form-control">
                              @if($musteri_bilgi->yakin_zamanda_ameliyat_gecirildi)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Herhangi bir alerjisi var?</label>
                           <select name="alerji_var" class="form-control">
                              @if($musteri_bilgi->alerji_var)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>48 saat içinde alkol alımı var mı?</label>
                           <select name="alkol_alimi_yapildi" class="form-control">
                              @if($musteri_bilgi->alkol_alimi_yapildi)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Regl döneminde mi?</label>
                           <select name="regl_doneminde" class="form-control">
                              @if($musteri_bilgi->regl_doneminde)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Deri veya yumuşak doku hastalığı var mı?</label>
                           <select name="deri_yumusak_doku_hastaligi_var" class="form-control">
                              @if($musteri_bilgi->deri_yumusak_doku_hastaligi_var)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Sürekli kullanıdığı ilaç var mı?</label>
                           <select name="surekli_kullanilan_ilac_Var" class="form-control">
                              @if($musteri_bilgi->surekli_kullanilan_ilac_Var)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Kemoterapi görüyor mu?</label>
                           <select name="kemoterapi_goruyor" class="form-control">
                              @if($musteri_bilgi->kemoterapi_goruyor)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Daha önce uygulama yaptırdı mı?</label>
                           <select name="daha_once_uygulama_yaptirildi" class="form-control">
                              @if($musteri_bilgi->daha_once_uygulama_yaptirildi)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Cilt Tipi</label>
                           <select name="cilt_tipi" class="form-control">
                              @if($musteri_bilgi->cilt_tipi == 0)
                              <option value="0" selected >Karma</option>
                              <option value="1">Yağlı</option>
                              <option value="2">Hassas</option>
                              <option value="3">Kuru</option>
                              <option value="4">Nemli</option>
                              <option value="5">Normal</option>
                              @elseif($musteri_bilgi->cilt_tipi == 1)
                              <option value="0"  >Karma</option>
                              <option value="1" selected>Yağlı</option>
                              <option value="2">Hassas</option>
                              <option value="3">Kuru</option>
                              <option value="4">Nemli</option>
                              <option value="5">Normal</option>
                              @elseif($musteri_bilgi->cilt_tipi == 2)
                              <option value="0"  >Karma</option>
                              <option value="1" >Yağlı</option>
                              <option value="2" selected>Hassas</option>
                              <option value="3">Kuru</option>
                              <option value="4">Nemli</option>
                              <option value="5">Normal</option>
                              @elseif($musteri_bilgi->cilt_tipi == 3)
                              <option value="0"  >Karma</option>
                              <option value="1" >Yağlı</option>
                              <option value="2" >Hassas</option>
                              <option value="3" selected>Kuru</option>
                              <option value="4">Nemli</option>
                              <option value="5">Normal</option>
                              @elseif($musteri_bilgi->cilt_tipi == 4)
                              <option value="0"  >Karma</option>
                              <option value="1" >Yağlı</option>
                              <option value="2" >Hassas</option>
                              <option value="3">Kuru</option>
                              <option value="4" selected>Nemli</option>
                              <option value="5">Normal</option>
                              @elseif($musteri_bilgi->cilt_tipi == 5)
                              <option value="0"  >Karma</option>
                              <option value="1" >Yağlı</option>
                              <option value="2" >Hassas</option>
                              <option value="3">Kuru</option>
                              <option value="4" >Nemli</option>
                              <option value="5" selected>Normal</option>
                              @else
                              <option value="0"  >Karma</option>
                              <option value="1" >Yağlı</option>
                              <option value="2" >Hassas</option>
                              <option value="3">Kuru</option>
                              <option value="4" >Nemli</option>
                              <option value="5">Normal</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-12">
                           <label>Ek sağlık sorunları var mı?</label>
                           <textarea class="form-control" name="ek_saglik_sorunu">@if(!empty($musteri_bilgi->ek_saglik_sorunu)) {{$musteri_bilgi->ek_saglik_sorunu}} @else Yok @endif</textarea>
                        </div>
                        <div class="col-md-12" style="margin-top:20px">
                           <button type="submit" class="btn btn-success" style="width:100%;text-align: center;">Formu Kaydet</button>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
            <div
               class="tab-pane fade"
               id="musteri_resimleri"
               role="tabpanel"
               >
               <div class="card-box pd-20">
                  <div class="mr-head">
                     <h4 class="text-blue" style="margin:0;">Müşteri Resimleri</h4>
                     <button class="mr-add" data-target="#musterifotoekle" data-toggle="modal" type="button"><i class="fa fa-plus"></i> Yeni Resim Ekle</button>
                  </div>
               </div>
               <div class="card-box pd-20" style="margin-top: 20px">
                  <div id="buttonContainer">
                     @foreach($islemler as $islem)
                     <button class="btn btn-outline-primary" name="islemdetaygetir" style="margin-top: 10px" data-toggle="modal" data-target="#islemdetayigetirmodal" type="button" name="islemdetaygetir" data-value="{{$islem->id}}"> @php
                     $images = json_decode($islem->islem_fotolari, true);
                     @endphp
                     <img src="/{{ $images[0] }}" alt="İşlem Fotoğrafı" style="width:100px; height:100px;">
                     <br><br>{{date('d.m.Y',strtotime($islem->tarih))}}</button>
                     @endforeach
                     @if(count($islemler)==0)
                     <div class="mdetay-empty mr-empty"><i class="fa fa-image"></i> Bu müşteriye ait kayıtlı resim bulunmuyor.</div>
                     @endif
                  </div>
               </div>
            </div>
            @yetki('satis.adisyon_olustur')
            <div
               class="tab-pane fade"
               id="tahsilatEkrani"
               role="tabpanel"
               >
               
               <div  style="margin-top: 20px;padding: 20px;">
                   <form id="adisyon_tahsilat"  method="POST">
<div class="row">
   <div class="col-md-9">
      <div class="card-box pd-5"  style="margin-bottom:20px">
        
            <input type="hidden" name='sube' value="{{$isletme->id}}">
            <input type="hidden" name="tahsilat_ekrani" id='tahsilat_ekrani' value="1">
            <input type="hidden" name="tahsilat_tutari" id='toplam_tahsilat_tutari' >
            <input type="hidden" name="adisyon_id" id="session_adisyon_id" value="">
            <div class="modal-header">
               <div class="col-6 col-xs-6 col-sm-6">
                  <h2>Tahsilat</h2>
               </div>
               
                     
               <div class="col-md-6 col-6 col-xs-6 col-sm-6" style="display:none">
                        <div class="from-group"  >
                           <select name='tahsilat_musteri_id' style="width:100%"  class="form-control"  id='tahsilat_musteri_id' >
                              <option value="{{$musteri_bilgi->id}}">{{$musteri_bilgi->name}}</option>
                           </select>
                        </div>
               </div>
                     
                  
                
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <div class="row">
                  <div class="col-md-12">
                     <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-2 col-sm-4 col-4" style="margin-bottom: 20px;">
                           <button disabled type="button" data-toggle="modal" data-target="#adisyon_yeni_hizmet_modal" id="adisyon_hizmet_ekle_button" class="btn btn-info btn-block adisyon_ekle_buttonlar"  style="font-size:12px">Hizmet Ekle</button>
                        </div>
                        <div class="col-md-2 col-sm-4 col-4" style="padding-left: 0;margin-bottom: 20px;">
                           <button disabled type="button" data-toggle="modal" id="adisyon_urun_ekle_button" data-target="#urun_satisi_modal" data-value=''onclick="modalbaslikata('Yeni Ürün Satışı Ekle','')" class="btn  btn-danger  btn-block adisyon_ekle_buttonlar"  style="font-size:12px">Ürün Ekle</button>
                        </div>
                        <div class="col-md-2 col-sm-4 col-4" style="padding-left: 0;margin-bottom: 20px;">
                           <button disabled type="button" data-toggle="modal" id="adisyon_paket_ekle_button" data-target="#paket_satisi_modal" data-value='' class="btn  btn-primary  btn-block adisyon_ekle_buttonlar" style="font-size:12px">Paket Ekle</button>
                        </div>

                        <div class="col-md-6 text-right" id="tahsilats_type" >
                           <button type="button" class="btn btn-success adisyon_ekle_buttonlar" id='senetle_veya_taksitle_tahsil_et' disabled> Alacaklar</button>
                           @yetki('satis.senet_olustur')
                           <button type="button" id='yeni_taksitli_tahsilat_olusur' href="#"  data-value='' class="btn  btn-primary adisyon_ekle_buttonlar" style="font-weight: bold;" disabled>Taksit Yap</button>
                           @endyetki
                        </div>
                     </div>
                     <div id='tum_tahsilatlar'>
                     </div>
                     <div id="taksitli_ve_senetli_tahsilatlar">
                     </div>
                     <div id="cark_kupon_bolumu" style="display:none;background:#fff3cd;border:1px solid #ffeeba;border-radius:6px;padding:10px;margin:10px 0;">
                        <label style="font-weight:bold;color:#856404;margin:0 0 6px 0;display:block;">🎁 Çark Kuponu</label>
                        <div id="cark_kupon_listesi" style="font-size:12px;color:#856404;margin-bottom:6px;"></div>
                        <div class="row" style="margin:0;">
                           <div class="col-md-8 col-8" style="padding-left:0;">
                              <input type="text" id="cark_kupon_kod" class="form-control" placeholder="Kupon kodu" autocomplete="off" style="text-transform:uppercase;">
                           </div>
                           <div class="col-md-4 col-4" style="padding:0;">
                              <button type="button" id="cark_kupon_uygula_btn" class="btn btn-warning btn-block">Uygula</button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="row tek_tahsilat_formu" data-value="0">
                  <div class="col-md-2 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Tarih</label>
                        <input type="text" required class="form-control" name="tahsilat_tarihi" id='tahsilat_tarihi' value="{{date('Y-m-d')}}" autocomplete="off">
                     </div>
                  </div>
                  <div class="col-md-2 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label style="width: 100%">Birim Tutar (₺)</label>
                        <input  class="form-control try-currency" id='birim_tutar' value=""   style="font-size:20px" >
                     </div>
                  </div>
                  <div class="col-md-2 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Müşteri İndirimi (%)</label>
                        <input type="text" class="form-control" disabled id='musteri_indirim' name="musteri_indirim" value="0">
                        <input type="hidden" id='musteri_indirimi' name="musteri_indirimi">
                     </div>
                  </div>
                  <div class="col-md-3 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>İndirim (₺)</label>
                        <input  type="tel" name="indirim_tutari" id='harici_indirim_tutari' class="form-control try-currency">
                     </div>
                  </div>
                  <div class="col-md-3 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Ödenecek Tutar (₺)</label>
                        <input type="tel" style="font-size: 20px; background-color: #d4edda; border-color: #c3e6cb;" class="form-control try-currency"  name="indirimli_toplam_tahsilat_tutari" id="indirimli_toplam_tahsilat_tutari" value="0">
                     </div>
                  </div>
                  <div class="col-md-4 col-sm-4 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Ödeme Yönetmi</label>
                        <select class="form-control" id='adisyon_tahsilat_odeme_yontemi' name="odeme_yontemi">
                           @foreach($odeme_yontemleri as $odeme_yontemi)
                           <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-4 col-sm-4 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Banka (opsiyonel)</label>
                        <select class="form-control" id='adisyon_tahsilat_banka' name="banka">
                           <option value=''>Seçiniz...</option>
                           @foreach($bankalar as $banka)
                           <option value="{{$banka->id}}">{{$banka->banka}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-4 col-sm-4 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Kalan Alacak Tutarı (₺)</label>
                        <input type="tel" class="form-control try-currency" name="odenecek_tutar" id='odenecek_tutar'>
                     </div>
                  </div>
                  <div class="col-md-6"></div>
                  @yetki('satis.tahsilat_al')
                  <div class="col-md-6">
                     <button disabled id='yeni_tahsilat_ekle' type="submit" class="btn btn-success btn-lg btn-block adisyon_ekle_buttonlar"> <i class="fa fa-money"></i>
                     Tahsil Et </button>
                  </div>
                  @endyetki
               </div>
            </div>
        
      </div>
   </div>
   <div class="col-md-3">
      <div id="odeme_kayit_bolumu">
         <h2>Ödeme</h2>
         <div class="card-box pd-20 odemeozeti"  style="margin-bottom:20px">
            <div class="row">
               <div class="col-12 col-xs-12 col-sm-12">
                  <b style="width: 100%;">Alacak Tutarı (₺)</b>
               </div>
               <div class="col-md-12">
                  <span id="tahsil_edilecek_kalan_tutar" style="color:#ff0000;font-size:30px">
                  </span>
               </div>
               <div class="col-md-12">
                  <table class="table" style="margin-top:20px">
                     <thead id="tahsilat_durumu">
                        <tr>
                           <td colspan="4" style='border:none;font-weight: bold;padding: 20px 0 20px 0;font-size: 16px;'>Özet</td>
                        </tr>
                        <tr>
                           <td colspan="3">Ara Toplam (₺)</td>
                           <td id='ara_toplam' style="text-align:right;">  </td>
                        </tr>
                        <tr>
                           <td colspan="3">Müşteri İndirimi (₺)</td>
                           <td id='uygulanan_indirim_tutari' style="text-align:right;"> </td>
                        </tr>
                        <tr>
                           <td colspan="3">Harici İndirim (₺)</td>
                           <td id='uygulanan_harici_indirim_tutari' style="text-align:right;"> </td>
                        </tr>
                        <tr style="font-weight: bold; color: green;display: none;">
                           <td colspan="3">
                              Ödenen Tutar (₺): 
                           </td>
                           <td id="tahsil_edilen_tutar" style="text-align:right;">
                              {{number_format(0,2,',','.')}}
                           </td>
                        </tr>
                        <tr style="font-weight: bold; color: red;">
                           <td colspan="3">
                              Alacak Tutarı (₺): 
                           </td>
                           <td class="tahsil_edilecek_kalan_tutar" style="text-align:right;">
                           </td>
                        </tr>
                     </thead>
                     <tbody id="tahsilat_listesi">
                        <tr>
                           <td colspan="4" style='border:none;font-weight: bold;padding: 20px 0 20px 0;font-size: 16px;'>Geçmiş Ödemeler</td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
         <button type="submit" class="btn btn-success" style="width:100%;margin-top: 10px;display: none;">Değişiklikleri Kaydet</button>
      </div>
   </div>
</div>
 
</form>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endyetki
<div id="musterifotoekle" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dailog-centered" style="max-width: 560px">
      <form id="musterifotoekleform">
         {{ csrf_field() }}
         <input type="hidden" name="sube" value="{{$isletme->id}}">
         <input name="musteri_id" type="hidden" value="{{$musteri_bilgi->id}}">
         <div class="modal-content" style="min-height: 200px;">
            <div class="modal-header">
               <h4 class="h4">Yeni Resim Ekle</h4>
               <button
                  type="button"
                  class="close"
                  data-dismiss="modal"
                  aria-hidden="true"
                  >
               ×
               </button>
            </div>
            <div class="modal-body" style="padding:1rem 1rem 0rem 1rem;">
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Tarih</label>
                        <input type="text" required class="form-control" name="resimtarih" id="resimtarih" value="{{date('Y-m-d')}}" autocomplete="off">
                     </div>
                  </div>
                  <div class="col-md-6 col-xs-6 col-sm-6 col-6 form-group">
                     <label>Resim Yükle</label>
                     <input type="file" name="musteriresimyukle[]" id="musteriresimyukle" class="form-control-file form-control " multiple>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="justify-content: center;">
               <div class="col-md-6 col-xs-6 col-6 col-sm-6" >
                  <button type="submit" class="btn btn-success btn-block"> Kaydet</button>
               </div>
            </div>
         </div>
      </form>
   </div>
</div>
<div id="islemdetayigetirmodal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dailog-centered" style="max-width: 750px">
      <form id="islemdetayigetirform">
         {{ csrf_field() }}
         <input type="hidden" name="sube" value="{{$isletme->id}}">
         <input name="klasor_id" type="hidden" value="">
         <div class="modal-content" style="min-height: 550px;">
            <div class="modal-header">
               <h4 class="h4">Resimler</h4>
               <button
                  type="button"
                  class="close"
                  data-dismiss="modal"
                  aria-hidden="true"
                  >
               ×
               </button>
            </div>
            <div class="modal-body" style="padding:20px">
               <div class="row" style="overflow-y:scroll; max-height: 500px">
                  <div class="gallery-wrap" id="islembolumu">
                  </div>
               </div>
            </div>
         </div>
      </form>
   </div>
</div>
<script>
   function thisFileUpload() {
       document.getElementById("musteri_profil_resmi").click();
   };
   // Notlar kutusunu en alta (en yeni nota) kaydir
   function mdetayNotlarKaydir(){
       var nb = document.getElementById('musteriNotlarKutu');
       if (nb) { nb.scrollTop = nb.scrollHeight; }
   }
   document.addEventListener('DOMContentLoaded', function(){
       mdetayNotlarKaydir();
       // Bilgiler kaydedildiginde (.musteri_genel_bilgi_kart yeniden doldurulur)
       // Notlar kutusunu tekrar en alta kaydir.
       var kart = document.querySelector('#mdetay .musteri_genel_bilgi_kart');
       if (kart && window.MutationObserver) {
           new MutationObserver(function(){ mdetayNotlarKaydir(); }).observe(kart, {childList:true});
       }
   });
</script>

{{-- ====== DAKIKA PAKETLERI: MODAL + JS ====== --}}
<div id="dakika_paketi_sat_modal" class="modal fade" tabindex="-1" role="dialog">
   <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
      <div class="modal-content">
         <div class="modal-header" style="background:#f5f7fb;">
            <h5 class="modal-title"><b>Yeni Dakika Paketi Sat</b></h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
         </div>
         <div class="modal-body">
            <div class="form-group">
               <label>Hizmet</label>
               <select id="dp_hizmet_id" class="form-control">
                  <option value="">— Hizmet Seciniz —</option>
                  @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->with('hizmetler')->get() as $sh)
                     @if($sh->hizmetler)
                        <option value="{{$sh->hizmet_id}}">{{$sh->hizmetler->hizmet_adi}}</option>
                     @endif
                  @endforeach
               </select>
            </div>
            <div class="form-group">
               <label>Toplam Dakika</label>
               <input type="number" id="dp_toplam_dakika" class="form-control" min="1" placeholder="orn: 100">
            </div>
            <div class="form-group">
               <label>Satis Fiyati (TL)</label>
               <input type="number" id="dp_satis_fiyati" class="form-control" min="0" step="0.01" value="0">
            </div>
            <div class="form-group">
               <label>Bitis Tarihi <small style="color:#999">(opsiyonel, bos = suresiz)</small></label>
               <input type="date" id="dp_bitis_tarihi" class="form-control">
            </div>
            <div class="form-group">
               <label>Notlar</label>
               <textarea id="dp_notlar" class="form-control" rows="2"></textarea>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Iptal</button>
            <button type="button" class="btn btn-primary" onclick="dakikaPaketSatKaydet()">Paketi Sat</button>
         </div>
      </div>
   </div>
</div>

<div id="dakika_paketi_kullanim_modal" class="modal fade" tabindex="-1" role="dialog">
   <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
      <div class="modal-content">
         <div class="modal-header" style="background:#f5f7fb;">
            <h5 class="modal-title"><b>Manuel Kullanim Ekle</b></h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
         </div>
         <div class="modal-body">
            <input type="hidden" id="dpk_paket_id">
            <div id="dpk_paket_bilgi" style="background:#f8f9fa;padding:10px;border-radius:6px;margin-bottom:12px;font-size:13px;"></div>
            <div class="form-group">
               <label>Kullanilan Dakika</label>
               <input type="number" id="dpk_dakika" class="form-control" min="1" placeholder="orn: 10">
            </div>
            <div class="form-group">
               <label>Aciklama</label>
               <textarea id="dpk_aciklama" class="form-control" rows="2" placeholder="orn: Randevusuz geldi, 10 dk yandi"></textarea>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Iptal</button>
            <button type="button" class="btn btn-warning" onclick="dakikaPaketKullanimKaydet()">Dus</button>
         </div>
      </div>
   </div>
</div>

<div id="dakika_paketi_hareket_modal" class="modal fade" tabindex="-1" role="dialog">
   <div class="modal-dialog modal-dialog-centered" style="max-width: 640px;">
      <div class="modal-content">
         <div class="modal-header" style="background:#f5f7fb;">
            <h5 class="modal-title"><b>Hareket Gecmisi</b></h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
         </div>
         <div class="modal-body" style="max-height:500px;overflow-y:auto;">
            <div id="dpgecmis_icerik">Yukleniyor...</div>
         </div>
      </div>
   </div>
</div>

<script>
   const DP_PORTFOY_ID = {{ $portfoy ? (int)$portfoy->id : 0 }};
   const DP_SALON_ID   = {{ (int)$isletme->id }};
   const DP_CSRF       = document.querySelector('meta[name=csrf-token]')?.content || '';

   function dpFmtTr(n){ return Number(n).toLocaleString('tr-TR'); }
   function dpDurumRozet(d){
      const renkler = { aktif:'#28a745', bitti:'#6c757d', iptal:'#dc3545' };
      const yazi    = { aktif:'Aktif', bitti:'Bitti', iptal:'Iptal' };
      return `<span style="background:${renkler[d]||'#6c757d'};color:#fff;padding:2px 8px;border-radius:10px;font-size:11px;">${yazi[d]||d}</span>`;
   }

   async function dpFetch(url, opts){
      opts = opts || {};
      opts.headers = Object.assign({
         'Accept': 'application/json',
         'X-CSRF-TOKEN': DP_CSRF,
         'X-Requested-With': 'XMLHttpRequest',
      }, opts.headers || {});
      if (opts.body && typeof opts.body === 'object' && !(opts.body instanceof FormData)) {
         opts.headers['Content-Type'] = 'application/json';
         opts.body = JSON.stringify(opts.body);
      }
      const r = await fetch(url, opts);
      let j = {};
      try { j = await r.json(); } catch(e){}
      return { ok: r.ok, status: r.status, data: j };
   }

   async function dakikaPaketleriYukle(){
      const kutu = document.getElementById('dakika_paketleri_liste');
      kutu.innerHTML = '<div style="color:#888;text-align:center;padding:30px;">Yukleniyor...</div>';
      if (!DP_PORTFOY_ID) {
         kutu.innerHTML = '<div style="color:#dc3545;text-align:center;padding:30px;">Musteri portfoy bulunamadi.</div>';
         return;
      }
      const res = await dpFetch(`/isletmeyonetim/dakika-paketi/musteri/${DP_PORTFOY_ID}?salon_id=${DP_SALON_ID}`);
      if (!res.ok) {
         kutu.innerHTML = '<div style="color:#dc3545;text-align:center;padding:30px;">Yuklenemedi.</div>';
         return;
      }
      const paketler = res.data.paketler || [];
      if (paketler.length === 0) {
         kutu.innerHTML = '<div style="color:#888;text-align:center;padding:30px;">Bu musterinin dakika paketi yok. Ust sagdan yeni paket satabilirsiniz.</div>';
         return;
      }
      kutu.innerHTML = paketler.map(p => dpPaketKart(p)).join('');
   }

   function dpPaketKart(p){
      const yuzde = p.toplam_dakika > 0 ? Math.round((p.kalan_dakika / p.toplam_dakika) * 100) : 0;
      const barRenk = yuzde > 40 ? '#28a745' : (yuzde > 15 ? '#ffc107' : '#dc3545');
      const aktif = p.durum === 'aktif';
      return `
        <div style="border:1px solid #e0e4eb;border-radius:10px;padding:14px;background:#fff;">
           <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
              <div>
                 <div style="font-weight:600;font-size:15px;">${p.hizmet_adi || 'Hizmet #' + p.hizmet_id}</div>
                 <div style="color:#777;font-size:12px;margin-top:2px;">Satis: ${p.satis_tarihi || '-'} ${p.bitis_tarihi ? ' / Bitis: ' + p.bitis_tarihi : '/ Suresiz'} ${p.satis_fiyati > 0 ? ' / ' + dpFmtTr(p.satis_fiyati) + ' TL' : ''}</div>
              </div>
              ${dpDurumRozet(p.durum)}
           </div>
           <div style="margin:10px 0;">
              <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                 <span><b>${dpFmtTr(p.kalan_dakika)}</b> dk kalan</span>
                 <span style="color:#888">${dpFmtTr(p.kullanilan_dakika)} / ${dpFmtTr(p.toplam_dakika)} kullanildi</span>
              </div>
              <div style="height:8px;background:#eef0f5;border-radius:4px;overflow:hidden;">
                 <div style="height:100%;width:${100-yuzde}%;background:${barRenk};transition:width .3s;"></div>
              </div>
           </div>
           ${p.notlar ? `<div style="color:#666;font-size:12px;font-style:italic;margin:6px 0;">${p.notlar}</div>` : ''}
           <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;">
              ${aktif ? `<button class="btn btn-sm btn-warning" onclick="dakikaPaketKullanimAc(${p.id}, '${(p.hizmet_adi||'').replace(/'/g,'')}', ${p.kalan_dakika})">+ Manuel Kullanim</button>` : ''}
              <button class="btn btn-sm btn-outline-secondary" onclick="dakikaPaketGecmis(${p.id})">Hareket Gecmisi</button>
              ${aktif ? `<button class="btn btn-sm btn-outline-danger" onclick="dakikaPaketIptal(${p.id})">Iptal Et</button>` : ''}
           </div>
        </div>`;
   }

   function dakikaPaketSatModal(){
      document.getElementById('dp_hizmet_id').value = '';
      document.getElementById('dp_toplam_dakika').value = '';
      document.getElementById('dp_satis_fiyati').value = '0';
      document.getElementById('dp_bitis_tarihi').value = '';
      document.getElementById('dp_notlar').value = '';
      $('#dakika_paketi_sat_modal').modal('show');
   }

   async function dakikaPaketSatKaydet(){
      const hizmet_id = document.getElementById('dp_hizmet_id').value;
      const toplam = parseInt(document.getElementById('dp_toplam_dakika').value || '0', 10);
      if (!hizmet_id) { alert('Hizmet seciniz'); return; }
      if (toplam <= 0) { alert('Toplam dakika 0 dan buyuk olmali'); return; }

      const res = await dpFetch('/isletmeyonetim/dakika-paketi/sat', {
         method: 'POST',
         body: {
            salon_id: DP_SALON_ID,
            musteri_portfoy_id: DP_PORTFOY_ID,
            hizmet_id: parseInt(hizmet_id, 10),
            toplam_dakika: toplam,
            satis_fiyati: parseFloat(document.getElementById('dp_satis_fiyati').value || '0'),
            bitis_tarihi: document.getElementById('dp_bitis_tarihi').value || null,
            notlar: document.getElementById('dp_notlar').value || null,
         }
      });
      if (!res.ok) {
         alert(res.data.mesaj || res.data.message || 'Hata olustu');
         return;
      }
      $('#dakika_paketi_sat_modal').modal('hide');
      dakikaPaketleriYukle();
   }

   function dakikaPaketKullanimAc(paketId, hizmetAdi, kalan){
      document.getElementById('dpk_paket_id').value = paketId;
      document.getElementById('dpk_paket_bilgi').innerHTML = `<b>${hizmetAdi}</b> &middot; Kalan: <b>${kalan} dk</b>`;
      document.getElementById('dpk_dakika').value = '';
      document.getElementById('dpk_aciklama').value = '';
      $('#dakika_paketi_kullanim_modal').modal('show');
   }

   async function dakikaPaketKullanimKaydet(){
      const id = document.getElementById('dpk_paket_id').value;
      const dakika = parseInt(document.getElementById('dpk_dakika').value || '0', 10);
      if (dakika <= 0) { alert('Dakika girilmeli'); return; }
      const res = await dpFetch(`/isletmeyonetim/dakika-paketi/${id}/manuel-kullanim`, {
         method: 'POST',
         body: { dakika, aciklama: document.getElementById('dpk_aciklama').value || null }
      });
      if (!res.ok) { alert(res.data.mesaj || 'Hata'); return; }
      $('#dakika_paketi_kullanim_modal').modal('hide');
      dakikaPaketleriYukle();
   }

   async function dakikaPaketGecmis(id){
      $('#dakika_paketi_hareket_modal').modal('show');
      document.getElementById('dpgecmis_icerik').innerHTML = 'Yukleniyor...';
      const res = await dpFetch(`/isletmeyonetim/dakika-paketi/${id}`);
      if (!res.ok) { document.getElementById('dpgecmis_icerik').innerHTML = 'Yuklenemedi'; return; }
      const tur = { randevu_kullanim:'Randevu', manuel_kullanim:'Manuel', iade:'Iade', duzeltme:'Duzeltme' };
      const turRenk = { randevu_kullanim:'#0d6efd', manuel_kullanim:'#fd7e14', iade:'#198754', duzeltme:'#6c757d' };
      const hrk = (res.data.hareketler || []).map(h => `
         <tr>
           <td>${h.tarih || ''}</td>
           <td><span style="background:${turRenk[h.tur]||'#6c757d'};color:#fff;padding:2px 8px;border-radius:8px;font-size:11px;">${tur[h.tur]||h.tur}</span></td>
           <td style="text-align:right;font-weight:600;color:${h.dakika > 0 ? '#dc3545' : '#198754'};">${h.dakika > 0 ? '-' : '+'}${Math.abs(h.dakika)} dk</td>
           <td style="font-size:12px;color:#666;">${h.aciklama || ''}</td>
         </tr>`).join('');
      document.getElementById('dpgecmis_icerik').innerHTML = `
         <table class="table table-sm" style="font-size:13px;">
            <thead><tr><th>Tarih</th><th>Tur</th><th style="text-align:right;">Dakika</th><th>Aciklama</th></tr></thead>
            <tbody>${hrk || '<tr><td colspan=4 style="text-align:center;color:#888;">Henuz hareket yok</td></tr>'}</tbody>
         </table>`;
   }

   async function dakikaPaketIptal(id){
      if (!confirm('Bu paketi iptal etmek istiyor musunuz? Kalan dakika silinir, hareket gecmisi durur.')) return;
      const res = await dpFetch(`/isletmeyonetim/dakika-paketi/${id}/iptal`, { method: 'POST', body: {} });
      if (!res.ok) { alert(res.data.mesaj || 'Hata'); return; }
      dakikaPaketleriYukle();
   }
</script>

@include('isletmeadmin.partials.whatsapp_mesaj_modal')
@include('modaldialogs.harici-tahsilat-modal')
</div>{{-- /#mdetay --}}

<script>
// URL hash'i bir sekmeye isaret ediyorsa onu aç (orn. çağrı merkezinden #tahsilatEkrani ile gelince)
$(function(){
   var h = window.location.hash;
   if (h && h.length > 1){
      var $t = $('a[data-toggle="tab"][href="'+h+'"]');
      if ($t.length){ setTimeout(function(){ $t.tab('show'); $t[0].scrollIntoView({block:'start'}); }, 300); }
   }
});

// Tek-tik memnuniyet anketi gonderim (WA-first + SMS fallback backend'de)
function anketHizliGonder(userId, btn){
   if (!confirm('Bu müşteriye memnuniyet anketi gönderilsin mi?')) return;
   var $btn = $(btn);
   var eskiHtml = $btn.html();
   $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Gönderiliyor...');
   $.ajax({
      url: '/isletmeyonetim/anket-hizli-gonder',
      method: 'POST',
      data: { user_id: userId, sube: (new URLSearchParams(location.search)).get('sube') || '' },
      dataType: 'json',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
   }).done(function(res){
      if (res && res.basarili) {
         var kanal = res.kanal === 'whatsapp' ? 'WhatsApp' : 'SMS';
         toastr && toastr.success ? toastr.success('Anket ' + kanal + ' ile gönderildi.') : alert('Anket ' + kanal + ' ile gönderildi.');
      } else {
         toastr && toastr.error ? toastr.error(res && res.mesaj ? res.mesaj : 'Anket gönderilemedi.') : alert(res && res.mesaj ? res.mesaj : 'Anket gönderilemedi.');
      }
   }).fail(function(){
      alert('İstek başarısız. Ağ hatası olabilir.');
   }).always(function(){
      $btn.prop('disabled', false).html(eskiHtml);
   });
}
</script>
@endsection