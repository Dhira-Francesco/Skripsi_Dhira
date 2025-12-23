<?PHP
// =======================================================
// (E) RINGKASAN LAPORAN STOK - 5 STOK TERENDAH PER LOKASI
// =======================================================
$startPeriode = date('Y-m-01');   // awal bulan ini
$endPeriode   = date('Y-m-t');    // akhir bulan ini

$sqlMiniLaporan = "
SELECT
  p.product_id,
  l.location_id,
  CONCAT(COALESCE(b.brand_name,''),' ',COALESCE(m.model_name,'')) AS nama_produk,
  l.namalokasi,

  COALESCE(sa.stok_awal, 0) AS stok_awal,
  COALESCE(mi.masuk,     0) AS total_masuk,
  COALESCE(ke.keluar,    0) AS total_keluar,
  (COALESCE(sa.stok_awal, 0) + COALESCE(mi.masuk, 0) - COALESCE(ke.keluar, 0)) AS stok_akhir

FROM product p
CROSS JOIN (
  SELECT location_id, namalokasi
  FROM location
  WHERE status = 1
) l
LEFT JOIN brand b  ON p.brand_id = b.brand_id
LEFT JOIN model m  ON p.model_id = m.model_id

/* ====== STOK AWAL (semua pergerakan sebelum startPeriode) ====== */
LEFT JOIN (
  SELECT product_id, location_id, SUM(qty) AS stok_awal
  FROM (
    -- Transaksi IN/OUT → qty bertanda (OUT disimpan negatif)
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
  WHERE L.tgl < '{$startPeriode}'
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
    SELECT d.product_id, d.target_location_id AS location_id,
           d.quantity AS masuk,
           DATE(s.created_at) AS tgl
    FROM transfer_stok_detail d
    JOIN transfer_stok s ON s.transfer_id = d.transfer_id
  ) M
  WHERE M.tgl >= '{$startPeriode}' AND M.tgl <= '{$endPeriode}'
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
    SELECT d.product_id, d.source_location_id AS location_id,
           d.quantity AS keluar,
           DATE(s.created_at) AS tgl
    FROM transfer_stok_detail d
    JOIN transfer_stok s ON s.transfer_id = d.transfer_id
  ) K
  WHERE K.tgl >= '{$startPeriode}' AND K.tgl <= '{$endPeriode}'
  GROUP BY product_id, location_id
) ke ON ke.product_id = p.product_id AND ke.location_id = l.location_id

WHERE p.status = 1
  -- jangan tampilkan baris yang benar-benar tidak ada pergerakan sama sekali
  AND (
       COALESCE(sa.stok_awal,0) <> 0
    OR COALESCE(mi.masuk,0)     <> 0
    OR COALESCE(ke.keluar,0)    <> 0
  )

ORDER BY stok_akhir ASC, nama_produk ASC, l.namalokasi ASC
LIMIT 5
";

$resMiniLaporan = mysqli_query($connection, $sqlMiniLaporan)
  or die('Query ringkasan laporan stok error: '.mysqli_error($connection));
?>


<!-- ================= RINGKASAN LAPORAN STOK (5 TERENDAH) ================= -->
<div class="card mb-4">
  <div class="card-header bg-secondary text-white">
    <i class="fa fa-clipboard-list me-1"></i> Ringkasan Laporan Stok (5 Stok Terendah per Lokasi - Bulan Ini)
  </div>
  <div class="card-body">
    <table class="table table-bordered table-hover align-middle mb-3">
      <thead class="table-light">
        <tr>
          <th style="width:60px;">No</th>
          <th>Produk</th>
          <th>Lokasi</th>
          <th style="width:100px;">Stok Awal</th>
          <th style="width:100px;">Masuk</th>
          <th style="width:100px;">Keluar</th>
          <th style="width:100px;">Stok Akhir</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($resMiniLaporan && mysqli_num_rows($resMiniLaporan) > 0): ?>
          <?php $no = 1; while($row = mysqli_fetch_assoc($resMiniLaporan)): ?>
            <tr>
              <td><?php echo $no++; ?></td>
              <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
              <td><?php echo htmlspecialchars($row['namalokasi']); ?></td>
              <td><?php echo (int)$row['stok_awal']; ?></td>
              <td><?php echo (int)$row['total_masuk']; ?></td>
              <td><?php echo (int)$row['total_keluar']; ?></td>
              <td><?php echo (int)$row['stok_akhir']; ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center text-muted">
              Belum ada data mutasi stok untuk periode bulan ini.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <a href="laporan.php" class="btn btn-outline-primary btn-sm">
      <i class="fa fa-arrow-right"></i> Lihat Laporan Lengkap
    </a>
  </div>
</div>
