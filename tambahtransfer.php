<!DOCTYPE html>
<?php
// ======================================================
// (A) INCLUDE KONFIG & CEK LOGIN
// ======================================================
include "include/config.php";
include "include/guard.php";

// ======================================================
// (B) AMBIL DATA MASTER (LOKASI & PRODUK)
// ======================================================
$queryLokasiAktif   = "SELECT location_id, namalokasi FROM location WHERE status=1 ORDER BY namalokasi";
$resultLokasiAktif  = mysqli_query($connection, $queryLokasiAktif) or die(mysqli_error($connection));
$listLokasi = [];
while ($row = mysqli_fetch_assoc($resultLokasiAktif)) $listLokasi[] = $row;

$queryProdukAktif   = "SELECT 
  p.product_id, 
  b.brand_name, 
  m.model_name
FROM product p
LEFT JOIN brand b ON p.brand_id = b.brand_id
LEFT JOIN model m ON p.model_id = m.model_id
WHERE p.status = 1
ORDER BY b.brand_name, m.model_name
";
$resultProdukAktif  = mysqli_query($connection, $queryProdukAktif) or die(mysqli_error($connection));
$listProduk = [];
while ($row = mysqli_fetch_assoc($resultProdukAktif)) $listProduk[] = $row;

// ======================================================
// (C) PROSES SIMPAN TRANSFER
//      - Validasi input
//      - Insert transfer_stok (header)
//      - Loop detail: cek stok sumber, update inventory kedua lokasi,
//        insert transfer_stok_detail (before/after)
// ======================================================
if (isset($_POST['btnSimpan'])) {
    // ---------- Ambil input utama ----------
    $idLokasiSumber = (int)($_POST['source_location_id'] ?? 0);
    $idLokasiTujuan = (int)($_POST['target_location_id'] ?? 0);
    $tanggalTransfer= mysqli_real_escape_string($connection, $_POST['created_at'] ?? date('Y-m-d\TH:i'));
    $catatanTransfer= mysqli_real_escape_string($connection, $_POST['note'] ?? '');

    // Baris detail
    $listProdukId = $_POST['product_id'] ?? [];
    $listQtyInput = $_POST['qty'] ?? [];

    // ---------- Validasi dasar ----------
    if ($idLokasiSumber <= 0 || $idLokasiTujuan <= 0) {
        die("Lokasi sumber dan tujuan wajib dipilih.");
    }
    if ($idLokasiSumber === $idLokasiTujuan) {
        die("Lokasi sumber dan tujuan tidak boleh sama.");
    }
    if (empty($listProdukId)) {
        die("Minimal satu produk harus diinput.");
    }

    // ---------- Transaksi DB ----------
    mysqli_begin_transaction($connection);
    try {
        // (C1) Insert header transfer
        $idUserAktif = (int)($_SESSION['user_id'] ?? 1); // sesuaikan: simpan user_id saat login
        $queryInsertTransfer = "
          INSERT INTO transfer_stok (source_location_id, target_location_id, user_id, note, created_at)
          VALUES ($idLokasiSumber, $idLokasiTujuan, $idUserAktif, '$catatanTransfer', '$tanggalTransfer')
        ";
        mysqli_query($connection, $queryInsertTransfer) or throw new Exception(mysqli_error($connection));
        $idTransferBaru = mysqli_insert_id($connection);

        // (C2) Loop detail
        foreach ($listProdukId as $i => $pidRaw) {
            $idProduk = (int)$pidRaw;
            $qtyInput = (int)($listQtyInput[$i] ?? 0);
            if ($idProduk <= 0 || $qtyInput <= 0) continue; // abaikan baris invalid/0

            // (C2a) Pastikan baris inventory untuk kedua lokasi ada
            // Sumber
            $queryInvSrc = "
              SELECT quantity FROM inventory 
              WHERE location_id=$idLokasiSumber AND product_id=$idProduk LIMIT 1";
            $resultInvSrc = mysqli_query($connection, $queryInvSrc) or throw new Exception(mysqli_error($connection));
            if ($rowInvSrc = mysqli_fetch_assoc($resultInvSrc)) {
                $qtySumberBefore = (int)$rowInvSrc['quantity'];
            } else {
                mysqli_query($connection, "
                    INSERT INTO inventory(location_id, product_id, quantity)
                    VALUES ($idLokasiSumber, $idProduk, 0)
                ") or throw new Exception(mysqli_error($connection));
                $qtySumberBefore = 0;
            }

            // Tujuan
            $queryInvTgt = "
              SELECT quantity FROM inventory 
              WHERE location_id=$idLokasiTujuan AND product_id=$idProduk LIMIT 1";
            $resultInvTgt = mysqli_query($connection, $queryInvTgt) or throw new Exception(mysqli_error($connection));
            if ($rowInvTgt = mysqli_fetch_assoc($resultInvTgt)) {
                $qtyTujuanBefore = (int)$rowInvTgt['quantity'];
            } else {
                mysqli_query($connection, "
                    INSERT INTO inventory(location_id, product_id, quantity)
                    VALUES ($idLokasiTujuan, $idProduk, 0)
                ") or throw new Exception(mysqli_error($connection));
                $qtyTujuanBefore = 0;
            }

            // (C2b) Validasi stok sumber cukup
            if ($qtySumberBefore < $qtyInput) {
                throw new Exception("Stok sumber tidak cukup untuk produk ID $idProduk (stok: $qtySumberBefore, minta: $qtyInput)");
            }

            // (C2c) Hitung after
            $qtySumberAfter = $qtySumberBefore - $qtyInput;
            $qtyTujuanAfter = $qtyTujuanBefore + $qtyInput;

            // (C2d) Update inventory kedua lokasi
            mysqli_query($connection, "
                UPDATE inventory SET quantity=$qtySumberAfter
                WHERE location_id=$idLokasiSumber AND product_id=$idProduk
            ") or throw new Exception(mysqli_error($connection));

            mysqli_query($connection, "
                UPDATE inventory SET quantity=$qtyTujuanAfter
                WHERE location_id=$idLokasiTujuan AND product_id=$idProduk
            ") or throw new Exception(mysqli_error($connection));

            // (C2e) Insert detail transfer (rekam before/after)
            $queryInsertDetail = "
              INSERT INTO transfer_stok_detail
                (transfer_id, product_id, source_location_id, target_location_id, quantity,
                 src_quantitybef, src_quantityaft, tgt_quantitybef, tgt_quantityaft)
              VALUES
                ($idTransferBaru, $idProduk, $idLokasiSumber, $idLokasiTujuan, $qtyInput,
                 $qtySumberBefore, $qtySumberAfter, $qtyTujuanBefore, $qtyTujuanAfter)
            ";
            mysqli_query($connection, $queryInsertDetail) or throw new Exception(mysqli_error($connection));
        }

        // (C3) Commit
        mysqli_commit($connection);
        header("Location: transfer.php?msg=add");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($connection);
        die("Gagal menyimpan transfer: " . htmlspecialchars($e->getMessage()));
    }
}
?>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Tambah Transfer Stok</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet" />
</head>
<body class="sb-nav-fixed">
<?php include "include/navbar.php"; ?>
<div id="layoutSidenav">
<?php include "include/menu.php"; ?>
<div id="layoutSidenav_content">
<main class="container-fluid px-4">
    <h1 class="mt-4">Tambah Transfer Stok</h1>
    <ol class="breadcrumb mb-4"><li class="breadcrumb-item active">Pindahkan stok antar lokasi</li></ol>

    <div class="mb-3">
        <a href="transfer.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</a>
    </div>

    <!-- ==================== FORM TRANSFER ==================== -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Form Transfer Stok</div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row g-3">
                    <!-- Lokasi Sumber -->
                    <div class="col-md-4">
                        <label class="form-label">Lokasi Sumber</label>
                        <select name="source_location_id" id="selectSource" class="form-select" required>
                            <option value="">-- Pilih Lokasi Sumber --</option>
                            <?php foreach ($listLokasi as $lok): ?>
                              <option value="<?= $lok['location_id'] ?>"><?= htmlspecialchars($lok['namalokasi']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Lokasi Tujuan -->
                    <div class="col-md-4">
                        <label class="form-label">Lokasi Tujuan</label>
                        <select name="target_location_id" id="selectTarget" class="form-select" required>
                            <option value="">-- Pilih Lokasi Tujuan --</option>
                            <?php foreach ($listLokasi as $lok): ?>
                              <option value="<?= $lok['location_id'] ?>"><?= htmlspecialchars($lok['namalokasi']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tanggal -->
                    <div class="col-md-4">
                        <label class="form-label">Tanggal</label>
                        <input type="datetime-local" name="created_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>

                    <!-- Catatan -->
                    <div class="col-12">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="note" class="form-control" placeholder="Catatan (opsional)">
                    </div>
                </div>

                <hr>
                <h5>Detail Barang yang Ditransfer</h5>
                <p class="text-muted small mb-2">Isi beberapa baris sekaligus: pilih produk dan jumlah yang dipindahkan dari Sumber ke Tujuan.</p>

                <!-- ====== TABEL DINAMIS DETAIL ====== -->
                <div id="detailContainer">
                    <div class="row detail-row mb-2">
                        <div class="col-md-7">
                            <select name="product_id[]" class="form-select tomselect-produk" required>
                                <option value="">-- Pilih Produk --</option>
                                <?php foreach ($listProduk as $prd): ?>
                                  <option value="<?= $prd['product_id'] ?>"><?= htmlspecialchars($prd['brand_name'].' '.$prd['model_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="qty[]" class="form-control" placeholder="Qty" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger removeRow">Hapus</button>
                        </div>
                    </div>
                </div>
                <button type="button" id="addRow" class="btn btn-sm btn-secondary mt-2">+ Tambah Baris</button>

                <div class="mt-4">
                    <button type="submit" name="btnSimpan" id="btnSubmit" class="btn btn-success">Simpan Transfer</button>
                </div>
            </form>
        </div>
    </div>
</main>
   <?php include "include/footer.php"; ?>

</div>
</div>

<!-- ========================= SCRIPT ========================= -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
(function(){
  // ------------------------------------------------------
  // (D) Inisialisasi TomSelect untuk select produk
  //     (hindari clone node yang sudah di-wrap TomSelect)
  // ------------------------------------------------------
  if (window.__transferAddBound) return;
  window.__transferAddBound = true;

  // Simpan opsi produk mentah (untuk baris baru)
  const firstSel = document.querySelector('.detail-row select.tomselect-produk');
  const productOptionsHTML = firstSel ? firstSel.innerHTML : '<option value="">-- Pilih Produk --</option>';

  function initTomSelectProduk(context=document){
    context.querySelectorAll('select.tomselect-produk').forEach(sel=>{
      if(!sel.tomselect){
        new TomSelect(sel,{
          persist:false, maxOptions:1000,
          sortField:{field:'text',direction:'asc'},
          placeholder:'Pilih produk...'
        });
      }
    });
  }
  initTomSelectProduk();

  // Tambah baris detail (bangun dari HTML mentah)
  const container = document.getElementById('detailContainer');
  document.getElementById('addRow').addEventListener('click', ()=>{
    const row = document.createElement('div');
    row.className = 'row detail-row mb-2';
    row.innerHTML = `
      <div class="col-md-7">
        <select name="product_id[]" class="form-select tomselect-produk" required>
          ${productOptionsHTML}
        </select>
      </div>
      <div class="col-md-3">
        <input type="number" name="qty[]" class="form-control" placeholder="Qty" min="1" required>
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-danger removeRow">Hapus</button>
      </div>
    `;
    container.appendChild(row);
    initTomSelectProduk(row);
  });

  // Hapus baris
  document.addEventListener('click', (e)=>{
    if(e.target.classList.contains('removeRow')){
      const rows = document.querySelectorAll('.detail-row');
      if(rows.length > 1){
        e.target.closest('.detail-row').remove();
      }
    }
  });

  // ------------------------------------------------------
  // (E) Validasi front-end: lokasi sumber != tujuan
  //     Disable tombol submit kalau sama
  // ------------------------------------------------------
  const selSource = document.getElementById('selectSource');
  const selTarget = document.getElementById('selectTarget');
  const btnSubmit = document.getElementById('btnSubmit');
  function cekLokasiBerbeda(){
    const same = selSource.value && selTarget.value && (selSource.value === selTarget.value);
    btnSubmit.disabled = same;
    if (same) {
      btnSubmit.title = "Lokasi sumber dan tujuan tidak boleh sama";
    } else {
      btnSubmit.title = "";
    }
  }
  selSource.addEventListener('change', cekLokasiBerbeda);
  selTarget.addEventListener('change', cekLokasiBerbeda);
  cekLokasiBerbeda();
})();
</script>
</body>
</html>
