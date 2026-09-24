<?php
session_start();
require_once 'koneksi.php';

if (isset($_SESSION['admin_login'])) {
    header("Location: dashboard.php");    
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM admins WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password']) || $password === $row['password']) {
            $_SESSION['admin_login'] = true;
            $_SESSION['admin_user']  = $row['username'];
            
            // PERBAIKAN: Ubah $data['foto'] menjadi $row['foto']
            $_SESSION['admin_foto']  = $row['foto'] ?? ''; 
            
            header("Location: dashboard.php");
            exit;
        }
    }
    $error = "Username atau password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin — Perpustakaan Digital</title>
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
        padding: 20px;
        background: linear-gradient(-45deg, #2b0509, #5c0a14, #7a0e1c, #3d060e);
        background-size: 400% 400%;
        animation: gradientMove 14s ease infinite;
        overflow: hidden;
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
    .shape-1 { width: 360px; height: 360px; background: #a4133c; top: -90px; left: -90px;   animation: floatA 10s ease-in-out infinite; }
    .shape-2 { width: 300px; height: 300px; background: #800f2f; bottom: -80px; right: -70px; animation: floatB 12s ease-in-out infinite; }
    .shape-3 { width: 220px; height: 220px; background: #c9184a; top: 55%; left: 10%;      animation: floatA 14s ease-in-out infinite reverse; }

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

    /* ====== KARTU LOGIN ====== */
    .login-card {
        width: 100%;
        max-width: 430px;
        background: rgba(255,255,255,.98);
        border-radius: 20px;
        padding: 48px 40px 34px;
        position: relative;
        z-index: 2;
        box-shadow: 0 25px 60px rgba(0,0,0,.45);
        animation: cardIn .8s cubic-bezier(.22,1,.36,1) both;
    }

    .login-card::before {
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
        margin-bottom: 26px;
    }

    /* ====== ALERT ERROR ====== */
    .alert {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fdecea;
        color: #b3261e;
        border: 1px solid #f5c6c2;
        border-left: 4px solid #d93025;
        padding: 12px 14px;
        border-radius: 10px;
        font-size: 13.5px;
        margin-bottom: 20px;
        animation: alertIn .4s ease, shake .45s .15s ease;
    }
    .alert svg { width: 18px; height: 18px; flex-shrink: 0; }

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
    .form-group { margin-bottom: 20px; animation: fadeUp .6s ease both; }
    .form-group:nth-of-type(1) { animation-delay: .25s; }
    .form-group:nth-of-type(2) { animation-delay: .35s; }

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
    input:hover { border-color: #d3bbbb; }
    input:focus {
        border-color: #8f1226;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(143,18,38,.12);
    }

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

    /* ====== TOMBOL LOGIN ====== */
    .btn-login {
        width: 100%;
        padding: 14px;
        margin-top: 6px;
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
        animation: fadeUp .6s .45s ease both;
    }
    .btn-login:hover  { transform: translateY(-2px); box-shadow: 0 14px 28px rgba(92,10,20,.55); }
    .btn-login:active { transform: translateY(0) scale(.98); }
    .btn-login:disabled { opacity: .75; cursor: not-allowed; transform: none; }

    .btn-login::before {
        content: "";
        position: absolute;
        top: 0; left: -100%;
        width: 60%; height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.3), transparent);
        transform: skewX(-20deg);
        transition: left .6s;
    }
    .btn-login:hover::before { left: 130%; }

    .btn-login svg { width: 18px; height: 18px; transition: transform .3s; }
    .btn-login:hover svg { transform: translateX(4px); }

    .spinner {
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,.4);
        border-top-color: #fff;
        border-radius: 50%;
        display: inline-block;
        animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ====== LINK DAFTAR ====== */
    .register-link {
        text-align: center;
        font-size: 13.5px;
        color: #8a7f80;
        margin-top: 24px;
        animation: fadeUp .6s .55s ease both;
    }
    .register-link a {
        color: #8f1226;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: color .3s;
    }
    .register-link a:hover { color: #4a0812; }
    .register-link a svg { width: 15px; height: 15px; }

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
        .login-card { padding: 38px 24px 28px; border-radius: 16px; }
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

    <div class="login-card">
        <div class="logo-badge">
            <!-- Ikon buku terbuka -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
        </div>

        <span class="brand">Perpustakaan Digital</span>
        <h1 class="title">Login Admin</h1>
        <p class="subtitle">Silakan masuk untuk mengelola sistem perpustakaan</p>

        <?php if ($error): ?>
            <div class="alert">
                <!-- Ikon peringatan -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span><?= $error; ?></span>
            </div>
        <?php endif; ?>

        <form id="loginForm" action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <!-- Ikon user -->
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="off">
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
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                    <button type="button" class="toggle-pass" onclick="togglePassword()" aria-label="Tampilkan password">
                        <!-- Ikon mata terbuka -->
                        <svg id="eyeOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <!-- Ikon mata tertutup -->
                        <svg id="eyeOff" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" id="btnLogin" class="btn-login">
                MASUK
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
            </button>
        </form>

        <p class="register-link">
            Belum punya akun?
            <a href="register.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <line x1="20" y1="8" x2="20" y2="14"/>
                    <line x1="23" y1="11" x2="17" y2="11"/>
                </svg>
                Daftar Akun Baru
            </a>
        </p>
    </div>

    <p class="footer">&copy; <?= date('Y'); ?> Perpustakaan Digital &mdash; Panel Admin</p>

    <script>
        // Toggle tampilkan / sembunyikan password
        function togglePassword() {
            const pwd      = document.getElementById('password');
            const eyeOpen  = document.getElementById('eyeOpen');
            const eyeOff   = document.getElementById('eyeOff');

            if (pwd.type === 'password') {
                pwd.type = 'text';
                eyeOpen.style.display = 'none';
                eyeOff.style.display  = 'block';
            } else {
                pwd.type = 'password';
                eyeOpen.style.display = 'block';
                eyeOff.style.display  = 'none';
            }
        }

        // Status loading saat tombol ditekan
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('btnLogin');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> MEMPROSES...';
        });
    </script>
</body>
</html>