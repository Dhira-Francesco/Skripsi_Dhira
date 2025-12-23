<?php
header('Content-Type: application/json; charset=utf-8');

include "include/config.php";

$locationId = (int)($_GET['location_id'] ?? 0);
$productId  = (int)($_GET['product_id'] ?? 0);

if ($locationId <= 0 || $productId <= 0) {
  echo json_encode(["ok"=>false, "quantity"=>null]);
  exit;
}

$sql = "
  SELECT quantity
  FROM inventory
  WHERE location_id=$locationId AND product_id=$productId
  LIMIT 1
";
$res = mysqli_query($connection, $sql);
if (!$res) {
  echo json_encode(["ok"=>false, "quantity"=>null]);
  exit;
}

$row = mysqli_fetch_assoc($res);
$qty = $row ? (int)$row['quantity'] : 0; // kalau belum ada baris inventory -> anggap 0

echo json_encode(["ok"=>true, "quantity"=>$qty]);
