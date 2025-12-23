<?php

// ======================================================

// ---------- Include koneksi dan session guard ----------
include_once "include/config.php";
include_once "include/guard.php";

// ======================================================
// (A) PROSES TAMBAH DATA Lokasi Peyimpanan BARU
// ======================================================
if (isset($_POST['Simpan'])) {

    // -- Ambil data dari form, dan bersihkan dengan mysqli_real_escape_string --
    $namalokasi = mysqli_real_escape_string($connection, $_POST['namalokasi']);
    $alamat       = mysqli_real_escape_string($connection, $_POST['alamat']);
    $status       = 1; // default aktif

    // -- Validasi sederhana (tidak boleh kosong) --
    if ($namalokasi != '') {
        // -- Query untuk menyimpan data ke tabel lokasi --
        $queryInsertLokasi = "
            INSERT INTO location (namalokasi, alamat, status)
            VALUES ('$namalokasi', '$alamat', $status)";
        mysqli_query($connection, $queryInsertLokasi);

        // -- Redirect setelah berhasil simpan --
        header("location:lokasi.php?msg=add");
        exit;
    }
}

// ======================================================
// (B) AMBIL DATA Lokasi Peyimpanan DARI DATABASE UNTUK DITAMPILKAN
// ======================================================
$queryGetLokasi = "SELECT * FROM location ORDER BY location_id DESC";
$resultlokasi   = mysqli_query($connection, $queryGetLokasi);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Data Lokasi Penyimpanan - PBS Inventory</title>
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

                <h1 class="mt-4">Lokasi Penyimpanan</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Lokasi Penyimpanan</li>
                </ol>

                <!-- ===================== NOTIFIKASI JIKA BERHASIL ===================== -->
                <?php if(isset($_GET['msg'])): ?>
                    <div class="alert alert-success">
                        <?php
                        if ($_GET['msg'] == 'add')    echo "Lokasi Penyimpanan berhasil ditambahkan!";
                        if ($_GET['msg'] == 'edit')   echo "Lokasi Penyimpanan berhasil diperbarui!";
                        if ($_GET['msg'] == 'status') echo "Status Lokasi Penyimpanan berhasil diperbarui!";
                        ?>
                    </div>
                <?php endif; ?>

                <!-- ========================= FORM TAMBAH Lokasi Penyimpanan ========================= -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-truck me-1"></i> Tambah Lokasi Penyimpanan
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lokasi</label>
                                    <input name="namalokasi" type="text" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Alamat</label>
                                    <input name="alamat" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-success" name="Simpan">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ========================= TABEL DATA Lokasi Penyimpanan ========================= -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i> Daftar Lokasi Penyimpanan
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Lokasi Penyimpanan</th>
                                    <th>Alamat</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $nomorBaris=1;
                                 while($rowlokasi = mysqli_fetch_assoc($resultlokasi)): ?>
                                <tr>
                                   <td><?php echo $nomorBaris++; ?></td>
                                    <td><?php echo htmlspecialchars($rowlokasi['namalokasi']); ?></td>
                                    <td><?php echo htmlspecialchars($rowlokasi['alamat']); ?></td>
                                    <td>
                                        <?php
                                        if ($rowlokasi['status'] == 1) {
                                            echo '<span class="badge bg-success">Aktif</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">Nonaktif</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <!-- Tombol Edit -->
                                        <a class="btn btn-sm btn-primary"
                                           href="lokasiedit.php?id=<?php echo $rowlokasi['location_id']; ?>">
                                           Edit
                                        </a>

                                        <!-- Tombol Ubah Status -->
                                        <?php if($rowlokasi['status']==1): ?>
                                            <a class="btn btn-sm btn-warning"
                                               href="lokasistatus.php?id=<?php echo $rowlokasi['location_id']; ?>&status=0"
                                               onclick="return confirm('Nonaktifkan Lokasi Penyimpanan ini?')">Nonaktifkan</a>
                                        <?php else: ?>
                                            <a class="btn btn-sm btn-success"
                                               href="lokasistatus.php?id=<?php echo $rowlokasi['location_id']; ?>&status=1"
                                               onclick="return confirm('Aktifkan Lokasi Penyimpanan ini?')">Aktifkan</a>
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
