<?php

namespace App\Services;

/**
 * Uygulama ici yasal metinler (2026-11-23): Kullanim Kosullari, Gizlilik Politikasi, KVKK Aydinlatma Metni.
 * Her isletme icin kendi adi/adresi/telefonuyla otomatik uretilir; yeni acilan (satilan) her uygulamada
 * ayrica bir sey yapmadan gelir. Veri sorumlusu isletmedir; Webfirmam Ltd. Sti. teknik hizmet saglayicidir.
 */
class IsletmeYasalMetin
{
    const GELISTIRICI = 'Webfirmam İnternet Hizmetleri ve Reklamcılık Sanayi Ticaret Limited Şirketi';
    const GUNCELLEME = '23.11.2026';

    public static function isletme($salonId)
    {
        $s = \App\Salonlar::where('id', $salonId)->first();
        if (!$s) return null;
        $tel = '';
        foreach (['telefon_1', 'telefon_2', 'yetkili_telefon'] as $k) {
            if (!empty($s->{$k})) { $tel = (string) $s->{$k}; break; }
        }
        $logo = (string) ($s->logo ?? '');
        return [
            'ad' => trim((string) $s->salon_adi) ?: 'İşletme',
            'adres' => trim((string) ($s->adres ?? '')),
            'telefon' => $tel,
            'logo' => $logo !== '' && $logo !== 'null' ? (preg_match('#^https?://#', $logo) ? $logo : 'https://app.randevumcepte.com.tr/' . ltrim($logo, '/')) : '',
        ];
    }

    /** [{kod, baslik, guncelleme, bolumler:[{baslik, metin}]}] */
    public static function metinler($salonId)
    {
        $i = self::isletme($salonId);
        if (!$i) return [];
        $ad = $i['ad'];
        $iletisim = $ad . ($i['adres'] ? ', ' . $i['adres'] : '') . ($i['telefon'] ? ' · Tel: ' . $i['telefon'] : '');
        $gel = self::GELISTIRICI;

        $kullanim = [
            ['Taraflar ve kapsam', "Bu koşullar, {$ad} mobil uygulamasını kullanan herkes için geçerlidir. Uygulama {$ad} adına randevu, müşteri ve işletme süreçlerinin yönetimi için sunulur; teknik altyapısı {$gel} tarafından sağlanır. Uygulamayı kullanarak bu koşulları kabul etmiş sayılırsınız."],
            ['Hesap ve güvenlik', "Hesabınıza ait kullanıcı adı ve şifrenin gizliliğinden siz sorumlusunuz. Hesabınızın izinsiz kullanıldığını düşünüyorsanız şifrenizi hemen değiştirin ve {$ad} ile iletişime geçin. Profil > Cihazlarım ekranından hesabınızın açık olduğu cihazları görüp kapatabilirsiniz."],
            ['Kullanım kuralları', "Uygulama yalnızca hukuka uygun amaçlarla kullanılabilir. Başkası adına randevu almak için o kişinin onayı gerekir. Sisteme zarar verecek, başkalarının verilerine yetkisiz erişmeye çalışacak ya da hizmeti kötüye kullanacak davranışlar yasaktır; bu durumda hesap askıya alınabilir."],
            ['Randevular', "Randevu saatleri, hizmetler ve fiyatlar {$ad} tarafından belirlenir ve önceden haber verilerek değiştirilebilir. Randevu iptal ve değişiklik kuralları için doğrudan {$ad} ile iletişime geçebilirsiniz."],
            ['Personel kullanımı', "İşletme çalışanları uygulamayı yalnızca kendilerine tanınan yetkiler ölçüsünde ve işle ilgili amaçlarla kullanabilir. Müşteri bilgileri işletme dışına aktarılamaz, kişisel amaçla kullanılamaz. Yapılan işlemler kayıt altına alınır."],
            ['Bildirimler', "Uygulama; randevu, hatırlatma ve işletme duyuruları için bildirim gönderebilir. Bildirim izinlerini cihaz ayarlarından, sesli/sessiz tercihlerinizi Profil > Bildirimlerim ekranından değiştirebilirsiniz."],
            ['Sorumluluğun sınırı', "Uygulama \"olduğu gibi\" sunulur. Bakım, güncelleme veya internet bağlantısı kaynaklı kısa süreli kesintiler yaşanabilir. Kullanıcı hatasından ya da cihaz güvenliğinin sağlanmamasından doğan zararlardan {$ad} ve teknik hizmet sağlayıcı sorumlu tutulamaz."],
            ['Değişiklikler', "Bu koşullar gerektiğinde güncellenebilir. Güncel metin her zaman uygulama içinde Profil > Uygulama Hakkında bölümünde yer alır."],
            ['İletişim', $iletisim],
        ];

        $gizlilik = [
            ['Amaç', "{$ad}, uygulamayı kullananların gizliliğine önem verir. Bu politika hangi bilgilerin toplandığını, nasıl kullanıldığını ve nasıl korunduğunu açıklar."],
            ['Toplanan bilgiler', "Ad soyad, telefon numarası, e-posta (isteğe bağlı), doğum tarihi (isteğe bağlı), randevu ve işlem geçmişi, profil fotoğrafı (yüklenirse), cihaz ve bildirim kimliği, uygulama kullanım kayıtları."],
            ['Kullanım amacı', "Bilgiler; randevu oluşturma ve hatırlatma, iletişim, hizmet geçmişinin takibi, işletme içi müşteri ve personel yönetimi ile hizmet kalitesinin ölçülmesi amacıyla kullanılır. Açık izniniz olmadan pazarlama iletisi gönderilmez."],
            ['Paylaşım', "Bilgileriniz satılmaz ve kiralanmaz. Yalnızca hizmetin sunulması için gerekli teknik hizmet sağlayıcılarla (sunucu barındırma, SMS / WhatsApp / bildirim iletimi) ve yasal zorunluluk hâlinde yetkili kurumlarla paylaşılır."],
            ['Güvenlik', "Uygulama ile sunucu arasındaki tüm iletişim şifreli (HTTPS) yapılır. Şifreler geri döndürülemeyen biçimde saklanır. Personel erişimi yetki ile sınırlandırılır ve işlemler kayıt altına alınır."],
            ['Saklama süresi', "Veriler, işlenme amacının gerektirdiği süre ve yasal saklama süreleri boyunca tutulur; süre dolduğunda silinir, yok edilir veya anonim hâle getirilir."],
            ['Çerezler ve cihazda saklama', "Mobil uygulamada oturumun açık kalması için cihazda yerel saklama kullanılır. Bu bilgiler cihazınızdan başka bir yere aktarılmaz."],
            ['Hesap silme', "Hesabınızın ve ilişkili verilerin silinmesini {$ad} üzerinden talep edebilirsiniz. Yasal olarak saklanması zorunlu kayıtlar (fatura, işlem kaydı) süresi dolana kadar saklanır."],
            ['İletişim', $iletisim],
        ];

        $kvkk = [
            ['Veri sorumlusu', "6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) kapsamında veri sorumlusu {$ad}'dir. Uygulamanın teknik altyapısını sağlayan {$gel} veri işleyen sıfatıyla hareket eder."],
            ['İşlenen kişisel veriler', "Kimlik (ad soyad, doğum tarihi), iletişim (telefon, e-posta, adres), müşteri işlem (randevu, satış, tahsilat, paket ve seans bilgileri), görsel (profil ve işlem fotoğrafları), işlem güvenliği (cihaz kimliği, IP, oturum kayıtları); çalışanlar için ek olarak çalışma saati, izin ve performans bilgileri."],
            ['İşleme amaçları', "Randevu ve hizmet süreçlerinin yürütülmesi, müşteri ilişkilerinin yönetimi, hatırlatma ve bilgilendirme yapılması, finans ve muhasebe işlemleri, çalışan süreçlerinin (mesai, izin, hakediş) yürütülmesi, bilgi güvenliğinin sağlanması ve yasal yükümlülüklerin yerine getirilmesi."],
            ['Hukuki sebepler', "KVKK md. 5/2 kapsamında sözleşmenin kurulması ve ifası, hukuki yükümlülüğün yerine getirilmesi, bir hakkın tesisi ve korunması ile veri sorumlusunun meşru menfaati; pazarlama iletileri ve fotoğraf paylaşımı gibi durumlarda açık rıza."],
            ['Aktarım', "Kişisel veriler; hizmetin sunulması için teknik hizmet sağlayıcılara (sunucu, SMS, WhatsApp ve bildirim altyapısı), yasal zorunluluk hâlinde yetkili kamu kurum ve kuruluşlarına KVKK md. 8 ve 9'a uygun olarak aktarılabilir."],
            ['Toplama yöntemi', "Veriler uygulama, web paneli, telefon, WhatsApp ve işletmede yüz yüze iletişim yoluyla, elektronik ortamda otomatik ya da kısmen otomatik yöntemlerle toplanır."],
            ['Haklarınız (KVKK md. 11)', "Kişisel verinizin işlenip işlenmediğini öğrenme, işlenmişse bilgi talep etme, işleme amacını ve amacına uygun kullanılıp kullanılmadığını öğrenme, aktarıldığı üçüncü kişileri bilme, eksik veya yanlış işlenmişse düzeltilmesini, silinmesini veya yok edilmesini isteme, bu işlemlerin aktarılan kişilere bildirilmesini isteme, münhasıran otomatik sistemlerle analiz sonucu aleyhinize bir sonuç çıkmasına itiraz etme ve kanuna aykırı işleme nedeniyle zarara uğramanız hâlinde zararın giderilmesini talep etme haklarına sahipsiniz."],
            ['Başvuru', "Haklarınıza ilişkin taleplerinizi {$ad}'e yazılı olarak iletebilirsiniz. Başvurular en geç 30 gün içinde ücretsiz olarak sonuçlandırılır. İletişim: {$iletisim}"],
        ];

        $f = function ($kod, $baslik, $liste) {
            return ['kod' => $kod, 'baslik' => $baslik, 'guncelleme' => self::GUNCELLEME,
                'bolumler' => array_map(function ($b) { return ['baslik' => $b[0], 'metin' => $b[1]]; }, $liste)];
        };
        return [
            $f('kullanim', 'Kullanım Koşulları', $kullanim),
            $f('gizlilik', 'Gizlilik Politikası', $gizlilik),
            $f('kvkk', 'KVKK Aydınlatma Metni', $kvkk),
        ];
    }
}
