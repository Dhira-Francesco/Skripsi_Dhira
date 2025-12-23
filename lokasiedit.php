<?php
// ======================================================
// FILE   : lokasiedit.php
// DESC   : Halaman untuk mengedit data lokasi penyimpanan
// ======================================================

include_once "include/config.php";
include_once "include/guard.php";

// ---------- Ambil data berdasarkan ID ----------
if (!isset($_GET['id'])) {
    die("ID lokasi tidak ditemukan.");
}

$idlokasi = (int) $_GET['id'];

$queryGetData = "SELECT * FROM `location` WHERE `location_id` = $idlokasi";
$resultData   = mysqli_query($connection, $queryGetData);

if (!$resultData) {
    die("Gagal ambil data lokasi: " . mysqli_error($connection));
}

$dataLocation = mysqli_fetch_assoc($resultData);
if (!$dataLocation) {
    die("Data lokasi tidak ditemukan.");
}

// ======================================================
// (A) PROSES UPDATE DATA SAAT FORM DIKIRIM
// ======================================================
if (isset($_POST['Edit'])) {
    $namalokasi = mysqli_real_escape_string($connection, $_POST['namalokasi']);
    $alamat     = mysqli_real_escape_string($connection, $_POST['alamat']);

    $queryUpdateLocation = "
        UPDATE `location` 
        SET `namalokasi` = '$namalokasi',
            `alamat`     = '$alamat'
        WHERE `location_id` = $idlokasi
    ";

    $updateResult = mysqli_query($connection, $queryUpdateLocation);

    if (!$updateResult) {
        die("Gagal update lokasi: " . mysqli_error($connection));
    }

    header("Location: lokasi.php?msg=edit");
    exit;
}
?>

<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Edit Lokasi Penyimpanan</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body class="sb-nav-fixed">
<?php include "include/navbar.php"; ?>
<div id="layoutSidenav">
    <?php include "include/menu.php"; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Edit Lokasi Penyimpanan</h1>

                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lokasi</label>
                            <input name="namalokasi"
                                   type="text"
                                   class="form-control"
                                   required
                                   value="<?php echo htmlspecialchars($dataLocation['namalokasi']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alamat</label>
                            <input name="alamat"
                                   type="text"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($dataLocation['alamat']); ?>">
                        </div>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-primary" name="Edit">Simpan Perubahan</button>
                        <a href="lokasi.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </main>
        <?php include "include/footer.php"; ?>
    </div>
</div>
</body>
</html>
