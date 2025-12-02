<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "UPDATE produk SET status = 'nonaktif' WHERE id_produk = $id");
    header('Location: products.php?msg=deleted');
    exit;
}

// Handle Form Submit (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kode_produk = mysqli_real_escape_string($conn, $_POST['kode_produk']);
    $id_kategori = (int)$_POST['id_kategori'];
    $id_satuan = (int)$_POST['id_satuan'];
    $harga_beli = (int)$_POST['harga_beli'];
    $harga_jual = (int)$_POST['harga_jual'];
    $stok = (int)$_POST['stok'];
    $stok_minimum = (int)$_POST['stok_minimum'];
    $merk = mysqli_real_escape_string($conn, $_POST['merk']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    if (isset($_POST['id_produk']) && $_POST['id_produk']) {
        // Update
        $id = (int)$_POST['id_produk'];
        $query = "UPDATE produk SET 
                  nama_produk='$nama_produk', kode_produk='$kode_produk',
                  id_kategori=$id_kategori, id_satuan=$id_satuan,
                  harga_beli=$harga_beli, harga_jual=$harga_jual,
                  stok=$stok, stok_minimum=$stok_minimum,
                  merk='$merk', deskripsi='$deskripsi'
                  WHERE id_produk=$id";
        mysqli_query($conn, $query);
        header('Location: products.php?msg=updated');
    } else {
        // Insert
        $query = "INSERT INTO produk (nama_produk, kode_produk, id_kategori, id_satuan, 
                  harga_beli, harga_jual, stok, stok_minimum, merk, deskripsi, status)
                  VALUES ('$nama_produk', '$kode_produk', $id_kategori, $id_satuan,
                  $harga_beli, $harga_jual, $stok, $stok_minimum, '$merk', '$deskripsi', 'aktif')";
        mysqli_query($conn, $query);
        header('Location: products.php?msg=added');
    }
    exit;
}

// Get product for edit
$edit_product = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = $id");
    $edit_product = mysqli_fetch_assoc($result);
}

// Get all products
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$query = "SELECT p.*, k.nama_kategori, s.nama_satuan 
          FROM produk p
          JOIN kategori_barang k ON p.id_kategori = k.id_kategori
          JOIN satuan_barang s ON p.id_satuan = s.id_satuan
          WHERE p.status = 'aktif'";
if ($search) {
    $query .= " AND (p.nama_produk LIKE '%$search%' OR p.kode_produk LIKE '%$search%')";
}
$query .= " ORDER BY p.nama_produk";
$result_products = mysqli_query($conn, $query);

// Get categories & units for dropdown
$result_categories = mysqli_query($conn, "SELECT * FROM kategori_barang ORDER BY nama_kategori");
$result_units = mysqli_query($conn, "SELECT * FROM satuan_barang ORDER BY nama_satuan");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout{display:flex;min-height:calc(100vh - 70px)}
        .sidebar{width:250px;background:linear-gradient(180deg,var(--primary-green),var(--dark-green));padding:2rem 0;color:var(--white)}
        .sidebar-menu{list-style:none}
        .sidebar-menu li a{display:block;padding:1rem 2rem;color:var(--white);text-decoration:none;transition:all .3s ease;border-left:3px solid transparent}
        .sidebar-menu li a:hover,.sidebar-menu li a.active{background:rgba(255,255,255,.1);border-left-color:var(--white)}
        .main-content{flex:1;padding:2rem;background:var(--bg-light)}
        .card{background:var(--white);border-radius:15px;padding:1.5rem;box-shadow:var(--shadow);margin-bottom:1.5rem}
        .form-group{margin-bottom:1rem}
        .form-group label{display:block;color:var(--text-dark);font-weight:600;margin-bottom:.5rem}
        .form-group input,.form-group select,.form-group textarea{width:100%;padding:.8rem;border:2px solid #e5e7eb;border-radius:10px;font-size:1rem}
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--primary-green)}
        table{width:100%;border-collapse:collapse}
        th{background:var(--light-green);color:var(--text-dark);padding:.8rem;text-align:left;font-weight:600}
        td{padding:.8rem;border-bottom:1px solid #e5e7eb}
        tr:hover{background:var(--bg-light)}
        .btn-action{padding:.5rem 1rem;border-radius:8px;text-decoration:none;font-size:.9rem;margin:0 .2rem}
        .btn-edit{background:var(--light-green);color:var(--primary-green)}
        .btn-delete{background:#fee2e2;color:#dc2626}
        .alert{padding:1rem;border-radius:10px;margin-bottom:1rem}
        .alert-success{background:var(--light-green);color:var(--dark-green);border:1px solid var(--primary-green)}
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
                <li><a href="#"><?=$_SESSION['nama_lengkap']?></a></li>
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
            <h1 style="margin-bottom:2rem"><i class="fas fa-box"></i> Manajemen Produk</h1>
            <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?php if($_GET['msg']=='added'):?>✅ Produk berhasil ditambahkan!<?php endif;?>
                <?php if($_GET['msg']=='updated'):?>✅ Produk berhasil diupdate!<?php endif;?>
                <?php if($_GET['msg']=='deleted'):?>✅ Produk berhasil dihapus!<?php endif;?>
            </div>
            <?php endif;?>
            <div class="card fade-in">
                <h2 style="margin-bottom:1.5rem"><?=$edit_product?'Edit':'Tambah'?> Produk</h2>
                <form method="POST">
                    <?php if($edit_product):?><input type="hidden" name="id_produk" value="<?=$edit_product['id_produk']?>"><?php endif;?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                        <div class="form-group">
                            <label>Nama Produk</label>
                            <input type="text" name="nama_produk" value="<?=$edit_product['nama_produk']??''?>" required>
                        </div>
                        <div class="form-group">
                            <label>Kode Produk</label>
                            <input type="text" name="kode_produk" value="<?=$edit_product['kode_produk']??''?>" required>
                        </div>
                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="id_kategori" required>
                                <?php while($cat=mysqli_fetch_assoc($result_categories)):?>
                                <option value="<?=$cat['id_kategori']?>" <?=($edit_product&&$edit_product['id_kategori']==$cat['id_kategori'])?'selected':''?>><?=$cat['nama_kategori']?></option>
                                <?php endwhile;?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Satuan</label>
                            <select name="id_satuan" required>
                                <?php while($unit=mysqli_fetch_assoc($result_units)):?>
                                <option value="<?=$unit['id_satuan']?>" <?=($edit_product&&$edit_product['id_satuan']==$unit['id_satuan'])?'selected':''?>><?=$unit['nama_satuan']?></option>
                                <?php endwhile;?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Harga Beli</label>
                            <input type="number" name="harga_beli" value="<?=$edit_product['harga_beli']??''?>" required>
                        </div>
                        <div class="form-group">
                            <label>Harga Jual</label>
                            <input type="number" name="harga_jual" value="<?=$edit_product['harga_jual']??''?>" required>
                        </div>
                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stok" value="<?=$edit_product['stok']??0?>" required>
                        </div>
                        <div class="form-group">
                            <label>Stok Minimum</label>
                            <input type="number" name="stok_minimum" value="<?=$edit_product['stok_minimum']??5?>" required>
                        </div>
                        <div class="form-group">
                            <label>Merk</label>
                            <input type="text" name="merk" value="<?=$edit_product['merk']??''?>">
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" rows="3"><?=$edit_product['deskripsi']??''?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn-secondary" style="margin-top:1rem">
                        <i class="fas fa-save"></i> <?=$edit_product?'Update':'Simpan'?> Produk
                    </button>
                    <?php if($edit_product):?>
                    <a href="products.php" class="btn-primary" style="margin-left:1rem">Batal</a>
                    <?php endif;?>
                </form>
            </div>
            <div class="card fade-in-up">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
                    <h2>Daftar Produk</h2>
                    <form method="GET" style="display:flex;gap:1rem">
                        <input type="text" name="search" placeholder="Cari produk..." value="<?=$search?>" style="padding:.8rem;border:2px solid #e5e7eb;border-radius:10px">
                        <button type="submit" class="btn-secondary"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th><th>Nama Produk</th><th>Kategori</th><th>Stok</th><th>Harga Jual</th><th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($prod=mysqli_fetch_assoc($result_products)):?>
                            <tr>
                                <td><?=$prod['kode_produk']?></td>
                                <td><strong><?=$prod['nama_produk']?></strong><br><small><?=$prod['merk']?></small></td>
                                <td><?=$prod['nama_kategori']?></td>
                                <td><?=$prod['stok']?> <?=$prod['nama_satuan']?></td>
                                <td><?=formatRupiah($prod['harga_jual'])?></td>
                                <td>
                                    <a href="?edit=<?=$prod['id_produk']?>" class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="?delete=<?=$prod['id_produk']?>" class="btn-action btn-delete" onclick="return confirm('Yakin hapus produk ini?')"><i class="fas fa-trash"></i> Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>