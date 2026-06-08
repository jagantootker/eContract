# Prompt 05 — ekontrak-web: Laman Utama (Dashboard)

## Context
Build the Dashboard (Laman Utama) for `ekontrak-web`. Two different dashboard views depending on user role. Data fetched from `ekontrak-api` via ApiService.

---

## Route

```php
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
```

**Controller**: `DashboardController@index`
- Call `GET /api/v1/dashboard` via ApiService
- Call `GET /api/v1/dashboard/alerts` via ApiService
- Pass data to view
- Show different view based on role:
  - Roles `pendaftar_kontrak`, `pemilik_projek`, `admin` → `dashboard.pegawai`
  - Role `pegawai_undang_undang` → `dashboard.undang_undang`

---

## View 1: `dashboard.pegawai` (Pendaftar Kontrak / Pemilik Projek)

**Blade**: `resources/views/dashboard/pegawai.blade.php`

### Status Kontrak Summary Cards

Display 6 stat cards in a horizontal row:

| Card | Label | Value | Colour |
|---|---|---|---|
| 1 | Maklumat Tidak Lengkap | dynamic | Orange/amber with doc icon |
| 2 | Draf Kontrak | dynamic | Grey with draft icon |
| 3 | Dalam Pelaksanaan | dynamic | Blue with gear icon |
| 4 | Extension of Time (EOT) | dynamic | Purple with clock icon |
| 5 | Kontrak Selesai | dynamic | Green with check icon |
| 6 | **Jumlah Keseluruhan** | dynamic | **Dark blue, highlighted/larger** |

Each card: icon (top), number (large bold), label (small text below).
Card 6 (`Jumlah Keseluruhan`) should have a distinct blue background to stand out.

Clicking "Maklumat Tidak Lengkap" card → opens the **Maklumat Tidak Lengkap** modal.
Clicking "Kontrak Selesai" → opens the **Kontrak Selesai** modal.

---

### Alert Sections (Accordion/Collapsible)

Three collapsible sections below the cards:

**Section 1 — Red/danger** (triangle warning icon):
> `({count} Kontrak) — Tempoh Tandatangan Draf Kontrak Telah Tamat (Sila Mohon EGHA)`

**Section 2 — Amber/warning** (clock icon):
> `({count} Kontrak) — Tempoh Tandatangan Draf Kontrak Akan Tamat (Dalam tempoh 2 minggu) — {tarikh_mula} sehingga {tarikh_tamat}`

**Section 3 — Green/info** (check icon, expanded by default):
> `({count} Kontrak) — Kontrak Aktif (Dalam tempoh 6 bulan) — {tarikh_mula} sehingga {tarikh_tamat}`

When expanded, each section shows a table:
| # | ↕ Tajuk Kontrak | No.Kontrak | Tempoh Kontrak | ✦ Pemilik Projek |
|---|---|---|---|---|

Rows are clickable → opens Perincian Kontrak modal/page.

---

### Modal: Maklumat Tidak Lengkap

Triggered when clicking the "Maklumat Tidak Lengkap" stat card.

**Modal title**: "Maklumat Tidak Lengkap" | subtitle: "Senarai kontrak dengan maklumat tidak lengkap"

**Action buttons** (top of modal table):
- `🖨 Print` | `📄 PDF` | `📊 Excel`
- Search input: "Cari tajuk, no kontrak, pemilik..."
- **Filter: by tahun** — dropdown (current year and past years)

**Table columns**:
| # | TAJUK KONTRAK | NO KONTRAK | PEMILIK PROJEK | TEMPOH KONTRAK |
|---|---|---|---|---|

Tajuk is a hyperlink → opens Perincian Kontrak.
Pagination at bottom.

API call: `GET /api/v1/dashboard/maklumat-tidak-lengkap?tahun={year}&search={q}&page={p}`

---

### Modal: Kontrak Selesai

**Modal title**: "Kontrak Selesai" | subtitle: "Senarai kontrak yang telah selesai"

Same action buttons + table as above.

API call: `GET /api/v1/dashboard/kontrak-selesai?tahun={year}&search={q}&page={p}`

---

## View 2: `dashboard.undang_undang` (Pegawai Undang-Undang)

**Blade**: `resources/views/dashboard/undang_undang.blade.php`

### Summary Table

Title: **Laman Utama** | subtitle: "Paparan ringkasan status kontrak mengikut jabatan."

A matrix/grid table:

**Rows**:
- Draf Kontrak
- Extension Of Time (EOT) Kontrak
- JUMLAH KESELURUHAN (bold, last row)

**Columns** (one per department code + total):
`BKK | BKT | BIS | BUK | BTM | JKT | JPET | APM | JKT | JUN | PIN | PLANM | TPPS | URS | JUMLAH`

Each cell: numeric count of contracts matching that status + department.
Header row is dark navy. Data rows alternate white/light grey. Last column and last row bolded.

### Alert Sections (same as Pegawai view, below the table)

Two alert sections:
- Red: Contracts overdue (Tempoh Tandatangan Draf Kontrak Telah Tamat)
- Amber: Contracts expiring in 2 weeks

---

## Laporan A Table (shown in same view for Pegawai Undang-Undang)

When user navigates to `/laporan` → shows "Pemantauan Status Kontrak Ditandatangan (Lampiran A)".

This view shows:
- Filter by tahun mula and tamat (year range)
- Print, PDF, Excel export buttons
- Search bar

**Table columns**:
| # | JABATAN/BAHAGIAN | BAHAGIAN/UNIT | TAJUK PEROLEHAN | KAEDAH PEROLEHAN | TARIKH SST | TARIKH SST SEMULA TERIMA | NAMA PEMBEKAL | TELAH DRAF/TANDATANGAN SISTEM |
|---|---|---|---|---|---|---|---|---|

---

## JavaScript / AJAX Behaviour

1. **Stat cards**: Click → open relevant modal, auto-fetch data
2. **Accordion alerts**: Click header → expand/collapse (CSS transition)
3. **Export buttons** (Print/PDF/Excel):
   - Print: `window.print()` on the modal table
   - PDF: Call API endpoint that returns PDF (or use `jsPDF` library client-side)
   - Excel: Trigger download via API endpoint or client-side `SheetJS`
4. **Pagination in modals**: AJAX, replace table content
5. **Year filter**: On change → refetch data and repopulate table

---

## DashboardController

```php
class DashboardController extends Controller {
    public function index() {
        $role = AuthHelper::roles();
        $data = $this->apiService->withAuth()->get('/dashboard');
        $alerts = $this->apiService->withAuth()->get('/dashboard/alerts');
        
        if (in_array('pegawai_undang_undang', $role)) {
            return view('dashboard.undang_undang', compact('data', 'alerts'));
        }
        return view('dashboard.pegawai', compact('data', 'alerts'));
    }
    
    public function getMaklumatTidakLengkap(Request $request) {
        // AJAX — returns JSON for modal table
        $result = $this->apiService->withAuth()->get('/dashboard/maklumat-tidak-lengkap', $request->all());
        return response()->json($result);
    }
    
    public function getKontrakSelesai(Request $request) {
        $result = $this->apiService->withAuth()->get('/dashboard/kontrak-selesai', $request->all());
        return response()->json($result);
    }
}
```
