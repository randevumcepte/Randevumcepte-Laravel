#!/bin/bash
# Deploy script - Cron job ile her dakika calisir
# Flag dosyasi varsa git pull yapar ve flag'i siler

PROJECT_DIR="/var/www/www-root/data/www/randevumceptetest"
FLAG_FILE="$PROJECT_DIR/storage/.deploy-flag"
LOG_FILE="$PROJECT_DIR/storage/logs/deploy.log"

if [ -f "$FLAG_FILE" ]; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Deploy basladi" >> "$LOG_FILE"

    cd "$PROJECT_DIR"
    OUTPUT=$(git pull origin main 2>&1)

    echo "$(date '+%Y-%m-%d %H:%M:%S') - $OUTPUT" >> "$LOG_FILE"

    rm -f "$FLAG_FILE"

    echo "$(date '+%Y-%m-%d %H:%M:%S') - Deploy tamamlandi" >> "$LOG_FILE"
fi
