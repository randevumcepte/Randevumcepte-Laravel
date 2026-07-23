-- =====================================================================
-- SATICISI BOS PAKET SATISLARI — NEDEN BOS? (kaynak bazinda teshis)
--
-- "Paket satisi" = gercek adisyon_paketler + seansli adisyon_hizmetler
--                  (seans_sayisi > 0 — sistemdeki yeni siniflandirma).
-- Adisyonun notlar alanindaki import marker'ina bakarak kalemin nereden
-- geldigini gosterir; boylece hangi akisin satici yazmadigi netlesir.
--
-- KULLANIM: @salon degistir, phpMyAdmin'de calistir.
-- =====================================================================
SET @salon = 368;

-- ---------- 1) OZET: saticisi bos paket kalemleri, kaynagina gore ----------
SELECT
    kaynak,
    tur,
    COUNT(*)          AS kalem_sayisi,
    ROUND(SUM(tutar)) AS toplam_tutar
FROM (
    -- Seansli hizmetler (Salonappy paketleri burada durur)
    SELECT
        CASE
            WHEN a.notlar LIKE '%[salonappy-pkgsale:%' THEN '1-Salonappy paket satisi'
            WHEN a.notlar LIKE '%[salonappy-visit:%'   THEN '2-Salonappy ziyaret'
            WHEN a.notlar LIKE '%[salonappy-prodsale:%' THEN '3-Salonappy urun satisi'
            WHEN a.notlar LIKE '%[salonappy%'          THEN '4-Salonappy diger'
            WHEN a.notlar IS NULL OR a.notlar = ''     THEN '5-Manuel (marker yok)'
            ELSE '6-Diger'
        END          AS kaynak,
        'Seansli hizmet' AS tur,
        ah.fiyat     AS tutar
    FROM adisyon_hizmetler ah
    JOIN adisyonlar a ON a.id = ah.adisyon_id
    WHERE a.salon_id = @salon
      AND ah.seans_sayisi > 0
      AND ah.personel_id IS NULL

    UNION ALL

    -- Gercek paket satislari
    SELECT
        CASE
            WHEN a.notlar LIKE '%[salonappy-pkgsale:%' THEN '1-Salonappy paket satisi'
            WHEN a.notlar LIKE '%[salonappy-visit:%'   THEN '2-Salonappy ziyaret'
            WHEN a.notlar LIKE '%[salonappy-prodsale:%' THEN '3-Salonappy urun satisi'
            WHEN a.notlar LIKE '%[salonappy%'          THEN '4-Salonappy diger'
            WHEN a.notlar IS NULL OR a.notlar = ''     THEN '5-Manuel (marker yok)'
            ELSE '6-Diger'
        END        AS kaynak,
        'Gercek paket' AS tur,
        ap.fiyat   AS tutar
    FROM adisyon_paketler ap
    JOIN adisyonlar a ON a.id = ap.adisyon_id
    WHERE a.salon_id = @salon
      AND ap.personel_id IS NULL
) x
GROUP BY kaynak, tur
ORDER BY kaynak, tur;


-- ---------- 2) DETAY: Salonappy paket satisi olup saticisi bos olanlar ----------
-- group_id'yi marker'dan cikarir; dump'taki seller_text ile karsilastirmak icin.
-- (Dump'ta seller_text='Kasa' ise satici KAYNAKTA yoktur — doldurulamaz.)
SELECT
    SUBSTRING_INDEX(SUBSTRING_INDEX(a.notlar, '[salonappy-pkgsale:', -1), ']', 1) AS salonappy_group_id,
    a.id            AS adisyon_id,
    ah.id           AS kalem_id,
    h.hizmet_adi    AS kalem_adi,
    u.name          AS musteri,
    DATE(a.tarih)   AS satis_tarihi,
    ah.seans_sayisi,
    ah.fiyat        AS satis_tutari
FROM adisyon_hizmetler ah
JOIN adisyonlar a     ON a.id = ah.adisyon_id
LEFT JOIN hizmetler h ON h.id = ah.hizmet_id
LEFT JOIN users u     ON u.id = a.user_id
WHERE a.salon_id = @salon
  AND ah.seans_sayisi > 0
  AND ah.personel_id IS NULL
  AND a.notlar LIKE '%[salonappy-pkgsale:%'
ORDER BY a.tarih DESC
LIMIT 200;


-- ---------- 3) KONTROL: dolu olanlar hangi personellere yazilmis ----------
SELECT
    COALESCE(p.personel_adi, '(BOS)') AS satici,
    COUNT(*)          AS kalem_sayisi,
    ROUND(SUM(ah.fiyat)) AS toplam_tutar
FROM adisyon_hizmetler ah
JOIN adisyonlar a ON a.id = ah.adisyon_id
LEFT JOIN salon_personelleri p ON p.id = ah.personel_id
WHERE a.salon_id = @salon
  AND ah.seans_sayisi > 0
GROUP BY satici
ORDER BY kalem_sayisi DESC;
