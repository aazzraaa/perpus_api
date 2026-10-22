<?php
require_once 'koneksi.php';
require_once 'auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, username, password 
             FROM admins 
             WHERE username = ? 
             LIMIT 1'
        );

        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header('Location: dashboard.php');
            exit;
        }

        $error = 'Username atau password salah.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Admin - Perpus API</title>

    <link rel="stylesheet" href="style.css">

    <style>
        /* =====================================================
           LOGIN PAGE
        ===================================================== */

        * {
            box-sizing: border-box;
        }

        body.login-page {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: #fff7f7;
            color: #3d1018;
            overflow-x: hidden;
        }

        /* =====================================================
           CONTAINER
        ===================================================== */

        .login-wrapper {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #ffffff;
        }

        /* =====================================================
           LEFT SIDE
        ===================================================== */

        .login-visual {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
            background:
                radial-gradient(
                    circle at 15% 20%,
                    rgba(255,255,255,.18) 0,
                    rgba(255,255,255,0) 30%
                ),
                linear-gradient(
                    145deg,
                    #5b0f1b 0%,
                    #741526 45%,
                    #8f1d32 100%
                );
        }

        .visual-decoration {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,.07);
            pointer-events: none;
        }

        .visual-decoration.one {
            width: 420px;
            height: 420px;
            top: -170px;
            left: -150px;
        }

        .visual-decoration.two {
            width: 500px;
            height: 500px;
            right: -240px;
            bottom: -220px;
        }

        .visual-decoration.three {
            width: 180px;
            height: 180px;
            right: 70px;
            top: 90px;
            border: 1px solid rgba(255,255,255,.13);
            background: transparent;
        }

        .visual-content {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 560px;
            color: white;
        }

        .brand-mini {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 34px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .brand-mini-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.2);
        }

        .brand-mini-icon svg {
            width: 22px;
            height: 22px;
        }

        .visual-content h2 {
            margin: 0 0 18px;
            font-size: clamp(32px, 4vw, 52px);
            line-height: 1.12;
            font-weight: 800;
            letter-spacing: -1.5px;
        }

        .visual-content h2 span {
            color: #ffd1d9;
        }

        .visual-content > p {
            max-width: 470px;
            margin: 0;
            color: rgba(255,255,255,.78);
            font-size: 16px;
            line-height: 1.8;
        }

        /* =====================================================
           ILLUSTRATION
        ===================================================== */

        .book-scene {
            position: relative;
            height: 300px;
            margin-top: 48px;
        }

        .book {
            position: absolute;
            border-radius: 8px;
            box-shadow: 0 18px 35px rgba(0,0,0,.18);
        }

        .book-back {
            width: 210px;
            height: 270px;
            left: 50%;
            top: 5px;
            transform: translateX(-50%) rotate(-8deg);
            background: #f8d9df;
        }

        .book-middle {
            width: 220px;
            height: 275px;
            left: 50%;
            top: 0;
            transform: translateX(-50%) rotate(5deg);
            background: #fff0f2;
        }

        .book-front {
            width: 210px;
            height: 270px;
            left: 50%;
            top: 10px;
            transform: translateX(-50%);
            padding: 28px 22px;
            background: #ffffff;
            color: #641221;
        }

        .book-front::before {
            content: "";
            position: absolute;
            left: 18px;
            top: 20px;
            bottom: 20px;
            width: 4px;
            border-radius: 4px;
            background: #8f1d32;
        }

        .book-front .book-icon {
            width: 58px;
            height: 58px;
            margin: 12px auto 22px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9e1e5;
            color: #7c1729;
        }

        .book-front .book-icon svg {
            width: 30px;
            height: 30px;
        }

        .book-front h3 {
            margin: 0;
            text-align: center;
            font-size: 21px;
            font-weight: 800;
        }

        .book-front p {
            margin: 9px 0 0;
            text-align: center;
            font-size: 11px;
            color: #94616a;
        }

        .floating-card {
            position: absolute;
            z-index: 5;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 14px;
            background: rgba(255,255,255,.95);
            color: #631321;
            box-shadow: 0 15px 30px rgba(35,0,8,.18);
            font-size: 12px;
            font-weight: 700;
        }

        .floating-card svg {
            width: 19px;
            height: 19px;
            color: #8f1d32;
        }

        .floating-card.card-one {
            left: 8%;
            top: 55px;
        }

        .floating-card.card-two {
            right: 5%;
            bottom: 40px;
        }

        .visual-footer {
            position: absolute;
            left: 60px;
            bottom: 35px;
            color: rgba(255,255,255,.55);
            font-size: 12px;
        }

        /* =====================================================
           RIGHT SIDE / LOGIN
        ===================================================== */

        .login-side {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px;
            background: #ffffff;
        }

        .login-card {
            width: 100%;
            max-width: 430px;
            padding: 10px;
            background: transparent;
            border: none;
            box-shadow: none;
        }

        .login-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 42px;
            color: #691323;
            font-size: 15px;
            font-weight: 800;
        }

        .login-brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            background: #7b1729;
            box-shadow: 0 8px 18px rgba(123,23,41,.22);
        }

        .login-brand-icon svg {
            width: 22px;
            height: 22px;
        }

        .login-title {
            margin: 0 0 8px;
            color: #431019;
            font-size: 31px;
            line-height: 1.2;
            font-weight: 800;
        }

        .login-subtitle {
            margin: 0 0 32px;
            color: #92727a;
            font-size: 14px;
            line-height: 1.6;
        }

        /* =====================================================
           ERROR
        ===================================================== */

        .login-error {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 22px;
            padding: 13px 15px;
            border: 1px solid #efc6cd;
            border-radius: 10px;
            background: #fff2f4;
            color: #9d263c;
            font-size: 13px;
            line-height: 1.5;
        }

        .login-error svg {
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            margin-top: 1px;
        }

        /* =====================================================
           FORM
        ===================================================== */

        .login-form-group {
            margin-bottom: 20px;
        }

        .login-form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4b1a22;
            font-size: 13px;
            font-weight: 700;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper svg {
            position: absolute;
            left: 15px;
            top: 50%;
            width: 18px;
            height: 18px;
            transform: translateY(-50%);
            color: #a77c84;
            pointer-events: none;
        }

        .login-input {
            width: 100%;
            height: 50px;
            padding: 0 15px 0 46px;
            border: 1px solid #ead8dc;
            border-radius: 10px;
            outline: none;
            background: #fff;
            color: #3e1119;
            font-family: inherit;
            font-size: 14px;
            transition: .2s ease;
        }

        .login-input::placeholder {
            color: #b9a1a6;
        }

        .login-input:focus {
            border-color: #8f1d32;
            box-shadow: 0 0 0 4px rgba(143,29,50,.09);
        }

        .password-toggle {
            position: absolute;
            right: 13px;
            top: 50%;
            width: 30px;
            height: 30px;
            padding: 0;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #a77c84;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: #7b1729;
        }

        .password-toggle svg {
            position: static;
            width: 18px;
            height: 18px;
            transform: none;
        }

        /* =====================================================
           REMEMBER / INFO
        ===================================================== */

        .login-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 4px 0 24px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #87666e;
            font-size: 12px;
        }

        .remember input {
            width: 15px;
            height: 15px;
            accent-color: #7b1729;
        }

        /* =====================================================
           BUTTON
        ===================================================== */

        .login-button {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #671321, #8f1d32);
            color: #ffffff;
            font-family: inherit;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: .3px;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(111,19,37,.22);
            transition: .2s ease;
        }

        .login-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 13px 25px rgba(111,19,37,.28);
            background: linear-gradient(135deg, #5a101d, #80182c);
        }

        .login-button:active {
            transform: translateY(0);
        }

        /* =====================================================
           ACCOUNT HINT
        ===================================================== */

        .login-hint {
            margin-top: 25px;
            padding: 15px 17px;
            border-radius: 11px;
            border: 1px solid #ead8dc;
            background: #fff9fa;
            color: #8c6971;
            font-size: 12px;
            line-height: 1.7;
        }

        .login-hint-title {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 6px;
            color: #681422;
            font-weight: 800;
        }

        .login-hint-title svg {
            width: 16px;
            height: 16px;
        }

        .login-hint strong {
            color: #641321;
        }

        /* =====================================================
           COPYRIGHT
        ===================================================== */

        .login-copyright {
            margin-top: 28px;
            text-align: center;
            color: #b49da2;
            font-size: 11px;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 900px) {

            .login-wrapper {
                grid-template-columns: 1fr;
            }

            .login-visual {
                min-height: 390px;
                padding: 40px 30px;
            }

            .visual-content {
                max-width: 600px;
            }

            .visual-content h2 {
                font-size: 35px;
            }

            .visual-content > p {
                font-size: 14px;
            }

            .book-scene {
                display: none;
            }

            .visual-footer {
                display: none;
            }

            .login-side {
                min-height: auto;
                padding: 45px 25px 55px;
            }

            .login-card {
                max-width: 500px;
            }
        }

        @media (max-width: 500px) {

            .login-visual {
                min-height: 330px;
                padding: 30px 22px;
            }

            .brand-mini {
                margin-bottom: 24px;
            }

            .visual-content h2 {
                font-size: 29px;
            }

            .visual-content > p {
                font-size: 13px;
            }

            .login-side {
                padding: 35px 20px 45px;
            }

            .login-title {
                font-size: 27px;
            }

            .login-brand {
                margin-bottom: 32px;
            }
        }
    </style>
</head>

<body class="login-page">

<div class="login-wrapper">

    <!-- =====================================================
         BAGIAN KIRI
    ====================================================== -->

    <section class="login-visual">

        <div class="visual-decoration one"></div>
        <div class="visual-decoration two"></div>
        <div class="visual-decoration three"></div>

        <div class="visual-content">

            <div class="brand-mini">

                <div class="brand-mini-icon">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         stroke-linecap="round"
                         stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                        <path d="M8 6h8"/>
                        <path d="M8 10h8"/>
                    </svg>
                </div>

                <span>Perpus API</span>

            </div>

            <h2>
                Kelola perpustakaan
                <span>lebih mudah.</span>
            </h2>

            <p>
                Sistem informasi perpustakaan untuk membantu admin
                mengelola data buku secara cepat, terstruktur, dan
                terintegrasi melalui API.
            </p>

            <div class="book-scene">

                <div class="book book-back"></div>

                <div class="book book-middle"></div>

                <div class="book book-front">

                    <div class="book-icon">
                        <svg viewBox="0 0 24 24" fill="none"
                             stroke="currentColor"
                             stroke-width="1.7"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                            <path d="M8 6h8"/>
                            <path d="M8 10h6"/>
                        </svg>
                    </div>

                    <h3>PERPUSTAKAAN</h3>

                    <p>
                        DATA BUKU & INFORMASI
                    </p>

                </div>

                <div class="floating-card card-one">

                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round">
                        <path d="M20 7h-9"/>
                        <path d="M14 17H4"/>
                        <path d="M17 17l3-3-3-3"/>
                        <path d="M7 7L4 10l3 3"/>
                    </svg>

                    <span>Data terorganisir</span>

                </div>

                <div class="floating-card card-two">

                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round">
                        <path d="M12 3v18"/>
                        <path d="M3 12h18"/>
                    </svg>

                    <span>Kelola data buku</span>

                </div>

            </div>

        </div>

        <div class="visual-footer">
            © <?= date('Y') ?> Perpus API · Admin Panel
        </div>

    </section>


    <!-- =====================================================
         BAGIAN KANAN
    ====================================================== -->

    <section class="login-side">

        <div class="login-card">

            <div class="login-brand">

                <div class="login-brand-icon">

                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         stroke-linecap="round"
                         stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                        <path d="M8 6h8"/>
                    </svg>

                </div>

                <span>PERPUS API</span>

            </div>


            <h1 class="login-title">
                Selamat datang kembali!
            </h1>

            <p class="login-subtitle">
                Silakan masuk menggunakan akun admin untuk
                mengelola data perpustakaan.
            </p>


            <?php if ($error): ?>

                <div class="login-error">

                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>

                    <span><?= e($error) ?></span>

                </div>

            <?php endif; ?>


            <form method="post" autocomplete="off">

                <!-- USERNAME -->

                <div class="login-form-group">

                    <label for="username">
                        Username
                    </label>

                    <div class="input-wrapper">

                        <svg viewBox="0 0 24 24" fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="login-input"
                            placeholder="Masukkan username"
                            required
                            autofocus
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="login-form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-wrapper">

                        <svg viewBox="0 0 24 24" fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="10" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="login-input"
                            placeholder="Masukkan password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            id="togglePassword"
                            aria-label="Tampilkan password"
                        >

                            <svg id="eyeIcon"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">

                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>

                            </svg>

                        </button>

                    </div>

                </div>


                <!-- INFO -->

                <div class="login-info">

                    <label class="remember">

                        <input
                            type="checkbox"
                            name="remember"
                        >

                        <span>Ingat perangkat ini</span>

                    </label>

                </div>


                <!-- LOGIN -->

                <button
                    type="submit"
                    class="login-button"
                >
                    MASUK KE DASHBOARD
                </button>

            </form>


            <!-- ACCOUNT INFO -->

            <div class="login-hint">

                <div class="login-hint-title">

                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>

                    <span>Akun praktik</span>

                </div>

                Username:
                <strong>admin</strong>
                <br>

                Password:
                <strong>admin123</strong>

            </div>


            <div class="login-copyright">
                Sistem Informasi Perpustakaan · Perpus API
            </div>

        </div>

    </section>

</div>


<script>
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', function () {

        if (passwordInput.type === 'password') {

            passwordInput.type = 'text';

            togglePassword.setAttribute(
                'aria-label',
                'Sembunyikan password'
            );

            eyeIcon.innerHTML = `
                <path d="M3 3l18 18"></path>
                <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                <path d="M9.9 4.2A9.7 9.7 0 0 1 12 4c6.5 0 10 8 10 8a18 18 0 0 1-3.1 4.4"></path>
                <path d="M6.6 6.6C3.8 8.5 2 12 2 12s3.5 8 10 8a9.9 9.9 0 0 0 4.1-.9"></path>
            `;

        } else {

            passwordInput.type = 'password';

            togglePassword.setAttribute(
                'aria-label',
                'Tampilkan password'
            );

            eyeIcon.innerHTML = `
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                <circle cx="12" cy="12" r="3"/>
            `;

        }

    });
</script>

</body>
</html>