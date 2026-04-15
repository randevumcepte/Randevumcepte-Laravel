<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {    
          
         try {
        $dump = new IMysqldump\Mysqldump('mysql:host='.env('DB_HOST').';port='.env('DB_PORT').';dbname='.env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'));
        $filePath = storage_path('dbyedekler/randevumceptedb-'.date('Y-m-d-H-i-s').'.sql');
        $dump->start($filePath);

        \Log::info('DB backup başarıyla alındı: ' . $filePath);
    } catch (\Exception $e) {
        \Log::error('DB backup hatası: ' . $e->getMessage());
    }

     
        
    }
}