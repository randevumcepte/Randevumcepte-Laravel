@if(Auth::guard('satisortakligi')->check()) 
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp 
@else 
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp 
@endif 

@extends($_layout)

@section('content')
<input type="hidden" id="satis_takibi_ekrani" value="1">
<div class="page-header">
   <div class="row">
      <div class="col-md-6 col-sm-6">
         <div class="title">
            <h1 style="font-size:20px">{{$sayfa_baslik}}</h1>
         </div>
         <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
               </li>
               <li class="breadcrumb-item active" aria-current="page">
                  {{$sayfa_baslik}}
               </li>
            </ol>
         </nav>
      </div>
      <div class="col-md-6 col-sm-6 text-right">
         <a href="/isletmeyonetim/yenitahsilat{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}"  class="btn btn-success btn-lg yenieklebuton"><i class="fa fa-plus"></i> Yeni Satış & Tahsilat</a>
      </div>
   </div>
</div>

<div class="card-box mb-30">
   <div class="pb-20" style="padding-top:20px">
      <!-- Özet Bilgiler -->
      <div class="row mb-3" style="padding: 0 30px 10px 30px">
         <div class="col-md-4 col-sm-6">
            <div class="card card-box bg-primary text-white">
               <div class="card-body">
                  <h6 class="card-title" style="color: white;">Toplam Satış</h6>
                  <h4 class="card-text" style="color: white;" id="toplamSatisTutar">0,00 ₺</h4>
               </div>
            </div>
         </div>
         <div class="col-md-4 col-sm-6">
            <div class="card card-box bg-success text-white">
               <div class="card-body">
                  <h6 class="card-title" style="color: white;">Ödenen Toplam</h6>
                  <h4 class="card-text" style="color: white;" id="odenenToplamTutar">0,00 ₺</h4>
               </div>
            </div>
         </div>
         <div class="col-md-4 col-sm-6">
            <div class="card card-box bg-danger text-white">
               <div class="card-body">
                  <h6 class="card-title" style="color: white;">Kalan Toplam</h6>
                  <h4 class="card-text" style="color: white;" id="kalanToplamTutar">0,00 ₺</h4>
               </div>
            </div>
         </div>
        
      </div>

      <div class="row" style="padding: 0 30px 20px 30px">
         <div class="col-sm-3 col-xs-12 col-12">
            <label>Zaman Aralığı</label>
            <select class="form-control" id="satis_zamana_gore_filtre">
               <option value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
               <option value="{{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}}">Dün</option>
               <option selected value="<?php echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
               <option value="<?php echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
               <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
               <option value="<?php echo date(date('Y',strtotime('-1 year')).'-01-01') . " / ". date(date('Y',strtotime('-1 year')).'-12-31'); ?>">Geçen yıl</option>
               <option value="ozel">Özel</option>
            </select>
         </div>
         <div class="col-sm-3 col-xs-6 col-6" id="satis_zamana_gore_filtre1" style="display:none;"> 
            <label>Başlangıç Tarihi</label>
            <input class="form-control" placeholder="Başlangıç Tarihini seçiniz.." type="text" id="satisbaslangictarihi" />
         </div>
         <div class="col-sm-3 col-xs-6 col-6" id="satis_zamana_gore_filtre2" style="display:none;">
            <label>Bitiş Tarihi</label>
            <input class="form-control" placeholder="Bitiş tarihini seçiniz.." type="text" id="satisbitistarihi" />
         </div>
         <div class="col-sm-3 col-xs-6 col-6"> 
            <label>Personele Göre Filtrele</label>
            <select class="form-control personel_secimi" id='satisPersonelFiltre' style="width:100%">
               <option></option>
            </select>
         </div>
         <div class="col-sm-3 col-xs-12 col-12">
            <label>Satış Durumu </label>
            <select class="form-control" id="satis_durumu_filtre">
               <option value="" selected>Tüm Satışlar</option>
               <option value="acik">Açık Satışlar</option>
               <option value="kapali">Kapalı Satışlar</option>
            </select>
            
         </div>
          <div class="col-md-3" style="    margin-top: 2%;"> <span  id="satisDurumuSayilari" style="font-size: 12px; font-weight: normal;"></span>
</div>
      </div>

      <ul class="nav nav-tabs element" role="tablist">
         <li class="nav-item" style="margin-left: 20px;">
            <button class="btn btn-outline-primary active" data-toggle="tab" href="#tum_adisyonlar" role="tab" aria-selected="false" style="width: 120px;">
               Tümü 
            </button>
         </li>
         <li class="nav-item" style="margin-left: 20px;display: inline-block;">
            <button class="btn btn-outline-primary" data-toggle="tab" href="#hizmet_adisyonlar" role="tab" aria-selected="false" style="width: 160px;">
               Hizmetler/İşlemler
            </button>
         </li>
         <li class="nav-item" style="margin-left: 20px;display: inline-block;">
            <button class="btn btn-outline-primary" data-toggle="tab" href="#ürün_adisyonlar" role="tab" aria-selected="false" style="width: 120px;">
               Ürünler
            </button>
         </li>
         <li class="nav-item" style="margin-left: 20px;display: inline-block;">
            <button class="btn btn-outline-primary" data-toggle="tab" href="#paket_adisyonlar" role="tab" style="width: 120px;">
               Paketler 
            </button>
         </li>
         <li class="nav-item" style="margin-left: 20px;display: inline-block;">
            <button class="btn btn-outline-primary" data-toggle="tab" href="#taksitli_alacaklar" role="tab" style="width: 120px;">
               Taksitler 
            </button>
         </li>
      </ul>

      <div class="tab-content" style="padding: 0 30px 0 30px;">
         <!-- Tüm Adisyonlar Tabı -->
         <div class="tab-pane fade show active" id="tum_adisyonlar" role="tab-panel" style="margin-top: 20px;">
            <table class="data-table table stripe hover nowrap" id="adisyon_liste">
               <thead>
                  <tr>
                     <th>Durum</th>
                     <th>Satış Tarihi</th>
                     <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                     <th>Yaklaşan Ödeme Tarihi</th>
                     <th>Adisyon İçeriği</th>
                     <th>Toplam ₺</th>
                     <th>Ödenen ₺</th>
                     <th>Kalan ₺</th>
                     <th>İşlemler</th>
                  </tr>
               </thead>
               <tbody></tbody>
            </table>
         </div>

         <!-- Hizmet Adisyonları Tabı -->
         <div class="tab-pane fade show" id="hizmet_adisyonlar" role="tab-panel" style="margin-top: 20px;">
            <table class="data-table table stripe hover nowrap" id="adisyon_liste_hizmet">
               <thead>
                  <tr>
                     <th>Durum</th>
                     <th>Satış Tarihi</th>
                     <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                     <th>Yaklaşan Ödeme Tarihi</th>
                     <th>Adisyon İçeriği</th>
                     <th>Toplam ₺</th>
                     <th>Ödenen ₺</th>
                     <th>Kalan ₺</th>
                     <th>İşlemler</th>
                  </tr>
               </thead>
               <tbody></tbody>
            </table>
         </div>

         <!-- Ürün Adisyonları Tabı -->
         <div class="tab-pane fade show" id="ürün_adisyonlar" role="tab-panel" style="margin-top: 20px;">
            <table class="data-table table stripe hover nowrap" id="adisyon_liste_urun">
               <thead>
                  <tr>
                     <th>Durum</th>
                     <th>Satış Tarihi</th>
                     <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                     <th>Yaklaşan Ödeme Tarihi</th>
                     <th>Adisyon İçeriği</th>
                     <th>Toplam ₺</th>
                     <th>Ödenen ₺</th>
                     <th>Kalan ₺</th>
                     <th>İşlemler</th>
                  </tr>
               </thead>
               <tbody></tbody>
            </table>
         </div>

         <!-- Paket Adisyonları Tabı -->
         <div class="tab-pane fade show" id="paket_adisyonlar" role="tab-panel" style="margin-top: 20px;">
            <table class="data-table table stripe hover nowrap" id="adisyon_liste_paket">
               <thead>
                  <tr>
                     <th>Durum</th>
                     <th>Satış Tarihi</th>
                     <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                     <th>Yaklaşan Ödeme Tarihi</th>
                     <th>Adisyon İçeriği</th>
                     <th>Toplam ₺</th>
                     <th>Ödenen ₺</th>
                     <th>Kalan ₺</th>
                     <th>İşlemler</th>
                  </tr>
               </thead>
               <tbody></tbody>
            </table>
         </div>

         <!-- Taksitli Alacaklar Tabı -->
         <div class="tab-pane fade show" id="taksitli_alacaklar" role="tab-panel" style="margin-top: 20px;">
            <div class="tab">
               <ul class="nav nav-tabs element" role="tablist">
                  <li class="nav-item">
                     <a class="nav-link active" data-toggle="tab" href="#tum_taksit" role="tab" aria-selected="true" style="height: 80px;">Tümü</a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" data-toggle="tab" href="#acik_taksit" role="tab" aria-selected="false" style="height: 80px;">Açık Taksitler</a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" data-toggle="tab" href="#kapali_taksit" role="tab" aria-selected="false" style="height: 80px;">Kapalı Taksitler</a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" data-toggle="tab" href="#gecikmis_taksit" role="tab" aria-selected="false" style="height: 80px;">Gecikmiş Taksitler</a>
                  </li>
               </ul>
               <div class="tab-content">
                  <div class="tab-pane fade show active" id="tum_taksit" role="tab-panel" style="margin-top: 20px;">
                     <table class="data-table table stripe hover nowrap" id="tum_taksitler">
                        <thead>
                           <tr>
                              <th>Durum</th>
                              <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                              <th>Vade Sayısı</th>
                              <th>Ödenmiş</th>
                              <th>Ödenmemiş</th>
                              <th>Yaklaşan Taksit</th>
                              <th>İşlemler</th>
                           </tr>
                        </thead>
                        <tbody></tbody>
                     </table>
                  </div>
                  <div class="tab-pane fade show" id="acik_taksit" role="tab-panel" style="margin-top: 20px;">
                     <table class="data-table table stripe hover nowrap" id="acik_taksitler">
                        <thead>
                           <tr>
                              <th>Durum</th>
                              <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                              <th>Vade Sayısı</th>
                              <th>Ödenmiş</th>
                              <th>Ödenmemiş</th>
                              <th>Yaklaşan Taksit</th>
                              <th>İşlemler</th>
                           </tr>
                        </thead>
                        <tbody></tbody>
                     </table>
                  </div>
                  <div class="tab-pane fade show" id="kapali_taksit" role="tab-panel" style="margin-top: 20px;">
                     <table class="data-table table stripe hover nowrap" id="kapali_taksitler">
                        <thead>
                           <tr>
                              <th>Durum</th>
                              <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                              <th>Vade Sayısı</th>
                              <th>Ödenmiş</th>
                              <th>Ödenmemiş</th>
                              <th>Yaklaşan Taksit</th>
                              <th>İşlemler</th>
                           </tr>
                        </thead>
                        <tbody></tbody>
                     </table>
                  </div>
                  <div class="tab-pane fade show" id="gecikmis_taksit" role="tab-panel" style="margin-top: 20px;">
                     <table class="data-table table stripe hover nowrap" id="gecikmis_taksitler">
                        <thead>
                           <tr>
                              <th>Durum</th>
                              <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                              <th>Vade Sayısı</th>
                              <th>Ödenmiş</th>
                              <th>Ödenmemiş</th>
                              <th>Yaklaşan Taksit</th>
                              <th>İşlemler</th>
                           </tr>
                        </thead>
                        <tbody></tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

     

<script>
    var dataTablesInstances = {};
    var activeTableId = null;
    var tumAdisyonAcikSayisi = 0;
    var tumAdisyonKapaliSayisi = 0;
    var toplamAdisyonSayisi = 0;
 // Özet bilgileri güncelleme fonksiyonu
    function updateSummaryInfo(response) {
        if (!response) return;
        
        // Tüm tutarları güncelle
        $('#toplamSatisTutar').text(response.toplamSatis ? response.toplamSatis + ' ₺' : '0,00 ₺');
        $('#odenenToplamTutar').text(response.odenen ? response.odenen + ' ₺' : '0,00 ₺');
        $('#kalanToplamTutar').text(response.kalan ? response.kalan + ' ₺' : '0,00 ₺');
        
        // Açık/Kapalı sayılarını güncelle
        var acikSayi = response.acikAdisyonSayisi || 0;
        var kapaliSayi = response.kapaliAdisyonSayisi || 0;
        
        $('#acikAdisyonSayisi').text(acikSayi);
        $('#kapaliAdisyonSayisi').text(kapaliSayi);
        
        // Tüm adisyon sayılarını sakla (filtre değişikliklerinde kullanmak için)
        if (response.tumAdisyonAcikSayisi !== undefined) {
            tumAdisyonAcikSayisi = response.tumAdisyonAcikSayisi;
        }
        if (response.tumAdisyonKapaliSayisi !== undefined) {
            tumAdisyonKapaliSayisi = response.tumAdisyonKapaliSayisi;
        }
        if (response.toplamAdisyonSayisi !== undefined) {
            toplamAdisyonSayisi = response.toplamAdisyonSayisi;
        }
        
        // Satış durumu filtresi yanındaki sayıları güncelle
        updateSalesStatusCounts();
        
        // Tablardaki sayıları güncelle
        if (response.islemsayisi !== undefined) {
            $('#satis_takibi_islem_sayisi').text('(' + response.islemsayisi + ')');
        }
        if (response.urunsayisi !== undefined) {
            $('#satis_takibi_urun_sayisi').text('(' + response.urunsayisi + ')');
        }
        if (response.paketsayisi !== undefined) {
            $('#satis_takibi_paket_sayisi').text('(' + response.paketsayisi + ')');
        }
        
        // Tümü tabı için toplam sayı
        var toplamSayi = (response.recordsTotal || response.recordsFiltered || 0);
        $('#satis_takibi_tumu_sayisi').text('(' + toplamSayi + ')');
    }
    
    // Satış durumu filtresi yanındaki sayıları güncelleme fonksiyonu
    function updateSalesStatusCounts() {
        var selectedStatus = $('#satis_durumu_filtre').val();
        var acikText = '', kapaliText = '', tumText = '';
        
        // Eğer tüm satışlar seçiliyse, tüm sayıları göster
        if (selectedStatus === '' || selectedStatus === null) {
            acikText = tumAdisyonAcikSayisi;
            kapaliText = tumAdisyonKapaliSayisi;
            tumText = toplamAdisyonSayisi;
            $('#satisDurumuSayilari').html(
                '<span class="badge badge-success ml-1" style="font-size: 10px;">Açık: ' + acikText + '</span>' +
                '<span class="badge badge-danger ml-1" style="font-size: 10px;">Kapalı: ' + kapaliText + '</span>' +
                '<span class="badge badge-info ml-1" style="font-size: 10px;">Toplam: ' + tumText + '</span>'
            );
        } 
        // Eğer açık satışlar seçiliyse, sadece açık sayısını göster
        else if (selectedStatus === 'acik') {
            acikText = tumAdisyonAcikSayisi;
            $('#satisDurumuSayilari').html(
                '<span class="badge badge-success ml-1" style="font-size: 10px;">Açık: ' + acikText + '</span>'
            );
        } 
        // Eğer kapalı satışlar seçiliyse, sadece kapalı sayısını göster
        else if (selectedStatus === 'kapali') {
            kapaliText = tumAdisyonKapaliSayisi;
            $('#satisDurumuSayilari').html(
                '<span class="badge badge-danger ml-1" style="font-size: 10px;">Kapalı: ' + kapaliText + '</span>'
            );
        }
    }
    
    // Personel seçimi başlatma
    function initializePersonelSelect() {
        var urlParams = new URLSearchParams(window.location.search);
        var sube = urlParams.get('sube') || '';
        
        $.ajax({
            url: '/isletmeyonetim/personel_listesi_getir',
            type: 'GET',
            data: { sube: sube },
            success: function(response) {
                var select = $('#satisPersonelFiltre');
                select.empty();
                select.append('<option value="">Tüm Personeller</option>');
                
                $.each(response, function(index, personel) {
                    select.append('<option value="' + personel.id + '">' + personel.personel_adi + '</option>');
                });
                
                select.select2({
                    placeholder: "Personel seçin",
                    allowClear: true,
                    width: '100%'
                });
            },
            error: function(xhr, status, error) {
                console.error('Personel listesi yüklenirken hata:', error);
                $('#satisPersonelFiltre').html('<option value="">Tüm Personeller</option>');
            }
        });
    }
     // Tab ID'sine göre tablo ID'sini bul
    function getTableIdFromTab(tabId) {
        var tableMap = {
            'tum_adisyonlar': '#adisyon_liste',
            'hizmet_adisyonlar': '#adisyon_liste_hizmet',
            'ürün_adisyonlar': '#adisyon_liste_urun',
            'paket_adisyonlar': '#adisyon_liste_paket',
            'tum_taksit': '#tum_taksitler',
            'acik_taksit': '#acik_taksitler',
            'kapali_taksit': '#kapali_taksitler',
            'gecikmis_taksit': '#gecikmis_taksitler'
        };
        
        if (tabId && tabId.startsWith('#')) {
            tabId = tabId.substring(1);
        }
        
        return tableMap[tabId];
    }
    
    // Filtreleri uygulama fonksiyonu
    function applyFilters() {
        var personelId = $('#satisPersonelFiltre').val() || '';
        var selectedRange = $('#satis_zamana_gore_filtre').val();
        var satisDurumu = $('#satis_durumu_filtre').val() || '';
        
        var startDate, endDate;
        
        if (selectedRange === 'ozel') {
            startDate = $('#satisbaslangictarihi').val();
            endDate = $('#satisbitistarihi').val();
            
            if (!startDate || !endDate) {
                var today = new Date();
                startDate = today.toISOString().split('T')[0];
                endDate = startDate;
            }
        } else {
            var dates = selectedRange.split(' / ');
            startDate = dates[0];
            endDate = dates[1];
        }
        
        if (!isValidDate(startDate) || !isValidDate(endDate)) {
            console.error('Geçersiz tarih formatı');
            return;
        }
        
        if (activeTableId) {
            refreshSpecificTable(activeTableId, personelId, startDate, endDate, satisDurumu);
        }
    }
    
    // Belirli bir tabloyu yenile
    function refreshSpecificTable(tableId, personelId, startDate, endDate, satisDurumu) {
        // Eğer parametreler verilmediyse, UI'dan al
        if (!personelId && !startDate && !endDate && !satisDurumu) {
            personelId = $('#satisPersonelFiltre').val() || '';
            var selectedRange = $('#satis_zamana_gore_filtre').val();
            satisDurumu = $('#satis_durumu_filtre').val() || '';
            
            if (selectedRange === 'ozel') {
                startDate = $('#satisbaslangictarihi').val();
                endDate = $('#satisbitistarihi').val();
                if (!startDate || !endDate) {
                    var today = new Date();
                    startDate = today.toISOString().split('T')[0];
                    endDate = startDate;
                }
            } else {
                var dates = selectedRange.split(' / ');
                startDate = dates[0];
                endDate = dates[1];
            }
        }
        
        // Tablo türünü belirle
        var tur = 0; // Varsayılan: Tümü
        switch(tableId) {
            case '#adisyon_liste_hizmet':
                tur = 1;
                break;
            case '#adisyon_liste_urun':
                tur = 3;
                break;
            case '#adisyon_liste_paket':
                tur = 2;
                break;
        }
        
        // Tablo zaten başlatılmış mı kontrol et ve temizle
        if (dataTablesInstances[tableId]) {
            try {
                // Önce responsive'yi temizle
                if (dataTablesInstances[tableId].responsive) {
                    dataTablesInstances[tableId].responsive.destroy();
                }
                
                // DataTable instance'ını tamamen yok et
                dataTablesInstances[tableId].clear().destroy();
            } catch (e) {
                console.log('Temizleme hatası:', e);
            }
            
            delete dataTablesInstances[tableId];
            
            // Tabloyu tamamen temizle
            $(tableId).empty();
            
            // Orijinal HTML yapısını geri yükle
            restoreTableHTML(tableId);
        }
        
        // Yeni instance oluştur
        initializeDataTable(tableId, tur, personelId, startDate, endDate, satisDurumu);
    }
    
    // Tablo HTML yapısını geri yükle
    function restoreTableHTML(tableId) {
        var isTaksitTable = tableId.includes('taksit');
        var originalHTML = '';
        
        if (isTaksitTable) {
            originalHTML = `
                <thead>
                    <tr>
                        <th>Durum</th>
                        <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                        <th>Vade Sayısı</th>
                        <th>Ödenmiş</th>
                        <th>Ödenmemiş</th>
                        <th>Yaklaşan Taksit</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
        } else {
            originalHTML = `
                <thead>
                    <tr>
                        <th>Durum</th>
                        <th>Satış Tarihi</th>
                        <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                        <th>Yaklaşan Ödeme Tarihi</th>
                        <th>Adisyon İçeriği</th>
                        <th>Toplam ₺</th>
                        <th>Ödenen ₺</th>
                        <th>Kalan ₺</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
        }
        
        $(tableId).html(originalHTML);
    }
    
    // DataTable başlatma fonksiyonu
    function initializeDataTable(tableId, tur = 0, personelId = '', startDate = '', endDate = '', satisDurumu = '') {
        if(!tableId.includes('taksit'))
        {
            // UI'dan değerleri al
        if (personelId === '' && startDate === '' && endDate === '' && satisDurumu === '') {
            personelId = $('#satisPersonelFiltre').val() || '';
            var selectedRange = $('#satis_zamana_gore_filtre').val();
            satisDurumu = $('#satis_durumu_filtre').val() || '';
            
            if (selectedRange === 'ozel') {
                startDate = $('#satisbaslangictarihi').val();
                endDate = $('#satisbitistarihi').val();
                if (!startDate || !endDate) {
                    var today = new Date();
                    startDate = today.toISOString().split('T')[0];
                    endDate = startDate;
                }
            } else {
                var dates = selectedRange.split(' / ');
                startDate = dates[0];
                endDate = dates[1];
            }
        }
        
        var tarihAraligi = startDate + ' / ' + endDate;
        if (!startDate || !endDate) {
            var today = new Date();
            var defaultDate = today.toISOString().split('T')[0];
            tarihAraligi = defaultDate + ' / ' + defaultDate;
        }
        
        var urlParams = new URLSearchParams(window.location.search);
        var sube = urlParams.get('sube') || '';
        
        var isTaksitTable = tableId.includes('taksit');
        var ajaxUrl = isTaksitTable ? "/isletmeyonetim/taksit-filtreli-getir" : "/isletmeyonetim/adisyon-filtreli-getir";
        
        console.log('Tabloyu başlatıyorum:', {
            tableId: tableId,
            tur: tur,
            tarihAraligi: tarihAraligi,
            personelId: personelId,
            satisDurumu: satisDurumu
        });
        
        var table = $(tableId).DataTable({
            autoWidth: false,
            responsive: false, // Responsive'i kapat
            processing: true,
            serverSide: true,
            deferRender: true,
            destroy: true, // Her seferinde yeni instance
            ajax: {
                url: ajaxUrl,
                type: "GET",
                data: function(d) {
                    var data = {
                        sube: sube,
                        tur: tur,
                        musteri_id: '',
                        tariharaligi: tarihAraligi,
                        personel_id: personelId,
                        draw: d.draw,
                        start: d.start,
                        length: d.length,
                        search: { value: d.search.value },
                        order: d.order
                    };
                    
                    // Adisyon durumu parametresini ekle (sadece adisyon tabloları için)
                    if (!isTaksitTable && satisDurumu) {
                        data.adisyondurumu = satisDurumu;
                    }
                    
                    return data;
                },
                dataSrc: function(json) {
                    // Özet bilgileri güncelle
                    updateSummaryInfo(json);
                    
                    // DataTables'in beklediği formatta veri döndür
                    return json.data;
                },
                error: function(xhr, error, code) {
                    console.error('DataTables AJAX error for ' + tableId + ':', error);
                    $(tableId + ' tbody').html(
                        '<tr><td colspan="' + (isTaksitTable ? '7' : '9') + '" class="text-center">Veri yüklenirken bir hata oluştu.</td></tr>'
                    );
                }
            },
            columns: isTaksitTable ? [
                { data: 'durum' },
                { data: 'musteri' },
                { data: 'vade_sayisi' },
                { data: 'odenen' },
                { data: 'kalan' },
                { data: 'yaklasan_taksit' },
                { data: 'islemler' }
            ] : [
                { data: 'durum' },
                { data: 'acilis_tarihi' },
                { data: 'musteri' },
                { data: 'planlanan_alacak_tarihi' },
                { data: 'icerik' },
                { data: 'toplam' },
                { data: 'odenen' },
                { data: 'kalan_tutar' },
                { data: 'islemler' }
            ],
            order: isTaksitTable ? [[5, "asc"]] : [[1, "desc"]],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                searchPlaceholder: "Ara",
                paginate: {
                    next: '<i class="ion-chevron-right"></i>',
                    previous: '<i class="ion-chevron-left"></i>'  
                },
                processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Yükleniyor...</span></div> Yükleniyor...',
                emptyTable: "Tabloda veri bulunmamaktadır.",
                info: "_START_-_END_ arası _TOTAL_ kayıt",
                infoEmpty: "0-0 arası 0 kayıt",
                infoFiltered: "(_MAX_ kayıttan filtrelendi)",
                loadingRecords: "Yükleniyor...",
                zeroRecords: "Eşleşen kayıt bulunamadı."
            },
            initComplete: function(settings, json) {
                console.log(tableId + ' tablosu başarıyla yüklendi');
            }
        });
        
        dataTablesInstances[tableId] = table;
        return table;
        }
        
    }
    // Tarih formatını kontrol eden yardımcı fonksiyon
    function isValidDate(dateString) {
        var regEx = /^\d{4}-\d{2}-\d{2}$/;
        if (!dateString.match(regEx)) return false;
        var d = new Date(dateString);
        var dNum = d.getTime();
        if (!dNum && dNum !== 0) return false;
        return d.toISOString().slice(0, 10) === dateString;
    }
$(document).ready(function() {
   
    
   
    
    // Datepicker ayarları
    $('#satisbaslangictarihi, #satisbitistarihi').datepicker({
        maxDate: new Date(),
        language: "tr",
        autoClose: true,
        dateFormat: "yyyy-mm-dd",
        onSelect: function(selectedDate) {
            if ($('#satisbaslangictarihi').val() && $('#satisbitistarihi').val()) {
                setTimeout(function() {
                    applyFilters();
                }, 300);
            }
        }
    });
    
    // Filtre değişikliklerini dinle
    $('#satis_zamana_gore_filtre').on('change', function() {
        var selectedValue = $(this).val();
        
        if (selectedValue === 'ozel') {
            $('#satis_zamana_gore_filtre1, #satis_zamana_gore_filtre2').show();
        } else {
            $('#satis_zamana_gore_filtre1, #satis_zamana_gore_filtre2').hide();
            setTimeout(function() {
                applyFilters();
            }, 100);
        }
    });
    
    $('#satisbaslangictarihi, #satisbitistarihi').on('change', function() {
        if ($('#satisbaslangictarihi').val() && $('#satisbitistarihi').val()) {
            setTimeout(function() {
                applyFilters();
            }, 300);
        }
    });
    
    $('#satisPersonelFiltre').on('change', function() {
        applyFilters();
    });
    
    // Satış durumu filtresini dinle
    $('#satis_durumu_filtre').on('change', function() {
        // Önce sayıları güncelle
        updateSalesStatusCounts();
        // Sonra filtreleri uygula
        applyFilters();
    });
    
    // Tab değiştiğinde
    $('button[data-toggle="tab"], .nav-link[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).attr("href") || $(e.relatedTarget).attr("href");
        var tableId = getTableIdFromTab(target);
        
        if (tableId) {
            // Önceki tablonun responsive özelliklerini temizle
            if (activeTableId && dataTablesInstances[activeTableId]) {
                try {
                    // DataTables responsive instance'ını temizle
                    if (dataTablesInstances[activeTableId].responsive) {
                        dataTablesInstances[activeTableId].responsive.destroy();
                    }
                } catch (e) {
                    console.log('Responsive temizleme hatası:', e);
                }
            }
            
            // Yeni aktif tabloyu güncelle
            activeTableId = tableId;
            
            // Tab'ın görünür olmasını bekle
            setTimeout(function() {
                refreshSpecificTable(tableId);
            }, 50);
        }
    });
    
    // Sayfa yüklendiğinde
    setTimeout(function() {
        var activeTab = $('.tab-pane.active');
        if (activeTab.length) {
            var tableId = getTableIdFromTab(activeTab.attr('id'));
            if (tableId) {
                activeTableId = tableId;
                initializeDataTable(tableId);
            }
        }
        
        // Personel seçimini başlat
        initializePersonelSelect();
        
        // Satış durumu sayılarını başlangıçta güncelle
        updateSalesStatusCounts();
        
        // İlk filtreleme
        setTimeout(function() {
            applyFilters();
        }, 500);
    }, 300);
    
   
});
</script>
@endsection