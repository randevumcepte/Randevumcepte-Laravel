<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Salonun musterilerine gonderdigi resimli/tiklanabilir bildirim reklami.
 * Mevcut kampanya_yonetimi'nden AYRI, sade modul. Detay icin migration:
 * 2026_07_23_000001_create_bildirim_reklamlari_table.php
 */
class BildirimReklamlari extends Model
{
    protected $table = 'bildirim_reklamlari';

    protected $fillable = [
        'salon_id', 'baslik', 'mesaj', 'gorsel', 'tur',
        'kanal_push', 'kanal_inapp',
        'aksiyon_tipi', 'aksiyon_hedef',
        'kupon_indirim_tipi', 'kupon_deger', 'kupon_hizmet_id', 'kupon_baslik',
        'kupon_gecerlilik_gun', 'kupon_kisi_limit', 'kupon_toplam_adet', 'kupon_dagitilan',
        'hedef_kitle', 'hedef_kosul',
        'durum', 'yayin_baslangic', 'yayin_bitis', 'push_gonderildi',
    ];

    protected $casts = [
        'kanal_push'       => 'boolean',
        'kanal_inapp'      => 'boolean',
        'kupon_deger'      => 'float',
        'kupon_kisi_limit' => 'integer',
        'kupon_dagitilan'  => 'integer',
        'push_gonderildi'  => 'boolean',
        'yayin_baslangic'  => 'datetime',
        'yayin_bitis'      => 'datetime',
    ];

    /** Reklam su an yayinda/gecerli mi (tarih + durum + adet). */
    public function yayindaMi()
    {
        if ($this->durum !== 'aktif') return false;
        $now = now();
        if ($this->yayin_baslangic && $this->yayin_baslangic->isFuture()) return false;
        if ($this->yayin_bitis && $this->yayin_bitis->isPast()) return false;
        if ($this->kupon_toplam_adet !== null && $this->kupon_dagitilan >= $this->kupon_toplam_adet) return false;
        return true;
    }
}
