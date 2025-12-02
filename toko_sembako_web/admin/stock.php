<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_stock'])) {
        $id_produk = $_POST['id_produk'];
        $jumlah = $_POST['jumlah'];
        $harga_beli = $_POST['harga_beli'];
        $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

        // Insert stok masuk
        $query = "INSERT INTO stok_masuk (tanggal, total_item, total_harga, keterangan) 
                  VALUES (CURDATE(), $jumlah, " . ($jumlah * $harga_beli) . ", '$keterangan')";
        mysqli_query($conn, $query);
        $id_stok = mysqli_insert_id($conn);

        // Insert detail
        $query_detail = "INSERT INTO detail_stok_masuk (id_stok_masuk, id_produk, jumlah, harga_beli) 
                        VALUES ($id_stok, $id_produk, $jumlah, $harga_beli)";
        mysqli_query($conn, $query_detail);

        // Update stok produk
        $query_update = "UPDATE produk SET stok = stok + $jumlah WHERE id_produk = $id_produk";
        mysqli_query($conn, $query_update);

        $success = "Stok berhasil ditambahkan!";
    }
}

// Get low stock products
$query_low = "SELECT * FROM v_stok_menipis ORDER BY stok ASC";
$result_low = mysqli_query($conn, $query_low);

// Get recent stock movements
$query_recent = "SELECT sm.*, COUNT(d.id_detail) as total_item
                 FROM stok_masuk sm
                 LEFT JOIN detail_stok_masuk d ON sm.id_stok_masuk = d.id_stok_masuk
                 GROUP BY sm.id_stok_masuk
                 ORDER BY sm.tanggal DESC
                 LIMIT 10";
$result_recent = mysqli_query($conn, $query_recent);

// Get all products for dropdown
$query_products = "SELECT id_produk, nama_produk, stok FROM produk WHERE status = 'aktif' ORDER BY nama_produk";
$result_products = mysqli_query($conn, $query_products);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Stok - Admin</title>
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
        .form-group { margin-bottom: 1rem; }
        .form-group label {
            display: block;
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-green);
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
        .badge-warning {
            background: #fef3c7;
            color: #f59e0b;
        }
        .badge-danger {
            background: #fee2e2;
            color: #dc2626;
        }
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: var(--light-green);
            color: var(--dark-green);
            border: 1px solid var(--primary-green);
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
                <li><a href="stock.php" class="active"><i class="fas fa-warehouse"></i> Stok</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="users.php"><i class="fas fa-user-shield"></i> Users</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;"><i class="fas fa-warehouse"></i> Manajemen Stok</h1>

            <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success ?>
            </div>
            <?php endif; ?>

            <!-- FORM TAMBAH STOK -->
            <div class="card fade-in">
                <h2 style="margin-bottom: 1.5rem;"><i class="fas fa-plus-circle"></i> Tambah Stok Masuk</h2>

                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="id_produk">Produk</label>
                            <select id="id_produk" name="id_produk" required>
                                <option value="">-- Pilih Produk --</option>
                                <?php while($prod = mysqli_fetch_assoc($result_products)): ?>
                                <option value="<?= $prod['id_produk'] ?>">
                                    <?= $prod['nama_produk'] ?> (Stok: <?= $prod['stok'] ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="jumlah">Jumlah</label>
                            <input type="number" id="jumlah" name="jumlah" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="harga_beli">Harga Beli (per unit)</label>
                            <input type="number" id="harga_beli" name="harga_beli" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <input type="text" id="keterangan" name="keterangan" placeholder="Pembelian dari...">
                        </div>
                    </div>

                    <button type="submit" name="add_stock" class="btn-secondary" style="margin-top: 1rem;">
                        <i class="fas fa-save"></i> Tambah Stok
                    </button>
                </form>
            </div>

            <!-- STOK MENIPIS -->
            <div class="card fade-in-up">
                <h2 style="margin-bottom: 1.5rem;"><i class="fas fa-exclamation-triangle"></i> Stok Menipis</h2>

                <?php if (mysqli_num_rows($result_low) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Stok Saat Ini</th>
                                <th>Stok Minimum</th>
                                <th>Satuan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($item = mysqli_fetch_assoc($result_low)): ?>
                            <tr>
                                <td><?= $item['kode_produk'] ?></td>
                                <td><?= $item['nama_produk'] ?></td>
                                <td><?= $item['nama_kategori'] ?></td>
                                <td><strong><?= $item['stok'] ?></strong></td>
                                <td><?= $item['stok_minimum'] ?></td>
                                <td><?= $item['nama_satuan'] ?></td>
                                <td>
                                    <?php if ($item['stok'] == 0): ?>
                                        <span class="badge badge-danger">Habis</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Menipis</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="text-align: center; padding: 2rem; color: var(--text-gray);">
                    <i class="fas fa-check-circle" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                    Semua stok aman!
                </p>
                <?php endif; ?>
            </div>
                    
            <!-- RIWAYAT STOK MASUK -->
            <div class="card fade-in-up">
                <h2 style="margin-bottom: 1.5rem;"><i class="fas fa-history"></i> Riwayat Stok Masuk</h2>

                <?php if (mysqli_num_rows($result_recent) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Total Item</th>
                                <th>Total Harga</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($stock = mysqli_fetch_assoc($result_recent)): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($stock['tanggal'])) ?></td>
                                <td><?= $stock['total_item'] ?> item</td>
                                <td><?= formatRupiah($stock['total_harga']) ?></td>
                                <td><?= $stock['keterangan'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="text-align: center; padding: 2rem; color: var(--text-gray);">Belum ada riwayat stok masuk</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>