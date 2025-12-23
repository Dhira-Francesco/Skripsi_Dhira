<?php
// =======================================================
// (A) KONEKSI & GUARD
// =======================================================
include "include/config.php";
include "include/guard.php";

// =======================================================
// (B) AMBIL PARAM PRODUK (dukung ?id= atau ?product_id=)
// =======================================================
$productId = (int)($_GET['id'] ?? $_GET['product_id'] ?? 0);
if ($productId <= 0) {
  die("ID produk tidak valid.");
}

// =======================================================
// (C) AMBIL DETAIL PRODUK + TOTAL STOK
// =======================================================
$sqlProduk = "
  SELECT 
    p.product_id,
    COALESCE(b.brand_name,'')  AS brand_name,
    COALESCE(m.model_name,'')  AS model_name,
    COALESCE(c.category_name,'') AS category_name,
    COALESCE(p.ukuran,'')      AS ukuran
  FROM product p
  LEFT JOIN brand    b ON p.brand_id    = b.brand_id
  LEFT JOIN model    m ON p.model_id    = m.model_id
  LEFT JOIN category c ON p.category_id = c.category_id
  WHERE p.product_id = $productId
  LIMIT 1
";
$resProduk = mysqli_query($connection, $sqlProduk) or die("Query produk gagal: ".mysqli_error($connection));
$produk    = mysqli_fetch_assoc($resProduk);
if (!$produk) {
  die("Produk tidak ditemukan.");
}

// Total stok agregat (semua lokasi)
$sqlTotalStok = "
  SELECT SUM(quantity) AS total_stok
  FROM inventory
  WHERE product_id = $productId
";
$resStok   = mysqli_query($connection, $sqlTotalStok) or die("Query stok gagal: ".mysqli_error($connection));
$rowStok   = mysqli_fetch_assoc($resStok);
$totalStok = (int)($rowStok['total_stok'] ?? 0);

// =======================================================
// (D) AMBIL DAFTAR SUPPLIER UNTUK PRODUK INI
// =======================================================
$sqlSupplier = "
  SELECT s.namasupplier, s.phone, s.email, sp.price
  FROM supplier_product sp
  JOIN supplier s ON sp.supplier_id = s.supplier_id
  WHERE sp.product_id = $productId
  ORDER BY s.namasupplier ASC
";
$resSupplier = mysqli_query($connection, $sqlSupplier) or die("Query supplier gagal: ".mysqli_error($connection));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Info Supplier - PBS Inventory</title>
  <link href="css/styles.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<?php include "include/navbar.php"; ?>
<div id="layoutSidenav">
<?php include "include/menu.php"; ?>
<div id="layoutSidenav_content">
<main class="container-fluid px-4">
  <h1 class="mt-4">Informasi Supplier</h1>
  <ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item active">
      <?php 
        $namaProduk = trim(($produk['brand_name'].' '.$produk['model_name']));
        echo htmlspecialchars($namaProduk !== '' ? $namaProduk : 'Produk #'.$produk['product_id']);
      ?>
    </li>
  </ol>

  <!-- Detail Produk -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">Detail Produk</div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <div><strong>Produk</strong></div>
          <div><?php echo htmlspecialchars($namaProduk); ?></div>
        </div>
        <div class="col-md-4">
          <div><strong>Kategori</strong></div>
          <div><?php echo htmlspecialchars($produk['category_name'] ?: '-'); ?></div>
        </div>
        <div class="col-md-4">
          <div><strong>Ukuran</strong></div>
          <div><?php echo htmlspecialchars($produk['ukuran'] ?: '-'); ?></div>
        </div>
        <div class="col-md-4">
          <div class="mt-3"><strong>Total Stok (semua lokasi)</strong></div>
          <div class="<?php echo ($totalStok < 10 ? 'text-danger fw-bold' : ''); ?>">
            <?php echo $totalStok; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Daftar Supplier -->
  <div class="card mb-4">
    <div class="card-header bg-success text-white">Daftar Supplier</div>
    <div class="card-body">
      <table class="table table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:60px;">No</th>
            <th>Nama Supplier</th>
            <th style="width:180px;">Harga (Rp)</th>
            <th style="width:320px;">Kontak</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($resSupplier && mysqli_num_rows($resSupplier) > 0): ?>
            <?php $no=1; while($s = mysqli_fetch_assoc($resSupplier)): ?>
              <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($s['namasupplier']); ?></td>
                <td><?php echo number_format((float)$s['price'], 0, ',', '.'); ?></td>
                <td>
                  <?php if (!empty($s['phone'])): ?>
                    📞 <?php echo htmlspecialchars($s['phone']); ?><br>
                  <?php endif; ?>
                  <?php if (!empty($s['email'])): ?>
                    ✉️ <?php echo htmlspecialchars($s['email']); ?>
                  <?php endif; ?>
                  <?php if (empty($s['phone']) && empty($s['email'])): ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center text-muted">
                Belum ada supplier untuk produk ini.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <div class="d-flex gap-2 mt-3">
        <a href="productedit.php?id=<?php echo $productId; ?>" class="btn btn-primary">
          <i class="fa fa-edit"></i> Kelola Hubungan Supplier Produk Ini
        </a>
        <a href="index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
      </div>
    </div>
  </div>

</main>
   <?php include "include/footer.php"; ?>

</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
