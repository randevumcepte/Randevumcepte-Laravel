<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
 use App\Http\Controllers\Controller;
use App\Salonlar;



class NLPTokenGuncelle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nlptoken:guncelle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nlp token güncellemesi';

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
        $controller = app()->make(Controller::class);
         foreach(Salonlar::all() as $isletme)
         {
            if($isletme->nlp_token == null || date('Y-m-d H:i:s',strtotime($isletme->nlp_token_expires)) < date('Y-m-d H:i:s'))
            {
                $controller->getAccessTokenNLP($isletme->id);
            }
         }

     
        
    }
}