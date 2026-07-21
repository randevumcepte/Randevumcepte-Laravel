<?php

namespace App\Console\Commands;

use App\FormTaslaklari;
use App\Salonlar;
use Illuminate\Console\Command;

class CerenCevizFormKur extends Command
{
    protected $signature = 'cerenceviz:form-kur
        {--salon= : Salon ID (verilmezse salon_adi LIKE %ceren ceviz% ile bulunur)}
        {--dry-run : Sadece raporla, kayit yapma}
        {--yenile : Ayni isimli form varsa uzerine yaz (sorular_json guncelle)}';

    protected $description = 'Ceren Ceviz Guzellik Merkezi icin Cilt Bakimi Bilgilendirme ve Onam Formunu kurar. Idempotent, --yenile/--dry-run destekli. Sadece Ceren Ceviz salonuna yuklenir.';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $yenile = (bool) $this->option('yenile');

        if ($this->option('salon')) {
            $salonlar = Salonlar::where('id', (int) $this->option('salon'))->get();
        } else {
            $salonlar = Salonlar::where('salon_adi', 'like', '%ceren ceviz%')->get();
            if ($salonlar->isEmpty()) {
                // Bosluk/yazim farki ihtimaline karsi gevsek arama
                $salonlar = Salonlar::where('salon_adi', 'like', '%ceren%ceviz%')->get();
            }
        }

        if ($salonlar->isEmpty()) {
            $this->error('Ceren Ceviz salonu bulunamadi. --salon=ID ile deneyin.');
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
     * Ceren Ceviz'e yuklenecek formlarin listesi.
     * Yeni form gelince buraya bir eleman ekle.
     */
    private function formlar()
    {
        return [
            $this->ciltBakimiOnam(),
        ];
    }

    private function el($tip, $soru, $zorunlu = false)
    {
        return ['tip' => $tip, 'soru' => $soru, 'zorunlu' => $zorunlu];
    }

    private function ciltBakimiOnam()
    {
        $sorular = [
            $this->el('metin_blogu', "Bu form, merkezimizde uygulanacak işlemin detayları, olası riskleri ve işlem öncesi/sonrası dikkat etmeniz gereken hususlar hakkında sizi bilgilendirmek amacıyla hazırlanmıştır. Lütfen dikkatlice okuyunuz."),

            $this->el('bolum_basligi', 'KİŞİSEL BİLGİLER'),
            $this->el('metin_ops', 'T.C. Kimlik No'),

            $this->el('bolum_basligi', 'DİKKAT EDİLECEK HASTALIK VE SAĞLIK DURUMLARI'),
            $this->el('evet_hayir', 'Uygulama alanında aktif enfeksiyon, açık yara, aktif akne veya uçuk var mı?', true),
            $this->el('evet_hayir', 'Ciltte hassasiyet, roza (gül hastalığı), egzama veya sedef hastalığınız var mı?', true),
            $this->el('evet_hayir', 'Son 6 ay içinde Roaccutane vb. ağır akne ilacı kullandınız mı?', true),
            $this->el('evet_hayir', 'Herhangi bir kozmetik ürüne, aside veya bitkisel içeriğe alerjiniz var mı?', true),
            $this->el('evet_hayir', 'Son 2 hafta içinde botoks, dolgu, gençlik aşısı veya asitle soyma yaptırdınız mı?', true),
            $this->el('evet_hayir', 'Gebelik veya emzirme döneminde misiniz?', true),

            $this->el('bolum_basligi', 'İŞLEM SONRASI DİKKAT EDİLMESİ GEREKEN HUSUSLAR'),
            $this->el('madde_listesi', "İşlemden sonraki ilk 24 saat boyunca cilde makyaj ürünü veya fondöten uygulamayınız.\nİlk 24 saat yüzünüzü çok sıcak suyla yıkamayınız; havuz, sauna ve hamamdan uzak durunuz.\nCildinizi doğrudan güneş ışığından koruyunuz ve her gün en az 50+ SPF güneş koruyucu kullanınız.\nUzmanınızın önermediği agresif peeling, kese veya asitli ürünleri en az 1 hafta kullanmayınız.\nİşlem sonrası oluşan hafif pembelik ve hassasiyet olağandır; cildinizi ellemeyiniz veya koparmayınız."),

            $this->el('bolum_basligi', 'OLASI REAKSİYONLAR VE FERAGAT'),
            $this->el('metin_blogu', "Uygulamalarda, kişinin cilt yapısı, dolaşım sistemi, hormonal durumu ve biyolojik hassasiyetine bağlı olarak geçici kızarıklık, hassasiyet, morarma, ödem veya lokal ısı reaksiyonları gelişebilir. Alıcı, uzman tarafından önerilen seans ayarlarına, beslenme/su tüketimi tavsiyelerine ve bakım kurallarına uyacağını, merkezin ağır kusuru dışındaki bu tür olağan doku reaksiyonlarında satıcının hukuki bir sorumluluğu olmadığını kabul eder."),

            $this->el('onay_kutusu', 'Bu onam formunda yazan tüm maddeleri, olası riskleri, sağlık sorgulamalarını ve işlem öncesi/sonrası dikkat etmem gereken tüm kuralları sözlü olarak dinledim, yazılı olarak okudum ve kayıtsız şartsız kabul ettim.', true),

            $this->el('bolum_basligi', 'İMZALAR'),
            $this->el('not_kutusu', "ALICI / DANIŞAN dijital imzasını aşağıdaki imza alanına atar. İŞLEMİ UYGULAYAN UZMAN'ın imzası, bu formun basılı (PDF) çıktısı üzerinde ıslak imza olarak alınır."),
        ];

        return [
            'form_adi' => 'Cilt Bakımı Bilgilendirme ve Onam Formu',
            'aciklama' => "Sayın danışanımız, bu form Ceren Ceviz Güzellik Merkezi'nde uygulanacak cilt bakımı işlemi hakkında sizi bilgilendirmek ve onayınızı almak amacıyla hazırlanmıştır. Lütfen tüm bölümleri dikkatle okuyunuz.",
            'sorular'  => $sorular,
        ];
    }
}
