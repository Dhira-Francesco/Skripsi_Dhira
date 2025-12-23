<?php
// ======================================================
// FILE   : latihan2
// AUTHOR : Dhira Halim
// DESC   : Halaman utama untuk menambah dan menampilkan data user
// NOTE   : Sudah menggunakan mysqli_real_escape_string() dan htmlspecialchars()
// ======================================================

// ---------- Include koneksi dan session guard ----------
include_once "include/config.php";
include_once "include/guard.php";

// ======================================================
// (A) PROSES TAMBAH DATA user BARU
// ======================================================
// ======================================================
// (A) PROSES TAMBAH DATA user BARU
// ======================================================
if (isset($_POST['Simpan'])) {

    // -- Ambil data dari form --
    $raw_username = $_POST['username'];
    $raw_password = $_POST['password'];

    // 1. Lakukan HASHING pada password mentah (untuk keamanan)
    //    Gunakan password_hash() untuk enkripsi yang aman.
    if (!empty($raw_password)) {
        $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);
    } else {
        // Handle jika password kosong, meskipun harusnya sudah di-required di form
        $hashed_password = '';
    }

    // 2. Bersihkan input dengan mysqli_real_escape_string
    $username = mysqli_real_escape_string($connection, $raw_username);
    $safe_hashed_password = mysqli_real_escape_string($connection, $hashed_password);
    

    // -- Validasi sederhana (tidak boleh kosong) --
    if ($username != '' && $raw_password != '') {
        // -- Query untuk menyimpan data ke tabel user --
        // Pastikan daftar kolom dan VALUES memiliki jumlah yang sama!
        $queryInsertuser = "
            INSERT INTO user (username, password)
            VALUES ('$username', '$safe_hashed_password')";
            
        mysqli_query($connection, $queryInsertuser);

        // -- Redirect setelah berhasil simpan --
        header("location:latihan2.php?msg=add");
        exit;
    }
}

// ======================================================
// (B) AMBIL DATA user DARI DATABASE UNTUK DITAMPILKAN
// ======================================================
$queryGetUser = "SELECT * FROM user ORDER BY user_id DESC";
$resultuser  = mysqli_query($connection, $queryGetUser);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Data User - PBS Inventory</title>
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

                <h1 class="mt-4">Manajamen User</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">User</li>
                </ol>

                <!-- ===================== NOTIFIKASI JIKA BERHASIL ===================== -->
                <?php if(isset($_GET['msg'])): ?>
                    <div class="alert alert-success">
                        <?php
                        if ($_GET['msg'] == 'add')    echo "User berhasil ditambahkan!";
                        if ($_GET['msg'] == 'edit')   echo "User berhasil diperbarui!";
                        if ($_GET['msg'] == 'hapus')   echo "User berhasil dihapus!";
                        ?>
                    </div>
                <?php endif; ?>

                <!-- ========================= FORM TAMBAH User ========================= -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-truck me-1"></i> Tambah User
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Username</label>
                                    <input name="username" type="text" class="form-control" required>
                                </div>
                                    <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <input name="password" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-success" name="Simpan">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ========================= TABEL DATA User ========================= -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i> Daftar User Aktif
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Password</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $nomorBaris = 1;
                                 while($rowuser = mysqli_fetch_assoc($resultuser)): ?>
                                <tr>
                                   <td><?php echo $nomorBaris++; ?></td>
                                    <td><?php echo htmlspecialchars($rowuser['username']); ?></td>
                                    <td><?php echo htmlspecialchars($rowuser['password']); ?></td>
                                    <td>
                                        <!-- Tombol Edit -->
                                        <a class="btn btn-sm btn-primary"
                                           href="latihan2edit.php?id=<?php echo $rowuser['user_id']; ?>">
                                           Edit
                                        </a>
                                         <a class="btn btn-sm btn-danger"
                                           href="latihan2hapus.php?id=<?php echo $rowuser['user_id']; ?>">
                                           Hapus
                                        </a>
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
