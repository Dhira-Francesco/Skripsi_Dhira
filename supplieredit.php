<?php
// ======================================================
// FILE   : supplieredit.php
// DESC   : Halaman untuk mengedit data supplier
// ======================================================

include_once "include/config.php";
include_once "include/guard.php";

// ---------- Ambil data berdasarkan ID ----------
$idSupplier = $_GET['id'];
$queryGetData = "SELECT * FROM supplier WHERE supplier_id=$idSupplier";
$resultData   = mysqli_query($connection, $queryGetData);
$dataSupplier = mysqli_fetch_assoc($resultData);

// ======================================================
// (A) PROSES UPDATE DATA SAAT FORM DIKIRIM
// ======================================================
if (isset($_POST['Edit'])) {
    $namaSupplier = mysqli_real_escape_string($connection, $_POST['namasupplier']);
    $alamat       = mysqli_real_escape_string($connection, $_POST['alamat']);
    $phone        = mysqli_real_escape_string($connection, $_POST['phone']);
    $email        = mysqli_real_escape_string($connection, $_POST['email']);
    $status       = $_POST['status'];

    $queryUpdateSupplier = "
        UPDATE supplier 
        SET namasupplier='$namaSupplier', alamat='$alamat', phone='$phone', email='$email', status=$status
        WHERE supplier_id=$idSupplier";
    mysqli_query($connection, $queryUpdateSupplier);

    header("location:supplier.php?msg=edit");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Edit Supplier</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body class="sb-nav-fixed">
<?php include "include/navbar.php"; ?>
<div id="layoutSidenav">
    <?php include "include/menu.php"; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Edit Supplier</h1>
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Supplier</label>
                            <input name="namasupplier" class="form-control"
                                   value="<?php echo htmlspecialchars($dataSupplier['namasupplier']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alamat</label>
                            <input name="alamat" class="form-control"
                                   value="<?php echo htmlspecialchars($dataSupplier['alamat']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telepon</label>
                            <input name="phone" class="form-control"
                                   value="<?php echo htmlspecialchars($dataSupplier['phone']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input name="email" class="form-control"
                                   value="<?php echo htmlspecialchars($dataSupplier['email']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" <?php echo ($dataSupplier['status']==1?'selected':''); ?>>Aktif</option>
                                <option value="0" <?php echo ($dataSupplier['status']==0?'selected':''); ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary" name="Edit">Simpan Perubahan</button>
                        <a href="supplier.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </main>
           <?php include "include/footer.php"; ?>
    </div>
</div>
</body>
</html>
