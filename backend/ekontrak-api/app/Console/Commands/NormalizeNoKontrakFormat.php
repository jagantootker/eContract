<?php

namespace App\Console\Commands;

use App\Models\Kontrak;
use Illuminate\Console\Command;

class NormalizeNoKontrakFormat extends Command
{
    protected $signature = 'ekontrak:normalize-no-kontrak {--dry-run : Preview conversion without updating database}';
    protected $description = 'Normalize no_kontrak records to PREFIXNUMBER-YEAR format (example: BTMSH1-2025).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $stats = [
            'total' => 0,
            'converted' => 0,
            'unchanged' => 0,
            'invalid' => 0,
            'collision' => 0,
        ];

        Kontrak::query()->orderBy('id')->chunkById(200, function ($items) use (&$stats, $dryRun) {
            foreach ($items as $kontrak) {
                $stats['total']++;

                $original = strtoupper(trim((string) $kontrak->no_kontrak));
                $normalized = $this->normalizeNoKontrak($original);

                if ($normalized === null) {
                    $stats['invalid']++;
                    $this->warn("[INVALID] ID {$kontrak->id}: {$original}");
                    continue;
                }

                if ($normalized === $original) {
                    $stats['unchanged']++;
                    continue;
                }

                $hasCollision = Kontrak::query()
                    ->where('id', '!=', $kontrak->id)
                    ->where('no_kontrak', $normalized)
                    ->exists();

                if ($hasCollision) {
                    $stats['collision']++;
                    $this->error("[COLLISION] ID {$kontrak->id}: {$original} -> {$normalized}");
                    continue;
                }

                if (! $dryRun) {
                    $kontrak->no_kontrak = $normalized;
                    $kontrak->save();
                }

                $stats['converted']++;
                $label = $dryRun ? '[DRY-RUN]' : '[UPDATED]';
                $this->line("{$label} ID {$kontrak->id}: {$original} -> {$normalized}");
            }
        });

        $this->newLine();
        $this->info('Normalization summary:');
        $this->line("Total: {$stats['total']}");
        $this->line("Converted: {$stats['converted']}");
        $this->line("Unchanged: {$stats['unchanged']}");
        $this->line("Invalid: {$stats['invalid']}");
        $this->line("Collision: {$stats['collision']}");

        if ($dryRun) {
            $this->comment('Dry run only. Re-run without --dry-run to apply updates.');
        }

        return self::SUCCESS;
    }

    private function normalizeNoKontrak(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        if (preg_match('/^([A-Z0-9]+)-(\d{4})$/', $value, $m)) {
            return $m[1] . '-' . $m[2];
        }

        if (preg_match('/^([A-Z]+)\/([A-Z]+)\/(\d{4})\/0*([0-9]+)$/', $value, $m)) {
            $num = ltrim($m[4], '0');
            $num = $num === '' ? '0' : $num;
            return $m[1] . $m[2] . $num . '-' . $m[3];
        }

        if (preg_match('/^([A-Z]+)\/([A-Z]+)\/(\d{4})$/', $value, $m)) {
            return $m[1] . $m[2] . '-' . $m[3];
        }

        return null;
    }
}
