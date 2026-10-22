<?php

require_once 'koneksi.php';
require_once 'auth.php';

requireLogin();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Data Buku - Perpus API</title>

    <link rel="stylesheet" href="style.css">

    <style>

        /* =====================================================
           DATA BUKU PAGE
        ====================================================== */

        .books-page {
            max-width: 100%;
        }


        /* =====================================================
           PAGE HEADER
        ====================================================== */

        .books-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            gap: 25px;

            margin-bottom: 25px;
        }

        .books-heading {
            display: flex;
            align-items: flex-start;

            gap: 14px;
        }

        .books-heading-icon {
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

        .books-heading-icon svg {
            width: 23px;
            height: 23px;
            stroke-width: 1.8;
        }

        .books-heading h1 {
            margin: 0 0 5px;

            color: #431019;

            font-size: 27px;
            line-height: 1.2;

            font-weight: 800;

            letter-spacing: -.4px;
        }

        .books-heading p {
            margin: 0;

            color: #92747b;

            font-size: 13px;

            line-height: 1.5;
        }


        /* =====================================================
           HEADER ACTIONS
        ====================================================== */

        .books-actions {
            display: flex;
            align-items: center;

            gap: 8px;

            flex-wrap: wrap;
        }

        .books-action {
            height: 40px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 7px;

            padding: 0 14px;

            border-radius: 9px;

            text-decoration: none;

            font-size: 12px;
            font-weight: 700;

            transition: .2s ease;
        }

        .books-action svg {
            width: 16px;
            height: 16px;

            stroke-width: 2;
        }

        .books-action:hover {
            transform: translateY(-1px);
        }

        .books-action.excel {
            color: #24734b;
            background: #eaf7ef;
            border: 1px solid #ccebd9;
        }

        .books-action.excel:hover {
            background: #ddf2e6;
        }

        .books-action.pdf {
            color: #7c2631;
            background: #fbecef;
            border: 1px solid #efd0d5;
        }

        .books-action.pdf:hover {
            background: #f7e2e6;
        }

        .books-action.add {
            color: white;
            background: linear-gradient(
                135deg,
                #661321,
                #8f1d32
            );

            border: 1px solid #661321;

            box-shadow:
                0 7px 16px rgba(102,19,33,.18);
        }

        .books-action.add:hover {
            box-shadow:
                0 9px 20px rgba(102,19,33,.25);
        }


        /* =====================================================
           SUMMARY CARDS
        ====================================================== */

        .book-summary {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 14px;

            margin-bottom: 20px;
        }

        .summary-card {
            display: flex;
            align-items: center;

            gap: 13px;

            padding: 17px;

            background: #ffffff;

            border: 1px solid #eee1e4;

            border-radius: 13px;

            box-shadow:
                0 5px 18px rgba(70,12,24,.04);
        }

        .summary-icon {
            width: 42px;
            height: 42px;

            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 11px;
        }

        .summary-icon svg {
            width: 20px;
            height: 20px;

            stroke-width: 1.8;
        }

        .summary-icon.books {
            color: #7b1729;
            background: #fae7ea;
        }

        .summary-icon.category {
            color: #8b5a19;
            background: #fff4dc;
        }

        .summary-icon.api {
            color: #356b8a;
            background: #e8f3f8;
        }

        .summary-info {
            min-width: 0;

            display: flex;
            flex-direction: column;

            gap: 3px;
        }

        .summary-label {
            color: #a08389;

            font-size: 10px;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: .7px;
        }

        .summary-value {
            color: #4a121b;

            font-size: 20px;

            line-height: 1;

            font-weight: 800;
        }


        /* =====================================================
           MAIN PANEL
        ====================================================== */

        .books-panel {
            overflow: hidden;

            padding: 0 !important;

            background: #ffffff;

            border: 1px solid #eee1e4;

            border-radius: 15px;

            box-shadow:
                0 7px 25px rgba(70,12,24,.05);
        }


        /* =====================================================
           TOOLBAR
        ====================================================== */

        .books-toolbar {
            display: flex;
            align-items: center;

            gap: 10px;

            padding: 17px;

            background: #fff;

            border-bottom: 1px solid #f0e5e7;
        }

        .books-search {
            position: relative;

            flex: 1;

            min-width: 240px;
        }

        .books-search > svg {
            position: absolute;

            left: 14px;
            top: 50%;

            width: 17px;
            height: 17px;

            transform: translateY(-50%);

            color: #ad8d94;

            pointer-events: none;
        }

        .books-search input {
            width: 100%;
            height: 42px;

            padding: 0 14px 0 42px;

            border: 1px solid #eadcdf;

            border-radius: 9px;

            outline: none;

            color: #4a151e;

            background: #fff;

            font-family: inherit;

            font-size: 12px;

            transition: .2s ease;
        }

        .books-search input::placeholder {
            color: #b9a4a9;
        }

        .books-search input:focus {
            border-color: #9a3447;

            box-shadow:
                0 0 0 3px rgba(154,52,71,.08);
        }

        .books-filter {
            height: 42px;

            min-width: 150px;

            padding: 0 34px 0 12px;

            border: 1px solid #eadcdf;

            border-radius: 9px;

            outline: none;

            color: #5b252d;

            background-color: #fff;

            font-family: inherit;

            font-size: 12px;

            cursor: pointer;
        }

        .books-filter:focus {
            border-color: #9a3447;

            box-shadow:
                0 0 0 3px rgba(154,52,71,.08);
        }

        .refresh-button {
            height: 42px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 7px;

            padding: 0 13px;

            border: 1px solid #eadcdf;

            border-radius: 9px;

            color: #6c444c;

            background: #fff;

            font-family: inherit;

            font-size: 12px;

            font-weight: 700;

            cursor: pointer;

            transition: .2s ease;
        }

        .refresh-button svg {
            width: 16px;
            height: 16px;
        }

        .refresh-button:hover {
            color: #76182a;

            background: #fff7f8;

            border-color: #dfc2c8;
        }


        /* =====================================================
           API STATUS
        ====================================================== */

        .books-status {
            display: flex;
            align-items: center;

            gap: 8px;

            min-height: 39px;

            padding: 0 17px;

            color: #9a7d83;

            background: #fffafb;

            border-bottom: 1px solid #f2e8ea;

            font-size: 11px;
        }

        .books-status::before {
            content: "";

            width: 7px;
            height: 7px;

            flex-shrink: 0;

            border-radius: 50%;

            background: #c9adb3;
        }

        .books-status.success {
            color: #4d7960;
        }

        .books-status.success::before {
            background: #55a874;

            box-shadow:
                0 0 0 3px rgba(85,168,116,.11);
        }

        .books-status.error {
            color: #a03c4d;
        }

        .books-status.error::before {
            background: #bd4358;

            box-shadow:
                0 0 0 3px rgba(189,67,88,.1);
        }


        /* =====================================================
           TABLE
        ====================================================== */

        .books-table-wrap {
            overflow-x: auto;
        }

        .books-table {
            width: 100%;

            border-collapse: collapse;

            min-width: 1050px;
        }

        .books-table thead th {
            height: 45px;

            padding: 0 14px;

            color: #896a72;

            background: #fffafb;

            border-bottom: 1px solid #eee1e4;

            font-size: 10px;

            font-weight: 800;

            text-align: left;

            text-transform: uppercase;

            letter-spacing: .65px;

            white-space: nowrap;
        }

        .books-table tbody td {
            padding: 13px 14px;

            color: #68434b;

            border-bottom: 1px solid #f2e9eb;

            font-size: 12px;

            vertical-align: middle;
        }

        .books-table tbody tr {
            transition: .15s ease;
        }

        .books-table tbody tr:hover {
            background: #fffafb;
        }

        .books-table tbody tr:last-child td {
            border-bottom: none;
        }


        /* =====================================================
           ID
        ====================================================== */

        .book-id {
            color: #a2848b;

            font-size: 11px;

            font-weight: 700;

            white-space: nowrap;
        }


        /* =====================================================
           COVER
        ====================================================== */

        .cover-wrapper {
            width: 43px;
            height: 57px;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;

            border-radius: 7px;

            background:
                linear-gradient(
                    145deg,
                    #f7e0e4,
                    #f2cbd2
                );

            border: 1px solid #ecd0d5;

            box-shadow:
                0 4px 9px rgba(80,12,25,.08);
        }

        .table-cover {
            width: 100%;
            height: 100%;

            display: block;

            object-fit: cover;
        }

        .cover-placeholder {
            width: 100%;
            height: 100%;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #8c394a;
        }

        .cover-placeholder svg {
            width: 19px;
            height: 19px;

            stroke-width: 1.7;
        }


        /* =====================================================
           BOOK CODE
        ====================================================== */

        .book-code {
            display: inline-flex;
            align-items: center;

            padding: 5px 8px;

            border-radius: 6px;

            color: #7d2636;

            background: #fbecef;

            border: 1px solid #f0d4d9;

            font-family: monospace;

            font-size: 10px;

            font-weight: 700;

            white-space: nowrap;
        }


        /* =====================================================
           BOOK TITLE
        ====================================================== */

        .book-title {
            min-width: 190px;

            color: #48141d;

            font-size: 12px;

            line-height: 1.5;

            font-weight: 750;
        }


        /* =====================================================
           CATEGORY
        ====================================================== */

        .category-badge {
            display: inline-flex;

            align-items: center;

            padding: 5px 9px;

            border-radius: 20px;

            color: #75404a;

            background: #f8f0f1;

            border: 1px solid #ecdee1;

            font-size: 10px;

            font-weight: 700;

            white-space: nowrap;
        }


        /* =====================================================
           YEAR
        ====================================================== */

        .book-year {
            color: #68434b;

            font-weight: 700;

            white-space: nowrap;
        }


        /* =====================================================
           ACTION
        ====================================================== */

        .book-actions {
            display: flex;
            align-items: center;

            gap: 6px;
        }

        .book-actions form {
            margin: 0;
            padding: 0;
        }

        .book-action {
            width: 32px;
            height: 32px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            border-radius: 8px;

            text-decoration: none;

            border: 1px solid transparent;

            cursor: pointer;

            transition: .18s ease;
        }

        .book-action svg {
            width: 15px;
            height: 15px;

            stroke-width: 1.9;
        }

        .book-action.edit {
            color: #87611f;

            background: #fff5df;

            border-color: #f2dfb7;
        }

        .book-action.edit:hover {
            color: #6c4a10;

            background: #ffedc5;
        }

        .book-action.delete {
            color: #a0374a;

            background: #fff0f2;

            border-color: #f1d4d9;
        }

        .book-action.delete:hover {
            color: #8a2337;

            background: #fbe0e4;
        }


        /* =====================================================
           EMPTY
        ====================================================== */

        .books-empty {
            padding: 55px 20px !important;

            color: #a4878e !important;

            text-align: center;

            font-size: 12px !important;
        }

        .empty-content {
            display: flex;
            align-items: center;
            justify-content: center;

            flex-direction: column;

            gap: 9px;
        }

        .empty-icon {
            width: 42px;
            height: 42px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 12px;

            color: #a87580;

            background: #f9edef;
        }

        .empty-icon svg {
            width: 20px;
            height: 20px;
        }


        /* =====================================================
           FLASH MESSAGE
        ====================================================== */

        .books-alert {
            display: flex;
            align-items: center;

            gap: 9px;

            margin-bottom: 18px;

            padding: 12px 15px;

            border-radius: 9px;

            font-size: 12px;

            font-weight: 600;
        }

        .books-alert.success {
            color: #3f7655;

            background: #eef9f2;

            border: 1px solid #d3eddd;
        }

        .books-alert.danger {
            color: #a03649;

            background: #fff0f2;

            border: 1px solid #efd3d8;
        }

        .books-alert svg {
            width: 17px;
            height: 17px;
        }


        /* =====================================================
           PAGINATION
        ====================================================== */

        .books-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 15px;

            padding: 14px 17px;

            background: #ffffff;

            border-top: 1px solid #eee1e4;
        }

        .pagination-info {
            color: #9a7d83;

            font-size: 11px;

            line-height: 1.5;

            white-space: nowrap;
        }

        .pagination-info strong {
            color: #6d3943;

            font-weight: 800;
        }

        .pagination-controls {
            display: flex;
            align-items: center;

            gap: 5px;
        }

        .pagination-button {
            min-width: 32px;
            height: 32px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 0 9px;

            border: 1px solid #eadcdf;

            border-radius: 8px;

            color: #70424c;

            background: #ffffff;

            font-family: inherit;

            font-size: 11px;

            font-weight: 700;

            cursor: pointer;

            transition: .18s ease;
        }

        .pagination-button svg {
            width: 14px;
            height: 14px;

            stroke-width: 2;
        }

        .pagination-button:hover:not(:disabled) {
            color: #76182a;

            background: #fff5f7;

            border-color: #dcb8c0;
        }

        .pagination-button.active {
            color: #ffffff;

            background: linear-gradient(
                135deg,
                #661321,
                #8f1d32
            );

            border-color: #661321;

            box-shadow:
                0 4px 10px rgba(102,19,33,.16);
        }

        .pagination-button:disabled {
            color: #c4b0b5;

            background: #faf8f8;

            cursor: not-allowed;

            opacity: .7;
        }

        .pagination-dots {
            min-width: 25px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            color: #a98c92;

            font-size: 12px;
        }


        /* =====================================================
           RESPONSIVE
        ====================================================== */

        @media (max-width: 1100px) {

            .books-header {
                flex-direction: column;
            }

            .books-actions {
                width: 100%;
            }

        }


        @media (max-width: 850px) {

            .book-summary {
                grid-template-columns: 1fr;
            }

            .books-toolbar {
                flex-wrap: wrap;
            }

            .books-search {
                width: 100%;
                flex-basis: 100%;
            }

            .books-pagination {
                align-items: flex-start;
                flex-direction: column;
            }

            .pagination-controls {
                width: 100%;
                justify-content: center;
            }

        }


        @media (max-width: 600px) {

            .books-heading h1 {
                font-size: 23px;
            }

            .books-heading-icon {
                width: 43px;
                height: 43px;
            }

            .books-actions {
                display: grid;

                grid-template-columns: 1fr 1fr;
            }

            .books-action.add {
                grid-column: 1 / -1;
            }

            .books-filter {
                flex: 1;
                min-width: 0;
            }

            .pagination-info {
                width: 100%;

                text-align: center;

                white-space: normal;
            }

            .pagination-controls {
                gap: 3px;
            }

            .pagination-button {
                min-width: 30px;
                height: 30px;

                padding: 0 7px;
            }

        }

    </style>

</head>


<body>


<div class="app">

    <?php include 'sidebar.php'; ?>


    <main class="main-content">

        <div class="books-page">


            <!-- =================================================
                 HEADER
            ================================================== -->

            <div class="books-header">

                <div class="books-heading">

                    <div class="books-heading-icon">

                        <i data-lucide="book-open"></i>

                    </div>

                    <div>

                        <h1>
                            Data Buku
                        </h1>

                        <p>
                            Kelola dan pantau seluruh koleksi buku
                            perpustakaan melalui JSON API.
                        </p>

                    </div>

                </div>


                <div class="books-actions">

                    <a
                        href="export_excel.php"
                        class="books-action excel"
                    >

                        <i data-lucide="file-spreadsheet"></i>

                        Export Excel

                    </a>


                    <a
                        href="export_pdf.php"
                        class="books-action pdf"
                    >

                        <i data-lucide="file-text"></i>

                        Export PDF

                    </a>


                    <a
                        href="tambah.php"
                        class="books-action add"
                    >

                        <i data-lucide="plus"></i>

                        Tambah Buku

                    </a>

                </div>

            </div>


            <!-- =================================================
                 FLASH MESSAGE
            ================================================== -->

            <?php if ($flash): ?>

                <div
                    class="books-alert <?= $flash['type'] === 'success' ? 'success' : 'danger' ?>"
                >

                    <?php if ($flash['type'] === 'success'): ?>

                        <i data-lucide="circle-check"></i>

                    <?php else: ?>

                        <i data-lucide="circle-alert"></i>

                    <?php endif; ?>


                    <span>
                        <?= e($flash['message']) ?>
                    </span>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 SUMMARY
            ================================================== -->

            <div class="book-summary">


                <div class="summary-card">

                    <div class="summary-icon books">

                        <i data-lucide="library"></i>

                    </div>

                    <div class="summary-info">

                        <span class="summary-label">
                            Total Buku
                        </span>

                        <span
                            class="summary-value"
                            id="totalBooks"
                        >
                            -
                        </span>

                    </div>

                </div>


                <div class="summary-card">

                    <div class="summary-icon category">

                        <i data-lucide="tags"></i>

                    </div>

                    <div class="summary-info">

                        <span class="summary-label">
                            Kategori
                        </span>

                        <span
                            class="summary-value"
                            id="totalCategories"
                        >
                            -
                        </span>

                    </div>

                </div>


                <div class="summary-card">

                    <div class="summary-icon api">

                        <i data-lucide="radio"></i>

                    </div>

                    <div class="summary-info">

                        <span class="summary-label">
                            Status API
                        </span>

                        <span
                            class="summary-value"
                            id="apiSummary"
                            style="font-size:15px;"
                        >
                            Memuat...
                        </span>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 TABLE PANEL
            ================================================== -->

            <section class="panel books-panel">


                <!-- TOOLBAR -->

                <div class="books-toolbar">


                    <div class="books-search">

                        <i data-lucide="search"></i>

                        <input
                            type="text"
                            id="searchInput"
                            placeholder="Cari judul, kode, penulis, kategori..."
                        >

                    </div>


                    <select
                        id="categoryFilter"
                        class="books-filter"
                    >

                        <option value="">
                            Semua Kategori
                        </option>

                    </select>


                    <select
                        id="yearSort"
                        class="books-filter"
                    >

                        <option value="">
                            Urutan Tahun
                        </option>

                        <option value="desc">
                            Terbaru → Terlama
                        </option>

                        <option value="asc">
                            Terlama → Terbaru
                        </option>

                    </select>


                    <button
                        type="button"
                        class="refresh-button"
                        id="refreshBtn"
                    >

                        <i data-lucide="refresh-cw"></i>

                        Refresh

                    </button>


                </div>


                <!-- API STATUS -->

                <div
                    id="apiStatus"
                    class="books-status"
                >
                    Memuat data dari API...
                </div>


                <!-- TABLE -->

                <div class="books-table-wrap">

                    <table class="books-table">

                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    SAMPUL
                                </th>

                                <th>
                                    KODE
                                </th>

                                <th>
                                    JUDUL
                                </th>

                                <th>
                                    PENULIS
                                </th>

                                <th>
                                    KATEGORI
                                </th>

                                <th>
                                    TAHUN
                                </th>

                                <th>
                                    PENERBIT
                                </th>

                                <th>
                                    AKSI
                                </th>

                            </tr>

                        </thead>


                        <tbody id="bookTableBody">

                            <tr>

                                <td
                                    colspan="9"
                                    class="books-empty"
                                >

                                    <div class="empty-content">

                                        <div class="empty-icon">

                                            <i data-lucide="loader-circle"></i>

                                        </div>

                                        <span>
                                            Memuat data buku...
                                        </span>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- =================================================
                     PAGINATION
                ================================================== -->

                <div
                    class="books-pagination"
                    id="paginationContainer"
                >

                    <div
                        class="pagination-info"
                        id="paginationInfo"
                    >
                        Menampilkan 0 data
                    </div>

                    <div
                        class="pagination-controls"
                        id="paginationControls"
                    >
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


<script>

const API_URL = 'data_json.php';


// ======================================================
// PAGINATION CONFIG
// ======================================================

const ITEMS_PER_PAGE = 8;

let currentPage = 1;

let allBooks = [];


// ======================================================
// ELEMENT
// ======================================================

const body =
    document.getElementById('bookTableBody');

const statusBox =
    document.getElementById('apiStatus');

const searchInput =
    document.getElementById('searchInput');

const categoryFilter =
    document.getElementById('categoryFilter');

const yearSort =
    document.getElementById('yearSort');

const refreshBtn =
    document.getElementById('refreshBtn');

const totalBooks =
    document.getElementById('totalBooks');

const totalCategories =
    document.getElementById('totalCategories');

const apiSummary =
    document.getElementById('apiSummary');

const paginationInfo =
    document.getElementById('paginationInfo');

const paginationControls =
    document.getElementById('paginationControls');


// ======================================================
// LUCIDE
// ======================================================

function refreshIcons() {

    if (typeof lucide !== 'undefined') {

        lucide.createIcons();

    }

}


// ======================================================
// ESCAPE HTML
// ======================================================

function escapeHtml(value) {

    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

}


// ======================================================
// COVER
// ======================================================

function createCover(book) {

    if (book.gambar) {

        return `
            <div class="cover-wrapper">

                <img
                    src="${escapeHtml(book.gambar)}"
                    alt="Sampul ${escapeHtml(book.judul)}"
                    class="table-cover"
                    onerror="this.parentElement.innerHTML='<div class=&quot;cover-placeholder&quot;><i data-lucide=&quot;book-open&quot;></i></div>'; refreshIcons();"
                >

            </div>
        `;

    }


    return `
        <div class="cover-wrapper">

            <div class="cover-placeholder">

                <i data-lucide="book-open"></i>

            </div>

        </div>
    `;

}


// ======================================================
// FILTER + SORT DATA
// ======================================================

function getFilteredBooks() {

    const search =
        searchInput.value
            .toLowerCase()
            .trim();

    const category =
        categoryFilter.value;

    const sort =
        yearSort.value;


    let data =
        allBooks.filter(book => {

            const haystack = [

                book.kode_buku,
                book.judul,
                book.penulis,
                book.kategori,
                book.tahun_terbit,
                book.penerbit

            ]
            .join(' ')
            .toLowerCase();


            return (
                haystack.includes(search)
                &&
                (
                    !category
                    ||
                    book.kategori === category
                )
            );

        });


    // ==================================================
    // SORT TAHUN
    // ==================================================

    if (sort === 'asc') {

        data.sort(
            (a, b) =>
                Number(a.tahun_terbit)
                -
                Number(b.tahun_terbit)
        );

    }


    if (sort === 'desc') {

        data.sort(
            (a, b) =>
                Number(b.tahun_terbit)
                -
                Number(a.tahun_terbit)
        );

    }


    return data;

}


// ======================================================
// RENDER PAGINATION
// ======================================================

function renderPagination(totalItems) {

    const totalPages =
        Math.ceil(totalItems / ITEMS_PER_PAGE);


    paginationControls.innerHTML = '';


    // Tidak perlu pagination kalau data <= 8
    if (totalPages <= 1) {

        paginationInfo.innerHTML =
            totalItems > 0
                ? `Menampilkan <strong>${totalItems}</strong> data buku`
                : 'Tidak ada data buku';

        return;

    }


    // ==================================================
    // INFO
    // ==================================================

    const start =
        ((currentPage - 1) * ITEMS_PER_PAGE) + 1;

    const end =
        Math.min(
            currentPage * ITEMS_PER_PAGE,
            totalItems
        );


    paginationInfo.innerHTML = `
        Menampilkan
        <strong>${start}–${end}</strong>
        dari
        <strong>${totalItems}</strong>
        buku
    `;


    // ==================================================
    // BUTTON SEBELUMNYA
    // ==================================================

    const previousButton =
        document.createElement('button');

    previousButton.type = 'button';

    previousButton.className =
        'pagination-button';

    previousButton.innerHTML =
        '<i data-lucide="chevron-left"></i>';

    previousButton.title =
        'Halaman sebelumnya';

    previousButton.disabled =
        currentPage === 1;

    previousButton.addEventListener(
        'click',
        function () {

            if (currentPage > 1) {

                currentPage--;

                renderBooks();

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

            }

        }
    );

    paginationControls.appendChild(
        previousButton
    );


    // ==================================================
    // NOMOR HALAMAN
    // ==================================================

    const pages =
        getPaginationPages(
            currentPage,
            totalPages
        );


    pages.forEach(page => {

        if (page === 'dots') {

            const dots =
                document.createElement('span');

            dots.className =
                'pagination-dots';

            dots.textContent =
                '•••';

            paginationControls.appendChild(
                dots
            );

            return;

        }


        const pageButton =
            document.createElement('button');

        pageButton.type = 'button';

        pageButton.className =
            'pagination-button'
            +
            (
                page === currentPage
                    ? ' active'
                    : ''
            );

        pageButton.textContent =
            page;

        pageButton.addEventListener(
            'click',
            function () {

                currentPage =
                    page;

                renderBooks();

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

            }
        );

        paginationControls.appendChild(
            pageButton
        );

    });


    // ==================================================
    // BUTTON BERIKUTNYA
    // ==================================================

    const nextButton =
        document.createElement('button');

    nextButton.type = 'button';

    nextButton.className =
        'pagination-button';

    nextButton.innerHTML =
        '<i data-lucide="chevron-right"></i>';

    nextButton.title =
        'Halaman berikutnya';

    nextButton.disabled =
        currentPage === totalPages;

    nextButton.addEventListener(
        'click',
        function () {

            if (currentPage < totalPages) {

                currentPage++;

                renderBooks();

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

            }

        }
    );

    paginationControls.appendChild(
        nextButton
    );


    refreshIcons();

}


// ======================================================
// GENERATE NOMOR PAGINATION
// ======================================================

function getPaginationPages(current, total) {

    const pages = [];


    // Jika halaman sedikit
    if (total <= 7) {

        for (
            let i = 1;
            i <= total;
            i++
        ) {

            pages.push(i);

        }

        return pages;

    }


    // Halaman awal
    pages.push(1);


    // ==================================================
    // CURRENT DEKAT AWAL
    // ==================================================

    if (current <= 4) {

        pages.push(2);
        pages.push(3);
        pages.push(4);
        pages.push(5);

        pages.push('dots');

        pages.push(total);

        return pages;

    }


    // ==================================================
    // CURRENT DEKAT AKHIR
    // ==================================================

    if (current >= total - 3) {

        pages.push('dots');

        pages.push(total - 4);
        pages.push(total - 3);
        pages.push(total - 2);
        pages.push(total - 1);
        pages.push(total);

        return pages;

    }


    // ==================================================
    // CURRENT DI TENGAH
    // ==================================================

    pages.push('dots');

    pages.push(current - 1);
    pages.push(current);
    pages.push(current + 1);

    pages.push('dots');

    pages.push(total);


    return pages;

}


// ======================================================
// RENDER DATA BUKU
// ======================================================

function renderBooks() {

    const data =
        getFilteredBooks();


    const totalItems =
        data.length;


    const totalPages =
        Math.ceil(
            totalItems / ITEMS_PER_PAGE
        );


    // ==================================================
    // CEK HALAMAN
    // ==================================================

    if (
        totalPages > 0
        &&
        currentPage > totalPages
    ) {

        currentPage =
            totalPages;

    }


    if (totalPages === 0) {

        currentPage = 1;

    }


    // ==================================================
    // DATA KOSONG
    // ==================================================

    if (!totalItems) {

        body.innerHTML = `

            <tr>

                <td
                    colspan="9"
                    class="books-empty"
                >

                    <div class="empty-content">

                        <div class="empty-icon">

                            <i data-lucide="search-x"></i>

                        </div>

                        <strong>
                            Data tidak ditemukan
                        </strong>

                        <span>
                            Coba ubah kata pencarian atau filter.
                        </span>

                    </div>

                </td>

            </tr>

        `;


        paginationInfo.textContent =
            'Tidak ada data buku';

        paginationControls.innerHTML = '';

        refreshIcons();

        return;

    }


    // ==================================================
    // AMBIL DATA UNTUK HALAMAN AKTIF
    // ==================================================

    const startIndex =
        (currentPage - 1)
        *
        ITEMS_PER_PAGE;


    const endIndex =
        startIndex
        +
        ITEMS_PER_PAGE;


    const pageData =
        data.slice(
            startIndex,
            endIndex
        );


    // ==================================================
    // RENDER TABLE
    // ==================================================

    body.innerHTML =
        pageData.map(book => {

            return `

                <tr>

                    <td>

                        <span class="book-id">
                            #${escapeHtml(book.id)}
                        </span>

                    </td>


                    <td>

                        ${createCover(book)}

                    </td>


                    <td>

                        <span class="book-code">
                            ${escapeHtml(book.kode_buku)}
                        </span>

                    </td>


                    <td>

                        <div class="book-title">

                            ${escapeHtml(book.judul)}

                        </div>

                    </td>


                    <td>

                        ${escapeHtml(book.penulis)}

                    </td>


                    <td>

                        <span class="category-badge">

                            ${escapeHtml(book.kategori)}

                        </span>

                    </td>


                    <td>

                        <span class="book-year">

                            ${escapeHtml(book.tahun_terbit)}

                        </span>

                    </td>


                    <td>

                        ${escapeHtml(book.penerbit)}

                    </td>


                    <td>

                        <div class="book-actions">


                            <a
                                href="edit.php?id=${encodeURIComponent(book.id)}"
                                class="book-action edit"
                                title="Edit Buku"
                            >

                                <i data-lucide="pencil"></i>

                            </a>


                            <form
                                method="post"
                                action="hapus.php"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?');"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="${escapeHtml(book.id)}"
                                >


                                <button
                                    type="submit"
                                    class="book-action delete"
                                    title="Hapus Buku"
                                >

                                    <i data-lucide="trash-2"></i>

                                </button>

                            </form>


                        </div>

                    </td>

                </tr>

            `;

        })
        .join('');


    // ==================================================
    // PAGINATION
    // ==================================================

    renderPagination(
        totalItems
    );


    refreshIcons();

}


// ======================================================
// LOAD API
// ======================================================

async function loadBooks() {

    statusBox.textContent =
        'Mengambil data dari data_json.php...';

    statusBox.className =
        'books-status';


    apiSummary.textContent =
        'Memuat...';


    body.innerHTML = `

        <tr>

            <td
                colspan="9"
                class="books-empty"
            >

                <div class="empty-content">

                    <div class="empty-icon">

                        <i data-lucide="loader-circle"></i>

                    </div>

                    <span>
                        Mengambil data buku...
                    </span>

                </div>

            </td>

        </tr>

    `;


    paginationInfo.textContent =
        'Memuat data...';

    paginationControls.innerHTML = '';


    refreshIcons();


    try {

        const response =
            await fetch(
                API_URL,
                {
                    cache: 'no-store'
                }
            );


        // ==================================================
        // HTTP
        // ==================================================

        if (!response.ok) {

            throw new Error(
                'HTTP ' + response.status
            );

        }


        // ==================================================
        // JSON
        // ==================================================

        const result =
            await response.json();


        // ==================================================
        // VALIDASI
        // ==================================================

        if (
            !result
            ||
            result.success !== true
            ||
            !Array.isArray(result.data)
        ) {

            throw new Error(
                'Format API tidak valid'
            );

        }


        // ==================================================
        // DATA
        // ==================================================

        allBooks =
            result.data;


        // Kembali ke halaman pertama
        currentPage = 1;


        // ==================================================
        // KATEGORI
        // ==================================================

        const categories =
            [
                ...new Set(
                    allBooks
                        .map(book => book.kategori)
                        .filter(category => category)
                )
            ]
            .sort();


        categoryFilter.innerHTML = `

            <option value="">
                Semua Kategori
            </option>

        `;


        categories.forEach(category => {

            const option =
                document.createElement('option');

            option.value =
                category;

            option.textContent =
                category;

            categoryFilter.appendChild(
                option
            );

        });


        // ==================================================
        // SUMMARY
        // ==================================================

        totalBooks.textContent =
            allBooks.length;

        totalCategories.textContent =
            categories.length;

        apiSummary.textContent =
            'Terhubung';


        // ==================================================
        // STATUS
        // ==================================================

        statusBox.textContent =
            `API berhasil terhubung · ${allBooks.length} data buku`;

        statusBox.className =
            'books-status success';


        // ==================================================
        // RENDER
        // ==================================================

        renderBooks();


    } catch (error) {

        console.error(
            'Error mengambil data API:',
            error
        );


        totalBooks.textContent =
            '-';

        totalCategories.textContent =
            '-';

        apiSummary.textContent =
            'Gagal';


        statusBox.textContent =
            'Gagal mengambil data API: '
            +
            error.message;

        statusBox.className =
            'books-status error';


        body.innerHTML = `

            <tr>

                <td
                    colspan="9"
                    class="books-empty"
                >

                    <div class="empty-content">

                        <div class="empty-icon">

                            <i data-lucide="cloud-off"></i>

                        </div>

                        <strong>
                            Data gagal dimuat
                        </strong>

                        <span>
                            Periksa data_json.php dan koneksi database.
                        </span>

                    </div>

                </td>

            </tr>

        `;


        paginationInfo.textContent =
            'Data tidak tersedia';

        paginationControls.innerHTML = '';


        refreshIcons();

    }

}


// ======================================================
// SEARCH
// ======================================================

searchInput.addEventListener(
    'input',
    function () {

        currentPage = 1;

        renderBooks();

    }
);


// ======================================================
// CATEGORY
// ======================================================

categoryFilter.addEventListener(
    'change',
    function () {

        currentPage = 1;

        renderBooks();

    }
);


// ======================================================
// YEAR SORT
// ======================================================

yearSort.addEventListener(
    'change',
    function () {

        currentPage = 1;

        renderBooks();

    }
);


// ======================================================
// REFRESH
// ======================================================

refreshBtn.addEventListener(
    'click',
    async function () {

        const icon =
            refreshBtn.querySelector('svg');


        if (icon) {

            icon.style.animation =
                'spin .7s linear infinite';

        }


        await loadBooks();


        const newIcon =
            refreshBtn.querySelector('svg');


        if (newIcon) {

            newIcon.style.animation =
                '';

        }

    }
);


// ======================================================
// CSS SPIN
// ======================================================

const spinStyle =
    document.createElement('style');

spinStyle.textContent = `

    @keyframes spin {

        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }

    }

`;

document.head.appendChild(
    spinStyle
);


// ======================================================
// LOAD AWAL
// ======================================================

loadBooks();

</script>


</body>

</html>