<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product detail
$query = "SELECT p.*, k.nama_kategori, s.nama_satuan 
          FROM produk p 
          JOIN kategori_barang k ON p.id_kategori = k.id_kategori
          JOIN satuan_barang s ON p.id_satuan = s.id_satuan
          WHERE p.id_produk = $id AND p.status = 'aktif'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: products.php');
    exit;
}

$product = mysqli_fetch_assoc($result);

// Get related products (same category)
$query_related = "SELECT p.*, k.nama_kategori 
                  FROM produk p 
                  JOIN kategori_barang k ON p.id_kategori = k.id_kategori
                  WHERE p.id_kategori = {$product['id_kategori']} 
                  AND p.id_produk != $id 
                  AND p.status = 'aktif'
                  LIMIT 4";
$result_related = mysqli_query($conn, $query_related);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product['nama_produk'] ?> - Toko Sembako</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-detail-section {
            padding: 3rem 5%;
            background: var(--white);
        }

        .detail-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }

        .detail-image {
            background: var(--light-green);
            border-radius: 20px;
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 400px;
        }

        .detail-image i {
            font-size: 10rem;
            color: var(--primary-green);
        }

        .detail-info h1 {
            font-size: 2rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .detail-brand {
            color: var(--text-gray);
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .detail-price {
            font-size: 2.5rem;
            color: var(--primary-green);
            font-weight: bold;
            margin: 1.5rem 0;
        }

        .detail-specs {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: 15px;
            margin: 2rem 0;
        }

        .spec-item {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .spec-item:last-child {
            border-bottom: none;
        }

        .spec-label {
            font-weight: 600;
            color: var(--text-dark);
        }

        .spec-value {
            color: var(--text-gray);
        }

        .stock-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            margin: 1rem 0;
        }

        .stock-available {
            background: var(--light-green);
            color: var(--primary-green);
        }

        .stock-low {
            background: #fef3c7;
            color: #f59e0b;
        }

        .order-section {
            background: var(--light-green);
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .qty-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: var(--primary-green);
            color: var(--white);
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .qty-btn:hover {
            background: var(--dark-green);
            transform: scale(1.1);
        }

        .qty-input {
            width: 80px;
            text-align: center;
            font-size: 1.2rem;
            padding: 0.5rem;
            border: 2px solid var(--primary-green);
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .detail-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <span>Toko Sembako</span>
            </a>

            <ul class="nav-menu">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="products.php">Produk</a></li>
                <li><a href="index.php#categories">Kategori</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="customer/dashboard.php">Dashboard</a></li>
                    <li><a href="customer/logout.php" class="btn-primary">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php" class="btn-primary">Daftar</a></li>
                <?php endif; ?>
            </ul>

            <div class="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <!-- PRODUCT DETAIL -->
    <section class="product-detail-section fade-in">
        <div class="detail-container">
            <div class="detail-image slide-in-left">
                <?php if ($product['gambar']): ?>
                    <img src="assets/image/products/<?= $product['gambar'] ?>" alt="<?= $product['nama_produk'] ?>" style="max-width: 100%; height: auto;">
                <?php else: ?>
                    <i class="fas fa-box-open"></i>
                <?php endif; ?>
            </div>

            <div class="detail-info slide-in-right">
                <div class="product-category" style="color: var(--primary-green); font-weight: 600; margin-bottom: 0.5rem;">
                    <i class="fas fa-tag"></i> <?= $product['nama_kategori'] ?>
                </div>

                <h1><?= $product['nama_produk'] ?></h1>
                <p class="detail-brand">
                    <i class="fas fa-copyright"></i> <?= $product['merk'] ?? 'Original' ?>
                </p>

                <div class="detail-price">
                    <?= formatRupiah($product['harga_jual']) ?>
                </div>

                <span class="stock-status <?= $product['stok'] <= $product['stok_minimum'] ? 'stock-low' : 'stock-available' ?>">
                    <i class="fas fa-box"></i> 
                    <?= $product['stok'] > 0 ? "Stok: {$product['stok']} {$product['nama_satuan']}" : 'Stok Habis' ?>
                </span>

                <div class="detail-specs">
                    <div class="spec-item">
                        <span class="spec-label">Kode Produk</span>
                        <span class="spec-value"><?= $product['kode_produk'] ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Barcode</span>
                        <span class="spec-value"><?= $product['barcode'] ?? '-' ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Satuan</span>
                        <span class="spec-value"><?= $product['nama_satuan'] ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Kategori</span>
                        <span class="spec-value"><?= $product['nama_kategori'] ?></span>
                    </div>
                </div>

                <?php if ($product['keterangan']): ?>
                <div style="margin: 1.5rem 0;">
                    <h3 style="color: var(--text-dark); margin-bottom: 0.5rem;">Deskripsi</h3>
                    <p style="color: var(--text-gray); line-height: 1.8;"><?= nl2br($product['keterangan']) ?></p>
                </div>
                <?php endif; ?>

                <div class="order-section">
                    <h3 style="margin-bottom: 1rem;"><i class="fas fa-shopping-cart"></i> Pesan Produk</h3>

                    <?php if ($product['stok'] > 0): ?>
                    <form action="customer/add-to-cart.php" method="POST">
                        <input type="hidden" name="id_produk" value="<?= $product['id_produk'] ?>">

                        <div class="quantity-selector">
                            <button type="button" class="qty-btn" onclick="decreaseQty()">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" name="jumlah" id="qty" class="qty-input" value="1" min="1" max="<?= $product['stok'] ?>" required>
                            <button type="button" class="qty-btn" onclick="increaseQty()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>

                        <?php if (isLoggedIn()): ?>
                        <button type="submit" class="btn-secondary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                            <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                        </button>
                        <?php else: ?>
                        <a href="login.php?redirect=product-detail.php?id=<?= $product['id_produk'] ?>" class="btn-secondary" style="width: 100%; padding: 1rem; font-size: 1.1rem; text-align: center; display: block;">
                            <i class="fas fa-sign-in-alt"></i> Login untuk Membeli
                        </a>
                        <?php endif; ?>
                    </form>
                    <?php else: ?>
                    <div style="text-align: center; padding: 2rem;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #f59e0b; margin-bottom: 1rem;"></i>
                        <p style="color: var(--text-gray);">Maaf, produk sedang habis</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- RELATED PRODUCTS -->
    <?php if (mysqli_num_rows($result_related) > 0): ?>
    <section class="products-section" style="background: var(--bg-light);">
        <h2 class="section-title">Produk Terkait</h2>
        <p class="section-subtitle">Produk lain dari kategori <?= $product['nama_kategori'] ?></p>

        <div class="products-grid">
            <?php while ($related = mysqli_fetch_assoc($result_related)): ?>
            <div class="product-card fade-in-up">
                <div class="product-image">
                    <i class="fas fa-image" style="font-size: 4rem; color: var(--primary-green);"></i>
                </div>

                <div class="product-info">
                    <div class="product-category"><?= $related['nama_kategori'] ?></div>
                    <h3 class="product-name"><?= $related['nama_produk'] ?></h3>
                    <p class="product-brand"><?= $related['merk'] ?? 'Original' ?></p>

                    <div class="product-footer">
                        <div class="product-price"><?= formatRupiah($related['harga_jual']) ?></div>
                        <a href="product-detail.php?id=<?= $related['id_produk'] ?>" class="btn-detail">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- FOOTER -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Toko Sembako</h3>
                <p>Toko online terpercaya untuk kebutuhan sembako Anda.</p>
            </div>

            <div class="footer-section">
                <h3>Menu</h3>
                <a href="index.php">Beranda</a>
                <a href="products.php">Produk</a>
            </div>

            <div class="footer-section">
                <h3>Kontak</h3>
                <p><i class="fas fa-phone"></i> 0251-8321456</p>
                <p><i class="fas fa-envelope"></i> info@tokosembako.com</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2024 Toko Sembako. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        function increaseQty() {
            const qtyInput = document.getElementById('qty');
            const max = parseInt(qtyInput.max);
            const current = parseInt(qtyInput.value);
            if (current < max) {
                qtyInput.value = current + 1;
            }
        }

        function decreaseQty() {
            const qtyInput = document.getElementById('qty');
            const current = parseInt(qtyInput.value);
            if (current > 1) {
                qtyInput.value = current - 1;
            }
        }
    </script>
</body>
</html>
