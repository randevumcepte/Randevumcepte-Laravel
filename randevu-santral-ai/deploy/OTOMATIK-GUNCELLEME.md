# Köprüyü git'ten otomatik güncelleme (santral sunucusu)

Cron her 5 dakikada bir repo'nun **yalnız `randevu-santral-ai` klasörünü** çeker. Değişiklik varsa `/opt/randevu-santral-ai`'ye kopyalar ve servisi yeniden başlatır. `.env` ve `node_modules` dokunulmaz. Aktif asistan görüşmesi varsa restart ertelenir.

## 1. GitHub erişimi (bir kez, salt-okunur deploy key)

```bash
ssh-keygen -t ed25519 -f /root/.ssh/santral_deploy -N "" -C "santral-kopru"
cat /root/.ssh/santral_deploy.pub
```

Çıkan satırı GitHub'da ekle: **randevumcepte/Randevumcepte-Laravel → Settings → Deploy keys → Add deploy key**. "Allow write access" kutusunu İŞARETLEME.

```bash
cat >> /root/.ssh/config <<'EOF'
Host github-santral
  HostName github.com
  User git
  IdentityFile /root/.ssh/santral_deploy
  IdentitiesOnly yes
EOF
chmod 600 /root/.ssh/config
ssh -T github-santral    # "successfully authenticated" görmelisin
```

## 2. Sparse kopyayı al ve betiği kur

```bash
command -v rsync || yum install -y rsync    # Debian/Ubuntu: apt install -y rsync
git clone --depth 1 --filter=blob:none --no-checkout --branch main \
  git@github-santral:randevumcepte/Randevumcepte-Laravel.git /opt/randevu-santral-ai-git
cd /opt/randevu-santral-ai-git
git sparse-checkout init --cone
git sparse-checkout set randevu-santral-ai
git checkout main
cp randevu-santral-ai/deploy/otomatik-guncelle.sh /usr/local/bin/santral-guncelle.sh
chmod +x /usr/local/bin/santral-guncelle.sh
/usr/local/bin/santral-guncelle.sh      # ilk kurulum: dosyaları kopyalar + servisi yeniden başlatır
```

İlk çalıştırmayı **arama yokken** yap. Betiğin kendisi `/usr/local/bin`'de durur; git güncellerken çalışan betik değişmesin diye kopyadan çalıştırılmaz. Betik güncellenirse bu `cp` satırını tekrar çalıştırman yeterli.

## 3. Cron

```bash
crontab -e
```
Şu satırı ekle:
```
*/5 * * * * /usr/local/bin/santral-guncelle.sh >> /var/log/santral-guncelle.log 2>&1
```

## Kontrol

```bash
tail -20 /var/log/santral-guncelle.log
cat /opt/randevu-santral-ai/.son-kurulan-commit
```

Geçici olarak durdurmak için crontab satırının başına `#` koy.
