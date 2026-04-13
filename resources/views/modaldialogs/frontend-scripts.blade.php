 <script type="text/javascript">
     $(document).ready(function () {

        $('#musteri_arama').select2({
             placeholder: "Müşteri/Danışan arayın", // Placeholder ekleme
             language: {
        inputTooShort: function () {
            return "Lütfen en az 2 karakter girin.";
        },
        searching: function () {
            return "Aranıyor...";
        },
        noResults: function () {
            return "Sonuç bulunamadı.";
        },
        loadingMore: function () {
            return "Daha fazla veri yükleniyor...";
        }
    },
        ajax: {
            url: '/isletmeyonetim/musteri-arama-bolumu-verileri', 
            dataType: 'json',
            delay: 250, // 250ms bekleyerek gereksiz istekleri önle
            data: function (params) {
                return { 
                    query: params.term || '', // Eğer giriş yoksa boş string gönder
                    sube:$('input[name="sube"]').val(),
                    aramaMi:false,
                }; // Arama terimi
            },

            processResults: function (data) {
                
                return { results: data.map(musteri => ({ id: musteri.detayli_bilgi, text: musteri.ad_soyad })) };
            }

        },
        minimumInputLength: 0 // En az 2 harf girilince aramaya başla
        }); 
        $('.musteri_secimi').select2({
             placeholder: "Müşteri/Danışan seçin", 
              allowClear:true,
             language: {
        inputTooShort: function () {
            return "Lütfen en az 2 karakter girin.";
        },
        searching: function () {
            return "Aranıyor...";
        },
        noResults: function () {
            return "Sonuç bulunamadı.";
        },
        loadingMore: function () {
            return "Daha fazla veri yükleniyor...";
        }
    },
        ajax: {
            url: '/isletmeyonetim/musteri-arama-bolumu-verileri', 
            dataType: 'json',
            delay: 250, // 250ms bekleyerek gereksiz istekleri önle
            data: function (params) {
                return { 
                    query: params.term || '', // Eğer giriş yoksa boş string gönder
                    sube:$('input[name="sube"]').val(),
                    aramaMi:false,
                }; // Arama terimi
            },

            processResults: function (data) {
                
                return { results: data.map(musteri => ({ id: musteri.id, text: musteri.ad_soyad })) };
            }

        },
        minimumInputLength: 0 // En az 2 harf girilince aramaya başla
        });

         $('.personel_secimi').select2({
             placeholder: "Personel seçin",  
             allowClear:true,
             language: {
        inputTooShort: function () {
            return "Lütfen en az 2 karakter girin.";
        },
        searching: function () {
            return "Aranıyor...";
        },
        noResults: function () {
            return "Sonuç bulunamadı.";
        },
        loadingMore: function () {
            return "Daha fazla veri yükleniyor...";
        }
    },
        ajax: {
            url: '/isletmeyonetim/personel-secimi', 
            dataType: 'json',
            delay: 250, // 250ms bekleyerek gereksiz istekleri önle
            data: function (params) {
                return { 
                    query: params.term || '', // Eğer giriş yoksa boş string gönder
                    sube:$('input[name="sube"]').val(),
                    aramaMi:false,
                }; // Arama terimi
            },

            processResults: function (data) {
                
                return { results: data.map(personel => ({ id: personel.id, text: personel.ad_soyad })) };
            }

        },
        minimumInputLength: 0 // En az 2 harf girilince aramaya başla
        });

    
      $('.cihaz_secimi').select2({
             placeholder: "Cihaz seçin",  
             allowClear:true,
             language: {
        inputTooShort: function () {
            return "Lütfen en az 2 karakter girin.";
        },
        searching: function () {
            return "Aranıyor...";
        },
        noResults: function () {
            return "Sonuç bulunamadı.";
        },
        loadingMore: function () {
            return "Daha fazla veri yükleniyor...";
        }
    },
        ajax: {
            url: '/isletmeyonetim/cihaz-secimi', 
            dataType: 'json',
            delay: 250, // 250ms bekleyerek gereksiz istekleri önle
            data: function (params) {
                return { 
                    query: params.term || '', // Eğer giriş yoksa boş string gönder
                    sube:$('input[name="sube"]').val(),
                    aramaMi:false,
                }; // Arama terimi
            },

            processResults: function (data) {
                
                return { results: data.map(cihaz => ({ id: cihaz.id, text: cihaz.cihaz_adi })) };
            }

        },
        minimumInputLength: 0 // En az 2 harf girilince aramaya başla
        });

      $('.oda_secimi').select2({
             placeholder: "Oda seçin",  
             allowClear:true,
             language: {
        inputTooShort: function () {
            return "Lütfen en az 2 karakter girin.";
        },
        searching: function () {
            return "Aranıyor...";
        },
        noResults: function () {
            return "Sonuç bulunamadı.";
        },
        loadingMore: function () {
            return "Daha fazla veri yükleniyor...";
        }
    },
        ajax: {
            url: '/isletmeyonetim/oda-secimi', 
            dataType: 'json',
            delay: 250, // 250ms bekleyerek gereksiz istekleri önle
            data: function (params) {
                return { 
                    query: params.term || '', // Eğer giriş yoksa boş string gönder
                    sube:$('input[name="sube"]').val(),
                    aramaMi:false,
                }; // Arama terimi
            },

            processResults: function (data) {
                
                return { results: data.map(oda => ({ id: oda.id, text: oda.oda_adi })) };
            }

        },
        minimumInputLength: 0 // En az 2 harf girilince aramaya başla
        }); 
        $('.hizmet_secimi').select2({
             placeholder: "Hizmet seçin",  
             allowClear:true,
             language: {
        inputTooShort: function () {
            return "Lütfen en az 2 karakter girin.";
        },
        searching: function () {
            return "Aranıyor...";
        },
        noResults: function () {
            return "Sonuç bulunamadı.";
        },
        loadingMore: function () {
            return "Daha fazla veri yükleniyor...";
        }
    },
        ajax: {
            url: '/isletmeyonetim/hizmet-secimi', 
            dataType: 'json',
            delay: 250, // 250ms bekleyerek gereksiz istekleri önle
            data: function (params) {
                return { 
                    query: params.term || '', // Eğer giriş yoksa boş string gönder
                    sube:$('input[name="sube"]').val(),
                    aramaMi:false,
                }; // Arama terimi
            },

            processResults: function (data) {
                
                return { results: data.map(hizmet => ({ id: hizmet.id, text: hizmet.hizmet_adi })) };
            }

        },
        minimumInputLength: 0 // En az 2 harf girilince aramaya başla
        }); 

     });

 </script>

 @if($pageindex == 2)
         <script type="text/javascript">
            function getQueryStrings()
             {
               var vars = [], hash; //vars arrayi ve hash değişkeni tanımlıyoruz
               var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&'); //QueryString değerlerini ayıklıyoruz.
               for(var i = 0; i < hashes.length; i++)
               {
                 hash = hashes[i].split('=');
                 vars.push(hash[0]);
                 vars[hash[0]] = hash[1]; //Değerlerimizi dizimize ekliyoruz
               }
               return vars;
             }
            
            $(document).ready(function () {
            
              
            
              var tarih = getQueryStrings()["tarih"];
            
            
              if(tarih){
                 
                  tarih = new Date(tarih);
              } 
              else{
               
                  tarih = new Date();
               }
                
            
            $('#calendar').fullCalendar({
             timeZone:'Europe/Istanbul',
             slotWidth: 200,
             nowIndicator:true,
             monthNames: ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],
             monthNamesShort: ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],
             dayNames: ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'],
             dayNamesShort: ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'],
             editable:true,
             buttonText: {
                   today:    'Bugün',
                   month:    'Ay',
                   week:     'Hafta',
                   day:      'Gün',
                   list:     'Liste',
                   listMonth: 'Aylık Liste',
                   listYear: 'Yıllık Liste',
                   listWeek: 'Haftalık Liste',
                   listDay: 'Günlük Liste'
             },
             
              
              
             businessHours: <?php echo json_encode($randevular["calismasaatleri"]) ?>,
             defaultView: 'agendaDay',
             defaultDate: tarih,
             editable: true,
             selectable: true,
            
             eventLimit: true, // allow "more" link when too many events
             header: {
               left: 'prev,next today',
               center: 'title',
               right: 'month,agendaWeek,agendaDay'
             },
             minTime: <?php echo json_encode($randevular["baslangic"]) ?>,
             maxTime: <?php echo json_encode($randevular["bitis"]) ?>,
             //// uncomment this line to hide the all-day slot
             allDaySlot: false,
             slotDuration: '00:15:00',
             height:768,
             resources: <?php echo json_encode($randevular["resource"]) ?>,
             events: <?php echo json_encode($randevular["randevu"])?>,
             timeFormat: 'H:mm',
             views: {
                 agenda: {
                     slotLabelFormat: 'H:mm',
                 }
             },
             moreLinkContent:function(args){
                return '+'+args.num+' Randevu Daha';
             },
             select: function(start, end, jsEvent, view, resource) {
               console.log(
                 'select',
                 start.format(),
                 end.format(),
                 resource ? resource.id : '(no resource)'
               );
               var tarihsaattext = start.format().split("T");
             },
             dayClick: function (start) {
               var tarihsaattext = start.format().split("T");
              
              
                  
               if(new Date(start.format()) < new Date())
                     swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text: 'Geçmiş tarih / saat için randevu oluşturulamaz!',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                            }
                     );
               else{
                   
                  $('#randevutarihiyeni').val(tarihsaattext[0]);
            
                  randevusaatlerinigetir(tarihsaattext[0],$('input[name="sube"]').val(),tarihsaattext[1]);
                  
                   
            
                  jQuery("#modal-view-event-add").modal();
               }
            
             },
            
             eventResize: function(event) {
               clearInterval(interval);
               eventGuncelle(event);
             },
             eventDrop: function(event, delta, revertFunc) {
               
                console.log('Drop');
                console.log(event);
                eventGuncelle(event);
                
            
             },
             eventDragStart: function (event, jsEvent, view){
               clearInterval(interval);
               console.log('Event drag start');
               console.log(event);
             },
             eventDragStop: function( event, jsEvent, view){
                console.log('drag stop');
                console.log(event);
                //interval = setInterval(takvimyukle.bind(false), 10000);
             },
             resourceChange: function(resource, oldResource, revert){
            
               console.log('resource change');
               console.log(resource)
            },
             eventClick: function (event, jsEvent, view) {
                var randevuid = event.randevu_id;
                if(event.userid==2012)
                {
                    swal({
                        title: "Emin misiniz?",
                        text: "Bu kapalı saat kaydını silmek istediğinize emin misiniz? Bu işlem geri alınamaz",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#00bc8c',
                        confirmButtonText: 'Saat Kapamayı Kaldır',
                        cancelButtonText: "Vazgeç",
                        confirmButtonClass: 'btn btn-success',
                        cancelButtonClass: 'btn btn-danger',
                               
                    }).then(function (result) {
                        
                        if(result.value){
                            kapalisaatsil(randevuid);
                                
                            
            
                        }
                     
                        
                              
                    
                    });
                }
               else{
                  jQuery(".event-icon").html("<i class='fa fa-" + event.icon + "'></i>");
                  jQuery(".event-title").html(event.modal_title);
                  jQuery(".event-body").html(event.description);
                  jQuery(".event-buttons").html(event.eventbuttons);
                  jQuery('#duzenle_butonu_bolumu').html(event.duzenle_buton);
                  jQuery(".eventUrl").attr("href", event.url);
                  jQuery('input[name="randevuhizmettarih"]').datepicker({
                     minDate: new Date(),
                     timepicker: true,
                     language: "tr",
                     autoClose: true,
                     dateFormat: "yyyy-mm-dd",
                     timeFormat:  "hh:mm",
                    
            
                  });
            
                  jQuery("#modal-view-event").modal();
               }
              
             },
             
            });

           $('.fc-axis.fc-widget-header').attr('style','width:43px');
            if($('.fc-resource-cell').width()<300)    
            {
               $('.fc-view-container').attr('style','overflow-x:scroll;');
               $('.fc-resource-cell').attr('style','width:300px');
               var newwidth=  Number($('.fc-resource-cell').length*300) + Number(95);   
               $('.fc-agendaDay-view').attr('style','width:'+newwidth+'px');
               
               
            }
            else
                $('.fc-view-container').attr('style','overflow-x:scroll');
            
            
            
            $(document).on('click','.fc-header-toolbar button',function(){
            
                  
                     
                  var view = $('#calendar').fullCalendar('getView');
      
                  if(view.type=='agendaDay'){
                     <?php $headdata = json_decode($randevular['resource'],true); ?>
                  <?php foreach($headdata as $key=>$res){ ?>
                     $('.fc th:nth-child('+<?php echo $key+2 ;?>+'n)').css({'background':'<?php echo $res['bgcolor']; ?>','color':'#fff'});
                      
                  <?php } ?>
                  }  
                  takvimyukle(true,false);
                 
                  
            });
  
            
            <?php $headdata = json_decode($randevular['resource'],true); ?>
            <?php foreach($headdata as $key=>$res){ ?>
               
               $('.fc th:nth-child(<?php echo $key+2 ;?>n)').css({'background':'<?php echo $res['bgcolor']; ?>','color':'#fff'});
               
            <?php } ?>
            });
         </script>
         @endif
         @if($pageindex==4)
         <script>
            $(document).ready(function(){
               if($('#musteri_tablo').length){
                    var sadiktablo = $('#musteri_tablo_sadik').DataTable({
                          
                        autoWidth: false,
                       responsive: true,
                       "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": "/isletmeyonetim/musterilistegetir/2",
                            "type": "GET",
                            "data": function (d) {
                               var sube = $('input[name="sube"]').val();
                                
                                d.sube = sube;
                                
                                
                                console.log('Request parameters:', d); // Debugging line
                            },
                            "error": function(xhr, error, code) {
                                console.error('DataTables AJAX error:', error, code);
                                console.error('XHR:', xhr);
                            },
                        },
                       columns:[
                           { data: 'ad_soyad',name: 'ad_soyad' },
                           { data: 'telefon' ,name: 'telefon'},
                           { data: 'kayit_tarihi',name: 'kayit_tarihi' }, 
                           { data: 'son_randevu_tarihi',name: 'son_randevu_tarihi' },
                           { data: 'randevu_sayisi',name: 'randevu_sayisi' },  
                           {data:'odenen'},  
                           {data : 'islemler'},
                       ],
                      
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       },
            
                        
                      
            
                     });
                     var musteritablo = $('#musteri_tablo').DataTable({
                        autoWidth: false,
                        responsive: true,
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": "/isletmeyonetim/musterilistegetir/3",
                            "type": "GET",
                            "data": function (d) {
                               var sube = $('input[name="sube"]').val();
                                
                                d.sube = sube;
                                
                                
                                console.log('Request parameters:', d); // Debugging line
                            },
                            "error": function(xhr, error, code) {
                                console.error('DataTables AJAX error:', error, code);
                                console.error('XHR:', xhr);
                            },
                        },
                        columns:[
                              { data: 'ad_soyad',name: 'ad_soyad' },
                              { data: 'telefon' ,name: 'telefon'},
                              { data: 'kayit_tarihi',name: 'kayit_tarihi' }, 
                              { data: 'son_randevu_tarihi',name: 'son_randevu_tarihi' },
                              { data: 'randevu_sayisi',name: 'randevu_sayisi' },
                              {data:'odenen'},    
                              {data : 'islemler'},
                        ],
                        
               
                        "language" : {
                              "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                              searchPlaceholder: "Ara",
                              paginate: {
                                  next: '<i class="ion-chevron-right"></i>',
                                  previous: '<i class="ion-chevron-left"></i>'  
                              }
                          },
            
                        
                      
            
                     });
                     var aktiftablo = $('#musteri_tablo_aktif').DataTable({
                          
                        autoWidth: false,
                        responsive: true,
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": "/isletmeyonetim/musterilistegetir/1",
                            "type": "GET",
                            "data": function (d) {
                               var sube = $('input[name="sube"]').val();
                                
                                d.sube = sube;
                                
                                
                                console.log('Request parameters:', d); // Debugging line
                            },
                            "error": function(xhr, error, code) {
                                console.error('DataTables AJAX error:', error, code);
                                console.error('XHR:', xhr);
                            },
                        },
                        columns:[
                           { data: 'ad_soyad',name: 'ad_soyad' },
                           { data: 'telefon' ,name: 'telefon'},
                           { data: 'kayit_tarihi',name: 'kayit_tarihi' }, 
                           { data: 'son_randevu_tarihi',name: 'son_randevu_tarihi' },
                           { data: 'randevu_sayisi',name: 'randevu_sayisi' }, 
                           {data:'odenen'},   
                           {data : 'islemler'},
                        ],
                      
            
                        "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                        },
            
                        
                      
            
                     });
                     var pasiftablo = $('#musteri_tablo_pasif').DataTable({
                          
                        autoWidth: false,
                       responsive: true,
                       "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": "/isletmeyonetim/musterilistegetir/0",
                            "type": "GET",
                            "data": function (d) {
                               var sube = $('input[name="sube"]').val();
                                
                                d.sube = sube;
                                
                                
                                console.log('Request parameters:', d); // Debugging line
                            },
                            "error": function(xhr, error, code) {
                                console.error('DataTables AJAX error:', error, code);
                                console.error('XHR:', xhr);
                            },
                        },
                        columns:[
                           { data: 'ad_soyad',name: 'ad_soyad' },
                           { data: 'telefon' ,name: 'telefon'},
                           { data: 'kayit_tarihi',name: 'kayit_tarihi' }, 
                           { data: 'son_randevu_tarihi',name: 'son_randevu_tarihi' },
                           { data: 'randevu_sayisi',name: 'randevu_sayisi' },  
                           { data:'odenen'},  
                           { data: 'islemler'},
                        ],
                        
            
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
               });
              
             
         </script>
         @endif
         @if($pageindex==40)
         <script type="text/javascript">
            $(document).ready(function(){
                 $('#ajanda_liste').DataTable({
                    autoWidth: false,
                     responsive: true,
                      "order": [[ 4, "desc" ]],
                      columns:[
                        
                         { data: 'title'   },
                         { data: 'description' },
                           
                         { data: 'ajanda_hatirlatma'   },
                       
            
                       
                         { data: 'start' },
                            
                         { data: 'ajanda_durum' },
            
                        
                         { data: 'ajanda_olusturan' },
                        
                        
                         { data: 'islemler' }
                          
                     
                         
                      ],
                      data: <?php echo  $ajanda['ajanda']; ?>,
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                       },
                 });
            });
            
            
         </script>
         <script src="{{asset('public/yeni_panel/src/plugins/fullcalendar/fullcalendar.min.js')}}"></script>
         <script src="{{asset('public/yeni_panel/vendors/scripts/calendar-setting.js')}}"></script>
         <script type="text/javascript">
            function getQueryStrings()
             {
               var vars = [], hash; //vars arrayi ve hash değişkeni tanımlıyoruz
               var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&'); //QueryString değerlerini ayıklıyoruz.
               for(var i = 0; i < hashes.length; i++)
               {
                 hash = hashes[i].split('=');
                 vars.push(hash[0]);
                 vars[hash[0]] = hash[1]; //Değerlerimizi dizimize ekliyoruz
               }
               return vars;
             }
            
            $(document).ready(function () {
            
            
            
              var tarih = getQueryStrings()["ajanda_tarih"];
            
            
              if(tarih){
                 
                  tarih = new Date(tarih);
              } 
              else{
               
                  tarih = new Date();
               }
            
            $('#calendar_ajanda').fullCalendar({
             timeZone:'Europe/Istanbul',
            
            dayClick: function (start) {
               var tarihsaattext = start.format().split("T");
              
                  
               if(new Date(start.format()) < new Date())
                     swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text: 'Geçmiş tarih / saat için ajanda oluşturulamaz!',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                            }
                     );
               else{
                   
                 $('#ajandatarih').val(start.format('YYYY-MM-DD'));
                    $('#ajandasaat').val(start.format('HH:mm'));
                  
                   
            
                  jQuery("#yeni_ajanda_ekle").modal();
               }
            
             },
             nowIndicator:true,
             monthNames: ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],
             monthNamesShort: ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],
             dayNames: ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'],
             dayNamesShort: ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'],
             editable:true,
             buttonText: {
                   today:    'Bugün',
                   month:    'Ay',
                   week:     'Hafta',
                   day:      'Gün',
                   list:     'Liste',
                   listMonth: 'Aylık Liste',
                   listYear: 'Yıllık Liste',
                   listWeek: 'Haftalık Liste',
                   listDay: 'Günlük Liste'
             },
            
            
             defaultView: 'agendaWeek',
             defaultDate: tarih,
             editable: false,
             selectable: true,
             events:<?php echo json_encode($ajanda['ajanda'])?>,
             eventLimit: true, // allow "more" link when too many events
             header: {
               left: 'prev,next today',
               center: 'title',
               right: 'month,agendaWeek,agendaDay'
             },
            minTime: '06:00:00',
             //// uncomment this line to hide the all-day slot
            allDaySlot: false,
               slotDuration: '00:15:00',
             
            contentHeight: 600,   
            
             timeFormat: 'H:mm',
             views: {
                 agenda: {
                     slotLabelFormat: 'H:mm',
                 }
             },
            
            
             businessHours: false,
             
            select: function(start, end, jsEvent, view) {
               console.log(
                 'select',
                 start.format(),
                 end.format(),
            
              
               );
               
             },
            
            
             eventClick:function(event,jsEvent, view){
               updateState(event.id);
               jQuery(".event-icon").html("<i class='fa fa-" + event.icon + "'></i>");
               jQuery(".event-title").html(event.title+" Not Detayı");
               jQuery(".event-body").html("<div class='row' ><b style='margin-left:20px;'>İçerik :</b> <p style='margin-left:20px;'>"+event.description+"</p></div> <div class='row' ><b style='margin-left:20px;'>Tarih :</b> <p style='margin-left:23px;'>"+event.start.format('DD/MM/YYYY')+"</p></div> </div> <div class='row' ><b style='margin-left:20px;'>Saat :</b> <p style='margin-left:30px;'>"+event.start.format('H:mm')+"</p></div>");
            
               jQuery(".event-buttons").html(event.eventbuttons);
               jQuery(".eventUrl").attr("href", event.url);
               jQuery("#ajandadetayigetir").trigger('click');
             }
            
            
             
            });
            if($('.fc-day-header').width()<300)    
            {
            
               $('.fc-view-container').attr('style','overflow-x:scroll;');
                          $('.fc-axis.fc-widget-header').attr('style','width:44px');
               $('.fc-day-header').attr('style','width:300px');
               var newwidth=  Number($('.fc-day-header').length*300) + Number(90);   
               $('.fc-agendaDay-view').attr('style','width:'+newwidth+'px');
               $('.fc-agendaWeek-view').attr('style','width:'+newwidth+'px');
               
               
            }
            else
                $('.fc-view-container').attr('style','overflow-x:scroll');
             $('.fc-axis.fc-widget-header').attr('style','width:44px');
             
             
            
            
            
            
            });
         </script>
         @endif
         @if($pageindex==41)
         <script type="text/javascript">
            $(document).ready(function () {
               if($('#randevu_liste').length)
                  $('#randevu_liste').DataTable({
                                autoWidth: false,
                                responsive: true,
                                "order": [[ 4, "asc" ]],
                                columns:[
                                    
                                    { data: 'tarih' },
                                   
                                    { data: 'saat' },
                                    { data: 'durum' }, 
                                   
                                    { data: 'hizmetler'   },  
                                    { data: 'olusturan' },
                                    { data: 'olusturulma' },
                                
                                    { data: 'islemler' }
                                      
                                 
                                     
                                ],
                                data: <?php echo $randevular_liste; ?>,
                        
                                "language" : {
                                      "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                      searchPlaceholder: "Ara",
                                      paginate: {
                                          next: '<i class="ion-chevron-right"></i>',
                                          previous: '<i class="ion-chevron-left"></i>'  
                                      }
                                },
                            });
            
               
            });
         </script>
         @endif 
         @if($pageindex==11 )
         <script type="text/javascript">
            $(document).ready(function () {
               var namesType = $.fn.dataTable.absoluteOrder( [
                     { value: null, position: 'bottom' }
                     ] );
                 $.fn.dataTable.moment('DD.MM.YYYY');
               
               var adisyontablo = $('#adisyon_liste').DataTable({
                       autoWidth: false,
                       responsive: true,
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
                       "order": [[ 1, "asc" ]],
                       data: <?php echo $adisyonlar; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       }, 
               });
               var adisyontablo2 = $('#adisyon_liste_hizmet').DataTable({
                       autoWidth: false,
                       responsive: true,
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
                       "order": [[ 1, "asc" ]],
                       data: <?php echo $adisyonlar_hizmet; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       }, 
               });
                var adisyontablo3 = $('#adisyon_liste_urun').DataTable({
                       autoWidth: false,
                       responsive: true,
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
                        "order": [[ 1, "asc" ]],
                       data: <?php echo $adisyonlar_urun; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       }, 
               });
                 var adisyontablo4 = $('#adisyon_liste_paket').DataTable({
                       autoWidth: false,
                       responsive: true,
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
                        "order": [[ 1, "asc" ]],
                       data: <?php echo $adisyonlar_paket; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       }, 
               });
                 
            $('#tum_taksitler').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $tum_taksitler; ?>,
            
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
            
                     
                   
            
            });
            
            
            $('#acik_taksitler').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $taksitler_acik; ?>,
            
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
            
                     
                   
            
            });
            
            
            $('#kapali_taksitler').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $taksitler_kapali; ?>,
            
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
            
                     
                   
            
            });
            
            
            $('#gecikmis_taksitler').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $taksitler_odenmemis; ?>,
            
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
            
                     
                   
            
            });
             
            });
         </script>
         @endif
         @if($pageindex==105 )
         <script type="text/javascript">
            $(document).ready(function () {
               $('#adisyon_liste_personel').DataTable().destroy();
               var adisyontablo = $('#adisyon_liste_personel').DataTable({
                       autoWidth: false,
                       responsive: true,
                       columns:[
                           { data: 'acilis_tarihi'},
                           { data: 'musteri'},
                           { data: 'satis_turu'},
                           { data: 'icerik'},
                           
                          
                           {data : 'toplam'},
                           {data : 'odenen'},  
                           {data : 'kalan_tutar'},
                           {data : 'hakedis'},
                       ],
                       "order": [[ 0, "asc" ]],
                       data: <?php echo $adisyonlar; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       },
            
                        
                      
            
               });
             
            });
         </script>
         @endif
         @if($pageindex==41 )
         <script type="text/javascript">
            $(document).ready(function () {
               $('#adisyon_liste_musteri').DataTable().destroy();
               $('#adisyon_liste_musteri').DataTable({
                       autoWidth: false,
                       responsive: true,
                       columns:[
                           { data: 'acilis_tarihi'},
                           { data: 'planlanan_alacak_tarihi'},
                           { data: 'satis_turu'},
                           { data: 'icerik'},
                           
                          
                           {data : 'toplam'},
                           {data : 'odenen'},  
                           {data : 'kalan_tutar'},
                           {data : 'islemler'},
                       ],
                       "order": [[ 0, "asc" ]],
                       data: <?php echo $adisyonlar; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       },
            
                        
                      
            
               });
            });
         </script>
         @endif 
         @if($pageindex==13)
         <script type="text/javascript">
            $(document).ready(function(){
                  $('#paket_liste').DataTable().destroy();
                  $('#paket_liste').DataTable({
                          autoWidth:false,
                          responsive:true,
                           
                       columns:[
                       {data:'id'},
                           { data: 'paket_adi' },
                           
                           { data: 'hizmetler' }, 
                           { data: 'seanslar' }, 
                           { data: 'fiyat' },
                               
                           {data : 'islemler'},
                       ],
                       data: <?php echo $paketler["paket_liste"]; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       },
            
                        
                      
            
               });
            });
            
         </script>
         @endif
         @if($pageindex==30)
         <script type="text/javascript">
            $(document).ready(function(){
               $('#urun_liste').DataTable().destroy();
               $('#urun_liste').DataTable({
                          autoWidth:false,
                          responsive:true,
                           
                       columns:[
                       {data:'id'},
                              { data: 'urun_adi',name: 'urun_adi' },
                              { data: 'stok_adedi' ,name: 'stok_adedi'},
            
                              { data: 'fiyat',name: 'fiyat' }, 
                              { data: 'barkod',name: 'barkod' },
                               { data: 'dusuk_stok_siniri'}, 
                              {data : 'islemler'},
                       ],
                       data: <?php echo $urunler["urun_liste"]; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       },
            
                        
                      
            
               });
             });
              
         </script>
         @endif
         @if($pageindex==6 ||$pageindex == 9 )
         <script>
            $(document).ready(function(){
               
               
               $('#hizmet_liste').DataTable().destroy();
               $('#hizmet_liste').DataTable({
                          autoWidth:false,
                          responsive:true,
                           
                       columns:[
                           { data: 'hizmet_adi' },
                           { data: 'personel' },
                           { data: 'islemler' }, 
                            
                       ],
                       data: <?php echo $hizmetler["hizmet_liste"] ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       },
            
                        
                      
            
               });
            });
         </script>
         @endif
         @if($pageindex==1 || $pageindex==60)
          <script type="text/javascript">
            $(document).ready(function(){
                $('#bugunkugorevtablo').DataTable({
                     autoWidth: false,
                    responsive: true,
                       columns:[
                            { data: 'baslik'},
                            { data: 'mesaj' ,"width": "500px" },
                            { data: 'saat'  },
                            { data: 'durum' },
                            { data: 'sonuc' }, 
                            { data: 'islemler' }, 
                       ],
                       "order": [[ 2, "asc" ]],
                       data: <?php echo $easistandata; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                     },
               });
            });
        
            $(document).ready(function(){
                $('#yarinkigorevtablo').DataTable({
                     autoWidth: false,
                    responsive: true,
                       columns:[
                            { data: 'baslik'},
                            { data: 'mesaj' ,"width": "500px" },
                            { data: 'saat'  },
                            { data: 'durum' },
                            { data: 'islemler' }, 
                       ],
                       "order": [[ 2, "asc" ]],
                       data: <?php echo $easistanYarinYapilacak; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                     },
               });
            });
        </script>
        @endif
        @if($pageindex==1)
        <script type="text/javascript">
      $(document).ready(function(){
               
               $('#adisyon_liste_ozet_urun').DataTable({
                       autoWidth: false,
                       responsive: true,
                       columns:[
                           
                           { data: 'musteri'},
                           { data: 'icerik' }, 
                           { data: 'urun_satan'},
                           { data: 'toplam' },
                           {data : 'islemler'},
                       ],
                       "order": [[ 0, "asc" ]],
                       data: <?php echo $gunluk_urun_satislari; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       },
            
                        
                      
            
               });
             
                 $('#on_gorusme_liste_gunluk').DataTable({
                    autoWidth: false,
                     responsive: true,
                       "order": [[ 1, "asc" ]],
                      columns:[
                         {data:'id'},
                          { data: 'olusturulma'   },
                          { data: 'musteri'   },
                          { data: 'musteri_tipi'   },
            
                          { data: 'telefon' },
                           
                          
                          { data: 'hatirlatma' },
                       
                           { data: 'paket' },
                            
                              { data: 'gorusmeyiyapan' },
            
                             { data: 'durum' },
                              { data: 'islemler' },
                          
                     
                         
                      ],
                      data: <?php echo $on_gorusmeler; ?>,
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                    },
              });
            });
                $('#adisyon_liste_ozet_paket').DataTable({
                       autoWidth: false,
                       responsive: true,
                       columns:[
                           
                           { data: 'musteri'},
                           { data: 'icerik' }, 
                           { data: 'paket_satan'},
                           { data: 'toplam' },
                           {data : 'islemler'},
                       ],
                       "order": [[ 0, "asc" ]],
                       data: <?php echo $gunluk_paket_satislari; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       },
            
                        
                      
            
               });
            
              
               
              
               $('#randevu_liste_tum').DataTable({
                           autoWidth: false,
                           responsive: true,
                           "order": [[ 3, "asc" ]],
                           columns:[
                         
                              { data: 'musteri'   },
                              { data: 'telefon' },
                                
                              { data: 'hizmetler'   },
            
                              { data: 'tarih' },
                            
                              { data: 'saat' },
                                 
                              { data: 'durum' },
            
                             
                              { data: 'olusturan' },
                             
                             
                              { data: 'islemler' }
                               
                          
                              
                           ],
                           data: <?php echo $randevular_tum; ?>,
            
                           "language" : {
                               "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                               searchPlaceholder: "Ara",
                               paginate: {
                                   next: '<i class="ion-chevron-right"></i>',
                                   previous: '<i class="ion-chevron-left"></i>'  
                           }
                        },
                  });
                   $('#randevu_liste_salon').DataTable({
                           autoWidth: false,
                           responsive: true,
                           "order": [[ 3, "asc" ]],
                           columns:[
                         
                              { data: 'musteri'   },
                              { data: 'telefon' },
                                
                              { data: 'hizmetler'   },
                              { data: 'tarih' },
                            
                              { data: 'saat' },
                                 
                              { data: 'durum' },
            
                             
                              { data: 'olusturan' },
                             
                             
                              { data: 'islemler' }
                               
                          
                              
                           ],
                           data: <?php echo $randevular_salon; ?>,
            
                           "language" : {
                               "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                               searchPlaceholder: "Ara",
                               paginate: {
                                   next: '<i class="ion-chevron-right"></i>',
                                   previous: '<i class="ion-chevron-left"></i>'  
                           }
                        },
                  });   
                   $('#randevu_liste_web').DataTable({
                           autoWidth: false,
                           responsive: true,
                           "order": [[ 3, "asc" ]],
                           columns:[
                         
                              { data: 'musteri'   },
                              { data: 'telefon' },
                                
                              { data: 'hizmetler'   },
                              { data: 'tarih' },
                            
                              { data: 'saat' },
                                 
                              { data: 'durum' },
            
                             
                              { data: 'olusturan' },
                             
                             
                              { data: 'islemler' }
                               
                          
                              
                           ],
                           data: <?php echo $randevular_web; ?>,
            
                           "language" : {
                               "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                               searchPlaceholder: "Ara",
                               paginate: {
                                   next: '<i class="ion-chevron-right"></i>',
                                   previous: '<i class="ion-chevron-left"></i>'  
                           }
                        },
                  });  
                   $('#randevu_liste_uygulama').DataTable({
                           autoWidth: false,
                           responsive: true,
                           "order": [[ 3, "asc" ]],
                           columns:[
                         
                              { data: 'musteri'   },
                              { data: 'telefon' },
                                
                              { data: 'hizmetler'   },
                              { data: 'tarih' },
                            
                              { data: 'saat' },
                                 
                              { data: 'durum' },
            
                             
                              { data: 'olusturan' },
                             
                             
                              { data: 'islemler' }
                               
                          
                              
                           ],
                           data: <?php echo $randevular_uygulama; ?>,
            
                           "language" : {
                               "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                               searchPlaceholder: "Ara",
                               paginate: {
                                   next: '<i class="ion-chevron-right"></i>',
                                   previous: '<i class="ion-chevron-left"></i>'  
                           }
                        },
                  });       
         </script>
         @endif
         @if($pageindex==9)
         <script type="text/javascript">
            $(document).ready(function(){
               if($('#personel_tablo').length){
                 $('#personel_tablo').DataTable({
                    autoWidth: false,
                     responsive: true,
                     
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                       }
                    },
                     columns:[
                         {data:'ad_soyad'},
                          { data: 'hesap_turu'   },
                          { data: 'telefon' },
                           { data: 'durum'},
                           { data: 'islemler'   },
                        
                          
                     
                         
                      ],
                      data: <?php echo $personeller;?>,
                 });
             }
            });
             var hizmet_sec_tablo = $('#hizmet_sec_tablo').DataTable({
                    autoWidth:false,
                    responsive:true,
                    paging:false,
                    ordering: false,
                    "dom":"lrtip",
            
                    "drawCallback": function() {
                       $(this.api().table().header()).hide();
                    },  
            
                    "language" : {
                    "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                    },
            
                       
                     
            
              });
            
             $('#hizmet_ara').keyup(function(){  
              hizmet_sec_tablo.search($(this).val()).draw();   // this  is for customized searchbox with datatable search feature.
            });
             $('#ozel_hizmet_kategorileri').DataTable({
                    autoWidth:false,
                    responsive:true,
                    paging:false,
                    ordering: false,
                    "dom":"lrtip",
            
                    "drawCallback": function() {
                       $(this.api().table().header()).hide();
                    },  
            
                    "language" : {
                    "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                    },
            
                       
                     
            
              });
            
            
            
         </script>
         @endif
         @if($pageindex==12)
         <script type="text/javascript">
            $(document).ready(function(){
                 $('#on_gorusme_liste').DataTable({
                    autoWidth: false,
                     responsive: true,
                       "order": [[ 1, "desc" ]],
                      columns:[     
                         {data:'id'},
                          { data: 'olusturulma'   },
                          { data: 'musteri'   },
                          { data: 'musteri_tipi'   },
            
                          { data: 'telefon' },
                           
                          
                          { data: 'hatirlatma' },
                       
                           { data: 'paket' },
                            
                              { data: 'gorusmeyiyapan' },
            
                             { data: 'durum' },
                              { data: 'islemler' },
                          
                     
                         
                      ],
                      data: <?php echo $on_gorusmeler; ?>,
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                    },
              });
            });
            
         </script>
         @endif
         @if($pageindex==14)
         <script type="text/javascript">
            $(document).ready(function(){
                 $('#seans_takip_liste').DataTable({
                    autoWidth: false,
                     responsive: true,
                      columns:[
                         { data: 'musteri'   },
                         { data: 'baslangic_tarihi' },
                           
                         { data: 'paket_adi'   },
                         { data: 'durum', "width": "250px"},
                       
                         
                            { data: 'islemler' }
                          
                     
                         
                      ],
                      data: <?php echo $seanstakip; ?>,
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                    },
              });
            });
            
         </script>
         @endif
         @if($pageindex==3)
         <script type="text/javascript">
            $(document).ready(function(){
                 $('#randevu_liste').DataTable({
                    autoWidth: false,
                     responsive: true,
                      "order": [[ 4, "desc" ]],
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
                      data: <?php echo $randevular_liste; ?>,
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                       },
                 });
            });
            
         </script>
         @endif
         @if($pageindex==15)
         <script type="text/javascript">
            $(document).ready(function(){
                 $('#masraf_tablo').DataTable({
                    autoWidth: false,
                     responsive: true,
                      columns:[
                        
                         { data: 'tarih'   },
                         { data: 'kategori' },
                           
                         { data: 'aciklama'   },
                          { data: 'tutar'   },
                         { data: 'masraf_sahibi' },
                       
                        
                            
                         { data: 'odeme_yontemi' },
            
                        
                       
                        
                        
                         { data: 'islemler' }
                          
                     
                         
                      ],
                      data: <?php echo $masraflar; ?>,
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
            });
            
         </script>
         @endif
         @if($pageindex==16)
         <script type="text/javascript">
            $(document).ready(function(){
                 $('#alacaklar').DataTable({
                    autoWidth: false,
                     responsive: true,
                      "order": [[ 0, "dsc" ]],
                      columns:[ 
                       { data: 'olusturulma' },  
                         { data: 'musteri'   },
                          { data: 'icerik'   },
                         { data: 'tutar' }, 
                         { data: 'planlanan_odeme_tarihi'   },
                          
                        
                         { data: 'islemler' }  
                      ],
                      data: <?php echo $alacaklar['alacaklar']; ?>,
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                       },
                 });
            
              
                 $('#alacaklar_hizmet').DataTable({
                    autoWidth: false,
                     responsive: true,
                      "order": [[ 0, "dsc" ]],
                      columns:[ 
                       { data: 'olusturulma' },  
                         { data: 'musteri'   },
                          { data: 'icerik'   },
                         { data: 'tutar' }, 
                         { data: 'planlanan_odeme_tarihi'   },
                          
                        
                         { data: 'islemler' }  
                      ],
                     data: <?php echo $alacaklar['alacaklar_hizmet']; ?>,
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                       },
                 });
            
                
                 $('#alacaklar_urun').DataTable({
                    autoWidth: false,
                     responsive: true,
                      "order": [[ 0, "dsc" ]],
                      columns:[ 
                       { data: 'olusturulma' },  
                         { data: 'musteri'   },
                          { data: 'icerik'   },
                         { data: 'tutar' }, 
                         { data: 'planlanan_odeme_tarihi'   },
                          
                        
                         { data: 'islemler' }  
                      ],
                     data: <?php echo $alacaklar['alacaklar_urun']; ?>,
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                       },
                 });
            
                  
                 $('#alacaklar_paket').DataTable({
                    autoWidth: false,
                     responsive: true,
                      "order": [[ 0, "dsc" ]],
                      columns:[ 
                       { data: 'olusturulma' },  
                         { data: 'musteri'   },
                          { data: 'icerik'   },
                         { data: 'tutar' }, 
                         { data: 'planlanan_odeme_tarihi'   },
                          
                        
                         { data: 'islemler' }  
                      ],
                     data: <?php echo $alacaklar['alacaklar_paket']; ?>,
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                       },
                 });
            
            
            $('#tum_taksitler').DataTable().destroy();
            $('#tum_taksitler').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $alacaklar['tum_taksitler']; ?>,
            
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
            
                     
                   
            
            });
            
            $('#acik_taksitler').DataTable().destroy();
            $('#acik_taksitler').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $alacaklar['taksitler_acik']; ?>,
            
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
            
                     
                   
            
            });
            
            $('#kapali_taksitler').DataTable().destroy();
            $('#kapali_taksitler').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $alacaklar['taksitler_kapali']; ?>,
            
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
            
                     
                   
            
            });
            
            $('#gecikmis_taksitler').DataTable().destroy();
            $('#gecikmis_taksitler').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $alacaklar['taksitler_odenmemis']; ?>,
            
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
            
                     
                   
            
            });
             });
         </script>
         @endif
         <script type="text/javascript">
            $(document).ready(function(){
                
              
              // $('#masraf_duzenle_modal').DataTable();
              var musteri_sec_tablo = $('#musteri_sec_tablo').DataTable({
                     autoWidth:false,
                     responsive:true,
                     paging:false,  
                     "language" : {
                     "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                     },
            
                        
                      
            
               });
              $('#katilimci_ara').keyup(function(){  
               musteri_sec_tablo.search($(this).val()).draw();   // this  is for customized searchbox with datatable search feature.
             });
            
            });
         </script>
         