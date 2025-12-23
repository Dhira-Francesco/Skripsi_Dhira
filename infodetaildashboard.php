<?php
// =======================================================
// (A) KONEKSI & GUARD
// =======================================================
include "include/config.php";
include "include/guard.php";

// =======================================================
// (B) PERSIAPAN WAKTU (BULAN INI)
// =======================================================
$bulanIni = date('Y-m'); // format YYYY-MM

// =======================================================
// (C) HITUNG KPI UTAMA (PASTIKAN SELALU TERDEFINISI)
// =======================================================

// 1) Total produk aktif
$sqlTotalProduk = "SELECT COUNT(*) AS total FROM product WHERE status=1";
$resTotalProduk = mysqli_query($connection, $sqlTotalProduk) or die("Query total produk gagal: ".mysqli_error($connection));
$rowTotalProduk = mysqli_fetch_assoc($resTotalProduk);
$totalProduk    = (int)($rowTotalProduk['total'] ?? 0);

// 2) Total transaksi bulan ini (semua tipe)
$sqlTotalTransaksi = "
  SELECT COUNT(*) AS total
  FROM `transaction`
  WHERE DATE_FORMAT(transaction_date, '%Y-%m') = '$bulanIni'
";
$resTotalTransaksi = mysqli_query($connection, $sqlTotalTransaksi) or die("Query total transaksi gagal: ".mysqli_error($connection));
$rowTotalTransaksi = mysqli_fetch_assoc($resTotalTransaksi);
$totalTransaksi    = (int)($rowTotalTransaksi['total'] ?? 0);


// 3) Data stok kritis (total stok < 10, digabung seluruh lokasi)
//    -> juga kita butuh jumlah barisnya utk card
$sqlStokKritis = "
  SELECT 
    p.product_id,
    COALESCE(b.brand_name,'') AS brand_name,
    COALESCE(m.model_name,'') AS model_name,
    SUM(i.quantity) AS total_stok,
    p.safety_stock,
    p.lead_time_days,
    p.avg_daily_usage,
    p.reorder_point
  FROM inventory i
  JOIN product p ON i.product_id = p.product_id
  LEFT JOIN brand b ON p.brand_id = b.brand_id
  LEFT JOIN model m ON p.model_id = m.model_id
  WHERE p.status = 1
  GROUP BY 
    p.product_id,
    p.safety_stock,
    p.lead_time_days,
    p.avg_daily_usage,
    p.reorder_point
  HAVING total_stok < p.reorder_point
  ORDER BY total_stok ASC
";
$resStokKritis = mysqli_query($connection, $sqlStokKritis) or die("Query stok kritis gagal: ".mysqli_error($connection));
$jumlahStokKritis = mysqli_num_rows($resStokKritis);


// 4) Produk terlaris bulan ini (TOP 5) — berdasarkan OUT
$sqlProdukTerlaris = "
  SELECT 
    p.product_id,
    COALESCE(b.brand_name,'') AS brand_name,
    COALESCE(m.model_name,'') AS model_name,
    SUM(ABS(td.quantity)) AS total_keluar
  FROM transaction_details td
  JOIN `transaction` t ON td.transaction_id = t.transaction_id
  JOIN product p       ON td.product_id     = p.product_id
  LEFT JOIN brand b    ON p.brand_id        = b.brand_id
  LEFT JOIN model m    ON p.model_id        = m.model_id
  WHERE t.type = 'OUT' AND p.status = 1
    AND DATE_FORMAT(t.transaction_date, '%Y-%m') = '$bulanIni'
  GROUP BY p.product_id
  ORDER BY total_keluar DESC, brand_name ASC, model_name ASC
  LIMIT 5
";
$resProdukTerlaris = mysqli_query($connection, $sqlProdukTerlaris) or die("Query produk terlaris gagal: ".mysqli_error($connection));
?>

<!-- ================= CARD RINGKASAN ================= -->
<div class="row mb-4">

<div class="col-xl-3 col-md-6 mb-4">
  <div class="card bg-primary text-white mb-4 shadow-sm">
    <div class="card-body">

      <h5 class="mb-2">Total Produk</h5>

      <!-- angka + button satu baris -->
      <div class="d-flex align-items-center justify-content-between">
        <h2 class="mb-0"><?php echo $totalProduk; ?></h2>

 <a href="product.php" class="btn btn-sm btn-light text-primary fw-bold px-3 py-1" style="opacity:0.85;">
          <i class="fas fa-box-open me-1"></i> View
        </a>
      </div>

    </div>
  </div>
</div>

<div class="col-xl-3 col-md-6 mb-4">
  <div class="card bg-success text-white mb-4 shadow-sm">
    <div class="card-body">
      <h5 class="mb-2">Total Transaksi</h5>
      <!-- angka + button satu baris -->
      <div class="d-flex align-items-center justify-content-between">
        <h2 class="mb-0"><?php echo $totalTransaksi; ?></h2>

<a href="transaksi.php" class="btn btn-sm btn-light text-success fw-bold px-3 py-1" style="opacity:0.85;">
          <i class="fas fa-exchange-alt me-1"></i> View
        </a>
      </div>

    </div>
  </div>
</div>


  <div class="col-xl-3 col-md-6">
    <div class="card bg-danger text-white mb-4 shadow-sm">
      <div class="card-body">
<h5 class="mb-1">Produk Perlu Reorder (Stok ≤ ROP)</h5>
<h2 class="mb-0"><?php echo $jumlahStokKritis; ?></h2>

      </div>
    </div>
  </div>

</div>

<!-- ================= TABEL PRODUK TERLARIS ================= -->
<div class="card mb-4">
  <div class="card-header bg-success text-white">
    <i class="fas fa-fire me-1"></i> Produk Terlaris Bulan Ini
  </div>
  <div class="card-body">
    <table class="table table-bordered table-hover align-middle">
      <thead>
        <tr>
          <th style="width:60px;">No</th>
          <th>Produk</th>
          <th style="width:160px;">Total Keluar</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($resProdukTerlaris && mysqli_num_rows($resProdukTerlaris) > 0): ?>
          <?php $no=1; while($row = mysqli_fetch_assoc($resProdukTerlaris)): 
            $namaProduk = trim(($row['brand_name'] ?? '').' '.($row['model_name'] ?? ''));
            if ($namaProduk === '') $namaProduk = 'Produk #'.$row['product_id'];
          ?>
          <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo htmlspecialchars($namaProduk); ?></td>
            <td><?php echo (int)$row['total_keluar']; ?></td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="3" class="text-center text-muted">Belum ada data transaksi OUT bulan ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ================= TABEL STOK KRITIS ================= -->
<div class="card mb-4">
  <div class="card-header bg-danger text-white">
    <i class="fas fa-exclamation-triangle me-1"></i> Stok Kritis 
  </div>
  <div class="card-body">
    <table class="table table-bordered table-hover align-middle">
            <thead>
        <tr>
          <th style="width:60px;">No</th>
          <th>Produk</th>
          <th style="width:120px;">Total Stok</th>
          <th style="width:120px;">ROP</th>
          <th style="width:120px;">Safety Stock</th>
          <th style="width:160px;">Status</th>
          <th style="width:160px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($resStokKritis && mysqli_num_rows($resStokKritis) > 0): ?>
  <?php $no=1; while($row = mysqli_fetch_assoc($resStokKritis)):
    $namaProduk = trim(($row['brand_name'] ?? '').' '.($row['model_name'] ?? ''));
    if ($namaProduk === '') $namaProduk = 'Produk #'.$row['product_id'];

    $totalStok    = (int)$row['total_stok'];
    $rop          = (int)$row['reorder_point'];
    $safetyStock  = (int)$row['safety_stock'];

    // Tentukan status
    $statusText  = '';
    $statusClass = '';

    if ($totalStok <= $safetyStock) {
      $statusText  = 'Sangat Kritis (≤ Safety Stock)';
      $statusClass = 'badge bg-danger';
    } elseif ($totalStok <= $rop) {
      $statusText  = 'Perlu Reorder (≤ ROP)';
      $statusClass = 'badge bg-warning text-dark';
    }
  ?>
  <tr>
    <td><?php echo $no++; ?></td>
    <td><?php echo htmlspecialchars($namaProduk); ?></td>
    <td><?php echo $totalStok; ?></td>
    <td><?php echo $rop; ?></td>
    <td><?php echo $safetyStock; ?></td>
    <td>
      <?php if ($statusText): ?>
        <span class="<?php echo $statusClass; ?>">
          <?php echo htmlspecialchars($statusText); ?>
        </span>
      <?php endif; ?>
    </td>
    <td>
      <a href="supplier_info.php?id=<?php echo (int)$row['product_id']; ?>" 
         class="btn btn-sm btn-outline-primary">
        <i class="fas fa-info-circle"></i> Lihat Supplier
      </a>
    </td>
  </tr>
  <?php endwhile; ?>
<?php else: ?>
  <tr>
    <td colspan="7" class="text-center text-muted">
      Tidak ada produk yang mencapai ROP. Semua stok masih aman.
    </td>
  </tr>
<?php endif; ?>

      </tbody>
    </table>
  </div>
</div>


