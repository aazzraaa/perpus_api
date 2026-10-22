<?php

$currentPage = basename($_SERVER['PHP_SELF']);

/*
|--------------------------------------------------------------------------
| ACTIVE MENU
|--------------------------------------------------------------------------
*/

$isDashboard = $currentPage === 'dashboard.php';

$isBuku = in_array(
    $currentPage,
    [
        'buku.php',
        'tambah.php',
        'edit.php',
        'hapus.php'
    ],
    true
);

$isTambah = $currentPage === 'tambah.php';

$isUser = in_array(
    $currentPage,
    [
        'users.php',
        'tambah_user.php',
        'edit_user.php',
        'hapus_user.php'
    ],
    true
);

$isExcel = $currentPage === 'export_excel.php';

$isPdf = $currentPage === 'export_pdf.php';

$isApi = $currentPage === 'data_json.php';

?>

<aside class="sidebar">

    <!-- =====================================================
         BRAND
    ====================================================== -->

    <div class="sidebar-brand">

        <a href="dashboard.php" class="brand">

            <div class="brand-logo">

                <i data-lucide="library-big"></i>

            </div>

            <div class="brand-text">

                <strong>PERPUS API</strong>

                <span>ADMIN PANEL</span>

            </div>

        </a>

    </div>


    <!-- =====================================================
         NAVIGATION
    ====================================================== -->

    <nav class="sidebar-nav">

        <!-- MENU UTAMA -->

        <div class="nav-section-title">
            MENU UTAMA
        </div>


        <!-- =================================================
             DASHBOARD
        ================================================== -->

        <a
            href="dashboard.php"
            class="nav-item <?= $isDashboard ? 'active' : '' ?>"
        >

            <span class="nav-icon">

                <i data-lucide="layout-dashboard"></i>

            </span>

            <span class="nav-text">
                Dashboard
            </span>

        </a>


        <!-- =================================================
             DATA BUKU
        ================================================== -->

        <a
            href="buku.php"
            class="nav-item <?= $isBuku ? 'active' : '' ?>"
        >

            <span class="nav-icon">

                <i data-lucide="book-open"></i>

            </span>

            <span class="nav-text">
                Data Buku
            </span>

        </a>


        <!-- =================================================
             TAMBAH BUKU
        ================================================== -->

        <a
            href="tambah.php"
            class="nav-item <?= $isTambah ? 'active' : '' ?>"
        >

            <span class="nav-icon">

                <i data-lucide="book-plus"></i>

            </span>

            <span class="nav-text">
                Tambah Buku
            </span>

        </a>


        <!-- =================================================
             USER TERDAFTAR
        ================================================== -->

        <a
            href="users.php"
            class="nav-item <?= $isUser ? 'active' : '' ?>"
        >

            <span class="nav-icon">

                <i data-lucide="users"></i>

            </span>

            <span class="nav-text">
                User Terdaftar
            </span>

        </a>


        <!-- =================================================
             SECTION DATA & EXPORT
        ================================================== -->

        <div class="nav-section-title nav-section-second">
            DATA & EXPORT
        </div>


        <!-- =================================================
             EXPORT EXCEL
        ================================================== -->

        <a
            href="export_excel.php"
            class="nav-item <?= $isExcel ? 'active' : '' ?>"
        >

            <span class="nav-icon">

                <i data-lucide="file-spreadsheet"></i>

            </span>

            <span class="nav-text">
                Export Excel
            </span>

        </a>


        <!-- =================================================
             EXPORT PDF
        ================================================== -->

        <a
            href="export_pdf.php"
            class="nav-item <?= $isPdf ? 'active' : '' ?>"
        >

            <span class="nav-icon">

                <i data-lucide="file-text"></i>

            </span>

            <span class="nav-text">
                Export PDF
            </span>

        </a>


        <!-- =================================================
             JSON API
        ================================================== -->

        <a
            href="data_json.php"
            target="_blank"
            class="nav-item <?= $isApi ? 'active' : '' ?>"
        >

            <span class="nav-icon">

                <i data-lucide="braces"></i>

            </span>

            <span class="nav-text">
                JSON API
            </span>

            <i
                data-lucide="external-link"
                class="external-icon"
            ></i>

        </a>

    </nav>


    <!-- =====================================================
         SIDEBAR BOTTOM
    ====================================================== -->

    <div class="sidebar-bottom">

        <div class="sidebar-divider"></div>


        <!-- =================================================
             ADMIN PROFILE
        ================================================== -->

        <div class="admin-profile">

            <div class="admin-avatar">

                <i data-lucide="user-round"></i>

            </div>

            <div class="admin-info">

                <span class="admin-label">
                    LOGIN SEBAGAI
                </span>

                <strong>
                    <?= e($_SESSION['admin_username'] ?? 'Admin') ?>
                </strong>

            </div>

            <span class="online-dot"></span>

        </div>


        <!-- =================================================
             LOGOUT
        ================================================== -->

        <a
            href="logout.php"
            class="logout-link"
        >

            <span class="logout-icon">

                <i data-lucide="log-out"></i>

            </span>

            <span>
                Logout
            </span>

        </a>

    </div>

</aside>


<!-- =====================================================
     LUCIDE ICON
====================================================== -->

<script src="https://unpkg.com/lucide@latest"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    if (typeof lucide !== 'undefined') {

        lucide.createIcons();

    }

});

</script>


<style>

/* =====================================================
   SIDEBAR
====================================================== */

.sidebar {

    position: fixed;

    top: 0;
    left: 0;

    z-index: 1000;

    width: 260px;
    height: 100vh;

    display: flex;
    flex-direction: column;

    background:
        linear-gradient(
            180deg,
            #4d0d18 0%,
            #5f101e 45%,
            #4a0b15 100%
        );

    color: #ffffff;

    border-right:
        1px solid rgba(255,255,255,.06);

    box-shadow:
        8px 0 30px rgba(45,5,12,.12);

    overflow-y: auto;
    overflow-x: hidden;

    box-sizing: border-box;

}


/* =====================================================
   BRAND
====================================================== */

.sidebar-brand {

    padding: 25px 20px 22px;

    border-bottom:
        1px solid rgba(255,255,255,.08);

    flex-shrink: 0;

}


.brand {

    display: flex;

    align-items: center;

    gap: 12px;

    width: 100%;

    text-decoration: none;

    color: #ffffff;

}


.brand-logo {

    width: 44px;
    height: 44px;

    flex-shrink: 0;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 13px;

    background:
        linear-gradient(
            145deg,
            #ffffff,
            #f8dce2
        );

    color: #721626;

    box-shadow:
        0 8px 20px rgba(0,0,0,.16);

}


.brand-logo svg {

    width: 23px;
    height: 23px;

    stroke-width: 2;

}


.brand-text {

    display: flex;

    flex-direction: column;

    gap: 4px;

    min-width: 0;

}


.brand-text strong {

    font-size: 15px;

    line-height: 1;

    letter-spacing: .5px;

}


.brand-text span {

    font-size: 9px;

    line-height: 1;

    color:
        rgba(255,255,255,.56);

    letter-spacing: 1.7px;

    font-weight: 700;

}


/* =====================================================
   NAVIGATION
====================================================== */

.sidebar-nav {

    flex: 1;

    padding: 20px 13px;

}


.nav-section-title {

    padding: 0 12px;

    margin: 3px 0 9px;

    color:
        rgba(255,255,255,.38);

    font-size: 9px;

    font-weight: 800;

    letter-spacing: 1.5px;

}


.nav-section-second {

    margin-top: 25px;

    padding-top: 4px;

}


/* =====================================================
   NAV ITEM
====================================================== */

.nav-item {

    position: relative;

    display: flex;

    align-items: center;

    min-height: 47px;

    width: 100%;

    margin: 4px 0;

    padding: 0 12px;

    gap: 12px;

    box-sizing: border-box;

    border-radius: 11px;

    color:
        rgba(255,255,255,.68);

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

    transition:
        background .2s ease,
        color .2s ease,
        transform .2s ease;

}


.nav-item:hover {

    color: #ffffff;

    background:
        rgba(255,255,255,.075);

    transform:
        translateX(2px);

}


.nav-item.active {

    color: #ffffff;

    background:
        linear-gradient(
            90deg,
            rgba(255,255,255,.16),
            rgba(255,255,255,.075)
        );

    box-shadow:
        inset 0 0 0 1px
        rgba(255,255,255,.06);

}


.nav-item.active::before {

    content: "";

    position: absolute;

    left: 0;

    top: 9px;
    bottom: 9px;

    width: 3px;

    border-radius:
        0 4px 4px 0;

    background:
        #ffd5dc;

}


/* =====================================================
   ICON AREA
====================================================== */

.nav-icon {

    width: 36px;
    height: 36px;

    flex-shrink: 0;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 9px;

    color:
        rgba(255,255,255,.65);

    transition:
        background .2s ease,
        color .2s ease;

}


.nav-icon svg {

    width: 18px;
    height: 18px;

    stroke-width: 1.8;

}


.nav-item:hover .nav-icon {

    color: #ffffff;

}


.nav-item.active .nav-icon {

    color: #ffdce2;

    background:
        rgba(255,255,255,.08);

}


.nav-text {

    flex: 1;

    min-width: 0;

    white-space: nowrap;

}


/* =====================================================
   EXTERNAL ICON
====================================================== */

.external-icon {

    width: 13px !important;
    height: 13px !important;

    flex-shrink: 0;

    color:
        rgba(255,255,255,.32);

    stroke-width: 1.8;

}


/* =====================================================
   SIDEBAR BOTTOM
====================================================== */

.sidebar-bottom {

    flex-shrink: 0;

    padding:
        0 13px 17px;

}


.sidebar-divider {

    height: 1px;

    margin:
        0 6px 15px;

    background:
        rgba(255,255,255,.08);

}


/* =====================================================
   ADMIN PROFILE
====================================================== */

.admin-profile {

    position: relative;

    display: flex;

    align-items: center;

    gap: 11px;

    padding: 11px;

    margin-bottom: 8px;

    border-radius: 12px;

    background:
        rgba(255,255,255,.055);

    border:
        1px solid
        rgba(255,255,255,.06);

}


.admin-avatar {

    width: 37px;
    height: 37px;

    flex-shrink: 0;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 10px;

    background:
        #f8dce2;

    color:
        #741626;

}


.admin-avatar svg {

    width: 18px;
    height: 18px;

    stroke-width: 1.9;

}


.admin-info {

    min-width: 0;

    flex: 1;

    display: flex;

    flex-direction: column;

    gap: 3px;

}


.admin-label {

    font-size: 8px;

    color:
        rgba(255,255,255,.4);

    font-weight: 700;

    letter-spacing: 1px;

}


.admin-info strong {

    display: block;

    max-width: 135px;

    overflow: hidden;

    color: #ffffff;

    font-size: 12px;

    white-space: nowrap;

    text-overflow: ellipsis;

}


.online-dot {

    width: 7px;
    height: 7px;

    flex-shrink: 0;

    margin-left: auto;

    border-radius: 50%;

    background:
        #7ed9a1;

    box-shadow:
        0 0 0 3px
        rgba(126,217,161,.09);

}


/* =====================================================
   LOGOUT
====================================================== */

.logout-link {

    display: flex;

    align-items: center;

    gap: 11px;

    min-height: 43px;

    padding: 0 12px;

    box-sizing: border-box;

    border-radius: 10px;

    color:
        rgba(255,255,255,.58);

    text-decoration: none;

    font-size: 12px;

    font-weight: 600;

    transition:
        background .2s ease,
        color .2s ease;

}


.logout-link:hover {

    color: #ffdce2;

    background:
        rgba(255,255,255,.06);

}


.logout-icon {

    width: 30px;
    height: 30px;

    flex-shrink: 0;

    display: flex;

    align-items: center;
    justify-content: center;

}


.logout-icon svg {

    width: 17px;
    height: 17px;

    stroke-width: 1.9;

}


/* =====================================================
   SCROLLBAR
====================================================== */

.sidebar::-webkit-scrollbar {

    width: 4px;

}


.sidebar::-webkit-scrollbar-track {

    background: transparent;

}


.sidebar::-webkit-scrollbar-thumb {

    border-radius: 10px;

    background:
        rgba(255,255,255,.12);

}


/* =====================================================
   RESPONSIVE
====================================================== */

@media (max-width: 900px) {

    .sidebar {

        width: 230px;

    }

}


@media (max-width: 700px) {

    .sidebar {

        width: 220px;

    }

    .sidebar-brand {

        padding-left: 16px;
        padding-right: 16px;

    }

    .sidebar-nav {

        padding-left: 9px;
        padding-right: 9px;

    }

    .sidebar-bottom {

        padding-left: 9px;
        padding-right: 9px;

    }

}

</style>