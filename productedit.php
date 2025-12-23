<!DOCTYPE html>
<?php
// ======================================================
// (A) INCLUDE KONFIGURASI & CEK LOGIN
// ======================================================
include "include/config.php";
include "include/guard.php";

// ======================================================
// (B) VALIDASI ID PRODUK
// ======================================================
$idProduk = (int)($_GET['id'] ?? 0);
if ($idProduk <= 0) { header("location:product.php"); exit; }

// ======================================================
// (C) AMBIL DATA PRODUK + RELASI
// ======================================================
$qProduk = "
  SELECT p.*, 
         b.brand_name, m.model_name, c.category_name,
         u.unit_name, u.satuan_hitung
  FROM product p
  LEFT JOIN brand b ON p.brand_id=b.brand_id
  LEFT JOIN model m ON p.model_id=m.model_id
  LEFT JOIN category c ON p.category_id=c.category_id
  LEFT JOIN unit u ON p.unit_id=u.unit_id
  WHERE p.product_id=$idProduk
  LIMIT 1";
$rProduk = mysqli_query($connection,$qProduk) or die(mysqli_error($connection));
$dProduk = mysqli_fetch_assoc($rProduk);
if(!$dProduk){ header("location:product.php"); exit; }

// ======================================================
// (D) PANGGIL DATA LIST REFERENSI
// ======================================================
$resBrand = mysqli_query($connection,"SELECT brand_id, brand_name FROM brand ORDER BY brand_name");
$resModel = mysqli_query($connection,"SELECT model_id, model_name FROM model ORDER BY model_name");
$resUnit  = mysqli_query($connection,"SELECT unit_id, unit_name, satuan_hitung FROM unit ORDER BY unit_name");
$resCat   = mysqli_query($connection,"SELECT category_id, category_name FROM category ORDER BY category_name");



// Supplier aktif → simpan ke array & siapkan HTML <option> (value = supplier_id)
$resSupp  = mysqli_query($connection,"SELECT supplier_id, namasupplier FROM supplier WHERE status=1 ORDER BY namasupplier");
$suppliers = [];
$supplierOptionsHtml = "";
while($s = mysqli_fetch_assoc($resSupp)){
  $sid = (int)$s['supplier_id'];
  $snm = htmlspecialchars($s['namasupplier'], ENT_QUOTES);
  $suppliers[] = ['supplier_id'=>$sid,'namasupplier'=>$snm];
  $supplierOptionsHtml .= "<option value=\"{$sid}\">{$snm}</option>";
}

// ======================================================
// (E) AMBIL SUPPLIER TERKAIT PRODUK
// ======================================================
$resSuppProd = mysqli_query($connection,"
  SELECT sp.supplier_id, s.namasupplier, sp.price
  FROM supplier_product sp
  LEFT JOIN supplier s ON sp.supplier_id=s.supplier_id
  WHERE sp.product_id=$idProduk
");
$listSupp = [];
while($r=mysqli_fetch_assoc($resSuppProd)){
  $listSupp[]=[
    'supplier_id'=>(int)$r['supplier_id'],
    'namasupplier'=>htmlspecialchars($r['namasupplier'] ?? '', ENT_QUOTES),
    'price'=>(float)$r['price']
  ];
}

// ======================================================
// (F) FUNGSI RESOLVE REFERENSI
// ======================================================
function resolveReferenceId($connection,$table,$pk,$col,$val){
  $v=mysqli_real_escape_string($connection,trim($val));
  if($v==='') return null;
  $r=mysqli_query($connection,"SELECT $pk FROM $table WHERE $col='$v' LIMIT 1");
  if($row=mysqli_fetch_assoc($r)) return (int)$row[$pk];
  mysqli_query($connection,"INSERT INTO $table($col) VALUES('$v')") or die(mysqli_error($connection));
  return (int)mysqli_insert_id($connection);
}
function resolveUnitId($connection,$name,$type){
  $n=mysqli_real_escape_string($connection,trim($name));
  $t=mysqli_real_escape_string($connection,trim($type));
  if($n==='') return null;
  $r=mysqli_query($connection,"SELECT unit_id FROM unit WHERE unit_name='$n' LIMIT 1");
  if($row=mysqli_fetch_assoc($r)) return (int)$row['unit_id'];
  mysqli_query($connection,"INSERT INTO unit(unit_name,satuan_hitung)VALUES('$n','$t')") or die(mysqli_error($connection));
  return (int)mysqli_insert_id($connection);
}

// ======================================================
// (G) PROSES UPDATE PRODUK
// ======================================================
if (isset($_POST['btnUpdate'])) {
  // 1. Data teks biasa
  $desc = mysqli_real_escape_string($connection, $_POST['product_desc'] ?? '');
  $size = mysqli_real_escape_string($connection, $_POST['product_size'] ?? '');

  // 2. Ambil teks brand/model/category/unit dari form (bisa pilih / ketik baru)
  $brandText = trim($_POST['brand_name'] ?? '');
  $modelText = trim($_POST['model_name'] ?? '');
  $catText   = trim($_POST['category_name'] ?? '');
  $unitName  = trim($_POST['unit_name'] ?? '');
  $unitType  = trim($_POST['satuan_hitung'] ?? '');

  // 3. Resolve ID referensi (kalau belum ada di master → INSERT dulu)
  $brandId = resolveReferenceId($connection, 'brand',    'brand_id',    'brand_name',    $brandText);
  $modelId = resolveReferenceId($connection, 'model',    'model_id',    'model_name',    $modelText);
  $catId   = resolveReferenceId($connection, 'category', 'category_id', 'category_name', $catText);
  $unitId  = resolveUnitId      ($connection, $unitName, $unitType);

  // 4. Susun display name = "Brand Model"
  $displayName = mysqli_real_escape_string($connection, trim($brandText . ' ' . $modelText));

  // 5. Data ROP
  $safety_stock    = max(0, (int)($_POST['safety_stock'] ?? 0));
  $lead_time_days  = max(1, (int)($_POST['lead_time_days'] ?? 1));
  $avg_daily_usage = max(0, (int)($_POST['avg_daily_usage'] ?? 0));

  $reorder_point = ($avg_daily_usage * $lead_time_days) + $safety_stock;

  // 6. UPDATE product dengan ID yang sudah pasti valid
  $q = "
    UPDATE product SET
      brand_id        = " . ($brandId !== null ? $brandId : "NULL") . ",
      model_id        = " . ($modelId !== null ? $modelId : "NULL") . ",
      unit_id         = " . ($unitId  !== null ? $unitId  : "NULL") . ",
      category_id     = " . ($catId   !== null ? $catId   : "NULL") . ",
      name            = '$displayName',
      `desc`          = '$desc',
      ukuran          = '$size',
      safety_stock    = $safety_stock,
      lead_time_days  = $lead_time_days,
      avg_daily_usage = $avg_daily_usage,
      reorder_point   = $reorder_point
    WHERE product_id = $idProduk
  ";
  mysqli_query($connection, $q) or die(mysqli_error($connection));

  // 7. Bersihkan relasi supplier lama
  mysqli_query($connection, "DELETE FROM supplier_product WHERE product_id=$idProduk");

  // 8. Simpan relasi supplier baru
  if (!empty($_POST['supplier_id']) && is_array($_POST['supplier_id'])) {
    foreach ($_POST['supplier_id'] as $i => $sidRaw) {
      $supplierId = (int)$sidRaw;
      $hargaRaw   = $_POST['harga'][$i] ?? '';
      $harga      = (float)str_replace(['.', ',', ' '], '', $hargaRaw);

      if ($supplierId > 0 && $harga > 0) {
        $sqlSupp = "
          INSERT INTO supplier_product (supplier_id, product_id, price)
          VALUES ($supplierId, $idProduk, $harga)
          ON DUPLICATE KEY UPDATE price = VALUES(price)
        ";
        mysqli_query($connection, $sqlSupp) or die(mysqli_error($connection));
      }
    }
  }

  header("location:product.php?msg=update");
  exit;
}
?>
<html lang="id">
<head>
<meta charset="utf-8"/>
<title>Edit Produk - PBS Inventory</title>
<link href="css/styles.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet"/>
<script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<?php include "include/navbar.php"; ?>
<div id="layoutSidenav">
<?php include "include/menu.php"; ?>
<div id="layoutSidenav_content">
<main class="container-fluid px-4">
  <h1 class="mt-4">Edit Produk</h1>
  <a href="product.php" class="btn btn-secondary mb-3"><i class="fa fa-arrow-left"></i> Kembali</a>

  <div class="card mb-4">
    <div class="card-header bg-warning">Form Edit Produk</div>
    <div class="card-body">
      <form method="POST">
        <div class="row g-3">
          <!-- Nama tampilan otomatis (readonly) -->
                <div class="col-md-6">
            <label class="form-label">Deskripsi</label>
            <input name="product_desc" class="form-control" value="<?= htmlspecialchars($dProduk['desc'] ?? '') ?>">
          </div>

          <div class="col-md-3">
            <label class="form-label">Ukuran</label>
            <input name="product_size" class="form-control" value="<?= htmlspecialchars($dProduk['ukuran'] ?? '') ?>">
          </div>

          <!-- Dropdown Brand -->
          <div class="col-md-3">
            <label class="form-label">Brand</label>
            <select name="brand_name" id="brandSelect" class="form-control">
              <?php while($b=mysqli_fetch_assoc($resBrand)): ?>
                <?php $bn = $b['brand_name']; ?>
                <option value="<?= htmlspecialchars($bn) ?>" <?= ($bn === ($dProduk['brand_name'] ?? ''))?'selected':'' ?>>
                  <?= htmlspecialchars($bn) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Dropdown Model -->
          <div class="col-md-3">
            <label class="form-label">Model</label>
            <select name="model_name" id="modelSelect" class="form-control">
              <?php while($m=mysqli_fetch_assoc($resModel)): ?>
                <?php $mn = $m['model_name']; ?>
                <option value="<?= htmlspecialchars($mn) ?>" <?= ($mn === ($dProduk['model_name'] ?? ''))?'selected':'' ?>>
                  <?= htmlspecialchars($mn) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Dropdown Unit -->
          <div class="col-md-3">
            <label class="form-label">Unit</label>
            <select name="unit_name" id="unitNameSelect" class="form-control">
              <?php while($u=mysqli_fetch_assoc($resUnit)): ?>
                <?php $un = $u['unit_name']; ?>
                <option value="<?= htmlspecialchars($un) ?>" <?= ($un === ($dProduk['unit_name'] ?? ''))?'selected':'' ?>>
                  <?= htmlspecialchars($un) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Dropdown Satuan -->
          <div class="col-md-3">
            <label class="form-label">Satuan Hitung</label>
            <select name="satuan_hitung" id="unitTypeSelect" class="form-control">
              <?php
                $opt=["pcs","box","set","roll","pack"];
                $curType = $dProduk['satuan_hitung'] ?? '';
                foreach($opt as $o){
                  $sel = ($o === $curType) ? 'selected' : '';
                  echo "<option value=\"$o\" $sel>".strtoupper($o)."</option>";
                }
              ?>
            </select>
          </div>

          <!-- Dropdown Kategori -->
          <div class="col-md-3">
            <label class="form-label">Kategori</label>
            <select name="category_name" id="categorySelect" class="form-control">
              <?php while($c=mysqli_fetch_assoc($resCat)): ?>
                <?php $cn = $c['category_name']; ?>
                <option value="<?= htmlspecialchars($cn) ?>" <?= ($cn === ($dProduk['category_name'] ?? ''))?'selected':'' ?>>
                  <?= htmlspecialchars($cn) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
                

          <div class="col-md-3">
  <label class="form-label">Rata rata barang keluar Per hari</label>
  <input type="number" class="form-control"
         id="avg_daily_usage"
         name="avg_daily_usage"
         value="<?php echo (int)($dProduk['avg_daily_usage'] ?? 0); ?>" 
         required> 
</div>

<div class="col-md-3">
  <label class="form-label">Estimasi waktu pengiriman dari Supplier (hari)</label>
  <input type="number" class="form-control"
         id="lead_time_days"
         name="lead_time_days"
         value="<?php echo (int)($dProduk['lead_time_days'] ?? 1); ?>" 
         required>          
</div>

<div class="col-md-3">
  <label class="form-label">Safety Stock</label>
  <input type="number" class="form-control"
         id="safety_stock"
         name="safety_stock"
         value="<?php echo (int)($dProduk['safety_stock'] ?? 0); ?>" 
         required>  
</div>

<div class="col-md-3">
  <label class="form-label">Stock minimal untuk pemesanan barang (ROP)</label>
  <input type="number" class="form-control"
         id="reorder_point"
         name="reorder_point"
         value="<?php echo (int)($dProduk['reorder_point'] ?? 0); ?>" 
         readonly>          
</div>



        </div>

        <!-- ================================================== -->
        <!-- SUPPLIER PRODUK -->
        <!-- ================================================== -->
        <hr><h5>Supplier Produk</h5>
        <div id="supplierContainer">
          <?php if(empty($listSupp)): ?>
            <div class="row supplier-row mb-2">
              <div class="col-md-6">
                <select name="supplier_id[]" class="form-select supplier-select">
                  <option value="">-- Pilih Supplier --</option>
                  <?= $supplierOptionsHtml ?>
                </select>
              </div>
              <div class="col-md-4">
                <input type="number" name="harga[]" class="form-control" placeholder="Harga (Rp)" min="1">
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-danger removeSupplier">Hapus</button>
              </div>
            </div>
          <?php else: ?>
            <?php foreach($listSupp as $r): ?>
              <div class="row supplier-row mb-2">
                <div class="col-md-6">
                  <select name="supplier_id[]" class="form-select supplier-select">
                    <option value="">-- Pilih Supplier --</option>
                    <?php foreach($suppliers as $s): ?>
                      <option value="<?= (int)$s['supplier_id'] ?>" <?= ((int)$s['supplier_id']===(int)$r['supplier_id'])?'selected':'' ?>>
                        <?= $s['namasupplier'] ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <input type="number" name="harga[]" class="form-control" value="<?= htmlspecialchars($r['price']) ?>" min="1">
                </div>
                <div class="col-md-2">
                  <button type="button" class="btn btn-danger removeSupplier">Hapus</button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <button type="button" id="addSupplier" class="btn btn-sm btn-secondary mt-2">+ Tambah Supplier</button>

        <div class="mt-4">
          <button type="submit" name="btnUpdate" class="btn btn-success">Simpan Perubahan</button>
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
/**
 * Inisialisasi dropdown referensi (brand/model/unit/kategori).
 * Tetap boleh create:true (biar bisa nambah master baru),
 * kecuali SUPPLIER (harus pilih dari yang sudah ada → create:false).
 */
new TomSelect('#brandSelect', {create:true,  persist:false, sortField:{field:'text',direction:'asc'}});
new TomSelect('#modelSelect', {create:true,  persist:false, sortField:{field:'text',direction:'asc'}});
new TomSelect('#unitNameSelect', {create:true, persist:false, sortField:{field:'text',direction:'asc'}});
new TomSelect('#unitTypeSelect', {create:true, persist:false, sortField:{field:'text',direction:'asc'}});
new TomSelect('#categorySelect', {create:true, persist:false, sortField:{field:'text',direction:'asc'}});

// ========================= SUPPLIER DYNAMIC ROWS =========================

// HTML <option> supplier mentah dari PHP (sekali saja)
const supplierOptionsHTML = `<?= $supplierOptionsHtml ?>`;

// Helper: inisialisasi TomSelect untuk select supplier (create:false)
function initSupplierSelect(context=document){
  context.querySelectorAll('select.supplier-select').forEach(sel=>{
    if(!sel.tomselect){
      new TomSelect(sel, {
        create: false, // Edit: tidak boleh tambah supplier baru
        persist:false,
        maxOptions: 1000,
        sortField:{field:'text', direction:'asc'},
        placeholder: 'Pilih supplier...'
      });
    }
  });
}
// Init baris awal
initSupplierSelect();

// Tambah baris supplier dari HTML mentah (bukan clone node TS)
document.getElementById('addSupplier').addEventListener('click', ()=>{
  const cont = document.getElementById('supplierContainer');
  const row  = document.createElement('div');
  row.className = 'row supplier-row mb-2';
  row.innerHTML = `
    <div class="col-md-6">
      <select name="supplier_id[]" class="form-select supplier-select">
        <option value="">-- Pilih Supplier --</option>
        ${supplierOptionsHTML}
      </select>
    </div>
    <div class="col-md-4">
      <input type="number" name="harga[]" class="form-control" placeholder="Harga (Rp)" min="1">
    </div>
    <div class="col-md-2">
      <button type="button" class="btn btn-danger removeSupplier">Hapus</button>
    </div>
  `;
  cont.appendChild(row);
  initSupplierSelect(row);
});

// Hapus baris supplier (sisakan minimal 1)
document.addEventListener('click', (e)=>{
  if(e.target.classList.contains('removeSupplier')){
    const rows = document.querySelectorAll('.supplier-row');
    if(rows.length > 1){
      e.target.closest('.supplier-row').remove();
    } else {
      const row = e.target.closest('.supplier-row');
      if (row.querySelector('select.supplier-select').tomselect) {
        row.querySelector('select.supplier-select').tomselect.clear();
      }
      row.querySelector('input[name="harga[]"]').value = '';
    }
  }
});
</script>
</body>
</html>
