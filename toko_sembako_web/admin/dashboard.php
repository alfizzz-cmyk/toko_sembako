<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get statistics
$query_total_produk = "SELECT COUNT(*) as total FROM produk WHERE status = 'aktif'";
$total_produk = mysqli_fetch_assoc(mysqli_query($conn, $query_total_produk))['total'];

$query_stok_menipis = "SELECT COUNT(*) as total FROM produk WHERE stok <= stok_minimum AND status = 'aktif'";
$stok_menipis = mysqli_fetch_assoc(mysqli_query($conn, $query_stok_menipis))['total'];

$query_total_transaksi = "SELECT COUNT(*) as total FROM transaksi_penjualan WHERE status = 'selesai'";
$total_transaksi = mysqli_fetch_assoc(mysqli_query($conn, $query_total_transaksi))['total'];

$query_pendapatan = "SELECT SUM(total_bayar) as total FROM transaksi_penjualan WHERE status = 'selesai'";
$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, $query_pendapatan))['total'] ?? 0;

// Get recent transactions
$query_recent = "SELECT t.*, u.nama_lengkap 
                 FROM transaksi_penjualan t
                 JOIN users u ON t.id_user = u.id_user
                 ORDER BY t.tanggal DESC, t.waktu DESC
                 LIMIT 5";
$result_recent = mysqli_query($conn, $query_recent);

// Get low stock products
$query_low_stock = "SELECT * FROM v_stok_menipis LIMIT 5";
$result_low_stock = mysqli_query($conn, $query_low_stock);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Toko Sembako</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout {
            display: flex;
            min-height: calc(100vh - 70px);
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--primary-green), var(--dark-green));
            padding: 2rem 0;
            color: var(--white);
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li a {
            display: block;
            padding: 1rem 2rem;
            color: var(--white);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--white);
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            background: var(--bg-light);
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
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
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-card h3 {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            color: var(--text-dark);
            font-weight: bold;
        }

        .card {
            background: var(--white);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: var(--light-green);
            color: var(--text-dark);
            padding: 0.8rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }

        td {
            padding: 0.8rem;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.9rem;
        }

        tr:hover {
            background: var(--bg-light);
        }

        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success {
            background: var(--light-green);
            color: var(--primary-green);
        }

        .badge-warning {
            background: #fef3c7;
            color: #f59e0b;
        }

        @media (max-width: 768px) {
            .admin-layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <nav class="navbar">
            <a href="dashboard.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <span>Admin Panel</span>
            </a>

            <ul class="nav-menu">
                <li><a href="../index.php">Lihat Website</a></li>
                <li><a href="#"><?= $_SESSION['nama_lengkap'] ?></a></li>
                <li><a href="../customer/logout.php" class="btn-primary">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="admin-layout">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Produk</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="transactions.php"><i class="fas fa-receipt"></i> Transaksi</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Pelanggan</a></li>
                <li><a href="stock.php"><i class="fas fa-warehouse"></i> Stok</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="users.php"><i class="fas fa-user-shield"></i> Users</a></li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <div class="dashboard-header fade-in">
                <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
                <p>Selamat datang, <?= $_SESSION['nama_lengkap'] ?>! Berikut ringkasan sistem Anda.</p>
            </div>

            <!-- STATISTICS -->
            <div class="stats-grid fade-in-up">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--light-green); color: var(--primary-green);">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3>Total Produk</h3>
                    <div class="stat-value"><?= $total_produk ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Stok Menipis</h3>
                    <div class="stat-value"><?= $stok_menipis ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #dbeafe; color: #3b82f6;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Total Transaksi</h3>
                    <div class="stat-value"><?= $total_transaksi ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #e0e7ff; color: #6366f1;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3>Total Pendapatan</h3>
                    <div class="stat-value" style="font-size: 1.3rem;"><?= formatRupiah($total_pendapatan) ?></div>
                </div>
            </div>

            <!-- RECENT TRANSACTIONS -->
            <div class="card fade-in-up">
                <h2 style="margin-bottom: 1.5rem;"><i class="fas fa-history"></i> Transaksi Terbaru</h2>

                <?php if (mysqli_num_rows($result_recent) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode Transaksi</th>
                                <th>Tanggal</th>
                                <th>Kasir</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($trans = mysqli_fetch_assoc($result_recent)): ?>
                            <tr>
                                <td><?= $trans['kode_transaksi'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($trans['tanggal'] . ' ' . $trans['waktu'])) ?></td>
                                <td><?= $trans['nama_lengkap'] ?></td>
                                <td><?= formatRupiah($trans['total_bayar']) ?></td>
                                <td><span class="badge badge-success"><?= ucfirst($trans['status']) ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="text-align: center; padding: 2rem; color: var(--text-gray);">Belum ada transaksi</p>
                <?php endif; ?>
            </div>

            <!-- LOW STOCK ALERT -->
            <div class="card fade-in-up">
                <h2 style="margin-bottom: 1.5rem;"><i class="fas fa-exclamation-circle"></i> Stok Menipis</h2>

                <?php if (mysqli_num_rows($result_low_stock) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Min. Stok</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($product = mysqli_fetch_assoc($result_low_stock)): ?>
                            <tr>
                                <td><?= $product['kode_produk'] ?></td>
                                <td><?= $product['nama_produk'] ?></td>
                                <td><?= $product['nama_kategori'] ?></td>
                                <td><?= $product['stok'] ?> <?= $product['nama_satuan'] ?></td>
                                <td><?= $product['stok_minimum'] ?></td>
                                <td><span class="badge badge-warning">Perlu Restock</span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="text-align: center; padding: 2rem; color: var(--text-gray);">Semua stok aman!</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
