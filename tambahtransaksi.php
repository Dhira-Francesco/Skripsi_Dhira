<!DOCTYPE html>
<?php
// ======================================================
// (A) INCLUDE KONFIG & CEK LOGIN
// ======================================================
include "include/config.php";
include "include/guard.php";

// ======================================================
// (B) AMBIL DATA MASTER (lokasi, produk, supplier)
// ======================================================
$resultLokasi = mysqli_query($connection, "
  SELECT location_id, namalokasi
  FROM location
  WHERE status=1
  ORDER BY namalokasi
");

$resultProduk = mysqli_query($connection, "
  SELECT 
    p.product_id, 
    b.brand_name, 
    m.model_name
  FROM product p
  LEFT JOIN brand b ON p.brand_id = b.brand_id
  LEFT JOIN model m ON p.model_id = m.model_id
  WHERE p.status = 1
  ORDER BY b.brand_name, m.model_name
");

$resultSupplier = mysqli_query($connection, "
  SELECT supplier_id, namasupplier
  FROM supplier
  WHERE status=1
  ORDER BY namasupplier
");

// ======================================================
// (C) PROSES SIMPAN TRANSAKSI
// ======================================================
if (isset($_POST['btnSimpan'])) {

  $idLokasi      = (int)($_POST['location_id'] ?? 0);
  $tipeTransaksi = $_POST['type'] ?? 'IN';
  $tanggalTrans  = mysqli_real_escape_string($connection, $_POST['transaction_date'] ?? date('Y-m-d\TH:i'));
  $catatan       = mysqli_real_escape_string($connection, $_POST['note'] ?? '');

  if ($idLokasi <= 0) {
    die("Lokasi wajib dipilih.");
  }

  $listProdukId = $_POST['product_id'] ?? [];
  $listQtyInput = $_POST['qty'] ?? [];

  if (empty($listProdukId)) {
    die("Minimal satu produk harus diinput.");
  }

  // Khusus IN: supplier wajib
  $idSupplier = 0;
  $noInvoice  = '';
  if ($tipeTransaksi === 'IN') {
    $idSupplier = (int)($_POST['supplier_id'] ?? 0);
    $noInvoice  = mysqli_real_escape_string($connection, $_POST['invoice'] ?? '');
    if ($idSupplier <= 0) {
      die("Supplier wajib dipilih untuk transaksi IN.");
    }
  }

  mysqli_begin_transaction($connection);
  try {
    // (C1) Insert transaction
    $idUserAktif = (int)($_SESSION['user_id'] ?? 1);

    $tipeSafe = mysqli_real_escape_string($connection, $tipeTransaksi);
    $sqlTrans = "
      INSERT INTO `transaction` (location_id, user_id, transaction_date, note, `type`)
      VALUES ($idLokasi, $idUserAktif, '$tanggalTrans', '$catatan', '$tipeSafe')
    ";
    mysqli_query($connection, $sqlTrans) or throw new Exception(mysqli_error($connection));
    $idTransaksiBaru = (int)mysqli_insert_id($connection);

    // (C2) Khusus IN => transaction_stok_in
    if ($tipeTransaksi === 'IN') {
      $sqlIn = "
        INSERT INTO transaction_stok_in (transaction_id, supplier_id, invoice)
        VALUES ($idTransaksiBaru, $idSupplier, '$noInvoice')
      ";
      mysqli_query($connection, $sqlIn) or throw new Exception(mysqli_error($connection));
    }

    // (C3) Loop detail
    foreach ($listProdukId as $i => $pidRaw) {
      $idProduk = (int)$pidRaw;
      $qtyInput = trim($listQtyInput[$i] ?? '0');

      if ($idProduk <= 0) continue;
      if ($qtyInput === '' || !is_numeric(str_replace(['+','-'], '', $qtyInput))) continue;

      $qtyInput = (int)$qtyInput;

      // Normalisasi qty change sesuai tipe
      if ($tipeTransaksi === 'IN') {
        if ($qtyInput <= 0) continue;
        $qtyChange = $qtyInput;
      } elseif ($tipeTransaksi === 'OUT') {
        if ($qtyInput <= 0) continue;
        $qtyChange = -$qtyInput;
      } else { // ADJUST
        $qtyChange = $qtyInput; // boleh +/-
        if ($qtyChange === 0) continue;
      }

      // VALIDASI SERVER-SIDE:
      // kalau IN, produk harus terdaftar di supplier_product sesuai supplier yang dipilih
      if ($tipeTransaksi === 'IN') {
        $cek = mysqli_query($connection, "
          SELECT 1
          FROM supplier_product
          WHERE supplier_id=$idSupplier AND product_id=$idProduk
          LIMIT 1
        ") or throw new Exception(mysqli_error($connection));

        if (mysqli_num_rows($cek) === 0) {
          throw new Exception("Produk (ID $idProduk) tidak terdaftar pada supplier yang dipilih.");
        }
      }

      // Ambil stok sebelum (lock row)
      $resInv = mysqli_query($connection, "
        SELECT quantity
        FROM inventory
        WHERE location_id=$idLokasi AND product_id=$idProduk
        LIMIT 1
        FOR UPDATE
      ") or throw new Exception(mysqli_error($connection));

      if ($rowInv = mysqli_fetch_assoc($resInv)) {
        $qtyBefore = (int)$rowInv['quantity'];
      } else {
        // kalau belum ada baris inventory, buat 0
        mysqli_query($connection, "
          INSERT INTO inventory(location_id, product_id, quantity)
          VALUES ($idLokasi, $idProduk, 0)
        ") or throw new Exception(mysqli_error($connection));
        $qtyBefore = 0;
      }

      // OUT tidak boleh minus
      if ($tipeTransaksi === 'OUT' && ($qtyBefore + $qtyChange) < 0) {
        throw new Exception("Stok tidak cukup untuk produk ID $idProduk (stok: $qtyBefore, minta: ".abs($qtyChange).")");
      }

      // update inventory
      $qtyAfter = $qtyBefore + $qtyChange;
      mysqli_query($connection, "
        UPDATE inventory
        SET quantity=$qtyAfter
        WHERE location_id=$idLokasi AND product_id=$idProduk
      ") or throw new Exception(mysqli_error($connection));

      // insert detail transaksi
      mysqli_query($connection, "
        INSERT INTO transaction_details
          (transaction_id, product_id, location_id, quantity, quantity_before, quantity_after)
        VALUES
          ($idTransaksiBaru, $idProduk, $idLokasi, $qtyChange, $qtyBefore, $qtyAfter)
      ") or throw new Exception(mysqli_error($connection));
    }

    mysqli_commit($connection);
    header("Location: transaksi.php?msg=add");
    exit;

  } catch (Exception $e) {
    mysqli_rollback($connection);
    die("Gagal menyimpan transaksi: " . htmlspecialchars($e->getMessage()));
  }
}
?>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Tambah Transaksi Stok</title>
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
  <h1 class="mt-4">Tambah Transaksi</h1>
  <ol class="breadcrumb mb-4"><li class="breadcrumb-item active">IN / OUT / ADJUST</li></ol>

  <div class="mb-3">
    <a href="transaksi.php" class="btn btn-secondary">
      <i class="fa fa-arrow-left"></i> Kembali
    </a>
  </div>

  <div class="card mb-4">
    <div class="card-header bg-primary text-white">Form Transaksi Stok</div>
    <div class="card-body">

      <form method="POST" action="">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Tipe</label>
            <select name="type" id="typeSelect" class="form-select" required>
              <option value="IN">IN (Barang Masuk)</option>
              <option value="OUT">OUT (Barang Keluar)</option>
              <option value="ADJUST">ADJUST (Penyesuaian)</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Lokasi</label>
            <select name="location_id" id="lokasiSelect" class="form-select" required>
              <option value="">-- Pilih Lokasi --</option>
              <?php while($l=mysqli_fetch_assoc($resultLokasi)): ?>
                <option value="<?= (int)$l['location_id'] ?>"><?= htmlspecialchars($l['namalokasi']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Tanggal</label>
            <input type="datetime-local" name="transaction_date" class="form-control"
                   value="<?= date('Y-m-d\TH:i') ?>" required>
          </div>

          <div class="col-md-12">
            <label class="form-label">Catatan</label>
            <input type="text" name="note" class="form-control" placeholder="Catatan (opsional)">
          </div>
        </div>

        <!-- BLOK SUPPLIER (KHUSUS IN) -->
        <div id="blokSupplier" class="row g-3 mt-3">
          <div class="col-md-6">
            <label class="form-label">Supplier (khusus IN)</label>
            <select name="supplier_id" id="supplierSelect" class="form-select">
              <option value="">-- Pilih Supplier --</option>
              <?php while($s=mysqli_fetch_assoc($resultSupplier)): ?>
                <option value="<?= (int)$s['supplier_id'] ?>"><?= htmlspecialchars($s['namasupplier']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">No. Invoice (opsional)</label>
            <input type="text" name="invoice" class="form-control" placeholder="Nomor invoice">
          </div>
        </div>

        <hr>
        <h5>Detail Barang</h5>
        <p class="text-muted small mb-2">
          IN: isi jumlah masuk (positif).&nbsp;
          OUT: isi jumlah keluar (positif).&nbsp;
          ADJUST: isi selisih (boleh + / -).
        </p>

        <div id="detailContainer">
          <div class="row detail-row mb-2">
            <div class="col-md-6">
              <select name="product_id[]" class="form-select tomselect-produk" required>
                <option value="">-- Pilih Produk --</option>
                <?php while($p=mysqli_fetch_assoc($resultProduk)): ?>
                  <option value="<?= (int)$p['product_id'] ?>">
                    <?= htmlspecialchars(trim(($p['brand_name'] ?? '').' '.($p['model_name'] ?? ''))) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="col-md-2">
              <input type="text" class="form-control stok-now" value="-" readonly>
              <small class="text-muted">Stok</small>
            </div>

            <div class="col-md-2">
              <input type="number" name="qty[]" class="form-control" placeholder="Qty" required>
            </div>

            <div class="col-md-2">
              <button type="button" class="btn btn-danger removeRow w-100">Hapus</button>
            </div>
          </div>
        </div>

        <button type="button" id="addRow" class="btn btn-sm btn-secondary mt-2">+ Tambah Baris</button>

        <div class="mt-4">
          <button type="submit" name="btnSimpan" class="btn btn-success">Simpan Transaksi</button>
        </div>
      </form>

    </div>
  </div>

</main>
<?php include "include/footer.php"; ?>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<script>
(function(){
  if (window.__txAddBound) return;
  window.__txAddBound = true;

  const typeSelect     = document.getElementById('typeSelect');
  const blokSupplier   = document.getElementById('blokSupplier');
  const supplierSelect = document.getElementById('supplierSelect');
  const lokasiSelect   = document.getElementById('lokasiSelect');

  // ====== ambil options semua produk (untuk OUT/ADJUST) ======
  const firstSelect = document.querySelector('.detail-row select.tomselect-produk');
  const allProductOptionsHTML = firstSelect ? firstSelect.innerHTML : '<option value="">-- Pilih Produk --</option>';

  // currentOptions dipakai untuk addRow agar ikut list yang aktif
  let currentOptionsHTML = allProductOptionsHTML;

  // ====== init tomselect untuk produk ======
  function initTomSelectProduk(context=document){
    context.querySelectorAll('select.tomselect-produk').forEach(sel=>{
      if(!sel.tomselect){
        new TomSelect(sel,{
          persist:false,
          maxOptions:1000,
          sortField:{field:'text',direction:'asc'},
          placeholder:'Pilih produk...'
        });
      }
    });

    // supplier select juga enak kalau pakai tomselect (optional)
    if (supplierSelect && !supplierSelect.tomselect) {
      new TomSelect(supplierSelect,{
        create:false,
        persist:false,
        maxOptions:1000,
        sortField:{field:'text',direction:'asc'},
        placeholder:'Pilih supplier...'
      });
    }
  }
  initTomSelectProduk();

  function toggleSupplier(){
    blokSupplier.style.display = (typeSelect.value === 'IN') ? '' : 'none';
  }

  function applyOptionsToAllSelects(optionsHTML){
    currentOptionsHTML = optionsHTML;

    document.querySelectorAll('select.tomselect-produk').forEach(sel=>{
      sel.innerHTML = optionsHTML;

      if(sel.tomselect){
        sel.tomselect.clear(true);
        sel.tomselect.clearOptions();

        sel.querySelectorAll('option').forEach(opt=>{
          if(opt.value !== ''){
            sel.tomselect.addOption({ value: opt.value, text: opt.textContent });
          }
        });

        sel.tomselect.refreshOptions(false);
      }
    });

    // setelah options berubah, refresh stok
    updateAllStocks();
  }

  async function loadProductsBySupplier(supplierId){
    const url = `ajax_produk_by_supplier.php?supplier_id=${encodeURIComponent(supplierId)}`;
    const res = await fetch(url);
    const data = await res.json();
    if(!data.ok) return [];
    return data.items || [];
  }

  function buildOptionsHTML(items, emptyText){
    let html = `<option value="">${emptyText}</option>`;
    items.forEach(it=>{
      html += `<option value="${it.value}">${it.text}</option>`;
    });
    return html;
  }

  async function refreshProductList(){
    // OUT / ADJUST: semua produk
    if(typeSelect.value !== 'IN'){
      applyOptionsToAllSelects(allProductOptionsHTML);
      return;
    }

    // IN: supplier wajib dipilih dulu
    const sid = supplierSelect.value;
    if(!sid){
      applyOptionsToAllSelects(`<option value="">-- Pilih Supplier dulu --</option>`);
      return;
    }

    const items = await loadProductsBySupplier(sid);
    if(items.length === 0){
      applyOptionsToAllSelects(`<option value="">-- Produk supplier ini belum di-set --</option>`);
      return;
    }

    applyOptionsToAllSelects(buildOptionsHTML(items, '-- Pilih Produk --'));
  }

  // ====== STOCK INFO (lokasi + produk) ======
  async function fetchStock(locationId, productId){
    const url = `ajax_get_stock.php?location_id=${encodeURIComponent(locationId)}&product_id=${encodeURIComponent(productId)}`;
    const res = await fetch(url);
    const data = await res.json();
    if(!data.ok) return null;
    return data.quantity;
  }

  async function updateRowStock(row){
    const stokBox = row.querySelector('.stok-now');
    const prodSel = row.querySelector('select.tomselect-produk');
    if(!stokBox || !prodSel) return;

    const locId = lokasiSelect.value;
    const prodId = prodSel.value;

    if(!locId || !prodId){
      stokBox.value = "-";
      return;
    }

    stokBox.value = "Loading...";
    const qty = await fetchStock(locId, prodId);
    stokBox.value = (qty === null) ? "-" : qty;
  }

  async function updateAllStocks(){
    const rows = document.querySelectorAll('.detail-row');
    for(const r of rows) {
      await updateRowStock(r);
    }
  }

  // ====== add row ======
  const container = document.getElementById('detailContainer');
  const btnAdd    = document.getElementById('addRow');

  btnAdd.addEventListener('click', ()=>{
    const row = document.createElement('div');
    row.className = 'row detail-row mb-2';
    row.innerHTML = `
      <div class="col-md-6">
        <select name="product_id[]" class="form-select tomselect-produk" required>
          ${currentOptionsHTML}
        </select>
      </div>

      <div class="col-md-2">
        <input type="text" class="form-control stok-now" value="-" readonly>
        <small class="text-muted">Stok</small>
      </div>

      <div class="col-md-2">
        <input type="number" name="qty[]" class="form-control" placeholder="Qty" required>
      </div>

      <div class="col-md-2">
        <button type="button" class="btn btn-danger removeRow w-100">Hapus</button>
      </div>
    `;
    container.appendChild(row);

    initTomSelectProduk(row);

    // refresh stok row baru (kalau lokasi & produk sudah kepilih)
    updateRowStock(row);
  });

  // ====== remove row ======
  document.addEventListener('click', (e)=>{
    if(e.target.classList.contains('removeRow')){
      const rows = document.querySelectorAll('.detail-row');
      if(rows.length > 1){
        e.target.closest('.detail-row').remove();
      }
    }
  });

  // ====== event bindings ======
  typeSelect.addEventListener('change', async ()=>{
    toggleSupplier();
    await refreshProductList();
  });

  supplierSelect.addEventListener('change', refreshProductList);

  // lokasi berubah -> update semua stok
  lokasiSelect.addEventListener('change', updateAllStocks);

  // produk berubah -> update stok row tsb
  document.addEventListener('change', (e)=>{
    if(e.target && e.target.matches('select.tomselect-produk')){
      const row = e.target.closest('.detail-row');
      if(row) updateRowStock(row);
    }
  });

  // init
  toggleSupplier();
  refreshProductList();   // ini juga akan trigger updateAllStocks via applyOptionsToAllSelects
})();
</script>

</body>
</html>
