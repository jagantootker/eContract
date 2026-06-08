# eKontrak Web Application Documentation

## Overview

The frontend lives in `frontend/ekontrak-web` and is a Laravel web application that renders Blade pages, stores the authenticated API token in session, and proxies business operations to the backend API.

This layer is responsible for:

- login, logout, registration, and password change screens
- dashboard views and modal interactions
- user management pages
- contract and company pages
- report screens and export actions
- audit trail interface

The frontend does not own the business data. It consumes the backend API and renders server-side Blade views with JavaScript-enhanced tables and modals.

## Web Architecture

Main building blocks:

- `app/Services/ApiService.php`
    Central wrapper around HTTP calls to the backend API.
- `app/Helpers/AuthHelper.php`
    Reads current user and role data from session.
- `routes/web.php`
    Defines guest routes and authenticated routes.
- controllers under `app/Http/Controllers`
    Handle page rendering and AJAX proxies.
- Blade views under `resources/views`
    Render modules, tables, forms, alerts, and modal layouts.

## Session and API Flow

1. User submits login form on the web app.
2. `AuthController` calls backend `/auth/login` through `ApiService`.
3. Backend returns a Sanctum token and user role list.
4. Frontend stores `api_token`, `user`, and `roles` in session.
5. Subsequent authenticated page requests call backend endpoints through `ApiService::withAuth()`.
6. If backend returns `401`, the web app clears session data and redirects to login.

## Modules

### 1. Authentication Module

Controller: `App\Http\Controllers\AuthController`

Routes:

- `GET /login`
- `POST /login`
- `GET /daftar`
- `GET /daftar/permohonan`
- `GET /daftar/bahagian-unit`
- `POST /daftar`
- `POST /daftar/semakan`
- `GET /tukar-kata-laluan`
- `POST /tukar-kata-laluan`
- `POST /logout`

Key functions:

- `showLogin()`
    Renders the login page.
- `login()`
    Validates the login form, calls backend login, regenerates session, and stores token/user/roles.
- `logout()`
    Calls backend logout, clears web session, and redirects to login.
- `showRegister()`
    Renders the registration landing page.
- `showRegisterForm()`
    Builds the registration form view, normalizes identifier input, and loads `jabatan` reference data.
- `registerBahagianUnit()`
    AJAX proxy that fetches units based on selected department.
- `register()`
    Validates form input, attaches uploaded files, and submits the registration request to backend `/auth/register`.
- `checkRegistrationStatus()`
    Calls backend `/auth/register-check` and shows current application status.
- `showChangePassword()`
    Renders the change-password page.
- `changePassword()`
    Sends the password change request to backend and clears session after success.

Important behavior:

- registration uses multipart upload because supporting documents are mandatory
- registration stores department and unit display names, not foreign keys
- password-change success forces re-login because the backend revokes all tokens

### 2. Dashboard Module

Controller: `App\Http\Controllers\DashboardController`

Routes:

- `GET /dashboard`
- `GET /dashboard/maklumat-tidak-lengkap`
- `GET /dashboard/kontrak-selesai`
- `GET /dashboard/status/{type}`

Key functions:

- `index()`
    Loads dashboard summary and alerts from the API, then chooses the legal-officer dashboard view or the general dashboard view based on roles.
- `getMaklumatTidakLengkap()`
    AJAX proxy for incomplete-contract modal data.
- `getKontrakSelesai()`
    AJAX proxy for completed-contract modal data.
- `getStatusList()`
    Maps UI card types to backend statuses and reuses `/kontrak` filtering for status modal lists.

Current dashboard views:

- `resources/views/components/dashboard/pegawai.blade.php`
    General/admin-style dashboard with status cards, alert panels, status modals, and contract detail modal.
- `resources/views/components/dashboard/undang_undang.blade.php`
    Pegawai Undang-Undang dashboard with agency-focused alert presentation.
- `resources/views/components/dashboard/_alert_table.blade.php`
    Shared alert-row table partial.
- `resources/views/components/dashboard/_modal_table.blade.php`
    Shared modal list table for status views.

Important recent behavior:

- contract titles in status lists and alert lists open the shared detail modal
- the detail modal is layered above list modals
- the Pegawai dashboard alert design is aligned with the legal-officer dashboard style

### 3. Contract Module

Controller: `App\Http\Controllers\KontrakController`

Routes:

- `GET /kontrak`
- `GET /kontrak/fetch`
- `GET /kontrak/syarikat-search`
- `GET /kontrak/user-search`
- `POST /kontrak`
- `GET /kontrak/{id}`
- `PUT /kontrak/{id}`
- `GET /kontrak/{id}/catatan`
- `POST /kontrak/{id}/catatan`

Key functions:

- `index()`
    Loads contract list plus supporting reference data for departments, companies, and officers.
- `fetchAjax()`
    Returns either JSON or the contract table partial for AJAX refresh.
- `show()`
    Returns contract details for modal display.
- `store()` and `update()`
    Proxy create and update requests to the API.
- `getCatatan()` and `storeCatatan()`
    Manage contract notes.
- `searchSyarikat()` and `searchUser()`
    Support typeahead searches.

Views:

- `resources/views/components/kontrak/index.blade.php`
- `resources/views/components/kontrak/_table.blade.php`

### 4. Company Module

Controller: `App\Http\Controllers\SyarikatController`

Routes:

- `GET /syarikat`
- `GET /syarikat/table`
- `GET /syarikat/fetch`
- `POST /syarikat`
- `GET /syarikat/{id}`
- `PUT /syarikat/{id}`

Key functions:

- `index()`
    Renders the company page with paginated data.
- `fetchAjax()`
    Returns JSON for client-side refresh.
- `table()`
    Returns the company table partial.
- `show()`
    Returns one company detail payload.
- `store()` and `update()`
    Proxy write actions to the API.

Views:

- `resources/views/syarikat/index.blade.php`
- `resources/views/syarikat/_table.blade.php`

### 5. Reporting Module

Controller: `App\Http\Controllers\LaporanController`

Routes:

- `GET /laporan`
- `GET /laporan/lampiran-a`
- `GET /laporan/lampiran-b`
- `GET /laporan/lampiran-a/export-pdf`
- `GET /laporan/lampiran-b/export-pdf`
- `GET /laporan/lampiran-a/export-excel`
- `GET /laporan/lampiran-b/export-excel`

Key functions:

- `index()`
    Renders the reports home page.
- `lampiranA()` and `lampiranB()`
    Fetch report data from the API, apply local sorting/pagination, and render Blade tables.
- `exportPdfA()` and `exportPdfB()`
    Render PDF exports using DomPDF.
- `exportExcelA()` and `exportExcelB()`
    Export Excel files using Laravel Excel.
- `reportFilters()`
    Normalizes filter input.
- `yearOptions()`
    Builds the year dropdown.
- `sortRecords()`
    Applies local column sorting when the API returns full data sets.

Views:

- `resources/views/laporan/index.blade.php`
- `resources/views/laporan/lampiran_a.blade.php`
- `resources/views/laporan/lampiran_b.blade.php`
- `resources/views/laporan/pdf/*`

### 6. User Management Module

Controller: `App\Http\Controllers\PenggunaController`

Routes:

- `GET /pengguna`
- `GET /pengguna/permohonan`
- `GET /pengguna/permohonan/{id}`
- `PUT /pengguna/permohonan/{id}/keputusan`
- `POST /pengguna`
- `PUT /pengguna/{id}`
- `DELETE /pengguna/{id}`
- `PUT /pengguna/{id}/toggle-block`
- `GET /pengguna/bahagian-unit`

Key functions:

- `checkAccess()`
    Restricts the whole module to `admin` and `admin_sistem` roles.
- `index()`
    Loads users, departments, and roles for the main user management page.
- `permohonan()`
    Loads registration application review data.
- `permohonanShow()`
    Returns one application with attachment URLs normalized against the API host.
- `permohonanKeputusan()`
    Sends approval/rejection decisions to the backend.
- `store()`, `update()`, `destroy()`, `toggleBlock()`
    Proxy CRUD and activation actions to the API.
- `bahagianUnit()`
    Returns units for a selected department.

Views:

- `resources/views/components/pengguna/index.blade.php`
- `resources/views/components/pengguna/_table.blade.php`
- `resources/views/components/pengguna/permohonan/index.blade.php`
- `resources/views/components/pengguna/permohonan/_table.blade.php`

### 7. Audit Trail Module

Controller: `App\Http\Controllers\AuditTrailController`

Routes:

- `GET /audit-trail`
- `GET /audit-trail/fetch`

Key functions:

- `checkAccess()`
    Restricts access to `admin` only.
- `resolveApiParams()`
    Normalizes frontend pseudo-filters `__LOGIN__` and `__LOGOUT__` to backend `model_type` filters.
- `index()`
    Loads the main audit page and available actions.
- `fetchAjax()`
    Returns filtered audit rows as JSON.

Views:

- `resources/views/audit-trail/index.blade.php`
- `resources/views/audit-trail/_table.blade.php`

Important recent behavior:

- login/logout rows are displayed distinctly instead of appearing only as generic `create` events
- audit stats and filters now use both `action` and `model_type` to correctly count login/logout activity

## Shared Components and Layouts

Important shared view folders:

- `resources/views/components/layouts`
    Shared app structure and layout pieces
- `resources/views/components/form`
    Reusable form fragments
- `resources/views/components/table`
    Reusable table pieces
- `resources/views/components/shared`
    Shared helper UI fragments
- `resources/views/components/modal.blade.php`
    Shared modal wrapper
- `resources/views/components/toast.blade.php`
    Shared notification UI
- `resources/views/components/stat-card.blade.php`
    Shared dashboard metric card
- `resources/views/components/alert-section.blade.php`
    Shared alert presentation wrapper

## Roles in the Web Layer

The frontend reads roles from session using `AuthHelper`.

Role helpers:

- `isAdmin()`
- `isAdminSistem()`
- `isPendaftar()`
- `isPemilik()`
- `isPegawaiUndang()`
- `hasRole()`

Usage summary:

- `admin`
    Full UI access including audit trail.
- `admin_sistem`
    User management access without admin-only delete/audit behavior in some backend routes.
- `pendaftar_kontrak`
    Contract and company entry screens.
- `pemilik_projek`
    Mostly viewing and monitoring flows.
- `pegawai_undang_undang`
    Receives the legal-specific dashboard view.

## Middleware and Access Boundaries

Web middleware groups:

- `guest.session`
    Guest-only routes such as login and registration.
- `auth.session`
    Logged-in routes.

Controller-level role checks are used inside page controllers for finer access control.

## Current Operational Notes

- `API_BASE_URL` in the frontend environment must point to the backend API, not the web server.
- The current workspace has had dashboard modal interactions and audit normalization updated to match the live UI requirements.
- The contracts page expects officer reference data from `/ref/pegawai`, but the backend route registration should be verified because the controller method exists while the route is not currently declared in `routes/api.php`.
