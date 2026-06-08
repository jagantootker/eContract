<?php

namespace App\Console\Commands;

use App\Models\Kontrak;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillEmptyRequiredKontrakFields extends Command
{
    protected $signature = 'ekontrak:fill-empty-required-kontrak-fields';
    protected $description = 'Fill empty required fields in kontrak table with default/sample data.';

    public function handle(): int
    {
        $updated = 0;
        $defaults = [
            'no_kontrak' => 'DUMMY-' . date('Y'),
            'status_kontrak' => 'DRAF',
            'tajuk_kontrak' => 'Tajuk Kontrak Dummy',
            'syarikat_id' => 1, // Make sure ID 1 exists or adjust accordingly
            'nilai_kontrak' => 1.00,
            'kaedah_perolehan' => 'SEBUT HARGA',
            'kategori_perolehan' => 'PERKHIDMATAN',
            'mula_tarikh' => now()->toDateString(),
            'tamat_tarikh' => now()->addMonth()->toDateString(),
            'pegawai_bertanggungjawab_id' => 1, // Make sure ID 1 exists or adjust accordingly
        ];

        $kontraks = Kontrak::all();
        foreach ($kontraks as $kontrak) {
            $changed = false;
            foreach ($defaults as $field => $value) {
                if (empty($kontrak->$field)) {
                    $kontrak->$field = $value;
                    $changed = true;
                }
            }
            // Special: tamat_tarikh must be after_or_equal mula_tarikh
            if (strtotime($kontrak->tamat_tarikh) < strtotime($kontrak->mula_tarikh)) {
                $kontrak->tamat_tarikh = $kontrak->mula_tarikh;
                $changed = true;
            }
            if ($changed) {
                $kontrak->save();
                $updated++;
                $this->info("Updated kontrak ID {$kontrak->id}");
            }
        }
        $this->info("Total kontrak updated: $updated");
        return 0;
    }
}
