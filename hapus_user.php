<?php

require_once 'koneksi.php';
require_once 'auth.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: users.php?error=User tidak ditemukan.');
    exit;
}

/*
|--------------------------------------------------------------------------
| Jangan izinkan admin menghapus akun yang sedang digunakan
|--------------------------------------------------------------------------
*/

if ($id === (int)($_SESSION['admin_id'] ?? 0)) {
    header('Location: users.php?error=Akun yang sedang digunakan tidak dapat dihapus.');
    exit;
}

try {

    $stmt = $pdo->prepare("
        DELETE FROM admins
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    header('Location: users.php?success=hapus');
    exit;

} catch (PDOException $e) {

    header('Location: users.php?error=Gagal menghapus user.');
    exit;
}