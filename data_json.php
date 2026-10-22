<?php

require_once 'koneksi.php';

header('Content-Type: application/json; charset=utf-8');

try {

    $sql = "
        SELECT
            id,
            kode_buku,
            judul,
            penulis,
            kategori,
            tahun_terbit,
            penerbit,
            gambar
        FROM books
        ORDER BY id DESC
    ";

    $stmt = $pdo->query($sql);

    $books = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $books
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil data buku.',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

}