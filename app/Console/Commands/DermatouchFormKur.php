<?php

namespace App\Console\Commands;

use App\FormTaslaklari;
use App\Salonlar;
use Illuminate\Console\Command;

class DermatouchFormKur extends Command
{
    protected $signature = 'dermatouch:form-kur
        {--salon= : Salon ID (verilmezse salon_adi LIKE %dermatouch% ile bulunur)}
        {--dry-run : Sadece raporla, kayit yapma}
        {--yenile : Ayni isimli form varsa uzerine yaz (sorular_json guncelle)}';

    protected $description = 'Dermatouch Klik salonu icin ozel dinamik onam formlarini kurar (Regenera Activa vb.). Idempotent, --yenile/--dry-run destekli. Sadece Dermatouch salonuna yuklenir.';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $yenile = (bool) $this->option('yenile');

        if ($this->option('salon')) {
            $salonlar = Salonlar::where('id', (int) $this->option('salon'))->get();
        } else {
            $salonlar = Salonlar::where('salon_adi', 'like', '%dermatouch%')->get();
        }

        if ($salonlar->isEmpty()) {
            $this->error('Dermatouch salonu bulunamadi. --salon=ID ile deneyin.');
            return 1;
        }

        $formlar = $this->formlar();

        foreach ($salonlar as $s) {
            $this->info("Salon: [{$s->id}] {$s->salon_adi}");
            foreach ($formlar as $f) {
                $mevcut = FormTaslaklari::where('salon_id', $s->id)
                    ->where('form_adi', $f['form_adi'])
                    ->first();

                if ($mevcut && !$yenile) {
                    $this->line("  - \"{$f['form_adi']}\": zaten var (id={$mevcut->id}), atlandi. Guncellemek icin --yenile.");
                    continue;
                }

                $json = json_encode($f['sorular'], JSON_UNESCAPED_UNICODE);
                $this->line("  - \"{$f['form_adi']}\": ".($mevcut ? "guncellenecek (id={$mevcut->id})" : 'eklenecek')." (".count($f['sorular'])." eleman)");

                if ($dryRun) {
                    continue;
                }

                if ($mevcut) {
                    $mevcut->aciklama     = $f['aciklama'];
                    $mevcut->sorular_json = $json;
                    $mevcut->is_dinamik   = 1;
                    $mevcut->save();
                    $this->info("    -> guncellendi (id={$mevcut->id})");
                } else {
                    $sonSira = (int) FormTaslaklari::where('salon_id', $s->id)->max('sira');
                    $form = new FormTaslaklari();
                    $form->salon_id         = $s->id;
                    $form->form_adi         = $f['form_adi'];
                    $form->aciklama         = $f['aciklama'];
                    $form->sorular_json     = $json;
                    $form->is_dinamik       = 1;
                    $form->is_sozlesme_tipi = 0;
                    $form->sira             = $sonSira + 1;
                    $form->save();
                    $this->info("    -> eklendi (id={$form->id})");
                }
            }
        }

        $this->line('');
        $this->info('Bitti.');
        return 0;
    }

    /**
     * Dermatouch'a yuklenecek formlarin listesi.
     * Yeni form gelince buraya bir eleman ekle.
     */
    private function formlar()
    {
        return [
            $this->regeneraActiva(),
        ];
    }

    private function el($tip, $soru, $zorunlu = false)
    {
        return ['tip' => $tip, 'soru' => $soru, 'zorunlu' => $zorunlu];
    }

    private function regeneraActiva()
    {
        $sorular = [
            $this->el('metin_blogu', "Bu formun amacı, sağlığınız ile ilgili konularda sizi bilinçlendirerek alınacak karara katılımınızı sağlamaktır. Yasal ve tıbbi zorunluluk taşıyan durumlar dışında bilgilendirmeyi reddedebilirsiniz.\n\nBu form, çoğu hastanın pek çok koşulda ihtiyaçlarını karşılayacak şekilde tanımlanmış olmakla birlikte bütün tedavi şekillerinin risklerini içeren bir belge olarak düşünülmemelidir. Kişisel sağlık durumunuza bağlı olarak hekiminiz size farklı ya da ek bilgi verebilir.\n\nTanı, tıbbi tedavi ve medikal işlem yararlarını ve olası risklerini öğrendikten sonra yapılacak uygulamaları kabul etmek ya da etmemek kendi kararınıza bağlıdır."),

            $this->el('not_kutusu', "UYARI: Bu formda verilen bilgiler, önerilen medikal işlemler hakkındaki riskleri ve alternatif tedavileri açıklamaya yöneliktir. Ancak burada bahsedilen medikal işlemin ya da alternatif tedavilerin karşılaşılabilecek risklerin tamamını içerdiği düşünülmemelidir. Doktorunuz, belli bir hasta ile ilgili olarak ya da tıbbi bilgilerine dayanarak size farklı bilgiler verebilir. Bu form yapılacak işlemle ilgili olarak hasta ve yakınlarını bilgilendirmek ve hastanın onayını almak amacıyla hazırlanmıştır; okutularak imzalanması yasal bir gerekliliktir. Yukarıda belirtilen tüm hususları dikkatle okumanız ve tüm sorularınızın bu formu imzalamadan önce cevaplanması çok önemlidir."),

            $this->el('bolum_basligi', 'AMELİYAT / GİRİŞİM / İŞLEM ÖNCESİ TANI'),
            $this->el('metin_ops', 'Ameliyat / Girişim / İşlem Öncesi Tanı'),

            $this->el('bolum_basligi', 'NASIL BİR TEDAVİ / GİRİŞİM UYGULANACAK?'),
            $this->el('metin_blogu', "Regenera Activa uygulamasında kullanılan maddeler: Kişinin kendi dokusu, Serum Fizyolojik / PRP.\n\nKişinin kendi dokusu: Bu uygulama için alınacak doku, kişinin donör bölgesindeki kısımdan alınır. Bu alınan doku saç folikül hücresi veya dokusu içerir.\n\nPRP (Platelet Rich Plasma): Uygulama için hastadan 10 cc kan alınır. Özel tüplere alınarak santrifüj edilir. Bu işlem sonucu trombositten zengin plazma elde edilir. Trombosit denilen kan hücreleri, vücudumuzdaki hasarlı dokuların onarımı ve doğal haline dönüşmelerini sağlamak için gerekli 'büyüme faktörlerini' yapısında barındırmaktadır. Dokularımızda herhangi bir hasar olursa bu kan hücreleri hasarlı dokuya gelerek onarım sürecini başlatır. PRP tedavisinde ise normal şartlar altında toplanandan daha fazla trombosit hasarlı dokuda birikir ve böylece onarım süreci hızlı ve güçlü bir şekilde başlar."),

            $this->el('bolum_basligi', 'REGENERA ACTİVA UYGULAMA TEKNİĞİ'),
            $this->el('madde_listesi', "Öncelikle hastanın saçlı derisi işleme uygun hale gelecek şekilde temizlenir.\nDokunun alınacağı donör bölgede yaklaşık 1.5-2 santimetrelik bir alan traşlanır ve bu bölgeye lokal anestezi uygulanır.\nDaha sonra üç farklı noktadan 2.5 mm punch biyopsi ile doku alınır.\nAlınan doku, içerisine serum fizyolojik konulmuş özel bir kite yerleştirilir ve tedavi için hazırlanmış cihaza konularak mikro parçacıklara ayrılması sağlanır.\nElde edilen otolog süspansiyon, sorun olan ve olma ihtimali olan bölgelere enjekte edilir.\nUygulanan bölgeye masaj yapılarak tedavi sonlandırılır."),

            $this->el('bolum_basligi', 'İŞLEMİN KOMPLİKASYONLARI VE RİSKLERİ'),
            $this->el('madde_listesi', "Anestezi sonrası ödem\nBaş ağrısı\nŞişlikler\nGeçici saç dökülmesi\nKızarıklık, kanama\nMorluk\nEnfeksiyon\nAlerjik reaksiyonlar\nYanma"),

            $this->el('bolum_basligi', 'UYGULAMA SONRASINDA DİKKAT EDİLMESİ GEREKEN HUSUSLAR'),
            $this->el('madde_listesi', "Uygulama alanına temas edilmemeli.\nUygulamadan sonra 24 saat süreyle banyo yapılmamalı ve saça hiçbir ürün kullanılmamalı.\nUygulamadan sonra 1 hafta süreyle yüzme aktivitesi yapılmamalı.\nUygulama günü aşırı şekilde güneşte kalınmamalı.\nUygulama sonrası doktorunuzun önerdiği ürünler kullanılmalı.\nBeklenmeyen bir etki gelişirse lütfen doktorunuza başvurunuz.\nUygulamadan 1 ay sonrasına kadar saç boya işlemi yapılmamalı."),

            $this->el('bolum_basligi', 'İŞLEMİ REDDETME DURUMUNDA ORTAYA ÇIKABİLECEK RİSK / FAYDALAR'),
            $this->el('metin_ops', 'İşlemi reddetme durumunda ortaya çıkabilecek risk / faydalar'),

            $this->el('bolum_basligi', 'ALTERNATİF TEDAVİ VE RİSK / FAYDALARI'),
            $this->el('metin_ops', 'Alternatif tedavi ve risk / faydaları'),

            $this->el('bolum_basligi', 'HASTA TARAFINDAN DURUMUNA İLİŞKİN VERİLEN ÖZEL BİLGİLER'),
            $this->el('metin_ops', 'Hasta tarafından durumuna ilişkin verilen özel bilgiler'),

            $this->el('bolum_basligi', 'HASTAYA ÖZEL HUSUSLAR (VARSA)'),
            $this->el('metin_ops', 'Genel tedavi süreci dışında hastaya özel hususlar (varsa)'),

            $this->el('bolum_basligi', 'KULLANILACAK İLAÇLARIN ÖNEMLİ ÖZELLİKLERİ'),
            $this->el('metin_ops', 'Kullanılacak ilaçların önemli özellikleri'),

            $this->el('bolum_basligi', 'HASTANIN SAĞLIĞI İÇİN KRİTİK YAŞAM TARZI ÖNERİLERİ'),
            $this->el('metin_ops', 'Hastanın sağlığı için kritik yaşam tarzı önerileri'),

            $this->el('bolum_basligi', 'GEREKTİĞİNDE AYNI KONUDA TIBBİ YARDIMA NASIL ULAŞILACAĞI'),
            $this->el('metin_ops', 'Gerektiğinde aynı konuda tıbbi yardıma nasıl ulaşılacağı'),

            $this->el('bolum_basligi', 'YAPILACAK İŞLEM İÇİN HASTA RIZASI'),
            $this->el('metin_ops', 'Hasta Adı Soyadı'),
            $this->el('metin_ops', 'Uygulayan Doktor (Dr.)'),
            $this->el('metin_blogu', "1) Yukarıda adı geçen doktor tarafından tarafıma uygulanacak işleme ilişkin hazırlanmış hasta bilgilendirme formunu okudum. Ayrıca hekimim bana sözlü olarak da nasıl bir tıbbi tedavi/işlem yapılacağını, amacını, yararlarını, tahmini tedavi süresini, komplikasyon ve risklerini ve alternatif tedavi yöntemleri ile bunların risk ve komplikasyonlarını açıkladı. Tüm sorularımı yanıtladı. Tedaviyi kabul etmemem durumunda karşı karşıya kalabileceğim durumları açıkladı. Uygulamanın başarı şansı, iyileşme sürecim, kullanılacak ilaçların önemli özellikleri, kritik yaşam tarzı önerileri ve gerektiğinde tıbbi yardıma nasıl ulaşabileceğim hakkında bilgi verdi. Onam İçin Bilgilendirme Formu'nu okudum; sözel olarak detaylı bilgilendirildim ve tüm sorularım cevaplandırıldı, kararımı vermek için yeterli süre tanındı. Yapılacak tıbbi uygulamanın amacı, yararları, alternatif tedavi yöntemleri, olası risk ve komplikasyonlarını anladım; yapılacak tıbbi uygulama için onam veriyorum. Ameliyat esnasında gerektiğinde kan verilmesini kabul ediyorum."),
            $this->el('metin_blogu', "2) İşlem sırasında, sonrasında ya da anestezi sırasında önceden bilinemeyen durumların ortaya çıkması halinde yukarıda anlatılanların dışında işlemlerin gerekebileceğini anladım. Bu durumda yukarıda adı geçen doktor ve ekibinin gerekebilecek uygulamalara karar vermelerini ve yapmalarını onaylıyorum. İmzam ile tedavi için gerekebilecek tüm uygulamaları ve daha sonra çıkabilecek durumlarda yapılacak tedavileri onaylıyorum.\n\n3) İşlem için gerekecek anestezik ilaçların kullanılmasını onaylıyorum. Tüm anestezi metotlarının komplikasyonlara, doku hasarına ya da ölüme neden olabileceğini anladım.\n\n4) İşlemle elde edilecek sonuçlar hakkında hiçbir garanti verilmediğini anladım.\n\n5) Girişim/tetkik sırasında ortaya çıkabilecek zorunlu durumlarda önceden onay alınmasına gerek olmaksızın ek girişim/tetkik yapılmasına ve kan/kan ürünleri kullanılmasına rıza gösteriyorum.\n\n6) Onamda adı geçen hekimin yanı sıra, onun yetkisi, gözlemi ve yöntemi altında başka hekim, hemşire ve sağlık çalışanlarının da işlemi yürütebileceğini/işleme katılabileceğini anladım ve onam veriyorum.\n\n7) İşlem sırasında bir organ veya doku çıkarılırsa, bunların bir süre testler için alıkonulup sonra kurum tarafından yok edilebileceğini anladım.\n\n8) İşlem sırasında ani, hayati tehdit edici olaylar olursa bunların uygun şekilde tedavi edileceği bana anlatıldı.\n\n9) İşlem sırasında fotoğraf ve video kaydı yapılabileceği bana anlatıldı; bunlar kimliğim ortaya konulmamak üzere tıp eğitiminde kullanılabilir. Tıbbi eğitim amacıyla gözlemciler bulunabileceğini anladım.\n\n10) Bu işlem sırasında ve sonrasında oluşabilecek sorunlar nedeniyle tam istediğim sonucun elde edilemeyebileceğini ve bilgilendirme formunda okuduğum istenmeyen komplikasyonların oluşabileceğini anladım.\n\n11) Doktor bana tıbbi durumumu ve planlanan işlemi anlattı; işlemin risklerini ve olası sonuçlarını anladım. Bu işlem dışında başka seçeneklerim olduğunu ve bunların risklerini de anladım.\n\n12) Gerektiğinde teşhis ve tedaviye yardımcı olmak üzere hekimimin talebiyle kurum dışından bir uzman hekimin konsültasyonuna izin veriyorum.\n\n13) Hekimin ilaç orderına riayet edeceğimi, hemşire ve hekimin bilgisi dışında kendi başıma herhangi bir ilaç kullanmayacağımı anladım ve onayladım.\n\n14) Bu onam belgesinin özgün dili Türkçe olup, ana dilime çevirmen tarafından kelime kelime çevrilerek bana/bize anlatıldı.\n\n15) Bu onam belgesinin bana/bize okunduğunu, tamamen açıklandığını, okuduğumu, boşlukların birlikte doldurulduğunu ve içeriğini anladığımı kabul ve tasdik ederim.\n\n16) Tüm tedavi sürecimin 6 ay - 1 yılda tamamlandığı bilgisi tarafıma verilmiştir. Bu süreçte yaşadığım her problemde kuruma ve operasyonu yapan hekimime bilgi vereceğimi kabul ederim."),
            $this->el('onay_kutusu', 'Yukarıdaki bilgilendirme ve onam metninin tamamını okudum, anladım ve tüm maddeleri kabul ederek onam veriyorum.', true),

            $this->el('bolum_basligi', 'HASTA / YASAL TEMSİLCİ BİLGİLERİ'),
            $this->el('metin_ops', 'Hasta Doğum Tarihi'),
            $this->el('metin_ops', 'Yasal Temsilci Adı Soyadı (gerekliyse)'),
            $this->el('metin_ops', 'Yasal Temsilci Yakınlık Derecesi'),
            $this->el('checkbox_grup', "Hastanın bilinci kapalı\nHasta 18 yaşından küçük\nHastanın karar verme yetisi yok\nAcil"),
            $this->el('metin_ops', 'Şahit Adı Soyadı (hastane çalışanı harici, mevcutsa)'),
            $this->el('metin_ops', 'Tercüman Adı Soyadı (ihtiyaç halinde)'),
            $this->el('metin_ops', 'Bilgilendirmeyi Yapan Hekim Adı Soyadı'),
            $this->el('not_kutusu', "Hasta dijital imzasını aşağıdaki imza alanına atar. Yasal temsilci, şahit, tercüman ve hekim imzaları, bu formun basılı (PDF) çıktısı üzerinde ıslak imza olarak alınır."),
        ];

        return [
            'form_adi' => 'Regenera Activa Uygulama ve Onam Formu',
            'aciklama' => 'Sayın hastamız, bu form Regenera Activa uygulaması hakkında sizi bilgilendirmek ve onayınızı almak amacıyla hazırlanmıştır. Lütfen tüm bölümleri dikkatle okuyunuz.',
            'sorular'  => $sorular,
        ];
    }
}
