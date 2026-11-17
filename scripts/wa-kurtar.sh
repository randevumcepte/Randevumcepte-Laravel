#!/bin/bash
# 17 Kas 2026: deploy reset --hard ile silinen whatsmeow-bridge + WA oturumlarini geri yukler. Sunucuda root ile: bash scripts/wa-kurtar.sh
R=/var/www/www-root/data/www/randevumcepte; K=/root/wa-kurtarma; ESKI=8f1c542c; YENI=80031a25
cd $R || exit 1
Y=/root/wa-yedek-$(date +%H%M%S); mkdir -p $Y && cp -a whatsmeow-bridge $Y/ && echo "1) mevcut durum yedeklendi: $Y"

# 2) 16:22'de silinen dosyalari (data/*.db haric) eski commit'ten TAKIPSIZ geri koy
git diff --name-only --diff-filter=D $ESKI $YENI | grep -v '^whatsmeow-bridge/data/' \
  | while IFS= read -r f; do [ -e "$f" ] || printf '%s\0' "$f"; done > /root/geri.lst
tr '\0' '\n' < /root/geri.lst | cut -d/ -f1-2 | sort | uniq -c
xargs -0 -n 300 sh -c 'git --literal-pathspecs archive '$ESKI' -- "$@" | tar -x -C '$R _ < /root/geri.lst
echo "2) git'ten geri kondu: $(tr -cd '\0' < /root/geri.lst | wc -c) dosya"

# 3) WA oturum DB'leri: /proc'tan kurtarilan en guncel kopya (yalniz diskte yok/0 bayt ise)
D=$R/whatsmeow-bridge/data
for p in 204:2925053 223:2888887 236:2967727 246:2888889 249:2897523 257:2888891 278:2888892 \
         296:2888893 331:2888894 333:2967920 343:2888896 352:2888897 355:2893848 368:2968154 \
         370:2888900 376:2888901 389:2888902 391:2888904 395:2888905 397:2888906 401:2893779 \
         403:2888909 405:2888910 415:2967017 416:2888912 420:2888913 421:2897651 432:2900445; do
  s=${p%%:*}; i=${p##*:}
  if [ ! -s $D/salon_$s.db ]; then cp $K/salon_$s.db.$i $D/salon_$s.db && echo "   salon_$s geri kondu"; fi
done
chmod +x $R/whatsmeow-bridge/whatsmeow-bridge 2>/dev/null || cp $K/whatsmeow-bridge.bin $R/whatsmeow-bridge/whatsmeow-bridge
ls -la $R/whatsmeow-bridge/ | head -12

# 4) Kopruyu yeniden baslat ve kontrol et
pm2 restart 2 && sleep 15
pm2 logs 2 --lines 200 --nostream | grep -E "auto-restore|\[236\]|\] connected" | tail -40
T=$(grep '^SHARED_SECRET=' $R/whatsmeow-bridge/.env | cut -d= -f2-)
curl -s -H "X-Service-Token: $T" localhost:3002/session/236/status; echo
