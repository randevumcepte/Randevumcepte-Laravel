<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kuafor + Guzellik salonu HIZMET BILGILENDIRME kalip kutuphanesi (cevap havuzlu).
 *
 * Her hizmet = 1 kalip: KISA anahtar tetikleyiciler (bir kelime cumlenin icinde
 * geciyorsa eslesir -> "dip boya" tek basina "dip boya nasil yapilir/nedir/yaptirmak
 * istiyorum" hepsini yakalar) + "---" ile ayrilmis 10-14 CEVAP (rastgele secilir ->
 * ayni metni tekrar etmez).
 *
 * NOT: Fiyat/veri sorulari BILEREK yok. "kac TL" gibi sorular kural motoruna/canli
 * veriye birakilir; bilgi kaliplari sadece "nedir/nasil" icindir (uydurma fiyat yok).
 *
 * Idempotent: /opt/php74/bin/php artisan asistan:hizmet-kaliplari
 */
class HizmetBilgiKaliplari extends Command
{
    protected $signature = 'asistan:hizmet-kaliplari {--force : Surum ayni olsa da yeniden yaz} {--quiet-noop : Degismemisse sessizce cik (zamanlayici icin)}';
    protected $description = 'Kuafor/guzellik hizmet BILGILENDIRME kaliplarini (cevap havuzlu) yukler. Surum degisince OTOMATIK uygulanir (zamanlayici).';

    // ICERIK SURUMU: iceriyi (tetik/cevap) her degistirdiginde ARTIR. Sunucuda
    // zamanlayici bu surumu gorunce KENDILIGINDEN uygular; elle komut GEREKMEZ.
    protected $surum = 'v18-2026-08-24-cilt-bakimi';

    public function handle()
    {
        if (!Schema::hasTable('asistan_kalip')) {
            if (!$this->option('quiet-noop')) $this->error('asistan_kalip tablosu yok. Once: php artisan migrate --force');
            return 0;
        }

        // SURUM KONTROLU: ayni surum zaten yuklenmisse hicbir sey yapma (ucuz no-op).
        $surumAnahtar = 'asistan_hizmet_kalip_surum';
        $mevcut = null;
        try { $mevcut = \Cache::get($surumAnahtar); } catch (\Throwable $e) {}
        if (!$this->option('force') && $mevcut === $this->surum) {
            if (!$this->option('quiet-noop')) $this->info("Hizmet kaliplari guncel (surum {$this->surum}), degisiklik yok.");
            return 0;
        }

        $lib = $this->kutuphane();
        // ZENGIN bilgilendirme icerigi (app/Support/HizmetBilgiIcerik) built-in kisa
        // kayitlarin UZERINE yazar. Yeni/genis hizmetler orada yonetilir.
        try {
            foreach (\App\Support\HizmetBilgiIcerik::veri() as $kat => $veri) {
                if (!empty($veri['tetik']) && !empty($veri['cevaplar'])) {
                    $lib[$kat] = $veri;
                }
            }
        } catch (\Throwable $e) {}
        $kategoriler = array_keys($lib);
        $now = date('Y-m-d H:i:s');

        // EMEKLI KATEGORILER: artik uretilmeyen ama eskiden DB'ye yazilmis kategoriler.
        // Bunlar $lib'te olmadigi icin normal silme onlari birakirdi (oksuz kayit).
        // Burada elle silinir. rofle-balyaj -> yerine ayri 'rofle'/'balayage'/'ombre'.
        $emekli = ['rofle-balyaj', 'keratin', 'lazer'];
        DB::table('asistan_kalip')->whereIn('kategori', $emekli)->delete();

        // KOPYA TEMIZLIGI: bu seeder'in sahip oldugu kategorileri silip yeniden yaz.
        // NOT: Panelden bu kategorilere elle eklediklerin surum artinca silinir;
        //      kalici olsun istersen seeder'a eklenmeli.
        $silinen = DB::table('asistan_kalip')->whereIn('kategori', $kategoriler)->delete();

        $eklendi = 0;
        foreach ($lib as $kat => $m) {
            DB::table('asistan_kalip')->insert([
                'tetikleyiciler' => trim($m['tetik']),
                'cevap'          => implode("\n---\n", $m['cevaplar']),
                'kategori'       => $kat, 'aktif' => 1, 'kullanim_sayisi' => 0,
                'created_at'     => $now, 'updated_at' => $now,
            ]);
            $eklendi++;
        }

        try { \Cache::forever($surumAnahtar, $this->surum); } catch (\Throwable $e) {}
        try { \Cache::forget('asistan_kalip_liste_v1'); } catch (\Throwable $e) {}
        if (!$this->option('quiet-noop')) {
            $this->info("Hizmet kutuphanesi yazildi (surum {$this->surum}). Silinen: {$silinen}, yazilan: {$eklendi}.");
        }
        return 0;
    }

    /** kategori => ['tetik' => 'a, b, c', 'cevaplar' => [...]] */
    protected function kutuphane()
    {
        return [
            'dip-boya' => [
                'tetik' => 'dip boya, dip boyama, dip boyasi, dip boya nedir, dip boya nasil, dip boya ne demek, dip boya uygula, dip boya islem, dip boya yaptir, dip boya komple, dip boya kok boya, kok boya ile dip, dip boya fark, dip boya renk, dip boya hangi renk, dip boya ton, dip boya dogal, dip boya acma, dip boya acilir, dip boya acar, dip boya zarar, dip boya yiprat, dip boya bakim, dip boya sonrasi, dip boya sure, dip boya kac saat, dip boya ne kadar surer, dip boya siklik, dip boya kac haftada, dip boya dayanir, dip boya ne zaman, dip boya beyaz, dip boya analiz, dip boya danismanlik, dipleri geldi, dipleri boyat, diplerimi boyat, dipler cikti, diplerim cikti, diplerim geldi, diplerim uzadi, dipleri belli, dipleri belli oldu, sac diplerim, sac dipleri, dip acildi, kokleri geldi, kokler geldi, koklerim geldi, koklerim cikti, koklerimi boyat, sadece kokleri, sadece dipleri, cikan saclarimi boyat, dip rengi, dip kapatma, sac dibi boya',
                'cevaplar' => [
                    'Dip boya, saçın uzayan kök kısmındaki renk farkını gidermek amacıyla uygulanan bir boya işlemidir.',
                    'Saç dipleriniz uzadığında oluşan renk farklılığını kapatmak için genellikle yalnızca dip bölgesine boya uygulanır.',
                    'Dip boya işleminde saçın tamamı yerine, uzayan ve renk farkı oluşturan kök bölümü boyanır.',
                    'Saç renginizin bütünlüğünü korumak ve çıkan dipleri kapatmak için dip boya uygulanabilir.',
                    'Eğer saç dipleriniz belirginleştiyse, saçın tamamını boyamadan yalnızca köklere uygulama yapılabilir.',
                    'Dip boya, özellikle saç uzadıkça ortaya çıkan dip rengini mevcut saç rengiyle uyumlu hale getirmek için tercih edilir.',
                    'İşlem sırasında saçınızın mevcut rengine uygun bir ton belirlenerek uzayan dip bölgesine uygulama yapılır.',
                    'Dip boya sayesinde saçın dip kısmındaki renk farklılığı azaltılarak daha bütün ve düzenli bir görünüm elde edilebilir.',
                    'Saçınızın sadece kök kısmında renk değişikliği varsa, komple boya yerine dip boya yeterli olabilir.',
                    'Dip boya uygulamasında amaç, uzayan saç köklerini mevcut saç rengiyle mümkün olduğunca uyumlu hale getirmektir.',
                    'Beyaz saçlarınız varsa, saç renginize uygun bir uygulamayla beyazların görünümünü azaltmak için dip boya tercih edilebilir.',
                    'Dip boya işlemi saçın tamamını boyamak anlamına gelmez; esas olarak uzayan kök bölgesine uygulanır.',
                    'Saç dipleriniz çıktıysa ve uçlarda renk değişikliği istemiyorsanız, yalnızca dip bölgesine işlem yapılması değerlendirilebilir.',
                    'Hangi rengin kullanılacağı saçınızın mevcut rengine, dip renginize ve istediğiniz sonuca göre belirlenir.',
                    'Dip boya öncesinde saçın mevcut durumu ve renk farkı değerlendirilerek uygun uygulama belirlenebilir.',
                    'Dip boya işleminin süresi saçın yapısına, kullanılan ürüne ve uygulanacak işleme göre değişiklik gösterebilir.',
                    'Dip boya ile saçın uzayan kısmındaki renk farkı giderilmeye çalışılır ve daha homojen bir görünüm hedeflenir.',
                    'Saçınızın yalnızca dipleri çıktıysa, komple boya yaptırmak yerine dip boya seçeneğini değerlendirebilirsiniz.',
                    'Dip boya uygulamasının ne sıklıkla tekrarlanacağı saçın uzama hızına ve renk farkının ne kadar belirgin olduğuna bağlıdır.',
                    'Dip boya konusunda en doğru renk seçimi, saçınızın mevcut rengi ve hedeflenen görünüm değerlendirilerek yapılır.',
                    'Kök boya ve dip boya günlük kullanımda benzer anlamlarda kullanılabilir; ikisi de genellikle saçın uzayan kök bölümüne yapılan uygulamayı ifade eder.',
                    'Dip boya saç rengini tamamen değiştirmekten ziyade mevcut saç rengiyle uzayan köklerin uyumunu sağlamaya yönelik bir uygulamadır.',
                    'Eğer saçınızın dipleri ile uzunlukları arasında belirgin bir renk farkı oluştuysa, uzmanınız uygun işlemi belirleyebilir.',
                    'Dip boya işleminin saçınızı yıpratıp yıpratmayacağı kullanılan ürün, saçın mevcut durumu ve uygulama şekline göre değişebilir.',
                    'Saçınızda beyazlar varsa dip boya ile beyazların görünümünü azaltmaya yönelik bir uygulama yapılabilir.',
                    'Dip boya yaptırmadan önce saçınızı yıkayıp yıkamamanız kullanılacak ürüne ve salonun uygulama prosedürüne göre değişebilir.',
                    'Dip boya ile saç renginin tamamen açılması her zaman mümkün değildir; hedeflenen renk ve mevcut saç rengi değerlendirilmelidir.',
                    'Dip boya için uygulanacak işlem saçınızın mevcut durumuna göre değişebileceğinden, salonda değerlendirme yapılması en sağlıklı sonucu verir.',
                    'Dipleriniz çıktıysa ancak saçınızın tamamında işlem istemiyorsanız, sadece köklere yönelik bir uygulama sizin için daha uygun olabilir.',
                    'Dip boya hakkında daha net bilgi verebilmemiz için saçınızın mevcut rengi, diplerinizin durumu ve istediğiniz görünüm önemlidir.',
                    'Dip boya, saçınızın uzayan kök kısmında oluşan renk farkını kapatmak için yapılan boya işlemidir. Sadece dip bölgesine, mevcut saç renginize uygun ton uygulanır.',
                    'Saç dipleriniz uzayınca çıkan renk farkını gidermek için uygulanan işleme dip boya denir. Saçın tamamı değil, yalnızca uzayan kökler boyanır.',
                    'Dip boyada amaç, kökle boylar arasındaki renk geçişini eşitlemektir. Saç renginize en yakın tonu seçip yalnızca diplere uyguluyoruz.',
                    'Dipleriniz geldiyse komple boyamaya gerek kalmadan sadece kökleri boyayarak rengi yenileyebiliriz; buna dip boya diyoruz.',
                    'Dip boya, düzenli boya yaptıranların araya yaptırdığı bir bakımdır. Böylece saç fazla yıpranmadan renk bütünlüğü korunur.',
                    'Uzayan köklerinizi mevcut renginize uygun boyayla kapatıyoruz. İşlem sadece dip bölgesine yapıldığı için saç uçlarınız korunur.',
                    'Saçınızın dipleri çıktıysa ve komple boya istemiyorsanız, yalnızca kökleri boyayan dip boya tam size göre.',
                    'Dip boyada boylardaki rengi bozmadan sadece yeni uzayan bölümü renklendiriyoruz; sonuç doğal ve bütünlüklü olur.',
                    'Kısaca: kök bölgesine renginize uygun boya uygulanır, bekletilir ve durulanır. Böylece dipteki renk farkı kaybolur.',
                    'Dip boya, saç sağlığı açısından komple boyamaktan daha koruyucudur çünkü uçlar tekrar tekrar boyaya maruz kalmaz.',
                    'Kökleriniz açıldığında rengi tazelemenin en pratik yolu dip boyadır; sadece ihtiyaç olan bölgeye uygulanır.',
                    'Saç diplerinizdeki renk açılmasını uygun tonda boyayla kapatıyoruz; boylarınız olduğu gibi kalıyor.',
                    'Dip boya genelde kısa sürer, ancak saçınızın uzunluğuna ve durumuna göre süre değişebilir. Renk tercihini birlikte belirleriz.',
                    'Diplerinizi kapatmak istiyorsanız uygun; rengi seçip yalnızca köklere uyguluyoruz, böylece saçınız yeni boyanmış gibi görünüyor.',
                    'Dip boya, saç uzadıkça kökte beliren renk farkını mevcut saç renginizle uyumlu hale getirmek için uygulanır. Saçın tamamı boyanmaz; yalnızca uzayan dip bölgesine, renginize en yakın ton seçilerek uygulama yapılır. Böylece renk bütünlüğü korunur ve saç uçları yıpranmaz.',
                    'Saç dipleriniz belirginleştiyse, tüm saçı boyamaya gerek kalmadan sadece kökleri boyayarak doğal bir görünüm elde edebiliriz. Beyaz ya da kır saçınız varsa dip boya bunları da kapatır. İşlem kısa sürer; süre saç uzunluğunuza ve dip miktarına göre değişir.',
                    'Dip boyada amaç, saçın uzayan kök kısmındaki renk açılmasını gidermektir. Boylardaki renk korunur, yalnızca yeni çıkan dip bölgesi renklendirilir. Düzenli boya yaptıranların araya yaptırdığı pratik bir bakımdır ve komple boyaya göre saçı daha az yıpratır.',
                    'Diplerinizin çıktığını fark ettiyseniz ve komple renk değişikliği istemiyorsanız dip boya idealdir. Renginize uygun ton belirlenir, sadece köklere uygulanır, bekletilir ve durulanır. Sonuçta saçınız yeni boyanmış gibi bütünlüklü görünür.',
                ],
            ],

            // FIYAT (kontrollu): uydurma rakam YOK; salona/randevuya yonlendirir.
            // Tetikleyiciler DIP BOYA'ya OZEL (bilgi kalibinin "dip boya" anahtarindan
            // daha uzun -> fiyat sorusunda fiyat kalibi kazanir). "kac para" gibi genel
            // kelimeler BILEREK yok: patron kendisi "kac para kazandik" derse ciroya karismasin.
            'fiyat-dip-boya' => [
                'tetik' => 'dip boya fiyat, dip boya fiyati, dip boya ne kadar, dip boya ucret, dip boya ucreti, dip boya kac para, dip boya kac tl, dip boya kac lira, dip boya kaca, dip boya fiyat bilgisi, dip boya ne kadara, dip boya ucret ne',
                'cevaplar' => [
                    'Dip boya fiyatı; saç uzunluğunuza, kullanılan boyaya ve saçınızın durumuna göre değişebilir efendim. Daha net bir fiyat bilgisi için salonumuzla iletişime geçebilir ya da bir randevu oluşturabilirsiniz.',
                    'Dip boya ücreti saç uzunluğu ve tercih edilen ürüne göre farklılık gösterir. Kesin fiyat için sizi salonumuza bekleriz; dilerseniz hemen bir randevu oluşturalım.',
                    'Dip boyada fiyat, saçınızın uzunluğuna ve renk/ürün tercihine göre belirlenir. En doğru bilgiyi salonumuzu arayarak ya da randevu alarak öğrenebilirsiniz.',
                    'Dip boya için kesin fiyatı, saçınızı görüp değerlendirdikten sonra netleştiriyoruz. İsterseniz bir randevu oluşturalım; geldiğinizde size net fiyatı sunalım.',
                ],
            ],

            // GENEL FIYAT: "fiyatlar ne kadar / fiyat listesi / ucretiniz" gibi belirli
            // hizmet gecmeyen sorular. Kural motorunda hicbir niyete uymuyor -> AI "fiyat"i
            // urun saniyordu. Bu kalip AI'dan ONCE devreye girer, kontrollu cevap verir
            // (uydurma fiyat YOK). Belirli hizmet+fiyat ("dip boya fiyati") daha uzun tetikle
            // fiyat-dip-boya'ya gider; urun/hizmet/kasa sorulari niyetCoz'da zaten yakalanir.
            'fiyat-genel' => [
                'tetik' => 'fiyat, fiyatlar, fiyati, fiyatlari, fiyatiniz, fiyatlariniz, fiyat listesi, fiyat bilgisi, ucret, ucretler, ucretiniz, ne kadar tutar, kaca, fiyat ne',
                'cevaplar' => [
                    'Fiyatlarımız yapılan işleme, saç ya da uygulama durumunuza ve kullanılan ürüne göre değişir. En doğru fiyat için salonumuzla iletişime geçebilir ya da bir randevu oluşturabilirsiniz.',
                    'Hizmetlerimizin fiyatı işlemin türüne ve kapsamına göre farklılık gösterir. Size en doğru fiyatı salonumuzda değerlendirip veririz; dilerseniz hemen bir randevu oluşturalım.',
                    'Fiyat, tercih ettiğiniz işleme ve saçınızın/uygulamanızın durumuna göre belirlenir. Güncel fiyat için bizi arayabilir veya randevu alarak yerinde net bilgi alabilirsiniz.',
                    'Kesin fiyatı işleme ve ihtiyacınıza göre belirlediğimiz için önceden tek bir rakam vermek doğru olmaz. Salonumuzu arayarak ya da randevu oluşturarak net fiyatı öğrenebilirsiniz.',
                    'Fiyatlarımız hizmete göre değiştiği için size en uygun ve net bilgiyi salonumuzda verebiliyoruz. İsterseniz bir randevu oluşturayım, geldiğinizde tüm detayları paylaşalım.',
                ],
            ],

            'komple-boya' => [
                'tetik' => 'komple boya, sac boyama, sacimi boyat, tum sac boya, sacima renk, boya yaptirmak, sac rengi degistir, komple renk, sac boyatmak, rengimi degistir',
                'cevaplar' => [
                    'Komple boya, saçınızın tamamının tek renkte boyanmasıdır. İstediğiniz tonu belirleyip kökten uca uyguluyoruz.',
                    'Saçınızın tümüne renk vermek istiyorsanız komple boya yapılır; mevcut renginizi tamamen değiştirebiliriz.',
                    'Komple boyada saç baştan sona boyanır. Açık ya da koyu, hayalinizdeki tonu birlikte seçeriz.',
                    'Tüm saçınıza yeni bir renk uyguluyoruz. Beyaz/kır saç kapatma da bu işlemle yapılır.',
                    'Renginizi baştan değiştirmek isterseniz komple boya idealdir; saç uçlarına kadar homojen bir renk elde edilir.',
                    'Önce ton belirlenir, sonra saç bölümlere ayrılıp tamamına boya uygulanır, bekletilip durulanır.',
                    'Saçınızın rengini tazelemek ya da tamamen yenilemek için komple boya yapabiliriz. Kırları da güzelce kapatır.',
                    'İstediğiniz rengi söyleyin; saçınızın tamamına uygulayıp bütünlüklü bir sonuç elde edelim.',
                    'Komple boya, dip boyadan farklı olarak sadece kökleri değil bütün saçı kapsar. Renk değişikliği isteyenler için uygundur.',
                    'Saç renginizi koyulaştırmak, açmak veya kırları kapatmak için tüm saça boya uyguluyoruz.',
                    'Rengini komple değiştirmek isteyenlere kökten uca tek ton boya uyguluyoruz; sonuç canlı ve eşit olur.',
                    'Saç sağlığını korumak için uygun ürün ve tonu birlikte seçeriz. Süre saç uzunluğunuza göre değişir.',
                ],
            ],

            // NOT: rofle/balyaj/balayage/ombre/sombre ARTIK AYRI kategoriler
            // (app/Support/HizmetBilgiIcerik). Eski lumped 'rofle-balyaj' kalibi EMEKLI
            // edildi (handle() icindeki $emekli listesi DB'den siler).

            // NOT: keratin ARTIK 'keratin-bakimi', brezilya fonu/duzlestirme ARTIK
            // 'brezilya-fonu' (ikisi de HizmetBilgiIcerik'te ayri zengin kategori).
            // Eski kisa built-in 'keratin' kalibi EMEKLI edildi (handle() $emekli listesi siler).

            'sac-kesim' => [
                'tetik' => 'sac kesimi, sac kestirmek, sac kes, kesim, uc alma, uclari al, model kesim, sacimi kestir, fon, fon cektirmek, fon cekmek, sac sekillendirme',
                'cevaplar' => [
                    'Saç kesiminde önce yüz şeklinize ve tercihinize uygun modeli konuşur, sonra istediğiniz boyda kesim yaparız.',
                    'Sadece uçları almak isterseniz de olur, model değişikliği isterseniz de; nasıl bir görünüm istediğinizi söylemeniz yeterli.',
                    'Fön, saçınızın yıkanıp şekillendirilmesidir; düz, dalgalı ya da hacimli, istediğiniz stilde yapabiliriz.',
                    'Kesimden önce saç yapınıza bakıp size en çok yakışacak modeli öneriyoruz; kararı birlikte veririz.',
                    'Uç alma, saç sağlığı için düzenli yaptırılması önerilen bir bakımdır; kırık uçları temizler.',
                    'Fön çektirmek isterseniz saçınızı istediğiniz forma sokuyoruz; özel bir gün için hacimli dalgalar da yapabiliriz.',
                    'Katlı, küt ya da modelli kesim... aklınızdaki stili anlatın, ustamız ona göre şekillendirsin.',
                    'Saçınızı kısaltmak mı yoksa sadece şekil vermek mi istiyorsunuz, buna göre kesim planlarız.',
                    'Fön ve şekillendirmede saç tipinize uygun ürünler kullanarak kalıcı ve sağlıklı bir sonuç hedefleriz.',
                    'Kesim sonrası bakım için birkaç önerimiz olur; böylece model daha uzun süre formunu korur.',
                ],
            ],

            // NOT: 'lazer' ARTIK ayri zengin kategori 'lazer-epilasyon' (HizmetBilgiIcerik).
            // Eski kisa built-in 'lazer' kalibi EMEKLI edildi (handle() $emekli listesi siler).

            // NOT: 'cilt-bakimi' ARTIK ayri zengin kategori (HizmetBilgiIcerik, ustune yazar).
            // Buradaki blok HYDRAFACIAL koprusune donusturuldu (yalniz hydrafacial tetikleri);
            // cilt-bakimi ile cakismaz. Hydrafacial (14. hizmet) gelince bu kopru ya ayni
            // 'hydrafacial' slug'iyla ustune yazilir ya da emekli edilir.
            'hydrafacial' => [
                'tetik' => 'hydrafacial, hydra facial, hidrafacial, hydra fesyıl, hydrafacial nedir, hydrafacial bakim',
                'cevaplar' => [
                    'Hydrafacial, cildi derinlemesine temizleyip nemlendiren, siyah noktaları ve ölü derileri arındırmaya yardımcı olan popüler bir cilt bakım uygulamasıdır.',
                    'Hydrafacial sonrası cilt genellikle daha pürüzsüz, nemli ve parlak görünür; özel günler öncesinde sıkça tercih edilir.',
                    'Hydrafacial birçok cilt tipine uygulanabilir; cildinizde hassasiyet varsa uygulama ve ürünler ona göre seçilir.',
                    'İşlemde cilt önce temizlenir, ardından arındırma ve nemlendirme aşamalarıyla cildin daha canlı görünmesi hedeflenir.',
                    'Hydrafacial cildin daha temiz ve nemli görünmesine yardımcı olabilir ancak sonuç cilt tipinize ve cildinizin durumuna göre değişebilir.',
                ],
            ],

            'kalici-oje' => [
                'tetik' => 'kalici oje, manikur, pedikur, oje, tirnak boyama, el bakimi, ayak bakimi, tirnak bakimi, kalici oje yaptirmak, tirnaklarimi yaptir',
                'cevaplar' => [
                    'Kalıcı oje, normal ojeden farklı olarak UV/LED ışıkta sertleşen ve haftalarca dökülmeden kalan bir uygulamadır.',
                    'Manikür, el ve tırnak bakımıdır; tırnak şekillendirilir, tırnak etleri düzenlenir ve isteğe göre oje uygulanır.',
                    'Pedikür ise ayak ve ayak tırnağı bakımıdır; tırnaklar bakımlı, ayaklar pürüzsüz hale gelir.',
                    'Uzun süre bozulmayan bir tırnak istiyorsanız kalıcı oje idealdir; parlaklığını haftalarca korur.',
                    'Kalıcı ojede istediğiniz rengi seçiyoruz, tırnağa uygulayıp ışıkta kurutuyoruz; çıkışta hemen işinize dönebilirsiniz.',
                    'Manikür-pedikürle ellerinizi ve ayaklarınızı bakımlı, temiz ve şık gösteriyoruz.',
                    'Kalıcı oje günlük hayatta çabuk bozulmadığı için pratiktir; renk ve modeli birlikte seçeriz.',
                    'Tırnak bakımında tırnak sağlığını koruyarak şekillendirme ve renklendirme yapıyoruz.',
                    'İstediğiniz renk, simli, mat ya da nail-art detayları... nasıl bir tırnak istediğinizi söylemeniz yeterli.',
                    'El ve ayak bakımınızı birlikte yaptırabilir, hem bakımlı hem şık bir görünüm elde edebilirsiniz.',
                ],
            ],

            'protez-tirnak' => [
                'tetik' => 'protez tirnak, tirnak uzatma, jel tirnak, tirnak protez, akrilik tirnak, tirnak dolgu, tirnak yaptirmak',
                'cevaplar' => [
                    'Protez tırnak, doğal tırnağın üzerine ek yapılarak uzun ve şekilli bir tırnak elde edilen uygulamadır.',
                    'Tırnaklarınız kısa ya da kırılıyorsa protez tırnakla istediğiniz boy ve formda bakımlı tırnaklara kavuşabilirsiniz.',
                    'İşlemde doğal tırnağınıza uygun malzemeyle uzatma yapılır, şekil verilir ve isteğe göre renk/model uygulanır.',
                    'Protez tırnakla düzgün, uzun ve dayanıklı bir görünüm elde edilir; günlük kullanımda oldukça sağlamdır.',
                    'İstediğiniz uzunluk ve şekli (badem, kare, oval) birlikte belirleyip ona göre uyguluyoruz.',
                    'Tırnaklarınızı kendiniz uzatmakta zorlanıyorsanız protez tırnak pratik ve şık bir çözümdür.',
                    'Belirli aralıklarla dolgu yaptırarak protez tırnağınızın görünümünü uzun süre koruyabilirsiniz.',
                    'Renk, simli ya da nail-art detaylı... nasıl bir tırnak hayal ediyorsanız ona göre tasarlıyoruz.',
                ],
            ],

            'kirpik' => [
                'tetik' => 'ipek kirpik, kirpik, kirpik lifting, kirpik takma, kirpik perma, kirpik dolgu, volume kirpik, kirpik yaptirmak, takma kirpik',
                'cevaplar' => [
                    'İpek kirpik, doğal kirpiklere tek tek ya da tutam halinde takılan ve gözlere belirgin, dolgun bir görünüm veren uygulamadır.',
                    'Kirpik lifting, kendi kirpiklerinizi kıvırıp yukarı kaldırarak daha uzun ve açık bir bakış elde etmenizi sağlar.',
                    'Kirpiklerinizin daha uzun ve dolgun görünmesini istiyorsanız ipek kirpik veya kirpik lifting güzel seçenekler.',
                    'İpek kirpikle maskara sürmeden bile belirgin, bakımlı gözler elde edersiniz; günlük hayatı kolaylaştırır.',
                    'Kirpik liftingde takma kirpik kullanılmaz; kendi kirpikleriniz şekillendirilip kaldırılır, çok doğal durur.',
                    'Ne kadar dolgun bir görünüm istediğinizi konuşup ona göre doğal ya da volüm kirpik uyguluyoruz.',
                    'Belirli aralıklarla dolgu yaptırarak ipek kirpiklerinizin dolgunluğunu koruyabilirsiniz.',
                    'Kirpik perması ve lifting, kendi kirpiğini uzun süre kıvrık göstermek isteyenler için idealdir.',
                    'Uygulama sırasında gözünüz kapalı şekilde rahatça uzanırsınız; işlem konforlu geçer.',
                    'Doğal mı yoksa iddialı mı bir bakış istiyorsunuz, buna göre kirpik yoğunluğunu birlikte seçeriz.',
                ],
            ],

            'kas' => [
                'tetik' => 'kas laminasyon, kas dizayn, kas alma, ipek kas, kas boyama, kas sekillendirme, microblading, kalici kas, kas yaptirmak, kaslarim',
                'cevaplar' => [
                    'Kaş dizaynı, yüz hatlarınıza uygun kaş şeklinin belirlenip şekillendirilmesidir.',
                    'Kaş laminasyonu, kaş kıllarını istenen yöne sabitleyip daha dolgun ve düzenli bir görünüm kazandıran işlemdir.',
                    'Kaşlarınız dağınık ya da seyrekse laminasyon ve dizaynla daha dolgun, bakımlı bir form elde edebiliriz.',
                    'Kaş boyamayla kaşlarınıza uygun ton verilir; seyrek bölgeler daha dolgun görünür.',
                    'Yüzünüze en çok yakışacak kaş formunu birlikte belirleyip ona göre şekillendiriyoruz.',
                    'Kaş laminasyonu kaşları taranmış gibi düzenli tutar ve günlük olarak daha derli toplu görünmenizi sağlar.',
                    'İstediğiniz görünüm doğal mı yoksa belirgin mi, buna göre kaş tasarımını planlıyoruz.',
                    'Kaş alma ve şekillendirmeyle yüz ifadenizi tazeleyip daha bakımlı bir görünüm kazandırıyoruz.',
                    'Kaş boyama ve laminasyon birlikte yapıldığında etki daha dolgun ve kalıcı olur.',
                    'Kaşlarınızın mevcut yapısını değerlendirip size en uygun işlemi öneriyoruz.',
                ],
            ],
        ];
    }
}
