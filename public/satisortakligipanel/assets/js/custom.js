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
$('#profil_satis_ortagi_bilgi').on('submit',function (e) {

    e.preventDefault();
     
    $.ajax({
        type: "POST",
        url: '/satisortakligi/bilgileri-guncelle',
        data:  $('#profil_satis_ortagi_bilgi').serialize(),
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
$('#sifre_bilgi_satis_ortagi').on('submit',function (e) {
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
                data:  $('#sifre_bilgi_satis_ortagi').serialize(),
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
        url: '/satisortakligi/profil-resmi-degistir',
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
 
  
$('#yeni_musteri_ekle').on('submit', function (e) {
    e.preventDefault();
    if($('#il_id_yeni_musteri').val()!='0' && $('#il_id_yeni_musteri').val()!=''&& $('#ilce_id_yeni_musteri').val()!="0" &&$('#ilce_id_yeni_musteri').val()!="" )
    {
        $.ajax({
        type: "POST",
        url: '/satisortakligi/yeni-musteri-ekle',
        data: $('#yeni_musteri_ekle').serialize(),
        dataType: "json",

        beforeSend: function () {
            $('#preloader').show();
        },
        success: function (result) {
            $('#preloader').hide();

            if (result.status === 'warning') {
                if (result.has_demo_account) {
                    swal({
                        title: 'Uyarı',
                        text: 'Bu kişi sistemde demo hesabı var!',
                        icon: 'warning',
                        confirmButtonText: 'Satış Yap'
                    }).then(function(result2) {

                        if (result2.value) {
                            
                            satisformunuac(e, result.formid);  // `e` event parametresi burada geçerli.
                        }
                    });
                } else {
                    swal({
                        title: 'Uyarı',
                        text: 'Bu kişi sistemde kayıtlı. Demo hesabı açabilirsiniz.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Demo Hesabı Aç',
                        cancelButtonText: 'Kapat'
                    }).then(function(result2) {
                        if (result2.value) {
                               
                            demo_hesabi_ac(e,$(this).attr('data-value'),'','');
                            // Demo hesabı açmak için gerekli işlem yapılabilir.
                        }
                    });
                }
            } else if (result.status === 'success') {
                swal({
                    title: 'Başarılı',
                    text: result.message,
                    icon: 'success',
                    confirmButtonText: 'Kapat'
                });
            }
            $('#yeni_musteri_ekle')[0].reset();
        },
        error: function (request, status, error) {
            $('#preloader').hide();
            document.getElementById('hata').innerHTML = request.responseText;
            swal({
                title: 'Hata',
                text: 'Bir hata oluştu. Lütfen sistem yöneticisine başvurunuz! ' + error,
                icon: 'error',
                confirmButtonText: 'Kapat'
            });
        }
    });
       
    }
    else{
         swal({
            title: 'Uyarı',
            text: "Devam etmek için il ve ilçe bilgileri girmek zorunludur!",
            type: 'warning',
            buttonsStyling: false,
            confirmButtonClass: 'btn btn-warning',
            confirmButtonText: 'Kapat',
            timer:3000,
        });
    }
    
});
   
 

$('#yeni_musteri_ekle_excel').on('submit',function(e){
    
    e.preventDefault();
    var liste=  $('#excel_dosyasi_yeni').get(0).files[0];
     
    var formData = new FormData();
    formData.append('excel_dosyasi_yeni',liste);
    formData.append('pasif_ortak',$('#pasif_ortak_excel').val());
      
     
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
            
            swal({
                        title: 'Aktarım Tamamlandı',
                        text: result,
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
                       timer:3000,
             });
            $('#yeni_musteri_ekle_excel')[0].reset();
           
          
            
               
        

           
            
             
            
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
/*$('#musteri-leads').on('submit',function(e){
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

});*/
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
    document.getElementById('hakedis_miktari_text').innerHTML = $('#satis_ortagi_guncel_hakedis').attr('data-value') +' ₺';
    $('#hakedis_miktari').val($('#satis_ortagi_guncel_hakedis').attr('data-value'));
   
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
        dataType: "json",
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
                        text: result.returntext,
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
                       timer:3000,
            });
             $('#odeme_talep_et_formu')[0].reset();
             $('#modal-default').modal('hide');
             if($('#odeme_talep_et_button').length > 0)
             {
                if(result.hakedis.toplam == 0)
                    $('#odeme_talep_et_button').attr('disabled',true);
             }
             $('#hakedis_talepleri').DataTable().destroy();
            $('#hakedis_talepleri').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'tarih'   },
                                 { data: 'tutar' },
                                 { data: 'durum'   },
                                 
                            ],
                            data:  result.talepler,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                                },
            });

             

            
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
$('#satis-ortagi-banka-bilgileri').on('submit',function(e){
    e.preventDefault();
    
     $.ajax({
        type: "POST",
        url: '/satisortakligi/yeni-banka-hesabi-ekle',
        data: $('#satis-ortagi-banka-bilgileri').serialize(),
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
                $('#satis_ortagi_banka_bilgileri').DataTable().destroy();
                     $('#satis_ortagi_banka_bilgileri').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "asc" ]],
                            columns:[ 
                               
                                 { data: 'banka' },
                                 { data: 'iban'   },
                                  { data: 'sube_kodu' },
                                  { data: 'hesap_no' },
                                  { data: 'alici'   },
                              
                                   { data: 'islemler' },
                            ],
                            data:  result.bankalar,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
                            });
                      
            
              
            
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
        url: '/sistem-yoneticisi/satis-ortagi-hakedis-banka-bilgi-goruntule',
        data: {talep_id:talepid},
        dataType: "text",
        
       
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             $('#preloader').hide();
            $('#satis_ortagi_banka_bilgi_liste').empty();
            $('#satis_ortagi_banka_bilgi_liste').append(result);
            
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
    $('#satis_ortagi_banka_id').val('');
    $('#satis-ortagi-banka-bilgileri').trigger('reset');

});
$('#satis_ortagi_banka_bilgileri').on('click','a[name="satis_ortagi_banka_bilgi_guncelle"]',function (e) {
    var id=$(this).attr('data-value');

    var tds = $(this).closest('tr').children('td');
    $('#satis_ortagi_banka_adi option').filter(function() {
    return $(this).text() === tds[0].innerHTML;  
    }).prop('selected', true);  
    $('#satis_ortagi_banka_id').val(id);
    $('#satis_ortagi_hesap_iban').val(tds[1].innerHTML);
    $('#satis_ortagi_hesap_sube_kodu').val(tds[2].innerHTML);
    $('#satis_ortagi_hesap_no').val(tds[3].innerHTML);

     $('#satis_ortagi_alici_hesap_adi').val(tds[4].innerHTML);
    $('#banka-bilgi-ekleme').modal('show');

});
$('#satis_ortagi_banka_bilgileri').on('click','a[name="satis_ortagi_banka_bilgi_kaldir"]',function (e) {
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
                url: '/satisortakligi/satis-ortagi-banka-hesabi-kaldir',
                data:  {satis_ortagi_banka_id:id,_token:$('meta[name="csrf-token"]').attr('content')},
                dataType: "json",
                
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {   
                     
                    $('#preloader').hide(); 
                    $('#satis_ortagi_banka_bilgileri').DataTable().destroy();
                     $('#satis_ortagi_banka_bilgileri').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "asc" ]],
                            columns:[ 
                               
                                 { data: 'banka' },
                                 { data: 'iban'   },
                                  { data: 'sube_kodu' },
                                  { data: 'hesap_no' },
                                  { data: 'alici'   },
                              
                                   { data: 'islemler' },
                            ],
                            data:  result,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
                      
                       
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
function demo_hesabi_ac(event,salonid,dogrulamakodu,ceptelefon){
    event.preventDefault();
    let ev = event;
    var salon_id = salonid;
    $.ajax({
        type: "POST",
        url: '/satisortakligi/demohesabiac',
        data:  {salonid:salon_id,dogrulama_kodu:dogrulamakodu,pasifortakid:$('#pasifortakid').val(),yetkili_telefon:ceptelefon},
        dataType: "json",
        headers: { 
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') 
        },
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {   
             
            $('#preloader').hide(); 
            if(result.dogrulama_gerekiyor=="1")
                    {
                        swal({
                            title: result.mesaj,
                            input: 'text',
                            showCancelButton: true,
                            confirmButtonText: 'Gönder',
                            cancelButtonText: 'Vazgeç',
                            showLoaderOnConfirm: true,
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                        }).then(function (result2) {
                            if(result2.value)

                                demo_hesabi_ac(ev,salonid,result2.value,'');
                           
                        });
             
                    }
            else if(result.dogrulama_gerekiyor=="2")
            {
                 swal({
                            title: result.mesaj,
                            input: 'text',
                            showCancelButton: true,
                            confirmButtonText: 'Kaydet',
                            cancelButtonText: 'Vazgeç',
                            showLoaderOnConfirm: true,
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                  }).then(function (result2) {
                        if(result2.value)
                            demo_hesabi_ac(ev,salonid,'',result2.value);
                           
                  });
            }
            else{

                swal({
                    title: "Başarılı",
                    text: result.mesaj,
                    type: "success",
                    buttonsStyling: false,
                    confirmButtonClass: 'btn btn-success',
                   confirmButtonText: 'Kapat',
                });
                bilgitablolariniguncelle(result);
            }   



            
            
            
               
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $('#preloader').hide();
            
        }
    });
}
$(document).on('click','a[name="demohesabiac"]',function(e){
    
    demo_hesabi_ac(e,$(this).attr('data-value'),'','');
    

});

function satisformunuac(event,salonid){
    event.preventDefault();
    var form_id = salonid;
    $.ajax({
        type: "GET",
        url: '/satisortakligi/isletmedetaylari',
        data : {formid:form_id},
        dataType: "json",
        
        
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {  
            console.log(result);
            $('#preloader').hide();
            
            $('#form_islemleri_form_id').val(form_id);

            $('#form_islemleri_musteri_id').val(result.id);
            $('#form_islemleri_isletme_adi').val(result.isletme_adi);
            $('#form_islemleri_firma_unvani').val(result.firma_unvani);
            $('#form_islemleri_yetkili_kisi').val(result.ad_soyad);
            $('#form_islemleri_email').val(result.email);
            $('#form_islemleri_yetkili_telefon').val(result.yetkili_telefon);
            $('#form_islemleri_telefon').val(result.isletme_telefon);
            
            $('#form_islemleri_gsm1').val(result.gsm_1);
            $('#form_islemleri_gsm2').val(result.gsm_2);
            
            $('#form_islemleri_adres').val(result.adres);
            $('#form_islemleri_vergi_dairesi').val(result.vergi_dairesi);
            $('#form_islemleri_vergi_tc_no').val(result.vergi_tc_no);
            $('#ilce_id').empty();
             $('#ilce_id').select2({
        
        
                data: result.ilceler
            });
          
            $('#il_id').val(result.il_id).trigger('change');
             $('#ilce_id').val(result.ilce_id).trigger('change').select2();
            $('#satis-formu').modal();
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
}
$(document).on('click','a[name="satisyap"]',function(e){ 
     
    satisformunuac(e,$(this).attr('data-value'));
    
});
 
$('#il_id,#il_id_yeni_musteri,#il_id_musteri_duzenleme').change(function(e){
    e.preventDefault();
     $.ajax({
        type: "POST",
        url: '/ilcelerigetir',
        dataType: "json",
        data: {il_id:$(this).val(),_token:$('input[name="_token"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        headers: {
        'X-CSRF-TOKEN': $('input[name="_token"]').val()  // Set the CSRF token in the header
        },
        success: function(result)  {

            $("#preloader").hide();
             $('#ilce_id').empty();
              var defaultOption = new Option("Seçiniz...", "0", true, true);
            $('#ilce_id').append(defaultOption);
            var data= result.map(item => ({
                        id: item.id,
                        text: item.ilce_adi // Adjust based on your JSON structure
            }));

            data.forEach(item => {
                    var option = new Option(item.text, item.id, false, false);
                    $('#ilce_id').append(option);
            });
            if($('#il_id_yeni_musteri').length)
            {
                $('#ilce_id_yeni_musteri').empty();
                 var defaultOption = new Option("Seçiniz...", "0", true, true);
                $('#ilce_id_yeni_musteri').append(defaultOption);

                data.forEach(item => {

                        var option = new Option(item.text, item.id, false, false);
                        $('#ilce_id_yeni_musteri').append(option);
                });
            }
        },
        error: function (request, status, error) {

            document.getElementById('hata').innerHTML = request.responseText;

            $("#preloader").hide();

        }

    });
});

$(document).on('click', 'a[name="demouzat"]', function (e) {

    e.preventDefault();

    $.ajax({
        type: "POST",
        url: '/satisortakligi/demosurasiuzat',
        data:  {salonid:$(this).attr('data-value'),pasifortakid:$('#pasifortakid').val()},
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
                title: "Başarılı",
                text: "Demo süresi uzatıldı",
                type: "success",
                buttonsStyling: false,
                confirmButtonClass: 'btn btn-success',
               confirmButtonText: 'Kapat',
            });
            bilgitablolariniguncelle(result);

            
            
               
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $('#preloader').hide();
            
        }
    });



});

function bilgitablolariniguncelle(result)
{   
    if($('#tum_musteriler').length)
    {
        $('#tum_musteriler').DataTable().destroy();
        $('#tum_musteriler').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'salon_id'   },
                                 { data: 'salon_adi' },
                                 { data: 'yetkili_bilgisi'   },
                                     { data: 'yetkili_telefon'   },
                                  { data: 'created_at'   },
                                  { data: 'durum'   },
                                      { data: 'notlar'   },
                                 { data: 'islemler' } 
                            ],
                            data:  result.tum_musteriler,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });

    }
    if($('#pasif_musteriler').length)
    {
        $('#pasif_musteriler').DataTable().destroy();
          $('#pasif_musteriler').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'salon_id'   },
                                 { data: 'salon_adi' },
                                 { data: 'yetkili_bilgisi'   },
                                  { data: 'yetkili_telefon'   },
                                  { data: 'created_at'   },
                                      { data: 'notlar'   },
                                 { data: 'islemler' } 
                            ],
                            data: result.pasif_musteriler,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
    }
    if($('#aktif_musteriler').length)
    {
        $('#aktif_musteriler').DataTable().destroy();
          $('#aktif_musteriler').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'salon_id'   },
                                 { data: 'salon_adi' },
                                 { data: 'yetkili_bilgisi'   },
                                 { data: 'yetkili_telefon'   },
                                { data: 'satilan_paket'   },
                                { data: 'kalan_sure'   },
                                 { data: 'islemler' } 
                            ],
                            data:  result.aktif_musteriler,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
    }
    if($('#demosu_olan_musteriler').length)
    {
        $('#demosu_olan_musteriler').DataTable().destroy();
        $('#demosu_olan_musteriler').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'salon_id'   },
                                 { data: 'salon_adi' },
                                 { data: 'yetkili_bilgisi'   },
{ data: 'yetkili_telefon'   },
                                  { data: 'created_at'   },
                                      { data: 'notlar'   },
                                 { data: 'islemler' } 
                            ],
                            data:  result.demosu_olan_musteriler,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
    }
    if($('#satis_yapilamayan_musteriler').length)
    {
        $('#satis_yapilamayan_musteriler').DataTable().destroy();
         $('#satis_yapilamayan_musteriler').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'salon_id'   },
                                 { data: 'salon_adi' },
                                 { data: 'yetkili_bilgisi'   },
                                 { data: 'yetkili_telefon'   },
                                  { data: 'created_at'   },
                                      { data: 'notlar'   },
                                 { data: 'islemler' } 
                            ],
                            data:  result.satis_yapilamayan_musteriler,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
    }
    if($('#demomusterileri').length)
    {
        $('#demomusterileri').DataTable().destroy();
        $('#demomusterileri').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'salon_id'   },
                                 { data: 'salon_adi' },
                                 { data: 'yetkili_bilgisi'   },
                                 { data: 'yetkili_telefon'   },
                                  { data: 'kalan_sure'   },
                                      { data: 'notlar'   },
                                 { data: 'islemler' } 
                            ],
                            data:  result.demosu_olan_musteriler,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '<i class="ion-chevron-right"></i>',
                                      previous: '<i class="ion-chevron-left"></i>'
                                  }
                            },
      });
    }
}




$('#musteri-leads').on('submit',function(e){
    e.preventDefault();
    
     $.ajax({
        type: "POST",
        url: '/satisortakligi/formu-kaydet',
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
                        text: "Satış bilgileri başarıyla kaydedildi.",
                        type: 'success',
                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-success',
                       confirmButtonText: 'Kapat',
                       timer:3000,
             });
             $('#musteri-leads')[0].reset();
             $('#satis-formu').modal('hide');
             bilgitablolariniguncelle(result);
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
function gecmisodemelerigetir(baslangict,bitist)
{

        var baslangicTarihi = baslangict;
        var bitisTarihi = bitist;
        
         $.ajax({
            type: "POST",
            url: '/satisortakligi/gecmis-odemeler-filtre',
            data : {baslangic:baslangicTarihi,bitis:bitisTarihi},
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            
            beforeSend:function(){
                $('#preloader').show();
            },
           success: function(result)  {  
                $('#preloader').hide();
                $('#gecmis_odemeler').DataTable().destroy();
                 $('#gecmis_odemeler').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'tarih'   },
                                 { data: 'tutar' },
                                 { data: 'banka'   },
                                 
                            ],
                            data:  result,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
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
}
$('#gecmis_odeme_filtre').change(function(e){
     e.preventDefault();
    
    if($('#gecmis_odeme_filtre').val() == 'ozel')
    {

        $('.odeme_tarihleri_ozel').removeAttr('style');
    }
    else
    {
        $('.odeme_tarihleri_ozel').attr('style','display:none');
        var tarihAraligi = $('#gecmis_odeme_filtre').val();
        var tarihler = tarihAraligi.split(" / ");
        var baslangicTarihi = tarihler[0];
        var bitisTarihi = tarihler[1];

        
        gecmisodemelerigetir(baslangicTarihi,bitisTarihi);
        
    }
  
});
$('.odeme_tarihi_araligi').change(function(e){
    e.preventDefault();
    gecmisodemelerigetir($('#odeme_bitis_tarihi').val(),$('#odeme_bitis_tarihi').val());
});

$('#satisortakligikayitformu').on('submit', function(e){
    e.preventDefault();
    satisortagikaydi('','');

});
function satisortagikaydi(dogrulamakodu,satisortagiid)
{
     var formData = new FormData();
     formData.append('dogrulama_kodu',dogrulamakodu);
     formData.append('satisortagiid',satisortagiid);
     var other_data = $('#satisortakligikayitformu').serializeArray();
       $.each(other_data,function(key,input){
       
        formData.append(input.name,input.value);
    });
     $.ajax({
            type: "POST",
            url: '/satis-ortakligi-kayit',
            data : formData,
            dataType: "json",
           
            contentType: false,        
        cache: false,             
        processData:false,   
            beforeSend:function(){
                $('#preloader').show();
            },
           success: function(result)  {  
                $('#preloader').hide();
                if(result.status=='warning')
                {
                         swal({
                            title: 'Uyarı',
                            html: result.mesaj + '<br><br><a href="/satisortakligi" class="btn btn-success">Giriş Yap</a>&nbsp;&nbsp;<a href="/satisortakligi/sifremiunuttum" class="btn btn-danger">Şifremi Unuttum</a>',
                            type: 'warning',
                            showCancelButton: false,
                            showConfirmButton: false,
                            

                        });
                }

                if(result.dogrulamakodu == "1")
                {
                    swal({
                            html: result.mesaj,
                            input: 'text',
                            showCancelButton: true,
                            confirmButtonText: 'Gönder',
                            cancelButtonText: 'Vazgeç',
                        
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',

                        }).then(function (result2) {
                            if(result2.value)
                                satisortagikaydi(result2.value,result.satisortagiid);
                        });
                }
                if(result.dogrulamakodu == "0")
                {
                    swal({
                            title: 'Başarılı',
                            text : result.mesaj,
                            type: 'success',
                            timer:5000,
                             showCancelButton: false,
                            showConfirmButton: false,

                        });
                    setTimeout(function(){
                                                window.location.href = '/satisortakligi';
                                            }, 5000);
                    
                }

       
                
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
}
$('#sifremiunuttum_satisortagi').on('submit',function (e) {
    e.preventDefault();
    $.ajax({
                type: "POST",
                url: '/satisortakligi/sifregonder',
                dataType: "json",
                data : $('#sifremiunuttum_satisortagi').serialize(),
                beforeSend:function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                     $('#preloader').hide();
                    if(result.status==true)
                    {
                       yenisifrebelirle_satisortagi('');
                    }
                    else
                    {
                         swal({
                            type: "error",
                            title: "Hata",
                            text:  result.mesaj,
                            timer:3000,
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                        });
                    }
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;

                }

    });



 });
function yenisifrebelirle_satisortagi(uyaritext)
{
    swal({
                            type: "success",
                            title: "Yeni Şifrenizi Belirleyin",
                            html: '<p style="color:#ff0000;text-align:center;margin-bottom:30px">'+uyaritext+'<p><input type="text" class="form-control" required id="sifre_yenileme_dogrulama_kodu" placeholder="SMS ile gönderilen doğrulama kodunu giriniz."></p>'+
                                   '<p><input type="password" class="form-control" required id="sifre_yenileme" placeholder="Yeni şifrenizi giriniz."></p>'+
                                   '<p><input type="password" class="form-control" required id="sifre_yenileme_tekrar" placeholder="Yeni şifrenizi tekrar giriniz."></p>',
                            confirmButtonColor: '#00bc8c',
                            confirmButtonText: 'Gönder',
                            confirmButtonClass: 'btn btn-success',
                            showCloseButton: false,
                            showCancelButton: false,
                            allowOutsideClick: false,
                            closeOnConfirm: false,
                            closeOnCancel: false,
                        }).then(function(res){
                            if($('#sifre_yenileme').val()!=$('#sifre_yenileme_tekrar').val())
                            {
                                swal({
                                            type: "warning",
                                            title: "Uyarı",
                                            text:  'Girdiğiniz şifreler uyuşmamaktadır!',
                                            showCloseButton: false,
                                            showCancelButton: false,
                                            showConfirmButton:false,
                                });
                                yenisifrebelirle_satisortagi('Girdiğiniz şifreler uyuşmamaktadır!');
                            }
                            else if($('#sifre_yenileme_dogrulama_kodu').val()!='' &&$('#sifre_yenileme').val()!= '' && $('#sifre_yenileme_tekrar').val()!= '')
                            {
                                if(res.value)
                                {
                                    $.ajax({
                                    type: "POST",
                                    url: '/satisortakligi/sifredegistir',
                                    dataType: "json",
                                    data : {dogrulama_kodu:$('#sifre_yenileme_dogrulama_kodu').val(),_token:$('input[name="_token"]').val(),sifre:$('#sifre_yenileme').val(),sube:$('input[name="sube"]').val()},
                                    beforeSend:function(){
                                        $('#preloader').show();
                                    },
                                   success: function(result)  {
                                        $('#preloader').hide();
                                        if(result.status==true)
                                        {
                                            swal({
                                                type: "success",
                                                title: "Başarılı",
                                                text:  'Şifreniz başarıyla değiştirildi. Yönlendiriliyorsunuz...',
                                                showCloseButton: false,
                                                showCancelButton: false,
                                                showConfirmButton:false,
                                            });
                                            window.location.href= '/satisortakligi';
                                        }
                                        else
                                        {
                                            yenisifrebelirle_satisortagi('Hatalı doğrulama kodu girdiniz. Lütfen yeniden deneyiniz!');
                                        }
                                    },
                                    error: function (request, status, error) {
                                        $('#preloader').hide();
                                         document.getElementById('hata').innerHTML = request.responseText;
                                        }
                                    });
                                }
                            }
                            else
                            {
                                    swal({
                                            type: "warning",
                                            title: "Uyarı",
                                            text:  'Lütfen tüm alanları eksiksiz doldurunuz!',
                                            showCloseButton: false,                                            
                                            showCancelButton: false,
                                            showConfirmButton:false,
                                });
                                yenisifrebelirle_satisortagi('Lütfen tüm alanları eksiksiz doldurunuz!');
                            }
    });
}
$('#pasif_ortak_ekle').click(function(e){
    $('#pasif_ortak_formu')[0].reset();
    $('#pasif_ortak_id').val('');
});
$('#pasif_ortak_formu').on('submit',function(e){
    e.preventDefault();
     $.ajax({
                type: "POST",
                url: '/satisortakligi/pasif-ortak-ekle-guncelle',
                dataType: "json",
                data : $('#pasif_ortak_formu').serialize(),
                beforeSend:function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                        $('#preloader').hide();
                    
                         swal({
                            type: result.type,
                            title: result.title,
                            text:  result.mesaj,
                            timer: 3000,
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                        })
                         pasifortakliste(result);
                         $('#pasif-ortak-bilgi').modal('hide');
                         $('#pasif_ortak_formu')[0].reset();
                    
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;

                }

    });


});
$(document).on('click','a[name="pasif_ortak_bilgi_guncelle"]',function(e){
    var tds = $(this).closest('tr').children('td');
    $('#pasif_ortak_id').val($(this).atrr('data-value'));
    $('#pasif_ortak_ad_soyad').val(tds[0].innerHTML);
    $('#pasif_ortak_telefon').val(tds[1].innerHTML);
    $('#pasif_ortak_email').val(tds[2].innerHTML);
    $('#pasif_ortak_yuzde').val(tds[3].innerHTML);

});
$(document).on('click','a[name="pasif_ortak_kaldir"]',function(e){
    var id = $(this).attr('data-value');
    e.preventDefault();
      swal({

        title: "Emin misiniz?",

        text: "Pasif ortak silme işlemi geri alınamaz!",

        type: "warning",

        showCancelButton: true, 

        confirmButtonText: 'Kaldır',

        cancelButtonText: "Vazgeç",

        

               

    }).then(function (result) {

         if(result.value){
            $.ajax({
                type: "POST",
                url: '/satisortakligi/pasif-ortak-kaldir',
                data:  {pasifortakid:id,_token:$('meta[name="csrf-token"]').attr('content')},
                dataType: "json",
                
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result2)  {   
                     
                    $('#preloader').hide();                     
                     swal({
                        title: 'Başarılı',
                        text: "Pasif ortak sistemden başarıyla kaldırıldı",
                        type: 'success',
                        showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                       timer:3000
                    });
                     pasifortakliste(result2);
                       
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML = request.responseText;
                     $('#preloader').hide();
                    
                }
            });
         }
    });
});

function pasifortakliste(result)
{
    $('#pasif_ortaklar').DataTable().destroy();
    $('#pasif_ortaklar').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "asc" ]],
                            columns:[
                                 { data: 'adsoyad'   },
                                 { data: 'telefon' },
                                 { data: 'email'   },
                                 { data: 'satisyuzde'   },
                                  { data: 'islemler'   },
                                 
                            ],
                            data:  result.pasifortaklar,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
    });
}
$('#sozlesme_fesih_hesap_silme').on('submit',function(e){
    e.preventDefault();
     $.ajax({
                type: "POST",
                url: '/satisortakligi/sozlesme-fesih-talebi-gonder',
                data:  $('#sozlesme_fesih_hesap_silme').serialize(),
                dataType: "text",
                
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result2)  {   
                     
                    $('#preloader').hide();                     
                     swal({
                        title: 'Başarılı',
                        text: "Sözleşme fesih / verilerinizin ve hesabınızın silinmesi talebiniz tarafınıza ulaşmış olup en kısa sürede konu ile ilgili size ulaşılacaktır.",
                        type: 'success',
                        showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                       timer:3000,
                    });

                     $('#sozlesme_fesih_talebi').modal('hide');
                       $('#sozlesme_fesih_hesap_silme').trigger('reset');
                       
                },
                error: function (request, status, error) {
                    $('#preloader').hide();                     
                     swal({
                        title: 'Başarılı',
                        text: "Sözleşme fesih / verilerinizin ve hesabınızın silinmesi talebiniz tarafınıza ulaşmış olup en kısa sürede konu ile ilgili size ulaşılacaktır.",
                        type: 'success',
                        showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                       timer:3000,
                    });
                      $('#sozlesme_fesih_talebi').modal('hide');
                      $('#sozlesme_fesih_hesap_silme').trigger('reset');
                     
                    
                }
            });
         
});
$(document).on('click','a[name="musteriduzenle"]',function(e){
    e.preventDefault();
     $.ajax({
                type: "GET",
                url: '/satisortakligi/musteri-detaylari',
                data:  {salon_id:$(this).attr('data-value')},
                dataType: "json",
                
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {   
                     
                  $('#preloader').hide(); 
                    $('#salon_id').val(result.salon_id);
                    $('#yetkili_adi_duzenleme').val(result.yetkili_adi);
                    $('#yetkili_telefon_duzenleme').val(result.yetkili_telefon);
                    $('#salon_adi_duzenleme').val(result.salon_adi);
                    $('#telefon_1_duzenleme').val(result.telefon1);
                    $('#telefon_2_duzenleme').val(result.telefon2);
                    $('#telefon_3_duzenleme').val(result.telefon3);
                    $('#adres_duzenleme').val(result.adres);
                    $('#ilce_id_musteri_duzenleme').empty();
                    $('#ilce_id_musteri_duzenleme').select2({
        
        
                        data: result.ilceler
                    });
          
                    $('#il_id_musteri_duzenleme').val(result.il_id).trigger('change');
                    $('#ilce_id_musteri_duzenleme').val(result.ilce_id).trigger('change').select2();
                    $('#satis_ortagi_notu_duzenleme').val(result.satis_ortagi_notu);
                    $('#musteri-bilgi-formu').modal('show');   
                },
                error: function (request, status, error) {
                    $('#preloader').hide();                     
                     swal({
                        title: 'Hata',
                        text: "Bir hata oluştu",
                        type: 'error',
                        showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                       timer:3000,
                    });
                     
                     
                    
                }
            });
});

$('#musteri_duzenle').on('submit',function(e){
    e.preventDefault();
    console.log($('#musteri_duzenle').serialize());
     $.ajax({
                type: "POST",
                url: '/satisortakligi/musteri-guncelle',
                data:  $('#musteri_duzenle').serialize(),
                dataType: "json",
                
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {   
                    console.log(result);
                   $('#preloader').hide();                     
                     swal({
                        title: 'Başarılı',
                        text: "Müşteri başarıyla güncellendi",
                        type: 'success',
                        showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                       timer:3000,
                    });
                     $('#musteri-bilgi-formu').modal('hide'); 
                     $('#musteri_duzenle').trigger('reset');
                     bilgitablolariniguncelle(result);
                },
                error: function (request, status, error) {
                    $('#preloader').hide();                     
                     swal({
                        title: 'Hata',
                        text: "Bir hata oluştu",
                        type: 'error',
                        showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                       timer:3000,
                    });
                     
                     
                    
                }
            });
});




