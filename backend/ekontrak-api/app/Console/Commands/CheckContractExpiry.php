<?php

namespace App\Console\Commands;

use App\Models\Kontrak;
use App\Notifications\ContractExpiryNotification;
use Illuminate\Console\Command;

class CheckContractExpiry extends Command
{
    protected $signature   = 'ekontrak:check-expiry';
    protected $description = 'Send expiry notifications for contracts expiring within 2 weeks.';

    public function handle(): int
    {
        $twoWeeksFromNow = now()->addWeeks(2);
        $today           = now()->startOfDay();

        $contracts = Kontrak::with(['pegawaiBertanggungjawab', 'syarikat'])
            ->where('status_kontrak', 'DALAM_PELAKSANAAN')
            ->whereNotNull('tamat_tarikh')
            ->whereBetween('tamat_tarikh', [$today, $twoWeeksFromNow])
            ->get();

        if ($contracts->isEmpty()) {
            $this->info('Tiada kontrak hampir tamat. Tiada notifikasi dihantar.');
            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($contracts as $kontrak) {
            $pegawai = $kontrak->pegawaiBertanggungjawab;

            if (! $pegawai || ! $pegawai->email) {
                $this->warn("Kontrak {$kontrak->no_kontrak}: tiada pegawai bertanggungjawab atau e-mel. Dilangkau.");
                continue;
            }

            try {
                $pegawai->notify(new ContractExpiryNotification($kontrak));
                $this->info("Notifikasi dihantar → {$pegawai->email} ({$kontrak->no_kontrak})");
                $sent++;
            } catch (\Throwable $e) {
                $this->error("Gagal hantar ke {$pegawai->email}: {$e->getMessage()}");
            }
        }

        $this->info("Selesai. {$sent} notifikasi dihantar daripada {$contracts->count()} kontrak.");

        return self::SUCCESS;
    }
}
