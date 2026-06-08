# ABM Module Summary

## Overview

The new ABM module is the server-rendered Laravel implementation for ABM upload, preview, summary, repository, audit trail, and dashboard flows.

The visible product label is now **ABM** in the UI and sidebar, while the existing internal route group still uses the `abm-v3` prefix and route names.

## What Was Built

- A dedicated ABM dashboard with a modern analytics layout.
- A streamlined ABM import page for Excel upload.
- A year-aware summary page that compares the selected year with the previous year.
- Repository, preview, and audit trail screens for uploaded ABM files.
- A sidebar section for ABM access in the shared app layout.

## Key Behaviors

- Uploads are handled through the ABM V3 controller flow.
- The upload page sends the file to the backend and redirects to preview after a successful extract.
- The summary page aggregates object AM totals by year.
- The dashboard and summary screens use ABM labels only in the UI.
- The upload flow now keeps the form clean and focused on file selection, filename display, and submit action.

## Fixes Completed

- Fixed the workbook parser year helper so uploads no longer fail with a missing `normalizeYearValue()` method.
- Fixed a `preg_match()` warning caused by punctuation-only header cells in the ABM V3 workbook parser.
- Removed the extra upload-page helper panels and duplicated header blocks that made the form look crowded.
- Updated the sidebar labels from V3 wording to ABM wording.
- Reworked the dashboard into a denser, modern analytics view.

## Main Files

- `frontend/ekontrak-web/app/Http/Controllers/AbmV3Controller.php`
- `frontend/ekontrak-web/app/Services/AbmV3WorkbookParser.php`
- `frontend/ekontrak-web/resources/views/abm-v3/dashboard.blade.php`
- `frontend/ekontrak-web/resources/views/abm-v3/import.blade.php`
- `frontend/ekontrak-web/resources/views/abm-v3/summary.blade.php`
- `frontend/ekontrak-web/resources/views/abm-v3/repository.blade.php`
- `frontend/ekontrak-web/resources/views/abm-v3/preview.blade.php`
- `frontend/ekontrak-web/resources/views/abm-v3/audit-trail.blade.php`
- `frontend/ekontrak-web/resources/views/components/layouts/app.blade.php`

## Notes

- The module still uses `abm.v3` route names internally for compatibility.
- The public-facing copy and sidebar now say **ABM** instead of **V3**.
- If the team wants a full rename later, the next step would be a route and controller alias cleanup, but that was intentionally left unchanged to avoid breaking links.
