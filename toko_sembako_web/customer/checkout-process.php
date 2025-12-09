<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isLoggedIn() || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$cart = $_SESSION['cart'];

// sementara: pelanggan default id 1 (PEL001)
$id_pelanggan = 1;

// sementara: user default id 1 (admin POS)
$id_user = 1;

$tanggal = date('Y-m-d');
$waktu   = date('H:i:s');
$kode_transaksi = 'TRX' . date('YmdHis');

// hitung total item dan subtotal
$total_item = 0;
$subtotal   = 0;
foreach ($cart as $item) {
    $total_item += (int)$item['qty'];
    $subtotal   += (float)$item['subtotal'];
}

// tanpa diskon & pajak dulu
$diskon       = 0.0;
$pajak        = 0.0;
$total_bayar  = $subtotal;
$tunai        = $total_bayar;
$kembalian    = 0.0;
$metode_bayar = 'tunai';
$keterangan   = 'Transaksi dari website';

mysqli_begin_transaction($conn);

try {
    // 1. PANGGIL stored procedure sp_tambah_transaksi_penjualan
    //    (definisi di db_toko_sembako.sql punya 14 parameter, status='selesai' diisi di dalam SP)
    $sqlSp = "CALL sp_tambah_transaksi_penjualan(
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
              )";

    $stmtSp = mysqli_prepare($conn, $sqlSp);
    if (!$stmtSp) {
        throw new Exception('Prepare SP gagal: ' . mysqli_error($conn));
    }

    // 14 parameter: kode, tanggal, waktu, id_pelanggan, id_user,
    // total_item, subtotal, diskon, pajak, total_bayar, tunai, kembalian,
    // metode_bayar, keterangan
    mysqli_stmt_bind_param(
        $stmtSp,
        'sssiidddddddss',   // 14 tipe untuk 14 variabel
        $kode_transaksi,    // s
        $tanggal,           // s
        $waktu,             // s
        $id_pelanggan,      // i
        $id_user,           // i
        $total_item,        // d
        $subtotal,          // d
        $diskon,            // d
        $pajak,             // d
        $total_bayar,       // d
        $tunai,             // d
        $kembalian,         // d
        $metode_bayar,      // s
        $keterangan         // s
    );
    mysqli_stmt_execute($stmtSp);
    mysqli_stmt_close($stmtSp);

    // bersihkan resultset dari CALL sebelum query berikutnya
    while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
        $dummy = mysqli_use_result($conn);
        if ($dummy instanceof mysqli_result) {
            mysqli_free_result($dummy);
        }
    }

    // ambil id_transaksi yang baru dari AUTO_INCREMENT
    $res = mysqli_query($conn, "SELECT LAST_INSERT_ID() AS id_transaksi");
    if (!$res) {
        throw new Exception('Gagal ambil LAST_INSERT_ID: ' . mysqli_error($conn));
    }
    $row = mysqli_fetch_assoc($res);
    $id_transaksi = (int)$row['id_transaksi'];
    if ($id_transaksi <= 0) {
        throw new Exception('ID transaksi tidak terbentuk dari SP.');
    }

    // 2. INSERT detail_transaksi (trigger akan otomatis kurangi stok)
    $sqlDetail = "INSERT INTO detail_transaksi
        (id_transaksi, id_produk, nama_produk, harga_jual, jumlah, diskon, subtotal)
        VALUES (?,?,?,?,?,?,?)";
    $stmtDetail = mysqli_prepare($conn, $sqlDetail);
    if (!$stmtDetail) {
        throw new Exception('Prepare detail gagal: ' . mysqli_error($conn));
    }

    foreach ($cart as $item) {
        $id_produk   = (int)$item['id'];
        $nama_produk = $item['nama'];
        $harga_jual  = (float)$item['harga'];
        $jumlah      = (int)$item['qty'];
        $diskonItem  = 0.0;
        $sub         = (float)$item['subtotal'];

        mysqli_stmt_bind_param(
            $stmtDetail,
            'iisdidd',
            $id_transaksi,
            $id_produk,
            $nama_produk,
            $harga_jual,
            $jumlah,
            $diskonItem,
            $sub
        );
        mysqli_stmt_execute($stmtDetail);
    }
    mysqli_stmt_close($stmtDetail);

    mysqli_commit($conn);

    unset($_SESSION['cart'], $_SESSION['cart_total']);

    $_SESSION['flash_success'] = 'Transaksi ' . $kode_transaksi . ' berhasil disimpan.';
    header('Location: orders.php');
    exit;

} catch (Throwable $e) {
    mysqli_rollback($conn);
    die('ERROR CHECKOUT (SP): ' . $e->getMessage());
}
