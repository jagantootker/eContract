# eKontrak API Documentation

## Overview

The backend API lives in `backend/ekontrak-api` and is a Laravel 13 application that exposes `/api/v1` endpoints for authentication, reference data, dashboard summaries, user administration, audit trail, contract management, company management, and reporting.

The API uses:

- Laravel Sanctum for token-based authentication
- role-based middleware for protected actions
- Eloquent models for business entities
- JSON responses with a common shape: `success`, `data`, `message`, and sometimes `status` or `errors`

## API Modules

### 1. Auth Module

Controller: `App\Http\Controllers\Api\AuthController`

Purpose:

- handle registration requests
- allow login/logout
- allow password changes
- enforce approval and active-account checks

Public endpoints:

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/register-check`

Authenticated endpoints:

- `POST /api/v1/auth/logout`
- `POST /api/v1/auth/change-password`

Key functions:

- `register()`
  Creates a pending user request, stores uploaded attachments, hashes the password, creates a reference number, and assigns requested roles.
- `registerCheck()`
  Looks up a registration by IC number and returns its latest application status.
- `login()`
  Validates credentials, blocks inactive users, blocks unapproved non-admin users, revokes old tokens, updates `last_login_at`, and returns a new Sanctum token plus basic user profile data.
- `logout()`
  Deletes the current access token.
- `changePassword()`
  Verifies the current password, updates the password hash, and revokes all tokens to force re-login.

Important behavior:

- non-admin users must have `permohonan_status = diluluskan` before they can log in
- blocked users with `is_active = false` cannot log in
- login is single-session because old tokens are deleted before a new token is issued

### 2. Reference Data Module

Controller: `App\Http\Controllers\Api\ReferenceController`

Purpose:

- provide dropdown and lookup data for registration and contract screens

Endpoints:

- `GET /api/v1/ref/jabatan`
- `GET /api/v1/ref/bahagian-unit`
- `GET /api/v1/ref/negeri`

Implemented function not currently registered in routes:

- `pegawai()` returns active approved users for officer lookups

Key functions:

- `jabatan()`
  Returns registration-visible departments ordered by code.
- `bahagianUnit()`
  Returns units, optionally filtered by `jabatan_id`.
- `negeri()`
  Returns a static list of Malaysian states and federal territories.
- `pegawai()`
  Returns active approved users ordered by name. This function exists in the controller, but the current `routes/api.php` file does not register `/ref/pegawai`.

### 3. Dashboard Module

Controller: `App\Http\Controllers\Api\DashboardController`

Purpose:

- provide homepage summaries and alert data
- provide role-specific dashboard data shape

Endpoints:

- `GET /api/v1/dashboard`
- `GET /api/v1/dashboard/maklumat-tidak-lengkap`
- `GET /api/v1/dashboard/kontrak-selesai`
- `GET /api/v1/dashboard/alerts`

Key functions:

- `index()`
  Chooses between the general dashboard and the Pegawai Undang-Undang dashboard based on user roles.
- `maklumatTidakLengkap()`
  Returns contracts missing required fields such as title, company, value, start date, or end date.
- `kontrakSelesai()`
  Returns completed contracts filtered by year and search.
- `alerts()`
  Returns three alert lists: `tempoh_tamat_telah_tamat`, `tempoh_tamat_dalam_2_minggu`, and `tempoh_aktif_6_bulan`.
- `generalDashboard()`
  Builds summary counts for incomplete contracts, draft contracts, active contracts, EOT contracts, completed contracts, and total contracts.
- `undangUndangDashboard()`
  Builds agency-level summary rows for legal officers using `jabatan` codes and contract status/date rules.

Business rules reflected in the dashboard:

- incomplete contracts are contracts with missing required core fields
- general status cards are driven by `status_kontrak`
- alert panels are driven by `tamat_tarikh`, `mula_tarikh`, and current date windows

### 4. User Administration Module

Controller: `App\Http\Controllers\Api\UserController`

Purpose:

- manage active users
- review user applications
- assign or update user roles
- block/unblock users

Endpoints:

- `GET /api/v1/users/roles`
- `GET /api/v1/users/permohonan`
- `GET /api/v1/users/permohonan/{id}`
- `PUT /api/v1/users/permohonan/{id}/keputusan`
- `GET /api/v1/users`
- `POST /api/v1/users`
- `PUT /api/v1/users/{id}`
- `PUT /api/v1/users/{id}/toggle-block`
- `DELETE /api/v1/users/{id}`

Access rules:

- `admin` and `admin_sistem` can list and manage users
- only `admin` can delete users

Key functions:

- `index()`
  Lists standard users and approved requests with search and pagination.
- `rolesIndex()`
  Returns master role records for UI dropdowns.
- `permohonanIndex()`
  Lists pending or historical registration requests with filters.
- `permohonanShow()`
  Returns full application details including uploaded attachment URLs.
- `permohonanKeputusan()`
  Approves or rejects requests, updates `permohonan_status`, sets roles and access scope, and activates new accounts on approval.

Important behavior:

- role-change applications are treated differently from first-time account approval
- pending requested roles can be shown even before they become active roles

### 5. Audit Log Module

Controller: `App\Http\Controllers\Api\AuditLogController`

Purpose:

- expose audit trail records to admin users
- provide available action values for filters

Endpoints:

- `GET /api/v1/audit-log`
- `GET /api/v1/audit-log/actions`

Key functions:

- `index()`
  Filters audit entries by search text, action, model type, and date range.
- `actions()`
  Returns distinct stored action names for the filter dropdown.

Important note:

- login/logout events may be represented by `model_type=login/logout` while `action=create`, so frontend reporting normalizes both fields when showing stats and filters.

### 6. Contract Module

Controller: `App\Http\Controllers\Api\KontrakController`

Purpose:

- list, create, update, and inspect contracts
- manage contract notes

Endpoints:

- `GET /api/v1/kontrak`
- `POST /api/v1/kontrak`
- `GET /api/v1/kontrak/{id}`
- `PUT /api/v1/kontrak/{id}`
- `GET /api/v1/kontrak/{id}/catatan`
- `POST /api/v1/kontrak/{id}/catatan`

Access rules:

- any authenticated user can view contract lists and details
- only `pendaftar_kontrak` can create, update, and add notes

Key functions:

- `index()`
  Returns paginated contracts with related company, department, unit, and responsible officer.
- `store()`
  Creates a contract and stamps `created_by` from the logged-in user.
- `show()`
  Returns a full contract record with relationships including notes, EOT records, and linked officers.
- `update()`
  Updates a contract unless it already has status `KONTRAK_SELESAI`.
- `catatanIndex()`
  Returns contract notes with author details.
- `catatanStore()`
  Saves a note with `status`, `tahap`, and free-text `catatan`.

Important behavior:

- completed contracts are locked from further updates
- list filtering supports search, year filtering, and status filtering

### 7. Company Module

Controller: `App\Http\Controllers\Api\SyarikatController`

Purpose:

- manage supplier/company master records used by contracts

Endpoints:

- `GET /api/v1/syarikat`
- `POST /api/v1/syarikat`
- `GET /api/v1/syarikat/{id}`
- `PUT /api/v1/syarikat/{id}`

Access rules:

- all authenticated users can view companies
- `pendaftar_kontrak` and `admin` can create and update companies

Key functions:

- `index()`
  Returns paginated company records with creator info.
- `store()`
  Creates a company and stamps `created_by`.
- `show()`
  Returns a company with creator and linked contracts.
- `update()`
  Updates company master data.

### 8. Reporting Module

Controller: `App\Http\Controllers\Api\LaporanController`

Purpose:

- generate data feeds for Lampiran A and Lampiran B reports

Endpoints:

- `GET /api/v1/laporan/lampiran-a`
- `GET /api/v1/laporan/lampiran-b`

Key functions:

- `lampiranA()`
  Returns signed-contract monitoring data including signing deadlines, signing status, and late-signing reasons.
- `lampiranB()`
  Returns contract-duration monitoring data including start date, end date, and calculated duration in months.
- `datePlusMonths()`
  Helper for deadline calculation.
- `unsignStatusLabel()`
  Formats status text for unsigned contracts.
- `lateSigningReason()`
  Derives explanation text when a contract is signed late or remains unsigned beyond the allowed period.
- `calcTempohBulan()`
  Calculates contract duration in months.

## Roles and Access Model

Defined roles:

- `admin`
- `admin_sistem`
- `pendaftar_kontrak`
- `pemilik_projek`
- `pegawai_undang_undang`

Middleware:

- `auth:sanctum` protects authenticated API routes
- `role:...` checks whether the current user has at least one allowed role

Typical role responsibilities:

- `admin`
  Full admin access including deletion and audit log review.
- `admin_sistem`
  User and application administration without the admin-only delete route.
- `pendaftar_kontrak`
  Contract and company data entry and contract note management.
- `pemilik_projek`
  Primarily viewing and tracking contract-related information.
- `pegawai_undang_undang`
  Uses the department-based legal dashboard summary.

## Response Conventions

Most endpoints return:

```json
{
  "success": true,
  "data": {},
  "message": "OK"
}
```

Validation or business-rule failures normally return:

```json
{
  "success": false,
  "message": "...",
  "errors": {}
}
```

## Request Validation

The API uses dedicated Form Request classes for core write actions, including:

- auth requests
- user store/update requests
- contract store/update/note requests
- company store/update requests

This keeps validation rules close to the backend domain layer.

## Current Notes

- Mail configuration has been made backward-compatible with `MAIL_DRIVER` and `MAIL_ENCRYPTION` in addition to Laravel 13 defaults.
- Registration reference data for `jabatan` has been updated in seed data to include missing departments required by the current UI.
- The controller method for `/ref/pegawai` exists, but the route is not currently registered in `routes/api.php`.
