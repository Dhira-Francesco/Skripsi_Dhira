<?php
// ======================================================
// FILE   : lokasistatus.php
// DESC   : Mengubah status aktif/nonaktif lokasi
// ======================================================

include_once "include/config.php";
include_once "include/guard.php";

// ---------- Ambil ID dan status dari URL (GET) ----------
$idlokasi = (int)$_GET['id'];      // paksa integer biar aman
$statusBaru = (int)$_GET['status'];  // 1 = aktif, 0 = nonaktif

// ---------- Jalankan update status ----------
$queryUpdateStatus = "UPDATE location SET status=$statusBaru WHERE location_id=$idlokasi";
mysqli_query($connection, $queryUpdateStatus);

// ---------- Kembali ke halaman utama ----------
header("location:lokasi.php?msg=status");
exit;
?>
