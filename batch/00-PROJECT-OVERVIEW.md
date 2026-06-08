# eKontrak System — Project Overview & Architecture

## Project Summary
eKontrak is a government contract management system for Kementerian Perumahan dan Kerajaan Tempatan (KPKT) / Jabatan Perumahan dan Bangunan (JBPM). It enables officers to register, manage, and monitor contracts under KPKT's oversight.

---

## Technology Stack

| Technology | Item | Version |
|---|---|---|
| Laravel | Framework | 12.0 |
| PHP | Language | 8.5 |
| MySQL | Database | 9.0 |
| GitHub | Version Control | - |
| SMTP | Email Notifications | - |
| Nginx | Reverse Proxy | 1.29.3 |

---

## Architecture: Two Separate Laravel Projects

### 1. `ekontrak-web` — Laravel Web Application (Frontend)
- Renders Blade views / HTML UI
- Communicates with the backend via internal HTTP API calls
- Handles sessions, authentication UI, MFA UI
- Role: UI layer only — no direct database access

### 2. `ekontrak-api` — Laravel Backend API (Services)
- RESTful JSON API
- Handles all business logic
- Direct database (MySQL) access
- Issues JWT / Sanctum tokens
- Sends SMTP email notifications
- Role: Data + business logic layer only

---

## User Roles

| Role | Malay Label | Permissions |
|---|---|---|
| Admin | Admin | Full system administration |
| Pendaftar Kontrak | Contract Registrar | Register and edit contracts |
| Pemilik Projek | Project Owner | View/manage their contracts |
| Admin Sistem | System Admin | System-level admin |
| Pegawai Undang-Undang | Legal Officer | Legal review access |

---

## Core Modules

1. **Authentication** — Login, MFA, Password Change
2. **Urus Pengguna** — User Management (CRUD, Roles, Block)
3. **Laman Utama / Dashboard** — Contract status summary, alerts
4. **Senarai Kontrak** — Contract listing with filters
5. **Perincian Kontrak** — Contract detail (Maklumat + Catatan tabs)
6. **Maklumat Syarikat** — Company/vendor information
7. **Laporan** — Reports (Lampiran A & B)

---

## Prompt Files Index

| File | Description |
|---|---|
| `01-DATABASE-SCHEMA.md` | Full MySQL schema for all tables |
| `02-API-BACKEND.md` | ekontrak-api Laravel project — all endpoints |
| `03-WEB-AUTH.md` | ekontrak-web — Login, MFA, Password modules |
| `04-WEB-USER-MANAGEMENT.md` | ekontrak-web — Urus Pengguna module |
| `05-WEB-DASHBOARD.md` | ekontrak-web — Laman Utama dashboard |
| `06-WEB-CONTRACT.md` | ekontrak-web — Senarai & Perincian Kontrak |
| `07-WEB-COMPANY.md` | ekontrak-web — Maklumat Syarikat |
| `08-WEB-LAPORAN.md` | ekontrak-web — Laporan module |
| `09-SHARED-CONFIG.md` | Shared config, middleware, helpers |
