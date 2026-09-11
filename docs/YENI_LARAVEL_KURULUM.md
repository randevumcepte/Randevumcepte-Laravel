# Yeni Laravel (Modüler Yapı) Kurulum Kılavuzu

Mevcut monolitik uygulamayı (Laravel 5.6 / PHP 7.4) peyder pey modüler hale getirmek için,
**canlı sunucuya son sürüm Laravel'in ayrı bir dizine, yan yana (strangler pattern)** kurulması.
Eski uygulamaya dokunmadan, aynı MySQL veritabanını paylaşarak modüller tek tek taşınır.

- **Sunucu:** `89.252.140.44` (ISPmanager panelli, nginx + PHP-FPM)
- **Eski uygulama:** `/var/www/www-root/data/www/randevumcepte` — PHP 7.4 / Laravel 5.6
- **Yeni uygulama:** `/var/www/www-root/data/www/randevumcepteyeni` — PHP 8.3 / Laravel 13.x
- **Yeni domain:** `panel.randevumcepte.com.tr`
- **Kullanıcı:** `www-root` (FPM bu kullanıcıyla çalışır)

> ⚠️ **Panel-yönetimli sunucu:** Bu makine ISPmanager kullanır (`vhosts-resources`, `php-fpm/1.sock`,
> `disable_symlinks` bunun izleri). Manuel nginx `.conf` düzenlemeleri panel tarafından **ezilebilir**.
> Kalıcı yol: siteyi **panel arayüzünden** oluşturmak ve document root'u `public/` yapmaktır.

---

## 1. PHP sürümü kontrolü

Laravel son sürüm **PHP 8.2+** ister. Sunucuda 8.3 zaten kuruluydu (varsayılan CLI 8.3.8):

```bash
php -v                    # PHP 8.3.x
ls -la /usr/bin/php*      # kurulu binary'ler
dpkg -l | grep -i php | grep '^ii'
```

Eski uygulama PHP 7.4 FPM havuzunda çalışmaya devam eder; ikisi yan yana durur.

### Gerekli PHP eklentileri

```bash
php -m | grep -iE "mbstring|xml|curl|pdo_mysql|zip|bcmath|gd|intl|openssl|tokenizer|ctype|fileinfo"
```

Eksik varsa:

```bash
apt install -y php8.3-mbstring php8.3-xml php8.3-curl php8.3-mysql \
  php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl
systemctl restart php8.3-fpm
```

> **Önemli:** `pdo_mysql` mutlaka kurulu olmalı. Yoksa "could not find driver" hatası alınır.

---

## 2. Composer + Laravel kurulumu

```bash
# Composer (yoksa)
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Yeni Laravel'i AYRI dizine kur
cd /var/www/www-root/data/www/
composer create-project laravel/laravel randevumcepteyeni
```

---

## 3. nginx site + document root (KURALLARA UYGUN)

### Kök neden hatası: `listen` adresi uyuşmazlığı

İlk denemede `panel.randevumcepte.com.tr` açılmayıp **eski app dizini** açılıyordu. Sebep:

- Sistemdeki tüm bloklar (özellikle wildcard `*.randevumcepte.com.tr`) `listen 89.252.140.44:80;` kullanıyor.
- Eklenen panel bloğu ise `listen 80;` (yani `0.0.0.0:80`) kullanıyordu — **ayrı soket grubu**.
- nginx önce listen soketini seçer, sonra o soket içinde `server_name` eşleştirir. Belirli IP'ye
  gelen istek, `89.252.140.44:80` grubuna gider; panel bloğu orada olmadığı için wildcard
  (`*.randevumcepte.com.tr` → eski `randevumcepte` dizini) isteği yakalar.

**Kural:** Exact `server_name`, wildcard'dan önceliklidir — **ama yalnızca aynı listen soketinde**.

### Doğru yol: ISPmanager panelinden site oluştur

ISPmanager → **Siteler (WWW Domains)** → **Yeni site**:

- Domain: `panel.randevumcepte.com.tr`
- **Document root:** `www/randevumcepteyeni/public`  ← **kritik**, `public` olacak
- **PHP sürümü:** 8.3 (PHP-FPM)
- SSL: Let's Encrypt (panel certbot'u yönetir, yenileme otomatik)

> Document root'un `public/` olması, `.env` / `.git` / `storage` dizinlerinin web köküne **düşmemesini**
> sağlar. (Eski `randevumcepte` sitesinde kök = repo kökü olduğu için `.env` ve `.git` web'e sızmıştı —
> aynı hatayı tekrarlama.)

### Manuel nginx bloğu (panel yetmezse — geçici)

Panel bloklarıyla **aynı sokete** bağla:

```nginx
server {
    listen 89.252.140.44:80;
    server_name panel.randevumcepte.com.tr;
    root /var/www/www-root/data/www/randevumcepteyeni/public;
    index index.php;

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;   # 8.3 havuzu — 1.sock DEĞİL (o 7.4)
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
# + 443 için aynısı, wildcard sertifikayla:
#   listen 89.252.140.44:443 ssl http2;
#   ssl_certificate     /etc/letsencrypt/live/randevumcepte.com.tr/fullchain.pem;
#   ssl_certificate_key /etc/letsencrypt/live/randevumcepte.com.tr/privkey.pem;
```

FPM soketini doğrula:

```bash
ls -la /run/php/                                  # php8.3-fpm.sock var mı?
grep -rl "randevumcepteyeni" /etc/php/8.3/fpm/pool.d/
nginx -t && systemctl reload nginx
```

---

## 4. İzinler

Laravel'de yalnızca `storage` ve `bootstrap/cache` yazılabilir olmalı. Tek satır:

```bash
cd /var/www/www-root/data/www/randevumcepteyeni
chown -R www-root:www-root . && chmod -R 775 storage bootstrap/cache
```

> `777` verme. `www-root` hem dosya sahibi hem FPM kullanıcısı; grup yazma (775) yeterli.
> `git pull` sonrası bu komut tekrar gerekebilir.

---

## 5. `open_basedir` / tempnam uyarısı

**Belirti:** `tempnam(): file created in the system's temporary directory`

**Sebep:** ISPmanager her siteye `open_basedir` koyar; sistem temp (`/tmp`) bu kısıtın dışında kalır,
PHP 8.3 uyarı basar.

**Çözüm:** Siteye kendi içinde temp klasörü ver, PHP'yi ona yönlendir.

```bash
mkdir -p /var/www/www-root/data/www/randevumcepteyeni/tmp
chown www-root:www-root /var/www/www-root/data/www/randevumcepteyeni/tmp
chmod 775 /var/www/www-root/data/www/randevumcepteyeni/tmp
```

`sys_temp_dir` / `upload_tmp_dir` **PHP_INI_SYSTEM** seviyesidir — `.user.ini` / `ini_set()` ile
değişmez, sadece FPM pool / php.ini'de olur. Panel PHP ayarlarından veya pool `.conf`'una ekle:

```ini
php_admin_value[sys_temp_dir]   = /var/www/www-root/data/www/randevumcepteyeni/tmp
php_admin_value[upload_tmp_dir] = /var/www/www-root/data/www/randevumcepteyeni/tmp
php_admin_value[open_basedir]   = /var/www/www-root/data/www/randevumcepteyeni:/var/www/www-root/data/www/randevumcepteyeni/tmp
```

```bash
systemctl restart php8.3-fpm
```

---

## 6. Veritabanı ayarı (mevcut MySQL'i paylaş)

**Belirti:** `could not find driver (Connection: sqlite ...)` — yeni Laravel varsayılan sqlite kullanır
ve `sessions` tablosunu DB'de arar.

**Çözüm:** `.env`'i mevcut MySQL'e bağla, session/cache'i dosyaya al (paylaşılan DB'ye tablo AÇMA).

DB bilgilerini eski uygulamadan al:

```bash
grep -E "^DB_" /var/www/www-root/data/www/randevumcepte/.env
```

Yeni `.env`:

```env
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<eski_ile_ayni>
DB_USERNAME=<eski_ile_ayni>
DB_PASSWORD=<eski_ile_ayni>

# Paylasilan DB'ye sessions/cache tablosu ACMASIN
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

```bash
php artisan config:clear && php artisan cache:clear
```

> ⚠️ Paylaşılan DB'de **`php artisan migrate` ÇALIŞTIRMA** — mevcut tabloları bozar.
> Yeni modüllerde mevcut tablolara Eloquent ile map'le: `protected $table`, `protected $primaryKey`,
> gerekirse `public $timestamps = false`.

---

## 7. Güvenlik kontrol listesi (public'ten açarken)

- [ ] `.env` içinde `APP_DEBUG=false`, `APP_ENV=production`
- [ ] Document root = `public/` (kök = repo kökü DEĞİL)
- [ ] `.env` tarayıcıdan erişilemez: `curl -sI https://panel.randevumcepte.com.tr/.env` → 403/404
- [ ] `.git` web'den erişilemez
- [ ] `storage`, `bootstrap/cache`, `tmp` → 775, sahiplik `www-root:www-root`
- [ ] SSL aktif ve otomatik yenileniyor

---

## Modülerleştirme stratejisi (strangler pattern)

1. Yeni Laravel aynı MySQL'e bağlanır, mevcut tabloları model ile kullanır (migrate YOK).
2. Bir modül yeni uygulamada yazılıp test edilir.
3. nginx'te ilgili route/path yeni uygulamaya yönlendirilir; gerisi eskide kalır.
4. Modül canlı doğrulanınca bir sonrakine geçilir. Eski monolit zamanla erir.
