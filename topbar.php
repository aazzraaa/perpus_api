<?php
/* =====================================================
   topbar.php — Topbar tema maroon (menimpa style.css)
   Struktur asli dipertahankan: .topbar, .user-profile, .avatar
   ===================================================== */

 $admin_user = $_SESSION['admin_user'] ?? 'Admin';
 $inisial    = strtoupper(substr($admin_user, 0, 1));

// Foto profil (jika ada)
 $foto     = $_SESSION['admin_foto'] ?? '';
 $ada_foto = !empty($foto) && file_exists('uploads/' . $foto);

// Tanggal hari ini dalam Bahasa Indonesia
 $hari  = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
 $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
 $tanggal_hari_ini = $hari[date('l')] . ', ' . date('j') . ' ' . $bulan[(int)date('n')] . ' ' . date('Y');
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* Semua style di-scope di bawah .topbar agar tidak memengaruhi halaman lain */
    .topbar {
        position: sticky; top: 0; z-index: 900;
        display: flex; align-items: center;
        min-height: 66px;
        padding: 10px 28px;
        background: rgba(255,255,255,.92);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid #f3e2e2;
        box-shadow: 0 2px 14px rgba(61,6,14,.06);
        font-family: 'Poppins', 'Segoe UI', sans-serif;
        animation: tbIn .5s ease both;
    }
    @keyframes tbIn {
        from { opacity: 0; transform: translateY(-10px); }
        to   { opacity: 1; transform: none; }
    }

    .topbar .user-profile {
        display: flex; align-items: center; justify-content: space-between;
        gap: 14px; width: 100%;
    }
    .topbar .user-greet {
        font-size: 14.5px; color: #6b5b5d; white-space: nowrap;
    }
    .topbar .user-greet strong { color: #8f1226; font-weight: 600; }

    .topbar .user-actions {
        display: flex; align-items: center; gap: 12px; margin-left: auto;
    }

    /* Badge tanggal */
    .topbar .tb-date {
        display: inline-flex; align-items: center; gap: 8px;
        font-size: 12.5px; font-weight: 500; color: #8f1226;
        background: #fdf3f3; border: 1px solid #f0dcdc;
        padding: 8px 15px; border-radius: 99px; white-space: nowrap;
    }
    .topbar .tb-date svg { width: 15px; height: 15px; }

    /* Tombol ikon (pengaturan) */
    .topbar .tb-btn {
        width: 40px; height: 40px; border-radius: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #fdf3f3; border: 1.5px solid #f0dcdc; color: #8f1226;
        text-decoration: none;
        transition: background .25s, color .25s, box-shadow .25s, transform .25s;
    }
    .topbar .tb-btn svg { width: 18px; height: 18px; }
    .topbar .tb-btn:hover {
        background: linear-gradient(135deg, #c9184a, #8f1226);
        color: #fff; border-color: transparent;
        box-shadow: 0 6px 14px rgba(143,18,38,.35);
        transform: translateY(-2px);
    }

    /* Tombol logout */
    .topbar .tb-logout {
        height: 40px; padding: 0 16px; border-radius: 12px;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        font-size: 13px; font-weight: 600; font-family: inherit;
        background: #fff; border: 1.5px solid #f0dcdc; color: #8f1226;
        text-decoration: none;
        transition: background .25s, color .25s, box-shadow .25s, transform .25s;
    }
    .topbar .tb-logout svg { width: 15px; height: 15px; }
    .topbar .tb-logout:hover {
        background: linear-gradient(135deg, #c9184a, #8f1226);
        color: #fff; border-color: transparent;
        box-shadow: 0 6px 14px rgba(143,18,38,.35);
        transform: translateY(-2px);
    }

    /* Avatar: foto profil atau inisial */
    .topbar .avatar {
        position: relative;
        width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, #c9184a, #5c0a14);
        color: #fff; font-size: 17px; font-weight: 600;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid #fff;
        outline: 2px solid rgba(233,196,106,.65);
        box-shadow: 0 4px 12px rgba(143,18,38,.4);
        cursor: default; user-select: none;
        transition: transform .25s, box-shadow .25s;
    }
    .topbar .avatar:hover {
        transform: scale(1.07);
        box-shadow: 0 7px 18px rgba(143,18,38,.5);
    }
    .topbar .avatar img {
        width: 100%; height: 100%;
        object-fit: cover; border-radius: 50%; display: block;
    }
    /* Titik status hijau di pojok avatar */
    .topbar .avatar-dot {
        position: absolute; right: -2px; bottom: -2px;
        width: 12px; height: 12px; border-radius: 50%;
        background: #2e9e5b; border: 2px solid #fff;
        box-shadow: 0 0 6px rgba(46,158,91,.8);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        /* Beri ruang untuk tombol burger dari sidebar.php */
        .topbar { padding: 10px 16px 10px 68px; }
        .topbar .tb-date { display: none; }
        .topbar .tb-logout { width: 40px; padding: 0; }
        .topbar .tb-logout span { display: none; } /* logout jadi ikon saja */
    }
</style>

<header class="topbar">
    <div class="user-profile">
        <!-- Sapaan admin -->
        <span class="user-greet">
            Halo, <strong><?= htmlspecialchars($admin_user); ?></strong>
        </span>

        <!-- Aksi di sisi kanan -->
        <div class="user-actions">
            <!-- Tanggal hari ini -->
            <span class="tb-date">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <?= $tanggal_hari_ini; ?>
            </span>

            <!-- Shortcut pengaturan -->
            <a href="pengaturan.php" class="tb-btn" title="Pengaturan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </a>

            <!-- Logout -->
            <a href="logout.php" class="tb-logout" onclick="return confirm('Yakin ingin logout?')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span>Keluar</span>
            </a>

            <!-- Avatar: foto profil atau inisial -->
            <div class="avatar" title="<?= htmlspecialchars($admin_user); ?>">
                <?php if ($ada_foto): ?>
                    <img src="uploads/<?= htmlspecialchars($foto); ?>" alt="Foto <?= htmlspecialchars($admin_user); ?>">
                <?php else: ?>
                    <?= htmlspecialchars($inisial); ?>
                <?php endif; ?>
                <span class="avatar-dot"></span>
            </div>
        </div>
    </div>
</header>