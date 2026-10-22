<?php

require_once 'koneksi.php';
require_once 'auth.php';

requireLogin();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

try {
    $stmt = $pdo->query("
        SELECT id, username
        FROM admins
        ORDER BY id DESC
    ");

    $users = $stmt->fetchAll();

} catch (PDOException $e) {
    $users = [];
    $error = 'Gagal mengambil data user.';
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Terdaftar - Perpus API</title>

    <link rel="stylesheet" href="style.css">

    <script src="https://unpkg.com/lucide@latest"></script>

    <style>

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 24px;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .page-title-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, #7f1d1d, #991b1b);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(127, 29, 29, .18);
        }

        .page-title h1 {
            margin: 0;
            font-size: 28px;
        }

        .page-title p {
            margin: 5px 0 0;
        }

        .users-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #f1e4e4;
            box-shadow: 0 10px 30px rgba(76, 29, 29, .06);
            overflow: hidden;
        }

        .users-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f3e8e8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .users-card-header h2 {
            margin: 0;
            font-size: 18px;
            color: #3f1d1d;
        }

        .users-card-header p {
            margin: 5px 0 0;
            font-size: 13px;
            color: #8b7777;
        }

        .user-count {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #fff1f2;
            color: #9f1239;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th {
            padding: 15px 24px;
            text-align: left;
            font-size: 12px;
            color: #7f6b6b;
            text-transform: uppercase;
            letter-spacing: .04em;
            background: #fffafa;
            border-bottom: 1px solid #f3e8e8;
        }

        .users-table td {
            padding: 16px 24px;
            border-bottom: 1px solid #f5eeee;
            color: #4b3434;
            font-size: 14px;
        }

        .users-table tbody tr {
            transition: .2s ease;
        }

        .users-table tbody tr:hover {
            background: #fffafa;
        }

        .number {
            width: 70px;
            color: #9a8585 !important;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #991b1b, #be123c);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            flex-shrink: 0;
        }

        .username {
            font-weight: 700;
            color: #3f1d1d;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 11px;
            border-radius: 999px;
            background: #fff7ed;
            color: #9a3412;
            font-size: 12px;
            font-weight: 700;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .icon-button {
            width: 38px;
            height: 38px;
            border: none;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            transition: .2s ease;
        }

        .edit-button {
            background: #fff7ed;
            color: #c2410c;
        }

        .edit-button:hover {
            background: #ffedd5;
            transform: translateY(-1px);
        }

        .delete-button {
            background: #fff1f2;
            color: #be123c;
        }

        .delete-button:hover {
            background: #ffe4e6;
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #8b7777;
        }

        .empty-state-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            margin: 0 auto 15px;
            background: #fff1f2;
            color: #9f1239;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert {
            border-radius: 12px;
            padding: 13px 16px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }

        @media (max-width: 700px) {

            .page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .users-card-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .users-table th,
            .users-table td {
                padding: 13px 15px;
            }

        }

    </style>
</head>

<body>

<div class="app">

    <?php include 'sidebar.php'; ?>

    <main class="main-content">

        <div class="page-header">

            <div class="page-title">

                <div class="page-title-icon">
                    <i data-lucide="users"></i>
                </div>

                <div>
                    <h1>User Terdaftar</h1>
                    <p class="muted">
                        Kelola akun administrator perpustakaan.
                    </p>
                </div>

            </div>

            <a href="tambah_user.php" class="btn btn-primary">
                <i data-lucide="user-plus" size="18"></i>
                Tambah User
            </a>

        </div>

        <?php if ($success === 'tambah'): ?>

            <div class="alert alert-success">
                <i data-lucide="circle-check" size="17"></i>
                User berhasil ditambahkan.
            </div>

        <?php elseif ($success === 'edit'): ?>

            <div class="alert alert-success">
                <i data-lucide="circle-check" size="17"></i>
                Data user berhasil diperbarui.
            </div>

        <?php elseif ($success === 'hapus'): ?>

            <div class="alert alert-success">
                <i data-lucide="circle-check" size="17"></i>
                User berhasil dihapus.
            </div>

        <?php endif; ?>


        <?php if ($error): ?>

            <div class="alert alert-danger">
                <i data-lucide="circle-alert" size="17"></i>
                <?= e($error) ?>
            </div>

        <?php endif; ?>


        <section class="users-card">

            <div class="users-card-header">

                <div>
                    <h2>Daftar Administrator</h2>
                    <p>
                        Semua akun yang dapat mengakses panel admin.
                    </p>
                </div>

                <div class="user-count">
                    <i data-lucide="users" size="15"></i>
                    <?= count($users) ?> User
                </div>

            </div>


            <?php if (!empty($users)): ?>

                <div class="table-wrapper">

                    <table class="users-table">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($users as $index => $user): ?>

                            <tr>

                                <td class="number">
                                    <?= $index + 1 ?>
                                </td>

                                <td>

                                    <div class="user-info">

                                        <div class="user-avatar">
                                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                        </div>

                                        <div class="username">
                                            <?= e($user['username']) ?>
                                        </div>

                                    </div>

                                </td>

                                <td>

                                    <span class="role-badge">
                                        <i data-lucide="shield-check" size="14"></i>
                                        Administrator
                                    </span>

                                </td>

                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="edit_user.php?id=<?= (int)$user['id'] ?>"
                                            class="icon-button edit-button"
                                            title="Edit User"
                                        >
                                            <i data-lucide="pencil" size="17"></i>
                                        </a>

                                        <?php if ((int)$user['id'] !== (int)($_SESSION['admin_id'] ?? 0)): ?>

                                            <a
                                                href="hapus_user.php?id=<?= (int)$user['id'] ?>"
                                                class="icon-button delete-button"
                                                title="Hapus User"
                                                onclick="return confirm('Yakin ingin menghapus user ini?')"
                                            >
                                                <i data-lucide="trash-2" size="17"></i>
                                            </a>

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <div class="empty-state-icon">
                        <i data-lucide="users" size="28"></i>
                    </div>

                    <strong>Belum ada user</strong>

                    <p>
                        Silakan tambahkan akun administrator baru.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    </main>

</div>


<script>
    lucide.createIcons();
</script>

</body>
</html>