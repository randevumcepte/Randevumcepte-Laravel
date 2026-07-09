<!-- ================= SENARYO SİHİRBAZI ================= -->
<div class="modal fade" id="senaryo_sihirbaz_modal" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content ssb-content">

         <div class="ssb-header">
            <div class="ssb-header-icon"><i class="fa fa-magic"></i></div>
            <div class="ssb-header-text">
               <h5>Senaryo Sihirbazı</h5>
               <p>Müşterinizle konuşulacak metni adım adım hazırlayın</p>
            </div>
            <button type="button" class="ssb-close" data-dismiss="modal" aria-label="Kapat">&times;</button>
         </div>

         <!-- Adım göstergesi -->
         <div class="ssb-steps">
            <div class="ssb-step is-active" data-step="1"><span>1</span> Senaryo</div>
            <div class="ssb-step" data-step="2"><span>2</span> Metinler</div>
            <div class="ssb-step" data-step="3"><span>3</span> Aksiyonlar</div>
            <div class="ssb-step" data-step="4"><span>4</span> Önizleme</div>
         </div>

         <div class="modal-body ssb-body">
            <input type="hidden" id="ssbSenaryoId">
            <input type="hidden" id="ssbSenaryoTipi" value="geri_kazanim">

            <!-- ADIM 1: kanal + hazır senaryo -->
            <div class="ssb-pane" data-pane="1">
               <div class="ssb-label">Bu senaryo hangi kanalda kullanılacak?</div>
               <div class="ssb-kanal">
                  <button type="button" class="ssb-kanal-btn is-active" data-kanal="1"><i class="fa fa-phone-alt"></i> Santral Arama</button>
                  <button type="button" class="ssb-kanal-btn" data-kanal="2"><i class="fa fa-comment-alt"></i> SMS</button>
                  <button type="button" class="ssb-kanal-btn" data-kanal="3"><i class="fa fa-bell"></i> Bildirim</button>
               </div>
               <div class="ssb-hint" id="ssbKanalHint">
                  Santral Arama'da bot açılışı okur, müşteri <b>evet/hayır</b> der; kod SMS'i ve randevu adımları otomatik ilerler.
               </div>

               <div class="ssb-label" style="margin-top:18px;">Hazır bir senaryodan başlayın</div>
               <div class="ssb-presets" id="ssbPresetler"><!-- JS doldurur --></div>
            </div>

            <!-- ADIM 2: metinler -->
            <div class="ssb-pane" data-pane="2" style="display:none;">
               <div class="ssb-chips">
                  <span class="ssb-chips-label">Ekle:</span>
                  <div id="ssbPlaceholderChips"></div>
               </div>
               <div class="ssb-hint" style="margin-bottom:14px;">
                  Bir metin kutusuna tıklayın, sonra yukarıdaki etiketlere basarak müşteri adı, işletme adı gibi bilgileri ekleyin.
               </div>
               <div id="ssbMetinAlanlari"><!-- JS doldurur --></div>
            </div>

            <!-- ADIM 3: aksiyonlar -->
            <div class="ssb-pane" data-pane="3" style="display:none;">
               <div class="ssb-hint" style="margin-bottom:14px;">
                  Görüşme sırasında sistemin <b>otomatik yapacağı</b> işlemler. Kapattığınız adım konuşmada sorulmaz.
               </div>
               <label class="ssb-aksiyon">
                  <input type="checkbox" id="ssbAksIndirim" checked>
                  <span class="ssb-aksiyon-ic">
                     <b>💳 İndirim kodu SMS'i gönder</b>
                     <small>Müşteri "evet" derse indirim kodu mesaj olarak gider.</small>
                  </span>
               </label>
               <label class="ssb-aksiyon">
                  <input type="checkbox" id="ssbAksYolTarifi" checked>
                  <span class="ssb-aksiyon-ic">
                     <b>📍 Yol tarifi gönder</b>
                     <small>İndirim kodu ile birlikte salonun yol tarifi de gönderilir.</small>
                  </span>
               </label>
               <label class="ssb-aksiyon">
                  <input type="checkbox" id="ssbAksRandevu" checked>
                  <span class="ssb-aksiyon-ic">
                     <b>📅 Telefonda randevu oluştur</b>
                     <small>Müşteriye gün/saat sorulur ve randevu anında oluşturulur.</small>
                  </span>
               </label>
            </div>

            <!-- ADIM 4: önizleme + kaydet -->
            <div class="ssb-pane" data-pane="4" style="display:none;">
               <div class="ssb-onizleme-bar">
                  <div class="ssb-label" style="margin:0;">Konuşma önizlemesi</div>
                  <button type="button" class="ssb-sesli" id="ssbSesliOnizle"><i class="fa fa-play"></i> Sesli Önizle</button>
               </div>
               <div class="ssb-diyalog" id="ssbDiyalog"><!-- JS doldurur --></div>

               <div class="ssb-kaydet">
                  <label class="ssb-label" for="ssbSenaryoAd">Senaryo adı</label>
                  <input type="text" class="form-control" id="ssbSenaryoAd" placeholder="örn: 90 Gün Geri Kazanım">
               </div>
            </div>
         </div>

         <div class="ssb-footer">
            <button type="button" class="btn btn-light" id="ssbGeri" style="display:none;"><i class="fa fa-chevron-left"></i> Geri</button>
            <div style="flex:1"></div>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
            <button type="button" class="btn btn-primary" id="ssbIleri">İleri <i class="fa fa-chevron-right"></i></button>
            <button type="button" class="btn btn-success" id="ssbKaydet" style="display:none;"><i class="fa fa-save"></i> Senaryoyu Kaydet</button>
         </div>

      </div>
   </div>
</div>

<style>
#senaryo_sihirbaz_modal .ssb-content{ border:none; border-radius:18px; overflow:hidden; box-shadow:0 28px 70px rgba(15,23,42,.28); }
.ssb-header{ display:flex; align-items:center; gap:14px; padding:18px 22px; background:linear-gradient(135deg,#7c3aed,#a855f7); color:#fff; }
.ssb-header-icon{ width:44px; height:44px; border-radius:12px; background:rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center; font-size:19px; }
.ssb-header-text h5{ margin:0; font-weight:700; font-size:18px; }
.ssb-header-text p{ margin:0; font-size:12.5px; opacity:.85; }
.ssb-close{ margin-left:auto; background:none; border:none; color:#fff; font-size:28px; line-height:1; opacity:.85; cursor:pointer; }

.ssb-steps{ display:flex; gap:6px; padding:14px 22px; background:#faf7ff; border-bottom:1px solid #eee; }
.ssb-step{ flex:1; display:flex; align-items:center; gap:7px; font-size:12.5px; font-weight:600; color:#94a3b8; }
.ssb-step span{ width:24px; height:24px; border-radius:50%; background:#e2e8f0; color:#64748b; display:flex; align-items:center; justify-content:center; font-size:12px; }
.ssb-step.is-active{ color:#6d28d9; }
.ssb-step.is-active span{ background:#7c3aed; color:#fff; }
.ssb-step.is-done span{ background:#22c55e; color:#fff; }

.ssb-body{ padding:22px; max-height:56vh; overflow-y:auto; }
.ssb-label{ font-weight:700; font-size:13px; color:#334155; margin-bottom:9px; }
.ssb-hint{ font-size:12.5px; color:#64748b; background:#f8fafc; border:1px dashed #e2e8f0; border-radius:10px; padding:10px 12px; }

.ssb-kanal{ display:flex; gap:8px; flex-wrap:wrap; }
.ssb-kanal-btn{ border:1.5px solid #e2e8f0; background:#fff; border-radius:11px; padding:9px 15px; font-size:13px; font-weight:600; color:#475569; cursor:pointer; transition:all .12s; }
.ssb-kanal-btn:hover{ border-color:#c4b5fd; }
.ssb-kanal-btn.is-active{ border-color:#7c3aed; background:#f5f3ff; color:#6d28d9; }

.ssb-presets{ display:grid; grid-template-columns:repeat(auto-fill,minmax(190px,1fr)); gap:10px; }
.ssb-preset{ text-align:left; border:1.5px solid #e2e8f0; background:#fff; border-radius:13px; padding:13px; cursor:pointer; transition:all .12s; }
.ssb-preset:hover{ transform:translateY(-2px); border-color:#c4b5fd; box-shadow:0 8px 20px rgba(15,23,42,.07); }
.ssb-preset.is-active{ border-color:#7c3aed; background:#faf5ff; box-shadow:0 8px 22px rgba(124,58,237,.14); }
.ssb-preset-emoji{ font-size:21px; }
.ssb-preset-ad{ display:block; font-weight:700; font-size:13.5px; color:#1e293b; margin-top:5px; }
.ssb-preset-ac{ display:block; font-size:11.5px; color:#64748b; line-height:1.35; margin-top:2px; }

.ssb-chips{ display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:10px; }
.ssb-chips-label{ font-size:12.5px; font-weight:700; color:#475569; }
.ssb-chip{ border:1px solid #ddd6fe; background:#f5f3ff; color:#6d28d9; border-radius:999px; padding:4px 11px; font-size:12px; font-weight:600; cursor:pointer; margin:2px; }
.ssb-chip:hover{ background:#7c3aed; color:#fff; border-color:#7c3aed; }

.ssb-alan{ margin-bottom:14px; }
.ssb-alan-baslik{ font-size:12.5px; font-weight:700; color:#334155; margin-bottom:5px; display:flex; align-items:center; gap:6px; }
.ssb-alan-baslik small{ font-weight:500; color:#94a3b8; }
.ssb-alan textarea{ width:100%; border:1.5px solid #e2e8f0; border-radius:11px; padding:10px 12px; font-size:13.5px; line-height:1.5; resize:vertical; min-height:64px; outline:none; }
.ssb-alan textarea:focus{ border-color:#a855f7; box-shadow:0 0 0 3px rgba(168,85,247,.13); }

.ssb-aksiyon{ display:flex; align-items:flex-start; gap:11px; border:1.5px solid #e2e8f0; border-radius:12px; padding:13px; margin-bottom:10px; cursor:pointer; }
.ssb-aksiyon:hover{ border-color:#c4b5fd; }
.ssb-aksiyon input{ margin-top:3px; }
.ssb-aksiyon-ic b{ display:block; font-size:13.5px; color:#1e293b; }
.ssb-aksiyon-ic small{ color:#64748b; font-size:12px; }

.ssb-onizleme-bar{ display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.ssb-sesli{ border:none; background:#ecfdf5; color:#0f9d58; border-radius:9px; padding:7px 13px; font-size:12.5px; font-weight:600; cursor:pointer; }
.ssb-sesli:hover{ background:#0f9d58; color:#fff; }
.ssb-diyalog{ background:#f8fafc; border-radius:14px; padding:14px; min-height:120px; }
.ssb-balon{ max-width:80%; padding:9px 13px; border-radius:14px; font-size:13px; line-height:1.45; margin-bottom:9px; }
.ssb-balon--bot{ background:#fff; border:1px solid #e2e8f0; border-bottom-left-radius:4px; color:#1e293b; }
.ssb-balon--musteri{ background:#7c3aed; color:#fff; margin-left:auto; border-bottom-right-radius:4px; }
.ssb-kaydet{ margin-top:16px; }

.ssb-footer{ display:flex; align-items:center; gap:9px; padding:14px 22px; border-top:1px solid #eee; background:#fff; }
@media (max-width:575px){
   .ssb-steps .ssb-step{ font-size:0; } .ssb-steps .ssb-step span{ font-size:12px; }
   .ssb-presets{ grid-template-columns:1fr; }
}
</style>
