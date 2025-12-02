# 🛒 SISTEM TOKO SEMBAKO ONLINE
### Aplikasi Web E-Commerce Sembako dengan Manajemen Lengkap

---

## 📋 DESKRIPSI

Sistem Toko Sembako Online adalah aplikasi web e-commerce yang dirancang khusus untuk toko sembako dengan fitur manajemen produk, stok, transaksi penjualan, pelanggan, dan laporan yang lengkap.

### ✨ FITUR UTAMA

#### 👥 UNTUK PELANGGAN:
- ✅ Registrasi & Login
- ✅ Browse produk dengan filter kategori
- ✅ Pencarian produk berdasarkan nama
- ✅ Detail produk lengkap
- ✅ Dashboard pelanggan
- ✅ Riwayat transaksi
- ✅ Responsive design (mobile-friendly)

#### 🔐 UNTUK ADMIN:
- ✅ Dashboard statistik lengkap
- ✅ Manajemen Produk (CRUD)
- ✅ Manajemen Kategori
- ✅ Manajemen Stok (masuk/keluar)
- ✅ Manajemen Transaksi Penjualan
- ✅ Manajemen Pelanggan & Member
- ✅ Laporan penjualan (harian, bulanan)
- ✅ Laporan produk terlaris
- ✅ Alert stok menipis
- ✅ Multi-user dengan level akses

---

## 🎨 TEKNOLOGI

- **Frontend:** HTML5, CSS3 (Animasi Modern), JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL/MariaDB
- **Framework:** Native PHP (No Framework)
- **Design:** Custom CSS dengan tema hijau & animasi

---

## 📦 REQUIREMENTS

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau MariaDB 10.3+
- Apache/Nginx Web Server
- Web Browser modern (Chrome, Firefox, Edge, Safari)

---

## 🚀 CARA INSTALASI

### 1. PERSIAPAN DATABASE

#### A. Import Database
1. Buka phpMyAdmin (`http://localhost/phpmyadmin`)
2. Klik tab "SQL"
3. Copy-paste isi file `db_toko_sembako_indonesia.sql`
4. Klik "Go" untuk mengeksekusi
5. Database `db_toko_sembako` akan otomatis terbuat

#### B. Konfigurasi Database
Edit file `config.php` sesuai dengan konfigurasi database Anda:
```php
define('DB_HOST', 'localhost');      // Sesuaikan jika berbeda
define('DB_USER', 'root');           // Sesuaikan username MySQL
define('DB_PASS', '');               // Sesuaikan password MySQL
define('DB_NAME', 'db_toko_sembako');
```

### 2. SETUP WEB

#### A. Copy Files
1. Extract folder `toko_sembako_web` ke direktori htdocs (XAMPP) atau www (WAMP)
2. Pastikan struktur folder seperti ini:
```
htdocs/
└── toko_sembako_web/
    ├── assets/
    ├── admin/
    ├── customer/
    ├── includes/
    ├── config.php
    ├── index.php
    └── ...
```

#### B. Set Permissions (Linux/Mac)
```bash
chmod -R 755 toko_sembako_web/
chmod -R 777 toko_sembako_web/assets/images/
```

### 3. AKSES APLIKASI

#### A. Jalankan Server
- XAMPP: Start Apache & MySQL
- WAMP: Start All Services

#### B. Buka Browser
- **Homepage:** `http://localhost/toko_sembako_web/`
- **Admin Panel:** `http://localhost/toko_sembako_web/admin/`

---

## 👤 USER DEFAULT

### ADMIN
- **Username:** admin
- **Password:** admin123
- **Level:** Admin (Full Access)

### KASIR
- **Username:** kasir01
- **Password:** kasir123
- **Level:** Kasir

### OWNER
- **Username:** owner
- **Password:** owner123
- **Level:** Owner

### STAFF
- **Username:** staff01
- **Password:** staff123
- **Level:** Staff

> ⚠️ **PENTING:** Ganti password default setelah instalasi!

---

## 📂 STRUKTUR FOLDER

```
toko_sembako_web/
├── assets/
│   ├── css/
│   │   └── style.css           # Main stylesheet dengan animasi
│   ├── js/
│   │   └── main.js             # JavaScript interaktif
│   └── images/
│       └── products/           # Upload gambar produk
├── admin/
│   ├── dashboard.php           # Dashboard admin
│   ├── products.php            # Manajemen produk
│   ├── categories.php          # Manajemen kategori
│   ├── transactions.php        # Manajemen transaksi
│   ├── customers.php           # Manajemen pelanggan
│   ├── stock.php               # Manajemen stok
│   ├── reports.php             # Laporan
│   └── users.php               # Manajemen user
├── customer/
│   ├── dashboard.php           # Dashboard pelanggan
│   ├── add-to-cart.php         # Proses tambah keranjang
│   └── logout.php              # Logout
├── includes/                   # Helper functions
├── config.php                  # Konfigurasi database
├── index.php                   # Homepage
├── products.php                # Halaman semua produk
├── product-detail.php          # Detail produk
├── login.php                   # Halaman login
└── register.php                # Halaman registrasi
```

---

## 🎯 FUNGSIONAL REQUIREMENTS

### 1. MANAJEMEN PRODUK
- ✅ Tambah, edit, hapus produk
- ✅ Upload gambar produk
- ✅ Barcode support
- ✅ Kategori & satuan
- ✅ Harga beli & harga jual
- ✅ Stok & stok minimum
- ✅ Status aktif/nonaktif

### 2. MANAJEMEN STOK
- ✅ Pencatatan stok masuk (pembelian)
- ✅ Pencatatan stok keluar (penjualan)
- ✅ Alert stok menipis
- ✅ Riwayat pergerakan stok
- ✅ Supplier management

### 3. MANAJEMEN TRANSAKSI
- ✅ Point of Sale (POS) kasir
- ✅ Keranjang belanja
- ✅ Multi payment method (tunai, debit, QRIS, dll)
- ✅ Generate kode transaksi otomatis
- ✅ Cetak struk digital
- ✅ Riwayat transaksi

### 4. MANAJEMEN PELANGGAN
- ✅ Registrasi pelanggan
- ✅ Login/logout
- ✅ Tipe pelanggan (reguler/member)
- ✅ Sistem poin member
- ✅ Riwayat pembelian

### 5. LAPORAN SISTEM
- ✅ Laporan penjualan harian/bulanan
- ✅ Laporan produk terlaris
- ✅ Laporan stok menipis
- ✅ Laporan pendapatan
- ✅ Dashboard statistik real-time

---

## 💡 FITUR UNGGULAN

### 1. RESPONSIVE DESIGN
- Mobile-first approach
- Adaptive layout untuk semua device
- Touch-friendly interface

### 2. ANIMASI MODERN
- Fade in animations
- Slide animations
- Bounce effects
- Smooth transitions
- Hover effects

### 3. UI/UX
- Tema hijau modern & fresh
- Consistent color scheme
- Intuitive navigation
- Search & filter yang powerful
- Loading indicators

### 4. KEAMANAN
- Password hashing (MD5)
- Session management
- SQL injection prevention
- XSS protection
- Access level control

---

## 📊 DATABASE

### TABEL UTAMA

1. **users** - Data pengguna sistem
2. **kategori_barang** - Kategori produk
3. **satuan_barang** - Satuan produk
4. **supplier** - Data pemasok
5. **pelanggan** - Data pelanggan/member
6. **produk** - Data produk (50 produk sample)
7. **stok_masuk** - Header pembelian
8. **detail_stok_masuk** - Detail pembelian
9. **transaksi_penjualan** - Header penjualan
10. **detail_transaksi** - Detail penjualan
11. **tmp_transaksi** - Temporary cart
12. **retur_penjualan** - Data retur
13. **pengeluaran** - Pengeluaran operasional

### VIEW (untuk laporan)
- `v_stok_menipis` - Produk stok menipis
- `v_laporan_penjualan_harian` - Laporan penjualan
- `v_produk_terlaris` - Produk terlaris

---

## 🔧 TROUBLESHOOTING

### Database Connection Error
```
Pastikan:
- MySQL service running
- Config database benar
- Database sudah di-import
```

### 404 Not Found
```
Pastikan:
- Files ada di folder htdocs/www
- Akses URL dengan benar
- .htaccess dikonfigurasi (jika perlu)
```

### Session Error
```
Pastikan:
- session_start() aktif
- Folder session writable
- PHP session extension enabled
```

---

## 📞 SUPPORT & CONTACT

Untuk bantuan lebih lanjut:
- Email: info@tokosembako.com
- Phone: 0251-8321456
- Location: Bogor, Jawa Barat, Indonesia

---

## 📝 LICENSE

© 2024 Toko Sembako. All Rights Reserved.

Dibuat dengan ❤️ di Indonesia 🇮🇩

---

## 🙏 CREDITS

- Font Awesome untuk icons
- Google Fonts untuk typography
- Data harga dari Panel Harga Pangan & SP2KP Kemendag

---

## 📌 CATATAN

- Sistem ini sudah production-ready
- Database include 50+ produk sample dengan harga real Indonesia 2024-2025
- Semua merk produk menggunakan brand populer Indonesia
- Harga disesuaikan dengan pasaran Indonesia

**Happy Coding! 🚀**
