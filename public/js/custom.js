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

function randevuyaGelmedi(hizmetid,id,seansDusumuYap)
{
     $.ajax({
                type: "POST",
                url: '/isletmeyonetim/randevuyagelmedi',
                data: {randevuid:id,sube:$('input[name="sube"]').val(),_token:$('input[name="_token"]').val(),hizmetId:hizmetid,seansDusumuYap:seansDusumuYap} ,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();

                    if(result.seansDusmeOnayi)
                    {
                        swal({
                            title: "Seans Kullanımı",
                            html: "<p style='font-size:14px'>"+result.mesaj+"</p>",
                            type: "info",
                            showCancelButton: true,
                            confirmButtonColor: '#00bc8c',
                            confirmButtonText: 'Evet',
                            cancelButtonText: "Hayır",
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                        }).then(function (result) {
                            if(result.value){

                                randevuyaGelmedi(hizmetid,id,1);

                               
                            }
                            else
                            {
                                randevuyaGelmedi(hizmetid,id,0)
                            }
                        });
                    }
                    else
                    {
                        $('#modal-view-event').modal('hide');
                    
                        if($('#randevu_liste').length){
                             randevufiltre();
                        }
                        if($('#calendar').length){
                            takvimyukle(false,false);
                        }
                    }
                   
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML =request.responseText;
                     $('#preloader').hide();
                }
            });
}

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

            randevuyaGelmedi(hizmetid,id,'');

           
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
                            "order": [[ 4, "desc" ]],
                            columns:[
                                 { data: 'musteri'   },
                                 { data: 'telefon' },
                                 { data: 'hizmetler'   },
                                  { data: 'personelcihazoda'   },
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
    var totalFormDuration = 0;
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
       
    });
     if($('#randevuduzenle_saat').val()==''){

        warningtext += "- Randevu saatini seçiniz.<br>";
        saatsecili = false;
    }
    $(this).find('.hizmet-suresi').each(function() {
                    totalFormDuration += parseFloat($(this).val()) || 0;
    });
    if(totalFormDuration == 0)
    {
         warningtext += "- Hizmetlerin toplam süresi sıfırdan büyük olmalıdır.<br>";
        suregirildi =false;
    }
    if(personelveyacihasecili == false || hizmetsecili == false || suregirildi == false || musterisecili == false ||saatsecili ==false  || totalFormDuration == 0){
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
        $('#yenirandevuekleform select[name="randevuhizmetleriyeni"]').each(function(index1){
            if($(this).val()=='')
                formData.append("randevuhizmetleriyeni_"+index1+"[]","");
            else{
                var $selectedOptions = $(this).find('option:selected');
                $selectedOptions.each(function(){
                    formData.append('randevuhizmetleriyeni_'+index1+'[]',$(this).val());
                });
            }
        });
        $.each(other_data,function(key,input){
            if(input != "randevuyardimcipersonelleriyeni" && input != 'randevuhizmetleriyeni')
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
                    /*$('.hizmetler_bolumu div.row').each(function(e){
                        if($(this).attr('data-value')!="0")
                         $(this).remove();
                    });
                    $('#yenirandevuekleform').trigger('reset');
                    $('#randevuekle_musteri_id').val('0').trigger('change');
                    $('select[name="randevupersonelleriyeni[]"]').val('0').trigger('change');
                    $('#randevuhizmetleriyeni').val('0').trigger('change');*/
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
                    resetForm();
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
/*$(document).on('click','#bir_hizmet_daha_ekle',function (e) {
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
});*/
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
                form[0].reset();
                //$('#paket_formu_duzenleme')[0].reset();
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
                console.log('eklenen paket :'+result.eklenen_paket_id+" - "+result.eklenen_paket);  
                $('select[name="paketadiadisyon[]"]').each(function () {
                    var data = {
                        id: result.eklenen_paket_id,
                        text: result.eklenen_paket
                    };
                    var option = new Option(data.text, data.id, false, false);
                    $(this).append(option);
                    $(this).val(data.id);
                });
                  $('select[name="paketadi[]"]').each(function () {
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
                 $('input[name="paketfiyat[]"]').each(function () {
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
                    $('#paketFiyati').val(result.fiyat);
                    $('#paketSeanslari').val(result.miktar);
                    let select = $('#paketHizmetleri');
                    select.empty();

                    result.hizmetler.forEach(h => {
                        // option DOM’a ekle, seçili yap
                        let option = new Option(h.hizmet.hizmet_adi, h.hizmet_id, true, true);
                        select.append(option);
                    });
                   
                    select.val(hizmet_ids).trigger('change');

                    
                    //select.trigger('change');
                                
                    
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
    
    
    if($('input[name="adisyon_id"]').val()!='' ||$('#tahsilat_ekrani').length > 0){
        formData = new FormData();
        var other_data = $(this).serializeArray();
            $.each(other_data,function(key,input){
                formData.append(input.name,input.value);
        });
        if($('#tahsilat_ekrani').length){
            formData.append('tahsilatekrani',$('#tahsilat_ekrani').val());
            formData.append('adisyonsuz',$('#adisyonsuz').val());

            formData.append('satisDuzenle',false);
            formData.append('musteri_id',$('select[name="tahsilat_musteri_id"]').val());
            $('#tum_tahsilatlar input[name="adisyon_urun_id[]"]').each(function(e){
                 formData.append('adisyon_urun_id[]',$(this).val());

            });
              $('#tum_tahsilatlar input[name="adisyon_paket_id[]"]').each(function(e){
                 formData.append('adisyon_paket_id[]',$(this).val());

            });
            $('#tum_tahsilatlar input[name="adisyon_hizmet_id[]"]').each(function(e){
                 formData.append('adisyon_hizmet_id[]',$(this).val());

            });
        }
        if($('#satis_takibi_ekrani').length)
            formData.append('satisDuzenle',true);
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
                        if($('#tahsilat_ekrani').length>0 || $('#satis_takibi_ekrani').length>0)
                        {
                            if($('#tum_tahsilatlar').length)
                            {
                                $('#tum_tahsilatlar').empty();
                                $('#tum_tahsilatlar').append(result.kalemler);
                            }
                            if($('#tum_tahsilatlar_duzenleme').length)
                            {
                                $('#tum_tahsilatlar_duzenleme').empty();
                                $('#tum_tahsilatlar_duzenleme').append(result.kalemler);
                            }
                            

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
                            { data: 'durum'},
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
                            { data: 'durum'},
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
                            { data: 'durum'},
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
                            { data: 'durum'},
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
    formData.append('adisyonsuz',$('#adisyonsuz').val());
    $('#tum_tahsilatlar input[name="adisyon_urun_id[]"]').each(function(e){
         formData.append('adisyon_urun_id[]',$(this).val());

    });
      $('#tum_tahsilatlar input[name="adisyon_paket_id[]"]').each(function(e){
         formData.append('adisyon_paket_id[]',$(this).val());

    });
    $('#tum_tahsilatlar input[name="adisyon_hizmet_id[]"]').each(function(e){
         formData.append('adisyon_hizmet_id[]',$(this).val());

    });
    if($('#satis_takibi_ekrani').length)
        formData.append('satisDuzenle',true);
    else
        formData.append('satisDuzenle',false);
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
                        $('#urun_satisi_modal').modal('hide');
                        if($('#tahsilat_ekrani').length>0 || $('#satis_takibi_ekrani').length>0)
                        {
                             if($('#tum_tahsilatlar').length)
                            {
                                $('#tum_tahsilatlar').empty();
                                $('#tum_tahsilatlar').append(result.kalemler);
                            }
                            if($('#tum_tahsilatlar_duzenleme').length)
                            {
                                $('#tum_tahsilatlar_duzenleme').empty();
                                $('#tum_tahsilatlar_duzenleme').append(result.kalemler);
                            }
                            
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

function musteriEkleGuncelle(kvkkOnayKodu,musteriFormu)
{
    var yanit_goster = $('#eklendi_yanit_goster').val();
    var formdata = new FormData();
    //var other_data = $('.musteri_bilgi_formu').serializeArray();
    var other_data = $('#'+musteriFormu).serializeArray();
    $.each(other_data,function(key,input){
            formdata.append(input.name,input.value);
    });
    if(kvkkOnayKodu != '')
        formdata.append('kvkkOnayKodu',kvkkOnayKodu);

 
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/musteriekleguncelle',
        dataType: "json",
        data : formdata,
        contentType: false,
        processData: false,     
         headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
           
            var musteriid = result.musteri_id;

            console.log('result : '+JSON.stringify(result))
            if(result.status === "success" && result.onayGerekli === false){
                console.log('Eklendi');
                 $(musteriFormu).trigger('reset');
                if($('#musteri_ekle_modal_kapat').length)
                    $('#musteri_ekle_modal_kapat').trigger('click');
                if($('#musteri_guncelle_modal_kapat').length){
                    console.log('güncelleme penceresi kapat');
                    $('#musteri_guncelle_modal_kapat').trigger('click');
                }
            }
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
            
            if(yanit_goster==1 || result.status=='warning'  || result.onayGerekli){

                swal(
                        {
                            type: result.status,
                            title: result.title,
                            input: result.onayGerekli ? 'text' : null,
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
                    ).then(function (result) {
                    if(result.value){
                        console.log("Onay kodu "+result.value+' form id '+musteriFormu);
                        musteriEkleGuncelle(result.value,musteriFormu);
                    }
                     
                });
            }

           
            if(result.yeniekleme != true)
            {
                 if($('.musteri_genel_bilgi_kart').length){
                    $('.musteri_genel_bilgi_kart').empty();
                    $('.musteri_genel_bilgi_kart').append(result.detailtext);
                }
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
}
$(document).on('submit', '.musteri_bilgi_formu',function (e) {
    e.preventDefault();
    console.log("Form id "+$(this).attr('id'));
    musteriEkleGuncelle('',$(this).attr('id'));
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
                formData.append('hizmet_odeme_secili[]','true');
            }
            if($(this).attr('name')=='adisyon_odeme_urun[]'){
                formData.append('adisyon_urun_id[]',$(this).attr('data-value'));
                formData.append('urun_odeme_secili[]','true');
            }
            if($(this).attr('name')=='adisyon_odeme_paket[]'){
                formData.append('adisyon_paket_id[]',$(this).attr('data-value'));
                formData.append('paket_odeme_secili[]','true');
            }
        });
        if($('#indirim_tutari').prop('disabled'))
            formData.append('indirim_tutari',$('#indirim_tutari').val())
        var other_data = $('#adisyon_tahsilat').serializeArray();
        $.each(other_data,function(key,input){
            formData.append(input.name,input.value);
        });
        for (var pair of formData.entries()) {
            console.log(pair[0]+ ', ' + pair[1]);
        }
        $.ajax({
            type: "POST",
            url: '/isletmeyonetim/tahsilatekle',
            dataType: "json",
            data : formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $("#preloader").show();
            },
            success: function(result)  {
                $("#preloader").hide();
                swal(
                    {
                        type: 'success',
                        title: 'Başarılı',
                        text: result.statustext,
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer:3000,
                    }
                );
                
                if($('#tahsilat_ekrani').length )
                {
                    $('#tum_tahsilatlar').empty();
                    $('#tum_tahsilatlar').append(result.kalemler);
                    $('#tahsilat_listesi').empty();
                    $('#tahsilat_listesi').append(result.tahsilatlar);
                    $('input[name="taksitvadeler[]"]:checked').each(function(){
                        $(this).prop('checked',false);
                    });
                    $('input[name="senetvadeler[]"]:checked').each(function(){
                        $(this).prop('checked',false);
                    });
                    tahsilatyenidenhesapla();
                    if($('input[name="adisyon_id"]').val()=='')
                        $('select[name="tahsilat_musteri_id"]').trigger('change');
                }
                else
                {
                    $('#tahsilat_listesi').empty();
                    $('#tahsilat_listesi').append(result.html);
                    $('#tahsilat_sayisi').val(result.tahsilat_sayisi);
                    $('#tahsil_edilen_tutar').empty();
                    $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').empty();
                    $('#tahsil_edilen_tutar').append(result.tahsilat_tutari);
                    $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').append(result.kalan_tutar);
                    $('#adisyon_tahsilati_hizmetler').empty();
                    $('#adisyon_tahsilati_hizmetler').append(result.adisyon_hizmetler_html);
                    $('#adisyon_tahsilati_urunler').empty();
                    $('#adisyon_tahsilati_urunler').append(result.adisyon_urunler_html);
                    $('#adisyon_tahsilati_paketler').empty();
                    $('#adisyon_tahsilati_paketler').append(result.adisyon_paketler_html);
                    $('#odenecek_tutar').val("0,00");
                    adisyonyenidenhesapla();
                    
                }
            },
            error: function (request, status, error) {
                $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
            }
        });
});
$('input[name="randevuya_geldi_gelmedi"]').change(function(e){
    var geldi = $(this).val();
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/randevuguncelle',
        dataType: "text",
        data : $('#adisyon_form').serialize(),
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
            alert(geldi);
            if(geldi == 1){
                $('#odeme_kayit_bolumu').attr('style','display:block');
            }
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#adisyon_form').on('submit',function (e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/randevuguncelle',
        dataType: "text",
        data : $('#adisyon_form').serialize(),
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
            alert(geldi);
            if(geldi == 1){
                $('#odeme_kayit_bolumu').attr('style','display:block');
            }
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});

function tahsilatSil(dogrulamaKodu,id)
{   
    var satis_duzenle = false; 
    if($('#satis_takibi_ekrani').length)
        satis_duzenle = true;
     
    var adisyonid = $('input[name="adisyon_id"]').val();
    $.ajax({
                type: "POST",
                url: '/isletmeyonetim/tahsilatkaldir',
                dataType: "json",
                data : {satisDuzenle:satis_duzenle,dogrulama_kodu:dogrulamaKodu, tahsilatid:id,adisyon_id:adisyonid,tahsilatekrani:$('#tahsilat_ekrani').length,sube:$('input[name="sube"]').val()},
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                beforeSend: function() {
                    $("#preloader").show();
                },
                success: function(result2)  {
                    $("#preloader").hide();
                    if(result2.dogrulamaGerekli)
                    {
                        $(document).off('focusin.modal');
                        swal({
                            title: 'Lütfen hesap sahibinin cep telefonuna gönderilen onay kodunu giriniz!',
                            input: 'text',
                            showCancelButton: true,
                            confirmButtonText: 'Gönder',
                            cancelButtonText: 'Vazgeç',
                            showLoaderOnConfirm: true,
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                        }).then(function (result3) {
                            if(result3.value){
                                tahsilatSil(result3.value,id);                 
                                // modal focus kilidini geri bağla
                                $(document).on('focusin.modal', function (e) {
                                    if ($(e.target).closest('.modal').length === 0) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                    }
                                });
                                

                               
                            }
                        })
                    }
                    else
                    {
                        if($('#tahsilat_ekrani').length || $('#satis_takibi_ekrani').length)
                     {
                        swal(
                        {
                            type: 'success',
                            title: 'Başarılı',
                            text: 'Tahsilat kaydı başarıyla kaldırıldı',
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                            }
                        );
                        if($('#tum_tahsilatlar').length)
                        {
                                $('#tum_tahsilatlar').empty();
                                $('#tum_tahsilatlar').append(result2.kalemler);
                                $('#tahsilat_listesi').empty();
                                $('#tahsilat_listesi').append(result2.tahsilatlar);
                        }
                        if($('#tum_tahsilatlar_duzenleme').length)
                        {
                                $('#tum_tahsilatlar_duzenleme').empty();
                                $('#tum_tahsilatlar_duzenleme').append(result2.kalemler);
                                $('#tahsilat_listesi_duzenleme').empty();
                                $('#tahsilat_listesi_duzenleme').append(result2.tahsilatlar);
                        }
                       
                       
                        tahsilatyenidenhesapla();
                     }
                     else{
                        swal(
                        {
                            type: 'success',
                            title: 'Başarılı',
                            text: result2.statustext,
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                            }
                        );
                        if($('#satis_takibi_ekrani').length==0)
                            $('button[data-dismiss="modal"]').trigger('click');
                        $('#tahsilat_listesi').empty();
                        $('#tahsilat_listesi').append(result2.html);
                        $('#tahsilat_sayisi').val(result2.tahsilat_sayisi);
                        $('#tahsil_edilen_tutar').empty();
                        $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').empty();
                        $('#tahsil_edilen_tutar').append(result2.tahsilat_tutari);
                        $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').append(result2.kalan_tutar);
                        $('#adisyon_tahsilati_hizmetler').empty();
                        $('#adisyon_tahsilati_hizmetler').append(result2.adisyon_hizmetler_html);
                        $('#adisyon_tahsilati_urunler').empty();
                        $('#adisyon_tahsilati_urunler').append(result2.adisyon_urunler_html);
                        $('#adisyon_tahsilati_paketler').empty();
                        $('#adisyon_tahsilati_paketler').append(result2.adisyon_paketler_html);
                        $('#senetli_adisyon_tahsilati_hizmetler').empty();
                        $('#senetli_adisyon_tahsilati_hizmetler').append(result2.adisyon_hizmetler_html);
                        $('#senetli_adisyon_tahsilati_urunler').empty();
                        $('#senetli_adisyon_tahsilati_urunler').append(result2.adisyon_urunler_html);
                        $('#seneetli_adisyon_tahsilati_paketler').empty();
                        $('#seneetli_adisyon_tahsilati_paketler').append(result2.adisyon_paketler_html);
                         $('#adisyon_tahsilati_senetler').empty();
                        $('#adisyon_tahsilati_senetler').append(result2.adisyon_paketler_html);
                        adisyonyenidenhesapla();
                     }
                    }
                    
                     
                },
                error: function (request, status, error) {
                    $("#preloader").hide();
                    document.getElementById('hata').innerHTML = request.responseText;
                }
    });
   
    
}

$(document).on('click','button[name="tahsilat_adisyondan_sil"]',function(e){
    e.preventDefault();
    var id=$(this).attr('data-value');
    swal({
                        title: "Emin misiniz?",
                        text: "Tahsilat kaydını silmek istediğinize emin misiniz? Bu işlem geri alınamaz",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#00bc8c',
                        confirmButtonText: 'Tahsilatı Sil',
                        cancelButtonText: "Vazgeç",
                        confirmButtonClass: 'btn btn-success',
                        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value)
        {
            tahsilatSil('',id);
        }
    });
   
   
});
$(document).on('submit','#ongorusmeformu',function(e){
    e.preventDefault();
    var warningtext = "";
    var gorusmeyapansecili = true;
    var paketurunsecili = true;
    if($('#ongorusmeformu select[name="paket_urun"]').val()=="")
    {
        paketurunsecili = false;
        warningtext += "- Ön görüşme sebebini seçiniz.<br>";
    }
    if($('#ongorusmeformu select[name="gorusmeyi_yapan"]').val()=="")
    {
        gorusmeyapansecili = false;
        warningtext += "- Ön görüşmeyi gerçekleştirecek personeli seçiniz.<br>";
    }
    if(gorusmeyapansecili == false || paketurunsecili == false)
    {
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
        $.ajax({
        type: "POST",
        url: '/isletmeyonetim/ongorusmeekleduzenle',
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
                        $('#musteri_select_list').val('0').trigger('change');
                        $('#paket').val('0').trigger('change');
                        $('#gorusmeyi_yapan').val('0').trigger('change');
            $('button[data-dismiss="modal"]').trigger('click');
            swal({
                    type: 'success',
                    title: 'Başarılı',
                    html: 'Ön görüşme başarıyla kaydedildi',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer: 3000,
            });
            if($('#on_gorusme_liste').length){
                $('#on_gorusme_liste').DataTable().destroy();
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
            if($('#calendar').length)
                takvimyukle(false,false);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
    }
});
$(document).on('change','#musteri_select_list',function(e){
    e.preventDefault();
    var musteriid = $(this).val();
    $.ajax({
            type: "GET",
            url: '/isletmeyonetim/musteribilgigetir',
            dataType: "json",
            data : {musteri_id:musteriid,sube:$('input[name="sube"]').val()},
            beforeSend: function() {
                $("#preloader").show();
            },
           success: function(result)  {
               $("#preloader").hide();
                $('#ad_soyad').val(result.name);
                $('#telefon').val(result.cep_telefon);
                $('#email').val(result.email);
                $('#cinsiyet').val(result.cinsiyet);
                $('#adres').val(result.adres);
                $('#meslek').val(result.meslek);
                $('#musteri_tipi').val(result.musteri_tipi);
                $('#sehir').val(result.sehir);
                console.log(result);
            },
            error: function (request, status, error) {
                 document.getElementById('hata').innerHTML = request.responseText;
                 $("#preloader").hide();
            }
        });
});
$('#hepsini_sec_liste').change(function(e){
    if (this.checked) {
        $('input[type="checkbox"]').each(function(){
            $(this).attr('checked',true);
        });
    }
    else{
        $('input[type="checkbox"]').each(function(){
            $(this).attr('checked',false);
        });
    }
});
$(document).on('click','a[name="ongorusme_duzenle"]',function(e){
    e.preventDefault();
      $.ajax({
            type: "GET",
            url: '/isletmeyonetim/ongorusmedetay',
            dataType: "json",
            data : {ongorusme_id:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
            beforeSend: function() {
                $("#preloader").show();
            },
           success: function(result)  {
                $("#preloader").hide();
                console.log(result);
                $('#on_gorusme_id').val(result.id);
                $('#ad_soyad').val(result.ad_soyad);
                $('#telefon').val(result.cep_telefon);
                $('#email').val(result.email);
                $('#cinsiyet').val(result.cinsiyet);
                $('#adres').val(result.adres);
                $('#meslek').val(result.meslek);
                $('#musteri_tipi').val(result.musteri_tipi);
                $('#ongorusme_tarihi').val(result.tarih);
                $('#ongorusme_saati').val(result.saat);
                if(result.paket_id != null)
                {
                    $('select[name="paket_urun"]').val(result.paket_id)
                    $('select[name="paket_urun"]').select2("trigger", "select", {
                        data: { id: result.paket_id }
                    });
                }
                if(result.urun_id != null)
                {
                    $('select[name="paket_urun"]').val(result.urun_id)
                    $('select[name="paket_urun"]').select2("trigger", "select", {
                        data: { id: result.urun_id }
                    });
                }
                if(result.user_id != null){
                    $('#musteri_select_list').val(result.user_id);
                    $("#musteri_select_list").select2("trigger", "select", {
                        data: { id: result.user_id }
                    });
                }
                $('#sehir').val(result.il_id);
                $('#paket').val(result.paket_id);
                $("#gorusmeyi_yapan").select2("trigger", "select", {
                    data: { id: result.paket_id }
                });
                $('#hatirlatma_yeni_ekleme').attr('style','display:none');
                $('#hatirlatma_tarihi_guncelleme').attr('style','display:block');
                $('#hatirlatma_tarihi').val(result.hatirlatma_tarihi);
                $('#gorusmeyi_yapan').val(result.personel_id);
                $('#aciklama').val(result.aciklama);
                $("#gorusmeyi_yapan").select2("trigger", "select", {
                    data: { id: result.personel_id }
                });
                 $("#sehir").select2("trigger", "select", {
                    data: { id: result.il_id }
                });
            },
            error: function (request, status, error) {
                 document.getElementById('hata').innerHTML = request.responseText;
                 $("#preloader").hide();
            }
        });
});
function ongorusmeseansgirdikontrol(ongorusme_id,durum,tur)
{
    if(durum && tur==1)
    {
        swal({
        title: "Onayla",
          html: "<p>Paket satışına devam etmek için lütfen aşağıda seans sayısı ve fiyatını belirleyin!</p>"+
                "<div class='row'><div class='col-md-6'><label>Seans Sayısı</label><input type='tel' class='form-control'"+
                " id='ongorusme_seans_sayisi'></div><div class='col-md-6'><label>Fiyat (₺)</label><input type='tel' class='form-control' "+
                "id='ongorusme_satis_fiyat'></div></div>",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Gönder',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
        }).then(function (result) {
            if(result.value){
                if($('#ongorusme_seans_sayisi').val()!='' && $('#ongorusme_satis_fiyat').val()!='')
                {
                    $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/ongorusmesatisyapildi',
                        dataType: "json",
                        data : {sube:$('input[name="sube"]').val(),ongorusmeid:ongorusme_id,_token:$('input[name="_token"]').val(),seans:$('#ongorusme_seans_sayisi').val(),fiyat:$('#ongorusme_satis_fiyat').val()},
                        headers: {
                            'X-CSRF-TOKEN': $('input[name="_token"]').val()
                        },
                        beforeSend: function() {
                            $("#preloader").show();
                        },
                        success: function(result2)  {
                            $("#preloader").hide();
                            swal(
                                {
                                    type: 'success',
                                    title: 'Başarılı',
                                    html: "<p>Paket satışı başarıyla oluşturuldu</p>"+
                                                  "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/tahsilat/"+result2.user_id+"/"+result2.adisyon_id+"?sube="+$('input[name="sube"]').val()+"'>"
                                                  +"Tahsil Et</a>",
                                    showCloseButton: false,
                                    showCancelButton: false,
                                    showConfirmButton:false,
                                }
                            );
                            $('#on_gorusme_liste').DataTable().destroy();
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
                                    data: result2.on_gorusmeler,
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
                else
                {
                    ongorusmeseansgirdikontrol(ongorusme_id,true,1);
                }
            }
        });
        $('#ongorusme_satis_seans_baslangic').datepicker({
            language: "tr",
            autoClose: true,
            dateFormat: "yyyy-mm-dd",
        });
    }
    else if(durum && tur==2)
    {
        swal({
        title: "Onayla",
        html: "<p>Ürün satışına devam etmek için lütfen ürün adedini belirleyiniz!</p>"+
                "<div class='row'><div class='col-md-12'><label>Adet</label><input type='text' class='form-control'"+
                " id='ongorusme_urunsatisi_adet' value='1' required></div></div>",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Gönder',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
        }).then(function (result) {
            if(result.value){
                if($('#ongorusme_urunsatisi_adet').val()!='')
                {
                    $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/ongorusmesatisyapildi',
                        dataType: "json",
                        data : {sube:$('input[name="sube"]').val(),ongorusmeid:ongorusme_id,_token:$('input[name="_token"]').val(),urun_adedi:$('#ongorusme_urunsatisi_adet').val()},
                        headers: {
                            'X-CSRF-TOKEN': $('input[name="_token"]').val()
                        },
                        beforeSend: function() {
                            $("#preloader").show();
                        },
                        success: function(result2)  {
                            $("#preloader").hide();
                            swal(
                                {
                                    type: 'success',
                                    title: 'Başarılı',
                                    html: "<p>Ürün satışı başarıyla oluşturuldu</p>"+
                                                  "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/tahsilat/"+result2.user_id+"/"+result2.adisyon_id+"?sube="+$('input[name="sube"]').val()+"'>"
                                                  +"Tahsil Et</a>",
                                    showCloseButton: false,
                                    showCancelButton: false,
                                    showConfirmButton:false,
                                }
                            );
                            $('#on_gorusme_liste').DataTable().destroy();
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
                                    data: result2.on_gorusmeler,
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
                else
                {
                    ongorusmeseansgirdikontrol(ongorusme_id,true,2);
                }
            }
        });
    }
    else if(durum && tur==3)
        {
            swal({
            title: "Onayla",
              html: "<p>Hizmet satışına devam etmek için lütfen aşağıdan randevu tarihini belirleyin!</p>"+
                    "<div class='row'><div class='col-md-4'><label>Tarih</label><input type='text' class='form-control'"+
                 " id='ongorusme_hizmet_satis_seans_baslangic'></div><div class='col-md-4'><label>Saat</label><input type='time' class='form-control' value='09:00' style=' height:30px;' id='hizmet_ongorusme_satis_saat'></div></div>",
               
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#00bc8c',
            confirmButtonText: 'Gönder',
            cancelButtonText: "Vazgeç",
            confirmButtonClass: 'btn btn-success',
            cancelButtonClass: 'btn btn-danger',
            }).then(function (result) {
                if(result.value){
                    if($('#ongorusme_hizmet_satis_seans_baslangic').val()!='' )
                    {
                        $.ajax({
                            type: "POST",
                            url: '/isletmeyonetim/ongorusmesatisyapildi',
                            dataType: "json",
                            data : {sube:$('input[name="sube"]').val(),ongorusmeid:ongorusme_id,_token:$('input[name="_token"]').val(),hizmet_tarihi:$('#ongorusme_hizmet_satis_seans_baslangic').val(),hizmet_randevu_saati:$('#hizmet_ongorusme_satis_saat').val(),hizmet_seans_araligi:$('#ongorusme_hizmet_satis_seans_araligi').val(),hizmet_kac_seans:$('#ongorusme_hizmet_satis_seans_adet').val(),personelsec:$('#hizmet_satis_personel_id').val()},
                            headers: {
                                'X-CSRF-TOKEN': $('input[name="_token"]').val()
                            },
                            beforeSend: function() {
                                $("#preloader").show();
                            },
                            success: function(result2)  {
                                $("#preloader").hide();
                                swal(
                                    {
                                        type: 'success',
                                        title: 'Başarılı',
                                        html: "<p>Hizmet satışı başarıyla oluşturuldu</p>"+
                                                      "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/tahsilat/"+result2.user_id+"/"+result2.adisyon_id+"?sube="+$('input[name="sube"]').val()+"'>"
                                                      +"Tahsil Et</a>",
                                        showCloseButton: false,
                                        showCancelButton: false,
                                        showConfirmButton:false,
                                    }
                                );
                                $('#on_gorusme_liste').DataTable().destroy();
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
                                        data: result2.on_gorusmeler,
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
                    else
                    {
                        ongorusmeseansgirdikontrol(ongorusme_id,true,3);
                    }
                }
            });
            select2YenidenYukle2();
            $('#ongorusme_satis_seans_baslangic').datepicker({
                language: "tr",
                autoClose: true,
                dateFormat: "yyyy-mm-dd",
            });
            $('#ongorusme_hizmet_satis_seans_baslangic').datepicker({
                language: "tr",
                autoClose: true,
                dateFormat: "yyyy-mm-dd",
            });
        }
    else
    {
        swal({
        title: "Onayla",
        html: "<p>Satış yapılmama sebebini giriniz!</p>"+
                "<div class='row'><div class='col-md-12'><label>Açıklama</label><input type='text' class='form-control'"+
                " id='satisyapilmadi' required/></div></div>",
        type: "warning",
 showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Ekle',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
        }).then(function (result) {
          if(result.value){
            if ($('#satisyapilmadi').val()!='') {
                  $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/ongorusmesatisyapilmadi',
                        dataType: "json",
                        data : {sube:$('input[name="sube"]').val(),ongorusmeid:ongorusme_id,_token:$('input[name="_token"]').val(),satisyapilmamasebebi:$('#satisyapilmadi').val()},
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
                                text: 'Ön görüşme durumu SATIŞ YAPILMADI olarak güncellendi',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                                }
                            );
                            $('#on_gorusme_liste').DataTable().destroy();
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
                            $("#preloader").hide();
                            document.getElementById('hata').innerHTML = request.responseText;
                        }
            });
            }
               else
                {
                    ongorusmeseansgirdikontrol(ongorusme_id,false,1);
                }
          }
        });
    }
}
$(document).on('click','a[name="satis_yapildi"]',function(e){
    e.preventDefault();
    ongorusmeseansgirdikontrol($(this).attr('data-value'),true,1);
});
$(document).on('click','a[name="satis_yapilmadi"]',function(e){
    e.preventDefault();
    ongorusmeseansgirdikontrol($(this).attr('data-value'),false,1);
});
$(document).on('click','a[name="urun_satis_yapildi"]',function(e){
    e.preventDefault();
    ongorusmeseansgirdikontrol($(this).attr('data-value'),true,2);
});
$(document).on('click','a[name="hizmet_satis_yapildi"]',function(e){
    e.preventDefault();
    ongorusmeseansgirdikontrol($(this).attr('data-value'),true,3);
});
$('#yeni_on_gorusme_ekle').click(function(){
    $('#hatirlatma_yeni_ekleme').attr('style','display:block');
    $('#hatirlatma_tarihi_guncelleme').attr('style','display:none');
});
$('#secilenlere_sms_gonder').click(function(e){
    e.preventDefault();
    var i = 0;
    $('input:checkbox[name="on_gorusme_bilgi[]"]:checked').each(function(){
         i++;
    });
    if(i==0)
    {
          swal(
                {
                    type: 'warning',
                    title: 'Uyarı',
                    text: 'SMS göndermeden önce lütfen listeden seçim yapınız',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
         });
    }
    else{
        $.ajax({
                type: "GET",
                url: '/isletmeyonetim/hatirlatmasmsgonder',
                dataType: "text",
                data : $('#on_gorusme_liste_form').serialize(),
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                     $('input:checkbox').prop('checked',false);
                     $('#smsmesaj').val('');
                      swal(
                            {
                            type: "success",
                            title: "Başarılı",
                            html: "<p>Seçili müşterilere ön görüşme hatırlatma SMSi gönderildi.</p>",
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                            }
                    );
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
        });
    }
});
$(document).on('click','button[name="satis_formuna_paket_ekle"]',function(){
                  var paketid=$(this).attr('data-value');
                  var tds = $(this).closest('tr').children('td');
                  var currentRow = $(this).closest("tr");
                  var data = $('#paket_liste_modal').DataTable().row(currentRow).data();
                  console.log(tds);
                  $('.paketler_bolumu').find(".row").each(function(index){
                     if($('input[name="paket_id[]"]').eq(index).val() == ""){
                        $('input[name="paketadi[]"]').eq(index).val(tds[0].innerHTML);
                        $('input[name="paketadet[]"]').eq(index).val(tds[1].innerHTML);
                        $('input[name="paketfiyat[]"]').eq(index).val(tds[3].innerHTML);
                        $('input[name="paket_id[]').eq(index).val(paketid);
                        $('select[name="pakethizmet[]"]').eq(index).select2("trigger", "select", {
                            data: { id: data['hizmet_id']}
                        });
                        return false;
                     }
                  });
                  $('#kayitli_paket_ekle_modal_kapat').trigger('click');
             });
$('#paket_satisi').off('submit').on('submit',function(e){
    e.preventDefault();
    
    var formData = new FormData();
    var other_data = $(this).serializeArray();
    $.each(other_data,function(key,input){
                formData.append(input.name,input.value);
    });
    formData.append('musteri_id',$('select[name="musteri_adi_yeni_paket"]').val());
    formData.append('tahsilatekrani',$('#tahsilat_ekrani').val());
    formData.append('adisyonsuz',$('#adisyonsuz').val());
     $('#tum_tahsilatlar input[name="adisyon_urun_id[]"]').each(function(e){
         formData.append('adisyon_urun_id[]',$(this).val());

    });
      $('#tum_tahsilatlar input[name="adisyon_paket_id[]"]').each(function(e){
         formData.append('adisyon_paket_id[]',$(this).val());

    });
    $('#tum_tahsilatlar input[name="adisyon_hizmet_id[]"]').each(function(e){
         formData.append('adisyon_hizmet_id[]',$(this).val());

    });
    if($('#satis_takibi_ekrani').length)
        formData.append('satisDuzenle',true);
    else
        formData.append('satisDuzenle',false);

  
    $.ajax({
                type: "POST",
                url: '/isletmeyonetim/paketsatisekle',
                dataType: "json",
                data : formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                    
                    const previousDisabledValue = $('select[name="musteri_adi_yeni_paket"]').val();

                    $('#paket_satisi').trigger('reset');
                    $('select[name="musteri_adi_yeni_paket"]').val(previousDisabledValue).trigger('change.select2');
                    $('#paket_satisi .paketler_bolumu div').each(function(e){
                        if($(this).attr('data-value')>0)
                            $(this).remove();
                    });

                    if(result.adisyonduzenleme!=true && $('#tahsilat_ekrani').length <= 0)
                    {
                            swal(
                            {
                            type: "success",
                            title: "Başarılı",
                            html: "<p>Paket satışı başarıyla oluşturuldu</p>"+
                                  "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/seanstakip?sube="+$('input[name="sube"]').val()+">"
                                  +"Seans Takibine Git</a>"+
                                  "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/adisyonlar?sube="+$('input[name="sube"]').val()+">"
                                  +"Satış Takibine Git</a>",
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            }
                            );
                    }

                    //$('button[data-dismiss="modal"]').trigger('click');
                    $('#paket_satisi_modal').modal('hide');
                    if($('#adisyon_detay_paket_tablo').length)
                    {
                        $('#adisyon_detay_paket_tablo').empty();
                        $('#adisyon_detay_paket_tablo').append(result.adisyonpaketleri);
                         adisyontoplamhesapla();
                         select2ozellikyukle();
                         $('input[name="seans_tarihi_adisyon_paket"]').each(function(index){
                                                //$(this).datepicker('destroy');
                                                $(this).datepicker({
                                                    language: "tr",
                                                    autoClose: true,
                                                    dateFormat: "yyyy-mm-dd",
                                                     onSelect: function(dateText) {
                                                         seanstarihguncelle(dateText,this.value);
                                                        //alert($(this).attr('data-value')+ ' nolu seans için tarih '+dateText+ ' olarak değiştirildi');
                                                    }
                                                });
                         });
                        $('#tahsil_edilen_tutar').empty();
                        $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').empty();
                        $('#tahsil_edilen_tutar').append(result.tahsil_edilen);
                        $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').append(result.kalan_tutar);
                         $('#adisyon_tahsilati_paketler').empty();
                         $('#adisyon_tahsilati_paketler').append(result.tahsilat_paket_eklenecek);
                         adisyonyenidenhesapla();
                    }
                    if($('#paket_satislari_liste').length){
                        $('#paket_satislari_liste').DataTable().destroy();
                        $('#paket_satislari_liste').DataTable({
                              autoWidth: false,
                               responsive: true,
                                columns:[
                                   {data:'satis_tarihi'},
                                   { data: 'musteri'   },
                                   { data: 'satici' },
                                   { data: 'hizmet'   },
                                   { data: 'miktar' },
                                   { data: 'kullanilan' },
                                   { data: 'kalan_kullanim' },
                                   { data: 'toplam_tutar' },
                                   { data: 'odenen_tutar' },
                                   { data: 'kalan_tutar' },
                                    { data: 'olusturan' },
                                     { data: 'olusturulma' },
                                      { data: 'islemler' }
                                ],
                                data: result.paketsatislari,
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
                    if($('#tahsilat_ekrani').length>0 || $('#satis_takibi_ekrani').length>0)
                    {
                       
                        if($('#tum_tahsilatlar').length)
                        {
                                $('#tum_tahsilatlar').empty();
                                $('#tum_tahsilatlar').append(result.tum_tahsilatlar.kalemler);
                                
                        }
                        if($('#tum_tahsilatlar_duzenleme').length)
                        {
                                $('#tum_tahsilatlar_duzenleme').empty();
                                $('#tum_tahsilatlar_duzenleme').append(result.tum_tahsilatlar.kalemler);
                              
                        }
                        tahsilatyenidenhesapla();
                    }
                   
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
        });
});
$('#masraf_tablo').on('click','*[name="masraf_duzenle"]',function(){
    var masrafid = $(this).attr('data-value');
    $.ajax({
                type: "GET",
                url: '/isletmeyonetim/masraf-detay',
                dataType: "json",
                data : {sube:$('input[name="sube"]').val(),masraf_id:masrafid},
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                    $('#masraf_tutari').val(result.tutar);
                    $('#masraf_tarihi').val(result.tarih);
                    $('#masraf_aciklama').val(result.aciklama);
                    $('#masraf_kategorisi').val(result.masraf_kategori_id);
                    $('#masraf_odeme_yontemi').val(result.odeme_yontemi_id);
                    $('#harcayan').val(result.harcayan_id);
                    $('#masraf_notlari').val(result.notlar);
                    $('#masraf_id').val(result.id);
                    $('input[name="sube"]').val(result.salon_id);
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
        });
});
 $(document).on('click','button[name="masraf_sil"]',function(){
    var masrafid = $(this).attr('data-value');
     swal({
                        title: "Emin misiniz?",
                        text: "Masraf kaydını silmek istediğinize emin misiniz? Bu işlem geri alınamaz",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#00bc8c',
                        confirmButtonText: 'Masrafı Sil',
                        cancelButtonText: "Vazgeç",
                        confirmButtonClass: 'btn btn-success',
                        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
                        if(result.value){
                            $.ajax({
                                    type: "POST",
                                    url: '/isletmeyonetim/masraf-sil',
                                    dataType: "json",
                                    data : {sube:$('input[name="sube"]').val(),_token:$('input[name="_token"]').val(),masraf_id:masrafid},
                                    beforeSend:function(){
                                        $('#preloader').show();
                                    },
                                   success: function(result)  {
                                        $('#preloader').hide();
                                        swal(
                                            {
                                            type: "success",
                                            title: "Başarılı",
                                            html:  result.mesaj,
                                            showCloseButton: false,
                                            showCancelButton: false,
                                            showConfirmButton:false,
                                        });
                                        if($('#masraf_tablo').length)
                                        {
                                             $('#masraf_tablo').DataTable().destroy();
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
                                          data: result.masraflar,
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
                                        if($('#kasa_sayfasi').length){
                                            $('#kasa_gelir_tutari').empty();
                                            $('#kasa_gider_tutari').empty();
                                            $('#kasa_toplam_tutar').empty();
                                             $('#toplam_ciro_tutari').empty();
                                            $('#tahsilatlar_listesi').empty();
                                            $('#masraflar_listesi').empty();
                                           console.log(result.toplam_ciro);
                                            $('#kasa_gelir_tutari').append(result.kasa_raporu.gelir);
                                            $('#kasa_gider_tutari').append(result.kasa_raporu.gider);
                                            $('#kasa_toplam_tutar').append(result.kasa_raporu.toplam);
                                            $('#toplam_ciro_tutari').append(result.kasa_raporu.toplam_ciro);
                                            $('#tahsilatlar_listesi').append(result.kasa_raporu.tahsilatlar);
                                            $('#masraflar_listesi').append(result.kasa_raporu.masraflar);
                                        }
                                       
                                    },
                                    error: function (request, status, error) {
                                        $('#preloader').hide();
                                         document.getElementById('hata').innerHTML = request.responseText;
                                    }
                            });
                        }
    });
});
 $(document).on('click','button[name="para_ekleme_sil"]',function(){
    var tahsilatId = $(this).attr('data-value');
     swal({
                        title: "Emin misiniz?",
                        text: "Eklenmiş para kaydını silmek istediğinize emin misiniz? Bu işlem geri alınamaz",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#00bc8c',
                        confirmButtonText: 'Para Eklemeyi Sil',
                        cancelButtonText: "Vazgeç",
                        confirmButtonClass: 'btn btn-success',
                        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
                        if(result.value){
                            $.ajax({
                                    type: "POST",
                                    url: '/isletmeyonetim/tahsilatkaldir',
                                    dataType: "json",
                                    data : {sube:$('input[name="sube"]').val(),_token:$('input[name="_token"]').val(),tahsilatid:tahsilatId,adisyon_id:'',tahsilatekrani:$('#tahsilat_ekrani').length},
                                    beforeSend:function(){
                                        $('#preloader').show();
                                    },
                                   success: function(result)  {
                                        $('#preloader').hide();
                                        swal(
                                            {
                                            type: "success",
                                            title: "Başarılı",
                                            html:  result.mesaj,
                                            showCloseButton: false,
                                            showCancelButton: false,
                                            showConfirmButton:false,
                                        });
                                       
                                        if($('#kasa_sayfasi').length){
                                            $('#kasa_gelir_tutari').empty();
                                            $('#kasa_gider_tutari').empty();
                                            $('#kasa_toplam_tutar').empty();
                                              $('#toplam_ciro_tutari').empty();
                                            $('#tahsilatlar_listesi').empty();
                                            $('#masraflar_listesi').empty();
                                          
                                            $('#kasa_gelir_tutari').append(result.kasa_raporu.gelir);
                                            $('#kasa_gider_tutari').append(result.kasa_raporu.gider);
                                            $('#kasa_toplam_tutar').append(result.kasa_raporu.toplam);
                                            $('#toplam_ciro_tutari').append(result.toplam_ciro);
                                            $('#tahsilatlar_listesi').append(result.kasa_raporu.tahsilatlar);
                                            $('#masraflar_listesi').append(result.kasa_raporu.masraflar);
                                        }
                                       
                                    },
                                    error: function (request, status, error) {
                                        $('#preloader').hide();
                                         document.getElementById('hata').innerHTML = request.responseText;
                                    }
                            });
                        }
    });
});
$('#masraf_formu').on('submit',function(e){
    e.preventDefault();
    var masrafkategorisisecili = true;
    var odemeyontemisecili = true;
    var harcayansecili = true;
    var warningtext = "";
    if($('#masraf_kategorisi').val() =="")
    {
        warningtext += "- Masraf kategorisini seçiniz.<br>";
        masrafkategorisisecili = false;
    }
    if($('#harcayan').val()=="")
    {
        warningtext += "- Harcama yapan personeli seçiniz.<br>";
        harcayansecili = false;
    }
    if($('#masraf_odeme_yontemi').val()=="")
    {
        warningtext += "- Harcamada kullanılan ödeme yöntemini seçiniz.<br>";
        odemeyontemisecili = false;
    }
    if(harcayansecili == false || odemeyontemisecili == false || masrafkategorisisecili == false)
    {
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
    if($('#kasa_sayfasi').length!== 0)
    {
       
        formData.append('baslangic_bitis_tarihi',$('#zamana_gore_filtre_kasa').val());
        formData.append('odeme_yontemi',$('#odeme_yontemine_gore_filtre').val());
        if($('#zamana_gore_filtre_kasa').val()=='ozel'){
            formData.append('kasa_baslangic_tarihi',$('#kasa_baslangic_tarihi').val());
            formData.append('kasa_bitis_tarihi',$('#kasa_bitis').val());
        }
        else
        {
            formData.append('kasa_baslangic_tarihi','');
            formData.append('kasa_bitis_tarihi','');
        }
    }
     var data = $('#masraf_formu').serializeArray();
     $.each(data,function(key,input){
                formData.append(input.name,input.value);
    });
     $.ajax({
                type: "POST",
                url: '/isletmeyonetim/masrafekleduzenle',
                dataType: "json",
                data : formData,
                 processData: false,
                contentType: false,
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                    $('button[data-dismiss="modal"]').trigger('click');
                    $('#masraf_formu').trigger('reset');
                    swal(
                        {
                        type: "success",
                        title: "Başarılı",
                        html:  result.mesaj,
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                    });
                    if($('#kasa_sayfasi').length){
                        $('#kasa_gelir_tutari').empty();
                        $('#kasa_gider_tutari').empty();
                        $('#kasa_toplam_tutar').empty();
                           $('#toplam_ciro_tutari').empty();
                        $('#tahsilatlar_listesi').empty();
                        $('#masraflar_listesi').empty();
                     
                        $('#kasa_gelir_tutari').append(result.kasa_raporu.gelir);
                        $('#kasa_gider_tutari').append(result.kasa_raporu.gider);
                        $('#kasa_toplam_tutar').append(result.kasa_raporu.toplam);
                        $('#toplam_ciro_tutari').append(result.kasa_raporu.toplam_ciro);
                        $('#tahsilatlar_listesi').append(result.kasa_raporu.tahsilatlar);
                        $('#masraflar_listesi').append(result.kasa_raporu.masraflar);
                    }
                    if($('#masraf_tablo').length)
                    {
                        $('#masraf_tablo').DataTable().destroy();
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
                      data: result.masraflar,
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
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
        });
    }
});
$('#alacak_formu').on('submit',function(e){
    e.preventDefault();
     $.ajax({
                type: "POST",
                url: '/isletmeyonetim/alacakekleduzenle',
                dataType: "text",
                data : $('#alacak_formu').serialize(),
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                $('#preloader').hide();
                     swal(
                {
                    type: "success",
                    title: "Başarılı",
                    html:  "<p>Bilgiler başarıyla kaydedildi</p><p><a href='/isletmeyonetim/alacaklar' class='btn btn-primary btn-lg btn-block'>Alacak Listeme Git</a>",
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                }
                    );
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
        });
});
if(($('#formdoldurma').length>0 && $('#formdoldurma').val()!= '1')||$('#formdoldurma').length==0)
    setInterval(bildirimKontrol, 10000);
function bildirimKontrol() {
    $(".notification-list.customscroll").first().mCustomScrollbar();
    $.ajax({
                type: "GET",
                url: '/isletmeyonetim/bildirimkontrolet',
                dataType: "json",
                data:{sube:$('input[name="sube"]').val()},
               success: function(result)  {
                    $(".notification-list.customscroll").first().mCustomScrollbar("destroy");
                    if(result.bildirim_sayisi!=0)
                    {
                        $('#bildirim-badge').addClass('badge notification-afctive');
                        $('#bildirim-badge').empty();
                        $('#bildirim-badge').append(result.bildirim_sayisi);
                        $('#bildirim_listesi').empty();
                        $('#bildirim_listesi').append(result.bildirimler);
                    }
                    else
                    {
                        $('#bildirim-badge').removeClass('badge notification-afctive');
                        $('#bildirim-badge').empty();
                    }
                },
                complete: function () {
                    jQuery(".notification-list.customscroll").first().mCustomScrollbar({
                        theme: "dark-2",
                        scrollInertia: 300,
                        autoExpandScrollbar: true,
                        advanced: { autoExpandHorizontalScroll: true },
                    });
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                    if(request.status==401 && window.location.href.indexOf("/isletmeyonetim") !== -1)
                        window.location.href = '/isletmeyonetim/girisyap';
                    else
                         document.getElementById('hata').innerHTML = request.responseText;
                }
    });
}
$(document).on('click','a[name="bildirim"]', function(e){
    e.preventDefault();
    /*var bildirimid = $(this).attr('data-value');
    $.ajax({
                type: "POST",
                url: '/isletmeyonetim/bildirimokundu',
                dataType: "text",
                data : {_token: $('input[name="_token"]').val(),bildirim_id:bildirimid},
                success: function(result)  {
                   window.location.href = result;
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML = request.responseText;
                }
    });
     e.preventDefault();*/
    var randevu_id = $(this).attr('data-value');
     var arsivid = $(this).attr('data-value');
    var bildirim_id =$(this).attr('data-index-number');
    var href = $(this).attr('href');
    if(href.indexOf("#not-") >= 0)
    {
        url = href.split('-');
        $.ajax({
            type: "GET",
            dataType: "json",
            url: '/isletmeyonetim/ajandadetaygetir',
            data: {ajandaid:url[1],bildirimid:bildirim_id,sube:$('input[name="sube"]').val()},
            beforeSend: function(){
                $('#preloader').show();
            },
            success: function(result)  {
                  $('#preloader').hide();
                bildirimKontrol();
                jQuery(".event-title").html(result.baslik);
                jQuery(".event-body").html("<div class='row' ><b style='margin-left:20px;'>İçerik :</b> <p style='margin-left:20px;'>"+result.icerik+"</p></div> <div class='row' ><b style='margin-left:20px;'>Tarih :</b> <p style='margin-left:23px;'>"+result.tarih+"</p></div> </div> <div class='row' ><b style='margin-left:20px;'>Saat :</b> <p style='margin-left:30px;'>"+result.saat+"</p></div>");
                jQuery(".event-buttons").html('<div class="modal-footer" style="justify-content: center;">'+
                   '<div class="col-md-6 col-xs-6 col-6 col-sm-6">'+
                         '<button data-toggle="modal" class="btn btn-success btn-block" data-value="'+result.id+'" data-target="#ajanda_duzenle_modal" name="ajanda_notu_duzenle">Düzenle</button>'+
                   '</div>'+
                '</div>');
                jQuery("#ajandadetayigetir").trigger('click');
            },
            error: function (request, status, error) {
                 $('#preloader').hide();
                 document.getElementById('hata').innerHTML = request.responseText;
            }
        });
    }
    else if(href.indexOf("#form-") >= 0){
        url = href.split('-');
         $.ajax({
        type: "GET",
        url: '/isletmeyonetim/formyazdir',
        dataType: "text",
        data : {arsiv_id:url[1],bildirimid:bildirim_id,sube:$('input[name="sube"]').val()},
        headers: {
             'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            $('#yazdirilacak').empty()
            $('#yazdirilacak').append(result);
            $('#yazdirilacak div').each(function(){
               // $(this).removeAttr('style');
            });
            var originalContents = $("body").html();
            var printContents = $("#yazdirilacak").html();
            var myStyle = '<link rel="stylesheet" href="public/yeni_panel/vendors/styles/style.css" />';
            var content = '<div style="padding: 20px;">';
            var myStyle2 = '<link rel="stylesheet" href="public/yeni_panel/src/plugins/datatables/css/responsive.bootstrap4.min.css" />'
             myWindow = window.open('https://app.randevumcepte.com.tr/'+$(this).attr('data-value'));
             myWindow.document.write(myStyle + myStyle2 + printContents + content);
             myWindow.print();
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
        });
    }
    else
    {
        $.ajax({
            type: "GET",
            dataType: "json",
            url: '/isletmeyonetim/randevugetir',
            data: {randevuid:randevu_id,bildirim_okundu:true,bildirimid:bildirim_id,sube:$('input[name="sube"]').val()},
            beforeSend: function(){
                $('#preloader').show();
            },
            success: function(result)  {
                bildirimKontrol();
                $('.event-body').empty();
                $('.event-body').append(result.randevu_icerik);
                $('.event-title').empty();
                $(".event-title").append(result.ad_soyad+" Randevu Detayı");
                $('.event-buttons').empty();
                $('.event-buttons').append(result.butonlar);
                $('#preloader').hide();
                $('#randevudetayigetir').trigger('click');
            },
            error: function (request, status, error) {
                 $('#preloader').hide();
                 document.getElementById('hata').innerHTML = request.responseText;
            }
        });
    }
});
function randevufiltre()
{
    $.ajax({
                type: "GET",
                url: '/isletmeyonetim/randevulistefiltre',
                dataType: "json",
                data : {olusturulma : $('#olusturulmaya_gore_filtre').val(),durum : $('#duruma_gore_filtre').val(),zaman : $('#zamana_gore_filtre').val(),ozeltarih:$('#tarihe_gore_filtre').val(),salon_id:$('input[name="sube"]').val()},
                beforeSend:function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                    $('#preloader').hide();
                   $('#randevu_liste').DataTable().destroy();
                    $('#randevu_liste').DataTable({
                        autoWidth: false,
                        responsive: true,
                        "order": [[ 4, "desc" ]],
                        columns:[
                           { data: 'musteri'   },
                           { data: 'telefon' },
                           { data: 'hizmetler'   },
                            { data: 'personelcihazoda'   },
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
$('#olusturulmaya_gore_filtre,#duruma_gore_filtre').change(function(e){
    e.preventDefault();
    if($('#zamana_gore_filtre').val()!="ozel")
        randevufiltre();
    else if($('#zamana_gore_filtre').val()=="ozel" && $('#tarihe_gore_filtre').val()!= '')
        randevufiltre();
    else{
        swal(
                            {
                                type: "warning",
                                title: "Uyarı",
                                text:  "Lütfen tarih aralığı giriniz!",
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
        );
    }
});
$('#zamana_gore_filtre').change(function(e){
    e.preventDefault();
    if($('#zamana_gore_filtre').val()=="ozel")
        $('#tarihe_gore_filtre').attr('style','display:block');
    else
    {
        $('#tarihe_gore_filtre').val('');
        $('#tarihe_gore_filtre').attr('style','display:none');
        randevufiltre();
    }
});

$('#hizmet_rapor_zamana_gore_filtre').change(function(e){
    e.preventDefault();
    if($('#hizmet_rapor_zamana_gore_filtre').val()=="ozel"){
        $('#hizmet_rapor_ozel_tarih_filtresi_1').attr('style','display:block');
        $('#hizmet_rapor_ozel_tarih_filtresi_2').attr('style','display:block');

    }
    else
    {
        $('#hizmet_rapor_baslangic_tarihi').val('');
        $('#hizmet_rapor_bitis_tarihi').val('');
        $('#hizmet_rapor_ozel_tarih_filtresi_1').attr('style','display:none');
        $('#hizmet_rapor_ozel_tarih_filtresi_2').attr('style','display:none');
        hizmetRaporFiltre('','');
    }
});

$('#urun_rapor_zamana_gore_filtre').change(function(e){
    e.preventDefault();
    if($('#urun_rapor_zamana_gore_filtre').val()=="ozel"){
        $('#urun_rapor_ozel_tarih_filtresi_1').attr('style','display:block');
        $('#urun_rapor_ozel_tarih_filtresi_2').attr('style','display:block');

    }
    else
    {
        $('#urun_rapor_baslangic_tarihi').val('');
        $('#urun_rapor_bitis_tarihi').val('');
        $('#urun_rapor_ozel_tarih_filtresi_1').attr('style','display:none');
        $('#urun_rapor_ozel_tarih_filtresi_2').attr('style','display:none');
        urunRaporFiltre('','');
    }
});
$('#paket_rapor_zamana_gore_filtre').change(function(e){
    e.preventDefault();
    if($('#paket_rapor_zamana_gore_filtre').val()=="ozel"){
        $('#paket_rapor_ozel_tarih_filtresi_1').attr('style','display:block');
        $('#paket_rapor_ozel_tarih_filtresi_2').attr('style','display:block');

    }
    else
    {
        $('#paket_rapor_baslangic_tarihi').val('');
        $('#paket_rapor_bitis_tarihi').val('');
        $('#paket_rapor_ozel_tarih_filtresi_1').attr('style','display:none');
        $('#paket_rapor_ozel_tarih_filtresi_2').attr('style','display:none');
        paketRaporFiltre('','');
    }
});
$('#personel_rapor_zamana_gore_filtre').change(function(e){
    e.preventDefault();
    if($('#personel_rapor_zamana_gore_filtre').val()=="ozel"){
        $('#personel_rapor_ozel_tarih_filtresi_1').attr('style','display:block');
        $('#personel_rapor_ozel_tarih_filtresi_2').attr('style','display:block');

    }
    else
    {
        $('#personel_rapor_baslangic_tarihi').val('');
        $('#personel_rapor_bitis_tarihi').val('');
        $('#personel_rapor_ozel_tarih_filtresi_1').attr('style','display:none');
        $('#personel_rapor_ozel_tarih_filtresi_2').attr('style','display:none');
        personelRaporFiltre('','');
    }
});

$('#hizmet_rapor_baslangic_tarihi,#hizmet_rapor_bitis_tarihi').on('paste keyup keydown change',function(e){
    e.preventDefault();
    hizmetRaporFiltre('','');
});
$('#urun_rapor_baslangic_tarihi,#urun_rapor_bitis_tarihi').on('paste keyup keydown change',function(e){
    e.preventDefault();
    urunRaporFiltre('','');
});
$('#paket_rapor_baslangic_tarihi,#paket_rapor_bitis_tarihi').on('paste keyup keydown change',function(e){
    e.preventDefault();
    paketRaporFiltre('','');
});
$('#personel_rapor_baslangic_tarihi,#personel_rapor_bitis_tarihi').on('paste keyup keydown change',function(e){
    e.preventDefault();
    personelRaporFiltre('','');
});


$('#tarihe_gore_filtre').on('paste keyup keydown change',function(e){
    e.preventDefault();
    randevufiltre();
});

$('#hizmetRaporPersonelFiltre').change(function(e){
    hizmetRaporFiltre($('#hizmet_rapor_baslangic_tarihi').val(),$('#hizmet_rapor_bitis_tarihi').val());
});
$('#urunRaporPersonelFiltre').change(function(e){
    urunRaporFiltre($('#urun_rapor_baslangic_tarihi').val(),$('#urun_rapor_bitis_tarihi').val());
});
$('#paketRaporPersonelFiltre').change(function(e){
    paketRaporFiltre($('#paket_rapor_baslangic_tarihi').val(),$('#paket_rapor_bitis_tarihi').val());
});
function hizmetRaporFiltre(baslangic_tarihi,bitis_tarihi)
{
    $.ajax({
                type: "GET",
                url: '/isletmeyonetim/hizmetRaporFiltre',
                dataType: "json",
                data : {salonId : $('input[name="sube"]').val(),baslangicTarihi : baslangic_tarihi,bitisTarihi: bitis_tarihi,zaman:$('#hizmet_rapor_zamana_gore_filtre').val(),personel:$('#hizmetRaporPersonelFiltre').val()},
                beforeSend:function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                    $('#preloader').hide();
                    var toplamTutar = 0;
                    var toplamKAzanc = 0;
                    var toplamBorc = 0;

                    result.forEach(function(item){
                         
                        //console.log('toplam tutar '+item.toplamTutarNumeric);
                        toplamTutar += item.toplamTutarNumeric;
                        toplamKAzanc += item.toplamKazancNumeric;
                        toplamBorc  += item.borcNumeric;
                        console.log('toplam tutar '+toplamTutar);
                    });
                    $('#hizmetGeliri').empty();
                    $('#hizmetKazanci').empty();
                    $('#hizmetBorc').empty();

                    $('#hizmetGeliri').append(turkLiraFormat(toplamTutar));
                    $('#hizmetKazanci').append(turkLiraFormat(toplamKAzanc));
                    $('#hizmetBorc').append(turkLiraFormat(toplamBorc));

                    $('#hizmet_rapor_tablo').DataTable().destroy();
                    $('#hizmet_rapor_tablo').DataTable({
                        autoWidth: false,
                        responsive: true,
                         pageLength: 100, 
                        "order": [[ 0, "asc" ]],
                        columns:[
                           { data: 'hizmet_adi'   },
                           { data: 'adet' },
                           { data: 'toplam_tutar'   },
                           { data: 'toplamKazanc'   },
                           { data: 'borc' },
                           { data: 'islemler' },
                          
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

function urunRaporFiltre(baslangic_tarihi,bitis_tarihi)
{
    $.ajax({
                type: "GET",
                url: '/isletmeyonetim/urunRaporFiltre',
                dataType: "json",
                data : {salonId : $('input[name="sube"]').val(),baslangicTarihi : baslangic_tarihi,bitisTarihi: bitis_tarihi,zaman:$('#urun_rapor_zamana_gore_filtre').val(),personel:$('#urunRaporPersonelFiltre').val()},
                beforeSend:function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                    $('#preloader').hide();
                     var toplamTutar = 0;
                    var toplamKAzanc = 0;
                    var toplamBorc = 0;

                    result.forEach(function(item){
                         
                        console.log('toplam tutar '+item.toplamTutarNumeric);
                        toplamTutar += item.toplamTutarNumeric;
                        toplamKAzanc += item.toplamKazancNumeric;
                        toplamBorc  += item.borcNumeric;
                    });
                    $('#urunGeliri').empty();
                    $('#urunKazanci').empty();
                    $('#urunBorc').empty();

                    $('#urunGeliri').append(turkLiraFormat(toplamTutar));
                    $('#urunKazanci').append(turkLiraFormat(toplamKAzanc));
                    $('#urunBorc').append(turkLiraFormat(toplamBorc));
                    $('#urun_rapor_tablo').DataTable().destroy();
                    $('#urun_rapor_tablo').DataTable({
                        autoWidth: false,
                        responsive: true,
                         pageLength: 100, 
                        "order": [[ 0, "asc" ]],
                        columns:[
                           { data: 'urun_adi'   },
                           { data: 'adet' },
                           { data: 'toplam_tutar'   },
                           { data: 'toplamKazanc'   },
                           { data: 'borc' },
                           { data: 'islemler' },
                          
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

function paketRaporFiltre(baslangic_tarihi,bitis_tarihi)
{
    $.ajax({
                type: "GET",
                url: '/isletmeyonetim/paketRaporFiltre',
                dataType: "json",
                data : {salonId : $('input[name="sube"]').val(),baslangicTarihi : baslangic_tarihi,bitisTarihi: bitis_tarihi,zaman:$('#paket_rapor_zamana_gore_filtre').val(),personel:$('#paketRaporPersonelFiltre').val()},
                beforeSend:function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                    $('#preloader').hide();

                    var toplamTutar = 0;
                    var toplamKAzanc = 0;
                    var toplamBorc = 0;

                    result.forEach(function(item){
                         
                        console.log('toplam tutar '+item.toplamTutarNumeric);
                        toplamTutar += item.toplamTutarNumeric;
                        toplamKAzanc += item.toplamKazancNumeric;
                        toplamBorc  += item.borcNumeric;
                    });
                    $('#paketGeliri').empty();
                    $('#paketKazanci').empty();
                    $('#paketBorc').empty();

                    $('#paketGeliri').append(turkLiraFormat(toplamTutar));
                    $('#paketKazanci').append(turkLiraFormat(toplamKAzanc));
                    $('#paketBorc').append(turkLiraFormat(toplamBorc));

                    $('#paket_rapor_tablo').DataTable().destroy();
                    $('#paket_rapor_tablo').DataTable({
                        autoWidth: false,
                        responsive: true,
                         pageLength: 100, 
                        "order": [[ 0, "asc" ]],
                        columns:[
                           { data: 'paket_adi'   },
                           { data: 'adet' },
                           { data: 'toplam_tutar'   },
                           { data: 'toplamKazanc'   },
                           { data: 'borc' },
                           { data: 'islemler' },
                          
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

function personelRaporFiltre(baslangic_tarihi,bitis_tarihi)
{
    $.ajax({
                type: "GET",
                url: '/isletmeyonetim/personelRaporFiltre',
                dataType: "json",
                data : {salonId : $('input[name="sube"]').val(),baslangicTarihi : baslangic_tarihi,bitisTarihi: bitis_tarihi,zaman:$('#personel_rapor_zamana_gore_filtre').val()},
                beforeSend:function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                    $('#preloader').hide();
                    $('#personel_rapor_tablo').DataTable().destroy();
                    $('#personel_rapor_tablo').DataTable({
                        autoWidth: false,
                        responsive: true,
                         pageLength: 100, 
                        "order": [[ 0, "asc" ]],
                        columns:[
                           { data: 'personel_adi'   },
                           { data: 'hizmet_geliri' },
                           { data: 'hizmet_primi'   },
                           { data: 'urun_geliri'   },
                           { data: 'urun_primi' },
                           { data: 'paket_geliri' },
                           { data: 'paket_primi' },
                          
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



function takvimiyenidenyukle(takvimverisi)
{
}
$('#musteri_arama,#sube_arama').change(function(e){
    e.preventDefault();
    if($(this).val()!=0)
        window.location.href = $(this).val();
});
$('#sube_formu').on('submit',function(e){
    e.preventDefault();
    var warningtext = "";
    if($('#firma_il').val()=="")
        warningtext += "- Lütfen eklemek istediğiniz şubenin/işletmenin ilini seçiniz!";
    if($('#firma_ilce').val()=="")
        warningtext += "<br>- Lütfen eklemek istediğiniz şubenin/işletmenin ilçesini seçiniz!";
    if(warningtext != "")
    {
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
        $.ajax({
                type: "POST",
                url: '/isletmeyonetim/yeniisletmeekle',
                dataType: "json",
                data:$('#sube_formu').serialize(),
                beforeSend:function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                    swal(
                        {
                            type: "success",
                            title: "Başarılı",
                            text:  result.mesaj,
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                        }
                    );
                    window.location.href = '/isletmeyonetim/uyelik?sube='+$('input[name="sube"]').val()+'&yenisube='+result.sube
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
    });
    }
});
$(document).on('click','.swal_kapat',function(e){
    swal.close();
});
$('#hizmet_personel_ekleme_butonu').click(function(){
        var checkboxes = document.getElementsByName('salon_hizmetleri[]');
        var checkboxesChecked = [];
      for (var i=0; i<checkboxes.length; i++) {
         if (checkboxes[i].checked) {
              checkboxesChecked.push(checkboxes[i]);
         }
     }
        if(checkboxesChecked.length == 0){
                        swal(
                            {
                                type: "warning",
                                title: "Uyarı",
                                text:  'Devam etmek için lütfen en az bir hizmet seçiniz',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
                        );
        }
        else{
            $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/hizmetpersonelsecimigetir',
                    data: $('#hizmet_ekle_formu').serialize(),
                    dataType: "text",
                    beforeSend:function(){
                        $('#preloader').show();
                    },
                    success: function(result)  {
                        $('#preloader').hide();
                          $('#hizmet_secimi_modal').modal('hide');
                        $('#hizmet_personel_sec_bolumu').empty();
                        $('#hizmet_personel_sec_bolumu').append(result);
                         $(".custom-select2").each(function(i){
                            $(this).removeAttr('data-select2-id').removeAttr('id');
                            $(this).find('option').removeAttr('data-select2-id');
                            $(this).select2({
                                placeholder: "Personel Seçiniz..."
                            }
                            );
                        });
                         $('#hizmet_personel_ekle_modal_ac').trigger('click');
                    },
                    error: function (request, status, error) {
                        $('#preloader').hide();
                         document.getElementById('hata').innerHTML = request.responseText;
                    }
            });
        }
});
$('#hizmet_personel_formu').on('submit',function (e) {
    e.preventDefault();
     $.ajax({
        type: "POST",
        url: '/isletmeyonetim/hizmetekleduzenle',
        data:  $('#hizmet_personel_formu').serialize(),
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
          beforeSend:function(){
                        $('#preloader').show();
                    },
       success: function(result)  {
          $('#preloader').hide();
          $('#personel_sec_modal').modal('hide');
             $('.modal_kapat').trigger('click');
            swal(
                            {
                                type: "success",
                                title: "Başarılı",
                                text:  result.status,
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
            );
            if($('#hizmet_liste').length)
            {
               $('#hizmet_liste').DataTable().destroy();
                $('#hizmet_liste').DataTable({
                           autoWidth:false,
                           responsive:true,
                        columns:[
                            { data: 'hizmet_adi' },
                            { data: 'personel' },
                            { data: 'islemler' },
                        ],
                        data: result.hizmet_liste,
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
            $('#secilmeyen_hizmetler_liste').empty();
            $('#secilmeyen_hizmetler_liste').append(result.secilmeyen_hizmetler);
        },
        error: function (request, status, error) {
            $('#preloader').hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('click','a[name="hizmet_duzenle"]',function (e) {
    e.preventDefault();
    var sunulan_hizmet_id = $(this).attr('data-value');
    $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/hizmetpersonelsecimigetir',
                    data: {sunulanhizmetid:sunulan_hizmet_id,sube:$('input[name="sube"]').val()},
                    dataType: "text",
                    beforeSend:function(){
                        $('#preloader').show();
                    },
                    success: function(result)  {
                        $('#preloader').hide();
                        $('#hizmet_personel_sec_bolumu').empty();
                        $('#hizmet_personel_sec_bolumu').append(result);
                         $(".custom-select2").each(function(i){
                            $(this).removeAttr('data-select2-id').removeAttr('id');
                            $(this).find('option').removeAttr('data-select2-id');
                            $(this).select2({
                                placeholder: "Personel Seçiniz..."
                            }
                            );
                        });
                         $('#hizmet_personel_ekle_modal_ac').trigger('click');
                    },
                    error: function (request, status, error) {
                        $('#preloader').hide();
                         document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});

$('#personel_tablo').on('click','button[name="personel_siralamayi_bir_asagi_tasi"]',function(e){
     $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/personelSiralamaArtir',
                    data: {personelid:$(this).attr('data-value'),sube:$('input[name="sube"]').val(),siraNo:$(this).attr('data-index-number')},
                    dataType: "json",
                    beforeSend:function(){
                        
                    },
                    success: function(result)  {
                        
                        $('#personel_tablo').DataTable().destroy();
                        $('#personel_tablo').DataTable({
                            ordering: false,
                            paging: false,
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
                                {data : 'siralama', className: "text-center"},
                                {data:'ad_soyad'},
                                { data: 'hesap_turu'   },
                                { data: 'telefon' },
                                { data: 'durum'},
                                { data: 'islemler' },
                              ],
                              data: result,
                        });
                         
                    },
                    error: function (request, status, error) {
                        $('#preloader').hide();
                         document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$('#personel_tablo').on('click','button[name="personel_siralamayi_bir_yukari_tasi"]',function(e){
     $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/personelSiralamaAzalt',
                    data: {personelid:$(this).attr('data-value'),sube:$('input[name="sube"]').val(),siraNo:$(this).attr('data-index-number')},
                    dataType: "json",
                    beforeSend:function(){
                         
                    },
                    success: function(result)  {
                        
                        $('#personel_tablo').DataTable().destroy();
                    $('#personel_tablo').DataTable({
                        ordering: false,
                        paging: false,
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
                            {data : 'siralama', className: "text-center"},
                            {data:'ad_soyad'},
                            { data: 'hesap_turu'   },
                            { data: 'telefon' },
                            { data: 'durum'},
                            { data: 'islemler' },
                          ],
                          data: result,
                    });
                         
                    },
                    error: function (request, status, error) {
                        $('#preloader').hide();
                         document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});


$('#personel_tablo').on('click','a[name="personel_detayi"]',function(){
     $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/personeldetaygetir',
                    data: {personelid:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
                    dataType: "json",
                    beforeSend:function(){
                        $('#preloader').show();
                    },
                    success: function(result)  {
                        $('#preloader').hide();
                        $('#personel_id').val(result.personelbilgi.id);
                        $('#personel_adi').val(result.personelbilgi.personel_adi);
                        $('#cinsiyet').val(result.personelbilgi.cinsiyet);
                        $('#cep_telefon').val(result.cep_telefon);
                        $('#unvan').val(result.personelbilgi.unvan);
                        $('#hizmet_prim_yuzde').val(result.personelbilgi.hizmet_prim_yuzde);
                        $('#urun_prim_yuzde').val(result.personelbilgi.urun_prim_yuzde);
                        $('#paket_prim_yuzde').val(result.personelbilgi.paket_prim_yuzde);
                        $('#personel_maas').val(result.personelbilgi.maas);
                        if(result.hesapturu=='Hesap Sahibi'){
                            $('#sistem_yetki').prop('disabled',true);
                        }
                        else{
                            $('#sistem_yetki').prop('disabled',false);
                        }
                        $('#sistem_yetki').val(result.hesapturu);
                        $.each(result.calismasaatleri, function( key, value ) {
                            ++key;
                            if(value.calisiyor==1)
                                $('#personelcalisiyor'+key).prop('checked', 'checked');
                            else
                                $('#personelcalisiyor'+key).prop('checked', false);
                            $('#personelbaslangicsaati'+key).val(value.baslangic_saati);
                            $('#personelbitissaati'+key).val(value.bitis_saati);
                        });
                        $.each(result.molasaatleri, function( key, value ) {
                            ++key;
                            if(value.mola_var==1)
                                $('#personelmolavar'+key).prop('checked', 'checked');
                            else
                                $('#personelmolavar'+key).prop('checked', false);
                            $('#personelmolabaslangicsaati'+key).val(value.baslangic_saati);
                            $('#personelmolabitissaati'+key).val(value.bitis_saati);
                        });
                    },
                    error: function (request, status, error) {
                        $('#preloader').hide();
                         document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$(document).on('click','a[name="hizmet_sil"]',function(e){
    e.preventDefault();
    var sunulanhizmetid = $(this).attr('data-value');
     swal({
        title: "Emin misiniz? Bu işlem geri alınamaz!",
        text: "Seçilen hizmet(-ler)i silmeniz durumunda adisyon yönetiminde işlem gerçekleştiremeyecek, randevu alırken/tanımlarken bir daha bu hizmetler üzerinden randevu alamayacaksınız. Bu hizmet(-ler)e ait verileriniz korunacaktır.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Sil',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then((isConfirm) => {
        if (isConfirm.value) {
             $.ajax({
            type: "POST",
            url: '/isletmeyonetim/salonhizmetsil',
             data: {sube:$('input[name="sube"]').val(), sunulan_hizmet_id:sunulanhizmetid,_token:$('input[name="_token"]').val()} ,
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN':$('input[name="_token"]').val()
            },
            beforeSend: function(){
                $('#preloader').show();
            },
           success: function(result)  {
                $('#preloader').hide();
                swal({
                    type: "success",
                    title: "Başarılı",
                    text:  result.status,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                });
                $('#hizmet_liste').DataTable().destroy();
                $('#hizmet_liste').DataTable({
                           autoWidth:false,
                           responsive:true,
                        columns:[
                            { data: 'hizmet_adi' },
                            { data: 'personel' },
                            { data: 'islemler' },
                        ],
                        data: result.hizmet_liste,
                        "language" : {
                            "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                            searchPlaceholder: "Ara",
                            paginate: {
                                next: '<i class="ion-chevron-right"></i>',
                                previous: '<i class="ion-chevron-left"></i>'
                            }
                        },
                });
                $('#secilmeyen_hizmetler_liste').empty();
                $('#secilmeyen_hizmetler_liste').append(result.secilmeyen_hizmetler);
                $('button[data-dismiss="modal"]').trigger('click');
            },
            error: function (request, status, error) {
                 document.getElementById('hata').innerHTML =request.responseText;
                 $('#preloader').hide();
            }
        });
        }
     });
});
$('#yeni_hizmet_formu').on('submit',function(e){
    e.preventDefault();
     $.ajax({
        type: "POST",
        url: '/isletmeyonetim/sistemeyenihizmetekle',
        data: $('#yeni_hizmet_formu').serialize(),
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
          beforeSend: function(){
                $('#preloader').show();
            },
        success: function(result)  {
            $('#preloader').hide();
            $('#yeni_hizmet_formu').trigger('reset');
            $('#yeni_hizmet_modal').modal('hide');
             $('#hizmet_secimi_modal').modal('hide');
                swal({
                    type: "success",
                    title: "Başarılı",
                    text:  result.status,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                });
                 $('#hizmet_liste').DataTable().destroy();
                $('#hizmet_liste').DataTable({
                           autoWidth:false,
                           responsive:true,
                        columns:[
                            { data: 'hizmet_adi' },
                            { data: 'personel' },
                            { data: 'islemler' },
                        ],
                        data: result.hizmet_liste,
                        "language" : {
                            "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                            searchPlaceholder: "Ara",
                            paginate: {
                                next: '<i class="ion-chevron-right"></i>',
                                previous: '<i class="ion-chevron-left"></i>'
                            }
                        },
                });
                 $('#secilmeyen_hizmetler_liste').empty();
                 $('#secilmeyen_hizmetler_liste').append(result.secilmeyen_hizmetler);
                $('button[data-dismiss="modal"]').trigger('click');
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML ='hata : '+error+' '+request.responseText;
        }
    });
});
$('#randevu_ayarina_gore').change(function(e){
    e.preventDefault();
    takvimyukle(true,true);
});
if($('#calendar').length){
    interval = setInterval(takvimyukle.bind(false,false), 10000);
}
if($('randevu_liste').length){setInterval(randevufiltre,10000);}
function takvimyukle(preload,turdegisti)
{

    console.log("takvim yükleniyor / güncelleniyor");
     var curview = $('#calendar').fullCalendar('getView');
     var moment = '';
     if($('#takvim_tarihe_gore').val()!= '')
        moment = $('#takvim_tarihe_gore').val();
     else{
         moment = $('#calendar').fullCalendar('getDate');
         moment = moment.format();
     }
     //alert("The current date of the calendar is " + moment.format()+' - '+new Date().format());
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/randevuyukle',
        data: {ayar:$('#randevu_ayarina_gore').val(),sube:$('input[name="sube"]').val(),takvimtarih:moment,takvimgorunum:curview.type},
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        beforeSend: function(){
            if(preload)
                $('#preloader').show();
        },
        success: function(result)  {
            console.log("takvim verisi getirme işi bitti, yükleniyor");
             var countText = "";
             switch(curview.name) {
                    case 'agendaDay':
                        countText = "Günlük Randevu: " + result.randevu_sayisi;
                        break;
                    case 'agendaWeek':
                        case 'basicWeek':
                        case 'week':
                        case 'listWeek':
                        case 'timelineWeek':
                            countText = "Haftalık Randevu: " + result.randevu_sayisi;
                            break;
                    case 'month':
                    case 'basicMonth':
                    case 'listMonth':
                    case 'timelineMonth':
                        countText = "Aylık Randevu: " + result.randevu_sayisi;
                        break;
                    default:
                        countText = "Günlük Randevu: " + result.randevu_sayisi;
                }
                console.log("Randevu sayısı "+countText);
                $('.randevu-count-button').text(countText);

            if(preload){
                $('#preloader').hide();
            }
            if(turdegisti)
            {
                  

                var calendarHeight = '';
                 if ($(window).width() < 1300) { 
                    // Mobil cihaz
                      calendarHeight = $(window).height() - 110;
                } else {
                    // Masaüstü
                    calendarHeight = $(window).height() - 310;
                }  
             
                
                $('#calendar').fullCalendar('destroy');
                $('#calendar').fullCalendar({
                    titleFormat: 'D MMMM YYYY dddd',  
                     firstDay: 1, 
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
              defaultView: curview.type,
              defaultDate: moment,
              editable: true,
              selectable: true,
              eventLimit: true, // allow "more" link when too many events
              header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
              },
              //// uncomment this line to hide the all-day slot
              allDaySlot: false,
              minTime: result.baslangic,
              maxTime: result.bitis,
              slotDuration: '00:15:00',
              slotLabelInterval: '00:15:00',
             slotLabelFormat: 'H:mm',
              height: calendarHeight,
              resources: result.resource,
              events: result.randevu,
              timeFormat: 'H:mm',
              views: {
                  agenda: {
                      slotLabelFormat: 'H:mm',
                  }
              },
              businessHours : result.calismasaatleri,
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
              dayClick: function (start,end,jsEvent,view, resource) {
                var tarihsaattext = start.format().split("T");
               
               
                   
           
                   $('#randevutarihiyeni').val(tarihsaattext[0]);
             
                   console.log('Resource:', resource);
                     console.log('Resource ID:', resource ? resource.id : "Tanımsız");
                     console.log('Resource Title:', resource ? resource.title : "Tanımsız");
                    
             
                   jQuery("#modal-view-event-add").modal();
                  $('#modal-view-event-add').on('click', function (e) {
    // Eğer modal-content'in dışına tıklandıysa (arka plan mesela)
    if (!$(e.target).closest('.modal-content').length) {
      
        // Event propagation'ı durdur
        e.stopPropagation();
        e.preventDefault();    // Formun submit olmasını engelle
        // Modal'ı kapat
         
        // Formu sıfırla
        $('#yenirandevuekleform')[0].reset();

        // Hizmetler bölümünü temizle
        $('.hizmetler_bolumu div').each(function () {
            if ($(this).attr('data-value') > 0) {
                $(this).remove();
            }
        });
        $()
        // Select2 veya diğer seçim kutularını sıfırla
        $('#yenirandevuekleform select').each(function () {
            $(this).val(null).trigger('change');
        });
    }
});

             
             
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
             eventRender: function(event, element) {
                   
                    // Arkaplan etkinlikleri tıklanabilir hale getirme
                    if (event.title === 'Boş slot') {
                        event.editable = false; 
                        event.className += " disabled-event"; 
                    }

                },

             eventClick: function (event, jsEvent, view) {
                
         
                
                if (event.title === 'Boş slot') {
                    // Burada istediğiniz işlemi yapabilirsiniz, örneğin slotu seçmek
                    console.log('Boş slot tıklandı Resource id: ' + event.resourceId + " "+event.resourceTitle);
                    console.log('Boş slot tıklandı Resource id: ' + event.personelId + " "+event.personelAdi);
                    var tarihsaattext = event.start.format().split("T");
                    

                 
                  
                     if(new Date(event.start.format()) < new Date())
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
                      $('#randevu_saat').val(tarihsaattext[1])
                      var seciliOption = new Option(event.resourceTitle, event.resourceId, true, true);

                      if($('#randevu_ayarina_gore').val()==1){
                            $('select[name="randevupersonelleriyeni[]"]').append(seciliOption).trigger('change');
                            if(event.odaId != ""){
                                var odaOption = new Option(event.odaAdi,event.odaId,true,true);
                                $('select[name="randevuodalariyeni[]"]').append(odaOption).trigger('change');
                            }
                             else
                                $('select[name="randevuodalariyeni[]"]').val(null).trigger('change');

                      }
                       if($('#randevu_ayarina_gore').val()==2)
                            $('select[name="randevucihazlariyeni[]"]').append(seciliOption).trigger('change');
                       if($('#randevu_ayarina_gore').val()==3){
                            $('select[name="randevuodalariyeni[]"]').append(seciliOption).trigger('change');
                        if(event.personelId != ""){
                             var personelOption = new Option(event.personelAdi,event.personelId,true,true);
                                $('select[name="randevupersonelleriyeni[]"]').append(personelOption).trigger('change');
                        }
                        else
                                $('select[name="randevupersonelleriyeni[]"]').val(null).trigger('change');
                       }
                      //randevusaatlerinigetir(tarihsaattext[0],$('input[name="sube"]').val(),tarihsaattext[1]);

                      // Saat kapama sekmesi icin tarih, saat ve personel doldur
                      $('#saat_kapama input[name="tarih"]').val(tarihsaattext[0]);
                      $('#kapama_saat_baslangic').val(tarihsaattext[1]);

                      if($('#randevu_ayarina_gore').val()==1){
                          var kapamaPersonelOption = new Option(event.resourceTitle, event.resourceId, true, true);
                          $('#saat_kapama select[name="personel"]').empty().append('<option></option>').append(kapamaPersonelOption).trigger('change');
                      }
                      if($('#randevu_ayarina_gore').val()==3 && event.personelId != ""){
                          var kapamaPersonelOption = new Option(event.personelAdi, event.personelId, true, true);
                          $('#saat_kapama select[name="personel"]').empty().append('<option></option>').append(kapamaPersonelOption).trigger('change');
                      }

                      jQuery("#modal-view-event-add").modal();
                   }
                }
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
               if(event.title !== 'Boş slot'){
                var randevuid = event.randevu_id;
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
if (preload && !turdegisti) {
    $('html, body').animate({
        scrollTop: $('tr[data-time="'+result.baslangic+'"]').offset().top
    }, 'slow');
}
            $('.fc-header-toolbar button').click(function(){
                   $('#takvim_tarihe_gore').val('');
                   var view = $('#calendar').fullCalendar('getView');
                   $('.fc-axis.fc-widget-header').attr('style','width:43px');
                   if(view.type=='agendaDay'){
                      $.each(result.resource,function(key,item) {
                           var child = parseInt(key)+parseInt(2);
                             console.log(child);
                         $('.fc th:nth-child('+child+'n)').css({'background':item.bgcolor,'color':'#fff'});
                      });
                   }
            });
           
            $('.fc-axis.fc-widget-header').attr('style','width:43px');
            }
            else{
                console.log("takvim türü değişmedi sadece güncel veriler geliyor");
                $('#calendar').fullCalendar('removeEvents');
                $('#calendar').fullCalendar('addEventSource', result.randevu);
                $('#calendar').fullCalendar('refetchEvents');
            }
            if($('.fc-resource-cell').width()<80)
            {
               //$('.fc-view-container').attr('style','overflow-x:scroll;');
               $('.fc-resource-cell').attr('style','width:80px');
               var newwidth=  Number($('.fc-resource-cell').length*80) + Number(95);
               $('.fc-agendaDay-view').attr('style','width:'+newwidth+'px');
            }
            
            $('.fc-view-container').attr('style','overflow-x:scroll');
            
            $('.fc-today-button').click(function(e){
                e.preventDefault();
                
                $('#takvim_tarihe_gore').val('');
                takvimyukle(true,true);
            });
            $('.fc-prev-button').click(function(e){
                e.preventDefault();
                
                $('#takvim_tarihe_gore').val('');
                takvimyukle(true,true);
            });
            
            $('.fc-next-button').click(function(e){
                e.preventDefault();
                
                $('#takvim_tarihe_gore').val('');
                takvimyukle(true,true);
            });
            
            
            var $calendarContainer = $('.fc-view-container');
            var $timeColumn = $('.fc-axis.fc-time');
            $calendarContainer.on('scroll', function() {
                    var scrollLeft = $(this).scrollLeft();
                    $timeColumn.css('transform', 'translateX(' + scrollLeft + 'px)');
            });
                
            $.each(result.resource,function(key,item) {
                var child = parseInt(key)+parseInt(2);
                console.log(child);
                $('.fc-resource-cell:nth-child('+child+'n)').css({'background':item.bgcolor,'color':'#fff'});
            });
        },
        error: function (request, status, error) {
            if(preload)
                $('#preloader').hide();
             document.getElementById('hata').innerHTML =request.responseText;
        }
    });
}
function kapalisaatsil(randevuid)
{
    $.ajax({
                                    type: "POST",
                                    url: '/isletmeyonetim/kapalisaatsil',
                                    dataType: "text",
                                    data : {randevu_id:randevuid},
                                    headers: {
                                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                                    },
                                    beforeSend: function() {
                                        $("#preloader").show();
                                    },
                                    success: function(result)  {
                                        takvimyukle(true,false);
                                    },
                                    error: function (request, status, error) {
                                        $("#preloader").hide();
                                        takvimyukle(true,false);
                                        document.getElementById('hata').innerHTML = request.responseText;
                                    }
   });
}
$('#hizmet_kategori_ekle_duzenle_form').on('submit',function(e){
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/sistemeyenihizmetkategorisiekle',
        data: $('#hizmet_kategori_ekle_duzenle_form').serialize(),
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
          beforeSend: function(){
                $('#preloader').show();
            },
        success: function(result)  {
            $('#preloader').hide();
                swal({
                    type: "success",
                    title: "Başarılı",
                    text:  result.sonuc,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                });
                var resdata = result.kategori;
                var data={
                    id:resdata[0].id,
                    text:resdata[0].text
                };
                var option = new Option(data.text, data.id, false, false);
                $('select[name="hizmet_kategorisi"]').append(option).trigger('change');
                $('select[name="hizmet_kategorisi"]').val(data.id);
                $('select[name="hizmet_kategorisi"]').trigger('change');
                if($('select[name="hizmet_kategorisi"]').length)
                {
                    $('select[name="ozel_hizmet_kategorisi"]').append(option).trigger('change');
                    $('select[name="ozel_hizmet_kategorisi"]').val(data.id);
                    $('select[name="ozel_hizmet_kategorisi"]').trigger('change');
                }
                $('#hizmet_kategori_ekle_modal_kapat').trigger('click');
                $('#hizmet_kategori_modal_kapat').trigger('click');
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML ='hata : '+error+' '+request.responseText;
        }
    });
});
$('#katilimci_kaydet').click(function(e){
    e.preventDefault();
     $('#katilimci_belirle_buton').empty();
    if($('input:checkbox[name="katilimci_musteriler[]"]:checked').length>0)
        $('#katilimci_belirle_buton').append('Katılımcılar ('+$('input:checkbox[name="katilimci_musteriler[]"]:checked').length+')');
    else
        $('#katilimci_belirle_buton').append('Katılımcıları Belirle');
});
function eventGuncelle(event)
{
    var personelid = '';
    var cihazid = '';
    var odaid = '';
    var randevuid = event.randevuId;
    var hizmetid = event.id;
    var startt = event.start.format();
    var endd = event.end.format();
    var takvimturu = $('#randevu_ayarina_gore').val();
    var yenihizmetid = '';
    swal({
        title: "Onayla",
        text: "Bu işlemi gerçekleştirmek istediğinize emin misiniz?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Evet',
        cancelButtonText: "Hayır",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
                if($('#randevu_ayarina_gore').val()==1)
                    personelid = event.resourceId;
                if($('#randevu_ayarina_gore').val()==2)
                    cihazid = event.resourceId;
                if($('#randevu_ayarina_gore').val()==3)
                    odaid = event.resourceId;
                if($('#randevu_ayarina_gore').val()==0)
                {
                    var hizmet_id= '';
                    $.ajax({
                        type: "GET",
                        url: '/isletmeyonetim/kategoriyegorehizmetgetir',
                        dataType: "text",
                        data : {hizmet_kategori_id:event.resourceId,sube:$('input[name="sube"]').val()},
                        beforeSend: function() {
                            $("#preloader").show();
                        },
                        success: function(result)  {
                            $("#preloader").hide();
                            swal({
                                        title: 'Lütfen aşağıdan hizmet seçiniz',
                                        html: result,
                                        showCancelButton: true,
                                        confirmButtonText: 'Gönder',
                                        cancelButtonText: 'Vazgeç',
                                        showLoaderOnConfirm: true,
                                        confirmButtonClass: 'btn btn-success',
                                        cancelButtonClass: 'btn btn-danger',
                                        allowOutsideClick: false
                            }).then(function (result) {
                                    if(result.value){
                                        randevudropresizeguncelle(randevuid,hizmetid,personelid,startt,endd,takvimturu,odaid,cihazid,$('#hizmet_kategorisine_gore_yeni_hizmet_secimi').val());
                                    }
                                    else
                                        takvimyukle(true,false);
                            });
                        },
                        error: function (request, status, error) {
                                    $("#preloader").hide();
                                    document.getElementById('hata').innerHTML = request.responseText;
                        }
                    });
                }
                else
                    randevudropresizeguncelle(randevuid,hizmetid,personelid,startt,endd,takvimturu,odaid,cihazid,yenihizmetid);
        }
        else
            takvimyukle(true,false);
    });
}
function randevudropresizeguncelle(randevuid,hizmetid,personelid,startt,endd,takvimturu,odaid,cihazid,yenihizmetid)
{
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/randevuguncelledragdropresize',
                    dataType: "text",
                    data : {randevu_id:randevuid,id:hizmetid,personel_id:personelid,start:startt,end:endd,takvim_turu:takvimturu,oda_id:odaid,cihaz_id:cihazid,yeni_hizmet_id:yenihizmetid},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        takvimyukle(true,false);
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
}
$('#yenicihazbilgiekle').on('submit',function(e){
    e.preventDefault();
     var eklenebilir = 0;
     var uyari = "";
             $.ajax({
        type: "POST",
        url: '/isletmeyonetim/cihazekleduzenle',
        dataType: "json",
        data : $('#yenicihazbilgiekle').serialize(),
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
                    type: 'success',
                    title: 'Başarılı',
                    text: result.sonuc,
                }
            );
            $('#cihaz_tablo').DataTable().destroy();
            $('#cihaz_tablo').DataTable({
                ordering: false,
                        paging: false,
                        autoWidth: false,
                        responsive: true,
                       columns:[
                       { data: 'siralama', className: "text-center"},
                       {data:'cihaz_adi'},
                      {data:'durum'},
                      {data:'cihaz_aciklama'},
                         { data: 'islemler'   },
                    ],
                    data: result.cihazlar,
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
 $('#yeniodabilgiekle').on('submit',function(e){
    e.preventDefault();
     var eklenebilir = 0;
     var uyari = "";
             $.ajax({
        type: "POST",
        url: '/isletmeyonetim/odaekleduzenle',
        dataType: "json",
        data : $('#yeniodabilgiekle').serialize(),
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
             $('#preloader').hide();
            $('#yeniodabilgiekle').trigger('reset');

            $('#yeni_oda_modal').modal('hide');
            swal(
                {
                    type: 'success',
                    title: 'Başarılı',
                    text: result.sonuc,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                }
            );
            $('#oda_tablo').DataTable().destroy();
            $('#oda_tablo').DataTable({
                  ordering: false,
                        paging: false,
                        autoWidth: false,
                        responsive: true,
                       columns:[
                       { data: 'siralama', className: "text-center"},
                       {data:'oda_adi'},
                         {data:'durum'},
                           {data:'oda_aciklama'},
                         { data: 'islemler'   },
                    ],
                    data: result.odalar,
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


$('#odabilgiduzenle').on('submit',function(e){
    e.preventDefault();
     var eklenebilir = 0;
     var uyari = "";
             $.ajax({
        type: "POST",
        url: '/isletmeyonetim/odaekleduzenle',
        dataType: "json",
        data : $('#odabilgiduzenle').serialize(),
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        beforeSend: function(){
            $('#preloader').show();
        },
       success: function(result)  {
            $('#preloader').hide();
            $('#odabilgiduzenle').trigger('reset');

            $('#oda_duzenle_modal2').modal('hide');
            swal(
                {
                    type: 'success',
                    title: 'Başarılı',
                    text: result.sonuc,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                }
            );
            $('#oda_tablo').DataTable().destroy();
            $('#oda_tablo').DataTable({
                     ordering: false,
                        paging: false,
                        autoWidth: false,
                        responsive: true,
                    columns:[
                    { data: 'siralama', className: "text-center"},
                        {data:'oda_adi'},
                        {data:'durum'},
                        {data:'oda_aciklama'},
                        { data: 'islemler'   },
                    ],
                    data: result.odalar,
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

 $(document).on('click','a[name="oda_duzenle"]',function (e) {
    e.preventDefault();
     $.ajax({
                type: "GET",
                url: '/isletmeyonetim/odadetayigetir',
                dataType: "json",
                data : {oda_id:$(this).attr('data-value')},
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                beforeSend: function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                    console.log(result);
                    console.log(result.personel_id);
                    $('#preloader').hide();
                    $('#oda_adi').val(result.oda_adi);
                    $('#oda_personeli').val(result.personel_id).trigger('change');
                    $('#duzenlenecek_oda_id').val(result.id);
                    $('#oda_duzenle_modal2').modal();
                },
                error: function (request, status, error) {
                        $('#preloader').hide();
                        document.getElementById('hata').innerHTML = request.responseText + ' '+ error;
                }
            });


 });
 $(document).on("click",'a[name="oda_musait_isaretle"]',function(e){
     e.preventDefault();
     var odaid = $(this).attr('data-value');
     $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/odamusaitisaretle',
                    dataType: "json",
                    data : {oda_id:odaid,durum:1,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        $('#modal_kapat').trigger('click');
                        swal(
                            {
                                type: 'success',
                                title: 'Başarılı',
                                text: result.status,
                            }
                        );
                        $('#oda_tablo').DataTable().destroy();
                        $('#oda_tablo').DataTable({
                             ordering: false,
                        paging: false,
                        autoWidth: false,
                        responsive: true,
                                columns:[
                                { data: 'siralama', className: "text-center"},
                                        {data:'oda_adi'},
                                        { data: 'durum' },
                                          {data:'oda_aciklama'},
                                        {data : 'islemler'},
                                ],
                                data: result.odalar,
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
});
$(document).on("click",'a[name="oda_musaitdegil_isaretle"]',function(e){
     e.preventDefault();
    $('#oda_id').val($(this).attr('data-value'));
});
$('#oda_duzenle').on('submit',function(e){
    e.preventDefault();
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/odamusaitdegilisaretle',
                    dataType: "json",
                    data : {oda_id:$('#oda_id').val(),durum:0,sube:$('input[name="sube"]').val(),aciklama:$('#oda_aciklama').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                         $('#oda_duzenle')[0].reset();
                      $('#oda_duzenle_modal').empty();
                        $('.modal_kapat').trigger('click');
                        swal(
                            {
                                type: 'success',
                                title: 'Başarılı',
                                text: result.status,
                            }
                        );
                        $('#oda_tablo').DataTable().destroy();
                        $('#oda_tablo').DataTable({
                             ordering: false,
                        paging: false,
                        autoWidth: false,
                        responsive: true,
                                columns:[
                                { data: 'siralama', className: "text-center"},
                                        {data:'oda_adi'},
                                        { data: 'durum' },
                                        {data:'oda_aciklama'},
                                        {data : 'islemler'},
                                ],
                                data: result.odalar,
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
});
$('#oda_tablo').on('click','a[name="oda_sil"]',function(e){
    e.preventDefault();
    var odaid = $(this).attr('data-value');
    swal({
        title: "Emin misiniz?",
        text: "Kaldırma işlemi geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Odayı kaldır',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
                $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/odasil',
                    dataType: "json",
                    data : {oda_id:odaid,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        $('#oda_duzenle')[0].reset();
                        $('#modal_kapat').trigger('click');
                        swal(
                            {
                                type: 'success',
                                title: 'Başarılı',
                                text: "Oda başarıyla silindi",
                                 showCloseButton: false,
                                                    showCancelButton: false,
                                                    showConfirmButton:false,
                                                    timer:3000,

                            }
                        );
                        $('#oda_tablo').DataTable().destroy();
                        $('#oda_tablo').DataTable({
                             ordering: false,
                        paging: false,
                        autoWidth: false,
                        responsive: true,
                                columns:[
                                { data: 'siralama', className: "text-center"},
                                        { data: 'oda_adi' },
                                        { data: 'durum' },
                                        { data: 'oda_aciklama'},
                                        {data : 'islemler'},
                                ],
                                data: result.odalar,
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


$('#oda_tablo').on('click','button[name="oda_siralamayi_bir_asagi_tasi"]',function(e){
     $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/odaSiralamaArtir',
                    data: {odaid:$(this).attr('data-value'),sube:$('input[name="sube"]').val(),siraNo:$(this).attr('data-index-number')},
                    dataType: "json",
                    beforeSend:function(){
                        
                    },
                    success: function(result)  {
                        
                        $('#oda_tablo').DataTable().destroy();
                        $('#oda_tablo').DataTable({
                             ordering: false,
                        paging: false,
                        autoWidth: false,
                        responsive: true,
                                columns:[
                                { data: 'siralama', className: "text-center"},
                                        { data: 'oda_adi' },
                                        { data: 'durum' },
                                        { data: 'oda_aciklama'},
                                        {data : 'islemler'},
                                ],
                                data: result.odalar,
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
 $('#oda_tablo').on('click','button[name="oda_siralamayi_bir_yukari_tasi"]',function(e){
     $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/odaSiralamaAzalt',
                    data: {odaid:$(this).attr('data-value'),sube:$('input[name="sube"]').val(),siraNo:$(this).attr('data-index-number')},
                    dataType: "json",
                    beforeSend:function(){
                        
                    },
                    success: function(result)  {
                        
                        $('#oda_tablo').DataTable().destroy();
                        $('#oda_tablo').DataTable({
                             ordering: false,
                        paging: false,
                        autoWidth: false,
                        responsive: true,
                                columns:[
                                { data: 'siralama', className: "text-center"},
                                        { data: 'oda_adi' },
                                        { data: 'durum' },
                                        { data: 'oda_aciklama'},
                                        {data : 'islemler'},
                                ],
                                data: result.odalar,
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

$(document).on("click",'a[name="cihaz_musait_isaretle"]',function(e){
     e.preventDefault();
     var cihazid = $(this).attr('data-value');
     $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/cihazmusaitisaretle',
                    dataType: "json",
                    data : {cihaz_id:cihazid,durum:1,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        $('#modal_kapat').trigger('click');
                        swal(
                            {
                                type: 'success',
                                title: 'Başarılı',
                                text: "Cihaz müsait durumuna alındı.",
                                 showCloseButton: false,
                                                    showCancelButton: false,
                                                    showConfirmButton:false,
                                                    timer:3000,
                            }
                        );
                        $('#cihaz_tablo').DataTable().destroy();
                        $('#cihaz_tablo').DataTable({
                            ordering: false,
                        paging: false,
                        autoWidth: false,
                        responsive: true,
                                columns:[
                                { data: 'siralama', className: "text-center"},
                                        {data:'cihaz_adi'},
                                        { data: 'durum' },
                                          {data:'cihaz_aciklama'},
                                        {data : 'islemler'},
                                ],
                                data: result.cihazlar,
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
});
$(document).on("click",'a[name="cihaz_musaitdegil_isaretle"]',function(e){
     e.preventDefault();
    $('#cihaz_id').val($(this).attr('data-value'));
});
$('#cihaz_duzenle').on('submit',function(e){
    e.preventDefault();
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/cihazmusaitdegilisaretle',
                    dataType: "json",
                    data : {cihaz_id:$('#cihaz_id').val(),durum:0,sube:$('input[name="sube"]').val(),aciklama:$('#cihaz_aciklama').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                         $('#cihaz_duzenle')[0].reset();
                      $('#cihaz_duzenle_modal').empty();
                        $('.modal_kapat').trigger('click');
                        swal(
                            {
                                type: 'success',
                                title: 'Başarılı',
                                text: "Cihaz müsait değil durumuna alındı",
                                 showCloseButton: false,
                                                    showCancelButton: false,
                                                    showConfirmButton:false,
                                                    timer:3000,
                            }
                        );
                        $('#cihaz_tablo').DataTable().destroy();
                        $('#cihaz_tablo').DataTable({
                            ordering: false,
                        paging: false,
                        autoWidth: false,
                        responsive: true,
                                columns:[
                                { data: 'siralama', className: "text-center"},
                                        {data:'cihaz_adi'},
                                        { data: 'durum' },
                                        {data:'cihaz_aciklama'},
                                        {data : 'islemler'},
                                ],
                                data: result.cihazlar,
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
});
$('#cihaz_tablo').on('click','a[name="cihaz_sil"]',function(e){
    e.preventDefault();
    var cihazid = $(this).attr('data-value');
    swal({
        title: "Emin misiniz?",
        text: "Kaldırma işlemi geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Cihazı kaldır',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
                $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/cihazsil',
                    dataType: "json",
                    data : {cihaz_id:cihazid,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        $('#cihaz_duzenle')[0].reset();
                        $('#modal_kapat').trigger('click');
                        swal(
                            {
                                type: 'success',
                                title: 'Başarılı',
                                text: "Cihaz başarıyla silindi",
                                 showCloseButton: false,
                                                    showCancelButton: false,
                                                    showConfirmButton:false,
                                                    timer:3000,
                            }
                        );
                        $('#cihaz_tablo').DataTable().destroy();
                        $('#cihaz_tablo').DataTable({
                            ordering: false,
                        paging: false,
                        autoWidth: false,
                        responsive: true,
                                columns:[
                                { data: 'siralama', className: "text-center"},
                                        { data: 'cihaz_adi' },
                                        { data: 'durum' },
                                        { data: 'cihaz_aciklama'},
                                        {data : 'islemler'},
                                ],
                                data: result.cihazlar,
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


$('#cihaz_tablo').on('click','button[name="cihaz_siralamayi_bir_asagi_tasi"]',function(e){

     $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/cihazSiralamaArtir',
                    data: {cihazid:$(this).attr('data-value'),sube:$('input[name="sube"]').val(),siraNo:$(this).attr('data-index-number')},
                    dataType: "json",
                    beforeSend:function(){
                        
                    },
                    success: function(result)  {
                        
                        $('#cihaz_tablo').DataTable().destroy();
                        $('#cihaz_tablo').DataTable({
                            ordering: false,
                        paging: false,
                        autoWidth: false,
                        responsive: true,
                                columns:[
                                { data: 'siralama', className: "text-center"},
                                        { data: 'cihaz_adi' },
                                        { data: 'durum' },
                                        { data: 'cihaz_aciklama'},
                                        {data : 'islemler'},
                                ],
                                data: result.cihazlar,
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
 $('#cihaz_tablo').on('click','button[name="cihaz_siralamayi_bir_yukari_tasi"]',function(e){
     $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/cihazSiralamaAzalt',
                    data: {cihazid:$(this).attr('data-value'),sube:$('input[name="sube"]').val(),siraNo:$(this).attr('data-index-number')},
                    dataType: "json",
                    beforeSend:function(){
                        
                    },
                    success: function(result)  {
                        
                        $('#cihaz_tablo').DataTable().destroy();
                        $('#cihaz_tablo').DataTable({
                             ordering: false,
                        paging: false,
                        autoWidth: false,
                        responsive: true,
                                columns:[
                                        { data: 'siralama', className: "text-center"},
                                        { data: 'cihaz_adi' },
                                        { data: 'durum' },
                                        { data: 'cihaz_aciklama'},
                                        {data : 'islemler'},
                                ],
                                data: result.cihazlar,
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


$(document).on('click','a[name="tahsil_et"]',function(e){
    var randevu_id = $(this).attr('data-value');
     $.ajax({
                                    type: "POST",
                                    url: '/isletmeyonetim/randevutahsilet',
                                    dataType: "text",
                                    data : {randevuid:randevu_id,dogrulama_kodu:''},
                                    headers: {
                                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                                    },
                                    beforeSend: function() {
                                        $("#preloader").show();
                                    },
                                   success: function(result)  {
                                        $("#preloader").hide();
                                        window.location.href = '/isletmeyonetim/tahsilat/'+result+'?sube='+$('input[name="sube"]').val();
                                    },
                                    error: function (request, status, error) {
                                        $("#preloader").hide();
                                        document.getElementById('hata').innerHTML = request.responseText;
                                    }
        });
});
 function randevuyaGeldiIsaretle(randevu_id, hizmetid, dogrulamaKodu, kvkkKodu,dogrulamaSoruldu=false ,dogrulamaSorulduGonderilecek=false, isKvkkProcess = false, seansDusuldu = false, seansFormData = null) {
    
    // Eğer seansFormData varsa, onu kullan
    if (seansFormData !== null) {
        // FormData'ya ek alanları ekle (sadece varsa)
        if (kvkkKodu !== undefined && kvkkKodu !== null && kvkkKodu !== '') {
            seansFormData.append('kvkkOnayKodu', kvkkKodu);
        }
        
        if (randevu_id !== undefined && randevu_id !== null && randevu_id !== '') {
            seansFormData.append('randevuid', randevu_id);
        }
        
        if (dogrulamaKodu !== undefined && dogrulamaKodu !== null && dogrulamaKodu !== '') {
            seansFormData.append('dogrulama_kodu', dogrulamaKodu);
        }
        
        if (seansDusuldu !== undefined && seansDusuldu !== null) {
            seansFormData.append('seansDusmeYapildi', seansDusuldu);
        }
        
        var sube = $('input[name="sube"]').val();
        if (sube !== undefined && sube !== null && sube !== '') {
            seansFormData.append('sube', sube);
        }
        
        if (hizmetid !== undefined && hizmetid !== null && hizmetid !== '') {
            seansFormData.append('hizmetId', hizmetid);
        }
        
        if (isKvkkProcess !== undefined && isKvkkProcess !== null) {
            seansFormData.append('isKvkkProcess', isKvkkProcess);
        }
        if (dogrulamaSoruldu !== undefined && dogrulamaSoruldu !== null) {
            seansFormData.append('dogrulamaSoruldu', dogrulamaSoruldu ? 1 : 0);
        }
          if (dogrulamaSorulduGonderilecek !== undefined && dogrulamaSorulduGonderilecek !== null) {
           seansFormData.append('dogrulamaSorulduGonderilecek', dogrulamaSorulduGonderilecek ? 1 : 0);
        }
        
        var ajaxData = seansFormData;
        var processData = false;
        var contentType = false;
    } else {
        // Normal data objesi oluştur
        var ajaxData = {};
        
        if (kvkkKodu !== undefined && kvkkKodu !== null && kvkkKodu !== '') {
            ajaxData.kvkkOnayKodu = kvkkKodu;
        }
        
        if (randevu_id !== undefined && randevu_id !== null && randevu_id !== '') {
            ajaxData.randevuid = randevu_id;
        }
        
        if (dogrulamaKodu !== undefined && dogrulamaKodu !== null && dogrulamaKodu !== '') {
            ajaxData.dogrulama_kodu = dogrulamaKodu;
        }
        
        if (seansDusuldu !== undefined && seansDusuldu !== null) {
            ajaxData.seansDusmeYapildi = seansDusuldu;
        }
        
        var sube = $('input[name="sube"]').val();
        if (sube !== undefined && sube !== null && sube !== '') {
            ajaxData.sube = sube;
        }
        
        if (hizmetid !== undefined && hizmetid !== null && hizmetid !== '') {
            ajaxData.hizmetId = hizmetid;
        }
        
        if (isKvkkProcess !== undefined && isKvkkProcess !== null) {
            ajaxData.isKvkkProcess = isKvkkProcess;
        }
         if (dogrulamaSoruldu !== undefined && dogrulamaSoruldu !== null) {
          ajaxData.dogrulamaSoruldu = dogrulamaSoruldu ? 1 : 0;
        }
         if (dogrulamaSorulduGonderilecek !== undefined && dogrulamaSorulduGonderilecek !== null) {
           ajaxData.dogrulamaSorulduGonderilecek = dogrulamaSorulduGonderilecek ? 1 : 0;
        }
        
        var processData = true;
        var contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
    }
   if (ajaxData instanceof FormData) {
    for (var pair of ajaxData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
} else {
    console.log(ajaxData);
}
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/randevugeldiisaretle',
        dataType: "json",
        data: ajaxData,
        processData: processData,
        contentType: contentType,

        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result) {
            $("#preloader").hide();
            if (result.geldiIsaretlendi) {
                $('#modal-view-event').modal('hide');
                if ($('#randevu_liste').length) {
                    randevufiltre();
                }
                if ($('#calendar').length) {
                    takvimyukle(false, false);
                }
            } else {
                if (result.onayGerekli) {
                    kvkkGerekli(randevu_id, hizmetid, result, dogrulamaKodu,dogrulamaSoruldu,dogrulamaSorulduGonderilecek);
                } else if (result.dogrulamaGerekli) {
                    dogrulama_islemleri(randevu_id, hizmetid, dogrulamaKodu,dogrulamaSoruldu,dogrulamaSorulduGonderilecek);
                } else if(result.seansDusumuBildir) {
                    seansDusumu(randevu_id, hizmetid, result, dogrulamaKodu, kvkkKodu,dogrulamaSoruldu,dogrulamaSorulduGonderilecek);   
                }
                else if(result.dogrulamaSorulsun)
                {
                     swal({
                        title: "Doğrulama",
                        text: "Müşteriye geldiğine dair doğrulama kodu gönderilsin mi?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#00bc8c',
                        confirmButtonText: 'Gönder',
                        cancelButtonText: "Gönderme",
                        confirmButtonClass: 'btn btn-success',
                        cancelButtonClass: 'btn btn-danger',
                    }).then(function (result) {
                         
                         if(result.value)
                            randevuyaGeldiIsaretle(randevu_id,hizmetid,'','',true,true);
                        else
                            randevuyaGeldiIsaretle(randevu_id,hizmetid,'','',true,false);
                         
                    });
                }
            }
        },
        error: function(request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
}
$(document).on('click','a[name="geldi_isaretle"]',function(e){
    randevu_id = $(this).attr('data-value');
    hizmetid = $(this).attr('data-index-number');
    swal({
        title: "Emin misiniz?",
        text: "Randevuyu geldi olarak işaretlemek istediğinize emin misiniz?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Geldi Olarak İşaretle',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){


                   
                
                randevuyaGeldiIsaretle(randevu_id,hizmetid,'','',false,false);
                
            
        }
    });
});
 $(document).on('click','a[name="geldi_isareti_kaldir"]',function(e){
    randevu_id = $(this).attr('data-value');
    hizmetid = $(this).attr('data-index-number');
    swal({
        title: "Emin misiniz?",
        text: "Randevudan geldi işaretini kaldırmak istediğinize emin misiniz?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Geldi İşaretini Kaldır',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){

            $.ajax({
                type: "POST",
                url: '/isletmeyonetim/randevuGeldiGelmediIsaretiKaldir',
                dataType: "json",
                data: {randevuid:randevu_id,hizmetid:hizmetid},
                 
                

                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                beforeSend: function() {
                    $("#preloader").show();
                },
                success: function(result) {
                    $("#preloader").hide();
                      $('#modal-view-event').modal('hide');
                    takvimyukle(false,false);
                },
                error: function(request, status, error) {
                    $("#preloader").hide();
                    document.getElementById('hata').innerHTML = request.responseText;
                }
            });
                       
                
                 
                
            
        }
    });
});

$(document).on('click','a[name="gelmedi_isareti_kaldir"]',function(e){
    randevu_id = $(this).attr('data-value');
    hizmetid = $(this).attr('data-index-number');
    swal({
        title: "Emin misiniz?",
        text: "Randevudan gelmedi işaretini kaldırmak istediğinize emin misiniz?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Gelmedi İşaretini Kaldır',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){

            $.ajax({
                type: "POST",
                url: '/isletmeyonetim/randevuGeldiGelmediIsaretiKaldir',
                dataType: "json",
                data: {randevuid:randevu_id,hizmetid:hizmetid},
               
                

                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                beforeSend: function() {
                    $("#preloader").show();
                },
                success: function(result) {
                    $("#preloader").hide();
                      $('#modal-view-event').modal('hide');
                      takvimyukle(false,false);
                },
                error: function(request, status, error) {
                    $("#preloader").hide();
                    document.getElementById('hata').innerHTML = request.responseText;
                }
            });
                       
                
                 
                
            
        }
    });
});

function dogrulama_islemleri(randevu_id,hizmetid,dogrulamaKodu,dogrulamaSoruldu,dogrulamaSorulduGonderilecek)
{
    
                        swal({
                            title: 'Lütfen müşterinin cep telefonuna gönderilen doğrulama kodunu giriniz',
                            input: 'text',
                            showCancelButton: true,
                            confirmButtonText: 'Gönder',
                            cancelButtonText: 'Vazgeç',
                            showLoaderOnConfirm: true,
                        confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                        }).then(function (result) {
                            if(result.value){
                                randevuyaGeldiIsaretle(randevu_id,hizmetid,result.value,'',true,false);
                               
                            }
                        })
}
function kvkkGerekli(randevu_id, hizmetid, result1, dogrulamaKodu,dogrulamaSoruldu,dogrulamaSorulduGonderilecek) {
    swal({
        type: result1.status,
        title: result1.title,
        input: result1.onayGerekli ? 'text' : null,
        html: "<div><p>" + result1.mesaj + "</p></div>",
        showCloseButton: result1.showCloseButton,
        showCancelButton: result1.showCancelButton,
        showConfirmButton: result1.showConfirmButton,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Kaydet',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function(result) {
        if (result.value) {
            // KVKK işlemi olduğunu belirt
            randevuyaGeldiIsaretle(randevu_id, hizmetid, dogrulamaKodu, result.value, dogrulamaSoruldu,dogrulamaSorulduGonderilecek);
        }
    });
}
function seansDusumu(randevu_id, hizmetid, result1, dogrulamaKodu,kvkkKodu,dogrulamaSoruldu,dogrulamaSorulduGonderilecek) {
    swal({
        
        title: 'Seans İşleme Ekranı',
        icon: null,  // <-- Bunu ekleyin
        html: result1.seanslar,
        showCloseButton: false,
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Seansı Kaydet',
        cancelButtonText: 'İptal',
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function(result) {
            
            if(result.value)
            {
                var formData = new FormData();
                $('input[name="seansGirdileri[]"]').each(function(){
                    if($(this).prop('checked'))
                        formData.append('seansGirdileri[]',$(this).attr('data-value'));
                });
                randevuyaGeldiIsaretle(randevu_id, hizmetid, dogrulamaKodu, kvkkKodu,dogrulamaSoruldu,dogrulamaSorulduGonderilecek, true, true,  formData);
               
               
            }
            // KVKK işlemi olduğunu belirt
            
        
    });
}
function seans_dogrulama_islemleri(seans_id,durum,seanssayfasi,musteriid)
{
                        swal({
                            title: 'Lütfen müşterinin cep telefonuna gönderilen doğrulama kodunu giriniz',
                            input: 'text',
                            showCancelButton: true,
                            confirmButtonText: 'Gönder',
                            cancelButtonText: 'Vazgeç',
                            showLoaderOnConfirm: true,
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                        }).then(function (result) {
                            if(result.value){
                                $.ajax({
                                    type: "POST",
                                    url: '/isletmeyonetim/seansgirdiguncelle',
                                    dataType: "json",
                                    data : {id:seans_id,sube:$('input[name="sube"]').val(),geldi:durum,visibility:true,dogrulama_kodu:result.value,dogrulama:true,seans_sayfasi:seanssayfasi},
                                    headers: {
                                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                                    },
                                    beforeSend: function() {
                                        $("#preloader").show();
                                    },
                                   success: function(result)  {
                                        $("#preloader").hide();
                                        if(result=='hatalikod')
                                            seans_dogrulama_islemleri(seans_id,durum,seanssayfasi,musteriid);
                                        else
                                        {
                                                if($.trim($("#seans_detayi").html())==''){
                                                    $('#adisyon_detay_paket_tablo').empty();
                                                    $('#adisyon_detay_paket_tablo').append(result.html);
                                                     select2ozellikyukle('#adisyon_detay_paket_tablo');
                                                }
                                                if($.trim($("#seans_detayi").html())!=''){
                                                    $('#seans_detayi').empty();
                                                    $('#seans_detayi').append(result.html);
                                                    select2ozellikyukle('#seans_detayi');
                                                }
                                                if(musteriid != '')
                                                {
                                                    $('#adisyon_detay_paket_tablo_2').empty();
                                                    $('#adisyon_detay_paket_tablo_2').append(result.html2);
                                                }
                                                $('input[name="seans_tarihi_adisyon_paket"]').each(function(index){
                                                        //$(this).datepicker('destroy');
                                                        $(this).datepicker({
                                                            language: "tr",
                                                            autoClose: true,
                                                            dateFormat: "yyyy-mm-dd",
                                                            onSelect: function(dateText) {
                                                                 seanstarihguncelle(dateText,this.value);
                                                                //alert($(this).attr('data-value')+ ' nolu seans için tarih '+dateText+ ' olarak değiştirildi');
                                                            }
                                                        });
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
}
$('#adisyona_paket_ekle,#adisyona_urun_ekle').click(function () {
    $('input[name="adisyon_id"]').val($(this).attr('data-value'));
});
$('#adisyon_harici_urun_satisi,#adisyon_harici_paket_satisi').click(function (){
    $('input[name="adisyon_id"]').removeAttr('value');
});
function adisyontoplamhesapla()
{
    let total = 0;
                        $('input[name="hizmet_fiyati_adisyon"]').each(function(){
                              total += parseFloat($(this).val()) || 0;
                        });
                        $('input[name="urun_fiyati_adisyon[]"]').each(function(){
                              total += parseFloat($(this).val()) || 0;
                        });
                         $('input[name="paket_fiyati_adisyon[]"]').each(function(){
                              total += parseFloat($(this).val()) || 0;
                        });
                           $('#hizmet_urunler_toplam_fiyat').empty();
                            var currency_symbol = "₺";
                  var formattedOutput = new Intl.NumberFormat('tr-TR', {
                   style: 'currency',
                   currency: 'TRY',
                   minimumFractionDigits: 2,
                 });
                  $('#hizmet_urunler_toplam_fiyat').append(formattedOutput.format(total).replace(currency_symbol, ''));
                  $('#tahsilat_tutari').val(total);
                  $('#tahsilat_tutari_numeric').val(total);
}
$(document).on('click','button[name="paket_seans_detay_getir"] ',function(e){
    if($('tr[name="paket_seanslari"][data-value="'+$(this).attr('data-value')+'"]').css('visibility') == 'collapse'){
        $('tr[name="paket_seanslari"][data-value="'+$(this).attr('data-value')+'"]').attr('style','visibility:visible');
        $('button[name="paket_seans_detay_getir"][data-value="'+$(this).attr('data-value')+'"]').empty();
        $('button[name="paket_seans_detay_getir"][data-value="'+$(this).attr('data-value')+'"]').append('<i class="fa fa-chevron-up"></i>');
    }
    else{
        $('tr[name="paket_seanslari"][data-value="'+$(this).attr('data-value')+'"]').attr('style','visibility:collapse');
        $('button[name="paket_seans_detay_getir"][data-value="'+$(this).attr('data-value')+'"]').empty();
        $('button[name="paket_seans_detay_getir"][data-value="'+$(this).attr('data-value')+'"]').append('<i class="fa fa-chevron-down"></i>');
    }
});
$(document).on('click','button[name="seans_randevu_olustur"]',function (e) {
    var acceptable = true;
    var tarih = $('input[name="seans_tarihi_adisyon_paket"][data-value="'+$(this).attr('data-index-number')+'"]').val().split(' ');
    if($('select[name="paketseanspersonelcihaz"][data-value="'+$(this).attr('data-index-number')+'"]').val()=='' && $('select[name="paketseanshizmet"][data-value="'+$(this).attr('data-index-number')+'"]').val()==''){
         swal(
                {
                    type: 'warning',
                    title: 'Uyarı',
                    text: 'Randevu oluşturmadan önce lütfen hizmet ve personel & cihaz seçimi yapınız',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000
                }
         );
         acceptable = false;
    }
    if(acceptable)
    {
        var tarihsaat = $('input[name="seans_tarihi_adisyon_paket"][data-value="'+$(this).attr('data-index-number')+'"]').val();
        var personelcihaz =  $('select[name="paketseanspersonelcihaz"][data-value="'+$(this).attr('data-index-number')+'"]').val();
        var personelcihazadi = $('select[name="paketseanspersonelcihaz"][data-value="'+$(this).attr('data-index-number')+'"]').select2('data')[0].text;
        var odaadi = $('select[name="paketseansoda"][data-value="'+$(this).attr('data-index-number')+'"]').select2('data')[0].text;
        var oda = $('select[name="paketseansoda"][data-value="'+$(this).attr('data-index-number')+'"]').val();
        var hizmet = $('select[name="paketseanshizmet"][data-value="'+$(this).attr('data-index-number')+'"]').val();
        var hizmetadi = $('select[name="paketseanshizmet"][data-value="'+$(this).attr('data-index-number')+'"] option:selected').text();
        var adisyon_id = $(this).attr('data-value');
        var seans_id = $(this).attr('data-index-number');
        $.ajax({
            type: "GET",
            url: '/isletmeyonetim/musait-randevu-saatlerini-getir',
            dataType: "text",
            data : {tarih_saat:tarihsaat,personelid:personelcihaz,odaid:oda,hizmetid:hizmet,adisyonid:adisyon_id,seansid:seans_id,sube:$('input[name="sube"]').val()},
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
                        type: "warning",
                        title: "Randevu Oluşturma",
                        html: result,
                        showCancelButton: false,
                        confirmButtonText: 'Randevu Oluştur',
                        confirmButtonColor:'#5C008E',
                        showLoaderOnConfirm: true,
                        confirmButtonClass: 'btn btn-success',
                        cancelButtonClass: 'btn btn-danger',
                    }
                ).then(function (result) {
                    if(result.value){
                        var randevusaati  = $('#seansadisyonsaat').val();
                        swal({
                            type: "info",
                            title: "Randevu Detayları",
                            html : "<table class='table'><tr><td><b>Tarih</td><td>:</td><td></b>"+tarihsaat+'</td></tr>'
                                    +"<tr><td><b>Saat</td><td>:</td><td></b>"+$('#seansadisyonsaat').val()+'</td></tr>'
                                    +"<tr><td><b>Personel/Cihaz</td><td>:</td><td></b>"+personelcihazadi+'</td></tr>'
                                    +"<tr><td><b>Oda</td><td>:</td><td></b>"+odaadi+'</td></tr>'
                                    +"<tr><td><b>Hizmet</td><td>:</td><td></b>"+hizmetadi+'</td></tr></table>',
                            showCancelButton: false,
                            confirmButtonText: 'Randevuyu Onayla',
                            confirmButtonColor:'#28a745',
                            showLoaderOnConfirm: true,
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                        }).then(function (result2) {
                            if(result2.value){
                                //alert(randevusaati);
                                $.ajax({
                                    type: "POST",
                                    url: '/isletmeyonetim/seanstanrandevuolustur',
                                    dataType: "text",
                                    data : {tarih:tarihsaat,saat:randevusaati,personelid:personelcihaz,odaid:oda,hizmetid:hizmet,adisyonid:adisyon_id,seansid:seans_id,sube:$('input[name="sube"]').val()},
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
                                                type: "success",
                                                title: "Başarılı",
                                                html: "<p>Seans kaydı üzerinden randevu başarıyla eklendi</p>"+
                                                      "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/randevular?sube="+$('input[name="sube"]').val()+">"
                                                      +"Takvime Git</a>"+
                                                      "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/randevular-liste?sube="+$('input[name="sube"]').val()+">"
                                                      +"Randevu Listesine Git</a>",
                                                showCloseButton: false,
                                                showCancelButton: false,
                                                showConfirmButton:false,
                                            }
                                        );
                                        $("button[data-dismiss='modal']").trigger('click');
                                        if($('#adisyon_detay_paket_tablo').length){
                                            $('#adisyon_detay_paket_tablo').empty();
                                            $('#adisyon_detay_paket_tablo').append(result);
                                             select2ozellikyukle('#adisyon_detay_paket_tablo');
                                        }
                                        if($('#seans_detayi').length){
                                             $('#seans_detayi').empty();
                                            $('#seans_detayi').append(result);
                                             select2ozellikyukle('#seans_detayi');
                                        }
                                    },
                                    error: function (request, status, error) {
                                        $("#preloader").hide();
                                        document.getElementById('hata').innerHTML = request.responseText;
                                    }
                                });
                            }
                        });
                    }
                });
            },
            error: function (request, status, error) {
                    $("#preloader").hide();
                    document.getElementById('hata').innerHTML = request.responseText;
            }
        });
        /*$.ajax({
        type: "POST",
        url: '/isletmeyonetim/seanstanrandevuolustur',
        dataType: "text",
        data : {tarih_saat:tarihsaat,personelid:personelcihaz,odaid:oda,hizmetid:hizmet,adisyonid:adisyon_id,seansid:seans_id,sube:$('input[name="sube"]').val()},
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
                    type: "success",
                    title: "Başarılı",
                    html: "<p>Seans kaydı üzerinden randevu başarıyla eklendi</p>"+
                          "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/randevular/'>"
                          +"Takvime Git</a>"+
                          "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/randevular-liste'>"
                          +"Randevu Listesine Git</a>",
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                }
            );
            $('#adisyon_detay_paket_tablo').empty();
            $('#adisyon_detay_paket_tablo').append(result);
        },
        error: function (request, status, error) {
                       $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
        }
        });*/
    }
})
$('#adisyon_detay_paket_tablo,#seans_detayi').on('change','select[name="paketseanshizmet"]',function(e){
    e.preventDefault();
    var seanssayfasi = false;
     if($.trim($("#seans_detayi").html())!='')
        seanssayfasi = true;
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/seansgirdiguncelle',
        dataType: "json",
        data : {id:$(this).attr('data-value'),hizmet:$(this).val(),visibility:true,seans_sayfa:seanssayfasi,sube:$('input[name="sube"]').val()},
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
         beforeSend: function() {
                        $("#preloader").show();
            },
        success: function(result)  {
             $("#preloader").hide();
            if($.trim($("#seans_detayi").html())==''){
                $('#adisyon_detay_paket_tablo').empty();
                $('#adisyon_detay_paket_tablo').append(result.html);
                 select2ozellikyukle('#adisyon_detay_paket_tablo');
            }
            if($.trim($("#seans_detayi").html())!=''){
                 $('#seans_detayi').empty();
                $('#seans_detayi').append(result.html);
                 select2ozellikyukle('#seans_detayi');
            }
             $('input[name="seans_tarihi_adisyon_paket"]').each(function(index){
                    //$(this).datepicker('destroy');
                    $(this).datepicker({
                        language: "tr",
                        autoClose: true,
                        dateFormat: "yyyy-mm-dd",
                        onSelect: function(dateText) {
                             seanstarihguncelle(dateText,this.value);
                            //alert($(this).attr('data-value')+ ' nolu seans için tarih '+dateText+ ' olarak değiştirildi');
                        }
                    });
                });
        },
        error: function (request, status, error) {
            $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#adisyon_detay_paket_tablo,#seans_detayi').on('change','select[name="paketseansoda"]',function(e){
    e.preventDefault();
    var seanssayfasi = false;
     if($.trim($("#seans_detayi").html())!='')
        seanssayfasi = true;
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/seansgirdiguncelle',
        dataType: "json",
        data : {id:$(this).attr('data-value'),oda:$(this).val(),visibility:true,seans_sayfa:seanssayfasi,sube:$('input[name="sube"]').val()},
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
         beforeSend: function() {
                        $("#preloader").show();
            },
        success: function(result)  {
            $("#preloader").hide();
            if($.trim($("#seans_detayi").html())==''){
                $('#adisyon_detay_paket_tablo').empty();
                $('#adisyon_detay_paket_tablo').append(result.html);
                 select2ozellikyukle('#adisyon_detay_paket_tablo');
            }
            if($.trim($("#seans_detayi").html())!=''){
                 $('#seans_detayi').empty();
                $('#seans_detayi').append(result.html);
                 select2ozellikyukle('#seans_detayi');
            }
             $('input[name="seans_tarihi_adisyon_paket"]').each(function(index){
                    //$(this).datepicker('destroy');
                    $(this).datepicker({
                        language: "tr",
                        autoClose: true,
                        dateFormat: "yyyy-mm-dd",
                        onSelect: function(dateText) {
                             seanstarihguncelle(dateText,this.value);
                            //alert($(this).attr('data-value')+ ' nolu seans için tarih '+dateText+ ' olarak değiştirildi');
                        }
                    });
                });
        },
        error: function (request, status, error) {
             $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#adisyon_detay_paket_tablo,#seans_detayi').on('change','select[name="paketseanspersonelcihaz"]',function(e){
    e.preventDefault();
    var seanssayfasi = false;
     if($.trim($("#seans_detayi").html())!='')
        seanssayfasi = true;
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/seansgirdiguncelle',
        dataType: "json",
        data : {id:$(this).attr('data-value'),personelcihaz:$(this).val(),visibility:true,seans_sayfa:seanssayfasi,sube:$('input[name="sube"]').val()},
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
         beforeSend: function() {
                        $("#preloader").show();
            },
        success: function(result)  {
              $("#preloader").hide();
            if($.trim($("#seans_detayi").html())==''){
                $('#adisyon_detay_paket_tablo').empty();
                $('#adisyon_detay_paket_tablo').append(result.html);
                 select2ozellikyukle('#adisyon_detay_paket_tablo');
            }
            if($.trim($("#seans_detayi").html())!=''){
                 $('#seans_detayi').empty();
                $('#seans_detayi').append(result.html);
                 select2ozellikyukle('#seans_detayi');
            }
            $('input[name="seans_tarihi_adisyon_paket"]').each(function(index){
                    //$(this).datepicker('destroy');
                    $(this).datepicker({
                        language: "tr",
                        autoClose: true,
                        dateFormat: "yyyy-mm-dd",
                        onSelect: function(dateText) {
                             seanstarihguncelle(dateText,this.value);
                            //alert($(this).attr('data-value')+ ' nolu seans için tarih '+dateText+ ' olarak değiştirildi');
                        }
                    });
            });
        },
        error: function (request, status, error) {
            $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#adisyon_detay_paket_tablo,#seans_detayi').on('change','select[name="paketseansdurum"]',function(e){
    e.preventDefault();
    var seansid = $(this).attr('data-value');
    var durum = $(this).val();
    var seanssayfasi = false;
    var musteriid = '';
    if($.trim($("#seans_detayi").html())!='')
        seanssayfasi = true;
    if($("input[name='musteri_id']").length)
        musteriid = $("input[name='musteri_id']").val();
    //if($('select[name="paketseanshizmet"][data-value="'+$(this).attr('data-value')+'"]').val()!='' && $('select[name="paketseanspersonelcihaz"][data-value="'+$(this).attr('data-value')+'"]').val()!='')
    //{
    if(durum==1&&$('#dogrulama_kodu_ayari').val()==1)
    {
             $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/seansdogrulamakodugonder',
                    dataType: "text",
                    data : {seans_id:seansid,geldi:durum,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        seans_dogrulama_islemleri(seansid,durum,seanssayfasi,musteriid);
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
            });
    }
    else
    {
        $.ajax({
            type: "POST",
            url: '/isletmeyonetim/seansgirdiguncelle',
            dataType: "json",
            data : {id:seansid,geldi:durum,visibility:true,seans_sayfa:seanssayfasi,dogrulama:false,musteri_id:musteriid,sube:$('input[name="sube"]').val()},
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            beforeSend: function() {
                $("#preloader").show();
            },
            success: function(result)  {
                $("#preloader").hide();
                if($.trim($("#seans_detayi").html())==''){
                    $('#adisyon_detay_paket_tablo').empty();
                    $('#adisyon_detay_paket_tablo').append(result.html);
                    select2ozellikyukle('#adisyon_detay_paket_tablo');
                }
                if($.trim($("#seans_detayi").html())!=''){
                     $('#seans_detayi').empty();
                    $('#seans_detayi').append(result.html);
                     select2ozellikyukle('#seans_detayi');
                 }
                 if(musteriid != '')
                 {
                        $('#adisyon_detay_paket_tablo_2').empty();
                        $('#adisyon_detay_paket_tablo_2').append(result.html2);
                 }
                  $('input[name="seans_tarihi_adisyon_paket"]').each(function(index){
                    //$(this).datepicker('destroy');
                    $(this).datepicker({
                        language: "tr",
                        autoClose: true,
                        dateFormat: "yyyy-mm-dd",
                        onSelect: function(dateText) {
                             seanstarihguncelle(dateText,this.value);
                            //alert($(this).attr('data-value')+ ' nolu seans için tarih '+dateText+ ' olarak değiştirildi');
                        }
                    });
                });
                  $('#seans_takip_liste').DataTable().destroy();
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
                      data: result.seanslar_liste,
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
    /*}
    else
    {
        swal(
                {
                    type: 'warning',
                    title: 'Uyarı',
                    text: 'Geldi & gelmedi işlemleri öncesi personel&cihaz ve hizmet seçimleri gereklidir!',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
                }
        );
        $('select[name="paketseansdurum"]').val('');
    }*/
});
function select2ozellikyukle(domNode)
{
    //$('#musteri_arama').select2('destroy');
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
            allowClear:true
        });
    });
}
$('#senet_formu').on('submit',function(e){
    e.preventDefault();
    warningtext = "";
    musterisecili = true;
    hizmeturunpaketsecili = true;
    if(!$('input[name="senet_hizmet_id[]"]').length && !$('input[name="urun_id_senet[]"]').length && !$('input[name="paket_id_senet[]"]').length)
    {
        hizmeturunpaketsecili = false;
        warningtext += "- En az bir hizmet, ürün veya paket seçiniz.<br>";
    }
    if($('#senet_formu select[name="ad_soyad"]').val()=="")
    {
        musterisecili = false;
        warningtext += "- Müşteri/danışan seçiniz.<br>";
    }
    if(musterisecili == false || hizmeturunpaketsecili == false)
    {
        swal({
            type: "warning",
            title: "Uyarı",
            text:  'Senet oluşturmak için en az bir hizmet, ürün veya paket eklemeniz gerekir!',
            showCloseButton: false,
            showCancelButton: false,
            showConfirmButton:false,
        });
    }
    else
    {
         var formData = new FormData();
        var data1 = $('#senet_formu').serializeArray();
        $.each(data1,function(key,input){
                formData.append(input.name,input.value);
        });
        if($('#senet_tutar').prop('disabled'))
            formData.append('senet_tutar',$('#senet_tutar').val());
        $.ajax({
            type: "POST",
            url: '/isletmeyonetim/senetekleguncelle',
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
                 swal(
                    {
                        type: "success",
                        title: "Başarılı",
                        html: "<p>Senet başarıyla oluşturuldu.</p>",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer:3000
                    }
                );
                if($('#yeni_senet_olusur').length){
                    $('#yeni_senet_olusur').attr('style','display:none');
                }
                $('#yeni_tahsilat_ekle').removeAttr('data-value');
                $('#yeni_tahsilat_ekle').removeAttr('data-toggle');
                $('#yeni_tahsilat_ekle').removeAttr('data-targe');
                $('#yeni_tahsilat_ekle').attr('id','senetle_tahsil_et');
                $('#taksitveyasenet').val(result.senet_id);
                $('#taksitveyasenet').attr('id','adisyonsenetid');
                if($('#senet_liste').length)
                {
                    $('#senet_liste').DataTable().destroy();
                    $('#senet_liste').DataTable({
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
                        data: result.senetler,
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
                $('button[data-dismiss="modal"]').trigger('click');
            },
            error: function (request, status, error) {
                    $("#preloader").hide();
                    document.getElementById('hata').innerHTML = request.responseText;
            }
        });
    }
});
$('#taksitli_tahsilat_formu').on('submit',function(e){
    e.preventDefault();
    var formData = new FormData();
    var data1 = $('#taksitli_tahsilat_formu').serializeArray();
    $.each(data1,function(key,input){
            formData.append(input.name,input.value);
    });
    var data2 = $('#adisyon_tahsilat').serializeArray();
    $.each(data2,function(key,input){
        formData.append(input.name,input.value);
    });
    if($('#taksit_tutar').prop('disabled'))
        formData.append('taksit_tutar',$('#taksit_tutar').val());
    formData.append('indirimsiz_birim_tutar',$('#birim_tutar').val());
    formData.append('musteri_indirim_yuzde',$('#musteri_indirim').val());
    if($('#tahsilat_ekrani').length)
    {
        formData.append('tahsilatekrani',$('#tahsilat_ekrani').val());
    }
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/taksitekleguncelle',
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
             swal(
                {
                    type: "success",
                    title: "Başarılı",
                    html: "<p>Taksitli alacak kayıtları başarıyla oluşturuldu</p>"+
                          "<a class='btn btn-primary btn-lg btn-block' id='alacaklar_listeme_git' href='#'>"
                          +"Alacaklar Listeme Git</a>",
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                }
            );
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
                $('#yeni_tahsilat_ekle').removeAttr('data-value');
                $('#yeni_tahsilat_ekle').removeAttr('data-toggle');
                $('#yeni_tahsilat_ekle').removeAttr('data-target');
                $('#yeni_tahsilat_ekle').attr('id','taksitle_tahsil_et');
                $('#taksitveyasenet').val(result);
                $('#taksitveyasenet').attr('id','adisyontaksitlitahsilatid');
            }
            if($('#yeni_taksitli_tahsilat_olusur').length)
                $('#yeni_taksitli_tahsilat_olusur').attr('disabled','true');
            $('#taksitModalKapat').trigger('click');
        },
        error: function (request, status, error) {
                $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('click','#senetle_veya_taksitle_tahsil_et',function(e){
            e.preventDefault();
            $.ajax({
                type: "GET",
                url: '/isletmeyonetim/senetvadegetir-tahsilat',
                dataType: "text",
                data : {musteriid:$('select[name="tahsilat_musteri_id"]').val(),sube:$('input[name="sube"]').val()},
                beforeSend: function() {
                    $("#preloader").show();
                },
                success: function(result)  {
                    $("#preloader").hide();
                    $('#senet_vade_listesi_tahsilat').empty();
                    $('#senet_vade_listesi_tahsilat').append(result);
                    $('#senet_id').val($('#adisyonsenetid').val());
                    $('.eklenen_senetler').each(function(){
                        $('input[name="senetvadeler[]"][data-value="'+$(this).val()+'"]').prop('checked',true);
                    });
                    $('#senet_taksit_detay_modal').modal();
                },
                error: function (request, status, error) {
                    $("#preloader").hide();
                    document.getElementById('hata').innerHTML = request.responseText;
                }
            });
            $.ajax({
                type: "GET",
                url: '/isletmeyonetim/taksitvadegetir-tahsilat',
                dataType: "text",
                data : {musteriid:$('select[name="tahsilat_musteri_id"]').val(),sube:$('input[name="sube"]').val()},
                beforeSend: function() {
                    $("#preloader").show();
                },
                success: function(result)  {
                    $("#preloader").hide();
                    $('#taksit_vade_listesi_tahsilat').empty();
                    $('#taksit_vade_listesi_tahsilat').append(result);
                    $('#taksitli_tahsilat_id').val($('#adisyontaksitlitahsilatid').val());
                     $('.eklenen_taksitler').each(function(){
                        $('input[name="taksitvadeler[]"][data-value="'+$(this).val()+'"]').prop('checked',true);
                    });
                    //$('#taksit_detay_modal').modal();
                    $('#senet_taksit_detay_modal').modal();
                },
                error: function (request, status, error) {
                    $("#preloader").hide();
                    document.getElementById('hata').innerHTML = request.responseText;
                }
            });
            //
});
$(document).on('click', '#taksitle_tahsil_et', function(e){
    e.preventDefault();
});
$('#adisyon_tahsilat_odeme_yontemi').change(function(e){
    e.preventDefault();
    if($(this).val()=='5')
        {
            $.ajax({
                type: "GET",
                url: '/isletmeyonetim/senetvadegetir',
                dataType: "text",
                data : {id:$('#adisyonsenetid').val(),sube:$('input[name="sube"]').val()},
                beforeSend: function() {
                    $("#preloader").show();
                },
                success: function(result)  {
                    $("#preloader").hide();
                    $('#senet_vade_listesi').empty();
                    $('#senet_vade_listesi').append(result);
                    $('#senet_id').val($('#adisyonsenetid').val());
                    $('#senet_detay_modal').modal();
                },
                error: function (request, status, error) {
                    $("#preloader").hide();
                    document.getElementById('hata').innerHTML = request.responseText;
                }
            });
        }
});
$('#senet_filtre').change(function (e) {
    e.preventDefault();
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/senetfiltre',
        dataType: "json",
        data : {sorgu:$(this).val(),sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            $('#senet_liste').DataTable().destroy();
            $('#senet_liste').DataTable({
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
                         $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('click','a[name="kampanya_detay"]',function(e){
    e.preventDefault();
    var kampanya_id = $(this).attr('data-value');
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/kampanyadetay',
        data: {kampanyaid:kampanya_id,sube:$('input[name="sube"]').val()},
        dataType: "json",
        beforeSend: function(){
                $('#preloader').show();
            },
        success: function(result)  {
             
            $('#preloader').hide();
            $('#paket_adi').empty();
            $('#paket_adi').append(result.kampanya.paket_isim);
            $('#kampanya_seans').empty();
            $('#kampanya_seans').append(result.kampanya.seans);
            $('#kampanya_katilimci').empty();
            $('#kampanya_katilimci').append(result.kampanya.katilimci_sayisi);
            $('#kampanya_hizmeti').empty();
            $('#kampanya_hizmeti').append(result.kampanya.hizmet_adi);
            $('#kampanya_toplam_tutar').empty();
            $('#kampanya_toplam_tutar').append(result.kampanya.fiyat);
            if(result.beklenen_count==0){
                $('#kampanyabeklenenleresmsgonder').attr('disabled',true);
            }
            else{
                $('#kampanyabeklenenleresmsgonder').removeAttr('disabled');
                $('#kampanyabeklenenleresmsgonder').attr('data-value',result.kampanyaid);
            }
            if(result.katilmayan_count==0){
                $('#kampanyabeklenenleretekrarsmsgonder').attr('disabled',true);
            }
            else{
                $('#kampanyabeklenenleretekrarsmsgonder').removeAttr('disabled');
                $('#kampanyabeklenenleretekrarsmsgonder').attr('data-value',result.kampanyaid);
            }
            if(result.beklenen_count_arama==0){
                $('#kampanyabeklenenleriara').attr('disabled',true);
            }
            else{
                $('#kampanyabeklenenleriara').removeAttr('disabled');
                $('#kampanyabeklenenleriara').attr('data-value',result.kampanyaid);
            }
             if(result.s==0){
                $('#kampanyabeklenenleritekrarara').attr('disabled',true);
            }
            else{
                $('#kampanyabeklenenleritekrarara').removeAttr('disabled');
                $('#kampanyabeklenenleritekrarara').attr('data-value',result.kampanyaid);
            }
            $('#kampanya_tablo_tum_katilimci').DataTable().destroy();
            $('#kampanya_tablo_tum_katilimci').DataTable({
                 autoWidth: false,
                  responsive: true,
                  paging: false,info: false,
                   columns:[
                      { data: 'ad_soyad'   },
                      { data: 'telefon' },
                      { data: 'durum' },
                   ],
                   data: result.katilimcilar,
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'
                       }
                    },
                   dom: '<"custom-info-tum">frtip', // Burada "custom-info" adlı özel bir div oluşturuyoruz
                    initComplete: function () {
                        var totalRecords = this.api().page.info().recordsTotal; // Toplam kayıt sayısını al
                        $(".custom-info-tum").html('<div style="position: absolute; left: 0; font-weight: bold;">Toplam Katılımcı: ' + totalRecords + '</div>');
                    }
            });
            $('#kampanya_tablo_katilanlar_katilimci').DataTable().destroy();
            $('#kampanya_tablo_katilanlar_katilimci').DataTable({
                 autoWidth: false,
                  responsive: true,
                  paging: false,info: false,
                   columns:[
                      { data: 'ad_soyad'   },
                      { data: 'telefon' },
                   ],
                   data: result.katilimcilar_katilanlar,
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'
                       }
                    },
                    dom: '<"custom-info-katilan">frtip', // Burada "custom-info" adlı özel bir div oluşturuyoruz
                    initComplete: function () {
                        var totalRecords = this.api().page.info().recordsTotal; // Toplam kayıt sayısını al
                        $(".custom-info-katilan").html('<div style="position: absolute; left: 0; font-weight: bold;">Toplam Katılımcı: ' + totalRecords + '</div>');
                    }

            });
            $('#kampanya_tablo_katilmayanlar_katilimci').DataTable().destroy();
            $('#kampanya_tablo_katilmayanlar_katilimci').DataTable({
                 autoWidth: false,
                  responsive: true,
                  paging: false,info: false,
                   columns:[
                      { data: 'ad_soyad'   },
                      { data: 'telefon' },
                   ],
                   data: result.katilimcilar_katilmayanlar,
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
                   dom: '<"custom-info-katilmayan">frtip', // Burada "custom-info" adlı özel bir div oluşturuyoruz
                    initComplete: function () {
                        var totalRecords = this.api().page.info().recordsTotal; // Toplam kayıt sayısını al
                        $(".custom-info-katilmayan").html('<div style="position: absolute; left: 0; font-weight: bold;">Toplam Katılımcı: ' + totalRecords + '</div>');
                    }
            });
            $('#kampanya_tablo_beklenen_katilimci').DataTable().destroy();
            $('#kampanya_tablo_beklenen_katilimci').DataTable({
                 autoWidth: false,
                  responsive: true,
                  paging: false,info: false,
                   columns:[
                      { data: 'ad_soyad'   },
                      { data: 'telefon' },
                   ],
                   data: result.katilimcilar_beklenen,
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'
                       }
                    },
                   dom: '<"custom-info-beklenen">frtip', // Burada "custom-info" adlı özel bir div oluşturuyoruz
                    initComplete: function () {
                        var totalRecords = this.api().page.info().recordsTotal; // Toplam kayıt sayısını al
                        $(".custom-info-beklenen").html('<div style="position: absolute; left: 0; font-weight: bold;">Toplam Katılımcı: ' + totalRecords + '</div>');
                    }
              });


             $('#kampanya_tablo_tum_katilimci_arama').DataTable().destroy();
            $('#kampanya_tablo_tum_katilimci_arama').DataTable({
                 autoWidth: false,
                  responsive: true,
                  paging: false,info: false,
                   columns:[
                      { data: 'ad_soyad'   },
                      { data: 'telefon' },
                      { data: 'durum' },
                   ],
                   data: result.katilimcilar_arama,
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'
                       }
                    },
                    dom: '<"custom-info-tum-arama">frtip', // Burada "custom-info" adlı özel bir div oluşturuyoruz
                    initComplete: function () {
                        var totalRecords = this.api().page.info().recordsTotal; // Toplam kayıt sayısını al
                        $(".custom-info-tum-arama").html('<div style="position: absolute; left: 0; font-weight: bold;">Toplam Katılımcı: ' + totalRecords + '</div>');
                    }
            });
            $('#kampanya_tablo_katilanlar_katilimci_arama').DataTable().destroy();
            $('#kampanya_tablo_katilanlar_katilimci_arama').DataTable({
                 autoWidth: false,
                  responsive: true,
                  paging: false,info: false,
                   columns:[
                      { data: 'ad_soyad'   },
                      { data: 'telefon' },
                   ],
                   data: result.katilimcilar_katilanlar_arama,
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'
                       }
                    },
                   dom: '<"custom-info-katilan-arama">frtip', // Burada "custom-info" adlı özel bir div oluşturuyoruz
                    initComplete: function () {
                        var totalRecords = this.api().page.info().recordsTotal; // Toplam kayıt sayısını al
                        $(".custom-info-katilan-arama").html('<div style="position: absolute; left: 0; font-weight: bold;">Toplam Katılımcı: ' + totalRecords + '</div>');
                    }
            });
            console.log(result.katilimcilar_katilmayanlar_arama);
            $('#kampanya_tablo_katilmayanlar_katilimci_arama').DataTable().destroy();
            $('#kampanya_tablo_katilmayanlar_katilimci_arama').DataTable({
                 autoWidth: false,
                  responsive: true,
                  info: false,
                  paging: false,
                  columns:[
                      { data: 'ad_soyad'   },
                      { data: 'telefon' },
                   ],
                   data: result.katilimcilar_katilmayanlar_arama,
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'
                   }
                },
                dom: '<"custom-info-katilmayan-arama">frtip', // Burada "custom-info" adlı özel bir div oluşturuyoruz
                    initComplete: function () {
                        var totalRecords = this.api().page.info().recordsTotal; // Toplam kayıt sayısını al
                        $(".custom-info-katilmayan-arama").html('<div style="position: absolute; left: 0; font-weight: bold;">Toplam Katılımcı: ' + totalRecords + '</div>');
                    }
            });
            $('#kampanya_tablo_beklenen_katilimci_arama').DataTable().destroy();
            $('#kampanya_tablo_beklenen_katilimci_arama').DataTable({
                 autoWidth: false,
                  responsive: true,
                  paging: false,
                  info: false,
                   columns:[
                      { data: 'ad_soyad'   },
                      { data: 'telefon' },
                   ],
                   data: result.katilimcilar_beklenen_arama,
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'
                       }
                    },
                    dom: '<"custom-info-beklenen-arama">frtip', // Burada "custom-info" adlı özel bir div oluşturuyoruz
                    initComplete: function () {
                        var totalRecords = this.api().page.info().recordsTotal; // Toplam kayıt sayısını al
                        $(".custom-info-beklenen-arama").html('<div style="position: absolute; left: 0; font-weight: bold;">Toplam Katılımcı: ' + totalRecords + '</div>');
                    }
              });
        },
        error: function (request, status, error) {
             $('#preloader').hide();
             document.getElementById('hata').innerHTML ='hata : '+error+' '+request.responseText;
        }
    });
});
$(document).on('click','a[name="etkinlik_detay"]',function(e){
    e.preventDefault();
    var etkinlik_id = $(this).attr('data-value');
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/etkinlikdetay',
        data: {etkinlikid:etkinlik_id,sube:$('input[name="sube"]').val()},
        dataType: "json",
        beforeSend: function(){
                $('#preloader').show();
            },
        success: function(result)  {
            console.log(result.katilimcilar);
            $('#preloader').hide();
            $('#etkinlik_tarih').empty();
            $('#etkinlik_tarih').append(result.etkinlik.tarih);
            $('#etkinlik_adi').empty();
            $('#etkinlik_adi').append(result.etkinlik.etkinlik_adi);
            $('#etkinlik_katilimci').empty();
            $('#etkinlik_katilimci').append(result.etkinlik.katilimci_sayisi);
            $('#toplam_tutar').empty();
            $('#toplam_tutar').append(result.etkinlik.fiyat);
            if(result.beklenen_count==0){
                $('#etkinlikbeklenenleresmsgonder').attr('disabled',true);
            }
            else{
                $('#etkinlikbeklenenleresmsgonder').removeAttr('disabled');
                $('#etkinlikbeklenenleresmsgonder').attr('data-value',result.etkinlikid);
            }
            $('#etkinlik_tablo_tum_katilimci').DataTable().destroy();
            $('#etkinlik_tablo_tum_katilimci').DataTable({
                 autoWidth: false,
                  responsive: true,
                   columns:[
                      { data: 'ad_soyad'   },
                      { data: 'telefon' },
                      { data: 'durum' },
                   ],
                   data: result.katilimcilar,
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'
                       }
                    },
              });
               $('#etkinlik_tablo_katilanlar_katilimci').DataTable().destroy();
            $('#etkinlik_tablo_katilanlar_katilimci').DataTable({
                 autoWidth: false,
                  responsive: true,
                   columns:[
                      { data: 'ad_soyad'   },
                      { data: 'telefon' },
                   ],
                   data: result.katilimcilar_katilanlar,
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'
                       }
                    },
              });
             $('#etkinlik_tablo_katilmayanlar_katilimci').DataTable().destroy();
            $('#etkinlik_tablo_katilmayanlar_katilimci').DataTable({
                 autoWidth: false,
                  responsive: true,
                   columns:[
                      { data: 'ad_soyad'   },
                      { data: 'telefon' },
                   ],
                   data: result.katilimcilar_katilmayanlar,
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'
                       }
                    },
              });
               $('#etkinlik_tablo_beklenen_katilimci').DataTable().destroy();
            $('#etkinlik_tablo_beklenen_katilimci').DataTable({
                 autoWidth: false,
                  responsive: true,
                   columns:[
                      { data: 'ad_soyad'   },
                      { data: 'telefon' },
                   ],
                   data: result.katilimcilar_beklenen,
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
             document.getElementById('hata').innerHTML ='hata : '+error+' '+request.responseText;
        }
    });
});
function seanstarihguncelle(dateText,seansid)
{
    var seanssayfasi = false;
    if($.trim($("#seans_detayi").html())!='')
        seanssayfasi = true;
    if($("input[name='musteri_id']").length)
        musterisayfasi = true;
     $.ajax({
                type: "POST",
                url: '/isletmeyonetim/seansgirdiguncelle',
                dataType: "json",
                data : {id:seansid,tarih:dateText,visibility:true,seans_sayfa:seanssayfasi,sube:$('input[name="sube"]').val()},
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                beforeSend: function() {
                        $("#preloader").show();
            },
                success: function(result)  {
                   $("#preloader").hide();
                     if($.trim($("#seans_detayi").html())==''){
                                                    $('#adisyon_detay_paket_tablo').empty();
                                                    $('#adisyon_detay_paket_tablo').append(result.html);
                                                     select2ozellikyukle('#adisyon_detay_paket_tablo');
                    }
                    if($.trim($("#seans_detayi").html())!=''){
                                                     $('#seans_detayi').empty();
                                                    $('#seans_detayi').append(result.html);
                                                     select2ozellikyukle('#seans_detayi');
                    }
                     select2ozellikyukle('#adisyon_detay_paket_tablo');
                    $('input[name="seans_tarihi_adisyon_paket"]').each(function(index){
                        //$(this).datepicker('destroy');
                        $(this).datepicker({
                            language: "tr",
                            autoClose: true,
                            dateFormat: "yyyy-mm-dd",
                            onSelect: function(dateText2) {
                                seanstarihguncelle(dateText2,this.value);
                            }
                        });
                    });
                },
                error: function (request, status, error) {
                    $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                }
            });
}
$(document).on('click','a[name="randevu_duzenle"]',function(){
    $.ajax({
                type: "GET",
                url: '/isletmeyonetim/randevudetayigetir',
                dataType: "json",
                data : {randevu_id:$(this).attr('data-value'),sube:$('input[name="sube"]').val(),hizmetId:$(this).attr('data-index-number')},
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                beforeSend: function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                    $('#preloader').hide();
                    $("#randevuduzenle_musteri_id").empty();
                    $('#randevuduzenle_musteri_id').append(result.musteri);
                    $('#duzenlenecek_randevu_id').val(result.randevu_id);
                    $("#randevuduzenle_tarih").val(result.randevu_tarih);
                    console.log(result.randevu_saat);
                    if ($('#randevuduzenle_saat option[value="' + result.randevu_saat + '"]').length === 0) {
                        $('#randevuduzenle_saat').append('<option value="'+result.randevu_saat+'">'+result.randevu_saat+'</option>');
                    }
                    $('#randevuduzenle_saat').val(result.randevu_saat).trigger('change');
                    console.log("Seçilen Değer: ", $('#randevuduzenle_saat').val());
                    $('#randevuduzenle_personel_notu').val(result.randevu_notu);
                    if(result.sms_hatirlatma)
                        $('#randevuduzenle_sms_hatirlatma').prop('checked', true);
                    else
                        $('#randevuduzenle_sms_hatirlatma').prop('checked', false);
                    $('.hizmetler_bolumu_randevu_duzenleme').empty();
                    $('.hizmetler_bolumu_randevu_duzenleme').append(result.randevu_hizmetler);
                    $('.hizmetler_bolumu_randevu_duzenleme').find(".custom-select2").each(function(index){
                        $(this).select2();
                    });
                    $('.hizmetler_bolumu_randevu_duzenleme').find(".opsiyonelSelect").each(function(index){
                        $(this).select2({
                            placeholder: "Seçiniz",
                            allowClear:true
                        });
                    });
                    $('#randevu-duzenle-modal').modal();
                    console.log(result);
                },
                error: function (request, status, error) {
                        $('#preloader').hide();
                        document.getElementById('hata').innerHTML = request.responseText + ' '+ error;
                }
            });
});
   
$('#grup_sms_formu_duzenle').on('submit',function(e){
    e.preventDefault();
        var formData = new FormData();
        var data1 = $('#grup_sms_formu_duzenle').serializeArray();
        $.each(data1,function(key,input){
            formData.append(input.name,input.value);
        });
         $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/grupsmsekleduzenle',
                    dataType: "json",
                    data:formData,
                      headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
                    processData: false,
                    contentType: false,
                    beforeSend:function(){
                        $('#preloader').show();
                  },
                    success: function(result)  {
                         $('#preloader').hide();
                         $('button[data-dismiss="modal"]').trigger('click');
                        swal(
                            {
                                type: "success",
                                title: "Başarılı",
                                text:  result.mesaj,
                                  showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                            }
                        );
                        $('#grup_sms_tablo').DataTable().destroy();
                        $('#grup_sms_tablo').DataTable({
                             columns:[
                                 { data: 'grup_adi', className: "text-center",   },
                      { data: 'grup_katilimci_sayisi',className: "text-center", },
                      { data: 'islemler',className: "text-right"  },
                        ],
                        data: result.grup,
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
$(document).on('click','a[name=grup_sms_gonder]',function(){
    var tds = $(this).closest('tr').children('td');
    $('#smsgrupid').val($(this).attr('data-value'));
    $('#sms_grup_adi').val(tds[0].innerHTML);
});
$('#sablon_sec_grup_sms').change(function(){
    $('#grup_mesaj').val($(this).val());
});


$('#grup_sms_tablo').on('click','a[name="grup_duzenle"]', function(e){
    var tds = $(this).closest('tr').children('td');
    var id = $(this).attr('data-value');
     
            $('#preloader').hide();
            $('#grup_adi').val(tds[0].innerHTML);
            $('#grup_duzenle_grup_id').val(id);
            $('#grup_musteri_liste').empty();
            $('#grup_musteri_liste').append(result);
            $ ('select[name="duallistbox_demo1[]"]').bootstrapDualListbox({
                removeAllLabel: 'Hepsini Kaldır',
                moveAllLabel: 'Tümünü Seç',
                removeAllLabel:'Tümünü Kaldır',
                infoText: '{0} kişi',
                infoTextEmpty: 'Boş müşteri listesi',
                filterPlaceHolder: 'Müşteri Ara',
            });
        
    
});
$('#grup_sms_tablo').on('click','a[name="grup_sil"]',function(e){
    e.preventDefault();
    var grupid = $(this).attr('data-value');
    swal({
        title: "Emin misiniz?",
        text: "Kaldırma işlemi geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Grubu kaldır',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
                $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/grupsil',
                    dataType: "json",
                    data : {grup_id:grupid,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        $('#grup_sms_formu')[0].reset();
                        $('#modal_kapat').trigger('click');
                        swal(
                            {
                                type: 'success',
                                title: 'Başarılı',
                                text: 'SMS grubu başarıyla kaldırıldı',
                                 showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,    
                            }
                        );
                        $('#grup_sms_tablo').DataTable().destroy();
                        $('#grup_sms_tablo').DataTable({
                                columns:[
                      { data: 'grup_adi', className: "text-center",   },
                      { data: 'grup_katilimci_sayisi',className: "text-center", },
                      { data: 'islemler',className: "text-right"  },
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
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
                });
        }
    });
});
$('#grup_olustur_buton').click(function(){
    $('#grup_duzenle_grup_id').val('');
});
$('select[name="paketadi[]"]').change(function(e){
    var fiyat_text = $(this).closest('div .row').find('input[name="paketfiyat[]"]');
    paketid = $(this).val();
    $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/paketfiyatgetir',
                    dataType: "text",
                    data : {paket_id:paketid,sube:$('input[name="sube"]').val()},
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
$(document).on('change', 'select[name="paketadiadisyon[]"]' ,function(e){
    var fiyat_text = $(this).closest('div .row').find('input[name="paketfiyatadisyon[]"]');
    paketid = $(this).val();
    $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/paketfiyatgetir',
                    dataType: "text",
                    data : {paket_id:paketid,sube:$('input[name="sube"]').val()},
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
$(document).on('change', 'select[name="paketadisenet[]"]' ,function(e){
    var fiyat_text = $(this).closest('div .row').find('input[name="paketfiyatsenet[]"]');
    paketid = $(this).val();
    $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/paketfiyatgetir',
                    dataType: "text",
                    data : {paket_id:paketid,sube:$('input[name="sube"]').val()},
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
$('#adisyon_musteriye_gore_filtrele').change(function(){
  adisyonlistefiltre();
});
function adisyonlistefiltre()
{
    $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/adisyon-filtreli-getir',
                    dataType: "json",
                    data : {musteri_id:$('#adisyon_musteriye_gore_filtrele').val(),tariharaligi:$('#adisyon_tarihe_gore_filtre').val(),sube:$('input[name="sube"]').val()},
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                        $("#preloader").hide();
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
                          { data: 'durum'},
                           { data: 'acilis_tarihi'},
                           { data: 'planlanan_alacak_tarihi'},
                           { data: 'musteri'},
                       
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
                          { data: 'durum'},
                           { data: 'acilis_tarihi'},
                           { data: 'planlanan_alacak_tarihi'},
                           { data: 'musteri'},
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
                          { data: 'durum'},
                           { data: 'acilis_tarihi'},
                           { data: 'planlanan_alacak_tarihi'},
                           { data: 'musteri'},
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
                          { data: 'durum'},
                           { data: 'acilis_tarihi'},
                           { data: 'planlanan_alacak_tarihi'},
                           { data: 'musteri'},
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
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
}
$('#personel_rapor_ay,#personel_rapor_yil').change(function(e){
    adisyonlistefiltrepersonel();
    $('#personel_rapor_tarih_araligi_1').val('');
    $('#personel_rapor_tarih_araligi_2').val('');
});
function adisyonlistefiltrepersonel()
{
    $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/adisyon-filtreli-getir-personel',
                    dataType: "json",
                    data : {ay:$('#personel_rapor_ay').val(),yil:$('#personel_rapor_yil').val(),satisturu:$('#adisyon_icerigi_personel').val(),personel_id:$('#personel_id').val(),tarih1:$('#personel_rapor_tarih_araligi_1').val(),tarih2:$('personel_rapor_tarih_araligi_2').val(),sube:$('input[name="sube"]').val()},
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                       $("#preloader").hide();
                       $('#hizmet_satisi').empty();
                       $('#hizmet_primi').empty();
                       $('#urun_satisi').empty();
                       $('#urun_primi').empty();
                       $('#paket_satisi').empty();
                       $('#paket_primi').empty();
                       $('#toplam_hakedis').empty();
                       $('#hizmet_satisi').append(result.hizmet_satisi);
                       $('#hizmet_primi').append(result.hizmet_primi);
                       $('#urun_satisi').append(result.urun_satisi);
                       $('#urun_primi').append(result.urun_primi);
                       $('#paket_satisi').append(result.paket_satisi);
                       $('#paket_primi').append(result.paket_primi);
                       $('#toplam_hakedis').append(result.toplam_hakedis);
                       $('#adisyon_liste_personel').DataTable().destroy();
                        $('#adisyon_liste_personel').DataTable({
                               autoWidth: false,
                               responsive: true,
                               columns:[
                                   { data: 'acilis_tarihi'},
                                   { data: 'musteri'},
                                   
                                   { data: 'icerik'},
                                   {data : 'toplam'},
                                   {data : 'odenen'},
                                   {data : 'kalan_tutar'},
                                   {data : 'hakedis'},
                               ],
                               data: result.adisyonlar,
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
$('#otomatik_sms_ayarlari').on('submit',function(e){
    e.preventDefault();
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/sms-ayar-kaydet',
                    dataType: "text",
                    data : $(this).serialize(),
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                       $("#preloader").hide();
                        swal({
                            type: "success",
                            title: "SMS ayarları başarıyla kaydedildi.",
                            text:  result.status,
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                        });
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$('#santral_ayarlari').on('submit',function(e){
    e.preventDefault();
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/santral-ayar-kaydet',
                    dataType: "text",
                    data : $(this).serialize(),
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                       $("#preloader").hide();
                        swal({
                            type: "success",
                            title: "Santral ayarları başarıyla kaydedildi.",
                            text:  result.status,
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                        });
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$('input[name="musteri_telefon"]').change(function(){
    $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/musterihesapvar',
                    dataType: "json",
                    data : $(this).serialize(),
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
// grup_sms_formu submit handler musteriListeSecimi.js icinde tanimli
$('#sendbutton').click(function(e){
    e.preventDefault();
               $.ajax({
                type: "POST",
                url: '/isletmeyonetim/grupsmsgonder',
                dataType: "json",
                data : $('#grup_sms_gonderme_formu').serialize(),
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
                     $('#grup_mesaj').val('');
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
            });
  });
$(document).on('click','button[name=grup_sms_gonder]',function(){
    var tds = $(this).closest('tr').children('td');
    $('#smsgrupid').val($(this).attr('data-value'));
    $('#sms_grup_adi').val(tds[0].innerHTML);
});
$('#sablon_sec_grup_sms').change(function(){
    $('#grup_mesaj').val($(this).val());
});
$('#filtre_sablon_sec').change(function(){
    $('#filtre_sms').val($(this).val());
});
$('#grup_sms_tablo').on('click','button[name="grup_duzenle"]', function(e){
    var tds = $(this).closest('tr').children('td');
    var id = $(this).attr('data-value');
    $.ajax({
        type:'POST',
        url:'/isletmeyonetim/grupduzenle',
        dataType:"text",
        data:{grup_id:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
                              headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        beforeSend: function(){
            $('#preloader').show();
        },
        success: function(result){
            $('#preloader').hide();
            $('#grup_modal_baslik').empty();
            $('#grup_modal_baslik').append("Grup Düzenle");
            $('#grup_ad').val(tds[0].innerHTML);
            $('#grup_duzenle_grup_id').val(id);
            $('#grup_musteri_liste_duzenle').empty();
            $('#grup_musteri_liste_duzenle').append(result);
            $ ('#grup_musteri_liste_duzenle').bootstrapDualListbox({
                removeAllLabel: 'Hepsini Kaldır',
                moveAllLabel: 'Tümünü Seç',
                removeAllLabel:'Tümünü Kaldır',
                infoText: '{0} kişi',
                infoTextEmpty: 'Boş müşteri listesi',
                filterPlaceHolder: 'Müşteri Ara',
              });
        },
        error: function(request, status,error){
            $('#preloader').hide();
            document.getElementById('hata').innerHTML=request.responseText;
        }
    });
});
$('#grup_sms_tablo').on('click','button[name="grup_sil"]',function(e){
    e.preventDefault();
    var grupid = $(this).attr('data-value');
    swal({
        title: "Emin misiniz?",
        text: "Kaldırma işlemi geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Grubu kaldır',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
                $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/grupsil',
                    dataType: "json",
                    data : {grup_id:grupid,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        $('#grup_sms_formu')[0].reset();
                        $('#modal_kapat').trigger('click');
                        swal(
                            {
                                type: 'success',
                                title: 'Başarılı',
                                text: 'Grup başarıyla kaldırıldı',
                            }
                        );
                        $('#grup_sms_tablo').DataTable().destroy();
                        $('#grup_sms_tablo').DataTable({
                                columns:[
                    { data: 'grup_adi', className: "text-center",   },
                      { data: 'grup_katilimci_sayisi',className: "text-center", },
                      { data: 'islemler',className: "text-right"  },
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
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
                });
        }
    });
});
$('#grup_olustur_buton').click(function(){
    $('#grup_duzenle_grup_id').val('');
});
$('#sablonlar').change(function(){
    $('#smsmesaj').val($(this).val());
});
$(document).on('change','#filtre_cinsiyet',function(e){
    e.preventDefault();
    var selectedGender=$("#filtre_cinsiyet").val();
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/cinsiyetegore',
        dataType: "text",
        data: { cinsiyet: selectedGender , sube:$('input[name="sube"]').val()},
 beforeSend: function() {
                        $("#preloader").show();
                    },
        success: function(result){
             $("#preloader").hide();
            $('#filtrelimusteri').empty();
            $('#filtrelimusteri').append(result);
             $ ('#filtrelimusteri').bootstrapDualListbox('refresh',true);
             $ ('#filtrelimusteri').bootstrapDualListbox({
                moveAllLabel: 'Hepsini Seç',
                removeAllLabel: 'Hepsini Kaldır'
              });
        },
         error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$(document).on('change','#filtrehizmetsecim',function(e){
    e.preventDefault();
    var selectedhizmet=$("#filtrehizmetsecim").val();
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/hizmetegore',
        dataType: "text",
        data: { hizmet: selectedhizmet , sube:$('input[name="sube"]').val()},
 beforeSend: function() {
                        $("#preloader").show();
                    },
        success: function(result){
             $("#preloader").hide();
            $('#filtrelimusteri').empty();
            $('#filtrelimusteri').append(result);
             $ ('#filtrelimusteri').bootstrapDualListbox('refresh',true);
             $ ('#filtrelimusteri').bootstrapDualListbox({
                moveAllLabel: 'Hepsini Seç',
                removeAllLabel: 'Hepsini Kaldır'
              });
        },
         error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$('#filtrelismsgonder').click(function(e){
        e.preventDefault();
               $.ajax({
                type: "POST",
                url: '/isletmeyonetim/filtrelismsgonder',
                dataType: "json",
                data : $('#filtrelismsform').serialize(),
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
                     $('#filtre_sms').val('');
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
            });
  });
$('#filtre_cinsiyet,#filtrehizmetsecim').change(function(e){
    e.preventDefault();
 var selectedGender=$("#filtre_cinsiyet").val();
 var selectedhizmet=$("#filtrehizmetsecim").val();
    $.ajax({
 type: "GET",
        url: '/isletmeyonetim/cinsiyetehizmetegore',
        dataType: "text",
        data: { hizmet: selectedhizmet ,cinsiyet:selectedGender, sube:$('input[name="sube"]').val()},
 beforeSend: function() {
                        $("#preloader").show();
                    },
        success: function(result){
             $("#preloader").hide();
            $('#filtrelimusteri').empty();
            $('#filtrelimusteri').append(result);
             $ ('#filtrelimusteri').bootstrapDualListbox('refresh',true);
             $ ('#filtrelimusteri').bootstrapDualListbox({
                moveAllLabel: 'Hepsini Seç',
                removeAllLabel: 'Hepsini Kaldır'
              });
        },
         error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$('#musteri_tablo,#musteri_tablo_sadik,#musteri_tablo_aktif,#musteri_tablo_pasif').on('click','a[name="musteri_duzenle"]',function(e){
    $('input[name="musteri_id"]:eq(1)').val($(this).attr('data-value'));
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/musteridetaybilgi',
        dataType: "json",
        data: { musteriid: $(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result){
            $("#preloader").hide();
            $('.musteri_bilgi_formu input[name="ad_soyad"]').eq(1).val(result.ad_soyad);
            $('.musteri_bilgi_formu input[name="telefon"]').eq(1).val(result.cep_telefon);
            $('.musteri_bilgi_formu input[name="email"]').eq(1).val(result.eposta);
            $('.musteri_bilgi_formu select[name="dogum_tarihi_gun"]').eq(1).val(result.dogum_tarihi_gun).change();
            $('.musteri_bilgi_formu select[name="dogum_tarihi_ay"]').eq(1).val(result.dogum_tarihi_ay).change();
            $('.musteri_bilgi_formu select[name="dogum_tarihi_yil"]').eq(1).val(result.dogum_tarihi_yil).change();
            $('.musteri_bilgi_formu input[name="tc_kimlik_no"]').eq(1).val(result.tc);
            $('.musteri_bilgi_formu select[name="cinsiyet"]').eq(1).val(result.cinsiyet);
            $('.musteri_bilgi_formu select[name="musteri_referans"]').eq(1).val(result.referans);
            $('.musteri_bilgi_formu select[name="ozel_notlar"]').eq(1).val(result.notlar);
            $('.musteri_bilgi_formu input[name="eklendi_yanit_goster"]').eq(1).val(1);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('.table').on('click','button[name="paket_seans_detay_getir_modal"]',function(){
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/seansdetaylari',
        dataType: "text",
        data: { adisyonpaketid: $(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result){
            $("#preloader").hide();
            $('#seans_detayi').empty();
            $('#seans_detayi').append(result);
            /*$('input[name="seans_tarihi_adisyon_paket"]').each(function(index){
                                                //$(this).datepicker('destroy');
                                                $(this).datepicker({
                                                    language: "tr",
                                                    autoClose: true,
                                                    dateFormat: "yyyy-mm-dd",
                                                    onSelect: function(dateText) {
                                                         seanstarihguncelle(dateText,this.value);
                                                        //alert($(this).attr('data-value')+ ' nolu seans için tarih '+dateText+ ' olarak değiştirildi');
                                                    }
                                                });
            });*/
            $('#seans_detay_ac').trigger('click');
        },
        error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('.table').on('click','button[name="hizmet_seans_detay_getir_modal"]',function(){
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/seansdetaylari',
        dataType: "text",
        data: { adisyonhizmetid: $(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result){
            $("#preloader").hide();
            $('#seans_detayi').empty();
            $('#seans_detayi').append(result);
            /*$('input[name="seans_tarihi_adisyon_paket"]').each(function(index){
                                                //$(this).datepicker('destroy');
                                                $(this).datepicker({
                                                    language: "tr",
                                                    autoClose: true,
                                                    dateFormat: "yyyy-mm-dd",
                                                    onSelect: function(dateText) {
                                                         seanstarihguncelle(dateText,this.value);
                                                        //alert($(this).attr('data-value')+ ' nolu seans için tarih '+dateText+ ' olarak değiştirildi');
                                                    }
                                                });
            });*/
            $('#seans_detay_ac').trigger('click');
        },
        error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('table').on('click','a[name="senet_vadeleri"]', function(e){
    var senetid = $(this).attr('data-value');
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/senetvadegetir',
        dataType: "text",
        data : {id:senetid,sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            $('#senet_vade_listesi').empty();
            $('#senet_vade_listesi').append(result);
            $('#senet_id').val(senetid);
            $('#senet_detay_modal_ac').trigger('click');
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('click','button[name="senet_vadesi"]',function(){
    
    $('#vade_odeme_tarihi').val($('input[name="senet_vade_tarihi"][data-value="'+$(this).attr('data-value')+'"]').val());
    $('#vade_id').val($(this).attr('data-value'));
});
$(document).on('click','a[name="taksit_vadesi"]',function(){
    $('#taksit_vade_odeme_tarihi').val($('input[name="taksit_vade_tarihi"][data-value="'+$(this).attr('data-value')+'"]').val());
    $('#taksit_vade_id').val($(this).attr('data-value'));
});
$('#vade_guncelle').click(function(e){
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/senetvadeguncelle',
        dataType: "json",
        data : $('#senet_vade_odeme_guncelleme').serialize(),
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            $('#senet_odeme_ekrani_kapat').trigger('click');
            $('#senet_vade_listesi').empty();
            $('#senet_vade_listesi').append(result.vadeler);
            senetlisteguncelle(result);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#taksit_vade_guncelle').click(function(e){
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/taksitvadeguncelle',
        dataType: "json",
        data : $('#taksit_vade_guncelleme').serialize(),
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            $('#taksit_odeme_ekrani_kapat').trigger('click');
            $('#taksit_vade_listesi').empty();
            $('#taksit_vade_listesi').append(result.vadeler);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
function senet_odeme_islemleri(vade_odeme_yontemi,dogrulama_kodu)
{
            var formdata = new FormData();
            formdata.append('odeme_yontemi',vade_odeme_yontemi);
            formdata.append('dogrulama_kodu',dogrulama_kodu);
            if($('input[name="adisyon_id"]').length)
                formdata.append('adisyon_id',$('input[name="adisyon_id"]').val());
            var other_data = $('#senet_vade_odeme_guncelleme').serializeArray();
            $.each(other_data,function(key,input){
                formdata.append(input.name,input.value);
            });
            $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/senetvadeodemeyitamamla',
                    dataType: "json",
                    data : formdata,
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
                     if(result.status=='hatalikod')
                     {
                        swal({
                            title: "Ödeme Yöntemi",
                            html: "<p>Devam etmek için lütfen ödeme yöntemini seçip müşterinin telefon numarasına gönderilen doğrulama kodunu giriniz.</p><select id='vade_odeme_yontemi' class='form-control'><option value='1'>Nakit</option><option value='2'>Kredi Kartı</option><option value='3'>Havale / EFT</option></select><br><input type='text' id='senet_odeme_dogrulama_kodu' class='form-control' placeholder='Doğrulama Kodu'>",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonColor: '#00bc8c',
                            confirmButtonText: 'Ödeme Yap',
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                        }).then(function (result) {
                            if(result.value){
                                senet_odeme_islemleri($('#vade_odeme_yontemi').val(),$('#senet_odeme_dogrulama_kodu').val());
                            }
                        });
                     }
                     else
                     {
                            $('button[data-dismiss="modal"]').trigger('click');
                           senetlisteguncelle(result);
                     }
                    $('button[data-dismiss="modal"]').trigger('click');
                    if( $('#tahsilat_listesi').length){
                        $('#tahsilat_listesi').empty();
                        $('#tahsilat_listesi').append(result.tahsilatlar.html);
                        $('#tahsil_edilen_tutar').empty();
                        $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').empty();
                        $('#tahsil_edilen_tutar').append(result.tahsilatlar.tahsilat_tutari);
                        $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').append(result.tahsilatlar.kalan_tutar);
                        if(parseFloat(result.kalan_tutar) == parseFloat(0)){
                                $('#yeni_tahsilat_ekle').attr('disabled','true');
                        }
                    }
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
            });
}
function taksit_odeme_islemleri(vade_odeme_yontemi,dogrulama_kodu,formveri)
{
            let formdata;
            if(formveri == null)
                formdata = new FormData();
            else
                formdata = formveri;
            formdata.append('odeme_yontemi',vade_odeme_yontemi);
            formdata.append('dogrulama_kodu',dogrulama_kodu);
            if($('input[name="adisyon_id"]').length)
                formdata.append('adisyon_id',$('input[name="adisyon_id"]').val());
            $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/taksitvadeodemeyitamamla',
                    dataType: "json",
                    data : formdata,
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
                     if(result.status=='hatalikod')
                     {
                        swal({
                            title: "Ödeme Yöntemi",
                            html: "<p>Devam etmek için lütfen ödeme yöntemini seçip müşterinin telefon numarasına gönderilen doğrulama kodunu giriniz.</p><select id='taksit_vade_odeme_yontemi' class='form-control'><option value='1'>Nakit</option><option value='2'>Kredi Kartı</option><option value='3'>Havale / EFT</option></select><br><input type='text' id='taksit_odeme_dogrulama_kodu' class='form-control' placeholder='Doğrulama Kodu'>",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonColor: '#00bc8c',
                            confirmButtonText: 'Ödeme Yap',
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                        }).then(function (result) {
                            if(result.value){
                                taksit_odeme_islemleri($('#taksit_vade_odeme_yontemi').val(),$('#taksit_odeme_dogrulama_kodu').val(),formveri);
                            }
                        });
                     }
                     else
                     {
                            $('button[data-dismiss="modal"]').trigger('click');
                     }
                    $('button[data-dismiss="modal"]').trigger('click');
                    if($('#tahsilat_ekrani').length){
                          $('#tum_tahsilatlar').empty();
                            $('#tum_tahsilatlar').append(result.tahsilatlar.kalemler);
                            $('#tahsilat_listesi').empty();
                            $('#tahsilat_listesi').append(result.tahsilatlar.tahsilatlar);
                            tahsilatyenidenhesapla();
                    }
                    else{
                        if( $('#tahsilat_listesi').length){
                            $('#tahsilat_listesi').empty();
                            $('#tahsilat_listesi').append(result.tahsilatlar.html);
                            $('#tahsil_edilen_tutar').empty();
                            $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').empty();
                            $('#tahsil_edilen_tutar').append(result.tahsilatlar.tahsilat_tutari);
                            $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').append(result.tahsilatlar.kalan_tutar);
                            if(parseFloat(result.kalan_tutar) == parseFloat(0)){
                                $('#yeni_tahsilat_ekle').attr('disabled','true');
                            }
                        }
                    }
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
            });
}
function senetlisteguncelle(result)
{
    $('#senet_liste').DataTable().destroy();
    $('#senet_liste').DataTable({
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
        data: result.senetler,
        "order": [[ 5, "asc" ]],
        "language" : {
                                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                        searchPlaceholder: "Ara",
                                        paginate: {
                                            next: '<i class="ion-chevron-right"></i>',
                                            previous: '<i class="ion-chevron-left"></i>'
                                        }
        },
    });
    $('#senet_liste_acik').DataTable().destroy();
    $('#senet_liste_acik').DataTable({
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
        data: result.senetler_acik,
        "order": [[ 5, "asc" ]],
        "language" : {
                                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                        searchPlaceholder: "Ara",
                                        paginate: {
                                            next: '<i class="ion-chevron-right"></i>',
                                            previous: '<i class="ion-chevron-left"></i>'
                                        }
        },
    });
    $('#senet_liste_kapali').DataTable().destroy();
    $('#senet_liste_kapali').DataTable({
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
        data: result.senetler_kapali,
        "order": [[ 5, "asc" ]],
        "language" : {
                                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                        searchPlaceholder: "Ara",
                                        paginate: {
                                            next: '<i class="ion-chevron-right"></i>',
                                            previous: '<i class="ion-chevron-left"></i>'
                                        }
        },
    });
    $('#senet_liste_odenmemis').DataTable().destroy();
    $('#senet_liste_odenmemis').DataTable({
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
        data: result.senetler_odenmemis,
        "order": [[ 5, "asc" ]],
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
$('#vade_odendi_olarak_isaretle').click(function (e) {
   if($('#dogrulama_kodu_ayari').val()==1)
   {
    e.preventDefault();
   $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/senetodemedogrulamakodugonder',
                    dataType: "text",
                    data : $('#senet_vade_odeme_guncelleme').serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        swal({
                            title: "Ödeme Yöntemi",
                            html: "<p>Devam etmek için lütfen ödeme yöntemini seçip müşterinin telefon numarasına gönderilen doğrulama kodunu giriniz.</p><select id='vade_odeme_yontemi' class='form-control'><option value='1'>Nakit</option><option value='2'>Kredi Kartı</option><option value='3'>Havale / EFT</option></select><br><input type='text' id='senet_odeme_dogrulama_kodu' class='form-control' placeholder='Doğrulama Kodu'>",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonColor: '#00bc8c',
                            confirmButtonText: 'Ödeme Yap',
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                        }).then(function (result) {
                            if(result.value){
                                senet_odeme_islemleri($('#vade_odeme_yontemi').val(),$('#senet_odeme_dogrulama_kodu').val());
                            }
                        });
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
   }
   else
   {
                        swal({
                            title: "Ödeme Yöntemi",
                            html: "<p>Devam etmek için lütfen ödeme yöntemini seçiniz.</p><select id='vade_odeme_yontemi' class='form-control'><option value='1'>Nakit</option><option value='2'>Kredi Kartı</option><option value='3'>Havale / EFT</option></select>",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonColor: '#00bc8c',
                            confirmButtonText: 'Ödeme Yap',
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                        }).then(function (result) {
                            if(result.value){
                                senet_odeme_islemleri($('#vade_odeme_yontemi').val(),'');
                            }
                         });
   }
});
/*$('#taksit_vade_odendi_olarak_isaretle').click(function (e) {
   if($('#dogrulama_kodu_ayari').val()==1)
   {
    e.preventDefault();
   $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/taksitodemedogrulamakodugonder',
                    dataType: "text",
                    data : $('#taksit_vade_odeme_guncelleme').serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        swal({
                            title: "Ödeme Yöntemi",
                            html: "<p>Devam etmek için lütfen ödeme yöntemini seçip müşterinin telefon numarasına gönderilen doğrulama kodunu giriniz.</p><select id='taksit_vade_odeme_yontemi' class='form-control'><option value='1'>Nakit</option><option value='2'>Kredi Kartı</option><option value='3'>Havale / EFT</option></select><br><input type='text' id='taksit_odeme_dogrulama_kodu' class='form-control' placeholder='Doğrulama Kodu'>",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonColor: '#00bc8c',
                            confirmButtonText: 'Ödeme Yap',
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                        }).then(function (result) {
                            if(result.value){
                                taksit_odeme_islemleri($('#taksit_vade_odeme_yontemi').val(),$('#taksit_odeme_dogrulama_kodu').val());
                            }
                        });
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
   }
   else
   {
                        swal({
                            title: "Ödeme Yöntemi",
                            html: "<p>Devam etmek için lütfen ödeme yöntemini seçiniz.</p><select id='taksit_vade_odeme_yontemi' class='form-control'><option value='1'>Nakit</option><option value='2'>Kredi Kartı</option><option value='3'>Havale / EFT</option></select>",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonColor: '#00bc8c',
                            confirmButtonText: 'Ödeme Yap',
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                        }).then(function (result) {
                            if(result.value){
                                taksit_odeme_islemleri($('#taksit_vade_odeme_yontemi').val(),'');
                            }
                         });
   }
});*/
$('#tahsilat_listesi').on('click','button[name="tahsilat_duzenle"]',function(){
    $('#tahsilat_id').val($(this).attr('data-value'));
     $.ajax({
        type: "GET",
        url: '/isletmeyonetim/tahsilatdetaygetir',
        dataType: "json",
        data: {tahsilat_id:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result){
            $("#preloader").hide();
            $('#tahsilat_notlari').val(result[0].notlar);
            $('#tahsilat_tarihi').val(result[0].tarih);
            $('#tahsilat_tutari').val(result[0].tutar);
            $('#adisyon_tahsilat_odeme_yontemi').val(result[0].odeme_yontemi);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#zamana_gore_filtre_kasa,#odeme_yontemine_gore_filtre').change(function(e){
    if($('#zamana_gore_filtre_kasa').val()=='ozel')
    {
        $('#kasa_baslangic').attr('style','display:block');
        $('#kasa_bitis').attr('style','display:block');
    }
    else
    {
        $('#kasa_baslangic_tarihi').val('');
        $('#kasa_bitis_tarihi').val('');
        $('#kasa_baslangic').attr('style','display:none');
        $('#kasa_bitis').attr('style','display:none');
        kasaraporu('','');
    }
});
function kasaraporu(baslangic,bitis)
{
        $.ajax({
            type: "GET",
            url: '/isletmeyonetim/kasaraporufiltre',
            dataType: "json",
            data: {baslangic_bitis_tarihi:$('#zamana_gore_filtre_kasa').val(),odeme_yontemi:$('#odeme_yontemine_gore_filtre').val(),sube:$('input[name="sube"]').val(),kasa_baslangic_tarihi:baslangic,kasa_bitis_tarihi:bitis},
            beforeSend: function() {
                $("#preloader").show();
            },
            success: function(result){
                $("#preloader").hide();
                console.log(result);
                $('#kasa_gelir_tutari').empty();
                $('#kasa_gider_tutari').empty();
                $('#kasa_toplam_tutar').empty();
                $('#toplam_ciro_tutari').empty(); // TOPLAM CİRO EKLENDİ
                $('#tahsilatlar_listesi').empty();
                $('#masraflar_listesi').empty();
                $('#kasa_gelir_tutari').append(result.gelir);
                $('#kasa_gider_tutari').append(result.gider);
                $('#kasa_toplam_tutar').append(result.toplam);
                $('#toplam_ciro_tutari').append(result.toplam_ciro); // TOPLAM CİRO EKLENDİ
                $('#tahsilatlar_listesi').append(result.tahsilatlar);
                $('#masraflar_listesi').append(result.masraflar);
            },
            error: function (request, status, error) {
                $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
            }
        });
}
function cdrraporu(baslangic,bitis)
{
        $.ajax({
            type: "GET",
            url: '/isletmeyonetim/cdrraporugetir',
            dataType: "json",
            data: {sube:$('input[name="sube"]').val(),cdr_baslangic_tarihi:baslangic,cdr_bitis_tarihi:bitis},
            beforeSend: function() {
                $("#preloader").show();
            },
            success: function(result){
                $("#preloader").hide();
                console.log(result);
                $('#cevapsiz_arama_sayisi').empty();
                $('#gelen_arama_sayisi').empty();
                $('#giden_arama_sayisi').empty();
                $('#gelen_arama_sayisi').append(result.gelen_arama);
                $('#giden_arama_sayisi').append(result.giden_arama);
                $('#cevapsiz_arama_sayisi').append(result.cevapsiz_arama);
                $('#santral_arama_tum').DataTable().destroy();
                $('#santral_arama_tum').DataTable({
                     autoWidth: false,
                     responsive: true,
                    columns:[
                        {data: 'musteri' },
                        {data: 'telefon' },
                        {data: 'gorusmeyiyapan' },
                        {data: 'tarih' },
                        {data: 'saat'},
                        {data: 'durum' },
                        {data: 'seskaydi'},
                    ],
                    "order": [[ 3, "desc" ]],
                    data: result.rapor,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
               });
               $('#santral_giden_arama').DataTable().destroy();
               $('#santral_giden_arama').DataTable({
                     autoWidth: false,
                     responsive: true,
                     "search": {
                       "search": "GİDEN"
                    },
                    stateSave: true,
                    deferRender: true,
                    columns:[
                        {data: 'musteri' },
                        {data: 'telefon' },
                        {data: 'gorusmeyiyapan' },
                        {data: 'tarih' },
                        {data: 'saat'},
                        {data: 'durum' },
                        {data: 'seskaydi'},
                    ],
                    "order": [[ 3, "desc" ]],
                    data: result.rapor,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
               });
                $('#santral_gelen_arama').DataTable().destroy();
                $('#santral_gelen_arama').DataTable({
                       autoWidth: false,
                     responsive: true,
                      "search": {
                       "search": "GELEN"
                    },
                    stateSave: true,
                    deferRender: true,
                    columns:[
                        {data: 'musteri' },
                        {data: 'telefon' },
                        {data: 'gorusmeyiyapan' },
                        {data: 'tarih' },
                        {data: 'saat'},
                        {data: 'durum' },
                        {data: 'seskaydi'},
                    ],
                    "order": [[ 3, "desc" ]],
                    data: result.rapor,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
               });
               $('#santral_cevapsiz_arama').DataTable().destroy();
               $('#santral_cevapsiz_arama').DataTable({
                     autoWidth: false,
                     responsive: true,
                        "search": {
                        "search": "CEVAPSIZ"
                    },
                    stateSave: true,
                    deferRender: true,
                    columns:[
                        {data: 'musteri' },
                        {data: 'telefon' },
                        {data: 'gorusmeyiyapan' },
                        {data: 'tarih' },
                        {data: 'saat'},
                        {data: 'durum' },
                        {data: 'seskaydi'},
                    ],
                    "order": [[ 3, "desc" ]],
                    data: result.rapor,
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
$('button[name="kampanya_katilim"]').click(function(){
    $.ajax({
            type: "POST",
            url: '/kampanyakatilimanketicevapla',
            dataType: "json",
            data: {_token:$('input[name="_token"]').val(),userid:$('#user_id').val(),kampanyaid:$('#kampanya_id').val(),durum:$(this).attr('data-value')},
            beforeSend: function() {
                $("#preloader").show();
            },
            success: function(result){
                $("#preloader").hide();
                var cevap = '';
                if(result == 1)
                    cevap = 'Katılacağım';
                else
                    cevap = 'Katılmayacağım';
                 $('#kampanya_cevap').append(cevap);
                 $('#kampanya_anket_bolumu').attr('style','display:none');
                 $('#kampanya_anket_bolumu_cevap').attr('style','display:block');
            },
            error: function (request, status, error) {
                $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
            }
    });
});
$('button[name="etkinlik_katilim"]').click(function(){
    $.ajax({
            type: "POST",
            url: '/etkinlikkatilimanketicevapla',
            dataType: "json",
            data: {_token:$('input[name="_token"]').val(),userid:$('#user_id').val(),etkinlikid:$('#etkinlik_id').val(),durum:$(this).attr('data-value')},
            beforeSend: function() {
                $("#preloader").show();
            },
            success: function(result){
                $("#preloader").hide();
                var cevap = '';
                if(result == 1)
                    cevap = 'Katılacağım';
                else
                    cevap = 'Katılmayacağım';
                 $('#etkinlik_cevap').append(cevap);
                 $('#etkinklik_anket_bolumu').attr('style','display:none');
                 $('#etkinlik_anket_bolumu_cevap').attr('style','display:block');
            },
            error: function (request, status, error) {
                $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
            }
    });
});
/*$('select[name="kampanyapaketadi"]').change(function(e){
    var fiyat_text = $(this).closest('div .row').find('input[name="kampanyapaketfiyat"]');
    var paket_hizmet=$(this).closest('div .row').find('input[name="kampanyapakethizmet"]');
    var paket_seans=$(this).closest('div .row').find('input[name="kampanyapaketseans"]');
    paketid = $(this).val();
    $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/kampanyapaketfiyatgetir',
                    dataType: "json",
                    data : {paket_id:paketid,sube:$('input[name="sube"]').val()},
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                        $("#preloader").hide();
                        console.log(result);
                        fiyat_text.val(result.fiyat);
                        paket_hizmet.val(result.hizmetler);
                        paket_seans.val(result.seans);
                        $('#musteri_liste_hizmete_gore').empty();
                        $('#musteri_liste_hizmete_gore').append(result.hizmete_gore_musteri);
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});*/
$('#musteri_sms_kara_listeye_ekle').click(function(e){
    e.preventDefault();
    var musteriid=$(this).attr('data-value');
    swal({
         title: "Emin misiniz?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Kara listeye ekle',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result){
        if(result.value){
            $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/musterikaralisteayari',
                    dataType: "json",
                    data : {user_id:musteriid,_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val(),karaliste:1},
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                        $("#preloader").hide();
                        swal({
                        type: "success",
                        title: "Başarılı",
                        text:  "Müşteri SMS ve arama karalistesine başarıyla eklenmiş olup artık SMS ve arama bildirimlerini almayacaktır",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        });
                        $('#musteri_sms_kara_listeye_ekle').attr('style','display:none');
                        $('#musteri_sms_kara_listeden_cikar').attr('style','display:inline-block');
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
            });
        }
    });
});
$('#musteri_sms_kara_listeden_cikar').click(function(e){
    e.preventDefault();
    var musteriid=$(this).attr('data-value');
    swal({
         title: "Emin misiniz?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Kara listeden çıkar',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result){
        if(result.value){
            $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/musterikaralisteayari',
                    dataType: "text",
                    data : {user_id:musteriid,_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val(),karaliste:0},
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                        $("#preloader").hide();
                        swal({
                        type: "success",
                        title: "Başarılı",
                        timer:3000,
                        text:  "Müşteri SMS ve arama karalistesinden başarıyla çıkarılmış olup artık SMS ve arama bildirimlerini alacaktır",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        });
                        $('#musteri_sms_kara_listeye_ekle').attr('style','display:inline-block');
                        $('#musteri_sms_kara_listeden_cikar').attr('style','display:none');
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
            });
        }
    });
});

$('#reklam_asistan_ile_gonder').click(function(e){
    kampanyaolustur(1);
});
$('#reklam_sms_ile_gonder').click(function(e){
    kampanyaolustur(2);
});
$('#reklam_asistan_ve_sms_ile_gonder').click(function(e){
    kampanyaolustur(3);
});

$('#kampanyaTuru').change(function(e){
    e.preventDefault();
     
    if($(this).val()=='1')
    {
         $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/hizmet-secimi-2',
                    dataType: "json",
                    data : {salonId:$('input[name="sube"]').val()},
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                        $("#preloader").hide();
                        $('#hizmetUrunPaket').select2('destroy');
                        $('#hizmetUrunPaket').empty();
                        $('#hizmetUrunPaket').select2({
                            data:result,
                            allowClear: true,
                            placeholder :'Seçiniz...',
                        });
                       
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
            });
    }
    if($(this).val()=='2')
    {
         $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/urun-secimi',
                    dataType: "json",
                    data : {salonId:$('input[name="sube"]').val()},
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                        $("#preloader").hide();
                        $('#hizmetUrunPaket').select2('destroy');
                        $('#hizmetUrunPaket').empty();
                        $('#hizmetUrunPaket').select2({
                            allowClear: true,
                            placeholder :'Seçiniz...',
                            data:result,
                        });

                       
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
            });
    }
    if($(this).val()=='3')
    {
         $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/paket-secimi',
                    dataType: "json",
                    data : {salonId:$('input[name="sube"]').val()},
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                        $("#preloader").hide();
                       
                        $('#hizmetUrunPaket').select2('destroy');
                        $('#hizmetUrunPaket').empty();
                        $('#hizmetUrunPaket').select2({
                            allowClear: true,
                            placeholder :'Seçiniz...',
                            data:result,
                        });
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
            });
    }
}); 
$('#gorevTuru,#kampanyaTuru').change(function(e){
    e.preventDefault();
     $.ajax({
                    type: "GET",
                    url: '/isletmeyonetim/kampanya-sablon-filtre',
                    dataType: "json",
                    data : {salonId:$('input[name="sube"]').val(),tur:$('#gorevTuru').val(),kategori:$('#kampanyaTuru').val()},
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                        $("#preloader").hide();
                       
                        $('#hizmetUrunPaket').select2('destroy');
                        $('#hizmetUrunPaket').select2({
                            data:result.secimMenusu,
                        });
                        $('#kampanyaSablonBolumu').empty()
                        $('#kampanyaSablonBolumu').append(result.sablonlar);
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
            });
});


function kampanyaolustur(tur)
{

    var isValid = false;
    if($('#kampanyaGecerlilikTarihi').val()!=''  &&$('#kampanyaPrompt').text()!='' && $('#katilimciTuru').val()!='' && tur != '' )
        isValid = true;
    var warningtext = '';
     
    if($('#kampanyaGecerlilikTarihi').val()=='')
        warningtext += '<br>-Kampanya geçerlilik tarihini belirtiniz gerekir.';
     
    if($('#kampanyaPrompt').text()=='')
        warningtext += '<br>-Kampanya şablonunu seçmeniz gerekir.';
    if($('#katilimciTuru').val()=='')
        warningtext += '<br>-Kampanyaya katılacak müşteri/danışanları seçmeniz gerekir.';
    if($('#gorevTuru').val() == '')
        warningtext += '<br>-Kampanyaya görev türü (arama/sms) seçmeniz gerekir.';


     if(isValid) 
     {
          
            var formData = new FormData();
            formData.append("gonderim_turu",tur);
            formData.append('kampanya_sms',$('#kampanyaPrompt').text())
            var other_data = $('#kampanya_formu').serializeArray();
                    $.each(other_data,function(key,input){
                        formData .append(input.name,input.value);
            });
                
            
                $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/kampanyaekleduzenle',
                        dataType: "json",
                        data : formData,
                        processData: false,
                        contentType: false,
                        beforeSend:function(){
                            $('#preloader').show();
                        },
                         headers: {
                                'X-CSRF-TOKEN': $('input[name="_token"]').val()
                            },
                       success: function(result)  {
                            console.log(result);
                            $('#preloader').hide();
                             $('#kampanya_formu').trigger('reset');
                              $('#kampanyaPrompt').empty();
                            $('.modal_kapat').trigger('click');
                             swal(
                                    {
                                        type: "success",
                                        title: "Başarılı",
                                        timer:3000,
                                        text:  result.mesaj,
                                         showCloseButton: false,
                                        showCancelButton: false,
                                        showConfirmButton:false,
                                    }
                                );
                        $('#kampanyayonetim_tablo').DataTable().destroy();
                                $('#kampanyayonetim_tablo').DataTable({
                                        columns:[
                             { data: 'paket_isim'   },
                 
                              { data: 'seans', className:"ortaya-yasli" },
                 
                             { data: 'katilimci_sayisi'  ,   className:"ortaya-yasli" },
                 
                              { data: 'hizmet_adi'   },
                 
                              { data: 'fiyat', className:"saga-yasli"},
                 
                              { data: 'islemler', className:"saga-yasli" }, 
                           ],
                                        data: result.kampanya_yonetimi,
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
    else{
         swal(
                            {
                                type: "warning",
                                title: "Uyarı",
                                html:  'Kampanyayı oluşturmadan önce;<br><br>'+warningtext,
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:5000,
                            }
        );
    }


  
}


$(document).on('submit','#kampanya_formu',function(e){
     e.preventDefault();
    
     
  });
$('#kampanyaTuru').change(function(e){
    e.preventDefault();
    $('#hizmetUrunPaket').show();
    
     
});
$('#kampanyabeklenenleresmsgonder').click(function(e){
    e.preventDefault();

    var id = $(this).attr('data-value');
    swal({
        type: "info",
        title :"Planla",
        html: 
        "<div class='row'><div class='col-md-12'><div class='form-group'><input type='checkbox'  id='hemen_sms_gonder_bekleyen'> <label style='font-size:23px'><b>Hemen SMS Gönder</b></label></div></div>"+
        "<div class='col-sm-6'><div class='form-group'><label>SMS Gönderim Tarihi</label><input type='text' id='tekrar_sms_tarihi_bekleyen'  class='form-control date-picker'></div></div>"+
        "<div class='col-sm-6'><div class='form-group'><label>SMS Gönderim Saati</label><input type='time' id='tekrar_sms_saati_bekleyen' class='form-control'></div></div>"+        
        "</div>",
        showCancelButton: false,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Tekrar SMS Gönder',
        showCloseButton:true,
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
            $.ajax({
                type: "POST",
                url: '/isletmeyonetim/kampanyaSMSGonder',
                dataType: "json",
                data : {kampanyaid:id,_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val(),beklenen:1,hemen_gonderilecek:$('#hemen_sms_gonder_bekleyen').val(),gonderim_tarih:$('#tekrar_sms_tarihi_bekleyen').val(),gonderim_saat:$('#tekrar_sms_saati_bekleyen').val()},
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    console.log(result);
                    $('#preloader').hide();
                     swal({
                        type: "success",
                        title: "Başarılı",
                        text:  result.mesaj,
                        timer:3000,
                 });
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
            }); 
    });
    var today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD formatına çevirir
    $("#tekrar_sms_tarihi_bekleyen").val(today);
    var now = new Date();
    var hours = now.getHours().toString().padStart(2, '0'); // Saat (HH)
    var minutes = now.getMinutes().toString().padStart(2, '0'); // Dakika (MM)
    var currentTime = `${hours}:${minutes}`;

    $("#tekrar_sms_saati_bekleyen").val(currentTime);
    $('#tekrar_sms_tarihi_bekleyen').datepicker({
            minDate: new Date(),
            language: "tr",
            autoClose: true,
            dateFormat: "yyyy-mm-dd",
    });
});


$('#kampanyabeklenenleretekrarsmsgonder').click(function(e){
    e.preventDefault();

        var id = $(this).attr('data-value');
    swal({
        type: "info",
        title :"Planla",
        html: 
        "<div class='row'><div class='col-md-12'><div class='form-group'><input type='checkbox'  id='hemen_sms_gonder_katilmayan'> <label style='font-size:23px'><b>Hemen SMS Gönder</b></label></div></div>"+
        "<div class='col-sm-6'><div class='form-group'><label>SMS Gönderim Tarihi</label><input type='text' id='tekrar_sms_tarihi_katilmayan'  class='form-control date-picker'></div></div>"+
        "<div class='col-sm-6'><div class='form-group'><label>SMS Gönderim Saati</label><input type='time' id='tekrar_sms_saati_katilmayan' class='form-control'></div></div>"+        
        "</div>",
        showCancelButton: false,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Tekrar SMS Gönder',
        showCloseButton:true,
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
            $.ajax({
                type: "POST",
                url: '/isletmeyonetim/kampanyaSMSGonder',
                dataType: "json",
                data : {kampanyaid:id,_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val(),katilmayan:1,hemen_gonderilecek:$('#hemen_sms_gonder_katilmayan').val(),gonderim_tarih:$('#tekrar_sms_tarihi_katilmayan').val(),gonderim_saat:$('#tekrar_sms_saati_katilmayan').val()},
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    console.log(result);
                    $('#preloader').hide();
                     swal({
                        type: "success",
                        title: "Başarılı",
                        text:  result.mesaj,
                        timer:3000,
                 });
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
            }); 
    });
    var now = new Date();
    var hours = now.getHours().toString().padStart(2, '0'); // Saat (HH)
    var minutes = now.getMinutes().toString().padStart(2, '0'); // Dakika (MM)
    var currentTime = `${hours}:${minutes}`;

    $("#tekrar_sms_saati_katilmayan").val(currentTime);
    var today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD formatına çevirir
    $("#tekrar_sms_tarihi_katilmayan").val(today);
    $('#tekrar_sms_tarihi_katilmayan').datepicker({
            minDate: new Date(),
            language: "tr",
            autoClose: true,
            dateFormat: "yyyy-mm-dd",
    });
});

$('#kampanyabeklenenleritekrarara').click(function(e){
    e.preventDefault();
    var id = $(this).attr('data-value');
    swal({
        type: "info",
        title :"Planla",
        html: 
        "<div class='row'><div class='col-md-12'><div class='form-group'><input type='checkbox'  id='hemen_ara_katilmayan'> <label style='font-size:23px'><b>Hemen Ara</b></label></div></div>"+
        "<div class='col-sm-6'><div class='form-group'><label>Arama Tarihi</label><input type='text' id='tekrar_arama_tarihi_katilmayan'  class='form-control date-picker'></div></div>"+
        "<div class='col-sm-6'><div class='form-group'><label>Arama Saati</label><input type='time' id='tekrar_arama_saati_katilmayan' class='form-control'></div></div>"+        
        "</div>",
        showCancelButton: false,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Tekrar Ara',
        showCloseButton:true,
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
            $.ajax({
                type: "POST",
                url: '/isletmeyonetim/kampanyaAra',
                dataType: "json",
                data : {kampanyaid:id,_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val(),katilmayan:1,hemen_aranacak:$('#hemen_ara_katilmayan').val(),arama_tarih:$('#tekrar_arama_tarihi_katilmayan').val(),arama_saat:$('#tekrar_arama_saati_katilmayan').val()},
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    console.log(result);
                    $('#preloader').hide();
                     swal({
                        type: "success",
                        title: "Başarılı",
                        text:  result.mesaj,
                        timer:3000,
                 });
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
            }); 
    });
     var today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD formatına çevirir
    $("#tekrar_arama_tarihi_katilmayan").val(today);
    var now = new Date();
    var hours = now.getHours().toString().padStart(2, '0'); // Saat (HH)
    var minutes = now.getMinutes().toString().padStart(2, '0'); // Dakika (MM)
    var currentTime = `${hours}:${minutes}`;

    $("#tekrar_arama_saati_katilmayan").val(currentTime);
    $('#tekrar_arama_tarihi_katilmayan').datepicker({
            minDate: new Date(),
            language: "tr",
            autoClose: true,
            dateFormat: "yyyy-mm-dd",
    });
     
    
});

$('#kampanyabeklenenleriara').click(function(e){
    e.preventDefault();
    var id = $(this).attr('data-value');
    swal({
        type: "info",
        title :"Planla",
        html: 
        "<div class='row'><div class='col-md-12'><div class='form-group'><input type='checkbox'  id='hemen_ara_bekleyen'> <label style='font-size:23px'><b>Hemen Ara</b></label></div></div>"+
        "<div class='col-sm-6'><div class='form-group'><label>Arama Tarihi</label><input type='text' id='tekrar_arama_tarihi_bekleyen'  class='form-control date-picker'></div></div>"+
        "<div class='col-sm-6'><div class='form-group'><label>Arama Saati</label><input type='time' id='tekrar_arama_saati_bekleyen' class='form-control'></div></div>"+        
        "</div>",
        showCancelButton: false,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Tekrar Ara',
        showCloseButton:true,
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
            $.ajax({
                type: "POST",
                url: '/isletmeyonetim/kampanyaAra',
                dataType: "json",
                data : {kampanyaid:id,_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val(),beklenen:1,hemen_aranacak:$('#hemen_ara_bekleyen').val(),arama_tarih:$('#tekrar_arama_tarihi_bekleyen').val(),arama_saat:$('#tekrar_arama_saati_bekleyen').val()},
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    console.log(result);
                    $('#preloader').hide();
                     swal({
                        type: "success",
                        title: "Başarılı",
                        text:  result.mesaj,
                        timer:3000,
                 });
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
            }); 
    });
     var today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD formatına çevirir
    $("#tekrar_arama_tarihi_bekleyen").val(today);
    var now = new Date();
    var hours = now.getHours().toString().padStart(2, '0'); // Saat (HH)
    var minutes = now.getMinutes().toString().padStart(2, '0'); // Dakika (MM)
    var currentTime = `${hours}:${minutes}`;

    $("#tekrar_arama_saati_bekleyen").val(currentTime);
    $('#tekrar_arama_tarihi_bekleyen').datepicker({
            minDate: new Date(),
            language: "tr",
            autoClose: true,
            dateFormat: "yyyy-mm-dd",
        });
     
    
});

$(document).on('change','#hemen_ara_bekleyen',function(e){
    e.preventDefault();
     if(this.checked)
     {
        $('#tekrar_arama_tarihi_bekleyen').attr('disabled',true);
        $('#tekrar_arama_saati_bekleyen').attr('disabled',true);
     }
     else
     {
        $('#tekrar_arama_tarihi_bekleyen').removeAttr('disabled');
        $('#tekrar_arama_saati_bekleyen').removeAttr('disabled');
     }
});
$(document).on('change','#hemen_sms_gonder_bekleyen',function(e){
    e.preventDefault();
     if(this.checked)
     {
        $('#tekrar_sms_tarihi_bekleyen').attr('disabled',true);
        $('#tekrar_sms_saati_bekleyen').attr('disabled',true);
     }
     else
     {
        $('#tekrar_sms_tarihi_bekleyen').removeAttr('disabled');
        $('#tekrar_sms_saati_bekleyen').removeAttr('disabled');
     }
});


$(document).on('change','#hemen_ara_katilmayan',function(e){
     e.preventDefault();
     if(this.checked)
     {
        $('#tekrar_arama_tarihi_katilmayan').attr('disabled',true);
        $('#tekrar_arama_saati_katilmayan').attr('disabled',true);
     }
     else
     {
        $('#tekrar_arama_tarihi_katilmayan').removeAttr('disabled');
        $('#tekrar_arama_saati_katilmayan').removeAttr('disabled');
     }
});
$(document).on('change','#hemen_sms_gonder_katilmayan',function(e){
     e.preventDefault();
     if(this.checked)
     {
        $('#tekrar_sms_tarihi_katilmayan').attr('disabled',true);
        $('#tekrar_sms_saati_katilmayan').attr('disabled',true);
     }
     else
     {
        $('#tekrar_arama_tarihi_katilmayan').removeAttr('disabled');
        $('#tekrar_arama_saati_katilmayan').removeAttr('disabled');
     }
});

  $('#kampanyayonetim_tablo').on('click','a[name="kampanya_sil"]',function(e){
    e.preventDefault();
    var kampanyaid = $(this).attr('data-value');
    swal({
        title: "Emin misiniz?",
        text: "Kaldırma işlemi geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Kampanyayı kaldır',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
                $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/kampanyasil',
                    dataType: "json",
                    data : {kampanya_id:kampanyaid,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        $('#modal_kapat').trigger('click');
                        swal(
                            {
                                type: 'success',
                                title: 'Başarılı',
                                text: 'Kampanya başarıyla kaldırıldı',
                                timer:3000,
                                 showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                            }
                        );
                        $('#kampanyayonetim_tablo').DataTable().destroy();
                        $('#kampanyayonetim_tablo').DataTable({
                                columns:[
                    { data: 'paket_isim'   },
         
                      { data: 'seans', className:"ortaya-yasli" },
         
                     { data: 'katilimci_sayisi'  ,   className:"ortaya-yasli" },
         
                      { data: 'hizmet_adi'   },
         
                      { data: 'fiyat', className:"saga-yasli"},
         
                      { data: 'islemler', className:"saga-yasli" }, 
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
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
                });
        }
    });
});
/*$('#personel_tablo').on('click','#personel_sifre_degistir_gonder',function(e){
     $.ajax({
        type: "POST",
        url: '/isletmeyonetim/personelsifredegistir',
        dataType: "text",
        data: { yetkili_id:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result){
            $("#preloader").hide();
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});*/
$('#kampanya_sablon_sec').change(function(){
    $('#kampanya_sms').val($(this).val());
});
$("#hepsinisec1").change(function(){
     if(this.checked){
         $('input:checkbox').not(this).prop('checked', this.checked);
     }
     else{
         $('input:checkbox').not(this).prop('checked', false);
     }
});
$("#hepsinisec2").change(function(){
     if(this.checked){
         $('input:checkbox').not(this).prop('checked', this.checked);
     }
     else{
         $('input:checkbox').not(this).prop('checked', false);
     }
});
$("#hepsinisec3").change(function(){
     if(this.checked){
         $('input:checkbox').not(this).prop('checked', this.checked);
     }
     else{
         $('input:checkbox').not(this).prop('checked', false);
     }
});
$("#hepsinisec4").change(function(){
     if(this.checked){
         $('input:checkbox').not(this).prop('checked', this.checked);
     }
     else{
         $('input:checkbox').not(this).prop('checked', false);
     }
});
$(document).on('submit','#etkinlik_formu',function(e){
    e.preventDefault();
    var katilimcilarsecildi = true;
    var secilensayi = 0;
    $('input[name="etkinlik_katilimci_musteriler[]"]').each(function() {
      if ($(this).is(":checked")) {
        secilensayi = secilensayi + 1
      }
    });
    $('input[name="etkinlik_grup_katilimci_musteriler[]"]').each(function() {
      if ($(this).is(":checked")) {
        secilensayi = secilensayi + 1
      }
    });
    if(secilensayi == 0)
    {
         swal(
                            {
                                type: "warning",
                                title: "Uyarı",
                                html:  'Devam etmek için lütfen etkinlik katılımcılarını seçiniz.',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
        );
    }
    else{
        $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/etkinlikekleduzenle',
                    dataType: "json",
                    data:$('#etkinlik_formu').serialize(),
                    beforeSend:function(){
                        $('#preloader').show();
                    },
                   headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').val()
        },
                    success: function(result)  {
                         $('#preloader').hide();
             $('#etkinlik_formu').trigger('reset');
            $('#yeni_etkinlik_modal').modal('hide');
                        $('#etkinlik_sms').val('');
                        swal(
                            {
                                type: "success",
                                title: "Başarılı",
                                text:  result.mesaj,
                                 showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
                        );
                         $('#etkinlik_tablo').DataTable().destroy();
                        $('#etkinlik_tablo').DataTable({
                                columns:[
                      { data: 'tarih'   },
                      { data: 'etkinlik_adi' },
                      { data: 'katilimci_sayisi'   },
                      { data: 'fiyat' },
                      { data: 'islemler' },
                   ],
                                data: result.katilimci,
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
});
$('#etkinlik_sablon_sec').change(function(){
    $('#etkinlik_sms').val($(this).val());
});
$("#hepsinisec4").change(function(){
     if(this.checked){
         $('input:checkbox').not(this).prop('checked', this.checked);
     }
     else{
         $('input:checkbox').not(this).prop('checked', false);
     }
});
$("#hepsinisec5").change(function(){
     if(this.checked){
         $('input:checkbox').not(this).prop('checked', this.checked);
     }
     else{
         $('input:checkbox').not(this).prop('checked', false);
     }
});
$('#etkinlikbeklenenleresmsgonder').click(function(e){
        e.preventDefault();
            $.ajax({
                type: "POST",
                url: '/isletmeyonetim/etkinlikbeklenensms',
                dataType: "json",
                data : {etkinlikid:$(this).attr('data-value'),_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val()},
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    console.log(result);
                    $('#preloader').hide();
                     swal({
                        type: "success",
                        title: "Başarılı",
                        text:  result.mesaj,
                        timer:3000,
                 });
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
            });
  });
 $('#etkinlik_tablo').on('click','a[name="etkinlik_sil"]',function(e){
    e.preventDefault();
    var etkinlikid = $(this).attr('data-value');
    swal({
        title: "Emin misiniz?",
        text: "Kaldırma işlemi geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Etkinliği kaldır',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
                $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/etkinliksil',
                    dataType: "json",
                    data : {etkinlik_id:etkinlikid,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
                        $('#modal_kapat').trigger('click');
                        swal(
                            {
                                type: 'success',
                                title: 'Başarılı',
                                text: 'Etkinlik başarıyla kaldırıldı',
                            }
                        );
                        $('#etkinlik_tablo').DataTable().destroy();
                        $('#etkinlik_tablo').DataTable({
                                columns:[
                         { data: 'tarih'   },
                      { data: 'etkinlik_adi' },
                      { data: 'katilimci_sayisi'   },
                      { data: 'fiyat' },
                      { data: 'islemler' },
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
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
                });
        }
    });
});
 $('#sifremiunuttum_isletme').on('submit',function (e) {
    e.preventDefault();
    $.ajax({
                type: "POST",
                url: '/isletmeyonetim/sifregonder',
                dataType: "json",
                data : $('#sifremiunuttum_isletme').serialize(),
                beforeSend:function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                     $('#preloader').hide();
                    if(result.status==true)
                    {
                       yenisifrebelirle_isletme('');
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
function yenisifrebelirle_isletme(uyaritext)
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
                                yenisifrebelirle_isletme('Girdiğiniz şifreler uyuşmamaktadır!');
                            }
                            else if($('#sifre_yenileme_dogrulama_kodu').val()!='' &&$('#sifre_yenileme').val()!= '' && $('#sifre_yenileme_tekrar').val()!= '')
                            {
                                if(res.value)
                                {
                                    $.ajax({
                                    type: "POST",
                                    url: '/isletmeyonetim/sifredegistir',
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
                                                timer:5000,
                                            });
                                            setTimeout(function(){
                                                window.location.href = '/isletmeyonetim/girisyap';
                                            }, 5000);
                                            //window.location.href= '/isletmeyonetim/girisyap';
                                        }
                                        else
                                        {
                                            yenisifrebelirle_isletme('Hatalı doğrulama kodu girdiniz. Lütfen yeniden deneyiniz!');
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
                                yenisifrebelirle_isletme('Lütfen tüm alanları eksiksiz doldurunuz!');
                            }
    });
}
$('#personel_tablo').on('click','a[name="personel_sifre_degistir_gonder"]',function () {
    $.ajax({
                type: "POST",
                url: '/isletmeyonetim/personelsifregonder',
                dataType: "text",
                data : {personelid:$(this).attr('data-value'),_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val()},
                beforeSend:function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                     $('#preloader').hide();
                         swal({
                            type: "success",
                            title: "Başarılı",
                            text:  result,
                            timer:3000,
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                        });
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
    });
});
$('#personel_tablo').on('click','a[name="personel_pasif_aktif_yap"]',function () {
    $.ajax({
                type: "POST",
                url: '/isletmeyonetim/personelaktifpasifyap',
                dataType: "json",
                data : {personelid:$(this).attr('data-value'),_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val(),aktif:$(this).attr('data-index-number')},
                beforeSend:function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                    $('#preloader').hide();
                    swal({
                            type: "success",
                            title: "Başarılı",
                            text:  result.mesaj,
                            timer:3000,
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                    });
                    $('#personel_tablo').DataTable().destroy();
                    $('#personel_tablo').DataTable({
                        ordering: false,
                        paging: false,
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
                            {data : 'siralama', className: "text-center"},
                            {data:'ad_soyad'},
                            { data: 'hesap_turu'   },
                            { data: 'telefon' },
                            { data: 'durum'},
                            { data: 'islemler' },
                          ],
                          data: result.personeller,
                    });
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
    });
});
$('#karaliste_sms_tablo').on('click','button[name="numara_karalisteden_kaldir"]', function(e){
    e.preventDefault();
    var musteriid=$(this).attr('data-value');
    swal({
         title: "Emin misiniz?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Kara listeden çıkar',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result){
        if(result.value){
            $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/musterikaralisteayari',
                    dataType: "json",
                    data : {user_id:musteriid,_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val(),karaliste:0},
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                        $("#preloader").hide();
                        swal({
                        type: "success",
                        title: "Başarılı",
                        text:  "Müşteri SMS karalistesinden başarıyla çıkarılmış olup artık SMS bildirimlerini alacaktır",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        });
                        $('#karaliste_sms_tablo').DataTable().destroy();
                        $('#karaliste_sms_tablo').DataTable({
                        autoWidth: false,
                        responsive: true,
                        columns:[
                              { data: 'ad_soyad', className: "text-center",   },
                              { data: 'telefon',className: "text-center", },
                               { data: 'eklenme_tarihi',className: "text-center", },
                              { data: 'islemler',className: "text-right"  },
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
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
            });
        }
    });
});
$(document).on('submit','#karaliste_sms_formu',function(e){
    e.preventDefault();
    var musteriid=$(this).attr('data-value');
    swal({
         title: "Emin misiniz?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Kara listeye ekle',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result){
        if(result.value){
            $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/musterikaralisteayari',
                    dataType: "json",
                    data : $('#karaliste_sms_formu').serialize(),
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                        $("#preloader").hide();
                        swal({
                        type: "success",
                        title: "Başarılı",
                        text:  "Müşteriler SMS karalistesine başarıyla eklenmiş olup artık SMS bildirimlerini almayacaktır",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        });
                        $('#karaliste_sms_tablo').DataTable().destroy();
                        $('#karaliste_sms_tablo').DataTable({
                        autoWidth: false,
                        responsive: true,
                        columns:[
                              { data: 'ad_soyad', className: "text-center",   },
                              { data: 'telefon',className: "text-center", },
                               { data: 'eklenme_tarihi',className: "text-center", },
                              { data: 'islemler',className: "text-right"  },
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
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
            });
        }
    });
});
$('#urun_liste').on('click','a[name="urun_duzenle"]',function(e){
    var tds = $(this).closest('tr').children('td');
    //alert(tds[0].innerHTML)
    $('#urun_ad').val(tds[1].innerHTML);
    $('#stok_aded').val( tds[2].innerHTML);
    $('#fiyat_duzenle').val(tds[3].innerHTML);
    $('#barkod_duzenle').val(tds[4].innerHTML);
    $('#dusuk_stok_siniri_duzenle').val(tds[5].innerHTML);
    $('#urun_id_duzenle').val($(this).attr('data-value'));
});
$('#dusuk_stok_siniri_duzenle').on('keyup paste',function () {
    const dusukStokSiniri = parseFloat($('#dusuk_stok_siniri_duzenle').val());
    const stokAdedi = parseFloat($('#stok_aded').val());
    if (!isNaN(dusukStokSiniri) && !isNaN(stokAdedi))
    {
        if (dusukStokSiniri >= stokAdedi) {
            swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text:  'Düşük stok sınırı stok adedinden az olmalıdır!',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
            );
            $('#dusuk_stok_siniri_duzenle').val('');
        }
    }
})
$('#dusuk_stok_siniri').on('keyup paste',function () {
    const dusukStokSiniri = parseFloat($('#dusuk_stok_siniri').val());
    const stokAdedi = parseFloat($('#stok_adedi').val());
    if (!isNaN(dusukStokSiniri) && !isNaN(stokAdedi))
    {
        if (dusukStokSiniri >= stokAdedi) {
            swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text:  'Düşük stok sınırı stok adedinden az olmalıdır!',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
            );
            $('#dusuk_stok_siniri').val('');
        }
    }
});
$(document).on('submit','#urun_formu_duzenle',function(e){
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/urunguncelle',
        dataType: "json",
        data : $('#urun_formu_duzenle').serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            $("#preloader").show();
        },
       success: function(result)  {
            $("#preloader").hide();
              $('#urun_formu_duzenle').trigger('reset');
            $('#urun-modal-duzenle').modal('hide');
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
             $('button[data-dismiss="modal"]').trigger('click');
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('.hizmetler_bolumu_adisyon_2').on('change','select[name="adisyon_hizmet_durum"]',function(){
            var hizmetid = $(this).attr('data-value');
            var geldimidurum = $(this).val();
            if($('#dogrulama_kodu_ayari').val()==1 && geldimidurum==1)
            {
                $.ajax({
                                type: "POST",
                                url: '/isletmeyonetim/hizmetdogrulamakodugonder',
                                dataType: "text",
                                data : {hizmet_id:hizmetid,_token: $('input[name="_token"]').val(),sube:$('input[name="sube"]').val()},
                                headers: {
                                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                                },
                                beforeSend: function() {
                                    $("#preloader").show();
                                },
                                success: function(result)  {
                                    $("#preloader").hide();
                                     hizmet_dogrulama_islemleri(hizmetid,geldimidurum);
                                },
                                error: function (request, status, error) {
                                    $("#preloader").hide();
                                    document.getElementById('hata').innerHTML = request.responseText;
                                }
                });
            }
            else
            {
                $.ajax({
                                    type: "POST",
                                    url: '/isletmeyonetim/adisyonhizmetguncelle',
                                    dataType: "text",
                                    data : {hizmet_id:hizmetid,geldimi:geldimidurum,dogrulama:false,sube:$('input[name="sube"]').val()},
                                    headers: {
                                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                                    },
                                    beforeSend: function() {
                                        $("#preloader").show();
                                    },
                                   success: function(result)  {
                                        $("#preloader").hide();
                                        $('.hizmetler_bolumu_adisyon_2').empty();
                                        $('.hizmetler_bolumu_adisyon_2').append(result);
                                    },
                                    error: function (request, status, error) {
                                        $("#preloader").hide();
                                        document.getElementById('hata').innerHTML = request.responseText;
                                    }
                });
            }
});
$('.hizmetler_bolumu_adisyon_2').on('change','select[name="islem_personelleri"]',function(){
    var hizmetid = $(this).attr('data-value');
    var personelid = $(this).val();
    $.ajax({
                                    type: "POST",
                                    url: '/isletmeyonetim/adisyonhizmetpersonelguncelle',
                                    dataType: "text",
                                    data : {hizmet_id:hizmetid,personel_id:personelid,sube:$('input[name="sube"]').val()},
                                    headers: {
                                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                                    },
                                   success: function(result)  {
                                        $("#preloader").hide();
                                        $('.hizmetler_bolumu_adisyon_2').empty();
                                        $('.hizmetler_bolumu_adisyon_2').append(result);
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
    $.ajax({
                                    type: "POST",
                                    url: '/isletmeyonetim/adisyonhizmethizmetguncelle',
                                    dataType: "text",
                                    data : {hizmet_id:hizmetid,adisyon_hizmet_id:adiysonhizmetid,sube:$('input[name="sube"]').val()},
                                    headers: {
                                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                                    },
                                   success: function(result)  {
                                        $("#preloader").hide();
                                        $('.hizmetler_bolumu_adisyon_2').empty();
                                        $('.hizmetler_bolumu_adisyon_2').append(result);
                                    },
                                    error: function (request, status, error) {
                                        $("#preloader").hide();
                                        document.getElementById('hata').innerHTML = request.responseText;
                                    }
    });
});
function hizmet_dogrulama_islemleri(hizmetid,geldidurum)
{
        swal({
                            title: 'Lütfen müşterinin cep telefonuna gönderilen doğrulama kodunu giriniz',
                            input: 'text',
                            showCancelButton: true,
                            confirmButtonText: 'Gönder',
                            cancelButtonText: 'Vazgeç',
                            showLoaderOnConfirm: true,
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonClass: 'btn btn-danger',
                        }).then(function (result) {
                            if(result.value){
                                $.ajax({
                                    type: "POST",
                                    url: '/isletmeyonetim/adisyonhizmetguncelle',
                                    dataType: "text",
                                    data : {hizmet_id:hizmetid,geldimi:geldidurum,dogrulama:true,dogrulama_kodu:result.value,sube:$('input[name="sube"]').val()},
                                    headers: {
                                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                                    },
                                    beforeSend: function() {
                                        $("#preloader").show();
                                    },
                                   success: function(result)  {
                                        $("#preloader").hide();
                                        if(result=='Doğrulama kodu hatalı, lütfen yeniden deneyiniz.')
                                        {
                                            hizmet_dogrulama_islemleri(hizmetid,geldidurum);
                                        }
                                        else
                                        {
                                            $('.hizmetler_bolumu_adisyon_2').empty();
                                            $('.hizmetler_bolumu_adisyon_2').append(result);
                                        }
                                    },
                                    error: function (request, status, error) {
                                        $("#preloader").hide();
                                        document.getElementById('hata').innerHTML = request.responseText;
                                    }
                                });
                            }
    })
}
$('#tahsilat_tutari').on('keyup paste',function(){
    if(parseFloat($('#tahsilat_tutari').val())>parseFloat($('#tahsilat_tutari_numeric').val()))
    {
        swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text:  'Tahsilat tutarı adisyon tutarından büyük olamaz!',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
        );
           $('#tahsilat_tutari').val($('#tahsilat_tutari_numeric').val());
    }
});
$('#tumunu_tahsil_et').click(function(e){
    var total = 0;
    var indirim = parseFloat($('#indirim_tutari').val().replace(".", ""));
    $('input[name="adisyon_odeme_hizmet[]"]').each(function(i){
        $(this).prop('checked','checked');
        total += parseFloat($(this).val());
    });
    $('input[name="adisyon_odeme_urun[]"]').each(function(i){
        $(this).prop('checked','checked');
        total += parseFloat($(this).val());
    });
    $('input[name="adisyon_odeme_paket[]"]').each(function(i){
        $(this).prop('checked','checked');
        total += parseFloat($(this).val());
    });
    $('#tahsilat_tutari').val(total);
    $('#tahsilat_tutari_numeric').val(total);
    $('#indirimli_toplam_tahsilat_tutari').val(accounting.formatMoney(total-indirim, "", 2, ".", ","));
    $('#odenecek_tutar').val(accounting.formatMoney(0, "", 2, ".", ","));
});
$('#indirim_tutari').on('keyup paste', function(){
    adisyonyenidenhesapla();
});
$('#harici_indirim_tutari').on('keyup paste', function(){
    tahsilatyenidenhesapla();
});
function adisyonyenidenhesapla()
{
    var total  = 0;
    var indirim = parseFloat($('#indirim_tutari').val().replace(".", ""));
    var indirim_carpan = 0;
    if(!$('#indirim_tutari').prop('disabled')){
        indirim_carpan = parseFloat($('#indirim_tutari').val().replace(".", ""));
    }
    var kalan = 0;
    var indirimli_tutar = 0;
    $('.adisyon_kalemler').each(function(){
        if($(this).attr('name')=='adisyon_odeme_hizmet[]'){
            var tutar = parseFloat($('input[name="adisyon_hizmet_tahsilat_tutari_girilen[]"][data-value="'+$(this).attr('data-value')+'"]').val());
            var eksilecek = (tutar/parseFloat($('#birim_tutar').val().replace(".", "")))*indirim;
            var yenitutar = tutar - eksilecek;
            console.log(tutar+' - '+eksilecek +' = '+yenitutar);
            var moneytutar = accounting.formatMoney(tutar,'',2,'.',',');
            var moneyyenitutar = accounting.formatMoney(yenitutar, "", 2, ".", ",");
            $('span[name="adisyon_hizmet_tahsilat_tutari"][data-value="'+$(this).attr('data-value')+'"]').empty();
            $('span[name="adisyon_hizmet_tahsilat_tutari"][data-value="'+$(this).attr('data-value')+'"]').append(moneyyenitutar);
            $('input[name="adisyon_hizmet_tahsilat_tutari[]"][data-value="'+$(this).attr('data-value')+'"]').val(moneyyenitutar);
            indirimli_tutar += yenitutar;
            total += tutar;
        }
        if($(this).attr('name')=='adisyon_odeme_urun[]'){
            var tutar = parseFloat($('input[name="adisyon_urun_tahsilat_tutari_girilen[]"][data-value="'+$(this).attr('data-value')+'"]').val());
            var eksilecek = (tutar/parseFloat($('#birim_tutar').val().replace(".", "")))*indirim;
            var yenitutar = tutar - eksilecek;
             console.log('Birim tutar : ' + parseFloat($('#birim_tutar').val().replace(".", "")));
            console.log(tutar+' - '+eksilecek +' = '+yenitutar);
            var moneytutar = accounting.formatMoney(tutar,'',2,'.',',');
            var moneyyenitutar = accounting.formatMoney(yenitutar, "", 2, ".", ",");
            $('span[name="adisyon_urun_tahsilat_tutari"][data-value="'+$(this).attr('data-value')+'"]').empty();
            $('span[name="adisyon_urun_tahsilat_tutari"][data-value="'+$(this).attr('data-value')+'"]').append(moneyyenitutar);
            $('input[name="adisyon_urun_tahsilat_tutari[]"][data-value="'+$(this).attr('data-value')+'"]').val(moneyyenitutar);
            indirimli_tutar += yenitutar;
            total += tutar;
        }
        if($(this).attr('name')=='adisyon_odeme_paket[]'){
            var tutar = parseFloat($('input[name="adisyon_paket_tahsilat_tutari_girilen[]"][data-value="'+$(this).attr('data-value')+'"]').val());
            var moneytutar = accounting.formatMoney(tutar,'',2,'.',',');
            var eksilecek = (tutar/parseFloat($('#birim_tutar').val().replace(".", "")))*indirim;
            var yenitutar = tutar - eksilecek;
            console.log('Birim tutar : ' + parseFloat($('#birim_tutar').val().replace(".", "")));
            console.log(tutar+' - '+eksilecek +' = '+yenitutar);
            var moneyyenitutar = accounting.formatMoney(yenitutar, "", 2, ".", ",")
            $('span[name="adisyon_paket_tahsilat_tutari"][data-value="'+$(this).attr('data-value')+'"]').empty();
            $('span[name="adisyon_paket_tahsilat_tutari"][data-value="'+$(this).attr('data-value')+'"]').append(moneyyenitutar);
            $('input[name="adisyon_paket_tahsilat_tutari[]"][data-value="'+$(this).attr('data-value')+'"]').val(moneyyenitutar);
            indirimli_tutar += yenitutar;
            total += tutar;
        }
    });
    $('#odenecek_tutar').attr('style','background-color:#fff;border-color:#d4d4d4;font-size:20px;');
    $('#birim_tutar').val(accounting.formatMoney(total+kalan,'',2,'.',','));
    $('#planlanan_alacak_tarihi').attr('style','background-color:#fff;border-color:#d4d4d4;');
    $('#planlanan_alacak_tarihi').val('');
    $('#odenecek_tutar,#taksit_tutar').val(accounting.formatMoney(kalan,'',2,'.',','));
    $('#tahsilat_tutari').val(accounting.formatMoney(total-indirim, "", 2, ".", ","));
    $('#senet_tutar').val(accounting.formatMoney(total-indirim, "", 2, ".", ","));
    $('#taksit_tutar').val(accounting.formatMoney(total-indirim-parseFloat($('#senetli_toplam_tutar').val().replace(".", "")), "", 2, ".", ","));

    if($('#taksit_tutar').val()=="0,00" || $('#taksit_tutar').val()=="0"){
        $('#taksit_tutar').val(accounting.formatMoney(total-indirim, "", 2, ".", ",")).change();
        console.log("ön ödeme girilmedi");
    }
    $('#odenecek_tutar').val("0,00");
    
    $('#indirimli_toplam_tahsilat_tutari').val(accounting.formatMoney(total-indirim, "", 2, ".", ",")).change();
}
/*function senetli_taksitli_toplam_tutar(){
    var senetli_taksitli_birim_tutar = 0;
    $('.tahsilat_kalemleri').each(function() {
        var kalemtutar =$(this).val().replace(".", "");
        kalemtutar = kalemtutar.replace(',','.');
        kalemtutar = parseFloat(kalemtutar);
        if($(this).attr('type') == 'hidden')
            senetli_taksitli_birim_tutar += kalemtutar;
    });
    return senetli_taksitli_birim_tutar;
}*/
$('#indirimli_toplam_tahsilat_tutari').on('keyup paste change',function () {
    var total = parseFloat($('#tahsil_edilecek_kalan_tutar').text().replace(".", ""));
    //var indirim = parseFloat($('#indirim_tutari').val().replace(",", ""));
    var alacak = total /* - senetli_taksitli_toplam_tutar()*/;
    if(parseFloat($('#indirimli_toplam_tahsilat_tutari').val().replace(".", ""))>alacak)
    {
        swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text:  'Tahsilat tutarı adisyon tutarından büyük olamaz!',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
        );
        $('#indirimli_toplam_tahsilat_tutari').val(accounting.formatMoney(alacak, "", 2, ".", ","));
    }
    else if(parseFloat($('#indirimli_toplam_tahsilat_tutari').val().replace(".", ""))<alacak)
    {
        $('#planlanan_alacak_tarihi').prop('required','required');
        $('#planlanan_alacak_tarihi').prop('placeholder','Alacak için planlanan ödeme tarihi giriniz.');
        $('#odenecek_tutar,#taksit_tutar').val(accounting.formatMoney(alacak-parseFloat($('#indirimli_toplam_tahsilat_tutari').val().replace(".", "")), "", 2, ".", ","));
        $('#odenecek_tutar').attr('style','background-color:#f8d7da;border-color:#f5c6cb;font-size:20px;');
        $('#planlanan_alacak_tarihi').attr('style','background-color:#f8d7da;border-color:#f5c6cb;padding-left:40px');
    }
    else
    {
        $('#planlanan_alacak_tarihi').removeAttr('required');
        $('#planlanan_alacak_tarihi').removeAttr('placeholder');
        $('#odenecek_tutar,#taksit_tutar').val(accounting.formatMoney(alacak-parseFloat($('#indirimli_toplam_tahsilat_tutari').val().replace(".", "")), "", 2, ".", ","));
        $('#odenecek_tutar').attr('style','background-color:#fff;border-color:#d4d4d4;font-size:20px;');
        $('#planlanan_alacak_tarihi').attr('style','background-color:#fff;border-color:#d4d4d4;padding-left:40px');
        $('#planlanan_alacak_tarihi').val('');
    }
});
$('#tum_gun').change(function(){
    if(this.checked){
        $('#kapama_saat_baslangic,#kapama_saat_bitis').removeAttr('required');
       $('#kapama_saat_baslangic,#kapama_saat_bitis').prop('disabled',true);
    }
    else{
        $('#kapama_saat_baslangic,#kapama_saat_bitis').prop('required',true);
         $('#kapama_saat_baslangic,#kapama_saat_bitis').removeAttr('disabled');
    }
});
function randevusaatlerinigetir(secilentarih,salonid,saat)
{
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/saatlerigetir',
        dataType: "text",
        data : {tarih:secilentarih,sube:salonid,sube:$('input[name="sube"]').val()},
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            $('#randevu_saat,#randevuduzenle_saat,#ongorusme_saati').empty();
            $('#randevu_saat,#randevuduzenle_saat,#ongorusme_saati').append(result);
            $('#randevu_saat').val(saat);
            $('#randevuduzenle_saat').val(saat);
            $('#ongorusme_saati').val(saat);
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
}
$('#musteri_tablo,#musteri_tablo_sadik,#musteri_tablo_aktif,#musteri_tablo_pasif').on('click', 'a[name="musteri_sil"]', function(e) {
    e.preventDefault();
    var portfoyid = $(this).attr('data-value');
    var row = $(this).closest('tr'); // Silinecek satırı seç
    
    swal({
        title: "Emin misiniz?",
        text: "Kaydı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#ff0000',
        confirmButtonText: 'Sil',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function(result) {
        if(result.value) {
            $("#preloader").show();
            
            $.ajax({
                type: "POST",
                url: '/isletmeyonetim/musterisil',
                data: { 
                    portfoy_id: portfoyid,
                    sube: $('input[name="sube"]').val(),
                    _token: $('input[name="_token"]').val()
                },
                dataType: "json",
                success: function(response) {
                    $("#preloader").hide();
                    
                    if(response.status === 'success') {
                        // Select listelerden müşteriyi kaldır
                        $("#musteri_arama option[value='/isletmeyonetim/musteridetay/"+response.musteri_id+"']").remove();
                        $('select[name="musteri"], select[name="ad_soyad"], select[name="adsoyad"]')
                            .find('option[value="'+response.musteri_id+'"]').remove();
                        
                        // Satırı tablodan kaldır
                        row.remove();
                        
                        swal({
                            type: 'success',
                            title: response.title,
                            text: response.mesaj,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Tabloları yenilemek yerine sadece ilgili satırı kaldır
                        // Veya sadece ilgili tabloyu yenileyin
                        var table = row.closest('table').DataTable();
                        table.draw(false); // Sayfalamayı koruyarak yenile
                        
                    } else {
                        swal({
                            type: 'error',
                            title: response.title,
                            text: response.mesaj,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    $("#preloader").hide();
                    swal({
                        type: 'error',
                        title: 'Hata',
                        text: 'Bir hata oluştu: ' + xhr.responseText,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
    });
});
$('#on_gorusme_liste').on('click','a[name="satisyapilmamasebep"]',function(e){
    e.preventDefault();
     swal({
        html: "<p style='padding-top:-50px;' >Satış yapılmama sebebi</p>"+
                "<div class='row'><div class='col-md-12'><label style='font-size:18px; padding-top:10px; font-weight:bold'>"+$('input[name="satisyapilmamanotu"][data-value="'+$(this).attr('data-value')+'"]').val()+"</label></div></div>",
        type: "info",
         showCloseButton: true,
         showCancelButton: false,
         showConfirmButton:false,
     });
});
//Datatable içeren tablar tıklandığında çalışacak olan kod
$('.tab-pane,.tab-content, button[data-toggle="tab"],a[data-toggle="tab"]').on('shown.bs.tab',function(event){
    var url = '';
    var musteriTablo = false;
    var satisTablo = false;
    var aramaTablo = false;
    var seansTablo = false;
    var satisTur = '';
    console.log("href : "+$(this).attr('href'));
    if($(this).attr('href')=="#tum-musteriler"){
        url = '/isletmeyonetim/musterilistegetir/3';
        musteriTablo = true;

    }
    if($(this).attr('href')=="#sadik-musteriler"){
        url = '/isletmeyonetim/musterilistegetir/2';
        musteriTablo = true;
    }
    if($(this).attr('href')=="#pasif-musteriler"){
        url = '/isletmeyonetim/musterilistegetir/0';
        musteriTablo = true;
    }

    if($(this).attr('href')=="#aktif-musteriler"){
        url = '/isletmeyonetim/musterilistegetir/1';
        musteriTablo = true;
    }
     if($(this).attr('href')=="#tum_adisyonlar"){
        url = '/isletmeyonetim/adisyon-filtreli-getir';
        satisTablo = true;

    }
    if($(this).attr('href')=="#hizmet_adisyonlar"){
        url = '/isletmeyonetim/adisyon-filtreli-getir';
        satisTablo = true;
        satisTur = 1;
        console.log("hizmetler tabı");
    }
    if($(this).attr('href')=="#ürün_adisyonlar"){
        url = '/isletmeyonetim/adisyon-filtreli-getir';
        satisTablo = true;
        satisTur = 3;
        console.log("ürünler tabı");
    }
    if($(this).attr('href')=="#paket_adisyonlar"){
        url = '/isletmeyonetim/adisyon-filtreli-getir';
        satisTablo = true;
        satisTur = 2;
        console.log("paketler tabı");
    }
    if($(this).attr('href')=='#arama_listesi_tablosu')
    {
        url = '/isletmeyonetim/arama_listesi_getir';
        aramaTablo = true;
    }
     if($(this).attr('href')=='#formlar')
    {
        url = '/isletmeyonetim/';
        seansTablo = true;
    }
      if($(this).attr('href')=='#tahsilatEkrani'){
        $('#tahsilat_musteri_id').trigger('change');
    }
    if(url != '' && aramaTablo){
        aramaListesiniGetir(url);
    }
    else if(url != '' && seansTablo)
    {
         seanslariGetir(null);
    }
    else if(url != '' && musteriTablo)
    {
         $('.data-table').each(function(){
            $(this).DataTable().destroy();
            $(this).DataTable({
                 autoWidth: false,
                       responsive: true,
                       "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": url,
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
            }).columns.adjust().responsive.recalc();
        });
          if($('#hizmet_rapor_tablo').length){
                $('#hizmet_rapor_tablo').DataTable().destroy()
                var himzetRaporTablo = $('#hizmet_rapor_tablo').DataTable({
                      ordering: false,
                      autoWidth: false,
                      pageLength: 100, 
                      
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                       }
                    }
                });
                himzetRaporTablo.columns.adjust().responsive.recalc();
            }
         if($('#urun_rapor_tablo').length){
$('#urun_rapor_tablo').DataTable().destroy()
                var urunRaporTablo =  $('#urun_rapor_tablo').DataTable({
                      ordering: false,
                    autoWidth: false,
                      pageLength: 100, 
                      
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                       }
                    }
                 });
                urunRaporTablo.columns.adjust().responsive.recalc();
             }
             if($('#paket_rapor_tablo').length){
$('#paket_rapor_tablo').DataTable().destroy()
                 var paketRaporTablo = $('#paket_rapor_tablo').DataTable({
                      ordering: false,
                    autoWidth: false,
                      pageLength: 100, 
                      
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                       }
                    }
                 });
                 paketRaporTablo.columns.adjust().responsive.recalc();
             }
             if($('#personel_rapor_tablo').length){
$('#personel_rapor_tablo').DataTable().destroy()
                var personelRaporTablo = $('#personel_rapor_tablo').DataTable({
                      ordering: false,
                    autoWidth: false,
                      pageLength: 100, 
                      
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                       }
                    }
                 });
                personelRaporTablo.columns.adjust().responsive.recalc();
             }
    }
    else if(url != '' && satisTablo)
    {
      
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
                          { data: 'durum'},
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
                          { data: 'durum'},
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
                          { data: 'durum'},
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
                          { data: 'durum'},
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

    else{
          $('.data-table').each(function(){
            $(this).DataTable().destroy();
            $(this).DataTable({
                responsive: true,
                autoWidth:false,
                   "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'
                           }
                        },
        }).columns.adjust().responsive.recalc();
        });
    }
});
 $('#adisyon_liste,#adisyon_liste_paket,#adisyon_liste_hizmet,#adisyon_liste_urun,#adisyon_liste_musteri').on('click','button[name="adisyon_sil"]',function(){
    var adisyonid = $(this).attr('data-value');
     swal({
                        title: "Emin misiniz?",
                        text: "Adisyon kaydını silmek istediğinize emin misiniz? Adisyona ait tüm içerik, tahsilat, ve alacak kayıtları silinecek olup bu işlem geri alınamayacaktır.",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#00bc8c',
                        confirmButtonText: 'Adisyonu Sil',
                        cancelButtonText: "Vazgeç",
                        confirmButtonClass: 'btn btn-success',
                        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
                        if(result.value){
                            $.ajax({
                                    type: "POST",
                                    url: '/isletmeyonetim/adisyon-sil',
                                    dataType: "json",
                                    data : {sube:$('input[name="sube"]').val(),_token:$('input[name="_token"]').val(),adisyon_id:adisyonid,musteriid:$('#adisyon_musteriye_gore_filtrele').val(),tariharaligi:$('#adisyon_tarihe_gore_filtre').val()},
                                    beforeSend:function(){
                                        $('#preloader').show();
                                    },
                                   success: function(result)  {
                                        $('#preloader').hide();
                                        swal(
                                            {
                                            type: "success",
                                            title: "Başarılı",
                                            html:  result.mesaj,
                                            showCloseButton: false,
                                            showCancelButton: false,
                                            showConfirmButton:false,
                                        });
                                         applyFilters();
                                        /* var namesType = $.fn.dataTable.absoluteOrder( [
                                             { value: null, position: 'bottom' }
                                             ] );
                                        $.fn.dataTable.moment('DD.MM.YYYY');
                                        var table1 = $('#adisyon_liste').DataTable();
                                        var table5 = $('#adisyon_liste_musteri').DataTable();
                                        var table2 = $('#adisyon_liste_hizmet').DataTable();
                                        var table3 = $('#adisyon_liste_urun').DataTable();
                                        var table4 = $('#adisyon_liste_paket').DataTable();
                                        
                                        
                                        // Reload tables without resetting pagination
                                        table1.ajax.reload(null, false);
                                        table5.ajax.reload(null, false);
                                        table2.ajax.reload(null, false);
                                        table3.ajax.reload(null, false);
                                        table4.ajax.reload(null, false);*/
                                        
                        
                                        
                           
                                    },
                                    error: function (request, status, error) {
                                        $('#preloader').hide();
                                         document.getElementById('hata').innerHTML = request.responseText;
                                    }
                            });
                        }
    });
});
$(document).on('submit','#senetli_adisyon_tahsilat',function(e){
    e.preventDefault();
        var formData = new FormData();
        $('#senetli_adisyon_tahsilat .adisyon_kalemler').each(function(){
            if($(this).attr('name')=='adisyon_odeme_hizmet[]'){
                formData.append('adisyon_hizmet_id[]',$(this).attr('data-value'));
                formData.append('hizmet_odeme_secili[]','true');
            }
            if($(this).attr('name')=='adisyon_odeme_urun[]'){
                formData.append('adisyon_urun_id[]',$(this).attr('data-value'));
                formData.append('urun_odeme_secili[]','true');
            }
            if($(this).attr('name')=='adisyon_odeme_paket[]'){
                formData.append('adisyon_paket_id[]',$(this).attr('data-value'));
                formData.append('paket_odeme_secili[]','true');
            }
        });
        if($('#senetli_indirim_tutari').prop('disabled'))
            formData.append('indirim_tutari',$('#senetli_indirim_tutari').val())
        var other_data = $(this).serializeArray();
            $.each(other_data,function(key,input){
                formData .append(input.name,input.value);
        });
        for (var pair of formData.entries()) {
            console.log(pair[0]+ ', ' + pair[1]);
        }
        $.ajax({
            type: "POST",
            url: '/isletmeyonetim/senetlitahsilatekle',
            dataType: "json",
            data : formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $("#preloader").show();
            },
            success: function(result)  {
                $("#preloader").hide();
                swal(
                    {
                        type: 'success',
                        title: 'Başarılı',
                        text: result.statustext,
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer:3000,
                    }
                );
                $('button[data-dismiss="modal"]').trigger('click');
                $('#tahsilat_listesi').empty();
                $('#tahsilat_listesi').append(result.html);
                $('#tahsilat_sayisi').val(result.tahsilat_sayisi);
                $('#tahsil_edilen_tutar').empty();
                $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').empty();
                $('#tahsil_edilen_tutar').append(result.tahsilat_tutari);
                $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').append(result.kalan_tutar);
                $('#senetli_adisyon_tahsilati_hizmetler').empty();
                $('#senetli_adisyon_tahsilati_hizmetler').append(result.adisyon_hizmetler_html);
                $('#senetli_adisyon_tahsilati_urunler').empty();
                $('#senetli_adisyon_tahsilati_urunler').append(result.adisyon_urunler_html);
                $('#seneetli_adisyon_tahsilati_paketler').empty();
                $('#seneetli_adisyon_tahsilati_paketler').append(result.adisyon_paketler_html);
                $('#adisyon_tahsilati_senetler').empty();
                $('#adisyon_tahsilati_senetler').append(result.adisyon_paketler_html);
                adisyonyenidenhesapla();
                if(parseFloat(result.kalan_tutar) == parseFloat(0)){
                    $('#yeni_tahsilat_ekle').attr('disabled','true');
                }
            },
            error: function (request, status, error) {
                $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
            }
        });
});
$('table').on('click','a[name="taksit_vadeleri"]', function(e){
    var taksitid = $(this).attr('data-value');
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/taksitvadegetir',
        dataType: "text",
        data : {id:taksitid,sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            $('#taksit_vade_listesi').empty();
            $('#taksit_vade_listesi').append(result);
            $('#taksitli_tahsilat_id').val(taksitid);
            $('#taksit_detay_modal_ac').trigger('click');
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#senet_yeni_hizmet').click(function(e){
});
function senet_tutar_hesapla()
{
    var toplam = 0;
    $('input[name="senet_hizmet_fiyat[]"]').each(function(){
        toplam += parseFloat($(this).val());
    });
    $('input[name="urun_fiyat_senet[]"]').each(function(){
        toplam += parseFloat($(this).val());
    });
    $('input[name="paket_fiyat_senet[]"]').each(function(){
        toplam += parseFloat($(this).val());
    });
    $('#senet_tutar').val(accounting.formatMoney(toplam-parseFloat($('#on_odeme_tutari').val().replace(".", "")), "", 2, ".", ","));
}
$('#on_odeme_tutari').on('keyup paste change',function (e) {
     if(parseFloat($('#on_odeme_tutari').val().replace(".", ""))>parseFloat($('#senet_tutar').val().replace(".", "")))
     {
        swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text:  'Tahsilat tutarı adisyon tutarından büyük olamaz!',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:3000,
                            }
        );
        $('#on_odeme_tutari').val(0);
    }
   senet_tutar_hesapla();
});


$('#onOdemeTutari').on('keyup paste change',function (e) {

    var onOdemeTutari = parseFloat($('#onOdemeTutari').val().replace(".", ""));
    var taksitTutari = parseFloat($('#indirimli_toplam_tahsilat_tutari').val().replace(".", ""));
    
    var kalanTutar = taksitTutari-onOdemeTutari;
    
    
    $('#taksit_tutar').val(kalanTutar);
    $('#taksit_tutar').trigger('change');
    
      
});

$('#secilenpaket_satis_yap').click(function(e){
    e.preventDefault();
    var i = 0;
    var html='';
    $('input:checkbox[name="paket_bilgi[]"]:checked').each(function(){
         i++;
         var row=$(this).closest("tr")[0];
         html+= "<div class='row'><div class='col-md-12'><p style='font-size:18px;font-weight:bold;margin-bottom:10px;margin-top:10px'>"+row.cells[1].innerHTML
+"</p></div>"+
                "<div class='col-md-6'><label>Seans Sayısı</label><input type='tel' class='form-control' name='paket_satis_seans[]' style='height:38px;margin-bottom:10px'></div>"+
                "<div class='col-md-6'><label>Fiyat</label><input type='tel' class='form-control' name='paket_satis_fiyat[]' style='height:38px;margin-bottom:10px'></div>"+
                "</div> ";
    });
    if(i==0 || $('select[name="paket_satis_musteri_id"]').val()==0 )
    {
          swal(
                {
                    type: 'warning',
                    title: 'Uyarı',
                    text: 'Satış yapmadan önce lütfen paket ve müşteri seçiniz.',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
         });
    }
    else{
        swal({
        html: "<p style='font-size:18px;font-weight:bold;'>Seans ve gün aralığını belirleyin!</p>"+
                html,
        showCancelButton: false,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Tahsilata Git',
        showCloseButton:true,
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
            if(result.value){
                if(
                    ($('input[name="paket_satis_seans_baslangic[]"]').val()!='' && $('input[name="paket_satis_seans_araligi[]"]').val()!='' && $('input[name="paket_satis_seans_saati[]"]').val()!='' && $('#paketRandevuOlustur2').prop('checked'))
                    || (!$('#paketRandevuOlustur2').prop('checked'))
                )
                {
                    var personelIds = [];
                  
                   
                    var formData = new FormData();
                    $('input[name="paket_satis_seans[]"]').each(function(){
                        formData.append('paket_satis_seans[]',$(this).val());
                    });
                    $('input[name="paket_satis_fiyat[]"]').each(function(){
                        formData.append('paket_satis_fiyat[]',$(this).val());
                    });
                    /*$('input[name="paket_satis_seans_baslangic[]"]').each(function(){
                        formData.append('paket_satis_seans_baslangic[]',$(this).val());
                    });
                    $('input[name="paket_satis_seans_araligi[]"]').each(function(){
                        formData.append('paket_satis_seans_araligi[]',$(this).val());
                    });
                     $('input[name="paket_satis_seans_saati[]"]').each(function(){
                        formData.append('paket_satis_seans_saati[]',$(this).val());
                    });
                    $('select[name="paket_satis_personel_id[]"]').each(function(){
                        formData.append('paket_satis_personel_id[]',$(this).val());
                        personelIds.push($(this).val());
                    });
                    if($('#paketRandevuOlustur2').prop('checked')){
                        
                        formData.append('paketRandevuOlustur','on');
                    }*/
                    
                    var data1 = $('#paket_satis_form').serializeArray();
                    $.each(data1,function(key,input){
                        formData.append(input.name,input.value);
                    });
                    $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/pakettahsilatagit',
                        dataType: "text",
                        data : formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('input[name="_token"]').val()
                        },
                        beforeSend: function() {
                            $("#preloader").show();
                        },
                        success: function(result2)  {
                            $("#preloader").hide();
                            window.location.href = '/isletmeyonetim/tahsilat/'+result2;
                        },
                        error: function (request, status, error) {
                            $("#preloader").hide();
                            document.getElementById('hata').innerHTML = request.responseText;
                        }
                    });
                }
                else
                {
                    swal(
                                {
                                    type: 'warning',
                                    title: 'Başarılı',
                                    html: "<p>Otomatik randevu oluşturmak için seans bilgilerini girmeniz gerekmektedir.</p>",
                                    showCloseButton: false,
                                    showCancelButton: false,
                                    showConfirmButton:false,
                                }
                            );
                }
            }
        });
        select2YenidenYukle2();
    }
   
    $('input[name="paket_satis_seans_baslangic[]').each(function(){
        $(this).datepicker({
            minDate: new Date(),
            language: "tr",
            autoClose: true,
            dateFormat: "yyyy-mm-dd",
        });
     });
});
$('#paket_hepsini_sec_liste').change(function(e){
    if (this.checked) {
        $('input[type="checkbox"]').each(function(){
            $(this).attr('checked',true);
        });
    }
    else{
        $('input[type="checkbox"]').each(function(){
            $(this).attr('checked',false);
        });
    }
});
$('#urun_hepsini_sec_liste').change(function(e){
    if (this.checked) {
        $('input[type="checkbox"]').each(function(){
            $(this).attr('checked',true);
        });
    }
    else{
        $('input[type="checkbox"]').each(function(){
            $(this).attr('checked',false);
        });
    }
});
$('#secilenurun_satis_yap').click(function(e){
    e.preventDefault();
    var i = 0;
    var html='';
    $('input:checkbox[name="urun_bilgi[]"]:checked').each(function(){
         i++;
         var row=$(this).closest("tr")[0];
         html+= "<div class='row' style='padding-left:45px'><div  class='col-6'><p style='text-align:start; font-size:16px;'>"+row.cells[1].innerHTML
+" için Adet</p></div>"+
"<div  class='col-6 '><input type='tel' class='form-control' style='height:25px; width:120px;' name='urun_adedi_tahsilat[]'></div></div>";
    });
    if(i==0 || $('select[name="urun_satis_musteri_id"]').val()==0 )
    {
          swal(
                {
                    type: 'warning',
                    title: 'Uyarı',
                    text: 'Satış yapmadan önce lütfen urun ve müşteri seçiniz.',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
         });
    }
    else{
        swal({
        html: "<p style='font-size:20px;font-weight:bold;'>Ürün adedini giriniz!</p>"+
                html,
        showCancelButton: false,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Tahsilata Git',
        showCloseButton:true,
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
            if(result.value){
                if($('input[name="urun_adedi_tahsilat[]"]').val()!='')
                {
                    var formData = new FormData();
                    $('input[name="urun_adedi_tahsilat[]"]').each(function(){
                        formData.append('urun_adedi_tahsilat[]',$(this).val());
                    });
                    var data1 = $('#urun_satis_form').serializeArray();
                    $.each(data1,function(key,input){
                        formData.append(input.name,input.value);
                    });
                    $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/uruntahsilatagit',
                        dataType: "text",
                        data : formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('input[name="_token"]').val()
                        },
                        beforeSend: function() {
                            $("#preloader").show();
                        },
                        success: function(result2)  {
                            $("#preloader").hide();
                            window.location.href = '/isletmeyonetim/tahsilat/'+result2;
                        },
                        error: function (request, status, error) {
                            $("#preloader").hide();
                            document.getElementById('hata').innerHTML = request.responseText;
                        }
                    });
                }
                else
                {
                    swal(
                                {
                                    type: 'warning',
                                    title: 'Başarılı',
                                    html: "<p>Lütfen seans başlangıç tarihini ve sayısını giriniz</p>"+
                                                  "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/tahsilat/"+$('select[name="urun_satis_musteri_id"]').val()+"/"+result2.adisyon_id+"?sube="+$('input[name="sube"]').val()+">"
                                                  +"Tahsil Et</a>",
                                    showCloseButton: false,
                                    showCancelButton: false,
                                    showConfirmButton:false,
                                }
                            );
                }
            }
        });
    }
});
$('#musteriindirimleri').on('submit',function(e){
    e.preventDefault();
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/musteriindirimkaydet',
                    dataType: "text",
                    data : $(this).serialize(),
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                       $("#preloader").hide();
                        swal({
                            type: "success",
                            title: "İşlem başarıyla kaydedildi.",
                            text:  result.status,
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                        });
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$(document).ready(function(){
     $("#sadik_acikkapali").change(function() {
        if ($(this).is(":checked")) {
          // Checkbox is checked, enable the input and set its value
          $("#sadik_musteri_indirimi").prop("disabled", false).val("");
        } else {
          // Checkbox is not checked, disable the input and set its value to 0
          $("#sadik_musteri_indirimi").prop("disabled", true).val("0");
        }
    });
    $("#aktif_acikkapali").change(function() {
        if ($(this).is(":checked")) {
          // Checkbox is checked, enable the input and set its value
          $("#aktif_musteri_indirimi").prop("disabled", false).val("");
        } else {
          // Checkbox is not checked, disable the input and set its value to 0
          $("#aktif_musteri_indirimi").prop("disabled", true).val("0");
        }
      });
        /*function initializeDualListbox(selector, ajaxRoute, searchQuery, selectYenidenYukle) {
    if (selectYenidenYukle) {
        $(selector).bootstrapDualListbox({
            removeAllLabel: 'Hepsini Kaldır',
            moveAllLabel: 'Tümünü Seç',
            infoText: '{0} kişi',
            infoTextEmpty: 'Boş müşteri listesi',
            filterPlaceHolder: 'Müşteri Ara',
            preserveSelectionOnMove: 'moved', // Seçimleri koru
        });
    }

    function loadCustomers(search) {
        let select = $(selector);

        // **Seçili öğeleri AL (Ajax çağrısından önce)**
        let selectedOptions = [];
        select.find("option:selected").each(function () {
            selectedOptions.push($(this).val());
        });

        console.log("Seçili öğeler (önce):", selectedOptions); // Test için ekle

        $.ajax({
            url: ajaxRoute,
            type: "GET",
            data: { query: search, sube: $('input[name="sube"]').val() },
            success: function (data) {
                select.empty(); // Mevcut öğeleri temizle

                let foundSelected = []; // Ajax ile gelen veriler içindeki seçili ID'leri takip etmek için
                
                $.each(data, function (index, customer) {
                    let option = new Option(customer.ad_soyad, customer.id);

                    if (selectedOptions.includes(customer.id.toString())) {
                        $(option).prop("selected", true);
                        foundSelected.push(customer.id.toString()); // Seçili olarak bulunanları kaydet
                    }

                    select.append(option);
                });

                // **Seçili olup Ajax çağrısı ile gelmeyen öğeleri tekrar ekle**
                selectedOptions.forEach(function (id) {
                    if (!foundSelected.includes(id)) {
                        let option = new Option("Seçili Müşteri (Silinmiş)", id, true, true);
                        select.append(option);
                    }
                });

                select.bootstrapDualListbox('refresh'); // Listeyi güncelle
                console.log("Seçili öğeler (sonra):", select.find("option:selected").map(function () { return $(this).val(); }).get());
            },
            error: function (request, status, error) {
                document.getElementById('hata').innerHTML = request.responseText;
            }
        });
    }

    loadCustomers(searchQuery);
}

// **Dual Listbox'ları Başlat**
/*var dualboxvar = false;
if ($('select[name="duallistbox_demo1[]"]').length) {
    initializeDualListbox('select[name="duallistbox_demo1[]"]', "/isletmeyonetim/musteri-arama-bolumu-verileri", "", true);
    dualboxvar = true;
}
if ($('select[name="duallistbox_demo2[]"]').length) {
    initializeDualListbox('select[name="duallistbox_demo2[]"]', "/isletmeyonetim/musteri-arama-bolumu-verileri", "", true);
    dualboxvar = true;
}
if ($('select[name="duallistbox_demo3[]"]').length) {
    initializeDualListbox('select[name="duallistbox_demo3[]"]', "/isletmeyonetim/musteri-arama-bolumu-verileri", "", true);
    dualboxvar = true;
}

if (dualboxvar) {
    $(document).on("input", ".box1 .filter", function () {
        let searchQuery = $(this).val();
        console.log("Ara: " + searchQuery);

        let selectBox = $(this).closest('.box1').find('select');
        let selectName = selectBox.attr("name").replace("_helper1", ""); // "_helper1" kısmını kaldır

        initializeDualListbox('select[name="' + selectName + '"]', "/isletmeyonetim/musteri-arama-bolumu-verileri", searchQuery, false);
    });
}


*/
   
   
});
$(document).on('change paste keyup', 'input[name="himzet_tahsilat_tutari_girilen[]"]' ,function (e) {
    e.preventDefault();
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/hizmettahsilattutaridegistir',
                    dataType: "text",
                    data : {_token:$('input[name="_token"]').val(),adisyonhizmetid :$(this).attr('data-value'),tutar:$(this).val(),sube:$('input[name="sube"]').val()},
                    success: function(result)  {
                        tahsilatyenidenhesapla();
                    },
                    error: function (request, status, error) {
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$(document).on('click','.tahsilat_hizmet_hediye_ver', function(){
    if ($(this).hasClass('tahsilatVar')) {
            swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text: 'Hediye vermek istediğiniz hizmete ait tahsilat bulunmaktadır. Lütfen önce tahsilatı siliniz!',
                                showCloseButton: true,
                                showCancelButton: false,
                                showConfirmButton:false,
                            }
            );
    } 
    else
    {
        var satis_duzenle = false;
        if($('#satis_takibi_ekrani').length)
            satis_duzenle = true;
        $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/hizmet-hediye-isle',
                        dataType: "json",
                        data : {satisDuzenle:satis_duzenle,_token:$('input[name="_token"]').val(),adisyonhizmetid :$(this).attr('data-value'),adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                        success: function(result)  {
                          

                            if($('#tum_tahsilatlar').length)
                            {
                                    $('#tum_tahsilatlar').empty();
                                    $('#tum_tahsilatlar').append(result.kalemler);
                                    
                            }
                            if($('#tum_tahsilatlar_duzenleme').length)
                            {
                                    $('#tum_tahsilatlar_duzenleme').empty();
                                    $('#tum_tahsilatlar_duzenleme').append(result.kalemler);
                                  
                            }
                            tahsilatyenidenhesapla();
                        },
                        error: function (request, status, error) {
                            document.getElementById('hata').innerHTML = request.responseText;
                        }
        });
    }
    
});
$(document).on('click','.tahsilat_hizmet_hediye_kaldir', function(){
    var satis_duzenle = false;
    if($('#satis_takibi_ekrani').length)
        satis_duzenle = true;
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/hizmet-hediye-kaldir',
                    dataType: "json",
                    data : {satisDuzenle:satis_duzenle, _token:$('input[name="_token"]').val(),adisyonhizmetid :$(this).attr('data-value'),adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                    success: function(result)  {
                        if($('#tum_tahsilatlar').length)
                            {
                                    $('#tum_tahsilatlar').empty();
                                    $('#tum_tahsilatlar').append(result.kalemler);
                                    
                            }
                            if($('#tum_tahsilatlar_duzenleme').length)
                            {
                                    $('#tum_tahsilatlar_duzenleme').empty();
                                    $('#tum_tahsilatlar_duzenleme').append(result.kalemler);
                                  
                            }
                        tahsilatyenidenhesapla();
                    },
                    error: function (request, status, error) {
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$(document).on('click','.tahsilat_urun_hediye_ver', function(){
    if ($(this).hasClass('tahsilatVar')) {
            swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text: 'Hediye vermek istediğiniz ürüne ait tahsilat bulunmaktadır. Lütfen önce tahsilatı siliniz!',
                                showCloseButton: true,
                                showCancelButton: false,
                                showConfirmButton:false,
                            }
            );
    } 
    else
    {
        var satis_duzenle = false;
        if($('#satis_takibi_ekrani').length)
            satis_duzenle = true;
         $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/urun-hediye-isle',
                        dataType: "json",
                        data : {satisDuzenle:satis_duzenle,_token:$('input[name="_token"]').val(),adisyonurunid :$(this).attr('data-value'),adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                        success: function(result)  {
                            if($('#tum_tahsilatlar').length)
                            {
                                    $('#tum_tahsilatlar').empty();
                                    $('#tum_tahsilatlar').append(result.kalemler);
                                    
                            }
                            if($('#tum_tahsilatlar_duzenleme').length)
                            {
                                    $('#tum_tahsilatlar_duzenleme').empty();
                                    $('#tum_tahsilatlar_duzenleme').append(result.kalemler);
                                  
                            }
                            tahsilatyenidenhesapla();
                        },
                        error: function (request, status, error) {
                            document.getElementById('hata').innerHTML = request.responseText;
                        }
        });
    }
   
});
$(document).on('click','.tahsilat_urun_hediye_kaldir', function(){
    var satis_duzenle = false;
    if($('#satis_takibi_ekrani').length)
        satis_duzenle = true;
     $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/urun-hediye-kaldir',
                    dataType: "json",
                    data : {satisDuzenle:satis_duzenle,_token:$('input[name="_token"]').val(),adisyonurunid :$(this).attr('data-value'),adet: $('input[name="urun_adet_girilen[]"][data-value="'+$(this).attr('data-value')+'"]').val(),adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                    success: function(result)  {
                        if($('#tum_tahsilatlar').length)
                            {
                                    $('#tum_tahsilatlar').empty();
                                    $('#tum_tahsilatlar').append(result.kalemler);
                                    
                            }
                            if($('#tum_tahsilatlar_duzenleme').length)
                            {
                                    $('#tum_tahsilatlar_duzenleme').empty();
                                    $('#tum_tahsilatlar_duzenleme').append(result.kalemler);
                                  
                            }
                        tahsilatyenidenhesapla();
                    },
                    error: function (request, status, error) {
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$(document).on('click','.tahsilat_paket_hediye_ver', function(){
     if ($(this).hasClass('tahsilatVar')) {
            swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text: 'Hediye vermek istediğiniz pakete ait tahsilat bulunmaktadır. Lütfen önce tahsilatı siliniz!',
                                showCloseButton: true,
                                showCancelButton: false,
                                showConfirmButton:false,
                            }
            );
    } 
    else
    {
        var satis_duzenle = false;
        if($('#satis_takibi_ekrani').length)
            satis_duzenle = true;
         $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/paket-hediye-isle',
                        dataType: "json",
                        data : {satisDuzenle:satis_duzenle,_token:$('input[name="_token"]').val(),adisyonpaketid :$(this).attr('data-value'),adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                        success: function(result)  {
                            if($('#tum_tahsilatlar').length)
                            {
                                    $('#tum_tahsilatlar').empty();
                                    $('#tum_tahsilatlar').append(result.kalemler);
                                    
                            }
                            if($('#tum_tahsilatlar_duzenleme').length)
                            {
                                    $('#tum_tahsilatlar_duzenleme').empty();
                                    $('#tum_tahsilatlar_duzenleme').append(result.kalemler);
                                  
                            }
                            tahsilatyenidenhesapla();
                        },
                        error: function (request, status, error) {
                            document.getElementById('hata').innerHTML = request.responseText;
                        }
        });
    }
   
});
$(document).on('click','.tahsilat_paket_hediye_kaldir', function(){
    var satis_duzenle = false;
    if($('#satis_takibi_ekrani').length)
        satis_duzenle = true;
     $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/paket-hediye-kaldir',
                    dataType: "json",
                    data : { satisDuzenle:satis_duzenle,_token:$('input[name="_token"]').val(),adisyonpaketid :$(this).attr('data-value'),adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                    success: function(result)  {
                        if($('#tum_tahsilatlar').length)
                            {
                                    $('#tum_tahsilatlar').empty();
                                    $('#tum_tahsilatlar').append(result.kalemler);
                                    
                            }
                            if($('#tum_tahsilatlar_duzenleme').length)
                            {
                                    $('#tum_tahsilatlar_duzenleme').empty();
                                    $('#tum_tahsilatlar_duzenleme').append(result.kalemler);
                                  
                            }
                        tahsilatyenidenhesapla();
                    },
                    error: function (request, status, error) {
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$(document).on('change paste keyup', 'input[name="urun_tahsilat_tutari_girilen[]"]' ,function () {
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/uruntahsilattutaridegistir',
                    dataType: "text",
                    data : {_token:$('input[name="_token"]').val(),adisyonurunid :$(this).attr('data-value'),tutar:$(this).val(),adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                    success: function(result)  {
                        tahsilatyenidenhesapla();
                    },
                    error: function (request, status, error) {
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$(document).on('change paste keyup', 'input[name="urun_adet_girilen[]"]' ,function () {
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/urunadetdegistir',
                    dataType: "json",
                    data : {_token:$('input[name="_token"]').val(),adisyonurunid :$(this).attr('data-value'),adet:$(this).val(),adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                    success: function(result)  {
                        console.log(result.kalemler);
                        if($('#tum_tahsilatlar').length)
                            {
                                    $('#tum_tahsilatlar').empty();
                                    $('#tum_tahsilatlar').append(result.kalemler);
                                    
                            }
                            if($('#tum_tahsilatlar_duzenleme').length)
                            {
                                    $('#tum_tahsilatlar_duzenleme').empty();
                                    $('#tum_tahsilatlar_duzenleme').append(result.kalemler);
                                  
                            }
                        tahsilatyenidenhesapla();
                    },
                    error: function (request, status, error) {
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$(document).on('change paste keyup', 'input[name="paket_seans_girilen[]"]' ,function () {
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/paketseansdegistir',
                    dataType: "json",
                    data : {_token:$('input[name="_token"]').val(),adisyonpaketid :$(this).attr('data-value'),seans:$(this).val(),adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                    success: function(result)  {
                        console.log(result.kalemler);
                        if($('#tum_tahsilatlar').length)
                            {
                                    $('#tum_tahsilatlar').empty();
                                    $('#tum_tahsilatlar').append(result.kalemler);
                                    
                            }
                            if($('#tum_tahsilatlar_duzenleme').length)
                            {
                                    $('#tum_tahsilatlar_duzenleme').empty();
                                    $('#tum_tahsilatlar_duzenleme').append(result.kalemler);
                                  
                            }
                        tahsilatyenidenhesapla();
                    },
                    error: function (request, status, error) {
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$(document).on('change paste keyup', 'input[name="hizmet_seans_girilen[]"]' ,function () {
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/hizmetseansdegistir',
                    dataType: "json",
                    data : {_token:$('input[name="_token"]').val(),adisyonhizmetid :$(this).attr('data-value'),seans:$(this).val(),adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                    success: function(result)  {
                        console.log(result.kalemler);
                       if($('#tum_tahsilatlar').length)
                            {
                                    $('#tum_tahsilatlar').empty();
                                    $('#tum_tahsilatlar').append(result.kalemler);
                                    
                            }
                            if($('#tum_tahsilatlar_duzenleme').length)
                            {
                                    $('#tum_tahsilatlar_duzenleme').empty();
                                    $('#tum_tahsilatlar_duzenleme').append(result.kalemler);
                                  
                            }
                        tahsilatyenidenhesapla();
                    },
                    error: function (request, status, error) {
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
$(document).on('change paste keyup', 'input[name="paket_tahsilat_tutari_girilen[]"]' ,function () {
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/pakettahsilattutaridegistir',
                    dataType: "text",
                    data : {_token:$('input[name="_token"]').val(),adisyonpaketid :$(this).attr('data-value'),tutar:$(this).val(),adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                    success: function(result)  {
                        tahsilatyenidenhesapla();
                    },
                    error: function (request, status, error) {
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
function tahsilatUrunSil(kalem,dogrulama_kodu)
{
    var id = $(this).attr('data-value');
    var row = $(this);
    $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/urunadisyondansil',
                        dataType: "json",
                        data : {adisyonurunid:id,adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                        headers: {
                            'X-CSRF-TOKEN': $('input[name="_token"]').val()
                        },
                        beforeSend: function() {
                            $("#preloader").show();
                        },
                        success: function(result2)  {
                            $("#preloader").hide();
                            if(result2.dogrulamaGerekli){
                                    $(document).off('focusin.modal');
                                    swal({
                                        title: 'Lütfen hesap sahibinin cep telefonuna gönderilen onay kodunu giriniz!',
                                        input: 'text',
                                        showCancelButton: true,
                                        confirmButtonText: 'Gönder',
                                        cancelButtonText: 'Vazgeç',
                                        showLoaderOnConfirm: true,
                                        confirmButtonClass: 'btn btn-success',
                                        cancelButtonClass: 'btn btn-danger',
                                    }).then(function (result3) {
                                        if(result3.value){
                                            tahsilatUrunSil(row, result3.value);                 
                                            // modal focus kilidini geri bağla
                                            $(document).on('focusin.modal', function (e) {
                                                if ($(e.target).closest('.modal').length === 0) {
                                                    e.preventDefault();
                                                    e.stopPropagation();
                                                }
                                            });
                                            

                                           
                                        }
                                    });
                            }
                            else
                            {
                                row.closest('.tahsilat_kalemleri_listesi').remove();
                                tahsilatyenidenhesapla();
                            }
                           
                        },
                        error: function (request, status, error) {
                            $("#preloader").hide();
                            document.getElementById('hata').innerHTML = request.responseText;
                        }
    });

}
$(document).on('click','.tahsilat_urun_sil',function(e) {
    var kalem = $(this);
     if ($(this).hasClass('tahsilatVar')) {
            swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text: 'Silmek istediğiniz ürüne ait tahsilat bulunmaktadır. Lütfen önce tahsilatı siliniz!',
                                showCloseButton: true,
                                showCancelButton: false,
                                showConfirmButton:false,
                            }
            );
    } 
    else
     {
        
        swal({
            title: "Emin misiniz?",
            text: "Ürünü tahsilatını kaldırmak istediğinize emin misiniz? Bu işlem geri alınamaz!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#00bc8c',
            confirmButtonText: 'Kaldır',
            cancelButtonText: "Vazgeç",
            confirmButtonClass: 'btn btn-success',
            cancelButtonClass: 'btn btn-danger',
        }).then(function (result) {
            if(result.value){
                if(result.value){
                    tahsilatUrunSil(kalem,'');
                }
            }
        });
     }   
    
});
function tahsilatHizmetSil(kalem,dogrulama_kodu)
{
        var id = kalem.attr('data-value');
        var row = kalem;
        $.ajax({
                            type: "POST",
                            url: '/isletmeyonetim/adisyon-hizmet-sil',
                            dataType: "json",
                            data : {dogrulamaKodu:dogrulama_kodu ,hizmet_id:id,adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                            headers: {
                                'X-CSRF-TOKEN': $('input[name="_token"]').val()
                            },
                            beforeSend: function() {
                                $("#preloader").show();
                            },
                            success: function(result2)  {
                                $("#preloader").hide();
                                if(result2.dogrulamaGerekli){
                                    $(document).off('focusin.modal');
                                    swal({
                                        title: 'Lütfen hesap sahibinin cep telefonuna gönderilen onay kodunu giriniz!',
                                        input: 'text',
                                        showCancelButton: true,
                                        confirmButtonText: 'Gönder',
                                        cancelButtonText: 'Vazgeç',
                                        showLoaderOnConfirm: true,
                                        confirmButtonClass: 'btn btn-success',
                                        cancelButtonClass: 'btn btn-danger',
                                    }).then(function (result3) {
                                        if(result3.value){
                                            tahsilatHizmetSil(row, result3.value);                 
                                            // modal focus kilidini geri bağla
                                            $(document).on('focusin.modal', function (e) {
                                                if ($(e.target).closest('.modal').length === 0) {
                                                    e.preventDefault();
                                                    e.stopPropagation();
                                                }
                                            });
                                            

                                           
                                        }
                                    });
                                }
                                else{

                                    row.closest('.tahsilat_kalemleri_listesi').remove();
                                    tahsilatyenidenhesapla();
                                }

                            },
                            error: function (request, status, error) {
                                $("#preloader").hide();
                                document.getElementById('hata').innerHTML = request.responseText;
                            }
        });

}
$(document).on('click','.tahsilat_hizmet_sil',function(e) {
    var kalem = $(this);
    if ($(this).hasClass('tahsilatVar')) {
            swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text: 'Silmek istediğiniz hizmete ait tahsilat bulunmaktadır. Lütfen önce tahsilatı siliniz!',
                                showCloseButton: true,
                                showCancelButton: false,
                                showConfirmButton:false,
                            }
            );
    } 
    else
    {
        
        swal({
            title: "Emin misiniz?",
            text: "Hizmeti tahsilatını kaldırmak istediğinize emin misiniz? Bu işlem geri alınamaz!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#00bc8c',
            confirmButtonText: 'Kaldır',
            cancelButtonText: "Vazgeç",
            confirmButtonClass: 'btn btn-success',
            cancelButtonClass: 'btn btn-danger',
        }).then(function (result) {
            if(result.value){
                if(result.value){
                    tahsilatHizmetSil(kalem,'');
                }
            }
        });
    }
    
});
function tahsilatPaketSil(kalem,dogrulama_kodu)
{
        
        var id = kalem.attr('data-value');
        var row = kalem;
        var musteriid = $('select[name="tahsilat_musteri_id"]').val();
        var satis_duzenle = false;
        if($('#satis_takibi_ekrani').length){

            satis_duzenle = true;
        }


                    $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/paketadisyondansil',
                        dataType: "json",
                        data : {dogrulamaKodu:dogrulama_kodu, satisDuzenle:satis_duzenle,adisyonpaketid:id,musteri_id:musteriid,adisyon_id :$('input[name="adisyon_id"]').val(),sube:$('input[name="sube"]').val()},
                        headers: {
                            'X-CSRF-TOKEN': $('input[name="_token"]').val()
                        },
                        beforeSend: function() {
                            $("#preloader").show();
                        },
                       success: function(result2)  {


                            $("#preloader").hide();
                            if(result2.dogrulamaGerekli){
                                $(document).off('focusin.modal');
                                swal({
                                    title: 'Lütfen hesap sahibinin cep telefonuna gönderilen onay kodunu giriniz!',
                                    input: 'text',
                                    showCancelButton: true,
                                    confirmButtonText: 'Gönder',
                                    cancelButtonText: 'Vazgeç',
                                    showLoaderOnConfirm: true,
                                    confirmButtonClass: 'btn btn-success',
                                    cancelButtonClass: 'btn btn-danger',
                                }).then(function (result3) {
                                    if(result3.value){
                                        tahsilatPaketSil(row, result3.value);                 
                                        // modal focus kilidini geri bağla
                                        $(document).on('focusin.modal', function (e) {
                                            if ($(e.target).closest('.modal').length === 0) {
                                                e.preventDefault();
                                                e.stopPropagation();
                                            }
                                        });
                                        

                                       
                                    }
                                });
                            }
                            else
                            {
                                row.closest('.tahsilat_kalemleri_listesi').remove();
                                tahsilatyenidenhesapla();
                            }

                        },
                        error: function (request, status, error) {
                            $("#preloader").hide();
                            document.getElementById('hata').innerHTML = request.responseText;
                        }
                    });
         
          
        
}
$(document).on('click','.tahsilat_paket_sil',function(e) {
    var kalem = $(this);
    if ($(this).hasClass('tahsilatVar')) {
            swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text: 'Silmek istediğiniz pakete ait tahsilat bulunmaktadır. Lütfen önce tahsilatı siliniz!',
                                showCloseButton: true,
                                showCancelButton: false,
                                showConfirmButton:false,
                            }
            );
    } 
    else {
        swal({
            title: "Emin misiniz?",
            text: "Paket tahsilatını kaldırmak istediğinize emin misiniz? Bu işlem geri alınamaz!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#00bc8c',
            confirmButtonText: 'Kaldır',
            cancelButtonText: "Vazgeç",
            confirmButtonClass: 'btn btn-success',
            cancelButtonClass: 'btn btn-danger',
        }).then(function (result) {

        
            if(result.value){
                tahsilatPaketSil(kalem,'');
            }
        });
    }
    
});
function tahsilatyenidenhesapla()
{
    var taksitvadegirdi = 1;
    $('#taksitli_ve_senetli_tahsilatlar').empty();
    $('input[name="taksitvadeler[]"]:checked').each(function(){
        if(!$('.eklenen_taksitler[data-index-number="'+$(this).attr('data-value')+'"]').length)
        {
            $('#taksitli_ve_senetli_tahsilatlar').append('<div class="row tahsilat_kalemleri_listesi taksit_vadeleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="'+$(this).attr('data-value')+'">'+
                              '<div class="col-md-4 col-5 col-xs-5  col-sm-4">Taksit Vadesi '
                              +'</div><div class="col-md-3 col-7 col-xs-7  col-sm-3">'+$('input[name="taksit_vade_tarihi"][data-value="'+$(this).attr('data-value')+'"]').val()+'</div>'
                              +'<div class="col-md-2 col-5 col-xs-5  col-sm-2">1 adet</div>'
                              +'<div class="col-md-3 col-7 col-xs-7  col-sm-3" style="text-align:right">'
                              +'<input type="hidden" name="taksit_vade_id[]" class="eklenen_taksitler" data-index-number="'+$(this).attr('data-value')+'" value="'+$(this).attr('data-value')+'">'
                              +'<input type="hidden" class="form-control try-currency tahsilat_kalemleri" name="taksit_tahsilat_tutari_girilen[]"   value="'+$('span[name="vade_tutar[]"][data-value="'+$(this).attr('data-value')+'"]').text()+'" style="text-align: right;">'
                              +'<p style="position: relative; float: left; width: 70%;">'+$('span[name="vade_tutar[]"][data-value="'+$(this).attr('data-value')+'"]').text()+' ₺</p>'
                              +'<div class="dropdown" style="width: 15%;float:left">'
                              +'<a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown">'
                              +'<i class="dw dw-more"></a></i><div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">'
                              +'<a class="dropdown-item tahsilat_taksit_sil" data-value="'+$(this).attr('data-value')+'" href="#"><i class="dw dw-delete-3"></i> Sil</a> </div> </div>'
                              +'</div>'
                              +'</div>');
        }
        taksitvadegirdi += parseInt(taksitvadegirdi);
        $('#odenecek_tutar').val('0').change();
    });
    var senetvadegirdi = 1;
    $('input[name="senetvadeler[]"]:checked').each(function(){
        if(!$('.eklenen_senetler[data-index-number="'+$(this).attr('data-value')+'"]').length)
        {
            $('#taksitli_ve_senetli_tahsilatlar').append('<div class="row tahsilat_kalemleri_listesi senet_vadeleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="'+$(this).attr('data-value')+'">'+
                              '<div class="col-md-4 col-5 col-xs-5  col-sm-4">Senet Vadesi '
                              +'</div><div class="col-md-3 col-7 col-xs-7  col-sm-3">'+$('input[name="senet_vade_tarihi"][data-value="'+$(this).attr('data-value')+'"]').val()+'</div>'
                              +'<div class="col-md-2 col-5 col-xs-5  col-sm-2">1 adet</div>'
                              +'<div class="col-md-3 col-7 col-xs-7  col-sm-3" style="text-align:right">'
                             +'<input type="hidden" name="senet_vade_id[]" class="eklenen_senetler" data-index-number="'+$(this).attr('data-value')+'" value="'+$(this).attr('data-value')+'">'
                              +'<input type="hidden" class="form-control try-currency tahsilat_kalemleri" name="senet_tahsilat_tutari_girilen[]"  value="'+$('span[name="vade_tutar[]"][data-value="'+$(this).attr('data-value')+'"]').text()+'" style="text-align: right;">'
                              +'<p style="position: relative; float: left; width: 70%;">'+$('span[name="vade_tutar[]"][data-value="'+$(this).attr('data-value')+'"]').text()+' ₺</p>'
                              +'<div class="dropdown" style="width: 15%;float:left">'
                              +'<a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown">'
                              +'<i class="dw dw-more"></a></i><div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">'
                              +'<a class="dropdown-item tahsilat_senet_sil" data-value="'+$(this).attr('data-value')+'" href="#"><i class="dw dw-delete-3"></i> Sil</a> </div> </div>'
                              +'</div>'
                              +'</div>');
        }
        senetvadegirdi += parseInt(senetvadegirdi);
        $('#odenecek_tutar').val('0').change();
    });
    $('input[name="kalemcheck[]"]:checked').each(function(){
        console.log("diğer alacakları seçtim");
        console.log($('#tum_tahsilatlar .tahsilat_kalemleri[data-value="'+$(this).attr('data-value')+'"]').length);
        if(!$('#tum_tahsilatlar .tahsilat_kalemleri[data-value="'+$(this).attr('data-value')+'"]').length)
        {
             console.log("diğer alacakları aktarıyorum");
             let kopya = $('.aktarilacakKalem[data-value="'+$(this).attr('data-value')+'"]').clone();
             kopya.css('display','');
             kopya.addClass('tahsilat_kalemleri_listesi');
            $('#tum_tahsilatlar').append(kopya);
           
                              
        }
    });

     var ekstragirdiler = 0;
    $('input[name="adisyon_paket_taksit_id[]"]').each(function(){
        if($(this).val()=='')
            ekstragirdiler = parseInt(ekstragirdiler) + 1;
    });
    $('input[name="adisyon_paket_senet_id[]"]').each(function(){
        if($(this).val()=='')
            ekstragirdiler = parseInt(ekstragirdiler) + 1;
    });
    if(ekstragirdiler  == 0)
        $('#harici_indirim_tutari').val('0').change();
    var total = 0;
    var harici_indirim = $('#harici_indirim_tutari').val().replace(".", "");
    harici_indirim = harici_indirim.replace(',','.');
    harici_indirim = parseFloat(harici_indirim);
    if(!$.isNumeric(harici_indirim))
        harici_indirim = 0;
    $('#uygulanan_harici_indirim_tutari').empty();
    $('#uygulanan_harici_indirim_tutari').append(accounting.formatMoney(harici_indirim,'',2,'.',','))
    var musteri_indirimi = $('#musteri_indirim').val().replace(".", "");
    musteri_indirimi = musteri_indirimi.replace(',','.');
    musteri_indirimi = parseFloat(musteri_indirimi);
    var birim_tutar = 0;
    var indirim_tutari = 0;
    var senetli_taksitli_birim_tutar = 0;
    $('#tum_tahsilatlar .tahsilat_kalemleri, #taksitli_ve_senetli_tahsilatlar .tahsilat_kalemleri').each(function() {
  
        var kalemtutar =$(this).val().replace(".", "");
        kalemtutar = kalemtutar.replace(',','.');
        kalemtutar = parseFloat(kalemtutar);
        birim_tutar += kalemtutar;
        if($('input[name="indirim[]"][data-value="'+$(this).attr('data-value')+'"]').val()== '')
            indirim_tutari += kalemtutar*(musteri_indirimi/100);

        console.log("birim tutar :"+birim_tutar);


    });
    if(String(birim_tutar).indexOf('.99') >= 0 || String(birim_tutar).indexOf('.01') >=0)
         birim_tutar = birim_tutar.toFixed(0);
    else
        birim_tutar = birim_tutar.toFixed(2);
    $('#birim_tutar').val(accounting.formatMoney(birim_tutar,'',2,'.',','));
    //var indirim_tutari = birim_tutar*(musteri_indirimi/100);
    var indirimli_tutar = indirim_tutari - harici_indirim;
    senetli_taksitli_birim_tutar = senetli_taksitli_birim_tutar.toFixed(2);
    var toplam = birim_tutar - indirim_tutari - harici_indirim;
    var odenecek_tutar = birim_tutar - indirim_tutari - harici_indirim /*- senetli_taksitli_toplam_tutar()*/;
    indirimli_tutar = indirimli_tutar.toFixed(2);
    toplam = toplam.toFixed(2);
    $('#ara_toplam').empty();
    $('#ara_toplam').append(accounting.formatMoney(birim_tutar,'',2,'.',','));
    $('#uygulanan_indirim_tutari').empty();
    $('#uygulanan_indirim_tutari').append(accounting.formatMoney(indirim_tutari,'',2,'.',','))
    $('#musteri_indirimi').val(indirim_tutari);
    $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').empty();
    $('#tahsil_edilecek_kalan_tutar,.tahsil_edilecek_kalan_tutar').append(accounting.formatMoney(toplam,'',2,'.',','));
    $('#toplam_tahsilat_tutari').val(accounting.formatMoney(odenecek_tutar,'',2,'.',','));
    $('#indirimli_toplam_tahsilat_tutari').val(accounting.formatMoney(odenecek_tutar,'',2,'.',','));
    if($('#yeni_taksitli_tahsilat_olusur').prop('disabled') && ( $('#indirimli_toplam_tahsilat_tutari').val(accounting.formatMoney(odenecek_tutar,'',2,'.',','))!="0,00" || $('#indirimli_toplam_tahsilat_tutari').val(accounting.formatMoney(odenecek_tutar,'',2,'.',','))!="0" ))
        $('#yeni_taksitli_tahsilat_olusur').removeAttr('disabled');
    console.log("Ödenecek toplam tutar : "+odenecek_tutar);
    $('#taksit_tutar').val(accounting.formatMoney(odenecek_tutar,'',2,'.',','));
    if(parseFloat($('#indirimli_toplam_tahsilat_tutari').val().replace(".", "")) == parseFloat(0)){
        $('#yeni_tahsilat_ekle').attr('disabled','true');
    }
    else
        $('#yeni_tahsilat_ekle').removeAttr('disabled');
    $('#odenecek_tutar').val("0,00");

    /*var total  = 0;
    var indirim = parseFloat($('#indirim_tutari').val().replace(".", ""));
    var indirim_carpan = 0;
    if(!$('#indirim_tutari').prop('disabled')){
        indirim_carpan = parseFloat($('#indirim_tutari').val().replace(".", ""));
    }
    var kalan = 0;
    var indirimli_tutar = 0;
    $('.adisyon_kalemler').each(function(){
        if($(this).attr('name')=='adisyon_odeme_hizmet[]'){
            var tutar = parseFloat($('input[name="adisyon_hizmet_tahsilat_tutari_girilen[]"][data-value="'+$(this).attr('data-value')+'"]').val());
            var eksilecek = (tutar/parseFloat($('#birim_tutar').val().replace(".", "")))*indirim;
            var yenitutar = tutar - eksilecek;
            console.log(tutar+' - '+eksilecek +' = '+yenitutar);
            var moneytutar = accounting.formatMoney(tutar,'',2,'.',',');
            var moneyyenitutar = accounting.formatMoney(yenitutar, "", 2, ".", ",");
            $('span[name="adisyon_hizmet_tahsilat_tutari"][data-value="'+$(this).attr('data-value')+'"]').empty();
            $('span[name="adisyon_hizmet_tahsilat_tutari"][data-value="'+$(this).attr('data-value')+'"]').append(moneyyenitutar);
            $('input[name="adisyon_hizmet_tahsilat_tutari[]"][data-value="'+$(this).attr('data-value')+'"]').val(moneyyenitutar);
            indirimli_tutar += yenitutar;
            total += tutar;
        }
        if($(this).attr('name')=='adisyon_odeme_urun[]'){
            var tutar = parseFloat($('input[name="adisyon_urun_tahsilat_tutari_girilen[]"][data-value="'+$(this).attr('data-value')+'"]').val());
            var eksilecek = (tutar/parseFloat($('#birim_tutar').val().replace(".", "")))*indirim;
            var yenitutar = tutar - eksilecek;
             console.log('Birim tutar : ' + parseFloat($('#birim_tutar').val().replace(".", "")));
            console.log(tutar+' - '+eksilecek +' = '+yenitutar);
            var moneytutar = accounting.formatMoney(tutar,'',2,'.',',');
            var moneyyenitutar = accounting.formatMoney(yenitutar, "", 2, ".", ",");
            $('span[name="adisyon_urun_tahsilat_tutari"][data-value="'+$(this).attr('data-value')+'"]').empty();
            $('span[name="adisyon_urun_tahsilat_tutari"][data-value="'+$(this).attr('data-value')+'"]').append(moneyyenitutar);
            $('input[name="adisyon_urun_tahsilat_tutari[]"][data-value="'+$(this).attr('data-value')+'"]').val(moneyyenitutar);
            indirimli_tutar += yenitutar;
            total += tutar;
        }
        if($(this).attr('name')=='adisyon_odeme_paket[]'){
            var tutar = parseFloat($('input[name="adisyon_paket_tahsilat_tutari_girilen[]"][data-value="'+$(this).attr('data-value')+'"]').val());
            var moneytutar = accounting.formatMoney(tutar,'',2,'.',',');
            var eksilecek = (tutar/parseFloat($('#birim_tutar').val().replace(".", "")))*indirim;
            var yenitutar = tutar - eksilecek;
             console.log('Birim tutar : ' + parseFloat($('#birim_tutar').val().replace(".", "")));
            console.log(tutar+' - '+eksilecek +' = '+yenitutar);
            var moneyyenitutar = accounting.formatMoney(yenitutar, "", 2, ".", ",")
            $('span[name="adisyon_paket_tahsilat_tutari"][data-value="'+$(this).attr('data-value')+'"]').empty();
            $('span[name="adisyon_paket_tahsilat_tutari"][data-value="'+$(this).attr('data-value')+'"]').append(moneyyenitutar);
            $('input[name="adisyon_paket_tahsilat_tutari[]"][data-value="'+$(this).attr('data-value')+'"]').val(moneyyenitutar);
            indirimli_tutar += yenitutar;
            total += tutar;
        }
    });
    $('#odenecek_tutar').attr('style','background-color:#fff;border-color:#d4d4d4;font-size:20px;');
    $('#birim_tutar').val(accounting.formatMoney(total+kalan,'',2,'.',','));
    $('#planlanan_alacak_tarihi').attr('style','background-color:#fff;border-color:#d4d4d4;');
    $('#planlanan_alacak_tarihi').val('');
    $('#odenecek_tutar').val(accounting.formatMoney(kalan,'',2,'.',','));
    $('#tahsilat_tutari').val(accounting.formatMoney(total-indirim, "", 2, ".", ","));
    $('#senet_tutar').val(accounting.formatMoney(total-indirim, "", 2, ".", ","));
    $('#taksit_tutar').val(accounting.formatMoney(total-indirim-parseFloat($('#senetli_toplam_tutar').val().replace(".", "")), "", 2, ".", ","));
    $('#indirimli_toplam_tahsilat_tutari').val(accounting.formatMoney(total-indirim, "", 2, ".", ",")).change();*/
}
/*$(document).on('change','input[type="checkbox"]',function(){
    var tahsil_edilecek_tutar = 0;
    var musteri_indirimi = parseFloat($('#musteri_indirim').val().replace(".", ""));
    var harici_indirim = parseFloat($('#harici_indirim_tutari').val().replace(".", ""));
    if(!$.isNumeric(harici_indirim))
        harici_indirim = 0;
    $('.tahsilata_ekle').each(function(){
        var dataid = $(this).attr('data-value');
        if(this.checked){
            var birimtutar = $('.tahsilat_kalemleri[data-value="'+dataid+'"]').val().replace(".", "");
            var indirim =  (birimtutar*(musteri_indirimi/100)) + harici_indirim;
            tahsil_edilecek_tutar += parseFloat(birimtutar-indirim);
        }
    });
    $('#indirimli_toplam_tahsilat_tutari').val(accounting.formatMoney(tahsil_edilecek_tutar, "", 2, ".", ","));
    $('#indirimli_toplam_tahsilat_tutari').trigger('change');
});*/
$('#yeni_ajanda_ekle_form').on('submit',function(e){
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/ajandayayeninotekle',
        dataType: "json",
        data : $('#yeni_ajanda_ekle_form').serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            $("#preloader").show();
        },
       success: function(result)  {
            $("#preloader").hide();
             $('#yeni_ajanda_ekle_form').trigger('reset');
            $('#yeni_ajanda_ekle').modal('hide');
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
            if($('#calendar_ajanda').length)
                 ajandatakvimyukle(false,false);
            $('#ajanda_liste').DataTable().destroy();
            $('#ajanda_liste').DataTable({
                    columns:[
                         { data: 'title'   },
                         { data: 'description' },
                         { data: 'ajanda_hatirlatma'   },
                         { data: 'start' },
                         { data: 'ajanda_durum' },
                         { data: 'ajanda_olusturan' },
                         { data: 'islemler' }
                    ],
                    data: result.ajanda,
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
$(document).on('submit','#ajanda_duzenle_form',function(e){
    e.preventDefault();
    $.ajax({
        type:"GET",
        url:'/isletmeyonetim/ajandaguncelle',
        dataType:"json",
        data:$('#ajanda_duzenle_form').serialize(),
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
        beforeSend: function(){
            $("preloader").show();
        },
        success: function(result){
            $("#preloader").hide();
            $('#ajanda_duzenle_modal').modal('hide');
            $('#ajanda_detay_modal').modal('hide');
            swal({
                type:'success',
                title:'Başarılı',
                text:result.status,
                showCloseButton:false,
                showCancelButton: false,
                showConfirmButton:false,
                timer:3000,
            });
            if($('#calendar_ajanda').length)
                 ajandatakvimyukle(false,false);
            $('#ajanda_liste').DataTable().destroy();
            $('#ajanda_liste').DataTable({
                    columns:[
                         { data: 'title'   },
                         { data: 'description' },
                         { data: 'ajanda_hatirlatma'   },
                         { data: 'start' },
                         { data: 'ajanda_durum' },
                         { data: 'ajanda_olusturan' },
                         { data: 'islemler' }
                    ],
                    data: result.ajanda,
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
             $("#ajanda_duzenle_form")[0].reset();
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('click','a[name="ajanda_notu_duzenle"]',function(e){
        e.preventDefault();
  e.preventDefault();
        $.ajax({
            type: "GET",
                url: '/isletmeyonetim/ajandadetay',
                dataType: "json",
                data : {ajandaid:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                 beforeSend: function(){
                    $('#preloader').show();
                },
                success:function(result){
                     $('#preloader').hide();
                      $('#ajanda_detay_modal').modal('hide');
                     $('#ajanda_id_duzenle').val(result.id);
                     $('#ajandabaslikduzenle').val(result.baslik);
                     $('#ajandaicerikduzenle').val(result.icerik);
                     $('#ajanda_hatirlatma_saat_once_duzenle').val(result.hatirlatmasaat).change();
                     if(result.hatirlatma==true)
                        $('#ajandahatirlatmaduzenle').prop('checked',true);
                    else
                        $('#ajandahatirlatmaduzenle').prop('checked',false);
                    $('#ajandatarihduzenle').val(result.tarih);
                    $('#ajandasaatduzenle').val(result.saat);
                    console.log(result);
                },
                 error: function (request, status, error) {
                        $('#preloader').hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                }
        });
});
$(document).on('click','button[name="ajanda_notu_duzenle"]',function(e){
        e.preventDefault();
        $.ajax({
            type: "GET",
                url: '/isletmeyonetim/ajandadetay',
                dataType: "json",
                data : {ajandaid:$(this).attr('data-value'),sube:$('input[name="sube"]').val()},
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                 beforeSend: function(){
                    $('#preloader').show();
                },
                success:function(result){
                     $('#preloader').hide();
                      $('#ajanda_detay_modal').modal('hide');
                     $('#ajanda_id_duzenle').val(result.id);
                     $('#ajandabaslikduzenle').val(result.baslik);
                     $('#ajandaicerikduzenle').val(result.icerik);
                     $('#ajanda_hatirlatma_saat_once_duzenle').val(result.hatirlatmasaat).change();
                     if(result.hatirlatma==true)
                        $('#ajandahatirlatmaduzenle').prop('checked',true);
                    else
                        $('#ajandahatirlatmaduzenle').prop('checked',false);
                    $('#ajandatarihduzenle').val(result.tarih);
                    $('#ajandasaatduzenle').val(result.saat);
                    console.log(result);
                },
                 error: function (request, status, error) {
                        $('#preloader').hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                }
        });
});
$('#ajanda_liste').on('click','a[name="ajanda_sil"]',function(e){
    e.preventDefault();
    var ajandaid = $(this).attr('data-value');
    swal({
        title: "Emin misiniz?",
        text: "Notunuzu kaldırmak istediğinize emin misiniz? Bu işlem sonrasında notunuzla ilgili bildirim alamazsınız!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Kaldır',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value){
                $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/ajandasil',
                    dataType: "json",
                    data : {ajanda_id:ajandaid,sube:$('input[name="sube"]').val()},
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                   success: function(result)  {
                        $("#preloader").hide();
            if($('#calendar_ajanda').length)
                 ajandatakvimyukle(false,false);
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
                        $('#ajanda_liste').DataTable().destroy();
                        $('#ajanda_liste').DataTable({
                                columns:[
                         { data: 'title'   },
                         { data: 'description' },
                         { data: 'ajanda_hatirlatma'   },
                         { data: 'start' },
                         { data: 'ajanda_durum' },
                         { data: 'ajanda_olusturan' },
                         { data: 'islemler' }
                                ],
                                data: result.ajanda,
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
$(document).on('click','a[name="ajanda_okundu_isaretle"],button[name="ajanda_okundu_isaretle"]',function(e){
    e.preventDefault();
     var ajandaid = $(this).attr('data-value');
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/ajandaokunduisaretle',
        dataType: "json",
        data : {ajanda_id:ajandaid,sube:$('input[name="sube"]').val()},
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
            if($('#calendar_ajanda').length)
                 ajandatakvimyukle(false,false);
            $('#ajanda_liste').DataTable().destroy();
            $('#ajanda_liste').DataTable({
                    columns:[
                         { data: 'title'   },
                         { data: 'description' },
                         { data: 'ajanda_hatirlatma'   },
                         { data: 'start' },
                         { data: 'ajanda_durum' },
                         { data: 'ajanda_olusturan' },
                         { data: 'islemler' }
                    ],
                    data: result.ajanda,
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
function ajandatakvimyukle(preload,turdegisti){
    var curview = $('#calendar_ajanda').fullCalendar('getView');
     var moment = '';
     if($('#takvim_tarihe_gore_ajanda').val()!= '')
        moment = new Date($('#takvim_tarihe_gore_ajanda').val());
     else{
         moment = $('#calendar_ajanda').fullCalendar('getDate');
         moment = moment.format();
     }
     var curdate=
        $.ajax({
               type: "GET",
        url: '/isletmeyonetim/ajandayukle',
        data: {sube:$('input[name="sube"]').val()},
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
            beforeSend: function(){
            if(preload)
                $('#preloader').show();
        },
        success:function(result){
            if(preload){
                $('#preloader').hide();
            }
            if(turdegisti)
            {
                $('#calendar_ajanda').fullCalendar('destroy');
                $('#calendar_ajanda').fullCalendar({
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
             defaultView: curview.type,
             defaultDate: moment,
             editable: false,
             selectable: true,
             events:result.ajanda,
             eventLimit: true, // allow "more" link when too many events
             header: {
               left: 'prev,next today',
               center: 'title dayNames',
               right: 'month,agendaWeek,agendaDay'
             },
            minTime: '06:00:00',
             //// uncomment this line to hide the all-day slot
             allDaySlot: false,
             slotDuration: '00:15:00',
             height:768,
             timeFormat: 'H:mm',
             views: {
                 agenda: {
                     slotLabelFormat: 'H:mm',
                 }
             },
             businessHours: false,
                eventClick:function(event,jsEvent, view){
                    jQuery(".event-icon").html("<i class='fa fa-" + event.icon + "'></i>");
                    jQuery(".event-title").html(event.title);
                    Query(".event-body").html("<div class='row' ><b style='margin-left:20px;'>İçerik :</b> <p style='margin-left:20px;'>"+event.description+"</p></div> <div class='row' ><b style='margin-left:20px;'>Tarih :</b> <p style='margin-left:23px;'>"+event.start.format('DD/MM/YYYY')+"</p></div> </div> <div class='row' ><b style='margin-left:20px;'>Saat :</b> <p style='margin-left:30px;'>"+event.start.format('H:mm')+"</p></div>");
                    jQuery(".event-buttons").html(event.eventbuttons);
                    jQuery(".eventUrl").attr("href", event.url);
                    jQuery("#ajandadetayigetir").trigger('click');
                },
             });
             if($('.fc-day-header').width()<300)
            {
               $('.fc-view-container').attr('style','overflow-x:scroll;');
               $('.fc-day-header').attr('style','width:300px');
               var newwidth=  Number($('.fc-day-header').length*300) + Number(90);
               $('.fc-agendaDay-view').attr('style','width:'+newwidth+'px');
               $('.fc-agendaWeek-view').attr('style','width:'+newwidth+'px');
            }
            else
                $('.fc-view-container').attr('style','overflow-x:scroll');
            $('.fc-axis.fc-widget-header').attr('style','width:44px');
        }
        else{
                $('#calendar_ajanda').fullCalendar('removeEvents');
                $('#calendar_ajanda').fullCalendar('addEventSource', result.ajanda);
                $('#calendar_ajanda').fullCalendar('refetchEvents');
        }
        },
         error: function (request, status, error) {
            if(preload)
                $('#preloader').hide();
            document.getElementById('hata').innerHTML =request.responseText;
        }
    });
}
function updateState(eventId){
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/eventrenk',
        dataType: "json",
        data : {ajanda_id:eventId,sube:$('input[name="sube"]').val()},
        headers: {
             'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        beforeSend: function() {
            $("#preloader").show();
        },
          success: function(result)  {
            $("#preloader").hide();
            if($('#calendar_ajanda').length)
                 ajandatakvimyukle(false,false);
            $('#ajanda_liste').DataTable().destroy();
            $('#ajanda_liste').DataTable({
                    columns:[
                         { data: 'title'   },
                         { data: 'description' },
                         { data: 'ajanda_hatirlatma'   },
                         { data: 'start' },
                         { data: 'ajanda_durum' },
                         { data: 'ajanda_olusturan' },
                         { data: 'islemler' }
                    ],
                    data: result.ajanda,
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
}
$('#senet_taksit_duzenleme_tahsilat').on('submit',function (e) {
    e.preventDefault();
    tahsilatyenidenhesapla();
    $('#senet_taksit_detay_modal').modal('hide');
});
$('#yeni_taksitli_tahsilat_olusur').click(function(e){
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/taksitsenetkontrol',
        dataType: "text",
        data : {user_id:$('select[name="tahsilat_musteri_id"]').val(),sube:$('input[name="sube"]').val()},
        headers: {
             'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result){
            $("#preloader").hide();
            if(result != "")
            {
                 swal({
                    title: "Uyarı!",
                    text: result,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#00bc8c',
                    confirmButtonText: 'Taksit Yap',
                    cancelButtonText: "Vazgeç",
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonClass: 'btn btn-danger',
                }).then(function (result2) {
                    if(result2.value){
                         $('#yeni_taksitli_tahsilat_modal').modal();
                    }
                });
            }
            else
                $('#yeni_taksitli_tahsilat_modal').modal();
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#taksitli_ve_senetli_tahsilatlar').on('click','.tahsilat_taksit_sil',function () {
    var vadeid = $(this).attr('data-value');
    $('input[name="taksitvadeler[]"][data-value="'+vadeid+'"]').prop('checked',false);
    $('.taksit_vadeleri_listesi').each(function(){
        if($(this).attr('data-value') == vadeid)
            $(this).remove();
    });
    tahsilatyenidenhesapla();
});
$('#taksitli_ve_senetli_tahsilatlar').on('click','.tahsilat_senet_sil',function () {
    var vadeid = $(this).attr('data-value');
    $('input[name="senetvadeler[]"][data-value="'+vadeid+'"]').prop('checked',false);
    $('.senet_vadeleri_listesi').each(function(){
        if($(this).attr('data-value') == vadeid)
            $(this).remove();
    });
    tahsilatyenidenhesapla();
});
$(document).on('click','.tahsilat_kalem_sil',function () {
    var kalemid = $(this).attr('data-value');
    console.log("kalem sil");
    $('input[name="kalemcheck[]"][data-value="'+kalemid+'"]').prop('checked',false);
    $('.tahsilat_kalemleri_listesi').each(function(){
        if($(this).attr('data-value') == kalemid)
            $(this).remove();
    });
    tahsilatyenidenhesapla();
});
$('.fc-next-button').click(function(e){
    e.preventDefault();

    takvimyukle(true,true);
});
$(document).on('change','#formmusterisec',function(e){
    e.preventDefault();
    var musteriid = $(this).val();
    $.ajax({
            type: "GET",
            url: '/isletmeyonetim/formmusteribilgigetir',
            dataType: "json",
            data : {musteri_id:musteriid,sube:$('input[name="sube"]').val()},
            beforeSend: function() {
                $("#preloader").show();
            },
           success: function(result)  {
               $("#preloader").hide();
                $('#formmustericeptelefon').val(result.telefon);
                $('#formmusterikimlikno').val(result.tc);
                $('#formmustericinsiyet').val(result.cins);
                $('#formmusteriyas').val(result.yas);
            },
            error: function (request, status, error) {
                 document.getElementById('hata').innerHTML = request.responseText;
                 $("#preloader").hide();
            }
        });
});
$(document).on('change','#formpersonelsec',function(e){
    e.preventDefault();
    var personelid = $(this).val();
    $.ajax({
            type: "GET",
            url: '/isletmeyonetim/formpersonelbilgigetir',
            dataType: "json",
            data : {personel_id:personelid,sube:$('input[name="sube"]').val()},
            beforeSend: function() {
                $("#preloader").show();
            },
           success: function(result)  {
               $("#preloader").hide();
                $('#formpersonelceptelefon').val(result.telefon);
            },
            error: function (request, status, error) {
                 document.getElementById('hata').innerHTML = request.responseText;
                 $("#preloader").hide();
            }
        });
});
$('#arsivformekleme').on('submit',function(e){
    e.preventDefault();
    
    var formsecili = true;
    var musterisecili = true;
    var personelsecili = true;
    var warningtext = "";
    if($('#formtaslaklari').val()=="")
    {
        warningtext = "- Form/sözleşme türünü seçiniz.<br>";
        formsecili = false;
    }
     if($('#formmusterisec').val()=="")
    {
        warningtext = "- Müşteri/danışan seçiniz.<br>";
        musterisecili = false;
    }
    if($('#formpersonelsec').val()=="")
    {

        warningtext = "- İşlemi yapacak personeli seçiniz.<br>";
        personelsecili = false;
    }
    if(formsecili==false||musterisecili==false||personelsecili==false)
    {
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
        $.ajax({
        type:"POST",
        url:'/isletmeyonetim/arsivformekleme',
        dataType:"json",
        data:$('#arsivformekleme').serialize(),
         beforeSend: function() {
            $("#preloader").show();
        },
        success:function(result){
            $("#preloader").hide();
             $('#arsivformekleme').trigger('reset');
            $('#formtaslaklari').val('0').trigger('change');
            $('#formmusterisec').val('0').trigger('change');
            $('#formpersonelsec').val('0').trigger('change');
            $('#formmustericinsiyet').val('0').trigger('change');
                $('#formugondermodal').modal('hide');
            swal({
                type: 'success',
                    title: 'Başarılı',
                    text: 'Form başarıyla kaydedildi',
                     showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
            });
            $('#arsiv_liste').DataTable().destroy();
            $('#arsiv_liste').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_onayli').DataTable().destroy();
            $('#arsiv_liste_onayli').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_onayli,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_beklenen').DataTable().destroy();
            $('#arsiv_liste_beklenen').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_beklenen,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_iptal').DataTable().destroy();
            $('#arsiv_liste_iptal').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_iptal,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_harici').DataTable().destroy();
            $('#arsiv_liste_harici').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_harici,
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
$('#arsivmusteriformgonderme').on('submit',function(e){
    e.preventDefault();
    var formData = new FormData();
    formData.append('musteri_imza',document.getElementById('musteriimza').toDataURL());
    var other_data = $('#arsivmusteriformgonderme').serializeArray();
    $.each(other_data,function(key,input){
        formData.append(input.name,input.value);
    });
    $.ajax({
        type:"POST",
        url:'/musterionamformugonderme',
        dataType:"json",
        data:formData,
        contentType: false,
        cache: false,
        processData:false,
        headers:{
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
         beforeSend: function() {
            $("#preloader").show();
        },
        success:function(result){
                $("#preloader").hide();
                /*if(result.enfeksiyon==true)
                    $('#enfeksiyon').prop('checked',true);
                else
                    $('#enfeksiyon').prop('checked',false);
                if(result.seker==true)
                    $('#seker').prop('checked',true);
                else
                    $('#seker').prop('checked',false);
                if(result.alerji==true)
                    $('#alerji').prop('checked',true);
                else
                    $('#alerji').prop('checked',false);
                if(result.operasyon==true)
                    $('#operasyon').prop('checked',true);
                else
                    $('#operasyon').prop('checked',false);
                if(result.deri==true)
                    $('#deri_hastaligi').prop('checked',true);
                else
                    $('#deri_hastaligi').prop('checked',false);
                if(result.kanama==true)
                    $('#kanama').prop('checked',true);
                else
                    $('#kanama').prop('checked',false);
                if(result.hepatit==true)
                    $('#hepatit').prop('checked',true);
                else
                    $('#hepatit').prop('checked',false);
                if(result.gebelik==true)
                    $('#gebelik').prop('checked',true);
                else
                    $('#gebelik').prop('checked',false);
                if(result.birhafta==true)
                    $('#son_bir_hafta').prop('checked',true);
                else
                    $('#son_bir_hafta').prop('checked',false);
                if(result.ucgun==true)
                    $('#son_uc_gun').prop('checked',true);
                else
                    $('#son_uc_gun').prop('checked',false);
                if(result.biray==true)
                    $('#son_bir_ay').prop('checked',true);
                else
                    $('#son_bir_ay').prop('checked',false);
                if(result.birkachafta==true)
                    $('#son_birkac').prop('checked',true);
                else
                    $('#son_birkac').prop('checked',false);
                if(result.dahaonceislem==true)
                    $('#dahaonceislem').prop('checked',true);
                else
                    $('#dahaonceislem').prop('checked',false);
                if(result.dahaonceislem==true)
                    $('#dahaonceislem').prop('checked',true);
                else
                    $('#dahaonceislem').prop('checked',false);*/
            swal(
                {
                     type: 'success',
                    title: 'Cevaplarınız tarafımıza ulaşmıştır. Teşekkür ederiz.',
                    text: 'İşlem Başarılı',
                     showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                }
            );
                    $("#form_bolumu_musteri").attr('style','display:none');
                    $('#cevapformumusteri').attr('style','display:block');
        },
         error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#arsivmusteriformgonderme2').on('submit',function(e){
    e.preventDefault();
    var formData = new FormData();
    formData.append('musteri_imza',document.getElementById('musteriimza').toDataURL());
    var other_data = $('#arsivmusteriformgonderme2').serializeArray();
    $.each(other_data,function(key,input){
        formData.append(input.name,input.value);
    });
    $.ajax({
        type:"POST",
        url:'/musterionamformugonderme2',
        dataType:"json",
        data:formData,
        contentType: false,
        cache: false,
        processData:false,
        headers:{
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
         beforeSend: function() {
            $("#preloader").show();
        },
        success:function(result){
                $("#preloader").hide();
                
            swal(
                {
                     type: 'success',
                    title: 'Cevaplarınız tarafımıza ulaşmıştır. Teşekkür ederiz.',
                    text: 'İşlem Başarılı',
                     showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                }
            );
                    $("#form_bolumu_musteri").attr('style','display:none');
                    $('#cevapformumusteri').attr('style','display:block');
        },
         error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('#arsivmusteriformgonderme3').on('submit',function(e){
    e.preventDefault();
    var formData = new FormData();
    formData.append('musteri_imza',document.getElementById('musteriimza').toDataURL());
    var other_data = $('#arsivmusteriformgonderme3').serializeArray();
    $.each(other_data,function(key,input){
        formData.append(input.name,input.value);
    });
    $.ajax({
        type:"POST",
        url:'/musterionamformugonderme3',
        dataType:"json",
        data:formData,
        contentType: false,
        cache: false,
        processData:false,
        headers:{
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
         beforeSend: function() {
            $("#preloader").show();
        },
        success:function(result){
                $("#preloader").hide();
                
            swal(
                {
                     type: 'success',
                    title: 'Cevaplarınız tarafımıza ulaşmıştır. Teşekkür ederiz.',
                    text: 'İşlem Başarılı',
                     showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                }
            );
                    $("#form_bolumu_musteri").attr('style','display:none');
                    $('#cevapformumusteri').attr('style','display:block');
        },
         error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('click','a[name="form_yazdir"]' ,function(e){
    e.preventDefault();
    var arsivid = '';
    if($.isNumeric($(this).attr('data-value')))
    {
        arsivid = $(this).attr('data-value');
        $.ajax({
        type: "GET",
        url: '/isletmeyonetim/formyazdir',
        dataType: "text",
        data : {arsiv_id:arsivid,sube:$('input[name="sube"]').val()},
        headers: {
             'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            $('#yazdirilacak').empty()
            $('#yazdirilacak').append(result);
            $('#yazdirilacak div').each(function(){
               // $(this).removeAttr('style');
            });
            var originalContents = $("body").html();
            var printContents = $("#yazdirilacak").html();
            var myStyle = '<link rel="stylesheet" href="public/yeni_panel/vendors/styles/style.css" />';
            var myStyle2 = '<link rel="stylesheet" href="public/yeni_panel/src/plugins/datatables/css/responsive.bootstrap4.min.css" />'
             myWindow = window.open('https://app.randevumcepte.com.tr/'+$(this).attr('data-value'));
             myWindow.document.write(myStyle + myStyle2 + printContents);
             myWindow.print();
        },
        error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
        });
    }
    else{
        var wnd = window.open('https://app.randevumcepte.com.tr/'+$(this).attr('data-value'));
        wnd.print();
    }
});
$(document).on('click','a[name="form_onaylandı"]' ,function(e){
    e.preventDefault();
    var arsivid = $(this).attr('data-value');
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/arsivonaylaform',
        dataType: "json",
        data : {arsiv_id:arsivid,musteriid:$('input[name="musteri_id"]').val(),sube:$('input[name="sube"]').val()},
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
                    text: 'İşlem Başarılı',
                     showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                }
            );
            $('#arsiv_liste').DataTable().destroy();
            $('#arsiv_liste').DataTable({
                         "order": [[ 2, "desc" ]],
                    columns:[
                            { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_onayli').DataTable().destroy();
            $('#arsiv_liste_onayli').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_onayli,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_beklenen').DataTable().destroy();
            $('#arsiv_liste_beklenen').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_beklenen,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_iptal').DataTable().destroy();
            $('#arsiv_liste_iptal').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_iptal,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_harici').DataTable().destroy();
            $('#arsiv_liste_harici').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_harici,
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
});
$(document).on('click','a[name="form_iptal"]' ,function(e){
    e.preventDefault();
    var arsivid = $(this).attr('data-value');
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/arsiviptalform',
        dataType: "json",
        data : {arsiv_id:arsivid,musteriid:$('input[name="musteri_id"]').val(),sube:$('input[name="sube"]').val()},
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
                    text: 'İşlem Başarılı',
                     showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                }
            );
            $('#arsiv_liste').DataTable().destroy();
            $('#arsiv_liste').DataTable({
                         "order": [[ 2, "desc" ]],
                    columns:[
                              { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_onayli').DataTable().destroy();
            $('#arsiv_liste_onayli').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_onayli,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_beklenen').DataTable().destroy();
            $('#arsiv_liste_beklenen').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_beklenen,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_iptal').DataTable().destroy();
            $('#arsiv_liste_iptal').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_iptal,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_harici').DataTable().destroy();
            $('#arsiv_liste_harici').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_harici,
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
});
$('#haricibelgeekleform').on('submit',function(e){
    e.preventDefault();
    var musterisecili = true;
    var personelsecili = true;
    var warningtext = "";
     if($('#haricibelgemusteri').val()=="")
    {
        warningtext = "- Müşteri/danışan seçiniz.<br>";
        musterisecili = false;
    }
    if($('#haricibelgepersonel').val()=="")
    {
        warningtext = "- İşlemi yapacak personeli seçiniz.<br>";
        personelsecili = false;
    }
    if(musterisecili==false||personelsecili==false)
    {
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
    else
    {
        var form=  document.getElementById("hariciformyukle").files[0];
    var formData = new FormData();
    formData.append('hariciformyukle',form);
    var other_data = $('#haricibelgeekleform').serializeArray();
     $.each(other_data,function(key,input){
        formData.append(input.name,input.value);
    });
    $.ajax({
        type:"POST",
        url:'/isletmeyonetim/haricibelgeekleme',
        dataType:"json",
        data:formData,
        contentType: false,
        cache: false,
        processData:false,
        headers:{
            'X-CSRF-TOKEN': $('input[name=_token]').val()
        },
         beforeSend: function() {
            $("#preloader").show();
        },
        success:function(result){
            $("#preloader").hide();
            swal({
                type: 'success',
                    title: 'Başarılı',
                    text: 'Form başarıyla eklendi',
                     showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
            });
             $('#haricibelgeekleform').trigger('reset');
            $('#arsiv_liste').DataTable().destroy();
            $('#arsiv_liste').DataTable({
                         "order": [[ 2, "desc" ]],
                    columns:[
                      { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_onayli').DataTable().destroy();
            $('#arsiv_liste_onayli').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_onayli,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_beklenen').DataTable().destroy();
            $('#arsiv_liste_beklenen').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_beklenen,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_iptal').DataTable().destroy();
            $('#arsiv_liste_iptal').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_iptal,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_harici').DataTable().destroy();
            $('#arsiv_liste_harici').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_harici,
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
$(document).on("click",'a[name="form_tekrar_gonder"]',function(e){
     e.preventDefault();
    $('#arsiv_id').val($(this).attr('data-value'));
});
$('#formutekrargondermodal').on('click','button[name="musteriyeformutekrargonder"]' ,function(e){
 e.preventDefault();
    $.ajax({
        type:"POST",
        url:'/isletmeyonetim/formutekrargonder',
        dataType:"json",
        data:{arsiv_id:$('#arsiv_id').val(),musteriid:$('input[name="musteri_id"]').val(),sube:$('input[name="sube"]').val()},
        headers:{
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
         beforeSend: function() {
            $("#preloader").show();
        },
        success:function(result){
            $("#preloader").hide();
            $('#arsivformekleme').trigger('reset');
           $("#formutekrargondermodal").modal('hide');
            swal({
                type: 'success',
                    title: 'Başarılı',
                    text: 'Form başarıyla gönderildi',
                     showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
            });
            $('#arsiv_liste').DataTable().destroy();
            $('#arsiv_liste').DataTable({
                         "order": [[ 2, "desc" ]],
                    columns:[
                         { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_onayli').DataTable().destroy();
            $('#arsiv_liste_onayli').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_onayli,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_beklenen').DataTable().destroy();
            $('#arsiv_liste_beklenen').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_beklenen,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_iptal').DataTable().destroy();
            $('#arsiv_liste_iptal').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_iptal,
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'
                        }
                    },
            });
            $('#arsiv_liste_harici').DataTable().destroy();
            $('#arsiv_liste_harici').DataTable({
                        "order": [[ 2, "desc" ]],
                    columns:[
                       { data: 'musteriadi'},
                           { data: 'baslik'},
                           { data: 'tarih'},
                           {data:'belge_durum'},
                            { data: 'durum'},
                             { data: 'islemler'},
                    ],
                    data: result.arsiv_harici,
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
});
$('#arsivpersonelformgonderme').on('submit',function(e){
    e.preventDefault();
    var formData = new FormData();
    formData.append('personel_imza',document.getElementById('personelimza').toDataURL());
    formData.append('arsiv_id',$('#arsiv_id').val());
    $.ajax({
        type:"POST",
        url:'/personelonamformugonderme',
        dataType:"json",
        data:formData,
        contentType: false,
        cache: false,
        processData:false,
        headers:{
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
         beforeSend: function() {
            $("#preloader").show();
        },
        success:function(result){
                $("#preloader").hide();
            swal(
                {
                     type: 'success',
                    title: 'İmzanız tarafımıza ulaşmıştır. Teşekkür ederiz.',
                    text: 'İşlem Başarılı',
                     showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                }
            );
                    $("#form_bolumu").attr('style','display:none');
                    $('#cevapformupersonel').attr('style','display:block');
        },
         error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});

$('#arsivpersonelformgonderme2').on('submit',function(e){
    e.preventDefault();
    var formData = new FormData();
    formData.append('personel_imza',document.getElementById('personelimza').toDataURL());
    formData.append('arsiv_id',$('#arsiv_id').val());
    var other_data = $('#arsivpersonelformgonderme2').serializeArray();
    $.each(other_data,function(key,input){
        formData.append(input.name,input.value);
    });
    $.ajax({
        type:"POST",
        url:'/personelonamformugonderme2',
        dataType:"json",
        data:formData,
        contentType: false,
        cache: false,
        processData:false,
        headers:{
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
         beforeSend: function() {
            $("#preloader").show();
        },
        success:function(result){
                $("#preloader").hide();
            swal(
                {
                     type: 'success',
                    title: 'Bilgileriniz tarafımıza ulaşmıştır. Teşekkür ederiz.',
                    text: 'İşlem Başarılı',
                     showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                }
            );
                    $("#form_bolumu").attr('style','display:none');
                    $('#cevapformupersonel').attr('style','display:block');
        },
         error: function (request, status, error) {
            $("#preloader").hide();
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$(document).on('click','a[name="form_goster"]' ,function(e){
    e.preventDefault();
    var wnd = window.open('/'+$(this).attr('data-value'));
});
$('.dropdown-menu.webphone *').click(function(e) {
    e.stopPropagation();
});
$('.numkeypad').click(function(e){
    $('#aranacak_dahili_telefon').append($(this).attr('data-value'));
    $('#dial').val($('#aranacak_dahili_telefon').text());
});
$('#dial').on('keyup paste change',function(e){
    $('#aranacak_dahili_telefon').empty();
    $('#aranacak_dahili_telefon').append($(this).val());
});
$('table').on('click','button[name="ses_kaydi_cal"],a[name="ses_kaydi_cal"]',function(){
    var audio = $("#santral_ses_kaydi");
     $('#calinacak_kayit').attr('src',$(this).attr('data-value'));
    /****************/
    audio[0].pause();
    audio[0].load();//suspends and restores all audio element
    //audio[0].play(); changed based on Sprachprofi's comment below
    audio[0].oncanplaythrough = audio[0].play();
    $('#sescalmodal').modal();
});
$('table').on('click','button[name="ses_kaydi_indir"]',function(){
     $.ajax({
        type:"GET",
        url:'/isletmeyonetim/seskaydiindir',
        dataType:"text",
        data:{url:$(this).attr('data-value')},
        headers:{
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        success:function(result){
            swal(
            {
                     type: 'success',
                    title: 'Dosya indirme işlemi başarıyla başlatıldı.',
                    text: 'İşlem Başarılı',
                     showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                }
            );
        },
         error: function (request, status, error) {
            document.getElementById('hata').innerHTML = request.responseText;
        }
    });
});
$('table').on('click','button[name="musteriyi_ara"]',function (e) {
   e.preventDefault();
   var telefon = $('#webtelefon')
   if(!$('#webtelefon').prop('aria-expanded')||$('#webtelefon').prop('aria-expanded')=='false')
   {
     $('#webtelefon').trigger('click');
   }
   $('#dial').val($(this).attr('data-value'))
   $('#call').trigger('click');
   if($(this).attr('data-index-number') != '')
   {


        $('#arama_detay_modal').modal('hide');
        $('a[name="arama_liste_detaylari"][data-value="'+$(this).attr('data-index-number')+'"]').attr('disabled','disabled');
        $.ajax({
            type:"POST",
            url:'/isletmeyonetim/arama-listesi-arandi-isaretle',
            dataType:"json",
            data:{aramaListeId:$(this).attr('data-index-number'),telefon:$(this).attr('data-value')},
           
            headers:{
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            
            success:function(result){
               
            },
             error: function (request, status, error) {
                $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
            }
        });
   }
    /*if($('#webtelefon').attr('aria-expanded','false')||!$('#webtelefon').hasAttr('aria-expanded'))
    {
        $('#webtelefon').trigger('click');
    }
    $('#dial').val($(this).attr('data-value'))
    $('#call').trigger('click');*/
});
$('#web_telefon_burada_kullan').click(function(){
    let script2 = document.createElement('script');
    script2.src =
        "/public/js/santral/webphone.js?v=3.37";
        document.head.appendChild(script2);
    $('#webtelefon').attr('data-toggle','dropdown');
    $('#webtelefon').removeAttr('data-target');
    $('.webphone').addClass('show');
    //$('#santral-ustune-al').modal('hide');
});
/*jQuery(document).on('click', function (e) {
    if($('.webphone').hasClass('show')){
        if($(e.target).closest(".webphone").length === 0
            && $(e.target).closest("#webtelefon").length===0
            && $(e.target).closest('button[name="musteriyi_ara"]').length===0
            && $(e.target).closest('#call').length===0)
            $('.webphone').removeClass('show');
    }
});*/
$('select[name="tahsilat_musteri_id"]').change(function(){
    console.log("müşteri id "+$(this).val());
    musteri_id = $(this).val();
     $('#taksitli_ve_senetli_tahsilatlar').empty();
                $('#taksit_vade_listesi_tahsilat').empty();
                $('#senet_vade_listesi_tahsilat').empty();
                 $('#musteri_indirim').val(0);
                 $('#musteri_indirimi').val(0);
                 $('#tum_tahsilatlar').empty();
                 $('#tahsilat_listesi').empty();
    if($(this).val()!=0)
    {
        $('.adisyon_ekle_buttonlar').each(function(){
            if($(this).prop('disabled'))
                $(this).removeAttr('disabled');
        });
        $.ajax({
            type:"GET",
            url:'/isletmeyonetim/tahsilatbilgigetir',
            dataType:"json",
            data:{musteriid:musteri_id,sube:$('input[name="sube"]').val(),adisyon_id:$('input[name="adisyon_id"]').val()},
            headers:{
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
        beforeSend: function() {
            $("#preloader").show();
        },
            success:function(result){
                $("#preloader").hide();
                $('#musteri_indirimi').val(result.indirim);
                $('#musteri_indirim').val(result.indirim);
                $('#taksitli_ve_senetli_tahsilatlar').empty();
                $('#taksitli_ve_senetli_tahsilatlar').append(result.taksitler_senetler);
                $('#taksit_vade_listesi_tahsilat').empty();
                $('#taksit_vade_listesi_tahsilat').append(result.tum_taksitler);
                $('#senet_vade_listesi_tahsilat').empty();
                $('#senet_vade_listesi_tahsilat').append(result.tum_senetler);
                //$('#tum_tahsilatlar').append(result.tahsilat_liste);
                 $('#tahsilat_listesi').empty();
                $('#tahsilat_listesi').append(result.odeme_akisi);
                 $('#kalan_odemeler_tahsil').empty();
                $('#kalan_odemeler_tahsil').append(result.tahsilat_liste);

                var musteridata = result.musteribilgi;
                var data={
                        id:musteridata[0].id,
                        text:musteridata[0].text
                };
                var option = new Option(data.text, data.id, false, false);
                $('.musteri_satis').append(option);
                $('.musteri_satis').val(data.id).trigger('change');
                tahsilatyenidenhesapla();
            },
             error: function (request, status, error) {
               $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
            }
        });
    }
    else
    {
        $('.adisyon_ekle_buttonlar').each(function(){
                $(this).attr('disabled','true');
        });
    }
});
$('#kasaya_para_koy_form').on('submit',function(e){
    e.preventDefault();
     $.ajax({
                type: "POST",
                url: '/isletmeyonetim/kasayaparaekle',
                dataType: "json",
                data : $('#kasaya_para_koy_form').serialize(),
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                    $('button[data-dismiss="modal"]').trigger('click');
                    $('#kasaya_para_koy_form').trigger('reset');
                    swal(
                        {
                        type: "success",
                        title: "Başarılı",
                        html:  result.mesaj,
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                    });
                     $('#kasa_gelir_tutari').empty();
                    $('#kasa_gider_tutari').empty();
                    $('#kasa_toplam_tutar').empty();
                    $('#toplam_ciro_tutari').empty();
                    $('#tahsilatlar_listesi').empty();
                    $('#masraflar_listesi').empty();
                    
                    $('#kasa_gelir_tutari').append(result.gelir);
                    $('#kasa_gider_tutari').append(result.gider);
                    $('#kasa_toplam_tutar').append(result.toplam);
                       $('#toplam_ciro_tutari').append(result.toplam_ciro);
                    $('#tahsilatlar_listesi').append(result.tahsilatlar);
                 
                    $('#masraflar_listesi').append(result.masraflar);
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
        });
});
$('#kasadan_para_al_form').on('submit',function(e){
        e.preventDefault();
        $.ajax({
                type: "POST",
                url: '/isletmeyonetim/kasadanparaal',
                dataType: "json",
                data : $('#kasadan_para_al_form').serialize(),
                beforeSend:function(){
                    $('#preloader').show();
                },
                success: function(result)  {
                    $('#preloader').hide();
                    swal(
                        {
                        type: "success",
                        title: "Başarılı",
                        html:  result.mesaj,
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                    });
                    $('button[data-dismiss="modal"]').trigger('click');
                    $('#kasadan_para_al_form').trigger('reset');
                     $('#kasa_gelir_tutari').empty();
                $('#kasa_gider_tutari').empty();
                $('#kasa_toplam_tutar').empty();
                  $('#toplam_ciro_tutari').empty();
                $('#tahsilatlar_listesi').empty();
                $('#masraflar_listesi').empty();
              
                $('#kasa_gelir_tutari').append(result.gelir);
                $('#kasa_gider_tutari').append(result.gider);
                $('#kasa_toplam_tutar').append(result.toplam);
                $('#toplam_ciro_tutari').append(result.toplam_ciro);
                $('#tahsilatlar_listesi').append(result.tahsilatlar);
                $('#masraflar_listesi').append(result.masraflar);
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
        });
});
$('#urun_fiyat_zam_yap').click(function(e){
    e.preventDefault();
    var i = 0;
    var html='';
    var selectedIDs = [];
    var fiyat_text = $('input[name="urun_oran"]').val();
    var formData = new FormData();
    $('input:checkbox[name="urun_bilgi[]"]:checked').each(function(){
        i++;
        selectedIDs.push($(this).val());
        formData.append('urun_id[]',$(this).val());
        formData.append('urun_oran',$(this).val());
    });
    if(i==0)
    {
          swal(
                {
                    type: 'warning',
                    title: 'Uyarı',
                    text: 'Önce lütfen ürün seçiniz.',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
         });
    }
    else{
             console.log("Selected Product IDs:", selectedIDs);
        $.ajax({
            type:'GET',
            url:'/isletmeyonetim/urunfiyatdegistir',
             dataType: "json",
             data :{urun_bilgi: selectedIDs, urun_oran: fiyat_text,sube:$('input[name="sube"]').val()},
              beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                       $("#preloader").hide();
                        swal({
                            type: "success",
                            title: "İşlem başarıyla kaydedildi.",
                            text:  result.status,
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                        });
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
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
        });
    }
});
$('#urun_fiyat_indirim_yap').click(function(e){
    e.preventDefault();
    var i = 0;
    var html='';
     var selectedIDs = [];
    var fiyat_text = $('input[name="urun_oran"]').val();
  var formData = new FormData();
    $('input:checkbox[name="urun_bilgi[]"]:checked').each(function(){
        i++;
        selectedIDs.push($(this).val());
        formData.append('urun_id[]',$(this).val());
        formData.append('urun_oran',$(this).val());
    });
    if(i==0)
    {
          swal(
                {
                    type: 'warning',
                    title: 'Uyarı',
                    text: 'Önce lütfen ürün seçiniz.',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
         });
    }
    else{
             console.log("Selected Product IDs:", selectedIDs);
        $.ajax({
            type:'GET',
            url:'/isletmeyonetim/urunfiyatindirimdegistir',
             dataType: "json",
             data :{urun_bilgi: selectedIDs, urun_oran: fiyat_text,sube:$('input[name="sube"]').val()},
              beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                       $("#preloader").hide();
                        swal({
                            type: "success",
                            title: "İşlem başarıyla kaydedildi.",
                            text:  result.status,
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                        });
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
                    },
                      error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
        });
    }
});
$(document).on("click",'button[name="paracekmeonaykodu"]',function(e){
    e.preventDefault();
    if ($('#paraalma_tutari').val()==0) {
          swal(
                {
                    type: 'warning',
                    title: 'Uyarı',
                    text: 'Lütfen geçerli bir tutar giriniz.',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton:false,
                    timer:3000,
         });
    }
     else{
        $.ajax({
                type: "POST",
                url: '/isletmeyonetim/paracekmeonaykodugonder',
                dataType: "json",
                data : $('#kasadan_para_al_form').serialize(),
                beforeSend:function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
               swal(
                {
                     type: 'success',
                     title: "Kod Gönderildi",
                     showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                }
            );
                },
                error: function (request, status, error) {
                    $('#preloader').hide();
                     document.getElementById('hata').innerHTML = request.responseText;
                }
        });
     }
});
$(document).on("click",'a[name="randevu_sonrasi_islem_notu"]',function(e){
    e.preventDefault();
    randevuid=$(this).attr('data-value')
        swal({
        html: "<p>İşlem sonrası notunuzu ekleyiniz!</p>"+
                "<div class='row'><div class='col-md-12' style='height:60px;margin-top:20px;'><label style='font-size:16px;'>Notunuz</label><textarea maxlenght='7' type='text' class='form-control'"+
                " id='islemsonrasinot' required/></textarea></div></div>",
 showCloseButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'Ekle',
        cancelButtonText : 'Vazgeç',
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
        }).then(function (result) {
          if(result.value){
            if ($('#islemsonrasinot').val()!='') {
                    $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/islemsonrasinotekleme',
                        dataType: "json",
                        data : {sube:$('input[name="sube"]').val(),randevu_id:randevuid,_token:$('input[name="_token"]').val(),islemsonrasinot:$('#islemsonrasinot').val()},
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
                                text: 'İşlem Başarılı',
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
            }
          }
        });
});
$('#musterifotoekleform').on('submit',function(e){
    e.preventDefault();
    var gorseller=  $('#musteriresimyukle').get(0).files.length;
    var formData = new FormData();
    for(var i=0;i<gorseller;i++){
         formData.append('musteriresimyukle[]',$('#musteriresimyukle').get(0).files[i]);
         formData.append('user_id',$('input[name="musteri_id"]').val());
    }
    var other_data = $('#musterifotoekleform').serializeArray();
    $.each(other_data,function(key,input){
        formData.append(input.name,input.value);
    });
    $.ajax({
      url: '/isletmeyonetim/islemsonrasiresimyukleme',
      type: "POST",
      data: formData,
      contentType: false,
        cache: false,
        processData:false,
        beforeSend: function(){
                          $('#preloader').show();
                        },
                      success: function(result) {
                           $('#preloader').hide();
                           swal({
                              type: "success",
                              title: "Başarılı",
                              text:  "Resimler Başarıyla Kaydedildi",
                              showCloseButton: false,
                              showCancelButton: false,
                              showConfirmButton:false,
                              timer: 3000,
                           });
                        var date = $('input[name="resimtarih"]').val();
                         var imageUrl = URL.createObjectURL($('#musteriresimyukle').get(0).files[0]);
                        $('#buttonContainer').append('<button class="btn btn-outline-primary" style="margin-top:10px" data-value"'+result+'" name="islemdetaygetir" data-toggle="modal" data-target="#islemdetayigetirmodal" type="button" name="islemdetaygetir"  > <img src="' + imageUrl + '"  style="width:100px; height:100px;"/> <br> <br>' + date + '</button>');
                      },
         error: function (request, status, error) {
                            $("#preloader").hide();
                            document.getElementById('hata').innerHTML = request.responseText;
                        }
    });
  });
$('button[name="islemdetaygetir"]').click(function(e){
    e.preventDefault();
    console.log($(this).attr('data-value'));
    var islemid = $(this).attr('data-value'); // Use data-value attribute to get the islemid
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/islemdetayigetir',
        dataType: "text",
        data: {islem_id: islemid,sube:$('input[name="sube"]').val()},
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
            $('#islembolumu').empty();
            $('#islembolumu').append(result);
        },
        error: function (request, status, error) {
            document.getElementById('hata').innerHTML = request.responseText;
            $("#preloader").hide();
        }
    });
});
function calendarprevnextclick() {
    moment = $('#calendar').fullCalendar('getDate');
    moment = moment.format();
    curview = $('#calendar').fullCalendar('getView');
    alert(curview.type);
}
$('#periyot').change(function(){
    if($('#aylik_uyelikler').css('display') == 'none')
    {
        $('#aylik_uyelikler').css('display','flex');
        $('#yillik_uyelikler').css('display','none');
        $('html, body').animate({ scrollTop:  $('#aylik_uyelikler').offset().top }, 'slow');
    }
    else
    {
        $('#aylik_uyelikler').css('display','none');
        $('#yillik_uyelikler').css('display','flex');
         $('html, body').animate({ scrollTop:  $('#yillik_uyelikler').offset().top }, 'slow');
    }
});
$('#uyelikiletisimbilgileri').on('submit',function (e) {
    e.preventDefault();
    var formData = new FormData();
    formData.append('sube',$('input[name="sube"]').val());
    var other_data = $('#uyelikiletisimbilgileri').serializeArray();
    $.each(other_data,function(key,input){
        formData.append(input.name,input.value);
    });
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/uyelikiletisimvefaturabilgiguncelle',
        dataType: "text",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result)  {
            $("#preloader").hide();
             window.location.reload();
        },
        error: function (request, status, error) {
            document.getElementById('hata').innerHTML = request.responseText;
            $("#preloader").hide();
        }
    });
})
$('#santral_arama_tum, #santral_gelen_arama, #santral_giden_arama, #santral_cevapsiz_arama').on('click', 'a[name="ses_kaydi_indir"]',function (e) {
        e.preventDefault();
        
        var url = $(this).attr('data-value');  // Get the data-value attribute
        var filename = $(this).attr('href');   // Get the href attribute as the filename
        // Use XMLHttpRequest to fetch the file and force download
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.responseType = 'blob';  // Set the response type to blob
        xhr.onload = function() {
            if (xhr.status === 200) {
                var blob = new Blob([xhr.response], { type: xhr.getResponseHeader('Content-Type') });
                var a = document.createElement('a');
                a.href = window.URL.createObjectURL(blob);
                a.download = filename;  // Set the download attribute with the correct filename
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        };
        xhr.send();
    // body...
});
$('#firma_il').change(function(e){
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
             $('#firma_ilce').empty();
            var data= result.map(item => ({
                        id: item.id,
                        text: item.ilce_adi // Adjust based on your JSON structure
            }));
            data.forEach(item => {
                    var option = new Option(item.text, item.id, false, false);
                    $('#firma_ilce').append(option);
            });
        },
        error: function (request, status, error) {
            document.getElementById('hata').innerHTML = request.responseText;
            $("#preloader").hide();
        }
    });
});
$('#yonetici_ekle').change(function(e) {
    e.preventDefault();
    if($(this).is(':checked')){
        $('#sube_yonetici_ekle').attr('style','display:flex');
        $('#sube_yetkili_adi').attr('required',true);
        $('#sube_yetkili_telefon').attr('required',true);
    }
    else
     {
          $('#sube_yonetici_ekle').attr('style','display:none');
         $('#sube_yetkili_adi').removeAttr('required');
        $('#sube_yetkili_telefon').removeAttr('required');
     }
})
 
 $(document).on('click', '#randevu_modal_kapat', function () {
     
     $('#yenirandevuekleform').trigger('reset');  
});
$(document).on('click', '#sablonkapatmodal', function () {
     
    $('#sablon_formu').trigger('reset');  
});
 $('#otomatik_e_asistan_ayarlari').on('submit',function(e){
    e.preventDefault();
    $.ajax({
                    type: "POST",
                    url: '/isletmeyonetim/e_asistan_ayar_kaydet',
                    dataType: "text",
                    data : $(this).serialize(),
                    beforeSend: function() {
                        $("#preloader").show();
                    },
                    success: function(result)  {
                       $("#preloader").hide();
                        swal({
                            type: "success",
                            title: "E-Asistan ayarları başarıyla kaydedildi.",
                            text:  result.status,
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton:false,
                            timer:3000,
                        });
                    },
                    error: function (request, status, error) {
                        $("#preloader").hide();
                        document.getElementById('hata').innerHTML = request.responseText;
                    }
    });
});
 $(document).on('click','button[name="gorev_iptal_et_randevu"]', function(e){
    e.preventDefault();

    gorevIptalEt('randevu_id',$(this).attr('data-value'),"Randevu hatırlatma görevini iptal etmek istediğinize emin misiniz?");
 });
 $(document).on('click','button[name="gorev_iptal_et_alacak"]', function(e){
    e.preventDefault();
    gorevIptalEt('alacak_id',$(this).attr('data-value'),"Alacak hatırlatma görevini iptal etmek istediğinize emin misiniz?");
 });
 $(document).on('click','button[name="gorev_iptal_et_kampanya"]', function(e){
    e.preventDefault();
    gorevIptalEt('kampanya_id',$(this).attr('data-value'),"Kampanya tanıtım görevini iptal etmek istediğinize emin misiniz?");
 });

 function gorevIptalEt(tur,id,eminMisinizYazi)
 {
     swal({
        title: "Emin misiniz?",
        text: eminMisinizYazi+" Bu işlem geri alınamaz!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: 'İptal Et',
        cancelButtonText: "Vazgeç",
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
    }).then(function (result) {
        if(result.value)
        {
            var formData = new FormData();
            formData.append(tur,id);
            formData.append('_token',$('input[name="_token"]').val());
            formData.append('sube',$('input[name="sube"]').val());
            $.ajax({
                            type: "POST",
                            url: '/isletmeyonetim/gorev-iptal-et',
                            dataType: "json",
                            data : formData,
                            processData: false,
                            contentType: false,
                            beforeSend: function() {
                                $("#preloader").show();
                            },
                            success: function(result)  {
                               $("#preloader").hide();
                                swal({
                                    type: "success",
                                    title: "Başarılı",
                                    text:  result.mesaj,
                                    showCloseButton: false,
                                    showCancelButton: false,
                                    showConfirmButton:false,
                                    timer:3000,
                                });
                                $('#bugunkugorevtablo').DataTable().destroy();
                                $('#yarinkigorevtablo').DataTable().destroy();
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
                                       data: result.easistandata,
                            
                                       "language" : {
                                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                           searchPlaceholder: "Ara",
                                           paginate: {
                                               next: '<i class="ion-chevron-right"></i>',
                                               previous: '<i class="ion-chevron-left"></i>'  
                                           }
                                     },
                               });
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
                                       data: result.easistanYarinYapilacak,
                            
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
   
 }
  function select2YenidenYukle(){

    $(".musteri_secimi").each(function() {
        $('.musteri_secimi').select2({
            placeholder: "Müşteri/Danışan seçin", 
            allowClear: true,
            language: {
                inputTooShort: function() {
                    return "Lütfen en az 2 karakter girin.";
                },
                searching: function() {
                    return "Aranıyor...";
                },
                noResults: function() {
                    return "Sonuç bulunamadı.";
                },
                loadingMore: function() {
                    return "Daha fazla veri yükleniyor...";
                }
            },
            ajax: {
                url: '/isletmeyonetim/musteri-arama-bolumu-verileri', 
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    // Türkçe karakterleri normalize et
                    let term = params.term || '';
                    term = term
                        .replace(/ı/g, 'i').replace(/ğ/g, 'g').replace(/ü/g, 'u')
                        .replace(/ş/g, 's').replace(/ö/g, 'o').replace(/ç/g, 'c')
                        .replace(/İ/g, 'i').replace(/Ğ/g, 'g').replace(/Ü/g, 'u')
                        .replace(/Ş/g, 's').replace(/Ö/g, 'o').replace(/Ç/g, 'c');
                    
                    return { 
                        query: params.term || '', // Orijinal terim
                        normalized_query: term,   // Normalize edilmiş terim
                        sube: $('input[name="sube"]').val(),
                        aramaMi: false,
                    };
                },
                processResults: function(data) {
                    return { 
                        results: data.map(musteri => ({ 
                            id: musteri.id, 
                            text: musteri.ad_soyad 
                        })) 
                    };
                }
            },
            minimumInputLength: 2
        });
    });
      $('.personel_secimi').each(function(){   
         var container = $(this).closest('div');
         var odaSelect = container.find('select[name="randevuodalariyeni[]"]');
         var oda_id = "";
         if(odaSelect)
            oda_id = odaSelect.val();
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
                    odaId: oda_id,
                    aramaMi:false,
                }; // Arama terimi
            },

            processResults: function (data) {
                
                return { results: data.map(personel => ({ id: personel.id, text: personel.ad_soyad })) };
            }

        },
        minimumInputLength: 0 // En az 2 harf girilince aramaya başla
        });
     });

     $('.cihaz_secimi').each(function(){
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
                    console.log(data);
                return { results: data.map(cihaz => ({ id: cihaz.id, text: cihaz.cihaz_adi })) };
            }

        },
        minimumInputLength: 0 // En az 2 harf girilince aramaya başla
        });
});
$('.oda_secimi').each(function(){
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
       }); 
 $('.hizmet_secimi').each(function(e){
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
                
                return { results: data.map(hizmet => ({ id: hizmet.id, text: hizmet.hizmet_adi ,sure: hizmet.sure, fiyat: hizmet.fiyat })) };
            }

        },
        minimumInputLength: 0 // En az 2 harf girilince aramaya başla
        }); 
}); 
        }
        function select2YenidenYukle2(){
            $(".musteri_secimi").each(function(){
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
       
       
            });
             $('.personel_secimi').each(function(){   
                var container = $(this).closest('div');
                var odaSelect = container.find('select[name="randevuodalariyeni[]"]');
                var oda_id = "";
                if(odaSelect)
                    oda_id = odaSelect.val();
                $('.personel_secimi').select2({
                    placeholder: "Personel seçin",  
                    allowClear:true,
                    dropdownParent: $('.swal2-container'),
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
                           odaId :oda_id,
                           aramaMi:false,
                       }; // Arama terimi
                   },
       
                   processResults: function (data) {
                       
                       return { results: data.map(personel => ({ id: personel.id, text: personel.ad_soyad })) };
                   }
       
               },
               minimumInputLength: 0 // En az 2 harf girilince aramaya başla
               });
            });
            $('.cihaz_secimi').each(function(){
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
                           console.log(data);
                       return { results: data.map(cihaz => ({ id: cihaz.id, text: cihaz.cihaz_adi })) };
                   }
       
               },
               minimumInputLength: 0 // En az 2 harf girilince aramaya başla
               });
       });
       $('.oda_secimi').each(function(){
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
              }); 
        $('.hizmet_secimi').each(function(e){
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
               }




$(document).on('click','a[name="adisyon_odeme_detaylari"]',function(e){
    e.preventDefault();
    $.ajax({
            type:"GET",
            url:'/isletmeyonetim/adisyonOdemeDetaylari',
            dataType:"text",
            data:{adisyon_id:$(this).attr('data-value')},
            headers:{
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
        beforeSend: function() {
            $("#preloader").show();
        },
            success:function(result){
                $("#preloader").hide();
                $('#gecmis_odemeler').empty();
                $('#gecmis_odemeler').append(result);
                 $('#odeme_detay_modal').modal();
            },
             error: function (request, status, error) {
               $("#preloader").hide();
                document.getElementById('hata').innerHTML = request.responseText;
            }
        });
   
});
$(document).on('click','#alacaklar_listeme_git',function(e){
    e.preventDefault();
    swal.close();
    $('#senetle_veya_taksitle_tahsil_et').trigger('click');
    $('#taksitli_tahsilatlar_bolumu').trigger('click');
})
$(document).on('click','a[name="paket_tahsilatlari"]',function(e){
    e.preventDefault();
     $.ajax({
            type:"GET",
            url:'/isletmeyonetim/paketTahsilatlari',
            dataType:"json",
            data:{randevuId:$(this).attr('data-value'),hizmetId:$(this).attr('data-index-number')},
            headers:{
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
        beforeSend: function() {
            $("#preloader").show();
        },
            success:function(result){
                if(!result.paketSatisi)
                {
                    
                }
                $("#preloader").hide();
                $('#paket_gecmis_odemeler').empty();
                $('#paket_gecmis_odemeler').append(result.odemeler);
               
                $('#paket_odeme_detay_modal').modal();
            },
             error: function (request, status, error) {
               $("#preloader").hide();
               console.log(error);
                document.getElementById('hata').innerHTML = request.responseText;
            }
        });

});

function aramaListesiniGetir(url)
{
     var namesType = $.fn.dataTable.absoluteOrder( [
                     { value: null, position: 'bottom' }
                     ] );
                 $.fn.dataTable.moment('DD.MM.YYYY');
       $('#arama_liste_tablo').DataTable().destroy();
            
         $('#arama_liste_tablo').DataTable({
                       autoWidth: false,
                       responsive: true,
                       "processing": true,
                       "serverSide": true,
                       "deferRender": true, // DOM yükünü azaltır!
                        "ajax": {
                            "url": url,
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
                        
                                { data: 'arama_baslik'},
                               { data: 'personel'},
                           
                               { data: 'detaylar'},
                            
                     
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
// Unbind any existing submit handlers first
/*$('#arama_listesi_formu').off('submit').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $submitBtn = $form.find('button[type="submit"]');
    
    // Disable submit button to prevent multiple clicks
    $submitBtn.prop('disabled', true);
    
    // Use the form element directly for FormData
    var formData = new FormData(this);
    
    $.ajax({
        type: "POST",
        url: '/isletmeyonetim/arama_listesi_ekle',
        dataType: "json",
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#preloader').show();
        },
        success: function(result) {
            $('#preloader').hide();
            $('button[data-dismiss="modal"]').trigger('click');
            swal({
                type: "success",
                title: "Başarılı",
                text: result.mesaj,
                showCloseButton: false,
                showCancelButton: false,
                showConfirmButton: false,
                timer: 3000,
            });
            aramaListesiniGetir('/isletmeyonetim/arama_listesi_getir');
        },
        error: function(request, status, error) {
            $('#preloader').hide();
            document.getElementById('hata').innerHTML = request.responseText;
        },
        complete: function() {
            // Re-enable the submit button when the request is complete
            $submitBtn.prop('disabled', false);
        }
    });
});*/

let aramaDetayId = null;
let currentPage1 = 1;
let totalKayit = 0;
let perPage2 = 50;
let loading = false;
$(document).on('click', 'a[name="arama_liste_detaylari"]', function (e) {
    e.preventDefault();
    aramaDetayId = $(this).attr('data-value');
    currentPage1 = 1;
    loadAramaDetaylari(1);
    $('#arama_detay_modal').modal('show');
});
function loadAramaDetaylari(page = 1) {
    if (loading) return;
    loading = true;

    $.ajax({
        url: '/isletmeyonetim/arama_liste_detay_getir',
        method: 'POST',
        data: {
            arama_detay_id: aramaDetayId,
            page: page,
            perPage: perPage2,
            _token: $('input[name="_token"]').val()
        },
        success: function (response) {
            const tbody = $('#arama_liste_detay_tablo tbody');
            totalKayit = response.total;

            if (page === 1) {
                tbody.empty();
            }

            let htmlRows = [];

            response.data.forEach(function (item, index) {
                let sesBtn = item.ses
                    ? `<a name="ses_kaydi_cal" data-value="${item.ses}" class="btn btn-danger btn-sm" title="Ses Kaydını Dinle">
                           <i class="fa fa-play" style="color:white"></i>
                       </a>`
                    : '';

                let aramaBtn = `<button name="musteriyi_ara" data-index-number="${item.aramaListesi}" data-value="0${item.telefon}" class="btn btn-sm btn-success" title="Ara">
                                    <i class="fa fa-phone"></i>
                                </button>`;

                let notBtn = `<button class="btn btn-sm btn-warning btn-not-ekle" data-index="${index}" title="Not Ekle">
                                <i class="fa fa-pencil"></i>
                              </button>`;

                let notHTML = item.not || '';
                if (item.not_tarih && item.not_saat) {
                    notHTML += `<br><small class="text-muted">${item.not_tarih} - ${item.not_saat}</small>`;
                }

                htmlRows.push(`
                    <tr data-index="${index}">
                        <td>${item.ad}</td>
                        <td>${item.telefonGizlenmis}</td>
                        <td>${item.durum}</td>
                        <td class="not-cell">${notHTML}</td>
                        <td>${aramaBtn} ${sesBtn} ${notBtn}</td>
                    </tr>
                `);
            });

            tbody.append(htmlRows.join(''));
            currentPage1++;
            loading = false;
        },
        error: function (xhr) {
            console.error("AJAX Hatası:", xhr);
            loading = false;
        }
    });
}
$('#aranacak_musteriler').on('scroll', function () {
    const container = $(this);
    const scrollBottom = container[0].scrollHeight - container.scrollTop() - container.innerHeight();

    if (scrollBottom < 80 && !loading && ((currentPage1 - 1) * perPage2) < totalKayit) {
        loadAramaDetaylari(currentPage1);
    }
});

$(document).on('click', '.btn-not-ekle', function(e) {
    e.preventDefault(); 
    console.log("Not ekle butonuna tıklandı");

    var index = $(this).data('index');

    // Tablo satırını bul
    var row = $('#arama_liste_detay_tablo tbody tr[data-index="'+index+'"]');

    // Not hücresindeki metni al (sadece ilk satırı: içerik)
    var notIcerik = row.find('.not-cell').clone() // clone ile orijinali bozmadan
        .children('small').remove().end() // <small> etiketini kaldır
        .text().trim();

    // <small> içindeki tarih ve saat bilgisini al
    var tarihSaat = row.find('.not-cell small').text().trim(); // örnek: 2025-06-01 - 13:45

    var tarih = '';
    var saat = '';

    if (tarihSaat.includes('-')) {
        var parts = tarihSaat.split('-');
        tarih = parts[0].trim();
        saat = parts[1].trim();
    }

    // Modal alanlarını doldur
    $('#noticerik').val(notIcerik);
    $('#santralnottarih').val(tarih);
    $('#santralnotsaat').val(saat);
    $('#not_id').val(index);

    // Modalı göster
    $('#yeni_not_ekle_santral').modal('show');
});


$('#yeni_not_ekle_form').on('submit', function(e) {
    e.preventDefault();

    var formData = {
        _token: $('input[name="_token"]').val(),
        arama_detay_id: $('#arama_detay_id').val(), // Bu alan modal dışında varsa sayfada tanımlı olmalı
        not_id: $('#not_id').val(),
        noticerik: $('#noticerik').val(),
        santralnottarih: $('#santralnottarih').val(),
        santralnotsaat: $('#santralnotsaat').val()
    };

    $.ajax({
        url: '/isletmeyonetim/santral_not_ekle',
        method: 'POST',
        data: formData,
        success: function(response) {
            if(response.success) {
                // Modalı kapat
                $('#yeni_not_ekle_santral').modal('hide');

                // Tabloyu güncelle
                var index = formData.not_id;
                var row = $('#arama_liste_detay_tablo tbody tr[data-index="' + index + '"]');

                // Not içeriğini, tarih ve saat ile birlikte güncelle
                row.find('.not-cell').html(
                    formData.noticerik + '<br><small class="text-muted">' + 
                    formData.santralnottarih + ' - ' + formData.santralnotsaat + 
                    '</small>'
                );

                alert('Not başarıyla kaydedildi.');
            } else {
                alert('Not kaydedilemedi: ' + response.message);
            }
        },
        error: function() {
            alert('Not kaydedilirken bir hata oluştu.');
        }
    });
});


function aramaListesiniGetir(url)
{
     var namesType = $.fn.dataTable.absoluteOrder( [
                     { value: null, position: 'bottom' }
                     ] );
                 $.fn.dataTable.moment('DD.MM.YYYY');
       $('#arama_liste_tablo').DataTable().destroy();
            
         $('#arama_liste_tablo').DataTable({
                       autoWidth: false,
                       responsive: true,
                       "processing": true,
                       "serverSide": true,
                       "deferRender": true, // DOM yükünü azaltır!
                        "ajax": {
                            "url": url,
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
                        
                                { data: 'arama_baslik'},
                               { data: 'personel'},
                           
                               { data: 'detaylar'},
                            
                     
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
function turkLiraFormat(num) {
    if (num === null || num === undefined) return "0,00";

    // 2 ondalık basamakla yuvarla
    num = Math.round(num * 100) / 100;

    return num.toLocaleString('tr-TR', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    });
}


$(document).on('click','a[name="kampanyaSablonSecim"]',function(e){
    e.preventDefault();

    var isValid = true;
    /*if($('#kampanyaGecerlilikTarihi').val()!='' && $('#hizmetUrunPaket').val()!='' && $('#kampanyaIndirim').val() != '' )
        isValid = true;
    var warningtext = '';
    if($('#hizmetUrunPaket').val()=='')
        warningtext += '-Kampanyayı duyurmak istediğiniz hizmet, ürün veya paketinizi seçmeniz gerekir.';
    if($('#kampanyaGecerlilikTarihi').val()=='')
        warningtext += '<br>-Kampanya geçerlilik tarihini belirtiniz gerekir.';
    if($('#kampanyaIndirim').val() == '')
         warningtext += '<br>-Uygulayacağınız indirim miktarını belirtiniz gerekir.';*/
 
    if(isValid)
    {
         $.ajax({
            url: '/isletmeyonetim/kampanyaIceriginiGoruntule',
            method: 'GET',
            dataType:'json',
            data: {salonId:$('input[name="sube"]').val(),kampanyaMetin:$(this).text(),kampanyaIndirim:$('#kampanyaIndirim').val(),gecerlilikTarihi:$('#kampanyaGecerlilikTarihi').val(),hizmetUrunPaket:$('#hizmetUrunPaket').val(),sablonId:$(this).attr('data-value')},
            beforeSend: function() {
                $('#preloader').show();
            },
            success: function(result) {
                 $('#preloader').hide();
                $('#kampanyaPrompt').empty();
                $('#kampanyaPrompt').append(result.promptStr);
                $('#calinacak_kayit').attr('src',result.calinacakMetin.trim());
                var audio = $("#kampanyaSesKaydiCal")[0];
                audio.load();
            },
             error: function(request, status, error) {
                $('#preloader').hide();
                $('#hata').html(request.responseText);
            }
        });
        $('html, body').animate({ scrollTop: $('#kampanyaPrompt').offset().top }, 'slow');
    }
    else{
         swal(
                            {
                                type: "warning",
                                title: "Uyarı",
                                html:  'Şablon seçmeden önce;<br><br>'+warningtext,
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                                timer:5000,
                            }
        );
    }

   
});
$('#katilimciTuru,#gelmeyenMusteri').change(function(e){
    let cinsiyet = '';

    if($('#katilimciTuru').val()=='kadinlar')
        cinsiyet = '0';
    if($('#katilimciTuru').val()=='erkekler')
        cinsiyet = '1';
    let currentFilter = $('#gelmeyenMusteri').val();
    $.ajax({
        url: '/isletmeyonetim/musteriportfoydropliste',
        method: 'POST',
        data: {
          page: 1,
          salonId:$('input[name="sube"]').val(),
          perPage: 100,
          filtre: currentFilter,
          cinsiyet : cinsiyet,
          search: '',
          _token: $('input[name="_token"]').val()
        },
        success: function (res) {
          console.log(res);
          $('#kampanya_katilimci_sayisi').empty();
          $('#kampanya_katilimci_sayisi').append(res.total);
        },
        
        error: function (xhr) {
          console.error("Error:", xhr.statusText);
        }
    });
});
$('#gorevTanimla').click(function(e){
    e.preventDefault();

    kampanyaolustur($('#kampanyaTuru').val());


});
$(document).on('click','button[name="satisDuzenle"]',function(e){
    var tds = $(this).closest('tr').children('td');
    e.preventDefault();
     $.ajax({
        url: '/isletmeyonetim/satisDetaylariveDuzenleme',
        method: 'GET',
        data: {
         
          sube:$('input[name="sube"]').val(),
          musteriId: $(this).attr('data-index-number'),
          adisyonId: $(this).attr('data-value'),
          
          _token: $('input[name="_token"]').val()
        },
        success: function (result) {
           $('input[name="adisyon_id"]').val(result.adisyonId);
           $('#tum_tahsilatlar_duzenleme').empty();
           $('#tum_tahsilatlar_duzenleme').append(result.kalemler);
           $('#tahsilat_listesi_duzenleme').empty();
           $('#tahsilat_listesi_duzenleme').append(result.tahsilatlar);
            var musteridata = result.musteribilgi; 
                var data={
                        id:musteridata.id,
                        text:musteridata.name
                };
                var option = new Option(data.text, data.id, false, false);
                $('.musteri_satis').append(option);
                $('.musteri_satis').val(data.id).trigger('change');
                tahsilatyenidenhesapla();
                adisyontoplamhesapla();
                $('#adisyon_odenen_tutar').empty();
                $('#adisyon_toplam_tutar').empty();
                $('#tahsil_edilecek_kalan_tutar').empty();
                $('#tahsil_edilecek_kalan_tutar').append(result.kalanTutar);
                $('#adisyon_odenen_tutar').append(result.odenenTutar);
                $('#adisyon_toplam_tutar').append(result.toplamTutar);
                $('#satisKalemleri').modal('show');
        },
        
        error: function (xhr) {
          console.error("Error:", xhr.statusText);
        }
    });    
  

});
$('#satis_listesi').on('submit',function(e){
    e.preventDefault();
     $('#satisKalemleri').modal('hide');
    if($('#satis_takibi_ekrani').length)
        applyFilters();
    if($('#adisyon_liste_musteri').length)
    {
         var namesType = $.fn.dataTable.absoluteOrder( [
                                 { value: null, position: 'bottom' }
                                 ] );
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


     
});
$(document).on('select2:select', '#randevuekle_musteri_id', function (e) {
    // Müşteri seçildiğinde paket kontrolü yap
    paketKontrolü($(this).val(), false);
});

function paketKontrolü(userId, onayVar) {
    if (!userId) return;
    
    $.ajax({
        type: "GET",
        url: '/isletmeyonetim/paketVarmiKontrolu',
        dataType: "json",
        data: {
            userId: userId, 
            paketRandevuOnayiVar: onayVar, 
            sube: $('input[name="sube"]').val()
        },
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        beforeSend: function() {
            $("#preloader").show();
        },
        success: function(result) {
            $("#preloader").hide();
            
            console.log("Paket kontrol sonucu:", result);
            
            if (!result.paketVarMi) {
                console.log("Müşteride paket/hizmet bulunmuyor");
                return;
            }
            
            if (result.paketRandevuOnayiGerekli) {
                showSoftPackageSelectionModal(result);
            } 
            else {
                console.log("Onay gerekmiyor, hizmetler ekleniyor");
                if (result.paketDetaylari && result.paketDetaylari.length > 0) {
                    const hizmetData = convertAllPackagesToServiceData(result.paketDetaylari);
                    if (hizmetData.length > 0) {
                        // ÖNCE formdaki mevcut hizmetleri temizle
                        const $hizmetSelect = $('#yenirandevuekleform .hizmet_secimi').first();
                        $hizmetSelect.val(null).trigger('change.select2');
                        
                        // SONRA yeni hizmetleri ekle
                        setTimeout(() => {
                            addServicesToForm(hizmetData, result, false);
                        }, 200);
                    }
                }
            }
        },
        error: function(request, status, error) {
            $("#preloader").hide();
            console.error("Paket kontrol hatası:", error);
            
            showErrorAlert('Paket kontrolü sırasında bir hata oluştu. Lütfen tekrar deneyin.');
        }
    });
}

function convertAllPackagesToServiceData(paketDetaylari) {
    const hizmetData = [];
    
    paketDetaylari.forEach(packageDetail => {
        if (packageDetail.icerik && packageDetail.icerik.length > 0) {
            packageDetail.icerik.forEach(hizmet => {
                hizmetData.push({
                    id: hizmet.id,
                    text: hizmet.text,
                    seans: hizmet.seans,
                    tur: packageDetail.type === 'paket' ? 'paket' : 'hizmet',
                    sure: hizmet.sure || 0,
                    paket_adi: packageDetail.type === 'paket' ? packageDetail.adi : null,
                    original_seans: hizmet.seans,
                    hizmet_id: hizmet.id,
                    paket_id: packageDetail.paket_id || null,
                    adisyon_hizmet_id: packageDetail.adisyon_hizmet_id || null,
                    adisyon_paket_id: packageDetail.adisyon_paket_id || null
                });
            });
        }
    });
    
    console.log('Tüm paketlerden dönüştürülen hizmet verisi:', hizmetData);
    return hizmetData;
}

// Soft tasarımlı paket seçim modal'ını göster
function showSoftPackageSelectionModal(result) {
    const packageCount = result.paketDetaylari?.length || 0;
    
    const modalHtml = `
        <div class="modal fade" id="softPaketSecimModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document" style="width: 40%;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-bottom: none;">
                        <div class="d-flex align-items-center w-100">
                            <div class="icon-container mr-3" style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 10px;">
                                <i class="fa fa-gift text-white" style="font-size: 24px;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title text-white mb-0" style="font-weight: 600;">Müşteri Paket/Hizmetleri</h5>
                                <p class="text-white-50 mb-0 small">${result.userName} için mevcut paket ve hizmetler</p>
                            </div>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                            <span aria-hidden="true" style="font-size: 28px;">&times;</span>
                        </button>
                    </div>
                    
                    <div class="modal-body py-4">
                        <div class="alert alert-soft-info mb-4" style="background-color: #f0f7ff; border: 1px solid #d1e3ff; border-left: 4px solid #4dabf7; border-radius: 8px;">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-info-circle mr-3" style="color: #4dabf7; font-size: 20px;"></i>
                                <div>
                                    <p class="mb-1" style="color: #2c3e50; font-weight: 500;">Müşteriye ait bekleyen paket/hizmetler bulundu.</p>
                                    <p class="mb-0 small" style="color: #6c757d;">Eklemek istediğiniz paket/hizmetleri seçiniz.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="section-title mb-3" style="color: #495057; font-weight: 600; padding-bottom: 8px; border-bottom: 2px solid #f1f3f5;">
                                <i class="fa fa-list-check mr-2"></i>Mevcut Paket/Hizmetler
                                <span class="badge badge-soft-primary ml-2" style="background-color: #e3f2fd; color: #1976d2;">${packageCount} adet</span>
                            </h6>
                            
                            <div class="table-container" style="border-radius: 10px; overflow: hidden; border: 1px solid #eef2f7;">
                                <div id="softPaketTableScroll" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-hover mb-0">
                                        <thead style="background-color: #f8fafc; position: sticky; top: 0; z-index: 10;">
                                            <tr>
                                                <th width="50" class="py-3" style="border-bottom: 2px solid #eef2f7;"></th>
                                                <th class="py-3" style="border-bottom: 2px solid #eef2f7; color: #64748b; font-weight: 600;">Paket/Hizmet</th>
                                                <th class="py-3" style="border-bottom: 2px solid #eef2f7; color: #64748b; font-weight: 600;">Kalan Seans</th>
                                                <th class="py-3" style="border-bottom: 2px solid #eef2f7; color: #64748b; font-weight: 600;">Tür</th>
                                                <th class="py-3" style="border-bottom: 2px solid #eef2f7; color: #64748b; font-weight: 600;">Süre</th>
                                            </tr>
                                        </thead>
                                        <tbody id="softPaketTableBody">
                                            ${generateSoftPackageRows(result.paketDetaylari)}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="table-footer mt-2 text-right">
                                <small class="text-muted">
                                    <i class="fa fa-info-circle mr-1"></i>
                                    ${packageCount} adet paket/hizmet listeleniyor
                                </small>
                            </div>
                        </div>
                        
                        
                    <div class="modal-footer py-3" style="background-color: #f9fafb; border-top: 1px solid #eef2f7;">
                        <button type="button" class="btn btn-light btn-lg px-4" data-dismiss="modal" style="border-radius: 8px; border: 1px solid #e5e7eb; color: #6b7280; font-weight: 500;">
                            <i class="fa fa-times mr-2"></i>Vazgeç
                        </button>
                        <button type="button" class="btn btn-primary btn-lg px-4" id="softAddSelectedPackages" style="border-radius: 8px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; font-weight: 500; min-width: 160px;">
                            <i class="fa fa-check mr-2"></i>Seçilenleri Ekle
                            <span class="badge badge-light ml-2" id="selectedCountBadge" style="background: rgba(255,255,255,0.3); color: white;">0</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modalHtml);
    
    $('#softPaketSecimModal').modal('show');
    
    adjustPackageTableScroll(packageCount);
    
    setupSoftPackageSelectionEvents(result);
}

// Soft tasarımlı paket satırlarını oluştur (checkbox'lar seçili olmadan)
function generateSoftPackageRows(paketDetaylari) {
    let rows = '';
    
    paketDetaylari.forEach((item, index) => {
        const isPackage = item.type === 'paket';
        const icon = isPackage ? '📦' : '✨';
        const typeClass = isPackage ? 'badge-soft-primary' : 'badge-soft-info';
        const typeText = isPackage ? 'Paket' : 'Tek Hizmet';
        
        let hizmetListesi = '';
        if (item.icerik && item.icerik.length > 0) {
            hizmetListesi = '<div class="small text-muted mt-1">';
            item.icerik.forEach(hizmet => {
                hizmetListesi += `<div>• ${hizmet.text} (${hizmet.seans} seans)</div>`;
            });
            hizmetListesi += '</div>';
        }
        
        rows += `
            <tr class="soft-package-row" data-index="${index}" style="cursor: pointer; transition: background-color 0.2s;">
                <td class="py-3" style="border-bottom: 1px solid #eef2f7;">
                    <div class="form-check" style="margin: 0;">
                        <input class="form-check-input soft-package-checkbox" 
                               type="checkbox" 
                               data-index="${index}"
                               id="softPackageCheck${index}"
                               style="cursor: pointer; width: 18px; height: 18px;">
                    </div>
                </td>
                <td class="py-3" style="border-bottom: 1px solid #eef2f7;">
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="mr-2" style="font-size: 18px;">${icon}</span>
                            <strong style="color: #334155;">${item.adi}</strong>
                        </div>
                        ${hizmetListesi}
                    </div>
                </td>
                <td class="py-3" style="border-bottom: 1px solid #eef2f7; color: #475569;">
                    <span class="badge ${typeClass}" style="color:#000;font-size: 12px; padding: 4px 8px;">
                        ${item.seans} seans
                    </span>
                </td>
                <td class="py-3" style="border-bottom: 1px solid #eef2f7; color: #475569;">
                    <span class="badge badge-soft-secondary" style="background-color: #f1f5f9; color: #475569;">
                        ${typeText}
                    </span>
                </td>
                <td class="py-3" style="border-bottom: 1px solid #eef2f7; color: #475569;">
                    ${item.sure ? item.sure + ' dk' : '-'}
                </td>
            </tr>
        `;
    });
    
    return rows;
}

// Paket tablosu scroll ayarını yap
function adjustPackageTableScroll(packageCount) {
    const scrollContainer = $('#softPaketTableScroll');
    const tableBody = $('#softPaketTableBody');
    
    if (packageCount > 5) {
        // 5'ten fazla paket varsa scroll aktif
        scrollContainer.css({
            'max-height': '300px',
            'overflow-y': 'auto'
        });
        
        // Başlık sticky olacak
        scrollContainer.find('thead').css({
            'position': 'sticky',
            'top': '0',
            'z-index': '10',
            'background-color': '#f8fafc'
        });
        
        // Scroll bar stilleri
        scrollContainer.css({
            'scrollbar-width': 'thin',
            'scrollbar-color': '#cbd5e1 #f1f5f9'
        });
        
        // Webkit için özel scrollbar
        scrollContainer.on('mouseenter', function() {
            $(this).css('overflow-y', 'auto');
        }).on('mouseleave', function() {
            $(this).css('overflow-y', 'hidden');
        });
        
    } else {
        // 5 veya daha az paket varsa scroll yok
        scrollContainer.css({
            'max-height': 'none',
            'overflow-y': 'visible'
        });
    }
}

// Soft paket seçim event'lerini kur
function setupSoftPackageSelectionEvents(result) {
    // Bireysel checkbox değişiklikleri
    $(document).on('change', '.soft-package-checkbox', function() {
        const row = $(this).closest('tr');
        if ($(this).prop('checked')) {
            row.css('background-color', '#f0f9ff');
            row.css('border-left', '3px solid #3b82f6');
        } else {
            row.css('background-color', '');
            row.css('border-left', '');
        }
        updateSoftSelectedPackagesSummary(result);
    });
    
    // Satıra tıklayınca da checkbox'ı değiştir
    $(document).on('click', '.package-row', function(e) {
        if (!$(e.target).is('input, label, a, button, .btn, .badge')) {
            const checkbox = $(this).find('.soft-package-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });
    
    // Seçilenleri ekle butonu
    $(document).on('click', '#softAddSelectedPackages', function() {
        const selectedPackages = getSoftSelectedPackages(result);
        console.log(selectedPackages);
        if (selectedPackages.length === 0) {
            showWarningAlert('Lütfen en az bir paket/hizmet seçin.');
            return;
        }
        
        // Butonu loading durumuna getir
        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.html('<i class="fa fa-spinner fa-spin mr-2"></i>Ekleniyor...').prop('disabled', true);
        
        // Modal'ı kapat
        $('#softPaketSecimModal').modal('hide');
        
        // 500ms sonra paketleri ekle (modal animasyonu için)
        setTimeout(() => {
            const hizmetData = convertSoftPackagesToServiceData(selectedPackages, result);
        
            addServicesToForm(hizmetData, result, true);
            
            // Butonu eski haline getir
            $btn.html(originalHtml).prop('disabled', false);
        }, 500);
    });
    
    // Modal kapanınca temizle
    $('#softPaketSecimModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
    
    // İlk yüklemede özeti güncelle
    updateSoftSelectedPackagesSummary(result);
}

// Soft seçili paketleri getir
function getSoftSelectedPackages(result) {
    const selectedPackages = [];
    
    $('.soft-package-checkbox:checked').each(function() {
        const index = $(this).data('index');
        if (result.paketDetaylari && result.paketDetaylari[index]) {
            selectedPackages.push(result.paketDetaylari[index]);
        }
    });
    
    return selectedPackages;
}

// Soft seçilen paketler özetini güncelle
function updateSoftSelectedPackagesSummary(result) {
    const selectedPackages = getSoftSelectedPackages(result);
    const summaryList = $('#softSelectedPackagesList');
    const totalServices = $('#softTotalSelectedServices');
    const totalSessions = $('#softTotalSelectedSessions');
    const selectedCountBadge = $('#selectedCountBadge');
    const selectedCountBadgeHeader = $('#selectedCountBadgeHeader');
    const summaryFooterText = $('#summaryFooterText');
    
    // Seçili sayısını güncelle
    selectedCountBadge.text(selectedPackages.length);
    selectedCountBadgeHeader.text(`${selectedPackages.length} seçili`);
    
    if (selectedPackages.length === 0) {
        summaryList.html(`
            <div class="empty-state text-center py-4">
                <i class="fa fa-inbox mb-3" style="font-size: 48px; color: #d1d5db;"></i>
                <p class="text-muted mb-0" style="color: #9ca3af;">Henüz seçim yapılmadı</p>
            </div>
        `);
        totalServices.text('0');
        totalSessions.text('0');
        summaryFooterText.html('<i class="fa fa-mouse-pointer mr-1"></i> Seçim yapmak için yukarıdaki listeden seçin');
        return;
    }
    
    let listHtml = '<div class="selected-items-container">';
    let totalSeans = 0;
    
    selectedPackages.forEach((paket, index) => {
        totalSeans += parseInt(paket.seans) || 0;
        const isPackage = paket.type === 'paket';
        
        listHtml += `
            <div class="selected-item mb-2 p-3" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; border-left: 3px solid ${isPackage ? '#f59e0b' : '#3b82f6'};">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            ${isPackage ? 
                                '<div style="width: 32px; height: 32px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 6px; display: flex; align-items: center; justify-content: center;"><i class="fa fa-box" style="color: #d97706; font-size: 14px;"></i></div>' : 
                                '<div style="width: 32px; height: 32px; background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-radius: 6px; display: flex; align-items: center; justify-content: center;"><i class="fa fa-cog" style="color: #1d4ed8; font-size: 14px;"></i></div>'
                            }
                        </div>
                        <div>
                            <div class="fw-bold mb-1" style="color: #374151; font-size: 14px;">${paket.adi}</div>
                            <div class="small" style="color: #6b7280;">${paket.tur}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-soft-secondary mr-2 px-2 py-1" style="background-color: #f3f4f6; color: #4b5563; font-size: 12px;">
                            <i class="fa fa-calendar-day mr-1"></i>${paket.seans}
                        </span>
                        <span class="badge badge-soft-success px-2 py-1" style="background-color: #d1fae5; color: #065f46; font-size: 12px;">
                            ${(paket.sure)} 
                        </span>
                    </div>
                </div>
            </div>
        `;
    });
    
    listHtml += '</div>';
    summaryList.html(listHtml);
    totalServices.text(selectedPackages.length);
    totalSessions.text(totalSeans);
    
    // Footer metnini güncelle
    summaryFooterText.html(`
        <i class="fa fa-check-circle mr-1" style="color: #10b981;"></i>
        ${selectedPackages.length} hizmet, ${totalSeans} seans seçildi
    `);
    
    // Scroll container'ı güncelle
    const scrollContainer = $('#softSelectedPackagesScroll');
    if (selectedPackages.length > 3) {
        scrollContainer.css({
            'max-height': '200px',
            'overflow-y': 'auto'
        });
        
        // Smooth scroll özelliği
        scrollContainer.scrollTop(scrollContainer[0].scrollHeight);
    } else {
        scrollContainer.css({
            'max-height': 'none',
            'overflow-y': 'visible'
        });
    }
}

// Soft paketleri hizmet verisine dönüştür
function convertSoftPackagesToServiceData(selectedPackages, result) {
    const hizmetData = [];
    
    selectedPackages.forEach(packageDetail => {
        if (packageDetail.icerik && packageDetail.icerik.length > 0) {
            const isPaket = packageDetail.type === 'paket';
            const hizmetSayisi = packageDetail.icerik.length;
            let toplamSure = parseInt(packageDetail.sure) || 0;
            
            // Toplam süreyi hizmetlere dağıt
            packageDetail.icerik.forEach((hizmet, index) => {
                let hizmetSuresi = 0;
                
                if (isPaket) {
                    // Eğer tek hizmet varsa tüm süreyi ona ver
                    if (hizmetSayisi === 1) {
                        hizmetSuresi = toplamSure;
                    } 
                    // İlk hizmete tüm süreyi ver, diğerlerine 0
                    else if (index === 0) {
                        hizmetSuresi = toplamSure;
                    } 
                   
                    
                } else {
                    // Tek hizmetse kendi süresini kullan
                    hizmetSuresi = hizmet.sure || 0;
                }
                
                hizmetData.push({
                    id: hizmet.id,
                    text: hizmet.text,
                    seans: hizmet.seans,
                    tur: isPaket ? 'paket' : 'hizmet',
                    sure: hizmetSuresi,
                    paket_adi: isPaket ? packageDetail.adi : null,
                    original_seans: hizmet.seans,
                    hizmet_id: hizmet.id,
                    paket_id: packageDetail.paket_id || null,
                    adisyon_hizmet_id: packageDetail.adisyon_hizmet_id || null,
                    adisyon_paket_id: packageDetail.adisyon_paket_id || null
                });
            });
        }
    });
    
    console.log('Dönüştürülen hizmet verisi:', hizmetData);
    return hizmetData;
}
// Soft geçici ID oluştur
function generateSoftTempId(name) {
    return 'temp_' + name.replace(/\s+/g, '_').toLowerCase() + '_' + Date.now();
}

// Hizmetleri form'a ekle
function addServicesToForm(hizmetData, result, showSuccessMessage = false) {
    if (!hizmetData || hizmetData.length === 0) {
        console.log("Eklenebilecek hizmet yok");
        return;
    }
    
    console.log(`${hizmetData.length} adet hizmet eklenecek:`, hizmetData);
    
    const $hizmetSelect = $('#yenirandevuekleform .hizmet_secimi').first();
    const index = $hizmetSelect.data('index') || 0;
    
    const addedServices = [];
    const hizmetIdsToAdd = hizmetData.map(item => item.id);
    
    // ÖNCE: Mevcut seçimleri temizle
    $hizmetSelect.val(null).trigger('change');
    
    // SONRA: Hizmetleri ekle
    hizmetData.forEach(function(item) {
        if (item && item.id) {
            const optionText = item.text;
            
            // Option zaten var mı kontrol et
            let $existingOption = $hizmetSelect.find('option[value="' + item.id + '"]');
            
            if ($existingOption.length === 0) {
                // Yeni option oluştur
                const option = new Option(optionText, item.id, true, true);
                
                $(option).data('extra', {
                    seans: item.seans,
                    tur: item.tur,
                    paket_adi: item.paket_adi,
                    sure: item.sure,
                    original_seans: item.original_seans,
                    hizmet_id: item.hizmet_id,
                    paket_id: item.paket_id,
                    adisyon_hizmet_id: item.adisyon_hizmet_id,
                    adisyon_paket_id: item.adisyon_paket_id
                });
                
                $hizmetSelect.append(option);
            } else {
                // Var olan option'ı güncelle
                $existingOption.text(optionText);
                $existingOption.data('extra', {
                    seans: item.seans,
                    tur: item.tur,
                    paket_adi: item.paket_adi,
                    sure: item.sure,
                    original_seans: item.original_seans,
                    hizmet_id: item.hizmet_id,
                    paket_id: item.paket_id,
                    adisyon_hizmet_id: item.adisyon_hizmet_id,
                    adisyon_paket_id: item.adisyon_paket_id
                });
                $existingOption.prop('selected', true);
            }
            
            // Cache'i güncelle
            hizmetDataCache[item.id] = {
                id: item.id,
                text: item.text,
                sure: item.sure || 0,
                kategori: item.tur === 'paket' ? 'Paket Hizmeti' : 'Tek Hizmet',
                renk: item.tur === 'paket' ? '#f59e0b' : '#3b82f6',
                seans: item.seans,
                paket_adi: item.paket_adi
            };
            
            addedServices.push({
                id: item.id,
                text: item.text,
                seans: item.seans,
                sure: item.sure,
                tur: item.tur,
                paket_adi: item.paket_adi
            });
        }
    });
    
    // Select2'yi güncelle
    if ($hizmetSelect.hasClass('select2-hidden-accessible')) {
        setTimeout(() => {
            const selectedIds = addedServices.map(s => s.id);
            console.log("Eklenecek hizmet ID'leri:", selectedIds);
            
            $hizmetSelect.val(selectedIds).trigger('change.select2');
            
            // Hizmet detaylarını güncelle
            updateHizmetDetaylari(index);
            updateRandevuOzeti();
            
            if (showSuccessMessage) {
                showSuccessAlert(`${addedServices.length} adet hizmet forma eklendi.`);
            }
        }, 100);
    }
}

// Soft başarı bildirimi göster
function showSoftSuccessNotification(serviceCount) {
    const toast = `
        <div class="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="toast show" style="min-width: 350px; border-radius: 10px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <div class="toast-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 10px 10px 0 0; border: none; padding: 12px 16px;">
                    <i class="fa fa-check-circle text-white mr-2"></i>
                    <strong class="mr-auto text-white">Başarılı</strong>
                    <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" style="opacity: 0.8;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="toast-body" style="padding: 20px;">
                    <div class="d-flex align-items-center">
                        <div class="icon-container mr-3" style="width: 48px; height: 48px; background: #d1fae5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-box-open" style="color: #10b981; font-size: 24px;"></i>
                        </div>
                        <div>
                            <p class="mb-1" style="color: #1f2937; font-weight: 500;">${serviceCount} adet paket/hizmet eklendi</p>
                            <p class="mb-0 small" style="color: #6b7280;">Hizmetler formda seçili durumda</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(toast);
    
    // 3 saniye sonra toast'ı kaldır
    setTimeout(() => {
        $('.toast-container').remove();
    }, 3000);
}

// Hata mesajı göster
function showErrorAlert(message) {
    const errorHtml = `
        <div class="text-center py-4">
            <div class="error-icon mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #ef4444 0%, #f87171 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <i class="fa fa-exclamation-triangle text-white" style="font-size: 28px;"></i>
            </div>
            <h5 class="mb-2" style="color: #991b1b; font-weight: 600;">Hata!</h5>
            <p class="text-muted mb-3">${message}</p>
        </div>
    `;
    
    Swal.fire({
        html: errorHtml,
        icon: 'error',
        confirmButtonText: 'Tamam',
        confirmButtonColor: '#ef4444',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-danger btn-lg px-4 py-2',
            popup: 'rounded-lg'
        }
    });
}

// Uyarı mesajı göster
function showWarningAlert(message) {
    const warningHtml = `
        <div class="text-center py-4">
            <div class="warning-icon mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <i class="fa fa-exclamation-circle text-white" style="font-size: 28px;"></i>
            </div>
            <h5 class="mb-2" style="color: #92400e; font-weight: 600;">Uyarı</h5>
            <p class="text-muted mb-3">${message}</p>
        </div>
    `;
    
    Swal.fire({
        html: warningHtml,
        icon: 'warning',
        confirmButtonText: 'Tamam',
        confirmButtonColor: '#f59e0b',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-warning btn-lg px-4 py-2',
            popup: 'rounded-lg'
        }
    });
}

// Modal kapatıldığında formu sıfırla
$('#modal-view-event-add').on('hidden.bs.modal', function() {
    // Hizmet seçimlerini temizle
    $('.hizmet_secimi').each(function() {
        $(this).val(null).trigger('change');
    });
});

// Modal açıldığında formu temizle
$(document).on('show.bs.modal', '#modal-view-event-add', function() {
    // Tüm hizmet seçimlerini temizle
    $('.hizmet_secimi').each(function() {
        $(this).val(null);
        
        // Select2 kullanılıyorsa
        if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).trigger('change.select2');
        } else {
            $(this).trigger('change');
        }
    });
    
    // Diğer form alanlarını temizle
    $('textarea[name="personel_notu"]').val('');
});

$(document).on('hidden.bs.modal', '#softPaketSecimModal', function() {
    $(this).remove();
    
    // Seçim değişkenlerini temizle
    if (typeof window.selectedPackageIds !== 'undefined') {
        window.selectedPackageIds = [];
    }
});
