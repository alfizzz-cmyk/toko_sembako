<?php
require_once '../config.php';
if (!isLoggedIn() || !isAdmin()) {header('Location: ../login.php');exit;}
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM kategori_barang WHERE id_kategori = ".(int)$_GET['delete']);
    header('Location: categories.php?msg=deleted');exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);
    if (isset($_POST['id_kategori']) && $_POST['id_kategori']) {
        mysqli_query($conn, "UPDATE kategori_barang SET nama_kategori='$nama', keterangan='$ket' WHERE id_kategori=".(int)$_POST['id_kategori']);
        header('Location: categories.php?msg=updated');
    } else {
        mysqli_query($conn, "INSERT INTO kategori_barang (nama_kategori, keterangan) VALUES ('$nama', '$ket')");
        header('Location: categories.php?msg=added');
    }
    exit;
}
$edit_cat = null;
if (isset($_GET['edit'])) {
    $result = mysqli_query($conn, "SELECT * FROM kategori_barang WHERE id_kategori = ".(int)$_GET['edit']);
    $edit_cat = mysqli_fetch_assoc($result);
}
$result_categories = mysqli_query($conn, "SELECT k.*, COUNT(p.id_produk) as total_produk FROM kategori_barang k LEFT JOIN produk p ON k.id_kategori = p.id_kategori WHERE p.status='aktif' OR p.status IS NULL GROUP BY k.id_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kategori - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout{display:flex;min-height:calc(100vh - 70px)}
        .sidebar{width:250px;background:linear-gradient(180deg,var(--primary-brown),var(--dark-brown));padding:2rem 0;color:var(--white)}
        .sidebar-menu{list-style:none}
        .sidebar-menu li a{display:block;padding:1rem 2rem;color:var(--white);text-decoration:none;transition:all .3s ease;border-left:3px solid transparent}
        .sidebar-menu li a:hover,.sidebar-menu li a.active{background:rgba(255,255,255,.1);border-left-color:var(--white)}
        .main-content{flex:1;padding:2rem;background:var(--bg-light)}
        .card{background:var(--white);border-radius:15px;padding:1.5rem;box-shadow:var(--shadow);margin-bottom:1.5rem}
        .form-group{margin-bottom:1rem}
        .form-group label{display:block;font-weight:600;margin-bottom:.5rem}
        .form-group input,.form-group textarea{width:100%;padding:.8rem;border:2px solid #e5e7eb;border-radius:10px}
        table{width:100%;border-collapse:collapse}
        th{background:var(--light-brown);padding:.8rem;text-align:left;font-weight:600}
        td{padding:.8rem;border-bottom:1px solid #e5e7eb}
        .alert{padding:1rem;border-radius:10px;margin-bottom:1rem;background:var(--light-brown);color:var(--dark-brown)}
        .btn-action{padding:.5rem 1rem;border-radius:8px;text-decoration:none;margin:.2rem}
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
                <li><a href="categories.php" class="active"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="transactions.php"><i class="fas fa-receipt"></i> Transaksi</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Pelanggan</a></li>
                <li><a href="stock.php"><i class="fas fa-warehouse"></i> Stok</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="users.php"><i class="fas fa-user-shield"></i> Users</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <h1 style="margin-bottom:2rem"><i class="fas fa-tags"></i> Manajemen Kategori</h1>
            <?php if(isset($_GET['msg'])):?>
            <div class="alert">
                <?php if($_GET['msg']=='added'):?>✅ Kategori berhasil ditambahkan!<?php endif;?>
                <?php if($_GET['msg']=='updated'):?>✅ Kategori berhasil diupdate!<?php endif;?>
                <?php if($_GET['msg']=='deleted'):?>✅ Kategori berhasil dihapus!<?php endif;?>
            </div>
            <?php endif;?>
            <div class="card">
                <h2 style="margin-bottom:1.5rem"><?=$edit_cat?'Edit':'Tambah'?> Kategori</h2>
                <form method="POST">
                    <?php if($edit_cat):?><input type="hidden" name="id_kategori" value="<?=$edit_cat['id_kategori']?>"><?php endif;?>
                    <div class="form-group">
                        <label>Nama Kategori</label>
                        <input type="text" name="nama_kategori" value="<?=$edit_cat['nama_kategori']??''?>" required>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" rows="3"><?=$edit_cat['keterangan']??''?></textarea>
                    </div>
                    <button type="submit" class="btn-secondary"><i class="fas fa-save"></i> Simpan</button>
                    <?php if($edit_cat):?><a href="categories.php" class="btn-primary">Batal</a><?php endif;?>
                </form>
            </div>
            <div class="card">
                <h2 style="margin-bottom:1.5rem">Daftar Kategori</h2>
                <table>
                    <thead>
                        <tr><th>Nama Kategori</th><th>Keterangan</th><th>Total Produk</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php while($cat=mysqli_fetch_assoc($result_categories)):?>
                        <tr>
                            <td><strong><?=$cat['nama_kategori']?></strong></td>
                            <td><?=$cat['keterangan']?></td>
                            <td><?=$cat['total_produk']?> produk</td>
                            <td>
                                <a href="?edit=<?=$cat['id_kategori']?>" class="btn-action" style="background:var(--light-brown);color:var(--primary-brown)"><i class="fas fa-edit"></i> Edit</a>
                                <a href="?delete=<?=$cat['id_kategori']?>" class="btn-action" style="background:#fee2e2;color:#dc2626" onclick="return confirm('Yakin hapus?')"><i class="fas fa-trash"></i> Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile;?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>