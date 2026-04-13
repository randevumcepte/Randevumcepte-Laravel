$('#randevuModu').on('click', function(){
      $('#randevuModuRandevuTarihi').datepicker({
            language: "tr",
            autoClose: true,
            dateFormat: "yyyy-mm-dd",
            minDate : new Date();
        });
    var acik = $(this).is(':checked');
    var confirmBtnText = '';
    var description = '';
    if(acik)
    {
        description = 'Bu işlem randevu modunu ve santralde randevu alma menüsünü aktif edecektir!.';
        description += '<br><br><div class="form-group"> '+
        '<i class="fa fa-calendar" style=" position: absolute;top: 30px; right: 20px; font-size: 13px; z-index: 0;"></i>'+
        '<input style="max-width:150px" type="text" class="form-control date-picker" id="randevuModuRandevuTarihi" placeholder="Randevu Tarihi"></div>';
        confirmBtnText = 'Aktif Et';
    }
    else
    {
         description = 'Bu işlem randevu modunu ve santralde randevu alma menüsünü pasif edecektir!.';
        confirmBtnText = 'Pasif Et';
    }
    swal({
        title: "Emin misiniz?",
        html: description,
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#00bc8c',
        confirmButtonText: confirmBtnText,
        cancelButtonText: "Vazgeç",
        focusConfirm: false,
        
    }).then(function(result) {
        if ( result.dismiss === 'cancel') {
            // Vazgeç basıldı veya validation hatası → hiçbir işlem yapma
            return;
        }
        else{

            $.ajax({
                type:'POST',
                url:'/isletmeyonetim/randevuModuAcKapa',
                dataType:"text",
                data:{
                    acikKapali: acik ? 1 : 0,
                    salonId: $('input[name="sube"]').val(),
                    randevuTarihi:$('#randevuModuRandevuTarihi').val()

                },
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function(result){
                    $('#bugunNobetteyim').toggle(acik);

                    $('.randevuMenusu').each(function(i){
                        if(acik)
                            $(this).attr('style','display:block');
                        else
                            $(this).attr('style','display:none');
                    });
                },
                error: function(request){
                    document.getElementById('hata').innerHTML=request.responseText;
                }
            });
        }
       

    });

  
});