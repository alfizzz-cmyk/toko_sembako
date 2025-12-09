<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// SEMENTARA: tampilkan semua transaksi dulu (tanpa filter pelanggan)
$sql = "SELECT *
        FROM transaksi_penjualan
        ORDER BY tanggal DESC, waktu DESC";

$result = mysqli_query($conn, $sql);
$total_transaksi = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Transaksi - Toko Sembako</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
    <nav class="navbar">
        <a href="../index.php" class="logo">
            <div class="logo-icon"><i class="fas fa-shopping-basket"></i></div>
            <span>Toko Sembako</span>
        </a>
        <ul class="nav-menu">
            <li><a href="../products.php">Produk</a></li>
            <li><a href="cart.php">Keranjang</a></li>
            <li><a href="orders.php" class="btn-primary">Riwayat</a></li>
            <li><a href="../customer/logout.php" class="btn-primary">Logout</a></li>
        </ul>
    </nav>
</header>

<section class="products-section" style="min-height:60vh;">
    <h2 class="section-title">Riwayat Transaksi</h2>
    <p class="section-subtitle">
        Total transaksi: <?= $total_transaksi ?>
    </p>

    <div class="card" style="max-width:1000px;margin:0 auto;">
        <?php if ($total_transaksi === 0): ?>
            <p>Belum ada transaksi.</p>
            <a href="../products.php" class="btn-secondary">
                <i class="fas fa-shopping-cart"></i> Belanja Sekarang
            </a>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                    <tr style="background:var(--light-green);">
                        <th style="padding:.8rem;text-align:left;">Tanggal</th>
                        <th style="padding:.8rem;text-align:left;">Kode</th>
                        <th style="padding:.8rem;text-align:right;">Total Item</th>
                        <th style="padding:.8rem;text-align:right;">Total Bayar</th>
                        <th style="padding:.8rem;text-align:left;">Metode</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($t = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td style="padding:.8rem;border-bottom:1px solid #e5e7eb;">
                                <?= htmlspecialchars($t['tanggal'] . ' ' . $t['waktu']) ?>
                            </td>
                            <td style="padding:.8rem;border-bottom:1px solid #e5e7eb;">
                                <?= htmlspecialchars($t['kode_transaksi']) ?>
                            </td>
                            <td style="padding:.8rem;border-bottom:1px solid #e5e7eb;text-align:right;">
                                <?= (int)$t['total_item'] ?>
                            </td>
                            <td style="padding:.8rem;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:600;">
                                <?= formatRupiah($t['total_bayar']) ?>
                            </td>
                            <td style="padding:.8rem;border-bottom:1px solid #e5e7eb;">
                                <?= htmlspecialchars(ucfirst($t['metode_bayar'])) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
</body>
</html>
