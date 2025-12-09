<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Filter berdasarkan kategori atau harga
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$kategori_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

// Query produk dengan filter
if ($filter == 'mahal') {
    // SUBQUERY: produk dengan harga di atas rata-rata kategorinya
    $query = "
        SELECT p.*, k.nama_kategori, s.nama_satuan
        FROM produk p
        JOIN kategori_barang k ON p.id_kategori = k.id_kategori
        JOIN satuan_barang s ON p.id_satuan = s.id_satuan
        WHERE p.harga_jual > (
            SELECT AVG(p2.harga_jual)
            FROM produk p2
            WHERE p2.id_kategori = p.id_kategori
        )
        AND p.status = 'aktif'
        ORDER BY p.harga_jual DESC
    ";
} elseif ($kategori_filter > 0) {
    $query = "
        SELECT p.*, k.nama_kategori, s.nama_satuan
        FROM produk p
        JOIN kategori_barang k ON p.id_kategori = k.id_kategori
        JOIN satuan_barang s ON p.id_satuan = s.id_satuan
        WHERE p.id_kategori = $kategori_filter
        AND p.status = 'aktif'
        ORDER BY p.nama_produk
    ";
} else {
    $query = "
        SELECT p.*, k.nama_kategori, s.nama_satuan
        FROM produk p
        JOIN kategori_barang k ON p.id_kategori = k.id_kategori
        JOIN satuan_barang s ON p.id_satuan = s.id_satuan
        WHERE p.status = 'aktif'
        ORDER BY p.nama_produk
    ";
}

$result = mysqli_query($conn, $query);
$total_produk = mysqli_num_rows($result);

// Ambil kategori untuk dropdown
$kategori_list = mysqli_query($conn, "SELECT * FROM kategori_barang ORDER BY nama_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin</title>
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
        .filter-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .filter-bar select, .filter-bar a {
            padding: 0.8rem 1.2rem;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            background: white;
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }
        .filter-bar a:hover, .filter-bar a.active {
            background: var(--primary-green);
            color: white;
            border-color: var(--primary-green);
        }
        .filter-bar a.active {
            background: var(--dark-green);
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
        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-premium {
            background: #fef3c7;
            color: #d97706;
        }
        .badge-normal {
            background: #dbeafe;
            color: #1e40af;
        }
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
                <li><a href="products.php" class="active"><i class="fas fa-box"></i> Produk</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="transactions.php"><i class="fas fa-receipt"></i> Transaksi</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Pelanggan</a></li>
                <li><a href="stock.php"><i class="fas fa-warehouse"></i> Stok</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="users.php"><i class="fas fa-user-shield"></i> Users</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;">
                <i class="fas fa-box"></i> Kelola Produk
            </h1>

            <!-- FILTER BAR -->
            <div class="filter-bar">
                <a href="products.php" class="<?= $filter == 'all' ? 'active' : '' ?>">
                    <i class="fas fa-th"></i> Semua Produk (<?= $total_produk ?>)
                </a>
                <a href="products.php?filter=mahal" class="<?= $filter == 'mahal' ? 'active' : '' ?>">
                    <i class="fas fa-crown"></i> Produk Premium
                </a>
                
                <select onchange="if(this.value) window.location='products.php?kategori='+this.value" style="margin-left:auto;">
                    <option value="">Filter Kategori</option>
                    <?php while($kat = mysqli_fetch_assoc($kategori_list)): ?>
                    <option value="<?= $kat['id_kategori'] ?>" <?= $kategori_filter == $kat['id_kategori'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <?php if ($filter == 'mahal'): ?>
            <div class="card" style="background:#fef3c7;border-left:4px solid #d97706;">
                <p style="margin:0;">
                    <i class="fas fa-info-circle"></i>
                    <strong>Filter Produk Premium:</strong> Menampilkan produk dengan harga di atas rata-rata kategorinya.
                </p>
            </div>
            <?php endif; ?>

            <!-- TABEL PRODUK -->
            <div class="card fade-in">
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th style="text-align:right;">Stok</th>
                                <th style="text-align:right;">Harga Beli</th>
                                <th style="text-align:right;">Harga Jual</th>
                                <th style="text-align:center;">Label</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($total_produk > 0): ?>
                                <?php mysqli_data_seek($result, 0); ?>
                                <?php while($prod = mysqli_fetch_assoc($result)): ?>
                                <?php
                                // Cek apakah harga di atas rata-rata kategori (untuk badge)
                                $avg_query = mysqli_query($conn, "SELECT AVG(harga_jual) as avg_price FROM produk WHERE id_kategori = {$prod['id_kategori']}");
                                $avg_data = mysqli_fetch_assoc($avg_query);
                                $is_premium = $prod['harga_jual'] > $avg_data['avg_price'];
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($prod['kode_produk']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($prod['nama_produk']) ?></strong>
                                        <?php if ($prod['merk']): ?>
                                        <br><small style="color:#888;"><?= htmlspecialchars($prod['merk']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($prod['nama_kategori']) ?></td>
                                    <td style="text-align:right;"><?= $prod['stok'] ?> <?= htmlspecialchars($prod['nama_satuan']) ?></td>
                                    <td style="text-align:right;"><?= formatRupiah($prod['harga_beli']) ?></td>
                                    <td style="text-align:right;font-weight:600;color:var(--primary-green);">
                                        <?= formatRupiah($prod['harga_jual']) ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php if ($is_premium): ?>
                                        <span class="badge badge-premium">
                                            <i class="fas fa-crown"></i> Premium
                                        </span>
                                        <?php else: ?>
                                        <span class="badge badge-normal">Normal</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align:center;padding:2rem;color:var(--text-gray);">
                                        Tidak ada produk ditemukan
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
