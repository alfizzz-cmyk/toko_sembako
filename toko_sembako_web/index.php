<?php
require_once 'config.php';

// Get categories
$query_categories = "SELECT * FROM kategori_barang ORDER BY id_kategori";
$result_categories = mysqli_query($conn, $query_categories);

// Get featured products (limit 6)
$query_products = "SELECT p.*, k.nama_kategori, s.nama_satuan 
                   FROM produk p 
                   JOIN kategori_barang k ON p.id_kategori = k.id_kategori
                   JOIN satuan_barang s ON p.id_satuan = s.id_satuan
                   WHERE p.status = 'aktif'
                   ORDER BY p.created_at DESC
                   LIMIT 6";
$result_products = mysqli_query($conn, $query_products);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Sembako - Belanja Kebutuhan Pokok Online</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <li><a href="#categories">Kategori</a></li>
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

    <!-- HERO SECTION -->
    <section class="hero fade-in">
        <div class="hero-content">
            <div class="hero-text slide-in-left">
                <h1>Belanja <span>Sembako</span><br>Mudah & Terpercaya</h1>
                <p>Dapatkan kebutuhan pokok berkualitas dengan harga terjangkau. Pengiriman cepat ke seluruh Indonesia!</p>
                <div class="hero-buttons">
                    <a href="products.php" class="btn-secondary">
                        <i class="fas fa-shopping-cart"></i> Belanja Sekarang
                    </a>
                    <a href="#categories" class="btn-primary">
                        <i class="fas fa-list"></i> Lihat Kategori
                    </a>
                </div>
            </div>
            <div class="hero-image slide-in-right">
                <i class="fas fa-shopping-basket" style="font-size: 15rem; color: var(--primary-green); opacity: 0.3;"></i>
            </div>
        </div>
    </section>

    <!-- SEARCH SECTION -->
    <section class="search-section fade-in">
        <div class="search-container">
            <form action="products.php" method="GET" class="search-box">
                <i class="fas fa-search" style="color: var(--primary-green); font-size: 1.2rem; margin: 0 0.5rem;"></i>
                <input type="text" name="search" placeholder="Cari produk sembako (beras, minyak, gula, dll...)">
                <button type="submit">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>
        </div>
    </section>

    <!-- CATEGORIES SECTION -->
    <section id="categories" class="categories-section">
        <h2 class="section-title">Kategori Produk</h2>
        <p class="section-subtitle">Temukan produk sesuai kebutuhan Anda</p>

        <div class="categories-grid">
            <?php 
            $icons = [
                'Sembako Pokok' => 'fa-bowl-rice',
                'Minuman' => 'fa-bottle-water',
                'Bumbu dan Rempah' => 'fa-pepper-hot',
                'Sabun & Pembersih' => 'fa-pump-soap'
            ];

            while ($category = mysqli_fetch_assoc($result_categories)): 
                $icon = $icons[$category['nama_kategori']] ?? 'fa-box';
            ?>
            <a href="products.php?category=<?= $category['id_kategori'] ?>" class="category-card">
                <div class="category-icon">
                    <i class="fas <?= $icon ?>"></i>
                </div>
                <h3><?= $category['nama_kategori'] ?></h3>
                <p><?= $category['keterangan'] ?></p>
            </a>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- PRODUCTS SECTION -->
    <section class="products-section">
        <h2 class="section-title">Produk Terbaru</h2>
        <p class="section-subtitle">Produk pilihan dengan kualitas terbaik</p>

        <div class="products-grid">
            <?php while ($product = mysqli_fetch_assoc($result_products)): ?>
            <div class="product-card" data-category="<?= $product['id_kategori'] ?>">
                <?php if ($product['stok'] <= $product['stok_minimum']): ?>
                <span class="product-badge">Stok Terbatas</span>
                <?php endif; ?>

                <div class="product-image">
                    <?php if ($product['gambar']): ?>
                        <img src="assets/image/products/<?= $product['gambar'] ?>" alt="<?= $product['nama_produk'] ?>">
                    <?php else: ?>
                        <i class="fas fa-image" style="font-size: 4rem; color: var(--primary-green);"></i>
                    <?php endif; ?>
                </div>

                <div class="product-info">
                    <div class="product-category"><?= $product['nama_kategori'] ?></div>
                    <h3 class="product-name"><?= $product['nama_produk'] ?></h3>
                    <p class="product-brand"><?= $product['merk'] ?? 'Original' ?></p>

                    <div class="product-footer">
                        <div class="product-price"><?= formatRupiah($product['harga_jual']) ?></div>
                        <a href="product-detail.php?id=<?= $product['id_produk'] ?>" class="btn-detail">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="see-more-container">
            <a href="products.php" class="btn-see-more">
                <i class="fas fa-arrow-right"></i> Lihat Semua Produk
            </a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Toko Sembako</h3>
                <p>Toko online terpercaya untuk kebutuhan sembako Anda. Belanja mudah, cepat, dan aman!</p>
            </div>

            <div class="footer-section">
                <h3>Menu</h3>
                <a href="index.php">Beranda</a>
                <a href="products.php">Produk</a>
                <a href="register.php">Daftar</a>
                <a href="login.php">Login</a>
            </div>

            <div class="footer-section">
                <h3>Kategori</h3>
                <a href="products.php?category=1">Sembako Pokok</a>
                <a href="products.php?category=2">Minuman</a>
                <a href="products.php?category=3">Bumbu & Rempah</a>
                <a href="products.php?category=4">Sabun & Pembersih</a>
            </div>

            <div class="footer-section">
                <h3>Kontak</h3>
                <p><i class="fas fa-phone"></i> 0123-4567890</p>
                <p><i class="fas fa-envelope"></i> info@tokosembako.com</p>
                <p><i class="fas fa-map-marker-alt"></i> Bandar Lampung, Lampung</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 Toko Sembako. All rights reserved. <i class="fas fa-heart" style="color: red;"></i> </p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
