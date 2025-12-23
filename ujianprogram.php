    <?php
    // ======================================================
    // (A) INCLUDE KONFIG & GUARD (login)
    // ======================================================
    include "include/config.php";
    include "include/guard.php";

    // ======================================================
    // (B) AMBIL DAFTAR LOKASI (untuk header kolom stok per lokasi)
    // NOTE: di schema, kolomnya 'namalokasi' (bukan location_name)
    // ======================================================

    // ======================================================
    // (C) AMBIL DATA PRODUK + REFERENSI
    // NOTE: kolom 'desc' adalah reserved word → wajib pakai backtick `desc`
    // NOTE: di unit gunakan 'satuan_hitung' (bukan unit_type)
    // ======================================================
    $queryProduk = "
        SELECT 
            p.product_id,
            CONCAT(b.brand_name,' ',m.model_name)        AS nama_produk,
            p.status     AS status_produk,
            b.brand_name,
            m.model_name,
            c.category_name,
            p.reorder_point,
            sp.price,
            s.namasupplier
        FROM product p
        LEFT JOIN brand    b ON p.brand_id    = b.brand_id
        LEFT JOIN model    m ON p.model_id    = m.model_id
        LEFT JOIN category c ON p.category_id = c.category_id
        LEFT JOIN unit     u ON p.unit_id     = u.unit_id
        LEFT JOIN supplier_product  sp on p.product_id = sp.product_id
        LEFT JOIN supplier s on sp.supplier_id = s.supplier_id
        ORDER BY p.product_id DESC
    ";
    $resultProduk = mysqli_query($connection, $queryProduk)
        or die("Query produk error: " . mysqli_error($connection));

     $querySupplier = "
    SELECT s.namasupplier, s.phone, s.email, sp.price
    FROM supplier_product sp
    JOIN supplier s ON sp.supplier_id = s.supplier_id
";
    $resultSupplier = mysqli_query($connection, $querySupplier);
?>
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
                    <h1 class="mt-4">Testing Sidang</h1>
                    <ol class="breadcrumb mb-4"><li class="breadcrumb-item active">Daftar Produk</li></ol>


                    <!-- ================== TABEL PRODUK ================== -->
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <i class="fa fa-box"></i> Daftar Laporan
                        </div>
                        <div class="card-body">
                            <table id="datatablesSimple" class="table table-bordered table-hover">
                                <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Produk</th>
                                    <th>Nama Supplier</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                </tr>
                                </thead>

                                <tbody>
                                    <?php 
                                    $nomorBaris = 1;
                                while ($rowProduk = mysqli_fetch_assoc($resultProduk)):
                                        $produkId = (int)$rowProduk['product_id'];
                                    ?>
                                    <tr>
                                   <td><?php echo $nomorBaris++; ?></td>
                                  <td><?php echo htmlspecialchars($rowProduk['nama_produk']); ?></td>
                                    <td><?php echo htmlspecialchars($rowProduk['namasupplier']); ?></td>
                                    <td><?php echo htmlspecialchars($rowProduk['category_name']); ?></td>
                                    <td><?php echo htmlspecialchars($rowProduk['price']); ?></td>

                                    <td>
                                    <?php if ((int)$rowProduk['status_produk'] === 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Nonaktif</span>
                                    <?php endif; ?>
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
