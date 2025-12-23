<!DOCTYPE html>
<?php
// ======================================================
// (A) INCLUDE FILE KONFIG & CEK LOGIN
// ======================================================
include "include/config.php";
include "include/guard.php";

// ======================================================
// (B) AMBIL DATA REFERENSI UNTUK DROPDOWN
// Catatan: untuk supplier kita bangun HTML <option> sekali,
//          supaya bisa dipakai ulang saat add baris via JS.
// ======================================================
$resultBrandList    = mysqli_query($connection, "SELECT brand_id, brand_name FROM brand ORDER BY brand_name");
$resultModelList    = mysqli_query($connection, "SELECT model_id, model_name FROM model ORDER BY model_name");
$resultUnitList     = mysqli_query($connection, "SELECT unit_id, unit_name, satuan_hitung FROM unit ORDER BY unit_name");
$resultCategoryList = mysqli_query($connection, "SELECT category_id, category_name FROM category ORDER BY category_name");

$resultSupplierList = mysqli_query($connection, "SELECT supplier_id, namasupplier FROM supplier WHERE status=1 ORDER BY namasupplier");
$supplierOptionsHtml = "";
while ($s = mysqli_fetch_assoc($resultSupplierList)) {
    $sid = (int)$s['supplier_id'];
    $snm = htmlspecialchars($s['namasupplier'], ENT_QUOTES);
    $supplierOptionsHtml .= "<option value=\"{$sid}\">{$snm}</option>";
}

// ======================================================
// (C) untuk ngelist referensi  (brand/model/category/unit)
// ======================================================
// Umum: brand/model/category — input berupa NAMA, balikan ID (insert jika belum ada)
function resolveReferenceId($connection, $tableName, $pk, $col, $inputValue) {
    $cleanValue = mysqli_real_escape_string($connection, trim($inputValue));
    if ($cleanValue === '') return null;

    $q = "SELECT $pk FROM $tableName WHERE $col = '$cleanValue' LIMIT 1";
    $r = mysqli_query($connection, $q);
    if ($r && mysqli_num_rows($r) > 0) {
        $row = mysqli_fetch_assoc($r);
        return (int)$row[$pk];
    }
    mysqli_query($connection, "INSERT INTO $tableName ($col) VALUES ('$cleanValue')")
        or die("Insert $tableName gagal: ".mysqli_error($connection));
    return (int)mysqli_insert_id($connection);
}

// Khusus unit: (unit_name + satuan_hitung)
function resolveUnitId($connection, $unitName, $unitType) {
    $cleanName = mysqli_real_escape_string($connection, trim($unitName));
    $cleanType = mysqli_real_escape_string($connection, trim($unitType));
    if ($cleanName === '') return null;

    $q = "SELECT unit_id FROM unit WHERE unit_name='$cleanName' LIMIT 1";
    $r = mysqli_query($connection, $q);
    if ($row = mysqli_fetch_assoc($r)) return (int)$row['unit_id'];

    mysqli_query($connection, "INSERT INTO unit (unit_name, satuan_hitung) VALUES ('$cleanName','$cleanType')")
        or die("Insert unit gagal: ".mysqli_error($connection));
    return (int)mysqli_insert_id($connection);
}

// ======================================================
// (D) PROSES SIMPAN PRODUK BARU
// - Tidak ada input "Nama Produk" lagi.
// - Kolom product.name diisi otomatis = "Brand + Model" (display).
// - Supplier hanya pilih dari DB (value = supplier_id).
// ======================================================
if (isset($_POST['btnSimpan'])) {
    // --- Input deskriptif (opsional) ---
    $deskripsiProduk = mysqli_real_escape_string($connection, $_POST['product_desc'] ?? '');
    $ukuranProduk    = mysqli_real_escape_string($connection, $_POST['product_size'] ?? '');

    // --- buat ROP ---
    $safety_stock    = (int)($_POST['safety_stock'] ?? 0);
    $lead_time_days  = (int)($_POST['lead_time_days'] ?? 1);
    $avg_daily_usage = (int)($_POST['avg_daily_usage'] ?? 0);
    $reorder_point   = ($avg_daily_usage * $lead_time_days) + $safety_stock;

    // --- Ambil string brand/model untuk display name ---
    $brandText = trim($_POST['brand_name']  ?? '');
    $modelText = trim($_POST['model_name']  ?? '');

    // --- Resolve ID referensi (insert jika perlu) ---
    $brandId    = resolveReferenceId($connection, 'brand',    'brand_id',    'brand_name',    $brandText);
    $modelId    = resolveReferenceId($connection, 'model',    'model_id',    'model_name',    $modelText);
    $categoryId = resolveReferenceId($connection, 'category', 'category_id', 'category_name', $_POST['category_name'] ?? '');
    $unitId     = resolveUnitId($connection, $_POST['unit_name'] ?? '', $_POST['satuan_hitung'] ?? '');

    // --- Bentuk display name (Brand + Model) ---
    $displayName = mysqli_real_escape_string($connection, trim($brandText.' '.$modelText));

    // --- Insert produk ---
    $sqlProduk = "
        INSERT INTO product (
          brand_id, model_id, unit_id, category_id,
          name, `desc`, ukuran, status,
          safety_stock, lead_time_days, avg_daily_usage, reorder_point
        ) VALUES (
          $brandId, $modelId, $unitId, $categoryId,
          '$displayName', '$deskripsiProduk', '$ukuranProduk', 1,
          $safety_stock, $lead_time_days, $avg_daily_usage, $reorder_point
        )";
    mysqli_query($connection, $sqlProduk) or die("Insert produk gagal: ".mysqli_error($connection));
    $newProductId = (int)mysqli_insert_id($connection);

    // --- Prefill inventory stok 0 untuk semua lokasi ---
    mysqli_query($connection, "
        INSERT INTO inventory(location_id, product_id, quantity)
        SELECT l.location_id, $newProductId, 0 FROM location l
        ON DUPLICATE KEY UPDATE quantity = inventory.quantity
    ") or die("Prefill inventory gagal: ".mysqli_error($connection));

    // --- Simpan relasi supplier_product (hanya dari supplier yang sudah ada; value=ID) ---
    if (!empty($_POST['supplier_id']) && is_array($_POST['supplier_id'])) {
        foreach ($_POST['supplier_id'] as $i => $sidRaw) {
            $supplierId = (int)$sidRaw; // hanya ID valid
            $hargaRaw   = $_POST['harga'][$i] ?? '';
            // normalisasi harga: hapus titik pemisah ribuan
            $harga = (float)str_replace(['.', ',',' '], '', $hargaRaw);
            if ($supplierId > 0 && $harga > 0) {
                $sqlSupp = "INSERT INTO supplier_product(supplier_id,product_id,price)
                            VALUES($supplierId,$newProductId,$harga)
                            ON DUPLICATE KEY UPDATE price=VALUES(price)";
                mysqli_query($connection, $sqlSupp)
                    or die("Gagal simpan supplier_product: ".mysqli_error($connection));
            }
        }
    }

    header("location:product.php?msg=add");
    exit;
}
?>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Tambah Produk - PBS Inventory</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<?php include "include/navbar.php"; ?>
<div id="layoutSidenav">
<?php include "include/menu.php"; ?>
<div id="layoutSidenav_content">
<main class="container-fluid px-4">
    <h1 class="mt-4">Tambah Produk Baru</h1>
    <ol class="breadcrumb mb-4"><li class="breadcrumb-item active">Form Produk</li></ol>

    <div class="mb-3"><a href="product.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</a></div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Form Tambah Produk</div>
        <div class="card-body">

        <!-- ==================================================
             Catatan:
             - Tidak ada input "Nama Produk".
             - Nama tampilan disusun otomatis dari Brand + Model.
        =================================================== -->
        <form method="POST" action="">
            <div class="row g-3">
                <!-- Deskripsi & Ukuran (opsional) -->
                <div class="col-md-6">
                    <label class="form-label">Deskripsi</label>
                    <input name="product_desc" class="form-control" placeholder="Keterangan singkat produk">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ukuran</label>
                    <input name="product_size" class="form-control" placeholder="cth: 10mm">
                </div>

                <!-- Brand / Model / Unit / Kategori -->
                <div class="col-md-3">
                    <label class="form-label">Brand</label>
                    <select name="brand_name" id="brandSelect" class="form-control" required>
                        <?php while($b = mysqli_fetch_assoc($resultBrandList)): ?>
                        <option value="<?= htmlspecialchars($b['brand_name']) ?>"><?= htmlspecialchars($b['brand_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Model</label>
                    <select name="model_name" id="modelSelect" class="form-control" required>
                        <?php while($m = mysqli_fetch_assoc($resultModelList)): ?>
                        <option value="<?= htmlspecialchars($m['model_name']) ?>"><?= htmlspecialchars($m['model_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Unit</label>
                    <select name="unit_name" id="unitNameSelect" class="form-control">
                        <?php while($u = mysqli_fetch_assoc($resultUnitList)): ?>
                        <option value="<?= htmlspecialchars($u['unit_name']) ?>"><?= htmlspecialchars($u['unit_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Satuan Hitung</label>
                    <select name="satuan_hitung" id="unitTypeSelect" class="form-control">
                        <option value="pcs">pcs</option>
                        <option value="box">box</option>
                        <option value="set">set</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select name="category_name" id="categorySelect" class="form-control">
                        <?php while($c = mysqli_fetch_assoc($resultCategoryList)): ?>
                        <option value="<?= htmlspecialchars($c['category_name']) ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- ================== BAGIAN ROP ================== -->
                <hr class="mt-4">

                <div class="mt-3 p-3 border rounded bg-light">
                    <h5 class="mb-1">Pengaturan Reorder Point (ROP)</h5>
                    <p class="text-muted small mb-3">
                        Section untuk mengatur titik pemesanan ulang (Reorder Point) per produk.
                    </p>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="avg_daily_usage" class="form-label">
                                    Rata-rata Pemakaian / Hari
                                </label>
                                <input type="number" class="form-control" id="avg_daily_usage" name="avg_daily_usage"
                                       value="0" min="0" required>
                                <div class="form-text">
                                    Perkiraan jumlah barang yang keluar setiap hari ( rata-rata penjualan harian).
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="lead_time_days" class="form-label">
                                    Lead Time (hari)
                                </label>
                                <input type="number" class="form-control" id="lead_time_days" name="lead_time_days"
                                       value="1" min="1" required>
                                <div class="form-text">
                                    Lama waktu (hari) dari pemesanan ke supplier sampai barang diterima.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="safety_stock" class="form-label">
                                    Safety Stock
                                </label>
                                <input type="number" class="form-control" id="safety_stock" name="safety_stock"
                                       value="0" min="0" required>
                                <div class="form-text">
                                    Stok minimal yang harus selalu tersedia sebagai cadangan (buffer) untuk mengantisipasi stock habis.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label for="reorder_point" class="form-label">Reorder Point (otomatis)</label>
                        <input type="number" class="form-control" id="reorder_point" name="reorder_point" readonly>
                        <div class="form-text">
                            ROP = (Rata-rata pemakaian per hari × Lead Time) + Safety Stock. 
                            Jika stok di bawah nilai ini, sistem akan menandai produk sebagai perlu pemesanan ulang.
                        </div>
                    </div>
                </div>
                <!-- ================= END BAGIAN ROP ================= -->

            </div>

            <!-- ================================================== -->
            <!-- (E) SUPPLIER PRODUK -->
            <!-- ================================================== -->
            <hr>
            <h5>Supplier Produk</h5>
            <p class="text-muted small mb-2">Pilih dari supplier yang sudah ada. (Tidak bisa tambah supplier baru di sini.)</p>

            <div id="supplierContainer">
                <!-- Baris supplier pertama (dibangun dengan options HTML mentah) -->
                <div class="row supplier-row mb-2">
                    <div class="col-md-6">
                        <select name="supplier_id[]" class="form-select supplier-select">
                            <option value="">-- Pilih Supplier --</option>
                            <?= $supplierOptionsHtml /* options siap pakai */ ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="harga[]" class="form-control" placeholder="Harga (Rp)" min="1">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger removeSupplier">Hapus</button>
                    </div>
                </div>
            </div>
            <button type="button" id="addSupplier" class="btn btn-sm btn-secondary mt-2">+ Tambah Supplier</button>

            <div class="mt-4">
                <button type="submit" name="btnSimpan" class="btn btn-success">Simpan Produk</button>
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
new TomSelect('#brandSelect',   {create:true,  persist:false, sortField:{field:'text',direction:'asc'}});
new TomSelect('#modelSelect',   {create:true,  persist:false, sortField:{field:'text',direction:'asc'}});
new TomSelect('#unitNameSelect',{create:true,  persist:false, sortField:{field:'text',direction:'asc'}});
new TomSelect('#unitTypeSelect',{create:true,  persist:false, sortField:{field:'text',direction:'asc'}});
new TomSelect('#categorySelect',{create:true,  persist:false, sortField:{field:'text',direction:'asc'}});

// ========================= SUPPLIER DYNAMIC ROWS =========================

// 1) Simpan HTML <option> supplier mentah dari PHP (sekali saja)
const supplierOptionsHTML = `<?= $supplierOptionsHtml ?>`;

// 2) Helper: inisialisasi TomSelect untuk select supplier (create:false)
function initSupplierSelect(context=document){
  context.querySelectorAll('select.supplier-select').forEach(sel=>{
    if(!sel.tomselect){
      new TomSelect(sel, {
        create: false,            // tidak boleh menambah supplier baru
        persist:false,
        maxOptions: 1000,
        sortField:{field:'text', direction:'asc'},
        placeholder: 'Pilih supplier...'
      });
    }
  });
}

// Init baris pertama
initSupplierSelect();

// 3) Tambah baris supplier TANPA meng-clone node yang sudah di-wrap TS.
//    Kita buat elemen baru dari HTML mentah setiap kali.
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
  initSupplierSelect(row); // inisialisasi TS untuk select yang baru saja dibuat
});

// 4) Hapus baris supplier (minimal sisakan 1 baris agar UX aman)
document.addEventListener('click', (e)=>{
  if(e.target.classList.contains('removeSupplier')){
    const rows = document.querySelectorAll('.supplier-row');
    if(rows.length > 1){
      e.target.closest('.supplier-row').remove();
    } else {
      // opsional: kosongkan nilai jika tinggal satu baris
      const row = e.target.closest('.supplier-row');
      row.querySelector('select.supplier-select').tomselect.clear();
      row.querySelector('input[name="harga[]"]').value = '';
    }
  }
});
</script>

<script>
function hitungROP() {
  const avg  = parseInt(document.getElementById('avg_daily_usage').value) || 0;
  const lead = parseInt(document.getElementById('lead_time_days').value) || 1;
  const ss   = parseInt(document.getElementById('safety_stock').value) || 0;

  const rop = (avg * lead) + ss;
  document.getElementById('reorder_point').value = rop;
}

['avg_daily_usage','lead_time_days','safety_stock'].forEach(id => {
  const el = document.getElementById(id);
  if (el) el.addEventListener('input', hitungROP);
});

document.addEventListener('DOMContentLoaded', hitungROP);
</script>

</body>
</html>
