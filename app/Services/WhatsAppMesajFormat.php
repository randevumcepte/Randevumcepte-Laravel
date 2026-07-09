<?php

namespace App\Services;

/**
 * WhatsApp icin zenginlestirilmis (emoji + markdown + konum/tel) mesaj sablonlari.
 * Ayni format panel (StoreAdminController), mobile (ApiController) ve cron
 * (RandevuSMSHatirlatma) tarafindan kullanilir; yer yer duplike etmek yerine
 * bu tek noktadan geliyor.
 *
 * SMS'e giden kisa metin ayni degildir — cagri yerinde ayrica olusturulur;
 * bu class SADECE WA icin uzun/formatli metni uretir.
 */
class WhatsAppMesajFormat
{
    /**
     * Randevu olusturuldu bildirimi (WA).
     *
     * @param \App\Salonlar|object $salon salon_adi + konum_linki + telefon_1 alanlari kullanilir
     * @param string $musteriAdi
     * @param string $tarihStr d.m.Y formatinda
     * @param string $saatStr H:i formatinda
     * @param string $hizmetlerStr virgullu hizmet listesi ("solaryum, cilt bakimi")
     */
    public static function randevuOlusturuldu($salon, $musteriAdi, $tarihStr, $saatStr, $hizmetlerStr = '')
    {
        $mesaj  = "🌸 Merhaba, *" . $musteriAdi . "*,\n\n";
        $mesaj .= ($salon->salon_adi ?? '') . " için randevunuz başarıyla oluşturulmuştur. ✨\n\n";
        $mesaj .= "📅 *Tarih:* " . $tarihStr . "\n";
        $mesaj .= "🕒 *Saat:* " . $saatStr . "\n";
        if (!empty($hizmetlerStr)) {
            $mesaj .= "✂️ *Hizmetler:* " . $hizmetlerStr . "\n";
        }
        $mesaj .= "\nRandevunuzun sorunsuz geçmesi için lütfen belirtilen saatten birkaç dakika önce hazır olunuz. 💐\n\n";
        if (!empty($salon->konum_linki)) {
            $mesaj .= "📍 *Konum:* " . $salon->konum_linki . "\n";
        }
        if (!empty($salon->telefon_1)) {
            $tel = ltrim((string) $salon->telefon_1);
            if ($tel !== '' && ctype_digit($tel[0]) && $tel[0] !== '0') {
                $tel = '0' . $tel;
            }
            $mesaj .= "📞 *İletişim:* " . $tel . "\n";
        }
        $mesaj .= "\nRandevunuzu değiştirmek ya da iptal etmek isterseniz bu WhatsApp hattından ya da telefon numaramızdan bize ulaşabilirsiniz.\n\n";
        $mesaj .= "Sizi ağırlamayı sabırsızlıkla bekliyoruz. 💖";
        return $mesaj;
    }
}
