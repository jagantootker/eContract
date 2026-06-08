# Prompt 06 — ekontrak-web: Senarai & Perincian Kontrak

## Context
Build the Contract List and Contract Detail modules for `ekontrak-web`. Data via ApiService to `ekontrak-api`.

---

## Page: Senarai Kontrak (`/kontrak`)

**Route**: `GET /kontrak`

**Blade**: `resources/views/kontrak/index.blade.php`

**Roles allowed**: `pendaftar_kontrak`, `pemilik_projek`, `admin`

---

### Page Header

Title: **Senarai Kontrak**
Subtitle: "Senarai semua kontrak di bawah pengurusan anda."

**Search bar** (full width): placeholder "Cari kontrak..." — searches by No Kontrak, Tajuk Kontrak, Pemilik Kontrak, Syarikat, Status Kontrak

Note in red above search: "carian by tahun kontrak daripada tahun mula dan tamat" (visible as a hint label near the search)

Action button (top right): `+ Tambah Kontrak` — primary blue

---

### Table

**Columns**:
| # | ↕ TAJUK KONTRAK | NO KONTRAK | ✦ PEMILIK PROJEK | ⏱ TEMPOH KONTRAK | STATUS | STATUS DRAF KOMPAN | TARIKH TAMAT DIMASUKKAN SISTEM |
|---|---|---|---|---|---|---|---|

**Row details**:
- `TAJUK KONTRAK`: Blue hyperlink text (wrap long titles)
- `STATUS` badge colours:
  - `KONTRAK SELESAI` → green pill
  - `DALAM PELAKSANAAN` → blue pill
  - `DRAF` → grey pill
  - `EOT` → purple pill
- `STATUS DRAF KOMPAN`: Show `✓` (blue checkmark) or `—` (dash)
- `TARIKH TAMAT DIMASUKKAN SISTEM`: Date or `—`

**Filter bar** (above table, below search): 
- Year range filter: `Tahun Mula [____]` to `Tahun Tamat [____]` → trigger AJAX reload

**Pagination**: Same style as Urus Pengguna (Papar N entri, page links)

**Clicking a contract row** → opens Perincian Kontrak modal.

---

### Modal: Tambah Kontrak

Triggered by `+ Tambah Kontrak` button. Full-screen or large modal.

**Modal title**: "Tambah Kontrak Baru"

Tabs inside modal: `Maklumat Kontrak` | `Catatan Kontrak`

---

#### Tab 1: Maklumat Kontrak

Section header `> Maklumat Projek`

**Fields**:
- `No Kontrak *` — text input (e.g. BTM/MG/2025)
- `Tajuk Kontrak *` — textarea (2 rows)
- `Nama Syarikat *` — searchable dropdown (type to search companies from `/api/v1/syarikat?search=`)
  - Show `+ Tambah Syarikat Baru` option at bottom of dropdown
- `Nilai Kontrak (RM) *` — number input (formatted with commas, 2 decimal places)
- `Kaedah Perolehan *` — dropdown:
  - SEBUT HARGA
  - TENDER
  - RUNDINGAN TERUS
  - PEMBELIAN TERUS
- `Kategori Perolehan *` — dropdown:
  - PERKHIDMATAN
  - BEKALAN
  - KERJA
- `Pihak Berkuasa Melulus` — two sub-fields side by side:
  - `Kekuatan:` text input
  - `Tarikh Kekuasan:` date picker
- Two date fields side by side:
  - `Diluluskan:` date picker
  - `Ditandatangani:` date picker
- `Tarikh SST *` — two date fields side by side (`Mula:` and `Tamat:`)
- `Tempoh Kontrak *` — two date fields side by side (`Mula:` and `Tamat:`)

Section header `> Pemilik Projek`

**Fields**:
- `Jabatan / Bahagian *` — text or dropdown
- `Bahagian / Unit *` — dropdown (loads based on jabatan)
- `Pegawai Bertanggungjawab *` — searchable dropdown (users list) | `Emel:` auto-filled from selected user
- `Pegawai Perhubungan 1` — searchable dropdown | `Emel:` auto-filled | label "TIADA" if empty
- `Pegawai Perhubungan 2` — same
- `Status Kontrak *` — dropdown (DRAF / DALAM_PELAKSANAAN / KONTRAK_SELESAI / EOT)
- `Catatan Kontrak *` — textarea

**Note in red**: "E-mel - ejaan" (indicating email field should be validated for correct spelling/format)

Footer buttons: `Simpan` (primary) | `Batal` (outline)

---

#### Tab 2: Catatan Kontrak

**When adding new contract**: Tab shows "Tiada catatan diterima." (empty state with folder icon)

**When viewing existing contract**: Shows a table of notes:
| # | ↕ STATUS | ⏱ TAHAP | ✎ CATATAN |
|---|---|---|---|

Below the table: a form to add a new note:
- `Tahap` — text input
- `Status` — text input
- `Catatan` — textarea
- Button: `Tambah Catatan`

API: `POST /api/v1/kontrak/{id}/catatan`

---

## Modal: Perincian Kontrak

Opened when clicking a contract in Senarai Kontrak list.

**Modal title**: "Perincian Kontrak" | subtitle: contract no (e.g. "BTM/MG/2017")

**Tabs**: `Maklumat Kontrak` | `Catatan Kontrak`

This is essentially the same form as Tambah Kontrak but in **read/edit mode**:
- All fields pre-filled with contract data
- Fields are **editable** if user has `pendaftar_kontrak` role and status != `KONTRAK_SELESAI`
- Fields are **read-only** (greyed out, no border) for `pemilik_projek` role

**Catatan Kontrak tab** in detail view: Shows full notes history.

---

## KontrakController

```php
class KontrakController extends Controller {
    
    public function index(Request $request) {
        // Server-side render with initial data
        $params = $request->only(['search', 'tahun_mula', 'tahun_tamat', 'page', 'per_page']);
        $result = $this->apiService->withAuth()->get('/kontrak', $params);
        return view('kontrak.index', ['contracts' => $result['data']]);
    }
    
    public function fetchAjax(Request $request) {
        // AJAX reload — returns JSON
        $result = $this->apiService->withAuth()->get('/kontrak', $request->all());
        return response()->json($result);
    }
    
    public function show(int $id) {
        // AJAX — returns JSON for modal population
        $result = $this->apiService->withAuth()->get("/kontrak/{$id}");
        return response()->json($result);
    }
    
    public function store(Request $request) {
        $result = $this->apiService->withAuth()->post('/kontrak', $request->all());
        return response()->json($result);
    }
    
    public function update(Request $request, int $id) {
        $result = $this->apiService->withAuth()->put("/kontrak/{$id}", $request->all());
        return response()->json($result);
    }
    
    public function getCatatan(int $id) {
        $result = $this->apiService->withAuth()->get("/kontrak/{$id}/catatan");
        return response()->json($result);
    }
    
    public function storeCatatan(Request $request, int $id) {
        $result = $this->apiService->withAuth()->post("/kontrak/{$id}/catatan", $request->all());
        return response()->json($result);
    }
}
```

---

## Routes

```php
Route::middleware('auth.session')->prefix('kontrak')->group(function () {
    Route::get('/', [KontrakController::class, 'index'])->name('kontrak.index');
    Route::get('/fetch', [KontrakController::class, 'fetchAjax']);
    Route::post('/', [KontrakController::class, 'store'])->name('kontrak.store');
    Route::get('/{id}', [KontrakController::class, 'show'])->name('kontrak.show');
    Route::put('/{id}', [KontrakController::class, 'update'])->name('kontrak.update');
    Route::get('/{id}/catatan', [KontrakController::class, 'getCatatan']);
    Route::post('/{id}/catatan', [KontrakController::class, 'storeCatatan']);
});
```

---

## JavaScript Behaviour

1. **Search**: Debounced 300ms → AJAX fetch → replace table rows
2. **Year filter**: On blur/change → AJAX reload
3. **Row click**: Fetch `/kontrak/{id}` → populate modal fields → open modal
4. **Syarikat dropdown**: As user types → debounced fetch `/api/v1/syarikat?search=X` → show dropdown results
5. **Pegawai dropdown**: Same pattern — search users from API
6. **Tab switching**: CSS only — show/hide sections
7. **Nilai Kontrak**: Format as `RM 94,400.00` display while keeping raw value in hidden input
8. **Add new note**: POST → re-fetch catatan → re-render catatan table
