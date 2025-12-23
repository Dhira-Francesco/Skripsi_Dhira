<?php
// login.php
 include "include/config.php";
if (isset($_POST['btnLogin'])) {
  $username = mysqli_real_escape_string($connection, $_POST['username'] ?? '');
  $password = mysqli_real_escape_string($connection, $_POST['password'] ?? '');

  // tabel `user` berisi kolom: username, password (MD5 sementara, sesuai gaya lama)
  $sql = "SELECT * FROM `user` WHERE username='$username' AND `password`=MD5('$password') LIMIT 1";
  $q   = mysqli_query($connection, $sql);

  if ($row = mysqli_fetch_assoc($q)) {
    $_SESSION['stLogin'] = true;
    $_SESSION['user']    = $row['username'];
    header('Location: index.php'); // masuk ke halaman utama
    exit;
  } else {
    $msg = 'Username atau password salah.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login - PBS Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<?php
// login.php
 include "include/config.php";

$msg = '';
if (isset($_POST['btnLogin'])) {
  $username = mysqli_real_escape_string($connection, $_POST['username'] ?? '');
  $password = mysqli_real_escape_string($connection, $_POST['password'] ?? '');

  // tabel `user` berisi kolom: username, password 
  $sql = "SELECT * FROM `user` WHERE username='$username' AND `password`=MD5('$password') LIMIT 1";
  $q   = mysqli_query($connection, $sql);

  if ($row = mysqli_fetch_assoc($q)) {
    $_SESSION['stLogin'] = true;
    $_SESSION['user']    = $row['username'];
    header('Location: index.php'); // masuk ke halaman utama
    exit;
  } else {
    $msg = 'Username atau password salah.';
  }
}
?>

<body>
<section class="bg-light p-3 p-md-4 p-xl-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-xxl-11">
        <div class="card border-light-subtle shadow-sm">
          <div class="row g-0">
            <div class="col-12 col-md-6">
              <img class="img-fluid rounded-start w-100 h-100 object-fit-cover" loading="lazy"
                   src="assets/images/pbs.png" alt="Welcome back you've been missed!">
            </div>
            <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
              <div class="col-12 col-lg-11 col-xl-10">
                <div class="card-body p-3 p-md-4 p-xl-5">

                  <div class="text-center mb-4">
              
                  </div>
                  <h4 class="text-center mb-4">Welcome back to PBS Management</h4>

                  <?php if ($msg): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($msg) ?></div>
                  <?php endif; ?>

                  <!--  pakai method POST -->
                  <form method="post" action="">
                    <div class="row gy-3 overflow-hidden">
                      <div class="col-12">
                        <div class="form-floating mb-3">
                          <input type="text" class="form-control" name="username" id="username"
                                 placeholder="Username" required autofocus>
                          <label for="username" class="form-label">Username</label>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="form-floating mb-3">
                          <input type="password" class="form-control" name="password" id="password"
                                 placeholder="Password" required>
                          <label for="password" class="form-label">Password</label>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="d-grid">
                          <button class="btn btn-dark btn-lg" type="submit" name="btnLogin">Log in now</button>
                        </div>
                      </div>
                    </div>
                  </form>

                </div><!--/card-body-->
              </div>
            </div>
          </div><!--/row-->
        </div><!--/card-->
      </div>
    </div>
  </div>
</section>
<script src="https://unpkg.com/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
