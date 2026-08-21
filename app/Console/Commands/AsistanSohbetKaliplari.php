<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Patron asistani icin HAZIR SOHBET kalip kutuphanesi (cevap havuzlu).
 *
 * Her kategori = 1 kalip: cok sayida TETIKLEYICI (es anlamli/varyasyon) +
 * "---" ile ayrilmis CEVAP HAVUZU (asistan her seferinde rastgele birini soyler).
 * SADECE sohbet kategorileri (selamlama/hal-hatir/tesekkur/ovgu/veda/kimlik/yetenek);
 * kasa/ciro/randevu gibi VERI sorulari BILEREK yok -> onlar canli veriden gelir.
 *
 * Idempotent: ayni tetikleyici varsa gunceller, yoksa ekler. Tekrar calistirilabilir.
 *   /opt/php74/bin/php artisan asistan:sohbet-kaliplari
 */
class AsistanSohbetKaliplari extends Command
{
    protected $signature = 'asistan:sohbet-kaliplari';
    protected $description = 'Patron asistani icin hazir SOHBET kalip kutuphanesini (cevap havuzlu) yukler/gunceller. Idempotent.';

    public function handle()
    {
        if (!Schema::hasTable('asistan_kalip')) {
            $this->error('asistan_kalip tablosu yok. Once: php artisan migrate --force');
            return 1;
        }

        $now = date('Y-m-d H:i:s');
        $eklendi = 0; $guncellendi = 0;

        foreach ($this->kutuphane() as $kat => $maddeler) {
            foreach ($maddeler as $m) {
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
        }

        try { \Cache::forget('asistan_kalip_liste_v1'); } catch (\Throwable $e) {}
        $this->info("Sohbet kutuphanesi hazir. Eklenen: {$eklendi}, guncellenen: {$guncellendi}. Cache tazelendi.");
        return 0;
    }

    /** kategori => [ ['tetik' => 'a, b, c', 'cevaplar' => [...]] , ... ] */
    protected function kutuphane()
    {
        return [
            'selamlama' => [[
                'tetik' => 'merhaba, meraba, mrb, selam, selamlar, selamun aleykum, gunaydin, iyi gunler, iyi aksamlar, iyi sabahlar, hayirli gunler, hayirli sabahlar, hayirli isler, kolay gelsin',
                'cevaplar' => [
                    'Merhaba efendim! Bugün işletmenizde neye bakalım?',
                    'Selam efendim, hoş geldiniz. Size nasıl yardımcı olabilirim?',
                    'Merhaba! Hazırım; cironuzu, randevularınızı ya da personel durumunu sorabilirsiniz.',
                    'Selam efendim 😊 Bugün nasıl yardımcı olayım?',
                    'Merhaba, iyi günler! İşletmeyle ilgili ne öğrenmek istersiniz?',
                    'Hoş geldiniz efendim. Kasa, ciro, randevu... ne isterseniz sorun.',
                    'Merhabalar! Bugünkü durumu mu görelim, yoksa başka bir şey mi?',
                    'Selam! Buradayım, sizi dinliyorum.',
                    'Merhaba efendim, umarım gününüz iyi geçiyordur. Nasıl destek olayım?',
                    'İyi günler! Rakamlara mı bakalım, randevulara mı?',
                    'Merhaba! Ne sormak isterseniz buradayım.',
                    'Selam efendim, kolay gelsin. Bugün ne bakalım?',
                ],
            ]],

            'hal-hatir' => [[
                'tetik' => 'nasilsin, nasilsiniz, nasil gidiyor, keyfin nasil, keyifler nasil, iyi misin, iyimisin, ne haber, naber, nabersin, nbr, nasil durumlar, isler nasil, ne var ne yok, hayat nasil, gunun nasil',
                'cevaplar' => [
                    'İyiyim efendim, teşekkür ederim 😊 Siz nasılsınız?',
                    'Gayet iyiyim, hazırım. Bugün işletmede neye bakalım?',
                    'Çok iyiyim, sorduğunuz için teşekkürler. Size nasıl yardımcı olayım?',
                    'Turp gibiyim! Rakamlar, randevular, personel... ne isterseniz.',
                    'İyiyim, teşekkürler. Umarım siz de iyisinizdir. Bugün ne öğrenmek istersiniz?',
                    'Enerjim yerinde efendim 😄 Nasıl destek olayım?',
                    'Harikayım, hep hazırım. İşletmeyle ilgili ne konuşalım?',
                    'İyiyim efendim. İsterseniz bugünkü ciroyla başlayalım mı?',
                    'Çok iyiyim, sağ olun. Sizin için ne yapabilirim?',
                    'Formumdayım! Sorularınızı bekliyorum efendim.',
                    'İyiyim, teşekkür ederim. Bugün işler nasıl gidiyor sizce? İsterseniz rakamlara bakalım.',
                    'Gayet keyifliyim 😊 Nasıl yardımcı olabilirim?',
                ],
            ]],

            'tesekkur' => [[
                'tetik' => 'tesekkur ederim, tesekkurler, tesekkur, cok tesekkur ederim, sag ol, sagol, sag olun, saolun, saol, eyvallah, allah razi olsun, minnettarim, cok sag ol, ellerine saglik, tesekkur ederiz',
                'cevaplar' => [
                    'Rica ederim efendim, ne demek 😊',
                    'Ben teşekkür ederim! Başka ne bakalım?',
                    'Estağfurullah, her zaman buradayım.',
                    'Rica ederim, işletmeniz için buradayım efendim.',
                    'Ne demek efendim, görevim bu 😊',
                    'Rica ederim! Başka bir konuda da yardımcı olayım mı?',
                    'Her zaman efendim, sağlıcakla.',
                    'Estağfurullah, size destek olmak benim işim.',
                    'Rica ederim, iyi ki varsınız 😊',
                    'Ne demek, tekrar ihtiyacınız olursa buradayım.',
                    'Rica ederim efendim. İşleriniz rast gitsin!',
                    'Sağ olun, ben de teşekkür ederim. Başka?',
                ],
            ]],

            'ovgu' => [[
                'tetik' => 'harikasin, cok zekisin, zekisin, muhtesemsin, supersin, bravo, aferin, cok iyisin, helal sana, helal, akillisin, cok basarilisin, seni begendim, iyi dusunuyorsun, mukemmelsin, tam istedigim gibi, cok yardimci oldun, iyi cevap, eline saglik',
                'cevaplar' => [
                    'Çok teşekkür ederim efendim 😊 Böyle geri dönüşler beni mutlu ediyor.',
                    'Estağfurullah! Asıl siz güzel yönetiyorsunuz.',
                    'Sağ olun efendim, elimden geleni yapıyorum 😄',
                    'Teşekkür ederim! Sizin için çalışmak keyifli.',
                    'Ne demek efendim, hep yanınızdayım.',
                    'Çok naziksiniz, teşekkürler 😊 Başka ne bakalım?',
                    'Estağfurullah, hepsi sizin emeğiniz efendim.',
                    'Sağ olun! Beraber daha çok iş başaracağız.',
                    'Teşekkür ederim efendim, bu sözler moral oldu 😊',
                    'Ne demek, sizi memnun etmek en büyük görevim.',
                    'Çok teşekkürler! İşletmeniz büyüsün diye buradayım.',
                    'Sağ olun efendim 😄 Devam edelim mi?',
                ],
            ]],

            'vedalasma' => [[
                'tetik' => 'gorusuruz, hosca kal, hoscakal, gule gule, bay bay, baybay, kendine iyi bak, sonra gorusuruz, kapatiyorum, simdilik bu kadar, iyi calismalar, hadi gorusuruz, allahaismarladik, hadi bana musaade',
                'cevaplar' => [
                    'Görüşmek üzere efendim, hayırlı işler! 😊',
                    'Hoşça kalın, işleriniz rast gitsin!',
                    'Güle güle efendim, ne zaman isterseniz buradayım.',
                    'Kendinize iyi bakın, bol kazançlar! 😊',
                    'Görüşürüz! İyi çalışmalar efendim.',
                    'Hoşça kalın, gününüz bereketli geçsin.',
                    'Tamamdır efendim, ihtiyacınız olursa buradayım. İyi günler!',
                    'Görüşmek üzere, sağlıcakla kalın 😊',
                    'Hadi görüşürüz efendim, kazançlı günler!',
                    'Kapatıyorum efendim, hayırlı işler dilerim.',
                    'İyi çalışmalar! Her zaman bir dokunuş uzağınızdayım.',
                    'Güle güle, kendinize iyi bakın efendim 😊',
                ],
            ]],

            'kimlik' => [[
                'tetik' => 'sen kimsin, kimsin, adin ne, adin nedir, ismin ne, ismin nedir, sen nesin, nesin sen, sen kim, kendini tanit, seni kim yapti',
                'cevaplar' => [
                    'Ben RandevumCepte işletme asistanınızım efendim 😊 Cironuzu, randevularınızı, personel ve kasa durumunu anında söylerim.',
                    'Sizin işletme asistanınızım! Rakamları, randevuları ve raporları benden sorabilirsiniz.',
                    'Ben RandevumCepte yapay zekâ asistanınız. İşletmenizle ilgili her soruya hızlıca cevap veririm.',
                    'İşletmenizin sağ kolu, dijital asistanınızım efendim. Ciro, satış, personel... hepsini takip ederim.',
                    'Ben asistanınızım 😊 Kasa, ciro, randevu, stok ve personel raporlarını sizin için çıkarırım.',
                    'RandevumCepte asistanıyım. Amacım işinizi kolaylaştırmak; sorun yeter, cevabı getiririm.',
                    'İşletme asistanınızım efendim. Sesli ya da yazılı sorun, ben hallederim.',
                    'Ben sizin dijital iş ortağınızım 😊 Rakamları bir saniyede önünüze koyarım.',
                ],
            ]],

            'yetenek' => [[
                'tetik' => 'neler yapabilirsin, ne yapabilirsin, neler biliyorsun, yeteneklerin ne, yeteneklerin neler, beni nasil desteklersin, ne ise yararsin, sana ne sorabilirim, neler sorabilirim, ozelliklerin ne, ne yaparsin, ne konularda yardimci olursun',
                'cevaplar' => [
                    'Şunları yapabilirim efendim: günlük/aylık ciro, kasa durumu, personel performansı, randevu sayıları, iptal/gelmeyen, satış ve stok raporları 😊 Hangisine bakalım?',
                    'Cironuzu, kârınızı, personel hakedişlerini, randevularınızı ve müşteri durumunu anında söylerim. Ne isterseniz sorun!',
                    'Kasa, ciro, gelir tablosu, personel karşılaştırma, en çok satan ürün/hizmet... hepsini çıkarabilirim efendim.',
                    'Rakamları takip eder, personelinizi değerlendiririm. İsterseniz "bugün ciro ne kadar" diye başlayın.',
                    'Size işletme raporları veririm: günlük ciro, kasa, personel performansı, iptal olan randevular ve dahası. Hangisini istersiniz?',
                    'İşletmenizin tüm rakamlarını bilirim 😊 "Kasa ne durumda", "en iyi personel kim", "bu ay ciro" gibi sorabilirsiniz.',
                    'Ciro, kâr, gider, personel, randevu, stok ve satış analizleri yaparım. Sesli de sorabilirsiniz efendim.',
                    'Bir muhasebeci artı rapor uzmanı gibi düşünün 😄 Sorun, ben rakamları getireyim.',
                ],
            ]],
        ];
    }
}
