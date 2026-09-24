<?php

session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}

require_once 'koneksi.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // =====================================================
    // AMBIL DATA FORM
    // =====================================================

    $kode_buku    = trim($_POST['kode_buku'] ?? '');
    $judul        = trim($_POST['judul'] ?? '');
    $penulis      = trim($_POST['penulis'] ?? '');
    $kategori     = trim($_POST['kategori'] ?? '');
    $tahun_terbit = trim($_POST['tahun_terbit'] ?? '');
    $penerbit     = trim($_POST['penerbit'] ?? '');
    $stok         = isset($_POST['stok']) ? (int) $_POST['stok'] : 0;

    // =====================================================
    // VALIDASI DATA WAJIB
    // =====================================================

    if (
        $kode_buku === '' ||
        $judul === '' ||
        $penulis === '' ||
        $kategori === '' ||
        $tahun_terbit === '' ||
        $penerbit === ''
    ) {
        $error = "Semua kolom yang bertanda * wajib diisi.";
    }

    // =====================================================
    // VALIDASI TAHUN
    // =====================================================

    $tahun_sekarang = (int) date('Y');

    if (empty($error)) {

        if (!ctype_digit($tahun_terbit)) {

            $error = "Tahun terbit harus berupa angka.";

        } elseif (
            (int) $tahun_terbit < 1900 ||
            (int) $tahun_terbit > ($tahun_sekarang + 1)
        ) {

            $error = "Tahun terbit tidak valid.";
        }
    }

    // =====================================================
    // VALIDASI STOK
    // =====================================================

    if (empty($error) && $stok < 1) {
        $error = "Stok buku minimal 1.";
    }

    // =====================================================
    // CEK KODE BUKU DUPLIKAT
    // =====================================================

    if (empty($error)) {

        $stmtCheck = mysqli_prepare(
            $koneksi,
            "SELECT id FROM books WHERE kode_buku = ? LIMIT 1"
        );

        if ($stmtCheck) {

            mysqli_stmt_bind_param(
                $stmtCheck,
                "s",
                $kode_buku
            );

            mysqli_stmt_execute($stmtCheck);
            mysqli_stmt_store_result($stmtCheck);

            if (mysqli_stmt_num_rows($stmtCheck) > 0) {

                $error =
                    "Kode buku <b>" .
                    htmlspecialchars(
                        $kode_buku,
                        ENT_QUOTES,
                        'UTF-8'
                    ) .
                    "</b> sudah digunakan.";
            }

            mysqli_stmt_close($stmtCheck);

        } else {

            $error = "Gagal memeriksa kode buku.";
        }
    }

    // =====================================================
    // DEFAULT COVER
    // =====================================================

    $cover_name = 'default_cover.jpg';
    $uploaded_cover = false;

    // =====================================================
    // UPLOAD COVER
    // =====================================================

    if (empty($error)) {

        if (
            isset($_FILES['cover']) &&
            $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            $file = $_FILES['cover'];

            // =================================================
            // CEK ERROR UPLOAD
            // =================================================

            if ($file['error'] !== UPLOAD_ERR_OK) {

                switch ($file['error']) {

                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:

                        $error = "Ukuran file terlalu besar.";
                        break;

                    case UPLOAD_ERR_PARTIAL:

                        $error = "File hanya ter-upload sebagian.";
                        break;

                    case UPLOAD_ERR_NO_TMP_DIR:

                        $error = "Folder temporary upload tidak tersedia.";
                        break;

                    case UPLOAD_ERR_CANT_WRITE:

                        $error = "File gagal ditulis ke server.";
                        break;

                    default:

                        $error =
                            "Terjadi kesalahan saat upload cover.";
                        break;
                }
            }

            // =================================================
            // CEK UKURAN FILE
            // =================================================

            if (empty($error)) {

                $maxSize = 2 * 1024 * 1024;

                if ($file['size'] > $maxSize) {

                    $error =
                        "Ukuran cover maksimal 2 MB.";
                }
            }

            // =================================================
            // CEK MIME TYPE
            // =================================================

            if (empty($error)) {

                $finfo = finfo_open(FILEINFO_MIME_TYPE);

                if (!$finfo) {

                    $error =
                        "Server tidak dapat memeriksa format file.";

                } else {

                    $mimeType = finfo_file(
                        $finfo,
                        $file['tmp_name']
                    );

                    finfo_close($finfo);

                    $allowedMime = [
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/webp' => 'webp'
                    ];

                    if (!isset($allowedMime[$mimeType])) {

                        $error =
                            "Format cover tidak didukung. " .
                            "Gunakan JPG, PNG, atau WEBP.";

                    } else {

                        $file_ext = $allowedMime[$mimeType];

                        // =================================================
                        // BUAT NAMA FILE UNIK
                        // =================================================

                        try {

                            $randomName = bin2hex(
                                random_bytes(5)
                            );

                        } catch (Exception $e) {

                            $randomName =
                                uniqid('', true);
                        }

                        $new_name =
                            date('YmdHis') .
                            '_' .
                            $randomName .
                            '.' .
                            $file_ext;

                        // =================================================
                        // FOLDER UPLOAD
                        // =================================================

                        $upload_dir =
                            __DIR__ . '/uploads/covers/';

                        if (!is_dir($upload_dir)) {

                            if (!mkdir(
                                $upload_dir,
                                0755,
                                true
                            )) {

                                $error =
                                    "Folder upload cover gagal dibuat.";
                            }
                        }

                        // =================================================
                        // PINDAHKAN FILE
                        // =================================================

                        if (empty($error)) {

                            $destination =
                                $upload_dir . $new_name;

                            if (
                                move_uploaded_file(
                                    $file['tmp_name'],
                                    $destination
                                )
                            ) {

                                $cover_name =
                                    $new_name;

                                $uploaded_cover =
                                    true;

                            } else {

                                $error =
                                    "Cover gagal disimpan ke folder uploads.";
                            }
                        }
                    }
                }
            }
        }
    }

    // =====================================================
    // SIMPAN KE DATABASE
    // =====================================================

    if (empty($error)) {

        $sql = "
            INSERT INTO books
            (
                kode_buku,
                judul,
                penulis,
                kategori,
                tahun_terbit,
                penerbit,
                stok,
                cover
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = mysqli_prepare(
            $koneksi,
            $sql
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ssssssis",
                $kode_buku,
                $judul,
                $penulis,
                $kategori,
                $tahun_terbit,
                $penerbit,
                $stok,
                $cover_name
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header(
                    "Location: buku.php?status=success_add"
                );

                exit;

            } else {

                // =================================================
                // HAPUS COVER JIKA DATABASE GAGAL
                // =================================================

                if (
                    $uploaded_cover &&
                    file_exists(
                        __DIR__ .
                        '/uploads/covers/' .
                        $cover_name
                    )
                ) {

                    unlink(
                        __DIR__ .
                        '/uploads/covers/' .
                        $cover_name
                    );
                }

                $error =
                    "Gagal menyimpan data buku: " .
                    mysqli_stmt_error($stmt);

                mysqli_stmt_close($stmt);
            }

        } else {

            $error =
                "Gagal menyiapkan query database: " .
                mysqli_error($koneksi);
        }
    }
}

// ==========================================================
// TAHUN SEKARANG
// ==========================================================

$tahun_sekarang = date('Y');

// ==========================================================
// AMBIL DAFTAR KATEGORI
// ==========================================================

$list_kategori = [];

$res_kat = mysqli_query(
    $koneksi,
    "
    SELECT DISTINCT kategori
    FROM books
    WHERE kategori IS NOT NULL
    AND kategori != ''
    ORDER BY kategori ASC
    "
);

if ($res_kat) {

    while ($row = mysqli_fetch_assoc($res_kat)) {

        if (!empty($row['kategori'])) {

            $list_kategori[] =
                $row['kategori'];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Tambah Buku — Admin Perpustakaan
    </title>

    <style>

        .content-body .tb-page-header {

            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;

            animation:
                tbFadeUp .5s ease both;
        }

        .content-body .tb-title {

            font-size: 23px;
            font-weight: 700;

            background:
                linear-gradient(
                    90deg,
                    #4a0812,
                    #8f1226,
                    #c9184a
                );

            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;

            display: flex;
            align-items: center;
            gap: 12px;
        }

        .content-body .tb-title svg {

            width: 27px;
            height: 27px;

            color: #8f1226;

            -webkit-text-fill-color:
                #8f1226;
        }

        .content-body .tb-title small {

            display: block;

            font-size: 12px;
            font-weight: 500;

            -webkit-text-fill-color:
                #a29294;

            letter-spacing: .5px;

            margin-top: 2px;
        }

        .content-body .tb-card {

            background: #fff;

            border-radius: 18px;

            border:
                1px solid #f7ebeb;

            box-shadow:
                0 8px 24px
                rgba(74, 8, 18, .08);

            max-width: 820px;

            overflow: hidden;

            animation:
                tbFadeUp .5s .1s ease both;
        }

        .content-body .tb-card-head {

            background:
                linear-gradient(
                    135deg,
                    #8f1226 0%,
                    #5c0a14 100%
                );

            padding: 20px 28px;

            display: flex;
            align-items: center;

            gap: 14px;

            position: relative;
            overflow: hidden;
        }

        .content-body .tb-card-head::before {

            content: "";

            position: absolute;

            top: -45px;
            right: -45px;

            width: 150px;
            height: 150px;

            background:
                rgba(255, 255, 255, .1);

            border-radius: 50%;
        }

        .content-body .tb-card-head-icon {

            width: 44px;
            height: 44px;

            border-radius: 13px;

            background:
                rgba(255, 255, 255, .16);

            backdrop-filter:
                blur(4px);

            display: flex;
            align-items: center;
            justify-content: center;

            box-shadow:
                inset 0 1px 0
                rgba(255, 255, 255, .25);

            flex-shrink: 0;
        }

        .content-body
        .tb-card-head-icon svg {

            width: 22px;
            height: 22px;

            color: #ffd8a8;
        }

        .content-body .tb-card-head h2 {

            font-size: 16.5px;
            font-weight: 600;

            color: #fff;
        }

        .content-body .tb-card-head p {

            font-size: 12.5px;

            color:
                rgba(255, 255, 255, .75);

            margin-top: 2px;
        }

        .content-body .tb-card-body {

            padding: 28px;
        }

        .content-body .tb-alert {

            display: flex;
            align-items: flex-start;

            gap: 10px;

            background: #fdecea;

            color: #b3261e;

            border:
                1px solid #f5c6c2;

            border-left:
                4px solid #d93025;

            padding: 13px 15px;

            border-radius: 11px;

            font-size: 13.5px;

            line-height: 1.5;

            margin-bottom: 22px;

            animation:
                tbAlertIn .4s ease,
                tbShake .45s .15s ease;
        }

        .content-body .tb-alert svg {

            width: 18px;
            height: 18px;

            flex-shrink: 0;

            margin-top: 1px;
        }

        .content-body .tb-form-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 18px;
        }

        .content-body .tb-field {

            animation:
                tbFadeUp .5s ease both;
        }

        .content-body .tb-span-2 {

            grid-column:
                span 2;
        }

        .content-body .tb-field label {

            display: flex;
            align-items: center;

            gap: 6px;

            font-size: 13px;

            font-weight: 500;

            color: #4a3f40;

            margin-bottom: 8px;

            letter-spacing: .3px;
        }

        .content-body
        .tb-field
        label .req {

            color: #c9184a;

            font-weight: 700;
        }

        .content-body .tb-input-wrap {

            position: relative;
        }

        .content-body
        .tb-input-wrap >
        svg.tb-icon {

            position: absolute;

            left: 14px;
            top: 50%;

            transform:
                translateY(-50%);

            width: 18px;
            height: 18px;

            color: #b09a9c;

            pointer-events: none;

            transition:
                color .3s;

            z-index: 2;
        }

        .content-body
        .tb-input-wrap:focus-within >
        svg.tb-icon {

            color: #8f1226;
        }

        .content-body
        .tb-field input,
        .content-body
        .tb-field select {

            width: 100%;

            padding:
                12px
                14px
                12px
                44px;

            border:
                1.5px solid #e6dcdc;

            border-radius: 12px;

            font-size: 14px;

            font-family: inherit;

            color: #2b0509;

            background: #faf7f7;

            outline: none;

            transition:
                border-color .3s,
                box-shadow .3s,
                background .3s;

            box-sizing: border-box;
        }

        .content-body
        .tb-field input::placeholder {

            color: #c2b2b4;
        }

        .content-body
        .tb-field input:hover,
        .content-body
        .tb-field select:hover {

            border-color: #d3bbbb;
        }

        .content-body
        .tb-field input:focus,
        .content-body
        .tb-field select:focus {

            border-color: #8f1226;

            background: #fff;

            box-shadow:
                0 0 0 4px
                rgba(143, 18, 38, .12);
        }

        .content-body input.tb-mono {

            font-family:
                'Consolas',
                'Monaco',
                monospace;

            font-weight: 600;

            letter-spacing: .5px;

            text-transform:
                uppercase;
        }

        .content-body .tb-cover-upload {

            display: flex;

            gap: 16px;

            align-items: center;

            background: #faf7f7;

            padding: 12px;

            border-radius: 12px;

            border:
                1.5px dashed #e6dcdc;
        }

        .content-body
        .tb-cover-preview {

            width: 60px;
            height: 80px;

            border-radius: 8px;

            background: #e6dcdc;

            object-fit: cover;

            display: flex;

            align-items: center;
            justify-content: center;

            color: #a29294;

            font-size: 10px;

            font-weight: 600;

            text-align: center;

            flex-shrink: 0;
        }

        .content-body .tb-cover-input {

            flex: 1;
        }

        .content-body
        .tb-cover-input
        input[type="file"] {

            padding:
                8px 10px;

            font-size: 12px;

            background: #fff;

            box-sizing: border-box;
        }

        .content-body .tb-hint {

            font-size: 11.5px;

            color: #a29294;

            margin-top: 6px;

            display: flex;

            align-items: center;

            gap: 5px;
        }

        .content-body .tb-hint svg {

            width: 12px;
            height: 12px;

            flex-shrink: 0;
        }

        .content-body .tb-actions {

            display: flex;

            gap: 12px;

            align-items: center;

            margin-top: 26px;

            padding-top: 22px;

            border-top:
                1px solid #f7ebeb;

            animation:
                tbFadeUp .5s .45s ease both;
        }

        .content-body .tb-btn {

            display: inline-flex;

            align-items: center;

            gap: 9px;

            padding:
                12px 24px;

            border-radius: 12px;

            font-size: 14px;

            font-weight: 600;

            font-family: inherit;

            text-decoration: none;

            cursor: pointer;

            border: none;

            transition:
                transform .25s,
                box-shadow .25s,
                background .25s,
                color .25s;

            position: relative;

            overflow: hidden;
        }

        .content-body .tb-btn svg {

            width: 17px;
            height: 17px;

            transition:
                transform .3s;
        }

        .content-body .tb-btn-save {

            background:
                linear-gradient(
                    135deg,
                    #c9184a,
                    #8f1226 60%,
                    #5c0a14
                );

            color: #fff;

            box-shadow:
                0 8px 18px
                rgba(143, 18, 38, .35);
        }

        .content-body
        .tb-btn-save:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 12px 26px
                rgba(143, 18, 38, .45);
        }

        .content-body
        .tb-btn-save:hover svg {

            transform:
                translateX(3px);
        }

        .content-body
        .tb-btn-save:active {

            transform:
                translateY(0)
                scale(.98);
        }

        .content-body
        .tb-btn-save:disabled {

            opacity: .75;

            cursor:
                not-allowed;

            transform: none;
        }

        .content-body .tb-btn-batal {

            background: #fff;

            color: #8f1226;

            border:
                1.5px solid #e6dcdc;
        }

        .content-body
        .tb-btn-batal:hover {

            background: #fdf3f3;

            border-color:
                #c9184a;

            transform:
                translateY(-2px);
        }

        .content-body .tb-spinner {

            width: 16px;
            height: 16px;

            border:
                2px solid
                rgba(255, 255, 255, .4);

            border-top-color: #fff;

            border-radius: 50%;

            display: inline-block;

            animation:
                tbSpin .7s linear infinite;
        }

        @keyframes tbSpin {

            to {
                transform:
                    rotate(360deg);
            }
        }

        @keyframes tbAlertIn {

            from {

                opacity: 0;

                transform:
                    translateY(-8px);
            }

            to {

                opacity: 1;

                transform: none;
            }
        }

        @keyframes tbShake {

            0%,
            100% {
                transform:
                    translateX(0);
            }

            20% {
                transform:
                    translateX(-6px);
            }

            40% {
                transform:
                    translateX(6px);
            }

            60% {
                transform:
                    translateX(-4px);
            }

            80% {
                transform:
                    translateX(4px);
            }
        }

        @keyframes tbFadeUp {

            from {

                opacity: 0;

                transform:
                    translateY(16px);
            }

            to {

                opacity: 1;

                transform: none;
            }
        }

        @media (max-width: 640px) {

            .content-body
            .tb-form-grid {

                grid-template-columns:
                    1fr;
            }

            .content-body
            .tb-span-2 {

                grid-column:
                    span 1;
            }

            .content-body
            .tb-card-body {

                padding: 20px;
            }

            .content-body
            .tb-actions {

                flex-direction:
                    column-reverse;
            }

            .content-body
            .tb-btn {

                width: 100%;

                justify-content:
                    center;
            }

            .content-body
            .tb-title {

                font-size: 19px;
            }

            .content-body
            .tb-cover-upload {

                align-items:
                    flex-start;
            }
        }

    </style>

</head>

<body>

<div class="app-layout">

    <?php include 'sidebar.php'; ?>

    <div class="main-content">

        <?php include 'topbar.php'; ?>

        <div class="content-body">

            <!-- HEADER -->

            <div class="tb-page-header">

                <h2 class="tb-title">

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >

                        <circle
                            cx="12"
                            cy="12"
                            r="10"
                        />

                        <line
                            x1="12"
                            y1="8"
                            x2="12"
                            y2="16"
                        />

                        <line
                            x1="8"
                            y1="12"
                            x2="16"
                            y2="12"
                        />

                    </svg>

                    <span>

                        Tambah Data Buku

                        <small>
                            Lengkapi formulir untuk mendaftarkan buku baru
                        </small>

                    </span>

                </h2>

            </div>


            <!-- CARD -->

            <div class="tb-card">

                <div class="tb-card-head">

                    <div class="tb-card-head-icon">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >

                            <path
                                d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"
                            />

                            <path
                                d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"
                            />

                        </svg>

                    </div>

                    <div>

                        <h2>
                            Formulir Buku Baru
                        </h2>

                        <p>
                            Semua kolom bertanda <b>*</b> wajib diisi
                        </p>

                    </div>

                </div>


                <div class="tb-card-body">

                    <?php if ($error): ?>

                        <div class="tb-alert">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >

                                <path
                                    d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
                                />

                                <line
                                    x1="12"
                                    y1="9"
                                    x2="12"
                                    y2="13"
                                />

                                <line
                                    x1="12"
                                    y1="17"
                                    x2="12.01"
                                    y2="17"
                                />

                            </svg>

                            <span>
                                <?= $error; ?>
                            </span>

                        </div>

                    <?php endif; ?>


                    <form
                        action=""
                        method="POST"
                        enctype="multipart/form-data"
                        id="tbForm"
                    >

                        <div class="tb-form-grid">


                            <!-- KODE -->

                            <div class="tb-field">

                                <label>

                                    Kode Buku

                                    <span class="req">
                                        *
                                    </span>

                                </label>

                                <div class="tb-input-wrap">

                                    <svg
                                        class="tb-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >

                                        <rect
                                            x="3"
                                            y="4"
                                            width="18"
                                            height="16"
                                            rx="2"
                                        />

                                        <line
                                            x1="7"
                                            y1="9"
                                            x2="17"
                                            y2="9"
                                        />

                                        <line
                                            x1="7"
                                            y1="13"
                                            x2="12"
                                            y2="13"
                                        />

                                    </svg>

                                    <input
                                        type="text"
                                        name="kode_buku"
                                        class="tb-mono"
                                        required
                                        placeholder="BK001"
                                        maxlength="20"
                                        autocomplete="off"
                                        value="<?= htmlspecialchars(
                                            $_POST['kode_buku'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                </div>

                                <div class="tb-hint">
                                    Kode unik buku
                                </div>

                            </div>


                            <!-- STOK -->

                            <div class="tb-field">

                                <label>

                                    Jumlah Stok

                                    <span class="req">
                                        *
                                    </span>

                                </label>

                                <div class="tb-input-wrap">

                                    <svg
                                        class="tb-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >

                                        <path
                                            d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"
                                        />

                                    </svg>

                                    <input
                                        type="number"
                                        name="stok"
                                        min="1"
                                        required
                                        placeholder="1"
                                        value="<?= htmlspecialchars(
                                            $_POST['stok'] ?? '1',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                </div>

                            </div>


                            <!-- JUDUL -->

                            <div class="tb-field tb-span-2">

                                <label>

                                    Judul Buku

                                    <span class="req">
                                        *
                                    </span>

                                </label>

                                <div class="tb-input-wrap">

                                    <svg
                                        class="tb-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >

                                        <path
                                            d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"
                                        />

                                        <path
                                            d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"
                                        />

                                    </svg>

                                    <input
                                        type="text"
                                        name="judul"
                                        required
                                        placeholder="Judul lengkap buku"
                                        maxlength="200"
                                        autocomplete="off"
                                        value="<?= htmlspecialchars(
                                            $_POST['judul'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                </div>

                            </div>


                            <!-- PENULIS -->

                            <div class="tb-field">

                                <label>

                                    Penulis

                                    <span class="req">
                                        *
                                    </span>

                                </label>

                                <div class="tb-input-wrap">

                                    <svg
                                        class="tb-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >

                                        <path
                                            d="M12 20h9"
                                        />

                                        <path
                                            d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"
                                        />

                                    </svg>

                                    <input
                                        type="text"
                                        name="penulis"
                                        required
                                        placeholder="Nama penulis"
                                        maxlength="100"
                                        autocomplete="off"
                                        value="<?= htmlspecialchars(
                                            $_POST['penulis'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                </div>

                            </div>


                            <!-- KATEGORI -->

                            <div class="tb-field">

                                <label>

                                    Kategori

                                    <span class="req">
                                        *
                                    </span>

                                </label>

                                <div class="tb-input-wrap">

                                    <svg
                                        class="tb-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >

                                        <path
                                            d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"
                                        />

                                        <line
                                            x1="7"
                                            y1="7"
                                            x2="7.01"
                                            y2="7"
                                        />

                                    </svg>

                                    <input
                                        type="text"
                                        name="kategori"
                                        list="kategori_list"
                                        required
                                        placeholder="Pilih atau ketik kategori"
                                        maxlength="50"
                                        autocomplete="off"
                                        value="<?= htmlspecialchars(
                                            $_POST['kategori'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                    <datalist id="kategori_list">

                                        <?php foreach (
                                            $list_kategori
                                            as $kat
                                        ): ?>

                                            <option
                                                value="<?= htmlspecialchars(
                                                    $kat,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                            ></option>

                                        <?php endforeach; ?>

                                    </datalist>

                                </div>

                            </div>


                            <!-- TAHUN -->

                            <div class="tb-field">

                                <label>

                                    Tahun Terbit

                                    <span class="req">
                                        *
                                    </span>

                                </label>

                                <div class="tb-input-wrap">

                                    <svg
                                        class="tb-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >

                                        <rect
                                            x="3"
                                            y="4"
                                            width="18"
                                            height="18"
                                            rx="2"
                                        />

                                        <line
                                            x1="16"
                                            y1="2"
                                            x2="16"
                                            y2="6"
                                        />

                                        <line
                                            x1="8"
                                            y1="2"
                                            x2="8"
                                            y2="6"
                                        />

                                        <line
                                            x1="3"
                                            y1="10"
                                            x2="21"
                                            y2="10"
                                        />

                                    </svg>

                                    <input
                                        type="number"
                                        name="tahun_terbit"
                                        required
                                        placeholder="<?= $tahun_sekarang ?>"
                                        min="1900"
                                        max="<?= $tahun_sekarang + 1 ?>"
                                        autocomplete="off"
                                        value="<?= htmlspecialchars(
                                            $_POST['tahun_terbit'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                </div>

                            </div>


                            <!-- PENERBIT -->

                            <div class="tb-field">

                                <label>

                                    Penerbit

                                    <span class="req">
                                        *
                                    </span>

                                </label>

                                <div class="tb-input-wrap">

                                    <svg
                                        class="tb-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >

                                        <path
                                            d="M3 21h18"
                                        />

                                        <path
                                            d="M5 21V7l7-4 7 4v14"
                                        />

                                        <path
                                            d="M9 21v-6h6v6"
                                        />

                                    </svg>

                                    <input
                                        type="text"
                                        name="penerbit"
                                        required
                                        placeholder="Nama penerbit"
                                        maxlength="100"
                                        autocomplete="off"
                                        value="<?= htmlspecialchars(
                                            $_POST['penerbit'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                </div>

                            </div>


                            <!-- COVER -->

                            <div class="tb-field tb-span-2">

                                <label>

                                    Sampul Buku

                                    <span
                                        style="
                                            font-size:11px;
                                            color:#a29294;
                                            font-weight:400;
                                        "
                                    >
                                        (Opsional)
                                    </span>

                                </label>

                                <div class="tb-cover-upload">

                                    <img
                                        id="imgPreview"
                                        class="tb-cover-preview"
                                        src=""
                                        alt="Preview"
                                        style="display:none;"
                                    >

                                    <div
                                        id="previewPlaceholder"
                                        class="tb-cover-preview"
                                    >
                                        No Cover
                                    </div>

                                    <div class="tb-cover-input">

                                        <input
                                            type="file"
                                            name="cover"
                                            id="coverInput"
                                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                        >

                                        <div class="tb-hint">

                                            PNG, JPG, atau WEBP.
                                            Maksimal 2MB.

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- ACTION -->

                        <div class="tb-actions">

                            <button
                                type="submit"
                                id="tbBtnSave"
                                class="tb-btn tb-btn-save"
                            >

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >

                                    <path
                                        d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"
                                    />

                                    <polyline
                                        points="17 21 17 13 7 13 7 21"
                                    />

                                    <polyline
                                        points="7 3 7 8 15 8"
                                    />

                                </svg>

                                Simpan Buku

                            </button>


                            <a
                                href="buku.php"
                                class="tb-btn tb-btn-batal"
                            >

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >

                                    <line
                                        x1="18"
                                        y1="6"
                                        x2="6"
                                        y2="18"
                                    />

                                    <line
                                        x1="6"
                                        y1="6"
                                        x2="18"
                                        y2="18"
                                    />

                                </svg>

                                Batal

                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


<script>

// =====================================================
// PREVIEW COVER
// =====================================================

const coverInput =
    document.getElementById('coverInput');

const preview =
    document.getElementById('imgPreview');

const placeholder =
    document.getElementById('previewPlaceholder');


coverInput.addEventListener(
    'change',
    function (e) {

        const file =
            e.target.files[0];

        // =============================================
        // JIKA TIDAK ADA FILE
        // =============================================

        if (!file) {

            preview.style.display =
                'none';

            placeholder.style.display =
                'flex';

            preview.src = '';

            return;
        }


        // =============================================
        // CEK UKURAN
        // =============================================

        const maxSize =
            2 * 1024 * 1024;

        if (file.size > maxSize) {

            showUploadError(
                'Ukuran cover maksimal 2 MB.'
            );

            return;
        }


        // =============================================
        // CEK MIME TYPE
        // =============================================

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type)) {

            showUploadError(
                'Format cover harus JPG, PNG, atau WEBP.'
            );

            return;
        }


        // =============================================
        // PREVIEW
        // =============================================

        const reader =
            new FileReader();

        reader.onload =
            function (evt) {

                preview.src =
                    evt.target.result;

                preview.style.display =
                    'block';

                placeholder.style.display =
                    'none';
            };

        reader.readAsDataURL(file);
    }
);


// =====================================================
// ERROR UPLOAD
// =====================================================

function showUploadError(message) {

    coverInput.value = '';

    preview.src = '';

    preview.style.display =
        'none';

    placeholder.style.display =
        'flex';

    let alertBox =
        document.querySelector(
            '.tb-upload-alert'
        );

    if (!alertBox) {

        alertBox =
            document.createElement('div');

        alertBox.className =
            'tb-upload-alert';

        alertBox.style.cssText = `
            margin-top:10px;
            padding:10px 12px;
            border-radius:9px;
            background:#fdecea;
            border:1px solid #f5c6c2;
            color:#b3261e;
            font-size:12px;
        `;

        document
            .querySelector('.tb-cover-input')
            .appendChild(alertBox);
    }

    alertBox.textContent =
        message;

    setTimeout(
        function () {

            if (alertBox) {
                alertBox.remove();
            }

        },
        4000
    );
}


// =====================================================
// SUBMIT FORM
// =====================================================

document
    .getElementById('tbForm')
    .addEventListener(
        'submit',
        function () {

            const btn =
                document.getElementById(
                    'tbBtnSave'
                );

            btn.disabled = true;

            btn.innerHTML = `
                <span class="tb-spinner"></span>
                MENYIMPAN...
            `;
        }
    );


// =====================================================
// AUTO UPPERCASE KODE BUKU
// =====================================================

const kodeInput =
    document.querySelector(
        'input[name="kode_buku"]'
    );

kodeInput.addEventListener(
    'input',
    function () {

        this.value =
            this.value.toUpperCase();
    }
);

</script>

</body>
</html>