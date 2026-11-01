<?php

return [
    /*
     | Eski web paneli → yeni v2 paneli (Randevumcepte-New-Laravel) gecis koprusu.
     |
     | panel_url        : v2 panelinin kok adresi.
     | takvim_salonlari : "Randevu Takvimi" menusu v2 takvimini acacak salonlar
     |                    (pilot). CSV "20,236" ya da "*" (tum salonlar). Bos = kapali.
     |
     | v2 tarafinda yazma ayrica RANDEVU_V2_YAZMA_SALONLARI / _AKTIF ile acilir.
     */
    'panel_url'        => rtrim(env('V2_PANEL_URL', 'https://panel.randevumcepte.com.tr'), '/'),
    'takvim_salonlari' => env('V2_TAKVIM_SALONLARI', ''),
];
