<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarkifelekSistemi extends Model
{

    protected $table = 'carkifelek_sistemi';

    /**
     * Salon sahibi hiç kural girmediğinde müşteriye gösterilecek,
     * tüm salonları koruyan varsayılan kullanım kuralları.
     */
    const VARSAYILAN_KURALLAR =
        "• Çarkıfelek, salonumuza özel bir hediye/sadakat etkinliğidir; katılım tamamen isteğe bağlıdır.\n" .
        "• Kazanılan ödüller (puan, indirim, kupon) yalnızca bu salonda geçerlidir; nakde çevrilemez, başkasına devredilemez ve satılamaz.\n" .
        "• Çevirme hakkı onaylı randevuya bağlıdır ve her müşteri günde en fazla 1 kez çevirebilir.\n" .
        "• Kuponlar yalnızca geçerlilik süresi (kupon üzerindeki tarih) içinde ve tek seferde kullanılır; süresi geçen ödüller iptal sayılır.\n" .
        "• Ödüller başka kampanya, indirim veya promosyonlarla birleştirilemez.\n" .
        "• Ödülün kullanımı, randevu/hizmet sırasında kupon kodunun salona iletilmesiyle yapılır.\n" .
        "• Teknik hata, sistem arızası veya kötüye kullanım tespit edilen çevirme ve ödüller geçersiz sayılır.\n" .
        "• Salonumuz; uygunluk, stok veya işletme koşullarına göre ödülü eşdeğeriyle değiştirme ya da etkinliği durdurma hakkını saklı tutar.\n" .
        "• Çarkıfelek kuralları ve ödülleri önceden haber verilmeksizin güncellenebilir.";

    protected $fillable = [
        'aktifmi',
        'kullanim_kurallari',
        'created_at',
        'updated_at',
        'salon_id',
    ];

    public function dilimler()
    {
        return $this->hasMany(CarkifelekDilimleri::class, 'cark_id');
    }
}
