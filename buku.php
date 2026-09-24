<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku — Admin Perpustakaan</title>

    <style>
        .content-body .bk-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 22px;
            animation: bkFadeUp .5s ease both;
        }

        .content-body .bk-title {
            font-size: 23px;
            font-weight: 700;
            background: linear-gradient(90deg, #4a0812, #8f1226, #c9184a);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .content-body .bk-title svg {
            width: 27px;
            height: 27px;
            color: #8f1226;
            -webkit-text-fill-color: #8f1226;
        }

        .content-body .bk-title small {
            display: block;
            font-size: 12px;
            font-weight: 500;
            -webkit-text-fill-color: #a29294;
            letter-spacing: .5px;
            margin-top: 2px;
        }

        .content-body .bk-btn-add {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            background: linear-gradient(135deg, #c9184a, #8f1226);
            color: #fff;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            padding: 11px 20px;
            border-radius: 12px;
            box-shadow: 0 8px 18px rgba(143,18,38,.35);
            transition: transform .25s, box-shadow .25s;
            position: relative;
            overflow: hidden;
        }

        .content-body .bk-btn-add svg {
            width: 17px;
            height: 17px;
        }

        .content-body .bk-btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(143,18,38,.45);
        }

        .content-body .bk-btn-add::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255,255,255,.35),
                transparent
            );
            transform: skewX(-20deg);
            transition: left .5s;
        }

        .content-body .bk-btn-add:hover::before {
            left: 130%;
        }

        .content-body .bk-toolbar {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 18px;
            animation: bkFadeUp .5s .1s ease both;
        }

        .content-body .bk-search {
            position: relative;
            flex: 1;
            min-width: 240px;
            max-width: 420px;
        }

        .content-body .bk-search svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 17px;
            height: 17px;
            color: #b09a9c;
            pointer-events: none;
            transition: color .3s;
        }

        .content-body .bk-search:focus-within svg {
            color: #8f1226;
        }

        .content-body .bk-search input {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1.5px solid #e6dcdc;
            border-radius: 12px;
            font-size: 13.5px;
            font-family: inherit;
            color: #2b0509;
            background: #fff;
            outline: none;
            transition: border-color .3s, box-shadow .3s;
        }

        .content-body .bk-search input::placeholder {
            color: #c2b2b4;
        }

        .content-body .bk-search input:focus {
            border-color: #8f1226;
            box-shadow: 0 0 0 4px rgba(143,18,38,.12);
        }

        .content-body .bk-count {
            font-size: 12.5px;
            font-weight: 500;
            color: #8f1226;
            background: #fdf3f3;
            border: 1px solid #f0dcdc;
            padding: 8px 15px;
            border-radius: 99px;
            white-space: nowrap;
        }

        .content-body .bk-table-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #f7ebeb;
            box-shadow: 0 8px 24px rgba(74,8,18,.08);
            overflow: hidden;
            animation: bkFadeUp .5s .2s ease both;
        }

        .content-body .bk-table-wrap {
            overflow-x: auto;
        }

        .content-body table.bk-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
            font-size: 13.5px;
        }

        .content-body .bk-table thead th {
            background: linear-gradient(135deg, #8f1226, #5c0a14);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 15px 16px;
            text-align: left;
            white-space: nowrap;
        }

        .content-body .bk-table thead th:first-child {
            padding-left: 20px;
        }

        .content-body .bk-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #f7ebeb;
            color: #4a3f40;
            vertical-align: middle;
        }

        .content-body .bk-table tbody td:first-child {
            padding-left: 20px;
        }

        .content-body .bk-table tbody tr {
            transition: background .2s;
            animation: bkRowIn .45s ease both;
        }

        .content-body .bk-table tbody tr:hover {
            background: #fdf5f6;
        }

        .content-body .bk-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* COVER */
        .content-body .bk-cover-img {
            width: 48px;
            height: 68px;
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 3px 8px rgba(0,0,0,.15);
            display: block;
            background: #faf0f0;
        }

        .content-body .bk-no-cover {
            width: 48px;
            height: 68px;
            border-radius: 6px;
            background: #fdf3f3;
            border: 1px dashed #e3c8c8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #b09a9c;
            font-size: 10px;
            font-weight: 600;
            text-align: center;
        }

        .content-body .bk-no {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 99px;
            background: #fdf3f3;
            color: #8f1226;
            font-size: 12px;
            font-weight: 600;
        }

        .content-body .bk-kode {
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 12px;
            font-weight: 600;
            color: #5c0a14;
            background: #faf0f0;
            border: 1px dashed #e3c8c8;
            padding: 4px 10px;
            border-radius: 8px;
            white-space: nowrap;
        }

        .content-body .bk-judul {
            font-weight: 600;
            color: #2b0509;
            max-width: 200px;
        }

        .content-body .bk-penulis {
            color: #6b5b5d;
        }

        .content-body .bk-kategori {
            display: inline-block;
            font-size: 11.5px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 99px;
            white-space: nowrap;
        }

        .bk-kat-0 {
            background: #fde8ee;
            color: #a4133c;
        }

        .bk-kat-1 {
            background: #fdf3dd;
            color: #9a6b0f;
        }

        .bk-kat-2 {
            background: #e5f3ec;
            color: #1e6b3a;
        }

        .bk-kat-3 {
            background: #e8eefb;
            color: #3b5bab;
        }

        .bk-kat-4 {
            background: #f4ecfb;
            color: #7a3ba8;
        }

        .bk-kat-5 {
            background: #fef0e7;
            color: #b05a1f;
        }

        .content-body .bk-tahun {
            display: inline-block;
            font-size: 12.5px;
            font-weight: 500;
            color: #6b5b5d;
            background: #f7f2f2;
            padding: 4px 11px;
            border-radius: 8px;
        }

        .content-body .bk-stok {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 8px;
            text-align: center;
        }

        .bk-stok-ada {
            background: #e5f3ec;
            color: #1e6b3a;
        }

        .bk-stok-habis {
            background: #fde8ee;
            color: #a4133c;
        }

        .content-body .bk-actions {
            display: flex;
            gap: 8px;
        }

        .content-body .bk-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 13px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: transform .2s, box-shadow .2s, filter .2s;
            white-space: nowrap;
        }

        .content-body .bk-btn svg {
            width: 13.5px;
            height: 13.5px;
        }

        .content-body .bk-btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.06);
        }

        .content-body .bk-btn:active {
            transform: translateY(0) scale(.97);
        }

        .content-body .bk-btn-edit {
            background: linear-gradient(135deg, #e9c46a, #d4a13d);
            color: #4a3208;
            box-shadow: 0 4px 10px rgba(212,161,61,.35);
        }

        .content-body .bk-btn-hapus {
            background: linear-gradient(135deg, #d93025, #a61b1b);
            color: #fff;
            box-shadow: 0 4px 10px rgba(166,27,27,.35);
        }

        .content-body .bk-status {
            padding: 56px 20px;
            text-align: center;
            color: #8a7f80;
        }

        .content-body .bk-status svg {
            width: 44px;
            height: 44px;
            color: #d8bfc2;
            margin-bottom: 12px;
        }

        .content-body .bk-status p {
            font-size: 14px;
            margin-bottom: 4px;
        }

        .content-body .bk-status small {
            font-size: 12.5px;
            color: #b09a9c;
        }

        .content-body .bk-status.bk-error svg {
            color: #d93025;
        }

        .content-body .bk-status.bk-error p {
            color: #b3261e;
            font-weight: 600;
        }

        .content-body .bk-btn-retry {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #c9184a, #8f1226);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 6px 14px rgba(143,18,38,.35);
            transition: transform .2s;
        }

        .content-body .bk-btn-retry:hover {
            transform: translateY(-2px);
        }

        .content-body .bk-btn-retry svg {
            width: 15px;
            height: 15px;
            color: #fff;
        }

        .content-body .bk-spinner {
            width: 34px;
            height: 34px;
            margin: 0 auto 14px;
            border: 3px solid #f3dede;
            border-top-color: #8f1226;
            border-radius: 50%;
            animation: bkSpin .8s linear infinite;
        }

        @keyframes bkSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes bkFadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes bkRowIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @media (max-width: 640px) {
            .content-body .bk-title {
                font-size: 19px;
            }

            .content-body .bk-btn-add {
                width: 100%;
                justify-content: center;
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

            <div class="bk-page-header">

                <h2 class="bk-title">

                    <svg viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round">

                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>

                    </svg>

                    <span>
                        Data Buku Perpustakaan
                        <small>
                            Kelola koleksi buku — tambah, ubah, dan hapus data
                        </small>
                    </span>

                </h2>

                <a href="tambah.php" class="bk-btn-add">

                    <svg viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2.5"
                         stroke-linecap="round"
                         stroke-linejoin="round">

                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>

                    </svg>

                    Tambah Buku

                </a>

            </div>


            <div class="bk-toolbar">

                <div class="bk-search">

                    <svg viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round">

                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>

                    </svg>

                    <input
                        type="text"
                        id="bkCari"
                        placeholder="Cari judul, penulis, kategori, kode..."
                        autocomplete="off"
                    >

                </div>

                <span class="bk-count" id="bkCount">
                    Memuat...
                </span>

            </div>


            <div class="bk-table-card">

                <div class="bk-table-wrap">

                    <table class="bk-table">

                        <thead>

                            <tr>
                                <th>No</th>
                                <th>Cover</th>
                                <th>Kode Buku</th>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Kategori</th>
                                <th>Tahun</th>
                                <th>Penerbit</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>

                        </thead>

                        <tbody id="buku-table-body">

                            <tr>

                                <td colspan="10">

                                    <div class="bk-status">

                                        <div class="bk-spinner"></div>

                                        <p>
                                            Memuat data dari API...
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


<script>

function escapeHtml(str) {

    return String(str ?? '').replace(/[&<>"']/g, function(s) {

        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[s];

    });

}


function escapeJsParam(str) {

    return String(str ?? '')
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'");

}


function kategoriClass(nama) {

    let hash = 0;

    const strNama = String(nama ?? 'Umum');

    for (let i = 0; i < strNama.length; i++) {

        hash = (hash * 31 + strNama.charCodeAt(i)) >>> 0;

    }

    return 'bk-kat-' + (hash % 6);

}


const ICON_EDIT = `
<svg viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="2"
     stroke-linecap="round"
     stroke-linejoin="round">

    <path d="M12 20h9"/>
    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>

</svg>`;


const ICON_TRASH = `
<svg viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="2"
     stroke-linecap="round"
     stroke-linejoin="round">

    <polyline points="3 6 5 6 21 6"/>
    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>

</svg>`;


const ICON_RETRY = `
<svg viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="2"
     stroke-linecap="round"
     stroke-linejoin="round">

    <polyline points="23 4 23 10 17 10"/>
    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>

</svg>`;


let semuaBuku = [];


/* =========================================================
   LOAD DATA API
========================================================= */

function loadBuku() {

    const tbody = document.getElementById('buku-table-body');

    tbody.innerHTML = `
        <tr>
            <td colspan="10">

                <div class="bk-status">

                    <div class="bk-spinner"></div>

                    <p>Memuat data dari API...</p>

                </div>

            </td>
        </tr>
    `;


    fetch("data_json.php")

        .then(response => {

            if (!response.ok) {

                throw new Error(
                    "Gagal mengambil data HTTP " + response.status
                );

            }

            return response.json();

        })

        .then(data => {

            semuaBuku = Array.isArray(data) ? data : [];

            renderTabel(semuaBuku);

        })

        .catch(error => {

            console.error("Error fetching API:", error);

            tbody.innerHTML = `

                <tr>

                    <td colspan="10">

                        <div class="bk-status bk-error">

                            <svg viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">

                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>

                                <line x1="12" y1="9" x2="12" y2="13"/>

                                <line x1="12" y1="17" x2="12.01" y2="17"/>

                            </svg>

                            <p>Gagal memuat data dari API.</p>

                            <small>
                                Periksa koneksi atau pastikan endpoint API valid.
                            </small>

                            <br>

                            <button
                                type="button"
                                class="bk-btn-retry"
                                onclick="loadBuku()">

                                ${ICON_RETRY}

                                Coba Lagi

                            </button>

                        </div>

                    </td>

                </tr>

            `;

            document.getElementById('bkCount').textContent = '0 buku';

        });

}


/* =========================================================
   RENDER TABEL
========================================================= */

function renderTabel(data) {

    const tbody = document.getElementById('buku-table-body');


    if (data.length === 0) {

        const keyword =
            document.getElementById('bkCari').value.trim();


        tbody.innerHTML = `

            <tr>

                <td colspan="10">

                    <div class="bk-status">

                        <p>
                            <b>
                                ${
                                    keyword
                                    ? 'Buku tidak ditemukan.'
                                    : 'Tidak ada data buku.'
                                }
                            </b>
                        </p>

                        <small>
                            ${
                                keyword
                                ? 'Coba cari dengan kata kunci lain.'
                                : 'Tambahkan buku pertama melalui tombol "Tambah Buku".'
                            }
                        </small>

                    </div>

                </td>

            </tr>

        `;


        document.getElementById('bkCount').textContent =
            keyword
            ? `0 dari ${semuaBuku.length} buku`
            : '0 buku';

        return;

    }


    tbody.innerHTML = '';


    data.forEach((buku, index) => {

        const tr = document.createElement('tr');

        tr.style.animationDelay =
            (index * 0.03) + 's';


        const jumlahStok =
            buku.stok ?? 0;


        /* =================================================
           BAGIAN COVER
           ================================================= */

        let coverHtml = `
            <div class="bk-no-cover">
                No Cover
            </div>
        `;


        if (buku.cover && String(buku.cover).trim() !== '') {

            const namaCover =
                String(buku.cover).trim();


            let srcCover = '';


            /*
             * Kalau API menyimpan URL lengkap
             */
            if (/^https?:\/\//i.test(namaCover)) {

                srcCover = namaCover;

            }

            /*
             * Kalau nama file adalah default_cover.jpg
             */
            else if (namaCover === 'default_cover.jpg') {

                srcCover =
                    '/perpus_api/uploads/cover/default_cover.jpg';

            }

            /*
             * Semua cover upload dari edit.php
             * disimpan di uploads/cover/
             */
            else {

                srcCover =
                    '/perpus_api/uploads/cover/' +
                    encodeURIComponent(namaCover);

            }


            coverHtml = `
                <img
                    src="${escapeHtml(srcCover)}"
                    alt="Cover ${escapeHtml(buku.judul)}"
                    class="bk-cover-img"
                    loading="lazy"
                    onerror="this.onerror=null; this.outerHTML='<div class=&quot;bk-no-cover&quot;>Cover<br>tidak ditemukan</div>'"
                >
            `;

        }


        const stokClass =
            Number(jumlahStok) > 0
            ? 'bk-stok-ada'
            : 'bk-stok-habis';


        tr.innerHTML = `

            <td>
                <span class="bk-no">
                    ${index + 1}
                </span>
            </td>


            <td>
                ${coverHtml}
            </td>


            <td>
                <span class="bk-kode">
                    ${escapeHtml(buku.kode_buku)}
                </span>
            </td>


            <td class="bk-judul">
                ${escapeHtml(buku.judul)}
            </td>


            <td class="bk-penulis">
                ${escapeHtml(buku.penulis)}
            </td>


            <td>

                <span class="bk-kategori ${kategoriClass(buku.kategori)}">

                    ${escapeHtml(buku.kategori)}

                </span>

            </td>


            <td>

                <span class="bk-tahun">

                    ${escapeHtml(buku.tahun_terbit)}

                </span>

            </td>


            <td>

                ${escapeHtml(buku.penerbit)}

            </td>


            <td>

                <span class="bk-stok ${stokClass}">

                    ${escapeHtml(jumlahStok)}

                </span>

            </td>


            <td>

                <div class="bk-actions">

                    <a
                        href="edit.php?id=${encodeURIComponent(buku.id)}"
                        class="bk-btn bk-btn-edit">

                        ${ICON_EDIT}

                        Edit

                    </a>


                    <a
                        href="hapus.php?id=${encodeURIComponent(buku.id)}"
                        class="bk-btn bk-btn-hapus"
                        onclick="return confirm('Apakah Anda yakin ingin menghapus &quot;${escapeJsParam(buku.judul)}&quot;?')">

                        ${ICON_TRASH}

                        Hapus

                    </a>

                </div>

            </td>

        `;


        tbody.appendChild(tr);

    });


    const keyword =
        document.getElementById('bkCari').value.trim();


    document.getElementById('bkCount').textContent =
        keyword
        ? `${data.length} dari ${semuaBuku.length} buku`
        : `${semuaBuku.length} buku`;

}


/* =========================================================
   PENCARIAN
========================================================= */

document
    .getElementById('bkCari')
    .addEventListener('input', function () {

        const q =
            this.value.trim().toLowerCase();


        if (!q) {

            renderTabel(semuaBuku);

            return;

        }


        const hasilFilter =
            semuaBuku.filter(buku => {

                const targetText = [

                    buku.kode_buku,
                    buku.judul,
                    buku.penulis,
                    buku.kategori,
                    buku.tahun_terbit,
                    buku.penerbit,
                    buku.stok

                ]
                .join(' ')
                .toLowerCase();


                return targetText.includes(q);

            });


        renderTabel(hasilFilter);

    });


/* =========================================================
   JALANKAN SAAT HALAMAN SELESAI
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    loadBuku
);

</script>

</body>
</html>