<?php
require_once '../config.php';
if (!isLoggedIn() || !isAdmin()) {header('Location: ../login.php');exit;}
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$query = "SELECT t.*, u.nama_lengkap FROM transaksi_penjualan t JOIN users u ON t.id_user = u.id_user WHERE 1=1";
if ($search) $query .= " AND (t.kode_transaksi LIKE '%$search%' OR u.nama_lengkap LIKE '%$search%')";
$query .= " ORDER BY t.tanggal DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi - Admin</title>
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
        .badge{padding:.3rem .8rem;border-radius:15px;font-size:.85rem;font-weight:600}
        .badge-success{background:#d1fae5;color:#10b981}
        .badge-warning{background:#fef3c7;color:#f59e0b}
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
                <li><a href="transactions.php" class="active"><i class="fas fa-receipt"></i> Transaksi</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Pelanggan</a></li>
                <li><a href="stock.php"><i class="fas fa-warehouse"></i> Stok</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="users.php"><i class="fas fa-user-shield"></i> Users</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <h1 style="margin-bottom:2rem"><i class="fas fa-receipt"></i> Data Transaksi</h1>
            <div class="card">
                <form method="GET" style="margin-bottom:1.5rem;display:flex;gap:1rem">
                    <input type="text" name="search" placeholder="Cari transaksi..." value="<?=$search?>" style="flex:1;padding:.8rem;border:2px solid #e5e7eb;border-radius:10px">
                    <button type="submit" class="btn-secondary"><i class="fas fa-search"></i> Cari</button>
                </form>
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr><th>Kode</th><th>Tanggal</th><th>Pelanggan</th><th>Total Item</th><th>Total Bayar</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php while($t=mysqli_fetch_assoc($result_)):?>
                            <tr>
                                <td><strong><?=$t['kode_transaksi']?></strong></td>
                                <td><?=date('d/m/Y H:i',strtotime($t['tanggal']))?></td>
                                <td><?=$t['nama_lengkap']?></td>
                                <td><?=$t['total_item']?> item</td>
                                <td><strong><?=formatRupiah($t['total_bayar'])?></strong></td>
                                <td><span class="badge <?=$t['status']=='selesai'?'badge-success':'badge-warning'?>"><?=ucfirst($t['status'])?></span></td>
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