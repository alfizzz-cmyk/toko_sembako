<?php
require_once 'config.php';

// Get search query
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build query
$query = "SELECT p.*, k.nama_kategori, s.nama_satuan 
          FROM produk p 
          JOIN kategori_barang k ON p.id_kategori = k.id_kategori
          JOIN satuan_barang s ON p.id_satuan = s.id_satuan
          WHERE p.status = 'aktif'";

if ($search) {
    $query .= " AND (p.nama_produk LIKE '%$search%' OR p.merk LIKE '%$search%' OR k.nama_kategori LIKE '%$search%')";
}

if ($category_filter > 0) {
    $query .= " AND p.id_kategori = $category_filter";
}

$query .= " ORDER BY p.created_at DESC";

$result_products = mysqli_query($conn, $query);

// Get categories for filter
$query_categories = "SELECT * FROM kategori_barang ORDER BY nama_kategori";
$result_categories = mysqli_query($conn, $query_categories);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Produk - Toko Sembako</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filter-section {
            background: var(--white);
            padding: 2rem 5%;
            box-shadow: var(--shadow);
        }

        .filter-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .filter-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .category-filter-btn {
            background: var(--light-brown);
            color: var(--text-dark);
            border: 2px solid transparent;
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .category-filter-btn:hover,
        .category-filter-btn.active {
            background: var(--primary-brown);
            color: var(--white);
            transform: translateY(-2px);
        }

        .products-header {
            text-align: center;
            padding: 3rem 5% 2rem;
        }

        .products-count {
            color: var(--text-gray);
            margin-top: 1rem;
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

    <!-- SEARCH SECTION -->
    <section class="search-section">
        <div class="search-container">
            <form action="products.php" method="GET" class="search-box">
                <i class="fas fa-search" style="color: var(--primary-brown); font-size: 1.2rem; margin: 0 0.5rem;"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari produk...">
                <button type="submit">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>
        </div>
    </section>

    <!-- PRODUCTS HEADER -->
    <div class="products-header fade-in">
        <h1 class="section-title">
            <?php 
            if ($search) {
                echo 'Hasil Pencarian: "' . htmlspecialchars($search) . '"';
            } elseif ($category_filter > 0) {
                $cat_query = "SELECT nama_kategori FROM kategori_barang WHERE id_kategori = $category_filter";
                $cat_result = mysqli_query($conn, $cat_query);
                $cat = mysqli_fetch_assoc($cat_result);
                echo $cat['nama_kategori'];
            } else {
                echo "Semua Produk";
            }
            ?>
        </h1>
        <p class="products-count">Menampilkan <?= mysqli_num_rows($result_products) ?> produk</p>
    </div>

    <!-- FILTER SECTION -->
    <section class="filter-section fade-in">
        <div class="filter-container">
            <div class="filter-buttons">
                <a href="products.php" class="category-filter-btn <?= $category_filter == 0 ? 'active' : '' ?>">
                    <i class="fas fa-th"></i> Semua
                </a>
                <?php 
                mysqli_data_seek($result_categories, 0);
                while ($category = mysqli_fetch_assoc($result_categories)): 
                ?>
                <a href="products.php?category=<?= $category['id_kategori'] ?>" 
                   class="category-filter-btn <?= $category_filter == $category['id_kategori'] ? 'active' : '' ?>">
                    <?= $category['nama_kategori'] ?>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- PRODUCTS SECTION -->
    <section class="products-section">
        <div class="products-grid">
            <?php if (mysqli_num_rows($result_products) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($result_products)): ?>
                <div class="product-card fade-in-up" data-category="<?= $product['id_kategori'] ?>">
                    <?php if ($product['stok'] <= $product['stok_minimum']): ?>
                    <span class="product-badge">Stok Terbatas</span>
                    <?php endif; ?>

                    <div class="product-image">
                        <?php if ($product['gambar']): ?>
                            <img src="assets/image/products/<?= $product['gambar'] ?>" alt="<?= $product['nama_produk'] ?>">
                        <?php else: ?>
                            <i class="fas fa-image" style="font-size: 4rem; color: var(--primary-brown);"></i>
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
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                    <i class="fas fa-box-open" style="font-size: 5rem; color: var(--text-gray); margin-bottom: 1rem;"></i>
                    <h3 style="color: var(--text-gray);">Produk tidak ditemukan</h3>
                    <p style="color: var(--text-gray); margin: 1rem 0;">Coba kata kunci lain atau lihat semua produk</p>
                    <a href="products.php" class="btn-secondary">Lihat Semua Produk</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

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
                <a href="register.php">Daftar</a>
                <a href="login.php">Login</a>
            </div>

            <div class="footer-section">
                <h3>Kategori</h3>
                <?php 
                mysqli_data_seek($result_categories, 0);
                while ($cat = mysqli_fetch_assoc($result_categories)): 
                ?>
                <a href="products.php?category=<?= $cat['id_kategori'] ?>"><?= $cat['nama_kategori'] ?></a>
                <?php endwhile; ?>
            </div>

            <div class="footer-section">
                <h3>Kontak</h3>
                <p><i class="fas fa-phone"></i> 0251-8321456</p>
                <p><i class="fas fa-envelope"></i> info@tokosembako.com</p>
                <p><i class="fas fa-map-marker-alt"></i> Bogor, Jawa Barat</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 Toko Sembako. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
