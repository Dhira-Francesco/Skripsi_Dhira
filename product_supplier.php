<!DOCTYPE html>
<?php
// ======================================================
// 1. Sertakan koneksi dan guard login
// ======================================================
include "include/config.php";
include "include/guard.php";

// ======================================================
// 2. Ambil ID produk dari URL (GET)
// ======================================================
$productId = (int)($_GET['id'] ?? 0);

// Jika tidak ada ID, kembalikan ke halaman utama
if ($productId <= 0) {
    header("Location: product.php");
    exit;
}

// ======================================================
// 3. Ambil info nama produk dari tabel product
// ======================================================
$queryProduct = "SELECT name FROM product WHERE product_id=$productId";
$resultProduct = mysqli_query($connection, $queryProduct);
$dataProduct   = mysqli_fetch_assoc($resultProduct);

// ======================================================
// 4. Ambil daftar supplier untuk produk ini
//    (join ke tabel supplier_product + supplier)
// ======================================================
$querySupplier = "
    SELECT s.namasupplier, s.phone, s.email, sp.price
    FROM supplier_product sp
    JOIN supplier s ON sp.supplier_id = s.supplier_id
    WHERE sp.product_id = $productId
";
$resultSupplier = mysqli_query($connection, $querySupplier);
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Daftar Supplier Produk</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="sb-nav-fixed">
<?php include "include/navbar.php"; ?>
<div id="layoutSidenav">
    <?php include "include/menu.php"; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 mt-4">
                <!-- Judul halaman -->
                <h3>Daftar Supplier untuk Produk:
                    <span class="text-primary"><?php echo htmlspecialchars($dataProduct['name']); ?></span>
                </h3>
                <hr>

                <!-- Tabel supplier -->
                <div class="card mb-4">
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Supplier</th>
                                    <th>Telepon</th>
                                    <th>Email</th>
                                    <th>Harga (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($resultSupplier) > 0): ?>
                                    <?php while ($rowSupp = mysqli_fetch_assoc($resultSupplier)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($rowSupp['namasupplier']); ?></td>
                                        <td><?php echo htmlspecialchars($rowSupp['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($rowSupp['email']); ?></td>
                                        <td><?php echo number_format($rowSupp['price'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada supplier untuk produk ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <!-- Tombol kembali -->
                        <a href="product.php" class="btn btn-secondary mt-2">Kembali ke Daftar Produk</a>
                    </div>
                </div>
            </div>
        </main>
   <?php include "include/footer.php"; ?>

    </div>
</div>

<!-- Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
</body>
</html>
