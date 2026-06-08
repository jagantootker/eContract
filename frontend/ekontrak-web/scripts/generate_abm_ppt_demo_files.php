<?php

declare(strict_types=1);

use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require __DIR__ . '/../vendor/autoload.php';

$targetDir = realpath(__DIR__ . '/../../..') . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'abm-ppt-demo';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$headers = ['Tahun', 'Bahagian', 'Program', 'Kod Objek', 'Jumlah'];
$rows = [
    [date('Y'), 'Bahagian Perolehan', 'Program Naik Taraf Infrastruktur', 'KOD001', 1200000],
    [date('Y'), 'Bahagian Kewangan', 'Program Pengukuhan Tadbir Urus', 'KOD112', 650000],
    [date('Y'), 'Bahagian ICT', 'Program Pendigitalan Sistem', 'KOD305', 980000],
    [date('Y'), 'Bahagian Pembangunan', 'Program Pembangunan Bandar', 'KOD411', 1450000],
];

$excelFiles = [
    'ABM1_Demo.xlsx',
    'ABM2_Demo.xlsx',
    'ABM7A_Demo.xlsx',
    'ABM7B_Demo.xlsx',
    'PPT_Baru_Demo.xlsx',
];

foreach ($excelFiles as $fileName) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    foreach ($headers as $index => $header) {
        $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
    }

    foreach ($rows as $rowIndex => $row) {
        foreach ($row as $colIndex => $value) {
            $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $value);
        }
    }

    $sheet->setTitle(pathinfo($fileName, PATHINFO_FILENAME));

    $writer = new Xlsx($spreadsheet);
    $writer->save($targetDir . DIRECTORY_SEPARATOR . $fileName);

    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
}

$pdfFiles = [
    'ABM1_Demo.pdf' => 'ABM 1 Demo Dokumen',
    'ABM2_Demo.pdf' => 'ABM 2 Demo Dokumen',
    'PPT_Permohonan_Demo.pdf' => 'PPT Permohonan Demo Dokumen',
];

foreach ($pdfFiles as $fileName => $title) {
    $html = '<html><body style="font-family: DejaVu Sans, sans-serif;">'
        . '<h1 style="margin-bottom:0;">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>'
        . '<p style="margin-top:6px;color:#555;">Fail demo untuk semakan aliran ABM/PPT</p>'
        . '<table width="100%" border="1" cellspacing="0" cellpadding="6">'
        . '<tr><th>Tahun</th><th>Bahagian</th><th>Program</th><th>Kod Objek</th><th>Jumlah</th></tr>'
        . '<tr><td>' . date('Y') . '</td><td>Bahagian Perolehan</td><td>Program A</td><td>KOD001</td><td>350,000.00</td></tr>'
        . '<tr><td>' . date('Y') . '</td><td>Bahagian ICT</td><td>Program B</td><td>KOD210</td><td>520,000.00</td></tr>'
        . '</table>'
        . '</body></html>';

    $dompdf = new Dompdf(['defaultFont' => 'DejaVu Sans']);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    file_put_contents($targetDir . DIRECTORY_SEPARATOR . $fileName, $dompdf->output());
}

echo 'Generated demo files in: ' . $targetDir . PHP_EOL;
