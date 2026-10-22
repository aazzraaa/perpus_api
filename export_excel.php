```php
<?php

require_once 'koneksi.php';
require_once 'auth.php';

requireLogin();

require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

try {

    // Ambil data buku
    $stmt = $pdo->query("
        SELECT
            id,
            kode_buku,
            judul,
            penulis,
            kategori,
            tahun_terbit,
            penerbit
        FROM books
        ORDER BY id ASC
    ");

    $books = $stmt->fetchAll();

    // Buat spreadsheet baru
    $spreadsheet = new Spreadsheet();

    $sheet = $spreadsheet->getActiveSheet();

    // Nama sheet
    $sheet->setTitle('Data Buku');

    // =========================
    // JUDUL
    // =========================

    $sheet->mergeCells('A1:G1');

    $sheet->setCellValue('A1', 'DATA BUKU PERPUSTAKAAN');

    $sheet->getStyle('A1')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getFont()->setSize(16);

    $sheet->getStyle('A1')->getAlignment()->setHorizontal(
        Alignment::HORIZONTAL_CENTER
    );

    $sheet->getStyle('A1')->getAlignment()->setVertical(
        Alignment::VERTICAL_CENTER
    );

    $sheet->getRowDimension(1)->setRowHeight(30);

    // =========================
    // HEADER
    // =========================

    $headers = [
        'A3' => 'NO',
        'B3' => 'KODE BUKU',
        'C3' => 'JUDUL',
        'D3' => 'PENULIS',
        'E3' => 'KATEGORI',
        'F3' => 'TAHUN TERBIT',
        'G3' => 'PENERBIT'
    ];

    foreach ($headers as $cell => $header) {
        $sheet->setCellValue($cell, $header);
    }

    // Style header
    $sheet->getStyle('A3:G3')->getFont()->setBold(true);

    $sheet->getStyle('A3:G3')->getAlignment()->setHorizontal(
        Alignment::HORIZONTAL_CENTER
    );

    $sheet->getStyle('A3:G3')->getAlignment()->setVertical(
        Alignment::VERTICAL_CENTER
    );

    $sheet->getStyle('A3:G3')->getFill()->setFillType(
        Fill::FILL_SOLID
    );

    $sheet->getStyle('A3:G3')->getFill()->getStartColor()->setARGB('FF800000');

    $sheet->getStyle('A3:G3')->getFont()->getColor()->setARGB('FFFFFFFF');

    // =========================
    // ISI DATA
    // =========================

    $row = 4;
    $no = 1;

    foreach ($books as $book) {

        $sheet->setCellValue('A' . $row, $no);
        $sheet->setCellValue('B' . $row, $book['kode_buku']);
        $sheet->setCellValue('C' . $row, $book['judul']);
        $sheet->setCellValue('D' . $row, $book['penulis']);
        $sheet->setCellValue('E' . $row, $book['kategori']);
        $sheet->setCellValue('F' . $row, $book['tahun_terbit']);
        $sheet->setCellValue('G' . $row, $book['penerbit']);

        $row++;
        $no++;
    }

    // =========================
    // BORDER
    // =========================

    if ($row > 4) {

        $lastRow = $row - 1;

        $sheet->getStyle('A3:G' . $lastRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('A3:G' . $lastRow)
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    // =========================
    // ALIGNMENT
    // =========================

    $sheet->getStyle('A4:A' . max(4, $row - 1))
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->getStyle('F4:F' . max(4, $row - 1))
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // =========================
    // LEBAR KOLOM
    // =========================

    $sheet->getColumnDimension('A')->setWidth(8);
    $sheet->getColumnDimension('B')->setWidth(18);
    $sheet->getColumnDimension('C')->setWidth(35);
    $sheet->getColumnDimension('D')->setWidth(25);
    $sheet->getColumnDimension('E')->setWidth(20);
    $sheet->getColumnDimension('F')->setWidth(15);
    $sheet->getColumnDimension('G')->setWidth(25);

    // =========================
    // WRAP TEXT
    // =========================

    if ($row > 4) {
        $sheet->getStyle('A4:G' . ($row - 1))
            ->getAlignment()
            ->setWrapText(true);
    }

    // =========================
    // FREEZE HEADER
    // =========================

    $sheet->freezePane('A4');

    // =========================
    // DOWNLOAD EXCEL
    // =========================

    $filename = 'data_buku_perpustakaan_' . date('Y-m-d_H-i-s') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);

    $writer->save('php://output');

    exit;

} catch (Throwable $e) {

    http_response_code(500);

    echo '<h2>Export Excel gagal</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}