<?php

require_once 'koneksi.php';
require_once 'auth.php';

requireLogin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $kode = trim($_POST['kode_buku'] ?? '');
    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $tahun = trim($_POST['tahun_terbit'] ?? '');
    $penerbit = trim($_POST['penerbit'] ?? '');
    $gambarUrl = trim($_POST['gambar_url'] ?? '');

    $gambar = $gambarUrl !== '' ? $gambarUrl : null;


    // ======================================================
    // VALIDASI DATA
    // ======================================================

    if ($kode === '') {
        $errors[] = 'Kode buku wajib diisi.';
    }

    if ($judul === '') {
        $errors[] = 'Judul buku wajib diisi.';
    }

    if ($penulis === '') {
        $errors[] = 'Penulis wajib diisi.';
    }

    if ($kategori === '') {
        $errors[] = 'Kategori wajib diisi.';
    }

    if (
        $tahun === ''
        || !filter_var($tahun, FILTER_VALIDATE_INT)
        || (int)$tahun < 1000
        || (int)$tahun > 9999
    ) {
        $errors[] = 'Tahun terbit harus berupa tahun yang valid.';
    }

    if ($penerbit === '') {
        $errors[] = 'Penerbit wajib diisi.';
    }


    // ======================================================
    // VALIDASI URL GAMBAR
    // ======================================================

    if (
        $gambarUrl !== ''
        && !filter_var($gambarUrl, FILTER_VALIDATE_URL)
    ) {
        $errors[] = 'URL gambar tidak valid.';
    }


    // ======================================================
    // UPLOAD GAMBAR
    // ======================================================

    if (
        isset($_FILES['gambar_file'])
        && $_FILES['gambar_file']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['gambar_file']['error'] !== UPLOAD_ERR_OK) {

            $errors[] = 'Upload gambar gagal.';

        } else {

            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif'
            ];

            $finfo = new finfo(FILEINFO_MIME_TYPE);

            $mime = $finfo->file(
                $_FILES['gambar_file']['tmp_name']
            );


            if (!isset($allowed[$mime])) {

                $errors[] =
                    'Format gambar harus JPG, PNG, WEBP, atau GIF.';

            } elseif (
                $_FILES['gambar_file']['size']
                > 2 * 1024 * 1024
            ) {

                $errors[] =
                    'Ukuran gambar maksimal 2 MB.';

            } else {

                $uploadDir =
                    __DIR__ . '/assets/uploads/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }


                $filename =
                    'cover_'
                    . bin2hex(random_bytes(8))
                    . '.'
                    . $allowed[$mime];

                $target =
                    $uploadDir . $filename;


                if (
                    move_uploaded_file(
                        $_FILES['gambar_file']['tmp_name'],
                        $target
                    )
                ) {

                    $gambar =
                        'assets/uploads/' . $filename;

                } else {

                    $errors[] =
                        'Gambar tidak dapat disimpan.';
                }
            }
        }
    }


    // ======================================================
    // SIMPAN DATABASE
    // ======================================================

    if (!$errors) {

        try {

            $stmt = $pdo->prepare(
                'INSERT INTO books
                (
                    kode_buku,
                    judul,
                    penulis,
                    kategori,
                    tahun_terbit,
                    penerbit,
                    gambar
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)'
            );


            $stmt->execute([
                $kode,
                $judul,
                $penulis,
                $kategori,
                $tahun,
                $penerbit,
                $gambar
            ]);


            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Buku berhasil ditambahkan.'
            ];


            header('Location: buku.php');

            exit;


        } catch (PDOException $e) {

            $errors[] =
                $e->getCode() === '23000'
                    ? 'Kode buku sudah digunakan. Gunakan kode lain.'
                    : 'Data gagal disimpan.';
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

    <title>Tambah Buku - Perpus API</title>

    <link
        rel="stylesheet"
        href="style.css"
    >


    <style>

        /* =====================================================
           TAMBAH BUKU PAGE
        ====================================================== */

        .add-book-page {
            max-width: 1180px;
            margin: 0 auto;
        }


        /* =====================================================
           HEADER
        ====================================================== */

        .add-book-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            gap: 25px;

            margin-bottom: 24px;
        }


        .add-book-heading {
            display: flex;
            align-items: flex-start;

            gap: 14px;
        }


        .add-book-heading-icon {
            width: 48px;
            height: 48px;

            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 13px;

            color: #7a1729;

            background: #f9e4e8;

            border: 1px solid #f0cfd6;
        }


        .add-book-heading-icon svg {
            width: 23px;
            height: 23px;

            stroke-width: 1.8;
        }


        .add-book-heading h1 {
            margin: 0 0 5px;

            color: #431019;

            font-size: 27px;

            line-height: 1.2;

            font-weight: 800;

            letter-spacing: -.4px;
        }


        .add-book-heading p {
            margin: 0;

            color: #92747b;

            font-size: 13px;

            line-height: 1.5;
        }


        /* =====================================================
           BACK BUTTON
        ====================================================== */

        .back-button {
            height: 40px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 7px;

            padding: 0 14px;

            color: #6c444c;

            background: #ffffff;

            border: 1px solid #eadcdf;

            border-radius: 9px;

            text-decoration: none;

            font-size: 12px;

            font-weight: 700;

            transition: .2s ease;
        }


        .back-button svg {
            width: 16px;
            height: 16px;
        }


        .back-button:hover {
            color: #76182a;

            background: #fff7f8;

            border-color: #dfc2c8;

            transform: translateX(-2px);
        }


        /* =====================================================
           ERROR ALERT
        ====================================================== */

        .add-book-alert {
            display: flex;
            align-items: flex-start;

            gap: 11px;

            margin-bottom: 20px;

            padding: 14px 16px;

            color: #a03649;

            background: #fff1f3;

            border: 1px solid #efd3d8;

            border-radius: 10px;

            font-size: 12px;
        }


        .add-book-alert > svg {
            width: 18px;
            height: 18px;

            flex-shrink: 0;

            margin-top: 1px;
        }


        .add-book-alert-content {
            flex: 1;
        }


        .add-book-alert-title {
            margin-bottom: 5px;

            color: #8e293c;

            font-weight: 800;
        }


        .add-book-alert ul {
            margin: 0;

            padding-left: 17px;
        }


        .add-book-alert li {
            margin: 3px 0;
        }


        /* =====================================================
           FORM PANEL
        ====================================================== */

        .add-book-panel {
            overflow: hidden;

            padding: 0 !important;

            background: #ffffff;

            border: 1px solid #eee1e4;

            border-radius: 15px;

            box-shadow:
                0 7px 25px rgba(70, 12, 24, .05);
        }


        /* =====================================================
           FORM TOP
        ====================================================== */

        .form-panel-header {
            display: flex;
            align-items: center;

            gap: 12px;

            padding: 18px 20px;

            background:
                linear-gradient(
                    180deg,
                    #fffafa,
                    #ffffff
                );

            border-bottom: 1px solid #f0e5e7;
        }


        .form-panel-icon {
            width: 38px;
            height: 38px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            color: #7a1729;

            background: #f9e5e9;
        }


        .form-panel-icon svg {
            width: 19px;
            height: 19px;
        }


        .form-panel-title {
            display: flex;
            flex-direction: column;

            gap: 2px;
        }


        .form-panel-title strong {
            color: #4a121b;

            font-size: 13px;

            font-weight: 800;
        }


        .form-panel-title span {
            color: #a08389;

            font-size: 11px;
        }


        /* =====================================================
           FORM CONTENT
        ====================================================== */

        .add-book-form {
            padding: 22px;
        }


        /* =====================================================
           SECTION
        ====================================================== */

        .form-section {
            margin-bottom: 25px;
        }


        .form-section:last-of-type {
            margin-bottom: 0;
        }


        .form-section-heading {
            display: flex;
            align-items: center;

            gap: 9px;

            margin-bottom: 16px;
        }


        .form-section-heading-icon {
            width: 31px;
            height: 31px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 8px;

            color: #7a1729;

            background: #fae8eb;
        }


        .form-section-heading-icon svg {
            width: 16px;
            height: 16px;
        }


        .form-section-heading-text {
            display: flex;
            flex-direction: column;

            gap: 2px;
        }


        .form-section-heading-text strong {
            color: #4a121b;

            font-size: 13px;

            font-weight: 800;
        }


        .form-section-heading-text span {
            color: #a4878e;

            font-size: 10px;
        }


        /* =====================================================
           FORM GRID
        ====================================================== */

        .book-form-grid {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 16px;
        }


        .form-group {
            display: flex;

            flex-direction: column;

            gap: 7px;
        }


        .form-group.full {
            grid-column: 1 / -1;
        }


        .form-label {
            display: flex;
            align-items: center;

            gap: 3px;

            color: #5c2831;

            font-size: 11px;

            font-weight: 800;
        }


        .required {
            color: #b5364c;
        }


        .form-input {
            width: 100%;

            height: 43px;

            box-sizing: border-box;

            padding: 0 13px;

            color: #4a151e;

            background: #ffffff;

            border: 1px solid #eadcdf;

            border-radius: 9px;

            outline: none;

            font-family: inherit;

            font-size: 12px;

            transition: .2s ease;
        }


        .form-input::placeholder {
            color: #b9a4a9;
        }


        .form-input:hover {
            border-color: #dcc6cb;
        }


        .form-input:focus {
            border-color: #9a3447;

            box-shadow:
                0 0 0 3px rgba(154, 52, 71, .08);
        }


        .form-help {
            margin: -2px 0 3px;

            color: #a4878e;

            font-size: 10px;

            line-height: 1.5;
        }


        /* =====================================================
           IMAGE SECTION
        ====================================================== */

        .image-section {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                210px;

            gap: 24px;

            align-items: start;
        }


        /* =====================================================
           IMAGE TABS
        ====================================================== */

        .source-tabs {
            display: flex;

            gap: 5px;

            padding: 4px;

            margin-bottom: 13px;

            background: #f8f1f2;

            border: 1px solid #eee0e3;

            border-radius: 10px;
        }


        .source-tab {
            flex: 1;

            height: 38px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 7px;

            padding: 0 12px;

            color: #896a72;

            background: transparent;

            border: 0;

            border-radius: 7px;

            font-family: inherit;

            font-size: 11px;

            font-weight: 700;

            cursor: pointer;

            transition: .2s ease;
        }


        .source-tab svg {
            width: 15px;
            height: 15px;
        }


        .source-tab:hover {
            color: #721729;
        }


        .source-tab.active {
            color: #711627;

            background: #ffffff;

            box-shadow:
                0 2px 7px rgba(70, 12, 24, .08);
        }


        /* =====================================================
           SOURCE BOX
        ====================================================== */

        .source-box {
            display: none;

            padding: 15px;

            background: #fffafa;

            border: 1px solid #eee1e4;

            border-radius: 10px;
        }


        .source-box.active {
            display: block;
        }


        .source-box input[type="file"] {
            width: 100%;

            box-sizing: border-box;

            padding: 10px;

            color: #74515a;

            background: #ffffff;

            border: 1px dashed #d9bec4;

            border-radius: 8px;

            font-family: inherit;

            font-size: 11px;

            cursor: pointer;
        }


        .source-box input[type="file"]::file-selector-button {
            margin-right: 10px;

            padding: 7px 10px;

            color: #7a1729;

            background: #fae8eb;

            border: 1px solid #efd2d8;

            border-radius: 6px;

            font-family: inherit;

            font-size: 10px;

            font-weight: 700;

            cursor: pointer;
        }


        .source-box input[type="url"] {
            width: 100%;

            height: 42px;

            box-sizing: border-box;

            padding: 0 12px;

            color: #4a151e;

            background: #ffffff;

            border: 1px solid #eadcdf;

            border-radius: 8px;

            outline: none;

            font-family: inherit;

            font-size: 11px;
        }


        .source-box input[type="url"]:focus {
            border-color: #9a3447;

            box-shadow:
                0 0 0 3px rgba(154, 52, 71, .08);
        }


        .source-box small {
            display: block;

            margin-top: 8px;

            color: #a4878e;

            font-size: 10px;

            line-height: 1.5;
        }


        /* =====================================================
           PREVIEW
        ====================================================== */

        .preview-box {
            padding: 14px;

            background: #fffafa;

            border: 1px solid #eee1e4;

            border-radius: 11px;
        }


        .preview-title {
            display: flex;
            align-items: center;

            gap: 7px;

            margin-bottom: 11px;

            color: #5c2831;

            font-size: 11px;

            font-weight: 800;
        }


        .preview-title svg {
            width: 15px;
            height: 15px;

            color: #7a1729;
        }


        .cover-preview {
            width: 100%;

            aspect-ratio: 3 / 4;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;

            color: #8c394a;

            background:
                linear-gradient(
                    145deg,
                    #f9e3e7,
                    #f1ccd3
                );

            border: 1px solid #e7cbd0;

            border-radius: 9px;

            box-shadow:
                0 6px 15px rgba(70, 12, 24, .07);
        }


        .cover-preview img {
            width: 100%;
            height: 100%;

            display: block;

            object-fit: cover;
        }


        .cover-preview.placeholder {
            flex-direction: column;

            gap: 8px;
        }


        .cover-preview.placeholder svg {
            width: 27px;
            height: 27px;

            stroke-width: 1.5;
        }


        .preview-placeholder-text {
            color: #9b747c;

            font-size: 9px;

            font-weight: 600;

            text-align: center;
        }


        /* =====================================================
           DIVIDER
        ====================================================== */

        .form-divider {
            height: 1px;

            margin: 24px 0;

            background: #f0e5e7;

            border: 0;
        }


        /* =====================================================
           FORM FOOTER
        ====================================================== */

        .form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;

            gap: 9px;

            padding-top: 20px;

            margin-top: 22px;

            border-top: 1px solid #f0e5e7;
        }


        .form-button {
            height: 41px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 7px;

            padding: 0 15px;

            border-radius: 9px;

            font-family: inherit;

            font-size: 11px;

            font-weight: 800;

            text-decoration: none;

            cursor: pointer;

            transition: .2s ease;
        }


        .form-button svg {
            width: 16px;
            height: 16px;
        }


        .form-button.cancel {
            color: #6c444c;

            background: #ffffff;

            border: 1px solid #eadcdf;
        }


        .form-button.cancel:hover {
            color: #76182a;

            background: #fff7f8;

            border-color: #dfc2c8;
        }


        .form-button.submit {
            color: #ffffff;

            background:
                linear-gradient(
                    135deg,
                    #661321,
                    #8f1d32
                );

            border: 1px solid #661321;

            box-shadow:
                0 6px 15px rgba(102, 19, 33, .18);
        }


        .form-button.submit:hover {
            transform: translateY(-1px);

            box-shadow:
                0 8px 19px rgba(102, 19, 33, .25);
        }


        /* =====================================================
           RESPONSIVE
        ====================================================== */

        @media (max-width: 900px) {

            .image-section {
                grid-template-columns: 1fr;
            }


            .preview-box {
                max-width: 220px;
            }

        }


        @media (max-width: 700px) {

            .add-book-header {
                flex-direction: column;
            }


            .add-book-header .back-button {
                width: 100%;
            }


            .book-form-grid {
                grid-template-columns: 1fr;
            }


            .form-group.full {
                grid-column: auto;
            }


            .add-book-form {
                padding: 17px;
            }

        }


        @media (max-width: 480px) {

            .add-book-heading h1 {
                font-size: 23px;
            }


            .add-book-heading-icon {
                width: 43px;
                height: 43px;
            }


            .source-tabs {
                flex-direction: column;
            }


            .form-actions {
                flex-direction: column-reverse;
            }


            .form-button {
                width: 100%;
            }

        }

    </style>

</head>


<body>


<div class="app">

    <?php include 'sidebar.php'; ?>


    <main class="main-content">

        <div class="add-book-page">


            <!-- =================================================
                 HEADER
            ================================================== -->

            <div class="add-book-header">

                <div class="add-book-heading">

                    <div class="add-book-heading-icon">

                        <i data-lucide="book-plus"></i>

                    </div>


                    <div>

                        <h1>
                            Tambah Buku
                        </h1>

                        <p>
                            Tambahkan koleksi buku baru ke dalam
                            sistem perpustakaan.
                        </p>

                    </div>

                </div>


                <a
                    href="buku.php"
                    class="back-button"
                >

                    <i data-lucide="arrow-left"></i>

                    Kembali ke Data Buku

                </a>

            </div>


            <!-- =================================================
                 ERROR
            ================================================== -->

            <?php if ($errors): ?>

                <div class="add-book-alert">

                    <i data-lucide="circle-alert"></i>


                    <div class="add-book-alert-content">

                        <div class="add-book-alert-title">
                            Data belum dapat disimpan
                        </div>


                        <ul>

                            <?php foreach ($errors as $error): ?>

                                <li>
                                    <?= e($error) ?>
                                </li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 FORM PANEL
            ================================================== -->

            <section class="panel add-book-panel">


                <!-- PANEL HEADER -->

                <div class="form-panel-header">

                    <div class="form-panel-icon">

                        <i data-lucide="file-pen-line"></i>

                    </div>


                    <div class="form-panel-title">

                        <strong>
                            Informasi Buku
                        </strong>

                        <span>
                            Lengkapi informasi utama buku yang akan ditambahkan.
                        </span>

                    </div>

                </div>


                <!-- FORM -->

                <form
                    method="post"
                    enctype="multipart/form-data"
                    id="bookForm"
                    class="add-book-form"
                >


                    <!-- =================================================
                         INFORMASI BUKU
                    ================================================== -->

                    <div class="form-section">

                        <div class="form-section-heading">

                            <div class="form-section-heading-icon">

                                <i data-lucide="book-open"></i>

                            </div>


                            <div class="form-section-heading-text">

                                <strong>
                                    Detail Buku
                                </strong>

                                <span>
                                    Masukkan informasi dasar buku.
                                </span>

                            </div>

                        </div>


                        <div class="book-form-grid">


                            <!-- KODE -->

                            <div class="form-group">

                                <label
                                    class="form-label"
                                    for="kode_buku"
                                >
                                    Kode Buku
                                    <span class="required">*</span>
                                </label>


                                <input
                                    class="form-input"
                                    id="kode_buku"
                                    type="text"
                                    name="kode_buku"
                                    maxlength="20"
                                    required
                                    value="<?= e($_POST['kode_buku'] ?? '') ?>"
                                    placeholder="Contoh: BK011"
                                >

                            </div>


                            <!-- JUDUL -->

                            <div class="form-group">

                                <label
                                    class="form-label"
                                    for="judul"
                                >
                                    Judul Buku
                                    <span class="required">*</span>
                                </label>


                                <input
                                    class="form-input"
                                    id="judul"
                                    type="text"
                                    name="judul"
                                    maxlength="150"
                                    required
                                    value="<?= e($_POST['judul'] ?? '') ?>"
                                    placeholder="Masukkan judul buku"
                                >

                            </div>


                            <!-- PENULIS -->

                            <div class="form-group">

                                <label
                                    class="form-label"
                                    for="penulis"
                                >
                                    Penulis
                                    <span class="required">*</span>
                                </label>


                                <input
                                    class="form-input"
                                    id="penulis"
                                    type="text"
                                    name="penulis"
                                    maxlength="100"
                                    required
                                    value="<?= e($_POST['penulis'] ?? '') ?>"
                                    placeholder="Nama penulis"
                                >

                            </div>


                            <!-- KATEGORI -->

                            <div class="form-group">

                                <label
                                    class="form-label"
                                    for="kategori"
                                >
                                    Kategori
                                    <span class="required">*</span>
                                </label>


                                <input
                                    class="form-input"
                                    id="kategori"
                                    type="text"
                                    name="kategori"
                                    maxlength="50"
                                    required
                                    value="<?= e($_POST['kategori'] ?? '') ?>"
                                    placeholder="Novel / Teknologi / Pendidikan"
                                >

                            </div>


                            <!-- TAHUN -->

                            <div class="form-group">

                                <label
                                    class="form-label"
                                    for="tahun_terbit"
                                >
                                    Tahun Terbit
                                    <span class="required">*</span>
                                </label>


                                <input
                                    class="form-input"
                                    id="tahun_terbit"
                                    type="number"
                                    name="tahun_terbit"
                                    min="1000"
                                    max="9999"
                                    required
                                    value="<?= e($_POST['tahun_terbit'] ?? '') ?>"
                                    placeholder="2026"
                                >

                            </div>


                            <!-- PENERBIT -->

                            <div class="form-group">

                                <label
                                    class="form-label"
                                    for="penerbit"
                                >
                                    Penerbit
                                    <span class="required">*</span>
                                </label>


                                <input
                                    class="form-input"
                                    id="penerbit"
                                    type="text"
                                    name="penerbit"
                                    maxlength="100"
                                    required
                                    value="<?= e($_POST['penerbit'] ?? '') ?>"
                                    placeholder="Nama penerbit"
                                >

                            </div>


                        </div>

                    </div>


                    <hr class="form-divider">


                    <!-- =================================================
                         GAMBAR
                    ================================================== -->

                    <div class="form-section">


                        <div class="form-section-heading">

                            <div class="form-section-heading-icon">

                                <i data-lucide="image"></i>

                            </div>


                            <div class="form-section-heading-text">

                                <strong>
                                    Sampul Buku
                                </strong>

                                <span>
                                    Upload dari komputer atau gunakan URL gambar.
                                </span>

                            </div>

                        </div>


                        <div class="image-section">


                            <!-- SOURCE -->

                            <div>

                                <div class="source-tabs">


                                    <button
                                        type="button"
                                        class="source-tab active"
                                        data-source="file"
                                    >

                                        <i data-lucide="upload"></i>

                                        Upload dari Komputer

                                    </button>


                                    <button
                                        type="button"
                                        class="source-tab"
                                        data-source="url"
                                    >

                                        <i data-lucide="link"></i>

                                        Gunakan URL

                                    </button>


                                </div>


                                <!-- FILE -->

                                <div
                                    id="fileSource"
                                    class="source-box active"
                                >

                                    <input
                                        type="file"
                                        id="gambar_file"
                                        name="gambar_file"
                                        accept="image/jpeg,image/png,image/webp,image/gif"
                                    >


                                    <small>
                                        Format yang didukung:
                                        JPG, PNG, WEBP, GIF.
                                        Maksimal 2 MB.
                                    </small>

                                </div>


                                <!-- URL -->

                                <div
                                    id="urlSource"
                                    class="source-box"
                                >

                                    <input
                                        type="url"
                                        id="gambar_url"
                                        name="gambar_url"
                                        value="<?= e($_POST['gambar_url'] ?? '') ?>"
                                        placeholder="https://contoh.com/sampul.jpg"
                                    >


                                    <small>
                                        Masukkan URL langsung yang mengarah
                                        ke file gambar sampul.
                                    </small>

                                </div>

                            </div>


                            <!-- PREVIEW -->

                            <div class="preview-box">


                                <div class="preview-title">

                                    <i data-lucide="eye"></i>

                                    Preview Sampul

                                </div>


                                <div
                                    id="imagePreview"
                                    class="cover-preview placeholder"
                                >

                                    <i data-lucide="image"></i>

                                    <span class="preview-placeholder-text">
                                        Belum ada gambar
                                    </span>

                                </div>


                            </div>


                        </div>

                    </div>


                    <!-- =================================================
                         ACTION
                    ================================================== -->

                    <div class="form-actions">


                        <a
                            href="buku.php"
                            class="form-button cancel"
                        >

                            <i data-lucide="x"></i>

                            Batal

                        </a>


                        <button
                            type="submit"
                            class="form-button submit"
                        >

                            <i data-lucide="save"></i>

                            Simpan Buku

                        </button>


                    </div>


                </form>

            </section>

        </div>

    </main>

</div>


<!-- =========================================================
     LUCIDE ICON
========================================================== -->

<script src="https://unpkg.com/lucide@latest"></script>


<script>

const tabs =
    document.querySelectorAll('.source-tab');

const fileSource =
    document.getElementById('fileSource');

const urlSource =
    document.getElementById('urlSource');

const fileInput =
    document.getElementById('gambar_file');

const urlInput =
    document.getElementById('gambar_url');

const preview =
    document.getElementById('imagePreview');


// ======================================================
// LUCIDE
// ======================================================

function refreshIcons() {

    if (typeof lucide !== 'undefined') {

        lucide.createIcons();

    }

}


// ======================================================
// PREVIEW
// ======================================================

function showPreview(src) {

    if (!src) {

        preview.className =
            'cover-preview placeholder';

        preview.innerHTML = `
            <i data-lucide="image"></i>

            <span class="preview-placeholder-text">
                Belum ada gambar
            </span>
        `;

        refreshIcons();

        return;
    }


    preview.className =
        'cover-preview';


    preview.innerHTML = `

        <img
            src="${escapeAttribute(src)}"
            alt="Preview Sampul"
            onerror="imageLoadError(this)"
        >

    `;
}


// ======================================================
// ESCAPE ATTRIBUTE
// ======================================================

function escapeAttribute(value) {

    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

}


// ======================================================
// ERROR GAMBAR
// ======================================================

function imageLoadError(image) {

    image.parentElement.className =
        'cover-preview placeholder';

    image.parentElement.innerHTML = `

        <i data-lucide="image-off"></i>

        <span class="preview-placeholder-text">
            Gambar tidak dapat dimuat
        </span>

    `;

    refreshIcons();

}


// ======================================================
// TAB SUMBER GAMBAR
// ======================================================

tabs.forEach(tab => {

    tab.addEventListener('click', () => {

        tabs.forEach(item => {

            item.classList.remove('active');

        });


        tab.classList.add('active');


        const source =
            tab.dataset.source;


        fileSource.classList.toggle(
            'active',
            source === 'file'
        );


        urlSource.classList.toggle(
            'active',
            source === 'url'
        );


        if (source === 'file') {

            urlInput.value = '';

            showPreview('');

        } else {

            fileInput.value = '';

            showPreview(
                urlInput.value.trim()
            );

        }

    });

});


// ======================================================
// FILE INPUT
// ======================================================

fileInput.addEventListener(
    'change',
    () => {

        if (!fileInput.files[0]) {

            showPreview('');

            return;
        }


        const file =
            fileInput.files[0];


        if (
            !file.type.startsWith('image/')
        ) {

            fileInput.value = '';

            showPreview('');

            return;
        }


        const reader =
            new FileReader();


        reader.onload =
            event => {

                showPreview(
                    event.target.result
                );

            };


        reader.readAsDataURL(file);


        urlInput.value = '';

    }
);


// ======================================================
// URL INPUT
// ======================================================

urlInput.addEventListener(
    'input',
    () => {

        const value =
            urlInput.value.trim();


        if (value) {

            fileInput.value = '';

            showPreview(value);

        } else {

            showPreview('');

        }

    }
);


// ======================================================
// SUBMIT
// ======================================================

document
    .getElementById('bookForm')
    .addEventListener(
        'submit',
        () => {

            const button =
                document.querySelector(
                    '.form-button.submit'
                );


            if (!button) {
                return;
            }


            button.innerHTML = `

                <i
                    data-lucide="loader-circle"
                    class="submit-loader"
                ></i>

                Menyimpan...

            `;


            button.disabled = true;

            button.style.opacity = '0.8';

            refreshIcons();

        }
    );


// ======================================================
// LOAD ICON
// ======================================================

refreshIcons();

</script>


<style>

.submit-loader {
    animation:
        submitSpin .8s linear infinite;
}


@keyframes submitSpin {

    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }

}

</style>


</body>

</html>