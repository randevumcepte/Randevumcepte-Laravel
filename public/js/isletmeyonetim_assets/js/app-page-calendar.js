var App = (function () {
  'use strict';
  
  App.pageCalendar = function( ){


    /* initialize the external events
    -----------------------------------------------------------------*/

    $('#external-events .fc-event').each(function() {

      // store data so the calendar knows to render an event upon drop
      $(this).data('event', {
        title: $.trim($(this).text()), // use the element's text as the event title
        stick: true // maintain when user navigates (see docs on the renderEvent method)
      });

      // make the event draggable using jQuery UI
      $(this).draggable({
        zIndex: 999,
        revert: true,      // will cause the event to go back to its
        revertDuration: 0  //  original position after the drag
      });

    });
     $.ajax({
         type: "GET",
        url: '/isletmeyonetim/randevuyukle', 
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
          $('#preloader').show();
        }
       success: function(result)  { 
             $('#preloader').hide();
               $('#calendar').fullCalendar({
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
              lang: 'tr',
      header: {
        left: 'title',
        center: '',
        right: 'month,agendaWeek,agendaDay, today, prev,next',
      },
      defaultDate: new Date(),
      editable: true,
      eventLimit: true,
      droppable: true, // this allows things to be dropped onto the calendar
       eventClick: function(event){
        
         
         $('input[name="randevualan"]').attr('value', event.title);
         $('input[name="randevutarihi"]').attr('value', event.start.format('YYYY-MM-DD'));
         $('input[name="randevusaatibaslangic"]').attr('value', event.start.format('HH:mm'));
         $('input[name="randevusaatibitis"]').attr('value', event.end.format('HH:mm'));
         $('input[name="telefonev"]').attr('value',event.telefonev);
         $('input[name="telefoncep"]').attr('value',event.telefoncep);
         $('input[name="eposta"]').attr('value',event.eposta);
         $('input[name="randevuhizmetler"]').attr('value',event.hizmet);
         $('input[name="randevupersoneller"]').attr('value',event.personel);
         $('input[name="randevuid"]').attr('value',event.id);
         if(event.color=="#34a853" )
            $('#randevuonayla').attr('style','display:none');
          if(event.color=="#FF0000")
            $('#randevuislemleri').attr('style','display:none');
          $('#randevudetayigetir').trigger('click');

         
          

        },
       eventDrop: function(event,delta){
          //confirm('move exist event'+ event.start + '-' +event.end);
          saveEvent(event);
      },
       eventResize: function(event,delta){
          //confirm('move exist event'+ event.start + '-' +event.end);
          saveEvent(event);
      },
      drop: function() {
        // is the "remove after drop" checkbox checked?
        if ($('#drop-remove').is(':checked')) {
          // if so, remove the element from the "Draggable Events" list
          $(this).remove();
        }
      },
      events: result,
    });
          
           
              
        },
        error: function (request, status, error) {
            
             document.getElementById('hata').innerHTML = request.responseText;
             
        }
     });
     function saveEvent(event){
        
       $.ajax({
        type: "GET",
        url: '/isletmeyonetim/randevuguncelle',

         data: {randevuid:event.id,randevutarihi:event.start.format('YYYY-MM-DD'),randevusaati:event.start.format('HH:mm'),randevusaatibitis:event.end.format('HH:mm')},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {   
            
            $('#calendar').fullCalendar( 'refetchEvents' ); 
        },
        error: function (request, status, error) { 
             document.getElementById('hata').innerHTML =request.responseText; 
        }

    });  
       
     }

    /* initialize the calendar
    -----------------------------------------------------------------*/
     
   

  };

  return App;
})(App || {});
