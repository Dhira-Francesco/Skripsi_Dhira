<!DOCTYPE html>
<?php
// ======================================================
// (A) INCLUDE KONFIG & CEK LOGIN
// ======================================================
include "include/config.php";
include "include/guard.php";

// ======================================================
// (B) AMBIL FILTER DARI QUERY STRING (opsional)
// ======================================================
$filterType   = $_GET['type']  ?? '';   // IN | OUT | ADJUST | '' (semua)
$filterLoc    = $_GET['loc']   ?? '';   // location_id
$filterStart  = $_GET['start'] ?? '';   // YYYY-MM-DD
$filterEnd    = $_GET['end']   ?? '';   // YYYY-MM-DD

$where = "1=1";
if ($filterType !== '') {
    $where .= " AND t.type = '".mysqli_real_escape_string($connection, $filterType)."'";
}
if ($filterLoc !== '') {
    $where .= " AND t.location_id = ".(int)$filterLoc;
}
if ($filterStart !== '' && $filterEnd !== '') {
    $where .= " AND DATE(t.transaction_date) BETWEEN '".
              mysqli_real_escape_string($connection,$filterStart)."' AND '".
              mysqli_real_escape_string($connection,$filterEnd)."'";
}


// ======================================================
// (C) QUERY UTAMA: SETIAP BARIS = 1 DETAIL TRANSAKSI
// - Join transaction + transaction_details + product + location
// - Left join transaction_stok_in + supplier (hanya untuk IN)
// - Sertakan user pembuat
// ======================================================
$sqlDaftarTransaksi = "
SELECT 
  t.transaction_id,
  t.transaction_date,
  t.type,
  t.note,
  l.namalokasi,
  u.username,
  CONCAT(b.brand_name,' ',m.model_name) AS nama_produk,
  td.quantity        AS qty_transaksi,     -- positif utk IN, negatif utk OUT, delta utk ADJ
  td.quantity_before AS qty_awal,
  td.quantity_after  AS qty_akhir,
  COALESCE(s.namasupplier, '-') AS supplier_name,
  si.invoice
FROM `transaction` t
JOIN transaction_details td ON td.transaction_id = t.transaction_id
JOIN product p              ON td.product_id     = p.product_id
LEFT JOIN brand b ON p.brand_id=b.brand_id
LEFT JOIN model m ON p.model_id=m.model_id
JOIN location l             ON t.location_id     = l.location_id
LEFT JOIN transaction_stok_in si ON si.transaction_id = t.transaction_id
LEFT JOIN supplier s            ON si.supplier_id    = s.supplier_id
LEFT JOIN `user` u              ON t.user_id         = u.user_id
WHERE $where
ORDER BY t.transaction_date DESC, t.transaction_id DESC, p.name ASC
";
$resultDaftarTransaksi = mysqli_query($connection, $sqlDaftarTransaksi) 
    or die("Query transaksi error: " . mysqli_error($connection));

// ======================================================
// (D) DATA UNTUK FILTER DROPDOWN LOKASI
// ======================================================
$resultLokasi = mysqli_query($connection, "SELECT location_id, namalokasi FROM location WHERE status=1 ORDER BY namalokasi");
?>
<html lang="id">
<head>
<meta charset="utf-8" />
<title>Transaksi Stok - PBS Inventory</title>
<link href="css/styles.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
</head>
<body class="sb-nav-fixed">
<?php include "include/navbar.php"; ?>
<div id="layoutSidenav">
<?php include "include/menu.php"; ?>
<div id="layoutSidenav_content">
<main class="container-fluid px-4">
    <h1 class="mt-4">Transaksi Stok</h1>
    <ol class="breadcrumb mb-4"><li class="breadcrumb-item active">Daftar (Detail per baris)</li></ol>

    <!-- Notifikasi sukses setelah add -->
    <?php if(isset($_GET['msg']) && $_GET['msg']=='add'): ?>
      <div class="alert alert-success">Transaksi berhasil disimpan!</div>
    <?php endif; ?>

    <!-- Tombol Tambah Transaksi -->
    <div class="mb-3 text-end">
        <a href="tambahtransaksi.php" class="btn btn-primary">
            <i class="fa fa-plus"></i> Tambah Transaksi
        </a>
    </div>

    <!-- ==================== FILTER ==================== -->
    <form method="get" class="row g-2 mb-3">
        <!-- Tipe -->
        <div class="col-md-3">
            <select name="type" class="form-select">
                <option value="">Semua Tipe</option>
                <option value="IN"     <?= $filterType==='IN'?'selected':'' ?>>IN</option>
                <option value="OUT"    <?= $filterType==='OUT'?'selected':'' ?>>OUT</option>
                <option value="ADJUST" <?= $filterType==='ADJUST'?'selected':'' ?>>ADJUST</option>
            </select>
        </div>
        <!-- Lokasi -->
        <div class="col-md-3">
            <select name="loc" class="form-select">
                <option value="">Semua Lokasi</option>
                <?php while($lok=mysqli_fetch_assoc($resultLokasi)): ?>
                  <option value="<?= $lok['location_id'] ?>" <?= ($filterLoc==$lok['location_id'])?'selected':'' ?>>
                    <?= htmlspecialchars($lok['namalokasi']) ?>
                  </option>
                <?php endwhile; ?>
            </select>
        </div>
        <!-- Rentang tanggal -->
        <div class="col-md-2"><input type="date" name="start" value="<?= htmlspecialchars($filterStart) ?>" class="form-control"></div>
        <div class="col-md-2"><input type="date" name="end"   value="<?= htmlspecialchars($filterEnd)   ?>" class="form-control"></div>
        <!-- Cari produk -->
        <!-- Tombol -->
        <div class="col-md-1 d-grid">
            <button type="submit" class="btn btn-secondary">Filter</button>
        </div>
        <div class="col-md-1 d-grid">
            <a href="transaksi.php" class="btn btn-light">Reset</a>
        </div>
    </form>

    <!-- ==================== TABEL TRANSAKSI (DETAIL) ==================== -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <i class="fa fa-list"></i> Daftar Transaksi (Per-Detail)
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Produk</th>
                        <th>Lokasi</th>
                        <th>Qty Transaksi</th>
                        <th>Qty Awal</th>
                        <th>Qty Akhir</th>
                        <th>Supplier</th>
                        <th>Invoice</th>
                        <th>Note</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while($row = mysqli_fetch_assoc($resultDaftarTransaksi)): ?>
                    <tr>
                        <!-- No -->
                        <td><?= $no++; ?></td>

                        <!-- Tanggal -->
                        <td><?= date('d-m-Y H:i', strtotime($row['transaction_date'])) ?></td>

                        <!-- Tipe + badge warna -->
                        <td>
                            <?php if($row['type']==='IN'): ?>
                                <span class="badge bg-success">IN</span>
                            <?php elseif($row['type']==='OUT'): ?>
                                <span class="badge bg-danger">OUT</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">ADJUST</span>
                            <?php endif; ?>
                        </td>

                        <!-- Produk & Lokasi -->
                        <td><?= htmlspecialchars($row['nama_produk']); ?></td>
                        <td><?= htmlspecialchars($row['namalokasi']); ?></td>

                        <!-- Qty transaksi: tampilkan + untuk positif -->
                        <td>
                            <?php 
                              $qty = (int)$row['qty_transaksi'];
                              echo ($qty > 0 ? '+' : '') . $qty;
                            ?>
                        </td>

                        <!-- Qty awal & qty akhir -->
                        <td><?= (int)$row['qty_awal'];  ?></td>
                        <td><?= (int)$row['qty_akhir']; ?></td>

                        <!-- Supplier & Invoice (kalau bukan IN, biasanya '-') -->
                        <td><?= htmlspecialchars($row['supplier_name'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($row['invoice'] ?: '-') ?></td>

                        <!-- Note & User -->
                        <td><?= htmlspecialchars($row['note']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
   <?php include "include/footer.php"; ?>

</div>
</div>

<!-- Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script src="js/datatables-simple-demo.js"></script>
</body>
</html>
