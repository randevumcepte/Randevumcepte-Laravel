var App = (function() {
    'use strict';

    App.pageCalendar = function() {


        /* initialize the external events
        -----------------------------------------------------------------*/
        // will first fade out the loading animation


        $('#external-events .fc-event').each(function() {

            // store data so the calendar knows to render an event upon drop
            $(this).data('event', {
                title: $.trim($(this).text()), // use the element's text as the event title
                stick: true // maintain when user navigates (see docs on the renderEvent method)
            });

            // make the event draggable using jQuery UI
            $(this).draggable({
                zIndex: 999,
                revert: true, // will cause the event to go back to its
                revertDuration: 0 //  original position after the drag
            });

        });

        $.ajax({

            type: "GET",
            url: '/isletmeyonetim/randevuyukle',
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $("#preloader").show();

            },
            success: function(result) {
                var tarih = getQueryStrings()["tarih"];


                if (tarih) {

                    tarih = new Date(tarih);
                } else {

                    tarih = new Date();
                }

                $("#preloader").attr('style', 'display:none');
                


            },
            error: function(request, status, error) {

                document.getElementById('hata').innerHTML = request.responseText;

            }
        });

        function saveEvent(event) {

            $.ajax({
                type: "GET",
                url: '/isletmeyonetim/randevuguncelle',

                data: {
                    randevuid: event.id,
                    randevutarihi: event.start.format('YYYY-MM-DD'),
                    randevusaati: event.start.format('HH:mm'),
                    randevusaatibitis: event.end.format('HH:mm')
                },
                dataType: "text",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(result) {

                    $('#calendar').fullCalendar('refetchEvents');
                },
                error: function(request, status, error) {
                    document.getElementById('hata').innerHTML = request.responseText;
                }

            });

        }

        function getQueryStrings() {
            var vars = [],
                hash; //vars arrayi ve hash değişkeni tanımlıyoruz
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&'); //QueryString değerlerini ayıklıyoruz.
            for (var i = 0; i < hashes.length; i++) {
                hash = hashes[i].split('=');
                vars.push(hash[0]);
                vars[hash[0]] = hash[1]; //Değerlerimizi dizimize ekliyoruz
            }
            return vars;
        }

        /* initialize the calendar
        -----------------------------------------------------------------*/



    };

    return App;
})(App || {});