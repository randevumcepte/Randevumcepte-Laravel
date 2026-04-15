////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// jQuery
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
var automaticGeoLocation = false;
var resizeId;
const gizliAlanAdlari = ['randevu.randevumcepte.com.tr'];   // değiştirebilirsiniz
const odenenGizlensin = gizliAlanAdlari.includes(window.location.hostname);
$(document).ready(function($) {
    "use strict";
    var interval='';
    $('#button_paket_button').click(function(e){
        alert("");
    });
//  Geo Location button
    if( $(".geo-location").length > 0 && $(".map").length === 0 ){
        $("body").append("<div id='map-helper' style='display: none'></div>");
        var map = new google.maps.Map(document.getElementById("map-helper"));
        autoComplete(map);
    }
//  Selectize
    $("[data-enable-search=true]").each(function(){
        $(this).selectize({
            onDropdownOpen: dropdownOpen,
            onDropdownClose: dropdownClose,
            allowEmptyOption: false
        });
    });
    function dropdownOpen($dropdown){
        $dropdown.addClass("opening");
    }
    function dropdownClose($dropdown){
        $dropdown.removeClass("opening");
    }
//  Disable inputs in the non-active tab
    $(".form-slide:not(.active) input, .form-slide:not(.active) select, .form-slide:not(.active) textarea").prop("disabled", true);
//  Change tab button
    $("select.change-tab").each(function(){
        var _this = $(this);
        if( $(this).find(".item").attr("data-value") !== "" ){
            changeTab( _this );
        }
    });
    $(".change-tab").on("change", function() {
        changeTab( $(this) );
    });
    $(".box").each(function(){
        if( $(this).find(".background .background-image").length ) {
            $(this).css("background-color","transparent");
        }
    });
//  Star Rating
    $(".rating").each(function(){
        for( var i = 0; i <  5; i++ ){
            if( i < $(this).attr("data-rating") ){
                $(this).append("<i class='active fa fa-star'></i>")
            }
            else {
                $(this).append("<i class='fa fa-star'></i>")
            }
        }
    });
//  Button for class changing
    $(".change-class").on("click", function(e){
        e.preventDefault();
        var parentClass = $( "."+$(this).attr("data-parent-class") );
        parentClass.removeClass( $(this).attr("data-change-from-class") );
        parentClass.addClass( $(this).attr("data-change-to-class") );
        $(this).parent().find(".change-class").removeClass("active");
        $(this).addClass("active");
        readMore();
    });
    if( $(".masonry").length ){
        $(".items.masonry").masonry({
            itemSelector: ".item",
            transitionDuration: 0
        });
    }
    $(".ribbon-featured").each(function() {
        var thisText = $(this).text();
        $(this).html("");
        $(this).append(
            "<div class='ribbon-start'></div>" +
            "<div class='ribbon-content'>" + thisText + "</div>" +
            "<div class='ribbon-end'>" +
                "<figure class='ribbon-shadow'></figure>" +
            "</div>"
        );
    });
//  File input styling
    if( $("input[type=file].with-preview").length ){
        $("input.file-upload-input").MultiFile({
            list: ".file-upload-previews"
        });
    }
    $(".single-file-input input[type=file]").change(function() {
        previewImage(this);
    });
    $(".has-child a[href='#'].nav-link").on("click", function (e) {
        e.preventDefault();
       if( !$(this).parent().hasClass("hover") ){
           $(this).parent().addClass("hover");
       }
       else {
           $(this).parent().removeClass("hover");
       }
    });
    if( $(".owl-carousel").length ){
        var galleryCarousel = $(".gallery-carousel");
        galleryCarousel.owlCarousel({
            loop: false,
            margin: 0,
            nav: true,
            items: 1,
            navText: ["<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"],
            autoHeight: true,
            dots: false
        });
        $(".tabs-slider").owlCarousel({
            loop: false,
            margin: 0,
            nav: false,
            items: 1,
            autoHeight: true,
            dots: false,
            mouseDrag: true,
            touchDrag: false,
            pullDrag: false,
            freeDrag: false
        });
        $(".full-width-carousel").owlCarousel({
            loop: true,
            margin: 10,
            nav: true,
            items: 3,
            navText: ["<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"],
            autoHeight: false,
            center: true,
            dots: false,
            autoWidth:true,
            responsive: {
                768: {
                    items: 3
                },
                0 : {
                    items: 1,
                    center: false,
                    margin: 0,
                    autoWidth: false
                }
            }
        });
        $(".gallery-carousel-thumbs").owlCarousel({
            loop: false,
            margin: 20,
            nav: false,
            dots: true,
            items: 5,
            URLhashListener: true
        });
        $("a.owl-thumb").on("click", function () {
            $("a.owl-thumb").removeClass("active-thumb");
            $(this).addClass("active-thumb");
        });
        galleryCarousel.on('translated.owl.carousel', function() {
            var hash = $(this).find(".active").find("img").attr("data-hash");
            $(".gallery-carousel-thumbs").find("a[href='#" + hash + "']").trigger("click");
        });
    }
     $("[data-background-image]").each(function() {
        $(this).css("background-image", "url("+ $(this).attr("data-background-image") +")" );
    });
    $(".background-image").each(function() {
        $(this).css("background-image", "url("+ $(this).find("img").attr("src") +")" );
    });
//  Custom background color
    $("[data-background-color]").each(function() {
        $(this).css("background-color", $(this).attr("data-background-color") );
    });
//  Bootstrap tooltip initialization
    $('[data-toggle="tooltip"]').tooltip();
//  iCheck
    $(".input-radio,input[name='puanlama']").iCheck();
    $(".form.email .btn[type='submit']").on("click", function(e){
        var button = $(this);
        var form = $(this).closest("form");
        button.prepend("<div class='status'></div>");
        form.validate({
            submitHandler: function() {
                $.post("assets/php/email.php", form.serialize(),  function(response) {
                    button.find(".status").append(response);
                    form.addClass("submitted");
                });
                return false;
            }
        });
    });
//  No UI Slider -------------------------------------------------------------------------------------------------------
    if( $('.ui-slider').length > 0 ){
        $.getScript( "assets/js/jquery.nouislider.all.min.js", function() {
            $('.ui-slider').each(function() {
                if( $("body").hasClass("rtl") ) var rtl = "rtl";
                else rtl = "ltr";
                var step;
                if( $(this).attr('data-step') ) {
                    step = parseInt( $(this).attr('data-step') );
                }
                else {
                    step = 10;
                }
                var sliderElement = $(this).attr('id');
                var element = $( '#' + sliderElement);
                var valueMin = parseInt( $(this).attr('data-value-min') );
                var valueMax = parseInt( $(this).attr('data-value-max') );
                $(this).noUiSlider({
                    start: [ valueMin, valueMax ],
                    connect: true,
                    direction: rtl,
                    range: {
                        'min': valueMin,
                        'max': valueMax
                    },
                    step: step
                });
                if( $(this).attr('data-value-type') == 'price' ) {
                    if( $(this).attr('data-currency-placement') == 'before' ) {
                        $(this).Link('lower').to( $(this).children('.values').children('.value-min'), null, wNumb({ prefix: $(this).attr('data-currency'), decimals: 0, thousand: '.' }));
                        $(this).Link('upper').to( $(this).children('.values').children('.value-max'), null, wNumb({ prefix: $(this).attr('data-currency'), decimals: 0, thousand: '.' }));
                    }
                    else if( $(this).attr('data-currency-placement') == 'after' ){
                        $(this).Link('lower').to( $(this).children('.values').children('.value-min'), null, wNumb({ postfix: $(this).attr('data-currency'), decimals: 0, thousand: ' ' }));
                        $(this).Link('upper').to( $(this).children('.values').children('.value-max'), null, wNumb({ postfix: $(this).attr('data-currency'), decimals: 0, thousand: ' ' }));
                    }
                }
                else {
                    $(this).Link('lower').to( $(this).children('.values').children('.value-min'), null, wNumb({ decimals: 0 }));
                    $(this).Link('upper').to( $(this).children('.values').children('.value-max'), null, wNumb({ decimals: 0 }));
                }
            });
        });
    }
      // Handle checkbox change event
//  Read More
    readMore();
    footerHeight();





});
$(window).on("resize", function(){
    clearTimeout(resizeId);
    resizeId = setTimeout(doneResizing, 250);
});
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Functions
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Do after resize
function doneResizing(){
    footerHeight();
}
// Change Tab
function changeTab(_this){
    var parameters = _this.data("selectize").items[0];
    var changeTarget = $("#" + _this.attr("data-change-tab-target"));
    var slide = changeTarget.find(".form-slide");
    if( parameters === "" ){
        slide.removeClass("active");
        slide.first().addClass("default");
        changeTarget.find("input").prop("disabled", true);
        changeTarget.find("select").prop("disabled", true);
        changeTarget.find("textarea").prop("disabled", true);
    }
    else {
        slide.removeClass("default");
        slide.removeClass("active");
        changeTarget.find("input").prop("disabled", true);
        changeTarget.find("select").prop("disabled", true);
        changeTarget.find("textarea").prop("disabled", true);
        changeTarget.find( "#" + parameters ).addClass("active");
        changeTarget.find( "#" + parameters + " input").prop("disabled", false);
        changeTarget.find( "#" + parameters + " textarea").prop("disabled", false);
        changeTarget.find( "#" + parameters + " select").prop("disabled", false);
    }
}
// Footer Height
function footerHeight(){
    if( !viewport.is("xs") ) {
        var footer = $(".footer");
        var footerHeight = footer.height() + parseInt( footer.css("padding-top"), 10 ) + parseInt( footer.css("padding-bottom"), 10 ) ;
        $(".page >.content").css("margin-bottom", footerHeight + "px");
    }
    else if(viewport.is("xs")) {
        $(".page >.content").css("margin-bottom", "0px");
    }
}
// Read More
function readMore() {
    $(".read-more").each(function(){
        var readMoreLink = $(this).attr("data-read-more-link-more");
        var readLessLink = $(this).attr("data-read-more-link-less");
        var collapseHeight = $(this).find(".item:first").height() + parseInt( $(this).find(".item:first").css("margin-bottom"), 10 );
        $(".read-more").readmore({
            moreLink: '<div class="center"><a href="#" class="btn btn-primary btn-rounded btn-framed">' + readMoreLink + '</a></div>',
            lessLink: '<div class="center"><a href="#" class="btn btn-primary btn-rounded btn-framed">' + readLessLink + '</a></div>',
            collapsedHeight: 500
        });
    });
}
// Google Map
function simpleMap(latitude, longitude, markerImage, mapTheme, mapElement, markerDrag){
    if (!markerDrag){
        markerDrag = false;
    }
    if ( mapTheme === "light" ){
        var mapStyles = [{"featureType":"administrative.locality","elementType":"all","stylers":[{"hue":"#c79c60"},{"saturation":7},{"lightness":19},{"visibility":"on"}]},{"featureType":"landscape","elementType":"all","stylers":[{"hue":"#ffffff"},{"saturation":-100},{"lightness":100},{"visibility":"simplified"}]},{"featureType":"poi","elementType":"all","stylers":[{"hue":"#ffffff"},{"saturation":-100},{"lightness":100},{"visibility":"off"}]},{"featureType":"road","elementType":"geometry","stylers":[{"hue":"#c79c60"},{"saturation":-52},{"lightness":-10},{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"hue":"#c79c60"},{"saturation":-93},{"lightness":31},{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"labels","stylers":[{"hue":"#c79c60"},{"saturation":-93},{"lightness":-2},{"visibility":"simplified"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"hue":"#c79c60"},{"saturation":-52},{"lightness":-10},{"visibility":"simplified"}]},{"featureType":"transit","elementType":"all","stylers":[{"hue":"#c79c60"},{"saturation":10},{"lightness":69},{"visibility":"on"}]},{"featureType":"water","elementType":"all","stylers":[{"hue":"#c79c60"},{"saturation":-78},{"lightness":67},{"visibility":"simplified"}]}];
    }
    else if ( mapTheme === "dark" ){
        mapStyles = [{"featureType":"all","elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#000000"},{"lightness":40}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#000000"},{"lightness":16}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":20}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":17},{"weight":1.2}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":20}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":21}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":17}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":29},{"weight":0.2}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":18}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":16}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":19}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":17}]}]
    }
    var mapCenter = new google.maps.LatLng(latitude,longitude);
    var mapOptions = {
        zoom: 13,
        center: mapCenter,
        disableDefaultUI: false,
        scrollwheel: false,
        styles: mapStyles
    };
    var element = document.getElementById(mapElement);
    var map = new google.maps.Map(element, mapOptions);
    var marker = new google.maps.Marker({
        position: new google.maps.LatLng(latitude,longitude),
        map: map,
        icon: markerImage,
        draggable: markerDrag
    });
    google.maps.event.addListener(marker, 'dragend', function(){
        var latitudeInput = $('#latitude');
        var longitudeInput = $("#longitude");
        if( latitudeInput.length ){
            latitudeInput.val( marker.getPosition().lat() );
        }
        if( longitudeInput.length ){
            longitudeInput.val( marker.getPosition().lng() );
        }
    });
    autoComplete(map, marker);
}
//Autocomplete ---------------------------------------------------------------------------------------------------------
function autoComplete(map, marker){
    if( $("#input-location").length ){
        if( !map ){
            map = new google.maps.Map(document.getElementById("input-location"));
        }
        var mapCenter;
        var input = document.getElementById('input-location');
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            if (!place.geometry) {
                return;
            }
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }
            mapCenter = place.geometry.location;
            if( marker ){
                marker.setPosition(place.geometry.location);
                marker.setVisible(true);
                $('#latitude').val( marker.getPosition().lat() );
                $('#longitude').val( marker.getPosition().lng() );
            }
            var address = '';
            if (place.address_components) {
                address = [
                    (place.address_components[0] && place.address_components[0].short_name || ''),
                    (place.address_components[1] && place.address_components[1].short_name || ''),
                    (place.address_components[2] && place.address_components[2].short_name || '')
                ].join(' ');
            }
        });
        $('.geo-location').on("click", function(e) {
            e.preventDefault();
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(success);
            } else {
                console.log('Geo Location is not supported');
            }
        });
        function success(position) {
            var locationCenter = new google.maps.LatLng( position.coords.latitude, position.coords.longitude);
            map.setCenter( locationCenter );
            map.setZoom(14);
            if(marker){
                marker.setPosition(locationCenter);
            }
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({
                "latLng": locationCenter
            }, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    var lat = results[0].geometry.location.lat(),
                        lng = results[0].geometry.location.lng(),
                        placeName = results[0].address_components[0].long_name,
                        latlng = new google.maps.LatLng(lat, lng);
                    $("#input-location").val(results[0].formatted_address);
                    var latitudeInput = $('#latitude');
                    var longitudeInput = $("#longitude");
                    if( latitudeInput.length ){
                        latitudeInput.val( marker.getPosition().lat() );
                    }
                    if( longitudeInput.length ){
                        longitudeInput.val( marker.getPosition().lng() );
                    }
                }
            });
        }
    }
}
/*
if( $("#input-location2").length ){
    if( !map ){
        var map = new google.maps.Map(document.getElementById("input-location2"));
    }
    var mapCenter;
    var input = document.getElementById('input-location2');
    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.bindTo('bounds', map);
    google.maps.event.addListener(autocomplete, 'place_changed', function() {
        var place = autocomplete.getPlace();
        if (!place.geometry) {
            return;
        }
    });
}
*/
function previewImage(input) {
    var ext = $(input).val().split('.').pop().toLowerCase();
    if($.inArray(ext, ['gif','png','jpg','jpeg']) === -1) {
        alert('invalid extension!');
    }
    else {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(input).parents(".profile-image").find(".image").attr("style", "background-image: url('" + e.target.result + "');" );
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
}
// Viewport ------------------------------------------------------------------------------------------------------------
var viewport = (function() {
    var viewPorts = ['xs', 'sm', 'md', 'lg'];
    var viewPortSize = function() {
        return window.getComputedStyle(document.body, ':before').content.replace(/"/g, '');
    };
    var is = function(size) {
        if ( viewPorts.indexOf(size) === -1 ) throw "no valid viewport name given";
        return viewPortSize() === size;
    };
    var isEqualOrGreaterThan = function(size) {
        if ( viewPorts.indexOf(size) === -1 ) throw "no valid viewport name given";
        return viewPorts.indexOf(viewPortSize()) >= viewPorts.indexOf(size);
    };
    // Public API
    return {
        is: is,
        isEqualOrGreaterThan: isEqualOrGreaterThan
    }
})();
/*$('#saatsecimtablosu').on('click','input[name=randevusaati]',function(e){
    e.preventDefault();
      var tarihbolumu = document.getElementById('secilentarihkismi');
     var saatbolumu = document.getElementById('secilensaatkismi');
        tarihbolumu.setAttribute('style','display:block;width:100%');
       saatbolumu.setAttribute('style','display:block;width:100%');
        var tarihtablosu = document.getElementById('tarihtablosu');
    var saattablosu = document.getElementById('saatsecimtablosu');
    tarihtablosu.setAttribute('style','display:none');
    saattablosu.setAttribute('style','display:none');
    var kisiselbilgiler = document.getElementById('kisiselbilgiler');
    var randevuonay = document.getElementById('randevuonay');
    if(kisiselbilgiler !== null)
         kisiselbilgiler.setAttribute('style','display:block;width:100%');
    if(randevuonay !== null)
        randevuonay.setAttribute('style','display:block;width:100%');
     var secilentarih = document.getElementById('secilentarih');
     var secilensaat = document.getElementById('secilensaat');
     var secilentarihdeger =  document.getElementById('secilentarihdeger');
     var secilensaatdeger =  document.getElementById('secilensaatdeger');
     secilentarih.value =  $('input[name=randevutarihi]:checked').val();
     secilensaat.value = $('input[name=randevusaati]:checked').val();
     secilentarihdeger.innerHTML = $('input[name=randevutarihi]:checked').val();
     secilensaatdeger.innerHTML = $('input[name=randevusaati]:checked').val();
});*/
$('input[name=randevutarihi]').on('ifChecked', function(e) {
    e.preventDefault();
    
    // Seçilen personelleri al
    var secilenPersoneller = [];
    $('select[name="personeller[]"] option:selected').each(function() {
        secilenPersoneller.push($(this).val());
    });
    
    // Seçilen hizmetleri al
    var secilenHizmetler = [];
    $('input[name="secilenhizmetler[]"]').each(function() {
        secilenHizmetler.push($(this).val());
    });
    
    $.ajax({
        type: "GET",
        url: '/saatgetir',
        data: {
            randevutarihi: $('input[name="randevutarihi"]:checked').val(),
            isletmeno: $('#salonid').val(),
            secilenpersoneller: secilenPersoneller,
            secilenhizmetler: secilenHizmetler,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: "text",
        beforeSend: function() {
            $('#preloader').show();
        },
        success: function(result) {
            $('#preloader').hide();
            $('#saatsecimtablosu').html(result);
        },
        error: function(request, status, error) {
            $('#preloader').hide();
            $('#hata').html(request.responseText);
        }
    });
});


$('#secilenpersonelkismi').on('click','#personeldegistir',function(e){
    e.preventDefault();
    var tarihbolumu = document.getElementById('randevutarihalani');
    var saatbolumu =  document.getElementById('randevusaatalani');
    tarihbolumu.setAttribute('style','display:none');
    saatbolumu.setAttribute('style','display:none');
    personeltablosu = document.getElementById('personeltablosu');
    personeltablosu.setAttribute('style','display:block;width:100%');
    personeltablosu.setAttribute('class','personeller2');
    secilenpersonelkismi = document.getElementById('secilenpersonelkismi');
    secilenpersonelkismi.setAttribute('style','display:none');
});
$('#secilentarihkismi').on('click','#tarihdegistir',function(e){
    e.preventDefault();
     var tarihbolumu = document.getElementById('randevutarihalani');
      var tarihbolumu = document.getElementById('secilentarihkismi');
        tarihbolumu.setAttribute('style','display:none');
        var tarihtablosu = document.getElementById('tarihtablosu');
    tarihtablosu.setAttribute('style','display:block;width:100%');
});
$('#randevuhizmetvepersonelleri').on('submit', function(e) {
    e.preventDefault();
    var formData = $('#randevuhizmetvepersonelleri').serialize();
    var url = '/randevual';
     var secilenpersonelbolumu = document.getElementById('secilenpersonelkismi');
    secilenpersonelbolumu.setAttribute('style','display:block;width:100%');
    var secilenpersoneller = document.getElementsByName('personeller[]');
    var secilenpersonelliste = "<ul>";
        for(var i=0;i<secilenpersoneller.length;i++){
           secilenpersonelliste += '<li>  <input type="hidden" name="personeller_input[]" value='+secilenpersoneller[i].options[secilenpersoneller[i].selectedIndex].value+'>' + secilenpersoneller[i].options[secilenpersoneller[i].selectedIndex].text + '</li>';
        }
      secilenpersonelliste += '</ul>';
    var personeltablosu = document.getElementById('personeltablosu');
    personeltablosu.setAttribute('style','display:none');
    var secilenpersoneldeger = document.getElementById('secilenpersoneldeger');
    secilenpersoneldeger.innerHTML = secilenpersonelliste;
    $.ajax({
        type: "POST",
        url: url,
        data: formData,
        dataType: "text",
        headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  },
        success: function(result) {
            document.getElementById('saatsecimtablosu').innerHTML = result;
        },
        error: function (request, status, error) {
            alert('HATA : ' + request.responseText);
            document.getElementById('saatsecimtablosu').innerHTML = request.responseText;
        }
    });
    var tarihbolumu = document.getElementById('randevutarihalani');
    var saatbolumu = document.getElementById('randevusaatalani');
    tarihbolumu.setAttribute('style','display:block;width:100%');
    tarihbolumu.setAttribute('class','row');
     saatbolumu.setAttribute('class','row');
    saatbolumu.setAttribute('style','display:block;width:100%');
    var tarihtablosu = document.getElementById('tarihtablosu');
    var saatsecimtablosu =  document.getElementById('saatsecimtablosu');
    tarihtablosu.setAttribute('style','display:block;width:100%');
    saatsecimtablosu.setAttribute('style','display:block;width:100%');
   var secilentarihkismi = document.getElementById('secilentarihkismi');
   var secilensaatkismi = document.getElementById('secilensaatkismi');
    secilentarihkismi.setAttribute('style','display:none');
   secilensaatkismi.setAttribute('style','display:none');
    tarihbolumu.focus();
});
$('#randevusistemi').on('click','#sifregonder',function(e){
     e.preventDefault();
     if($('#cep_telefon').val() == '' ||$('#cep_telefon').val() == null){
        alert('Lütfen cep telefon numaranızı giriniz');
     }
     else{
        var phoneNumberRegex = /^05[0-9]{9}$/;
        var telefon = $('#cep_telefon').val();
        if(!telefon.match(phoneNumberRegex)){
            alert('Lütfen geçerli bir cep telefon numarası giriniz!');
        }
        else{
            var formData = $('#cep_telefon').val();
             var url = '/kullanicikontrolet';
              $.ajax({
                type: "GET",
                url: url,
                data: {cep_telefon:formData},
                dataType: "text",
                headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
               beforeSend: function(){
                    $("#preloader").show();
               },
                success: function(result) {
                    document.getElementById('hosgeldinizbildirimalani').innerHTML = result;
                   $('#hosgeldinizbildirimalani').attr('tabindex','-1');
                    $('html, body').animate({ scrollTop:  $('#hosgeldinizbildirimalani').offset().top }, 'slow');
                    var sifregonder = document.getElementById('sifregonder');
                    sifregonder.setAttribute('style','visibility:hidden');
                      $("#preloader").hide();
                },
                error: function (request, status, error) {
                    document.getElementById('epostahata').innerHTML = request.responseText;
                      $("#preloader").hide();
                }
            });
        }
     }
});
$('#randevusistemi').on('click','#sifregonder2',function(e){
    e.preventDefault();
     if(($('#adsoyad').val()!='')&&($('#cep_telefon').val()!='')){
        var phoneNumberRegex = /^05[0-9]{9}$/;
        var telefon = $('#cep_telefon').val();
        if(!telefon.match(phoneNumberRegex)){
            alert('Lütfen geçerli bir cep telefon numarası giriniz!');
        }
        else{
            var url = '/sifregonder2';
            $.ajax({
            type: "GET",
            url: url,
            data: {adsoyad:$('#adsoyad').val(), ceptelefon:$('#cep_telefon').val()},
            dataType: "text",
             headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              beforeSend: function(){
                        $("#preloader").show();
               },
                    success: function(result) {
                        var sifregonder2 = document.getElementById('sifregonder2');
                        var sifrealani = document.getElementById('sifrealaniregister');
                        $('#sifrealaniregister').attr('tabindex','-1');
                        sifregonder2.setAttribute('style','display:none');
                        sifrealani.setAttribute('style','display:block;width:100%;margin-top:10px');
                         sifrealani.innerHTML = result;
                         $('html, body').animate({ scrollTop:  $('#sifrealaniregister').offset().top }, 'slow');
                          $("#preloader").hide();
                    },
                    error: function (request, status, error) {
                        document.getElementById('epostahata').innerHTML = request.responseText;
                          $("#preloader").hide();
                    }
              });
        }
     }
     else{
          alert('Lütfen tüm bilgileri giriniz');
     }
});
$('#randevusistemi').on('click','#sifregonder3',function(e){
    e.preventDefault();
    if($('#cep_telefon').val() == '' ||$('#cep_telefon').val() == null){
        alert('Lütfen telefon numarası giriniz');
     }
     else{
        var phoneNumberRegex = /^05[0-9]{9}$/;
        var telefon = $('#cep_telefon').val();
        if(!telefon.match(phoneNumberRegex)){
            alert('Lütfen geçerli bir cep telefon numarası giriniz!');
        }
        else{
             var url = '/sifregonder2';
            $.ajax({
            type: "GET",
            url: url,
            data: {adsoyad:$('#adsoyad').val(), ceptelefon:$('#cep_telefon').val()},
            dataType: "text",
             headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              beforeSend: function(){
                        $("#preloader").show();
               },
                    success: function(result) {
                        var sifregonder2 = document.getElementById('sifregonder2');
                        $('#sifrealaniregister').empty();
                        var sifrealani = document.getElementById('sifrealaniregister');
                        $('#sifrealaniregister').attr('tabindex','-1');
                        sifregonder2.setAttribute('style','display:none');
                        sifrealani.setAttribute('style','display:block;width:100%;margin-top:10px');
                         sifrealani.innerHTML = result;
                         $('html, body').animate({ scrollTop:  $('#sifrealaniregister').offset().top }, 'slow');
                          $("#preloader").hide();
                    },
                    error: function (request, status, error) {
                        document.getElementById('epostahata').innerHTML = request.responseText;
                          $("#preloader").hide();
                    }
              });
        }
   }
});
$('#randevusistemi').on('click','#sifregonder4',function(e){
     e.preventDefault();
     if($('#cep_telefon').val() == '' ||$('#cep_telefon').val() == null){
        alert('Lütfen cep telefon numaranızı giriniz');
     }
     else{
        var phoneNumberRegex = /^05[0-9]{9}$/;
        var telefon = $('#cep_telefon').val();
        if(!telefon.match(phoneNumberRegex)){
            alert('Lütfen geçerli bir cep telefon numarası giriniz!');
        }
        else{
            var formData = $('#cep_telefon').val();
             var url = '/sifregonder';
              $.ajax({
                type: "GET",
                url: url,
                data: {cep_telefon:formData},
                dataType: "text",
                headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
               beforeSend: function(){
                    $("#preloader").show();
               },
                success: function(result) {
                    $('#hosgeldinizbildirimalani').empty();
                    document.getElementById('hosgeldinizbildirimalani').innerHTML = result;
                   $('#hosgeldinizbildirimalani').attr('tabindex','-1');
                    $('html, body').animate({ scrollTop:  $('#hosgeldinizbildirimalani').offset().top }, 'slow');
                    var sifregonder = document.getElementById('sifregonder');
                    sifregonder.setAttribute('style','visibility:hidden');
                      $("#preloader").hide();
                },
                error: function (request, status, error) {
                    document.getElementById('epostahata').innerHTML = request.responseText;
                      $("#preloader").hide();
                }
            });
        }
     }
});
$('#randevusistemi').on('click','#randevuonayla',function(e){
    e.preventDefault();
    if($('#sifre').val()===''){
        alert('Lütfen cep telefonunuza gönderilen şifreyi giriniz');
    }
    else{
        var formData =  $('#randevuozeti').serialize()+'&ceptelefon='+$('#cep_telefon').val()+'&sifre='+$('#sifre').val()+'&bildirimid='+$('#onesignalid').val();
        var url = '/randevuonayla';
          $.ajax({
        type: "POST",
        url: url,
        data: formData,
        dataType: "json",
        headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  },
   beforeSend: function(){
            $("#preloader").show();
       },
        success: function(result) {
            $('#kisiselbilgileralani').attr('style','display:none');
            $('#randevudokumu').attr('style','display:block;border:3px solid #FF4E00; border-radius: 4px;padding:20px;margin-bottom:20px');
           $('#randevudokumu').attr('tabindex','-1');
            document.getElementById('secilenhizmetdokumu').innerHTML = result.hizmetler;
            document.getElementById('secilenpersoneldokumu').innerHTML = result.personeller;
            document.getElementById('randevutarihidokumu').innerHTML = result.randevutarihi;
            document.getElementById('randevusaatidokumu').innerHTML = result.randevusaati;
            //document.getElementById('secilensube').innerHTML = result.sube;
            $('#randevusistemi').removeClass('col-lg-8');
            $('#randevusistemi').addClass('col-md-12');
            $('#randevuozetbolumu').attr('style','display:none');
            $("#preloader").hide();
              $('html, body').animate({ scrollTop: $('#randevudokumu').offset().top }, 'slow');
        },
        error: function (request, status, error) {
            document.getElementById('epostahata').innerHTML = request.responseText;
            $("#preloader").hide();
        }
    });
    }
});
$('#randevusistemi').on('click','#randevuonayla_auth',function(e){
    e.preventDefault();
    var formData =  $('#randevuozeti').serialize()+'&eposta='+$('#eposta').val()+'&sifre='+$('#sifre').val();
        var url = '/randevuonaylaauth';
          $.ajax({
        type: "POST",
        url: url,
        data: formData,
        dataType: "json",
        headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  },
   beforeSend: function(){
            $("#preloader").show();
       },
        success: function(result) {
             $('#kisiselbilgileralani').attr('style','display:none');
            $('#randevudokumu').attr('style','display:block;border:3px solid #FF4E00; border-radius: 4px;padding:20px;margin-bottom:20px');
            $('#randevudokumu').attr('tabindex','-1');
            document.getElementById('secilenhizmetdokumu').innerHTML = result.hizmetler;
            document.getElementById('secilenpersoneldokumu').innerHTML = result.personeller;
            document.getElementById('randevutarihidokumu').innerHTML = result.randevutarihi;
            document.getElementById('randevusaatidokumu').innerHTML = result.randevusaati;
            //document.getElementById('secilensube').innerHTML = result.sube;
            $('#randevusistemi').removeClass('col-lg-8');
            $('#randevusistemi').addClass('col-md-12');
            $("#preloader").hide();
            $('html, body').animate({ scrollTop: $('#randevudokumu').offset().top }, 'slow');
            $('#randevuozetbolumu').attr('style','display:none');
        },
        error: function (request, status, error) {
            //document.getElementById('epostahata').innerHTML = request.responseText;
            document.getElementById('hata').innerHTML = request.responseText;
              $("#preloader").hide()
        }
    });
});
$('#randevuonaylabutton').click(function(e){
    var formData = $('#randevuonayformu').serialize();
    var url = '/randevuekle';
          $.ajax({
        type: "POST",
        url: url,
        data: formData,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            $("#preloader").show();
       },
        success: function(result) {
             $("#preloader").hide();
            $('#randevuonaybildirim').attr('style','display:block');
            $('html, body').animate({ scrollTop: $('#randevuonaybildirim').offset().top }, 'slow');
            document.getElementById('randevuonaybildirim').innerHTML = result;
            setTimeout(function(){
                             window.location.href = '/randevularim';
            }, 2000);
        },
        error: function (request, status, error) {
            document.getElementById('hata').innerHTML = request.responseText;
             $("#preloader").hide();
        }
    });
});
$('#randevusistemi').on('click','#randevureddi',function(e){
    e.preventDefault();
    window.location.reload();
});
$("button[name='randevuiptalet'],a[name='randevuiptalet']").click(function(e){
    var dataval = $(this).attr('data-value');
    swal({
        title: "Emin misiniz?",
        text: "Randevuyu iptal etmek istediğinize emin misiniz? Bu işlem geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'İptal Et',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
            $.ajax({
                type: "GET",
                url: '/randevuiptalet',
                data: { randevuno : dataval,sube:$('input[name="sube"]').val(),hizmetId:$(this).attr('data-index-number')},
                dataType: "text",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function(){
                    $("#preloader").show();
               },
                success: function(result)  {
                    $("#preloader").hide();
                     var status =  $("span[name='guncelrandevudurum'][data-value='"+dataval+"']");
                     $("span[name='randevudurum'][data-value='"+dataval+"']").attr('style','display:none');
                     status.text("İptal Ettiniz");
                     status.addClass("btn btn-secondary small");
                     status.attr('style','width:100%');
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML = request.responseText;
                     $("#preloader").hide();
                }
            });
        }
    });
});
$("button[name='puanyorumla']").click(function(e){
    var salonno = $("input[name='salonno']").attr("data-value",$(this).attr("data-value")).val();
    var dataval = $(this).attr("data-value");
    $.ajax({
        type:"GET",
        url : '/puanyorumgetir',
        data:{salonno:salonno},
         headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success:function(result){
            document.getElementById('salonyorum').value = result.yorum;
            if(result.puan == 5)
                $('#puan5').attr("checked", "checked");
            else if(result.puan == 4)
            {
                 $('#puan4').attr("checked", "checked");
            }
            else if(result.puan == 3)
                 $('#puan3').attr("checked", "checked");
            else if(result.puan == 2)
                 $('#puan2').attr("checked", "checked");
            else if(result.puan == 1)
                $('#puan1').attr("checked", "checked");
            else
                $('#puan5').attr("checked", "checked");
        }
    });
    var modal = document.getElementById('myModal');
    var span = document.getElementsByClassName("close")[0];
    modal.style.display = "block";
    document.getElementById('salonbaslik').innerHTML = $("h3[data-value="+dataval+"]").html()+" randevunuz için puan ve yorum yapın";
    document.getElementById('puanyorumsalonno').value = dataval;
    document.getElementById('puanyorumsalonno').value = salonno;
    span.onclick = function() {
        modal.style.display = "none";
    }
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
});
$('#myModal').on('click','#puanlayorumla',function(e){
    var formData = $("#salonpuanlayorumla").serialize();
    $.ajax({
        type: "GET",
        url: '/randevuyorumlapuanla',
        data: formData,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(result)  {
              alert(result);
        },
        error: function (request, status, error) {
            alert(request.responseText);
        }
    });
});
$('#yenihizmetekleme').on('submit',function(e){
   var formData = $('#yenihizmetekleme').serialize();
   $.ajax({
        type: "GET",
        url: '/sistemyonetim/yenihizmetekleme',
        data: formData,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(result)  {
            alert(result);
        },
        error: function (request, status, error) {
            alert(request.responseText);
        }
    });
});
$('#yenihizmetkategoriekleme').on('submit',function(e){
   var formData = $('#yenihizmetkategoriekleme').serialize();
   $.ajax({
        type: "GET",
        url: '/sistemyonetim/yenihizmetkategoriekleme',
        data: formData,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(result)  {
            alert(result);
        },
        error: function (request, status, error) {
            alert(error);
        }
    });
});
$('a[name="hizmetkategorisil"]').click(function(e){
    var kategoriid =  $(this).attr("data-value");
    $.ajax({
        type: "GET",
        url: '/sistemyonetim/hizmetkategorisisil',
        data: {kategoriid:kategoriid},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(result)  {
            alert(result);
            location.reload();
        },
        error: function (request, status, error) {
            alert(request.responseText);
        }
    });
});
$('a[name="hizmetsil"]').click(function(e){
     e.preventDefault();
    var hizmetid =  $(this).attr("data-value");
    $.ajax({
        type: "GET",
        url: '/sistemyonetim/hizmetsil',
        data: {hizmetid:hizmetid},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(result)  {
            alert(result);
            location.reload();
        },
        error: function (request, status, error) {
            alert(request.responseText);
        }
    });
});
$('#yenisalonhizmetiekleme').on('submit',function(e){
    e.preventDefault();
    var formData = $('#yenisalonhizmetiekleme').serialize();
    $.ajax({
        type: "GET",
        url: '/sistemyonetim/yenisalonhizmetiekle',
        data: formData,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(result)  {
            alert(result);
        },
        error: function (request, status, error) {
           alert(request.responseText);
        }
    });
});
$('#aciklamaekle').click(function(e){
    e.preventDefault();
    var formData = $('#aciklamaekleme').serialize();
     $.ajax({
        type: "GET",
        url: '/sistemyonetim/isletmeaciklamaekle',
        data: formData,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(result)  {
            alert(result);
            location.reload();
        },
        error: function (request, status, error) {
           alert(request.responseText);
        }
    });
});
$('#aciklamaduzenle').click(function(e){
    e.preventDefault();
    var aciklamametni = document.getElementById('aciklamametni');
    var mevcutaciklama = aciklamametni.innerText;
    aciklamametni.setAttribute('style','display:none');
    var aciklamaduzenleme = document.getElementById('aciklamaduzenleme');
    aciklamaduzenleme.setAttribute('style','display:block');
    document.getElementById('mevcutaciklama').value = mevcutaciklama;
});
$('#aciklamaguncelle').click(function(e){
    e.preventDefault();
    var formData = $('#aciklamaduzenleme').serialize();
     $.ajax({
        type: "GET",
        url: '/sistemyonetim/aciklamaguncelle',
        data: formData,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(result)  {
            location.reload();
        },
        error: function (request, status, error) {
           alert(request.responseText);
        }
    });
});
$('input[name="calisiyor\\[\\]"]').attr("data-value",$(this).attr("data-value")).change(function(e){
    e.preventDefault();
     if(this.checked){
          $('#calismasaatibaslangic'+$(this).attr("data-value")).prop('disabled',false);
        $('#calismasaatibitis'+$(this).attr("data-value")).prop('disabled',false);
        document.getElementById('calisiyor'+$(this).attr("data-value")).value= 1;
     }
     else
     {
        $('#calismasaatibaslangic'+$(this).attr("data-value")).prop('disabled',true);
        $('#calismasaatibitis'+$(this).attr("data-value")).prop('disabled',true);
        document.getElementById('calisiyor'+$(this).attr("data-value")).value= 0;
     }
});
$('#calismasaatleriduzenle').click(function(e){
    e.preventDefault();
    var calismasaatiduzenlemealani =  document.getElementsByName('calismasaatiduzenlemealani');
    for(var i=0;i<calismasaatiduzenlemealani.length;i++){
        calismasaatiduzenlemealani[i].setAttribute('style','display:block');
    }
    document.getElementById('calismasaatiguncelle').setAttribute('style','display:block');
});
$('#calismasaatiguncelle').click(function(e){
    e.preventDefault();
    var formData = $('#calismasaatiduzenleme').serialize();
    $.ajax({
        type: "POST",
        url: '/sistemyonetim/calismasaatiguncelle',
        data: formData,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(result)  {
            alert(result);
        },
        error: function (request, status, error) {
          var hata =  document.getElementById('hatamesaj');
          hata.innerHTML = request.responseText;
        }
    });
});
$('#calismasaatiguncelle_isletme').click(function(e){
    e.preventDefault();
    var calisiyor1 = 0;
    var calisiyor2 = 0;
    var calisiyor3 = 0;
    var calisiyor4 = 0;
    var calisiyor5 = 0;
    var calisiyor6 = 0;
    var calisiyor7 = 0;
    var calismasaatibaslangic1 = "";
    var calismasaatibaslangic2 = "";
    var calismasaatibaslangic3 = "";
    var calismasaatibaslangic4 = "";
    var calismasaatibaslangic5 = "";
    var calismasaatibaslangic6 = "";
    var calismasaatibaslangic7 = "";
    var calismasaatibitis1 = "";
    var calismasaatibitis2 = "";
    var calismasaatibitis3 = "";
    var calismasaatibitis4 = "";
    var calismasaatibitis5 = "";
    var calismasaatibitis6 = "";
    var calismasaatibitis7 = "";
    if($('#calisiyor1').is(':checked')){
        calisiyor1 = 1;
        calismasaatibaslangic1 = $('#calismasaatibaslangic1').val();
        calismasaatibitis1 = $('#calismasaatibitis1').val();
    }
     if($('#calisiyor2').is(':checked')){
        calisiyor2 = 1;
        calismasaatibaslangic2 = $('#calismasaatibaslangic2').val();
        calismasaatibitis2 = $('#calismasaatibitis2').val();
    }
     if($('#calisiyor3').is(':checked')){
        calisiyor3 = 1;
        calismasaatibaslangic3 = $('#calismasaatibaslangic3').val();
        calismasaatibitis3 = $('#calismasaatibitis3').val();
    }
     if($('#calisiyor4').is(':checked')){
        calisiyor4 = 1;
        calismasaatibaslangic4 = $('#calismasaatibaslangic4').val();
        calismasaatibitis4 = $('#calismasaatibitis4').val();
    }
     if($('#calisiyor5').is(':checked')){
        calisiyor5 = 1;
        calismasaatibaslangic5 = $('#calismasaatibaslangic5').val();
        calismasaatibitis5 = $('#calismasaatibitis5').val();
    }
     if($('#calisiyor6').is(':checked')){
        calisiyor6 = 1;
        calismasaatibaslangic6 = $('#calismasaatibaslangic6').val();
        calismasaatibitis6 = $('#calismasaatibitis6').val();
    }
     if($('#calisiyor7').is(':checked')){
        calisiyor7 = 1;
        calismasaatibaslangic7 = $('#calismasaatibaslangic7').val();
        calismasaatibitis7 = $('#calismasaatibitis7').val();
    }
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/calismasaatiguncelle',
        data: {calisiyor1:calisiyor1,calismasaatibaslangic1:calismasaatibaslangic1,calismasaatibitis1:calismasaatibitis1,calisiyor2:calisiyor2,calismasaatibaslangic2:calismasaatibaslangic2,calismasaatibitis2:calismasaatibitis2,calisiyor3:calisiyor3,calismasaatibaslangic3:calismasaatibaslangic3,calismasaatibitis1:calismasaatibitis3,calisiyor4:calisiyor4,calismasaatibaslangic4:calismasaatibaslangic4,calismasaatibitis1:calismasaatibitis4,calisiyor5:calisiyor5,calismasaatibaslangic5:calismasaatibaslangic5,calismasaatibitis1:calismasaatibitis5,calisiyor6:calisiyor6,calismasaatibaslangic6:calismasaatibaslangic6,calismasaatibitis1:calismasaatibitis6,calisiyor7:calisiyor7,calismasaatibaslangic7:calismasaatibaslangic7,calismasaatibitis1:calismasaatibitis7,sube:$('input[name="sube"]').val()},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(result)  {
            alert(result);
        },
        error: function (request, status, error) {
          var hata =  document.getElementById('hatamesaj');
          hata.innerHTML = request.responseText;
        }
    });
});
function readURL(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      $('#profillogo').attr('src', e.target.result);
    }
    reader.readAsDataURL(input.files[0]);
  }
}
$("#isletmelogo").change(function() {
  readURL(this);
  var logo = $('#isletmelogo').get(0).files[0];
  var formData = new FormData();
  formData.append('isletmelogo',logo);
  formData.append('_token',$('input[name="_token"]').val());
  formData.append('sube',$('input[name="sube"]').val());
  $.ajax({
        type: "POST",
        url: '/isletmeyonetim/isletmelogoyukle',
        dataType: "text",
        data : formData,
        contentType: false,
        cache: false,
        processData:false,
       beforeSend: function(){
           $('#preloader').show();
       },
       success: function(result)  {
            $('#preloader').hide();
            swal({
                        type: "success",
                        title: "Başarılı",
                        text:  "Logo başarıyla kaydedildi",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer: 3000,
            });
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
               $('#preloader').hide();
        }
    });
});
function readURL2(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      $('#profilkapak').attr('src', e.target.result);
    }
    reader.readAsDataURL(input.files[0]);
  }
}
function readURL3(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      $('#yetkiliprofilresim').attr('src', e.target.result);
    }
    reader.readAsDataURL(input.files[0]);
  }
}
/*function readGorseller(input){
    var baslangicindex = 0;
    if($('input[name="sube"]').val()){
       baslangicindex = $.ajax({
            type :'GET',
             url: '/sistemyonetim/kayitlisalongorselisayisi',
            data: {isletmeid:$('input[name="sube"]').val()},
            async:false,
        }).responseText;
    }
    baslangicindex = parseInt(baslangicindex);
    var resimsayisimax = 12-baslangicindex;
    if(input.files.length > 12-baslangicindex){
        alert('En fazla '+resimsayisimax +' resim yükleyiniz!');
    }
    else{
        var filesindex = 0;
        for(var i =baslangicindex+1 ;i<=baslangicindex+input.files.length;i++){
                var reader = new FileReader();
                gorselGoruntule(i,reader);
                reader.readAsDataURL(input.files[filesindex]);
                filesindex++;
        }
         if(input.files.length<resimsayisimax){
            for(var j=input.files.length+1;j<=12;j++){
                 $('#gorsel'+j).attr('src','/public/img/image-01.jpg');
                 $('#gorsellink'+j).attr('href','/public/img/image-01.jpg');
            }
        }
    }
}*/
function readGorseller(input){
    var baslangicindex = $.ajax({
            type :'GET',
             url: '/isletmeyonetim/kayitlisalongorselisayisi',
            data: {isletmeid:$('input[name="sube"]').val()},
            async:false,
    }).responseText;
    baslangicindex = parseInt(baslangicindex);
    var resimsayisimax = 12-baslangicindex;
    if(input.files.length > 12-baslangicindex){
          swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text:  'En fazla '+resimsayisimax +' resim yükleyiniz!',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
                    );
    }
    else{
        var filesindex = 0;
        for(var i =baslangicindex+1 ;i<=baslangicindex+input.files.length;i++){
                var reader = new FileReader();
                gorselGoruntule(i,reader);
                reader.readAsDataURL(input.files[filesindex]);
                filesindex++;
        }
         if(input.files.length<resimsayisimax){
            for(var j=input.files.length+1;j<=12;j++){
                 $('#gorsel'+j).attr('src','/public/img/image-01.jpg');
                 $('#gorsellink'+j).attr('href','/public/img/image-01.jpg');
            }
        }
        var gorseller=  $('#isletmegorselleri').get(0).files.length;
    var formData = new FormData();
    for(var i=0;i<gorseller;i++){
         formData.append('isletmegorselleri[]',$('#isletmegorselleri').get(0).files[i]);
         formData.append('sube',$('input[name="sube"]').val());
         formData.append('_token',$('input[name="_token"]').val());
    }
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/isletmegorselekle',
        dataType: "json",
        data : formData,
        contentType: false,
        cache: false,
        processData:false,
       beforeSend: function(){
           $('#preloader').show();
       },
       success: function(result)  {
            $('#preloader').hide();
            $('#gorselbolumu').empty();
            $('#gorselbolumu').append(result.gorseller_html);
            $('#gorseleklemetext').empty();
            $('#gorseleklemetext').append(result.eklemetext);
            swal(
                {
                    type: 'success',
                    title: 'Başarılı',
                    text: 'İşletem görselleriniz başarıyla eklendi',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                }
            );
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
               $('#preloader').hide();
        }
    });
    }
}
function gorselGoruntule(i ,reader){
     reader.onload = function(e){
            $('#gorsel'+i).attr('src',e.target.result);
            $('#gorsellink'+i).attr('href',e.target.result);
    }
}
function gorselgoruntulebos(i,reader,src){
    reader.onload = function(e){
        $('#gorsel'+i).attr('src',src);
       $('#gorsellink'+i).attr('href',src);
    }
}
$("#isletmekapakfoto").change(function(e) {
   e.preventDefault();
    readURL2(this);
});
$("#isletmegorselleri").change(function(e){
    e.preventDefault();
    readGorseller(this);
});
$("#isletmegorselleri_isletme").change(function(e){
    e.preventDefault();
    readGorseller_isletme(this);
});
$('#mevcutisletmeduzenleme').on('submit',function(e){
    e.preventDefault();
    var gorseller=  $('#isletmegorselleri').get(0).files.length;
    var kapakfoto = $('#isletmekapakfoto').get(0).files[0];
    var logo = $('#isletmelogo').get(0).files[0];
    var formData = new FormData();
    formData.append('isletmekapakfoto',kapakfoto);
    formData.append('isletmelogo',logo);
    for(var i=0;i<gorseller;i++){
         formData.append('isletmegorselleri[]',$('#isletmegorselleri').get(0).files[i]);
    }
    var other_data = $('#mevcutisletmeduzenleme').serializeArray();
     $.each(other_data,function(key,input){
        formData.append(input.name,input.value);
    });
    $.ajax({
        type: "POST",
        url: '/sistemyonetim/mevcutisletmeduzenleme',
        dataType: "text",
        data : formData,
        contentType: false,
        cache: false,
        processData:false,
       beforeSend: function(){
           $('#preloader').show();
       },
       success: function(result)  {
            alert(result);
             $('#preloader').hide();
            window.location.reload();
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
               $('#preloader').hide();
        }
    });
});
$('#mevcutavantajduzenleme').on('submit',function(e){
    e.preventDefault();
    var gorseller=  $('#isletmegorselleri').get(0).files.length;
    var kapakfoto = $('#isletmekapakfoto').get(0).files[0];
    var formData = new FormData();
    formData.append('isletmekapakfoto',kapakfoto);
    for(var i=0;i<gorseller;i++){
         formData.append('isletmegorselleri[]',$('#isletmegorselleri').get(0).files[i]);
    }
    var other_data = $('#mevcutavantajduzenleme').serializeArray();
     $.each(other_data,function(key,input){
        formData.append(input.name,input.value);
    });
     formData.append('kampanya_detay',$('#detayicerikhtml').html());
      $.ajax({
        type: "POST",
        url: '/sistemyonetim/mevcutavantajduzenleme',
        dataType: "text",
        data : formData,
        contentType: false,
        cache: false,
        processData:false,
       beforeSend: function(){
           $('#preloader').show();
       },
       success: function(result)  {
             $('#preloader').hide();
             alert('Avantaj bilgileri başarı ile güncellendi');
            window.location.reload();
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
               $('#preloader').hide();
        }
    });
});
$('#mevcutisletmeduzenleme_isletmeadmin').on('submit',function(e){
    e.preventDefault();
    var gorseller=  $('#isletmegorselleri').get(0).files.length;
    var kapakfoto = $('#isletmekapakfoto').get(0).files[0];
    var logo = $('#isletmelogo').get(0).files[0];
    var formData = new FormData();
    formData.append('isletmekapakfoto',kapakfoto);
    formData.append('isletmelogo',logo);
    for(var i=0;i<gorseller;i++){
         formData.append('isletmegorselleri[]',$('#isletmegorselleri').get(0).files[i]);
    }
    var other_data = $('#mevcutisletmeduzenleme_isletmeadmin').serializeArray();
     $.each(other_data,function(key,input){
        formData.append(input.name,input.value);
    });
      $.ajax({
        type: "POST",
        url: '/isletmeyonetim/mevcutisletmeduzenleme',
        dataType: "text",
        data : formData,
        contentType: false,
        cache: false,
        processData:false,
       success: function(result)  {
            alert(result);
            window.location.reload();
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$("#yeniisletmeekleme").on('submit',function(e){
    e.preventDefault();
    var gorseller=  $('#isletmegorselleri').get(0).files.length;
    var kapakfoto = $('#isletmekapakfoto').get(0).files[0];
    var logo = $('#isletmelogo').get(0).files[0];
    var formData = new FormData();
    formData.append('isletmekapakfoto',kapakfoto);
    formData.append('isletmelogo',logo);
    for(var i=0;i<gorseller;i++){
         formData.append('isletmegorselleri[]',$('#isletmegorselleri').get(0).files[i]);
    }
    var other_data = $('#yeniisletmeekleme').serializeArray();
    $.each(other_data,function(key,input){
        formData.append(input.name,input.value);
    });
     $.ajax({
        type: "POST",
        url: '/sistemyonetim/yeniisletmeekle',
        dataType: "text",
        data : formData,
        contentType: false,
        cache: false,
        processData:false,
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
            alert(result);
            window.location.href = '/sistemyonetim/isletmeler';
        },
        error: function (request, status, error) {
             $('#preloader').show();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#yetkiliprofil').change(function(e){
    e.preventDefault();
    readURL3(this);
});
$('#yetkilidetayduzenleme').on('submit',function(e){
    e.preventDefault();
    var formData = new FormData();
     var yetkiliprofil = $('#yetkiliprofil')[0].files[0];
     var otherFormData = $('#yetkilidetayduzenleme').serialize();
     $.each($.parseJSON(otherFormData), function(key,input){
        formData.append(input.name,input.value);
     });
    formData.append('yetkiliprofil',yetkiliprofil);
    $.ajax({
        type: "POST",
        url: '/sistemyonetim/yetkilidetayduzenleme',
        data: formData,
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        contentType: false,
        processData: false,
        success: function(result)  {
            alert(result);
             location.reload();
        },
        error: function (request, status, error) {
          document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('.subecheck').on('change', function() {
   $(this).siblings('.subecheck').not(this).prop('checked', false);
});
$('#personelsecimadiminagec').click(function(e){
    e.preventDefault();
    var checkboxes = document.getElementsByName('randevuhizmet[]');
     var hizmetler = 'randevual/';
     var checkboxesChecked = [];
     var salonid = document.getElementById('salonid').value;
      for (var i=0; i<checkboxes.length; i++) {
         if (checkboxes[i].checked) {
            hizmetler += checkboxes[i].value;
             hizmetler += '_';
              checkboxesChecked.push(checkboxes[i]);
         }
     }
     if(checkboxesChecked.length == 0){
          alert('Devam etmek için en az bir tane hizmet seçiniz!');
     }
     else  {
         $("#personelsecimbolumu").attr('style', 'display:block');
         $("#secilenhizmetliste").attr('style', 'display:block');
        $.ajax({
        type: "GET",
        url: '/personelgetir/'+document.getElementById('salonid').value,
        data: $('input[name="randevuhizmet[]"]:checked').serialize(),
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
         beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#hizmetsecimbolumu").attr('style','display:none');
            $("#secilenhizmetlistebos").attr('style','display:none');
           $("#personelsecimbolumu").attr('style', 'display:block');
           $("#personelsecimbolumu").attr('tabindex','-1');
           document.getElementById("secilenhizmetliste").innerHTML = result.hizmetliste;
           document.getElementById("personelsecimbolumu").innerHTML = result.personelbolumu;
            $("#hizmetsecbaslik").removeClass('active');
            $("#hizmetsecbaslikmobil").removeClass('active');
            $("#personelsecbaslik").addClass('active');
             $("#personelsecbaslikmobil").addClass('active');
            $("#preloader").hide();
            $('html, body').animate({ scrollTop: $('#personelsecimbolumu').offset().top }, 'slow');
        },
        error: function (request, status, error) {
          document.getElementById('hata').innerHTML = request.responseText;
              $("#personelsecimbolumu").attr('style', 'display:none');
            $("#secilenhizmetliste").attr('style', 'display:none');
            $("#preloader").hide();
        }
    });
     }
});

$('#randevusistemi').on('submit','#personellisteparametreler' ,function(e){
    e.preventDefault();
       $.ajax({
        type: "GET",
        url: '/tarihsaatadiminagec/'+document.getElementById('salonid').value,
        data: $('#personellisteparametreler').serialize()+'&'+$('#randevuozeti').serialize(),
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            $("#preloader").show();
        },
       success: function(result)  {
            document.getElementById('personelliste').innerHTML = result.personelbilgi;
            $('#personellistebos').attr('style','display:none');
            $("#personelsecbaslik").removeClass('active');
            $("#personelsecbaslikmobil").removeClass('active');
            $('#tarihsaatsecbaslik').addClass('active');
            $('#tarihsaatsecbaslikmobil').addClass('active');
             $("#personelsecimbolumu").attr('style', 'display:none');
              $("#tarihsaatsecimbolumu").attr('style', 'display:block');
            if(document.getElementById('saatsecimtablosu').innerHTML.trim() == ''){
                 document.getElementById('saatsecimtablosu').innerHTML = result.tarihsaatbolumu;
            }
              $("#preloader").hide();
             $('html, body').animate({ scrollTop: $("#tarihsaatsecimbolumu").offset().top }, 'slow');
        },
        error: function (request, status, error) {
           alert(request.responseText);
            document.getElementById('hata').innerHTML = request.responseText;
            $("#preloader").hide();
        }
    });
});
$('#randevusistemi').on('click','#hizmetseckisminageridon',function(e){
    $("#personelsecimbolumu").attr('style', 'display:none');
    $("#hizmetsecimbolumu").attr('style', 'display:block');
     $("#hizmetsecbaslik").addClass('active');
     $("#hizmetsecbaslikmobil").addClass('active');
            $("#personelsecbaslik").removeClass('active');
             $("#personelsecbaslikmobil").removeClass('active');
 $("#hizmetsecimbolumu").attr('tabindex', '-1');
             $('html, body').animate({ scrollTop: $('#hizmetsecimbolumu').offset().top }, 'slow');
});
$('#randevusistemi').on('click','#personelseckisminageridon',function(e){
    $("#tarihsaatsecimbolumu").attr('style', 'display:none');
    $("#personelsecimbolumu").attr('style', 'display:block');
     $("#personelsecimbolumu").attr('tabindex', '-1');
     $("#personelsecbaslik").addClass('active');
            $("#tarihsaatsecbaslik").removeClass('active');
               $("#personelsecbaslikmobil").addClass('active');
            $("#tarihsaatsecbaslikmobil").removeClass('active');
        $('html, body').animate({ scrollTop: $('#personelsecimbolumu').offset().top }, 'slow');
});
$('#randevusistemi').on('click', '#onayadiminagec', function(e){
    if(!$('input[name=randevusaati]').is(':checked')) { alert("Devam etmek için lütfen randevu saatinizi seçiniz"); }
    else{
        $("#tarihsaatbos").attr('style','display:none');
        document.getElementById('tarihsaat').innerHTML = "<input type='hidden' name='randevutarihivesaati' value='"+ $('input[name=randevutarihi]:checked').val()+" "+$('input[name=randevusaati]:checked').val()+":00'>"+$('input[name=randevutarihi]:checked').val()+" "+$('input[name=randevusaati]:checked').val();
        $('#tarihsaatsecimbolumu').attr('style','display:none');
         $('#onaybolumu').attr('style','display:block');
          $('#onaybolumu').attr('tabindex','-1');
         $("#tarihsaatsecbaslik").removeClass('active');
          $("#onaybaslik").addClass('active');
           $("#tarihsaatsecbaslikmobil").removeClass('active');
          $("#onaybaslikmobil").addClass('active');
            $('html, body').animate({ scrollTop: $('#onaybolumu').offset().top }, 'slow');
    }
});
$('#randevusistemi').on('click','#tarihsaatseckisminageridon',function(e){
    $("#onaybolumu").attr('style', 'display:none');
    $("#tarihsaatsecimbolumu").attr('style', 'display:block');
     $("#tarihsaatsecimbolumu").attr('tabindex', '-1');
     $("#tarihsaatsecbaslik").addClass('active');
            $("#onaybaslik").removeClass('active');
              $("#tarihsaatsecbaslikmobil").addClass('active');
            $("#onaybaslikmobil").removeClass('active');
               $('html, body').animate({ scrollTop: $("#tarihsaatsecimbolumu").offset().top }, 'slow');
});
$('#favorilereekle').click(function(e){
     e.preventDefault();
       $.ajax({
        type: "GET",
        url: '/favorilereekle',
        data:  {salonno:$('input[name="salonno"]').val()},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            alert(result);
        },
        error: function (request, status, error) {
           alert(request.responseText);
        }
    });
});
$('#form_turu').on('change',function(e){
    e.preventDefault();
    if($('#form_turu').val()==33)
        $('.epilasyon_hizmeti_alanlari').attr('style','display:block');
    else
        $('.epilasyon_hizmeti_alanlari').attr('style','display:none');
    if($('#form_turu').val()==36)
        $('.zayiflama_hizmeti_alanlari').attr('style','display:block');
    else
        $('.zayiflama_hizmeti_alanlari').attr('style','display:none');
});
$('#tum_vucut').change(function(e){
    e.preventDefault();
    if(this.checked)
            $('.bolgeler').prop('checked',true);
    else
            $('.bolgeler').prop('checked',false);
});
$('a[name="islem-detayi"]').click( function(e){
    e.preventDefault();
    var hizmetkategoriid = $(this).attr('data-value');
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/islemdetaygetir',
        data:  {musteri_id:$('#musteri_id').val(), hizmet_kategori_id: hizmetkategoriid,sube:$('input[name="sube"]').val() },
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            $('#islemdetay').empty();
            $('#islemdetay').append(result);
            $('html, body').animate({ scrollTop: $('#islemdetay').offset().top }, 'slow');
            $('#preloader').hide();
        },
        error: function (request, status, error) {
           document.getElementById('hata').innerHTML = request.responseText;
              $('#preloader').hide();
        }
    });
});
$('#islemdetay').on('click','a[name="islem-duzenle"]',function(e){
    e.preventDefault();
    //alert($(this).attr('data-value'));
     $.ajax({
        type: "GET",
        dataType: "json",
        url: '/isletmeyonetim/islemgetir',
        data: {islemid:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            $('#preloader').hide();
            $('#islem_id').val(result.islem_id);
            $('input[name="tarih"]').val(result.tarih);
            $('input[name="seans_no"]').val(result.seans_no);
            $('#islem_personel').val(result.personel_id);
            $('#islem_yapilan').empty();
            $('#islem_yapilan').append(result.yapilanislemler);
            $('input[name="alinan_ucret"]').val(result.alinanodeme);
            $('textarea[name="aciklama"]').text(result.aciklama);
            $('#islem_formu_baslik').empty();
            $('#islem_formu_baslik').append(result.tarih +' tarihli işlemi düzenle');
            if(result.koltuk_alti)
                $('#koltuk_alti').prop('checked',true);
            if(result.bacak)
                $('#bacak').prop('checked',true);
             if(result.kol)
                $('#kol').prop('checked',true);
             if(result.bikini)
                $('#bikini').prop('checked',true);
             if(result.yuz)
                $('#yuz').prop('checked',true);
             if(result.gogus)
                $('#gogus').prop('checked',true);
             if(result.gobek)
                $('#gobek').prop('checked',true);
             if(result.sirt)
                $('#sirt').prop('checked',true);
             if(result.biyik)
                $('#biyik').prop('checked',true);
             if(result.favori)
                $('#favori').prop('checked',true);
             if(result.ense)
                $('#ense').prop('checked',true);
            $('#form_turu').val(result.hizmet_kategori_id);
            if(result.hizmet_kategori_id == 33){
                $('.epilasyon_hizmeti_alanlari').attr('style','display:block');
            }
            else
                 $('.epilasyon_hizmeti_alanlari').attr('style','display:none');
            if(result.hizmet_kategori_id == 36){
                $('.zayiflama_hizmeti_alanlari').attr('style','display:block');
            }
            else
                 $('.zayiflama_hizmeti_alanlari').attr('style','display:none');
            $('#formduzenle').trigger('click');
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#yeniformekle').click(function(e){
    seansformuac();
});
$(document).on('click','a[name="islem_olusturma"]',function(){
    seansformuac();
});
function seansformuac()
{
     $('#islemformu').trigger("reset");
    $('#islem_formu_baslik').empty();
    $('#islem_formu_baslik').append('yeni işlem formu oluştur');
    $('.epilasyon_hizmeti_alanlari').attr('style','display:none');
    $('.zayiflama_hizmeti_alanlari').attr('style','display:none');
    $('#islem_id').val('');
    $('#islem_yapilan option:selected').prop("selected", false);
}
$('#giderekle').click(function(e){
    e.preventDefault();
    if($('input[name="kasatarih"]').val() != '' && $('input[name="gider_aciklama"]').val() != '' && $('input[name="gider_miktar"]').val() != ''){
          $.ajax({
        type: "GET",
        url: '/isletmeyonetim/giderekle',
        data:  {gider_tarih:$('input[name="kasatarih"]').val(), gider_aciklama:$('input[name="gider_aciklama"]').val(),gider_miktar:$('input[name="gider_miktar"]').val(),sube:$('input[name="sube"]').val()  },
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            document.getElementById('kasadefterigidertablo').innerHTML = result.gider;
            document.getElementById('kasadefterigelirtablo').innerHTML = result.gelir;
             document.getElementById('toplamgelir').innerHTML = result.toplamgelir;
               document.getElementById('toplamgider').innerHTML = result.toplamgider;
                document.getElementById('kasaacilis').innerHTML = result.kasaacilis;
               document.getElementById('kasatoplam').innerHTML = result.kasatoplam;
               $('#preloader').hide();
        },
        error: function (request, status, error) {
           document.getElementById('hata').innerHTML = request.responseText;
              $('#preloader').hide();
        }
    });
    }
    else{
        alert('Lütfen tüm bilgileri giriniz');
    }
});
$('#gelirekle').click(function(e){
    e.preventDefault();
      if($('input[name="kasatarih"]').val() != '' && $('input[name="gelir_aciklama"]').val() != '' && $('input[name="gelir_miktar"]').val() != ''){
          $.ajax({
        type: "GET",
        url: '/isletmeyonetim/gelirekle',
        data:  {gelir_tarih:$('input[name="kasatarih"]').val(), gelir_aciklama:$('input[name="gelir_aciklama"]').val(),gelir_miktar:$('input[name="gelir_miktar"]').val(),sube:$('input[name="sube"]').val()},
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
          beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
                document.getElementById('kasadefterigidertablo').innerHTML = result.gider;
            document.getElementById('kasadefterigelirtablo').innerHTML = result.gelir;
             document.getElementById('toplamgelir').innerHTML = result.toplamgelir;
               document.getElementById('toplamgider').innerHTML = result.toplamgider;
                  document.getElementById('kasaacilis').innerHTML = result.kasaacilis;
               document.getElementById('kasatoplam').innerHTML = result.kasatoplam;
                 $('#preloader').hide();
        },
        error: function (request, status, error) {
           document.getElementById('hata').innerHTML = request.responseText;
             $('#preloader').hide();
        }
    });
    }
    else{
         alert('Lütfen tüm bilgileri giriniz');
    }
});
$('#kasadefterigelirtablo').on('click','a[name="kasadefterigirdisil"]',function(e){
    if(confirm('Girdi silmek istediğinize emin misiniz? Onayladığınız takdirde bu işlem geri alınamaz')){
         e.preventDefault();
        $.ajax({
        type: "GET",
        url: '/isletmeyonetim/kasadefterigirdisil',
        data:  {girdi_id:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
           beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {
               document.getElementById('kasadefterigidertablo').innerHTML = result.gider;
            document.getElementById('kasadefterigelirtablo').innerHTML = result.gelir;
             document.getElementById('toplamgelir').innerHTML = result.toplamgelir;
               document.getElementById('toplamgider').innerHTML = result.toplamgider;
                  document.getElementById('kasaacilis').innerHTML = result.kasaacilis;
               document.getElementById('kasatoplam').innerHTML = result.kasatoplam;
                $('#preloader').hide();
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
              $('#preloader').hide();
        }
    });
    }
});
$('#kasadefterigidertablo').on('click','a[name="kasadefterigirdisil"]',function(e){
    if(confirm('Girdi silmek istediğinize emin misiniz? Onayladığınız takdirde bu işlem geri alınamaz')){
         e.preventDefault();
        $.ajax({
        type: "GET",
        url: '/isletmeyonetim/kasadefterigirdisil',
        data:  {girdi_id:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
          beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {
             document.getElementById('kasadefterigidertablo').innerHTML = result.gider;
            document.getElementById('kasadefterigelirtablo').innerHTML = result.gelir;
             document.getElementById('toplamgelir').innerHTML = result.toplamgelir;
               document.getElementById('toplamgider').innerHTML = result.toplamgider;
                  document.getElementById('kasaacilis').innerHTML = result.kasaacilis;
               document.getElementById('kasatoplam').innerHTML = result.kasatoplam;
               $('#preloader').hide();
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $('#preloader').hide();
        }
    });
    }
});
$('#kasatarih').change(function(e){
    e.preventDefault();
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/kasadefteri',
        data:  {kasatarih:$('#kasatarih').val(),sube:$('input[name="sube"]').val()},
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {
             document.getElementById('kasadefterigidertablo').innerHTML = result.gider;
             document.getElementById('kasadefterigelirtablo').innerHTML = result.gelir;
              document.getElementById('toplamgelir').innerHTML = result.toplamgelir;
               document.getElementById('toplamgider').innerHTML = result.toplamgider;
                document.getElementById('kasaacilis').innerHTML = result.kasaacilis;
               document.getElementById('kasatoplam').innerHTML = result.kasatoplam;
              $('#preloader').hide();
        },
        error: function (request, status, error) {
            document.getElementById('hata').innerHTML = request.responseText;
              $('#preloader').hide();
        }
    });
});
$('#hizmetekle_personel').click(function(e){
});
$('#hizmetekle_personel_superadmin').click(function(e){
     e.preventDefault();
     $.ajax({
        type: "GET",
        url: '/sistemyonetim/personelhizmetekle/'+$('#personelid').val(),
        data:  {hizmetler:$('select[name="hizmetler"]').val(),sube:$('input[name="sube"]').val()},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            alert('Seçtiğiniz hizmetler personele başarı ile eklendi');
              document.getElementById('personelsunulanhizmetler').innerHTML = result;
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#arananhizmet').on('paste keyup',function(e){
    e.preventDefault();
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/personelhizmetara/'+$('#personelid').val(),
        data: {hizmet:$('#arananhizmet').val(),sube:$('input[name="sube"]').val()},
         dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
              document.getElementById('personelsunulanhizmetler').innerHTML = result;
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#personelsunulanhizmetler').on('click','a[name="hizmetsil_personel"]',function(e){
    e.preventDefault();
    if(confirm('Hizmeti silmek istediğinize emin misiniz? Onayladığınız takdirde bu işlem geri alınamaz')){
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/personelhizmetsil/'+$('#personelid').val(),
        data: {hizmet:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
         dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
              document.getElementById('personelsunulanhizmetler').innerHTML = result;
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
 }
});
$('#personelsunulanhizmetler').on('click','a[name="hizmetsil_personel_superadmin"]',function(e){
    e.preventDefault();
    if(confirm('Hizmeti silmek istediğinize emin misiniz? Onayladığınız takdirde bu işlem geri alınamaz')){
    $.ajax({
        type: "GET",
        url: '/sistemyonetim/personelhizmetsil/'+$('#personelid').val(),
        data: {hizmet:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
         dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
              document.getElementById('personelsunulanhizmetler').innerHTML = result;
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
 }
});
$('#profil_resim').change(function(e){
   $('#personelresimyukle').submit();
});
$('#profil_resim_superadmin').change(function(e){
   $('#personelresimyukle_superadmin').submit();
});
$('#personelresimyukle').on('submit',function(e){
    e.preventDefault();
     $.ajax({
        type: "POST",
        url: '/isletmeyonetim/personelprofilresmiyukle/'+$('#personelid').val(),
        dataType: "text",
        data : new FormData($(this)[0]),
        contentType: false,
        cache: false,
        processData:false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            document.getElementById('profilresim').setAttribute('src','/'+result);
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#personelresimyukle_superadmin').on('submit',function(e){
    e.preventDefault();
     $.ajax({
        type: "POST",
        url: '/sistemyonetim/personelprofilresmiyukle/'+$('#personelid').val(),
        dataType: "text",
        data : new FormData($(this)[0]),
        contentType: false,
        cache: false,
        processData:false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            document.getElementById('profilresim').setAttribute('src','/'+result);
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#personelekle').click(function(e){
    e.preventDefault();
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/personelekle',
         data: {personeladi:$('#personeladiyeniekle').val(),sube:$('input[name="sube"]').val()},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
             alert('Personel bilgisi başarı ile eklendi');
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#mevcutisletmeduzenleme_isletmeadmin').on('click','a[name="personelsil"]',function(e){
    e.preventDefault();
     if(confirm('Personeli silmek istediğinize emin misiniz? Bu işlem geri alınamaz')){
      $.ajax({
        type: "GET",
        url: '/isletmeyonetim/personelsil',
         data: {personelno:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
             alert('Personel bilgisi başarı ile silindi');
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
        }
    }); }
});
$('#mevcutisletmeduzenleme').on('click','a[name="personelsil"]',function(e){
    e.preventDefault();
    if(confirm('Personeli silmek istediğinize emin misiniz? Bu işlem geri alınamaz')){
          $.ajax({
        type: "GET",
        url: '/sistemyonetim/personelsil/'+$('#isletmeid').val(),
         data: {personelno:$(this).attr('data-value')},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            document.getElementById('personellistesimevcut').innerHTML = result;
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
    }
});
$('#yenimusteritemsilcisi').on('submit',function(e){
    e.preventDefault();
    var isadmin = 0;
   if($('#admin').is(":checked")) isadmin= 1;
  $.ajax({
        type: "POST",
        url: '/sistemyonetim/yenimusteritemsilcisiekle',
         data: $('#yenimusteritemsilcisi').serialize()+'&isadmin='+isadmin,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
             alert('Müşteri temsilcisi bilgileri başarı ile eklendi');
             window.location.href = '/sistemyonetim/musteritemsilcileri';
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#yenihizmetgir').click(function(e){
    e.preventDefault();
    if($('#hizmetadi_yeni').val()=='' ||$('#hizmetkateogirisi_yeni').val()==0){
        alert('Lütfen tüm bilgileri eksiksiz giriniz!');
    }
    else{
          $.ajax({
        type: "GET",
        url: '/sistemyonetim/sistemeyenihizmetekle',
         data: {hizmetadi:$('#hizmetadi_yeni').val(),hizmetkategori:$('#hizmetkateogirisi_yeni').val()},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            alert($('#hizmetadi_yeni').val()+" adlı hizmet sisteme başarı ile eklendi");
            $('#hizmetlerlistesi_bayan').append(result);
            $('#hizmetlerlistesi_bay').append(result);
            $('#personelsunulanhizmetlerbayan_yeni').append(result);
             $('#personelsunulanhizmetlerbay_yeni').append(result);
             $('.modal_kapat').trigger('click');
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
    }
});
$('#yenihizmetgir_isletme').click(function(e){
    e.preventDefault();
    if($('#hizmetadi_yeni').val()=='' ||$('#hizmetkateogirisi_yeni').val()==0){
        alert('Lütfen tüm bilgileri eksiksiz giriniz!');
    }
    else{
          $.ajax({
        type: "GET",
        url: '/isletmeyonetim/sistemeyenihizmetekle',
         data: {hizmetadi:$('#hizmetadi_yeni').val(),hizmetkategori:$('#hizmetkateogirisi_yeni').val(),sube:$('input[name="sube"]').val()},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            alert($('#hizmetadi_yeni').val()+" adlı hizmet sisteme başarı ile eklendi");
            $('#hizmetlerlistesi_bayan').append(result);
            $('hizmetlerlistesi_bay').append(result);
           $('#personelsunulanhizmetlerbayan_yeni').append(result);
             $('#personelsunulanhizmetlerbay_yeni').append(result);
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
    }
});
$('#yenisubegir_isletme').on('submit', function(e){
    e.preventDefault();
    if($('#subeadi_yeni').val()=='' ||$('#subeadres_yeni').val()=='' ||$('#subetel_yeni').val()==''){
        alert('Lütfen tüm bilgileri eksiksiz giriniz!');
    }
    else{
          $.ajax({
        type: "GET",
        url: '/isletmeyonetim/yenisubeekle',
         data: {subeadi:$('#subeadi_yeni').val(),subeadres:$('#subeadres_yeni').val(),subetel:$('#subetel_yeni').val()},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            alert($('#subeadi_yeni').val()+" şubesi sisteme başarı ile eklendi");
            $('#subelistesi').append(result);
           /* $('#hizmetlerlistesi_bayan').append(result);
            $('hizmetlerlistesi_bay').append(result);
           $('#personelsunulanhizmetlerbayan_yeni').append(result);
             $('#personelsunulanhizmetlerbay_yeni').append(result);*/
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
    }
});
$('#subelistesi').on('click','a[name="subepasifet"]',function(e){
    e.preventDefault();
    var sube_id = $(this).attr('data-value');
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/subepasifet',
         data: {subeid:sube_id},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            alert("Şube başarıyla pasif edildi");
          $('span[name="subeislembuton"][data-value="'+result+'"]').empty();
             $('span[name="subeislembuton"][data-value="'+result+'"]').append('<a title="Şube Aktif Et" name="subeaktifet" style="font-size: 20px;cursor: pointer;" data-value="'+result+'" class="icon"><div class="icon"><span class="mdi mdi-check-circle"></span></div><span class="icon-class"></span></div> </a>');
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
});
$('#subelistesi').on('click','a[name="subeaktifet"]',function(e){
    e.preventDefault();
      var sube_id = $(this).attr('data-value');
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/subepasifet',
         data: {subeid:sube_id},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            alert("Şube başarıyla aktif edildi");
            $('span[name="subeislembuton"][data-value="'+result+'"]').empty();
             $('span[name="subeislembuton"][data-value="'+result+'"]').append('<a title="Şube Pasif Et" name="subepasifet" style="font-size: 20px;cursor: pointer;" data-value="'+result+'" class="icon"><div class="icon"><span class="mdi mdi-minus-circle"></span></div><span class="icon-class"></span></div> </a>');
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
});
$('#yeniisletmeyetikilisigir').click(function(e){
    e.preventDefault();
    if($('#yetkiliadi_yeni').val()!="" &&$('#yetkili_eposta_yeni').val()!="" &&$('#yetkili_sifre_yeni').val()!="" &&$('#yetkili_sifre_tekrar_yeni').val()!="" ){
         if($('#yetkili_sifre_yeni').val()!= $('#yetkili_sifre_tekrar_yeni').val()){
            alert('Şifreler uyuşmamaktadır. Lütfen yeniden deneyiniz!');
        }
        else{
             $.ajax({
        type: "GET",
        url: '/sistemyonetim/yeniyetkilibilgisiekle',
         data: {adsoyad:$('#yetkiliadi_yeni').val(),ceptelefon:$('#yetkili_cep_telefon_yeni').val(), eposta:$('#yetkili_eposta_yeni').val(),sifre:$('#yetkili_sifre_yeni').val()},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
             alert($('#yetkiliadi_yeni').val()+' isimli yetkili & kullanıcı başarı ile eklendi');
            document.getElementById('isletmeyetkililiste').innerHTML = result;
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
        }
    }
    else{
      alert('Lütfen tüm bilgileri giriniz!');
    }
});
$('#yeniisletmeturugir').click(function(e){
    e.preventDefault();
    if($('#isletmeturuadi_yeni').val() == ""){
        alert('Lütfen işletme türünü giriniz');
    }
    else{
          $.ajax({
        type: "GET",
        url: '/sistemyonetim/yeniisletmeturuekle',
         data: {isletmeturu:$('#isletmeturuadi_yeni').val()},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
             alert($('#isletmeturuadi_yeni').val()+' işletme türü olarak sisteme başarı ile eklendi');
            document.getElementById('isletmeturulistesi').innerHTML = result;
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
    }
});
$('#yenipersonelgir').click(function(e){
    e.preventDefault();
    var formData = new FormData();
    if($('#personeladi_yeni').val() == ""){
        alert('Lütfen personel adı giriniz');
    }
    else{
       $.ajax({
        type: "GET",
        url: '/sistemyonetim/yenipersonelgir',
         data: $('#yenipersonelgirisi').serialize(),
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
             alert($('#personeladi_yeni').val()+' adlı personel sisteme başarı ile eklendi');
            $('#personelliste').append(result);
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
    }
});
$('#yenipersonelgir_isletme').click(function(e){
    e.preventDefault();
    var formData = new FormData();
    if($('#personeladi_yeni').val() == ""){
        alert('Lütfen personel adı giriniz');
    }
    else{
       $.ajax({
        type: "GET",
        url: '/isletmeyonetim/yenipersonelgir',
         data: $('#yenipersonelgirisi').serialize(),
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
             alert($('#personeladi_yeni').val()+' adlı personel sisteme başarı ile eklendi');
            document.getElementById('personelliste').innerHTML = result;
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
    }
});
$('#illistesi').change(function(e){
     $.ajax({
        type: "GET",
        url: '/sistemyonetim/ilcelistele',
         data: {il:$('#illistesi').val()},
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            document.getElementById('ilcelistesi').innerHTML = result;
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
});
$('#mevcutisletmeduzenleme').on('click','a[name="gorsel"]',function(e){
    e.preventDefault();
    if(confirm('Görseli silmek istediğinize emin misiniz? Bu işlem geri alınamaz')){
         $.ajax({
        type: "GET",
        url: '/sistemyonetim/gorselsil',
        data:{gorselid:$(this).attr('data-value')},
        dataType: "text",
         headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            document.getElementById('gorselbolumu').innerHTML = result;
       },
       error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
    }
});
$('#gorselbolumu').on('click','a[name="gorsel_sil"]',function(e){
    e.preventDefault();
    var gorsel_id =$(this).attr('data-value')
    swal({
        title: "Emin misiniz?",
        text: "Görseli silmek istediğinize emin misiniz? Bu işlem geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Sil',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
            $.ajax({
                type: "GET",
                url: '/isletmeyonetim/gorselsil',
                data:{gorselid:gorsel_id,sube:$('input[name="sube"]').val()},
                dataType: "text",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                    document.getElementById('gorselbolumu').innerHTML = result;
                     swal({
                        type: "success",
                        title: "Başarılı",
                        text:  "Görsel başarıyla kaldırıdı",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer: 3000,
                    });
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML =request.responseText;
                     $('#preloader').hide();
                }
            });
        }
    });
});
$('#fiyatlistesineeklebayan').click(function(e){
    e.preventDefault();
    var fiyatlistesibayanhtml = "";
    $('#hizmetlerlistesi_bayan :selected').each(function(){
         fiyatlistesibayanhtml += "<tr><td><input type='hidden' name='salonsunulanhizmetbayanid[]' value='"+$(this).val()+"'></td><td>"+$(this).text()+"</td><td><input type='text' class='form-control input-xs' name='salonsunulanhizmetbayanbaslangicfiyat[]'></td><td><input type='text' class='form-control input-xs' name='salonsunulanhizmetbayansonfiyat[]'></td></tr>";
    });
    document.getElementById('hizmetfiyatlaribayan').innerHTML = fiyatlistesibayanhtml;
});
$('#fiyatlistesineeklebay').click(function(e){
    e.preventDefault();
    var fiyatlistesibayhtml = "";
    $('#hizmetlerlistesi_bay :selected').each(function(){
         fiyatlistesibayhtml += "<tr><td><input type='hidden' name='salonsunulanhizmetbayid[]' value='"+$(this).val()+"'></td><td>"+$(this).text()+"</td><td><input type='text' class='form-control input-xs' name='salonsunulanhizmetbaybaslangicfiyat[]'></td><td><input type='text' class='form-control input-xs' name='salonsunulanhizmetbaysonfiyat[]'></td></tr>";
    });
    document.getElementById('hizmetfiyatlaribay').innerHTML = fiyatlistesibayhtml;
});
$('#randevuiptalet').click(function(e){
      if(confirm('Randevuyu iptal etmek istediğinize emin misiniz? Bu işlem geri alınamaz')){
        $.ajax({
        type: "GET",
        url: '/isletmeyonetim/randevuiptalet',
         data: {randevuid:$('input[name="randevuid"]').val(),sube:$('input[name="sube"]').val()} ,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            alert('Randevu başarı ile iptal edildi');
            $('#preloader').hide();
            window.location.href = '/isletmeyonetim/randevular';
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
             $('#preloader').hide();
        }
    });
      }
});
$('#musteri_sifre_degistir').on('submit',function (e) {
     e.preventDefault();
            $.ajax({
                type: "POST",
                url: '/sifredegistir',
                data: $('#musteri_sifre_degistir').serialize() ,
                dataType: "json",
                beforeSend: function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                    swal({
                        type: result.type,
                        title: result.title,
                        text:  result.text,
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer: 3000,
                    });
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML =request.responseText;
                     $('#preloader').hide();
                }
            });
})
$(document).on('click','.randevuiptalet',function(e){
    var id = $(this).attr('data-value');
    var hizmetid = $(this).attr('data-index-number');

    swal({
        title: "Emin misiniz?",
        text: "Randevuyu iptal etmek istediğinize emin misiniz? Bu işlem sonrasında randevunuzu tekrar düzenleyemezsiniz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'İptal Et',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
            $.ajax({
                type: "GET",
                url: '/isletmeyonetim/randevuiptalet',
                data: {randevuid:id,sube:$('input[name="sube"]').val(),musteriid:$('input[name="musteri_id"]').val(),hizmetId:hizmetid} ,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                    $('#modal-view-event').modal('hide');
                    swal({
                        type: "success",
                        title: "Başarılı",
                        text:  "Randevu başarıyla iptal edildi",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer: 3000,
                    });
                    if($('#randevu_liste').length){
                        $('#olusturulmaya_gore_filtre').trigger('change');
                    }
                    if($('#calendar').length)
                        takvimyukle(false,false);
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML =request.responseText;
                     $('#preloader').hide();
                }
            });
        }
    });
});
$(document).on('click','a[name="gelmedi_isaretle"]',function(e){
     var id = $(this).attr('data-value');
     var hizmetid = $(this).attr('data-index-number');
     swal({
        title: "Emin misiniz?",
        text: "Randevuyu gelmedi olarak işaretlemek istediğinize emin misiniz?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Gelmedi Olarak İşaretle',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
            $.ajax({
                type: "POST",
                url: '/isletmeyonetim/randevuyagelmedi',
                data: {randevuid:id,sube:$('input[name="sube"]').val(),_token:$('input[name="_token"]').val(),hizmetId:hizmetid} ,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                    $('#modal-view-event').modal('hide');
                    swal({
                        type: "success",
                        title: "Başarılı",
                        text:  "Randevuya gelmedi olarak işlenmiştir",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer:3000,
                    });
                    if($('#randevu_liste').length){
                         randevufiltre();
                    }
                    if($('#calendar').length){
                        takvimyukle(false,false);
                    }
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML =request.responseText;
                     $('#preloader').hide();
                }
            });
        }
    });
});
$('#randevutablo').on('click','.randevusil',function(e){
      if(confirm('Randevu kaydını silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')){
        var id = $(this).attr('data-value');
        $.ajax({
        type: "GET",
        url: '/isletmeyonetim/randevusil',
         data: {randevuid:id,sube:$('#sube_secim_randevu').val(),tarih:$('#randevutarihi_randevuliste').val()} ,
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            alert('Randevu kaydı başarıyla silindi');
            $('#preloader').hide();
              $('#table1').DataTable().destroy();
            if($(window).width()<=1024){
                var table = $("#table1").DataTable({
                    pageLength:50,
                    "lengthChange": false,
                    "order": [[ 0, "desc" ]],
                    responsive: {
                      breakpoints: [
                          { name: 'desktop', width: Infinity },
                          { name: 'tablet',  width: 1024 },
                          { name: 'fablet',  width: 768 },
                          { name: 'phone',   width: 480 }
                      ]
                    },
                     columns:[
                            { data: 'musteri' },
                            { data: 'sube'},
                            { data: 'tarihsaat' },
                            { data: 'hizmetler' },
                            { data: 'durum' },
                            { data: 'islemler' },
                       ],
                       data: result,
                });
             }
             else{
                 $("#table1").DataTable({
                    pageLength:50,
                    "lengthChange": false,
                    responsive: false,
                    "order": [[ 0, "desc" ]],
                     columns:[
                            { data: 'musteri' },
                            { data: 'sube'},
                            { data: 'tarihsaat' },
                            { data: 'hizmetler' },
                            { data: 'durum' },
                            { data: 'islemler' },
                       ],
                       data: result,
                });
             }
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
             $('#preloader').hide();
        }
    });
      }
});
$('#randevubilgiguncelle').click(function(e){
       $.ajax({
        type: "GET",
        url: '/isletmeyonetim/randevubilgiguncelle',
         data: {randevuid:$('input[name="randevuid"]').val(),randevutarihi:$('input[name="randevutarihi"]').val(),randevusaati:$('input[name="randevusaatibaslangic"]').val(),randevusaatibitis:$('input[name="randevusaatibitis"]').val(),sube:$('input[name="sube"]').val()} ,
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            alert(result.success);
            $("#calendar").fullCalendar('removeEvents',result.newevent.id);
            $("#calendar").fullCalendar('renderEvent', result.newevent,true);
            $('button[data-dismiss="modal"]').trigger('click');
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
});
$(document).on('click','.randevuonayla',function(e){
    var id = $(this).attr('data-value');
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/randevuonayla',
         data: {randevuid:id,sube:$('input[name="sube"]').val()} ,
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend : function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
                swal(
                {
                    type: "success",
                    title: "Başarılı",
                    text:  "Randevu başarıyla onaylandı",
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                }
            );
            if($('#randevu_liste').length){
                        $('#randevu_liste').DataTable().destroy()
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
                    }
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
             $('#preloader').hide();
        }
    });
});
$('#randevuonayla').click(function(e){
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/randevuonayla',
         data: {randevuid:$('input[name="randevuid"]').val(),sube:$('input[name="sube"]').val()} ,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend : function(){
            $('#preloader').show();
        },
       success: function(result)  {
            alert('Randevu başarı ile onaylandı');
             window.location.href = '/isletmeyonetim/randevular';
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
             $('#preloader').show();
        }
    });
});
$('#sifreguncelle').click(function(e){
       e.preventDefault();
       if($('#yenisifre').val()!= $('#yenisifretekrar').val() ){
          alert('Girdiğiniz şifreler uyuşmamaktadır');
       }
       else if($('#yenisifre').val()!= '' && $('#yenisifretekrar').val() !='' && $('#eskisifre').val()!=''){
          $.ajax({
        type: "GET",
        url: '/isletmeyonetim/sifredegistir',
         data: $('#sifreguncelleme').serialize(),
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {
            alert(result);
            $('#preloader').hide();
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
             $('#preloader').hide();
        }
         });
       }
       else{
          alert('Lütfen bilgileri eksiksiz giriniz');
       }
});
$('#yetkilibilgileri').on('submit',function(e){
    e.preventDefault();
     $.ajax({
        type: "POST",
        url: '/isletmeyonetim/yetkilibilgiguncelle',
         data: $('#yetkilibilgileri').serialize(),
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
           beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {
        $('#preloader').hide();
             swal(
                {
                    type: "success",
                    title: "Başarılı",
                    text:  "Bilgiler başarıyla güncellendi",
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                }
            );
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             document.getElementById('hata').innerHTML =request.responseText;
        }
         });
});
$('#bilgileriguncelle_personel_superadmin').click(function(e){
    e.preventDefault();
    $.ajax({
        type:"GET",
        url :'/sistemyonetim/personelbilgiguncelle/'+$('#personelid').val(),
        data :$('#calismasaatiguncelle_personel').serialize()+'&'+$('#personeladiunvani').serialize(),
        dataType : "text",
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            alert('Personel bilgileri başarı ile güncellendi');
            window.location.reload();
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
});
$('#bilgileriguncelle_personel').click(function(e){
    e.preventDefault();
    $.ajax({
        type:"GET",
        url :'/isletmeyonetim/personelbilgiguncelle/'+$('#personelid').val(),
        data :$('#calismasaatiguncelle_personel').serialize()+'&'+$('#personeladiunvani').serialize(),
        dataType : "text",
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
            alert('Personel bilgileri başarı ile güncellendi');
            window.location.reload();
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
});
$('#mevcutisletmeduzenleme').on('click','a[name="hizmetlistedensil"]',function(e){
    e.preventDefault();
    if(confirm('Hizmeti silmek istediğinize emin misiniz? Bu işlem geri alınamaz')){
         $.ajax({
        type:"GET",
        url:'/sistemyonetim/salonhizmetsil/'+$('#isletmeid').val(),
        data:{salonhizmetid:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        $dataType:"json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {
             document.getElementById('mevcuthizmetlistebayan').innerHTML = result.bayan;
             document.getElementById('mevcuthizmetlistebay').innerHTML = result.bay;
             $('#preloader').hide();
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
             $('#preloader').hide();
        }
    });
    }
});
$('#mevcutisletmeduzenleme_isletmeadmin').on('click','a[name="hizmetlistedensil"]',function(e){
    e.preventDefault();
    if(confirm('Hizmeti silmek istediğinize emin misiniz? Bu işlem geri alınamaz')){
         $.ajax({
        type:"GET",
        url:'/isletmeyonetim/salonhizmetsil',
        data:{salonhizmetid:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        $dataType:"json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {
             document.getElementById('mevcuthizmetlistebayan').innerHTML = result.bayan;
             document.getElementById('mevcuthizmetlistebay').innerHTML = result.bay;
             $('#preloader').hide();
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
             $('#preloader').hide();
        }
    });
    }
});
 $('#salonadinagoreara').click (function(e){
     if($('#searchable_select').val()==''){
                alert('Lütfen salon adını giriniz');
            }
            else
            {
                window.location.href = $('#searchable_select').val();
            }
 });
     $('#salonturunegoreara').click(function(e){
           var category = document.getElementById('category');
            var location = document.getElementById('location_category');
            if(category.options[category.selectedIndex].value == 0){
                alert('Lütfen salon türünü seçiniz');
            }
            else{
                 category = category.options[category.selectedIndex].value;
                if(location.options[location.selectedIndex].value == "0")
                {
                    location = '';
                }
                else{
                     location = location.options[location.selectedIndex].value;
                }
                 window.location.href = category+location;
            }
     });
$('#hizmetegoreara').click(function(e){
            var service = document.getElementById('service');
            var location = document.getElementById('location_service');
            if(service.options[service.selectedIndex].value == 0){
                alert('Lütfen aramak istediğniz hizmeti seçiniz');
            }
            else{
                 service = service.options[service.selectedIndex].value;
               if(location.options[location.selectedIndex].value == "0")
                {
                    location = '';
                }
                else{
                     location = location.options[location.selectedIndex].value;
                }
                window.location.href =service+location;
            }
});
$('#avantajara').click(function(e){
            var service = document.getElementById('service');
            var location = document.getElementById('location_service');
            if(service.options[service.selectedIndex].value == 0){
                alert('Lütfen aramak istediğniz avantajı seçiniz veya giriniz');
            }
            else{
                 service = service.options[service.selectedIndex].value;
               if(location.options[location.selectedIndex].value == "0")
                {
                    location = '';
                }
                else{
                     location = location.options[location.selectedIndex].value;
                }
                window.location.href ='/avantajlikampanyalar'+service+location;
            }
});
$('#kampanyafirsatbildirim').change(function(e){
      e.preventDefault();
      var data = 0;
      if(this.checked)
        data = 1;
      else
        data = 0;
      $.ajax({
        type: "GET",
        url: '/kampanyafirastbildirimackapa',
         data: {data:data} ,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
       success: function(result)  {
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
});
$('a[name="randevuonaylamaplus"]').click(function(e){
      e.preventDefault();
      $.ajax({
        type: "GET",
        url: '/isletmeyonetim/randevuonayla',
         data: {randevuid:$(this).attr('data-value'),sube:$('input[name="sube"]').val()} ,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            alert('Randevu başarı ile onaylandı');
             window.location.href = '/isletmeyonetim/';
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
});
$('a[name="randevuiptalblock"]').click(function(e){
      e.preventDefault();
      if(confirm('Randevuyu iptal etmek istediğinize emin misiniz? Bu işlem geri alınamaz')){
             $.ajax({
        type: "GET",
        url: '/isletmeyonetim/randevuiptalet',
         data: {randevuid:$(this).attr('data-value'),sube:$('input[name="sube"]').val()} ,
        dataType: "text",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            alert('Randevu başarı ile iptal edildi');
             $('#preloader').hide();
             window.location.href = '/isletmeyonetim/';
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
      }
});
 /*$('#randevutarihiyeni,#ongorusme_tarihi').change(function(e){
      e.preventDefault();
      randevusaatlerinigetir($(this).val(),$('input[name="sube"]').val(),'');
 });*/
$('#hizmetpersonelgetir').click(function(e){
      e.preventDefault();
       $('#hizmetpersonelbolumu').empty();
      $('select[name="randevuhizmetleriyeni[]"] :selected').each(function(){
      $.ajax({
        type: "GET",
        url: '/isletmeyonetim/randevupersonelgetir',
        dataType: "text",
        data : {hizmetid:$(this).val(),sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
       success: function(result)  {
            $('#hizmetpersonelbolumu').append(result);
            $("#preloader").hide();
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $("#preloader").hide();
        }
    });
    });
});
$(document).on('submit','#yenirandevuekleform',function(e){
    e.preventDefault();
    var personelveyacihasecili = true;
    var hizmetsecili = true;
    var suregirildi = true;
    var musterisecili = true;
    var warningtext = "";
    var saatsecili = true;
    $('#yenirandevuekleform select[name="randevupersonelleriyeni[]"]').each(function(index){
        if($('#randevuekle_musteri_id').val()=="")
        {
            warningtext += "- Müşteri/danışan seçiniz.<br>";
            musterisecili = false;
        }
        if($('#yenirandevuekleform select[name="randevucihazlariyeni[]"]').eq(index).val() == '' && $(this).val()=='')
        {
            warningtext += "- En az bir personel veya cihaz seçiniz.<br>";
            personelveyacihasecili = false;
        }
        if($('#yenirandevuekleform select[name="randevuhizmetleriyeni[]"]').eq(index).val() == '')
        {
            warningtext += "- Hizmet seçiniz.<br>";
            hizmetsecili = false;
        }
        if($('#yenirandevuekleform input[name="hizmet_suresi[]"]').val() == "")
        {
            warningtext += "- Hizmet süresini giriniz.<br>";
            suregirildi =false;
        }
    });
     if($('#randevuduzenle_saat').val()==''){

        warningtext += "- Randevu saatini seçiniz.<br>";
        saatsecili = false;
    }
    if(personelveyacihasecili == false || hizmetsecili == false || suregirildi == false || musterisecili == false ||saatsecili ==false){
        swal(
                            {
                                type: "warning",
                                title: "Uyarı",
                                html:  'Devam etmek için;<br><br>'+warningtext,
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
        );
    }
     else{
        var formData = new FormData();
        formData.append('cakisanrandevuekle',1);
        formData.append('sube',$('input[name="sube"]').val());
        var other_data = $('#yenirandevuekleform').serializeArray();
        $('#yenirandevuekleform select[name="randevuyardimcipersonelleriyeni"]').each(function(index1){
            if($(this).val()=='')
                formData.append("randevuyardimcipersonelleriyeni_"+index1+"[]","");
            else{
                var $selectedOptions = $(this).find('option:selected');
                $selectedOptions.each(function(){
                    formData.append('randevuyardimcipersonelleriyeni_'+index1+'[]',$(this).val());
                });
            }
        });
        $.each(other_data,function(key,input){
            if(input != "randevuyardimcipersonelleriyeni")
                formData.append(input.name,input.value);
        });
        for (var pair of formData.entries()) {
            console.log(pair[0]+ ', ' + pair[1]);
        }
        $.ajax({
            type: "POST",
            //url: '/isletmeyonetim/yenirandevuekle',
            url: '/isletmeyonetim/yenirandevuekle',
            dataType: "json",
            //dataType:'text',
            data : formData,
             processData: false,
                            contentType: false,
            beforeSend: function() {
                $("#preloader").show();
            },
            success: function(result)  {
                $('#preloader').hide();
                if(result.cakismavar)
                {
                    swal(
                        {
                            type: "warning",
                            title: "<h2 style='font-size:40px;font-weight:bold;color:#fff'>Uyarı</h2>",
                            background: '#ff0000',
                            html: "<p style='color:#fff; font-size:20px'>Bu randevu aşağıdakilerle çakışmaktadır</p>"+result.cakismavar+
                                  "<p style='color:#fff; font-size:20px;padding:10px;border:1px solid #fff;border-radius:10px;margin:0 20px 0 20px'>Yine de kayıt etmek istiyor musunuz?",
                            showCancelButton: true,
                            confirmButtonColor: '#5C008E',
                            confirmButtonText: 'Randevuyu Oluştur',
                            cancelButtonText: "Vazgeç",
                            confirmButtonClass: 'btn btn-primary',
                            cancelButtonClass: 'btn btn-danger',
                        }
                    ).then(function(result2){
                        if(result2.value)
                        {
                            $.ajax({
                                type: "POST",
                                //url: '/isletmeyonetim/yenirandevuekle',
                                url: '/isletmeyonetim/yenirandevuekle',
                                dataType: "json",
                                //dataType:'text',
                                data: formData,
                                processData: false,
                                contentType: false,
                                beforeSend: function() {
                                    $("#preloader").show();
                                },
                                success: function(result3){
                                    $('#preloader').hide();
                                     console.log('randevu eklendi');
                                    $('.hizmetler_bolumu div.row').each(function(e){
                                       // if($(this).attr('data-value')!="0")
                                           // $(this).remove();
                                    });
                                    $('#yenirandevuekleform').trigger('reset');

                                    $('#modal-view-event-add').modal('hide');
                                    swal(
                                    {
                                        type: "success",
                                        title: "Başarılı",
                                        html: result3.success,
                                        showCloseButton: false,
                                        showCancelButton: false,
                                        showConfirmButton:false,
                                        timer:result3.timer,
                                    }
                                    );
                                    if($('#calendar').length)
                                         takvimyukle(false,false);
                                },
                                error: function (request, status, error) {
                                    $('#preloader').hide();
                                    document.getElementById('hata').innerHTML = request.responseText;
                                }
                            });
                        }
                    });
                }
                else if(result.eklenemez)
                {
                    swal(
                        {
                                            type: "warning",
                                            title: "Uyarı",
                                            html: result.eklenemez,
                                            showCloseButton: false,
                                            showCancelButton: false,
                                            showConfirmButton:false
                        }
                    );
                }
                else
                {
                    $('.hizmetler_bolumu div.row').each(function(e){
                        if($(this).attr('data-value')!="0")
                         $(this).remove();
                    });
                    $('#yenirandevuekleform').trigger('reset');
                            $('#randevuekle_musteri_id').val('0').trigger('change');
                            $('#randevupersonelleriyeni').val('0').trigger('change');
                            $('#randevuhizmetleriyeni').val('0').trigger('change');
                    $('#modal-view-event-add').modal('hide');
                    swal(
                        {
                                            type: "success",
                                            title: "Başarılı",
                                            html: result.success,
                                            showCloseButton: false,
                                            showCancelButton: false,
                                            showConfirmButton:false,
                                            timer: result.timer,
                        }
                    );
                    if($('#calendar').length)
                        takvimyukle(false,false);
                }
            },
            error: function (request, status, error) {
                document.getElementById('hata').innerHTML = request.responseText;
                $('button[data-dismiss="modal"]').trigger('click');
                $('#preloader').hide();
            }
        });
     }
});
$(document).on('submit','#randevuduzenleform',function(e){
    e.preventDefault();
     var personelveyacihasecili = true;
    var hizmetsecili = true;
    var suregirildi = true;
    var musterisecili = true;
    var warningtext = "";
    var saatsecili = true;
    $('#randevuduzenleform select[name="randevupersonelleriyeni[]"]').each(function(index){
        if($('#randevuduzenle_musteri_id').val()=="")
        {
            warningtext += "- Müşteri/danışan seçiniz.<br>";
            musterisecili = false;
        }
        if($('#randevuduzenleform select[name="randevucihazlariyeni[]"]').eq(index).val() == '' && $(this).val()=='')
        {
            warningtext += "- En az bir personel veya cihaz seçiniz.<br>";
            personelveyacihasecili = false;
        }
        if($('#randevuduzenleform select[name="randevuhizmetleriyeni[]"]').eq(index).val() == '')
        {
            warningtext += "- Hizmet seçiniz.<br>";
            hizmetsecili = false;
        }
        if($('#randevuduzenleform input[name="hizmet_suresi[]"]').val() == "")
        {
            warningtext += "- Hizmet süresini giriniz.<br>";
            suregirildi =false;
        }
    });
      if($('#randevuduzenle_saat').val()==''){

        warningtext += "- Randevu saatini seçiniz.<br>";
        saatsecili = false;
    }
    if(personelveyacihasecili == false || hizmetsecili == false || suregirildi == false || musterisecili == false ||saatsecili==false){
        swal(
                            {
                                type: "warning",
                                title: "Uyarı",
                                html:  'Devam etmek için;<br><br>'+warningtext,
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
        );
    }
     else{
       var formData = new FormData();
        formData.append('cakisanrandevuekle',1);
        var other_data = $('#randevuduzenleform').serializeArray();
        $('#randevuduzenleform select[name="randevuyardimcipersonelleriyeni"]').each(function(index1){
            if($(this).val()=='')
                formData.append("randevuyardimcipersonelleriyeni_"+index1+"[]","");
            else{
                var $selectedOptions = $(this).find('option:selected');
                $selectedOptions.each(function(){
                    formData.append('randevuyardimcipersonelleriyeni_'+index1+'[]',$(this).val());
                });
            }
        });
        $.each(other_data,function(key,input){
            if(input != "randevuyardimcipersonelleriyeni")
                formData.append(input.name,input.value);
        });
        for (var pair of formData.entries()) {
            console.log(pair[0]+ ', ' + pair[1]);
        }
         $.ajax({
        type: "POST",
        url: '/isletmeyonetim/randevuguncelle',
        dataType: "json",
        data : $('#randevuduzenleform').serialize(),
        beforeSend: function() {
            $("#preloader").show();
        },
       success: function(result)  {
         $('#preloader').hide();
            if(result.cakismavar)
            {
                swal(
                    {
                        type: "warning",
                        title: "<h2 style='font-size:40px;font-weight:bold;color:#fff'>Uyarı</h2>",
                        background: '#ff0000',
                        html: "<p style='color:#fff; font-size:20px'>Bu randevu aşağıdakilerle çakışmaktadır</p>"+result.cakismavar+
                              "<p style='color:#fff; font-size:20px;padding:10px;border:1px solid #fff;border-radius:10px;margin:0 20px 0 20px'>Yine de kayıt etmek istiyor musunuz?",
                        showCancelButton: true,
                        confirmButtonColor: '#5C008E',
                        confirmButtonText: 'Randevuyu Oluştur',
                        cancelButtonText: "Vazgeç",
                        confirmButtonClass: 'btn btn-primary',
                        cancelButtonClass: 'btn btn-danger',
                    }
                ).then(function(result2){
                    if(result2.value)
                    {
                        $.ajax({
                            type: "POST",
                            //url: '/isletmeyonetim/yenirandevuekle',
                            url: '/isletmeyonetim/randevuguncelle',
                            dataType: "json",
                            //dataType:'text',
                            data: formData,
                            processData: false,
                            contentType: false,
                            beforeSend: function() {
                                $("#preloader").show();
                            },
                            success: function(result3)  {
                                $('#preloader').hide();
                                swal(
                                {
                                    type: "success",
                                    title: "Başarılı",
                                    html: result3.success,
                                    showCloseButton: false,
                                    showCancelButton: false,
                                    showConfirmButton:false,
                                    timer:result3.timer,
                                }
                                );
                                $('#randevu-duzenle-modal').modal('hide');
                                $('#modal-view-event').modal('hide');
                                if($('#calendar').length)
                                     takvimyukle(false,false);
                            },
                            error: function (request, status, error) {
                                $('#preloader').hide();
                                document.getElementById('hata').innerHTML = request.responseText;
                            }
                        });
                    }
                });
            }
            else
            {
                $('#randevu-duzenle-modal').modal('hide');
                $('#modal-view-event').modal('hide');
                swal(
                    {
                        type: "success",
                        title: "Başarılı",
                        html: result.success,
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer:result.timer,
                    }
                );
                if($('#calendar').length)
                     takvimyukle(true,false);
                $("#randevuduzenleform")[0].reset();
            }
        },
        error: function (request, status, error) {
            document.getElementById('hata').innerHTML = request.responseText;
            $('button[data-dismiss="modal"]').trigger('click');
            $('#preloader').hide();
        }
    });
     }
});
$('#smsiptaltalebi').on('submit',function(e){
    e.preventDefault();
    if($('input[name=neden]:checked', this).val()==3 && $('textarea[name=digerneden]').val() == ''){
        alert('Lütfen SMS kampanya gönderim listemizden neden çıkmak istediğinizi belirtiniz');
    }
    else{
       $.ajax({
        type: "GET",
        url: '/smskampanyabildirimiptal',
        dataType: "text",
        data : $('#smsiptaltalebi').serialize(),
          beforeSend: function() {
            $("#preloader").show();
        },
       success: function(result)  {
             alert(result);
            $('#preloader').hide();
            window.location.href = '/';
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
            $('#preloader').hide();
        }
       });
    }
});
$('#epostaiptaltalebi').on('submit',function(e){
    e.preventDefault();
    if($('input[name=neden]:checked', this).val()==3 && $('textarea[name=digerneden]').val() == ''){
        alert('Lütfen e-posta kampanya gönderim listemizden neden çıkmak istediğinizi belirtiniz');
    }
    else{
       $.ajax({
        type: "GET",
        url: '/mailkampanyabildirimiptal',
        dataType: "text",
        data : $('#epostaiptaltalebi').serialize(),
          beforeSend: function() {
            $("#preloader").show();
        },
       success: function(result)  {
             alert(result);
            $('#preloader').hide();
            window.location.href = '/';
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
            $('#preloader').hide();
        }
       });
    }
});
$('#yenismslistesiekle').on('submit',function(e){
    e.preventDefault();
    var liste=  $('#listedosyasi_yeni').get(0).files[0];
    var formData = new FormData();
    formData.append('listedosyasi_yeni',liste);
     $.ajax({
        type: "POST",
        url: '/isletmeyonetim/yenismslistesiekle',
        dataType: "json",
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
            alert(result.sonuc);
            $('#musteriportfoy').append(result.liste);
              $('#preloader').hide();
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
    });
  $('a[name="bilgileriduzenle"]').click(function(e){
    e.preventDefault();
     $('#smslistebilgiid').val($(this).attr('data-value'));
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/smslistedetaybilgigetir',
        dataType: "json",
        data : {detayid : $(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {
            $('#preloader').hide();
            $('#liste_ad_soyad').val(result.ad_soyad);
            $('#liste_cep_telefon').val(result.cep_telefon);
            if(result.sms_kampanya_karaliste == 1){
                $('#liste_karaliste').attr('checked',true);
                $('#karalistenedengirdi').attr('style','display:block');
                if(result.sms_kampanya_karaliste_nedeni == 'Çok fazla gönderim yapıldığını bildirdi')
                    $('#liste_karalistenedeni1').attr('checked',true);
                else if(result.sms_kampanya_karaliste_nedeni == 'Gönderimlerle ilgilenmediğini bildirdi')
                    $('#liste_karalistenedeni2').attr('checked',true);
                else{
                    $('#liste_karalistenedeni3').attr('checked',true);
                    $('#liste_karalistenedeni_diger').val(result.sms_kampanya_karaliste_nedeni);
                }
            }
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
    $('#smsbilgiguncellememodal').trigger('click');
  });
$("#hepsinisec").change(function(){
    var count =0;
     if(this.checked){
         $('#secilenmusterisayisi').empty();
         $('input:checkbox').not(this).prop('checked', this.checked);
        count = $('input:checkbox').not(this).prop('checked', this.checked).length;
        $('#secilenmusterisayisi').text(count);
     }
     else{
         count=0;
         $('input:checkbox').not(this).prop('checked', false);
         $('#secilenmusterisayisi').empty();
         $('#secilenmusterisayisi').text(count);
     }
});
 $('input:checkbox').not('#hepsinisec').change(function(){
     count = $(':checkbox:checked').length;
     $('#secilenmusterisayisi').empty();
     $('#secilenmusterisayisi').text(count);
 });
  $('#liste_karaliste').change(function(e){
      e.preventDefault();
      if(this.checked)
          $('#karalistenedengirdi').attr('style','display:block');
      else
          $('#karalistenedengirdi').attr('style','display:none');
  });
  $('#smsbilgiguncelle').on('submit',function(e){
      e.preventDefault();
      var guncellenebilir = 0;
      if($('#liste_karaliste').prop('checked')){
           if($('input[name="liste_karalistenedeni"]:checked').val() == 3 && $('#liste_karalistenedeni_diger').val() == '')
              guncellenebilir =0;
          else
            guncellenebilir=1;
      }
      else{
        guncellenebilir=1;
      }
      if(guncellenebilir){
          alert($('#smsbilgiguncelle').serialize());
           $.ajax({
        type: "GET",
        url: '/isletmeyonetim/smsbilgiguncelle',
        dataType: "text",
        data : $('#smsbilgiguncelle').serialize(),
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {
            $('#preloader').hide();
            alert('Bilgiler başarı ile güncellendi');
            window.location.refresh;
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
      }
      else
        alert('Lütfen tüm bilgileri eksiksiz giriniz. Lütfen kara listeye eklemenizin diğer nedenini belirtiniz!');
  });
  $(document).on('click','a[name="smstaslaklari"]',function(e){
      e.preventDefault();
      var dataval = $(this).attr("data-value");
      var id = '#smstaslak'+dataval;
      var baslikid='#smstaslakbaslik'+dataval;
      $('#sablon_baslik').val($(baslikid).val());
      $('#smsmesaj').val($(id).val());
      var len = $('#smsmesaj').val().length;
      if(len<=155){
        $('#karaktersayisi').attr('style','color:black;background-color:white');
            $('#karaktersayisi').text(len+' (Gönderim başına 1 sms üzerinden ücretlendirilecektir)');
         }
           else if(len>155 && len <=301) {
              $('#karaktersayisi').attr('style','color:white;background-color:orange');
                                          $('#karaktersayisi').text(len+' (Gönderim başına 2 sms üzerinden ücretlendirilecektir)');
            }
                                     else if(len>301 && len <=453) {
                                         $('#karaktersayisi').attr('style','color:white;background-color:red');
                                        $('#karaktersayisi').text(len+' (Gönderim başına 3 sms üzerinden ücretlendirilecektir)');
                                     }
                                    else if(len>453 && len <=607) {
                                          $('#karaktersayisi').attr('style','color:white;background-color:red');
                                         $('#karaktersayisi').text(len+' (Gönderim başına 4 sms üzerinden ücretlendirilecektir)');
                                    }
      $('html, body').animate({ scrollTop: $('#smsgonderimkismi').offset().top }, 'slow');
  });
  $('#toplusmsgonder').click(function(e){
        if($('#smsmesaj').val() != '' && $('#toplu_musteri :selected').length>0)
        {
             e.preventDefault();
               $.ajax({
                type: "POST",
                url: '/isletmeyonetim/toplusmsgonder',
                dataType: "json",
                data : $('#sablonsmsform').serialize(),
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                     swal(
                            {
                                type: result.status,
                                title: result.title,
                                text:  result.text,
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
                    );
                    $('#preloader').hide();
                     $('#smsmesaj').val('');
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
            });
        }
        else
              swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text:  'Lütfen alıcıları seçip mesajınız yazınız!',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
                    );
  });
  function telefonnumarasıvar(){
      /*var phoneNumberRegex = /\0\d{3}\ \d{3}\ \d{2}\ \d{2}/g;
     var phoneNumberRegex2 = /\d{3}\ \d{3}\ \d{2}\ \d{2}/g;
     var phoneNumberRegex3 = /\0\d{4}\ \d{3}\ \d{4}/g;
     var phoneNumberRegex4 = /\d{3}\ \d{3}\ \d{4}/g;
     var phoneNumberRegex5 = /\0\d{10}/g;
     var phoneNumberRegex6 = /\d{10}/g;
     var phoneNumberRegex7 = /\(\d{3}\)\d{3}\ \d{2}\ \d{2}/g;
     var phoneNumberRegex8 = /\(\d{3}\)\d{3}\-\d{2}\-\d{2}/g;
     var phoneNumberRegex9 = /\0\d{3}\-\d{3}\-\d{2}\-\d{2}/g;
     var phoneNumberRegex10 = /\d{3}\-\d{3}\-\d{2}\-\d{2}/g;
     var phoneNumberRegex11 = /\0\(\d{3}\)\d{3}\ \d{2}\ \d{2}/g;
     var phoneNumberRegex12 = /\0\(\d{3}\)\d{3}\-\d{2}\-\d{2}/g;
     var phoneNumberRegex13 = /\(\0\d{3}\)\d{3}\ \d{2}\ \d{2}/g;
     var phoneNumberRegex14 = /\(\0\d{3}\)\d{3}\-\d{2}\-\d{2}/g;
    var questionText = document.getElementById('smsmesaj').value;
    var phoneNumberDetected = questionText.match(phoneNumberRegex);
    var phoneNumberDetected2 = questionText.match(phoneNumberRegex2);
    var phoneNumberDetected3 = questionText.match(phoneNumberRegex3);
     var phoneNumberDetected4 = questionText.match(phoneNumberRegex4);
    var phoneNumberDetected5 = questionText.match(phoneNumberRegex5);
    var phoneNumberDetected6 = questionText.match(phoneNumberRegex6);
     var phoneNumberDetected7 = questionText.match(phoneNumberRegex7);
    var phoneNumberDetected8 = questionText.match(phoneNumberRegex8);
    var phoneNumberDetected9 = questionText.match(phoneNumberRegex9);
     var phoneNumberDetected10 = questionText.match(phoneNumberRegex10);
    var phoneNumberDetected11 = questionText.match(phoneNumberRegex11);
    var phoneNumberDetected12 = questionText.match(phoneNumberRegex12);
        var phoneNumberDetected13 = questionText.match(phoneNumberRegex13);
    var phoneNumberDetected14 = questionText.match(phoneNumberRegex14);
    var formattedSubject = $('#smsmesaj').val();
    var formattedPhone = "Telefon numarası girilemez";
    var hasphonenumber = false;
    if (phoneNumberDetected != null)
    {
         phoneNumberDetected = String(phoneNumberDetected);
         formattedSubject = questionText.replace(phoneNumberDetected, formattedPhone);
         hasphonenumber = true;
    }
    if (phoneNumberDetected2 != null)
    {
         phoneNumberDetected2 = String(phoneNumberDetected2);
         formattedSubject = questionText.replace(phoneNumberDetected2, formattedPhone);
         hasphonenumber = true;
    }
      if (phoneNumberDetected3 != null)
    {
         phoneNumberDetected3 = String(phoneNumberDetected3);
         formattedSubject = questionText.replace(phoneNumberDetected3, formattedPhone);
         hasphonenumber = true;
    }
      if (phoneNumberDetected4 != null)
    {
         phoneNumberDetected4 = String(phoneNumberDetected4);
         formattedSubject = questionText.replace(phoneNumberDetected4, formattedPhone);
         hasphonenumber = true;
    }
      if (phoneNumberDetected5 != null)
    {
         phoneNumberDetected5 = String(phoneNumberDetected5);
         formattedSubject = questionText.replace(phoneNumberDetected5, formattedPhone);
         hasphonenumber = true;
    }
      if (phoneNumberDetected6 != null)
    {
         phoneNumberDetected6 = String(phoneNumberDetected6);
         formattedSubject = questionText.replace(phoneNumberDetected6, formattedPhone);
         hasphonenumber = true;
    }
       if (phoneNumberDetected7 != null)
    {
        hasphonenumber = true;
         phoneNumberDetected7 = String(phoneNumberDetected7);
         formattedSubject = questionText.replace(phoneNumberDetected7, formattedPhone);
    }
    if (phoneNumberDetected8 != null)
    {
            hasphonenumber = true;
         phoneNumberDetected8 = String(phoneNumberDetected8);
         formattedSubject = questionText.replace(phoneNumberDetected8, formattedPhone);
    }
      if (phoneNumberDetected9 != null)
    {
hasphonenumber = true;
         phoneNumberDetected9 = String(phoneNumberDetected9);
         formattedSubject = questionText.replace(phoneNumberDetected9, formattedPhone);
    }
      if (phoneNumberDetected10 != null)
    {
hasphonenumber = true;
         phoneNumberDetected10 = String(phoneNumberDetected10);
         formattedSubject = questionText.replace(phoneNumberDetected10, formattedPhone);
    }
      if (phoneNumberDetected11 != null)
    {
hasphonenumber = true;
         phoneNumberDetected11 = String(phoneNumberDetected11);
         formattedSubject = questionText.replace(phoneNumberDetected12, formattedPhone);
    }
      if (phoneNumberDetected12 != null)
    {
        hasphonenumber = true;
         phoneNumberDetected12 = String(phoneNumberDetected12);
         formattedSubject = questionText.replace(phoneNumberDetected12, formattedPhone);
    }
      if (phoneNumberDetected13 != null)
    {
        hasphonenumber = true;
         phoneNumberDetected13 = String(phoneNumberDetected13);
         formattedSubject = questionText.replace(phoneNumberDetected13, formattedPhone);
    }
      if (phoneNumberDetected14 != null)
    {
        hasphonenumber = true;
         phoneNumberDetected14 = String(phoneNumberDetected14);
         formattedSubject = questionText.replace(phoneNumberDetected14, formattedPhone);
    }
    $("#smsmesaj").val(formattedSubject);
    if(hasphonenumber){
            return true;
    }
    else{
        return false;
    }*/
    return false;
  }
   $('#smstaslakolarakkaydet').click(function(e){
             $.ajax({
        type: "GET",
        url: '/isletmeyonetim/smstaslakolarakkaydet',
        dataType: "json",
        data : $('#sablon_formu').serialize(),
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {
            $('#preloader').hide();
             if (result.liste!='') {
                $('#taslaklarbolumu2').empty();
                $('#sablon_formu').trigger('reset');
               $('#taslaklarbolumu2').append(result.liste);
             }
              $('.modal_kapat').trigger('click');
              $('#sablon_formu').trigger('reset');
                swal(
                            {
                                type: "success",
                                title: "Başarılı",
                                text:  result.sonuc,
                            }
                        );
        },
        error: function (request, status, error) {
            $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
  });
  $('#yenipersonelbilgiekle').on('submit',function(e){
    e.preventDefault();
     var eklenebilir = 0;
     var uyari = "";
             $.ajax({
        type: "POST",
        url: '/isletmeyonetim/personelekleduzenle',
        dataType: "json",
        data : $('#yenipersonelbilgiekle').serialize(),
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
             $('.modal_kapat').trigger('click');
            swal(
                {
                    type: result.swalstat,
                    title: result.swaltitle,
                    text: result.sonuc,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                }
            );
            $('#personel_tablo').DataTable().destroy();
            $('#personel_tablo').DataTable({
                ordering: false,
                paging: false,
                  autoWidth: false,
                   responsive: true,
                       columns:[
                       {data :'siralama' , className: "text-center"},
                       {data:'ad_soyad'},
                       { data: 'hesap_turu'},
                       { data: 'telefon' },
                       { data: 'durum'},
                       { data: 'islemler'},
                    ],
                    data: result.personeller,
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
  });
  $('#personelbilgilertablo').on('click','button[name="kullaniciyetkikaldir"]',function(e){
      e.preventDefault();
      if(confirm($('#personeladi'+$(this).attr('data-value')).val() +' isimli personelin sistem yetkisini kaldırmak istediğinize emin misiniz? Bu işlem geri alınamaz')){
               $.ajax({
        type: "GET",
        url: '/isletmeyonetim/personelsistemyetkikaldir',
        dataType: "json",
        data : {personelid:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
             alert(result.sonuc);
             $('#personelbilgilertablo').empty();
             $('#personelbilgilertablo').append(result.liste);
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
      }
  });
  $('#personelbilgilertablo').on('click','button[name="kullaniciolustur"]',function(e){
        e.preventDefault();
         $('#yetkilipersoneladi').text($('#personeladi'+$(this).attr('data-value')).val());
         $('#yetkili_personelid').val($(this).attr('data-value'));
        $('#yetkiverbutton').trigger('click');
  });
  $('#personelbilgilertablo').on('click','button[name="personelsil"]',function(e){
        e.preventDefault();
        if(confirm($('#personeladi'+$(this).attr('data-value')).val() +' isimli personeli yetkileri ile birlikte silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')){
              $.ajax({
        type: "GET",
        url: '/isletmeyonetim/personelbilgikaldir',
        dataType: "json",
        data : {personelid:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
             alert(result.sonuc);
             $('#personelbilgilertablo').empty();
             $('#personelbilgilertablo').append(result.liste);
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
        }
  });
  $('#personelyetkiolustur').on('submit',function(e){
      e.preventDefault();
      if($('#sifre_yeni2').val()!= $('#sifre_yeni_tekrar2').val()){
           alert('Girdiğiniz şifreler uyuşmamaktadır! Lütfen yeniden deneyiniz!');
      }
      else{
            $.ajax({
        type: "GET",
        url: '/isletmeyonetim/personelyetkiolustur',
        dataType: "json",
        data : $('#personelyetkiolustur').serialize(),
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
             alert(result.sonuc);
             $('#modalkapat2').trigger('click');
             $('#personelbilgilertablo').empty();
             $('#personelbilgilertablo').append(result.liste);
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
      }
  });
  $('#musteriportfoytablo').on('click','button[name="musterikaldir"]',function(e){
        e.preventDefault();
        if(confirm('Müşteriyi portföyünüzden kaldırmak istediğinize emin misiniz? Bu işlem geri alınamaz!')){
              $.ajax({
        type: "GET",
        url: '/isletmeyonetim/musteriportfoykaldir',
        dataType:'json',
        data :{musteriid:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            alert(result.sonuc);
          $('#musteriportfoytablo').empty();
            $('#musteriportfoytablo').append(result.liste);
            $('#musteriportfoysayisi').empty();
            $('#musteriportfoysayisi').append('('+result.toplammusteri+' kişi)');
             $('#preloader').hide();
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML= request.responseText;
        }
    });
        }
  });
  $(document).on('submit','#yenimusterilistesiekle',function(e){
    e.preventDefault();
    var liste=  document.getElementById("listedosyasi_yeni_musteri").files[0];
    var formData = new FormData();
    formData.append('listedosyasi_yeni_musteri',liste);
    formData.append('sube',$('input[name="sube"]').val());
     $.ajax({
        type: "POST",
        url: '/isletmeyonetim/yenimusterilistesiekle',
        dataType: "json",
        data : formData,
        contentType: false,
        cache: false,
        processData:false,
           headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
        beforeSend:function(){
            $('#preloader').show();
        },
       success: function(result)  {
            $('#preloader').hide();
            swal(
 
                            {
 
                            type: "success",
                            title: "Başarılı",
                            html: "<p>"+result.sonuc+"</p>",
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000, 
                            }
                    );
            window.location.href = '/isletmeyonetim/musteriler';
        },
        error: function (request, status, error) {
            $('#preloader').hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
  });
  $('#musteriexceleaktar').click(function(e){
     e.preventDefault();
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/musteriexceleaktar',
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
        },
        error: function (request, status, error) {
             $('#preloader').hide();
        }
    });
  });
  $('#yeniavantajyayinla').on('submit',function(e){
    e.preventDefault();
    var gorseller=  $('#isletmegorselleri').get(0).files.length;
    var kapakfoto = $('#isletmekapakfoto').get(0).files[0];
    var formData = new FormData();
    formData.append('isletmekapakfoto',kapakfoto);
    for(var i=0;i<gorseller;i++){
         formData.append('isletmegorselleri[]',$('#isletmegorselleri').get(0).files[i]);
    }
    var other_data = $('#yeniavantajyayinla').serializeArray();
    $.each(other_data,function(key,input){
        formData.append(input.name,input.value);
    });
     formData.append('kampanya_detay',$('#detayicerikhtml').html());
     $.ajax({
        type: "POST",
        url: '/sistemyonetim/yeniavantajyayinla',
        dataType: "text",
        data : formData,
        contentType: false,
        cache: false,
        processData:false,
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
            alert(result);
            window.location.href = '/sistemyonetim/avantajlar';
        },
        error: function (request, status, error) {
             $('#preloader').show();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
  });
  $('#avantajtablohtml').on('click','button[name="pasifdurumaal"]',function(e){
       e.preventDefault();
       if(confirm('Avantajı pasif duruma almak istediğinize emin misiniz. Bu işlem geri alınamaz!')){
         $.ajax({
        type: "GET",
        url: '/sistemyonetim/avantajpasifdurumaal',
        data: {kampanyaid:$(this).attr('data-value')},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
             alert('Avantaj pasif duruma alındı');
             $('#avantajtablohtml').empty();
             document.getElementById('avantajtablohtml').innerHTML = result;
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
       }
  });
  $('#musteribilgileri').on('submit',function(e){
     e.preventDefault();
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/musteribilgiguncelle',
        data: $('#musteribilgileri').serialize(),
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
             alert('Müşteri bilgileri başarı ile güncellendi');
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
  });
  $('#uyelikturu').change(function(e){
       e.preventDefault();
       if($('#uyelikturu').val()!=0){
           if($('#uyelikturu').val()==2){
                $('#isletmecalismasaatleribolumu').attr('class','col-md-12');
                $('#isletmepersonelbolumu').attr('style','display:none');
                $('#sunulanhizmetlerbaybayanbolumu').attr('style','display:none');
                if($('#isletmearamaterimibolumu') != null){
                    $('#isletmearamaterimibolumu').attr('style','display:none');
                }
                $('#isletmegorselbolumu').attr('style','display:none');
            }
            else{
                $('#isletmecalismasaatleribolumu').attr('class','col-md-6');
                $('#isletmepersonelbolumu').attr('style','display:block');
                $('#sunulanhizmetlerbaybayanbolumu').attr('style','display:block');
                if($('#isletmearamaterimibolumu') != null){
                    $('#isletmearamaterimibolumu').attr('style','display:block');
                }
                $('#isletmegorselbolumu').attr('style','display:block');
            }
       }
       else{
       }
  });
  $('#avantajadedi').change(function(e){
      e.preventDefault();
       $.ajax({
        type: "GET",
        dataType: "text",
        url: '/avantajfiyathesapla',
        data: {avantajadedi:$('#avantajadedi').val(),avantajid:$('#avantajid').val()},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
             $('#avantajtoplamfiyat').empty();
              $('#avantajtoplamfiyat').append(result);
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
  });
  $('#avantajkodkullan').on('click','#avantajkuponara',function(e){
     e.preventDefault();
     if($('#avantajkuponkodu').val()=='')
        alert('Lütfen kupon kodunu giriniz!');
    else{
        $.ajax({
        type: "GET",
        dataType: "json",
        url: '/isletmeyonetim/avantajkupongetir',
        data: {kuponkodu:$('#avantajkuponkodu').val()},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
             if(result.mesaj != ''){
                  $('#avantajkodbulunamadi').attr('style','display:block');
                  $('#avantajkuponlartablosu').hide();
                  $('#avantajkodbulunamadimesaj').empty();
                  $('#avantajkodbulunamadimesaj').append(result.mesaj);
             }
             else{
                 $('#avantajkodkullan').empty();
                 $('#avantajkodkullan').append(result.kupononay);
                 $('#avantajkodbulunamadi').hide();
                 $('#avantajkodbulunamadimesaj').empty();
                 $('#avantajkuponlartablosu').show();
             }
            $('#avantajkupontablo').empty();
            $('#avantajkupontablo').append(result.kupon);
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
    }
  });
  $('#avantajkodkullan').on('click','button[name="avantajkuponkullan"]',function(e){
      e.preventDefault();
      $.ajax({
        type: "GET",
        dataType: "json",
        url: '/isletmeyonetim/avantajkuponkullan',
        data: {kuponid:$(this).attr('data-value')},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
             alert(result.mesaj);
             $('#avantajkodkullan').empty();
             $('#avantajkodkullan').append('<button type="button" id="avantajkuponara" class="btn btn-space btn-primary" style="width: 200px;height: 30px;font-size: 20px"><i class="icon mdi mdi-search"></i> Kodu Ara</button>');
            $('#avantajkupontablo').empty();
            $('#avantajkupontablo').append(result.kupon);
            $('#kampanyatablohtml').empty();
            $('#kampanyatablohtml').append(result.avantaj);
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
  });
  $('#avantajsatinalma').on('submit',function(e){
     e.preventDefault();
     if($("#avantajsatinalma")[0].checkValidity()){
         $.ajax({
        type: "GET",
        dataType: "json",
        url: '/avantajkartodemeadimi',
        data: $('#avantajsatinalma').serialize(),
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
             $('#satinalinanavantajdetayi').show();
             $('#avantajsatinalinan').empty();
             $('#avantajsatinalinanbirimfiyat').empty();
             $('#avantajsatinalinanadet').empty();
             $('#kuponkodu').val(result.avantajdetay.siparis_kod);
             $('#avantajsatinalinan').append(result.avantajdetay.avantaj);
             $('#avantajsatinalinanbirimfiyat').append(result.avantajdetay.birimfiyat);
             $('#avantajsatinalinanadet').append(result.avantajdetay.adet);
             for(var i=0;i<result.odemesecenekleri.length;i++){
                 $('#odemesecenekleriliste'+result.odemesecenekleri[i].id).empty();
                 $('#odemesecenekleriliste'+result.odemesecenekleri[i].id).append(result.odemesecenekleri[i].tablo);
             }
              $('html, body').animate({ scrollTop: $('#satinalinanavantajdetayi').offset().top }, 'slow');
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
     }
  });
  $('#smskredikartiodeme').click(function(e){
      $('#smsodemebilgileri').show();
      $('#smsodemebilgileri_havale').hide();
       $('html, body').animate({ scrollTop: $('#smsodemebilgileri').offset().top }, 'slow');
  });
   $('#smshavaleodeme').click(function(e){
            $('#smsodemebilgileri').hide();
      $('#smsodemebilgileri_havale').show();
       $('html, body').animate({ scrollTop: $('#smsodemebilgileri_havale').offset().top }, 'slow');
  });
   $('a[name="kartmarkalar"]').click(function(e){
        e.preventDefault();
        $("div[id^='pos']").attr('style','display:none');
        $('#pos'+$(this).attr('data-value')).attr('style','display:block');
        $('html, body').animate({ scrollTop: $('#pos'+$(this).attr('data-value')).offset().top }, 'slow');
   });
  $('.odemesecenekleritablosu').on('change','input[name="taksittekcekimsecenek"]:radio',function(e){
       e.preventDefault();
       $('#odemetoplamfiyat').val($('#toplam_tutar_'+$(this).attr('data-value')+'_'+$(this).val()).val());
       $('#pos_id').val($(this).attr('data-value'));
       $('#taksit_sayisi').val($(this).val());
       $('#odenecektoplamtutar').empty();
       $('#odenecektoplamtutar').append($('#toplam_tutar_'+$(this).attr('data-value')+'_'+$(this).val()).val()+ ' <span class="simge-tl">&#8378;</span>');
       $('html, body').animate({ scrollTop: $('#kredikartibilgibolumu').offset().top }, 'slow');
  });
  $('#avantajkredikartiodeme').on('submit',function(e){
      e.preventDefault();
      console.log($('#avantajkredikartiodeme').serialize().toString());
     var avantajid = $('#avantajid').val();
     var avantajadedi = $('#avantajadedi').val();
     var formData = $('#avantajkredikartiodeme').serialize()+'&avantajid='+avantajid+'&avantajadedi='+avantajadedi;
     $.ajax({
        type: "GET",
        dataType: "text",
        url: '/odeme',
        data: formData,
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
             $('#3dekran').empty();
             $('#3dekran').append(result);
              $('html, body').animate({ scrollTop: $('#3dekran').offset().top }, 'slow');
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
  });
  $('#randevutablo').on('click','.randevudetayigetir',function(e){
    e.preventDefault();
    var randevu_id = $(this).attr('data-value');
    $.ajax({
        type: "GET",
        dataType: "json",
        url: '/isletmeyonetim/randevugetir',
        data: {randevuid:randevu_id,sube:$('input[name="sube"]').val()},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            $('#preloader').hide();
            $('input[name="randevualan"]').val(result.musteri);
            $('input[name="randevutarihi"]').val(result.tarih);
            $('input[name="randevusaatibaslangic"]').val(result.saat);
            $('input[name="randevusaatibitis"]').val(result.saatbitis);
            $('input[name="telefonev"]').val(result.telefon);
            $('input[name="telefoncep"]').val(result.gsm);
            $('input[name="eposta"]').val(result.eposta);
            $('#randevuhizmetler_duzenle').empty();
            $('#randevuhizmetler_duzenle').append(result.hizmetler);
            $('#randevusube_duzenle').empty();
            $('#randevusube_duzenle').append(result.subeler);
           /*$('input[name="randevuhizmetler"]').attr('value',event.hizmet);
            $('input[name="randevupersoneller"]').attr('value',event.personel);*/
            $('input[name="randevuid"]').val(result.randevuid);
            if(result.durum==1 ){
                $('#randevuislemleri').attr('style','display:inline-block;width:100%;text-align:center');
                $('#randevuonayla').attr('style','display:none;float:none');
                $('#randevuiptalet').attr('style','display:block;float:none');
                $('#randevubilgiguncelle').attr('style','display:block;float:none');
            }
            if(result.durum==2){
                $('#randevuislemleri').attr('style','display:none');
            }
            if(result.durum==0){
                $('#randevuislemleri').attr('style','display:inline-block;width:100%;text-align:center');
                $('#randevuonayla').attr('style','display:block;float:none');
                $('#randevuiptalet').attr('style','display:block;float:none');
                $('#randevubilgiguncelle').attr('style','display:block;float:none');
            }
             $('#randevudetayigetir').trigger('click');
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
  });
  $('#sube_secim_randevu, #randevutarihi_randevuliste').change(function(e){
    e.preventDefault();
      $.ajax({
        type: "GET",
        dataType: "json",
        url: '/isletmeyonetim/randevular-filtre',
        data: {sube:$('#sube_secim_randevu').val(),tarih:$('#randevutarihi_randevuliste').val()},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
             $('#table1').DataTable().destroy();
             if($(window).width()<=1024){
                var table = $("#table1").DataTable({
                    pageLength:50,
                    "lengthChange": false,
                    "order": [[ 0, "desc" ]],
                    responsive: {
                      breakpoints: [
                          { name: 'desktop', width: Infinity },
                          { name: 'tablet',  width: 1024 },
                          { name: 'fablet',  width: 768 },
                          { name: 'phone',   width: 480 }
                      ]
                    },
                     columns:[
                            { data: 'musteri' },
                            { data: 'sube'},
                            { data: 'tarihsaat' },
                            { data: 'hizmetler' },
                            { data: 'durum' },
                            { data: 'islemler' },
                       ],
                       data: result,
                });
             }
             else{
                 $("#table1").DataTable({
                    pageLength:50,
                    "lengthChange": false,
                    responsive: false,
                    "order": [[ 0, "desc" ]],
                     columns:[
                            { data: 'musteri' },
                            { data: 'sube'},
                            { data: 'tarihsaat' },
                            { data: 'hizmetler' },
                            { data: 'durum' },
                            { data: 'islemler' },
                       ],
                       data: result,
                });
             }
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#sube_secim_rapor, #raportarihi').change(function(e){
    e.preventDefault();
    var subeid = $('#sube_secim_rapor').val();
    var tarihsecim = $('#raportarihi').val();
      $.ajax({
        type: "GET",
        dataType: "json",
        url: '/isletmeyonetim/raporlar-filtre',
        data: {sube:subeid,tarih:tarihsecim},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
             let dash = JSON.stringify(result.total);
             dash = dash.replace("[","");
             dash = dash.replace("]","");
             var genel = $.parseJSON(dash);
             $('#gelenmusteri').empty();
             $('#alinanodeme').empty();
             $('#kalanodeme').empty();
             $('#gelenmusteri').append(genel.gelen_musteri);
             $('#alinanodeme').append(genel.alinan_odeme);
             $('#kalanodeme').append(genel.kalan_odeme);
             $('#table1').DataTable().destroy();
             if($(window).width()<=1024){
                var table = $("#table1").DataTable({
                    pageLength:50,
                    "lengthChange": false,
                    responsive: {
                      breakpoints: [
                          { name: 'desktop', width: Infinity },
                          { name: 'tablet',  width: 1024 },
                          { name: 'fablet',  width: 768 },
                          { name: 'phone',   width: 480 }
                      ]
                    },
                     columns:[
                            { data: 'tarih' },
                            { data: 'musteri'},
                            { data: 'subepersonel' },
                            { data: 'hizmetler' },
                            { data: 'alinanodeme' },
                            { data: 'kalanodeme' },
                            { data: 'islemler' },
                       ],
                       data: result.islemler,
                });
             }
             else{
                 $("#table1").DataTable({
                    pageLength:50,
                    "lengthChange": false,
                    responsive: false,
                     columns:[
                            { data: 'tarih' },
                            { data: 'musteri'},
                            { data: 'subepersonel' },
                            { data: 'hizmetler' },
                            { data: 'alinanodeme' },
                            { data: 'kalanodeme' },
                            { data: 'islemler' },
                       ],
                       data: result.islemler,
                });
             }
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#randevutablo').on('click','.randevuraporolustur',function(e){
    e.preventDefault();
    var randevu_id = $(this).attr('data-value');
    $('#rapor_randevuid').val(randevu_id);
     $.ajax({
        type: "GET",
        dataType: "json",
        url: '/isletmeyonetim/randevugetir',
        data: {randevuid:randevu_id,sube:$('input[name="sube"]').val()},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            $('#preloader').hide();
            $('#randevuraporformunuac').trigger("click");
            $('#rapor_randevuid').val(result.randevuid);
            $('#randevuraporbaslik').empty();
            $('#randevuraporbaslik').append(result.musteri +' '+ result.tarih +" tarihli randevusu için rapor oluştur");
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('.islemsonuraporu').on('submit',function (e) {
    e.preventDefault();
    var veri = "";
    if($(this).attr('data-value')=="update")
        veri = $('.islemsonuraporu[data-value="update"]').serialize();
    else
        veri = $('.islemsonuraporu[data-value="insert"]').serialize();
    alert(veri);
    $.ajax({
        type: "GET",
        dataType: "text",
        url: '/isletmeyonetim/islemsonuraporugir',
        data: veri,
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            $('#preloader').hide();
            alert(result);
            alert('İşlem sonu raporu başarıyla oluşturuldu');
            window.location.href = '/isletmeyonetim/islemraporlari';
        },
        error: function (request, status, error) {
             $('#preloader').hide();
              alert('İşlem sonu raporu oluşturulurken bir hata oluştu');
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
/*$('#mevcutisletmeduzenleme').on('submit',function(e){
   e.preventDefault();
    var gorseller=  $('#isletmegorselleri').get(0).files.length;
    var kapakfoto = $('#isletmekapakfoto').get(0).files[0];
    var logo = $('#isletmelogo').get(0).files[0];
    var formData = new FormData();
    formData.append('isletmekapakfoto',kapakfoto);
    formData.append('isletmelogo',logo);
    for(var i=0;i<gorseller;i++){
         formData.append('isletmegorselleri[]',$('#isletmegorselleri').get(0).files[i]);
    }
    var other_data = $('#mevcutisletmeduzenleme').serializeArray();
    $.each(other_data,function(key,input){
        formData.append(input.name,input.value);
    });
     $.ajax({
        type: "POST",
        url: '/sistemyonetim/isletmebilgileriguncelle',
        dataType: "text",
        data : formData,
        contentType: false,
        cache: false,
        processData:false,
       success: function(result)  {
            alert(result);
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
*/
$('#islemtablo').on('click','.kalanodemealindi',function(e){
    e.preventDefault();
    var islem_id = $(this).attr('data-value');
    $.ajax({
        type: "GET",
        dataType: "text",
        url: '/isletmeyonetim/islemkalanodemealindi',
        data: {islemid:islem_id,sube:$('input[name="sube"]').val()},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            $('#preloader').hide();
            alert('İşlem borcu başarıyla kapatıldı');
            window.location.href = '/isletmeyonetim/islemraporlari?tarih='+result;
        },
        error: function (request, status, error) {
             $('#preloader').hide();
              alert('İşlem borcu kapatılırken bir hata oluştu');
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#islemtablo').on('click','.islemdetayigetir',function(e){
    e.preventDefault();
    var islem_id = $(this).attr('data-value');
    $.ajax({
        type: "GET",
        dataType: "json",
        url: '/isletmeyonetim/islemgetir',
        data: {islemid:islem_id,sube:$('input[name="sube"]').val()},
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            $('#preloader').hide();
            $('#islem_id').val(result.islemid);
            $('#islem_tarihi').val(result.tarih);
            $('#islem_saati').val(result.saat);
            $('#islem_musteri').empty();
            $('#islem_musteri').append(result.musteri);
            $('#islem_musteri_telefon').val(result.gsm);
            $('#islem_personel').empty();
            $('#islem_personel').append(result.personel);
            $('#islem_yapilan').empty();
            $('#islem_yapilan').append(result.yapilanislemler);
            $('#islem_alinan_odeme').val(result.alinanodeme);
            $('#islem_kalan_odeme').val(result.kalanodeme);
            $('#islem_personel_notu').text(result.not);
             $('#rapordetayigetir').trigger('click');
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML = request.responseText;
        }
    });
  });
$('#rapor_musteri').change(function(e){
     e.preventDefault();
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/musteribilgigetir',
        dataType: "json",
        data : {musteriid:$('#rapor_musteri').val(),sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
       success: function(result)  {
            $("#preloader").hide();
            if($.trim(result.telefon))
                $('#musteritelefon').attr('disabled',true);
            else
                $('#musteritelefon').attr('disabled',false);
            $('#musteritelefon').val(result.telefon);
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $("#preloader").hide();
        }
    });
});
$('#islemformu').on('submit',function(e){
    e.preventDefault();
    if($('#form_turu').val()==0)
        alert('Lütfen form türü seçiniz');
    else{
       console.log($('#islemformu').serialize());
       $.ajax({
        type: "GET",
        url: '/isletmeyonetim/islemsonuraporugir',
        dataType: "text",
        data : $('#islemformu').serialize(),
        beforeSend: function() {
            $("#preloader").show();
        },
       success: function(result)  {
           alert(result);
           window.location.href='/isletmeyonetim/musteridetay/1?tab=icon3';
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $("#preloader").hide();
        }
    });
    }
});
$('#musteri_saglik_bilgileri').on('submit',function(e){
    e.preventDefault();
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/saglikbilgilerigir',
        dataType: "text",
        data : $('#musteri_saglik_bilgileri').serialize(),
        beforeSend: function() {
            $("#preloader").show();
        },
       success: function(result)  {
            $("#preloader").hide();
           alert(result);
           //window.location.href='/isletmeyonetim/musteridetay/1?tab=icon3';
        },
        error: function (request, status, error) {
             document.getElementById('hata').innerHTML = request.responseText;
             $("#preloader").hide();
        }
    });
});
$('a[name="yeni-islem"]').click(function(e){
     e.preventDefault();
    $('#islemformu').trigger("reset");
    $('#form_turu').val($(this).attr('data-value'));
     $('#islem_formu_baslik').empty();
    $('#islem_formu_baslik').append( $('#form_turu option:selected' ).text()+' için yeni işlem kaydı gir');
    if($(this).attr('data-value')==33)
        $('.epilasyon_hizmeti_alanlari').attr('style','display:block');
    else
        $('.epilasyon_hizmeti_alanlari').attr('style','display:none');
    if($(this).attr('data-value')==36)
        $('.zayiflama_hizmeti_alanlari').attr('style','display:block');
    else
        $('.zayiflama_hizmeti_alanlari').attr('style','display:none');
    $('#islem_id').val('');
    $('#islem_yapilan option:selected').prop("selected", false);
    $('#yeniislemekle').trigger('click');
});
$(document).on('click','#bir_hizmet_daha_ekle',function (e) {
    e.preventDefault();
    $('.hizmetler_bolumu').find(".custom-select2, .opsiyonelSelect").each(function(index){
        $(this).select2('destroy');
        //$('#musteri_arama').select2('destroy');
    });
    var index = $('.hizmetler_bolumu div.row:last-child').attr('data-value');
     var eskiindex = index;
    index++;
    $('.hizmetler_bolumu').append('<div class="row" style=" background: #e2e2e2;margin: 5px 0 5px 0;" data-value="'+index +'">'+$('.hizmetler_bolumu .row').last().html()+'</div>');
    $('button[name="hizmet_formdan_sil"]').each(function(i){
        if (i>0){
            $(this).prop('disabled',false);
        }
        if(i == index)
            $(this).attr("data-value",i);
    });
    $("select.custom-select2").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        $(this).select2({width: '100%'});
    });
     $("select.opsiyonelSelect").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        $(this).select2({
            placeholder: "Seçiniz",
            allowClear:true,
        });
    });
     
    $('input[name*="birlestir"]').last().attr('id','customCheck'+index);
    if(index == 0){
        $('input[name*="birlestir"]').last().prop('disabled',true);
        $('input[name*="birlestir"]').last().attr('style','display:none');
        $('.usttekiylebirlestiryazi').last().attr('style','visibility:visible;font-size:12px;width:100%');
        $('label[name="birlestir_label').last().attr('style','display:none');
    }
    else{
        $('input[name*="birlestir"]').last().prop('disabled',false);
        $('input[name*="birlestir"]').last().attr('style','display:block');
        $('.usttekiylebirlestiryazi').last().attr('style','visibility:hidden;font-size:12px;width:100%');
        $('label[name="birlestir_label').last().attr('for','customCheck'+index);
         $('label[name="birlestir_label').last().attr('style','display:block');
   }
   $('select[name="randevuyardimcipersonelleriyeni"]').last().attr('id','randevuyardimcipersonelleriyeni_'+index+'[]');
    $('input[name*="birlestir"]').last().attr('name','birlestir'+index);
    select2YenidenYukle();
});
$(document).on('click','#bir_hizmet_daha_ekle_randevu_duzenleme',function (e) {
    e.preventDefault();
    $('.hizmetler_bolumu_randevu_duzenleme').find(".custom-select2, .opsiyonelSelect").each(function(index){
        $(this).select2('destroy');
        //$('#musteri_arama').select2('destroy');
    });
    var index = $('.hizmetler_bolumu_randevu_duzenleme div.row:last-child').attr('data-value');
    index++;
    console.log("index "+index);
    $('.hizmetler_bolumu_randevu_duzenleme').append('<div class="row" data-value="'+index +'">'+$('.hizmetler_bolumu_randevu_duzenleme .row').last().html()+'</div>');
    $('button[name="hizmet_formdan_sil_randevu_duzenleme"]').each(function(i){
        if (i>0){
            $(this).prop('disabled',false);
        }
        if(i == index)
            $(this).attr("data-value",i);
    });
    $("select.custom-select2").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        $(this).select2({width: '100%'});
    });
     $("select.opsiyonelSelect").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        $(this).select2({
            placeholder: "Seçiniz",
            allowClear:true,
        });
    });
     select2YenidenYukle();
    $('.hizmetler_bolumu_randevu_duzenleme input[name*="birlestir"]').last().attr('id','customCheck_'+index);
     if(index == 0){
        $('.hizmetler_bolumu_randevu_duzenleme input[name*="birlestir"]').last().prop('disabled',true);
        $('.hizmetler_bolumu_randevu_duzenleme input[name*="birlestir"]').last().attr('style','display:none');
        $('.hizmetler_bolumu_randevu_duzenleme .usttekiylebirlestiryazi').last().attr('style','visibility:visible;font-size:12px;width:100%');
        $('.hizmetler_bolumu_randevu_duzenleme label[name="birlestir_label').last().attr('style','display:none');
    }
    else{
        $('.hizmetler_bolumu_randevu_duzenleme input[name*="birlestir"]').last().prop('disabled',false);
        $('.hizmetler_bolumu_randevu_duzenleme input[name*="birlestir"]').last().attr('style','display:block');
        $('.hizmetler_bolumu_randevu_duzenleme .usttekiylebirlestiryazi').last().attr('style','visibility:hidden;font-size:12px;width:100%');
        $('.hizmetler_bolumu_randevu_duzenleme label[name="birlestir_label').last().attr('for','customCheck_'+index);
         $('.hizmetler_bolumu_randevu_duzenleme label[name="birlestir_label').last().attr('style','display:block');
   }
    $('.hizmetler_bolumu_randevu_duzenleme label[name="birlestir_label').last().attr('for','customCheck_'+index);
    $('.hizmetler_bolumu_randevu_duzenleme input[name*="birlestir"]').last().attr('name','birlestir'+index);
});
$(document).on('click','#bir_hizmet_daha_ekle_adisyon',function (e) {
    e.preventDefault();
    $('.hizmetler_bolumu_adisyon').find(".custom-select2, .opsiyonelSelect").each(function(index){
        $(this).select2('destroy');
        //$('#musteri_arama').select2('destroy');
    });
    var index = $('.hizmetler_bolumu_adisyon div.row:last-child').attr('data-value');
    index++;
    $('.hizmetler_bolumu_adisyon').append('<div class="row" data-value="'+index +'">'+$('.hizmetler_bolumu_adisyon .row').last().html()+'</div>');
    $('button[name="hizmet_formdan_sil_adisyon"]').each(function(i){
        if (i>0){
            $(this).prop('disabled',false);
        }
        if(i == index)
            $(this).attr("data-value",i);
    });
    $("select.custom-select2").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        //$(this).select2({width: '100%'});
    });
    $("select.opsiyonelSelect").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        /*$(this).select2({
            placeholder: "Seçiniz",
            allowClear:true,
        });*/
    }); 
    select2YenidenYukle();
    $('input[name="islemtarihiyeni[]"').each(function(){
        $(this).datepicker({
            language: "tr",
            autoClose: true,
            dateFormat: "yyyy-mm-dd",
        });
    });
});
$(document).on('click','#bir_hizmet_daha_ekle_senet',function (e) {
    e.preventDefault();
    $('.hizmetler_bolumu_senet').find(".custom-select2, .opsiyonelSelect").each(function(index){
        $(this).select2('destroy');
        //$('#musteri_arama').select2('destroy');
    });
    var index = $('.hizmetler_bolumu_senet div.row:last-child').attr('data-value');
    index++;
    $('.hizmetler_bolumu_senet').append('<div class="row" data-value="'+index +'">'+$('.hizmetler_bolumu_senet .row').last().html()+'</div>');
    $('button[name="hizmet_formdan_sil_senet"]').each(function(i){
        if (i>0){
            $(this).prop('disabled',false);
        }
        if(i == index)
            $(this).attr("data-value",i);
    });
    $("select.custom-select2").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        $(this).select2({width: '100%'});
    });
    $("select.opsiyonelSelect").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        $(this).select2({
            placeholder: "Seçiniz",
            allowClear:true,
        });
    });
    select2YenidenYukle();
    $('input[name="senetislemtarihiyeni[]"').each(function(){
        $(this).datepicker({
            language: "tr",
            autoClose: true,
            dateFormat: "yyyy-mm-dd",
        });
    });
});
$(document).on('click','#bir_urun_daha_ekle',function (e) {
    e.preventDefault();
    $('.urunler_bolumu').find(".custom-select2").each(function(index){
        $(this).select2('destroy');
        $('#musteri_arama').select2('destroy');
    });
    var index = $('.urunler_bolumu div.row:last-child').attr('data-value');
    index++;
    $('.urunler_bolumu').append('<div class="row" data-value="'+index +'">'+$('.urunler_bolumu .row').last().html()+'</div>');
    $('button[name="urun_formdan_sil"]').each(function(i){
        if (i>0){
            $(this).prop('disabled',false);
        }
        if(i == index)
            $(this).attr("data-value",i);
    });
    $("select.custom-select2").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        $(this).select2({width: '100%'});
    });
    select2YenidenYukle();
});
$(document).on('click','#bir_urun_daha_ekle_senet',function (e) {
    e.preventDefault();
    $('.urunler_bolumu_senet').find(".custom-select2").each(function(index){
        $(this).select2('destroy');
        $('#musteri_arama').select2('destroy');
    });
    var index = $('.urunler_bolumu_senet div.row:last-child').attr('data-value');
    index++;
    $('.urunler_bolumu_senet').append('<div class="row" data-value="'+index +'">'+$('.urunler_bolumu_senet .row').last().html()+'</div>');
    $('button[name="urun_senetten_sil"]').each(function(i){
        if (i>0){
            $(this).prop('disabled',false);
        }
        if(i == index)
            $(this).attr("data-value",i);
    });
    $("select.custom-select2").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        $(this).select2({width: '100%'});
    });
     select2YenidenYukle();
});
$(document).on('click','#bir_urun_daha_ekle_adisyon',function (e) {
    e.preventDefault();
    $('.urunler_bolumu_adisyon').find(".custom-select2").each(function(index){
        $(this).select2('destroy');
        //$('#musteri_arama').select2('destroy');
    });
    var index = $('.urunler_bolumu_adisyon div.row:last-child').attr('data-value');
    index++;
    $('.urunler_bolumu_adisyon').append('<div class="row" data-value="'+index +'">'+$('.urunler_bolumu_adisyon .row').last().html()+'</div>');
    $('button[name="urun_formdan_sil_adisyon"]').each(function(i){
        if (i>0){
            $(this).prop('disabled',false);
        }
        if(i == index)
            $(this).attr("data-value",i);
    });
    $("select.custom-select2").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        $(this).select2({width: '100%'});
    });
});
$(document).on('click','#bir_paket_daha_ekle',function(e) {
    e.preventDefault();
    $('.paketler_bolumu').find(".custom-select2").each(function(index){
        $(this).select2('destroy');
        //$('#musteri_arama').select2('destroy');
    });
    var index = $('.paketler_bolumu div.row:last-child').attr('data-value');
    index++;
    $('.paketler_bolumu').append('<div class="row" data-value="'+index +'" style="background-color:#e2e2e2;padding:4px;margin-bottom:10px">'+$('.paketler_bolumu .row').last().html()+'</div>');
    $('button[name="paket_formdan_sil_yeni_ekle"]').each(function(i){
        if (i>0){
            $(this).prop('disabled',false);
        }
        if(i == index)
            $(this).attr("data-value",i);
    });
    $("select.custom-select2").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        $(this).select2({width: '100%'});
    });
    $('input[name="paketbaslangictarihi[]"]').each(function(){
        $(this).datepicker({
            language: "tr",
            autoClose: true,
            dateFormat: "yyyy-mm-dd",
        });
    });
     select2YenidenYukle();
});
$(document).on('click','#bir_paket_daha_ekle_adisyon',function(e) {
    e.preventDefault();
    $('.paketler_bolumu_adisyon').find(".custom-select2").each(function(index){
        $(this).select2('destroy');
        //$('#musteri_arama').select2('destroy');
    });
    var index = $('.paketler_bolumu_adisyon div.row:last-child').attr('data-value');
    index++;
    $('.paketler_bolumu_adisyon').append('<div class="row" data-value="'+index +'">'+$('.paketler_bolumu_adisyon .row').last().html()+'</div>');
    $('button[name="paket_formdan_sil_adisyon"]').each(function(i){
        if (i>0){
            $(this).prop('disabled',false);
        }
        if(i == index)
            $(this).attr("data-value",i);
    });
    $("select.custom-select2").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        $(this).select2({width: '100%'});
    });
    $('input[name="paketbaslangictarihiadisyon[]"]').each(function(){
        $(this).datepicker({
            language: "tr",
            autoClose: true,
            dateFormat: "yyyy-mm-dd",
        });
    });
     select2YenidenYukle();
});
$(document).on('click','#bir_paket_daha_ekle_senet',function(e) {
    e.preventDefault();
    $('.paketler_bolumu_senet').find(".custom-select2").each(function(index){
        $(this).select2('destroy');
        //$('#musteri_arama').select2('destroy');
    });
    var index = $('.paketler_bolumu_senet div.row:last-child').attr('data-value');
    index++;
    $('.paketler_bolumu_senet').append('<div class="row" data-value="'+index +'">'+$('.paketler_bolumu_senet .row').last().html()+'</div>');
    $('button[name="paket_formdan_sil_senet"]').each(function(i){
        if (i>0){
            $(this).prop('disabled',false);
        }
        if(i == index)
            $(this).attr("data-value",i);
    });
    $("select.custom-select2").each(function(i){
        $(this).removeAttr('data-select2-id').removeAttr('id');
        $(this).find('option').removeAttr('data-select2-id');
        $(this).select2({width: '100%'});
    });
    $('input[name="paketbaslangictarihisenet[]"]').each(function(){
        $(this).datepicker({
            language: "tr",
            autoClose: true,
            dateFormat: "yyyy-mm-dd",
        });
    });
     select2YenidenYukle();
});
$(document).on('click','#paket_hizmet_daha_ekle',function (e) {
    e.preventDefault();
    $('.paket_hizmetler_bolumu').find(".custom-select2, .opsiyonelSelect").each(function(index){
        $(this).select2('destroy');
        $('#musteri_arama').select2('destroy');
    });
    var index = $('.paket_hizmetler_bolumu div.row:last-child').attr('data-value');
    index++;
    $('.paket_hizmetler_bolumu').append('<div class="row" data-value="'+index +'">'+$('.paket_hizmetler_bolumu .row').last().html()+'</div>');
    $('button[name="paket_hizmet_formdan_sil"]').each(function(i){
        if (i>0){
            $(this).prop('disabled',false);
        }
        if(i == index)
            $(this).attr("data-value",i);
    });
   
    select2ozellikyukle();
    select2YenidenYukle();
});
$(document).on('click','#paket_hizmet_daha_ekle_duzenleme',function (e) {
    e.preventDefault();
    $('.paket_hizmetler_bolumu_duzenleme').find(".custom-select2, .opsiyonelSelect").each(function(index){
        $(this).select2('destroy');
        //$('#musteri_arama').select2('destroy');
    });
    var index = $('.paket_hizmetler_bolumu_duzenleme div.row:last-child').attr('data-value');
    index++;
    $('.paket_hizmetler_bolumu_duzenleme').append('<div class="row" data-value="'+index +'">'+$('.paket_hizmetler_bolumu_duzenleme .row').last().html()+'</div>');
    $('button[name="paket_hizmet_formdan_sil_duzenleme"]').each(function(i){
        if (i>0){
            $(this).prop('disabled',false);
        }
        if(i == index)
            $(this).attr("data-value",i);
    });
     select2ozellikyukle();
      select2YenidenYukle();
});
$(document).on('click','button[name="paket_hizmet_formdan_sil"]',function(e){
    e.preventDefault();
    var index = $(this).attr('data-value');
    if (index != "0");
        $('.paket_hizmetler_bolumu div.row[data-value="'+index+'"]').remove();
});
$(document).on('click','button[name="paket_hizmet_formdan_sil_duzenleme"]',function(e){
    e.preventDefault();
    var index = $(this).attr('data-value');
    if (index != "0");
        $('.paket_hizmetler_bolumu_duzenleme div.row[data-value="'+index+'"]').remove();
});
$(document).on('click','button[name="hizmet_formdan_sil"]',function(e){
    e.preventDefault();
    var index = $(this).attr('data-value');
    if (index != "0");
        $('.hizmetler_bolumu div.row[data-value="'+index+'"]').remove();
});
$(document).on('click','button[name="hizmet_formdan_sil_adisyon"]',function(e){
    e.preventDefault();
    var index = $(this).attr('data-value');
    if (index != "0");
        $('.hizmetler_bolumu_adisyon div.row[data-value="'+index+'"]').remove();
});
$(document).on('click','button[name="hizmet_formdan_sil_senet"]',function(e){
    e.preventDefault();
    var index = $(this).attr('data-value');
    if (index != "0");
        $('.hizmetler_bolumu_senet div.row[data-value="'+index+'"]').remove();
});
$(document).on('click','button[name="hizmet_formdan_sil_randevu_duzenleme"]',function(e){
    e.preventDefault();
    var index = $(this).attr('data-value');
    if (index != "0");
        $('.hizmetler_bolumu_randevu_duzenleme div.row[data-value="'+index+'"]').remove();
});
$(document).on('click','button[name="urun_formdan_sil"]',function(e){
    e.preventDefault();
    var index = $(this).attr('data-value');
    if (index != "0");
        $('.urunler_bolumu div.row[data-value="'+index+'"]').remove();
        $('.urunler_bolumu_adisyon div.row[data-value="'+index+'"]').remove();
});
$(document).on('click','button[name="urun_senetten_sil"]',function(e){
    e.preventDefault();
    var index = $(this).attr('data-value');
    if (index != "0");
        $('.urunler_bolumu_senet div.row[data-value="'+index+'"]').remove();
});
/*$(document).on('click','button[name="urun_formdan_sil"]',function(e){
    e.preventDefault();
    var id = $(this).attr('data-value');
        swal({
        title: "Emin misiniz?",
        text: "Ürün satışı mevcut adisyondan silinecek olup ürün kaldırma işlemi geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Ürün satışını kaldır',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
        }).then(function (result) {
        if(result.value){
                $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/urunadisyondansil',
                    dataType: "json",
                    data : {adisyonurunid:id,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result2)  {
                        $("#preloader").hide();
                        if(result2.silinemez != '')
                        {
                            swal(
                                    {
                                        type: "warning",
                                        title: "Uyarı",
                                        html: result2.silinemez,
                                        showCloseButton: false,
                                        showCancelButton: false,
                                        showConfirmButton:false,
                                        timer:3000
                                    }
                            );
                        }
                        else
                        {
                            $('#adisyon_detay_urun_tablo').empty();
                            $('#adisyon_detay_urun_tablo').append(result2.html);
                            adisyontoplamhesapla();
                            $('#tahsil_edilen_tutar').empty();
                            $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').empty();
                            $('#tahsil_edilen_tutar').append(result2.tahsil_edilen);
                            $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').append(result2.kalan_tutar);
                            $('#adisyon_tahsilati_urunler').empty();
                            $('#adisyon_tahsilati_urunler').append(result2.tahsilat_urun_eklenecek);
                            adisyonyenidenhesapla();
                        }
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
                });
        }
        });
});*/
$(document).on('click','button[name="paket_formdan_sil_adisyon"]',function(e){
    e.preventDefault();
    var index = $(this).attr('data-value');
    if (index != "0");
        $('.paketler_bolumu_adisyon div.row[data-value="'+index+'"]').remove();
});
$(document).on('click','button[name="paket_formdan_sil_senet"]',function(e){
    e.preventDefault();
    var index = $(this).attr('data-value');
    if (index != "0");
        $('.paketler_bolumu_senet div.row[data-value="'+index+'"]').remove();
});
$(document).on('click','button[name="paket_formdan_sil"]',function(e){
    e.preventDefault();
    var id = $(this).attr('data-value');
     swal({
        title: "Emin misiniz?",
        text: "Paket satışı mevcut adisyondan silinecek olup paket satışı kaldırma işlemi geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Paket satışını kaldır',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
                $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/paketadisyondansil',
                    dataType: "json",
                    data : {adisyonpaketid:id,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result2)  {
                        $("#preloader").hide();
                        if(result2.silinemez!='')
                        {
                            swal(
                                    {
                                        type: "warning",
                                        title: "Uyarı",
                                        html: result2.silinemez,
                                        showCloseButton: false,
                                        showCancelButton: false,
                                        showConfirmButton:false,
                                        timer:3000
                                    }
                            );
                        }
                        else
                        {
                            $('#adisyon_detay_paket_tablo').empty();
                            $('#adisyon_detay_paket_tablo').append(result2.html);
                            adisyontoplamhesapla();
                            $('#tahsil_edilen_tutar').empty();
                            $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').empty();
                            $('#tahsil_edilen_tutar').append(result2.tahsil_edilen);
                            $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').append(result2.kalan_tutar);
                            $('#adisyon_tahsilati_paketler').empty();
                            $('#adisyon_tahsilati_paketler').append(result2.tahsilat_paket_eklenecek);
                            adisyonyenidenhesapla();
                        }
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
                });
        }
    });
});
$(document).on('click','button[name="paket_formdan_sil_yeni_ekle"]',function(e){
    e.preventDefault();
    var count = $('.paketler_bolumu .row').length;
    var index = $(this).attr('data-value');
    if (count != 1)
        $('.paketler_bolumu div.row[data-value="'+index+'"]').remove();
    else{
        $('input[name="paket_id[]"]').val("");
        $('input[name="paketadet[]"]').val("");
        $('select[name="pakettip[]"]').val(0).change();
        $('input[name="paketfiyat[]"]').val("");
        /*$('select[name="pakethizmet[]"]').eq(index).select2("trigger", "select", {
            data: { id: data['hizmet_id']}
        });*/
    }
});
$(document).on('submit','#paket_formu,#paket_formu_duzenleme',function(e){
    e.preventDefault();
    var form = $(this);
    var hizmet_secili = true;
    form.find('select[name="hizmetler[]"]').each(function(){
        if($(this).val() == "")
            hizmet_secili = false;
    });

    if(hizmet_secili == false)
    {
        swal({
            type: "warning",
            title: "Uyarı",
            html: 'Devam etmek için hizmet seçmeniz gerekmektedir.',
            showCloseButton: false,
            showCancelButton: false,
            showConfirmButton: false,
            timer: 3000,
        });
    }
    else {
        $.ajax({
            type: "POST",
            url: '/isletmeyonetim/paketekleguncelle',
            dataType: "json",
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                $("#preloader").show();
            },
            success: function (result) {
                $("#preloader").hide();
                $('#paket_formu')[0].reset();
                $('#paket_formu_duzenleme')[0].reset();
                $('#paket-duzenle-modal').modal('hide');
                $('#paket-modal').modal('hide');
                $('#hizmetler').val('0').trigger('change');
                $('#paket_hizmet_daha_ekle').val('0').trigger('change');
                swal({
                    type: 'success',
                    title: 'Başarılı',
                    text: result.status,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 3000,
                });
                $('#paket_liste').DataTable().destroy();
                $('#paket_liste').DataTable({
                    autoWidth: false,
                    responsive: true,
                    columns: [
                        {data: 'id'},
                        {data: 'paket_adi'},
                        {data: 'hizmetler'},
                        {data: 'seanslar'},
                        {data: 'fiyat'},
                        {data: 'islemler'},
                    ],
                    data: result.paketler.paket_liste,
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
                });
                $('select[name="paketadiadisyon[]"]').each(function () {
                    var data = {
                        id: result.eklenen_paket_id,
                        text: result.eklenen_paket
                    };
                    var option = new Option(data.text, data.id, false, false);
                    $(this).append(option);
                    $(this).val(data.id);
                });
                $('input[name="paketfiyatadisyon[]"]').each(function () {
                    $(this).val(result.toplam_tutar);
                });
            },
            error: function (request, status, error) {
                $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
            }
        });
    }
});

$(document).on('submit','#urun_formu',function(e){
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/urunekleguncelle',
        dataType: "json",
        data : $('#urun_formu').serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            $("#preloader").show();
        },
       success: function(result)  {
            $("#preloader").hide();
              $('#urun_formu').trigger('reset');
            $('#urun-modal').modal('hide');
            swal(
                {
                    type: 'success',
                    title: 'Başarılı',
                    text: result.status,
                     showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                }
            );
            $('#urun_liste').DataTable().destroy();
            $('#urun_liste').DataTable({
                    columns:[
                    {data:'id'},
                          { data: 'urun_adi',name: 'urun_adi' },
                                        { data: 'stok_adedi' ,name: 'stok_adedi'},
                                        { data: 'fiyat',name: 'fiyat' },
                                        { data: 'barkod',name: 'barkod' },
                              { data: 'dusuk_stok_siniri' },
                            {data : 'islemler'},
                    ],
                    data: result.urun_liste,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
              $('button[data-dismiss="modal"]').trigger('click');
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#urun_liste').on('click','a[name="urun_sil"]',function(e){
    e.preventDefault();
    var urunid = $(this).attr('data-value');
    swal({
        title: "Emin misiniz?",
        text: "Ürüne ait tüm satış kayıtları ile beraber silincek olup ürün kaldırma işlemi geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Ürünü kaldır',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
                $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/urunsil',
                    dataType: "json",
                    data : {urun_id:urunid,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        $('#urun_formu')[0].reset();
                        swal(
                            {
                                type: 'success',
                                title: 'Başarılı',
                                text: result.status,
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
                        );
                        $('#urun_liste').DataTable().destroy();
                        $('#urun_liste').DataTable({
                                columns:[
                                        {data:'id'},
                                        { data: 'urun_adi',name: 'urun_adi' },
                                        { data: 'stok_adedi' ,name: 'stok_adedi'},
                                        { data: 'fiyat',name: 'fiyat' },
                                        { data: 'barkod',name: 'barkod' },
                                         { data: 'dusuk_stok_siniri'},
                                        {data : 'islemler'},
                                ],
                                data: result.urun_liste,
                                "language" : {
                                    "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                    searchPlaceholder: "Ara",
                                    paginate: {
                                        next: '<i class="ion-chevron-right"></i>',
                                        previous: '<i class="ion-chevron-left"></i>'
                                    }
                                },
                        });
                         $('#modal_kapat').trigger('click');
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
                });
        }
    });
});
$('#paket_liste').on('click','a[name="paket_sil"]',function(e){
    e.preventDefault();
    var paketid = $(this).attr('data-value');
    swal({
        title: "Emin misiniz?",
        text: "Paket silme işlemi geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Paketi kaldır',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
                $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/paketsil',
                    dataType: "json",
                    data : {paket_id:paketid,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        swal(
                            {
                                type: 'success',
                                title: 'Başarılı',
                                text: result.status,
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
                        );
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
                       data: result.paket_liste,
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
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
                });
        }
    });
});
$('#paket_liste').on('click','a[name="paket_duzenle"]',function(e){
     $.ajax({
                type: "GET",
                url: '/isletmeyonetim/paketdetayigetir',
                dataType: "json",
                data : {paket_id:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
                beforeSend: function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                    $('#preloader').hide();
                    $('#paketad').val(result.paket_adi);
                    $("#paket_id_duzenleme").val(result.id);
                    $('.paket_hizmetler_bolumu_duzenleme').empty();
                    $('.paket_hizmetler_bolumu_duzenleme').append(result.paket_hizmetler);
                    $('.paket_hizmetler_bolumu_duzenleme').find(".custom-select2").each(function(index){
                        $(this).select2();
                    });
                    $('.paket_hizmetler_bolumu_duzenleme').find(".opsiyonelSelect").each(function(index){
                        $(this).select2({
                            placeholder: "Seçiniz",
                            allowClear:true
                        });
                    });
                    console.log(result);
                },
                error: function (request, status, error) {
                        $('#preloader').hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                }
            });
});
$(document).on('click','button[name="hizmet_formdan_sil_adisyon_mevcut"]',function(){
    var hizmet_id = $(this).attr('data-value');
        swal(
                {
                    type: 'warning',
                    title: 'Emin misiniz?',
                    text: 'Adisyondan hizmet silme işlemi geri alınamaz!',
                    confirmButtonColor: '#00bc8c',
                    confirmButtonText: 'Gönder',
                    cancelButtonText: "Vazgeç",
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonClass: 'btn btn-danger',
                }
        ).then(function (result) {
            if(result.value){
                    $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/adisyon-hizmet-sil',
                        dataType: "json",
                        data : {hizmet_id:hizmet_id,sube:$('input[name="sube"]').val()},
                        headers: {
                            'X-CSRF-TOKEN': $('input[name="_token"]').val()
                        },
                        beforeSend: function() {
                            $("#preloader").show();
                        },
                        success: function(result2)  {
                            $("#preloader").hide();
                            if(result2.silinemez != '')
                                 swal(
                                    {
                                        type: "warning",
                                        title: "Uyarı",
                                        html: result2.silinemez,
                                        showCloseButton: false,
                                        showCancelButton: false,
                                        showConfirmButton:false,
                                        timer:3000
                                    }
                                );
                            else
                            {
                                $('.hizmetler_bolumu_adisyon_2').empty();
                                $('.hizmetler_bolumu_adisyon_2').append(result2.html);
                                $("select.custom-select2").each(function(i){
                                    $(this).removeAttr('data-select2-id').removeAttr('id');
                                    $(this).find('option').removeAttr('data-select2-id');
                                    $(this).select2({width: '100%'});
                                });
                                adisyontoplamhesapla();
                                $('#tahsil_edilen_tutar').empty();
                                $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').empty();
                                $('#tahsil_edilen_tutar').append(result2.tahsil_edilen);
                                $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').append(result2.kalan_tutar);
                                $('#adisyon_tahsilati_hizmetler').empty();
                                $('#adisyon_tahsilati_hizmetler').append(result2.hizmet_tahsilata_eklenecek);
                                adisyonyenidenhesapla();
                            }
                        },
                        error: function (request, status, error) {
                            $("#preloader").hide();
                            document.getElementById('hata').innerHTML = request.responseText;
                        }
                    });
            }
        });
});
$('#adisyon_hizmet_formu').on('submit',function(e){
    e.preventDefault();
    console.log("müşteri id "+$('select[name="tahsilat_musteri_id"]').val());
    if($('input[name="adisyon_id"]').val()!='' ||$('#tahsilat_ekrani').length > 0){
        formData = new FormData();
        var other_data = $(this).serializeArray();
            $.each(other_data,function(key,input){
                formData.append(input.name,input.value);
        });
        if($('#tahsilat_ekrani').length){
            formData.append('tahsilatekrani',$('#tahsilat_ekrani').val());
            formData.append('musteri_id',$('select[name="tahsilat_musteri_id"]').val());
        }
        $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/adisyonhizmetekle',
                    dataType: "json",
                    data : formData,
                    processData:false,
                    contentType:false,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                        $("#preloader").hide();
                        $('#adisyon_hizmet_formu')[0].reset();
                        $('#adisyon_hizmet_modal_kapat').trigger('click');

                        $('#adisyon_hizmet_formu .hizmetler_bolumu_adisyon div').each(function(e){
                            if($(this).attr('data-value')>0)
                                $(this).remove();


                        });
                        $('select[name="adisyonhizmetpersonelleriyeni[]"]').val(null).trigger('change'); 
                         $('select[name="adisyonhizmetleriyeni[]"]').val(null).trigger('change'); 
                        if($('#tahsilat_ekrani').length > 0)
                        {
                            $('#tum_tahsilatlar').empty();
                            $('#tum_tahsilatlar').append(result.kalemler);
                            tahsilatyenidenhesapla();
                        }
                        else
                        {
                            $('.hizmetler_bolumu_adisyon_2').empty();
                            $('.hizmetler_bolumu_adisyon_2').append(result.html);
                            $("select.custom-select2").each(function(i){
                                $(this).removeAttr('data-select2-id').removeAttr('id');
                                $(this).find('option').removeAttr('data-select2-id');
                                $(this).select2({width: '100%'});
                            });
                            $('#tahsil_edilen_tutar').empty();
                            $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').empty();
                            $('#tahsil_edilen_tutar').append(result.tahsil_edilen);
                            $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').append(result.kalan_tutar);
                            $('#adisyon_tahsilati_hizmetler').empty();
                            console.log(result.hizmet_tahsilata_eklenecek);
                            $('#adisyon_tahsilati_hizmetler').append(result.hizmet_tahsilata_eklenecek);
                            adisyonyenidenhesapla();
                        }
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
                });
    }
    else
    {
        $('#adisyon_secilen_hizmetler').empty();
        var html = '';
        var index = 0;
        $('input[name="islemtarihiyeni[]"]').each(function(e){
            var personel = $('select[name="adisyonhizmetpersonelleriyeni[]"]').eq(index).select2('data')[0].text;
            var hizmet = $('select[name="adisyonhizmetleriyeni[]"]').eq(index).select2('data')[0].text;
            html += '<div class="row" style="background:#e2e2e2;margin:5px 0 5px 0; padding:5px;font-size:14px"  data-value="'+index+'">'+
            '<div class="col-md-2"><input type="hidden" name="adisyon_hizmet_tarih[]" value="'+$('input[name="islemtarihiyeni[]"]').eq(index).val()+'">'+$('input[name="islemtarihiyeni[]"]').eq(index).val()+'</div>'+
            '<div class="col-md-1"><input type="hidden" name="adisyon_hizmet_saat[]" value="'+$('input[name="islemsaatiyeni[]"]').eq(index).val()+'">'+$('input[name="islemsaatiyeni[]"]').eq(index).val()+'</div>'+
            '<div class="col-md-2"><input type="hidden" name="adisyon_hizmet_id[]" value="'+$('select[name="adisyonhizmetleriyeni[]"]').eq(index).val()+'">'+hizmet+' </div>'+
            '<div class="col-md-2"><input type="hidden" name="adisyon_hizmet_personel[]" value="'+$('select[name="adisyonhizmetpersonelleriyeni[]"]').eq(index).val()+'">'+personel+' </div>'+
            '<div class="col-md-2"><input type="hidden" name="adisyon_hizmet_sure[]" value="'+$('input[name="adisyonhizmetsuresi[]"]').eq(index).val()+'">'+$('input[name="adisyonhizmetsuresi[]"]').eq(index).val()+' dk</div>'+
            '<div class="col-md-2" style="text-align:right"><input type="hidden" name="adisyon_hizmet_fiyat[]" value="'+$('input[name="adisyonhizmetfiyati[]"]').eq(index).val()+'">'+$('input[name="adisyonhizmetfiyati[]"]').eq(index).val()+' ₺</div>'+
            '<div class="col-md-1"><button type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545" class="btn btn-danger" name="yeni_adisyon_formundan_hizmet_sil" data-value="'+index+'"><i class="fa fa-times"></i></button> </div></div>';
            index+=1;
        });
        $('#adisyon_hizmet_modal_kapat').trigger('click');
        $('#adisyon_secilen_hizmetler').append(html);
        adisyon_toplam_hesapla();
    }
});
$(document).on('submit','#senet_hizmet_formu',function (e) {
    e.preventDefault();
    $('#hizmetler_bolumu_senet').empty();
        var html = '';
        var index = 0;
        $('input[name="senetislemtarihiyeni[]"]').each(function(e){
            var personel = $('select[name="senethizmetpersonelleriyeni[]"]').eq(index).select2('data')[0].text;
            var hizmet = $('select[name="senethizmetleriyeni[]"]').eq(index).select2('data')[0].text;
            html += '<div class="row" style="background:#e2e2e2;margin:5px 0 5px 0; padding:5px;font-size:14px"  data-value="'+index+'">'+
            '<div class="col-md-3 col-sm-6 col-xs-6 col-6"><input type="hidden" name="senet_hizmet_tarih[]" value="'+$('input[name="senetislemtarihiyeni[]"]').eq(index).val()+'">'+
            '<input type="hidden" name="senet_hizmet_saat[]" value="'+$('input[name="senetislemsaatiyeni[]"]').eq(index).val()+'">'+
            '<input type="hidden" name="senet_hizmet_id[]" value="'+$('select[name="senethizmetleriyeni[]"]').eq(index).val()+'">'+hizmet+' </div>'+
            '<div class="col-md-3 col-sm-6 col-xs-6 col-6"><input type="hidden" name="senet_hizmet_personel[]" value="'+$('select[name="senethizmetpersonelleriyeni[]"]').eq(index).val()+'">'+personel+' </div>'+
            '<div class="col-md-3 col-sm-4 col-xs-4 col-4"><input type="hidden" name="senet_hizmet_sure[]" value="'+$('input[name="senethizmetsuresi[]"]').eq(index).val()+'">'+$('input[name="senethizmetsuresi[]"]').eq(index).val()+' dk</div>'+
            '<div class="col-md-2 col-sm-4 col-xs-4 col-4" style="text-align:right"><input type="hidden" name="senet_hizmet_fiyat[]" value="'+$('input[name="senethizmetfiyati[]"]').eq(index).val()+'">'+$('input[name="senethizmetfiyati[]"]').eq(index).val()+' ₺</div>'+
            '<div class="col-md-1 col-sm-4 col-xs-4 col-4"><button type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545" class="btn btn-danger" name="senet_formundan_hizmet_sil" data-value="'+index+'"><i class="fa fa-times"></i></button> </div></div>';
            index+=1;
    });
    $('#senet_hizmet_modal_kapat').trigger('click');
    $('#hizmetler_bolumu_senet').append(html);
    senet_tutar_hesapla();
})
$(document).on('submit','#adisyon_urun_satisi_yeni_adisyon',function(e){
    e.preventDefault();
    $('#adisyon_secilen_urunler').empty();
    var html = '';
    var index = 0;
    var satici = $('select[name="urun_satici_adisyon"]').select2('data')[0].text;
    $('select[name="urunyeniadisyon[]"]').each(function(e){
            var urun = $('select[name="urunyeniadisyon[]"]').eq(index).select2('data')[0].text;
            html += '<div class="row" style="background:#e2e2e2;margin:5px 0 5px 0; padding:5px;font-size:14px"  data-value="'+index+'">'+
            '<div class="col-md-3"><input type="hidden" name="urun_id_adisyon[]" value="'+$('select[name="urunyeniadisyon[]"]').eq(index).val()+'">'+urun+'</div>'+
            '<div class="col-md-3"><input type="hidden" name="urun_satan_adisyon[]" value="'+$('select[name="urun_satici_adisyon"]').val()+'">'+satici+' </div>'+
            '<div class="col-md-3"><input type="hidden" name="urun_adet_adisyon[]" value="'+$('input[name="urun_adedi_adisyon[]"]').eq(index).val()+'">'+$('input[name="urun_adedi_adisyon[]"]').eq(index).val()+' adet</div>'+
            '<div class="col-md-2" style="text-align:right"><input type="hidden" name="urun_fiyat_adisyon[]" value="'+$('input[name="urun_fiyatiadisyon[]"]').eq(index).val()+'">'+$('input[name="urun_fiyatiadisyon[]"]').eq(index).val()+' ₺</div>'+
            '<div class="col-md-1"><button type="button" style="padding:5px" class="btn btn-danger" name="yeni_adisyon_formundan_urun_sil" data-value="'+index+'"><i class="fa fa-times"></i></button> </div></div>';
            index+=1;
    });
    $('#adisyon_urun_modal_kapat').trigger('click');
    $('#adisyon_secilen_urunler').append(html);
    adisyon_toplam_hesapla();
});
$(document).on('submit','#urun_satisi_senet',function(e){
    e.preventDefault();
    $('#urunler_bolumu_senet').empty();
    var html = '';
    var index = 0;
    var satici = $('select[name="urun_satici_senet"]').select2('data')[0].text;
    $('select[name="urunyenisenet[]"]').each(function(e){
            var urun = $('select[name="urunyenisenet[]"]').eq(index).select2('data')[0].text;
            html += '<div class="row" style="background:#e2e2e2;margin:5px 0 5px 0; padding:5px;font-size:14px"  data-value="'+index+'">'+
            '<div class="col-md-3 col-sm-6 col-xs-6 col-6"><input type="hidden" name="urun_id_senet[]" value="'+$('select[name="urunyenisenet[]"]').eq(index).val()+'">'+urun+'</div>'+
            '<div class="col-md-3 col-sm-6 col-xs-6 col-6"><input type="hidden" name="urun_satan_senet[]" value="'+$('select[name="urun_satici_senet"]').val()+'">'+satici+' </div>'+
            '<div class="col-md-3 col-sm-4 col-xs-4 col-4"><input type="hidden" name="urun_adet_senet[]" value="'+$('input[name="urun_adedi_senet[]"]').eq(index).val()+'">'+$('input[name="urun_adedi_senet[]"]').eq(index).val()+' adet</div>'+
            '<div class="col-md-2 col-sm-4 col-xs-4 col-4" style="text-align:right"><input type="hidden" name="urun_fiyat_senet[]" value="'+$('input[name="urun_fiyatisenet[]"]').eq(index).val()+'">'+$('input[name="urun_fiyatisenet[]"]').eq(index).val()+' ₺</div>'+
            '<div class="col-md-1 col-sm-4 col-xs-4 col-4"><button type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545" class="btn btn-danger" name="senet_formundan_urun_sil" data-value="'+index+'"><i class="fa fa-times"></i></button> </div></div>';
            index+=1;
    });
    $('#senet_urun_modal_kapat').trigger('click');
    $('#urunler_bolumu_senet').append(html);
    senet_tutar_hesapla();
});
$(document).on('submit','#paket_satisi_adisyon',function(e){
    e.preventDefault();
    $('#adisyon_secilen_paketler').empty();
    var html = '';
    var index = 0;
    var satici = $('select[name="paket_satici_adisyon"]').select2('data')[0].text;
    $('select[name="paketadiadisyon[]"]').each(function(e){
            var paket = $('select[name="paketadiadisyon[]"]').eq(index).select2('data')[0].text;
            html += '<div class="row" style="background:#e2e2e2;margin:5px 0 5px 0; padding:5px;font-size:14px"  data-value="'+index+'">'+
            '<div class="col-md-3"><input type="hidden" name="paket_id_adisyon[]" value="'+$('select[name="paketadiadisyon[]"]').eq(index).val()+'">'+paket+'</div>'+
            '<div class="col-md-2"><input type="hidden" name="paket_satan_adisyon[]" value="'+$('select[name="paket_satici_adisyon"]').val()+'">'+satici+' </div>'+
            '<div class="col-md-2"><input type="hidden" name="paket_baslangic_tarihi_adisyon[]" value="'+$('input[name="paketbaslangictarihiadisyon[]"]').eq(index).val()+'">'+$('input[name="paketbaslangictarihiadisyon[]"]').eq(index).val()+' tarihinde başlar.</div>'+
            '<div class="col-md-2"><input type="hidden" name="paket_seans_aralik_gun_adisyon[]" value="'+$('input[name="seansaralikgunadisyon[]"]').eq(index).val()+'">'+$('input[name="seansaralikgunadisyon[]"]').eq(index).val()+' günde bir</div>'+
            '<div class="col-md-2" style="text-align:right"><input type="hidden" name="paket_fiyat_adisyon[]" value="'+$('input[name="paketfiyatadisyon[]"]').eq(index).val()+'">'+$('input[name="paketfiyatadisyon[]"]').eq(index).val()+' ₺</div>'+
            '<div class="col-md-1"><button type="button" style="padding:5px" class="btn btn-danger" name="yeni_adisyon_formundan_paket_sil" data-value="'+index+'"><i class="fa fa-times"></i></button> </div></div>';
            index+=1;
    });
    $('#adisyon_paket_modal_kapat').trigger('click');
    $('#adisyon_secilen_paketler').append(html);
    adisyon_toplam_hesapla();
});
$(document).on('submit','#paket_satisi_senet',function(e){
    e.preventDefault();
    $('#paketler_bolumu_senet').empty();
    var html = '';
    var index = 0;
    var satici = $('select[name="paket_satici_senet"]').select2('data')[0].text;
    $('select[name="paketadisenet[]"]').each(function(e){
            var paket = $('select[name="paketadisenet[]"]').eq(index).select2('data')[0].text;
            html += '<div class="row" style="background:#e2e2e2;margin:5px 0 5px 0; padding:5px;font-size:14px"  data-value="'+index+'">'+
            '<div class="col-md-3 col-sm-6 col-xs-6 col-6"><input type="hidden" name="paket_id_senet[]" value="'+$('select[name="paketadisenet[]"]').eq(index).val()+'">'+paket+'</div>'+
            '<div class="col-md-3 col-sm-6 col-xs-6 col-6"><input type="hidden" name="paket_satan_senet[]" value="'+$('select[name="paket_satici_senet"]').val()+'">'+satici+' '+
            '<input type="hidden" name="paket_baslangic_tarihi_senet[]" value="'+$('input[name="paketbaslangictarihisenet[]"]').eq(index).val()+'">'+
            '<input type="hidden" name="paket_seans_aralik_gun_senet[]" value="'+$('input[name="seansaralikgunsenet[]"]').eq(index).val()+'"></div>'+
            '<div class="col-md-3 col-sm-4 col-xs-4 col-4">1 adet</div>'+
            '<div class="col-md-2 col-sm-4 col-xs-4 col-4" style="text-align:right"><input type="hidden" name="paket_fiyat_senet[]" value="'+$('input[name="paketfiyatsenet[]"]').eq(index).val()+'">'+$('input[name="paketfiyatsenet[]"]').eq(index).val()+' ₺</div>'+
            '<div class="col-md-1 col-sm-4 col-xs-4 col-4"><button type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545" class="btn btn-danger" name="senet_formundan_paket_sil" data-value="'+index+'"><i class="fa fa-times"></i></button> </div></div>';
            index+=1;
    });
    $('#senet_paket_modal_kapat').trigger('click');
    $('#paketler_bolumu_senet').append(html);
    senet_tutar_hesapla();
});
function adisyon_toplam_hesapla(){
    var toplam = 0;
    $('input[name="adisyon_hizmet_fiyat[]"]').each(function(){
        toplam += parseFloat($(this).val()) || 0;
    });
    $('input[name="urun_fiyat_adisyon[]"]').each(function(){
        toplam += parseFloat($(this).val()) || 0;
    });
    $('input[name="paket_fiyat_adisyon[]"]').each(function(){
        toplam += parseFloat($(this).val()) || 0;
    });
    $('#adisyon_toplam_tutar').empty();
    $('#adisyon_toplam_tutar').append(toplam);
}
$('#adisyon_secilen_hizmetler').on('click','button[name="yeni_adisyon_formundan_hizmet_sil"]',function(e){
    e.preventDefault();
    $('#adisyon_secilen_hizmetler div.row[data-value="'+$(this).attr('data-value')+'"]').remove();
    adisyon_toplam_hesapla();
});
$('#hizmetler_bolumu_senet').on('click','button[name="senet_formundan_hizmet_sil"]',function(e){
    $('#hizmetler_bolumu_senet div.row[data-value="'+$(this).attr('data-value')+'"]').remove();
    senet_tutar_hesapla();
});
$('#adisyon_secilen_urunler').on('click','button[name="yeni_adisyon_formundan_urun_sil"]',function(e){
    e.preventDefault();
    $('#adisyon_secilen_urunler div.row[data-value="'+$(this).attr('data-value')+'"]').remove();
    adisyon_toplam_hesapla();
});
$('#urunler_bolumu_senet').on('click','button[name="senet_formundan_urun_sil"]',function(e){
    e.preventDefault();
    $('#urunler_bolumu_senet div.row[data-value="'+$(this).attr('data-value')+'"]').remove();
      senet_tutar_hesapla();
});
$('#adisyon_secilen_paketler').on('click','button[name="yeni_adisyon_formundan_paket_sil"]',function(e){
    e.preventDefault();
    $('#adisyon_secilen_paketler div.row[data-value="'+$(this).attr('data-value')+'"]').remove();
    adisyon_toplam_hesapla();
});
$('#paketler_bolumu_senet').on('click','button[name="senet_formundan_paket_sil"]',function(e){
    e.preventDefault();
    $('#paketler_bolumu_senet div.row[data-value="'+$(this).attr('data-value')+'"]').remove();
      senet_tutar_hesapla();
});
$('#adisyon_secilen_paketler').on('click','button[name="yeni_adisyon_formundan_paket_sil"]',function(e){
    e.preventDefault();
    $('#adisyon_secilen_paketler div.row[data-value="'+$(this).attr('data-value')+'"]').remove();
    adisyon_toplam_hesapla();
});
$(document).on('submit','#adisyon_formu',function(e){
    e.preventDefault();
    index = 0;
    $('input[name="adisyon_hizmet_fiyat[]"]').each(function(){
        index += 1;
    });
    $('input[name="urun_fiyat_adisyon[]"]').each(function(){
        index += 1;
    });
    $('input[name="paket_fiyat_adisyon[]"]').each(function(){
       index += 1;
    });
    if(index == 0)
    {
        swal(
                {
                    type: 'warning',
                    title: 'Uyarı',
                    text: 'Adisyon oluşturmadan önce adisyona en az bir hizmet, ürün veya paket eklemeniz gerekir',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                }
        );
    }
    else
    {
        $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/yeni-adisyon',
                    dataType: "json",
                    data : $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                         $("#preloader").hide();
                         console.log(result);
                         $('button[data-dismiss="modal"]').trigger('click');
                          swal(
                            {
                                type: 'success',
                                title: 'Başarılı',
                                html: '<p>Adisyon (satış) sisteme başarıyla kaydedildi </p> <a class="btn btn-primary btn-lg btn-block" href="/isletmeyonetim/tahsilat/'+result.user_id+'/'+result.adisyon_id+'?sube='+$('input[name="sube"]').val()+'>Tahsil Et</a>',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                            }
                        );
                        $('#adisyon_secilen_hizmetler').empty();
                        $('#adisyon_secilen_urunler').empty();
                        $('#adisyon_secilen_paketler').empty();
                        if($('#adisyon_liste').length)
                        {
                            var namesType = $.fn.dataTable.absoluteOrder( [
                                 { value: null, position: 'bottom' }
                                 ] );
                            $.fn.dataTable.moment('DD.MM.YYYY');
                            $('#adisyon_liste').DataTable().destroy();
                            $('#adisyon_liste_hizmet').DataTable().destroy();
                            $('#adisyon_liste_urun').DataTable().destroy();
                            $('#adisyon_liste_paket').DataTable().destroy();
                            $('#adisyon_liste').DataTable({
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
                           data: result.tum_adisyonlar,
                           "language" : {
                               "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                               searchPlaceholder: "Ara",
                               paginate: {
                                   next: '<i class="ion-chevron-right"></i>',
                                   previous: '<i class="ion-chevron-left"></i>'
                               }
                           },
                   });
                     $('#adisyon_liste_hizmet').DataTable({
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
                           data: result.hizmet_adisyonlar,
                           "language" : {
                               "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                               searchPlaceholder: "Ara",
                               paginate: {
                                   next: '<i class="ion-chevron-right"></i>',
                                   previous: '<i class="ion-chevron-left"></i>'
                               }
                           },
                   });
                    $('#adisyon_liste_urun').DataTable({
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
                           data: result.urun_adisyonlar,
                           "language" : {
                               "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                               searchPlaceholder: "Ara",
                               paginate: {
                                   next: '<i class="ion-chevron-right"></i>',
                                   previous: '<i class="ion-chevron-left"></i>'
                               }
                           },
                   });
                      $('#adisyon_liste_paket').DataTable({
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
                           data: result.paket_adisyonlar,
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
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
        });
    }
});
$(document).on('change','select[name="randevuhizmetleriyeni[]"],select[name="adisyonhizmetleriyeni[]"], select[name="randevuhizmetleri[]"]',function(e){
    e.preventDefault();
    var hizmetid = $(this).val();
    var fiyat_text = $(this).closest('div .row').find('input[name="hizmet_fiyat[]"]');
    var fiyat_text_adisyon = $(this).closest('div .row').find('input[name="adisyonhizmetfiyati[]"]');
    var sure_text = $(this).closest('div .row').find('input[name="hizmet_suresi[]"]');
     var sure_text_adisyon = $(this).closest('div .row').find('input[name="adisyonhizmetsuresi[]"]');
    if(hizmetid=="0" || hizmetid=="")
    {
        fiyat_text.val("");
            sure_text.val("");
            fiyat_text_adisyon.val("");
            sure_text_adisyon.val("");
    }
    else{
         $.ajax({
        type: "GET",
        url: '/isletmeyonetim/hizmetsurefiyatgetir',
        dataType: "json",
        data : {hizmet_id:hizmetid,sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            fiyat_text.val(result.fiyat);
            sure_text.val(result.sure);
            fiyat_text_adisyon.val(result.fiyat);
            sure_text_adisyon.val(result.sure);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
    }
});
/*$(document).on('change','select[name="randevucihazlariyeni[]"],select[name="randevupersonelleriyeni[]"]',function(e){
    var personelid = $(this).closest('div .row').find('select[name="randevupersonelleriyeni[]"]');
    var cihazid = $(this).closest('div .row').find('select[name="randevucihazlariyeni[]"]');
    var hizmet_select = $(this).closest('div .row').find('select[name="randevuhizmetleriyeni[]"]');
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/personelcihazhizmetlerinigetir',
        dataType: "text",
        data : {cihaz_id:cihazid.val(), personel_id: personelid.val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            if(result!= '')
            {
                hizmet_select.empty();
                hizmet_select.append(result);
                select2ozellikyukle(hizmet_select);
            }
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});*/
$(document).on('change','select[name="urunyeni[]"]',function(e){
    e.preventDefault();
    var fiyat_text = $(this).closest('div .row').find('input[name="urun_fiyati[]"]');
    urunid = $(this).val();
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/urunfiyatgetir',
        dataType: "text",
        data : {urun_id:urunid,sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            fiyat_text.val(result);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('change','select[name="urunyeniadisyon[]"]',function(e){
    e.preventDefault();
    var fiyat_text = $(this).closest('div .row').find('input[name="urun_fiyatiadisyon[]"]');
    urunid = $(this).val();
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/urunfiyatgetir',
        dataType: "text",
        data : {urun_id:urunid,sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            fiyat_text.val(result);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('change','select[name="urunyenisenet[]"]',function(e){
    e.preventDefault();
    var fiyat_text = $(this).closest('div .row').find('input[name="urun_fiyatisenet[]"]');
    urunid = $(this).val();
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/urunfiyatgetir',
        dataType: "text",
        data : {urun_id:urunid,sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            fiyat_text.val(result);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('change','input[name="urun_adedi[]"]',function(e){
    e.preventDefault();
    var urun = $(this).closest('div .row').find('select[name="urunyeni[]"]');
    var fiyat_text = $(this).closest('div .row').find('input[name="urun_fiyati[]"]');
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/urunfiyathesapla',
        dataType: "text",
        data : {urun_id:urun.val(),adet:$(this).val(),sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            fiyat_text.val(result);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('change','input[name="urun_adedi_adisyon[]"]',function(e){
    e.preventDefault();
    var fiyat_text = $(this).closest('div .row').find('input[name="urun_fiyatiadisyon[]"]');
     var urun = $(this).closest('div .row').find('select[name="urunyeniadisyon[]"]');
      $.ajax({
        type: "GET",
        url: '/isletmeyonetim/urunfiyathesapla',
        dataType: "text",
        data : {urun_id:urun.val(),adet:$(this).val(),sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            fiyat_text.val(result);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('change','input[name="urun_adedi_senet[]"]',function(e){
    e.preventDefault();
    var fiyat_text = $(this).closest('div .row').find('input[name="urun_fiyatisenet[]"]');
     var urun = $(this).closest('div .row').find('select[name="urunyenisenet[]"]');
      $.ajax({
        type: "GET",
        url: '/isletmeyonetim/urunfiyathesapla',
        dataType: "text",
        data : {urun_id:urun.val(),adet:$(this).val(),sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            fiyat_text.val(result);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('submit','#adisyon_urun_satisi',function(e) {
    e.preventDefault();
    var formData = new FormData();

    var other_data = $(this).serializeArray();
    $.each(other_data,function(key,input){
                formData.append(input.name,input.value);
    });


    formData.append('musteri_id',$('select[name="musteri_adi_yeni_urun"]').val());
    console.log("Müşteri id "+$('select[name="musteri_adi_yeni_urun"]').val());
    formData.append('tahsilatekrani',$('#tahsilat_ekrani').val());
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/urunsatisekle',
                    dataType: "json",
                    data : formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        console.log(result);

                        const previousDisabledValue = $('select[name="musteri_adi_yeni_urun"]').val();

                        $('#adisyon_urun_satisi')[0].reset();


                        $('select[name="musteri_adi_yeni_urun"]').val(previousDisabledValue).trigger('change.select2');

                        
                        $('#adisyon_urun_satisi .urunler_bolumu div').each(function(e){
                            if($(this).attr('data-value')>0)
                                  $(this).remove();

                        });
                        $('#modal_kapat').trigger('click');
                        if($('#tahsilat_ekrani').length)
                        {
                            $('#tum_tahsilatlar').empty();
                            $('#tum_tahsilatlar').append(result.kalemler);
                            $('#tahsilat_listesi').empty();
                            $('#tahsilat_listesi').append(result.tahsilatlar);
                            tahsilatyenidenhesapla();
                            
                        }
                        else
                        {
                            $('#adisyon_detay_urun_tablo').empty();
                            $('#adisyon_detay_urun_tablo').append(result.html);
                            $('#tahsil_edilen_tutar').empty();
                            $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').empty();
                            $('#tahsil_edilen_tutar').append(result.tahsil_edilen);
                            $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').append(result.kalan_tutar);
                            $('#adisyon_tahsilati_urunler').empty();
                            $('#adisyon_tahsilati_urunler').append(result.tahsilat_urun_eklenecek);
                            adisyonyenidenhesapla();
                        }
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$('.hizmetler_bolumu_adisyon_2').on('change','input[name="hizmet_fiyati_adisyon"]',function (e) {
    var hizmetid = $(this).attr('data-value');
    var fiyat = $(this).val();
    $.ajax({
                                    type: "POST",
                                    url: '/isletmeyonetim/adisyonhizmetfiyatguncelle',
                                    dataType: "json",
                                    data : {hizmet_id:hizmetid,tutar:fiyat,sube:$('input[name="sube"]').val()},
                                    headers: {
                                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                                    },
                                    beforeSend: function() {
                                        $("#preloader").show();
                                    },
                                   success: function(result)  {
                                        $("#preloader").hide();
                                        $('.hizmetler_bolumu_adisyon_2').empty();
                                        $('.hizmetler_bolumu_adisyon_2').append(result.hizmet_liste);
                                        adisyontoplamhesapla();
                                        $('#tahsil_edilen_tutar').empty();
                                        $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').empty();
                                        $('#tahsil_edilen_tutar').append(result.adisyon_detay.tahsil_edilen);
                                        $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').append(result.adisyon_detay.kalan_tutar);
                                        $('#adisyon_tahsilati_hizmetler').empty();
                                       //document.getElementById('hata').innerHTML = result.adisyon_detay.hizmet_tahsilata_eklenecek;
                                        $('#adisyon_tahsilati_hizmetler').append(result.adisyon_detay.hizmet_tahsilata_eklenecek);
                                        adisyonyenidenhesapla();
                                         select2ozellikyukle();
                                    },
                                    error: function (request, status, error) {
                                        $("#preloader").hide();
                                        document.getElementById('hata').innerHTML = request.responseText;
                                    }
    });
});
$('.hizmetler_bolumu_adisyon_2').on('change','select[name="islem_hizmetleri"]',function(){
    var hizmetid = $(this).attr('data-value');
    var adiysonhizmetid = $(this).val();
});
$('#musteri_arama').on('paste keyup',function(e){
    e.preventDefault();
    if($(this).val().length >= 3){
         musteriadi = $(this).val();
        $.ajax({
        type: "GET",
        url: '/isletmeyonetim/musteriarama',
        dataType: "text",
        data : {musteri_adi:musteriadi,sube:$('input[name="sube"]').val()},
        success: function(result)  {
            $('#musteriler_filtre_liste').empty();
            $('#musteriler_filtre_liste').append(result);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
    }
});
$(document).on('submit', '.musteri_bilgi_formu',function (e) {
    var yanit_goster = $('input[name="eklendi_yanit_goster"]').val();
    var formdata = $(this).serialize();
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/musteriekleguncelle',
        dataType: "json",
        data : formdata,
         headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            $('.musteri_bilgi_formu').trigger('reset');
            var musteriid = result.musteri_id;
            if(result.status=="success")
                $('#musteri_ekle_modal_kapat').trigger('click');
            var buttons = "";
            if(result.yeniekleme==true){
                buttons = $('<div><p> '+result.mesaj+'</p>'+
                    '<a href="/isletmeyonetim/musteriler?sube='+$('input[name="sube"]').val()+'" class="btn btn-primary btn-lg btn-block">Müşteri Listeme Git</a>'+
                    '<a href="/isletmeyonetim/musteridetay/'+result.musteri_id+'?sube='+$('input[name="sube"]').val()+'" class="btn btn-primary btn-lg btn-block">'+
                    'Müşteri Detayına Git</a></div>');
                var musteridata = result.musteribilgi;
                var data={
                        id:musteridata[0].id,
                        text:musteridata[0].text
                };
                 var data_search={
                        id:'/isletmeyonetim/musteridetay/'+musteridata[0].id,
                        text:musteridata[0].text
                };
                var option = new Option(data.text, data.id, false, false);
                var option_search = new Option(data_search.text, data_search.id, false, false);
                $('#musteri_arama').append(option_search);
                    $('#randevuekle_musteri_id,select[name="ad_soyad"],select[name="musteri"]').append(option).trigger('change');
                    $('#randevuekle_musteri_id,select[name="ad_soyad"],select[name="musteri"]').val(data.id).trigger('change');
            }
            else
                buttons = $('<div><p> '+result.mesaj+'</p></div>');
            console.log(result.sadik_musteriler);
            console.log(result.musteriler);
            console.log(result.aktif_musteriler);
            console.log(result.pasif_musteriler);
            if(yanit_goster==1 || result.status=='warning')
                swal(
                        {
                            type: result.status,
                            title: result.title,
                            html: buttons,
                            showCloseButton: result.showCloseButton,
                            showCancelButton: result.showCancelButton,
                            showConfirmButton: result.showConfirmButton,
                            confirmButtonColor: '#00bc8c',
                            confirmButtonText: 'Ekle',
                            cancelButtonText: "Vazgeç",
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                    }
                );
            if($('.musteri_genel_bilgi_kart').length){
                $('.musteri_genel_bilgi_kart').empty();
                $('.musteri_genel_bilgi_kart').append(result.detailtext);
            }
            if($('#musteri_tablo').length)
            {   
                
                $('#musteri-bilgi-modal').modal('hide');
                $('#musteri_tablo_sadik').DataTable().destroy();
                $('#musteri_tablo').DataTable().destroy();
                $('#musteri_tablo_pasif').DataTable().destroy();
                $('#musteri_tablo_aktif').DataTable().destroy();
                  var sadiktablo = $('#musteri_tablo_sadik').DataTable({
                          pageLength: 10,
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
                           { data:'odenen', visible: !odenenGizlensin},
                           { data : 'islemler'},
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
                        pageLength: 10,
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
                              {data:'odenen', visible: !odenenGizlensin},
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
                       pageLength: 10,
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
                           {data:'odenen', visible: !odenenGizlensin},
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
                       pageLength: 10,
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
                           { data:'odenen', visible: !odenenGizlensin},
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
                     pasiftablo.columns.adjust().draw();
                     aktiftablo.columns.adjust().draw();
                     musteritablo.columns.adjust().draw();
                     sadiktablo.columns.adjust().draw();
            }
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
function modalbaslikata(str,form_id){
    $('.modal_baslik').empty();
    $('.modal_baslik').append(str);
    if(form_id != ""){
        $('#'+form_id)[0].reset();
        $('input[type="hidden"]').each(function(){
            if($(this).attr('name')!='sube' &&$(this).attr('name')!='_token'  &&$(this).attr('name')!='masraf_sayfasi' && $(this).attr('name') != 'takvim_sayfasi')
                $(this).val('');
        });
        if($('#sistem_yetki').length)
            $('#sistem_yetki').removeAttr('disabled');
    }
}
$(document).on('click','.yanitsiz_musteri_ekleme',function (e) {
    $('#eklendi_yanit_goster').val(0);
    // body...
});
$(document).on('click','.yanitli_musteri_ekleme',function (e) {
    $('#eklendi_yanit_goster').val(1);
});
function createButton(text, cb) {
  return $('<button class="btn btn-primary btn-lg btn-block">' + text + '</button>').on('click', cb);
}
$('#calisma_mola_saatleri').on('submit',function (e) {
   e.preventDefault();
   $.ajax({
        type: "POST",
        url: '/isletmeyonetim/calismasaatleriduzenle',
        dataType: "text",
        data : $('#calisma_mola_saatleri').serialize(),
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            swal(
                {
                    type: 'success',
                    title: 'Başarılı',
                    text: result,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                }
            );
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#randevu_ayarlari').on('submit',function(e){
    e.preventDefault();
      $.ajax({
        type: "POST",
        url: '/isletmeyonetim/randevuayarguncelle',
        dataType: "text",
        data : $('#randevu_ayarlari').serialize(),
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            swal(
                {
                    type: 'success',
                    title: 'Başarılı',
                    text: result,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                }
            );
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#isletme_temel_bilgiler').on('submit', function(e){
    e.preventDefault();
      $.ajax({
        type: "POST",
        url: '/isletmeyonetim/isletmebilgiguncelle',
        dataType: "text",
        data : $('#isletme_temel_bilgiler').serialize(),
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            swal(
                {
                    type: 'success',
                    title: 'Başarılı',
                    text: result,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                }
            );
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('change','#tekrarlayan',function(){
    if (this.checked) {
         $('.tekrar_randevu').removeAttr('disabled');
    }
    else{
        $('.tekrar_randevu').prop('disabled',true);
    }
    //blah blah
});
$(document).on('change','#tekrarlayan_saat_kapama',function(){
    if (this.checked) {
         $('.tekrar_saat_kapama').removeAttr('disabled');
    }
    else{
        $('.tekrar_saat_kapama').prop('disabled',true);
    }
    //blah blah
});
$(document).on('submit','#saat_kapama',function(e){
    e.preventDefault();
      $.ajax({
        type: "POST",
        url: '/isletmeyonetim/saatkapamaekle',
        dataType: "text",
        data : $(this).serialize(),
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            swal(
                {
                    type: 'success',
                    title: 'Başarılı',
                    text: result,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                }
            );
            takvimyukle(false,false);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('submit','#adisyon_tahsilat',function(e){
        e.preventDefault();
        console.log($('#adisyon_tahsilat').serialize());
        var formData = new FormData();
        $('#adisyon_tahsilat .adisyon_kalemler').each(function(){
            if($(this).attr('name')=='adisyon_odeme_hizmet[]'){
                formData.append('adisyon_hizmet_id[]',$(this).attr('data-value'));
                formData.append('h