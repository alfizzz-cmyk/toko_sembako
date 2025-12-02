<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_toko_sembako');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF8
mysqli_set_charset($conn, "utf8mb4");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL
define('BASE_URL', 'http://localhost/toko_sembako_web/');

// Helper function untuk format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Helper function untuk generate kode transaksi
function generateKodeTransaksi($prefix = 'TRX') {
    return $prefix . '-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999));
}

// Helper function untuk cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function untuk cek admin
function isAdmin() {
    return isset($_SESSION['level']) && $_SESSION['level'] === 'admin';
}
?>
