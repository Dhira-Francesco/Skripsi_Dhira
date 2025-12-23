<?php
// ======================================================
// FILE   : useredit.php
// DESC   : Halaman untuk mengedit data user
// ======================================================

include_once "include/config.php";
include_once "include/guard.php";

// ---------- Ambil data berdasarkan ID ----------
$iduser = $_GET['id'];
$queryGetData = "SELECT * FROM user WHERE user_id=$iduser";
$resultData   = mysqli_query($connection, $queryGetData);
$datauser = mysqli_fetch_assoc($resultData);

// ======================================================
// (A) PROSES UPDATE DATA SAAT FORM DIKIRIM
// ======================================================
if (isset($_POST['Edit'])) {
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $password       = mysqli_real_escape_string($connection, $_POST['password']);

    $queryUpdateuser= "
        UPDATE user 
        SET username='$username', password='$password'
        WHERE user_id=$iduser";
    mysqli_query($connection, $queryUpdateuser);

    header("location:latihan2.php?msg=edit");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Edit User</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body class="sb-nav-fixed">
<?php include "include/navbar.php"; ?>
<div id="layoutSidenav">
    <?php include "include/menu.php"; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Edit User</h1>
                <form method="post">
                    
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Username</label>
                                    <input name="username" type="text" class="form-control" value="<?php echo htmlspecialchars($datauser['username']); ?>" required>
                                </div>
                                    <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <input name="password" type="text" class="form-control"value="<?php echo htmlspecialchars($datauser['password']); ?>"required>
                                </div>
                            </div>

                    <div class="mt-3">
                        <button class="btn btn-primary" name="Edit">Simpan Perubahan</button>
                        <a href="latihan2.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </main>
           <?php include "include/footer.php"; ?>
    </div>
</div>
</body>
</html>
