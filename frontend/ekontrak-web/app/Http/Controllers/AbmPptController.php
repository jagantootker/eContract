<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Models\AbmPptUpload;
use App\Models\AbmPptWorkflowHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AbmPptController extends Controller
{
    private const DB_FALLBACK_MESSAGE = 'Pangkalan data tidak tersedia (PDO SQLite driver tidak dijumpai). Paparan demo kosong dipaparkan.';

    /**
     * Check if user has access to ABM/PPT module (admin or admin_sistem only)
     */
    private function checkAccess(): void
    {
        $roles = AuthHelper::roles();
        $hasAccess = in_array('admin', $roles, true) || in_array('admin_sistem', $roles, true);
        
        if (!$hasAccess) {
            abort(403, 'Anda tidak mempunyai akses kepada modul ini.');
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // DASHBOARD
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Dashboard ABM/PPT with summary cards
     */
    public function dashboard(): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            $totalUpload = 0;
            $draft = 0;
            $sedangDisemak = 0;
            $diluluskan = 0;
            $ditolak = 0;
            $selesai = 0;
            $recentActivities = collect();
            $recentUploads = collect();
            $dbUnavailableMessage = self::DB_FALLBACK_MESSAGE;

            return view('abm-ppt.dashboard', compact(
                'totalUpload',
                'draft',
                'sedangDisemak',
                'diluluskan',
                'ditolak',
                'selesai',
                'recentActivities',
                'recentUploads',
                'dbUnavailableMessage'
            ));
        }

        $totalUpload = AbmPptUpload::count();
        $draft = AbmPptUpload::where('status', 'DRAFT')->count();
        $sedangDisemak = AbmPptUpload::where('status', 'SEDANG_DISEMAK')->count();
        $diluluskan = AbmPptUpload::where('status', 'DILULUSKAN')->count();
        $ditolak = AbmPptUpload::where('status', 'DITOLAK')->count();
        $selesai = AbmPptUpload::where('status', 'SELESAI')->count();

        $recentActivities = AbmPptWorkflowHistory::with('upload')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentUploads = AbmPptUpload::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('abm-ppt.dashboard', compact(
            'totalUpload',
            'draft',
            'sedangDisemak',
            'diluluskan',
            'ditolak',
            'selesai',
            'recentActivities',
            'recentUploads'
        ));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // UPLOAD/IMPORT
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Show upload page
     */
    public function uploadPage(): View
    {
        $this->checkAccess();
        return view('abm-ppt.upload');
    }

    /**
     * Handle file upload
     */
    public function handleUpload(Request $request): JsonResponse
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return response()->json([
                'success' => false,
                'message' => self::DB_FALLBACK_MESSAGE,
            ], 503);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,pdf|max:10240',
            'template_type' => 'required|in:ABM1,ABM2,ABM3,ABM4,ABM5,ABM6,ABM7,ABM7A,ABM7B,ABM8,PPT_BARU,PPT_KEMAS_KINI',
        ]);

        try {
            $file = $request->file('file');
            $templateType = $request->input('template_type');
            $fileType = strtoupper($file->getClientOriginalExtension()) === 'PDF' ? 'PDF' : 'EXCEL';

            // Generate reference number
            $refNo = 'ABM-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Store file
            $filename = $refNo . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('abm-ppt-uploads', $filename, 'public');

            // Create upload record
            $upload = AbmPptUpload::create([
                'reference_no' => $refNo,
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'template_type' => $templateType,
                'file_type' => $fileType,
                'uploaded_by' => AuthHelper::user()['id'] ?? null,
                'uploaded_by_name' => AuthHelper::userName(),
                'status' => 'DRAFT',
            ]);

            // Log workflow
            $this->logWorkflow($upload->id, 'UPLOADED', 'Fail telah dimuat naik');

            // Simulate extraction
            $extractedData = $this->simulateExtraction($upload, $file);
            $upload->update([
                'extraction_data' => $extractedData,
                'total_records' => count($extractedData),
            ]);

            $this->logWorkflow($upload->id, 'EXTRACTED', 'Data telah diekstrak');

            return response()->json([
                'success' => true,
                'message' => 'Fail telah dimuat naik dengan berjaya',
                'data' => [
                    'id' => $upload->id,
                    'reference_no' => $upload->reference_no,
                    'template_type' => $upload->template_type,
                    'file_type' => $upload->file_type,
                    'total_records' => $upload->total_records,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ralat: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PREVIEW & VERIFICATION
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Show preview page for uploaded file
     */
    public function preview(AbmPptUpload $upload): View
    {
        $this->checkAccess();

        $workflowHistory = AbmPptWorkflowHistory::where('upload_id', $upload->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('abm-ppt.preview', compact('upload', 'workflowHistory'));
    }

    /**
     * Get extracted data for preview (AJAX)
     */
    public function getExtractedData(AbmPptUpload $upload): JsonResponse
    {
        $this->checkAccess();

        return response()->json([
            'success' => true,
            'data' => $upload->extraction_data ?? [],
            'total_records' => $upload->total_records,
        ]);
    }

    /**
     * Save as draft
     */
    public function saveDraft(Request $request, AbmPptUpload $upload): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return response()->json([
                'success' => false,
                'message' => self::DB_FALLBACK_MESSAGE,
            ], 503);
        }

        $extractionData = $this->parseExtractionData($request->input('extraction_data'));

        $payload = [
            'notes' => $request->input('notes'),
            'status' => 'DRAFT',
        ];

        if ($extractionData !== null) {
            $payload['extraction_data'] = $extractionData;
            $payload['total_records'] = count($extractionData);
        }

        $upload->update($payload);

        $this->logWorkflow($upload->id, 'VERIFIED', 'Draf telah disimpan');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Draf telah disimpan',
            ]);
        }

        return redirect()->route('abm.preview', $upload->id)->with('success', 'Draf telah disimpan.');
    }

    /**
     * Submit for verification
     */
    public function submitForVerification(Request $request, AbmPptUpload $upload): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return response()->json([
                'success' => false,
                'message' => self::DB_FALLBACK_MESSAGE,
            ], 503);
        }

        $extractionData = $this->parseExtractionData($request->input('extraction_data'));

        $payload = [
            'status' => 'SEDANG_DISEMAK',
        ];

        if ($request->filled('notes')) {
            $payload['notes'] = $request->input('notes');
        }

        if ($extractionData !== null) {
            $payload['extraction_data'] = $extractionData;
            $payload['total_records'] = count($extractionData);
        }

        $upload->update($payload);

        $this->logWorkflow($upload->id, 'SUBMITTED', 'Diserahkan untuk pengesahan');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Telah diserahkan untuk pengesahan',
            ]);
        }

        return redirect()->route('abm.preview', $upload->id)->with('success', 'Telah diserahkan untuk pengesahan.');
    }

    /**
     * Approve upload
     */
    public function approve(Request $request, AbmPptUpload $upload): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return response()->json([
                'success' => false,
                'message' => self::DB_FALLBACK_MESSAGE,
            ], 503);
        }

        $upload->update([
            'status' => 'DILULUSKAN',
        ]);

        $this->logWorkflow($upload->id, 'APPROVED', 'Telah diluluskan');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Telah diluluskan',
            ]);
        }

        return redirect()->route('abm.preview', $upload->id)->with('success', 'Dokumen telah diluluskan.');
    }

    /**
     * Reject upload
     */
    public function reject(Request $request, AbmPptUpload $upload): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return response()->json([
                'success' => false,
                'message' => self::DB_FALLBACK_MESSAGE,
            ], 503);
        }

        $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        $upload->update([
            'status' => 'DITOLAK',
            'rejection_reason' => $request->input('reason'),
        ]);

        $this->logWorkflow($upload->id, 'REJECTED', 'Ditolak: ' . $request->input('reason'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Telah ditolak',
            ]);
        }

        return redirect()->route('abm.preview', $upload->id)->with('success', 'Dokumen telah ditolak.');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // DOCUMENT MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Document repository (list all documents)
     */
    public function repository(): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            $documents = $this->emptyPaginator(15);
            $dbUnavailableMessage = self::DB_FALLBACK_MESSAGE;
            return view('abm-ppt.repository', compact('documents', 'dbUnavailableMessage'));
        }

        $documents = AbmPptUpload::orderBy('created_at', 'desc')
            ->paginate(15);

        return view('abm-ppt.repository', compact('documents'));
    }

    /**
     * Dedicated viewer page (PDF/Excel)
     */
    public function viewer(AbmPptUpload $upload): View
    {
        $this->checkAccess();

        return view('abm-ppt.viewer', compact('upload'));
    }

    /**
     * Fetch documents (AJAX with filtering)
     */
    public function fetchDocuments(Request $request): JsonResponse
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => (int) $request->input('per_page', 15),
                    'current_page' => (int) $request->input('page', 1),
                    'last_page' => 1,
                ],
                'warning' => self::DB_FALLBACK_MESSAGE,
            ]);
        }

        $query = AbmPptUpload::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('reference_no', 'like', "%$search%")
                  ->orWhere('template_type', 'like', "%$search%")
                  ->orWhere('department', 'like', "%$search%");
        }

        if ($request->filled('template_type')) {
            $query->where('template_type', $request->input('template_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }

        $documents = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $documents->items(),
            'pagination' => [
                'total' => $documents->total(),
                'per_page' => $documents->perPage(),
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
            ]
        ]);
    }

    /**
     * View document (get file info)
     */
    public function viewDocument(AbmPptUpload $upload): JsonResponse
    {
        $this->checkAccess();

        $workflowHistory = AbmPptWorkflowHistory::where('upload_id', $upload->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'document' => [
                'id' => $upload->id,
                'reference_no' => $upload->reference_no,
                'filename' => $upload->filename,
                'original_filename' => $upload->original_filename,
                'file_path' => asset('storage/' . $upload->file_path),
                'template_type' => $upload->template_type,
                'file_type' => $upload->file_type,
                'status' => $upload->status,
                'uploaded_by_name' => $upload->uploaded_by_name,
                'created_at' => $upload->created_at->format('d M Y H:i'),
                'total_records' => $upload->total_records,
            ],
            'workflow' => $workflowHistory->map(fn($h) => [
                'action' => $h->action_label,
                'description' => $h->description,
                'performed_by' => $h->performed_by_name,
                'created_at' => $h->created_at->format('H:i d M Y'),
            ]),
        ]);
    }

    /**
     * Download document
     */
    public function downloadDocument(AbmPptUpload $upload)
    {
        $this->checkAccess();

        $filePath = storage_path('app/public/' . $upload->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'Fail tidak dijumpai');
        }

        return response()->download($filePath, $upload->original_filename);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // WORKFLOW STATUS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Show workflow status page
     */
    public function workflowStatus(AbmPptUpload $upload): View
    {
        $this->checkAccess();

        $workflowHistory = AbmPptWorkflowHistory::where('upload_id', $upload->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('abm-ppt.workflow-status', compact('upload', 'workflowHistory'));
    }

    /**
     * Workflow status list page
     */
    public function statusProses(): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            $uploads = $this->emptyPaginator(20);
            $dbUnavailableMessage = self::DB_FALLBACK_MESSAGE;
            return view('abm-ppt.status-proses', compact('uploads', 'dbUnavailableMessage'));
        }

        $uploads = AbmPptUpload::orderBy('updated_at', 'desc')->paginate(20);
        return view('abm-ppt.status-proses', compact('uploads'));
    }

    /**
     * Get workflow progress (AJAX)
     */
    public function getWorkflowProgress(AbmPptUpload $upload): JsonResponse
    {
        $this->checkAccess();

        $workflow = [
            ['step' => 1, 'name' => 'Dimuat Naik', 'status' => 'UPLOADED', 'completed' => true],
            ['step' => 2, 'name' => 'Diekstrak', 'status' => 'EXTRACTED', 'completed' => true],
            ['step' => 3, 'name' => 'Disahkan', 'status' => 'VERIFIED', 'completed' => $upload->status !== 'DRAFT'],
            ['step' => 4, 'name' => 'Diluluskan', 'status' => 'APPROVED', 'completed' => $upload->status === 'DILULUSKAN' || $upload->status === 'SELESAI'],
            ['step' => 5, 'name' => 'Selesai', 'status' => 'COMPLETED', 'completed' => $upload->status === 'SELESAI'],
        ];

        if ($upload->status === 'DITOLAK') {
            $workflow[3]['rejected'] = true;
        }

        return response()->json([
            'success' => true,
            'workflow' => $workflow,
            'current_status' => $upload->status_label,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // HELPER METHODS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Log workflow action
     */
    private function logWorkflow(int $uploadId, string $action, string $description, ?int $performedBy = null): void
    {
        AbmPptWorkflowHistory::create([
            'upload_id' => $uploadId,
            'action' => $action,
            'description' => $description,
            'performed_by' => $performedBy ?? (AuthHelper::user()['id'] ?? null),
            'performed_by_name' => AuthHelper::userName(),
        ]);
    }

    /**
     * Simulate file extraction (demo data parser)
     * In production, this would use PhpSpreadsheet or PDF parser
     */
    private function simulateExtraction(AbmPptUpload $upload, $file): array
    {
        // Demo data - in production, would parse actual file
        $demoData = [];

        // Generate realistic demo data based on template type
        $recordCount = rand(5, 20);

        $departments = ['Bahagian Perolehan', 'Bahagian Kewangan', 'Bahagian ICT', 'Bahagian Pembangunan'];
        $officers = ['Pn. Siti', 'Encik Ahmad', 'Pn. Fatimah', 'Encik Zainal'];

        for ($i = 1; $i <= $recordCount; $i++) {
            $demoData[] = [
                'bilangan' => $i,
                'tahun' => date('Y'),
                'bahagian' => $departments[array_rand($departments)],
                'program' => 'Program ' . chr(64 + rand(1, 5)),
                'kod_objek' => 'KOD' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'jumlah' => number_format(rand(10000, 500000), 2),
                'pegawai' => $officers[array_rand($officers)],
                'keterangan' => 'Demo data untuk ' . $upload->template_type_label,
            ];
        }

        return $demoData;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Management page - Pengurusan ABM/PPT
     */
    public function management(): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            $uploads = $this->emptyPaginator(20);
            $stats = [
                'total' => 0,
                'draft' => 0,
                'sedang_disemak' => 0,
                'diluluskan' => 0,
                'ditolak' => 0,
                'selesai' => 0,
            ];
            $dbUnavailableMessage = self::DB_FALLBACK_MESSAGE;
            return view('abm-ppt.management', compact('uploads', 'stats', 'dbUnavailableMessage'));
        }

        $uploads = AbmPptUpload::orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => AbmPptUpload::count(),
            'draft' => AbmPptUpload::where('status', 'DRAFT')->count(),
            'sedang_disemak' => AbmPptUpload::where('status', 'SEDANG_DISEMAK')->count(),
            'diluluskan' => AbmPptUpload::where('status', 'DILULUSKAN')->count(),
            'ditolak' => AbmPptUpload::where('status', 'DITOLAK')->count(),
            'selesai' => AbmPptUpload::where('status', 'SELESAI')->count(),
        ];

        return view('abm-ppt.management', compact('uploads', 'stats'));
    }

    /**
     * Management page filtered for PPT templates
     */
    public function managementPpt(): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            $uploads = $this->emptyPaginator(20);
            $stats = [
                'total' => 0,
                'draft' => 0,
                'sedang_disemak' => 0,
                'diluluskan' => 0,
                'ditolak' => 0,
                'selesai' => 0,
            ];
            $dbUnavailableMessage = self::DB_FALLBACK_MESSAGE;

            return view('abm-ppt.management', [
                'uploads' => $uploads,
                'stats' => $stats,
                'title' => 'Pengurusan PPT',
                'dbUnavailableMessage' => $dbUnavailableMessage,
            ]);
        }

        $uploads = AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])->count(),
            'draft' => AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])->where('status', 'DRAFT')->count(),
            'sedang_disemak' => AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])->where('status', 'SEDANG_DISEMAK')->count(),
            'diluluskan' => AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])->where('status', 'DILULUSKAN')->count(),
            'ditolak' => AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])->where('status', 'DITOLAK')->count(),
            'selesai' => AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])->where('status', 'SELESAI')->count(),
        ];

        return view('abm-ppt.management', [
            'uploads' => $uploads,
            'stats' => $stats,
            'title' => 'Pengurusan PPT',
        ]);
    }

    /**
     * Check DB connectivity and driver readiness.
     */
    private function isDatabaseReady(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Return an empty paginator for fallback screens.
     */
    private function emptyPaginator(int $perPage): LengthAwarePaginator
    {
        $currentPage = max((int) request()->get('page', 1), 1);
        return new LengthAwarePaginator(new Collection(), 0, $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    /**
     * Parse extraction data payload from hidden form input.
     */
    private function parseExtractionData(mixed $raw): ?array
    {
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? array_values($decoded) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
