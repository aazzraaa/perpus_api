<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

$query = "SELECT * FROM books ORDER BY id DESC";
$result = mysqli_query($koneksi, $query);

$books = array();

while ($row = mysqli_fetch_assoc($result)) {
    $books[] = $row;
}

echo json_encode($books);
?>