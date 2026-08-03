# Güvenlik Duvarı (Sistem Yönetim v2)

Flood / SSH brute-force **otomatik engelleme** + saldırı anında **WhatsApp/SMS alarm** + panelden IP yönetimi.

## Mimari

```
┌─────────────────────────┐        ┌──────────────────────────────┐
│ ROOT watchdog (cron/dk)  │  yazar │  DB: guvenlik_olaylari         │
│ scripts/guvenlik-        │───────▶│      guvenlik_ip_kurallari     │
│  watchdog.sh             │        └──────────────────────────────┘
│  • ss ile flood tespit   │                     ▲ okur/yazar
│  • auth.log SSH brute    │                     │
│  • ipset+iptables DROP    │        ┌──────────────────────────────┐
│  • artisan guvenlik:bildir│        │ Panel /sistemyonetim/v2/      │
└──────────┬───────────────┘        │        guvenlik-duvari         │
           │ alarm                   │  • engelli IP listesi         │
           ▼                         │  • whitelist / blacklist      │
   WhatsApp (sistem oturumu)         │  • "engeli kaldır" (whitelist)│
   + SMS fallback                    └──────────────────────────────┘
```

- **Tespit + engelleme ROOT'ta** (PHP-FPM `www-root` iptables çalıştıramaz).
- **Panel deklaratiftir**: whitelist/blacklist kuralı yazar, watchdog bir sonraki turda (≤60 sn) uygular.
- **Alarm** `SistemBildirim::gonder()` üzerinden gider → önce Sistem WhatsApp oturumu (whatsmeow 3002), bağlı değilse **otomatik SMS**.

## Ön koşul: Sistem WhatsApp bildirim ayarı

Panelde **Sistem WhatsApp** sayfasından:
1. Bir gönderen numarayı QR ile bağla (alıcıdan **farklı** numara olmalı — WA kendine mesaj atmaz).
2. **Bildirim alıcı numarası** = senin numaran, "Bildirimler açık" işaretle, Kaydet.
3. "Test" ile doğrula.

> Ayar yapılmazsa alarm gitmez ama **engelleme yine çalışır** (script log'a yazar: `journalctl -t guvenlik-watchdog`).

## Kurulum (sunucuda, root)

```bash
# 1) ipset kur (yoksa)
apt-get update && apt-get install -y ipset

# 2) Script deploy ile /var/www/.../scripts/ altına gelir. /usr/local/bin'e link'le:
ln -sf /var/www/www-root/data/www/randevumcepte/scripts/guvenlik-watchdog.sh \
       /usr/local/bin/guvenlik-watchdog.sh
chmod +x /var/www/www-root/data/www/randevumcepte/scripts/guvenlik-watchdog.sh

# 3) Migration'ı çalıştır (deploy zaten migrate ediyor; elle de olur)
cd /var/www/www-root/data/www/randevumcepte && /opt/php74/bin/php artisan migrate --force

# 4) İLK ÇALIŞTIRMA — kendi IP'ni önce whitelist'e ekle (yanlışlıkla kilitlenme!)
#    EN KOLAYI: panelden "Whitelist'e Ekle". SQL istersen ($DB = .env'deki DB_DATABASE):
#    mysql <DB> -e "INSERT INTO guvenlik_ip_kurallari (ip,tip,aciklama,ekleyen,created_at)
#      VALUES ('SENIN_IP','whitelist','ofis','kurulum',NOW())
#      ON DUPLICATE KEY UPDATE tip='whitelist';"

# 5) Elle bir kez dene (kuru çalışma — ne bulduğunu logla)
/usr/local/bin/guvenlik-watchdog.sh; echo "cikis: $?"
journalctl -t guvenlik-watchdog -n 20 --no-pager

# 6) Cron: her dakika
( crontab -l 2>/dev/null | grep -v guvenlik-watchdog;
  echo '* * * * * /usr/local/bin/guvenlik-watchdog.sh >/dev/null 2>&1' ) | crontab -
```

## Eşikler (script başındaki AYARLAR)

| Değişken | Varsayılan | Anlamı |
|---|---|---|
| `CONN_THRESHOLD` | 60 | Tek IP'den 80/443'e eşzamanlı bağlantı ≥ bu → flood, engelle |
| `SSH_THRESHOLD` | 15 | Tek IP'den 1 dk içinde başarısız SSH ≥ bu → engelle |
| `LOAD_FACTOR` | 4 | `load1 > çekirdek×4` → yük uyarısı (engelleme yok) |
| `BAN_TIMEOUT` | 86400 | Otomatik ban süresi (sn) = 24 saat (sonra otomatik düşer) |
| `LOAD_ALERT_COOLDOWN` | 900 | Yük uyarısı en fazla 15 dk'da bir |

Eşiği değiştirdiysen dosyayı düzenle; cron sonraki turda yeni değerle çalışır.

## Panel kullanımı

`/sistemyonetim/v2/guvenlik-duvari`

- **Şu an engelli IP'ler** → "Engeli Kaldır" = IP whitelist'e eklenir, watchdog ≤60 sn içinde ipset'ten siler.
- **Whitelist'e Ekle** → kendi/ekip IP'lerin asla engellenmez.
- **Kalıcı Engelle (Blacklist)** → bilinen saldırganı elle kalıcı engelle.
- **Son Olaylar** → tüm flood/brute/yük olay geçmişi.

## Doğrulama / sorun giderme

```bash
# Aktif engelli IP'ler (ipset)
ipset list guvenlik_ban | sed -n '/Members/,$p'
ipset list guvenlik_ban_kalici | sed -n '/Members/,$p'

# iptables kuralı duruyor mu
iptables -L INPUT -n | grep match-set

# Watchdog logu
journalctl -t guvenlik-watchdog -n 50 --no-pager

# Bir IP'yi elle çıkar (acil erişim)
ipset del guvenlik_ban 1.2.3.4
```

## Notlar

- **fail2ban ile çakışmaz** — ikisi de DROP eder, zararsız. Watchdog'un asıl kattığı değer **HTTP flood** (fail2ban varsayılanı bunu görmez).
- Ban'lar `hash:ip` + 24s timeout ile geçici; dinamik IP'ler kalıcı takılmaz. Kalıcı engel için blacklist kullan.
- **Sunucu reboot'unda** ipset/iptables kuralları sıfırlanır ama watchdog ilk turda seti+kuralı yeniden kurar ve blacklist'i DB'den geri yükler (whitelist de aynı şekilde). Kalıcılık için ayrıca `netfilter-persistent`/`ipset save` gerekmez.
