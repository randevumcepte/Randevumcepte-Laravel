<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Salonlar;
use App\SalonSMSAyarlari;
use App\IsletmeYetkilileri;
use App\Personeller;
use App\Senetler;
use App\SenetVadeleri;
use App\Ajanda;
use App\Bildirimler;
use App\BildirimKimlikleri;
use App\SatisOrtakligiModel\SatisOrtaklari;
use App\SatisOrtakligiModel\Musteri_Formlari;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SMSGonder extends Command
{
    protected $signature = 'sms:gonder';
    protected $description = 'Ajanda not, vadesi geçmiş senet SMS ve satış ortaklığı bildirim hatırlatmaları. (Randevu/Doğum günü/Alacak SMS\'leri ayrı komutlarda.)';

    public function handle()
    {
        $controller = app()->make(Controller::class);

        // Ajanda notları hatırlatma
        $notlar = Ajanda::where('ajanda_tarih', date('Y-m-d'))->get();
        foreach ($notlar as $not) {
            if ($not->ajanda_hatirlatma != 1) {
                continue;
            }
            $simdi = date('d.m.Y H:i');
            $tetikZaman = date('d.m.Y H:i', strtotime('-' . $not->ajanda_hatirlatma_saat . ' hours', strtotime($not->ajanda_tarih . ' ' . $not->ajanda_saat)));
            if ($simdi != $tetikZaman) {
                continue;
            }

            $not->aciklama = $tetikZaman;
            $not->save();

            $notmesaj = 'Sayın ' . $not->personel->personel_adi . '. Bugün ' . $not->ajanda_saat . ' saatinde ' . $not->ajanda_baslik . ' adında notunuz vardır hatırlatmak isteriz.';
            $mesaj = [[
                'to' => $not->personel->cep_telefon,
                'message' => $notmesaj,
            ]];

            if (SalonSMSAyarlari::where('salon_id', $not->salon_id)->where('ayar_id', 17)->value('personel')) {
                $controller->sms_gonder($not->salon_id, $mesaj);
            }
            self::bildirimekle(
                $not->salon_id,
                $notmesaj,
                '#not-' . $not->id,
                $not->ajanda_olusturan,
                null,
                IsletmeYetkilileri::where('id', $not->personel->yetkili_id)->value('profil_resim'),
                null,
                null
            );
        }

        // Vadesi geçmiş ödenmemiş senet vadeleri
        $senet_vadeleri = SenetVadeleri::where('vade_tarih', '<', date('Y-m-d'))->get();
        foreach ($senet_vadeleri as $vade) {
            if ($vade->odendi) {
                continue;
            }
            $senet = Senetler::where('id', $vade->senet_id)->first();
            if (!$senet || !$senet->musteri || !$senet->salon) {
                continue;
            }

            $mesaj = 'Sayın ' . $senet->musteri->name . ' ' . date('d.m.Y', strtotime($vade->vade_tarih)) . ' vade tarihli ' . money_format('%i', $vade->tutar) . ' ₺ tutarındaki senedinizin ödenmemiş olduğunu hatırlatmak isteriz.';
            $headers = [
                'Authorization: Key ' . $senet->salon->sms_apikey,
                'Content-Type: application/json',
                'Accept: application/json',
            ];
            $postData = json_encode([
                'originator' => $senet->salon->sms_baslik,
                'message' => $mesaj,
                'to' => $senet->musteri->cep_telefon,
                'encoding' => 'auto',
            ]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://api.efetech.net.tr/v2/sms/basic');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_exec($ch);
            curl_close($ch);
        }

        // Satış ortaklığı bildirimleri (demo süresi / lisans bitişi)
        $satisortaklari = SatisOrtaklari::where('pasif_ortak', 0)->get();
        foreach ($satisortaklari as $satisortagi) {
            $demosuolanlar = self::satis_ortakligi_icin_filtreli_musteri_listesi('demolar', $satisortagi->id, '');
            $aktifsuresibitenler = self::satis_ortakligi_icin_filtreli_musteri_listesi('aktifsuresibitenler', '', $satisortagi->id);

            foreach ($demosuolanlar as $demosuolan) {
                if ($demosuolan['kalan_sure'] <= 3 && date('H:i') == '11:00') {
                    Log::info('Satış ortağı demo bitiş hatırlatması: ' . $satisortagi->ad_soyad . ' / ' . $demosuolan['salon_adi']);
                }
            }
            foreach ($aktifsuresibitenler as $aktifsuresibiten) {
                if ($aktifsuresibiten['kalan_sure'] <= 15 && date('H:i') == '11:00') {
                    $bildirimmesaj = 'Sayın ' . $satisortagi->ad_soyad . '. ' . $aktifsuresibiten['salon_adi'] . ' hesabının lisans süresinin bitmesine ' . $aktifsuresibiten['kalan_sure'] . ' gün kalmış olduğunu hatırlatmak isteriz.';
                    self::bildirimekle(null, $bildirimmesaj, 'https://app.randevumcepte.com.tr/satisortakligi/aktif-musteriler', null, null, null, null, $satisortagi->id);
                }
            }
        }
    }

    public function satis_ortakligi_icin_filtreli_musteri_listesi($querydurum, $satis_ortagi, $satan_satis_ortagi)
    {
        $query = Musteri_Formlari::query();
        if ($satis_ortagi != '') {
            $query->where('satis_ortagi_id', $satis_ortagi);
        }
        if ($satan_satis_ortagi != '') {
            $query->where('satan_satis_ortagi', $satan_satis_ortagi);
        }

        if ($querydurum == 'demolar') {
            $query->where('durum_id', '!=', 7)
                ->whereHas('salon', function ($q) {
                    $q->where('demo_hesabi', true)->where('hesap_acildi', true);
                });
        } elseif ($querydurum == 'aktifsuresibitenler') {
            $query->where('durum_id', '7')
                ->whereHas('salon', function ($salonQuery) {
                    $salonQuery->whereDate('uyelik_bitis_tarihi', '<=', now()->addMonth());
                });
        }

        return $query->with('salon')->get()->map(function ($musteri) {
            return [
                'salon_adi' => $musteri->salon->salon_adi ?? null,
                'kalan_sure' => $musteri->salon ? self::lisans_sure_kontrol($musteri->salon->id) : null,
            ];
        });
    }

    public function lisans_sure_kontrol($salonid)
    {
        $isletme = Salonlar::where('id', $salonid)->first();
        $from_time = strtotime(date('Y-m-d H:i:s'));
        $to_time = strtotime($isletme->uyelik_bitis_tarihi . ' 23:59:59');
        $diff = round(($to_time - $from_time) / (3600 * 24), 0);
        if ($isletme->uyelik_bitis_tarihi === null || $isletme->uyelik_bitis_tarihi === '') {
            $diff .= '-';
        }
        return $diff;
    }

    public function bildirimekle($salonid, $mesaj, $url, $personelid, $musteriid, $imgurl, $randevuid, $satisortagiid)
    {
        $bildirim = new Bildirimler();
        $bildirim->aciklama = $mesaj;
        $bildirim->salon_id = $salonid;
        $bildirim->personel_id = $personelid;
        $bildirim->satis_ortagi_id = $satisortagiid;
        $bildirim->url = $url;
        $bildirim->tarih_saat = date('Y-m-d H:i:s');
        $bildirim->okundu = false;
        $bildirim->user_id = $musteriid;
        $bildirim->img_src = $imgurl;
        $bildirim->randevu_id = $randevuid;
        $bildirim->save();
    }
}
