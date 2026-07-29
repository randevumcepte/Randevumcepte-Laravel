@extends("layout.layout_isletmeadmin")
@section("content")

<style>
/* ===== Çağrı Merkezi — Agent Cockpit (modern indigo + nötr) ===== */
.ag-wrap{ --ana:#6d28d9; --ana2:#7c3aed; --ana3:#8b5cf6; --yesil1:#16a34a; --yesil2:#22c55e;
          --ink:#1f2433; --muted:#6b7280; --cizgi:#e9eaf1; }

/* Başlık şeridi — premium koyu indigo→violet, tek renkli alan */
.ag-hero{
   background:linear-gradient(125deg,#1e1b4b 0%,#4c1d95 48%,#6d28d9 100%);
   border-radius:18px; padding:20px 24px; color:#fff; margin-bottom:18px;
   box-shadow:0 14px 34px -14px rgba(76,29,149,.55); position:relative; overflow:hidden;
}
.ag-hero:after{ content:""; position:absolute; right:-50px; top:-50px; width:200px; height:200px; border-radius:50%; background:radial-gradient(circle,rgba(255,255,255,.16),transparent 70%); }
.ag-hero h4{ margin:0 0 4px; font-weight:700; font-size:21px; color:#fff; }
.ag-hero p{ margin:0; opacity:.90; font-size:13px; }
.ag-hero-stats{ display:flex; gap:10px; flex-wrap:wrap; margin-top:14px; position:relative; z-index:2; }
.ag-hstat{ background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.10); border-radius:12px; padding:8px 16px; min-width:104px; }
.ag-hstat .n{ font-size:22px; font-weight:800; line-height:1; }
.ag-hstat .t{ font-size:11.5px; opacity:.9; margin-top:3px; }

/* İki kolon düzen */
.ag-grid{ display:grid; grid-template-columns:380px 1fr; gap:18px; align-items:start; }
@media (max-width:991px){ .ag-grid{ grid-template-columns:1fr; } }

/* Geniş ekran: bu bölüm ekran yüksekliğini doldursun; SOL kuyruk kendi içinde
   kaysın, SAĞ arama paneli sabit kalsın (sayfa kaymaz). */
@media (min-width:992px){
   .ag-wrap{ display:flex; flex-direction:column; height:calc(100vh - 96px); }
   .ag-hero{ flex:0 0 auto; }
   .ag-grid{ flex:1 1 auto; min-height:0; }
   .ag-grid > .ag-panel{ height:100%; max-height:100%; min-height:0; display:flex; flex-direction:column; }
   #ag_kuyruk{ flex:1 1 auto; min-height:0; max-height:none; }
   #ag_detay_panel{ overflow-y:auto; }
}

.ag-panel{ background:#fff; border-radius:18px; border:1px solid #eef0f5; box-shadow:0 6px 20px -12px rgba(30,30,60,.16); overflow:hidden; }
.ag-panel-head{ padding:14px 16px; border-bottom:1px solid #eef0f5; display:flex; align-items:center; gap:10px; }
.ag-panel-head h5{ margin:0; font-weight:700; font-size:15px; color:#1f2433; }
.ag-panel-head .say{ margin-left:auto; background:#f1edfb; color:#6d28d9; font-weight:700; border-radius:20px; padding:3px 11px; font-size:12px; }

/* Liste seçici çipleri */
.ag-listeler{ display:flex; gap:8px; flex-wrap:wrap; padding:12px 16px 4px; }
.ag-liste-chip{ border:1px solid #e7e9f0; background:#fff; color:#5b6172; border-radius:12px; padding:8px 13px; cursor:pointer; font-size:12.5px; font-weight:600; transition:all .12s ease; max-width:100%; }
.ag-liste-chip:hover{ border-color:#8b5cf6; color:#6d28d9; }
.ag-liste-chip.aktif{ background:linear-gradient(120deg,#6d28d9,#7c3aed); border-color:transparent; color:#fff; box-shadow:0 6px 16px -8px rgba(109,40,217,.6); }
.ag-liste-chip .alt{ display:block; font-size:10.5px; opacity:.85; font-weight:500; margin-top:2px; }

/* Arama + filtre */
.ag-ara-kutu{ position:relative; padding:10px 16px; }
.ag-ara-kutu input{ width:100%; border:1px solid #e7e9f0; border-radius:12px; padding:9px 12px 9px 34px; font-size:13px; outline:none; transition:border-color .12s ease; }
.ag-ara-kutu input:focus{ border-color:#8b5cf6; }
.ag-ara-kutu .fa{ position:absolute; left:28px; top:50%; transform:translateY(-50%); color:#aab0c0; }
.ag-fil{ display:flex; gap:6px; flex-wrap:wrap; padding:0 16px 10px; }
.ag-fil-chip{ border:1px solid #eaecf2; background:#f7f8fc; color:#7b8194; border-radius:20px; padding:4px 11px; cursor:pointer; font-size:11.5px; font-weight:600; }
.ag-fil-chip.aktif{ background:#6d28d9; border-color:#6d28d9; color:#fff; }

/* Müşteri kuyruğu */
.ag-kuyruk{ max-height:62vh; overflow-y:auto; padding:4px 10px 12px; }
.ag-musteri{ display:flex; align-items:center; gap:11px; padding:11px 12px; border-radius:13px; cursor:pointer; border:1px solid transparent; transition:background .12s ease, border-color .12s ease; }
.ag-musteri:hover{ background:#f7f8fc; }
.ag-musteri.secili{ background:#f4f1fd; border-color:#ddd4f3; }
.ag-mus-av{ width:40px; height:40px; flex:0 0 40px; border-radius:50%; background:#ede9fe; color:#6d28d9; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:16px; }
.ag-musteri.secili .ag-mus-av{ background:linear-gradient(135deg,#6d28d9,#8b5cf6); color:#fff; }
.ag-mus-orta{ flex:1; min-width:0; }
.ag-mus-ad{ font-weight:700; color:#1f2433; font-size:13.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ag-mus-tel{ font-size:11.5px; color:#9298a8; margin-top:1px; }
.ag-mus-dur{ font-size:10.5px; font-weight:700; padding:3px 9px; border-radius:20px; white-space:nowrap; }

/* Durum renkleri */
.d-yesil{ background:#e4f7ec; color:#1b9e4b; } .d-kirmizi{ background:#fdecec; color:#d33; }
.d-mavi{ background:#e7f1fd; color:#1565c0; } .d-turuncu{ background:#fff2e3; color:#e07a1a; }
.d-gri{ background:#f0eef5; color:#8a85a0; }
.bl-yesil{ border-left-color:#1b9e4b!important; } .bl-kirmizi{ border-left-color:#d33!important; }
.bl-mavi{ border-left-color:#1565c0!important; } .bl-turuncu{ border-left-color:#e07a1a!important; }
.bl-gri{ border-left-color:#cfc7df!important; }

/* Detay (sağ) panel */
.ag-detay-bos{ text-align:center; padding:70px 24px; color:#9a95ab; }
.ag-detay-bos .ic{ width:90px; height:90px; border-radius:50%; margin:0 auto 18px; background:linear-gradient(135deg,#f1edfb,#e9e4fb); display:flex; align-items:center; justify-content:center; font-size:40px; color:#a78bda; }
.ag-detay-bos .t1{ font-weight:700; color:#4b5163; font-size:16px; }
.ag-detay-bos .t2{ font-size:13px; margin-top:5px; }

/* Müşteri başlığı — temiz beyaz, alt çizgili (mor blok kaldırıldı) */
.ag-dhead{ background:#fff; color:#1f2433; padding:20px 22px; display:flex; align-items:center; gap:16px; flex-wrap:wrap; position:sticky; top:0; z-index:3; border-bottom:1px solid #eef0f5; }
.ag-dhead-av{ width:56px; height:56px; flex:0 0 56px; border-radius:50%; background:linear-gradient(135deg,#6d28d9,#8b5cf6); color:#fff; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:700; box-shadow:0 8px 18px -8px rgba(109,40,217,.55); }
.ag-dhead-ad{ font-weight:700; font-size:20px; line-height:1.1; color:#1f2433; }
.ag-dhead-sub{ font-size:13px; color:#6b7280; margin-top:5px; }
.ag-dhead-sub .durnok{ display:inline-block; padding:2px 10px; border-radius:20px; background:#f1edfb; color:#6d28d9; font-weight:700; font-size:11.5px; margin-left:4px; }
/* ARA = yeşil (çağrı eylemi) */
.ag-ara-btn{ margin-left:auto; background:linear-gradient(135deg,#16a34a,#22c55e); color:#fff; border:none; border-radius:14px; padding:13px 26px; font-weight:800; font-size:15px; cursor:pointer; box-shadow:0 10px 22px -8px rgba(22,163,74,.55); display:flex; align-items:center; gap:9px; transition:transform .12s ease, box-shadow .12s ease; }
.ag-ara-btn:hover{ transform:translateY(-2px); box-shadow:0 14px 26px -8px rgba(22,163,74,.6); }
.ag-ara-btn:disabled{ opacity:.55; cursor:not-allowed; transform:none; }
/* Sonuç kaydedilene kadar pasif görünüm (yine tıklanabilir; tıklayınca uyarı çıkar) */
.ag-ara-btn.pasif{ background:#cbd0db; color:#fff; box-shadow:none; cursor:not-allowed; }
.ag-ara-btn.pasif:hover{ transform:none; box-shadow:none; }
.ag-ara-btn .fa{ font-size:17px; }

.ag-dbody{ padding:20px 22px; background:#fbfbfe; }
.ag-kart{ background:#fff; border:1px solid #eef0f5; border-radius:14px; padding:15px 16px; margin-bottom:16px; box-shadow:0 2px 10px -8px rgba(30,30,60,.15); }
.ag-kart-bas{ font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.4px; color:#8a90a2; margin-bottom:11px; display:flex; align-items:center; gap:7px; }
.ag-kart-bas .fa{ color:#8b5cf6; }
.ag-gecmis-yenile{ margin-left:auto; border:1px solid #e3e6ee; background:#fff; color:#6d28d9; border-radius:8px; padding:4px 10px; font-size:11px; font-weight:700; cursor:pointer; text-transform:none; letter-spacing:0; }
.ag-gecmis-yenile:hover{ border-color:#8b5cf6; background:#f7f5fe; }
.ag-gecmis-yenile .fa{ color:#6d28d9; margin-right:3px; }

/* Sonuç butonları */
.ag-sonuc-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:8px; }
@media (max-width:520px){ .ag-sonuc-grid{ grid-template-columns:repeat(2,1fr); } }
.ag-sonuc{ border:1.5px solid #e9ebf2; background:#fff; border-radius:12px; padding:11px 6px; text-align:center; cursor:pointer; font-weight:700; font-size:12.5px; transition:all .12s ease; }
.ag-sonuc .fa{ display:block; font-size:17px; margin-bottom:5px; }
.ag-sonuc.aktif{ box-shadow:0 6px 16px -8px rgba(30,30,60,.25); }

/* Görüşüldü = yeşil */
.ag-sonuc.sec-yesil{ color:#15803d; border-color:#cdeeda; background:#f5fbf7; }
.ag-sonuc.sec-yesil .fa{ color:#16a34a; }
.ag-sonuc.sec-yesil:hover{ border-color:#16a34a; background:#ecf8f1; }
.ag-sonuc.sec-yesil.aktif{ background:#dff5e7; border-color:#16a34a; color:#15803d; }

/* Cevapsız = kırmızı */
.ag-sonuc.sec-kirmizi{ color:#c92a2a; border-color:#f6d4d4; background:#fdf6f6; }
.ag-sonuc.sec-kirmizi .fa{ color:#dc2626; }
.ag-sonuc.sec-kirmizi:hover{ border-color:#dc2626; background:#fcecec; }
.ag-sonuc.sec-kirmizi.aktif{ background:#fce3e3; border-color:#dc2626; color:#c92a2a; }

/* Meşgul = pembe/macenta */
.ag-sonuc.sec-koyukirmizi{ color:#be185d; border-color:#f6d3e4; background:#fdf5f9; }
.ag-sonuc.sec-koyukirmizi .fa{ color:#db2777; }
.ag-sonuc.sec-koyukirmizi:hover{ border-color:#db2777; background:#fbeaf3; }
.ag-sonuc.sec-koyukirmizi.aktif{ background:#fbe0ee; border-color:#db2777; color:#be185d; }

/* Ulaşılamadı = turuncu */
.ag-sonuc.sec-turuncu{ color:#c2570c; border-color:#f8ddc6; background:#fef8f2; }
.ag-sonuc.sec-turuncu .fa{ color:#ea580c; }
.ag-sonuc.sec-turuncu:hover{ border-color:#ea580c; background:#fdefe2; }
.ag-sonuc.sec-turuncu.aktif{ background:#fde7d3; border-color:#ea580c; color:#c2570c; }

/* Görüşme sonucu kartı — ARA yapılana kadar kilitli */
.ag-kart.kilitli .ag-sonuc-grid,
.ag-kart.kilitli .ag-donusum-grid,
.ag-kart.kilitli .ag-donusum-lbl,
.ag-kart.kilitli .ag-kat-secim,
.ag-kart.kilitli .ag-not-alani,
.ag-kart.kilitli .ag-satis-alan,
.ag-kart.kilitli .ag-sonra,
.ag-kart.kilitli .ag-sonra-alan,
.ag-kart.kilitli .ag-kaydet{ opacity:.45; pointer-events:none; filter:grayscale(.3); }
.ag-kilit-not{ display:none; background:#fff7ed; border:1px solid #fed7aa; color:#c2570c; border-radius:10px; padding:9px 12px; font-size:12.5px; font-weight:600; margin-bottom:10px; }
.ag-kart.kilitli .ag-kilit-not{ display:block; }
.ag-kilit-not .fa{ margin-right:5px; }

/* Dönüşüm (hedef) butonları: Ön Görüşme + Telefonda Satış */
.ag-donusum-lbl{ font-size:11.5px; font-weight:800; color:#8a90a2; text-transform:uppercase; letter-spacing:.4px; margin:14px 0 7px; }
.ag-donusum-grid{ display:grid; grid-template-columns:1fr 1fr; gap:8px; }
@media (max-width:520px){ .ag-donusum-grid{ grid-template-columns:1fr; } }
.ag-donusum{ font-size:13px; }
/* Ön Görüşme = mavi */
.ag-sonuc.sec-mavi{ color:#1565c0; border-color:#cfe0f7; background:#f4f8fe; }
.ag-sonuc.sec-mavi .fa{ color:#1565c0; }
.ag-sonuc.sec-mavi:hover{ border-color:#1565c0; background:#eaf2fd; }
.ag-sonuc.sec-mavi.aktif{ background:#e0edfb; border-color:#1565c0; color:#0d4a93; }
/* Telefonda Satış = altın/yeşil (başarı) */
.ag-sonuc.sec-altin{ color:#15803d; border-color:#c9ead4; background:#f3fbf6; }
.ag-sonuc.sec-altin .fa{ color:#16a34a; }
.ag-sonuc.sec-altin:hover{ border-color:#16a34a; background:#e9f8ef; }
.ag-sonuc.sec-altin.aktif{ background:#daf5e4; border-color:#16a34a; color:#15803d; }
/* Satış tutarı alanı */
.ag-satis-alan{ display:none; align-items:center; gap:10px; margin-top:12px; background:#f3fbf6; border:1px solid #c9ead4; border-radius:12px; padding:10px 12px; }
.ag-satis-lbl{ font-size:12.5px; font-weight:700; color:#15803d; white-space:nowrap; }
.ag-satis-alan input{ flex:1; border:1px solid #c9ead4; border-radius:10px; padding:9px 11px; font-size:14px; outline:none; font-weight:700; }
.ag-satis-alan input:focus{ border-color:#16a34a; }
.ag-kasa-btn{ border:none; background:linear-gradient(135deg,#16a34a,#22c55e); color:#fff; border-radius:10px; padding:9px 14px; font-size:12.5px; font-weight:800; cursor:pointer; white-space:nowrap; display:inline-flex; align-items:center; gap:6px; }
.ag-kasa-btn:hover{ opacity:.93; }

.ag-not-alani{ width:100%; border:1px solid #e7e9f0; border-radius:12px; padding:11px 13px; font-size:13.5px; outline:none; resize:vertical; min-height:80px; margin-top:12px; transition:border-color .12s ease; }
.ag-not-alani:focus{ border-color:#8b5cf6; }
select.ag-not-alani{ min-height:auto; padding:10px 12px; background:#fff; }
.ag-script-icerik{ background:#f7f8fc; border:1px solid #eef0f5; border-radius:10px; padding:12px 14px; margin-top:10px; font-size:13.5px; color:#4b5163; white-space:pre-wrap; line-height:1.55; }
.ag-kat-secim{ display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:12px; }
@media (max-width:520px){ .ag-kat-secim{ grid-template-columns:1fr; } }
.ag-sonra{ display:flex; align-items:center; gap:9px; margin-top:12px; font-size:13px; color:#574f6b; cursor:pointer; }
.ag-sonra input{ width:17px; height:17px; }
.ag-sonra-alan{ display:none; gap:10px; margin-top:10px; }
.ag-sonra-alan input{ flex:1; border:1px solid #e7e9f0; border-radius:10px; padding:9px 11px; font-size:13px; outline:none; }
.ag-kaydet{ width:100%; margin-top:14px; background:linear-gradient(120deg,#6d28d9,#7c3aed); color:#fff; border:none; border-radius:12px; padding:13px; font-weight:800; font-size:14.5px; cursor:pointer; transition:opacity .12s ease, transform .12s ease; box-shadow:0 8px 20px -10px rgba(109,40,217,.6); }
.ag-kaydet:hover{ opacity:.95; transform:translateY(-1px); }
.ag-kaydet:disabled{ opacity:.55; cursor:not-allowed; }
.ag-anket-btn{ width:100%; margin-top:9px; background:linear-gradient(120deg,#16a34a,#25D366); color:#fff; border:none; border-radius:12px; padding:12px; font-weight:800; font-size:13.5px; cursor:pointer; transition:opacity .12s ease, transform .12s ease; box-shadow:0 8px 20px -10px rgba(22,163,74,.6); display:flex; align-items:center; justify-content:center; gap:7px; }
.ag-anket-btn:hover{ opacity:.95; transform:translateY(-1px); }
.ag-anket-btn:disabled{ opacity:.55; cursor:not-allowed; }

/* Çağrı geçmişi */
.ag-gecmis-item{ border:1px solid #efeaf6; border-left:4px solid #cfc7df; border-radius:12px; padding:11px 13px; margin-bottom:10px; background:#fff; }
.ag-gecmis-top{ display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.ag-gecmis-tarih{ font-weight:700; color:#6d28d9; font-size:12.5px; }
.ag-randevu-zaman{ background:#e7f1fd; color:#1565c0; font-weight:700; font-size:11.5px; border-radius:20px; padding:3px 10px; margin-left:6px; white-space:nowrap; }
.ag-satis-rozet{ background:#daf5e4; color:#15803d; font-weight:800; font-size:11.5px; border-radius:20px; padding:3px 10px; margin-left:6px; white-space:nowrap; }
.ag-gecmis-item audio{ width:100%; height:36px; margin-top:9px; border-radius:8px; }
.ag-gecmis-bos{ text-align:center; color:#9a95ab; padding:22px; font-size:13px; }
.ag-gecmis-bos .fa{ font-size:28px; color:#cfc7df; display:block; margin-bottom:8px; }

.ag-spin{ text-align:center; padding:30px; color:#9a95ab; }
</style>

<div class="ag-wrap">

   {{-- Başlık şeridi + kuyruk özetleri --}}
   <div class="ag-hero">
      <h4><i class="fa fa-headphones"></i> Çağrı Merkezi — Çalışma Ekranı</h4>
      <p>Size atanan listelerden müşterileri arayın, görüşme sonucunu ve notunu kaydedin. Numaralar KVKK gereği gizlidir — <b>Ara</b> butonuyla santral sizi bağlar.</p>
      <div class="ag-hero-stats">
         <div class="ag-hstat"><div class="n" id="ag_h_liste">0</div><div class="t">Atanan Liste</div></div>
         <div class="ag-hstat"><div class="n" id="ag_h_toplam">0</div><div class="t">Toplam Müşteri</div></div>
         <div class="ag-hstat"><div class="n" id="ag_h_arandi">0</div><div class="t">Arandı</div></div>
         <div class="ag-hstat"><div class="n" id="ag_h_kalan">0</div><div class="t">Sırada Bekleyen</div></div>
      </div>
   </div>

   <div class="ag-grid">

      {{-- SOL: kuyruk --}}
      <div class="ag-panel">
         <div class="ag-panel-head">
            <h5><i class="fa fa-list-ul" style="color:#8b5cf6;"></i> Müşteri Kuyruğu</h5>
            <span class="say" id="ag_kuyruk_say">0</span>
         </div>
         <div class="ag-listeler" id="ag_listeler"><span class="text-muted" style="font-size:12px;padding:4px;">Listeler yükleniyor...</span></div>
         <div class="ag-ara-kutu"><i class="fa fa-search"></i><input type="text" id="ag_ara" placeholder="Müşteri ara..."></div>
         <div class="ag-fil" id="ag_filtreler"></div>
         <div class="ag-kuyruk" id="ag_kuyruk">
            <div class="ag-gecmis-bos"><i class="fa fa-hand-o-up"></i>Yukarıdan bir liste seçin.</div>
         </div>
      </div>

      {{-- SAĞ: detay cockpit --}}
      <div class="ag-panel" id="ag_detay_panel">
         <div class="ag-detay-bos" id="ag_detay_bos">
            <div class="ic"><i class="fa fa-user-circle-o"></i></div>
            <div class="t1">Bir müşteri seçin</div>
            <div class="t2">Soldaki kuyruktan bir müşteriye tıklayın; bilgileri, arama butonu ve görüşme geçmişi burada açılır.</div>
         </div>
         <div id="ag_detay_icerik" style="display:none;"></div>
      </div>

   </div>
</div>

{{-- KVKK: ses-kaydi eslestirmesi icin gizli arama_liste_detaylari linki (webphone callHangUp bunu okur) --}}
<a id="aktif_arama_liste_link" name="arama_liste_detaylari" data-value="" href="#" style="display:none;"></a>

<input type="hidden" name="sube" value="{{ $isletme->id }}">

{{-- Memnuniyet anketi gönderim yetkisi (görüşme sonucu penceresindeki buton bu bayrağa bakar) --}}
<script>window.agAnketYetki = @yetki('pazarlama.anket_yonet') true @else false @endyetki;</script>

{{-- Telefonda Satış: sade Hızlı Satış popup (gerçek satış kaydı oluşturur -> kasa/satış takibi) --}}
<div id="ag_satis_modal" class="modal fade" role="dialog">
   <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:520px;">
      <div class="modal-content" style="border:none;border-radius:16px;overflow:hidden;">
         <div class="modal-header" style="background:linear-gradient(120deg,#16a34a,#22c55e);color:#fff;border:none;">
            <h5 class="modal-title" style="font-weight:700;"><i class="fa fa-money"></i> Telefonda Satış</h5>
            <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.9;"><span>&times;</span></button>
         </div>
         <div class="modal-body" style="padding:18px 20px;">
            <input type="hidden" id="hs_musteri_id">
            <div style="margin-bottom:12px;font-size:14px;"><i class="fa fa-user-circle-o" style="color:#16a34a;"></i> <b id="hs_musteri_ad" style="color:#15803d;"></b></div>

            <label style="font-size:12.5px;font-weight:700;color:#4b5163;display:block;margin-bottom:4px;">Ne satıldı?</label>
            <select id="hs_tip" class="form-control" style="margin-bottom:8px;">
               <option value="paket">Paket</option>
               <option value="hizmet">Hizmet</option>
               <option value="urun">Ürün</option>
            </select>
            <select id="hs_item_paket" class="form-control hs-item" style="margin-bottom:10px;">
               <option value="">Paket seçin...</option>
               @foreach(($cm_paketler ?? []) as $p)<option value="{{$p->id}}" data-fiyat="{{$p->fiyat}}">{{$p->ad}}</option>@endforeach
            </select>
            <select id="hs_item_hizmet" class="form-control hs-item" style="display:none;margin-bottom:10px;">
               <option value="">Hizmet seçin...</option>
               @foreach(($cm_hizmetler ?? []) as $h)<option value="{{$h->id}}" data-fiyat="{{$h->fiyat}}">{{$h->ad}}</option>@endforeach
            </select>
            <select id="hs_item_urun" class="form-control hs-item" style="display:none;margin-bottom:10px;">
               <option value="">Ürün seçin...</option>
               @foreach(($cm_urunler ?? []) as $u)<option value="{{$u->id}}" data-fiyat="{{$u->fiyat}}">{{$u->ad}}</option>@endforeach
            </select>

            <div class="row">
               <div class="col-7">
                  <label style="font-size:12.5px;font-weight:700;color:#4b5163;display:block;margin-bottom:4px;">Satış fiyatı (₺)</label>
                  <input type="number" id="hs_fiyat" class="form-control" min="0" step="0.01" placeholder="örn: 1500">
               </div>
               <div class="col-5">
                  <label style="font-size:12.5px;font-weight:700;color:#4b5163;display:block;margin-bottom:4px;">Ödeme</label>
                  <select id="hs_odeme" class="form-control">
                     @foreach(($cm_odeme_yontemleri ?? []) as $oy)<option value="{{$oy->id}}">{{$oy->odeme_yontemi}}</option>@endforeach
                  </select>
               </div>
            </div>
            <div id="hs_adet_kutu" style="display:none;margin-top:10px;">
               <label style="font-size:12.5px;font-weight:700;color:#4b5163;display:block;margin-bottom:4px;">Adet</label>
               <input type="number" id="hs_adet" class="form-control" value="1" min="1" step="1">
            </div>
         </div>
         <div class="modal-footer" style="border:none;padding:0 20px 18px;">
            <button type="button" class="ag-kasa-btn" id="hs_kaydet" style="width:100%;justify-content:center;padding:12px;"><i class="fa fa-check"></i> Satışı Oluştur (Kasaya İşle)</button>
         </div>
      </div>
   </div>
</div>

<script>
function agEsc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g,function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

// Durum kodu -> renk/etiket. 0 Ulaşılamadı,1 Arandı,2 Cevapsız,3 Tekrar Aranacak,4 Görüşüldü,5 Meşgul,6 Ön Görüşme,7 Satış
function durStil(kod){
   switch (parseInt(kod,10)){
      case 4: return { c:'yesil',   et:'Görüşüldü' };
      case 1: return { c:'yesil',   et:'Arandı' };
      case 2: return { c:'kirmizi', et:'Cevapsız' };
      case 5: return { c:'kirmizi', et:'Meşgul' };
      case 0: return { c:'turuncu', et:'Ulaşılamadı' };
      case 3: return { c:'mavi',    et:'Tekrar Aranacak' };
      case 6: return { c:'mavi',    et:'Ön Görüşme' };
      case 7: return { c:'yesil',   et:'Satış' };
      default:return { c:'gri',     et:'Bekliyor' };
   }
}

// Kuyruk filtre grupları (durum_kod'a göre)
var AG_FILTRELER = [
   { key:'hepsi',     et:'Tümü',      test:function(){ return true; } },
   { key:'bekleyen',  et:'Bekleyen',  test:function(k){ return k===null || isNaN(k); } },
   { key:'ongorusme', et:'Ön Görüşme', test:function(k){ return k===6; } },
   { key:'satis',     et:'Satış',     test:function(k){ return k===7; } },
   { key:'arandi',    et:'Görüşülen', test:function(k){ return k===1 || k===4; } },
   { key:'olumsuz',   et:'Cevapsız',  test:function(k){ return k===0 || k===2 || k===5; } },
   { key:'randevu',   et:'Tekrar Aranacak', test:function(k){ return k===3; } }
];

var agListeler = [];
var agAktifListe = null;
var agMusteriler = [];
var agSecili = null;
var agFiltre = 'hepsi';
var agSecilenSonuc = null; // form: seçili sonuç kodu
var agScriptler = [];      // yöneticinin tanımladığı görüşme scriptleri
var agKategoriler = [];    // sonuç kategori ağacı (ana + alt)

function agScriptleriYukle(){
   $.get('/isletmeyonetim/cagri-scriptleri-getir', { sube:$('input[name="sube"]').val() }, function(res){
      agScriptler = (res && res.scriptler) ? res.scriptler : [];
   });
}
function agKategorileriYukle(){
   $.get('/isletmeyonetim/cagri-kategori-liste', { sube:$('input[name="sube"]').val() }, function(res){
      agKategoriler = (res && res.kategoriler) ? res.kategoriler : [];
   });
}

// ---- Listeler ----
function agListeleriYukle(){
   $.ajax({
      url:'/isletmeyonetim/arama-kartlarim', method:'GET',
      data:{ sube:$('input[name="sube"]').val() },
      success:function(res){
         agListeler = (res && res.kartlar) ? res.kartlar : [];
         var $l = $('#ag_listeler');
         if (!agListeler.length){
            $l.html('<span class="text-muted" style="font-size:12px;padding:4px;">Size atanmış aktif liste yok.</span>');
            return;
         }
         // Hero özet
         var top=0, ar=0, ka=0;
         agListeler.forEach(function(k){ top+=+k.toplam||0; ar+=+k.arandi||0; ka+=+k.kalan||0; });
         $('#ag_h_liste').text(agListeler.length);
         $('#ag_h_toplam').text(top); $('#ag_h_arandi').text(ar); $('#ag_h_kalan').text(ka);

         var html='';
         agListeler.forEach(function(k){
            var tar = k.aranacak_tarih ? k.aranacak_tarih : 'Tarih yok';
            html += '<div class="ag-liste-chip" data-id="'+agEsc(k.id)+'" data-baslik="'+agEsc(k.baslik)+'" data-tarih="'+agEsc(tar)+'">'+
                    agEsc(k.baslik)+'<span class="alt">'+agEsc(k.kalan)+' sırada • '+agEsc(tar)+'</span></div>';
         });
         $l.html(html);
         // İlk listeyi otomatik seç
         agListeSec(agListeler[0].id, agListeler[0].baslik);
      },
      error:function(){ $('#ag_listeler').html('<span style="color:#c62828;font-size:12px;">Listeler yüklenemedi.</span>'); }
   });
}

function agListeSec(id, baslik){
   agAktifListe = id;
   $('#aktif_arama_liste_link').attr('data-value', id);
   $('.ag-liste-chip').removeClass('aktif');
   $('.ag-liste-chip[data-id="'+id+'"]').addClass('aktif');
   agMusterileriYukle();
}

$(document).on('click', '.ag-liste-chip', function(){
   agListeSec($(this).data('id'), $(this).data('baslik'));
});

var agHedefSecim = null; // liste yuklenince secilecek belirli musteri (randevu popup'tan)

// ---- Müşteriler (kuyruk) ----
function agMusterileriYukle(){
   agSecili = null; // yeni liste -> önceki seçim sıfırlanır
   $('#ag_kuyruk').html('<div class="ag-spin"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>');
   $.ajax({
      url:'/isletmeyonetim/arama_liste_detay_getir', method:'POST',
      data:{ arama_detay_id:agAktifListe, page:1, perPage:500, _token:$('input[name="_token"]').val() },
      success:function(res){
         agMusteriler = (res && res.data) ? res.data : [];
         agFiltreCiz();
         agKuyrukCiz();
         // Hedef müşteri varsa onu aç (randevu popup), yoksa ilk müşteriyi aç
         if (agHedefSecim){ agHedefSeciminiUygula(); }
         else { agIlkMusteriyiSec(); }
      },
      error:function(){ $('#ag_kuyruk').html('<div class="ag-gecmis-bos" style="color:#c62828;">Müşteriler yüklenemedi.</div>'); }
   });
}

// Kuyruktaki ilk (görünen) müşteriyi otomatik seçer
function agIlkMusteriyiSec(){
   var ilk = $('#ag_kuyruk .ag-musteri').first();
   if (ilk.length){ ilk.trigger('click'); }
}

// Belirli bir müşteriyi kuyrukta seçer (randevu popup'tan gelince)
function agHedefSeciminiUygula(){
   if (!agHedefSecim) return;
   var hedef = agHedefSecim; agHedefSecim = null;
   var $el = $('#ag_kuyruk .ag-musteri[data-id="'+hedef+'"]');
   if ($el.length){ try { $el[0].scrollIntoView({block:'center'}); } catch(e){} $el.trigger('click'); }
   else { agIlkMusteriyiSec(); } // hedef bu filtrede yoksa ilkini aç
}

// Randevu popup'tan: müşteriyi (gerekirse listesini açarak) arama ekranında seç
function agMusteriyeGit(aramaId, aranacakId){
   agHedefSecim = String(aranacakId);
   agFiltre = 'hepsi'; // hedef görünür olsun
   if (String(agAktifListe) === String(aramaId)){
      agFiltreCiz(); agKuyrukCiz(); agHedefSeciminiUygula();
   } else {
      var $chip = $('.ag-liste-chip[data-id="'+aramaId+'"]');
      agListeSec(aramaId, $chip.length ? $chip.data('baslik') : ''); // yüklenince hedef seçilir
   }
}

// Çağrı geçmişini elle yenile
$(document).on('click', '#ag_gecmis_yenile', function(){
   if (agSecili){
      $('#ag_gecmis').html('<div class="ag-spin"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>');
      agGecmisYukle(agSecili.aranacak_musteri_id);
   }
});

function agFiltreCiz(){
   var html='';
   AG_FILTRELER.forEach(function(f){
      var sayi = f.key==='hepsi' ? agMusteriler.length
               : agMusteriler.filter(function(m){ return f.test(m.durum_kod===undefined||m.durum_kod===null?null:parseInt(m.durum_kod,10)); }).length;
      if (f.key!=='hepsi' && sayi===0) return;
      html += '<span class="ag-fil-chip '+(agFiltre===f.key?'aktif':'')+'" data-fil="'+f.key+'">'+f.et+' ('+sayi+')</span>';
   });
   $('#ag_filtreler').html(html);
}

function agKuyrukCiz(){
   var ara = ($('#ag_ara').val()||'').toLocaleLowerCase('tr');
   var grup = AG_FILTRELER.find(function(f){ return f.key===agFiltre; }) || AG_FILTRELER[0];
   var veri = agMusteriler.filter(function(m){
      var k = (m.durum_kod===undefined||m.durum_kod===null)?null:parseInt(m.durum_kod,10);
      if (!grup.test(k)) return false;
      if (ara && (m.ad||'').toLocaleLowerCase('tr').indexOf(ara)===-1) return false;
      return true;
   });
   $('#ag_kuyruk_say').text(veri.length);

   if (!veri.length){
      $('#ag_kuyruk').html('<div class="ag-gecmis-bos"><i class="fa fa-inbox"></i>Bu filtreye uygun müşteri yok.</div>');
      return;
   }
   var html='';
   veri.forEach(function(m){
      var k = (m.durum_kod===undefined||m.durum_kod===null)?null:parseInt(m.durum_kod,10);
      var st = durStil(k);
      var harf = ((m.ad||'?').trim().charAt(0)||'?').toUpperCase();
      var sec = (agSecili && agSecili.aranacak_musteri_id===m.aranacak_musteri_id) ? 'secili' : '';
      html += '<div class="ag-musteri '+sec+'" data-id="'+agEsc(m.aranacak_musteri_id)+'">'+
                 '<div class="ag-mus-av">'+agEsc(harf)+'</div>'+
                 '<div class="ag-mus-orta">'+
                    '<div class="ag-mus-ad">'+agEsc(m.ad)+'</div>'+
                    '<div class="ag-mus-tel"><i class="fa fa-phone"></i> '+agEsc(m.telefonGizlenmis||'gizli')+'</div>'+
                 '</div>'+
                 '<span class="ag-mus-dur d-'+st.c+'">'+st.et+'</span>'+
              '</div>';
   });
   $('#ag_kuyruk').html(html);
}

$('#ag_ara').on('input', agKuyrukCiz);
$(document).on('click', '.ag-fil-chip', function(){ agFiltre=$(this).data('fil'); agFiltreCiz(); agKuyrukCiz(); });

// ---- Müşteri seç -> detay ----
$(document).on('click', '.ag-musteri', function(){
   var id = $(this).data('id');
   var m = agMusteriler.find(function(x){ return String(x.aranacak_musteri_id)===String(id); });
   if (!m) return;
   agSecili = m;
   $('.ag-musteri').removeClass('secili');
   $(this).addClass('secili');
   agDetayCiz(m);
});

// Script kartı (script tanımlıysa)
function agScriptKart(){
   if (!agScriptler.length) return '';
   var opts = '<option value="">— Görüşme scripti seçin —</option>';
   agScriptler.forEach(function(s){ opts += '<option value="'+agEsc(s.id)+'">'+agEsc(s.baslik)+'</option>'; });
   return '<div class="ag-kart">'+
      '<div class="ag-kart-bas"><i class="fa fa-file-text-o"></i> Görüşme Scripti</div>'+
      '<select class="ag-not-alani" id="ag_script_sec">'+opts+'</select>'+
      '<div id="ag_script_icerik" class="ag-script-icerik" style="display:none;"></div>'+
   '</div>';
}
// Kategori + alt kategori select'leri (kategori tanımlıysa)
function agKatSelectler(){
   if (!agKategoriler.length) return '';
   var opts = '<option value="">Kategori seçin (opsiyonel)</option>';
   agKategoriler.forEach(function(k){ opts += '<option value="'+agEsc(k.id)+'">'+agEsc(k.ad)+'</option>'; });
   return '<div class="ag-kat-secim">'+
      '<select class="ag-not-alani" id="ag_kat">'+opts+'</select>'+
      '<select class="ag-not-alani" id="ag_altkat" style="display:none;"></select>'+
   '</div>';
}

function agDetayCiz(m){
   var k = (m.durum_kod===undefined||m.durum_kod===null)?null:parseInt(m.durum_kod,10);
   var st = durStil(k);
   var harf = ((m.ad||'?').trim().charAt(0)||'?').toUpperCase();
   var listeBaslik = $('.ag-liste-chip[data-id="'+agAktifListe+'"]').data('baslik') || '';
   var randevuBilgi = (m.not_tarih && m.not_saat) ? '<div style="margin-top:6px;font-size:12.5px;color:#1565c0;"><i class="fa fa-calendar-check-o"></i> Randevu: '+agEsc(m.not_tarih)+' '+agEsc(m.not_saat)+'</div>' : '';

   var html =
   '<div class="ag-dhead">'+
      '<div class="ag-dhead-av">'+agEsc(harf)+'</div>'+
      '<div>'+
         '<div class="ag-dhead-ad">'+agEsc(m.ad)+'</div>'+
         '<div class="ag-dhead-sub"><i class="fa fa-phone"></i> '+agEsc(m.telefonGizlenmis||'gizli')+' <span class="durnok">'+st.et+'</span></div>'+
      '</div>'+
      '<button class="ag-ara-btn'+(agBeklemedeId!==null?' pasif':'')+'" id="ag_ara_btn" data-id="'+agEsc(m.aranacak_musteri_id)+'"><i class="fa fa-phone"></i> ARA</button>'+
   '</div>'+
   '<div class="ag-dbody">'+

      // Liste / kampanya bağlamı
      '<div class="ag-kart">'+
         '<div class="ag-kart-bas"><i class="fa fa-bullhorn"></i> Aktif Liste</div>'+
         '<div style="font-weight:700;color:#241b3a;">'+agEsc(listeBaslik)+'</div>'+
         randevuBilgi +
      '</div>'+

      // Görüşme scripti (tanımlıysa)
      agScriptKart() +

      // Görüşme sonucu formu — ARA yapilana kadar KILITLI (arama yapmadan sonuc isaretlenemez)
      '<div class="ag-kart'+( (agBeklemedeId!==null && String(agBeklemedeId)===String(m.aranacak_musteri_id)) ? '' : ' kilitli')+'" id="ag_sonuc_kart">'+
         '<div class="ag-kart-bas"><i class="fa fa-check-circle"></i> Görüşme Sonucu</div>'+
         '<div class="ag-kilit-not"><i class="fa fa-lock"></i> Sonuç işaretlemek için önce yukarıdan <b>ARA</b>’ya basın.</div>'+
         '<div class="ag-sonuc-grid">'+
            '<div class="ag-sonuc sec-yesil" data-sonuc="4"><i class="fa fa-phone"></i>Görüşüldü</div>'+
            '<div class="ag-sonuc sec-kirmizi" data-sonuc="2"><i class="fa fa-phone-square"></i>Cevapsız</div>'+
            '<div class="ag-sonuc sec-koyukirmizi" data-sonuc="5"><i class="fa fa-ban"></i>Meşgul</div>'+
            '<div class="ag-sonuc sec-turuncu" data-sonuc="0"><i class="fa fa-volume-off"></i>Ulaşılamadı</div>'+
         '</div>'+
         '<div class="ag-donusum-lbl">🎯 Sonuç (hedef)</div>'+
         '<div class="ag-donusum-grid">'+
            '<div class="ag-sonuc ag-donusum sec-mavi" data-sonuc="6"><i class="fa fa-calendar-check-o"></i>Ön Görüşme Randevusu</div>'+
            '<div class="ag-sonuc ag-donusum sec-altin" data-sonuc="7"><i class="fa fa-shopping-bag"></i>Telefonda Satış</div>'+
         '</div>'+
         agKatSelectler() +
         '<textarea class="ag-not-alani" id="ag_not" placeholder="Görüşme notu (müşteri ne dedi, talep, vb.)..."></textarea>'+
         // Telefonda Satış tutarı (sonuc=7)
         '<div class="ag-satis-alan" id="ag_satis_alan" style="display:none;">'+
            '<span class="ag-satis-lbl">Satış tutarı (₺)</span>'+
            '<input type="number" min="0" step="0.01" id="ag_satis_tutari" placeholder="örn: 1500">'+
            '<button type="button" class="ag-kasa-btn" id="ag_satis_kasa"><i class="fa fa-money"></i> Kasaya İşle</button>'+
         '</div>'+
         '<label class="ag-sonra"><input type="checkbox" id="ag_sonra_chk"> Müşteri sonra aranmak istedi (Tekrar Aranacak)</label>'+
         '<div class="ag-sonra-alan" id="ag_sonra_alan">'+
            '<input type="date" id="ag_sonra_tarih">'+
            '<input type="time" id="ag_sonra_saat">'+
         '</div>'+
         '<button class="ag-kaydet" id="ag_kaydet"><i class="fa fa-save"></i> Sonucu Kaydet</button>'+
         // Memnuniyet anketi — arama şartına bağlı değil; yetkiliyse görünür (kilitli grileşmeden bağımsız)
         (window.agAnketYetki ? '<button type="button" class="ag-anket-btn" id="ag_anket_gonder"><i class="fa fa-star"></i> Memnuniyet Anketi Gönder</button>' : '')+
      '</div>'+

      // Çağrı geçmişi (ses kayıtları)
      '<div class="ag-kart">'+
         '<div class="ag-kart-bas"><i class="fa fa-history"></i> Çağrı Geçmişi & Ses Kayıtları'+
            '<button class="ag-gecmis-yenile" id="ag_gecmis_yenile" title="Yenile"><i class="fa fa-refresh"></i> Yenile</button>'+
         '</div>'+
         '<div id="ag_gecmis"><div class="ag-spin"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div></div>'+
      '</div>'+

   '</div>';

   $('#ag_detay_bos').hide();
   $('#ag_detay_icerik').html(html).show();
   agGecmisYukle(m.aranacak_musteri_id);
}

// ---- Çağrı geçmişi (bu görüşmenin AYRIK kayıtları: gorusme_notlari) ----
function agGecmisYukle(aranacakId){
   $.ajax({
      url:'/isletmeyonetim/cagri-musteri-gecmisi', method:'POST',
      data:{ aranacak_musteri_id:aranacakId, _token:$('input[name="_token"]').val() },
      success:function(res){
         var gecmis = (res && res.gecmis) ? res.gecmis : [];
         if (!gecmis.length){
            $('#ag_gecmis').html('<div class="ag-gecmis-bos"><i class="fa fa-microphone-slash"></i>Bu müşteriyle henüz görüşme kaydedilmedi.</div>');
            return;
         }
         var html='';
         gecmis.forEach(function(g){
            var st = durStil(g.sonuc_kod);
            var not = g.not ? '<div style="font-size:12.5px;color:#574f6b;margin-top:7px;"><i class="fa fa-sticky-note-o" style="color:#b6aecb;margin-right:5px;"></i>'+agEsc(g.not)+'</div>' : '';
            var ses = g.ses ? '<audio controls preload="none" src="'+agEsc(g.ses)+'"></audio>'
                            : '<div style="font-size:11.5px;color:#aab0c0;margin-top:8px;"><i class="fa fa-hourglass-half"></i> Ses kaydı işleniyor — birazdan "Yenile"ye basın.</div>';
            var randevuBilgi = (g.randevu_tarih) ? '<span class="ag-randevu-zaman"><i class="fa fa-calendar-check-o"></i> '+agEsc(g.randevu_tarih)+(g.randevu_saat?(' '+agEsc(g.randevu_saat)):'')+'</span>' : '';
            var satisBilgi = (g.satis_tutari) ? '<span class="ag-satis-rozet"><i class="fa fa-shopping-bag"></i> '+agEsc(g.satis_tutari)+' ₺</span>' : '';
            html += '<div class="ag-gecmis-item bl-'+st.c+'">'+
                       '<div class="ag-gecmis-top">'+
                          '<span class="ag-gecmis-tarih"><i class="fa fa-clock-o"></i> '+agEsc(g.tarih)+'</span>'+
                          '<span class="ag-mus-dur d-'+st.c+'" style="margin-left:8px;">'+st.et+'</span>'+
                          randevuBilgi + satisBilgi +
                       '</div>'+
                       not +
                       ses +
                    '</div>';
         });
         $('#ag_gecmis').html(html);
      },
      error:function(){ $('#ag_gecmis').html('<div class="ag-gecmis-bos" style="color:#c62828;">Geçmiş yüklenemedi.</div>'); }
   });
}

// ---- Sonuç butonları ----
$(document).on('click', '.ag-sonuc', function(){
   $('.ag-sonuc').removeClass('aktif');
   $(this).addClass('aktif');
   agSecilenSonuc = parseInt($(this).data('sonuc'),10);
   // sonuç seçilince "sonra ara" kapanır (çelişmesin)
   $('#ag_sonra_chk').prop('checked', false);
   $('#ag_sonra_alan').hide();
   // Telefonda Satış(7) -> tutar alanı
   $('#ag_satis_alan').css('display', agSecilenSonuc===7 ? 'flex' : 'none');
   // Ön Görüşme Randevusu(6) -> gerçek ön görüşme randevu ekranı açılır
   if (agSecilenSonuc===6){ agOnGorusmeAc(); }
});

// Ön Görüşme: gerçek randevu modalını müşteri prefill'li açar (KVKK maskesi bu an için kalkar)
function agOnGorusmeAc(){
   if (!agSecili) return;
   $.post('/isletmeyonetim/cagri-musteri-ongorusme-bilgi',
      { aranacak_musteri_id: agSecili.aranacak_musteri_id, _token: $('input[name="_token"]').val() },
      function(res){
         if (res && res.success){
            try {
               var $sel = $('#musteri_select_list');
               if ($sel.length){
                  if ($sel.find('option[value="'+res.user_id+'"]').length===0){
                     $sel.append(new Option(res.ad || ('#'+res.user_id), res.user_id, true, true));
                  } else { $sel.val(res.user_id); }
                  $sel.trigger('change');
               }
               if (res.ad){ $('#ad_soyad').val(res.ad); }
               if (res.telefon){ $('#telefon').val(res.telefon); }
            } catch(e){}
            $('#ongorusme-modal').modal('show');
         } else {
            swal({ type:'warning', title:'Açılamadı', text:(res&&res.message)||'Müşteri bilgisi alınamadı.' });
         }
      }
   ).fail(function(){ swal({ type:'error', title:'Hata', text:'Ön görüşme ekranı açılamadı.' }); });
}

// ---- Memnuniyet anketi gönder (görüşme sonucu penceresinden) ----
$(document).on('click', '#ag_anket_gonder', function(){
   if (!agSecili) return;
   var $btn = $(this);
   swal({
      title: 'Memnuniyet anketi gönderilsin mi?',
      text: 'Müşteriye anket linki WhatsApp veya SMS ile gönderilecek.',
      type: 'question',
      showCancelButton: true,
      confirmButtonText: 'Evet, gönder',
      cancelButtonText: 'Vazgeç',
      confirmButtonColor: '#25D366',
   }).then(function(r){
      if (!r || !r.value) return;
      $btn.prop('disabled', true);
      // aranacak_musteri_id -> gerçek müşteri user_id çöz (satış/ön görüşme ile aynı uç)
      $.post('/isletmeyonetim/cagri-musteri-ongorusme-bilgi',
         { aranacak_musteri_id: agSecili.aranacak_musteri_id, _token: $('input[name="_token"]').val() },
         function(res){
            if (!(res && res.success && res.user_id)){
               $btn.prop('disabled', false);
               swal({ type:'warning', title:'Gönderilemedi', text:(res&&res.message)||'Müşteri bilgisi alınamadı.' });
               return;
            }
            $.ajax({
               url: '/isletmeyonetim/anket-hizli-gonder',
               method: 'POST',
               timeout: 20000,
               data: { user_id: res.user_id, sube: $('input[name="sube"]').val() || '' },
               dataType: 'json',
               headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            }).done(function(a){
               if (a && a.basarili){
                  var kanal = a.kanal === 'whatsapp' ? 'WhatsApp' : 'SMS';
                  swal('Gönderildi', 'Anket ' + kanal + ' ile iletildi.', 'success');
               } else {
                  swal('Gönderilemedi', (a && a.mesaj) || 'Bilinmeyen hata.', 'error');
               }
            }).fail(function(xhr, status){
               var msg = 'İstek başarısız.';
               if (xhr && xhr.responseText){ try { var j = JSON.parse(xhr.responseText); if (j && j.mesaj) msg = j.mesaj; } catch(e){} }
               if (xhr && xhr.status) msg += ' (HTTP ' + xhr.status + ')';
               if (status === 'timeout') msg = 'Sunucu 20 saniye içinde cevap vermedi.';
               swal('Hata', msg, 'error');
            }).always(function(){ $btn.prop('disabled', false); });
         }
      ).fail(function(){ $btn.prop('disabled', false); swal({ type:'error', title:'Hata', text:'İstek başarısız.' }); });
   });
});

// Telefonda Satış: sade satış popup'ını müşteri + tutar prefill'li açar
$(document).on('click', '#ag_satis_kasa', function(){
   if (!agSecili) return;
   var tutar = $('#ag_satis_tutari').val() || '';
   $.post('/isletmeyonetim/cagri-musteri-ongorusme-bilgi',
      { aranacak_musteri_id: agSecili.aranacak_musteri_id, _token: $('input[name="_token"]').val() },
      function(res){
         if (res && res.success && res.user_id){
            $('#hs_musteri_id').val(res.user_id);
            $('#hs_musteri_ad').text(res.ad || ('#'+res.user_id));
            $('#hs_fiyat').val(tutar);
            $('#hs_tip').val('paket').trigger('change');
            $('.hs-item').val('');
            $('#hs_adet').val(1);
            $('#ag_satis_modal').modal('show');
         } else {
            swal({ type:'warning', title:'Açılamadı', text:(res&&res.message)||'Müşteri bilgisi alınamadı.' });
         }
      }
   ).fail(function(){ swal({ type:'error', title:'Hata', text:'Satış ekranı açılamadı.' }); });
});

// Kalem tipi -> ilgili seçim kutusu + adet (ürün)
$(document).on('change', '#hs_tip', function(){
   var t = $(this).val();
   $('.hs-item').hide().val('');
   $('#hs_item_'+t).show();
   $('#hs_adet_kutu').toggle(t==='urun');
});
// Kalem seçilince fiyatı otomatik doldur
$(document).on('change', '.hs-item', function(){
   var f = $(this).find('option:selected').data('fiyat');
   if (f!==undefined && f!=='' && !($('#hs_fiyat').val()>0)){ $('#hs_fiyat').val(f); }
});
// Satışı oluştur -> gerçek satış kaydı
$(document).on('click', '#hs_kaydet', function(){
   var tip = $('#hs_tip').val();
   var itemId = $('#hs_item_'+tip).val();
   var fiyat = $('#hs_fiyat').val();
   var odeme = $('#hs_odeme').val();
   var musteriId = $('#hs_musteri_id').val();
   if (!itemId){ swal({type:'warning',title:'Seçim yapın',text:'Satılan paket/hizmet/ürünü seçin.'}); return; }
   if (!fiyat || parseFloat(fiyat)<=0){ swal({type:'warning',title:'Fiyat girin',text:'Satış fiyatını girin.'}); return; }
   if (!odeme){ swal({type:'warning',title:'Ödeme',text:'Ödeme yöntemi seçin.'}); return; }
   var $btn = $(this); $btn.prop('disabled', true);
   $.post('/isletmeyonetim/cagri-hizli-satis',
      { sube:$('input[name="sube"]').val(), musteri_id:musteriId, kalem_tip:tip, kalem_id:itemId,
        fiyat:fiyat, odeme_yontemi:odeme, adet:$('#hs_adet').val()||1, _token:$('input[name="_token"]').val() },
      function(res){
         if (res && res.success){
            $('#ag_satis_modal').modal('hide');
            swal({ type:'success', title:'Satış oluşturuldu', text:res.message||'Kasaya işlendi.', timer:2600, showConfirmButton:false });
         } else { swal({ type:'error', title:'Hata', text:(res&&res.message)||'Satış oluşturulamadı.' }); }
      }
   ).fail(function(){ swal({ type:'error', title:'Hata', text:'İşlem başarısız.' }); })
    .always(function(){ $btn.prop('disabled', false); });
});

$(document).on('change', '#ag_sonra_chk', function(){
   if ($(this).is(':checked')){
      $('#ag_sonra_alan').css('display','flex');
      $('#ag_satis_alan').hide();
      $('.ag-sonuc').removeClass('aktif'); agSecilenSonuc=null; // tekrar aranacak, sonuçla çelişmesin
   } else {
      $('#ag_sonra_alan').hide();
   }
});

// Script seçimi -> içeriği göster
$(document).on('change', '#ag_script_sec', function(){
   var id = $(this).val();
   var s = agScriptler.find(function(x){ return String(x.id)===String(id); });
   if (s && (s.icerik||'').trim()){ $('#ag_script_icerik').text(s.icerik).show(); }
   else { $('#ag_script_icerik').hide().text(''); }
});

// Kategori seçimi -> alt kategorileri doldur
$(document).on('change', '#ag_kat', function(){
   var id = $(this).val();
   var k = agKategoriler.find(function(x){ return String(x.id)===String(id); });
   var $alt = $('#ag_altkat');
   if (k && (k.altlar||[]).length){
      var opts = '<option value="">Alt kategori seçin</option>';
      k.altlar.forEach(function(a){ opts += '<option value="'+agEsc(a.id)+'">'+agEsc(a.ad)+'</option>'; });
      $alt.html(opts).show();
   } else { $alt.hide().html(''); }
});

// ---- ARA (santral originate) ----
// Bir arama yapildiktan sonra, sonucu KAYDEDILENE kadar yeni arama yapilamaz.
var agBeklemedeId = null;   // sonucu bekleyen aramanin musteri id'si (null = bekleyen yok)
var agAramaGonderiliyor = false; // ayni anda cift gonderimi engeller

$(document).on('click', '#ag_ara_btn', function(){
   // Onceki aramanin sonucu kaydedilmeden yeni arama YOK
   if (agBeklemedeId !== null){
      swal({ type:'warning', title:'Önce sonucu kaydedin',
             text:'Yeni arama yapmadan önce, yaptığınız aramanın sonucunu (Görüşüldü / Cevapsız / Meşgul / Ulaşılamadı veya Sonra Ara) seçip "Sonucu Kaydet"e basın.' });
      return;
   }
   if (agAramaGonderiliyor) return;
   var id = $(this).data('id');
   agAramaGonderiliyor = true;
   $.ajax({
      url:'/isletmeyonetim/arama-baslat', method:'POST',
      data:{ aranacak_musteri_id:id, _token:$('input[name="_token"]').val() },
      success:function(res){
         if (res.success){
            agDurumGuncelle(id, 1); // optimistic: Arandı
            agBeklemedeId = id;     // sonuç kaydedilene kadar kilitle
            $('#ag_ara_btn').addClass('pasif');
            $('#ag_sonuc_kart').removeClass('kilitli'); // arama yapıldı -> sonuç formu açılır
            swal({ type:'success', title:'Arama başlatıldı', text:res.message||'Telefonunuz (Bria) çalacak, açın.', timer:3800, showConfirmButton:false });
         } else {
            swal({ type:'warning', title:'Aranamadı', text:res.message||'Arama başlatılamadı.' });
         }
      },
      error:function(xhr){
         var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Arama başlatılamadı.';
         swal({ type:'error', title:'Hata', text:msg });
      },
      complete:function(){ agAramaGonderiliyor = false; }
   });
});

// ---- Sonucu kaydet ----
$(document).on('click', '#ag_kaydet', function(){
   if (!agSecili) return;
   var sonraMu   = $('#ag_sonra_chk').is(':checked');
   var onGorusme = (agSecilenSonuc===6);
   var satisMi   = (agSecilenSonuc===7);
   var tarih = $('#ag_sonra_tarih').val();
   var saat  = $('#ag_sonra_saat').val();
   var not   = $('#ag_not').val();
   var satisTutari = satisMi ? ($('#ag_satis_tutari').val()||'') : '';

   if (sonraMu && (!tarih || !saat)){
      swal({ type:'warning', title:'Tarih/saat seçin', text:'Tekrar arama için tarih ve saat seçin.' }); return;
   }
   if (satisMi && (!satisTutari || parseFloat(satisTutari) <= 0)){
      swal({ type:'warning', title:'Satış tutarı', text:'Telefonda satış için tutar girin.' }); return;
   }
   if (!sonraMu && agSecilenSonuc===null && !(not||'').trim()){
      swal({ type:'warning', title:'Sonuç seçin', text:'Bir görüşme sonucu seçin, tekrar arama oluşturun ya da not yazın.' }); return;
   }

   var katId = $('#ag_kat').val() || '';
   var altKatId = $('#ag_altkat').val() || '';

   var $btn = $(this); $btn.prop('disabled', true);
   $.ajax({
      url:'/isletmeyonetim/santral_not_ekle', method:'POST',
      data:{
         arama_detay_id: agAktifListe,
         aranacak_musteri_id: agSecili.aranacak_musteri_id,
         noticerik: not,
         sonuc: sonraMu ? '' : (agSecilenSonuc===null ? '' : agSecilenSonuc),
         kategori_id: katId,
         alt_kategori_id: altKatId,
         satis_tutari: satisMi ? satisTutari : '',
         santralnottarih: sonraMu ? tarih : '',
         santralnotsaat:  sonraMu ? saat  : '',
         _token:$('input[name="_token"]').val()
      },
      success:function(res){
         if (res.success){
            var yeniDurum = sonraMu ? 3 : (agSecilenSonuc===null ? (agSecili.durum_kod||4) : agSecilenSonuc);
            agSecili.not = not;
            agSecili.not_tarih = sonraMu ? tarih : agSecili.not_tarih;
            agSecili.not_saat  = sonraMu ? saat  : agSecili.not_saat;
            agDurumGuncelle(agSecili.aranacak_musteri_id, yeniDurum);
            agGecmisYukle(agSecili.aranacak_musteri_id); // sonuç kaydedildi -> kaydı/geçmişi tazele
            // Bu müşterinin sonucu kaydedildi -> arama kilidini aç
            if (agBeklemedeId !== null && String(agBeklemedeId) === String(agSecili.aranacak_musteri_id)){
               agBeklemedeId = null;
               $('#ag_ara_btn').removeClass('pasif');
            }
            agSecilenSonuc = null;
            $('.ag-sonuc').removeClass('aktif');
            $('#ag_not').val(''); $('#ag_satis_alan').hide(); $('#ag_sonra_alan').hide(); $('#ag_sonra_chk').prop('checked', false);
            $('#ag_sonuc_kart').addClass('kilitli'); // sonuç kaydedildi -> form tekrar kilitlenir (yeni arama gerekir)
            swal({ type:'success', title:'Kaydedildi', timer:1400, showConfirmButton:false });
         } else {
            swal({ type:'error', title:'Hata', text:res.message||'Kaydedilemedi.' });
         }
      },
      error:function(){ swal({ type:'error', title:'Hata', text:'Kaydedilirken sorun oluştu.' }); },
      complete:function(){ $btn.prop('disabled', false); }
   });
});

// Bir müşterinin durumunu hem state'te hem ekranda günceller
function agDurumGuncelle(aranacakId, yeniKod){
   var m = agMusteriler.find(function(x){ return String(x.aranacak_musteri_id)===String(aranacakId); });
   if (m) m.durum_kod = yeniKod;
   if (agSecili && String(agSecili.aranacak_musteri_id)===String(aranacakId)){
      var st = durStil(yeniKod);
      $('#ag_detay_icerik .durnok').attr('class','durnok').text(st.et);
   }
   agFiltreCiz();
   agKuyrukCiz();
}

// ---- Arama randevusu hatırlatma popup'ı (zamanı gelen randevular) ----
var agGosterilenRandevular = {}; // tekrar tekrar uyarmamak icin (id -> true)

function agRandevuKontrol(){
   $.get('/isletmeyonetim/cagri-yaklasan-randevular', { sube: $('input[name="sube"]').val() }, function(res){
      var liste = (res && res.randevular) ? res.randevular : [];
      var yeni = liste.filter(function(r){ return !agGosterilenRandevular[r.id]; });
      if (!yeni.length) return;
      yeni.forEach(function(r){ agGosterilenRandevular[r.id] = true; });

      var hedef = yeni[0]; // OK -> bu müşteri arama ekranında açılır
      var metin = yeni.map(function(r){ return '• ' + r.ad + ' — ' + r.tarih + ' ' + r.saat; }).join('\n');
      var baslik = yeni.length>1 ? (yeni.length+' arama randevusunun zamanı geldi') : 'Arama randevusu zamanı geldi';
      var gitEt = function(){ if (hedef && hedef.arama_id){ agMusteriyeGit(hedef.arama_id, hedef.id); } };

      if (typeof swal === 'function'){
         var sonuc = swal({
            type:'info', title:'🔔 '+baslik, text:metin,
            showCancelButton:true,
            confirmButtonText: (yeni.length>1 ? 'İlk müşteriye git' : 'Müşteriye git'),
            cancelButtonText:'Kapat'
         }, function(onay){ if (onay) gitEt(); });            // SweetAlert1 callback
         if (sonuc && typeof sonuc.then === 'function'){      // SweetAlert2 promise
            sonuc.then(function(res){ if (res === true || (res && res.value)) gitEt(); }).catch(function(){});
         }
      } else {
         if (confirm('🔔 '+baslik+'\n'+metin+'\n\nMüşteriye gidilsin mi?')) gitEt();
      }
   });
}

$(document).ready(function(){
   agListeleriYukle(); agScriptleriYukle(); agKategorileriYukle();
   // Randevu hatirlatma: acilista + her 45 sn'de bir kontrol et
   agRandevuKontrol();
   setInterval(agRandevuKontrol, 45000);
});
</script>
@endsection
