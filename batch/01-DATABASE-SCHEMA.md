# Prompt 01 — Database Schema (MySQL 9.0)

## Context
Generate the full MySQL 9.0 database schema for the eKontrak system. This schema is used exclusively by `ekontrak-api` (the Laravel backend). The `ekontrak-web` project never connects to the database directly.

---

## Instructions

Create Laravel 12 migration files for each table below. Use `php artisan make:migration` naming convention. All tables must:
- Use `id()` as primary key (BIGINT UNSIGNED, auto-increment)
- Include `timestamps()` (created_at, updated_at)
- Use `softDeletes()` where indicated
- Use UTF8MB4 character set

---

## Tables Required

### 1. `users`
```
id
ic_number          VARCHAR(12) UNIQUE NOT NULL  -- Malaysian IC (no dashes)
name               VARCHAR(255) NOT NULL
email              VARCHAR(255) UNIQUE NOT NULL
jabatan_bahagian   VARCHAR(255) NULL            -- Department/Division
bahagian_unit      VARCHAR(255) NULL            -- Unit
telefon_pejabat    VARCHAR(20) NULL
telefon_bimbit     VARCHAR(20) NULL
password           VARCHAR(255) NOT NULL        -- bcrypt hashed
is_active          BOOLEAN DEFAULT TRUE
mfa_secret         VARCHAR(255) NULL            -- TOTP secret
source             ENUM('BTM','JBPM','AGENSI') DEFAULT 'BTM'
last_login_at      TIMESTAMP NULL
timestamps
softDeletes
```

### 2. `roles`
```
id
name               VARCHAR(50) UNIQUE           -- e.g. 'admin', 'pendaftar_kontrak'
label              VARCHAR(100)                 -- Display label in Malay
timestamps
```

Seed these roles:
- `admin` → Admin
- `pendaftar_kontrak` → Pendaftar Kontrak
- `pemilik_projek` → Pemilik Projek
- `admin_sistem` → Admin Sistem
- `pegawai_undang_undang` → Pegawai Undang-Undang

### 3. `user_roles` (pivot)
```
id
user_id            FK users.id
role_id            FK roles.id
UNIQUE(user_id, role_id)
timestamps
```

### 4. `jabatan` (Departments — dropdown source)
```
id
kod                VARCHAR(20) UNIQUE
nama               VARCHAR(255)
timestamps
```

### 5. `bahagian_unit` (Units under departments)
```
id
jabatan_id         FK jabatan.id
kod                VARCHAR(20)
nama               VARCHAR(255)
timestamps
```

### 6. `syarikat` (Vendor Companies)
```
id
nama_syarikat      VARCHAR(255) NOT NULL
alamat             TEXT NOT NULL
negeri             VARCHAR(100) NOT NULL
pegawai_hubungi_1_nama     VARCHAR(255) NULL
pegawai_hubungi_1_email    VARCHAR(255) NULL
pegawai_hubungi_1_tel_pejabat  VARCHAR(20) NULL
pegawai_hubungi_1_tel_hp   VARCHAR(20) NULL
pegawai_hubungi_2_nama     VARCHAR(255) NULL
pegawai_hubungi_2_email    VARCHAR(255) NULL
pegawai_hubungi_2_tel_pejabat  VARCHAR(20) NULL
pegawai_hubungi_2_tel_hp   VARCHAR(20) NULL
pegawai_hubungi_3_nama     VARCHAR(255) NULL
pegawai_hubungi_3_email    VARCHAR(255) NULL
pegawai_hubungi_3_tel_pejabat  VARCHAR(20) NULL
pegawai_hubungi_3_tel_hp   VARCHAR(20) NULL
created_by         FK users.id
timestamps
softDeletes
```

### 7. `kontrak` (Contracts — core table)
```
id
no_kontrak         VARCHAR(50) UNIQUE NOT NULL   -- e.g. BTM/MG/2025
tajuk_kontrak      TEXT NOT NULL
syarikat_id        FK syarikat.id
nilai_kontrak      DECIMAL(15,2) NOT NULL
kaedah_perolehan   ENUM('SEBUT HARGA','TENDER','RUNDINGAN TERUS','PEMBELIAN TERUS')
kategori_perolehan ENUM('PERKHIDMATAN','BEKALAN','KERJA')
pihak_berkuasa_melulus_nama   VARCHAR(255)
pihak_berkuasa_melulus_tarikh DATE NULL
diluluskan_tarikh  DATE NULL
ditandatangani_tarikh DATE NULL
mula_tarikh        DATE NULL
tamat_tarikh       DATE NULL
tarikh_sst         DATE NULL                    -- SST/GST date
status_kontrak     ENUM('DRAF','DALAM_PELAKSANAAN','KONTRAK_SELESAI','EOT') DEFAULT 'DRAF'
status_draf_kompan BOOLEAN DEFAULT FALSE        -- "Telah Draf Kompan"
tarikh_draf_hantar_sistem DATE NULL
catatan_kontrak    TEXT NULL
jabatan_id         FK jabatan.id NULL
bahagian_unit_id   FK bahagian_unit.id NULL
pegawai_bertanggungjawab_id FK users.id NULL
pegawai_perhubungan_1_id    FK users.id NULL
pegawai_perhubungan_2_id    FK users.id NULL
created_by         FK users.id
timestamps
softDeletes
```

### 8. `catatan_kontrak` (Contract Notes/Remarks log)
```
id
kontrak_id         FK kontrak.id
user_id            FK users.id
status             VARCHAR(100)                 -- Status at time of note
tahap              VARCHAR(100)                 -- Stage/phase
catatan            TEXT
timestamps
```

### 9. `eot_kontrak` (Extension of Time records)
```
id
kontrak_id         FK kontrak.id
tarikh_mula_baru   DATE
tarikh_tamat_baru  DATE
sebab              TEXT
approved_by        FK users.id NULL
timestamps
```

### 10. `audit_log`
```
id
user_id            FK users.id NULL
action             VARCHAR(100)
model_type         VARCHAR(100)
model_id           BIGINT UNSIGNED NULL
payload            JSON NULL
ip_address         VARCHAR(45)
timestamps
```

---

## Seeders Required

Create seeders for:
1. `RoleSeeder` — seed 5 roles above
2. `JabatanSeeder` — seed departments: BKP, BKT, BIS, BUK, BTM, JKT, JPET, APM, JKT, JUN, PIN, PLANM, TPPS, URS
3. `AdminUserSeeder` — create 1 default Admin user (ic: 000000000000, password: Admin@1234!)

---

## Notes
- All `ENUM` values must be stored in English internally; labels shown in Malay in UI
- `no_kontrak` format: `{BAHAGIAN}/{TYPE}/{YEAR}` e.g. `BTM/MG/2025`
- Index: `kontrak.status_kontrak`, `kontrak.tamat_tarikh`, `kontrak.no_kontrak`, `users.ic_number`
