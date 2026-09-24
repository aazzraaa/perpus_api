<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}

require_once 'koneksi.php';

 $username_saat_ini = $_SESSION['admin_user'] ?? '';
 $foto_saat_ini     = $_SESSION['admin_foto'] ?? '';
 $sukses = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_baru = trim(mysqli_real_escape_string($koneksi, $_POST['username']));
    $password_baru = trim($_POST['password']);

    if (empty($username_baru)) {
        $error = "Username tidak boleh kosong!";
    } else {
        // Cari nama tabel akun secara aman tanpa memicu fatal error
        $tabel = ''; $daftar_pilihan_tabel = ['admin', 'users', 'user', 'tb_admin', 'tb_user', 'akun', 'admins'];

        foreach ($daftar_pilihan_tabel as $nama_tabel) {
            try {
                $cek = mysqli_query($koneksi, "SELECT 1 FROM `$nama_tabel` LIMIT 1");
                if ($cek !== false) {
                    $tabel = $nama_tabel;
                    break;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        if (empty($tabel)) {
            try {
                $res_tables = mysqli_query($koneksi, "SHOW TABLES");
                if ($res_tables && mysqli_num_rows($res_tables) > 0) {
                    $row = mysqli_fetch_array($res_tables);
                    $tabel = $row[0];
                }
            } catch (Exception $e) {
                $tabel = '';
            }
        }

        if (empty($tabel)) {
            $error = "Tabel data akun/admin tidak ditemukan di database!";
        } else {
            $nama_foto_simpan = $foto_saat_ini;

            // Proses Upload Foto Profil
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $file_tmp      = $_FILES['foto']['tmp_name'];
                $file_name     = $_FILES['foto']['name'];
                $file_ext      = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $ext_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($file_ext, $ext_diizinkan)) {
                    $nama_foto_simpan = time() . '_' . uniqid() . '.' . $file_ext;
                    $tujuan_upload = 'uploads/' . $nama_foto_simpan;

                    if (!is_dir('uploads')) {
                        mkdir('uploads', 0777, true);
                    }

                    if (move_uploaded_file($file_tmp, $tujuan_upload)) {
                        if (!empty($foto_saat_ini) && file_exists('uploads/' . $foto_saat_ini)) {
                            unlink('uploads/' . $foto_saat_ini);
                        }
                    } else {
                        $error = "Gagal mengunggah foto profil.";
                    }
                } else {
                    $error = "Format foto harus JPG, JPEG, PNG, atau WEBP!";
                }
            }

            if (empty($error)) {
                // Tambahkan kolom 'foto' secara aman jika belum ada
                try {
                    @mysqli_query($koneksi, "ALTER TABLE `$tabel` ADD `foto` VARCHAR(255) NULL");
                } catch (Exception $e) {
                    // Abaikan error jika kolom 'foto' sudah ada
                }

                // Query Update Data — Prepared Statement
                if (!empty($password_baru)) {
                    // Di-hash agar kompatibel dengan password_verify() di login.php
                    $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
                    $query = "UPDATE `$tabel` SET `username` = ?, `password` = ?, `foto` = ? WHERE `username` = ?";
                    $stmt_update = mysqli_prepare($koneksi, $query);
                    mysqli_stmt_bind_param($stmt_update, "ssss", $username_baru, $password_hashed, $nama_foto_simpan, $username_saat_ini);
                } else {
                    $query = "UPDATE `$tabel` SET `username` = ?, `foto` = ? WHERE `username` = ?";
                    $stmt_update = mysqli_prepare($koneksi, $query);
                    mysqli_stmt_bind_param($stmt_update, "sss", $username_baru, $nama_foto_simpan, $username_saat_ini);
                }

                if (mysqli_stmt_execute($stmt_update)) {
                    // Update Session
                    $_SESSION['admin_user'] = $username_baru;
                    $_SESSION['admin_foto'] = $nama_foto_simpan;

                    $username_saat_ini = $username_baru;
                    $foto_saat_ini     = $nama_foto_simpan;
                    $sukses = "Profil dan foto berhasil diperbarui!";
                } else {
                    $error = "Gagal memperbarui profil: " . mysqli_error($koneksi);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun — Admin Perpustakaan</title>
    <style>
        /* Semua class berprefix pg- dan di-scope di .content-body agar aman dari style.css */
        .content-body .pg-page-header {
            margin-bottom: 22px;
            animation: pgFadeUp .5s ease both;
        }
        .content-body .pg-title {
            font-size: 23px; font-weight: 700;
            background: linear-gradient(90deg, #4a0812, #8f1226, #c9184a);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex; align-items: center; gap: 12px;
        }
        .content-body .pg-title svg {
            width: 27px; height: 27px; color: #8f1226; -webkit-text-fill-color: #8f1226;
        }
        .content-body .pg-title small {
            display: block; font-size: 12px; font-weight: 500;
            -webkit-text-fill-color: #a29294; letter-spacing: .5px; margin-top: 2px;
        }

        /* ===== GRID 2 KOLOM ===== */
        .content-body .pg-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 20px;
            max-width: 900px;
            align-items: start;
        }

        /* ===== KARTU PROFIL (KIRI) ===== */
        .content-body .pg-profile {
            background: linear-gradient(160deg, #3d060e 0%, #5c0a14 55%, #7a0e1c 100%);
            border-radius: 18px;
            padding: 30px 24px 26px;
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 14px 34px rgba(61,6,14,.35);
            animation: pgFadeUp .5s .1s ease both;
        }
        .content-body .pg-profile::before {
            content: ""; position: absolute;
            top: -55px; right: -55px;
            width: 170px; height: 170px;
            background: radial-gradient(circle, rgba(233,196,106,.2), transparent 70%);
            border-radius: 50%;
        }

        .content-body .pg-avatar-wrap { position: relative; width: 118px; margin: 0 auto 14px; }
        .content-body .pg-avatar {
            width: 118px; height: 118px; border-radius: 50%;
            background: linear-gradient(135deg, #c9184a, #8f1226);
            color: #fff; font-size: 44px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            border: 4px solid rgba(255,255,255,.25);
            outline: 3px solid rgba(233,196,106,.55);
            overflow: hidden;
            box-shadow: 0 10px 26px rgba(0,0,0,.4);
            animation: pgPop .6s .25s cubic-bezier(.34,1.56,.64,1) both;
        }
        @keyframes pgPop {
            from { opacity: 0; transform: scale(.5); }
            to   { opacity: 1; transform: scale(1); }
        }
        .content-body .pg-avatar img {
            width: 100%; height: 100%;
            object-fit: cover; display: block;
        }

        /* Tombol kamera di pojok avatar */
        .content-body .pg-avatar-btn {
            position: absolute; right: 2px; bottom: 2px;
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, #e9c46a, #d4a13d);
            border: 3px solid #5c0a14;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,.4);
            transition: transform .25s;
        }
        .content-body .pg-avatar-btn:hover { transform: scale(1.12) rotate(8deg); }
        .content-body .pg-avatar-btn svg { width: 17px; height: 17px; color: #4a3208; }

        .content-body .pg-profile-name {
            font-size: 18px; font-weight: 600; margin-bottom: 4px;
            word-break: break-word;
        }
        .content-body .pg-profile-role {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: 11px; font-weight: 600;
            letter-spacing: 1.5px; text-transform: uppercase;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.22);
            padding: 6px 14px; border-radius: 99px;
            margin-bottom: 18px;
        }
        .content-body .pg-profile-role svg { width: 13px; height: 13px; color: #ffd8a8; }

        /* Nama file foto terpilih */
        .content-body .pg-file-name {
            display: none;
            font-size: 12px; color: #ffd8a8;
            background: rgba(255,255,255,.1);
            border: 1px dashed rgba(233,196,106,.45);
            border-radius: 9px;
            padding: 8px 12px; margin-top: 12px;
            max-width: 100%; overflow: hidden;
            text-overflow: ellipsis; white-space: nowrap;
            animation: pgFadeUp .3s ease both;
        }
        .content-body .pg-file-name.show { display: block; }

        .content-body .pg-profile-hint {
            font-size: 11.5px; color: rgba(255,255,255,.55);
            margin-top: 16px; line-height: 1.6;
        }

        /* ===== KARTU FORM (KANAN) ===== */
        .content-body .pg-card {
            background: #fff; border-radius: 18px;
            border: 1px solid #f7ebeb;
            box-shadow: 0 8px 24px rgba(74,8,18,.08);
            overflow: hidden;
            animation: pgFadeUp .5s .2s ease both;
        }
        .content-body .pg-card-head {
            background: linear-gradient(135deg, #8f1226 0%, #5c0a14 100%);
            padding: 18px 26px;
            display: flex; align-items: center; gap: 13px;
            position: relative; overflow: hidden;
        }
        .content-body .pg-card-head::before {
            content: ""; position: absolute;
            top: -40px; right: -40px;
            width: 130px; height: 130px;
            background: rgba(255,255,255,.1);
            border-radius: 50%;
        }
        .content-body .pg-card-head-icon {
            width: 42px; height: 42px; border-radius: 12px;
            background: rgba(255,255,255,.16);
            backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .content-body .pg-card-head-icon svg { width: 21px; height: 21px; color: #ffd8a8; }
        .content-body .pg-card-head h2 { font-size: 16px; font-weight: 600; color: #fff; position: relative; }
        .content-body .pg-card-head p  { font-size: 12px; color: rgba(255,255,255,.72); margin-top: 1px; position: relative; }

        .content-body .pg-card-body { padding: 26px; }

        /* ===== ALERT ===== */
        .content-body .pg-alert {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 13px 15px; border-radius: 11px;
            font-size: 13.5px; line-height: 1.5;
            margin-bottom: 20px;
            animation: pgAlertIn .4s ease;
        }
        .content-body .pg-alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
        .content-body .pg-alert-error {
            background: #fdecea; color: #b3261e;
            border: 1px solid #f5c6c2; border-left: 4px solid #d93025;
            animation: pgAlertIn .4s ease, pgShake .45s .15s ease;
        }
        .content-body .pg-alert-success {
            background: #e8f6ee; color: #1e6b3a;
            border: 1px solid #c3e6cb; border-left: 4px solid #2e9e5b;
        }
        @keyframes pgAlertIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: none; }
        }
        @keyframes pgShake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-6px); } 40% { transform: translateX(6px); }
            60% { transform: translateX(-4px); } 80% { transform: translateX(4px); }
        }

        /* ===== FORM ===== */
        .content-body .pg-field { margin-bottom: 20px; animation: pgFadeUp .5s ease both; }
        .content-body .pg-field:nth-of-type(1) { animation-delay: .25s; }
        .content-body .pg-field:nth-of-type(2) { animation-delay: .35s; }

        .content-body .pg-field label {
            display: flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 500; color: #4a3f40;
            margin-bottom: 8px; letter-spacing: .3px;
        }
        .content-body .pg-field label .opt {
            font-size: 11px; font-weight: 500; color: #a29294;
            background: #f7f2f2; padding: 2px 9px; border-radius: 99px;
        }

        .content-body .pg-input-wrap { position: relative; }
        .content-body .pg-input-wrap > svg.pg-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            width: 18px; height: 18px;
            color: #b09a9c; pointer-events: none;
            transition: color .3s;
        }
        .content-body .pg-input-wrap:focus-within > svg.pg-icon { color: #8f1226; }

        .content-body .pg-field input[type="text"],
        .content-body .pg-field input[type="password"] {
            width: 100%;
            padding: 12px 46px 12px 44px;
            border: 1.5px solid #e6dcdc; border-radius: 12px;
            font-size: 14px; font-family: inherit; color: #2b0509;
            background: #faf7f7; outline: none;
            transition: border-color .3s, box-shadow .3s, background .3s;
        }
        .content-body .pg-field input::placeholder { color: #c2b2b4; }
        .content-body .pg-field input:hover  { border-color: #d3bbbb; }
        .content-body .pg-field input:focus {
            border-color: #8f1226; background: #fff;
            box-shadow: 0 0 0 4px rgba(143,18,38,.12);
        }

        .content-body .pg-toggle-pass {
            position: absolute; right: 8px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            padding: 8px; border-radius: 8px;
            display: flex; color: #b09a9c;
            transition: color .3s, background .3s;
        }
        .content-body .pg-toggle-pass:hover { color: #8f1226; background: rgba(143,18,38,.07); }
        .content-body .pg-toggle-pass svg { width: 18px; height: 18px; }

        .content-body .pg-hint {
            font-size: 11.5px; color: #a29294; margin-top: 6px;
            display: flex; align-items: center; gap: 5px;
        }
        .content-body .pg-hint svg { width: 12px; height: 12px; flex-shrink: 0; }

        /* Meter kekuatan password */
        .content-body .pg-strength { display: flex; gap: 6px; margin-top: 9px; }
        .content-body .pg-strength-bar {
            flex: 1; height: 5px; border-radius: 99px;
            background: #ece3e3; transition: background .35s ease;
        }
        .content-body .pg-strength-label {
            font-size: 11.5px; margin-top: 6px; color: #a29294; min-height: 15px;
        }

        /* ===== TOMBOL ===== */
        .content-body .pg-actions {
            display: flex; gap: 12px; align-items: center;
            margin-top: 24px; padding-top: 22px;
            border-top: 1px solid #f7ebeb;
            animation: pgFadeUp .5s .45s ease both;
        }
        .content-body .pg-btn {
            display: inline-flex; align-items: center; gap: 9px;
            padding: 12px 24px; border-radius: 12px;
            font-size: 14px; font-weight: 600; font-family: inherit;
            text-decoration: none; cursor: pointer; border: none;
            transition: transform .25s, box-shadow .25s, background .25s, color .25s;
            position: relative; overflow: hidden;
        }
        .content-body .pg-btn svg { width: 17px; height: 17px; transition: transform .3s; }

        .content-body .pg-btn-save {
            background: linear-gradient(135deg, #c9184a, #8f1226 60%, #5c0a14);
            color: #fff;
            box-shadow: 0 8px 18px rgba(143,18,38,.35);
        }
        .content-body .pg-btn-save:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(143,18,38,.45); }
        .content-body .pg-btn-save:active { transform: translateY(0) scale(.98); }
        .content-body .pg-btn-save:disabled { opacity: .75; cursor: not-allowed; transform: none; }
        .content-body .pg-btn-save::before {
            content: ""; position: absolute; top: 0; left: -100%;
            width: 60%; height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,.35), transparent);
            transform: skewX(-20deg); transition: left .5s;
        }
        .content-body .pg-btn-save:hover::before { left: 130%; }

        .content-body .pg-btn-batal {
            background: #fff; color: #8f1226;
            border: 1.5px solid #e6dcdc;
        }
        .content-body .pg-btn-batal:hover {
            background: #fdf3f3; border-color: #c9184a;
            transform: translateY(-2px);
        }

        .content-body .pg-spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,.4); border-top-color: #fff;
            border-radius: 50%; display: inline-block;
            animation: pgSpin .7s linear infinite;
        }
        @keyframes pgSpin { to { transform: rotate(360deg); } }

        @keyframes pgFadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: none; }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 820px) {
            .content-body .pg-grid { grid-template-columns: 1fr; }
            .content-body .pg-card-body { padding: 20px; }
            .content-body .pg-actions { flex-direction: column-reverse; }
            .content-body .pg-btn { width: 100%; justify-content: center; }
            .content-body .pg-title { font-size: 19px; }
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar Menu -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Topbar (Profile Admin) -->
            <?php include 'topbar.php'; ?>

            <!-- Content Body -->
            <div class="content-body">
                <div class="pg-page-header">
                    <h2 class="pg-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                        <span>Pengaturan Akun<small>Kelola profil, foto, dan keamanan akun admin</small></span>
                    </h2>
                </div>

                <form action="" method="POST" enctype="multipart/form-data" id="pgForm">
                    <div class="pg-grid">

                        <!-- ===== KARTU PROFIL (KIRI) ===== -->
                        <div class="pg-profile">
                            <div class="pg-avatar-wrap">
                                <div class="pg-avatar" id="pgPreview">
                                    <?php if (!empty($foto_saat_ini) && file_exists('uploads/' . $foto_saat_ini)): ?>
                                        <img src="uploads/<?= htmlspecialchars($foto_saat_ini); ?>" alt="Foto Profil">
                                    <?php else: ?>
                                        <?= strtoupper(substr($username_saat_ini, 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <!-- Tombol kamera: membuka dialog pilih file -->
                                <label class="pg-avatar-btn" for="pgFoto" title="Ganti foto profil">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                        <circle cx="12" cy="13" r="4"/>
                                    </svg>
                                </label>
                            </div>

                            <div class="pg-profile-name"><?= htmlspecialchars($username_saat_ini); ?></div>
                            <div class="pg-profile-role">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                    <polyline points="9 12 11 14 15 10"/>
                                </svg>
                                Administrator
                            </div>

                            <!-- Nama file yang dipilih (muncul via JS) -->
                            <div class="pg-file-name" id="pgFileName"></div>

                            <!-- Input file asli (disembunyikan, dipicu tombol kamera) -->
                            <input type="file" name="foto" id="pgFoto" accept="image/png,image/jpeg,image/webp" style="display: none;">

                            <p class="pg-profile-hint">
                                Format: JPG, JPEG, PNG, WEBP.<br>
                                Foto lama otomatis terganti saat disimpan.
                            </p>
                        </div>

                        <!-- ===== KARTU FORM (KANAN) ===== -->
                        <div class="pg-card">
                            <div class="pg-card-head">
                                <div class="pg-card-head-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2>Detail Akun</h2>
                                    <p>Perbarui username dan password admin</p>
                                </div>
                            </div>

                            <div class="pg-card-body">
                                <?php if ($sukses): ?>
                                    <div class="pg-alert pg-alert-success">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                            <polyline points="22 4 12 14.01 9 11.01"/>
                                        </svg>
                                        <span><?= $sukses; ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($error): ?>
                                    <div class="pg-alert pg-alert-error">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                        </svg>
                                        <span><?= $error; ?></span>
                                    </div>
                                <?php endif; ?>

                                <!-- Username -->
                                <div class="pg-field">
                                    <label>Username / Nama Admin</label>
                                    <div class="pg-input-wrap">
                                        <svg class="pg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                            <circle cx="12" cy="7" r="4"/>
                                        </svg>
                                        <input type="text" name="username" value="<?= htmlspecialchars($username_saat_ini); ?>" required maxlength="50" autocomplete="off">
                                    </div>
                                </div>

                                <!-- Password -->
                                <div class="pg-field">
                                    <label>Password Baru <span class="opt">Opsional</span></label>
                                    <div class="pg-input-wrap">
                                        <svg class="pg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                        <input type="password" name="password" id="pgPassword" placeholder="Masukkan password baru" autocomplete="new-password">
                                        <button type="button" class="pg-toggle-pass" onclick="pgTogglePass()" aria-label="Tampilkan password">
                                            <svg id="pgEyeOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            <svg id="pgEyeOff" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                                <line x1="1" y1="1" x2="23" y2="23"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Meter kekuatan password -->
                                    <div class="pg-strength">
                                        <span class="pg-strength-bar" id="pgBar1"></span>
                                        <span class="pg-strength-bar" id="pgBar2"></span>
                                        <span class="pg-strength-bar" id="pgBar3"></span>
                                        <span class="pg-strength-bar" id="pgBar4"></span>
                                    </div>
                                    <div class="pg-strength-label" id="pgStrengthLabel">Kosongkan jika tidak ingin mengubah password</div>
                                </div>

                                <!-- Tombol aksi -->
                                <div class="pg-actions">
                                    <button type="submit" id="pgBtnSave" class="pg-btn pg-btn-save">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                            <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                                        </svg>
                                        Simpan Perubahan
                                    </button>
                                    <a href="dashboard.php" class="pg-btn pg-btn-batal">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                        Batal
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // ====== Preview foto sebelum upload ======
        document.getElementById('pgFoto').addEventListener('change', function () {
            const file = this.files[0];
            const preview  = document.getElementById('pgPreview');
            const fileName = document.getElementById('pgFileName');

            if (!file) {
                fileName.classList.remove('show');
                return;
            }

            // Validasi tipe di sisi browser
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                fileName.textContent = 'Format tidak didukung! Gunakan JPG, PNG, atau WEBP.';
                fileName.style.color = '#ffb3ab';
                fileName.classList.add('show');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview Foto">';
            };
            reader.readAsDataURL(file);

            fileName.textContent = 'Dipilih: ' + file.name;
            fileName.style.color = '#ffd8a8';
            fileName.classList.add('show');
        });

        // ====== Toggle tampilkan / sembunyikan password ======
        function pgTogglePass() {
            const pwd     = document.getElementById('pgPassword');
            const eyeOpen = document.getElementById('pgEyeOpen');
            const eyeOff  = document.getElementById('pgEyeOff');

            if (pwd.type === 'password') {
                pwd.type = 'text';
                eyeOpen.style.display = 'none';
                eyeOff.style.display  = 'block';
            } else {
                pwd.type = 'password';
                eyeOpen.style.display = 'block';
                eyeOff.style.display  = 'none';
            }
        }

        // ====== Meter kekuatan password ======
        document.getElementById('pgPassword').addEventListener('input', function () {
            const val   = this.value;
            const bars  = [
                document.getElementById('pgBar1'),
                document.getElementById('pgBar2'),
                document.getElementById('pgBar3'),
                document.getElementById('pgBar4')
            ];
            const label = document.getElementById('pgStrengthLabel');

            const levels = [
                { text: 'Kosongkan jika tidak ingin mengubah password', color: '#a29294' },
                { text: 'Lemah — tambah panjang atau kombinasi',         color: '#d93025' },
                { text: 'Sedang — cukup baik',                           color: '#e09f3e' },
                { text: 'Kuat — password aman',                          color: '#2e9e5b' },
                { text: 'Sangat kuat — excellent!',                      color: '#1e6b3a' }
            ];

            let score = 0;
            if (val.length >= 6)  score++;
            if (val.length >= 10) score++;
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
            if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) score++;

            bars.forEach((bar, i) => {
                bar.style.background = (val && i < score) ? levels[score].color : '#ece3e3';
            });

            label.textContent = val ? levels[score].text : levels[0].text;
            label.style.color = val ? levels[score].color : '#a29294';
        });

        // ====== Status loading saat submit ======
        document.getElementById('pgForm').addEventListener('submit', function () {
            const btn = document.getElementById('pgBtnSave');
            btn.disabled = true;
            btn.innerHTML = '<span class="pg-spinner"></span> MENYIMPAN...';
        });
    </script>
</body>
</html>