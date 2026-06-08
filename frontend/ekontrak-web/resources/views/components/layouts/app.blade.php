<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — eKontrak</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ekontrak.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --white: #ffffff;
            --slate-50: #f8fafc; --slate-100: #f1f5f9; --slate-200: #e2e8f0;
            --slate-300: #cbd5e1; --slate-400: #94a3b8; --slate-500: #64748b;
            --slate-600: #475569; --slate-700: #334155; --slate-800: #1e293b;
            --slate-900: #0f172a;
            /* Legacy aliases for shared components */
            --gray-50: #f8fafc; --gray-100: #f1f5f9;
            --gray-200: #e2e8f0; --gray-300: #cbd5e1; --gray-400: #94a3b8;
            --gray-500: #64748b; --gray-700: #334155; --gray-900: #0f172a;
            --blue: #2563eb; --blue-hover: #1d4ed8;
            --red: #ef4444; --green: #22c55e; --amber: #f59e0b;
            --sidebar-width: 256px;
        }
        body { font-family: 'Inter', sans-serif; background: var(--slate-50); display: flex; min-height: 100vh; color: var(--slate-900); }

        /* ── Sidebar (AdminSidebar.tsx) ── */
        .sidebar { width: var(--sidebar-width); background: var(--slate-900); color: var(--slate-300); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; height: 100vh; z-index: 100; transition: transform 0.3s ease; overflow-y: auto; flex-shrink: 0; }
        .sidebar.collapsed { transform: translateX(calc(-1 * var(--sidebar-width))); }
        .sidebar-brand { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--slate-800); display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; }
        .brand-logo { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .brand-logo img { width: 36px; height: 36px; object-fit: contain; filter: brightness(0) invert(1); }
        .brand-text { font-size: 1.1rem; font-weight: 700; color: white; letter-spacing: -0.01em; }
        .brand-sub  { font-size: 0.65rem; color: var(--slate-500); letter-spacing: 0.08em; text-transform: uppercase; }
        /* User Profile (matches AdminSidebar.tsx p-6 section) */
        .sidebar-user { padding: 1.5rem; border-bottom: 1px solid var(--slate-800); }
        .sidebar-user-name { font-size: 1.125rem; font-weight: 700; color: white; line-height: 1.3; margin-bottom: 0.25rem; }
        .sidebar-user-dept { font-size: 0.75rem; font-weight: 500; color: var(--slate-400); margin-bottom: 0.75rem; line-height: 1.5; }
        .role-badge { display: inline-flex; align-items: center; padding: 0.125rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: rgba(250,204,21,0.15); color: #facc15; border: 1px solid rgba(250,204,21,0.3); }
        /* Nav */
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 1rem 0; }
        .nav-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem; color: var(--slate-400); text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: all 0.15s; cursor: pointer; border: none; background: none; width: 100%; text-align: left; border-left: 2px solid transparent; }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: var(--slate-200); }
        .nav-item.active { background: rgba(37,99,235,0.2); color: white; border-left-color: #facc15; }
        .nav-item svg { width: 20px; height: 20px; flex-shrink: 0; }
        /* Pentadbiran section header */
        .nav-section-header { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1.5rem; font-size: 0.875rem; font-weight: 500; color: var(--slate-200); background: rgba(255,255,255,0.05); cursor: pointer; border: none; width: 100%; text-align: left; border-left: 2px solid transparent; }
        .nav-section-header:hover { background: rgba(255,255,255,0.07); }
        .nav-hdr-left { display: flex; align-items: center; gap: 0.75rem; }
        .nav-hdr-left svg { width: 20px; height: 20px; flex-shrink: 0; color: var(--slate-400); }
        .nav-chevron { width: 16px; height: 16px; transition: transform 0.2s; color: var(--slate-500); flex-shrink: 0; }
        .nav-chevron.open { transform: rotate(180deg); }
        /* Sub-menu items */
        .nav-submenu { display: none; }
        .nav-submenu.open { display: block; }
        .nav-sub-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem 1.5rem 0.625rem 3.5rem; color: var(--slate-400); text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: all 0.15s; border-left: 2px solid transparent; }
        .nav-sub-item:hover { background: rgba(255,255,255,0.05); color: var(--slate-200); }
        .nav-sub-item.active { background: rgba(37,99,235,0.2); color: white; border-left-color: #facc15; }
        .nav-sub-item svg { width: 16px; height: 16px; flex-shrink: 0; }
        .sidebar-footer { padding: 1rem; border-top: 1px solid var(--slate-800); }

        /* ── Main (AdminHeader.tsx + content area) ── */
        .main-wrap { margin-left: var(--sidebar-width); flex: 1; display: flex; flex-direction: column; transition: margin-left 0.3s; min-width: 0; }
        .main-wrap.expanded { margin-left: 0; }
        .topbar { background: white; border-bottom: 1px solid var(--slate-200); padding: 0 1.5rem; height: 64px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; flex-shrink: 0; }
        .topbar-left { display: flex; align-items: center; gap: 1rem; }
        .hamburger { background: none; border: none; cursor: pointer; color: var(--slate-500); padding: 0.5rem; border-radius: 0.5rem; transition: background 0.15s; }
        .hamburger:hover { background: var(--slate-100); }
        .topbar-brand { display: flex; align-items: center; gap: 0.75rem; }
        .topbar-logo { width: 48px; height: 48px; object-fit: contain; }
        .topbar-title { font-size: 1.25rem; font-weight: 700; letter-spacing: -0.025em; color: var(--slate-900); }
        .topbar-right { display: flex; align-items: center; gap: 0.75rem; }
        .topbar-user { font-size: 0.875rem; color: var(--slate-500); }
        .btn-logout { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; color: var(--slate-600); border: 1px solid var(--slate-200); border-radius: 0.5rem; background: white; cursor: pointer; transition: all 0.2s; }
        .btn-logout:hover { background: var(--slate-50); color: var(--slate-900); }
        .btn-logout svg { width: 16px; height: 16px; }
        .main-content { flex: 1; padding: 2rem 2rem 4.75rem; }

        /* ── Toast ── */
        .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.5rem; width: min(92vw, 430px); }
        .toast { border-radius: 10px; font-size: 0.84rem; box-shadow: 0 8px 24px rgba(15,23,42,0.14); display: flex; align-items: flex-start; gap: 0.65rem; min-width: 280px; max-width: 100%; border: 1px solid transparent; border-left-width: 4px; padding: 0.75rem 0.75rem 0.75rem 0.7rem; transform: translateY(-6px); opacity: 0; transition: transform 0.18s ease, opacity 0.18s ease; }
        .toast-show { transform: translateY(0); opacity: 1; }
        .toast-out { transform: translateY(-6px); opacity: 0; }
        .toast-icon { margin-top: 0.1rem; flex-shrink: 0; }
        .toast-content { flex: 1; min-width: 0; }
        .toast-title { font-size: 0.82rem; font-weight: 700; margin-bottom: 0.14rem; }
        .toast-message { font-size: 0.8rem; line-height: 1.35; color: #475569; }
        .toast-close { border: 0; background: transparent; color: #94a3b8; font-size: 1.05rem; line-height: 1; cursor: pointer; padding: 0.08rem 0.18rem; border-radius: 4px; }
        .toast-close:hover { background: rgba(148,163,184,0.15); color: #475569; }
        .toast-success { background: #ecfdf3; color: #15803d; border-color: #bbf7d0; }
        .toast-warning { background: #fff7e6; color: #b45309; border-color: #f8d08a; }
        .toast-error   { background: #fef2f2; color: #be123c; border-color: #fecdd3; }
        .toast-info    { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }

        /* ── Shared ── */
        .card { background: white; border-radius: 16px; border: 1px solid var(--slate-200); box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .card-header { padding: 1.5rem 1.75rem; border-bottom: 1px solid var(--slate-100); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; }
        .card-body   { padding: 1.5rem 1.75rem; }
        .stat-card {
            background: white;
            border: 1px solid var(--slate-200);
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(15,23,42,0.06);
            padding: 1rem 1rem 0.9rem;
            transition: all 0.2s ease;
            min-height: 122px;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        .stat-card--clickable { cursor: pointer; }
        .stat-card--clickable:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(15,23,42,0.12); border-color: #bfdbfe; }
        .stat-card__top { display:flex; align-items:center; justify-content:space-between; margin-bottom: 0.2rem; }
        .stat-card__icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            background: #eff6ff;
        }
        .stat-card__icon svg { width: 18px; height: 18px; }
        .stat-card__pct {
            font-size: 0.72rem;
            font-weight: 700;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.15rem 0.45rem;
        }
        .stat-card__value { font-size: 1.6rem; line-height: 1; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
        .stat-card__label { font-size: 0.8rem; font-weight: 700; color: #475569; }
        .stat-card__hint { margin-top: auto; font-size: 0.72rem; color: #94a3b8; }
        .stat-card--red .stat-card__icon { color: #e11d48; background: #fff1f2; }
        .stat-card--amber .stat-card__icon { color: #b45309; background: #fffbeb; }
        .stat-card--blue .stat-card__icon { color: #1d4ed8; background: #eff6ff; }
        .stat-card--purple .stat-card__icon { color: #7e22ce; background: #f5f3ff; }
        .stat-card--green .stat-card__icon { color: #059669; background: #ecfdf5; }
        .page-title    { font-size: 1.875rem; font-weight: 700; color: var(--slate-900); letter-spacing: -0.025em; }
        .page-subtitle { font-size: 0.875rem; color: var(--slate-500); margin-top: 0.25rem; }
        .btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 7px; font-size: 0.82rem; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; font-family: 'Inter', sans-serif; white-space: nowrap; }
        .btn-primary { background: var(--blue); color: white; }
        .btn-primary:hover { background: var(--blue-hover); }
        .btn-outline { background: white; color: var(--gray-700); border: 1.5px solid var(--gray-300); }
        .btn-outline:hover { border-color: var(--gray-500); }
        .btn-outline-blue { background: white; color: var(--blue); border: 1.5px solid var(--blue); }
        .btn-outline-blue:hover { background: var(--blue); color: white; }
        .btn-outline-red { background: white; color: var(--red); border: 1.5px solid var(--red); }
        .btn-outline-red:hover { background: var(--red); color: white; }
        .btn-danger { background: var(--red); color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.775rem; }
        .form-group { margin-bottom: 1.15rem; }
        .form-label { display: block; font-size: 0.78rem; font-weight: 600; color: var(--gray-700); margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.04em; }
        .form-control { width: 100%; padding: 0.6rem 0.875rem; border: 1.5px solid var(--gray-300); border-radius: 7px; font-size: 0.875rem; font-family: 'DM Sans', sans-serif; color: var(--gray-900); outline: none; transition: all 0.2s; }
        .form-control:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .form-control.pw { padding-right: 2.5rem; }
        .input-wrap { position: relative; }
        .toggle-pw { position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-400); }
        .invalid-note { font-size: 0.75rem; color: var(--red); margin-top: 0.25rem; }
        .form-hint { font-size: 0.72rem; color: var(--gray-400); margin-top: 0.25rem; }
        /* ── Table ── */
        .table-wrap {
            overflow-x: auto;
            border-radius: 14px;
            border: 1px solid #d6e0ec;
            background: #ffffff;
            box-shadow: 0 3px 10px rgba(15, 23, 42, 0.06);
        }
        table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.84rem; min-width: 980px; }
        thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafc;
            padding: 0.92rem 1rem;
            text-align: left;
            font-size: 0.73rem;
            font-weight: 700;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            border-bottom: 1px solid #d6e0ec;
            white-space: nowrap;
        }
        .th-icon-wrap { display: inline-flex; align-items: center; gap: 0.26rem; }
        .th-icon-wrap svg { width: 12px; height: 12px; color: #94a3b8; }
        tbody td {
            padding: 0.94rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            color: var(--slate-700);
            line-height: 1.5;
            background: white;
            transition: background-color 0.15s ease;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        tbody tr:hover td { background: #f7fbff; }
        tbody tr:hover td { color: var(--slate-900); }
        /* ── Pills ── */
        .pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.24rem 0.7rem;
            border-radius: 999px;
            font-size: 0.66rem;
            font-weight: 800;
            white-space: nowrap;
            letter-spacing: 0.03em;
            border: 1px solid transparent;
        }
        .pill-blue   { background: #dbeafe; color: #1d4ed8; }
        .pill-green  { background: #dcfce7; color: #15803d; }
        .pill-orange { background: #ffedd5; color: #c2410c; }
        .pill-red    { background: #fee2e2; color: #b91c1c; }
        .pill-purple { background: #f3e8ff; color: #7e22ce; }
        .pill-gray   { background: #f1f5f9; color: #475569; }
        /* ── Pagination ── */
        .pag-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 0.95rem 1rem;
            border-top: 1px solid #d6e0ec;
            background: linear-gradient(180deg, #fbfdff 0%, #f8fafc 100%);
        }
        .pag-info { font-size: 0.78rem; color: var(--slate-500); font-weight: 600; }
        .pag-btns { display: flex; align-items: center; gap: 0.3rem; }
        .page-btn {
            min-width: 32px;
            height: 32px;
            padding: 0 0.5rem;
            border: 1px solid #dbe3ee;
            background: #fff;
            color: var(--slate-500);
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 9px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            box-shadow: 0 1px 2px rgba(15,23,42,0.04);
        }
        .page-btn:hover:not(:disabled):not(.active) { background: #f8fbff; border-color: var(--blue); color: var(--blue); }
        .page-btn.active { background: var(--blue); color: white; border-color: var(--blue); box-shadow: 0 3px 8px rgba(37,99,235,0.22); }
        .page-btn:disabled { opacity: 0.35; cursor: not-allowed; }
        /* ── Filter / Search bar ── */
        .filter-bar { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 1rem; padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid var(--slate-200); border-radius: 10px; }
        .filter-label { font-size: 0.75rem; font-weight: 600; color: var(--slate-500); white-space: nowrap; }
        .filter-hint { font-size: 0.72rem; color: #ef4444; }
        .filter-input { padding: 0.45rem 0.75rem; border: 1.5px solid var(--slate-200); border-radius: 7px; font-size: 0.82rem; font-family: 'Inter', sans-serif; color: var(--slate-700); outline: none; background: white; }
        .filter-input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }
        .filter-select { padding: 0.45rem 0.75rem; border: 1.5px solid var(--slate-200); border-radius: 7px; font-size: 0.82rem; font-family: 'Inter', sans-serif; color: var(--slate-700); outline: none; background: white; cursor: pointer; }
        .filter-select:focus { border-color: var(--blue); }
        .filter-spacer { flex: 1; }
        .per-page-wrap { display: flex; align-items: center; gap: 0.45rem; font-size: 0.78rem; color: var(--slate-500); font-weight: 500; }
        .per-page-select { padding: 0.42rem 0.65rem; border: 1.5px solid var(--slate-200); border-radius: 7px; font-size: 0.82rem; font-family: 'Inter', sans-serif; color: var(--slate-700); background: white; outline: none; cursor: pointer; }
        .per-page-select:focus { border-color: var(--blue); }
        .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.55); backdrop-filter: blur(2px); z-index: 200; display: none; align-items: center; justify-content: center; padding: 1rem; }
        .modal-overlay.open { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 540px; max-height: 92vh; overflow-y: auto; box-shadow: 0 24px 60px rgba(0,0,0,0.18); animation: modalIn 0.25s ease; display: flex; flex-direction: column; }
        @keyframes modalIn { from { opacity: 0; transform: translateY(-1rem) scale(0.97); } to { opacity: 1; transform: none; } }
        .modal-header { padding: 1.4rem 1.75rem; border-bottom: 1px solid var(--slate-200); display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border-radius: 20px 20px 0 0; flex-shrink: 0; }
        .modal-header-content { display: flex; align-items: center; gap: 0.875rem; }
        .modal-icon-bubble { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .modal-icon-bubble.blue   { background: rgba(37,99,235,0.1); color: #2563eb; }
        .modal-icon-bubble.green  { background: rgba(16,185,129,0.1); color: #059669; }
        .modal-icon-bubble.amber  { background: rgba(245,158,11,0.1); color: #d97706; }
        .modal-icon-bubble svg { width: 20px; height: 20px; }
        .modal-title { font-size: 1.05rem; font-weight: 700; color: var(--slate-900); }
        .modal-subtitle { font-size: 0.74rem; color: var(--slate-500); margin-top: 0.15rem; }
        .modal-close { background: none; border: none; cursor: pointer; color: var(--slate-400); padding: 0.4rem; border-radius: 8px; transition: all 0.15s; }
        .modal-close:hover { color: var(--slate-700); background: var(--slate-100); }
        .modal-body { padding: 1.75rem; overflow-y: auto; flex: 1; }
        .modal-footer { padding: 1.1rem 1.75rem; border-top: 1px solid var(--slate-100); display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; }
        /* Border-bottom modal tabs */
        .modal-tabs { display: flex; padding: 0 1.75rem; border-bottom: 2px solid var(--slate-200); background: #f8fafc; flex-shrink: 0; }
        .modal-tab-btn { padding: 0.875rem 1.25rem; border: none; background: none; font-size: 0.875rem; font-weight: 600; color: var(--slate-500); cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.15s; white-space: nowrap; }
        .modal-tab-btn:hover { color: var(--slate-700); }
        .modal-tab-btn.active { color: #2563eb; border-bottom-color: #2563eb; }
        /* Legacy box tabs kept for compat */
        .tab-bar { display: flex; gap: 0; border: 1.5px solid var(--slate-200); border-radius: 8px; overflow: hidden; margin-bottom: 1.25rem; }
        .tab-btn { flex: 1; padding: 0.5rem; font-size: 0.82rem; font-weight: 600; cursor: pointer; border: none; background: white; color: var(--slate-500); transition: all 0.2s; }
        .tab-btn.active { background: var(--blue); color: white; }
        .checkbox-group { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        .checkbox-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--gray-700); cursor: pointer; }
        .checkbox-item input { width: 15px; height: 15px; accent-color: var(--blue); cursor: pointer; }
        .search-wrap { position: relative; flex: 1; min-width: 200px; max-width: 360px; }
        .search-wrap svg { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--slate-400); width: 15px; height: 15px; pointer-events: none; }
        .search-input { width: 100%; padding: 0.5rem 0.875rem 0.5rem 2.35rem; border: 1.5px solid var(--slate-200); border-radius: 9px; font-size: 0.82rem; font-family: 'Inter', sans-serif; outline: none; color: var(--slate-700); background: white; transition: all 0.15s; }
        .search-input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }
        /* Emerald section header */
        .section-hdr { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 700; color: #065f46; display: flex; align-items: center; gap: 0.5rem; }
        .section-hdr::before { content: '>'; color: #10b981; font-weight: 900; }
        /* Report cards */
        .report-card { display: flex; align-items: flex-start; gap: 1rem; padding: 1.25rem 1.5rem; background: white; border: 1px solid var(--slate-200); border-radius: 14px; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .report-card:hover { border-color: #2563eb; box-shadow: 0 4px 16px rgba(37,99,235,0.12); transform: translateY(-1px); }
        .report-card-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .report-card-icon.blue    { background: #dbeafe; color: #1d4ed8; }
        .report-card-icon.emerald { background: #d1fae5; color: #059669; }
        .report-card-icon svg { width: 22px; height: 22px; }
        .report-card-body { flex: 1; min-width: 0; }
        .report-card-title { font-size: 0.9rem; font-weight: 700; color: var(--slate-800); line-height: 1.4; }
        .report-card-desc  { font-size: 0.78rem; color: var(--slate-500); margin-top: 0.25rem; line-height: 1.4; }
        /* Export bar */
        .action-bar { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap; }
        .btn-export { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.45rem 0.875rem; font-size: 0.8rem; font-weight: 600; border-radius: 8px; cursor: pointer; border: none; transition: all 0.18s; text-decoration: none; }
        .btn-export.blue  { background: #1e40af; color: white; }
        .btn-export.blue:hover  { background: #1d4ed8; }
        .btn-export.green { background: #059669; color: white; }
        .btn-export.green:hover { background: #047857; }
        /* Back link */
        .back-link-btn { display: inline-flex; align-items: center; gap: 0.4rem; color: var(--slate-500); font-size: 0.84rem; font-weight: 600; text-decoration: none; margin-bottom: 1.25rem; padding: 0.4rem 0.75rem; background: white; border: 1.5px solid var(--slate-200); border-radius: 8px; transition: all 0.15s; }
        .back-link-btn:hover { color: #2563eb; border-color: #2563eb; background: #eff6ff; }
        /* Form control upgrade */
        .form-control { width: 100%; padding: 0.6rem 0.875rem; border: 1.5px solid var(--slate-200); border-radius: 10px; font-size: 0.875rem; font-family: 'Inter', sans-serif; color: var(--slate-800); outline: none; transition: all 0.2s; background: white; }
        .form-control:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .swal-theme-popup { border-radius: 14px; border: 1px solid #e2e8f0; }
        .swal-theme-title { color: #0f172a; font-size: 1.05rem; }
        .swal-theme-content { color: #475569; font-size: 0.88rem; }
        .swal-theme-confirm {
            background: #2563eb !important;
            color: #ffffff !important;
            border: 1px solid #2563eb !important;
            border-radius: 8px !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            padding: 0.5rem 1rem !important;
        }
        .swal-theme-confirm:hover { background: #1d4ed8 !important; border-color: #1d4ed8 !important; }
        .swal-theme-cancel {
            background: #ffffff !important;
            color: #334155 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            padding: 0.5rem 1rem !important;
        }
        .swal-theme-cancel:hover { background: #f8fafc !important; border-color: #94a3b8 !important; }
        .swal2-actions {
            gap: 0.5rem !important;
        }
        .swal2-actions .swal2-styled {
            margin: 0 !important;
        }

        .global-footer {
            width: 100%;
            border-top: 3px solid #dc2626;
            margin-top: auto;
            background: #4b5563;
            color: #e5e7eb;
        }

        .global-footer-contact {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
            align-items: center;
            padding: 0.72rem 1.1rem;
            background: #6b7280;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .global-footer-item {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            justify-content: center;
            text-align: center;
            line-height: 1.35;
        }

        .global-footer-item svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            color: #f3f4f6;
        }

        .global-footer-note {
            text-align: center;
            background: #5b6470;
            color: #f3f4f6;
            padding: 0.62rem 1rem;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .global-footer-copy {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            padding: 0.82rem 1rem;
            background: #4b5563;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .global-footer-copy img {
            width: 34px;
            height: 34px;
            object-fit: contain;
            border-radius: 6px;
            background: #ffffff;
            padding: 2px;
        }

        @media (max-width: 900px) {
            .global-footer-contact {
                grid-template-columns: 1fr;
                gap: 0.45rem;
                padding: 0.8rem;
            }

            .global-footer-item {
                justify-content: flex-start;
                text-align: left;
            }

            .global-footer-note,
            .global-footer-copy {
                font-size: 0.86rem;
                padding-left: 0.9rem;
                padding-right: 0.9rem;
            }
        }
    </style>
    @include('components.design-system')
    @stack('styles')
</head>
<body>

{{-- ════════ Sidebar (AdminSidebar.tsx) ════════ --}}
<aside class="sidebar" id="sidebar">

    {{-- User Profile --}}
    <div class="sidebar-user">
        <div class="sidebar-user-name">{{ \App\Helpers\AuthHelper::userName() }}</div>
        @php
            $dept = session('user.jabatan_bahagian') ?? session('user.bahagian_unit') ?? null;
            $roleLabels = [
                'admin'                 => 'Admin',
                'admin_sistem'          => 'Admin Sistem',
                'pendaftar_kontrak'     => 'Pendaftar Kontrak',
                'pemilik_projek'        => 'Pemilik Projek',
                'pegawai_undang_undang' => 'Peg. Undang-Undang',
            ];
            $sessionRoles = array_values(array_unique(array_filter(session('roles', []), fn ($role) => is_string($role) && $role !== '')));

            // Union permissions for multi-role users while keeping menu items unique.
            $menuByRole = [
                'admin' => [
                    'dashboard' => true,
                    'kontrak' => false,
                    'syarikat' => false,
                    'laporan' => false,
                    'admin_section' => true,
                    'audit_trail' => true,
                    'change_password' => true,
                    'abm_v3' => true,
                    'ppt_section' => true,
                ],
                'admin_sistem' => [
                    'dashboard' => true,
                    'kontrak' => true,
                    'syarikat' => true,
                    'laporan' => true,
                    'admin_section' => false,
                    'audit_trail' => false,
                    'change_password' => true,
                    'abm_v3' => true,
                    'ppt_section' => true,
                ],
                'pendaftar_kontrak' => [
                    'dashboard' => true,
                    'kontrak' => true,
                    'syarikat' => true,
                    'laporan' => true,
                    'admin_section' => false,
                    'audit_trail' => false,
                    'change_password' => true,
                    'abm_v3' => false,
                    'ppt_section' => false,
                ],
                'pemilik_projek' => [
                    'dashboard' => true,
                    'kontrak' => true,
                    'syarikat' => false,
                    'laporan' => true,
                    'admin_section' => false,
                    'audit_trail' => false,
                    'change_password' => true,
                    'abm_v3' => false,
                    'ppt_section' => false,
                ],
                'pegawai_undang_undang' => [
                    'dashboard' => true,
                    'kontrak' => false,
                    'syarikat' => false,
                    'laporan' => false,
                    'admin_section' => false,
                    'audit_trail' => false,
                    'change_password' => true,
                    'abm_v3' => false,
                    'ppt_section' => false,
                ],
            ];

            $menuAccess = [
                'dashboard' => true,
                'kontrak' => false,
                'syarikat' => false,
                'laporan' => false,
                'admin_section' => false,
                'audit_trail' => false,
                'change_password' => true,
                'abm_v3' => false,
                'ppt_section' => false,
            ];

            foreach ($sessionRoles as $role) {
                foreach (($menuByRole[$role] ?? []) as $key => $allowed) {
                    if ($allowed) {
                        $menuAccess[$key] = true;
                    }
                }
            }
        @endphp
        @if($dept)
            <div class="sidebar-user-dept">{{ $dept }}</div>
        @endif
        @foreach($sessionRoles as $role)
            <span class="role-badge">{{ $roleLabels[$role] ?? $role }}</span>
        @endforeach
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">

        {{-- Halaman Utama → Dashboard --}}
        @if($menuAccess['dashboard'])
        <a href="{{ url('/dashboard') }}" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Halaman Utama
        </a>
        @endif

        @if($menuAccess['kontrak'])
        <a href="{{ url('/kontrak') }}" class="nav-item {{ request()->is('kontrak*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Senarai Kontrak
        </a>
        @endif
        @if($menuAccess['syarikat'])
        <a href="{{ url('/syarikat') }}" class="nav-item {{ request()->is('syarikat*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Maklumat Syarikat
        </a>
        @endif
        @if($menuAccess['laporan'])
        <a href="{{ url('/laporan') }}" class="nav-item {{ request()->is('laporan*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Laporan
        </a>
        @endif

        @if($menuAccess['abm_v3'])
        {{-- ABM expandable section --}}
        <button class="nav-section-header" onclick="toggleSubMenu('submenu-abm-v3')">
            <div class="nav-hdr-left">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"/></svg>
                ABM
            </div>
            <svg class="nav-chevron {{ request()->is('abm-v3*') ? 'open' : '' }}" id="arrow-submenu-abm-v3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="nav-submenu {{ request()->is('abm-v3*') ? 'open' : '' }}" id="submenu-abm-v3">
            <a href="{{ route('abm.v3.dashboard') }}" class="nav-sub-item {{ request()->is('abm-v3/dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard ABM
            </a>
            <a href="{{ route('abm.v3.import') }}" class="nav-sub-item {{ request()->is('abm-v3/import') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Muat Naik ABM
            </a>
            <a href="{{ route('abm.v3.summary') }}" class="nav-sub-item {{ request()->is('abm-v3/summary') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 14l3-3 3 2 4-6"/></svg>
                Ringkasan
            </a>
            <a href="{{ route('abm.v3.repository') }}" class="nav-sub-item {{ request()->is('abm-v3/repository') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                Repositori
            </a>
            <a href="{{ route('abm.v3.audit') }}" class="nav-sub-item {{ request()->is('abm-v3/audit-trail') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 12h6m-6 4h6"/></svg>
                Audit Trail
            </a>
        </div>
        @endif

        @if($menuAccess['ppt_section'])
        {{-- PPT expandable section --}}
        <button class="nav-section-header" onclick="toggleSubMenu('submenu-ppt-v3')">
            <div class="nav-hdr-left">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h14M4 18h10"/></svg>
                PPT
            </div>
            <svg class="nav-chevron {{ request()->is('abm-v3/ppt*') ? 'open' : '' }}" id="arrow-submenu-ppt-v3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="nav-submenu {{ request()->is('abm-v3/ppt*') ? 'open' : '' }}" id="submenu-ppt-v3">
            <a href="{{ route('abm.v3.ppt.dashboard') }}" class="nav-sub-item {{ request()->is('abm-v3/ppt/dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard PPT
            </a>
            <a href="{{ route('abm.v3.ppt.import') }}" class="nav-sub-item {{ request()->is('abm-v3/ppt/import') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Muat Naik PPT
            </a>
            <a href="{{ route('abm.v3.ppt.summary') }}" class="nav-sub-item {{ request()->is('abm-v3/ppt/summary') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 14l3-3 3 2 4-6"/></svg>
                Ringkasan PPT
            </a>
            <a href="{{ route('abm.v3.ppt.repository') }}" class="nav-sub-item {{ request()->is('abm-v3/ppt/repository') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                Repositori PPT
            </a>
            <a href="{{ route('abm.v3.ppt.status') }}" class="nav-sub-item {{ request()->is('abm-v3/ppt/status-proses') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 12h6m-6 4h6"/></svg>
                Status Proses PPT
            </a>
            <a href="{{ route('abm.v3.ppt.audit') }}" class="nav-sub-item {{ request()->is('abm-v3/ppt/audit-trail') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Audit Trail PPT
            </a>
        </div>
        @endif

        @if($menuAccess['admin_section'])
        {{-- Pentadbiran expandable section --}}
        <button class="nav-section-header" onclick="toggleSubMenu('submenu-admin')">
            <div class="nav-hdr-left">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Pentadbiran
            </div>
            <svg class="nav-chevron {{ request()->is('pengguna*') || request()->is('audit-trail*') ? 'open' : '' }}" id="arrow-submenu-admin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="nav-submenu {{ request()->is('pengguna*') || request()->is('audit-trail*') ? 'open' : '' }}" id="submenu-admin">
            <a href="{{ url('/pengguna') }}" class="nav-sub-item {{ request()->is('pengguna') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Peranan Pengguna
            </a>
            <a href="{{ url('/pengguna/permohonan') }}" class="nav-sub-item {{ request()->is('pengguna/permohonan*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Senarai Permohonan
            </a>
            @if($menuAccess['audit_trail'])
            <a href="{{ url('/audit-trail') }}" class="nav-sub-item {{ request()->is('audit-trail*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Jejak Audit
            </a>
            @endif
        </div>
        @endif

        @if($menuAccess['change_password'])
        <a href="{{ route('change-password') }}" class="nav-item {{ request()->is('tukar-kata-laluan/baharu') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            Tukar Kata Laluan
        </a>
        @endif
    </nav>

</aside>

{{-- ════════ Main area ════════ --}}
<div class="main-wrap" id="mainWrap">

    {{-- Topbar (AdminHeader.tsx) --}}
    <header class="topbar">
        <div class="topbar-left">
            <button class="hamburger" onclick="toggleSidebar()">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="topbar-brand">
                <img src="{{ asset('storage/images/JATA_KPKT_BM_BLACK.png') }}" alt="KPKT" class="topbar-logo">
                <span class="topbar-title">eKONTRAK</span>
            </div>
        </div>
        <div class="topbar-right">
            <form method="POST" action="/logout" id="logoutForm">
                @csrf
                <button type="submit" class="btn-logout">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Log Keluar
                </button>
            </form>
        </div>
    </header>

    <main class="main-content">
        @yield('content')
    </main>
</div>

@include('components.layouts.global-footer', ['variant' => 'internal'])

@include('components.sweetalert-service')
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('collapsed');
        document.getElementById('mainWrap').classList.toggle('expanded');
    }
    function toggleSubMenu(id) {
        const menu  = document.getElementById(id);
        const arrow = document.getElementById('arrow-' + id);
        menu.classList.toggle('open');
        if (arrow) arrow.classList.toggle('open');
    }
    document.querySelectorAll('.nav-submenu').forEach(menu => {
        if (menu.querySelector('.active')) {
            menu.classList.add('open');
            const arrow = document.getElementById('arrow-' + menu.id);
            if (arrow) arrow.classList.add('open');
        }
    });
    function togglePw(inputId, btn) {
        const input = document.getElementById(inputId);
        input.type  = input.type === 'password' ? 'text' : 'password';
        btn.style.color = input.type === 'text' ? '#2563eb' : '#9ca3af';
    }

    const logoutForm = document.getElementById('logoutForm');
    if (logoutForm) {
        logoutForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const ok = await window.ConfirmationService.confirmLogout();
            if (ok) this.submit();
        });
    }
    function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        if (modal.classList.contains('modal-overlay')) {
            modal.classList.add('open');
            modal.style.display = '';
            return;
        }
        modal.style.display = 'block';
    }
    function closeModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        if (modal.classList.contains('modal-overlay')) {
            modal.classList.remove('open');
            modal.style.display = 'none';
            return;
        }
        modal.style.display = 'none';
    }
    function toggleAlert(el) {
        const body = el.nextElementSibling;
        if (!body) return;
        const isOpen = body.style.display === 'block';
        body.style.display = isOpen ? 'none' : 'block';
    }

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        const requiredFields = Array.from(form.querySelectorAll('[required]'));
        if (requiredFields.length === 0) return;

        const firstMissing = requiredFields.find(function (el) {
            if (el.disabled) return false;
            if (el.type === 'checkbox' || el.type === 'radio') return !el.checked;
            return !String(el.value ?? '').trim();
        });

        if (!firstMissing) return;

        event.preventDefault();
        const label = (firstMissing.getAttribute('aria-label') || firstMissing.getAttribute('placeholder') || firstMissing.name || 'Medan wajib').replace(/_/g, ' ');
        showToast(label + ' diperlukan.', 'warning', 'Maklumat tidak lengkap');
    }, true);
</script>
@stack('scripts')
</body>
</html>
