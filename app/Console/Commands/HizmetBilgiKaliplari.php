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
    protected $signature = 'asistan:hizmet-kaliplari';
    protected $description = 'Kuafor/guzellik hizmet BILGILENDIRME kaliplarini (cevap havuzlu) yukler/gunceller. Idempotent.';

    public function handle()
    {
        if (!Schema::hasTable('asistan_kalip')) {
            $this->error('asistan_kalip tablosu yok. Once: php artisan migrate --force');
            return 1;
        }

        $now = date('Y-m-d H:i:s');
        $eklendi = 0; $guncellendi = 0;

        foreach ($this->kutuphane() as $kat => $m) {
            $tet = trim($m['tetik']);
            $cev = implode("\n---\n", $m['cevaplar']);
            $row = DB::table('asistan_kalip')->where('tetikleyiciler', $tet)->first();
            if ($row) {
                DB::table('asistan_kalip')->where('id', $row->id)->update([
                    'cevap' => $cev, 'kategori' => $kat, 'aktif' => 1, 'updated_at' => $now,
                ]);
                $guncellendi++;
            } else {
                DB::table('asistan_kalip')->insert([
                    'tetikleyiciler' => $tet, 'cevap' => $cev, 'kategori' => $kat,
                    'aktif' => 1, 'kullanim_sayisi' => 0, 'created_at' => $now, 'updated_at' => $now,
                ]);
                $eklendi++;
            }
        }

        try { \Cache::forget('asistan_kalip_liste_v1'); } catch (\Throwable $e) {}
        $this->info("Hizmet bilgilendirme kutuphanesi hazir. Eklenen: {$eklendi}, guncellenen: {$guncellendi}. Cache tazelendi.");
        return 0;
    }

    /** kategori => ['tetik' => 'a, b, c', 'cevaplar' => [...]] */
    protected function kutuphane()
    {
        return [
            'dip-boya' => [
                'tetik' => 'dip boya, dip boyama, dip boyasi, dipleri geldi, dipler cikti, dip kapatma, sac dibi boya, kokleri geldi, kokler geldi, diplerim uzadi, dipleri boyat, dip rengi, sac dipleri',
                'cevaplar' => [
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

            'rofle-balyaj' => [
                'tetik' => 'rofle, rofle yaptirmak, roflelerim, balyaj, balayage, ombre, sombre, sac acma, acma islemi, ince tel, renkli tutam, gunes vurmus, tonlama, sac tonlama',
                'cevaplar' => [
                    'Röfle, saçın belirli tutamlarının açılarak açık renk verilmesidir; saça boyutlu, ışıltılı bir görünüm katar.',
                    'Balyaj (balayage), fırça tekniğiyle saç uçlarına doğru doğal bir açılma verilen boyamadır; güneş vurmuş etkisi yaratır.',
                    'Ombre, dipten uca koyudan açığa geçişli bir renklendirmedir. Sombre ise bunun daha yumuşak, doğal tonudur.',
                    'Röfle/balyaj ile saçınıza boyut ve hareket kazandırıyoruz; tamamını açmadan ışıltılı tutamlar oluşturuyoruz.',
                    'Doğal bir açılma ve derinlik istiyorsanız balyaj çok tercih edilir; dipler daha koyu, uçlar açık olur.',
                    'Röflede ince tutamlar folyoyla açılır; böylece saçta ton ton bir canlılık elde edilir.',
                    'Balyaj serbest teknikle uygulandığı için sonuç çok doğal olur ve uzadıkça belirgin dip çizgisi bırakmaz.',
                    'Güneşte açılmış gibi doğal bir görünüm istiyorsanız balayage ideal; uçlara doğru yumuşak bir açılma veriyoruz.',
                    'Ombre’de dip koyu bırakılıp uçlara doğru belirgin bir açılma yapılır; iddialı bir görünüm sever misiniz, birlikte karar veririz.',
                    'Röfle ile hem kır kapatma hem canlılık sağlanabilir; ne kadar açık istediğinizi konuşarak belirleriz.',
                    'Açma işleminden sonra genelde tonlama yapılır; böylece istenmeyen sarılık/turuncu giderilir ve renk oturur.',
                    'Balyaj/ombre saç boyunuza ve mevcut renginize göre planlanır; doğal mı iddialı mı istediğinizi söylemeniz yeterli.',
                ],
            ],

            'keratin' => [
                'tetik' => 'keratin, keratin bakimi, brezilya fonu, sac duzlestirme, duzlestirme, sacim kabariyor, kabaran sac, elektrikleniyor, elektriklenme, sac pruzsuz, ipeksi sac',
                'cevaplar' => [
                    'Keratin bakımı, saça keratin proteini kazandırarak elektriklenmeyi azaltan ve saçı pürüzsüz, parlak gösteren bir bakımdır.',
                    'Brezilya fönü, kabaran ve elektriklenen saçları düzleştirip yönetilebilir hale getiren bir bakım işlemidir.',
                    'Saçınız çok kabarıyor veya elektrikleniyorsa keratin bakımıyla daha düz, ipeksi ve bakımlı bir görünüm elde edebiliriz.',
                    'Keratin, saç tellerindeki boşlukları doldurarak yıpranmış saçları onarır ve parlaklık verir.',
                    'Brezilya fönünden sonra saçınız günlük bakımda çok daha kolay şekil alır; fön süreniz belirgin şekilde kısalır.',
                    'İşlemde saça keratin içerikli ürün uygulanır, ardından ısıyla sabitlenir; sonuç birkaç ay kalıcı olur.',
                    'Sık fön/maşa kullanıp yıpranan saçlar için keratin bakımı hem onarır hem düzleştirir.',
                    'Kabarıklığı almak ve saçı pürüzsüzleştirmek isteyenler için brezilya fönü çok idealdir.',
                    'Keratin saçı düzleştirirken doğal görünümü korur; taş gibi düz değil, akışkan ve parlak bir sonuç verir.',
                    'İşlem saç durumunuza göre planlanır; ne kadar düzlük istediğinizi konuşarak ürünü ona göre seçeriz.',
                    'Bakım sonrası kalıcılık için ilk günlerde dikkat edilmesi gereken birkaç nokta var, onları da anlatırız.',
                    'Elektriklenme ve kabarmadan şikâyetçiyseniz keratin/brezilya fönü günlük saç rutininizi ciddi şekilde kolaylaştırır.',
                ],
            ],

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

            'lazer' => [
                'tetik' => 'lazer epilasyon, lazer, epilasyon, tuy aldirmak, istenmeyen tuy, lazerle tuy, kalici epilasyon, tuy azaltma, bolgesel lazer, agda yerine lazer',
                'cevaplar' => [
                    'Lazer epilasyon, istenmeyen tüyleri kökten azaltmak için lazer ışını kullanılan bir uygulamadır; seanslar halinde yapılır.',
                    'Lazerde tüy kökü ısıyla etkisizleştirilir; birkaç seans sonunda tüyler belirgin şekilde azalır ve incelir.',
                    'İstenmeyen tüylerden kalıcı olarak kurtulmak isteyenler için lazer epilasyon uygulanır. Bölgeye göre seans sayısı değişir.',
                    'İşlem seanslar halinde yapılır çünkü tüyler farklı büyüme döngülerindedir; düzenli seanslarla en iyi sonuç alınır.',
                    'Lazer yüz, koltuk altı, bacak, bikini gibi birçok bölgeye uygulanabilir; hangi bölgeyi istediğinizi konuşarak planlarız.',
                    'Her seansta o an aktif büyüme evresindeki tüyler etkilenir, bu yüzden seanslar aralıklı yapılır ve tüyler giderek azalır.',
                    'Uygulama genelde rahat tolere edilir; cilt tipiniz ve tüy yapınıza göre uygun ayarlarla yapılır.',
                    'Ağda/tıraşla uğraşmaktan yorulduysanız lazer epilasyon uzun vadede çok daha pratik ve kalıcı bir çözümdür.',
                    'Seans öncesi bölgenin tıraşlı olması ve güneşten korunması önemlidir; detayları uygulamadan önce anlatırız.',
                    'Bölgeye ve tüy yoğunluğuna göre ortalama seans sayısını değerlendirip size özel bir plan çıkarıyoruz.',
                    'Lazer tüyleri inceltip seyreltir; birkaç seans sonra tıraş ihtiyacınız ciddi şekilde azalır.',
                    'Cilt renginiz ve tüy renginize göre uygun lazer tipi belirlenir; en doğru yöntemi uzmanımız seçer.',
                    'Kalıcı tüy azaltma için düzenli seans şarttır; aralıkları doğru takip edince sonuç çok daha başarılı olur.',
                    'Uygulama sonrası cildinizi güneşten korumanız ve nemlendirmeniz önerilir; bakım önerilerini de paylaşırız.',
                ],
            ],

            'cilt-bakimi' => [
                'tetik' => 'cilt bakimi, hydrafacial, hydra facial, yuz bakimi, cilt temizligi, siyah nokta, cilt yenileme, cilt nemlendirme, gozenek, cilt pruzsuz',
                'cevaplar' => [
                    'Cilt bakımı, cildin temizlenmesi, nemlendirilmesi ve canlandırılması için yapılan bir uygulamadır; cilt tipinize göre planlanır.',
                    'Hydrafacial, cildi derinlemesine temizleyip nemlendiren, siyah noktaları ve ölü derileri arındıran popüler bir bakımdır.',
                    'Cildinizde siyah nokta, mat görünüm veya kuruluk varsa cilt bakımıyla temiz, nemli ve ışıltılı bir görünüm elde edebiliriz.',
                    'İşlemde cilt önce temizlenir, ardından cilt tipinize uygun bakım adımları uygulanır ve nemlendirmeyle tamamlanır.',
                    'Hydrafacial sonrası cilt anında daha pürüzsüz ve parlak görünür; özel günler öncesi çok tercih edilir.',
                    'Cilt bakımıyla gözenekler arındırılır, cilt tonu düzenlenir ve cilt yenilenmeye teşvik edilir.',
                    'Cilt tipinizi (kuru, yağlı, karma, hassas) değerlendirip bakımı ona göre kişiselleştiriyoruz.',
                    'Düzenli cilt bakımı cildin daha sağlıklı, dengeli ve genç görünmesine yardımcı olur.',
                    'Leke, mat görünüm veya yorgun bir cilt için canlandırıcı bakımlar uyguluyoruz; ihtiyacınıza göre programlarız.',
                    'Bakım sırasında cildi yormadan, nazik ürünlerle temizlik ve nemlendirme yapıyoruz.',
                    'Cildinizin ihtiyacını birlikte belirleyip uygun bakımı öneriyoruz.',
                    'Hydrafacial gibi bakımlar birçok cilt tipine uygundur; hassasiyetiniz varsa ona göre ürün seçeriz.',
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
