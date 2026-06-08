<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AbmV3WorkbookParser
{
    public function parsePath(string $path, ?string $templateType = null): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheets = [];
        $headerInformation = [];
        $budgetRows = [];
        $budgetSections = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheetIndex => $sheet) {
            $sheetName = trim((string) $sheet->getTitle()) ?: 'Sheet ' . ($sheetIndex + 1);
            $sheetData = $this->parseSheet($sheet->toArray(null, true, true, true), $sheetName, $templateType, $sheetIndex + 1);

            if (! $sheetData['budget_rows'] && ! $sheetData['header_information']) {
                continue;
            }

            $sheets[] = $sheetData;
            $headerInformation = $headerInformation ?: $sheetData['header_information'];
            $budgetRows = array_merge($budgetRows, $sheetData['budget_rows']);
            $budgetSections = array_merge($budgetSections, $sheetData['budget_sections']);
        }

        $budgetSections = $this->mergeSections($budgetSections);
        $grandTotal = array_sum(array_map(fn (array $section) => (float) ($section['amount'] ?? 0), $budgetSections));
        $hierarchy = $this->buildHierarchy($headerInformation, $budgetSections);

        return [
            'header_information' => $headerInformation,
            'budget_rows' => $budgetRows,
            'budget_sections' => $budgetSections,
            'hierarchy' => $hierarchy,
            'summary_tree' => $budgetSections,
            'flat_rows' => $budgetRows,
            'sheets' => $sheets,
            'totals' => [
                'sheets' => count($sheets),
                'sections' => count($budgetSections),
                'rows' => count($budgetRows),
                'amount' => $grandTotal,
                'categories' => count($budgetSections),
                'programs' => count($budgetRows),
                'activities' => 0,
                'header_fields' => count(array_filter($headerInformation, fn ($value) => trim((string) $value) !== '')),
            ],
        ];
    }

    private function parseSheet(array $rows, string $sheetName, ?string $templateType, int $sheetIndex): array
    {
        $rows = array_values(array_filter($rows, function (array $row) {
            foreach ($row as $cell) {
                if (trim((string) $cell) !== '') {
                    return true;
                }
            }

            return false;
        }));

        if (! $rows) {
            return [
                'sheet_index' => $sheetIndex,
                'name' => $sheetName,
                'header_information' => [],
                'budget_rows' => [],
                'budget_sections' => [],
            ];
        }

        $budgetHeaderRow = $this->detectBudgetHeaderRow($rows);
        $headerInformation = $this->extractHeaderInformation($rows, $budgetHeaderRow);
        $budgetRows = $budgetHeaderRow === null
            ? $this->extractBudgetRowsWithoutHeader($rows, $sheetName, $sheetIndex, $templateType)
            : $this->extractBudgetRows($rows, $budgetHeaderRow, $sheetName, $sheetIndex, $templateType);
        $budgetSections = $this->buildBudgetSections($budgetRows);

        return [
            'sheet_index' => $sheetIndex,
            'name' => $sheetName,
            'header_information' => $headerInformation,
            'budget_rows' => $budgetRows,
            'budget_sections' => $budgetSections,
        ];
    }

    private function extractHeaderInformation(array $rows, ?int $budgetHeaderRow): array
    {
        $header = [
            'sektor' => null,
            'maksud' => null,
            'program' => null,
            'aktiviti' => null,
            'jenis_aktiviti' => null,
            'dasar' => null,
            'tahun' => null,
            'tajuk' => null,
        ];

        $limit = $budgetHeaderRow === null ? min(count($rows), 24) : min($budgetHeaderRow, 24);

        for ($index = 0; $index < $limit; $index++) {
            $cells = $this->nonEmptyCells($rows[$index]);
            if (! $cells) {
                continue;
            }

            for ($cellIndex = 0; $cellIndex < count($cells); $cellIndex++) {
                $label = $this->mapHeaderLabel($cells[$cellIndex]);
                if (! $label) {
                    continue;
                }

                $value = $this->firstMeaningfulCellAfter($cells, $cellIndex);
                if ($value === null) {
                    continue;
                }

                if (trim((string) $header[$label]) === '') {
                    $header[$label] = $label === 'tahun'
                        ? ($this->normalizeYearValue($value) ?? $value)
                        : $value;
                }
            }
        }

        return $header;
    }

    private function detectBudgetHeaderRow(array $rows): ?int
    {
        $expected = ['kod', 'jenis_perbelanjaan', 'peruntukan_2024', 'anggaran_dipohon_2026'];

        foreach ($rows as $index => $row) {
            $normalized = array_values(array_filter(array_map(fn ($cell) => $this->normalizeHeader((string) $cell), $row)));
            $hits = 0;

            foreach ($expected as $word) {
                foreach ($normalized as $cell) {
                    if ($cell !== '' && Str::contains($cell, $word)) {
                        $hits++;
                        break;
                    }
                }
            }

            if ($hits >= 2) {
                return $index;
            }
        }

        return null;
    }

    private function extractBudgetRowsWithoutHeader(array $rows, string $sheetName, int $sheetIndex, ?string $templateType): array
    {
        $out = [];
        $currentSection = null;
        $started = false;

        foreach ($rows as $rowIndex => $row) {
            $cells = $this->nonEmptyCells($row);
            if (! $cells) {
                continue;
            }

            $codeKey = $this->findBudgetCodeIndex($row);
            if ($codeKey === null) {
                continue;
            }

            $code = trim((string) ($row[$codeKey] ?? ''));
            $description = $this->firstNonEmptyCellAfter($row, $codeKey);
            $codeIndex = $this->columnKeyToPosition($row, $codeKey);

            if (! $started) {
                if ($this->looksLikeBudgetCode($code) || $this->isTotalRow($code, $description)) {
                    $started = true;
                } else {
                    continue;
                }
            }

            if (! $this->looksLikeBudgetCode($code) && ! $this->isTotalRow($code, $description)) {
                continue;
            }

            $groupCode = $this->resolveBudgetGroupCode($code);
            $rowType = $this->classifyBudgetRow($code, $description, $this->isTotalRow($code, $description));

            if ($rowType === 'GROUP_HEADER' || $rowType === 'TOTAL') {
                $currentSection = $this->normalizeGroupName($groupCode, $description, $code);
            } elseif (! $currentSection) {
                $currentSection = $this->normalizeGroupName($groupCode, $description, $code);
            }

            $mapped = $this->mapBudgetColumnsFromWorksheetRow($row, $codeKey);
            $rowTotals = $this->calculateBudgetRowTotals($mapped);

            $out[] = [
                'sheet_name' => $sheetName,
                'sheet_index' => $sheetIndex,
                'sheet_row' => $rowIndex + 1,
                'template_type' => $templateType,
                'row_type' => $rowType,
                'section_code' => $groupCode,
                'section_name' => $currentSection,
                'code' => $code ?: null,
                'jenis_perbelanjaan' => $description ?: null,
                'peruntukan_2024' => $rowTotals['peruntukan_2024'],
                'perbelanjaan_sebenar_2024' => $rowTotals['perbelanjaan_sebenar_2024'],
                'peruntukan_asal_2025' => $rowTotals['peruntukan_asal_2025'],
                'anggaran_dipohon_2026' => $rowTotals['anggaran_dipohon_2026'],
                'anggaran_disyorkan_2026' => $rowTotals['anggaran_disyorkan_2026'],
                'beza_rm' => $rowTotals['beza_rm'],
                'beza_pct' => $rowTotals['beza_pct'],
                'anggaran_dipohon_2027' => $rowTotals['anggaran_dipohon_2027'],
                'anggaran_disyorkan_2027' => $rowTotals['anggaran_disyorkan_2027'],
                'beza_rm_2027' => $rowTotals['beza_rm_2027'],
                'beza_pct_2027' => $rowTotals['beza_pct_2027'],
                'display_amount' => $rowTotals['amount'],
                'amount' => $rowTotals['amount'],
                'raw' => $mapped['raw'],
                'sheet_row_code' => $code ?: null,
            ];
        }

        return $out;
    }

    private function extractBudgetRows(array $rows, int $budgetHeaderRow, string $sheetName, int $sheetIndex, ?string $templateType): array
    {
        $headers = $this->buildHeaders($rows[$budgetHeaderRow]);
        $out = [];
        $currentSection = null;

        foreach (array_slice($rows, $budgetHeaderRow + 1) as $rowIndex => $row) {
            $record = [];

            foreach ($headers as $column => $key) {
                $record[$key] = trim((string) ($row[$column] ?? ''));
            }

            if (! array_filter($record, fn ($value) => trim((string) $value) !== '')) {
                continue;
            }

            $codeKey = $this->findBudgetCodeIndex($row);
            if ($codeKey === null) {
                continue;
            }

            $codeIndex = $codeKey !== null ? $this->columnKeyToPosition($row, $codeKey) : null;
            $code = $this->firstValue($record, ['kod']) ?: ($codeKey !== null ? trim((string) ($row[$codeKey] ?? '')) : null);
            $description = $this->firstValue($record, ['jenis_perbelanjaan']) ?: ($codeKey !== null ? $this->firstNonEmptyCellAfter($row, $codeKey) : $this->secondCell($row));
            $groupCode = $this->resolveBudgetGroupCode($code);
            $isTotalRow = $this->isTotalRow($code, $description);
            $rowType = $this->classifyBudgetRow($code, $description, $isTotalRow);

            if ($rowType === 'GROUP_HEADER' || $rowType === 'TOTAL') {
                $currentSection = $this->normalizeGroupName($groupCode, $description, $code);
            } elseif (! $currentSection) {
                $currentSection = $this->normalizeGroupName($groupCode, $description, $code);
            }

            $mapped = $this->mapBudgetColumnsFromWorksheetRow($row, $codeKey);
            $rowTotals = $this->calculateBudgetRowTotals($mapped);

            $out[] = [
                'sheet_name' => $sheetName,
                'sheet_index' => $sheetIndex,
                'sheet_row' => $budgetHeaderRow + $rowIndex + 2,
                'template_type' => $templateType,
                'row_type' => $rowType,
                'section_code' => $groupCode,
                'section_name' => $currentSection,
                'code' => $code ?: null,
                'jenis_perbelanjaan' => $description ?: null,
                'peruntukan_2024' => $rowTotals['peruntukan_2024'],
                'perbelanjaan_sebenar_2024' => $rowTotals['perbelanjaan_sebenar_2024'],
                'peruntukan_asal_2025' => $rowTotals['peruntukan_asal_2025'],
                'anggaran_dipohon_2026' => $rowTotals['anggaran_dipohon_2026'],
                'anggaran_disyorkan_2026' => $rowTotals['anggaran_disyorkan_2026'],
                'beza_rm' => $rowTotals['beza_rm'],
                'beza_pct' => $rowTotals['beza_pct'],
                'anggaran_dipohon_2027' => $rowTotals['anggaran_dipohon_2027'],
                'anggaran_disyorkan_2027' => $rowTotals['anggaran_disyorkan_2027'],
                'beza_rm_2027' => $rowTotals['beza_rm_2027'],
                'beza_pct_2027' => $rowTotals['beza_pct_2027'],
                'display_amount' => $rowTotals['amount'],
                'amount' => $rowTotals['amount'],
                'raw' => $record,
                'sheet_row_code' => $code ?: null,
            ];
        }

        return $out;
    }

    private function buildBudgetSections(array $budgetRows): array
    {
        $categories = [];
        $pendingDetails = [];

        foreach ($budgetRows as $row) {
            $rowType = $row['row_type'] ?? 'DETAIL';

            if ($rowType === 'TOTAL') {
                continue;
            }

            if ($rowType === 'GROUP_HEADER') {
                $categoryCode = $this->normalizeCode($row['code'] ?? null) ?: ($row['section_code'] ?: 'UNCLASSIFIED');
                $categoryName = $this->looksLikeTextValue((string) ($row['jenis_perbelanjaan'] ?? ''))
                    ? trim((string) $row['jenis_perbelanjaan'])
                    : $this->normalizeGroupName($categoryCode, $row['jenis_perbelanjaan'] ?? null, $row['code'] ?? null);

                $details = $pendingDetails;
                $pendingDetails = [];

                $detailsAmount = array_sum(array_map(fn (array $detail) => (float) ($detail['amount'] ?? 0), $details));
                $categoryAmount = $detailsAmount;
                $orderedRows = $details;
                $row['amount'] = $detailsAmount;
                $row['display_amount'] = $detailsAmount;
                $row['totals'] = $this->sumBudgetRows($details);
                $orderedRows[] = $row;

                $categories[] = [
                    'code' => $categoryCode,
                    'name' => $categoryName,
                    'row_count' => count($orderedRows),
                    'amount' => $detailsAmount,
                    'category_amount' => $categoryAmount,
                    'detail_amount' => $detailsAmount,
                    'totals' => $row['totals'] ?? $this->sumBudgetRows($details),
                    'rows' => $orderedRows,
                    'category_row' => $row,
                ];

                continue;
            }

            $pendingDetails[] = $row;
        }

        if ($pendingDetails) {
            if ($categories) {
                $lastIndex = array_key_last($categories);
                $detailsAmount = array_sum(array_map(fn (array $detail) => (float) ($detail['amount'] ?? 0), $pendingDetails));
                $categories[$lastIndex]['rows'] = array_merge($categories[$lastIndex]['rows'], $pendingDetails);
                $categories[$lastIndex]['row_count'] = count($categories[$lastIndex]['rows']);
                $categories[$lastIndex]['detail_amount'] += $detailsAmount;
                $categories[$lastIndex]['amount'] = (float) ($categories[$lastIndex]['amount'] ?? 0) + $detailsAmount;
                $categories[$lastIndex]['totals'] = $this->sumBudgetRows(array_merge($categories[$lastIndex]['rows'], []));
            } else {
                $detailsAmount = array_sum(array_map(fn (array $detail) => (float) ($detail['amount'] ?? 0), $pendingDetails));
                $categories[] = [
                    'code' => 'UNCLASSIFIED',
                    'name' => 'TIDAK DINYATAKAN',
                    'row_count' => count($pendingDetails),
                    'amount' => $detailsAmount,
                    'category_amount' => 0,
                    'detail_amount' => $detailsAmount,
                    'totals' => $this->sumBudgetRows($pendingDetails),
                    'rows' => $pendingDetails,
                    'category_row' => null,
                ];
            }
        }

        return $categories;
    }

    private function buildHierarchy(array $headerInformation, array $budgetSections): array
    {
        return [
            'header_information' => $headerInformation,
            'budget_sections' => $budgetSections,
        ];
    }

    private function mergeSections(array $budgetSections): array
    {
        $merged = [];

        foreach ($budgetSections as $section) {
            $code = $section['code'] ?: 'UNCLASSIFIED';

            if (! isset($merged[$code])) {
                $merged[$code] = $section;
                continue;
            }

            $merged[$code]['row_count'] += $section['row_count'];
            $merged[$code]['amount'] += $section['amount'];
            $merged[$code]['category_amount'] += $section['category_amount'] ?? 0;
            $merged[$code]['detail_amount'] += $section['detail_amount'] ?? 0;
            $merged[$code]['rows'] = array_merge($merged[$code]['rows'], $section['rows']);
            $merged[$code]['totals'] = $this->mergeColumnTotals($merged[$code]['totals'] ?? [], $section['totals'] ?? []);
        }

        return array_values($merged);
    }

    private function calculateBudgetRowTotals(array $mapped): array
    {
        $peruntukan2024 = $this->normalizeAmount($mapped['peruntukan_2024'] ?? null);
        $perbelanjaan2024 = $this->normalizeAmount($mapped['perbelanjaan_sebenar_2024'] ?? null);
        $peruntukanAsal2025 = $this->normalizeAmount($mapped['peruntukan_asal_2025'] ?? null);
        $anggaranDipohon2026 = $this->normalizeAmount($mapped['anggaran_dipohon_2026'] ?? null);
        $anggaranDisyorkan2026 = $this->normalizeAmount($mapped['anggaran_disyorkan_2026'] ?? null);
        $anggaranDipohon2027 = $this->normalizeAmount($mapped['anggaran_dipohon_2027'] ?? null);
        $anggaranDisyorkan2027 = $this->normalizeAmount($mapped['anggaran_disyorkan_2027'] ?? null);

        $bezaRm = ($anggaranDisyorkan2026 > 0.0 && $peruntukanAsal2025 > 0.0)
            ? $anggaranDisyorkan2026 - $peruntukanAsal2025
            : 0.0;
        $bezaPct = ($anggaranDisyorkan2026 > 0.0 && $peruntukanAsal2025 > 0.0)
            ? ($bezaRm / $peruntukanAsal2025) * 100
            : 0.0;
        $bezaRm2027 = ($anggaranDisyorkan2027 > 0.0 && $anggaranDisyorkan2026 > 0.0)
            ? $anggaranDisyorkan2027 - $anggaranDisyorkan2026
            : 0.0;
        $bezaPct2027 = ($anggaranDisyorkan2027 > 0.0 && $anggaranDisyorkan2026 > 0.0)
            ? ($bezaRm2027 / $anggaranDisyorkan2026) * 100
            : 0.0;

        return [
            'peruntukan_2024' => $peruntukan2024,
            'perbelanjaan_sebenar_2024' => $perbelanjaan2024,
            'peruntukan_asal_2025' => $peruntukanAsal2025,
            'anggaran_dipohon_2026' => $anggaranDipohon2026,
            'anggaran_disyorkan_2026' => $anggaranDisyorkan2026,
            'beza_rm' => $bezaRm,
            'beza_pct' => $bezaPct,
            'anggaran_dipohon_2027' => $anggaranDipohon2027,
            'anggaran_disyorkan_2027' => $anggaranDisyorkan2027,
            'beza_rm_2027' => $bezaRm2027,
            'beza_pct_2027' => $bezaPct2027,
            'amount' => $anggaranDisyorkan2027 > 0 ? $anggaranDisyorkan2027 : $anggaranDisyorkan2026,
        ];
    }

    private function sumBudgetRows(array $rows): array
    {
        $totals = [
            'peruntukan_2024' => 0.0,
            'perbelanjaan_sebenar_2024' => 0.0,
            'peruntukan_asal_2025' => 0.0,
            'anggaran_dipohon_2026' => 0.0,
            'anggaran_disyorkan_2026' => 0.0,
            'beza_rm' => 0.0,
            'beza_pct' => 0.0,
            'anggaran_dipohon_2027' => 0.0,
            'anggaran_disyorkan_2027' => 0.0,
            'beza_rm_2027' => 0.0,
            'beza_pct_2027' => 0.0,
            'amount' => 0.0,
        ];

        foreach ($rows as $row) {
            $totals['peruntukan_2024'] += (float) ($row['peruntukan_2024'] ?? 0);
            $totals['perbelanjaan_sebenar_2024'] += (float) ($row['perbelanjaan_sebenar_2024'] ?? 0);
            $totals['peruntukan_asal_2025'] += (float) ($row['peruntukan_asal_2025'] ?? 0);
            $totals['anggaran_dipohon_2026'] += (float) ($row['anggaran_dipohon_2026'] ?? 0);
            $totals['anggaran_disyorkan_2026'] += (float) ($row['anggaran_disyorkan_2026'] ?? 0);
            $totals['anggaran_dipohon_2027'] += (float) ($row['anggaran_dipohon_2027'] ?? 0);
            $totals['anggaran_disyorkan_2027'] += (float) ($row['anggaran_disyorkan_2027'] ?? 0);
        }

        $totals['beza_rm'] = ($totals['anggaran_disyorkan_2026'] > 0.0 && $totals['peruntukan_asal_2025'] > 0.0)
            ? $totals['anggaran_disyorkan_2026'] - $totals['peruntukan_asal_2025']
            : 0.0;
        $totals['beza_pct'] = ($totals['anggaran_disyorkan_2026'] > 0.0 && $totals['peruntukan_asal_2025'] > 0.0)
            ? ($totals['beza_rm'] / $totals['peruntukan_asal_2025']) * 100
            : 0.0;
        $totals['beza_rm_2027'] = ($totals['anggaran_disyorkan_2027'] > 0.0 && $totals['anggaran_disyorkan_2026'] > 0.0)
            ? $totals['anggaran_disyorkan_2027'] - $totals['anggaran_disyorkan_2026']
            : 0.0;
        $totals['beza_pct_2027'] = ($totals['anggaran_disyorkan_2027'] > 0.0 && $totals['anggaran_disyorkan_2026'] > 0.0)
            ? ($totals['beza_rm_2027'] / $totals['anggaran_disyorkan_2026']) * 100
            : 0.0;
        $totals['amount'] = $totals['anggaran_disyorkan_2027'] > 0 ? $totals['anggaran_disyorkan_2027'] : $totals['anggaran_disyorkan_2026'];

        return $totals;
    }

    private function mergeColumnTotals(array $left, array $right): array
    {
        $merged = [];
        foreach (['peruntukan_2024', 'perbelanjaan_sebenar_2024', 'peruntukan_asal_2025', 'anggaran_dipohon_2026', 'anggaran_disyorkan_2026', 'beza_rm', 'beza_pct', 'anggaran_dipohon_2027', 'anggaran_disyorkan_2027', 'beza_rm_2027', 'beza_pct_2027', 'amount'] as $key) {
            $merged[$key] = (float) ($left[$key] ?? 0) + (float) ($right[$key] ?? 0);
        }

        $merged['beza_rm'] = ($merged['anggaran_disyorkan_2026'] > 0.0 && $merged['peruntukan_asal_2025'] > 0.0)
            ? $merged['anggaran_disyorkan_2026'] - $merged['peruntukan_asal_2025']
            : 0.0;
        $merged['beza_pct'] = ($merged['anggaran_disyorkan_2026'] > 0.0 && $merged['peruntukan_asal_2025'] > 0.0)
            ? ($merged['beza_rm'] / $merged['peruntukan_asal_2025']) * 100
            : 0.0;
        $merged['beza_rm_2027'] = ($merged['anggaran_disyorkan_2027'] > 0.0 && $merged['anggaran_disyorkan_2026'] > 0.0)
            ? $merged['anggaran_disyorkan_2027'] - $merged['anggaran_disyorkan_2026']
            : 0.0;
        $merged['beza_pct_2027'] = ($merged['anggaran_disyorkan_2027'] > 0.0 && $merged['anggaran_disyorkan_2026'] > 0.0)
            ? ($merged['beza_rm_2027'] / $merged['anggaran_disyorkan_2026']) * 100
            : 0.0;
        $merged['amount'] = $merged['anggaran_disyorkan_2027'] > 0 ? $merged['anggaran_disyorkan_2027'] : $merged['anggaran_disyorkan_2026'];

        return $merged;
    }

    private function firstMeaningfulCellAfter(array $cells, int $index): ?string
    {
        for ($cursor = $index + 1, $count = count($cells); $cursor < $count; $cursor++) {
            $value = trim((string) ($cells[$cursor] ?? ''));
            if ($value === '' || trim(str_replace([':', ';', ',', '.', '-', '_', '/', '\\', '|'], '', $value)) === '') {
                continue;
            }

            return $value;
        }

        return null;
    }

    private function normalizeYearValue(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 1900 && $value <= 2100 ? $value : null;
        }

        if (is_float($value)) {
            $year = (int) round($value);

            return $year >= 1900 && $year <= 2100 ? $year : null;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/\b(19|20)\d{2}\b/', $value, $matches)) {
            $year = (int) $matches[0];

            return $year >= 1900 && $year <= 2100 ? $year : null;
        }

        return null;
    }

    private function classifyBudgetRow(?string $code, ?string $description, bool $isTotalRow): string
    {
        if ($isTotalRow) {
            return 'TOTAL';
        }

        $normalizedCode = $this->normalizeCode($code);

        if ($normalizedCode && in_array($normalizedCode, ['10000', '20000', '30000', '40000', '50000'], true)) {
            return 'GROUP_HEADER';
        }

        return 'DETAIL';
    }

    private function looksLikeTextValue(string $value): bool
    {
        $value = trim($value);

        if ($value === '') {
            return false;
        }

        return (bool) preg_match('/[a-zA-Z]/', $value);
    }

    private function looksLikeBudgetCode(?string $value): bool
    {
        $value = $this->normalizeCode($value);

        return $value !== null && (bool) preg_match('/^(?:\d{5}|\d{6}|TOTAL)$/i', $value);
    }

    private function isTotalRow(?string $code, ?string $description): bool
    {
        $code = Str::upper(trim((string) $code));
        $description = Str::upper(trim((string) $description));

        return $code === 'TOTAL' || Str::contains($description, ['JUMLAH BESAR', 'TOTAL']);
    }

    private function resolveBudgetGroupCode(?string $code): ?string
    {
        $normalizedCode = $this->normalizeCode($code);

        if (! $normalizedCode || ! preg_match('/^\d+$/', $normalizedCode)) {
            return null;
        }

        if (in_array($normalizedCode, ['10000', '20000', '30000', '40000', '50000'], true)) {
            return $normalizedCode;
        }

        $firstDigit = substr($normalizedCode, 0, 1);

        return $firstDigit ? str_pad($firstDigit, 5, '0', STR_PAD_RIGHT) : null;
    }

    private function normalizeGroupName(?string $groupCode, ?string $description = null, ?string $code = null): string
    {
        $map = [
            '10000' => 'EMOLUMEN',
            '20000' => 'PERKHIDMATAN DAN BEKALAN',
            '30000' => 'ASET',
            '40000' => 'PEMBERIAN DAN KENAAAN BAYARAN TETAP',
            '50000' => 'PERBELANJAAN-PERBELANJAAN LAIN',
            'TOTAL' => 'JUMLAH BESAR',
        ];

        if ($groupCode && isset($map[$groupCode])) {
            return $map[$groupCode];
        }

        if ($description !== null && trim($description) !== '') {
            return trim($description);
        }

        if ($code !== null && trim($code) !== '') {
            return trim($code);
        }

        return 'TIDAK DINYATAKAN';
    }

    private function mapHeaderLabel(string $label): ?string
    {
        $normalized = $this->normalizeHeader($label);

        return match (true) {
            Str::contains($normalized, ['sektor']) => 'sektor',
            Str::contains($normalized, ['maksud']) => 'maksud',
            Str::contains($normalized, ['jenis_aktiviti', 'jenisaktiviti']) => 'jenis_aktiviti',
            Str::contains($normalized, ['program']) && ! Str::contains($normalized, ['aktiviti']) => 'program',
            Str::contains($normalized, ['aktiviti']) => 'aktiviti',
            Str::contains($normalized, ['dasar']) => 'dasar',
            Str::contains($normalized, ['tahun']) => 'tahun',
            Str::contains($normalized, ['tajuk']) => 'tajuk',
            default => null,
        };
    }

    private function buildHeaders(array $headerRow): array
    {
        $headers = [];

        foreach ($headerRow as $column => $value) {
            $headers[$column] = $this->normalizeHeader((string) $value) ?: 'column_' . strtolower($column);
        }

        return $headers;
    }

    private function mapBudgetColumnsFromWorksheetRow(array $row, ?string $codeKey = null): array
    {
        $cells = array_values($row);

        $wideLayout = [
            'peruntukan_2024' => $this->normalizeAmount($cells[5] ?? null),
            'perbelanjaan_sebenar_2024' => $this->normalizeAmount($cells[6] ?? null),
            'peruntukan_asal_2025' => $this->normalizeAmount($cells[7] ?? null),
            'anggaran_dipohon_2026' => $this->normalizeAmount($cells[8] ?? null),
            'anggaran_disyorkan_2026' => $this->normalizeAmount($cells[9] ?? null),
            'beza_rm' => $this->normalizeAmount($cells[12] ?? null),
            'beza_pct' => $this->normalizePercentage($cells[13] ?? null),
            'anggaran_dipohon_2027' => $this->normalizeAmount($cells[16] ?? null),
            'anggaran_disyorkan_2027' => $this->normalizeAmount($cells[17] ?? null),
            'beza_rm_2027' => $this->normalizeAmount($cells[18] ?? null),
            'beza_pct_2027' => $this->normalizePercentage($cells[19] ?? null),
            'raw' => $cells,
        ];

        $hasWideLayoutValue = array_sum(array_map(fn ($value) => (float) $value, [
            $wideLayout['peruntukan_2024'],
            $wideLayout['perbelanjaan_sebenar_2024'],
            $wideLayout['peruntukan_asal_2025'],
            $wideLayout['anggaran_dipohon_2026'],
            $wideLayout['anggaran_disyorkan_2026'],
            $wideLayout['anggaran_dipohon_2027'],
            $wideLayout['anggaran_disyorkan_2027'],
        ]));

        if ($hasWideLayoutValue > 0) {
            return $wideLayout;
        }

        $codeIndex = is_string($codeKey) ? $this->columnKeyToPosition($row, $codeKey) : null;
        $values = array_values(array_slice($cells, $codeIndex ?? 0));

        return [
            'peruntukan_2024' => $this->normalizeAmount($values[2] ?? null),
            'perbelanjaan_sebenar_2024' => $this->normalizeAmount($values[3] ?? null),
            'peruntukan_asal_2025' => $this->normalizeAmount($values[4] ?? null),
            'anggaran_dipohon_2026' => $this->normalizeAmount($values[5] ?? null),
            'anggaran_disyorkan_2026' => $this->normalizeAmount($values[6] ?? null),
            'beza_rm' => $this->normalizeAmount($values[7] ?? null),
            'beza_pct' => $this->normalizePercentage($values[8] ?? null),
            'anggaran_dipohon_2027' => $this->normalizeAmount($values[9] ?? null),
            'anggaran_disyorkan_2027' => $this->normalizeAmount($values[10] ?? null),
            'beza_rm_2027' => $this->normalizeAmount($values[11] ?? null),
            'beza_pct_2027' => $this->normalizePercentage($values[12] ?? null),
            'raw' => $cells,
        ];
    }

    private function mapBudgetColumnsFromCells(array $cells, int $codeIndex = 0): array
    {
        $cells = array_values($cells);
        $values = array_values(array_slice($cells, $codeIndex));

        return [
            'peruntukan_2024' => $this->normalizeAmount($values[2] ?? null),
            'perbelanjaan_sebenar_2024' => $this->normalizeAmount($values[3] ?? null),
            'peruntukan_asal_2025' => $this->normalizeAmount($values[4] ?? null),
            'anggaran_dipohon_2026' => $this->normalizeAmount($values[5] ?? null),
            'anggaran_disyorkan_2026' => $this->normalizeAmount($values[6] ?? null),
            'beza_rm' => $this->normalizeAmount($values[7] ?? null),
            'beza_pct' => $this->normalizePercentage($values[8] ?? null),
            'anggaran_dipohon_2027' => $this->normalizeAmount($values[9] ?? null),
            'anggaran_disyorkan_2027' => $this->normalizeAmount($values[10] ?? null),
            'beza_rm_2027' => $this->normalizeAmount($values[11] ?? null),
            'beza_pct_2027' => $this->normalizePercentage($values[12] ?? null),
            'raw' => $cells,
        ];
    }

    private function nonEmptyCells(array $row): array
    {
        return array_values(array_filter(array_map(function ($value) {
            $value = trim((string) $value);
            return $value === '' ? null : $value;
        }, $row)));
    }

    private function firstCell(array $row): ?string
    {
        $cells = $this->nonEmptyCells($row);
        return $cells[0] ?? null;
    }

    private function secondCell(array $row): ?string
    {
        $cells = $this->nonEmptyCells($row);
        return $cells[1] ?? null;
    }

    private function firstNonEmptyCellAfter(array $row, string $columnKey): ?string
    {
        $keys = array_keys($row);
        $position = array_search($columnKey, $keys, true);

        if ($position === false) {
            return null;
        }

        $count = count($keys);

        for ($cursor = $position + 1; $cursor < $count; $cursor++) {
            $value = trim((string) ($row[$keys[$cursor]] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function findBudgetCodeIndex(array $row): ?string
    {
        foreach ($row as $index => $cell) {
            $value = $this->normalizeCode((string) $cell);
            if ($this->looksLikeBudgetCode($value) || $this->isTotalRow($value, null)) {
                return $index;
            }
        }

        return null;
    }

    private function columnKeyToPosition(array $row, string $columnKey): ?int
    {
        $keys = array_keys($row);
        $position = array_search($columnKey, $keys, true);

        return $position === false ? null : $position;
    }

    private function firstValue(array $record, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($record[$key]) && trim((string) $record[$key]) !== '') {
                return trim((string) $record[$key]);
            }
        }

        return null;
    }

    private function normalizeHeader(string $header): string
    {
        $header = Str::lower(trim($header));
        $header = preg_replace('/[^a-z0-9]+/i', '_', $header) ?? '';

        return trim($header, '_');
    }

    private function normalizeCode(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return preg_replace('/\s+/', '', $value) ?: null;
    }

    private function normalizeAmount(mixed $value): float
    {
        if (is_int($value)) {
            return (float) $value;
        }

        if (is_float($value)) {
            return (float) $value;
        }

        if (! is_string($value)) {
            return 0.0;
        }

        $clean = preg_replace('/[^0-9\.,\-]/', '', $value) ?? '';
        if ($clean === '') {
            return 0.0;
        }

        $clean = str_replace(',', '', $clean);

        return (float) $clean;
    }

    private function normalizePercentage(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (! is_string($value)) {
            return 0.0;
        }

        $clean = preg_replace('/[^0-9\.\-]/', '', $value) ?? '';

        return $clean === '' ? 0.0 : (float) $clean;
    }

    private function preferredAmount(array $record): float
    {
        foreach (['anggaran_disyorkan_2027', 'column_r', 'anggaran_disyorkan_2026', 'column_j', 'anggaran_dipohon_2027', 'column_q', 'anggaran_dipohon_2026', 'column_i', 'peruntukan_asal_2025', 'column_h', 'perbelanjaan_sebenar_2024', 'column_g', 'peruntukan_2024', 'column_f'] as $key) {
            $amount = $this->normalizeAmount($record[$key] ?? null);
            if (abs($amount) > 0.00001) {
                return $amount;
            }
        }

        return 0.0;
    }
}