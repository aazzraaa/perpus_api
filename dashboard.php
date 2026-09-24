<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}

require_once 'koneksi.php';

// Menghitung Statistik dari Database
 $total_buku = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM books"))['total'];
 $total_kategori = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(DISTINCT kategori) as total FROM books"))['total'];
 $total_penulis = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(DISTINCT penulis) as total FROM books"))['total'];
 $terbit_terbaru = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT MAX(tahun_terbit) as max_tahun FROM books"))['max_tahun'];

// Query Data untuk Diagram Kategori (Bar Chart)
 $query_kategori = mysqli_query($koneksi, "SELECT kategori, COUNT(*) as jumlah FROM books GROUP BY kategori");
 $kat_labels = [];
 $kat_counts = [];
while ($row = mysqli_fetch_assoc($query_kategori)) {
    $kat_labels[] = $row['kategori'];
    $kat_counts[] = $row['jumlah'];
}

// Query Data untuk Kurva Tren Buku Berdasarkan Tahun Terbit (Line Chart)
 $query_tahun = mysqli_query($koneksi, "SELECT tahun_terbit, COUNT(*) as jumlah FROM books GROUP BY tahun_terbit ORDER BY tahun_terbit ASC");
 $thn_labels = [];
 $thn_counts = [];
while ($row = mysqli_fetch_assoc($query_tahun)) {
    $thn_labels[] = $row['tahun_terbit'];
    $thn_counts[] = $row['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — Perpustakaan Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #faf5f5 0%, #f7ecec 50%, #fdf7f2 100%);
            min-height: 100vh;
            color: #2b0509;
        }

        /* ====== LAYOUT ====== */
        .app-layout { display: flex; min-height: 100vh; }

        .main-content {
            flex: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .content-body { padding: 28px 32px 40px; }

        /* ====== HEADER HALAMAN ====== */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 26px;
            animation: fadeUp .6s ease both;
        }
        .page-title {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(90deg, #4a0812, #8f1226, #c9184a);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .page-title svg { width: 30px; height: 30px; color: #8f1226; -webkit-text-fill-color: #8f1226; }

        .date-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            border: 1px solid #f0dcdc;
            color: #8f1226;
            font-size: 13px;
            font-weight: 500;
            padding: 9px 16px;
            border-radius: 99px;
            box-shadow: 0 3px 10px rgba(143,18,38,.07);
        }
        .date-badge svg { width: 16px; height: 16px; }

        /* ====== KARTU STATISTIK GRADASI ====== */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
        }

        .stat-card {
            position: relative;
            border-radius: 18px;
            padding: 24px 22px;
            color: #fff;
            overflow: hidden;
            cursor: default;
            box-shadow: 0 12px 28px rgba(74,8,18,.28);
            transition: transform .35s cubic-bezier(.22,1,.36,1), box-shadow .35s;
            animation: fadeUp .7s ease both;
        }
        .stat-card:hover { transform: translateY(-6px); box-shadow: 0 20px 38px rgba(74,8,18,.4); }

        .stat-card::after {
            content: "";
            position: absolute;
            top: -40px; right: -40px;
            width: 130px; height: 130px;
            background: rgba(255,255,255,.12);
            border-radius: 50%;
            transition: transform .5s;
        }
        .stat-card:hover::after { transform: scale(1.4); }

        .stat-card.c1 { background: linear-gradient(135deg, #c9184a 0%, #8f1226 60%, #5c0a14 100%); animation-delay: .05s; }
        .stat-card.c2 { background: linear-gradient(135deg, #a4133c 0%, #7a0e1c 60%, #4a0812 100%); animation-delay: .15s; }
        .stat-card.c3 { background: linear-gradient(135deg, #8f1226 0%, #5c0a14 60%, #3d060e 100%); animation-delay: .25s; }
        .stat-card.c4 { background: linear-gradient(135deg, #b3173f 0%, #800f2f 55%, #4a0812 100%); animation-delay: .35s; }

        .stat-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .stat-icon {
            width: 46px; height: 46px;
            border-radius: 13px;
            background: rgba(255,255,255,.18);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.25);
        }
        .stat-icon svg { width: 23px; height: 23px; color: #ffd8a8; }
        .stat-trend {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            background: rgba(255,255,255,.15);
            padding: 5px 10px;
            border-radius: 99px;
        }
        .stat-label { font-size: 13px; font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase; opacity: .85; }
        .stat-number {
            font-size: 34px;
            font-weight: 700;
            line-height: 1.2;
            margin-top: 4px;
            text-shadow: 0 2px 10px rgba(0,0,0,.15);
        }

        /* ====== KARTU CHART ====== */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-top: 26px;
        }

        .chart-card {
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(74,8,18,.08);
            border: 1px solid #f7ebeb;
            transition: transform .3s, box-shadow .3s;
            animation: fadeUp .7s ease both;
        }
        .chart-card:nth-child(1) { animation-delay: .45s; }
        .chart-card:nth-child(2) { animation-delay: .55s; }
        .chart-card:hover { transform: translateY(-4px); box-shadow: 0 14px 32px rgba(74,8,18,.13); }

        .chart-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }
        .chart-head-icon {
            width: 38px; height: 38px;
            border-radius: 11px;
            background: linear-gradient(135deg, #c9184a, #8f1226);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 12px rgba(143,18,38,.3);
            flex-shrink: 0;
        }
        .chart-head-icon svg { width: 19px; height: 19px; color: #ffd8a8; }
        .chart-head h3 { font-size: 15.5px; font-weight: 600; color: #3d060e; }
        .chart-head p  { font-size: 12px; color: #a29294; margin-top: 1px; }

        .chart-wrap { position: relative; height: 300px; }

        /* ====== BANNER SELAMAT DATANG ====== */
        .welcome-banner {
            margin-top: 26px;
            border-radius: 18px;
            padding: 28px 30px;
            background: linear-gradient(120deg, #3d060e 0%, #5c0a14 35%, #8f1226 75%, #a4133c 100%);
            background-size: 200% 200%;
            animation: bannerShift 10s ease infinite, fadeUp .7s .65s ease both;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 14px 34px rgba(61,6,14,.35);
        }
        @keyframes bannerShift {
            0%, 100% { background-position: 0% 50%; }
            50%      { background-position: 100% 50%; }
        }
        .welcome-banner::before {
            content: "";
            position: absolute;
            right: -60px; top: -60px;
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(233,196,106,.22), transparent 70%);
            border-radius: 50%;
        }
        .welcome-banner h3 {
            font-size: 19px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .welcome-banner h3 svg { width: 24px; height: 24px; color: #e9c46a; }
        .welcome-banner p {
            margin-top: 10px;
            font-size: 14px;
            line-height: 1.7;
            color: rgba(255,255,255,.82);
            max-width: 720px;
        }
        .welcome-banner a {
            color: #ffd8a8;
            font-weight: 600;
            text-decoration: none;
            border-bottom: 1.5px solid rgba(255,216,168,.5);
            transition: color .3s, border-color .3s;
        }
        .welcome-banner a:hover { color: #fff; border-color: #fff; }

        /* Shortcut chips */
        .shortcut-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }
        .shortcut-chip {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            backdrop-filter: blur(4px);
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 10px 18px;
            border-radius: 99px;
            transition: background .3s, transform .3s, border-color .3s;
        }
        .shortcut-chip svg { width: 16px; height: 16px; color: #ffd8a8; }
        .shortcut-chip:hover {
            background: rgba(255,255,255,.24);
            border-color: rgba(255,255,255,.45);
            transform: translateY(-2px);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: none; }
        }

        /* ====== RESPONSIVE ====== */
        @media (max-width: 992px) {
            .main-content { margin-left: 0; }
        }
        @media (max-width: 640px) {
            .content-body { padding: 20px 16px 32px; }
            .charts-grid { grid-template-columns: 1fr; }
            .stat-number { font-size: 28px; }
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
                <div class="page-header">
                    <h2 class="page-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/>
                            <rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>
                        </svg>
                        Dashboard Statistik
                    </h2>
                    <span class="date-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <?= date('d M Y'); ?>
                    </span>
                </div>

                <!-- Cards Statistik -->
                <div class="card-grid">
                    <div class="stat-card c1">
                        <div class="stat-top">
                            <div class="stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                                </svg>
                            </div>
                            <span class="stat-trend">Koleksi</span>
                        </div>
                        <div class="stat-label">Total Buku</div>
                        <div class="stat-number" data-count="<?= $total_buku; ?>">0</div>
                    </div>

                    <div class="stat-card c2">
                        <div class="stat-top">
                            <div class="stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                                </svg>
                            </div>
                            <span class="stat-trend">Ragam</span>
                        </div>
                        <div class="stat-label">Kategori</div>
                        <div class="stat-number" data-count="<?= $total_kategori; ?>">0</div>
                    </div>

                    <div class="stat-card c3">
                        <div class="stat-top">
                            <div class="stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                </svg>
                            </div>
                            <span class="stat-trend">Kontributor</span>
                        </div>
                        <div class="stat-label">Data Penulis</div>
                        <div class="stat-number" data-count="<?= $total_penulis; ?>">0</div>
                    </div>

                    <div class="stat-card c4">
                        <div class="stat-top">
                            <div class="stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </div>
                            <span class="stat-trend">Terbaru</span>
                        </div>
                        <div class="stat-label">Terbit Terbaru</div>
                        <div class="stat-number" data-count="<?= $terbit_terbaru ?? 0; ?>" data-plain="1"><?= $terbit_terbaru ?? '-'; ?></div>
                    </div>
                </div>

                <!-- Section Diagram & Kurva -->
                <div class="charts-grid">
                    <!-- Diagram Batang Kategori Buku -->
                    <div class="chart-card">
                        <div class="chart-head">
                            <div class="chart-head-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="20" x2="22" y2="20"/><line x1="12" y1="16" x2="18" y2="16"/>
                                    <line x1="12" y1="8" x2="15" y2="8"/><line x1="12" y1="4" x2="13" y2="4"/>
                                    <line x1="4" y1="20" x2="4" y2="4"/>
                                </svg>
                            </div>
                            <div>
                                <h3>Jumlah Buku Per Kategori</h3>
                                <p>Sebaran koleksi berdasarkan klasifikasi</p>
                            </div>
                        </div>
                        <div class="chart-wrap"><canvas id="kategoriChart"></canvas></div>
                    </div>

                    <!-- Kurva Tren Tahun Terbit -->
                    <div class="chart-card">
                        <div class="chart-head">
                            <div class="chart-head-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                    <polyline points="17 6 23 6 23 12"/>
                                </svg>
                            </div>
                            <div>
                                <h3>Kurva Tren Terbit Buku</h3>
                                <p>Perkembangan jumlah terbitan per tahun</p>
                            </div>
                        </div>
                        <div class="chart-wrap"><canvas id="trenChart"></canvas></div>
                    </div>
                </div>

                <!-- Banner Selamat Datang -->
                <div class="welcome-banner">
                    <h3>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2l2.4 4.86 5.36.78-3.88 3.78.92 5.34L12 14.24l-4.8 2.52.92-5.34L4.24 7.64l5.36-.78L12 2z"/>
                        </svg>
                        Selamat Datang di Sistem Kelola Perpustakaan
                    </h3>
                    <p>
                        Gunakan menu di sidebar untuk mengelola <a href="buku.php">Data Buku</a>, mengorganisir kategori,
                        memantau transaksi peminjaman, atau mencetak laporan Excel/PDF. Semua data ditampilkan secara real-time
                        dari basis data perpustakaan.
                    </p>
                    <div class="shortcut-row">
                        <a href="buku.php" class="shortcut-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                            </svg>
                            Kelola Buku
                        </a>
                       
                        <a href="pengaturan.php" class="shortcut-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                            Pengaturan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script Render Chart.js dengan Gradasi -->
    <script>
        // Data dari PHP
        const katLabels = <?= json_encode($kat_labels); ?>;
        const katCounts = <?= json_encode($kat_counts); ?>;
        const thnLabels = <?= json_encode($thn_labels); ?>;
        const thnCounts = <?= json_encode($thn_counts); ?>;

        // Palet warna maroon
        const MAROON      = '#8f1226';
        const MAROON_MUDA = '#c9184a';
        const MAROON_TUA  = '#4a0812';
        const EMAS        = '#e9c46a';

        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.color = '#8a7f80';

        // ====== BAR CHART: Gradasi Maroon ======
        const ctxKategori = document.getElementById('kategoriChart').getContext('2d');
        const gradBar = ctxKategori.createLinearGradient(0, 0, 0, 320);
        gradBar.addColorStop(0,   MAROON_MUDA);   // merah marun terang di atas
        gradBar.addColorStop(0.6, MAROON);        // marun tengah
        gradBar.addColorStop(1,   MAROON_TUA);    // marun gelap di bawah

        const gradBarHover = ctxKategori.createLinearGradient(0, 0, 0, 320);
        gradBarHover.addColorStop(0, '#e5383b');
        gradBarHover.addColorStop(1, '#7a0e1c');

        new Chart(ctxKategori, {
            type: 'bar',
            data: {
                labels: katLabels,
                datasets: [{
                    label: 'Jumlah Buku',
                    data: katCounts,
                    backgroundColor: gradBar,
                    hoverBackgroundColor: gradBarHover,
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 55
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1200, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#3d060e',
                        titleColor: '#ffd8a8',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: false,
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y} buku`
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11.5 } } },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, font: { size: 11.5 } },
                        grid: { color: 'rgba(143,18,38,.07)' }
                    }
                }
            }
        });

        // ====== LINE CHART: Gradasi Garis & Area Fill ======
        const ctxTren = document.getElementById('trenChart').getContext('2d');

        function gradGaris(ctx, area) {
            const g = ctx.createLinearGradient(0, area.top, 0, area.bottom);
            g.addColorStop(0, MAROON_MUDA);
            g.addColorStop(1, MAROON_TUA);
            return g;
        }
        function gradArea(ctx, area) {
            const g = ctx.createLinearGradient(0, area.top, 0, area.bottom);
            g.addColorStop(0,   'rgba(201, 24, 74, .35)');  // marun pekat di atas
            g.addColorStop(0.5, 'rgba(143, 18, 38, .15)');
            g.addColorStop(1,   'rgba(143, 18, 38, 0)');    // transparan di bawah
            return g;
        }

        new Chart(ctxTren, {
            type: 'line',
            data: {
                labels: thnLabels,
                datasets: [{
                    label: 'Jumlah Terbitan',
                    data: thnCounts,
                    borderColor: ctx => {
                        const { chart } = ctx;
                        return chart.chartArea ? gradGaris(chart.ctx, chart.chartArea) : MAROON_MUDA;
                    },
                    backgroundColor: ctx => {
                        const { chart } = ctx;
                        return chart.chartArea ? gradArea(chart.ctx, chart.chartArea) : 'rgba(143,18,38,.1)';
                    },
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4.5,
                    pointHoverRadius: 6.5,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: MAROON,
                    pointBorderWidth: 2.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1200, easing: 'easeOutQuart' },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#3d060e',
                        titleColor: '#ffd8a8',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: false,
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y} buku terbit`
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11.5 } } },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, font: { size: 11.5 } },
                        grid: { color: 'rgba(143,18,38,.07)' }
                    }
                }
            }
        });

        // ====== ANIMASI ANGKA COUNTER ======
        document.querySelectorAll('.stat-number').forEach(el => {
            const target = parseInt(el.dataset.count) || 0;

            // Kolom tahun tidak perlu format ribuan
            const isPlain = el.dataset.plain === '1';
            const format  = n => isPlain ? String(n) : n.toLocaleString('id-ID');

            if (target === 0) { el.textContent = isPlain ? (el.textContent !== '0' ? el.textContent : '-') : '0'; return; }

            const durasi = 1400;
            const mulai  = performance.now();

            function update(sekarang) {
                const progress = Math.min((sekarang - mulai) / durasi, 1);
                const eased    = 1 - Math.pow(1 - progress, 3); // easeOutCubic
                el.textContent = format(Math.floor(eased * target));
                if (progress < 1) requestAnimationFrame(update);
                else el.textContent = format(target);
            }
            requestAnimationFrame(update);
        });
    </script>
</body>
</html>