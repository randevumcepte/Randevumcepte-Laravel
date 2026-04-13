jQuery(window).on("load", function () {
	"use strict";
	// bootstrap wysihtml5
	$(".textarea_editor").wysihtml5({
		html: true,
	});
});
jQuery(window).on("load resize", function () {
	// custom scrollbar
	$(".customscroll").mCustomScrollbar({
		theme: "dark-2",
		scrollInertia: 300,
		autoExpandScrollbar: true,
		advanced: { autoExpandHorizontalScroll: true },
	});
});

jQuery(document).ready(function () {
	"use strict";
	// Background Image
	jQuery(".bg_img").each(function (i, elem) {
		var img = jQuery(elem);
		jQuery(this).hide();
		jQuery(this)
			.parent()
			.css({
				background: "url(" + img.attr("src") + ") no-repeat center center",
			});
	});

	/*==============================================================*/
	// Image to svg convert start
	/*==============================================================*/
	jQuery("img.svg").each(function () {
		var $img = jQuery(this);
		var imgID = $img.attr("id");
		var imgClass = $img.attr("class");
		var imgURL = $img.attr("src");
		jQuery.get(
			imgURL,
			function (data) {
				var $svg = jQuery(data).find("svg");
				if (typeof imgID !== "undefined") {
					$svg = $svg.attr("id", imgID);
				}
				if (typeof imgClass !== "undefined") {
					$svg = $svg.attr("class", imgClass + " replaced-svg");
				}
				$svg = $svg.removeAttr("xmlns:a");
				if (
					!$svg.attr("viewBox") &&
					$svg.attr("height") &&
					$svg.attr("width")
				) {
					$svg.attr(
						"viewBox",
						"0 0 " + $svg.attr("height") + " " + $svg.attr("width")
					);
				}
				$img.replaceWith($svg);
			},
			"xml"
		);
	});
	/*==============================================================*/
	// Image to svg convert end
	/*==============================================================*/

	// click to scroll
	// $('.collapse-box').on('shown.bs.collapse', function () {
	// 	$(".customscroll").mCustomScrollbar("scrollTo",$(this));
	// });

	// code split
	var entityMap = {
		"&": "&amp;",
		"<": "&lt;",
		">": "&gt;",
		'"': "&quot;",
		"'": "&#39;",
		"/": "&#x2F;",
	};
	function escapeHtml(string) {
		return String(string).replace(/[&<>"'\/]/g, function (s) {
			return entityMap[s];
		});
	}
	//document.addEventListener("DOMContentLoaded", init, false);
	window.onload = function init() {
		var codeblock = document.querySelectorAll("pre code");
		if (codeblock.length) {
			for (var i = 0, len = codeblock.length; i < len; i++) {
				var dom = codeblock[i];
				var html = dom.innerHTML;
				html = escapeHtml(html);
				dom.innerHTML = html;
			}
			$("pre code").each(function (i, block) {
				hljs.highlightBlock(block);
			});
		}
	};

	// Search Icon
	$("#filter_input").on("keyup", function () {
		var value = $(this).val().toLowerCase();
		$("#filter_list .fa-hover").filter(function () {
			$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
		});
	});

	// custom select 2 init
	$(".custom-select2").select2();
	 
	$('.opsiyonelSelect').select2({
		    placeholder: "Seçiniz",
		    allowClear:true,
		   

	});
	$('.opsiyonelSelectAy').select2({
		    placeholder: "Yıl",
		    allowClear:true,
		   

	});
	$('.opsiyonelSelectYil').select2({
		    placeholder: "Ay",
		    allowClear:true,
		     

	});
	$('.opsiyonelSelectGun').select2({
		    placeholder: "Gün",
		    allowClear:true,
		   
	});

	// Bootstrap Select
	//$('.selectpicker').selectpicker();

	// tooltip init
	$('[data-toggle="tooltip"]').tooltip();

	// popover init
	$('[data-toggle="popover"]').popover();

	// form-control on focus add class
	$(".form-control").on("focus", function () {
		$(this).parent().addClass("focus");
	});
	$(".form-control").on("focusout", function () {
		$(this).parent().removeClass("focus");
	});

	// sidebar menu icon
	$('.menu-icon, [data-toggle="left-sidebar-close"]').on("click", function () {
		//$(this).toggleClass('open');
		$("body").toggleClass("sidebar-shrink");
		$(".left-side-bar").toggleClass("open");
		$(".mobile-menu-overlay").toggleClass("show");
	});
	$('[data-toggle="header_search"]').on("click", function () {
		jQuery(".header-search").slideToggle();
		if(jQuery(".header-search2").css('display')=='block')
			jQuery('[data-toggle="header_search2"]').trigger('click');

	});
	$('[data-toggle="header_search2"]').on("click", function () {
		jQuery(".header-search2").slideToggle();
		if(jQuery(".header-search").css('display')=='block')
			jQuery('[data-toggle="header_search"]').trigger('click');
		
	});

	var w = $(window).width();
	$(document).on("touchstart click", function (e) {
		if (
			$(e.target).parents(".left-side-bar").length == 0 &&
			!$(e.target).is(".menu-icon, .menu-icon img")
		) {
			$(".left-side-bar").removeClass("open");
			$(".menu-icon").removeClass("open");
			$(".mobile-menu-overlay").removeClass("show");
		}
	});
	// $(window).on("resize", function () {
	// 	var w = $(window).width();
	// 	if ($(window).width() > 1200) {
	// 		$(".left-side-bar").removeClass("open");
	// 		$(".menu-icon").removeClass("open");
	// 		$(".mobile-menu-overlay").removeClass("show");
	// 	}
	// });

	// sidebar menu Active Class
	$("#accordion-menu").each(function () {
		var vars = window.location.href.split("/").pop();
		$(this)
			.find('a[href="' + vars + '"]')
			.addClass("active");
	});

	// click to copy icon
	$(".fa-hover").click(function (event) {
		event.preventDefault();
		var $html = $(this).find(".icon-copy").first();
		var str = $html.prop("outerHTML");
		CopyToClipboard(str, true, "Copied");
	});
	var clipboard = new ClipboardJS(".code-copy");
	clipboard.on("success", function (e) {
		CopyToClipboard("", true, "Copied");
		e.clearSelection();
	});

	// date picker
	$(".date-picker").datepicker({
		minDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
	});
	$("#formmusteriyas").datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
	});
	$('#adisyon_tarihi,#tahsilat_tarihi,.geriye-yonelik').datepicker({
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
	});

	$('#randevutarihiyeni,#randevuduzenle_tarih,#ongorusme_tarihi').datepicker({
	
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			//randevusaatlerinigetir(dateText,$('input[name="sube"]').val(),'');			
		}
	});

	$('#personel_rapor_tarih_araligi_1').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			 
			if($('#personel_rapor_tarih_araligi_2').val()!='')
				adisyonlistefiltrepersonel();
			else
				$('#personel_rapor_tarih_araligi_2').focus();
			$('#personel_rapor_tarih_araligi_1').val(dateText);
		}
	})
	$('#personel_rapor_tarih_araligi_2').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			if($('#personel_rapor_tarih_araligi_1').val()!='')
				adisyonlistefiltrepersonel();
			else
				$('#personel_rapor_tarih_araligi_1').focus();
			$('#personel_rapor_tarih_araligi_2').val(dateText);
		}
	})
	$('#kasa_baslangic_tarihi').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			kasaraporu(dateText,$('#kasa_bitis_tarihi').val());
			$('#kasa_baslangic_tarihi').val(dateText);
		}
	});
	$('#kasa_bitis_tarihi').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			kasaraporu($('#kasa_baslangic_tarihi').val(),dateText);
			$('#kasa_bitis_tarihi').val(dateText);
		}
	});
	$('#cdr_baslangic_tarihi').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			cdrraporu(dateText,$('#cdr_bitis_tarihi').val());
			$('#cdr_baslangic_tarihi').val(dateText);
		}
	});
	$('#cdr_bitis_tarihi').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			cdrraporu($('#cdr_baslangic_tarihi').val(),dateText);
			$('#cdr_bitis_tarihi').val(dateText);
		}
	});
	$('#masraf_tarihi').each(function(){
		$(this).datepicker({
		 	
			language: "tr",
			autoClose: true,
			dateFormat: "yyyy-mm-dd",
		});
	});
	$('#resimtarih').each(function(){
		$(this).datepicker({
		 	
			language: "tr",
			autoClose: true,
			dateFormat: "yyyy-mm-dd",
		});
	});
	$('input[name="paketbaslangictarihiadisyon[]"],input[name="paketbaslangictarihisenet[]"],input[name="islemtarihiyeni[]"]').each(function(){
		$(this).datepicker({
		 
			language: "tr",
			autoClose: true,
			dateFormat: "yyyy-mm-dd",
		});
	});
	
	$('input[name="dogum_tarihi"]').datepicker({
		 
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
	});
	$('.calendardatepicker').datepicker({
		 
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			
			takvimyukle(true,true);
		}

	});
	$('.calendardatepicker2').datepicker({
		 
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
		
			ajandatakvimyukle(true,true);
		}

	});
	$('input[name="seans_tarihi_adisyon_paket"]').datepicker({ 
			 
			language: "tr",
			autoClose: true,
			dateFormat: "yyyy-mm-dd",
			onSelect: function(dateText,inst) {
			     
			     
			   seanstarihguncelle(dateText,this.value);
	       		 		 
	    	}

		

	});
	

	$('#personel_rapor_baslangic_tarihi').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			var baslangicTarihi = '';
			var bitisTarihi = '';
			if($('#personel_rapor_baslangic_tarihi').val()!='')
				baslangicTarihi = dateText;
			if($('#personel_rapor_bitis_tarihi').val()!='')
				bitisTarihi = dateText;
			 personelRaporFiltre(baslangicTarihi,bitisTarihi);
	       		 		 
	    }

	});

	$('#hizmet_rapor_baslangic_tarihi').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
		 
			 hizmetRaporFiltre($('#hizmet_rapor_baslangic_tarihi').val(),$('#hizmet_rapor_bitis_tarihi').val());
	       		 		 
	    }

	});

	$('#urun_rapor_baslangic_tarihi').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			 
			 urunRaporFiltre($('#urun_rapor_baslangic_tarihi').val(),$('#urun_rapor_bitis_tarihi').val());
	       		 		 
	    }

	});

	$('#paket_rapor_baslangic_tarihi').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			 
			 paketRaporFiltre($('#paket_rapor_baslangic_tarihi').val(),$('#paket_rapor_bitis_tarihi').val());
	       		 		 
	    }

	});

	$('#personel_rapor_bitis_tarihi').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			 
			 personelRaporFiltre($('#personel_rapor_baslangic_tarihi').val(),$('#personel_rapor_bitis_tarihi').val());
	       		 		 
	    }

	});

	$('#hizmet_rapor_bitis_tarihi').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			hizmetRaporFiltre($('#hizmet_rapor_baslangic_tarihi').val(),$('#hizmet_rapor_bitis_tarihi').val());
	       		 		 
	    }

	});

	$('#urun_rapor_bitis_tarihi').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
		 	urunRaporFiltre($('#urun_rapor_baslangic_tarihi').val(),$('#urun_rapor_bitis_tarihi').val());
	       		 		 
	    }

	});

	$('#paket_rapor_bitis_tarihi').datepicker({
		maxDate: new Date(),
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
			 paketRaporFiltre($('#paket_rapor_baslangic_tarihi').val(),$('#paket_rapor_bitis_tarihi').val());
	       		 		 
	    }

	});


	$(".datetimepicker").datepicker({
		minDate: new Date(),
		timepicker: true,
		language: "tr",
		autoClose: true,
		dateFormat: "yyyy-mm-dd",
	});
	$(".datetimepicker-range").datepicker({
		
		language: "tr",
		range: true,
		multipleDates: true,
		multipleDatesSeparator: " / ",
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
		    if(dateText.indexOf('/')>-1){
		    	 
		    $.ajax({
                type: "GET",
                url: '/isletmeyonetim/randevulistefiltre', 
                dataType: "json",
                data : {olusturulma : $('#olusturulmaya_gore_filtre').val(),durum : $('#duruma_gore_filtre').val(),zaman : $('#zamana_gore_filtre').val(),ozeltarih:dateText,salon_id:$('input[name="sube"]').val()}, 
                beforeSend:function(){
                    $('#preloader').show();
                },
                success: function(result)  {   
                    
                    $('#preloader').hide();
                    
                   $('#randevu_liste').DataTable().destroy();
                    $('#randevu_liste').DataTable({
                        autoWidth: false,
                        responsive: true,
                        "order": [[ 4, "asc" ]],
                        columns:[
                      
                           { data: 'musteri'   },
                           { data: 'telefon' },
                             
                           { data: 'hizmetler'   },
                           { data: 'odalar'   },
                           { data: 'tarih' },
                         
                           { data: 'saat' },
                              
                           { data: 'durum' },

                          
                           { data: 'olusturan' },
                          
                          
                           { data: 'islemler' }
                            
                       
                           
                        ],
                        data: result,

                        "language" : {
                            "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                            searchPlaceholder: "Ara",
                            paginate: {
                                next: '<i class="ion-chevron-right"></i>',
                                previous: '<i class="ion-chevron-left"></i>'  
                        }
                     },
               });   
                },
                error: function (request, status, error) { 
                     $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                     
                }

    		});  

		    }
       		 		 
    	}
	});
	$("#adisyon_tarihe_gore_filtre").datepicker({
		
		language: "tr",
		range: true,
		multipleDates: true,
		multipleDatesSeparator: " / ",
		dateFormat: "yyyy-mm-dd",
		onSelect: function(dateText) {
		    if(dateText.indexOf('/')>-1){
		    	 
		     	  var namesType = $.fn.dataTable.absoluteOrder( [
                     { value: null, position: 'bottom' }
                     ] );
                 $.fn.dataTable.moment('DD.MM.YYYY');
               $('#adisyon_liste').DataTable().destroy();
               $('#adisyon_liste').DataTable({
                       autoWidth: false,
                       responsive: true,
                       "processing": true,
                       "serverSide": true,
                       "deferRender": true, // DOM yükünü azaltır!
                        "ajax": {
                            "url": "/isletmeyonetim/adisyon-filtreli-getir",
                            "type": "GET",
                            "data": function (d) {
                               var sube = $('input[name="sube"]').val();
                                
                                 d.sube = sube;
                                d.tur = '';
                                d.musteri_id=$('#adisyon_musteriye_gore_filtrele').val();
                                d.tariharaligi=$('#adisyon_tarihe_gore_filtre').val();
                                
                                
                                console.log('Request parameters:', d); // Debugging line
                            },
                            "error": function(xhr, error, code) {
                                console.error('DataTables AJAX error:', error, code);
                                console.error('XHR:', xhr);
                            },
                        },
                       columns:[
                        { data: 'acilis_tarihi'},
                               { data: 'musteri'},
                               { data: 'planlanan_alacak_tarihi'},
                              
                               { data: 'satis_turu'},
                               { data: 'icerik'},
                               {data : 'toplam'},
                               {data : 'odenen'},
                               {data : 'kalan_tutar'},
                               {data : 'islemler' },
                       ],
                       columnDefs: [
            
                           { type: namesType, targets: 1 }
                        ],
                       "order": [[ 0, "desc" ]],
                      
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       }, 
               });
               $('#adisyon_liste_musteri').DataTable().destroy();
               $('#adisyon_liste_musteri').DataTable({
                       autoWidth: false,
                       responsive: true,
                       "processing": true,
                       "serverSide": true,
                       "deferRender": true, // DOM yükünü azaltır!
                        "ajax": {
                            "url": "/isletmeyonetim/adisyon-filtreli-getir",
                            "type": "GET",
                            "data": function (d) {
                               var sube = $('input[name="sube"]').val();
                                
                                 d.sube = sube;
                                d.tur = '';
                                d.musteri_id=$('#adisyon_musteriye_gore_filtrele').val();
                                d.tariharaligi=$('#adisyon_tarihe_gore_filtre').val();
                                
                                
                                console.log('Request parameters:', d); // Debugging line
                            },
                            "error": function(xhr, error, code) {
                                console.error('DataTables AJAX error:', error, code);
                                console.error('XHR:', xhr);
                            },
                        },
                       columns:[
                                { data: 'acilis_tarihi'},
                               
                               { data: 'planlanan_alacak_tarihi'},
                              
                               { data: 'satis_turu'},
                               { data: 'icerik'},
                               {data : 'toplam'},
                               {data : 'odenen'},
                               {data : 'kalan_tutar'},
                               {data : 'islemler' },
                       ],
                       columnDefs: [
            
                           { type: namesType, targets: 1 }
                        ],
                       "order": [[ 0, "desc" ]],
                      
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       }, 
               });
                $('#adisyon_liste_hizmet').DataTable().destroy();
               $('#adisyon_liste_hizmet').DataTable({
                       autoWidth: false,
                       responsive: true,
                       "processing": true,
                       "serverSide": true,
                       "deferRender": true, // DOM yükünü azaltır!
                        "ajax": {
                            "url": "/isletmeyonetim/adisyon-filtreli-getir",
                            "type": "GET",
                            "data": function (d) {
                               var sube = $('input[name="sube"]').val();
                                
                                 d.sube = sube;
                                d.tur = 1;
                                d.musteri_id=$('#adisyon_musteriye_gore_filtrele').val();
                                d.tariharaligi=$('#adisyon_tarihe_gore_filtre').val();
                                
                                
                                console.log('Request parameters:', d); // Debugging line
                            },
                            "error": function(xhr, error, code) {
                                console.error('DataTables AJAX error:', error, code);
                                console.error('XHR:', xhr);
                            },
                        },
                       columns:[
                        { data: 'acilis_tarihi'},
                                { data: 'musteri'},
                               { data: 'planlanan_alacak_tarihi'},
                              
                           
                               { data: 'icerik'},
                               {data : 'toplam'},
                               {data : 'odenen', visible: !odenenGizlensin},
                               {data : 'kalan_tutar'},
                               {data : 'islemler' },
                       ],
                       columnDefs: [
            
                           { type: namesType, targets: 1 }
                        ],
                       "order": [[ 0, "desc" ]],
                       
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       }, 
               });
                $('#adisyon_liste_urun').DataTable().destroy();
                $('#adisyon_liste_urun').DataTable({
                       autoWidth: false,
                       responsive: true,
                       "processing": true,
                       "serverSide": true,
                       "deferRender": true, // DOM yükünü azaltır!
                        "ajax": {
                            "url": "/isletmeyonetim/adisyon-filtreli-getir",
                            "type": "GET",
                            "data": function (d) {
                               var sube = $('input[name="sube"]').val();
                                
                                d.sube = sube;
                                d.tur = 3;
                                d.musteri_id=$('#adisyon_musteriye_gore_filtrele').val();
                                d.tariharaligi=$('#adisyon_tarihe_gore_filtre').val();
                                
                                
                                console.log('Request parameters:', d); // Debugging line
                            },
                            "error": function(xhr, error, code) {
                                console.error('DataTables AJAX error:', error, code);
                                console.error('XHR:', xhr);
                            },
                        },
                       columns:[
                        { data: 'acilis_tarihi'},
                                { data: 'musteri'},
                               { data: 'planlanan_alacak_tarihi'},
                              
                           
                               { data: 'icerik'},
                               {data : 'toplam'},
                               {data : 'odenen'},
                               {data : 'kalan_tutar'},
                               {data : 'islemler' },
                       ],
                        columnDefs: [
            
                           { type: namesType, targets: 1 }
                        ],
                        "order": [[ 0, "desc" ]],
                       
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       }, 
               });
                $('#adisyon_liste_paket').DataTable().destroy();
                 $('#adisyon_liste_paket').DataTable({
                       autoWidth: false,
                       responsive: true,
                       "processing": true,
                       "serverSide": true,
                       "deferRender": true, // DOM yükünü azaltır!
                        "ajax": {
                            "url": "/isletmeyonetim/adisyon-filtreli-getir",
                            "type": "GET",
                            "data": function (d) {
                               var sube = $('input[name="sube"]').val();
                                
                                 d.sube = sube;
                                d.tur = 2;
                                d.musteri_id=$('#adisyon_musteriye_gore_filtrele').val();
                                d.tariharaligi=$('#adisyon_tarihe_gore_filtre').val();
                                
                                
                                console.log('Request parameters:', d); // Debugging line
                            },
                            "error": function(xhr, error, code) {
                                console.error('DataTables AJAX error:', error, code);
                                console.error('XHR:', xhr);
                            },
                        },
                       columns:[
                        { data: 'acilis_tarihi'},
                                { data: 'musteri'},
                               { data: 'planlanan_alacak_tarihi'},
                              
                           
                               { data: 'icerik'},
                               {data : 'toplam'},
                               {data : 'odenen'},
                               {data : 'kalan_tutar'},
                               {data : 'islemler' },
                       ],
                       columnDefs: [
            
                           { type: namesType, targets: 1 }
                        ],
                        "order": [[ 0, "desc" ]],
                       
            
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
	});
	
	$(".month-picker").datepicker({
		minDate: new Date(),
		language: "tr",
		minView: "daysMin",
		view: "days",
		autoClose: true,
		dateFormat: "mm yyyy",
	});

	// only time picker
	$(".time-picker").timeDropper({
		minDate: new Date(),
		mousewheel: true,
		meridians: true,
		init_animation: "dropdown",
		setCurrentTime: false,
	});
	$(".time-picker-default").timeDropper();

	// var color = $('.btn').data('color');
	// console.log(color);
	// $('.btn').style('color'+color);
	$("[data-color]").each(function () {
		$(this).css("color", $(this).attr("data-color"));
	});
	$("[data-bgcolor]").each(function () {
		$(this).css("background-color", $(this).attr("data-bgcolor"));
	});
	$("[data-border]").each(function () {
		$(this).css("border", $(this).attr("data-border"));
	});

	$("#accordion-menu").vmenuModule({
		Speed: 400,
		autostart: false,
		autohide: true,
	});
});

// sidebar menu accordion
(function ($) {
	$.fn.vmenuModule = function (option) {
		var obj, item;
		var options = $.extend(
			{
				Speed: 220,
				autostart: true,
				autohide: 1,
			},
			option
		);
		obj = $(this);

		item = obj.find("ul").parent("li").children("a");
		item.attr("data-option", "off");

		item.unbind("click").on("click", function () {
			var a = $(this);
			if (options.autohide) {
				a.parent()
					.parent()
					.find("a[data-option='on']")
					.parent("li")
					.children("ul")
					.slideUp(options.Speed / 1.2, function () {
						$(this).parent("li").children("a").attr("data-option", "off");
						$(this).parent("li").removeClass("show");
					});
			}
			if (a.attr("data-option") == "off") {
				a.parent("li")
					.children("ul")
					.slideDown(options.Speed, function () {
						a.attr("data-option", "on");
						a.parent("li").addClass("show");
					});
			}
			if (a.attr("data-option") == "on") {
				a.attr("data-option", "off");
				a.parent("li").children("ul").slideUp(options.Speed);
				a.parent("li").removeClass("show");
			}
		});
		if (options.autostart) {
			obj.find("a").each(function () {
				$(this)
					.parent("li")
					.parent("ul")
					.slideDown(options.Speed, function () {
						$(this).parent("li").children("a").attr("data-option", "on");
					});
			});
		} else {
			obj.find("a.active").each(function () {
				$(this)
					.parent("li")
					.parent("ul")
					.slideDown(options.Speed, function () {
						$(this).parent("li").children("a").attr("data-option", "on");
						$(this).parent("li").addClass("show");
					});
			});
		}
	};
})(window.jQuery || window.Zepto);
(function ($) { $.fn.datepicker.language['tr'] = {
    days: ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'],
    daysShort: ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'],
    daysMin: ['Pz', 'Pt', 'Sa', 'Ça', 'Pe', 'Cu', 'Ct'],
    months: ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran', 'Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],
    monthsShort: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'],
    today: 'Bugün',
    clear: 'Temizle',
    dateFormat: 'dd.mm.yyyy',
    timeFormat: 'hh:ii',
    firstDay: 1
};
 })(jQuery);
// copy to clipboard function
function CopyToClipboard(value, showNotification, notificationText) {
	var $temp = $("<input>");
	if (value != "") {
		var $temp = $("<input>");
		$("body").append($temp);
		$temp.val(value).select();
		document.execCommand("copy");
		$temp.remove();
	}
	if (typeof showNotification === "undefined") {
		showNotification = true;
	}
	if (typeof notificationText === "undefined") {
		notificationText = "Copied to clipboard";
	}
	var notificationTag = $("div.copy-notification");
	if (showNotification && notificationTag.length == 0) {
		notificationTag = $("<div/>", {
			class: "copy-notification",
			text: notificationText,
		});
		$("body").append(notificationTag);

		notificationTag.fadeIn("slow", function () {
			setTimeout(function () {
				notificationTag.fadeOut("slow", function () {
					notificationTag.remove();
				});
			}, 1000);
		});
	}
}

// detectIE Browser
(function detectIE() {
	var ua = window.navigator.userAgent;

	var msie = ua.indexOf("MSIE ");
	if (msie > 0) {
		// IE 10 or older => return version number
		var ieV = parseInt(ua.substring(msie + 5, ua.indexOf(".", msie)), 10);
		document.querySelector("body").className += " IE";
	}

	var trident = ua.indexOf("Trident/");
	if (trident > 0) {
		// IE 11 => return version number
		var rv = ua.indexOf("rv:");
		var ieV = parseInt(ua.substring(rv + 3, ua.indexOf(".", rv)), 10);
		document.querySelector("body").className += " IE";
	}

	// other browser
	return false;
})();
