<?php
session_start();
require_once 'koneksi.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['admin_login'])) {
    header("Location: dashboard.php");
    exit;
}

 $error   = '';
 $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username        = trim(mysqli_real_escape_string($koneksi, $_POST['username']));
    $password        = $_POST['password'];
    $konfirmasi_pass = $_POST['konfirmasi_password'];

    // Validasi Input
    if (empty($username) || empty($password) || empty($konfirmasi_pass)) {
        $error = "Semua kolom wajib diisi!";
    } elseif ($password !== $konfirmasi_pass) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        // Cek apakah username sudah digunakan
        $check_user = mysqli_query($koneksi, "SELECT * FROM admins WHERE username = '$username'");
        if (mysqli_num_rows($check_user) > 0) {
            $error = "Username sudah terdaftar! Gunakan username lain.";
        } else {
            // Hash password demi keamanan
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

            // Simpan data ke database
            $query = "INSERT INTO admins (username, password) VALUES ('$username', '$password_hashed')";
            if (mysqli_query($koneksi, $query)) {
                $success = "Pendaftaran berhasil! Silakan <a href='login.php'>Login di sini</a>.";
            } else {
                $error = "Gagal mendaftar: " . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register Admin — Perpustakaan Digital</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Poppins', sans-serif;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px 20px;
        background: linear-gradient(-45deg, #2b0509, #5c0a14, #7a0e1c, #3d060e);
        background-size: 400% 400%;
        animation: gradientMove 14s ease infinite;
        overflow-x: hidden;
    }

    @keyframes gradientMove {
        0%   { background-position: 0% 50%; }
        50%  { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    /* ====== DEKORASI BACKGROUND ====== */
    .bg-decor {
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 1;
        overflow: hidden;
    }
    .bg-shape {
        position: absolute;
        border-radius: 50%;
        filter: blur(70px);
        opacity: .5;
    }
    .shape-1 { width: 360px; height: 360px; background: #a4133c; top: -90px; left: -90px;    animation: floatA 10s ease-in-out infinite; }
    .shape-2 { width: 300px; height: 300px; background: #800f2f; bottom: -80px; right: -70px; animation: floatB 12s ease-in-out infinite; }
    .shape-3 { width: 220px; height: 220px; background: #c9184a; top: 55%; left: 10%;       animation: floatA 14s ease-in-out infinite reverse; }

    @keyframes floatA {
        0%, 100% { transform: translate(0,0) scale(1); }
        50%      { transform: translate(35px, 45px) scale(1.08); }
    }
    @keyframes floatB {
        0%, 100% { transform: translate(0,0) scale(1); }
        50%      { transform: translate(-40px, -35px) scale(1.1); }
    }

    .particle {
        position: absolute;
        bottom: -12px;
        border-radius: 50%;
        background: rgba(255,255,255,.30);
        animation: rise 16s linear infinite;
    }
    .particle:nth-of-type(1) { left: 6%;  width: 7px; height: 7px; animation-duration: 15s; }
    .particle:nth-of-type(2) { left: 18%; width: 4px; height: 4px; animation-duration: 19s; animation-delay: 3s; }
    .particle:nth-of-type(3) { left: 32%; width: 5px; height: 5px; animation-duration: 13s; animation-delay: 6s; }
    .particle:nth-of-type(4) { left: 55%; width: 6px; height: 6px; animation-duration: 18s; animation-delay: 2s; }
    .particle:nth-of-type(5) { left: 70%; width: 4px; height: 4px; animation-duration: 14s; animation-delay: 8s; }
    .particle:nth-of-type(6) { left: 84%; width: 6px; height: 6px; animation-duration: 20s; animation-delay: 4s; }
    .particle:nth-of-type(7) { left: 93%; width: 5px; height: 5px; animation-duration: 16s; animation-delay: 10s; }

    @keyframes rise {
        0%   { transform: translateY(0);      opacity: 0; }
        10%  { opacity: .8; }
        100% { transform: translateY(-105vh); opacity: 0; }
    }

    /* ====== KARTU REGISTER ====== */
    .register-card {
        width: 100%;
        max-width: 460px;
        background: rgba(255,255,255,.98);
        border-radius: 20px;
        padding: 44px 38px 30px;
        position: relative;
        z-index: 2;
        box-shadow: 0 25px 60px rgba(0,0,0,.45);
        animation: cardIn .8s cubic-bezier(.22,1,.36,1) both;
    }

    .register-card::before {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 5px;
        border-radius: 20px 20px 0 0;
        background: linear-gradient(90deg, #8f1226, #c9184a, #e9c46a, #8f1226);
        background-size: 200% 100%;
        animation: shimmer 5s linear infinite;
    }

    @keyframes shimmer { to { background-position: 200% 0; } }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(50px) scale(.96); }
        to   { opacity: 1; transform: translateY(0)    scale(1); }
    }

    /* ====== LOGO & JUDUL ====== */
    .logo-badge {
        width: 78px; height: 78px;
        margin: 0 auto 16px;
        border-radius: 22px;
        background: linear-gradient(135deg, #8f1226, #4a0812);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 12px 25px rgba(122,14,28,.45), inset 0 1px 0 rgba(255,255,255,.15);
        animation: popIn .7s .2s cubic-bezier(.34,1.56,.64,1) both;
    }
    .logo-badge svg { width: 38px; height: 38px; color: #e9c46a; }

    @keyframes popIn {
        from { opacity: 0; transform: scale(.4) rotate(-12deg); }
        to   { opacity: 1; transform: scale(1)  rotate(0); }
    }

    .brand {
        display: block;
        text-align: center;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: #a4133c;
        margin-bottom: 4px;
    }
    .title {
        text-align: center;
        font-size: 24px;
        font-weight: 600;
        color: #2b0509;
        margin-bottom: 6px;
    }
    .subtitle {
        text-align: center;
        font-size: 13.5px;
        color: #8a7f80;
        margin-bottom: 24px;
    }

    /* ====== ALERT ====== */
    .alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 10px;
        font-size: 13.5px;
        line-height: 1.5;
        margin-bottom: 18px;
        animation: alertIn .4s ease, shake .45s .15s ease;
    }
    .alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }

    .alert-error {
        background: #fdecea;
        color: #b3261e;
        border: 1px solid #f5c6c2;
        border-left: 4px solid #d93025;
    }
    .alert-success {
        background: #e8f6ee;
        color: #1e6b3a;
        border: 1px solid #c3e6cb;
        border-left: 4px solid #2e9e5b;
        animation: alertIn .4s ease both;
    }
    .alert-success a {
        color: #14602f;
        font-weight: 600;
        text-decoration: underline;
    }
    .alert-success a:hover { color: #0d4522; }

    @keyframes alertIn {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: none; }
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20% { transform: translateX(-6px); }
        40% { transform: translateX(6px); }
        60% { transform: translateX(-4px); }
        80% { transform: translateX(4px); }
    }

    /* ====== FORM ====== */
    .form-group { margin-bottom: 18px; animation: fadeUp .6s ease both; }
    .form-group:nth-of-type(1) { animation-delay: .25s; }
    .form-group:nth-of-type(2) { animation-delay: .35s; }
    .form-group:nth-of-type(3) { animation-delay: .45s; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(15px); }
        to   { opacity: 1; transform: none; }
    }

    label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #4a3f40;
        margin-bottom: 8px;
        letter-spacing: .3px;
    }

    .input-wrap { position: relative; }

    .input-wrap > svg.icon {
        position: absolute;
        left: 15px; top: 50%;
        transform: translateY(-50%);
        width: 19px; height: 19px;
        color: #b09a9c;
        transition: color .3s;
        pointer-events: none;
    }
    .input-wrap:focus-within > svg.icon { color: #8f1226; }

    input[type="text"], input[type="password"] {
        width: 100%;
        padding: 13px 46px 13px 47px;
        border: 1.5px solid #e6dcdc;
        border-radius: 12px;
        font-size: 14.5px;
        font-family: inherit;
        color: #2b0509;
        background: #faf7f7;
        outline: none;
        transition: border-color .3s, box-shadow .3s, background .3s;
    }
    input::placeholder { color: #c2b2b4; }
    input:hover  { border-color: #d3bbbb; }
    input:focus  {
        border-color: #8f1226;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(143,18,38,.12);
    }

    /* Status validasi real-time */
    input.input-match    { border-color: #2e9e5b !important; background: #f4fbf6; }
    input.input-mismatch { border-color: #d93025 !important; background: #fdf5f4; }

    .toggle-pass {
        position: absolute;
        right: 8px; top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 8px;
        border-radius: 8px;
        display: flex;
        color: #b09a9c;
        transition: color .3s, background .3s;
    }
    .toggle-pass:hover  { color: #8f1226; background: rgba(143,18,38,.07); }
    .toggle-pass svg { width: 19px; height: 19px; }

    /* Pesan kecocokan password */
    .match-msg {
        display: none;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        margin-top: 7px;
        animation: fadeUp .3s ease both;
    }
    .match-msg.show { display: flex; }
    .match-msg svg { width: 15px; height: 15px; flex-shrink: 0; }
    .match-ok   { color: #2e9e5b; }
    .match-bad  { color: #d93025; }

    /* Meter kekuatan password */
    .strength-meter {
        display: flex;
        gap: 6px;
        margin-top: 9px;
    }
    .strength-bar {
        flex: 1;
        height: 5px;
        border-radius: 99px;
        background: #ece3e3;
        transition: background .35s ease;
    }
    .strength-label {
        font-size: 12px;
        margin-top: 6px;
        color: #a29294;
        min-height: 16px;
        transition: color .3s;
    }

    /* ====== TOMBOL DAFTAR ====== */
    .btn-register {
        width: 100%;
        padding: 14px;
        margin-top: 8px;
        background: linear-gradient(135deg, #8f1226, #5c0a14);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: 2px;
        font-family: inherit;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 22px rgba(92,10,20,.4);
        transition: transform .25s, box-shadow .25s;
        animation: fadeUp .6s .55s ease both;
    }
    .btn-register:hover  { transform: translateY(-2px); box-shadow: 0 14px 28px rgba(92,10,20,.55); }
    .btn-register:active { transform: translateY(0) scale(.98); }
    .btn-register:disabled { opacity: .75; cursor: not-allowed; transform: none; }

    .btn-register::before {
        content: "";
        position: absolute;
        top: 0; left: -100%;
        width: 60%; height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.3), transparent);
        transform: skewX(-20deg);
        transition: left .6s;
    }
    .btn-register:hover::before { left: 130%; }

    .btn-register svg { width: 18px; height: 18px; transition: transform .3s; }
    .btn-register:hover svg { transform: translateX(4px); }

    .spinner {
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,.4);
        border-top-color: #fff;
        border-radius: 50%;
        display: inline-block;
        animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ====== LINK LOGIN ====== */
    .login-link {
        text-align: center;
        font-size: 13.5px;
        color: #8a7f80;
        margin-top: 22px;
        animation: fadeUp .6s .65s ease both;
    }
    .login-link a {
        color: #8f1226;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: color .3s;
    }
    .login-link a:hover { color: #4a0812; }
    .login-link a svg { width: 15px; height: 15px; }

    .footer {
        position: fixed;
        bottom: 14px; left: 0; right: 0;
        text-align: center;
        font-size: 12px;
        letter-spacing: .5px;
        color: rgba(255,255,255,.55);
        z-index: 2;
    }

    @media (max-width: 480px) {
        .register-card { padding: 36px 22px 26px; border-radius: 16px; }
        .title { font-size: 21px; }
    }
</style>
</head>
<body>

    <!-- Dekorasi background -->
    <div class="bg-decor" aria-hidden="true">
        <div class="bg-shape shape-1"></div>
        <div class="bg-shape shape-2"></div>
        <div class="bg-shape shape-3"></div>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
    </div>

    <div class="register-card">
        <div class="logo-badge">
            <!-- Ikon buku terbuka -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
        </div>

        <span class="brand">Perpustakaan Digital</span>
        <h1 class="title">Daftar Akun Admin</h1>
        <p class="subtitle">Buat akun baru untuk mengakses panel admin</p>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <!-- Ikon peringatan -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span><?= $error; ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <!-- Ikon centang lingkaran -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <span><?= $success; ?></span>
            </div>
        <?php endif; ?>

        <form id="registerForm" action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <!-- Ikon user -->
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <input type="text" id="username" name="username" placeholder="Pilih username" required autocomplete="off"
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <!-- Ikon gembok -->
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <input type="password" id="password" name="password" placeholder="Buat password" required autocomplete="new-password">
                    <button type="button" class="toggle-pass" onclick="togglePass('password','eyeOpen1','eyeOff1')" aria-label="Tampilkan password">
                        <svg id="eyeOpen1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="eyeOff1" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>

                <!-- Meter kekuatan password -->
                <div class="strength-meter">
                    <span class="strength-bar" id="bar1"></span>
                    <span class="strength-bar" id="bar2"></span>
                    <span class="strength-bar" id="bar3"></span>
                    <span class="strength-bar" id="bar4"></span>
                </div>
                <div class="strength-label" id="strengthLabel">Kekuatan password akan muncul di sini</div>
            </div>

            <div class="form-group">
                <label for="konfirmasi_password">Konfirmasi Password</label>
                <div class="input-wrap">
                    <!-- Ikon perisai centang -->
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <polyline points="9 12 11 14 15 10"/>
                    </svg>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi password" required autocomplete="new-password">
                    <button type="button" class="toggle-pass" onclick="togglePass('konfirmasi_password','eyeOpen2','eyeOff2')" aria-label="Tampilkan konfirmasi password">
                        <svg id="eyeOpen2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="eyeOff2" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>

                <!-- Pesan kecocokan real-time -->
                <div class="match-msg match-ok" id="matchOk">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Password cocok
                </div>
                <div class="match-msg match-bad" id="matchBad">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Password belum cocok
                </div>
            </div>

            <button type="submit" id="btnRegister" class="btn-register">
                DAFTAR AKUN
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </button>
        </form>

        <p class="login-link">
            Sudah punya akun?
            <a href="login.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Login di sini
            </a>
        </p>
    </div>

    <p class="footer">&copy; <?= date('Y'); ?> Perpustakaan Digital &mdash; Panel Admin</p>

    <script>
        // ====== Toggle tampilkan / sembunyikan password ======
        function togglePass(inputId, eyeOpenId, eyeOffId) {
            const input   = document.getElementById(inputId);
            const eyeOpen = document.getElementById(eyeOpenId);
            const eyeOff  = document.getElementById(eyeOffId);

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.style.display = 'none';
                eyeOff.style.display  = 'block';
            } else {
                input.type = 'password';
                eyeOpen.style.display = 'block';
                eyeOff.style.display  = 'none';
            }
        }

        const pwd  = document.getElementById('password');
        const conf = document.getElementById('konfirmasi_password');

        // ====== Meter kekuatan password ======
        function checkStrength(val) {
            let score = 0;
            if (val.length >= 6)  score++;
            if (val.length >= 10) score++;
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
            if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) score++;
            return score;
        }

        pwd.addEventListener('input', function () {
            const val = this.value;
            const score = checkStrength(val);
            const bars = [
                document.getElementById('bar1'),
                document.getElementById('bar2'),
                document.getElementById('bar3'),
                document.getElementById('bar4')
            ];
            const label = document.getElementById('strengthLabel');

            const levels = [
                { text: 'Kekuatan password akan muncul di sini', color: '#a29294' },
                { text: 'Lemah — tambah panjang atau kombinasi', color: '#d93025' },
                { text: 'Sedang — cukup baik',                   color: '#e09f3e' },
                { text: 'Kuat — password aman',                  color: '#2e9e5b' },
                { text: 'Sangat kuat — excellent!',              color: '#1e6b3a' }
            ];

            bars.forEach((bar, i) => {
                if (val && i < score) {
                    bar.style.background = levels[score].color;
                } else {
                    bar.style.background = '#ece3e3';
                }
            });

            if (val) {
                label.textContent = levels[score].text;
                label.style.color = levels[score].color;
            } else {
                label.textContent = levels[0].text;
                label.style.color = '#a29294';
            }

            validateMatch();
        });

        // ====== Validasi kecocokan real-time ======
        function validateMatch() {
            const okMsg  = document.getElementById('matchOk');
            const badMsg = document.getElementById('matchBad');

            if (conf.value === '') {
                okMsg.classList.remove('show');
                badMsg.classList.remove('show');
                conf.classList.remove('input-match', 'input-mismatch');
                return;
            }

            if (pwd.value === conf.value) {
                okMsg.classList.add('show');
                badMsg.classList.remove('show');
                conf.classList.add('input-match');
                conf.classList.remove('input-mismatch');
            } else {
                okMsg.classList.remove('show');
                badMsg.classList.add('show');
                conf.classList.add('input-mismatch');
                conf.classList.remove('input-match');
            }
        }

        conf.addEventListener('input', validateMatch);

        // ====== Cegah submit jika password belum cocok + status loading ======
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            if (pwd.value !== conf.value) {
                e.preventDefault();
                validateMatch();
                conf.focus();
                return;
            }

            const btn = document.getElementById('btnRegister');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> MEMPROSES...';
        });
    </script>
</body>
</html>