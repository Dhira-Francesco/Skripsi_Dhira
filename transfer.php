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
$filtersourceLoc  = $_GET['locawal']   ?? '';   // location_id sumber
$filtertargetLoc  = $_GET['loctarget'] ?? '';   // location_id target
$filterStart      = $_GET['start']     ?? '';   // YYYY-MM-DD
$filterEnd        = $_GET['end']       ?? '';   // YYYY-MM-DD


// Bangun WHERE sesuai filter
$where = "1=1";
if ($filtersourceLoc !== '') {
    $where .= " AND tr.source_location_id = " . (int)$filtersourceLoc;
}
if ($filtertargetLoc !== '') {
    $where .= " AND tr.target_location_id = " . (int)$filtertargetLoc;
}
if ($filterStart !== '' && $filterEnd !== '') {
    $where .= " AND DATE(tr.created_at) BETWEEN '".
              mysqli_real_escape_string($connection, $filterStart)."' AND '".
              mysqli_real_escape_string($connection, $filterEnd)."'";
}

// ======================================================
// (C) QUERY UTAMA (PER-DETAIL TRANSFER)
// - Join transfer_stok + transfer_stok_detail + product
// - Join 2x lokasi: source & target
// - Join user pembuat
// ======================================================
$sqlDaftarTransfer = "
SELECT 
  tr.transfer_id,
  tr.created_at,
  tr.note,
  u.username,
  CONCAT(b.brand_name,' ',m.model_name) AS nama_produk,

  -- lokasi asal & tujuan
  lsrc.namalokasi AS lokasi_sumber,
  ltgt.namalokasi AS lokasi_tujuan,

  -- qty per lokasi
  td.quantity        AS qty_transaksi,      -- jumlah yang dipindahkan
  td.src_quantitybef AS qty_awal_sumber,
  td.src_quantityaft AS qty_akhir_sumber,
  td.tgt_quantitybef AS qty_awal_tujuan,
  td.tgt_quantityaft AS qty_akhir_tujuan

FROM transfer_stok tr
JOIN transfer_stok_detail td   ON tr.transfer_id        = td.transfer_id
JOIN product p                 ON td.product_id         = p.product_id
LEFT JOIN brand b ON p.brand_id=b.brand_id
LEFT JOIN model m ON p.model_id=m.model_id
JOIN location lsrc             ON td.source_location_id = lsrc.location_id
JOIN location ltgt             ON td.target_location_id = ltgt.location_id
LEFT JOIN `user` u             ON tr.user_id            = u.user_id
WHERE $where
ORDER BY tr.created_at DESC, tr.transfer_id DESC, p.name ASC
";
$resultDaftarTransfer = mysqli_query($connection, $sqlDaftarTransfer)
    or die("Query transfer error: " . mysqli_error($connection));

// ======================================================
// (D) DATA UNTUK FILTER DROPDOWN LOKASI (PAKAI ARRAY SUPAYA BISA DIPAKAI 2X)
// ======================================================
$resultLokasi = mysqli_query($connection, "SELECT location_id, namalokasi FROM location WHERE status=1 ORDER BY namalokasi");
$listLokasi = [];
while ($row = mysqli_fetch_assoc($resultLokasi)) {
    $listLokasi[] = $row;
}
?>
<html lang="id">
<head>
<meta charset="utf-8" />
<title>Transfer Stok - PBS Inventory</title>
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
    <h1 class="mt-4">Transfer Stok</h1>
    <ol class="breadcrumb mb-4"><li class="breadcrumb-item active">Daftar (Detail per baris)</li></ol>

    <!-- Notifikasi sukses setelah add -->
    <?php if(isset($_GET['msg']) && $_GET['msg']=='add'): ?>
      <div class="alert alert-success">Transfer berhasil disimpan!</div>
    <?php endif; ?>

    <!-- Tombol Tambah Transfer -->
    <div class="mb-3 text-end">
        <a href="tambahtransfer.php" class="btn btn-primary">
            <i class="fa fa-plus"></i> Tambah Transfer
        </a>
    </div>

    <!-- ==================== FILTER ==================== -->
    <form method="get" class="row g-2 mb-3">
        <!-- Lokasi Sumber -->
        <div class="col-md-3">
            <select name="locawal" class="form-select">
                <option value="">Lokasi Sumber (semua)</option>
                <?php foreach($listLokasi as $lok): ?>
                  <option value="<?= $lok['location_id'] ?>"
                    <?= ($filtersourceLoc !== '' && (int)$filtersourceLoc === (int)$lok['location_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($lok['namalokasi']) ?>
                  </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Lokasi Tujuan -->
        <div class="col-md-3">
            <select name="loctarget" class="form-select">
                <option value="">Lokasi Tujuan (semua)</option>
                <?php foreach($listLokasi as $lok): ?>
                  <option value="<?= $lok['location_id'] ?>"
                    <?= ($filtertargetLoc !== '' && (int)$filtertargetLoc === (int)$lok['location_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($lok['namalokasi']) ?>
                  </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Rentang tanggal -->
        <div class="col-md-2">
            <input type="date" name="start" value="<?= htmlspecialchars($filterStart) ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <input type="date" name="end" value="<?= htmlspecialchars($filterEnd) ?>" class="form-control">
        </div>

    

        <!-- Tombol apply/reset -->
        <div class="col-md-1 d-grid">
            <button type="submit" class="btn btn-secondary">Filter</button>
        </div>
        <div class="col-md-1 d-grid">
            <a href="transfer.php" class="btn btn-light">Reset</a>
        </div>
    </form>

    <!-- ==================== TABEL TRANSFER (DETAIL) ==================== -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <i class="fa fa-list"></i> Daftar Transfer (Per-Detail)
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Lokasi Sumber</th>
                        <th>Lokasi Tujuan</th>
                        <th>Qty Transfer</th>
                        <th>Qty Awal (Sumber)</th>
                        <th>Qty Akhir (Sumber)</th>
                        <th>Qty Awal (Tujuan)</th>
                        <th>Qty Akhir (Tujuan)</th>
                        <th>Catatan</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while($row = mysqli_fetch_assoc($resultDaftarTransfer)): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= date('d-m-Y H:i', strtotime($row['created_at'])) ?></td>
                        <td><?= htmlspecialchars($row['nama_produk']); ?></td>
                        <td><?= htmlspecialchars($row['lokasi_sumber']); ?></td>
                        <td><?= htmlspecialchars($row['lokasi_tujuan']); ?></td>
                        <td><?= (int)$row['qty_transaksi']; ?></td>
                        <td><?= (int)$row['qty_awal_sumber'];  ?></td>
                        <td><?= (int)$row['qty_akhir_sumber']; ?></td>
                        <td><?= (int)$row['qty_awal_tujuan'];  ?></td>
                        <td><?= (int)$row['qty_akhir_tujuan']; ?></td>
                        <td><?= htmlspecialchars($row['note']); ?></td>
                        <td><?= htmlspecialchars($row['username']); ?></td>
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
