<?php
namespace App\Http\Middleware;
use Illuminate\Support\Facades\File;

use Closure;
use Illuminate\Support\Facades\Log;

class LogRequestPerformance
{
    public function handle($request, Closure $next)
    {
         $start = microtime(true);
        $response = $next($request);
        $duration = number_format((microtime(true) - $start) * 1000, 2); // ms

        $route = $request->path();
        $ip = $request->ip();
        $userId = auth()->check() ? auth()->id() : 'giriş yapmamış kullanıcı';
        $timestamp = date('Y-m-d H:i:s');

        // performance log
        Log::channel('performance')->info("Route: $route | Time: {$duration}ms | IP: $ip | UserID: $userId");

        // process counter log
        $line = "$timestamp | IP: $ip | Route: $route | UserID: $userId" . PHP_EOL;
        File::append(storage_path('logs/process_counter.log'), $line);

        return $response;
    }
}
