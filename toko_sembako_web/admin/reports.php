<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get filter
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Laporan Penjualan
$query_penjualan = "SELECT 
                        DATE(tanggal) as tgl,
                        COUNT(*) as total_transaksi,
                        SUM(total_bayar) as total_pendapatan,
                        SUM(total_item) as total_item_terjual
                    FROM transaksi_penjualan
                    WHERE MONTH(tanggal) = $filter_bulan 
                    AND YEAR(tanggal) = $filter_tahun
                    AND status = 'selesai'
                    GROUP BY DATE(tanggal)
                    ORDER BY tanggal DESC";
$result_penjualan = mysqli_query($conn, $query_penjualan);

// Summary bulan ini
$query_summary = "SELECT 
                    COUNT(*) as total_transaksi,
                    SUM(total_bayar) as total_pendapatan,
                    AVG(total_bayar) as rata_rata_transaksi
                 FROM transaksi_penjualan
                 WHERE MONTH(tanggal) = $filter_bulan
                 AND YEAR(tanggal) = $filter_tahun
                 AND status = 'selesai'";
$summary = mysqli_fetch_assoc(mysqli_query($conn, $query_summary));

// Produk Terlaris
$query_terlaris = "SELECT 
                    p.nama_produk,
                    p.merk,
                    k.nama_kategori,
                    COUNT(dt.id_detail) as total_terjual,
                    SUM(dt.jumlah) as total_qty,
                    SUM(dt.subtotal) as total_pendapatan
                FROM detail_transaksi dt
                JOIN produk p ON dt.id_produk = p.id_produk
                JOIN kategori_barang k ON p.id_kategori = k.id_kategori
                JOIN transaksi_penjualan t ON dt.id_transaksi = t.id_transaksi
                WHERE MONTH(t.tanggal) = $filter_bulan
                AND YEAR(t.tanggal) = $filter_tahun
                AND t.status = 'selesai'
                GROUP BY dt.id_produk
                ORDER BY total_qty DESC
                LIMIT 10";
$result_terlaris = mysqli_query($conn, $query_terlaris);

// Laporan per Kategori
$query_kategori = "SELECT 
                    k.nama_kategori,
                    COUNT(DISTINCT dt.id_transaksi) as total_transaksi,
                    SUM(dt.jumlah) as total_qty,
                    SUM(dt.subtotal) as total_pendapatan
                FROM detail_transaksi dt
                JOIN produk p ON dt.id_produk = p.id_produk
                JOIN kategori_barang k ON p.id_kategori = k.id_kategori
                JOIN transaksi_penjualan t ON dt.id_transaksi = t.id_transaksi
                WHERE MONTH(t.tanggal) = $filter_bulan
                AND YEAR(t.tanggal) = $filter_tahun
                AND t.status = 'selesai'
                GROUP BY k.id_kategori
                ORDER BY total_pendapatan DESC";
$result_kategori = mysqli_query($conn, $query_kategori);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout { display: flex; min-height: calc(100vh - 70px); }
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--primary-green), var(--dark-green));
            padding: 2rem 0;
            color: var(--white);
        }
        .sidebar-menu { list-style: none; }
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
        .main-content { flex: 1; padding: 2rem; background: var(--bg-light); }
        .card {
            background: var(--white);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
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
            font-size: 1.8rem;
            color: var(--text-dark);
            font-weight: bold;
        }
        table { width: 100%; border-collapse: collapse; }
        th {
            background: var(--light-green);
            color: var(--text-dark);
            padding: 0.8rem;
            text-align: left;
            font-weight: 600;
        }
        td { padding: 0.8rem; border-bottom: 1px solid #e5e7eb; }
        tr:hover { background: var(--bg-light); }
        .filter-form {
            display: flex;
            gap: 1rem;
            align-items: end;
            margin-bottom: 2rem;
        }
        .filter-form select {
            padding: 0.8rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
        }
    </style>
</head>
<body>
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
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Produk</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="transactions.php"><i class="fas fa-receipt"></i> Transaksi</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Pelanggan</a></li>
                <li><a href="stock.php"><i class="fas fa-warehouse"></i> Stok</a></li>
                <li><a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="users.php"><i class="fas fa-user-shield"></i> Users</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;"><i class="fas fa-chart-bar"></i> Laporan Penjualan</h1>

            <!-- FILTER -->
            <div class="card fade-in">
                <form method="GET" class="filter-form">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Bulan</label>
                        <select name="bulan">
                            <?php 
                            $bulan_nama = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                            for($i=1; $i<=12; $i++): 
                            ?>
                            <option value="<?= sprintf('%02d', $i) ?>" <?= $filter_bulan == sprintf('%02d', $i) ? 'selected' : '' ?>>
                                <?= $bulan_nama[$i-1] ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Tahun</label>
                        <select name="tahun">
                            <?php for($y=2024; $y<=2026; $y++): ?>
                            <option value="<?= $y ?>" <?= $filter_tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-secondary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </form>
            </div>

            <!-- SUMMARY -->
            <div class="stats-grid fade-in-up">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--light-green); color: var(--primary-green);">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h3>Total Transaksi</h3>
                    <div class="stat-value"><?= $summary['total_transaksi'] ?? 0 ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #e0e7ff; color: #6366f1;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3>Total Pendapatan</h3>
                    <div class="stat-value" style="font-size: 1.3rem;"><?= formatRupiah($summary['total_pendapatan'] ?? 0) ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #dbeafe; color: #3b82f6;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Rata-rata Transaksi</h3>
                    <div class="stat-value" style="font-size: 1.2rem;"><?= formatRupiah($summary['rata_rata_transaksi'] ?? 0) ?></div>
                </div>
            </div>

            <!-- LAPORAN HARIAN -->
            <div class="card fade-in-up">
                <h2 style="margin-bottom: 1.5rem;">
                    <i class="fas fa-calendar-day"></i> Laporan Harian - 
                    <?= $bulan_nama[$filter_bulan-1] ?> <?= $filter_tahun ?>
                </h2>

                <?php if (mysqli_num_rows($result_penjualan) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Total Transaksi</th>
                                <th>Total Item Terjual</th>
                                <th>Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($lap = mysqli_fetch_assoc($result_penjualan)): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($lap['tgl'])) ?></td>
                                <td><?= $lap['total_transaksi'] ?> transaksi</td>
                                <td><?= $lap['total_item_terjual'] ?> item</td>
                                <td><strong><?= formatRupiah($lap['total_pendapatan']) ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="text-align: center; padding: 2rem; color: var(--text-gray);">Belum ada transaksi di bulan ini</p>
                <?php endif; ?>
            </div>

            <!-- PRODUK TERLARIS -->
            <div class="card fade-in-up">
                <h2 style="margin-bottom: 1.5rem;"><i class="fas fa-trophy"></i> Top 10 Produk Terlaris</h2>

                <?php if (mysqli_num_rows($result_terlaris) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th>Merk</th>
                                <th>Kategori</th>
                                <th>Total Terjual</th>
                                <th>Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while($prod = mysqli_fetch_assoc($result_terlaris)): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $prod['nama_produk'] ?></td>
                                <td><?= $prod['merk'] ?></td>
                                <td><?= $prod['nama_kategori'] ?></td>
                                <td><strong><?= $prod['total_qty'] ?> unit</strong></td>
                                <td><?= formatRupiah($prod['total_pendapatan']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="text-align: center; padding: 2rem; color: var(--text-gray);">Belum ada data penjualan produk</p>
                <?php endif; ?>
            </div>

            <!-- LAPORAN PER KATEGORI -->
            <div class="card fade-in-up">
                <h2 style="margin-bottom: 1.5rem;"><i class="fas fa-tags"></i> Penjualan per Kategori</h2>

                <?php if (mysqli_num_rows($result_kategori) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Total Transaksi</th>
                                <th>Total Qty Terjual</th>
                                <th>Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($kat = mysqli_fetch_assoc($result_kategori)): ?>
                            <tr>
                                <td><strong><?= $kat['nama_kategori'] ?></strong></td>
                                <td><?= $kat['total_transaksi'] ?> transaksi</td>
                                <td><?= $kat['total_qty'] ?> unit</td>
                                <td><strong><?= formatRupiah($kat['total_pendapatan']) ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="text-align: center; padding: 2rem; color: var(--text-gray);">Belum ada data penjualan kategori</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>