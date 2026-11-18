<?php

namespace App\Services\Gorsel;

/**
 * Gorsel analiz saglayicisi (degistirilebilir). WaGorselDanisman yalniz bu arayuzu kullanir;
 * baska bir model/servis bu arayuzu uygulayip config('services.wa_asistan.gorsel_saglayici') ile secilir.
 */
interface GorselSaglayici
{
    /**
     * @param string $b64    gorselin base64 verisi
     * @param string $mime   image/jpeg | image/png | image/webp
     * @param string $talimat sistem talimati (sektor semasina gore kurulur)
     * @param array  $sema   beklenen JSON semasi (json_schema)
     * @param string $metin  musterinin fotografla yazdigi aciklama (bos olabilir)
     * @return array ['json' => ?array, 'kullanim' => ['girdi','cikti'], 'maliyet_usd' => float, 'hata' => ?string]
     */
    public function analiz(string $b64, string $mime, string $talimat, array $sema, string $metin = ''): array;
}
