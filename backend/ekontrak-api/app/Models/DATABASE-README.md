# eKontrak API — Database Layer

## Migration Run Order

Migrations are prefixed with timestamps to enforce FK-safe execution order:

| Order | File | Table |
|-------|------|-------|
| 1 | `000001_create_roles_table` | `roles` |
| 2 | `000002_create_jabatan_table` | `jabatan` |
| 3 | `000003_create_bahagian_unit_table` | `bahagian_unit` → FK: jabatan |
| 4 | `000004_create_users_table` | `users` |
| 5 | `000005_create_user_roles_table` | `user_roles` → FK: users, roles |
| 6 | `000006_create_syarikat_table` | `syarikat` → FK: users |
| 7 | `000007_create_kontrak_table` | `kontrak` → FK: syarikat, jabatan, bahagian_unit, users (×4) |
| 8 | `000008_create_catatan_kontrak_table` | `catatan_kontrak` → FK: kontrak, users |
| 9 | `000009_create_eot_kontrak_table` | `eot_kontrak` → FK: kontrak, users |
| 10 | `000010_create_audit_log_table` | `audit_log` → FK: users |

## Commands

```bash
# Fresh migrate + seed (development)
php artisan migrate:fresh --seed

# Production (run migrations only, seed manually)
php artisan migrate
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=JabatanSeeder
php artisan db:seed --class=AdminUserSeeder
```

## Models & Relationships

```
User ──< user_roles >── Role
User ──< kontrak (created_by, pegawai_bertanggungjawab, perhubungan_1, perhubungan_2)
User ──< syarikat (created_by)
User ──< catatan_kontrak
User ──< audit_log

Jabatan ──< bahagian_unit
Jabatan ──< kontrak

Syarikat ──< kontrak

Kontrak ──< catatan_kontrak
Kontrak ──< eot_kontrak
```

## Soft Deletes

Tables with `softDeletes()`: `users`, `syarikat`, `kontrak`

Queries will automatically exclude soft-deleted records.
Use `withTrashed()` to include them when needed (e.g. audit views).

## Important Notes

- **`kontrak` table** has 4 separate FK references to `users`:
  `created_by`, `pegawai_bertanggungjawab_id`, `pegawai_perhubungan_1_id`, `pegawai_perhubungan_2_id`
  — all use `nullOnDelete()` except `created_by` which uses `restrictOnDelete()`

- **`no_kontrak`** format: `{BAHAGIAN}/{TYPE}/{YEAR}` e.g. `BTM/MG/2025`
  — validated in API request layer, not enforced at DB level

- **MFA secret** (`mfa_secret`) is hidden in `User::$hidden` — never returned in API responses

- **Default admin credentials** (change after first login!):
  - IC: `000000000000`
  - Email: `admin@ekontrak.gov.my`
  - Password: `Admin@1234!`
