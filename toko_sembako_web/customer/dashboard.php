<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Get user transactions
$user_id = $_SESSION['user_id'];
$query_trans = "SELECT t.*, COUNT(dt.id_detail) as total_item
                FROM transaksi_penjualan t
                LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
                WHERE t.id_user = $user_id
                GROUP BY t.id_transaksi
                ORDER BY t.tanggal DESC, t.waktu DESC
                LIMIT 10";
$result_trans = mysqli_query($conn, $query_trans);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Toko Sembako</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-section {
            padding: 3rem 5%;
            min-height: 70vh;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-brown), var(--dark-brown));
            color: var(--white);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .stat-card h3 {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            color: var(--primary-brown);
            font-weight: bold;
        }

        .transaction-table {
            background: var(--white);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: var(--light-brown);
            color: var(--text-dark);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        tr:hover {
            background: var(--bg-light);
        }

        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-success {
            background: var(--light-brown);
            color: var(--primary-brown);
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <nav class="navbar">
            <a href="../index.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <span>Toko Sembako</span>
            </a>

            <ul class="nav-menu">
                <li><a href="../index.php">Beranda</a></li>
                <li><a href="../products.php">Produk</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php" class="btn-primary">Logout</a></li>
            </ul>

            <div class="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <!-- DASHBOARD SECTION -->
    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-header fade-in">
                <h1><i class="fas fa-user-circle"></i> Selamat Datang, <?= $_SESSION['nama_lengkap'] ?>!</h1>
                <p>Kelola transaksi dan riwayat pembelian Anda di sini</p>
            </div>

            <div class="stats-grid fade-in-up">
                <div class="stat-card">
                    <h3><i class="fas fa-shopping-cart"></i> Total Transaksi</h3>
                    <div class="stat-value"><?= mysqli_num_rows($result_trans) ?></div>
                </div>

                <div class="stat-card">
                    <h3><i class="fas fa-box"></i> Produk Dibeli</h3>
                    <div class="stat-value">
                        <?php 
                        $total_items = 0;
                        mysqli_data_seek($result_trans, 0);
                        while($t = mysqli_fetch_assoc($result_trans)) {
                            $total_items += $t['total_item'];
                        }
                        echo $total_items;
                        ?>
                    </div>
                </div>

                <div class="stat-card">
                    <h3><i class="fas fa-wallet"></i> Total Belanja</h3>
                    <div class="stat-value">
                        <?php 
                        mysqli_data_seek($result_trans, 0);
                        $total_belanja = 0;
                        while($t = mysqli_fetch_assoc($result_trans)) {
                            $total_belanja += $t['total_bayar'];
                        }
                        echo 'Rp ' . number_format($total_belanja, 0, ',', '.');
                        ?>
                    </div>
                </div>
            </div>

            <div class="transaction-table fade-in-up">
                <h2 style="margin-bottom: 1.5rem;">
                    <i class="fas fa-history"></i> Riwayat Transaksi
                </h2>

                <?php if (mysqli_num_rows($result_trans) > 0): ?>
                    <?php mysqli_data_seek($result_trans, 0); ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Kode Transaksi</th>
                                <th>Tanggal</th>
                                <th>Total Item</th>
                                <th>Total Bayar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($trans = mysqli_fetch_assoc($result_trans)): ?>
                            <tr>
                                <td><?= $trans['kode_transaksi'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($trans['tanggal'] . ' ' . $trans['waktu'])) ?></td>
                                <td><?= $trans['total_item'] ?> item</td>
                                <td><?= formatRupiah($trans['total_bayar']) ?></td>
                                <td><span class="badge badge-success"><?= ucfirst($trans['status']) ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-receipt" style="font-size: 4rem; color: var(--text-gray); margin-bottom: 1rem;"></i>
                        <p style="color: var(--text-gray);">Belum ada transaksi</p>
                        <a href="../products.php" class="btn-secondary" style="margin-top: 1rem; display: inline-block;">
                            <i class="fas fa-shopping-cart"></i> Mulai Belanja
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-bottom">
            <p>&copy; 2026 Toko Sembako. All rights reserved.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
