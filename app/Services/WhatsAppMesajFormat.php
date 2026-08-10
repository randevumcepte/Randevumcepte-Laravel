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
     * Müşteri WA mesajının altına "Uygulamamızı İndirin" daveti + link ekler.
     * SADECE müşterinin AKTİF push token'ı YOKSA (=uygulaması yok) eklenir.
     * Link: salonun uygulamalar_kisa_link'i, yoksa cihaz-duyarlı /indir/{salon}.
     * Uygulaması olana / hata / eksik veri durumunda mesaj AYNEN döner.
     * Tüm müşteri randevu mesajlarında (oluşturma/hatırlatma/güncelleme/iptal)
     * tek noktadan kullanılsın diye burada.
     */
    public static function uygulamaDavetiEk($mesaj, $salon, $userId)
    {
        try {
            if (!$salon || !$userId) return $mesaj;
            $appVar = \App\BildirimKimlikleri::where('user_id', $userId)
                ->where('aktif', true)->forBrand($salon->id)->exists();
            if ($appVar) return $mesaj; // uygulaması var -> ekleme yok
            $link = trim((string) ($salon->uygulamalar_kisa_link ?? ''));
            if ($link === '') $link = url('/indir/' . $salon->id);
            if ($link === '') return $mesaj;
            return $mesaj . "\n\n📲 *Uygulamamızı İndirin!*\n"
                . "Randevularınızı takip edin, otomatik hatırlatma alın ve size özel "
                . "fırsatlardan ilk siz haberdar olun 👉 " . $link;
        } catch (\Throwable $e) {
            return $mesaj;
        }
    }

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
