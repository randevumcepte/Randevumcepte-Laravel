<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * AI santral çağrı loglarını terminalde göster — teşhis için.
 *
 *   php artisan ai:loglar                 → son 20 çağrı özeti
 *   php artisan ai:loglar --salon=15      → sadece o salon
 *   php artisan ai:loglar --id=42         → tek çağrının TÜM turlarını (müşteri
 *                                            ne dedi / Whisper ne yazdı / AI ne yaptı)
 *   php artisan ai:loglar --son=50        → son 50 çağrı
 */
class AiCagriLoglari extends Command
{
    protected $signature = 'ai:loglar {--salon=} {--id=} {--son=20}';
    protected $description = 'AI santral çağrı loglarını göster (teşhis)';

    public function handle()
    {
        if (!Schema::hasTable('ai_cagri_loglari')) {
            $this->error('ai_cagri_loglari tablosu yok. Önce: php artisan migrate');
            return 1;
        }

        // Tek çağrı detayı
        if ($this->option('id')) {
            return $this->detay((int) $this->option('id'));
        }

        $q = DB::table('ai_cagri_loglari')->orderByDesc('id');
        if ($this->option('salon')) {
            $q->where('salon_id', (int) $this->option('salon'));
        }
        $limit = max(1, (int) $this->option('son'));
        $loglar = $q->limit($limit)->get();

        if ($loglar->isEmpty()) {
            $this->info('Henüz çağrı logu yok.');
            return 0;
        }

        $rows = [];
        foreach ($loglar as $l) {
            $rows[] = [
                $l->id,
                $l->created_at,
                $l->salon_id,
                $l->caller_telefon,
                $l->durum,
                $l->tur_sayisi,
                ($l->toplam_sure_sn ?? '?') . 's',
                mb_substr((string) $l->sonuc, 0, 45),
            ];
        }
        $this->table(
            ['id', 'tarih', 'salon', 'telefon', 'durum', 'tur', 'süre', 'sonuç'],
            $rows
        );
        $this->line('Detay için: php artisan ai:loglar --id=<id>');
        return 0;
    }

    private function detay($id)
    {
        $log = DB::table('ai_cagri_loglari')->where('id', $id)->first();
        if (!$log) {
            $this->error("Çağrı #{$id} bulunamadı.");
            return 1;
        }

        $this->line('');
        $this->info("═══ Çağrı #{$log->id} ═══");
        $this->line("Salon: {$log->salon_id}   Telefon: {$log->caller_telefon}   Durum: {$log->durum}");
        $this->line("Süre: " . ($log->toplam_sure_sn ?? '?') . "s   Tur: {$log->tur_sayisi}   Sonuç: {$log->sonuc}");
        $this->line("Gecikme toplam — STT: {$log->stt_ms_toplam}ms  LLM: {$log->llm_ms_toplam}ms  TTS: {$log->tts_ms_toplam}ms");
        $this->line('');

        $turlar = DB::table('ai_cagri_turlari')->where('cagri_log_id', $id)->orderBy('tur_no')->get();
        foreach ($turlar as $t) {
            $this->line("── Tur {$t->tur_no} ──");
            if ($t->kullanici_metni) {
                $this->line("  👤 Müşteri : " . $t->kullanici_metni);
            }
            if ($t->tool_cagrilari) {
                $tools = json_decode($t->tool_cagrilari, true) ?: [];
                foreach ($tools as $tc) {
                    $ad = $tc['name'] ?? '?';
                    $ozet = $tc['ozet'] ?? '';
                    $this->line("  🔧 Tool   : {$ad} → {$ozet}");
                }
            }
            if ($t->asistan_metni) {
                $this->line("  🤖 Asistan: " . $t->asistan_metni);
            }
            $this->line("     (stt {$t->stt_ms}ms · llm {$t->llm_ms}ms · tts {$t->tts_ms}ms)");
        }
        $this->line('');
        return 0;
    }
}
