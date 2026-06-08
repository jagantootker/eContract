# eKontrak Database Documentation

## Overview

The database is centered around users, roles, contracts, companies, reference data, contract notes, EOT history, and audit logging.

Main characteristics:

- Laravel migrations define the schema
- foreign keys are used throughout the core domain
- `users`, `syarikat`, and `kontrak` use soft deletes
- registration-specific fields were added later to the `users` table
- authentication tokens are stored via Sanctum in `personal_access_tokens`

## Table Summary

### 1. `users`

Purpose:

- store login identity, profile information, account status, and registration workflow data

Core columns:

- `id`
- `ic_number`
- `no_tentera`
- `name`
- `email`
- `jabatan_bahagian`
- `bahagian_unit`
- `telefon_pejabat`
- `telefon_bimbit`
- `password`
- `is_active`
- `mfa_secret`
- `source`
- `last_login_at`

Registration columns:

- `jenis_permohonan`
- `kategori_permohonan_agensi`
- `kategori_permohonan_pengguna`
- `capaian_peranan`
- `akses_scope`
- `permohonan_status`
- `no_rujukan_permohonan`
- `lampiran_borang_permohonan`
- `lampiran_kp_tentera`
- `lampiran_pas_pekerja`

Important notes:

- `ic_number` is unique
- `email` is unique
- `password` is stored hashed
- `permohonan_status` is indexed
- `jenis_permohonan` is indexed
- user department and unit are stored as plain strings in the user table, not as foreign keys

### 2. `roles`

Purpose:

- store master role definitions

Columns:

- `id`
- `name`
- `label`

Current roles:

- `admin`
- `pendaftar_kontrak`
- `pemilik_projek`
- `admin_sistem`
- `pegawai_undang_undang`

### 3. `user_roles`

Purpose:

- pivot table connecting users and roles

Columns:

- `id`
- `user_id`
- `role_id`

Constraints:

- cascade delete on both foreign keys
- unique combination of `user_id` and `role_id`

### 4. `jabatan`

Purpose:

- master department/agency list used in registration and contracts

Columns:

- `id`
- `kod`
- `nama`

Notes:

- `kod` is unique
- current API filters registration-visible records using `is_visible_in_registration`, so that field must exist in the live database even if it is not shown in the migration set surfaced here

### 5. `bahagian_unit`

Purpose:

- unit/division records linked to departments

Columns:

- `id`
- `jabatan_id`
- `kod`
- `nama`

Relationship:

- many `bahagian_unit` belong to one `jabatan`

### 6. `syarikat`

Purpose:

- store supplier/company master data

Columns:

- `id`
- `nama_syarikat`
- `alamat`
- `negeri`
- contact person 1 fields
- contact person 2 fields
- contact person 3 fields
- `created_by`
- timestamps
- soft delete column

Relationship:

- many `syarikat` belong to one creator user
- one `syarikat` can have many contracts

### 7. `kontrak`

Purpose:

- central contract table for registration, tracking, dashboard, and reporting

Core columns:

- `id`
- `no_kontrak`
- `tajuk_kontrak`
- `syarikat_id`
- `nilai_kontrak`
- `kaedah_perolehan`
- `kategori_perolehan`
- `pihak_berkuasa_melulus_nama`
- `pihak_berkuasa_melulus_tarikh`
- `diluluskan_tarikh`
- `ditandatangani_tarikh`
- `mula_tarikh`
- `tamat_tarikh`
- `tarikh_sst`
- `status_kontrak`
- `status_draf_kompan`
- `tarikh_draf_hantar_sistem`
- `catatan_kontrak`
- `jabatan_id`
- `bahagian_unit_id`
- `pegawai_bertanggungjawab_id`
- `pegawai_perhubungan_1_id`
- `pegawai_perhubungan_2_id`
- `created_by`
- timestamps
- soft delete column

Enumerations:

- `status_kontrak`: `DRAF`, `DALAM_PELAKSANAAN`, `KONTRAK_SELESAI`, `EOT`
- `kaedah_perolehan`: `SEBUT HARGA`, `TENDER`, `RUNDINGAN TERUS`, `PEMBELIAN TERUS`
- `kategori_perolehan`: `PERKHIDMATAN`, `BEKALAN`, `KERJA`

Important notes:

- `no_kontrak` is unique
- indexed fields include `status_kontrak`, `tamat_tarikh`, and `no_kontrak`
- the application treats completed contracts as locked from updates
- four foreign keys point to the `users` table for different responsibilities

### 8. `catatan_kontrak`

Purpose:

- store timeline notes against contracts

Columns:

- `id`
- `kontrak_id`
- `user_id`
- `status`
- `tahap`
- `catatan`
- timestamps

Relationship:

- many notes belong to one contract
- many notes belong to one user

### 9. `eot_kontrak`

Purpose:

- store extension-of-time history for contracts

Columns:

- `id`
- `kontrak_id`
- `tarikh_mula_baru`
- `tarikh_tamat_baru`
- `sebab`
- `approved_by`
- timestamps

Relationship:

- many EOT records belong to one contract
- optional approver references a user

### 10. `audit_log`

Purpose:

- store audit trail events for admin review

Columns:

- `id`
- `user_id`
- `action`
- `model_type`
- `model_id`
- `payload`
- `ip_address`
- timestamps

Indexes:

- composite index on `model_type`, `model_id`
- single index on `action`

Important note:

- login/logout behavior may be represented by `model_type` values such as `login` or `logout`, even when `action` is `create`

### 11. Framework Tables

Other supporting tables in the current project include:

- `password_reset_tokens`
- `sessions`
- `cache`
- `jobs`
- `personal_access_tokens`

These are used by Laravel framework services such as session handling, queues, cache, and Sanctum.

## Relationship Map

```text
users --< user_roles >-- roles
users --< syarikat.created_by
users --< kontrak.created_by
users --< kontrak.pegawai_bertanggungjawab_id
users --< kontrak.pegawai_perhubungan_1_id
users --< kontrak.pegawai_perhubungan_2_id
users --< catatan_kontrak.user_id
users --< audit_log.user_id
users --< eot_kontrak.approved_by

jabatan --< bahagian_unit
jabatan --< kontrak
bahagian_unit --< kontrak
syarikat --< kontrak
kontrak --< catatan_kontrak
kontrak --< eot_kontrak
```

## Eloquent Models and Responsibilities

- `User`
  Handles authentication, role lookup, audit relationship, and contract ownership relationships.
- `Role`
  Stores master role labels and user assignments.
- `Jabatan`
  Represents departments/agencies.
- `BahagianUnit`
  Represents units under a department.
- `Syarikat`
  Stores company master records and links to contracts.
- `Kontrak`
  Represents the main contract entity and exposes helpers like `isExpired()` and `isExpiringSoon()`.
- `CatatanKontrak`
  Stores per-contract notes.
- `EotKontrak`
  Stores contract extension periods.
- `AuditLog`
  Stores system event history.

## Seeders

Standard database seeding currently runs:

- `RoleSeeder`
- `JabatanSeeder`
- `BahagianUnitSeeder`
- `AdminUserSeeder`
- `UserSeeder`
- `SyarikatSeeder`
- `KontrakSeeder`

Additional seeders used during development/testing include:

- `DashboardAlertSeeder`
- `MaklumatTidakLengkapSeeder`

Seeder purpose summary:

- `RoleSeeder` creates the master roles
- `JabatanSeeder` creates reference department records
- `UserSeeder` creates sample users for each role
- `SyarikatSeeder` creates sample companies
- `KontrakSeeder` creates sample contracts
- `DashboardAlertSeeder` creates targeted dashboard demo records

## Important Data Rules

- registration applications are stored in the same `users` table as approved users
- account approval is driven by `permohonan_status` and `is_active`
- the frontend registration form stores human-readable department and unit names in `users.jabatan_bahagian` and `users.bahagian_unit`
- contracts use foreign keys to `jabatan` and `bahagian_unit`
- incomplete contract dashboards depend on null checks in the contract table rather than a separate completeness flag
- audit reporting may need both `action` and `model_type` for accurate interpretation

## Recommended Maintenance Notes

- keep `jabatan` and `bahagian_unit` seed data aligned with the registration UI expectations
- preserve unique formats for `no_kontrak` and `no_rujukan_permohonan`
- validate role changes carefully because application roles and active roles can diverge during pending approval flows
