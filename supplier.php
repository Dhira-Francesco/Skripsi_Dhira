<?php
// ======================================================

// ---------- Include koneksi dan session guard ----------
include_once "include/config.php";
include_once "include/guard.php";

// ======================================================
// (A) PROSES TAMBAH DATA SUPPLIER BARU
// ======================================================
if (isset($_POST['Simpan'])) {

    // -- Ambil data dari form, dan bersihkan dengan mysqli_real_escape_string --
    $namaSupplier = mysqli_real_escape_string($connection, $_POST['namasupplier']);
    $alamat       = mysqli_real_escape_string($connection, $_POST['alamat']);
    $phone        = mysqli_real_escape_string($connection, $_POST['phone']);
    $email        = mysqli_real_escape_string($connection, $_POST['email']);
    $status       = 1; // default aktif

    // -- Validasi sederhana (tidak boleh kosong) --
    if ($namaSupplier != '') {
        // -- Query untuk menyimpan data ke tabel supplier --
        $queryInsertSupplier = "
            INSERT INTO supplier (namasupplier, alamat, phone, email, status)
            VALUES ('$namaSupplier', '$alamat', '$phone', '$email', $status)";
        mysqli_query($connection, $queryInsertSupplier);

        // -- Redirect setelah berhasil simpan --
        header("location:supplier.php?msg=add");
        exit;
    }
}

// ======================================================
// (B) AMBIL DATA SUPPLIER DARI DATABASE UNTUK DITAMPILKAN
// ======================================================
$queryGetSupplier = "SELECT * FROM supplier ORDER BY supplier_id DESC";
$resultSupplier   = mysqli_query($connection, $queryGetSupplier);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Data Supplier - PBS Inventory</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
<?php include "include/navbar.php"; ?>
<div id="layoutSidenav">
    <?php include "include/menu.php"; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">

                <h1 class="mt-4">Supplier</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Supplier</li>
                </ol>

                <!-- ===================== NOTIFIKASI JIKA BERHASIL ===================== -->
                <?php if(isset($_GET['msg'])): ?>
                    <div class="alert alert-success">
                        <?php
                        if ($_GET['msg'] == 'add')    echo "Supplier berhasil ditambahkan!";
                        if ($_GET['msg'] == 'edit')   echo "Supplier berhasil diperbarui!";
                        if ($_GET['msg'] == 'status') echo "Status supplier berhasil diperbarui!";
                        ?>
                    </div>
                <?php endif; ?>

                <!-- ========================= FORM TAMBAH SUPPLIER ========================= -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-truck me-1"></i> Tambah Supplier
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Supplier</label>
                                    <input name="namasupplier" type="text" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Alamat</label>
                                    <input name="alamat" type="text" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Telepon</label>
                                    <input name="phone" type="text" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email</label>
                                    <input name="email" type="email" class="form-control">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-success" name="Simpan">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ========================= TABEL DATA SUPPLIER ========================= -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i> Daftar Supplier
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Supplier</th>
                                    <th>Telepon</th>
                                    <th>Email</th>
                                    <th>Alamat</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $nomorBaris = 1;
                                 while($rowSupplier = mysqli_fetch_assoc($resultSupplier)): ?>
                                <tr>
                                   <td><?php echo $nomorBaris++; ?></td>
                                    <td><?php echo htmlspecialchars($rowSupplier['namasupplier']); ?></td>
                                    <td><?php echo htmlspecialchars($rowSupplier['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($rowSupplier['email']); ?></td>
                                    <td><?php echo htmlspecialchars($rowSupplier['alamat']); ?></td>
                                    <td>
                                        <?php
                                        if ($rowSupplier['status'] == 1) {
                                            echo '<span class="badge bg-success">Aktif</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">Nonaktif</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <!-- Tombol Edit -->
                                        <a class="btn btn-sm btn-primary"
                                           href="supplieredit.php?id=<?php echo $rowSupplier['supplier_id']; ?>">
                                           Edit
                                        </a>

                                        <!-- Tombol Ubah Status -->
                                        <?php if($rowSupplier['status']==1): ?>
                                            <a class="btn btn-sm btn-warning"
                                               href="supplierstatus.php?id=<?php echo $rowSupplier['supplier_id']; ?>&status=0"
                                               onclick="return confirm('Nonaktifkan supplier ini?')">Nonaktifkan</a>
                                        <?php else: ?>
                                            <a class="btn btn-sm btn-success"
                                               href="supplierstatus.php?id=<?php echo $rowSupplier['supplier_id']; ?>&status=1"
                                               onclick="return confirm('Aktifkan supplier ini?')">Aktifkan</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
           <?php include "include/footer.php"; ?>

    </div>
</div>

<!-- ========================= SCRIPT TAMBAHAN ========================= -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script src="js/datatables-simple-demo.js"></script>
</body>
</html>
