<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// cek data dari form
if (!isset($_POST['id_produk'], $_POST['jumlah']) ||
    !is_numeric($_POST['id_produk']) ||
    !is_numeric($_POST['jumlah'])) {
    header('Location: ../products.php');
    exit;
}

$id_produk = (int)$_POST['id_produk'];
$qty_add   = (int)$_POST['jumlah'];
if ($qty_add < 1) $qty_add = 1;

// ambil data produk dari DB
$sql = "SELECT p.*, k.nama_kategori, s.nama_satuan
        FROM produk p
        JOIN kategori_barang k ON p.id_kategori = k.id_kategori
        JOIN satuan_barang s ON p.id_satuan = s.id_satuan
        WHERE p.id_produk = ? AND p.status = 'aktif'
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id_produk);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// jika produk tidak ada
if (!$produk) {
    $_SESSION['flash_error'] = 'Produk tidak ditemukan.';
    header('Location: ../products.php');
    exit;
}

// cek stok
if ($qty_add > (int)$produk['stok']) {
    $_SESSION['flash_error'] = 'Jumlah melebihi stok tersedia.';
    header('Location: ../product-detail.php?id='.$id_produk);
    exit;
}

// inisialisasi keranjang
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$id = $produk['id_produk'];

if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]['qty'] += $qty_add;
} else {
    $_SESSION['cart'][$id] = [
        'id'    => $produk['id_produk'],
        'nama'  => $produk['nama_produk'],
        'harga' => $produk['harga_jual'],
        'qty'   => $qty_add
    ];
}

// hitung subtotal per item
$_SESSION['cart'][$id]['subtotal'] =
    $_SESSION['cart'][$id]['qty'] * $_SESSION['cart'][$id]['harga'];

// hitung total cart
$total_cart = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_cart += $item['subtotal'];
}
$_SESSION['cart_total'] = $total_cart;

// pesan sukses + redirect ke keranjang
$_SESSION['flash_success'] =
    'Produk "'.$produk['nama_produk'].'" ditambahkan ke keranjang.';
header('Location: cart.php'); // customer/cart.php
exit;
