<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Ifsnop\Mysqldump as IMysqldump;

class DBYedekAl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbyedek:al';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Veritabanı yedeği oluştur';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // env() DEGIL config(): config:cache calisinca Dotenv hic yuklenmez, env()
        // null doner ve DSN 'mysql:host=;port=;dbname=' olup "Missing host from DSN
        // string" ile patlar. config/database.php varsayilanlarla okudugu icin site
        // ayakta kalir, sadece bu komut sessizce olurdu.
        $db = config('database.connections.mysql');

        $dsn = 'mysql:host='.$db['host'].';port='.$db['port'].';dbname='.$db['database'];

        try {
            $dump = new IMysqldump\Mysqldump($dsn, $db['username'], $db['password']);
            $filePath = storage_path('dbyedekler/randevumceptedb-'.date('Y-m-d-H-i-s').'.sql');
            $dump->start($filePath);

            \Log::info('DB backup başarıyla alındı: ' . $filePath);

            // Rotasyon: 7 gunden eski yedekleri sil (disk sismesi onleme).
            // disk:temizle'den bagimsiz, her yedek sonrasi kendi kendini toplar.
            $esik = time() - (7 * 86400);
            $silinen = 0;
            foreach (glob(storage_path('dbyedekler') . '/*.sql') ?: [] as $eski) {
                $mt = @filemtime($eski);
                if ($mt !== false && $mt < $esik && @unlink($eski)) {
                    $silinen++;
                }
            }
            if ($silinen > 0) {
                \Log::info('DB yedek rotasyonu: ' . $silinen . ' eski yedek silindi (>7 gun)');
            }
        } catch (\Exception $e) {
            $this->error('DB backup hatasi: ' . $e->getMessage());
            \Log::error('DB backup hatası: ' . $e->getMessage());
        }
    }
}
