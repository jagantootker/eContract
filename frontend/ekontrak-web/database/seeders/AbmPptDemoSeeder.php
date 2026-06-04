<?php

namespace Database\Seeders;

use App\Models\AbmPptUpload;
use App\Models\AbmPptWorkflowHistory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AbmPptDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = ['Bahagian Perolehan', 'Bahagian Kewangan', 'Bahagian ICT', 'Bahagian Pembangunan'];
        $officers = ['Pn. Siti', 'Encik Ahmad', 'Pn. Fatimah', 'Encik Zainal'];
        $templates = ['ABM1', 'ABM2', 'ABM7', 'ABM7A', 'PPT_BARU'];
        $statuses = ['DRAFT', 'SEDANG_DISEMAK', 'DILULUSKAN', 'DITOLAK', 'SELESAI'];

        // Create 10 demo uploads
        for ($i = 1; $i <= 10; $i++) {
            $status = $statuses[($i - 1) % count($statuses)];
            $template = $templates[($i - 1) % count($templates)];

            $data = [];
            for ($j = 1; $j <= rand(5, 15); $j++) {
                $data[] = [
                    'bilangan' => $j,
                    'tahun' => date('Y'),
                    'bahagian' => $departments[array_rand($departments)],
                    'program' => 'Program ' . chr(64 + rand(1, 5)),
                    'kod_objek' => 'KOD' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'jumlah' => number_format(rand(10000, 500000), 2),
                    'pegawai' => $officers[array_rand($officers)],
                    'keterangan' => 'Demo data untuk ' . $template,
                ];
            }

            $upload = AbmPptUpload::create([
                'reference_no' => 'ABM-' . date('Ymd') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'filename' => 'demo_' . $i . '.xlsx',
                'original_filename' => 'Demo_' . $template . '_' . $i . '.xlsx',
                'file_path' => 'abm-ppt-uploads/demo_' . $i . '.xlsx',
                'template_type' => $template,
                'file_type' => 'EXCEL',
                'uploaded_by' => 1,
                'uploaded_by_name' => 'Admin',
                'status' => $status,
                'extraction_data' => $data,
                'total_records' => count($data),
                'year' => date('Y'),
                'department' => $departments[array_rand($departments)],
                'officer_name' => $officers[array_rand($officers)],
            ]);

            // Add workflow history
            AbmPptWorkflowHistory::create([
                'upload_id' => $upload->id,
                'action' => 'UPLOADED',
                'description' => 'Fail telah dimuat naik',
                'performed_by' => 1,
                'performed_by_name' => 'Admin',
                'created_at' => $upload->created_at,
            ]);

            AbmPptWorkflowHistory::create([
                'upload_id' => $upload->id,
                'action' => 'EXTRACTED',
                'description' => 'Data telah diekstrak (' . count($data) . ' rekod)',
                'performed_by' => null,
                'performed_by_name' => 'Sistem',
                'created_at' => $upload->created_at->addMinutes(5),
            ]);

            if ($status !== 'DRAFT') {
                AbmPptWorkflowHistory::create([
                    'upload_id' => $upload->id,
                    'action' => 'VERIFIED',
                    'description' => 'Data telah disahkan',
                    'performed_by' => 1,
                    'performed_by_name' => 'Admin',
                    'created_at' => $upload->created_at->addMinutes(15),
                ]);
            }

            if ($status === 'DILULUSKAN' || $status === 'SELESAI') {
                AbmPptWorkflowHistory::create([
                    'upload_id' => $upload->id,
                    'action' => 'APPROVED',
                    'description' => 'Dokumen telah diluluskan',
                    'performed_by' => 1,
                    'performed_by_name' => 'Admin',
                    'created_at' => $upload->created_at->addMinutes(30),
                ]);
            }

            if ($status === 'DITOLAK') {
                $upload->update([
                    'rejection_reason' => 'Data tidak lengkap atau format tidak sesuai',
                ]);
                AbmPptWorkflowHistory::create([
                    'upload_id' => $upload->id,
                    'action' => 'REJECTED',
                    'description' => 'Dokumen telah ditolak: Data tidak lengkap atau format tidak sesuai',
                    'performed_by' => 1,
                    'performed_by_name' => 'Admin',
                    'created_at' => $upload->created_at->addMinutes(20),
                ]);
            }

            if ($status === 'SELESAI') {
                AbmPptWorkflowHistory::create([
                    'upload_id' => $upload->id,
                    'action' => 'COMPLETED',
                    'description' => 'Proses dokumen telah selesai',
                    'performed_by' => 1,
                    'performed_by_name' => 'Admin',
                    'created_at' => $upload->created_at->addMinutes(60),
                ]);
            }
        }

        $this->command->info('ABM/PPT demo data created successfully!');
    }
}
