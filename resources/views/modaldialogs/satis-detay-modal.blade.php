 <div
         id="satisKalemleri"
         class="modal modal-top fade calendar-modal sd-modal"
         >
         <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width:1200px;width:95%;">
            <div class="modal-content sd-content" style="max-width:1200px; width:100%; border:none; border-radius:14px; overflow:hidden; box-shadow:0 18px 48px -16px rgba(15,23,42,.35);">
               <form id="satis_listesi">
                  <input type="hidden" name="adisyon_id">
                  <input type="hidden" id="harici_indirim_tutari" value="0">
                  <input type="hidden" id="musteri_indirim" value="0">
                  <input type="hidden" class="form-control try-currency" name="indirimli_toplam_tahsilat_tutari" id="indirimli_toplam_tahsilat_tutari" value="0">

                  {{-- ===== HEADER BAR ===== --}}
                  <div class="sd-header">
                     <div class="sd-title">
                        <i class="fa fa-receipt"></i>
                        <span>Satış Detayları</span>
                     </div>

                     <div class="sd-tarih-edit">
                        <i class="fa fa-calendar"></i>
                        <span class="sd-tarih-label">Satış Tarihi:</span>
                        <strong id="sd-tarih-goster">—</strong>
                        <button type="button" id="sd-tarih-duzenle" class="sd-mini-btn" title="Tarihi düzenle"><i class="fa fa-pencil"></i></button>
                        <input type="text" name="satis_tarihi_duzenle" id="satis_tarihi_duzenle" class="form-control geriye-yonelik sd-tarih-input" autocomplete="off" value="" placeholder="yyyy-aa-gg" style="display:none;">
                        <button type="button" id="sd-tarih-kaydet" class="sd-mini-btn sd-mini-ok" style="display:none;"><i class="fa fa-check"></i></button>
                        <button type="button" id="sd-tarih-iptal" class="sd-mini-btn" style="display:none;">×</button>
                     </div>

                     <button type="button" class="sd-close" data-dismiss="modal" title="Kapat">×</button>
                  </div>

                  <div class="modal-body sd-body">

                     <input type="hidden" name="sube" value="{{$isletme->id}}">

                     {{-- ===== EKLE BUTONLARI ===== --}}
                     <div class="sd-add-buttons">
                        <button type="button" data-toggle="modal" data-target="#adisyon_yeni_hizmet_modal" id="adisyon_hizmet_ekle_button" class="sd-add-btn hizmet adisyon_ekle_buttonlar">
                           <i class="fa fa-plus"></i> Hizmet Ekle
                        </button>
                        <button type="button" data-toggle="modal" id="adisyon_urun_ekle_button" data-target="#urun_satisi_modal" data-value='' onclick="modalbaslikata('Yeni Ürün Satışı Ekle','')" class="sd-add-btn urun adisyon_ekle_buttonlar">
                           <i class="fa fa-plus"></i> Ürün Ekle
                        </button>
                        <button type="button" data-toggle="modal" id="adisyon_paket_ekle_button" data-target="#paket_satisi_modal" data-value='' class="sd-add-btn paket adisyon_ekle_buttonlar">
                           <i class="fa fa-plus"></i> Paket Ekle
                        </button>
                     </div>

                     {{-- ===== SATIRLAR BAŞLIĞI ===== --}}
                     <div class="sd-section">
                        <div class="sd-row sd-row-head">
                           <div class="sd-col-name">Hizmet / Ürün / Paket</div>
                           <div class="sd-col-seller">Satıcı</div>
                           <div class="sd-col-qty">Miktar / Seans</div>
                           <div class="sd-col-amount">Tutar (₺)</div>
                        </div>
                        <div id='tum_tahsilatlar_duzenleme' class="sd-items">

                        </div>
                     </div>

                     {{-- ===== ÖZET + GEÇMİŞ ÖDEMELER ===== --}}
                     <div id="odeme_kayit_bolumu">
                        <div class="sd-summary card-box odemeozeti">

                           {{-- Sol: Tutarlar --}}
                           <div class="sd-summary-amounts">
                              <div class="sd-amount-block">
                                 <div class="sd-amount-label">Toplam Tutar</div>
                                 <div class="sd-amount-val total"><span id="adisyon_toplam_tutar"></span> <span class="sd-tl">₺</span></div>
                              </div>
                              <div class="sd-amount-block">
                                 <div class="sd-amount-label">Ödenen Tutar</div>
                                 <div class="sd-amount-val paid"><span id="adisyon_odenen_tutar"></span> <span class="sd-tl">₺</span></div>
                              </div>
                              <div class="sd-amount-block">
                                 <div class="sd-amount-label">Kalan Tutar</div>
                                 <div class="sd-amount-val remaining"><span id="tahsil_edilecek_kalan_tutar"></span> <span class="sd-tl">₺</span></div>
                              </div>
                           </div>

                           {{-- Sağ: Geçmiş Ödemeler --}}
                           <div class="sd-summary-history">
                              <table class="table sd-summary-table">
                                 <thead id="tahsilat_durumu" style="display:none">
                                    <tr><td colspan="4" style='border:none;font-weight:700; font-size:14px;'>Özet</td></tr>
                                    <tr><td colspan="3">Ara Toplam (₺)</td><td id='ara_toplam' style="text-align:right;"></td></tr>
                                    <tr><td colspan="3">Müşteri İndirimi (₺)</td><td id='uygulanan_indirim_tutari' style="text-align:right;"></td></tr>
                                    <tr><td colspan="3">Harici İndirim (₺)</td><td id='uygulanan_harici_indirim_tutari' style="text-align:right;"></td></tr>
                                    <tr style="font-weight:700; color:green; display:none;">
                                       <td colspan="3">Ödenen Tutar (₺):</td>
                                       <td id="tahsil_edilen_tutar" style="text-align:right;"></td>
                                    </tr>
                                    <tr style="font-weight:700; color:red;">
                                       <td colspan="3">Alacak Tutarı (₺):</td>
                                       <td class="tahsil_edilecek_kalan_tutar" style="text-align:right;"></td>
                                    </tr>
                                 </thead>
                                 <tbody id="tahsilat_listesi_duzenleme">
                                    <tr><td colspan="4" style='border:none;font-weight:700; font-size:14px; color:#475569;'><i class="fa fa-history" style="color:#7B2FB8; margin-right:6px;"></i> Geçmiş Ödemeler</td></tr>
                                 </tbody>
                              </table>
                           </div>
                        </div>
                        <button type="submit" class="btn btn-success" style="width:100%;margin-top:10px;display:none;">Değişiklikleri Kaydet</button>
                     </div>
                  </div>

                  <div class="modal-footer sd-footer">
                     <button type="button" class="sd-btn sd-btn-cancel" data-dismiss="modal">
                        <i class="fa fa-times"></i> Kapat
                     </button>
                     <button type="submit" class="sd-btn sd-btn-save">
                        <i class="fa fa-check"></i> Kaydet
                     </button>
                  </div>
               </form>
            </div>
         </div>

         {{-- ===== MODERN STYLES ===== --}}
         <style>
            #satisKalemleri.sd-modal *{ box-sizing:border-box; }
            #satisKalemleri .sd-content{ font-family:'Inter','Segoe UI',sans-serif; background:#f8fafc; }

            /* Header */
            #satisKalemleri .sd-header{
               display:flex; align-items:center; gap:18px; flex-wrap:wrap;
               background:#fff; padding:16px 22px; border-bottom:1px solid #e5e7eb;
            }
            #satisKalemleri .sd-title{
               display:flex; align-items:center; gap:10px;
               font-size:20px; font-weight:700; color:#0f172a;
            }
            #satisKalemleri .sd-title i{ color:#7B2FB8; font-size:22px; }
            #satisKalemleri .sd-tarih-edit{
               display:inline-flex; align-items:center; gap:8px;
               font-size:14px; color:#475569;
               background:#f8fafc; border:1px solid #e2e8f0; border-radius:9px;
               padding:6px 12px;
            }
            #satisKalemleri .sd-tarih-edit i.fa-calendar{ color:#7B2FB8; }
            #satisKalemleri .sd-tarih-edit .sd-tarih-label{ color:#64748b; font-size:13px; }
            #satisKalemleri .sd-tarih-edit strong{ color:#0f172a; font-size:14.5px; font-weight:700; }
            #satisKalemleri .sd-tarih-input{
               height:30px !important; width:140px !important; padding:0 8px !important;
               border:1px solid #cbd5e1 !important; border-radius:6px !important;
               font-size:13px !important; margin:0 !important;
            }
            #satisKalemleri .sd-mini-btn{
               background:#fff; border:1px solid #cbd5e1; color:#475569;
               padding:5px 9px; border-radius:6px; font-size:12px; line-height:1;
               cursor:pointer; display:inline-flex; align-items:center; gap:4px;
            }
            #satisKalemleri .sd-mini-btn:hover{ background:#f1f5f9; color:#0f172a; }
            #satisKalemleri .sd-mini-btn.sd-mini-ok{ background:#10b981; border-color:#10b981; color:#fff; }
            #satisKalemleri .sd-mini-btn.sd-mini-ok:hover{ background:#059669; }
            #satisKalemleri .sd-close{
               margin-left:auto; background:transparent; border:none;
               font-size:28px; line-height:1; color:#94a3b8; cursor:pointer; padding:0 6px;
            }
            #satisKalemleri .sd-close:hover{ color:#dc2626; }

            /* Body */
            #satisKalemleri .sd-body{ padding:18px 22px; }

            /* Add buttons */
            #satisKalemleri .sd-add-buttons{
               display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px;
            }
            #satisKalemleri .sd-add-btn{
               padding:8px 14px; font-size:13px; font-weight:600; border-radius:8px;
               border:1px solid; cursor:pointer; display:inline-flex; align-items:center; gap:6px;
               transition:all .12s ease; background:#fff;
            }
            #satisKalemleri .sd-add-btn.hizmet{ border-color:#3b82f6; color:#1d4ed8; }
            #satisKalemleri .sd-add-btn.hizmet:hover{ background:#3b82f6; color:#fff; }
            #satisKalemleri .sd-add-btn.urun{ border-color:#ef4444; color:#b91c1c; }
            #satisKalemleri .sd-add-btn.urun:hover{ background:#ef4444; color:#fff; }
            #satisKalemleri .sd-add-btn.paket{ border-color:#8b5cf6; color:#6d28d9; }
            #satisKalemleri .sd-add-btn.paket:hover{ background:#8b5cf6; color:#fff; }

            /* Section card */
            #satisKalemleri .sd-section{
               background:#fff; border:1px solid #e5e7eb; border-radius:12px;
               padding:12px 14px; margin-bottom:14px;
               box-shadow:0 2px 8px -4px rgba(15,23,42,.06);
            }
            #satisKalemleri .sd-row-head{
               display:grid; grid-template-columns: 1.6fr 1fr 1fr 1.1fr;
               gap:10px; padding:6px 10px; font-size:11.5px; font-weight:700;
               color:#64748b; text-transform:uppercase; letter-spacing:.4px;
               border-bottom:1px solid #f1f5f9; margin-bottom:6px;
            }
            #satisKalemleri .sd-row-head .sd-col-amount{ text-align:right; }
            #satisKalemleri .sd-items{ display:flex; flex-direction:column; gap:5px; }

            /* Server-rendered item rows: override the bootstrap layout to look modern */
            #satisKalemleri .sd-items .tahsilat_kalemleri_listesi{
               display:grid !important; grid-template-columns: 1.6fr 1fr 1fr 1.1fr !important;
               gap:10px !important; align-items:center !important;
               background:#f8fafc !important; border-left:3px solid #7B2FB8 !important;
               border-radius:8px !important; padding:9px 12px !important;
               margin:0 !important; font-size:13px !important;
            }
            #satisKalemleri .sd-items .tahsilat_kalemleri_listesi:hover{ background:#f1f5f9 !important; }
            #satisKalemleri .sd-items .tahsilat_kalemleri_listesi > div{
               padding:0 !important; max-width:none !important; flex:none !important;
            }
            #satisKalemleri .sd-items .tahsilat_kalemleri_listesi > div:first-child{
               font-weight:600; color:#0f172a;
            }
            #satisKalemleri .sd-items .tahsilat_kalemleri_listesi > div:nth-child(2){
               color:#64748b; font-size:12.5px;
            }
            #satisKalemleri .sd-items .tahsilat_kalemleri_listesi input[type="tel"]{
               height:30px !important; padding:0 8px !important;
               border:1px solid #cbd5e1 !important; border-radius:6px !important;
               font-size:13px !important;
            }
            #satisKalemleri .sd-items .tahsilat_kalemleri_listesi input.tahsilat_kalemleri{
               text-align:right !important; font-weight:700 !important; color:#0f172a !important;
            }

            /* Summary card */
            #satisKalemleri .sd-summary{
               display:grid; grid-template-columns: 1.1fr 1fr; gap:0;
               background:#fff; border:1px solid #e5e7eb; border-radius:12px;
               overflow:hidden; box-shadow:0 4px 14px -6px rgba(15,23,42,.1);
               padding:0 !important; margin-bottom:0 !important;
            }
            #satisKalemleri .sd-summary-amounts{
               padding:18px 22px; background:linear-gradient(135deg,#fafbff 0%,#fff 100%);
               border-right:1px solid #f1f5f9;
               display:flex; flex-direction:column; gap:10px; justify-content:center;
            }
            #satisKalemleri .sd-amount-block{ display:flex; align-items:baseline; justify-content:space-between; gap:14px; }
            #satisKalemleri .sd-amount-label{
               font-size:11.5px; font-weight:700; color:#64748b;
               text-transform:uppercase; letter-spacing:.5px;
            }
            #satisKalemleri .sd-amount-val{ font-size:24px; font-weight:800; line-height:1.1; letter-spacing:-.3px; }
            #satisKalemleri .sd-amount-val.total{ color:#0f172a; }
            #satisKalemleri .sd-amount-val.paid{ color:#059669; }
            #satisKalemleri .sd-amount-val.remaining{ color:#dc2626; }
            #satisKalemleri .sd-amount-val .sd-tl{ font-size:14px; font-weight:600; opacity:.6; margin-left:2px; }

            #satisKalemleri .sd-summary-history{ padding:14px 18px; background:#fafbff; }
            #satisKalemleri .sd-summary-table{ margin:0; font-size:13px; }
            #satisKalemleri .sd-summary-table td{ border:none; padding:6px 4px; vertical-align:middle; }
            #satisKalemleri .sd-summary-table tbody tr td:first-child{ color:#0f172a; }
            #satisKalemleri #tahsilat_listesi_duzenleme tr td button[name="tahsilat_adisyondan_sil"]{
               background:transparent !important; border:none !important;
               color:#dc2626 !important; padding:2px 6px !important;
               font-size:14px !important; line-height:1 !important;
            }
            #satisKalemleri #tahsilat_listesi_duzenleme tr td button[name="tahsilat_adisyondan_sil"]:hover{ color:#b91c1c !important; }

            /* Footer */
            #satisKalemleri .sd-footer{
               background:#fff; border-top:1px solid #e5e7eb;
               padding:12px 22px; display:flex; gap:10px; justify-content:flex-end;
            }
            #satisKalemleri .sd-btn{
               padding:10px 22px; font-size:14px; font-weight:700; border-radius:9px;
               border:none; cursor:pointer; display:inline-flex; align-items:center; gap:7px;
               transition:transform .12s ease, box-shadow .12s ease;
            }
            #satisKalemleri .sd-btn-cancel{
               background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;
            }
            #satisKalemleri .sd-btn-cancel:hover{ background:#e2e8f0; color:#0f172a; }
            #satisKalemleri .sd-btn-save{
               background:linear-gradient(135deg,#10b981 0%,#059669 100%); color:#fff;
               box-shadow:0 6px 14px -4px rgba(16,185,129,.45);
            }
            #satisKalemleri .sd-btn-save:hover{ transform:translateY(-1px); box-shadow:0 10px 18px -4px rgba(16,185,129,.55); color:#fff; }

            /* Responsive */
            @media (max-width: 900px){
               #satisKalemleri .sd-summary{ grid-template-columns:1fr; }
               #satisKalemleri .sd-summary-amounts{ border-right:none; border-bottom:1px solid #f1f5f9; }
               #satisKalemleri .sd-row-head, #satisKalemleri .sd-items .tahsilat_kalemleri_listesi{
                  grid-template-columns:1.4fr 1fr 1fr 1fr !important;
               }
            }
            @media (max-width: 640px){
               #satisKalemleri .sd-header{ padding:12px 14px; gap:10px; }
               #satisKalemleri .sd-body{ padding:14px; }
               #satisKalemleri .sd-row-head{ display:none; }
               #satisKalemleri .sd-items .tahsilat_kalemleri_listesi{
                  grid-template-columns:1fr !important; gap:6px !important;
               }
            }
         </style>

         <script>
            (function(){
               function init(){
                  var modal = document.getElementById('satisKalemleri');
                  if(!modal) return;

                  var goster = document.getElementById('sd-tarih-goster');
                  var input = document.getElementById('satis_tarihi_duzenle');
                  var btnDuzenle = document.getElementById('sd-tarih-duzenle');
                  var btnKaydet = document.getElementById('sd-tarih-kaydet');
                  var btnIptal = document.getElementById('sd-tarih-iptal');
                  if(!goster || !input || !btnDuzenle) return;

                  function bicimle(iso){
                     if(!iso) return '—';
                     var p = iso.split('-');
                     return p.length===3 ? (p[2]+'.'+p[1]+'.'+p[0]) : iso;
                  }
                  function senkronGoster(){
                     goster.textContent = bicimle(input.value);
                  }

                  // Modal her açıldığında, ajax sonrası input.val() yapıldığında göstergeyi güncelle
                  $(modal).on('shown.bs.modal', function(){
                     senkronGoster();
                     // edit modunu kapat
                     goster.style.display='inline';
                     btnDuzenle.style.display='inline-flex';
                     input.style.display='none';
                     btnKaydet.style.display='none';
                     btnIptal.style.display='none';
                  });

                  // Input değer değişimlerini de yansıt (datepicker veya manuel)
                  $(input).on('change', senkronGoster);

                  var orijinalDeger = '';

                  btnDuzenle.addEventListener('click', function(){
                     orijinalDeger = input.value;
                     goster.style.display='none';
                     btnDuzenle.style.display='none';
                     input.style.display='inline-block';
                     btnKaydet.style.display='inline-flex';
                     btnIptal.style.display='inline-flex';
                     try { input.focus(); } catch(e){}
                  });

                  btnIptal.addEventListener('click', function(){
                     input.value = orijinalDeger;
                     senkronGoster();
                     goster.style.display='inline';
                     btnDuzenle.style.display='inline-flex';
                     input.style.display='none';
                     btnKaydet.style.display='none';
                     btnIptal.style.display='none';
                  });

                  btnKaydet.addEventListener('click', function(){
                     if(!input.value){ alert('Geçerli bir tarih giriniz'); return; }
                     var token = $('input[name="_token"]').val();
                     var adisyonId = $('#satis_listesi input[name="adisyon_id"]').val();
                     var sube = $('#satis_listesi input[name="sube"]').val();
                     btnKaydet.disabled = true;
                     btnKaydet.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
                     $.ajax({
                        url:'/isletmeyonetim/satisTarihiGuncelle',
                        method:'POST',
                        data:{ adisyon_id:adisyonId, satis_tarihi:input.value, sube:sube, _token:token }
                     }).always(function(){
                        btnKaydet.disabled = false;
                        btnKaydet.innerHTML = '<i class="fa fa-check"></i>';
                        senkronGoster();
                        orijinalDeger = input.value;
                        goster.style.display='inline';
                        btnDuzenle.style.display='inline-flex';
                        input.style.display='none';
                        btnKaydet.style.display='none';
                        btnIptal.style.display='none';
                     });
                  });
               }
               if(document.readyState==='loading'){
                  document.addEventListener('DOMContentLoaded', init);
               } else {
                  init();
               }
            })();
         </script>
      </div>
