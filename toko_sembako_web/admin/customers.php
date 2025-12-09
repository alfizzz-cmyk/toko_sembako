<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// SUBQUERY: Member dengan total belanja dan ranking
$query_member = "
    SELECT 
        p.nama_pelanggan,
        p.telepon,
        p.tipe,
        IFNULL(SUM(t.total_bayar), 0) AS total_belanja,
        COUNT(t.id_transaksi) AS total_transaksi,
        (SELECT COUNT(*) + 1
         FROM pelanggan p2
         LEFT JOIN transaksi_penjualan t2 ON p2.id_pelanggan = t2.id_pelanggan AND t2.status = 'selesai'
         WHERE p2.tipe = 'member' 
         AND IFNULL(SUM(t2.total_bayar), 0) > IFNULL(SUM(t.total_bayar), 0)
        ) AS ranking
    FROM pelanggan p
    LEFT JOIN transaksi_penjualan t ON p.id_pelanggan = t.id_pelanggan AND t.status = 'selesai'
    WHERE p.tipe = 'member' AND p.status = 'aktif'
    GROUP BY p.id_pelanggan
    ORDER BY total_belanja DESC
    LIMIT 20
";
$result_member = mysqli_query($conn, $query_member);

// Total pelanggan
$total_pelanggan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggan WHERE status='aktif'"))['total'];
$total_member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggan WHERE tipe='member' AND status='aktif'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelanggan - Admin</title>
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
        .sidebar-menu li a:hover, .sidebar-menu li a.active {
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
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-box {
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        .stat-box h3 {
            font-size: 2.5rem;
            margin: 0;
        }
        .stat-box p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
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
        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            font-weight: 700;
            font-size: 1rem;
        }
        .rank-1 { background: #fbbf24; color: #78350f; }
        .rank-2 { background: #cbd5e1; color: #1e293b; }
        .rank-3 { background: #fdba74; color: #7c2d12; }
        .rank-other { background: #e5e7eb; color: #6b7280; }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="dashboard.php" class="logo">
                <div class="logo-icon"><i class="fas fa-shopping-basket"></i></div>
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
                <li><a href="customers.php" class="active"><i class="fas fa-users"></i> Pelanggan</a></li>
                <li><a href="stock.php"><i class="fas fa-warehouse"></i> Stok</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="users.php"><i class="fas fa-user-shield"></i> Users</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;">
                <i class="fas fa-users"></i> Pelanggan Member
            </h1>

            <div class="stats-row">
                <div class="stat-box">
                    <h3><?= $total_pelanggan ?></h3>
                    <p>Total Pelanggan</p>
                </div>
                <div class="stat-box">
                    <h3><?= $total_member ?></h3>
                    <p>Member Aktif</p>
                </div>
            </div>

            <!-- RANKING MEMBER -->
            <div class="card fade-in">
                <h2 style="margin-bottom: 1rem;">
                    <i class="fas fa-trophy"></i> Ranking Member Berdasarkan Total Belanja
                </h2>
                
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align:center;width:80px;">Peringkat</th>
                                <th>Nama Pelanggan</th>
                                <th>Telepon</th>
                                <th style="text-align:right;">Total Transaksi</th>
                                <th style="text-align:right;">Total Belanja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_member && mysqli_num_rows($result_member) > 0): ?>
                                <?php while($member = mysqli_fetch_assoc($result_member)): ?>
                                <tr>
                                    <td style="text-align:center;">
                                        <?php
                                        $rank = $member['ranking'];
                                        $rank_class = $rank == 1 ? 'rank-1' : ($rank == 2 ? 'rank-2' : ($rank == 3 ? 'rank-3' : 'rank-other'));
                                        ?>
                                        <div class="rank-badge <?= $rank_class ?>">
                                            <?php if ($rank <= 3): ?>
                                                <i class="fas fa-crown"></i>
                                            <?php else: ?>
                                                <?= $rank ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($member['nama_pelanggan']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($member['telepon']) ?></td>
                                    <td style="text-align:right;"><?= $member['total_transaksi'] ?>x</td>
                                    <td style="text-align:right;font-weight:600;color:var(--primary-green);">
                                        <?= formatRupiah($member['total_belanja']) ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;padding:2rem;color:var(--text-gray);">
                                        Belum ada data member
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
