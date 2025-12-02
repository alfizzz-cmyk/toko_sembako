<?php
require_once 'config.php';

$error = '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'customer/dashboard.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username' AND status = 'aktif'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Check password (MD5)
        if (md5($password) == $user['password']) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['level'] = $user['level'];

            // Redirect based on level
            if ($user['level'] == 'admin' || $user['level'] == 'owner') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: ' . $redirect);
            }
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Sembako</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .auth-section {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 5%;
            background: linear-gradient(135deg, var(--light-green), var(--white));
        }

        .auth-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .auth-image {
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
            padding: 3rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--white);
            text-align: center;
        }

        .auth-image i {
            font-size: 5rem;
            margin-bottom: 2rem;
            animation: bounce 2s infinite;
        }

        .auth-form {
            padding: 3rem;
        }

        .auth-form h2 {
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }

        .auth-form p {
            color: var(--text-gray);
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px var(--light-green);
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            animation: shake 0.5s;
        }

        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        @media (max-width: 768px) {
            .auth-container {
                grid-template-columns: 1fr;
            }

            .auth-image {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <span>Toko Sembako</span>
            </a>

            <ul class="nav-menu">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="products.php">Produk</a></li>
                <li><a href="register.php" class="btn-primary">Daftar</a></li>
            </ul>

            <div class="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <!-- AUTH SECTION -->
    <section class="auth-section">
        <div class="auth-container fade-in">
            <div class="auth-image slide-in-left">
                <i class="fas fa-sign-in-alt"></i>
                <h2>Selamat Datang Kembali!</h2>
                <p>Login untuk melanjutkan belanja sembako favorit Anda</p>
            </div>

            <div class="auth-form slide-in-right">
                <h2>Login</h2>
                <p>Masukkan username dan password Anda</p>

                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input type="text" id="username" name="username" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn-secondary" style="width: 100%; padding: 1rem; font-size: 1.1rem; margin-bottom: 1rem;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>

                    <p class="text-center" style="color: var(--text-gray);">
                        Belum punya akun? <a href="register.php" style="color: var(--primary-green); font-weight: 600;">Daftar Sekarang</a>
                    </p>
                </form>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-bottom">
            <p>&copy; 2025 Toko Sembako. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
