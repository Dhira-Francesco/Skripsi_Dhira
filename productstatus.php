<?php
// ======================================================
// (A) INCLUDE KONFIGURASI
// ======================================================
include "include/config.php";
include "include/guard.php";

// ======================================================
// (B) AMBIL PARAMETER DARI URL
// ======================================================
$idProduk = $_GET['id'] ?? 0;
$statusBaru = $_GET['status'] ?? null;
$idProduk = (int)$idProduk;
$statusBaru = (int)$statusBaru;

// Validasi sederhana
if ($idProduk > 0 && ($statusBaru === 0 || $statusBaru === 1)) {

    // ======================================================
    // (C) UPDATE STATUS PRODUK
    // ======================================================
    $queryUbahStatus = "UPDATE product SET status=$statusBaru WHERE product_id=$idProduk";
    $resultUbahStatus = mysqli_query($connection, $queryUbahStatus);

    // ======================================================
    // (D) REDIRECT KEMBALI KE DAFTAR PRODUK
    // ======================================================
    header("location:product.php?msg=status");
    exit;
} else {
    header("location:product.php");
    exit;
}
?>
