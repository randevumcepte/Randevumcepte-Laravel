function kapat_button(){
    
    
    $('#ozel-tarih-araligi').attr('style','display:none');
    $('#rapor-filtre').attr('style','display:block');
    $('#rapor-baslik-kolon1').attr('class','col-sm-6 col-md-6 col-lg-6');
    $('#rapor-baslik-kolon2').attr('class','col-sm-6 col-md-6 col-lg-6');
     
}
function kapat_button2(){
    
    
    $('#ozel-tarih-araligi-anahtar-kelime').attr('style','display:none');
    $('#rapor-filtre').attr('style','display:block');
    $('#rapor-baslik-kolon1').attr('class','col-sm-6 col-md-6 col-lg-6');
    $('#rapor-baslik-kolon2').attr('class','col-sm-6 col-md-6 col-lg-6');
     
}
function kapat_button3(){
    
    
    $('#ozel-tarih-araligi-tum-dokum').attr('style','display:none');
    $('#rapor-filtre').attr('style','display:block');
    $('#rapor-baslik-kolon1').attr('class','col-sm-6 col-md-6 col-lg-6');
    $('#rapor-baslik-kolon2').attr('class','col-sm-6 col-md-6 col-lg-6');
     
}
$('#rapor-filtre-select').on('change',function(e){
    e.preventDefault();
    if($('#rapor-filtre-select').val()==3){
        
        $('#ozel-tarih-araligi').attr('style','display:block');
        $('#rapor-filtre').attr('style','display:none');

        $('#rapor-baslik-kolon1').attr('class','col-sm-4 col-md-4 col-lg-4');
        $('#rapor-baslik-kolon2').attr('class','col-sm-8 col-md-8 col-lg-8');
    }
    else{
        kapat_button();
        window.location.href= "/musteri/raporlar?&rapor_araligi="+$('#rapor-filtre-select').val();

    }
});
$('#anahtar-kelime-rapor-filtre-select').on('change',function(e){
    e.preventDefault();
    if($('#anahtar-kelime-rapor-filtre-select').val()==3){
        
        $('#ozel-tarih-araligi-anahtar-kelime').attr('style','display:block');
        $('#rapor-filtre').attr('style','display:none');

        $('#rapor-baslik-kolon1').attr('class','col-sm-4 col-md-4 col-lg-4');
        $('#rapor-baslik-kolon2').attr('class','col-sm-8 col-md-8 col-lg-8');
    }
    else{
        kapat_button2();
        window.location.href= "/musteri/kampanya-detaylari/"+$('#kampanya_id').val()+"?&rapor_araligi="+$('#anahtar-kelime-rapor-filtre-select').val();

    }
});
$('#tum-dokum-rapor-filtre-select').on('change',function(e){
    e.preventDefault();
    if($('#tum-dokum-rapor-filtre-select').val()==3){
        
        $('#ozel-tarih-araligi-tum-dokum').attr('style','display:block');
        $('#rapor-filtre').attr('style','display:none');

        $('#rapor-baslik-kolon1').attr('class','col-sm-4 col-md-4 col-lg-4');
        $('#rapor-baslik-kolon2').attr('class','col-sm-8 col-md-8 col-lg-8');
    }
    else{
        kapat_button3();
        window.location.href= "/musteri/hesap-detaylari?&rapor_araligi="+$('#tum-dokum-rapor-filtre-select').val();

    }
});

$('#ozel-tarih-araligi-anahtar-kelime').on('submit',function(e){
    e.preventDefault();
     window.location.href= "/musteri/kampanya-detaylari/"+$('#kampanya_id').val()+"?&rapor_araligi=CUSTOM_DATE&"+$('#ozel-tarih-araligi-anahtar-kelime').serialize();
 });
$('#ozel-tarih-araligi-tum-dokum').on('submit',function(e){
    e.preventDefault();
     window.location.href= "/musteri/hesap-detaylari/?&rapor_araligi=CUSTOM_DATE&"+$('#ozel-tarih-araligi-tum-dokum').serialize();
 });

$('#ozel-tarih-araligi').on('submit',function(e){
    e.preventDefault();
     window.location.href= "/musteri/raporlar?&rapor_araligi=CUSTOM_DATE&"+$('#ozel-tarih-araligi').serialize();
   /* $.ajax({
        type: "GET",
        url: '/musteri/rapor-indir',
        data:  $('#ozel-tarih-araligi').serialize(),
        dataType: "text",
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
               $('#preloader').hide();
                alert(result);
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $('#preloader').hide();
        }
    });*/
});
$('#ozel_aralik_kapat').click(function(e){
    e.preventDefault();
    kapat_button();
});
$('#hesap-bilgileri-formu').on('submit',function(e){
    e.preventDefault();
     $.ajax({
        type: "GET",
        url: '/reklam-yoneticisi/hesap-bilgileri-guncelle',
        data:  $('#hesap-bilgileri-formu').serialize(),
        dataType: "text",
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
               $('#preloader').hide();
               swal({
                        title: 'Başarılı',
                        text: 'Müşteri bilgileri başarı ile güncellendi',
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
             });
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $('#preloader').hide();
              swal({
                        title: 'Hata',
                        text: 'Müşteri bilgileri güncellenirken hata oluştu',
                        type: 'danger',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-danger',
                       confirmButtonText: 'Kapat',
             });
        }
    });
});
$('a[name="kampanya_baslat"]').click(function(e){
    e.preventDefault();
     if($('#kalan_butce').val()==0){
            swal({
                        title: 'Uyarı',
                        text: 'Kampanyayı aktif hale getirmeniz için yeterli bütçeniz bulunmamaktadır. Ödeme yap butonuna tıklayıp bütçe satın alarak aktif hale getirebilirsiniz.',
                        type: 'warning',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                        confirmButtonText: 'Ödeme Yap',
                         
                          
                    }).then(function(){
                window.location.href = '/musteri/havale-ile-odeme';
            });
 


                    
     }
     else{
        $.ajax({
        type: "GET",
        url: '/musteri/kampanya-durdur-baslat',
        data: {kampanya_id:$(this).attr('data-id'),baslat_durdur:1},
        dataType: "text",
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
                
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $('#preloader').hide();
        }
    });
     }
     

});
$('a[name="kampanya_duraklat"]').click(function(e){
    e.preventDefault();
       
     $.ajax({
        type: "GET",
        url: '/musteri/kampanya-durdur-baslat',
        data: {kampanya_id:$(this).attr('data-id'),baslat_durdur:0},
        dataType: "text",
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
               
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $('#preloader').hide();
        }
    });

});
$('#sikayet_memnuniyet_formu').on('submit',function(e){
    e.preventDefault();
   
    $.ajax({
        type: "POST",
        url: '/musteri/sikayet-memnuniyet-gonder',
        data:  $('#sikayet_memnuniyet_formu').serialize(),
        dataType: "text",
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
         $('#preloader').hide();
             $('#sikayet_memnuniyet_formu').trigger("reset");

             swal({
                        title: 'Tebrikler',
                        text: 'Anketimize katıldığınız için teşekkür ederiz',
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                        button: {
                            text: "Kapat",
                          }
             });
        },
        error: function (request, status, error) {
          
               swal({
                        title: 'Uyarı',
                        text: status + " " +error,
                        type: 'warning',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                        button:{
                            text: "Kapat",
                          }
                    });
                  document.getElementById('hata').innerHTML = request.responseText;
             $('#preloader').hide();
        }
    });
});
$('#sikayet_memnuniyet_formu_bayi').on('submit',function(e){
    e.preventDefault();
   
    $.ajax({
        type: "POST",
        url: '/satisortakligi/sikayet-memnuniyet-gonder',
        data:  $('#sikayet_memnuniyet_formu_bayi').serialize(),
        dataType: "text",
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
         $('#preloader').hide();
             $('#sikayet_memnuniyet_formu_bayi').trigger("reset");

             swal({
                        title: 'Tebrikler',
                        text: 'Anketimize katıldığınız için teşekkür ederiz',
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                        button: {
                            text: "Kapat",
                          }
             });
        },
        error: function (request, status, error) {
          
               swal({
                        title: 'Uyarı',
                        text: status + " " +error,
                        type: 'warning',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                        button:{
                            text: "Kapat",
                          }
                    });
                  document.getElementById('hata').innerHTML = request.responseText;
             $('#preloader').hide();
        }
    });
});
$('#havale_ile_odeme_butcesi').keyup(function(e){
     e.preventDefault();
    var kdvharic = 0;
     
    if($('#havale_ile_odeme_butcesi').val() != ""){
        kdvharic = $('#havale_ile_odeme_butcesi').val();
       
        $('#butce_kdv_dahil').val((parseFloat(kdvharic)+parseFloat(kdvharic)*0.18).toFixed(2));
    }
    else
        $('#butce_kdv_dahil').val(parseFloat(kdvharic).toFixed(2));
    
    /* $.ajax({
        type: "GET",
        url: '/reklam-butce-tahmin-araci',
        data:  {butce:$('#havale_ile_odeme_butcesi').val()},
        dataType: "json",
       
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
            $('#tahmini_ziyarteci_sayisi').empty();
            $('#tahmini_ziyarteci_sayisi').append(result.ziyaretci);
            $('#tahmini_gosterim_sayisi').empty();
            $('#tahmini_gosterim_sayisi').append(result.gosterim);
           
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
            
            
        }
    });*/
});
$('#havale-eft-fatura-bilgileri').on('submit',function(e){
    e.preventDefault();
    
    $.ajax({
        type: "POST",
        url: '/musteri/havale-eft-odeme-yap',
        data:  $('#havale-eft-fatura-bilgileri').serialize(),
        dataType: "text",
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             $('#havale-eft-fatura-bilgileri').trigger('reset');
               $('#preloader').hide();
                swal({
                        title: 'Ödeme kaydınız alındı',
                        text: 'Havale / Eft ödemesinin ekranda görünen hesap numarasına gerçekleştirilmesiyle ödeme tutarınız bakiyenize aktarılacaktır',
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
             });
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $('#preloader').hide();
             swal({
                        title: 'Uyarı',
                        text: status + " "+error,
                        type: 'warning',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                      confirmButtonText: 'Kapat',
             });
        }
    });
});
 
$('#profil_firma_bilgi').on('submit',function (e) {

    e.preventDefault();
     
    $.ajax({
        type: "POST",
        url: '/musteri/bilgileri-guncelle',
        data:  $('#profil_firma_bilgi').serialize(),
        dataType: "text",
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
               $('#preloader').hide();
                  swal({
                        title: 'Başarılı',
                        text: 'Bilgileriniz başarı ile güncellendi!',
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                        confirmButtonText: 'Kapat',
                       
             });
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $('#preloader').hide();
                 swal({
                        title: 'Uyarı',
                        text: status + " "+error,
                        type: 'warning',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                        confirmButtonText: 'Kapat',
             });
        }
    });

});
$('#profil_bayi_bilgi').on('submit',function (e) {

    e.preventDefault();
     
    $.ajax({
        type: "POST",
        url: '/satisortakligi/bilgileri-guncelle',
        data:  $('#profil_bayi_bilgi').serialize(),
        dataType: "text",
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
               $('#preloader').hide();
                  swal({
                        title: 'Başarılı',
                        text: result,
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                        confirmButtonText: 'Kapat',
                       
             });
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $('#preloader').hide();
                 swal({
                        title: 'Uyarı',
                        text: status + " "+error,
                        type: 'warning',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                        confirmButtonText: 'Kapat',
             });
        }
    });

});
$('#sifre_bilgi').on('submit',function (e) {
     e.preventDefault();
     $('#alert_box').empty();
     if($('#yeni_sifre').val()!= $('#yeni_sifre_tekrar').val()){
        $('#alert_box').append('    <div class="alert alert-danger alert-dismissible fade show" role="alert"> '+
                            '<span class="alert-icon">&times;</span>'+
                          '<span class="alert-text"><strong>Hata!</strong> Girdiğiniz yeni şifreler uyuşmamaktadır.</span>'+
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Kapat">'+
                             '<span aria-hidden="true">&times;</span>'+
                            '</button></div>');
        $('html, body').animate({ scrollTop:  $('#alert_box').offset().top }, 'slow');

     }
     else{
            $.ajax({
                type: "POST",
                url: '/musteri/sifre-guncelle',
                data:  $('#sifre_bilgi').serialize(),
                dataType: "text",
                
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {   
                     
                       $('#preloader').hide();
                       $('#alert_box').append(result);
                       $('html, body').animate({ scrollTop:  $('#alert_box').offset().top }, 'slow');
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML = request.responseText;
                     $('#preloader').hide();
                }
            });
     }
});
$('#sifre_bilgi_bayi').on('submit',function (e) {
     e.preventDefault();
     $('#alert_box').empty();
     if($('#yeni_sifre').val()!= $('#yeni_sifre_tekrar').val()){
        $('#alert_box').append('    <div class="alert alert-danger alert-dismissible fade show" role="alert"> '+
                            '<span class="alert-icon">&times;</span>'+
                          '<span class="alert-text"><strong>Hata!</strong> Girdiğiniz yeni şifreler uyuşmamaktadır.</span>'+
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Kapat">'+
                             '<span aria-hidden="true">&times;</span>'+
                            '</button></div>');
        $('html, body').animate({ scrollTop:  $('#alert_box').offset().top }, 'slow');

     }
     else{
            $.ajax({
                type: "POST",
                url: '/satisortakligi/sifre-guncelle',
                data:  $('#sifre_bilgi_bayi').serialize(),
                dataType: "text",
                
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {   
                     
                       $('#preloader').hide();
                       $('#alert_box').append(result);
                       $('html, body').animate({ scrollTop:  $('#alert_box').offset().top }, 'slow');
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML = request.responseText;
                     $('#preloader').hide();
                }
            });
     }
});
$('#resim_yukle_link').click(function (e) {
    e.preventDefault();
    $('#profil_resmi').trigger('click');
});
$('#profil_resmi').change(function (e){
    e.preventDefault();
    $('#profil_resmi_formu').trigger('submit');
});
$('#profil_resmi_formu').on('submit',function (e){
    e.preventDefault();
    document.getElementById('profil_resmi_gorsel').src = window.URL.createObjectURL($('#profil_resmi').get(0).files[0]);
      var formData = new FormData();
     formData.append('profil_resmi',$('#profil_resmi').get(0).files[0]);
     var other_data = $('#profil_resmi_form').serializeArray();
  
     $.each(other_data,function(key,input){
       
        formData.append(input.name,input.value);
    });
    $.ajax({
        type: "POST",
        url: '/musteri/profil-resmi-degistir',
        data:  formData,
        dataType: "text",
          contentType: false,        
        cache: false,             
        processData:false,   
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
               $('#preloader').hide();
            $('#')
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
            
            
        }
    });
});
$('#yeni_hesap_ekle').on('submit',function (e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/reklam-yoneticisi/yeni-hesap-ekle',
        data:  $('#yeni_hesap_ekle').serialize(),
        dataType: "text",
       
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
            $('#preloader').hide();
            window.location.href= '/reklam-yoneticisi/hesap-detaylari/'+result;
           
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = error;
            
            
        }
    });

});
$('#yeni_hesap_ekle_bayi').on('submit',function (e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/sistem-yoneticisi/yeni-bayi-hesabi-ekle',
        data:  $('#yeni_hesap_ekle_bayi').serialize(),
        dataType: "json",
       
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
            $('#preloader').hide();
             swal({
                        title: result.title,
                        text: result.mesaj,
                        type: result.type,
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
             });
           
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
             
            
        }
    });

});
$('#yeni_hesap_ekle_yetkili').on('submit',function (e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/sistem-yoneticisi/yeni-yetkili-hesabi-ekle',
        data:  $('#yeni_hesap_ekle_yetkili').serialize(),
        dataType: "json",
       
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
            $('#preloader').hide();
             swal({
                        title: result.title,
                        text: result.mesaj,
                        type: result.type,
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
             });
           
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
             
            
        }
    });

});
$('#fatura-bilgileri').on('submit',function(e){
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/musteri/fatura-bilgi-guncelle',
        data:  $('#fatura-bilgileri').serialize(),
        dataType: "text",
       
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
           
             $('#preloader').hide();
            $('#bilgi-basarı-ile-guncellendi-bildirim').trigger('click');
           
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
            
            
        }
    });
});
$('#havale_ile_odeme_banka_bilgi').change(function(e){
    e.preventDefault();
     
     $.ajax({
        type: "GET",
        url: '/musteri/banka-bilgi-getir',
        data:  {banka_id:$('#havale_ile_odeme_banka_bilgi').val()},
        dataType: "json",
       
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
           
             $('#preloader').hide();
             $('#banka_sube_kodu').empty();
             $('#banka_hesap_no').empty();
             $('#banka_iban').empty();
             $('#alici_adi').empty();
             $('#banka_sube_kodu').append(result.sube_kodu);
             $('#banka_hesap_no').append(result.hesap_no);
             $('#banka_iban').append(result.iban);
             $('#alici_adi').append(result.alici_adi);
             $('#banka_logo').attr('src','/'+result.banka_logo);
           
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
            
            
        }
    }
    );
});
$('#ozel_randevu').on('submit',function (e) {

    e.preventDefault();
  
     $.ajax({
        type: "post",
        url: '/musteri/ozel-randevu-talebi',
        data:  $('#ozel_randevu').serialize(),
        dataType: "text",

       
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             console.log(result);
           
             $('#preloader').hide();
             if(result=="Randevu talebiniz hesap uzmanınıza başarı ile iletildi")
               swal({
                        title: 'Başarılı',
                        text: result,
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
             });
            
                    else{
                          swal({
                        title: 'Hata',
                        text: 'Randevu talebiniz gönderilirken bir hata oluştu. Lütfen sistem yöneticisine başvurunuz. Hata mesajı : '+result,
                        type: 'error',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-danger',
                       confirmButtonText: 'Kapat',
             });
                   
             }
           
        },
        error: function (request, status, error) {
 document.getElementById('hata').innerHTML = request.responseText;
              $('#preloader').hide();
              swal({
                        title: 'Hata',
                        text: 'Mesajınız gönderilirken bir hata oluştu. Lütfen sistem yöneticisine başvurunuz! Hata mesajı : '+error,
                        type: 'danger',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-danger',
                       confirmButtonText: 'Kapat',
             });
            
        }
    }
    );
});


$('#teknik_analiz_talebi').on('submit',function (e) {

    e.preventDefault();
  
     $.ajax({
        type: "post",
        url: '/musteri/teknik-analiz-talebi',
        data:  $('#teknik_analiz_talebi').serialize(),
        dataType: "text",

       
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             console.log(result);
           
             $('#preloader').hide();
             if(result=="Teknik analiz talebiniz hesap uzmanınıza başarı ile iletildi")
               swal({
                        title: 'Başarılı',
                        text: result,
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
             });
              
             else{
                          swal({
                        title: 'Hata',
                        text: 'Teknik analiz talebiniz gönderilirken bir hata oluştu. Lütfen sistem yöneticisine başvurunuz. Hata mesajı : '+result,
                        type: 'error',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-danger',
                       confirmButtonText: 'Kapat',
             });
                   
             }
           
        },
        error: function (request, status, error) {
 document.getElementById('hata').innerHTML = request.responseText;
              $('#preloader').hide();
              swal({
                        title: 'Hata',
                        text: 'Mesajınız gönderilirken bir hata oluştu. Lütfen sistem yöneticisine başvurunuz! Hata Mesajı : '+error,
                        type: 'danger',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-danger',
                       confirmButtonText: 'Kapat',
             });
            
        }
    }
    );
});
$('#teknik_analiz_popup_ac').click(function(e){
    e.preventDefault();
      $.ajax({
        type: "GET",
        url: '/reklam-yoneticisi/teknik-analiz-detay-getir',
        data:  {teknik_analiz_id:$('#teknik_analiz_liste_id').val()},
        dataType: "json",
       
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
            
           
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
            
            
        }
    });
});
$('#yeni_musteri_ekle').on('submit',function(e){
    e.preventDefault();
    
    $.ajax({
        type: "POST",
        url: '/satisortakligi/yeni-musteri-ekle',
        data:  $('#yeni_musteri_ekle').serialize(),
        dataType: "text",

       
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
           
             $('#preloader').hide();
             
               swal({
                        title: 'Başarılı',
                        text: result,
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
             });
              
           
        },
        error: function (request, status, error) {
                
              $('#preloader').hide();
               document.getElementById('hata').innerHTML = request.responseText;
              swal({
                        title: 'Hata',
                        text: 'Bir hata oluştu. Lütfen sistem yöneticisine başvurunuz!'+'<br>'+error,
                        type: 'danger',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-danger',
                       confirmButtonText: 'Kapat',
             });
            
        }
    }
    );
});
$('#yeni_musteri_ekle_excel').on('submit',function(e){
    
    e.preventDefault();
    var liste=  $('#excel_dosyasi_yeni').get(0).files[0];
     
    var formData = new FormData();
    formData.append('excel_dosyasi_yeni',liste);
      
     
     $.ajax({
        type: "POST",
        url: '/satisortakligi/yeni-musteri-ekle-excel',
        
        dataType: "text",
        data : formData,
        contentType: false,        
        cache: false,             
        processData:false,   
           headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {  
         $('#preloader').hide();
           if (result=="Müşteriler sisteme başarı ile aktarıldı"){
                  swal({
                        title: 'Başarılı',
                        text: result,
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
             });
           }
          
            else if (result.indexOf("Integrity constraint violation")>-1)
            {
                 swal({
                        title: 'Uyarı',
                        text: "Excel dosyanızdaki e-mail adreslerinden biri sistemimizde mevcut. Lütfen kontrol ediniz!",
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                       confirmButtonText: 'Kapat',
                });
            }
            else
            {
                alert(result);
            }
               
        

           
            
             
            
        },
        error: function (request, status, error) {
            $('#preloader').hide();
           

             document.getElementById('hata').innerHTML = request.responseText;
             
        }

    });
 
});

$('#son_eklenen_musteriler').on('click','button[name="musteri_temsilcisi_atama"]',function(e){
    e.preventDefault();
 
    $('#atanacak_firma_unvani').empty();
     $('#atanacak_firma_yetkili').empty();
     $('#atanacak_musteri_id').val($(this).attr('data-value'));
     $('span[id="firma_unvani_'+$(this).attr('data-value')+'"]').clone().appendTo('#atanacak_firma_unvani');
      $('span[id="firma_yetkili_'+$(this).attr('data-value')+'"]').clone().appendTo('#atanacak_firma_yetkili');
      

});
$('#musteri_temsilcine_ata').on('submit',function(e){
    e.preventDefault();

    $.ajax({
        type: "POST",
        url: '/sistem-yoneticisi/musteri-temsilcisi-atama',
        
        dataType: "text",
        data : $('#musteri_temsilcine_ata').serialize(),
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {  
         $('#preloader').hide();
            
            $('#datatable-basic').DataTable().destroy();
            $('#son_eklenen_musteriler').empty();
            $('#son_eklenen_musteriler').append(result);
            $('#datatable-basic').DataTable(
                { 
                    "order": [[ 0, "desc" ]],
                    "language" : {

                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",

                                  searchPlaceholder: "Ara",

                                  paginate: {

                                      next: '<i class="ion-chevron-right"></i>',

                                      previous: '<i class="ion-chevron-left"></i>'  

                                  }

                            },
                }
            );
            swal({
                        title: 'Başarılı',
                        text: "Müşteri temsilcisine atama başarı ile gerçekleşti",
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                       confirmButtonText: 'Kapat',
                       timer:3000
            });
        

           
            
             
            
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             swal({
                        title: 'Hata',
                        text: "Bir hata oluştu! Lütfen teknik ekibe başvurunuz!",
                        type: 'danger',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                       confirmButtonText: 'Kapat',
                       timer:3000
                });

             document.getElementById('hata').innerHTML = request.responseText;
             
        }

    });
});
$('#musteri_bilgileri_tablo').on('click','button[name="musteri_bilgileri_duzenleme"],button[name="musteri_telefon_randevusu_olustur"]',function(e){
    e.preventDefault();
    form_id = $('input[name="form_id"][data-value="'+$(this).attr('data-value')+'"]').val();
    $.ajax({
        type: "GET",
        url: '/musteri-temsilcisi/musteri-detaylari',
        data : {musteri_id:$(this).attr('data-value')},
        dataType: "json",
        
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {  
            $('#preloader').hide();
            $('#form_islemleri_form_id').val(form_id);
            $('#form_islemleri_musteri_id').val(result.id);
            $('#form_islemleri_firma_unvani').val(result.firma_unvani);
            $('#form_islemleri_yetkili_kisi').val(result.ad_soyad);
            $('#form_islemleri_email').val(result.email);
            $('#form_islemleri_telefon').val(result.telefon);
            $('#form_islemleri_gsm1').val(result.gsm_1);
            $('#form_islemleri_gsm2').val(result.gsm_2);
            $('#form_islemleri_website').val(result.web_sitesi);
            $('#form_islemleri_adres').val(result.adres);
            $('#form_islemleri_vergi_dairesi').val(result.vergi_dairesi);
            $('#form_islemleri_vergi_tc_no').val(result.vergi_tc_no);
            
             $('#form_islemleri_form_id2').val(form_id);
            $('#form_islemleri_musteri_id2').val(result.id);
            $('#form_islemleri_firma_unvani2').val(result.firma_unvani);
            $('#form_islemleri_yetkili_kisi2').val(result.ad_soyad);
            $('#form_islemleri_email2').val(result.email);
            $('#form_islemleri_telefon2').val(result.telefon);
            $('#form_islemleri_gsm12').val(result.gsm_1);
            $('#form_islemleri_gsm22').val(result.gsm_2);
            $('#form_islemleri_website2').val(result.web_sitesi);
            $('#form_islemleri_adres2').val(result.adres);
            $('#form_islemleri_vergi_dairesi2').val(result.vergi_dairesi);
            $('#form_islemleri_vergi_tc_no2').val(result.vergi_tc_no);
        

           
            
             
            
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             swal({
                        title: 'Hata',
                        text: "Bir hata oluştu! Lütfen teknik ekibe başvurunuz!",
                        type: 'danger',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                       confirmButtonText: 'Kapat',
                });

             document.getElementById('hata').innerHTML = request.responseText;
             
        }

    });
});
$('#musteri_bilgileri_tablo').on('click','button[name="musteri_ilgilenmiyor"]',function(e){
    e.preventDefault();
    
    $.ajax({
        type: "GET",
        url: '/musteri-temsilcisi/durum-kaydet',
        data : {form_id:$(this).attr('data-value'),durum:5},
        dataType: "text",
        
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {  
            $('#preloader').hide();
            window.location.href = '/musteri-temsilcisi/yeni-atanan-musteriler';
            
             
            
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             swal({
                        title: 'Hata',
                        text: "Bir hata oluştu! Lütfen teknik ekibe başvurunuz!",
                        type: 'danger',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                       confirmButtonText: 'Kapat',
                });

             document.getElementById('hata').innerHTML = request.responseText;
             
        }

    });
});
$('#musteri_bilgileri_tablo').on('click','button[name="musteri_yanlis_numara"]',function(e){
    e.preventDefault();
    
    $.ajax({
        type: "GET",
        url: '/musteri-temsilcisi/durum-kaydet',
        data : {form_id:$(this).attr('data-value'),durum:4},
        dataType: "text",
        
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {  
            $('#preloader').hide();
            window.location.href = '/musteri-temsilcisi/yeni-atanan-musteriler';
            
             
            
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             swal({
                        title: 'Hata',
                        text: "Bir hata oluştu! Lütfen teknik ekibe başvurunuz!",
                        type: 'danger',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                       confirmButtonText: 'Kapat',
                });

             document.getElementById('hata').innerHTML = request.responseText;
             
        }

    });
});
$('#musteri_bilgileri_tablo').on('click','button[name="musteri_ulasilamiyor"]',function(e){
    e.preventDefault();
    
    $.ajax({
        type: "GET",
        url: '/musteri-temsilcisi/durum-kaydet',
        data : {form_id:$(this).attr('data-value'),durum:3},
        dataType: "text",
        
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {  
            $('#preloader').hide();
            window.location.href = '/musteri-temsilcisi/yeni-atanan-musteriler';
            
             
            
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             swal({
                        title: 'Hata',
                        text: "Bir hata oluştu! Lütfen teknik ekibe başvurunuz!",
                        type: 'danger',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                       confirmButtonText: 'Kapat',
                });

             document.getElementById('hata').innerHTML = request.responseText;
             
        }

    });
});
$('#musteri-leads').on('submit',function(e){
    e.preventDefault();
   
     $.ajax({
        type: "POST",
        url: '/musteri-temsilcisi/formu-kaydet',
        data : $('#musteri-leads').serialize(),
        dataType: "json",
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {  
            $('#preloader').hide();
             swal({
                        title: 'Başarılı',
                        text: "Müşteri bilgileri başarı ile güncellendi!",
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
             });
             $('#datatable-basic').DataTable().destroy();
              var table = $('#datatable-basic').DataTable({
                        
                        serverSide:false,
                         columns:[
                            { data: 'sira_no' },
                            { data: 'firma_unvani_yetkili_ad_soyad' },
                            { data: 'bayi' }, 
                            { data: 'bayi_notu' },
                            { data: 'islemler' },
                            
                       ],
                       data: result,
 "language" : {
           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json"
        },
            });
         
            
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             swal({
                        title: 'Hata',
                        text: "Bir hata oluştu! Lütfen teknik ekibe başvurunuz!",
                        type: 'error',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                       confirmButtonText: 'Kapat',
                });

             document.getElementById('hata').innerHTML = request.responseText;
             
        }

    }); 

});
$('#musteri_bilgileri_tablo').on('click','button[name="satis_onayi"]',function(e){
    e.preventDefault();
    formid = $(this).attr('data-value');
    $.ajax({
        type: "GET",
        url: '/sistem-yoneticisi/satis-onayi',
        data : {form_id:$(this).attr('data-value'),durum:7},
        dataType: "text",
        
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {  
            $('#preloader').hide();
            $('div[name="satis_durumu_bildirim"][data-value="'+formid+'"]').empty();
            $('div[name="satis_durumu_bildirim"][data-value="'+formid+'"]').append(result);
             
            
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             swal({
                        title: 'Hata',
                        text: "Bir hata oluştu! Lütfen teknik ekibe başvurunuz!",
                        type: 'danger',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-warning',
                       confirmButtonText: 'Kapat',
                });

             document.getElementById('hata').innerHTML = request.responseText;
             
        }

    });



});
$('#odeme_talep_et_button').click(function(e){
    e.preventDefault();
    
    $('#hakedis_miktari_text').empty();
    document.getElementById('hakedis_miktari_text').innerHTML = $('#bayi_guncel_hakedis').attr('data-value') +' ₺';
    $('#hakedis_miktari').val($('#bayi_guncel_hakedis').attr('data-value'));
   
});
$('#odeme_talep_et_formu').on('submit',function(e){
    e.preventDefault();
     var formData = new FormData();
     formData.append('komisyon_fatura_gider_pusulasi_belge',$('#komisyon_fatura_gider_pusulasi').get(0).files[0]);
    var other_data = $('#odeme_talep_et_formu').serializeArray();
  
     $.each(other_data,function(key,input){
       
        formData.append(input.name,input.value);
    });
    $.ajax({
        type: "POST",
        url: '/satisortakligi/odeme-talebi-gonder',
        data:  formData,
        dataType: "text",
        contentType: false,        
        cache: false,             
        processData:false,   
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
               $('#preloader').hide();
                swal({
                        title: 'Başarılı',
                        text: result,
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
             });
            window.location.href ='/satisortakligi/musteriler';

            
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             swal({
                        title: 'Hata',
                        text: 'Bir hata oluştu. Lütfen teknik ekibe başvurunuz',
                        type: 'error',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-danger',
                       confirmButtonText: 'Kapat',
             });
             document.getElementById('hata').innerHTML = request.responseText;
            
            
        }
    });
});

$('#forma_hizmetler_ekle').click(function(e){
     

    $("#forma_eklenecek_hizmet_secimi :selected").each(function(){
        if($(this).val()== "1"&& $("#form_satir_google_reklamlari").length == 0){

            $('#form_hizmetler_tablosu').append('<tr id="form_satir_google_reklamlari">'
                                     +'<td><a href="#" id="google_reklamlari_formdan_kaldir" style="font-size:20px;color:#ff0000"><i class="fa fa-times-circle"></i></a></td>'
                                
                                    +'<td>'
                                     +' <label>Google Reklamları</label>'
                                    +'</td> '
                                   +'<td>'
                                     
                                     +'<input type="text" class="form-control" name="suresiz_hizmet_aciklama_google_reklamlari">'
                                    
                                       
                                    +'</td> '
                                    +'<td>'
                                     
                                      +' <div class="input-group input-group-merge"><input id="ucret_google_reklamlari" type="text" name="ucret_google_reklamlari" class="form-control">'
                                     +'<div class="input-group-append">'
                                      +'<span class="input-group-text"><small class="font-weight-bold">₺</small></span>'
                                      +' </div></div>'
                                   +'</td>'
                                    +'<td><span id="kdv_dahil_ucret_google_reklamlari">0 ₺</span></td>'
                                      +'</tr>');
        }
         if($(this).val()== "2"&& $("#form_satir_facebook_reklamlari").length == 0){

            $('#form_hizmetler_tablosu').append('<tr id="form_satir_facebook_reklamlari">'
                                     +'<td><a href="#" id="facebook_reklamlari_formdan_kaldir" style="font-size:20px;color:#ff0000"><i class="fa fa-times-circle"></i></a></td>'
                                
                                    +'<td>'
                                     +' <label>Facebook Reklamları</label>'
                                    +'</td> '
                                   +'<td>'
                                     
                                     +'<input type="text" class="form-control" name="suresiz_hizmet_aciklama_facebook_reklamlari">'
                                    
                                       
                                    +'</td> '
                                    +'<td>'
                                       
                                      +' <div class="input-group input-group-merge"><input id="ucret_facebook_reklamlari" type="text" name="ucret_facebook_reklamlari" class="form-control">' 
                                      +'<div class="input-group-append">'
                                      +'<span class="input-group-text"><small class="font-weight-bold">₺</small></span>'
                                      +' </div></div>'
                                     
                                   +'</td>'
                                    +'<td><span id="kdv_dahil_ucret_facebook_reklamlari">0 ₺</span></td>'
                                      +'</tr>');
        }
         if($(this).val()== "3"&& $("#form_satir_instagram_reklamlari").length == 0){

            $('#form_hizmetler_tablosu').append('<tr id="form_satir_instagram_reklamlari">'
                                     +'<td><a href="#" id="instagram_reklamlari_formdan_kaldir" style="font-size:20px;color:#ff0000"><i class="fa fa-times-circle"></i></a></td>'
                                
                                    +'<td>'
                                     +' <label>Instagram Reklamları</label>'
                                    +'</td> '
                                   +'<td>'
                                   
                                     +'<input type="text" class="form-control" name="suresiz_hizmet_aciklama_instagram_reklamlari">'
                                    
                                       
                                    +'</td> '
                                    +'<td>'
                                    
                                      +' <div class="input-group input-group-merge"><input id="ucret_instagram_reklamlari" type="text" name="ucret_instagram_reklamlari" class="form-control"> '
                                     +'<div class="input-group-append">'
                                      +'<span class="input-group-text"><small class="font-weight-bold">₺</small></span>'
                                      +' </div></div>'
                                   +'</td>'
                                    +'<td><span id="kdv_dahil_ucret_instagram_reklamlari">0 ₺</span></td>'
                                      +'</tr>');
        }
         if($(this).val()== "4"&& $("#form_satir_produksiyon").length == 0){

            $('#form_hizmetler_tablosu').append('<tr id="form_satir_produksiyon">'
                                     +'<td><a href="#" id="produksiyon_formdan_kaldir" style="font-size:20px;color:#ff0000"><i class="fa fa-times-circle"></i></a></td>'
                                
                                    +'<td>'
                                     +' <label>Prodüksiyon</label>'
                                    +'</td> '
                                   +'<td>'
                                      
                                      +'<select name="sure_secimi_produksiyon" class="form-control">'
                                           +' <option value="1">1 Ay</option> ' 
                                          +'  <option value="2">3 Ay</option>  '
                                           +' <option value="3">6 Ay</option>  '
                                          +'  <option value="4">1 Yıl</option>  '
                                           +' <option value="5">2 Yıl</option>'
                                          +'  <option value="6">3 Yıl</option> '
                                          +'  <option value="10">3.5 Yıl</option> '
                                          +'  <option value="7">4 Yıl</option>'
                                          +'  <option value="8">5 Yıl</option>'
                                          +'  <option value="9">5+1 Yıl</option> '
                                       +' </select> '
                                  
                                       
                                    +'</td> '
                                    +'<td>'
                                     
                                      +' <div class="input-group input-group-merge"><input id="ucret_produksiyon" type="text" name="ucret_produksiyon" class="form-control"> '
                                     +'<div class="input-group-append">'
                                      +'<span class="input-group-text"><small class="font-weight-bold">₺</small></span>'
                                      +' </div></div>'
                                   +'</td>'
                                    +'<td><span id="kdv_dahil_ucret_produksiyon">0 ₺</span></td>'
                                      +'</tr>');
        }
         if($(this).val()== "5"&& $("#form_satir_sosyal_medya").length == 0){

            $('#form_hizmetler_tablosu').append('<tr id="form_satir_sosyal_medya">'
                                     +'<td><a href="#" id="sosyal_medya_formdan_kaldir" style="font-size:20px;color:#ff0000"><i class="fa fa-times-circle"></i></a></td>'
                                
                                    +'<td>'
                                     +' <label>Sosyal Medya Yönetimi</label>'
                                    +'</td> '
                                   +'<td>'
                                     
                                       +'<select name="sure_secimi_sosyal_medya" class="form-control">'
                                           +' <option value="1">1 Ay</option> ' 
                                          +'  <option value="2">3 Ay</option>  '
                                           +' <option value="3">6 Ay</option>  '
                                          +'  <option value="4">1 Yıl</option>  '
                                           +' <option value="5">2 Yıl</option>'
                                          +'  <option value="6">3 Yıl</option> '
                                          +'  <option value="10">3.5 Yıl</option> '
                                          +'  <option value="7">4 Yıl</option>'
                                          +'  <option value="8">5 Yıl</option>'
                                          +'  <option value="9">5+1 Yıl</option> '
                                       +' </select> '
                                   
                                       
                                    +'</td> '
                                    +'<td>'
                                      
                                      +' <div class="input-group input-group-merge"><input id="ucret_sosyal_medya" type="text" name="ucret_sosyal_medya" class="form-control"> '
                                     +'<div class="input-group-append">'
                                      +'<span class="input-group-text"><small class="font-weight-bold">₺</small></span>'
                                      +' </div></div>'
                                   +'</td>'
                                    +'<td><span id="kdv_dahil_ucret_sosyal_medya">0 ₺</span></td>'
                                      +'</tr>');
        }
         if($(this).val()== "6"&& $("#form_satir_google_local_seo").length == 0){

            $('#form_hizmetler_tablosu').append('<tr id="form_satir_google_local_seo">'
                                     +'<td><a href="#" id="google_local_seo_formdan_kaldir" style="font-size:20px;color:#ff0000"><i class="fa fa-times-circle"></i></a></td>'
                                
                                    +'<td>'
                                     +' <label>Google Local SEO</label>'
                                    +'</td> '
                                   +'<td>'
                                      
                                       +'<select name="sure_secimi_google_local_seo" class="form-control">'
                                           +' <option value="1">1 Ay</option> ' 
                                          +'  <option value="2">3 Ay</option>  '
                                           +' <option value="3">6 Ay</option>  '
                                          +'  <option value="4">1 Yıl</option>  '
                                           +' <option value="5">2 Yıl</option>'
                                          +'  <option value="6">3 Yıl</option> '
                                          +'  <option value="10">3.5 Yıl</option> '
                                          +'  <option value="7">4 Yıl</option>'
                                          +'  <option value="8">5 Yıl</option>'
                                          +'  <option value="9">5+1 Yıl</option> '
                                       +' </select> '
                                    
                                       
                                    +'</td> '
                                    +'<td>'
                                      
                                      +' <div class="input-group input-group-merge"><input id="ucret_google_local_seo" type="text" name="ucret_google_local_seo" class="form-control"> '
                                     +'<div class="input-group-append">'
                                      +'<span class="input-group-text"><small class="font-weight-bold">₺</small></span>'
                                      +' </div></div>'
                                   +'</td>'
                                    +'<td><span id="kdv_dahil_ucret_google_local_seo">0 ₺</span></td>'
                                      +'</tr>');
        }
        if($(this).val()== "7"&& $("#form_satir_google_maps").length == 0){

            $('#form_hizmetler_tablosu').append('<tr id="form_satir_google_maps">'
                                     +'<td><a href="#" id="google_maps_formdan_kaldir" style="font-size:20px;color:#ff0000"><i class="fa fa-times-circle"></i></a></td>'
                                
                                    +'<td>'
                                     +' <label>Goolge Maps My Business Harita Kaydı</label>'
                                    +'</td> '
                                   +'<td>'
                                 
                                       +'<select name="sure_secimi_google_maps" class="form-control">'
                                           +' <option value="1">1 Ay</option> ' 
                                          +'  <option value="2">3 Ay</option>  '
                                           +' <option value="3">6 Ay</option>  '
                                          +'  <option value="4">1 Yıl</option>  '
                                           +' <option value="5">2 Yıl</option>'
                                          +'  <option value="6">3 Yıl</option> '
                                          +'  <option value="10">3.5 Yıl</option> '
                                          +'  <option value="7">4 Yıl</option>'
                                          +'  <option value="8">5 Yıl</option>'
                                          +'  <option value="9">5+1 Yıl</option> '
                                       +' </select> '
                                    
                                       
                                    +'</td> '
                                    +'<td>'
                                     
                                      +' <div class="input-group input-group-merge"><input id="ucret_google_maps" type="text" name="ucret_google_maps" class="form-control"> '
                                      +'<div class="input-group-append">'
                                      +'<span class="input-group-text"><small class="font-weight-bold">₺</small></span>'
                                      +' </div></div>'
                                   +'</td>'
                                    +'<td><span id="kdv_dahil_ucret_google_maps">0 ₺</span></td>'
                                      +'</tr>');
        }
         if($(this).val()== "8"&& $("#form_satir_kurumsal_kimlik_tasarimi").length == 0){

            $('#form_hizmetler_tablosu').append('<tr id="form_satir_kurumsal_kimlik_tasarimi">'
                                     +'<td><a href="#" id="kurumsal_kimlik_tasarimi_formdan_kaldir" style="font-size:20px;color:#ff0000"><i class="fa fa-times-circle"></i></a></td>'
                                
                                    +'<td>'
                                     +' <label>Kurumsal Kimlik Tasarımı</label>'
                                    +'</td> '
                                   +'<td>'
                                   
                                     +'<input type="text" class="form-control" name="suresiz_hizmet_aciklama_kurumsal_kimlik_tasarimi">'
                                   
                                       
                                    +'</td> '
                                    +'<td>'
                                    
                                      +' <div class="input-group input-group-merge"><input id="ucret_kurumsal_kimlik_tasarimi" type="text" name="ucret_kurumsal_kimlik_tasarimi" class="form-control"> '
                                      +'<div class="input-group-append">'
                                      +'<span class="input-group-text"><small class="font-weight-bold">₺</small></span>'
                                      +' </div></div>'
                                   +'</td>'
                                    +'<td><span id="kdv_dahil_ucret_kurumsal_kimlik_tasarimi">0 ₺</span></td>'
                                      +'</tr>');
        }
         if($(this).val()== "9"&& $("#form_satir_web_tasarim").length == 0){

            $('#form_hizmetler_tablosu').append('<tr id="form_satir_web_tasarim">'
                                     +'<td><a href="#" id="web_tasarim_formdan_kaldir" style="font-size:20px;color:#ff0000"><i class="fa fa-times-circle"></i></a></td>'
                                
                                    +'<td>'
                                     +' <label>Web Tasarım</label>'
                                    +'</td> '
                                   +'<td>'
                                     
                                     +'<input type="text" class="form-control" name="suresiz_hizmet_aciklama_web_tasarim">'
                                    
                                       
                                    +'</td> '
                                    +'<td>'
                                    
                                      +' <div class="input-group input-group-merge"><input id="ucret_web_tasarim" type="text" name="ucret_web_tasarim" class="form-control"> '
                                      +'<div class="input-group-append">'
                                      +'<span class="input-group-text"><small class="font-weight-bold">₺</small></span>'
                                      +' </div></div>'
                                   +'</td>'
                                    +'<td><span id="kdv_dahil_ucret_web_tasarim">0 ₺</span></td>'
                                      +'</tr>');
        }
         if($(this).val()== "12"&& $("#form_satir_optimizasyon").length == 0){

            $('#form_hizmetler_tablosu').append('<tr id="form_satir_optimizasyon">'
                                     +'<td><a href="#" id="optimizasyon_formdan_kaldir" style="font-size:20px;color:#ff0000"><i class="fa fa-times-circle"></i></a></td>'
                                
                                    +'<td>'
                                     +' <label>Optimizasyon</label>'
                                    +'</td> '
                                   +'<td>'
                                     
                                      +'<select name="sure_secimi_optimizasyon" class="form-control">'
                                           +' <option value="1">1 Ay</option> ' 
                                          +'  <option value="2">3 Ay</option>  '
                                           +' <option value="3">6 Ay</option>  '
                                          +'  <option value="4">1 Yıl</option>  '
                                           +' <option value="5">2 Yıl</option>'
                                          +'  <option value="6">3 Yıl</option> '
                                          +'  <option value="10">3.5 Yıl</option> '
                                          +'  <option value="7">4 Yıl</option>'
                                          +'  <option value="8">5 Yıl</option>'
                                          +'  <option value="9">5+1 Yıl</option> '
                                       +' </select> '
                                    
                                       
                                    +'</td> '
                                    +'<td>'
                                     
                                      +' <div class="input-group input-group-merge"><input id="ucret_optimizasyon" type="text" name="ucret_optimizasyon" class="form-control"> '
                                      +'<div class="input-group-append">'
                                      +'<span class="input-group-text"><small class="font-weight-bold">₺</small></span>'
                                      +' </div>'
                                     +'</div>'
                                   +'</td>'
                                    +'<td><span id="kdv_dahil_ucret_optimizasyon">0 ₺</span></td>'
                                      +'</tr>');
        }
         if($(this).val()== "13"&& $("#form_satir_e_ticaret").length == 0){

            $('#form_hizmetler_tablosu').append('<tr id="form_satir_e_ticaret">'
                                     +'<td><a href="#" id="e_ticaret_formdan_kaldir" style="font-size:20px;color:#ff0000"><i class="fa fa-times-circle"></i></a></td>'
                                
                                    +'<td>'
                                     +' <label>E-Ticaret</label>'
                                    +'</td> '
                                   +'<td>'
                                   
                                     +'<input type="text" class="form-control" name="suresiz_hizmet_aciklama_e_ticaret">'
                                   
                                       
                                    +'</td> '
                                    +'<td>'
                                     
                                      +' <div class="input-group input-group-merge"><input id="ucret_e_ticaret" type="text" name="ucret_e_ticaret" class="form-control"> '
                                     +'<div class="input-group-append">'
                                      +'<span class="input-group-text"><small class="font-weight-bold">₺</small></span>'
                                      +' </div></div>'
                                   +'</td>'
                                    +'<td><span id="kdv_dahil_ucret_e_ticaret">0 ₺</span></td>'
                                      +'</tr>');
        }
         if($(this).val()== "14"&& $("#form_satir_ozel_yazilim").length == 0){

            $('#form_hizmetler_tablosu').append('<tr id="form_satir_ozel_yazilim">'
                                     +'<td><a href="#" id="ozel_yazilim_formdan_kaldir" style="font-size:20px;color:#ff0000"><i class="fa fa-times-circle"></i></a></td>'
                                
                                    +'<td>'
                                     +' <label>Özel Yazılım</label>'
                                    +'</td> '
                                   +'<td>'
                                    
                                     +'<input type="text" class="form-control" name="suresiz_hizmet_aciklama_ozel_yazilim">'
                                  
                                       
                                    +'</td> '
                                    +'<td>'
                                   
                                      +' <div class="input-group input-group-merge"><input id="ucret_ozel_yazilim" type="text" name="ucret_ozel_yazilim" class="form-control"> '
                                     +'<div class="input-group-append">'
                                      +'<span class="input-group-text"><small class="font-weight-bold">₺</small></span>'
                                      +' </div></div>'
                                   +'</td>'
                                    +'<td><span id="kdv_dahil_ucret_ozel_yazilim">0 ₺</span></td>'
                                      +'</tr>');
        }

    });
  
   
 

});
$('#form_hizmetler_tablosu').on('change','#ucret_google_reklamlari',function(e){
    e.preventDefault();
    $('#kdv_dahil_ucret_google_reklamlari').empty();
    $('#kdv_dahil_ucret_google_reklamlari').append(parseFloat($('#ucret_google_reklamlari').val()*1.18) +" ₺");
});
$('#form_hizmetler_tablosu').on('click','#google_reklamlari_formdan_kaldir',function(e){
    e.preventDefault();
    $('#form_satir_google_reklamlari').remove();

});
$('#form_hizmetler_tablosu').on('change','#ucret_facebook_reklamlari',function(e){
    e.preventDefault();
    $('#kdv_dahil_ucret_facebook_reklamlari').empty();
    $('#kdv_dahil_ucret_facebook_reklamlari').append(parseFloat($('#ucret_facebook_reklamlari').val()*1.18) +" ₺");
});
$('#form_hizmetler_tablosu').on('click','#facebook_reklamlari_formdan_kaldir',function(e){
    e.preventDefault();
    $('#form_satir_facebook_reklamlari').remove();

});
 $('#form_hizmetler_tablosu').on('change','#ucret_instagram_reklamlari',function(e){
    e.preventDefault();
    $('#kdv_dahil_ucret_instagram_reklamlari').empty();
    $('#kdv_dahil_ucret_instagram_reklamlari').append(parseFloat($('#ucret_instagram_reklamlari').val()*1.18) +" ₺");
});
$('#form_hizmetler_tablosu').on('click','#instagram_reklamlari_formdan_kaldir',function(e){
    e.preventDefault();
    $('#form_satir_instagram_reklamlari').remove();

});
 $('#form_hizmetler_tablosu').on('change','#ucret_produksiyon',function(e){
    e.preventDefault();
    $('#kdv_dahil_ucret_produksiyon').empty();
    $('#kdv_dahil_ucret_produksiyon').append(parseFloat($('#ucret_produksiyon').val()*1.18) +" ₺");
});
$('#form_hizmetler_tablosu').on('click','#produksiyon_formdan_kaldir',function(e){
    e.preventDefault();
    $('#form_satir_produksiyon').remove();

});
 $('#form_hizmetler_tablosu').on('change','#ucret_sosyal_medya',function(e){
    e.preventDefault();
    $('#kdv_dahil_ucret_sosyal_medya').empty();
    $('#kdv_dahil_ucret_sosyal_medya').append(parseFloat($('#ucret_sosyal_medya').val()*1.18) +" ₺");
});
$('#form_hizmetler_tablosu').on('click','#sosyal_medya_formdan_kaldir',function(e){
    e.preventDefault();
    $('#form_satir_sosyal_medya').remove();

});
 $('#form_hizmetler_tablosu').on('change','#ucret_google_local_seo',function(e){
    e.preventDefault();
    $('#kdv_dahil_ucret_google_local_seo').empty();
    $('#kdv_dahil_ucret_google_local_seo').append(parseFloat($('#ucret_google_local_seo').val()*1.18) +" ₺");
});
$('#form_hizmetler_tablosu').on('click','#google_local_seo_formdan_kaldir',function(e){
    e.preventDefault();
    $('#form_satir_google_local_seo').remove();

});
 $('#form_hizmetler_tablosu').on('change','#ucret_google_maps',function(e){
    e.preventDefault();
    $('#kdv_dahil_ucret_google_maps').empty();
    $('#kdv_dahil_ucret_google_maps').append(parseFloat($('#ucret_google_maps').val()*1.18) +" ₺");
});
$('#form_hizmetler_tablosu').on('click','#google_maps_formdan_kaldir',function(e){
    e.preventDefault();
    $('#form_satir_google_maps').remove();

});
 $('#form_hizmetler_tablosu').on('change','#ucret_kurumsal_kimlik_tasarimi',function(e){
    e.preventDefault();
    $('#kdv_dahil_ucret_kurumsal_kimlik_tasarimi').empty();
    $('#kdv_dahil_ucret_kurumsal_kimlik_tasarimi').append(parseFloat($('#ucret_kurumsal_kimlik_tasarimi').val()*1.18) +" ₺");
});
$('#form_hizmetler_tablosu').on('click','#kurumsal_kimlik_tasarimi_formdan_kaldir',function(e){
    e.preventDefault();
    $('#form_satir_kurumsal_kimlik_tasarimi').remove();

});
 $('#form_hizmetler_tablosu').on('change','#ucret_web_tasarim',function(e){
    e.preventDefault();
    $('#kdv_dahil_ucret_web_tasarim').empty();
    $('#kdv_dahil_ucret_web_tasarim').append(parseFloat($('#ucret_web_tasarim').val()*1.18) +" ₺");
});
$('#form_hizmetler_tablosu').on('click','#web_tasarim_formdan_kaldir',function(e){
    e.preventDefault();
    $('#form_satir_web_tasarim').remove();

});
 $('#form_hizmetler_tablosu').on('change','#ucret_optimizasyon',function(e){
    e.preventDefault();
    $('#kdv_dahil_ucret_optimizasyon').empty();
    $('#kdv_dahil_ucret_optimizasyon').append(parseFloat($('#ucret_optimizasyon').val()*1.18) +" ₺");
});
$('#form_hizmetler_tablosu').on('click','#optimizasyon_formdan_kaldir',function(e){
    e.preventDefault();
    $('#form_satir_optimizasyon').remove();

});
 $('#form_hizmetler_tablosu').on('change','#ucret_e_ticaret',function(e){
    e.preventDefault();
    $('#kdv_dahil_ucret_e_ticaret').empty();
    $('#kdv_dahil_ucret_e_ticaret').append(parseFloat($('#ucret_e_ticaret').val()*1.18) +" ₺");
});
$('#form_hizmetler_tablosu').on('click','#e_ticaret_formdan_kaldir',function(e){
    e.preventDefault();
    $('#form_satir_e_ticaret').remove();

});
 $('#form_hizmetler_tablosu').on('change','#ucret_ozel_yazilim',function(e){
    e.preventDefault();
    $('#kdv_dahil_ucret_ozel_yazilim').empty();
    $('#kdv_dahil_ucret_ozel_yazilim').append(parseFloat($('#ucret_ozel_yazilim').val()*1.18) +" ₺");
});
$('#form_hizmetler_tablosu').on('click','#ozel_yazilim_formdan_kaldir',function(e){
    e.preventDefault();
    $('#form_satir_ozel_yazilim').remove();

});

$('#hakedis_odeme_talepleri').on('click','#hakedis_odeme_onayi',function(e){
    e.preventDefault();
     $.ajax({
        type: "GET",
        url: '/sistem-yoneticisi/bayi-odeme-talebini-onayla',
        data:  {talep_id:$(this).attr('data-value')},
        dataType: "text",
       
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
               $('#preloader').hide();
                swal({
                        title: 'Başarılı',
                        text: result,
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
             });
                window.location.href = '/sistem-yoneticisi/bayi-odeme-talepleri';

            
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             swal({
                        title: 'Hata',
                        text: 'Bir hata oluştu. Lütfen teknik ekibe başvurunuz',
                        type: 'error',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-danger',
                       confirmButtonText: 'Kapat',
             });
             document.getElementById('hata').innerHTML = request.responseText;
            
            
        }
    });
});
$('#bayi-banka-bilgileri').on('submit',function(e){
    e.preventDefault();
    
     $.ajax({
        type: "POST",
        url: '/satisortakligi/yeni-banka-hesabi-ekle',
        data: $('#bayi-banka-bilgileri').serialize(),
        dataType: "json",
        
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
               $('#preloader').hide();
                swal({
                        title: 'Başarılı',
                        text: result.mesaj,
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
             });
            
               $('#bayi_banka_bilgileri_liste').empty();
                       $('#bayi_banka_bilgileri_liste').append(result.bayi_banka_html);
            
        },
        error: function (request, status, error) {
             $('#preloader').hide();
            
             document.getElementById('hata').innerHTML = request.responseText;
            
            
        }
    });
});
$('#hakedis_odeme_talepleri').on('click','button[name="banka_bilgi_goruntule"]',function(e){
     e.preventDefault();
    var talepid = $(this).attr('data-value');
     $.ajax({
        type: "GET",
        url: '/sistem-yoneticisi/bayi-hakedis-banka-bilgi-goruntule',
        data: {talep_id:talepid},
        dataType: "text",
        
       
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             $('#preloader').hide();
            $('#bayi_banka_bilgi_liste').empty();
            $('#bayi_banka_bilgi_liste').append(result);
            
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             swal({
                        title: 'Hata',
                        text: 'Bir hata oluştu. Lütfen teknik ekibe başvurunuz',
                        type: 'error',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-danger',
                       confirmButtonText: 'Kapat',
             });
             document.getElementById('hata').innerHTML = request.responseText;
            
            
        }
    });
     $('#datatable-basic').on('click','button[name="hakedis_odeme_onayi"]',function(e){
        e.preventDefault();

     });
});
$('#yeni_banka_bilgisi_ekle').click(function(e){
    $('#bayi_banka_id').val('');
    $('#bayi-banka-bilgileri').trigger('reset');

});
$('#bayi_banka_bilgileri').on('click','a[name="bayi_banka_bilgi_guncelle"]',function (e) {
    var id=$(this).attr('data-value');

    var tds = $(this).closest('tr').children('td');
    $('#bayi_banka_adi option').filter(function() {
    return $(this).text() === tds[1].innerHTML;  
    }).prop('selected', true);  
    $('#bayi_banka_id').val(id);
    $('#bayi_hesap_iban').val(tds[2].innerHTML);
    $('#bayi_hesap_sube_kodu').val(tds[4].innerHTML);
    $('#bayi_hesap_no').val(tds[5].innerHTML);

     $('#bayi_alici_hesap_adi').val(tds[3].innerHTML);
    $('#banka-bilgi-ekleme').modal('show');

});
$('#bayi_banka_bilgileri').on('click','a[name="bayi_banka_bilgi_kaldir"]',function (e) {
    var id = $(this).attr('data-value');
    e.preventDefault();
      swal({

        title: "Emin misiniz?",

        text: "Banka hesabı silme işlemi geri alınamaz!",

        type: "warning",

        showCancelButton: true, 

        confirmButtonText: 'Kaldır',

        cancelButtonText: "Vazgeç",

        

               

    }).then(function (result) {

         if(result.value){
            $.ajax({
                type: "POST",
                url: '/satisortakligi/bayi-banka-hesabi-kaldir',
                data:  {bayi_banka_id:id,_token:$('meta[name="csrf-token"]').attr('content')},
                dataType: "text",
                
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {   
                     
                       $('#preloader').hide(); 
                        $('#bayi_banka_bilgileri_liste').empty();
                       $('#bayi_banka_bilgileri_liste').append(result);
                       
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML = request.responseText;
                     $('#preloader').hide();
                    
                }
            });
         }
    });
});
$('#musteri-randevu').on('submit',function (e) {
    e.preventDefault();
   
});
$('#musteri_bilgileri_tablo').on('click','button[name="formu_kaydet_not_al"]',function (e) {
     var tds = $(this).closest('tr').children('td');
     $('#yapılan_islemler_baslik').empty();
     $('#yapılan_islemler_baslik').append(tds[0].innerHTML + ' için yapılan işlemler');
     $('#islem_eklenecek_musteri_id').val($(this).attr('data-value'));
     
     $.ajax({
                type: "GET",
                url: '/musteri-temsilcisi/yapilan-islem-getir/'+$(this).attr('data-value'),
                data:  {musteri_id:$(this).attr('data-value')},
                dataType: "text",
                
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {   
                     
                      $('#preloader').hide();
                    $('#yapilan_islemler').empty();
                    $('#yapilan_islemler').append(result);
                       
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML = request.responseText;
                     $('#preloader').hide();
                    
                }
            });

});
$('#son_eklenen_musteriler').on('click','button[name="musteri_yapilan_islemler"]',function (e) {
     var tds = $(this).closest('tr').children('td');
     $('#yapılan_islemler_baslik').empty();
     $('#yapılan_islemler_baslik').append(tds[1].innerHTML + ' için yapılan işlemler');
     $('#islem_eklenecek_musteri_id').val($(this).attr('data-value'));
     
     $.ajax({
                type: "GET",
                url: '/sistem-yoneticisi/yapilan-islem-getir/'+$(this).attr('data-value'),
                data:  {musteri_id:$(this).attr('data-value')},
                dataType: "text",
                
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {   
                     
                      $('#preloader').hide();
                    $('#yapilan_islemler').empty();
                    $('#yapilan_islemler').append(result);
                       
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML = request.responseText;
                     $('#preloader').hide();
                    
                }
            });

});
$('#yapilan_islemler').on('click','a[name="yapilan_islem_sil"]',function(e){
    e.preventDefault();
    var notid = $(this).attr('data-value');
     swal({

        title: "Emin misiniz?",

        text: "İşlem silme işlemi geri alınamaz!",

        type: "warning",

        showCancelButton: true, 

        confirmButtonText: 'Sil',

        cancelButtonText: "Vazgeç", 

    }).then(function (result) {
          $.ajax({
                type: "POST",
                url: '/musteri-temsilcisi/not-sil',
                data:  {id:notid,_token:$('meta[name="csrf-token"]').attr('content')},
                dataType: "text",
                
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {   
                     
                   $('#preloader').hide();
                    $('#yapilan_islemler').empty();
                    $('#yapilan_islemler').append(result);
                       
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML = request.responseText;
                     $('#preloader').hide();
                    
                }
            });
    });

});
$('#yeni_yapilan_islem').on('submit',function(e){
    e.preventDefault();
            $.ajax({
                type: "POST",
                url: '/musteri-temsilcisi/not-ekle-duzenle',
                data:  $('#yeni_yapilan_islem').serialize(),
                dataType: "text",
                
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {   
                     
                    $('#preloader').hide(); 
                        swal({
                        title: "Başarılı",
                        text: "Yapılan işlem bilgileri başarıyla eklendi",
                        type: "success",
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
                     });
                    $('#yapilan_islemler').empty();
                    $('#yapilan_islemler').append(result);
                       
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML = request.responseText;
                     $('#preloader').hide();
                    
                }
            });
});




