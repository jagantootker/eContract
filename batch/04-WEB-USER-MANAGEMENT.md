# Prompt 04 — ekontrak-web: Urus Pengguna (User Management)

## Context
Build the User Management module for `ekontrak-web`. All data fetched via ApiService to `ekontrak-api`. Accessible only to users with `admin` or `admin_sistem` roles.

---

## Layout & Navigation

All authenticated pages use a shared layout: `resources/views/layouts/app.blade.php`

**Layout structure**:
- **Left sidebar** (dark navy, `~220px` wide, collapsible via hamburger)
  - System name: `eKONTRAK` with logo icon
  - User block: name, bahagian/unit, role badge (e.g. "Admin" in blue pill)
  - Navigation links (icons + labels):
    - 🏠 Laman Utama → `/`
    - 📋 Senarai Kontrak → `/kontrak` (hidden for admin_sistem)
    - 🏢 Maklumat Syarikat → `/syarikat` (hidden for admin_sistem)
    - 📊 Laporan → `/laporan` (hidden for admin_sistem)
    - 👤 Pentadbiran → collapsible sub-menu:
      - Urus Pengguna → `/pengguna`
      - Urus Peranan Pengguna → `/pengguna/peranan`
      - Urus Pelayaran → `/pengguna/pelayaran`
    - 🔑 Tukar Kata Laluan → `/tukar-kata-laluan`
- **Top bar**: breadcrumb + `Log Keluar` button (top right)
- **Main content area**: white background

---

## Page: Urus Pengguna (`/pengguna`)

**Route**: `GET /pengguna`

**Blade**: `resources/views/pengguna/index.blade.php`

### Table Display

Title: **Urus Pengguna** — subtitle: "Urus akaun pengguna, peranan, dan akses sistem eKONTRAK."

Action buttons (top right):
- `+ Tambah Agensi (JBPM, PlanMalaysia, JLN)` — secondary button (outline)
- `+ Tambah Pengguna` — primary blue button

**Search bar**: placeholder "Cari nama, IC, jabatan, peranan..."

**Table columns**:
| # | PENGGUNA | JABATAN & UNIT | NO. K/P | PERANAN | TINDAKAN |
|---|---|---|---|---|---|

**PENGGUNA cell**: Show `nama` (bold), `email` (smaller grey text), role badge(s) below
**PERANAN cell**: Show role badge(s) — each role as a coloured pill:
- Pentadbir Kontrak → blue pill
- Pemilik Projek → green pill  
- Admin Sistem → orange pill
- Admin → red pill
- Pegawai Undang-Undang → purple pill

**TINDAKAN cell**: Two buttons:
- `✏ Kemaskini` — outline blue
- `🗑 Hapus` — outline red

**Pagination**: "Papar [5▾] entri" | "Memaparkan 1 hingga 5 daripada 15 entri" | `< 1 2 3 >`

---

### Modal: Tambah Pengguna Baru (BTM)

Triggered by `+ Tambah Pengguna` button.
Implement as a **modal dialog** (not a new page).

**Modal title**: "Tambah Pengguna Baru" with subtitle "Tambah akaun dan tetapkan akses."

**Tab/Toggle at top of modal**: `BTM | JBPM/Agensi` — clicking switches which form is shown.

**BTM Form fields**:
- `No Kad Pengenalan` — text input (placeholder: cfn-890101145678)
  - Note in red below field: "No Kad Pengenalan"
- `Telefon Bimbit` — text input (placeholder: cfn-0123456789)
- `Kata Laluan Baru` — password input with toggle (eye icon)
- `Taip Semula Kata Laluan Baru` — password input with toggle
- **Peranan** — checkbox group (hide Admin role, show only):
  - [ ] Pentadbir Kontrak
  - [ ] Pemilik Projek
  - [ ] Admin Sistem
  - [ ] Pegawai Undang-Undang
- **Note in red**: "Kemas kini" (see wireframe — indicating this is for updates)
- Footer buttons: `Batal` (outline) | `Kemaskini` (primary blue)

---

### Modal: Tambah Pengguna Baru JBPM/Agensi

**JBPM Form fields**:
- `No Kad Pengenalan`
- `Nama Pegawai`
- `Emel` (placeholder: cfn-nama@agensi.gov.my)
- `Jabatan/Bahagian` — dropdown (from `/api/v1/ref/jabatan`)
- `Bahagian/Unit` — dropdown (dynamically loads from `/api/v1/ref/bahagian-unit?jabatan_id=X`)
- `Telefon` — text
- `Telefon Bimbit` — text
- `Kata Laluan Baru` — password with toggle
- `Taip Semula Kata Laluan Baru` — password with toggle
- **Peranan** — same checkbox group (Admin role visible and selectable for JBPM form)
- Footer: `Batal` | `Kemaskini`

---

### Modal: Kemaskini Pengguna

Same layout as Tambah but pre-populated with existing user data.

Additional fields shown (pre-filled, non-editable labels in grey):
- `No Kad Pengenalan` — pre-filled, read-only
- `Nama Pegawai` — editable
- `Emel` — editable
- `Jabatan/Bahagian` — dropdown pre-selected
- `Bahagian/Unit` — dropdown pre-selected
- `Telefon` — editable
- `Telefon Bimbit` — editable
- `Kata Laluan Baru` — optional (leave blank to keep current)
  - Note: "Biarkan kosong jika tidak mahu tukar"
- `Taip Semula Kata Laluan Baru` — optional

**Peranan** checkboxes — pre-checked based on user's current roles.

Footer buttons:
- `Kemaskini` (primary blue, left)
- `Batal` (outline)
- `Sekat Pengguna` (red button, right) — calls toggle-block API

**Note in red at bottom**: "Masukkan fungsi Tukar kata laluan mengikut standard keselamatan terkini."

---

## JavaScript Behaviour

1. **Search**: Debounced (300ms) — reload table via AJAX (`fetch`) on keyup
2. **Pagination**: AJAX-based — update table without full page reload
3. **Modal open/close**: CSS transition fade-in
4. **JBPM Bahagian/Unit**: On jabatan change → fetch `/api/v1/ref/bahagian-unit?jabatan_id={id}` → repopulate dropdown
5. **Sekat/Unblock button**: Changes label dynamically ("Sekat Pengguna" ↔ "Nyah Sekat Pengguna") based on `is_active`
6. **Delete confirm**: Show browser `confirm()` dialog before DELETE call
7. **Toast notifications**: Show success/error toast after each API action (top-right corner, auto-dismiss 3s)

---

## Controller: `PenggunaController`

```php
class PenggunaController extends Controller {
    public function index(Request $request) { /* GET /pengguna — load view + fetch users */ }
    public function store(Request $request) { /* POST via AJAX — call API POST /users */ }
    public function update(Request $request, int $id) { /* PUT via AJAX */ }
    public function destroy(int $id) { /* DELETE via AJAX */ }
    public function toggleBlock(int $id) { /* PUT via AJAX */ }
}
```

All store/update/destroy/toggleBlock methods return JSON (for AJAX), not redirects.

---

## Routes

```php
Route::middleware('auth.session')->prefix('pengguna')->group(function () {
    Route::get('/', [PenggunaController::class, 'index'])->name('pengguna.index');
    Route::post('/', [PenggunaController::class, 'store'])->name('pengguna.store');
    Route::put('/{id}', [PenggunaController::class, 'update'])->name('pengguna.update');
    Route::delete('/{id}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');
    Route::put('/{id}/toggle-block', [PenggunaController::class, 'toggleBlock']);
});
```

---

## Access Control

Add role check at top of each controller method:
```php
if (!AuthHelper::hasRole('admin') && !AuthHelper::hasRole('admin_sistem')) {
    abort(403, 'Akses tidak dibenarkan.');
}
```
