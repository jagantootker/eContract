<?php

namespace App\Console\Commands;

use App\Models\Syarikat;
use Illuminate\Console\Command;

class FillEmptyRequiredSyarikatFields extends Command
{
    protected $signature = 'ekontrak:fill-empty-required-syarikat-fields';
    protected $description = 'Fill empty required fields in syarikat table with default/sample data.';

    public function handle(): int
    {
        $updated = 0;
        $defaults = [
            'nama_syarikat' => 'Syarikat Dummy',
            'alamat' => 'Alamat Dummy',
            'negeri' => 'Selangor',
            'pegawai_hubungi_1_nama' => 'Pegawai Dummy',
            'pegawai_hubungi_1_email' => 'dummy@email.com',
            'pegawai_hubungi_1_tel_pejabat' => '0388751234',
            'pegawai_hubungi_1_tel_hp' => '0123456789',
        ];

        $syarikats = Syarikat::all();
        foreach ($syarikats as $syarikat) {
            $changed = false;
            foreach ($defaults as $field => $value) {
                if (empty($syarikat->$field)) {
                    $syarikat->$field = $value;
                    $changed = true;
                }
            }
            if ($changed) {
                $syarikat->save();
                $updated++;
                $this->info("Updated syarikat ID {$syarikat->id}");
            }
        }
        $this->info("Total syarikat updated: $updated");
        return 0;
    }
}
