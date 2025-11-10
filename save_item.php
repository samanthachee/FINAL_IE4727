<?php
require __DIR__ . '/db.php';
if (empty($_SESSION['user_id'])) { http_response_code(401); echo 'Not logged in'; exit; }

$data  = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
$name  = trim($data['product_name'] ?? '');
$price = (float)($data['unit_price'] ?? 0);
$size  = trim($data['size'] ?? 'M');
$qty   = max(1, (int)($data['qty'] ?? 1));

if ($name === '' || $price <= 0) { http_response_code(400); echo 'Bad data'; exit; }

$pdo = db();
$pdo->beginTransaction();
try {
  // Check inventory
  $inv = $pdo->prepare('SELECT stock_quantity FROM inventory WHERE product_name=? AND size=?');
  $inv->execute([$name, $size]);
  $stock = $inv->fetch();
  
  if (!$stock || $stock['stock_quantity'] < $qty) {
    $pdo->rollBack();
    http_response_code(400);
    echo 'Insufficient stock';
    exit;
  }
  
  $sel = $pdo->prepare('SELECT id, qty FROM cart_items WHERE user_id=? AND product_name=? AND unit_price=? AND size=?');
  $sel->execute([$_SESSION['user_id'], $name, $price, $size]);
  if ($row = $sel->fetch()) {
    $upd = $pdo->prepare('UPDATE cart_items SET qty = qty + ? WHERE id = ?');
    $upd->execute([$qty, $row['id']]);
  } else {
    $ins = $pdo->prepare('INSERT INTO cart_items(user_id,product_name,unit_price,size,qty) VALUES (?,?,?,?,?)');
    $ins->execute([$_SESSION['user_id'], $name, $price, $size, $qty]);
  }
  
  // Update inventory
  $upd_inv = $pdo->prepare('UPDATE inventory SET stock_quantity = stock_quantity - ? WHERE product_name=? AND size=?');
  $upd_inv->execute([$qty, $name, $size]);
  
  $pdo->commit();
  echo 'OK';
} catch (Throwable $e) {
  $pdo->rollBack();
  http_response_code(500); echo 'Error';
}
