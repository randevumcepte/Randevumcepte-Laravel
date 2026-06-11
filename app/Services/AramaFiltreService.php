<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

/**
 * Cagri Merkezi (Call Center) musteri filtre motoru.
 *
 * Sahip, arama listesi olustururken bircok filtreyi AND'leyerek musteri secer.
 * build() tek bir Query Builder doner; hem onizleme sayaci (count) hem de liste
 * kaydi (ids) AYNI builder'i kullanir -> tek dogruluk kaynagi.
 *
 * Beklenen $f anahtarlari (hepsi opsiyonel, bos/null gelirse o filtre uygulanmaz):
 *   kayit        : 'son1yil' | 'ozel'  (ozel ise kayit_t1 / kayit_t2 = Y-m-d)
 *   kayit_t1, kayit_t2 : ozel tarih araligi
 *   durum        : 'pasif' | 'aktif' | 'sadik'   (tahsilat sayisi: 0 / <=2 / >=3)
 *   gelmeyen     : 15 | 30 | 60 | 90             (son gelis MAX(tarih) < now()-N gun)
 *   satis        : 'var' | 'yok'                 (tahsilat var/yok)
 *   cinsiyet     : 0 (Kadin) | 1 (Erkek)         (bos = tumu; "unisex" musteri degeri degildir)
 *   kara_liste_haric : true  (kara listedekileri disla)
 *   whatsapp_onay    : true  (sadece whatsapp onayli)
 *   dogumgunu_yaklasan : N gun (yaklasan dogum gunu penceresi)
 *   hic_randevu_yok  : true  (hic randevu almamis)
 *   iptal_eden       : true  (iptal/reddedilmis randevusu olan)
 *   il_id, ilce_id   : konum filtresi
 */
class AramaFiltreService
{
    /**
     * Filtreleri uygulanmis musteri sorgusunu doner.
     * Secim: musteri_portfoy.user_id as id, users.name, users.cep_telefon, users.cinsiyet.
     */
    public static function build($salonId, array $f)
    {
        $query = DB::table('musteri_portfoy')
            ->join('users', 'musteri_portfoy.user_id', '=', 'users.id')
            ->where('musteri_portfoy.salon_id', $salonId)
            ->where('musteri_portfoy.aktif', true)
            // Aranabilmesi icin telefon zorunlu
            ->whereNotNull('users.cep_telefon')
            ->where('users.cep_telefon', '!=', '')
            ->select(
                'musteri_portfoy.user_id as id',
                'users.name as name',
                'users.cep_telefon as cep_telefon',
                'users.cinsiyet as cinsiyet',
                'musteri_portfoy.created_at as kayit_tarihi'
            )
            // Not: groupBy KULLANILMIYOR. musteri_portfoy zaten (user, salon) basina tekil;
            // ayrica Laravel 5.6.x (bu kurulumda) fromSub icermedigi icin grup sayimi sorun cikariyordu.
            ->distinct();

        // adisyonlar tablosunda soft-delete (silindi) kolonu var mi? (bazi kurulumlarda yok)
        $silindiVar = Schema::hasColumn('adisyonlar', 'silindi');
        // Gelmeyen icin silindi filtresi SQL parcasi (whereRaw subquery'de kullanilir)
        $silindiSql = $silindiVar ? ' AND IFNULL(adisyonlar.silindi,0)=0 ' : '';

        // 1) Kayit durumu / tarih araligi
        $kayit = $f['kayit'] ?? '';
        if ($kayit === 'son1yil') {
            $query->where('musteri_portfoy.created_at', '>=', Carbon::now()->subYear());
        } elseif ($kayit === 'ozel') {
            if (!empty($f['kayit_t1'])) {
                $query->where('musteri_portfoy.created_at', '>=', Carbon::parse($f['kayit_t1'])->startOfDay());
            }
            if (!empty($f['kayit_t2'])) {
                $query->where('musteri_portfoy.created_at', '<=', Carbon::parse($f['kayit_t2'])->endOfDay());
            }
        }

        // 2) Adisyon (satis fisi = "geldi + islem yapti") bazli musteri durumu.
        //    musteri_liste_getir (ApiController) ile ayni mantik: adisyon sayisi.
        //    Pasif = 0 adisyon, Aktif = 1-2, Sadik = 3+.
        $durum = $f['durum'] ?? '';
        if ($durum === 'pasif') {
            $query->whereNotExists(function ($q) use ($salonId, $silindiVar) {
                $q->select(DB::raw(1))->from('adisyonlar')
                    ->whereRaw('adisyonlar.user_id = musteri_portfoy.user_id')
                    ->where('adisyonlar.salon_id', $salonId);
                if ($silindiVar) $q->whereRaw('IFNULL(adisyonlar.silindi,0)=0');
            });
        } elseif ($durum === 'aktif') {
            $query->whereExists(function ($q) use ($salonId, $silindiVar) {
                $q->select(DB::raw(1))->from('adisyonlar')
                    ->whereRaw('adisyonlar.user_id = musteri_portfoy.user_id')
                    ->where('adisyonlar.salon_id', $salonId);
                if ($silindiVar) $q->whereRaw('IFNULL(adisyonlar.silindi,0)=0');
                $q->groupBy('adisyonlar.user_id')->havingRaw('COUNT(*) BETWEEN 1 AND 2');
            });
        } elseif ($durum === 'sadik') {
            $query->whereExists(function ($q) use ($salonId, $silindiVar) {
                $q->select(DB::raw(1))->from('adisyonlar')
                    ->whereRaw('adisyonlar.user_id = musteri_portfoy.user_id')
                    ->where('adisyonlar.salon_id', $salonId);
                if ($silindiVar) $q->whereRaw('IFNULL(adisyonlar.silindi,0)=0');
                $q->groupBy('adisyonlar.user_id')->havingRaw('COUNT(*) >= 3');
            });
        }

        // 3) Belirli gun gelmeyenler. "Son gelis" = musterinin GERCEKTEN geldigi/islem
        //    yaptigi son tarih = en son adisyonun tarihi (COALESCE(tarih,created_at)).
        //    Iptal/gelinmeyen randevular SAYILMAZ (adisyon = gercek gelis). Hic adisyonu
        //    olmayan (MAX NULL) bu filtreye girmez; onlar zaten "Pasif" segmentidir.
        $gelmeyen = $f['gelmeyen'] ?? '';
        if (in_array((int) $gelmeyen, [15, 30, 60, 90], true)) {
            $sinir = Carbon::now()->subDays((int) $gelmeyen)->toDateString();
            $query->whereRaw(
                '(SELECT MAX(COALESCE(adisyonlar.tarih, adisyonlar.created_at)) FROM adisyonlar WHERE adisyonlar.user_id = musteri_portfoy.user_id AND adisyonlar.salon_id = ?' . $silindiSql . ') < ?',
                [$salonId, $sinir]
            );
        }

        // 4) Satis yapilmis / yapilmamis = en az bir adisyonu (satis fisi) var/yok.
        $satis = $f['satis'] ?? '';
        if ($satis === 'var') {
            $query->whereExists(function ($q) use ($salonId, $silindiVar) {
                $q->select(DB::raw(1))->from('adisyonlar')
                    ->whereRaw('adisyonlar.user_id = musteri_portfoy.user_id')
                    ->where('adisyonlar.salon_id', $salonId);
                if ($silindiVar) $q->whereRaw('IFNULL(adisyonlar.silindi,0)=0');
            });
        } elseif ($satis === 'yok') {
            $query->whereNotExists(function ($q) use ($salonId, $silindiVar) {
                $q->select(DB::raw(1))->from('adisyonlar')
                    ->whereRaw('adisyonlar.user_id = musteri_portfoy.user_id')
                    ->where('adisyonlar.salon_id', $salonId);
                if ($silindiVar) $q->whereRaw('IFNULL(adisyonlar.silindi,0)=0');
            });
        }

        // 5) Cinsiyet (0=Kadin, 1=Erkek). Bos = tumu.
        $cinsiyet = $f['cinsiyet'] ?? '';
        if ($cinsiyet === 0 || $cinsiyet === '0' || $cinsiyet === 1 || $cinsiyet === '1') {
            $query->where('users.cinsiyet', (int) $cinsiyet);
        }

        // 6) Akilli ek filtreler
        if (!empty($f['kara_liste_haric'])) {
            // kara_liste cogu kayitta NULL -> "= 0" NULL'lari da eler ve herkes kaybolurdu.
            // Dogrusu: kara listede OLMAYAN = IFNULL(kara_liste,0)=0 (NULL ve 0 dahil).
            $query->whereRaw('IFNULL(musteri_portfoy.kara_liste,0) = 0');
        }

        if (!empty($f['whatsapp_onay'])) {
            $query->where('users.whatsapp_onay', 1);
        }

        if (!empty($f['dogumgunu_yaklasan'])) {
            $gun = (int) $f['dogumgunu_yaklasan'];
            $bugun = Carbon::now();
            $bitis = (clone $bugun)->addDays($gun);
            $bas = $bugun->format('m-d');
            $son = $bitis->format('m-d');
            if ($bas <= $son) {
                // Yil ici pencere
                $query->whereRaw("DATE_FORMAT(users.dogum_tarihi,'%m-%d') BETWEEN ? AND ?", [$bas, $son]);
            } else {
                // Yil sonu sarmasi ( orn 12-28 .. 01-05)
                $query->whereRaw("(DATE_FORMAT(users.dogum_tarihi,'%m-%d') >= ? OR DATE_FORMAT(users.dogum_tarihi,'%m-%d') <= ?)", [$bas, $son]);
            }
        }

        if (!empty($f['hic_randevu_yok'])) {
            $query->whereNotExists(function ($q) use ($salonId) {
                $q->select(DB::raw(1))->from('randevular')
                    ->whereRaw('randevular.user_id = musteri_portfoy.user_id')
                    ->where('randevular.salon_id', $salonId);
            });
        }

        if (!empty($f['iptal_eden'])) {
            $query->whereExists(function ($q) use ($salonId) {
                $q->select(DB::raw(1))->from('randevular')
                    ->whereRaw('randevular.user_id = musteri_portfoy.user_id')
                    ->where('randevular.salon_id', $salonId)
                    ->whereIn('randevular.durum', [2, 3]);
            });
        }

        if (!empty($f['il_id'])) {
            $query->where('users.il_id', $f['il_id']);
        }
        if (!empty($f['ilce_id'])) {
            $query->where('users.ilce_id', $f['ilce_id']);
        }

        // Metin aramasi (modal icindeki canli arama)
        if (!empty($f['search'])) {
            $s = $f['search'];
            $query->where(function ($q) use ($s) {
                $q->where('users.name', 'like', '%' . $s . '%')
                  ->orWhere('users.cep_telefon', 'like', '%' . $s . '%');
            });
        }

        return $query;
    }

    /**
     * Filtreye uyan benzersiz musteri (user_id) sayisi.
     */
    public static function count($salonId, array $f)
    {
        // distinct select uzerinde dogrudan say (fromSub Laravel 5.6.x'te yok).
        return self::build($salonId, $f)->count('musteri_portfoy.user_id');
    }

    /**
     * Filtreye uyan tum user_id'ler.
     */
    public static function ids($salonId, array $f)
    {
        return self::build($salonId, $f)->pluck('id')->all();
    }

    /**
     * Gelen istekten ($request->all veya 'filtre' JSON) standart filtre dizisi cikarir.
     */
    public static function parseRequest($request)
    {
        $ham = $request->input('filtre');
        if (is_string($ham)) {
            $decoded = json_decode($ham, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        } elseif (is_array($ham)) {
            return $ham;
        }

        // Geriye donuk: ayri ayri alanlar
        return [
            'kayit'              => $request->input('kayit', ''),
            'kayit_t1'           => $request->input('kayit_t1', ''),
            'kayit_t2'           => $request->input('kayit_t2', ''),
            'durum'              => $request->input('durum', ''),
            'gelmeyen'           => $request->input('gelmeyen', ''),
            'satis'              => $request->input('satis', ''),
            'cinsiyet'           => $request->input('cinsiyet', ''),
            // Not: Request::boolean() bu Laravel surumunde YOK -> filter_var ile (bkz. reference_laravel_surum)
            'kara_liste_haric'   => filter_var($request->input('kara_liste_haric'), FILTER_VALIDATE_BOOLEAN),
            'whatsapp_onay'      => filter_var($request->input('whatsapp_onay'), FILTER_VALIDATE_BOOLEAN),
            'dogumgunu_yaklasan' => $request->input('dogumgunu_yaklasan', ''),
            'hic_randevu_yok'    => filter_var($request->input('hic_randevu_yok'), FILTER_VALIDATE_BOOLEAN),
            'iptal_eden'         => filter_var($request->input('iptal_eden'), FILTER_VALIDATE_BOOLEAN),
            'il_id'              => $request->input('il_id', ''),
            'ilce_id'            => $request->input('ilce_id', ''),
            'search'             => $request->input('search', ''),
        ];
    }
}
