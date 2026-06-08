# Prompt 02 — ekontrak-api (Laravel 12 Backend API)

## Context
Build `ekontrak-api` as a standalone Laravel 12 REST API project. This project:
- Has direct MySQL 9.0 database access
- Exposes JSON endpoints consumed by `ekontrak-web`
- Uses Laravel Sanctum for token-based authentication
- Sends email notifications via SMTP (Laravel Mail)
- Has NO Blade views or frontend assets

---

## Project Setup Instructions

```bash
composer create-project laravel/laravel ekontrak-api
cd ekontrak-api
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

Configure `.env`:
```
APP_NAME=eKontrak-API
APP_URL=http://localhost:8001
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ekontrak
DB_USERNAME=root
DB_PASSWORD=
SANCTUM_STATEFUL_DOMAINS=localhost:8000
```

---

## Global API Rules

- All routes prefixed with `/api/v1/`
- All responses in JSON format
- Authentication via `Authorization: Bearer {token}` header (Sanctum)
- All authenticated routes use `auth:sanctum` middleware
- Failed auth returns `401 Unauthorized`
- Forbidden action returns `403 Forbidden`
- Validation errors return `422 Unprocessable Entity` with `errors` object
- Standard success response wrapper:
```json
{
  "success": true,
  "data": {},
  "message": "OK"
}
```
- Standard error response wrapper:
```json
{
  "success": false,
  "message": "Error description",
  "errors": {}
}
```

---

## Module 1: Authentication

### POST `/api/v1/auth/login`
**No auth required**

Request:
```json
{
  "ic_number": "890101145678",
  "password": "yourpassword"
}
```

Logic:
1. Find user by `ic_number`
2. Verify password with `Hash::check()`
3. Check `is_active == true`, return `403` if blocked
4. If valid, create Sanctum token
5. Update `last_login_at`
6. Return token + user info + roles

Response:
```json
{
  "success": true,
  "data": {
    "token": "...",
    "user": {
      "id": 1,
      "name": "Siti Hajar",
      "ic_number": "760717005788",
      "email": "haja@kpkt.gov.my",
      "jabatan_bahagian": "UNIT INTEGRITI",
      "bahagian_unit": "SEKSYEN PENGESAHAN...",
      "roles": ["pendaftar_kontrak"]
    }
  }
}
```

### POST `/api/v1/auth/logout`
**Auth required**
- Revoke current token
- Return `{ "success": true, "message": "Logged out" }`

### POST `/api/v1/auth/change-password`
**Auth required**

Request:
```json
{
  "current_password": "...",
  "new_password": "...",
  "new_password_confirmation": "..."
}
```

Password rules (enforce these in validation):
- Minimum 8 characters
- At least 1 uppercase letter
- At least 1 lowercase letter
- At least 1 number
- At least 1 special character (`@$!%*?&`)

---

## Module 2: User Management

### GET `/api/v1/users`
**Auth required | Role: admin, admin_sistem**

Query params: `?search=&page=1&per_page=5`

Returns paginated list of users with their roles.

### POST `/api/v1/users`
**Auth required | Role: admin, admin_sistem**

Request body (for BTM user):
```json
{
  "ic_number": "890101145678",
  "telefon_bimbit": "0123456789",
  "password": "...",
  "password_confirmation": "...",
  "roles": ["pendaftar_kontrak", "pemilik_projek"],
  "source": "BTM"
}
```

Request body (for JBPM user — additional fields):
```json
{
  "ic_number": "...",
  "name": "...",
  "email": "nama@agensi.gov.my",
  "jabatan_bahagian": "...",
  "bahagian_unit": "...",
  "telefon_pejabat": "...",
  "telefon_bimbit": "...",
  "password": "...",
  "password_confirmation": "...",
  "roles": ["pendaftar_kontrak"],
  "source": "JBPM"
}
```

Logic:
- Validate `ic_number` is unique
- Do NOT allow role `admin` to be assigned (hide Admin role from creation — only assign via seeder/direct DB)
- Hash password
- Assign roles via `user_roles`

### PUT `/api/v1/users/{id}`
**Auth required | Role: admin, admin_sistem**

Same fields as POST. Password fields optional (only update if provided). Include `tukar_kata_laluan` note requirement (must log this in audit).

### PUT `/api/v1/users/{id}/toggle-block`
**Auth required | Role: admin, admin_sistem**

Toggles `is_active` between true/false.
- Returns updated user with new `is_active` status

### DELETE `/api/v1/users/{id}`
**Auth required | Role: admin**

Soft-delete the user.

---

## Module 3: Dashboard / Laman Utama

### GET `/api/v1/dashboard`
**Auth required**

Response based on role:

For `pendaftar_kontrak` / `pemilik_projek`:
```json
{
  "success": true,
  "data": {
    "summary": {
      "maklumat_tidak_lengkap": 7,
      "draf_kontrak": 0,
      "dalam_pelaksanaan": 0,
      "eot": 0,
      "kontrak_selesai": 32,
      "jumlah_keseluruhan": 39
    },
    "alerts": {
      "tempoh_tamat_telah_tamat": [],
      "tempoh_tamat_dalam_2_minggu": [],
      "tempoh_aktif_6_bulan": []
    }
  }
}
```

For `pegawai_undang_undang`:
Returns a breakdown table by jabatan/bahagian with columns:
- STATUS rows: Draf Kontrak, Extension of Time (EOT), JUMLAH KESELURUHAN
- Columns per department code: BKP, BKT, BIS, BUK, BTM, JKT, JPET, APM, JKT, JUN, PIN, PLANM, TPPS, URS, JUMLAH

### GET `/api/v1/dashboard/maklumat-tidak-lengkap`
**Auth required**

Query: `?tahun=&search=&page=1&per_page=5`

Returns contracts where required fields are null/empty.
Fields: `tajuk_kontrak`, `syarikat_id`, `nilai_kontrak`, `tarikh_mula`, `tarikh_tamat`

### GET `/api/v1/dashboard/kontrak-selesai`
**Auth required**

Query: `?tahun=&search=&page=1&per_page=5`

Returns contracts with `status_kontrak = KONTRAK_SELESAI`, filtered by year of `tamat_tarikh`.

### GET `/api/v1/dashboard/alerts`
**Auth required**

Returns:
- Contracts where `tamat_tarikh` has passed (no EOT)
- Contracts where `tamat_tarikh` is within 2 weeks
- Contracts active within 6 months

---

## Module 4: Contracts

### GET `/api/v1/kontrak`
**Auth required**

Query params: `?search=&tahun_mula=&tahun_tamat=&status=&page=1&per_page=10`

Search searches across: `no_kontrak`, `tajuk_kontrak`, `syarikat.nama_syarikat`, `status_kontrak`

Returns paginated list with columns:
- `tajuk_kontrak`, `no_kontrak`, `pemilik_projek` (jabatan+unit), `tempoh_kontrak` (mula–tamat), `status`, `status_draf_kompan`, `tarikh_draf_hantar_sistem`

### POST `/api/v1/kontrak`
**Auth required | Role: pendaftar_kontrak**

Request:
```json
{
  "no_kontrak": "BTM/MG/2025",
  "tajuk_kontrak": "...",
  "syarikat_id": 1,
  "nilai_kontrak": 94400.00,
  "kaedah_perolehan": "SEBUT HARGA",
  "kategori_perolehan": "PERKHIDMATAN",
  "pihak_berkuasa_melulus_nama": "JAWATANKUASA SEBUT HARGA",
  "pihak_berkuasa_melulus_tarikh": "2025-03-03",
  "diluluskan_tarikh": "2025-03-25",
  "ditandatangani_tarikh": "2025-07-17",
  "mula_tarikh": "2025-07-16",
  "tamat_tarikh": "2026-07-15",
  "tarikh_sst": "2025-07-16",
  "jabatan_id": 1,
  "bahagian_unit_id": 2,
  "pegawai_bertanggungjawab_id": 5,
  "pegawai_perhubungan_1_id": null,
  "pegawai_perhubungan_2_id": null,
  "catatan_kontrak": ""
}
```

### GET `/api/v1/kontrak/{id}`
**Auth required**

Returns full contract detail with:
- All `kontrak` fields
- Related `syarikat` data
- Related `jabatan` + `bahagian_unit`
- `pegawai_bertanggungjawab` (user object with email)
- `catatan_kontrak` array (notes log)

### PUT `/api/v1/kontrak/{id}`
**Auth required | Role: pendaftar_kontrak**

Same fields as POST. Only updatable if status != `KONTRAK_SELESAI`.

### GET `/api/v1/kontrak/{id}/catatan`
**Auth required**

Returns all notes for the contract (from `catatan_kontrak` table), ordered by `created_at` DESC.

### POST `/api/v1/kontrak/{id}/catatan`
**Auth required**

```json
{
  "tahap": "...",
  "status": "...",
  "catatan": "Catatan text here"
}
```

---

## Module 5: Companies (Syarikat)

### GET `/api/v1/syarikat`
**Auth required**

Query: `?search=&page=1&per_page=10`

### POST `/api/v1/syarikat`
**Auth required | Role: pendaftar_kontrak, admin**

```json
{
  "nama_syarikat": "WMI MULTI RESOURCES",
  "alamat": "193/B, Tingkat 1, Rumah Kedai...",
  "negeri": "TERENGGANU",
  "pegawai_hubungi_1_nama": "...",
  "pegawai_hubungi_1_email": "...",
  "pegawai_hubungi_1_tel_pejabat": "...",
  "pegawai_hubungi_1_tel_hp": "...",
  "pegawai_hubungi_2_nama": null,
  "pegawai_hubungi_2_email": null,
  "pegawai_hubungi_3_nama": null
}
```

### GET `/api/v1/syarikat/{id}`
**Auth required**

Returns full company detail with all 3 contact person blocks.

### PUT `/api/v1/syarikat/{id}`
**Auth required | Role: pendaftar_kontrak, admin**

Same fields as POST.

---

## Module 6: Reports (Laporan)

### GET `/api/v1/laporan/lampiran-a`
**Auth required**

Query: `?tahun_mula=&tahun_tamat=&search=`

Returns all contracts with SST date info for "Pemantauan Status Kontrak Ditandatangani" report.

Columns: jabatan/bahagian, bahagian/unit, tajuk perolehan, kaedah perolehan, tarikh SST, tarikh SST semula terima, nama pembekal, telah draf/tandatangan sistem

### GET `/api/v1/laporan/lampiran-b`
**Auth required**

Query: `?tahun_mula=&tahun_tamat=&search=`

Returns contracts for "Pemantauan Tempoh Kontrak" report.

---

## Module 7: Reference Data

### GET `/api/v1/ref/jabatan`
**Auth required**
Returns all jabatan for dropdowns.

### GET `/api/v1/ref/bahagian-unit?jabatan_id={id}`
**Auth required**
Returns bahagian/unit filtered by jabatan.

### GET `/api/v1/ref/negeri`
**No auth required**
Returns list of Malaysian states.

---

## Middleware & Policies

Create a `RoleMiddleware` that checks:
```php
// Usage in routes: ->middleware('role:admin,admin_sistem')
```

Create `AuditLogMiddleware` to log every write action (POST/PUT/DELETE) to `audit_log` table automatically.

---

## Email Notifications (SMTP)

Create `ContractExpiryNotification` — triggered by scheduled command (daily):
- Send email to `pegawai_bertanggungjawab` when contract expires in 2 weeks
- Use Laravel Queue for sending

Create artisan command: `php artisan ekontrak:check-expiry`
Register in `app/Console/Kernel.php` to run daily.
