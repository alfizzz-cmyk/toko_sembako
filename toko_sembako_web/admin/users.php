<?php
require_once '../config.php';
if (!isLoggedIn() || !isAdmin()) {header('Location: ../login.php');exit;}
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM users WHERE id_user = ".(int)$_GET['delete']);
    header('Location: users.php?msg=deleted');exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    if (isset($_POST['id_user']) && $_POST['id_user']) {
        $id = (int)$_POST['id_user'];
        $pass_update = $_POST['password'] ? ", password='$password'" : "";
        mysqli_query($conn, "UPDATE users SET nama_lengkap='$nama', email='$email', role='$role', no_hp='$hp' $pass_update WHERE id_user=$id");
        header('Location: users.php?msg=updated');
    } else {
        mysqli_query($conn, "INSERT INTO users (nama_lengkap, email, password, role, no_hp) VALUES ('$nama', '$email', '$password', '$role', '$hp')");
        header('Location: users.php?msg=added');
    }
    exit;
}
$edit_user = null;
if (isset($_GET['edit'])) {
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id_user = ".(int)$_GET['edit']);
    $edit_user = mysqli_fetch_assoc($result);
}
$result_users = mysqli_query($conn, "SELECT * FROM users ORDER BY role, nama_lengkap");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Users - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout{display:flex;min-height:calc(100vh - 70px)}
        .sidebar{width:250px;background:linear-gradient(180deg,var(--primary-green),var(--dark-green));padding:2rem 0;color:var(--white)}
        .sidebar-menu{list-style:none}
        .sidebar-menu li a{display:block;padding:1rem 2rem;color:var(--white);text-decoration:none;border-left:3px solid transparent}
        .sidebar-menu li a:hover,.sidebar-menu li a.active{background:rgba(255,255,255,.1);border-left-color:var(--white)}
        .main-content{flex:1;padding:2rem;background:var(--bg-light)}
        .card{background:var(--white);border-radius:15px;padding:1.5rem;box-shadow:var(--shadow);margin-bottom:1.5rem}
        .form-group{margin-bottom:1rem}
        .form-group label{display:block;font-weight:600;margin-bottom:.5rem}
        .form-group input,.form-group select{width:100%;padding:.8rem;border:2px solid #e5e7eb;border-radius:10px}
        table{width:100%;border-collapse:collapse}
        th{background:var(--light-green);padding:.8rem;text-align:left;font-weight:600}
        td{padding:.8rem;border-bottom:1px solid #e5e7eb}
        .badge{padding:.3rem .8rem;border-radius:15px;font-size:.85rem;font-weight:600}
        .badge-admin{background:#dbeafe;color:#3b82f6}
        .badge-kasir{background:#fef3c7;color:#f59e0b}
        .badge-customer{background:#d1fae5;color:#10b981}
        .alert{padding:1rem;border-radius:10px;margin-bottom:1rem;background:var(--light-green);color:var(--dark-green)}
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
                <li><a href="customers.php"><i class="fas fa-users"></i> Pelanggan</a></li>
                <li><a href="stock.php"><i class="fas fa-warehouse"></i> Stok</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="users.php" class="active"><i class="fas fa-user-shield"></i> Users</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <h1 style="margin-bottom:2rem"><i class="fas fa-user-shield"></i> Manajemen Users</h1>
            <?php if(isset($_GET['msg'])):?>
            <div class="alert">
                <?php if($_GET['msg']=='added'):?>✅ User berhasil ditambahkan!<?php endif;?>
                <?php if($_GET['msg']=='updated'):?>✅ User berhasil diupdate!<?php endif;?>
                <?php if($_GET['msg']=='deleted'):?>✅ User berhasil dihapus!<?php endif;?>
            </div>
            <?php endif;?>
            <div class="card">
                <h2 style="margin-bottom:1.5rem"><?=$edit_user?'Edit':'Tambah'?> User</h2>
                <form method="POST">
                    <?php if($edit_user):?><input type="hidden" name="id_user" value="<?=$edit_user['id_user']?>"><?php endif;?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?=$edit_user['nama_lengkap']??''?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?=$edit_user['email']??''?>" required>
                        </div>
                        <div class="form-group">
                            <label>Password <?=$edit_user?'(kosongkan jika tidak diganti)':''?></label>
                            <input type="password" name="password" <?=$edit_user?'':'required'?>>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" required>
                                <option value="admin" <?=($edit_user&&$edit_user['role']=='admin')?'selected':''?>>Admin</option>
                                <option value="kasir" <?=($edit_user&&$edit_user['role']=='kasir')?'selected':''?>>Kasir</option>
                                <option value="customer" <?=($edit_user&&$edit_user['role']=='customer')?'selected':''?>>Customer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>No. HP</label>
                            <input type="text" name="no_hp" value="<?=$edit_user['no_hp']??''?>">
                        </div>
                    </div>
                    <button type="submit" class="btn-secondary" style="margin-top:1rem"><i class="fas fa-save"></i> Simpan</button>
                    <?php if($edit_user):?><a href="users.php" class="btn-primary">Batal</a><?php endif;?>
                </form>
            </div>
            <div class="card">
                <h2 style="margin-bottom:1.5rem">Daftar User</h2>
                <table>
                    <thead>
                        <tr><th>Nama</th><th>Email</th><th>No. HP</th><th>Role</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result_users && mysqli_num_rows($result_users) > 0) {
                         while($u = mysqli_fetch_assoc($result_users)): ?>
                        <tr>
                            <td><strong><?=$u['nama_lengkap']?></strong></td>
                            <td><?=$u['email']?></td>
                            <td><?=$u['no_hp']?></td>
                            <td><span class="badge badge-<?=$u['role']?>"><?=ucfirst($u['role'])?></span></td>
                            <td>
                                <a href="?edit=<?=$u['id_user']?>" class="btn-action" style="padding:.5rem 1rem;border-radius:8px;background:var(--light-green);color:var(--primary-green);text-decoration:none"><i class="fas fa-edit"></i> Edit</a>
                                <?php if($u['id_user']!=$_SESSION['id_user']):?>
                                <a href="?delete=<?=$u['id_user']?>" class="btn-action" style="padding:.5rem 1rem;border-radius:8px;background:#fee2e2;color:#dc2626;text-decoration:none" onclick="return confirm('Yakin hapus user ini?')"><i class="fas fa-trash"></i> Hapus</a>
                                <?php endif;?>
                            </td>
                        </tr>
                        <?php endwhile;?>
                        <?php } else { ?>
    <tr>
        <td colspan="5" style="text-align:center;padding:2rem;color:#999">
            Belum ada user di database
        </td>
    </tr>
<?php } ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>