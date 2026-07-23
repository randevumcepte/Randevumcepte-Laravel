    -- =====================================================================
    -- SATIS & TAHSILAT DOKUMU — hizmet / urun / paket kalemleri tek listede
    -- Satici (personel), musteri, satis tarihi, satis tutari, tahsil edilen, kalan
    -- Kaynak: adisyon_{hizmetler,urunler,paketler} + tahsilat_{...} (prim raporuyla ayni zincir)
    --
    -- KULLANIM: @salon ve tarih araligini degistir. phpMyAdmin/adminer'da calistir.
    -- =====================================================================
    SET @salon    = 368;
    SET @tarih1   = '2026-07-01';
    SET @tarih2   = '2026-07-31';

    SELECT * FROM (
        -- ---------- HIZMETLER ----------
        SELECT
            'Hizmet'                             AS tur,
            h.hizmet_adi                         AS kalem_adi,
            u.name                               AS musteri,
            u.cep_telefon                        AS musteri_tel,
            COALESCE(p.personel_adi, '(SATICI YOK)') AS satici,
            DATE(COALESCE(ah.islem_tarihi, a.tarih)) AS satis_tarihi,
            ah.fiyat                             AS satis_tutari,
            COALESCE(th.tahsil, 0)               AS tahsil_edilen,
            ah.fiyat - COALESCE(th.tahsil, 0)    AS kalan,
            a.id                                 AS adisyon_id,
            ah.id                                AS kalem_id
        FROM adisyon_hizmetler ah
        JOIN adisyonlar a          ON a.id = ah.adisyon_id
        LEFT JOIN hizmetler h      ON h.id = ah.hizmet_id
        LEFT JOIN users u          ON u.id = a.user_id
        LEFT JOIN salon_personelleri p ON p.id = ah.personel_id
        LEFT JOIN (
            SELECT adisyon_hizmet_id, SUM(tutar) AS tahsil
            FROM tahsilat_hizmetler GROUP BY adisyon_hizmet_id
        ) th ON th.adisyon_hizmet_id = ah.id
        WHERE a.salon_id = @salon
        AND a.tarih BETWEEN CONCAT(@tarih1,' 00:00:00') AND CONCAT(@tarih2,' 23:59:59')

        UNION ALL

        -- ---------- URUNLER ----------
        SELECT
            'Urun'                               AS tur,
            ur.urun_adi                          AS kalem_adi,
            u.name                               AS musteri,
            u.cep_telefon                        AS musteri_tel,
            COALESCE(p.personel_adi, '(SATICI YOK)') AS satici,
            DATE(a.tarih)                        AS satis_tarihi,
            au.fiyat                             AS satis_tutari,
            COALESCE(tu.tahsil, 0)               AS tahsil_edilen,
            au.fiyat - COALESCE(tu.tahsil, 0)    AS kalan,
            a.id                                 AS adisyon_id,
            au.id                                AS kalem_id
        FROM adisyon_urunler au
        JOIN adisyonlar a          ON a.id = au.adisyon_id
        LEFT JOIN urunler ur       ON ur.id = au.urun_id
        LEFT JOIN users u          ON u.id = a.user_id
        LEFT JOIN salon_personelleri p ON p.id = au.personel_id
        LEFT JOIN (
            SELECT adisyon_urun_id, SUM(tutar) AS tahsil
            FROM tahsilat_urunler GROUP BY adisyon_urun_id
        ) tu ON tu.adisyon_urun_id = au.id
        WHERE a.salon_id = @salon
        AND a.tarih BETWEEN CONCAT(@tarih1,' 00:00:00') AND CONCAT(@tarih2,' 23:59:59')

        UNION ALL

        -- ---------- PAKETLER ----------
        SELECT
            'Paket'                              AS tur,
            pk.paket_adi                         AS kalem_adi,
            u.name                               AS musteri,
            u.cep_telefon                        AS musteri_tel,
            COALESCE(p.personel_adi, '(SATICI YOK)') AS satici,
            DATE(a.tarih)                        AS satis_tarihi,
            ap.fiyat                             AS satis_tutari,
            COALESCE(tp.tahsil, 0)               AS tahsil_edilen,
            ap.fiyat - COALESCE(tp.tahsil, 0)    AS kalan,
            a.id                                 AS adisyon_id,
            ap.id                                AS kalem_id
        FROM adisyon_paketler ap
        JOIN adisyonlar a          ON a.id = ap.adisyon_id
        LEFT JOIN paketler pk      ON pk.id = ap.paket_id
        LEFT JOIN users u          ON u.id = a.user_id
        LEFT JOIN salon_personelleri p ON p.id = ap.personel_id
        LEFT JOIN (
            SELECT adisyon_paket_id, SUM(tutar) AS tahsil
            FROM tahsilat_paketler GROUP BY adisyon_paket_id
        ) tp ON tp.adisyon_paket_id = ap.id
        WHERE a.salon_id = @salon
        AND a.tarih BETWEEN CONCAT(@tarih1,' 00:00:00') AND CONCAT(@tarih2,' 23:59:59')
    ) dokum
    ORDER BY satis_tarihi DESC, tur, kalem_id;
