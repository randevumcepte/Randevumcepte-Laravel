<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Guzellik salonu varsayilan hizmet sablonu
    |--------------------------------------------------------------------------
    |
    | Yeni bir guzellik salonu kayit oldugunda, "sablon salon"un
    | salon_sunulan_hizmetler kayitlari yeni salona kopyalanir
    | (ApiController::varsayilanHizmetleriKopyala). Kopyalama yalnizca kaynak
    | ve hedef salonun salon_turu_id'si AYNI ise calisir.
    |
    | Sablon salon `hizmetler:guzellik-sablon-kur` komutuyla olusturulur;
    | musteri tarafinda gizli (askiya_alindi=1, demo_hesabi=1), sadece
    | varsayilan hizmet kaynagi olarak kullanilir. Hicbir gercek salona dokunmaz.
    |
    | Cozumleme sirasi: once guzellik_sablon_salon_id (>0 ise), yoksa
    | guzellik_sablon_salon_adi ile salon adindan id bulunur. Boylece prod'da
    | ID elle girmeye gerek kalmaz — komut salonu sabit adla olusturur.
    |
    */
    'guzellik_sablon_salon_id'  => env('GUZELLIK_SABLON_SALON_ID', 0),

    'guzellik_sablon_salon_adi' => 'SABLON - Guzellik Varsayilan Hizmetler (kullanmayin)',

];
