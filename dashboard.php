<?php

require_once 'koneksi.php';
require_once 'auth.php';

requireLogin();


// ======================================================
// DATA STATISTIK
// ======================================================

$totalBuku = (int)$pdo
    ->query('SELECT COUNT(*) FROM books')
    ->fetchColumn();

$totalKategori = (int)$pdo
    ->query('SELECT COUNT(DISTINCT kategori) FROM books')
    ->fetchColumn();

$totalPenulis = (int)$pdo
    ->query('SELECT COUNT(DISTINCT penulis) FROM books')
    ->fetchColumn();

$tahunTerbaru = $pdo
    ->query('SELECT MAX(tahun_terbit) FROM books')
    ->fetchColumn() ?: '-';

$totalTerbaru = (int)$pdo
    ->query(
        'SELECT COUNT(*) 
         FROM books 
         WHERE tahun_terbit = (
             SELECT MAX(tahun_terbit) FROM books
         )'
    )
    ->fetchColumn();


// ======================================================
// DATA KATEGORI
// ======================================================

$kategoriRows = $pdo
    ->query(
        'SELECT kategori, COUNT(*) AS jumlah
         FROM books
         GROUP BY kategori
         ORDER BY jumlah DESC, kategori ASC'
    )
    ->fetchAll();


// ======================================================
// DATA PER TAHUN
// ======================================================

$tahunRows = $pdo
    ->query(
        'SELECT tahun_terbit, COUNT(*) AS jumlah
         FROM books
         GROUP BY tahun_terbit
         ORDER BY tahun_terbit ASC'
    )
    ->fetchAll();


// ======================================================
// DATA BUKU TERBARU
// ======================================================

$latestBooks = $pdo
    ->query(
        'SELECT *
         FROM books
         ORDER BY id DESC
         LIMIT 5'
    )
    ->fetchAll();


// ======================================================
// DATA UNTUK CHART
// ======================================================

$kategoriLabels = [];
$kategoriValues = [];

foreach ($kategoriRows as $row) {

    $kategoriLabels[] = $row['kategori'];
    $kategoriValues[] = (int)$row['jumlah'];

}


$tahunLabels = [];
$tahunValues = [];

foreach ($tahunRows as $row) {

    $tahunLabels[] = (string)$row['tahun_terbit'];
    $tahunValues[] = (int)$row['jumlah'];

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

    <title>Dashboard - Perpus API</title>

    <link rel="stylesheet" href="style.css">


    <style>

        /* =====================================================
           DASHBOARD
        ====================================================== */

        .dashboard-page {
            max-width: 100%;
        }


        /* =====================================================
           HEADER
        ====================================================== */

        .dashboard-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 25px;

        }


        .dashboard-heading {

            display: flex;

            align-items: flex-start;

            gap: 14px;

        }


        .dashboard-heading-icon {

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


        .dashboard-heading-icon svg {

            width: 23px;

            height: 23px;

            stroke-width: 1.8;

        }


        .dashboard-heading h1 {

            margin: 0 0 5px;

            color: #431019;

            font-size: 27px;

            line-height: 1.2;

            font-weight: 800;

            letter-spacing: -.4px;

        }


        .dashboard-heading p {

            margin: 0;

            color: #92747b;

            font-size: 13px;

            line-height: 1.5;

        }


        /* =====================================================
           HEADER BUTTON
        ====================================================== */

        .dashboard-logout {

            height: 40px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            padding: 0 14px;

            color: #a0374a;

            background: #fff0f2;

            border: 1px solid #f1d4d9;

            border-radius: 9px;

            text-decoration: none;

            font-size: 12px;

            font-weight: 700;

            transition: .2s ease;

        }


        .dashboard-logout svg {

            width: 16px;

            height: 16px;

        }


        .dashboard-logout:hover {

            background: #fbe0e4;

            transform: translateY(-1px);

        }


        /* =====================================================
           STAT CARDS
        ====================================================== */

        .dashboard-stats {

            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 14px;

            margin-bottom: 20px;

        }


        .dashboard-stat {

            position: relative;

            overflow: hidden;

            display: flex;

            align-items: center;

            gap: 13px;

            min-height: 105px;

            padding: 18px;

            background: #fff;

            border: 1px solid #eee1e4;

            border-radius: 14px;

            box-shadow:
                0 6px 22px rgba(70,12,24,.045);

            transition: .2s ease;

        }


        .dashboard-stat:hover {

            transform: translateY(-2px);

            box-shadow:
                0 10px 27px rgba(70,12,24,.08);

        }


        .dashboard-stat::after {

            content: "";

            position: absolute;

            right: -30px;

            bottom: -30px;

            width: 90px;

            height: 90px;

            border-radius: 50%;

            background: rgba(122,23,41,.035);

        }


        .stat-icon {

            width: 45px;

            height: 45px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

        }


        .stat-icon svg {

            width: 21px;

            height: 21px;

            stroke-width: 1.8;

        }


        .stat-icon.book {

            color: #7a1729;

            background: #fae7ea;

        }


        .stat-icon.category {

            color: #8b5a19;

            background: #fff4dc;

        }


        .stat-icon.author {

            color: #356b8a;

            background: #e8f3f8;

        }


        .stat-icon.year {

            color: #5d4a8a;

            background: #eeeafd;

        }


        .stat-content {

            min-width: 0;

            display: flex;

            flex-direction: column;

            gap: 4px;

        }


        .stat-label {

            color: #a08389;

            font-size: 10px;

            font-weight: 800;

            letter-spacing: .65px;

            text-transform: uppercase;

        }


        .stat-number {

            color: #48141d;

            font-size: 25px;

            line-height: 1;

            font-weight: 800;

        }


        .stat-description {

            color: #a08389;

            font-size: 10px;

        }


        /* =====================================================
           CHART GRID
        ====================================================== */

        .dashboard-chart-grid {

            display: grid;

            grid-template-columns:
                1fr 1.55fr;

            gap: 16px;

            margin-bottom: 16px;

        }


        .dashboard-panel {

            background: #fff;

            border: 1px solid #eee1e4;

            border-radius: 15px;

            box-shadow:
                0 7px 25px rgba(70,12,24,.05);

            overflow: hidden;

        }


        .dashboard-panel-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            padding: 17px 18px;

            border-bottom: 1px solid #f0e5e7;

        }


        .dashboard-panel-title {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .panel-title-icon {

            width: 34px;

            height: 34px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 9px;

            color: #7a1729;

            background: #fae7ea;

        }


        .panel-title-icon svg {

            width: 17px;

            height: 17px;

        }


        .dashboard-panel-header h2 {

            margin: 0 0 3px;

            color: #4a121b;

            font-size: 14px;

            font-weight: 800;

        }


        .dashboard-panel-header p {

            margin: 0;

            color: #a08389;

            font-size: 10px;

        }


        .chart-container {

            position: relative;

            height: 280px;

            padding: 18px;

        }


        .chart-container.line {

            height: 280px;

        }


        /* =====================================================
           BOTTOM GRID
        ====================================================== */

        .dashboard-bottom-grid {

            display: grid;

            grid-template-columns:
                1.25fr 1fr;

            gap: 16px;

        }


        /* =====================================================
           CATEGORY LIST
        ====================================================== */

        .category-list {

            padding: 5px 18px 17px;

        }


        .category-item {

            padding: 12px 0;

            border-bottom: 1px solid #f3e9eb;

        }


        .category-item:last-child {

            border-bottom: none;

        }


        .category-top {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            margin-bottom: 7px;

            color: #68434b;

            font-size: 11px;

        }


        .category-top strong {

            color: #7a1729;

            font-size: 11px;

        }


        .progress {

            height: 6px;

            overflow: hidden;

            background: #f5e9eb;

            border-radius: 10px;

        }


        .progress span {

            display: block;

            height: 100%;

            border-radius: inherit;

            background:
                linear-gradient(
                    90deg,
                    #661321,
                    #a9324b
                );

        }


        /* =====================================================
           LATEST BOOKS
        ====================================================== */

        .latest-list {

            padding: 4px 18px 17px;

        }


        .latest-row {

            display: flex;

            align-items: center;

            gap: 11px;

            padding: 10px 0;

            border-bottom: 1px solid #f3e9eb;

        }


        .latest-row:last-child {

            border-bottom: none;

        }


        .latest-cover {

            width: 39px;

            height: 50px;

            flex-shrink: 0;

            overflow: hidden;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 6px;

            background:
                linear-gradient(
                    145deg,
                    #f7e0e4,
                    #f2cbd2
                );

            border: 1px solid #ecd0d5;

            color: #8c394a;

        }


        .latest-cover img {

            width: 100%;

            height: 100%;

            object-fit: cover;

        }


        .latest-cover svg {

            width: 17px;

            height: 17px;

        }


        .latest-info {

            min-width: 0;

            display: flex;

            flex-direction: column;

            gap: 4px;

        }


        .latest-title {

            overflow: hidden;

            color: #48141d;

            font-size: 11px;

            font-weight: 750;

            white-space: nowrap;

            text-overflow: ellipsis;

        }


        .latest-meta {

            color: #a08389;

            font-size: 10px;

        }


        .latest-action {

            display: flex;

            align-items: center;

            justify-content: center;

            width: 30px;

            height: 30px;

            margin-left: auto;

            flex-shrink: 0;

            color: #7a1729;

            background: #fae7ea;

            border: 1px solid #efd3d8;

            border-radius: 8px;

            text-decoration: none;

        }


        .latest-action svg {

            width: 14px;

            height: 14px;

        }


        .latest-action:hover {

            background: #f6dce1;

        }


        /* =====================================================
           EMPTY
        ====================================================== */

        .dashboard-empty {

            padding: 35px 20px;

            text-align: center;

            color: #a08389;

            font-size: 11px;

        }


        /* =====================================================
           RESPONSIVE
        ====================================================== */

        @media (max-width: 1150px) {

            .dashboard-stats {

                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

            }


            .dashboard-chart-grid {

                grid-template-columns: 1fr;

            }


            .dashboard-bottom-grid {

                grid-template-columns: 1fr;

            }

        }


        @media (max-width: 650px) {

            .dashboard-header {

                align-items: flex-start;

                flex-direction: column;

            }


            .dashboard-stats {

                grid-template-columns: 1fr;

            }


            .dashboard-heading h1 {

                font-size: 23px;

            }


            .dashboard-logout {

                width: 100%;

            }


            .dashboard-panel-header {

                align-items: flex-start;

                flex-direction: column;

            }


            .chart-container {

                height: 240px;

                padding: 12px;

            }

        }

    </style>

</head>


<body>

<div class="app">

    <?php include 'sidebar.php'; ?>


    <main class="main-content">

        <div class="dashboard-page">


            <!-- =================================================
                 HEADER
            ================================================== -->

            <div class="dashboard-header">

                <div class="dashboard-heading">

                    <div class="dashboard-heading-icon">

                        <i data-lucide="layout-dashboard"></i>

                    </div>


                    <div>

                        <h1>
                            Dashboard
                        </h1>

                        <p>
                            Ringkasan dan statistik koleksi
                            Perpustakaan Perpus API.
                        </p>

                    </div>

                </div>


                <a
                    href="logout.php"
                    class="dashboard-logout"
                >

                    <i data-lucide="log-out"></i>

                    Logout

                </a>

            </div>


            <!-- =================================================
                 STATISTIK
            ================================================== -->

            <section class="dashboard-stats">


                <div class="dashboard-stat">

                    <div class="stat-icon book">

                        <i data-lucide="library"></i>

                    </div>


                    <div class="stat-content">

                        <span class="stat-label">
                            Total Buku
                        </span>

                        <span class="stat-number">
                            <?= $totalBuku ?>
                        </span>

                        <span class="stat-description">
                            Semua koleksi buku
                        </span>

                    </div>

                </div>


                <div class="dashboard-stat">

                    <div class="stat-icon category">

                        <i data-lucide="tags"></i>

                    </div>


                    <div class="stat-content">

                        <span class="stat-label">
                            Kategori
                        </span>

                        <span class="stat-number">
                            <?= $totalKategori ?>
                        </span>

                        <span class="stat-description">
                            Kategori berbeda
                        </span>

                    </div>

                </div>


                <div class="dashboard-stat">

                    <div class="stat-icon author">

                        <i data-lucide="users"></i>

                    </div>


                    <div class="stat-content">

                        <span class="stat-label">
                            Penulis
                        </span>

                        <span class="stat-number">
                            <?= $totalPenulis ?>
                        </span>

                        <span class="stat-description">
                            Penulis berbeda
                        </span>

                    </div>

                </div>


                <div class="dashboard-stat">

                    <div class="stat-icon year">

                        <i data-lucide="calendar-days"></i>

                    </div>


                    <div class="stat-content">

                        <span class="stat-label">
                            Terbit Terbaru
                        </span>

                        <span class="stat-number">
                            <?= e((string)$tahunTerbaru) ?>
                        </span>

                        <span class="stat-description">
                            <?= $totalTerbaru ?> buku pada tahun tersebut
                        </span>

                    </div>

                </div>


            </section>


            <!-- =================================================
                 GRAFIK
            ================================================== -->

            <section class="dashboard-chart-grid">


                <!-- GRAFIK KATEGORI -->

                <div class="dashboard-panel">

                    <div class="dashboard-panel-header">

                        <div class="dashboard-panel-title">

                            <div class="panel-title-icon">

                                <i data-lucide="pie-chart"></i>

                            </div>


                            <div>

                                <h2>
                                    Distribusi Kategori
                                </h2>

                                <p>
                                    Jumlah buku berdasarkan kategori
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="chart-container">

                        <canvas id="categoryChart"></canvas>

                    </div>

                </div>


                <!-- GRAFIK TAHUN -->

                <div class="dashboard-panel">

                    <div class="dashboard-panel-header">

                        <div class="dashboard-panel-title">

                            <div class="panel-title-icon">

                                <i data-lucide="chart-line"></i>

                            </div>


                            <div>

                                <h2>
                                    Tren Tahun Terbit
                                </h2>

                                <p>
                                    Perkembangan jumlah buku berdasarkan tahun
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="chart-container line">

                        <canvas id="yearChart"></canvas>

                    </div>

                </div>


            </section>


            <!-- =================================================
                 BOTTOM
            ================================================== -->

            <section class="dashboard-bottom-grid">


                <!-- DISTRIBUSI -->

                <div class="dashboard-panel">

                    <div class="dashboard-panel-header">

                        <div class="dashboard-panel-title">

                            <div class="panel-title-icon">

                                <i data-lucide="bar-chart-3"></i>

                            </div>


                            <div>

                                <h2>
                                    Distribusi Koleksi
                                </h2>

                                <p>
                                    Perbandingan jumlah buku setiap kategori
                                </p>

                            </div>

                        </div>

                    </div>


                    <?php if ($kategoriRows): ?>

                        <div class="category-list">

                            <?php

                            $maxKategori =
                                max(
                                    array_column(
                                        $kategoriRows,
                                        'jumlah'
                                    )
                                );

                            foreach ($kategoriRows as $row):

                                $percentage =
                                    $maxKategori > 0
                                    ? (
                                        ($row['jumlah'] / $maxKategori)
                                        * 100
                                    )
                                    : 0;

                            ?>

                                <div class="category-item">

                                    <div class="category-top">

                                        <span>
                                            <?= e($row['kategori']) ?>
                                        </span>

                                        <strong>
                                            <?= (int)$row['jumlah'] ?>
                                            buku
                                        </strong>

                                    </div>


                                    <div class="progress">

                                        <span
                                            style="width: <?= $percentage ?>%"
                                        ></span>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <div class="dashboard-empty">

                            Belum ada data buku.

                        </div>

                    <?php endif; ?>

                </div>


                <!-- BUKU TERBARU -->

                <div class="dashboard-panel">

                    <div class="dashboard-panel-header">

                        <div class="dashboard-panel-title">

                            <div class="panel-title-icon">

                                <i data-lucide="clock-3"></i>

                            </div>


                            <div>

                                <h2>
                                    Buku Terbaru
                                </h2>

                                <p>
                                    5 buku terakhir ditambahkan
                                </p>

                            </div>

                        </div>


                        <a
                            href="tambah.php"
                            class="latest-action"
                            title="Tambah Buku"
                        >

                            <i data-lucide="plus"></i>

                        </a>

                    </div>


                    <div class="latest-list">

                        <?php if ($latestBooks): ?>

                            <?php foreach ($latestBooks as $book): ?>

                                <div class="latest-row">


                                    <div class="latest-cover">

                                        <?php if (!empty($book['gambar'])): ?>

                                            <img
                                                src="<?= e($book['gambar']) ?>"
                                                alt="Sampul <?= e($book['judul']) ?>"
                                                onerror="
                                                    this.style.display='none';
                                                    this.nextElementSibling.style.display='block';
                                                "
                                            >

                                            <i
                                                data-lucide="book-open"
                                                style="display:none;"
                                            ></i>

                                        <?php else: ?>

                                            <i data-lucide="book-open"></i>

                                        <?php endif; ?>

                                    </div>


                                    <div class="latest-info">

                                        <span class="latest-title">

                                            <?= e($book['judul']) ?>

                                        </span>


                                        <span class="latest-meta">

                                            <?= e($book['penulis']) ?>

                                            ·

                                            <?= e($book['tahun_terbit']) ?>

                                        </span>

                                    </div>


                                    <a
                                        href="edit.php?id=<?= urlencode($book['id']) ?>"
                                        class="latest-action"
                                        title="Edit Buku"
                                    >

                                        <i data-lucide="chevron-right"></i>

                                    </a>


                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="dashboard-empty">

                                Belum ada buku.

                            </div>

                        <?php endif; ?>

                    </div>

                </div>


            </section>


        </div>

    </main>

</div>


<!-- =========================================================
     LUCIDE
========================================================== -->

<script src="https://unpkg.com/lucide@latest"></script>


<!-- =========================================================
     CHART.JS
========================================================== -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        // ==================================================
        // LUCIDE
        // ==================================================

        if (typeof lucide !== 'undefined') {

            lucide.createIcons();

        }


        // ==================================================
        // DATA DARI PHP
        // ==================================================

        const kategoriLabels =
            <?= json_encode(
                $kategoriLabels,
                JSON_UNESCAPED_UNICODE
            ) ?>;

        const kategoriValues =
            <?= json_encode(
                $kategoriValues
            ) ?>;


        const tahunLabels =
            <?= json_encode(
                $tahunLabels,
                JSON_UNESCAPED_UNICODE
            ) ?>;

        const tahunValues =
            <?= json_encode(
                $tahunValues
            ) ?>;


        // ==================================================
        // CHART KATEGORI
        // ==================================================

        const categoryCanvas =
            document.getElementById(
                'categoryChart'
            );


        if (
            categoryCanvas
            &&
            typeof Chart !== 'undefined'
        ) {

            new Chart(
                categoryCanvas,
                {

                    type: 'doughnut',

                    data: {

                        labels: kategoriLabels,

                        datasets: [

                            {

                                data: kategoriValues,

                                borderWidth: 3,

                                borderColor: '#ffffff',

                                backgroundColor: [

                                    '#7a1729',
                                    '#9b2940',
                                    '#b74459',
                                    '#c96778',
                                    '#d98b99',
                                    '#e5abb5',
                                    '#efd0d5',
                                    '#f4e1e4'

                                ]

                            }

                        ]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        cutout: '66%',

                        plugins: {

                            legend: {

                                position: 'bottom',

                                labels: {

                                    usePointStyle: true,

                                    pointStyle: 'circle',

                                    padding: 14,

                                    font: {

                                        size: 10

                                    }

                                }

                            },

                            tooltip: {

                                callbacks: {

                                    label: function(context) {

                                        const value =
                                            context.raw;

                                        const total =
                                            context.dataset.data
                                                .reduce(
                                                    (
                                                        sum,
                                                        item
                                                    ) =>
                                                        sum + item,
                                                    0
                                                );

                                        const percentage =
                                            total > 0
                                            ? (
                                                value /
                                                total *
                                                100
                                            ).toFixed(1)
                                            : 0;

                                        return (
                                            ' ' +
                                            context.label +
                                            ': ' +
                                            value +
                                            ' buku (' +
                                            percentage +
                                            '%)'
                                        );

                                    }

                                }

                            }

                        }

                    }

                }
            );

        }


        // ==================================================
        // CHART TREN TAHUN
        // ==================================================

        const yearCanvas =
            document.getElementById(
                'yearChart'
            );


        if (
            yearCanvas
            &&
            typeof Chart !== 'undefined'
        ) {

            const ctx =
                yearCanvas.getContext('2d');


            const gradient =
                ctx.createLinearGradient(
                    0,
                    0,
                    0,
                    260
                );


            gradient.addColorStop(
                0,
                'rgba(122,23,41,.24)'
            );


            gradient.addColorStop(
                1,
                'rgba(122,23,41,0)'
            );


            new Chart(
                yearCanvas,
                {

                    type: 'line',

                    data: {

                        labels: tahunLabels,

                        datasets: [

                            {

                                label: 'Jumlah Buku',

                                data: tahunValues,

                                borderColor: '#7a1729',

                                backgroundColor: gradient,

                                fill: true,

                                tension: .4,

                                borderWidth: 2.5,

                                pointRadius: 4,

                                pointHoverRadius: 6,

                                pointBackgroundColor: '#7a1729',

                                pointBorderColor: '#ffffff',

                                pointBorderWidth: 2

                            }

                        ]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        interaction: {

                            intersect: false,

                            mode: 'index'

                        },

                        plugins: {

                            legend: {

                                display: false

                            },

                            tooltip: {

                                backgroundColor:
                                    '#431019',

                                titleColor:
                                    '#ffffff',

                                bodyColor:
                                    '#f8e8eb',

                                padding: 10,

                                displayColors: false,

                                callbacks: {

                                    label: function(context) {

                                        return (
                                            ' ' +
                                            context.raw +
                                            ' buku'
                                        );

                                    }

                                }

                            }

                        },

                        scales: {

                            x: {

                                grid: {

                                    display: false

                                },

                                ticks: {

                                    color: '#9a7d83',

                                    font: {

                                        size: 10

                                    }

                                }

                            },

                            y: {

                                beginAtZero: true,

                                ticks: {

                                    precision: 0,

                                    color: '#9a7d83',

                                    font: {

                                        size: 10

                                    }

                                },

                                grid: {

                                    color:
                                        'rgba(122,23,41,.07)'

                                }

                            }

                        }

                    }

                }

            );

        }

    }

);

</script>


</body>

</html>