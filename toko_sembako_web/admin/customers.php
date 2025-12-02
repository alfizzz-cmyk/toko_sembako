<?php
require_once '../config.php';
if (!isLoggedIn() || !isAdmin()) {header('Location: ../login.php');exit;}
$query = "SELECT u.*, COUNT(t.id_transaksi) as total_transaksi, COALESCE(SUM(t.total_bayar), 0) as total_belanja 
          FROM users u 
          LEFT JOIN transaksi_penjualan t ON u.id_user = t.id_user AND t.status='selesai'
          WHERE u.role = 'customer' 
          GROUP BY u.id_user 
          ORDER BY total_belanja DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pelanggan - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout{display:flex;min-height:calc(100vh - 70px)}
        .sidebar{width:250px;background:linear-gradient(180deg,var(--primary-green),var(--dark-green));padding:2rem 0;color:var(--white)}
        .sidebar-menu{list-style:none}
        .sidebar-menu li a{display:block;padding:1rem 2rem;color:var(--white);text-decoration:none;border-left:3px solid transparent}
        .sidebar-menu li a:hover,.sidebar-menu li a.active{background:rgba(255,255,255,.1);border-left-color:var(--white)}
        .main-content{flex:1;padding:2rem;background:var(--bg-light)}
        .card{background:var(--white);border-radius:15px;padding:1.5rem;box-shadow:var(--shadow)}
        table{width:100%;border-collapse:collapse}
        th{background:var(--light-green);padding:.8rem;text-align:left;font-weight:600}
        td{padding:.8rem;border-bottom:1px solid #e5e7eb}
        .badge{padding:.3rem .8rem;border-radius:15px;font-size:.85rem;font-weight:600;background:#e0e7ff;color:#6366f1}
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="dashboard.php" class="logo"><div class="logo-icon"><i class="fas fa-shopping-basket"></i></div><span>Admin Panel</span></a>
            <ul class="nav-menu">
                <li><a href="../index.php">Lihat Website</a></li>
                <li><a href="#"><?=$_SESSION['nama_lengkap']?></a></li>
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
            <h1 style="margin-bottom:2rem"><i class="fas fa-users"></i> Data Pelanggan</h1>
            <div class="card">
                <h2 style="margin-bottom:1.5rem">Total Pelanggan: <?=mysqli_num_rows($result)?></h2>
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr><th>Nama</th><th>Email</th><th>No. HP</th><th>Alamat</th><th>Total Transaksi</th><th>Total Belanja</th></tr>
                        </thead>
                        <tbody>
                            <?php while($c=mysqli_fetch_assoc($result)):?>
                            <tr>
                                <td><strong><?=$c['nama_lengkap']?></strong></td>
                                <td><?=$c['email']?></td>
                                <td><?=$c['no_hp']?></td>
                                <td><?=$c['alamat']?:'<em style="color:#999">Belum diisi</em>'?></td>
                                <td><span class="badge"><?=$c['total_transaksi']?> transaksi</span></td>
                                <td><strong><?=formatRupiah($c['total_belanja'])?></strong></td>
                            </tr>
                            <?php endwhile;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>