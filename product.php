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
    $queryLokasi  = "SELECT location_id, namalokasi FROM location ORDER BY namalokasi";
    $resultLokasi = mysqli_query($connection, $queryLokasi) 
        or die("Query lokasi error: " . mysqli_error($connection));

    $listLokasi = []; // [location_id => namalokasi]
    while ($rowLokasi = mysqli_fetch_assoc($resultLokasi)) {
        $listLokasi[(int)$rowLokasi['location_id']] = $rowLokasi['namalokasi'];
    }

    // ======================================================
    // (C) AMBIL DATA PRODUK + REFERENSI
    // NOTE: kolom 'desc' adalah reserved word → wajib pakai backtick `desc`
    // NOTE: di unit gunakan 'satuan_hitung' (bukan unit_type)
    // ======================================================
    $queryProduk = "
        SELECT 
            p.product_id,
            CONCAT(b.brand_name,' ',m.model_name)        AS nama_produk,
            p.`desc`     AS deskripsi_produk,
            p.ukuran     AS ukuran_produk,
            p.status     AS status_produk,
            b.brand_name,
            m.model_name,
            c.category_name,
            u.unit_name,
            u.satuan_hitung,
            p.safety_stock,
            p.lead_time_days,
            p.avg_daily_usage,
            p.reorder_point
        FROM product p
        LEFT JOIN brand    b ON p.brand_id    = b.brand_id
        LEFT JOIN model    m ON p.model_id    = m.model_id
        LEFT JOIN category c ON p.category_id = c.category_id
        LEFT JOIN unit     u ON p.unit_id     = u.unit_id
        ORDER BY p.product_id DESC
    ";
    $resultProduk = mysqli_query($connection, $queryProduk)
        or die("Query produk error: " . mysqli_error($connection));
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

                    <!-- Notifikasi sukses setelah tambah -->
                    <?php if(isset($_GET['msg']) && $_GET['msg']=='add'): ?>
                        <div class="alert alert-success">Produk berhasil ditambahkan!</div>
                    <?php endif; ?>

                    <!-- Tombol menuju halaman tambah produk -->
                    <div class="mb-3 text-end">
                        <a href="tambahproduk.php" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Tambah Produk
                        </a>
                    </div>

                    <?php if(isset($_GET['msg']) && $_GET['msg']=='update'): ?>
                    <div class="alert alert-success">Produk berhasil diperbarui!</div>
                    <?php elseif(isset($_GET['msg']) && $_GET['msg']=='status'): ?>
                    <div class="alert alert-info">Status produk berhasil diubah!</div>
                    <?php endif; ?>



                    <!-- ================== TABEL PRODUK ================== -->
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <i class="fa fa-box"></i> Daftar Produk
                        </div>
                        <div class="card-body">
                            <table id="datatablesSimple" class="table table-bordered table-hover">
                                <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Brand</th>
                                    <th>Model</th>
                                    <th>Kategori</th>
                                    <th>Unit</th>
                                    <th>Ukuran</th>

                                    <!-- PARAMETER ROP -->
                                    <th>Avg/Hari</th>
                                    <th>Lead Time (hari)</th>
                                    <th>Safety Stock</th>
                                    <th>ROP</th>
                                    <th>Total Stok (All Lokasi)</th>

                                    <!-- Header stok per lokasi (dinamis dari $listLokasi) -->
                                    <?php foreach ($listLokasi as $namaLokasi): ?>
                                    <th>Stok (<?php echo htmlspecialchars($namaLokasi); ?>)</th>
                                    <?php endforeach; ?>

                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                                </thead>

                                <tbody>
                                    <?php 
                                    $nomorBaris = 1;
                                while ($rowProduk = mysqli_fetch_assoc($resultProduk)):
                                        $produkId = (int)$rowProduk['product_id'];

                                        $stokPerLokasi = [];
                                        $queryStok  = "SELECT location_id, quantity FROM inventory WHERE product_id = $produkId";
                                        $resultStok = mysqli_query($connection, $queryStok)
                                            or die("Query stok error: " . mysqli_error($connection));
                                        while ($rowStok = mysqli_fetch_assoc($resultStok)) {
                                            $stokPerLokasi[(int)$rowStok['location_id']] = (int)$rowStok['quantity'];
                                        }

                                        // Total stok semua lokasi untuk produk ini
                                        $totalStokAll = array_sum($stokPerLokasi);
                                    ?>
                                   

                                    <tr>
                                   <td><?php echo $nomorBaris++; ?></td>
                                  <!--  <td><?php echo htmlspecialchars($rowProduk['nama_produk']); ?></td> -->
                                    <td><?php echo htmlspecialchars($rowProduk['brand_name']); ?></td>
                                    <td><?php echo htmlspecialchars($rowProduk['model_name']); ?></td>
                                    <td><?php echo htmlspecialchars($rowProduk['category_name']); ?></td>
                                    <td><?php echo htmlspecialchars(trim($rowProduk['unit_name'].' '.$rowProduk['satuan_hitung'])); ?></td>
                                    <td><?php echo htmlspecialchars($rowProduk['ukuran_produk']); ?></td>

                                    <!-- PARAMETER ROP -->
                                    <td><?php echo (int)$rowProduk['avg_daily_usage']; ?></td>
                                    <td><?php echo (int)$rowProduk['lead_time_days']; ?></td>
                                    <td><?php echo (int)$rowProduk['safety_stock']; ?></td>
                                    <td><?php echo (int)$rowProduk['reorder_point']; ?></td>
                                    <td><?php echo (int)$totalStokAll; ?></td>

                                    <!-- Stok per lokasi -->
                                    <?php foreach ($listLokasi as $lokasiId => $namaLokasi): ?>
                                    <td><?php echo $stokPerLokasi[$lokasiId] ?? 0; ?></td>
                                    <?php endforeach; ?>

                                    <td>
                                    <?php if ((int)$rowProduk['status_produk'] === 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Nonaktif</span>
                                    <?php endif; ?>
                                    </td>
                                        <td>
                                        <!-- Tombol Edit -->
                                        <a href="productedit.php?id=<?php echo $rowProduk['product_id']; ?>"
                                        class="btn btn-sm btn-primary">
                                        Edit
                                        </a>

                                        
                                        <a href="product_supplier.php?id=<?php echo $rowProduk['product_id']; ?>" 
                                            class="btn btn-sm btn-info text-white">
                                            View Supplier
                                        </a>
                                        <!-- Tombol Ubah Status -->
                                        <?php if($rowProduk['status_produk']==1): ?>
                                            <a href="productstatus.php?id=<?php echo $rowProduk['product_id']; ?>&status=0"
                                            onclick="return confirm('Nonaktifkan produk ini?');"
                                            class="btn btn-sm btn-warning">
                                            Nonaktifkan
                                            </a>
                                        <?php else: ?>
                                            <a href="productstatus.php?id=<?php echo $rowProduk['product_id']; ?>&status=1"
                                            onclick="return confirm('Aktifkan produk ini?');"
                                            class="btn btn-sm btn-success">
                                            Aktifkan
                                            </a>
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
