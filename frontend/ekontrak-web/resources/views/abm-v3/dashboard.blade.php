@extends('components.layouts.app')

@section('title', 'Dashboard ABM')

@php
    $selectedUploads = collect($recentUploads ?? []);
    $selectedActivities = collect($recentActivities ?? []);
    $yearComparison = collect($yearComparison ?? []);
    $sectionBreakdown = collect($sectionBreakdown ?? []);
    $sectorBreakdown = collect($sectorBreakdown ?? []);
    $programBreakdown = collect($programBreakdown ?? []);
    $activityBreakdown = collect($activityBreakdown ?? []);
    $monthlyCalendar = collect($monthlyCalendar ?? []);
    $headerSummary = collect($headerSummary ?? []);
    $topSections = collect($topSections ?? []);

    $yearOptions = collect($yearOptions ?? [date('Y')])
        ->map(fn ($year) => (int) $year)
        ->unique()
        ->sortDesc()
        ->values();

    $selectedYear = (int) ($selectedYear ?? date('Y'));
    $previousYear = (int) ($previousYear ?? ($selectedYear - 1));

    $normalizeValue = static function ($value): float {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $cleaned = preg_replace('/[^0-9.\-]/', '', (string) $value);

        return is_numeric($cleaned) ? (float) $cleaned : 0.0;
    };

    $comparisonRows = $yearComparison
        ->map(function ($row) use ($normalizeValue) {
            return [
                'year' => (int) data_get($row, 'year', 0),
                'amount' => $normalizeValue(data_get($row, 'amount', 0)),
                'rows' => (int) data_get($row, 'rows', 0),
                'count' => (int) data_get($row, 'count', 0),
            ];
        })
        ->filter(fn ($row) => $row['year'] > 0)
        ->values();

    $comparisonLookup = $comparisonRows->keyBy('year');
    $selectedYearRow = $comparisonLookup->get($selectedYear, ['amount' => (float) data_get($stats, 'amount', 0), 'rows' => (int) data_get($stats, 'rows', 0), 'count' => (int) data_get($stats, 'uploads', 0)]);
    $previousYearRow = $comparisonLookup->get($previousYear, ['amount' => 0, 'rows' => 0, 'count' => 0]);

    $selectedAmount = (float) data_get($selectedYearRow, 'amount', 0);
    $previousAmount = (float) data_get($previousYearRow, 'amount', 0);
    $amountDelta = $selectedAmount - $previousAmount;
    $amountGrowth = $previousAmount > 0 ? ($amountDelta / $previousAmount) * 100 : 0;
    $forecastAmount = $selectedAmount > 0 ? ($selectedAmount * (1 + max(min($amountGrowth, 60), -60) / 100)) : 0;

    $uploadsCount = (int) data_get($stats, 'uploads', 0);
    $rowsCount = (int) data_get($stats, 'rows', 0);
    $sheetsCount = (int) data_get($stats, 'sheets', 0);
    $sectionsCount = (int) data_get($stats, 'sections', 0);
    $programsCount = (int) data_get($stats, 'programs', 0);
    $activitiesCount = (int) data_get($stats, 'activities', 0);
    $headerFieldsCount = (int) data_get($stats, 'header_fields', 0);
    $amountValue = (float) data_get($stats, 'amount', $selectedAmount);

    $fillRatio = static function (float $value, float $max): int {
        if ($max <= 0) {
            return 0;
        }

        return (int) max(12, min(100, round(($value / $max) * 100)));
    };

    $monthNames = [1 => 'Jan', 2 => 'Feb', 3 => 'Mac', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ogo', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Dis'];

    $monthRows = collect($monthlyCalendar)
        ->map(function ($row, $key) use ($monthNames, $normalizeValue) {
            $monthKey = (int) data_get($row, 'month', data_get($row, 'month_no', is_numeric($key) ? ((int) $key + 1) : 0));

            return [
                'month' => $monthKey,
                'label' => data_get($row, 'label', $monthNames[$monthKey] ?? data_get($row, 'month_label', 'M' . $monthKey)),
                'amount' => $normalizeValue(data_get($row, 'amount', data_get($row, 'total', 0))),
                'count' => (int) data_get($row, 'count', 0),
            ];
        })
        ->filter(fn ($row) => $row['month'] > 0)
        ->sortBy('month')
        ->values();

    $monthLookup = $monthRows->keyBy('month');
    $calendarRows = collect(range(1, 12))->map(function ($month) use ($monthLookup, $monthNames) {
        $row = $monthLookup->get($month, ['amount' => 0, 'count' => 0]);

        return [
            'month' => $month,
            'label' => $monthNames[$month],
            'amount' => (float) data_get($row, 'amount', 0),
            'count' => (int) data_get($row, 'count', 0),
        ];
    });

    $monthPeak = max(1, (float) $calendarRows->max('amount'));
    $monthAmounts = $calendarRows->pluck('amount')->map(fn ($value) => round((float) $value, 2))->values();

    $sectionRows = $sectionBreakdown
        ->map(function ($row) use ($normalizeValue) {
            return [
                'label' => data_get($row, 'label', data_get($row, 'name', '-')),
                'code' => data_get($row, 'code', data_get($row, 'sheet', '-')),
                'selected_year' => $normalizeValue(data_get($row, 'selected_year', 0)),
                'previous_year' => $normalizeValue(data_get($row, 'previous_year', 0)),
                'count' => (int) data_get($row, 'count', 0),
                'rows' => (int) data_get($row, 'rows', 0),
            ];
        })
        ->sortByDesc('selected_year')
        ->values();

    $pivotRows = [
        'section' => $sectionRows,
        'sector' => $sectorBreakdown->map(function ($row) use ($normalizeValue) {
            return [
                'label' => data_get($row, 'label', '-'),
                'count' => (int) data_get($row, 'count', 0),
                'rows' => (int) data_get($row, 'rows', 0),
                'amount' => $normalizeValue(data_get($row, 'amount', 0)),
            ];
        })->sortByDesc('amount')->values(),
        'program' => $programBreakdown->map(function ($row) use ($normalizeValue) {
            return [
                'label' => data_get($row, 'label', '-'),
                'count' => (int) data_get($row, 'count', 0),
                'rows' => (int) data_get($row, 'rows', 0),
                'amount' => $normalizeValue(data_get($row, 'amount', 0)),
            ];
        })->sortByDesc('amount')->values(),
        'activity' => $activityBreakdown->map(function ($row) use ($normalizeValue) {
            return [
                'label' => data_get($row, 'label', '-'),
                'count' => (int) data_get($row, 'count', 0),
                'rows' => (int) data_get($row, 'rows', 0),
                'amount' => $normalizeValue(data_get($row, 'amount', 0)),
            ];
        })->sortByDesc('amount')->values(),
    ];

    $pivotTotals = [
        'section' => (float) $pivotRows['section']->sum('selected_year'),
        'sector' => (float) $pivotRows['sector']->sum('amount'),
        'program' => (float) $pivotRows['program']->sum('amount'),
        'activity' => (float) $pivotRows['activity']->sum('amount'),
    ];

    $topSectionRows = $sectionRows->take(6)->values();
    $topSectionTotal = max(1, (float) $topSectionRows->sum('selected_year'));
    $sectionLabels = $sectionRows->take(6)->map(fn ($row) => $row['label'])->values();
    $sectionValues = $sectionRows->take(6)->map(fn ($row) => round($row['selected_year'], 2))->values();

    $donutColors = ['#0f766e', '#0284c7', '#1d4ed8', '#7c3aed', '#e11d48', '#f59e0b'];

    $headerCards = $headerSummary->map(function ($value, $key) {
        $valueText = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;

        return [
            'label' => is_string($key) ? str_replace('_', ' ', strtoupper($key)) : (string) $key,
            'value' => $value,
            'search' => strtolower((string) $key . ' ' . $valueText),
        ];
    })->values();
@endphp

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .abm-wip-overlay {
            position: fixed;
            inset: 0;
            z-index: 5000;
            display: grid;
            place-items: center;
            background: rgba(15, 23, 42, 0.34);
            backdrop-filter: blur(4px);
        }

        .abm-wip-modal {
            position: relative;
            width: min(92vw, 440px);
            padding: 2.5rem 1.8rem 2.1rem;
            border-radius: 24px;
            border: 1px solid #dbe7f3;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 28px 70px rgba(15, 23, 42, 0.25);
            text-align: center;
            color: #0f172a;
        }

        .abm-wip-close {
            position: absolute;
            top: .8rem;
            right: .8rem;
            width: 2.25rem;
            height: 2.25rem;
            border: 0;
            border-radius: 999px;
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 1.5rem;
            line-height: 1;
            font-weight: 700;
            cursor: pointer;
        }

        .abm-wip-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .42rem .75rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .abm-wip-modal h2 {
            margin: 0;
            font-size: 1.6rem;
            line-height: 1.15;
            font-weight: 900;
        }

        .abm-wip-modal p {
            margin: .65rem 0 0;
            color: #64748b;
            font-weight: 600;
        }

        .abm-wip-hidden {
            display: none !important;
        }

        .abm-dashboard-shell {
            position: relative;
            color: #0f172a;
        }

        .abm-dashboard-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.14), transparent 32%),
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 24%),
                linear-gradient(180deg, #f8fbff 0%, #eef4f9 100%);
        }

        .abm-dashboard-shell > * {
            position: relative;
            z-index: 1;
        }

        .abm-hero,
        .panel-card,
        .kpi-card,
        .filter-shell,
        .mini-card,
        .forecast-card,
        .calendar-cell,
        .upload-card {
            border: 1px solid #d8e3ee;
            border-radius: 22px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .abm-hero {
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.97), rgba(30, 64, 175, 0.96));
            color: white;
            overflow: hidden;
            position: relative;
        }

        .abm-hero::after {
            content: '';
            position: absolute;
            right: -6rem;
            top: -5rem;
            width: 18rem;
            height: 18rem;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.24), rgba(255, 255, 255, 0.02) 68%);
            pointer-events: none;
        }

        .abm-hero .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.24em;
            font-size: 0.72rem;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.78);
        }

        .abm-hero h1 {
            font-size: clamp(1.75rem, 3vw, 2.65rem);
            font-weight: 900;
            letter-spacing: -0.04em;
            line-height: 1.02;
        }

        .abm-hero p {
            color: rgba(255, 255, 255, 0.84);
            max-width: 68rem;
        }

        .hero-metric {
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.08);
            padding: 1rem;
            backdrop-filter: blur(8px);
            min-height: 100%;
        }

        .hero-metric .value {
            font-size: 1.7rem;
            font-weight: 900;
            letter-spacing: -0.04em;
            line-height: 1;
        }

        .hero-metric .label {
            margin-top: 0.4rem;
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: rgba(255, 255, 255, 0.72);
            font-weight: 800;
        }

        .hero-metric .hint {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.88);
            margin-top: 0.45rem;
        }

        .filter-bar {
            position: sticky;
            top: 0.5rem;
            z-index: 20;
            backdrop-filter: blur(14px);
        }

        .filter-shell {
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid #d8e3ee;
        }

        .filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.5rem 0.75rem;
            border-radius: 14px;
            border: 1px solid #d8e3ee;
            background: white;
            color: #1f2937;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .filter-pill i {
            color: #0f766e;
        }

        .filter-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 800;
            color: #5b6b7f;
            margin-bottom: 0.35rem;
        }

        .filter-control {
            border-radius: 14px !important;
            border-color: #d8e3ee !important;
            box-shadow: none !important;
            font-weight: 600;
        }

        .kpi-card {
            background: rgba(255, 255, 255, 0.92);
            padding: 1rem 1rem 0.95rem;
            height: 100%;
        }

        .kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
        }

        .kpi-icon i {
            font-size: 1.1rem;
        }

        .kpi-value {
            font-size: 1.7rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.04em;
            margin-top: 0.8rem;
        }

        .kpi-label {
            margin-top: 0.4rem;
            font-size: 0.78rem;
            color: #5b6b7f;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .kpi-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.85rem;
            font-size: 0.76rem;
            color: #5b6b7f;
        }

        .kpi-badge {
            border-radius: 999px;
            padding: 0.24rem 0.6rem;
            font-weight: 800;
            font-size: 0.72rem;
        }

        .kpi-badge.up { background: #dcfce7; color: #166534; }
        .kpi-badge.down { background: #fee2e2; color: #b91c1c; }
        .kpi-badge.flat { background: #e2e8f0; color: #475569; }

        .panel-card {
            background: rgba(255, 255, 255, 0.92);
        }

        .panel-head {
            padding: 1rem 1.1rem 0.7rem;
            border-bottom: 1px solid #e8eef5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .panel-head h2 {
            font-size: 1rem;
            font-weight: 900;
            letter-spacing: -0.02em;
            margin: 0;
        }

        .panel-head .sub {
            font-size: 0.78rem;
            color: #5b6b7f;
        }

        .panel-body {
            padding: 1rem 1.1rem 1.15rem;
        }

        .chart-box {
            min-height: 340px;
        }

        .chart-box.tall {
            min-height: 380px;
        }

        .chart-box.short {
            min-height: 280px;
        }

        .chart-legend {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            font-size: 0.78rem;
            color: #5b6b7f;
        }

        .legend-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.38rem 0.65rem;
            border-radius: 999px;
            background: #f8fbff;
            border: 1px solid #d8e3ee;
            font-weight: 700;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .mini-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.8rem;
        }

        .mini-card {
            border-radius: 18px;
            border: 1px solid #d8e3ee;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbfd 100%);
            padding: 0.9rem;
        }

        .mini-card .title {
            font-size: 0.74rem;
            color: #5b6b7f;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 800;
        }

        .mini-card .value {
            font-size: 1.25rem;
            font-weight: 900;
            margin-top: 0.35rem;
            letter-spacing: -0.03em;
        }

        .mini-card .sub {
            font-size: 0.78rem;
            color: #5b6b7f;
            margin-top: 0.3rem;
        }

        .forecast-card {
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.98), rgba(59, 130, 246, 0.97));
            color: white;
            border-radius: 22px;
            padding: 1rem;
            box-shadow: 0 20px 40px rgba(15, 118, 110, 0.18);
            height: 100%;
        }

        .forecast-card .label {
            text-transform: uppercase;
            letter-spacing: 0.14em;
            font-size: 0.7rem;
            opacity: 0.76;
            font-weight: 800;
        }

        .forecast-card .big {
            font-size: 1.95rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.04em;
            margin-top: 0.35rem;
        }

        .forecast-card .detail {
            font-size: 0.85rem;
            opacity: 0.92;
            margin-top: 0.55rem;
        }

        .forecast-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            padding: 0.3rem 0.6rem;
            background: rgba(255, 255, 255, 0.14);
            font-size: 0.74rem;
            font-weight: 800;
            margin-top: 0.8rem;
        }

        .pivot-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.9rem;
        }

        .pivot-tab {
            border-radius: 999px;
            border: 1px solid #d8e3ee;
            background: white;
            color: #334155;
            font-weight: 800;
            font-size: 0.8rem;
            padding: 0.55rem 0.85rem;
            cursor: pointer;
        }

        .pivot-tab.active {
            background: #0f766e;
            color: white;
            border-color: #0f766e;
        }

        .pivot-panel {
            display: none;
        }

        .pivot-panel.active {
            display: block;
        }

        .pivot-table {
            width: 100%;
            min-width: 820px;
        }

        .pivot-table thead th {
            position: sticky;
            top: 0;
            background: #f8fbff;
            z-index: 1;
            border-bottom: 1px solid #dfe7f0;
            padding: 0.82rem 0.9rem;
            color: #64748b;
            font-size: 0.72rem;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            font-weight: 800;
            white-space: nowrap;
        }

        .pivot-table tbody td {
            padding: 0.85rem 0.9rem;
            border-bottom: 1px solid #edf2f7;
            font-size: 0.84rem;
            white-space: nowrap;
        }

        .pivot-table tbody tr:hover td {
            background: #f8fbff;
        }

        .table-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .upload-card {
            display: flex;
            justify-content: space-between;
            gap: 0.85rem;
            background: white;
            padding: 0.9rem 1rem;
            text-decoration: none;
            color: inherit;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .upload-card:hover {
            transform: translateY(-1px);
            border-color: #93c5fd;
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.08);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.24rem 0.55rem;
            font-size: 0.7rem;
            font-weight: 800;
        }

        .timeline {
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }

        .timeline-item {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.75rem;
            align-items: flex-start;
        }

        .timeline-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #0f766e;
            margin-top: 0.35rem;
            box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.12);
        }

        .timeline-item strong {
            display: block;
            font-size: 0.86rem;
        }

        .timeline-item p,
        .timeline-item span {
            margin: 0;
            font-size: 0.78rem;
            color: #5b6b7f;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.7rem;
        }

        .calendar-cell {
            border: 1px solid #d8e3ee;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 0.8rem 0.9rem;
            min-height: 96px;
        }

        .calendar-cell strong {
            font-size: 0.84rem;
        }

        .calendar-cell .amount {
            margin-top: 0.3rem;
            font-size: 1rem;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .calendar-cell .count {
            margin-top: 0.15rem;
            font-size: 0.74rem;
            color: #5b6b7f;
        }

        .calendar-cell .bar {
            height: 9px;
            border-radius: 999px;
            background: #dbe7f3;
            margin-top: 0.7rem;
            overflow: hidden;
        }

        .calendar-cell .fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #0f766e, #2563eb);
        }

        .compact-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .compact-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border-bottom: 1px dashed #d8e3ee;
            padding-bottom: 0.65rem;
        }

        .compact-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .compact-row .name {
            font-size: 0.84rem;
            font-weight: 700;
        }

        .compact-row .meta {
            font-size: 0.76rem;
            color: #5b6b7f;
        }

        .compact-row .score {
            font-size: 0.9rem;
            font-weight: 900;
        }

        .search-highlight {
            transition: background-color 0.15s ease, opacity 0.15s ease;
        }

        .is-hidden {
            display: none !important;
        }

        @media (max-width: 1199.98px) {
            .calendar-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 991.98px) {
            .mini-grid {
                grid-template-columns: 1fr;
            }

            .chart-box,
            .chart-box.tall {
                min-height: 300px;
            }
        }

        @media (max-width: 767.98px) {
            .calendar-grid {
                grid-template-columns: 1fr;
            }

            .abm-hero h1 {
                font-size: 1.7rem;
            }
        }

        @media print {
            .sidebar,
            .topbar,
            .filter-bar,
            .btn,
            .pivot-tabs {
                display: none !important;
            }

            .main-wrap,
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }

            .panel-card,
            .kpi-card,
            .abm-hero {
                box-shadow: none !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="abm-wip-overlay" id="abmWipOverlay" role="presentation">
    <div class="abm-wip-modal" role="dialog" aria-modal="true" aria-labelledby="abmWipTitle">
        <button type="button" class="abm-wip-close" id="abmWipClose" aria-label="Tutup popup">&times;</button>
        <div class="abm-wip-badge">WORK IN PROGRESS</div>
        <h2 id="abmWipTitle">Dashboard ABM</h2>
        <p>Halaman ini masih dalam pembangunan.</p>
    </div>
</div>

    <div class="abm-dashboard-shell">
        <div class="container-fluid px-0">
            <div class="filter-bar mb-3">
                <div class="filter-shell p-3 p-xl-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xxl-4">
                            <div class="d-flex flex-column h-100 justify-content-between gap-2">
                                <div>
                                    <div class="filter-pill mb-2"><i class="bi bi-graph-up-arrow"></i> Executive ABM Command Center</div>
                                    <div class="filter-label">Paparan semasa</div>
                                    <div class="fw-bold">Tahun {{ $selectedYear }} berbanding {{ $previousYear }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 text-nowrap">
                                    <span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill px-3 py-2">{{ number_format($uploadsCount) }} fail</span>
                                    <span class="badge text-bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill px-3 py-2">{{ number_format($rowsCount) }} baris</span>
                                    <span class="badge text-bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2">{{ number_format($sectionsCount) }} seksyen</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-xxl-2">
                            <div class="filter-label">Tahun</div>
                            <form method="GET" action="{{ route('abm.v3.dashboard') }}">
                                <select class="form-select filter-control" name="year" onchange="this.form.submit()">
                                    @foreach($yearOptions as $year)
                                        <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="col-6 col-md-3 col-xxl-2">
                            <div class="filter-label">Carian langsung</div>
                            <input type="search" class="form-control filter-control" id="dashboardSearch" placeholder="Cari seksyen, sektor, program...">
                        </div>
                        <div class="col-12 col-md-6 col-xxl-2">
                            <div class="filter-label">Fokus pantas</div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-sm btn-outline-success rounded-pill px-3" type="button" data-focus="kpi-row">KPI</button>
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" type="button" data-focus="charts">Carta</button>
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" type="button" data-focus="pivot-grid">Pivot</button>
                            </div>
                        </div>
                        <div class="col-12 col-xxl-2">
                            <div class="d-flex gap-2 flex-wrap justify-content-xxl-end">
                                <a href="#pivot-grid" class="btn btn-dark rounded-pill px-3"><i class="bi bi-grid-3x3-gap"></i> Pivot</a>
                                <button type="button" class="btn btn-outline-dark rounded-pill px-3" id="exportCsvBtn"><i class="bi bi-download"></i> CSV</button>
                                <button type="button" class="btn btn-outline-dark rounded-pill px-3" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($dbUnavailableMessage))
                <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ $dbUnavailableMessage }}
                </div>
            @endif

            <div class="abm-hero p-4 p-xl-5 mb-4">
                <div class="row g-4 align-items-stretch">
                    <div class="col-12 col-xl-8">
                        <div class="eyebrow mb-2">ABM Executive Dashboard</div>
                        <h1>ABM monitoring</h1>
                        <p class="mt-3 mb-0">Pantau prestasi, trend, pecahan seksyen, sektor, program dan aktiviti dalam satu skrin operasi yang padat. Susun atur ini mengekalkan data controller semasa dan menambah visualisasi eksekutif, penapisan cepat, serta ringkasan audit.</p>
                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <span class="badge rounded-pill text-bg-light text-dark px-3 py-2">Selected Year: {{ $selectedYear }}</span>
                            <span class="badge rounded-pill text-bg-light text-dark px-3 py-2">Previous Year: {{ $previousYear }}</span>
                            <span class="badge rounded-pill text-bg-light text-dark px-3 py-2">Header fields: {{ number_format($headerFieldsCount) }}</span>
                            <span class="badge rounded-pill text-bg-light text-dark px-3 py-2">Monthly coverage: {{ $calendarRows->filter(fn ($row) => $row['amount'] > 0)->count() }}/12</span>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="hero-metric h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="label">Current amount</div>
                                <div class="value mt-2">{{ number_format($amountValue, 2) }}</div>
                                <div class="hint mt-2">Delta {{ $amountDelta >= 0 ? '+' : '' }}{{ number_format($amountDelta, 2) }} berbanding {{ $previousYear }}</div>
                            </div>
                            <div class="row g-2 mt-3">
                                <div class="col-4"><div class="mini-card bg-white bg-opacity-10 border border-white border-opacity-25 text-white"><div class="title text-white-50">Uploads</div><div class="value text-white">{{ number_format($uploadsCount) }}</div></div></div>
                                <div class="col-4"><div class="mini-card bg-white bg-opacity-10 border border-white border-opacity-25 text-white"><div class="title text-white-50">Rows</div><div class="value text-white">{{ number_format($rowsCount) }}</div></div></div>
                                <div class="col-4"><div class="mini-card bg-white bg-opacity-10 border border-white border-opacity-25 text-white"><div class="title text-white-50">Sheets</div><div class="value text-white">{{ number_format($sheetsCount) }}</div></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4" id="kpi-row">
                @php
                    $kpiCards = [
                        ['label' => 'Muat Naik', 'value' => $uploadsCount, 'hint' => 'Fail ABM diproses', 'badge' => $amountGrowth >= 0 ? 'up' : 'down', 'delta' => $amountGrowth >= 0 ? '+' . number_format($amountGrowth, 1) . '%' : number_format($amountGrowth, 1) . '%', 'icon' => 'bi-cloud-arrow-up-fill', 'color' => 'linear-gradient(135deg,#0f766e,#14b8a6)'],
                        ['label' => 'Baris', 'value' => $rowsCount, 'hint' => 'Rekod budget row', 'badge' => 'flat', 'delta' => number_format($sheetsCount) . ' sheets', 'icon' => 'bi-list-ul', 'color' => 'linear-gradient(135deg,#2563eb,#60a5fa)'],
                        ['label' => 'Jumlah', 'value' => number_format($amountValue, 2), 'hint' => 'Agregat selected year', 'badge' => $amountDelta >= 0 ? 'up' : 'down', 'delta' => ($amountDelta >= 0 ? '+' : '') . number_format($amountDelta, 2), 'icon' => 'bi-currency-dollar', 'color' => 'linear-gradient(135deg,#7c3aed,#a855f7)'],
                        ['label' => 'Seksyen', 'value' => $sectionsCount, 'hint' => 'Top-level breakdown', 'badge' => 'flat', 'delta' => number_format($selectedAmount / max($sectionsCount, 1), 1) . ' avg', 'icon' => 'bi-diagram-3-fill', 'color' => 'linear-gradient(135deg,#ea580c,#fb923c)'],
                        ['label' => 'Program', 'value' => $programsCount, 'hint' => 'Program items', 'badge' => 'flat', 'delta' => number_format($headerFieldsCount) . ' header fields', 'icon' => 'bi-kanban-fill', 'color' => 'linear-gradient(135deg,#0891b2,#22d3ee)'],
                        ['label' => 'Aktiviti', 'value' => $activitiesCount, 'hint' => 'Activity rows', 'badge' => 'flat', 'delta' => 'live trail', 'icon' => 'bi-bounding-box-circles', 'color' => 'linear-gradient(135deg,#be185d,#f472b6)'],
                        ['label' => 'Forecast', 'value' => number_format($forecastAmount, 2), 'hint' => 'Simple projection', 'badge' => $forecastAmount >= $amountValue ? 'up' : 'down', 'delta' => 'next year', 'icon' => 'bi-graph-up-arrow', 'color' => 'linear-gradient(135deg,#1d4ed8,#38bdf8)'],
                        ['label' => 'Headers', 'value' => $headerFieldsCount, 'hint' => 'Metadata fields', 'badge' => 'flat', 'delta' => number_format($headerCards->count()) . ' cards', 'icon' => 'bi-journal-text', 'color' => 'linear-gradient(135deg,#475569,#94a3b8)'],
                    ];
                @endphp
                @foreach($kpiCards as $card)
                    <div class="col-6 col-xl-3">
                        <div class="kpi-card search-highlight" data-search-text="{{ strtolower($card['label'] . ' ' . $card['value'] . ' ' . $card['hint']) }}">
                            <div class="kpi-top">
                                <div>
                                    <div class="kpi-label">{{ $card['label'] }}</div>
                                </div>
                                <div class="kpi-icon" style="background: {{ $card['color'] }}">
                                    <i class="bi {{ $card['icon'] }}"></i>
                                </div>
                            </div>
                            <div class="kpi-value">{{ is_numeric($card['value']) ? number_format((float) $card['value']) : $card['value'] }}</div>
                            <div class="kpi-meta">
                                <span>{{ $card['hint'] }}</span>
                                <span class="kpi-badge {{ $card['badge'] }}">{{ $card['delta'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-3 mb-4" id="charts">
                <div class="col-12 col-xxl-7">
                    <div class="panel-card h-100 search-highlight" data-search-text="year comparison trend comparison">
                        <div class="panel-head">
                            <div>
                                <h2>Year comparison cockpit</h2>
                                <div class="sub">Selected year against historical ABM totals</div>
                            </div>
                            <div class="chart-legend">
                                <span class="legend-chip"><span class="legend-dot" style="background:#0f766e"></span>Amaun</span>
                                <span class="legend-chip"><span class="legend-dot" style="background:#94a3b8"></span>Baris</span>
                            </div>
                        </div>
                        <div class="panel-body chart-box tall" id="yearComparisonChart"></div>
                    </div>
                </div>
                <div class="col-12 col-xxl-5">
                    <div class="panel-card h-100 search-highlight" data-search-text="forecast donut section share">
                        <div class="panel-head">
                            <div>
                                <h2>Section mix and forecast</h2>
                                <div class="sub">Top section contribution with quick projection</div>
                            </div>
                            <span class="badge rounded-pill text-bg-light text-dark border px-3 py-2">Forecast next year</span>
                        </div>
                        <div class="panel-body">
                            <div class="row g-3 align-items-stretch">
                                <div class="col-12 col-lg-7">
                                    <div class="chart-box short" id="sectionDonutChart"></div>
                                </div>
                                <div class="col-12 col-lg-5">
                                    <div class="forecast-card">
                                        <div class="label">Projected amount</div>
                                        <div class="big">{{ number_format($forecastAmount, 2) }}</div>
                                        <div class="detail">Based on {{ $amountGrowth >= 0 ? '+' : '' }}{{ number_format($amountGrowth, 1) }}% growth from {{ $previousYear }} to {{ $selectedYear }}.</div>
                                        <div class="forecast-chip"><i class="bi bi-lightning-charge-fill"></i> {{ $amountGrowth >= 0 ? 'Momentum positif' : 'Momentum lemah' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 compact-list">
                                @foreach($topSectionRows as $row)
                                    <div class="compact-row search-highlight" data-search-text="{{ strtolower($row['label'] . ' ' . $row['code']) }}">
                                        <div>
                                            <div class="name">{{ $row['label'] }}</div>
                                            <div class="meta">{{ $row['code'] }} · {{ number_format($row['selected_year'], 2) }}</div>
                                        </div>
                                        <div class="score">{{ number_format($row['selected_year'], 2) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-xxl-7">
                    <div class="panel-card h-100 search-highlight" data-search-text="monthly trend line calendar">
                        <div class="panel-head">
                            <div>
                                <h2>Monthly trend line</h2>
                                <div class="sub">Seasonality and monthly execution rhythm</div>
                            </div>
                            <span class="badge rounded-pill text-bg-primary-subtle text-primary-emphasis border border-primary-subtle px-3 py-2">{{ $calendarRows->filter(fn ($row) => $row['amount'] > 0)->count() }} active months</span>
                        </div>
                        <div class="panel-body chart-box" id="monthlyTrendChart"></div>
                    </div>
                </div>
                <div class="col-12 col-xxl-5">
                    <div class="panel-card h-100 search-highlight" data-search-text="calendar heatmap monthly summary">
                        <div class="panel-head">
                            <div>
                                <h2>Monthly calendar</h2>
                                <div class="sub">12-month fiscal rhythm, scaled to current year</div>
                            </div>
                            <span class="badge rounded-pill text-bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-2">Coverage</span>
                        </div>
                        <div class="panel-body">
                            <div class="calendar-grid">
                                @foreach($calendarRows as $row)
                                    @php $barWidth = $fillRatio((float) $row['amount'], $monthPeak); @endphp
                                    <div class="calendar-cell search-highlight" data-search-text="{{ strtolower($row['label'] . ' ' . $row['amount'] . ' ' . $row['count']) }}">
                                        <strong>{{ $row['label'] }}</strong>
                                        <div class="amount">{{ number_format($row['amount'], 2) }}</div>
                                        <div class="count">{{ number_format($row['count']) }} item</div>
                                        <div class="bar"><div class="fill" style="width: {{ $barWidth }}%"></div></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4" id="pivot-grid">
                <div class="col-12 col-xxl-8">
                    <div class="panel-card h-100 search-highlight" data-search-text="pivot grid section sector program activity">
                        <div class="panel-head">
                            <div>
                                <h2>Pivot grid</h2>
                                <div class="sub">Switch between section, sector, program and activity views</div>
                            </div>
                            <div class="chart-legend">
                                <span class="legend-chip"><span class="legend-dot" style="background:#0f766e"></span>Current period</span>
                                <span class="legend-chip"><span class="legend-dot" style="background:#2563eb"></span>Historical compare</span>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="pivot-tabs" role="tablist" aria-label="ABM pivot tabs">
                                <button class="pivot-tab active" type="button" data-pivot-target="section">Seksyen</button>
                                <button class="pivot-tab" type="button" data-pivot-target="sector">Sektor</button>
                                <button class="pivot-tab" type="button" data-pivot-target="program">Program</button>
                                <button class="pivot-tab" type="button" data-pivot-target="activity">Aktiviti</button>
                            </div>

                            <div class="pivot-panel active" data-pivot-panel="section">
                                <x-table
                                    :headers="['Label', 'Kod', 'Fail', 'Baris', 'Tahun semasa', 'Tahun lepas', 'Perubahan']"
                                    wrap-class="table-scroll"
                                    table-class="table align-middle pivot-table mb-0"
                                >
                                    @forelse($pivotRows['section'] as $row)
                                        @php $delta = $row['selected_year'] - $row['previous_year']; @endphp
                                        <tr class="search-highlight" data-search-text="{{ strtolower($row['label'] . ' ' . $row['code']) }}">
                                            <td class="fw-bold">{{ $row['label'] }}</td>
                                            <td>{{ $row['code'] }}</td>
                                            <td>{{ number_format($row['count']) }}</td>
                                            <td>{{ number_format($row['rows']) }}</td>
                                            <td>{{ number_format($row['selected_year'], 2) }}</td>
                                            <td>{{ number_format($row['previous_year'], 2) }}</td>
                                            <td class="fw-bold {{ $delta >= 0 ? 'text-success' : 'text-danger' }}">{{ $delta >= 0 ? '+' : '' }}{{ number_format($delta, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center text-muted py-4">Tiada data seksyen untuk tahun ini.</td></tr>
                                    @endforelse
                                </x-table>
                            </div>

                            <div class="pivot-panel" data-pivot-panel="sector">
                                <x-table
                                    :headers="['Sektor', 'Fail', 'Baris', 'Jumlah']"
                                    wrap-class="table-scroll"
                                    table-class="table align-middle pivot-table mb-0"
                                >
                                    @forelse($pivotRows['sector'] as $row)
                                        <tr class="search-highlight" data-search-text="{{ strtolower($row['label']) }}">
                                            <td class="fw-bold">{{ $row['label'] }}</td>
                                            <td>{{ number_format($row['count']) }}</td>
                                            <td>{{ number_format($row['rows']) }}</td>
                                            <td>{{ number_format($row['amount'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">Tiada data sektor.</td></tr>
                                    @endforelse
                                </x-table>
                            </div>

                            <div class="pivot-panel" data-pivot-panel="program">
                                <x-table
                                    :headers="['Program', 'Fail', 'Baris', 'Jumlah']"
                                    wrap-class="table-scroll"
                                    table-class="table align-middle pivot-table mb-0"
                                >
                                    @forelse($pivotRows['program'] as $row)
                                        <tr class="search-highlight" data-search-text="{{ strtolower($row['label']) }}">
                                            <td class="fw-bold">{{ $row['label'] }}</td>
                                            <td>{{ number_format($row['count']) }}</td>
                                            <td>{{ number_format($row['rows']) }}</td>
                                            <td>{{ number_format($row['amount'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">Tiada data program.</td></tr>
                                    @endforelse
                                </x-table>
                            </div>

                            <div class="pivot-panel" data-pivot-panel="activity">
                                <x-table
                                    :headers="['Aktiviti', 'Fail', 'Baris', 'Jumlah']"
                                    wrap-class="table-scroll"
                                    table-class="table align-middle pivot-table mb-0"
                                >
                                    @forelse($pivotRows['activity'] as $row)
                                        <tr class="search-highlight" data-search-text="{{ strtolower($row['label']) }}">
                                            <td class="fw-bold">{{ $row['label'] }}</td>
                                            <td>{{ number_format($row['count']) }}</td>
                                            <td>{{ number_format($row['rows']) }}</td>
                                            <td>{{ number_format($row['amount'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">Tiada data aktiviti.</td></tr>
                                    @endforelse
                                </x-table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xxl-4">
                    <div class="panel-card h-100 mb-3 search-highlight" data-search-text="sector program activity summary">
                        <div class="panel-head">
                            <div>
                                <h2>Operational summaries</h2>
                                <div class="sub">Sector, program, activity density</div>
                            </div>
                            <span class="badge rounded-pill text-bg-light text-dark border px-3 py-2">{{ number_format($pivotTotals['sector'] + $pivotTotals['program'] + $pivotTotals['activity'], 2) }}</span>
                        </div>
                        <div class="panel-body mini-grid">
                            <div class="mini-card search-highlight" data-search-text="sector summary"><div class="title">Sektor</div><div class="value">{{ number_format($pivotTotals['sector'], 2) }}</div><div class="sub">{{ number_format($pivotRows['sector']->count()) }} kumpulan</div></div>
                            <div class="mini-card search-highlight" data-search-text="program summary"><div class="title">Program</div><div class="value">{{ number_format($pivotTotals['program'], 2) }}</div><div class="sub">{{ number_format($pivotRows['program']->count()) }} kumpulan</div></div>
                            <div class="mini-card search-highlight" data-search-text="activity summary"><div class="title">Aktiviti</div><div class="value">{{ number_format($pivotTotals['activity'], 2) }}</div><div class="sub">{{ number_format($pivotRows['activity']->count()) }} kumpulan</div></div>
                            <div class="mini-card search-highlight" data-search-text="header snapshot"><div class="title">Headers</div><div class="value">{{ number_format($headerFieldsCount) }}</div><div class="sub">{{ number_format($headerCards->count()) }} field</div></div>
                        </div>
                    </div>

                    <div class="panel-card h-100 search-highlight" data-search-text="top sections ranking">
                        <div class="panel-head">
                            <div>
                                <h2>Top sections</h2>
                                <div class="sub">Highest selected-year values</div>
                            </div>
                            <span class="badge rounded-pill text-bg-primary-subtle text-primary-emphasis border border-primary-subtle px-3 py-2">Ranked</span>
                        </div>
                        <div class="panel-body compact-list">
                            @forelse($topSectionRows as $row)
                                <div class="compact-row search-highlight" data-search-text="{{ strtolower($row['label'] . ' ' . $row['code']) }}">
                                    <div>
                                        <div class="name">{{ $row['label'] }}</div>
                                        <div class="meta">{{ $row['code'] }} · {{ number_format($row['selected_year'], 2) }}</div>
                                    </div>
                                    <div class="score">{{ number_format($row['selected_year'], 2) }}</div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">Tiada data seksyen.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-xxl-7">
                    <div class="panel-card h-100 search-highlight" data-search-text="header snapshot metadata">
                        <div class="panel-head">
                            <div>
                                <h2>Header snapshot</h2>
                                <div class="sub">Workbook metadata and extracted header fields</div>
                            </div>
                            <span class="badge rounded-pill text-bg-light text-dark border px-3 py-2">{{ $headerCards->count() }} items</span>
                        </div>
                        <div class="panel-body">
                            <x-table
                                :headers="['Field', 'Value']"
                                wrap-class="table-scroll"
                                table-class="table align-middle pivot-table mb-0"
                                table-style="min-width: 680px;"
                            >
                                @forelse($headerCards as $card)
                                    <tr class="search-highlight" data-search-text="{{ $card['search'] }}">
                                        <td class="fw-bold">{{ $card['label'] }}</td>
                                        <td>{{ is_array($card['value']) || is_object($card['value']) ? json_encode($card['value']) : $card['value'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-4">Tiada header metadata.</td></tr>
                                @endforelse
                            </x-table>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xxl-5">
                    <div class="panel-card h-100 search-highlight" data-search-text="recent uploads audit trail">
                        <div class="panel-head">
                            <div>
                                <h2>Recent uploads</h2>
                                <div class="sub">Operational entry points</div>
                            </div>
                            <a href="{{ route('abm.v3.import') }}" class="btn btn-sm btn-outline-primary rounded-pill">Muat naik baharu</a>
                        </div>
                        <div class="panel-body">
                            <x-table
                                :headers="['Reference', 'Template', 'Rows', 'Status', 'Date']"
                                wrap-class="table-scroll"
                                table-class="table align-middle pivot-table mb-0"
                            >
                                @forelse($selectedUploads->take(5) as $upload)
                                    <tr class="search-highlight" data-search-text="{{ strtolower($upload->reference_no . ' ' . $upload->template_type_label . ' ' . $upload->total_rows . ' ' . $upload->status_label) }}">
                                        <td class="fw-bold"><a href="{{ route('abm.v3.preview', $upload->id) }}" class="text-decoration-none text-dark">{{ $upload->reference_no }}</a></td>
                                        <td>{{ $upload->template_type_label }}</td>
                                        <td>{{ number_format($upload->total_rows) }}</td>
                                        <td><span class="status-pill {{ $upload->status_color }}">{{ $upload->status_label }}</span></td>
                                        <td>{{ optional($upload->created_at)->format('d M Y, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Tiada muat naik terkini.</td></tr>
                                @endforelse
                            </x-table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-xxl-7">
                    <div class="panel-card h-100 search-highlight" data-search-text="data trail audit log activity">
                        <div class="panel-head">
                            <div>
                                <h2>Data trail</h2>
                                <div class="sub">Latest workflow events and upload actions</div>
                            </div>
                            <span class="badge rounded-pill text-bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-2">Audit</span>
                        </div>
                        <div class="panel-body">
                            <div class="timeline">
                                @forelse($selectedActivities->take(8) as $activity)
                                    <div class="timeline-item search-highlight" data-search-text="{{ strtolower($activity->action_label . ' ' . $activity->description) }}">
                                        <div class="timeline-dot"></div>
                                        <div>
                                            <strong>{{ $activity->action_label }}</strong>
                                            <p>{{ $activity->description }}</p>
                                            <span>{{ $activity->upload?->reference_no ?? '-' }} · {{ $activity->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-4">Tiada aktiviti audit.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xxl-5">
                    <div class="panel-card h-100 search-highlight" data-search-text="supporting metadata reference cards">
                        <div class="panel-head">
                            <div>
                                <h2>Header detail cards</h2>
                                <div class="sub">Snapshot of extracted metadata</div>
                            </div>
                            <span class="badge rounded-pill text-bg-light text-dark border px-3 py-2">{{ $headerCards->count() }}</span>
                        </div>
                        <div class="panel-body mini-grid">
                            @forelse($headerCards->take(6) as $card)
                                <div class="mini-card search-highlight" data-search-text="{{ $card['search'] }}">
                                    <div class="title">{{ $card['label'] }}</div>
                                    @php $valueText = is_array($card['value']) || is_object($card['value']) ? (string) count((array) $card['value']) : (string) $card['value']; @endphp
                                    <div class="value">{{ strlen($valueText) > 24 ? substr($valueText, 0, 24) . '…' : $valueText }}</div>
                                    <div class="sub">{{ is_array($card['value']) || is_object($card['value']) ? 'complex value' : 'metadata field' }}</div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">Tiada header metadata.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        (function () {
            const yearSeries = {!! json_encode($comparisonRows->values()) !!};
            const selectedYear = {!! json_encode($selectedYear) !!};
            const previousYear = {!! json_encode($previousYear) !!};
            const uploadsCount = {!! json_encode($uploadsCount) !!};
            const rowsCount = {!! json_encode($rowsCount) !!};
            const amountValue = {!! json_encode(number_format($amountValue, 2, '.', '')) !!};
            const sectionLabels = {!! json_encode($sectionLabels) !!};
            const sectionValues = {!! json_encode($sectionValues) !!};
            const months = {!! json_encode($calendarRows->pluck('label')->values()) !!};
            const monthAmounts = {!! json_encode($monthAmounts) !!};
            const topSectionTotal = {!! json_encode($topSectionTotal) !!};

            const yearComparisonOptions = {
                series: [
                    {
                        name: 'Amaun',
                        data: yearSeries.map(item => Number(item.amount || 0)),
                    },
                    {
                        name: 'Baris',
                        data: yearSeries.map(item => Number(item.rows || 0)),
                    },
                ],
                chart: {
                    type: 'bar',
                    height: 340,
                    stacked: false,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif',
                },
                colors: ['#0f766e', '#94a3b8'],
                plotOptions: {
                    bar: {
                        columnWidth: '38%',
                        borderRadius: 10,
                    },
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: yearSeries.map(item => String(item.year)),
                    labels: { style: { colors: '#64748b' } },
                },
                yaxis: {
                    labels: {
                        formatter: value => Number(value).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }),
                    },
                },
                tooltip: {
                    y: {
                        formatter: value => Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                    },
                },
                grid: { borderColor: '#e5edf5' },
                legend: { position: 'top', horizontalAlign: 'left' },
            };

            const sectionDonutOptions = {
                series: sectionValues,
                chart: {
                    type: 'donut',
                    height: 280,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif',
                },
                labels: sectionLabels,
                colors: {!! json_encode($donutColors) !!},
                dataLabels: { enabled: false },
                legend: {
                    position: 'bottom',
                    labels: { colors: '#475569' },
                },
                stroke: { width: 0 },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: { show: true, color: '#0f172a' },
                                value: {
                                    show: true,
                                    color: '#0f172a',
                                    formatter: value => Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                                },
                                total: {
                                    show: true,
                                    label: 'Top sections',
                                    formatter: () => Number(topSectionTotal).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                                },
                            },
                        },
                    },
                },
                tooltip: {
                    y: {
                        formatter: value => Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                    },
                },
            };

            const monthlyTrendOptions = {
                series: [{ name: 'Jumlah', data: monthAmounts }],
                chart: {
                    type: 'area',
                    height: 340,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif',
                },
                colors: ['#2563eb'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 0.8,
                        opacityFrom: 0.42,
                        opacityTo: 0.06,
                        stops: [0, 90, 100],
                    },
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                },
                markers: { size: 4, strokeWidth: 0 },
                xaxis: {
                    categories: months,
                    labels: { style: { colors: '#64748b' } },
                },
                yaxis: {
                    labels: {
                        formatter: value => Number(value).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }),
                    },
                },
                grid: { borderColor: '#e5edf5' },
                tooltip: {
                    y: {
                        formatter: value => Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                    },
                },
            };

            const yearComparisonChart = new ApexCharts(document.querySelector('#yearComparisonChart'), yearComparisonOptions);
            const sectionDonutChart = new ApexCharts(document.querySelector('#sectionDonutChart'), sectionDonutOptions);
            const monthlyTrendChart = new ApexCharts(document.querySelector('#monthlyTrendChart'), monthlyTrendOptions);

            yearComparisonChart.render();
            sectionDonutChart.render();
            monthlyTrendChart.render();

            const pivotTabs = document.querySelectorAll('[data-pivot-target]');
            const pivotPanels = document.querySelectorAll('[data-pivot-panel]');
            pivotTabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    const target = tab.getAttribute('data-pivot-target');
                    pivotTabs.forEach((item) => item.classList.toggle('active', item === tab));
                    pivotPanels.forEach((panel) => panel.classList.toggle('active', panel.getAttribute('data-pivot-panel') === target));
                });
            });

            const searchInput = document.getElementById('dashboardSearch');
            const searchableNodes = Array.from(document.querySelectorAll('[data-search-text]'));

            function applySearch() {
                const term = String(searchInput?.value || '').trim().toLowerCase();
                searchableNodes.forEach((node) => {
                    const text = String(node.getAttribute('data-search-text') || '');
                    node.classList.toggle('is-hidden', term !== '' && !text.includes(term));
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', applySearch);
            }

            document.querySelectorAll('[data-focus]').forEach((button) => {
                button.addEventListener('click', () => {
                    const target = document.getElementById(button.getAttribute('data-focus'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

            const exportCsvBtn = document.getElementById('exportCsvBtn');
            if (exportCsvBtn) {
                exportCsvBtn.addEventListener('click', () => {
                    const lines = [
                        ['Label', 'Value'].join(','),
                        ['Selected Year', selectedYear].join(','),
                        ['Previous Year', previousYear].join(','),
                        ['Uploads', uploadsCount].join(','),
                        ['Rows', rowsCount].join(','),
                        ['Amount', amountValue].join(','),
                    ];

                    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = `abm-dashboard-${selectedYear}.csv`;
                    link.click();
                    URL.revokeObjectURL(link.href);
                });
            }
        })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const overlay = document.getElementById('abmWipOverlay');
            const closeButton = document.getElementById('abmWipClose');

            if (!overlay || !closeButton) {
                return;
            }

            const closeOverlay = function () {
                overlay.classList.add('abm-wip-hidden');
            };

            closeButton.addEventListener('click', closeOverlay);

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeOverlay();
                }
            });
        });
    </script>
@endpush