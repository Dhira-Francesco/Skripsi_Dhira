    <?php
    // ======================================================
    // (A) INCLUDE KONFIG & GUARD (login)
    // ======================================================
    include "include/config.php";
    include "include/guard.php";

     $querybrand = "
        SELECT brand_id, brand_name FROM brand
        ORDER BY brand_name DESC";
    $resultbrand = mysqli_query($connection, $querybrand)
        or die("Query brand error: " . mysqli_error($connection));
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
        <main class="container-fluid px-4">
                    <h1 class="mt-4">Manajemen Produk</h1>
                    <ol class="breadcrumb mb-4"><li class="breadcrumb-item active">Daftar Produk</li></ol>
                   
                    <!-- Tombol menuju halaman tambah produk -->
                    <div class="mb-3 text-end">
                        <a href="tambahproduk.php" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Tambah Produk
                        </a>
                    </div>

               



                    <!-- ================== TABEL PRODUK ================== -->
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <i class="fa fa-box"></i> Daftar Brand
                        </div>
                        <div class="card-body">
                            <table id="datatablesSimple" class="table table-bordered table-hover">
                                <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Daftar Brand</th>
                                    <th>edit</th>
                                </tr>
                                </thead>

                                <tbody>
                                    <?php 
                                    $nomorBaris = 1;
                                while ($rowbrand = mysqli_fetch_assoc($resultbrand)):
                                    ?>

                                    <tr>
                                   <td><?php echo $nomorBaris++; ?></td>
                                <td><?php echo htmlspecialchars($rowbrand['brand_name']); ?></td> 
                                    
                                    </td>
                                        <td>
                                        <!-- Tombol Edit -->
                                        <a href="productedit.php?id=<?php echo $rowbrand['brand_id']; ?>"
                                        class="btn btn-sm btn-primary">
                                        Edit
                                        </a>
                                    </td>

                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
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
        <style>
            /* Sembunyikan search bar Simple-DataTables */
            .dataTable-top .dataTable-search {
                display: none !important;
            }
        </style>
    </body>
    </html>
