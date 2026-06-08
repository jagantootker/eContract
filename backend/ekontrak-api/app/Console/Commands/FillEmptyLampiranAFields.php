<?php

namespace App\Console\Commands;

use App\Models\Kontrak;
use Illuminate\Console\Command;

class FillEmptyLampiranAFields extends Command
{
    protected $signature = 'ekontrak:fill-empty-lampiran-a-fields';

    protected $description = 'Fill empty Lampiran A related fields in kontrak table with safe defaults.';

    public function handle(): int
    {
        $updated = 0;

        Kontrak::query()->chunkById(200, function ($rows) use (&$updated) {
            foreach ($rows as $kontrak) {
                $changed = false;

                if (empty($kontrak->tarikh_sst)) {
                    $fallbackTarikhSst = $kontrak->diluluskan_tarikh
                        ?? $kontrak->mula_tarikh
                        ?? now();
                    $kontrak->tarikh_sst = $fallbackTarikhSst;
                    $changed = true;
                }

                if (empty($kontrak->tarikh_draf_hantar_sistem) && ! empty($kontrak->tarikh_sst)) {
                    $kontrak->tarikh_draf_hantar_sistem = $kontrak->tarikh_sst->copy()->addDays(6);
                    $changed = true;
                }

                if (empty($kontrak->status_kontrak)) {
                    $kontrak->status_kontrak = 'DALAM_PELAKSANAAN';
                    $changed = true;
                }

                if (empty($kontrak->catatan_kontrak)) {
                    if (! empty($kontrak->ditandatangani_tarikh)) {
                        $tarikhTanda = $kontrak->ditandatangani_tarikh->format('d.m.Y');
                        $kontrak->catatan_kontrak = "Kontrak telah selesai ditandatangani pada {$tarikhTanda}.";
                    } elseif (! empty($kontrak->tarikh_draf_hantar_sistem) && now()->greaterThan($kontrak->tarikh_draf_hantar_sistem->copy()->addMonths(3))) {
                        $kontrak->catatan_kontrak = 'Masih belum ditandatangani selepas 3 bulan dari tarikh SST disetuju terima.';
                    } else {
                        $kontrak->catatan_kontrak = 'Tiada catatan.';
                    }
                    $changed = true;
                }

                if ($changed) {
                    $kontrak->save();
                    $updated++;
                }
            }
        });

        $this->info("Jumlah rekod kontrak dikemas kini: {$updated}");

        return self::SUCCESS;
    }
}
