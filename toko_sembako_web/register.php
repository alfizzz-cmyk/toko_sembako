<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Validation
    if ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } else {
        // Check if username exists
        $check = "SELECT * FROM users WHERE username = '$username'";
        $result_check = mysqli_query($conn, $check);

        if (mysqli_num_rows($result_check) > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            // Generate kode pelanggan
            $kode_pelanggan = 'PEL' . sprintf('%03d', rand(100, 999));

            // Insert user
            $password_hash = md5($password);
            $query_user = "INSERT INTO users (username, password, nama_lengkap, level, no_hp, email, status) 
                          VALUES ('$username', '$password_hash', '$nama_lengkap', 'kasir', '$no_hp', '$email', 'aktif')";

            if (mysqli_query($conn, $query_user)) {
                $id_user = mysqli_insert_id($conn);

                // Insert pelanggan
                $query_pelanggan = "INSERT INTO pelanggan (kode_pelanggan, nama_pelanggan, telepon, email, tipe, status) 
                                   VALUES ('$kode_pelanggan', '$nama_lengkap', '$no_hp', '$email', 'reguler', 'aktif')";

                if (mysqli_query($conn, $query_pelanggan)) {
                    $success = 'Registrasi berhasil! Silakan login.';
                    // Redirect after 2 seconds
                    header("refresh:2;url=login.php");
                }
            } else {
                $error = 'Registrasi gagal! Coba lagi.';
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
    <title>Daftar - Toko Sembako</title>
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
            max-width: 1000px;
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
            margin-bottom: 1.2rem;
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
            animation: fadeIn 0.5s;
        }

        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: var(--light-green);
            color: var(--dark-green);
            border: 1px solid var(--primary-green);
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
                <li><a href="login.php" class="btn-primary">Login</a></li>
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
                <i class="fas fa-user-plus"></i>
                <h2>Bergabung Dengan Kami!</h2>
                <p>Daftar sekarang dan nikmati kemudahan belanja sembako online</p>
                <div style="margin-top: 2rem;">
                    <p><i class="fas fa-check-circle"></i> Harga Terjangkau</p>
                    <p><i class="fas fa-check-circle"></i> Produk Berkualitas</p>
                    <p><i class="fas fa-check-circle"></i> Pengiriman Cepat</p>
                </div>
            </div>

            <div class="auth-form slide-in-right">
                <h2>Daftar</h2>
                <p>Buat akun baru untuk mulai belanja</p>

                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="nama_lengkap">
                            <i class="fas fa-id-card"></i> Nama Lengkap
                        </label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" required>
                    </div>

                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="no_hp">
                            <i class="fas fa-phone"></i> No. HP
                        </label>
                        <input type="tel" id="no_hp" name="no_hp" placeholder="08xxxxxxxxxx" required>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Konfirmasi Password
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn-secondary" style="width: 100%; padding: 1rem; font-size: 1.1rem; margin-bottom: 1rem;">
                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                    </button>

                    <p class="text-center" style="color: var(--text-gray);">
                        Sudah punya akun? <a href="login.php" style="color: var(--primary-green); font-weight: 600;">Login Di Sini</a>
                    </p>
                </form>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-bottom">
            <p>&copy; 2024 Toko Sembako. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
