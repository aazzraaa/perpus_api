<?php

require_once 'koneksi.php';
require_once 'auth.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: users.php?error=User tidak ditemukan.');
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, username
    FROM admins
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$user = $stmt->fetch();

if (!$user) {
    header('Location: users.php?error=User tidak ditemukan.');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirmation = $_POST['password_confirmation'] ?? '';

    if ($username === '') {

        $error = 'Username wajib diisi.';

    } elseif (strlen($username) < 3) {

        $error = 'Username minimal 3 karakter.';

    } else {

        try {

            $check = $pdo->prepare("
                SELECT id
                FROM admins
                WHERE username = ?
                AND id != ?
                LIMIT 1
            ");

            $check->execute([
                $username,
                $id
            ]);

            if ($check->fetch()) {

                $error = 'Username tersebut sudah digunakan.';

            } elseif ($password !== '' && strlen($password) < 6) {

                $error = 'Password minimal 6 karakter.';

            } elseif ($password !== '' && $password !== $password_confirmation) {

                $error = 'Konfirmasi password tidak sama.';

            } else {

                if ($password !== '') {

                    $hashedPassword = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $update = $pdo->prepare("
                        UPDATE admins
                        SET username = ?, password = ?
                        WHERE id = ?
                    ");

                    $update->execute([
                        $username,
                        $hashedPassword,
                        $id
                    ]);

                } else {

                    $update = $pdo->prepare("
                        UPDATE admins
                        SET username = ?
                        WHERE id = ?
                    ");

                    $update->execute([
                        $username,
                        $id
                    ]);
                }

                if ((int)($_SESSION['admin_id'] ?? 0) === $id) {
                    $_SESSION['admin_username'] = $username;
                }

                header('Location: users.php?success=edit');
                exit;
            }

        } catch (PDOException $e) {

            $error = 'Gagal memperbarui user.';
        }
    }

    $user['username'] = $username;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit User - Perpus API</title>

    <link rel="stylesheet" href="style.css">

    <script src="https://unpkg.com/lucide@latest"></script>

    <style>

        .form-page {
            max-width: 760px;
        }

        .page-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 25px;
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

        .page-header h1 {
            margin: 0;
            font-size: 28px;
        }

        .page-header p {
            margin: 5px 0 0;
        }

        .form-card {
            background: white;
            border: 1px solid #f1e4e4;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(76, 29, 29, .06);
            padding: 28px;
        }

        .user-preview {
            display: flex;
            align-items: center;
            gap: 13px;
            background: #fffafa;
            border: 1px solid #f3e8e8;
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 24px;
        }

        .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 13px;
            background: linear-gradient(135deg, #991b1b, #be123c);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }

        .user-preview strong {
            display: block;
            color: #3f1d1d;
        }

        .user-preview span {
            font-size: 12px;
            color: #8b7777;
        }

        .form-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #3f1d1d;
            margin-bottom: 22px;
        }

        .form-section-title h2 {
            font-size: 18px;
            margin: 0;
        }

        .form-section-title i {
            color: #991b1b;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #4b3434;
        }

        .form-group input {
            width: 100%;
            box-sizing: border-box;
            padding: 13px 15px;
            border: 1px solid #eadada;
            border-radius: 11px;
            background: #fffafa;
            color: #3f1d1d;
            font-size: 14px;
            outline: none;
            transition: .2s ease;
        }

        .form-group input:focus {
            border-color: #991b1b;
            background: white;
            box-shadow: 0 0 0 4px rgba(153, 27, 27, .08);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            padding-right: 48px;
        }

        .toggle-password {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            cursor: pointer;
            color: #8b7777;
            width: 35px;
            height: 35px;
            border-radius: 8px;
        }

        .toggle-password:hover {
            background: #fff1f2;
            color: #991b1b;
        }

        .form-help {
            margin-top: 7px;
            font-size: 12px;
            color: #8b7777;
        }

        .password-note {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 22px;
            font-size: 13px;
            display: flex;
            gap: 9px;
            align-items: flex-start;
        }

        .alert-danger {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #be123c;
            border-radius: 12px;
            padding: 13px 15px;
            margin-bottom: 22px;
            font-size: 14px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 10px;
            border-top: 1px solid #f3e8e8;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            padding: 11px 16px;
            border-radius: 10px;
            background: #f8f4f4;
            color: #624d4d;
            font-size: 14px;
            font-weight: 700;
        }

        .btn-secondary:hover {
            background: #f1e8e8;
        }

        @media (max-width: 600px) {

            .form-card {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .form-actions a,
            .form-actions button {
                width: 100%;
                justify-content: center;
                box-sizing: border-box;
            }

        }

    </style>

</head>

<body>

<div class="app">

    <?php include 'sidebar.php'; ?>

    <main class="main-content">

        <div class="form-page">

            <div class="page-header">

                <div class="page-title-icon">
                    <i data-lucide="user-cog"></i>
                </div>

                <div>
                    <h1>Edit User</h1>
                    <p class="muted">
                        Perbarui informasi akun administrator.
                    </p>
                </div>

            </div>


            <div class="form-card">

                <div class="user-preview">

                    <div class="user-avatar">
                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                    </div>

                    <div>

                        <strong>
                            <?= e($user['username']) ?>
                        </strong>

                        <span>
                            Administrator
                        </span>

                    </div>

                </div>


                <?php if ($error): ?>

                    <div class="alert-danger">
                        <i data-lucide="circle-alert" size="17"></i>
                        <?= e($error) ?>
                    </div>

                <?php endif; ?>


                <div class="form-section-title">

                    <i data-lucide="user-cog"></i>

                    <h2>Informasi Akun</h2>

                </div>


                <form method="post" autocomplete="off">

                    <div class="form-group">

                        <label for="username">
                            Username
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?= e($user['username']) ?>"
                            required
                        >

                    </div>


                    <div class="password-note">

                        <i data-lucide="info" size="17"></i>

                        <span>
                            Kosongkan password jika tidak ingin mengubah password user.
                        </span>

                    </div>


                    <div class="form-group">

                        <label for="password">
                            Password Baru
                        </label>

                        <div class="input-wrapper">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Masukkan password baru"
                            >

                            <button
                                type="button"
                                class="toggle-password"
                                onclick="togglePassword('password', this)"
                            >
                                <i data-lucide="eye" size="18"></i>
                            </button>

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="password_confirmation">
                            Konfirmasi Password Baru
                        </label>

                        <div class="input-wrapper">

                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Ulangi password baru"
                            >

                            <button
                                type="button"
                                class="toggle-password"
                                onclick="togglePassword('password_confirmation', this)"
                            >
                                <i data-lucide="eye" size="18"></i>
                            </button>

                        </div>

                    </div>


                    <div class="form-actions">

                        <a href="users.php" class="btn-secondary">
                            <i data-lucide="arrow-left" size="17"></i>
                            Kembali
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" size="17"></i>
                            Simpan Perubahan
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </main>

</div>


<script>

    lucide.createIcons();

    function togglePassword(id, button) {

        const input = document.getElementById(id);

        if (input.type === 'password') {

            input.type = 'text';

            button.innerHTML = '<i data-lucide="eye-off" size="18"></i>';

        } else {

            input.type = 'password';

            button.innerHTML = '<i data-lucide="eye" size="18"></i>';
        }

        lucide.createIcons();
    }

</script>

</body>
</html>