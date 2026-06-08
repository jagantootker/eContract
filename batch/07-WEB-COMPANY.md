# Prompt 07 — ekontrak-web: Maklumat Syarikat (Company Module)

## Context
Build the Company/Vendor information module for `ekontrak-web`.

---

## Page: Maklumat Syarikat (`/syarikat`)

**Blade**: `resources/views/syarikat/index.blade.php`

**Roles allowed**: `pendaftar_kontrak`, `admin`

---

### Page Header

Title: **Maklumat Syarikat**
Subtitle: "Senarai syarikat yang berdaftar dalam sistem eKONTRAK."

Section accordion: `> Senarai Syarikat` (expanded by default)

**Search**: "Cari Syarikat..." (full-width input)
**Button**: `+ Tambah Syarikat` (primary blue, top right)

---

### Table

**Columns**:
| # | NAMA SYARIKAT | ALAMAT | PEGAWAI UNTUK DIHUBUNGI 1 | PEGAWAI UNTUK DIHUBUNGI 2 | PEGAWAI UNTUK DIHUBUNGI 3 |
|---|---|---|---|---|---|

**NAMA SYARIKAT**: Blue hyperlink → opens detail modal
**PEGAWAI** cells: Show only the name (e.g. "MUHAMAD JUFRI BIN JUSOH") or "TIADA" if empty

Pagination: standard style.

---

### Modal: Maklumat Syarikat (Detail/Edit)

Triggered by clicking company name.

**Modal title**: "Maklumat Syarikat" | subtitle: company name

Section: `> Daftar Syarikat`

**Fields**:
- `Nama Syarikat *` — text input
- `Alamat *` — textarea
- `Negeri *` — dropdown (Malaysian states: Johor, Kedah, Kelantan, Melaka, Negeri Sembilan, Pahang, Perak, Perlis, Pulau Pinang, Sabah, Sarawak, Selangor, Terengganu, WP Kuala Lumpur, WP Labuan, WP Putrajaya)

Three contact person sections (accordion sections):

**Section: `> Nama Pegawai Dihubungi 1`**:
- `Nama *` — text input
- `No Tel Pejabat *` — text (placeholder: Format:0388751234 (tanpa lari))
- `Emel *` — text
- `No H/P *` — text (placeholder: Format:0388751234 (tanpa lari))

**Section: `> Nama Pegawai Dihubungi 2`** (optional):
- Same 4 fields (all optional)
- Placeholder names for each input

**Section: `> Nama Pegawai Dihubungi 3`** (optional):
- Same 4 fields (all optional)

**Footer buttons**:
- When viewing: `KEMBALI` (outline) | `KEMASKINI` (primary blue)
- After clicking KEMASKINI: form becomes editable, button changes to `SIMPAN` | `BATAL`

---

### Modal: Tambah Syarikat Baru

Same form as detail modal but empty fields.

**Footer**: `Batal` | `Kemaskini` (primary)

---

## SyarikatController

```php
class SyarikatController extends Controller {
    public function index(Request $request) {
        $result = $this->apiService->withAuth()->get('/syarikat', $request->only(['search', 'page']));
        return view('syarikat.index', ['companies' => $result['data']]);
    }
    
    public function fetchAjax(Request $request) {
        $result = $this->apiService->withAuth()->get('/syarikat', $request->all());
        return response()->json($result);
    }
    
    public function show(int $id) {
        $result = $this->apiService->withAuth()->get("/syarikat/{$id}");
        return response()->json($result);
    }
    
    public function store(Request $request) {
        $result = $this->apiService->withAuth()->post('/syarikat', $request->all());
        return response()->json($result);
    }
    
    public function update(Request $request, int $id) {
        $result = $this->apiService->withAuth()->put("/syarikat/{$id}", $request->all());
        return response()->json($result);
    }
}
```

## Routes

```php
Route::middleware('auth.session')->prefix('syarikat')->group(function () {
    Route::get('/', [SyarikatController::class, 'index'])->name('syarikat.index');
    Route::get('/fetch', [SyarikatController::class, 'fetchAjax']);
    Route::post('/', [SyarikatController::class, 'store']);
    Route::get('/{id}', [SyarikatController::class, 'show']);
    Route::put('/{id}', [SyarikatController::class, 'update']);
});
```
