<?php
require __DIR__ . '/db.php';

$product = $_GET['product'] ?? '';
$size = $_GET['size'] ?? '';

if (!$product || !$size) {
    echo json_encode(['available' => false, 'error' => 'Missing product or size']);
    exit;
}

$stmt = db()->prepare('SELECT stock_quantity FROM inventory WHERE product_name = ? AND size = ?');
$stmt->execute([$product, $size]);
$row = $stmt->fetch();

$available = $row && $row['stock_quantity'] > 0;
echo json_encode([
    'available' => $available, 
    'stock' => $row['stock_quantity'] ?? 0,
    'product' => $product,
    'size' => $size,
    'found' => $row ? true : false
]);
?>