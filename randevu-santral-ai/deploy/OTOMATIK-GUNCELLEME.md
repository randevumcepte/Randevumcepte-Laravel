# Köprüyü git'ten otomatik güncelleme (santral sunucusu)

`otomatik-guncelle.sh` repo'dan yalnız `randevu-santral-ai` klasörünü çeker (HTTPS, anahtar gerekmez). Değişiklik varsa `/opt/randevu-santral-ai`'ye kopyalar ve servisi yeniden başlatır. `.env` ve `node_modules` dokunulmaz. Aktif asistan görüşmesi varsa restart ertelenir.

## Kurulum (bir kez)

```bash
curl -fsSL https://raw.githubusercontent.com/randevumcepte/Randevumcepte-Laravel/main/randevu-santral-ai/deploy/otomatik-guncelle.sh -o /usr/local/bin/santral-guncelle.sh
chmod +x /usr/local/bin/santral-guncelle.sh
/usr/local/bin/santral-guncelle.sh     # ilk çalıştırma: klasörü çeker, kopyalar, servisi yeniden başlatır (arama yokken)
```

`rsync` yoksa önce: `yum install -y rsync` (Debian/Ubuntu: `apt install -y rsync`).

## Cron

`crontab -e`:
```
*/5 * * * * /usr/local/bin/santral-guncelle.sh >> /var/log/santral-guncelle.log 2>&1
```

## Kontrol

```bash
tail -20 /var/log/santral-guncelle.log
```
