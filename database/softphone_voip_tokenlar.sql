-- Mobil softphone iOS VoIP push token tablosu (SoftphoneApiController).
-- Bir kez calistir. Mevcut tablolara dokunmaz.
CREATE TABLE IF NOT EXISTS `softphone_voip_tokenlar` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `dahili` varchar(20) NOT NULL,
  `salon_id` int DEFAULT NULL,
  `personel_id` int DEFAULT NULL,
  `token` varchar(200) NOT NULL,
  `bundle` varchar(150) NOT NULL,
  `platform` varchar(10) NOT NULL DEFAULT 'ios',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `softphone_voip_token_uniq` (`token`),
  KEY `softphone_voip_dahili_idx` (`dahili`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
