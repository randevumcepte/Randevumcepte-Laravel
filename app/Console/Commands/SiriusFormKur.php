<?php

namespace App\Console\Commands;

use App\FormTaslaklari;
use App\Salonlar;
use Illuminate\Console\Command;

class SiriusFormKur extends Command
{
    protected $signature = 'sirius:form-kur
        {--salon= : Salon ID (verilmezse salon_adi LIKE %sirius% ile bulunur)}
        {--dry-run : Sadece raporla, kayit yapma}
        {--yenile : Ayni isimli form varsa uzerine yaz (sorular_json guncelle)}';

    protected $description = 'Sirius salonu icin kapsamli Dijital Musteri Kayit Formu (saglik beyani + onam + kvkk + sozlesme) dinamik form sablonunu kurar. Idempotent.';

    // Ayni salona iki kez eklenmesini engellemek icin sabit form adi.
    const FORM_ADI = 'Dijital Müşteri Kayıt Formu (Sağlık Beyanı + Onam)';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $yenile = (bool) $this->option('yenile');

        // Hedef salon(lar)i bul.
        if ($this->option('salon')) {
            $salonlar = Salonlar::where('id', (int) $this->option('salon'))->get();
        } else {
            $salonlar = Salonlar::where('salon_adi', 'like', '%sirius%')->get();
        }

        if ($salonlar->isEmpty()) {
            $this->error('Sirius salonu bulunamadi. --salon=ID ile deneyin.');
            return 1;
        }

        $sorular  = $this->sorulariOlustur();
        $aciklama = 'Sağlık Beyanı • Aydınlatılmış Onam • KVKK Açık Rıza • Hizmet Sözleşmesi. Lütfen tüm bölümleri eksiksiz ve doğru şekilde doldurunuz.';
        $json     = json_encode($sorular, JSON_UNESCAPED_UNICODE);

        foreach ($salonlar as $s) {
            $mevcut = FormTaslaklari::where('salon_id', $s->id)
                ->where('form_adi', self::FORM_ADI)
                ->first();

            if ($mevcut && !$yenile) {
                $this->line("- [{$s->id}] {$s->salon_adi}: form zaten var (id={$mevcut->id}), atlandi. Guncellemek icin --yenile kullanin.");
                continue;
            }

            $this->line("- [{$s->id}] {$s->salon_adi}: ".($mevcut ? "guncellenecek (id={$mevcut->id})" : 'eklenecek')." ".count($sorular)." eleman");

            if ($dryRun) {
                continue;
            }

            if ($mevcut) {
                $mevcut->aciklama    = $aciklama;
                $mevcut->sorular_json = $json;
                $mevcut->is_dinamik  = 1;
                $mevcut->save();
                $this->info("  -> guncellendi (id={$mevcut->id})");
            } else {
                $sonSira = (int) FormTaslaklari::where('salon_id', $s->id)->max('sira');
                $form = new FormTaslaklari();
                $form->salon_id        = $s->id;
                $form->form_adi        = self::FORM_ADI;
                $form->aciklama        = $aciklama;
                $form->sorular_json    = $json;
                $form->is_dinamik      = 1;
                $form->is_sozlesme_tipi = 0;
                $form->sira            = $sonSira + 1;
                $form->save();
                $this->info("  -> eklendi (id={$form->id})");
            }
        }

        $this->line('');
        $this->info('Bitti.');
        return 0;
    }

    private function sorulariOlustur()
    {
        $eh = function ($tip, $soru, $zorunlu = false) {
            return ['tip' => $tip, 'soru' => $soru, 'zorunlu' => $zorunlu];
        };

        return [
            // 1. MÜŞTERİ BİLGİLERİ (ad-soyad + telefon formun ustunde otomatik gosterilir)
            $eh('bolum_basligi', '1. MÜŞTERİ BİLGİLERİ'),
            $eh('metin', 'T.C. Kimlik No', true),
            $eh('metin', 'Doğum Tarihi', true),
            $eh('metin_ops', 'E-posta'),
            $eh('metin_ops', 'Adres'),
            $eh('metin_ops', 'Meslek'),
            $eh('metin_ops', 'Kan Grubu'),
            $eh('metin_ops', 'Acil Durumda Ulaşılacak Kişi'),
            $eh('metin_ops', 'Yakınlık Derecesi'),
            $eh('metin_ops', 'Acil Telefon'),

            // 2. UYGULANACAK İŞLEM
            $eh('bolum_basligi', '2. UYGULANACAK İŞLEM'),
            $eh('checkbox_grup', "Lazer Epilasyon\nBölgesel İncelme\nCilt Bakımı\nHydrafacial\nAltın İğne\nHIFU\nG5\nLenf Drenaj\nKalıcı Makyaj\nLeke Tedavisi\nSelülit/Çatlak\nGıdı Germe\nDiğer"),

            // 3. SAĞLIK BEYANI
            $eh('bolum_basligi', '3. SAĞLIK BEYANI'),
            $eh('metin_blogu', 'Aşağıdaki durumlardan sizde bulunanları işaretleyiniz. Hiçbiri yoksa "Hiçbiri" seçeneğini işaretleyiniz.'),
            $eh('checkbox_grup', "Hamilelik\nEmzirme\nKalp pili\nMetal implant/protez\nEpilepsi\nDiyabet\nTansiyon\nTiroit hastalığı\nKanser öyküsü\nKemoterapi/Radyoterapi\nKan sulandırıcı ilaç\nAlerji\nEgzama\nSedef\nRosacea\nUçuk\nAçık yara\nEnfeksiyon\nKeloid oluşumu\nIşığa duyarlılık\nVaris\nLenfödem\nKaraciğer hastalığı\nBöbrek hastalığı\nHormonal rahatsızlık\nPolikistik over\nBotoks\nDolgu\nKimyasal peeling\nRoaccutane/Isotretinoin kullanımı (son 6 ay)\nDüzenli ilaç kullanımı\nYakın zamanda ameliyat\nHiçbiri"),
            $eh('metin_ops', 'Kullandığınız ilaçlar'),
            $eh('metin_ops', 'Doktorunuzun adı (varsa)'),

            // 4. İŞLEME ÖZEL DEĞERLENDİRME
            $eh('bolum_basligi', '4. İŞLEME ÖZEL DEĞERLENDİRME'),
            $eh('alt_baslik', 'LAZER EPİLASYON'),
            $eh('checkbox_grup', "Son 30 gün güneşlendim\nSon 30 gün solaryuma girdim\nAğda/cımbız kullandım\nDövme mevcut\nYoğun ben mevcut\nHormonal tedavi görüyorum"),
            $eh('alt_baslik', 'BÖLGESEL İNCELME'),
            $eh('checkbox_grup', "Kalp pili\nMetal implant\nVaris\nLenfödem\nLiposuction öyküsü\nObezite tedavisi\nDiyetisyen kontrolündeyim"),
            $eh('alt_baslik', 'CİLT BAKIMI'),
            $eh('checkbox_grup', "Hassas cilt\nAkne tedavisi\nKimyasal peeling\nDolgu/Botoks\nAktif enfeksiyon\nSon 15 gün lazer işlemi"),

            // 5. FOTOĞRAF / VİDEO İZNİ
            $eh('bolum_basligi', '5. FOTOĞRAF / VİDEO İZNİ'),
            $eh('checkbox_grup', "Öncesi-sonrası fotoğraflarım yalnızca hasta dosyamda saklanabilir.\nEğitim amaçlı anonim kullanılabilir.\nSosyal medya ve reklam çalışmalarında ayrıca izin verdiğim ölçüde kullanılabilir.\nHiçbir şekilde paylaşılmasını istemiyorum."),

            // 6. KVKK AYDINLATMA VE AÇIK RIZA
            $eh('bolum_basligi', '6. KVKK AYDINLATMA VE AÇIK RIZA'),
            $eh('metin_blogu', '6698 Sayılı Kişisel Verilerin Korunması Kanunu kapsamında kimlik, iletişim, sağlık, fotoğraf/video, randevu, işlem ve ödeme bilgilerimin; hizmetin güvenli şekilde sunulması, yasal yükümlülüklerin yerine getirilmesi, randevu ve paket yönetimi, müşteri memnuniyeti süreçleri ve gerektiğinde resmi mercilere bilgi verilmesi amacıyla işlenebileceği tarafıma açıklanmıştır.'),
            $eh('onay_kutusu', 'Okudum, anladım ve açık rıza veriyorum.', true),

            // 7. AYDINLATILMIŞ ONAM
            $eh('bolum_basligi', '7. AYDINLATILMIŞ ONAM'),
            $eh('metin_blogu', 'Uygulanacak işlemin amacı, uygulanış şekli, beklenen faydaları, olası riskleri, geçici yan etkileri (kızarıklık, hassasiyet, ödem vb.), seans gerekliliği ve işlem sonrası dikkat edilmesi gereken hususlar tarafıma anlatılmıştır. Sonuçların kişiden kişiye değişebileceğini, hiçbir estetik işlemin kesin sonuç garantisi vermediğini, sağlık bilgilerimi eksiksiz ve doğru beyan etmekle yükümlü olduğumu kabul ederim.'),
            $eh('onay_kutusu', 'Kabul ediyorum.', true),

            // 8. HİZMET SÖZLEŞMESİ
            $eh('bolum_basligi', '8. HİZMET SÖZLEŞMESİ'),
            $eh('madde_listesi', "Randevu saatlerine uyacağımı ve gecikmelerin hizmet süresini etkileyebileceğini kabul ederim.\nPaketler kişiye özeldir, devredilemez ve bölünemez.\nKullanılmış seanslar için ücret iadesi talep etmeyeceğimi kabul ederim.\nUzmanın işlemi güvenlik gerekçesiyle erteleme veya durdurma yetkisi olduğunu kabul ederim.\nİşlem sonrası bakım talimatlarına uyacağımı kabul ederim.\nYanlış veya eksik sağlık beyanından doğabilecek sonuçların sorumluluğunun tarafıma ait olduğunu kabul ederim."),
            $eh('onay_kutusu', 'Tüm maddeleri okudum ve kabul ediyorum.', true),
        ];
    }
}
