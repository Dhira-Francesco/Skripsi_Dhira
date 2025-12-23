<!DOCTYPE html>
<?php
// ======================================================
// (A) INCLUDE KONFIG & CEK LOGIN
// ======================================================
include "include/config.php";
include "include/guard.php";

// Set zona waktu biar preset tanggal akurat (Asia/Jakarta)
date_default_timezone_set('Asia/Jakarta');

// ======================================================
// (B) AMBIL FILTER DARI QUERY STRING
//    - type: IN | OUT | ADJUST | '' (semua)
//    - loc : location_id | '' (semua)
//    - start/end: YYYY-MM-DD
//    - prod: cari nama produk (LIKE)
//    - preset: today|week|month -> auto set start/end
// ======================================================
$filterType   = $_GET['type']  ?? '';
$filterLoc    = $_GET['loc']   ?? '';
$filterStart  = $_GET['start'] ?? '';
$filterEnd    = $_GET['end']   ?? '';
$filterProd   = $_GET['prod']  ?? '';
$presetRange  = $_GET['preset'] ?? '';
$aksi         = $_GET['action'] ?? ''; // 'export' untuk export CSV

// ---------- Hitung preset tanggal jika diminta ----------
if ($presetRange !== '') {
    $today = new DateTime('today'); // 00:00 hari ini
    if ($presetRange === 'today') {
        $filterStart = $today->format('Y-m-d');
        $filterEnd   = $today->format('Y-m-d');
    } elseif ($presetRange === 'week') {
        // Minggu ini (Senin - Minggu)
        $monday = clone $today;
        $monday->modify('monday this week');
        $sunday = clone $monday;
        $sunday->modify('sunday this week');
        $filterStart = $monday->format('Y-m-d');
        $filterEnd   = $sunday->format('Y-m-d');
    } elseif ($presetRange === 'month') {
        $first = new DateTime('first day of this month');
        $last  = new DateTime('last day of this month');
        $filterStart = $first->format('Y-m-d');
        $filterEnd   = $last->format('Y-m-d');
    }
}

// ======================================================
// (C) BANGUN WHERE CLAUSE UNTUK SEMUA QUERY
// ======================================================
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
if ($filterProd !== '') {
    $where .= " AND p.name LIKE '%".mysqli_real_escape_string($connection,$filterProd)."%'";
}

// ======================================================
// (D) QUERY UTAMA (PER-DETAIL)
// ======================================================
$sqlDaftarTransaksi = "
SELECT 
  t.transaction_id,
  t.transaction_date,
  t.type,
  t.note,
  l.namalokasi,
  u.username,
  p.name AS nama_produk,
  td.quantity        AS qty_transaksi,     -- +IN, -OUT, +/- ADJUST
  td.quantity_before AS qty_awal,
  td.quantity_after  AS qty_akhir,
  COALESCE(s.namasupplier, '-') AS supplier_name,
  si.invoice
FROM `transaction` t
JOIN transaction_details td ON td.transaction_id = t.transaction_id
JOIN product p              ON td.product_id     = p.product_id
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
// (E) RINGKASAN (TOTAL IN/OUT/ADJUST/NET) BERDASARKAN FILTER
// ======================================================
$sqlRingkasan = "
SELECT
  SUM(CASE WHEN t.type='IN'     THEN td.quantity ELSE 0 END)                AS total_in,
  SUM(CASE WHEN t.type='OUT'    THEN -td.quantity ELSE 0 END)               AS total_out,   -- quantity OUT negatif, balik jadi positif
  SUM(CASE WHEN t.type='ADJUST' THEN td.quantity ELSE 0 END)                AS total_adjust_delta,
  SUM(td.quantity)                                                            AS total_net
FROM `transaction` t
JOIN transaction_details td ON td.transaction_id = t.transaction_id
JOIN product p              ON td.product_id     = p.product_id
WHERE $where
";
$resultRingkasan = mysqli_query($connection, $sqlRingkasan) or die("Query ringkasan error: " . mysqli_error($connection));
$dataRingkasan   = mysqli_fetch_assoc($resultRingkasan);
$totalIn     = (int)($dataRingkasan['total_in'] ?? 0);
$totalOut    = (int)($dataRingkasan['total_out'] ?? 0);
$totalAdjust = (int)($dataRingkasan['total_adjust_delta'] ?? 0);
$totalNet    = (int)($dataRingkasan['total_net'] ?? 0);

// ======================================================
// (F) EXPORT CSV (MENGGUNAKAN FILTER YANG SAMA)
// ======================================================
if ($aksi === 'export') {
    // Header CSV
    header('Content-Type: text/csv; charset=utf-8');
    $namaFile = 'transaksi_detail_'.date('Ymd_His').'.csv';
    header('Content-Disposition: attachment; filename='.$namaFile);

    $output = fopen('php://output', 'w');

    // Baris judul filter (opsional)
    fputcsv($output, ['Filter',
        'Tipe='.$filterType,
        'Lokasi='.$filterLoc,
        'Tanggal='.$filterStart.' s/d '.$filterEnd,
        'Produk LIKE='.$filterProd
    ]);
    // Header kolom
    fputcsv($output, [
        'Tanggal','Tipe','Produk','Lokasi','Qty Transaksi','Qty Awal','Qty Akhir','Supplier','Invoice','Note','User','Transaction ID'
    ]);

    // Ambil ulang result (pakai query yang sama)
    $resExport = mysqli_query($connection, $sqlDaftarTransaksi);

    while ($r = mysqli_fetch_assoc($resExport)) {
        $qty = (int)$r['qty_transaksi'];
        $tipe = $r['type'];
        fputcsv($output, [
            date('Y-m-d H:i', strtotime($r['transaction_date'])),
            $tipe,
            $r['nama_produk'],
            $r['namalokasi'],
            ($qty>0?'+':'').$qty,
            (int)$r['qty_awal'],
            (int)$r['qty_akhir'],
            $r['supplier_name'] ?: '-',
            $r['invoice'] ?: '-',
            $r['note'],
            $r['username'],
            $r['transaction_id']
        ]);
    }

    // Akhiri output
    fclose($output);
    exit;
}

// ======================================================
// (G) DATA UNTUK FILTER DROPDOWN LOKASI
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

    <!-- ==================== RINGKASAN (KPI) ==================== -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total IN</div>
                    <div class="fs-4 fw-bold text-success"><?= number_format($totalIn,0,',','.') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total OUT</div>
                    <div class="fs-4 fw-bold text-danger"><?= number_format($totalOut,0,',','.') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">ADJUST (Δ)</div>
                    <div class="fs-4 fw-bold text-warning"><?= number_format($totalAdjust,0,',','.') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">NET (IN + OUT + ADJ)</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalNet,0,',','.') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Tambah + Export -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="transaction_add.php" class="btn btn-primary">
                <i class="fa fa-plus"></i> Tambah Transaksi
            </a>
        </div>
        <div>
            <!-- Export CSV mempertahankan filter aktif -->
            <a class="btn btn-outline-secondary"
               href="transaction.php?<?= http_build_query(['type'=>$filterType,'loc'=>$filterLoc,'start'=>$filterStart,'end'=>$filterEnd,'prod'=>$filterProd,'action'=>'export']) ?>">
               <i class="fa fa-file-csv"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- ==================== FILTER + PRESET ==================== -->
    <form method="get" class="row g-2 mb-3">
        <!-- Tipe -->
        <div class="col-md-2">
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
        <!-- Rentang Tanggal -->
        <div class="col-md-2"><input type="date" name="start" value="<?= htmlspecialchars($filterStart) ?>" class="form-control"></div>
        <div class="col-md-2"><input type="date" name="end"   value="<?= htmlspecialchars($filterEnd)   ?>" class="form-control"></div>
        <!-- Cari Produk -->
        <div class="col-md-2">
            <input type="text" name="prod" class="form-control" placeholder="Cari produk..." value="<?= htmlspecialchars($filterProd) ?>">
        </div>
        <!-- Tombol apply/reset -->
        <div class="col-md-1 d-grid">
            <button type="submit" class="btn btn-secondary">Filter</button>
        </div>
        <div class="col-md-1 d-grid">
            <a href="transaction.php" class="btn btn-light">Reset</a>
        </div>

        <!-- Preset cepat -->
        <div class="col-12 mt-2">
            <?php
              // helper buat build URL preset sambil bawa filter lain (type, loc, prod)
              function url_preset($preset, $type, $loc, $prod){
                return 'transaction.php?' . http_build_query([
                  'preset'=>$preset,
                  'type'=>$type,
                  'loc'=>$loc,
                  'prod'=>$prod
                ]);
              }
            ?>
            <div class="btn-group" role="group" aria-label="Preset">
                <a href="<?= url_preset('today',$filterType,$filterLoc,$filterProd) ?>" class="btn btn-outline-primary btn-sm">Hari ini</a>
                <a href="<?= url_preset('week',$filterType,$filterLoc,$filterProd)  ?>" class="btn btn-outline-primary btn-sm">Minggu ini</a>
                <a href="<?= url_preset('month',$filterType,$filterLoc,$filterProd) ?>" class="btn btn-outline-primary btn-sm">Bulan ini</a>
            </div>
            <small class="text-muted ms-2">
                <?php if($filterStart && $filterEnd): ?>
                    Rentang aktif: <?= htmlspecialchars($filterStart) ?> s/d <?= htmlspecialchars($filterEnd) ?>
                <?php else: ?>
                    Tidak ada rentang tanggal (menampilkan semua)
                <?php endif; ?>
            </small>
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
                    <?php $nomorBaris=1; while($row = mysqli_fetch_assoc($resultDaftarTransaksi)): ?>
                    <tr>
                        <td><?= $nomorBaris++; ?></td>
                        <td><?= date('d-m-Y H:i', strtotime($row['transaction_date'])) ?></td>
                        <td>
                            <?php if($row['type']==='IN'): ?>
                                <span class="badge bg-success">IN</span>
                            <?php elseif($row['type']==='OUT'): ?>
                                <span class="badge bg-danger">OUT</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">ADJUST</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['nama_produk']); ?></td>
                        <td><?= htmlspecialchars($row['namalokasi']); ?></td>
                        <td>
                            <?php $qty=(int)$row['qty_transaksi']; echo ($qty>0?'+':'').$qty; ?>
                        </td>
                        <td><?= (int)$row['qty_awal'];  ?></td>
                        <td><?= (int)$row['qty_akhir']; ?></td>
                        <td><?= htmlspecialchars($row['supplier_name'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($row['invoice'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($row['note']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
</div>

<!-- Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script src="js/datatables-simple-demo.js"></script>
</body>
</html>
