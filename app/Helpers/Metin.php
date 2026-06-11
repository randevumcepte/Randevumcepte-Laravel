<?php

namespace App\Helpers;

/**
 * Türkçe metin yardımcıları.
 *
 * basHarfBuyut(): Her kelimenin ilk harfini büyütür (Türkçe uyumlu).
 *   - "ceren ceviz"  -> "Ceren Ceviz"
 *   - "ali  veli"    -> "Ali  Veli"   (fazla boşluklar korunur)
 *   - Sadece baş harfe dokunur; kelimenin geri kalanını BOZMAZ; kısaltmalar
 *     (VIP, SPA) ve CAPS yazım korunur.
 *   - Türkçe locale: i -> İ, ı -> I doğru çalışır.
 *   - Bu davranış frontend-scripts.blade.php'deki istemci tarafı ile aynıdır;
 *     buradaki sürüm API/JS kapalı durumlar için sunucu tarafı güvencedir.
 */
class Metin
{
    public static function basHarfBuyut($str)
    {
        // null / dizi / boş gibi durumlara dokunma.
        if (!is_string($str) || $str === '') {
            return $str;
        }

        return preg_replace_callback('/(^|\s)(\S)/u', function ($m) {
            return $m[1] . self::trBuyukHarf($m[2]);
        }, $str);
    }

    /**
     * Tek bir karakteri Türkçe kurallarına göre büyütür.
     * Yalnızca i/ı locale'e özeldir; diğer Türkçe harfler (ş,ç,ö,ü,ğ) için
     * mb_strtoupper zaten doğru sonucu verir.
     */
    private static function trBuyukHarf($ch)
    {
        if ($ch === 'i') return 'İ';
        if ($ch === 'ı') return 'I';
        return mb_strtoupper($ch, 'UTF-8');
    }
}
