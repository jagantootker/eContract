# Prompt 03 — ekontrak-web: Authentication Module

## Context
Build the authentication UI module for `ekontrak-web` (Laravel 12 Blade application). This app communicates with `ekontrak-api` via HTTP (using Laravel HTTP Client / Guzzle). It does NOT connect to the database directly.

---

## Project Setup

```bash
composer create-project laravel/laravel ekontrak-web
cd ekontrak-web
```

Configure `.env`:
```
APP_NAME=eKontrak
APP_URL=http://localhost:8000
API_BASE_URL=http://localhost:8001/api/v1
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

Create `config/api.php`:
```php
return [
    'base_url' => env('API_BASE_URL', 'http://localhost:8001/api/v1'),
];
```

Create `App\Services\ApiService` — a wrapper around Laravel HTTP Client:
```php
class ApiService {
    protected string $baseUrl;
    
    public function __construct() {
        $this->baseUrl = config('api.base_url');
    }
    
    public function withAuth(): static {
        // Attach Bearer token from session('api_token')
    }
    
    public function get(string $endpoint, array $params = []): array { }
    public function post(string $endpoint, array $data = []): array { }
    public function put(string $endpoint, array $data = []): array { }
    public function delete(string $endpoint): array { }
}
```

---

## UI Design System

Use the existing eKontrak visual identity:
- **Left panel**: Dark navy/dark teal background (`#0d1f3c` or similar) with "Sistem eKONTRAK" branding, white "Sistem" text and cyan/blue "eKONTRAK" text
- **Right panel**: White background with form
- **Primary button**: Blue (`#2563eb`)
- **Logo**: KPKT logo at top of left panel (use placeholder `<img>` with alt text)
- **Font**: Use a formal/professional government system font — `'DM Sans'` for body, `'Playfair Display'` for display headings, or source from Google Fonts

Reference the existing design in the wireframes (dark left panel with geometric circular background art, right side plain white form).

---

## Pages Required

### Page 1: Login (`/login`)

**Route**: `GET /login` → show form | `POST /login` → process

**Blade view**: `resources/views/auth/login.blade.php`

**Layout**: Two-column layout (left: branding, right: form)

**Left panel elements**:
- KPKT logo (top left)
- "Sistem" (white) + "eKONTRAK" (blue/cyan) heading
- System description paragraph in Malay
- Decorative geometric/circular background art (CSS-only, using border-radius and opacity)

**Right panel — form elements**:
- Header nav: "Panduan Pentadbir Kontrak" link | "Log Masuk" button | "Daftar" button
- Section title: "Log Masuk"
- Subtitle: "Sila masukkan butiran anda untuk meneruskan."
- Field: `No Kad Pengenalan` (placeholder: Contoh: 890101145678, icon: ID card icon)
- Field: `Kata Laluan` (password, with show/hide toggle, icon: lock)
- Checkbox: `Ingat Saya` (left) | Link: `Lupa Kata Laluan?` (right) — link goes to `#` for now
- Button: `LOG MASUK` (primary, full width, with → icon)
- Button: `RESET` (secondary/outline, full width)
- Divider text: "— atau Daftar menggunakan —"
- **MFA section placeholder**: A note/TODO area labeled "MFA - cadangkan jenis MFA" (add a placeholder text input or a section with label "Kod MFA (Jika Dikehendaki)" visible only after first login step — implement as 2-step: step 1 = credentials, step 2 = MFA code)

**Controller** `AuthController@login`:
1. Validate `ic_number` (required, digits, 12 chars) and `password` (required)
2. POST to `{API_BASE_URL}/auth/login` via ApiService
3. On success: store `api_token`, `user`, `roles` in session → redirect to `/`
4. On fail: back with error message

**Validation error display**: Show errors below each field in red.

---

### Page 2: Register (`/daftar`)

**Route**: `GET /daftar` → show form

**Note**: Registration is admin-only (no self-registration). This page must show a notice:
> "Pendaftaran akaun perlu dibuat oleh Pentadbir Sistem. Sila hubungi pentadbir anda."

Still render the registration form but DISABLE the submit button and show the notice prominently in an amber/warning alert box at top.

**Blade view**: `resources/views/auth/register.blade.php`

**Form fields** (read-only/disabled):
- No Kad Pengenalan
- Telefon Bimbit
- Kata Laluan Baru
- Taip Semula Kata Laluan Baru
- Button: DAFTAR (disabled)
- Link: "Sudah ada akaun? Log Masuk"

---

### Page 3: Tukar Kata Laluan (`/tukar-kata-laluan`)

**Route**: `GET /tukar-kata-laluan` | `POST /tukar-kata-laluan`

**Auth required** (middleware: check session has `api_token`)

**Blade view**: `resources/views/auth/change-password.blade.php`

**Layout**: Authenticated layout (with sidebar navigation)

**Form fields**:
- Kata Laluan Semasa
- Kata Laluan Baru (with strength indicator)
- Taip Semula Kata Laluan Baru
- Button: KEMASKINI

**Password strength indicator** (JavaScript):
Show a progress bar below the new password field:
- Red: < 8 chars or missing required character types
- Yellow: meets some requirements
- Green: meets all requirements (8+ chars, upper, lower, number, special)

List requirements as checkboxes that turn green as met:
- ✓ Minimum 8 aksara
- ✓ Huruf besar (A-Z)
- ✓ Huruf kecil (a-z)
- ✓ Nombor (0-9)
- ✓ Aksara khas (@$!%*?&)

**Controller** `AuthController@changePassword`:
1. Validate
2. POST to `/auth/change-password` via ApiService (with Bearer token)
3. On success: show success alert, redirect back to dashboard
4. On fail: show API error message

---

## Middleware

Create `App\Http\Middleware\AuthenticatedSession`:
```php
// Checks session('api_token') exists
// If not → redirect to /login
// Apply to all authenticated routes
```

Register in `bootstrap/app.php` as `auth.session`.

---

## Session Helpers

Create `App\Helpers\AuthHelper`:
```php
class AuthHelper {
    public static function user(): ?array { return session('user'); }
    public static function token(): ?string { return session('api_token'); }
    public static function roles(): array { return session('roles', []); }
    public static function hasRole(string $role): bool {
        return in_array($role, static::roles());
    }
    public static function isAdmin(): bool { return static::hasRole('admin'); }
}
```

---

## Route File (`routes/web.php`) — Auth section

```php
// Guest routes
Route::middleware('guest.session')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
});

// Authenticated routes
Route::middleware('auth.session')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/tukar-kata-laluan', [AuthController::class, 'showChangePassword']);
    Route::post('/tukar-kata-laluan', [AuthController::class, 'changePassword']);
});
```
