<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeployController extends Controller
{
    /**
     * GitHub Webhook Secret Key
     * .env dosyasında DEPLOY_SECRET=your_secret_key olarak tanımlayın
     */
    public function webhook(Request $request)
    {
        $secret = env('DEPLOY_SECRET', '');

        if (empty($secret)) {
            Log::error('Deploy: DEPLOY_SECRET .env dosyasında tanımlı değil');
            return response()->json(['error' => 'Server yapılandırma hatası'], 500);
        }

        // GitHub imza doğrulaması
        $signature = $request->header('X-Hub-Signature-256', '');
        if (empty($signature)) {
            Log::warning('Deploy: Signature eksik istek geldi', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Signature eksik'], 403);
        }

        $payload = $request->getContent();
        $hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($hash, $signature)) {
            Log::warning('Deploy: Geçersiz signature', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Geçersiz signature'], 403);
        }

        // Sadece push event
        $event = $request->header('X-GitHub-Event', '');
        if ($event !== 'push') {
            return response()->json(['message' => "Event: $event - deploy gerekmiyor"]);
        }

        $branch = $request->input('ref', '');
        $pusher = $request->input('pusher.name', 'bilinmiyor');
        Log::info("Deploy: Push alındı - Branch: $branch, Pusher: $pusher");

        // Deploy script'i arka planda çalıştır
        $deployScript = base_path('deploy.sh');
        exec("chmod +x $deployScript");
        exec("bash $deployScript > /dev/null 2>&1 &");

        Log::info('Deploy: Script başlatıldı');

        return response()->json([
            'status' => 'ok',
            'message' => 'Deploy başlatıldı',
            'branch' => $branch,
            'pusher' => $pusher,
            'time' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
