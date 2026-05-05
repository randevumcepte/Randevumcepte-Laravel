{{-- ================= Personel Modal (Ekle / Düzenle) — Modern Tasarim ================= --}}
<style>
   #personel-modal .modal-dialog{
      max-width: 920px !important; width: 94%;
      margin: 1.5rem auto !important;
   }
   #personel-modal .modal-content{
      border:0; border-radius: 22px; overflow: hidden;
      box-shadow: 0 25px 60px rgba(15,23,42,.22);
   }
   /* Header — mor gradient + ikon */
   #personel-modal .modal-header{
      background: linear-gradient(135deg,#5C008E 0%,#7B2FB8 50%,#9D5DC8 100%);
      color:#fff; border:0; padding: 22px 28px;
      position:relative; display:flex; align-items:center; justify-content:space-between;
   }
   #personel-modal .modal-header::before{
      content:''; position:absolute; top:-40px; right:-40px; width:160px; height:160px;
      background: radial-gradient(circle, rgba(255,255,255,.18) 0%, transparent 70%);
      border-radius:50%;
   }
   #personel-modal .modal-header__inner{
      display:flex; align-items:center; gap:14px; position:relative; z-index:2;
   }
   #personel-modal .modal-header__icon{
      width:48px; height:48px; border-radius:12px; background:rgba(255,255,255,.18);
      display:flex; align-items:center; justify-content:center; font-size:20px;
      backdrop-filter: blur(6px);
   }
   #personel-modal .modal-header h2.modal_baslik{
      color:#fff !important; font-size:18px; font-weight:700; margin:0; padding:0;
      letter-spacing:-.2px;
   }
   #personel-modal .modal-header__sub{
      color: rgba(255,255,255,.82); font-size:12px; margin-top:3px;
   }
   #personel-modal .pm-close-x{
      width:38px; height:38px; border-radius:10px; border:0;
      background:rgba(255,255,255,.18); color:#fff; font-size:20px;
      display:flex; align-items:center; justify-content:center; cursor:pointer;
      transition:.15s; position:relative; z-index:2;
   }
   #personel-modal .pm-close-x:hover{ background:rgba(255,255,255,.32); }

   /* Body */
   #personel-modal .modal-body{
      padding: 24px 28px; background:#fafbfc; max-height:calc(100vh - 200px); overflow-y:auto;
   }

   /* Section card */
   .pm-section{
      background:#fff; border-radius:14px; padding:18px 20px;
      border:1px solid #ece6f2; margin-bottom:14px;
      box-shadow: 0 1px 3px rgba(92,0,142,.04);
   }
   .pm-section__head{
      display:flex; align-items:center; gap:10px; margin-bottom:14px;
      padding-bottom:12px; border-bottom:1px solid #f1edf5;
   }
   .pm-section__icon{
      width:36px; height:36px; border-radius:10px; flex-shrink:0;
      display:flex; align-items:center; justify-content:center; color:#fff; font-size:14px;
   }
   .pm-section__icon--info{ background: linear-gradient(135deg,#5C008E,#7B2FB8); }
   .pm-section__icon--tanitim{ background: linear-gradient(135deg,#3b82f6,#60a5fa); }
   .pm-section__icon--saatler{ background: linear-gradient(135deg,#10b981,#34d399); }
   .pm-section__icon--prim{ background: linear-gradient(135deg,#f59e0b,#fbbf24); }
   .pm-section__title{ font-size:14.5px; font-weight:700; color:#2d1b3f; margin:0; }
   .pm-section__sub{ font-size:11.5px; color:#8a8295; margin:1px 0 0; font-weight:500; }
   .pm-section__opt{
      font-size:10.5px; font-weight:700; color:#9ca3af; background:#f3f4f6;
      padding:2px 8px; border-radius:10px; margin-left:auto; letter-spacing:.3px;
   }

   /* Form alanlari */
   #personel-modal .pm-field{ margin-bottom:14px; }
   #personel-modal .pm-field label,
   #personel-modal .form-group label{
      display:block; font-size:11.5px; font-weight:700; color:#475569;
      letter-spacing:.3px; text-transform:uppercase; margin-bottom:6px;
   }
   #personel-modal .form-control,
   #personel-modal input[type=text],
   #personel-modal input[type=tel],
   #personel-modal input[type=number],
   #personel-modal input[type=time],
   #personel-modal select,
   #personel-modal textarea{
      border:1.5px solid #e5e7eb !important; border-radius:10px !important;
      padding:9px 13px !important; font-size:13.5px !important;
      background:#fff !important; color:#1e293b !important;
      transition:all .15s; box-shadow:none !important;
      width:100%; height:auto;
   }
   #personel-modal .form-control:focus,
   #personel-modal input:focus,
   #personel-modal select:focus,
   #personel-modal textarea:focus{
      outline:none !important;
      border-color:#7B2FB8 !important;
      box-shadow: 0 0 0 3px rgba(123,47,184,.1) !important;
   }
   #personel-modal textarea{ resize:vertical; min-height:90px; }
   #personel-modal .form-help{
      font-size:11px; color:#94a3b8; margin-top:4px; display:block; font-weight:500;
   }

   /* Calisma/Mola saatleri */
   .pm-saat-grid{
      display:grid; grid-template-columns: 1fr 1fr; gap:14px;
   }
   @media(max-width:780px){ .pm-saat-grid{ grid-template-columns: 1fr; } }
   .pm-saat-list{ display:flex; flex-direction:column; gap:6px; }
   .pm-saat-row{
      display:grid; grid-template-columns: 36px 90px 1fr 1fr; gap:8px; align-items:center;
      background:#f8fafc; padding:7px 10px; border-radius:10px; border:1px solid #eef0f3;
      transition:.15s;
   }
   .pm-saat-row:hover{ border-color:#cbd5e1; background:#fff; }
   .pm-saat-row.is-checked{ background:#f7f1fb; border-color:#e0d4ec; }

   /* Custom switch — be-checkbox kullaniyor ama biz toggle stili veriyoruz */
   .pm-switch{ position:relative; width:36px; height:20px; }
   .pm-switch input[type=checkbox]{
      position:absolute; opacity:0; width:100%; height:100%; margin:0; cursor:pointer; z-index:2;
   }
   .pm-switch label{
      position:absolute; top:0; left:0; right:0; bottom:0;
      background:#cbd5e1; border-radius:20px; transition:.2s; cursor:pointer; margin:0 !important;
      text-transform:none !important; letter-spacing:0 !important; font-weight:normal !important;
   }
   .pm-switch label::before{
      content:''; position:absolute; top:2px; left:2px; width:16px; height:16px;
      background:#fff; border-radius:50%; transition:.2s;
      box-shadow: 0 1px 3px rgba(0,0,0,.2);
   }
   .pm-switch input:checked + label{ background: linear-gradient(135deg,#7B2FB8,#9D5DC8); }
   .pm-switch input:checked + label::before{ left:18px; }

   /* Inline toggle satiri (form icinde sirit-mayan modern anahtar) */
   #personel-modal .pm-toggle-row{
      display:flex; align-items:center; justify-content:space-between; gap:14px;
      background:#f8fafc; border:1px solid #eef0f3; border-radius:12px;
      padding:11px 16px; transition:.15s;
   }
   #personel-modal .pm-toggle-row:hover{ border-color:#e0d4ec; background:#fbf8fd; }
   #personel-modal .pm-toggle-row.is-on{ border-color:#d8c8e8; background:#faf6fc; }
   #personel-modal .pm-toggle-row__info{ display:flex; flex-direction:column; gap:3px; flex:1; min-width:0; }
   #personel-modal .pm-toggle-row__title{
      font-size:13px; font-weight:700; color:#2d1b3f; letter-spacing:0;
      text-transform:none; margin:0; display:flex; align-items:center; gap:8px;
   }
   #personel-modal .pm-toggle-row__title i{ color:#7B2FB8; font-size:14px; }
   #personel-modal .pm-toggle-row__hint{
      font-size:11.5px; color:#94a3b8; font-weight:500; margin:0; padding-left:22px;
   }

   .pm-saat-day{ font-size:13px; font-weight:600; color:#475569; }
   .pm-saat-row input[type=time]{
      border:1px solid #e5e7eb !important; border-radius:8px !important;
      padding:6px 10px !important; font-size:12.5px !important; background:#fff !important;
      width:100% !important;
   }

   /* Prim alanlari grid */
   .pm-prim-grid{ display:grid; grid-template-columns: repeat(4, 1fr); gap:10px; }
   @media(max-width:780px){ .pm-prim-grid{ grid-template-columns: repeat(2, 1fr); } }
   @media(max-width:500px){ .pm-prim-grid{ grid-template-columns: 1fr; } }

   /* Footer */
   #personel-modal .modal-footer{
      padding:16px 28px; background:#fff; border-top:1px solid #ece6f2;
      display:flex !important; justify-content:flex-end; gap:10px;
   }
   #personel-modal .modal-footer > div.row{ display:none !important; }
   #personel-modal .pm-btn-iptal{
      background:#f3f4f6; color:#64748b; border:0;
      padding:10px 22px; border-radius:10px; font-weight:600; font-size:13.5px; cursor:pointer;
   }
   #personel-modal .pm-btn-iptal:hover{ background:#e5e7eb; color:#334155; }
   #personel-modal .pm-btn-kaydet{
      background: linear-gradient(135deg,#5C008E,#7B2FB8 60%,#9D5DC8); color:#fff; border:0;
      padding:10px 28px; border-radius:10px; font-weight:600; font-size:13.5px;
      box-shadow: 0 4px 14px rgba(92,0,142,.3); transition:.15s; cursor:pointer;
      display:inline-flex; align-items:center; gap:8px;
   }
   #personel-modal .pm-btn-kaydet:hover{
      transform: translateY(-1px); box-shadow: 0 8px 22px rgba(92,0,142,.4); color:#fff;
   }
</style>

<div id="personel-modal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <form id="yenipersonelbilgiekle" method="POST">
            {!!csrf_field()!!}
            <input type="hidden" name="personel_id" id='personel_id'>
            <input type="hidden" name="sube" value="{{$isletme->id}}">

            <div class="modal-header">
               <div class="modal-header__inner">
                  <div class="modal-header__icon"><i class="fa fa-user"></i></div>
                  <div>
                     <h2 class="modal_baslik">Personel Bilgileri</h2>
                     <div class="modal-header__sub">Personel bilgilerini, çalışma saatlerini ve hak ediş ayarlarını düzenleyin</div>
                  </div>
               </div>
               <button type="button" class="pm-close-x" data-dismiss="modal" aria-label="Kapat">&times;</button>
            </div>

            <div class="modal-body">

               {{-- ================ TEMEL BILGILER ================ --}}
               <div class="pm-section">
                  <div class="pm-section__head">
                     <div class="pm-section__icon pm-section__icon--info"><i class="fa fa-id-badge"></i></div>
                     <div>
                        <div class="pm-section__title">Temel Bilgiler</div>
                        <div class="pm-section__sub">Personel adı, iletişim ve hesap türü</div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-4">
                        <div class="pm-field">
                           <label>Personel Adı <span style="color:#dc2626">*</span></label>
                           <input id="personel_adi" name="personel_adi" required class="form-control" placeholder="Ör: Ayşe Yılmaz">
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="pm-field">
                           <label>Cinsiyet</label>
                           <select id="cinsiyet" name="cinsiyet" class="form-control">
                              <option value="0">Kadın</option>
                              <option value="1">Erkek</option>
                           </select>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="pm-field">
                           <label>Cep Telefon <span style="color:#dc2626">*</span></label>
                           <input type="tel" name='cep_telefon' id='cep_telefon' data-inputmask="'mask' : '5999999999'" required class="form-control" placeholder="5XXXXXXXXX">
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="pm-field">
                           <label>Unvan</label>
                           <input class="form-control" id='unvan' name="unvan" type="text" placeholder="Ör: Kuaför · Kalfa · Stajyer">
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="pm-field">
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
                     <div class="col-md-12">
                        <div class="pm-field" style="margin-bottom:0">
                           <div class="pm-toggle-row" id="takvimde_gorunsun_row">
                              <div class="pm-toggle-row__info">
                                 <div class="pm-toggle-row__title"><i class="fa fa-calendar-check-o"></i> Takvimde Görünsün</div>
                                 <div class="pm-toggle-row__hint">Kapatılırsa bu personel randevu takviminde gösterilmez</div>
                              </div>
                              <div class="pm-switch">
                                 <input type="checkbox" id="takvimde_gorunsun" name="takvimde_gorunsun" value="1" checked>
                                 <label for="takvimde_gorunsun"></label>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               {{-- ================ TANITIM SAYFASI ================ --}}
               <div class="pm-section">
                  <div class="pm-section__head">
                     <div class="pm-section__icon pm-section__icon--tanitim"><i class="fa fa-id-card-o"></i></div>
                     <div style="flex:1">
                        <div class="pm-section__title">Tanıtım Sayfası Bilgileri</div>
                        <div class="pm-section__sub">Online tanıtım sayfanızda personel kartında müşterilere gösterilir</div>
                     </div>
                     <span class="pm-section__opt">OPSİYONEL</span>
                  </div>
                  <div class="row">
                     <div class="col-md-6">
                        <div class="pm-field">
                           <label>Uzmanlık Alanı</label>
                           <input class="form-control" id="uzmanlik" name="uzmanlik" type="text" placeholder="Ör: Saç Boyama · Balyaj · Kaynak" maxlength="200">
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="pm-field">
                           <label>Yıllık Tecrübe</label>
                           <input class="form-control" id="yillik_tecrube" name="yillik_tecrube" type="number" min="0" max="80" placeholder="Ör: 8">
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="pm-field">
                           <label>Instagram</label>
                           <input class="form-control" id="instagram" name="instagram" type="text" placeholder="kullaniciadi" maxlength="150">
                        </div>
                     </div>
                     <div class="col-md-12">
                        <div class="pm-field">
                           <label>Detaylı Açıklama / Biyografi</label>
                           <textarea class="form-control" id="aciklama" name="aciklama" rows="4" maxlength="1500" placeholder="Ör: 10 yılı aşkın deneyimiyle müşterilerine en uygun saç stilini öneriyor. Özel gün makyajı ve balyaj konusunda uzmandır."></textarea>
                           <span class="form-help">Maksimum 1500 karakter. Personelin tecrübesi, eğitimleri ve uzmanlığını özetleyin.</span>
                        </div>
                     </div>
                  </div>
               </div>

               {{-- ================ CALISMA & MOLA SAATLERI ================ --}}
               @php $gunler = ['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar']; @endphp
               <div class="pm-section">
                  <div class="pm-section__head">
                     <div class="pm-section__icon pm-section__icon--saatler"><i class="fa fa-clock-o"></i></div>
                     <div>
                        <div class="pm-section__title">Çalışma & Mola Saatleri</div>
                        <div class="pm-section__sub">Hangi gün, hangi saatler arası çalıştığını ve mola saatlerini belirleyin</div>
                     </div>
                  </div>
                  <div class="pm-saat-grid">
                     <div>
                        <div style="font-size:11px; font-weight:700; color:#10b981; letter-spacing:.4px; text-transform:uppercase; margin-bottom:8px"><i class="fa fa-check-circle"></i> Çalışma Saatleri</div>
                        <div class="pm-saat-list">
                           @foreach($gunler as $i => $gun)
                              @php $n = $i+1; @endphp
                              <div class="pm-saat-row">
                                 <div class="be-checkbox be-checkbox-color inline pm-switch">
                                    <input type="checkbox" id="personelcalisiyor{{$n}}" name="calisiyor{{$n}}">
                                    <label for="calisiyor{{$n}}"></label>
                                 </div>
                                 <span class="pm-saat-day">{{$gun}}</span>
                                 <input type="time" id='personelbaslangicsaati{{$n}}' value="00:00" name="baslangicsaati{{$n}}">
                                 <input type="time" id='personelbitissaati{{$n}}' value="00:00" name="bitissaati{{$n}}">
                              </div>
                           @endforeach
                        </div>
                     </div>
                     <div>
                        <div style="font-size:11px; font-weight:700; color:#f59e0b; letter-spacing:.4px; text-transform:uppercase; margin-bottom:8px"><i class="fa fa-coffee"></i> Mola Saatleri</div>
                        <div class="pm-saat-list">
                           @foreach($gunler as $i => $gun)
                              @php $n = $i+1; @endphp
                              <div class="pm-saat-row">
                                 <div class="be-checkbox be-checkbox-color inline pm-switch">
                                    <input type="checkbox" id="personelmolavar{{$n}}" name="molavar{{$n}}">
                                    <label for="molavar{{$n}}"></label>
                                 </div>
                                 <span class="pm-saat-day">{{$gun}}</span>
                                 <input type="time" id='personelmolabaslangicsaati{{$n}}' value="00:00" name="molabaslangicsaati{{$n}}">
                                 <input type="time" id='personelmolabitissaati{{$n}}' value="00:00" name="molabitissaati{{$n}}">
                              </div>
                           @endforeach
                        </div>
                     </div>
                  </div>
               </div>

               {{-- ================ PRIM & HAK EDIS ================ --}}
               <div class="pm-section">
                  <div class="pm-section__head">
                     <div class="pm-section__icon pm-section__icon--prim"><i class="fa fa-money"></i></div>
                     <div>
                        <div class="pm-section__title">Prim & Hak Ediş Ayarları</div>
                        <div class="pm-section__sub">Aylık maaş ve tahsilat üzerinden prim yüzdeleri (Hak Ediş raporunda otomatik hesaplanır)</div>
                     </div>
                  </div>
                  <div class="pm-prim-grid">
                     <div class="pm-field" style="margin:0">
                        <label>Sabit Maaş (₺)</label>
                        <input type="number" step="0.01" min="0" id='personel_maas' name="personel_maas" class="form-control" placeholder="Ör: 15000">
                     </div>
                     <div class="pm-field" style="margin:0">
                        <label>Hizmet Primi (%)</label>
                        <input type="number" step="0.01" min="0" max="100" id='hizmet_prim_yuzde' name="hizmet_prim_yuzde" class="form-control" placeholder="Ör: 20">
                     </div>
                     <div class="pm-field" style="margin:0">
                        <label>Ürün Primi (%)</label>
                        <input type="number" step="0.01" min="0" max="100" id='urun_prim_yuzde' name="urun_prim_yuzde" class="form-control" placeholder="Ör: 10">
                     </div>
                     <div class="pm-field" style="margin:0">
                        <label>Paket Primi (%)</label>
                        <input type="number" step="0.01" min="0" max="100" id='paket_prim_yuzde' name="paket_prim_yuzde" class="form-control" placeholder="Ör: 15">
                     </div>
                  </div>
               </div>

            </div>

            <div class="modal-footer">
               <button type="button" class="pm-btn-iptal modal_kapat" data-dismiss="modal">İptal</button>
               <button type="submit" class="pm-btn-kaydet"><i class="fa fa-check"></i> Kaydet</button>
            </div>
         </form>
      </div>
   </div>
</div>

<script>
$(document).ready(function(){
   // Checkbox change ile satira is-checked sinifi ekle/kaldir
   $('#personel-modal').on('change', '.pm-switch input[type=checkbox]', function(){
      $(this).closest('.pm-saat-row').toggleClass('is-checked', this.checked);
   });
   // Takvimde gorunsun toggle: satira is-on sinifi
   $('#personel-modal').on('change', '#takvimde_gorunsun', function(){
      $('#takvimde_gorunsun_row').toggleClass('is-on', this.checked);
   });
   // Modal acildiginda mevcut checkbox state'lerine gore gorsel state uygula
   $('#personel-modal').on('shown.bs.modal', function(){
      $('.pm-switch input[type=checkbox]').each(function(){
         $(this).closest('.pm-saat-row').toggleClass('is-checked', this.checked);
      });
      $('#takvimde_gorunsun_row').toggleClass('is-on', $('#takvimde_gorunsun').is(':checked'));
   });
});
</script>
