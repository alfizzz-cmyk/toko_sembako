<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $cart = [];
    $total = 0;
} else {
    $cart  = $_SESSION['cart'];
    $total = $_SESSION['cart_total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
            <li><a href="cart.php" class="btn-primary">Keranjang</a></li>
            <li><a href="../customer/logout.php" class="btn-primary">Logout</a></li>
        </ul>
    </nav>
</header>

<section class="products-section" style="min-height:60vh;">
    <h2 class="section-title">Keranjang Belanja</h2>
    <p class="section-subtitle">Cek kembali pesanan Anda sebelum checkout</p>

    <div class="card" style="max-width:1000px;margin:0 auto;">
        <?php if (empty($cart)): ?>
            <p>Keranjang masih kosong.</p>
            <a href="../products.php" class="btn-secondary">
                <i class="fas fa-shopping-cart"></i> Belanja Sekarang
            </a>
        <?php else: ?>
            <div style="overflow-x:auto;margin-bottom:1.5rem;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                    <tr style="background:var(--light-green);">
                        <th style="padding:.8rem;text-align:left;">Produk</th>
                        <th style="padding:.8rem;text-align:right;">Harga</th>
                        <th style="padding:.8rem;text-align:center;">Qty</th>
                        <th style="padding:.8rem;text-align:right;">Subtotal</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cart as $item): ?>
                        <tr>
                            <td style="padding:.8rem;border-bottom:1px solid #e5e7eb;">
                                <?= htmlspecialchars($item['nama']) ?>
                            </td>
                            <td style="padding:.8rem;border-bottom:1px solid #e5e7eb;text-align:right;">
                                <?= formatRupiah($item['harga']) ?>
                            </td>
                            <td style="padding:.8rem;border-bottom:1px solid #e5e7eb;text-align:center;">
                                <?= (int)$item['qty'] ?>
                            </td>
                            <td style="padding:.8rem;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:600;">
                                <?= formatRupiah($item['subtotal']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="3" style="padding:.8rem;text-align:right;">Total</th>
                        <th style="padding:.8rem;text-align:right;color:var(--primary-green);font-size:1.2rem;">
                            <?= formatRupiah($total) ?>
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div class="hero-buttons" style="justify-content:space-between;">
                <a href="../products.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Lanjut Belanja
                </a>
                <a href="checkout-process.php" class="btn-primary">
                    <i class="fas fa-credit-card"></i> Proses Checkout
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
                            
</body>
</html>
