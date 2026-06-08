# Prompt 08 — ekontrak-web: Laporan (Reports Module)

## Context
Build the Reports module for `ekontrak-web`. Accessible to all authenticated users but with different data scope per role.

---

## Page: Laporan (`/laporan`)

**Blade**: `resources/views/laporan/index.blade.php`

**All authenticated roles** can access.

---

### Page Header

Title: **Laporan**
Subtitle: "Senarai laporan pemantauan kontrak yang tersedia."

Section: `> Senarai Laporan`

**List of available reports** (simple numbered list with hyperlinks):

| # | NAMA LAPORAN |
|---|---|
| 1 | Pemantauan Status Kontrak Ditandatangani (Lampiran A) |
| 2 | Pemantauan Tempoh Kontrak (Lampiran B) |

Clicking either report link opens the respective report view.

---

## Page: Lampiran A (`/laporan/lampiran-a`)

**Blade**: `resources/views/laporan/lampiran_a.blade.php`

### Header / Filter Bar

- Breadcrumb: `← Kembali ke Senarai Laporan`
- Section: `> Pemantauan Status Kontrak Ditandatangani (Lampiran A)`

**Filter row**:
- `Carian by tahun mula dan tamat` label (note in red, as in wireframe)
- `Tahun Mula` — year dropdown (past 10 years)
- `Tahun Tamat` — year dropdown
- Search input: `Q` icon, placeholder "Cari..."

**Export buttons**: `🖨 Print` | `📄 PDF` | `📊 Excel`

---

### Table

**Columns**:
| # | JABATAN / BAHAGIAN | BAHAGIAN / UNIT | TAJUK PEROLEHAN | KAEDAH PEROLEHAN | TARIKH SST | TARIKH SST SEMULA/TARIKH TERIMA | NAMA PEMBEKAL | TELAH DRAF TANDATANGAN SISTEM |
|---|---|---|---|---|---|---|---|---|

All columns sortable (click header → toggle ASC/DESC, re-fetch with `?sort=column&order=asc`).

Row data mapped from API `/api/v1/laporan/lampiran-a`.

---

## Page: Lampiran B (`/laporan/lampiran-b`)

**Blade**: `resources/views/laporan/lampiran_b.blade.php`

### Header / Filter Bar

Same filter pattern as Lampiran A (tahun mula, tahun tamat, search, export buttons).

### Table

**Columns**:
| # | JABATAN / BAHAGIAN | BAHAGIAN / UNIT | TAJUK PEROLEHAN | KAEDAH PEROLEHAN | TARIKH MULA | TARIKH TAMAT | TEMPOH (BULAN) | NAMA PEMBEKAL | STATUS |
|---|---|---|---|---|---|---|---|---|---|

---

## Export Functionality

### Print
```javascript
function printTable() {
    const printArea = document.getElementById('report-table').outerHTML;
    const win = window.open('', '_blank');
    win.document.write(`<html><head><title>Laporan</title>
        <style>table{border-collapse:collapse;width:100%}td,th{border:1px solid #000;padding:4px;font-size:11px}</style>
        </head><body>${printArea}</body></html>`);
    win.print();
}
```

### PDF Export
Use server-side approach: create `/laporan/lampiran-a/export-pdf` route that:
1. Fetches all data (no pagination) from API
2. Renders a minimal Blade view (`laporan/pdf/lampiran_a.blade.php`)
3. Returns PDF using `barryvdh/laravel-dompdf`:
```bash
composer require barryvdh/laravel-dompdf
```
```php
return PDF::loadView('laporan.pdf.lampiran_a', $data)
    ->setPaper('a4', 'landscape')
    ->download('Lampiran_A_' . date('Ymd') . '.pdf');
```

### Excel Export
Use server-side approach with `maatwebsite/excel`:
```bash
composer require maatwebsite/excel
```
Create `LampiranAExport` class implementing `FromCollection` and `WithHeadings`.
Route: `/laporan/lampiran-a/export-excel` → triggers download.

---

## LaporanController

```php
class LaporanController extends Controller {
    
    public function index() {
        return view('laporan.index');
    }
    
    public function lampiranA(Request $request) {
        $params = $request->only(['tahun_mula', 'tahun_tamat', 'search', 'sort', 'order', 'page']);
        $result = $this->apiService->withAuth()->get('/laporan/lampiran-a', $params);
        return view('laporan.lampiran_a', ['data' => $result['data']]);
    }
    
    public function lampiranB(Request $request) {
        $params = $request->only(['tahun_mula', 'tahun_tamat', 'search', 'sort', 'order', 'page']);
        $result = $this->apiService->withAuth()->get('/laporan/lampiran-b', $params);
        return view('laporan.lampiran_b', ['data' => $result['data']]);
    }
    
    public function exportPdfA(Request $request) {
        $result = $this->apiService->withAuth()->get('/laporan/lampiran-a', array_merge($request->all(), ['per_page' => 9999]));
        $pdf = PDF::loadView('laporan.pdf.lampiran_a', ['records' => $result['data']['records']]);
        return $pdf->setPaper('a4', 'landscape')->download('LampiranA.pdf');
    }
    
    public function exportExcelA(Request $request) {
        return Excel::download(new LampiranAExport($this->apiService, $request->all()), 'LampiranA.xlsx');
    }
    
    // Same for B...
}
```

## Routes

```php
Route::middleware('auth.session')->prefix('laporan')->group(function () {
    Route::get('/', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/lampiran-a', [LaporanController::class, 'lampiranA'])->name('laporan.a');
    Route::get('/lampiran-b', [LaporanController::class, 'lampiranB'])->name('laporan.b');
    Route::get('/lampiran-a/export-pdf', [LaporanController::class, 'exportPdfA']);
    Route::get('/lampiran-a/export-excel', [LaporanController::class, 'exportExcelA']);
    Route::get('/lampiran-b/export-pdf', [LaporanController::class, 'exportPdfB']);
    Route::get('/lampiran-b/export-excel', [LaporanController::class, 'exportExcelB']);
});
```
