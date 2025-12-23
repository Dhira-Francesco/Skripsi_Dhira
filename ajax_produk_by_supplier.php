<?php
// ======================================================
// AJAX: Ambil daftar produk berdasarkan supplier_id
// Return JSON: { ok: true, items: [{value:1, text:"Brand Model"}, ...] }
// ======================================================

header('Content-Type: application/json; charset=utf-8');

include "include/config.php"; // kalau file ini di folder ajax/, ubah jadi ../include/config.php

$supplierId = (int)($_GET['supplier_id'] ?? 0);
if ($supplierId <= 0) {
  echo json_encode(["ok" => false, "items" => []]);
  exit;
}

$sql = "
  SELECT 
    p.product_id,
    CONCAT(IFNULL(b.brand_name,''),' ',IFNULL(m.model_name,'')) AS label
  FROM supplier_product sp
  JOIN product p ON p.product_id = sp.product_id
  LEFT JOIN brand b ON b.brand_id = p.brand_id
  LEFT JOIN model m ON m.model_id = p.model_id
  WHERE sp.supplier_id = $supplierId
    AND p.status = 1
  ORDER BY b.brand_name, m.model_name
";

$res = mysqli_query($connection, $sql);
if (!$res) {
  echo json_encode(["ok" => false, "items" => []]);
  exit;
}

$items = [];
while ($row = mysqli_fetch_assoc($res)) {
  $items[] = [
    "value" => (int)$row["product_id"],
    "text"  => trim($row["label"])
  ];
}

echo json_encode(["ok" => true, "items" => $items]);
