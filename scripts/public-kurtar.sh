#!/bin/bash
# 17 Kas 2026 deploy olayi: canli checkout'ta yerel commit'e (8f1c542c vb.) girmis
# yuklenen resim/form dosyalari, DEPLOY_PROD=1 acilinca tek adimli
# 'reset --hard origin/main' ile silindi (bkz. scripts/wa-kurtar.sh, ayni kok neden).
# Bu betik canli repo'nun reflog'undaki HER commit'i tarar; o commit'lerde takipli
# olup diskte artik OLMAYAN dosyalari en guncel kopyalarindan TAKIPSIZ geri koyar.
# Hicbir mevcut dosyanin uzerine yazmaz, git durumunu degistirmez.
#
# Kullanim (sunucuda root):
#   bash scripts/public-kurtar.sh            -> yalniz rapor (KURU)
#   bash scripts/public-kurtar.sh uygula     -> geri yukle
#   KLASOR="public storage/app" bash scripts/public-kurtar.sh   -> taranacak klasorler

R=${R:-/var/www/www-root/data/www/randevumcepte}
KLASOR=${KLASOR:-public}
MOD=${1:-kuru}
cd "$R" || exit 1
L=/root/public-kurtar-$(date +%Y%m%d-%H%M%S); mkdir -p "$L"

# Reflog'daki tum commit'ler (yeniden eskiye), tekil
git reflog --format=%H | awk '!g[$0]++' > "$L/commitler"
echo "Reflog commit sayisi: $(wc -l < "$L/commitler")"

# Her eksik dosya icin: onu iceren EN YENI commit (ilk gorulen kazanir)
: > "$L/eksik.tsv"
while read -r c; do
    git ls-tree -r --name-only "$c" -- $KLASOR 2>/dev/null | while IFS= read -r f; do
        [ -e "$f" ] || printf '%s\t%s\n' "$c" "$f"
    done
done < "$L/commitler" | awk -F'\t' '!g[$2]++' > "$L/eksik.tsv"

N=$(wc -l < "$L/eksik.tsv")
echo "Diskte olmayan dosya: $N  (liste: $L/eksik.tsv)"
cut -f2 "$L/eksik.tsv" | cut -d/ -f1-3 | sort | uniq -c | sort -rn | head -30
[ "$N" -eq 0 ] && { echo "Geri yuklenecek bir sey yok (dosyalar hic commit'lenmemis olabilir -> yedekten donulmeli)"; exit 0; }

if [ "$MOD" != "uygula" ]; then
    echo; echo "KURU calisma. Geri yuklemek icin: bash $0 uygula"; exit 0
fi

# Commit bazinda git archive ile cikar (takipsiz kalir, index'e dokunmaz)
cut -f1 "$L/eksik.tsv" | sort -u | while read -r c; do
    awk -F'\t' -v c="$c" '$1==c{printf "%s\0",$2}' "$L/eksik.tsv" \
      | xargs -0 -n 300 sh -c 'git --literal-pathspecs archive "$0" -- "$@" | tar -x -C "'"$R"'"' "$c"
done

KALAN=$(cut -f2 "$L/eksik.tsv" | while IFS= read -r f; do [ -e "$f" ] || echo x; done | wc -l)
echo "Geri kondu: $((N-KALAN)) / $N"
# Web sunucusu yazabilsin (yuklemeler ayni klasore devam edecek)
cut -f2 "$L/eksik.tsv" | cut -d/ -f1-2 | sort -u | xargs -r chown -R www-root:www-root 2>/dev/null
echo "Bitti. Log: $L"
