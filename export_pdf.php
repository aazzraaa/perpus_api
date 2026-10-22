<?php
require_once 'koneksi.php';
require_once 'auth.php';
requireLogin();

$autoload = __DIR__ . '/vendor/autoload.php';

if (!file_exists($autoload)) {
    die('Library PDF belum terpasang. Buka CMD di folder project lalu jalankan: composer install');
}

require_once $autoload;

use Dompdf\Dompdf;
use Dompdf\Options;

$books = $pdo->query(
    'SELECT id, kode_buku, judul, penulis, kategori, tahun_terbit, penerbit
     FROM books ORDER BY id ASC'
)->fetchAll();

function pdfEscape($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$html = '
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
h1 { text-align: center; font-size: 17px; margin-bottom: 4px; }
p { text-align: center; margin-top: 0; color: #555; }
table { width: 100%; border-collapse: collapse; margin-top: 18px; }
th, td { border: 1px solid #444; padding: 6px 5px; vertical-align: top; }
th { background: #f0f0f0; text-align: center; font-weight: bold; }
.center { text-align: center; }
.footer { margin-top: 15px; text-align: right; font-size: 9px; color: #666; }
</style>
</head>
<body>
<h1>LAPORAN DATA BUKU PERPUSTAKAAN</h1>
<p>Perpus API - SMK PGRI 2 Ponorogo</p>
<table>
<thead>
<tr>
<th>NO / ID</th>
<th>KODE</th>
<th>JUDUL</th>
<th>PENULIS</th>
<th>KATEGORI</th>
<th>TAHUN</th>
<th>PENERBIT</th>
</tr>
</thead>
<tbody>';

foreach ($books as $book) {
    $html .= '<tr>';
    $html .= '<td class="center">' . pdfEscape($book['id']) . '</td>';
    $html .= '<td>' . pdfEscape($book['kode_buku']) . '</td>';
    $html .= '<td>' . pdfEscape($book['judul']) . '</td>';
    $html .= '<td>' . pdfEscape($book['penulis']) . '</td>';
    $html .= '<td>' . pdfEscape($book['kategori']) . '</td>';
    $html .= '<td class="center">' . pdfEscape($book['tahun_terbit']) . '</td>';
    $html .= '<td>' . pdfEscape($book['penerbit']) . '</td>';
    $html .= '</tr>';
}

$html .= '
</tbody>
</table>
<div class="footer">Dicetak: ' . date('d-m-Y H:i') . '</div>
</body>
</html>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream('laporan_data_buku.pdf', ['Attachment' => true]);
exit;
?>