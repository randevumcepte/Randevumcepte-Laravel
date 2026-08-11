<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $markaAdi }} — Gizlilik Politikası ve Güvenlik</title>
    <meta name="description" content="{{ $markaAdi }} uygulaması gizlilik politikası ve güvenlik ilkeleri.">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.7;
            color: #1f2937;
            background: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 860px;
            margin: 0 auto;
            padding: 40px 24px 80px;
        }
        .header {
            background: linear-gradient(135deg, #6C5CE7, #A29BFE);
            color: #fff;
            padding: 32px 24px;
            border-radius: 16px;
            margin-bottom: 32px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 8px;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            background: #fff;
            border-radius: 16px;
            padding: 32px 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        h2 {
            font-size: 18px;
            color: #6C5CE7;
            margin: 28px 0 10px;
            font-weight: 700;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 8px;
        }
        h2:first-child { margin-top: 0; }
        p { margin: 10px 0; font-size: 14.5px; }
        ul { padding-left: 22px; }
        li { margin: 6px 0; font-size: 14.5px; }
        .brand-tag {
            display: inline-block;
            background: #EEF2FF;
            color: #4338CA;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            margin-right: 6px;
        }
        .contact-box {
            background: #F3F4F6;
            border-left: 4px solid #6C5CE7;
            padding: 16px 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .contact-box a { color: #6C5CE7; text-decoration: none; font-weight: 600; }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            margin-top: 24px;
        }
        @media (max-width: 600px) {
            .container { padding: 20px 14px 60px; }
            .content { padding: 22px 18px; }
            .header { padding: 24px 16px; }
            .header h1 { font-size: 20px; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>{{ $markaAdi }}</h1>
        <p>Gizlilik Politikası ve Güvenlik</p>
    </div>

    <div class="content">

        <p><span class="brand-tag">{{ $markaAdi }}</span> Bu politika, {{ $markaAdi }} mobil uygulamasının ve ilişkili web hizmetlerinin kullanımı sırasında kullanıcıların gizliliğini korumaya yönelik ilkeleri kapsar. Uygulama, <strong>Webfirmam İnternet Hizmetleri ve Reklamcılık Sanayi Ticaret Limited Şirketi</strong> tarafından geliştirilmiş olup, {{ $markaAdi }} işletmesinin randevu ve müşteri yönetimi süreçlerini yürütür.</p>

        <h2>1. Gizlilik Politikası Kapsamı</h2>
        <p>{{ $markaAdi }}, uygulamayı ziyaret eden kullanıcıların bireysel ve kurumsal anlamda gizliliğini korumak amacıyla veri güvenliği ilkeleri benimsemiştir. Bu kurallar {{ $markaAdi }} mobil uygulaması, ilişkili web hizmetleri ve alt bileşenler için geçerlidir.</p>

        <h2>2. Kişisel Bilgilerin Kullanımı</h2>
        <p>Üyelik ve randevu oluşturma sırasında talep edilen kişisel bilgiler yalnızca Kullanıcı Sözleşmesi'nde belirtilen amaçlar (randevu takibi, iletişim, hatırlatma, işletme içi müşteri yönetimi) için kullanılacaktır. Vermiş olduğunuz bilgiler, tarafınızca aksine bir talimat verilmediği sürece herhangi bir kapsamda kullanılmayacak, üçüncü şahıslarla paylaşılmayacaktır.</p>

        <h2>3. Güvenlik Teknolojileri</h2>
        <p>Sistem verileri, en güncel güvenlik teknolojileri kullanılarak korunmaktadır. Uygulama ile sunucu arasındaki tüm iletişim SSL/TLS (HTTPS) protokolü üzerinden şifrelenmektedir. Şifreler tek yönlü kriptografik hash algoritmalarıyla saklanır.</p>

        <h2>4. Ticari İletişim</h2>
        <p>{{ $markaAdi }}, kullanıcının isteği dışında ticari iletişim faaliyeti yapmamayı, açık izin alınmaksızın pazarlama yapmamayı taahhüt eder. Kullanıcı istediği anda ticari elektronik ileti almayı reddedebilir.</p>

        <h2>5. IP Adresi Kullanımı</h2>
        <p>Sistemle ilgili teknik sorunların tespiti, güvenlik denetimi ve demografik analiz amacıyla IP adresleri toplanıp işlenebilmektedir. Bu veriler kişisel tanımlama amacıyla kullanılmaz.</p>

        <h2>6. Yasal Yükümlülükler</h2>
        <p>5651 sayılı yasada belirtilen trafik verisi saklama yükümlülükleri ile 213 sayılı Vergi Usul Kanunu hükümleri ve 6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) çerçevesindeki yükümlülükler saklıdır.</p>

        <h2>7. Üçüncü Taraf Bağlantıları</h2>
        <p>{{ $markaAdi }} uygulaması, üçüncü taraf sitelere veya servislere bağlantı içerebilir. Bu bağlantılar aracılığıyla erişilen sitelerin gizlilik uygulamalarından {{ $markaAdi }} ve Webfirmam Ltd. Şti. sorumlu tutulamaz.</p>

        <h2>8. Kullanıcı Sorumluluğu</h2>
        <p>Kullanıcılar hesap ve şifrelerinin güvenliğinden sorumludur. Uygulamanın kullanımı sırasında verilerin yedeklenmemesinden veya kullanıcı hatasından doğacak zararlardan {{ $markaAdi }} sorumlu değildir.</p>

        <h2>9. Toplanan Bilgi Türleri</h2>
        <ul>
            <li>Ad ve soyad</li>
            <li>Telefon numarası</li>
            <li>E-posta adresi (isteğe bağlı)</li>
            <li>Doğum tarihi (isteğe bağlı; hatırlatma amaçlı)</li>
            <li>Randevu geçmişi ve tercih verileri</li>
            <li>Profil fotoğrafı (isteğe bağlı; kullanıcı yüklediğinde)</li>
            <li>Cihaz kimliği ve push bildirimi token bilgisi</li>
            <li>Uygulama kullanım istatistikleri (analiz amacıyla)</li>
        </ul>

        <h2>10. Bilgilerin Açıklanması İstisnaları</h2>
        <p>Aşağıdaki durumlarda kişisel veriler üçüncü kişilere açıklanabilir:</p>
        <ul>
            <li>Mevzuattaki yasal zorunluluklara uyulması</li>
            <li>Kullanıcı sözleşmelerinin gerekleri</li>
            <li>Yetkili idari ve adli makamların talepleri</li>
            <li>Kullanıcıların hak ve özgürlüklerini koruma amacıyla</li>
        </ul>

        <h2>11. Gizli Bilgilerin Korunması</h2>
        <p>Gizli bilgilerin tamamının veya herhangi bir parçasının kamu alanına girmesini önleme gereği olan gerekli tüm teknik, hukuki ve yönetimsel tedbirleri almayı taahhüt ederiz.</p>

        <h2>12. Ödeme Bilgileri</h2>
        <p>{{ $markaAdi }} uygulaması içinde satın alma (in-app purchase) bulunmamaktadır. Ödemeler işletmenizin fiziksel adresinde nakit veya kredi kartı olarak alınır. {{ $markaAdi }} göndereceği e-postalarda kredi kartı numarası, kullanıcı adı, şifre veya parola talep etmez.</p>

        <h2>13. Çerezler ve Uygulama İzleme</h2>
        <p>Web tabanlı bileşenlerde tarayıcı çerezleri kullanılabilir. Mobil uygulamada oturum yönetimi için cihazda yerel saklama (SharedPreferences / Keychain) kullanılır. Bu veriler cihaz dışına aktarılmaz.</p>

        <h2>14. Anketler ve Yardım Talepleri</h2>
        <p>Kullanıcıdan alınan anket cevapları ve yardım talepleri istatistiksel analiz, ürün iyileştirme ve hizmet kalitesi ölçümü amacıyla kullanılabilir.</p>

        <h2>15. 6698 Sayılı KVKK Uygulaması</h2>
        <p>Kişisel veriler hukuka ve dürüstlük kuralına uygun; güncel ve doğru; belirli, açık ve meşru amaçlar için işlenmektedir. Kullanıcılar KVKK 11. madde kapsamındaki tüm haklarını (verinin silinmesi, düzeltilmesi, aktarımı vb.) kullanabilir.</p>

        <h2>16. Verilerin Aktarılması</h2>
        <p>Kişisel veriler yalnızca yasal zorunluluk ve teknik hizmet sunumu (bulut sunucu barındırma, bildirim iletimi) amacıyla yurtiçi veya yurtdışında ilgili mevzuat ve öngörülen güvenlik önlemleri dahilinde aktarılabilir.</p>

        <h2>17. Rıza Olmaksızın Veri İşleme</h2>
        <p>Kanun, sözleşme, hukuki yükümlülük veya veri sahibinin bilgiyi bizzat alenileştirmesi nedeniyle rıza alınmaksızın işleme yapılabilir.</p>

        <h2>18. Hesap Silme Talebi</h2>
        <p>Kullanıcılar uygulama içinden "Hesabımı Sil" seçeneğini kullanarak veya aşağıdaki iletişim adreslerinden bize başvurarak hesabının ve tüm ilişkili verilerin silinmesini talep edebilir. Silme talebi 30 iş günü içinde işleme alınır. Yasal saklama yükümlülüğüne tabi veriler (fatura, işlem kaydı) yasal süre boyunca saklanmaya devam eder.</p>

        <h2>19. Pazarlama ve İletişim İzni</h2>
        <p>Politika kabul edilerek {{ $markaAdi }} işletmesinin sunacağı çeşitli avantajların bildirilmesi ve size özel tanıtım, promosyon, reklam, pazarlama ve anket amaçlarıyla telefon, SMS, e-posta gibi elektronik iletişim yapılmasına izin verilmektedir. Bu izni her zaman geri çekebilirsiniz.</p>

        <h2>20. Politika Değişiklikleri</h2>
        <p>{{ $markaAdi }} bu gizlilik politikasını dilediği zaman değiştirme hakkını saklı tutar. Önemli değişiklikler uygulama içi bildirim ile kullanıcılara duyurulur.</p>

        <h2>21. İletişim</h2>
        <div class="contact-box">
            <p style="margin-top:0;"><strong>Veri Sorumlusu:</strong> Webfirmam İnternet Hizmetleri ve Reklamcılık Sanayi Ticaret Limited Şirketi</p>
            <p><strong>Uygulama:</strong> {{ $markaAdi }}</p>
            <p><strong>Telefon:</strong> <a href="tel:+905412948144">0541 294 81 44</a></p>
            <p><strong>E-posta:</strong> <a href="mailto:info@randevumcepte.com.tr">info@randevumcepte.com.tr</a></p>
            <p style="margin-bottom:0;"><strong>Adres:</strong> Adalet Mahallesi, Şht. Polis Fethi Sekin Cd. No: 6, Kat: 3 Ofis: 32, 35530 Bayraklı / İzmir</p>
        </div>

        <h2>22. Yargılama Yetkisi</h2>
        <p>İşbu politikadan doğacak uyuşmazlıklarda İzmir Merkez Mahkemeleri ve İcra Daireleri yetkilidir. Politika Türkiye Cumhuriyeti kanunlarına tabidir.</p>

    </div>

    <p class="footer">© {{ date('Y') }} Webfirmam İnternet Hizmetleri Ltd. Şti. — {{ $markaAdi }}</p>

</div>
</body>
</html>
