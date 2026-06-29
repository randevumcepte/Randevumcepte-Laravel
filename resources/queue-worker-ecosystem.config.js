// PM2 ecosystem — Santral (FreePBX) hatırlatma araması kuyruk işleyicisi.
//
// Reklam (kampanyaarama:yap), randevu (randevuarama:yap) ve alacak
// (alacakhatirlatma:aramayap) komutları arama işlerini HatirlatmaAramaJob ile
// `database` kuyruğuna (queue: hatirlatmalar / notifications) ekler. Bu süreç
// o işleri çekip Asterisk AMI Originate'i gerçekleştirir.
//
// Kurulum (prod sunucu, proje kökünde):
//   pm2 start resources/queue-worker-ecosystem.config.js
//   pm2 save
//   pm2 startup        # çıkan komutu çalıştır (yeniden başlatmada otomatik açılır)
//
// Deploy sonrası deploy.sh `php artisan queue:restart` çağırır; pm2 süreci
// canlı kalır, worker güncel kodla işe devam eder.
//
// NOT: --timeout (1700) config/queue.php database.retry_after (1800) değerinden
// KÜÇÜK olmalı; aksi halde uzun süren bir iş kuyruğa geri düşüp aynı numaralar
// MÜKERRER aranır.

module.exports = {
  apps: [
    {
      name: 'randevumcepte-arama-worker',
      script: 'artisan',
      interpreter: '/opt/php74/bin/php',
      args: 'queue:work database --queue=hatirlatmalar,notifications --sleep=3 --tries=1 --timeout=1700',
      cwd: __dirname + '/..',
      instances: 1,
      exec_mode: 'fork',
      autorestart: true,
      max_restarts: 20,
      min_uptime: '30s',
      max_memory_restart: '300M',
      watch: false,
      error_file: './storage/logs/arama-worker-err.log',
      out_file: './storage/logs/arama-worker-out.log',
      merge_logs: true,
      time: true,
    },
  ],
};
