<?php
/* =====================================================
   sidebar.php — Tema maroon, style menimpa style.css
   Struktur class asli dipertahankan agar kompatibel
   dengan semua halaman yang memuat style.css
   ===================================================== */

 $current_page = basename($_SERVER['SCRIPT_NAME']);

// ---- Helper ikon SVG profesional (Feather Icons) ----
if (!function_exists('sb_icon')) {
    function sb_icon($name) {
        $icons = [
            'bookopen' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
            'grid'     => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
            'book'     => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
            'tag'      => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
            'users'    => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'plus'     => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>',
            'excel'    => '<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="12" y1="3" x2="12" y2="21"/>',
            'pdf'      => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
            'gear'     => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
            'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        ];
        $path = $icons[$name] ?? '';
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
    }
}

// ---- Daftar menu: [file, label, ikon, tab_baru] — sesuai nama file Anda ----
 $menu = [
    ['dashboard.php',    'Dashboard',     'grid',  false],
    ['buku.php',         'Data Buku',     'book',  false],
     ['tambah.php',       'Tambah Buku',   'plus',  false],
    ['export_excel.php', 'Export Excel',  'excel', true],
    ['export_pdf.php',   'Export PDF',    'pdf',   true],
    ['pengaturan.php',   'Pengaturan',    'gear',  false],
];
?>

<style>
    /* ===== SIDEBAR MAROON — menimpa style.css karena dimuat belakangan ===== */
    .sidebar {
        position: fixed; top: 0; left: 0; bottom: 0;
        width: 260px; z-index: 1000;
        background: linear-gradient(180deg, #3d060e 0%, #5c0a14 55%, #7a0e1c 100%);
        display: flex; flex-direction: column; justify-content: space-between;
        box-shadow: 4px 0 24px rgba(61,6,14,.35);
        overflow-y: auto;
        transition: transform .35s cubic-bezier(.22,1,.36,1);
    }
    .sidebar::after {
        content: ""; position: absolute; top: 0; right: 0; bottom: 0; width: 1px;
        background: linear-gradient(180deg, transparent, rgba(233,196,106,.35), transparent);
        pointer-events: none;
    }
    .sidebar::-webkit-scrollbar { width: 5px; }
    .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 99px; }

    /* Layout konten: beri jarak dari sidebar fixed */
    .main-content { margin-left: 260px !important; }

    /* ===== BRAND ===== */
    .sidebar-brand {
        display: flex; align-items: center; gap: 12px;
        padding: 22px 20px 18px;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .sidebar-brand .brand-logo {
        width: 44px; height: 44px; border-radius: 13px; flex-shrink: 0;
        background: linear-gradient(135deg, #c9184a, #8f1226);
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 6px 16px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.2);
    }
    .sidebar-brand .brand-logo svg { width: 22px; height: 22px; color: #e9c46a; }
    .sidebar-brand h2 {
        font-size: 16px; font-weight: 600; color: #fff;
        line-height: 1.25; letter-spacing: .3px;
    }
    .sidebar-brand h2 span {
        display: block; font-size: 10px; font-weight: 500;
        letter-spacing: 2.5px; text-transform: uppercase;
        color: rgba(255,255,255,.5); margin-top: 2px;
    }

    /* ===== MENU ===== */
    .sidebar-menu { list-style: none; padding: 14px; margin: 0; }
    .sidebar-menu li { margin-bottom: 4px; }
    .sb-group {
        font-size: 10px; font-weight: 600; letter-spacing: 2.5px;
        text-transform: uppercase; color: rgba(255,255,255,.38);
        margin: 14px 12px 8px;
    }
    .sidebar-menu a {
        display: flex; align-items: center; gap: 12px;
        padding: 11px 14px; border-radius: 11px;
        color: rgba(255,255,255,.75);
        font-size: 13.5px; font-weight: 400; text-decoration: none;
        position: relative;
        transition: background .25s, color .25s, transform .25s;
    }
    .sidebar-menu a svg { width: 18px; height: 18px; flex-shrink: 0; opacity: .85; transition: opacity .25s, color .25s; }
    .sidebar-menu a:hover {
        background: rgba(255,255,255,.09); color: #fff; transform: translateX(3px);
    }
    .sidebar-menu a.active {
        background: linear-gradient(90deg, rgba(201,24,74,.5), rgba(143,18,38,.15));
        color: #fff; font-weight: 500;
    }
    .sidebar-menu a.active::before {
        content: ""; position: absolute; left: 0; top: 9px; bottom: 9px;
        width: 4px; border-radius: 99px;
        background: #e9c46a; box-shadow: 0 0 12px rgba(233,196,106,.9);
    }
    .sidebar-menu a.active svg { color: #e9c46a; opacity: 1; }

    /* Logout merah lembut */
    .sidebar-menu a.logout-link { color: rgba(255,185,185,.9); }
    .sidebar-menu a.logout-link svg { color: rgba(255,150,150,.9); }
    .sidebar-menu a.logout-link:hover { background: rgba(220,40,60,.28); color: #fff; transform: none; }

    /* ===== TOMBOL BURGER (khusus mobile) ===== */
    .sb-burger {
        position: fixed; top: 13px; left: 13px; z-index: 1001;
        width: 42px; height: 42px; border-radius: 11px;
        border: 1.5px solid #f0dcdc; background: #fff; color: #8f1226;
        display: none; align-items: center; justify-content: center;
        cursor: pointer; box-shadow: 0 4px 12px rgba(61,6,14,.18);
    }
    .sb-burger svg { width: 20px; height: 20px; }

    .sb-overlay {
        position: fixed; inset: 0; background: rgba(43,5,9,.55);
        z-index: 999; opacity: 0; visibility: hidden;
        transition: opacity .3s, visibility .3s;
    }
    .sb-overlay.show { opacity: 1; visibility: visible; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .sidebar { transform: translateX(-105%); }
        .sidebar.open { transform: translateX(0); }
        .main-content { margin-left: 0 !important; }
        .sb-burger { display: flex; }
    }
</style>

<!-- Tombol buka menu di layar kecil -->
<button type="button" class="sb-burger" onclick="toggleSidebar()" aria-label="Buka menu">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
</button>

<!-- Overlay penutup sidebar di layar kecil -->
<div class="sb-overlay" id="sbOverlay" onclick="toggleSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <div>
        <div class="sidebar-brand">
            <div class="brand-logo"><?= sb_icon('bookopen'); ?></div>
            <h2>Perpus Admin<span>Perpustakaan</span></h2>
        </div>

        <ul class="sidebar-menu">
            <li class="sb-group">Menu Utama</li>
            <?php foreach ($menu as $i => $item):
                if ($i === 5) echo '<li class="sb-group">Laporan</li>'; // pemisah sebelum Export
                $aktif = ($current_page === $item[0]) ? 'active' : '';
                $target = $item[3] ? ' target="_blank"' : '';
            ?>
                <li>
                    <a href="<?= $item[0]; ?>"<?= $target; ?> class="<?= $aktif; ?>">
                        <?= sb_icon($item[2]); ?><span><?= $item[1]; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Menu Logout tetap dipisah di bawah -->
    <ul class="sidebar-menu">
        <li>
            <a href="logout.php" class="logout-link" onclick="return confirm('Yakin ingin logout?')">
                <?= sb_icon('logout'); ?><span>Logout</span>
            </a>
        </li>
    </ul>
</aside>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sbOverlay').classList.toggle('show');
}
</script>