# eKontrak System Overview

## What the System Does

eKontrak is a contract management system with two Laravel applications:

- `backend/ekontrak-api`
  The source of truth for data, business rules, reporting logic, and role-protected API endpoints.
- `frontend/ekontrak-web`
  The server-rendered web interface that calls the backend API and renders the user-facing screens.

The main business areas are:

- user registration and account approval
- authentication and password management
- contract registration and tracking
- company master data management
- dashboard summaries and alerts
- audit trail monitoring
- reports for Lampiran A and Lampiran B

## High-Level Architecture

```text
Browser
  -> frontend/ekontrak-web (Blade + session)
  -> ApiService
  -> backend/ekontrak-api (/api/v1)
  -> Laravel models + database
```

Key architectural decisions:

- the web app is not a separate SPA; it is a Laravel Blade application
- authentication is performed by the backend API and the returned token is stored in frontend session
- frontend controllers mostly orchestrate views and proxy requests to the backend
- backend controllers own business rules, validation, and persistence

## Main Modules

### Backend Modules

- Auth
- Reference Data
- Dashboard
- User Administration
- Audit Log
- Contracts
- Companies
- Reports

### Frontend Modules

- Login and Registration
- Dashboard
- Kontrak
- Syarikat
- Laporan
- Pengguna
- Audit Trail

## Roles

Current roles in the system:

- `admin`
- `admin_sistem`
- `pendaftar_kontrak`
- `pemilik_projek`
- `pegawai_undang_undang`

Role summary:

- `admin`
  Highest operational access, including user deletion and audit review.
- `admin_sistem`
  Administrative access focused on user and application management.
- `pendaftar_kontrak`
  Main data-entry role for contracts and companies.
- `pemilik_projek`
  Project owner role focused on contract visibility and monitoring.
- `pegawai_undang_undang`
  Legal officer role with a specialized dashboard presentation.

## Major Components and Views

### Authentication Views

- login screen
- registration entry screen
- registration form
- change-password screen

### Dashboard Components

- general dashboard view for most roles
- legal dashboard view for `pegawai_undang_undang`
- shared alert table partial
- shared status modal table partial
- shared contract detail modal behavior

### Administration Components

- user list table
- registration request review table
- audit table and stats view

### Core Business Components

- contract list and modal detail views
- company list and detail modal views
- report tables and export actions

## Database Domains

The main data domains are:

- users and roles
- departments and units
- companies
- contracts
- contract notes
- EOT history
- audit trail

The user and contract areas are linked but modeled differently:

- users store department and unit names as text for registration history
- contracts store department and unit as foreign keys for reporting and filtering

## Important Live Behaviors

- login is token-based through backend Sanctum
- non-admin users must be approved before login
- dashboard cards and alerts are driven from contract status and date fields
- completed contracts cannot be updated
- audit login/logout reporting requires interpreting both `action` and `model_type`
- registration dropdown data depends on seeded `jabatan` and `bahagian_unit` records

## Current Documentation Set

- `docs/SYSTEM-OVERVIEW.md`
  High-level architecture, roles, modules, and major components.
- `docs/API-README.md`
  Backend API modules, endpoints, controller responsibilities, and role rules.
- `docs/DATABASE-README.md`
  Table-by-table schema summary, relationships, seeders, and data rules.
- `docs/WEB-AUTH-README.md`
  Web application modules, controllers, shared components, and UI flow.

## Current Notes and Gaps

- Mail configuration has been updated to support the current SMTP environment values.
- Some reference-data expectations were fixed through seed updates rather than form rewiring.
- The backend `ReferenceController` contains a `pegawai()` method, but the current route file does not register `/ref/pegawai`, so that should be treated as a follow-up check.