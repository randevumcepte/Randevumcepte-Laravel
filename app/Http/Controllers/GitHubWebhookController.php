<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class GitHubWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Gelen imzayı al
        $signature = $request->header('X-Hub-Signature-256');
        if (!$signature) {
            Log::warning('Webhook imzası eksik.');
            return response()->json(['message' => 'Signature missing'], 401);
        }

        // 2. Kendi imzamızı oluştur
        $secret = "I{'6k#TzN7wfR6yHCOd{L)j:<ACe6W";
        $payload = $request->getContent(); // Ham gövdeyi al, çünkü imza ham veri ile oluşturulur.
        $computedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        // 3. İmzaları karşılaştır (zaman saldırılarına karşı güvenli karşılaştırma)
        if (!hash_equals($signature, $computedSignature)) {
            Log::warning('Geçersiz webhook imzası.');
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        // 4. İmza doğrulandı. Şimdi olay tipine göre işlem yapalım.
        $event = $request->header('X-GitHub-Event');
        $payload = $request->all(); // Artık güvenli olduğu için array olarak alabiliriz.

        // 'ping' olayı, webhook kurulduğunda GitHub tarafından gönderilen bir testtir.
        if ($event === 'ping') {
            Log::info('GitHub webhook başarıyla kuruldu!');
            return response()->json(['message' => 'Webhook configured!']);
        }

        // Sadece 'push' olaylarını ve belki de sadece 'main' dalını dinleyelim.
        if ($event === 'push' && ($payload['ref'] ?? '') === 'refs/heads/main') {
            Log::info('Main branch\'ine push algılandı. Deployment başlatılıyor...');

            // !!! BURASI ÇOK ÖNEMLİ !!!
            // Bu komutları arka planda çalıştırmalısınız. Direkt burada çalıştırmak,
            // işlem uzun sürerse GitHub'dan timeout hatası almanıza neden olur.
            //dispatch(function () {
                // Process::run('cd /home/your-site && git pull origin main');
                // Process::run('cd /home/your-site && php artisan migrate --force');
            //})->onQueue('deployments');

            // Basitçe loglayıp geçelim. Gerçek deployment'ı bir Job ile queue'ya atın.
            Log::info('Deployment komutları burada çalışacak.');
        }

        // GitHub her zaman 200 OK yanıtı bekler. Başka bir kod dönerse, webhook'u tekrar dener.
        return response()->json(['message' => 'Webhook received']);
    }
}