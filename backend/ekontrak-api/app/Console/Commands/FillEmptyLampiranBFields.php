<?php

namespace App\Console\Commands;

use App\Models\Kontrak;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FillEmptyLampiranBFields extends Command
{
    protected $signature   = 'laporan:fill-lampiran-b';
    protected $description = 'Fill empty Lampiran B fields (tarikh_draf_hantar_sistem, catatan_kontrak) for existing kontrak records';

    public function handle(): int
    {
        $updated = 0;

        Kontrak::whereNotNull('mula_tarikh')
            ->whereNotNull('tamat_tarikh')
            ->get()
            ->each(function (Kontrak $k) use (&$updated): void {
                $changed = false;

                // Fill tarikh_draf_hantar_sistem from tarikh_sst if empty
                if (empty($k->tarikh_draf_hantar_sistem) && ! empty($k->tarikh_sst)) {
                    $k->tarikh_draf_hantar_sistem = $k->tarikh_sst instanceof \Carbon\Carbon
                        ? $k->tarikh_sst->copy()->addDays(7)
                        : Carbon::parse($k->tarikh_sst)->addDays(7);
                    $changed = true;
                }

                // Fill tarikh_draf_hantar_sistem from mula_tarikh if still empty
                if (empty($k->tarikh_draf_hantar_sistem) && ! empty($k->mula_tarikh)) {
                    $mulaDate = $k->mula_tarikh instanceof \Carbon\Carbon
                        ? $k->mula_tarikh
                        : Carbon::parse($k->mula_tarikh);
                    $k->tarikh_draf_hantar_sistem = $mulaDate->copy()->subDays(14);
                    $changed = true;
                }

                // Fill catatan_kontrak for records that have a signing date
                if (empty($k->catatan_kontrak) && ! empty($k->ditandatangani_tarikh)) {
                    $signedDate = $k->ditandatangani_tarikh instanceof \Carbon\Carbon
                        ? $k->ditandatangani_tarikh
                        : Carbon::parse($k->ditandatangani_tarikh);

                    $stampDate = ! empty($k->tarikh_sst)
                        ? ($k->tarikh_sst instanceof \Carbon\Carbon ? $k->tarikh_sst : Carbon::parse($k->tarikh_sst))
                        : $signedDate->copy()->subDays(1);

                    $k->catatan_kontrak = 'Kontrak Telah Selesai Ditandatangani Pada '
                        . $signedDate->format('d.m.Y')
                        . ' Dan Distamping Pada '
                        . $stampDate->format('d.m.Y') . '.';
                    $changed = true;
                }

                if ($changed) {
                    $k->save();
                    $updated++;
                }
            });

        $this->info("Done. Updated {$updated} record(s).");

        return self::SUCCESS;
    }
}
