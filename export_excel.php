<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}

require_once 'koneksi.php';

header("Content-Type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=data_buku.xls");

$query = "SELECT * FROM books ORDER BY id ASC";
$result = mysqli_query($koneksi, $query);
?>
<table border="1">
    <thead>
        <tr>
            <th>NO / ID</th>
            <th>KODE BUKU</th>
            <th>JUDUL</th>
            <th>PENULIS</th>
            <th>KATEGORI</th>
            <th>TAHUN TERBIT</th>
            <th>PENERBIT</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)): 
        ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $row['kode_buku']; ?></td>
            <td><?= $row['judul']; ?></td>
            <td><?= $row['penulis']; ?></td>
            <td><?= $row['kategori']; ?></td>
            <td><?= $row['tahun_terbit']; ?></td>
            <td><?= $row['penerbit']; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>