<?php
require_once 'koneksi.php';
require_once 'auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: buku.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT gambar FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Data buku tidak ditemukan.'];
    header('Location: buku.php');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM books WHERE id = ?');
$stmt->execute([$id]);

if (!empty($book['gambar']) && str_starts_with($book['gambar'], 'assets/uploads/')) {
    $file = __DIR__ . '/' . $book['gambar'];
    if (is_file($file)) @unlink($file);
}

$_SESSION['flash'] = ['type' => 'success', 'message' => 'Buku berhasil dihapus.'];
header('Location: buku.php');
exit;
?>