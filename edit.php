<?php
require_once 'koneksi.php';
require_once 'auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Data buku tidak ditemukan.'];
    header('Location: buku.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = trim($_POST['kode_buku'] ?? '');
    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $tahun = trim($_POST['tahun_terbit'] ?? '');
    $penerbit = trim($_POST['penerbit'] ?? '');
    $gambarUrl = trim($_POST['gambar_url'] ?? '');
    $hapusGambar = isset($_POST['hapus_gambar']);

    $gambar = $book['gambar'];

    if ($kode === '') $errors[] = 'Kode buku wajib diisi.';
    if ($judul === '') $errors[] = 'Judul buku wajib diisi.';
    if ($penulis === '') $errors[] = 'Penulis wajib diisi.';
    if ($kategori === '') $errors[] = 'Kategori wajib diisi.';
    if ($tahun === '' || !filter_var($tahun, FILTER_VALIDATE_INT) || (int)$tahun < 1000 || (int)$tahun > 9999) {
        $errors[] = 'Tahun terbit harus berupa tahun yang valid.';
    }
    if ($penerbit === '') $errors[] = 'Penerbit wajib diisi.';

    if ($gambarUrl !== '' && !filter_var($gambarUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'URL gambar tidak valid.';
    }

    if ($hapusGambar) {
        if ($gambar && str_starts_with($gambar, 'assets/uploads/')) {
            $oldPath = __DIR__ . '/' . $gambar;
            if (is_file($oldPath)) @unlink($oldPath);
        }
        $gambar = null;
    } elseif ($gambarUrl !== '') {
        if ($gambar && str_starts_with($gambar, 'assets/uploads/')) {
            $oldPath = __DIR__ . '/' . $gambar;
            if (is_file($oldPath)) @unlink($oldPath);
        }
        $gambar = $gambarUrl;
    }

    if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] !== UPLOAD_ERR_NO_FILE) {
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
            $mime = $finfo->file($_FILES['gambar_file']['tmp_name']);

            if (!isset($allowed[$mime])) {
                $errors[] = 'Format gambar harus JPG, PNG, WEBP, atau GIF.';
            } elseif ($_FILES['gambar_file']['size'] > 2 * 1024 * 1024) {
                $errors[] = 'Ukuran gambar maksimal 2 MB.';
            } else {
                $uploadDir = __DIR__ . '/assets/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $filename = 'cover_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                $target = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['gambar_file']['tmp_name'], $target)) {
                    if ($gambar && str_starts_with($gambar, 'assets/uploads/')) {
                        $oldPath = __DIR__ . '/' . $gambar;
                        if (is_file($oldPath)) @unlink($oldPath);
                    }
                    $gambar = 'assets/uploads/' . $filename;
                } else {
                    $errors[] = 'Gambar tidak dapat disimpan.';
                }
            }
        }
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                'UPDATE books
                 SET kode_buku = ?, judul = ?, penulis = ?, kategori = ?, tahun_terbit = ?, penerbit = ?, gambar = ?
                 WHERE id = ?'
            );
            $stmt->execute([$kode, $judul, $penulis, $kategori, $tahun, $penerbit, $gambar, $id]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Buku berhasil diperbarui.'];
            header('Location: buku.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = $e->getCode() === '23000'
                ? 'Kode buku sudah digunakan. Gunakan kode lain.'
                : 'Data gagal diperbarui.';
        }
    }

    $book = array_merge($book, [
        'kode_buku' => $kode,
        'judul' => $judul,
        'penulis' => $penulis,
        'kategori' => $kategori,
        'tahun_terbit' => $tahun,
        'penerbit' => $penerbit,
        'gambar' => $gambar
    ]);
}

$currentImage = $book['gambar'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Perpus API</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div>
                <h1>Edit Buku</h1>
                <p class="muted">Perbarui data buku dan sampul jika diperlukan.</p>
            </div>
            <a href="buku.php" class="btn btn-light">← Kembali</a>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <strong>Periksa data:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="panel form-panel">
            <form method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Kode Buku *</label>
                        <input type="text" name="kode_buku" maxlength="20" required value="<?= e($book['kode_buku']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Judul Buku *</label>
                        <input type="text" name="judul" maxlength="150" required value="<?= e($book['judul']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Penulis *</label>
                        <input type="text" name="penulis" maxlength="100" required value="<?= e($book['penulis']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Kategori *</label>
                        <input type="text" name="kategori" maxlength="50" required value="<?= e($book['kategori']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Tahun Terbit *</label>
                        <input type="number" name="tahun_terbit" min="1000" max="9999" required value="<?= e((string)$book['tahun_terbit']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Penerbit *</label>
                        <input type="text" name="penerbit" maxlength="100" required value="<?= e($book['penerbit']) ?>">
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label>Ganti Gambar Sampul</label>
                    <p class="form-help">Upload dari folder komputer atau masukkan URL baru. Jika tidak diganti, gambar lama tetap digunakan.</p>

                    <div class="source-box active">
                        <input type="file" name="gambar_file" accept="image/jpeg,image/png,image/webp,image/gif">
                        <small>Format JPG, PNG, WEBP, GIF. Maksimal 2 MB.</small>
                    </div>

                    <div class="source-box active" style="margin-top:10px;">
                        <input type="url" name="gambar_url" placeholder="https://contoh.com/sampul.jpg">
                        <small>Jika diisi, URL akan menggantikan gambar lama.</small>
                    </div>

                    <?php if ($currentImage): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="hapus_gambar" value="1">
                            Hapus gambar yang sekarang
                        </label>
                    <?php endif; ?>
                </div>

                <div class="current-cover">
                    <div class="preview-title">Gambar Saat Ini</div>
                    <div class="cover-preview">
                        <?php if ($currentImage): ?>
                            <img src="<?= e($currentImage) ?>" alt="Sampul saat ini" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="cover-preview placeholder" style="display:none">⚠️<span>Gambar tidak dapat dimuat</span></div>
                        <?php else: ?>
                            <div class="cover-preview placeholder">📖<span>Belum ada gambar</span></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="buku.php" class="btn btn-light">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>