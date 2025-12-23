<!DOCTYPE html>
<?php
// ======================================================
// (A) INCLUDE KONEKSI & GUARD LOGIN
// ======================================================
include "include/config.php";   // $connection (mysqli)
include "include/guard.php";    // cek sudah login

// ======================================================
// (B) BACA FILTER DARI QUERY STRING (dengan default)
// ======================================================
$today        = date('Y-m-d');
$firstOfMonth = date('Y-m-01');
$lastOfMonth  = date('Y-m-t');

$filterLoc   = (int)($_GET['loc'] ?? 0); // 0 = semua lokasi
$filterStart = $_GET['start'] ?? $firstOfMonth;
$filterEnd   = $_GET['end']   ?? $lastOfMonth;

// Sanitasi ringan
$filterStartSql = mysqli_real_escape_string($connection, $filterStart);
$filterEndSql   = mysqli_real_escape_string($connection, $filterEnd);

// ======================================================
// (C) DATA UNTUK DROPDOWN LOKASI (hanya aktif)
// ======================================================
$resLokasi = mysqli_query(
  $connection,
  "SELECT location_id, namalokasi
   FROM location
   WHERE status=1
   ORDER BY namalokasi"
);

// ======================================================
// (D) QUERY INTI LAPORAN (stok awal / masuk / keluar)
// ======================================================

$sql = "
SELECT
  p.product_id,
  l.location_id,
  CONCAT(COALESCE(b.brand_name,''),' ',COALESCE(m.model_name,'')) AS nama_produk,
  l.namalokasi,

  COALESCE(sa.stok_awal, 0) AS stok_awal,
  COALESCE(mi.masuk,     0) AS total_masuk,
  COALESCE(ke.keluar,    0) AS total_keluar

FROM product p
CROSS JOIN (
  SELECT location_id, namalokasi
  FROM location
  WHERE status = 1
) l
LEFT JOIN brand b  ON p.brand_id = b.brand_id
LEFT JOIN model m  ON p.model_id = m.model_id

/* ====== STOK AWAL (semua pergerakan sebelum start) ====== */
LEFT JOIN (
  SELECT product_id, location_id, SUM(qty) AS stok_awal
  FROM (
    -- Transaksi IN/OUT → qty bertanda (OUT harus tersimpan negatif di data)
    SELECT td.product_id, t.location_id,
           td.quantity AS qty,
           t.transaction_date AS tgl
    FROM transaction_details td
    JOIN `transaction` t ON t.transaction_id = td.transaction_id

    UNION ALL
    -- Transfer: sumber (keluar)
    SELECT d.product_id, d.source_location_id AS location_id,
           -d.quantity AS qty,
           DATE(s.created_at) AS tgl
    FROM transfer_stok_detail d
    JOIN transfer_stok s ON s.transfer_id = d.transfer_id

    UNION ALL
    -- Transfer: tujuan (masuk)
    SELECT d.product_id, d.target_location_id AS location_id,
           d.quantity AS qty,
           DATE(s.created_at) AS tgl
    FROM transfer_stok_detail d
    JOIN transfer_stok s ON s.transfer_id = d.transfer_id
  ) L
  WHERE L.tgl < '{$filterStartSql}'
  GROUP BY product_id, location_id
) sa ON sa.product_id = p.product_id AND sa.location_id = l.location_id

/* ====== MASUK periode (IN + transfer masuk) ====== */
LEFT JOIN (
  SELECT product_id, location_id, SUM(masuk) AS masuk
  FROM (
    -- IN periode
    SELECT td.product_id, t.location_id,
           CASE WHEN t.type='IN' THEN td.quantity ELSE 0 END AS masuk,
           t.transaction_date AS tgl
    FROM transaction_details td
    JOIN `transaction` t ON t.transaction_id = td.transaction_id

    UNION ALL
    -- Transfer masuk ke lokasi tujuan
    SELECT d.product_id, d.target_location_id,
           d.quantity AS masuk,
           DATE(s.created_at) AS tgl
    FROM transfer_stok_detail d
    JOIN transfer_stok s ON s.transfer_id = d.transfer_id
  ) M
  WHERE M.tgl >= '{$filterStartSql}' AND M.tgl <= '{$filterEndSql}'
  GROUP BY product_id, location_id
) mi ON mi.product_id = p.product_id AND mi.location_id = l.location_id

/* ====== KELUAR periode (OUT + transfer keluar) ====== */
LEFT JOIN (
  SELECT product_id, location_id, SUM(keluar) AS keluar
  FROM (
    -- OUT periode (pakai ABS karena quantity OUT disimpan negatif)
    SELECT td.product_id, t.location_id,
           CASE WHEN t.type='OUT' THEN ABS(td.quantity) ELSE 0 END AS keluar,
           t.transaction_date AS tgl
    FROM transaction_details td
    JOIN `transaction` t ON t.transaction_id = td.transaction_id

    UNION ALL
    -- Transfer keluar dari lokasi sumber
    SELECT d.product_id, d.source_location_id,
           d.quantity AS keluar,
           DATE(s.created_at) AS tgl
    FROM transfer_stok_detail d
    JOIN transfer_stok s ON s.transfer_id = d.transfer_id
  ) K
  WHERE K.tgl >= '{$filterStartSql}' AND K.tgl <= '{$filterEndSql}'
  GROUP BY product_id, location_id
) ke ON ke.product_id = p.product_id AND ke.location_id = l.location_id

WHERE p.status = 1
";

if ($filterLoc > 0) {
  $sql .= " AND l.location_id = {$filterLoc} ";
}

$sql .= "
-- Hindari baris kosong total (opsional)
AND (
     COALESCE(sa.stok_awal,0) <> 0
  OR COALESCE(mi.masuk,0)     <> 0
  OR COALESCE(ke.keluar,0)    <> 0
)
ORDER BY nama_produk, l.namalokasi
";

$resReport = mysqli_query($connection, $sql) or die('Query laporan stok error: '.mysqli_error($connection));

// ======================================================
// (E) RINGKASAN TOTAL DI ATAS TABEL
// ======================================================
$totalMasuk  = 0;
$totalKeluar = 0;
$totalAkhir  = 0;

// Tarik semua ke array
$rows = [];
while ($r = mysqli_fetch_assoc($resReport)) {
  $r['stok_awal']    = (int)$r['stok_awal'];
  $r['total_masuk']  = (int)$r['total_masuk'];
  $r['total_keluar'] = (int)$r['total_keluar'];
  $r['stok_akhir']   = $r['stok_awal'] + $r['total_masuk'] - $r['total_keluar'];

  $totalMasuk  += $r['total_masuk'];
  $totalKeluar += $r['total_keluar'];
  $totalAkhir  += $r['stok_akhir'];
  $rows[] = $r;
}

// Ambang kritis (kalau mau dipakai nanti)
$AMBANG_KRITIS = 10;

// Info cetak
$printedAt   = date('d M Y H:i');
$periodeText = date('d M Y', strtotime($filterStart)) . " s.d " . date('d M Y', strtotime($filterEnd));

// lokasi penyimpanan
if ($filterLoc > 0) {
  $locRes = mysqli_query(
    $connection,
    "SELECT namalokasi FROM location WHERE location_id={$filterLoc} LIMIT 1"
  );
  $locRow      = mysqli_fetch_assoc($locRes);
  $lokasiText  = $locRow ? $locRow['namalokasi'] : 'Lokasi tidak ditemukan';
} else {
  $lokasiText = 'Semua Lokasi';
}

?>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Laporan Stok - PBS Inventory</title>
  <link href="css/styles.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
  <style>
    @media print {
      .no-print { display:none !important; }
    }
  </style>
</head>
<body class="sb-nav-fixed">
<?php include "include/navbar.php"; ?>
<div id="layoutSidenav">
<?php include "include/menu.php"; ?>
<div id="layoutSidenav_content">
<main class="container-fluid px-4">

  <h1 class="mt-4">Laporan Stok</h1>
  <ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Laporan Stok</li>
  </ol>

  <!-- ===================== FILTER BAR ===================== -->
  <div class="card mb-3 no-print">
    <div class="card-body">
      <form method="get" class="row g-2">
        <!-- Lokasi -->
        <div class="col-md-4">
          <label class="form-label">Lokasi</label>
          <select name="loc" class="form-select">
            <option value="0">Semua Lokasi</option>
            <?php mysqli_data_seek($resLokasi, 0); while($l = mysqli_fetch_assoc($resLokasi)): ?>
              <option value="<?php echo (int)$l['location_id']; ?>"
                <?php echo ($filterLoc == (int)$l['location_id'] ? 'selected' : ''); ?>>
                <?php echo htmlspecialchars($l['namalokasi']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <!-- Periode -->
        <div class="col-md-4">
          <label class="form-label">Tanggal Awal</label>
          <input type="date" name="start" value="<?php echo htmlspecialchars($filterStart); ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Tanggal Akhir</label>
          <input type="date" name="end" value="<?php echo htmlspecialchars($filterEnd); ?>" class="form-control">
        </div>

        <div class="col-12 d-flex gap-2 mt-2">
          <button class="btn btn-secondary"><i class="fa fa-filter"></i> Terapkan</button>
          <a class="btn btn-light" href="laporan.php"><i class="fa fa-rotate-left"></i> Reset</a>
          <button type="button" class="btn btn-outline-primary ms-auto" onclick="window.print()">
            <i class="fa fa-print"></i> Cetak
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ===================== RINGKASAN ===================== -->
  <div class="row mb-3">
    <div class="col-xl-4 col-md-6">
      <div class="card bg-light shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h6 class="mb-1">Total Barang Masuk</h6>
              <h3 class="mb-0"><?php echo number_format($totalMasuk, 0, ',', '.'); ?></h3>
            </div>
            <div class="align-self-center"><i class="fa fa-arrow-down text-success fa-2x"></i></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-4 col-md-6">
      <div class="card bg-light shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h6 class="mb-1">Total Barang Keluar</h6>
              <h3 class="mb-0"><?php echo number_format($totalKeluar, 0, ',', '.'); ?></h3>
            </div>
            <div class="align-self-center"><i class="fa fa-arrow-up text-danger fa-2x"></i></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-4 col-md-12">
      <div class="card bg-light shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h6 class="mb-1">Total Stok Akhir (per filter)</h6>
              <h3 class="mb-0"><?php echo number_format($totalAkhir, 0, ',', '.'); ?></h3>
            </div>
            <div class="align-self-center"><i class="fa fa-boxes-stacked text-primary fa-2x"></i></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <p><strong>Periode Laporan:</strong> <?php echo htmlspecialchars($periodeText); ?></p>
  <p><strong>Lokasi:</strong> <?php echo htmlspecialchars($lokasiText); ?></p>
  <p class="text-muted">Dicetak pada: <?php echo htmlspecialchars($printedAt); ?></p>
  

  <!-- ===================== TABEL LAPORAN ===================== -->
  <div class="card mb-4">
    <div class="card-header bg-secondary text-white">
      <i class="fa fa-clipboard-list me-1"></i> Laporan Stok Per Produk & Lokasi
    </div>
    <div class="card-body">
      <table id="datatablesSimple" class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>Produk</th>
            <th>Lokasi</th>
            <th>Stok Awal</th>
            <th>Masuk</th>
            <th>Keluar</th>
            <th>Stok Akhir</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($rows) === 0): ?>
            <tr><td colspan="8" class="text-center text-muted">Tidak ada data untuk filter ini.</td></tr>
          <?php else: $no=1; foreach ($rows as $r): ?>
            <?php
            if ($r['stok_akhir'] <= 0) {
              $statusBadge = '<span class="badge bg-danger">Habis</span>';
            } else {
              $statusBadge = '<span class="badge bg-success">Ada Stok</span>';
            }
            ?>
            <tr>
              <td><?php echo $no++; ?></td>
              <td><?php echo htmlspecialchars($r['nama_produk']); ?></td>
              <td><?php echo htmlspecialchars($r['namalokasi']); ?></td>
              <td><?php echo (int)$r['stok_awal']; ?></td>
              <td><?php echo (int)$r['total_masuk']; ?></td>
              <td><?php echo (int)$r['total_keluar']; ?></td>
              <td><?php echo (int)$r['stok_akhir']; ?></td>
              <td><?php echo $statusBadge; ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>
<?php include "include/footer.php"; ?>
</div>
</div>

<!-- ===================== SCRIPT ===================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script src="js/datatables-simple-demo.js"></script>
</body>
</html>
