<?php
// ======================================================
// FILE   : supplierstatus.php
// DESC   : Mengubah status aktif/nonaktif supplier
// ======================================================

include_once "include/config.php";
include_once "include/guard.php";

// ---------- Ambil ID dan status dari URL (GET) ----------
$idSupplier = (int)$_GET['id'];      // paksa integer biar aman
$statusBaru = (int)$_GET['status'];  // 1 = aktif, 0 = nonaktif

// ---------- Jalankan update status ----------
$queryUpdateStatus = "UPDATE supplier SET status=$statusBaru WHERE supplier_id=$idSupplier";
mysqli_query($connection, $queryUpdateStatus);

// ---------- Kembali ke halaman utama ----------
header("location:supplier.php?msg=status");
exit;
?>
