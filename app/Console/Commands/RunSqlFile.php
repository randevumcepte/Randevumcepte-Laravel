<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunSqlFile extends Command
{
    protected $signature = 'db:run-sql {file=storage/app/cinsiyet.sql}';
    protected $description = 'Run a large SQL file without timeout using PDO chunking.';

    public function handle()
    {
        $path = base_path($this->argument('file'));
        if (!file_exists($path)) {
            $this->error("❌ File not found: {$path}");
            return 1;
        }

        $this->info(" Running SQL file: {$path}");
        $content = file_get_contents($path);

        // Dosyayı ";" ile ayır — büyük dosyalarda memory dostu olması için satır satır da yapılabilir
        $queries = explode(";", $content);
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($queries as $query) {
                $query = trim($query);
                if ($query) {
                    DB::statement($query);
                    $count++;

                    // Her 500 sorguda bir commit
                    if ($count % 500 === 0) {
                        DB::commit();
                        DB::beginTransaction();
                        $this->info(" Executed {$count} queries...");
                    }
                }
            }
            DB::commit();
            $this->info("🎉 Completed! Total queries executed: {$count}");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error(" Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
