<?php
require __DIR__ . '/db.php';
if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  header('Content-Type: application/json');
  echo json_encode(['error'=>'Not logged in']);
  exit;
}

$stmt = db()->prepare('
  SELECT id, product_name, unit_price, size, qty, (unit_price*qty) AS line_total
  FROM cart_items
  WHERE user_id=?
  ORDER BY added_at DESC
');
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

$total = 0.0;
foreach ($items as $it) $total += (float)$it['line_total'];

header('Content-Type: application/json');
echo json_encode(['items'=>$items, 'total'=>$total]);
