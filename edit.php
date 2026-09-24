<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}

require_once 'koneksi.php';

$error = '';

/* =========================================================
   FUNGSI ESCAPE
   ========================================================= */
function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

/* =========================================================
   AMBIL ID
   ========================================================= */
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: buku.php");
    exit;
}

/* =========================================================
   AMBIL DATA BUKU
   ========================================================= */
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM books WHERE id = ?"
);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$buku = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$buku) {
    header("Location: buku.php");
    exit;
}

/* =========================================================
   DATA AWAL
   ========================================================= */
$kode_buku    = $buku['kode_buku'] ?? '';
$judul        = $buku['judul'] ?? '';
$penulis      = $buku['penulis'] ?? '';
$kategori     = $buku['kategori'] ?? '';
$tahun_terbit = $buku['tahun_terbit'] ?? '';
$penerbit     = $buku['penerbit'] ?? '';
$stok         = $buku['stok'] ?? 0;
$cover_lama   = $buku['cover'] ?? '';

/* =========================================================
   PROSES UPDATE
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $kode_buku = trim($_POST['kode_buku'] ?? '');
    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $tahun_terbit = (int) ($_POST['tahun_terbit'] ?? 0);
    $penerbit = trim($_POST['penerbit'] ?? '');
    $stok = (int) ($_POST['stok'] ?? 0);

    /* =====================================================
       VALIDASI
       ===================================================== */

    if (
        $kode_buku === '' ||
        $judul === '' ||
        $penulis === '' ||
        $kategori === '' ||
        $tahun_terbit === 0 ||
        $penerbit === ''
    ) {
        $error = "Semua kolom wajib diisi.";
    }

    if ($stok < 0) {
        $error = "Stok tidak boleh kurang dari 0.";
    }

    $tahun_sekarang = (int) date('Y');

    if (
        $tahun_terbit < 1900 ||
        $tahun_terbit > ($tahun_sekarang + 1)
    ) {
        $error = "Tahun terbit tidak valid.";
    }

    /* =====================================================
       CEK KODE BUKU DUPLIKAT
       ===================================================== */

    if (empty($error)) {

        $stmt_cek = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM books
             WHERE kode_buku = ?
             AND id != ?
             LIMIT 1"
        );

        if ($stmt_cek) {

            mysqli_stmt_bind_param(
                $stmt_cek,
                "si",
                $kode_buku,
                $id
            );

            mysqli_stmt_execute($stmt_cek);

            $result_cek = mysqli_stmt_get_result($stmt_cek);

            if (mysqli_num_rows($result_cek) > 0) {
                $error = "Kode buku tersebut sudah digunakan oleh buku lain.";
            }

            mysqli_stmt_close($stmt_cek);
        }
    }

    /* =====================================================
       COVER DEFAULT = COVER LAMA
       ===================================================== */

    $cover_nama = $cover_lama;

    /* =====================================================
       UPLOAD COVER BARU
       Dibuat sama seperti sistem pengaturan yang berhasil
       ===================================================== */

    if (
        empty($error) &&
        isset($_FILES['cover']) &&
        $_FILES['cover']['error'] === UPLOAD_ERR_OK
    ) {

        $file_tmp = $_FILES['cover']['tmp_name'];
        $file_name = $_FILES['cover']['name'];

        $file_ext = strtolower(
            pathinfo($file_name, PATHINFO_EXTENSION)
        );

        $allowed_ext = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        $allowed_mime = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        /* =================================================
           CEK UKURAN
           ================================================= */

        if ($_FILES['cover']['size'] > 2 * 1024 * 1024) {

            $error = "Ukuran gambar maksimal 2 MB.";

        } else {

            /* =============================================
               CEK MIME TYPE
               ============================================= */

            $file_mime = mime_content_type($file_tmp);

            if (
                !in_array($file_ext, $allowed_ext, true) ||
                !in_array($file_mime, $allowed_mime, true)
            ) {

                $error =
                    "Format gambar tidak valid atau tidak didukung! " .
                    "(Gunakan JPG, JPEG, PNG, atau WEBP)";

            } else {

                /* =========================================
                   FOLDER UPLOAD
                   SAMA DENGAN PENGATURAN
                   ========================================= */

                $upload_dir = 'uploads/cover/';

                if (!is_dir($upload_dir)) {

                    mkdir(
                        $upload_dir,
                        0755,
                        true
                    );
                }

                /* =========================================
                   NAMA FILE BARU
                   ========================================= */

                $new_cover_name =
                    time() .
                    '_' .
                    bin2hex(random_bytes(8)) .
                    '.' .
                    $file_ext;

                /* =========================================
                   PINDAHKAN FILE
                   ========================================= */

                if (
                    move_uploaded_file(
                        $file_tmp,
                        $upload_dir . $new_cover_name
                    )
                ) {

                    /* =====================================
                       HAPUS COVER LAMA
                       ===================================== */

                    if (
                        !empty($cover_lama) &&
                        $cover_lama !== 'default_cover.jpg' &&
                        file_exists(
                            $upload_dir . $cover_lama
                        )
                    ) {

                        unlink(
                            $upload_dir . $cover_lama
                        );
                    }

                    /* =====================================
                       SIMPAN NAMA COVER BARU
                       ===================================== */

                    $cover_nama = $new_cover_name;

                } else {

                    $error =
                        "Gagal mengunggah gambar cover baru.";
                }
            }
        }
    }

    /* =====================================================
       UPDATE DATABASE
       ===================================================== */

    if (empty($error)) {

        $query = "
            UPDATE books SET
                kode_buku = ?,
                judul = ?,
                penulis = ?,
                kategori = ?,
                tahun_terbit = ?,
                penerbit = ?,
                stok = ?,
                cover = ?
            WHERE id = ?
        ";

        $stmt_update = mysqli_prepare(
            $koneksi,
            $query
        );

        if (!$stmt_update) {

            $error =
                "Gagal mempersiapkan query update: " .
                mysqli_error($koneksi);

        } else {

            /*
             * 9 parameter:
             *
             * kode_buku    = s
             * judul        = s
             * penulis      = s
             * kategori     = s
             * tahun_terbit = i
             * penerbit     = s
             * stok         = i
             * cover        = s
             * id           = i
             *
             * Jadi:
             * ssssisi si -> SALAH
             * ssssisisi -> BENAR
             */

            mysqli_stmt_bind_param(
                $stmt_update,
                "ssssisisi",
                $kode_buku,
                $judul,
                $penulis,
                $kategori,
                $tahun_terbit,
                $penerbit,
                $stok,
                $cover_nama,
                $id
            );

            if (mysqli_stmt_execute($stmt_update)) {

                mysqli_stmt_close($stmt_update);

                header(
                    "Location: buku.php?status=success_edit"
                );

                exit;

            } else {

                $error =
                    "Gagal memperbarui data: " .
                    mysqli_stmt_error($stmt_update);

                mysqli_stmt_close($stmt_update);
            }
        }
    }
}

/* =========================================================
   DAFTAR KATEGORI
   ========================================================= */

$list_kategori = [];

$res_kat = mysqli_query(
    $koneksi,
    "SELECT DISTINCT kategori
     FROM books
     WHERE kategori IS NOT NULL
     AND kategori != ''
     ORDER BY kategori ASC"
);

if ($res_kat) {

    while ($row = mysqli_fetch_assoc($res_kat)) {

        if (!empty($row['kategori'])) {
            $list_kategori[] = $row['kategori'];
        }
    }
}

$tahun_sekarang = date('Y');

/* =========================================================
   PATH COVER
   ========================================================= */

$coverPath = '';

if (
    !empty($cover_lama) &&
    $cover_lama !== 'default_cover.jpg'
) {

    $coverPath =
        'uploads/cover/' .
        rawurlencode($cover_lama);
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

    <title>Edit Buku — Admin Perpustakaan</title>

    <style>

        .content-body .eb-page-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
            animation: ebFadeUp .5s ease both;
        }

        .content-body .eb-title {
            font-size: 23px;
            font-weight: 700;
            background: linear-gradient(
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

        .content-body .eb-title svg {
            width: 27px;
            height: 27px;
            color: #8f1226;
            -webkit-text-fill-color: #8f1226;
        }

        .content-body .eb-title small {
            display: block;
            font-size: 12px;
            font-weight: 500;
            -webkit-text-fill-color: #a29294;
            letter-spacing: .5px;
            margin-top: 2px;
        }

        .content-body .eb-card {
            background: #fff;
            border-radius: 18px;
            border: 1px solid #f7ebeb;
            box-shadow: 0 8px 24px rgba(74,8,18,.08);
            max-width: 820px;
            overflow: hidden;
            animation: ebFadeUp .5s .1s ease both;
        }

        .content-body .eb-card-head {
            background: linear-gradient(
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

        .content-body .eb-card-head::before {
            content: "";
            position: absolute;
            top: -45px;
            right: -45px;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,.1);
            border-radius: 50%;
        }

        .content-body .eb-card-head-icon {
            width: 44px;
            height: 44px;
            border-radius: 13px;
            background: rgba(255,255,255,.16);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.25);
            flex-shrink: 0;
        }

        .content-body .eb-card-head-icon svg {
            width: 22px;
            height: 22px;
            color: #ffd8a8;
        }

        .content-body .eb-card-head h2 {
            font-size: 16.5px;
            font-weight: 600;
            color: #fff;
        }

        .content-body .eb-card-head p {
            font-size: 12.5px;
            color: rgba(255,255,255,.75);
            margin-top: 2px;
        }

        .content-body .eb-card-body {
            padding: 28px;
        }

        .content-body .eb-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #fdecea;
            color: #b3261e;
            border: 1px solid #f5c6c2;
            border-left: 4px solid #d93025;
            padding: 13px 15px;
            border-radius: 11px;
            font-size: 13.5px;
            line-height: 1.5;
            margin-bottom: 22px;
        }

        .content-body .eb-alert svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .content-body .eb-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .content-body .eb-field {
            animation: ebFadeUp .5s ease both;
        }

        .content-body .eb-span-2 {
            grid-column: span 2;
        }

        .content-body .eb-field label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #4a3f40;
            margin-bottom: 8px;
            letter-spacing: .3px;
        }

        .content-body .eb-field label .req {
            color: #c9184a;
            font-weight: 700;
        }

        .content-body .eb-input-wrap {
            position: relative;
        }

        .content-body .eb-input-wrap > svg.eb-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #b09a9c;
            pointer-events: none;
            transition: color .3s;
            z-index: 2;
        }

        .content-body .eb-input-wrap:focus-within > svg.eb-icon {
            color: #8f1226;
        }

        .content-body .eb-field input {
            width: 100%;
            padding: 12px 14px 12px 44px;
            border: 1.5px solid #e6dcdc;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            color: #2b0509;
            background: #faf7f7;
            outline: none;
            transition: .3s;
            box-sizing: border-box;
        }

        .content-body .eb-field input:hover {
            border-color: #d3bbbb;
        }

        .content-body .eb-field input:focus {
            border-color: #8f1226;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(143,18,38,.12);
        }

        .content-body input.eb-mono {
            font-family: Consolas, Monaco, monospace;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .content-body .eb-cover-upload {
            display: flex;
            gap: 16px;
            align-items: center;
            background: #faf7f7;
            padding: 12px;
            border-radius: 12px;
            border: 1.5px dashed #e6dcdc;
        }

        .content-body .eb-cover-preview {
            width: 80px;
            height: 105px;
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

        .content-body .eb-cover-input {
            flex: 1;
        }

        .content-body .eb-cover-input input[type="file"] {
            width: 100%;
            padding: 8px 10px;
            font-size: 12px;
            background: #fff;
            border: 1px solid #e6dcdc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .content-body .eb-hint {
            font-size: 11.5px;
            color: #a29294;
            margin-top: 6px;
        }

        .content-body .eb-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-top: 26px;
            padding-top: 22px;
            border-top: 1px solid #f7ebeb;
        }

        .content-body .eb-btn {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: .25s;
        }

        .content-body .eb-btn svg {
            width: 17px;
            height: 17px;
        }

        .content-body .eb-btn-save {
            background: linear-gradient(
                135deg,
                #c9184a,
                #8f1226 60%,
                #5c0a14
            );
            color: #fff;
            box-shadow: 0 8px 18px rgba(143,18,38,.35);
        }

        .content-body .eb-btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(143,18,38,.45);
        }

        .content-body .eb-btn-save:disabled {
            opacity: .75;
            cursor: not-allowed;
            transform: none;
        }

        .content-body .eb-btn-batal {
            background: #fff;
            color: #8f1226;
            border: 1.5px solid #e6dcdc;
        }

        .content-body .eb-btn-batal:hover {
            background: #fdf3f3;
            border-color: #c9184a;
            transform: translateY(-2px);
        }

        .content-body .eb-spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            display: inline-block;
            animation: ebSpin .7s linear infinite;
        }

        @keyframes ebSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes ebFadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @media (max-width: 640px) {

            .content-body .eb-form-grid {
                grid-template-columns: 1fr;
            }

            .content-body .eb-span-2 {
                grid-column: span 1;
            }

            .content-body .eb-card-body {
                padding: 20px;
            }

            .content-body .eb-actions {
                flex-direction: column-reverse;
            }

            .content-body .eb-btn {
                width: 100%;
                justify-content: center;
            }

            .content-body .eb-title {
                font-size: 19px;
            }

            .content-body .eb-cover-upload {
                align-items: flex-start;
                flex-direction: column;
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

            <div class="eb-page-header">

                <h2 class="eb-title">

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>

                    <span>
                        Edit Data Buku
                        <small>
                            Perbarui informasi buku yang tersimpan
                        </small>
                    </span>

                </h2>

            </div>

            <div class="eb-card">

                <div class="eb-card-head">

                    <div class="eb-card-head-icon">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        </svg>

                    </div>

                    <div>

                        <h2>Formulir Edit Buku</h2>

                        <p>
                            Periksa kembali data sebelum menyimpan perubahan
                        </p>

                    </div>

                </div>

                <div class="eb-card-body">

                    <?php if (!empty($error)): ?>

                        <div class="eb-alert">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>

                            <span>
                                <?= e($error); ?>
                            </span>

                        </div>

                    <?php endif; ?>

                    <form
                        action=""
                        method="POST"
                        enctype="multipart/form-data"
                        id="ebForm"
                    >

                        <div class="eb-form-grid">

                            <!-- KODE -->

                            <div class="eb-field">

                                <label>
                                    Kode Buku
                                    <span class="req">*</span>
                                </label>

                                <div class="eb-input-wrap">

                                    <svg
                                        class="eb-icon"
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
                                        class="eb-mono"
                                        value="<?= e($kode_buku); ?>"
                                        required
                                        maxlength="20"
                                        autocomplete="off"
                                    >

                                </div>

                            </div>

                            <!-- STOK -->

                            <div class="eb-field">

                                <label>
                                    Jumlah Stok
                                    <span class="req">*</span>
                                </label>

                                <div class="eb-input-wrap">

                                    <svg
                                        class="eb-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z"/>
                                    </svg>

                                    <input
                                        type="number"
                                        name="stok"
                                        min="0"
                                        value="<?= e($stok); ?>"
                                        required
                                    >

                                </div>

                            </div>

                            <!-- JUDUL -->

                            <div class="eb-field eb-span-2">

                                <label>
                                    Judul Buku
                                    <span class="req">*</span>
                                </label>

                                <div class="eb-input-wrap">

                                    <svg
                                        class="eb-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                    </svg>

                                    <input
                                        type="text"
                                        name="judul"
                                        value="<?= e($judul); ?>"
                                        required
                                        maxlength="200"
                                    >

                                </div>

                            </div>

                            <!-- PENULIS -->

                            <div class="eb-field">

                                <label>
                                    Penulis
                                    <span class="req">*</span>
                                </label>

                                <div class="eb-input-wrap">

                                    <svg
                                        class="eb-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                    </svg>

                                    <input
                                        type="text"
                                        name="penulis"
                                        value="<?= e($penulis); ?>"
                                        required
                                        maxlength="100"
                                    >

                                </div>

                            </div>

                            <!-- KATEGORI -->

                            <div class="eb-field">

                                <label>
                                    Kategori
                                    <span class="req">*</span>
                                </label>

                                <div class="eb-input-wrap">

                                    <svg
                                        class="eb-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
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
                                        value="<?= e($kategori); ?>"
                                        required
                                        maxlength="50"
                                    >

                                    <datalist id="kategori_list">

                                        <?php foreach ($list_kategori as $kat): ?>

                                            <option value="<?= e($kat); ?>">

                                        <?php endforeach; ?>

                                    </datalist>

                                </div>

                            </div>

                            <!-- TAHUN -->

                            <div class="eb-field">

                                <label>
                                    Tahun Terbit
                                    <span class="req">*</span>
                                </label>

                                <div class="eb-input-wrap">

                                    <svg
                                        class="eb-icon"
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
                                        value="<?= e($tahun_terbit); ?>"
                                        min="1900"
                                        max="<?= $tahun_sekarang + 1; ?>"
                                        required
                                    >

                                </div>

                            </div>

                            <!-- PENERBIT -->

                            <div class="eb-field">

                                <label>
                                    Penerbit
                                    <span class="req">*</span>
                                </label>

                                <div class="eb-input-wrap">

                                    <svg
                                        class="eb-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="M3 21h18"/>
                                        <path d="M5 21V7l7-4 7 4v14"/>
                                        <path d="M9 21v-6h6v6"/>
                                    </svg>

                                    <input
                                        type="text"
                                        name="penerbit"
                                        value="<?= e($penerbit); ?>"
                                        required
                                        maxlength="100"
                                    >

                                </div>

                            </div>

                            <!-- COVER -->

                            <div class="eb-field eb-span-2">

                                <label>
                                    Sampul Buku
                                </label>

                                <div class="eb-cover-upload">

                                    <?php if (!empty($coverPath)): ?>

                                        <img
                                            id="imgPreview"
                                            class="eb-cover-preview"
                                            src="<?= e($coverPath); ?>"
                                            alt="Cover Buku"
                                        >

                                        <div
                                            id="previewPlaceholder"
                                            class="eb-cover-preview"
                                            style="display:none;"
                                        >
                                            No Cover
                                        </div>

                                    <?php else: ?>

                                        <img
                                            id="imgPreview"
                                            class="eb-cover-preview"
                                            src=""
                                            alt="Preview"
                                            style="display:none;"
                                        >

                                        <div
                                            id="previewPlaceholder"
                                            class="eb-cover-preview"
                                        >
                                            No Cover
                                        </div>

                                    <?php endif; ?>

                                    <div class="eb-cover-input">

                                        <input
                                            type="file"
                                            name="cover"
                                            id="coverInput"
                                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                        >

                                        <div class="eb-hint">
                                            Biarkan kosong jika tidak ingin
                                            mengganti sampul. Maksimal 2MB.
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- ACTION -->

                        <div class="eb-actions">

                            <button
                                type="submit"
                                id="ebBtnSave"
                                class="eb-btn eb-btn-save"
                            >

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                    <polyline points="17 21 17 13 7 13 7 21"/>
                                    <polyline points="7 3 7 8 15 8"/>
                                </svg>

                                Simpan Perubahan

                            </button>

                            <a
                                href="buku.php"
                                class="eb-btn eb-btn-batal"
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

/* =========================================================
   PREVIEW COVER
   ========================================================= */

document
    .getElementById('coverInput')
    .addEventListener('change', function (event) {

        const file = event.target.files[0];

        const preview =
            document.getElementById('imgPreview');

        const placeholder =
            document.getElementById('previewPlaceholder');

        if (!file) {
            return;
        }

        /* Maksimal 2MB */

        if (file.size > 2 * 1024 * 1024) {

            alert(
                'Ukuran gambar maksimal 2 MB.'
            );

            event.target.value = '';

            return;
        }

        /* Validasi MIME */

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type)) {

            alert(
                'Format gambar harus JPG, JPEG, PNG, atau WEBP.'
            );

            event.target.value = '';

            return;
        }

        const reader = new FileReader();

        reader.onload = function (e) {

            preview.src = e.target.result;

            preview.style.display = 'block';

            placeholder.style.display = 'none';
        };

        reader.readAsDataURL(file);

    });


/* =========================================================
   TOMBOL SIMPAN
   ========================================================= */

document
    .getElementById('ebForm')
    .addEventListener('submit', function () {

        const btn =
            document.getElementById('ebBtnSave');

        btn.disabled = true;

        btn.innerHTML =
            '<span class="eb-spinner"></span> MENYIMPAN...';

    });

</script>

</body>
</html>