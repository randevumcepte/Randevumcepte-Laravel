<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarkifelekSistemi extends Model
{

    protected $table = 'carkifelek_sistemi';

    /**
     * Salon sahibi hiç kural girmediğinde müşteriye gösterilecek,
     * tüm salonları koruyan varsayılan kullanım kuralları.
     *
     * NOT (hukuki kurgu): Metin, faaliyeti Milli Piyango İdaresi iznine tabi bir
     * "çekiliş / şans oyunu" olmaktan çıkarıp "sadakat / hediye kampanyası" zeminine
     * oturtacak şekilde yazılmıştır. Backend'de ödül salon tarafından önceden
     * belirlenir ve herkese aynıdır (şans/talih unsuru yoktur); bu yüzden "garantili
     * hediye" muafiyetine dayanır. "Çekiliş/şans/talih/kazanmak" gibi ibarelerden
     * kaçınılmıştır. Bu bir hukuki mütalaa değildir.
     */
    const VARSAYILAN_KURALLAR =
        "• Bu uygulama bir piyango, çekiliş veya şans oyunu DEĞİLDİR; salonumuzun müşterilerine yönelik bir sadakat ve hediye kampanyasıdır. Katılım ücretsizdir ve isteğe bağlıdır.\n" .
        "• Verilen hediye (puan, indirim veya armağan) şans/talih unsuruna dayanmaz; kampanya koşullarını sağlayan müşterilerimize sunulan bir teşekkür hediyesidir.\n" .
        "• Hediyeler yalnızca salonumuzun kendi hizmet/ürünlerinde geçerlidir; nakit değildir, nakde çevrilemez, başkasına devredilemez ve satılamaz.\n" .
        "• Her müşteri çarkı günde en fazla 1 kez çevirebilir; kazanılan hediye yalnızca salonumuzdan alınan hizmet/ürün sırasında kullanılabilir.\n" .
        "• Hediye/indirimler yalnızca belirtilen geçerlilik süresi içinde ve tek seferde kullanılır; süresi geçenler geçersiz olur.\n" .
        "• Hediye/indirimler başka kampanya, indirim veya promosyonlarla birleştirilemez.\n" .
        "• Yararlanma, randevu/hizmet sırasında ilgili hediye kodunun salonumuza iletilmesiyle yapılır.\n" .
        "• Teknik hata, sistem arızası veya kötüye kullanım tespit edilen işlemler geçersiz sayılır.\n" .
        "• Salonumuz; uygunluk, stok veya işletme koşullarına göre hediyeyi eşdeğeriyle değiştirme ya da kampanyayı durdurma hakkını saklı tutar.\n" .
        "• Kampanya koşulları önceden haber verilmeksizin güncellenebilir.";

    protected $fillable = [
        'aktifmi',
        'kullanim_kurallari',
        'cevirme_araligi_gun',
        'kupon_cark_gecerlilik_gun',
        'kupon_puan_gecerlilik_gun',
        'created_at',
        'updated_at',
        'salon_id',
    ];

    public function dilimler()
    {
        return $this->hasMany(CarkifelekDilimleri::class, 'cark_id');
    }
}
