<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Guzellik salonu varsayilan hizmet sablonu
    |--------------------------------------------------------------------------
    |
    | Yeni bir guzellik salonu kayit oldugunda, bu ID'li "sablon salon"un
    | salon_sunulan_hizmetler kayitlari yeni salona kopyalanir
    | (ApiController::varsayilanHizmetleriKopyala). Kopyalama yalnizca kaynak
    | ve hedef salonun salon_turu_id'si AYNI ise calisir; boylece guzellik
    | sablonu sadece guzellik salonlarina dagilir.
    |
    | Sablon salonun hizmet listesini normal isletme panelinden duzenleyerek
    | varsayilanlari guncelleyebilirsiniz (FormVarsayilanYay ile ayni mantik).
    | 0 birakilirsa hicbir kopyalama yapilmaz (ozellik pasif).
    |
    */
    'guzellik_sablon_salon_id' => env('GUZELLIK_SABLON_SALON_ID', 355),

];
